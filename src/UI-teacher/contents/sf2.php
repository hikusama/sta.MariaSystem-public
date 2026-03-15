<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

// Get section, grade level, school form data, and adviser info in one optimized query
$stmt = $pdo->prepare("SELECT 
    en.section_name, en.Grade_level,
    sf.sf_add_data_id, sf.school_id, sf.school_name,
    sy.school_year_id, sy.school_year_name,
    u.user_id, u.firstname, u.lastname
FROM enrolment en
INNER JOIN users u ON en.adviser_id = u.user_id
LEFT JOIN classes c ON u.user_id = c.adviser_id
LEFT JOIN sf_add_data sf ON sf.sf_type = 'sf_8'
LEFT JOIN school_year sy ON sy.school_year_status = 'Active'
WHERE u.user_id = :user_id
LIMIT 1");
$stmt->execute(['user_id' => $user_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];

$section_name = $data["section_name"] ?? '';
$Grade_level = $data["Grade_level"] ?? '';
$data_sf_eight = ['sf_add_data_id' => $data["sf_add_data_id"] ?? '', 'school_id' => $data["school_id"] ?? '', 'school_name' => $data["school_name"] ?? ''];
$sy = ['school_year_id' => $data["school_year_id"] ?? '', 'school_year_name' => $data["school_year_name"] ?? ''];
$adviser_data = ['firstname' => $data["firstname"] ?? '', 'lastname' => $data["lastname"] ?? ''];

// Get selected month and school year from POST
$selected_month = $_POST['month'] ?? '';
$selected_year = date('Y');
$selected_school_year_id = $_POST['school_year'] ?? $sy['school_year_id'];

// Get selected school year details
$stmt_sy = $pdo->prepare("SELECT school_year_id, school_year_name, school_year_status FROM school_year WHERE school_year_id = ?");
$stmt_sy->execute([$selected_school_year_id]);
$selected_sy_data = $stmt_sy->fetch(PDO::FETCH_ASSOC) ?? ['school_year_name' => '', 'school_year_status' => ''];
$is_active_sy = $selected_sy_data['school_year_status'] === 'Active';

function getSchoolDaysForMonth($month, $year)
{
    // Convert month name to numeric month (01..12)
    $month_num = str_pad(date('m', strtotime(ucfirst(strtolower($month)) . " 1, $year")), 2, '0', STR_PAD_LEFT);

    $days_in_month = (int) date("t", strtotime("$year-$month_num-01"));
    $school_days = [];
    $position = 0;

    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = sprintf("%04d-%02d-%02d", $year, $month_num, $day);
        $weekday = date('w', strtotime($date));  

        if ($weekday >= 1 && $weekday <= 6) {
            $position++;
            $school_days[$day] = [
                'date' => $date,
                'weekday' => $weekday,
                'weekday_name' => date('D', strtotime($date)), // 'Mon', 'Tue', etc.
                'position' => $position
            ];
        }
    }

    return $school_days;
}

// Get school days if month is selected
$school_days = $selected_month ? getSchoolDaysForMonth($selected_month, $selected_year) : [];

// Get all students for this section with attendance data in one optimized query
$month_num = $selected_month ? str_pad(date('m', strtotime($selected_month . " 1, $selected_year")), 2, '0', STR_PAD_LEFT) : '';

$stmt = $pdo->prepare("SELECT 
    s.student_id, s.sex, s.lname, s.fname, s.mname, e.enrolment_id, e.enrolment_status,
    a.attendance_at, a.attendance_summary
FROM student s 
INNER JOIN enrolment e ON s.student_id = e.student_id 
LEFT JOIN attendance a ON s.student_id = a.student_id 
    AND a.school_year_id = ? 
    AND a.attendance_summary IS NOT NULL
    " . ($selected_month ? "AND MONTH(a.attendance_at) = ? AND YEAR(a.attendance_at) = ?" : "") . "
WHERE e.section_name = ? AND e.Grade_level = ? AND e.enrolment_Status = 'Approved'
ORDER BY s.sex, s.lname, s.fname, s.mname");

$params = [$selected_school_year_id];
if ($selected_month) {
    $params[] = $month_num;
    $params[] = $selected_year;
}
$params[] = $section_name;
$params[] = $Grade_level;

$stmt->execute($params);
$attendance_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process attendance data and organize by student
$students = [];
$student_attendance = [];
$student_present_counts = [];
$student_absent_counts = [];
$daily_totals = [];

foreach ($attendance_rows as $row) {
    $sid = $row['student_id'];

    // Add student info once
    if (!isset($students[$sid])) {
        $students[$sid] = $row;
        $student_absent_counts[$sid] = 0;
        $student_present_counts[$sid] = 0;
    }

    // Process attendance
    if ($row['attendance_at'] && $selected_month) {
        $day = (int)date('d', strtotime($row['attendance_at']));
        $is_absent = $row['attendance_summary'] === 'Absent';

        $student_attendance[$sid][$day] = $is_absent ? 'ABSENT' : 'PRESENT';

        if ($is_absent) {
            $student_absent_counts[$sid]++;
        } else {
            $student_present_counts[$sid]++;
        }
    }
}

// Separate by gender
$male_students = array_filter($students, fn($s) => strtoupper($s['sex'] ?? '') === 'MALE');
$female_students = array_filter($students, fn($s) => strtoupper($s['sex'] ?? '') === 'FEMALE');

// Calculate daily and monthly totals
$daily_male_present = $daily_male_absent = $daily_female_present = $daily_female_absent = [];
$monthly_male_absent_total = $monthly_male_present_total = $monthly_female_absent_total = $monthly_female_present_total = 0;

if ($selected_month && !empty($school_days)) {
    foreach ($school_days as $day => $day_info) {
        $daily_male_absent[$day] = $daily_male_present[$day] = 0;
        $daily_female_absent[$day] = $daily_female_present[$day] = 0;

        foreach ($male_students as $student) {
            $student_id = $student['student_id'];
            if (isset($student_attendance[$student_id][$day])) {
                if ($student_attendance[$student_id][$day] === 'ABSENT') {
                    $daily_male_absent[$day]++;
                } else {
                    $daily_male_present[$day]++;
                }
            }
        }

        foreach ($female_students as $student) {
            $student_id = $student['student_id'];
            if (isset($student_attendance[$student_id][$day])) {
                if ($student_attendance[$student_id][$day] === 'ABSENT') {
                    $daily_female_absent[$day]++;
                } else {
                    $daily_female_present[$day]++;
                }
            }
        }
    }

    // Calculate monthly totals
    foreach ($male_students as $student) {
        $monthly_male_absent_total += $student_absent_counts[$student['student_id']] ?? 0;
        $monthly_male_present_total += $student_present_counts[$student['student_id']] ?? 0;
    }

    foreach ($female_students as $student) {
        $monthly_female_absent_total += $student_absent_counts[$student['student_id']] ?? 0;
        $monthly_female_present_total += $student_present_counts[$student['student_id']] ?? 0;
    }
}

$monthly_combined_absent_total = $monthly_male_absent_total + $monthly_female_absent_total;
$monthly_combined_present_total = $monthly_male_present_total + $monthly_female_present_total;
?>

<main>
    <form class="main-container" id="sfEight-form" style="width: 1900px;" method="POST" onsubmit="return validateBeforeSave()">
        <div class="mt-3 text-start d-flex flex-wrap gap-1 justify-center align-center">
            <?php if ($is_active_sy): ?>
                <button type="submit" class="btn btn-danger" name="save" id="saveBtnData">Save Data</button>
            <?php endif; ?>
            <button type="button" class="btn btn-secondary" onclick="generateReport()">Generate Report</button>
            <select id="syFilter" name="school_year" class="form-select" style="max-width: 200px; margin-bottom: .8rem;" onchange="this.form.submit()">
                <?php
                $schoolYears = $pdo->query("
                    SELECT school_year_id, school_year_name, school_year_status
                    FROM school_year
                    ORDER BY 
                        CASE WHEN school_year_status = 'Active' THEN 0 ELSE 1 END,
                        school_year_name ASC
                ")->fetchAll(PDO::FETCH_ASSOC);

                $activeSyId = null;
                foreach ($schoolYears as $cat) {
                    if ($cat['school_year_status'] === 'Active' && $activeSyId === null) {
                        $activeSyId = $cat['school_year_id'];
                    }
                }
                ?>
                <?php foreach ($schoolYears as $sy_item): ?>
                    <option value="<?= htmlspecialchars($sy_item['school_year_id']) ?>"
                        <?= ($sy_item['school_year_id'] == $selected_school_year_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sy_item['school_year_name']) ?>
                        <?= $sy_item['school_year_status'] === 'Active' ? ' (Active)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="id" value="<?= htmlspecialchars($data_sf_eight["sf_add_data_id"] ?? '') ?>">
        <input type="hidden" id="report_for_the_month_of" name="report_for_the_month_of" value="">
        <div class="col-md-12 d-flex justify-content-between">
            <div class="col-md-3 d-flex align-items-center justify-content-start">
                <img id="school_logo" src="../../assets/image/logo.png" alt="No Image" style="width: auto; height: 150px;">
            </div>
            <div class="col-md-6">
                <div class="form-title text-center w-100">
                    <h2>Department of Education <br> School Form 2 Daily Attendance Report of Learners (SF2)</h2>
                    <p class="text-muted">(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)</p>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-center justify-content-end">
                <img id="deped_logo" src="../../assets/image/deped.png" alt="No Image" style="width: 200px; height: auto; transform: translateX(-30px);">
            </div>
        </div>

        <div class="form-section">
            <div class="row mb-2">
                <div class="d-flex align-items-center mb-2">
                    <div class="col-md-3 d-flex">
                        <label class="me-2 col-4">School ID</label>
                        <input type="text" name="school_id" value="<?= htmlspecialchars($data_sf_eight["school_id"] ?? '') ?>" class="me-2 flex-grow-1">
                    </div>
                    <div class="col-md-3 d-flex">
                        <label class="me-2 col-4">School Year</label>
                        <input readonly class="form-control" type="text" name="school_year_name" value="<?= htmlspecialchars($selected_sy_data["school_year_name"] ?? '') ?>">
                    </div>
                    <div class="col-md-5 ms-5 d-flex">
                        <label class="me-2 col-4">Report for the month of</label>
                        <select name="month" id="month_attendance" class="form-select" onchange="this.form.submit()">
                            <option value="">Select Month</option>
                            <?php
                            $months = ['JANUARY', 'FEBRUARY', 'MARCH', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
                            foreach ($months as $month) {
                                $selected = ($selected_month == $month) ? 'selected' : '';
                                echo "<option value='$month' $selected>$month</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-start mb-2">
                    <div class="col-md-6 me-1">
                        <div class="d-flex align-items-center justify-content-start mb-2">
                            <label class="me-2 col-2">Name of school</label>
                            <input type="text" name="school_name" value="<?= htmlspecialchars($data_sf_eight["school_name"] ?? '') ?>" class="flex-grow-1 form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-end mb-2">
                            <label class="ms-5 col-4">Grade Level</label>
                            <input type="text" readonly name="grade_level" value="<?= htmlspecialchars($Grade_level) ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-2">
                            <label class="me-2 col-2">Section</label>
                            <input type="text" readonly name="sections" id="section_name" value="<?= htmlspecialchars($section_name) ?>" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 1800px; overflow: auto;">
                <table class="table table-bordered table-sm table-hover" style="text-align:center; min-width: 1800px; font-size: 11px;">
                    <thead>
                        <tr>
                            <th colspan="2" rowspan="3" width="15%">LEARNER'S NAME <br> (Last Name, First Name, Middle Name) </th>
                            <th colspan="<?= count($school_days) ?>" rowspan="1" width="50%">(1st row for date)</th>
                            <th colspan="2" rowspan="2" width="15%">Total for the <br> Month</th>
                            <th rowspan="3" width="20%"><strong>REMARKS</strong> (if <strong>DROPPED OUT</strong>, state reason, please refer to <br> legend number 2 <br> if <strong>TRANSFERRED IN/OUT, </strong>write the name of school)</th>
                        </tr>
                        <tr>
                            <?php if ($selected_month && !empty($school_days)): ?>
                                <?php foreach ($school_days as $day => $day_info): ?>
                                    <th width='0.5%' class='m-0 p-0 text-center'><?= $day ?></th>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php for ($i = 1; $i <= 25; $i++): ?>
                                    <th width='0.5%' class='m-0 p-0 text-center'></th>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </tr>
                        <tr class="text-center p-0 m-0">
                            <?php if ($selected_month && !empty($school_days)): ?>
                                <?php foreach ($school_days as $day => $day_info): ?>
                                    <th width='0.5%' class='text-center p-0 m-0'><?= substr($day_info['weekday_name'], 0, 1) ?></th>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php $weekdays = ['M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F']; ?>
                                <?php foreach ($weekdays as $abbr): ?>
                                    <th width='0.5%' class='text-center p-0 m-0'><?= $abbr ?></th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <th width="7.5">ABSENT</th>
                            <th width="7.5">PRESENT</th>
                        </tr>
                    </thead>

                    <tbody style="font-size: 0.85rem;">
                        <!-- MALE STUDENTS -->
                        <?php $male_count = 1; ?>
                        <?php foreach ($male_students as $student): ?>
                            <?php $student_id = $student['student_id']; ?>
                            <tr>
                                <td><?= $male_count++ ?></td>
                                <td style="text-align: left; padding-left: 10px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars(strtoupper($student["lname"] . ', ' . $student["fname"] . ' ' . $student["mname"])) ?>">
                                    <?= htmlspecialchars(strtoupper($student["lname"] . ', ' . $student["fname"] . ' ' . $student["mname"])) ?>
                                </td>
                                <?php if ($selected_month && !empty($school_days)): ?>
                                    <?php foreach ($school_days as $day => $day_info): ?>
                                        <td><?= (isset($student_attendance[$student_id][$day]) && $student_attendance[$student_id][$day] === 'ABSENT') ? 'X' : '' ?></td>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php for ($i = 1; $i <= 25; $i++): ?>
                                        <td></td>
                                    <?php endfor; ?>
                                <?php endif; ?>
                                <td><strong><?= $student_absent_counts[$student_id] ?? 0 ?></strong></td>
                                <td><strong><?= $student_present_counts[$student_id] ?? 0 ?></strong></td>
                                <td>
                                    <?php
                                    $status = $student['enrolment_status'] ?? '';
                                    echo match ($status) {
                                        'dropped' => 'DROPPED OUT',
                                        'transferred_out' => 'TRANSFERRED OUT',
                                        'transferred_in' => 'TRANSFERRED IN',
                                        default => ''
                                    };
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- MONTHLY TOTAL FOR MALE -->
                        <tr>
                            <th colspan="2">MONTHLY TOTAL (Male)</th>
                            <?php if ($selected_month && !empty($school_days)): ?>
                                <?php foreach ($school_days as $day => $day_info): ?>
                                    <td><strong><?= ($daily_male_absent[$day] ?? 0) ?>/<?= ($daily_male_present[$day] ?? 0) ?></strong></td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php for ($i = 1; $i <= 25; $i++): ?>
                                    <td></td>
                                <?php endfor; ?>
                            <?php endif; ?>
                            <td><strong><?= $monthly_male_absent_total ?></strong></td>
                            <td><strong><?= $monthly_male_present_total ?></strong></td>
                            <td></td>
                        </tr>

                        <!-- FEMALE STUDENTS -->
                        <?php $female_count = 1; ?>
                        <?php foreach ($female_students as $student): ?>
                            <?php $student_id = $student['student_id']; ?>
                            <tr>
                                <td><?= $female_count++ ?></td>
                                <td style="text-align: left; padding-left: 10px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars(strtoupper($student["lname"] . ', ' . $student["fname"] . ' ' . $student["mname"])) ?>">
                                    <?= htmlspecialchars(strtoupper($student["lname"] . ', ' . $student["fname"] . ' ' . $student["mname"])) ?>
                                </td>
                                <?php if ($selected_month && !empty($school_days)): ?>
                                    <?php foreach ($school_days as $day => $day_info): ?>
                                        <td><?= (isset($student_attendance[$student_id][$day]) && $student_attendance[$student_id][$day] === 'ABSENT') ? 'X' : '' ?></td>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php for ($i = 1; $i <= 25; $i++): ?>
                                        <td></td>
                                    <?php endfor; ?>
                                <?php endif; ?>
                                <td><strong><?= $student_absent_counts[$student_id] ?? 0 ?></strong></td>
                                <td><strong><?= $student_present_counts[$student_id] ?? 0 ?></strong></td>
                                <td>
                                    <?php
                                    $status = $student['enrolment_status'] ?? '';
                                    echo match ($status) {
                                        'dropped' => 'DROPPED OUT',
                                        'transferred_out' => 'TRANSFERRED OUT',
                                        'transferred_in' => 'TRANSFERRED IN',
                                        default => ''
                                    };
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- MONTHLY TOTAL FOR FEMALE -->
                        <tr>
                            <th colspan="2">MONTHLY TOTAL (Female)</th>
                            <?php if ($selected_month && !empty($school_days)): ?>
                                <?php foreach ($school_days as $day => $day_info): ?>
                                    <td><strong><?= ($daily_female_absent[$day] ?? 0) ?>/<?= ($daily_female_present[$day] ?? 0) ?></strong></td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php for ($i = 1; $i <= 25; $i++): ?>
                                    <td></td>
                                <?php endfor; ?>
                            <?php endif; ?>
                            <td><strong><?= $monthly_female_absent_total ?></strong></td>
                            <td><strong><?= $monthly_female_present_total ?></strong></td>
                            <td></td>
                        </tr>

                        <!-- COMBINED MONTHLY TOTAL -->
                        <tr>
                            <th colspan="2">MONTHLY TOTAL (Combined)</th>
                            <?php if ($selected_month && !empty($school_days)): ?>
                                <?php foreach ($school_days as $day => $day_info): ?>
                                    <td><strong><?= ($daily_male_absent[$day] + $daily_female_absent[$day] ?? 0) ?>/<?= ($daily_male_present[$day] + $daily_female_present[$day] ?? 0) ?></strong></td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php for ($i = 1; $i <= 25; $i++): ?>
                                    <td></td>
                                <?php endfor; ?>
                            <?php endif; ?>
                            <td><strong><?= $monthly_combined_absent_total ?></strong></td>
                            <td><strong><?= $monthly_combined_present_total ?></strong></td>
                            <td></td>
                        </tr>

                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="<?= count($school_days) + 5 ?>">No students found for this section.</td>
                            </tr>
                        <?php endif; ?>

                        <!-- SUMMARY ROWS -->
                        <tr>
                            <th colspan="2" style="text-align: right;">Legend:</th>
                            <td colspan="<?= count($school_days) ?>" style="text-align: left; padding-left: 20px;">
                                X - Absent, Blank - Present (including Late/Tardy)
                            </td>
                            <td colspan="3" style="text-align: left; padding-left: 20px;">
                                Daily totals show: Absent/Present
                            </td>
                        </tr>

                        <!-- ADVISER SIGNATURE -->
                        <tr>
                            <th colspan="<?= count($school_days) + 2 ?>" style="text-align: right; padding-right: 50px;">
                                Prepared by:
                            </th>
                            <td colspan="3" style="text-align: center;">
                                <div style="height: 40px;"></div>
                                <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;">
                                    <strong><?= htmlspecialchars($adviser_data['firstname'] . ' ' . $adviser_data['lastname'] ?? 'Adviser Name') ?></strong><br>
                                    Class Adviser
                                </div>
                            </td>
                        </tr>

                        <!-- CERTIFICATION -->
                        <tr>
                            <th colspan="<?= count($school_days) + 2 ?>" style="text-align: right; padding-right: 50px;">
                                Certified Correct:
                            </th>
                            <td colspan="3" style="text-align: center;">
                                <div style="height: 40px;"></div>
                                <div style="border-top: 1px solid #000; width: 80%; margin: 0 auto;">
                                    <strong>School Principal Name</strong><br>
                                    School Principal
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</main>

<style>
    main {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        padding: 20px;
        max-width: 80vw;
        max-height: 88vh !important;
        overflow: auto !important;
    }

    .main-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin: 0 auto;
        min-width: 1500px !important;
    }

    .scroll-container {
        width: 100%;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-top: 20px;
        overflow-x: auto;
    }

    .form-table {
        min-width: 2500px;
        width: 100%;
    }

    .form-table>div {
        display: flex;
        border-bottom: 1px solid #dee2e6;
    }

    .form-table>div>div {
        padding: 8px;
        border-right: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .form-table>div>div:last-child {
        border-right: none;
    }

    .header-row {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    input {
        border: 1px solid #ced4da;
        padding: 4px 8px;
        border-radius: 4px;
        width: 100%;
        box-sizing: border-box;
    }

    .nested-columns {
        display: flex;
        flex-direction: column;
    }

    .nested-row {
        display: flex;
        flex: 1;
    }

    .nested-cell {
        flex: 1;
        border-right: 1px solid #dee2e6;
        padding: 4px;
        text-align: center;
        font-size: 0.85rem;
    }

    .nested-cell:last-child {
        border-right: none;
    }

    .scroll-indicator {
        text-align: center;
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .form-title {
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .form-section {
        margin-bottom: 15px;
    }

    .table-bordered {
        border: 1px solid #000;
        width: 100%;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
        vertical-align: middle;
        font-weight: bold;
    }

    .table-bordered th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .table-bordered td {
        font-weight: normal;
        height: 35px;
    }

    .responsive-table {
        overflow-x: auto;
        width: 100%;
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    @media print {
        .main-container {
            min-width: auto !important;
            width: 100% !important;
            padding: 0;
        }

        main {
            max-width: 100% !important;
            padding: 0;
        }

        .table-responsive {
            max-height: none !important;
            overflow: visible !important;
        }

        table {
            font-size: 9px !important;
        }

        .table-bordered td,
        .table-bordered th {
            padding: 2px !important;
            height: auto !important;
        }
    }
</style>

<script>
    function validateBeforeSave() {
        const month = document.getElementById('month_attendance').value;
        
        if (!month) {
            alert('Please select a month before saving!');
            return false;
        }
        
        // Convert month name to date (first day of the month)
        const year = new Date().getFullYear();
        const monthMap = {
            'JANUARY': '01',
            'FEBRUARY': '02',
            'MARCH': '03',
            'APRIL': '04',
            'MAY': '05',
            'JUNE': '06',
            'JULY': '07',
            'AUGUST': '08',
            'SEPTEMBER': '09',
            'OCTOBER': '10',
            'NOVEMBER': '11',
            'DECEMBER': '12'
        };
        
        const monthNum = monthMap[month];
        if (monthNum) {
            const dateValue = `${year}-${monthNum}-01`;
            document.getElementById('report_for_the_month_of').value = dateValue;
        }
        
        return true;
    }

    function generateReport() {
        // Show loading indicator
        const button = document.querySelector('.btn-secondary');
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Preparing...';
        button.disabled = true;

        // Check if month is selected
        const month = document.getElementById('month_attendance').value;
        if (!month) {
            alert('Please select a month first!');
            button.innerHTML = originalText;
            button.disabled = false;
            return;
        }

        // Store original styles
        const originalContainerWidth = document.querySelector('.main-container').style.width;
        const originalTableWidth = document.querySelector('table').style.minWidth;

        // Adjust for printing
        document.querySelector('.main-container').style.width = '100%';
        document.querySelector('table').style.minWidth = '100%';

        // Hide buttons for print
        const printButtons = document.querySelector('.mt-3.text-start');
        if (printButtons) {
            printButtons.style.display = 'none';
        }

        // Create a custom print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Attendance Report - ${month}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                .report-header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 3px solid #000;
                    padding-bottom: 20px;
                }
                .report-header h1 {
                    margin: 0;
                    font-size: 24px;
                    color: #2c3e50;
                }
                .report-header h2 {
                    margin: 5px 0 15px 0;
                    font-size: 18px;
                    color: #7f8c8d;
                }
                .report-info {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                .summary {
                    background: #f8f9fa;
                    padding: 15px;
                    margin-bottom: 20px;
                    border-radius: 5px;
                    border-left: 4px solid #3498db;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 10px;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 4px;
                    text-align: center;
                    vertical-align: middle;
                }
                th {
                    background-color: #f8f9fa;
                    font-weight: bold;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 11px;
                    color: #777;
                    text-align: center;
                }
                .no-print {
                    text-align: center;
                    margin-top: 20px;
                }
                .no-print button {
                    padding: 10px 20px;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    margin: 0 5px;
                }
                .btn-print {
                    background: #007bff;
                }
                .btn-close {
                    background: #6c757d;
                }
                @media print {
                    body {
                        margin: 0;
                        padding: 10px;
                    }
                    .no-print {
                        display: none !important;
                    }
                    .report-header h1 {
                        font-size: 16px;
                    }
                    .report-header h2 {
                        font-size: 11px;
                    }
                    .report-info {
                        font-size: 9px;
                    }
                    .summary {
                        padding: 8px;
                        font-size: 8px;
                    }
                    table {
                        font-size: 8px;
                    }
                    th, td {
                        padding: 2px;
                    }
                    .footer {
                        font-size: 8px;
                    }
                }
            </style>
        </head>
        <body>
    `);

        // Get form data
        const gradeLevel = document.querySelector('input[name="grade_level"]').value;
        const sectionName = document.getElementById('section_name').value;
        const schoolYear = document.querySelector('input[name="school_year_name"]').value;
        const schoolName = document.querySelector('input[name="school_name"]').value;
        const schoolId = document.querySelector('input[name="school_id"]').value;
        const currentDate = new Date();

        // Add header
        printWindow.document.write(`
            <div class="report-header">
                <h1>DEPARTMENT OF EDUCATION</h1>
                <h2>School Form 2 - Daily Attendance Report of Learners</h2>
                <div class="report-info">
                    <div>
                        <strong>School:</strong> ${schoolName}<br>
                        <strong>School ID:</strong> ${schoolId}<br>
                        <strong>Month:</strong> ${month}
                    </div>
                    <div>
                        <strong>School Year:</strong> ${schoolYear}<br>
                        <strong>Grade Level:</strong> ${gradeLevel}<br>
                        <strong>Section:</strong> ${sectionName}
                    </div>
                    <div>
                        <strong>Generated:</strong> ${currentDate.toLocaleDateString()}<br>
                        <strong>Time:</strong> ${currentDate.toLocaleTimeString()}
                    </div>
                </div>
            </div>
        `);

        // Add the table
        const tableHTML = document.querySelector('.table-responsive').innerHTML;
        printWindow.document.write(`
            <div class="summary">
                <strong>Legend:</strong> X = Absent, Blank = Present (including Late/Tardy) | 
                <strong>Daily Totals:</strong> Absent/Present
            </div>
            ${tableHTML}
        `);

        // Add footer
        printWindow.document.write(`
            <div class="footer">
                <div style="margin-bottom: 10px;">
                    <strong>Department of Education - Official Attendance Report</strong><br>
                    This is a computer-generated document. Valid without signature for electronic copy.
                </div>
                <div>
                    Generated on: ${currentDate.toLocaleString()} | 
                    System Generated Report
                </div>
            </div>
            <div class="no-print">
                <button class="btn-print" onclick="window.print()">Print Report</button>
                <button class="btn-close" onclick="window.close()">Close</button>
            </div>
        `);

        printWindow.document.write('</body></html>');
        printWindow.document.close();

        // Restore original styles
        document.querySelector('.main-container').style.width = originalContainerWidth;
        document.querySelector('table').style.minWidth = originalTableWidth;

        // Show buttons again
        if (printButtons) {
            printButtons.style.display = '';
        }

        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;

        // Focus on the print window
        printWindow.focus();
    }
</script>