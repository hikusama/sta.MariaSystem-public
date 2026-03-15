<?php
require_once 'config.php';
include "session.php";

// Sanitize and trim all incoming POST data
foreach ($_POST as $key => $value) {
    if (is_string($value)) {
        $_POST[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: eror.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // LOGIN PROCESS
    if (isset($_POST['loginAuth']) && $_POST['loginAuth'] === 'true') {
        $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
        $password = trim($_POST['password'] ?? '');  // Don't sanitize password, only trim

        $hasCredentials = $username !== '' && $password !== '';

        // ==================== INPUT VALIDATION ====================== //
        if (!$hasCredentials) {
            header("Location: ../src/index.php?validation=failed");
            exit;
        }

        try {
            $recaptcha_secret = '6LdSd4csAAAAAL31gtAH7xkNO0fq10rzZuY5Oegc';
            $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

            if (empty($recaptcha_response)) {
                header("Location: ../src/index.php?validation=failed");
                exit;
            }

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ]
            ];
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result_json = json_decode($result);

            if (!($result_json->success)) {
                header("Location: ../src/index.php?recaptcha=failed");
                exit;
            }

            $acc = $pdo->prepare("SELECT accessible_to FROM accessibility WHERE id = 1");
            $acc->execute();
            $accesible_to = $acc->fetch(PDO::FETCH_ASSOC);
            if (!$accesible_to) {
                $pdo->prepare("INSERT INTO accessibility (id, accessible_to) VALUES (1, 'allusers')")->execute();
            }
            $ac = $accesible_to['accessible_to'];

            // First, check if it's a user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $user_role = $user['user_role'];

                if ($user_role == 'PARENT') {
                    $stmt->execute([$user['user_id']]);
                    if ($ac !== 'allusers') {
                        header("Location: ../src/index.php?restricted=1A");
                        exit;
                    }
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_role'] = $user['user_role'];
                    $stmt = $pdo->prepare("INSERT INTO users_history (user_id, login_time) VALUES (?, NOW())");

                    header("Location: ../src/UI-parents/index.php");
                    exit();
                } else if ($user_role == 'TEACHER') {
                    $stmt->execute([$user['user_id']]);
                    if ($ac === 'onlyadmin') {
                        header("Location: ../src/index.php?restricted=12");
                        exit;
                    }
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_role'] = $user['user_role'];
                    $stmt = $pdo->prepare("INSERT INTO users_history (user_id, login_time) VALUES (?, NOW())");
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
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $_SESSION['errors_login']['login_incorrect'] = 'System error. Please try again later.';
            header("Location: ../src/index.php");
            exit();
        }
    }

    //USERS MANAGEMENT
    if (isset($_POST['resgiter']) && $_POST['resgiter'] === 'true') {
        $username = htmlspecialchars(trim($_POST["username"] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
        $firstName = htmlspecialchars(trim($_POST["firstName"] ?? ''), ENT_QUOTES, 'UTF-8');
        $middleName = htmlspecialchars(trim($_POST["middleName"] ?? ''), ENT_QUOTES, 'UTF-8');
        $lastName = htmlspecialchars(trim($_POST["lastName"] ?? ''), ENT_QUOTES, 'UTF-8');
        $suffix = htmlspecialchars(trim($_POST["suffix"] ?? ''), ENT_QUOTES, 'UTF-8');
        $relationship = htmlspecialchars(trim($_POST["relationship"] ?? ''), ENT_QUOTES, 'UTF-8');
        $contact = htmlspecialchars(trim($_POST["contact"] ?? ''), ENT_QUOTES, 'UTF-8');
        $password = trim($_POST["password"] ?? '');
        $cpassword = trim($_POST["cpassword"] ?? '');
        $errors = [];

        // ==================== INPUT VALIDATION ====================== //
        if (empty($firstName)) {
            header("Location: ../src/register.php?validation=failed1");
            exit;
        }
        if (empty($lastName)) {
            header("Location: ../src/register.php?validation=failed2");
            exit;
        }
        if (empty($username) || strlen($username) < 3) {
            header("Location: ../src/register.php?unlen=failed3");
            exit;
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../src/register.php?validation=failed4");
            exit;
        }
        if (empty($password) || strlen($password) < 6) {
            header("Location: ../src/register.php?pwlen=failed5");
            exit;
        }
        if (empty($cpassword)) {
            header("Location: ../src/register.php?validation=failed6");
            exit;
        }
        if (empty($relationship)) {
            header("Location: ../src/register.php?validation=failed7");
            exit;
        }
        if (empty($contact)) {
            header("Location: ../src/register.php?validation=failed8");
            exit;
        }

        try {
            $recaptcha_secret = '6LdSd4csAAAAAL31gtAH7xkNO0fq10rzZuY5Oegc';
            $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

            if (empty($recaptcha_response)) {
                header("Location: ../src/register.php?validation=failed");
                exit;
            }

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ]
            ];
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result_json = json_decode($result);

            if (!($result_json->success)) {
                header("Location: ../src/register.php?recaptcha=failed");
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
            // Check username in both users and admin tables
            $query = "SELECT username FROM users WHERE username = :username 
                      UNION 
                      SELECT admin_username as username FROM admin WHERE admin_username = :username";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':username' => $username]);
            $usernameExist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usernameExist) {
                header("Location: ../src/register.php?username=exist");
                exit;
            }

            // Check email in both users and admin tables
            $query = "SELECT email FROM users WHERE email = :email 
                      UNION 
                      SELECT admin_email as email FROM admin WHERE admin_email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':email' => $email]);
            $emailExist = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($emailExist) {
                header("Location: ../src/register.php?email=exist");
                exit;
            }

            // Password match
            $password = trim($_POST["password"] ?? '');
            $cpassword = trim($_POST["cpassword"] ?? '');
            
            if ($password !== $cpassword) {
                header("Location: ../src/register.php?password=notMatch");
                exit;
            }

            if (empty($errors)) {
                $newHashed = password_hash($password, PASSWORD_BCRYPT);
                $user_role = "PARENT";
                $firstName = htmlspecialchars(trim($_POST["firstName"] ?? ''), ENT_QUOTES, 'UTF-8');
                $contact = htmlspecialchars(trim($_POST["contact"] ?? ''), ENT_QUOTES, 'UTF-8');
                $middleName = htmlspecialchars(trim($_POST["middleName"] ?? ''), ENT_QUOTES, 'UTF-8');
                $lastName = htmlspecialchars(trim($_POST["lastName"] ?? ''), ENT_QUOTES, 'UTF-8');
                $suffix = htmlspecialchars(trim($_POST["suffix"] ?? ''), ENT_QUOTES, 'UTF-8');
                $relationship = htmlspecialchars(trim($_POST["relationship"] ?? ''), ENT_QUOTES, 'UTF-8');
                
                $query = "INSERT INTO users (firstname, middlename, lastname, suffix, user_role, email, relationship, contact, username, password) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    $firstName,
                    $middleName,
                    $lastName,
                    $suffix,
                    $user_role,
                    $email,
                    $relationship,
                    $contact,
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
            // header("Location: ../src/register.php?error=server");
            var_dump($e->getMessage());
            exit;
        }
    }
    // PROFILE MANAGEMENT
    if (isset($_POST['parentSettings']) && $_POST['parentSettings'] === 'true') {
        $user_id  = htmlspecialchars(trim($_POST["user_id"] ?? ''), ENT_QUOTES, 'UTF-8');
        $firstname = htmlspecialchars(trim($_POST["firstname"] ?? ''), ENT_QUOTES, 'UTF-8');
        $lastname  = htmlspecialchars(trim($_POST["lastname"] ?? ''), ENT_QUOTES, 'UTF-8');
        $middlename = htmlspecialchars(trim($_POST["middlename"] ?? ''), ENT_QUOTES, 'UTF-8');
        $suffix    = htmlspecialchars(trim($_POST["suffix"] ?? ''), ENT_QUOTES, 'UTF-8');
        $email     = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
        $profile   = '';

        // ==================== INPUT VALIDATION ====================== //
        if (empty($user_id) || empty($firstname) || empty($lastname) || empty($email)) {
            header("Location: ../src/UI-parents/index.php?page=contents/settings&validation=failed");
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../src/UI-parents/index.php?page=contents/settings&validation=failed");
            exit;
        }

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
        $user_id  = htmlspecialchars(trim($_POST["user_id"] ?? ''), ENT_QUOTES, 'UTF-8');
        $firstname = htmlspecialchars(trim($_POST["firstname"] ?? ''), ENT_QUOTES, 'UTF-8');
        $lastname  = htmlspecialchars(trim($_POST["lastname"] ?? ''), ENT_QUOTES, 'UTF-8');
        $middlename = htmlspecialchars(trim($_POST["middlename"] ?? ''), ENT_QUOTES, 'UTF-8');
        $suffix    = htmlspecialchars(trim($_POST["suffix"] ?? ''), ENT_QUOTES, 'UTF-8');
        $email     = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
        $profile   = '';

        // ==================== INPUT VALIDATION ====================== //
        if (empty($user_id) || empty($firstname) || empty($lastname) || empty($email)) {
            header("Location: ../src/UI-teacher/index.php?page=contents/settings&validation=failed");
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../src/UI-teacher/index.php?page=contents/settings&validation=failed");
            exit;
        }

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
        $adminID  = htmlspecialchars(trim($_POST["adminID"] ?? ''), ENT_QUOTES, 'UTF-8');
        $admin_lastname      = htmlspecialchars(trim($_POST["admin_lastname"] ?? ''), ENT_QUOTES, 'UTF-8');
        $admin_firstname      = htmlspecialchars(trim($_POST["admin_firstname"] ?? ''), ENT_QUOTES, 'UTF-8');
        $admin_middlename      = htmlspecialchars(trim($_POST["admin_middlename"] ?? ''), ENT_QUOTES, 'UTF-8');
        $admin_suffix     = htmlspecialchars(trim($_POST["admin_suffix"] ?? ''), ENT_QUOTES, 'UTF-8');
        $admin_email      = filter_var(trim($_POST["admin_email"] ?? ''), FILTER_SANITIZE_EMAIL);
        $profile    = '';

        // ==================== INPUT VALIDATION ====================== //
        if (empty($adminID) || empty($admin_firstname) || empty($admin_lastname) || empty($admin_email)) {
            header("Location: ../src/UI-Admin/index.php?page=contents/settings&validation=failed");
            exit;
        }
        if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ../src/UI-Admin/index.php?page=contents/settings&validation=failed");
            exit;
        }

        // CSRF protection
        if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            die("CSRF token validation failed.");
        }

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
            } else {
                $_SESSION['error_messages'] = $errors;
                header("Location: ../src/UI-Admin/index.php?page=contents/settings&update=error");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Admin profile update error: " . $e->getMessage());
            header("Location: ../src/UI-Admin/index.php?page=contents/settings&error=dbError");
            exit;
        }
    }
    // PASSWORD MANAGEMENT
    if (isset($_POST['usersForgottenPass']) && $_POST['usersForgottenPass'] === 'true') {
        $Users_id = htmlspecialchars(trim($_POST["Users_id"] ?? ''), ENT_QUOTES, 'UTF-8');
        $currentPassword = trim($_POST["current_password"] ?? "");
        $newPassword = trim($_POST["new_password"] ?? "");
        $confirmPassword = trim($_POST["confirm_password"] ?? "");

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
        $Users_id = htmlspecialchars(trim($_POST["Users_id"] ?? ''), ENT_QUOTES, 'UTF-8');
        $currentPassword = trim($_POST["current_password"] ?? "");
        $newPassword = trim($_POST["new_password"] ?? "");
        $confirmPassword = trim($_POST["confirm_password"] ?? "");

        try {
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
            
            if ($updateSuccess) {
                header("Location: ../src/UI-Admin/index.php?page=contents/settings&passwordChange=success");
                exit;
            } else {
                header("Location: ../src/UI-Admin/index.php?page=contents/settings&error=updateFailed");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Admin password change error: " . $e->getMessage());
            echo json_encode(["status" => "error", "message" => "Database error occurred"]);
            header("Location: ../src/UI-Admin/index.php?page=contents/settings&error=dbError");
            exit;
        }
    }
    if (isset($_POST['LogoutAdmin']) && $_POST['LogoutAdmin'] === 'true') {
        $adminID = htmlspecialchars(trim($_POST["adminID"] ?? ''), ENT_QUOTES, 'UTF-8');
        try {
            if (empty($adminID)) {
                session_unset();
                session_destroy();
                header('Location: ../index.php');
                exit;
            }
            
            $query = "INSERT INTO admin_history (admin_id, login_time) VALUES (?, NOW());";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$adminID]);
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_unset();
            session_destroy();
            header('Location: ../index.php');
            exit;
        } catch (PDOException $e) {
            error_log('Admin logout error: ' . $e->getMessage());
            session_unset();
            session_destroy();
            header('Location: ../index.php?error=logout');
            exit;
        }
    }
    if (isset($_POST['LogoutUser']) && $_POST['LogoutUser'] === 'true') {
        $user_id = htmlspecialchars(trim($_POST["user_id"] ?? ''), ENT_QUOTES, 'UTF-8');
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_unset();
            session_destroy();
            header('Location: ../index.php');
            exit;
        } catch (Exception $e) {
            error_log('User logout error: ' . $e->getMessage());
            session_unset();
            session_destroy();
            header('Location: ../index.php?error=logout');
            exit;
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
