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
     <form class="main-container" id="sfFour-form">
         <div class="form-title text-center w-100">
             <h2>School Form 4 (SF4) Monthly Learner's Movement and Attendance</h2>
             <p class="text-muted">(this replaces Form 3 & STS Form 4-Absenteeism and Dropout Profile)</p>
         </div>

         <div class="form-section">
             <div class="row mb-2">
                 <div class="col-md-4">
                     <div class="d-flex align-items-center mb-2">
                         <label class="me-2 col-4">School ID</label>
                         <input type="text" name="school_id" class="me-2 flex-grow-1">
                         <input type="text" name="region" class="flex-grow-1" placeholder="Region">
                     </div>
                 </div>
                 <div class="col-md-4">
                     <div class="d-flex align-items-center mb-2">
                         <label class="me-2 col-4">Division</label>
                         <input type="text" name="Division" class="flex-grow-1">
                     </div>
                 </div>
                 <div class="col-md-4">
                     <div class="d-flex align-items-center mb-2">
                         <label class="me-2 col-4">District</label>
                         <input type="text" name="district" class="flex-grow-1">
                     </div>
                 </div>
             </div>

             <div class="row mb-3">
                 <div class="col-md-4">
                     <div class="d-flex align-items-center mb-2">
                         <label class="me-2 col-4">School Name</label>
                         <input type="text" name="school_name" class="flex-grow-1">
                     </div>
                 </div>
                 <div class="col-md-4">
                     <div class="d-flex align-items-center mb-2">
                         <label class="me-2 col-4">School Year</label>
                            <?php
                                $stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active'");
                                $stmt->execute();
                                $sy = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                           <input readonly class="form-control" type="text" value="<?= $sy["school_year_name"] ?>">
                     </div>
                 </div>
                 <div class="col-md-4">
                     <div class="d-flex align-items-center mb-2">
                         <label class="me-2 col-4">Report for the month of</label>
                         <input type="text" name="report_for_the_month_of" class="flex-grow-1">
                     </div>
                 </div>
             </div>
         </div>

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
                                // Registered learners per adviser
                                $stmt = $pdo->prepare("
                                    SELECT 
                                        u.user_id AS adviser_id,
                                        u.firstname AS adviser_fname,
                                        u.lastname AS adviser_lname,
                                        s.section_name AS sec_name,
                                        s.section_grade_level AS grade_level,
                                        SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_registered,
                                        SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_registered,
                                        COUNT(st.student_id) AS total_registered
                                    FROM enrolment e
                                    INNER JOIN sections s ON s.section_name = e.section_name
                                    INNER JOIN users u ON e.adviser_id = u.user_id
                                    INNER JOIN student st ON e.student_id = st.student_id
                                    WHERE e.enrolment_status = 'Approved'
                                    GROUP BY s.section_name, s.section_grade_level, u.user_id, u.firstname, u.lastname
                                ");
                                $stmt->execute();
                                $advisers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach($advisers as $row):

                                    $adviserId = $row['adviser_id'];

                                    // Attendance per month
                                    $stmtAttend = $pdo->prepare("
                                        SELECT 
                                            SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_present,
                                            SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_present,
                                            COUNT(*) AS total_present
                                        FROM attendance a
                                        INNER JOIN student st ON a.student_id = st.student_id
                                        WHERE a.adviser_id = :adviser_id
                                        AND MONTH(a.morning_attendance) = MONTH(CURRENT_DATE())
                                        AND a.attendance_type = 'Present'
                                    ");
                                    $stmtAttend->execute(['adviser_id' => $adviserId]);
                                    $attend = $stmtAttend->fetch(PDO::FETCH_ASSOC);

                                    // Dropped students
                                    $stmtDropped = $pdo->prepare("
                                        SELECT 
                                            SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_dropped,
                                            SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_dropped
                                        FROM enrolment e
                                        INNER JOIN student st ON e.student_id = st.student_id
                                        WHERE e.adviser_id = :adviser_id
                                        AND st.enrolment_status='dropped'
                                    ");
                                    $stmtDropped->execute(['adviser_id' => $adviserId]);
                                    $dropped = $stmtDropped->fetch(PDO::FETCH_ASSOC);

                                    // Transferred Out
                                    $stmtTransOut = $pdo->prepare("
                                        SELECT 
                                            SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) < MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_prev,
                                            SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) < MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_prev,
                                            SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_this_month,
                                            SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_this_month
                                        FROM enrolment e
                                        INNER JOIN student st ON e.student_id = st.student_id
                                        WHERE e.adviser_id = :adviser_id
                                        AND st.enrolment_status='transferred_out'
                                    ");
                                    $stmtTransOut->execute(['adviser_id' => $adviserId]);
                                    $transOut = $stmtTransOut->fetch(PDO::FETCH_ASSOC);

                                    // Transferred In
                                    $stmtTransIn = $pdo->prepare("
                                        SELECT 
                                            SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) < MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_prev,
                                            SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) < MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_prev,
                                            SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_this_month,
                                            SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_this_month
                                        FROM enrolment e
                                        INNER JOIN student st ON e.student_id = st.student_id
                                        WHERE e.adviser_id = :adviser_id AND st.enrolment_status = 'transferred_in'
                                    ");
                                    $stmtTransIn->execute(['adviser_id' => $adviserId]);
                                    $transIn = $stmtTransIn->fetch(PDO::FETCH_ASSOC);

                                    $stmtTransOut->execute(['adviser_id' => $adviserId]);
                                    $transOut = $stmtTransOut->fetch(PDO::FETCH_ASSOC);

                                    // Transferred In
                                    $stmtNotActive = $pdo->prepare("
                                        SELECT 
                                            SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) < MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_prev,
                                            SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) < MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_prev,
                                            SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_this_month,
                                            SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_this_month
                                        FROM enrolment e
                                        INNER JOIN student st ON e.student_id = st.student_id
                                        WHERE e.adviser_id = :adviser_id AND st.enrolment_status = 'not_active'
                                    ");
                                    $stmtNotActive->execute(['adviser_id' => $adviserId]);
                                    $notActive = $stmtNotActive->fetch(PDO::FETCH_ASSOC);
                            ?>
                         <tr>
                             <td><?= htmlspecialchars($row['grade_level']) ?></td>
                             <td><?= htmlspecialchars($row['sec_name']) ?></td>
                             <td><?= htmlspecialchars($row['adviser_fname'] . " " . $row['adviser_lname']) ?></td>

                             <!-- Registered -->
                             <td><?= $row['male_registered'] ?></td>
                             <td><?= $row['female_registered'] ?></td>
                             <td><?= $row['total_registered'] ?></td>

                             <!-- Attendance -->
                             <td><?= $attend['male_present'] ?? 0 ?></td>
                             <td><?= $attend['female_present'] ?? 0 ?></td>
                             <td><?= $attend['total_present'] ?? 0 ?></td>
                             <td><?= $attend['male_present'] ?? 0 ?></td>
                             <td><?= $attend['female_present'] ?? 0 ?></td>
                             <td><?= $attend['total_present'] ?? 0 ?></td>

                             <!-- No Longer Participating (blank) -->
                             <td><?= $notActive['male_prev'] ?? 0 ?></td>
                             <td><?= $notActive['female_prev'] ?? 0 ?></td>
                             <td><?= ($notActive['male_prev'] ?? 0) + ($notActive['female_prev'] ?? 0) ?></td>
                             <td><?= $notActive['male_this_month'] ?? 0 ?></td>
                             <td><?= $notActive['female_this_month'] ?? 0 ?></td>
                             <td><?= ($notActive['male_this_month'] ?? 0) + ($notActive['female_this_month'] ?? 0) ?>
                             </td>
                             <td><?= ($notActive['male_prev'] ?? 0) + ($notActive['male_this_month'] ?? 0) ?></td>
                             <td><?= ($notActive['female_prev'] ?? 0) + ($notActive['female_this_month'] ?? 0) ?></td>
                             <td><?= ($notActive['male_prev'] ?? 0) + ($notActive['male_this_month'] ?? 0) + ($notActive['female_prev'] ?? 0) + ($notActive['female_this_month'] ?? 0) ?>
                             </td>

                             <!-- Transferred Out -->
                             <td><?= $transOut['male_prev'] ?? 0 ?></td>
                             <td><?= $transOut['female_prev'] ?? 0 ?></td>
                             <td><?= ($transOut['male_prev'] ?? 0) + ($transOut['female_prev'] ?? 0) ?></td>
                             <td><?= $transOut['male_this_month'] ?? 0 ?></td>
                             <td><?= $transOut['female_this_month'] ?? 0 ?></td>
                             <td><?= ($transOut['male_this_month'] ?? 0) + ($transOut['female_this_month'] ?? 0) ?></td>
                             <td><?= ($transOut['male_prev'] ?? 0) + ($transOut['male_this_month'] ?? 0) ?></td>
                             <td><?= ($transOut['female_prev'] ?? 0) + ($transOut['female_this_month'] ?? 0) ?></td>
                             <td><?= ($transOut['male_prev'] ?? 0) + ($transOut['male_this_month'] ?? 0) + ($transOut['female_prev'] ?? 0) + ($transOut['female_this_month'] ?? 0) ?>
                             </td>

                             <!-- Transferred In -->
                             <td><?= $transIn['male_prev'] ?? 0 ?></td>
                             <td><?= $transIn['female_prev'] ?? 0 ?></td>
                             <td><?= ($transIn['male_prev'] ?? 0) + ($transIn['female_prev'] ?? 0) ?></td>
                             <td><?= $transIn['male_this_month'] ?? 0 ?></td>
                             <td><?= $transIn['female_this_month'] ?? 0 ?></td>
                             <td><?= ($transIn['male_this_month'] ?? 0) + ($transIn['female_this_month'] ?? 0) ?></td>
                             <td><?= ($transIn['male_prev'] ?? 0) + ($transIn['male_this_month'] ?? 0) ?></td>
                             <td><?= ($transIn['female_prev'] ?? 0) + ($transIn['female_this_month'] ?? 0) ?></td>
                             <td><?= ($transIn['male_prev'] ?? 0) + ($transIn['male_this_month'] ?? 0) + ($transIn['female_prev'] ?? 0) + ($transIn['female_this_month'] ?? 0) ?>
                             </td>
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
                        // Registered learners per grade level - Using the correct column from enrolment table
                        $stmt = $pdo->prepare("
                            SELECT 
                                e.Grade_level AS grade_level,
                                SUM(CASE WHEN st.sex='MALE' THEN 1 ELSE 0 END) AS male_registered,
                                SUM(CASE WHEN st.sex='FEMALE' THEN 1 ELSE 0 END) AS female_registered,
                                COUNT(st.student_id) AS total_registered
                            FROM enrolment e
                            INNER JOIN student st ON e.student_id = st.student_id
                            WHERE e.enrolment_status = 'Approved'
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
                        $stmt->execute();
                        $gradeLevels = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Debug: Check what grade levels are returned
                        echo "<!-- Debug: Found " . count($gradeLevels) . " grade levels -->";
                        foreach ($gradeLevels as $debugRow) {
                            echo "<!-- Debug: Grade Level: " . $debugRow['grade_level'] . " -->";
                        }

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
                                AND MONTH(a.morning_attendance) = MONTH(CURRENT_DATE())
                                AND YEAR(a.morning_attendance) = YEAR(CURRENT_DATE())
                                AND a.attendance_type = 'Present'
                            ");
                            $stmtAttend->execute(['grade_level' => $gradeLevel]);
                            $attend = $stmtAttend->fetch(PDO::FETCH_ASSOC);

                            // Calculate attendance percentage
                            $malePercentage = 0;
                            $femalePercentage = 0;
                            $totalPercentage = 0;
                            
                            if ($row['male_registered'] > 0) {
                                $malePercentage = round((($attend['male_present'] ?? 0) / $row['male_registered']) * 100, 2);
                            }
                            if ($row['female_registered'] > 0) {
                                $femalePercentage = round((($attend['female_present'] ?? 0) / $row['female_registered']) * 100, 2);
                            }
                            if ($row['total_registered'] > 0) {
                                $totalPercentage = round((($attend['total_present'] ?? 0) / $row['total_registered']) * 100, 2);
                            }

                            // NO LONGER PARTICIPATING - Dropped students
                            // (A) Cumulative as of Previous Month
                            $stmtDroppedPrev = $pdo->prepare("
                                SELECT 
                                    SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS male_prev,
                                    SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS female_prev
                                FROM enrolment e
                                INNER JOIN student st ON e.student_id = st.student_id
                                WHERE e.Grade_level = :grade_level
                                AND e.enrolment_status = 'dropped'
                            ");
                            $stmtDroppedPrev->execute(['grade_level' => $gradeLevel]);
                            $droppedPrev = $stmtDroppedPrev->fetch(PDO::FETCH_ASSOC);

                            // (B) For the Month
                            $stmtDroppedMonth = $pdo->prepare("
                                SELECT 
                                    SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) AND YEAR(st.enrolled_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_month,
                                    SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) AND YEAR(st.enrolled_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_month
                                FROM enrolment e
                                INNER JOIN student st ON e.student_id = st.student_id
                                WHERE e.Grade_level = :grade_level
                                AND e.enrolment_status = 'dropped'
                            ");
                            $stmtDroppedMonth->execute(['grade_level' => $gradeLevel]);
                            $droppedMonth = $stmtDroppedMonth->fetch(PDO::FETCH_ASSOC);

                            // Calculate (A + B) Cumulative as End of Month
                            $maleDroppedTotal = ($droppedPrev['male_prev'] ?? 0) + ($droppedMonth['male_month'] ?? 0);
                            $femaleDroppedTotal = ($droppedPrev['female_prev'] ?? 0) + ($droppedMonth['female_month'] ?? 0);
                            $totalDroppedTotal = $maleDroppedTotal + $femaleDroppedTotal;

                            // TRANSFERRED OUT
                            // (A) Cumulative as of Previous Month
                            $stmtTransOutPrev = $pdo->prepare("
                                SELECT 
                                    SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS male_prev,
                                    SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS female_prev
                                FROM enrolment e
                                INNER JOIN student st ON e.student_id = st.student_id
                                WHERE e.Grade_level = :grade_level
                                AND e.enrolment_status = 'transferred_out'
                            ");
                            $stmtTransOutPrev->execute(['grade_level' => $gradeLevel]);
                            $transOutPrev = $stmtTransOutPrev->fetch(PDO::FETCH_ASSOC);

                            // (B) For the Month
                            $stmtTransOutMonth = $pdo->prepare("
                                SELECT 
                                    SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) AND YEAR(st.enrolled_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_month,
                                    SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) AND YEAR(st.enrolled_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_month
                                FROM enrolment e
                                INNER JOIN student st ON e.student_id = st.student_id
                                WHERE e.Grade_level = :grade_level
                                AND e.enrolment_status = 'transferred_out'
                            ");
                            $stmtTransOutMonth->execute(['grade_level' => $gradeLevel]);
                            $transOutMonth = $stmtTransOutMonth->fetch(PDO::FETCH_ASSOC);

                            // Calculate (A + B) Cumulative as End of Month
                            $maleTransOutTotal = ($transOutPrev['male_prev'] ?? 0) + ($transOutMonth['male_month'] ?? 0);
                            $femaleTransOutTotal = ($transOutPrev['female_prev'] ?? 0) + ($transOutMonth['female_month'] ?? 0);
                            $totalTransOutTotal = $maleTransOutTotal + $femaleTransOutTotal;

                            // TRANSFERRED IN
                            // (A) Cumulative as of Previous Month
                            $stmtTransInPrev = $pdo->prepare("
                                SELECT 
                                    SUM(CASE WHEN st.sex='MALE' AND st.enrolled_date < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS male_prev,
                                    SUM(CASE WHEN st.sex='FEMALE' AND st.enrolled_date < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS female_prev
                                FROM enrolment e
                                INNER JOIN student st ON e.student_id = st.student_id
                                WHERE e.Grade_level = :grade_level
                                AND e.enrolment_status = 'transferred_in'
                            ");
                            $stmtTransInPrev->execute(['grade_level' => $gradeLevel]);
                            $transInPrev = $stmtTransInPrev->fetch(PDO::FETCH_ASSOC);

                            // (B) For the Month
                            $stmtTransInMonth = $pdo->prepare("
                                SELECT 
                                    SUM(CASE WHEN st.sex='MALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) AND YEAR(st.enrolled_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) AS male_month,
                                    SUM(CASE WHEN st.sex='FEMALE' AND MONTH(st.enrolled_date) = MONTH(CURRENT_DATE()) AND YEAR(st.enrolled_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) AS female_month
                                FROM enrolment e
                                INNER JOIN student st ON e.student_id = st.student_id
                                WHERE e.Grade_level = :grade_level
                                AND e.enrolment_status = 'transferred_in'
                            ");
                            $stmtTransInMonth->execute(['grade_level' => $gradeLevel]);
                            $transInMonth = $stmtTransInMonth->fetch(PDO::FETCH_ASSOC);

                            // Calculate (A + B) Cumulative as End of Month
                            $maleTransInTotal = ($transInPrev['male_prev'] ?? 0) + ($transInMonth['male_month'] ?? 0);
                            $femaleTransInTotal = ($transInPrev['female_prev'] ?? 0) + ($transInMonth['female_month'] ?? 0);
                            $totalTransInTotal = $maleTransInTotal + $femaleTransInTotal;

                            // ================== OUTPUT ROW ==================
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
                        <?php
                        endforeach;
                        ?>
                    </tbody>
                 </table>
             </div>
         </div>
                <div class="col-md-12">
                    <strong>Mortality Deatch</strong>
                </div>
                <div class="col-md-5">
                    <table>
                        <thead>
                            <tr>
                                <th>Previous Month</th>
                                <th><input type="text" name="Previous_Month" class="form-control"></th>
                                <th>For the month</th>
                                <th><input type="text" name="For_the_month" class="form-control"></th>
                                <th>Cumulative as of End of Month</th>
                                <th><input type="text" name="Cumulative_as_of_End_of_Month" class="form-control"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
         <div class="mt-3 text-start">
             <button class="btn btn-primary">Save Data</button>
             <button class="btn btn-secondary">Generate Report</button>
         </div>
    </form>

 </main>