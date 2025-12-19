<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}

require_once BASE_PATH . '/authentication/functions.php';

require_once BASE_PATH . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;


$mysqli = new mysqli("localhost", "root", "", "stamariadb");
if ($mysqli->connect_error) die("DB Connection failed: " . $mysqli->connect_error);


$templatePath = BASE_PATH . '/src/UI-Admin/contents/sf5/sf5.xlsx';
$saveDir      = BASE_PATH . '/sf5_files';
if (!is_dir($saveDir)) mkdir($saveDir, 0777, true);

$totalRows = 59;
$skipRow = 33;

$formData = $_SESSION['sf5_form'] ?? [];
$downloadLink = $_SESSION['sf5_download'] ?? '';


$sectionId = $_GET['section_id'] ?? '';
$gradeLevel = $_GET['grade'] ?? '';
$sectionName = $_GET['section'] ?? '';

if ($sectionId) {
    $formData['grade_level'] = $gradeLevel;
    $formData['section'] = $sectionName;
}


$formData['male_total'] = 0;
$formData['female_total'] = 0;
$formData['combined_total'] = 0;


$progressCategories = [
    'did_not_meet' => ['min'=>0, 'max'=>74],
    'fairly_satisfactory' => ['min'=>75, 'max'=>79],
    'satisfactory' => ['min'=>80, 'max'=>84],
    'very_satisfactory' => ['min'=>85, 'max'=>89],
    'outstanding' => ['min'=>90, 'max'=>100]
];
foreach($progressCategories as $status=>$range){
    $formData['progress'][$status] = ['male'=>0,'female'=>0,'total'=>0];
}


