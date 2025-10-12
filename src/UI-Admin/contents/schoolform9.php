<?php
// part 1 - full backend (save/load/download)
require_once 'C:/xampp/htdocs/sta.MariaSystem/vendor/autoload.php'; // absolute path

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// DATABASE CONNECTION
$pdo = new PDO("mysql:host=localhost;dbname=stamariadb;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// helper to build safe filename from values
function build_sf9_filename($lrn, $first, $last, $grade) {
    $safe_lrn = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$lrn);
    $safe_first = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$first);
    $safe_last = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$last);
    $safe_grade = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$grade);
    $filename = trim($safe_lrn . '_' . $safe_first . '_' . $safe_last . '_' . $safe_grade . '.xlsx', '_');
    return $filename;
}

// Fetch student info (student_id in GET)
$student_id = isset($_GET['student_id']) ? trim($_GET['student_id']) : null;
$student = null;
if ($student_id) {
    $stmt = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
}

// helper to render name like before
function getFullName($person) {
    if (!$person) return "Unknown";
    $first = htmlspecialchars($person['fname'] ?? $person['firstname'] ?? '');
    $middle = htmlspecialchars($person['mname'] ?? $person['middlename'] ?? '');
    $last = htmlspecialchars($person['lname'] ?? $person['lastname'] ?? '');
    $suffix = htmlspecialchars($person['suffix'] ?? '');
    $fullName = trim($first . ' ' . (!empty($middle) ? substr($middle,0,1) . '. ' : '') . $last . ' ' . $suffix);
    return $fullName ?: 'Unknown';
}

// Guardian
$guardian_name = 'N/A';
if (!empty($student['guardian_id'])) {
    $stmt = $pdo->prepare("SELECT firstname, middlename, lastname, suffix FROM users WHERE user_id = ?");
    $stmt->execute([$student['guardian_id']]);
    $guardian = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($guardian) {
        $guardian_arr = [
            'firstname' => $guardian['firstname'] ?? '',
            'middlename' => $guardian['middlename'] ?? '',
            'lastname' => $guardian['lastname'] ?? '',
            'suffix' => $guardian['suffix'] ?? ''
        ];
        $guardian_name = getFullName($guardian_arr);
    }
}

// Student photo path
$default_photo = "assets/image/users.png";
$student_photo_path = $default_photo;
if (!empty($student['student_profile']) && file_exists(__DIR__ . "/assets/image/" . $student['student_profile'])) {
    $student_photo_path = "assets/image/" . $student['student_profile'];
}

// Where files are saved
$saveDir = 'C:/xampp/htdocs/sta.MariaSystem/sf9_files';
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

