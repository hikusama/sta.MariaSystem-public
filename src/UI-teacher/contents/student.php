<?php
require_once __DIR__ . '/../../../tupperware.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Teacher check ---
$teacher_id = $_SESSION['user_id'] ?? null;
if (!$teacher_id) {
    http_response_code(403);
    exit;
}

// --- Pagination ---
$limit = 10;
$page = max(1, (int)($_POST['page'] ?? 1));
$offset = ($page - 1) * $limit;

// --- Filters ---
$search = trim($_POST['search'] ?? '');
$grade  = trim($_POST['grade'] ?? '');
$status = trim($_POST['status'] ?? '');
$sy     = trim($_POST['school_year'] ?? '');

// --- Badge map ---
$statusMap = [
    'active' => 'success',
    'pending' => 'warning',
    'not_active' => 'secondary',
    'transferred_out' => 'secondary',
    'dropped' => 'danger',
    'rejected' => 'purple',
];

// --- Build WHERE clause ---
$where = ["e.adviser_id = :teacher_id"];
$params = [':teacher_id' => $teacher_id];

if ($sy) {
    $where[] = "e.school_year_id = :sy";
    $params[':sy'] = $sy;
}

if ($grade) {
    $where[] = "LOWER(s.gradeLevel) = :grade";
    $params[':grade'] = strtolower($grade);
}

if ($status) {
    $where[] = "LOWER(s.enrolment_status) = :status";
    $params[':status'] = strtolower($status);
}

if ($search) {
    $where[] = "(s.fname LIKE :search OR s.lname LIKE :search OR s.lrn LIKE :search)";
    $params[':search'] = "%$search%";
}

$whereSql = implode(" AND ", $where);

// --- Count total ---
$countSql = "SELECT COUNT(DISTINCT s.student_id)
             FROM enrolment e
             JOIN student s ON s.student_id = e.student_id
             WHERE $whereSql";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));

// --- Fetch students ---
$sql = "SELECT DISTINCT s.*, e.section_name, e.school_year_id, sy.school_year_name
        FROM enrolment e
        JOIN student s ON s.student_id = e.student_id
        JOIN school_year sy ON sy.school_year_id = e.school_year_id
        WHERE $whereSql
        ORDER BY s.lname ASC, s.fname ASC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- AJAX ---
if (isset($_POST['ajax'])) {
    ob_start();
    $count = $offset + 1;
    if ($students):
        foreach ($students as $s):
            $statusKey = strtolower($s['enrolment_status'] ?? 'pending');
            $badgeClass = $statusMap[$statusKey] ?? 'secondary';
?>
            <tr data-name="<?= htmlspecialchars(strtolower($s['lname'] . ' ' . $s['fname'])) ?>"
                data-grade="<?= htmlspecialchars(strtolower($s['gradeLevel'])) ?>"
                data-status="<?= $statusKey ?>">
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($s['lname'] . ', ' . $s['fname']) ?></td>
                <td><?= htmlspecialchars($s['gradeLevel']) ?></td>
                <td><?= htmlspecialchars($s['section_name']) ?></td>
                <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($statusKey) ?></span></td>
                <td><?= date('M d, Y', strtotime($s['enrolled_date'])) ?></td>
                <td><a href="index.php?page=contents/profile&student_id=<?= $s['student_id'] ?>" class="btn btn-sm btn-info">Profile</a></td>
            </tr>
        <?php
        endforeach;
        ?>
        <tr>
            <td colspan="6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <div>
                        <?php if ($page > 1): ?>
                            <button class="btn btn-sm btn-secondary" onclick="fetchStudents(<?= $page - 1 ?>)">Prev</button>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <button class="btn btn-sm btn-secondary" onclick="fetchStudents(<?= $page + 1 ?>)">Next</button>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    <?php
    else:
    ?>
        <tr>
            <td colspan="7" class="text-center py-3">No students found.</td>
        </tr>
<?php
    endif;
    $rowsHtml = ob_get_clean();
    echo json_encode([
        'hasData' => !empty($students),
        'html' => $rowsHtml,
        'pagination' => ['current' => $page, 'total' => $totalPages]
    ]);
    exit;
}
?>

<!-- ==================== HTML Page ==================== -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Student Management</h4>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search name or LRN">
    </div>
    <div class="col-md-2">
        <select id="statusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="pending">Pending</option>
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
        <select id="syFilter" class="form-select">
            <option value="">--- All my student ---</option>
            <?php
            $syStmt = $pdo->query("SELECT school_year_id, school_year_name, school_year_status FROM school_year ORDER BY CASE WHEN school_year_status='Active' THEN 0 ELSE 1 END, school_year_name ASC");
            while ($syRow = $syStmt->fetch(PDO::FETCH_ASSOC)):
                $selected = ($syRow['school_year_status'] == 'Active') ? 'selected' : '';
            ?>
                <option value="<?= $syRow['school_year_id'] ?>" <?= $selected ?>><?= htmlspecialchars($syRow['school_year_name']) ?> <?= ($syRow['school_year_status'] == 'Active') ? '(Active)' : '' ?></option>
            <?php endwhile; ?>
        </select>
    </div>
</div>

<div class="table-responsive" style="max-height:500px; overflow-y:auto;">
    <table class="table table-bordered table-hover table-sm">
        <thead class="table-light position-sticky top-0">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Status</th>
                <th>Enrolled Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="studentTableBody"></tbody>
    </table>
</div>

<script>
    let currentPage = 1;
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const gradeFilter = document.getElementById('gradeFilter');
    const syFilter = document.getElementById('syFilter');
    const tableBody = document.getElementById('studentTableBody');

    function fetchStudents(page = 1) {
        currentPage = page;
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-3">Loading...</td></tr>`;
        const formData = new FormData();
        formData.append('ajax', 1);
        formData.append('search', searchInput.value);
        formData.append('status', statusFilter.value);
        formData.append('grade', gradeFilter.value);
        formData.append('school_year', syFilter.value);
        formData.append('page', page);

        fetch('contents/student.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = data.html;
            })
            .catch(err => {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-danger text-center">Failed to load data</td></tr>`;
                console.error(err);
            });
    }

    searchInput.addEventListener('input', () => fetchStudents(1));
    statusFilter.addEventListener('change', () => fetchStudents(1));
    gradeFilter.addEventListener('change', () => fetchStudents(1));
    syFilter.addEventListener('change', () => fetchStudents(1));

    document.addEventListener('DOMContentLoaded', () => fetchStudents(currentPage));
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
</style>