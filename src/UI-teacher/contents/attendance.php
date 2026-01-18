<?php
require_once __DIR__ . '/../../../tupperware.php';

$result = checkURI('teacher', 2);
if ($result['res']) {
    header($result['uri']);
    exit;
}

$adviser_id = $_SESSION['user_id'];

if (isset($_POST['ajax'])) {

    $search = $_POST['search'] ?? '';
    $limit  = 25;
    $page   = max(1, (int)($_POST['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    $syStmt = $pdo->prepare("SELECT school_year_status,school_year_id FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
    $syStmt->execute();
    $syStatus = $syStmt->fetch(PDO::FETCH_ASSOC);
    $iddf = $syStatus['school_year_id'];
    $where = "WHERE c.adviser_id = ? AND c.sy_id = ?";
    $params = [$adviser_id, $iddf];

    if ($search) {
        $where .= " AND (st.fname LIKE ? OR st.lname LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $base = "
    FROM classes c
    INNER JOIN enrolment e ON e.section_name = c.section_name AND e.Grade_level = c.grade_level AND e.school_year_id = c.sy_id
    INNER JOIN student st ON st.student_id = e.student_id
    LEFT JOIN attendance a 
        ON a.student_id = st.student_id
        AND DATE(a.attendance_at) = CURDATE()
    $where
";

    // Count
    $stmt = $pdo->prepare("SELECT COUNT(*) $base");
    $stmt->execute($params);
    $totalRows = $stmt->fetchColumn();
    $totalPages = max(1, ceil($totalRows / $limit));

    // Data
    $stmt = $pdo->prepare("
    SELECT 
        e.*,
        st.*,
        a.morning_attendance,
        a.attendance_at,
        a.afternoon_attendance,
        a.attendance_type AS morning_type,
        a.A_attendance_type AS afternoon_type,
        a.attendance_summary
    $base
    ORDER BY st.fname ASC
    LIMIT $limit OFFSET $offset
");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    $count = $offset + 1;

    function attendanceIcon($type)
    {
        return match ($type) {
            'Present' => '<span class="text-success">✔</span>',
            'Absent'  => '<span class="text-danger">✖</span>',
            'Late'    => '<span class="text-warning">🕒</span>',
            default   => '<span class="text-secondary">----</span>',
        };
    }

    foreach ($rows as $user):
        $morningRecorded = !empty($user['morning_type']);
        $afternoonRecorded = !empty($user['afternoon_type']);
        $recordedSum = !empty($user['attendance_summary']);
        $allRecorded = $morningRecorded && $afternoonRecorded;
        $allRecordedSum = $recordedSum;
?>
        <tr>
            <td><?= $count++ ?></td>
            <td>
                <strong><?= htmlspecialchars($user["lname"] . ", " . $user["fname"]) ?></strong><br>
                <small><?= htmlspecialchars($user["mname"] ?? '') ?></small>
            </td>
            <td style="text-align: center;"><span class="badge bg-info"><?= $user["Grade_level"] ?></span></td>
            <td style="text-align: center;"><span class="badge bg-secondary"><?= $user["section_name"] ?></span></td>
            <td class="text-center">
                <?= attendanceIcon($user['morning_type'] ?? null) ?>
            </td>
            <td class="text-center">
                <?= attendanceIcon($user['afternoon_type'] ?? null) ?>
            </td>
            <td style="text-align: center;"><?= $user["attendance_at"] ? date('M d, Y', strtotime($user["attendance_at"])) : '----' ?></td>
            <?php
            $today = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $isSunday = $today->format('w') == 0;
            if ($isSunday):
            ?>
                <td class="text-center">
                    <span class="text-black" style="height: 100%;">Closed (Sunday)</span>
                </td>
            <?php else: ?>
                <td class="text-center d-flex gap-1 justify-content-center flex-wrap items-center">
                    <?php if ($allRecordedSum): ?>
                        <span class="text-success">✔</span>
                        <div class="d-flex gap-1 justify-content-center">
                        <?php elseif ($allRecorded): ?>
                            <form title="Confirm" class="attendance-form" data-type="confirm">
                                <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                <button type="submit" class="btn btn-sm btn-success">✔ Confirm</button>
                            </form>
                            <form title="Cancel" class="attendance-form" data-type="cancel">
                                <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">✖ Cancel</button>
                            </form>
                        <?php else: ?>
                            <form title="Present" class="attendance-form" data-type="P">
                                <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                <button type="submit" class="btn btn-sm btn-success">✔</button>
                            </form>
                            <form title="Absent" class="attendance-form" data-type="A">
                                <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">✖</button>
                            </form>
                            <form title="Late" class="attendance-form" data-type="L">
                                <input type="hidden" name="student_id" value="<?= $user["student_id"] ?>">
                                <button type="submit" class="btn btn-sm btn-warning">🕒</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </td>
            <?php endif; ?>
        </tr>
    <?php
    endforeach;

    ?>

    <tr>
        <td colspan="7">
            <div class="d-flex justify-content-between">
                <span>Page <?= $page ?> / <?= $totalPages ?></span>
                <div>
                    <?php if ($page > 1): ?>
                        <button class="btn btn-sm btn-secondary" onclick="fetchStudents(<?= $page - 1 ?>)">Prev</button>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <button class="btn btn-sm btn-secondary" onclick="fetchStudents(<?= $page + 1 ?>)">Next</button>
                    <?php endif; ?>
                </div>
            </div>
        </td>
    </tr>

<?php
    echo json_encode([
        'html' => ob_get_clean(),
        'hasData' => count($rows) > 0
    ]);
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mx-2">
        <h4><i class="fa-solid fa-clipboard-check me-2"></i>Student Attendance</h4>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Search student...">
    </div>

    <div class="col-md-3">
        <select id="sessionFilter" class="form-select">
            <option value="">All Sessions</option>
            <option value="Morning">Morning</option>
            <option value="Afternoon">Afternoon</option>
        </select>
    </div>

    <div class="col-md-3">
        <input type="time" id="timeInput" class="form-control">
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Mo.ning</th>
                <th>Af.noon</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="studentTableBody"></tbody>
    </table>
</div>

<div id="noResults" class="text-center text-muted d-none">No students found</div>

<script>
    let currentPage = 1;

    const timeInput = document.getElementById('timeInput');
    const sessionSelect = document.getElementById('sessionFilter');

    function applyTimeRules() {
        const session = sessionSelect.value;

        if (session === 'Morning') {
            timeInput.readOnly = false;
            timeInput.min = "06:00";
            timeInput.max = "11:59";
            if (!timeInput.value || timeInput.value < timeInput.min || timeInput.value > timeInput.max) {
                timeInput.value = "07:00";
            }
        } else if (session === 'Afternoon') {
            timeInput.readOnly = false;
            timeInput.min = "13:00";
            timeInput.max = "17:00";
            if (!timeInput.value || timeInput.value < timeInput.min || timeInput.value > timeInput.max) {
                timeInput.value = "13:00";
            }
        } else {
            timeInput.readOnly = true; // make readonly if session not selected
            timeInput.value = '';
            timeInput.removeAttribute('min');
            timeInput.removeAttribute('max');
        }
    }

    function fetchStudents(page = 1) {
        const fd = new FormData();
        fd.append('ajax', 1);
        fd.append('search', document.getElementById('searchInput').value);
        fd.append('session', sessionSelect.value);
        fd.append('page', page);

        const tbody = document.getElementById('studentTableBody');
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">Loading...</td></tr>`;

        fetch('contents/attendance.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                tbody.innerHTML = data.html;
                document.getElementById('noResults').classList.toggle('d-none', data.hasData);
                currentPage = page;
            });
    }

    // Event listeners
    document.getElementById('searchInput').addEventListener('input', () => fetchStudents(1));
    sessionSelect.addEventListener('change', () => {
        applyTimeRules();
    });

    // Validate time on change
    // timeInput.addEventListener('change', () => {
    //     const session = sessionSelect.value;
    //     const val = timeInput.value;
    //     if (!val) return;

    //     const [h, m] = val.split(':').map(Number);
    //     const totalMinutes = h * 60 + m;

    //     let isValid = false;
    //     if (session === 'Morning') {
    //         isValid = totalMinutes >= 6 * 60 && totalMinutes <= 11 * 60 + 59;
    //     } else if (session === 'Afternoon') {
    //         isValid = totalMinutes >= 13 * 60 && totalMinutes <= 17 * 60;
    //     } else {
    //         isValid = false; // no session selected
    //     }

    //     if (!isValid) {
    //         Swal.fire("Invalid Time", "Please select a valid time for this session.", "warning");
    //         timeInput.value = '';
    //     }
    // });

    // Initial setup
    document.addEventListener('DOMContentLoaded', () => {
        applyTimeRules();
        fetchStudents();
    });
</script>


<style>
    .timerr {
        margin-top: .5rem;
    }

    .timerr input {
        padding-left: 1.5rem !important;
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
        min-width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
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

    .btn-sm:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>