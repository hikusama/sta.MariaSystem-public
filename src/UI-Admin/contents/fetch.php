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
        $teachers = 0;
        $parents = 0;
        $activeUsers = 0;
        $html = '';
        // Return table rows
        foreach ($users as $index => $user) {
            if ($user["user_role"] === "TEACHER") {
                $teachers++;
            } elseif ($user["user_role"] === "PARENT") {
                $parents++;
            }
            if ($user["status"] === "Active") {
                $activeUsers++;
            }
            $html .= '<tr class="user-row"
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
                $html .= '<br><small class="text-muted">' . htmlspecialchars($user["middlename"]) . '</small>';
            }
            $html .= '</div>
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

        echo json_encode([
            'rows' => $html,
            'hasData' => !empty($users),
            'teachers' => $teachers,
            'parents' => $parents,
            'activeUsers' => $activeUsers,
            'totalUsers' => count($users)
        ]);
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
            'hasData' => !empty($classrooms),
            'stats' => $stats
        ]);
    } elseif ($action === 'fetch_enrollments') {
        $search = trim($_POST['search'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $grade  = trim($_POST['grade'] ?? '');
        $sy     = trim($_POST['school_year'] ?? '');

        $where = [];
        $params = [];

        // Only filter by school year if selected
        if ($sy) {
            $where[] = "u.school_year_id = ?";
            $params[] = $sy;
        }

        // Only filter by enrolment status if selected
        if ($status) {
            $where[] = "LOWER(s.enrolment_status) = ?";
            $params[] = strtolower($status);
        }

        // Only filter by grade if selected
        if ($grade) {
            $where[] = "LOWER(s.gradeLevel) = ?";
            $params[] = strtolower($grade);
        }

        // Search by name, LRN, or parent
        if ($search) {
            $where[] = "(s.fname LIKE ? OR s.lname LIKE ? OR s.lrn LIKE ? OR u.firstname LIKE ? OR u.lastname LIKE ?)";
            $s = "%$search%";
            array_push($params, $s, $s, $s, $s, $s);
        }

        // Build SQL
        $sql = "SELECT s.*, u.*
                FROM student s
                LEFT JOIN users u
                ON s.guardian_id = u.user_id
                ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY s.fname ASC";

        // Execute
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate stats
        // $stats = ['total' => count($users), 'enrolled' => 0, 'transferred' => 0, 'inactive' => 0];
        $html = '';

        $en = 0;
        $pn = 0;
        $rd = 0;



        foreach ($users as $user) {

            $status = $user["enrolment_status"] ?? '';
            $badgeClass = '';
            $statusText = '';

            if ($status == 'active') {
                $en += 1;
                $badgeClass = 'success';
                $statusText = 'Enrolled';
            } elseif ($status == 'rejected') {
                $rd += 1;
                $badgeClass = 'danger';
                $statusText = 'Rejected';
            } elseif ($status == 'transferred') {
                $badgeClass = 'info';
                $statusText = 'Transferred';
            } elseif ($status == 'dropped') {
                $rd += 1;
                $badgeClass = 'danger';
                $statusText = 'Dropped';
            } else {
                $pn += 1;
                $badgeClass = 'secondary';
                $statusText = 'Pending';
            }
            $html .= '
                            <tr class="student-row"
                                data-name="' . htmlspecialchars(strtolower($user["lname"] . " " . $user["fname"])) . '"
                                data-grade="' . htmlspecialchars(strtolower($user["gradeLevel"] ?? '')) . '"
                                data-status="' . htmlspecialchars(strtolower($status)) . '">
                                <td width="5%">' . $count++ . '</td>
                                <td width="20%" class="student-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-user-graduate text-secondary"></i>
                                        </div>
                                        <div>
                                            <strong>' . htmlspecialchars($user["lname"] . ", " . $user["fname"]) . '</strong>';

            if (!empty($user["mname"])) {

                $html .= '<br><small class="text-muted">' . htmlspecialchars($user["mname"]) . '</small>';
            }

            $html .= '</div>
                                    </div>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-info">' . htmlspecialchars($user["gradeLevel"] ?? 'Not set') . '</span>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-' . $badgeClass . '">
                                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                                        ' . $statusText . '
                                    </span>
                                </td>
                                <td width="20%">';

            if (!empty($user["enrolled_date"])) {
                $html .= '
                                        <small>' . date('M d, Y', strtotime($user["enrolled_date"])) . '</small>';
            } else {
                $html .= '
                                        <small class="text-muted">Not enrolled yet</small>';
            }
            $html .= '
                                     </td>
                                        <td width="25%">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="index.php?page=contents/form&student_id=' . htmlspecialchars($user["student_id"]) . '"
                                                    class="btn btn-sm btn-info" title="View Enrollment Form">
                                                    <i class="fa-solid fa-file-lines me-1"></i> Form
                                                </a>';
            if ($status != 'active' && $status != 'rejected') {
                $html .= '
                                            <button type="button" class="btn btn-success btn-sm open-enrolment"
                                                data-id="' . htmlspecialchars($user["student_id"]) . '"
                                                data-gradelevel="' . htmlspecialchars($user["gradeLevel"]) . '"
                                                title="Approve Enrollment">
                                                <i class="fa-solid fa-check me-1"></i> Approve
                                            </button>';
            }
            if ($status != 'rejected' && $status != 'active') {
                $html .= '
                                            <button type="button" class="btn btn-danger btn-sm open-rejection"
                                                data-id="' . htmlspecialchars($user["student_id"]) . '" title="Reject Enrollment">
                                                <i class="fa-solid fa-xmark me-1"></i> Reject
                                            </button>';
            }
            $html .= '</div>
                                            </td>
                                        </tr>';
            $i++;
        }


        echo json_encode([
            'rows' => $html,
            'hasData' => !empty($users),
            'ts' => count($users),
            'pn' => $pn,
            'en' => $en,
            'rd' => $rd,
        ]);
    } elseif ($action === 'fetch_classrooms') {

        $search = trim($_POST['search'] ?? '');
        $sy = trim($_POST['school_year'] ?? '');

        $where = [];
        $params = [];
        if ($sy) {
            $where[] = " classrooms.school_year_id = ?";
            $params[] = $sy;
        }
        if ($search) {
            $where[] = " classrooms.room_name LIKE ?";
            $s = "%$search%";
            $params[] = $s;
        }
        $sql = "SELECT * FROM classrooms LEFT JOIN school_year ON classrooms.school_year_id = school_year.school_year_id ";


        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY classrooms.created_date DESC;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $html = '';
        $availableCount = 0;
        $unavailableCount = 0;
        $count = 0;
        if ($classrooms) {
            foreach ($classrooms as $user) {
                $availableCount += ($user["room_status"] === 'Available') ? 1 : 0;
                $unavailableCount += ($user["room_status"] !== 'Available') ? 1 : 0;
                $count += 1;
                $html .= '
                <tr class="classroom-row" data-name="' . htmlspecialchars(strtolower($user["room_name"])) . '"
                                 data-type="' . htmlspecialchars(strtolower($user["room_type"])) . '"
                                 data-status="' . htmlspecialchars(strtolower($user["room_status"])) . '">
                                 <td width="1rem">' . $count . '</td>
                                 <td width="20%" class="classroom-name">
                                     <div class="d-flex align-items-center">
                                         <div class="avatar-placeholder me-2">
                                             <i class="fa-solid fa-door-closed text-secondary"></i>
                                         </div>
                                         <div>
                                             <strong>' . htmlspecialchars($user["room_name"]) . '</strong>
                                         </div>
                                     </div>
                                 </td>
                                 <td width="15%">
                                     <span class="badge bg-info">' . htmlspecialchars($user["room_type"]) . '</span>
                                 </td>
                                 <td width="15%">
                                        <span class="badge bg-' . (($user["room_status"] === 'Available') ? 'success' : 'secondary') . '">
                                         <i class="fa-solid fa-circle fa-xs me-1"></i>
                                         ' . htmlspecialchars($user["room_status"] ?? 'Unavailable') . '
                                     </span>
                                 </td>
                                 <td width="20%">
                                     <small>' . htmlspecialchars($user["school_year_name"]) . '</small>
                                 </td>
                                 <td width="20%">
                                     <small>' . date('M, d, y', strtotime($user["created_date"])) . '</small>
                                 </td>
                                 <td width="25%">
                                     <div class="d-flex gap-1 justify-content-center">
                                         <button type="button" data-id="' . htmlspecialchars($user['room_id']) . '"
                                             class="btn btn-sm btn-info editClassroomsBtn" title="Edit Classroom">
                                             <i class="fa-solid fa-pen me-1"></i> Edit
                                         </button>
                                         <button type="button" data-id="' . htmlspecialchars($user['room_id']) . '"
                                         class="btn btn-sm btn-danger deleteClassroomBtn" title="Delete Classroom">
                                             <i class="fa-solid fa-trash me-1"></i> Delete
                                         </button>
                                     </div>
                                     </td>
                             </tr>
            ';
            }
        } else {
            $html = '<tr>
                <td colspan="6" class="text-center py-3">No classrooms found.</td>
            </tr>';
        }
        echo json_encode([
            'rows' => $html,
            'hasData' => !empty($classrooms),
            'availableCount' => $availableCount,
            'unavailableCount' => $unavailableCount,
            'totalCount' => $count

        ]);
    } elseif ($action === 'fetch_sections') {

        $search = trim($_POST['search'] ?? '');
        $sy = trim($_POST['school_year'] ?? '');

        $where = [];
        $params = [];
        if ($sy) {
            $where[] = " sections.school_year_id = ?";
            $params[] = $sy;
        }
        if ($search) {
            $where[] = " sections.section_name LIKE ?";
            $s = "%$search%";
            $params[] = $s;
        }
        $sql = "SELECT * FROM sections LEFT JOIN school_year ON sections.school_year_id = school_year.school_year_id ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY sections.created_date DESC;";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $gradeTypeCount = 0;
        $availableCount = 0;
        $unavailableCount = 0;
        $count = 0;
        $grades = [];
        $html = '';

        if ($sections) {
            foreach ($sections as $section) {
                $availableCount += ($section["section_status"] === 'Available') ? 1 : 0;
                $unavailableCount += ($section["section_status"] !== 'Available') ? 1 : 0;
                if (in_array($section["section_grade_level"], $grades)) {
                    $gradeTypeCount += 1;
                } else {
                    $grades[] = $section["section_grade_level"];
                }
                $count += 1;
                $html .= '
                        <tr class="section-row"
                                data-name="' . htmlspecialchars(strtolower($section["section_name"])) . '"
                                data-grade="' . htmlspecialchars(strtolower($section["section_grade_level"])) . '"
                                data-status="' . htmlspecialchars(strtolower($section["section_status"])) . '">
                                <td style="white-space: wrap; width:5rem">' . $count . '</td>
                                <td style="white-space: wrap; max-width:9rem" class="section-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-layer-group text-secondary"></i>
                                        </div>
                                        <div>
                                            <strong>' . htmlspecialchars($section["section_name"]) . '</strong>
                                        </div>
                                    </div>
                                </td>
                                <td style="white-space: wrap; max-width:9rem">
                                    <span class="badge bg-info">' . htmlspecialchars($section["section_grade_level"]) . '</span>
                                </td>
                                <td style="white-space: wrap; max-width:9rem">
                                    <span class="badge bg-' . (($section["section_status"] == 'Available') ? 'success' : 'secondary') . '">
                                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                                        ' . htmlspecialchars($section["section_status"] ?? 'Unavailable') . '
                                    </span>
                                </td>
                                <td style="white-space: wrap; max-width:9rem">
                                    <small>' . $section["school_year_name"] . '</small>
                                </td>
                                <td style="white-space: wrap; max-width:9rem">
                                    <small>' . date('M d, Y', strtotime($section["created_date"])) . '</small>
                                </td>
                                <td style="white-space: wrap;"                                     <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" data-id="' . $section["section_id"] . '"
                                            class="btn btn-sm btn-info editSectionBtn"
                                            title="Edit Section">
                                            <i class="fa-solid fa-pen me-1"></i> Edit
                                        </button>
                                        <button type="button" data-id="' . $section["section_id"] . '"
                                            class="btn btn-sm btn-danger deleteSectionBtn"
                                            title="Delete Section">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                        </tr>
                ';
            }
        }
        echo json_encode([
            'rows' => $html,
            'hasData' => !empty($sections),
            'availableCount' => $availableCount,
            'unavailableCount' => $unavailableCount,
            'gradeTypeCount' => $gradeTypeCount,
            'totalCount' => $count

        ]);
    } elseif ($action === 'fetch_subjects') {

        $search = trim($_POST['search'] ?? '');
        $sy = trim($_POST['school_year'] ?? '');

        $where = [];
        $params = [];
        if ($sy) {
            $where[] = " subjects.school_year_id = ?";
            $params[] = $sy;
        }
        if ($search) {
            $where[] = " subjects.subject_name LIKE ?";
            $s = "%$search%";
            $params[] = $s;
        }
        $sql = "SELECT * FROM subjects LEFT JOIN school_year ON subjects.school_year_id = school_year.school_year_id ";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY subjects.created_date DESC;";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tuCount = 0;
        $availableCount = 0;
        $unavailableCount = 0;
        $count = 0;
        $gradesCC = [];
        $grades = [
            'Grade 1' => 0,
            'Grade 2' => 0,
            'Grade 3' => 0,
            'Grade 4' => 0,
            'Grade 5' => 0,
            'Grade 6' => 0,
        ];
        $html = '';

        if ($subjects) {
            foreach ($subjects as $subject) {
                $availableCount += ($subject["subjects_status"] === 'Available') ? 1 : 0;
                $unavailableCount += ($subject["subjects_status"] !== 'Available') ? 1 : 0;
                if (isset($grades[$subject["grade_level"]])) {
                    $grades[$subject["grade_level"]]++;
                }


                $tuCount += $subject["subject_units"];
                $count += 1;
                $html .= '
                        <tr class="subject-row"
                                data-name="' . htmlspecialchars(strtolower($subject["subject_name"])) . '"
                                data-code="' . htmlspecialchars(strtolower($subject["subject_code"])) . '"
                                data-grade="' . htmlspecialchars(strtolower($subject["grade_level"])) . '"
                                data-status="' . htmlspecialchars(strtolower($subject["subjects_status"])) . '">
                                <td width="5%">' . $count . '</td>
                                <td width="20%" class="subject-name">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2">
                                            <i class="fa-solid fa-book"></i>
                                        </div>
                                        <div>
                                            <strong>' . htmlspecialchars($subject["subject_name"]) . '</strong>
                                        </div>
                                    </div>
                                </td>
                                <td width="10%">
                                    <span class="badge bg-dark">' . htmlspecialchars($subject["subject_code"] ?? 'N/A') . '</span>
                                </td>
                                <td width="10%">
                                    <span class="badge bg-info">' . htmlspecialchars($subject["subject_units"]) . ' units</span>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-secondary">' . htmlspecialchars($subject["grade_level"]) . '</span>
                                </td>
                                <td width="15%">
                                    <span class="badge bg-' . (($subject["subjects_status"] == 'Available') ? 'success' : 'secondary') . '">
                                        <i class="fa-solid fa-circle fa-xs me-1"></i>
                                        ' . htmlspecialchars($subject["subjects_status"] ?? 'Unavailable') . '
                                    </span>
                                </td>
                                <td width="15%">
                                    <small>' . date('M d, Y', strtotime($subject["created_date"])) . '</small>
                                </td>
                                <td width="20%">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" data-id="' . $subject['subject_id'] . '"
                                            class="btn btn-sm btn-info editSubjectBtn" title="Edit Subject">
                                            <i class="fa-solid fa-pen me-1"></i> Edit
                                        </button>
                                        <button type="button" data-id="' . $subject['subject_id'] . '"
                                            class="btn btn-sm btn-danger deleteSubjectBtn" title="Delete Subject">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                ';
            }
        }
        echo json_encode([
            'rows' => $html,
            'hasData' => !empty($subjects),
            'availableCount' => $availableCount,
            'unavailableCount' => $unavailableCount,
            'tuCount' => $tuCount,
            'totalCount' => $count,
            'grades' => $grades,

        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ], 400);
    exit();
}
