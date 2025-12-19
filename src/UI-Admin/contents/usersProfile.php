<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
isset($_GET["user_id"]) ? $user_id = $_GET["user_id"] : '';
$query = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$student_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate age
if (!empty($student_info["birthdate"])) {
    $birthDate = new DateTime($student_info["birthdate"]);
    $today = new DateTime();
    $age = $birthDate->diff($today)->y;
} else {
    $age = 'N/A';
}
?>
<div class="profile-container">
    <!-- Header -->
    <div class="profile-header mb-4 overflow-scroll overflow-visible">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="fas fa-user-circle me-2 text-primary"></i>User Profile
                </h2>
                <nav aria-label="breadcrumb" class="breadcrumb-nav">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php?page=contents/users" class="text-decoration-none">
                                <i class="fas fa-users me-1"></i>Users
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="index.php?page=contents/users" class="btn btn-outline-secondary d-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 overflow-visible" style="max-height: 70vh; overflow-y: scroll !important;">
        <!-- Profile Card -->
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="profile-avatar-container mx-auto mb-3">
                            <div class="profile-avatar position-relative">
                                <img src="../../assets/image/users.png"
                                    class="rounded-circle border shadow-sm"
                                    alt="Profile Picture"
                                    style="width: 180px; height: 180px; object-fit: cover;">
                                <span class="badge bg-primary position-absolute top-0 end-0 rounded-pill p-2">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                        </div>

                        <h4 class="fw-bold mb-2">
                            <?= htmlspecialchars($student_info["firstname"] ?? '') . " " .
                                htmlspecialchars($student_info["lastname"] ?? '') ?>
                        </h4>
                        <p class="text-muted mb-1">
                            <?= !empty($student_info["middlename"]) ?
                                htmlspecialchars(substr($student_info["middlename"], 0, 1)) . ". " : '' ?>
                        </p>

                        <div class="mt-3">
                            <span class="badge bg-info text-dark fs-6">
                                <i class="fas fa-user-tag me-1"></i>
                                <?= htmlspecialchars($student_info["user_role"] ?? 'Student') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="profile-stats mt-4 pt-4 border-top">
                        <h6 class="fw-semibold mb-3 text-muted">
                            <i class="fas fa-info-circle me-2"></i>Account Information
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-3 bg-light">
                                    <small class="text-muted d-block">Status</small>
                                    <span class="badge bg-<?= ($student_info["status"] == 'Active') ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($student_info["status"] ?? 'Inactive') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3 rounded-3 bg-light">
                                    <small class="text-muted d-block">Age</small>
                                    <div class="fw-bold fs-5"><?= $age ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Member since: <?= date('M d, Y', strtotime($student_info["created_date"] ?? 'now')) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="col-lg-8 col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-id-card me-2 text-primary"></i>Personal Information
                    </h5>
                </div>

                <div class="card-body p-4">
                    <form id="displayStudentInfo" class="row g-3">
                        <!-- Name Section -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-signature me-1 text-muted"></i> First Name
                            </label>
                            <div class="input-group">
                                <input type="text" readonly name="firstname"
                                    class="form-control form-control-lg bg-light"
                                    value="<?= htmlspecialchars($student_info["firstname"] ?? '') ?>">

                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-signature me-1 text-muted"></i> Middle Name
                            </label>
                            <div class="input-group">
                                <input type="text" readonly name="middlename"
                                    class="form-control form-control-lg bg-light"
                                    value="<?= htmlspecialchars($student_info["middlename"] ?? '') ?>">

                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-signature me-1 text-muted"></i> Last Name
                            </label>
                            <div class="input-group">
                                <input type="text" readonly name="lastname"
                                    class="form-control form-control-lg bg-light"
                                    value="<?= htmlspecialchars($student_info["lastname"] ?? '') ?>">

                            </div>
                        </div>

                        <!-- Suffix and Gender -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-1 text-muted"></i> Suffix
                            </label>
                            <div class="input-group">
                                <input type="text" readonly name="suffix"
                                    class="form-control form-control-lg bg-light"
                                    value="<?= !empty($student_info["suffix"]) ? htmlspecialchars($student_info["suffix"]) : 'N/A' ?>">

                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-venus-mars me-1 text-muted"></i> Gender
                            </label>
                            <div class="input-group">
                                <input type="text" name="sex"
                                    class="form-control form-control-lg"
                                    value="<?= htmlspecialchars($student_info["gender"] ?? 'N/A') ?>">

                            </div>
                        </div>

                        <!-- Age (Calculated) -->
                        <div class="col-12">
                            <div class="bg-light p-3 rounded-3">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-0">
                                            <i class="fas fa-calculator me-1 text-primary"></i> Calculated Age
                                        </label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="age-display fs-4 fw-bold text-primary">
                                            <?= $age ?> years old
                                        </div>
                                        <small class="text-muted">Based on birth date: <?= date('F d, Y', strtotime($student_info["birthdate"] ?? '')) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information (if available) -->
                        <?php if (!empty($student_info["email"]) || !empty($student_info["contact"])): ?>
                            <div class="col-12 mt-4">
                                <h6 class="fw-semibold border-bottom pb-2">
                                    <i class="fas fa-address-card me-2"></i>Contact Information
                                </h6>
                                <div class="row g-3">
                                    <?php if (!empty($student_info["email"])): ?>
                                        <div class="col-md-6">
                                            <label class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <input type="email" readonly
                                                    class="form-control bg-light"
                                                    name="email"
                                                    value="<?= htmlspecialchars($student_info["email"]) ?>">
                                                <span class="input-group-text">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($student_info["contact"])): ?>
                                        <div class="col-md-6">
                                            <label class="form-label">Contact Number</label>
                                            <div class="input-group">
                                                <input type="text" readonly
                                                    name="contact"
                                                    class="form-control bg-light"
                                                    value="<?= htmlspecialchars($student_info["contact"]) ?>">
                                                <span class="input-group-text">
                                                    <i class="fas fa-phone"></i>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="col-12 mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </div>
                        <?php $user_id = $_GET["user_id"]; ?>
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .profile-header {
        border-bottom: 2px solid #f1f3f5;
        padding-bottom: 1rem;
    }

    .breadcrumb-nav {
        --bs-breadcrumb-divider: '›';
    }

    .profile-avatar-container {
        position: relative;
        width: 180px;
        height: 180px;
    }

    .profile-avatar {
        position: relative;
    }

    .profile-stats .stat-card {
        transition: transform 0.2s ease;
        height: 100%;
    }

    .profile-stats .stat-card:hover {
        transform: translateY(-2px);
    }

    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .input-group .input-group-text {
        border-left: 0;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .age-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .profile-avatar-container {
            width: 150px;
            height: 150px;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .age-display {
            font-size: 1.5rem !important;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeIn 0.5s ease;
    }

    /* Form input focus effects */
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
</style>

<script>
    // Update age in real-time when birthdate changes
    document.addEventListener('DOMContentLoaded', function() {
        const birthdateInput = document.querySelector('input[name="birthdate"]');
        const ageDisplay = document.querySelector('.age-display');

        function calculateAge(birthdate) {
            if (!birthdate) return 'N/A';

            const birthDate = new Date(birthdate);
            const today = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            return age;
        }

        if (birthdateInput && ageDisplay) {
            birthdateInput.addEventListener('change', function() {
                const age = calculateAge(this.value);
                ageDisplay.textContent = `${age} years old`;
            });

        }
    });
</script>