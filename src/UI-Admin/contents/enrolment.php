<?php
require_once __DIR__ . '/../../../tupperware.php';

$result = checkURI('admin', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}


$activeSyStmt = $pdo->prepare("
    SELECT school_year_id, school_year_name
    FROM school_year
    WHERE school_year_status = 'Active'
    LIMIT 1
");
$activeSyStmt->execute();

$activeSy = $activeSyStmt->fetch(PDO::FETCH_ASSOC);

if ($activeSy && is_array($activeSy)) {
    $_SESSION['active_sy_id'] = $activeSy['school_year_id'];
    $_SESSION['active_sy_name'] = $activeSy['school_year_name'];
} else {
    $_SESSION['active_sy_id'] = null;
    $_SESSION['active_sy_name'] = null;
}

$activeSyId = $_SESSION['active_sy_id'];
$activeSyName = $_SESSION['active_sy_name'];

if (isset($_POST['ajax2'])) {
    $grade_level = trim($_POST['grade_level'] ?? '');

    if ($grade_level === '') {
        echo json_encode([
            'teachers' => [],
            'subjects' => [],
            'sectionMap' => [],
            'grade_level' => $grade_level,
            'school_year_name' => $activeSyName,
            'school_year_id' => $activeSyId,
        ]);
        exit;
    }

    $teachersStmt = $pdo->prepare("
    SELECT 
        u.user_id AS adviser_id,
        u.firstname,
        u.lastname,
        c.grade_level,
        c.section_name,
        COUNT(e.student_id) AS student_count
    FROM users u
    INNER JOIN classes c
        ON u.user_id = c.adviser_id
        AND c.sy_id = ?
        AND c.grade_level = ?
    LEFT JOIN enrolment e
        ON e.adviser_id = u.user_id
        AND e.school_year_id = c.sy_id
    WHERE u.user_role = 'TEACHER'
    GROUP BY u.user_id, u.firstname, u.lastname, c.grade_level, c.section_name
    ORDER BY u.lastname ASC
");
    $teachersStmt->execute([
        $_SESSION['active_sy_id'],
        $grade_level
    ]);
    $teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);


    $ste = $pdo->prepare("
    SELECT *
    FROM subjects
    WHERE grade_level = ?
");
    $ste->execute([$grade_level]);
    $subjects = $ste->fetchAll(PDO::FETCH_ASSOC);

    $sectionMap = [];
    foreach ($teachers as $t) {
        $sectionMap[$t['adviser_id']] = $t['section_name'];
    }

    echo json_encode([
        'teachers' => $teachers,
        'subjects' => $subjects,
        'sectionMap' => $sectionMap,
        'grade_level' => $grade_level,
        'school_year_name' => $activeSyName,
        'school_year_id' => $activeSyId,
    ]);
    exit;

    exit;
}
/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/
$search = trim($_POST['search'] ?? '');
$status = trim($_POST['status'] ?? '');
$grade  = trim($_POST['grade'] ?? '');
$sy     = trim($_POST['school_year'] ?? '');

$limit = 25;
$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$offset = ($page - 1) * $limit;

/*
|--------------------------------------------------------------------------
| BASE FILTER LOGIC (shared)
|--------------------------------------------------------------------------
*/
$whereSql = [];
$params = [];

$activeSyId = $_SESSION['active_sy_id'] ?? null;
$isActiveSy = ($sy && $activeSyId && $sy == $activeSyId);
$systatus = 0;
$iddf = $activeSyId;

if ($sy && $isActiveSy) {
    $systatus = 1;
    $whereSql[] = "s.enrolment_status = 'pending' OR e.school_year_id = ?";
    $params[] = $activeSyId;
} elseif ($sy && !$isActiveSy) {
    $systatus = 2;
    $whereSql[] = "s.student_id IN (SELECT student_id FROM enrolment WHERE school_year_id = ?)";
    $params[] = $sy;
} else {
    $whereSql[] = "s.student_id IN (SELECT student_id FROM enrolment)";
}

if ($status) {
    $whereSql[] = "LOWER(s.enrolment_status) = ?";
    $params[] = strtolower($status);
}

if ($grade) {
    $whereSql[] = "LOWER(s.gradeLevel) = ?";
    $params[] = strtolower($grade);
}

if ($search) {
    $whereSql[] = "(s.fname LIKE ? OR s.lname LIKE ? OR s.lrn LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s, $s, $s);
}

$whereClause = $whereSql ? "WHERE " . implode(" AND ", $whereSql) : "";

/*
|--------------------------------------------------------------------------
| STATS QUERY
|--------------------------------------------------------------------------
*/
$statSql = "
SELECT
    COUNT(*) AS total_students,
    SUM(CASE WHEN s.enrolment_status = 'active' THEN 1 ELSE 0 END) AS enrolled,
    SUM(CASE WHEN s.enrolment_status = 'pending' OR s.enrolment_status IS NULL THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN s.enrolment_status IN ('rejected','dropped') THEN 1 ELSE 0 END) AS rejected
FROM student s
LEFT JOIN users u ON s.guardian_id = u.user_id
LEFT JOIN enrolment e
    ON e.student_id = s.student_id
$whereClause
";

$stmtStat = $pdo->prepare($statSql);
$stmtStat->execute($params);
$stat = $stmtStat->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| STUDENTS QUERY
|--------------------------------------------------------------------------
*/
$dataSql = "
SELECT s.*, u.user_id, u.firstname, u.middlename, u.lastname, u.email, u.contact,e.school_year_id
FROM student s
LEFT JOIN users u ON s.guardian_id = u.user_id
LEFT JOIN enrolment e
    ON e.student_id = s.student_id
$whereClause
ORDER BY s.fname ASC
";

/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/
$countSql = "SELECT COUNT(*) FROM ($dataSql) temp";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRows = (int)$stmtCount->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));

