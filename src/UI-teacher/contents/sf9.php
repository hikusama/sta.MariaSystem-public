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

    /* teacher restriction */
    $where[] = "c.adviser_id = ?";
    $params[] = $teacher_id;
    $types .= 'i';

    /* school year */
    if ($sy) {
        $where[] = "c.sy_id = ?";
        $params[] = $sy;
        $types .= 'i';
    }

    /* status (NO pending / rejected) */
    if ($status && !in_array($status, ['pending', 'rejected'])) {
        $where[] = "s.enrolment_status = ?";
        $params[] = $status;
        $types .= 's';
    }

    /* search */
    if ($search) {
        $where[] = "(s.lrn LIKE ? OR s.fname LIKE ? OR s.mname LIKE ? OR s.lname LIKE ?)";
        $s = "%$search%";
        array_push($params, $s, $s, $s, $s);
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
    $stmt->bind_param($types, ...$params);
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
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

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
        }
    } else {
        echo "<tr><td colspan='7' class='text-center py-3'>No students found.</td></tr>";
    }

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
    <title>SF9</title>
    <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url() ?>assets/fontawesome/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: "Segoe UI", sans-serif;
        }
        #pagination{
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
                <select id="syFilter" class="form-select">
                    <option value="">All School Year</option>
                    <?php
                    $syq = $conn->query("SELECT school_year_id, school_year_name FROM school_year");
                    while ($sy = $syq->fetch_assoc()):
                    ?>
                        <option value="<?= $sy['school_year_id'] ?>">
                            <?= $sy['school_year_name'] ?>
                        </option>
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
                try {
                    const response = await fetch(
                        `contents/sf9.php?ajax=1
                &page=${page}
                &search=${encodeURIComponent(searchInput.value)}
                &status=${encodeURIComponent(statusFilter.value)}
                &sy=${encodeURIComponent(syFilter.value)}`
                    );

                    if (!response.ok) throw new Error('Fetch failed');

                    const html = await response.text();
                    studentTable.innerHTML = html;

                    updatePagination();
                    attachRowClick();

                } catch (err) {
                    console.error('Load error:', err);
                }
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
                    btn.className =
                        'btn btn-sm btn-outline-primary mx-1' +
                        (i === page ? ' active' : '');
                    btn.textContent = i;

                    btn.onclick = () => {
                        page = i;
                        loadStudents();
                    };

                    pagination.appendChild(btn);
                }
            }

            // 🔹 events
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

            // 🔹 FIRST LOAD (this was missing before)
            loadStudents();
        });
    </script>


</body>

</html>