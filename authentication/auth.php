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
                    $user_role = $user['user_role'];

                    if ($user_role == 'PARENT') {
                        $stmt = $pdo->prepare("INSERT INTO users_history (user_id, login_time) VALUES (?, NOW())");
                        $stmt->execute([$user['user_id']]);

                        header("Location: ../src/UI-parents/index.php");
                        exit();
                    } else if ($user_role == 'TEACHER') {
                        $stmt = $pdo->prepare("INSERT INTO users_history (user_id, login_time) VALUES (?, NOW())");
                        $stmt->execute([$user['user_id']]);

                        header("Location: ../src/UI-teacher/index.php");
                        exit();
                    }
                    // Log login history

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
                header("Location: ../src/index.php?incorrect=login");
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
    if (isset($_POST['resgiter']) && $_POST['resgiter'] === 'true') {
        $username = trim($_POST["username"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $errors = [];

        try {
            // Check username
            $query = "SELECT username FROM users WHERE username = :username";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':username' => $username]);
            $usernameExist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usernameExist) {
                header("Location: ../src/register.php?username=exist");
                exit;
            }

            // Get active school year
            $stmtCheckSY = $pdo->prepare("SELECT * FROM school_year WHERE school_year_status = 'Active' LIMIT 1");
            $stmtCheckSY->execute();
            $activeSY = $stmtCheckSY->fetch(PDO::FETCH_ASSOC);

            if (!$activeSY) {
                header("Location: ../src/register.php?noActiveSchoolYear=1");
                exit;
            }

            // Check email
            $query = "SELECT email FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':email' => $email]);
            $emailExist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($emailExist) {
                header("Location: ../src/register.php?email=exist");
                exit;
            }

            // Password match
            if (($_POST["password"] ?? '') !== ($_POST["cpassword"] ?? '')) {
                header("Location: ../src/register.php?password=notMatch");
                exit;
            }

            if (empty($errors)) {
                $newHashed = password_hash($_POST["password"], PASSWORD_BCRYPT);
                $user_role = "PARENT";
                $query = "INSERT INTO users (school_year_id, firstname, middlename, lastname, suffix, user_role, email, relationship, username, password) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    $activeSY["school_year_id"],
                    $_POST["firstName"],
                    $_POST["middleName"],
                    $_POST["lastName"],
                    $_POST["suffix"] ?? null,
                    $user_role,
                    $email,
                    $_POST["relationship"],
                    $username,
                    $newHashed
                ]);

                $stmt = null;
                $pdo = null;

                header("Location: ../src/register.php?create=success");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            header("Location: ../src/register.php?error=server");
            exit;
        }
    }
    // PROFILE MANAGEMENT
    if (isset($_POST['parentSettings']) && $_POST['parentSettings'] === 'true') {
        $user_id  = $_POST["user_id"] ?? null;
        $firstname = $_POST["firstname"] ?? '';
        $lastname  = $_POST["lastname"] ?? '';
        $middlename = $_POST["middlename"] ?? '';
        $suffix    = $_POST["suffix"] ?? '';
        $email     = $_POST["email"] ?? '';
        $profile   = '';

        // CSRF protection
        if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            die("CSRF token validation failed.");
        }

        $errors = [];

        try {
            // Handle profile picture upload
            if (isset($_FILES["student_profile"]) && $_FILES["student_profile"]["error"] === UPLOAD_ERR_OK) {
                $upload = $_FILES["student_profile"];

                // Validate file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $upload["tmp_name"]);
                finfo_close($file_info);

                if (!in_array($mime_type, $allowed_types)) {
                    $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.";
                }

                // Validate file size (max 2MB)
                $max_size = 2 * 1024 * 1024;
                if ($upload["size"] > $max_size) {
                    $errors[] = "File size too large. Maximum size is 2MB.";
                }

                // Create upload directory if it doesn't exist
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                // Generate unique filename
                $file_extension = pathinfo($upload["name"], PATHINFO_EXTENSION);
                $image_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $target_file = $target_dir . $image_file_name;

                // Check if file is a valid image
                $image_info = getimagesize($upload["tmp_name"]);
                if (!$image_info) {
                    $errors[] = "File is not a valid image.";
                }

                if (empty($errors)) {
                    // Move uploaded file
                    if (move_uploaded_file($upload["tmp_name"], $target_file)) {
                        // Get old profile picture to delete
                        $stmt_old = $pdo->prepare("SELECT student_profile FROM users WHERE user_id = :user_id");
                        $stmt_old->execute([':user_id' => $user_id]);
                        $old_profile = $stmt_old->fetchColumn();

                        // Delete old profile picture if exists and not default
                        if ($old_profile && $old_profile !== '' && !str_starts_with($old_profile, 'default_')) {
                            $old_file = $target_dir . $old_profile;
                            if (file_exists($old_file)) {
                                @unlink($old_file);
                            }
                        }

                        $profile = $image_file_name;
                    } else {
                        $errors[] = "Failed to upload profile image.";
                    }
                }
            } elseif (isset($_POST["current_profile_image"])) {
                $profile = $_POST["current_profile_image"];
            }

            if (empty($errors)) {
                // Prepare SQL query
                $query = "UPDATE users SET 
                            firstname = :firstname,
                            lastname = :lastname,
                            middlename = :middlename,
                            suffix = :suffix,
                            email = :email";

                // Add profile column only if we have a new profile
                if (!empty($profile)) {
                    $query .= ", student_profile = :student_profile";
                }

                $query .= " WHERE user_id = :user_id";

                $stmt = $pdo->prepare($query);

                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':firstname', $firstname);
                $stmt->bindParam(':lastname', $lastname);
                $stmt->bindParam(':middlename', $middlename);
                $stmt->bindParam(':suffix', $suffix);
                $stmt->bindParam(':email', $email);

                if (!empty($profile)) {
                    $stmt->bindParam(':student_profile', $profile);
                }

                $stmt->execute();

                // Clear old password reset tokens for this user (optional security measure)
                // $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = :user_id")->execute([':user_id' => $user_id]);

                // Regenerate CSRF token
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                if ($stmt->rowCount() > 0) {
                    $_SESSION['success_message'] = 'Profile updated successfully!';
                } else {
                    $_SESSION['info_message'] = 'No changes were made to your profile.';
                }

                header("Location: ../src/UI-parents/index.php?page=contents/settings&update=success");
                exit;
            } else {
                // Store errors in session to display on redirect
                $_SESSION['error_messages'] = $errors;
                header("Location: ../src/UI-parents/index.php?page=contents/settings&update=error");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Profile Update Error: " . $e->getMessage());
            $_SESSION['error_messages'] = ['A database error occurred. Please try again.'];
            header("Location: ../src/UI-parents/index.php?page=contents/settings&update=error");
            exit;
        }
    }
    if (isset($_POST['teacherSettings']) && $_POST['teacherSettings'] === 'true') {
        $user_id  = $_POST["user_id"] ?? null;
        $firstname = $_POST["firstname"] ?? '';
        $lastname  = $_POST["lastname"] ?? '';
        $middlename = $_POST["middlename"] ?? '';
        $suffix    = $_POST["suffix"] ?? '';
        $email     = $_POST["email"] ?? '';
        $profile   = '';

        // CSRF protection
        if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            die("CSRF token validation failed.");
        }

        $errors = [];

        try {
            // Handle profile picture upload
            if (isset($_FILES["student_profile"]) && $_FILES["student_profile"]["error"] === UPLOAD_ERR_OK) {
                $upload = $_FILES["student_profile"];

                // Validate file type
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $upload["tmp_name"]);
                finfo_close($file_info);

                if (!in_array($mime_type, $allowed_types)) {
                    $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.";
                }

                // Validate file size (max 2MB)
                $max_size = 2 * 1024 * 1024;
                if ($upload["size"] > $max_size) {
                    $errors[] = "File size too large. Maximum size is 2MB.";
                }

                // Create upload directory if it doesn't exist
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                // Generate unique filename
                $file_extension = pathinfo($upload["name"], PATHINFO_EXTENSION);
                $image_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $target_file = $target_dir . $image_file_name;

                // Check if file is a valid image
                $image_info = getimagesize($upload["tmp_name"]);
                if (!$image_info) {
                    $errors[] = "File is not a valid image.";
                }

                if (empty($errors)) {
                    // Move uploaded file
                    if (move_uploaded_file($upload["tmp_name"], $target_file)) {
                        // Get old profile picture to delete
                        $stmt_old = $pdo->prepare("SELECT student_profile FROM users WHERE user_id = :user_id");
                        $stmt_old->execute([':user_id' => $user_id]);
                        $old_profile = $stmt_old->fetchColumn();

                        // Delete old profile picture if exists and not default
                        if ($old_profile && $old_profile !== '' && !str_starts_with($old_profile, 'default_')) {
                            $old_file = $target_dir . $old_profile;
                            if (file_exists($old_file)) {
                                @unlink($old_file);
                            }
                        }

                        $profile = $image_file_name;
                    } else {
                        $errors[] = "Failed to upload profile image.";
                    }
                }
            } elseif (isset($_POST["current_profile_image"])) {
                $profile = $_POST["current_profile_image"];
            }

            if (empty($errors)) {
                // Prepare SQL query
                $query = "UPDATE users SET 
                            firstname = :firstname,
                            lastname = :lastname,
                            middlename = :middlename,
                            suffix = :suffix,
                            email = :email";

                // Add profile column only if we have a new profile
                if (!empty($profile)) {
                    $query .= ", student_profile = :student_profile";
                }

                $query .= " WHERE user_id = :user_id";

                $stmt = $pdo->prepare($query);

                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':firstname', $firstname);
                $stmt->bindParam(':lastname', $lastname);
                $stmt->bindParam(':middlename', $middlename);
                $stmt->bindParam(':suffix', $suffix);
                $stmt->bindParam(':email', $email);

                if (!empty($profile)) {
                    $stmt->bindParam(':student_profile', $profile);
                }

                $stmt->execute();

                // Clear old password reset tokens for this user (optional security measure)
                // $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = :user_id")->execute([':user_id' => $user_id]);

                // Regenerate CSRF token
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                if ($stmt->rowCount() > 0) {
                    $_SESSION['success_message'] = 'Profile updated successfully!';
                } else {
                    $_SESSION['info_message'] = 'No changes were made to your profile.';
                }

                header("Location: ../src/UI-teacher/index.php?page=contents/settings&update=success");
                exit;
            } else {
                // Store errors in session to display on redirect
                $_SESSION['error_messages'] = $errors;
                header("Location: ../src/UI-teacher/index.php?page=contents/settings&update=error");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Profile Update Error: " . $e->getMessage());
            $_SESSION['error_messages'] = ['A database error occurred. Please try again.'];
            header("Location: ../src/UI-teacher/index.php?page=contents/settings&update=error");
            exit;
        }
    }
    if (isset($_POST['adminProfile']) && $_POST['adminProfile'] === 'true') {
        $adminID  = $_POST["adminID"] ?? null;
        $admin_lastname      = $_POST["admin_lastname"] ?? '';
        $admin_firstname      = $_POST["admin_firstname"] ?? '';
        $admin_middlename      = $_POST["admin_middlename"] ?? '';
        $admin_suffix     = $_POST["admin_suffix"] ?? '';
        $admin_email      = $_POST["admin_email"] ?? '';
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
                $query = "UPDATE admin SET 
                            admin_lastname = :admin_lastname,
                            admin_firstname = :admin_firstname,
                            admin_middlename = :admin_middlename,
                            admin_suffix = :admin_suffix,
                            admin_email = :admin_email,
                            admin_picture = :admin_picture
                        WHERE admin_id = :admin_id";

                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':admin_id', $adminID);
                $stmt->bindParam(':admin_lastname', $admin_lastname);
                $stmt->bindParam(':admin_firstname', $admin_firstname);
                $stmt->bindParam(':admin_middlename', $admin_middlename);
                $stmt->bindParam(':admin_suffix', $admin_suffix);
                $stmt->bindParam(':admin_email', $admin_email);
                $stmt->bindParam(':admin_picture', $profile);

                $stmt->execute();

                // Optional cleanup
                $stmt = null;
                $pdo = null;

                header("Location: ../src/UI-Admin/index.php?page=contents/settings&update=success");
                exit;
            }
        } catch (PDOException $e) {
            die("Query Failed: " . $e->getMessage());
        }
    }
    // PASSWORD MANAGEMENT
    if (isset($_POST['usersForgottenPass']) && $_POST['usersForgottenPass'] === 'true') {
        $Users_id = $_POST["Users_id"] ?? '';
        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";

        try {
            // ==================== FETCH USER ====================== //
            $query = "SELECT * FROM users WHERE user_id = :user_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['user_id' => $Users_id]);
            $successPassword = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$successPassword) {
                header("Location: ../src/UI-parents/index.php?page=contents/settings&error=userNotFound");
                exit;
            }

            $role = $successPassword["user_role"];

            // ==================== EMPTY INPUTS ====================== //
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                if ($role == "PARENT") {
                    header("Location: ../src/UI-parents/index.php?page=contents/settings&error=emptyFields");
                    exit;
                } else if ($role == "TEACHER") {
                    header("Location: ../src/UI-teacher/index.php?page=contents/settings&error=emptyFields");
                    exit;
                }
            }

            // ==================== CONFIRM PASSWORD NOT MATCH ====================== //
            if ($newPassword !== $confirmPassword) {
                if ($role == "PARENT") {
                    header("Location: ../src/UI-parents/index.php?page=contents/settings&NewPassword=notMatch");
                    exit;
                } else if ($role == "TEACHER") {
                    header("Location: ../src/UI-teacher/index.php?page=contents/settings&NewPassword=notMatch");
                    exit;
                }
            }

            // ==================== VERIFY CURRENT PASSWORD ====================== //
            if (!password_verify($currentPassword, $successPassword['password'])) {
                if ($role == "PARENT") {
                    header("Location: ../src/UI-parents/index.php?page=contents/settings&error=wrongCurrent");
                    exit;
                } else if ($role == "TEACHER") {
                    header("Location: ../src/UI-teacher/index.php?page=contents/settings&error=wrongCurrent");
                    exit;
                }
            }

            // ==================== UPDATE PASSWORD ====================== //
            $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
            $updateSuccess = $updateStmt->execute([
                'password' => $newHashed,
                'user_id' => $Users_id
            ]);

            if ($updateSuccess) {
                if ($role == "PARENT") {
                    header("Location: ../src/UI-parents/index.php?page=contents/settings&passwordChange=success");
                    exit;
                } else if ($role == "TEACHER") {
                    header("Location: ../src/UI-teacher/index.php?page=contents/settings&passwordChange=success");
                    exit;
                }
            } else {
                if ($role == "PARENT") {
                    header("Location: ../src/UI-parents/index.php?page=contents/settings&error=updateFailed");
                    exit;
                } else if ($role == "TEACHER") {
                    header("Location: ../src/UI-teacher/index.php?page=contents/settings&error=updateFailed");
                    exit;
                }
            }
        } catch (PDOException $e) {
            if (isset($role) && $role == "PARENT") {
                header("Location: ../src/UI-parents/index.php?page=contents/settings&error=dbError");
                exit;
            } else if (isset($role) && $role == "TEACHER") {
                header("Location: ../src/UI-teacher/index.php?page=contents/settings&error=dbError");
                exit;
            } else {
                // fallback redirect
                header("Location: ../src/UI-Student/index.php?page=contents/settings&error=dbError");
                exit;
            }
        }
    }
    if (isset($_POST['usersForgottenPassAdmin']) && $_POST['usersForgottenPassAdmin'] === 'true') {
        $Users_id = $_POST["Users_id"];
        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";



        // ==================== EMPTY INPUTS ====================== //
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            header("Location: ../src/UI-Admin/index.php?page=contents/settings");
            exit;
        }
        // ==================== CONFIRM PASSWORD NOT MATCH ====================== //
        if ($newPassword !== $confirmPassword) {
            echo json_encode(["status" => "error", "message" => "New passwords do not match."]);
            header("Location: ../src/UI-Admin/index.php?page=contents/settings&NewPassword=notMatch");
            exit;
        }

        try {
            $Users_id = $Users_id ?? '';
            $stmt = $pdo->prepare("SELECT admin_password FROM admin WHERE admin_id  = :admin_id ");
            $stmt->execute(['admin_id' => $Users_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(["status" => "error", "message" => "user not found."]);
                exit;
            }

            // ==================== CURRENT PASSWORD NOT MATCH ====================== //
            if (!password_verify($currentPassword, $user['admin_password'])) {
                echo json_encode(["status" => "error", "message" => "Current password is incorrect."]);
                header("Location: ../src/UI-Admin/index.php?page=contents/settings&CurrentPasswoed=notMatch");
                exit;
            }

            $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $pdo->prepare("UPDATE admin SET admin_password = :admin_password WHERE admin_id  = :admin_id ");
            $updateSuccess = $updateStmt->execute([
                'admin_password' => $newHashed,
                'admin_id' => $Users_id
            ]);
            header("Location: ../src/UI-Admin/index.php?page=contents/settings&passwordChange=success");
            exit;
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
            // header("Location: ../src/UI-Admin/index.php?page=contents/settings&CurrentPasswoed=failedasdasdasd");
            exit;
        }
    }
    if (isset($_POST['LogoutAdmin']) && $_POST['LogoutAdmin'] === 'true') {
        $adminID = $_POST["adminID"];
        try {
            $query = "INSERT INTO admin_history (admin_id, login_time) VALUES (?, NOW());";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$adminID]);
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_unset();
            session_destroy();
            header('Location: ../index.php');
        } catch (PDOException $e) {
            die('Query Failed: ' . $e->getMessage());
        }
    }
    if (isset($_POST['LogoutUser']) && $_POST['LogoutUser'] === 'true') {
        $user_id = $_POST["user_id"];
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_unset();
            session_destroy();
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
