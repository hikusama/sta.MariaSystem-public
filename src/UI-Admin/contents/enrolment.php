<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$query = "SELECT classes.*, users.* FROM classes
    INNER JOIN users ON classes.adviser_id = users.user_id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute();
$schoolYear = $stmt->fetch(PDO::FETCH_ASSOC);

$users = [];
$count = 1;

$stmt = $pdo->prepare("
        SELECT s.*, u.* 
        FROM student s
        LEFT JOIN users u 
            ON s.guardian_id = u.user_id
        ORDER BY s.fname ASC
    ");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get subjects for JS
$subjects = $pdo->query("SELECT * FROM Subjects")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-folder me-2"></i>Enrollment Management</h4>
    </div>
</div>

<div class="row g-3 scroll-classes">
    <!-- Search and Filter Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-12">
            <div class="row input-group">
                <div class="col-md-4">
                    <input type="text" id="searchInput" name="search" class="form-control"
                        placeholder="Search by name, grade level, or enrollment status...">
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" name="statusCategory" class="form-select" style="max-width: 200px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Enrolled</option>
                        <option value="transferred">Transferred</option>
                        <option value="dropped">Dropped</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <select id="gradeFilter" name="gradeLevelCategory" class="form-select" style="max-width: 200px;">
                        <option value="">All Grades</option>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                        <option value="Grade 4">Grade 4</option>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Enrollment Statistics</h5>
                    <div class="row text-center">
                        <?php
                        $pendingCount = array_filter($users, fn($u) => empty($u['enrolment_status']) || $u['enrolment_status'] == 'pending');
                        $enrolledCount = array_filter($users, fn($u) => $u['enrolment_status'] == 'active');
                        $transferredCount = array_filter($users, fn($u) => $u['enrolment_status'] == 'transferred');
                        $rejectedCount = array_filter($users, fn($u) => $u['enrolment_status'] == 'rejected');
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($users) ?></h3>
                                <small class="text-white">Total Students</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($enrolledCount) ?></h3>
                                <small class="text-white">Enrolled</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($pendingCount) ?></h3>
                                <small class="text-white">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1">
                                    <?= count($rejectedCount) + count(array_filter($users, fn($u) => $u['enrolment_status'] == 'dropped')) ?>
                                </h3>
                                <small class="text-white">Rejected/Dropped</small>
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
                <tbody id="enrollmentTableBody">
                    <?php if ($users):
                        $count = 1;
                        foreach ($users as $user) :
                            $status = $user["enrolment_status"] ?? '';
                            $badgeClass = '';
                            $statusText = '';

                            if ($status == 'active') {
                                $badgeClass = 'success';
                                $statusText = 'Enrolled';
                            } elseif ($status == 'rejected') {
                                $badgeClass = 'danger';
                                $statusText = 'Rejected';
                            } elseif ($status == 'transferred') {
                                $badgeClass = 'info';
                                $statusText = 'Transferred';
                            } elseif ($status == 'dropped') {
                                $badgeClass = 'danger';
                                $statusText = 'Dropped';
                            } else {
                                $badgeClass = 'secondary';
                                $statusText = 'Pending';
                            }
                    ?>
                            <tr class="student-row"
                                data-name="<?= htmlspecialchars(strtolower($user["lname"] . " " . $user["fname"])) ?>"
                                data-grade="<?= htmlspecialchars(strtolower($user["gradeLevel"] ?? '')) ?>"
                                data-status="<?= htmlspecialchars(strtolower($status)) ?>">
                                <td width="5%"><?= $count++ ?></td>
                                <td width="20%" class="student-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-user-graduate text-secondary"></i>
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
                                    <span class="badge bg-info"><?= htmlspecialchars($user["gradeLevel"] ?? 'Not set') ?></span>
                                </td>
                                <td width="15%">
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
                                        <?php if ($status != 'active' && $status != 'rejected'): ?>
                                            <button type="button" class="btn btn-success btn-sm open-enrolment"
                                                data-id="<?= htmlspecialchars($user["student_id"]) ?>"
                                                data-gradelevel="<?= htmlspecialchars($user["gradeLevel"]) ?>"
                                                title="Approve Enrollment">
                                                <i class="fa-solid fa-check me-1"></i> Approve
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($status != 'rejected' && $status != 'active'): ?>
                                            <button type="button" class="btn btn-danger btn-sm open-rejection"
                                                data-id="<?= htmlspecialchars($user["student_id"]) ?>" title="Reject Enrollment">
                                                <i class="fa-solid fa-xmark me-1"></i> Reject
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No students found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>
        </div>
    </div>
</div>

<!-- enrolment modal -->
<div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="AddNewAccountLabel">
                    <i class="fa-solid fa-user-check me-2"></i>Approve Student Enrolment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="enrolment-form" method="post">
                    <input type="hidden" name="student_id" id="student_id" value="">

                    <div class="col-md-6">
                        <label class="form-label">Class Adviser <span class="text-danger">*</span></label>
                        <select name="adviser_id" id="adviserSelect" class="form-select" required>
                            <option value="">Select Adviser</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class["adviser_id"] ?>"
                                    data-section="<?= htmlspecialchars($class["section_name"]) ?>">
                                    <?= htmlspecialchars($class["lastname"]) . ", " . htmlspecialchars($class["firstname"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <div class="form-control bg-light" id="section_name"></div>
                        <input type="hidden" name="section_name" id="section_name_hidden">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">School Year <span class="text-danger">*</span></label>
                        <div class="form-control bg-light">
                            <?= htmlspecialchars($schoolYear["school_year_name"] ?? 'Not set') ?></div>
                        <input type="hidden" name="schoolyear_id" value="<?= $schoolYear["school_year_id"] ?? '' ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <div class="form-control bg-light" id="gradeLevelDisplay"></div>
                        <input type="hidden" id="gradeLevelValue" name="grade_level">
                    </div>

                    <div class="col-12">
                        <div class="card mt-3 border">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0"><i class="fa-solid fa-book me-2"></i>Subjects for this Grade
                                    Level</h6>
                            </div>
                            <div class="card-body">
                                <div id="subjectListContainer" class="row">
                                    <p class="text-muted text-center">Select a student to view their subjects</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fa-solid fa-check me-2"></i>Approve Enrolment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- rejection modal -->
<div class="modal fade" id="rejectEnrolment" tabindex="-1" aria-labelledby="rejectEnrolmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="rejectEnrolmentLabel">
                    <i class="fa-solid fa-user-xmark me-2"></i>Reject Enrollment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
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
    // Pass subjects from PHP to JS
    const allSubjects = <?= json_encode($subjects) ?>;
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const gradeFilter = document.getElementById('gradeFilter');
        const studentRows = document.querySelectorAll('.student-row');
        const enrollmentTableBody = document.getElementById('enrollmentTableBody');
        const noResultsDiv = document.getElementById('noResults');

        // Adviser select handler
        const adviserSelect = document.getElementById('adviserSelect');
        const sectionNameDiv = document.getElementById('section_name');
        const sectionNameHidden = document.getElementById('section_name_hidden');

        if (adviserSelect && sectionNameDiv) {
            adviserSelect.addEventListener('change', function() {
                const selectedOption = this.selectedOptions[0];
                const section = selectedOption.dataset.section || '';
                sectionNameDiv.textContent = section;
                sectionNameHidden.value = section;
            });
        }

        // Search and filter functionality
        function filterStudents() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const statusValue = statusFilter.value.toLowerCase();
            const gradeValue = gradeFilter.value.toLowerCase();

            let visibleCount = 0;

            studentRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const grade = row.getAttribute('data-grade');
                const status = row.getAttribute('data-status');

                let matchesSearch = true;
                let matchesStatus = true;
                let matchesGrade = true;

                // Apply search filter
                if (searchTerm) {
                    matchesSearch = name.includes(searchTerm);
                }

                // Apply status filter
                if (statusValue) {
                    matchesStatus = status.includes(statusValue);
                }

                // Apply grade filter
                if (gradeValue) {
                    matchesGrade = grade.includes(gradeValue.toLowerCase());
                }

                // Show/hide row based on filters
                if (matchesSearch && matchesStatus && matchesGrade) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0) {
                enrollmentTableBody.style.display = 'none';
                noResultsDiv.classList.remove('d-none');
            } else {
                enrollmentTableBody.style.display = '';
                noResultsDiv.classList.add('d-none');
            }

            // Update row numbers
            updateRowNumbers();
        }

        // Function to update row numbers
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
        statusFilter.addEventListener('change', filterStudents);
        gradeFilter.addEventListener('change', filterStudents);

        // clearSearchBtn.addEventListener('click', function() {
        //     searchInput.value = '';
        //     statusFilter.value = '';
        //     gradeFilter.value = '';
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

        statusFilter.addEventListener('focus', function() {
            this.parentElement.classList.add('border-primary', 'border-2');
        });

        statusFilter.addEventListener('blur', function() {
            this.parentElement.classList.remove('border-primary', 'border-2');
        });

        gradeFilter.addEventListener('focus', function() {
            this.parentElement.classList.add('border-primary', 'border-2');
        });

        gradeFilter.addEventListener('blur', function() {
            this.parentElement.classList.remove('border-primary', 'border-2');
        });

        // Initialize
        filterStudents();
    });

    // Enrollment modal functionality
    document.addEventListener('DOMContentLoaded', () => {
        // Open enrolment modal
        const openEnrolmentButtons = document.querySelectorAll('.open-enrolment');
        const openRejectionButtons = document.querySelectorAll('.open-rejection');
        const studentIdInput = document.getElementById('student_id');
        const gradeLevelDisplay = document.getElementById('gradeLevelDisplay');
        const gradeLevelValue = document.getElementById('gradeLevelValue');
        const subjectListContainer = document.getElementById('subjectListContainer');
        const studentIDInput = document.getElementById('studentID');

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
                    <div class="alert alert-warning"><i class="fa-solid fa-exclamation-triangle me-2"></i>No subjects available for ${gradeLevel}.</div>
                </div>
            `;
                return;
            }

            // Create a list of subjects
            const listGroup = document.createElement('div');
            listGroup.classList.add('list-group');

            filteredSubjects.forEach(subject => {
                const listItem = document.createElement('div');
                listItem.classList.add('list-group-item');

                const subjectInfo = document.createElement('div');
                subjectInfo.classList.add('d-flex', 'justify-content-between', 'align-items-center');
                subjectInfo.innerHTML = `
                <div>
                    <strong class="d-block">${subject.subject_code}</strong>
                    <small class="text-muted">${subject.subject_name}</small>
                </div>
                <input type="hidden" name="subjects[]" value="${subject.subject_id}">
            `;

                listItem.appendChild(subjectInfo);
                listGroup.appendChild(listItem);
            });

            subjectListContainer.innerHTML = '';
            subjectListContainer.appendChild(listGroup);
        }

        // Rejection modal
        openRejectionButtons.forEach(button => {
            button.addEventListener('click', () => {
                const studentId = button.getAttribute('data-id');
                studentIDInput.value = studentId;

                const modal = new bootstrap.Modal(document.getElementById('rejectEnrolment'));
                modal.show();
            });
        });
    });
</script>

<style>
    .scroll-classes {
        height: 80vh;
        overflow-y: scroll;
        overflow-x: hidden;
    }

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

    #clearSearch:hover {
        background-color: #e9ecef;
    }

    .form-control.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
        color: #495057;
    }

    .card {
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .list-group-item {
        border-left: 0;
        border-right: 0;
    }

    .list-group-item:first-child {
        border-top: 0;
    }

    .list-group-item:last-child {
        border-bottom: 0;
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>