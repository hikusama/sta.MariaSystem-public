<?php
// Get selected grade level from filter
$selectedGrade = $_POST['gradeLevelCategory'] ?? '';

// Build the SQL query with optional grade level filter
$sql = "SELECT student.*, parents_info.*, stuEnrolmentInfo.*, enrolment.Grade_level 
        FROM student
        INNER JOIN parents_info ON student.student_id = parents_info.student_id
        INNER JOIN stuEnrolmentInfo ON student.student_id = stuEnrolmentInfo.student_id
        INNER JOIN enrolment ON student.student_id = enrolment.student_id
        WHERE student.enrolment_status = 'active'";

// Add grade level filter if selected
if (!empty($selectedGrade)) {
    $sql .= " AND enrolment.Grade_level = :grade_level";
}

$stmtStudents = $pdo->prepare($sql);

// Bind parameter if grade level is selected
if (!empty($selectedGrade)) {
    $stmtStudents->bindParam(':grade_level', $selectedGrade);
}

$stmtStudents->execute();
$studentsEnrolled = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);
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
<main>
    <?php
        $stmt = $pdo->prepare("SELECT * FROM sf_add_data WHERE sf_type = 'sf_8'");
        $stmt->execute();
        $data_sf_eight = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtAdviser_data = $pdo->prepare("SELECT * FROM users
        INNER JOIN classes ON users.user_id = classes.adviser_id
        WHERE classes.adviser_id = '$user_id'");
        $stmtAdviser_data->execute();
        $adviser_data = $stmtAdviser_data->fetch(PDO::FETCH_ASSOC);
    ?>
    <form class="main-container" id="sfEight-form">
        <div class="mt-3 text-start">
            <button type="submit" class="btn btn-danger">Save Data</button>
            <button type="button" class="btn btn-secondary" onclick="generateReport()">Generate Report</button>
        </div>
        <input type="hidden" name="id" value="<?= htmlspecialchars($data_sf_eight["sf_add_data_id"] ?? '') ?>">
        <div class="col-md-12 d-flex justify-content-between">
            <div class="col-md-3 d-flex align-items-center justify-content-start">
                <img src="../../assets/image/logo.png" alt="No Image" style="width: auto; height: 150px;">
            </div>
            <div class="col-md-6">
                <div class="form-title text-center w-100">
                    <h2>Department of Education <br> School Form 2 Daily Attendance Report of Learners (SF2)
                    </h2>
                    <p class="text-muted">(this replaces Form 1, Master List & STS Form 2-Family Background and Profile)
                    </p>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-center justify-content-end ">
                <img src="../../assets/image/deped.png" alt="No Image"
                    style="width: 200px; height: auto; transform: translateX(-30px);">
            </div>
        </div>

        <div class="form-section">
            <div class="row mb-2">
                <div class="d-flex align-items-center mb-2">
                    <div class="col-md-3 d-flex">
                        <label class="me-2 col-4">School ID</label>
                        <input type="text" name="school_id"
                            value="<?= htmlspecialchars($data_sf_eight["school_id"] ?? '') ?>" class="me-2 flex-grow-1">
                    </div>
                    <div class="col-md-3 d-flex">
                        <label class="me-2 col-4">School Year</label>
                        <?php
                            $stmt = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active'");
                            $stmt->execute();
                            $sy = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <input readonly class="form-control" type="text" name="school_year_name"
                            value="<?= $sy["school_year_name"] ?>">
                    </div>
                    <div class="col-md-5 ms-5 d-flex">
                        <label class="me-2 col-4">Report for the month of</label>
                        <input type="text" name="For_the_month"
                            value="<?= htmlspecialchars($data_sf_eight["For_the_month"] ?? '') ?>" class="me-2 flex-grow-1">
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-start mb-2">
                    <div class="col-md-6 me-1">
                        <div class="d-flex align-items-center justify-content-start mb-2">
                            <label class="me-2 col-2">Name of school</label>
                            <input type="text" name="school_name"
                                value="<?= htmlspecialchars($data_sf_eight["school_name"] ?? '') ?>" class="flex-grow-1 form-control">
                        </div>
                    </div>
                    <div class="col-md-3 ">
                        <div class="d-flex align-items-center justify-content-end mb-2">
                            <label class="ms-5 col-4">Grade Level</label>
                            <input type="text" name="Division"
                                value="<?= htmlspecialchars($data_sf_eight["Division"] ?? '') ?>" class="flex-grow-1">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center mb-2">
                            <label class="me-2 col-2">Section</label>
                            <input type="text" name="sections"
                                value="" class="flex-grow-1">
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 600px; overflow: auto;">
            <table class="table table-bordered table-sm table-hover" style="text-align:center; min-width: 1400px;">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                    <!-- ROW 1 -->
                    <tr>
                        <th rowspan="3" colspan="2" class="align-middle" style="min-width: 20%; background-color: #e7f3ff;">
                            <small>LEARNER'S NAME</small><br>
                            <small><em>(Last Name, First Name, Middle Name)</em></small>
                        </th>

                        <th colspan="25" style="min-width: 50%;">
                            <small>(1st row for date)</small>
                        </th>

                        <th rowspan="2" colspan="2" class="align-middle" style="min-width: 15%; background-color: #f8f9fa;">
                            <small>Total for the Month</small>
                        </th>

                        <th rowspan="3" class="align-middle" style="min-width: 15%; background-color: #fff3cd;">
                            <small>REMARKS</small><br>
                            <small><em>If DROPPED OUT, state reason—refer to legend #2.</em></small><br>
                            <small><em>If TRANSFERRED IN/OUT, write the name of the school.</em></small>
                        </th>
                    </tr>

                    <!-- ROW 2 -->
                    <tr >
                        <!-- 25 date cells -->
                        <?php for ($i = 1; $i <= 25; $i++): ?>
                        <th style="min-width: 40px; max-width: 50px;">
                            <small><?= $i ?></small>
                        </th>
                        <?php endfor; ?>
                    </tr>

                    <!-- ROW 3 (Days: M T W TH F × 5 weeks) -->
                    <tr style="width: 5% !important;">
                        <?php 
                        $days = ['M', 'T', 'W', 'TH', 'F'];
                        for ($week = 1; $week <= 5; $week++):
                            foreach ($days as $day):
                        ?>
                        <th style="min-width: 40px; max-width: 50px;">
                            <small><strong><?= $day ?></strong></small>
                        </th>
                        <?php 
                            endforeach;
                        endfor; 
                        ?>
                        <th style="min-width: 50px; background-color: #f8f9fa;">
                            <small>ABSENT</small>
                        </th>
                        <th style="min-width: 50px; background-color: #f8f9fa;">
                            <small>TARDY</small>
                        </th>
                    </tr>
                </thead>
                
                <tbody style="font-size: 0.85rem;">
                    <!-- Add your data rows here with PHP loop -->
                </tbody>
            </table>
        </div>

        </div>
    </form>
</main>