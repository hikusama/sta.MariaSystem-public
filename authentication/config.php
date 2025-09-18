<?php

function db_connect()
{
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'staMariaDb';

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tableQueries = [
            "CREATE TABLE IF NOT EXISTS admin (
                admin_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                admin_firstname VARCHAR(50) NOT NULL,
                admin_middlename VARCHAR(50) NOT NULL,
                admin_lastname VARCHAR(50) NOT NULL,
                admin_suffix VARCHAR(5) NOT NULL,
                admin_email VARCHAR(100) NOT NULL,
                admin_username VARCHAR(50) NOT NULL,
                admin_password VARCHAR(255) NOT NULL,
                admin_user_role VARCHAR(20) NOT NULL,
                admin_picture VARCHAR(255) NOT NULL,
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS admin_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                login_time DATETIME NOT NULL,
                logout_time DATETIME DEFAULT NULL,
                FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS users (
                user_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                firstname VARCHAR(50) NOT NULL,
                middlename VARCHAR(50) NOT NULL,
                lastname VARCHAR(50) NOT NULL,
                suffix VARCHAR(5),
                email VARCHAR(100) NOT NULL,
                contact VARCHAR(14) NOT NULL,
                gender ENUM('MALE', 'FEMALE'),
                status ENUM('Active','Inactive'),
                username VARCHAR(50) NOT NULL,
                password VARCHAR(255) NOT NULL,
                user_role ENUM('PARENT', 'TEACHER'),
                relationship ENUM('Father', 'Mother', 'Guardian'),
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
             "CREATE TABLE IF NOT EXISTS users_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                login_time DATETIME NOT NULL,
                logout_time DATETIME DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS sections (
                section_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                section_name VARCHAR(50) NOT NULL,
                section_grade_level VARCHAR(7) NOT NULL,
                section_status ENUM('Available', 'Inavailable'),
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS Subjects (
                subject_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                grade_level VARCHAR(7) NOT NULL,
                subject_name VARCHAR(50) NOT NULL,
                subject_code VARCHAR(20) NOT NULL,
                subject_units INT(11) NOT NULL,
                subjects_status ENUM('Available', 'Unavailable') DEFAULT 'Available',
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

            )",
            "CREATE TABLE IF NOT EXISTS school_year (
                school_year_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                school_year_status ENUM('Active', 'Inactive'),
                school_year_name VARCHAR(50) NOT NULL,
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS classrooms (
                room_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                room_status  ENUM('Unavailable', 'Available') DEFAULT 'Available',
                room_name VARCHAR(50) NOT NULL,
                room_type VARCHAR(50) NOT NULL,
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS classes (
                class_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                section_id INT(11) NOT NULL,
                adviser_id INT(11) NOT NULL,
                sy_id INT(11) NOT NULL,
                section_name VARCHAR(20) NOT NULL,
                grade_level VARCHAR(10) NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
                FOREIGN KEY (adviser_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (sy_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS student (
                student_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                guardian_id INT(11),
                enrolment_status ENUM('active', 'transferred', 'dropped', 'pending') DEFAULT 'pending',
                lrn VARCHAR(12) NOT NULL,
                fname VARCHAR(150) NOT NULL,
                mname VARCHAR(150) NOT NULL,
                lname VARCHAR(150) NOT NULL,
                suffix VARCHAR(5) NOT NULL,
                sex ENUM('MALE', 'FEMALE'),
                birthdate date,
                birthplace VARCHAR(150),
                religion VARCHAR(50),
                address VARCHAR(255),
                gradeLevel VARCHAR(10),
                enrolled_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                student_profile BLOB,
                FOREIGN KEY (guardian_id) REFERENCES users(user_id) ON DELETE CASCADE
            )",
            
        ];

        foreach ($tableQueries as $sql) {
            $pdo->exec($sql);
        }

        // Insert default librarian
        $count = $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();
        if ($count == 0) {
            $stmt = $pdo->prepare("INSERT INTO admin (
                admin_firstname, admin_middlename, admin_lastname, admin_suffix, 
                admin_email, admin_username, admin_password, admin_user_role, admin_picture
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                'Juan',
                'Cruz',
                'Dela',
                '',
                'admin@school.edu.ph',
                'admin',
                password_hash('admin123', PASSWORD_BCRYPT),
                'admin',
                ''
            ]);
        }
        return $pdo;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Initialize database
$pdo = db_connect();
