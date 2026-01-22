<?php

require_once __DIR__ . '/../../../tupperware.php';
require_once __DIR__ . '/../../../authentication/config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$result = checkURI('teacher', 2);

if ($result['res']) {
  header($result['uri']);
  exit;
}
if (!isset($showSuccess)) $showSuccess = false;
if (!isset($successMessage)) $successMessage = '';

$pdo = db_connect();

// Function to generate Excel file for SF10
function generateSF10Excel($pdo, $student_id, $save_directory = null)
{
  try {
    // Get SF10 data
    $stmt = $pdo->prepare("SELECT * FROM sf10_data WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$student_id]);
    $sf10_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sf10_data) {
      throw new Exception('No SF10 data found for this student');
    }

    // Get student info
    $stmt_student = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
    $stmt_student->execute([$student_id]);
    $student = $stmt_student->fetch(PDO::FETCH_ASSOC);

    // Get SF9 records to retrieve scholastic data
    $stmt_sf9 = $pdo->prepare("SELECT * FROM sf9_data WHERE student_id = ? ORDER BY CAST(SUBSTRING_INDEX(school_year, '-', 1) AS UNSIGNED) DESC");
    $stmt_sf9->execute([$student_id]);
    $sf9_records = $stmt_sf9->fetchAll(PDO::FETCH_ASSOC);

    $num_subjects = 15;
    $num_scholastic_records = count($sf9_records) > 0 ? count($sf9_records) : 1;

    // Build scholastic data arrays
    $scholastic_data = [];
    foreach ($sf9_records as $idx => $sf9_record) {
      $scholastic_index = $idx + 1;
      $scholastic_data[$scholastic_index] = [
        'school' => $sf9_record['school'] ?? '',
        'district' => $sf9_record['district'] ?? '',
        'division' => $sf9_record['division'] ?? '',
        'school_id' => $sf9_record['school_id'] ?? '',
        'region' => $sf9_record['region'] ?? '',
        'grades' => $sf9_record['grade'] ?? '',
        'sections' => $sf9_record['section'] ?? '',
        'school_years' => $sf9_record['school_year'] ?? '',
        'adviser_name' => $sf9_record['teacher'] ?? '',
        'general_average' => $sf9_record['general_average'] ?? '',
        'learning_areas' => [],
        'q1' => [],
        'q2' => [],
        'q3' => [],
        'q4' => [],
        'final_ratings' => [],
        'remarks' => []
      ];

      for ($r = 1; $r <= $num_subjects; $r++) {
        $scholastic_data[$scholastic_index]['learning_areas'][] = $sf9_record["subject_{$r}"] ?? '';
        $scholastic_data[$scholastic_index]['q1'][] = $sf9_record["q1_{$r}"] ?? '';
        $scholastic_data[$scholastic_index]['q2'][] = $sf9_record["q2_{$r}"] ?? '';
        $scholastic_data[$scholastic_index]['q3'][] = $sf9_record["q3_{$r}"] ?? '';
        $scholastic_data[$scholastic_index]['q4'][] = $sf9_record["q4_{$r}"] ?? '';
        $scholastic_data[$scholastic_index]['final_ratings'][] = $sf9_record["final_{$r}"] ?? '';
        $scholastic_data[$scholastic_index]['remarks'][] = $sf9_record["remarks_{$r}"] ?? '';
      }
    }

    // Load Excel template
    $template_path = BASE_PATH . '/src/UI-Admin/contents/sf10/sf10.xlsx';
    if (!file_exists($template_path)) {
      throw new Exception('SF10 template file not found at: ' . $template_path);
    }

    $spreadsheet = IOFactory::load($template_path);
    $sheet = $spreadsheet->getSheet(0);
    $sheet_back = $spreadsheet->getSheet(1);

    // Excel cell mappings
    $excel_cell_mappings = [
      'lastname' => 'c9',
      'firstname' => 'h9',
      'suffix' => 'n9',
      'mname' => 's9',
      'lrn' => 'e10',
      'bd' => 'l10',
      'sx' => 't10',
      'kinderprogress' => 'f14',
      'eccd' => 'l14',
      'kinder_cert' => 'q14',
      'nos' => 'd15',
      'schoolid' => 'k15',
      'aos' => 'q15',
      'pept_passer' => 'b19',
      'doe' => 'k19',
      'otherscheckbx' => 'p19',
      'othersinput' => 't19',
      'natC' => 'f20',
      'remark' => 's20'
    ];

    $field_db_mapping = [
      'lastname' => 'last_name',
      'firstname' => 'first_name',
      'suffix' => 'suffix',
      'mname' => 'middle_name',
      'lrn' => 'lrn',
      'bd' => 'birthdate',
      'sx' => 'sex',
      'kinderprogress' => 'kinder_progress_report',
      'eccd' => 'eccd_checklist',
      'kinder_cert' => 'kinder_certificate',
      'nos' => 'school_name',
      'schoolid' => 'school_id',
      'aos' => 'school_address',
      'pept_passer' => 'pept_passer',
      'doe' => 'exam_date',
      'otherscheckbx' => 'others_check',
      'othersinput' => 'others_text',
      'natC' => 'testing_center_name',
      'remark' => 'remark'
    ];

    // Fill mapped cells
    foreach ($field_db_mapping as $field_key => $db_field) {
      if (isset($excel_cell_mappings[$field_key])) {
        $cell = strtoupper($excel_cell_mappings[$field_key]);
        $value = $sf10_data[$db_field] ?? '';

        if (in_array($db_field, ['kinder_progress_report', 'eccd_checklist', 'kinder_certificate', 'pept_passer', 'others_check'])) {
          $value = $value ? '✓' : 'ⅹ';
        }

        if ($db_field === 'lrn') {
          $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
        } else {
          $sheet->setCellValue($cell, $value);
        }
      }
    }

    // Scholastic patterns
    $scholastic_patterns_all = [
      1 => ['school' => 'C24', 'school_id' => 'J24', 'district' => 'C25', 'division' => 'E25', 'region' => 'J25', 'grade' => 'D26', 'section' => 'G26', 'school_year' => 'J26', 'adviser' => 'D27', 'signature' => 'J27', 'general_average' => 'E46', 'learning_start' => 31, 'learning_col' => 'B', 'remedial_start' => 50, 'remedial_col' => 'B'],
      2 => ['school' => 'M24', 'school_id' => 'T24', 'district' => 'M25', 'division' => 'O25', 'region' => 'T25', 'grade' => 'N26', 'section' => 'Q26', 'school_year' => 'T26', 'adviser' => 'N27', 'signature' => 'T27', 'general_average' => 'O46', 'learning_start' => 31, 'learning_col' => 'L', 'remedial_start' => 50, 'remedial_col' => 'L'],
      3 => ['school' => 'C4', 'school_id' => 'J4', 'district' => 'C5', 'division' => 'E5', 'region' => 'J5', 'grade' => 'D6', 'section' => 'G6', 'school_year' => 'J6', 'adviser' => 'D7', 'signature' => 'J7', 'general_average' => 'E26', 'learning_start' => 11, 'learning_col' => 'B', 'remedial_start' => 30, 'remedial_col' => 'B'],
      4 => ['school' => 'M4', 'school_id' => 'T4', 'district' => 'M5', 'division' => 'O5', 'region' => 'T5', 'grade' => 'N6', 'section' => 'Q6', 'school_year' => 'T6', 'adviser' => 'N7', 'signature' => 'T7', 'general_average' => 'O26', 'learning_start' => 11, 'learning_col' => 'L', 'remedial_start' => 30, 'remedial_col' => 'L'],
      5 => ['school' => 'C35', 'school_id' => 'J35', 'district' => 'C36', 'division' => 'E36', 'region' => 'J36', 'grade' => 'D37', 'section' => 'G37', 'school_year' => 'J37', 'adviser' => 'D38', 'signature' => 'J38', 'general_average' => 'E57', 'learning_start' => 42, 'learning_col' => 'B', 'remedial_start' => 61, 'remedial_col' => 'B'],
      6 => ['school' => 'M35', 'school_id' => 'T35', 'district' => 'M36', 'division' => 'O36', 'region' => 'T36', 'grade' => 'N37', 'section' => 'Q37', 'school_year' => 'T37', 'adviser' => 'N38', 'signature' => 'T38', 'general_average' => 'O57', 'learning_start' => 42, 'learning_col' => 'L', 'remedial_start' => 61, 'remedial_col' => 'L']
    ];

    // Sheet mapping
    $sheet_mapping = [];
    $front_sheet = $spreadsheet->getSheetByName('Front');
    $sheet_mapping[1] = $front_sheet;
    $sheet_mapping[2] = $front_sheet;

    $template_sheet = $spreadsheet->getSheetByName('Template');

    // Create cloned sheets
    for ($rec = 3; $rec <= $num_scholastic_records; $rec++) {
      $sheet_index = intdiv($rec - 3, 4) + 1;
      $back_sheet_name = 'Back ' . $sheet_index;

      if (!$spreadsheet->sheetNameExists($back_sheet_name)) {
        $cloned_sheet = $template_sheet->copy();
        $cloned_sheet->setTitle($back_sheet_name);
        $spreadsheet->addSheet($cloned_sheet);
      }

      $sheet_mapping[$rec] = $spreadsheet->getSheetByName($back_sheet_name);
    }

    // Fill all scholastic records
    for ($rec = 1; $rec <= $num_scholastic_records; $rec++) {
      if (!isset($scholastic_data[$rec])) continue;

      if ($rec <= 2) {
        $pattern_key = $rec;
      } else {
        $pattern_key = (($rec - 3) % 4) + 3;
      }
      $pattern = $scholastic_patterns_all[$pattern_key];
      $data = $scholastic_data[$rec];
      $current_sheet = $sheet_mapping[$rec];

      $current_sheet->setCellValue($pattern['school'], $data['school'] ?? '');
      $current_sheet->setCellValue($pattern['school_id'], $data['school_id'] ?? '');
      $current_sheet->setCellValue($pattern['district'], $data['district'] ?? '');
      $current_sheet->setCellValue($pattern['division'], $data['division'] ?? '');
      $current_sheet->setCellValue($pattern['region'], $data['region'] ?? '');
      $current_sheet->setCellValue($pattern['grade'], $data['grades'] ?? '');
      $current_sheet->setCellValue($pattern['section'], $data['sections'] ?? '');
      $current_sheet->setCellValue($pattern['school_year'], $data['school_years'] ?? '');
      $current_sheet->setCellValue($pattern['adviser'], $data['adviser_name'] ?? '');
      $current_sheet->setCellValue($pattern['general_average'], $data['general_average'] ?? '');

      $subject_columns = [
        1 => ['q1' => 'E', 'q2' => 'F', 'q3' => 'G', 'q4' => 'H', 'final' => 'I', 'remarks' => 'J'],
        2 => ['q1' => 'O', 'q2' => 'P', 'q3' => 'Q', 'q4' => 'R', 'final' => 'S', 'remarks' => 'T'],
        3 => ['q1' => 'E', 'q2' => 'F', 'q3' => 'G', 'q4' => 'H', 'final' => 'I', 'remarks' => 'J'],
        4 => ['q1' => 'O', 'q2' => 'P', 'q3' => 'Q', 'q4' => 'R', 'final' => 'S', 'remarks' => 'T'],
        5 => ['q1' => 'E', 'q2' => 'F', 'q3' => 'G', 'q4' => 'H', 'final' => 'I', 'remarks' => 'J'],
        6 => ['q1' => 'O', 'q2' => 'P', 'q3' => 'Q', 'q4' => 'R', 'final' => 'S', 'remarks' => 'T']
      ];

      $cols = $subject_columns[$pattern_key];
      $learning_col = $pattern['learning_col'];
      $learning_start = $pattern['learning_start'];

      for ($sub_idx = 0; $sub_idx < count($data['learning_areas']); $sub_idx++) {
        $row = $learning_start + $sub_idx;
        $subject = $data['learning_areas'][$sub_idx] ?? '';
        if (!empty($subject)) {
          $current_sheet->setCellValue($learning_col . $row, $subject);
          $current_sheet->setCellValue($cols['q1'] . $row, $data['q1'][$sub_idx] ?? '');
          $current_sheet->setCellValue($cols['q2'] . $row, $data['q2'][$sub_idx] ?? '');
          $current_sheet->setCellValue($cols['q3'] . $row, $data['q3'][$sub_idx] ?? '');
          $current_sheet->setCellValue($cols['q4'] . $row, $data['q4'][$sub_idx] ?? '');
          $current_sheet->setCellValue($cols['final'] . $row, $data['final_ratings'][$sub_idx] ?? '');
          $current_sheet->setCellValue($cols['remarks'] . $row, $data['remarks'][$sub_idx] ?? '');
        }
      }

      // Fill remedial classes
      if (!empty($data['school_years'])) {
        $stmt_rem = $pdo->prepare("
          SELECT rc.* FROM remedial_class rc
          INNER JOIN sf10_remedial_class src ON rc.sf10_rem_id = src.sf10_rem_id
          WHERE src.sf10_data_id = ? AND src.school_year = ?
          ORDER BY src.school_year, rc.remedial_id
        ");
        $stmt_rem->execute([$sf10_data['id'], $data['school_years']]);
        $remedial_records = $stmt_rem->fetchAll(PDO::FETCH_ASSOC);

        $remedial_col = $pattern['remedial_col'];
        $remedial_start = $pattern['remedial_start'];

        foreach ($remedial_records as $rem_idx => $remedial) {
          $rem_row = $remedial_start + $rem_idx;
          $rem_area = $remedial['area'] ?? '';
          if (!empty($rem_area)) {
            $current_sheet->setCellValue($remedial_col . $rem_row, $rem_area);
            $current_sheet->setCellValue(chr(ord($remedial_col) + 3) . $rem_row, $remedial['final_rating'] ?? '');
            $current_sheet->setCellValue(chr(ord($remedial_col) + 5) . $rem_row, $remedial['class_mark'] ?? '');
            $current_sheet->setCellValue(chr(ord($remedial_col) + 7) . $rem_row, $remedial['recomputed_rating'] ?? '');
            $current_sheet->setCellValue(chr(ord($remedial_col) + 8) . $rem_row, $remedial['remarks'] ?? '');
          }
        }
      }
    }

    // Add logo
    $logo_path = $_SERVER['DOCUMENT_ROOT'] . BASE_FR . '/assets/image/deped.png';
    if (file_exists($logo_path)) {
      $drawing = new Drawing();
      $drawing->setName('DepEd Logo');
      $drawing->setDescription('DepEd Logo');
      $drawing->setPath($logo_path);
      $drawing->setCoordinates('A1');
      $drawing->setWidth(80);
      $drawing->setHeight(80);
      $drawing->setWorksheet($sheet);
    }

    // Generate filename
    $safe_lrn = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($sf10_data['lrn'] ?? ''));
    $safe_first = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($sf10_data['first_name'] ?? ''));
    $safe_last = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($sf10_data['last_name'] ?? ''));
    $filename = trim($safe_lrn . '_' . $safe_first . '_' . $safe_last . '_SF10.xlsx', '_');

    // Use provided directory or default to sf10_files
    if (!$save_directory) {
      $save_directory = BASE_PATH . '/sf10_files';
    }

    // Ensure directory exists
    if (!is_dir($save_directory)) {
      mkdir($save_directory, 0755, true);
    }

    $savePath = $save_directory . DIRECTORY_SEPARATOR . $filename;
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($savePath);

    return [
      'success' => true,
      'filename' => $filename,
      'path' => $savePath,
      'message' => 'Excel file generated successfully'
    ];
  } catch (Exception $e) {
    return [
      'success' => false,
      'message' => $e->getMessage()
    ];
  }
}

