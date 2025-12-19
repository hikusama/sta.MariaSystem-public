-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 05:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


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
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_firstname`, `admin_middlename`, `admin_lastname`, `admin_suffix`, `admin_email`, `admin_username`, `admin_password`, `admin_user_role`, `admin_picture`, `created_date`) VALUES
(1, 'Juan', 'Cruz', 'Dela', '', 'admin@school.edu.ph', 'admin', '$2y$10$nG6bMmd7tB710V4O5HOMNOZwjYI.ic7ThNct.kZ38Dfkq6yUoiLzi', 'admin', '', '2025-09-19 13:16:50');

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

--
-- Dumping data for table `admin_history`
--

INSERT INTO `admin_history` (`id`, `admin_id`, `login_time`, `logout_time`) VALUES
(1, 1, '2025-09-21 19:05:49', NULL),
(2, 1, '2025-09-21 20:00:16', NULL),
(3, 1, '2025-09-22 23:20:43', NULL),
(4, 1, '2025-09-22 23:21:40', NULL),
(5, 1, '2025-09-22 23:22:39', NULL),
(6, 1, '2025-09-22 23:26:48', NULL),
(7, 1, '2025-09-24 05:39:24', NULL),
(8, 1, '2025-09-24 05:51:18', NULL),
(9, 1, '2025-09-24 05:55:37', NULL),
(10, 1, '2025-09-24 06:11:02', NULL),
(11, 1, '2025-09-24 06:15:16', NULL),
(12, 1, '2025-09-24 06:18:59', NULL),
(13, 1, '2025-09-24 06:21:21', NULL),
(14, 1, '2025-09-28 13:40:19', NULL),
(15, 1, '2025-09-28 23:02:29', NULL),
(16, 1, '2025-09-28 23:04:01', NULL),
(17, 1, '2025-09-29 08:19:24', NULL),
(18, 1, '2025-09-29 08:22:52', NULL),
(19, 1, '2025-09-29 08:23:07', NULL),
(20, 1, '2025-09-29 08:42:56', NULL),
(21, 1, '2025-09-29 12:33:27', NULL),
(22, 1, '2025-09-30 11:10:26', NULL),
(23, 1, '2025-10-01 17:11:48', NULL),
(24, 1, '2025-10-02 13:14:35', NULL),
(25, 1, '2025-10-03 11:27:20', NULL),
(26, 1, '2025-10-03 19:14:18', NULL),
(27, 1, '2025-10-03 20:53:24', NULL),
(28, 1, '2025-10-17 02:19:07', NULL),
(29, 1, '2025-10-17 02:20:07', NULL),
(30, 1, '2025-10-17 02:49:39', NULL),
(31, 1, '2025-10-27 10:59:37', NULL),
(32, 1, '2025-10-27 10:59:47', NULL),
(33, 1, '2025-11-01 23:08:35', NULL),
(34, 1, '2025-11-01 23:33:19', NULL),
(35, 1, '2025-11-01 23:34:49', NULL),
(36, 1, '2025-11-02 01:06:59', NULL),
(37, 1, '2025-11-05 02:38:59', NULL),
(38, 1, '2025-12-08 01:13:38', NULL),
(39, 1, '2025-12-08 01:14:24', NULL),
(40, 1, '2025-12-08 01:14:43', NULL),
(41, 1, '2025-12-08 01:14:57', NULL),
(42, 1, '2025-12-08 01:16:26', NULL),
(43, 1, '2025-12-08 18:15:49', NULL),
(44, 1, '2025-12-09 22:30:26', NULL),
(45, 1, '2025-12-09 22:32:38', NULL),
(46, 1, '2025-12-09 23:05:26', NULL),
(47, 1, '2025-12-12 00:35:30', NULL),
(48, 1, '2025-12-12 01:58:46', NULL),
(49, 1, '2025-12-14 00:11:19', NULL),
(50, 1, '2025-12-14 00:12:54', NULL),
(51, 1, '2025-12-14 00:14:34', NULL),
(52, 1, '2025-12-14 00:15:42', NULL),
(53, 1, '2025-12-14 00:30:05', NULL),
(54, 1, '2025-12-14 00:30:45', NULL),
(55, 1, '2025-12-14 00:32:30', NULL),
(56, 1, '2025-12-14 00:33:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `adviser_id` int(11) NOT NULL,
  `morning_attendance` datetime NOT NULL,
  `attendance_type` enum('Present','Absent','Late') NOT NULL,
  `afternoon_attendance` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `student_id`, `adviser_id`, `morning_attendance`, `attendance_type`, `afternoon_attendance`) VALUES
(2, 1015, 3, '2025-12-08 02:05:09', 'Present', NULL);

--
-- Triggers `attendance`
--
DELIMITER $$
CREATE TRIGGER `trg_update_sf9_after_attendance_insert` AFTER INSERT ON `attendance` FOR EACH ROW BEGIN
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
  `section_name` varchar(20) NOT NULL,
  `grade_level` varchar(10) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `section_id`, `adviser_id`, `sy_id`, `section_name`, `grade_level`, `assigned_at`) VALUES
(55, 1, 3, 4, '1A', '1', '2025-09-28 07:22:12'),
(56, 2, 3, 4, '1B', '1', '2025-09-28 07:22:12'),
(57, 3, 3, 4, '1C', '1', '2025-09-28 07:22:12'),
(58, 4, 3, 4, '2A', '2', '2025-09-28 07:22:12'),
(59, 5, 3, 4, '2B', '2', '2025-09-28 07:22:12'),
(60, 6, 3, 4, '2C', '2', '2025-09-28 07:22:12'),
(61, 7, 3, 4, '3A', '3', '2025-09-28 07:22:12'),
(62, 8, 3, 4, '3B', '3', '2025-09-28 07:22:12'),
(63, 9, 3, 4, '3C', '3', '2025-09-28 07:22:12'),
(64, 10, 3, 4, '4A', '4', '2025-09-28 07:22:12'),
(65, 11, 3, 4, '4B', '4', '2025-09-28 07:22:12'),
(66, 12, 3, 4, '4C', '4', '2025-09-28 07:22:12'),
(67, 13, 3, 4, '5A', '5', '2025-09-28 07:22:12'),
(68, 14, 3, 4, '5B', '5', '2025-09-28 07:22:12'),
(69, 15, 3, 4, '5C', '5', '2025-09-28 07:22:12'),
(70, 16, 3, 4, '6A', '6', '2025-09-28 07:22:12'),
(71, 17, 3, 4, '6B', '6', '2025-09-28 07:22:12'),
(72, 18, 3, 4, '6C', '6', '2025-09-28 07:22:12');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `room_id` int(11) NOT NULL,
  `room_status` enum('Unavailable','Available') DEFAULT 'Available',
  `room_name` varchar(50) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
-- Dumping data for table `enrolment`
--

INSERT INTO `enrolment` (`enrolment_id`, `student_id`, `section_name`, `adviser_id`, `school_year_id`, `Grade_level`, `enrolment_Status`) VALUES
(64, 1019, '1A', 3, 1, '1', 'Approved');

--
-- Triggers `enrolment`
--
DELIMITER $$
CREATE TRIGGER `after_enrolment_insert` AFTER INSERT ON `enrolment` FOR EACH ROW BEGIN
    DECLARE full_name VARCHAR(255);
    DECLARE sy_name VARCHAR(50);
    DECLARE student_full_name VARCHAR(255);
    DECLARE student_lrn VARCHAR(20);

    
    SELECT CONCAT(firstname, ' ', middlename, ' ', lastname)
    INTO full_name
    FROM users
    WHERE user_id = NEW.adviser_id;

    
    SELECT school_year_name
    INTO sy_name
    FROM school_year
    WHERE school_year_id = NEW.school_year_id;

    
    SELECT CONCAT(lname, ', ', fname, ' ', mname), lrn
    INTO student_full_name, student_lrn
    FROM student
    WHERE student_id = NEW.student_id;

    
    IF NOT EXISTS (SELECT 1 FROM sf9_data WHERE student_id = NEW.student_id) THEN
        INSERT INTO sf9_data (
            student_id,
            student_name,
            lrn,
            grade,
            section,
            school_year,
            teacher
        )
        VALUES (
            NEW.student_id,
            student_full_name,
            student_lrn,
            NEW.Grade_level,
            NEW.section_name,  
            sy_name,
            full_name
        );

        
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
        WHERE s.student_id = NEW.student_id;
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

--
-- Dumping data for table `enrolment_subjects`
--

INSERT INTO `enrolment_subjects` (`enrolment_subjects_id`, `enrolment_id`, `subjects_id`) VALUES
(146, 64, 1),
(147, 64, 2),
(148, 64, 3),
(149, 64, 4),
(150, 64, 5),
(151, 64, 6),
(152, 64, 7);

-- --------------------------------------------------------

--
-- Table structure for table `feeback`
--

CREATE TABLE `feeback` (
  `feeback_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL
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

