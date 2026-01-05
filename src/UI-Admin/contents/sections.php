<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$search = trim($_POST['search'] ?? '');
$sy     = trim($_POST['school_year'] ?? '');

$limit  = 25;
$page   = max(1, (int)($_POST['page'] ?? 1));
$offset = ($page - 1) * $limit;

$where  = [];
$params = [];

if ($search !== '') {
    $where[]  = "s.section_name LIKE ?";
    $params[] = "%$search%";
}

if ($sy !== '') {
    $where[] = "
        EXISTS (
            SELECT 1
            FROM enrolment e
            WHERE e.section_name = s.section_name
              AND e.school_year_id = ?
              AND e.enrolment_Status = 'Approved'
        )
    ";
    $params[] = $sy;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "
    SELECT COUNT(*)
    FROM sections s
    $whereSql
";

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows  = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));


$dataSql = "
    SELECT s.*
    FROM sections s
    $whereSql
    ORDER BY s.created_date DESC
    LIMIT $limit OFFSET $offset
";

$dataStmt = $pdo->prepare($dataSql);
$dataStmt->execute($params);
$sections = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
$html = '';
$count = 0;
if (isset($_POST['ajax'])):
    if (!empty($sections)):
        foreach ($sections as $section) :
            $count += 1;
            $html .= '<tr class="section-row"
                data-name="' . htmlspecialchars(strtolower($section["section_name"])) . '"
                data-grade="' . htmlspecialchars(strtolower($section["section_grade_level"])) . '"
                data-status="' . htmlspecialchars(strtolower($section["section_status"])) . '">
                <td style="white-space: wrap; width:5rem">' . $count++ . '</td>
                <td style="white-space: wrap; max-width:9rem" class="section-name">
                    <div class="d-flex align-items-center">
                        <div class="avatar-placeholder me-2">
                            <i class="fa-solid fa-layer-group text-secondary"></i>
                        </div>
                        <div>
                            <strong>' . htmlspecialchars($section["section_name"]) . '</strong>
                        </div>
                    </div>
                </td>
                <td style="white-space: wrap; max-width:9rem">
                    <span class="badge bg-info">' . htmlspecialchars($section["section_grade_level"]) . '</span>
                </td>
                <td style="white-space: wrap; max-width:9rem">
                    <span class="badge bg-' . (($section["section_status"] === "Available") ? "success" : "secondary") . '">
                        <i class="fa-solid fa-circle fa-xs me-1"></i></span>
                </td>
                <td style="white-space: wrap; max-width:9rem">
                    <small>' . date("M d, Y", strtotime($section["created_date"])) . '</small>
                </td>
                <td style="white-space: wrap;">
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" data-id="' . $section["section_id"] . '"
                            class="btn btn-sm btn-info editSectionBtn"
                            title="Edit Section">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </button>
                        <button type="button" data-id="' . $section["section_id"] . '"
                            class="btn btn-sm btn-danger deleteSectionBtn"
                            title="Delete Section">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>';
        endforeach;
        $bt = "";
        if ($page > 1) {
            $bt = '<button class="btn btn-sm btn-secondary"
                                onclick="fetchSections(' . $page - 1 . ')">
                                Prev
                            </button>';
        }
        if ($page < $totalPages) {
            $bt .= '<button class="btn btn-sm btn-secondary"
                                onclick="fetchSections(' . $page + 1 . ')">
                                Next
                            </button>';
        }
        $html .= '<tr>
            <td colspan="6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Page ' . $page . ' of ' . $totalPages . '</span>
                    <div>
                        ' . $bt . '
                    </div>
                </div>
            </td>
        </tr>';
    else:
        $html = '<tr>
            <td colspan="6" class="text-center py-3">No sections found.</td>
        </tr>';
    endif;
    $statsSql = "
    SELECT
        COUNT(*) AS total_sections,
        SUM(CASE WHEN s.section_status = 'Available' THEN 1 ELSE 0 END) AS available_sections,
        SUM(CASE WHEN s.section_status = 'Unavailable' THEN 1 ELSE 0 END) AS unavailable_sections,
        COUNT(DISTINCT s.section_grade_level) AS grade_levels
    FROM sections s
    WHERE 1=1
";

    $paramsStats = [];

    if ($search !== '') {
        $statsSql .= " AND s.section_name LIKE ?";
        $paramsStats[] = "%$search%";
    }

    if ($sy !== '') {
        $statsSql .= "
        AND EXISTS (
            SELECT 1
            FROM enrolment e
            WHERE e.section_name = s.section_name
              AND e.school_year_id = ?
              AND e.enrolment_Status = 'Approved'
        )
    ";
        $paramsStats[] = $sy;
    }

    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute($paramsStats);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);


    echo json_encode([
        'rows' => $html,
        'hasData' => !empty($sections),
        'stats' => $stats,
    ]);
    exit;
endif;

?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-layer-group me-2"></i>Sections Management</h4>
    </div>
</div>

