<?php
    require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
?>

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
    min-width: 2500px !important;
    /* overflow: auto !important; */
}

.scroll-container {
    width: 100%;
    /* overflow-x: auto; */
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin-top: 20px;
}

.form-table {
    min-width: 2500px;
    /* Increased to ensure scrolling */
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
</style>
<main>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM sf_add_data");
    $stmt->execute();
    $data_sf_four = $stmt->fetch(PDO::FETCH_ASSOC);
?>

    <?php
// Get selected month from the form
$selected_month = isset($_POST['month']) ? $_POST['month'] : (isset($_GET['month']) ? $_GET['month'] : '');
$month_number = 0;

// Convert month name to month number
if ($selected_month) {
    $months = [
        'JANUARY' => 1, 'FEBRUARY' => 2, 'MARCH' => 3, 'APRIL' => 4,
        'MAY' => 5, 'JUNE' => 6, 'JULY' => 7, 'AUGUST' => 8,
        'SEPTEMBER' => 9, 'OCTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12
    ];
    $month_number = $months[strtoupper($selected_month)] ?? 0;
}

// Get current year for filtering
$current_year = date('Y');

// Grade filter (for the adviser/section half)
$selected_grade = $_POST['filter_grade'] ?? $_GET['filter_grade'] ?? '';
// fetch available grade levels to populate the filter
$grade_stmt = $pdo->prepare("SELECT DISTINCT Grade_level FROM enrolment WHERE enrolment_Status = 'Approved' ORDER BY Grade_level");
$grade_stmt->execute();
$grade_levels = $grade_stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT * FROM sf_add_data");
$stmt->execute();
$data_sf_four = $stmt->fetch(PDO::FETCH_ASSOC);
?>

    <form class="main-container" id="sfFour-form" method="POST">
        <div class="mt-3 text-start">
            <button type="submit" class="btn btn-primary">Save Data</button>
            <button class="btn btn-secondary">Generate Report</button>
        </div>
        <input type="hidden" name="id" value="<?= htmlspecialchars($data_sf_four["sf_add_data_id"] ?? '') ?>">

        <div class="form-title text-center w-100" style="display:flex; align-items:center; justify-content:space-between;">
            <div style="flex:1; text-align:left;">
                <img id="school_logo" src="../../assets/image/logo.png" alt="School Logo" style="height:120px; object-fit:contain;">
            </div>
            <div style="flex:2; text-align:center;">
                <h2 style="margin:0;">School Form 4 (SF4) Monthly Learner's Movement and Attendance</h2>
                <p class="text-muted" style="margin:5px 0 0 0;">(this replaces Form 3 & STS Form 4-Absenteeism and Dropout Profile)</p>
            </div>
            <div style="flex:1; text-align:right;">
                <img id="deped_logo" src="../../assets/image/deped.png" alt="DepEd Logo" style="height:120px; object-fit:contain;">
            </div>
        </div>

        <div class="form-section">
            <div class="row mb-2">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School ID</label>
                        <input type="text" name="school_id"
                            value="<?= htmlspecialchars($data_sf_four["school_id"] ?? '') ?>" class="me-2 flex-grow-1">
                        <input type="text" name="region" value="<?= htmlspecialchars($data_sf_four["region"] ?? '') ?>"
                            class="flex-grow-1" placeholder="Region">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Division</label>
                        <input type="text" name="Division"
                            value="<?= htmlspecialchars($data_sf_four["Division"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">District</label>
                        <input type="text" name="district"
                            value="<?= htmlspecialchars($data_sf_four["district"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Name</label>
                        <input type="text" name="school_name"
                            value="<?= htmlspecialchars($data_sf_four["school_name"] ?? '') ?>" class="flex-grow-1">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">School Year</label>
                        <?php
                    $stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
                    $stmt->execute();
                    $sy = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                        <input readonly class="form-control" type="text" name="school_year_name"
                            value="<?= $sy["school_year_name"] ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <label class="me-2 col-4">Report for the month of</label>
                        <div style="display:flex; gap:12px; align-items:center;">
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

                            <select name="filter_grade" id="filter_grade" class="form-select" onchange="this.form.submit()">
                                <option value="">All Grades</option>
                                <?php foreach ($grade_levels as $g):
                                    $selg = ($selected_grade == $g) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($g) . "' $selg>" . htmlspecialchars($g) . "</option>";
                                endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($selected_month && $month_number): ?>
        <?php
    // Calculate first and last day of selected month
    $first_day_of_month = date("$current_year-$month_number-01");
    $last_day_of_month = date('Y-m-d', strtotime("$first_day_of_month +1 month -1 day"));

    // Function to count school days (Mon-Fri) in a month
    function count_school_days($month, $year) {
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $count = 0;
        for ($d = 1; $d <= $days_in_month; $d++) {
            $w = date('w', strtotime(sprintf('%04d-%02d-%02d', $year, $month, $d)));
            if ($w >= 1 && $w <= 5) $count++;
        }
        return $count;
    }

    $school_days_count = count_school_days($month_number, $current_year);
    ?>

        <div class="scroll-container">
            <style>
            table {
                width: 100%;
                border-collapse: collapse;
                text-align: center;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 5px;
                vertical-align: middle;
            }

            th {
                background: #f8f8f8;
            }
            </style>

            <div class="">
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">GRADE / YEAR LEVEL</th>
                            <th rowspan="2">SECTION</th>
                            <th rowspan="2">NAME OF ADVISER</th>
                            <th colspan="3">REGISTERED LEARNERS (As of End of the Month)</th>
                            <th colspan="6">ATTENDANCE</th>
                            <th colspan="9">NO LONGER PARTICIPATING IN LEARNING ACTIVITIES</th>
                            <th colspan="9">TRANSFERRED OUT</th>
                            <th colspan="9">TRANSFERRED IN</th>
                        </tr>
                        <tr>
                            <th colspan="3"></th>
                            <th colspan="3">Daily Average</th>
                            <th colspan="3">Percentage for the Month</th>
                            <th colspan="3">(A) Cumulative as of Previous Month</th>
                            <th colspan="3">(B) For the Month</th>
                            <th colspan="3">(A + B) Cumulative as End of Month</th>
                            <th colspan="3">(A) Cumulative as of Previous Month</th>
                            <th colspan="3">(B) For the Month</th>
                            <th colspan="3">(A + B) Cumulative as End of Month</th>
                            <th colspan="3">(A) Cumulative as of Previous Month</th>
                            <th colspan="3">(B) For the Month</th>
                            <th colspan="3">(A + B) Cumulative as End of Month</th>
                        </tr>
                        <tr>
                            <th colspan="3"></th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th colspan="9"></th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                            <th>M</th>
                            <th>F</th>
                            <th>T</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    // Registered learners per adviser - allow filtering by selected grade (top half only)
                    $adviserSql = "
                        SELECT 
                            u.user_id AS adviser_id,
                            u.firstname AS adviser_fname,
                            u.lastname AS adviser_lname,
                            s.section_name AS sec_name,
                            e.Grade_level AS grade_level,  
                            SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_registered,
                            SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_registered,
                            COUNT(st.student_id) AS total_registered
                        FROM enrolment e
                        INNER JOIN sections s ON s.section_name = e.section_name
                        INNER JOIN users u ON e.adviser_id = u.user_id
                        INNER JOIN student st ON e.student_id = st.student_id
                        WHERE e.enrolment_status = 'Approved'
                        AND DATE(st.enrolled_date) <= :last_day_of_month
                        AND st.enrolment_status NOT IN ('transferred_out','dropped','not_active')
                        ";

                    $params = ['last_day_of_month' => $last_day_of_month];
                    if (!empty($selected_grade)) {
                        $adviserSql .= " AND e.Grade_level = :selected_grade";
                        $params['selected_grade'] = $selected_grade;
                    }

                    $adviserSql .= " GROUP BY s.section_name, e.Grade_level, u.user_id, u.firstname, u.lastname
                        ORDER BY e.Grade_level ASC";

                    $stmt = $pdo->prepare($adviserSql);
                    $stmt->execute($params);
                    $advisers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach($advisers as $row):
                        $adviserId = $row['adviser_id'];
                        $gradeLevel = $row['grade_level'];

                        // Attendance per month - Filtered by selected month
                        $stmtAttend = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_present,
                                SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_present,
                                COUNT(*) AS total_present
                            FROM attendance a
                            INNER JOIN student st ON a.student_id = st.student_id
                            INNER JOIN enrolment e ON a.student_id = e.student_id
                            WHERE a.adviser_id = :adviser_id
                            AND e.Grade_level = :grade_level
                            AND MONTH(a.morning_attendance) = :month_number
                            AND YEAR(a.morning_attendance) = :current_year
                            AND a.attendance_type = 'Present'
                        ");
                        $stmtAttend->execute([
                            'adviser_id' => $adviserId, 
                            'grade_level' => $gradeLevel,
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $attend = $stmtAttend->fetch(PDO::FETCH_ASSOC);

                        // NO LONGER PARTICIPATING - (A) Cumulative as of Previous Month
                        $stmtNotActivePrev = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS male_prev,
                                SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS female_prev
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.adviser_id = :adviser_id
                            AND e.Grade_level = :grade_level
                            AND st.enrolment_status = 'not_active'
                        ");
                        $stmtNotActivePrev->execute([
                            'adviser_id' => $adviserId, 
                            'grade_level' => $gradeLevel, // Fixed: changed $grade_level to $gradeLevel
                            'first_day_of_month' => $first_day_of_month
                        ]);
                        $notActivePrev = $stmtNotActivePrev->fetch(PDO::FETCH_ASSOC);

                        // NO LONGER PARTICIPATING - (B) For the Month
                        $stmtNotActiveMonth = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS male_month,
                                SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS female_month
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.adviser_id = :adviser_id
                            AND e.Grade_level = :grade_level
                            AND st.enrolment_status = 'not_active'
                        ");
                        $stmtNotActiveMonth->execute([
                            'adviser_id' => $adviserId, 
                            'grade_level' => $gradeLevel, // Fixed: changed $grade_level to $gradeLevel
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $notActiveMonth = $stmtNotActiveMonth->fetch(PDO::FETCH_ASSOC);

                        // Calculate (A + B) Cumulative as End of Month for No Longer Participating
                        $maleNotActiveTotal = ($notActivePrev['male_prev'] ?? 0) + ($notActiveMonth['male_month'] ?? 0);
                        $femaleNotActiveTotal = ($notActivePrev['female_prev'] ?? 0) + ($notActiveMonth['female_month'] ?? 0);
                        $totalNotActiveTotal = $maleNotActiveTotal + $femaleNotActiveTotal;

                        // TRANSFERRED OUT - (A) Cumulative as of Previous Month
                        $stmtTransOutPrev = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS male_prev,
                                SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS female_prev
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.adviser_id = :adviser_id
                            AND e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_out'
                        ");
                        $stmtTransOutPrev->execute([
                            'adviser_id' => $adviserId, 
                            'grade_level' => $gradeLevel, // Fixed: changed $grade_level to $gradeLevel
                            'first_day_of_month' => $first_day_of_month
                        ]);
                        $transOutPrev = $stmtTransOutPrev->fetch(PDO::FETCH_ASSOC);

                        // TRANSFERRED OUT - (B) For the Month
                        $stmtTransOutMonth = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS male_month,
                                SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS female_month
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.adviser_id = :adviser_id
                            AND e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_out'
                        ");
                        $stmtTransOutMonth->execute([
                            'adviser_id' => $adviserId, 
                            'grade_level' => $gradeLevel, // Fixed: changed $grade_level to $gradeLevel
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $transOutMonth = $stmtTransOutMonth->fetch(PDO::FETCH_ASSOC);

                        // Calculate (A + B) Cumulative as End of Month for Transferred Out
                        $maleTransOutTotal = ($transOutPrev['male_prev'] ?? 0) + ($transOutMonth['male_month'] ?? 0);
                        $femaleTransOutTotal = ($transOutPrev['female_prev'] ?? 0) + ($transOutMonth['female_month'] ?? 0);
                        $totalTransOutTotal = $maleTransOutTotal + $femaleTransOutTotal;

                        // TRANSFERRED IN - (A) Cumulative as of Previous Month
                        $stmtTransInPrev = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS male_prev,
                                SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS female_prev
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.adviser_id = :adviser_id
                            AND e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_in'
                        ");
                        $stmtTransInPrev->execute([
                            'adviser_id' => $adviserId, 
                            'grade_level' => $gradeLevel, // Fixed: changed $grade_level to $gradeLevel
                            'first_day_of_month' => $first_day_of_month
                        ]);
                        $transInPrev = $stmtTransInPrev->fetch(PDO::FETCH_ASSOC);

                        // TRANSFERRED IN - (B) For the Month
                        $stmtTransInMonth = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS male_month,
                                SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS female_month
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.adviser_id = :adviser_id
                            AND e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_in'
                        ");
                        $stmtTransInMonth->execute([
                            'adviser_id' => $adviserId, 
                            'grade_level' => $gradeLevel, // Fixed: changed $grade_level to $gradeLevel
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $transInMonth = $stmtTransInMonth->fetch(PDO::FETCH_ASSOC);

                        // Calculate (A + B) Cumulative as End of Month for Transferred In
                        $maleTransInTotal = ($transInPrev['male_prev'] ?? 0) + ($transInMonth['male_month'] ?? 0);
                        $femaleTransInTotal = ($transInPrev['female_prev'] ?? 0) + ($transInMonth['female_month'] ?? 0);
                        $totalTransInTotal = $maleTransInTotal + $femaleTransInTotal;

                        // Calculate daily averages and attendance percentages (using school days count)
                        $male_daily_avg = $school_days_count > 0 ? round((($attend['male_present'] ?? 0) / $school_days_count), 2) : 0;
                        $female_daily_avg = $school_days_count > 0 ? round((($attend['female_present'] ?? 0) / $school_days_count), 2) : 0;
                        $total_daily_avg = $school_days_count > 0 ? round((($attend['total_present'] ?? 0) / $school_days_count), 2) : 0;

                        $malePercentage = 0;
                        $femalePercentage = 0;
                        $totalPercentage = 0;

                        if ($row['male_registered'] > 0) {
                            $malePercentage = round((($male_daily_avg) / $row['male_registered']) * 100, 2);
                        }
                        if ($row['female_registered'] > 0) {
                            $femalePercentage = round((($female_daily_avg) / $row['female_registered']) * 100, 2);
                        }
                        if ($row['total_registered'] > 0) {
                            $totalPercentage = round((($total_daily_avg) / $row['total_registered']) * 100, 2);
                        }
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($gradeLevel) ?></td>
                            <td><?= htmlspecialchars($row['sec_name']) ?></td>
                            <td><?= htmlspecialchars($row['adviser_fname'] . " " . $row['adviser_lname']) ?></td>

                            <!-- Registered -->
                            <td><?= $row['male_registered'] ?></td>
                            <td><?= $row['female_registered'] ?></td>
                            <td><?= $row['total_registered'] ?></td>

                            <!-- Attendance - Daily Average -->
                            <td><?= $male_daily_avg ?></td>
                            <td><?= $female_daily_avg ?></td>
                            <td><?= $total_daily_avg ?></td>

                            <!-- Attendance - Percentage for the Month -->
                            <td><?= $malePercentage ?>%</td>
                            <td><?= $femalePercentage ?>%</td>
                            <td><?= $totalPercentage ?>%</td>

                            <!-- NO LONGER PARTICIPATING -->
                            <!-- (A) Cumulative as of Previous Month -->
                            <td><?= $notActivePrev['male_prev'] ?? 0 ?></td>
                            <td><?= $notActivePrev['female_prev'] ?? 0 ?></td>
                            <td><?= ($notActivePrev['male_prev'] ?? 0) + ($notActivePrev['female_prev'] ?? 0) ?></td>

                            <!-- (B) For the Month -->
                            <td><?= $notActiveMonth['male_month'] ?? 0 ?></td>
                            <td><?= $notActiveMonth['female_month'] ?? 0 ?></td>
                            <td><?= ($notActiveMonth['male_month'] ?? 0) + ($notActiveMonth['female_month'] ?? 0) ?>
                            </td>

                            <!-- (A + B) Cumulative as End of Month -->
                            <td><?= $maleNotActiveTotal ?></td>
                            <td><?= $femaleNotActiveTotal ?></td>
                            <td><?= $totalNotActiveTotal ?></td>

                            <!-- TRANSFERRED OUT -->
                            <!-- (A) Cumulative as of Previous Month -->
                            <td><?= $transOutPrev['male_prev'] ?? 0 ?></td>
                            <td><?= $transOutPrev['female_prev'] ?? 0 ?></td>
                            <td><?= ($transOutPrev['male_prev'] ?? 0) + ($transOutPrev['female_prev'] ?? 0) ?></td>

                            <!-- (B) For the Month -->
                            <td><?= $transOutMonth['male_month'] ?? 0 ?></td>
                            <td><?= $transOutMonth['female_month'] ?? 0 ?></td>
                            <td><?= ($transOutMonth['male_month'] ?? 0) + ($transOutMonth['female_month'] ?? 0) ?></td>

                            <!-- (A + B) Cumulative as End of Month -->
                            <td><?= $maleTransOutTotal ?></td>
                            <td><?= $femaleTransOutTotal ?></td>
                            <td><?= $totalTransOutTotal ?></td>

                            <!-- TRANSFERRED IN -->
                            <!-- (A) Cumulative as of Previous Month -->
                            <td><?= $transInPrev['male_prev'] ?? 0 ?></td>
                            <td><?= $transInPrev['female_prev'] ?? 0 ?></td>
                            <td><?= ($transInPrev['male_prev'] ?? 0) + ($transInPrev['female_prev'] ?? 0) ?></td>

                            <!-- (B) For the Month -->
                            <td><?= $transInMonth['male_month'] ?? 0 ?></td>
                            <td><?= $transInMonth['female_month'] ?? 0 ?></td>
                            <td><?= ($transInMonth['male_month'] ?? 0) + ($transInMonth['female_month'] ?? 0) ?></td>

                            <!-- (A + B) Cumulative as End of Month -->
                            <td><?= $maleTransInTotal ?></td>
                            <td><?= $femaleTransInTotal ?></td>
                            <td><?= $totalTransInTotal ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <thead>
                        <tr>
                            <th colspan="3" class="text-start">Elementary</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    // Registered learners per grade level
                    $stmt = $pdo->prepare("
                        SELECT 
                            e.Grade_level AS grade_level,
                            SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_registered,
                            SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_registered,
                            COUNT(st.student_id) AS total_registered
                        FROM enrolment e
                        INNER JOIN student st ON e.student_id = st.student_id
                        WHERE e.enrolment_status = 'Approved'
                        AND DATE(st.enrolled_date) <= :last_day_of_month
                        AND st.enrolment_status NOT IN ('transferred_out','dropped','not_active')
                        GROUP BY e.Grade_level
                        ORDER BY 
                            CASE 
                                WHEN e.Grade_level LIKE '%Grade 1%' THEN 1
                                WHEN e.Grade_level LIKE '%Grade 2%' THEN 2
                                WHEN e.Grade_level LIKE '%Grade 3%' THEN 3
                                WHEN e.Grade_level LIKE '%Grade 4%' THEN 4
                                WHEN e.Grade_level LIKE '%Grade 5%' THEN 5
                                WHEN e.Grade_level LIKE '%Grade 6%' THEN 6
                                ELSE 999
                            END ASC
                    ");
                    $stmt->execute(['last_day_of_month' => $last_day_of_month]);
                    $gradeLevels = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($gradeLevels)) {
                        echo "<tr><td colspan='42' style='text-align: center;'>No data found</td></tr>";
                    }

                    foreach ($gradeLevels as $row):
                        $gradeLevel = $row['grade_level'];

                        // Clean up grade level display
                        $displayGrade = $gradeLevel;
                        if (strpos($gradeLevel, 'Grade') === false) {
                            $displayGrade = "Grade " . $gradeLevel;
                        }

                        // Attendance per month - Daily Average
                        $stmtAttend = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_present,
                                SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_present,
                                COUNT(*) AS total_present
                            FROM attendance a
                            INNER JOIN student st ON a.student_id = st.student_id
                            INNER JOIN enrolment e ON e.student_id = st.student_id
                            WHERE e.Grade_level = :grade_level
                            AND MONTH(a.morning_attendance) = :month_number
                            AND YEAR(a.morning_attendance) = :current_year
                            AND a.attendance_type = 'Present'
                        ");
                        $stmtAttend->execute([
                            'grade_level' => $gradeLevel,
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $attend = $stmtAttend->fetch(PDO::FETCH_ASSOC);

                        // Calculate daily averages and attendance percentages (using school days count)
                        $male_daily_avg = $school_days_count > 0 ? round((($attend['male_present'] ?? 0) / $school_days_count), 2) : 0;
                        $female_daily_avg = $school_days_count > 0 ? round((($attend['female_present'] ?? 0) / $school_days_count), 2) : 0;
                        $total_daily_avg = $school_days_count > 0 ? round((($attend['total_present'] ?? 0) / $school_days_count), 2) : 0;

                        $malePercentage = 0;
                        $femalePercentage = 0;
                        $totalPercentage = 0;

                        if ($row['male_registered'] > 0) {
                            $malePercentage = round((($male_daily_avg) / $row['male_registered']) * 100, 2);
                        }
                        if ($row['female_registered'] > 0) {
                            $femalePercentage = round((($female_daily_avg) / $row['female_registered']) * 100, 2);
                        }
                        if ($row['total_registered'] > 0) {
                            $totalPercentage = round((($total_daily_avg) / $row['total_registered']) * 100, 2);
                        }

                        // NO LONGER PARTICIPATING - Dropped students
                        // (A) Cumulative as of Previous Month
                        $stmtDroppedPrev = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS male_prev,
                                SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS female_prev
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.Grade_level = :grade_level
                            AND st.enrolment_status = 'dropped'
                        ");
                        $stmtDroppedPrev->execute([
                            'grade_level' => $gradeLevel,
                            'first_day_of_month' => $first_day_of_month
                        ]);
                        $droppedPrev = $stmtDroppedPrev->fetch(PDO::FETCH_ASSOC);

                        // (B) For the Month
                        $stmtDroppedMonth = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS male_month,
                                SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS female_month
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.Grade_level = :grade_level
                            AND st.enrolment_status = 'dropped'
                        ");
                        $stmtDroppedMonth->execute([
                            'grade_level' => $gradeLevel,
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $droppedMonth = $stmtDroppedMonth->fetch(PDO::FETCH_ASSOC);

                        // Calculate (A + B) Cumulative as End of Month
                        $maleDroppedTotal = ($droppedPrev['male_prev'] ?? 0) + ($droppedMonth['male_month'] ?? 0);
                        $femaleDroppedTotal = ($droppedPrev['female_prev'] ?? 0) + ($droppedMonth['female_month'] ?? 0);
                        $totalDroppedTotal = $maleDroppedTotal + $femaleDroppedTotal;

                        // TRANSFERRED OUT
                        // (A) Cumulative as of Previous Month
                        $stmtTransOutPrev = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS male_prev,
                                SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS female_prev
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_out'
                        ");
                        $stmtTransOutPrev->execute([
                            'grade_level' => $gradeLevel,
                            'first_day_of_month' => $first_day_of_month
                        ]);
                        $transOutPrev = $stmtTransOutPrev->fetch(PDO::FETCH_ASSOC);

                        // (B) For the Month
                        $stmtTransOutMonth = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS male_month,
                                SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS female_month
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_out'
                        ");
                        $stmtTransOutMonth->execute([
                            'grade_level' => $gradeLevel,
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $transOutMonth = $stmtTransOutMonth->fetch(PDO::FETCH_ASSOC);

                        // Calculate (A + B) Cumulative as End of Month
                        $maleTransOutTotal = ($transOutPrev['male_prev'] ?? 0) + ($transOutMonth['male_month'] ?? 0);
                        $femaleTransOutTotal = ($transOutPrev['female_prev'] ?? 0) + ($transOutMonth['female_month'] ?? 0);
                        $totalTransOutTotal = $maleTransOutTotal + $femaleTransOutTotal;

                        // TRANSFERRED IN
                        // (A) Cumulative as of Previous Month
                        $stmtTransInPrev = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS male_prev,
                                SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < :first_day_of_month THEN 1 ELSE 0 END) AS female_prev
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_in'
                        ");
                        $stmtTransInPrev->execute([
                            'grade_level' => $gradeLevel,
                            'first_day_of_month' => $first_day_of_month
                        ]);
                        $transInPrev = $stmtTransInPrev->fetch(PDO::FETCH_ASSOC);

                        // (B) For the Month
                        $stmtTransInMonth = $pdo->prepare("
                            SELECT 
                                SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS male_month,
                                SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = :month_number AND YEAR(st.enrolled_date) = :current_year THEN 1 ELSE 0 END) AS female_month
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.Grade_level = :grade_level
                            AND st.enrolment_status = 'transferred_in'
                        ");
                        $stmtTransInMonth->execute([
                            'grade_level' => $gradeLevel,
                            'month_number' => $month_number,
                            'current_year' => $current_year
                        ]);
                        $transInMonth = $stmtTransInMonth->fetch(PDO::FETCH_ASSOC);

                        // Calculate (A + B) Cumulative as End of Month
                        $maleTransInTotal = ($transInPrev['male_prev'] ?? 0) + ($transInMonth['male_month'] ?? 0);
                        $femaleTransInTotal = ($transInPrev['female_prev'] ?? 0) + ($transInMonth['female_month'] ?? 0);
                        $totalTransInTotal = $maleTransInTotal + $femaleTransInTotal;
                    ?>
                        <tr>
                            <!-- GRADE / YEAR LEVEL & EMPTY COLUMNS -->
                            <td colspan="3"><?= $displayGrade ?></td>

                            <!-- REGISTERED LEARNERS (As of End of the Month) -->
                            <td><?= $row['male_registered'] ?></td>
                            <td><?= $row['female_registered'] ?></td>
                            <td><?= $row['total_registered'] ?></td>

                            <!-- ATTENDANCE - Daily Average -->
                            <td><?= $attend['male_present'] ?? 0 ?></td>
                            <td><?= $attend['female_present'] ?? 0 ?></td>
                            <td><?= $attend['total_present'] ?? 0 ?></td>

                            <!-- ATTENDANCE - Percentage for the Month -->
                            <td><?= $malePercentage ?>%</td>
                            <td><?= $femalePercentage ?>%</td>
                            <td><?= $totalPercentage ?>%</td>

                            <!-- NO LONGER PARTICIPATING -->
                            <!-- (A) Cumulative as of Previous Month -->
                            <td><?= $droppedPrev['male_prev'] ?? 0 ?></td>
                            <td><?= $droppedPrev['female_prev'] ?? 0 ?></td>
                            <td><?= ($droppedPrev['male_prev'] ?? 0) + ($droppedPrev['female_prev'] ?? 0) ?></td>

                            <!-- (B) For the Month -->
                            <td><?= $droppedMonth['male_month'] ?? 0 ?></td>
                            <td><?= $droppedMonth['female_month'] ?? 0 ?></td>
                            <td><?= ($droppedMonth['male_month'] ?? 0) + ($droppedMonth['female_month'] ?? 0) ?></td>

                            <!-- (A + B) Cumulative as End of Month -->
                            <td><?= $maleDroppedTotal ?></td>
                            <td><?= $femaleDroppedTotal ?></td>
                            <td><?= $totalDroppedTotal ?></td>

                            <!-- TRANSFERRED OUT -->
                            <!-- (A) Cumulative as of Previous Month -->
                            <td><?= $transOutPrev['male_prev'] ?? 0 ?></td>
                            <td><?= $transOutPrev['female_prev'] ?? 0 ?></td>
                            <td><?= ($transOutPrev['male_prev'] ?? 0) + ($transOutPrev['female_prev'] ?? 0) ?></td>

                            <!-- (B) For the Month -->
                            <td><?= $transOutMonth['male_month'] ?? 0 ?></td>
                            <td><?= $transOutMonth['female_month'] ?? 0 ?></td>
                            <td><?= ($transOutMonth['male_month'] ?? 0) + ($transOutMonth['female_month'] ?? 0) ?></td>

                            <!-- (A + B) Cumulative as End of Month -->
                            <td><?= $maleTransOutTotal ?></td>
                            <td><?= $femaleTransOutTotal ?></td>
                            <td><?= $totalTransOutTotal ?></td>

                            <!-- TRANSFERRED IN -->
                            <!-- (A) Cumulative as of Previous Month -->
                            <td><?= $transInPrev['male_prev'] ?? 0 ?></td>
                            <td><?= $transInPrev['female_prev'] ?? 0 ?></td>
                            <td><?= ($transInPrev['male_prev'] ?? 0) + ($transInPrev['female_prev'] ?? 0) ?></td>

                            <!-- (B) For the Month -->
                            <td><?= $transInMonth['male_month'] ?? 0 ?></td>
                            <td><?= $transInMonth['female_month'] ?? 0 ?></td>
                            <td><?= ($transInMonth['male_month'] ?? 0) + ($transInMonth['female_month'] ?? 0) ?></td>

                            <!-- (A + B) Cumulative as End of Month -->
                            <td><?= $maleTransInTotal ?></td>
                            <td><?= $femaleTransInTotal ?></td>
                            <td><?= $totalTransInTotal ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-12">
            <strong>Mortality Death</strong>
        </div>
        <div class="col-md-5">
            <table>
                <thead>
                    <tr>
                        <th>Previous Month</th>
                        <th><input type="text" name="Previous_Month"
                                value="<?= htmlspecialchars($data_sf_four["Previous_Month"] ?? '') ?>"
                                class="form-control"></th>
                        <th>For the month</th>
                        <th><input type="text" name="For_the_month"
                                value="<?= htmlspecialchars($data_sf_four["For_the_month"] ?? '') ?>"
                                class="form-control"></th>
                        <th>Cumulative as of End of Month</th>
                        <th><input type="text" name="Cumulative_as_of_End_of_Month"
                                value="<?= htmlspecialchars($data_sf_four["Cumulative_as_of_End_of_Month"] ?? '') ?>"
                                class="form-control"></th>
                    </tr>
                </thead>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center mt-3">
            Please select a month to view the data.
        </div>
        <?php endif; ?>

    </form>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the Generate Report button
    const generateReportBtn = document.querySelector('.btn-secondary');

    // Add click event listener
    generateReportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        generatePrintableReport();
    });

    function generatePrintableReport() {
        // Store original content
        const originalContent = document.querySelector('.main-container').innerHTML;

        // Create a print-friendly version
        const printContent = createPrintFriendlyContent();

        // Open print window
        const printWindow = window.open('', '_blank', 'width=1000,height=600');

        // Get logo sources from the page (use absolute URLs)
        const logoLeftEl = document.getElementById('school_logo');
        const logoRightEl = document.getElementById('deped_logo');
        const logoLeftSrc = logoLeftEl ? new URL(logoLeftEl.src, window.location.href).href : '';
        const logoRightSrc = logoRightEl ? new URL(logoRightEl.src, window.location.href).href : '';

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>School Form 4 (SF4) Monthly Learner's Movement and Attendance</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #000;
                    }
                    .print-container {
                        width: 100%;
                    }
                    .report-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 6px;
                    }
                    .report-header img { height: 120px; object-fit: contain; }
                    .form-subtitle {
                        text-align: center;
                        margin-bottom: 10px;
                    }
                    .school-info {
                        margin-bottom: 20px;
                        font-size: 12px;
                    }
                    .school-info table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .school-info td {
                        padding: 2px 5px;
                        vertical-align: top;
                    }
                    table.data-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 10px;
                        margin-top: 10px;
                    }
                    table.data-table th,
                    table.data-table td {
                        border: 1px solid #000;
                        padding: 4px;
                        text-align: center;
                        vertical-align: middle;
                    }
                    table.data-table th {
                        background-color: #f8f9fa !important;
                        font-weight: bold;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .section-header {
                        background-color: #e9ecef !important;
                        font-weight: bold;
                        text-align: left;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .mortality-section {
                        margin-top: 20px;
                        font-size: 12px;
                    }
                    .mortality-section table {
                        width: auto;
                        border-collapse: collapse;
                    }
                    .mortality-section th,
                    .mortality-section td {
                        border: 1px solid #000;
                        padding: 4px 8px;
                        text-align: left;
                    }
                    @media print {
                        body { margin: 0; }
                        .print-container { width: 100%; }
                        table.data-table { font-size: 9px; }
                    }
                    @page {
                        size: landscape;
                        margin: 10mm;
                    }
                </style>
            </head>
            <body>
                <div class="report-header">
                    <div style="flex:1; text-align:left;"><img src="${logoLeftSrc}" alt="School Logo"></div>
                    <div style="flex:2; text-align:center;">
                        <h1 style="margin:0; font-size:18px;">DEPARTMENT OF EDUCATION</h1>
                        <h2 style="margin:4px 0 0 0; font-size:16px;">School Form 4 (SF4) Monthly Learner's Movement and Attendance</h2>
                    </div>
                    <div style="flex:1; text-align:right;"><img src="${logoRightSrc}" alt="DepEd Logo"></div>
                </div>
                ${printContent}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);

        printWindow.document.close();
    }

    function createPrintFriendlyContent() {
        // Clone the main container
        const mainContainer = document.querySelector('.main-container').cloneNode(true);

        // Remove form elements and buttons
        const form = mainContainer.querySelector('form');
        if (form) {
            form.removeAttribute('id');
            form.removeAttribute('class');
        }

        // Remove input fields and replace with display values
        const inputs = mainContainer.querySelectorAll('input');
        inputs.forEach(input => {
            const span = document.createElement('span');
            span.textContent = input.value;
            span.style.padding = '0 5px';
            input.parentNode.replaceChild(span, input);
        });

        // Remove the save and generate buttons
        const buttons = mainContainer.querySelectorAll('button');
        buttons.forEach(button => button.remove());

        // Get school information for the header
        const schoolInfo = `
            <div class="school-info">
                <table>
                    <tr>
                        <td style="width: 30%;"><strong>School ID:</strong> ${document.querySelector('input[name="school_id"]')?.value || ''}</td>
                        <td style="width: 30%;"><strong>Region:</strong> ${document.querySelector('input[name="region"]')?.value || ''}</td>
                        <td style="width: 40%;"><strong>Division:</strong> ${document.querySelector('input[name="Division"]')?.value || ''}</td>
                    </tr>
                    <tr>
                        <td><strong>School Name:</strong> ${document.querySelector('input[name="school_name"]')?.value || ''}</td>
                        <td><strong>School Year:</strong> ${document.querySelector('input[name="school_year_name"]')?.value || ''}</td>
                        <td><strong>Report Month:</strong> ${formatDate(document.querySelector('input[name="report_for_the_month_of"]')?.value || '')}</td>
                    </tr>
                </table>
            </div>
        `;

        return `
            <div class="print-container">
                <div class="form-title">
                    <h2>School Form 4 (SF4) Monthly Learner's Movement and Attendance</h2>
                    <p>(this replaces Form 3 & STS Form 4-Absenteeism and Dropout Profile)</p>
                </div>
                ${schoolInfo}
                ${mainContainer.querySelector('.scroll-container').outerHTML}
                <div class="mortality-section">
                    <strong>Mortality Death</strong>
                    <table style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Previous Month</th>
                                <th>${document.querySelector('input[name="Previous_Month"]')?.value || ''}</th>
                                <th>For the month</th>
                                <th>${document.querySelector('input[name="For_the_month"]')?.value || ''}</th>
                                <th>Cumulative as of End of Month</th>
                                <th>${document.querySelector('input[name="Cumulative_as_of_End_of_Month"]')?.value || ''}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div style="margin-top: 20px; font-size: 11px; text-align: center;">
                    <p>Generated on: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                </div>
            </div>
        `;
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long'
        });
    }
});
</script>