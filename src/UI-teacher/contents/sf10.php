<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}


$teacher_id = $_SESSION['user_id'];
$conn = new mysqli("db", "stuser", "stpass", "stamaraiadb");
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
    $where[] = "e.adviser_id = ?";
    $params[] = $teacher_id;
    $types .= 'i';

    /* school year */
    if ($sy) {
        $where[] = "e.school_year_id = ?";
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
        FROM enrolment AS e
        LEFT JOIN student AS s ON s.student_id = e.student_id
        $whereSQL
    ";

    $stmt = $conn->prepare($countSQL);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    /* DATA */
    $sql = "
        SELECT s.*,e.* FROM enrolment AS e
        LEFT JOIN student AS s ON s.student_id = e.student_id
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
                                        <td class='td-lrn'>
                                            <div class='lrn-content'>
                                                <div class='avatar-placeholder'>
                                                    <i class='fa-solid fa-id-card text-info'></i>
                                                </div>
                                                <div class='lrn-text'>
                                                    <strong>" . htmlspecialchars($row['lrn']) . "</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td class='td-fname'>" . htmlspecialchars($row['fname']) . "</td>
                                        <td class='td-mname'>" . htmlspecialchars($row['mname']) . "</td>
                                        <td class='td-lname'>" . htmlspecialchars($row['lname']) . "</td>
                                        <td class='td-grade'>
                                            <span class='badge bg-primary text-truncate'>" . htmlspecialchars($row['gradeLevel']) . "</span>
                                        </td>
                                        <td class='td-sex'>
                                            <span class='badge bg-" . (strtolower($row['sex']) == 'male' ? 'info' : 'warning') . "'>
                                                " . htmlspecialchars($row['sex']) . "
                                            </span>
                                        </td>
                                        <td class='td-status'>
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

        .container-fluid {
            max-width: 100%;
            padding: 0.75rem;
            overflow-x: hidden;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
        }

        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            padding: 0.75rem;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
            cursor: pointer;
        }

        .table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
        }

        /* LRN Cell Styling */
        .lrn-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
        }

        .lrn-text {
            min-width: 0;
            overflow: hidden;
        }

        .lrn-text strong {
            word-break: break-word;
            display: block;
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
            flex-shrink: 0;
        }

        /* Responsive columns */
        .td-lrn {
            min-width: 120px;
        }

        .td-fname {
            min-width: 100px;
        }

        .td-mname {
            min-width: 100px;
        }

        .td-lname {
            min-width: 100px;
        }

        .td-grade {
            min-width: 80px;
        }

        .td-sex {
            min-width: 70px;
        }

        .td-status {
            min-width: 130px;
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

        @media (max-width: 992px) {
            .table {
                font-size: 0.9rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.6rem;
            }

            .td-lrn {
                min-width: 100px;
            }

            .avatar-placeholder {
                width: 32px;
                height: 32px;
                font-size: 18px;
            }
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding: 0.5rem;
            }

            h4 {
                font-size: 1.15rem;
                margin-bottom: 1rem;
            }

            .row.mb-3 {
                margin-bottom: 1rem !important;
            }

            .form-control-sm,
            .form-select-sm {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
            }

            .table {
                font-size: 0.75rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.4rem;
            }

            .lrn-content {
                gap: 0.3rem;
            }

            .avatar-placeholder {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }

            .td-lrn {
                min-width: 90px;
            }

            .td-fname {
                min-width: 70px;
            }

            .td-mname {
                min-width: 70px;
            }

            .td-lname {
                min-width: 80px;
            }

            .td-grade {
                min-width: 60px;
            }

            .td-sex {
                min-width: 50px;
            }

            .td-status {
                min-width: 100px;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }

            .badge {
                font-size: 0.65rem;
                padding: 0.25em 0.4em;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 0.375rem;
            }

            h4 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }

            .form-control-sm,
            .form-select-sm {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }

            .table {
                font-size: 0.7rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.3rem 0.2rem;
            }

            .lrn-content {
                gap: 0.2rem;
            }

            .avatar-placeholder {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }

            .lrn-text strong {
                font-size: 0.65rem;
            }

            .td-lrn {
                min-width: 80px;
            }

            .td-fname {
                min-width: 60px;
            }

            .td-mname {
                min-width: 60px;
            }

            .td-lname {
                min-width: 70px;
            }

            .td-grade {
                min-width: 50px;
            }

            .td-sex {
                min-width: 45px;
            }

            .td-status {
                min-width: 90px;
            }

            .btn-sm {
                padding: 0.2rem 0.35rem;
                font-size: 0.65rem;
            }

            .badge {
                font-size: 0.6rem;
                padding: 0.2em 0.35em;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-3">
        <h4><i class="fa-solid fa-file-certificate me-2"></i>SF10 - Learner's Permanent Academic Record</h4>

        <div class="row mb-3 g-2">
            <div class="col-12 col-md-4">
                <input class="form-control form-control-sm" id="searchInput" placeholder="Search LRN, Name...">
            </div>
            <div class="col-6 col-md-3">
                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="active">Active</option>
                    <option value="not_active">Not Active</option>
                    <option value="transferred_in">Transferred In</option>
                    <option value="transferred_out">Transferred Out</option>
                    <option value="dropped">Dropped</option>
                    <option value="">All Status</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select id="syFilter" class="form-select form-select-sm">
                    <?php
                    $syq = $conn->query("SELECT school_year_id, school_year_name, school_year_status FROM school_year ORDER BY CASE WHEN school_year_status='Active' THEN 0 ELSE 1 END, school_year_name ASC");
                    while ($sy = $syq->fetch_assoc()):
                        $isActive = $sy['school_year_status'] == 'Active' ? ' (Active)' : '';
                    ?>
                        <option value="<?= $sy['school_year_id'] ?>"><?= htmlspecialchars($sy['school_year_name']) . $isActive ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
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
        </div>
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
                        window.location.href = "<?= base_url() ?>/src/UI-teacher/contents/schoolform10.php?student_id=" + row.dataset.id;
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
                pagination.innerHTML = `<div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
                    <span class="text-sm">Page ${page} of ${pages}</span>
                    <div class="d-flex gap-2">
                        ${page > 1 ? `<button class="btn btn-sm btn-secondary" onclick="page = ${page - 1}; loadStudents();">Prev</button>` : ''}
                        ${page < pages ? `<button class="btn btn-sm btn-secondary" onclick="page = ${page + 1}; loadStudents();">Next</button>` : ''}
                    </div>
                </div>`;
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