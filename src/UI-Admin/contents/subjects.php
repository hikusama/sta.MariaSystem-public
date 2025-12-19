<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM subjects ORDER BY created_date DESC");
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = 1;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-book-open me-2"></i>Subjects Management</h4>
    </div>
</div>

<div class="row g-3 scroll-subjects">
    <!-- Search and Action Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search subjects..."
                    id="searchInput">
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createSubjects"
                id="createSubjectBtn">
                <i class="fa-solid fa-plus me-2"></i> Create Subject
            </button>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Subjects Overview</h5>
                    <div class="row text-center">
                        <?php
                        $availableCount = array_filter($subjects, fn($s) => $s['subjects_status'] === 'Available');
                        $unavailableCount = array_filter($subjects, fn($s) => $s['subjects_status'] === 'Unavailable');

                        // Count by grade level
                        $grade1Count = array_filter($subjects, fn($s) => $s['grade_level'] === 'Grade 1');
                        $grade2Count = array_filter($subjects, fn($s) => $s['grade_level'] === 'Grade 2');
                        $grade3Count = array_filter($subjects, fn($s) => $s['grade_level'] === 'Grade 3');
                        $grade4Count = array_filter($subjects, fn($s) => $s['grade_level'] === 'Grade 4');
                        $grade5Count = array_filter($subjects, fn($s) => $s['grade_level'] === 'Grade 5');
                        $grade6Count = array_filter($subjects, fn($s) => $s['grade_level'] === 'Grade 6');
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($subjects) ?></h3>
                                <small class="text-white">Total Subjects</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($availableCount) ?></h3>
                                <small class="text-white">Available</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($unavailableCount) ?></h3>
                                <small class="text-white">Unavailable</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= array_sum(array_column($subjects, 'subject_units')) ?></h3>
                                <small class="text-white">Total Units</small>
                            </div>
                        </div>
                    </div>

                    <!-- Grade Level Breakdown -->
                    <div class="row mt-4">
                        <h6 class="text-muted mb-3">Grade Level Distribution</h6>
                        <div class="col-2 text-center">
                            <div class="p-2 bg-light rounded">
                                <h5 class="mb-1"><?= count($grade1Count) ?></h5>
                                <small class="text-muted">Grade 1</small>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <div class="p-2 bg-light rounded">
                                <h5 class="mb-1"><?= count($grade2Count) ?></h5>
                                <small class="text-muted">Grade 2</small>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <div class="p-2 bg-light rounded">
                                <h5 class="mb-1"><?= count($grade3Count) ?></h5>
                                <small class="text-muted">Grade 3</small>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <div class="p-2 bg-light rounded">
                                <h5 class="mb-1"><?= count($grade4Count) ?></h5>
                                <small class="text-muted">Grade 4</small>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <div class="p-2 bg-light rounded">
                                <h5 class="mb-1"><?= count($grade5Count) ?></h5>
                                <small class="text-muted">Grade 5</small>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <div class="p-2 bg-light rounded">
                                <h5 class="mb-1"><?= count($grade6Count) ?></h5>
                                <small class="text-muted">Grade 6</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="table-container-wrapper p-0">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM subjects ORDER BY created_date DESC");
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 1;
        ?>

        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Subject</th>
                        <th width="10%">Code</th>
                        <th width="10%">Units</th>
                        <th width="15%">Grade Level</th>
                        <th width="15%">Status</th>
                        <th width="15%">Created at</th>
                        <th width="20%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="subjectsTableBody">
                    <?php if ($subjects):
                        $count = 1;
                        foreach ($subjects as $subject) : ?>
                            <tr class="subject-row"
                                data-name="<?= htmlspecialchars(strtolower($subject["subject_name"])) ?>"
                                data-code="<?= htmlspecialchars(strtolower($subject["subject_code"])) ?>"
                                data-grade="<?= htmlspecialchars(strtolower($subject["grade_level"])) ?>"
                                data-status="<?= htmlspecialchars(strtolower($subject["subjects_status"])) ?>">
                                <td width="5%"><?= $count++ ?></td>
                                <td width="20%" class="subject-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-book text-primary"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($subject["subject_name"]) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td width="10%">
                                    <span class="badge bg-dark"><?= htmlspecialchars($subject["subject_code"] ?? 'N/A') ?></span>
                                </td>
                                <td width="10%">
                                    <span class="badge bg-info"><?= htmlspecialchars($subject["subject_units"]) ?> units</span>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($subject["grade_level"]) ?></span>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-<?= ($subject["subjects_status"] == 'Available') ? 'success' : 'secondary' ?>">
                                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                                        <?= htmlspecialchars($subject["subjects_status"] ?? 'Unavailable') ?>
                                    </span>
                                </td>
                                <td width="15%">
                                    <small><?= date('M d, Y', strtotime($subject["created_date"])) ?></small>
                                </td>
                                <td width="20%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" data-id="<?= $subject['subject_id'] ?>"
                                            class="btn btn-sm btn-info editSubjectBtn" title="Edit Subject">
                                            <i class="fa-solid fa-pen me-1"></i> Edit
                                        </button>
                                        <button type="button" data-id="<?= $subject['subject_id'] ?>"
                                            class="btn btn-sm btn-danger deleteSubjectBtn" title="Delete Subject">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-3">No subjects found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-book fa-3x text-muted mb-3"></i>
                <h5>No subjects found</h5>
                <p class="text-muted">Try adjusting your search</p>
            </div>
        </div>
    </div>
