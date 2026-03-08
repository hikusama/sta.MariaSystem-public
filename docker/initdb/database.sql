-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2026 at 11:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stamariadb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_firstname` varchar(50) NOT NULL,
  `admin_middlename` varchar(50) NOT NULL,
  `admin_lastname` varchar(50) NOT NULL,
  `admin_suffix` varchar(5) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_user_role` varchar(20) NOT NULL,
  `admin_picture` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_history`
--

CREATE TABLE `admin_history` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `adviser_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `morning_attendance` datetime NOT NULL,
  `attendance_type` enum('Present','Absent','Late') NOT NULL,
  `afternoon_attendance` datetime DEFAULT NULL,
  `A_attendance_type` enum('Present','Absent','Late') DEFAULT NULL,
  `attendance_summary` enum('Present','Absent','Half-day','Late','Half-day-late') DEFAULT NULL,
  `attendance_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `attendance`
--
DELIMITER $$
CREATE TRIGGER `trg_sf9_attendance_insert` AFTER INSERT ON `attendance` FOR EACH ROW BEGIN
    DECLARE new_date DATE;
    DECLARE new_summary VARCHAR(20);
    DECLARE new_month INT;
    DECLARE sy_name VARCHAR(50);

    SET new_date = NEW.attendance_at;
    SET new_summary = NEW.attendance_summary;

    -- Get active school year safely
    SELECT school_year_name INTO sy_name
    FROM school_year
    WHERE school_year_id = NEW.school_year_id
      AND school_year_status = 'Active'
    LIMIT 1;

    -- Only proceed if school year exists
    IF sy_name IS NOT NULL AND new_date IS NOT NULL AND DAYOFWEEK(new_date) <> 1 THEN
        SET new_month = MONTH(new_date);

        -- Single UPDATE for Present/Absent
        UPDATE sf9_data
        SET 
            days_school_june  = days_school_june  + IF(new_month = 6,1,0),
            days_school_july  = days_school_july  + IF(new_month = 7,1,0),
            days_school_aug   = days_school_aug   + IF(new_month = 8,1,0),
            days_school_sep   = days_school_sep   + IF(new_month = 9,1,0),
            days_school_oct   = days_school_oct   + IF(new_month = 10,1,0),
            days_school_nov   = days_school_nov   + IF(new_month = 11,1,0),
            days_school_dec   = days_school_dec   + IF(new_month = 12,1,0),
            days_school_jan   = days_school_jan   + IF(new_month = 1,1,0),
            days_school_feb   = days_school_feb   + IF(new_month = 2,1,0),
            days_school_mar   = days_school_mar   + IF(new_month = 3,1,0),
            days_school_apr   = days_school_apr   + IF(new_month = 4,1,0),
            days_present_june = days_present_june + IF(new_month = 6 AND new_summary='Present',1,0),
            days_present_july = days_present_july + IF(new_month = 7 AND new_summary='Present',1,0),
            days_present_aug  = days_present_aug  + IF(new_month = 8 AND new_summary='Present',1,0),
            days_present_sep  = days_present_sep  + IF(new_month = 9 AND new_summary='Present',1,0),
            days_present_oct  = days_present_oct  + IF(new_month = 10 AND new_summary='Present',1,0),
            days_present_nov  = days_present_nov  + IF(new_month = 11 AND new_summary='Present',1,0),
            days_present_dec  = days_present_dec  + IF(new_month = 12 AND new_summary='Present',1,0),
            days_present_jan  = days_present_jan  + IF(new_month = 1 AND new_summary='Present',1,0),
            days_present_feb  = days_present_feb  + IF(new_month = 2 AND new_summary='Present',1,0),
            days_present_mar  = days_present_mar  + IF(new_month = 3 AND new_summary='Present',1,0),
            days_present_apr  = days_present_apr  + IF(new_month = 4 AND new_summary='Present',1,0),
            days_absent_june  = days_absent_june  + IF(new_month = 6 AND new_summary='Absent',1,0),
            days_absent_july  = days_absent_july  + IF(new_month = 7 AND new_summary='Absent',1,0),
            days_absent_aug   = days_absent_aug   + IF(new_month = 8 AND new_summary='Absent',1,0),
            days_absent_sep   = days_absent_sep   + IF(new_month = 9 AND new_summary='Absent',1,0),
            days_absent_oct   = days_absent_oct   + IF(new_month = 10 AND new_summary='Absent',1,0),
            days_absent_nov   = days_absent_nov   + IF(new_month = 11 AND new_summary='Absent',1,0),
            days_absent_dec   = days_absent_dec   + IF(new_month = 12 AND new_summary='Absent',1,0),
            days_absent_jan   = days_absent_jan   + IF(new_month = 1 AND new_summary='Absent',1,0),
            days_absent_feb   = days_absent_feb   + IF(new_month = 2 AND new_summary='Absent',1,0),
            days_absent_mar   = days_absent_mar   + IF(new_month = 3 AND new_summary='Absent',1,0),
            days_absent_apr   = days_absent_apr   + IF(new_month = 4 AND new_summary='Absent',1,0)
        WHERE student_id = NEW.student_id AND school_year = sy_name;

        -- Recalculate totals
        UPDATE sf9_data
        SET 
            days_total   = days_school_june + days_school_july + days_school_aug + days_school_sep +
                           days_school_oct + days_school_nov + days_school_dec + days_school_jan +
                           days_school_feb + days_school_mar + days_school_apr,
            days_present = days_present_june + days_present_july + days_present_aug + days_present_sep +
                           days_present_oct + days_present_nov + days_present_dec + days_present_jan +
                           days_present_feb + days_present_mar + days_present_apr,
            days_absent  = days_absent_june + days_absent_july + days_absent_aug + days_absent_sep +
                           days_absent_oct + days_absent_nov + days_absent_dec + days_absent_jan +
                           days_absent_feb + days_absent_mar + days_absent_apr
        WHERE student_id = NEW.student_id AND school_year = sy_name;
    END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sf9_attendance_update` AFTER UPDATE ON `attendance` FOR EACH ROW BEGIN
    DECLARE old_date DATE;
    DECLARE new_date DATE;
    DECLARE old_summary VARCHAR(20);
    DECLARE new_summary VARCHAR(20);
    DECLARE old_month INT;
    DECLARE new_month INT;
    DECLARE sy_name VARCHAR(50);

    SET old_date = OLD.attendance_at;
    SET new_date = NEW.attendance_at;
    SET old_summary = OLD.attendance_summary;
    SET new_summary = NEW.attendance_summary;

    -- Get school year safely
    SELECT school_year_name INTO sy_name
    FROM school_year
    WHERE school_year_id = NEW.school_year_id
    LIMIT 1;

    IF sy_name IS NOT NULL THEN

        -- Remove old attendance if date exists and not Sunday
        IF old_date IS NOT NULL AND DAYOFWEEK(old_date) <> 1 THEN
            SET old_month = MONTH(old_date);

            UPDATE sf9_data
            SET
                days_school_june  = days_school_june  - IF(old_month=6,1,0),
                days_school_july  = days_school_july  - IF(old_month=7,1,0),
                days_school_aug   = days_school_aug   - IF(old_month=8,1,0),
                days_school_sep   = days_school_sep   - IF(old_month=9,1,0),
                days_school_oct   = days_school_oct   - IF(old_month=10,1,0),
                days_school_nov   = days_school_nov   - IF(old_month=11,1,0),
                days_school_dec   = days_school_dec   - IF(old_month=12,1,0),
                days_school_jan   = days_school_jan   - IF(old_month=1,1,0),
                days_school_feb   = days_school_feb   - IF(old_month=2,1,0),
                days_school_mar   = days_school_mar   - IF(old_month=3,1,0),
                days_school_apr   = days_school_apr   - IF(old_month=4,1,0),
                days_present_june = days_present_june - IF(old_month=6 AND old_summary='Present',1,0),
                days_present_july = days_present_july - IF(old_month=7 AND old_summary='Present',1,0),
                days_present_aug  = days_present_aug  - IF(old_month=8 AND old_summary='Present',1,0),
                days_present_sep  = days_present_sep  - IF(old_month=9 AND old_summary='Present',1,0),
                days_present_oct  = days_present_oct  - IF(old_month=10 AND old_summary='Present',1,0),
                days_present_nov  = days_present_nov  - IF(old_month=11 AND old_summary='Present',1,0),
                days_present_dec  = days_present_dec  - IF(old_month=12 AND old_summary='Present',1,0),
                days_present_jan  = days_present_jan  - IF(old_month=1 AND old_summary='Present',1,0),
                days_present_feb  = days_present_feb  - IF(old_month=2 AND old_summary='Present',1,0),
                days_present_mar  = days_present_mar  - IF(old_month=3 AND old_summary='Present',1,0),
                days_present_apr  = days_present_apr  - IF(old_month=4 AND old_summary='Present',1,0),
                days_absent_june  = days_absent_june  - IF(old_month=6 AND old_summary='Absent',1,0),
                days_absent_july  = days_absent_july  - IF(old_month=7 AND old_summary='Absent',1,0),
                days_absent_aug   = days_absent_aug   - IF(old_month=8 AND old_summary='Absent',1,0),
                days_absent_sep   = days_absent_sep   - IF(old_month=9 AND old_summary='Absent',1,0),
                days_absent_oct   = days_absent_oct   - IF(old_month=10 AND old_summary='Absent',1,0),
                days_absent_nov   = days_absent_nov   - IF(old_month=11 AND old_summary='Absent',1,0),
                days_absent_dec   = days_absent_dec   - IF(old_month=12 AND old_summary='Absent',1,0),
                days_absent_jan   = days_absent_jan   - IF(old_month=1 AND old_summary='Absent',1,0),
                days_absent_feb   = days_absent_feb   - IF(old_month=2 AND old_summary='Absent',1,0),
                days_absent_mar   = days_absent_mar   - IF(old_month=3 AND old_summary='Absent',1,0),
                days_absent_apr   = days_absent_apr   - IF(old_month=4 AND old_summary='Absent',1,0)
            WHERE student_id = OLD.student_id AND school_year = sy_name;
        END IF;

        -- Add new attendance if date exists and not Sunday
        IF new_date IS NOT NULL AND DAYOFWEEK(new_date) <> 1 THEN
            SET new_month = MONTH(new_date);

            UPDATE sf9_data
            SET
                days_school_june  = days_school_june  + IF(new_month=6,1,0),
                days_school_july  = days_school_july  + IF(new_month=7,1,0),
                days_school_aug   = days_school_aug   + IF(new_month=8,1,0),
                days_school_sep   = days_school_sep   + IF(new_month=9,1,0),
                days_school_oct   = days_school_oct   + IF(new_month=10,1,0),
                days_school_nov   = days_school_nov   + IF(new_month=11,1,0),
                days_school_dec   = days_school_dec   + IF(new_month=12,1,0),
                days_school_jan   = days_school_jan   + IF(new_month=1,1,0),
                days_school_feb   = days_school_feb   + IF(new_month=2,1,0),
                days_school_mar   = days_school_mar   + IF(new_month=3,1,0),
                days_school_apr   = days_school_apr   + IF(new_month=4,1,0),
                days_present_june = days_present_june + IF(new_month=6 AND new_summary='Present',1,0),
                days_present_july = days_present_july + IF(new_month=7 AND new_summary='Present',1,0),
                days_present_aug  = days_present_aug  + IF(new_month=8 AND new_summary='Present',1,0),
                days_present_sep  = days_present_sep  + IF(new_month=9 AND new_summary='Present',1,0),
                days_present_oct  = days_present_oct  + IF(new_month=10 AND new_summary='Present',1,0),
                days_present_nov  = days_present_nov  + IF(new_month=11 AND new_summary='Present',1,0),
                days_present_dec  = days_present_dec  + IF(new_month=12 AND new_summary='Present',1,0),
                days_present_jan  = days_present_jan  + IF(new_month=1 AND new_summary='Present',1,0),
                days_present_feb  = days_present_feb  + IF(new_month=2 AND new_summary='Present',1,0),
                days_present_mar  = days_present_mar  + IF(new_month=3 AND new_summary='Present',1,0),
                days_present_apr  = days_present_apr  + IF(new_month=4 AND new_summary='Present',1,0),
                days_absent_june  = days_absent_june  + IF(new_month=6 AND new_summary='Absent',1,0),
                days_absent_july  = days_absent_july  + IF(new_month=7 AND new_summary='Absent',1,0),
                days_absent_aug   = days_absent_aug   + IF(new_month=8 AND new_summary='Absent',1,0),
                days_absent_sep   = days_absent_sep   + IF(new_month=9 AND new_summary='Absent',1,0),
                days_absent_oct   = days_absent_oct   + IF(new_month=10 AND new_summary='Absent',1,0),
                days_absent_nov   = days_absent_nov   + IF(new_month=11 AND new_summary='Absent',1,0),
                days_absent_dec   = days_absent_dec   + IF(new_month=12 AND new_summary='Absent',1,0),
                days_absent_jan   = days_absent_jan   + IF(new_month=1 AND new_summary='Absent',1,0),
                days_absent_feb   = days_absent_feb   + IF(new_month=2 AND new_summary='Absent',1,0),
                days_absent_mar   = days_absent_mar   + IF(new_month=3 AND new_summary='Absent',1,0),
                days_absent_apr   = days_absent_apr   + IF(new_month=4 AND new_summary='Absent',1,0)
            WHERE student_id = NEW.student_id AND school_year = sy_name;
        END IF;

        -- Recalculate totals if any change
        IF (old_date IS NOT NULL AND DAYOFWEEK(old_date) <> 1) 
           OR (new_date IS NOT NULL AND DAYOFWEEK(new_date) <> 1) THEN
            UPDATE sf9_data
            SET 
                days_total   = days_school_june + days_school_july + days_school_aug + days_school_sep +
                               days_school_oct + days_school_nov + days_school_dec + days_school_jan +
                               days_school_feb + days_school_mar + days_school_apr,
                days_present = days_present_june + days_present_july + days_present_aug + days_present_sep +
                               days_present_oct + days_present_nov + days_present_dec + days_present_jan +
                               days_present_feb + days_present_mar + days_present_apr,
                days_absent  = days_absent_june + days_absent_july + days_absent_aug + days_absent_sep +
                               days_absent_oct + days_absent_nov + days_absent_dec + days_absent_jan +
                               days_absent_feb + days_absent_mar + days_absent_apr
            WHERE student_id = NEW.student_id AND school_year = sy_name;
        END IF;

    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `adviser_id` int(11) NOT NULL,
  `sy_id` int(11) NOT NULL,
  `classroom_id` int(11) DEFAULT NULL,
  `section_name` varchar(20) DEFAULT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `room_id` int(11) NOT NULL,
  `room_status` enum('Unavailable','Available') DEFAULT 'Available',
  `room_name` varchar(50) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrolment`
--

CREATE TABLE `enrolment` (
  `enrolment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `section_name` varchar(20) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `school_year_id` int(11) DEFAULT NULL,
  `Grade_level` varchar(10) DEFAULT NULL,
  `enrolment_Status` enum('Approved','Rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `enrolment`
--
DELIMITER $$
CREATE TRIGGER `after_enrolment_insert` AFTER INSERT ON `enrolment` FOR EACH ROW BEGIN
    DECLARE full_name VARCHAR(255);
    DECLARE sy_name VARCHAR(50);
    DECLARE student_full_name VARCHAR(255);
    DECLARE student_lrn VARCHAR(20);

    -- Get adviser full name
    SELECT CONCAT(firstname,' ',middlename,' ',lastname) INTO full_name
    FROM users WHERE user_id = NEW.adviser_id;

    -- Get active school year name
    SELECT school_year_name INTO sy_name
    FROM school_year
    WHERE school_year_id = NEW.school_year_id
      AND school_year_status = 'Active' LIMIT 1;

    IF sy_name IS NOT NULL THEN
        -- Get student full name and LRN
        SELECT CONCAT(lname, ', ', fname, ' ', mname), lrn
        INTO student_full_name, student_lrn
        FROM student WHERE student_id = NEW.student_id;

        -- Insert into sf9_data if not exists
        IF NOT EXISTS (
            SELECT 1
            FROM sf9_data
            WHERE student_id = NEW.student_id
              AND school_year = sy_name
        ) THEN
            INSERT INTO sf9_data (
                student_id, student_name, lrn, grade, section, school_year, teacher, school, district, division, school_id, region
            )
            VALUES (
                NEW.student_id, student_full_name, student_lrn, NEW.Grade_level, NEW.section_name, sy_name, full_name, 'Sta. Maria Central School' ,'Sta. Maria', 'Zamboanga City', '126220', 'IX'
            );

            -- Update sex, age, guardian
            UPDATE sf9_data s
            JOIN student st ON st.student_id = NEW.student_id
            LEFT JOIN users u ON u.user_id = st.guardian_id
            SET s.sex = st.sex,
                s.age = TIMESTAMPDIFF(YEAR, st.birthdate, CURDATE()),
                s.guardian = CONCAT(u.firstname,' ',u.middlename,' ',u.lastname)
            WHERE s.student_id = NEW.student_id
              AND s.school_year = sy_name;

            -- Update subjects
            UPDATE sf9_data s
            SET
                s.subject_1  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 0,1),
                s.subject_2  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 1,1),
                s.subject_3  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 2,1),
                s.subject_4  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 3,1),
                s.subject_5  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 4,1),
                s.subject_6  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 5,1),
                s.subject_7  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 6,1),
                s.subject_8  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 7,1),
                s.subject_9  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 8,1),
                s.subject_10 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 9,1),
                s.subject_11 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 10,1),
                s.subject_12 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 11,1),
                s.subject_13 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 12,1),
                s.subject_14 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 13,1),
                s.subject_15 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.Grade_level ORDER BY subject_id LIMIT 14,1)
            WHERE s.student_id = NEW.student_id
              AND s.school_year = sy_name;
        END IF;
    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `enrolment_subjects`
