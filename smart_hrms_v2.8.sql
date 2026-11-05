-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2026 at 08:08 AM
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
-- Table structure for table `applicant_notifications`
--

DROP TABLE IF EXISTS `applicant_notifications`;
CREATE TABLE `applicant_notifications` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `application_id` bigint(20) DEFAULT NULL,
  `category` enum('accepted','rejected','cancelled','general') NOT NULL DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
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
  `status` enum('submitted','reviewing','interviewed','hired','rejected') DEFAULT 'submitted',
  `reviewed_by_user_id` bigint(20) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `address` text DEFAULT NULL,
  `specialization` varchar(150) DEFAULT NULL,
  `prc_license_number` varchar(40) DEFAULT NULL,
  `prc_not_applicable` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_documents`
--

DROP TABLE IF EXISTS `application_documents`;
CREATE TABLE `application_documents` (
  `id` bigint(20) NOT NULL,
  `application_id` bigint(20) NOT NULL,
  `document_type` enum('resume','application_letter','transcript','credential') NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

DROP TABLE IF EXISTS `attendance_logs`;
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

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_table` varchar(50) NOT NULL,
  `record_id` bigint(20) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
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
(1, 'MAIN', 'Main Campus', 'Panabo City', '2026-10-25 14:32:14', '2026-11-02 01:28:48'),
(2, 'BR-EXT', 'Extension Campus', 'Extension Site, Panabo City', '2026-10-26 14:18:25', '2026-10-26 15:06:36');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

DROP TABLE IF EXISTS `classrooms`;
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

DROP TABLE IF EXISTS `departments`;
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
(1, 1, 'Information Technology', '2026-10-25 14:32:14', '2026-11-02 00:34:32'),
(2, 1, 'Mathematics', '2026-10-25 14:32:14', '2026-11-02 00:34:32'),
(3, 1, 'English', '2026-11-02 00:34:32', '2026-11-02 01:27:06'),
(4, 1, 'Business Administration', '2026-11-02 00:34:32', '2026-11-02 01:27:06'),
(5, 1, 'Administration', '2026-11-02 01:27:06', '2026-11-02 01:27:06');

-- --------------------------------------------------------

--
-- Table structure for table `email_notification_logs`
--

