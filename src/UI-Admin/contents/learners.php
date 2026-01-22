<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

$search = trim($_POST['search'] ?? '');
$status = trim($_POST['status'] ?? '');
$grade  = trim($_POST['grade'] ?? '');
$sy     = trim($_POST['school_year'] ?? '');
$page   = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

$statusMap = [
    'active'          => ['success', 'Enrolled'],
    'pending'         => ['plo', 'Pending'],
    'transferred_in'  => ['info', 'Transferred In'],
    'transferred_out' => ['primary', 'Transferred Out'],
    'transferred'     => ['secondary', 'Transferred'],
    'not_active'      => ['dark', 'Not Active'],
    'dropped'         => ['danger', 'Dropped'],
    'rejected'        => ['purple', 'Rejected']
];

if (isset($_POST['ajax'])) {

    $where = [];
    $params = [];

    if ($sy) {
        $where[] = "student.student_id IN (SELECT e.student_id FROM enrolment AS e WHERE e.school_year_id = ?)";
        $params[] = $sy;
    }

    if ($status) {
        $where[] = "LOWER(student.enrolment_status) = ?";
        $params[] = strtolower($status);
    }

    if ($grade) {
        $where[] = "LOWER(student.gradeLevel) = ?";
        $params[] = strtolower($grade);
    }

    if ($search) {
        $where[] = "(student.fname LIKE ? OR student.lname LIKE ? OR student.lrn LIKE ? OR users.firstname LIKE ? OR users.lastname LIKE ?)";
        $s = "%$search%";
        array_push($params, $s, $s, $s, $s, $s);
    }

    // Base query - include school_year to get the name
    $sql = "SELECT DISTINCT student.*, 
                   users.firstname AS parentFirstname, 
                   users.lastname AS parentLastname, 
                   users.middlename AS parentMiddle,
                   school_year.school_year_name
            FROM student
            JOIN users ON users.user_id = student.guardian_id
            LEFT JOIN enrolment ON student.student_id = enrolment.student_id
            LEFT JOIN school_year ON enrolment.school_year_id = school_year.school_year_id";

    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY student.lname ASC";

    // Total rows for pagination
    $countQuery = "SELECT COUNT(*) FROM ($sql) AS total_count";
    $stmtCount = $pdo->prepare($countQuery);
    $stmtCount->execute($params);
    $totalRows = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // Add LIMIT/OFFSET
    $sql .= " LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows):
        $count = $offset + 1;
        foreach ($rows as $user):
            $currentStatus = strtolower($user['enrolment_status'] ?? 'pending');
            $badgeClass = $statusMap[$currentStatus][0] ?? 'secondary';
            $label = $statusMap[$currentStatus][1] ?? ucfirst($currentStatus);
?>
            <tr class="learner-row"
                data-status="<?= htmlspecialchars($currentStatus) ?>"
                data-grade="<?= htmlspecialchars(strtolower($user['gradeLevel'] ?? '')) ?>"
                data-name="<?= htmlspecialchars(strtolower($user['lname'] . ' ' . $user['fname'] . ' ' . ($user['mname'] ?? ''))) ?>"
                data-lrn="<?= htmlspecialchars(strtolower($user['lrn'] ?? '')) ?>"
                data-parent="<?= htmlspecialchars(strtolower($user['parentLastname'] . ' ' . $user['parentFirstname'])) ?>">
                <td width="5%"><?= $count++ ?></td>
                <td width="10%"><code><?= htmlspecialchars($user["lrn"]) ?></code></td>
                <td width="20%">
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
                <td width="20%">
                    <div class="d-flex align-items-center">
                        <div class="avatar-placeholder-small me-2">
                            <i class="fa-solid fa-user text-secondary"></i>
                        </div>
                        <div>
                            <small>
                                <strong><?= htmlspecialchars($user["parentLastname"] . ", " . $user["parentFirstname"]) ?></strong>
                                <?php if (!empty($user["parentMiddle"])): ?>
                                    <br><?= htmlspecialchars($user["parentMiddle"]) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </td>
                <td width="10%"><span class="badge bg-info"><?= htmlspecialchars($user["gradeLevel"]) ?></span></td>
                <td style="text-align: center;" width="10%"><span style="width: 1.34rem;" class="badge bg-<?= $badgeClass ?>"><i class="fa-solid fa-circle fa-xs me-1"></i></span></td>
                <td width="15%">
                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                        <a href="index.php?page=contents/profile&student_id=<?= htmlspecialchars($user["student_id"]) ?>&school_year_name=<?= htmlspecialchars($user['school_year_name'] ?? '') ?>" class="btn btn-sm btn-info" title="View Profile"><i class="fa-solid fa-user me-1"></i> Profile</a>
                        <form class="status-enrolment-form">
                            <select name="status" class="status-enrolment-select form-select">
                                <option value="">Change Status</option>
                                <?php foreach ($statusMap as $key => $map):
                                    if ($key === 'pending') continue; ?>
                                    <option value="<?= $key ?>" <?= ($currentStatus === $key) ? "selected" : "" ?>><?= $map[1] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="user_id" value="<?= $user['student_id'] ?>">
                        </form>
                    </div>
                </td>
            </tr>
        <?php
        endforeach;
        ?>
        <tr>
            <td colspan="7">
                <div class="d-flex justify-content-between">
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <div>
                        <?php if ($page > 1): ?>
                            <button class="btn btn-sm btn-secondary" onclick="fetchLearners(<?= $page - 1 ?>)">Prev</button>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <button class="btn btn-sm btn-secondary" onclick="fetchLearners(<?= $page + 1 ?>)">Next</button>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    <?php
    else:
    ?>
        <tr>
            <td colspan="7" class="text-center py-3">No learners found.</td>
        </tr>
<?php
    endif;
    exit;
}
?>

