-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 04:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_hrms`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) NOT NULL,
  `vacancy_id` bigint(20) DEFAULT NULL,
  `applicant_user_id` bigint(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `age` tinyint(3) UNSIGNED DEFAULT NULL,
  `position_applied` varchar(50) DEFAULT NULL,
  `highest_education` varchar(50) DEFAULT NULL,
  `years_experience` tinyint(3) UNSIGNED DEFAULT NULL,
  `experience_details` text DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `resume_file_path` varchar(255) DEFAULT NULL,
  `status` enum('submitted','under_review','accepted','rejected') DEFAULT 'submitted',
  `reviewed_by_user_id` bigint(20) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `vacancy_id`, `applicant_user_id`, `full_name`, `email`, `contact_number`, `age`, `position_applied`, `highest_education`, `years_experience`, `experience_details`, `cover_letter`, `resume_file_path`, `status`, `reviewed_by_user_id`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'Maria Santos', 'maria.santos@dnsc.edu.ph', '09170000003', 32, 'Instructor II - Computer Science', 'Masters Degree', 5, '5 years as Instructor I at XYZ College', 'I am applying for the Instructor II position...', '/uploads/resumes/maria_santos_cv.pdf', 'under_review', 1, '2026-10-26 02:00:00', '2026-10-26 17:16:13', '2026-10-26 17:16:13'),
(2, 2, 11, 'Juan Dela Cruz', 'juan.delacruz@dnsc.edu.ph', '09170000004', 28, 'HR Officer', 'Bachelors Degree', 2, '2 years as HR Assistant at ABC Corp', 'Interested in the HR Officer role...', '/uploads/resumes/juan_delacruz_cv.pdf', 'submitted', NULL, NULL, '2026-10-26 17:16:13', '2026-10-26 17:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `work_date` date NOT NULL,
  `check_in_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('present','late','corrected') DEFAULT 'present',
  `is_corrected` tinyint(1) DEFAULT 0,
  `corrected_by_user_id` bigint(20) DEFAULT NULL,
  `correction_notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `check_out_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`id`, `employee_id`, `branch_id`, `work_date`, `check_in_time`, `status`, `is_corrected`, `corrected_by_user_id`, `correction_notes`, `created_at`, `updated_at`, `check_out_time`) VALUES
(1, 1, 1, '2026-03-20', '2026-03-19 23:45:00', 'present', 0, NULL, NULL, '2026-10-26 14:30:44', '2026-10-26 14:30:44', NULL),
(2, 2, 1, '2026-03-20', '2026-03-20 00:00:00', 'present', 0, NULL, NULL, '2026-10-26 14:30:44', '2026-10-26 14:30:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_table` varchar(50) NOT NULL,
  `record_id` bigint(20) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action_type`, `target_table`, `record_id`, `details`, `created_at`) VALUES
(1, 1, 'CREATE', 'vacancies', 1, 'Posted vacancy: Instructor II - Computer Science', '2026-10-26 17:16:13'),
(2, 1, 'UPDATE', 'applications', 1, 'Status changed from submitted to under_review', '2026-10-26 17:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_code`, `branch_name`, `address`, `created_at`, `updated_at`) VALUES
(1, 'BR-MAIN', 'Panabo Main Campus', 'Panabo City', '2026-10-25 14:32:14', '2026-10-25 14:32:14'),
(2, 'BR-EXT', 'Extension Campus', 'Extension Site, Panabo City', '2026-10-26 14:18:25', '2026-10-26 15:06:36');

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` bigint(20) NOT NULL,
  `room_code` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `building` varchar(100) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 40,
  `branch_id` bigint(20) NOT NULL,
  `room_type` enum('lecture','laboratory','conference') NOT NULL DEFAULT 'lecture',
  `status` enum('available','maintenance') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`id`, `room_code`, `room_name`, `building`, `capacity`, `branch_id`, `room_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CS-301', 'Computer Laboratory 1', 'Main Building', 40, 1, 'laboratory', 'available', '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(2, 'R-201', 'Lecture Room 1', 'Admin Building', 50, 1, 'lecture', 'available', '2026-10-27 15:36:18', '2026-10-27 15:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `branch_id`, `department_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Human Resources', '2026-10-25 14:32:14', '2026-10-25 14:32:14'),
