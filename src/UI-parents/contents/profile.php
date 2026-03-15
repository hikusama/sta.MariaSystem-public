<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('parent', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

// Handle AJAX attendance data request
if (isset($_POST['ajax_attendance'])) {
    header('Content-Type: application/json');
    
    $student_id = (int)($_POST['student_id'] ?? 0);
    $selectedYear = (int)($_POST['year'] ?? date('Y'));
    $school_year_name = $_POST['school_year_name'] ?? '';
    
    $attendanceData = [];
    $attendanceSummary = ['present' => 0, 'absent' => 0, 'late' => 0, 'half_day' => 0, 'half_day_late' => 0];
    
    if ($student_id) {
        // Fetch attendance records filtered by attendance_at dates (June to March)
        $attendanceQuery = "
            SELECT 
                DATE(morning_attendance) AS date,
                YEAR(attendance_at) AS att_year,
                MONTH(attendance_at) AS att_month,
                attendance_type AS morning,
                A_attendance_type AS afternoon,
                attendance_summary AS summary,
                attendance_at AS recorded_at
            FROM attendance
            WHERE student_id = :student_id
            AND (
                (YEAR(attendance_at) = :year AND MONTH(attendance_at) >= 6)
                OR (YEAR(attendance_at) = :year_plus_one AND MONTH(attendance_at) <= 3)
            )
            ORDER BY morning_attendance DESC
        ";
        $stmt = $pdo->prepare($attendanceQuery);
        $stmt->execute([
            ':student_id' => $student_id,
            ':year' => $selectedYear,
            ':year_plus_one' => $selectedYear + 1
        ]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build attendance data for calendar and count summary
        foreach ($records as $record) {
            $date = $record['date'];
            $attendanceData[$date] = [
                'morning' => $record['morning'] ?: null,
                'afternoon' => $record['afternoon'] ?: null,
                'summary' => strtolower(str_replace('-', '-', $record['summary'] ?? 'absent'))
            ];
            
            // Update summary counts
            $summary = strtolower($record['summary'] ?? '');
            if ($summary === 'present') {
                $attendanceSummary['present']++;
            } elseif ($summary === 'absent') {
                $attendanceSummary['absent']++;
            } elseif ($summary === 'late') {
                $attendanceSummary['late']++;
            } elseif ($summary === 'half-day') {
                $attendanceSummary['half_day']++;
            } elseif ($summary === 'half-day-late') {
                $attendanceSummary['half_day_late']++;
            }
        }
    }
    
    // Generate calendar HTML (June to March 10-month calendar)
    $months = [
        ['month' => 6,  'year' => $selectedYear],
        ['month' => 7,  'year' => $selectedYear],
        ['month' => 8,  'year' => $selectedYear],
        ['month' => 9,  'year' => $selectedYear],
        ['month' => 10, 'year' => $selectedYear],
        ['month' => 11, 'year' => $selectedYear],
        ['month' => 12, 'year' => $selectedYear],
        ['month' => 1,  'year' => $selectedYear + 1],
        ['month' => 2,  'year' => $selectedYear + 1],
        ['month' => 3,  'year' => $selectedYear + 1],
    ];
    
    $calendarHtml = '';
    foreach ($months as $m) {
        $monthIndex = $m['month'];
        $year = $m['year'];
        $monthName = date("F", mktime(0, 0, 0, $monthIndex, 1));
        $daysInMonth = date("t", mktime(0, 0, 0, $monthIndex, 1, $year));
        $firstDay = date("w", mktime(0, 0, 0, $monthIndex, 1, $year));
        
        // Count days recorded in this month
        $daysRecorded = 0;
        foreach ($attendanceData as $date => $record) {
            if (date('Y-m', strtotime($date)) == sprintf("%04d-%02d", $year, $monthIndex)) {
                $daysRecorded++;
            }
        }
        
        $calendarHtml .= '<div class="month-card mb-5">';
        $calendarHtml .= '<h6 class="fw-semibold mb-3 d-flex justify-content-between align-items-center">';
        $calendarHtml .= '<span>' . $monthName . ' ' . $year . '</span>';
        $calendarHtml .= '<small class="text-muted fw-normal">' . $daysRecorded . ' days recorded</small>';
        $calendarHtml .= '</h6>';
        
        // Day headers
        $calendarHtml .= '<div class="days-header mb-2"><div class="d-flex">';
        foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day) {
            $calendarHtml .= '<div class="day-header text-center" style="width: 14.28%;">' . $day . '</div>';
        }
        $calendarHtml .= '</div></div>';
        
        // Calendar grid
        $calendarHtml .= '<div class="calendar-grid mb-3"><div class="d-flex flex-wrap">';
        
        // Empty days for first week
        for ($i = 0; $i < $firstDay; $i++) {
            $calendarHtml .= '<div class="day-cell empty" style="width: 14.28%;"></div>';
        }
        
        // Days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf("%04d-%02d-%02d", $year, $monthIndex, $day);
            $cellClass = "day-cell";
            $attendanceStatus = $attendanceData[$dateStr] ?? null;
            $tooltip = "";
            
            if ($attendanceStatus) {
                $summary = strtolower($attendanceStatus['summary'] ?? '');
                switch ($summary) {
                    case 'present':
                        $cellClass .= " present";
                        $tooltip = "Present - All Day";
                        break;
                    case 'absent':
                        $cellClass .= " absent";
                        $tooltip = "Absent - All Day";
                        break;
                    case 'late':
                        $cellClass .= " late";
                        $tooltip = "Late - All Day";
                        break;
                    case 'half-day':
                        $cellClass .= " half-day";
                        $tooltip = "Half Day - " . ($attendanceStatus['morning'] == 'Present' ? 'Morning Only' : 'Afternoon Only');
                        break;
                    case 'half-day-late':
                        $cellClass .= " half-day-late";
                        $tooltip = "Half Day Late - " . ($attendanceStatus['morning'] == 'Late' ? 'Morning Only' : 'Afternoon Only');
                        break;
                    default:
                        $cellClass .= " unknown";
                        $tooltip = "Attendance recorded";
                }
            } else {
                $todayStr = date('Y-m-d');
                if ($dateStr == $todayStr) {
                    $cellClass .= " today";
                    $tooltip = "Today - No attendance record";
                }
            }
            
            $calendarHtml .= '<div class="' . $cellClass . '" style="width: 14.28%;" title="' . htmlspecialchars($tooltip) . '">' . $day . '</div>';
        }
        
        $calendarHtml .= '</div></div>'; // Close d-flex and calendar-grid
        $calendarHtml .= '</div>'; // Close month-card
    }
    
    echo json_encode(['success' => true, 'calendar' => $calendarHtml]);
    exit;
}