--
-- Dumping data for table `parents_info`
--

INSERT INTO `parents_info` (`parents_info_id`, `student_id`, `f_firstname`, `f_middlename`, `f_lastname`, `f_suffix`, `m_firstname`, `m_middlename`, `m_lastname`, `g_firstname`, `g_middlename`, `g_lastname`, `g_suffix`, `g_relationship`, `p_contact`) VALUES
(1, 1021, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_year`
--

CREATE TABLE `school_year` (
  `school_year_id` int(11) NOT NULL,
  `school_year_status` enum('Active','Inactive') DEFAULT NULL,
  `school_year_name` varchar(50) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_year`
--

INSERT INTO `school_year` (`school_year_id`, `school_year_status`, `school_year_name`, `created_date`) VALUES
(1, 'Active', '2025-2026', '2025-09-28 07:20:04'),
(2, 'Inactive', '2023-2024', '2025-09-28 07:20:19'),
(3, 'Inactive', '2024-2025', '2025-09-28 07:20:19'),
(4, 'Active', '2025-2026', '2025-09-28 07:20:19');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `section_grade_level` varchar(7) NOT NULL,
  `section_status` enum('Available','Inavailable') DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `section_grade_level`, `section_status`, `created_date`) VALUES
(1, 'A', '1', 'Available', '2025-10-12 20:43:55'),
(2, 'B', '1', 'Available', '2025-10-12 20:43:58'),
(3, 'C', '1', 'Inavailable', '2025-10-12 20:44:11'),
(4, 'A', '2', 'Available', '2025-11-01 08:06:10'),
(5, 'B', '2', 'Available', '2025-10-12 20:44:14'),
(6, 'C', '2', 'Inavailable', '2025-10-12 20:44:17'),
(7, 'A', '3', 'Available', '2025-10-12 20:44:19'),
(8, 'B', '3', 'Available', '2025-10-12 20:44:22'),
(9, 'C', '3', 'Inavailable', '2025-10-12 20:44:24'),
(10, 'A', '4', 'Available', '2025-10-12 20:44:28'),
(11, 'B', '4', 'Available', '2025-10-12 20:44:31'),
(12, 'C', '4', 'Inavailable', '2025-10-12 20:44:34'),
(13, 'A', '5', 'Available', '2025-10-12 20:44:37'),
(14, 'B', '5', 'Available', '2025-10-12 20:44:39'),
(15, 'C', '5', 'Inavailable', '2025-10-12 20:44:41'),
(16, 'A', '6', 'Available', '2025-10-12 20:44:43'),
(17, 'B', '6', 'Available', '2025-10-12 20:44:48'),
(18, 'C', '6', 'Inavailable', '2025-10-12 20:44:52');

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
  `conditional_male` int(11) DEFAULT NULL,
  `conditional_female` int(11) DEFAULT NULL,
  `conditional_total` int(11) DEFAULT NULL,
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
  `learners` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`learners`)),
  `action_taken` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sf5_data`
--

INSERT INTO `sf5_data` (`id`, `school_year`, `curriculum`, `grade_level`, `section`, `male_total`, `female_total`, `combined_total`, `promoted_male`, `promoted_female`, `promoted_total`, `conditional_male`, `conditional_female`, `conditional_total`, `retained_male`, `retained_female`, `retained_total`, `progress_did_not_meet_male`, `progress_did_not_meet_female`, `progress_did_not_meet_total`, `progress_fairly_satisfactory_male`, `progress_fairly_satisfactory_female`, `progress_fairly_satisfactory_total`, `progress_satisfactory_male`, `progress_satisfactory_female`, `progress_satisfactory_total`, `progress_very_satisfactory_male`, `progress_very_satisfactory_female`, `progress_very_satisfactory_total`, `progress_outstanding_male`, `progress_outstanding_female`, `progress_outstanding_total`, `prepared_by`, `certified_by`, `reviewed_by`, `learners`, `action_taken`, `created_at`) VALUES
(1, '', '', '1', 'A', 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'wda', 'ewa', 'ewtr', '{\"13\":{\"lrn\":\"123456789009\",\"name\":\"Bautista, Nathan Luke\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"14\":{\"lrn\":\"123456789003\",\"name\":\"Delos Santos, Carlos D.\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"15\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"16\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"17\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"18\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"19\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"20\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"21\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"22\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"23\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"24\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"25\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"26\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"27\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"28\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"29\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"30\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"31\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"32\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"34\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"35\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"36\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"37\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"38\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"39\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"40\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"41\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"42\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"43\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"44\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"45\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"46\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"47\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"48\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"49\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"50\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"51\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"52\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"53\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"54\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"55\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"56\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"57\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"58\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"59\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"}}', NULL, '2025-10-14 17:28:40'),
(6, '', '', '2', 'A', 0, 0, 0, 43, 2, 4, 4, 5, 5, 0, 0, 0, 1, 5, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'grs', 'trs', 'tghs', '{\"13\":{\"lrn\":\"123456789009\",\"name\":\"Nathan L. Bautista\",\"average\":\"88.63\",\"action\":\"\",\"did_not_meet\":\"\"},\"14\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"15\":{\"lrn\":\"ewa\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"16\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"17\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"18\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"19\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"20\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"21\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"22\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"23\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"24\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"25\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"26\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"27\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"28\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"29\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"30\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"31\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"32\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"34\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"35\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"36\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"37\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"38\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"39\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"40\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"41\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"42\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"43\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"44\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"45\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"46\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"47\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"48\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"49\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"50\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"51\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"52\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"53\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"54\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"55\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"56\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"57\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"58\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"},\"59\":{\"lrn\":\"\",\"name\":\"\",\"average\":\"\",\"action\":\"\",\"did_not_meet\":\"\"}}', '{\"13\":\"PROMOTED\",\"14\":\"\",\"15\":\"\",\"16\":\"\",\"17\":\"\",\"18\":\"\",\"19\":\"\",\"20\":\"\",\"21\":\"\",\"22\":\"\",\"23\":\"\",\"24\":\"\",\"25\":\"\",\"26\":\"\",\"27\":\"\",\"28\":\"\",\"29\":\"\",\"30\":\"\",\"31\":\"\",\"32\":\"\",\"34\":\"\",\"35\":\"\",\"36\":\"\",\"37\":\"\",\"38\":\"\",\"39\":\"\",\"40\":\"\",\"41\":\"\",\"42\":\"\",\"43\":\"\",\"44\":\"\",\"45\":\"\",\"46\":\"\",\"47\":\"\",\"48\":\"\",\"49\":\"\",\"50\":\"\",\"51\":\"\",\"52\":\"\",\"53\":\"\",\"54\":\"\",\"55\":\"\",\"56\":\"\",\"57\":\"\",\"58\":\"\",\"59\":\"\"}', '2025-10-15 23:49:52'),
(7, '2025-2026', NULL, '2', 'A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"13\":\"uyf\",\"14\":\"ffs\",\"15\":\"fsfsf\",\"16\":\"sefsf\",\"17\":\"\",\"18\":\"\",\"19\":\"\",\"20\":\"\",\"21\":\"\",\"22\":\"\",\"23\":\"\",\"24\":\"\",\"25\":\"\",\"26\":\"\",\"27\":\"\",\"28\":\"\",\"29\":\"\",\"30\":\"\",\"31\":\"\",\"32\":\"\",\"34\":\"\",\"35\":\"\",\"36\":\"\",\"37\":\"\",\"38\":\"\",\"39\":\"\",\"40\":\"\",\"41\":\"\",\"42\":\"\",\"43\":\"\",\"44\":\"\",\"45\":\"\",\"46\":\"\",\"47\":\"\",\"48\":\"\",\"49\":\"\",\"50\":\"\",\"51\":\"\",\"52\":\"\",\"53\":\"\",\"54\":\"\",\"55\":\"\",\"56\":\"\",\"57\":\"\",\"58\":\"\",\"59\":\"\"}', '2025-11-01 17:09:42'),
(8, '', NULL, '1', 'C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"13\":\"CONDITIONAL\",\"14\":\"\",\"15\":\"\",\"16\":\"\",\"17\":\"\",\"18\":\"\",\"19\":\"\",\"20\":\"\",\"21\":\"\",\"22\":\"\",\"23\":\"\",\"24\":\"\",\"25\":\"\",\"26\":\"\",\"27\":\"\",\"28\":\"\",\"29\":\"\",\"30\":\"\",\"31\":\"\",\"32\":\"\",\"34\":\"\",\"35\":\"\",\"36\":\"\",\"37\":\"\",\"38\":\"\",\"39\":\"\",\"40\":\"\",\"41\":\"\",\"42\":\"\",\"43\":\"\",\"44\":\"\",\"45\":\"\",\"46\":\"\",\"47\":\"\",\"48\":\"\",\"49\":\"\",\"50\":\"\",\"51\":\"\",\"52\":\"\",\"53\":\"\",\"54\":\"\",\"55\":\"\",\"56\":\"\",\"57\":\"\",\"58\":\"\",\"59\":\"\"}', '2025-11-30 14:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `sf9_data`
--

CREATE TABLE `sf9_data` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `student_name` varchar(150) DEFAULT NULL,
  `lrn` varchar(30) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `teacher` varchar(100) DEFAULT NULL,
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

--
-- Dumping data for table `sf9_data`
--

INSERT INTO `sf9_data` (`id`, `student_id`, `student_name`, `lrn`, `age`, `sex`, `grade`, `section`, `school_year`, `teacher`, `guardian`, `days_school_june`, `days_present_june`, `days_absent_june`, `days_school_july`, `days_present_july`, `days_absent_july`, `days_school_aug`, `days_present_aug`, `days_absent_aug`, `days_school_sep`, `days_present_sep`, `days_absent_sep`, `days_school_oct`, `days_present_oct`, `days_absent_oct`, `days_school_nov`, `days_present_nov`, `days_absent_nov`, `days_school_dec`, `days_present_dec`, `days_absent_dec`, `days_school_jan`, `days_present_jan`, `days_absent_jan`, `days_school_feb`, `days_present_feb`, `days_absent_feb`, `days_school_mar`, `days_present_mar`, `days_absent_mar`, `days_school_apr`, `days_present_apr`, `days_absent_apr`, `subject_1`, `q1_1`, `q2_1`, `q3_1`, `q4_1`, `final_1`, `remarks_1`, `subject_2`, `q1_2`, `q2_2`, `q3_2`, `q4_2`, `final_2`, `remarks_2`, `subject_3`, `q1_3`, `q2_3`, `q3_3`, `q4_3`, `final_3`, `remarks_3`, `subject_4`, `q1_4`, `q2_4`, `q3_4`, `q4_4`, `final_4`, `remarks_4`, `subject_5`, `q1_5`, `q2_5`, `q3_5`, `q4_5`, `final_5`, `remarks_5`, `subject_6`, `q1_6`, `q2_6`, `q3_6`, `q4_6`, `final_6`, `remarks_6`, `subject_7`, `q1_7`, `q2_7`, `q3_7`, `q4_7`, `final_7`, `remarks_7`, `subject_8`, `q1_8`, `q2_8`, `q3_8`, `q4_8`, `final_8`, `remarks_8`, `subject_9`, `q1_9`, `q2_9`, `q3_9`, `q4_9`, `final_9`, `remarks_9`, `subject_10`, `q1_10`, `q2_10`, `q3_10`, `q4_10`, `final_10`, `remarks_10`, `subject_11`, `q1_11`, `q2_11`, `q3_11`, `q4_11`, `final_11`, `remarks_11`, `subject_12`, `q1_12`, `q2_12`, `q3_12`, `q4_12`, `final_12`, `remarks_12`, `subject_13`, `q1_13`, `q2_13`, `q3_13`, `q4_13`, `final_13`, `remarks_13`, `subject_14`, `q1_14`, `q2_14`, `q3_14`, `q4_14`, `final_14`, `remarks_14`, `subject_15`, `q1_15`, `q2_15`, `q3_15`, `q4_15`, `final_15`, `remarks_15`, `general_average`, `behavior_1`, `b1_q1`, `b1_q2`, `b1_q3`, `b1_q4`, `behavior_2`, `b2_q1`, `b2_q2`, `b2_q3`, `b2_q4`, `behavior_3`, `b3_q1`, `b3_q2`, `b3_q3`, `b3_q4`, `behavior_4`, `b4_q1`, `b4_q2`, `b4_q3`, `b4_q4`, `behavior_5`, `b5_q1`, `b5_q2`, `b5_q3`, `b5_q4`, `behavior_6`, `b6_q1`, `b6_q2`, `b6_q3`, `b6_q4`, `behavior_7`, `b7_q1`, `b7_q2`, `b7_q3`, `b7_q4`, `created_at`) VALUES
(48, 1015, 'Joshua M. Garcia', '123456789005', 7, 'MALE', '2', '', '2025-2026', 'Maam', 'N/A', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, NULL, NULL, NULL, NULL, 'English', NULL, NULL, NULL, NULL, NULL, NULL, 'Mathematics', NULL, NULL, NULL, NULL, NULL, NULL, 'Araling Panlipunan', NULL, NULL, NULL, NULL, NULL, NULL, 'Edukasyon sa Pagpapakatao', NULL, NULL, NULL, NULL, NULL, NULL, 'MAPEH', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', '', '', '', '', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', '', 'Is sensitive to individual, social, and cultural differences.', '', '', '', '', 'Demonstrates contributions towards solidarity.', '', '', '', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', '', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', '', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', '', '', '2025-12-05 21:13:48'),
(49, 1014, 'Angela M. Cruz', '123456789004', 9, 'FEMALE', '4', '', '2025-2026', 'Maam', 'N/A', 22, 8, 14, 23, 4, 19, 15, 2, 13, 15, 2, 13, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, NULL, NULL, NULL, NULL, 'English', NULL, NULL, NULL, NULL, NULL, NULL, 'Mathematics', NULL, NULL, NULL, NULL, NULL, NULL, 'Araling Panlipunan', NULL, NULL, NULL, NULL, NULL, NULL, 'Edukasyon sa Pagpapakatao', NULL, NULL, NULL, NULL, NULL, NULL, 'MAPEH', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', '', '', '', '', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', '', 'Is sensitive to individual, social, and cultural differences.', '', '', '', '', 'Demonstrates contributions towards solidarity.', '', '', '', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', '', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', '', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', '', '', '2025-12-05 21:13:28'),
(50, 1018, 'Sofia G. Dela Cruz', '123456789008', 3, 'FEMALE', '2', '', '32', '332', 'Marcjohn Dave A. Calunod', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, NULL, NULL, NULL, NULL, 'English', NULL, NULL, NULL, NULL, NULL, NULL, 'Mathematics', NULL, NULL, NULL, NULL, NULL, NULL, 'Araling Panlipunan', NULL, NULL, NULL, NULL, NULL, NULL, 'Edukasyon sa Pagpapakatao', NULL, NULL, NULL, NULL, NULL, NULL, 'MAPEH', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', '', '', '', '', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', '', 'Is sensitive to individual, social, and cultural differences.', '', '', '', '', 'Demonstrates contributions towards solidarity.', '', '', '', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', '', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', '', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', '', '', '2025-12-05 21:13:38'),
(51, 1013, 'Carlos D. Delos Santos', '123456789003', NULL, 'MALE', '1', '', '', '', 'N/A', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, NULL, NULL, NULL, NULL, 'English', NULL, NULL, NULL, NULL, NULL, NULL, 'Mathematics', NULL, NULL, NULL, NULL, NULL, NULL, 'Araling Panlipunan', NULL, NULL, NULL, NULL, NULL, NULL, 'Edukasyon sa Pagpapakatao', NULL, NULL, NULL, NULL, NULL, NULL, 'MAPEH', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', '', '', '', '', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', '', 'Is sensitive to individual, social, and cultural differences.', '', '', '', '', 'Demonstrates contributions towards solidarity.', '', '', '', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', '', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', '', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', '', '', '2025-12-05 21:13:43'),
(52, 1016, 'Patricia A. Lopez', '123456789006', NULL, 'FEMALE', '3', '', '', '', 'N/A', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, NULL, NULL, NULL, NULL, 'English', NULL, NULL, NULL, NULL, NULL, NULL, 'Mathematics', NULL, NULL, NULL, NULL, NULL, NULL, 'Araling Panlipunan', NULL, NULL, NULL, NULL, NULL, NULL, 'Edukasyon sa Pagpapakatao', NULL, NULL, NULL, NULL, NULL, NULL, 'MAPEH', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', '', '', '', '', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', '', 'Is sensitive to individual, social, and cultural differences.', '', '', '', '', 'Demonstrates contributions towards solidarity.', '', '', '', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', '', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', '', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', '', '', '2025-12-05 21:13:56'),
(99, 1011, 'John P. Santos', '123456789001', NULL, 'MALE', '6', '', '', '', 'N/A', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', '', '', '', '', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', '', 'Is sensitive to individual, social, and cultural differences.', '', '', '', '', 'Demonstrates contributions towards solidarity.', '', '', '', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', '', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', '', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', '', '', '2025-12-05 21:21:57'),
(100, 1020, 'Dela Cruz, Juan Santos', '123456789012', NULL, NULL, '2', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', NULL, NULL, NULL, NULL, NULL, NULL, 'Filipino', NULL, NULL, NULL, NULL, NULL, NULL, 'English', NULL, NULL, NULL, NULL, NULL, NULL, 'Mathematics', NULL, NULL, NULL, NULL, NULL, NULL, 'Araling Panlipunan', NULL, NULL, NULL, NULL, NULL, NULL, 'Edukasyon sa Pagpapakatao', NULL, NULL, NULL, NULL, NULL, NULL, 'MAPEH (Music, Arts, PE, Health)', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-11 16:44:42'),
(108, 1012, 'Maria L. Reyes', '123456789002', NULL, 'FEMALE', '3', '', '2025-2026', 'Lebron D. James', 'N/A', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', '', '', '', '', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', '', 'Is sensitive to individual, social, and cultural differences.', '', '', '', '', 'Demonstrates contributions towards solidarity.', '', '', '', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', '', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', '', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', '', '', '2025-12-13 16:28:24'),
(110, 1021, NULL, NULL, NULL, NULL, 'Grade 3', '3A', '2025-2026', 'Lebron D. James', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-13 16:33:53'),
(114, 1019, 'Nathan L. Bautista', '123456789009', NULL, 'MALE', '1', '', '2025-2026', 'Lebron D. James', 'Lebron D. James Jr', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', 80.00, 89.00, 90.00, 95.00, 88.50, 'PASSED', 'Filipino', 82.00, 86.00, 89.00, 89.00, 86.50, 'PASSED', 'English', 90.00, 90.00, 97.00, 99.00, 94.00, 'PASSED', 'Mathematics', 89.00, 87.00, 99.00, 90.00, 91.25, 'PASSED', 'Araling Panlipunan', 80.00, 86.00, 88.00, 90.00, 86.00, 'PASSED', 'Edukasyon sa Pagpapakatao', 90.00, 90.00, 90.00, 90.00, 90.00, 'PASSED', 'MAPEH (Music, Arts, PE, Health)', 90.00, 90.00, 90.00, 90.00, 90.00, 'PASSED', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 89.46, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', 'AO', '', '', 'SO', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', 'SO', 'Is sensitive to individual, social, and cultural differences.', 'SO', 'AO', '', '', 'Demonstrates contributions towards solidarity.', '', '', 'SO', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', 'SO', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', 'SO', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', 'NO', '', '2025-12-19 14:24:23'),
(115, 1019, 'Nathan L. Bautista', '123456789009', NULL, 'MALE', '2', '', '2025-2026', 'Lebron D. James', 'Lebron D. James Jr', 12, 10, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Mother Tongue', 85.00, 88.00, 95.00, 99.00, 91.75, 'PASSED', 'Filipino', 82.00, 86.00, 89.00, 89.00, 86.50, 'PASSED', 'English', 90.00, 90.00, 97.00, 99.00, 94.00, 'PASSED', 'Mathematics', 89.00, 87.00, 99.00, 90.00, 91.25, 'PASSED', 'Araling Panlipunan', 80.00, 86.00, 88.00, 90.00, 86.00, 'PASSED', 'Edukasyon sa Pagpapakatao', 90.00, 90.00, 90.00, 90.00, 90.00, 'PASSED', 'MAPEH (Music, Arts, PE, Health)', 90.00, 90.00, 90.00, 90.00, 90.00, 'PASSED', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 89.93, 'Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.', 'AO', '', '', 'SO', 'Shows adherence to ethical principles by upholding truth in all undertakings.', '', '', '', 'SO', 'Is sensitive to individual, social, and cultural differences.', 'SO', 'AO', '', '', 'Demonstrates contributions towards solidarity.', '', '', 'SO', '', 'Cares for environment and utilizes resources wisely, judiciously and economically.', '', '', '', 'SO', 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.', '', 'SO', '', '', 'Demonstrates appropriate behavior in carrying out activities in school, community and country.', '', '', 'NO', '', '2025-12-19 16:34:23');

-- --------------------------------------------------------

--
-- Table structure for table `sf10_data`
--

CREATE TABLE `sf10_data` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
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
  `scholastic_records` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scholastic_records`)),
  `grade` varchar(10) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `adviser` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rem1_area` varchar(255) DEFAULT NULL,
  `rem1_final` varchar(50) DEFAULT NULL,
  `rem1_class_mark` varchar(50) DEFAULT NULL,
  `rem1_recomputed` varchar(50) DEFAULT NULL,
  `rem1_remarks` varchar(255) DEFAULT NULL,
  `rem2_area` varchar(255) DEFAULT NULL,
  `rem2_final` varchar(50) DEFAULT NULL,
  `rem2_class_mark` varchar(50) DEFAULT NULL,
  `rem2_recomputed` varchar(50) DEFAULT NULL,
  `rem2_remarks` varchar(255) DEFAULT NULL,
  `rem3_area` varchar(255) DEFAULT NULL,
  `rem3_final` varchar(50) DEFAULT NULL,
  `rem3_class_mark` varchar(50) DEFAULT NULL,
  `rem3_recomputed` varchar(50) DEFAULT NULL,
  `rem3_remarks` varchar(255) DEFAULT NULL,
  `rem4_area` varchar(255) DEFAULT NULL,
  `rem4_final` varchar(50) DEFAULT NULL,
  `rem4_class_mark` varchar(50) DEFAULT NULL,
  `rem4_recomputed` varchar(50) DEFAULT NULL,
  `rem4_remarks` varchar(255) DEFAULT NULL,
  `rem5_area` varchar(255) DEFAULT NULL,
  `rem5_final` varchar(50) DEFAULT NULL,
  `rem5_class_mark` varchar(50) DEFAULT NULL,
  `rem5_recomputed` varchar(50) DEFAULT NULL,
  `rem5_remarks` varchar(255) DEFAULT NULL,
  `rem6_area` varchar(255) DEFAULT NULL,
  `rem6_final` varchar(50) DEFAULT NULL,
  `rem6_class_mark` varchar(50) DEFAULT NULL,
  `rem6_recomputed` varchar(50) DEFAULT NULL,
  `rem6_remarks` varchar(255) DEFAULT NULL,
  `rem7_area` varchar(255) DEFAULT NULL,
  `rem7_final` varchar(50) DEFAULT NULL,
  `rem7_class_mark` varchar(50) DEFAULT NULL,
  `rem7_recomputed` varchar(50) DEFAULT NULL,
  `rem7_remarks` varchar(255) DEFAULT NULL,
  `rem8_area` varchar(255) DEFAULT NULL,
  `rem8_final` varchar(50) DEFAULT NULL,
  `rem8_class_mark` varchar(50) DEFAULT NULL,
  `rem8_recomputed` varchar(50) DEFAULT NULL,
  `rem8_remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sf10_data`
--

INSERT INTO `sf10_data` (`id`, `student_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `lrn`, `birthdate`, `sex`, `school_name`, `school_id`, `school_address`, `kinder_progress_report`, `eccd_checklist`, `kinder_certificate`, `pept_passer`, `pept_text`, `exam_date`, `others_check`, `others_text`, `testing_center_name`, `testing_center_address`, `remark`, `scholastic_records`, `grade`, `section`, `school_year`, `adviser`, `created_at`, `rem1_area`, `rem1_final`, `rem1_class_mark`, `rem1_recomputed`, `rem1_remarks`, `rem2_area`, `rem2_final`, `rem2_class_mark`, `rem2_recomputed`, `rem2_remarks`, `rem3_area`, `rem3_final`, `rem3_class_mark`, `rem3_recomputed`, `rem3_remarks`, `rem4_area`, `rem4_final`, `rem4_class_mark`, `rem4_recomputed`, `rem4_remarks`, `rem5_area`, `rem5_final`, `rem5_class_mark`, `rem5_recomputed`, `rem5_remarks`, `rem6_area`, `rem6_final`, `rem6_class_mark`, `rem6_recomputed`, `rem6_remarks`, `rem7_area`, `rem7_final`, `rem7_class_mark`, `rem7_recomputed`, `rem7_remarks`, `rem8_area`, `rem8_final`, `rem8_class_mark`, `rem8_recomputed`, `rem8_remarks`) VALUES
(87, 1019, 'Bautista', 'Nathan', 'Luke', '', '123456789009', '2009-09-10', 'MALE', '3e23rfed', '111', 'erwgegv', 1, 1, 1, 0, '', '0000-00-00', 0, 'gre', 'gre', 'gregre', 'gre', '{\"grades\":{\"1\":\"3\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"sections\":{\"1\":\"r32r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"school_years\":{\"1\":\"3r2r32\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"advisers\":{\"1\":\"rr3r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"learning_areas\":{\"1\":[\"80\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q1\":{\"1\":[\"90\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q2\":{\"1\":[\"99\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q3\":{\"1\":[\"98\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q4\":{\"1\":[\"99\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"final_ratings\":{\"1\":[96.5,\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"remarks\":{\"1\":[\"passed\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"general_averages\":{\"1\":96.5,\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"}}', NULL, NULL, NULL, NULL, '2025-12-15 18:39:17', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(88, 1019, 'Bautista', 'Nathan', 'Luke', '', '123456789009', '2009-09-10', 'MALE', '3e23rfed', '111', 'erwgegv', 1, 1, 1, 0, '', '0000-00-00', 0, 'gre', 'gre', 'gregre', 'gre', '{\"grades\":{\"1\":\"3\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"sections\":{\"1\":\"r32r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"school_years\":{\"1\":\"3r2r32\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"advisers\":{\"1\":\"rr3r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"learning_areas\":{\"1\":[\"80\",\"78\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q1\":{\"1\":[\"90\",\"88\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q2\":{\"1\":[\"99\",\"98\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q3\":{\"1\":[\"98\",\"90\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q4\":{\"1\":[\"99\",\"99\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"final_ratings\":{\"1\":[96.5,93.75,\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"remarks\":{\"1\":[\"passed\",\"passed\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"general_averages\":{\"1\":95.13,\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"}}', NULL, NULL, NULL, NULL, '2025-12-15 19:15:22', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(89, 1019, 'Bautista', 'Nathan', 'Luke', '', '123456789009', '2009-09-10', 'MALE', '3e23rfed', '111', 'erwgegv', 1, 1, 1, 0, '', '0000-00-00', 0, 'gre', 'gre', 'gregre', 'gre', '{\"grades\":{\"1\":\"3\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"sections\":{\"1\":\"r32r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"school_years\":{\"1\":\"3r2r32\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"advisers\":{\"1\":\"rr3r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"learning_areas\":{\"1\":[\"80\",\"78\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q1\":{\"1\":[\"90\",\"88\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q2\":{\"1\":[\"99\",\"98\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q3\":{\"1\":[\"98\",\"90\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q4\":{\"1\":[\"99\",\"99\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"final_ratings\":{\"1\":[96.5,93.75,\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"remarks\":{\"1\":[\"passed\",\"passed\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"general_averages\":{\"1\":95.13,\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"}}', NULL, NULL, NULL, NULL, '2025-12-15 19:16:02', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(90, 1019, 'Bautista', 'Nathan', 'Luke', '', '123456789009', '2009-09-10', 'MALE', '3e23rfed', '111', 'erwgegv', 1, 1, 1, 0, '', '0000-00-00', 0, 'gre', 'gre', 'gregre', 'gre', '{\"grades\":{\"1\":\"3\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"sections\":{\"1\":\"r32r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"school_years\":{\"1\":\"3r2r32\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"advisers\":{\"1\":\"rr3r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"learning_areas\":{\"1\":[\"80\",\"70\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q1\":{\"1\":[\"90\",\"88\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q2\":{\"1\":[\"99\",\"98\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q3\":{\"1\":[\"98\",\"90\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q4\":{\"1\":[\"99\",\"99\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"final_ratings\":{\"1\":[96.5,93.75,\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"remarks\":{\"1\":[\"passed\",\"passed\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"general_averages\":{\"1\":95.13,\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"}}', NULL, NULL, NULL, NULL, '2025-12-15 19:16:27', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(91, 1019, 'Bautista', 'Nathan', 'Luke', '', '123456789009', '2009-09-10', 'MALE', '3e23rfed', '111', 'erwgegv', 1, 1, 1, 0, '', '0000-00-00', 0, 'gre', 'gre', 'gregre', 'gre', '{\"grades\":{\"1\":\"3\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"sections\":{\"1\":\"r32r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"school_years\":{\"1\":\"3r2r32\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"advisers\":{\"1\":\"rr3r\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"},\"learning_areas\":{\"1\":[\"80\",\"70\",\"89\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q1\":{\"1\":[\"90\",\"88\",\"87\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q2\":{\"1\":[\"99\",\"98\",\"99\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q3\":{\"1\":[\"98\",\"90\",\"99\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"q4\":{\"1\":[\"99\",\"99\",\"90\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"final_ratings\":{\"1\":[96.5,93.75,93.75,\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"remarks\":{\"1\":[\"passed\",\"passed\",\"passed\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"2\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"3\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"4\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"5\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"6\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"7\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"8\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"]},\"general_averages\":{\"1\":94.67,\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\",\"6\":\"\",\"7\":\"\",\"8\":\"\"}}', NULL, NULL, NULL, NULL, '2025-12-15 19:17:36', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

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

--
-- Dumping data for table `sf_add_data`
--

INSERT INTO `sf_add_data` (`sf_add_data_id`, `sf_type`, `school_id`, `school_name`, `Division`, `region`, `sy_id`, `district`, `report_for_the_month_of`, `Previous_Month`, `For_the_month`, `Cumulative_as_of_End_of_Month`) VALUES
(1, 'sf_1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'sf_2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'sf_4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'sf_8', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `guardian_id` int(11) DEFAULT NULL,
  `enrolment_status` enum('active','transferred','dropped','pending') DEFAULT 'pending',
  `lrn` varchar(12) NOT NULL,
  `fname` varchar(150) NOT NULL,
  `mname` varchar(150) NOT NULL,
  `lname` varchar(150) NOT NULL,
  `suffix` varchar(5) NOT NULL,
  `sex` enum('MALE','FEMALE') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `birthplace` varchar(150) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `gradeLevel` varchar(10) DEFAULT NULL,
  `enrolled_date` datetime NOT NULL DEFAULT current_timestamp(),
  `student_profile` blob DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `height_squared` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `guardian_id`, `enrolment_status`, `lrn`, `fname`, `mname`, `lname`, `suffix`, `sex`, `birthdate`, `birthplace`, `religion`, `address`, `gradeLevel`, `enrolled_date`, `student_profile`, `weight`, `height`, `height_squared`) VALUES
(1, 2, 'active', '123456789012', 'Kanye West', 'D.', 'Santos', '', 'MALE', '2017-03-30', 'Pasay City', 'None', 'Pasay City', '2', '2025-09-01 00:00:00', 0x6572656e5f70726f66696c652e6a7067, NULL, NULL, NULL),
(1011, 4, 'active', '123456789001', 'John', 'Paul', 'Santos', '', 'MALE', '2010-05-14', 'Quezon City', 'Catholic', '123 Mabini St, QC', '6', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1012, 4, 'pending', '123456789002', 'Maria', 'Luz', 'Reyes', '', 'FEMALE', '2009-08-21', 'Pasig City', 'Catholic', '45 Luna St, Pasig', '3', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1013, 5, 'active', '123456789003', 'Carlos', 'D.', 'Delos Santos', '', 'MALE', '2011-02-11', 'Manila', 'Christian', '789 Rizal Ave, Manila', '1', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1014, 4, 'active', '123456789004', 'Angela', 'Mae', 'Cruz', '', 'FEMALE', '2010-11-25', 'Caloocan', 'Catholic', '56 Bonifacio St, Caloocan', '4', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1015, 5, 'active', '123456789005', 'Joshua', 'M.', 'Garcia', '', 'MALE', '2008-04-19', 'Taguig City', 'Catholic', '98 San Pedro, Taguig', '2', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1016, 4, 'active', '123456789006', 'Patricia', 'Anne', 'Lopez', '', 'FEMALE', '2009-12-03', 'Makati City', 'Christian', '21 Burgos St, Makati', '3', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1017, 5, 'active', '123456789007', 'Michael', 'James', 'Torres', 'jr', 'MALE', '2011-06-28', 'Pasay City', 'Catholic', '67 Mabuhay Rd, Pasay', '5', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1019, 3, 'active', '123456789009', 'Nathan', 'Luke', 'Bautista', '', 'MALE', '2009-09-10', 'Manila', 'Christian', '210 Taft Ave, Manila', '1', '2025-10-09 00:05:51', NULL, NULL, NULL, NULL),
(1020, 3, 'active', '123456789012', 'Juan', 'Santos', 'Dela Cruz', '', 'MALE', '2010-05-15', 'Zamboanga City', 'Catholic', 'Sta. Maria', '2', '2025-12-12 00:41:16', NULL, 50.00, 160.00, 25.60),
(1021, 2, 'active', '129393937344', 'Marcjohn', 'Abao', 'Calunod', '', 'MALE', '2025-12-16', 'North Pole', 'Others', NULL, 'Grade 3', '2025-12-14 00:31:56', 0x2e2e2f6173736574732f696d6167652f75706c6f6164732f70726f66696c655f36393364393466633763643065372e39383333363730342e706e67, NULL, NULL, NULL);

--
-- Triggers `student`
--
DELIMITER $$
CREATE TRIGGER `after_student_activation` AFTER UPDATE ON `student` FOR EACH ROW BEGIN
    
    IF NEW.enrolment_status = 'active' AND OLD.enrolment_status <> 'active' THEN

        
        IF NOT EXISTS (
            SELECT 1 FROM sf9_data WHERE student_id = NEW.student_id
        ) THEN

            
            INSERT INTO sf9_data (student_id, student_name, lrn, grade)
            VALUES (
                NEW.student_id,
                CONCAT(NEW.lname, ', ', NEW.fname, ' ', NEW.mname),
                NEW.lrn,
                NEW.gradeLevel
            );

            
            UPDATE sf9_data s
            SET
                s.subject_1  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 0,1),
                s.subject_2  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 1,1),
                s.subject_3  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 2,1),
                s.subject_4  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 3,1),
                s.subject_5  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 4,1),
                s.subject_6  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 5,1),
                s.subject_7  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 6,1),
                s.subject_8  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 7,1),
                s.subject_9  = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 8,1),
                s.subject_10 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 9,1),
                s.subject_11 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 10,1),
                s.subject_12 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 11,1),
                s.subject_13 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 12,1),
                s.subject_14 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 13,1),
                s.subject_15 = (SELECT subject_name FROM subjects WHERE grade_level = NEW.gradeLevel ORDER BY subject_id LIMIT 14,1)
            WHERE s.student_id = NEW.student_id;

        END IF;

    END IF;
