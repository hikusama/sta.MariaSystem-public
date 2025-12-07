<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="mx-2">
        <h4><i class="fa-solid fa-school me-2"></i>Classrooms Management</h4>
    </div>
</div>
<div class="row col-md-12 col-11 justify-content-between mb-2">
    <div class="col-md-4 position-relative">
        <input type="text" 
            class="form-control" 
            name="search" 
            placeholder="Search classrooms..." 
            id="searchInput">
    </div>
    
    <div class="col-md-2">
        <button class="btn btn-danger m-0" data-bs-toggle="modal" data-bs-target="#createClassrooms" id="createClassroomBtn">Create Classrooms</button>
    </div>
<!-- add Classroom Modal -->
 <div class="modal fade" id="createClassrooms" tabindex="-1" aria-labelledby="createClassroomsLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="createClassroomsLabel">Create New Classrooms</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="classroom-form" method="post">
                    <div class="my-2">
                        <label class="form-label">Classroom Name</label>
                        <input type="text" name="classroom_name" class="form-control" placeholder="ex. DAS 202">
                    </div>
                    <div class="my-2">
                        <label class="form-label">Classroom Type</label>
                        <input type="text" name="classroom_type" class="form-control" placeholder="ex. Lecture Room">
                    </div>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Create Classroom 
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- dsiplays Classrooms -->
<div class="classroomDisplays mt-3">
    <div class="table-container-wrapper">
        <?php
            $stmt = $pdo->prepare("SELECT * FROM classrooms ORDER BY created_date DESC");
            $stmt->execute();
            $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = 1;
        ?>

        <!-- Fixed Header -->
        <div class="table-responsive text-center">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Room Name</th>
                        <th width="15%">Room Type</th>
                        <th width="15%">Room Status</th>
                        <th width="20%">Created at</th>
                        <th width="25%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($classrooms as $user) : ?>
                    <tr>
                        <td width="5%"><?= $count++ ?></td>
                        <td width="20%">
                            <?= htmlspecialchars($user["room_name"])?>
                        </td>
                        <td width="15%"><?= htmlspecialchars($user["room_type"]) ?></td>
                        <td width="15%">
                            <span class="badge bg-<?= ($user["room_status"] == 'Available') ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($user["room_status"] ?? 'Inactive') ?>
                            </span>
                        </td>
                        <td width="20%"><?= htmlspecialchars($user["created_date"]) ?></td>
                        <td width="25%">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button" data-id="<?= $user['room_id'] ?>" class="btn btn-info btn-sm editClassroomsBtn">Edit</button>
                                <button type="button" data-id="<?= $user['room_id'] ?>" class="btn btn-danger btn-sm deleteClassroomBtn">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- activate School Year -->
<div class="modal fade" id="activateSY" tabindex="-1" aria-labelledby="activateSYLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="activateSYLabel">Activation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="activateSY-form" method="post">
                    <input type="hidden" name="school_year_id" id="school_year_id">
                    <span class="m-2">Are you Sure you want to <strong>Activate</strong> this School year?</span>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Activate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="DeactivateSY" tabindex="-1" aria-labelledby="DeactivateSYLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="DeactivateSYLabel">Deactivation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="DeactivateSY-form" method="post">
                    <input type="hidden" name="school_year_id" id="schoolyear_id">
                    <span class="m-2">Are you Sure you want to <strong>Dectivate</strong> this School year?</span>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Deactivate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- classrooms modal -->
 <div class="modal fade" id="deleteClassroom" tabindex="-1" aria-labelledby="deleteClassroomLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="deleteClassroomLabel">Deactivation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="deleteClassroom-form" method="post">
                    <input type="hidden" name="classroom_id" id="classroom_id">
                    <span class="m-2">Are you Sure you want to <strong>Delete</strong> this Classroom?</span>
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary px-5">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
 <div class="modal fade" id="editClassroom" tabindex="-1" aria-labelledby="editClassroomLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="editClassroomLabel">Update classroom</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="editClassroom-form" method="post">
                    <input type="hidden" name="classroom_id" id="classroom_ids">
                     <div class="my-2">
                        <label class="form-label">Room Status</label>
                       <select name="room_status" id="room_status" class="form-control">
                            <option value="">Select room status</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                       </select>
                    </div>
                    <div class="my-2">
                        <label class="form-label">Classroom Name</label>
                        <input type="text" id="classroom_name" name="classroom_name" class="form-control" placeholder="ex. DAS 202">
                    </div>
                    <div class="my-2">
                        <label class="form-label">Classroom Type</label>
                        <input type="text" id="classroom_type" name="classroom_type" class="form-control" placeholder="ex. Lecture Room">
                    </div>
                        <button type="submit" class="btn btn-primary px-5">
                            edit
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
    const tableBody = document.querySelector('.table-container-wrapper tbody');
    
    if (!searchInput || !tableBody) return;
    
    // Store original rows (skip the header row)
    const originalRows = Array.from(tableBody.querySelectorAll('tr'));
    
    // Extract row data for better filtering
    const rowData = originalRows.map(row => {
        const cells = row.querySelectorAll('td');
        return {
            element: row,
            roomName: cells[1]?.textContent?.toLowerCase() || '',
            roomType: cells[2]?.textContent?.toLowerCase() || '',
            roomStatus: cells[3]?.textContent?.toLowerCase() || '',
            createdDate: cells[4]?.textContent?.toLowerCase() || ''
        };
    });

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 1;
        
        rowData.forEach((data, index) => {
            const matchesSearch = searchTerm === '' ||
                data.roomName.includes(searchTerm) ||
                data.roomType.includes(searchTerm) ||
                data.roomStatus.includes(searchTerm) ||
                data.createdDate.includes(searchTerm);

            if (matchesSearch) {
                data.element.style.display = '';
                // Update the row number in first cell
                data.element.querySelector('td:first-child').textContent = visibleCount++;
            } else {
                data.element.style.display = 'none';
            }
        });
        
        // Handle no results
        const visibleRows = rowData.filter(data => data.element.style.display !== 'none');
        if (visibleRows.length === 0) {
            // Check if no results row already exists
            let noResultsRow = tableBody.querySelector('.no-results-row');
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = '<td colspan="6" class="text-center py-3">No classrooms found matching your search.</td>';
                tableBody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else {
            // Remove no results row if it exists
            const noResultsRow = tableBody.querySelector('.no-results-row');
            if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }
    }

    // Add event listener for search input
    searchInput.addEventListener('input', filterTable);
    
    // Add event listeners for edit and delete buttons (optional but good practice)
    document.querySelectorAll('.editClassroomsBtn').forEach(button => {
        button.addEventListener('click', function() {
            const classroomId = this.getAttribute('data-id');
            // Add your edit functionality here
            console.log('Edit classroom ID:', classroomId);
        });
    });

    document.querySelectorAll('.deleteClassroomBtn').forEach(button => {
        button.addEventListener('click', function() {
            const classroomId = this.getAttribute('data-id');
            // Add your delete functionality here
            console.log('Delete classroom ID:', classroomId);
        });
    });
});
</script>