-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2024 at 10:30 PM
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
-- Database: `questionnaire`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password_hash`, `email`) VALUES
(1, 'admin', '$2y$10$91gMqq6tVS.FyNpizHF.9.4wl5seyAZbtlU6w.Mf48ICRTp40lymC', 'dyaa6a@gmail.com'),
(3, 'admin2', '$2y$10$ICoaKD5D712EJOPQrTFaU./7koXIBsI0NjHFncFGjjtMKIdPqJ5jG', 'dyaa6a@gmai.com');

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `answer_id` int(10) UNSIGNED NOT NULL,
  `response_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `answer_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `answers`
--

INSERT INTO `answers` (`answer_id`, `response_id`, `question_id`, `answer_text`) VALUES
(1, 5, 1, 'test 1'),
(2, 5, 2, 'test 2'),
(3, 6, 8, 'لا توجد اجابة'),
(5, 7, 8, '4'),
(6, 7, 12, '5'),
(7, 7, 13, '6'),
(8, 7, 14, 'اقتراحات اخرى'),
(9, 8, 8, '8'),
(10, 8, 12, '2'),
(11, 8, 13, '9'),
(12, 8, 14, 'جيد'),
(13, 9, 8, '3'),
(14, 9, 12, '4'),
(15, 9, 13, '5'),
(16, 9, 14, 'جيد 1'),
(17, 10, 8, '3'),
(18, 10, 12, '4'),
(19, 10, 13, '5'),
(20, 10, 14, 'جيد 1'),
(21, 11, 8, '3'),
(22, 11, 12, '4'),
(23, 11, 13, '5'),
(24, 11, 14, 'جيد 1'),
(25, 12, 8, '3'),
(26, 12, 12, '7'),
(27, 12, 13, '7'),
(28, 12, 14, 'هناك مقترح آخر');

-- --------------------------------------------------------

--
-- Table structure for table `choices`
--

CREATE TABLE `choices` (
  `choice_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `choice_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questionnaires`
--

CREATE TABLE `questionnaires` (
  `questionnaire_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questionnaires`
--

INSERT INTO `questionnaires` (`questionnaire_id`, `title`, `description`, `created_at`) VALUES
(1, 'تقييم المطعم الاول', 'الأجواء تشمل الهدوء والموسيقى ومدى شعورك بالراحة أثناء جلوسك في المطعم', '2024-09-19 21:54:22'),
(2, 'تقييم المطعم الثاني', 'وصف الاستبيان الثاني', '2024-09-20 00:18:21');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(10) UNSIGNED NOT NULL,
  `questionnaire_id` int(10) UNSIGNED NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('text','textarea','choice','stars') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `questionnaire_id`, `question_text`, `question_type`) VALUES
(1, 1, 'ما هو تقييمك لديكور المطعم؟', 'textarea'),
(2, 1, 'ما هو تقييمك للخدمة / العاملين في المطعم؟', 'text'),
(3, 1, 'كيف تقيم الطعام / الوجبة التي تناولتها؟', ''),
(6, 1, 'سؤال لا يحتوي على تقييم بالنجوم', 'textarea'),
(7, 1, 'سؤال لا يحتوي على تقييم بالنجوم', ''),
(8, 2, 'السؤال الاول الخاص بالمطعم الثاني', 'stars'),
(12, 2, 'السؤال الثاني الخاص بالمطعم الثاني', 'stars'),
(13, 2, 'السؤال الثالث الخاص بالمطعم الثاني', 'stars'),
(14, 2, 'هل لديك اي اقتراحات اخرى؟', 'textarea');

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `response_id` int(10) UNSIGNED NOT NULL,
  `questionnaire_id` int(10) UNSIGNED NOT NULL,
  `user_ip` varchar(45) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `responses`
--

INSERT INTO `responses` (`response_id`, `questionnaire_id`, `user_ip`, `submitted_at`) VALUES
(1, 1, '::1', '2024-09-19 22:08:44'),
(2, 1, '::1', '2024-09-19 22:09:01'),
(3, 1, '::1', '2024-09-19 22:11:52'),
(4, 1, '::1', '2024-09-19 22:14:26'),
(5, 1, '::1', '2024-09-19 22:14:42'),
(6, 2, '::1', '2024-09-20 12:35:24'),
(7, 2, '::1', '2024-09-20 14:00:49'),
(8, 2, '::1', '2024-09-21 15:18:15'),
(9, 2, '::1', '2024-09-21 15:18:26'),
(10, 2, '::1', '2024-09-21 15:20:42'),
(11, 2, '::1', '2024-09-21 15:20:45'),
(12, 2, '::1', '2024-09-21 19:42:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `idx_response_id` (`response_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `choices`
--
ALTER TABLE `choices`
  ADD PRIMARY KEY (`choice_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `questionnaires`
--
ALTER TABLE `questionnaires`
  ADD PRIMARY KEY (`questionnaire_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `idx_questionnaire_id` (`questionnaire_id`);

--
-- Indexes for table `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `idx_questionnaire_id` (`questionnaire_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `answer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `choices`
--
ALTER TABLE `choices`
  MODIFY `choice_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questionnaires`
--
ALTER TABLE `questionnaires`
  MODIFY `questionnaire_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `responses`
--
ALTER TABLE `responses`
  MODIFY `response_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `fk_answers_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_answers_responses` FOREIGN KEY (`response_id`) REFERENCES `responses` (`response_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `choices`
--
ALTER TABLE `choices`
  ADD CONSTRAINT `fk_choices_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_questionnaires` FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires` (`questionnaire_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `fk_responses_questionnaires` FOREIGN KEY (`questionnaire_id`) REFERENCES `questionnaires` (`questionnaire_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