END
$$
DELIMITER ;

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

--
-- Dumping data for table `stuenrolmentinfo`
--

INSERT INTO `stuenrolmentinfo` (`stuEnrolmentInfo_id`, `student_id`, `mother_tongue`, `house_no`, `street`, `barnagay`, `city`, `province`, `country`, `zip_code`, `diagnosis`, `manifestations`, `pwd_id`, `balik_aral`, `learning_mode`, `indigenous_people`, `fourPs`) VALUES
(1, 1021, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `grade_level`, `subject_name`, `subject_code`, `subject_units`, `subjects_status`, `created_date`) VALUES
(1, '1', 'Mother Tongue', 'MTB1', 1, 'Available', '2025-09-28 05:06:38'),
(2, '1', 'Filipino', 'FIL1', 1, 'Available', '2025-09-28 05:06:38'),
(3, '1', 'English', 'ENG1', 1, 'Available', '2025-09-28 05:06:38'),
(4, '1', 'Mathematics', 'MATH1', 1, 'Available', '2025-09-28 05:06:38'),
(5, '1', 'Araling Panlipunan', 'AP1', 1, 'Available', '2025-09-28 05:06:38'),
(6, '1', 'Edukasyon sa Pagpapakatao', 'ESP1', 1, 'Available', '2025-09-28 05:06:38'),
(7, '1', 'MAPEH (Music, Arts, PE, Health)', 'MAPEH1', 1, 'Available', '2025-09-28 05:06:38'),
(8, '2', 'Mother Tongue', 'MTB2', 1, 'Available', '2025-09-28 05:06:38'),
(9, '2', 'Filipino', 'FIL2', 1, 'Available', '2025-09-28 05:06:38'),
(10, '2', 'English', 'ENG2', 1, 'Available', '2025-09-28 05:06:38'),
(11, '2', 'Mathematics', 'MATH2', 1, 'Available', '2025-09-28 05:06:38'),
(12, '2', 'Araling Panlipunan', 'AP2', 1, 'Available', '2025-09-28 05:06:38'),
(13, '2', 'Edukasyon sa Pagpapakatao', 'ESP2', 1, 'Available', '2025-09-28 05:06:38'),
(14, '2', 'MAPEH (Music, Arts, PE, Health)', 'MAPEH2', 1, 'Available', '2025-09-28 05:06:38'),
(15, '3', 'Mother Tongue', 'MTB3', 1, 'Available', '2025-09-28 05:06:38'),
(16, '3', 'Filipino', 'FIL3', 1, 'Available', '2025-09-28 05:06:38'),
(17, '3', 'English', 'ENG3', 1, 'Available', '2025-09-28 05:06:38'),
(18, '3', 'Mathematics', 'MATH3', 1, 'Available', '2025-09-28 05:06:38'),
(19, '3', 'Araling Panlipunan', 'AP3', 1, 'Available', '2025-09-28 05:06:38'),
(20, '3', 'Edukasyon sa Pagpapakatao', 'ESP3', 1, 'Available', '2025-09-28 05:06:38'),
(21, '3', 'MAPEH (Music, Arts, PE, Health)', 'MAPEH3', 1, 'Available', '2025-09-28 05:06:38'),
(22, '4', 'Filipino', 'FIL4', 1, 'Available', '2025-09-28 05:06:38'),
(23, '4', 'English', 'ENG4', 1, 'Available', '2025-09-28 05:06:38'),
(24, '4', 'Mathematics', 'MATH4', 1, 'Available', '2025-09-28 05:06:38'),
(25, '4', 'Araling Panlipunan', 'AP4', 1, 'Available', '2025-09-28 05:06:38'),
(26, '4', 'Edukasyon sa Pagpapakatao', 'ESP4', 1, 'Available', '2025-09-28 05:06:38'),
(27, '4', 'MAPEH (Music, Arts, PE, Health)', 'MAPEH4', 1, 'Available', '2025-09-28 05:06:38'),
(28, '4', 'Edukasyong Pantahanan at Pangkabuhayan (EPP)', 'EPP4', 1, 'Available', '2025-09-28 05:06:38'),
(29, '5', 'Filipino', 'FIL5', 1, 'Available', '2025-09-28 05:06:38'),
(30, '5', 'English', 'ENG5', 1, 'Available', '2025-09-28 05:06:38'),
(31, '5', 'Mathematics', 'MATH5', 1, 'Available', '2025-09-28 05:06:38'),
(32, '5', 'Araling Panlipunan', 'AP5', 1, 'Available', '2025-09-28 05:06:38'),
(33, '5', 'Edukasyon sa Pagpapakatao', 'ESP5', 1, 'Available', '2025-09-28 05:06:38'),
(34, '5', 'MAPEH (Music, Arts, PE, Health)', 'MAPEH5', 1, 'Available', '2025-09-28 05:06:38'),
(35, '5', 'Edukasyong Pantahanan at Pangkabuhayan (EPP)', 'EPP5', 1, 'Available', '2025-09-28 05:06:38'),
(36, '6', 'Filipino', 'FIL6', 1, 'Available', '2025-09-28 05:06:38'),
(37, '6', 'English', 'ENG6', 1, 'Available', '2025-09-28 05:06:38'),
(38, '6', 'Mathematics', 'MATH6', 1, 'Available', '2025-09-28 05:06:38'),
(39, '6', 'Araling Panlipunan', 'AP6', 1, 'Available', '2025-09-28 05:06:38'),
(40, '6', 'Edukasyon sa Pagpapakatao', 'ESP6', 1, 'Available', '2025-09-28 05:06:38'),
(41, '6', 'MAPEH (Music, Arts, PE, Health)', 'MAPEH6', 1, 'Available', '2025-09-28 05:06:38'),
(42, '6', 'Edukasyong Pantahanan at Pangkabuhayan (EPP with I', 'EPP6', 1, 'Available', '2025-09-28 05:06:38'),
(43, 'Grade 3', 'Mother Tongue Literacy', 'MT3', 1, 'Available', '2025-12-13 16:33:32'),
(44, 'Grade 3', 'Filipino', 'FIL3', 1, 'Available', '2025-12-13 16:33:32'),
(45, 'Grade 3', 'English', 'ENG3', 1, 'Available', '2025-12-13 16:33:32'),
(46, 'Grade 3', 'Mathematics', 'MATH3', 1, 'Available', '2025-12-13 16:33:32'),
(47, 'Grade 3', 'Science', 'SCI3', 1, 'Available', '2025-12-13 16:33:32'),
(48, 'Grade 3', 'Araling Panlipunan', 'AP3', 1, 'Available', '2025-12-13 16:33:32'),
(49, 'Grade 3', 'Edukasyon sa Pagpapakatao', 'ESP3', 1, 'Available', '2025-12-13 16:33:32'),
(50, 'Grade 3', 'Music', 'MUS3', 1, 'Available', '2025-12-13 16:33:32'),
(51, 'Grade 3', 'Arts', 'ART3', 1, 'Available', '2025-12-13 16:33:32'),
(52, 'Grade 3', 'Physical Education', 'PE3', 1, 'Available', '2025-12-13 16:33:32'),
(53, 'Grade 3', 'Health', 'HE3', 1, 'Available', '2025-12-13 16:33:32'),
(54, 'Grade 3', 'Computer Education', 'ICT3', 1, 'Available', '2025-12-13 16:33:32');

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
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `middlename`, `lastname`, `suffix`, `email`, `contact`, `gender`, `status`, `username`, `password`, `user_role`, `relationship`, `created_date`) VALUES
(2, 'Marcjohn Dave', 'Abao', 'Calunod', NULL, 'emjayrocks03@gmail.com', '', NULL, NULL, 'xterbenn.js', '$2y$10$1kXf.gpYXEfYNy3lMzj.Y.m6zG/vpJC93Hno0qLWqmcH1Kmh1BY.6', 'PARENT', 'Father', '2025-09-23 21:51:37'),
(3, 'Lebron', 'D.', 'James', 'Jr', 'lebron@gmail.com', '', NULL, NULL, 'Lebron', '$2y$10$ng.95MYFuH8zXho50rrXOOGZQVOotHfa74seD6VfuJIUzKt9heYoq', 'TEACHER', 'Guardian', '2025-10-13 12:49:52');

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

