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
                student_profile VARCHAR(255),
                created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS users_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT(11) NOT NULL,
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
                classroom_id INT(11),
                section_name VARCHAR(20),
                grade_level VARCHAR(20),
                assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
                FOREIGN KEY (adviser_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (sy_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE,
                FOREIGN KEY (classroom_id) REFERENCES classrooms(room_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS student (
                student_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                guardian_id INT(11),
                enrolment_status ENUM('active', 'transferred_in', 'not_active', 'transferred_out', 'dropped', 'pending', 'rejected') DEFAULT 'pending',
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
                student_profile VARCHAR(255),
                weight DECIMAL(5,2),
                height DECIMAL(4,2),
                height_squared DECIMAL(4,2),
                FOREIGN KEY (guardian_id) REFERENCES users(user_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS enrolment (
                enrolment_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                student_id INT(11),
                section_name VARCHAR(20),
                adviser_id INT(11),
                school_year_id INT(11),
                Grade_level VARCHAR(10),
                enrolment_Status ENUM('Approved', 'Rejected'),
                FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
                FOREIGN KEY (adviser_id) REFERENCES users(user_id) ON DELETE CASCADE,
                FOREIGN KEY (school_year_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS enrolment_subjects (
                enrolment_subjects_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                enrolment_id INT(11) NOT NULL,
                subjects_id INT(11) NOT NULL,
                FOREIGN KEY (enrolment_id) REFERENCES enrolment(enrolment_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS stuEnrolmentInfo (
                stuEnrolmentInfo_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                student_id INT(11) NOT NULL,
                mother_tongue VARCHAR(50),
                house_no VARCHAR(50),
                street VARCHAR(50),
                barnagay VARCHAR(50),
                city VARCHAR(50),
                province VARCHAR(50),
                country VARCHAR(50),
                zip_code VARCHAR(50), 
                diagnosis VARCHAR(50),
                manifestations VARCHAR(50),
                pwd_id VARCHAR(50),
                balik_aral VARCHAR(50),
                learning_mode VARCHAR(50),
                indigenous_people VARCHAR(50),
                fourPs VARCHAR(50),
                FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS attendance (
                attendance_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                student_id INT(11) NOT NULL,
                adviser_id INT(11) NOT NULL,
                morning_attendance DATETIME NOT NULL,
                attendance_type ENUM('Present', 'Absent', 'Late') NOT NULL,
                afternoon_attendance DATETIME DEFAULT NULL,
                A_attendance_type ENUM('Present', 'Absent', 'Late') DEFAULT NULL,
                attendance_summary ENUM('Present', 'Absent', 'Half-day', 'Late', 'Half-day-late') DEFAULT NULL,
                attendance_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
                FOREIGN KEY (adviser_id) REFERENCES users(user_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS feeback (
                feeback_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                parent_id INT(11) NOT NULL,
                title VARCHAR(100) NOT NULL,
                description TEXT NOT NULL,
                feed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES users(user_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS sf_add_data (
                sf_add_data_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                sf_type VARCHAR(20),
                school_id VARCHAR(20),
                school_name VARCHAR(100),
                Division VARCHAR(100),
                region VARCHAR(100),
                sy_id INT(11),
                district VARCHAR(20),
                report_for_the_month_of DATE,
                Previous_Month VARCHAR(12),
                For_the_month VARCHAR(12),
                Cumulative_as_of_End_of_Month VARCHAR(12),
                FOREIGN KEY (sy_id) REFERENCES school_year(school_year_id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS parents_info (
                parents_info_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                student_id INT(11) NOT NULL,
                f_firstname VARCHAR(50),
                f_middlename VARCHAR(50),
                f_lastname VARCHAR(50),
                f_suffix VARCHAR(50),
                m_firstname VARCHAR(50),
                m_middlename VARCHAR(50),
                m_lastname VARCHAR(50),
                g_firstname VARCHAR(50),
                g_middlename VARCHAR(50),
                g_lastname VARCHAR(50),
                g_suffix VARCHAR(50),
                g_relationship VARCHAR(50),
                p_contact VARCHAR(50),
                FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE
            )",

            "CREATE TABLE IF NOT EXISTS sf5_data (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                school_year VARCHAR(20),
                curriculum VARCHAR(50),
                grade_level VARCHAR(20),
                section VARCHAR(50),
                male_total INT(11),
                female_total INT(11),
                combined_total INT(11),
                promoted_male INT(11),
                promoted_female INT(11),
                promoted_total INT(11),
                conditional_male INT(11),
                conditional_female INT(11),
                conditional_total INT(11),
                retained_male INT(11),
                retained_female INT(11),
                retained_total INT(11),
                progress_did_not_meet_male INT(11),
                progress_did_not_meet_female INT(11),
                progress_did_not_meet_total INT(11),
                progress_fairly_satisfactory_male INT(11),
                progress_fairly_satisfactory_female INT(11),
                progress_fairly_satisfactory_total INT(11),
                progress_satisfactory_male INT(11),
                progress_satisfactory_female INT(11),
                progress_satisfactory_total INT(11),
                progress_very_satisfactory_male INT(11),
                progress_very_satisfactory_female INT(11),
                progress_very_satisfactory_total INT(11),
                progress_outstanding_male INT(11),
                progress_outstanding_female INT(11),
                progress_outstanding_total INT(11),
                prepared_by VARCHAR(100),
                certified_by VARCHAR(100),
                reviewed_by VARCHAR(100),
                learners LONGTEXT,
                action_taken JSON NULL
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            "CREATE TABLE IF NOT EXISTS sf10_data (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            student_id INT,
                            last_name VARCHAR(100),
                            first_name VARCHAR(100),
                            middle_name VARCHAR(100),
                            suffix VARCHAR(20),
                            lrn VARCHAR(50),
                            birthdate DATE,
                            sex VARCHAR(10),
                            school_name VARCHAR(255),
                            school_id VARCHAR(50),
                            school_address VARCHAR(255),
                            kinder_progress_report TINYINT(1),
                            eccd_checklist TINYINT(1),
                            kinder_certificate TINYINT(1),
                            pept_passer TINYINT(1),
                            pept_text VARCHAR(50),
                            exam_date DATE,
                            others_check TINYINT(1),
                            others_text VARCHAR(255),
                            testing_center_name VARCHAR(255),
                            testing_center_address VARCHAR(255),
                            remark VARCHAR(255),
                            scholastic_records JSON,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )",


            "CREATE TABLE IF NOT EXISTS sf9_data (
                        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        student_id INT(11),
                        student_name VARCHAR(150),
                        lrn VARCHAR(30),
                        age INT(11),
                        sex VARCHAR(10),
                        grade VARCHAR(20),
                        section VARCHAR(50),
                        school_year VARCHAR(20),
                        teacher VARCHAR(100),
                        guardian VARCHAR(150),
                        days_school_june INT(11) DEFAULT 0,
                        days_present_june INT(11) DEFAULT 0,
                        days_absent_june INT(11) DEFAULT 0,
                        days_school_july INT(11) DEFAULT 0,
                        days_present_july INT(11) DEFAULT 0,
                        days_absent_july INT(11) DEFAULT 0,
                        days_school_aug INT(11) DEFAULT 0,
                        days_present_aug INT(11) DEFAULT 0,
                        days_absent_aug INT(11) DEFAULT 0,
                        days_school_sep INT(11) DEFAULT 0,
                        days_present_sep INT(11) DEFAULT 0,
                        days_absent_sep INT(11) DEFAULT 0,
                        days_school_oct INT(11) DEFAULT 0,
                        days_present_oct INT(11) DEFAULT 0,
                        days_absent_oct INT(11) DEFAULT 0,
                        days_school_nov INT(11) DEFAULT 0,
                        days_present_nov INT(11) DEFAULT 0,
                        days_absent_nov INT(11) DEFAULT 0,
                        days_school_dec INT(11) DEFAULT 0,
                        days_present_dec INT(11) DEFAULT 0,
                        days_absent_dec INT(11) DEFAULT 0,
                        days_school_jan INT(11) DEFAULT 0,
                        days_present_jan INT(11) DEFAULT 0,
                        days_absent_jan INT(11) DEFAULT 0,
                        days_school_feb INT(11) DEFAULT 0,
                        days_present_feb INT(11) DEFAULT 0,
                        days_absent_feb INT(11) DEFAULT 0,
                        days_school_mar INT(11) DEFAULT 0,
                        days_present_mar INT(11) DEFAULT 0,
                        days_absent_mar INT(11) DEFAULT 0,
                        days_school_apr INT(11) DEFAULT 0,
                        days_present_apr INT(11) DEFAULT 0,
                        days_absent_apr INT(11) DEFAULT 0,
                    " . implode(',', array_map(function ($i) {
                return "
                            subject_$i VARCHAR(100),
                            q1_$i DECIMAL(5,2),
                            q2_$i DECIMAL(5,2),
                            q3_$i DECIMAL(5,2),
                            q4_$i DECIMAL(5,2),
                            final_$i DECIMAL(5,2),
                            remarks_$i VARCHAR(20)
                        ";
            }, range(1, 15))) . ",
                            general_average DECIMAL(5,2),
                        " . implode(',', array_map(function ($i) {
                return "
                                behavior_$i VARCHAR(255),
                                b{$i}_q1 VARCHAR(5),
                                b{$i}_q2 VARCHAR(5),
                                b{$i}_q3 VARCHAR(5),
                                b{$i}_q4 VARCHAR(5)
                            ";
            }, range(1, 7))) . ",
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE
                        )"



        ];

        foreach ($tableQueries as $sql) {
            $pdo->exec($sql);
        }

        try {
            $pdo->exec("DROP TRIGGER IF EXISTS trg_update_sf9_after_attendance_insert");

            $triggerSQL = "
        CREATE TRIGGER trg_update_sf9_after_attendance_insert
        AFTER INSERT ON attendance
        FOR EACH ROW
        BEGIN
            DECLARE attend_month INT;

            IF NEW.morning_attendance IS NOT NULL THEN
                SET attend_month = MONTH(NEW.morning_attendance);
            ELSE
                SET attend_month = MONTH(NEW.afternoon_attendance);
            END IF;

            IF NEW.attendance_type IN ('Present', 'Late') THEN

                CASE attend_month
                    WHEN 6 THEN 
                        UPDATE sf9_data SET 
                            days_present_june = days_present_june + 1,
                            days_school_june = days_school_june + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 7 THEN 
                        UPDATE sf9_data SET 
                            days_present_july = days_present_july + 1,
                            days_school_july = days_school_july + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 8 THEN 
                        UPDATE sf9_data SET 
                            days_present_aug = days_present_aug + 1,
                            days_school_aug = days_school_aug + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 9 THEN 
                        UPDATE sf9_data SET 
                            days_present_sep = days_present_sep + 1,
                            days_school_sep = days_school_sep + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 10 THEN 
                        UPDATE sf9_data SET 
                            days_present_oct = days_present_oct + 1,
                            days_school_oct = days_school_oct + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 11 THEN 
                        UPDATE sf9_data SET 
                            days_present_nov = days_present_nov + 1,
                            days_school_nov = days_school_nov + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 12 THEN 
                        UPDATE sf9_data SET 
                            days_present_dec = days_present_dec + 1,
                            days_school_dec = days_school_dec + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 1 THEN 
                        UPDATE sf9_data SET 
                            days_present_jan = days_present_jan + 1,
                            days_school_jan = days_school_jan + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 2 THEN 
                        UPDATE sf9_data SET 
                            days_present_feb = days_present_feb + 1,
                            days_school_feb = days_school_feb + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 3 THEN 
                        UPDATE sf9_data SET 
                            days_present_mar = days_present_mar + 1,
                            days_school_mar = days_school_mar + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 4 THEN 
                        UPDATE sf9_data SET 
                            days_present_apr = days_present_apr + 1,
                            days_school_apr = days_school_apr + 1
                        WHERE student_id = NEW.student_id;
                END CASE;

            ELSE

                CASE attend_month
                    WHEN 6 THEN 
                        UPDATE sf9_data SET 
                            days_absent_june = days_absent_june + 1,
                            days_school_june = days_school_june + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 7 THEN 
                        UPDATE sf9_data SET 
                            days_absent_july = days_absent_july + 1,
                            days_school_july = days_school_july + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 8 THEN 
                        UPDATE sf9_data SET 
                            days_absent_aug = days_absent_aug + 1,
                            days_school_aug = days_school_aug + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 9 THEN 
                        UPDATE sf9_data SET 
                            days_absent_sep = days_absent_sep + 1,
                            days_school_sep = days_school_sep + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 10 THEN 
                        UPDATE sf9_data SET 
                            days_absent_oct = days_absent_oct + 1,
                            days_school_oct = days_school_oct + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 11 THEN 
                        UPDATE sf9_data SET 
                            days_absent_nov = days_absent_nov + 1,
                            days_school_nov = days_school_nov + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 12 THEN 
                        UPDATE sf9_data SET 
                            days_absent_dec = days_absent_dec + 1,
                            days_school_dec = days_school_dec + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 1 THEN 
                        UPDATE sf9_data SET 
                            days_absent_jan = days_absent_jan + 1,
                            days_school_jan = days_school_jan + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 2 THEN 
                        UPDATE sf9_data SET 
                            days_absent_feb = days_absent_feb + 1,
                            days_school_feb = days_school_feb + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 3 THEN 
                        UPDATE sf9_data SET 
                            days_absent_mar = days_absent_mar + 1,
                            days_school_mar = days_school_mar + 1
                        WHERE student_id = NEW.student_id;

                    WHEN 4 THEN 
                        UPDATE sf9_data SET 
                            days_absent_apr = days_absent_apr + 1,
                            days_school_apr = days_school_apr + 1
                        WHERE student_id = NEW.student_id;
                END CASE;

            END IF;

        END;
    ";

            $pdo->exec($triggerSQL);
        } catch (PDOException $e) {
            // Optional: log error
        }


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

        $checkSF1 = $pdo->prepare("SELECT COUNT(*) FROM sf_add_data WHERE sf_type = 'sf_1'");
        $checkSF1->execute();
        if ($checkSF1->fetchColumn() == 0) {
            $stmtSF1 = $pdo->prepare("INSERT INTO sf_add_data (sf_type) VALUES ('sf_1')");
            $stmtSF1->execute();
        }


        $checkSF2 = $pdo->prepare("SELECT COUNT(*) FROM sf_add_data WHERE sf_type = 'sf_2'");
        $checkSF2->execute();
        if ($checkSF2->fetchColumn() == 0) {
            $stmtSF2 = $pdo->prepare("INSERT INTO sf_add_data (sf_type) VALUES ('sf_2')");
            $stmtSF2->execute();
        }


        $checkSF4 = $pdo->prepare("SELECT COUNT(*) FROM sf_add_data WHERE sf_type = 'sf_4'");
        $checkSF4->execute();
        if ($checkSF4->fetchColumn() == 0) {
            $stmtSF4 = $pdo->prepare("INSERT INTO sf_add_data (sf_type) VALUES ('sf_4')");
            $stmtSF4->execute();
        }


        $checkSF8 = $pdo->prepare("SELECT COUNT(*) FROM sf_add_data WHERE sf_type = 'sf_8'");
        $checkSF8->execute();
        if ($checkSF8->fetchColumn() == 0) {
            $stmtSF8 = $pdo->prepare("INSERT INTO sf_add_data (sf_type) VALUES ('sf_8')");
            $stmtSF8->execute();
        }

        try {

            $pdo->exec("ALTER TABLE sections ADD UNIQUE (section_name, section_grade_level)");
        } catch (PDOException $e) {
        }

        try {

            $pdo->exec("DROP TRIGGER IF EXISTS after_section_update");
            $pdo->exec("
        CREATE TRIGGER after_section_update
        AFTER UPDATE ON sections
        FOR EACH ROW
        BEGIN
            IF NEW.section_name <> OLD.section_name THEN
                UPDATE sf9_data
                SET section = NEW.section_name
                WHERE section = OLD.section_name
                AND grade = NEW.section_grade_level;
            END IF;
        END;
    ");
        } catch (PDOException $e) {
        }

        return $pdo;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}


$pdo = db_connect();
