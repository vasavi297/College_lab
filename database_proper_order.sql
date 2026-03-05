/*
  Database Export: college_lab
  Generated: 2026-02-06 05:27:42
  Tables in correct foreign key order
*/

-- Disable foreign key checks during import
SET FOREIGN_KEY_CHECKS = 0;
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

DROP DATABASE IF EXISTS `college_lab`;
CREATE DATABASE `college_lab` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `college_lab`;

-- Step 1: Create all tables without foreign keys
-- Table: weekly_experiments
CREATE TABLE `weekly_experiments` (
  `weekly_id` int(11) NOT NULL AUTO_INCREMENT,
  `experiment_id` int(11) NOT NULL,
  `week_number` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `enabled_date` date NOT NULL,
  `enabled_by` int(11) DEFAULT NULL,
  `enabled_until` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `target_branch` varchar(255) DEFAULT NULL,
  `target_section` varchar(255) DEFAULT NULL,
  `target_students` text DEFAULT NULL,
  PRIMARY KEY (`weekly_id`),
  KEY `experiment_id` (`experiment_id`),
  KEY `enabled_by` (`enabled_by`),
  KEY `idx_week_year` (`week_number`,`year`),
  KEY `idx_enabled_until` (`enabled_until`),
  CONSTRAINT `weekly_experiments_ibfk_2` FOREIGN KEY (`enabled_by`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: timetable
CREATE TABLE `timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day_of_week` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject` varchar(100) NOT NULL,
  `employee_username` varchar(50) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `section` varchar(10) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_timetable_branch_semester` (`branch`,`semester`,`section`),
  KEY `idx_timetable_subject` (`subject`),
  KEY `idx_timetable_employee` (`employee_username`),
  CONSTRAINT `timetable_ibfk_2` FOREIGN KEY (`subject`) REFERENCES `subjects` (`subject`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: submissions
CREATE TABLE `submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `experiment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `submitted_date` datetime DEFAULT current_timestamp(),
  `verification_status` enum('Pending','Verified','Retake') DEFAULT 'Pending',
  `is_retake` tinyint(1) DEFAULT 0,
  `original_submission_id` int(11) DEFAULT NULL,
  `verification_date` datetime DEFAULT NULL,
  `submission_data` text DEFAULT NULL,
  `graph_data` longtext DEFAULT NULL,
  `has_graph` tinyint(4) DEFAULT 0,
  `marks_obtained` decimal(4,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `experiment_subject` varchar(100) DEFAULT NULL,
  `retake_count` int(11) DEFAULT 0,
  `last_retake_date` datetime DEFAULT NULL,
  `can_retake_again` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`submission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: student_subject_employees
CREATE TABLE `student_subject_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_username` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `employee_username` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_subject` (`student_username`,`subject`),
  KEY `fk_student_subject` (`subject`),
  CONSTRAINT `student_subject_employees_ibfk_1` FOREIGN KEY (`student_username`) REFERENCES `students` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: student_notifications
CREATE TABLE `student_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) DEFAULT 'exam_scheduled',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `student_id` (`student_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `student_notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: students
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `roll_number` varchar(20) NOT NULL,
  `branch` varchar(10) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `semester` varchar(10) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `linkedin` varchar(255) DEFAULT NULL,
  `github` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `roll_number` (`roll_number`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: semester_subject_assignments
CREATE TABLE `semester_subject_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `semester` varchar(10) NOT NULL,
  `branch` varchar(10) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_type` enum('BSH','PROFESSIONAL','ELECTIVE') DEFAULT 'BSH',
  `is_mandatory` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`semester`,`branch`,`subject_name`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: lab_schedules
CREATE TABLE `lab_schedules` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject` varchar(100) NOT NULL,
  `section` char(1) NOT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `branch` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `lab_schedules_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: experiments
CREATE TABLE `experiments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(50) NOT NULL,
  `experiment_number` int(11) NOT NULL,
  `experiment_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_subject_exp` (`subject`,`experiment_number`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: employee_subjects
CREATE TABLE `employee_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_username` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `section` varchar(10) NOT NULL,
  `semester` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignment` (`employee_username`,`subject`,`branch`,`section`),
  KEY `subject` (`subject`),
  CONSTRAINT `employee_subjects_ibfk_2` FOREIGN KEY (`subject`) REFERENCES `subjects` (`subject`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: subjects
CREATE TABLE `subjects` (
  `subject` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `semester` varchar(10) NOT NULL,
  PRIMARY KEY (`subject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: employees
CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'Staff',
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=793 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: announcements
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: admin_users
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_reports` (
  `report_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `report_type` varchar(64) NOT NULL DEFAULT 'PDF',
  `report_content` longtext NOT NULL,
  `generated_date` datetime DEFAULT current_timestamp(),
  UNIQUE KEY `uniq_student_report_type` (`student_id`,`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Step 2: Add foreign key constraints
-- Foreign keys for table: weekly_experiments
ALTER TABLE `weekly_experiments` ADD CONSTRAINT `weekly_experiments_ibfk_1` FOREIGN KEY (`experiment_id`) REFERENCES `experiments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `weekly_experiments` ADD CONSTRAINT `weekly_experiments_ibfk_2` FOREIGN KEY (`enabled_by`) REFERENCES `employees` (`employee_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Foreign keys for table: timetable
ALTER TABLE `timetable` ADD CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`employee_username`) REFERENCES `employees` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE `timetable` ADD CONSTRAINT `timetable_ibfk_2` FOREIGN KEY (`subject`) REFERENCES `subjects` (`subject`) ON DELETE CASCADE ON UPDATE RESTRICT;

-- Foreign keys for table: student_subject_employees
ALTER TABLE `student_subject_employees` ADD CONSTRAINT `fk_student_subject` FOREIGN KEY (`subject`) REFERENCES `subjects` (`subject`) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE `student_subject_employees` ADD CONSTRAINT `student_subject_employees_ibfk_1` FOREIGN KEY (`student_username`) REFERENCES `students` (`username`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Foreign keys for table: student_notifications
ALTER TABLE `student_notifications` ADD CONSTRAINT `student_notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

-- Foreign keys for table: lab_schedules
ALTER TABLE `lab_schedules` ADD CONSTRAINT `lab_schedules_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Foreign keys for table: employee_subjects
ALTER TABLE `employee_subjects` ADD CONSTRAINT `employee_subjects_ibfk_1` FOREIGN KEY (`employee_username`) REFERENCES `employees` (`username`) ON DELETE CASCADE ON UPDATE RESTRICT;
ALTER TABLE `employee_subjects` ADD CONSTRAINT `employee_subjects_ibfk_2` FOREIGN KEY (`subject`) REFERENCES `subjects` (`subject`) ON DELETE CASCADE ON UPDATE RESTRICT;



-- Data for table: students
INSERT INTO `students` VALUES ('1', '23A81A61B4', '12345', 'Poppoppula Vasavi', '23A81A61B4', 'AIML', 'B', 'I', 'vasavi2972006@gmail.com', '9866279622', '2025-09-30 14:47:25', 'https://www.linkedin.com/in/vasavi poppoppula', 'https://github//vasavi297');
INSERT INTO `students` VALUES ('2', '24A81A61C0', '12345', 'Rajahmundry Sravani', '24A81A61C0', 'AIML', 'A', 'I', NULL, NULL, '2025-09-30 14:47:25', NULL, NULL);
INSERT INTO `students` VALUES ('3', '23A81A6193', '12345', 'Kadali Anya Sree', '23A81A6193', 'AIML', 'B', 'I', 'anyasreekadali@gmail.com', '8639862772', '2025-09-30 14:47:25', NULL, NULL);
INSERT INTO `students` VALUES ('4', '23a81a61b5', '12345', 'Ravi', '23a81a61b5', 'MECH', 'A', 'I', 'ravi@gmail.com', '9866279622', '2026-01-28 15:10:52', NULL, NULL);
INSERT INTO `students` VALUES ('5', '23A81A61D1', '12345', 'varsha', '23A81A61D1', 'MECH', 'A', 'IV', 'john.doe@example.com', '9876543210', '2026-01-30 18:33:48', NULL, NULL);

-- Data for table: semester_subject_assignments
INSERT INTO `semester_subject_assignments` VALUES ('1', 'I', 'AIML', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('5', 'I', 'CSE', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('9', 'I', 'CSD', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('13', 'I', 'CST', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('17', 'I', 'ECE', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('21', 'I', 'MECH', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('25', 'I', 'CIVIL', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('29', 'I', 'EEE', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('33', 'II', 'AIML', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('36', 'II', 'CSE', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('39', 'II', 'CSD', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('42', 'II', 'CST', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('45', 'II', 'ECE', 'Physics', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('48', 'II', 'MECH', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('51', 'II', 'CIVIL', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('54', 'II', 'EEE', 'Chemistry', 'BSH', '1');
INSERT INTO `semester_subject_assignments` VALUES ('62', 'III', 'MECH', 'Theory of Machines', 'PROFESSIONAL', '1');

-- Data for table: experiments
INSERT INTO `experiments` VALUES ('1', 'Chemistry', '1', 'Introduction to Chemistry Laboratory', 'experiments/chemistry/exp1.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:11:49');
INSERT INTO `experiments` VALUES ('2', 'Chemistry', '2', 'Estimation of ferrous Ion', 'experiments/chemistry/exp2.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:11:58');
INSERT INTO `experiments` VALUES ('3', 'Chemistry', '3', 'Preparation of Phenol', 'experiments/chemistry/exp3.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:12:06');
INSERT INTO `experiments` VALUES ('4', 'Chemistry', '4', 'Determination of Strength of an Acid in Pb - Acid Battery', 'experiments/chemistry/exp4.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:12:15');
INSERT INTO `experiments` VALUES ('5', 'Chemistry', '5', 'Conductometric Titration (Strong Acid vs Strong Base)', 'experiments/chemistry/exp5.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:12:24');
INSERT INTO `experiments` VALUES ('6', 'Chemistry', '6', 'Conductomatric Titration (Weak Acid vs Strong Base)', 'experiments/chemistry/exp6.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:12:33');
INSERT INTO `experiments` VALUES ('7', 'Chemistry', '7', 'Determination of Cell Constant and Conductance of Solution', 'experiments/chemistry/exp7.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:12:43');
INSERT INTO `experiments` VALUES ('8', 'Chemistry', '8', 'Estimation of Iron using Potentiometry', 'experiments/chemistry/exp8.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:12:54');
INSERT INTO `experiments` VALUES ('9', 'Chemistry', '9', 'Verify Beer-Lamberts Law', 'experiments/chemistry/exp9.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:13:05');
INSERT INTO `experiments` VALUES ('10', 'Chemistry', '10', 'WaveLength Measurement of Sample through UV-Visible Spectroscopy', 'experiments/chemistry/exp10.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:13:16');
INSERT INTO `experiments` VALUES ('11', 'Chemistry', '11', 'Measurement of 10 dq By Spectrophotometric Method', 'experiments/chemistry/exp11.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:13:27');
INSERT INTO `experiments` VALUES ('12', 'Chemistry', '12', 'Identification of Simple Organic Compounds by IR', 'experiments/chemistry/exp12.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:13:36');
INSERT INTO `experiments` VALUES ('13', 'Chemistry', '13', 'Preparation of Nanomaterials', 'experiments/chemistry/exp13.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:13:45');
INSERT INTO `experiments` VALUES ('14', 'Theory of Machines', '1', 'Hartneu Governor', 'experiments/Theoryofmachines/exp1.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:10:52');
INSERT INTO `experiments` VALUES ('15', 'Theory of Machines', '2', 'Whirling of Shaft', 'experiments/Theoryofmachines/exp2.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:11:05');
INSERT INTO `experiments` VALUES ('16', 'Theory of Machines', '3', 'Natural Frequency of Single Degree Undamped Free Vibrations', 'experiments/Theoryofmachines/exp3.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:11:15');
INSERT INTO `experiments` VALUES ('17', 'Theory of Machines', '4', 'Compound Screw Jack', 'experiments/Theoryofmachines/exp4.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:11:25');
INSERT INTO `experiments` VALUES ('18', 'Theory of Machines', '5', 'Study of Gears', 'experiments/Theoryofmachines/exp5.php', '1', '2025-10-04 19:27:34', '2026-01-30 11:11:35');

-- Data for table: subjects
INSERT INTO `subjects` VALUES ('C++ Programming Lab', 'CSE', 'I');
INSERT INTO `subjects` VALUES ('Chemistry', 'BSH', 'I');
INSERT INTO `subjects` VALUES ('Data Structures', 'AIML', 'II');
INSERT INTO `subjects` VALUES ('Physics', 'BSH', 'I');
INSERT INTO `subjects` VALUES ('Theory of Machines', 'Mechanical', 'III');

-- Data for table: employees
INSERT INTO `employees` VALUES ('123', '23a81a6154', '12345', 'varun', 'admin@college.edu', 'Staff', 'BSH', '9876543210', '2026-01-11 06:16:27', '2026-02-06 09:48:06', '1');
INSERT INTO `employees` VALUES ('456', '23a81a61b9', '12345', 'ramya', 'john@college.edu', 'Staff', 'MECH', '9876543211', '2026-01-11 06:16:27', '2026-02-03 21:10:49', '1');
INSERT INTO `employees` VALUES ('789', '23a81a61b3', '12345', 'rahul', 'jane@college.edu', 'Staff', 'ECE', '9876543212', '2026-01-11 06:16:27', NULL, '1');
INSERT INTO `employees` VALUES ('790', 'ADMIN', '12345', 'Admin', '', 'admin', NULL, NULL, '2026-02-01 11:34:48', '2026-02-05 19:55:49', '1');
INSERT INTO `employees` VALUES ('791', '23a81a6172', '12345', 'Srinivas', 'vasavi2972006@gmail.com', 'Staff', 'ECE', '9866279622', '2026-02-01 11:59:37', NULL, '1');
INSERT INTO `employees` VALUES ('792', '23a81a6184', '12345', 'Rohitha', 'rohitha@gmail.com', 'Staff', 'CIVIL', '9866279622', '2026-02-01 12:02:55', NULL, '1');

-- Data for table: announcements
INSERT INTO `announcements` VALUES ('1', 'Meeting', 'There will be a Meeting Regarding The semester exams at 10 am in the Morning.', '2026-02-04 16:03:06');
INSERT INTO `announcements` VALUES ('2', 'fgcvbhnj', 'fhgvbhnj,', '2026-02-05 19:13:02');
INSERT INTO `announcements` VALUES ('3', 'cvbnm', 'cvbn', '2026-02-05 19:13:26');

-- Data for table: admin_users
INSERT INTO `admin_users` VALUES ('1', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'admin@college.edu', '2026-02-01 10:59:16');

-- Data for table: weekly_experiments
INSERT INTO `weekly_experiments` VALUES ('47', '1', '5', '2026', '2026-01-31', '123', '2026-02-07', '0', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('48', '1', '5', '2026', '2026-01-31', '123', '2026-01-31', '0', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('49', '5', '5', '2026', '2026-01-31', '123', '2026-02-07', '1', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('50', '1', '5', '2026', '2026-01-31', '123', '2026-01-31', '0', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('51', '1', '5', '2026', '2026-01-31', '123', '2026-02-07', '1', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('52', '3', '5', '2026', '2026-01-31', '123', '2026-02-07', '1', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('53', '2', '5', '2026', '2026-01-31', '123', '2026-02-07', '1', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('54', '14', '5', '2026', '2026-01-31', '456', '2026-02-07', '0', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('55', '15', '5', '2026', '2026-01-31', '456', '2026-02-07', '1', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('56', '4', '5', '2026', '2026-01-31', '123', '2026-02-01', '0', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('57', '16', '5', '2026', '2026-01-31', '456', '2026-02-07', '1', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('58', '17', '5', '2026', '2026-01-31', '456', '2026-02-07', '1', NULL, NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('59', '18', '5', '2026', '2026-01-31', '456', '2026-02-07', '1', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('60', '6', '5', '2026', '2026-02-01', '123', '2026-02-08', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('61', '7', '6', '2026', '2026-02-02', '123', '0000-00-00', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('62', '7', '6', '2026', '2026-02-02', '123', '0000-00-00', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('63', '7', '6', '2026', '2026-02-02', '123', '0000-00-00', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('64', '7', '6', '2026', '2026-02-02', '123', '0000-00-00', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('65', '7', '6', '2026', '2026-02-02', '123', '0000-00-00', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('66', '6', '6', '2026', '2026-02-02', '123', '0000-00-00', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('67', '6', '6', '2026', '0000-00-00', '123', '0000-00-00', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('68', '6', '6', '2026', '0000-00-00', '123', '0000-00-00', '0', '0', 'B');
INSERT INTO `weekly_experiments` VALUES ('69', '6', '6', '2026', '2026-02-02', '123', '2026-02-09', '0', '0', 'B');
INSERT INTO `weekly_experiments` VALUES ('70', '1', '6', '2026', '2026-02-02', '123', '2026-02-09', '0', 'AIML', 'B');
INSERT INTO `weekly_experiments` VALUES ('71', '6', '6', '2026', '2026-02-02', '123', '2026-02-09', '0', 'AIML,CSD', 'B');
INSERT INTO `weekly_experiments` VALUES ('72', '3', '6', '2026', '2026-02-02', '123', '2026-02-09', '1', 'AIML', 'B');
INSERT INTO `weekly_experiments` VALUES ('73', '2', '6', '2026', '2026-02-02', '123', '2026-02-09', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('74', '14', '6', '2026', '2026-02-02', '123', '2026-02-09', '1', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('75', '4', '6', '2026', '2026-02-02', '123', '2026-02-09', '1', 'AIML', 'B');
INSERT INTO `weekly_experiments` VALUES ('76', '1', '6', '2026', '2026-02-05', '123', '2026-02-12', '0', NULL, NULL);
INSERT INTO `weekly_experiments` VALUES ('77', '5', '6', '2026', '2026-02-05', '123', '2026-02-12', '1', NULL, NULL);

-- Data for table: timetable
INSERT INTO `timetable` VALUES ('4', 'Tuesday', '16:54:00', '17:54:00', 'Chemistry', '23a81a6154', 'AIML', 'B', 'I', '2026-02-01 14:56:12');
INSERT INTO `timetable` VALUES ('5', 'Monday', '16:04:00', '16:06:00', 'Chemistry', '23a81a61b9', 'AIML', 'A', 'I', '2026-02-03 16:02:50');

-- Data for table: student_subject_employees
INSERT INTO `student_subject_employees` VALUES ('1', '23A81A61B4', 'Chemistry', '23a81a6154');
INSERT INTO `student_subject_employees` VALUES ('3', '23A81A6193', 'Chemistry', '23a81a6154');
INSERT INTO `student_subject_employees` VALUES ('4', '23a81a61b5', 'Chemistry', '23a81a6154');
INSERT INTO `student_subject_employees` VALUES ('5', '23a81a61b5', 'Theory of Machines', '23a81a61b9');
INSERT INTO `student_subject_employees` VALUES ('8', '23a81a61d1', 'Theory of Machines', '23a81a6154');

-- Data for table: student_notifications
INSERT INTO `student_notifications` VALUES ('1', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 02, 2026 from 12:00 to 15:00.', 'exam_scheduled', '1', '2026-02-02 12:00:51', '2026-02-04 14:52:43');
INSERT INTO `student_notifications` VALUES ('2', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 02, 2026 from 12:00 to 15:00.', 'exam_scheduled', '0', '2026-02-02 12:00:51', '2026-02-02 12:00:51');
INSERT INTO `student_notifications` VALUES ('4', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 12:13 to 12:14. Instructions: there will be a exam tomorrow', 'exam_scheduled', '1', '2026-02-02 12:13:30', '2026-02-04 14:52:43');
INSERT INTO `student_notifications` VALUES ('5', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 12:13 to 12:14. Instructions: there will be a exam tomorrow', 'exam_scheduled', '0', '2026-02-02 12:13:30', '2026-02-02 12:13:30');
INSERT INTO `student_notifications` VALUES ('7', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 12:13 to 12:14. Instructions: there will be a exam tomorrow', 'exam_scheduled', '1', '2026-02-02 12:18:23', '2026-02-04 14:52:43');
INSERT INTO `student_notifications` VALUES ('8', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 12:13 to 12:14. Instructions: there will be a exam tomorrow', 'exam_scheduled', '0', '2026-02-02 12:18:23', '2026-02-02 12:18:23');
INSERT INTO `student_notifications` VALUES ('10', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 14:20 to 16:22. Instructions: There will be an exam tomorrow so Please prepare well for the Exam.', 'exam_scheduled', '1', '2026-02-02 12:19:12', '2026-02-04 14:52:43');
INSERT INTO `student_notifications` VALUES ('11', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 14:20 to 16:22. Instructions: There will be an exam tomorrow so Please prepare well for the Exam.', 'exam_scheduled', '0', '2026-02-02 12:19:12', '2026-02-02 12:19:12');
INSERT INTO `student_notifications` VALUES ('13', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 14:20 to 16:22. Instructions: There will be an exam tomorrow so Please prepare well for the Exam.', 'exam_scheduled', '1', '2026-02-02 12:21:45', '2026-02-04 14:52:43');
INSERT INTO `student_notifications` VALUES ('14', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 14:20 to 16:22. Instructions: There will be an exam tomorrow so Please prepare well for the Exam.', 'exam_scheduled', '0', '2026-02-02 12:21:45', '2026-02-02 12:21:45');
INSERT INTO `student_notifications` VALUES ('16', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 14:20 to 16:22. Instructions: There will be an exam tomorrow so Please prepare well for the Exam.', 'exam_scheduled', '1', '2026-02-02 12:22:18', '2026-02-04 14:52:43');
INSERT INTO `student_notifications` VALUES ('17', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 03, 2026 from 14:20 to 16:22. Instructions: There will be an exam tomorrow so Please prepare well for the Exam.', 'exam_scheduled', '0', '2026-02-02 12:22:18', '2026-02-02 12:22:18');
INSERT INTO `student_notifications` VALUES ('19', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 05, 2026 from 12:22 to 14:24. Instructions: There will be an exam tomorrow', 'exam_scheduled', '1', '2026-02-02 12:22:49', '2026-02-04 14:52:43');
INSERT INTO `student_notifications` VALUES ('20', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 05, 2026 from 12:22 to 14:24. Instructions: There will be an exam tomorrow', 'exam_scheduled', '0', '2026-02-02 12:22:49', '2026-02-02 12:22:49');
INSERT INTO `student_notifications` VALUES ('22', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 05, 2026 from 14:56 to 17:54. Instructions: Prepare well for the Exam there will be an exam tomorrow...', 'exam_scheduled', '1', '2026-02-04 14:54:46', '2026-02-04 14:54:56');
INSERT INTO `student_notifications` VALUES ('23', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 05, 2026 from 14:56 to 17:54. Instructions: Prepare well for the Exam there will be an exam tomorrow...', 'exam_scheduled', '0', '2026-02-04 14:54:46', '2026-02-04 14:54:46');
INSERT INTO `student_notifications` VALUES ('24', '1', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 05, 2026 from 14:56 to 17:55. Instructions: fgmd hjy', 'exam_scheduled', '1', '2026-02-04 14:56:12', '2026-02-04 14:56:39');
INSERT INTO `student_notifications` VALUES ('25', '3', 'Lab Exam Scheduled - Chemistry', 'Lab exam for Chemistry scheduled on Feb 05, 2026 from 14:56 to 17:55. Instructions: fgmd hjy', 'exam_scheduled', '0', '2026-02-04 14:56:12', '2026-02-04 14:56:12');

-- Data for table: lab_schedules
INSERT INTO `lab_schedules` VALUES ('1', '123', '2026-02-03', '11:00:00', '13:00:00', 'Chemistry', 'B', 'There will be a lab exam tomorrow.', '2026-02-02 11:16:36', 'AIML');
INSERT INTO `lab_schedules` VALUES ('2', '123', '2026-02-02', '12:00:00', '15:00:00', 'Chemistry', 'B', 'There will be exam tomorrow\r\n', '2026-02-02 12:00:51', 'AIML');
INSERT INTO `lab_schedules` VALUES ('3', '123', '2026-02-03', '12:13:00', '12:14:00', 'Chemistry', 'B', 'there will be a exam tomorrow', '2026-02-02 12:13:30', 'AIML');
INSERT INTO `lab_schedules` VALUES ('4', '123', '2026-02-03', '12:13:00', '12:14:00', 'Chemistry', 'B', 'there will be a exam tomorrow', '2026-02-02 12:18:23', 'AIML');
INSERT INTO `lab_schedules` VALUES ('5', '123', '2026-02-03', '14:20:00', '16:22:00', 'Chemistry', 'B', 'There will be an exam tomorrow so Please prepare well for the Exam.', '2026-02-02 12:19:12', 'AIML');
INSERT INTO `lab_schedules` VALUES ('6', '123', '2026-02-03', '14:20:00', '16:22:00', 'Chemistry', 'B', 'There will be an exam tomorrow so Please prepare well for the Exam.', '2026-02-02 12:21:45', 'AIML');
INSERT INTO `lab_schedules` VALUES ('7', '123', '2026-02-03', '14:20:00', '16:22:00', 'Chemistry', 'B', 'There will be an exam tomorrow so Please prepare well for the Exam.', '2026-02-02 12:22:18', 'AIML');
INSERT INTO `lab_schedules` VALUES ('8', '123', '2026-02-05', '12:22:00', '14:24:00', 'Chemistry', 'B', 'There will be an exam tomorrow', '2026-02-02 12:22:49', 'AIML');
INSERT INTO `lab_schedules` VALUES ('9', '123', '2026-02-05', '14:56:00', '17:54:00', 'Chemistry', 'B', 'Prepare well for the Exam there will be an exam tomorrow...', '2026-02-04 14:54:46', 'AIML');
INSERT INTO `lab_schedules` VALUES ('10', '123', '2026-02-05', '14:56:00', '17:55:00', 'Chemistry', 'B', 'fgmd hjy', '2026-02-04 14:56:12', 'AIML');

-- Data for table: employee_subjects
INSERT INTO `employee_subjects` VALUES ('1', '23a81a6154', 'Chemistry', 'AIML', 'B', 'I');
INSERT INTO `employee_subjects` VALUES ('2', '23a81a6154', 'Theory of Machines', 'MECH', 'A', 'V');
INSERT INTO `employee_subjects` VALUES ('6', '23a81a61b9', 'Chemistry', 'CSE', 'B', 'I');


-- Step 4: Restore database settings
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET SQL_MODE = @OLD_SQL_MODE;