--
-- Dumping data for table `users_history`
--

INSERT INTO `users_history` (`id`, `user_id`, `login_time`, `logout_time`) VALUES
(4, 2, '2025-09-24 05:51:56', NULL),
(5, 2, '2025-09-24 06:11:11', NULL),
(6, 3, '2025-09-24 06:20:50', NULL),
(7, 2, '2025-09-26 05:07:48', NULL),
(8, 2, '2025-09-28 05:05:54', NULL),
(9, 3, '2025-09-28 05:06:37', NULL),
(10, 2, '2025-09-28 12:37:55', NULL),
(11, 3, '2025-09-28 12:38:46', NULL),
(12, 2, '2025-09-28 15:22:37', NULL),
(13, 3, '2025-09-28 15:55:20', NULL),
(14, 3, '2025-09-28 23:04:20', NULL),
(15, 2, '2025-09-28 23:23:41', NULL),
(16, 2, '2025-09-29 07:37:05', NULL),
(17, 3, '2025-09-29 07:44:07', NULL),
(18, 2, '2025-09-29 08:20:41', NULL),
(19, 3, '2025-09-29 08:21:18', NULL),
(20, 2, '2025-09-29 08:23:38', NULL),
(21, 2, '2025-09-29 12:31:05', NULL),
(22, 3, '2025-10-03 20:53:41', NULL),
(23, 2, '2025-10-03 20:56:06', NULL),
(24, 3, '2025-10-03 20:57:14', NULL),
(25, 3, '2025-10-08 23:35:36', NULL),
(26, 3, '2025-10-09 00:26:44', NULL),
(27, 3, '2025-10-09 01:24:14', NULL),
(28, 3, '2025-10-09 02:16:54', NULL),
(29, 3, '2025-10-09 21:14:58', NULL),
(30, 3, '2025-10-09 21:50:52', NULL),
(31, 3, '2025-10-09 22:09:31', NULL),
(32, 3, '2025-10-10 00:00:12', NULL),
(33, 3, '2025-10-10 15:30:57', NULL),
(34, 3, '2025-10-10 22:05:00', NULL),
(35, 3, '2025-10-10 23:37:28', NULL),
(36, 3, '2025-10-10 23:37:54', NULL),
(37, 3, '2025-10-10 23:40:09', NULL),
(38, 3, '2025-10-10 23:44:13', NULL),
(39, 3, '2025-10-11 01:05:12', NULL),
(40, 3, '2025-10-11 04:05:10', NULL),
(41, 3, '2025-10-11 04:21:12', NULL),
(42, 3, '2025-10-11 04:56:43', NULL),
(43, 3, '2025-10-11 15:35:49', NULL),
(44, 3, '2025-10-11 16:26:37', NULL),
(45, 3, '2025-10-12 17:48:34', NULL),
(46, 3, '2025-10-12 20:17:35', NULL),
(47, 3, '2025-10-12 22:14:06', NULL),
(48, 3, '2025-10-13 04:32:59', NULL),
(49, 3, '2025-10-13 06:26:38', NULL),
(50, 2, '2025-10-13 06:26:55', NULL),
(51, 3, '2025-10-13 18:36:08', NULL),
(52, 3, '2025-10-13 20:40:12', NULL),
(53, 2, '2025-10-13 20:51:13', NULL),
(54, 3, '2025-10-14 18:29:04', NULL),
(55, 3, '2025-10-14 20:04:19', NULL),
(56, 3, '2025-10-14 21:02:03', NULL),
(57, 3, '2025-10-14 21:44:58', NULL),
(58, 3, '2025-10-14 23:24:39', NULL),
(59, 3, '2025-10-14 23:44:43', NULL),
(60, 3, '2025-10-15 00:30:14', NULL),
(61, 3, '2025-10-15 04:42:30', NULL),
(62, 3, '2025-10-15 06:09:18', NULL),
(63, 3, '2025-10-15 06:33:05', NULL),
(64, 3, '2025-10-15 10:28:15', NULL),
(65, 3, '2025-10-15 21:44:59', NULL),
(66, 3, '2025-10-15 22:46:28', NULL),
(67, 3, '2025-10-16 00:49:25', NULL),
(68, 3, '2025-10-16 06:22:41', NULL),
(69, 3, '2025-10-16 06:33:50', NULL),
(70, 3, '2025-10-16 07:13:52', NULL),
(71, 3, '2025-10-16 08:51:02', NULL),
(72, 3, '2025-10-16 09:47:53', NULL),
(73, 3, '2025-10-16 18:33:47', NULL),
(74, 3, '2025-10-16 20:14:23', NULL),
(75, 3, '2025-10-17 02:18:48', NULL),
(76, 2, '2025-10-27 11:00:01', NULL),
(77, 3, '2025-10-27 11:00:25', NULL),
(78, 3, '2025-10-27 12:54:55', NULL),
(79, 2, '2025-10-29 21:03:07', NULL),
(80, 3, '2025-10-29 21:04:43', NULL),
(81, 3, '2025-10-30 01:03:13', NULL),
(82, 3, '2025-10-30 21:25:19', NULL),
(83, 2, '2025-10-30 22:06:15', NULL),
(84, 2, '2025-10-30 22:41:05', NULL),
(85, 2, '2025-10-31 20:40:52', NULL),
(86, 3, '2025-10-31 21:08:28', NULL),
(87, 2, '2025-10-31 21:30:03', NULL),
(88, 2, '2025-10-31 22:20:49', NULL),
(89, 3, '2025-10-31 23:29:18', NULL),
(90, 2, '2025-11-01 15:01:15', NULL),
(91, 3, '2025-11-01 15:42:54', NULL),
(92, 3, '2025-11-01 15:50:52', NULL),
(93, 3, '2025-11-01 16:07:16', NULL),
(94, 3, '2025-11-01 16:53:34', NULL),
(95, 2, '2025-11-01 17:23:14', NULL),
(96, 3, '2025-11-01 23:11:13', NULL),
(97, 3, '2025-11-01 23:33:24', NULL),
(98, 3, '2025-11-09 19:55:04', NULL),
(99, 3, '2025-11-16 20:08:42', NULL),
(100, 3, '2025-11-16 23:22:24', NULL),
(101, 3, '2025-11-16 23:22:25', NULL),
(102, 3, '2025-11-27 21:27:35', NULL),
(103, 3, '2025-11-29 19:23:37', NULL),
(104, 3, '2025-11-29 23:12:43', NULL),
(105, 3, '2025-11-30 16:50:57', NULL),
(106, 3, '2025-11-30 18:04:56', NULL),
(107, 3, '2025-11-30 22:19:45', NULL),
(108, 3, '2025-12-04 11:00:59', NULL),
(109, 3, '2025-12-04 20:47:08', NULL),
(110, 3, '2025-12-04 22:31:05', NULL),
(111, 3, '2025-12-04 23:24:32', NULL),
(112, 3, '2025-12-05 00:22:15', NULL),
(113, 3, '2025-12-05 18:39:34', NULL),
(114, 3, '2025-12-05 19:05:03', NULL),
(115, 3, '2025-12-05 21:03:59', NULL),
(116, 3, '2025-12-05 22:14:01', NULL),
(117, 3, '2025-12-06 01:55:23', NULL),
(118, 3, '2025-12-06 02:46:35', NULL),
(119, 3, '2025-12-06 04:37:59', NULL),
(120, 3, '2025-12-06 05:02:59', NULL),
(121, 2, '2025-12-06 05:03:26', NULL),
(122, 3, '2025-12-06 05:03:46', NULL),
(123, 3, '2025-12-06 06:30:00', NULL),
(124, 3, '2025-12-06 06:35:39', NULL),
(125, 3, '2025-12-06 06:47:14', NULL),
(126, 3, '2025-12-06 06:53:05', NULL),
(127, 3, '2025-12-06 16:32:28', NULL),
(128, 3, '2025-12-06 16:36:08', NULL),
(129, 3, '2025-12-07 02:45:02', NULL),
(130, 3, '2025-12-07 02:55:41', NULL),
(131, 3, '2025-12-07 04:20:33', NULL),
(132, 3, '2025-12-07 05:07:49', NULL),
(133, 3, '2025-12-07 15:17:24', NULL),
(134, 3, '2025-12-07 16:58:43', NULL),
(135, 3, '2025-12-07 20:17:24', NULL),
(136, 3, '2025-12-07 20:18:34', NULL),
(137, 3, '2025-12-07 23:47:33', NULL),
(138, 3, '2025-12-07 23:49:00', NULL),
(139, 3, '2025-12-07 23:56:59', NULL),
(140, 3, '2025-12-08 01:04:19', NULL),
(141, 3, '2025-12-08 01:06:16', NULL),
(142, 3, '2025-12-08 01:13:07', NULL),
(143, 3, '2025-12-08 01:16:34', NULL),
(144, 3, '2025-12-08 18:05:50', NULL),
(145, 3, '2025-12-09 22:01:00', NULL),
(146, 3, '2025-12-09 22:01:55', NULL),
(147, 3, '2025-12-09 22:32:43', NULL),
(148, 3, '2025-12-09 23:05:02', NULL),
(149, 3, '2025-12-10 01:15:35', NULL),
(150, 3, '2025-12-10 01:15:46', NULL),
(151, 3, '2025-12-10 01:55:41', NULL),
(152, 3, '2025-12-10 02:06:09', NULL),
(153, 3, '2025-12-10 02:39:02', NULL),
(154, 3, '2025-12-10 02:39:12', NULL),
(155, 3, '2025-12-10 02:48:57', NULL),
(156, 3, '2025-12-10 03:31:16', NULL),
(157, 3, '2025-12-12 00:31:43', NULL),
(158, 3, '2025-12-12 00:34:43', NULL),
(159, 3, '2025-12-12 00:44:52', NULL),
(160, 3, '2025-12-12 01:58:51', NULL),
(161, 3, '2025-12-13 20:53:06', NULL),
(162, 3, '2025-12-13 22:40:21', NULL),
(163, 3, '2025-12-14 00:02:09', NULL),
(164, 3, '2025-12-14 00:12:58', NULL),
(165, 3, '2025-12-14 00:15:46', NULL),
(166, 2, '2025-12-14 00:30:50', NULL),
(167, 3, '2025-12-14 00:32:12', NULL),
(168, 3, '2025-12-14 00:34:03', NULL),
(169, 3, '2025-12-14 00:52:08', NULL),
(170, 3, '2025-12-16 01:15:41', NULL),
(171, 3, '2025-12-16 02:23:43', NULL),
(172, 2, '2025-12-16 22:20:01', NULL),
(173, 3, '2025-12-18 21:29:19', NULL),
(174, 3, '2025-12-19 02:39:23', NULL),
(175, 3, '2025-12-19 22:22:44', NULL),
(176, 3, '2025-12-20 00:30:06', NULL);

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
  ADD KEY `adviser_id` (`adviser_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `adviser_id` (`adviser_id`),
  ADD KEY `sy_id` (`sy_id`);

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
-- Indexes for table `school_year`
--
ALTER TABLE `school_year`
  ADD PRIMARY KEY (`school_year_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `section_name` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_2` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_3` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_4` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_5` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_6` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_7` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_8` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_9` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_10` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_11` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_12` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_13` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_14` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_15` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_16` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_17` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_18` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_19` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_20` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_21` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_22` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_23` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_24` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_25` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_26` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_27` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_28` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_29` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_30` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_31` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_32` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_33` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_34` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_35` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_36` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_37` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_38` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_39` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_40` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_41` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_42` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_43` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_44` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_45` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_46` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_47` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_48` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_49` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_50` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_51` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_52` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_53` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_54` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_55` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_56` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_57` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_58` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_59` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_60` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_61` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_62` (`section_name`,`section_grade_level`),
  ADD UNIQUE KEY `section_name_63` (`section_name`,`section_grade_level`);

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
  ADD UNIQUE KEY `unique_student_grade` (`student_id`,`grade`);

--
-- Indexes for table `sf10_data`
--
ALTER TABLE `sf10_data`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_history`
--
ALTER TABLE `admin_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrolment`
--
ALTER TABLE `enrolment`
  MODIFY `enrolment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `enrolment_subjects`
--
ALTER TABLE `enrolment_subjects`
  MODIFY `enrolment_subjects_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `feeback`
--
ALTER TABLE `feeback`
  MODIFY `feeback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parents_info`
--
ALTER TABLE `parents_info`
  MODIFY `parents_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `school_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `sf5_data`
--
ALTER TABLE `sf5_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sf9_data`
--
ALTER TABLE `sf9_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `sf10_data`
--
ALTER TABLE `sf10_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `sf_add_data`
--
ALTER TABLE `sf_add_data`
  MODIFY `sf_add_data_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1022;

--
-- AUTO_INCREMENT for table `stuenrolmentinfo`
--
ALTER TABLE `stuenrolmentinfo`
  MODIFY `stuEnrolmentInfo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users_history`
--
ALTER TABLE `users_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

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
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`sy_id`) REFERENCES `school_year` (`school_year_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `enrolment_subjects_ibfk_1` FOREIGN KEY (`enrolment_id`) REFERENCES `enrolment` (`enrolment_id`) ON DELETE CASCADE;

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
