<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('parent', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}
?>
<main>
    <section class="container-fluid py-4" style="max-height: 85vh; overflow-y: auto;">
        
        <!-- Header with School Year Badge -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="section-header">
                        <h1 class="h4 text-gray-800 mb-0"><i class="fa-solid fa-message me-2"></i>Feedback</h1>
                        <p class="text-muted">Submit and view your feedback</p>
                    </div>
                    <div class="sy-badge">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-comments fa-2x"></i>
                            <div>
                                <small class="d-block mb-1">Your Feedback</small>
                                <h4 class="mb-0 fw-bold">Manage</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
            $stmt = $pdo->prepare("SELECT * FROM feeback WHERE parent_id = '$user_id'");
            $stmt->execute();
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $feedbackCount = count($feedbacks);
        ?>

        <!-- Parent Information Card -->
        <div class="row mb-4 animate-card">
            <div class="col-12">
                <div class="parent-info-card">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background: rgba(255,255,255,0.2); border: 2px solid white;">
                                    <i class="fa-solid fa-message text-white"></i>
                                </div>
                                <div>
                                    <h2 class="h4 mb-1">My Feedback</h2>
                                    <p class="mb-0">You have submitted <?= $feedbackCount ?> feedback/s</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="d-flex justify-content-md-end gap-4">
                                <div>
                                    <button class="btn btn-light px-4" data-bs-toggle="modal" data-bs-target="#AddNewAccount">
                                        <i class="fa-solid fa-plus me-2"></i>Create New
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">Recent Feedbacks</h2>
                    <p class="text-muted">Your latest submitted feedback</p>
                </div>
            </div>
            
            <?php if($feedbackCount > 0): ?>
                <?php foreach($feedbacks as $feedback): ?>
                <div class="col-xl-4 col-lg-6 mb-4 animate-card">
                    <div class="stat-card card border-0 shadow h-100 purple">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="stat-icon purple">
                                    <i class="fa-solid fa-comment"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-2 text-truncate" title="<?= htmlspecialchars($feedback["title"]) ?>">
                                        <?= htmlspecialchars($feedback["title"]) ?>
                                    </h5>
                                    <p class="text-muted mb-3 small" style="min-height: 40px; max-height: 60px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                        <?= htmlspecialchars($feedback["description"]) ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fa-solid fa-clock me-1"></i>
                                            <?= date('M d, Y', strtotime($feedback['created_at'] ?? 'now')) ?>
                                        </small>
                                        <span class="badge bg-purple">Submitted</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 animate-card">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <div class="stat-icon secondary mx-auto">
                                    <i class="fa-solid fa-message"></i>
                                </div>
                            </div>
                            <h5 class="text-muted mb-3">No Feedback Yet</h5>
                            <p class="text-muted mb-4">You haven't submitted any feedback yet.</p>
                            <button class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#AddNewAccount">
                                <i class="fa-solid fa-plus me-2"></i>Create Your First Feedback
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Feedback Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="section-header">
                    <h2 class="h4 text-gray-800 mb-0">Feedback Summary</h2>
                    <p class="text-muted">Your feedback activity overview</p>
                </div>
            </div>
            
            <!-- Total Feedback Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 purple">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon purple">
                                    <i class="fa-solid fa-message"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">
                                    Total Feedback
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($feedbackCount) ?></div>
                                <small class="text-muted">All submissions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Feedback Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 info">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon info">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Latest Feedback
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php if($feedbackCount > 0): ?>
                                        <?= date('M d', strtotime($feedbacks[0]['created_at'] ?? 'now')) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">Last submission</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Required Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon warning">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pending Review
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($feedbackCount) ?></div>
                                <small class="text-muted">Awaiting response</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Create New Card -->
            <div class="col-xl-3 col-md-6 mb-4 animate-card">
                <div class="stat-card card border-0 shadow h-100 py-2 success">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon success">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                            </div>
                            <div class="col ml-3">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    New Feedback
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Ready</div>
                                <button class="btn btn-sm btn-success mt-2 px-3" data-bs-toggle="modal" data-bs-target="#AddNewAccount">
                                    Create Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Feedback Modal -->
        <div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-gradient-primary text-white">
                        <div class="modal-title">
                            <h5 class="mb-0 text-white"><i class="fa-solid fa-plus-circle me-2"></i>Create New Feedback</h5>
                            <small class="text-white-50">Share your thoughts and suggestions</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form class="row g-3" id="feedback-form">
                            <input type="hidden" name="parent_id" value="<?= $user_id ?>">
                            
                            <!-- Form Header -->
                            <div class="col-12 mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="stat-icon purple">
                                        <i class="fa-solid fa-message"></i>
                                    </div>
                                    <h6 class="mb-0 text-purple">Feedback Details</h6>
                                </div>
                                <p class="text-muted small">Please provide clear and constructive feedback to help us improve.</p>
                            </div>
                            
                            <!-- Title Input -->
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" required class="form-control border-0 bg-light" name="title" 
                                           id="feedbackTitle" placeholder="Enter feedback title"
                                           style="border-radius: 10px;">
                                    <label for="feedbackTitle" class="text-muted">
                                        <i class="fa-solid fa-heading me-2"></i>Feedback Title
                                    </label>
                                </div>
                                <small class="text-muted mt-1 d-block">e.g., "Cleanliness Issue", "Academic Concern", "Suggestion"</small>
                            </div>
                            
                            <!-- Description Input -->
                            <div class="col-md-12 mt-3">
                                <div class="form-floating">
                                    <textarea name="description" required class="form-control border-0 bg-light" 
                                              id="feedbackDescription" placeholder="Describe your feedback"
                                              style="height: 150px; border-radius: 10px;"></textarea>
                                    <label for="feedbackDescription" class="text-muted">
                                        <i class="fa-solid fa-align-left me-2"></i>Description
                                    </label>
                                </div>
                                <small class="text-muted mt-1 d-block">Please be specific and include any relevant details</small>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                        <i class="fa-solid fa-times me-2"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-gradient-primary px-5">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Feedback
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Note -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="text-center text-muted">
                    <small class="last-updated">Last updated: <?= date('F j, Y, g:i a') ?></small>
                    <p class="mt-2 mb-0">Feedback System • School Management System</p>
                </div>
            </div>
        </div>
        
    </section>
