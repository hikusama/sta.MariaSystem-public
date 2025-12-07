
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><i class="fa-solid fa-school me-2"></i>Subjects Management</h4>
        </div>
    </div>
    
    <!-- Search and Create Button -->
    <div class="row col-md-12 col-12 mb-2 d-flex justify-content-between">
        <div class="col-md-4">
            <input type="text" class="form-control" id="searchInput" placeholder="Search subjects...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-danger m-0 w-100" data-bs-toggle="modal" data-bs-target="#createSubjects">+ Create Subjects</button>
        </div>
    </div>

    <!-- Create Subjects Modal -->
    <div class="modal fade" id="createSubjects" tabindex="-1" aria-labelledby="createSubjectsLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="createSubjectsLabel">Create New Subject</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="subjects-form" method="post">
                        <div class="my-2">
                            <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" name="subject_name" class="form-control" placeholder="ex. Mathematics" required>
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
                            <label class="form-label">Subject Status <span class="text-danger">*</span></label>
                            <select name="subjects_status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Available">Available</option>
                                <option value="Unavailable">Unavailable</option>
                            </select>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">Create Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="subjectsDisplays mt-3">
        <div class="table-container-wrapper pe-4">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM subjects ORDER BY created_date DESC");
            $stmt->execute();
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
            ?>

            <div class="table-responsive-lg">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Subject Name</th>
                            <th width="10%">Subject Code</th>
                            <th width="10%">Subject Units</th>
                            <th width="15%">Subject Status</th>
                            <th width="15%">Grade Level</th>
                            <th width="15%">Created Date</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="subjectsTableBody scroll-visible" style="overflow-y: scroll !important; height: 400px !important;">
                        <?php if(!empty($subjects)): ?>
                            <?php foreach($subjects as $subject) : ?>
                            <tr>
                                <td width="5%"><?= $count++ ?></td>
                                <td width="15%"><?= htmlspecialchars($subject["subject_name"]) ?></td>
                                <td width="10%"><?= htmlspecialchars($subject["subject_code"] ?? 'N/A') ?></td>
                                <td width="10%"><?= htmlspecialchars($subject["subject_units"]) ?></td>
                                <td width="15%">
                                    <span class="badge bg-<?= ($subject["subjects_status"] == 'Available') ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($subject["subjects_status"] ?? 'Inactive') ?>
                                    </span>
                                </td>
                                <td width="15%"><?= htmlspecialchars($subject["grade_level"]) ?></td>
                                <td width="15%"><?= htmlspecialchars($subject["created_date"]) ?></td>
                                <td width="15%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" data-id="<?= $subject["subject_id"] ?>" class="btn btn-info btn-sm editSubjectBtn">Edit</button>
                                        <button type="button" data-id="<?= $subject["subject_id"] ?>" class="btn btn-danger btn-sm deleteSubjectBtn">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-3">No Subjects Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Subject Modal -->
    <div class="modal fade" id="deleteSubject" tabindex="-1" aria-labelledby="deleteSubjectLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="deleteSubjectLabel">Delete Subject</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="deleteSubject-form" method="post">
                        <input type="hidden" name="subject_id" id="subject_id_delete">
                        <p class="text-center mb-3">Are you sure you want to <strong>delete</strong> this subject?</p>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-danger px-5">Delete</button>
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
                    <h5 class="modal-title text-white" id="editSubjectsLabel">Update Subject</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">Update Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('subjectsTableBody');
    
    if (!searchInput || !tableBody) return;
    
    // Store original rows data
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));
    const rowData = originalRows.map(row => {
        const cells = row.querySelectorAll('td');
        return {
            element: row,
            subjectName: cells[1]?.textContent?.toLowerCase() || '',
            subjectCode: cells[2]?.textContent?.toLowerCase() || '',
            subjectUnits: cells[3]?.textContent?.toLowerCase() || '',
            subjectStatus: cells[4]?.textContent?.toLowerCase() || '',
            gradeLevel: cells[5]?.textContent?.toLowerCase() || '',
            createdDate: cells[6]?.textContent?.toLowerCase() || ''
        };
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 1;
        
        rowData.forEach((data, index) => {
            const matchesSearch = searchTerm === '' ||
                data.subjectName.includes(searchTerm) ||
                data.subjectCode.includes(searchTerm) ||
                data.subjectUnits.includes(searchTerm) ||
                data.subjectStatus.includes(searchTerm) ||
                data.gradeLevel.includes(searchTerm) ||
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
                newRow.innerHTML = '<td colspan="8" class="text-center py-3">No subjects found matching your search.</td>';
                tableBody.appendChild(newRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
    }

    // Add event listener for search input
    searchInput.addEventListener('input', filterTable);

    // Delete Subject Modal
    document.querySelectorAll('.deleteSubjectBtn').forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.getAttribute('data-id');
            document.getElementById('subject_id_delete').value = subjectId;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteSubject'));
            deleteModal.show();
        });
    });

    // Edit Subject Modal
    document.querySelectorAll('.editSubjectBtn').forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.getAttribute('data-id');
            document.getElementById('subject_id_edit').value = subjectId;
            
            // Here you would typically fetch the subject data and populate the form
            // For example:
            // fetchSubjectData(subjectId).then(data => {
            //     document.getElementById('subject_name').value = data.subject_name;
            //     document.getElementById('subject_code').value = data.subject_code;
            //     document.getElementById('grade_level').value = data.grade_level;
            //     document.getElementById('subject_units').value = data.subject_units;
            //     document.getElementById('subjects_status').value = data.subjects_status;
            // });
            
            const editModal = new bootstrap.Modal(document.getElementById('editSubjects'));
            editModal.show();
        });
    });
});
</script>