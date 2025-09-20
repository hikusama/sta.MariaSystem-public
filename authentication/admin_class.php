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
        $section_status = htmlspecialchars(trim($_POST["section_status"]));
        
        // Validate inputs
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
                    'message' => 'Section "'.$section_name.'" already exists!'
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
    function subjects_form(){
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
    function assignTeacher_form(){
        $classroom_id = htmlspecialchars(trim($_POST["classroom_id"]));
        $section_id = htmlspecialchars(trim($_POST["section_id"]));
        $grade_level = htmlspecialchars(trim($_POST["grade_level"]));
        $teacher_name = htmlspecialchars(trim($_POST["teacher_name"])); // this is adviser_id
        $schoolYear_id = htmlspecialchars(trim($_POST["schoolYear_id"]));
        
        if (empty($classroom_id) || empty($section_id) || empty($grade_level)
                || empty($teacher_name) || empty($schoolYear_id)) {
            return json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields'
            ]);
        }

        try {
            // Fetch section_name using section_id
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

            $query = "INSERT INTO classes (section_id, adviser_id, sy_id, section_name, grade_level) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$section_id, $teacher_name, $schoolYear_id, $section_name, $grade_level]);

            return json_encode([
                'status' => 1,
                'message' => 'Teacher assigned successfully!'
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
            $student_id = $this->db->lastInsertId();

            $query = "INSERT INTO stuEnrolmentInfo (student_id) VALUES ('$student_id')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

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
    function enrolment_form() {
        $section_name = $_POST['section_name'] ?? null;
        $adviser_id = $_POST['adviser_id'] ?? null;
        $schoolyear_id = $_POST['schoolyear_id'] ?? null;
        $grade_level = $_POST['grade_level'] ?? null;
        $subjects = $_POST['subjects'] ?? []; // Changed from subjects[] to subjects
        $student_id = $_POST['student_id'] ?? null;

        try {
            // Validate required fields
            if (!$adviser_id || !$schoolyear_id || !$grade_level || empty($subjects) || !$student_id || !$section_name) {
                return json_encode(['status'=>0,'message'=>'All fields are required.']);
            }

            // Check if student already has an enrolment for this school year
            $stmt = $this->db->prepare("SELECT enrolment_id FROM enrolment WHERE student_id = ? AND school_year_id = ?");
            $stmt->execute([$student_id, $schoolyear_id]);
            $existing_enrolment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_enrolment) {
                return json_encode(['status'=>0,'message'=>'Student already has an enrolment for this school year.']);
            }

            // Insert enrolment
            $stmt = $this->db->prepare("INSERT INTO enrolment 
                (student_id, adviser_id, section_name, school_year_id, Grade_level, enrolment_Status)
                VALUES (?, ?, ?, ?, ?, 'Approved')");
            $stmt->execute([$student_id, $adviser_id, $section_name, $schoolyear_id, $grade_level]);

            $enrolment_id = $this->db->lastInsertId();

            // Insert selected subjects
            $stmt = $this->db->prepare("INSERT INTO enrolment_subjects (enrolment_id, subjects_id) VALUES (?, ?)");
            foreach($subjects as $subj_id) {
                $stmt->execute([$enrolment_id, $subj_id]);
            }

            $stmt = $this->db->prepare("UPDATE student SET enrolment_status = 'Active' WHERE student_id = ?");
            $stmt->execute([$student_id]);

            return json_encode(['status'=>1,'message'=>'Enrolment approved successfully.']);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            // Check for specific constraint violations
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return json_encode(['status'=>0,'message'=>'Invalid data provided. Please check your selections.']);
            }
            return json_encode(['status'=>0,'message'=>'Database error: ' . $e->getMessage()]);
        }
    }
    function activationSY_form() {
        $school_year_id = $_POST["school_year_id"] ?? null;

        try {
            $query = "UPDATE school_year SET school_year_status = 'Active' WHERE school_year_id = '$school_year_id'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
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
    function DeactivationSY_form() {
        $school_year_id = $_POST["school_year_id"] ?? null;

        try {
            $query = "UPDATE school_year SET school_year_status = 'Inactive' WHERE school_year_id = '$school_year_id'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return json_encode([
                'status' => 1,
                'message' => 'School Year Deactivated successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function rejectEnrolment_form() {
        $student_id = $_POST["studentID"] ?? null;

        try {
            $query = "UPDATE student SET enrolment_status = 'rejected' WHERE student_id = '$student_id'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return json_encode([
                'status' => 1,
                'message' => 'School Year Deactivated successfully!'
            ]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return json_encode([
                'status' => 0,
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function stduentEnrolment_form() {
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
            $check_query = "SELECT COUNT(*) FROM stuEnrolmentInfo WHERE student_id = :student_id";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([':student_id' => $student_id]);
            $exists = $check_stmt->fetchColumn();

            if ($exists) {
                // Update existing record
                $enrolment_query = "UPDATE stuEnrolmentInfo SET 
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
                $enrolment_query = "INSERT INTO stuEnrolmentInfo (
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
                'message' => 'An error occurred. Please try again later.'
            ]);
        }
    }
    function deleteClassroom_form(){
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
    function editClassroom_form(){
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

    function getClassroomById($id){
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



    
}