--

CREATE TABLE `enrolment_subjects` (
  `enrolment_subjects_id` int(11) NOT NULL,
  `enrolment_id` int(11) NOT NULL,
  `subjects_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feeback`
--

CREATE TABLE `feeback` (
  `feeback_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `feed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parents_info`
--

CREATE TABLE `parents_info` (
  `parents_info_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `f_firstname` varchar(50) DEFAULT NULL,
  `f_middlename` varchar(50) DEFAULT NULL,
  `f_lastname` varchar(50) DEFAULT NULL,
  `f_suffix` varchar(50) DEFAULT NULL,
  `m_firstname` varchar(50) DEFAULT NULL,
  `m_middlename` varchar(50) DEFAULT NULL,
  `m_lastname` varchar(50) DEFAULT NULL,
  `g_firstname` varchar(50) DEFAULT NULL,
  `g_middlename` varchar(50) DEFAULT NULL,
  `g_lastname` varchar(50) DEFAULT NULL,
  `g_suffix` varchar(50) DEFAULT NULL,
  `g_relationship` varchar(50) DEFAULT NULL,
  `p_contact` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remedial_class`
--

CREATE TABLE `remedial_class` (
  `remedial_id` int(11) NOT NULL,
  `sf10_rem_id` int(11) NOT NULL,
  `area` varchar(255) DEFAULT NULL,
  `final_rating` varchar(50) DEFAULT NULL,
  `class_mark` varchar(50) DEFAULT NULL,
  `recomputed_rating` varchar(50) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_year`
--

CREATE TABLE `school_year` (
  `school_year_id` int(11) NOT NULL,
  `school_year_status` enum('Active','Inactive') DEFAULT NULL,
  `school_year_name` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `school_year`
--
DELIMITER $$
CREATE TRIGGER `trg_school_year_status` AFTER INSERT ON `school_year` FOR EACH ROW BEGIN
    IF NEW.school_year_status = 'Active' THEN
        UPDATE classrooms c
        LEFT JOIN classes cl ON cl.classroom_id = c.room_id AND cl.sy_id = NEW.school_year_id
        SET c.room_status = 'Available'
        WHERE cl.classroom_id IS NULL;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_school_year_status_update` AFTER UPDATE ON `school_year` FOR EACH ROW BEGIN
    DECLARE latest_sy_id INT;

    IF NEW.school_year_status = 'Active' THEN
        SELECT school_year_id INTO latest_sy_id
        FROM school_year
        ORDER BY created_date DESC
        LIMIT 1;

        IF NEW.school_year_id = latest_sy_id THEN
            UPDATE classrooms c
            LEFT JOIN classes cl ON cl.classroom_id = c.room_id AND cl.sy_id = NEW.school_year_id
            SET c.room_status = 'Available'
            WHERE cl.classroom_id IS NULL;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `section_grade_level` varchar(7) NOT NULL,
  `section_status` enum('Available','Inavailable') DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `sections`
--
DELIMITER $$
CREATE TRIGGER `after_section_update` AFTER UPDATE ON `sections` FOR EACH ROW BEGIN
    IF NEW.section_name <> OLD.section_name THEN
        UPDATE sf9_data
        SET section = NEW.section_name
        WHERE section = OLD.section_name
          AND grade = NEW.section_grade_level;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sf5_data`
--

CREATE TABLE `sf5_data` (
  `id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `curriculum` varchar(50) DEFAULT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `male_total` int(11) DEFAULT NULL,
  `female_total` int(11) DEFAULT NULL,
  `combined_total` int(11) DEFAULT NULL,
  `promoted_male` int(11) DEFAULT NULL,
  `promoted_female` int(11) DEFAULT NULL,
  `promoted_total` int(11) DEFAULT NULL,
  `retained_male` int(11) DEFAULT NULL,
  `retained_female` int(11) DEFAULT NULL,
  `retained_total` int(11) DEFAULT NULL,
  `progress_did_not_meet_male` int(11) DEFAULT NULL,
  `progress_did_not_meet_female` int(11) DEFAULT NULL,
  `progress_did_not_meet_total` int(11) DEFAULT NULL,
  `progress_fairly_satisfactory_male` int(11) DEFAULT NULL,
  `progress_fairly_satisfactory_female` int(11) DEFAULT NULL,
  `progress_fairly_satisfactory_total` int(11) DEFAULT NULL,
  `progress_satisfactory_male` int(11) DEFAULT NULL,
  `progress_satisfactory_female` int(11) DEFAULT NULL,
  `progress_satisfactory_total` int(11) DEFAULT NULL,
  `progress_very_satisfactory_male` int(11) DEFAULT NULL,
  `progress_very_satisfactory_female` int(11) DEFAULT NULL,
  `progress_very_satisfactory_total` int(11) DEFAULT NULL,
  `progress_outstanding_male` int(11) DEFAULT NULL,
  `progress_outstanding_female` int(11) DEFAULT NULL,
  `progress_outstanding_total` int(11) DEFAULT NULL,
  `prepared_by` varchar(100) DEFAULT NULL,
  `certified_by` varchar(100) DEFAULT NULL,
  `reviewed_by` varchar(100) DEFAULT NULL,
  `learners` longtext DEFAULT NULL,
  `action_taken` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sf9_data`
--

CREATE TABLE `sf9_data` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `student_name` varchar(150) DEFAULT NULL,
  `lrn` varchar(30) DEFAULT NULL,
  `days_present` int(11) DEFAULT 0,
  `days_absent` int(11) DEFAULT 0,
  `days_total` int(11) DEFAULT 0,
  `days_late` int(11) DEFAULT 0,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `teacher` varchar(100) DEFAULT NULL,
  `school` varchar(150) DEFAULT NULL,
  `district` varchar(150) DEFAULT NULL,
  `division` varchar(150) DEFAULT NULL,
  `school_id` varchar(150) DEFAULT NULL,
  `region` varchar(150) DEFAULT NULL,
  `guardian` varchar(150) DEFAULT NULL,
  `days_school_june` int(11) DEFAULT 0,
  `days_present_june` int(11) DEFAULT 0,
  `days_absent_june` int(11) DEFAULT 0,
  `days_school_july` int(11) DEFAULT 0,
  `days_present_july` int(11) DEFAULT 0,
  `days_absent_july` int(11) DEFAULT 0,
  `days_school_aug` int(11) DEFAULT 0,
  `days_present_aug` int(11) DEFAULT 0,
  `days_absent_aug` int(11) DEFAULT 0,
  `days_school_sep` int(11) DEFAULT 0,
  `days_present_sep` int(11) DEFAULT 0,
  `days_absent_sep` int(11) DEFAULT 0,
  `days_school_oct` int(11) DEFAULT 0,
  `days_present_oct` int(11) DEFAULT 0,
  `days_absent_oct` int(11) DEFAULT 0,
  `days_school_nov` int(11) DEFAULT 0,
  `days_present_nov` int(11) DEFAULT 0,
  `days_absent_nov` int(11) DEFAULT 0,
  `days_school_dec` int(11) DEFAULT 0,
  `days_present_dec` int(11) DEFAULT 0,
  `days_absent_dec` int(11) DEFAULT 0,
  `days_school_jan` int(11) DEFAULT 0,
  `days_present_jan` int(11) DEFAULT 0,
  `days_absent_jan` int(11) DEFAULT 0,
  `days_school_feb` int(11) DEFAULT 0,
  `days_present_feb` int(11) DEFAULT 0,
  `days_absent_feb` int(11) DEFAULT 0,
  `days_school_mar` int(11) DEFAULT 0,
  `days_present_mar` int(11) DEFAULT 0,
  `days_absent_mar` int(11) DEFAULT 0,
  `days_school_apr` int(11) DEFAULT 0,
  `days_present_apr` int(11) DEFAULT 0,
  `days_absent_apr` int(11) DEFAULT 0,
  `subject_1` varchar(100) DEFAULT NULL,
  `q1_1` decimal(5,2) DEFAULT NULL,
  `q2_1` decimal(5,2) DEFAULT NULL,
  `q3_1` decimal(5,2) DEFAULT NULL,
  `q4_1` decimal(5,2) DEFAULT NULL,
  `final_1` decimal(5,2) DEFAULT NULL,
  `remarks_1` varchar(20) DEFAULT NULL,
  `subject_2` varchar(100) DEFAULT NULL,
  `q1_2` decimal(5,2) DEFAULT NULL,
  `q2_2` decimal(5,2) DEFAULT NULL,
  `q3_2` decimal(5,2) DEFAULT NULL,
  `q4_2` decimal(5,2) DEFAULT NULL,
  `final_2` decimal(5,2) DEFAULT NULL,
  `remarks_2` varchar(20) DEFAULT NULL,
  `subject_3` varchar(100) DEFAULT NULL,
  `q1_3` decimal(5,2) DEFAULT NULL,
  `q2_3` decimal(5,2) DEFAULT NULL,
  `q3_3` decimal(5,2) DEFAULT NULL,
  `q4_3` decimal(5,2) DEFAULT NULL,
  `final_3` decimal(5,2) DEFAULT NULL,
  `remarks_3` varchar(20) DEFAULT NULL,
  `subject_4` varchar(100) DEFAULT NULL,
  `q1_4` decimal(5,2) DEFAULT NULL,
  `q2_4` decimal(5,2) DEFAULT NULL,
  `q3_4` decimal(5,2) DEFAULT NULL,
  `q4_4` decimal(5,2) DEFAULT NULL,
  `final_4` decimal(5,2) DEFAULT NULL,
  `remarks_4` varchar(20) DEFAULT NULL,
  `subject_5` varchar(100) DEFAULT NULL,
  `q1_5` decimal(5,2) DEFAULT NULL,
  `q2_5` decimal(5,2) DEFAULT NULL,
  `q3_5` decimal(5,2) DEFAULT NULL,
  `q4_5` decimal(5,2) DEFAULT NULL,
  `final_5` decimal(5,2) DEFAULT NULL,
  `remarks_5` varchar(20) DEFAULT NULL,
  `subject_6` varchar(100) DEFAULT NULL,
  `q1_6` decimal(5,2) DEFAULT NULL,
  `q2_6` decimal(5,2) DEFAULT NULL,
  `q3_6` decimal(5,2) DEFAULT NULL,
  `q4_6` decimal(5,2) DEFAULT NULL,
  `final_6` decimal(5,2) DEFAULT NULL,
  `remarks_6` varchar(20) DEFAULT NULL,
  `subject_7` varchar(100) DEFAULT NULL,
  `q1_7` decimal(5,2) DEFAULT NULL,
  `q2_7` decimal(5,2) DEFAULT NULL,
  `q3_7` decimal(5,2) DEFAULT NULL,
  `q4_7` decimal(5,2) DEFAULT NULL,
  `final_7` decimal(5,2) DEFAULT NULL,
  `remarks_7` varchar(20) DEFAULT NULL,
  `subject_8` varchar(100) DEFAULT NULL,
  `q1_8` decimal(5,2) DEFAULT NULL,
  `q2_8` decimal(5,2) DEFAULT NULL,
  `q3_8` decimal(5,2) DEFAULT NULL,
  `q4_8` decimal(5,2) DEFAULT NULL,
  `final_8` decimal(5,2) DEFAULT NULL,
  `remarks_8` varchar(20) DEFAULT NULL,
  `subject_9` varchar(100) DEFAULT NULL,
  `q1_9` decimal(5,2) DEFAULT NULL,
  `q2_9` decimal(5,2) DEFAULT NULL,
  `q3_9` decimal(5,2) DEFAULT NULL,
  `q4_9` decimal(5,2) DEFAULT NULL,
  `final_9` decimal(5,2) DEFAULT NULL,
  `remarks_9` varchar(20) DEFAULT NULL,
  `subject_10` varchar(100) DEFAULT NULL,
  `q1_10` decimal(5,2) DEFAULT NULL,
  `q2_10` decimal(5,2) DEFAULT NULL,
  `q3_10` decimal(5,2) DEFAULT NULL,
  `q4_10` decimal(5,2) DEFAULT NULL,
  `final_10` decimal(5,2) DEFAULT NULL,
  `remarks_10` varchar(20) DEFAULT NULL,
  `subject_11` varchar(100) DEFAULT NULL,
  `q1_11` decimal(5,2) DEFAULT NULL,
  `q2_11` decimal(5,2) DEFAULT NULL,
  `q3_11` decimal(5,2) DEFAULT NULL,
  `q4_11` decimal(5,2) DEFAULT NULL,
  `final_11` decimal(5,2) DEFAULT NULL,
  `remarks_11` varchar(20) DEFAULT NULL,
  `subject_12` varchar(100) DEFAULT NULL,
  `q1_12` decimal(5,2) DEFAULT NULL,
  `q2_12` decimal(5,2) DEFAULT NULL,
  `q3_12` decimal(5,2) DEFAULT NULL,
  `q4_12` decimal(5,2) DEFAULT NULL,
  `final_12` decimal(5,2) DEFAULT NULL,
  `remarks_12` varchar(20) DEFAULT NULL,
  `subject_13` varchar(100) DEFAULT NULL,
  `q1_13` decimal(5,2) DEFAULT NULL,
  `q2_13` decimal(5,2) DEFAULT NULL,
  `q3_13` decimal(5,2) DEFAULT NULL,
  `q4_13` decimal(5,2) DEFAULT NULL,
  `final_13` decimal(5,2) DEFAULT NULL,
  `remarks_13` varchar(20) DEFAULT NULL,
  `subject_14` varchar(100) DEFAULT NULL,
  `q1_14` decimal(5,2) DEFAULT NULL,
  `q2_14` decimal(5,2) DEFAULT NULL,
  `q3_14` decimal(5,2) DEFAULT NULL,
  `q4_14` decimal(5,2) DEFAULT NULL,
  `final_14` decimal(5,2) DEFAULT NULL,
  `remarks_14` varchar(20) DEFAULT NULL,
  `subject_15` varchar(100) DEFAULT NULL,
  `q1_15` decimal(5,2) DEFAULT NULL,
  `q2_15` decimal(5,2) DEFAULT NULL,
  `q3_15` decimal(5,2) DEFAULT NULL,
  `q4_15` decimal(5,2) DEFAULT NULL,
  `final_15` decimal(5,2) DEFAULT NULL,
  `remarks_15` varchar(20) DEFAULT NULL,
  `general_average` decimal(5,2) DEFAULT NULL,
  `behavior_1` varchar(255) DEFAULT NULL,
  `b1_q1` varchar(5) DEFAULT NULL,
  `b1_q2` varchar(5) DEFAULT NULL,
  `b1_q3` varchar(5) DEFAULT NULL,
  `b1_q4` varchar(5) DEFAULT NULL,
  `behavior_2` varchar(255) DEFAULT NULL,
  `b2_q1` varchar(5) DEFAULT NULL,
  `b2_q2` varchar(5) DEFAULT NULL,
  `b2_q3` varchar(5) DEFAULT NULL,
  `b2_q4` varchar(5) DEFAULT NULL,
  `behavior_3` varchar(255) DEFAULT NULL,
  `b3_q1` varchar(5) DEFAULT NULL,
  `b3_q2` varchar(5) DEFAULT NULL,
  `b3_q3` varchar(5) DEFAULT NULL,
  `b3_q4` varchar(5) DEFAULT NULL,
  `behavior_4` varchar(255) DEFAULT NULL,
  `b4_q1` varchar(5) DEFAULT NULL,
  `b4_q2` varchar(5) DEFAULT NULL,
  `b4_q3` varchar(5) DEFAULT NULL,
  `b4_q4` varchar(5) DEFAULT NULL,
  `behavior_5` varchar(255) DEFAULT NULL,
  `b5_q1` varchar(5) DEFAULT NULL,
  `b5_q2` varchar(5) DEFAULT NULL,
  `b5_q3` varchar(5) DEFAULT NULL,
  `b5_q4` varchar(5) DEFAULT NULL,
  `behavior_6` varchar(255) DEFAULT NULL,
  `b6_q1` varchar(5) DEFAULT NULL,
  `b6_q2` varchar(5) DEFAULT NULL,
  `b6_q3` varchar(5) DEFAULT NULL,
  `b6_q4` varchar(5) DEFAULT NULL,
  `behavior_7` varchar(255) DEFAULT NULL,
  `b7_q1` varchar(5) DEFAULT NULL,
  `b7_q2` varchar(5) DEFAULT NULL,
  `b7_q3` varchar(5) DEFAULT NULL,
  `b7_q4` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sf10_data`
--

CREATE TABLE `sf10_data` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `days_present` int(11) DEFAULT 0,
  `days_absent` int(11) DEFAULT 0,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `school_id` varchar(50) DEFAULT NULL,
  `school_address` varchar(255) DEFAULT NULL,
  `kinder_progress_report` tinyint(1) DEFAULT NULL,
  `eccd_checklist` tinyint(1) DEFAULT NULL,
  `kinder_certificate` tinyint(1) DEFAULT NULL,
  `pept_passer` tinyint(1) DEFAULT NULL,
  `pept_text` varchar(50) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `others_check` tinyint(1) DEFAULT NULL,
  `others_text` varchar(255) DEFAULT NULL,
  `testing_center_name` varchar(255) DEFAULT NULL,
  `testing_center_address` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sf10_remedial_class`
--

CREATE TABLE `sf10_remedial_class` (
  `sf10_rem_id` int(11) NOT NULL,
  `school_year` varchar(255) DEFAULT NULL,
  `sf10_data_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sf_add_data`
--

CREATE TABLE `sf_add_data` (
  `sf_add_data_id` int(11) NOT NULL,
  `sf_type` varchar(20) DEFAULT NULL,
  `school_id` varchar(20) DEFAULT NULL,
  `school_name` varchar(100) DEFAULT NULL,
  `Division` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `sy_id` int(11) DEFAULT NULL,
  `district` varchar(20) DEFAULT NULL,
  `report_for_the_month_of` date DEFAULT NULL,
  `Previous_Month` varchar(12) DEFAULT NULL,
  `For_the_month` varchar(12) DEFAULT NULL,
  `Cumulative_as_of_End_of_Month` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `guardian_id` int(11) DEFAULT NULL,
  `enrolment_status` enum('active','transferred_in','not_active','transferred_out','dropped','pending','rejected') DEFAULT 'pending',
  `lrn` varchar(12) NOT NULL,
  `fname` varchar(150) NOT NULL,
  `mname` varchar(150) NOT NULL,
  `lname` varchar(150) NOT NULL,
  `suffix` varchar(5) NOT NULL,
  `sex` enum('MALE','FEMALE') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `isMovingUP` tinyint(1) DEFAULT NULL,
  `birthplace` varchar(150) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gradeLevel` varchar(10) DEFAULT NULL,
  `enrolled_date` datetime NOT NULL DEFAULT current_timestamp(),
  `student_profile` varchar(255) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(4,2) DEFAULT NULL,
  `height_squared` decimal(4,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `bmi_category` varchar(64) DEFAULT NULL,
  `hfa` varchar(64) DEFAULT NULL,
  `medical_remarks` varchar(255) DEFAULT NULL,
  `medical_updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stuenrolmentinfo`
--

CREATE TABLE `stuenrolmentinfo` (
  `stuEnrolmentInfo_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `mother_tongue` varchar(50) DEFAULT NULL,
  `house_no` varchar(50) DEFAULT NULL,
  `street` varchar(50) DEFAULT NULL,
  `barnagay` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `zip_code` varchar(50) DEFAULT NULL,
  `diagnosis` varchar(50) DEFAULT NULL,
  `manifestations` varchar(50) DEFAULT NULL,
  `pwd_id` varchar(50) DEFAULT NULL,
  `balik_aral` varchar(50) DEFAULT NULL,
  `learning_mode` varchar(50) DEFAULT NULL,
  `indigenous_people` varchar(50) DEFAULT NULL,
  `fourPs` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `grade_level` varchar(7) NOT NULL,
  `subject_name` varchar(50) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_units` int(11) NOT NULL,
  `subjects_status` enum('Available','Unavailable') DEFAULT 'Available',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `suffix` varchar(5) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(14) NOT NULL,
  `gender` enum('MALE','FEMALE') DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role` enum('PARENT','TEACHER') DEFAULT NULL,
  `relationship` enum('Father','Mother','Guardian') DEFAULT NULL,
  `student_profile` varchar(255) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_history`
--

CREATE TABLE `users_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `admin` ( `admin_firstname`, `admin_middlename`, `admin_lastname`, `admin_suffix`, `admin_picture`, `admin_email`, `admin_username`, `admin_password`, `admin_user_role`) 
VALUES( 'Administrator', 'Administrator', 'Administrator', '', '', 'stamariaenrollmentsystem@gmail.com', 'admin', '$2y$10$8KCwOtNLe.em638llio8rew./p1/Fe6NKoHncV02wKK691pk8pOd.', 'admin');
--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `admin_history`
--
ALTER TABLE `admin_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `adviser_id` (`adviser_id`),
  ADD KEY `school_year_id` (`school_year_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `adviser_id` (`adviser_id`),
  ADD KEY `sy_id` (`sy_id`),
  ADD KEY `classroom_id` (`classroom_id`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `enrolment`
--
ALTER TABLE `enrolment`
  ADD PRIMARY KEY (`enrolment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `adviser_id` (`adviser_id`),
  ADD KEY `school_year_id` (`school_year_id`);

--
-- Indexes for table `enrolment_subjects`
--
ALTER TABLE `enrolment_subjects`
  ADD PRIMARY KEY (`enrolment_subjects_id`),
  ADD KEY `subjects_id` (`subjects_id`),
  ADD KEY `enrolment_id` (`enrolment_id`);

--
-- Indexes for table `feeback`
--
ALTER TABLE `feeback`
  ADD PRIMARY KEY (`feeback_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `parents_info`
--
ALTER TABLE `parents_info`
  ADD PRIMARY KEY (`parents_info_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `remedial_class`
--
ALTER TABLE `remedial_class`
  ADD PRIMARY KEY (`remedial_id`),
  ADD KEY `sf10_rem_id` (`sf10_rem_id`);

--
-- Indexes for table `school_year`
--
ALTER TABLE `school_year`
  ADD PRIMARY KEY (`school_year_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `sf5_data`
--
ALTER TABLE `sf5_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sf9_data`
--
ALTER TABLE `sf9_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `sf10_data`
--
ALTER TABLE `sf10_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sf10_remedial_class`
--
ALTER TABLE `sf10_remedial_class`
  ADD PRIMARY KEY (`sf10_rem_id`),
  ADD KEY `sf10_data_id` (`sf10_data_id`);

--
-- Indexes for table `sf_add_data`
--
ALTER TABLE `sf_add_data`
  ADD PRIMARY KEY (`sf_add_data_id`),
  ADD KEY `sy_id` (`sy_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `guardian_id` (`guardian_id`);

--
-- Indexes for table `stuenrolmentinfo`
--
ALTER TABLE `stuenrolmentinfo`
  ADD PRIMARY KEY (`stuEnrolmentInfo_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `users_history`
--
ALTER TABLE `users_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_history`
--
ALTER TABLE `admin_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrolment`
--
ALTER TABLE `enrolment`
  MODIFY `enrolment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrolment_subjects`
--
ALTER TABLE `enrolment_subjects`
  MODIFY `enrolment_subjects_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feeback`
--
ALTER TABLE `feeback`
  MODIFY `feeback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents_info`
--
ALTER TABLE `parents_info`
  MODIFY `parents_info_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remedial_class`
--
ALTER TABLE `remedial_class`
  MODIFY `remedial_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `school_year_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sf5_data`
--
ALTER TABLE `sf5_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sf9_data`
--
ALTER TABLE `sf9_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sf10_data`
--
ALTER TABLE `sf10_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sf10_remedial_class`
--
ALTER TABLE `sf10_remedial_class`
  MODIFY `sf10_rem_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sf_add_data`
--
ALTER TABLE `sf_add_data`
  MODIFY `sf_add_data_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stuenrolmentinfo`
--
ALTER TABLE `stuenrolmentinfo`
  MODIFY `stuEnrolmentInfo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_history`
--
ALTER TABLE `users_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_history`
--
ALTER TABLE `admin_history`
  ADD CONSTRAINT `admin_history_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`school_year_id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`sy_id`) REFERENCES `school_year` (`school_year_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_4` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`room_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrolment`
--
ALTER TABLE `enrolment`
  ADD CONSTRAINT `enrolment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrolment_ibfk_2` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrolment_ibfk_3` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`school_year_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrolment_subjects`
--
ALTER TABLE `enrolment_subjects`
  ADD CONSTRAINT `enrolment_subjects_ibfk_1` FOREIGN KEY (`subjects_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrolment_subjects_ibfk_2` FOREIGN KEY (`enrolment_id`) REFERENCES `enrolment` (`enrolment_id`) ON DELETE CASCADE;

--
-- Constraints for table `feeback`
--
ALTER TABLE `feeback`
  ADD CONSTRAINT `feeback_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `parents_info`
--
ALTER TABLE `parents_info`
  ADD CONSTRAINT `parents_info_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `remedial_class`
--
ALTER TABLE `remedial_class`
  ADD CONSTRAINT `remedial_class_ibfk_1` FOREIGN KEY (`sf10_rem_id`) REFERENCES `sf10_remedial_class` (`sf10_rem_id`) ON DELETE CASCADE;

--
-- Constraints for table `sf9_data`
--
ALTER TABLE `sf9_data`
  ADD CONSTRAINT `sf9_data_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `sf10_remedial_class`
--
ALTER TABLE `sf10_remedial_class`
  ADD CONSTRAINT `sf10_remedial_class_ibfk_1` FOREIGN KEY (`sf10_data_id`) REFERENCES `sf10_data` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sf_add_data`
--
ALTER TABLE `sf_add_data`
  ADD CONSTRAINT `sf_add_data_ibfk_1` FOREIGN KEY (`sy_id`) REFERENCES `school_year` (`school_year_id`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`guardian_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `stuenrolmentinfo`
--
ALTER TABLE `stuenrolmentinfo`
  ADD CONSTRAINT `stuenrolmentinfo_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `users_history`
--
ALTER TABLE `users_history`
  ADD CONSTRAINT `users_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
