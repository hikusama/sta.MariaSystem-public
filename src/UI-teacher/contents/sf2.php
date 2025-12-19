<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}
// First, get section and grade level
$stmt = $pdo->prepare("SELECT en.section_name, en.Grade_level FROM enrolment en
    INNER JOIN users u ON en.adviser_id = u.user_id
    WHERE u.user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$getData = $stmt->fetch(PDO::FETCH_ASSOC);
$section_name = $getData["section_name"] ?? '';
$Grade_level = $getData["Grade_level"] ?? '';

// Get school form data
$stmt = $pdo->prepare("SELECT * FROM sf_add_data WHERE sf_type = 'sf_8'");
$stmt->execute();
$data_sf_eight = $stmt->fetch(PDO::FETCH_ASSOC);

// Get active school year
$stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
$stmt->execute();
$sy = $stmt->fetch(PDO::FETCH_ASSOC);

// Get adviser data
$stmtAdviser_data = $pdo->prepare("SELECT * FROM users
    INNER JOIN classes ON users.user_id = classes.adviser_id
    WHERE classes.adviser_id = :user_id");
$stmtAdviser_data->execute(['user_id' => $user_id]);
$adviser_data = $stmtAdviser_data->fetch(PDO::FETCH_ASSOC);

// Get selected month from POST or default
$selected_month = $_POST['month'] ?? '';
$selected_year = date('Y'); // Current year or get from school year

// Function to get all school days for a month
function getSchoolDaysForMonth($month, $year) {
    $month_num = date('m', strtotime($month . " 1, $year"));
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_num, $year);
    
    $school_days = [];
    $weekday_count = 0;
    
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date = "$year-$month_num-" . str_pad($day, 2, '0', STR_PAD_LEFT);
        $weekday = date('w', strtotime($date)); // 0 = Sunday, 1 = Monday, etc.
        
        // Only Monday to Friday are school days
        if ($weekday >= 1 && $weekday <= 5) {
            $weekday_count++;
            $school_days[$day] = [
                'date' => $date,
                'weekday' => $weekday,
                'weekday_name' => date('D', strtotime($date)),
                'position' => $weekday_count
            ];
        }
    }
    
    return $school_days;
}

// Get school days if month is selected
$school_days = [];
if ($selected_month) {
    $school_days = getSchoolDaysForMonth($selected_month, $selected_year);
}

