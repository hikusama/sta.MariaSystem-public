<?php
require_once __DIR__ . '/../../../tupperware.php';

$result = checkURI('parent', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(403);
    exit;
}

// Handle SF9 file existence check
if (isset($_POST['check_sf9'])) {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $school_year_name = trim($_POST['school_year_name'] ?? '');

    // Get student info
    $stmtStudent = $pdo->prepare("SELECT lrn, fname, lname, gradeLevel FROM student WHERE student_id = :student_id LIMIT 1");
    $stmtStudent->execute([':student_id' => $student_id]);
    $student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $lrn = $student['lrn'];
        $fname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student['fname']));
        $lname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student['lname']));
        $grade = str_replace(" ", "", strtolower($student['gradeLevel']));
        $safeSy = preg_replace('/[^A-Za-z0-9_-]/', '', $school_year_name);

        $reportFile = BASE_PATH . "/sf9_files/{$lrn}_{$fname}_{$lname}_{$grade}_{$safeSy}.xlsx";
        $file_exists = file_exists($reportFile);

        header('Content-Type: application/json');
        echo json_encode(['file_exists' => $file_exists]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['file_exists' => false]);
    exit;
}

$activeSyStmt = $pdo->prepare("
    SELECT school_year_id
    FROM school_year
    WHERE school_year_status = 'Active'
    LIMIT 1
");
$activeSyStmt->execute();
$activeSyId = (int)$activeSyStmt->fetchColumn();


$gradeFilter = $_POST['gradeFilter'] ?? '';
$syFilter    = isset($_POST['syFilter']) ? (int)$_POST['syFilter'] : $activeSyId;
$search      = trim($_POST['search'] ?? '');

$page     = max(1, (int)($_POST['page'] ?? 1));
$perPage  = 25;
$offset   = ($page - 1) * $perPage;

/* ===============================
   BUILD WHERE CLAUSE
================================ */
$where  = [];
$params = [];

// Guardian restriction
$where[] = "s.guardian_id = :user_id";
$params['user_id'] = $user_id;

// Grade filter
if ($gradeFilter !== '') {
    $where[] = "s.gradeLevel = :grade";
    $params['grade'] = $gradeFilter;
}

// 🔥 CORE SY LOGIC (THIS IS THE IMPORTANT PART)
if ($syFilter === $activeSyId) {

    // ACTIVE SY:
    // enrolled in active SY OR pending (regardless of enrolment)
    $where[] = "(
        e.school_year_id = :sy
        OR s.enrolment_status = 'pending'
    )";

    $params['sy'] = $syFilter;
} else if ($syFilter) {
    $where[] = "e.school_year_id = :sy";
    $params['sy'] = $syFilter;
}


// Search
if ($search !== '') {
    $where[] = "(
        s.fname LIKE :search
        OR s.mname LIKE :search
        OR s.lname LIKE :search
        OR s.lrn LIKE :search
    )";
    $params['search'] = "%$search%";
}

$whereSQL = implode(' AND ', $where);

/* ===============================
   COUNT (FOR PAGINATION)
================================ */
$countStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT s.student_id)
    FROM student s
    LEFT JOIN enrolment e ON s.student_id = e.student_id
    WHERE $whereSQL
");
$countStmt->execute($params);
$totalStudents = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalStudents / $perPage));

/* ===============================
   FETCH STUDENTS
================================ */
$studentsStmt = $pdo->prepare("
    SELECT
        s.*,
        e.enrolment_id,
        e.school_year_id,
        e.section_name,
        e.adviser_id,
        sy.school_year_name,
        CASE WHEN EXISTS (
            SELECT 1
            FROM enrolment e2
            JOIN school_year sy2
            ON sy2.school_year_id = e2.school_year_id
            WHERE e2.student_id = s.student_id
            AND sy2.school_year_status = 'Active'
        )
        THEN 1
        ELSE 0
    END AS is_enrolled_active_cycle
    FROM student s
    LEFT JOIN enrolment e ON s.student_id = e.student_id
    LEFT JOIN school_year sy ON e.school_year_id = sy.school_year_id
    WHERE $whereSQL
    ORDER BY s.student_id DESC
    LIMIT :offset, :perpage
");

foreach ($params as $key => $val) {
    $studentsStmt->bindValue(":$key", $val);
}

$studentsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$studentsStmt->bindValue(':perpage', $perPage, PDO::PARAM_INT);

$studentsStmt->execute();
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);


