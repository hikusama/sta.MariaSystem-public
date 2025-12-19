 <?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
    $stmt = $pdo->prepare("SELECT * FROM classrooms ORDER BY classrooms.created_date DESC");
    $stmt->execute();
    $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 1;
    ?>
 <div class="d-flex justify-content-between align-items-center mb-4">
     <div class="mx-2">
         <h4><i class="fa-solid fa-school me-2"></i>Classrooms Management</h4>
     </div>
 </div>

 <div class="row g-3 scroll-classes">
     <!-- Search and Action Section -->
     <div class="row mb-3 justify-content-between align-items-center">
         <div class="col-md-8">
             <div class="input-group">
                 <input type="text" class="form-control" name="search" placeholder="Search classrooms..."
                     id="searchInput">
             </div>
         </div>
         <div class="col-md-4 text-end">
             <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createClassrooms"
                 id="createClassroomBtn">
                 <i class="fa-solid fa-plus me-2"></i> Create Classroom
             </button>
         </div>
     </div>

     <!-- Statistics Summary -->
     <div class="row mb-4">
         <div class="col-md-12">
             <div class="card border-0 shadow-sm">
                 <div class="card-body">
                     <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Classrooms Overview</h5>
                     <div class="row text-center">
                         <?php
                            $availableCount = array_filter($classrooms, fn($c) => $c['room_status'] === 'Available');
                            $unavailableCount = array_filter($classrooms, fn($c) => $c['room_status'] === 'Unavailable');
                            ?>
                         <div class="col-md-4 col-6 mb-3">
                             <div class="p-3 bg-primary bg-opacity-10 rounded">
                                 <h3 class="text-white mb-1"><?= count($classrooms) ?></h3>
                                 <small class="text-white">Total Classrooms</small>
                             </div>
                         </div>
                         <div class="col-md-4 col-6 mb-3">
                             <div class="p-3 bg-success bg-opacity-10 rounded">
                                 <h3 class="text-white mb-1"><?= count($availableCount) ?></h3>
                                 <small class="text-white">Available</small>
                             </div>
                         </div>
                         <div class="col-md-4 col-6 mb-3">
                             <div class="p-3 bg-danger bg-opacity-10 rounded">
                                 <h3 class="text-white mb-1"><?= count($unavailableCount) ?></h3>
                                 <small class="text-white">Unavailable</small>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <!-- Classrooms Table -->
     <div class="table-container-wrapper p-0">
         <?php
            try {
                $stmt = $pdo->prepare("SELECT * FROM classrooms LEFT JOIN school_year ON classrooms.school_year_id = school_year.school_year_id ORDER BY classrooms.created_date DESC");
                $stmt->execute();
                $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Classrooms query failed: ' . $e->getMessage());
                // Fallback to a simpler query if the joined column is missing
                $stmt = $pdo->prepare("SELECT * FROM classrooms ORDER BY created_date DESC");
                $stmt->execute();
                $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Ensure school_year_name key exists to avoid undefined index when rendering
                foreach ($classrooms as &$c) {
                    if (!isset($c['school_year_name'])) {
                        $c['school_year_name'] = '';
                    }
                }
                unset($c);
            }
            $count = 1;
            ?>

         <!-- Fixed Header -->
         <div class="table-responsive">
             <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                 <thead class="table-light">
                     <tr>
                         <th width="5%">#</th>
                         <th width="20%">Room Name</th>
                         <th width="15%">Room Type</th>
                         <th width="15%">Room Status</th>
                         <th width="20%">School Year</th>
                         <th width="20%">Created at</th>
                         <th width="25%">Action</th>
                     </tr>
                 </thead>
             </table>
         </div>

         <!-- Scrollable Body -->
         <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
             <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                 <tbody id="classroomsTableBody">
                     <?php if ($classrooms):
                            $count = 1;
                            foreach ($classrooms as $user) : ?>
                             <tr class="classroom-row" data-name="<?= htmlspecialchars(strtolower($user["room_name"])) ?>"
                                 data-type="<?= htmlspecialchars(strtolower($user["room_type"])) ?>"
                                 data-status="<?= htmlspecialchars(strtolower($user["room_status"])) ?>">
                                 <td width="5%"><?= $count++ ?></td>
                                 <td width="20%" class="classroom-name">
                                     <div class="d-flex align-items-center">
                                         <div class="avatar-placeholder me-2">
                                             <i class="fa-solid fa-door-closed text-secondary"></i>
                                         </div>
                                         <div>
                                             <strong><?= htmlspecialchars($user["room_name"]) ?></strong>
                                         </div>
                                     </div>
                                 </td>
                                 <td width="15%">
                                     <span class="badge bg-info"><?= htmlspecialchars($user["room_type"]) ?></span>
                                 </td>
                                 <td width="15%">
                                     <span
                                         class="badge bg-<?= ($user["room_status"] == 'Available') ? 'success' : 'secondary' ?>">
                                         <i class="fa-solid fa-circle fa-xs me-1"></i>
                                         <?= htmlspecialchars($user["room_status"] ?? 'Unavailable') ?>
                                     </span>
                                 </td>
                                 <td width="20%">
                                     <small><?= htmlspecialchars($user["school_year_name"]) ?></small>
                                 </td>
                                 <td width="20%">
                                     <small><?= date('M, d, y', strtotime($user["created_date"])) ?></small>
                                 </td>
                                 <td width="25%">
                                     <div class="d-flex gap-1 justify-content-center">
                                         <button type="button" data-id="<?= $user['room_id'] ?>"
                                             class="btn btn-sm btn-info editClassroomsBtn" title="Edit Classroom">
                                             <i class="fa-solid fa-pen me-1"></i> Edit
                                         </button>
                                         <button type="button" data-id="<?= $user['room_id'] ?>"
                                             class="btn btn-sm btn-danger deleteClassroomBtn" title="Delete Classroom">
                                             <i class="fa-solid fa-trash me-1"></i> Delete
                                         </button>
                                     </div>
                                 </td>
                             </tr>
                         <?php endforeach; ?>
                     <?php else: ?>
                         <tr>
                             <td colspan="6" class="text-center py-3">No classrooms found.</td>
                         </tr>
                     <?php endif; ?>
                 </tbody>
             </table>
         </div>

         <!-- Empty State -->
         <div id="noResults" class="text-center py-5 d-none">
             <div class="empty-state">
                 <i class="fa-solid fa-school fa-3x text-muted mb-3"></i>
                 <h5>No classrooms found</h5>
                 <p class="text-muted">Try adjusting your search</p>
             </div>
         </div>
     </div>
 </div>

 <!-- Create Classroom Modal -->
 <div class="modal fade" id="createClassrooms" tabindex="-1" aria-labelledby="createClassroomsLabel" aria-hidden="true">
     <div class="modal-dialog modal-md">
         <div class="modal-content">
             <div class="modal-header bg-danger text-white">
                 <h5 class="modal-title text-white" id="createClassroomsLabel">
                     <i class="fa-solid fa-plus me-2"></i>Create New Classroom
                 </h5>
                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                     aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <form class="row g-3" id="classroom-form" method="post">
                     <div class="my-2">
                         <label class="form-label">Classroom Name <span class="text-danger">*</span></label>
                         <input type="text" name="classroom_name" class="form-control" placeholder="ex. DAS 202"
                             required>
                     </div>
                     <div class="my-2">
                         <label class="form-label">Classroom Type <span class="text-danger">*</span></label>
                         <input type="text" name="classroom_type" class="form-control" placeholder="ex. Lecture Room"
                             required>
                     </div>
                     <div class="col-12 text-center mt-3">
                         <button type="submit" class="btn btn-danger px-5">
                             <i class="fa-solid fa-plus me-2"></i>Create Classroom
                         </button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>

 <!-- Edit Classroom Modal -->
 <div class="modal fade" id="editClassroom" tabindex="-1" aria-labelledby="editClassroomLabel" aria-hidden="true">
     <div class="modal-dialog modal-md">
         <div class="modal-content">
             <div class="modal-header bg-danger text-white">
                 <h5 class="modal-title text-white" id="editClassroomLabel">
                     <i class="fa-solid fa-pen me-2"></i>Update Classroom
                 </h5>
                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                     aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <form class="row g-3" id="editClassroom-form" method="post">
                     <input type="hidden" name="classroom_id" id="classroom_ids">
                     <div class="my-2">
                         <label class="form-label">Room Status <span class="text-danger">*</span></label>
                         <select name="room_status" id="room_status" class="form-select" required>
                             <option value="">Select room status</option>
                             <option value="Available">Available</option>
                             <option value="Unavailable">Unavailable</option>
                         </select>
                     </div>
                     <div class="my-2">
                         <label class="form-label">Classroom Name <span class="text-danger">*</span></label>
                         <input type="text" id="classroom_name" name="classroom_name" class="form-control"
                             placeholder="ex. DAS 202" required>
                     </div>
                     <div class="my-2">
                         <label class="form-label">Classroom Type <span class="text-danger">*</span></label>
                         <input type="text" id="classroom_type" name="classroom_type" class="form-control"
                             placeholder="ex. Lecture Room" required>
                     </div>
                     <div class="col-12 text-center mt-3">
                         <button type="submit" class="btn btn-danger px-5">
                             <i class="fa-solid fa-save me-2"></i>Update Classroom
                         </button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>

 <!-- Delete Classroom Modal -->
 <div class="modal fade" id="deleteClassroom" tabindex="-1" aria-labelledby="deleteClassroomLabel" aria-hidden="true">
     <div class="modal-dialog modal-md">
         <div class="modal-content">
             <div class="modal-header bg-danger text-white">
                 <h5 class="modal-title text-white" id="deleteClassroomLabel">
                     <i class="fa-solid fa-trash me-2"></i>Delete Classroom
                 </h5>
                 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                     aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <form class="row g-3" id="deleteClassroom-form" method="post">
                     <input type="hidden" name="classroom_id" id="classroom_id">
                     <div class="col-12 text-center mb-3">
                         <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                         <h5>Confirm Deletion</h5>
                         <p class="text-muted">Are you sure you want to delete this classroom? This action cannot be
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
         const classroomRows = document.querySelectorAll('.classroom-row');
         const classroomsTableBody = document.getElementById('classroomsTableBody');
         const noResultsDiv = document.getElementById('noResults');
         const editButtons = document.querySelectorAll('.editClassroomsBtn');
         const deleteButtons = document.querySelectorAll('.deleteClassroomBtn');
         const clearSearchBtn = document.getElementById('clearSearch');

         // Classroom data for edit form (you would typically fetch this via AJAX)
         const classroomsData = <?= json_encode($classrooms); ?>;

         // Search functionality
         function filterClassrooms() {
             const searchTerm = searchInput.value.toLowerCase().trim();
             let visibleCount = 0;

             classroomRows.forEach(row => {
                 const name = row.getAttribute('data-name');
                 const type = row.getAttribute('data-type');
                 const status = row.getAttribute('data-status');

                 let matchesSearch = true;

                 if (searchTerm) {
                     matchesSearch = name.includes(searchTerm) ||
                         type.includes(searchTerm) ||
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
                 classroomsTableBody.style.display = 'none';
                 noResultsDiv.classList.remove('d-none');
             } else {
                 classroomsTableBody.style.display = '';
                 noResultsDiv.classList.add('d-none');
             }

             updateRowNumbers();
         }

         function updateRowNumbers() {
             let counter = 1;
             classroomRows.forEach(row => {
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
                 const classroomId = this.getAttribute('data-id');
                 const classroom = classroomsData.find(c => c.room_id == classroomId);

                 if (classroom) {
                     document.getElementById('classroom_ids').value = classroom.room_id;
                     document.getElementById('room_status').value = classroom.room_status;
                     document.getElementById('classroom_name').value = classroom.room_name;
                     document.getElementById('classroom_type').value = classroom.room_type;

                     const modal = new bootstrap.Modal(document.getElementById('editClassroom'));
                     modal.show();
                 }
             });
         });

         // Delete button click handler
         deleteButtons.forEach(button => {
             button.addEventListener('click', function() {
                 const classroomId = this.getAttribute('data-id');
                 document.getElementById('classroom_id').value = classroomId;

                 const modal = new bootstrap.Modal(document.getElementById('deleteClassroom'));
                 modal.show();
             });
         });

         // Event listeners
         searchInput.addEventListener('input', filterClassrooms);

         if (clearSearchBtn) {
             clearSearchBtn.addEventListener('click', function() {
                 searchInput.value = '';
                 filterClassrooms();
                 searchInput.focus();
             });
         }

         // Add Enter key support for search
         searchInput.addEventListener('keypress', function(e) {
             if (e.key === 'Enter') {
                 filterClassrooms();
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
         filterClassrooms();
     });
 </script>

 <style>
     .scroll-classes {
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

     #clearSearch:hover {
         background-color: #e9ecef;
     }

     .btn:hover {
         transform: translateY(-1px);
         transition: all 0.2s ease;
     }
 </style>