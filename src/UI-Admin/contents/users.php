<?php
require_once __DIR__ . '/../../../tupperware.php';

// Redirect if not authorized
$result = checkURI('admin', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

// Get filters
$role = $_POST['role'] ?? '';
$sy = $_POST['school_year'] ?? '';
$search = $_POST['search'] ?? '';

// Pagination
$limit = 25; // rows per page
$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$offset = ($page - 1) * $limit;


$statistic = "SELECT 
    COUNT(*) AS total_users,
    SUM(CASE WHEN user_role='TEACHER' THEN 1 ELSE 0 END) AS total_teachers,
    SUM(CASE WHEN user_role='PARENT' THEN 1 ELSE 0 END) AS total_parents,
    SUM(CASE WHEN status='Active' THEN 1 ELSE 0 END) AS total_active
FROM users u
WHERE 1";

$paramsSTT = [];

if ($role) {
    $statistic .= " AND u.user_role = ?";
    $paramsSTT[] = $role;
}

if ($sy) {
    $statistic .= " AND u.user_id IN (
        SELECT DISTINCT COALESCE(e.adviser_id, st.guardian_id)
        FROM enrolment e
        LEFT JOIN student st ON st.student_id = e.student_id
        WHERE e.school_year_id = ?
    )";
    $paramsSTT[] = $sy;
}

if ($search) {
    $statistic .= " AND (
        u.firstname LIKE ?
        OR u.lastname LIKE ?
    )";
    $paramsSTT[] = "%$search%";
    $paramsSTT[] = "%$search%";
}
if ($role) {
    $statistic .= " AND u.user_role = ?";
    $paramsSTT[] = $role;
}

$query = "SELECT u.*, 
            GROUP_CONCAT(DISTINCT s.school_year_name ORDER BY s.school_year_name) AS school_years
          FROM users u
          LEFT JOIN enrolment e ON e.adviser_id = u.user_id
          LEFT JOIN school_year s ON e.school_year_id = s.school_year_id
          LEFT JOIN student st ON st.guardian_id = u.user_id
          WHERE 1";

$params = [];

if ($role) {
    $query .= " AND u.user_role = ?";
    $params[] = $role;
}

if ($sy) {
    $query .= " AND (st.student_id IN (SELECT student_id FROM enrolment WHERE school_year_id = ?)
                OR u.user_id IN (SELECT adviser_id FROM enrolment WHERE school_year_id = ?))";
    $params[] = $sy;
    $params[] = $sy;
}

if ($search) {
    $query .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.user_role LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " GROUP BY u.user_id ORDER BY u.created_date DESC";

$countQuery = "SELECT COUNT(*) as total_count FROM (" . $query . ") AS temp";
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$query .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['ajax'])):
    $stmt2 = $pdo->prepare($statistic);
    $stmt2->execute($paramsSTT);
    $stat = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($users):
        ob_start();
        $count = $offset + 1;
        foreach ($users as $user):
            $syList = htmlspecialchars($user["school_years"] ?? ''); ?>
            <tr class="user-row"
                data-name="<?= htmlspecialchars(strtolower($user["firstname"] . " " . $user["lastname"])) ?>"
                data-role="<?= htmlspecialchars(strtolower($user["user_role"])) ?>"
                data-status="<?= htmlspecialchars(strtolower($user["status"])) ?>"
                data-date="<?= htmlspecialchars(strtolower(date('M d, Y', strtotime($user["created_date"])))) ?>"
                data-sy="<?= $syList ?>">
                <td width="1rem"><?= $count++ ?></td>
                <td style="white-space: wrap !important;" class="user-name">
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
                <td width="2.5rem">
                    <span class="badge bg-<?= ($user["user_role"] == 'TEACHER') ? 'info' : (($user["user_role"] == 'PARENT') ? 'primary' : 'secondary') ?>">
                        <?= htmlspecialchars($user["user_role"]) ?>
                    </span>
                </td>
                <td width="">
                    <span class="badge bg-<?= ($user["status"] == 'Active') ? 'success' : 'secondary' ?>">
                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                        <?= htmlspecialchars($user["status"] ?? 'Inactive') ?>
                    </span>
                </td>
                <td width=""><small><?= date('M d, Y', strtotime($user["created_date"])) ?></small></td>
                <td width="">
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="index.php?page=contents/usersProfile&user_id=<?= $user["user_id"] ?>"
                            class="btn btn-sm btn-info" title="View Profile">
                            <i class="fa-solid fa-eye me-1"></i> View
                        </a>
                        <form class="status-form">
                            <select name="status" class="status-select form-select form-select-sm">
                                <option value="">Change Status</option>
                                <option value="Active" <?= ($user["status"] === "Active") ? "selected" : "" ?>>Active</option>
                                <option value="Inactive" <?= ($user["status"] === "Inactive") ? "selected" : "" ?>>Inactive</option>
                            </select>
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="6">
                <div class="d-flex justify-content-between">
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <div>
                        <?php if ($page > 1): ?>
                            <button class="btn btn-sm btn-secondary" onclick="fetchUsers(<?= $page - 1 ?>)">Prev</button>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <button class="btn btn-sm btn-secondary" onclick="fetchUsers(<?= $page + 1 ?>)">Next</button>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    <?php else: ?>

