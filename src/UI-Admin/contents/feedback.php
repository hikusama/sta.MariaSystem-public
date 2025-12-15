<?php
    $stmt = $pdo->prepare("SELECT feeback.*, users.* FROM feeback
                INNER JOIN users ON feeback.parent_id = users.user_id");
            $stmt->execute();
            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<main>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="mx-2">
            <h4><i class="fa-solid fa-message me-2"></i>Feedback Management</h4>
        </div>
    </div>

    <div class="row g-3 scroll-feedback">
        <!-- Search and Action Section -->
        <div class="row mb-3 justify-content-between align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search feedback..."
                        id="searchInput">
                </div>
            </div>
            <div class="col-md-4 text-end">
            </div>
        </div>

        <!-- Statistics Summary -->
        <!-- <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Feedback Overview</h5>
                        <div class="row text-center">
                           
                            <div class="col-md-4 col-6 mb-3">
                                <div class="p-3 bg-primary bg-opacity-10 rounded">
                                    <h3 class="text-primary mb-1"><?= count($feedbacks) ?></h3>
                                    <small class="text-muted">Total Feedback</small>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <h3 class="text-success mb-1"><?= $recentCount ?></h3>
                                    <small class="text-muted">Recent (7 days)</small>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="p-3 bg-info bg-opacity-10 rounded">
                                    <h3 class="text-info mb-1"><?= date('M Y') ?></h3>
                                    <small class="text-muted">Current Month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Feedback Table -->
        <div class="table-container-wrapper p-0">
            <?php
                $stmt = $pdo->prepare("SELECT * FROM feeback");
                $stmt->execute();
                $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <!-- Fixed Header -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Title</th>
                            <th width="40%">Description</th>
                            <th width="15%">Submitted By</th>
                            <th width="15%">Submitted At</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Scrollable Body -->
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                    <tbody id="feedbackTableBody">
                        <?php if($feedbacks): 
                            $count = 1;
                            foreach($feedbacks as $feedback) : 
                                // Get parent name if parent_id exists
                                $parentName = "User";
                                if (isset($feedback['parent_id']) && $feedback['parent_id']) {
                                    $parentStmt = $pdo->prepare("SELECT firstname, lastname FROM users WHERE user_id = ?");
                                    $parentStmt->execute([$feedback['parent_id']]);
                                    $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
                                    if ($parent) {
                                        $parentName = htmlspecialchars($parent['firstname'] . ' ' . $parent['lastname']);
                                    }
                                }
                        ?>
                        <tr class="feedback-row" 
                            data-title="<?= htmlspecialchars(strtolower($feedback["title"])) ?>"
                            data-description="<?= htmlspecialchars(strtolower($feedback["description"])) ?>"
                            data-author="<?= htmlspecialchars(strtolower($parentName)) ?>">
                            <td width="5%"><?= $count++ ?></td>
                            <td width="20%" class="feedback-title">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-placeholder me-2">
                                        <i class="fa-solid fa-message text-info"></i>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($feedback["title"]) ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td width="40%" class="text-start">
                                <div class="feedback-description" style="max-height: 60px; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($feedback["description"]) ?>
                                </div>
                                
                            </td>
                            <td width="15%">
                                <span class="badge bg-secondary">
                                    <i class="fa-solid fa-user fa-xs me-1"></i>
                                    <?= $parentName ?>
                                </span>
                            </td>
                            <td width="15%">
                                <small>
                                    <?= isset($feedback['feed_at']) ? date('M d, Y', strtotime($feedback["feed_at"])) : 'N/A' ?>
                                </small>
                            </td>
                            <td width="15%">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="#" class="text-dark btn btn-sm btn-info read-more-link" style="font-size: 0.75rem;" 
                                        data-title="<?= htmlspecialchars($feedback["title"]) ?>"
                                        data-description="<?= htmlspecialchars($feedback["description"]) ?>"
                                        data-feed_at="<?= htmlspecialchars($feedback["feed_at"]) ?>"
                                        data-name="<?= htmlspecialchars($parentName) ?>"
                                        >
                                        Read more
                                    </a>
                                    <button type="button" data-id="<?= $feedback['feeback_id'] ?>"
                                        class="btn btn-sm btn-danger deleteFeedbackBtn" title="Delete Feedback">
                                        <i class="fa-solid fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">No feedback found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="noResults" class="text-center py-5 d-none">
                <div class="empty-state">
                    <i class="fa-solid fa-message fa-3x text-muted mb-3"></i>
                    <h5>No feedback found</h5>
                    <p class="text-muted">Try adjusting your search or create new feedback</p>
                </div>
            </div>
        </div>
    </div>
</main>


<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedback" tabindex="-1" aria-labelledby="viewFeedbackLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white" id="viewFeedbackLabel">
                    <i class="fa-solid fa-eye me-2"></i>Feedback Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h6 class="text-dark mb-1">Title</h6>
                        <h5 id="feedbackViewTitle" class="text-dark"></h5>
                    </div>
                    <div class="col-md-12 mb-3">
                        <h6 class="text-dark mb-1">Submitted By</h6>
                        <p id="feedbackViewAuthor" class="mb-0 text-dark"></p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <h6 class="text-dark mb-1">Submitted Date</h6>
                        <p id="feedbackViewDate" class="mb-0 text-dark"></p>
                    </div>
                    <div class="col-md-12">
                        <h6 class="text-dark mb-1">Description</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p id="feedbackViewDescription" class="mb-0 text-dark"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Feedback Modal -->
<div class="modal fade" id="deleteFeedback" tabindex="-1" aria-labelledby="deleteFeedbackLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="deleteFeedbackLabel">
                    <i class="fa-solid fa-trash me-2"></i>Delete Feedback
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="deleteFeedback-form">
                    <input type="hidden" name="feedback_id" id="feedback_id_delete">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                        <h5>Confirm Deletion</h5>
                        <p class="text-muted">Are you sure you want to delete this feedback? This action cannot be
                            undone.</p>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fa-solid fa-trash me-2"></i>Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const feedbackRows = document.querySelectorAll('.feedback-row');
    const feedbackTableBody = document.getElementById('feedbackTableBody');
    const noResultsDiv = document.getElementById('noResults');
    const viewButtons = document.querySelectorAll('.viewFeedbackBtn');
    const deleteButtons = document.querySelectorAll('.deleteFeedbackBtn');
    const readMoreLinks = document.querySelectorAll('.read-more-link');

    // Feedback data
    const feedbackData = <?= json_encode($feedbacks); ?>;

    // Search functionality
    function filterFeedback() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        feedbackRows.forEach(row => {
            const title = row.getAttribute('data-title');
            const description = row.getAttribute('data-description');
            const author = row.getAttribute('data-author');

            let matchesSearch = true;

            if (searchTerm) {
                matchesSearch = title.includes(searchTerm) ||
                    description.includes(searchTerm) ||
                    author.includes(searchTerm);
            }

            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            feedbackTableBody.style.display = 'none';
            noResultsDiv.classList.remove('d-none');
        } else {
            feedbackTableBody.style.display = '';
            noResultsDiv.classList.add('d-none');
        }

        updateRowNumbers();
    }

    function updateRowNumbers() {
        let counter = 1;
        feedbackRows.forEach(row => {
            if (row.style.display !== 'none') {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell) {
                    firstCell.textContent = counter++;
                }
            }
        });
    }

    // View button click handler
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const feedbackId = this.getAttribute('data-id');
            const feedback = feedbackData.find(f => f.id == feedbackId);

            if (feedback) {
                // Get parent name
                let parentName = "User";
                if (feedback.parent_id) {
                    // In a real application, you would fetch this from your database
                    // For now, we'll use the data from the table
                    const row = this.closest('tr');
                    const authorBadge = row.querySelector('.badge.bg-secondary');
                    if (authorBadge) {
                        parentName = authorBadge.textContent.trim().replace('👤', '').trim();
                    }
                }

                document.getElementById('feedbackViewTitle').textContent = feedback.title;
                document.getElementById('feedbackViewDescription').textContent = feedback.description;
                document.getElementById('feedbackViewAuthor').textContent = parentName;
                document.getElementById('feedbackViewDate').textContent = feedback.created_at ? 
                    new Date(feedback.created_at).toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : 'N/A';

                const modal = new bootstrap.Modal(document.getElementById('viewFeedback'));
                modal.show();
            }
        });
    });

    // Read more link click handler
    readMoreLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const description = this.getAttribute('data-description');
            const title = this.getAttribute('data-title');
            const name = this.getAttribute('data-name');
            const feed_at = this.getAttribute('data-feed_at');
            
            // Show description in modal
            document.getElementById('feedbackViewDescription').textContent = description;
            document.getElementById('feedbackViewTitle').textContent = title;
            document.getElementById('feedbackViewAuthor').textContent = name;
            document.getElementById('feedbackViewDate').textContent = feed_at;
            
            const modal = new bootstrap.Modal(document.getElementById('viewFeedback'));
            modal.show();
        });
    });

    // Delete button click handler
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const feedbackId = this.getAttribute('data-id');
            document.getElementById('feedback_id_delete').value = feedbackId;

            const modal = new bootstrap.Modal(document.getElementById('deleteFeedback'));
            modal.show();
        });
    });

    // Event listeners
    searchInput.addEventListener('input', filterFeedback);

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterFeedback();
        searchInput.focus();
    });

    // Add Enter key support for search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterFeedback();
        }
    });

    // Add some styling
    searchInput.addEventListener('focus', function() {
        this.parentElement.classList.add('border-primary', 'border-2');
    });

    searchInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('border-primary', 'border-2');
    });

    // Initialize
    filterFeedback();
});
</script>

<style>
.scroll-feedback {
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

#clearSearchBtn:hover {
    background-color: #e9ecef;
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.feedback-description {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.read-more-link {
    text-decoration: none;
    font-size: 0.75rem;
}

.read-more-link:hover {
    text-decoration: underline;
}

/* Custom scrollbar for main container */
.scroll-feedback::-webkit-scrollbar {
    width: 8px;
}

.scroll-feedback::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.scroll-feedback::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.scroll-feedback::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Modal styling */
.modal-body .card {
    border: 1px solid #dee2e6;
}

.modal-body h6 {
    font-size: 0.875rem;
    color: #6c757d;
}

.modal-body h5 {
    font-size: 1.25rem;
}

@media (max-width: 768px) {
    .scroll-feedback {
        height: auto;
        overflow: visible;
    }
    
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
}
</style>