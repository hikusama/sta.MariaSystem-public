<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../assets/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Action
{
    public $nao = 'now';
    // public $nao = '2026-01-11 13:00:00';
    private $db;
    public function __construct()
    {

        include 'config.php';

        if (!isset($pdo)) {
            die("Database not connected.");
        }
        $this->db = $pdo;
    }
    function __destruct()
    {
        $this->db = null;
    }
    //SYSTEM INITIALIZATION
    function save_installation_data()
    {
        $firstname = htmlspecialchars($_POST['firstname'] ?? '');
        $middlename = htmlspecialchars($_POST['middlename'] ?? '');
        $lastname = htmlspecialchars($_POST['lastname'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $username = htmlspecialchars($_POST['username'] ?? '');
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
        $system_title = htmlspecialchars($_POST['system_title'] ?? '');
        $system_description = htmlspecialchars($_POST['system_description'] ?? '');

        if (!isset($_FILES['system_logo']) || $_FILES['system_logo']['error'] !== 0) {
            return json_encode(['status' => 2, 'message' => 'Logo file is required.']);
        }

        $logo = $_FILES['system_logo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

        if ($logo['size'] > 2 * 1024 * 1024) {
            return json_encode(['status' => 2, 'message' => 'Logo file size exceeds 2MB.']);
        }

        if (!in_array($logo['type'], $allowed_types)) {
            return json_encode(['status' => 2, 'message' => 'Invalid logo file type.']);
        }

        $upload_dir = '../assets/image/system_logo/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $logo_name = uniqid('logo_') . '_' . basename($logo['name']);
        $upload_path = $upload_dir . $logo_name;
        if (!move_uploaded_file($logo['tmp_name'], $upload_path)) {
            return json_encode(['status' => 2, 'message' => 'Failed to upload logo file.']);
        }

        try {
            $stmt1 = $this->db->prepare("INSERT INTO admin (firstname, middlename, lastname, email, username, password, user_role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $adminInsert = $stmt1->execute([$firstname, $middlename, $lastname, $email, $username, $password, 'administrator']);

            if ($adminInsert) {
                $stmt2 = $this->db->prepare("INSERT INTO system (system_title, system_description, system_logo) VALUES (?, ?, ?)");
                $systemInsert = $stmt2->execute([$system_title, $system_description, $logo_name]);

                if ($systemInsert) {
                    return json_encode(['status' => 1, 'message' => 'Installation data saved successfully.']);
                } else {
                    return json_encode(['status' => 2, 'message' => 'Failed to save system data.']);
                }
            } else {
                return json_encode(['status' => 2, 'message' => 'Failed to save admin data.']);
            }
        } catch (Exception $e) {

            return json_encode(['status' => 2, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    function Account_form()
    {

        $lastName = htmlspecialchars(trim($_POST["lastName"]));
        $firstName = htmlspecialchars(trim($_POST["firstName"]));
        $middleName = htmlspecialchars(trim($_POST["middleName"] ?? ''));
        $suffix = htmlspecialchars(trim($_POST["suffix"] ?? ''));
        $user_role = htmlspecialchars(trim($_POST["user_role"]));
        $gender = htmlspecialchars(trim($_POST["gender"]));
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $contact = htmlspecialchars(trim($_POST["contact"]));
        $username = htmlspecialchars(trim($_POST["username"]));
        $password = $_POST["password"];
        $cpassword = $_POST["cpassword"];


        // ==================== INPUT VALIDATION ====================== //
        if (empty($user_role)) {
            return json_encode([
                'status' => 0,
                'message' => 'User role is required',
                'field' => 'user_role',
                'code' => 'failed1'
            ]);
        }

        if (empty($gender)) {
            return json_encode([
                'status' => 0,
                'message' => 'Gender is required',
                'field' => 'gender',
                'code' => 'failed2'
            ]);
        }

        if (empty($firstName)) {
            return json_encode([
                'status' => 0,
                'message' => 'First name is required',
                'field' => 'firstName',
                'code' => 'failed3'
            ]);
        }

        if (empty($lastName)) {
            return json_encode([
                'status' => 0,
                'message' => 'Last name is required',
                'field' => 'lastName',
                'code' => 'failed4'
            ]);
        }

        if (empty($username) || strlen($username) < 3) {
            return json_encode([
                'status' => 0,
                'message' => 'Username must be at least 3 characters',
                'field' => 'username',
                'code' => 'failed5'
            ]);
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json_encode([
                'status' => 0,
                'message' => 'Invalid email address',
                'field' => 'email',
                'code' => 'failed6'
            ]);
        }

        if (empty($password) || strlen($password) < 6) {
            return json_encode([
                'status' => 0,
                'message' => 'Password must be at least 6 characters',
                'field' => 'password',
                'code' => 'failed7'
            ]);
        }

        if (empty($cpassword)) {
            return json_encode([
                'status' => 0,
                'message' => 'Confirm password is required',
                'field' => 'cpassword',
                'code' => 'failed8'
            ]);
        }

        if (empty($contact)) {
            return json_encode([
                'status' => 0,
                'message' => 'Contact number is required',
                'field' => 'contact',
                'code' => 'failed10'
            ]);
        }



        // Validation code remains the same...

        try {

            // Validate password match first
            if ($cpassword != $password) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Mismatch password'
                ]);
            }

            // Check if username exists in both users and admin tables
            $stmt = $this->db->prepare("
                SELECT username FROM users WHERE username = ? 
                UNION 
                SELECT admin_username as username FROM admin WHERE admin_username = ?
            ");
            $stmt->execute([$username, $username]);
            $usernameTaken = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usernameTaken) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Username ' . $usernameTaken["username"] . ' already taken please try another username'
                ]);
            }

            // Check if email exists in both users and admin tables
            $stmt = $this->db->prepare("
                SELECT email FROM users WHERE email = ? 
                UNION 
                SELECT admin_email as email FROM admin WHERE admin_email = ?
            ");
            $stmt->execute([$email, $email]);
            $emailTaken = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($emailTaken) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Email address already registered'
                ]);
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // FIXED: Use $this->db instead of $pdo
            $query = "INSERT INTO users (firstname, middlename, lastname, suffix, email, contact, gender, username, password, user_role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";

            $stmt = $this->db->prepare($query); // CHANGED: $pdo to $this->db
            $stmt->execute([
                $firstName,
                $middleName,
                $lastName,
                $suffix,
                $email,
                $contact,
                $gender,
                $username,
                $hashedPassword,
                $user_role
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Account created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }

    function getActiveSY()
    {
        $stmt = $this->db->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function classroom_form()
    {
        $classroom_name = htmlspecialchars(trim($_POST["classroom_name"]));
        $classroom_type = htmlspecialchars(trim($_POST["classroom_type"]));

        // Validate inputs
        if (empty($classroom_name) || empty($classroom_type)) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields'
            ]);
        }

        try {
            // Check if classroom already exists
            $checkStmt = $this->db->prepare("SELECT room_name FROM classrooms WHERE room_name = ? ");
            $checkStmt->execute([$classroom_name]);
            $existingClassroom = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingClassroom) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Classroom "' . $classroom_name . '" already exists!'
                ]);
            }

            $query = "INSERT INTO classrooms (room_name, room_type) 
                    VALUES (?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$classroom_name, $classroom_type]);

            return json_encode([
                'status' => 1,
                'message' => 'Classroom created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function section_form()
    {
        $section_name = htmlspecialchars(trim($_POST["section_name"]));
        $grade_level = htmlspecialchars(trim($_POST["grade_level"]));
        $section_status = htmlspecialchars(trim($_POST["section_status"]));


        if (empty($section_name) || empty($grade_level) || empty($section_status)) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields'
            ]);
        }

        try {
            // Check if classroom already exists
            $checkStmt = $this->db->prepare("SELECT section_name FROM sections WHERE section_name = ?");
            $checkStmt->execute([$section_name]);
            $existingSection = $checkStmt->fetch(PDO::FETCH_ASSOC);


            if ($existingSection) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Section "' . $section_name . '" already exists!'
                ]);
            }

            $query = "INSERT INTO sections (section_name, section_grade_level, section_status) 
                    VALUES (?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$section_name, $grade_level, $section_status]);

            return json_encode([
                'status' => 1,
                'message' => 'Sectoin created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'msg' => $e->getMessage(),
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    public function gaccess()
    {
        header('Content-Type: application/json');
        $student_id = $_POST['studentId'] ?? null;

        if (!$student_id) {
            echo json_encode(['status' => 0, 'message' => 'No student ID provided.']);
            return;
        }

        try {
            // Check if already enrolled in active school year
            $stmt = $this->db->prepare("
            SELECT 1 FROM enrolment
            INNER JOIN school_year 
            ON enrolment.school_year_id = school_year.school_year_id
            WHERE enrolment.student_id = ? 
            AND school_year.school_year_status = 'Active'
            LIMIT 1
        ");
            $stmt->execute([$student_id]);

            if ($stmt->fetchColumn()) {
                echo json_encode(['status' => 0, 'message' => 'Student is already enrolled for the active school year.']);
                return;
            }

            // Update student
            $stmt = $this->db->prepare("
            UPDATE student 
            SET enrolment_status = 'pending', isMovingUp = 1 
            WHERE student_id = ?
        ");
            $stmt->execute([$student_id]);

            echo json_encode(['status' => 1, 'message' => 'Enrollment access granted successfully.']);
        } catch (PDOException $e) {
            error_log("Database error in gaccess: " . $e->getMessage());
            echo json_encode(['status' => 0, 'message' => 'An internal error occurred.']);
        }
    }

    function schoolYear_form()
    {
        $status = htmlspecialchars(trim($_POST["status"]));
        $currentYear = (new DateTime($this->nao))->format('Y');
        // $schoolYear_name = htmlspecialchars(trim($_POST["schoolYear_name"]));
        $nextYear = $currentYear + 1;

        $schoolYear_name = $currentYear . '-' . $nextYear;
        // Validate inputs
        if (empty($status)) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required field'
            ]);
        }

        try {
            // Check if classroom already exists
            $checkStmt = $this->db->prepare("SELECT school_year_name FROM school_year WHERE school_year_name = ?");
            $checkStmt->execute([$schoolYear_name]);
            $existingClassroom = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingClassroom) {
                return json_encode([
                    'status' => 0,
                    'message' => 'School Year: "' . $schoolYear_name . '" already exists!'
                ]);
            }
            if ($status === 'Active') {
                $stmt = $this->db->prepare("UPDATE school_year SET school_year_status = 'Inactive'");
                $stmt->execute();
            }

            $query = "INSERT INTO school_year (school_year_status, school_year_name) 
                    VALUES (?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$status, $schoolYear_name]);
            $lastId = $this->db->lastInsertId();

            if ($status === 'Active') {
                $_SESSION['active_sy_id'] = $lastId;
            }

            return json_encode([
                'status' => 1,
                'message' => 'School Year created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function subjects_form()
    {
        $subject_name = htmlspecialchars(trim($_POST["subject_name"]));
        $subject_code = htmlspecialchars(trim($_POST["subject_code"]));
        $grade_level = htmlspecialchars(trim($_POST["grade_level"]));
        $subject_units = htmlspecialchars(trim($_POST["subject_units"]));
        $subjects_status = htmlspecialchars(trim($_POST["subjects_status"]));

        if (empty($subject_name) || empty($subject_code) || empty($grade_level) || empty($subject_units) || empty($subjects_status)) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields'
            ]);
        }

        try {
            $checkStmt = $this->db->prepare(
                "SELECT subject_name FROM subjects WHERE subject_name = ? OR subject_code = ?"
            );
            $checkStmt->execute([$subject_name, $subject_code]);
            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Subject already exists!'
                ]);
            }

            $query = "INSERT INTO subjects (subject_name, subject_code, grade_level, subject_units, subjects_status)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$subject_name, $subject_code, $grade_level, $subject_units, $subjects_status]);

            return json_encode([
                'status' => 1,
                'message' => 'Subject created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function assignTeacher_form()
    {
        $classroom_id = htmlspecialchars(trim($_POST["classroom_id"] ?? ""));
        $section_id   = htmlspecialchars(trim($_POST["section_id"] ?? ""));
        $grade_level  = htmlspecialchars(trim($_POST["grade_level"] ?? ""));
        $teacher_id   = htmlspecialchars(trim($_POST["teacher_name"] ?? "")); // adviser_id
        $schoolYear_id = htmlspecialchars(trim($_POST["schoolYear_id"] ?? ""));

        if (
            empty($classroom_id) || empty($section_id) || empty($grade_level)
            || empty($teacher_id) || empty($schoolYear_id)
        ) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields'
            ]);
        }

        try {
            // ✅ Check if teacher already assigned for this school year
            $checkStmt = $this->db->prepare("
                SELECT u.firstname, u.lastname 
                FROM classes c
                INNER JOIN users u ON c.adviser_id = u.user_id
                WHERE c.adviser_id = ? AND c.sy_id = ?
            ");
            $checkStmt->execute([$teacher_id, $schoolYear_id]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $teacherFullName = $existing['firstname'] . ' ' . $existing['lastname'];
                return json_encode([
                    'status' => 0,
                    'message' => "Teacher {$teacherFullName} is already assigned as adviser."
                ]);
            }

            // ✅ Check if section exists
            $secStmt = $this->db->prepare("SELECT section_name FROM sections WHERE section_id = ?");
            $secStmt->execute([$section_id]);
            $section = $secStmt->fetch(PDO::FETCH_ASSOC);

            if (!$section) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid section selected.'
                ]);
            }

            $section_name = $section['section_name'];

            // ✅ Insert new class record
            $query = "INSERT INTO classes (section_id, adviser_id, sy_id, classroom_id, section_name, grade_level) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$section_id, $teacher_id, $schoolYear_id, $classroom_id, $section_name, $grade_level]);

            // ✅ Update classroom availability
            $roomStmt = $this->db->prepare("UPDATE classrooms SET room_status = 'Unavailable' WHERE room_id = ?");
            $roomStmt->execute([$classroom_id]);

            return json_encode([
                'status' => 1,
                'message' => 'Teacher assigned successfully! Classroom marked as Unavailable.'
            ]);
        } catch (PDOException $e) {
            error_log("Database error in assignTeacher_form: " . $e->getMessage());

            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage() // temporary for debugging
            ]);
        }
    }

    function studentAcc_form()
    {
        $lrn = htmlspecialchars(trim($_POST["lrn"] ?? ''));
        $gradeLevel = htmlspecialchars(trim($_POST["grade_level"] ?? ''));
        $sex = htmlspecialchars(trim($_POST["sex"] ?? ''));
        $lastName = htmlspecialchars(trim($_POST["lastName"] ?? ''));
        $firstName = htmlspecialchars(trim($_POST["firstName"] ?? ''));
        $middleName = htmlspecialchars(trim($_POST["middleName"] ?? ''));
        $suffix = htmlspecialchars(trim($_POST["suffix"] ?? ''));
        $religion = htmlspecialchars(trim($_POST["religion"] ?? ''));
        $birthdate = $_POST["birthdate"] ?? null;
        $birthplace = $_POST["birthplace"] ?? null;


        if (empty($lrn)) {
            return json_encode([
                'status' => 0,
                'message' => 'LRN is required',
                'field' => 'lrn'
            ]);
        }

        if (!preg_match('/^[0-9]{12}$/', $lrn)) {
            return json_encode([
                'status' => 0,
                'message' => 'LRN must be 12 digits',
                'field' => 'lrn'
            ]);
        }

        if (empty($gradeLevel)) {
            return json_encode([
                'status' => 0,
                'message' => 'Grade level is required',
                'field' => 'grade_level'
            ]);
        }

        if (empty($sex)) {
            return json_encode([
                'status' => 0,
                'message' => 'Sex is required',
                'field' => 'sex'
            ]);
        }

        if (empty($lastName)) {
            return json_encode([
                'status' => 0,
                'message' => 'Last name is required',
                'field' => 'lastName'
            ]);
        }

        if (empty($firstName)) {
            return json_encode([
                'status' => 0,
                'message' => 'First name is required',
                'field' => 'firstName'
            ]);
        }

        if (empty($birthdate)) {
            return json_encode([
                'status' => 0,
                'message' => 'Birthdate is required',
                'field' => 'birthdate'
            ]);
        }

        if (empty($birthplace)) {
            return json_encode([
                'status' => 0,
                'message' => 'Birthplace is required',
                'field' => 'birthplace'
            ]);
        }


        try {
            // Check if LRN exists
            $checkStmt = $this->db->prepare("SELECT lrn FROM student WHERE lrn = ?");
            $checkStmt->execute([$lrn]);
            $lrnTaken = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($lrnTaken) {
                return json_encode([
                    'status' => 0,
                    'message' => 'LRN: "' . $lrn . '" already exists!'
                ]);
            }

            // == FILE UPLOAD
            $student_profile = '';

            if (isset($_FILES['student_profile']) && $_FILES['student_profile']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/image/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileTmp = $_FILES['student_profile']['tmp_name'];
                $fileName = basename($_FILES['student_profile']['name']);
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // validate allowed file types
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($fileExt, $allowed)) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Invalid image file type.'
                    ]);
                }

                // create unique filename
                $newFileName = uniqid('profile_', true) . '.' . $fileExt;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $destPath)) {
                    $student_profile = $destPath;
                } else {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Failed to upload profile image.'
                    ]);
                }
            }


            // ==== INSERT ====
            $query = "INSERT INTO student 
                (guardian_id, lrn, fname, mname, lname, suffix, sex, birthdate, birthplace, religion, gradeLevel, student_profile) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $_SESSION['user_id'],
                $lrn,
                $firstName,
                $middleName,
                $lastName,
                $suffix,
                $sex,
                $birthdate,
                $birthplace,
                $religion,
                $gradeLevel,
                $student_profile
            ]);
            $student_id = $this->db->lastInsertId();

            $query = "INSERT INTO stuenrolmentinfo (student_id) VALUES ('$student_id')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $stmtParentInfo = $this->db->prepare("INSERT INTO parents_info (student_id) VALUES ('$student_id')");
            $stmtParentInfo->execute();

            return json_encode([
                'status' => 1,
                'message' => 'Account created successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'dump' => $e->getMessage(),
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function feedback_form()
    {
        $parent_id = $_POST["parent_id"] ?? '';
        $title = htmlspecialchars(trim($_POST["title"] ?? ''));
        $description = htmlspecialchars(trim($_POST["description"] ?? ''));

        try {
            $query = "INSERT INTO feeback (parent_id, title, description)
                VALUES ('$parent_id', '$title', '$description')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return json_encode([
                'status' => 1,
                'message' => 'Feedback submited successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function enrolment_form()
    {
        $section_name   = $_POST['section_name'] ?? null;
        $adviser_id     = $_POST['adviser_id'] ?? null;
        $schoolyear_id  = $_POST['schoolyear_id'] ?? null;
        $grade_level    = $_POST['grade_level'] ?? null;
        $subjects       = $_POST['subjects'] ?? [];
        $student_id     = $_POST['student_id'] ?? null;

        try {
            // Validate required fields
            if (!$adviser_id || !$schoolyear_id || !$grade_level || empty($subjects) || !$student_id || !$section_name) {
                return json_encode(['status' => 0, 'message' => 'All fields are required.']);
            }

            // Check if student already has an enrolment for this school year
            $stmt = $this->db->prepare("
            SELECT enrolment_id 
            FROM enrolment 
            WHERE student_id = ? AND school_year_id = ?
        ");
            $stmt->execute([$student_id, $schoolyear_id]);
            $existing_enrolment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_enrolment) {
                return json_encode(['status' => 0, 'message' => 'Student is already enrolled for this school year.']);
            }

            // Insert enrolment
            $stmt = $this->db->prepare("
            INSERT INTO enrolment 
                (student_id, adviser_id, section_name, school_year_id, Grade_level, enrolment_Status)
            VALUES (?, ?, ?, ?, ?, 'Approved')
        ");
            $stmt->execute([$student_id, $adviser_id, $section_name, $schoolyear_id, $grade_level]);
            $enrolment_id = $this->db->lastInsertId();

            // Insert selected subjects
            $stmt = $this->db->prepare("
            INSERT INTO enrolment_subjects (enrolment_id, subjects_id) 
            VALUES (?, ?)
        ");
            foreach ($subjects as $subj_id) {
                $stmt->execute([$enrolment_id, $subj_id]);
            }

            $stmt = $this->db->prepare("
            UPDATE student 
            SET enrolment_status = 'Active' 
            WHERE student_id = ?
        ");
            $stmt->execute([$student_id]);

            return json_encode(['status' => 1, 'message' => 'Enrolment approved successfully.']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            // if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            //     return json_encode(['status' => 0, 'message' => 'Invalid data provided. Please check your selections.']);
            // }
            return json_encode(['status' => 0, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    function reenrollstud_form()
    {
        $student_id = $_POST["student_id"] ?? null;
        if (!$student_id) {
            return json_encode([
                'status' => 0,
                'message' => 'No student selected'
            ]);
        }
        try {
            //code...
            $query = "UPDATE student SET enrolment_status = 'pending', isMovingUP = NULL WHERE student_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$student_id]);
            return json_encode([
                'status' => 1,
                'message' => 'Student is on Enrollment Process!'
            ]);
        } catch (\Throwable $th) {

            return json_encode([
                'status' => 0,
                'message' => 'Something went wrong in: ' . $th->getMessage()
            ]);
        }
    }
    function enrollstud_form()
    {
        $student_id = $_POST["student_id"] ?? null;
        $gradeLevel = $_POST["student_lvl"] ?? null;
        if (!$student_id) {
            return json_encode([
                'status' => 0,
                'message' => 'No student selected'
            ]);
        }
        $activeSyStmt = $this->db->prepare("
    SELECT school_year_id, school_year_name
    FROM school_year
    WHERE school_year_status = 'Active'
    LIMIT 1
");
        $activeSyStmt->execute();

        $activeSy = $activeSyStmt->fetch(PDO::FETCH_ASSOC);

        if (!$activeSy) {
            return json_encode([
                'status' => 0,
                'message' => 'No active school year found'
            ]);
        }

        $trStmt = $this->db->prepare("
    SELECT enrolment_id 
    FROM enrolment 
    WHERE student_id = ? AND school_year_id = ?
");
        $trStmt->execute([$student_id, $activeSy['school_year_id']]);

        $tr = $trStmt->fetch(PDO::FETCH_ASSOC);

        if ($tr) {
            return json_encode([
                'status' => 0,
                'message' => 'Student is already enrolled for the active school year: ' . $activeSy['school_year_name']
            ]);
        }
        $stmt = $this->db->prepare("
                SELECT * 
                FROM student 
                WHERE student_id = ?
            ");
        $stmt->execute([$student_id]);
        $std = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($std['gradeLevel'] == 'Grade 6') {
            return json_encode(['status' => 0, 'message' => 'Student grade level on this system limit reached.']);
        }

        try {
            $query = "UPDATE student SET enrolment_status = 'pending', isMovingUP = NULL, gradeLevel = ? WHERE student_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$gradeLevel, $student_id]);
            return json_encode([
                'status' => 1,
                'message' => 'Student is on Enrollment Process!'
            ]);
        } catch (\Throwable $th) {

            return json_encode([
                'status' => 0,
                'message' => 'Something went wrong in: ' . $th->getMessage()
            ]);
        }
    }
    function activationSY_form()
    {
        $school_year_id = $_POST["school_year_id"] ?? null;

        try {
            $query = "UPDATE school_year SET school_year_status = 'Inactive'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $query = "UPDATE school_year SET school_year_status = 'Active' WHERE school_year_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$school_year_id]);
            $_SESSION['active_sy_id'] = $school_year_id;
            return json_encode([
                'status' => 1,
                'message' => 'School Year Activated successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function DeactivationSY_form()
    {
        $school_year_id = $_POST["school_year_id"] ?? null;

        // Validate input
        if (empty($school_year_id)) {
            return json_encode([
                'status' => 0,
                'message' => 'School Year ID is required.'
            ]);
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            // // Use DELETE FROM (not DELETE *) and handle errors
            // $tables = ['classes', 'enrolment', 'enrolment_subjects', 'stuenrolmentinfo', 'attendance', 'student', 'stuenrolmentinfo'];

            // foreach ($tables as $table) {
            //     $stmt = $this->db->prepare("DELETE FROM $table");
            //     if (!$stmt->execute()) {
            //         throw new Exception("Failed to clear $table table");
            //     }
            // }

            // Deactivate the school year
            $query = "UPDATE school_year SET school_year_status = 'Inactive' WHERE school_year_id = :school_year_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':school_year_id' => $school_year_id]);

            // Check if update was successful
            if ($stmt->rowCount() === 0) {
                throw new Exception("School Year not found or already inactive");
            }

            // Commit transaction
            $this->db->commit();

            return json_encode([
                'status' => 1,
                'message' => 'School Year Deactivated successfully! All related data has been cleared.'
            ]);
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error in DeactivationSY_form: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in DeactivationSY_form: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }
    function rejectEnrolment_form()
    {
        $student_id = $_POST["studentID"] ?? null;

        try {
            $query = "UPDATE student SET enrolment_status = 'rejected' WHERE student_id = '$student_id'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return json_encode([
                'status' => 1,
                'message' => 'Student Rejected successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function stduentEnrolment_form()
    {
        $student_id = $_POST["student_id"] ?? null;

        if (!$student_id) {
            return json_encode([
                'status' => 0,
                'message' => 'Student ID is required'
            ]);
        }

        try {
            // Update student table
            $student_query = "UPDATE student SET 
                lrn = :lrn,
                fname = :fname,
                mname = :mname,
                lname = :lname,
                suffix = :suffix,
                sex = :gender,
                birthdate = :birthdate,
                birthplace = :birth_place,
                religion = :religious,
                enrolment_status = 'pending'
                WHERE student_id = :student_id";

            $student_stmt = $this->db->prepare($student_query);
            $student_stmt->execute([
                ':lrn' => $_POST['lrn'],
                ':fname' => $_POST['fname'],
                ':mname' => $_POST['mname'],
                ':lname' => $_POST['lname'],
                ':suffix' => $_POST['suffix'],
                ':gender' => $_POST['gender'],
                ':birthdate' => $_POST['birthdate'],
                ':birth_place' => $_POST['birth_place'],
                ':religious' => $_POST['religious'],
                ':student_id' => $student_id
            ]);

            // Handle arrays (convert to comma-separated strings)
            $diagnosis = isset($_POST['diagnosis']) ? implode(',', $_POST['diagnosis']) : '';
            $manifestations = isset($_POST['manifestations']) ? implode(',', $_POST['manifestations']) : '';
            $learning_mode = isset($_POST['learning_mode']) ? implode(',', $_POST['learning_mode']) : '';

            // Check if enrolment info already exists
            $check_query = "SELECT COUNT(*) FROM stuenrolmentinfo WHERE student_id = :student_id";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([':student_id' => $student_id]);
            $exists = $check_stmt->fetchColumn();

            if ($exists) {
                // Update existing record
                $enrolment_query = "UPDATE stuenrolmentinfo SET 
                    mother_tongue = :tongue,
                    house_no = :current_house_no,
                    street = :current_street,
                    barnagay = :current_barangay,
                    city = :current_city,
                    province = :current_province,
                    country = :current_country,
                    zip_code = :current_zip,
                    diagnosis = :diagnosis,
                    manifestations = :manifestations,
                    pwd_id = :has_pwd_id_specific,
                    balik_aral = :last_grade_level,
                    learning_mode = :learning_mode,
                    indigenous_people = :ip_specify,
                    fourPs = :household_id
                    WHERE student_id = :student_id";
            } else {
                // Insert new record
                $enrolment_query = "INSERT INTO stuenrolmentinfo (
                    student_id, mother_tongue, house_no, street, barnagay, city, 
                    province, country, zip_code, diagnosis, manifestations, pwd_id, 
                    balik_aral, learning_mode, indigenous_people, fourPs
                ) VALUES (
                    :student_id, :tongue, :current_house_no, :current_street, :current_barangay, 
                    :current_city, :current_province, :current_country, :current_zip, 
                    :diagnosis, :manifestations, :has_pwd_id_specific, :last_grade_level, 
                    :learning_mode, :ip_specify, :household_id
                )";
            }

            $enrolment_stmt = $this->db->prepare($enrolment_query);
            $enrolment_params = [
                ':student_id' => $student_id,
                ':tongue' => $_POST['tongue'],
                ':current_house_no' => $_POST['current_house_no'],
                ':current_street' => $_POST['current_street'],
                ':current_barangay' => $_POST['current_barangay'],
                ':current_city' => $_POST['current_city'],
                ':current_province' => $_POST['current_province'],
                ':current_country' => $_POST['current_country'],
                ':current_zip' => $_POST['current_zip'],
                ':diagnosis' => $diagnosis,
                ':manifestations' => $manifestations,
                ':has_pwd_id_specific' => $_POST['has_pwd_id_specific'],
                ':last_grade_level' => $_POST['last_grade_level'],
                ':learning_mode' => $learning_mode,
                ':ip_specify' => $_POST['ip_specify'],
                ':household_id' => $_POST['household_id']
            ];

            $enrolment_stmt->execute($enrolment_params);

            return json_encode([
                'status' => 1,
                'message' => 'Enrollment information updated successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'error' => $e->getMessage(),
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function student_update_form()
    {
        try {
            $student_id = $_POST["student_id"] ?? null;
            if (!$student_id) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Student ID is required'
                ]);
            }

            // ---- Student Info ----
            $fname = $_POST["fname"] ?? '';
            $mname = $_POST["mname"] ?? '';
            $lname = $_POST["lname"] ?? '';
            $suffix = $_POST["suffix"] ?? '';
            $sex = $_POST["gender"] ?? '';
            $birthdate = $_POST["birthdate"] ?? '';
            $birthplace = $_POST["birthplace"] ?? '';
            $religion = $_POST["religion"] ?? '';

            // ---- Parent Info ----
            $f_firstname = $_POST["f_firstname"] ?? '';
            $f_middlename = $_POST["f_middlename"] ?? '';
            $f_lastname = $_POST["f_lastname"] ?? '';

            $m_firstname = $_POST["m_firstname"] ?? '';
            $m_middlename = $_POST["m_middlename"] ?? '';
            $m_lastname = $_POST["m_lastname"] ?? '';

            $g_firstname = $_POST["g_firstname"] ?? '';
            $g_middlename = $_POST["g_middlename"] ?? '';
            $g_lastname = $_POST["g_lastname"] ?? '';
            $g_relationship = $_POST["g_relationship"] ?? '';
            $p_contact = $_POST["p_contact"] ?? '';

            // ---- Address Info ----
            $house_no = $_POST["house_no"] ?? '';
            $street = $_POST["street"] ?? '';
            $barangay = $_POST["barnagay"] ?? '';
            $city = $_POST["city"] ?? '';
            $province = $_POST["province"] ?? '';
            $country = $_POST["country"] ?? '';
            $zip_code = $_POST["zip_code"] ?? '';

            // ---- File Upload Handling ----
            $profile_filename = null;

            if (isset($_FILES['student_profile']) && $_FILES['student_profile']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['student_profile'];

                // Validate file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                $file_type = mime_content_type($file['tmp_name']);

                if (!in_array($file_type, $allowed_types)) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Invalid file type. Only JPG, PNG, and GIF images are allowed.'
                    ]);
                }

                // Validate file size (max 2MB)
                $max_size = 2 * 1024 * 1024; // 2MB
                if ($file['size'] > $max_size) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'File size too large. Maximum size is 2MB.'
                    ]);
                }

                // Create upload directory if it doesn't exist
                $upload_dir = 'uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $profile_filename = 'profile_' . $student_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $profile_filename;

                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Failed to upload profile picture. Please try again.'
                    ]);
                }

                // Get old profile picture to delete it later
                $stmt_old = $this->db->prepare("SELECT student_profile FROM student WHERE student_id = :student_id");
                $stmt_old->execute([':student_id' => $student_id]);
                $old_profile = $stmt_old->fetchColumn();
            }

            // Start Transaction
            $this->db->beginTransaction();

            // ============================
            // UPDATE STUDENT TABLE
            // ============================
            $update_fields = [
                'fname = :fname',
                'mname = :mname',
                'lname = :lname',
                'suffix = :suffix',
                'sex = :sex',
                'birthdate = :birthdate',
                'birthplace = :birthplace',
                'religion = :religion',
                'address = :address'
            ];

            $params = [
                ':fname' => $fname,
                ':mname' => $mname,
                ':lname' => $lname,
                ':suffix' => $suffix,
                ':sex' => $sex,
                ':birthdate' => $birthdate,
                ':birthplace' => $birthplace,
                ':religion' => $religion,
                ':student_id' => $student_id
            ];

            // Add profile picture update if uploaded
            if ($profile_filename !== null) {
                $update_fields[] = 'student_profile = :student_profile';
                $params[':student_profile'] = $profile_filename;
            }

            // Prepare full address
            $fullAddress = "$house_no $street, $barangay, $city, $province, $country, $zip_code";
            $params[':address'] = $fullAddress;

            // Build the SQL query
            $sql = "UPDATE student SET " . implode(', ', $update_fields) . " WHERE student_id = :student_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // ============================
            // UPDATE parents_info TABLE
            // ============================
            $stmt2 = $this->db->prepare("
                UPDATE parents_info SET 
                    f_firstname = :f_firstname,
                    f_middlename = :f_middlename,
                    f_lastname = :f_lastname,
                    
                    m_firstname = :m_firstname,
                    m_middlename = :m_middlename,
                    m_lastname = :m_lastname,

                    g_firstname = :g_firstname,
                    g_middlename = :g_middlename,
                    g_lastname = :g_lastname,
                    g_relationship = :g_relationship,

                    p_contact = :p_contact
                WHERE student_id = :student_id
            ");

            $stmt2->execute([
                ':f_firstname' => $f_firstname,
                ':f_middlename' => $f_middlename,
                ':f_lastname' => $f_lastname,

                ':m_firstname' => $m_firstname,
                ':m_middlename' => $m_middlename,
                ':m_lastname' => $m_lastname,

                ':g_firstname' => $g_firstname,
                ':g_middlename' => $g_middlename,
                ':g_lastname' => $g_lastname,
                ':g_relationship' => $g_relationship,

                ':p_contact' => $p_contact,
                ':student_id' => $student_id
            ]);

            // Commit Transaction
            $this->db->commit();

            // Delete old profile picture if a new one was uploaded
            if ($profile_filename !== null && $old_profile && $old_profile !== '') {
                $old_file_path = '../../assets/image/uploads/' . $old_profile;
                if (file_exists($old_file_path) && $old_file_path != $upload_path) {
                    @unlink($old_file_path); // Use @ to suppress errors if file doesn't exist
                }
            }

            // UPATE STUDENT ADDRESS

            $stmtAddress = $this->db->prepare("UPDATE stuenrolmentinfo SET house_no = :house_no, street = :street, barnagay = :barnagay,
            city = :city, province = :province, country = :country, zip_code = :zip_code
            WHERE student_id = :student_id");
            $stmtAddress->execute([
                ':house_no' => $house_no,
                ':street' => $street,
                ':barnagay' => $barangay,
                ':city' => $city,
                ':province' => $province,
                ':country' => $country,
                ':zip_code' => $zip_code,
                ':student_id' => $student_id
            ]);


            return json_encode([
                'status' => 1,
                'message' => 'Student profile updated successfully!' .
                    ($profile_filename !== null ? ' Profile picture has been updated.' : '')
            ]);
        } catch (PDOException $e) {
            $this->db->rollBack();

            // Delete uploaded file if transaction failed
            if (isset($upload_path) && file_exists($upload_path)) {
                @unlink($upload_path);
            }

            return json_encode([
                'status' => 0,
                'message' => 'Database error occurred. Please try again.',
                'error' => $e->getMessage() // Remove in production
            ]);
        }
    }

    function displayStudentInfo()
    {
        $user_id = $_POST["user_id"] ?? null;

        // User Information
        $firstname = $_POST["firstname"] ?? '';
        $middlename = $_POST["middlename"] ?? '';
        $lastname = $_POST["lastname"] ?? '';
        $suffix = $_POST["suffix"] ?? '';
        $sex = $_POST["sex"] ?? '';
        $email = $_POST["email"] ?? '';
        $contact = $_POST["contact"] ?? '';

        if (!$user_id) {
            return json_encode([
                'status' => 0,
                'message' => 'User ID is required'
            ]);
        }

        try {
            // Start Transaction
            $this->db->beginTransaction();

            $user_query = "UPDATE users SET 
                firstname = :firstname,
                middlename = :middlename,
                lastname = :lastname,
                suffix = :suffix,
                gender = :gender,
                email = :email,
                contact = :contact
            WHERE user_id = :user_id";

            $user_stmt = $this->db->prepare($user_query);
            $user_stmt->execute([
                ':firstname' => $firstname,
                ':middlename' => $middlename,
                ':lastname' => $lastname,
                ':suffix' => $suffix,
                ':gender' => $sex,
                ':email' => $email,
                ':contact' => $contact,
                ':user_id' => $user_id
            ]);

            $this->db->commit();

            return json_encode([
                'status' => 1,
                'message' => 'User information updated successfully!',
                'data' => [
                    'name' => $firstname . ' ' . $lastname,
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error: " . $e->getMessage());

            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.',
                'error' => $e->getMessage()
            ]);
        }
    }

    function medical_update()
    {
        $student_id = $_POST["student_id"] ?? null;

        if (!$student_id) {
            return json_encode([
                'status' => 0,
                'message' => 'Student ID is required'
            ]);
        }

        try {
            // Update student table
            $student_query = "UPDATE student SET 
                weight = :weight,
                height = :height,
                height_squared = :height_squared
                WHERE student_id = :student_id";

            $student_stmt = $this->db->prepare($student_query);
            $student_stmt->execute([
                ':weight' => $_POST['weight'] ?? null,
                ':height' => $_POST['height'] ?? null,
                ':height_squared' => $_POST['height_squared'] ?? null,
                ':student_id' => $student_id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Student BMI information updated successfully!'
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }




    function deleteClassroom_form()
    {
        $classroom_id = $_POST["classroom_id"];
        try {
            $stmt = $this->db->prepare("DELETE FROM classrooms WHERE room_id = :room_id");
            $stmt->bindParam(':room_id', $classroom_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'Classroom deleted successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No classroom found with that ID'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }
    function deleteSection_form()
    {
        $section_id = $_POST["section_id"];
        try {
            $stmt = $this->db->prepare("DELETE FROM sections WHERE section_id = :section_id");
            $stmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'Section deleted successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No classroom found with that ID'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }
    function editClassroom_form()
    {
        $classroom_id   = $_POST["classroom_id"];
        $room_status    = $_POST["room_status"] ?? '';
        $classroom_name = $_POST["classroom_name"] ?? '';
        $classroom_type = $_POST["classroom_type"] ?? '';

        try {
            $stmt = $this->db->prepare("
                UPDATE classrooms 
                SET room_status = :room_status, room_name = :room_name, room_type = :room_type
                WHERE room_id = :room_id
            ");
            $stmt->bindParam(':room_status', $room_status, PDO::PARAM_STR);
            $stmt->bindParam(':room_name', $classroom_name, PDO::PARAM_STR);
            $stmt->bindParam(':room_type', $classroom_type, PDO::PARAM_STR);
            $stmt->bindParam(':room_id', $classroom_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'Classroom updated successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No changes made (maybe same values or invalid ID) the room id is: ' . $classroom_id
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function getClassroomById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM classrooms WHERE room_id = :room_id");
            $stmt->bindParam(':room_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $classroom = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($classroom) {
                return json_encode([
                    'status' => 1,
                    'data' => $classroom
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'Classroom not found'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }


    function editSection_form()
    {
        $section_id   = $_POST["section_id"];
        $section_status    = $_POST["section_status"] ?? '';
        $section_name = $_POST["section_name"] ?? '';
        $section_grade_level = $_POST["section_grade_level"] ?? '';

        try {
            $stmt = $this->db->prepare("
                UPDATE sections 
                SET section_status = :section_status, section_name = :section_name, section_grade_level = :section_grade_level
                WHERE section_id = :section_id
            ");
            $stmt->bindParam(':section_status', $section_status, PDO::PARAM_STR);
            $stmt->bindParam(':section_name', $section_name, PDO::PARAM_STR);
            $stmt->bindParam(':section_grade_level', $section_grade_level, PDO::PARAM_STR);
            $stmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'Section updated successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No changes made (maybe same values or invalid ID) the room id is: ' . $section_id
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function getSectionById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sections WHERE section_id = :section_id");
            $stmt->bindParam(':section_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $section = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($section) {
                return json_encode([
                    'status' => 1,
                    'data' => $section
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'Section not found'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function deleteSchoolYear_form()
    {
        $school_year_id = $_POST["school_year_id"];
        if ($_SESSION['active_sy_id'] === $school_year_id) {
            session_unset();
            session_destroy();
            header('Location: ' . BASE_PATH . '/src/index.php');
        }
        try {
            $stmt = $this->db->prepare("DELETE FROM school_year WHERE school_year_id = :school_year_id");
            $stmt->bindParam(':school_year_id', $school_year_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'School Year deleted successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No School Year found with that ID'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function getSchoolYearById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM school_year WHERE school_year_id = :school_year_id");
            $stmt->bindParam(':school_year_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $schoolYear = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($schoolYear) {
                return json_encode([
                    'status' => 1,
                    'data' => $schoolYear
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'School Year not found'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function editSchoolyear_form()
    {
        $school_year_id   = $_POST["school_year_id"];
        $school_year_status = $_POST["school_year_status"] ?? '';
        $school_year_name   = $_POST["school_year_name"] ?? '';

        try {
            $stmt = $this->db->prepare("
                UPDATE school_year 
                SET school_year_status = :school_year_status, school_year_name = :school_year_name
                WHERE school_year_id = :school_year_id
            ");
            $stmt->bindParam(':school_year_status', $school_year_status, PDO::PARAM_STR);
            $stmt->bindParam(':school_year_name', $school_year_name, PDO::PARAM_STR);
            $stmt->bindParam(':school_year_id', $school_year_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'School Year updated successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No changes made (maybe same values or invalid ID): ' . $school_year_id
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    function deleteSubject_form()
    {
        $subject_id = $_POST["subject_id"];
        try {
            $stmt = $this->db->prepare("DELETE FROM subjects WHERE subject_id = :subject_id");
            $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'Subject deleted successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No Subject found with that ID'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function getSubjectsById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM subjects WHERE subject_id = :subject_id");
            $stmt->bindParam(':subject_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $subjects = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($subjects) {
                return json_encode([
                    'status' => 1,
                    'data' => $subjects
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'Subjects not found'
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function editSubjects_form()
    {
        $subject_id   = $_POST["subject_id"];
        $grade_level   = $_POST["grade_level"];
        $subject_name = $_POST["subject_name"] ?? '';
        $subject_code   = $_POST["subject_code"] ?? '';
        $subject_units   = $_POST["subject_units"] ?? '';
        $subjects_status   = $_POST["subjects_status"] ?? '';

        try {
            $stmt = $this->db->prepare("
                UPDATE subjects 
                SET grade_level = :grade_level, subject_name = :subject_name, subject_code = :subject_code, subject_units = :subject_units,
                subjects_status = :subjects_status
                WHERE subject_id = :subject_id
            ");
            $stmt->bindParam(':grade_level', $grade_level, PDO::PARAM_STR);
            $stmt->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
            $stmt->bindParam(':subject_code', $subject_code, PDO::PARAM_STR);
            $stmt->bindParam(':subject_units', $subject_units, PDO::PARAM_STR);
            $stmt->bindParam(':subjects_status', $subjects_status, PDO::PARAM_STR);
            $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode([
                    'status' => 1,
                    'message' => 'Subjects updated successfully'
                ]);
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => 'No changes made (maybe same values or invalid ID): ' . $subject_id
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    function attendance($student_id, $attendanceType, $clientTime, $session)
    {
        return $this->recordAttendance($student_id, $attendanceType, $clientTime, $session);
    }

    private function recordAttendance($student_id, string $attendanceType, string $clientTime, string $session)
    {
        $adviser_id = $_SESSION['user_id'] ?? null;

        if ((!$student_id || !$attendanceType || !$session) && $session !== 'confirm' && $session !== 'cancel') {
            return $this->jsonError("Missing required data.");
        }

        try {
            $tz = new DateTimeZone('Asia/Manila');
            $session = strtolower($session);

            // Check if today is Sunday
            $today = new DateTime($this->nao, $tz);
            if ($today->format('w') == 0) {
                return $this->jsonError("Attendance cannot be recorded on Sundays.");
            }

            if (!in_array($session, ['morning', 'afternoon', 'confirm', 'cancel'])) {
                return $this->jsonError("Invalid session.");
            }

            if ($session !== 'confirm' && $session !== 'cancel') {
                if (!$clientTime) return $this->jsonError("Missing time.");

                $clientDT = DateTime::createFromFormat('H:i', $clientTime, $tz);
                if (!$clientDT) return $this->jsonError("Invalid time format.");

                $hour = (int)$clientDT->format('H');
                if ($hour < 6 || $hour > 18) {
                    return $this->jsonError("Attendance allowed only 6:00–18:00.");
                }

                $attendanceDT = (new DateTime('now', $tz))->format('Y-m-d') . ' ' . $clientDT->format('H:i:s');
            }

            $sy_id = $this->db
                ->query("SELECT school_year_id FROM school_year WHERE school_year_status='Active' LIMIT 1")
                ->fetchColumn();

            if (!$sy_id) return $this->jsonError("No active school year.");

            $stmt = $this->db->prepare("
                SELECT * FROM attendance 
                WHERE student_id = ?
                AND school_year_id = ?
                AND attendance_at >= CURDATE()
                AND attendance_at < CURDATE() + INTERVAL 1 DAY
                LIMIT 1
            ");
            $stmt->execute([$student_id, $sy_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                if ($session === 'afternoon') {
                    return $this->jsonError("Morning attendance not yet recorded.");
                }
                $this->db->prepare("INSERT INTO attendance (student_id, adviser_id, school_year_id, attendance_at)
                    VALUES (?, ?, ?, NOW())
                ")->execute([$student_id, $adviser_id, $sy_id]);

                $attendance_id = $this->db->lastInsertId();

                $row = [
                    'attendance_id' => $attendance_id,
                    'attendance_type' => null,
                    'A_attendance_type' => null
                ];
            }


            $attendance_id = $row['attendance_id'];

            if ($session === 'cancel') {
                $this->db->prepare("
                DELETE FROM attendance 
                WHERE attendance_id=?
            ")->execute([$attendance_id]);
                return $this->jsonSuccess("Attendance canceled.");
            } elseif ($session === 'morning') {
                if ($row['attendance_type'] !== null) {
                    return $this->jsonError("Morning attendance already recorded.");
                }
                $this->db->prepare("
                UPDATE attendance 
                SET morning_attendance=?, attendance_type=? 
                WHERE attendance_id=?")->execute([$attendanceDT, $attendanceType, $attendance_id]);
            } elseif ($session === 'afternoon') {
                if ($row['A_attendance_type'] !== null) {
                    return $this->jsonError("Afternoon attendance already recorded.");
                }
                $this->db->prepare("
                UPDATE attendance 
                SET afternoon_attendance=?, A_attendance_type=? 
                WHERE attendance_id=?")->execute([$attendanceDT, $attendanceType, $attendance_id]);
            } elseif ($session === 'confirm') {

                $stmt = $this->db->prepare("
                    SELECT attendance_type, A_attendance_type 
                    FROM attendance 
                    WHERE attendance_id=?
                ");
                $stmt->execute([$attendance_id]);
                $types = $stmt->fetch(PDO::FETCH_ASSOC);

                $morningType   = $types['attendance_type'] ?? null;
                $afternoonType = $types['A_attendance_type'] ?? null;

                if ($morningType === null && $afternoonType === null) {
                    return $this->jsonError("Nothing to confirm.");
                }

                $summary = null;

                if ($morningType === 'Absent' && $afternoonType === 'Absent') {
                    $summary = 'Absent';
                } elseif (
                    $morningType === 'Present' || $afternoonType === 'Present' ||
                    $morningType === 'Late'    || $afternoonType === 'Late'
                ) {
                    $summary = 'Present';
                }

                $this->db->prepare("
                    UPDATE attendance 
                    SET attendance_summary=?
                    WHERE attendance_id=?
                ")->execute([$summary, $attendance_id]);


                return $this->jsonSuccess("Attendance confirmed.");
            }

            return $this->jsonSuccess(ucfirst($session) . " attendance recorded.");
        } catch (PDOException $e) {
            return $this->jsonError("DB Error: " . $e->getMessage());
        }
    }





    private function jsonError($msg)
    {
        return json_encode(['status' => 0, 'message' => $msg]);
    }
    private function jsonSuccess($msg)
    {
        return json_encode(['status' => 1, 'message' => $msg]);
    }

    function status_form()
    {
        $status = $_POST["status"] ?? '';
        $id = $_POST["user_id"];

        if (empty($status) || empty($id)) {
            return json_encode([
                'status' => 0,
                'message' => 'Invalid data provided'
            ]);
        }

        try {
            $stmt = $this->db->prepare("UPDATE users SET status = :status WHERE user_id = :user_id");
            $stmt->execute([
                ':status' => $status,
                ':user_id' => $id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Status updated successfully: ' . $status . ' ID: ' . $id
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function status_enrolment_form()
    {
        $status = $_POST["status"] ?? '';
        $id = $_POST["user_id"];

        if (empty($status) || empty($id)) {
            return json_encode([
                'status' => 0,
                'message' => 'Invalid data provided'
            ]);
        }

        try {
            $stmt = $this->db->prepare("UPDATE student SET enrolment_status = :enrolment_status WHERE student_id = :student_id");
            $stmt->execute([
                ':enrolment_status' => $status,
                ':student_id' => $id
            ]);

            return json_encode([
                'status' => 1,
                'message' => 'Status updated successfully: ' . $status . ' ID: ' . $id
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    function sfFour_form()
    {
        $school_id   = $_POST["school_id"] ?? '';
        $region      = $_POST["region"] ?? '';
        $division    = $_POST["Division"] ?? ''; // fixed casing
        $district    = $_POST["district"] ?? '';
        $school_name = $_POST["school_name"] ?? '';
        $report_for_the_month_of = $_POST["report_for_the_month_of"] ?? null; // must be YYYY-MM-DD
        $school_year_id = $_POST["school_year"] ?? '';
        $Previous_Month = $_POST["Previous_Month"] ?? '';
        $For_the_month = $_POST["For_the_month"] ?? '';
        $Cumulative_as_of_End_of_Month = $_POST["Cumulative_as_of_End_of_Month"] ?? '';
        $sf_add_data_id = $_POST["id"] ?? 0;

        try {
            // safely get school_year_id
            $stmt = $this->db->prepare("SELECT school_year_id FROM school_year WHERE school_year_id = :school_year_id");
            $stmt->execute([':school_year_id' => $school_year_id]);
            $syID = $stmt->fetch(PDO::FETCH_ASSOC);
            $school_year_id = $syID["school_year_id"] ?? null;
            $report_for_the_month_of = $_POST["report_for_the_month_of"] ?? null;

            if ($report_for_the_month_of) {
                $monthNumber = date('m', strtotime($report_for_the_month_of));
                $year = date('Y');
                $report_for_the_month_of = $year . '-' . $monthNumber . '-01';
            }
            if (!$school_year_id) {
                return json_encode([
                    'status' => 0,
                    'message' => "Invalid school year id"
                ]);
            }
            if ($school_id == 3) {
                if (empty($report_for_the_month_of)) {
                    return json_encode([
                        'status' => 0,
                        'message' => 'Please fill in all required fields'
                    ]);
                }
            }else{
                $report_for_the_month_of = null;
            }
            if (
                empty($school_id) ||
                empty($region) ||
                empty($division) ||
                empty($district) ||
                empty($school_name)
            ) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Please fill in all required fields'
                ]);
            }

            if ($sf_add_data_id > 0) {
                // check if record exists
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM sf_add_data WHERE sf_add_data_id = :id");
                $stmt->execute([':id' => $sf_add_data_id]);
                $exists = $stmt->fetchColumn();
            } else {
                $exists = 0;
            }

            if ($exists) {
                // UPDATE query
                $stmt = $this->db->prepare("
                    UPDATE sf_add_data 
                    SET 
                        school_id = :school_id,
                        school_name = :school_name,
                        Division = :division,
                        region = :region,
                        sy_id = :sy_id,
                        district = :district,
                        report_for_the_month_of = :report_for_the_month_of,
                        Previous_Month = :previous_month,
                        For_the_month = :for_the_month,
                        Cumulative_as_of_End_of_Month = :cumulative_as_of_end_of_month
                    WHERE sf_add_data_id = :sf_add_data_id
                ");

                $stmt->execute([
                    ':school_id' => $school_id,
                    ':school_name' => $school_name,
                    ':division' => $division,
                    ':region' => $region,
                    ':sy_id' => $school_year_id,
                    ':district' => $district,
                    ':report_for_the_month_of' => $report_for_the_month_of,
                    ':previous_month' => $Previous_Month,
                    ':for_the_month' => $For_the_month,
                    ':cumulative_as_of_end_of_month' => $Cumulative_as_of_End_of_Month,
                    ':sf_add_data_id' => $sf_add_data_id
                ]);

                return json_encode([
                    'status' => 1,
                    'message' => ($stmt->rowCount() > 0)
                        ? 'School Form updated successfully'
                        : 'No changes were made (data is already up-to-date)'
                ]);
            } else {
                // INSERT query
                $stmt = $this->db->prepare("
                    INSERT INTO sf_add_data (
                        school_id, school_name, Division, region, sy_id, district,
                        report_for_the_month_of, Previous_Month, For_the_month, Cumulative_as_of_End_of_Month
                    ) VALUES (
                        :school_id, :school_name, :division, :region, :sy_id, :district,
                        :report_for_the_month_of, :previous_month, :for_the_month, :cumulative_as_of_end_of_month
                    )
                ");

                $stmt->execute([
                    ':school_id' => $school_id,
                    ':school_name' => $school_name,
                    ':division' => $division,
                    ':region' => $region,
                    ':sy_id' => $school_year_id,
                    ':district' => $district,
                    ':report_for_the_month_of' => $report_for_the_month_of,
                    ':previous_month' => $Previous_Month,
                    ':for_the_month' => $For_the_month,
                    ':cumulative_as_of_end_of_month' => $Cumulative_as_of_End_of_Month
                ]);

                return json_encode([
                    'status' => 1,
                    'message' => 'SF4 inserted successfully',
                    'inserted_id' => $this->db->lastInsertId()
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    function sfEight_form()
    {
        $school_id   = $_POST["school_id"] ?? '';
        $region      = $_POST["region"] ?? '';
        $division    = $_POST["Division"] ?? ''; // fixed casing
        $district    = $_POST["district"] ?? '';
        $school_name = $_POST["school_name"] ?? '';
        $report_for_the_month_of = $_POST["report_for_the_month_of"] ?? ''; // must be YYYY-MM-DD
        $school_year_name = $_POST["school_year_name"] ?? '';
        $Previous_Month = $_POST["Previous_Month"] ?? '';
        $For_the_month = $_POST["For_the_month"] ?? '';
        $Cumulative_as_of_End_of_Month = $_POST["Cumulative_as_of_End_of_Month"] ?? '';
        $sf_add_data_id = $_POST["id"] ?? 0;

        try {
            // safely get school_year_id
            $stmt = $this->db->prepare("SELECT school_year_id FROM school_year WHERE school_year_name = :school_year_name");
            $stmt->execute([':school_year_name' => $school_year_name]);
            $syID = $stmt->fetch(PDO::FETCH_ASSOC);
            $school_year_id = $syID["school_year_id"] ?? null;

            if (!$school_year_id) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Invalid school year'
                ]);
            }

            if ($sf_add_data_id > 0) {
                // check if record exists
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM sf_add_data WHERE sf_add_data_id = :id");
                $stmt->execute([':id' => $sf_add_data_id]);
                $exists = $stmt->fetchColumn();
            } else {
                $exists = 0;
            }

            if ($exists) {
                // UPDATE query
                $stmt = $this->db->prepare("
                    UPDATE sf_add_data 
                    SET 
                        school_id = :school_id,
                        school_name = :school_name,
                        Division = :division,
                        region = :region,
                        sy_id = :sy_id,
                        district = :district,
                        report_for_the_month_of = :report_for_the_month_of,
                        Previous_Month = :previous_month,
                        For_the_month = :for_the_month,
                        Cumulative_as_of_End_of_Month = :cumulative_as_of_end_of_month
                    WHERE sf_add_data_id = :sf_add_data_id
                ");

                $stmt->execute([
                    ':school_id' => $school_id,
                    ':school_name' => $school_name,
                    ':division' => $division,
                    ':region' => $region,
                    ':sy_id' => $school_year_id,
                    ':district' => $district,
                    ':report_for_the_month_of' => $report_for_the_month_of,
                    ':previous_month' => $Previous_Month,
                    ':for_the_month' => $For_the_month,
                    ':cumulative_as_of_end_of_month' => $Cumulative_as_of_End_of_Month,
                    ':sf_add_data_id' => $sf_add_data_id
                ]);

                return json_encode([
                    'status' => 1,
                    'message' => ($stmt->rowCount() > 0)
                        ? 'SF4 updated successfully'
                        : 'No changes were made (data is already up-to-date)'
                ]);
            } else {
                // INSERT query
                $stmt = $this->db->prepare("
                    INSERT INTO sf_add_data (
                        school_id, school_name, Division, region, sy_id, district,
                        report_for_the_month_of, Previous_Month, For_the_month, Cumulative_as_of_End_of_Month
                    ) VALUES (
                        :school_id, :school_name, :division, :region, :sy_id, :district,
                        :report_for_the_month_of, :previous_month, :for_the_month, :cumulative_as_of_end_of_month
                    )
                ");

                $stmt->execute([
                    ':school_id' => $school_id,
                    ':school_name' => $school_name,
                    ':division' => $division,
                    ':region' => $region,
                    ':sy_id' => $school_year_id,
                    ':district' => $district,
                    ':report_for_the_month_of' => $report_for_the_month_of,
                    ':previous_month' => $Previous_Month,
                    ':for_the_month' => $For_the_month,
                    ':cumulative_as_of_end_of_month' => $Cumulative_as_of_End_of_Month
                ]);

                return json_encode([
                    'status' => 1,
                    'message' => 'SF4 inserted successfully',
                    'inserted_id' => $this->db->lastInsertId()
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    function deleteFeedback_form()
    {
        try {
            $feedback_id = $_POST["feedback_id"];

            $stmt = $this->db->prepare("DELETE FROM feeback WHERE feeback_id = :feeback_id");
            $stmt->execute([
                'feeback_id' => $feedback_id
            ]);
            return json_encode([
                'status' => 1,
                'message' => 'Feedback deleted successfully!'
            ]);
        } catch (PDOException $e) {
            return json_encode([
                'status' => 0,
                'message' => 'An error occured: ' . $e->getMessage()
            ]);
        }
    }
}
