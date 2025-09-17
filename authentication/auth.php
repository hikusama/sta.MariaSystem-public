<?php
require_once 'config.php';
include "session.php";

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: eror.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // LOGIN PROCESS
    if (isset($_POST['loginAuth']) && $_POST['loginAuth'] === 'true') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $hasCredentials = $username !== '' && $password !== '';

        try {
            if ($hasCredentials) {
                // First, check if it's a user
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_role'] = $user['user_role'];

                    // Log login history
                    $stmt = $pdo->prepare("INSERT INTO users_history (user_id, login_time) VALUES (?, NOW())");
                    $stmt->execute([$user['user_id']]);

                    header("Location: ../src/UI-users/index.php");
                    exit();
                }

                // If not a user, check if it's an admin
                $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_username = ?");
                $stmt->execute([$username]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin['admin_password'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_role'] = $admin['admin_user_role'];

                    // Log login history
                    $stmt = $pdo->prepare("INSERT INTO admin_history (admin_id, login_time) VALUES (?, NOW())");
                    $stmt->execute([$admin['admin_id']]);

                    header("Location: ../src/UI-Admin/index.php");
                    exit();
                }

                // If credentials don't match either
                $_SESSION['errors_login']['login_incorrect'] = 'Incorrect username or password.';
                header("Location: ../src/index.php");
                exit();
            } else {
                $_SESSION['errors_login']['login_incorrect'] = 'Please fill in both fields.';
                header("Location: ../src/index.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $_SESSION['errors_login']['login_incorrect'] = 'System error. Please try again later.';
            header("Location: ../src/index.php");
            exit();
        }
    }

    //USERS MANAGEMENT
    if (isset($_POST['resgiter']) && $_POST['resgiter'] === 'true'){
        try {
            $query = "SELECT username FROM users WHERE username = :username";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            $usernameExist = $stmt->fetchAll(PDO::FETCH_ASSOC);

             if($usernameExist){
                header("Location: ../src/register.php?username=exist");
                exit;
            }

            $query = "SELECT email FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $emailExist = $stmt->fetchAll(PDO::FETCH_ASSOC);

             if($emailExist){
                header("Location: ../src/register.php?email=exist");
                exit;
            }

            if($_POST["password"] != $_POST["cpassword"]){
                header("Location: ../src/register.php?password=notMatch");
                exit;
            }
            if (empty($errors)) {
                $newHashed = password_hash($_POST["password"], PASSWORD_BCRYPT);
                $user_role = "PARENT";
                $query = "INSERT INTO users (firstname, middlename, lastname, suffix, user_role, email, username, password) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    $_POST["firstName"],
                    $_POST["middleName"],
                    $_POST["lastName"],
                    $_POST["suffix"] ?? null,
                    $user_role,
                    $_POST["email"],
                    $_POST["username"],
                    $newHashed
                ]);

                $stmt = null;
                $pdo = null;

                header("Location: ../src/register.php?create=success");
                exit;

            } 

        } catch (PDOException $e) {
            die("Query Failed: " . $e->getMessage());
        }
    }
    // PROFILE MANAGEMENT
    if (isset($_POST['StudentProfile']) && $_POST['StudentProfile'] === 'true') {
        $facultyID  = $_POST["facultyID"] ?? null;
        $lname      = $_POST["lname"] ?? '';
        $fname      = $_POST["fname"] ?? '';
        $mname      = $_POST["mname"] ?? '';
        $suffix     = $_POST["suffix"] ?? '';
        $department = $_POST["department_ID"] ?? '';
        $course     = $_POST["course_id"] ?? '';
        $gender     = $_POST["gender"] ?? '';
        $email      = $_POST["email"] ?? '';
        $contact    = $_POST["contact"] ?? '';
        $birth_date = $_POST["birth_date"] ?? '';
        $profile    = '';

        $errors = [];

        try {
            // Handle profile picture
            if (isset($_FILES["user_profile"]) && $_FILES["user_profile"]["error"] === 0) {
                $upload = $_FILES["user_profile"];
                $target_dir = "uploads/";
                $image_file_name = uniqid() . "-" . basename($upload["name"]);
                $target_file = $target_dir . $image_file_name;

                if (move_uploaded_file($upload["tmp_name"], $target_file)) {
                    $profile = $image_file_name;
                } else {
                    $errors["upload_error"] = "Failed to upload profile image.";
                }
            } else {
                $profile = $_POST["current_profile_image"] ?? '';
            }

            if (empty($errors)) {
                $query = "UPDATE user_data SET 
                            lastname = :lastname,
                            firstname = :firstname,
                            middlename = :middlename,
                            suffix = :suffix,
                            contact = :contact,
                            department_id = :department_id,
                            course_id = :course_id,
                            gender = :gender,
                            email = :email,
                            birth_date = :birth_date,
                            profile_picture = :profile_picture
                        WHERE user_id = :user_id";

                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':user_id', $facultyID);
                $stmt->bindParam(':lastname', $lname);
                $stmt->bindParam(':firstname', $fname);
                $stmt->bindParam(':middlename', $mname);
                $stmt->bindParam(':suffix', $suffix);
                $stmt->bindParam(':contact', $contact);
                $stmt->bindParam(':department_id', $department);
                $stmt->bindParam(':course_id', $course);
                $stmt->bindParam(':gender', $gender);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':birth_date', $birth_date);
                $stmt->bindParam(':profile_picture', $profile);

                $stmt->execute();

                // Optional cleanup
                $stmt = null;
                $pdo = null;

                header("Location: ../src/UI-Student/index.php?page=contents/settings&update=success");
                exit;
            }

        } catch (PDOException $e) {
            die("Query Failed: " . $e->getMessage());
        }
    }
    // PASSWORD MANAGEMENT
    if (isset($_POST['usersForgottenPass']) && $_POST['usersForgottenPass'] === 'true'){
        $Users_id = $_POST["Users_id"];
        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        $query = "SELECT * FROM user_data WHERE User_id = :User_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['User_id' => $Users_id]);
        $successPassword = $stmt->fetch(PDO::FETCH_ASSOC);


        // ==================== EMPTY INPUTS ====================== //
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            if($successPassword["position"] == "STUDENT"){
                 echo json_encode(["status" => "error", "message" => "All fields are required."]);
                header("Location: ../src/UI-Student/index.php?page=contents/settings");
                exit;
            }else if($successPassword["position"] == "FACULTY"){
                 echo json_encode(["status" => "error", "message" => "All fields are required."]);
                header("Location: ../src/UI-Faculty/index.php?page=contents/settings");
                exit;
            }
        }
        // ==================== CONFIRM PASSWORD NOT MATCH ====================== //
        if ($newPassword !== $confirmPassword) {
             if($successPassword["position"] == "STUDENT"){
                echo json_encode(["status" => "error", "message" => "New passwords do not match."]);
                header("Location: ../src/UI-Student/index.php?page=contents/settings&NewPassword=notMatch");
                exit;
             }else if($successPassword["position"] == "FACULTY"){
                echo json_encode(["status" => "error", "message" => "New passwords do not match."]);
                header("Location: ../src/UI-Faculty/index.php?page=contents/settings&NewPassword=notMatch");
                exit;
             }
           
        }

        try {
            $Users_id = $Users_id ?? '';
            $stmt = $pdo->prepare("SELECT password FROM user_data WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $Users_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$Users_id) {
                echo json_encode(["status" => "error", "message" => "user not found."]);
                exit;
            }

            // ==================== CURRENT PASSWORD NOT MATCH ====================== //
            if (!password_verify($currentPassword, $user['password'])) {
                if($successPassword["position"] == "STUDENT"){
                    echo json_encode(["status" => "error", "message" => "Current password is incorrect."]);
                    header("Location: ../src/UI-Student/index.php?page=contents/settings&CurrentPasswoed=notMatch");
                    exit;
                }else if($successPassword["position"] == "FACULTY"){
                    echo json_encode(["status" => "error", "message" => "Current password is incorrect."]);
                    header("Location: ../src/UI-Faculty/index.php?page=contents/settings&CurrentPasswoed=notMatch");
                    exit;
                }
                
            }

            $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $pdo->prepare("UPDATE user_data SET password = :password WHERE User_id = :User_id");
            $updateSuccess = $updateStmt->execute([
                'password' => $newHashed,
                'User_id' => $Users_id
            ]);

            // ==================== PASSWORD CHANGE SUCCESSFULLY ====================== //
            if ($updateSuccess) {
                if($successPassword["position"] == "STUDENT"){
                     echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
                    header("Location: ../src/UI-Student/index.php?page=contents/settings&passwordChange=success");
                    exit;
                }else if($successPassword["position"] == "FACULTY"){
                    echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
                    header("Location: ../src/UI-Faculty/index.php?page=contents/settings&passwordChange=success");
                    exit;
                }
               
            } else {
                // ================== PASWORD FAILED TO CHANGE ====================== //
                if($successPassword["position"] == "STUDENT"){
                     echo json_encode(["status" => "error", "message" => "Failed to update password."]);
                    header("Location: ../src/UI-Student/index.php?page=contents/settings&password=failedsss");
                    exit;
                 }else if($successPassword["position"] == "FACULTY"){
                     echo json_encode(["status" => "error", "message" => "Failed to update password."]);
                    header("Location: ../src/UI-Faculty/index.php?page=contents/settings&password=failedsss");
                    exit;
                 }
            }

        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
            header("Location: ../src/UI-Student/index.php?page=contents/settings&CurrentPasswoed=failedasdasdasd");
                exit;
        }
    }
    if (isset($_POST['LogoutAdmin']) && $_POST['LogoutAdmin'] === 'true') {
        $adminID = $_POST["adminID"];
        try {
            $query = "INSERT INTO admin_history (admin_id, login_time) VALUES ('$adminID', NOW());";
            $stmt = $pdo->prepare($query);
            $stmt->execute();

            header('Location: ../index.php');
            
        } catch (PDOException $e) {
            die('Query Failed: ' . $e->getMessage());
        }
    }
    // if (isset($_POST['adminAccReg']) && $_POST['adminAccReg'] === 'true') {
    //     $lastName = $_POST["lastName"];
    //     $firstName = $_POST["firstName"];
    //     $middleName = $_POST["middleName"];
    //     $suffix = $_POST["suffix"] ?? '';
    //     $user_role = $_POST["user_role"];
    //     $gender = $_POST["gender"];
    //     $email = $_POST["email"];
    //     $contact = $_POST["contact"];
    //     $username = $_POST["username"];
    //     $password = $_POST["password"];
    //     $cpassword = $_POST["cpassword"];
    //     try {
    //         $stmt = $pdo->prepare("SELECT username FROM users WHERE username = '$username';"); $stmt->execute();
    //         $usernameTaken = $stmt->fetch(PDO::FETCH_ASSOC);
            
    //         if($usernameTaken){
    //             header('Location: ../src/UI-Admin/index.php?page=contents/users&username=taken');
    //             die();
    //         }

    //         $hasedPassword = password_hash($password, PASSWORD_BCRYPT);
    //         $query = "INSERT INTO users (firstname, middlename, lastname, suffix, email, username, password, user_role) 
    //             VALUES ('$firstName', '$middleName', '$lastName', '$suffix', '$email', '$username', '$hasedPassword', '$user_role')";
    //         $stmt = $pdo->prepare($query);
    //         $stmt->execute();

    //         header('Location: ../src/UI-Admin/index.php?page=contents/users&registration=success');
    //         die();

    //     } catch (PDOException $e) {
    //         die('Query Failed: ' . $e->getMessage());
    //     }
    // }
    unset($_SESSION['csrf_token']);
}