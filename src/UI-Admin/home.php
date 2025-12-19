<?php
// Your PHP code remains the same
require_once __DIR__ . '/../../tupperware.php';
$result = checkURI('admin', 1);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$studentCount = $pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();
$teacherCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'TEACHER'")->fetchColumn();
$parentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE user_role = 'PARENT'")->fetchColumn();

$classroom = $pdo->query("SELECT COUNT(*) FROM classrooms")->fetchColumn();
$sections = $pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn();
$subjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$school_year = $pdo->query("SELECT COUNT(*) FROM school_year")->fetchColumn();

$stmt = $pdo->prepare("SELECT school_year_name FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
$stmt->execute();
$activeSY = $stmt->fetch(PDO::FETCH_ASSOC);
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
    
    /* School Year Badge */
    .sy-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
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
    .animate-card:nth-child(5) { animation-delay: 0.5s; }
    .animate-card:nth-child(6) { animation-delay: 0.6s; }
    .animate-card:nth-child(7) { animation-delay: 0.7s; }
</style>

<body class="bg-light">
    <section class="container-fluid py-4" style="max-height: 85vh; overflow-y: auto;">
        
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h1 class="h3 mb-2 text-gray-800">Welcome to School Management System</h1>
                        <p class="text-muted mb-0">Overview and analytics dashboard</p>
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
        
        <!-- Analytics Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">System Overview</h2>
                    <p class="text-muted">Key statistics at a glance</p>
                </div>
            </div>
            
            <!-- Students Card -->
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
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Total Students
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($studentCount) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Teachers Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 success">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon success">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Teachers
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($teacherCount) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Parents Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 info">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon info">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Parents
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($parentCount) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon warning">
                                    <i class="fa-solid fa-school"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Total Community
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($studentCount + $teacherCount + $parentCount) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Facility Section -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">Facility Management</h2>
                    <p class="text-muted">Infrastructure and resources overview</p>
                </div>
            </div>
            
            <!-- Classrooms -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="stat-icon secondary">
                                    <i class="fa-solid fa-door-open"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Classrooms
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($classroom) ?></div>
                                <small class="text-muted">Available learning spaces</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sections -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 dark">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="stat-icon dark">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Sections
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($sections) ?></div>
                                <small class="text-muted">Student groups</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Subjects -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="stat-icon danger">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Subjects
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($subjects) ?></div>
                                <small class="text-muted">Courses offered</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- School Years -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="stat-icon primary">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    School Years
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($school_year) ?></div>
                                <small class="text-muted">Academic periods</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats Row -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Quick Statistics</h5>
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="text-dark mb-1"><?= number_format($studentCount) ?></h3>
                                    <small class="text-muted">Students Enrolled</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="text-success mb-1"><?= number_format($teacherCount) ?></h3>
                                    <small class="text-muted">Teaching Staff</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="text-info mb-1"><?= number_format($classroom) ?></h3>
                                    <small class="text-muted">Classrooms</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h3 class="text-dark mb-1"><?= number_format($sections) ?></h3>
                                    <small class="text-muted">Active Sections</small>
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
                    <small>Last updated: <?= date('F j, Y, g:i a') ?></small>
                    <p class="mt-2 mb-0">School Management System v1.0</p>
                </div>
            </div>
        </div>
        
    </section>
    
    <script>
    // Add some interactivity
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
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const timeString = now.toLocaleDateString('en-US', options);
            
            const timeElement = document.querySelector('.last-updated');
            if (timeElement) {
                timeElement.textContent = `Last updated: ${timeString}`;
            }
        }
        
        // Update time every minute
        setInterval(updateTime, 60000);
    });
    </script>
</body>