// Handle AJAX request
if (isset($_POST['ajax'])) {
    ob_start();
    if (empty($students)): ?>
        <div class="col-12">
            <div class="card border-0 shadow text-center py-5" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="fa-solid fa-users-slash fa-4x" style="color: #6c757d;"></i>
                    </div>
                    <h4 class="mb-3">No Learners Found</h4>
                    <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#AddNewAccount"
                        style="background: linear-gradient(135deg, #e74a3b, #be2617); border: none;">
                        <i class="fa fa-plus me-2"></i> Add Learner
                    </button>
                </div>
            </div>
        </div>
        <?php else:
        foreach ($students as $student):
            // Calculate animation delay
            static $delay = 0.1;
            $nxtlvl = filter_var($student['gradeLevel'], FILTER_SANITIZE_NUMBER_INT);
            // $student['gradeLevel'] = 'Grade 6';
            $isup = $student['isMovingUP'];
            $isopen = $student['is_enrolled_active_cycle'];
            // $isup = true;
            if ($isopen === 0) {
                $student["enrolment_status"] = 'Not active';
            }
        ?>
            <div class="col-xl-4 col-lg-6 col-md-6 mb-4 student-card s55" style="animation-delay: <?= $delay ?>s">
                <div class="card border-0 shadow h-100">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <!-- Profile Picture -->
                            <div class="col-md-5 text-center mb-3 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <?php if ($student["student_profile"] !== ''): ?>
                                        <img src="../../authentication/uploads/<?php echo htmlspecialchars($student["student_profile"]); ?>"
                                            class="img-fluid" alt="Profile Picture">
                                    <?php else: ?>
                                        <img src="../../assets/image/users.png" class="img-fluid" alt="Default Profile">
                                    <?php endif; ?>

                                    <!-- LRN Badge -->
                                    <div class="position-absolute bottom-0 end-0 bg-dark text-white rounded-pill px-2 py-1 lrr"
                                        style="font-size: 10px; transform: translate(5px, 5px); white-space: nowrap;">
                                        LRN: <?= htmlspecialchars($student["lrn"]) ?>
                                    </div>
                                </div>
                                <div class="rsyr">
                                    <?php if ($isup === 1 && $isup !== null && $student["gradeLevel"] == 'Grade 6' && $isopen === 0) { ?>
                                        <div id="passed">
                                            <p style="margin-top: .5rem;">Your student has completed <?= $student['gradeLevel'] ?> and is eligible to graduate.</p>
                                        </div>
                                    <?php } elseif ($isup === 1 && $isup !== null && $isopen === 0) { ?>
                                        <div id="passed">
                                            <button onclick="enroll('enrollstud',<?= $student['student_id'] ?>,<?= $nxtlvl ?>)">Enroll</button>
                                            <p>Your student passed <?= $student['gradeLevel'] ?> and is eligible to enroll for Grade <?= $nxtlvl + 1 ?>.</p>
                                        </div>
                                    <?php } elseif ($isup === 0 && $isup !== null && $isopen === 0) { ?>
                                        <div id="fail">
                                            <button onclick="enroll('reenrollstud',<?= $student['student_id'] ?>)">Re-Enroll</button>
                                            <p>Your student failed <?= $student['gradeLevel'] ?> and must re-enroll again.</p>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- Student Information -->
                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-0 text-dark">
                                        <?= htmlspecialchars($student["fname"]) . " " .
                                            htmlspecialchars(substr($student["mname"], 0, 1)) . ". " .
                                            htmlspecialchars($student["lname"]) ?>
                                    </h5>
                                    <span class="status-badge <?=
                                                                $student["enrolment_status"] == 'active' ? 'status-active' : ($student["enrolment_status"] == '' ? 'status-pending' : 'status-inactive')
                                                                ?>">
                                        <?= $student["enrolment_status"] == '' ? 'Pending' : htmlspecialchars(ucfirst($student["enrolment_status"])) ?>
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa-solid fa-graduation-cap me-2 text-primary" style="width: 16px;"></i>
                                        <span>Grade Level: <strong><?= htmlspecialchars($student["gradeLevel"]) ?></strong></span>
                                    </div>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa-solid fa-cake-candles me-2 text-danger" style="width: 16px;"></i>
                                        <span>Birthday: <strong><?= htmlspecialchars(date('M d, Y', strtotime($student["birthdate"]))) ?></strong></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-venus-mars me-2 text-success" style="width: 16px;"></i>
                                        <span>Sex: <strong><?= htmlspecialchars($student["sex"]) ?></strong></span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 mt-3 pt-3 border-top fltr">
                                    <?php if ($student['enrolment_id']): ?>
                                        <a href="index.php?page=contents/profile&student_id=<?= htmlspecialchars($student["student_id"]) ?>&school_year_name=<?= htmlspecialchars($student['school_year_name'] ?? '') ?>"
                                            class="flex-fill">
                                            <button class="btn btn-action btn-profile w-100">
                                                <i class="fa-solid fa-user me-1"></i> Profile
                                            </button>
                                        </a>
                                    <?php endif; ?>
                                    <a href="index.php?page=contents/form&student_id=<?= htmlspecialchars($student["student_id"]) ?>"
                                        class="flex-fill">
                                        <button class="btn btn-action btn-form w-100">
                                            <i class="fa-solid fa-file-lines me-1"></i> Form
                                        </button>
                                    </a>

                                    <?php
                                    // Construct report card filename with school year
                                    $lrn = $student["lrn"];
                                    $fname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student["fname"]));
                                    $lname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student["lname"]));
                                    $grade = str_replace(" ", "", strtolower($student["gradeLevel"]));

                                    // Get active school year for initial check
                                    $activeSyStmt = $pdo->prepare("SELECT school_year_name FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
                                    $activeSyStmt->execute();
                                    $activeSyName = $activeSyStmt->fetchColumn();
                                    $safeSy = preg_replace('/[^A-Za-z0-9_-]/', '', $activeSyName);

                                    $reportFile = BASE_PATH . "/sf9_files/{$lrn}_{$fname}_{$lname}_{$grade}_{$safeSy}.xlsx";

                                    if (file_exists($reportFile)) {
                                        $webPath = BASE_PATH . "/sf9_files/{$lrn}_{$fname}_{$lname}_{$grade}_{$safeSy}.xlsx";
                                    ?>
                                        <style>
                                            .report-button-container {
                                                display: flex;
                                                gap: 0;
                                                width: 100%;
                                                border-radius: 8px;
                                                overflow: hidden;
                                            }

                                            #syFilteree,
                                            [id^="syFilteree_"] {
                                                flex: 1;
                                                padding: 10px 14px;
                                                border: none;
                                                background: linear-gradient(135deg, rgba(28, 200, 138, 0.9), rgba(19, 133, 92, 0.9));
                                                color: white;
                                                font-size: 13px;
                                                font-weight: 500;
                                                cursor: pointer;
                                                outline: none;
                                                transition: all 0.3s ease;
                                            }

                                            #syFilteree option,
                                            [id^="syFilteree_"] option {
                                                background: #1cc88a;
                                                color: white;
                                                font-weight: 500;
                                            }

                                            #syFilteree:hover,
                                            [id^="syFilteree_"]:hover {
                                                background: linear-gradient(135deg, #1fb597, #179b80);
                                                box-shadow: 0 2px 8px rgba(28, 200, 138, 0.3);
                                            }

                                            #syFilteree:focus,
                                            [id^="syFilteree_"]:focus {
                                                background: linear-gradient(135deg, #1fb597, #179b80);
                                                box-shadow: 0 0 0 3px rgba(28, 200, 138, 0.2);
                                            }

                                            .report-link-wrapper {
                                                display: none;
                                                flex: 0.8;
                                                padding: 10px 14px;
                                                background: linear-gradient(135deg, rgba(28, 200, 138, 0.9), rgba(19, 133, 92, 0.9));
                                                color: white;
                                                text-decoration: none;
                                                border-left: 1px solid rgba(255, 255, 255, 0.2);
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                font-size: 13px;
                                                font-weight: 500;
                                                transition: all 0.3s ease;
                                            }

                                            .report-link-wrapper.show {
                                                display: flex;
                                            }

                                            .report-link-wrapper:hover {
                                                background: linear-gradient(135deg, #1fb597, #179b80);
                                                text-decoration: none;
                                                color: white;
                                                box-shadow: 0 2px 8px rgba(28, 200, 138, 0.3);
                                            }

                                            .report-link-wrapper i {
                                                transition: transform 0.3s ease;
                                            }

                                            .report-link-wrapper:hover i {
                                                transform: scale(1.1);
                                            }

                                            .btn-report {
                                                padding: 0 !important;
                                                display: flex !important;
                                                gap: 0 !important;
                                                font-size: 0.9rem !important;
                                                align-items: center;
                                                height: 38px;
                                                background: linear-gradient(135deg, #1cc88a, #13855c) !important;
                                                border: none !important;
                                                overflow: hidden;
                                                transition: all 0.3s ease;
                                            }

                                            .btn-report:hover {
                                                box-shadow: 0 4px 12px rgba(28, 200, 138, 0.3) !important;
                                                transform: translateY(-2px);
                                            }
                                        </style>
                                        <div class="btn btn-action btn-report w-100">
                                            <select id="syFilteree_<?= $student['student_id'] ?>" name="school_yearee" onchange="updateReportLink(this, <?= $student['student_id'] ?>)">
                                                <?php
                                                $catStmt11 = $pdo->query("
                                                SELECT school_year_id, school_year_name, school_year_status
                                                FROM school_year
                                                ORDER BY 
                                                    CASE WHEN school_year_status = 'Active' THEN 0 ELSE 1 END,
                                                    school_year_name ASC
                                            ");
                                                $schoolYears11 = [];
                                                $activeSyName = null;
                                                while ($cat = $catStmt11->fetch(PDO::FETCH_ASSOC)) {
                                                    if ($cat['school_year_status'] === 'Active' && $activeSyName === null) {
                                                        $activeSyName = $cat['school_year_name'];
                                                    }
                                                    $schoolYears11[] = $cat;
                                                }
                                                ?>
                                                <?php foreach ($schoolYears11 as $sy): ?>
                                                    <option value="<?= htmlspecialchars($sy['school_year_name']) ?>"
                                                        <?= ($sy['school_year_status'] === 'Active') ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($sy['school_year_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <a href="javascript:void(0);" class="report-link-wrapper show" id="reportLink_<?= $student['student_id'] ?>"
                                                data-student-id="<?= $student['student_id'] ?>" data-active-sy="<?= htmlspecialchars($activeSyName) ?>">
                                                <i class="fa-solid fa-file-excel me-1"></i> Report
                                            </a>
                                        </div>
                                    <?php } else { ?>
                                        <button class="btn btn-action w-100" disabled style="background: #000000;">
                                            <i class="fa-solid fa-file-excel me-1"></i> No Report File
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            $delay += 0.1;
        endforeach;
        ?>
        <div class="col-12 d-flex justify-content-between mt-3">
            <?php if ($page > 1): ?>
                <button class="btn btn-sm btn-secondary btn-prev">Prev</button>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <button class="btn btn-sm btn-secondary btn-next">Next</button>
            <?php endif; ?>
        </div>
<?php
    endif;
    $html = ob_get_clean();
    echo json_encode(['html' => $html, 'currentPage' => $page, 'totalPages' => $totalPages]);
    exit;
}
?>


<style>
    .fltr {
        flex-wrap: wrap;

    }

    .s55 {
        background: linear-gradient(312deg, #e7e7e7, #ffffff) !important;
        box-shadow: -17px 17px 34px #949494,
            17px -17px 34px #ffffff !important;
        height: 19rem;
    }

    .fltr a button {
        margin: 0 !important;
    }

    .rsyr button {
        padding: .4rem 3rem;
        border: solid 1px #595959;
        border-radius: .5rem;
        margin: 0.7rem 0 .1rem;
    }

    #fail button {
        color: #e9e9e9;
        background-color: #a91818;
    }

    #passed button {
        color: #000000;
        background-color: #1fb597;
    }

    .rsyr button:hover {
        opacity: 80%;
    }

    img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Card Styling */
    .student-card {
        width: 100%;
        max-width: 33rem;
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        position: relative;
        background: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    #studentsContainer {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: start;
    }

    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .student-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #e83e8c, #6f42c1);
        border-radius: 15px 15px 0 0;
    }

    /* Status Badges */
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-active {
        background: linear-gradient(135deg, #1cc88a, #13855c);
        color: white;
    }

    .status-pending {
        background: linear-gradient(135deg, #f6c23e, #dda20a);
        color: white;
    }

    .status-inactive {
        white-space: nowrap;
        background: linear-gradient(135deg, #e74a3b, #be2617);
        color: white;
    }

    /* Button Styling */
    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-profile {
        background: linear-gradient(135deg, #36b9cc, #258391);
        color: white;
    }

    .btn-form {
        background: linear-gradient(135deg, #e83e8c, #b52b6e);
        color: white;
    }

    .btn-report {
        background: linear-gradient(135deg, #1cc88a, #13855c);
        color: white;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .btn-action:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Modal Header */
    .modal-header-danger {
        background: linear-gradient(135deg, #e74a3b, #be2617);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 20px;
    }

    /* Form Styling */
    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #e83e8c;
        box-shadow: 0 0 0 0.2rem rgba(232, 62, 140, 0.25);
    }

    /* Alert Box */
    .info-alert {
        background: linear-gradient(135deg, #e8f4fd, #d1ecf1);
        border: 1px solid #bee5eb;
        border-radius: 12px;
        padding: 15px;
        color: #0c5460;
    }

    /* Search Box */
    #searchInput {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 2px solid #dee2e6;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 14px;
    }

    #searchInput:focus {
        background: white;
        border-color: #e83e8c;
        box-shadow: 0 0 0 0.2rem rgba(232, 62, 140, 0.25);
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .student-card {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .student-card {
            max-width: 100%;
        }

        #studentsContainer {
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .lrr {
            width: 6rem;
            transform: translate(16px, 5px) !important;
        }

        .student-card {
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
        }

        #studentsContainer {
            gap: 15px;
            justify-content: stretch;
        }

        img {
            width: 70px;
            height: 70px;
        }

        .btn-action {
            padding: 6px 12px;
            font-size: 12px;
        }

        .d-flex.gap-2 {
            flex-wrap: wrap;
        }

        .fltr a {
            flex: 1 1 48%;
            min-width: 100px;
        }

        h1.h3 {
            font-size: 1.5rem !important;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
    }

    @media (max-width: 576px) {
        .scroll-class {
            height: auto;
        }

        .student-card {
            width: 100%;
            border-radius: 10px;
        }

        img {
            width: 60px;
            height: 60px;
        }

        .btn-action {
            padding: 8px 12px;
            font-size: 11px;
            width: 100% !important;
        }

        .fltr a {
            flex: 1 1 100%;
        }

        .d-flex.gap-2.mt-3 {
            gap: 8px !important;
        }

        h1.h3 {
            font-size: 1.25rem !important;
            margin-bottom: 0.5rem !important;
        }

        p.text-muted {
            font-size: 0.85rem;
        }

        .col-md-8 {
            max-width: 100%;
        }

        .col-md-4 {
            max-width: 100%;
            margin-top: 1rem;
        }

        #searchInput {
            padding: 10px 15px;
            font-size: 12px;
        }

        .form-control,
        .form-select {
            padding: 8px 12px;
            font-size: 14px;
        }

        .s55 {
            height: auto;
        }
    }

    .scroll-class {
        height: 80vh;
        overflow-y: scroll;
    }
</style>

<div class="container-fluid py-3 scroll-class">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="p-3 rounded" style="background: linear-gradient(135deg, #e83e8c, #6f42c1);">
                            <i class="fa-solid fa-users-gear fa-2x text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 text-gray-800">Learners Management</h1>
                        <p class="text-muted mb-0">Manage your children's profiles and enrollment</p>
                    </div>
                </div>

                <!-- School Year Badge -->
                <?php
                $stmt = $pdo->prepare("SELECT school_year_name FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
                $stmt->execute();
                $activeSY = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="p-3 rounded" style="background: linear-gradient(135deg, #6f42c1, #4e2a8c); color: white;">
                    <small class="d-block mb-1">Active School Year</small>
                    <h6 class="mb-0 fw-bold"><?= $activeSY["school_year_name"] ?? 'No Active SY' ?></h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Add Button -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="searchInput" name="search" class="form-control"
                    placeholder="Search learners by name, LRN, or grade level...">
            </div>
        </div>
        <div class="col-md-4">
            <button type="button" style="background: linear-gradient(90deg, #e83e8c, #6f42c1);" class="btn w-100 text-white" data-bs-toggle="modal" data-bs-target="#AddNewAccount"
                id="add_new" style="background: linear-gradient(135deg, #e74a3b, #be2617); border: none; padding: 12px; border-radius: 10px;">
                <i class="fa fa-plus me-2"></i> Create Learner Profile
            </button>
        </div>
    </div>
    <div class="col-md-8 d-flex gap-2 align-items-center mt-2">
        <select id="gradeFilter" name="gradeLevelCategory" class="form-select" style="max-width: 200px;">
            <option value="">All Grades</option>
            <option value="Grade 1">Grade 1</option>
            <option value="Grade 2">Grade 2</option>
            <option value="Grade 3">Grade 3</option>
            <option value="Grade 4">Grade 4</option>
            <option value="Grade 5">Grade 5</option>
            <option value="Grade 6">Grade 6</option>
        </select>

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

    <!-- Create learner profile modal -->
    <div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header-danger">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fa-solid fa-user-plus fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-white mb-0" id="AddNewAccountLabel">Create New Learner Profile</h5>
                            <small>Add a new child to your account</small>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <form class="row g-3" id="studentAcc-form" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Student LRN <span class="text-danger">*</span></label>
                                <input type="text" name="lrn" pattern="\d{12}" maxlength="12" inputmode="numeric" required
                                    class="form-control" placeholder="12-digit LRN">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Grade Level <span class="text-danger">*</span></label>
                                <select id="grlvl" required name="grade_level" class="form-select">
                                    <option value="">Select Grade Level</option>
                                    <option value="Grade 1">Grade 1</option>
                                    <option value="Grade 2">Grade 2</option>
                                    <option value="Grade 3">Grade 3</option>
                                    <option value="Grade 4">Grade 4</option>
                                    <option value="Grade 5">Grade 5</option>
                                    <option value="Grade 6">Grade 6</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Sex <span class="text-danger">*</span></label>
                                <select required name="sex" class="form-select">
                                    <option value="">Select student sex</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="firstName" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Middle Name <span class="text-danger">*</span></label>
                                <input required type="text" class="form-control" name="middleName">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="lastName" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Suffix</label>
                                <select class="form-select" name="suffix">
                                    <option value="" selected>Select suffix (optional)</option>
                                    <option value="Jr">Jr</option>
                                    <option value="Sr">Sr</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Religion <span class="text-danger">*</span></label>
                                <select required name="religion" id="religion" class="form-select">
                                    <option value="">Select Religion</option>
                                    <option value="Roman Catholic">Roman Catholic</option>
                                    <option value="Iglesia ni Cristo">Iglesia ni Cristo</option>
                                    <option value="Evangelical">Evangelical</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Seventh-day Adventist">Seventh-day Adventist</option>
                                    <option value="Aglipayan (IFI)">Aglipayan (IFI)</option>
                                    <option value="Baptist">Baptist</option>
                                    <option value="Born Again Christian">Born Again Christian</option>
                                    <option value="Jehovah's Witness">Jehovah's Witness</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Birth date <span class="text-danger">*</span></label>
                                <input required type="date" id="bdate" name="birthdate" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Birth place</label>
                                <input type="text" name="birthplace" class="form-control" placeholder="Birth Place">
                            </div>


                        </div>

                        <div class="col-12 text-center mt-3 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-5 py-2"
                                style="background: linear-gradient(135deg, #4e73df, #224abe); border: none; border-radius: 10px;">
                                <i class="fa-solid fa-user-plus me-2"></i> Create Learner Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="enrollstud" tabindex="-1" aria-labelledby="enrollstudLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="enrollstudLabel">
                        <i class="fa-solid fa-check me-2"></i>Enroll Student For Next Grade Level
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="enrollstud-form" method="post">
                        <input type="hidden" name="student_id" id="student_id_e">
                        <input type="hidden" name="student_lvl" id="student_lvl">
                        <div class="col-12 text-center mb-3">
                            <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                            <h5>Confirm Enrollment</h5>
                            <p class="text-muted">Are you sure you want to enroll this student to the next level?</p>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                                <i class="fa-solid fa-times me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa-solid fa-check me-2"></i>Enroll
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="reenrollstud" tabindex="-1" aria-labelledby="reenrollstudLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="reenrollstudLabel">
                        <i class="fa-solid fa-ban me-2"></i>Re-Enroll Student
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="reenrollstud-form" method="post">
                        <input type="hidden" name="student_id" id="student_id_r">
                        <div class="col-12 text-center mb-3">
                            <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                            <h5>Confirm Re-Enrollment</h5>
                            <p class="text-muted">Are you sure you want to Re-enroll this student?</p>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                                <i class="fa-solid fa-times me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-warning px-4">
                                <i class="fa-solid fa-ban me-2"></i>Re-Enroll
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Students Grid -->
    <div class="row mt-4" id="studentsContainer">
    </div>
</div>

<script>
    let currentPage = 1;
    let totalPages = 1;

    function updateReportLink(selectElement, studentId) {
        const schoolYearName = selectElement.value;
        const reportLink = document.getElementById(`reportLink_${studentId}`);

        if (schoolYearName) {
            // Check if file exists for this school year
            const formData = new FormData();
            formData.append('check_sf9', true);
            formData.append('student_id', studentId);
            formData.append('school_year_name', schoolYearName);

            fetch('contents/learners.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.file_exists) {
                        reportLink.href = `index.php?page=contents/sf9_view&student_id=${studentId}&school_year_name=${encodeURIComponent(schoolYearName)}`;
                        reportLink.classList.add('show');
                    } else {
                        reportLink.classList.remove('show');
                        reportLink.href = 'javascript:void(0);';
                    }
                })
                .catch(err => {
                    console.error('Error checking file:', err);
                    reportLink.classList.remove('show');
                    reportLink.href = 'javascript:void(0);';
                });
        } else {
            reportLink.classList.remove('show');
            reportLink.href = 'javascript:void(0);';
        }
    }

    function initializeReportLinks() {
        document.querySelectorAll('[id^="reportLink_"]').forEach(link => {
            const studentId = link.getAttribute('data-student-id');
            const activeSy = link.getAttribute('data-active-sy');
            const select = document.getElementById(`syFilteree_${studentId}`);

            if (select && activeSy) {
                select.value = activeSy;
                updateReportLink(select, studentId);
            }
        });
    }

    function enroll(id, studid, nxtlvl = 0) {
        document.getElementById('student_id_r').value = studid
        document.getElementById('student_id_e').value = studid
        document.getElementById('student_lvl').value = 'Grade ' + (nxtlvl + 1)
        const modal = new bootstrap.Modal(document.getElementById(id));
        modal.show();
    }

    function fetchStudents(search = '', page = 1) {
        const studentsContainer = document.getElementById('studentsContainer');
        const gradeFilter = document.getElementById('gradeFilter')?.value || '';
        const syFilter = document.getElementById('syFilter')?.value || '';

        const formData = new FormData();
        formData.append('ajax', true);
        formData.append('search', search);
        formData.append('page', page);
        formData.append('gradeFilter', gradeFilter);
        formData.append('syFilter', syFilter);

        fetch('contents/learners.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                studentsContainer.innerHTML = data.html;
                currentPage = data.currentPage;
                totalPages = data.totalPages;

                const btnPrev = studentsContainer.querySelector('.btn-prev');
                const btnNext = studentsContainer.querySelector('.btn-next');
                if (btnPrev) btnPrev.addEventListener('click', () => fetchStudents(search, currentPage - 1));
                if (btnNext) btnNext.addEventListener('click', () => fetchStudents(search, currentPage + 1));

                attachCardHover();
                attachProfilePreview();
                initializeReportLinks();
            })
            .catch(err => console.error(err));
    }

    function attachCardHover() {
        document.querySelectorAll('.student-card').forEach(card => {
            card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-8px)');
            card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
        });
    }

    function attachProfilePreview() {
        const profileUpload = document.getElementById('profileUpload');
        const profilePreview = document.getElementById('profilePreview');
        if (profileUpload && profilePreview) {
            profileUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }
    const bdate = document.getElementById('bdate');
    const grlvl = document.getElementById('grlvl');


    function updateMinDate() {
        const gradelvl = parseInt(grlvl.value.replace('Grade ', ''))
        const minAge = 6 + (gradelvl - 1)
        const today = new Date()
        const cuttoff = new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate())

        bdate.max = cuttoff.toISOString().split('T')[0]
        bdate.min = new Date(today.getFullYear() - (minAge + 1), today.getMonth(), today.getDate() + 1).toISOString().split('T')[0]
        bdate.value = ''
    }
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const gradeFilter = document.getElementById('gradeFilter');
        const syFilter = document.getElementById('syFilter');
        grlvl.addEventListener('change', updateMinDate)

        // Initial load
        fetchStudents();

        // Search
        if (searchInput) searchInput.addEventListener('keyup', () => fetchStudents(searchInput.value, 1));
        // Filters
        if (gradeFilter) gradeFilter.addEventListener('change', () => fetchStudents(searchInput.value || '', 1));
        if (syFilter) syFilter.addEventListener('change', () => fetchStudents(searchInput.value || '', 1));

        // Attach profile preview initially
        attachProfilePreview();
        initializeReportLinks();
    });
</script>