// Get student ID and school year name from GET
$student_id = $_GET["student_id"] ?? '';
$school_year_name = $_GET["school_year_name"] ?? '';

$query = "SELECT student.*, 
            student.student_profile AS student_profile_img,
            users.*, stuenrolmentinfo.*, parents_info.* 
          FROM student
          LEFT JOIN users ON student.guardian_id = users.user_id
          LEFT JOIN stuenrolmentinfo ON student.student_id = stuenrolmentinfo.student_id 
          LEFT JOIN parents_info ON student.student_id = parents_info.student_id 
          WHERE student.student_id = :student_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':student_id' => $student_id]);
$student_info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student_info) {
    http_response_code(404);
    exit('Student not found');
}

// Calculate age if birthdate exists
$age = '';
if (!empty($student_info["birthdate"])) {
    try {
        $birthDate = new DateTime($student_info["birthdate"]);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;
    } catch (Exception $e) {
        $age = '';
    }
}

// Initialize attendance variables
$attendanceData = [];      // Array of date => attendance record
$attendanceSummary = [     // Summary stats for sidebar
    'present' => 0,
    'absent' => 0,
    'late' => 0,
    'half_day' => 0,
    'half_day_late' => 0
];
$attendanceList = [];      // List of all attendance records

// Extract year from school_year_name (e.g., "2023-2024" -> 2023)
$selectedYear = !empty($school_year_name) ? (int)substr($school_year_name, 0, 4) : (int)date('Y');

