<?php
session_start();
require_once 'C:/xampp/htdocs/sta.MariaSystem/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$templatePath = 'C:/xampp/htdocs/sta.MariaSystem/src/UI-Admin/contents/sf5/sf5.xlsx';
$saveDir = 'C:/xampp/htdocs/sta.MariaSystem/sf5_files';
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

$totalRows = 59;
$skipRow = 33;

$formData = $_SESSION['sf5_form'] ?? [];
$downloadLink = $_SESSION['sf5_download'] ?? '';

/* -------- yung download -------- */
if (isset($_GET['download'])) {
    if (!empty($_SESSION['sf5_download'])) {
        $file = $saveDir . DIRECTORY_SEPARATOR . $_SESSION['sf5_download'];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else die("File not found!");
    } else die("No file to download.");
}

/* -------- save o generate -------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    $formData['school_year'] = $_POST['school_year'] ?? '';
    $formData['curriculum'] = $_POST['curriculum'] ?? '';
    $formData['grade_level'] = $_POST['grade_level'] ?? '';
    $formData['section'] = $_POST['section'] ?? '';

    // learners (rows 13 to 59 except 33 since cell total ng male yon)
    for ($r = 13; $r <= $totalRows; $r++) {
        if ($r == $skipRow) continue;
        $formData['lrn'][$r] = $_POST['lrn'][$r] ?? '';
        $formData['name'][$r] = $_POST['name'][$r] ?? '';
        $formData['average'][$r] = $_POST['average'][$r] ?? '';
        $formData['action'][$r] = $_POST['action'][$r] ?? '';
        $formData['did_not_meet'][$r] = $_POST['did_not_meet'][$r] ?? '';
    }

    // male/female total
    $formData['male_total'] = (int)($_POST['male_total'] ?? 0);
    $formData['female_total'] = (int)($_POST['female_total'] ?? 0);
    $formData['combined_total'] = (int)$formData['male_total'] + (int)$formData['female_total'];

    // Summary (promoted / conditional / retained)
    $summaryRows = ['promoted','conditional','retained'];
    foreach ($summaryRows as $status) {
        $formData['summary'][$status]['male'] = (int)($_POST['summary'][$status]['male'] ?? 0);
        $formData['summary'][$status]['female'] = (int)($_POST['summary'][$status]['female'] ?? 0);
        $formData['summary'][$status]['total'] = (int)($_POST['summary'][$status]['total'] ?? 0);
    }

    // Learning progress categories
    $progressRows = [
        'did_not_meet'=>'Did Not Meet Expectations (74 and below)',
        'fairly_satisfactory'=>'Fairly Satisfactory (75-79)',
        'satisfactory'=>'Satisfactory (80-84)',
        'very_satisfactory'=>'Very Satisfactory (85-89)',
        'outstanding'=>'Outstanding (90-100)'
    ];
    foreach ($progressRows as $key=>$label) {
        $formData['progress'][$key]['male'] = (int)($_POST['progress'][$key]['male'] ?? 0);
        $formData['progress'][$key]['female'] = (int)($_POST['progress'][$key]['female'] ?? 0);
        $formData['progress'][$key]['total'] = (int)($_POST['progress'][$key]['total'] ?? 0);
    }

   
    $formData['prepared_by'] = $_POST['prepared_by'] ?? '';
    $formData['certified_by'] = $_POST['certified_by'] ?? '';
    $formData['reviewed_by'] = $_POST['reviewed_by'] ?? '';

    $_SESSION['sf5_form'] = $formData;

    /* -------- html/ php to excelL -------- */
    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('G5', $formData['school_year']);
    $sheet->setCellValue('J5', $formData['curriculum']);
    $sheet->setCellValue('J7', $formData['grade_level']);
    $sheet->setCellValue('M7', $formData['section']);

    for ($r = 13; $r <= $totalRows; $r++) {
        if ($r == $skipRow) continue;
        $sheet->setCellValue("A{$r}", $formData['lrn'][$r] ?? '');
        $sheet->setCellValue("B{$r}", $formData['name'][$r] ?? '');
        $sheet->setCellValue("F{$r}", $formData['average'][$r] ?? '');
        $sheet->setCellValue("G{$r}", $formData['action'][$r] ?? '');
        $sheet->setCellValue("I{$r}", $formData['did_not_meet'][$r] ?? '');
    }

    // Total
    $sheet->setCellValue('F33', $formData['male_total']);
    $sheet->setCellValue('F60', $formData['female_total']);
    $sheet->setCellValue('F61', $formData['combined_total']);

    $summaryMap = [
        'promoted' => ['M15','N15','O15'],
        'conditional' => ['M17','N17','O17'],
        'retained' => ['M19','N19','O19']
    ];
    foreach ($summaryMap as $status => $cells) {
        $sheet->setCellValue($cells[0], $formData['summary'][$status]['male']);
        $sheet->setCellValue($cells[1], $formData['summary'][$status]['female']);
        $sheet->setCellValue($cells[2], $formData['summary'][$status]['total']);
    }

    $progressMap = [
        'did_not_meet' => ['M24','N24','O24'],
        'fairly_satisfactory' => ['M26','N26','O26'],
        'satisfactory' => ['M28','N28','O28'],
        'very_satisfactory' => ['M30','N30','O30'],
        'outstanding' => ['M32','N32','O32']
    ];
    foreach ($progressMap as $status => $cells) {
        $sheet->setCellValue($cells[0], $formData['progress'][$status]['male']);
        $sheet->setCellValue($cells[1], $formData['progress'][$status]['female']);
        $sheet->setCellValue($cells[2], $formData['progress'][$status]['total']);
    }

    $sheet->setCellValue('N36', $formData['prepared_by']);
    $sheet->setCellValue('N41', $formData['certified_by']);
    $sheet->setCellValue('N46', $formData['reviewed_by']);

    // save excel file
    $schoolYear = preg_replace('/[^A-Za-z0-9_-]/', '', $formData['school_year']);
    $gradeLevel = preg_replace('/[^A-Za-z0-9_-]/', '', $formData['grade_level']);
    $section = preg_replace('/[^A-Za-z0-9_-]/', '', $formData['section']);
    $filename = trim("{$schoolYear}_{$gradeLevel}_{$section}.xlsx", '_');
    if ($filename === '') $filename = 'sf5_' . time() . '.xlsx';

    $savePath = $saveDir . DIRECTORY_SEPARATOR . $filename;
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($savePath);

    $_SESSION['sf5_download'] = $filename;
    $downloadLink = $filename;

    /* -------- INSERT INTO DATABASE (sf5_data) -------- */
    $mysqli = new mysqli("localhost", "root", "", "stamariadb");
    if ($mysqli->connect_error) {
        die("Database connection failed: " . $mysqli->connect_error);
    }

    $learners_store = [];
    for ($r = 13; $r <= $totalRows; $r++) {
        if ($r == $skipRow) continue;
        $learners_store[$r] = [
            'lrn' => $formData['lrn'][$r] ?? '',
            'name' => $formData['name'][$r] ?? '',
            'average' => $formData['average'][$r] ?? '',
            'action' => $formData['action'][$r] ?? '',
            'did_not_meet' => $formData['did_not_meet'][$r] ?? ''
        ];
    }
    $learners_json = json_encode($learners_store, JSON_UNESCAPED_UNICODE);

    $sql = "INSERT INTO sf5_data (
        school_year, curriculum, grade_level, section,
        male_total, female_total, combined_total,
        promoted_male, promoted_female, promoted_total,
        conditional_male, conditional_female, conditional_total,
        retained_male, retained_female, retained_total,
        progress_did_not_meet_male, progress_did_not_meet_female, progress_did_not_meet_total,
        progress_fairly_satisfactory_male, progress_fairly_satisfactory_female, progress_fairly_satisfactory_total,
        progress_satisfactory_male, progress_satisfactory_female, progress_satisfactory_total,
        progress_very_satisfactory_male, progress_very_satisfactory_female, progress_very_satisfactory_total,
        progress_outstanding_male, progress_outstanding_female, progress_outstanding_total,
        prepared_by, certified_by, reviewed_by, learners
    ) VALUES (" . rtrim(str_repeat('?,', 35), ',') . ")";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        // Show exact SQL error to help debugging
        die("SQL Prepare failed: " . $mysqli->error . "\nSQL: " . $sql);
    }

    // Prepare values in the exact same order as the columns above
    $values = [
        (string)$formData['school_year'],
        (string)$formData['curriculum'],
        (string)$formData['grade_level'],
        (string)$formData['section'],

        (int)$formData['male_total'],
        (int)$formData['female_total'],
        (int)$formData['combined_total'],

        (int)($formData['summary']['promoted']['male'] ?? 0),
        (int)($formData['summary']['promoted']['female'] ?? 0),
        (int)($formData['summary']['promoted']['total'] ?? 0),

        (int)($formData['summary']['conditional']['male'] ?? 0),
        (int)($formData['summary']['conditional']['female'] ?? 0),
        (int)($formData['summary']['conditional']['total'] ?? 0),

        (int)($formData['summary']['retained']['male'] ?? 0),
        (int)($formData['summary']['retained']['female'] ?? 0),
        (int)($formData['summary']['retained']['total'] ?? 0),

        (int)($formData['progress']['did_not_meet']['male'] ?? 0),
        (int)($formData['progress']['did_not_meet']['female'] ?? 0),
        (int)($formData['progress']['did_not_meet']['total'] ?? 0),

        (int)($formData['progress']['fairly_satisfactory']['male'] ?? 0),
        (int)($formData['progress']['fairly_satisfactory']['female'] ?? 0),
        (int)($formData['progress']['fairly_satisfactory']['total'] ?? 0),

        (int)($formData['progress']['satisfactory']['male'] ?? 0),
        (int)($formData['progress']['satisfactory']['female'] ?? 0),
        (int)($formData['progress']['satisfactory']['total'] ?? 0),

        (int)($formData['progress']['very_satisfactory']['male'] ?? 0),
        (int)($formData['progress']['very_satisfactory']['female'] ?? 0),
        (int)($formData['progress']['very_satisfactory']['total'] ?? 0),

        (int)($formData['progress']['outstanding']['male'] ?? 0),
        (int)($formData['progress']['outstanding']['female'] ?? 0),
        (int)($formData['progress']['outstanding']['total'] ?? 0),

        (string)$formData['prepared_by'],
        (string)$formData['certified_by'],
        (string)$formData['reviewed_by'],
        (string)$learners_json
    ];

    $types = 'ssss' . str_repeat('i', 27) . 'ssss';

    $bind_params = [];
    $bind_params[] = & $types;
    for ($i = 0; $i < count($values); $i++) {
        $bind_params[] = & $values[$i];
    }

    call_user_func_array([$stmt, 'bind_param'], $bind_params);

    if (!$stmt->execute()) {
        die("SQL Execute failed: " . $stmt->error);
    }

    $stmt->close();
    $mysqli->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SF5</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f4f5f7; }
