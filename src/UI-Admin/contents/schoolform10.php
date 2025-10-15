<?php
require_once 'C:/xampp/htdocs/sta.MariaSystem/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$pdo = new PDO("mysql:host=localhost;dbname=stamariadb;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function build_sf10_filename($lrn, $first, $last) {
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

$saveDir = 'C:/xampp/htdocs/sta.MariaSystem/sf10_files';
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

$showSuccess = false;
$successMessage = '';
$errorMessage = '';

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

    // Scholastic Records Arrays
    $grades = [];
    $sections = [];
    $school_years = [];
    $advisers = [];
    $learning_areas_all = [];
    $q1_all = [];
    $q2_all = [];
    $q3_all = [];
    $q4_all = [];
    $final_ratings_all = [];
    $remarks_all = [];

    for($i=1;$i<=4;$i++){
        $grades[$i] = $_POST['grade'.$i] ?? '';
        $sections[$i] = $_POST['section'.$i] ?? '';
        $school_years[$i] = $_POST['school_year'.$i] ?? '';
        $advisers[$i] = $_POST['adviser_name'.$i] ?? '';
        $learning_areas_all[$i] = $_POST['learning_area'.$i] ?? [];
        $q1_all[$i] = $_POST['q1_'.$i] ?? [];
        $q2_all[$i] = $_POST['q2_'.$i] ?? [];
        $q3_all[$i] = $_POST['q3_'.$i] ?? [];
        $q4_all[$i] = $_POST['q4_'.$i] ?? [];
        $final_ratings_all[$i] = $_POST['final_rating_'.$i] ?? [];
        $remarks_all[$i] = $_POST['remarks_table_'.$i] ?? [];
    }

    try {
        $template_path = 'C:/xampp/htdocs/sta.MariaSystem/src/UI-Admin/contents/sf10/sf10.xlsx';
        $spreadsheet = IOFactory::load($template_path);
        $sheet = $spreadsheet->getSheet(0);

        // Personal Info
        $sheet->setCellValue('E9', $last_name);
        $sheet->setCellValue('R9', $first_name);
        $sheet->setCellValue('AD9', $suffix);
        $sheet->setCellValue('AQ9', $middle_name);
        $sheet->setCellValueExplicit('J10', $lrn, DataType::TYPE_STRING);
        $sheet->setCellValue('V10', $birthdate);
        $sheet->setCellValue('AT10', $sex);

        // Eligibility
        $sheet->setCellValue('K14', $kinder_progress_report);
        $sheet->setCellValue('U14', $eccd_checklist);
        $sheet->setCellValue('AE14', $kinder_certificate);
        $sheet->getStyle('K14')->getFont()->setSize(18)->setBold(true);
        $sheet->getStyle('U14')->getFont()->setSize(18)->setBold(true);
        $sheet->getStyle('AE14')->getFont()->setSize(18)->setBold(true);

        $sheet->setCellValue('F15', $school_name);
        $sheet->setCellValue('T15', $school_id);
        $sheet->setCellValue('Z15', $school_address);

        $sheet->setCellValue('B18', $pept_passer);
        $sheet->getStyle('B18')->getFont()->setSize(18)->setBold(true);
        $sheet->setCellValue('J18', $pept_text);
        $sheet->getStyle('J18')->getFont()->setSize(11)->setBold(false);
        $sheet->setCellValue('W18', $exam_date);
        $sheet->setCellValue('AC18', $others_check);
        $sheet->getStyle('AC18')->getFont()->setSize(18)->setBold(true);
        $sheet->setCellValue('AQ18', $others_text);
        $sheet->setCellValue('L19', $testing_center_name);
        $sheet->setCellValue('M19', $testing_center_address);
        $sheet->setCellValue('AJ19', $remark);

        // Corrected Scholastic Records Mapping
        $scholastic_positions = [
            1 => ['grade'=>'F25','section'=>'J25','sy'=>'S25','adviser'=>'H26','start_row'=>30,'start_col'=>'B','q1'=>'K','q2'=>'L','q3'=>'N','q4'=>'O','final'=>'P','remarks'=>'S'],
            2 => ['grade'=>'Z25','section'=>'AE25','sy'=>'AU25','adviser'=>'AC26','start_row'=>30,'start_col'=>'V','q1'=>'AJ','q2'=>'AM','q3'=>'AO','q4'=>'AR','final'=>'AT','remarks'=>'AW'],
            3 => ['grade'=>'F54','section'=>'J54','sy'=>'S54','adviser'=>'H55','start_row'=>60,'start_col'=>'B','q1'=>'K','q2'=>'L','q3'=>'N','q4'=>'O','final'=>'P','remarks'=>'S'],
            4 => ['grade'=>'Z54','section'=>'AE54','sy'=>'AU54','adviser'=>'AC55','start_row'=>60,'start_col'=>'V','q1'=>'AJ','q2'=>'AM','q3'=>'AO','q4'=>'AR','final'=>'AT','remarks'=>'AW'],
        ];

        for($i=1;$i<=4;$i++){
            $pos = $scholastic_positions[$i];
            $sheet->setCellValue($pos['grade'],$grades[$i]);
            $sheet->setCellValue($pos['section'],$sections[$i]);
            $sheet->setCellValue($pos['sy'],$school_years[$i]);
            $sheet->setCellValue($pos['adviser'],$advisers[$i]);
            for($r=0;$r<15;$r++){
                $row = $pos['start_row']+$r;
                $sheet->setCellValue($pos['start_col'].$row,$learning_areas_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q1'].$row,$q1_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q2'].$row,$q2_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q3'].$row,$q3_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['q4'].$row,$q4_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['final'].$row,$final_ratings_all[$i][$r] ?? '');
                $sheet->setCellValue($pos['remarks'].$row,$remarks_all[$i][$r] ?? '');
            }
        }

        // DepEd Logo
        $drawing = new Drawing();
        $drawing->setName('DepEd Logo');
        $drawing->setDescription('DepEd Logo');
        $drawing->setPath('C:/xampp/htdocs/sta.MariaSystem/assets/image/deped.png');
        $drawing->setCoordinates('A1');
        $drawing->setWidth(80);
        $drawing->setHeight(80);
        $drawing->setWorksheet($sheet);

        $filename = build_sf10_filename($lrn, $first_name, $last_name);
        $savePath = $saveDir . DIRECTORY_SEPARATOR . $filename;
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($savePath);
        $showSuccess = true;
        $successMessage = "SF10 saved: {$filename}";
    } catch (Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['download']) && $_GET['download'] == '1' && $student) {
    $fileName = build_sf10_filename($student['lrn'], $student['fname'], $student['lname']);
    $filePath = $saveDir . DIRECTORY_SEPARATOR . $fileName;
    if (file_exists($filePath)) {
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
    } else {
        die("File not found.");
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
body { font-family: 'Poppins', Arial, sans-serif; background:#f4f5f7; margin:0; padding:0; }
.header-brand { border-bottom:1px solid rgba(0,0,0,.2); height:75px; background:#d32f2f; }
.header-brand img { width:65px; height:65px; border-radius:50%; margin-right:15px; object-fit:cover; }
.header-brand h4 { font-size:1.3rem; font-weight:700; color:#fff; margin:0; }
.sidebar, .eligibility-container, .scholastic-container { background:#fff; padding:20px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.08); margin-bottom:20px; }
.sidebar h5, .eligibility-container h5, .scholastic-container h5 { font-weight:700; font-size:1.2rem; color:#333; margin-bottom:15px; text-align:center; }
.form-label { font-weight:600; margin-top:10px; }
.form-control.form-control-sm { font-weight:500; padding:8px 10px; border-radius:6px; border:1px solid #ccc; }
.btn-lg { padding:10px 18px; font-size:16px; border-radius:6px; }
.table input { width:100%; }
</style>
</head>
<body>
<div class="d-flex align-items-center justify-content-between col-12 m-0 p-0 header-brand">
  <div class="d-flex align-items-center ps-4">
    <img src="/sta.MariaSystem/assets/image/logo2.png" alt="Logo">
    <h4>STA.MARIA WEB SYSTEM</h4>
  </div>
</div>
<div class="container-fluid p-3">
  <form method="post">
    <div class="row">
      <div class="col-md-4 col-sm-12">
        <div class="sidebar">
          <h5>Learner's Personal Information</h5>
          <label class="form-label">Last Name</label>
          <input type="text" class="form-control form-control-sm" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? $student['lname'] ?? '') ?>">
          <label class="form-label">First Name</label>
          <input type="text" class="form-control form-control-sm" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? $student['fname'] ?? '') ?>">
          <label class="form-label">Middle Name</label>
          <input type="text" class="form-control form-control-sm" name="middle_name" value="<?= htmlspecialchars($_POST['middle_name'] ?? $student['mname'] ?? '') ?>">
          <label class="form-label">Name Ext.</label>
          <input type="text" class="form-control form-control-sm" name="suffix" value="<?= htmlspecialchars($_POST['suffix'] ?? $student['suffix'] ?? '') ?>">
          <label class="form-label">LRN</label>
          <input type="text" class="form-control form-control-sm" name="lrn" value="<?= htmlspecialchars($_POST['lrn'] ?? $student['lrn'] ?? '') ?>">
          <label class="form-label">Birthdate (MM/DD/YY)</label>
          <input type="text" class="form-control form-control-sm" name="birthdate" value="<?= htmlspecialchars($_POST['birthdate'] ?? $student['birthdate'] ?? '') ?>">
          <label class="form-label">Sex</label>
          <input type="text" class="form-control form-control-sm" name="sex" value="<?= htmlspecialchars($_POST['sex'] ?? $student['sex'] ?? '') ?>">
          <div class="text-center mt-3 d-flex justify-content-center gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary btn-lg">Save</button>
            <?php if ($student):
                  $downloadUrl = htmlspecialchars($_SERVER['PHP_SELF']) . '?student_id=' . urlencode($student_id) . '&download=1';
            ?>
              <a href="<?= $downloadUrl ?>" class="btn btn-success btn-lg">Download</a>
            <?php else: ?>
              <a href="#" class="btn btn-success btn-lg disabled">Download</a>
            <?php endif; ?>
            <button type="button" class="btn btn-secondary btn-lg" onclick="window.history.back();">Back</button>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-12">
        <div class="eligibility-container">
          <h5>Elementary School Eligibility</h5>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="kinder_progress_report" id="kinder_progress_report" value="1" <?= isset($_POST['kinder_progress_report']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="kinder_progress_report">Kinder Progress Report</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="eccd_checklist" id="eccd_checklist" value="1" <?= isset($_POST['eccd_checklist']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="eccd_checklist">ECCD Checklist</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="kinder_certificate" id="kinder_certificate" value="1" <?= isset($_POST['kinder_certificate']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="kinder_certificate">Kindergarten Certificate of Completion</label>
          </div>
          <label class="form-label">Name of School</label>
          <input type="text" class="form-control form-control-sm" name="school_name" value="<?= htmlspecialchars($_POST['school_name'] ?? '') ?>">
          <label class="form-label">School ID</label>
          <input type="text" class="form-control form-control-sm" name="school_id" value="<?= htmlspecialchars($_POST['school_id'] ?? '') ?>">
          <label class="form-label">Address of School</label>
          <input type="text" class="form-control form-control-sm" name="school_address" value="<?= htmlspecialchars($_POST['school_address'] ?? '') ?>">
          <h6 class="mt-3">Other Credential Presented</h6>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="pept_passer" id="pept_passer" value="1" <?= isset($_POST['pept_passer']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="pept_passer">PEPT Passer Rating</label>
          </div>
          <label class="form-label">PEPT Passer Rating (text)</label>
          <input type="text" class="form-control form-control-sm" name="pept_text" value="<?= htmlspecialchars($_POST['pept_text'] ?? '') ?>">
          <label class="form-label">Date of Examination/Assessment (dd/mm/yyyy)</label>
          <input type="text" class="form-control form-control-sm" name="exam_date" value="<?= htmlspecialchars($_POST['exam_date'] ?? '') ?>">
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="others_check" id="others_check" value="1" <?= isset($_POST['others_check']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="others_check">Others, pls specify</label>
          </div>
          <label class="form-label">Others (text)</label>
          <input type="text" class="form-control form-control-sm" name="others_text" value="<?= htmlspecialchars($_POST['others_text'] ?? '') ?>">
          <label class="form-label">Name of Testing Center</label>
          <input type="text" class="form-control form-control-sm" name="testing_center_name" value="<?= htmlspecialchars($_POST['testing_center_name'] ?? '') ?>">
          <label class="form-label">Address of Testing Center</label>
          <input type="text" class="form-control form-control-sm" name="testing_center_address" value="<?= htmlspecialchars($_POST['testing_center_address'] ?? '') ?>">
          <label class="form-label">Remark</label>
          <input type="text" class="form-control form-control-sm" name="remark" value="<?= htmlspecialchars($_POST['remark'] ?? '') ?>">
        </div>
      </div>

      <!-- Scholastic Records Tabs -->
      <div class="col-md-4 col-sm-12">
        <div class="scholastic-container">
          <h5>Scholastic Records</h5>
          <ul class="nav nav-tabs mb-3" id="srTabs" role="tablist">
            <?php for($i=1;$i<=4;$i++): ?>
              <li class="nav-item" role="presentation">
                <button class="nav-link <?= $i===1?'active':'' ?>" id="tab<?=$i?>" data-bs-toggle="tab" data-bs-target="#sr<?=$i?>" type="button" role="tab">
                  Scholastic <?=$i?>
                </button>
              </li>
            <?php endfor; ?>
          </ul>
          <div class="tab-content">
            <?php for($i=1;$i<=4;$i++): ?>
              <div class="tab-pane fade <?= $i===1?'show active':'' ?>" id="sr<?=$i?>" role="tabpanel">
                <label class="form-label">Grade</label>
                <input type="text" class="form-control form-control-sm" name="grade<?=$i?>" value="<?= htmlspecialchars($_POST['grade'.$i] ?? '') ?>">
                <label class="form-label">Section</label>
                <input type="text" class="form-control form-control-sm" name="section<?=$i?>" value="<?= htmlspecialchars($_POST['section'.$i] ?? '') ?>">
                <label class="form-label">School Year</label>
                <input type="text" class="form-control form-control-sm" name="school_year<?=$i?>" value="<?= htmlspecialchars($_POST['school_year'.$i] ?? '') ?>">
                <label class="form-label">Name of Adviser</label>
                <input type="text" class="form-control form-control-sm" name="adviser_name<?=$i?>" value="<?= htmlspecialchars($_POST['adviser_name'.$i] ?? '') ?>">
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
                      <?php for($r=0;$r<15;$r++): ?>
                        <tr>
                          <td><input type="text" class="form-control form-control-sm" name="learning_area<?=$i?>[]" value="<?= htmlspecialchars($_POST['learning_area'.$i][$r] ?? '') ?>"></td>
                          <td><input type="text" class="form-control form-control-sm" name="q1_<?=$i?>[]" value="<?= htmlspecialchars($_POST['q1_'.$i][$r] ?? '') ?>"></td>
                          <td><input type="text" class="form-control form-control-sm" name="q2_<?=$i?>[]" value="<?= htmlspecialchars($_POST['q2_'.$i][$r] ?? '') ?>"></td>
                          <td><input type="text" class="form-control form-control-sm" name="q3_<?=$i?>[]" value="<?= htmlspecialchars($_POST['q3_'.$i][$r] ?? '') ?>"></td>
                          <td><input type="text" class="form-control form-control-sm" name="q4_<?=$i?>[]" value="<?= htmlspecialchars($_POST['q4_'.$i][$r] ?? '') ?>"></td>
                          <td><input type="text" class="form-control form-control-sm" name="final_rating_<?=$i?>[]" value="<?= htmlspecialchars($_POST['final_rating_'.$i][$r] ?? '') ?>"></td>
                          <td><input type="text" class="form-control form-control-sm" name="remarks_table_<?=$i?>[]" value="<?= htmlspecialchars($_POST['remarks_table_'.$i][$r] ?? '') ?>"></td>
                        </tr>
                      <?php endfor; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endfor; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if($showSuccess): ?>
<script>
  const successModal = new bootstrap.Modal(document.getElementById('successModal'));
  successModal.show();
  setTimeout(() => { successModal.hide(); }, 2000);
</script>
<?php endif; ?>
</body>
</html>
