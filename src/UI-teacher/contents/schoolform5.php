<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$mysqli = new mysqli("db", "stuser", "stpass", "stamaraiadb");
if ($mysqli->connect_error) die("DB Connection failed: " . $mysqli->connect_error);


$templatePath = BASE_PATH . '/src/UI-teacher/contents/sf5/sf5.xlsx';
$saveDir = BASE_PATH . '/sf5_files';
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

$totalRows = 100; // Dynamic limit
$skipRow = null; // No skipped rows

$formData = $_SESSION['sf5_form'] ?? [];
$downloadLink = $_SESSION['sf5_download'] ?? '';

// Don't initialize signatories - keep them null/undefined until loaded from database


$school_year = $_GET['school_year'] ?? '';
$sectionId = $_GET['section_id'] ?? '';
$gradeLevel = $_GET['grade'] ?? '';
$sectionName = $_GET['section'] ?? '';

$progressCategories = [
    'did_not_meet' => ['min' => 0, 'max' => 74],
    'fairly_satisfactory' => ['min' => 75, 'max' => 79],
    'satisfactory' => ['min' => 80, 'max' => 84],
    'very_satisfactory' => ['min' => 85, 'max' => 89],
    'outstanding' => ['min' => 90, 'max' => 100]
];

if ($sectionId) {
    // Get section info from database to ensure correct grade/section with proper spacing
    $sectionQuery = $mysqli->prepare("SELECT section_name, section_grade_level FROM sections WHERE section_id = ? LIMIT 1");
    $sectionQuery->bind_param("i", $sectionId);
    $sectionQuery->execute();
    $sectionResult = $sectionQuery->get_result();
    if ($sectionRow = $sectionResult->fetch_assoc()) {
        $gradeLevel = $sectionRow['section_grade_level'];
        $sectionName = $sectionRow['section_name'];
    }
    $sectionQuery->close();

    // Preserve signatories if they exist
    $preservedSignatories = [
        'prepared_by' => $formData['prepared_by'] ?? null,
        'certified_by' => $formData['certified_by'] ?? null,
        'reviewed_by' => $formData['reviewed_by'] ?? null
    ];

    // Clear all old form data when loading a new section
    $formData = [
        'school_year' => $school_year,
        'grade_level' => $gradeLevel,
        'section' => $sectionName,
        'male_total' => 0,
        'female_total' => 0,
        'combined_total' => 0,
        'student_rows' => [],
        'lrn' => [],
        'name' => [],
        'average' => [],
        'action' => [],
        'sex' => [],
        'did_not_meet' => [],
        'summary' => ['promoted' => ['male' => 0, 'female' => 0, 'total' => 0], 'conditional' => ['male' => 0, 'female' => 0, 'total' => 0], 'retained' => ['male' => 0, 'female' => 0, 'total' => 0]],
        'prepared_by' => $preservedSignatories['prepared_by'],
        'certified_by' => $preservedSignatories['certified_by'],
        'reviewed_by' => $preservedSignatories['reviewed_by'],
        'has_students' => false
    ];
    foreach ($progressCategories as $status => $range) {
        $formData['progress'][$status] = ['male' => 0, 'female' => 0, 'total' => 0];
    }
}


$formData['male_total'] = 0;
$formData['female_total'] = 0;
$formData['combined_total'] = 0;


$progressCategories = [
    'did_not_meet' => ['min' => 0, 'max' => 74],
    'fairly_satisfactory' => ['min' => 75, 'max' => 79],
    'satisfactory' => ['min' => 80, 'max' => 84],
    'very_satisfactory' => ['min' => 85, 'max' => 89],
    'outstanding' => ['min' => 90, 'max' => 100]
];
foreach ($progressCategories as $status => $range) {
    $formData['progress'][$status] = ['male' => 0, 'female' => 0, 'total' => 0];
}