// AJAX Handler for deleting remedial entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_remedial') {
  header('Content-Type: application/json');

  try {
    $remedial_id = $_POST['remedial_id'] ?? null;

    if (!$remedial_id) {
      throw new Exception('Missing remedial ID');
    }

    // First, get the sf10_rem_id before deleting
    $stmt_get_parent = $pdo->prepare("SELECT sf10_rem_id FROM remedial_class WHERE remedial_id = ?");
    $stmt_get_parent->execute([$remedial_id]);
    $remedial_record = $stmt_get_parent->fetch(PDO::FETCH_ASSOC);

    if (!$remedial_record) {
      throw new Exception('Remedial entry not found');
    }

    $sf10_rem_id = $remedial_record['sf10_rem_id'];

    // Delete the remedial entry
    $stmt_delete = $pdo->prepare("DELETE FROM remedial_class WHERE remedial_id = ?");
    $stmt_delete->execute([$remedial_id]);

    // Check if there are any remaining entries in this remedial class group
    $stmt_check_remaining = $pdo->prepare("SELECT COUNT(*) as count FROM remedial_class WHERE sf10_rem_id = ?");
    $stmt_check_remaining->execute([$sf10_rem_id]);
    $remaining = $stmt_check_remaining->fetch(PDO::FETCH_ASSOC);

    // If no more entries, delete the parent sf10_remedial_class
    if ($remaining['count'] == 0) {
      $stmt_delete_parent = $pdo->prepare("DELETE FROM sf10_remedial_class WHERE sf10_rem_id = ?");
      $stmt_delete_parent->execute([$sf10_rem_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Remedial entry deleted successfully']);
    exit;
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
  }
}

// AJAX Handler for adding remedial data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_remedial') {
  header('Content-Type: application/json');

  try {
    $student_id = $_POST['student_id'] ?? null;
    $scholastic_index = $_POST['scholastic_index'] ?? null;
    $school_year = $_POST['school_year'] ?? '';
    $area = $_POST['area'] ?? '';
    $final_rating = $_POST['final_rating'] ?? '';
    $class_mark = $_POST['class_mark'] ?? '';
    $recomputed_rating = $_POST['recomputed_rating'] ?? '';
    $remarks = $_POST['remarks'] ?? '';

    if (!$student_id || !$scholastic_index || !$area) {
      throw new Exception('Missing required fields');
    }

    // Validate school year
    if (empty($school_year)) {
      throw new Exception('School year is required');
    }

    if (strpos($school_year, '-') !== false) {
      list($from_year, $to_year) = explode('-', $school_year);
      $from_year = (int)$from_year;
      $to_year = (int)$to_year;

      if ($to_year - $from_year !== 1) {
        throw new Exception('School year must have exactly 1 year gap (e.g., 2024-2025)');
      }

      if ($from_year < 2000) {
        throw new Exception('From year cannot be before 2000');
      }

      // $current_year = (int)date('Y');
      // if ($to_year > $current_year) {
      //   throw new Exception("To year cannot be beyond the current year ($current_year)");
      // }
    } else {
      throw new Exception('School year format invalid. Use YYYY-YYYY format (e.g., 2024-2025)');
    }

    // Get or create SF10 record
    $stmt_check = $pdo->prepare("SELECT id FROM sf10_data WHERE student_id = ?");
    $stmt_check->execute([$student_id]);
    $sf10_record = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$sf10_record) {
      $stmt_insert_sf10 = $pdo->prepare("INSERT INTO sf10_data (student_id) VALUES (?)");
      $stmt_insert_sf10->execute([$student_id]);
      $sf10_id = $pdo->lastInsertId();
    } else {
      $sf10_id = $sf10_record['id'];
    }

    // Check if remedial class group already exists for this scholastic record
    $stmt_check_group = $pdo->prepare("SELECT sf10_rem_id FROM sf10_remedial_class WHERE sf10_data_id = ? AND school_year = ?");
    $stmt_check_group->execute([$sf10_id, $school_year]);
    $existing_group = $stmt_check_group->fetch(PDO::FETCH_ASSOC);

    if ($existing_group) {
      // Check how many entries already exist in this group
      $stmt_count = $pdo->prepare("SELECT COUNT(*) as count FROM remedial_class WHERE sf10_rem_id = ?");
      $stmt_count->execute([$existing_group['sf10_rem_id']]);
      $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);

      if ($count_result['count'] >= 2) {
        throw new Exception('Maximum 2 remedial entries allowed per scholastic record');
      }

      $sf10_rem_id = $existing_group['sf10_rem_id'];
    } else {
      // Create remedial class group for this scholastic record
      $stmt_group = $pdo->prepare("INSERT INTO sf10_remedial_class (sf10_data_id, school_year) VALUES (?, ?)");
      $stmt_group->execute([$sf10_id, $school_year]);
      $sf10_rem_id = $pdo->lastInsertId();
    }

    // Insert remedial entry
    $stmt_remedial = $pdo->prepare(
      "INSERT INTO remedial_class (sf10_rem_id, area, final_rating, class_mark, recomputed_rating, remarks) 
             VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt_remedial->execute([
      $sf10_rem_id,
      $area,
      $final_rating,
      $class_mark,
      $recomputed_rating,
      $remarks
    ]);

    echo json_encode(['success' => true, 'message' => 'Remedial data added successfully']);
    exit;
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
  }
}

// AJAX Handler for showing remedial records by school year
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'show_remedial_records') {
  header('Content-Type: text/html');

  try {
    $student_id = $_POST['student_id'] ?? null;
    $school_year = $_POST['school_year'] ?? '';

    if (!$student_id || !$school_year) {
      echo '<div class="alert alert-warning">Invalid parameters: student_id=' . htmlspecialchars($student_id) . ', school_year=' . htmlspecialchars($school_year) . '</div>';
      exit;
    }

    // Get SF10 record for this student
    $stmt_sf10 = $pdo->prepare("SELECT id FROM sf10_data WHERE student_id = ?");
    $stmt_sf10->execute([$student_id]);
    $sf10_data = $stmt_sf10->fetch(PDO::FETCH_ASSOC);

    if (!$sf10_data) {
      echo '<div class="alert alert-info"><small>No SF10 record found for this student. You may need to save the form first.</small></div>';
      exit;
    }

    // Get remedial class group for this school year
    $stmt_rem_group = $pdo->prepare("
      SELECT sf10_rem_id FROM sf10_remedial_class 
      WHERE sf10_data_id = ? AND school_year = ?
    ");
    $stmt_rem_group->execute([$sf10_data['id'], $school_year]);
    $rem_groups = $stmt_rem_group->fetchAll(PDO::FETCH_COLUMN);

    if (empty($rem_groups)) {
      echo '<div class="alert alert-info"><small>No remedial records for this scholastic record.</small></div>';
      exit;
    }

    // Build the table HTML
    echo '<div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
      <table class="table table-bordered table-sm" style="min-width: 600px;">
        <thead>
          <tr>
            <th>Learning Area</th>
            <th>Final Rating</th>
            <th>Remedial Class Mark</th>
            <th>Recomputed Final Grade</th>
            <th>Remarks</th>
            <th style="width: 80px; text-align: center;">Action</th>
          </tr>
        </thead>
        <tbody>';

    foreach ($rem_groups as $sf10_rem_id) {
      $stmt_entries = $pdo->prepare("
        SELECT * FROM remedial_class 
        WHERE sf10_rem_id = ?
        ORDER BY remedial_id ASC
      ");
      $stmt_entries->execute([$sf10_rem_id]);
      $entries = $stmt_entries->fetchAll(PDO::FETCH_ASSOC);

      foreach ($entries as $rem) {
        echo '<tr>
          <td>' . htmlspecialchars($rem['area'] ?? '') . '</td>
          <td>' . htmlspecialchars($rem['final_rating'] ?? '') . '</td>
          <td>' . htmlspecialchars($rem['class_mark'] ?? '') . '</td>
          <td>' . htmlspecialchars($rem['recomputed_rating'] ?? '') . '</td>
          <td>' . htmlspecialchars($rem['remarks'] ?? '') . '</td>
          <td style="text-align: center;">
            <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="deleteRemedialEntry(' . $rem['remedial_id'] . ', \'' . htmlspecialchars($school_year) . '\')">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>';
      }
    }

    echo '</tbody>
      </table>
    </div>';
    exit;
  } catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
  }
}

// AJAX Handler for downloading Excel file - NO LONGER USED
// Download now happens directly from sf10_files folder without DB access
// Kept here for reference but can be removed if needed
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'download_excel') {
  // ...
}
*/

// Handler to actually download the file from sf10_files directory
if (isset($_GET['download_sf10'])) {
  try {
    $filename = $_GET['download_sf10'] ?? null;
    if (!$filename) throw new Exception('No filename provided');

    // Sanitize filename
    $filename = basename($filename);
    $savePath = BASE_PATH . '/sf10_files' . DIRECTORY_SEPARATOR . $filename;

    if (!file_exists($savePath)) {
      throw new Exception('File not found at: ' . $savePath);
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($savePath));
    flush();
    readfile($savePath);
    exit;
  } catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
  }
}



// This matches the learning areas in schoolform9.php
$num_subjects = 15; // Maximum columns in SF9 schema (loop through all, skip empty ones)

// Get the number of behaviors dynamically
$behavior_columns = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'sf9_data' AND COLUMN_NAME LIKE 'behavior_%' ORDER BY COLUMN_NAME")->fetchAll(PDO::FETCH_COLUMN);
$num_behaviors = count($behavior_columns) / 4; // Divide by 4 because each behavior has 4 quarters

// Get the number of scholastic records dynamically from sf9_data
// Query all sf9 records for this student to determine how many school years they have
$num_scholastic_records = 0;
$sf9_records = [];
$student_id = $_GET['student_id'] ?? null;
$student = null;
if ($student_id) {
  $stmt = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
  $stmt->execute([$student_id]);
  $student = $stmt->fetch(PDO::FETCH_ASSOC);

  // Get all SF9 records for this student
  $stmt_sf9 = $pdo->prepare("SELECT * FROM sf9_data WHERE student_id = ? ORDER BY school_year");
  $stmt_sf9->execute([$student_id]);
  $sf9_records = $stmt_sf9->fetchAll(PDO::FETCH_ASSOC);
  $num_scholastic_records = count($sf9_records);

  if ($num_scholastic_records == 0) {
    $num_scholastic_records = 1;
  }
}

// Initialize scholastic data structure first
$scholastic_data = [
  'grades' => [],
  'sections' => [],
  'schools' => [],
  'districts' => [],
  'divisions' => [],
  'school_ids' => [],
  'regions' => [],
  'school_years' => [],
  'adviser_name' => [],
  'learning_areas' => [],
  'q1' => [],
  'q2' => [],
  'q3' => [],
  'q4' => [],
  'final_ratings' => [],
  'remarks_table' => [],
  'general_average' => []
];
$remedial_data = [];

