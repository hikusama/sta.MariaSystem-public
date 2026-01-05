<?php
require_once __DIR__ . '/../../../tupperware.php';

$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

$teacher_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "stamariadb");
if ($conn->connect_error) die("Connection failed");

$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

/* ================= AJAX ================= */
if (isset($_POST['ajax'])) {

    $search = trim($_POST['search'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $sy     = trim($_POST['sy'] ?? '');

    $limit = 10;
    $page = max(1, (int)($_POST['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];
    $types = '';

    $where[] = "e.adviser_id = ?";
    $params[] = $teacher_id;
    $types .= 'i';

    if ($sy) {
        $where[] = "e.school_year_id = ?";
        $params[] = $sy;
        $types .= 'i';
    }

    if ($status && !in_array($status, ['pending', 'rejected'])) {
        $where[] = "s.enrolment_status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if ($search) {
        $where[] = "(s.lrn LIKE ? OR s.fname LIKE ? OR s.mname LIKE ? OR s.lname LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like, $like);
        $types .= 'ssss';
    }

    $whereSQL = "WHERE " . implode(" AND ", $where);

    /* ===== COUNT ===== */
    $countSQL = "
        SELECT COUNT(DISTINCT s.student_id) total
        FROM student s
        LEFT JOIN enrolment as e ON s.student_id = e.student_id
        $whereSQL
    ";

    $stmt = $conn->prepare($countSQL);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $totalRows = (int)$stmt->get_result()->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    /* ===== DATA ===== */
    $sql = "
        SELECT s.student_id, s.lrn, s.fname, s.mname, s.lname,
               s.gradeLevel, s.sex, s.enrolment_status,e.adviser_id
        FROM student s
        LEFT JOIN enrolment as e ON s.student_id = e.student_id
        $whereSQL
        ORDER BY s.lname, s.fname
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    ob_start();

    if ($res->num_rows) {
        while ($row = $res->fetch_assoc()) {

            $sexColor = strtolower($row['sex']) === 'male' ? 'info' : 'warning';
            $statusColor = $row['enrolment_status'] === 'active' ? 'success' : 'secondary';

            echo "
            <tr class='student-row' 
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
                <div class="d-flex justify-content-between">
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <div>
                        <?php if ($page > 1): ?>
                            <button class="btn btn-sm btn-secondary"
                                onclick="fetchStudents(<?= $page - 1 ?>)">Prev</button>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <button class="btn btn-sm btn-secondary"
                                onclick="fetchStudents(<?= $page + 1 ?>)">Next</button>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>

    <?php } ?>
    <tr>
        <td colspan="7" class="text-center py-4">No students found</td>
    </tr>
<?php

    $html = ob_get_clean();

    echo json_encode([
        'html' => $html,
        'hasData' => $res->num_rows > 0
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
        }

        .table thead th {
            background: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, .05);
            cursor: pointer;
        }

        .avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f8f9fa;
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

        #pagination button {
            cursor: pointer;
            margin: 0 2px;
        }

        .table-responsive {
            overflow: auto;
            max-height: 500px;
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
            formData.append('sy', sy);
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
            document.querySelectorAll('.student-row').forEach(row => {
                row.onclick = () => {
                    window.location.href =
                        "<?= BASE_FR ?>/src/UI-teacher/contents/schoolform9.php?student_id=" +
                        row.dataset.id;
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