
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><i class="fa-solid fa-school me-2"></i>School Year Management</h4>
        </div>
    </div>
    
    <!-- Search and Create Button -->
    <div class="row col-md-12 col-12 mb-2 d-flex justify-content-between">
        <div class="col-md-4">
            <input type="text" class="form-control" name="search" id="searchInput" placeholder="Search school years...">
        </div>
        <div class="col-md-3">
            <button class="btn btn-danger m-0 w-100" data-bs-toggle="modal" data-bs-target="#createSchoolYear">+ Create School Year</button>
        </div>
    </div>

    <!-- Create School Year Modal -->
    <div class="modal fade" id="createSchoolYear" tabindex="-1" aria-labelledby="createSchoolYearLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="createSchoolYearLabel">Create New School Year</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="sy-form" method="post">
                        <div class="my-2">
                            <label class="form-label">School Year Name <span class="text-danger">*</span></label>
                            <input type="text" name="schoolYear_name" class="form-control" placeholder="ex. 2025 - 2026" required>
                        </div>
                        <div class="my-2">
                            <label class="form-label">School Year Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">Create School Year</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- School Year Table -->
    <div class="schoolYearDisplays mt-3">
        <div class="table-container-wrapper pe-4">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM school_year ORDER BY created_date DESC");
            $stmt->execute();
            $school_year = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
            ?>

            <div class="table-responsive text-center">
                <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">School Year Name</th>
                            <th width="20%">School Year Status</th>
                            <th width="20%">Created at</th>
                            <th width="25%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="schoolYearTableBody">
                        <?php if(!empty($school_year)): ?>
                            <?php foreach($school_year as $sy) : ?>
                            <tr>
                                <td width="5%"><?= $count++ ?></td>
                                <td width="25%"><?= htmlspecialchars($sy["school_year_name"]) ?></td>
                                <td width="20%">
                                    <span class="badge bg-<?= ($sy["school_year_status"] == 'Active') ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($sy["school_year_status"] ?? 'Inactive') ?>
                                    </span>
                                </td>
                                <td width="20%"><?= htmlspecialchars($sy["created_date"]) ?></td>
                                <td width="25%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <?php if($sy["school_year_status"] == 'Inactive'): ?>
                                            <button type="button" data-id="<?= $sy["school_year_id"] ?>" class="btn btn-success btn-sm activate-btn">Activate</button>
                                        <?php else: ?>
                                            <button type="button" data-id="<?= $sy["school_year_id"] ?>" class="btn btn-primary btn-sm deactivate-btn">Deactivate</button>
                                        <?php endif; ?>
                                        <button type="button" data-id="<?= $sy["school_year_id"] ?>" class="btn btn-danger btn-sm delete-btn">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-3">No School Year Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activate School Year Modal -->
    <div class="modal fade" id="activateSY" tabindex="-1" aria-labelledby="activateSYLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="activateSYLabel">Activate School Year</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="activateSY-form" method="post">
                        <input type="hidden" name="school_year_id" id="activate_school_year_id">
                        <p class="text-center text-dark mb-3">Are you sure you want to <strong>activate</strong> this school year?</p>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success px-5">Activate</button>
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
                    <h5 class="modal-title text-white" id="DeactivateSYLabel">Deactivate School Year</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="DeactivateSY-form" method="post">
                        <input type="hidden" name="school_year_id" id="deactivate_school_year_id">
                        <p class="text-center text-dark mb-3">Are you sure you want to <strong>deactivate</strong> this school year?</p>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-warning px-5">Deactivate</button>
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
                    <h5 class="modal-title text-white" id="deleteSchoolYearLabel">Delete School Year</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="deleteSchoolyear-form" method="post">
                        <input type="hidden" name="school_year_id" id="delete_school_year_id">
                        <p class="text-center text-dark mb-3">Are you sure you want to <strong>delete</strong> this school year?</p>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-danger px-5">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('schoolYearTableBody');
    
    if (!searchInput || !tableBody) return;
    
    // Store original rows data
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));
    const rowData = originalRows.map(row => {
        const cells = row.querySelectorAll('td');
        return {
            element: row,
            schoolYearName: cells[1]?.textContent?.toLowerCase() || '',
            schoolYearStatus: cells[2]?.textContent?.toLowerCase() || '',
            createdDate: cells[3]?.textContent?.toLowerCase() || ''
        };
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 1;
        
        rowData.forEach((data, index) => {
            const matchesSearch = searchTerm === '' ||
                data.schoolYearName.includes(searchTerm) ||
                data.schoolYearStatus.includes(searchTerm) ||
                data.createdDate.includes(searchTerm);

            if (matchesSearch) {
                data.element.style.display = '';
                // Update row number
                const firstCell = data.element.querySelector('td:first-child');
                if (firstCell) {
                    firstCell.textContent = visibleCount++;
                }
            } else {
                data.element.style.display = 'none';
            }
        });
        
        // Handle no results
        const visibleRows = rowData.filter(data => data.element.style.display !== 'none');
        const noResultsRow = tableBody.querySelector('.no-results-row');
        
        if (visibleRows.length === 0 && originalRows.length > 0) {
            if (!noResultsRow) {
                const newRow = document.createElement('tr');
                newRow.className = 'no-results-row';
                newRow.innerHTML = '<td colspan="5" class="text-center py-3">No school years found matching your search.</td>';
                tableBody.appendChild(newRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }

    // Add event listener for search input
    searchInput.addEventListener('input', filterTable);

    // Add event listeners for action buttons
    tableBody.addEventListener('click', function(e) {
        const target = e.target;
        
        // Activate button
        if (target.classList.contains('activate-btn')) {
            const schoolYearId = target.getAttribute('data-id');
            document.getElementById('activate_school_year_id').value = schoolYearId;
            // Show activate modal (you need to initialize Bootstrap modal)
            const activateModal = new bootstrap.Modal(document.getElementById('activateSY'));
            activateModal.show();
        }
        
        // Deactivate button
        else if (target.classList.contains('deactivate-btn')) {
            const schoolYearId = target.getAttribute('data-id');
            document.getElementById('deactivate_school_year_id').value = schoolYearId;
            // Show deactivate modal
            const deactivateModal = new bootstrap.Modal(document.getElementById('DeactivateSY'));
            deactivateModal.show();
        }
        
        // Delete button
        else if (target.classList.contains('delete-btn')) {
            const schoolYearId = target.getAttribute('data-id');
            document.getElementById('delete_school_year_id').value = schoolYearId;
            // Show delete modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteSchoolYear'));
            deleteModal.show();
        }
    });
});
</script>