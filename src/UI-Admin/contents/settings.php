<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
?>
<section class="settings-section">
    <!-- Header -->
    <div class="settings-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="fas fa-cog me-2 text-primary"></i>Account Settings
                </h2>
                <p class="text-muted mb-0">Manage your profile and security settings</p>
            </div>
            <button class="btn btn-outline-primary d-flex align-items-center text-dark"
                    data-bs-toggle="modal" 
                    data-bs-target="#changePassword">
                <i class="fas fa-key me-2"></i>Change Password
            </button>
        </div>
    </div>

    <?php
    $query = "SELECT * FROM admin WHERE admin_id = :admin_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':admin_id', $user_id);
    $stmt->execute();
    $LibrarianInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <!-- Profile Form -->
    <form action="../../authentication/auth.php" method="post" enctype="multipart/form-data" 
          id="profileForm" class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <input type="hidden" name="adminID" value="<?= htmlspecialchars($user_id); ?>">
            <input type="hidden" name="adminProfile" value="true">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"]; ?>">
            
            <div class="row">
                <!-- Profile Picture Section -->
                <div class="col-lg-4 col-md-5 mb-4 mb-md-0">
                    <div class="profile-picture-section text-center p-4 rounded-3 border bg-light">
                        <div class="profile-image-container mb-3 position-relative mx-auto" 
                             style="width: 180px; height: 180px;">
                            <?php if($profile && !empty($LibrarianInfo["admin_picture"])) { ?>
                                <img src="../../authentication/uploads/<?= $LibrarianInfo["admin_picture"] ?>"
                                     class="rounded-circle w-100 h-100 object-fit-cover border shadow-sm"
                                     id="settingsProfile"
                                     alt="Profile Picture">
                            <?php } else { ?>
                                <div class="rounded-circle w-100 h-100 bg-primary d-flex align-items-center justify-content-center border shadow-sm">
                                    <i class="fas fa-user text-white fa-4x"></i>
                                </div>
                            <?php } ?>
                            
                            <label for="user_profile" 
                                   class="btn btn-sm btn-outline-primary position-absolute bottom-0 end-0 rounded-circle"
                                   style="width: 40px; height: 40px; cursor: pointer;">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                        
                        <div>
                            <h5 class="mb-2"><?= htmlspecialchars($LibrarianInfo["admin_firstname"] . ' ' . $LibrarianInfo["admin_lastname"]) ?></h5>
                            <p class="text-muted small mb-0">Administrator</p>
                        </div>
                        
                        <input type="hidden" name="current_profile_image" value="<?= $LibrarianInfo["admin_picture"] ?>">
                        <input type="file" name="user_profile" id="user_profile" 
                               class="form-control d-none" 
                               accept="image/*"
                               onchange="previewImage(event)">
                        <small class="text-muted d-block mt-2">Click camera icon to upload new photo</small>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="col-lg-8 col-md-7">
                    <h5 class="mb-4 border-bottom pb-2">Personal Information</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-1 text-muted"></i> First Name
                            </label>
                            <input type="text" name="admin_firstname" 
                                   value="<?= $LibrarianInfo["admin_firstname"] ?>"
                                   class="form-control form-control-lg" 
                                   placeholder="Enter first name">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-1 text-muted"></i> Last Name
                            </label>
                            <input type="text" name="admin_lastname" 
                                   value="<?= $LibrarianInfo["admin_lastname"] ?>"
                                   class="form-control form-control-lg" 
                                   placeholder="Enter last name">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-1 text-muted"></i> Middle Name
                            </label>
                            <input type="text" name="admin_middlename" 
                                   value="<?= $LibrarianInfo["admin_middlename"] ?>"
                                   class="form-control form-control-lg" 
                                   placeholder="Enter middle name">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-1 text-muted"></i> Name Suffix
                            </label>
                            <select name="admin_suffix" class="form-select form-select-lg">
                                <option value="" <?= empty($LibrarianInfo["admin_suffix"]) ? 'selected' : '' ?>>None</option>
                                <option value="Jr." <?= $LibrarianInfo["admin_suffix"] == "Jr." ? 'selected' : '' ?>>Jr.</option>
                                <option value="Sr." <?= $LibrarianInfo["admin_suffix"] == "Sr." ? 'selected' : '' ?>>Sr.</option>
                                <option value="II" <?= $LibrarianInfo["admin_suffix"] == "II" ? 'selected' : '' ?>>II</option>
                                <option value="III" <?= $LibrarianInfo["admin_suffix"] == "III" ? 'selected' : '' ?>>III</option>
                                <option value="IV" <?= $LibrarianInfo["admin_suffix"] == "IV" ? 'selected' : '' ?>>IV</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-envelope me-1 text-muted"></i> Email Address
                            </label>
                            <input type="email" class="form-control form-control-lg" 
                                   name="admin_email"
                                   value="<?= $LibrarianInfo["admin_email"] ?>"
                                   placeholder="Enter email address">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Footer -->
        <div class="card-footer bg-light border-top d-flex justify-content-end py-3">
            <button type="button" class="btn btn-lg btn-success px-4 d-flex align-items-center" 
                    data-bs-toggle="modal" data-bs-target="#updateProfile">
                <i class="fas fa-save me-2"></i>Update Profile
            </button>
        </div>
    </form>

    <!-- Update Profile Modal -->
    <div class="modal fade" id="updateProfile" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-circle me-2"></i>Confirm Update
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" 
                            aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-question-circle fa-3x text-primary"></i>
                    </div>
                    <h5 class="mb-3">Update Profile?</h5>
                    <p class="text-muted">Are you sure you want to save these changes to your profile?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="profileForm" class="btn btn-primary px-4">
                        <i class="fas fa-check me-1"></i>Yes, Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePassword" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="../../authentication/auth.php" class="modal-content border-0 shadow">
                <input type="hidden" name="usersForgottenPassAdmin" value="true">
                <input type="hidden" name="Users_id" value="<?= $user_id ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"]; ?>">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" 
                            aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-muted"></i> Current Password
                        </label>
                        <div class="input-group">
                            <input type="password" name="current_password" 
                                   class="form-control form-control-lg"
                                   placeholder="Enter current password" required>
                            <button class="btn btn-outline-secondary my-0 toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-muted"></i> New Password
                        </label>
                        <div class="input-group">
                            <input type="password" name="new_password" 
                                   class="form-control form-control-lg"
                                   placeholder="Enter new password" required>
                            <button class="btn btn-outline-secondary my-0 toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 8 characters with letters and numbers</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-muted"></i> Confirm Password
                        </label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" 
                                   class="form-control form-control-lg"
                                   placeholder="Confirm new password" required>
                            <button class="btn btn-outline-secondary my-0 toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Success/Error Messages Script -->