<div class="row g-3">
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" name="search" id="searchInput" placeholder="Search sections by name or grade level...">

            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createSection">
                <i class="fa-solid fa-plus me-2"></i> Create Section
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Sections Overview</h5>
                    <div class="row text-center">
                        <?php
                        $availableCount = array_filter($sections, fn($s) => $s['section_status'] === 'Available');
                        $unavailableCount = array_filter($sections, fn($s) => $s['section_status'] === 'Unavailable');

                        // Count sections by grade level
                        $gradeLevels = array_count_values(array_column($sections, 'section_grade_level'));
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 id="tc" class="text-white mb-1"><?= count($sections) ?></h3>
                                <small class="text-white">Total Sections</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 id="av" class="text-white mb-1"><?= count($availableCount) ?></h3>
                                <small class="text-white">Available</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 id="uv" class="text-white mb-1"><?= count($unavailableCount) ?></h3>
                                <small class="text-white">Unavailable</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 id="gt" class="text-white mb-1"><?= count($gradeLevels) ?></h3>
                                <small class="text-white">Grade Levels</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 1rem; align-items: center; border: none;">
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
    <div class="fsfs">
        <li>
            <span class="badge bg-success">
                <i class="fa-solid fa-circle fa-xs me-1"></i>
            </span> - Available
        </li>
        <li>
            <span class="badge bg-secondary">
                <i class="fa-solid fa-circle fa-xs me-1"></i>
            </span> - Unavailable
        </li>
    </div>
    <!-- Sections Table -->
    <div class="table-container-wrapper p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <!-- Fixed Header -->
            <div>
                <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th style='white-space: wrap; width:5rem'>#</th>
                            <th style='white-space: wrap; max-width:9rem'>Section Name</th>
                            <th style='white-space: wrap; max-width:9rem'>Grade Level</th>
                            <th style='white-space: wrap; max-width:9rem'>Section Status</th>
                            <th style='white-space: wrap; max-width:9rem'>Created at</th>
                            <th style='white-space: wrap; '>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="sectionsTableBody">
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-layer-group fa-3x text-muted mb-3"></i>
                <h5>No sections found</h5>
                <p class="text-muted">Try adjusting your search</p>
            </div>
        </div>
    </div>
</div>

<!-- Create Section Modal -->
<div class="modal fade" id="createSection" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createSectionLabel">
                    <i class="fa-solid fa-plus me-2"></i>Create New Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="section-form" method="post">
                    <div class="my-2">
                        <label class="form-label">Section Name <span class="text-danger">*</span></label>
                        <input type="text" name="section_name" class="form-control" placeholder="ex. Jupiter" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-select" required>
                            <option value="">Select Grade Level</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                        </select>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Section Status</label>
                        <div class="form-control bg-light">Available</div>
                        <input type="hidden" name="section_status" value="Available">
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-plus me-2"></i>Create Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSections" tabindex="-1" aria-labelledby="editSectionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="editSectionsLabel">
                    <i class="fa-solid fa-pen me-2"></i>Update Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="editSection-form" method="post">
                    <input type="hidden" name="section_id" id="section_ids">
                    <div class="my-2">
                        <label class="form-label">Section Status <span class="text-danger">*</span></label>
                        <select name="section_status" id="section_status" class="form-select" required>
                            <option value="">Select section status</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Section Name <span class="text-danger">*</span></label>
                        <input type="text" id="section_name" name="section_name" class="form-control" placeholder="ex. Jupiter" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="section_grade_level" id="section_grade_level" class="form-select" required>
                            <option value="">Select Grade Level</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                        </select>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-save me-2"></i>Update Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Section Modal -->
<div class="modal fade" id="deleteSection" tabindex="-1" aria-labelledby="deleteSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="deleteSectionLabel">
                    <i class="fa-solid fa-trash me-2"></i>Delete Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="deleteSection-form" method="post">
                    <input type="hidden" name="section_id" id="section_id">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                        <h5>Confirm Deletion</h5>
                        <p class="text-muted">Are you sure you want to delete this section? This action cannot be undone.</p>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fa-solid fa-trash me-2"></i>Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    let currentPage = 1;

    const searchInput = document.getElementById('searchInput');
    const syFilter = document.getElementById('syFilter');
    const sectionsTableBody = document.getElementById('sectionsTableBody');
    const noResultsDiv = document.getElementById('noResults');
    //  const editButtons = document.querySelectorAll('.editClassroomsBtn');
    //  const deleteButtons = document.querySelectorAll('.deleteClassroomBtn');

    function fetchSections(page = 1) {
        sectionsTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        <div>Loading students...</div>
    </td></tr>`;
        const formData = new FormData();
        formData.append('ajax', 1);
        formData.append('search', searchInput.value.trim());
        formData.append('school_year', syFilter.value);
        formData.append('page', page);

        fetch('contents/sections.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                sectionsTableBody.innerHTML = data.rows;
                document.getElementById('tc').textContent = data.stats.total_sections ?? 0;
                document.getElementById('av').textContent = data.stats.available_sections ?? 0;
                document.getElementById('uv').textContent = data.stats.unavailable_sections ?? 0;
                document.getElementById('gt').textContent = data.stats.grade_levels ?? 0;

                if (!data.hasData) {
                    sectionsTableBody.style.display = 'none';
                    noResultsDiv.classList.remove('d-none');
                } else {
                    sectionsTableBody.style.display = '';
                    noResultsDiv.classList.add('d-none');
                }
            })
            .catch(err => {
                console.error(err);
                sectionsTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load data</td></tr>`;
            });

    }
    document.addEventListener('DOMContentLoaded', function() {
        searchInput.addEventListener('input', () => fetchSections(1));
        syFilter.addEventListener('change', () => fetchSections(1));

        searchInput.addEventListener('keypress', e => {
            if (e.key === 'Enter') fetchSections(1);
        });
        fetchSections(currentPage);

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

    .me-1 {
        margin-right: 0 !important;
    }

    .fsfs li {
        list-style: none;
    }

    .fsfs {
        display: flex;
        gap: 1rem;
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

    .form-control.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
        color: #495057;
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>