if (!empty($sectionId) && !empty($gradeLevel) && !empty($sectionName)) {
    $gradeLevel = trim($gradeLevel);
    $sectionName = trim($sectionName);

    $stmt = $mysqli->prepare("
        SELECT sf9.lrn, sf9.student_name, sf9.general_average, s.sex
        FROM sf9_data sf9
        JOIN student s ON s.lrn = sf9.lrn
        WHERE sf9.grade = ? AND LOWER(sf9.section) = LOWER(?)
        ORDER BY s.lname, s.fname
    ");
    $stmt->bind_param("ss", $gradeLevel, $sectionName);
    $stmt->execute();
    $result = $stmt->get_result();

    $rowNum = 13;
    while ($student = $result->fetch_assoc()) {
        if ($rowNum == $skipRow) $rowNum++;
        $formData['lrn'][$rowNum] = $student['lrn'];
        $formData['name'][$rowNum] = $student['student_name'];
        $formData['average'][$rowNum] = $student['general_average'];
      
$formData['action'][$rowNum] = $formData['action'][$rowNum] ?? '';

        $formData['sex'][$rowNum] = strtoupper($student['sex']);

        if ($student['sex'] === 'MALE') $formData['male_total']++;
        if ($student['sex'] === 'FEMALE') $formData['female_total']++;

        $avg = (float)$student['general_average'];
        foreach($progressCategories as $status=>$range){
            if($avg >= $range['min'] && $avg <= $range['max']){
                if($student['sex']==='MALE') $formData['progress'][$status]['male']++;
                if($student['sex']==='FEMALE') $formData['progress'][$status]['female']++;
                $formData['progress'][$status]['total']++;
                break;
            }
        }
        $rowNum++;
        if ($rowNum > $totalRows) break;
    }
    $formData['combined_total'] = $formData['male_total'] + $formData['female_total'];
    $stmt->close();
}


if (!empty($gradeLevel) && !empty($sectionName)) {
    $loadAct = $mysqli->prepare("SELECT action_taken FROM sf5_data WHERE grade_level=? AND section=? AND school_year=? LIMIT 1");
    $loadAct->bind_param("sss", $gradeLevel, $sectionName, $formData['school_year']);
    $loadAct->execute();
    $res = $loadAct->get_result();
    if ($row = $res->fetch_assoc()) {
        $savedActions = json_decode($row['action_taken'], true);
        if (is_array($savedActions)) {
            foreach ($savedActions as $r => $val) {
                $formData['action'][$r] = $val;
            }
        }
    }
    $loadAct->close();
}


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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['school_year'] = $_POST['school_year'] ?? '';
    $formData['curriculum'] = $_POST['curriculum'] ?? '';
    $formData['grade_level'] = $_POST['grade_level'] ?? '';
    $formData['section'] = $_POST['section'] ?? '';

    for ($r = 13; $r <= $totalRows; $r++) {
        if ($r == $skipRow) continue;
        $formData['lrn'][$r] = $_POST['lrn'][$r] ?? '';
        $formData['name'][$r] = $_POST['name'][$r] ?? '';
        $formData['average'][$r] = $_POST['average'][$r] ?? '';
        $formData['action'][$r] = $_POST['action'][$r] ?? '';
        $formData['sex'][$r] = $_POST['sex'][$r] ?? '';
        $formData['did_not_meet'][$r] = $_POST['did_not_meet'][$r] ?? '';
    }

    $male=0; $female=0;
    for ($r = 13; $r <= $totalRows; $r++) {
        if($r==$skipRow) continue;
        $sex = strtoupper($formData['sex'][$r] ?? '');
        if($sex==='MALE') $male++;
        if($sex==='FEMALE') $female++;
    }
    $formData['male_total']=$male;
    $formData['female_total']=$female;
    $formData['combined_total']=$male+$female;

    foreach($progressCategories as $status=>$range){
        $formData['progress'][$status] = ['male'=>0,'female'=>0,'total'=>0];
    }
    for ($r=13;$r<=$totalRows;$r++){
        if($r==$skipRow) continue;
        $avg = (float)($formData['average'][$r]??0);
        $sex = strtoupper($formData['sex'][$r]??'');
        foreach($progressCategories as $status=>$range){
            if($avg >= $range['min'] && $avg <= $range['max']){
                if($sex==='MALE') $formData['progress'][$status]['male']++;
                if($sex==='FEMALE') $formData['progress'][$status]['female']++;
                $formData['progress'][$status]['total']++;
                break;
            }
        }
    }

    $summaryRows = ['promoted','conditional','retained'];
    foreach ($summaryRows as $status) {
        $formData['summary'][$status]['male'] = (int)($_POST['summary'][$status]['male'] ?? 0);
        $formData['summary'][$status]['female'] = (int)($_POST['summary'][$status]['female'] ?? 0);
        $formData['summary'][$status]['total'] = (int)($_POST['summary'][$status]['total'] ?? 0);
    }

    $formData['prepared_by'] = $_POST['prepared_by'] ?? '';
    $formData['certified_by'] = $_POST['certified_by'] ?? '';
    $formData['reviewed_by'] = $_POST['reviewed_by'] ?? '';
    $_SESSION['sf5_form'] = $formData;

  
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
        $sheet->setCellValue("H{$r}", $formData['sex'][$r] ?? '');
        $sheet->setCellValue("I{$r}", $formData['did_not_meet'][$r] ?? '');
    }

    $sheet->setCellValue('F33', $formData['male_total']);
    $sheet->setCellValue('F60', $formData['female_total']);
    $sheet->setCellValue('F61', $formData['combined_total']);

    $summaryMap = [
        'promoted'=>['M15','N15','O15'],
        'conditional'=>['M17','N17','O17'],
        'retained'=>['M19','N19','O19']
    ];
    foreach ($summaryMap as $status=>$cells) {
        $sheet->setCellValue($cells[0], $formData['summary'][$status]['male']);
        $sheet->setCellValue($cells[1], $formData['summary'][$status]['female']);
        $sheet->setCellValue($cells[2], $formData['summary'][$status]['total']);
    }

    $progressMap = [
        'did_not_meet'=>['M24','N24','O24'],
        'fairly_satisfactory'=>['M26','N26','O26'],
        'satisfactory'=>['M28','N28','O28'],
        'very_satisfactory'=>['M30','N30','O30'],
        'outstanding'=>['M32','N32','O32']
    ];
    foreach ($progressMap as $status=>$cells) {
        $sheet->setCellValue($cells[0], $formData['progress'][$status]['male']);
        $sheet->setCellValue($cells[1], $formData['progress'][$status]['female']);
        $sheet->setCellValue($cells[2], $formData['progress'][$status]['total']);
    }

    $sheet->setCellValue('N36', $formData['prepared_by']);
    $sheet->setCellValue('N41', $formData['certified_by']);
    $sheet->setCellValue('N46', $formData['reviewed_by']);

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

    
    $actionData = json_encode($formData['action'], JSON_UNESCAPED_UNICODE);
    $check = $mysqli->prepare("SELECT id FROM sf5_data WHERE grade_level=? AND section=? AND school_year=? LIMIT 1");
    $check->bind_param("sss", $formData['grade_level'], $formData['section'], $formData['school_year']);
    $check->execute();
    $checkRes = $check->get_result();

    if ($checkRes->num_rows > 0) {
        $row = $checkRes->fetch_assoc();
        $update = $mysqli->prepare("UPDATE sf5_data SET action_taken=? WHERE id=?");
        $update->bind_param("si", $actionData, $row['id']);
        $update->execute();
        $update->close();
    } else {
        $insert = $mysqli->prepare("INSERT INTO sf5_data (school_year, grade_level, section, action_taken) VALUES (?,?,?,?)");
        $insert->bind_param("ssss", $formData['school_year'], $formData['grade_level'], $formData['section'], $actionData);
        $insert->execute();
        $insert->close();
    }
    $check->close();

    header("Location: ".$_SERVER['PHP_SELF']."?section_id={$sectionId}&grade={$gradeLevel}&section={$sectionName}");
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
body { font-family:'Poppins',sans-serif; background:#f4f5f7; padding-bottom:120px; }
.header { background:#FF3860; color:white; padding:7px 20px; display:flex; align-items:center; }
.header img { width:60px; height:60px; border-radius:50%; margin-right:10px; }
.scrollable-table { max-height:350px; overflow-y:auto; }
.scrollable-table thead th { position:sticky; top:0; background:#f8f9fa; }
.card { box-shadow:0 2px 8px rgba(0,0,0,0.1); border-radius:8px; }
#action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px; /* space from table */
}
#action-buttons button, #action-buttons a { min-width:140px; }
</style>
</head>
<body>
<div class="header">
<img src="../../../assets/image/logo2.png" alt="Logo">
<h4>STA. MARIA WEB SYSTEM</h4>
</div>
<div class="container-fluid mt-4">
<form method="post" id="sf5-form">
<div class="row">
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

<div class="card p-3 mb-3">
<h5>Learners Table</h5>
<div class="scrollable-table">
<table class="table table-bordered table-sm text-center align-middle">
<thead>
<tr><th>LRN</th><th>Learner's Name</th><th>General Average</th><th>ACTION TAKEN: PROMOTED, CONDITIONAL, or RETAINED</th><th>Sex</th><th>Did Not Meet Expectations of the ff. Learning Area/s as of end of current School Year </th></tr>
</thead>
<tbody>
<?php for ($r=13;$r<=59;$r++): if ($r==$skipRow) continue; ?>
<tr>
<td><input type="text" name="lrn[<?=$r?>]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['lrn'][$r]??'')?>"></td>
<td><input type="text" name="name[<?=$r?>]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['name'][$r]??'')?>"></td>
<td><input type="text" name="average[<?=$r?>]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['average'][$r]??'')?>"></td>
<td>
<select name="action[<?=$r?>]" class="form-control form-control-sm action-select">
    <option value="">Select</option>
    <option value="PROMOTED" <?=($formData['action'][$r]??'')==='PROMOTED'?'selected':''?>>PROMOTED</option>
    <option value="RETAINED" <?=($formData['action'][$r]??'')==='RETAINED'?'selected':''?>>RETAINED</option>
    <option value="CONDITIONAL" <?=($formData['action'][$r]??'')==='CONDITIONAL'?'selected':''?>>CONDITIONAL</option>
</select>
</td>

<td>
<select name="sex[<?=$r?>]" class="form-control form-control-sm sex-select">
    <option value="">Select</option>
    <option value="MALE" <?=($formData['sex'][$r]??'')==='MALE'?'selected':''?>>MALE</option>
    <option value="FEMALE" <?=($formData['sex'][$r]??'')==='FEMALE'?'selected':''?>>FEMALE</option>
</select>
</td>
<td><input type="text" name="did_not_meet[<?=$r?>]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['did_not_meet'][$r]??'')?>"></td>
</tr>
<?php endfor; ?>
</tbody>

</table>

</div>
<div id="action-buttons">
   <button type="button" class="btn btn-secondary" onclick="history.back()">Back</button>
    <button type="submit" form="sf5-form" class="btn btn-primary">Save</button>
    <?php if($downloadLink): ?>
    <a href="?download=1" class="btn btn-success">Download</a>
    <?php endif; ?>
</div>
</div>
</div>

<div class="col-md-4">

<div class="card p-3 mb-3">
<h5>Summary Table</h5>
<table class="table table-bordered table-sm text-center align-middle">
<tr><th>Status</th><th>Male</th><th>Female</th><th>Total</th></tr>
<?php foreach(['promoted','conditional','retained'] as $status): ?>
<tr>
<td><?=ucfirst($status)?></td>
<td><input type="number" name="summary[<?=$status?>][male]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['summary'][$status]['male']??'')?>"></td>
<td><input type="number" name="summary[<?=$status?>][female]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['summary'][$status]['female']??'')?>"></td>
<td><input type="number" name="summary[<?=$status?>][total]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['summary'][$status]['total']??'')?>"></td>
</tr>
<?php endforeach; ?>
</table>
</div>


<div class="card p-3 mb-3">
<h5>Learning Progress</h5>
<table class="table table-bordered table-sm text-center align-middle">
<tr><th>Performance</th><th>Male</th><th>Female</th><th>Total</th></tr>
<?php foreach(array_keys($progressCategories) as $status): ?>
<tr>
<td><?=ucwords(str_replace('_',' ',$status))?></td>
<td><input type="number" name="progress[<?=$status?>][male]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['progress'][$status]['male']??'')?>"></td>
<td><input type="number" name="progress[<?=$status?>][female]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['progress'][$status]['female']??'')?>"></td>
<td><input type="number" name="progress[<?=$status?>][total]" class="form-control form-control-sm" value="<?=htmlspecialchars($formData['progress'][$status]['total']??'')?>"></td>
</tr>
<?php endforeach; ?>
</table>
</div>


<div class="card p-3 mb-3">
<h5>Signatories</h5>
<div class="mb-2"><input type="text" name="prepared_by" class="form-control form-control-sm" placeholder="Prepared By" value="<?=htmlspecialchars($formData['prepared_by']??'')?>"></div>
<div class="mb-2"><input type="text" name="certified_by" class="form-control form-control-sm" placeholder="Certified Correct and Submitted By" value="<?=htmlspecialchars($formData['certified_by']??'')?>"></div>
<div class="mb-2"><input type="text" name="reviewed_by" class="form-control form-control-sm" placeholder="Reviewed By" value="<?=htmlspecialchars($formData['reviewed_by']??'')?>"></div>
</div>
</div>
</div>
</form>
</div>




<script>

function updateTotals() {
    let male = 0, female = 0;
    for (let r = 13; r <= 59; r++) {
        if (r == <?=$skipRow?>) continue;
        const sexSelect = document.querySelector(`select[name="sex[${r}]"]`);
        const sexVal = sexSelect?.value.toUpperCase() || '';
        if (sexVal === 'MALE') male++;
        if (sexVal === 'FEMALE') female++;
    }
    let maleInput = document.querySelector('input[name="male_total"]');
    if (!maleInput) { maleInput = document.createElement('input'); maleInput.type = 'hidden'; maleInput.name = 'male_total'; document.querySelector('form').appendChild(maleInput); }
    let femaleInput = document.querySelector('input[name="female_total"]');
    if (!femaleInput) { femaleInput = document.createElement('input'); femaleInput.type = 'hidden'; femaleInput.name = 'female_total'; document.querySelector('form').appendChild(femaleInput); }
    let combinedInput = document.querySelector('input[name="combined_total"]');
    if (!combinedInput) { combinedInput = document.createElement('input'); combinedInput.type = 'hidden'; combinedInput.name = 'combined_total'; document.querySelector('form').appendChild(combinedInput); }
    maleInput.value = male;
    femaleInput.value = female;
    combinedInput.value = male + female;
}


function updateActions() {
    for (let r = 13; r <= 59; r++) {
        if (r == <?=$skipRow?>) continue;
        const avgInput = document.querySelector(`input[name="average[${r}]"]`);
        const actionSelect = document.querySelector(`select[name="action[${r}]"]`);
        if (!avgInput || !actionSelect) continue;

        const avgStr = avgInput.value.trim();
        if (avgStr === '') {
            actionSelect.value = '';
            continue;
        }

        const avg = parseFloat(avgStr);
      if (!isNaN(avg)) {
   
    if (actionSelect.value !== 'CONDITIONAL' && actionSelect.value !== 'RETAINED' && actionSelect.value !== 'PROMOTED') {
        if (avg <= 74) actionSelect.value = 'RETAINED';
        else if (avg >= 75) actionSelect.value = 'PROMOTED';
    }
}

    }
    updateSummaryTable(); 
}


function updateSummaryTable() {
    const summaryStatuses = ['PROMOTED', 'CONDITIONAL', 'RETAINED'];
    const summaryInputs = {
        'PROMOTED': { male: 0, female: 0, total: 0 },
        'CONDITIONAL': { male: 0, female: 0, total: 0 },
        'RETAINED': { male: 0, female: 0, total: 0 }
    };

    for (let r = 13; r <= 59; r++) {
        if (r == <?=$skipRow?>) continue;
        const actionSelect = document.querySelector(`select[name="action[${r}]"]`);
        const sexSelect = document.querySelector(`select[name="sex[${r}]"]`);
        const action = actionSelect?.value.toUpperCase() || '';
        const sex = sexSelect?.value.toUpperCase() || '';

        if (summaryStatuses.includes(action)) {
            if (sex === 'MALE') summaryInputs[action].male++;
            if (sex === 'FEMALE') summaryInputs[action].female++;
            summaryInputs[action].total++;
        }
    }

    summaryStatuses.forEach(status => {
        const maleInput = document.querySelector(`input[name="summary[${status.toLowerCase()}][male]"]`);
        const femaleInput = document.querySelector(`input[name="summary[${status.toLowerCase()}][female]"]`);
        const totalInput = document.querySelector(`input[name="summary[${status.toLowerCase()}][total]"]`);
        if (maleInput) maleInput.value = summaryInputs[status].male;
        if (femaleInput) femaleInput.value = summaryInputs[status].female;
        if (totalInput) totalInput.value = summaryInputs[status].total;
    });
}

document.querySelectorAll('select[name^="sex"]').forEach(sel => sel.addEventListener('change', () => {
    updateTotals();
    updateSummaryTable();
}));
document.querySelectorAll('select[name^="action"]').forEach(sel => sel.addEventListener('change', updateSummaryTable));
document.querySelectorAll('input[name^="average"]').forEach(inp => inp.addEventListener('input', updateActions));
updateTotals();
updateActions();
updateSummaryTable();
</script>
</body>
</html>




