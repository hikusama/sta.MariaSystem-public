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
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
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
    
    .stat-card.pink::before {
        background: linear-gradient(90deg, #e83e8c, #b52b6e);
    }
    
    .stat-card.teal::before {
        background: linear-gradient(90deg, #20c9a6, #179b80);
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
    
    .stat-icon.pink {
        background: linear-gradient(135deg, #e83e8c, #b52b6e);
    }
    
    .stat-icon.teal {
        background: linear-gradient(135deg, #20c9a6, #179b80);
    }
    
    /* Parent Info Card */
    .parent-info-card {
        background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(232, 62, 140, 0.3);
    }
    
    /* School Year Badge */
    .sy-badge {
        background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(232, 62, 140, 0.2);
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
        background: linear-gradient(180deg, #e83e8c, #6f42c1);
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
    
    .animate-card:nth-child(1) { animation-delay: 0.1s; }
    .animate-card:nth-child(2) { animation-delay: 0.2s; }
    .animate-card:nth-child(3) { animation-delay: 0.3s; }
    .animate-card:nth-child(4) { animation-delay: 0.4s; }
    
    /* Children List */
    .child-item {
        border-left: 4px solid #e83e8c;
        transition: all 0.3s ease;
    }
    
    .child-item:hover {
        transform: translateX(5px);
        background: #f8f9fa;
    }
</style>
<?php
      $studentCount = $pdo->query("SELECT COUNT(*) FROM student WHERE student.guardian_id = '$user_id' AND enrolment_status = 'active'")->fetchColumn();
      $teacherCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'TEACHER'")->fetchColumn();
      $parentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'PARENT'")->fetchColumn();
      $PresentCounts = $pdo->query("SELECT COUNT(*) FROM attendance 
      INNER JOIN student ON attendance.student_id = student.student_id
      WHERE student.guardian_id = '$user_id' AND attendance.attendance_type = 'Present'")->fetchColumn();
      $AbsentCounts = $pdo->query("SELECT COUNT(*) FROM attendance 
      INNER JOIN student ON attendance.student_id = student.student_id
      WHERE student.guardian_id = '$user_id' AND attendance.attendance_type = 'Absent'")->fetchColumn();
      $LateCounts = $pdo->query("SELECT COUNT(*) FROM attendance 
      INNER JOIN student ON attendance.student_id = student.student_id
      WHERE student.guardian_id = '$user_id' AND attendance.attendance_type = 'Late'")->fetchColumn();

      $stmt = $pdo->prepare("SELECT section_name, grade_level FROM classes WHERE adviser_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $sectionName = $stmt->fetch(PDO::FETCH_ASSOC);


      $stmt = $pdo->prepare("SELECT school_year_name FROM school_year WHERE school_year_status = 'Active'");
      $stmt->execute();
      $activeSY = $stmt->fetch(PDO::FETCH_ASSOC);
   ?>

<body class="bg-light">
    <section class="container-fluid py-4" style="max-height: 85vh; overflow-y: auto;">
        
        <!-- Welcome Header with Parent Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="h3 mb-2 text-gray-800">Parent Dashboard</h1>
                        <p class="text-muted mb-0">
                            Monitoring your children's school performance
                        </p>
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
        
        <!-- Parent Information Card -->
        <div class="row mb-4 animate-card">
            <div class="col-12">
                <div class="parent-info-card">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(255,255,255,0.2); border: 2px solid white;">
                                    <i class="fa-solid fa-users text-white"></i>
                                </div>
                                <div>
                                    <h2 class="h4 mb-1">My Children</h2>
                                    <p class="mb-0">Monitoring <?= $studentCount ?> enrolled child/children</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="d-flex justify-content-md-end gap-4">
                                <div>
                                    <h3 class="h2 mb-0"><?= $studentCount ?></h3>
                                    <small>Total Enrolled</small>
                                </div>
                                <div>
                                    <h3 class="h2 mb-0"><?= round($PresentCounts / 2) ?></h3>
                                    <small>Average Present</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Children Overview -->
        <?php if (!empty($children)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">My Children</h2>
                    <p class="text-muted">Overview of your children's status</p>
                </div>
            </div>
            
            <?php foreach ($children as $child): ?>
            <div class="col-lg-6 mb-3 animate-card">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="stat-icon pink">
                                <i class="fa-solid fa-child"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">
                                    <?= htmlspecialchars($child['firstname'] . ' ' . ($child['middlename'] ? $child['middlename'][0] . '. ' : '') . $child['lastname']) ?>
                                </h5>
                                <p class="text-muted mb-2">
                                    <?php if ($child['section_name']): ?>
                                        <?= htmlspecialchars($child['grade_level']) ?> - <?= htmlspecialchars($child['section_name']) ?>
                                    <?php else: ?>
                                        Not yet assigned to a class
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex gap-3">
                                    <small class="text-muted">
                                        <i class="fa-solid fa-id-card me-1"></i>
                                        LRN: <?= htmlspecialchars($child['lrn']) ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fa-solid fa-user-graduate me-1"></i>
                                        ID: <?= htmlspecialchars($child['student_id']) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Attendance Summary -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">Children's Attendance Summary</h2>
                    <p class="text-muted">Overall attendance performance</p>
                </div>
            </div>
            
            <!-- Total Children Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 pink">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon pink">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-pink text-uppercase mb-1">
                                    Total Children
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($studentCount) ?></div>
                                <small class="text-muted">Enrolled in school</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Present Days Card -->
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
                                    Total Present Days
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(round($PresentCounts / 2)) ?></div>
                                <div class="attendance-progress bg-light mt-2">
                                    <?php if ($studentCount > 0 && $PresentCounts > 0): ?>
                                    <?php $avgPresent = round(($PresentCounts / 2) / ($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) * 100, 1) ?>
                                    <div class="bg-success" style="width: <?= min(100, $avgPresent) ?>%; height: 100%;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Absent Days Card -->
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
                                    Total Absent Days
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(round($AbsentCounts / 2)) ?></div>
                                <div class="attendance-progress bg-light mt-2">
                                    <?php if ($studentCount > 0 && $AbsentCounts > 0): ?>
                                    <?php $avgAbsent = round(($AbsentCounts / 2) / ($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) * 100, 1) ?>
                                    <div class="bg-danger" style="width: <?= min(100, $avgAbsent) ?>%; height: 100%;"></div>
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
                                    <?php if ($studentCount > 0 && $LateCounts > 0): ?>
                                    <?php $avgLate = round(($LateCounts / 2) / ($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) * 100, 1) ?>
                                    <div class="bg-warning" style="width: <?= min(100, $avgLate) ?>%; height: 100%;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Statistics -->
        <div class="row mb-5">
            <div class="col-lg-12 mb-4">
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
                                        <h6 class="mb-1">Present Days</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format(round($PresentCounts / 2)) ?></span>
                                            <?php if (($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) > 0): ?>
                                            <span class="badge bg-success">
                                                <?= round(($PresentCounts / 2) / ($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) * 100, 1) ?>%
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
                                        <h6 class="mb-1">Absent Days</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format(round($AbsentCounts / 2)) ?></span>
                                            <?php if (($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) > 0): ?>
                                            <span class="badge bg-danger">
                                                <?= round(($AbsentCounts / 2) / ($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) * 100, 1) ?>%
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
                                        <h6 class="mb-1">Tardy Days</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format(round($LateCounts / 2)) ?></span>
                                            <?php if (($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) > 0): ?>
                                            <span class="badge bg-warning">
                                                <?= round(($LateCounts / 2) / ($PresentCounts / 2 + $AbsentCounts / 2 + $LateCounts / 2) * 100, 1) ?>%
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="p-3 rounded bg-pink bg-opacity-10">
                                            <i class="fa-solid fa-users text-pink"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Total Children</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0"><?= number_format($studentCount) ?></span>
                                            <span class="badge bg-pink">100%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <!-- <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <a href="index.php?page=contents/attendance_view" class="btn btn-pink d-flex align-items-center justify-content-between p-3">
                                <span><i class="fa-solid fa-clipboard-list me-2"></i> View Attendance</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="index.php?page=contents/children" class="btn btn-success d-flex align-items-center justify-content-between p-3">
                                <span><i class="fa-solid fa-child me-2"></i> View Children</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="index.php?page=contents/reports" class="btn btn-info d-flex align-items-center justify-content-between p-3">
                                <span><i class="fa-solid fa-chart-line me-2"></i> View Reports</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
        
        <!-- Today's Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Overall Attendance Overview</h5>
                            <span class="badge bg-dark"><?= date('F j, Y') ?></span>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="text-dark mb-1"><?= number_format($studentCount) ?></h3>
                                    <small class="text-muted">Total Children</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <h3 class="text-dark mb-1"><?= number_format(round($PresentCounts / 2)) ?></h3>
                                    <small class="text-muted">Present Days</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-danger bg-opacity-10 rounded">
                                    <h3 class="text-dark mb-1"><?= number_format(round($AbsentCounts / 2)) ?></h3>
                                    <small class="text-muted">Absent Days</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-warning bg-opacity-10 rounded">
                                    <h3 class="text-dark mb-1"><?= number_format(round($LateCounts / 2)) ?></h3>
                                    <small class="text-muted">Tardy Days</small>
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
                    <p class="mt-2 mb-0">Parent Dashboard • School Management System</p>
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
            
            document.querySelector('.last-updated').textContent = `Last updated: ${timeString}`;
        }
        
        // Update time every minute
        setInterval(updateTime, 60000);
        
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
        
        // Add CSS for pink button
        const style = document.createElement('style');
        style.textContent = `
            .btn-pink {
                background: linear-gradient(135deg, #e83e8c, #b52b6e);
                border: none;
                color: white;
            }
            .btn-pink:hover {
                background: linear-gradient(135deg, #d62d7c, #9e2560);
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(232, 62, 140, 0.3);
            }
            .bg-pink {
                background-color: #e83e8c !important;
            }
            .text-pink {
                color: #e83e8c !important;
            }
        `;
        document.head.appendChild(style);
    });
    </script>
</body>