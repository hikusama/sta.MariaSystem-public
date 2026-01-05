<?php
require_once __DIR__ . '/../../../tupperware.php';

// Redirect if not authorized
$result = checkURI('admin', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

// Filters
$search   = trim($_POST['search'] ?? '');
$syFilter = trim($_POST['school_year'] ?? '');

// Pagination
$limit  = 25;
$page   = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$offset = ($page - 1) * $limit;

// Get current active school year
$currentSyStmt = $pdo->prepare("
    SELECT school_year_id, school_year_name 
    FROM school_year 
    WHERE school_year_status = 'Active' 
    LIMIT 1
");
$currentSyStmt->execute();
$currentSy = $currentSyStmt->fetch(PDO::FETCH_ASSOC);
$activeSyId = $currentSy['school_year_id'] ?? null;

// Determine the query condition
if (!$syFilter) {
    // 1. SY is empty → show all classrooms + active classes
    $sql = "
    SELECT c.room_id, c.room_name, c.room_type, c.room_status,
           cl.class_id, cl.sy_id, u.user_id AS adviser_id,
           u.firstname AS adviser_firstname, u.lastname AS adviser_lastname,
           sy.school_year_name
    FROM classrooms c
    LEFT JOIN classes cl 
        ON cl.classroom_id = c.room_id 
    LEFT JOIN users u ON u.user_id = cl.adviser_id
    LEFT JOIN school_year sy ON sy.school_year_id = cl.sy_id
    WHERE 1
    ";
    $params = [];
} else {
    if ($syFilter == $activeSyId) {
        // 2. SY is active → same as above
        $sql = "
        SELECT c.room_id, c.room_name, c.room_type, c.room_status,
               cl.class_id, cl.sy_id, u.user_id AS adviser_id,
               u.firstname AS adviser_firstname, u.lastname AS adviser_lastname,
               sy.school_year_name
        FROM classrooms c
        LEFT JOIN classes cl 
            ON cl.classroom_id = c.room_id 
            AND cl.sy_id = :activeSyId
        LEFT JOIN users u ON u.user_id = cl.adviser_id
        LEFT JOIN school_year sy ON sy.school_year_id = cl.sy_id
        WHERE 1
        ";
        $params = [':activeSyId' => $activeSyId];
    } else {
        // 3. SY is non-active → only classrooms with classes in that SY
        $sql = "
        SELECT c.room_id, c.room_name, c.room_type, c.room_status,
               cl.class_id, cl.sy_id, u.user_id AS adviser_id,
               u.firstname AS adviser_firstname, u.lastname AS adviser_lastname,
               sy.school_year_name
        FROM classes cl
        INNER JOIN classrooms c ON c.room_id = cl.classroom_id
        LEFT JOIN users u ON u.user_id = cl.adviser_id
        LEFT JOIN school_year sy ON sy.school_year_id = cl.sy_id
        WHERE cl.sy_id = :syFilter
        ";
        $params = [':syFilter' => $syFilter];
    }
}

// Search filter
if ($search) {
    $sql .= " AND (c.room_name LIKE :search OR c.room_type LIKE :search OR CONCAT(COALESCE(u.firstname,''),' ',COALESCE(u.lastname,'')) LIKE :search)";
    $params[':search'] = "%$search%";
}

// Order: NULL advisers first
$sql .= " ORDER BY CASE WHEN u.user_id IS NULL THEN 0 ELSE 1 END, c.room_name ASC";

// Get total rows for pagination
$countSql = "SELECT COUNT(*) FROM ($sql) AS total_count";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));

// Add limit/offset
$sql .= " LIMIT :limit OFFSET :offset";
$params[':limit']  = $limit;
$params[':offset'] = $offset;

// Classroom stats (always based on active SY)
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_classrooms,
        SUM(CASE WHEN c.room_id NOT IN (SELECT classroom_id FROM classes WHERE sy_id = :activeSyId) THEN 1 ELSE 0 END) AS available,
        SUM(CASE WHEN c.room_id IN (SELECT classroom_id FROM classes WHERE sy_id = :activeSyId) THEN 1 ELSE 0 END) AS occupied,
        SUM(CASE WHEN c.room_status != 'Active' THEN 1 ELSE 0 END) AS unavailable
    FROM classrooms c
