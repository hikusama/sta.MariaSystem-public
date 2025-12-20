<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
    $stmt = $pdo->prepare("SELECT * FROM school_year ORDER BY created_date DESC");
    $stmt->execute();
    $school_year = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 1;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-calendar-days me-2"></i>School Year Management</h4>
    </div>
</div>

<div class="row g-3 scroll-years">
    <!-- Search and Action Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search school years..."
                    id="searchInput">
            </div>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createSchoolYear"
                id="createSchoolYearBtn">
                <i class="fa-solid fa-plus me-2"></i> Create School Year
            </button>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>School Years Overview</h5>
                    <div class="row text-center">
                        <?php
                        $activeCount = array_filter($school_year, fn($sy) => $sy['school_year_status'] === 'Active');
                        $inactiveCount = array_filter($school_year, fn($sy) => $sy['school_year_status'] === 'Inactive');
                        ?>
                        <div class="col-md-4 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($school_year) ?></h3>
                                <small class="text-white">Total School Years</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($activeCount) ?></h3>
                                <small class="text-white">Active</small>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 class="text-white mb-1"><?= count($inactiveCount) ?></h3>
                                <small class="text-white">Inactive</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- School Years Table -->
    <div class="table-container-wrapper p-0">
        <?php
            $stmt = $pdo->prepare("SELECT * FROM school_year ORDER BY created_date DESC");
            $stmt->execute();
            $school_year = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
        ?>

        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="30%">School Year Name</th>
                        <th width="15%">Status</th>
                        <th width="20%">Created at</th>
                        <th width="30%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="schoolYearTableBody">
                    <?php if($school_year): 
                        $count = 1;
                        foreach($school_year as $sy) : ?>
                    <tr class="schoolyear-row" data-name="<?= htmlspecialchars(strtolower($sy["school_year_name"])) ?>"
                        data-status="<?= htmlspecialchars(strtolower($sy["school_year_status"])) ?>">
                        <td width="5%"><?= $count++ ?></td>
                        <td width="30%" class="schoolyear-name">
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder me-2">
                                    <i class="fa-solid fa-calendar text-primary"></i>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($sy["school_year_name"]) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td width="15%">
                            <span class="badge bg-<?= ($sy["school_year_status"] == 'Active') ? 'success' : 'secondary' ?>">
                                <i class="fa-solid fa-circle fa-xs me-1"></i>
                                <?= htmlspecialchars($sy["school_year_status"] ?? 'Inactive') ?>
                            </span>
                        </td>
                        <td width="20%">
                            <small><?= date('M d, Y', strtotime($sy["created_date"])) ?></small>
                        </td>
                        <td width="30%">
                            <div class="d-flex gap-1 justify-content-center">
                                <?php if($sy["school_year_status"] == 'Inactive'): ?>
                                    <button type="button" data-id="<?= $sy['school_year_id'] ?>"
                                        class="btn btn-sm btn-success activate-btn" title="Activate School Year">
                                        <i class="fa-solid fa-check me-1"></i> Activate
                                    </button>
                                <?php else: ?>
                                    <button type="button" data-id="<?= $sy['school_year_id'] ?>"
                                        class="btn btn-sm btn-warning deactivate-btn" title="Deactivate School Year">
                                        <i class="fa-solid fa-ban me-1"></i> Deactivate
                                    </button>
                                <?php endif; ?>
                                <button type="button" data-id="<?= $sy['school_year_id'] ?>"
                                    class="btn btn-sm btn-danger delete-btn" title="Delete School Year">
                                    <i class="fa-solid fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-3">No school years found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3"></i>
                <h5>No school years found</h5>
                <p class="text-muted">Try adjusting your search</p>
            </div>
        </div>
    </div>
</div>

<!-- Create School Year Modal -->
<div class="modal fade" id="createSchoolYear" tabindex="-1" aria-labelledby="createSchoolYearLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createSchoolYearLabel">
                    <i class="fa-solid fa-plus me-2"></i>Create New School Year
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="sy-form" method="post">
                    <div class="my-2">
                        <label class="form-label">School Year Name <span class="text-danger">*</span></label>
                        <input type="text" name="schoolYear_name" class="form-control" placeholder="ex. 2025 - 2026"
                            required>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Initial Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-plus me-2"></i>Create School Year
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Activate School Year Modal -->
<div class="modal fade" id="activateSY" tabindex="-1" aria-labelledby="activateSYLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="activateSYLabel">
                    <i class="fa-solid fa-check me-2"></i>Activate School Year
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="activateSY-form" method="post">
                    <input type="hidden" name="school_year_id" id="activate_school_year_id">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                        <h5>Confirm Activation</h5>
                        <p class="text-muted">Are you sure you want to activate this school year?</p>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fa-solid fa-check me-2"></i>Activate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate School Year Modal -->