<?php endif;
    $html = ob_get_clean();

    echo json_encode([
        'html'        => $html,
        'hasData'     => !empty($users),
        'stat'       => $stat
    ]);
    exit;
endif;
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
                        placeholder="Search by name...">
                </div>
                <div class="col-md-8 d-flex gap-2 align-items-center mt-2">
                    <select id="roleFilter" name="role" class="form-select" style="max-width: 200px;">
                        <option value="">All User Roles</option>
                        <?php
                        $catStmt = $pdo->query("SELECT DISTINCT user_role FROM users ORDER BY user_role ASC");
                        while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= htmlspecialchars($cat['user_role']) ?>">
                                <?= htmlspecialchars($cat['user_role']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select id="syFilter" name="school_year" class="form-select" style="max-width: 200px;">
                        <?php
                        // Get all SYs, order active first
                        $catStmt = $pdo->query("
                            SELECT school_year_id, school_year_name, school_year_status
                            FROM school_year
                            ORDER BY 
                                CASE WHEN school_year_status = 'Active' THEN 0 ELSE 1 END,
                                school_year_name ASC
                        ");

                        $activeSyId = null;
                        $yr['school_year_id'] = null;
                        $yr['school_year_name'] = null;
                        $schoolYears = [];
                        while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($cat['school_year_status'] === 'Active' && $activeSyId === null) {
                                $activeSyId = $cat['school_year_id'];
                                $yr['school_year_id'] = $cat['school_year_id'];
                                $yr['school_year_name'] = $cat['school_year_name'];
                            }
                            $schoolYears[] = $cat;
                        }
                        ?>
                        <option value="">--- active at ---</option>

                        <?php foreach ($schoolYears as $sy): ?>
                            <option value="<?= htmlspecialchars($sy['school_year_id']) ?>"
                                <?= ($sy['school_year_id'] == $activeSyId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sy['school_year_name']) ?>
                                <?= $sy['school_year_status'] === 'Active' ? ' (Active)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#AddNewAccount" id="add_new">
                <i class="fa-solid fa-user-plus me-2"></i> New Account
            </button>
        </div>
    </div>


    <!-- Scrollable Body -->
    <div class="table-responsive" style="max-height: 500px; overflow: auto; scrollbar-width: thin;">
        <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem; width: 100%;">
            <thead class="table-light">
                <tr>
                    <th style="width: 1rem;">#</th>
                    <th>Name</th>
                    <th style="width: 5rem;">Role</th>
                    <th style="width: 6rem;">Status</th>
                    <th style="width: 6rem;">Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="userTableBody">

            </tbody>
        </table>
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
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1" id="tu">0</h3>
                                <small style="color: black;">Total Users</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1" id="tt">0</h3>
                                <small style="color: black;">Teachers</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1" id="tp">0</h3>
                                <small style="color: black;">Parents</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1" id="ta">0</h3>
                                <small style="color: black;">Active Users</small>
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
    let currentPage = 1;

    function fetchUsers(page = 1) {
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const syFilter = document.getElementById('syFilter');
        const userTableBody = document.getElementById('userTableBody');
        const noResults = document.getElementById('noResults');

        // Show loading
        userTableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>Loading users...</div>
            </td>
        </tr>`;
        noResults.classList.add('d-none');

        const formData = new FormData();
        formData.append('action', 'fetch_users');
        formData.append('search', searchInput.value.trim());
        formData.append('role', roleFilter.value);
        formData.append('school_year', syFilter.value);
        formData.append('ajax', 1);
        formData.append('page', page);

        fetch('contents/users.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                userTableBody.innerHTML = data.html || '';
                noResults.classList.toggle('d-none', data.hasData);

                document.getElementById('tu').textContent = data.stat.total_users ?? 0;
                document.getElementById('tt').textContent = data.stat.total_teachers ?? 0;
                document.getElementById('tp').textContent = data.stat.total_parents ?? 0;
                document.getElementById('ta').textContent = data.stat.total_active ?? 0;

                currentPage = data.currentPage;
            })
            .catch(err => {
                console.error(err);
                userTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    Failed to load data
                </td>
            </tr>`;
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const syFilter = document.getElementById('syFilter');

        searchInput.addEventListener('input', () => {
            currentPage = 1;
            fetchUsers();
        });
        roleFilter.addEventListener('change', () => {
            currentPage = 1;
            fetchUsers();
        });
        syFilter.addEventListener('change', () => {
            currentPage = 1;
            fetchUsers();
        });
        searchInput.addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                currentPage = 1;
                fetchUsers();
            }
        });

        fetchUsers(currentPage);
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