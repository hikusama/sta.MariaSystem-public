<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}
$query = "SELECT classes.*, users.* FROM classes
    INNER JOIN users ON classes.adviser_id = users.user_id
    WHERE classes.adviser_id = :adviser_id";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'adviser_id' => $user_id
]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$schoolYear = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch students for the adviser
$stmt = $pdo->prepare("SELECT DISTINCT grade_level FROM classes WHERE adviser_id = :adviser_id");
$stmt->execute([
    ':adviser_id' => $user_id
]);
$getgrade_level = $stmt->fetch(PDO::FETCH_ASSOC);
$grade_level = $getgrade_level["grade_level"] ?? '';

$stmt = $pdo->prepare("SELECT * FROM student WHERE gradeLevel = :gradeLevel ORDER BY fname ASC");
$stmt->execute([
    ':gradeLevel' => $grade_level
]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = 1;

// Get subjects for JS
$subjects = $pdo->query("SELECT * FROM Subjects")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-folder me-2"></i>Enrollment Management</h4>
    </div>
</div>
<style>
    .scroll-class {
        max-height: 80vh;
        overflow-y: auto;
    }
</style>
<div class="row g-3 scroll-class">
    <!-- Search Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="searchInput" class="form-control"
                    placeholder="Search by name, grade level, or enrollment status...">
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <select id="categoryFilter" name="statusCategory" class="form-select">
                    <option value="">All Enrollment Status</option>
                    <option value="pending">Pending</option>
                    <option value="enrolled">Enrolled</option>
                    <option value="transferred">Transferred</option>
                    <option value="dropped">Dropped</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Enrollment Overview</h5>
                    <div class="row text-center">
                        <?php
                        $pendingCount = array_filter($users, fn($u) => ($u['enrolment_status'] ?? '') == '');
                        $enrolledCount = array_filter($users, fn($u) => ($u['enrolment_status'] ?? '') == 'active');
                        $rejectedCount = array_filter($users, fn($u) => ($u['enrolment_status'] ?? '') == 'rejected');
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($users) ?></h3>
                                <small class="text-white">Total Students</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($enrolledCount) ?></h3>
                                <small class="text-white">Enrolled</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($pendingCount) ?></h3>
                                <small class="text-white">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($rejectedCount) ?></h3>
                                <small class="text-white">Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="table-container-wrapper p-0">
        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
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
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="studentTableBody">
                    <?php foreach ($users as $user) : ?>
                        <tr class="student-row"
                            data-name="<?= htmlspecialchars(strtolower($user["lname"] . " " . $user["fname"])) ?>"
                            data-grade="<?= htmlspecialchars(strtolower($user["gradeLevel"])) ?>"
                            data-status="<?= htmlspecialchars(strtolower($user["enrolment_status"] ?? '')) ?>">
                            <td width="5%"><?= $count++ ?></td>
                            <td width="20%" class="student-name">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-placeholder me-2">
                                        <i class="fa-solid fa-user-circle text-secondary"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($user["lname"] . ", " . $user["fname"]) ?></strong>
                                        <?php if (!empty($user["mname"])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($user["mname"]) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td width="15%">
                                <span class="badge bg-info"><?= htmlspecialchars($user["gradeLevel"]) ?></span>
                            </td>
                            <td width="15%">
                                <?php
                                $status = $user["enrolment_status"] ?? '';
                                $statusText = '';
                                $badgeClass = '';

                                if ($status == 'active') {
                                    $statusText = 'Enrolled';
                                    $badgeClass = 'success';
                                } elseif ($status == 'rejected') {
                                    $statusText = 'Rejected';
                                    $badgeClass = 'danger';
                                } else {
                                    $statusText = 'Pending';
                                    $badgeClass = 'secondary';
                                }
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <i class="fa-solid fa-circle fa-xs me-1"></i>
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td width="20%">
                                <?php if (!empty($user["enrolled_date"])): ?>
                                    <small><?= date('M d, Y', strtotime($user["enrolled_date"])) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Not enrolled yet</small>
                                <?php endif; ?>
                            </td>
                            <td width="25%">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="index.php?page=contents/form&student_id=<?= htmlspecialchars($user["student_id"]) ?>"
                                        class="btn btn-sm btn-info" title="View Enrollment Form">
                                        <i class="fa-solid fa-file-lines me-1"></i> Form
                                    </a>
                                    <?php if (($user["enrolment_status"] ?? '') != 'active'): ?>
                                        <button type="button" class="btn btn-sm btn-success open-enrolment"
                                            data-id="<?= htmlspecialchars($user["student_id"]) ?>" id="approvalBtn"
                                            data-gradelevel="<?= htmlspecialchars($user["gradeLevel"]) ?>"
                                            data-name="<?= htmlspecialchars($user["lname"] . ", " . $user["fname"]) ?>">
                                            <i class="fa-solid fa-check me-1"></i> Approve
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-danger open-rejection"
                                        data-id="<?= htmlspecialchars($user["student_id"]) ?>"
                                        title="Reject Enrollment" id="rejectionBtn">
                                        <i class="fa-solid fa-xmark me-1"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No students found</h5>
                <p class="text-muted">Try adjusting your search</p>
            </div>
        </div>
    </div>
</div>

<!-- enrolment modal -->
<div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="AddNewAccountLabel">Approve Student Enrollment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="enrolment-form" method="post">
                    <input type="hidden" name="student_id" id="student_id" value="">

                    <div class="my-2">
                        <label class="form-label">Class Adviser <span class="text-danger">*</span></label>
                        <input type="hidden" name="adviser_id" id="adviserSelect" value="<?= $class["adviser_id"] ?? '' ?>">
                        <div class="form-control bg-light">
                            <?= isset($class["lastname"]) ? htmlspecialchars($class["lastname"]) . ", " . htmlspecialchars($class["firstname"]) : 'Not assigned' ?>
                        </div>
                    </div>

                    <div class="my-2">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <input type="hidden" name="section_name" id="section_name" value="<?= $class["section_name"] ?? '' ?>">
                        <div class="form-control bg-light"><?= htmlspecialchars($class["section_name"] ?? 'Not assigned') ?></div>
                    </div>

                    <div class="my-2">
                        <label class="form-label">School Year <span class="text-danger">*</span></label>
                        <div class="form-control bg-light"><?= htmlspecialchars($schoolYear["school_year_name"] ?? 'Not set') ?></div>
                        <input type="hidden" name="schoolyear_id" value="<?= $schoolYear["school_year_id"] ?? '' ?>">
                    </div>

                    <div class="my-2">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <div class="form-control bg-light" id="gradeLevelDisplay"></div>
                        <input type="hidden" id="gradeLevelValue" name="grade_level" class="form-control">
                    </div>

                    <div class="my-2">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3"><i class="fa-solid fa-book me-2"></i>Subjects for this Grade Level</h6>
                                <div id="subjectListContainer" class="row">
                                    <p class="text-muted text-center">Select a student to view their subjects</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-check me-2"></i>Approve Enrollment
                        </button>
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
                <h5 class="modal-title text-white" id="rejectEnrolmentLabel">
                    <i class="fa-solid fa-xmark me-2"></i>Reject Enrollment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="rejectEnrolment-form" method="post">
                    <input type="hidden" name="studentID" id="studentID">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                        <h5>Confirm Rejection</h5>
                        <p class="text-muted">Are you sure you want to reject this student's enrollment?</p>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Confirm Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Store all subjects data from PHP
        const allSubjects = <?= json_encode($subjects); ?>;

        // Store student data from PHP
        const allStudents = <?= json_encode($users); ?>;

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
                gradeLevelDisplay.textContent = gradeLevel;
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

    // Search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const studentRows = document.querySelectorAll('.student-row');
        const studentTableBody = document.getElementById('studentTableBody');
        const noResultsDiv = document.getElementById('noResults');

        function filterStudents() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filterValue = categoryFilter.value.toLowerCase();

            let visibleCount = 0;

            studentRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const grade = row.getAttribute('data-grade');
                const status = row.getAttribute('data-status');

                let matchesSearch = true;
                let matchesStatus = true;

                // Apply search filter
                if (searchTerm) {
                    matchesSearch = name.includes(searchTerm) ||
                        grade.includes(searchTerm) ||
                        status.includes(searchTerm);
                }

                // Apply status filter
                if (filterValue) {
                    if (filterValue === 'enrolled') {
                        matchesStatus = status === 'active';
                    } else if (filterValue === 'pending') {
                        matchesStatus = status === '' || status === 'pending';
                    } else if (filterValue === 'rejected') {
                        matchesStatus = status === 'rejected';
                    } else if (filterValue === 'transferred') {
                        matchesStatus = status === 'transferred';
                    } else if (filterValue === 'dropped') {
                        matchesStatus = status === 'dropped';
                    } else {
                        matchesStatus = true; // Show all if no match
                    }
                }

                // Show/hide row based on filters
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0) {
                studentTableBody.style.display = 'none';
                noResultsDiv.classList.remove('d-none');
            } else {
                studentTableBody.style.display = '';
                noResultsDiv.classList.add('d-none');
            }

            // Update row numbers
            updateRowNumbers();
        }

        function updateRowNumbers() {
            let counter = 1;
            studentRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const firstCell = row.querySelector('td:first-child');
                    if (firstCell) {
                        firstCell.textContent = counter++;
                    }
                }
            });
        }

        // Event listeners
        searchInput.addEventListener('input', filterStudents);
        categoryFilter.addEventListener('change', filterStudents);

        // clearSearchBtn.addEventListener('click', function() {
        //     searchInput.value = '';
        //     categoryFilter.value = '';
        //     filterStudents();
        //     searchInput.focus();
        // });

        // Add Enter key support for search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterStudents();
            }
        });

        // Add some styling
        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('border-primary', 'border-2');
        });

        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('border-primary', 'border-2');
        });

        categoryFilter.addEventListener('focus', function() {
            this.parentElement.classList.add('border-primary', 'border-2');
        });

        categoryFilter.addEventListener('blur', function() {
            this.parentElement.classList.remove('border-primary', 'border-2');
        });

        // Initialize
        filterStudents();
    });
</script>

<style>
    .table-container-wrapper {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .avatar-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .empty-state {
        padding: 3rem 1rem;
    }

    .empty-state i {
        opacity: 0.5;
    }

    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .input-group-text {
        border-right: none;
    }

    #searchInput:focus {
        box-shadow: none;
        border-color: #86b7fe;
    }

    .form-control.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
        font-weight: 500;
    }

    .card {
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    #clearSearch:hover {
        background-color: #e9ecef;
    }
</style>