DROP TABLE IF EXISTS `email_notification_logs`;
CREATE TABLE `email_notification_logs` (
  `id` bigint(20) NOT NULL,
  `application_id` bigint(20) DEFAULT NULL,
  `recipient_email` varchar(100) NOT NULL,
  `notification_type` enum('accepted','rejected','password_reset','payroll_info','general','cancelled') NOT NULL,
  `delivery_status` enum('pending','sent','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
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
(1, 1, 'EMP-2026-001', NULL, 'Maria', NULL, 'Santos', 5, 'HR Administrator', 0, '2026-01-01', 'active', '2026-11-02 00:34:32', '2026-11-02 01:27:06'),
(2, 2, 'EMP-2026-002', NULL, 'Carlos', NULL, 'Reyes', 5, 'HR Assistant', 0, '2026-01-01', 'active', '2026-11-02 00:34:32', '2026-11-02 01:27:06'),
(3, 3, 'EMP-2026-003', NULL, 'Juan', NULL, 'Cruz', 1, 'Instructor I', 1, '2026-01-01', 'active', '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(4, 4, 'EMP-2026-004', NULL, 'Ana', NULL, 'Garcia', 2, 'Instructor I', 1, '2026-01-01', 'active', '2026-11-02 00:34:32', '2026-11-02 00:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `employee_status_histories`
--

DROP TABLE IF EXISTS `employee_status_histories`;
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

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE `evaluations` (
  `id` bigint(20) NOT NULL,
  `student_id` bigint(20) NOT NULL,
  `instructor_id` bigint(20) NOT NULL,
  `subject_id` bigint(20) NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('draft','submitted') NOT NULL DEFAULT 'submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interview_schedules`
--

DROP TABLE IF EXISTS `interview_schedules`;
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

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
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

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` bigint(20) NOT NULL,
  `attempt_key` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `attempt_key`, `created_at`) VALUES
(14, 'eff8e7ca506627fe15dda5e0e512fcaad70b6d520f37cc76597fdb4f2d83a1a3', '2026-11-03 16:31:31');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2026_10_30_125927_create_password_reset_tokens_and_sessions_tables', 2),
(4, '2026_10_30_130135_add_laravel_columns_to_users_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

DROP TABLE IF EXISTS `payslips`;
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

-- --------------------------------------------------------

--
-- Table structure for table `payslip_items`
--

DROP TABLE IF EXISTS `payslip_items`;
CREATE TABLE `payslip_items` (
  `id` bigint(20) NOT NULL,
  `payslip_id` bigint(20) NOT NULL,
  `item_type` enum('earning','deduction') NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pay_rates`
--

DROP TABLE IF EXISTS `pay_rates`;
CREATE TABLE `pay_rates` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(30) NOT NULL,
  `description` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
(1, 'hr_admin', 'Human Resources administrator - full system access'),
(2, 'instructor', 'Faculty or staff member with a login'),
(3, 'applicant', 'Pending applicant - not yet an employee'),
(4, 'student', 'Enrolled student');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
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
(1, 'BSIT-1A', 'BSIT-1A', 'BSIT', 1, 1, '2026-2027', 40, '2026-10-27 15:36:18', '2026-11-02 01:47:10'),
(2, 'BSMATH-1A', 'BSMATH-1A', 'BSMATH', 1, 1, '2026-2027', 40, '2026-10-27 15:36:18', '2026-11-02 01:47:10');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('qQFUIROfuPQEJ6AQ9UAMwd3U8soPXyNYz4wahxh7', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJkRUVyOHJIa2hBa1JXd21JbWh1Ym55VnI0bHdBT0tUMFlzZFYwZTRaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxLCJwYXNzd29yZF9oYXNoX3dlYiI6IjIwODQ4MGEwOWU4Nzg5NWJmNWZhZmIwNDdiNmNkYTIzNjE3MmNhYjU1MmZhZjc5MDQ2ZGY3MTdkMTY2MjFmMmQifQ==', 1793398001);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `student_number` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `program` varchar(50) NOT NULL,
  `year_level` tinyint(3) UNSIGNED NOT NULL,
  `section_id` bigint(20) DEFAULT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('active','inactive','graduated','dropped') DEFAULT 'active',
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `review_notes` varchar(255) DEFAULT NULL,
  `reviewed_by_user_id` bigint(20) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_number`, `first_name`, `middle_name`, `last_name`, `program`, `year_level`, `section_id`, `enrollment_date`, `status`, `verification_status`, `review_notes`, `reviewed_by_user_id`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'STD-2026-001', 'Mark', NULL, 'Penduko', 'BSIT', 1, 1, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(2, 6, 'STD-2026-002', 'Bea', NULL, 'Alonzo', 'BSIT', 1, 1, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(3, 7, 'STD-2026-003', 'Jose', NULL, 'Rizal', 'BSIT', 1, 1, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(4, 8, 'STD-2026-004', 'Luz', NULL, 'Macaraeg', 'BSIT', 1, 1, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(5, 9, 'STD-2026-005', 'Kathryn', NULL, 'Bernardo', 'BSIT', 1, 1, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(6, 10, 'STD-2026-006', 'Coco', NULL, 'Martin', 'BSMATH', 1, 2, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(7, 11, 'STD-2026-007', 'Dingdong', NULL, 'Dantes', 'BSMATH', 1, 2, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(8, 12, 'STD-2026-008', 'Marian', NULL, 'Rivera', 'BSMATH', 1, 2, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(9, 13, 'STD-2026-009', 'Vic', NULL, 'Sotto', 'BSMATH', 1, 2, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(10, 14, 'STD-2026-010', 'Joey', NULL, 'De Leon', 'BSMATH', 1, 2, '2026-08-01', 'active', 'verified', NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
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

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('academic_period', '2026-2027 1st Semester'),
('company_name', 'Davao del Norte State College'),
('late_after', '08:00'),
('payroll_cutoff_day', '15');

-- --------------------------------------------------------

--
-- Table structure for table `teaching_schedules`
--

DROP TABLE IF EXISTS `teaching_schedules`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `google_sub` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `branch_id`, `role_id`, `username`, `name`, `email`, `email_verified_at`, `password_hash`, `remember_token`, `is_active`, `google_sub`, `created_at`, `updated_at`, `session_version`) VALUES
(1, NULL, 1, 'hr_msantos', NULL, 'maria.santos@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(2, NULL, 1, 'hr_creyes', NULL, 'carlos.reyes@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(3, NULL, 2, 'inst_jcruz', NULL, 'juan.cruz@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(4, NULL, 2, 'inst_agarcia', NULL, 'ana.garcia@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(5, NULL, 4, 'stud_mpenduko', NULL, 'mark.penduko@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(6, NULL, 4, 'stud_balonzo', NULL, 'bea.alonzo@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(7, NULL, 4, 'stud_jrizal', NULL, 'jose.rizal@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(8, NULL, 4, 'stud_lmacaraeg', NULL, 'luz.macaraeg@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(9, NULL, 4, 'stud_kbernardo', NULL, 'kathryn.bernardo@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(10, NULL, 4, 'stud_cmartin', NULL, 'coco.martin@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(11, NULL, 4, 'stud_ddantes', NULL, 'dingdong.dantes@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(12, NULL, 4, 'stud_mrivera', NULL, 'marian.rivera@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 01:47:10', 1),
(13, NULL, 4, 'stud_vsotto', NULL, 'vic.sotto@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1),
(14, NULL, 4, 'stud_jdeleon', NULL, 'joey.deleon@dnsc.edu.ph', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `user_id` bigint(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `prc_license_number` varchar(40) DEFAULT NULL,
  `prc_not_applicable` tinyint(1) NOT NULL DEFAULT 0,
  `specialization` varchar(150) DEFAULT NULL,
  `highest_education` varchar(100) DEFAULT NULL,
  `profile_completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `full_name`, `photo_path`, `contact_number`, `address`, `prc_license_number`, `prc_not_applicable`, `specialization`, `highest_education`, `profile_completed_at`, `created_at`, `updated_at`) VALUES
(1, 'Maria Santos', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(2, 'Carlos Reyes', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(3, 'Juan Cruz', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(4, 'Ana Garcia', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(5, 'Mark Penduko', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(6, 'Bea Alonzo', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(7, 'Jose Rizal', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(8, 'Luz Macaraeg', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(9, 'Kathryn Bernardo', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(10, 'Coco Martin', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(11, 'Dingdong Dantes', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(12, 'Marian Rivera', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(13, 'Vic Sotto', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(14, 'Joey De Leon', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-11-02 00:34:32', '2026-11-02 00:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `vacancies`
--

DROP TABLE IF EXISTS `vacancies`;
CREATE TABLE `vacancies` (
  `id` bigint(20) NOT NULL,
  `department_id` bigint(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `employment_type` enum('Full-time','Part-time','Contract') DEFAULT 'Full-time',
  `description` text NOT NULL,
  `closing_date` date NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vacancies`
--

INSERT INTO `vacancies` (`id`, `department_id`, `title`, `employment_type`, `description`, `closing_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Instructor I', 'Full-time', 'Prepare and deliver computing courses, assess learning, and mentor students.', '2026-12-15', 'open', '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(2, 2, 'Instructor I', 'Full-time', 'Teach mathematics courses, develop learning resources, and support student learning.', '2026-12-15', 'open', '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(3, 3, 'Instructor I', 'Full-time', 'Facilitate language and communication courses and assess student performance.', '2026-12-18', 'open', '2026-11-02 00:34:32', '2026-11-02 00:34:32'),
(4, 4, 'Instructor I', 'Part-time', 'Teach business courses and connect classroom concepts with professional practice.', '2026-12-18', 'open', '2026-11-02 00:34:32', '2026-11-02 00:34:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicant_notifications`
--
ALTER TABLE `applicant_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_applicant_vacancy` (`applicant_user_id`,`vacancy_id`),
  ADD KEY `vacancy_id` (`vacancy_id`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

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
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

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
  ADD KEY `departments_ibfk_1` (`branch_id`);

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
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_eval_unique` (`student_id`,`instructor_id`,`subject_id`,`semester`,`academic_year`),
  ADD KEY `idx_eval_instructor` (`instructor_id`),
  ADD KEY `idx_eval_subject` (`subject_id`),
  ADD KEY `idx_eval_period` (`academic_year`,`semester`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

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
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_student_number` (`student_number`),
  ADD UNIQUE KEY `uq_student_user` (`user_id`),
  ADD KEY `idx_student_program` (`program`,`year_level`),
  ADD KEY `idx_student_section` (`section_id`),
  ADD KEY `idx_students_verification` (`verification_status`),
  ADD KEY `students_reviewed_by_fk` (`reviewed_by_user_id`);

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
  ADD UNIQUE KEY `google_sub` (`google_sub`),
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
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applicant_notifications`
--
ALTER TABLE `applicant_notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `email_notification_logs`
--
ALTER TABLE `email_notification_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employee_status_histories`
--
ALTER TABLE `employee_status_histories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicant_notifications`
--
ALTER TABLE `applicant_notifications`
  ADD CONSTRAINT `fk_app_notif_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`applicant_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON UPDATE CASCADE;

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
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `fk_eval_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `fk_eval_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `fk_eval_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

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
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_reviewed_by_fk` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`);

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
  ADD CONSTRAINT `vacancies_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
