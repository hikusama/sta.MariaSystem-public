<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM sections ORDER BY created_date DESC");
$stmt->execute();
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = 1;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-layer-group me-2"></i>Sections Management</h4>
    </div>
</div>

<div class="row g-3">
    <!-- Search and Action Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" name="search" id="searchInput" placeholder="Search sections by name or grade level...">

            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createSection">
                <i class="fa-solid fa-plus me-2"></i> Create Section
            </button>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Sections Overview</h5>
                    <div class="row text-center">
                        <?php
                        $availableCount = array_filter($sections, fn($s) => $s['section_status'] === 'Available');
                        $unavailableCount = array_filter($sections, fn($s) => $s['section_status'] === 'Unavailable');

                        // Count sections by grade level
                        $gradeLevels = array_count_values(array_column($sections, 'section_grade_level'));
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($sections) ?></h3>
                                <small class="text-white">Total Sections</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($availableCount) ?></h3>
                                <small class="text-white">Available</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($unavailableCount) ?></h3>
                                <small class="text-white">Unavailable</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($gradeLevels) ?></h3>
                                <small class="text-white">Grade Levels</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sections Table -->
    <div class="table-container-wrapper p-0">
        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Section Name</th>
                        <th width="15%">Grade Level</th>
                        <th width="15%">Section Status</th>
                        <th width="20%">Created at</th>
                        <th width="25%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="sectionsTableBody">
                    <?php if (!empty($sections)):
                        $count = 1;
                        foreach ($sections as $section) : ?>
                            <tr class="section-row"
                                data-name="<?= htmlspecialchars(strtolower($section["section_name"])) ?>"
                                data-grade="<?= htmlspecialchars(strtolower($section["section_grade_level"])) ?>"
                                data-status="<?= htmlspecialchars(strtolower($section["section_status"])) ?>">
                                <td width="5%"><?= $count++ ?></td>
                                <td width="20%" class="section-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-layer-group text-secondary"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($section["section_name"]) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-info"><?= htmlspecialchars($section["section_grade_level"]) ?></span>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-<?= ($section["section_status"] == 'Available') ? 'success' : 'secondary' ?>">
                                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                                        <?= htmlspecialchars($section["section_status"] ?? 'Unavailable') ?>
                                    </span>
                                </td>
                                <td width="20%">
                                    <small><?= date('M d, Y', strtotime($section["created_date"])) ?></small>
                                </td>
                                <td width="25%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" data-id="<?= $section["section_id"] ?>"
                                            class="btn btn-sm btn-info editSectionBtn"
                                            title="Edit Section">
                                            <i class="fa-solid fa-pen me-1"></i> Edit
                                        </button>
                                        <button type="button" data-id="<?= $section["section_id"] ?>"
                                            class="btn btn-sm btn-danger deleteSectionBtn"
                                            title="Delete Section">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">No sections found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-layer-group fa-3x text-muted mb-3"></i>
                <h5>No sections found</h5>
                <p class="text-muted">Try adjusting your search</p>
            </div>
        </div>
    </div>
</div>

<!-- Create Section Modal -->
<div class="modal fade" id="createSection" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createSectionLabel">
                    <i class="fa-solid fa-plus me-2"></i>Create New Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="section-form" method="post">
                    <div class="my-2">
                        <label class="form-label">Section Name <span class="text-danger">*</span></label>
                        <input type="text" name="section_name" class="form-control" placeholder="ex. Jupiter" required>
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
                        <label class="form-label">Section Status</label>
                        <div class="form-control bg-light">Available</div>
                        <input type="hidden" name="section_status" value="Available">
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-plus me-2"></i>Create Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div class="modal fade" id="editSections" tabindex="-1" aria-labelledby="editSectionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="editSectionsLabel">
                    <i class="fa-solid fa-pen me-2"></i>Update Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="editSection-form" method="post">
                    <input type="hidden" name="section_id" id="section_ids">
                    <div class="my-2">
                        <label class="form-label">Section Status <span class="text-danger">*</span></label>
                        <select name="section_status" id="section_status" class="form-select" required>
                            <option value="">Select section status</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Section Name <span class="text-danger">*</span></label>
                        <input type="text" id="section_name" name="section_name" class="form-control" placeholder="ex. Jupiter" required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                        <select name="section_grade_level" id="section_grade_level" class="form-select" required>
                            <option value="">Select Grade Level</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                        </select>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-save me-2"></i>Update Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Section Modal -->
<div class="modal fade" id="deleteSection" tabindex="-1" aria-labelledby="deleteSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="deleteSectionLabel">
                    <i class="fa-solid fa-trash me-2"></i>Delete Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="deleteSection-form" method="post">
                    <input type="hidden" name="section_id" id="section_id">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                        <h5>Confirm Deletion</h5>
                        <p class="text-muted">Are you sure you want to delete this section? This action cannot be undone.</p>
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
        const sectionRows = document.querySelectorAll('.section-row');
        const sectionsTableBody = document.getElementById('sectionsTableBody');
        const noResultsDiv = document.getElementById('noResults');
        const editButtons = document.querySelectorAll('.editSectionBtn');
        const deleteButtons = document.querySelectorAll('.deleteSectionBtn');

        // Sections data for edit form
        const sectionsData = <?= json_encode($sections); ?>;

        // Search functionality
        function filterSections() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            sectionRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const grade = row.getAttribute('data-grade');
                const status = row.getAttribute('data-status');

                let matchesSearch = true;

                if (searchTerm) {
                    matchesSearch = name.includes(searchTerm) ||
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
                sectionsTableBody.style.display = 'none';
                noResultsDiv.classList.remove('d-none');
            } else {
                sectionsTableBody.style.display = '';
                noResultsDiv.classList.add('d-none');
            }

            updateRowNumbers();
        }

        function updateRowNumbers() {
            let counter = 1;
            sectionRows.forEach(row => {
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
                const sectionId = this.getAttribute('data-id');
                const section = sectionsData.find(s => s.section_id == sectionId);

                if (section) {
                    document.getElementById('section_ids').value = section.section_id;
                    document.getElementById('section_status').value = section.section_status;
                    document.getElementById('section_name').value = section.section_name;
                    document.getElementById('section_grade_level').value = section.section_grade_level;

                    const modal = new bootstrap.Modal(document.getElementById('editSections'));
                    modal.show();
                }
            });
        });

        // Delete button click handler
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-id');
                document.getElementById('section_id').value = sectionId;

                const modal = new bootstrap.Modal(document.getElementById('deleteSection'));
                modal.show();
            });
        });

        // Event listeners
        searchInput.addEventListener('input', filterSections);

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            filterSections();
            searchInput.focus();
        });

        // Add Enter key support for search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterSections();
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
        filterSections();
    });
</script>

<style>
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

    #clearSearch:hover {
        background-color: #e9ecef;
    }

    .form-control.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
        color: #495057;
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>