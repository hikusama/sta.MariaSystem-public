<?php
require_once __DIR__ . '/../../../tupperware.php';

$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

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
    'transferred_in' => 'info',
    'transferred_out' => 'secondary',
    'dropped' => 'danger',
    'rejected' => 'danger',
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
$sql = "SELECT DISTINCT s.*, e.section_name, sy.school_year_name
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

    if (!empty($students)) {
        foreach ($students as $row) {

            $sexColor = strtolower($row['sex']) === 'male' ? 'info' : 'warning';
            $statusColor = $row['enrolment_status'] === 'active' ? 'success' : 'secondary';

            echo "
            <tr class='student-row' 
                     data-school_year_name='" . htmlspecialchars($row['school_year_name']) . "'
                     data-id='" . htmlspecialchars($row['student_id']) . "'
                     data-lrn='" . htmlspecialchars(strtolower($row['lrn'])) . "'
                     data-fname='" . htmlspecialchars(strtolower($row['fname'])) . "'
                     data-lname='" . htmlspecialchars(strtolower($row['lname'])) . "'
                     data-grade='" . htmlspecialchars(strtolower($row['gradeLevel'])) . "'
                     data-sex='" . htmlspecialchars(strtolower($row['sex'])) . "'
                     data-status='" . htmlspecialchars(strtolower($row['enrolment_status'])) . "'>
                    <td>
                        <div class='d-flex align-items-center justify-content-center'>
                            <div class='avatar-placeholder me-2'>
                                <i class='fa-solid fa-id-card text-info'></i>
                            </div>
                            <div><strong>" . htmlspecialchars($row['lrn']) . "</strong></div>
                        </div>
                    </td>
                    <td>" . htmlspecialchars($row['fname']) . "</td>
                    <td>" . htmlspecialchars($row['mname']) . "</td>
                    <td>" . htmlspecialchars($row['lname']) . "</td>
                    <td><span class='badge bg-primary'>" . htmlspecialchars($row['gradeLevel']) . "</span></td>
                    <td >
                        <span class='badge bg-" . (strtolower($row['sex']) == 'male' ? 'info' : 'warning') . "'>" . htmlspecialchars($row['sex']) . "
                        </span>
                    </td>
                    <td><span class='badge bg-" . ($row['enrolment_status'] == 'active' ? 'success' : 'secondary') . "'>
                        <i class='fa-solid fa-circle fa-xs me-1'></i>" . htmlspecialchars($row['enrolment_status']) . "</span></td>
                  </tr>";
        } ?>

        <!-- pagination row -->
        <tr>
            <td colspan="7">
                <div class="d-flex justify-content-between align-items-center pt-3">
                    <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
                    <div class="btn-group">
                        <?php if ($page > 1): ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="fetchStudents(<?= $page - 1 ?>)">
                                <i class="fa-solid fa-chevron-left me-1"></i>Prev
                            </button>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="fetchStudents(<?= $page + 1 ?>)">
                                Next<i class="fa-solid fa-chevron-right ms-1"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    <?php
    } else {
    ?>
        <tr>
            <td colspan="7" class="text-center py-3">No students found.</td>
        </tr>
<?php
    }
    $rowsHtml = ob_get_clean();
    echo json_encode([
        'hasData' => !empty($students),
        'html' => $rowsHtml,
        'pagination' => ['current' => $page, 'total' => $totalPages]
    ]);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SF9</title>
    <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url() ?>assets/fontawesome/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: "Segoe UI", sans-serif;
        }

        #pagination {
            margin-top: 1rem;
        }

        .scroll-feedback {
            height: 80vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .table-container-wrapper {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background: linear-gradient(to bottom, #f8f9fa, #f1f3f5);
            font-weight: 700;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 0.75rem !important;
        }

        .table tbody tr {
            transition: all 0.15s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .student-row:hover {
            background-color: #f8f9fa;
            cursor: pointer;
            box-shadow: inset 0 0 0 1px rgba(0, 123, 255, 0.1);
        }

        .table td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
        }

        .badge {
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            padding: 0.6rem 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-control::placeholder {
            color: #adb5bd;
            font-weight: 500;
        }

        .btn-group .btn-outline-secondary {
            border-color: #dee2e6;
            color: #495057;
            transition: all 0.2s ease;
        }

        .btn-group .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #495057;
            color: #212529;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        h4 {
            color: #212529;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        h4 i {
            color: #007bff;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-3">
        <h4><i class="fa-solid fa-graduation-cap me-2"></i>SF9 - Learner's Progress</h4>

        <div class="row mb-3">
            <div class="col-md-4">
                <input class="form-control" id="searchInput" placeholder="Search name or LRN">
            </div>

            <div class="col-md-3">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="not_active">Not Active</option>
                    <option value="transferred_in">Transferred In</option>
                    <option value="transferred_out">Transferred Out</option>
                    <option value="dropped">Dropped</option>
                </select>
            </div>

            <div class="col-md-3">
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

        <table class="table table-bordered table-hover table-sm">
            <thead>
                <tr>
                    <th>LRN</th>
                    <th>First</th>
                    <th>Middle</th>
                    <th>Last</th>
                    <th>Grade</th>
                    <th>Sex</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="studentTable"></tbody>
        </table>

        <div id="pagination" class="text-center"></div>
    </div>
    <script>
        let currentPage = 1;

        function fetchStudents(page = 1) {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const sy = document.getElementById('syFilter').value;
            const tbody = document.getElementById('studentTable');

            tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary"></div>
            </td>
        </tr>`;

            const formData = new FormData();
            formData.append('ajax', 1);
            formData.append('search', search);
            formData.append('status', status);
            formData.append('school_year', sy);
            formData.append('page', page);

            fetch('contents/sf9.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = data.html || '';
                    currentPage = page;
                    attachRowClick();
                })
                .catch(() => {
                    tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
                    Failed to load students
                </td>
            </tr>`;
                });
        }

        function attachRowClick() {
            document.querySelectorAll('tbody tr').forEach(row => {
                row.onclick = () => {
                    window.location.href =
                        `<?= base_url() ?>/src/UI-teacher/contents/schoolform9.php?student_id=${row.dataset.id}&school_year_name=${row.dataset.school_year_name}`;
                };
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            ['searchInput', 'statusFilter', 'syFilter'].forEach(id => {
                document.getElementById(id).addEventListener('input', () => {
                    currentPage = 1;
                    fetchStudents();
                });
                document.getElementById(id).addEventListener('change', () => {
                    currentPage = 1;
                    fetchStudents();
                });
            });

            fetchStudents(currentPage);
        });
    </script>



</body>

</html>