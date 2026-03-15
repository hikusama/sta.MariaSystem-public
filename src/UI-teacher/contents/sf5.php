<?php
require_once __DIR__ . '/../../../tupperware.php';

$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

// AJAX handler for search & pagination
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $search = trim($_GET['search'] ?? '');
    $sy     = trim($_GET['sy'] ?? '');
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Base WHERE clauses
    $where = ["e.adviser_id = :adviser_id"];
    $params = [':adviser_id' => $_SESSION['user_id']];

    if ($sy) {
        $where[] = "e.school_year_id = :sy_id";
        $params[':sy_id'] = intval($sy);
    }

    if ($search) {
        $where[] = "e.section_name LIKE :section_name";
        $params[':section_name'] = "%" . $search . "%";
    }

    $whereSQL = "WHERE " . implode(" AND ", $where);

    // Count total rows
    $countSQL = "SELECT COUNT(*) AS total
             FROM enrolment e
             JOIN student s ON s.student_id = e.student_id
             $whereSQL";

    $countStmt = $pdo->prepare($countSQL);
    $countStmt->execute($params);
    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRows / $perPage);

    // Fetch paginated enrollments (inject LIMIT & OFFSET directly)
    $sql = "SELECT DISTINCT e.section_name, sc.section_id, e.Grade_level, sy.school_year_name, e.school_year_id
        FROM enrolment e
        JOIN sections sc ON sc.section_name = e.section_name
        JOIN school_year sy ON sy.school_year_id = e.school_year_id
        $whereSQL
        ORDER BY e.section_name ASC
        LIMIT $perPage OFFSET $offset"; // <-- direct integers, no placeholders

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = '';
    if ($result) {
        $count = $offset + 1;
        foreach ($result as $row) {
            $html .= "<tr class='section-row' 
                        data-school_year='" . htmlspecialchars($row['school_year_name']) . "'
                        data-id='" . htmlspecialchars($row['section_id']) . "'
                        data-grade='" . htmlspecialchars($row['Grade_level']) . "'
                        data-section='" . htmlspecialchars($row['section_name']) . "'>
                        <td width='5%'>$count</td>
                        <td width='60%'>
                            <div class='d-flex align-items-center'>
                                <div class='avatar-placeholder me-2'>
                                    <i class='fa-solid fa-layer-group text-secondary'></i>
                                </div>
                                <div>
                                    <strong>" . htmlspecialchars($row['section_name']) . "</strong> - <strong>" . htmlspecialchars($row['school_year_name']) . "</strong>
                                </div>
                            </div>
                        </td>
                        <td width='35%'>
                            <span class='badge bg-info'>" . htmlspecialchars($row['Grade_level']) . "</span>
                        </td>
                      </tr>";
            $count++;
        }
    } else {
        $html = "<tr><td colspan='3' class='text-center py-3'>No sections found.</td></tr>";
    }

    echo json_encode([
        'html' => $html,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="<?php echo base_url() ?>/assets/image/logo2.png" type="image/x-icon">
    <title>SF5 - Report on Promotion</title>
    <link href="<?= base_url() ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url() ?>assets/fontawesome/css/all.min.css">
    <style>
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

        #searchInput:focus {
            box-shadow: none;
            border-color: #86b7fe;
        }

        #pagination button {
            cursor: pointer;
            margin: 0 2px;
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

        @media (max-width: 768px) {
            .scroll-feedback {
                height: auto;
                overflow: visible;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mx-2">
                <h4><i class="fa-solid fa-chart-line me-2"></i>SF5 - Report on Promotion and Level of Proficiency</h4>
            </div>
        </div>

        <div class="scroll-feedback">
            <div class="row mb-3 justify-content-between align-items-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="text" class="form-control" name="search" id="searchInput" placeholder="Search sections by name or grade level...">
                    </div>
                </div>
                <div class="col-md-4 text-end">
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
                        $schoolYears = [];
                        while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                            if ($cat['school_year_status'] === 'Active' && $activeSyId === null) {
                                $activeSyId = $cat['school_year_id'];
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

            <div class="table-container-wrapper p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="60%">Section Name</th>
                                <th width="35%">Grade Level</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                        <tbody id="sectionTable"></tbody>
                    </table>
                </div>

                <div id="noResults" class="text-center py-5 d-none">
                    <div class="empty-state">
                        <i class="fa-solid fa-layer-group fa-3x text-muted mb-3"></i>
                        <h5>No sections found</h5>
                        <p class="text-muted">Try adjusting your search</p>
                    </div>
                </div>

                <div id="pagination" class="mt-2 text-center"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sectionTable = document.getElementById('sectionTable');
            const noResultsDiv = document.getElementById('noResults');
            const syEl = document.getElementById('syFilter');
            const searchInput = document.getElementById('searchInput');
            const paginationDiv = document.getElementById('pagination');

            let currentPage = 1;

            function loadSections(page = 1) {
                currentPage = page;
                const sy = syEl.value;
                const sr = searchInput.value;

                const xhr = new XMLHttpRequest();
                xhr.open('GET', `contents/sf5.php?ajax=1&search=${encodeURIComponent(sr)}&sy=${encodeURIComponent(sy)}&page=${currentPage}`, true);
                xhr.onload = function() {
                    if (this.status === 200) {
                        const res = JSON.parse(this.responseText);
                        sectionTable.innerHTML = res.html;
                        attachRowClickEvents();

                        if (res.html.includes('No sections found')) {
                            sectionTable.style.display = 'none';
                            noResultsDiv.classList.remove('d-none');
                            paginationDiv.style.display = 'none';
                        } else {
                            sectionTable.style.display = '';
                            noResultsDiv.classList.add('d-none');
                            paginationDiv.style.display = '';
                            renderPagination(res.totalPages, res.currentPage);
                        }
                    }
                };
                xhr.send();
            }

            function renderPagination(totalPages, current) {
                paginationDiv.innerHTML = '';
                // if (totalPages <= 1) return;

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.className = 'btn btn-sm btn-outline-primary mx-1';
                    if (i === current) btn.classList.add('active');
                    btn.addEventListener('click', () => loadSections(i));
                    paginationDiv.appendChild(btn);
                }
            }

            function attachRowClickEvents() {
                document.querySelectorAll('.section-row').forEach(row => {
                    row.addEventListener('click', function() {
                        const sectionId = this.dataset.id;
                        const school_year = this.dataset.school_year;
                        const gradeLevel = this.dataset.grade;
                        const sectionName = this.dataset.section;
const baseFr = '<?php echo rtrim(base_url(), "/"); ?>';
const url = `${baseFr}/src/UI-teacher/contents/schoolform5.php?school_year=${school_year}&section_id=${encodeURIComponent(sectionId)}&grade=${encodeURIComponent(gradeLevel)}&section=${encodeURIComponent(sectionName)}`;
                        window.location.href = url;
                    });
                });
            }

            searchInput.addEventListener('input', () => loadSections(1));
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') loadSections(1);
            });
            syEl.addEventListener('change', () => loadSections(1));

            loadSections(); // initial load
        });
    </script>
</body>

</html>