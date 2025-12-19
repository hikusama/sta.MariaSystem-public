<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

if (!isset($showSuccess)) $showSuccess = false;
if (!isset($successMessage)) $successMessage = '';

$pdo = new PDO("mysql:host=localhost;dbname=stamariadb;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function build_sf10_filename($lrn, $first, $last)
{
    $safe_lrn = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$lrn);
    $safe_first = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$first);
    $safe_last = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$last);
    return trim($safe_lrn . '_' . $safe_first . '_' . $safe_last . '_SF10.xlsx', '_');
}
$student_id = $_GET['student_id'] ?? null;
$student = null;
if ($student_id) {
    $stmt = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
}
$saveDir = BASE_PATH . '/sf10_files';
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

$grades = $sections = $school_years = $advisers = [];
$learning_areas_all = $q1_all = $q2_all = $q3_all = $q4_all = [];
$final_ratings_all = $remarks_all = $general_averages = [];

if (isset($_GET['student_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM sf10_data WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$_GET['student_id']]);
    $sf10_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sf10_data && !empty($sf10_data['scholastic_records'])) {
        $scholastic_data = json_decode($sf10_data['scholastic_records'], true);


        if (!empty($scholastic_data)) {
            $scholastic_data['grade'] = $scholastic_data['grades'] ?? [];
            $scholastic_data['section'] = $scholastic_data['sections'] ?? [];
            $scholastic_data['school_year'] = $scholastic_data['school_years'] ?? [];
            $scholastic_data['adviser_name'] = $scholastic_data['advisers'] ?? [];
            $scholastic_data['learning_area'] = $scholastic_data['learning_areas'] ?? [];
            $scholastic_data['remarks_table'] = $scholastic_data['remarks'] ?? [];
            $scholastic_data['final_rating'] = $scholastic_data['final_ratings'] ?? [];
            $scholastic_data['general_average'] = $scholastic_data['general_averages'] ?? [];
        }
    } else {
        $scholastic_data = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $last_name = $_POST['last_name'] ?? ($student['lname'] ?? '');
    $first_name = $_POST['first_name'] ?? ($student['fname'] ?? '');
    $middle_name = $_POST['middle_name'] ?? ($student['mname'] ?? '');
    $suffix = $_POST['suffix'] ?? ($student['suffix'] ?? '');
    $lrn = $_POST['lrn'] ?? ($student['lrn'] ?? '');
    $birthdate = $_POST['birthdate'] ?? ($student['birthdate'] ?? '');
    $sex = $_POST['sex'] ?? ($student['sex'] ?? '');
    $school_name = $_POST['school_name'] ?? '';
    $school_id = $_POST['school_id'] ?? '';
    $school_address = $_POST['school_address'] ?? '';
    $kinder_progress_report = !empty($_POST['kinder_progress_report']) ? '✓' : '';
    $eccd_checklist = !empty($_POST['eccd_checklist']) ? '✓' : '';
    $kinder_certificate = !empty($_POST['kinder_certificate']) ? '✓' : '';
    $pept_passer = !empty($_POST['pept_passer']) ? '✓' : '';
    $pept_text = $_POST['pept_text'] ?? '';
    $exam_date = $_POST['exam_date'] ?? '';
    $others_check = !empty($_POST['others_check']) ? '✓' : '';
    $others_text = $_POST['others_text'] ?? '';
    $testing_center_name = $_POST['testing_center_name'] ?? '';
    $testing_center_address = $_POST['testing_center_address'] ?? '';
    $remark = $_POST['remark'] ?? '';


    for ($i = 1; $i <= 8; $i++) {

        $grades[$i] = $_POST['grade' . $i] ?? '';
        $sections[$i] = $_POST['section' . $i] ?? '';
        $school_years[$i] = $_POST['school_year' . $i] ?? '';
        $advisers[$i] = $_POST['adviser_name' . $i] ?? '';
        $learning_areas_all[$i] = $_POST['learning_area' . $i] ?? [];
        $q1_all[$i] = $_POST['q1_' . $i] ?? [];
        $q2_all[$i] = $_POST['q2_' . $i] ?? [];
        $q3_all[$i] = $_POST['q3_' . $i] ?? [];
        $q4_all[$i] = $_POST['q4_' . $i] ?? [];
        $remarks_all[$i] = $_POST['remarks_table_' . $i] ?? [];

        $final_ratings_all[$i] = [];
        $total = 0;
        $count = 0;
        for ($r = 0; $r < 15; $r++) {
            $q1 = is_numeric($q1_all[$i][$r] ?? null) ? floatval($q1_all[$i][$r]) : 0;
            $q2 = is_numeric($q2_all[$i][$r] ?? null) ? floatval($q2_all[$i][$r]) : 0;
            $q3 = is_numeric($q3_all[$i][$r] ?? null) ? floatval($q3_all[$i][$r]) : 0;
            $q4 = is_numeric($q4_all[$i][$r] ?? null) ? floatval($q4_all[$i][$r]) : 0;
            if ($q1 || $q2 || $q3 || $q4) {
                $final = round(($q1 + $q2 + $q3 + $q4) / 4, 2);
                $final_ratings_all[$i][$r] = $final;
                $total += $final;
                $count++;
            } else {
                $final_ratings_all[$i][$r] = '';
            }
        }
        $general_averages[$i] = $count ? round($total / $count, 2) : '';
    }

    $remedials_db = [];
    for ($i = 1; $i <= 8; $i++) {
        $areas = $_POST["rem{$i}_area"] ?? [];
        $finals = $_POST["rem{$i}_final"] ?? [];
        $class_marks = $_POST["rem{$i}_class_mark"] ?? [];
        $recomputeds = $_POST["rem{$i}_recomputed"] ?? [];
        $remarks = $_POST["rem{$i}_remarks"] ?? [];


        $remedials_db[$i] = [
            'area' => implode('|', $areas),
            'final' => implode('|', $finals),
            'class_mark' => implode('|', $class_marks),
            'recomputed' => implode('|', $recomputeds),
            'remarks' => implode('|', $remarks),
        ];
    }


    $personal_data = [
        'student_id' => $student_id,
        'last_name' => $last_name,
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'suffix' => $suffix,
        'lrn' => $lrn,
        'birthdate' => $birthdate,
        'sex' => $sex,
        'school_name' => $school_name,
        'school_id' => $school_id,
        'school_address' => $school_address,
        'kinder_progress_report' => !empty($kinder_progress_report) ? 1 : 0,
        'eccd_checklist' => !empty($eccd_checklist) ? 1 : 0,
        'kinder_certificate' => !empty($kinder_certificate) ? 1 : 0,
        'pept_passer' => !empty($pept_passer) ? 1 : 0,
        'pept_text' => $pept_text,
        'exam_date' => $exam_date,
        'others_check' => !empty($others_check) ? 1 : 0,
        'others_text' => $others_text,
        'testing_center_name' => $testing_center_name,
        'testing_center_address' => $testing_center_address,
        'remark' => $remark,
        'scholastic_records' => json_encode([
            'grades' => $grades,
            'sections' => $sections,
            'school_years' => $school_years,
            'advisers' => $advisers,
            'learning_areas' => $learning_areas_all,
            'q1' => $q1_all,
            'q2' => $q2_all,
            'q3' => $q3_all,
            'q4' => $q4_all,
            'final_ratings' => $final_ratings_all,
            'remarks' => $remarks_all,
            'general_averages' => $general_averages


        ])
    ];

    for ($i = 1; $i <= 8; $i++) {
        $personal_data["rem{$i}_area"]        = $remedials_db[$i]['area'] ?? '';
        $personal_data["rem{$i}_final"]       = $remedials_db[$i]['final'] ?? '';
        $personal_data["rem{$i}_class_mark"]  = $remedials_db[$i]['class_mark'] ?? '';
        $personal_data["rem{$i}_recomputed"]  = $remedials_db[$i]['recomputed'] ?? '';
        $personal_data["rem{$i}_remarks"]     = $remedials_db[$i]['remarks'] ?? '';
    }


    $columns = implode(',', array_keys($personal_data));
    $placeholders = implode(',', array_fill(0, count($personal_data), '?'));
    $stmt = $pdo->prepare("INSERT INTO sf10_data ($columns) VALUES ($placeholders)");
    $stmt->execute(array_values($personal_data));


    try {
        $template_path = BASE_PATH . '/src/UI-Admin/contents/sf10/sf10.xlsx';
        $spreadsheet = IOFactory::load($template_path);
        $sheet = $spreadsheet->getSheet(0);
        $sheet_back = $spreadsheet->getSheet(1);



        $sheet->setCellValue('E9', $last_name);
        $sheet->setCellValue('R9', $first_name);
        $sheet->setCellValue('AD9', $suffix);
        $sheet->setCellValue('AQ9', $middle_name);
        $sheet->setCellValueExplicit('J10', $lrn, DataType::TYPE_STRING);
        $sheet->setCellValue('V10', $birthdate);
        $sheet->setCellValue('AT10', $sex);


        $sheet->setCellValue('K14', $kinder_progress_report);
        $sheet->setCellValue('U14', $eccd_checklist);
        $sheet->setCellValue('AE14', $kinder_certificate);
        $sheet->setCellValue('F15', $school_name);
        $sheet->setCellValue('T15', $school_id);
        $sheet->setCellValue('Z15', $school_address);
        $sheet->setCellValue('B18', $pept_passer);
        $sheet->setCellValue('J18', $pept_text);
        $sheet->setCellValue('W18', $exam_date);
        $sheet->setCellValue('AC18', $others_check);
        $sheet->setCellValue('AQ18', $others_text);
        $sheet->setCellValue('L19', $testing_center_name);
        $sheet->setCellValue('M19', $testing_center_address);
        $sheet->setCellValue('AJ19', $remark);


        $scholastic_positions = [
            1 => ['grade' => 'F25', 'section' => 'J25', 'sy' => 'S25', 'adviser' => 'H26', 'start_row' => 30, 'start_col' => 'B', 'q1' => 'K', 'q2' => 'L', 'q3' => 'N', 'q4' => 'O', 'final' => 'P', 'remarks' => 'S', 'gen_avg' => 'S45'],
            2 => ['grade' => 'Z25', 'section' => 'AE25', 'sy' => 'AU25', 'adviser' => 'AC26', 'start_row' => 30, 'start_col' => 'V', 'q1' => 'AJ', 'q2' => 'AM', 'q3' => 'AO', 'q4' => 'AR', 'final' => 'AT', 'remarks' => 'AW', 'gen_avg' => 'AW45'],
            3 => ['grade' => 'F54', 'section' => 'J54', 'sy' => 'S54', 'adviser' => 'H55', 'start_row' => 60, 'start_col' => 'B', 'q1' => 'K', 'q2' => 'L', 'q3' => 'N', 'q4' => 'O', 'final' => 'P', 'remarks' => 'S', 'gen_avg' => 'S75'],
            4 => ['grade' => 'Z54', 'section' => 'AE54', 'sy' => 'AU54', 'adviser' => 'AC55', 'start_row' => 60, 'start_col' => 'V', 'q1' => 'AJ', 'q2' => 'AM', 'q3' => 'AO', 'q4' => 'AR', 'final' => 'AT', 'remarks' => 'AW', 'gen_avg' => 'AW75']
        ];
        $scholastic_positions_back = [
            5 => ['grade' => 'D5', 'section' => 'H5', 'sy' => 'O5', 'adviser' => 'F6', 'start_row' => 10, 'start_col' => 'B', 'q1' => 'H', 'q2' => 'I', 'q3' => 'J', 'q4' => 'K', 'final' => 'L', 'remarks' => 'O', 'gen_avg' => 'O25'],
            6 => ['grade' => 'V5', 'section' => 'AB5', 'sy' => 'AG5', 'adviser' => 'V6', 'start_row' => 10, 'start_col' => 'S', 'q1' => 'AB', 'q2' => 'AD', 'q3' => 'AE', 'q4' => 'AF', 'final' => 'AG', 'remarks' => 'AH', 'gen_avg' => 'AH25'],
            7 => ['grade' => 'E34', 'section' => 'H34', 'sy' => 'O34', 'adviser' => 'F35', 'start_row' => 39, 'start_col' => 'B', 'q1' => 'H', 'q2' => 'I', 'q3' => 'J', 'q4' => 'K', 'final' => 'L', 'remarks' => 'O', 'gen_avg' => 'O54'],
            8 => ['grade' => 'V34', 'section' => 'AA34', 'sy' => 'V35', 'adviser' => 'AC40', 'start_row' => 39, 'start_col' => 'S', 'q1' => 'AB', 'q2' => 'AD', 'q3' => 'AE', 'q4' => 'AF', 'final' => 'AG', 'remarks' => 'AH', 'gen_avg' => 'AH54']
        ];



        for ($i = 1; $i <= 4; $i++) {
            $pos = $scholastic_positions[$i];
            $sheet->setCellValue($pos['grade'], $grades[$i]);
            $sheet->setCellValue($pos['section'], $sections[$i]);
            $sheet->setCellValue($pos['sy'], $school_years[$i]);
            $sheet->setCellValue($pos['adviser'], $advisers[$i]);
            for ($r = 0; $r < 15; $r++) {
                $row = $pos['start_row'] + $r;
                $sheet->setCellValue($pos['start_col'] . $row, $learning_areas_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q1'] . $row, $q1_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q2'] . $row, $q2_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q3'] . $row, $q3_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q4'] . $row, $q4_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['final'] . $row, $final_ratings_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['remarks'] . $row, $remarks_all[$i][$r] ?? '');
            }
            $sheet->setCellValue($pos['gen_avg'], $general_averages[$i]);
        }
        for ($i = 5; $i <= 8; $i++) {
            $pos = $scholastic_positions_back[$i];
            $sheet_back->setCellValue($pos['grade'], $grades[$i]);
            $sheet_back->setCellValue($pos['section'], $sections[$i]);
            $sheet_back->setCellValue($pos['sy'], $school_years[$i]);
            $sheet_back->setCellValue($pos['adviser'], $advisers[$i]);
            for ($r = 0; $r < 15; $r++) {
                $row = $pos['start_row'] + $r;
                $sheet_back->setCellValue($pos['start_col'] . $row, $learning_areas_all[$i][$r] ?? '');
                $sheet_back->setCellValue($pos['q1'] . $row, $q1_all[$i][$r] ?? '');
                $sheet_back->setCellValue($pos['q2'] . $row, $q2_all[$i][$r] ?? '');
                $sheet_back->setCellValue($pos['q3'] . $row, $q3_all[$i][$r] ?? '');
                $sheet_back->setCellValue($pos['q4'] . $row, $q4_all[$i][$r] ?? '');
                $sheet_back->setCellValue($pos['final'] . $row, $final_ratings_all[$i][$r] ?? '');
                $sheet_back->setCellValue($pos['remarks'] . $row, $remarks_all[$i][$r] ?? '');
            }
            $sheet_back->setCellValue($pos['gen_avg'], $general_averages[$i]);
        }

        $remedial_positions_front = [
            1 => ['subject' => ['B49', 'G49', 'K49', 'O49', 'S49'], 'grade' => ['B50', 'G50', 'K50', 'O50', 'S50']],
            2 => ['subject' => ['V49', 'AA49', 'AJ49', 'AQ49', 'AW49'], 'grade' => ['V50', 'AA50', 'AJ50', 'AQ50', 'AW50']],
            3 => ['subject' => ['B79', 'G79', 'K79', 'O79', 'S79'], 'grade' => ['B80', 'G80', 'K80', 'O80', 'S80']],
            4 => ['subject' => ['V79', 'AA79', 'AJ79', 'AQ79', 'AW79'], 'grade' => ['V80', 'AA80', 'AJ80', 'AQ80', 'AW80']]
        ];


        $remedial_positions_back = [
            5 => ['subject' => ['B29', 'F29', 'H29', 'K29', 'O29'], 'grade' => ['B30', 'F30', 'H30', 'K30', 'O30']],
            6 => ['subject' => ['S29', 'W29', 'AC29', 'AF29', 'AH29'], 'grade' => ['S30', 'W30', 'AC30', 'AF30', 'AH30']],
            7 => ['subject' => ['B58', 'F58', 'H58', 'K58', 'O58'], 'grade' => ['B59', 'F59', 'H59', 'K59', 'O59']],
            8 => ['subject' => ['S58', 'W58', 'AC58', 'AF58', 'AH58'], 'grade' => ['S59', 'W59', 'AC59', 'AF59', 'AH59']]
        ];


        for ($i = 1; $i <= 4; $i++) {
            $pos = $remedial_positions_front[$i];
            for ($c = 0; $c < 5; $c++) {
                $sheet->setCellValue($pos['subject'][$c], $learning_areas_all[$i]['remedial_subject'][$c] ?? '');
                $sheet->setCellValue($pos['grade'][$c], $final_ratings_all[$i]['remedial'][$c] ?? '');
            }
        }


        for ($i = 5; $i <= 8; $i++) {
            $pos = $remedial_positions_back[$i];
            for ($c = 0; $c < 5; $c++) {
                $sheet_back->setCellValue($pos['subject'][$c], $learning_areas_all[$i]['remedial_subject'][$c] ?? '');
                $sheet_back->setCellValue($pos['grade'][$c], $final_ratings_all[$i]['remedial'][$c] ?? '');
            }
        }

        $full_name = trim($last_name . ', ' . $first_name . ' ' . $middle_name . ' ' . $suffix);

        $certifications = [
            ['name_cell' => 'H62', 'lrn_cell' => 'S62'],
            ['name_cell' => 'H69', 'lrn_cell' => 'S69'],
            ['name_cell' => 'H76', 'lrn_cell' => 'S76']
        ];

        foreach ($certifications as $cert) {
            $sheet_back->setCellValue($cert['name_cell'], $full_name);
            $sheet_back->setCellValueExplicit($cert['lrn_cell'], $lrn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $drawing = new Drawing();
        $drawing->setName('DepEd Logo');
        $drawing->setDescription('DepEd Logo');
        $drawing->setPath(BASE_PATH . '/assets/image/deped.png');
        $drawing->setCoordinates('A1');
        $drawing->setWidth(80);
        $drawing->setHeight(80);
        $drawing->setWorksheet($sheet);


        $filename = build_sf10_filename($lrn, $first_name, $last_name);
        $savePath = $saveDir . DIRECTORY_SEPARATOR . $filename;
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($savePath);


        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($savePath));
        flush();
        readfile($savePath);
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SF10 Fill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #f4f5f7;
            margin: 0;
            padding: 0;
        }

        .header-brand {
            border-bottom: 1px solid rgba(0, 0, 0, .2);
            height: 75px;
            background: #f5365c;
        }

        .header-brand img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .header-brand h4 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .sidebar,
        .eligibility-container,
        .scholastic-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .sidebar h5,
        .eligibility-container h5,
        .scholastic-container h5 {
            font-weight: 700;
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
            margin-top: 10px;
        }

        .form-control.form-control-sm {
            font-weight: 500;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn-lg {
            padding: 10px 18px;
            font-size: 16px;
            border-radius: 6px;
        }

        .table input {
            width: 100%;
        }

        .remedial-carousel-container {
            position: relative;
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
            transform: translate(-3in, -4in);
        }

        .remedial-wrapper {
            overflow: hidden;
            position: relative;
        }

        .remedial-slide {
            display: none;
            width: 100%;
        }

        .remedial-slide.active {
            display: block;
        }

        .remedial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .remedial-table th,
        .remedial-table td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }

        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: #8c2b2b;
            color: white;
            border: none;
            font-size: 24px;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 50%;
        }

        .arrow.prev {
            left: -40px;
        }

        .arrow.next {
            right: -40px;
        }

        .arrow:hover {
            background: #b03a3a;
        }
    </style>
</head>

<body>
    <div class="d-flex align-items-center justify-content-between col-12 m-0 p-0 header-brand">
        <div class="d-flex align-items-center ps-4">
            <img src="../../../assets/image/logo2.png" alt="Logo">
            <h4>STA.MARIA WEB SYSTEM</h4>
        </div>
    </div>
    <div class="container-fluid p-3">
        <form method="post">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-4 col-sm-12">
                    <div class="sidebar">
                        <h5>Learner's Personal Information</h5>
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control form-control-sm" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? ($student['lname'] ?? ($sf10_data['last_name'] ?? ''))) ?>">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control form-control-sm" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? ($student['fname'] ?? ($sf10_data['first_name'] ?? ''))) ?>">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control form-control-sm" name="middle_name" value="<?= htmlspecialchars($_POST['middle_name'] ?? ($student['mname'] ?? ($sf10_data['middle_name'] ?? ''))) ?>">
                        <label class="form-label">Name Ext.</label>
                        <input type="text" class="form-control form-control-sm" name="suffix" value="<?= htmlspecialchars($_POST['suffix'] ?? ($student['suffix'] ?? ($sf10_data['suffix'] ?? ''))) ?>">
                        <label class="form-label">LRN</label>
                        <input type="text" class="form-control form-control-sm" name="lrn" value="<?= htmlspecialchars($_POST['lrn'] ?? ($student['lrn'] ?? ($sf10_data['lrn'] ?? ''))) ?>">
                        <label class="form-label">Birthdate (MM/DD/YY)</label>
                        <input type="text" class="form-control form-control-sm" name="birthdate" value="<?= htmlspecialchars($_POST['birthdate'] ?? ($student['birthdate'] ?? ($sf10_data['birthdate'] ?? ''))) ?>">
                        <label class="form-label">Sex</label>
                        <input type="text" class="form-control form-control-sm" name="sex" value="<?= htmlspecialchars($_POST['sex'] ?? ($student['sex'] ?? ($sf10_data['sex'] ?? ''))) ?>">
                        <div class="text-center mt-3 d-flex justify-content-center gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary btn-lg">Save and Download</button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="window.history.back();">Back</button>
                        </div>
                    </div>
                </div>


                <div class="col-md-4 col-sm-12">
                    <div class="eligibility-container">
                        <h5>Elementary School Eligibility</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kinder_progress_report" id="kinder_progress_report" value="1" <?= (!empty($_POST['kinder_progress_report']) || (!empty($sf10_data['kinder_progress_report']) && $sf10_data['kinder_progress_report'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kinder_progress_report">Kinder Progress Report</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="eccd_checklist" id="eccd_checklist" value="1" <?= (!empty($_POST['eccd_checklist']) || (!empty($sf10_data['eccd_checklist']) && $sf10_data['eccd_checklist'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="eccd_checklist">ECCD Checklist</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kinder_certificate" id="kinder_certificate" value="1" <?= (!empty($_POST['kinder_certificate']) || (!empty($sf10_data['kinder_certificate']) && $sf10_data['kinder_certificate'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="kinder_certificate">Kindergarten Certificate of Completion</label>
                        </div>
                        <label class="form-label">Name of School</label>
                        <input type="text" class="form-control form-control-sm" name="school_name" value="<?= htmlspecialchars($_POST['school_name'] ?? ($sf10_data['school_name'] ?? '')) ?>">
                        <label class="form-label">School ID</label>
                        <input type="text" class="form-control form-control-sm" name="school_id" value="<?= htmlspecialchars($_POST['school_id'] ?? ($sf10_data['school_id'] ?? '')) ?>">
                        <label class="form-label">Address of School</label>
                        <input type="text" class="form-control form-control-sm" name="school_address" value="<?= htmlspecialchars($_POST['school_address'] ?? ($sf10_data['school_address'] ?? '')) ?>">
                        <h6 class="mt-3">Other Credential Presented</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="pept_passer" id="pept_passer" value="1" <?= (!empty($_POST['pept_passer']) || (!empty($sf10_data['pept_passer']) && $sf10_data['pept_passer'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pept_passer">PEPT Passer Rating</label>
                        </div>
                        <label class="form-label">PEPT Passer Rating (text)</label>
                        <input type="text" class="form-control form-control-sm" name="pept_text" value="<?= htmlspecialchars($_POST['pept_text'] ?? ($sf10_data['pept_text'] ?? '')) ?>">
                        <label class="form-label">Date of Examination/Assessment (dd/mm/yyyy)</label>
                        <input type="text" class="form-control form-control-sm" name="exam_date" value="<?= htmlspecialchars($_POST['exam_date'] ?? ($sf10_data['exam_date'] ?? '')) ?>">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="others_check" id="others_check" value="1" <?= (!empty($_POST['others_check']) || (!empty($sf10_data['others_check']) && $sf10_data['others_check'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="others_check">Others, pls specify</label>
                        </div>
                        <label class="form-label">Others (text)</label>
                        <input type="text" class="form-control form-control-sm" name="others_text" value="<?= htmlspecialchars($_POST['others_text'] ?? ($sf10_data['others_text'] ?? '')) ?>">
                        <label class="form-label">Name of Testing Center</label>
                        <input type="text" class="form-control form-control-sm" name="testing_center_name" value="<?= htmlspecialchars($_POST['testing_center_name'] ?? ($sf10_data['testing_center_name'] ?? '')) ?>">
                        <label class="form-label">Address of Testing Center</label>
                        <input type="text" class="form-control form-control-sm" name="testing_center_address" value="<?= htmlspecialchars($_POST['testing_center_address'] ?? ($sf10_data['testing_center_address'] ?? '')) ?>">
                        <label class="form-label">Remark</label>
                        <input type="text" class="form-control form-control-sm" name="remark" value="<?= htmlspecialchars($_POST['remark'] ?? ($sf10_data['remark'] ?? '')) ?>">
                    </div>
                </div>


                <div class="col-md-4 col-sm-12">
                    <div class="scholastic-container">
                        <h5>Scholastic Records</h5>
                        <ul class="nav nav-tabs mb-3" id="srTabs" role="tablist">
                            <?php for ($i = 1; $i <= 8; $i++): ?>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $i === 1 ? 'active' : '' ?>" id="tab<?= $i ?>" data-bs-toggle="tab" data-bs-target="#sr<?= $i ?>" type="button" role="tab">
                                        Scholastic <?= $i ?>
                                    </button>
                                </li>
                            <?php endfor; ?>
                        </ul>


                        <div class="tab-content">
                            <?php for ($i = 1; $i <= 8; $i++): ?>

                                <div class="tab-pane fade <?= $i === 1 ? 'show active' : '' ?>" id="sr<?= $i ?>" role="tabpanel">
                                    <label class="form-label">Grade</label>
                                    <input type="text" class="form-control form-control-sm" name="grade<?= $i ?>"
                                        value="<?= htmlspecialchars($_POST['grade' . $i] ?? ($scholastic_data['grades'][$i] ?? '')) ?>">

                                    <label class="form-label">Section</label>
                                    <input type="text" class="form-control form-control-sm" name="section<?= $i ?>"
                                        value="<?= htmlspecialchars($_POST['section' . $i] ?? ($scholastic_data['sections'][$i] ?? '')) ?>">

                                    <label class="form-label">School Year</label>
                                    <input type="text" class="form-control form-control-sm" name="school_year<?= $i ?>"
                                        value="<?= htmlspecialchars($_POST['school_year' . $i] ?? ($scholastic_data['school_years'][$i] ?? '')) ?>">

                                    <label class="form-label">Name of Adviser</label>
                                    <input type="text" class="form-control form-control-sm" name="adviser_name<?= $i ?>"
                                        value="<?= htmlspecialchars($_POST['adviser_name' . $i] ?? ($scholastic_data['adviser_name'][$i] ?? '')) ?>">

                                    <h6 class="mt-3">Grades Table</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Learning Area</th>
                                                    <th>1</th>
                                                    <th>2</th>
                                                    <th>3</th>
                                                    <th>4</th>
                                                    <th>Final Rating</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php for ($r = 0; $r < 15; $r++): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="learning_area<?= $i ?>[]"
                                                                value="<?= htmlspecialchars($_POST['learning_area' . $i][$r] ?? ($scholastic_data['learning_areas'][$i][$r] ?? '')) ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="q1_<?= $i ?>[]"
                                                                value="<?= htmlspecialchars($_POST['q1_' . $i][$r] ?? ($scholastic_data['q1'][$i][$r] ?? '')) ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="q2_<?= $i ?>[]"
                                                                value="<?= htmlspecialchars($_POST['q2_' . $i][$r] ?? ($scholastic_data['q2'][$i][$r] ?? '')) ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="q3_<?= $i ?>[]"
                                                                value="<?= htmlspecialchars($_POST['q3_' . $i][$r] ?? ($scholastic_data['q3'][$i][$r] ?? '')) ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="q4_<?= $i ?>[]"
                                                                value="<?= htmlspecialchars($_POST['q4_' . $i][$r] ?? ($scholastic_data['q4'][$i][$r] ?? '')) ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="final_rating_<?= $i ?>[]"
                                                                value="<?= htmlspecialchars($_POST['final_rating_' . $i][$r] ?? ($scholastic_data['final_ratings'][$i][$r] ?? '')) ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="remarks_table_<?= $i ?>[]"
                                                                value="<?= htmlspecialchars($_POST['remarks_table_' . $i][$r] ?? ($scholastic_data['remarks_table'][$i][$r] ?? '')) ?>">
                                                        </td>
                                                    </tr>
                                                <?php endfor; ?>
                                            </tbody>
                                        </table>
                                    </div>



                                    <label class="form-label">General Average</label>
                                    <input type="text" class="form-control form-control-sm" name="general_average_<?= $i ?>" value="<?= htmlspecialchars($_POST['general_average_' . $i] ?? ($scholastic_data['general_average'][$i] ?? '')) ?>">
                                </div>

                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
    <!-- <div class="remedial-carousel-container">
    <button type="button" class="arrow prev" onclick="prevRemedial()">&#10094;</button>
    <div class="remedial-wrapper">
        <?php for ($i = 1; $i <= 8; $i++): ?>
        <div class="remedial-slide <?php echo $i === 1 ? 'active' : ''; ?>" id="remedial-<?php echo $i; ?>">
            <h3>Remedial Class <?php echo $i; ?></h3>  
            <table class="remedial-table">
                <thead>
                    <tr>
                        <th>Learning Areas</th>
                        <th>Final Rating</th>
                        <th>Remedial Class Mark</th>
                        <th>Recomputed Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($c = 0; $c < 2; $c++):  ?>
                    <tr>
                        <td>
                            <input type="text" 
                                   name="rem<?php echo $i; ?>_area[]" 
                                   value="<?php echo htmlspecialchars($learning_areas_all[$i]['remedial_subject'][$c] ?? ''); ?>">
                        </td>
                        <td>
                            <input type="text" 
                                   name="rem<?php echo $i; ?>_final[]" 
                                   value="<?php echo htmlspecialchars($final_ratings_all[$i]['final'][$c] ?? ''); ?>">
                        </td>
                        <td>
                            <input type="text" 
                                   name="rem<?php echo $i; ?>_class_mark[]" 
                                   value="<?php echo htmlspecialchars($final_ratings_all[$i]['remedial'][$c] ?? ''); ?>">
                        </td>
                        <td>
                            <input type="text" 
                                   name="rem<?php echo $i; ?>_recomputed[]" 
                                   value="<?php echo htmlspecialchars($final_ratings_all[$i]['recomputed'][$c] ?? ''); ?>">
                        </td>
                        <td>
                            <input type="text" 
                                   name="rem<?php echo $i; ?>_remarks[]" 
                                   value="<?php echo htmlspecialchars($remarks_all[$i][$c] ?? ''); ?>">
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <?php endfor; ?>
    </div>  -->
    <button type="button" class="arrow next" onclick="nextRemedial()">&#10095;</button>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-body text-center text-success">
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($showSuccess): ?>
        <script>
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            setTimeout(() => {
                successModal.hide();
            }, 2000);
        </script>
    <?php endif; ?>

    <script>
        function recalc(i) {
            let total = 0,
                count = 0;
            let q1s = document.querySelectorAll(`[name='q1_${i}[]']`);
            let q2s = document.querySelectorAll(`[name='q2_${i}[]']`);
            let q3s = document.querySelectorAll(`[name='q3_${i}[]']`);
            let q4s = document.querySelectorAll(`[name='q4_${i}[]']`);
            let finals = document.querySelectorAll(`[name='final_rating_${i}[]']`);
            for (let r = 0; r < 15; r++) {
                let q1 = parseFloat(q1s[r].value) || 0;
                let q2 = parseFloat(q2s[r].value) || 0;
                let q3 = parseFloat(q3s[r].value) || 0;
                let q4 = parseFloat(q4s[r].value) || 0;
                let final = 0;
                if (q1 || q2 || q3 || q4) {
                    final = ((q1 + q2 + q3 + q4) / 4).toFixed(2);
                    total += parseFloat(final);
                    count++;
                }
                finals[r].value = final ? final : '';
            }
            document.querySelector(`[name='general_average_${i}']`).value = count ? (total / count).toFixed(2) : '';
        }

        for (let i = 1; i <= 8; i++) {
            let qInputs = document.querySelectorAll(`[name^='q1_${i}'],[name^='q2_${i}'],[name^='q3_${i}'],[name^='q4_${i}']`);
            qInputs.forEach(input => {
                input.addEventListener('input', () => recalc(i));
            });
        }

        for (let i = 1; i <= 8; i++) recalc(i);

        let currentSlide = 1;
        const totalSlides = 8;

        function showSlide(n) {
            const slides = document.querySelectorAll('.remedial-slide');
            slides.forEach(slide => slide.classList.remove('active'));
            if (n < 1) currentSlide = totalSlides;
            else if (n > totalSlides) currentSlide = 1;
            else currentSlide = n;
            document.getElementById('remedial-' + currentSlide).classList.add('active');
        }

        function nextRemedial() {
            showSlide(currentSlide + 1);
        }

        function prevRemedial() {
            showSlide(currentSlide - 1);
        }
    </script>
</body>

</html>