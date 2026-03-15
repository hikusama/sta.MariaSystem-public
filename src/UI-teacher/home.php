<?php
require_once __DIR__ . '/../../tupperware.php';
$result = checkURI('teacher', 1);
if ($result['res']) {
    header($result['uri']);
    exit;
}
?>
<style>
    /* Modern Scrollbar */
    section::-webkit-scrollbar {
        width: 6px;
    }

    section::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    section::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    section::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Card Styling */
    .stat-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        position: relative;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #4e73df, #224abe);
    }

    .stat-card.primary::before {
        background: linear-gradient(90deg, #4e73df, #224abe);
    }

    .stat-card.success::before {
        background: linear-gradient(90deg, #1cc88a, #13855c);
    }

    .stat-card.info::before {
        background: linear-gradient(90deg, #36b9cc, #258391);
    }

    .stat-card.warning::before {
        background: linear-gradient(90deg, #f6c23e, #dda20a);
    }

    .stat-card.danger::before {
        background: linear-gradient(90deg, #e74a3b, #be2617);
    }

    .stat-card.secondary::before {
        background: linear-gradient(90deg, #858796, #5a5c69);
    }

    .stat-card.dark::before {
        background: linear-gradient(90deg, #5a5c69, #3a3b45);
    }

    .stat-card.purple::before {
        background: linear-gradient(90deg, #6f42c1, #4e2a8c);
    }

    .stat-card.orange::before {
        background: linear-gradient(90deg, #fd7e14, #c96a10);
    }

    /* Icon Styling */
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.primary {
        background: linear-gradient(135deg, #4e73df, #224abe);
    }

    .stat-icon.success {
        background: linear-gradient(135deg, #1cc88a, #13855c);
    }

    .stat-icon.info {
        background: linear-gradient(135deg, #36b9cc, #258391);
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, #f6c23e, #dda20a);
    }

    .stat-icon.danger {
        background: linear-gradient(135deg, #e74a3b, #be2617);
    }

    .stat-icon.secondary {
        background: linear-gradient(135deg, #858796, #5a5c69);
    }

    .stat-icon.dark {
        background: linear-gradient(135deg, #5a5c69, #3a3b45);
    }

    .stat-icon.purple {
        background: linear-gradient(135deg, #6f42c1, #4e2a8c);
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #fd7e14, #c96a10);
    }

    /* School Year Badge */
    .sy-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }

    /* Class Info Card */
    .class-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }

    /* Section Headers */
    .section-header {
        position: relative;
        padding-left: 15px;
        margin-bottom: 25px;
    }

    .section-header::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(180deg, #4e73df, #224abe);
        border-radius: 2px;
    }

    /* Attendance Progress */
    .attendance-progress {
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 20px;
        }

        .sy-badge {
            padding: 12px 20px;
        }
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

    .animate-card {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    .animate-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .animate-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .animate-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .animate-card:nth-child(4) {
        animation-delay: 0.4s;
    }
</style>
<?php
require_once __DIR__ . '/../../tupperware.php';
$result = checkURI('teacher', 1);
if ($result['res']) {
    header($result['uri']);
    exit;
}

// Get selected school year from filter (null means all school years)
$selectedSyId = isset($_GET['school_year_id']) ? (int)$_GET['school_year_id'] : null;

// Get active school year first
$stmt = $pdo->prepare("SELECT school_year_id, school_year_name FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
$stmt->execute();
$activeSY = $stmt->fetch(PDO::FETCH_ASSOC);
$activeSyId = $activeSY['school_year_id'] ?? null;

// Get all school years for dropdown filter
$stmt = $pdo->prepare("SELECT school_year_id, school_year_name, school_year_status FROM school_year ORDER BY school_year_status = 'Active' DESC, school_year_id DESC");
$stmt->execute();
$allSchoolYears = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optimized single query to get all adviser dashboard data (with optional school year filter)
$sql = "
SELECT 
    COUNT(DISTINCT e.student_id) AS student_count,
    SUM(CASE WHEN a.attendance_summary = 'Present' THEN 1 ELSE 0 END) AS present_count,
    SUM(CASE WHEN a.attendance_summary = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
    SUM(CASE WHEN a.attendance_summary = 'Late' THEN 1 ELSE 0 END) AS late_count,
    MAX(c.section_name) AS section_name,
    MAX(c.grade_level) AS grade_level
FROM enrolment e
LEFT JOIN attendance a 
    ON a.student_id = e.student_id
    AND a.adviser_id = ?
LEFT JOIN classes c
    ON c.adviser_id = ?
    AND c.sy_id = e.school_year_id
WHERE e.adviser_id = ?
";

// Add SY filter if selected
if ($selectedSyId !== null) {
    $sql .= " AND e.school_year_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $user_id, $user_id, $selectedSyId]);
} else {
    // No SY filter - get all data from all school years
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $user_id, $user_id]);
}

$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Extract data from optimized query
$activeSY = $activeSY ?? ['school_year_id' => null, 'school_year_name' => 'No Active SY'];
$studentCount = (int)($data['student_count'] ?? 0);
$PresentCounts = (int)($data['present_count'] ?? 0);
$AbsentCounts = (int)($data['absent_count'] ?? 0);
$LateCounts = (int)($data['late_count'] ?? 0);
$sectionName = [
    'section_name' => $data['section_name'] ?? null,
    'grade_level' => $data['grade_level'] ?? null
];

// Get system-wide counts (all users)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_role = 'TEACHER'");
$stmt->execute();
$teacherCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_role = 'PARENT'");
$stmt->execute();
$parentCount = $stmt->fetchColumn();
?>



<body class="bg-light">
    <section class="container-fluid py-4" style="max-height: 85vh; overflow-y: auto;">

        <!-- Welcome Header with Class Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="h3 mb-2 text-gray-800">Teacher Dashboard</h1>
                        <p class="text-muted mb-0">
                            <?php if ($sectionName && $sectionName['section_name']): ?>
                                Class Adviser: <strong><?= htmlspecialchars($sectionName['section_name']) ?></strong> |
                                Grade Level: <strong><?= htmlspecialchars($sectionName['grade_level']) ?></strong>
                            <?php else: ?>
                                No class assignment
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap qwe">
                        <!-- School Year Filter Dropdown -->
                        <div class="filter-container">
                            <label for="syFilter" class="form-label mb-0 me-2">Filter by SY:</label>
                            <select id="syFilter" class="form-select" style="width: 220px;">
                                <option value="" <?= ($selectedSyId === null) ? 'selected' : '' ?>>
                                    📊 All School Years
                                </option>
                                <?php foreach ($allSchoolYears as $sy): ?>
                                    <option value="<?= $sy['school_year_id'] ?>" <?= ($selectedSyId == $sy['school_year_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sy['school_year_name']) ?> 
                                        <?php if ($sy['school_year_status'] === 'Active'): ?>
                                            (Active)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="sy-badge">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fa-solid fa-calendar-alt fa-2x"></i>
                                <div>
                                    <small class="d-block mb-1">Active School Year</small>
                                    <h4 class="mb-0 fw-bold"><?= $activeSY["school_year_name"] ?? 'No Active SY' ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Class Information Card -->
        <?php if ($sectionName): ?>
            <div class="row mb-4 animate-card">
                <div class="col-12">
                    <div class="class-info-card">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); border: 2px solid white;">
                                        <i class="fa-solid fa-chalkboard-user text-white"></i>
                                    </div>
                                    <div>
                                        <h2 class="h4 mb-1"><?= $sectionName['section_name'] ?></h2>
                                        <p class="mb-0"><?= $sectionName['grade_level'] ?> Class Adviser</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <div class="d-flex justify-content-md-end gap-4">
                                    <div>
                                        <h3 class="h2 mb-0"><?= $studentCount ?></h3>
                                        <small>Total Students</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Activities Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">Class Activities & Attendance</h2>
                    <p class="text-muted">Overview of your class performance</p>
                </div>
            </div>

            <!-- Total Students Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 primary">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon primary">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Students
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($studentCount) ?></div>
                                <small class="text-muted">Enrolled in your class</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Present Students Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 success">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon success">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Present
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(round($PresentCounts / 2)) ?></div>
                                <div class="attendance-progress bg-light mt-2">
                                    <?php if ($studentCount > 0): ?>
                                        <div class="bg-success" style="width: <?= (int)min(100, round(($PresentCounts / 2) / $studentCount * 100)) ?>%; height: 100%;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absent Students Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 danger">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon danger">
                                    <i class="fa-solid fa-user-xmark"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Total Absent
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(round($AbsentCounts / 2)) ?></div>
                                <div class="attendance-progress bg-light mt-2">
                                    <?php if ($studentCount > 0): ?>
                                        <div class="bg-danger" style="width: <?= (int)min(100, round(($AbsentCounts / 2) / $studentCount * 100)) ?>%; height: 100%;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tardiness Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon warning">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Tardiness
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(round($LateCounts / 2)) ?></div>
                                <div class="attendance-progress bg-light mt-2">
                                    <?php if ($studentCount > 0): ?>
                                        <div class="bg-warning" style="width: <?= (int)min(100, round(($LateCounts / 2) / $studentCount * 100)) ?>%; height: 100%;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">Attendance Summary</h2>
                    <p class="text-muted">Percentage breakdown of attendance</p>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Attendance Distribution</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="me-3">
                                        <div class="p-3 rounded bg-success bg-opacity-10">
                                            <i class="fa-solid fa-user-check text-success"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Present Students</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format(round($PresentCounts / 2)) ?></span>
                                            <?php if ($studentCount > 0): ?>
                                                <span class="badge bg-success">
                                                    <?= round(($PresentCounts / 2) / $studentCount * 100, 1) ?>%
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mb-4">
                                    <div class="me-3">
                                        <div class="p-3 rounded bg-danger bg-opacity-10">
                                            <i class="fa-solid fa-user-xmark text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Absent Students</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format(round($AbsentCounts / 2)) ?></span>
                                            <?php if ($studentCount > 0): ?>
                                                <span class="badge bg-danger">
                                                    <?= round(($AbsentCounts / 2) / $studentCount * 100, 1) ?>%
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="me-3">
                                        <div class="p-3 rounded bg-warning bg-opacity-10">
                                            <i class="fa-solid fa-clock text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Tardy Students</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format(round($LateCounts / 2)) ?></span>
                                            <?php if ($studentCount > 0): ?>
                                                <span class="badge bg-warning">
                                                    <?= round(($LateCounts / 2) / $studentCount * 100, 1) ?>%
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="p-3 rounded bg-primary bg-opacity-10">
                                            <i class="fa-solid fa-graduation-cap text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Total Students</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format($studentCount) ?></span>
                                            <span class="badge bg-primary">100%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <a href="index.php?page=contents/attendance" class="btn btn-primary d-flex align-items-center justify-content-between p-3">
                                <span><i class="fa-solid fa-clipboard-list me-2"></i> Take Attendance</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="index.php?page=contents/student" class="btn btn-success d-flex align-items-center justify-content-between p-3">
                                <span><i class="fa-solid fa-graduation-cap me-2"></i> View Students</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="index.php?page=contents/datas" class="btn btn-info d-flex align-items-center justify-content-between p-3">
                                <span><i class="fa-solid fa-chart-line me-2"></i> View Reports</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Today's Overview</h5>
                            <span class="badge bg-dark"><?= date('F j, Y') ?></span>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="text-dark mb-1"><?= number_format($studentCount) ?></h3>
                                    <small class="text-muted">Class Size</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <h3 class="text-dark mb-1"><?= number_format(round($PresentCounts / 2)) ?></h3>
                                    <small class="text-muted">Present Today</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-danger bg-opacity-10 rounded">
                                    <h3 class="text-dark mb-1"><?= number_format(round($AbsentCounts / 2)) ?></h3>
                                    <small class="text-muted">Absent Today</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-warning bg-opacity-10 rounded">
                                    <h3 class="text-dark mb-1"><?= number_format(round($LateCounts / 2)) ?></h3>
                                    <small class="text-muted">Tardy Today</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="text-center text-muted">
                    <small class="last-updated">Last updated: <?= date('F j, Y, g:i a') ?></small>
                    <p class="mt-2 mb-0 today-date">Teacher Dashboard • School Management System</p>
                </div>
            </div>
        </div>

    </section>

    <script>
        // Add interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effect to all cards
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Update time every minute
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const lastUpdatedEl = document.querySelector('.last-updated');
                if (lastUpdatedEl) {
                    lastUpdatedEl.textContent = `Last updated: ${timeString}`;
                }

                const todayDateEl = document.querySelector('.today-date');
                if (todayDateEl) {
                    todayDateEl.textContent = now.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }

            // Update time every minute
            setInterval(updateTime, 60000);

            // Handle school year filter dropdown
            document.getElementById('syFilter').addEventListener('change', function() {
                const syId = this.value;
                if (syId) {
                    // Redirect to current page with school_year_id parameter
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('school_year_id', syId);
                    window.location.href = currentUrl.toString();
                } else {
                    // Clear filter
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.delete('school_year_id');
                    window.location.href = currentUrl.toString();
                }
            });

            // Add animation to quick action buttons
            const actionButtons = document.querySelectorAll('.btn-quick-action');
            actionButtons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.querySelector('.fa-arrow-right').style.transform = 'translateX(5px)';
                });

                btn.addEventListener('mouseleave', function() {
                    this.querySelector('.fa-arrow-right').style.transform = 'translateX(0)';
                });
            });
        });
    </script>
</body>