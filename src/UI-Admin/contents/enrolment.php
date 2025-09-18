<?php
    $query = "SELECT classes.*, users.* FROM classes
    INNER JOIN users ON classes.adviser_id = users.user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-folder me-2"></i></i>Enrolment Management</h4>
    </div>
</div>

<!-- Search and Filters -->

<div class="row g-2  justify-content-between">
    <div class="row mb-3  justify-content-between">
        <div class="col-md-4">
            <input type="text" id="searchInput" name="search" class="form-control"
                placeholder="Search by name, role, status, or date...">
        </div>
        <div class="col-md-4">
            <select id="categoryFilter" name="statusCategory" class="form-select">
                <option value="">Enrolment Status</option>
                <option value="pending">Pending</option>
                <option value="active">Enrolled</option>
                <option value="transferred">Transferred</option>
                <option value="dropped">Dropped</option>
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
            $stmt = $pdo->prepare("SELECT * FROM student ORDER BY fname ASC");
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
                        <th width="15%">Enrolment Status</th>
                        <th width="20%">Enrolled at</th>
                        <th width="25%">Action</th>
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
                        <td width="15%">
                            <span
                                class="badge bg-<?= ($user["enrolment_status"] == 'Active') ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($user["enrolment_status"] ?? 'Inactive') ?>
                            </span>
                        </td>
                        <td width="20%"><?= htmlspecialchars($user["enrolled_date"]) ?></td>
                        <td width="25%">
                            <div class="d-flex gap-1 justify-content-center">
                                <a
                                    href="index.php?page=contents/form&student_id=<?= htmlspecialchars($user["student_id"]) ?>"><button
                                        class="btn btn-sm m-0 btn-info">Enrolment Form</button></a>
                                <button type="button" class="btn btn-success btn-sm " data-bs-toggle="modal"
                                    data-bs-target="#AddNewAccount">Approve</button>
                                <button type="button" class="btn btn-danger btn-sm">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="AddNewAccountLabel">Approve Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <?php
                    // Assume you have already fetched all subjects from DB:
                    $subjects = $pdo->query("SELECT * FROM Subjects")->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <form class="row g-3" id="Account-form" method="post">
                        <div class="my-2 col-md-4">
                            <label class="form-label">Classes <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select" id="classSelect">
                                <option value="">Select Section</option>
                                <?php foreach($classes as $class) : ?>
                                <option value="<?= $class["class_id"] ?>">
                                    <?= "Adviser: " . htmlspecialchars($class["lastname"]) . " " . htmlspecialchars($class["firstname"]) . " - Section: " . htmlspecialchars($class["section_name"]) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="my-2 col-md-4">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select name="grade_level" class="form-select" id="gradeLevelSelect">
                                <option value="">Grade Level</option>
                                <option value="Grade 1">Grade 1</option>
                                <option value="Grade 2">Grade 2</option>
                                <option value="Grade 3">Grade 3</option>
                                <option value="Grade 4">Grade 4</option>
                                <option value="Grade 5">Grade 5</option>
                                <option value="Grade 6">Grade 6</option>
                            </select>
                        </div>

                        <div class="my-2 col-md-4">
                            <label class="form-label">Subject Counts <span class="text-danger">*</span></label>
                            <select name="subjectCounts" class="form-select" id="subjectCountsSelect">
                                <option value="">Select Subject Count</option>
                                <?php for($i=1; $i<=8; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Placeholder container where subject dropdowns will appear -->
                        <div id="subjectSelectContainer" class="my-2 col-md-12 d-flex gap-2 justify-content-center" style="flex-wrap: wrap !important;"></div>

                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

        for (let i = 0; i < count; i++) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('my-2');

            const label = document.createElement('label');
            label.classList.add('form-label');
            label.textContent = `Subject ${i + 1}`;

            const select = document.createElement('select');
            select.name = 'subjects[]'; // make it an array
            select.classList.add('form-select');

            // Default option
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Select Subject';
            select.appendChild(defaultOpt);

            // Add filtered subjects as options
            filteredSubjects.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.subject_id;
                opt.textContent = `${sub.subject_name} (${sub.subject_code})`;
                select.appendChild(opt);
            });

            wrapper.appendChild(label);
            wrapper.appendChild(select);
            container.appendChild(wrapper);
        }
    }

    // Regenerate whenever grade level or count changes
    gradeLevelSelect.addEventListener('change', generateSubjectSelects);
    subjectCountsSelect.addEventListener('change', generateSubjectSelects);
});
</script>