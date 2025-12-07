<div class="d-flex justify-content-start align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-folder me-2"></i></i>Learners Management</h4>
    </div>
</div>

<!-- Search and Filters -->

<div class="row g-2  justify-content-between">
    <div class="row mb-4  justify-content-between">
        <div class="col-md-4">
            <input type="text" id="searchInput" name="search" class="form-control"
                placeholder="Search by name, role, status, or date...">
        </div>
        <div class="col-md-4">
            <select id="categoryFilter" name="statusCategory" class="form-select">
                <option value="">Enrollment Status</option>
                <option value="active">Enrolled</option>
                <option value="transferred_in">Transferred in</option>
                <option value="transferred_out">Transferred out</option>
                <option value="not_active">not active</option>
                <option value="rejected">Rejected</option>
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
            $stmt = $pdo->prepare("SELECT student.*, users.firstname AS parentFirstname, 
            users.lastname AS parentLastname, users.middlename AS parentMiddle FROM student
            INNER JOIN users ON users.user_id = student.guardian_id
            WHERE student.enrolment_status != 'pending' ORDER BY fname ASC");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
        ?>

        <!-- Fixed Header -->
        <div class="table-responsive text-center">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">Lrn</th>
                        <th width="20%">Name</th>
                        <th width="20%">Parent/Guardian</th>
                        <th width="10%">Grade</th>
                        <th width="10%">Remarks</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($users){
                            foreach($users as $user) : ?>
                            <tr data-status="<?= htmlspecialchars($user['enrolment_status']) ?>"
                                data-grade="<?= htmlspecialchars($user['gradeLevel'] ?? '') ?>"
                                data-name="<?= htmlspecialchars($user['lname'] . ' ' . $user['fname'] . ' ' . $user['mname']) ?>">
                                <td width="5%"><?= $count++ ?></td>
                                <td width="10%"><?= htmlspecialchars($user["lrn"]) ?></td>
                                <td width="20%">
                                    <?= htmlspecialchars($user["lname"]) . " " . htmlspecialchars($user["fname"]) . " " .  
                                    (!empty($user["mname"]) ? htmlspecialchars(substr($user["mname"], 0, 1)) . ". " : "") ?>
                                </td>
                                <td width="20%">
                                    <?= htmlspecialchars($user["parentLastname"]) . " " . htmlspecialchars($user["parentFirstname"]) ?>
                                </td>
                                <td width="10%">
                                    <?= htmlspecialchars($user["gradeLevel"]) ?>
                                </td>
                                <?php
                                        $statusMap = [
                                            'active'      => ['success',   'Enrolled'],
                                            'pending'     => ['warning',   'Pending'],
                                            'transferred' => ['secondary',      'Transferred'],
                                            'dropped'     => ['danger',    'Dropped'],
                                            'rejected'    => ['danger', 'Rejected']
                                        ];

                                        $currentStatus = $user['enrolment_status'] ?? 'pending';
                                        $badgeClass = $statusMap[$currentStatus][0] ?? 'secondary';
                                        $label      = $statusMap[$currentStatus][1] ?? ucfirst($currentStatus);
                                        ?>
                                <td width="10%">
                                    <span class="badge bg-<?= $badgeClass ?>"><?= $label ?></span>
                                </td>
                                <td width="15%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a
                                            href="index.php?page=contents/profile&student_id=<?= htmlspecialchars($user["student_id"]) ?>"><button
                                                class="btn m-0 btn-sm h-100 btn-info">Profile</button>
                                        </a>
                                        <form class="status-enrolment-form">
                                            <select name="status" class="status-enrolment-select form-select">
                                                <option value="">Select Status</option>
                                                <option value="active"
                                                    <?= ($user["enrolment_status"] === "active") ? "selected" : "" ?>>
                                                    Enrolled</option>
                                                <option value="transferred_in"
                                                    <?= ($user["enrolment_status"] === "transferred_in") ? "selected" : "" ?>>
                                                    transferred in</option>
                                                <option value="transferred_out"
                                                    <?= ($user["enrolment_status"] === "transferred_out") ? "selected" : "" ?>>
                                                    transferred out</option>
                                                <option value="not_active"
                                                    <?= ($user["enrolment_status"] === "not_active") ? "selected" : "" ?>>
                                                    not active</option>
                                                <option value="dropped"
                                                    <?= ($user["enrolment_status"] === "dropped") ? "selected" : "" ?>>dropped
                                                </option>
                                                <option value="rejected"
                                                    <?= ($user["enrolment_status"] === "rejected") ? "selected" : "" ?>>rejected
                                                </option>
                                            </select>
                                            <input type="hidden" name="user_id" value="<?= $user['student_id'] ?>">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                    <?php endforeach;
                    }else{
                            echo '<tr><td colspan="7">No learners found.</td></tr>';
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
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
<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.querySelector('select[name="statusCategory"]');
    const gradeFilter = document.querySelector('select[name="gradeLevelCategory"]');
    const rows = document.querySelectorAll(".table-body-scroll tbody tr");

    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const gradeValue = gradeFilter.value.toLowerCase();

        rows.forEach(row => {
            const rowStatus = row.getAttribute("data-status")?.toLowerCase();
            const rowGrade = row.getAttribute("data-grade")?.toLowerCase();
            const rowName = row.getAttribute("data-name")?.toLowerCase();

            let matchesSearch = rowName.includes(searchText) || searchText === "";
            let matchesStatus = (statusValue === "" || rowStatus === statusValue);
            let matchesGrade = (gradeValue === "" || rowGrade === gradeValue);

            if (matchesSearch && matchesStatus && matchesGrade) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    searchInput.addEventListener("input", filterTable);
    statusFilter.addEventListener("change", filterTable);
    gradeFilter.addEventListener("change", filterTable);
});
</script>