");
$statsStmt->execute([':activeSyId' => $activeSyId]);
$classroomStats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Execute main query
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $val, $type);
}
$stmt->execute();
$classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ------------------------
// Other fetches you already have
// ------------------------

// Fetch available teachers
$teachersStmt = $pdo->prepare("
    SELECT * 
    FROM users 
    WHERE user_role = 'TEACHER' 
    AND user_id NOT IN (SELECT adviser_id FROM classes WHERE sy_id = ?) 
    ORDER BY lastname ASC
");
$teachersStmt->execute([$activeSyId]);
$teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);

// Current school year info
$schoolYears = $currentSy ?? [];

// Sections fetch for grade selection (AJAX)
if (isset($_POST['getsections'])) {
    $grade = trim($_POST['grade'] ?? '');
    if (empty($grade)) {
        echo json_encode(['sections' => []]);
        exit;
    }
    $stmt = $pdo->prepare("
        SELECT * FROM sections 
        WHERE section_grade_level = ? 
        AND section_status = 'Available' 
        AND section_name NOT IN(SELECT section_name FROM classes WHERE sy_id = ?)
        ORDER BY section_grade_level ASC, section_name ASC
    ");
    $stmt->execute([$grade, $activeSyId]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sections' => $sections]);
    exit;
}


// Fetch available teachers
$teachersStmt = $pdo->prepare("
    SELECT * 
    FROM users 
    WHERE user_role = 'TEACHER' 
    AND user_id NOT IN (SELECT adviser_id FROM classes WHERE sy_id = ?) 
    ORDER BY lastname ASC
");
$teachersStmt->execute([$activeSyId]);
$teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);

// Current school year info
$schoolYears = $currentSy ?? [];

if (isset($_POST['getsections'])) {
    $grade = trim($_POST['grade'] ?? '');
    if (empty($grade)) {
        echo json_encode(['sections' => []]);
        exit;
    }
    $stmt = $pdo->prepare("
        SELECT * FROM sections 
        WHERE section_grade_level = ? 
        AND section_status = 'Available' 
        AND section_name NOT IN(SELECT section_name FROM classes WHERE sy_id = ?)
        ORDER BY section_grade_level ASC, section_name ASC
    ");
    $stmt->execute([$grade, $activeSyId]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sections' => $sections]);
    exit;
}

if (isset($_POST['ajax'])):
    ob_start();
    if ($classrooms): ?>
        <div class="row">
            <?php foreach ($classrooms as $classroom):
                $isAvailable = empty($classroom['adviser_id']);
                $hasTeacher = !empty($classroom['adviser_id']);
                $roomStatus = $isAvailable ? 'Available' : 'Unavailable';
                $cardClass = $isAvailable ? 'available' : 'unavailable';
                $iconClass = $hasTeacher ? 'occupied' : ($isAvailable ? 'available' : 'unavailable');
            ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4 classroom-item"
                    style="height:19rem;width:27rem;background: linear-gradient(145deg, #ebebeb, #ffffff);"
                    data-name="<?= htmlspecialchars(strtolower($classroom["room_name"])) ?>"
                    data-type="<?= htmlspecialchars(strtolower($classroom["room_type"])) ?>"
                    data-status="<?= htmlspecialchars(strtolower($roomStatus)) ?>"
                    data-teacher="<?= htmlspecialchars(strtolower($classroom["adviser_firstname"] . ' ' . $classroom["adviser_lastname"])) ?>">
                    <div class="card border-0 classroom-card <?= $cardClass ?>" style="box-shadow:  17px 17px 33px #9d9d9d,-17px -17px 33px #ffffff;">
                        <div class="classroom-info">
                            <div class="d-flex align-items-center mb-3">
                                <div class="classroom-icon text-white <?= $iconClass ?> me-3">
                                    <i class="fa-solid fa-door-closed"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($classroom["room_name"]) ?></h5>
                                    <span class="badge-status text-white bg-<?= $isAvailable ? 'success' : 'danger' ?>">
                                        <?= $roomStatus ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">Type:</small>
                                <div><strong><?= htmlspecialchars($classroom["room_type"]) ?></strong></div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">Teacher Assigned:</small>
                                <div class="classroom-teacher">
                                    <?php if ($hasTeacher): ?>
                                        <i class="fa-solid fa-user-tie me-1"></i>
                                        <strong><?= htmlspecialchars($classroom["adviser_firstname"] . " " . $classroom["adviser_lastname"]) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">No teacher assigned</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                <?php if ($isAvailable && !$hasTeacher): ?>
                                    <button type="button" class="btn btn-danger btn-sm assign-teacher-btn"
                                        data-id="<?= $classroom["room_id"] ?>" title="Assign Teacher">
                                        <i class="fa-solid fa-user-plus me-1"></i> Assign Teacher
                                    </button>
                                <?php elseif ($hasTeacher): ?>
                                    <span class="badge bg-dark"><i class="fa-solid fa-user-check me-1"></i> Occupied</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="fa-solid fa-ban me-1"></i> Unavailable</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($classroom['sy_id']): ?>
                                <div class="ua">
                                    <p><?= htmlspecialchars($classroom['school_year_name']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <tr>
                <td colspan="6">
                    <div class="d-flex justify-content-between">
                        <span>Page <?= $page ?> of <?= $totalPages ?></span>
                        <div>
                            <?php if ($page > 1): ?>
                                <button class="btn btn-sm btn-secondary" onclick="fetchClassrooms(<?= $page - 1 ?>)">Prev</button>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <button class="btn btn-sm btn-secondary" onclick="fetchClassrooms(<?= $page + 1 ?>)">Next</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
        </div>
    <?php else: ?>
        <div class="empty-classroom text-center py-5">
            <i class="fa-solid fa-school fa-3x text-muted mb-3"></i>
            <h5>No Classrooms Found</h5>
            <p class="text-muted">Try adjusting your search or filters</p>
        </div>
<?php
    endif;

    $html = ob_get_clean();

    echo json_encode([
        'html'        => $html,
        'currentPage' => $page,
        'hasData'     => !empty($classrooms),
        'stats'       => $classroomStats
    ]);
    exit;
endif;
?>




<style>
    .classroom-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .classroom-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #4e73df, #224abe);
    }

    .classroom-card.available::before {
        background: linear-gradient(90deg, #1cc88a, #13855c);
    }

    .classroom-card.unavailable::before {
        background: linear-gradient(90deg, #e74a3b, #be2617);
    }

    .classroom-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .classroom-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .classroom-icon.available {
        background: linear-gradient(135deg, #1cc88a, #13855c);
    }

    .classroom-icon.unavailable {
        background: linear-gradient(135deg, #e74a3b, #be2617);
    }

    .classroom-icon.occupied {
        background: linear-gradient(135deg, #f6c23e, #dda20a);
    }

    .ua p {
        color: black;
        font-size: 1.2rem;
        width: fit-content;
        margin-bottom: 0 !important;
    }

    .ua {
        margin-top: 1rem;
        display: flex;
        justify-content: start;
        align-items: center;
        gap: .5rem;
        position: relative;
    }

    .ua::before {
        content: '';
        display: block;
        height: 2rem;
        border-radius: .5rem;
        width: .5rem;
        background-color: #224abe;
    }

    .classroom-info {
        padding: 15px;
    }

    .badge-status {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
    }

    .classroom-teacher {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .empty-classroom {
        padding: 3rem 1rem;
        text-align: center;
    }

    .empty-classroom i {
        opacity: 0.5;
    }

    .scroll-classes {
        height: 80vh;
        overflow-y: scroll;
        overflow-x: hidden;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-building-user me-2"></i>Class Management</h4>
    </div>
</div>

<div class="row g-3 scroll-classes">
    <!-- Search Section -->
    <div class="row mb-3 justify-content-between align-items-center d-flex flex-wrap gap-2">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="searchInput" placeholder="Search classrooms by name, type, or teacher..." class="form-control">
            </div>
        </div>
        <div class="col-md-4 text-start">
            <label for="syFilter">Occupied at</label>
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

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Classrooms Overview</h5>
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1" id="tc"><?= $classroomStats['total_classrooms'] ?? 0 ?></h3>
                                <small class="text-dark">Total Classrooms</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-dark mb-1" id="av"><?= $classroomStats['available'] ?? 0 ?></h3>
                                <small class="text-dark">Available</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <h3 class="text-dark mb-1" id="oc"><?= $classroomStats['occupied'] ?? 0 ?></h3>
                                <small class="text-dark">Occupied</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="text-dark mb-1" id="uv"><?= $classroomStats['unavailable'] ?? 0 ?></h3>
                                <small class="text-dark">Unavailable</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classrooms Grid -->
    <div id="classroomsGrid"></div>
</div>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assgnTeacher" tabindex="-1" aria-labelledby="assgnTeacherLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="assgnTeacherLabel">
                    <i class="fa-solid fa-user-plus me-2"></i>Assign Teacher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="assign-teacher-form" method="post">
                    <!-- Hidden Input for Classroom ID -->
                    <input type="hidden" name="classroom_id" id="classroomIdInput" value="">

                    <div class="my-2">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" id="grade_level" class="form-select" required>
                            <option value="">Select Grade Level</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                        </select>
                    </div>

                    <!-- Section Dropdown -->
                    <div class="my-2">
                        <label class="form-label">Section Name <span class="text-danger">*</span></label>
                        <select name="section_id" id="section_id" class="form-select" required disabled>
                            <option value="">Select Grade Level First</option>
                        </select>
                    </div>

                    <!-- Teacher Selection -->
                    <div class="my-2">
                        <label class="form-label">Teacher Name <span class="text-danger">*</span></label>
                        <select name="teacher_name" id="teacher_id" class="form-select" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher["user_id"] ?>">
                                    <?= htmlspecialchars($teacher["lastname"]) . ", " . htmlspecialchars($teacher["firstname"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($teachers)): ?>
                            <small class="text-danger">No available teachers for assignment</small>
                        <?php endif; ?>
                    </div>

                    <div class="my-2">
                        <label class="form-label">School Year</label>
                        <div class="form-control bg-light">
                            <?= htmlspecialchars($currentSy["school_year_name"] ?? 'Not set') ?>
                        </div>
                        <input type="hidden" name="schoolYear_id" value="<?= $currentSy["school_year_id"] ?? '' ?>">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-user-plus me-2"></i>Assign Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    let currentPage = 1;
    const grade_level = document.getElementById('grade_level');
    const section_id = document.getElementById('section_id');

    function getSections() {
        if (!grade_level.value) {
            section_id.innerHTML = '<option value="">Select Grade Level First</option>';
            section_id.disabled = true;
            return;
        }

        const formData = new FormData();
        formData.append('getsections', 1);
        formData.append('grade', grade_level.value);

        fetch('contents/assign.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                section_id.innerHTML = '<option value="">Select Section</option>';
                if (data.sections.length > 0) {
                    section_id.disabled = false;
                    data.sections.forEach(section => {
                        const opt = document.createElement('option');
                        opt.value = section.section_id;
                        opt.textContent = section.section_name;
                        section_id.appendChild(opt);
                    });
                } else {
                    section_id.disabled = true;
                }
            })
            .catch(err => {
                console.error('Failed to load sections:', err);
                section_id.innerHTML = '<option value="">Failed to load sections</option>';
                section_id.disabled = true;
            });
    }

    function fetchClassrooms(page = 1) {
        currentPage = page;
        const searchInput = document.getElementById('searchInput');
        const syFilter = document.getElementById('syFilter');
        const classroomsGrid = document.getElementById('classroomsGrid');

        // Show loading indicator inside the grid
        classroomsGrid.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <div>Loading classrooms...</div>
        </div>
    `;

        const formData = new FormData();
        formData.append('search', searchInput.value.trim());
        formData.append('school_year', syFilter.value);
        formData.append('ajax', 1);
        formData.append('page', page);

        fetch('contents/assign.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                classroomsGrid.innerHTML = data.html;

                document.getElementById('tc').textContent = data.stats.total_classrooms ?? 0;
                document.getElementById('av').textContent = data.stats.available ?? 0;
                document.getElementById('oc').textContent = data.stats.occupied ?? 0;
                document.getElementById('uv').textContent = data.stats.unavailable ?? 0;
            })
            .catch(err => {
                console.error(err);
                classroomsGrid.innerHTML = `
            <div class="text-danger py-4 text-center">
                Failed to load classrooms.
            </div>
        `;
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const syFilter = document.getElementById('syFilter');

        grade_level.addEventListener('change', () => getSections());
        searchInput.addEventListener('input', () => fetchClassrooms(1));
        searchInput.addEventListener('keypress', e => {
            if (e.key === 'Enter') fetchClassrooms(1);
        });

        syFilter.addEventListener('change', () => fetchClassrooms(1));
        fetchClassrooms(currentPage);
    });
</script>