</div>

<!-- Create Subject Modal -->
<div class="modal fade" id="createSubjects" tabindex="-1" aria-labelledby="createSubjectsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createSubjectsLabel">
                    <i class="fa-solid fa-plus me-2"></i>Create New Subject
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="subjects-form" method="post">
                    <div class="my-2">
                        <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="subject_name" class="form-control" placeholder="ex. Mathematics"
                            required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Subject Code <span class="text-danger">*</span></label>
                        <input type="text" name="subject_code" class="form-control" placeholder="ex. MATH" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" class="form-select" required>
                            <option value="">Select Grade Level</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                        </select>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Subject Units <span class="text-danger">*</span></label>
                        <input type="number" name="subject_units" class="form-control" placeholder="ex. 3" min="1" max="10" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Initial Status <span class="text-danger">*</span></label>
                        <select name="subjects_status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-plus me-2"></i>Create Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjects" tabindex="-1" aria-labelledby="editSubjectsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="editSubjectsLabel">
                    <i class="fa-solid fa-pen me-2"></i>Update Subject
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="editSubjects-form" method="post">
                    <input type="hidden" name="subject_id" id="subject_id_edit">
                    <div class="my-2">
                        <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" id="subject_name" name="subject_name" class="form-control" placeholder="ex. Mathematics" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Subject Code <span class="text-danger">*</span></label>
                        <input type="text" id="subject_code" name="subject_code" class="form-control" placeholder="ex. MATH" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="grade_level" id="grade_level" class="form-select" required>
                            <option value="">Select Grade Level</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                        </select>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Subject Units <span class="text-danger">*</span></label>
                        <input type="number" id="subject_units" name="subject_units" class="form-control" placeholder="ex. 3" min="1" max="10" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Subject Status <span class="text-danger">*</span></label>
                        <select name="subjects_status" id="subjects_status" class="form-select" required>
                            <option value="">Select Subject status</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-save me-2"></i>Update Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Subject Modal -->
<div class="modal fade" id="deleteSubject" tabindex="-1" aria-labelledby="deleteSubjectLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="deleteSubjectLabel">
                    <i class="fa-solid fa-trash me-2"></i>Delete Subject
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="deleteSubject-form" method="post">
                    <input type="hidden" name="subject_id" id="subject_id_delete">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                        <h5>Confirm Deletion</h5>
                        <p class="text-muted">Are you sure you want to delete this subject? This action cannot be
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
        const subjectRows = document.querySelectorAll('.subject-row');
        const subjectsTableBody = document.getElementById('subjectsTableBody');
        const noResultsDiv = document.getElementById('noResults');
        const editButtons = document.querySelectorAll('.editSubjectBtn');
        const deleteButtons = document.querySelectorAll('.deleteSubjectBtn');

        // Subject data for edit form
        const subjectsData = <?= json_encode($subjects); ?>;

        // Search functionality
        function filterSubjects() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            subjectRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const code = row.getAttribute('data-code');
                const grade = row.getAttribute('data-grade');
                const status = row.getAttribute('data-status');

                let matchesSearch = true;

                if (searchTerm) {
                    matchesSearch = name.includes(searchTerm) ||
                        code.includes(searchTerm) ||
                        grade.includes(searchTerm) ||
                        status.includes(searchTerm);
                }

                if (matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                subjectsTableBody.style.display = 'none';
                noResultsDiv.classList.remove('d-none');
            } else {
                subjectsTableBody.style.display = '';
                noResultsDiv.classList.add('d-none');
            }

            updateRowNumbers();
        }

        function updateRowNumbers() {
            let counter = 1;
            subjectRows.forEach(row => {
                if (row.style.display !== 'none') {
                    const firstCell = row.querySelector('td:first-child');
                    if (firstCell) {
                        firstCell.textContent = counter++;
                    }
                }
            });
        }

        // Edit button click handler
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const subjectId = this.getAttribute('data-id');
                const subject = subjectsData.find(s => s.subject_id == subjectId);

                if (subject) {
                    document.getElementById('subject_id_edit').value = subject.subject_id;
                    document.getElementById('subject_name').value = subject.subject_name;
                    document.getElementById('subject_code').value = subject.subject_code;
                    document.getElementById('grade_level').value = subject.grade_level;
                    document.getElementById('subject_units').value = subject.subject_units;
                    document.getElementById('subjects_status').value = subject.subjects_status;

                    const modal = new bootstrap.Modal(document.getElementById('editSubjects'));
                    modal.show();
                }
            });
        });

        // Delete button click handler
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const subjectId = this.getAttribute('data-id');
                const subject = subjectsData.find(s => s.subject_id == subjectId);

                if (subject) {
                    document.getElementById('subject_id_delete').value = subject.subject_id;

                    const modal = new bootstrap.Modal(document.getElementById('deleteSubject'));
                    modal.show();
                }
            });
        });

        // Event listeners
        searchInput.addEventListener('input', filterSubjects);

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterSubjects();
            searchInput.focus();
        });

        // Add Enter key support for search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterSubjects();
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
        filterSubjects();
    });
</script>

<style>
    .scroll-subjects {
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

    /* Custom scrollbar for main container */
    .scroll-subjects::-webkit-scrollbar {
        width: 8px;
    }

    .scroll-subjects::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scroll-subjects::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scroll-subjects::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Grade level distribution */
    .bg-light {
        transition: all 0.2s ease;
    }

    .bg-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>