// Fetch attendance records from database
if ($student_id) {
    // Filter attendance records by selected year (June to March of next year based on attendance_at)
    $attendanceQuery = "
        SELECT 
            DATE(morning_attendance) AS date,
            YEAR(attendance_at) AS att_year,
            MONTH(attendance_at) AS att_month,
            attendance_type AS morning,
            A_attendance_type AS afternoon,
            attendance_summary AS summary,
            attendance_at AS recorded_at
        FROM attendance
        WHERE student_id = :student_id
        AND (
            (YEAR(attendance_at) = :year AND MONTH(attendance_at) >= 6)
            OR (YEAR(attendance_at) = :year_plus_one AND MONTH(attendance_at) <= 3)
        )
        ORDER BY morning_attendance DESC
    ";
    $stmt = $pdo->prepare($attendanceQuery);
    $stmt->execute([
        ':student_id' => $student_id,
        ':year' => $selectedYear,
        ':year_plus_one' => $selectedYear + 1
    ]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build $attendanceData for calendar (keyed by date)
    // Build $attendanceList for table view
    foreach ($records as $record) {
        $date = $record['date'];
        
        // For calendar view
        $attendanceData[$date] = [
            'morning' => $record['morning'] ?: null,
            'afternoon' => $record['afternoon'] ?: null,
            'summary' => strtolower(str_replace('-', '-', $record['summary'] ?? 'absent'))
        ];
        
        // For list view
        $attendanceList[] = [
            'date' => $date,
            'morning' => $record['morning'],
            'afternoon' => $record['afternoon'],
            'summary' => $record['summary'],
            'recorded_at' => $record['recorded_at']
        ];
        
        // Update summary counts
        $summary = strtolower($record['summary'] ?? '');
        if ($summary === 'present') {
            $attendanceSummary['present']++;
        } elseif ($summary === 'absent') {
            $attendanceSummary['absent']++;
        } elseif ($summary === 'late') {
            $attendanceSummary['late']++;
        }
    }
}

// Fetch totals directly from sf9_data (alternative source)
if ($student_id && $school_year_name) {
    $totalsQuery = "
        SELECT days_present, days_late, days_absent
        FROM sf9_data
        WHERE student_id = :student_id
          AND school_year = :school_year
        LIMIT 1
    ";
    $stmt = $pdo->prepare($totalsQuery);
    $stmt->execute([
        ':student_id' => $student_id,
        ':school_year' => $school_year_name
    ]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($totals) {
        $attendanceSummary['present'] = (int)$totals['days_present'];
        $attendanceSummary['late'] = (int)$totals['days_late'];
        $attendanceSummary['absent'] = (int)$totals['days_absent'];
    }
}
?>
<!-- Main Container -->
<div class="student-profile-container">
    <!-- Header -->
    <div class="profile-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="fas fa-user-graduate me-2 text-primary"></i>Learner Profile
                </h2>
                <nav aria-label="breadcrumb" class="breadcrumb-nav">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="<?= base_url() ?>/src/UI-parents/index.php?page=contents/student" class="text-decoration-none">
                                <i class="fas fa-users me-1"></i>Learners
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="index.php?page=contents/learners" 
                   class="btn btn-outline-secondary d-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 scroll-visible" style="height: 70vh !important; overflow-y: scroll;">
        <!-- Sidebar Profile Card -->
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="profile-avatar-container mx-auto mb-3">
                            <div class="profile-avatar position-relative">
                                <?php if($student_info["student_profile_img"] != null): ?>
                                    <img src="<?= base_url() ?>/authentication/uploads/<?php echo htmlspecialchars($student_info["student_profile_img"]);?>" 
                                         class="img-fluid" style="width:180px; height: auto; border-radius: 50%;" alt="Profile Picture">
                                <?php else: ?>
                                    <img src="<?= base_url() ?>/assets/image/users.png" class="img-fluid" style="width:180px; height: auto; border-radius: 50%;" alt="Default Profile">
                                <?php endif; ?>
                                <span class="badge bg-primary position-absolute top-0 end-0 rounded-pill p-2">
                                    <i class="fas fa-graduation-cap"></i>
                                </span>
                            </div>
                        </div>
                        
                        <h4 class="fw-bold mb-1">
                            <?= htmlspecialchars($student_info["lname"] ?? '') . ', ' . 
                               htmlspecialchars($student_info["fname"] ?? '') ?>
                        </h4>
                        <p class="text-muted mb-2">
                            <?= !empty($student_info["mname"]) ? 
                               htmlspecialchars(substr($student_info["mname"], 0, 1)) . '. ' : '' ?>
                            <?= !empty($student_info["suffix"]) ? 
                               htmlspecialchars($student_info["suffix"]) : '' ?>
                        </p>
                        
                        <div class="mb-3">
                            <div class="badge bg-info text-dark fs-6">
                                <i class="fas fa-id-card me-1"></i>
                                LRN: <?= htmlspecialchars($student_info["lrn"] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($student_info["firstname"])): ?>
                        <div class="text-muted">
                            <i class="fas fa-user-shield me-1"></i>
                            Guardian: <?= htmlspecialchars($student_info["lastname"] ?? '') . ', ' . 
                                       htmlspecialchars($student_info["firstname"] ?? '') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Attendance Summary Stats -->
                    <div class="profile-stats mt-4 pt-4 border-top">
                        <h6 class="fw-semibold mb-3 text-muted">
                            <i class="fas fa-chart-line me-2"></i>Attendance Summary
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-3" style="background: #d1e7dd;">
                                    <small class="text-muted d-block">Present</small>
                                    <div class="fw-bold fs-5 text-success"><?= $attendanceSummary['present'] ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-3" style="background: #f8d7da;">
                                    <small class="text-muted d-block">Absent</small>
                                    <div class="fw-bold fs-5 text-danger"><?= $attendanceSummary['absent'] ?></div>
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="stat-card p-3 rounded-3" style="background: #fff3cd;">
                                    <small class="text-muted d-block">Late</small>
                                    <div class="fw-bold fs-5 text-warning"><?= $attendanceSummary['late'] ?></div>
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="stat-card p-3 rounded-3" style="background: #cff4fc;">
                                    <small class="text-muted d-block">Half Day</small>
                                    <div class="fw-bold fs-5 text-info"><?= $attendanceSummary['half_day'] + $attendanceSummary['half_day_late'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="quick-stats mt-4 pt-4 border-top">
                        <h6 class="fw-semibold mb-3 text-muted">
                            <i class="fas fa-info-circle me-2"></i>Quick Info
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-3 bg-light">
                                    <small class="text-muted d-block">Grade Level</small>
                                    <div class="fw-bold fs-5">
                                        <?= htmlspecialchars($student_info["gradeLevel"] ?? 'N/A') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-3 bg-light">
                                    <small class="text-muted d-block">Age</small>
                                    <div class="fw-bold fs-5"><?= $age ?: 'N/A' ?></div>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="stat-card p-3 rounded-3 bg-light">
                                    <small class="text-muted d-block">Status</small>
                                    <?php
                                    $statusMap = [
                                        'active' => ['success', 'Enrolled'],
                                        'pending' => ['warning', 'Pending'],
                                        'transferred' => ['info', 'Transferred'],
                                        'dropped' => ['danger', 'Dropped'],
                                        'not_active' => ['secondary', 'Not Active'],
                                        'transferred_in' => ['primary', 'Transferred In'],
                                        'transferred_out' => ['info', 'Transferred Out'],
                                        'rejected' => ['danger', 'Rejected']
                                    ];
                                    $currentStatus = $student_info['enrolment_status'] ?? 'pending';
                                    $badgeClass = $statusMap[$currentStatus][0] ?? 'secondary';
                                    $label = $statusMap[$currentStatus][1] ?? ucfirst($currentStatus);
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?> fs-6">
                                        <?= $label ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Last updated: <?= date('M d, Y', strtotime($student_info["created_date"] ?? 'now')) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-8 col-md-7">
            <!-- Tab Navigation -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <div class="d-flex nav-tabs-custom" role="tablist">
                        <button class="nav-link-tab active" id="personal-tab" data-bs-toggle="tab" 
                                data-bs-target="#personal-info" type="button">
                            <i class="fas fa-user-circle me-2"></i>Personal Info
                        </button>
                        <button class="nav-link-tab" id="attendance-tab" data-bs-toggle="tab" 
                                data-bs-target="#attendance-info" type="button">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </button>
                        <button class="nav-link-tab" id="medical-tab" data-bs-toggle="tab" 
                                data-bs-target="#medical-info" type="button">
                            <i class="fas fa-heartbeat me-2"></i>Medical
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Personal Information Tab -->
                <div class="tab-pane fade show active" id="personal-info" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="fas fa-id-card me-2 text-primary"></i>Personal Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="student-update-form" class="row g-3" enctype="multipart/form-data">
                                <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_info["student_id"] ?? '') ?>">
                                
                                <!-- Student Basic Info -->
                                <div class="col-12">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                        <i class="fas fa-user me-2"></i>Student Information
                                    </h6>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Upload / Update student profile</label>
                                    <input type="file" class="form-control" name="student_profile">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">First Name</label>
                                    <div class="input-group">
                                        <input type="text" name="fname" class="form-control"
                                               value="<?= htmlspecialchars($student_info["fname"] ?? '') ?>">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-signature text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Middle Name</label>
                                    <div class="input-group">
                                        <input type="text" name="mname" class="form-control"
                                               value="<?= htmlspecialchars($student_info["mname"] ?? '') ?>">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-signature text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Last Name</label>
                                    <div class="input-group">
                                        <input type="text" name="lname" class="form-control"
                                               value="<?= htmlspecialchars($student_info["lname"] ?? '') ?>">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-signature text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Suffix</label>
                                    <input type="text" name="suffix" class="form-control"
                                           value="<?= htmlspecialchars($student_info["suffix"] ?? '') ?>"
                                           placeholder="Jr., Sr., etc">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">LRN</label>
                                    <input readonly type="text" name="lrn" class="form-control"
                                           value="<?= htmlspecialchars($student_info["lrn"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Grade Level</label>
                                    <input readonly type="text" name="gradeLevel" class="form-control"
                                           value="<?= htmlspecialchars($student_info["gradeLevel"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="MALE" <?= ($student_info["sex"] ?? '') == 'MALE' ? 'selected' : '' ?>>Male</option>
                                        <option value="FEMALE" <?= ($student_info["sex"] ?? '') == 'FEMALE' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                                
                                <!-- Birth Information -->
                                <div class="col-12 mt-3">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                        <i class="fas fa-birthday-cake me-2"></i>Birth Information
                                    </h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Birth Date</label>
                                    <div class="input-group">
                                        <input type="date" name="birthdate" class="form-control"
                                               value="<?= htmlspecialchars($student_info["birthdate"] ?? '') ?>">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-calendar-day text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Birth Place</label>
                                    <input type="text" name="birthplace" class="form-control"
                                           value="<?= htmlspecialchars($student_info["birthplace"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Age</label>
                                    <input type="text" readonly class="form-control bg-light" 
                                           id="calculatedAge" value="<?= $age ?>">
                                </div>
                                
                                <!-- Religion & Mother Tongue -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Religion</label>
                                    <select name="religion" class="form-select">
                                        <option value="">Select Religion</option>
                                        <option value="Roman Catholic" <?= ($student_info["religion"] ?? '') == 'Roman Catholic' ? 'selected' : '' ?>>Roman Catholic</option>
                                        <option value="Iglesia ni Cristo" <?= ($student_info["religion"] ?? '') == 'Iglesia ni Cristo' ? 'selected' : '' ?>>Iglesia ni Cristo</option>
                                        <option value="Islam" <?= ($student_info["religion"] ?? '') == 'Islam' ? 'selected' : '' ?>>Islam</option>
                                        <!-- Add other options -->
                                    </select>
                                </div>
                                
                                <!-- Parent Information -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                        <i class="fas fa-users me-2"></i>Parent/Guardian Information
                                    </h6>
                                </div>
                                
                                <!-- Father's Information -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Father's First Name</label>
                                    <input type="text" name="f_firstname" class="form-control"
                                           value="<?= htmlspecialchars($student_info["f_firstname"] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Father's Middle Name</label>
                                    <input type="text" name="f_middlename" class="form-control"
                                           value="<?= htmlspecialchars($student_info["f_middlename"] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Father's Last Name</label>
                                    <input type="text" name="f_lastname" class="form-control"
                                           value="<?= htmlspecialchars($student_info["f_lastname"] ?? '') ?>">
                                </div>
                                
                                <!-- Mother's Information -->
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Mother's First Name</label>
                                    <input type="text" name="m_firstname" class="form-control"
                                           value="<?= htmlspecialchars($student_info["m_firstname"] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Mother's Middle Name</label>
                                    <input type="text" name="m_middlename" class="form-control"
                                           value="<?= htmlspecialchars($student_info["m_middlename"] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Mother's Last Name</label>
                                    <input type="text" name="m_lastname" class="form-control"
                                           value="<?= htmlspecialchars($student_info["m_lastname"] ?? '') ?>">
                                </div>
                                
                                <!-- Guardian Information -->
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Guardian's First Name</label>
                                    <input type="text" name="g_firstname" class="form-control"
                                           value="<?= htmlspecialchars($student_info["g_firstname"] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Guardian's Middle Name</label>
                                    <input type="text" name="g_middlename" class="form-control"
                                           value="<?= htmlspecialchars($student_info["g_middlename"] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Guardian's Last Name</label>
                                    <input type="text" name="g_lastname" class="form-control"
                                           value="<?= htmlspecialchars($student_info["g_lastname"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Relationship</label>
                                    <input type="text" name="g_relationship" class="form-control"
                                           value="<?= htmlspecialchars($student_info["g_relationship"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Contact Number</label>
                                    <div class="input-group">
                                        <input type="text" name="p_contact" class="form-control"
                                               value="<?= htmlspecialchars($student_info["p_contact"] ?? '') ?>">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-phone text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Address Information -->
                                <div class="col-12 mt-4">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                        <i class="fas fa-home me-2"></i>Address Information
                                    </h6>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">House No.</label>
                                    <input type="text" name="house_no" class="form-control"
                                           value="<?= htmlspecialchars($student_info["house_no"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Street</label>
                                    <input type="text" name="street" class="form-control"
                                           value="<?= htmlspecialchars($student_info["street"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Barangay</label>
                                    <input type="text" name="barnagay" class="form-control"
                                           value="<?= htmlspecialchars($student_info["barnagay"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">City</label>
                                    <input type="text" name="city" class="form-control"
                                           value="<?= htmlspecialchars($student_info["city"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Province</label>
                                    <input type="text" name="province" class="form-control"
                                           value="<?= htmlspecialchars($student_info["province"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Country</label>
                                    <input type="text" name="country" class="form-control"
                                           value="<?= htmlspecialchars($student_info["country"] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mt-3">
                                    <label class="form-label fw-semibold">Zip Code</label>
                                    <input type="text" name="zip_code" class="form-control"
                                           value="<?= htmlspecialchars($student_info["zip_code"] ?? '') ?>">
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="col-12 mt-4 pt-3 border-top">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i>Update Information
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Attendance Tab -->
                <div class="tab-pane fade" id="attendance-info" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="fas fa-calendar-check me-2 text-primary"></i>Attendance Record
                                </h5>
                                <div style="text-align: center; margin-bottom: .5rem;">
                                    <label for="yearFilter">Select Year</label>
                                    <select id="yearFilter" name="school_year" class="form-select" style="width:200px;">
                                        <?php
                                        $startYear = 2020;
                                        $currentYear = (int)date('Y');
                                        for ($year = $currentYear; $year >= $startYear; $year--):
                                        ?>
                                            <option value="<?= $year ?>" <?= $year === $selectedYear ? 'selected' : '' ?>>
                                                <?= $year ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <!-- Calendar View -->
                            <div id="calendarView">
                                <?php
                                // Calendar from June of selected year to March of next year
                                $months = [
                                    ['month' => 6,  'year' => $selectedYear],
                                    ['month' => 7,  'year' => $selectedYear],
                                    ['month' => 8,  'year' => $selectedYear],
                                    ['month' => 9,  'year' => $selectedYear],
                                    ['month' => 10, 'year' => $selectedYear],
                                    ['month' => 11, 'year' => $selectedYear],
                                    ['month' => 12, 'year' => $selectedYear],
                                    ['month' => 1,  'year' => $selectedYear + 1],
                                    ['month' => 2,  'year' => $selectedYear + 1],
                                    ['month' => 3,  'year' => $selectedYear + 1],
                                ];

                                foreach ($months as $m):
                                    $monthIndex = $m['month'];
                                    $year = $m['year'];

                                    $monthName = date("F", mktime(0, 0, 0, $monthIndex, 1));
                                    $daysInMonth = date("t", mktime(0, 0, 0, $monthIndex, 1, $year));

                                    // Get first day of the month (0=Sunday, 1=Monday, etc.)
                                    $firstDay = date("w", mktime(0, 0, 0, $monthIndex, 1, $year));

                                    // Count days recorded in this month
                                    $daysRecorded = 0;
                                    foreach ($attendanceData as $date => $record) {
                                        if (date('Y-m', strtotime($date)) == sprintf("%04d-%02d", $year, $monthIndex)) {
                                            $daysRecorded++;
                                        }
                                    }
                                ?>

                                    <div class="month-card mb-5">
                                        <h6 class="fw-semibold mb-3 d-flex justify-content-between align-items-center">
                                            <span><?= $monthName ?> <?= $year ?></span>
                                            <small class="text-muted fw-normal"><?= $daysRecorded ?> days recorded</small>
                                        </h6>

                                        <!-- Day Headers -->
                                        <div class="days-header mb-2">
                                            <div class="d-flex">
                                                <div class="day-header text-center" style="width: 14.28%;">Sun</div>
                                                <div class="day-header text-center" style="width: 14.28%;">Mon</div>
                                                <div class="day-header text-center" style="width: 14.28%;">Tue</div>
                                                <div class="day-header text-center" style="width: 14.28%;">Wed</div>
                                                <div class="day-header text-center" style="width: 14.28%;">Thu</div>
                                                <div class="day-header text-center" style="width: 14.28%;">Fri</div>
                                                <div class="day-header text-center" style="width: 14.28%;">Sat</div>
                                            </div>
                                        </div>

                                        <!-- Calendar Grid -->
                                        <div class="calendar-grid mb-3">
                                            <div class="d-flex flex-wrap">
                                                <!-- Empty days for first week -->
                                                <?php for ($i = 0; $i < $firstDay; $i++): ?>
                                                    <div class="day-cell empty" style="width: 14.28%;"></div>
                                                <?php endfor; ?>

                                                <!-- Days of the month -->
                                                <?php for ($day = 1; $day <= $daysInMonth; $day++):
                                                    $dateStr = sprintf("%04d-%02d-%02d", $year, $monthIndex, $day);
                                                    $dayOfWeek = ($firstDay + $day - 1) % 7;

                                                    // Determine cell class based on attendance
                                                    $cellClass = "day-cell";
                                                    $attendanceStatus = $attendanceData[$dateStr] ?? null;
                                                    $tooltip = "";

                                                    if ($attendanceStatus) {
                                                        $summary = strtolower($attendanceStatus['summary'] ?? '');
                                                        switch ($summary) {
                                                            case 'present':
                                                                $cellClass .= " present";
                                                                $tooltip = "Present - All Day";
                                                                break;
                                                            case 'absent':
                                                                $cellClass .= " absent";
                                                                $tooltip = "Absent - All Day";
                                                                break;
                                                            case 'late':
                                                                $cellClass .= " late";
                                                                $tooltip = "Late - All Day";
                                                                break;
                                                            case 'half-day':
                                                                $cellClass .= " half-day";
                                                                $tooltip = "Half Day - " .
                                                                    ($attendanceStatus['morning'] == 'Present' ? 'Morning Only' : 'Afternoon Only');
                                                                break;
                                                            case 'half-day-late':
                                                                $cellClass .= " half-day-late";
                                                                $tooltip = "Half Day Late - " .
                                                                    ($attendanceStatus['morning'] == 'Late' ? 'Morning Only' : 'Afternoon Only');
                                                                break;
                                                            default:
                                                                $cellClass .= " unknown";
                                                                $tooltip = "Attendance recorded";
                                                        }
                                                    } else {
                                                        // Check if today
                                                        $todayStr = date('Y-m-d');
                                                        if ($dateStr == $todayStr) {
                                                            $cellClass .= " today";
                                                            $tooltip = "Today - No attendance record";
                                                        } else {
                                                            // Future dates
                                                            $cellDate = new DateTime($dateStr);
                                                            $today = new DateTime($todayStr);
                                                            if ($cellDate > $today) {
                                                                $cellClass .= " future";
                                                                $tooltip = "Future date";
                                                            } else {
                                                                $cellClass .= " no-record";
                                                                $tooltip = "No attendance record";
                                                            }
                                                        }
                                                    }

                                                    // Weekend styling
                                                    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                                                        $cellClass .= " weekend";
                                                    }
                                                ?>

                                                    <div class="<?= $cellClass ?>" style="width: 14.28%;"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="<?= $tooltip ?>">
                                                        <?= $day ?>
                                                        <?php if ($attendanceStatus): ?>
                                                            <div class="day-indicators">
                                                                <?php if ($attendanceStatus['morning']): ?>
                                                                    <span class="indicator <?= strtolower($attendanceStatus['morning']) ?>"
                                                                        title="Morning: <?= $attendanceStatus['morning'] ?>"></span>
                                                                <?php endif; ?>
                                                                <?php if ($attendanceStatus['afternoon']): ?>
                                                                    <span class="indicator <?= strtolower($attendanceStatus['afternoon']) ?>"
                                                                        title="Afternoon: <?= $attendanceStatus['afternoon'] ?>"></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (($firstDay + $day) % 7 == 0 && $day != $daysInMonth): ?>
                                            </div>
                                            <div class="d-flex flex-wrap">
                                            <?php endif; ?>
                                        <?php endfor; ?>

                                        <!-- Empty days for last week -->
                                        <?php
                                        $remainingDays = 7 - (($firstDay + $daysInMonth) % 7);
                                        if ($remainingDays < 7) {
                                            for ($i = 0; $i < $remainingDays; $i++):
                                        ?>
                                                <div class="day-cell empty" style="width: 14.28%;"></div>
                                        <?php endfor;
                                        } ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- List View (Hidden by default) -->
                            <div id="listView" class="d-none">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Morning</th>
                                                <th>Afternoon</th>
                                                <th>Summary</th>
                                                <th>Recorded At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($attendanceList)): 
                                                // Sort by date descending
                                                usort($attendanceList, function($a, $b) {
                                                    return strtotime($b['date']) - strtotime($a['date']);
                                                });
                                                
                                                foreach ($attendanceList as $record):
                                                    $dateObj = new DateTime($record['date']);
                                                    $dayName = $dateObj->format('l');
                                                    $formattedDate = $dateObj->format('M d, Y');
                                                    $isToday = $record['date'] == date('Y-m-d');
                                            ?>
                                            <tr class="<?= $isToday ? 'table-info' : '' ?>">
                                                <td>
                                                    <?= $formattedDate ?>
                                                    <?php if ($isToday): ?>
                                                    <span class="badge bg-primary ms-1">Today</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $dayName ?></td>
                                                <td>
                                                    <?php if ($record['morning']): ?>
                                                    <span class="badge bg-<?= strtolower($record['morning']) == 'present' ? 'success' : 
                                                                           (strtolower($record['morning']) == 'absent' ? 'danger' : 'warning') ?>">
                                                        <?= $record['morning'] ?>
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">No record</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['afternoon']): ?>
                                                    <span class="badge bg-<?= strtolower($record['afternoon']) == 'present' ? 'success' : 
                                                                           (strtolower($record['afternoon']) == 'absent' ? 'danger' : 'warning') ?>">
                                                        <?= $record['afternoon'] ?>
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">No record</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['summary']): 
                                                        $summaryClass = match(strtolower($record['summary'])) {
                                                            'present' => 'success',
                                                            'absent' => 'danger',
                                                            'late' => 'warning',
                                                            'half-day' => 'info',
                                                            'half-day-late' => 'warning',
                                                            default => 'secondary'
                                                        };
                                                    ?>
                                                    <span class="badge bg-<?= $summaryClass ?>">
                                                        <?= ucfirst(str_replace('-', ' ', $record['summary'])) ?>
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">No summary</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($record['recorded_at']): ?>
                                                    <small class="text-muted">
                                                        <?= date('h:i A', strtotime($record['recorded_at'])) ?>
                                                    </small>
                                                    <?php else: ?>
                                                    <small class="text-muted">N/A</small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                                                    <h6 class="text-muted">No attendance records found</h6>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Legend -->
                            <div class="attendance-legend mt-4 p-3 bg-light rounded">
                                <h6 class="fw-semibold mb-3">Attendance Legend:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex flex-wrap gap-3 mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="legend-box present me-2"></div>
                                                <small>Present (All Day)</small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="legend-box absent me-2"></div>
                                                <small>Absent (All Day)</small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="legend-box late me-2"></div>
                                                <small>Late (All Day)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="d-flex align-items-center">
                                                <div class="legend-box half-day me-2"></div>
                                                <small>Half Day</small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="legend-box half-day-late me-2"></div>
                                                <small>Half Day Late</small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="legend-box no-record me-2"></div>
                                                <small>No Record</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Morning/Afternoon Indicators -->
                                <div class="mt-3 pt-3 border-top">
                                    <small class="d-block mb-2">Day Indicators:</small>
                                    <div class="d-flex gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="indicator present me-1" title="Present"></div>
                                            <small>Present</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="indicator absent me-1" title="Absent"></div>
                                            <small>Absent</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="indicator late me-1" title="Late"></div>
                                            <small>Late</small>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="indicator no-record me-1" title="No record"></div>
                                            <small>No record</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Tab -->
                <div class="tab-pane fade" id="medical-info" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="fas fa-heartbeat me-2 text-primary"></i>Medical Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="medical-update" class="row g-3">
                                <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_info["student_id"] ?? '') ?>">
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-weight me-1 text-muted"></i> Weight (kg)
                                    </label>
                                    <input type="number" step="0.1" class="form-control" name="weight" 
                                           value="<?= htmlspecialchars($student_info["weight"] ?? '') ?>" 
                                           placeholder="e.g., 45.5">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-ruler-vertical me-1 text-muted"></i> Height (m)
                                    </label>
                                    <input type="number" step="0.01" class="form-control" name="height" 
                                           value="<?= htmlspecialchars($student_info["height"] ?? '') ?>" 
                                           placeholder="e.g., 1.65">
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calculator me-1 text-muted"></i> Height² (m²)
                                    </label>
                                    <input type="text" class="form-control" name="height_squared" readonly
                                           value="<?= htmlspecialchars($student_info["height_squared"] ?? '') ?>">
                                </div>
                                
                                <!-- BMI Results -->
                                <div class="col-12 mt-4">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3">
                                                <i class="fas fa-chart-line me-2 text-primary"></i>BMI Calculation
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">BMI Result</label>
                                                    <input type="text" id="bm-result" readonly 
                                                           class="form-control bg-light">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">BMI Category</label>
                                                    <input type="text" id="bm-category" readonly 
                                                           class="form-control bg-light">
                                                </div>
                                            </div>
                                            
                                            <!-- BMI Chart -->
                                            <div class="mt-4">
                                                <div class="bmi-chart d-flex align-items-center mt-3">
                                                    <small class="text-muted me-2">Underweight</small>
                                                    <div class="flex-grow-1 bg-info" style="height: 20px; opacity: 0.3;"></div>
                                                    <small class="text-muted mx-2">Normal</small>
                                                    <div class="flex-grow-1 bg-success" style="height: 20px; opacity: 0.3;"></div>
                                                    <small class="text-muted mx-2">Overweight</small>
                                                    <div class="flex-grow-1 bg-warning" style="height: 20px; opacity: 0.3;"></div>
                                                    <small class="text-muted mx-2">Obese</small>
                                                    <div class="flex-grow-1 bg-danger" style="height: 20px; opacity: 0.3;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4 pt-3 border-top">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i>Update Medical Info
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<style>
/* Main Container */
.student-profile-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Header */
.profile-header {
    border-bottom: 2px solid #f1f3f5;
    padding-bottom: 1rem;
}

