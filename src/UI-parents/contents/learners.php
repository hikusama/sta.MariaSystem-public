<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('parent', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}
?>
<style>
    img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 3px solid #e83e8c;
        object-fit: cover;
    }

    /* Card Styling */
    .student-card {
        width: 31rem;
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
    @media (max-width: 768px) {
        .lrr {
            width: 6rem;
            transform: translate(16px, 5px) !important;
        }

        .student-card {
            width: 100%;
            margin-bottom: 20px;
        }

        img {
            width: 70px;
            height: 70px;
        }

        .btn-action {
            padding: 6px 12px;
            font-size: 12px;
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
                                <select required name="grade_level" class="form-select">
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
                                <label class="form-label fw-semibold">Nickname</label>
                                <input type="text" class="form-control" placeholder="Student nickname" name="nickname">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">Sex</label>
                                <select name="sex" class="form-select">
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
                                <input required type="date" name="birthdate" class="form-control">
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

    <!-- Students Grid -->
    <div class="row mt-4" id="studentsContainer">
        <?php
        $currentSyStmt = $pdo->prepare("
    SELECT school_year_id 
    FROM school_year 
    WHERE school_year_status = 'Active' 
    LIMIT 1
");
        $currentSyStmt->execute();
        $currentSy = $currentSyStmt->fetch(PDO::FETCH_ASSOC);

        $activeSyId = $currentSy['school_year_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;

        $students = [];

        if ($activeSyId && $user_id) {
            try {
                $stmt = $pdo->prepare("
            SELECT student.*, users.school_year_id 
            FROM student 
            LEFT JOIN users ON student.guardian_id = users.user_id
            WHERE student.guardian_id = ? 
              AND users.school_year_id = ?
            ORDER BY student.student_id DESC
        ");
                $stmt->execute([$user_id, $activeSyId]);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $students = [];
            }
        }

        if (empty($students)): ?>
            <div class="col-12">
                <div class="card border-0 shadow text-center py-5" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                    <div class="card-body">
                        <div class="mb-4">
                            <i class="fa-solid fa-users-slash fa-4x" style="color: #6c757d;"></i>
                        </div>
                        <h4 class="mb-3">No Learners Found</h4>
                        <p class="text-muted mb-4">You haven't added any learners to your account yet.</p>
                        <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#AddNewAccount"
                            style="background: linear-gradient(135deg, #e74a3b, #be2617); border: none;">
                            <i class="fa fa-plus me-2"></i> Add Your First Learner
                        </button>
                    </div>
                </div>
            </div>
            <?php else:
            foreach ($students as $student):
                // Calculate animation delay
                static $delay = 0.1;
            ?>
                <div class="col-xl-4 col-lg-6 col-md-6 mb-4 student-card" style="animation-delay: <?= $delay ?>s">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <!-- Profile Picture -->
                                <div class="col-md-4 text-center mb-3 mb-md-0">
                                    <div class="position-relative d-inline-block">
                                        <?php if ($student["student_profile"] !== ''): ?>
                                            <img src="../../authentication/uploads/<?php echo htmlspecialchars($student["student_profile"]); ?>"
                                                class="img-fluid" alt="Profile Picture">
                                        <?php else: ?>
                                            <img src="../../assets/image/users.png" class="img-fluid" alt="Default Profile">
                                        <?php endif; ?>

                                        <!-- LRN Badge -->
                                        <div class="position-absolute bottom-0 end-0 bg-dark text-white rounded-pill px-2 py-1 lrr"
                                            style="font-size: 10px; transform: translate(5px, 5px);">
                                            LRN: <?= substr(htmlspecialchars($student["lrn"]), 0, 6) ?>...
                                        </div>
                                    </div>
                                </div>

                                <!-- Student Information -->
                                <div class="col-md-8">
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
                                    <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                        <a href="index.php?page=contents/profile&student_id=<?= htmlspecialchars($student["student_id"]) ?>"
                                            class="flex-fill">
                                            <button class="btn btn-action btn-profile w-100">
                                                <i class="fa-solid fa-user me-1"></i> Profile
                                            </button>
                                        </a>
                                        <a href="index.php?page=contents/form&student_id=<?= htmlspecialchars($student["student_id"]) ?>"
                                            class="flex-fill">
                                            <button class="btn btn-action btn-form w-100">
                                                <i class="fa-solid fa-file-lines me-1"></i> Form
                                            </button>
                                        </a>

                                        <?php
                                        // Construct report card filename
                                        $lrn = $student["lrn"];
                                        $fname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student["fname"]));
                                        $lname = preg_replace("/[^A-Za-z0-9]/", "", strtolower($student["lname"]));
                                        $grade = str_replace(" ", "", strtolower($student["gradeLevel"]));
                                        $reportFile = BASE_PATH . "/sf9_files/{$lrn}_{$fname}_{$lname}_{$grade}.xlsx";

                                        if (file_exists($reportFile)) {
                                            $webPath = BASE_PATH . "/sf9_files/{$lrn}_{$fname}_{$lname}_{$grade}.xlsx";
                                        ?>
                                            <a href="index.php?page=contents/sf9_view&student_id=<?= htmlspecialchars($student['student_id']) ?>"
                                                class="flex-fill">
                                                <button class="btn btn-action btn-report w-100">
                                                    <i class="fa-solid fa-file-excel me-1"></i> Report
                                                </button>
                                            </a>
                                        <?php } else { ?>
                                            <button class="btn btn-action w-100" disabled style="background: #6c757d;">
                                                <i class="fa-solid fa-file-excel me-1"></i> Report
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
        endif;
        ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Profile picture preview
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

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const studentCards = document.querySelectorAll('.student-card');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                console.log(searchTerm);


                studentCards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        card.style.display = 'block';
                        // card.style.animation = 'fadeInUp 0.3s ease-out';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Add hover effects to student cards
        studentCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Form validation for LRN
        const lrnInput = document.querySelector('input[name="lrn"]');
        if (lrnInput) {
            lrnInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '');
                if (this.value.length > 12) {
                    this.value = this.value.slice(0, 12);
                }
            });
        }
    });
</script>