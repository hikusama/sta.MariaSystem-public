<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}
echo $admin_role = $_SESSION['admin_role'];
echo $admin_id = $_SESSION['admin_id'];
// $user_id = $_SESSION['user_id'];
$query = "SELECT classes.*, users.* FROM classes
    INNER JOIN users ON classes.adviser_id = users.user_id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students for the adviser
$stmt = $pdo->prepare("SELECT * FROM enrolment
        INNER JOIN student ON enrolment.student_id = student.student_id
        WHERE adviser_id = 1
        ORDER BY fname ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = 1;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-clipboard-check me-2"></i>Student Attendance</h4>
    </div>
</div>

<div class="row g-3">
    <!-- Search and Filter Section -->
    <div class="row col-md-12 mb-3 p-0 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group d-flex justify-content-between">
                <div class="col-md-6">
                    <input type="text" id="searchInput" name="search" class="form-control"
                        placeholder="Search by name, grade level, or section...">
                </div>
                <div class="col-md-6">
                    <select id="categoryFilter" name="gradeLevelCategory" class="form-select ms-2" style="max-width: 200px;">
                        <option value="">All Sessions</option>
                        <option value="Morning">Morning Session</option>
                        <option value="Afternoon">Afternoon Session</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="bg-light p-2 rounded d-inline-block">
                <i class="fa-solid fa-calendar-day text-primary me-1"></i>
                <strong>Today:</strong> <?= date('F j, Y') ?>
            </div>
        </div>
    </div>

    <!-- Morning Table -->
    <div class="table-container-wrapper morning p-0 d-none">
        <?php $count = 1; ?>
        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
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
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="morningTableBody">
                    <?php foreach ($users as $user) : ?>
                        <tr class="student-row"
                            data-name="<?= htmlspecialchars(strtolower($user["lname"] . " " . $user["fname"])) ?>"
                            data-grade="<?= htmlspecialchars(strtolower($user["gradeLevel"])) ?>"
                            data-section="<?= htmlspecialchars(strtolower($user["section_name"])) ?>"
                            data-status="<?= $user["enrolment_status"] ?>">
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
                                <span class="badge bg-secondary"><?= htmlspecialchars($user["section_name"]) ?></span>
                            </td>
                            <td width="15%">
                                <span class="badge bg-<?= ($user["enrolment_status"] == 'active') ? 'success' : 'secondary' ?>">
                                    <i class="fa-solid fa-circle fa-xs me-1"></i>
                                    <?= ($user["enrolment_status"] == 'active') ? 'Enrolled' : 'Inactive' ?>
                                </span>
                            </td>
                            <td width="15%">
                                <small><?= date('M d, Y', strtotime($user["enrolled_date"])) ?></small>
                            </td>
                            <td width="15%">
                                <div class="d-flex gap-1 justify-content-center">
                                    <form id="morning_attendanceP">
                                        <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                    <form id="morning_attendanceA">
                                        <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                    <form id="morning_attendanceL">
                                        <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fa-solid fa-clock"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="morningNoResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No students found</h5>
                <p class="text-muted">Try adjusting your search</p>
            </div>
        </div>
    </div>

    <!-- Afternoon Table -->
    <div class="table-container-wrapper afternoon p-0 d-none">
        <?php $count = 1; ?>
        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
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
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="afternoonTableBody">
                    <?php foreach ($users as $user) : ?>
                        <tr class="student-row"
                            data-name="<?= htmlspecialchars(strtolower($user["lname"] . " " . $user["fname"])) ?>"
                            data-grade="<?= htmlspecialchars(strtolower($user["gradeLevel"])) ?>"
                            data-section="<?= htmlspecialchars(strtolower($user["section_name"])) ?>"
                            data-status="<?= $user["enrolment_status"] ?>">
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
                                <span class="badge bg-secondary"><?= htmlspecialchars($user["section_name"]) ?></span>
                            </td>
                            <td width="15%">
                                <span class="badge bg-<?= ($user["enrolment_status"] == 'active') ? 'success' : 'secondary' ?>">
                                    <i class="fa-solid fa-circle fa-xs me-1"></i>
                                    <?= ($user["enrolment_status"] == 'active') ? 'Enrolled' : 'Inactive' ?>
                                </span>
                            </td>
                            <td width="15%">
                                <small><?= date('M d, Y', strtotime($user["enrolled_date"])) ?></small>
                            </td>
                            <td width="15%">
                                <div class="d-flex gap-1 justify-content-center">
                                    <form id="afternoon_attendanceP">
                                        <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                    <form id="afternoon_attendanceA">
                                        <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                    <form id="afternoon_attendanceL">
                                        <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fa-solid fa-clock"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="afternoonNoResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No students found</h5>
                <p class="text-muted">Try adjusting your search</p>
            </div>
        </div>
    </div>

    <!-- No Table Selected Message -->
    <div id="noTableSelected" class="text-center py-5">
        <div class="empty-state">
            <i class="fa-solid fa-calendar-check fa-3x text-primary mb-3"></i>
            <h5>Select a Session</h5>
            <p class="text-muted">Choose "Morning Session" or "Afternoon Session" from the dropdown to view attendance</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const morningTable = document.querySelector(".table-container-wrapper.morning");
        const afternoonTable = document.querySelector(".table-container-wrapper.afternoon");
        const noTableSelected = document.getElementById('noTableSelected');

        // Function to toggle tables based on filter
        function toggleTables() {
            const selected = categoryFilter.value;

            if (selected === "Morning") {
                morningTable.classList.remove('d-none');
                afternoonTable.classList.add('d-none');
                noTableSelected.classList.add('d-none');
                filterStudents();
            } else if (selected === "Afternoon") {
                morningTable.classList.add('d-none');
                afternoonTable.classList.remove('d-none');
                noTableSelected.classList.add('d-none');
                filterStudents();
            } else {
                morningTable.classList.add('d-none');
                afternoonTable.classList.add('d-none');
                noTableSelected.classList.remove('d-none');
            }
        }

        // Function to filter students
        function filterStudents() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selected = categoryFilter.value;

            let tableBody, noResultsDiv;

            if (selected === "Morning") {
                tableBody = document.getElementById('morningTableBody');
                noResultsDiv = document.getElementById('morningNoResults');
            } else if (selected === "Afternoon") {
                tableBody = document.getElementById('afternoonTableBody');
                noResultsDiv = document.getElementById('afternoonNoResults');
            } else {
                return;
            }

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const grade = row.getAttribute('data-grade');
                const section = row.getAttribute('data-section');

                let matchesSearch = true;

                if (searchTerm) {
                    matchesSearch = name.includes(searchTerm) ||
                        grade.includes(searchTerm) ||
                        section.includes(searchTerm);
                }

                if (matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                tableBody.style.display = 'none';
                noResultsDiv.classList.remove('d-none');
            } else {
                tableBody.style.display = '';
                noResultsDiv.classList.add('d-none');
            }

            updateRowNumbers(tableBody);
        }

        // Function to update row numbers
        function updateRowNumbers(tableBody) {
            let counter = 1;
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
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
        categoryFilter.addEventListener('change', toggleTables);

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterStudents();
            searchInput.focus();
        });

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
        toggleTables();
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
        min-width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
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

    .btn-sm:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>