if (!empty($sectionId) && !empty($gradeLevel) && !empty($sectionName) && !empty($school_year)) {
    $gradeLevel = trim($gradeLevel);
    $sectionName = trim($sectionName);
    $school_year = trim($school_year);

    $stmt = $mysqli->prepare("
        SELECT sf9.lrn, sf9.student_name, sf9.general_average, s.sex, sf9.school_year
        FROM sf9_data sf9
        JOIN student s ON s.lrn = sf9.lrn
        WHERE sf9.grade = ? AND LOWER(sf9.section) = LOWER(?) AND sf9.school_year = ?
        ORDER BY s.lname, s.fname
    ");
    $stmt->bind_param("sss", $gradeLevel, $sectionName, $school_year);
    $stmt->execute();
    $result = $stmt->get_result();
    $rowNum = 13;
    $formData['student_rows'] = [];
    $studentCount = 0;
    $hasStudentsWithAverages = false;

    while ($student = $result->fetch_assoc()) {
        $formData['lrn'][$rowNum] = $student['lrn'];
        $formData['name'][$rowNum] = $student['student_name'];
        $formData['average'][$rowNum] = $student['general_average'];
        if (!empty($student['general_average'])) {
            $hasStudentsWithAverages = true;
        }

        $formData['action'][$rowNum] = $formData['action'][$rowNum] ?? '';

        $formData['sex'][$rowNum] = strtoupper($student['sex']);
        $formData['student_rows'][] = $rowNum; // Track this row
        $studentCount++;

        if ($student['sex'] === 'MALE') $formData['male_total']++;
        if ($student['sex'] === 'FEMALE') $formData['female_total']++;

        $avg = (float)$student['general_average'];
        // Only compute progress if average is not empty
        if (!empty($student['general_average'])) {
            foreach ($progressCategories as $status => $range) {
                if ($avg >= $range['min'] && $avg <= $range['max']) {
                    if ($student['sex'] === 'MALE') $formData['progress'][$status]['male']++;
                    if ($student['sex'] === 'FEMALE') $formData['progress'][$status]['female']++;
                    $formData['progress'][$status]['total']++;
                    break;
                }
            }
        }
        $rowNum++;
        if ($rowNum > $totalRows) break;
    }
    $formData['combined_total'] = $formData['male_total'] + $formData['female_total'];
    $formData['total_student_count'] = $studentCount;
    $formData['has_students'] = $hasStudentsWithAverages;
    $stmt->close();
}


if (!empty($gradeLevel) && !empty($sectionName)) {
    $loadAct = $mysqli->prepare("SELECT action_taken, learners, curriculum, prepared_by, certified_by, reviewed_by FROM sf5_data WHERE grade_level=? AND section=? AND school_year=? LIMIT 1");
    $loadAct->bind_param("sss", $gradeLevel, $sectionName, $formData['school_year']);
    $loadAct->execute();
    $res = $loadAct->get_result();
    if ($row = $res->fetch_assoc()) {
        // Load actions
        $savedActions = json_decode($row['action_taken'], true);
        if (is_array($savedActions)) {
            foreach ($savedActions as $r => $val) {
                $formData['action'][$r] = $val;
            }
        }
        // Load learners/did_not_meet
        if (!empty($row['learners'])) {
            $savedLearners = json_decode($row['learners'], true);
            if (is_array($savedLearners)) {
                $formData['did_not_meet'] = $savedLearners;
            }
        }
        // Load curriculum and signatories
        $formData['curriculum'] = $row['curriculum'] ?? '';
        $formData['prepared_by'] = $row['prepared_by'] ?? '';
        $formData['certified_by'] = $row['certified_by'] ?? '';
        $formData['reviewed_by'] = $row['reviewed_by'] ?? '';
    }
    $loadAct->close();
}


if (isset($_GET['download'])) {
    $downloadFilename = $_SESSION['sf5_download'] ?? '';
    $file = $saveDir . DIRECTORY_SEPARATOR . $downloadFilename;

    if (isset($_GET['check'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'exists' => !empty($downloadFilename) && file_exists($file),
            'filename' => $downloadFilename
        ]);
        exit;
    }

    if (!empty($downloadFilename) && file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    // no file to send
    http_response_code(404);
    if (!isset($_GET['check'])) {
        ?>
        <script>
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'File not found',
                    text: '<?= addslashes(htmlspecialchars($downloadFilename, ENT_QUOTES)) ?>'
                });
            }
        </script>
        <?php
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Collect form data ---
    $formData = [
        'school_year' => $_POST['school_year'] ?? '',
        'curriculum' => $_POST['curriculum'] ?? '',
        'grade_level' => $_POST['grade_level'] ?? '',
        'section' => $_POST['section'] ?? '',
        'prepared_by' => $_POST['prepared_by'] ?? '',
        'certified_by' => $_POST['certified_by'] ?? '',
        'reviewed_by' => $_POST['reviewed_by'] ?? '',
        'student_rows' => [],
        'lrn' => $_POST['lrn'] ?? [],
        'name' => $_POST['name'] ?? [],
        'average' => $_POST['average'] ?? [],
        'action' => $_POST['action'] ?? [],
        'sex' => $_POST['sex'] ?? [],
        'did_not_meet' => $_POST['did_not_meet'] ?? [],
        'summary' => $_POST['summary'] ?? []
    ];

    // Filter out empty student rows
    foreach ($formData['lrn'] as $r => $lrn) {
        if (trim($lrn) !== '') {
            $formData['student_rows'][] = $r;
        }
    }

    // Count males/females
    $male = $female = 0;
    foreach ($formData['student_rows'] as $r) {
        $sex = strtoupper($formData['sex'][$r] ?? '');
        if ($sex === 'MALE') $male++;
        if ($sex === 'FEMALE') $female++;
    }
    $formData['male_total'] = $male;
    $formData['female_total'] = $female;
    $formData['combined_total'] = $male + $female;

    // Prepare progress categories
    foreach ($progressCategories as $status => $range) {
        $formData['progress'][$status] = ['male' => 0, 'female' => 0, 'total' => 0];
    }
    foreach ($formData['student_rows'] as $r) {
        $avg = (float)($formData['average'][$r] ?? 0);
        $sex = strtoupper($formData['sex'][$r] ?? '');
        foreach ($progressCategories as $status => $range) {
            if ($avg >= $range['min'] && $avg <= $range['max']) {
                if ($sex === 'MALE') $formData['progress'][$status]['male']++;
                if ($sex === 'FEMALE') $formData['progress'][$status]['female']++;
                $formData['progress'][$status]['total']++;
                break;
            }
        }
    }

    // Summary values
    $summaryRows = ['promoted', 'retained'];
    foreach ($summaryRows as $status) {
        $formData['summary'][$status]['male'] = (int)($formData['summary'][$status]['male'] ?? 0);
        $formData['summary'][$status]['female'] = (int)($formData['summary'][$status]['female'] ?? 0);
        $formData['summary'][$status]['total'] = (int)($formData['summary'][$status]['total'] ?? 0);
    }

    $_SESSION['sf5_form'] = $formData;

    // --- Load spreadsheet ---
    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Header values
    $sheet->setCellValue('G5', $formData['school_year']);
    $sheet->setCellValue('J5', $formData['curriculum']);
    $sheet->setCellValue('J7', $formData['grade_level']);
    $sheet->setCellValue('M7', $formData['section']);

    // Clear old student rows
    for ($row = 13; $row <= 100; $row++) {
        foreach (['A', 'B', 'F', 'G', 'H', 'I'] as $col) {
            $sheet->setCellValue("{$col}{$row}", '');
        }
    }

    // --- Insert student data ---
    foreach ($formData['student_rows'] as $r) {
        $sheet->setCellValueExplicit(
            "A{$r}",
            $formData['lrn'][$r] ?? '',
            DataType::TYPE_STRING
        );
        $sheet->setCellValue("B{$r}", $formData['name'][$r] ?? '');
        $sheet->setCellValue("F{$r}", $formData['average'][$r] ?? '');
        $sheet->setCellValue("G{$r}", $formData['action'][$r] ?? '');
        $sheet->setCellValue("H{$r}", $formData['sex'][$r] ?? '');
        $sheet->setCellValue("I{$r}", $formData['did_not_meet'][$r] ?? '');
    }

    // --- Add totals rows dynamically ---
    $lastStudentRow = !empty($formData['student_rows']) ? max($formData['student_rows']) : 12;
    $totalMaleRow = $lastStudentRow + 1;
    $totalFemaleRow = $totalMaleRow + 1;
    $combinedRow = $totalFemaleRow + 1;

    $sheet->setCellValue("B{$totalMaleRow}", "TOTAL MALE");
    $sheet->setCellValue("F{$totalMaleRow}", $formData['male_total']);

    $sheet->setCellValue("B{$totalFemaleRow}", "TOTAL FEMALE");
    $sheet->setCellValue("F{$totalFemaleRow}", $formData['female_total']);

    $sheet->setCellValue("B{$combinedRow}", "COMBINED");
    $sheet->setCellValue("F{$combinedRow}", $formData['combined_total']);

    // // --- Apply borders to all student + total rows ---
    // $sheet->getStyle("A13:I{$combinedRow}")->applyFromArray([
    //     'borders' => [
    //         'allBorders' => [
    //             'borderStyle' => Border::BORDER_THIN,
    //             'color' => ['argb' => 'FF000000'],
    //         ],
    //     ],
    //     'alignment' => [
    //         'horizontal' => Alignment::HORIZONTAL_CENTER,
    //         'vertical' => Alignment::VERTICAL_CENTER,
    //     ],
    // ]);
    // Apply borders to all student rows including merged I:J
    $sheet->getStyle("A12:J{$combinedRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ]);


    // Bold total rows
    $sheet->getStyle("B{$totalMaleRow}:F{$combinedRow}")->getFont()->setBold(true);

    // --- Progress & summary tables ---
    $summaryMap = [
        'promoted' => ['M11', 'N11', 'O11'],
        'retained' => ['M13', 'N13', 'O13']
    ];
    $grandTotal = 0;

    foreach ($summaryMap as $status => $cells) {
        $male   = $formData['summary'][$status]['male']   ?? 0;
        $female = $formData['summary'][$status]['female'] ?? 0;
        $total  = $formData['summary'][$status]['total']  ?? 0;

        $sheet->setCellValue($cells[0], $male);
        $sheet->setCellValue($cells[1], $female);
        $sheet->setCellValue($cells[2], $total);

        $grandTotal += $total;
    }

    $sheet->setCellValue('O15', $grandTotal);

    $progressMap = [
        'did_not_meet' => ['M20', 'N20', 'O20'],
        'fairly_satisfactory' => ['M22', 'N22', 'O22'],
        'satisfactory' => ['M24', 'N24', 'O24'],
        'very_satisfactory' => ['M26', 'N26', 'O26'],
        'outstanding' => ['M28', 'N28', 'O28']
    ];
    foreach ($progressMap as $status => $cells) {
        $sheet->setCellValue($cells[0], $formData['progress'][$status]['male']);
        $sheet->setCellValue($cells[1], $formData['progress'][$status]['female']);
        $sheet->setCellValue($cells[2], $formData['progress'][$status]['total']);
    }

    // Prepared / Certified / Reviewed by
    $sheet->setCellValue('L32', $formData['prepared_by']);
    $sheet->setCellValue('L37', $formData['certified_by']);
    $sheet->setCellValue('L42', $formData['reviewed_by']);

    // --- Save spreadsheet ---
    $schoolYear = preg_replace('/[^A-Za-z0-9_-]/', '', $formData['school_year']);
    $gradeLevel = preg_replace('/[^A-Za-z0-9_-]/', '', $formData['grade_level']);
    $section = preg_replace('/[^A-Za-z0-9_-]/', '', $formData['section']);
    $filename = trim("{$schoolYear}_{$gradeLevel}_{$section}.xlsx", '_') ?: 'sf5_' . time() . '.xlsx';
    $savePath = $saveDir . DIRECTORY_SEPARATOR . $filename;

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($savePath);

    $_SESSION['sf5_download'] = $filename;

    // --- Save actions to database ---
    $actionData = json_encode($formData['action'], JSON_UNESCAPED_UNICODE);
    $learnersData = json_encode($formData['did_not_meet'], JSON_UNESCAPED_UNICODE);

    // Escape data for SQL
    $school_year = $pdo->quote($formData['school_year']);
    $grade_level = $pdo->quote($formData['grade_level']);
    $section = $pdo->quote($formData['section']);
    $curriculum = $pdo->quote($formData['curriculum']);
    $action_taken = $pdo->quote($actionData);
    $learners = $pdo->quote($learnersData);

    $male_total = (int)$formData['male_total'];
    $female_total = (int)$formData['female_total'];
    $combined_total = (int)$formData['combined_total'];

    $promoted_male = (int)($formData['summary']['promoted']['male'] ?? 0);
    $promoted_female = (int)($formData['summary']['promoted']['female'] ?? 0);
    $promoted_total = (int)($formData['summary']['promoted']['total'] ?? 0);

    $retained_male = (int)($formData['summary']['retained']['male'] ?? 0);
    $retained_female = (int)($formData['summary']['retained']['female'] ?? 0);
    $retained_total = (int)($formData['summary']['retained']['total'] ?? 0);

    $dnm_male = (int)($formData['progress']['did_not_meet']['male'] ?? 0);
    $dnm_female = (int)($formData['progress']['did_not_meet']['female'] ?? 0);
    $dnm_total = (int)($formData['progress']['did_not_meet']['total'] ?? 0);

    $fs_male = (int)($formData['progress']['fairly_satisfactory']['male'] ?? 0);
    $fs_female = (int)($formData['progress']['fairly_satisfactory']['female'] ?? 0);
    $fs_total = (int)($formData['progress']['fairly_satisfactory']['total'] ?? 0);

    $sat_male = (int)($formData['progress']['satisfactory']['male'] ?? 0);
    $sat_female = (int)($formData['progress']['satisfactory']['female'] ?? 0);
    $sat_total = (int)($formData['progress']['satisfactory']['total'] ?? 0);

    $vs_male = (int)($formData['progress']['very_satisfactory']['male'] ?? 0);
    $vs_female = (int)($formData['progress']['very_satisfactory']['female'] ?? 0);
    $vs_total = (int)($formData['progress']['very_satisfactory']['total'] ?? 0);

    $out_male = (int)($formData['progress']['outstanding']['male'] ?? 0);
    $out_female = (int)($formData['progress']['outstanding']['female'] ?? 0);
    $out_total = (int)($formData['progress']['outstanding']['total'] ?? 0);

    $prepared_by = $pdo->quote($formData['prepared_by']);
    $certified_by = $pdo->quote($formData['certified_by']);
    $reviewed_by = $pdo->quote($formData['reviewed_by']);

    // Check if record exists
    $checkSql = "SELECT id FROM sf5_data WHERE grade_level = $grade_level AND section = $section AND school_year = $school_year LIMIT 1";
    $checkResult = $pdo->query($checkSql);

    if ($checkResult->rowCount() > 0) {
        // Update existing record
        $row = $checkResult->fetch();
        $id = $row['id'];
        $updateSql = "UPDATE sf5_data SET 
            curriculum = $curriculum,
            male_total = $male_total,
            female_total = $female_total,
            combined_total = $combined_total,
            promoted_male = $promoted_male,
            promoted_female = $promoted_female,
            promoted_total = $promoted_total,
            retained_male = $retained_male,
            retained_female = $retained_female,
            retained_total = $retained_total,
            progress_did_not_meet_male = $dnm_male,
            progress_did_not_meet_female = $dnm_female,
            progress_did_not_meet_total = $dnm_total,
            progress_fairly_satisfactory_male = $fs_male,
            progress_fairly_satisfactory_female = $fs_female,
            progress_fairly_satisfactory_total = $fs_total,
            progress_satisfactory_male = $sat_male,
            progress_satisfactory_female = $sat_female,
            progress_satisfactory_total = $sat_total,
            progress_very_satisfactory_male = $vs_male,
            progress_very_satisfactory_female = $vs_female,
            progress_very_satisfactory_total = $vs_total,
            progress_outstanding_male = $out_male,
            progress_outstanding_female = $out_female,
            progress_outstanding_total = $out_total,
            prepared_by = $prepared_by,
            certified_by = $certified_by,
            reviewed_by = $reviewed_by,
            learners = $learners,
            action_taken = $action_taken
            WHERE id = $id";
        $pdo->exec($updateSql);
    } else {
        // Insert new record
        $insertSql = "INSERT INTO sf5_data 
            (school_year, grade_level, section, curriculum, 
             male_total, female_total, combined_total,
             promoted_male, promoted_female, promoted_total,
             retained_male, retained_female, retained_total,
             progress_did_not_meet_male, progress_did_not_meet_female, progress_did_not_meet_total,
             progress_fairly_satisfactory_male, progress_fairly_satisfactory_female, progress_fairly_satisfactory_total,
             progress_satisfactory_male, progress_satisfactory_female, progress_satisfactory_total,
             progress_very_satisfactory_male, progress_very_satisfactory_female, progress_very_satisfactory_total,
             progress_outstanding_male, progress_outstanding_female, progress_outstanding_total,
             prepared_by, certified_by, reviewed_by, learners, action_taken) 
            VALUES ($school_year, $grade_level, $section, $curriculum, 
             $male_total, $female_total, $combined_total,
             $promoted_male, $promoted_female, $promoted_total,
             $retained_male, $retained_female, $retained_total,
             $dnm_male, $dnm_female, $dnm_total,
             $fs_male, $fs_female, $fs_total,
             $sat_male, $sat_female, $sat_total,
             $vs_male, $vs_female, $vs_total,
             $out_male, $out_female, $out_total,
             $prepared_by, $certified_by, $reviewed_by, $learners, $action_taken)";
        $pdo->exec($insertSql);
    }

    // --- Redirect back (include saved flag for JS notification) ---
    header("Location: " . $_SERVER['PHP_SELF'] . "?school_year=" . rawurlencode($formData['school_year']) . "&section_id=" . rawurlencode($sectionId) . "&grade=" . rawurlencode($formData['grade_level']) . "&section=" . rawurlencode($formData['section']) . "&saved=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SF5</title>
    <link rel="icon" href="<?php echo base_url() ?>/assets/image/logo2.png" type="image/x-icon">
    <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="<?= base_url() ?>/assets/js/sweetalert2.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f5f7;
            padding-bottom: 120px;
        }

        .header {
            background: #FF3860;
            color: white;
            padding: 7px 20px;
            display: flex;
            align-items: center;
        }

        .header img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .scrollable-table {
            max-height: 350px;
            overflow-y: auto;
        }

        .scrollable-table thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
        }

        .card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        #action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            /* space from table */
        }

        #action-buttons button,
        #action-buttons a {
            min-width: 140px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="<?php echo base_url() ?>/assets/image/logo2.png" alt="Logo">
        <h4>STA. MARIA WEB SYSTEM</h4>
    </div>
    <div class="container-fluid mt-4">
        <form method="post" id="sf5-form">
            <div class="row">
                <div class="col-md-8">

                    <div class="card p-3 mb-3">
                        <h5>School Info</h5>
                        <div class="row">
                            <div class="col-md-3"><input type="text" name="school_year" class="form-control" placeholder="School Year" value="<?= htmlspecialchars($formData['school_year'] ?? '', ENT_QUOTES) ?>" readonly></div>
                            <div class="col-md-3"><input type="text" name="curriculum" class="form-control" placeholder="Curriculum" value="<?= htmlspecialchars($formData['curriculum'] ?? '', ENT_QUOTES) ?>"></div>
                            <div class="col-md-3"><input type="text" name="grade_level" class="form-control" placeholder="Grade Level" value="<?= htmlspecialchars($formData['grade_level'] ?? '', ENT_QUOTES) ?>" readonly></div>
                            <div class="col-md-3"><input type="text" name="section" class="form-control" placeholder="Section" value="<?= htmlspecialchars($formData['section'] ?? '', ENT_QUOTES) ?>" readonly></div>
                        </div>
                    </div>

                    <div class="card p-3 mb-3">
                        <h5>Learners Table</h5>
                        <div class="scrollable-table">
                            <table class="table table-bordered table-sm text-center align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 8rem;">LRN</th>
                                        <th>Learner's Name</th>
                                        <th>General Average</th>
                                        <th>ACTION TAKEN: PROMOTED, CONDITIONAL, or RETAINED</th>
                                        <th style="width: 5rem;">Sex</th>
                                        <th>Did Not Meet Expectations of the ff. Learning Area/s as of end of current School Year </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Display only rows that have actual students
                                    if (!empty($formData['student_rows'])) {
                                        foreach ($formData['student_rows'] as $r):
                                    ?>
                                            <tr>
                                                <td><input type="text" name="lrn[<?= $r ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['lrn'][$r] ?? '') ?>" readonly></td>
                                                <td><input type="text" name="name[<?= $r ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['name'][$r] ?? '') ?>" readonly></td>
                                                <td><input type="text" name="average[<?= $r ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['average'][$r] ?? '') ?>" readonly></td>
                                                <td>
                                                    <?php
                                                    $average = isset($formData['average'][$r]) ? (float)$formData['average'][$r] : null;
                                                    $action  = $formData['action'][$r] ?? '';

                                                    if ($action === '' && $average !== null) {
                                                        $action = ($average >= 75) ? 'PROMOTED' : 'RETAINED';
                                                    }
                                                    ?>
                                                    <select name="action[<?= $r ?>]" class="form-control form-control-sm action-select" readonly>
                                                        <option value="<?= htmlspecialchars($action) ?>" selected>
                                                            <?= $action !== '' ? htmlspecialchars($action) : 'No results yet' ?>
                                                        </option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <select name="sex_display[<?= $r ?>]" class="form-control form-control-sm sex-select" readonly>
                                                        <?php
                                                        if (($formData['sex'][$r] ?? '') === '') {
                                                        ?>
                                                            <option value="" selected>Not selected</option>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <option value="<?= $formData['sex'][$r] ?>" selected><?= $formData['sex'][$r] ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <input type="hidden" name="sex[<?= $r ?>]" value="<?= htmlspecialchars($formData['sex'][$r] ?? '') ?>">
                                                </td>
                                                <td><input type="text" name="did_not_meet[<?= $r ?>]" class="form-control form-control-sm " value="<?= htmlspecialchars($formData['did_not_meet'][$r] ?? '') ?>"></td>
                                            </tr>
                                    <?php
                                        endforeach;
                                    }
                                    ?>
                                </tbody>

                            </table>

                        </div>
                        <div id="action-buttons">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='<?= base_url() ?>/src/UI-teacher/index.php?page=contents/sf5'">Back</button>
                            <button type="submit" id="save-grades" class="btn btn-primary">Save</button>
                            <button type="button" id="download-btn" class="btn btn-success">Download</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">

                    <div class="card p-3 mb-3">
                        <h5>Summary Table</h5>
                        <table class="table table-bordered table-sm text-center align-middle">
                            <tr>
                                <th>Status</th>
                                <th>Male</th>
                                <th>Female</th>
                                <th>Total</th>
                            </tr>
                            <?php foreach (['promoted', 'retained'] as $status): ?>
                                <tr>
                                    <td><?= ucfirst($status) ?></td>
                                    <td><input type="number" name="summary[<?= $status ?>][male]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['summary'][$status]['male'] ?? '') ?>" readonly></td>
                                    <td><input type="number" name="summary[<?= $status ?>][female]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['summary'][$status]['female'] ?? '') ?>" readonly></td>
                                    <td><input type="number" name="summary[<?= $status ?>][total]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['summary'][$status]['total'] ?? '') ?>" readonly></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>


                    <div class="card p-3 mb-3">
                        <h5>Learning Progress</h5>
                        <table class="table table-bordered table-sm text-center align-middle">
                            <tr>
                                <th>Performance</th>
                                <th>Male</th>
                                <th>Female</th>
                                <th>Total</th>
                            </tr>
                            <?php foreach (array_keys($progressCategories) as $status): ?>
                                <tr>
                                    <td><?= ucwords(str_replace('_', ' ', $status)) ?></td>
                                    <td><input type="number" name="progress[<?= $status ?>][male]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['progress'][$status]['male'] ?? '') ?>" readonly></td>
                                    <td><input type="number" name="progress[<?= $status ?>][female]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['progress'][$status]['female'] ?? '') ?>" readonly></td>
                                    <td><input type="number" name="progress[<?= $status ?>][total]" class="form-control form-control-sm" value="<?= htmlspecialchars($formData['progress'][$status]['total'] ?? '') ?>" readonly></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>


                    <div class="card p-3 mb-3">
                        <h5>Signatories</h5>
                        <div class="mb-2"><input type="text" name="prepared_by" class="form-control form-control-sm" placeholder="Prepared By" value="<?= htmlspecialchars($formData['prepared_by'] ?? '') ?>"></div>
                        <div class="mb-2"><input type="text" name="certified_by" class="form-control form-control-sm" placeholder="Certified Correct and Submitted By" value="<?= htmlspecialchars($formData['certified_by'] ?? '') ?>"></div>
                        <div class="mb-2"><input type="text" name="reviewed_by" class="form-control form-control-sm" placeholder="Reviewed By" value="<?= htmlspecialchars($formData['reviewed_by'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
        </form>




        <script>
            function computeAll() {
                updateActionsOnce();
                updateSummaryOnce();
            }

            function updateActionsOnce() {
                // Only compute if there are students with averages
                const hasAverages = Array.from(document.querySelectorAll('input[name^="average["]')).some(avg => !isNaN(parseFloat(avg.value)) && avg.value !== '');
                if (!hasAverages) return;

                document.querySelectorAll('input[name^="average["]').forEach(avgInput => {
                    const r = avgInput.name.match(/\[(\d+)\]/)?.[1];
                    if (!r) return;

                    const actionSelect = document.querySelector(`select[name="action[${r}]"]`);
                    if (!actionSelect || actionSelect.value !== '') return;

                    const avg = parseFloat(avgInput.value);
                    if (!isNaN(avg)) {
                        actionSelect.value = avg >= 75 ? 'PROMOTED' : 'RETAINED';
                    }
                });
            }


            function updateSummaryOnce() {
                // Only compute if there are students with averages
                const hasAverages = Array.from(document.querySelectorAll('input[name^="average["]')).some(avg => !isNaN(parseFloat(avg.value)) && avg.value !== '');
                if (!hasAverages) return;

                const summary = {
                    promoted: {
                        male: 0,
                        female: 0,
                        total: 0
                    },
                    retained: {
                        male: 0,
                        female: 0,
                        total: 0
                    }
                };

                document.querySelectorAll('select[name^="action["]').forEach(actionSelect => {
                    const r = actionSelect.name.match(/\[(\d+)\]/)?.[1];
                    if (!r) return;

                    const sexEl = document.querySelector(`[name="sex[${r}]"]`);
                    const sex = sexEl ? sexEl.value.toUpperCase() : '';
                    const action = actionSelect.value.toLowerCase();

                    if (!summary[action]) return;

                    if (sex === 'MALE') summary[action].male++;
                    if (sex === 'FEMALE') summary[action].female++;
                    summary[action].total++;
                });

                for (const status in summary) {
                    document.querySelector(`input[name="summary[${status}][male]"]`).value = summary[status].male;
                    document.querySelector(`input[name="summary[${status}][female]"]`).value = summary[status].female;
                    document.querySelector(`input[name="summary[${status}][total]"]`).value = summary[status].total;
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                computeAll();

                // Show success message after redirect from save action
                const params = new URLSearchParams(window.location.search);
                if (params.get('saved') === '1') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: 'Data saved successfully.',
                            confirmButtonText: 'OK'
                        });

                        // Clean URL so popup doesn’t show again on refresh
                        params.delete('saved');
                        const cleaned = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                        history.replaceState(null, '', cleaned);
                    }
                }

                // Download button with existence check via backend before triggering download
                const downloadBtn = document.getElementById('download-btn');
                if (downloadBtn) {
                    downloadBtn.addEventListener('click', async function () {
                        const checkParams = new URLSearchParams(window.location.search);
                        checkParams.set('download', '1');
                        checkParams.set('check', '1');
                        const checkUrl = window.location.pathname + '?' + checkParams.toString();

                        try {
                            const response = await fetch(checkUrl, { cache: 'no-store' });
                            if (!response.ok) throw new Error('Network check failed');

                            const data = await response.json();
                            if (data.exists) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Download ready',
                                    text: `Found ${data.filename}. Preparing download...`,
                                    timer: 900,
                                    showConfirmButton: false
                                });

                                setTimeout(() => {
                                    const downloadParams = new URLSearchParams(window.location.search);
                                    downloadParams.set('download', '1');
                                    downloadParams.delete('check');
                                    window.location.href = window.location.pathname + '?' + downloadParams.toString();
                                }, 950);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'File not found',
                                    text: 'No generated file exists yet. Save first before downloading.'
                                });
                            }
                        } catch (error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Download check failed',
                                text: 'Unable to verify if the export file exists.'
                            });
                        }
                    });
                }
            });
        </script>

</body>

</html>