.header { background:#E75480; color:white; padding:12px 20px; display:flex; align-items:center; }
.header img { width:60px; height:60px; border-radius:50%; margin-right:10px; }
.scrollable-table { max-height:350px; overflow-y:auto; }
.scrollable-table thead th { position:sticky; top:0; background:#f8f9fa; }
</style>
</head>
<body>

<div class="header">
  <img src="/sta.MariaSystem/assets/image/logo2.png" alt="Logo">
  <h4>STA. MARIA WEB SYSTEM</h4>
</div>

<div class="container-fluid mt-4">
<form method="post">
<div class="row">

<!-- LEFT -->
<div class="col-md-8">

<div class="card p-3 mb-3">
  <h5>School Info</h5>
  <div class="row">
    <div class="col-md-3"><input type="text" name="school_year" class="form-control" placeholder="School Year" value="<?= htmlspecialchars($formData['school_year'] ?? '', ENT_QUOTES) ?>"></div>
    <div class="col-md-3"><input type="text" name="curriculum" class="form-control" placeholder="Curriculum" value="<?= htmlspecialchars($formData['curriculum'] ?? '', ENT_QUOTES) ?>"></div>
    <div class="col-md-3"><input type="text" name="grade_level" class="form-control" placeholder="Grade Level" value="<?= htmlspecialchars($formData['grade_level'] ?? '', ENT_QUOTES) ?>"></div>
    <div class="col-md-3"><input type="text" name="section" class="form-control" placeholder="Section" value="<?= htmlspecialchars($formData['section'] ?? '', ENT_QUOTES) ?>"></div>
  </div>
</div>

<!-- Learners Table -->
<div class="card p-3 mb-3">
  <h5>Learners Table</h5>
  <div class="scrollable-table">
    <table class="table table-bordered table-sm text-center align-middle">
      <thead><tr><th>LRN</th><th>Learner Name</th><th>General Average</th><th>Action Taken</th><th>Did Not Meet</th></tr></thead>
      <tbody>
      <?php for ($r=13;$r<=59;$r++): if ($r==33) continue; ?>
      <tr>
        <td><input type="text" name="lrn[<?= $r ?>]" value="<?= htmlspecialchars($formData['lrn'][$r] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
        <td><input type="text" name="name[<?= $r ?>]" value="<?= htmlspecialchars($formData['name'][$r] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
        <td><input type="text" name="average[<?= $r ?>]" value="<?= htmlspecialchars($formData['average'][$r] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
        <td><input type="text" name="action[<?= $r ?>]" value="<?= htmlspecialchars($formData['action'][$r] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
        <td><input type="text" name="did_not_meet[<?= $r ?>]" value="<?= htmlspecialchars($formData['did_not_meet'][$r] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
      </tr>
      <?php endfor; ?>
      </tbody>
    </table>
  </div>

  <div class="row mt-3 text-center">
    <div class="col">
      <label>Male Total (F33)</label>
      <input type="number" name="male_total" id="male_total" value="<?= htmlspecialchars($formData['male_total'] ?? '', ENT_QUOTES) ?>" class="form-control text-center">
    </div>
    <div class="col">
      <label>Female Total (F60)</label>
      <input type="number" name="female_total" id="female_total" value="<?= htmlspecialchars($formData['female_total'] ?? '', ENT_QUOTES) ?>" class="form-control text-center">
    </div>
    <div class="col">
      <label>Combined (F61)</label>
      <input type="number" readonly id="combined_total" name="combined_total" value="<?= htmlspecialchars($formData['combined_total'] ?? '', ENT_QUOTES) ?>" class="form-control text-center">
    </div>
  </div>
</div>

<script>
document.getElementById('male_total').addEventListener('input', updateTotal);
document.getElementById('female_total').addEventListener('input', updateTotal);
function updateTotal() {
  const male = parseInt(document.getElementById('male_total').value || 0);
  const female = parseInt(document.getElementById('female_total').value || 0);
  document.getElementById('combined_total').value = male + female;
}
</script>

<!-- Summary Table -->
<div class="card p-3 mb-3">
  <h5>Summary Table</h5>
  <table class="table table-bordered table-sm text-center">
    <thead><tr><th>Status</th><th>Male</th><th>Female</th><th>Total</th></tr></thead>
    <tbody>
    <?php $summaryRows=['promoted'=>'Promoted','conditional'=>'Conditional','retained'=>'Retained'];
    foreach($summaryRows as $k=>$label): ?>
    <tr>
      <td><?= $label ?></td>
      <td><input name="summary[<?= $k ?>][male]" value="<?= htmlspecialchars($formData['summary'][$k]['male'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
      <td><input name="summary[<?= $k ?>][female]" value="<?= htmlspecialchars($formData['summary'][$k]['female'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
      <td><input name="summary[<?= $k ?>][total]" value="<?= htmlspecialchars($formData['summary'][$k]['total'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- learning progress table -->
<div class="card p-3 mb-3">
  <h5>Learning Progress & Achievement</h5>
  <table class="table table-bordered table-sm text-center">
    <thead><tr><th>Descriptors & Grading Scale</th><th>Male</th><th>Female</th><th>Total</th></tr></thead>
    <tbody>
    <?php
    $progress=[
      'did_not_meet'=>'Did Not Meet Expectations (74 and below)',
      'fairly_satisfactory'=>'Fairly Satisfactory (75-79)',
      'satisfactory'=>'Satisfactory (80-84)',
      'very_satisfactory'=>'Very Satisfactory (85-89)',
      'outstanding'=>'Outstanding (90-100)'
    ];
    foreach($progress as $k=>$label): ?>
    <tr>
      <td class="text-start"><?= $label ?></td>
      <td><input name="progress[<?= $k ?>][male]" value="<?= htmlspecialchars($formData['progress'][$k]['male'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
      <td><input name="progress[<?= $k ?>][female]" value="<?= htmlspecialchars($formData['progress'][$k]['female'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
      <td><input name="progress[<?= $k ?>][total]" value="<?= htmlspecialchars($formData['progress'][$k]['total'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm"></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

</div>

<!--  (Sidebar) right -->
<div class="col-md-4">
  <div class="card p-3">
    <h5>Prepared / Certified / Reviewed</h5>
    <div class="mb-3">
      <label>Prepared By:</label>
      <input name="prepared_by" value="<?= htmlspecialchars($formData['prepared_by'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm">
      <small>Class Adviser</small>
    </div>
    <div class="mb-3">
      <label>Certified Correct & Submitted:</label>
      <input name="certified_by" value="<?= htmlspecialchars($formData['certified_by'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm">
      <small>School Head</small>
    </div>
    <div class="mb-3">
      <label>Reviewed By:</label>
      <input name="reviewed_by" value="<?= htmlspecialchars($formData['reviewed_by'] ?? '', ENT_QUOTES) ?>" class="form-control form-control-sm">
      <small>Division Representative</small>
    </div>

    <div class="text-center mt-4">
      <button type="button" class="btn btn-secondary me-2" onclick="history.back()">Back</button>
      <button type="submit" class="btn btn-primary me-2">Save SF5</button>
      <a href="?download=1" class="btn btn-success">Download Latest SF5</a>
    </div>
  </div>
</div>

</div>
</form>
</div>

</body>
</html>