// Try to load latest existing sf9_data for this student (to prefill form)
$existingSf9 = null;
if ($student_id) {
    // choose latest by created_at for this student
    $q = $pdo->prepare("SELECT * FROM sf9_data WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
    $q->execute([$student_id]);
    $existingSf9 = $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Helper to populate array fields (subjects, q1..q4, remarks, finals, behaviors)
$existing_subjects = array_fill(0, 15, '');
$existing_q1 = array_fill(0, 15, '');
$existing_q2 = array_fill(0, 15, '');
$existing_q3 = array_fill(0, 15, '');
$existing_q4 = array_fill(0, 15, '');
$existing_final = array_fill(0, 15, '');
$existing_remarks = array_fill(0, 15, '');
if ($existingSf9) {
    for ($i = 0; $i < 15; $i++) {
        $idx = $i + 1;
        $existing_subjects[$i] = $existingSf9["subject_{$idx}"] ?? '';
        $existing_q1[$i] = $existingSf9["q1_{$idx}"] ?? '';
        $existing_q2[$i] = $existingSf9["q2_{$idx}"] ?? '';
        $existing_q3[$i] = $existingSf9["q3_{$idx}"] ?? '';
        $existing_q4[$i] = $existingSf9["q4_{$idx}"] ?? '';
        $existing_final[$i] = $existingSf9["final_{$idx}"] ?? '';
        $existing_remarks[$i] = $existingSf9["remarks_{$idx}"] ?? '';
    }
}

// Attendance existing
$months = ['june','july','aug','sep','oct','nov','dec','jan','feb','mar','apr'];
$existing_attendance = [];
foreach ($months as $m) {
    $existing_attendance["days_school_{$m}"] = $existingSf9["days_school_{$m}"] ?? '';
    $existing_attendance["days_present_{$m}"] = $existingSf9["days_present_{$m}"] ?? '';
    $existing_attendance["days_absent_{$m}"] = $existingSf9["days_absent_{$m}"] ?? '';
}

// Behavior existing (7 items)
$existing_behavior = [];
for ($i=0;$i<7;$i++){
    $idx = $i+1;
    $existing_behavior["b{$idx}_q1"] = $existingSf9["b{$idx}_q1"] ?? '';
    $existing_behavior["b{$idx}_q2"] = $existingSf9["b{$idx}_q2"] ?? '';
    $existing_behavior["b{$idx}_q3"] = $existingSf9["b{$idx}_q3"] ?? '';
    $existing_behavior["b{$idx}_q4"] = $existingSf9["b{$idx}_q4"] ?? '';
}

// === DOWNLOAD SF9 FILE AUTOMATICALLY ===
if (isset($_GET['download']) && $_GET['download'] === '1') {
    if (!$student) {
        die("Error: Student not found.");
    }

    // Build safe filename
    $fileName = build_sf9_filename($student['lrn'] ?? '', $student['fname'] ?? '', $student['lname'] ?? '', $student['gradeLevel'] ?? '');
    $filePath = 'C:/xampp/htdocs/sta.MariaSystem/sf9_files/' . $fileName;

    if (!file_exists($filePath)) {
        die("Error: File not found on server. Path: " . htmlspecialchars($filePath));
    }

    // Force download
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    flush();
    readfile($filePath);
    exit;
}

// Flags for UI (POST will set showSuccess or errorMessage)
$showSuccess = false;
$successMessage = '';
$errorMessage = '';

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect input (fall back to existing DB student values if needed)
    $name_input    = $_POST['student_name']    ?? getFullName($student);
    $lrn_input     = $_POST['student_lrn']     ?? ($student['lrn'] ?? '');
    $age_input     = $_POST['student_age']     ?? ($student['age'] ?? '');
    $sex_input     = $_POST['student_sex']     ?? ($student['sex'] ?? '');
    $grade_input   = $_POST['student_grade']   ?? ($student['gradeLevel'] ?? '');
    $section_input = $_POST['student_section'] ?? ($student['section'] ?? '');
    $sy_input      = $_POST['student_sy']      ?? '';
    $teacher_input = $_POST['student_teacher'] ?? '';

    // Load template and fill spreadsheet
    $template_path = 'C:/xampp/htdocs/sta.MariaSystem/src/UI-Admin/contents/sf9/sf9.xlsx';
    $spreadsheet = IOFactory::load($template_path);

    $sheet = $spreadsheet->getSheetByName('Sheet1');
    if (!$sheet) $sheet = $spreadsheet->getSheet(0);

    $dataStyle = [
        'font' => ['underline' => true, 'name' => 'Arial', 'size' => 11],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
    ];

    // Student info in sheet
    $sheet->setCellValue('Q22', $name_input)->getStyle('Q22')->applyFromArray($dataStyle);
    $sheet->setCellValueExplicit('S24', $lrn_input, DataType::TYPE_STRING)
          ->getStyle('S24')->applyFromArray(['alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]]);
    $sheet->setCellValue('Q26', $age_input)->getStyle('Q26')->applyFromArray($dataStyle);
    $sheet->setCellValue('T26', $sex_input)->getStyle('T26')->applyFromArray($dataStyle);
    $sheet->setCellValue('Q28', $grade_input)->getStyle('Q28')->applyFromArray($dataStyle);
    $sheet->setCellValue('T28', $section_input)->getStyle('T28')->applyFromArray($dataStyle);
    $sheet->setCellValue('R30', $sy_input)->getStyle('R30')->applyFromArray($dataStyle);
    $sheet->setCellValue('S40', $teacher_input)->getStyle('S40')
          ->applyFromArray(['alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER]]);

    // Attendance records (Front)
    foreach ($months as $i => $month) {
        $col = chr(66 + $i); // B=66
        $sheet->setCellValue($col.'7', $_POST['days_school_'.$month] ?? '');
        $sheet->setCellValue($col.'9', $_POST['days_present_'.$month] ?? '');
        $sheet->setCellValue($col.'12', $_POST['days_absent_'.$month] ?? '');
    }

    // Totals (Front)
    $sheet->setCellValue('M7', '=SUM(B7:L7)');
    $sheet->setCellValue('M9', '=SUM(B9:L9)');
    $sheet->setCellValue('M12', '=SUM(B12:L12)');

    // Add logo (Front) - absolute path
    $drawing = new Drawing();
    $drawing->setName('DepEd Logo');
    $drawing->setDescription('DepEd Logo');
    $drawing->setPath('C:/xampp/htdocs/sta.MariaSystem/assets/image/deped.png');
    $drawing->setCoordinates('P5');
    $drawing->setWidth(80);
    $drawing->setHeight(80);
    $drawing->setWorksheet($sheet);

    // BACK SHEET (Grades + Behavior)
    $backSheet = $spreadsheet->getSheetByName('back');
    if ($backSheet) {
        // Headers (Back)
        $backSheet->setCellValue('A6', 'Learning Areas');
        $backSheet->setCellValue('N6', '1st Quarter');
        $backSheet->setCellValue('O6', '2nd Quarter');
        $backSheet->setCellValue('P6', '3rd Quarter');
        $backSheet->setCellValue('Q6', '4th Quarter');
        $backSheet->setCellValue('R6', 'Final Rating');
        $backSheet->setCellValue('S6', 'Remarks');

        // Grades rows (A7..A21 and N..Q)
        for ($row = 7; $row <= 21; $row++) {
            $i = $row - 7;
            $backSheet->setCellValue("A{$row}", $_POST['subject'][$i] ?? '');
            $backSheet->setCellValue("N{$row}", $_POST['q1'][$i] ?? '');
            $backSheet->setCellValue("O{$row}", $_POST['q2'][$i] ?? '');
            $backSheet->setCellValue("P{$row}", $_POST['q3'][$i] ?? '');
            $backSheet->setCellValue("Q{$row}", $_POST['q4'][$i] ?? '');
            // Final Rating: keep as Excel formula
            $backSheet->setCellValue("R{$row}", "=IF(A{$row}<>\"\", AVERAGE(N{$row}:Q{$row}), \"\")");
            $backSheet->setCellValue("S{$row}", $_POST['remarks'][$i] ?? '');
        }

        // General Average label & formula
        $backSheet->setCellValue('Q22', 'General Average:');
        $backSheet->setCellValue('R22', '=IFERROR(AVERAGEIF(A7:A21,"<>",R7:R21),"")');

        // Behavior mapping rows -> columns Y, Z, AA, AB
        $behaviorRows = [7,10,13,16,18,20,22];
        foreach ($behaviorRows as $idx => $row) {
            $val1 = $_POST['behavior_q1'][$idx] ?? '';
            $val2 = $_POST['behavior_q2'][$idx] ?? '';
            $val3 = $_POST['behavior_q3'][$idx] ?? '';
            $val4 = $_POST['behavior_q4'][$idx] ?? '';

            $backSheet->setCellValue("Y{$row}", $val1);
            $backSheet->setCellValue("Z{$row}", $val2);
            $backSheet->setCellValue("AA{$row}", $val3);
            $backSheet->setCellValue("AB{$row}", $val4);
        }
    }

    // Build filename and save path
    $fname_first = $student['fname'] ?? '';
    $fname_last  = $student['lname'] ?? '';
    $filename = build_sf9_filename($lrn_input, $fname_first, $fname_last, $grade_input);
    $savePath = $saveDir . DIRECTORY_SEPARATOR . $filename;

    // Prepare data array for sf9_data
    $data = [
        'student_id' => $student_id,
        'student_name' => $name_input,
        'lrn' => $lrn_input,
        'age' => ($age_input === '' ? null : $age_input),
        'sex' => $sex_input,
        'grade' => $grade_input,
        'section' => $section_input,
        'school_year' => $sy_input,
        'teacher' => $teacher_input,
        'guardian' => $guardian_name
    ];

    // Attendance fields
    foreach ($months as $m) {
        $data["days_school_{$m}"] = isset($_POST["days_school_{$m}"]) && $_POST["days_school_{$m}"] !== '' ? (int)$_POST["days_school_{$m}"] : 0;
        $data["days_present_{$m}"] = isset($_POST["days_present_{$m}"]) && $_POST["days_present_{$m}"] !== '' ? (int)$_POST["days_present_{$m}"] : 0;
        $data["days_absent_{$m}"] = isset($_POST["days_absent_{$m}"]) && $_POST["days_absent_{$m}"] !== '' ? (int)$_POST["days_absent_{$m}"] : 0;
    }

    // Grades: 1..15
    $subjects = $_POST['subject'] ?? [];
    $q1 = $_POST['q1'] ?? [];
    $q2 = $_POST['q2'] ?? [];
    $q3 = $_POST['q3'] ?? [];
    $q4 = $_POST['q4'] ?? [];
    $finals = $_POST['final'] ?? [];
    $remarks = $_POST['remarks'] ?? [];

    for ($i = 0; $i < 15; $i++) {
        $idx = $i + 1;
        $data["subject_{$idx}"] = isset($subjects[$i]) && $subjects[$i] !== '' ? $subjects[$i] : null;
        $data["q1_{$idx}"] = isset($q1[$i]) && $q1[$i] !== '' ? (float)$q1[$i] : null;
        $data["q2_{$idx}"] = isset($q2[$i]) && $q2[$i] !== '' ? (float)$q2[$i] : null;
        $data["q3_{$idx}"] = isset($q3[$i]) && $q3[$i] !== '' ? (float)$q3[$i] : null;
        $data["q4_{$idx}"] = isset($q4[$i]) && $q4[$i] !== '' ? (float)$q4[$i] : null;
        $data["final_{$idx}"] = isset($finals[$i]) && $finals[$i] !== '' ? (float)$finals[$i] : null;
        $data["remarks_{$idx}"] = isset($remarks[$i]) && $remarks[$i] !== '' ? $remarks[$i] : null;
    }

    // General average
    $data['general_average'] = isset($_POST['general_average']) && $_POST['general_average'] !== '' ? (float)$_POST['general_average'] : null;

    // Behavior mapping (7 items)
    $behavior_texts = [
        "Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.",
        "Shows adherence to ethical principles by upholding truth in all undertakings.",
        "Is sensitive to individual, social, and cultural differences.",
        "Demonstrates contributions towards solidarity.",
        "Cares for environment and utilizes resources wisely, judiciously and economically.",
        "Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.",
        "Demonstrates appropriate behavior in carrying out activities in school, community and country."
    ];

    $bq1 = $_POST['behavior_q1'] ?? [];
    $bq2 = $_POST['behavior_q2'] ?? [];
    $bq3 = $_POST['behavior_q3'] ?? [];
    $bq4 = $_POST['behavior_q4'] ?? [];

    for ($i = 0; $i < 7; $i++) {
        $idx = $i + 1;
        $data["behavior_{$idx}"] = $behavior_texts[$i];
        $data["b{$idx}_q1"] = $bq1[$i] ?? null;
        $data["b{$idx}_q2"] = $bq2[$i] ?? null;
        $data["b{$idx}_q3"] = $bq3[$i] ?? null;
        $data["b{$idx}_q4"] = $bq4[$i] ?? null;
    }

    // Insert or update: attempt to find existing row for same student_id & grade
    try {
        $existingId = null;
        if (!empty($student_id) && $grade_input !== '') {
            $checkStmt = $pdo->prepare("SELECT id FROM sf9_data WHERE student_id = ? AND grade = ? LIMIT 1");
            $checkStmt->execute([$student_id, $grade_input]);
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($row) $existingId = $row['id'];
        }

        if ($existingId) {
            // UPDATE
            $setParts = [];
            foreach ($data as $col => $val) {
                $setParts[] = "`{$col}` = :{$col}";
            }
            $sql = "UPDATE sf9_data SET " . implode(',', $setParts) . ", created_at = NOW() WHERE id = :existing_id";
            $stmt = $pdo->prepare($sql);
            foreach ($data as $k => $v) {
                if (is_int($v)) {
                    $stmt->bindValue(':' . $k, $v, PDO::PARAM_INT);
                } elseif (is_null($v)) {
                    $stmt->bindValue(':' . $k, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':' . $k, $v, PDO::PARAM_STR);
                }
            }
            $stmt->bindValue(':existing_id', $existingId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // INSERT
            $columns = array_keys($data);
            $placeholders = array_map(function($c){ return ':' . $c; }, $columns);
            $sql = "INSERT INTO sf9_data (" . implode(',', $columns) . ", created_at)
                    VALUES (" . implode(',', $placeholders) . ", NOW())";
            $stmt = $pdo->prepare($sql);
            foreach ($data as $k => $v) {
                if (is_int($v)) {
                    $stmt->bindValue(':' . $k, $v, PDO::PARAM_INT);
                } elseif (is_null($v)) {
                    $stmt->bindValue(':' . $k, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':' . $k, $v, PDO::PARAM_STR);
                }
            }
            $stmt->execute();
        }

        // Save Excel file to disk
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($savePath);

        $showSuccess = true;
        $successMessage = "SF9 saved and file created: {$filename}";

        // After successful save, refresh $existingSf9 so page shows saved values (so user sees persisted data)
        $q = $pdo->prepare("SELECT * FROM sf9_data WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
        $q->execute([$student_id]);
        $existingSf9 = $q->fetch(PDO::FETCH_ASSOC) ?: null;

        // repopulate arrays from fresh DB row
        if ($existingSf9) {
            for ($i = 0; $i < 15; $i++) {
                $idx = $i + 1;
                $existing_subjects[$i] = $existingSf9["subject_{$idx}"] ?? '';
                $existing_q1[$i] = $existingSf9["q1_{$idx}"] ?? '';
                $existing_q2[$i] = $existingSf9["q2_{$idx}"] ?? '';
                $existing_q3[$i] = $existingSf9["q3_{$idx}"] ?? '';
                $existing_q4[$i] = $existingSf9["q4_{$idx}"] ?? '';
                $existing_final[$i] = $existingSf9["final_{$idx}"] ?? '';
                $existing_remarks[$i] = $existingSf9["remarks_{$idx}"] ?? '';
            }
            foreach ($months as $m) {
                $existing_attendance["days_school_{$m}"] = $existingSf9["days_school_{$m}"] ?? '';
                $existing_attendance["days_present_{$m}"] = $existingSf9["days_present_{$m}"] ?? '';
                $existing_attendance["days_absent_{$m}"] = $existingSf9["days_absent_{$m}"] ?? '';
            }
            for ($i=0;$i<7;$i++){
                $idx = $i+1;
                $existing_behavior["b{$idx}_q1"] = $existingSf9["b{$idx}_q1"] ?? '';
                $existing_behavior["b{$idx}_q2"] = $existingSf9["b{$idx}_q2"] ?? '';
                $existing_behavior["b{$idx}_q3"] = $existingSf9["b{$idx}_q3"] ?? '';
                $existing_behavior["b{$idx}_q4"] = $existingSf9["b{$idx}_q4"] ?? '';
            }
        }

    } catch (Exception $e) {
        $errorMessage = "Save failed: " . $e->getMessage();
        // still attempt to save file
        try {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($savePath);
            $errorMessage .= " — Excel saved to {$savePath}";
        } catch (Exception $e2) {
            $errorMessage .= " — Also failed to save Excel: " . $e2->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SF9 Fill</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  body { margin:0; padding:0; font-family: 'Poppins', Arial, sans-serif; background:#f4f5f7; }
  .sidebar { background:#fff; padding:10px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
  .sidebar img { border-radius:6px; width:96px; height:116px; object-fit:cover; display:block; margin:auto; }
  .sidebar label { font-size:13px; margin-top:6px; font-weight:600; color:#222; display:block; }
  .section-title { font-weight:700; font-size:1.12rem; color:#111; margin-bottom:8px; }
  .table th, .table td { font-size:13px; font-weight:600; color:#222; vertical-align:middle; }
  .table-sm input.form-control, .table-sm select.form-select { height:32px; padding:3px; font-size:13px; text-align:center; }
  .form-control.form-control-sm, .form-select.form-select-sm { font-weight:600; }
  .table-attendance input, .table-grades input { width:72px; height:32px; text-align:center; }
  .table-behavior select { width:72px; height:32px; text-align:center; }
  .table-behavior td:first-child { text-align:left; font-weight:600; font-size:13px; max-width:220px; }
  .btn-lg { padding:10px 18px; font-size:16px; }
  .header-brand { border-bottom: solid 1px rgba(0,0,0,.2); height: 75px; }
  @media (max-width: 767px) {
    .sidebar img { width:86px; height:104px; }
  }
</style>
</head>
<body>

<!-- HEADER -->
<div class="text-white d-flex align-items-center justify-content-between col-12 m-0 p-0 bg-danger header-brand">
  <div class="d-flex align-items-center ps-4">
    <img src="/sta.MariaSystem/assets/image/logo2.png" alt="Logo"
         style="width: 65px; height: 65px; border-radius: 50%; margin-right: 15px; object-fit: cover;">
    <h4 class="card-title text-white m-0 fw-bold" style="font-size: 1.3rem;">STA.MARIA WEB SYSTEM</h4>
  </div>
  <div class="pe-4 text-white">
    <!-- optional right area -->
  </div>
</div>

<!-- PAGE -->
<div class="container-fluid p-3">
  <form method="post" class="row g-0">

    <!-- Narrow sidebar with student info -->
    <div class="col-md-2 col-sm-12 p-2">
      <div class="sidebar">
       
        <div class="mt-2">
          <label>Name</label>
          <input type="text" class="form-control form-control-sm mb-1" name="student_name"
                 value="<?= htmlspecialchars($_POST['student_name'] ?? getFullName($student)) ?>">
          <label>LRN</label>
          <input type="text" class="form-control form-control-sm mb-1" name="student_lrn"
                 value="<?= htmlspecialchars($_POST['student_lrn'] ?? ($student['lrn'] ?? '')) ?>">
          <label>Age</label>
          <input type="text" class="form-control form-control-sm mb-1" name="student_age"
                 value="<?= htmlspecialchars($_POST['student_age'] ?? ($existingSf9['age'] ?? $student['age'] ?? '')) ?>">
          <label>Sex</label>
          <input type="text" class="form-control form-control-sm mb-1" name="student_sex"
                 value="<?= htmlspecialchars($_POST['student_sex'] ?? ($existingSf9['sex'] ?? $student['sex'] ?? '')) ?>">
          <label>Grade</label>
          <input type="text" class="form-control form-control-sm mb-1" name="student_grade"
                 value="<?= htmlspecialchars($_POST['student_grade'] ?? ($existingSf9['grade'] ?? $student['gradeLevel'] ?? '')) ?>">
          <label>Section</label>
          <input type="text" class="form-control form-control-sm mb-1" name="student_section"
                 value="<?= htmlspecialchars($_POST['student_section'] ?? ($existingSf9['section'] ?? $student['section'] ?? '')) ?>">
          <label>School Year</label>
          <input type="text" class="form-control form-control-sm mb-1" id="student_sy" name="student_sy"
                 value="<?= htmlspecialchars($_POST['student_sy'] ?? ($existingSf9['school_year'] ?? '')) ?>">
          <label>Teacher</label>
          <input type="text" class="form-control form-control-sm mb-1" id="student_teacher" name="student_teacher"
                 value="<?= htmlspecialchars($_POST['student_teacher'] ?? ($existingSf9['teacher'] ?? '')) ?>">
          <label>Guardian</label>
          <input type="text" class="form-control form-control-sm mb-1" value="<?= htmlspecialchars($guardian_name) ?>" readonly>
        </div>

        <div class="text-center mt-2 mb-4 d-flex justify-content-center gap-2">
          <!-- Generate SF9 -->
          <button type="submit" class="btn btn-primary btn-lg">Save SF9</button>

          <!-- Download SF9 (same script download action) -->
          <?php if ($student): 
                $downloadUrl = htmlspecialchars($_SERVER['PHP_SELF']) . '?student_id=' . urlencode($student_id) . '&download=1';
          ?>
            <a href="<?= $downloadUrl ?>" class="btn btn-success btn-lg">Download SF9</a>
          <?php else: ?>
            <a href="#" class="btn btn-success btn-lg disabled" title="No student selected">Download SF9</a>
          <?php endif; ?>
        </div>
<a href="javascript:history.back()" class="btn btn-secondary btn-lg">Back</a>

      </div>
    </div>

    <!-- Main content -->
    <div class="col-md-10 col-sm-12 p-3">

      <!-- Attendance -->
      <div class="bg-white p-3 rounded shadow-sm mb-3">
        <div class="section-title">Attendance Record</div>
        <table class="table table-bordered table-sm table-attendance text-center align-middle">
          <thead class="table-light">
            <tr>
              <th></th>
              <th>June</th><th>July</th><th>Aug</th><th>Sep</th><th>Oct</th>
              <th>Nov</th><th>Dec</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="fw-semibold text-dark">No. of School Days</td>
              <?php foreach ($months as $m): 
                 $val = $_POST["days_school_{$m}"] ?? ($existing_attendance["days_school_{$m}"] ?? '');
              ?>
                <td><input type="number" name="days_school_<?= $m ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($val) ?>"></td>
              <?php endforeach; ?>
              <td>-</td>
            </tr>
            <tr>
              <td class="fw-semibold text-dark">No. of Days Present</td>
              <?php foreach ($months as $m):
                 $val = $_POST["days_present_{$m}"] ?? ($existing_attendance["days_present_{$m}"] ?? '');
              ?>
                <td><input type="number" name="days_present_<?= $m ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($val) ?>"></td>
              <?php endforeach; ?>
              <td>-</td>
            </tr>
            <tr>
              <td class="fw-semibold text-dark">No. of Days Absent</td>
              <?php foreach ($months as $m):
                 $val = $_POST["days_absent_{$m}"] ?? ($existing_attendance["days_absent_{$m}"] ?? '');
              ?>
                <td><input type="number" name="days_absent_<?= $m ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($val) ?>" readonly></td>
              <?php endforeach; ?>
              <td>-</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Grades -->
      <div class="bg-white p-3 rounded shadow-sm mb-3">
        <div class="section-title">Grades</div>
        <table class="table table-bordered table-sm table-grades text-center align-middle">
          <thead class="table-light">
            <tr>
              <th>Learning Area</th>
              <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
              <th>Final Rating</th><th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<15;$i++):
                $subject_val = $_POST['subject'][$i] ?? ($existing_subjects[$i] ?? '');
                $q1_val = $_POST['q1'][$i] ?? ($existing_q1[$i] ?? '');
                $q2_val = $_POST['q2'][$i] ?? ($existing_q2[$i] ?? '');
                $q3_val = $_POST['q3'][$i] ?? ($existing_q3[$i] ?? '');
                $q4_val = $_POST['q4'][$i] ?? ($existing_q4[$i] ?? '');
                $final_val = $_POST['final'][$i] ?? ($existing_final[$i] ?? '');
                $remarks_val = $_POST['remarks'][$i] ?? ($existing_remarks[$i] ?? '');
            ?>
            <tr>
              <td><input type="text" name="subject[]" class="form-control form-control-sm" value="<?= htmlspecialchars($subject_val) ?>"></td>
              <td><input type="number" name="q1[]" class="q form-control form-control-sm" value="<?= htmlspecialchars($q1_val) ?>"></td>
              <td><input type="number" name="q2[]" class="q form-control form-control-sm" value="<?= htmlspecialchars($q2_val) ?>"></td>
              <td><input type="number" name="q3[]" class="q form-control form-control-sm" value="<?= htmlspecialchars($q3_val) ?>"></td>
              <td><input type="number" name="q4[]" class="q form-control form-control-sm" value="<?= htmlspecialchars($q4_val) ?>"></td>
              <td><input type="text" name="final[]" class="final form-control form-control-sm" readonly value="<?= htmlspecialchars($final_val) ?>"></td>
              <td><input type="text" name="remarks[]" class="remarks form-control form-control-sm" readonly value="<?= htmlspecialchars($remarks_val) ?>"></td>
            </tr>
            <?php endfor; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" class="text-end fw-bold">General Average</td>
              <td><input type="text" id="general_average" name="general_average" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($_POST['general_average'] ?? ($existingSf9['general_average'] ?? '')) ?>"></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Behavior -->
      <div class="bg-white p-3 rounded shadow-sm mb-3">
        <div class="section-title">Behavior</div>
        <table class="table table-bordered table-sm table-behavior text-center align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:18%;">Core Value</th>
              <th style="width:52%;">Behavior Statement</th>
              <th style="width:10%;">Q1</th>
              <th style="width:10%;">Q2</th>
              <th style="width:10%;">Q3</th>
              <th style="width:10%;">Q4</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $core_values = [
                "Maka-Diyos" => [
                  "Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.",
                  "Shows adherence to ethical principles by upholding truth in all undertakings."
                ],
                "Makatao" => [
                  "Is sensitive to individual, social, and cultural differences.",
                  "Demonstrates contributions towards solidarity."
                ],
                "Makakalikasan" => [
                  "Cares for environment and utilizes resources wisely, judiciously and economically."
                ],
                "Makabansa" => [
                  "Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.",
                  "Demonstrates appropriate behavior in carrying out activities in school, community and country."
                ]
              ];
              // behavior rows mapping order for saving is 7 items -> we saved earlier in order b1..b7
              $behIndex = 0;
              foreach ($core_values as $core => $behaviors):
                $rowspan = count($behaviors);
                foreach ($behaviors as $i => $behavior):
                  // present values come from POST or existing_behavior
                  $bq1_val = $_POST['behavior_q1'][$behIndex] ?? ($existing_behavior["b".($behIndex+1)."_q1"] ?? '');
                  $bq2_val = $_POST['behavior_q2'][$behIndex] ?? ($existing_behavior["b".($behIndex+1)."_q2"] ?? '');
                  $bq3_val = $_POST['behavior_q3'][$behIndex] ?? ($existing_behavior["b".($behIndex+1)."_q3"] ?? '');
                  $bq4_val = $_POST['behavior_q4'][$behIndex] ?? ($existing_behavior["b".($behIndex+1)."_q4"] ?? '');
            ?>
              <tr>
                <?php if ($i == 0): ?>
                  <td rowspan="<?= $rowspan ?>" class="fw-bold align-middle"><?= htmlspecialchars($core) ?></td>
                <?php endif; ?>
                <td class="text-start"><?= htmlspecialchars($behavior) ?></td>
                <td>
                  <select name="behavior_q1[]" class="form-select form-select-sm">
                    <option value=""></option>
                    <option value="AO" <?= $bq1_val === 'AO' ? 'selected' : '' ?>>AO</option>
                    <option value="SO" <?= $bq1_val === 'SO' ? 'selected' : '' ?>>SO</option>
                    <option value="RO" <?= $bq1_val === 'RO' ? 'selected' : '' ?>>RO</option>
                    <option value="NO" <?= $bq1_val === 'NO' ? 'selected' : '' ?>>NO</option>
                  </select>
                </td>
                <td>
                  <select name="behavior_q2[]" class="form-select form-select-sm">
                    <option value=""></option>
                    <option value="AO" <?= $bq2_val === 'AO' ? 'selected' : '' ?>>AO</option>
                    <option value="SO" <?= $bq2_val === 'SO' ? 'selected' : '' ?>>SO</option>
                    <option value="RO" <?= $bq2_val === 'RO' ? 'selected' : '' ?>>RO</option>
                    <option value="NO" <?= $bq2_val === 'NO' ? 'selected' : '' ?>>NO</option>
                  </select>
                </td>
                <td>
                  <select name="behavior_q3[]" class="form-select form-select-sm">
                    <option value=""></option>
                    <option value="AO" <?= $bq3_val === 'AO' ? 'selected' : '' ?>>AO</option>
                    <option value="SO" <?= $bq3_val === 'SO' ? 'selected' : '' ?>>SO</option>
                    <option value="RO" <?= $bq3_val === 'RO' ? 'selected' : '' ?>>RO</option>
                    <option value="NO" <?= $bq3_val === 'NO' ? 'selected' : '' ?>>NO</option>
                  </select>
                </td>
                <td>
                  <select name="behavior_q4[]" class="form-select form-select-sm">
                    <option value=""></option>
                    <option value="AO" <?= $bq4_val === 'AO' ? 'selected' : '' ?>>AO</option>
                    <option value="SO" <?= $bq4_val === 'SO' ? 'selected' : '' ?>>SO</option>
                    <option value="RO" <?= $bq4_val === 'RO' ? 'selected' : '' ?>>RO</option>
                    <option value="NO" <?= $bq4_val === 'NO' ? 'selected' : '' ?>>NO</option>
                  </select>
                </td>
              </tr>
            <?php
                $behIndex++;
                endforeach;
              endforeach;
            ?>
          </tbody>
        </table>
      </div>

    </div> <!-- end main content -->

  </form>
</div> <!-- end container -->


<!-- SUCCESS / ERROR MODAL -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <h5 id="modalTitle" class="text-success mb-2"><?= $showSuccess ? 'SF9 Saved' : 'Info' ?></h5>
      <p class="mb-0"><?= htmlspecialchars($successMessage ?: $errorMessage ?: '') ?></p>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Auto-calc "Absent" = SchoolDays - Present
  (function(){
    const schoolInputs = Array.from(document.querySelectorAll("input[name^='days_school_']"));
    const presentInputs = Array.from(document.querySelectorAll("input[name^='days_present_']"));
    const absentInputs = Array.from(document.querySelectorAll("input[name^='days_absent_']"));

    function updateAbsent(idx){
      const s = parseInt(schoolInputs[idx]?.value) || 0;
      const p = parseInt(presentInputs[idx]?.value) || 0;
      if (absentInputs[idx]) absentInputs[idx].value = Math.max(0, s - p);
    }

    schoolInputs.forEach((el, idx) => el.addEventListener('input', () => updateAbsent(idx)));
    presentInputs.forEach((el, idx) => el.addEventListener('input', () => updateAbsent(idx)));

    // initialize absent fields on page load
    document.addEventListener('DOMContentLoaded', function(){ 
      for (let i=0;i<schoolInputs.length;i++) updateAbsent(i);
    });
  })();

  // Auto-calc Final Rating and General Average (grades only)
  (function(){
    function computeGradesRow(row){
      const qEls = row.querySelectorAll("input.q");
      if (!qEls || qEls.length === 0) return null;
      const vals = Array.from(qEls).map(i => parseFloat(i.value) || 0);
      const filled = vals.filter(v => v > 0).length;
      const finalInput = row.querySelector("input.final");
      const remarksInput = row.querySelector("input.remarks");
      if (filled === 4) {
        const avg = (vals[0] + vals[1] + vals[2] + vals[3]) / 4;
        finalInput.value = avg.toFixed(2);
        remarksInput.value = (avg >= 75) ? "PASSED" : "FAILED";
        return avg;
      } else {
        finalInput.value = "";
        if (remarksInput) remarksInput.value = "";
        return null;
      }
    }

    function computeAllGrades(){
      const rows = document.querySelectorAll(".table-grades tbody tr");
      let total = 0, count = 0;
      rows.forEach(r => {
        const val = computeGradesRow(r);
        if (val !== null) { total += val; count++; }
      });
      document.getElementById("general_average").value = count > 0 ? (total / count).toFixed(2) : "";
    }

    document.querySelectorAll(".table-grades input.q").forEach(i => i.addEventListener("input", computeAllGrades));
    // initial compute on load
    document.addEventListener('DOMContentLoaded', computeAllGrades);
  })();

  // show success/error modal if PHP set flags
  <?php if ($showSuccess || !empty($errorMessage)): ?>
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('successModal');
    const bsModal = new bootstrap.Modal(modalEl);
    // change title color for error
    <?php if (!empty($errorMessage)): ?>
      document.getElementById('modalTitle').classList.remove('text-success');
      document.getElementById('modalTitle').classList.add('text-danger');
    <?php endif; ?>
    bsModal.show();
    <?php if ($showSuccess): ?>
      setTimeout(function(){
        bsModal.hide();
        // reload page with student_id param so form is ready again (preserve student)
        const params = new URLSearchParams(window.location.search);
        <?php if (!empty($student_id)): ?>
          window.location.href = window.location.pathname + '?student_id=' + <?= json_encode($student_id) ?>;
        <?php else: ?>
          window.location.reload();
        <?php endif; ?>
      }, 2000);
    <?php endif; ?>
  });
  <?php endif; ?>
</script>

</body>
</html>