$dataSql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($dataSql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| STATUS MAP
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| TEACHERS (UNCHANGED)
|--------------------------------------------------------------------------
*/

// --- Get teachers and their classes in the active SY (if any)


// --- JSON Response

if (isset($_POST['ajax'])) {
    ob_start();
    $studentsWithHtml = '';
    // Start output buffering
    ob_start();

    if ($students) {
        $count = $offset + 1;

        foreach ($students as $user) {
            $status = strtolower($user["enrolment_status"] ?? '');
            $badgeClass = $statusText = '';
            $ssd = '';
            $ssdCol = '';

            // Status badge
            if ($status == 'active') {
                $badgeClass = 'success';
                $statusText = 'Enrolled';
            } elseif ($status == 'rejected') {
                $badgeClass = 'danger';
                $statusText = 'Rejected';
            } elseif ($status == 'transferred') {
                $badgeClass = 'info';
                $statusText = 'Transferred';
            } elseif ($status == 'dropped') {
                $badgeClass = 'danger';
                $statusText = 'Dropped';
            } else {
                $badgeClass = 'secondary';
                $statusText = 'Pending';
            }

            $user["isMovingUP"] = $user["isMovingUP"] ?? null;
            if ($user["isMovingUP"] === false) {
                $ssdCol = 'solid 1px red';
                $ssd = '<div class="art"><i class="fas fa-angle-down text-danger"></i></div>';
            } elseif ($user["isMovingUP"] === true) {
                $ssdCol = 'solid 1px green';
                $ssd = '<div class="art"><i class="fas fa-angle-up text-success"></i></div>';
            } else {
                $ssdCol = 'none';
                $ssd = '';
            }

?>
            <tr class="student-row"
                data-name="<?= htmlspecialchars(strtolower($user["lname"] . ' ' . $user["fname"])) ?>"
                data-grade="<?= htmlspecialchars(strtolower($user["gradeLevel"] ?? '')) ?>"
                data-status="<?= htmlspecialchars($status) ?>">
                <td width="5%"><?= $count++ ?></td>
                <td width="20%" class="student-name">
                    <div class="d-flex align-items-center">
                        <?= $ssd ?>
                        <div class="avatar-placeholder me-2" style="border: <?= $ssdCol ?>;">
                            <i class="fa-solid fa-user-graduate text-secondary"></i>
                        </div>
                        <div>
                            <strong><?= htmlspecialchars($user["lname"] . ', ' . $user["fname"]) ?></strong>
                            <?php if (!empty($user["mname"])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($user["mname"]) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td width="15%"><span class="badge bg-info"><?= htmlspecialchars($user["gradeLevel"] ?? 'Not set') ?></span></td>
                <td width="15%"><span class="badge bg-<?= $badgeClass ?>"><i class="fa-solid fa-circle fa-xs me-1"></i><?= $statusText ?></span>
                    <?php if ($systatus === 0) {
                        if ($iddf != $user['school_year_id']) { ?>- Ended<?php }
                                                                                        } else {
                                                                                            if ($systatus === 2) { ?>- Ended<?php }
                                                                                                                                    } ?>
                </td>
                <td width="20%"><?= !empty($user["enrolled_date"]) ? '<small>' . date('M d, Y', strtotime($user["enrolled_date"])) . '</small>' : '<small class="text-muted">Not enrolled yet</small>' ?></td>
                <td width="25%">
                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <a href="index.php?page=contents/form&student_id=<?= htmlspecialchars($user["student_id"]) ?>" class="btn btn-sm btn-info" title="View Enrollment Form"><i class="fa-solid fa-file-lines me-1"></i> Form</a>
                        <?php if ($status != 'active' && $status != 'rejected'): ?>
                            <button onclick="approvebtn(<?= htmlspecialchars($user['student_id']) ?>, '<?= htmlspecialchars($user['gradeLevel'], ENT_QUOTES) ?>')" type="button" class="btn btn-success btn-sm open-enrolment" data-id="<?= htmlspecialchars($user["student_id"]) ?>" data-gradelevel="<?= htmlspecialchars($user["gradeLevel"]) ?>" title="Approve Enrollment"><i class="fa-solid fa-check me-1"></i> Approve</button>
                        <?php endif; ?>
                        <?php if ($status != 'rejected' && $status != 'active'): ?>
                            <button onclick="rjkbtn(<?= htmlspecialchars($user['student_id']) ?>)" type="button" class="btn btn-danger btn-sm open-rejection" data-id="<?= htmlspecialchars($user["student_id"]) ?>" title="Reject Enrollment"><i class="fa-solid fa-xmark me-1"></i> Reject</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php
        }

        $bt = "";
        if ($page > 1) {
            $bt = '<button class="btn btn-sm btn-secondary" onclick="fetchStudents(' . ($page - 1) . ')">Prev</button>';
        }
        if ($page < $totalPages) {
            $bt .= '<button class="btn btn-sm btn-secondary" onclick="fetchStudents(' . ($page + 1) . ')">Next</button>';
        }
        ?>
        <tr>
            <td colspan="6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Page <?= $page ?> of <?= $totalPages ?></span>
                    <div>
                        <?= $bt ?>
                    </div>
                </div>
            </td>
        </tr>
<?php
    }

    $studentsWithHtml = ob_get_clean();

    echo json_encode([
        'hasData' => !empty($students),
        'stats'   => $stat,
        'html'    => $studentsWithHtml
    ]);

    exit;
}

?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-folder me-2"></i>Enrollment Management</h4>
    </div>
</div>
<style>
    .art {
        margin-right: .5rem;
    }
</style>
<div class="row g-3 scroll-classes">
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-12">
            <div class="row input-group">
                <div class="col-md-4">
                    <input type="text" id="searchInput" name="search" class="form-control"
                        placeholder="Search by name, grade level, or enrollment status...">
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" name="statusCategory" class="form-select" style="max-width: 200px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Enrolled</option>
                        <option value="transferred">Transferred</option>
                        <option value="dropped">Dropped</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="gradeFilter" name="gradeLevelCategory" class="form-select" style="max-width: 200px;">
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
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Enrollment Statistics</h5>
                    <div class="row text-center">

                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 id="ts" class="text-primary mb-1"><?= $stat['total_students'] ?></h3>
                                <small class="text-white">Total Students</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 id="en" class="text-primary mb-1"><?= $stat['enrolled'] ?></h3>
                                <small class="text-white">Enrolled</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 id="pn" class="text-primary mb-1"><?= $stat['pending'] ?></h3>
                                <small class="text-white">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 id="rd" class="text-primary mb-1">
                                    <?= $stat['rejected'] ?>
                                </h3>
                                <small class="text-white">Rejected/Dropped</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    <!-- Students Table -->
    <div class="table-container-wrapper p-0">
        <!-- Fixed Header -->

        <!-- Scrollable Body -->
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 2.5rem;">#</th>
                        <th>Name</th>
                        <th>Grade Level</th>
                        <th>Enrollment Status</th>
                        <th>Enrolled at</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="enrollmentTableBody">
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No students found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>
        </div>
    </div>
</div>

<!-- enrolment modal -->
<div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="AddNewAccountLabel">
                    <i class="fa-solid fa-user-check me-2"></i>Approve Student Enrolment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="enrolment-form" method="post">
                    <input type="hidden" name="student_id" id="student_id" value="">

                    <div class="col-md-6">
                        <label class="form-label">Class Adviser <span class="text-danger">*</span></label>
                        <select name="adviser_id" id="adviserSelect" class="form-select" required>
                            <option value="">Select Adviser</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Section <span class="text-danger">*</span></label>
                        <div class="form-control bg-light" id="section_name"></div>
                        <input type="hidden" name="section_name" id="section_name_hidden">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">School Year <span class="text-danger">*</span></label>
                        <div id="syd" class="form-control bg-light">Not set</div>
                        <input id="syid" type="hidden" name="schoolyear_id" value="">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <div class="form-control bg-light" id="gradeLevelDisplay"></div>
                        <input type="hidden" id="gradeLevelValue" name="grade_level">
                    </div>

                    <div class="col-12">
                        <div class="card mt-3 border">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0"><i class="fa-solid fa-book me-2"></i>Subjects for this Grade
                                    Level</h6>
                            </div>
                            <div class="card-body">
                                <div id="subjectListContainer" class="row">
                                    <p class="text-muted text-center">Select a student to view their subjects</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fa-solid fa-check me-2"></i>Approve Enrolment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- rejection modal -->
<div class="modal fade" id="rejectEnrolment" tabindex="-1" aria-labelledby="rejectEnrolmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="rejectEnrolmentLabel">
                    <i class="fa-solid fa-user-xmark me-2"></i>Reject Enrollment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="rejectEnrolment-form" method="post">
                    <input type="hidden" name="studentID" id="studentID">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                        <h5>Confirm Rejection</h5>
                        <p class="text-muted">Are you sure you want to reject this student's enrollment?</p>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fa-solid fa-xmark me-2"></i>Confirm Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let selectedStudentId = null;
    let sections = {};


    function rjkbtn(studentId) {
        selectedStudentId = studentId;
        document.getElementById('studentID').value = studentId;

        const modal = new bootstrap.Modal(
            document.getElementById('rejectEnrolment')
        );
        modal.show();
    }

    function approvebtn(studentId, gradeLevel = '') {
        selectedStudentId = studentId;

        document.getElementById('student_id').value = studentId;
        // document.getElementById('gradeLevelDisplay').textContent = gradeLevel;
        // document.getElementById('gradeLevelValue').value = gradeLevel;

        loadSubjectsByGrade(gradeLevel);

        const modal = new bootstrap.Modal(
            document.getElementById('AddNewAccount')
        );
        modal.show();
    }

    function loadSubjectsByGrade(gradeLevel) {
        const container = document.getElementById('subjectListContainer');
        const advisers = document.getElementById('adviserSelect');
        container.innerHTML = `
        <div class="text-center text-muted py-3">
            <div class="spinner-border spinner-border-sm"></div>
            Loading subjects...
        </div>
    `;

        const formData = new FormData();
        formData.append('ajax2', 1);
        formData.append('grade_level', gradeLevel);

        fetch('contents/enrolment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                container.innerHTML = '';

                document.getElementById('gradeLevelValue').value = data.grade_level ?? '';
                document.getElementById('gradeLevelDisplay').textContent = data.grade_level ?? '';
                document.getElementById('syid').value = data.school_year_id ?? '';
                document.getElementById('syd').textContent = data.school_year_name ?? 'Not set';
                sections = data.sectionMap ?? {};

                advisers.innerHTML = `<option value="">Select Adviser</option>`;
                data.teachers.forEach(t => {
                    const option = document.createElement('option');
                    option.value = t.adviser_id;
                    option.textContent = `${t.lastname}, ${t.firstname} - (${t.student_count ?? 0}/50)`;

                    if ((t.student_count ?? 0) >= 50) {
                        option.disabled = true;
                        option.style.color = 'red';
                        option.textContent += ' (FULL)';
                    }

                    advisers.appendChild(option);
                });

                advisers.addEventListener('change', () => {
                    const selectedOption = advisers.selectedOptions[0];
                    if (selectedOption.disabled) {
                        advisers.value = '';
                        alert('This teacher already has 50 students.');
                    }
                });

                // Populate subjects
                if (!data.subjects || data.subjects.length === 0) {
                    container.innerHTML = `
                <p class="text-muted text-center">
                    No subjects found for this grade
                </p>
            `;
                    return;
                }

                data.subjects.forEach(sub => {
                    container.innerHTML += `
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-2">
                        <strong>${sub.subject_name}</strong>
                        <div class="small text-muted">
                            ${sub.subject_code ?? ''}
                        </div>
                        <input type="hidden" name="subjects[]" value="${sub.subject_id ?? ''}">
                    </div>
                </div>
            `;
                });

            })
            .catch(() => {
                container.innerHTML = `
            <p class="text-danger text-center">
                Failed to load subjects
            </p>
        `;
            });
    }

    document.getElementById('adviserSelect')?.addEventListener('change', function() {
        const adviserId = this.value;

        if (!adviserId || !sections[adviserId]) {
            document.getElementById('section_name').textContent = '';
            document.getElementById('section_name_hidden').value = '';
            return;
        }

        const section = sections[adviserId];

        document.getElementById('section_name').textContent = section;
        document.getElementById('section_name_hidden').value = section;
    });

    /* =========================
       UPDATE STATS
    ========================= */
    function updateStats(stats) {
        document.getElementById('ts').textContent = stats.total_students ?? 0;
        document.getElementById('en').textContent = stats.enrolled ?? 0;
        document.getElementById('pn').textContent = stats.pending ?? 0;
        document.getElementById('rd').textContent = stats.rejected ?? 0;
    }

    /* =========================
       FETCH STUDENTS (ajax)
    ========================= */
    function fetchStudents(page = 1) {
        currentPage = page;

        const tableBody = document.getElementById('enrollmentTableBody');
        const noResults = document.getElementById('noResults');

        tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <div>Loading students...</div>
            </td>
        </tr>
    `;
        noResults.classList.add('d-none');

        const formData = new FormData();
        formData.append('ajax', 1);
        formData.append('search', document.getElementById('searchInput').value);
        formData.append('status', document.getElementById('statusFilter').value);
        formData.append('grade', document.getElementById('gradeFilter').value);
        formData.append('school_year', document.getElementById('syFilter').value);
        formData.append('page', page);

        fetch('contents/enrolment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.hasData) {
                    tableBody.innerHTML = '';
                    noResults.classList.remove('d-none');
                } else {
                    tableBody.innerHTML = data.html;
                }
                updateStats(data.stats);
            })
            .catch(() => {
                tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    Failed to load data
                </td>
            </tr>
        `;
            });
    }


    document.addEventListener('DOMContentLoaded', () => {
        ['searchInput', 'statusFilter', 'gradeFilter', 'syFilter']
        .forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener('change', () => fetchStudents(1));
            el.addEventListener('input', () => fetchStudents(1));
        });

        fetchStudents(1);
    });
</script>


<style>
    .bg-plo {
        background-color: #ffa200 !important;
        color: #fff;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
        color: #fff;
    }

    .me-1 {
        margin-right: 0 !important;
    }

    .scroll-classes {
        height: 80vh;
        overflow-y: scroll;
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

    .card {
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .list-group-item {
        border-left: 0;
        border-right: 0;
    }

    .list-group-item:first-child {
        border-top: 0;
    }

    .list-group-item:last-child {
        border-bottom: 0;
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>