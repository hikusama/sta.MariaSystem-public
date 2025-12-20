<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}
    $query = "SELECT classes.*, users.* FROM classes
    INNER JOIN users ON classes.adviser_id = users.user_id
    WHERE users.user_id = '$user_id'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch students for the adviser
    $stmt = $pdo->prepare("SELECT * FROM enrolment
        INNER JOIN student ON enrolment.student_id = student.student_id
        WHERE adviser_id = '$user_id'
        ORDER BY fname ASC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 1;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-users me-2"></i>Student Management</h4>
    </div>
</div>

<div class="row g-3">
    <!-- Search and Filter Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="searchInput" class="form-control" 
                    placeholder="Search by name, grade level, section, or status...">
            </div>
        </div>
    </div>

     <!-- Summary Statistics -->
    <!-- <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($users) ?></h3>
                                <small class="text-muted">Total Students</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <?php 
                            $activeCount = array_filter($users, fn($u) => $u['enrolment_status'] === 'active');
                            ?>
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-success mb-1"><?= count($activeCount) ?></h3>
                                <small class="text-muted">Active Students</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <?php 
                            $inactiveCount = array_filter($users, fn($u) => $u['enrolment_status'] !== 'active');
                            ?>
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 class="text-secondary mb-1"><?= count($inactiveCount) ?></h3>
                                <small class="text-muted">Inactive Students</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-info mb-1"><?= count(array_unique(array_column($users, 'section_name'))) ?></h3>
                                <small class="text-muted">Sections</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Students Table -->
    <div class="table-container-wrapper p-0">
        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="20%">Name</th>
                        <th width="15%">Grade Level</th>
                        <th width="15%">Section</th>
                        <th width="15%">Enrollment Status</th>
                        <th width="15%">Enrolled at</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="studentTableBody">
                    <?php foreach($users as $user) : ?>
                    <tr class="student-row" 
                        data-name="<?= htmlspecialchars(strtolower($user["lname"] . " " . $user["fname"])) ?>"
                        data-grade="<?= htmlspecialchars(strtolower($user["gradeLevel"])) ?>"
                        data-section="<?= htmlspecialchars(strtolower($user["section_name"])) ?>"
                        data-status="<?= $user["enrolment_status"] ?>">
                        <td width="5%"><?= $count++ ?></td>
                        <td width="20%" class="student-name">
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder me-2">
                                    <i class="fa-solid fa-user-circle text-secondary"></i>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($user["lname"] . ", " . $user["fname"]) ?></strong>
                                    <?php if(!empty($user["mname"])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($user["mname"]) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td width="15%">
                            <span class="badge bg-info"><?= htmlspecialchars($user["gradeLevel"]) ?></span>
                        </td>
                        <td width="15%">
                            <span class="badge bg-secondary"><?= htmlspecialchars($user["section_name"]) ?></span>
                        </td>
                        <td width="15%">
                            <span class="badge bg-<?= ($user["enrolment_status"] == 'active') ? 'success' : 'secondary' ?>">
                                <i class="fa-solid fa-circle fa-xs me-1"></i>
                                <?= ($user["enrolment_status"] == 'active') ? 'Enrolled' : 'Inactive' ?>
                            </span>
                        </td>
                        <td width="15%">
                            <small><?= date('M d, Y', strtotime($user["enrolled_date"])) ?></small>
                        </td>
                        <td width="15%">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="index.php?page=contents/profile&student_id=<?= $user["student_id"] ?>" 
                                   class="btn btn-sm btn-info" 
                                   title="View Profile">
                                    <i class="fa-solid fa-user me-1"></i> Profile
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No students found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>
        </div>
    </div>

   
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    const studentRows = document.querySelectorAll('.student-row');
    const studentTableBody = document.getElementById('studentTableBody');
    const noResultsDiv = document.getElementById('noResults');

    // Function to filter students
    function filterStudents() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        let visibleCount = 0;

        studentRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const grade = row.getAttribute('data-grade');
            const section = row.getAttribute('data-section');
            
            let matchesSearch = true;
            
            // Apply search filter
            if (searchTerm) {
                matchesSearch = name.includes(searchTerm) || 
                               grade.includes(searchTerm) || 
                               section.includes(searchTerm);
            }
            
            // Show/hide row based on search
            if (matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0) {
            studentTableBody.style.display = 'none';
            noResultsDiv.classList.remove('d-none');
        } else {
            studentTableBody.style.display = '';
            noResultsDiv.classList.add('d-none');
        }
        
        // Update row numbers
        updateRowNumbers();
    }
    
    // Function to update row numbers
    function updateRowNumbers() {
        let counter = 1;
        studentRows.forEach(row => {
            if (row.style.display !== 'none') {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell) {
                    firstCell.textContent = counter++;
                }
            }
        });
    }
    
    // Event listeners
    searchInput.addEventListener('input', filterStudents);
    
    // clearSearchBtn.addEventListener('click', function() {
    //     searchInput.value = '';
    //     filterStudents();
    //     searchInput.focus();
    // });
    
    // Add Enter key support for search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterStudents();
        }
    });
    
    // Add attendance button functionality
    document.querySelectorAll('.view-attendance').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            // You can implement attendance modal here
            console.log('View attendance for student:', studentId);
            // Example: window.location.href = `attendance.php?student_id=${studentId}`;
        });
    });
    
    // Initialize
    filterStudents();
    
    // Add some styling
    searchInput.addEventListener('focus', function() {
        this.parentElement.classList.add('border-primary', 'border-2');
    });
    
    searchInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('border-primary', 'border-2');
    });
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
</style>