(2, 1, 'Information Technology', '2026-10-25 14:32:14', '2026-10-25 14:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `email_notification_logs`
--

CREATE TABLE `email_notification_logs` (
  `id` bigint(20) NOT NULL,
  `application_id` bigint(20) NOT NULL,
  `recipient_email` varchar(100) NOT NULL,
  `notification_type` enum('accepted','rejected') NOT NULL,
  `delivery_status` enum('pending','sent','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_notification_logs`
--

INSERT INTO `email_notification_logs` (`id`, `application_id`, `recipient_email`, `notification_type`, `delivery_status`, `error_message`, `sent_at`) VALUES
(1, 1, 'maria.santos@dnsc.edu.ph', 'accepted', 'sent', NULL, '2026-10-26 02:05:00'),
(2, 2, 'juan.delacruz@dnsc.edu.ph', 'rejected', 'failed', 'SMTP connection timeout', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `employee_number` varchar(30) NOT NULL,
  `rfid_card_number` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `department_id` bigint(20) NOT NULL,
  `position_title` varchar(100) NOT NULL,
  `is_faculty` tinyint(1) DEFAULT 1,
  `date_hired` date NOT NULL,
  `current_status` enum('active','on_leave','resigned','terminated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_number`, `rfid_card_number`, `first_name`, `middle_name`, `last_name`, `department_id`, `position_title`, `is_faculty`, `date_hired`, `current_status`, `created_at`, `updated_at`) VALUES
(1, 2, 'EMP-2026-001', 'RFID-99887766', 'John', NULL, 'Doe', 2, 'Instructor I', 1, '2026-01-15', 'active', '2026-10-25 14:32:14', '2026-10-25 14:32:14'),
(2, 11, 'EMP-2026-003', 'RFID-10000002', 'Juan', NULL, 'Dela Cruz', 2, 'HR Assistant', 0, '2025-06-01', 'active', '2026-10-26 14:26:41', '2026-10-26 14:26:41');

-- --------------------------------------------------------

--
-- Table structure for table `employee_status_histories`
--

CREATE TABLE `employee_status_histories` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `previous_status` enum('active','on_leave','resigned','terminated') DEFAULT NULL,
  `new_status` enum('active','on_leave','resigned','terminated') NOT NULL,
  `change_reason` text DEFAULT NULL,
  `changed_by_user_id` bigint(20) NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_status_histories`
--

INSERT INTO `employee_status_histories` (`id`, `employee_id`, `previous_status`, `new_status`, `change_reason`, `changed_by_user_id`, `effective_date`, `created_at`) VALUES
(1, 1, NULL, 'active', 'Initial hire', 1, '2026-01-15', '2026-10-26 17:16:13'),
(2, 2, 'active', 'on_leave', 'Approved sick leave', 1, '2026-10-01', '2026-10-26 17:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `interview_schedules`
--

CREATE TABLE `interview_schedules` (
  `id` bigint(20) NOT NULL,
  `application_id` bigint(20) NOT NULL,
  `interviewer_user_id` bigint(20) NOT NULL,
  `interview_date` date NOT NULL,
  `interview_time` time NOT NULL,
  `location_or_link` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','passed','failed') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interview_schedules`
--

INSERT INTO `interview_schedules` (`id`, `application_id`, `interviewer_user_id`, `interview_date`, `interview_time`, `location_or_link`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-11-05', '10:00:00', 'HR Office, Admin Building Room 201', 'scheduled', 'Panel interview with Dept Head', '2026-10-26 17:16:13', '2026-10-26 17:16:13'),
(2, 2, 1, '2026-11-06', '14:00:00', 'https://meet.google.com/abc-defg-hij', 'scheduled', 'Online interview via Google Meet', '2026-10-26 17:16:13', '2026-10-26 17:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `leave_type` varchar(50) DEFAULT 'Sick Leave',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by_user_id` bigint(20) DEFAULT NULL,
  `review_notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `reviewed_by_user_id`, `review_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'Vacation Leave', '2026-12-20', '2026-12-24', 'Family vacation during semester break', 'approved', 1, 'Approved - no conflicts', '2026-10-26 17:16:13', '2026-10-26 17:16:13'),
(2, 2, 'Sick Leave', '2026-10-01', '2026-10-05', 'Medical procedure recovery', 'approved', 1, 'Medical certificate submitted', '2026-10-26 17:16:13', '2026-10-26 17:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) NOT NULL,
  `attempt_key` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `attempt_key`, `created_at`) VALUES
(1, '1daa19db81b6e9fe075197d2c59ee854c1cfd0f44571888fd117552173b3947c', '2026-10-26 13:27:35'),
(2, '1daa19db81b6e9fe075197d2c59ee854c1cfd0f44571888fd117552173b3947c', '2026-10-26 13:35:32'),
(3, '1daa19db81b6e9fe075197d2c59ee854c1cfd0f44571888fd117552173b3947c', '2026-10-26 13:38:30');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` bigint(20) NOT NULL,
  `payslip_number` varchar(30) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `period_start_date` date NOT NULL,
  `period_end_date` date NOT NULL,
  `total_days_worked` int(11) NOT NULL,
  `daily_rate_applied` decimal(10,2) NOT NULL,
  `gross_salary` decimal(10,2) NOT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `generated_by_user_id` bigint(20) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`id`, `payslip_number`, `employee_id`, `period_start_date`, `period_end_date`, `total_days_worked`, `daily_rate_applied`, `gross_salary`, `net_salary`, `generated_by_user_id`, `generated_at`, `deductions`) VALUES
(1, 'PS-2026-10-001', 1, '2026-10-01', '2026-10-15', 11, 1500.00, 16500.00, 14850.00, 1, '2026-10-26 17:16:13', 1650.00),
(2, 'PS-2026-10-002', 2, '2026-10-01', '2026-10-15', 11, 1200.00, 13200.00, 11880.00, 1, '2026-10-26 17:16:13', 1320.00);

-- --------------------------------------------------------

--
-- Table structure for table `payslip_items`
--

CREATE TABLE `payslip_items` (
  `id` bigint(20) NOT NULL,
  `payslip_id` bigint(20) NOT NULL,
  `item_type` enum('earning','deduction') NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslip_items`
--

INSERT INTO `payslip_items` (`id`, `payslip_id`, `item_type`, `item_name`, `amount`, `created_at`) VALUES
(1, 1, 'deduction', 'SSS Contribution', 675.00, '2026-10-26 17:16:13'),
(2, 1, 'deduction', 'PhilHealth Contribution', 412.50, '2026-10-26 17:16:13'),
(3, 1, 'deduction', 'Pag-IBIG Contribution', 200.00, '2026-10-26 17:16:13'),
(4, 1, 'deduction', 'Withholding Tax', 362.50, '2026-10-26 17:16:13'),
(5, 2, 'deduction', 'SSS Contribution', 540.00, '2026-10-26 17:16:13'),
(6, 2, 'deduction', 'PhilHealth Contribution', 330.00, '2026-10-26 17:16:13'),
(7, 2, 'deduction', 'Pag-IBIG Contribution', 200.00, '2026-10-26 17:16:13'),
(8, 2, 'deduction', 'Withholding Tax', 250.00, '2026-10-26 17:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `pay_rates`
--

CREATE TABLE `pay_rates` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pay_rates`
--

INSERT INTO `pay_rates` (`id`, `employee_id`, `daily_rate`, `hourly_rate`, `effective_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1500.00, 187.50, '2026-01-15', '2026-10-26 17:16:13', '2026-10-26 17:16:13'),
(2, 2, 1200.00, 150.00, '2025-06-01', '2026-10-26 17:16:13', '2026-10-26 17:16:13');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(30) NOT NULL,
  `description` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'hr_admin', NULL),
(2, 'instructor', NULL),
(3, 'applicant', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) NOT NULL,
  `section_code` varchar(20) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `program` varchar(50) NOT NULL,
  `year_level` tinyint(3) UNSIGNED NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `max_students` int(11) DEFAULT 40,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_code`, `section_name`, `program`, `year_level`, `semester`, `academic_year`, `max_students`, `created_at`, `updated_at`) VALUES
(1, 'BSIT-2A', 'BSIT 2nd Year Section A', 'BSIT', 2, 1, '2026-2027', 40, '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(2, 'BSIT-2B', 'BSIT 2nd Year Section B', 'BSIT', 2, 1, '2026-2027', 40, '2026-10-27 15:36:18', '2026-10-27 15:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(150) NOT NULL,
  `units` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `department_id` bigint(20) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `units`, `department_id`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CS101', 'Introduction to Programming', 3, 2, 'Fundamentals of programming using Python', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(2, 'CS201', 'Data Structures and Algorithms', 3, 2, 'Arrays, linked lists, trees, sorting, and searching', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(3, 'CS301', 'Database Management Systems', 3, 2, 'Relational databases, SQL, normalization, and ER modeling', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(4, 'GE101', 'Understanding the Self', 3, 1, 'General education course on self-awareness and identity', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'Davao del Norte State College'),
('payroll_cutoff_day', '15');

-- --------------------------------------------------------

--
-- Table structure for table `teaching_schedules`
--

CREATE TABLE `teaching_schedules` (
  `id` bigint(20) NOT NULL,
  `instructor_id` bigint(20) NOT NULL,
  `subject_id` bigint(20) NOT NULL,
  `section_id` bigint(20) NOT NULL,
  `classroom_id` bigint(20) NOT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `status` enum('active','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `teaching_schedules`
--

INSERT INTO `teaching_schedules` (`id`, `instructor_id`, `subject_id`, `section_id`, `classroom_id`, `day`, `start_time`, `end_time`, `semester`, `academic_year`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'Monday', '08:00:00', '10:00:00', 1, '2026-2027', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(2, 1, 2, 1, 1, 'Wednesday', '13:00:00', '15:00:00', 1, '2026-2027', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(3, 1, 1, 2, 2, 'Tuesday', '08:00:00', '10:00:00', 1, '2026-2027', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18'),
(4, 1, 3, 2, 1, 'Thursday', '10:00:00', '12:00:00', 1, '2026-2027', 'active', '2026-10-27 15:36:18', '2026-10-27 15:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `branch_id`, `role_id`, `username`, `email`, `password_hash`, `is_active`, `created_at`, `updated_at`, `session_version`) VALUES
(1, 1, 1, 'hr_admin', 'hr@school.edu.ph', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe11yLqS.N9W1.0.u1.1.1.1.1.1.1', 1, '2026-10-25 14:32:14', '2026-10-25 14:32:14', 1),
(2, 1, 2, 'instructor_john', 'john.doe@school.edu.ph', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe11yLqS.N9W1.0.u1.1.1.1.1.1.1', 1, '2026-10-25 14:32:14', '2026-10-25 14:32:14', 1),
(10, 1, 2, 'instructor_maria', 'maria.santos@dnsc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-10-26 14:18:25', '2026-10-26 14:18:25', 1),
(11, 1, 2, 'staff_juan', 'juan.delacruz@dnsc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-10-26 14:18:25', '2026-10-26 14:18:25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` bigint(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `full_name`, `contact_number`, `address`, `created_at`, `updated_at`) VALUES
(2, 'John Doe', NULL, NULL, '2026-10-26 15:15:43', '2026-10-26 15:15:43'),
(10, 'Maria Santos', '09170000003', 'Panabo City', '2026-10-26 15:15:43', '2026-10-26 15:15:43'),
(11, 'Juan Dela Cruz', '09170000004', 'Panabo City', '2026-10-26 15:15:43', '2026-10-26 15:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `vacancies`
--

CREATE TABLE `vacancies` (
  `id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `department_id` bigint(20) NOT NULL,
  `job_title` varchar(150) NOT NULL,
  `job_description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `status` enum('open','closed','draft') DEFAULT 'open',
  `posted_by_user_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` enum('teaching','non-teaching') NOT NULL DEFAULT 'non-teaching'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vacancies`
--

INSERT INTO `vacancies` (`id`, `branch_id`, `department_id`, `job_title`, `job_description`, `requirements`, `status`, `posted_by_user_id`, `created_at`, `updated_at`, `category`) VALUES
(1, 1, 2, 'Instructor II - Computer Science', 'Teach undergraduate CS courses, advise students, participate in curriculum development.', 'MS in Computer Science or related field; 2+ years teaching experience.', 'open', 1, '2026-10-26 17:16:13', '2026-10-26 17:16:13', 'teaching'),
(2, 1, 1, 'HR Officer', 'Handle employee records, recruitment coordination, and benefits administration.', 'Bachelor degree in HR or Psychology; 1+ year HR experience.', 'open', 1, '2026-10-26 17:16:13', '2026-10-26 17:16:13', 'non-teaching');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vacancy_id` (`vacancy_id`),
  ADD KEY `applicant_user_id` (`applicant_user_id`),
  ADD KEY `reviewed_by_user_id` (`reviewed_by_user_id`),
  ADD KEY `idx_application_status` (`status`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`,`work_date`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `corrected_by_user_id` (`corrected_by_user_id`),
  ADD KEY `idx_attendance_date` (`work_date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_target_record` (`target_table`,`record_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_code` (`branch_code`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_room_code` (`room_code`),
  ADD KEY `idx_branch` (`branch_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `email_notification_logs`
--
ALTER TABLE `email_notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_number` (`employee_number`),
  ADD UNIQUE KEY `rfid_card_number` (`rfid_card_number`),
  ADD KEY `idx_employee_status` (`current_status`),
  ADD KEY `employees_ibfk_2` (`department_id`);

--
-- Indexes for table `employee_status_histories`
--
ALTER TABLE `employee_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `changed_by_user_id` (`changed_by_user_id`);

--
-- Indexes for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `interviewer_user_id` (`interviewer_user_id`),
  ADD KEY `idx_interview_date` (`interview_date`),
  ADD KEY `idx_interview_status` (`status`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `reviewed_by_user_id` (`reviewed_by_user_id`),
  ADD KEY `idx_leave_status` (`status`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempt_window` (`attempt_key`,`created_at`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payslip_number` (`payslip_number`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `generated_by_user_id` (`generated_by_user_id`);

--
-- Indexes for table `payslip_items`
--
ALTER TABLE `payslip_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslip_id` (`payslip_id`);

--
-- Indexes for table `pay_rates`
--
ALTER TABLE `pay_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_employee_effective` (`employee_id`,`effective_date`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_section_sem_ay` (`section_code`,`semester`,`academic_year`),
  ADD KEY `idx_program_year` (`program`,`year_level`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_subject_code` (`subject_code`),
  ADD KEY `idx_department` (`department_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `teaching_schedules`
--
ALTER TABLE `teaching_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_instructor` (`instructor_id`),
  ADD KEY `idx_subject_section` (`subject_id`,`section_id`),
  ADD KEY `idx_classroom_day` (`classroom_id`,`day`,`start_time`),
  ADD KEY `fk_schedule_section` (`section_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vacancies`
--
ALTER TABLE `vacancies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `posted_by_user_id` (`posted_by_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_notification_logs`
--
ALTER TABLE `email_notification_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_status_histories`
--
ALTER TABLE `employee_status_histories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payslip_items`
--
ALTER TABLE `payslip_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pay_rates`
--
ALTER TABLE `pay_rates`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teaching_schedules`
--
ALTER TABLE `teaching_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`applicant_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `applications_ibfk_3` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `attendance_logs_ibfk_3` FOREIGN KEY (`corrected_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD CONSTRAINT `fk_classrooms_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_notification_logs`
--
ALTER TABLE `email_notification_logs`
  ADD CONSTRAINT `email_notification_logs_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `employee_status_histories`
--
ALTER TABLE `employee_status_histories`
  ADD CONSTRAINT `employee_status_histories_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_status_histories_ibfk_2` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  ADD CONSTRAINT `interview_schedules_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interview_schedules_ibfk_2` FOREIGN KEY (`interviewer_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `payslips_ibfk_2` FOREIGN KEY (`generated_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payslip_items`
--
ALTER TABLE `payslip_items`
  ADD CONSTRAINT `payslip_items_ibfk_1` FOREIGN KEY (`payslip_id`) REFERENCES `payslips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pay_rates`
--
ALTER TABLE `pay_rates`
  ADD CONSTRAINT `pay_rates_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subjects_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `teaching_schedules`
--
ALTER TABLE `teaching_schedules`
  ADD CONSTRAINT `fk_schedule_classroom` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedule_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedule_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedule_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vacancies`
--
ALTER TABLE `vacancies`
  ADD CONSTRAINT `vacancies_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `vacancies_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `vacancies_ibfk_3` FOREIGN KEY (`posted_by_user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
