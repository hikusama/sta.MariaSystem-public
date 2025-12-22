<?php
require_once __DIR__ . '/../../../tupperware.php';
$result = checkURI('admin', 2);

if ($result['res']) {
    header($result['uri']);
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-graduation-cap me-2"></i>Learners Management</h4>
    </div>
</div>

<div class="row g-3">
    <!-- Search and Filter Section -->
    <div class="row mb-3 justify-content-between align-items-center">
        <div class="col-md-12">
            <div class="input-group row">
                <div class="col-md-4">
                    <input type="text" id="searchInput" name="search" class="form-control"
                        placeholder="Search by name, LRN, or parent name...">
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" name="statusCategory" class="form-select" style="max-width: 200px;">
                        <option value="">All Status</option>
                        <option value="active">Enrolled</option>
                        <option value="transferred_in">Transferred in</option>
                        <option value="transferred_out">Transferred out</option>
                        <option value="not_active">Not Active</option>
                        <option value="rejected">Rejected</option>
                        <option value="dropped">Dropped</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="gradeFilter" name="gradeLevelCategory" class="form-select" style="max-width: 200px;">
                        <option value="">All Grades</option>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                        <option value="Grade 4">Grade 4</option>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="syFilter" class="form-select">
                        <option value="">All School Years</option>
                        <?php
                        $syStmt = $pdo->query("SELECT school_year_id, school_year_name FROM school_year ORDER BY school_year_name DESC");
                        while ($sy = $syStmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $sy['school_year_id'] ?>">
                                <?= htmlspecialchars($sy['school_year_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>


            </div>
        </div>
    </div>

    <!-- Learners Table -->
    <div class="table-container-wrapper p-0">
        <?php
        // --- Get current active school year ---
        $query = "SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1";
        $stmt1 = $pdo->prepare($query);
        $stmt1->execute();
        $schoolYear = $stmt1->fetch(PDO::FETCH_ASSOC);
        $activeSyId = $schoolYear['school_year_id'] ?? null;

        $users = [];
        if ($activeSyId) {
            $stmt = $pdo->prepare("
        SELECT student.*, 
               users.firstname AS parentFirstname, 
               users.lastname AS parentLastname, 
               users.middlename AS parentMiddle
        FROM student
        INNER JOIN users 
            ON users.user_id = student.guardian_id
        WHERE student.enrolment_status != 'pending' 
        ORDER BY student.fname ASC
    ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $count = 1;
        ?>


        <!-- Fixed Header -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="10%">LRN</th>
                        <th width="20%">Name</th>
                        <th width="20%">Parent/Guardian</th>
                        <th width="10%">Grade</th>
                        <th width="10%">Remarks</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="table-scroll-body">
            <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.875rem;">
                <tbody id="learnersTableBody">
                    <?php if (!empty($users)):
                        $count = 1;
                        $statusMap = [
                            'active'          => ['success', 'Enrolled'],
                            'pending'         => ['warning', 'Pending'],
                            'transferred_in'  => ['info', 'Transferred In'],
                            'transferred_out' => ['info', 'Transferred Out'],
                            'transferred'     => ['info', 'Transferred'],
                            'not_active'      => ['secondary', 'Not Active'],
                            'dropped'         => ['danger', 'Dropped'],
                            'rejected'        => ['danger', 'Rejected']
                        ];
                        foreach ($users as $user):
                            // Normalize status to lowercase
                            $currentStatus = strtolower($user['enrolment_status'] ?? 'pending');
                            $badgeClass = $statusMap[$currentStatus][0] ?? 'secondary';
                            $label = $statusMap[$currentStatus][1] ?? ucfirst($currentStatus);
                    ?>
                            <tr class="learner-row"
                                data-status="<?= htmlspecialchars($currentStatus) ?>"
                                data-grade="<?= htmlspecialchars(strtolower($user['gradeLevel'] ?? '')) ?>"
                                data-name="<?= htmlspecialchars(strtolower($user['lname'] . ' ' . $user['fname'] . ' ' . ($user['mname'] ?? ''))) ?>"
                                data-lrn="<?= htmlspecialchars(strtolower($user['lrn'] ?? '')) ?>"
                                data-parent="<?= htmlspecialchars(strtolower($user['parentLastname'] . ' ' . $user['parentFirstname'])) ?>">

                                <td width="5%"><?= $count++ ?></td>
                                <td width="10%"><code class="text-dark"><?= htmlspecialchars($user["lrn"]) ?></code></td>
                                <td width="20%" class="learner-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-user-graduate text-secondary"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($user["lname"] . ", " . $user["fname"]) ?></strong>
                                            <?php if (!empty($user["mname"])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($user["mname"]) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td width="20%">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder-small me-2">
                                            <i class="fa-solid fa-user text-secondary"></i>
                                        </div>
                                        <div>
                                            <small>
                                                <strong><?= htmlspecialchars($user["parentLastname"] . ", " . $user["parentFirstname"]) ?></strong>
                                                <?php if (!empty($user["parentMiddle"])): ?>
                                                    <br><?= htmlspecialchars($user["parentMiddle"]) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td width="10%">
                                    <span class="badge bg-info"><?= htmlspecialchars($user["gradeLevel"]) ?></span>
                                </td>
                                <td width="10%">
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                                        <?= $label ?>
                                    </span>
                                </td>
                                <td width="15%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="index.php?page=contents/profile&student_id=<?= htmlspecialchars($user["student_id"]) ?>"
                                            class="btn btn-sm btn-info" title="View Profile">
                                            <i class="fa-solid fa-user me-1"></i> Profile
                                        </a>
                                        <form class="status-enrolment-form">
                                            <select name="status" class="status-enrolment-select form-select">
                                                <option value="">Change Status</option>
                                                <?php foreach ($statusMap as $key => $map): ?>
                                                    <option value="<?= $key ?>" <?= ($currentStatus === $key) ? "selected" : "" ?>>
                                                        <?= $map[1] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="user_id" value="<?= $user['student_id'] ?>">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-3">No learners found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


        <!-- Empty State -->
        <div id="noResults" class="text-center py-5 d-none">
            <div class="empty-state">
                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3"></i>
                <h5>No learners found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Learners Statistics</h5>
                    <div class="row text-center">
                        <?php
                        $enrolledCount = array_filter($users, fn($u) => ($u['enrolment_status'] ?? '') == 'active');
                        $transferredCount = array_filter($users, fn($u) => ($u['enrolment_status'] ?? '') == 'transferred_in' ||
                            ($u['enrolment_status'] ?? '') == 'transferred_out');
                        $inactiveCount = array_filter($users, fn($u) => ($u['enrolment_status'] ?? '') == 'not_active');
                        $rejectedCount = array_filter($users, fn($u) => ($u['enrolment_status'] ?? '') == 'rejected');
                        ?>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?= count($users) ?></h3>
                                <small class="text-muted">Total Learners</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-success mb-1"><?= count($enrolledCount) ?></h3>
                                <small class="text-muted">Enrolled</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-info mb-1"><?= count($transferredCount) ?></h3>
                                <small class="text-muted">Transferred</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <h3 class="text-secondary mb-1"><?= count($inactiveCount) + count($rejectedCount) ?>
                                </h3>
                                <small class="text-muted">Inactive/Rejected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal (if needed) -->
<div class="modal fade" id="AddNewAccount" tabindex="-1" aria-labelledby="AddNewAccountLabel" aria-hidden="true">
    <!-- Your modal content remains the same -->
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const gradeFilter = document.getElementById('gradeFilter');
        const syFilter = document.getElementById('syFilter');
        const tableBody = document.getElementById('learnersTableBody');

        function fetchLearners() {
            const formData = new FormData();
            formData.append('action', 'fetch_student');
            formData.append('search', searchInput.value.trim());
            formData.append('status', statusFilter.value);
            formData.append('grade', gradeFilter.value);
            formData.append('school_year', syFilter.value);

            fetch('contents/fetch.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    tableBody.innerHTML = data.rows || '<tr><td colspan="7" class="text-center text-muted py-4">No records found</td></tr>';
                })
                .catch(err => {
                    console.error(err);
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Failed to load data</td></tr>';
                });
        }

        searchInput.addEventListener('input', fetchLearners);
        statusFilter.addEventListener('change', fetchLearners);
        gradeFilter.addEventListener('change', fetchLearners);
        syFilter.addEventListener('change', fetchLearners);
    });
</script>


<style>
    .table-scroll-body {
        max-height: 420px;
        overflow-y: auto;
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

    .avatar-placeholder-small {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
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

    .status-select-wrapper {
        min-width: 150px;
    }

    .status-enrolment-select {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        height: 32px;
    }

    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    code {
        font-size: 0.8rem;
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
</style>