<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$currentSyStmt = $pdo->prepare("SELECT school_year_id FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
$currentSyStmt->execute();
$currentSy = $currentSyStmt->fetch(PDO::FETCH_ASSOC);
$activeSyId = $currentSy['school_year_id'] ?? null;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-users-gear me-2"></i>Users Management</h4>
    </div>
</div>

<div class="row g-3">
    <!-- Search and Filter Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="row input-group">
                <div class="col-md-6">
                    <input type="text" id="searchInput" name="search" class="form-control"
                        placeholder="Search by name, role, or status...">
                </div>
                <div class="col-md-5">
                    <select id="categoryFilter" name="category" class="form-select" style="max-width: 200px;">
                        <option value="">All User Roles</option>
                        <?php
                        $catStmt = $pdo->query("SELECT DISTINCT user_role FROM users ORDER BY user_role ASC");
                        while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= htmlspecialchars($cat['user_role']) ?>">
                                <?= htmlspecialchars($cat['user_role']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#AddNewAccount"
                id="add_new">
                <i class="fa-solid fa-user-plus me-2"></i> New Account
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="table-container-wrapper p-0">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM users WHERE school_year_id = ? ORDER BY created_date DESC");
        $stmt->execute([$activeSyId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 1;
        ?>

        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Name</th>
                        <th width="15%">User Role</th>
                        <th width="15%">Status</th>
                        <th width="20%">Created at</th>
                        <th width="25%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="userTableBody">
                    <?php if ($users):
                        $count = 1;
                        foreach ($users as $user) : ?>
                            <tr class="user-row"
                                data-name="<?= htmlspecialchars(strtolower($user["firstname"] . " " . $user["lastname"])) ?>"
                                data-role="<?= htmlspecialchars(strtolower($user["user_role"])) ?>"
                                data-status="<?= htmlspecialchars(strtolower($user["status"])) ?>"
                                data-date="<?= htmlspecialchars(strtolower(date('M d, Y', strtotime($user["created_date"])))) ?>">
                                <td width="5%"><?= $count++ ?></td>
                                <td width="20%" class="user-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-user-circle text-secondary"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($user["lastname"] . ", " . $user["firstname"]) ?></strong>
                                            <?php if (!empty($user["middlename"])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($user["middlename"]) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-<?=
                                                            ($user["user_role"] == 'TEACHER') ? 'info' : (($user["user_role"] == 'PARENT') ? 'primary' : 'secondary')
                                                            ?>">
                                        <?= htmlspecialchars($user["user_role"]) ?>
                                    </span>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-<?= ($user["status"] == 'Active') ? 'success' : 'secondary' ?>">
                                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                                        <?= htmlspecialchars($user["status"] ?? 'Inactive') ?>
                                    </span>
                                </td>
                                <td width="20%">
                                    <small><?= date('M d, Y', strtotime($user["created_date"])) ?></small>
                                </td>
                                <td width="25%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="index.php?page=contents/usersProfile&user_id=<?= $user["user_id"] ?>"
                                            class="btn btn-sm btn-info" title="View Profile">
                                            <i class="fa-solid fa-eye me-1"></i> View
                                        </a>
                                        <form class="status-form">
                                            <select name="status" class="status-select form-select form-select-sm">
                                                <option value="">Change Status</option>
                                                <option value="Active" <?= ($user["status"] === "Active") ? "selected" : "" ?>>
                                                    Active
                                                </option>
                                                <option value="Inactive" <?= ($user["status"] === "Inactive") ? "selected" : "" ?>>
                                                    Inactive
                                                </option>
                                            </select>
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No users found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>User Statistics</h5>
                    <div class="row text-center">
                        <?php
                        $teacherCount = array_filter($users, fn($u) => $u['user_role'] === 'TEACHER');
                        $parentCount = array_filter($users, fn($u) => $u['user_role'] === 'PARENT');
                        $activeCount = array_filter($users, fn($u) => $u['status'] === 'Active');
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($users) ?></h3>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-info mb-1"><?= count($teacherCount) ?></h3>
                                <small class="text-muted">Teachers</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($parentCount) ?></h3>
                                <small class="text-muted">Parents</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-success mb-1"><?= count($activeCount) ?></h3>
                                <small class="text-muted">Active Users</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adding account modal -->
<div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="AddNewAccountLabel">
                    <i class="fa-solid fa-user-plus me-2"></i>Create New User Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="Account-form" method="post">
                    <!-- Personal Information -->
                    <div class="col-md-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lastName" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="firstName" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control" name="middleName">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Suffix</label>
                        <select class="form-select" name="suffix">
                            <option value="" selected>Select suffix (optional)</option>
                            <option value="Jr">Jr</option>
                            <option value="Sr">Sr</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                        </select>
                    </div>

                    <!-- Role and Gender -->
                    <div class="col-md-3">
                        <label class="form-label">User Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="user_role" required>
                            <option value="" disabled selected>Select User Role</option>
                            <option value="TEACHER">Teacher</option>
                            <option value="PARENT">Parent</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" name="gender" required>
                            <option value="" disabled selected>Select</option>
                            <option value="MALE">Male</option>
                            <option value="FEMALE">Female</option>
                        </select>
                    </div>

                    <!-- Contact Information -->
                    <div class="col-md-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="contact" required>
                    </div>

                    <!-- Account Credentials -->
                    <div class="col-md-4">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="passwordField" required>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="cpassword" required>
                    </div>

                    <!-- Form Submission -->
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const userRows = document.querySelectorAll('.user-row');
        const userTableBody = document.getElementById('userTableBody');
        const noResultsDiv = document.getElementById('noResults');

        // Search and filter functionality
        function filterUsers() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filterValue = categoryFilter.value.toLowerCase();

            let visibleCount = 0;

            userRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');
                const date = row.getAttribute('data-date');

                let matchesSearch = true;
                let matchesCategory = true;

                // Apply search filter
                if (searchTerm) {
                    matchesSearch = name.includes(searchTerm) ||
                        role.includes(searchTerm) ||
                        status.includes(searchTerm) ||
                        date.includes(searchTerm);
                }

                // Apply category filter
                if (filterValue) {
                    matchesCategory = role.includes(filterValue);
                }

                // Show/hide row based on filters
                if (matchesSearch && matchesCategory) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0) {
                userTableBody.style.display = 'none';
                noResultsDiv.classList.remove('d-none');
            } else {
                userTableBody.style.display = '';
                noResultsDiv.classList.add('d-none');
            }

            // Update row numbers
            updateRowNumbers();
        }

        // Function to update row numbers
        function updateRowNumbers() {
            let counter = 1;
            userRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const firstCell = row.querySelector('td:first-child');
                    if (firstCell) {
                        firstCell.textContent = counter++;
                    }
                }
            });
        }

        // Event listeners
        searchInput.addEventListener('input', filterUsers);
        categoryFilter.addEventListener('change', filterUsers);

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            categoryFilter.value = '';
            filterUsers();
            searchInput.focus();
        });

        // Add Enter key support for search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterUsers();
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
        filterUsers();
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

    #clearSearch:hover {
        background-color: #e9ecef;
    }

    .status-select-wrapper {
        min-width: 150px;
    }

    .status-select {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        height: 32px;
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>