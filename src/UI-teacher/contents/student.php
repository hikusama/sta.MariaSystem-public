<?php
    $query = "SELECT classes.*, users.* FROM classes
    INNER JOIN users ON classes.adviser_id = users.user_id
    WHERE users.user_id = '$user_id'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $query = "SELECT * FROM school_year WHERE school_year_status = 'Active'";
    // $stmt = $pdo->prepare($query);
    // $stmt->execute();
    // $schoolYear = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-clock me-2"></i>Student Attendance</h4>
    </div>
</div>

<div class="row g-2  justify-content-between">
    <div class="row mb-3  justify-content-between">
        <div class="col-md-4">
            <input type="text" id="searchInput" name="search" class="form-control"
                placeholder="Search by name, role, status, or date...">
        </div>
        <div class="col-md-4">
            <select name="adviser_id" class="form-select" required>
                <option value="">Select Section</option>
                <?php foreach($classes as $class) : ?>
                <option value="<?= $class["user_id"] ?>">
                    <?= htmlspecialchars($class["section_name"]) ?> -
                    Adviser:
                    <?= htmlspecialchars($class["lastname"]) . " " . htmlspecialchars($class["firstname"]) ?>
                </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="col-md-4">
            <select id="categoryFilter" name="gradeLevelCategory" class="form-select">
                <option value="">Grade Level</option>
                <option value="Grade 1">Grade 1</option>
                <option value="Grade 2">Grade 2</option>
                <option value="Grade 3">Grade 3</option>
                <option value="Grade 4">Grade 4</option>
                <option value="Grade 5">Grade 5</option>
                <option value="Grade 6">Grade 6</option>
            </select>
        </div>
    </div>
    <!-- Accounts Displays -->
    <div class="table-container-wrapper">
        <?php
            $stmt = $pdo->prepare("SELECT * FROM enrolment
            INNER JOIN student ON enrolment.student_id = student.student_id
            WHERE adviser_id = '$user_id'
            ORDER BY fname ASC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
        ?>

        <!-- Fixed Header -->
        <div class="table-header">
            <table class="table table-bordered table-sm text-center mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Name</th>
                        <th width="15%">Grade Level</th>
                        <th width="15%">Section</th>
                        <th width="15%">Enrolment Status</th>
                        <th width="15%">Enrolled at</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="table-body-scroll">
            <table class="table table-bordered table-sm text-center mb-0">
                <tbody>
                    <?php foreach($users as $user) : ?>
                    <tr>
                        <td width="5%"><?= $count++ ?></td>
                        <td width="20%">
                            <?= htmlspecialchars($user["lname"]) . " " . 
                            htmlspecialchars($user["fname"]) . " " .  (!empty($user["mname"]) ? htmlspecialchars(substr($user["mname"], 0, 1)) . ". " : "") ?>
                        </td>
                        <td width="15%"><?= htmlspecialchars($user["gradeLevel"]) ?></td>
                        <td width="15%"><?= htmlspecialchars($user["section_name"]) ?></td>
                        <td width="15%">
                            <span
                                class="badge bg-<?= ($user["enrolment_status"] == 'active') ? 'success' : 'secondary' ?>">
                                <?= ($user["enrolment_status"] == 'active') ? 'Enrolled' : 'Inactive' ?>
                            </span>
                        </td>

                        <td width="15%"><?= htmlspecialchars($user["enrolled_date"]) ?></td>
                        <td width="15%">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="index.php?page=contents/profile&student_id=<?= $user["student_id"] ?>"><button class="btn btn-info m-0">Student Profile</button></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- <div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="AddNewAccountLabel">Approve Student Enrolment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                // Assume you have already fetched all subjects from DB:
                $subjects = $pdo->query("SELECT * FROM Subjects")->fetchAll(PDO::FETCH_ASSOC);
                ?>
                    <form class="row g-3" id="enrolment-form" method="post">
                        <input type="hidden" name="student_id" id="student_id" value="">
                        <div class="col-md-6">
                            <label class="form-label">Class Section <span class="text-danger">*</span></label>
                            <select name="adviser_id" class="form-select" required>
                                <option value="">Select Section</option>
                                <?php foreach($classes as $class) : ?>
                                <option value="<?= $class["user_id"] ?>">
                                    <?= htmlspecialchars($class["section_name"]) ?> -
                                    Adviser:
                                    <?= htmlspecialchars($class["lastname"]) . " " . htmlspecialchars($class["firstname"]) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">School Year <span class="text-danger">*</span></label>
                            <select name="schoolyear_id" class="form-select" required>
                                <option value="">Select School Year</option>
                                <?php foreach($schoolYear as $sy) : ?>
                                <option value="<?= $sy["school_year_id"] ?>">
                                    <?= htmlspecialchars($sy["school_year_name"]) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select name="grade_level" class="form-select" id="gradeLevelSelect" required>
                                <option value="">Select Grade Level</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Number of Subjects <span class="text-danger">*</span></label>
                            <select name="subjectCounts" class="form-select" id="subjectCountsSelect" required>
                                <option value="">Select Number of Subjects</option>
                                <?php for($i=1; $i<=8; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> Subject<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">Select Subjects</h6>
                                </div>
                                <div class="card-body">
                                    <div id="subjectSelectContainer" class="row"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5">Approve Enrolment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> -->
</div>

<script>
// Pass subjects from PHP to JS
const allSubjects = <?= json_encode($subjects) ?>;

document.addEventListener('DOMContentLoaded', () => {
    const gradeLevelSelect = document.getElementById('gradeLevelSelect');
    const subjectCountsSelect = document.getElementById('subjectCountsSelect');
    const container = document.getElementById('subjectSelectContainer');

    function generateSubjectSelects() {
        const gradeLevel = gradeLevelSelect.value;
        const count = parseInt(subjectCountsSelect.value);

        // Clear old selects
        container.innerHTML = '';

        // Only continue if both are selected
        if (!gradeLevel || isNaN(count) || count < 1) return;

        // Filter subjects by grade level
        const filteredSubjects = allSubjects.filter(s => s.grade_level === gradeLevel);

        if (filteredSubjects.length === 0) {
            container.innerHTML =
                '<div class="col-12"><div class="alert alert-warning">No subjects available for this grade level.</div></div>';
            return;
        }

        const row = document.createElement('div');
        row.classList.add('row');
        container.appendChild(row);

        for (let i = 0; i < count; i++) {
            const col = document.createElement('div');
            col.classList.add('col-md-6', 'mb-3');

            const label = document.createElement('label');
            label.classList.add('form-label', 'small');
            label.textContent = `Subject ${i + 1}`;

            const select = document.createElement('select');
            select.name = 'subjects[]';
            select.classList.add('form-select', 'form-select-sm');
            select.required = true;

            // Default option
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Select Subject';
            select.appendChild(defaultOpt);

            // Add filtered subjects as options
            filteredSubjects.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.subject_id;
                opt.textContent = `${sub.subject_code} - ${sub.subject_name}`;
                select.appendChild(opt);
            });

            col.appendChild(label);
            col.appendChild(select);
            row.appendChild(col);
        }
    }

    // Regenerate whenever grade level or count changes
    gradeLevelSelect.addEventListener('change', generateSubjectSelects);
    subjectCountsSelect.addEventListener('change', generateSubjectSelects);
});
</script>