// Get all students for this section, separated by gender
$stmt = $pdo->prepare("SELECT s.*, e.enrolment_id 
    FROM student s 
    INNER JOIN enrolment e ON s.student_id = e.student_id 
    WHERE e.section_name = :section_name 
    AND e.Grade_level = :grade_level 
    AND e.enrolment_Status = 'Approved'
    ORDER BY s.sex, s.lname, s.fname, s.mname");
$stmt->execute([
    'section_name' => $section_name,
    'grade_level' => $Grade_level
]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate male and female students
$male_students = array_filter($students, function($student) {
    return strtoupper($student['sex'] ?? '') === 'MALE';
});

$female_students = array_filter($students, function($student) {
    return strtoupper($student['sex'] ?? '') === 'FEMALE';
});

// Get attendance data for selected month
$student_attendance = [];
$student_present_counts = [];
$student_absent_counts = [];
if ($selected_month && !empty($students)) {
    $student_ids = array_column($students, 'student_id');
    $placeholders = str_repeat('?,', count($student_ids) - 1) . '?';
    
    // Convert month name to number
    $month_num = date('m', strtotime($selected_month . " 1, $selected_year"));
    
    // Get attendance records for the selected month
    $stmt = $pdo->prepare("SELECT a.*, s.student_id 
        FROM attendance a 
        INNER JOIN student s ON a.student_id = s.student_id 
        WHERE s.student_id IN ($placeholders) 
        AND MONTH(a.morning_attendance) = ? 
        AND YEAR(a.morning_attendance) = ?");
    
    $params = array_merge($student_ids, [$month_num, $selected_year]);
    $stmt->execute($params);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize attendance by student and date
    foreach ($attendance_records as $record) {
        $student_id = $record['student_id'];
        $date = date('Y-m-d', strtotime($record['morning_attendance']));
        $day = date('j', strtotime($record['morning_attendance']));
        $attendance_type = $record['attendance_summary'] ?? $record['attendance_type'];
        
        if (!isset($student_attendance[$student_id])) {
            $student_attendance[$student_id] = [];
        }
        
        $student_attendance[$student_id][$day] = $attendance_type;
        
        // Initialize counts if not set
        if (!isset($student_present_counts[$student_id])) {
            $student_present_counts[$student_id] = 0;
            $student_absent_counts[$student_id] = 0;
        }
        
        // Count present days (including tardy/late as present)
        if (in_array(strtoupper($attendance_type), ['PRESENT', 'LATE', 'HALF-DAY', 'HALF-DAY-LATE'])) {
            $student_present_counts[$student_id]++;
        } elseif (strtoupper($attendance_type) === 'ABSENT') {
            $student_absent_counts[$student_id]++;
        }
    }
}

// Calculate daily totals for male, female, and combined
$daily_male_present = [];
$daily_female_present = [];
$daily_combined_present = [];
$daily_male_absent = [];
$daily_female_absent = [];
$daily_combined_absent = [];

if ($selected_month && !empty($school_days)) {
    foreach ($school_days as $day => $day_info) {
        $daily_male_present[$day] = 0;
        $daily_female_present[$day] = 0;
        $daily_male_absent[$day] = 0;
        $daily_female_absent[$day] = 0;
        
        // Count male attendance per day
        foreach ($male_students as $student) {
            $student_id = $student['student_id'];
            if (isset($student_attendance[$student_id][$day])) {
                $attendance_type = strtoupper($student_attendance[$student_id][$day]);
                if (in_array($attendance_type, ['PRESENT', 'LATE', 'HALF-DAY', 'HALF-DAY-LATE'])) {
                    $daily_male_present[$day]++;
                } elseif ($attendance_type === 'ABSENT') {
                    $daily_male_absent[$day]++;
                }
            }
        }
        
        // Count female attendance per day
        foreach ($female_students as $student) {
            $student_id = $student['student_id'];
            if (isset($student_attendance[$student_id][$day])) {
                $attendance_type = strtoupper($student_attendance[$student_id][$day]);
                if (in_array($attendance_type, ['PRESENT', 'LATE', 'HALF-DAY', 'HALF-DAY-LATE'])) {
                    $daily_female_present[$day]++;
                } elseif ($attendance_type === 'ABSENT') {
                    $daily_female_absent[$day]++;
                }
            }
        }
        
        // Combined totals per day
        $daily_combined_present[$day] = $daily_male_present[$day] + $daily_female_present[$day];
        $daily_combined_absent[$day] = $daily_male_absent[$day] + $daily_female_absent[$day];
    }
}

// Calculate monthly totals
$monthly_male_absent_total = 0;
$monthly_female_absent_total = 0;
$monthly_combined_absent_total = 0;
$monthly_male_present_total = 0;
$monthly_female_present_total = 0;
$monthly_combined_present_total = 0;

// Calculate totals from student data
foreach ($male_students as $student) {
    $student_id = $student['student_id'];
    $monthly_male_absent_total += $student_absent_counts[$student_id] ?? 0;
    $monthly_male_present_total += $student_present_counts[$student_id] ?? 0;
}

foreach ($female_students as $student) {
    $student_id = $student['student_id'];
    $monthly_female_absent_total += $student_absent_counts[$student_id] ?? 0;
    $monthly_female_present_total += $student_present_counts[$student_id] ?? 0;
}

$monthly_combined_absent_total = $monthly_male_absent_total + $monthly_female_absent_total;
$monthly_combined_present_total = $monthly_male_present_total + $monthly_female_present_total;

$count = 1;
?>

<main>
    <form class="main-container" id="sfEight-form" style="width: 1900px;" method="POST">
        <div class="mt-3 text-start">
            <button type="submit" class="btn btn-danger" name="save">Save Data</button>
            <button type="button" class="btn btn-secondary" onclick="generateReport()">Generate Report</button>
        </div>
        <input type="hidden" name="id" value="<?= htmlspecialchars($data_sf_eight["sf_add_data_id"] ?? '') ?>">
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
                        <input readonly class="form-control" type="text" name="school_year_name" value="<?= htmlspecialchars($sy["school_year_name"] ?? '') ?>">
                    </div>
                    <div class="col-md-5 ms-5 d-flex">
                        <label class="me-2 col-4">Report for the month of</label>
                        <select name="month" id="month_attendance" class="form-select" onchange="this.form.submit()">
                            <option value="">Select Month</option>
                            <?php
                            $months = ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
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
                            <?php
                            // Display dates row
                            if ($selected_month && !empty($school_days)) {
                                foreach ($school_days as $day => $day_info) {
                                    echo "<th width='0.5%' class='m-0 p-0 text-center'><p>{$day}</p></th>";
                                }
                            } else {
                                // Display empty cells if no month selected
                                for ($i = 1; $i <= 25; $i++) {
                                    echo "<th width='0.5%' class='m-0 p-0 text-center'><p></p></th>";
                                }
                            }
                            ?>
                        </tr>
                        <tr class="text-center p-0 m-0">
                            <?php
                            // Display weekday abbreviations
                            if ($selected_month && !empty($school_days)) {
                                foreach ($school_days as $day => $day_info) {
                                    $weekday_abbr = substr($day_info['weekday_name'], 0, 1);
                                    echo "<th width='0.5%' class='text-center p-0 m-0'>$weekday_abbr</th>";
                                }
                            } else {
                                // Display empty weekday cells if no month selected
                                $weekdays = ['M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F', 'M', 'T', 'W', 'TH', 'F'];
                                foreach ($weekdays as $abbr) {
                                    echo "<th width='0.5%' class='text-center p-0 m-0'>$abbr</th>";
                                }
                            }
                            ?>
                            <th width="7.5">ABSENT</th>
                            <th width="7.5">PRESENT</th>
                        </tr>
                    </thead>
                    
                    <tbody style="font-size: 0.85rem;">
                        <!-- all male -->
                        <?php 
                        $male_count = 1;
                        if (!empty($male_students)) {
                            foreach($male_students as $student) : 
                                $student_id = $student['student_id'];
                                
                                // Calculate absent count and get present count
                                $absent_count = $student_absent_counts[$student_id] ?? 0;
                                $present_count = $student_present_counts[$student_id] ?? 0;
                        ?>
                        <tr>
                            <td><?= $male_count++ ?></td>
                            <td style="text-align: left; padding-left: 10px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars(strtoupper($student["lname"] . ', ' . $student["fname"] . ' ' . $student["mname"])) ?>">
                                <?= htmlspecialchars(strtoupper($student["lname"] . ', ' . $student["fname"] . ' ' . $student["mname"])) ?>
                            </td>
                            
                            <?php
                            // Display attendance cells for each school day
                            if ($selected_month && !empty($school_days)) {
                                foreach ($school_days as $day => $day_info) {
                                    $attendance_status = $student_attendance[$student_id][$day] ?? '';
                                    $is_absent = (strtoupper($attendance_status) === 'ABSENT');
                                    
                                    echo '<td>';
                                    if ($is_absent) {
                                        echo 'X';
                                    }
                                    echo '</td>';
                                }
                            } else {
                                // Display empty cells if no month selected
                                for ($i = 1; $i <= 25; $i++) {
                                    echo '<td></td>';
                                }
                            }
                            ?>
                            
                            <td><strong><?= $absent_count ?></strong></td>
                            <td><strong><?= $present_count ?></strong></td>
                            <td>
                                <?php
                                // Display remarks based on enrolment status
                                $status = $student['enrolment_status'] ?? '';
                                if ($status == 'dropped') {
                                    echo 'DROPPED OUT';
                                } elseif ($status == 'transferred_out') {
                                    echo 'TRANSFERRED OUT';
                                } elseif ($status == 'transferred_in') {
                                    echo 'TRANSFERRED IN';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; 
                        } ?>
                        
                        <!-- MONTHLY TOTAL FOR MALE -->
                        <tr>
                            <th colspan="2">MONTHLY TOTAL (Male)</th>
                            <?php
                            if ($selected_month && !empty($school_days)) {
                                foreach ($school_days as $day => $day_info) {
                                    $absent_count = $daily_male_absent[$day] ?? 0;
                                    $present_count = $daily_male_present[$day] ?? 0;
                                    echo '<td><strong>' . $absent_count . '/' . $present_count . '</strong></td>';
                                }
                            } else {
                                for ($i = 1; $i <= 25; $i++) {
                                    echo '<td></td>';
                                }
                            }
                            ?>
                            <td><strong><?= $monthly_male_absent_total ?></strong></td>
                            <td><strong><?= $monthly_male_present_total ?></strong></td>
                            <td></td>
                        </tr>
                        
                        <!-- all female -->
                        <?php 
                        $female_count = 1;
                        if (!empty($female_students)) {
                            foreach($female_students as $student) : 
                                $student_id = $student['student_id'];
                                
                                // Calculate absent count and get present count
                                $absent_count = $student_absent_counts[$student_id] ?? 0;
                                $present_count = $student_present_counts[$student_id] ?? 0;
                        ?>
                        <tr>
                            <td><?= $female_count++ ?></td>
                            <td style="text-align: left; padding-left: 10px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars(strtoupper($student["lname"] . ', ' . $student["fname"] . ' ' . $student["mname"])) ?>
                            </td>
                            
                            <?php
                            // Display attendance cells for each school day
                            if ($selected_month && !empty($school_days)) {
                                foreach ($school_days as $day => $day_info) {
                                    $attendance_status = $student_attendance[$student_id][$day] ?? '';
                                    $is_absent = (strtoupper($attendance_status) === 'ABSENT');
                                    
                                    echo '<td>';
                                    if ($is_absent) {
                                        echo 'X';
                                    }
                                    echo '</td>';
                                }
                            } else {
                                // Display empty cells if no month selected
                                for ($i = 1; $i <= 25; $i++) {
                                    echo '<td></td>';
                                }
                            }
                            ?>
                            
                            <td><strong><?= $absent_count ?></strong></td>
                            <td><strong><?= $present_count ?></strong></td>
                            <td>
                                <?php
                                // Display remarks based on enrolment status
                                $status = $student['enrolment_status'] ?? '';
                                if ($status == 'dropped') {
                                    echo 'DROPPED OUT';
                                } elseif ($status == 'transferred_out') {
                                    echo 'TRANSFERRED OUT';
                                } elseif ($status == 'transferred_in') {
                                    echo 'TRANSFERRED IN';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; 
                        } ?>
                        
                        <!-- MONTHLY TOTAL FOR FEMALE -->
                        <tr>
                            <th colspan="2">MONTHLY TOTAL (Female)</th>
                            <?php
                            if ($selected_month && !empty($school_days)) {
                                foreach ($school_days as $day => $day_info) {
                                    $absent_count = $daily_female_absent[$day] ?? 0;
                                    $present_count = $daily_female_present[$day] ?? 0;
                                    echo '<td><strong>' . $absent_count . '/' . $present_count . '</strong></td>';
                                }
                            } else {
                                for ($i = 1; $i <= 25; $i++) {
                                    echo '<td></td>';
                                }
                            }
                            ?>
                            <td><strong><?= $monthly_female_absent_total ?></strong></td>
                            <td><strong><?= $monthly_female_present_total ?></strong></td>
                            <td></td>
                        </tr>
                        
                        <!-- COMBINED MONTHLY TOTAL -->
                        <tr>
                            <th colspan="2">MONTHLY TOTAL (Combined)</th>
                            <?php
                            if ($selected_month && !empty($school_days)) {
                                foreach ($school_days as $day => $day_info) {
                                    $absent_count = $daily_combined_absent[$day] ?? 0;
                                    $present_count = $daily_combined_present[$day] ?? 0;
                                    echo '<td><strong>' . $absent_count . '/' . $present_count . '</strong></td>';
                                }
                            } else {
                                for ($i = 1; $i <= 25; $i++) {
                                    echo '<td></td>';
                                }
                            }
                            ?>
                            <td><strong><?= $monthly_combined_absent_total ?></strong></td>
                            <td><strong><?= $monthly_combined_present_total ?></strong></td>
                            <td></td>
                        </tr>
                        
                        <?php 
                        // Show message if no students found
                        if (empty($students)) { ?>
                        <tr>
                            <td colspan="<?= count($school_days) + 5 ?>">No students found for this section.</td>
                        </tr>
                        <?php } ?>
                        
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
</style>

<script>
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
                @media print {
                    body {
                        margin: 0;
                        padding: 10px;
                    }
                    .no-print {
                        display: none !important;
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

    // Get logo sources from the existing page so the print window shows the same images
    const logoLeftEl = document.getElementById('school_logo');
    const logoRightEl = document.getElementById('deped_logo');
    const logoLeftSrc = logoLeftEl ? new URL(logoLeftEl.src, window.location.href).href : (Array.from(document.querySelectorAll('img')).find(img => img.src && img.src.includes('logo.png'))?.src || '');
    const logoRightSrc = logoRightEl ? new URL(logoRightEl.src, window.location.href).href : (Array.from(document.querySelectorAll('img')).find(img => img.src && img.src.includes('deped.png'))?.src || '');
    
    // Add header (including logos)
    printWindow.document.write(`
        <div class="report-header" style="display:flex; align-items:center; justify-content:space-between;">
            <div style="flex:1; text-align:left;"><img src="${logoLeftSrc}" alt="School Logo" style="height:120px; object-fit:contain;"></div>
            <div style="flex:2; text-align:center;">
                <h1>DEPARTMENT OF EDUCATION</h1>
                <h2>School Form 2 - Daily Attendance Report of Learners</h2>
                <div class="report-info" style="display:flex; justify-content:space-between; margin-top:10px; font-size:14px;">
                    <div style="text-align:left;">
                        <strong>School:</strong> ${schoolName}<br>
                        <strong>School ID:</strong> ${schoolId}<br>
                        <strong>Month:</strong> ${month}
                    </div>
                    <div style="text-align:center;">
                        <strong>School Year:</strong> ${schoolYear}<br>
                        <strong>Grade Level:</strong> ${gradeLevel}<br>
                        <strong>Section:</strong> ${sectionName}
                    </div>
                    <div style="text-align:right;">
                        <strong>Generated:</strong> ${new Date().toLocaleDateString()}<br>
                        <strong>Time:</strong> ${new Date().toLocaleTimeString()}
                    </div>
                </div>
            </div>
            <div style="flex:1; text-align:right;"><img src="${logoRightSrc}" alt="DepEd Logo" style="height:120px; object-fit:contain;"></div>
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
                Generated on: ${new Date().toLocaleString()} | 
                System Generated Report
            </div>
        </div>
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Print Report
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                Close
            </button>
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

// Add print button to the page
// document.addEventListener('DOMContentLoaded', function() {
//     const buttonContainer = document.querySelector('.mt-3.text-start');
//     if (buttonContainer) {
//         const printButton = document.createElement('button');
//         printButton.type = 'button';
//         printButton.className = 'btn btn-info ms-2';
//         printButton.innerHTML = '<i class="bi bi-printer d-none"></i> Print Report';
//         printButton.onclick = function() {
//             // Simple print function
//             const month = document.getElementById('month_attendance').value;
//             if (!month) {
//                 alert('Please select a month first!');
//                 return;
//             }
            
//             // Show print dialog
//             window.print();
//         };
//         buttonContainer.appendChild(printButton);
//     }
// });
</script>