.breadcrumb-nav {
    --bs-breadcrumb-divider: '›';
}

/* Avatar */
.profile-avatar-container {
    position: relative;
}

.profile-avatar {
    position: relative;
}

/* Stats Cards */
.profile-stats .stat-card,
.quick-stats .stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    border: 1px solid rgba(0,0,0,0.05);
}

.profile-stats .stat-card:hover,
.quick-stats .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

/* Custom Tab Navigation */
.nav-tabs-custom {
    gap: 5px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.nav-link-tab {
    background: none;
    border: none;
    padding: 12px 20px;
    color: #6c757d;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: flex;
    align-items: center;
    position: relative;
}

.nav-link-tab:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.nav-link-tab.active {
    background-color: #e7f1ff;
    color: #0d6efd;
    font-weight: 600;
}

.nav-link-tab.active::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 3px;
    background-color: #0d6efd;
    border-radius: 3px;
}

/* Tab Content */
.tab-content {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form Styling */
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #495057;
}

.input-group .input-group-text {
    border-left: 0;
    background-color: #f8f9fa;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    background-color: #fff;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    background-color: #fff;
}

.form-control:read-only {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.bg-light {
    background-color: #f8f9fa !important;
}

/* Card Styling */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: rgba(255, 255, 255, 0.95);
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

/* Attendance Calendar Styles */
.calendar-grid {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.days-header {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 8px;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.day-header {
    font-weight: 600;
    color: #495057;
    font-size: 0.85rem;
    padding: 10px 5px;
    text-transform: uppercase;
}

.day-cell {
    height: 70px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding: 8px 5px;
    position: relative;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.day-cell:nth-child(7n) {
    border-right: none;
}

.day-cell.weekend {
    background-color: #f8f9fa;
}

.day-cell.today {
    border: 2px solid #0d6efd !important;
    background-color: #e7f1ff !important;
    font-weight: bold;
}

.day-cell:hover {
    transform: scale(1.05);
    z-index: 1;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* Attendance Status Colors */
.day-cell.present {
    background-color: #d1e7dd;
    color: #0f5132;
}

.day-cell.absent {
    background-color: #f8d7da;
    color: #842029;
}

.day-cell.late {
    background-color: #fff3cd;
    color: #664d03;
}

.day-cell.half-day {
    background-color: #cff4fc;
    color: #055160;
}

.day-cell.half-day-late {
    background-color: #ffe7cc;
    color: #663c00;
}

.day-cell.no-record {
    background-color: #f8f9fa;
    color: #6c757d;
}

.day-cell.future {
    background-color: #ffffff;
    color: #adb5bd;
    opacity: 0.7;
}

.day-cell.empty {
    background-color: #ffffff;
    border: none;
}

.day-cell.unknown {
    background-color: #e9ecef;
    color: #6c757d;
}

/* Day Indicators */
.day-indicators {
    display: flex;
    gap: 3px;
    margin-top: 3px;
}

.indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.indicator.present {
    background-color: #198754;
    border: 1px solid #0f5132;
}

.indicator.absent {
    background-color: #dc3545;
    border: 1px solid #842029;
}

.indicator.late {
    background-color: #ffc107;
    border: 1px solid #664d03;
}

.indicator.no-record {
    background-color: #6c757d;
    border: 1px solid #495057;
}

/* Legend */
.attendance-legend {
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #dee2e6;
}

.legend-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.1);
}

.legend-box.present {
    background: #d1e7dd;
}

.legend-box.absent {
    background: #f8d7da;
}

.legend-box.late {
    background: #fff3cd;
}

.legend-box.half-day {
    background: #cff4fc;
}

.legend-box.half-day-late {
    background: #ffe7cc;
}

.legend-box.no-record {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

/* Table Styling for List View */
#listView table th {
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

#listView table td {
    vertical-align: middle;
    padding: 12px 8px;
}

#listView table tr:hover {
    background-color: rgba(0,0,0,0.02);
}

/* Badge Styling */
.badge {
    padding: 0.4em 0.8em;
    font-weight: 500;
    font-size: 0.85em;
}

/* BMI Chart */
.bmi-chart {
    height: 30px;
    border-radius: 5px;
    overflow: hidden;
    background: linear-gradient(90deg, 
        #17a2b8 0%, 
        #17a2b8 18.5%, 
        #28a745 18.5%, 
        #28a745 25%, 
        #ffc107 25%, 
        #ffc107 30%, 
        #dc3545 30%, 
        #dc3545 100%);
    margin-top: 10px;
    position: relative;
}

.bmi-chart::before {
    content: '';
    position: absolute;
    left: 18.5%;
    width: 0;
    height: 100%;
    border-left: 2px dashed white;
}

.bmi-chart::after {
    content: '';
    position: absolute;
    left: 25%;
    width: 0;
    height: 100%;
    border-left: 2px dashed white;
}

.bmi-chart span {
    position: relative;
    z-index: 2;
}

/* Month Card Styling */
.month-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    border: 1px solid #e9ecef;
}

.month-card:last-child {
    margin-bottom: 0;
}

/* Button Styling */
.btn-outline-primary {
    border: 2px solid #0d6efd;
    color: #0d6efd;
    font-weight: 500;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    border: none;
    padding: 10px 30px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0b5ed7, #0a58ca);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
}

/* Animation for form submission */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-primary:active {
    animation: pulse 0.3s ease;
}

/* Loading Spinner */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Scrollbar Styling */
.scroll-visible::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.scroll-visible::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.scroll-visible::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.scroll-visible::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Responsive */
@media (max-width: 768px) {
    .nav-tabs-custom {
        flex-wrap: nowrap;
        overflow-x: auto;
    }
    
    .nav-link-tab {
        padding: 10px 15px;
        font-size: 0.9rem;
        min-width: max-content;
    }
    
    .day-cell {
        height: 50px;
        font-size: 0.8rem;
        padding: 5px 3px;
    }
    
    .day-indicators {
        gap: 2px;
    }
    
    .indicator {
        width: 6px;
        height: 6px;
    }
    
    .profile-avatar-container {
        width: 150px;
        height: 150px;
    }
    
    .month-card {
        padding: 15px;
    }
    
    .btn-primary {
        padding: 8px 20px;
        font-size: 0.9rem;
    }
}

/* Tooltip */
.tooltip {
    font-size: 0.875rem;
}

/* Form Section Headers */
h6.fw-semibold.border-bottom {
    color: #495057;
    font-weight: 600 !important;
}

/* Input Group Focus States */
.input-group:focus-within .input-group-text {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

/* Custom checkbox and radio */
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Alert Styling */
.alert {
    border: none;
    border-radius: 8px;
    padding: 15px;
}

/* Status Badges */
.badge.bg-success { background-color: #198754 !important; }
.badge.bg-danger { background-color: #dc3545 !important; }
.badge.bg-warning { background-color: #ffc107 !important; color: #000 !important; }
.badge.bg-info { background-color: #0dcaf0 !important; }
.badge.bg-primary { background-color: #0d6efd !important; }
.badge.bg-secondary { background-color: #6c757d !important; }
</style>
<script>
// Calculate age on birthdate change
document.addEventListener('DOMContentLoaded', function() {
    const birthdateInput = document.querySelector('input[name="birthdate"]');
    const ageInput = document.getElementById('calculatedAge');
    
    function calculateAge() {
        if (!birthdateInput.value) {
            ageInput.value = '';
            return;
        }
        
        const birthDate = new Date(birthdateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        ageInput.value = age;
    }
    
    calculateAge();
    birthdateInput.addEventListener('change', calculateAge);
});

// BMI Calculator
document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.querySelector('input[name="weight"]');
    const heightInput = document.querySelector('input[name="height"]');
    const heightSqInput = document.querySelector('input[name="height_squared"]');
    const bmiResult = document.getElementById('bm-result');
    const bmiCategory = document.getElementById('bm-category');
    
    function calculateBMI() {
        const weight = parseFloat(weightInput.value);
        const height = parseFloat(heightInput.value);
        
        if (!isNaN(weight) && !isNaN(height) && height > 0) {
            // Calculate height squared
            const heightSq = (height * height).toFixed(2);
            heightSqInput.value = heightSq;
            
            // Calculate BMI
            const bmi = (weight / (height * height)).toFixed(2);
            bmiResult.value = bmi;
            
            // Determine category
            let category = '';
            if (bmi < 18.5) {
                category = 'Underweight';
            } else if (bmi < 25) {
                category = 'Normal';
            } else if (bmi < 30) {
                category = 'Overweight';
            } else {
                category = 'Obese';
            }
            
            bmiCategory.value = category;
        } else {
            heightSqInput.value = '';
            bmiResult.value = '';
            bmiCategory.value = '';
        }
    }
    
    // Auto-convert height from cm to m if > 3
    heightInput.addEventListener('blur', function() {
        let value = parseFloat(this.value);
        if (!isNaN(value) && value > 3) {
            this.value = (value / 100).toFixed(2);
            calculateBMI();
        }
    });
    
    weightInput.addEventListener('input', calculateBMI);
    heightInput.addEventListener('input', calculateBMI);
    
    // Initialize BMI calculation if values exist
    if (weightInput.value || heightInput.value) {
        calculateBMI();
    }
});

// Form Submissions with AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Personal Information Form
    const personalForm = document.getElementById('displayStudentInfo');
    if (personalForm) {
        personalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;
            
            // AJAX request
            fetch('update_student.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update. Please try again.'
                });
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Medical Form
    const medicalForm = document.getElementById('medical-update');
    if (medicalForm) {
        medicalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;
            
            fetch('update_medical.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 1) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update medical info.'
                });
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Initialize Bootstrap tabs
    const triggerTabList = [].slice.call(document.querySelectorAll('.nav-link-tab'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
    
    // Year filter handler for attendance - AJAX version
    document.getElementById('yearFilter')?.addEventListener('change', function(e) {
        const selectedYear = e.target.value;
        const formData = new FormData();
        formData.append('ajax_attendance', true);
        formData.append('student_id', '<?= $student_id ?>');
        formData.append('year', selectedYear);
        formData.append('school_year_name', e.target.options[e.target.selectedIndex].text.trim());
        
        fetch('<?= base_url() ?>/src/UI-parents/contents/profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update only the calendar view
                document.getElementById('calendarView').innerHTML = data.calendar;
                
                // Reinitialize Bootstrap tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            }
        })
        .catch(error => console.error('Error loading attendance data:', error));
    });
});
</script>