<div class="modal fade" id="DeactivateSY" tabindex="-1" aria-labelledby="DeactivateSYLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="DeactivateSYLabel">
                    <i class="fa-solid fa-ban me-2"></i>Deactivate School Year
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="DeactivateSY-form" method="post">
                    <input type="hidden" name="school_year_id" id="deactivate_school_year_id">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                        <h5>Confirm Deactivation</h5>
                        <p class="text-muted">Are you sure you want to deactivate this school year?</p>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="button" class="btn btn-secondary me-3 px-4" data-bs-dismiss="modal">
                            <i class="fa-solid fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="fa-solid fa-ban me-2"></i>Deactivate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete School Year Modal -->
<div class="modal fade" id="deleteSchoolYear" tabindex="-1" aria-labelledby="deleteSchoolYearLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="deleteSchoolYearLabel">
                    <i class="fa-solid fa-trash me-2"></i>Delete School Year
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="deleteSchoolyear-form" method="post">
                    <input type="hidden" name="school_year_id" id="delete_school_year_id">
                    <div class="col-12 text-center mb-3">
                        <i class="fa-solid fa-triangle-exclamation fa-3x text-danger mb-3"></i>
                        <h5>Confirm Deletion</h5>
                        <p class="text-muted">Are you sure you want to delete this school year? This action cannot be
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
    const schoolYearRows = document.querySelectorAll('.schoolyear-row');
    const schoolYearTableBody = document.getElementById('schoolYearTableBody');
    const noResultsDiv = document.getElementById('noResults');
    const activateButtons = document.querySelectorAll('.activate-btn');
    const deactivateButtons = document.querySelectorAll('.deactivate-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    // School Year data
    const schoolYearData = <?= json_encode($school_year); ?>;

    // Search functionality
    function filterSchoolYears() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        schoolYearRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const status = row.getAttribute('data-status');

            let matchesSearch = true;

            if (searchTerm) {
                matchesSearch = name.includes(searchTerm) || status.includes(searchTerm);
            }

            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            schoolYearTableBody.style.display = 'none';
            noResultsDiv.classList.remove('d-none');
        } else {
            schoolYearTableBody.style.display = '';
            noResultsDiv.classList.add('d-none');
        }

        updateRowNumbers();
    }

    function updateRowNumbers() {
        let counter = 1;
        schoolYearRows.forEach(row => {
            if (row.style.display !== 'none') {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell) {
                    firstCell.textContent = counter++;
                }
            }
        });
    }

    // Activate button click handler
    activateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const schoolYearId = this.getAttribute('data-id');
            const schoolYear = schoolYearData.find(sy => sy.school_year_id == schoolYearId);

            if (schoolYear) {
                document.getElementById('activate_school_year_id').value = schoolYear.school_year_id;
                const modal = new bootstrap.Modal(document.getElementById('activateSY'));
                modal.show();
            }
        });
    });

    // Deactivate button click handler
    deactivateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const schoolYearId = this.getAttribute('data-id');
            const schoolYear = schoolYearData.find(sy => sy.school_year_id == schoolYearId);

            if (schoolYear) {
                document.getElementById('deactivate_school_year_id').value = schoolYear.school_year_id;
                const modal = new bootstrap.Modal(document.getElementById('DeactivateSY'));
                modal.show();
            }
        });
    });

    // Delete button click handler
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const schoolYearId = this.getAttribute('data-id');
            const schoolYear = schoolYearData.find(sy => sy.school_year_id == schoolYearId);

            if (schoolYear) {
                document.getElementById('delete_school_year_id').value = schoolYear.school_year_id;
                const modal = new bootstrap.Modal(document.getElementById('deleteSchoolYear'));
                modal.show();
            }
        });
    });

    // Event listeners
    searchInput.addEventListener('input', filterSchoolYears);
    
    // clearSearchBtn.addEventListener('click', function() {
    //     searchInput.value = '';
    //     filterSchoolYears();
    //     searchInput.focus();
    // });

    // Add Enter key support for search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterSchoolYears();
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
    filterSchoolYears();
});
</script>

<style>
.scroll-years {
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
.scroll-years::-webkit-scrollbar {
    width: 8px;
}

.scroll-years::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.scroll-years::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.scroll-years::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>