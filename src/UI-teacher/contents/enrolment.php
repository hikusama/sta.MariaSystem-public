<?php
    $query = "SELECT classes.*, users.* FROM classes
    INNER JOIN users ON classes.adviser_id = users.user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT * FROM school_year WHERE school_year_status = 'Active'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $schoolYear = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-folder me-2"></i></i>Enrollment Management</h4>
    </div>
</div>

<!-- Search and Filters -->

<div class="row g-2  justify-content-between">
    <div class="row mb-3  justify-content-start">
        <div class="col-md-4">
            <input type="text" id="searchInput" name="search" class="form-control"
                placeholder="Search by name, role, status, or date...">
        </div>
        <div class="col-md-4">
            <select id="categoryFilter" name="statusCategory" class="form-select">
                <option value="">Enrollment Status</option>
                <option value="pending">Pending</option>
                <option value="enrolled">Enrolled</option>
                <option value="transferred">Transferred</option>
                <option value="dropped">Dropped</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>
    <!-- Accounts Displays -->
    <div class="table-container-wrapper">
        <?php
            $stmt = $pdo->prepare("SELECT DISTINCT grade_level FROM classes WHERE adviser_id = :adviser_id");
            $stmt->execute([
                ':adviser_id' => $user_id
            ]);
            $getgrade_lelvel = $stmt->fetch(PDO::FETCH_ASSOC);
            $grade_level = $getgrade_lelvel["grade_level"];
            $stmt = $pdo->prepare("SELECT * FROM student WHERE gradeLevel = :gradeLevel
            ORDER BY fname ASC");
            $stmt->execute([
                ':gradeLevel' => $grade_level
            ]);
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
                        <th width="15%">Enrollment Status</th>
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
                            <span class="badge bg-<?= 
                                    ($user["enrolment_status"] == 'active') ? 'success' : 
                                    (($user["enrolment_status"] == 'rejected') ? 'danger' : 'secondary')
                                ?>">
                                                                <?= 
                                        ($user["enrolment_status"] == 'active') ? 'Enrolled' : 
                                        (($user["enrolment_status"] == 'rejected') ? 'Rejected' : 'Pending')
                                    ?>
                            </span>
                        </td>

                        <td width="20%"><?= htmlspecialchars($user["enrolled_date"]) ?></td>
                        <td width="25%">
                            <div class="d-flex gap-1 justify-content-center">
                                <a
                                    href="index.php?page=contents/form&student_id=<?= htmlspecialchars($user["student_id"]) ?>"><button
                                        class="btn btn-sm m-0 btn-info">Enrollment Form</button></a>
                                <button type="button" class="btn btn-success btn-sm open-enrolment"
                                    data-id="<?= htmlspecialchars($user["student_id"]) ?>"
                                    data-gradelevel="<?= htmlspecialchars($user["gradeLevel"]) ?>">Approve</button>
                                <button type="button" id="rejectionBtn" data-id="<?= $user["student_id"] ?>"
                                    class="btn btn-danger btn-sm">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- enrolment modal -->
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
                            <label class="form-label">Class Adviser <span class="text-danger">*</span></label>
                            <select name="adviser_id" id="adviserSelect" class="form-select" required>
                                <option value="">Select Adviser</option>
                                <?php foreach($classes as $class): ?>
                                <option value="<?= $class["adviser_id"] ?>"
                                    data-section="<?= htmlspecialchars($class["section_name"]) ?>">
                                    <?= htmlspecialchars($class["lastname"]) . ", " . htmlspecialchars($class["firstname"]) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <input type="text" name="section_name" id="section_name" class="form-control" readonly
                                required>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">School Year <span class="text-danger">*</span></label>
                            <input type="text" readonly name="school_year_name"
                                value="<?= $schoolYear["school_year_name"] ?>" class="form-control">
                            <input type="hidden" name="schoolyear_id" value="<?= $schoolYear["school_year_id"] ?>"
                                class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <input type="text" id="gradeLevelDisplay" readonly class="form-control">
                            <input type="hidden" id="gradeLevelValue" name="grade_level">
                        </div>

                        <div class="col-12">
                            <div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">Subjects for this Grade Level</h6>
                                </div>
                                <div class="card-body">
                                    <div id="subjectListContainer" class="row">
                                        <p class="text-muted">Select a student to view their subjects</p>
                                    </div>
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
    <!-- rejection modal -->
    <div class="modal fade" id="rejectEnrolment" tabindex="-1" aria-labelledby="rejectEnrolmentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="rejectEnrolmentLabel">Deactivation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="rejectEnrolment-form" method="post">
                        <input type="hidden" name="studentID" id="studentID">
                        <span class="m-2">Are you Sure you want to <strong>Reject</strong> this student
                            Enrolment?</span>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">
                                Reject
                            </button>
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
document.getElementById('adviserSelect').addEventListener('change', function() {
    const selectedOption = this.selectedOptions[0];
    const section = selectedOption.dataset.section || '';
    document.getElementById('section_name').value = section;
});

document.addEventListener('DOMContentLoaded', () => {

    // Open enrolment modal
    const openEnrolmentButtons = document.querySelectorAll('.open-enrolment');
    const studentIdInput = document.getElementById('student_id');
    const gradeLevelDisplay = document.getElementById('gradeLevelDisplay');
    const gradeLevelValue = document.getElementById('gradeLevelValue');
    const subjectListContainer = document.getElementById('subjectListContainer');

    openEnrolmentButtons.forEach(button => {
        button.addEventListener('click', () => {
            const studentId = button.getAttribute('data-id');
            const gradeLevel = button.getAttribute('data-gradelevel');

            // Set values in the form
            studentIdInput.value = studentId;
            gradeLevelDisplay.value = gradeLevel;
            gradeLevelValue.value = gradeLevel;

            // Display subjects for this grade level
            displaySubjectsForGradeLevel(gradeLevel);

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('AddNewAccount'));
            modal.show();
        });
    });

    function displaySubjectsForGradeLevel(gradeLevel) {
        // Clear previous content
        subjectListContainer.innerHTML = '';

        // Filter subjects by grade level
        const filteredSubjects = allSubjects.filter(s => s.grade_level === gradeLevel);

        if (filteredSubjects.length === 0) {
            subjectListContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">No subjects available for ${gradeLevel}.</div>
                </div>
            `;
            return;
        }

        // Create a list of subjects
        const listGroup = document.createElement('div');
        listGroup.classList.add('list-group');

        filteredSubjects.forEach(subject => {
            const listItem = document.createElement('div');
            listItem.classList.add('list-group-item', 'd-flex', 'justify-content-between',
                'align-items-center');

            const subjectInfo = document.createElement('div');
            subjectInfo.innerHTML = `
                <strong>${subject.subject_code}</strong> - ${subject.subject_name}
                <input type="hidden" name="subjects[]" value="${subject.subject_id}">
            `;

            listItem.appendChild(subjectInfo);
            listGroup.appendChild(listItem);
        });

        subjectListContainer.appendChild(listGroup);
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.querySelector("select[name='statusCategory']");
    const gradeFilter = document.querySelector("select[name='gradeLevelCategory']");
    const tableRows = document.querySelectorAll(".table-body-scroll tbody tr");

    function filterTable() {
        const searchValue = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const gradeValue = gradeFilter.value.toLowerCase();

        tableRows.forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const grade = row.cells[2].textContent.toLowerCase();
            const status = row.cells[3].textContent.toLowerCase();
            const date = row.cells[4].textContent.toLowerCase();

            let matchesSearch =
                name.includes(searchValue) ||
                grade.includes(searchValue) ||
                status.includes(searchValue) ||
                date.includes(searchValue);

            let matchesStatus = !statusValue || status.includes(statusValue);
            let matchesGrade = !gradeValue || grade.includes(gradeValue);

            if (matchesSearch && matchesStatus && matchesGrade) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Attach event listeners
    searchInput.addEventListener("input", filterTable);
    statusFilter.addEventListener("change", filterTable);
    gradeFilter.addEventListener("change", filterTable);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const tableRows = document.querySelectorAll('table tbody tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const filterValue = categoryFilter.value.toLowerCase();
        
        tableRows.forEach(row => {
            let showRow = true;
            
            // Search filter
            if (searchTerm) {
                const rowText = row.textContent.toLowerCase();
                if (!rowText.includes(searchTerm)) {
                    showRow = false;
                }
            }
            
            // Status filter
            if (filterValue && showRow) {
                const statusBadge = row.querySelector('.badge');
                if (statusBadge) {
                    const statusText = statusBadge.textContent.toLowerCase().trim();
                    if (statusText !== filterValue) {
                        showRow = false;
                    }
                }
            }
            
            // Show/hide row
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    // Add event listeners
    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);
    
    // Initial filter
    filterTable();
});
</script>