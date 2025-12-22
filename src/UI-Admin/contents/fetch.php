<?php
require_once __DIR__ . '/../../../tupperware.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["action"])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
        exit();
    }
    $action = $_POST["action"];
    if ($action === "fetch_users") {

        $role = $_POST['role'] ?? '';
        $sy = $_POST['school_year'] ?? '';

        $query = "SELECT u.*, s.school_year_name 
          FROM users u 
          LEFT JOIN school_year s ON u.school_year_id = s.school_year_id
          WHERE 1";
        $params = [];

        if ($role) {
            $query .= " AND u.user_role = ?";
            $params[] = $role;
        }

        if ($sy) {
            $query .= " AND u.school_year_id = ?";
            $params[] = $sy;
        }
        $search = $_POST['search'] ?? '';
        if ($search) {
            $query .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.user_role LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }


        $query .= " ORDER BY u.created_date DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return table rows
        foreach ($users as $index => $user) {
            echo '<tr class="user-row"
            data-name="' . strtolower($user["firstname"] . ' ' . $user["lastname"]) . '"
            data-role="' . strtolower($user["user_role"]) . '"
            data-status="' . strtolower($user["status"]) . '"
            data-date="' . strtolower(date('M d, Y', strtotime($user["created_date"]))) . '"
            data-sy="' . strtolower($user["school_year_name"]) . '">
            <td>' . ($index + 1) . '</td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar-placeholder me-2">
                        <i class="fa-solid fa-user-circle text-secondary"></i>
                    </div>
                    <div>
                        <strong>' . htmlspecialchars($user["lastname"] . ', ' . $user["firstname"]) . '</strong>';
            if (!empty($user["middlename"])) {
                echo '<br><small class="text-muted">' . htmlspecialchars($user["middlename"]) . '</small>';
            }
            echo '</div>
                </div>
            </td>
            <td>
                <span class="badge bg-' .
                (($user["user_role"] == "TEACHER") ? "info" : (($user["user_role"] == "PARENT") ? "primary" : "secondary"))
                . '">' . htmlspecialchars($user["user_role"]) . '</span>
            </td>
            <td>
                <span class="badge bg-' . (($user["status"] == "Active") ? "success" : "secondary") . '">
                    <i class="fa-solid fa-circle fa-xs me-1"></i>
                    ' . htmlspecialchars($user["status"] ?? 'Inactive') . '
                </span>
            </td>
            <td><small>' . date('M d, Y', strtotime($user["created_date"])) . '</small></td>
            <td>
                <div class="d-flex gap-1 justify-content-center">
                    <a href="index.php?page=contents/usersProfile&user_id=' . $user["user_id"] . '" class="btn btn-sm btn-info" title="View Profile">
                        <i class="fa-solid fa-eye me-1"></i> View
                    </a>
                    <form class="status-form">
                        <select name="status" class="status-select form-select form-select-sm">
                            <option value="">Change Status</option>
                            <option value="Active"' . (($user["status"] === "Active") ? " selected" : "") . '>Active</option>
                            <option value="Inactive"' . (($user["status"] === "Inactive") ? " selected" : "") . '>Inactive</option>
                        </select>
                        <input type="hidden" name="user_id" value="' . $user['user_id'] . '">
                    </form>
                </div>
            </td>
          </tr>';
        }
    } elseif ($action === 'fetch_student') {
        $search = trim($_POST['search'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $grade  = trim($_POST['grade'] ?? '');
        $sy     = trim($_POST['school_year'] ?? '');

        $where = [];
        $params = [];

        // Only filter by school year if selected
        if ($sy) {
            $where[] = "users.school_year_id = ?";
            $params[] = $sy;
        }

        // Only filter by enrolment status if selected
        if ($status) {
            $where[] = "LOWER(student.enrolment_status) = ?";
            $params[] = strtolower($status);
        }

        // Only filter by grade if selected
        if ($grade) {
            $where[] = "LOWER(student.gradeLevel) = ?";
            $params[] = strtolower($grade);
        }

        // Search by name, LRN, or parent
        if ($search) {
            $where[] = "(student.fname LIKE ? OR student.lname LIKE ? OR student.lrn LIKE ? OR users.firstname LIKE ? OR users.lastname LIKE ?)";
            $s = "%$search%";
            array_push($params, $s, $s, $s, $s, $s);
        }

        // Build SQL
        $sql = "SELECT student.*, users.firstname AS parentFirstname, users.lastname AS parentLastname, users.middlename AS parentMiddle
            FROM student
            JOIN users ON users.user_id = student.guardian_id";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY student.lname ASC";

        // Execute
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate stats
        $stats = ['total' => count($rows), 'enrolled' => 0, 'transferred' => 0, 'inactive' => 0];
        $html = '';
        $i = 1;
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


        foreach ($rows as $r) {
            $enrolment = strtolower($r['enrolment_status'] ?? 'pending');
            if ($enrolment === 'active') $stats['enrolled']++;
            if (str_contains($enrolment, 'transferred')) $stats['transferred']++;
            if (in_array($enrolment, ['not_active', 'rejected'])) $stats['inactive']++;

            $badgeClass = $statusMap[$enrolment][0] ?? 'secondary';
            $label = $statusMap[$enrolment][1] ?? ucfirst($enrolment);

            $html .= "
<tr>
    <td>{$i}</td>
    <td>" . htmlspecialchars($r['lrn']) . "</td>
    <td>" . htmlspecialchars($r['lname'] . ', ' . $r['fname']) . "</td>
    <td>
        <strong>" . htmlspecialchars($r['parentLastname'] . ', ' . $r['parentFirstname']) . "</strong>";
            if (!empty($r['parentMiddle'])) {
                $html .= "<br><small>" . htmlspecialchars($r['parentMiddle']) . "</small>";
            }
            $html .= "
    </td>
    <td>" . htmlspecialchars($r['gradeLevel']) . "</td>
    <td>
        <span class='badge bg-{$badgeClass}'>
            <i class='fa-solid fa-circle fa-xs me-1'></i>
            {$label}
        </span>
    </td>
    <td>
        <div class='d-flex gap-1 justify-content-center'>
            <a class='btn btn-sm btn-info' href='index.php?page=contents/profile&student_id={$r['student_id']}'>
                Profile
            </a>
            <form class='status-enrolment-form'>
                <select name='status' class='status-enrolment-select form-select'>
                    <option value=''>Change Status</option>
                    <option value='active'" . ($enrolment === 'active' ? ' selected' : '') . ">Enrolled</option>
                    <option value='transferred_in'" . ($enrolment === 'transferred_in' ? ' selected' : '') . ">Transferred In</option>
                    <option value='transferred_out'" . ($enrolment === 'transferred_out' ? ' selected' : '') . ">Transferred Out</option>
                    <option value='not_active'" . ($enrolment === 'not_active' ? ' selected' : '') . ">Not Active</option>
                    <option value='dropped'" . ($enrolment === 'dropped' ? ' selected' : '') . ">Dropped</option>
                    <option value='rejected'" . ($enrolment === 'rejected' ? ' selected' : '') . ">Rejected</option>
                </select>
                <input type='hidden' name='user_id' value='{$r['student_id']}'>
            </form>
        </div>
    </td>
</tr>";
            $i++;
        }


        echo json_encode([
            'rows' => $html,
            'stats' => $stats
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
}
