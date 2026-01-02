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
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {

    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $sy     = trim($_GET['sy'] ?? '');

    $where = [];
    $params = [];
    $types = '';

    /* teacher restriction (adviser) */
    $where[] = "c.adviser_id = ?";
    $params[] = $teacher_id;
    $types .= 'i';

    /* school year */
    if ($sy) {
        $where[] = "c.sy_id = ?";
        $params[] = $sy;
        $types .= 'i';
    }

    /* status */
    if ($status && !in_array($status, ['pending', 'rejected'])) {
        $where[] = "s.enrolment_status = ?";
        $params[] = $status;
        $types .= 's';
    }

    /* search */
    if ($search) {
        $where[] = "(s.lrn LIKE ? OR s.fname LIKE ? OR s.mname LIKE ? OR s.lname LIKE ?)";
        $s = "%$search%";
        array_push($params, $s, $s, $s, $s,);
        $types .= 'ssss';
    }

    $whereSQL = " WHERE " . implode(" AND ", $where);

    /* COUNT */
    $countSQL = "
        SELECT COUNT(*) total
        FROM student s
        INNER JOIN classes c 
            ON c.grade_level = s.gradeLevel
        $whereSQL
    ";

    $stmt = $conn->prepare($countSQL);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    /* DATA */
    $sql = "
        SELECT s.*, c.grade_level
        FROM student s
        INNER JOIN classes c 
            ON c.grade_level = s.gradeLevel
        $whereSQL
        ORDER BY s.lname, s.fname
        LIMIT $perPage OFFSET $offset
    ";

    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows) {
        while ($row = $res->fetch_assoc()) {
            echo "<tr class='student-row' 
                                         data-id='" . htmlspecialchars($row['student_id']) . "'
                                         data-lrn='" . htmlspecialchars(strtolower($row['lrn'])) . "'
                                         data-fname='" . htmlspecialchars(strtolower($row['fname'])) . "'
                                         data-lname='" . htmlspecialchars(strtolower($row['lname'])) . "'
                                         data-grade='" . htmlspecialchars(strtolower($row['gradeLevel'])) . "'
                                         data-sex='" . htmlspecialchars(strtolower($row['sex'])) . "'
                                         data-status='" . htmlspecialchars(strtolower($row['enrolment_status'])) . "'>
                                        <td width='15%'>
                                            <div class='d-flex align-items-center justify-content-center'>
                                                <div class='avatar-placeholder me-2'>
                                                    <i class='fa-solid fa-id-card text-info'></i>
                                                </div>
                                                <div>
                                                    <strong>" . htmlspecialchars($row['lrn']) . "</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td width='15%'>" . htmlspecialchars($row['fname']) . "</td>
                                        <td width='15%'>" . htmlspecialchars($row['mname']) . "</td>
                                        <td width='15%'>" . htmlspecialchars($row['lname']) . "</td>
                                        <td width='10%'>
                                            <span class='badge bg-primary'>" . htmlspecialchars($row['gradeLevel']) . "</span>
                                        </td>
                                        <td width='10%'>
                                            <span class='badge bg-" . (strtolower($row['sex']) == 'male' ? 'info' : 'warning') . "'>
                                                " . htmlspecialchars($row['sex']) . "
                                            </span>
                                        </td>
                                        <td width='20%'>
                                            <span class='badge bg-" . ($row['enrolment_status'] == 'active' ? 'success' : 'secondary') . "'>
                                                <i class='fa-solid fa-circle fa-xs me-1'></i>
                                                " . htmlspecialchars($row['enrolment_status']) . "
                                            </span>
                                        </td>
                                      </tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='text-center py-3'>No students found.</td></tr>";
    }

    /* Pagination info */
    echo "<tr id='pagination-info' class='d-none nss'
            data-total='$total'
            data-page='$page'
            data-perpage='$perPage'></tr>";

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SF10 - Learner's Permanent Academic Record</title>
    <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url() ?>assets/fontawesome/css/all.min.css">
    <style>
        #pagination{
            margin-top: 1rem;
        }
        body {
            background-color: #f5f7fa;
            font-family: "Segoe UI", sans-serif;
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
            background-color: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
            cursor: pointer;
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

        .scroll-feedback::-webkit-scrollbar {
            width: 8px;
        }

        .scroll-feedback::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .scroll-feedback::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .scroll-feedback::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        @media (max-width: 768px) {
            .scroll-feedback {
                height: auto;
                overflow: visible;
            }

            .table-responsive {
                font-size: 0.75rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-3">
        <h4><i class="fa-solid fa-file-certificate me-2"></i>SF10 - Learner's Permanent Academic Record</h4>

        <div class="row mb-3">
            <div class="col-md-4">
                <input class="form-control" id="searchInput" placeholder="Search LRN, Name...">
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
                <select id="syFilter" class="form-select">
                    <option value="">All School Year</option>
                    <?php
                    $syq = $conn->query("SELECT school_year_id, school_year_name FROM school_year");
                    while ($sy = $syq->fetch_assoc()):
                    ?>
                        <option value="<?= $sy['school_year_id'] ?>"><?= $sy['school_year_name'] ?></option>
                    <?php endwhile; ?>
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
        document.addEventListener('DOMContentLoaded', () => {
            const studentTable = document.getElementById('studentTable');
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const syFilter = document.getElementById('syFilter');
            const pagination = document.getElementById('pagination');
            let page = 1;

            async function loadStudents() {
                const params = new URLSearchParams({
                    ajax: 1,
                    page,
                    search: searchInput.value,
                    status: statusFilter.value,
                    sy: syFilter.value
                });
                const response = await fetch(`contents/sf10.php?${params}`);
                const html = await response.text();
                studentTable.innerHTML = html;
                updatePagination();
                attachRowClick();
            }

            function attachRowClick() {
                document.querySelectorAll('.student-row').forEach(row => {
                    row.onclick = () => {
                        window.location.href = "<?= BASE_FR ?>/src/UI-teacher/contents/schoolform10.php?student_id=" + row.dataset.id;
                    };
                });
            }

            function updatePagination() {
                const info = document.getElementById('pagination-info');
                if (!info) {
                    pagination.innerHTML = '';
                    return;
                }
                const total = parseInt(info.dataset.total);
                const perPage = parseInt(info.dataset.perpage);
                const pages = Math.ceil(total / perPage);
                pagination.innerHTML = '';
                for (let i = 1; i <= pages; i++) {
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-sm btn-outline-primary mx-1' + (i === page ? ' active' : '');
                    btn.textContent = i;
                    btn.onclick = () => {
                        page = i;
                        loadStudents();
                    };
                    pagination.appendChild(btn);
                }
            }

            searchInput.oninput = () => {
                page = 1;
                loadStudents();
            };
            statusFilter.onchange = () => {
                page = 1;
                loadStudents();
            };
            syFilter.onchange = () => {
                page = 1;
                loadStudents();
            };

            loadStudents();
        });
    </script>
</body>

</html>