// Helper function to validate school year
function validateSchoolYearPHP($schoolYear)
{
  if (empty($schoolYear)) {
    return ['valid' => false, 'error' => 'School year is required'];
  }

  if (strpos($schoolYear, '-') === false) {
    return ['valid' => false, 'error' => 'School year format invalid. Use YYYY-YYYY format (e.g., 2024-2025)'];
  }

  $parts = explode('-', $schoolYear);
  if (count($parts) !== 2) {
    return ['valid' => false, 'error' => 'School year format invalid. Use YYYY-YYYY format (e.g., 2024-2025)'];
  }

  $from_year = (int)$parts[0];
  $to_year = (int)$parts[1];

  // Validate year gap
  if ($to_year - $from_year !== 1) {
    return ['valid' => false, 'error' => 'School year must have exactly 1 year gap (e.g., 2024-2025)'];
  }

  // Validate from year
  if ($from_year < 2000) {
    return ['valid' => false, 'error' => 'From year cannot be before 2000'];
  }

  // Validate to year against current year
  $current_year = (int)date('Y');
  if ($to_year > $current_year) {
    return ['valid' => false, 'error' => "To year cannot be beyond the current year ($current_year)"];
  }

  return ['valid' => true];
}
$sf10_data = [];

// POST Handler - Save New Scholastic Record (from submitrec form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_new_scholastic_record']) && $_POST['is_new_scholastic_record'] === '1') {
  try {
    $student_id = $_GET['student_id'] ?? null;
    if (!$student_id) {
      throw new Exception('Student ID is required');
    }

    // Get scholastic index from the stored_grade_data
    $scholastic_index = null;
    foreach ($_POST as $key => $value) {
      if (strpos($key, 'stored_grade_data_') === 0) {
        $scholastic_index = (int)str_replace('stored_grade_data_', '', $key);
        break;
      }
    }

    if (!$scholastic_index) {
      throw new Exception('Scholastic index not found');
    }

    error_log("DEBUG: New scholastic record submission detected. Index: $scholastic_index, StudentID: $student_id");
    error_log("DEBUG: POST data keys: " . implode(", ", array_keys($_POST)));

    // Get form data
    $school = trim($_POST["school{$scholastic_index}"] ?? '');
    $district = trim($_POST["district{$scholastic_index}"] ?? '');
    $division = trim($_POST["division{$scholastic_index}"] ?? '');
    $school_id = trim($_POST["school_id{$scholastic_index}"] ?? '');
    $region = trim($_POST["region{$scholastic_index}"] ?? '');
    $grade = trim($_POST["grade{$scholastic_index}"] ?? '');
    $section = trim($_POST["section{$scholastic_index}"] ?? '');
    $school_year_from = trim($_POST["school_year_from{$scholastic_index}"] ?? '');
    $school_year_to = trim($_POST["school_year_to{$scholastic_index}"] ?? '');
    $adviser_name = trim($_POST["adviser_name{$scholastic_index}"] ?? '');
    $school_year = $school_year_from . '-' . $school_year_to;

    error_log("DEBUG: Grade='$grade', Section='$section', SchoolYear='$school_year', Adviser='$adviser_name'");

    // Validate required fields
    if (empty($grade)) {
      throw new Exception('Grade level is required');
    }
    if (empty($section)) {
      throw new Exception('Section is required');
    }
    if (empty($school_year_from) || empty($school_year_to)) {
      throw new Exception('School year is required');
    }
    if (empty($adviser_name)) {
      throw new Exception('Adviser name is required');
    }

    error_log("DEBUG: Validation passed. Checking school year format.");

    // Validate school year
    $sy_validation = validateSchoolYearPHP($school_year);
    if (!$sy_validation['valid']) {
      throw new Exception($sy_validation['error']);
    }

    error_log("DEBUG: School year validation passed.");

    // Get stored grade data - check both sources: stored JSON and form arrays
    $stored_data_key = "stored_grade_data_{$scholastic_index}";
    $stored_grades = [];

    // First try to get data from stored_grade_data JSON
    if (isset($_POST[$stored_data_key]) && !empty($_POST[$stored_data_key])) {
      $stored_grades = json_decode($_POST[$stored_data_key], true);
      error_log("DEBUG: Got stored grades from JSON: " . json_encode($stored_grades));
      if (!is_array($stored_grades)) {
        throw new Exception('Invalid grade data format in JSON');
      }
    } else {
      // Fallback: Reconstruct from form array fields (learning_area_2[], q1_2[], etc.)
      error_log("DEBUG: No JSON data found, reconstructing from form arrays");

      $learning_areas = $_POST["learning_area_{$scholastic_index}"] ?? [];
      $q1_array = $_POST["q1_{$scholastic_index}"] ?? [];
      $q2_array = $_POST["q2_{$scholastic_index}"] ?? [];
      $q3_array = $_POST["q3_{$scholastic_index}"] ?? [];
      $q4_array = $_POST["q4_{$scholastic_index}"] ?? [];
      $final_array = $_POST["final_rating_{$scholastic_index}"] ?? [];
      $remarks_array = $_POST["remarks_{$scholastic_index}"] ?? [];

      error_log("DEBUG: Learning areas array: " . json_encode($learning_areas));
      error_log("DEBUG: Learning areas count: " . count($learning_areas));
      error_log("DEBUG: Q1 count: " . count($q1_array));
      error_log("DEBUG: Final ratings count: " . count($final_array));

      // Filter out empty entries
      $valid_entries = 0;
      foreach ($learning_areas as $idx => $area) {
        $area_trimmed = trim($area ?? '');
        $q1_val = floatval($q1_array[$idx] ?? 0);
        $q2_val = floatval($q2_array[$idx] ?? 0);
        $q3_val = floatval($q3_array[$idx] ?? 0);
        $q4_val = floatval($q4_array[$idx] ?? 0);
        $final_val = floatval($final_array[$idx] ?? 0);

        if (!empty($area_trimmed) && ($q1_val > 0 || $q2_val > 0 || $q3_val > 0 || $q4_val > 0)) {
          $stored_grades[] = [
            'learning_area' => $area_trimmed,
            'q1' => $q1_val,
            'q2' => $q2_val,
            'q3' => $q3_val,
            'q4' => $q4_val,
            'final_rating' => $final_val,
            'remarks' => trim($remarks_array[$idx] ?? '')
          ];
          $valid_entries++;
          // Added valid grade entry
        }
      }
    }

    if (empty($stored_grades)) {
      throw new Exception('No subjects added. Fill in at least one subject in the Grades Table and click the Add (+) button.');
    }

    // Get student info
    $stmt_student = $pdo->prepare("SELECT * FROM student WHERE student_id = ?");
    $stmt_student->execute([$student_id]);
    $student_details = $stmt_student->fetch(PDO::FETCH_ASSOC);

    if (!$student_details) {
      throw new Exception('Student not found in database');
    }

    error_log("DEBUG: Student found: " . $student_details['fname'] . " " . $student_details['lname']);

    // Check if SF9 record already exists for this student and school year
    $stmt_check_sf9 = $pdo->prepare("SELECT id FROM sf9_data WHERE student_id = ? AND school_year = ?");
    $stmt_check_sf9->execute([$student_id, $school_year]);
    $existing_sf9 = $stmt_check_sf9->fetch(PDO::FETCH_ASSOC);

    if ($existing_sf9) {
      throw new Exception('A scholastic record for this school year already exists.');
    }

    error_log("DEBUG: No duplicate school year found. Proceeding with insert.");

    // Prepare SF9 insert data
    $sf9_insert = [
      'school' => $school,
      'district' => $district,
      'division' => $division,
      'school_id' => $school_id,
      'region' => $region,
      'student_id' => $student_id,
      'student_name' => trim($student_details['fname'] . ' ' . $student_details['lname']),
      'lrn' => $student_details['lrn'] ?? '',
      'age' => null,
      'sex' => $student_details['sex'] ?? '',
      'school_year' => $school_year,
      'teacher' => $adviser_name,
      'section' => $section,
      'grade' => $grade
    ];

    error_log("DEBUG: SF9 insert data prepared: " . json_encode($sf9_insert));

    // Insert SF9 record
    $cols = implode(', ', array_keys($sf9_insert));
    $placeholders = implode(', ', array_fill(0, count($sf9_insert), '?'));
    $insert_sql = "INSERT INTO sf9_data ($cols) VALUES ($placeholders)";

    error_log("DEBUG: Executing SQL: $insert_sql");

    $stmt_insert_sf9 = $pdo->prepare($insert_sql);
    $stmt_insert_sf9->execute(array_values($sf9_insert));
    $sf9_id = $pdo->lastInsertId();

    if (!$sf9_id) {
      throw new Exception('Failed to insert SF9 record');
    }

    error_log("DEBUG: SF9 record inserted with ID: $sf9_id");

    // Insert grade data for each stored subject
    $update_parts = [];
    $update_values = [];
    $final_grades = [];

    foreach ($stored_grades as $idx => $grade_entry) {
      $subject = trim($grade_entry['learning_area'] ?? '');
      $q1 = floatval($grade_entry['q1'] ?? 0);
      $q2 = floatval($grade_entry['q2'] ?? 0);
      $q3 = floatval($grade_entry['q3'] ?? 0);
      $q4 = floatval($grade_entry['q4'] ?? 0);
      $final = floatval($grade_entry['final_rating'] ?? 0);
      $remarks = trim($grade_entry['remarks'] ?? '');

      // Column numbers start from 1 (subject_1, subject_2, etc.)
      $col_num = $idx + 1;
      if ($col_num <= 15) {
        $update_parts[] = "subject_$col_num = ?, q1_$col_num = ?, q2_$col_num = ?, q3_$col_num = ?, q4_$col_num = ?, final_$col_num = ?, remarks_$col_num = ?";
        array_push($update_values, $subject, $q1, $q2, $q3, $q4, $final, $remarks);

        error_log("DEBUG: Subject $col_num: '$subject', Q1=$q1, Q2=$q2, Q3=$q3, Q4=$q4, Final=$final, Remarks='$remarks'");

        if (!empty($final) && is_numeric($final) && $final > 0) {
          $final_grades[] = (float)$final;
        }
      }
    }

    error_log("DEBUG: Total update parts: " . count($update_parts));
    error_log("DEBUG: Total final grades collected: " . count($final_grades));

    $general_avg = null;
    if (!empty($final_grades)) {
      $general_avg = round(array_sum($final_grades) / count($final_grades), 2);
    }

    error_log("DEBUG: Calculated general average: $general_avg");

    if (!empty($update_parts)) {
      $update_parts[] = "general_average = ?";
      $update_values[] = $general_avg;
      $update_values[] = $sf9_id;

      $update_sql = "UPDATE sf9_data SET " . implode(', ', $update_parts) . " WHERE id = ?";

      $stmt_update_grades = $pdo->prepare($update_sql);
      if (!$stmt_update_grades) {
        throw new Exception('SQL Prepare failed: ' . json_encode($pdo->errorInfo()));
      }

      $exec_result = $stmt_update_grades->execute($update_values);
      if (!$exec_result) {
        $error_info = $stmt_update_grades->errorInfo();
        throw new Exception('SQL Execute failed: ' . $error_info[2]);
      }
    } else {
      error_log("DEBUG: WARNING - No grade data to update!");
    }


    $showSuccess = true;
    $successMessage = "New scholastic record for {$school_year} has been saved successfully!";

    // Reload the page to show the new record in the tabs
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  } catch (Exception $e) {
    die('Error: ' . htmlspecialchars($e->getMessage()));
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
  try {
    $student_id = $_GET['student_id'] ?? null;
    if (!$student_id) {
      throw new Exception('Student ID is required');
    }

    // Prepare SF10 data
    $sf10_insert = [
      'student_id' => $student_id,
      'last_name' => $_POST['last_name'] ?? '',
      'first_name' => $_POST['first_name'] ?? '',
      'middle_name' => $_POST['middle_name'] ?? '',
      'suffix' => $_POST['suffix'] ?? '',
      'lrn' => $_POST['lrn'] ?? '',
      'birthdate' => !empty($_POST['birthdate']) ? $_POST['birthdate'] : null,
      'sex' => $_POST['sex'] ?? '',
      'school_name' => $_POST['school_name'] ?? '',
      'school_id' => $_POST['school_id'] ?? '',
      'school_address' => $_POST['school_address'] ?? '',
      'kinder_progress_report' => isset($_POST['kinder_progress_report']) ? 1 : 0,
      'eccd_checklist' => isset($_POST['eccd_checklist']) ? 1 : 0,
      'kinder_certificate' => isset($_POST['kinder_certificate']) ? 1 : 0,
      'pept_passer' => isset($_POST['pept_passer']) ? 1 : 0,
      'pept_text' => $_POST['pept_text'] ?? '',
      'exam_date' => !empty($_POST['exam_date']) ? $_POST['exam_date'] : null,
      'others_check' => isset($_POST['others_check']) ? 1 : 0,
      'others_text' => $_POST['others_text'] ?? '',
      'testing_center_name' => $_POST['testing_center_name'] ?? '',
      'testing_center_address' => $_POST['testing_center_address'] ?? '',
      'remark' => $_POST['remark'] ?? ''
    ];

    // Check if SF10 record exists
    $stmt_check = $pdo->prepare("SELECT id FROM sf10_data WHERE student_id = ?");
    $stmt_check->execute([$student_id]);
    $existing_sf10 = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($existing_sf10) {
      // Update existing SF10 record
      $update_cols = implode(', ', array_map(fn($k) => "$k = ?", array_keys($sf10_insert)));
      $stmt_update = $pdo->prepare("UPDATE sf10_data SET $update_cols WHERE id = ?");
      $values = array_values($sf10_insert);
      $values[] = $existing_sf10['id'];
      $stmt_update->execute($values);
      $sf10_id = $existing_sf10['id'];
    } else {
      // Insert new SF10 record
      $cols = implode(', ', array_keys($sf10_insert));
      $placeholders = implode(', ', array_fill(0, count($sf10_insert), '?'));
      $stmt_insert = $pdo->prepare("INSERT INTO sf10_data ($cols) VALUES ($placeholders)");
      $stmt_insert->execute(array_values($sf10_insert));
      $sf10_id = $pdo->lastInsertId();
    }

    // Delete existing remedial data for this SF10
    // $pdo->prepare("DELETE FROM remedial_class WHERE sf10_rem_id IN (
    //         SELECT sf10_rem_id FROM sf10_remedial_class WHERE sf10_data_id = ?
    //     )")->execute([$sf10_id]);
    // $pdo->prepare("DELETE FROM sf10_remedial_class WHERE sf10_data_id = ?")->execute([$sf10_id]);

    // Process remedial data for each scholastic record
    for ($i = 1; $i <= $num_scholastic_records; $i++) {
      $rem_areas = $_POST["rem{$i}_area"] ?? [];
      $school_year = $_POST["school_year{$i}"] ?? '';

      // Validate school year if remedial data exists
      if (!empty($rem_areas) && is_array($rem_areas) && !empty($school_year)) {
        $sy_validation = validateSchoolYearPHP($school_year);
        if (!$sy_validation['valid']) {
          throw new Exception($sy_validation['error']);
        }
      }

      if (!empty($rem_areas) && is_array($rem_areas)) {
        // Create a remedial class group for this scholastic record
        $stmt_rem_insert = $pdo->prepare(
          "INSERT INTO sf10_remedial_class (sf10_data_id, school_year) VALUES (?, ?)"
        );
        $stmt_rem_insert->execute([$sf10_id, $school_year]);
        $sf10_rem_id = $pdo->lastInsertId();

        // Insert remedial entries
        foreach ($rem_areas as $idx => $area) {
          if (!empty($area)) {
            $stmt_rem_data = $pdo->prepare(
              "INSERT INTO remedial_class (sf10_rem_id, area, final_rating, class_mark, recomputed_rating, remarks) 
                             VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt_rem_data->execute([
              $sf10_rem_id,
              $area,
              $_POST["rem{$i}_final"][$idx] ?? '',
              $_POST["rem{$i}_class_mark"][$idx] ?? '',
              $_POST["rem{$i}_recomputed"][$idx] ?? '',
              $_POST["rem{$i}_remarks"][$idx] ?? ''
            ]);
          }
        }
      }
    }

    $showSuccess = true;
    $successMessage = 'SF10 data saved successfully!';

    // Generate Excel file immediately after successful save to sf10_files
    try {
      $excel_result = generateSF10Excel($pdo, $student_id, BASE_PATH . '/sf10_files');
      if ($excel_result['success']) {
        error_log("Excel file created successfully: " . $excel_result['path']);
      } else {
        error_log("Warning: Excel generation failed: " . $excel_result['message']);
      }
    } catch (Exception $e) {
      error_log("Warning: Could not generate Excel file after save: " . $e->getMessage());
    }
  } catch (Exception $e) {
    error_log("SF10 Error: " . $e->getMessage());
    die("Error: " . htmlspecialchars($e->getMessage()));
  }
}

if (isset($_GET['student_id'])) {
  $stmt = $pdo->prepare("SELECT * FROM sf10_data WHERE student_id = ? ORDER BY id DESC LIMIT 1");
  $stmt->execute([$_GET['student_id']]);
  $sf10_data = $stmt->fetch(PDO::FETCH_ASSOC);

  // Populate learning areas and grades from sf9_records
  foreach ($sf9_records as $idx => $sf9_record) {
    $scholastic_index = $idx + 1; // Start from 1, not 0

    // Set basic info from SF9
    $scholastic_data['schools'][$scholastic_index] = $sf9_record['school'] ?? '';
    $scholastic_data['districts'][$scholastic_index] = $sf9_record['district'] ?? '';
    $scholastic_data['divisions'][$scholastic_index] = $sf9_record['division'] ?? '';
    $scholastic_data['school_ids'][$scholastic_index] = $sf9_record['school_id'] ?? '';
    $scholastic_data['regions'][$scholastic_index] = $sf9_record['region'] ?? '';

    $scholastic_data['grades'][$scholastic_index] = $sf9_record['grade'] ?? '';
    $scholastic_data['sections'][$scholastic_index] = $sf9_record['section'] ?? '';
    $scholastic_data['school_years'][$scholastic_index] = $sf9_record['school_year'] ?? '';
    $scholastic_data['adviser_name'][$scholastic_index] = $sf9_record['teacher'] ?? '';

    // Extract learning areas and grades from SF9
    $scholastic_data['learning_areas'][$scholastic_index] = [];
    $scholastic_data['q1'][$scholastic_index] = [];
    $scholastic_data['q2'][$scholastic_index] = [];
    $scholastic_data['q3'][$scholastic_index] = [];
    $scholastic_data['q4'][$scholastic_index] = [];
    $scholastic_data['final_ratings'][$scholastic_index] = [];
    $scholastic_data['remarks_table'][$scholastic_index] = [];

    // Populate learning areas and quarterly grades from SF9 columns
    for ($r = 1; $r <= $num_subjects; $r++) {
      $scholastic_data['learning_areas'][$scholastic_index][$r - 1] = $sf9_record["subject_{$r}"] ?? '';
      $scholastic_data['q1'][$scholastic_index][$r - 1] = $sf9_record["q1_{$r}"] ?? '';
      $scholastic_data['q2'][$scholastic_index][$r - 1] = $sf9_record["q2_{$r}"] ?? '';
      $scholastic_data['q3'][$scholastic_index][$r - 1] = $sf9_record["q3_{$r}"] ?? '';
      $scholastic_data['q4'][$scholastic_index][$r - 1] = $sf9_record["q4_{$r}"] ?? '';
      $scholastic_data['final_ratings'][$scholastic_index][$r - 1] = $sf9_record["final_{$r}"] ?? '';
      $scholastic_data['remarks_table'][$scholastic_index][$r - 1] = $sf9_record["remarks_{$r}"] ?? '';
    }

    // Calculate general average
    $total = 0;
    $count = 0;
    for ($r = 0; $r < $num_subjects; $r++) {
      $final = $scholastic_data['final_ratings'][$scholastic_index][$r] ?? '';
      if (!empty($final) && is_numeric($final)) {
        $total += floatval($final);
        $count++;
      }
    }
    $scholastic_data['general_average'][$scholastic_index] = $count > 0 ? round($total / $count, 2) : '';
  }

  // Load remedial data from database for each scholastic record
  $remedial_loaded = [];

  // Initialize all scholastic records with empty arrays
  for ($i = 1; $i <= $num_scholastic_records; $i++) {
    $remedial_loaded[$i] = [
      'area' => [],
      'final' => [],
      'class_mark' => [],
      'recomputed' => [],
      'remarks' => []
    ];
  }

  if (!empty($sf10_data['id'])) {
    // Get all remedial groups for this SF10 in order
    $stmt_groups = $pdo->prepare("
            SELECT sf10_rem_id FROM sf10_remedial_class 
            WHERE sf10_data_id = ?
            ORDER BY sf10_rem_id ASC
        ");
    $stmt_groups->execute([$sf10_data['id']]);
    $remedial_groups = $stmt_groups->fetchAll(PDO::FETCH_COLUMN);

    // For each group, load its remedial entries
    foreach ($remedial_groups as $group_index => $sf10_rem_id) {
      $scholastic_index = $group_index + 1; // Convert 0-based to 1-based

      if ($scholastic_index <= $num_scholastic_records) {
        $stmt_entries = $pdo->prepare("
                    SELECT * FROM remedial_class 
                    WHERE sf10_rem_id = ?
                    ORDER BY remedial_id ASC
                ");
        $stmt_entries->execute([$sf10_rem_id]);
        $entries = $stmt_entries->fetchAll(PDO::FETCH_ASSOC);

        foreach ($entries as $rem) {
          $remedial_loaded[$scholastic_index]['area'][] = $rem['area'] ?? '';
          $remedial_loaded[$scholastic_index]['final'][] = $rem['final_rating'] ?? '';
          $remedial_loaded[$scholastic_index]['class_mark'][] = $rem['class_mark'] ?? '';
          $remedial_loaded[$scholastic_index]['recomputed'][] = $rem['recomputed_rating'] ?? '';
          $remedial_loaded[$scholastic_index]['remarks'][] = $rem['remarks'] ?? '';
        }
      }
    }
  }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>SF10 Fill</title>
  <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE_FR ?>/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet">
  <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet\"> -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/fontawesome/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: #f4f5f7;
      margin: 0;
      padding: 0;
    }

    /* Header */
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

    /* Containers */
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

    .sidebar {
      top: 90px;
      height: fit-content;
    }

    /* Main content centering */
    .main-content {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .main-content>.eligibility-container,
    .main-content>.scholastic-container {
      width: 100%;
      max-width: 1100px;
    }

    /* Forms */
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

    /* Remedial carousel */
    .remedial-carousel-container {
      width: 100%;
      background: #fff;
      /* same as other containers */
      padding: 20px;
      /* match other containers */
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
      margin: 30px auto 20px;
      /* space from Scholastic and below */
      position: relative;
      overflow: hidden;
    }

    /* Wrapper inside carousel */
    .remedial-wrapper {
      position: relative;
    }

    /* Individual slides */
    .remedial-slide {
      display: none;
      width: 100%;
    }

    .remedial-slide.active {
      display: block;
    }

    /* Table inside slides */
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

    /* Arrows */
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

    /* Remedial Records Table Styling */
    .table-responsive {
      margin-top: 15px;
      border-radius: 8px;
      overflow: hidden;
    }

    .table {
      margin-bottom: 0;
    }

    .table thead {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .table thead th {
      padding: 12px;
      font-weight: 600;
      border: none;
    }

    .table tbody td {
      padding: 12px;
      vertical-align: middle;
      border-color: #e0e0e0;
    }

    .table tbody tr:hover {
      background-color: #f8f9fa;
    }

    .table tbody tr:last-child td {
      border-bottom: 2px solid #667eea;
    }

    .btn-danger {
      padding: 5px 10px;
      font-size: 0.875rem;
      transition: all 0.3s ease;
    }

    .btn-danger:hover {
      transform: scale(1.1);
      box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    .btn-danger i {
      margin-right: 3px;
    }

    /* Card Styling */
    .card {
      border: none;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
      border-radius: 10px;
      overflow: hidden;
      margin-top: 30px;
    }

    .card-header {
      border: none;
      padding: 15px 20px;
      font-weight: 600;
    }

    .card-body {
      padding: 20px;
    }
  </style>

</head>

<body>
  <div class="d-flex align-items-center justify-content-between col-12 m-0 p-0 header-brand">
    <div class="d-flex align-items-center ps-4">
      <img src="<?= BASE_FR ?>/assets/image/logo2.png" alt="Logo">
      <h4>STA.MARIA WEB SYSTEM</h4>
    </div>
  </div>
  <div class="container-fluid" style="padding: 1rem 10%!important;">
    <form method="post" onsubmit="populateGradeDataFromStore(); return validateFormBeforeSubmit()">
      <input type="hidden" id="form_student_id" value="<?= htmlspecialchars($student_id ?? '') ?>">

      <!-- Learner's Personal Information Header -->
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">Learner's Personal Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
              <label class="form-label fw-bold">Last Name</label>
              <input readonly type="text" class="form-control form-control-sm" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? ($student['lname'] ?? ($sf10_data['last_name'] ?? ''))) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
              <label class="form-label fw-bold">First Name</label>
              <input readonly type="text" class="form-control form-control-sm" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? ($student['fname'] ?? ($sf10_data['first_name'] ?? ''))) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
              <label class="form-label fw-bold">Middle Name</label>
              <input readonly type="text" class="form-control form-control-sm" name="middle_name" value="<?= htmlspecialchars($_POST['middle_name'] ?? ($student['mname'] ?? ($sf10_data['middle_name'] ?? ''))) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-1 mb-3">
              <label class="form-label fw-bold">Suffix</label>
              <input readonly type="text" class="form-control form-control-sm" name="suffix" value="<?= htmlspecialchars($_POST['suffix'] ?? ($student['suffix'] ?? ($sf10_data['suffix'] ?? ''))) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
              <label class="form-label fw-bold">LRN</label>
              <input readonly type="text" class="form-control form-control-sm" name="lrn" value="<?= htmlspecialchars($_POST['lrn'] ?? ($student['lrn'] ?? ($sf10_data['lrn'] ?? ''))) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-2 mb-3">
              <label class="form-label fw-bold">Birthdate</label>
              <input readonly type="text" class="form-control form-control-sm" name="birthdate" value="<?= htmlspecialchars($_POST['birthdate'] ?? ($student['birthdate'] ?? ($sf10_data['birthdate'] ?? ''))) ?>">
            </div>
            <div class="col-12 col-sm-6 col-lg-1 mb-3">
              <label class="form-label fw-bold">Sex</label>
              <input readonly type="text" class="form-control form-control-sm" name="sex" value="<?= htmlspecialchars($_POST['sex'] ?? ($student['sex'] ?? ($sf10_data['sex'] ?? ''))) ?>">
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Save/Back Buttons -->
        <div class="col-12 mb-3">
          <div class="text-center d-flex justify-content-center gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary btn-lg">Save</button>
            <?php
            $safe_lrn = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($sf10_data['lrn'] ?? ''));
            $safe_first = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($sf10_data['first_name'] ?? ''));
            $safe_last = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($sf10_data['last_name'] ?? ''));
            $filename = trim($safe_lrn . '_' . $safe_first . '_' . $safe_last . '_SF10.xlsx', '_');
            $savePath = BASE_PATH . '/sf10_files' . DIRECTORY_SEPARATOR . $filename;
            // Only show download button if file exists in sf10_files
            if (file_exists($savePath)):
            ?>
              <button type="button" class="btn btn-success btn-lg" id="downloadBtn" onclick="downloadExcelFile('<?= htmlspecialchars($filename) ?>')">
                <i class="fas fa-download"></i> Download Excel
              </button>
            <?php endif; ?>
            <a onclick="window.location.href='<?= BASE_FR ?>/src/UI-teacher/index.php?page=contents/sf10'" class="btn btn-secondary btn-lg">Back</a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
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


        <div class="col-12">
          <div class="scholastic-container">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; gap: 15px;">
              <h5 style="margin: 0; flex: 1;">Scholastic Records</h5>
              <button type="button" class="btn btn-success btn-sm" id="addsr" onclick="addNewScholasticRecord()" title="Add new scholastic record" style="white-space: nowrap;">
                <i class="fas fa-plus"></i> Add Record
              </button>
            </div>
            <ul class="nav nav-tabs mb-3" id="srTabs" role="tablist">
              <?php for ($i = 1; $i <= $num_scholastic_records; $i++): ?>

                <li class="nav-item" role="presentation">
                  <button class="nav-link <?= $i === $num_scholastic_records ? 'active' : '' ?>" id="tab<?= $i ?>" data-bs-toggle="tab" data-bs-target="#sr<?= $i ?>" type="button" role="tab">
                    Scholastic <?= $i ?> <?= $i === $num_scholastic_records ? ' (Latest)' : '' ?>
                  </button>
                </li>
              <?php endfor; ?>
            </ul>


            <div class="tab-content">
              <?php for ($i = 1; $i <= $num_scholastic_records; $i++): ?>

                <div class="tab-pane fade <?= $i === $num_scholastic_records ? 'show active' : '' ?>" id="sr<?= $i ?>" role="tabpanel">
                  <div class="alert alert-info"><small>Scholastic records are read-only and cannot be edited.</small></div>
                  <label class="form-label">School</label>
                  <input type="text" class="form-control form-control-sm" disabled name="school<?= $i ?>" value="<?= htmlspecialchars($_POST['school' . $i] ?? ($scholastic_data['schools'][$i] ?? '')) ?>">
                  <label class="form-label">District</label>
                  <input type="text" class="form-control form-control-sm" disabled name="district<?= $i ?>" value="<?= htmlspecialchars($_POST['district' . $i] ?? ($scholastic_data['districts'][$i] ?? '')) ?>">
                  <label class="form-label">Division</label>
                  <input type="text" class="form-control form-control-sm" disabled name="division<?= $i ?>" value="<?= htmlspecialchars($_POST['division' . $i] ?? ($scholastic_data['divisions'][$i] ?? '')) ?>">
                  <label class="form-label">Region</label>
                  <input type="text" class="form-control form-control-sm" disabled name="region<?= $i ?>" value="<?= htmlspecialchars($_POST['region' . $i] ?? ($scholastic_data['regions'][$i] ?? '')) ?>">
                  <label class="form-label">School ID</label>
                  <input type="text" class="form-control form-control-sm" disabled name="school_id<?= $i ?>" value="<?= htmlspecialchars($_POST['school_id' . $i] ?? ($scholastic_data['school_ids'][$i] ?? '')) ?>">

                  <label class="form-label">Grade</label>
                  <input type="text" class="form-control form-control-sm" disabled name="grade<?= $i ?>"
                    value="<?= htmlspecialchars($_POST['grade' . $i] ?? ($scholastic_data['grades'][$i] ?? '')) ?>">

                  <label class="form-label">Section</label>
                  <input type="text" class="form-control form-control-sm" disabled name="section<?= $i ?>"
                    value="<?= htmlspecialchars($_POST['section' . $i] ?? ($scholastic_data['sections'][$i] ?? '')) ?>">

                  <label class="form-label">School Year</label>
                  <div class="row g-2 d-flex align-items-center mb-2">
                    <div class="col-2">
                      <input type="number" class="form-control form-control-sm" disabled name="school_year_from<?= $i ?>" placeholder="From" min="2000" max="2099" value="<?php $sy = explode('-', $_POST['school_year' . $i] ?? ($scholastic_data['school_years'][$i] ?? ''));
                                                                                                                                                                          echo htmlspecialchars($sy[0] ?? ''); ?>">
                    </div>
                    <span class="col-1 d-flex align-items-center justify-content-center" style="width:fit-content;">-</span>
                    <div class="col-2">
                      <input type="number" class="form-control form-control-sm" disabled name="school_year_to<?= $i ?>" placeholder="To" min="2000" max="2099" value="<?php echo htmlspecialchars($sy[1] ?? ''); ?>">
                    </div>
                  </div>

                  <label class="form-label">Name of Adviser</label>
                  <input type="text" class="form-control form-control-sm" disabled name="adviser_name<?= $i ?>"
                    value="<?= htmlspecialchars($_POST['adviser_name' . $i] ?? ($scholastic_data['adviser_name'][$i] ?? '')) ?>">

                  <h6 class="mt-3">Grades Table</h6>
                  <div class="table-responsive" id="tobeform" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-bordered table-sm" style="min-width: 700px;">
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
                        <?php
                        // Count non-empty subjects for this scholastic record
                        $subjects_count = 0;
                        if (!empty($scholastic_data['learning_areas'][$i])) {
                          foreach ($scholastic_data['learning_areas'][$i] as $subj) {
                            if (!empty($subj)) {
                              $subjects_count++;
                            }
                          }
                        }

                        // Loop through actual subjects only
                        $subj_idx = 0;
                        for ($r = 0; $r < 15; $r++):
                          if (!empty($scholastic_data['learning_areas'][$i][$r])):
                        ?>
                            <tr>
                              <td>
                                <input type="text" class="form-control form-control-sm" disabled
                                  name="learning_area<?= $i ?>[]"
                                  value="<?= htmlspecialchars($_POST['learning_area' . $i][$subj_idx] ?? ($scholastic_data['learning_areas'][$i][$r] ?? '')) ?>">
                              </td>
                              <td>
                                <input type="text" class="form-control form-control-sm" disabled
                                  name="q1_<?= $i ?>[]"
                                  value="<?= htmlspecialchars($_POST['q1_' . $i][$subj_idx] ?? ($scholastic_data['q1'][$i][$r] ?? '')) ?>">
                              </td>
                              <td>
                                <input type="text" class="form-control form-control-sm" disabled
                                  name="q2_<?= $i ?>[]"
                                  value="<?= htmlspecialchars($_POST['q2_' . $i][$subj_idx] ?? ($scholastic_data['q2'][$i][$r] ?? '')) ?>">
                              </td>
                              <td>
                                <input type="text" class="form-control form-control-sm" disabled
                                  name="q3_<?= $i ?>[]"
                                  value="<?= htmlspecialchars($_POST['q3_' . $i][$subj_idx] ?? ($scholastic_data['q3'][$i][$r] ?? '')) ?>">
                              </td>
                              <td>
                                <input type="text" class="form-control form-control-sm" disabled
                                  name="q4_<?= $i ?>[]"
                                  value="<?= htmlspecialchars($_POST['q4_' . $i][$subj_idx] ?? ($scholastic_data['q4'][$i][$r] ?? '')) ?>">
                              </td>
                              <td>
                                <input type="text" class="form-control form-control-sm" disabled
                                  name="final_rating_<?= $i ?>[]"
                                  value="<?= htmlspecialchars($_POST['final_rating_' . $i][$subj_idx] ?? ($scholastic_data['final_ratings'][$i][$r] ?? '')) ?>">
                              </td>
                              <td>
                                <input type="text" class="form-control form-control-sm" disabled
                                  name="remarks_table_<?= $i ?>[]"
                                  value="<?= htmlspecialchars($_POST['remarks_table_' . $i][$subj_idx] ?? ($scholastic_data['remarks_table'][$i][$r] ?? '')) ?>">
                              </td>
                            </tr>
                        <?php
                            $subj_idx++;
                          endif;
                        endfor;
                        ?>
                      </tbody>
                    </table>
                  </div>



                  <label class="form-label">General Average</label>
                  <input type="text" class="form-control form-control-sm" disabled name="general_average_<?= $i ?>" value="<?= htmlspecialchars($_POST['general_average_' . $i] ?? ($scholastic_data['general_average'][$i] ?? '')) ?>">
                  <!-- Form to add new remedial entries -->
                  <div class="card card-body bg-light mb-3">
                    <h6 class="mb-3">Add New Remedial Entry for Scholastic Record <?= $i ?> (School Year: <?= htmlspecialchars($_POST['school_year' . $i] ?? ($scholastic_data['school_years'][$i] ?? '')) ?>)</h6>
                    <input type="hidden" id="school_year_<?= $i ?>" value="<?= htmlspecialchars($_POST['school_year' . $i] ?? ($scholastic_data['school_years'][$i] ?? '')) ?>">
                    <div class="row">
                      <div class="col-md-6 mb-2">
                        <label class="form-label">Learning Area</label>
                        <select class="form-control form-control-sm" id="rem_area_<?= $i ?>" onchange="updateFinalRating(<?= $i ?>)">
                          <option value="" data-final-rating="">-- Select Learning Area --</option>
                          <?php
                          // Get subjects from this scholastic record with final ratings
                          if (!empty($scholastic_data['learning_areas'][$i])) {
                            foreach ($scholastic_data['learning_areas'][$i] as $subj_idx => $subject) {
                              if (!empty($subject)) {
                                $final_rating = $scholastic_data['final_ratings'][$i][$subj_idx] ?? '';
                                echo '<option value="' . htmlspecialchars($subject) . '" data-final-rating="' . htmlspecialchars($final_rating) . '">' . htmlspecialchars($subject) . '</option>';
                              }
                            }
                          }
                          ?>
                        </select>
                      </div>
                      <div class="col-md-2 mb-2">
                        <label class="form-label">Final Rating</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Final Rating"
                          id="rem_final_<?= $i ?>" disabled />
                      </div>
                      <div class="col-md-2 mb-2">
                        <label class="form-label">Class Mark</label>
                        <input type="text" class="form-control form-control-sm" placeholder="PASSED/FAILED"
                          id="rem_class_mark_<?= $i ?>" disabled />
                      </div>
                      <div class="col-md-2 mb-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-success w-100" onclick="addRemedialAjax(<?= $i ?>)">
                          <i class="fas fa-plus"></i> Add
                        </button>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3 mb-2">
                        <label class="form-label">Recomputed Final Grade</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Recomputed Final Grade"
                          id="rem_recomputed_<?= $i ?>" onchange="generateClassMark(<?= $i ?>)" oninput="generateClassMark(<?= $i ?>)" />
                      </div>
                      <div class="col-md-3 mb-2">
                        <label class="form-label">Remarks (Comment)</label>
                        <input type="text" class="form-control form-control-sm" placeholder="Teacher remarks"
                          id="rem_remarks_input_<?= $i ?>" />
                      </div>
                    </div>
                  </div>

                </div>


              <?php endfor; ?>
            </div>

            <!-- Remedial Records Display Section (Updated dynamically by tab) -->
            <div class="card mt-4" id="remedial-records-card">
              <div class="card-header bg-primary text-white">
                <h5 style="color: white !important;" class="mb-0">Remedial Records for <span id="active-scholastic-label">Scholastic Record <?= $num_scholastic_records ?></span></h5>
              </div>
              <div class="card-body">
                <div id="remedial-records-container">
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </form>
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

  <script src="<?= BASE_FR ?>/assets/js/bootstrap.min.js"></script>
  <script src="<?= BASE_FR ?>/assets/libs/sweetalert2/sweetalert2.min.js"></script>
  <?php if ($showSuccess): ?>
    <script>
      // Show success modal after save
      const successModal = new bootstrap.Modal(document.getElementById('successModal'));
      successModal.show();
      setTimeout(() => {
        successModal.hide();
      }, 2000);
    </script>
  <?php endif; ?>

  <script>
    // Global object to store grade entries for each scholastic record
    const gradeDataStore = {};

    function downloadExcelFile(filename) {
      if (!filename) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Filename is required'
        });
        return;
      }

      // Directly download from sf10_files folder
      const downloadUrl = '<?= $_SERVER['REQUEST_URI'] ?>' + (window.location.search ? '&' : '?') + 'download_sf10=' + encodeURIComponent(filename);
      window.location.href = downloadUrl;
    }

    function updateFinalRating(i) {
      const dropdown = document.getElementById(`rem_area_${i}`);
      const finalRatingInput = document.getElementById(`rem_final_${i}`);
      const selectedSubject = dropdown.value;

      if (selectedSubject) {
        // Find the subject in the grades table within the same tab pane
        const tabPane = document.getElementById(`sr${i}`);
        if (tabPane) {
          const table = tabPane.querySelector('table');
          if (table) {
            const rows = table.querySelectorAll('tbody tr');
            for (let row of rows) {
              // Get all TD elements - first TD has the learning area
              const tds = row.querySelectorAll('td');
              if (tds.length >= 5) {
                const firstInput = tds[0].querySelector('input');
                if (firstInput && firstInput.value === selectedSubject) {
                  // Found the subject row - get Q1-Q4 from TD 1-4
                  const q1Input = tds[1].querySelector('input');
                  const q2Input = tds[2].querySelector('input');
                  const q3Input = tds[3].querySelector('input');
                  const q4Input = tds[4].querySelector('input');

                  const q1 = parseFloat(q1Input?.value) || 0;
                  const q2 = parseFloat(q2Input?.value) || 0;
                  const q3 = parseFloat(q3Input?.value) || 0;
                  const q4 = parseFloat(q4Input?.value) || 0;

                  // Calculate average from quarters
                  const average = ((q1 + q2 + q3 + q4) / 4).toFixed(2);
                  finalRatingInput.value = average;
                  break;
                }
              }
            }
          }
        }
      } else {
        finalRatingInput.value = '';
      }

      // Update dropdown to hide already-selected subjects
      filterDropdownDuplicates(i);
    }

    function fetchRemedial(sy) {
      const studentIdInput = document.getElementById('form_student_id');
      const studentId = studentIdInput ? studentIdInput.value : '<?= $student_id ?>';

      const formData = new FormData();
      formData.append('action', 'show_remedial_records');
      formData.append('student_id', studentId);
      formData.append('school_year', sy);

      console.log('fetchRemedial called with sy:', sy, 'studentId:', studentId);

      fetch(window.location.href, {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(html => {
          console.log('AJAX Response received, length:', html.length);
          console.log('Response HTML:', html.substring(0, 200));
          document.getElementById('remedial-records-container').innerHTML = html;
          console.log('Remedial records loaded for school year:', sy);
          // Refresh all dropdown filters since remedial data has changed
          const dropdowns = document.querySelectorAll('[id^="rem_area_"]');
          dropdowns.forEach(dd => {
            const indexMatch = dd.id.match(/\d+$/);
            if (indexMatch) {
              const idx = parseInt(indexMatch[0]);
              filterDropdownDuplicates(idx);
            }
          });
        })
        .catch(error => {
          console.error('Fetch Error:', error);
        });
    }

    function deleteRemedialEntry(remedialId, schoolYear) {
      Swal.fire({
        icon: 'warning',
        title: 'Delete Remedial Entry',
        text: 'Are you sure you want to delete this remedial entry?',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: '#dc3545'
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('action', 'delete_remedial');
          formData.append('remedial_id', remedialId);

          fetch(window.location.href, {
              method: 'POST',
              body: formData
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted',
                  text: 'Remedial entry has been deleted successfully',
                  timer: 1500,
                  showConfirmButton: false
                }).then(() => {
                  // Reload remedial records for this school year
                  fetchRemedial(schoolYear);
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: data.message || 'Failed to delete remedial entry'
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the entry'
              });
            });
        }
      });
    }

    function addRemedialAjax(i) {
      const areaSelect = document.getElementById(`rem_area_${i}`);
      const finalRatingInput = document.getElementById(`rem_final_${i}`);
      const classMarkInput = document.getElementById(`rem_class_mark_${i}`);
      const recomputedInput = document.getElementById(`rem_recomputed_${i}`);
      const remarksInput = document.getElementById(`rem_remarks_input_${i}`);
      const schoolYearInput = document.getElementById(`school_year_${i}`);
      const studentIdInput = document.getElementById('form_student_id');

      const area = areaSelect.value;
      const finalRating = finalRatingInput.value;
      const classMark = classMarkInput.value;
      const recomputed = recomputedInput.value;
      const remarks = remarksInput.value;
      const schoolYear = schoolYearInput.value;
      const studentId = studentIdInput ? studentIdInput.value : '<?= $student_id ?>';

      if (!area) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Field',
          text: 'Please select a learning area'
        });
        return;
      }
      if (!recomputed) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Field',
          text: 'Please enter a recomputed final grade'
        });
        return;
      }

      // Check remedial entries count
      const remedialContainer = document.getElementById('remedial-records-container');
      const table = remedialContainer ? remedialContainer.querySelector('table tbody') : null;
      const existingEntries = table ? table.querySelectorAll('tr').length : 0;
      console.log('Existing remedial entries:', existingEntries);

      if (existingEntries >= 2) {
        Swal.fire({
          icon: 'warning',
          title: 'Limit Reached',
          text: 'Maximum 2 remedial entries allowed per scholastic record'
        });
        return;
      }

      // Send to backend via AJAX
      const formData = new FormData();
      formData.append('action', 'add_remedial');
      formData.append('student_id', studentId);
      formData.append('scholastic_index', i);
      formData.append('school_year', schoolYear);
      formData.append('area', area);
      formData.append('final_rating', finalRating);
      formData.append('class_mark', classMark);
      formData.append('recomputed_rating', recomputed);
      formData.append('remarks', remarks);

      fetch(window.location.href, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: 'Remedial data added successfully!',
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              // Clear form
              areaSelect.value = '';
              finalRatingInput.value = '';
              classMarkInput.value = '';
              recomputedInput.value = '';
              remarksInput.value = '';
              // Reload remedial records for this school year
              fetchRemedial(schoolYear);
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.message
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while adding remedial data'
          });
        });
    }

    function filterDropdownDuplicates(i) {
      const dropdown = document.getElementById(`rem_area_${i}`);
      const allOptions = dropdown.querySelectorAll('option');

      // Get all currently used areas from the remedial records table in the AJAX container
      const usedAreas = new Set();
      const remedialContainer = document.getElementById('remedial-records-container');

      if (remedialContainer) {
        const table = remedialContainer.querySelector('table tbody');
        if (table) {
          const rows = table.querySelectorAll('tr');
          rows.forEach(row => {
            // First cell contains the learning area name
            if (row.cells.length > 0) {
              const areaText = row.cells[0].textContent.trim();
              if (areaText && areaText !== '') {
                usedAreas.add(areaText);
              }
            }
          });
        }
      }

      // Show/hide options based on whether they're already selected for this school year
      allOptions.forEach(option => {
        if (option.value === '' || option.value === undefined) {
          option.disabled = false; // Always show the blank option
          option.style.display = '';
        } else if (usedAreas.has(option.value)) {
          option.disabled = true; // Disable if already used for this school year
          option.style.display = 'none';
        } else {
          option.disabled = false;
          option.style.display = '';
        }
      });
    }

    function generateClassMark(i) {
      const recomputedInput = document.getElementById(`rem_recomputed_${i}`);
      const classMarkInput = document.getElementById(`rem_class_mark_${i}`);
      const recomputedValue = parseFloat(recomputedInput.value);

      if (!isNaN(recomputedValue)) {
        // 75 is passing grade
        classMarkInput.value = recomputedValue >= 75 ? 'PASSED' : 'FAILED';
      } else {
        classMarkInput.value = '';
      }
    }

    function calculateFinalRating(i) {
      // Calculate final rating from quarters for remedial entry
      // This is for the add remedial form
      const q1Input = document.querySelector(`input[name="rem_q1_${i}"]`);
      const q2Input = document.querySelector(`input[name="rem_q2_${i}"]`);
      const q3Input = document.querySelector(`input[name="rem_q3_${i}"]`);
      const q4Input = document.querySelector(`input[name="rem_q4_${i}"]`);
      const finalRatingInput = document.getElementById(`rem_final_${i}`);

      if (q1Input && q2Input && q3Input && q4Input && finalRatingInput) {
        const q1 = parseFloat(q1Input.value) || 0;
        const q2 = parseFloat(q2Input.value) || 0;
        const q3 = parseFloat(q3Input.value) || 0;
        const q4 = parseFloat(q4Input.value) || 0;

        if (q1 || q2 || q3 || q4) {
          const average = ((q1 + q2 + q3 + q4) / 4).toFixed(2);
          finalRatingInput.value = average;
        }
      }
    }



    function recalc(i) {
      let total = 0,
        count = 0;
      let q1s = document.querySelectorAll(`[name='q1_${i}[]']`);
      let q2s = document.querySelectorAll(`[name='q2_${i}[]']`);
      let q3s = document.querySelectorAll(`[name='q3_${i}[]']`);
      let q4s = document.querySelectorAll(`[name='q4_${i}[]']`);
      let finals = document.querySelectorAll(`[name='final_rating_${i}[]']`);

      // Loop through actual number of subjects (not hardcoded 15)
      for (let r = 0; r < q1s.length; r++) {
        if (!q1s[r] || !q2s[r] || !q3s[r] || !q4s[r] || !finals[r]) break;

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

    // Initialize event listeners for all scholastic records
    let numScholasticRecords = <?= $num_scholastic_records; ?>;
    const numSubjects = <?= $num_subjects; ?>;

    for (let i = 1; i <= numScholasticRecords; i++) {
      // Add event listeners to quarterly inputs for each scholastic record
      let qInputs = document.querySelectorAll(
        `[name^='q1_${i}'],[name^='q2_${i}'],[name^='q3_${i}'],[name^='q4_${i}']`
      );
      qInputs.forEach(input => {
        input.addEventListener('input', () => recalc(i));
      });

      // Initialize calculations for each scholastic record
      recalc(i);
    }

    // Initialize tab click listeners
    document.addEventListener('DOMContentLoaded', function() {
      // Load initial remedial records - try to find any school year input
      let schoolYearInput = null;
      for (let i = 1; i <= numScholasticRecords; i++) {
        schoolYearInput = document.getElementById(`school_year_${i}`);
        if (schoolYearInput) {
          fetchRemedial(schoolYearInput.value);
          break;
        }
      }

      // Add event listeners to all scholastic tabs
      const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
      tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(e) {
          // Extract scholastic index from button ID (e.g., 'tab1' -> 1)
          const tabId = this.getAttribute('id');
          const scholasticIndex = parseInt(tabId.replace('tab', ''));

          // Check if this is a new record by checking if button text contains "(New)"
          const isNewRecord = this.textContent.includes('(New)');
          console.log('Tab clicked:', tabId, 'isNewRecord:', isNewRecord, 'text:', this.textContent);

          // Hide remedial section if this is a new record, show otherwise
          const remedialCard = document.getElementById('remedial-records-card');
          if (remedialCard) {
            remedialCard.style.display = isNewRecord ? 'none' : 'block';
            console.log('Remedial card display:', remedialCard.style.display);
          }

          // Get the school year for this scholastic record
          const schoolYearElement = document.getElementById(`school_year_${scholasticIndex}`);
          if (schoolYearElement) {
            const schoolYear = schoolYearElement.value;
            // Reload remedial records for this school year
            fetchRemedial(schoolYear);
          }
        });
      });
    });
    let ntab = 0;

    function addNewScholasticRecord() {
      // Prevent spam clicking - get the button reference
      const addButton = document.getElementById('addsr');
      if (addButton.disabled) {
        return;
      }
      addButton.disabled = true;
      // const remedialSection = document.getElementById(`remedial_section_${scholasticIndex}`);
      // if (remedialSection) {
      //   remedialSection.style.display = 'block';
      // }

      const remedialCard = document.getElementById('remedial-records-card');
      if (remedialCard) {
        remedialCard.style.display = 'none';
      }

      // Get current max tab number
      const existingTabs = document.querySelectorAll('[id^="tab"]');
      const maxTabNum = Math.max(...Array.from(existingTabs).map(t => {
        const match = t.id.match(/\d+$/);
        return match ? parseInt(match[0]) : 0;
      }));

      const newTabNum = maxTabNum + 1;

      // Remove active class from all existing tab buttons
      document.querySelectorAll('#srTabs .nav-link').forEach(btn => {
        btn.classList.remove('active');
      });

      // Remove show active class from all existing tab panes
      document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('show', 'active');
      });

      // Create new tab button
      const tabList = document.getElementById('srTabs');
      const newTabButton = document.createElement('li');
      newTabButton.className = 'nav-item';
      newTabButton.setAttribute('role', 'presentation');
      newTabButton.innerHTML = `
        <button class="nav-link active" id="tab${newTabNum}" data-bs-toggle="tab" data-bs-target="#sr${newTabNum}" type="button" role="tab">
          Scholastic ${newTabNum} (New)
        </button>
      `;
      tabList.appendChild(newTabButton);

      // Attach event listener to the new tab button
      const newTabBtn = newTabButton.querySelector('button');
      newTabBtn.addEventListener('shown.bs.tab', function(e) {
        const isNewRecord = this.textContent.includes('(New)');
        console.log('New tab clicked, isNewRecord:', isNewRecord, 'text:', this.textContent);
        const remedialCard = document.getElementById('remedial-records-card');
        if (remedialCard) {
          remedialCard.style.display = isNewRecord ? 'none' : 'block';
          console.log('Remedial card display:', remedialCard.style.display);
        }
      });

      // Create new tab content
      const tabContent = document.querySelector('.tab-content');
      const newTabPane = document.createElement('div');
      newTabPane.className = 'tab-pane fade show active';
      newTabPane.id = `sr${newTabNum}`;
      newTabPane.setAttribute('role', 'tabpanel');
      newTabPane.innerHTML = `
      <form id="submitrec" method="post" action="">
        <input type="hidden" name="is_new_scholastic_record" value="1">
        <div class="alert alert-warning"><small>This is a new scholastic record. Fill in the details below.</small></div>
        <label class="form-label">School</label>
        <input type="text" class="form-control form-control-sm" name="school${newTabNum}" value="">
        <label class="form-label">District</label>
        <input type="text" class="form-control form-control-sm" name="district${newTabNum}" value="">
        <label class="form-label">Division</label>
        <input type="text" class="form-control form-control-sm" name="division${newTabNum}" value="">
        <label class="form-label">Region</label>
        <input type="text" class="form-control form-control-sm" name="region${newTabNum}" value="">
        <label class="form-label">School ID</label>
        <input type="text" class="form-control form-control-sm" name="school_id${newTabNum}" value="">
        
        <label class="form-label">Grade</label>
        <input type="text" class="form-control form-control-sm" name="grade${newTabNum}" value="">

        <label class="form-label">Section</label>
        <input type="text" class="form-control form-control-sm" name="section${newTabNum}" value="">

        <label class="form-label">School Year</label>
        <div class="row g-2 d-flex align-items-center mb-2">
          <div class="col-2">
            <input type="number" class="form-control form-control-sm sy-from" id="school_year_from_${newTabNum}" name="school_year_from${newTabNum}" placeholder="From" min="2000" max="2099" value="" oninput="updateSchoolYearDisplay(${newTabNum})">
          </div>
          <span class="col-1 d-flex align-items-center justify-content-center" style="width:fit-content;">-</span>
          <div class="col-2">
            <input type="number" class="form-control form-control-sm sy-to" id="school_year_to_${newTabNum}" name="school_year_to${newTabNum}" placeholder="To" min="2000" max="2099" value="" onchange="validateSchoolYear(${newTabNum})" oninput="updateSchoolYearDisplay(${newTabNum})">
          </div>
        </div>
        <input type="hidden" id="school_year_concat_${newTabNum}" name="school_year${newTabNum}" value="">

        <label class="form-label">Name of Adviser</label>
        <input type="text" class="form-control form-control-sm" name="adviser_name${newTabNum}" value="">

        <h6 class="mt-3">Grades Table</h6>
        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
          <table class="table table-bordered table-sm grades-table-${newTabNum}" style="min-width: 700px;">
            <thead>
              <tr>
                <th>Learning Area</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Final Rating</th>
                <th>Remarks</th>
                <th style="width: 50px; text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody class="grades-tbody-${newTabNum}">
              <tr class="grades-row" data-row-index="1">
                <td><input type="text" class="form-control form-control-sm learning-area" name="learning_area_${newTabNum}[]" placeholder="Subject" oninput="checkGradeRowFilled(this)"></td>
                <td><input type="number" class="form-control form-control-sm q1-input" name="q1_${newTabNum}[]" placeholder="Q1" step="0.01" oninput="checkGradeRowFilled(this)"></td>
                <td><input type="number" class="form-control form-control-sm q2-input" name="q2_${newTabNum}[]" placeholder="Q2" step="0.01" oninput="checkGradeRowFilled(this)"></td>
                <td><input type="number" class="form-control form-control-sm q3-input" name="q3_${newTabNum}[]" placeholder="Q3" step="0.01" oninput="checkGradeRowFilled(this)"></td>
                <td><input type="number" class="form-control form-control-sm q4-input" name="q4_${newTabNum}[]" placeholder="Q4" step="0.01" onkeyup="calculateFinalRating(this.closest('tr'))" oninput="calculateFinalRating(this.closest('tr'))"></td>
                <td><input type="number" readonly class="form-control form-control-sm final-rating" name="final_rating_${newTabNum}[]" step="0.01" oninput="checkGradeRowFilled(this); updateRemarksAndGenAve(this)" onkeyup="updateRemarksAndGenAve(this)"></td>
                <td><input type="text" class="form-control form-control-sm remarks" name="remarks_${newTabNum}[]" readonly></td>
                <td style="text-align: center;">
                  <button type="button" class="btn btn-sm btn-success add-grade-btn" style="display: none;" onclick="addGradeEntry(this)" title="Add this row" >
                    <i class="fas fa-plus"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <label class="form-label">General Average</label>
        <input type="text" class="form-control form-control-sm" name="general_average_${newTabNum}" readonly value="">
        <div class="d-flex justify-content-center">
          <button type="button" class="btn btn-sm btn-primary mt-2" style="padding:.8rem 2rem;" onclick="addnewrecordbtn()">
          <i class="fas fa-plus"></i> SUBMIT NEW RECORD
          </button>
        </div>
      </form>
      `;
      tabContent.appendChild(newTabPane);

      numScholasticRecords = newTabNum;
      ntab = newTabNum;

      const firstRow = newTabPane.querySelector('.grades-row');
      const inputs = firstRow.querySelectorAll('input[type="number"]');
      inputs.forEach(input => {
        input.addEventListener('keyup', () => calculateFinalRating(firstRow));
        input.addEventListener('input', () => checkRowsFilledAndShowAddButton(newTabNum));
      });
      firstRow.querySelector('.learning-area').addEventListener('input', () => checkRowsFilledAndShowAddButton(newTabNum));

      // Swal.fire({
      //   icon: 'success',
      //   title: 'New Record Added',
      //   text: `Scholastic Record ${newTabNum} has been created. Fill in the details and save.`,
      //   timer: 2000,
      //   showConfirmButton: false
      // });

    }

    function addnewrecordbtn() {
      const form = document.getElementById('submitrec');
      if (!form) {
        Swal.fire({
          icon: 'error',
          title: 'Form Not Found',
          text: 'Unable to locate the form. Please refresh and try again.'
        });
        return;
      }

      // Get the scholastic index from the active tab
      const activeTab = document.querySelector('[id^="sr"][role="tabpanel"].show');
      if (!activeTab) {
        Swal.fire({
          icon: 'error',
          title: 'Tab Error',
          text: 'Unable to determine scholastic record. Please try again.'
        });
        return;
      }

      const scholasticIndex = activeTab.id.replace('sr', '');

      // Validate school year
      const fromInput = document.getElementById(`school_year_from_${scholasticIndex}`);
      const toInput = document.getElementById(`school_year_to_${scholasticIndex}`);
      const gradeInput = document.querySelector(`[name="grade${scholasticIndex}"]`);
      const sectionInput = document.querySelector(`[name="section${scholasticIndex}"]`);
      const adviserInput = document.querySelector(`[name="adviser_name${scholasticIndex}"]`);

      // Validate required fields
      if (!gradeInput || !gradeInput.value.trim()) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Grade',
          text: 'Please enter the grade level.'
        });
        return;
      }

      if (!sectionInput || !sectionInput.value.trim()) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Section',
          text: 'Please enter the section.'
        });
        return;
      }

      if (!fromInput || !fromInput.value || !toInput || !toInput.value) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing School Year',
          text: 'Please enter both From and To years.'
        });
        return;
      }

      if (!adviserInput || !adviserInput.value.trim()) {
        Swal.fire({
          icon: 'warning',
          title: 'Missing Adviser Name',
          text: 'Please enter the adviser name.'
        });
        return;
      }

      // Validate school year values
      const from = parseInt(fromInput.value);
      const to = parseInt(toInput.value);

      if (isNaN(from) || isNaN(to)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Year Format',
          text: 'Years must be numeric values.'
        });
        return;
      }

      if (to - from !== 1) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid School Year Gap',
          text: 'School year must have exactly 1 year gap (e.g., 2024-2025).'
        });
        return;
      }

      if (from < 2000) {
        Swal.fire({
          icon: 'error',
          title: 'Year Too Old',
          text: 'From year cannot be before 2000.'
        });
        return;
      }

      const currentYear = new Date().getFullYear();
      if (to > currentYear) {
        Swal.fire({
          icon: 'error',
          title: 'Year Too Far',
          text: `To year cannot be beyond the current year (${currentYear}).`
        });
        return;
      }

      // Validate that at least one grade has been entered
      if (!gradeDataStore[scholasticIndex] || gradeDataStore[scholasticIndex].length === 0) {
        console.error('DEBUG: No grades in store for index', scholasticIndex);
        console.error('DEBUG: gradeDataStore content:', JSON.stringify(gradeDataStore));
        Swal.fire({
          icon: 'warning',
          title: 'No Grades Added',
          text: 'Please add at least one learning area with grades using the Add button in the Grades Table.'
        });
        return;
      }

      // Check if any grade entry has invalid values
      const hasInvalidGrades = gradeDataStore[scholasticIndex].some(entry => {
        return entry.q1 < 50 || entry.q1 > 100 ||
          entry.q2 < 50 || entry.q2 > 100 ||
          entry.q3 < 50 || entry.q3 > 100 ||
          entry.q4 < 50 || entry.q4 > 100 ||
          entry.final_rating < 50 || entry.final_rating > 100;
      });

      if (hasInvalidGrades) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Grades',
          text: 'All grades must be between 50-100. Please check your entries.'
        });
        return;
      }

      // All validations passed - show loading and submit
      Swal.fire({
        title: 'Submitting New Record',
        text: 'Please wait while your data is being saved...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // Populate hidden inputs with stored data before submit
      populateGradeDataFromStore();

      // Submit the form
      try {
        form.submit();
        // Note: Page will reload on successful submission from PHP handler
      } catch (error) {
        console.error('Form submission error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Submission Error',
          text: 'An error occurred while submitting the form: ' + error.message
        });
      }
    }

    function addGradeRow(scholasticIndex) {

      const tbody = document.querySelector(`.grades-tbody-${scholasticIndex}`);
      const rowCount = tbody.querySelectorAll('tr').length + 1;

      const newRow = document.createElement('tr');
      newRow.className = 'grades-row';
      newRow.dataset.rowIndex = rowCount;
      newRow.innerHTML = `
        <td><input type="text" class="form-control form-control-sm learning-area" name="learning_area_${scholasticIndex}[]" placeholder="Subject" oninput="checkGradeRowFilled(this)"></td>
        <td><input type="number" class="form-control form-control-sm q1-input" name="q1_${scholasticIndex}[]" placeholder="Q1" step="0.01" oninput="checkGradeRowFilled(this)"></td>
        <td><input type="number" class="form-control form-control-sm q2-input" name="q2_${scholasticIndex}[]" placeholder="Q2" step="0.01" oninput="checkGradeRowFilled(this)"></td>
        <td><input type="number" class="form-control form-control-sm q3-input" name="q3_${scholasticIndex}[]" placeholder="Q3" step="0.01" oninput="checkGradeRowFilled(this)"></td>
        <td><input type="number" class="form-control form-control-sm q4-input" name="q4_${scholasticIndex}[]" placeholder="Q4" step="0.01" onkeyup="calculateFinalRating(this.closest('tr'))" oninput="calculateFinalRating(this.closest('tr'))"></td>
        <td><input type="number" readonly class="form-control form-control-sm final-rating" name="final_rating_${scholasticIndex}[]" step="0.01" oninput="checkGradeRowFilled(this); updateRemarksAndGenAve(this)" onkeyup="updateRemarksAndGenAve(this)"></td>
        <td><input type="text" class="form-control form-control-sm remarks" name="remarks_${scholasticIndex}[]" readonly></td>
        <td style="text-align: center;">
          <button type="button" class="btn btn-sm btn-success add-grade-btn" style="display: none;" onclick="addGradeEntry(this)" title="Add this row">
            <i class="fas fa-plus"></i>
          </button>
        </td>
      `;

      tbody.appendChild(newRow);

      // Add input listeners for the new row
      const inputs = newRow.querySelectorAll('input[type="number"]');
      inputs.forEach(input => {
        input.addEventListener('keyup', () => calculateFinalRating(newRow));
        input.addEventListener('input', () => checkRowsFilledAndShowAddButton(scholasticIndex));
      });
      newRow.querySelector('.learning-area').addEventListener('input', () => checkRowsFilledAndShowAddButton(scholasticIndex));

      // Hide add button initially
      document.getElementById(`addRowBtn${scholasticIndex}`).style.display = 'none';
    }

    function deleteGradeRow(button) {
      const row = button.closest('tr');
      const tbody = row.closest('tbody');
      const match = tbody.className.match(/grades-tbody-(\d+)/);
      if (!match || !match[1]) {
        console.error('Could not extract scholastic index from tbody class:', tbody.className);
        return;
      }
      const scholasticIndex = match[1];

      row.remove();
      checkRowsFilledAndShowAddButton(scholasticIndex);
    }


    // Form validation before submit
    function validateFormBeforeSubmit() {
      const tabElements = document.querySelectorAll('[id^="sr"]');

      for (let elem of tabElements) {
        // Get the tab number from the element ID
        const tabNum = elem.id.replace('sr', '');

        // Check if this is a new record tab (not from SF9)
        const fromInput = document.getElementById(`school_year_from_${tabNum}`);
        if (fromInput && !fromInput.disabled) {
          // This is a new record, validate school year
          const toInput = document.getElementById(`school_year_to_${tabNum}`);
          const from = fromInput.value;
          const to = toInput.value;

          // Skip if both are empty (optional)
          if (!from && !to) {
            continue;
          }

          // If one is filled, both must be filled
          if ((from && !to) || (!from && to)) {
            Swal.fire({
              icon: 'error',
              title: 'Invalid School Year',
              text: 'Both From and To years must be filled in Scholastic Record ' + tabNum
            });
            return false;
          }

          // If both are filled, validate
          if (from && to) {
            const fromInt = parseInt(from);
            const toInt = parseInt(to);

            if (toInt - fromInt !== 1) {
              Swal.fire({
                icon: 'error',
                title: 'Invalid School Year Gap',
                text: 'School year in Scholastic Record ' + tabNum + ' must have exactly 1 year gap (e.g., 2024-2025).'
              });
              return false;
            }

            if (fromInt < 2000) {
              Swal.fire({
                icon: 'error',
                title: 'Year Too Old',
                text: 'From year in Scholastic Record ' + tabNum + ' cannot be before 2000.'
              });
              return false;
            }

            const currentYear = new Date().getFullYear();
            if (toInt > currentYear) {
              Swal.fire({
                icon: 'error',
                title: 'Year Too Far',
                text: 'To year in Scholastic Record ' + tabNum + ' cannot be beyond the current year (' + currentYear + ').'
              });
              return false;
            }
          }
        }
      }

      return true;
    }

    function populateGradeDataFromStore() {
      // Create hidden inputs for stored grade data
      const form = document.getElementById('submitrec');
      if (!form) return;

      // Remove any existing grade data inputs
      form.querySelectorAll('[name^="stored_grade_data_"]').forEach(el => el.remove());

      // Add stored grade data as hidden inputs
      for (const scholasticIndex in gradeDataStore) {
        if (gradeDataStore[scholasticIndex].length > 0) {
          const hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = `stored_grade_data_${scholasticIndex}`;
          hiddenInput.value = JSON.stringify(gradeDataStore[scholasticIndex]);
          form.appendChild(hiddenInput);
        }
      }
    }

    function calculateFinalRating(row) {
      const q1 = parseFloat(row.querySelector('.q1-input').value) || 0;
      const q2 = parseFloat(row.querySelector('.q2-input').value) || 0;
      const q3 = parseFloat(row.querySelector('.q3-input').value) || 0;
      const q4 = parseFloat(row.querySelector('.q4-input').value) || 0;

      if (q1 || q2 || q3 || q4) {
        const final = ((q1 + q2 + q3 + q4) / 4).toFixed(2);
        row.querySelector('.final-rating').value = final;
        updateRemarksAndGenAve(row.querySelector('.final-rating'));
      } else {
        row.querySelector('.final-rating').value = '';
        row.querySelector('.remarks').value = '';
      }
    }

    function updateRemarksAndGenAve(finalRatingInput) {
      const row = finalRatingInput.closest('tr');
      const finalRating = parseFloat(finalRatingInput.value);
      const remarksField = row.querySelector('.remarks');

      // Update remarks based on final rating
      if (finalRatingInput.value) {
        if (finalRating >= 75) {
          remarksField.value = 'PASSED';
        } else {
          remarksField.value = 'RETAINED';
        }
      } else {
        remarksField.value = '';
      }

      // Calculate and update general average
      const tbody = row.closest('tbody');
      const match = tbody.className.match(/grades-tbody-(\d+)/);
      if (!match || !match[1]) {
        console.error('Could not extract scholastic index from tbody class:', tbody.className);
        return;
      }
      const scholasticIndex = match[1];
      calculateGeneralAverage(scholasticIndex);

      // Check if button should be shown
      checkGradeRowFilled(finalRatingInput);
    }

    function calculateGeneralAverage(scholasticIndex) {
      let totalFinal = 0;
      let countFinal = 0;

      // Add all grades from the stored array
      if (gradeDataStore[scholasticIndex] && gradeDataStore[scholasticIndex].length > 0) {
        gradeDataStore[scholasticIndex].forEach(entry => {
          totalFinal += entry.final_rating;
          countFinal++;
        });
      }

      // Also add the current input row if it has a final rating
      const tbody = document.querySelector(`.grades-tbody-${scholasticIndex}`);
      const inputRow = tbody.querySelector('.grades-row');
      if (inputRow) {
        const finalRating = parseFloat(inputRow.querySelector('.final-rating').value);
        if (!isNaN(finalRating) && finalRating) {
          totalFinal += finalRating;
          countFinal++;
        }
      }

      const genAveInput = document.querySelector(`[name="general_average_${scholasticIndex}"]`);
      if (genAveInput && countFinal > 0) {
        const genAve = (totalFinal / countFinal).toFixed(2);
        genAveInput.value = genAve;
      } else if (genAveInput) {
        genAveInput.value = '';
      }
    }

    function checkGradeRowFilled(input) {
      const row = input.closest('tr');
      const learningArea = row.querySelector('.learning-area').value.trim();
      const q1 = row.querySelector('.q1-input').value;
      const q2 = row.querySelector('.q2-input').value;
      const q3 = row.querySelector('.q3-input').value;
      const q4 = row.querySelector('.q4-input').value;
      const finalRating = row.querySelector('.final-rating').value;

      // Check if all fields are filled (learning area must not be empty, all quarters must have values, final rating must have value)
      const allFilled = learningArea && q1 && q2 && q3 && q4 && finalRating !== '';

      const addBtn = row.querySelector('.add-grade-btn');
      if (addBtn) {
        console.log(`Checking row: Learning Area="${learningArea}", Q1=${q1}, Q2=${q2}, Q3=${q3}, Q4=${q4}, FinalRating="${finalRating}", allFilled=${allFilled}`);
        addBtn.style.display = allFilled ? 'inline-block' : 'none';
      }
    }

    function addGradeEntry(button) {
      const row = button.closest('tr');
      const tbody = row.closest('tbody');
      const match = tbody.className.match(/grades-tbody-(\d+)/);
      if (!match || !match[1]) {
        console.error('Could not extract scholastic index from tbody class:', tbody.className);
        return;
      }
      const scholasticIndex = match[1];

      // Get row data
      const learningArea = row.querySelector('.learning-area').value;
      const q1 = parseFloat(row.querySelector('.q1-input').value);
      const q2 = parseFloat(row.querySelector('.q2-input').value);
      const q3 = parseFloat(row.querySelector('.q3-input').value);
      const q4 = parseFloat(row.querySelector('.q4-input').value);
      const finalRating = parseFloat(row.querySelector('.final-rating').value);
      const remarks = row.querySelector('.remarks').value;

      // Validate all grades are between 50-100
      if (q1 < 50 || q1 > 100 || q2 < 50 || q2 > 100 || q3 < 50 || q3 > 100 || q4 < 50 || q4 > 100 || finalRating < 50 || finalRating > 100) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Grades',
          text: 'All grades (Q1, Q2, Q3, Q4, and Final Rating) must be between 50-100'
        });
        return;
      }

      // Initialize array for this scholastic record if not exists
      if (!gradeDataStore[scholasticIndex]) {
        gradeDataStore[scholasticIndex] = [];
      }

      // Check if learning area already exists in array (must be unique)
      const learningAreaExists = gradeDataStore[scholasticIndex].some(entry => entry.learning_area.toLowerCase().trim() === learningArea.toLowerCase().trim());
      if (learningAreaExists) {
        Swal.fire({
          icon: 'error',
          title: 'Duplicate Learning Area',
          text: `"${learningArea}" has already been added. Each learning area must be unique.`
        });
        return;
      }

      // Store entry in array
      gradeDataStore[scholasticIndex].push({
        learning_area: learningArea,
        q1: q1,
        q2: q2,
        q3: q3,
        q4: q4,
        final_rating: finalRating,
        remarks: remarks
      });

      // Clear current row for new entry
      row.querySelector('.learning-area').value = '';
      row.querySelector('.q1-input').value = '';
      row.querySelector('.q2-input').value = '';
      row.querySelector('.q3-input').value = '';
      row.querySelector('.q4-input').value = '';
      row.querySelector('.final-rating').value = '';
      row.querySelector('.remarks').value = '';
      button.style.display = 'none';

      // Display added entries summary
      displayGradeEntriesSummary(scholasticIndex);


      calculateGeneralAverage(scholasticIndex);
    }

    function displayGradeEntriesSummary(scholasticIndex) {
      const tbody = document.querySelector(`.grades-tbody-${scholasticIndex}`);

      // Remove any previously added summary rows (but keep the input row at the end)
      const existingSummaryRows = tbody.querySelectorAll('tr.grade-entry-row');
      existingSummaryRows.forEach(row => row.remove());

      // Get the input row (first/only row)
      const inputRow = tbody.querySelector('tr.grades-row');

      // Insert stored entries as rows before the input row
      gradeDataStore[scholasticIndex].forEach((entry, index) => {
        const summaryRow = document.createElement('tr');
        summaryRow.className = 'grade-entry-row';
        summaryRow.innerHTML = `
          <td><input type="text" class="form-control form-control-sm" value="${entry.learning_area}" readonly></td>
          <td><input type="number" class="form-control form-control-sm" value="${entry.q1}" readonly></td>
          <td><input type="number" class="form-control form-control-sm" value="${entry.q2}" readonly></td>
          <td><input type="number" class="form-control form-control-sm" value="${entry.q3}" readonly></td>
          <td><input type="number" class="form-control form-control-sm" value="${entry.q4}" readonly></td>
          <td><input type="number" class="form-control form-control-sm" value="${entry.final_rating}" readonly></td>
          <td><input type="text" class="form-control form-control-sm" value="${entry.remarks}" readonly></td>
          <td style="text-align: center;">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeGradeEntry(${scholasticIndex}, ${index})" title="Delete">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        `;
        // Insert before the input row
        tbody.insertBefore(summaryRow, inputRow);
      });
    }

    function removeGradeEntry(scholasticIndex, index) {
      if (gradeDataStore[scholasticIndex]) {
        gradeDataStore[scholasticIndex].splice(index, 1);
        displayGradeEntriesSummary(scholasticIndex);
        calculateGeneralAverage(scholasticIndex);
      }
    }

    function checkRowsFilledAndShowAddButton(scholasticIndex) {
      const tbody = document.querySelector(`.grades-tbody-${scholasticIndex}`);
      if (!tbody) return; // Exit if tbody doesn't exist

      const rows = tbody.querySelectorAll('.grades-row');
      const addBtn = document.getElementById(`addRowBtn${scholasticIndex}`);

      if (!addBtn) return; // Exit if add button doesn't exist

      let lastRowFilled = false;

      if (rows.length > 0) {
        const lastRow = rows[rows.length - 1];
        const learningArea = lastRow.querySelector('.learning-area').value.trim();
        const q1 = lastRow.querySelector('.q1-input').value;
        const q2 = lastRow.querySelector('.q2-input').value;
        const q3 = lastRow.querySelector('.q3-input').value;
        const q4 = lastRow.querySelector('.q4-input').value;

        // Show button if last row has learning area OR any quarter filled
        lastRowFilled = !!(learningArea || q1 || q2 || q3 || q4);
      }

      addBtn.style.display = lastRowFilled ? 'inline-block' : 'none';
    }

    // School Year validation and concatenation
    function updateSchoolYearDisplay(tabNum) {
      const fromInput = document.getElementById(`school_year_from_${tabNum}`);
      const toInput = document.getElementById(`school_year_to_${tabNum}`);
      const concatInput = document.getElementById(`school_year_concat_${tabNum}`);

      if (fromInput && toInput && concatInput) {
        const from = fromInput.value;
        const to = toInput.value;
        if (from && to) {
          concatInput.value = `${from}-${to}`;
        } else {
          concatInput.value = '';
        }
      }
    }

    function validateSchoolYear(tabNum) {
      const fromInput = document.getElementById(`school_year_from_${tabNum}`);
      const toInput = document.getElementById(`school_year_to_${tabNum}`);

      if (!fromInput || !toInput) return true;

      const from = parseInt(fromInput.value);
      const to = parseInt(toInput.value);

      // If empty, skip validation (optional field)
      if (!fromInput.value && !toInput.value) {
        return true;
      }

      // Both must be filled if one is filled
      if ((fromInput.value && !toInput.value) || (!fromInput.value && toInput.value)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid School Year',
          text: 'Both From and To years must be filled.'
        });
        return false;
      }

      // Year gap must be exactly 1
      if (to - from !== 1) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid School Year Gap',
          text: 'School year must have exactly 1 year gap (e.g., 2024-2025).'
        });
        fromInput.value = '';
        toInput.value = '';
        return false;
      }

      // From year cannot be before 2000
      if (from < 2000) {
        Swal.fire({
          icon: 'error',
          title: 'Year Too Old',
          text: 'From year cannot be before 2000.'
        });
        fromInput.value = '';
        toInput.value = '';
        return false;
      }

      // To year cannot be beyond current year (2026)
      const currentYear = new Date().getFullYear();
      if (to > currentYear) {
        Swal.fire({
          icon: 'error',
          title: 'Year Too Far',
          text: `To year cannot be beyond the current year (${currentYear}).`
        });
        fromInput.value = '';
        toInput.value = '';
        return false;
      }

      updateSchoolYearDisplay(tabNum);
      return true;
    }

    // Debounce function for preventing spam clicks
    function debounce(func, delay) {
      let timeout;
      return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
      };
    }

    // Modify addGradeRow to prevent spam
    const originalAddGradeRow = addGradeRow;
    const debouncedAddGradeRow = {};

    function addGradeRowDebounced(scholasticIndex) {
      if (!debouncedAddGradeRow[scholasticIndex]) {
        debouncedAddGradeRow[scholasticIndex] = debounce(() => {
          originalAddGradeRow(scholasticIndex);
          const addBtn = document.getElementById(`addRowBtn${scholasticIndex}`);
          if (addBtn) {
            addBtn.disabled = false;
          }
        }, 300);
      }

      const addBtn = document.getElementById(`addRowBtn${scholasticIndex}`);
      if (addBtn && !addBtn.disabled) {
        addBtn.disabled = true;
        debouncedAddGradeRow[scholasticIndex]();
      }
    }
  </script>
</body>

</html>