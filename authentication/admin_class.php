<?php
session_start();
require_once __DIR__ . '/../assets/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Action
{
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

    function Account_form(){
        
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

        // Validation code remains the same...

        try {
            // Check if username exists using prepared statement
            $stmt = $this->db->prepare("SELECT username FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $usernameTaken = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($usernameTaken){
                return json_encode([
                    'status' => 0,
                    'message' => 'Username ' . $usernameTaken["username"] . ' already taken please try another username'
                ]);
            }

            // Check if email exists
            $stmt = $this->db->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $emailTaken = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($emailTaken){
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
                $firstName, $middleName, $lastName, $suffix, 
                $email, $contact, $gender, $username, $hashedPassword, $user_role
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

    function classroom_form(){
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
            $checkStmt = $this->db->prepare("SELECT room_name FROM classrooms WHERE room_name = ?");
            $checkStmt->execute([$classroom_name]);
            $existingClassroom = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingClassroom) {
                return json_encode([
                    'status' => 0,
                    'message' => 'Classroom "'.$classroom_name.'" already exists!'
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
    function section_form(){
        $section_name = htmlspecialchars(trim($_POST["section_name"]));
        $grade_level = htmlspecialchars(trim($_POST["grade_level"]));
        $section_desc = htmlspecialchars(trim($_POST["section_desc"]));
        
        // Validate inputs
        if (empty($section_name) || empty($grade_level) || empty($section_desc)) {
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
                    'message' => 'Section "'.$section_name.'" already exists!'
                ]);
            }
            
            $query = "INSERT INTO sections (section_name, section_grade_level, section_description) 
                    VALUES (?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$section_name, $grade_level, $section_desc]);

            return json_encode([
                'status' => 1,
                'message' => 'Sectoin created successfully!'
            ]);
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function schoolYear_form(){
        $status = htmlspecialchars(trim($_POST["status"]));
        $schoolYear_name = htmlspecialchars(trim($_POST["schoolYear_name"]));
        
        // Validate inputs
        if (empty($status) || empty($schoolYear_name)) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields'
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
                    'message' => 'School Year: "'.$schoolYear_name.'" already exists!'
                ]);
            }
            
            $query = "INSERT INTO school_year (school_year_status, school_year_name) 
                    VALUES (?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status, $schoolYear_name]);

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
    function assignTeacher_form(){
        $classroom_id = htmlspecialchars(trim($_POST["classroom_id"]));
        $section_id = htmlspecialchars(trim($_POST["section_id"]));
        $grade_level = htmlspecialchars(trim($_POST["grade_level"]));
        $teacher_name = htmlspecialchars(trim($_POST["teacher_name"]));
        $schoolYear_id = htmlspecialchars(trim($_POST["schoolYear_id"]));
        
        // Validate inputs
        if (empty($classroom_id) || empty($section_id) || empty($grade_level)
                || empty($teacher_name) || empty($schoolYear_id)) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields'
            ]);
        }
        
        try {
            // Check if classroom already exists
            // $checkStmt = $this->db->prepare("SELECT school_year_name FROM school_year WHERE school_year_name = ?");
            // $checkStmt->execute([$schoolYear_name]);
            // $existingClassroom = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            // if ($existingClassroom) {
            //     return json_encode([
            //         'status' => 0,
            //         'message' => 'School Year: "'.$schoolYear_name.'" already exists!'
            //     ]);
            // }
            
            $query = "INSERT INTO classes (section_id, adviser_id, sy_id, section_name, grade_level) 
                    VALUES (?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status, $schoolYear_name]);

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
    function studentAcc_form() {
        $lrn = htmlspecialchars(trim($_POST["lrn"] ?? ''));
        $gradeLevel = htmlspecialchars(trim($_POST["grade_level"] ?? ''));
        $nickname = htmlspecialchars(trim($_POST["nickname"] ?? ''));
        $sex = htmlspecialchars(trim($_POST["sex"] ?? ''));
        $lastName = htmlspecialchars(trim($_POST["lastName"] ?? ''));
        $firstName = htmlspecialchars(trim($_POST["firstName"] ?? ''));
        $middleName = htmlspecialchars(trim($_POST["middleName"] ?? ''));
        $suffix = htmlspecialchars(trim($_POST["suffix"] ?? ''));
        $religion = htmlspecialchars(trim($_POST["religion"] ?? ''));
        $birthdate = $_POST["birthdate"] ?? null;
        $birthplace = $_POST["birthplace"] ?? null;

        try {
            // Check if LRN exists
            $checkStmt = $this->db->prepare("SELECT lrn FROM student WHERE lrn = ?");
            $checkStmt->execute([$lrn]);
            $lrnTaken = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($lrnTaken) {
                return json_encode([
                    'status' => 0,
                    'message' => 'LRN: "'.$lrn.'" already exists!'
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
                $allowed = ['jpg','jpeg','png','gif','webp'];
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
                $_SESSION['user_id'], $lrn, $firstName, $middleName, 
                $lastName, $suffix, $sex, $birthdate, $birthplace,
                $religion, $gradeLevel, $student_profile
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

    
}
