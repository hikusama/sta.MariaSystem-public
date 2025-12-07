
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><i class="fa-solid fa-school me-2"></i>Sections Management</h4>
        </div>
    </div>
    
    <!-- Search and Create Button -->
    <div class="row col-md-12 col-12 mb-2 d-flex justify-content-between">
        <div class="col-md-4">
            <input type="text" class="form-control" name="search" id="searchInput" placeholder="Search sections...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger m-0 w-100" data-bs-toggle="modal" data-bs-target="#createSection">+ Create Section</button>
        </div>
    </div>

    <!-- Create Section Modal -->
    <div class="modal fade" id="createSection" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="createSectionLabel">Create New Section</h5>
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
                            <input readonly type="text" name="section_status" value="Available" class="form-control">
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">Create Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sections Table -->
    <div class="classroomDisplays mt-3">
        <div class="table-container-wrapper pe-4">
            <?php
                $stmt = $pdo->prepare("SELECT * FROM sections ORDER BY created_date DESC");
                $stmt->execute();
                $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $count = 1;
            ?>

            <div class="table-responsive text-center">
                <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Section Name</th>
                            <th width="15%">Grade Level</th>
                            <th width="15%">Section Status</th>
                            <th width="20%">Created at</th>
                            <th width="25%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="sectionsTableBody">
                        <?php if(!empty($sections)): ?>
                            <?php foreach($sections as $section) : ?>
                            <tr>
                                <td width="5%"><?= $count++ ?></td>
                                <td width="20%"><?= htmlspecialchars($section["section_name"]) ?></td>
                                <td width="15%"><?= htmlspecialchars($section["section_grade_level"]) ?></td>
                                <td width="15%">
                                    <span class="badge bg-<?= ($section["section_status"] == 'Available') ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($section["section_status"] ?? 'Inactive') ?>
                                    </span>
                                </td>
                                <td width="20%"><?= htmlspecialchars($section["created_date"]) ?></td>
                                <td width="25%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" data-id="<?= $section["section_id"] ?>" class="btn btn-info btn-sm editSectionBtn">Edit</button>
                                        <button type="button" data-id="<?= $section["section_id"] ?>" class="btn btn-danger btn-sm deleteSectionBtn">Delete</button>
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
        </div>
    </div>

    <!-- Delete Section Modal -->
    <div class="modal fade" id="deleteSection" tabindex="-1" aria-labelledby="deleteSectionLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="deleteSectionLabel">Delete Section</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="deleteSection-form" method="post">
                        <input type="hidden" name="section_id" id="section_id">
                        <p class="text-center">Are you sure you want to <strong>delete</strong> this section?</p>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-danger px-5">Delete</button>
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
                    <h5 class="modal-title text-white" id="editSectionsLabel">Update Section</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="editSection-form" method="post">
                        <input type="hidden" name="section_id" id="section_ids">
                        <div class="my-2">
                            <label class="form-label">Section Status <span class="text-danger">*</span></label>
                            <select name="section_status" id="section_status" class="form-control" required>
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
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">Update Section</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('sectionsTableBody');
    
    if (!searchInput || !tableBody) return;
    
    // Store original rows data
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));
    const rowData = originalRows.map(row => {
        const cells = row.querySelectorAll('td');
        return {
            element: row,
            sectionName: cells[1]?.textContent?.toLowerCase() || '',
            gradeLevel: cells[2]?.textContent?.toLowerCase() || '',
            sectionStatus: cells[3]?.textContent?.toLowerCase() || '',
            createdDate: cells[4]?.textContent?.toLowerCase() || ''
        };
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 1;
        
        rowData.forEach((data, index) => {
            const matchesSearch = searchTerm === '' ||
                data.sectionName.includes(searchTerm) ||
                data.gradeLevel.includes(searchTerm) ||
                data.sectionStatus.includes(searchTerm) ||
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
                newRow.innerHTML = '<td colspan="6" class="text-center py-3">No sections found matching your search.</td>';
                tableBody.appendChild(newRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }

    // Add event listener for search input
    searchInput.addEventListener('input', filterTable);

    // Add event listeners for edit buttons
    document.querySelectorAll('.editSectionBtn').forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-id');
            // TODO: Fetch section data and populate edit modal
            console.log('Edit section ID:', sectionId);
        });
    });

    // Add event listeners for delete buttons
    document.querySelectorAll('.deleteSectionBtn').forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-id');
            document.getElementById('section_id').value = sectionId;
            // TODO: Show confirmation modal
        });
    });
});
</script>