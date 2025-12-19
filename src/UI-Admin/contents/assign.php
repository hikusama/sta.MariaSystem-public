<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
// Get current active school year
$currentSyStmt = $pdo->prepare("SELECT school_year_id, school_year_name FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
$currentSyStmt->execute();
$currentSy = $currentSyStmt->fetch(PDO::FETCH_ASSOC);
$activeSyId = $currentSy['school_year_id'] ?? null;


$classrooms = [];

if ($currentSy) {
    $stmt = $pdo->prepare("
        SELECT 
            c.room_id,
            c.room_name,
            c.room_type,
            c.room_status,

            u.user_id AS adviser_id,
            u.firstname AS adviser_firstname,
            u.lastname  AS adviser_lastname

        FROM classrooms c
        LEFT JOIN classes cl
            ON cl.classroom_id = c.room_id
            AND cl.sy_id = ?
        LEFT JOIN users u
            ON u.user_id = cl.adviser_id

        ORDER BY c.room_name ASC
    ");

    try {
        $stmt->execute([
            $currentSy['school_year_id'] // for classes.sy_id (the LEFT JOIN condition)
        ]);
        $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Assign page classrooms query failed: ' . $e->getMessage());
        // Fallback: fetch classrooms without filtering by sy_id in the JOIN
        $fallback = $pdo->prepare("SELECT 
            c.room_id, c.room_name, c.room_type, c.room_status,
            u.user_id AS adviser_id, u.firstname AS adviser_firstname, u.lastname AS adviser_lastname
            FROM classrooms c
            LEFT JOIN classes cl ON cl.classroom_id = c.room_id
            LEFT JOIN users u ON u.user_id = cl.adviser_id
            ORDER BY c.room_name ASC");
        $fallback->execute();
        $classrooms = $fallback->fetchAll(PDO::FETCH_ASSOC);
    }
}


// Fetch sections
$stmt = $pdo->prepare("SELECT * FROM sections ORDER BY section_grade_level ASC, section_name ASC");
$stmt->execute();
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group sections by grade level
$sectionsByGrade = [];
foreach ($sections as $section) {
    $sectionsByGrade[$section['section_grade_level']][] = $section;
}

// Fetch available teachers
$teachers = [];
if ($activeSyId) {
    $stmt = $pdo->prepare("
        SELECT u.* 
        FROM users u
        WHERE u.user_role = 'TEACHER'
        AND u.user_id NOT IN (
            SELECT adviser_id 
            FROM classes
            WHERE sy_id = ?
        )
        AND u.school_year_id = ?
        ORDER BY u.lastname ASC
    ");
    $stmt->execute([$activeSyId, $activeSyId]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Current school year info
$schoolYears = $currentSy ?? [];
?>


<style>
    .classroom-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .classroom-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #4e73df, #224abe);
    }

    .classroom-card.available::before {
        background: linear-gradient(90deg, #1cc88a, #13855c);
    }

    .classroom-card.unavailable::before {
        background: linear-gradient(90deg, #e74a3b, #be2617);
    }

    .classroom-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .classroom-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .classroom-icon.available {
        background: linear-gradient(135deg, #1cc88a, #13855c);
    }

    .classroom-icon.unavailable {
        background: linear-gradient(135deg, #e74a3b, #be2617);
    }

    .classroom-icon.occupied {
        background: linear-gradient(135deg, #f6c23e, #dda20a);
    }

    .classroom-info {
        padding: 15px;
    }

    .badge-status {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
    }

    .classroom-teacher {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .empty-classroom {
        padding: 3rem 1rem;
        text-align: center;
    }

    .empty-classroom i {
        opacity: 0.5;
    }

    .scroll-classes {
        height: 80vh;
        overflow-y: scroll;
        overflow-x: hidden;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-building-user me-2"></i>Class Management</h4>
    </div>
</div>

<div class="row g-3 scroll-classes">
    <!-- Search Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="searchInput" placeholder="Search classrooms by name, type, or teacher..." class="form-control">

            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="bg-light p-2 rounded d-inline-block">
                <i class="fa-solid fa-calendar-day text-primary me-1"></i>
                <strong>School Year:</strong> <?= htmlspecialchars($schoolYears["school_year_name"] ?? 'Not set') ?>
            </div>
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
                        $occupiedCount = array_filter($classrooms, fn($c) => !empty($c['adviser_id']));
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($classrooms) ?></h3>
                                <small class="text-white">Total Classrooms</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-dark mb-1"><?= count($availableCount) ?></h3>
                                <small class="text-white">Available</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <h3 class="text-dark mb-1"><?= count($occupiedCount) ?></h3>
                                <small class="text-dark">Occupied</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="text-dark mb-1"><?= count($unavailableCount) ?></h3>
                                <small class="text-white">Unavailable</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classrooms Grid -->
    <div id="classroomsGrid">
        <?php if ($classrooms): ?>
            <div class="row">
                <?php foreach ($classrooms as $classroom) :
                    $roomStatus = $classroom["room_status"];
                    $isAvailable = $roomStatus === 'Available';
                    $hasTeacher = !empty($classroom["adviser_id"]);
                    $cardClass = $isAvailable ? 'available' : 'unavailable';
                    $iconClass = $hasTeacher ? 'occupied' : ($isAvailable ? 'available' : 'unavailable');
                ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4 classroom-item"
                        data-name="<?= htmlspecialchars(strtolower($classroom["room_name"])) ?>"
                        data-type="<?= htmlspecialchars(strtolower($classroom["room_type"])) ?>"
                        data-status="<?= htmlspecialchars(strtolower($roomStatus)) ?>"
                        data-teacher="<?= htmlspecialchars(strtolower($classroom["adviser_firstname"] . ' ' . $classroom["adviser_lastname"])) ?>">
                        <div class="card border-0 shadow classroom-card <?= $cardClass ?>">
                            <div class="classroom-info">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="classroom-icon text-white <?= $iconClass ?> me-3">
                                        <i class="fa-solid fa-door-closed"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($classroom["room_name"]) ?></h5>
                                        <span class="badge-status bg-<?= $isAvailable ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars($roomStatus) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted">Type:</small>
                                    <div><strong><?= htmlspecialchars($classroom["room_type"]) ?></strong></div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted">Teacher Assigned:</small>
                                    <div class="classroom-teacher">
                                        <?php if ($hasTeacher): ?>
                                            <i class="fa-solid fa-user-tie me-1"></i>
                                            <strong><?= htmlspecialchars($classroom["adviser_firstname"] . " " . $classroom["adviser_lastname"]) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">No teacher assigned</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center mt-3">
                                    <?php if ($isAvailable && !$hasTeacher): ?>
                                        <button type="button"
                                            class="btn btn-danger btn-sm assign-teacher-btn"
                                            data-id="<?= $classroom["room_id"] ?>"
                                            title="Assign Teacher to this Classroom">
                                            <i class="fa-solid fa-user-plus me-1"></i> Assign Teacher
                                        </button>
                                    <?php elseif ($hasTeacher): ?>
                                        <span class="badge bg-dark">
                                            <i class="fa-solid fa-user-check me-1"></i> Occupied
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fa-solid fa-ban me-1"></i> Unavailable
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php else: ?>
            <div class="empty-classroom">
                <i class="fa-solid fa-school fa-3x text-muted mb-3"></i>
                <h5>No Classrooms Found</h5>
                <p class="text-muted">No classrooms have been added yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assgnTeacher" tabindex="-1" aria-labelledby="assgnTeacherLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="assgnTeacherLabel">
                    <i class="fa-solid fa-user-plus me-2"></i>Assign Teacher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3" id="assign-teacher-form" method="post">
                    <!-- Hidden Input for Classroom ID -->
                    <input type="hidden" name="classroom_id" id="classroomIdInput" value="">

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

                    <!-- Section Dropdown -->
                    <div class="my-2">
                        <label class="form-label">Section Name <span class="text-danger">*</span></label>
                        <select name="section_id" id="section_id" class="form-select" required disabled>
                            <option value="">Select Grade Level First</option>
                        </select>
                    </div>

                    <!-- Teacher Selection -->
                    <div class="my-2">
                        <label class="form-label">Teacher Name <span class="text-danger">*</span></label>
                        <select name="teacher_name" id="teacher_id" class="form-select" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher["user_id"] ?>">
                                    <?= htmlspecialchars($teacher["lastname"]) . ", " . htmlspecialchars($teacher["firstname"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($teachers)): ?>
                            <small class="text-danger">No available teachers for assignment</small>
                        <?php endif; ?>
                    </div>

                    <div class="my-2">
                        <label class="form-label">School Year</label>
                        <div class="form-control bg-light">
                            <?= htmlspecialchars($schoolYears["school_year_name"] ?? 'Not set') ?>
                        </div>
                        <input type="hidden" name="schoolYear_id" value="<?= $schoolYears["school_year_id"] ?? '' ?>">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="fa-solid fa-user-plus me-2"></i>Assign Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        const classroomItems = document.querySelectorAll('.classroom-item');
        const assignTeacherBtns = document.querySelectorAll('.assign-teacher-btn');
        const classroomIdInput = document.getElementById('classroomIdInput');
        const gradeSelect = document.getElementById('grade_level');
        const sectionSelect = document.getElementById('section_id');
        const clearSearchBtn = document.getElementById('clearSearch');

        // PHP data passed to JavaScript
        const sectionsByGrade = <?php echo json_encode($sectionsByGrade); ?>;

        // Search functionality
        function filterClassrooms() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            classroomItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const type = item.getAttribute('data-type');
                const status = item.getAttribute('data-status');
                const teacher = item.getAttribute('data-teacher');

                let matchesSearch = true;

                if (searchTerm) {
                    matchesSearch = name.includes(searchTerm) ||
                        type.includes(searchTerm) ||
                        status.includes(searchTerm) ||
                        teacher.includes(searchTerm);
                }

                if (matchesSearch) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show empty state if no results
            const classroomsGrid = document.getElementById('classroomsGrid');
            const emptyState = classroomsGrid.querySelector('.empty-classroom') || document.createElement('div');

            if (visibleCount === 0) {
                if (!classroomsGrid.querySelector('.empty-classroom')) {
                    emptyState.className = 'empty-classroom';
                    emptyState.innerHTML = `
                    <i class="fa-solid fa-search fa-3x text-muted mb-3"></i>
                    <h5>No Classrooms Found</h5>
                    <p class="text-muted">Try adjusting your search</p>
                `;
                    classroomsGrid.appendChild(emptyState);
                }
            } else if (classroomsGrid.querySelector('.empty-classroom')) {
                classroomsGrid.querySelector('.empty-classroom').remove();
            }
        }

        // Assign teacher button click handler
        assignTeacherBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const classroomId = this.getAttribute('data-id');
                classroomIdInput.value = classroomId;

                // Reset form
                gradeSelect.value = '';
                sectionSelect.innerHTML = '<option value="">Select Grade Level First</option>';
                sectionSelect.disabled = true;

                const modal = new bootstrap.Modal(document.getElementById('assgnTeacher'));
                modal.show();
            });
        });

        // Grade level change handler
        gradeSelect.addEventListener('change', function() {
            const selectedGrade = this.value;

            // Clear current sections
            sectionSelect.innerHTML = '<option value="">Select Section</option>';

            if (selectedGrade) {
                // Enable section dropdown
                sectionSelect.disabled = false;

                // Get sections for the selected grade
                const sections = sectionsByGrade[selectedGrade];

                if (sections && sections.length > 0) {
                    // Add sections to dropdown
                    sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.section_id;
                        option.textContent = section.section_name;
                        sectionSelect.appendChild(option);
                    });
                } else {
                    // No sections for this grade
                    sectionSelect.innerHTML = '<option value="">No sections available for this grade</option>';
                }
            } else {
                // No grade selected
                sectionSelect.disabled = true;
                sectionSelect.innerHTML = '<option value="">Select Grade Level First</option>';
            }
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