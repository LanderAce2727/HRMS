-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 26, 2026 at 10:46 AM
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
(1, NULL, 3, 'Aira Rocel Nazareno', 'airarocelnazareno@gmail.com', '09639753843', 21, 'Instructor (full-time)', 'Bachelor\'s degree', 3, NULL, NULL, 'uploads/resumes/20261026_085235_66a158a8bcf95c69.pdf', 'submitted', NULL, NULL, '2026-10-26 07:52:35', '2026-10-26 07:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `branch_id` bigint(20) NOT NULL,
  `work_date` date NOT NULL,
  `check_in_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('present','late','corrected') DEFAULT 'present',
  `is_corrected` tinyint(1) DEFAULT 0,
  `corrected_by_user_id` bigint(20) DEFAULT NULL,
  `correction_notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'BR-MAIN', 'Panabo Main Campus', 'Panabo City', '2026-10-25 14:32:14', '2026-10-25 14:32:14');

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
(1, 2, 'EMP-2026-001', 'RFID-99887766', 'John', NULL, 'Doe', 2, 'Instructor I', 1, '2026-01-15', 'active', '2026-10-25 14:32:14', '2026-10-25 14:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `employee_status_histories`
--

CREATE TABLE `employee_status_histories` (
  `id` bigint(20) NOT NULL,
  `employee_id` bigint(20) NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `changed_by_user_id` bigint(20) NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 'employee', NULL),
(3, 'applicant', NULL);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `branch_id`, `role_id`, `username`, `email`, `password_hash`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'hr_admin', 'hr@school.edu.ph', '$2y$10$JxUW1KU2OmP8Eg6xYDtdXuPB5POw7hSjAyiuddGsYaskwa8RLAAxW', 1, '2026-10-25 14:32:14', '2026-10-25 16:48:39'),
(2, 1, 2, 'instructor_john', 'john.doe@school.edu.ph', '$2y$10$JxUW1KU2OmP8Eg6xYDtdXuPB5POw7hSjAyiuddGsYaskwa8RLAAxW', 1, '2026-10-25 14:32:14', '2026-10-25 16:48:39'),
(3, NULL, 3, 'airarocelnazareno', 'airarocelnazareno@gmail.com', '$2y$10$5CgnSRX32ZewLAIeUYT2aeyIaOj5SnY0x6UDH0U3qPxIoVhGvVqgS', 1, '2026-10-26 07:52:35', '2026-10-26 07:52:35');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_code` (`branch_code`);

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
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_employee_status` (`current_status`),
  ADD KEY `idx_rfid_card` (`rfid_card_number`);

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
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payslip_number` (`payslip_number`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `generated_by_user_id` (`generated_by_user_id`);

--
-- Indexes for table `pay_rates`
--
ALTER TABLE `pay_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_notification_logs`
--
ALTER TABLE `email_notification_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_status_histories`
--
ALTER TABLE `employee_status_histories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interview_schedules`
--
ALTER TABLE `interview_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pay_rates`
--
ALTER TABLE `pay_rates`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `pay_rates`
--
ALTER TABLE `pay_rates`
  ADD CONSTRAINT `pay_rates_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

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