</main>

<style>
    /* Modal Gradient Background */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #6f42c1 0%, #4e2a8c 100%) !important;
    }
    
    .btn-gradient-primary {
        background: linear-gradient(135deg, #6f42c1, #4e2a8c);
        border: none;
        color: white;
        padding: 10px 30px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-gradient-primary:hover {
        background: linear-gradient(135deg, #5e35b1, #3d2173);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);
        color: white;
    }
    
    /* Form Styling */
    .form-control.bg-light:focus {
        background-color: #fff !important;
        box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1) !important;
        border-color: #6f42c1 !important;
    }
    
    /* Modal Styling */
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .modal-header {
        border-bottom: none;
        padding: 1.5rem 1.5rem 1rem;
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    /* Purple Color Variants */
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .text-purple {
        color: #6f42c1 !important;
    }
    
    .border-purple {
        border-color: #6f42c1 !important;
    }
    
    /* Form Floating Labels */
    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        color: #6f42c1;
    }
    
    /* Button Hover Effects */
    .btn-outline-secondary:hover {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling
    const feedbackForm = document.getElementById('feedback-form');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Simulate AJAX submission (replace with actual AJAX call)
            setTimeout(() => {
                // In real implementation, use fetch or XMLHttpRequest
                // fetch('submit_feedback.php', {
                //     method: 'POST',
                //     body: formData
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if(data.success) {
                //         // Show success message
                //         showAlert('Feedback submitted successfully!', 'success');
                //         // Close modal
                //         const modal = bootstrap.Modal.getInstance(document.getElementById('AddNewAccount'));
                //         modal.hide();
                //         // Reload page after 1.5 seconds
                //         setTimeout(() => location.reload(), 1500);
                //     } else {
                //         showAlert('Error submitting feedback: ' + data.message, 'error');
                //         submitBtn.innerHTML = originalText;
                //         submitBtn.disabled = false;
                //     }
                // })
                // .catch(error => {
                //     showAlert('Network error. Please try again.', 'error');
                //     submitBtn.innerHTML = originalText;
                //     submitBtn.disabled = false;
                // });
                
                // For demo purposes - show success and reload
                showAlert('Feedback submitted successfully!', 'success');
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('AddNewAccount'));
                    modal.hide();
                    location.reload();
                }, 1500);
            }, 1500);
        });
    }
    
    // Show alert function
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 10px;
            border: none;
        `;
        
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-3 fs-5"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Add hover effects to feedback cards
    const feedbackCards = document.querySelectorAll('.stat-card');
    feedbackCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 15px 35px rgba(0,0,0,0.1) !important';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Update time
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
});
</script>