<!-- HTML Content -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="fa-solid fa-graduation-cap me-2"></i>Learners Management</h4>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name, LRN, or parent name...">
    </div>
    <div class="col-md-2">
        <select id="statusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="active">Enrolled</option>
            <option value="transferred_in">Transferred in</option>
            <option value="transferred_out">Transferred out</option>
            <option value="not_active">Not Active</option>
            <option value="rejected">Rejected</option>
            <option value="dropped">Dropped</option>
        </select>
    </div>
    <div class="col-md-2">
        <select id="gradeFilter" class="form-select">
            <option value="">All Grades</option>
            <option value="Grade 1">Grade 1</option>
            <option value="Grade 2">Grade 2</option>
            <option value="Grade 3">Grade 3</option>
            <option value="Grade 4">Grade 4</option>
            <option value="Grade 5">Grade 5</option>
            <option value="Grade 6">Grade 6</option>
        </select>
    </div>
    <div class="col-md-2">
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
<div class="d-flex gap-3 mb-3 flex-wrap" id="statusLegend">
    <?php foreach ($statusMap as $key => $map):
        $color = $map[0];
        $label = $map[1];
    ?>
        <div class="d-flex align-items-center gap-1">
            <span class="badge bg-<?= $color ?> rounded-circle" style="width: 12px; height: 12px; display:inline-block;"></span>
            <small><?= htmlspecialchars($label) ?></small>
        </div>
    <?php endforeach; ?>
</div>
<div class="table-container-wrapper p-0">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th width="10%">LRN</th>
                    <th width="20%">Name</th>
                    <th width="20%">Parent/Guardian</th>
                    <th width="10%">Grade</th>
                    <th width="10%">Remarks</th>
                    <th width="15%">Action</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="table-scroll-body">
        <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
            <tbody id="learnersTableBody"></tbody>
        </table>
    </div>
</div>

<script>
    let currentPage = 1;
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const gradeFilter = document.getElementById('gradeFilter');
    const syFilter = document.getElementById('syFilter');
    const tableBody = document.getElementById('learnersTableBody');

    function fetchLearners(page = 1) {
        currentPage = page;
        tableBody.innerHTML = `
        <tr><td colspan="7" class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <div>Loading learners...</div>
        </td></tr>
    `;

        const formData = new FormData();
        formData.append('ajax', 1);
        formData.append('search', searchInput.value.trim());
        formData.append('status', statusFilter.value);
        formData.append('grade', gradeFilter.value);
        formData.append('school_year', syFilter.value);
        formData.append('page', page);

        fetch('contents/learners.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(html => {
                tableBody.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Failed to load data</td></tr>';
            });
    }

    searchInput.addEventListener('input', () => fetchLearners(1));
    statusFilter.addEventListener('change', () => fetchLearners(1));
    gradeFilter.addEventListener('change', () => fetchLearners(1));
    syFilter.addEventListener('change', () => fetchLearners(1));

    document.addEventListener('DOMContentLoaded', () => fetchLearners(currentPage));
</script>



<style>
    .table-scroll-body {
        max-height: 420px;
        overflow-y: auto;
    }

    .bg-plo {
        background-color: #ffa200 !important;
        color: #fff;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
        color: #fff;
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

    .avatar-placeholder-small {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
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
        margin: 0 !important;
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

    .status-enrolment-select {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        height: 32px;
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    code {
        font-size: 0.8rem;
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
</style>