<?php if (isset($_GET['update']) || isset($_GET['passwordChange']) || isset($_GET['NewPassword']) || isset($_GET['CurrentPasswoed'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const messages = {
        update: {
            icon: 'success',
            title: 'Success!',
            text: 'Profile updated successfully.',
            color: '#28a745'
        },
        passwordChange: {
            icon: 'success',
            title: 'Success!',
            text: 'Password changed successfully.',
            color: '#28a745'
        },
        NewPassword: {
            icon: 'error',
            title: 'Error!',
            text: 'New passwords do not match.',
            color: '#dc3545'
        },
        CurrentPasswoed: {
            icon: 'error',
            title: 'Error!',
            text: 'Current password is incorrect.',
            color: '#dc3545'
        }
    };

    for (const key in messages) {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has(key)) {
            const message = messages[key];
            
            // Create custom notification
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 p-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast show" role="alert" style="min-width: 300px;">
                    <div class="toast-header" style="border-left: 4px solid ${message.color}">
                        <i class="fas fa-${message.icon === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'} me-2"></i>
                        <strong class="me-auto">${message.title}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message.text}
                    </div>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.remove();
                // Clean URL
                const url = new URL(window.location);
                url.searchParams.delete(key);
                window.history.replaceState({}, document.title, url.toString());
            }, 5000);
            
            break;
        }
    }
});
</script>
<?php endif; ?>

<style>
/* Custom Styles */
.settings-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.profile-picture-section {
    transition: all 0.3s ease;
}

.profile-picture-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.form-control-lg, .form-select-lg {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.toggle-password {
    border-left: 0;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .settings-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .profile-picture-section {
        margin: 0 auto;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
}

/* Animation for profile image */
#settingsProfile {
    transition: transform 0.3s ease;
}

#settingsProfile:hover {
    transform: scale(1.05);
}
</style>

<script>
// Image preview function
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, GIF, WebP)');
            event.target.value = '';
            return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size must be less than 2MB');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("settingsProfile").src = e.target.result;
            document.getElementById("settingsProfile").style.objectFit = 'cover';
        };
        reader.readAsDataURL(file);
    }
}

// Password visibility toggle
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>