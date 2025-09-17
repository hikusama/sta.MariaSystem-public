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
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
             "CREATE TABLE IF NOT EXISTS users_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                login_time DATETIME NOT NULL,
                logout_time DATETIME DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
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
