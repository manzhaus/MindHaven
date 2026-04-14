-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 07:48 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mindhaven`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_files`
--

CREATE TABLE `chat_files` (
  `id` int(11) NOT NULL,
  `message_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `anon_id` varchar(100) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_files`
--

INSERT INTO `chat_files` (`id`, `message_id`, `user_id`, `anon_id`, `file_name`, `file_path`, `file_type`, `uploaded_at`) VALUES
(1, NULL, 1, NULL, 'MindHaven Poster.pdf', 'uploads/chat_files/1753826100_MindHaven_Poster.pdf', 'application/pdf', '2025-07-30 05:55:00'),
(2, NULL, 1, NULL, 'MindHaven Poster.pdf', 'uploads/chat_files/1753826321_MindHaven_Poster.pdf', 'application/pdf', '2025-07-30 05:58:41'),
(3, NULL, 1, NULL, 'Screenshot 2024-11-05 235713.png', 'uploads/chat_files/1753826334_Screenshot_2024-11-05_235713.png', 'image/png', '2025-07-30 05:58:54'),
(4, NULL, 1, NULL, 'MindHaven Poster.pdf', 'uploads/chat_files/1753826918_MindHaven_Poster.pdf', 'application/pdf', '2025-07-30 06:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `anon_id` varchar(255) DEFAULT NULL,
  `sender_type` enum('user','counsellor') NOT NULL,
  `message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `anon_id`, `sender_type`, `message`, `timestamp`, `created_at`) VALUES
(26, 1, NULL, 'user', 'help', '2025-06-23 07:45:17', '2025-07-09 07:01:34'),
(27, 1, NULL, 'user', 'me', '2025-06-23 07:45:37', '2025-07-09 07:01:34'),
(28, 1, NULL, 'counsellor', 'what happened', '2025-06-23 07:45:48', '2025-07-09 07:01:34'),
(29, 1, NULL, 'user', 'hi', '2025-06-23 10:42:01', '2025-07-09 07:01:34'),
(30, 1, NULL, 'counsellor', 'lndvlsakfjdmmenkrv', '2025-06-23 10:53:46', '2025-07-09 07:01:34'),
(31, 1, NULL, 'user', 'hi', '2025-06-23 10:55:32', '2025-07-09 07:01:34'),
(32, 1, NULL, 'counsellor', 'test', '2025-06-23 11:01:38', '2025-07-09 07:01:34'),
(33, 1, NULL, 'user', 'test', '2025-06-23 11:03:54', '2025-07-09 07:01:34'),
(34, 1, NULL, 'user', 'yabedabedu', '2025-06-23 11:07:04', '2025-07-09 07:01:34'),
(35, 1, NULL, 'counsellor', 'tung tung tung sahur', '2025-06-23 11:07:37', '2025-07-09 07:01:34'),
(36, 1, NULL, 'user', 'ballerina cappucina', '2025-06-23 11:12:36', '2025-07-09 07:01:34'),
(37, 1, NULL, 'counsellor', 'tralalero tralala', '2025-06-23 11:13:05', '2025-07-09 07:01:34'),
(38, 1, NULL, 'counsellor', 'hello', '2025-06-23 14:22:59', '2025-07-09 07:01:34'),
(39, 1, NULL, 'counsellor', 'gvjh', '2025-06-23 14:23:42', '2025-07-09 07:01:34'),
(40, 1, NULL, 'counsellor', 'mand', '2025-06-26 15:33:20', '2025-07-09 07:01:34'),
(41, 1, NULL, 'user', 'hello', '2025-06-26 15:33:36', '2025-07-09 07:01:34'),
(42, 4, NULL, 'user', 'hi counsellor', '2025-07-07 22:58:11', '2025-07-09 07:01:34'),
(43, 1, NULL, 'user', 'hello', '2025-07-07 22:59:55', '2025-07-09 07:01:34'),
(44, 4, NULL, 'user', 'sir', '2025-07-07 23:00:24', '2025-07-09 07:01:34'),
(45, 4, NULL, 'user', 'help me', '2025-07-07 23:00:32', '2025-07-09 07:01:34'),
(46, 4, NULL, 'counsellor', 'what is it', '2025-07-07 23:00:42', '2025-07-09 07:01:34'),
(64, 1, NULL, 'user', 'hellooooo', '2025-07-09 17:08:32', '2025-07-09 09:08:32'),
(65, 1, NULL, 'counsellor', 'yes?', '2025-07-09 17:08:38', '2025-07-09 09:08:38'),
(66, 1, NULL, 'counsellor', 'hellooo', '2025-07-09 17:13:36', '2025-07-09 09:13:36'),
(67, 1, NULL, 'counsellor', 'do you have any issue lately?', '2025-07-09 17:14:02', '2025-07-09 09:14:02'),
(68, 1, NULL, 'user', 'hiiii', '2025-07-12 00:49:55', '2025-07-11 16:49:55'),
(69, 1, NULL, 'user', 'i need a guidance', '2025-07-12 00:50:03', '2025-07-11 16:50:03'),
(70, 1, NULL, 'user', 'hello', '2025-07-30 08:37:34', '2025-07-30 00:37:34'),
(71, 1, NULL, 'user', 'hii', '2025-07-30 08:38:05', '2025-07-30 00:38:05'),
(72, 1, NULL, 'user', 'help', '2025-07-30 08:38:27', '2025-07-30 00:38:27'),
(73, 1, NULL, 'user', 'excuse me', '2025-07-30 08:38:36', '2025-07-30 00:38:36'),
(74, 1, NULL, 'user', 'can i ask something', '2025-07-30 09:40:58', '2025-07-30 01:40:58'),
(75, NULL, 'anon_68898b39b7643', 'user', 'hallo', '2025-07-30 11:02:28', '2025-07-30 03:02:28'),
(76, 1, NULL, 'user', 'jvkb', '2025-07-30 11:11:25', '2025-07-30 03:11:25'),
(77, 1, NULL, 'user', 'i need help', '2025-07-30 11:21:17', '2025-07-30 03:21:17'),
(78, 1, NULL, 'user', 'hello', '2025-07-30 11:21:27', '2025-07-30 03:21:27'),
(79, NULL, 'anon_68898fee50fa8', 'user', 'helloooo', '2025-07-30 11:24:36', '2025-07-30 03:24:36'),
(80, NULL, 'anon_68898fee50fa8', 'user', 'hii', '2025-07-30 11:24:48', '2025-07-30 03:24:48'),
(81, NULL, 'anon_68898fee50fa8', 'user', 'hguyf', '2025-07-30 11:25:13', '2025-07-30 03:25:13'),
(82, NULL, 'anon_6889925e79e94', 'user', 'hello there', '2025-07-30 11:34:04', '2025-07-30 03:34:04'),
(83, NULL, 'anon_6889925e79e94', 'user', 'hbdayubd', '2025-07-30 11:34:12', '2025-07-30 03:34:12'),
(84, NULL, 'anon_6889925e79e94', 'user', 'i need someone to talk to', '2025-07-30 11:34:24', '2025-07-30 03:34:24'),
(85, NULL, 'anon_68f704e41b63c', 'user', '65fgyujk', '2025-10-21 11:58:38', '2025-10-21 03:58:38'),
(86, 1, NULL, 'user', 'sadviasdvlaifdvbeq', '2025-10-29 16:22:26', '2025-10-29 08:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `feedbacks`
--

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`id`, `user_id`, `name`, `email`, `message`, `rating`, `submitted_at`) VALUES
(1, 1, '', '', 'nice app very helpful', 5, '2025-06-22 19:57:41'),
(2, 2, 'anon', '', 'appreciated', 5, '2025-06-23 02:22:40');

-- --------------------------------------------------------

--
-- Table structure for table `gad7_history`
--

CREATE TABLE `gad7_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `interpretation` varchar(100) DEFAULT NULL,
  `date_taken` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gad7_history`
--

INSERT INTO `gad7_history` (`id`, `user_id`, `score`, `interpretation`, `date_taken`) VALUES
(1, 1, 9, 'Mild anxiety', '2025-06-21 04:49:16'),
(2, 1, 0, 'Minimal anxiety', '2025-06-21 04:52:10'),
(3, 1, 9, 'Mild anxiety', '2025-07-30 02:59:38'),
(4, 1, 2, 'Minimal anxiety', '2025-07-30 03:00:20'),
(5, 1, 16, 'Severe anxiety', '2025-07-30 03:00:39');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
(8, 'lurhkaf030224@gmail.com', '2a65aa440e112ff3e234ea983773c5cb56b2d12f2198bad46ce8bf6a9c1f19a2', '2025-07-31 05:04:05'),
(9, 'lurhkaf030224@gmail.com', '4bf7326ca6ff5e50f4a3c8d50878c02b67f476a7eda012801f88d657b0564c82', '2025-07-31 05:04:45');

-- --------------------------------------------------------

--
-- Table structure for table `phq9_history`
--

CREATE TABLE `phq9_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `interpretation` varchar(100) NOT NULL,
  `date_taken` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phq9_history`
--

INSERT INTO `phq9_history` (`id`, `user_id`, `score`, `interpretation`, `date_taken`) VALUES
(1, 1, 3, 'Minimal depression', '2025-06-21 09:32:25'),
(2, 1, 0, 'Minimal depression', '2025-06-21 12:15:45'),
(3, 1, 27, 'Severe depression', '2025-06-21 12:33:43'),
(4, 1, 10, 'Moderate depression', '2025-06-21 12:34:04'),
(5, 1, 9, 'Mild depression', '2025-06-23 14:10:53'),
(6, 1, 17, 'Moderately severe depression', '2025-06-26 15:29:42'),
(7, 1, 14, 'Moderate depression', '2025-07-30 10:59:58'),
(8, 1, 14, 'Moderate depression', '2025-07-30 11:01:07'),
(9, 1, 6, 'Mild depression', '2025-07-30 11:17:37'),
(10, 1, 14, 'Moderate depression', '2025-10-29 16:18:59'),
(11, 1, 14, 'Moderate depression', '2025-10-29 16:19:53');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resourceID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(500) NOT NULL,
  `type` enum('Article','Video','Pdf','Other') NOT NULL,
  `dateAdded` datetime DEFAULT current_timestamp(),
  `tags` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resourceID`, `title`, `description`, `link`, `type`, `dateAdded`, `tags`) VALUES
(1, 'Understanding Depression', 'An article explaining symptoms and treatments of depression.', 'https://www.helpguide.org/articles/depression/coping-with-depression.htm', 'Article', '2025-05-05 05:06:51', 'depression, mental health, mood disorders'),
(2, 'Guided Meditation for Anxiety', 'A calming video to reduce anxiety using mindfulness.', 'https://www.youtube.com/embed/O-6f5wQXSu8', 'Video', '2025-05-05 05:06:51', 'anxiety, meditation, relaxation, breathing'),
(3, 'Stress Management Techniques', 'Tips and strategies to manage daily stress effectively.', 'https://www.mentalhealth.org.uk/explore-mental-health/a-z-topics/stress', 'Article', '2025-05-05 08:56:11', 'stress, coping strategies, mental health, self-care'),
(4, 'Understanding Anxiety Disorders', 'Comprehensive guide on different types of anxiety disorders.', 'https://www.nimh.nih.gov/health/topics/anxiety-disorders', 'Article', '2025-05-05 08:56:11', 'anxiety, mental health, panic, worry'),
(5, 'Mindfulness for Beginners', 'A video introduction to mindfulness and its mental health benefits.', 'https://www.youtube.com/embed/1vx8iUvfyCY', 'Video', '2025-05-05 08:56:11', 'mindfulness, focus, self-awareness, beginners'),
(13, 'Guided Meditation for Anxiety & Stress', 'A calming 10-minute guided meditation to help with anxiety, stress, and emotional grounding.', 'https://www.youtube.com/watch?v=MIr3RsUWrdo', 'Video', '2025-07-07 22:45:21', 'anxiety, stress, meditation, calm, relaxation'),
(15, '5-Minute Meditation You Can Do Anywhere', 'A short guided meditation by Headspace to help reduce stress and improve focus.', 'https://www.youtube.com/watch?v=inpok4MKVLM', 'Video', '2025-07-29 22:55:49', 'stress,mindfulness,meditation,relaxation,focus'),
(16, 'Mental Health America (MHA)', 'Nonprofit offering screening tools, resources, and support for mental wellness', 'https://www.mhanational.org/', 'Article', '2025-07-30 08:42:07', 'mental wellness,mindfulness');

-- --------------------------------------------------------

--
-- Table structure for table `saved_resources`
--

CREATE TABLE `saved_resources` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `date_saved` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_resources`
--

INSERT INTO `saved_resources` (`id`, `user_id`, `resource_id`, `date_saved`) VALUES
(5, 4, 15, '2025-07-29 23:24:20'),
(6, 4, 13, '2025-07-29 23:24:22'),
(7, 4, 2, '2025-07-29 23:24:40'),
(8, 4, 3, '2025-07-29 23:24:43'),
(16, 1, 1, '2025-07-30 01:18:22'),
(22, 1, 15, '2025-07-30 01:40:34'),
(23, 1, 3, '2025-07-30 01:40:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin','counsellor') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'Fakhrul', 'lurhkaf030224@gmail.com', '$2y$10$wv.Rno.nW9P4Mj.IEgItquF9Jrg2e8hpBM0TVmp7anXf2nT3fz0XW', '2025-04-28 04:56:59', 'user'),
(2, 'Admin', '2022899734@student.uitm.edu.my', '$2y$10$FA72VHwUkg.r8g2hcrE3q.wi/qcaooRS.BpAn3hnbtZKd.wz0ReN2', '2025-06-08 06:15:44', 'admin'),
(3, 'Counsellor', 'manz030224@gmail.com', '$2y$10$J1RpphG0/JVCJwMDPzfN2e1MEPXfLEKYCnacc.Zm1qJSG1kOcYQP6', '2025-06-21 18:21:44', 'counsellor'),
(4, 'Iman', 'fakhruliman.amini@gmail.com', '$2y$10$hWwOUVDVIvbwIyUKR8vYvuZ0mXKeMdwoPgbhFbUQf1CBLmnhSY6n2', '2025-07-07 14:57:31', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_files`
--
ALTER TABLE `chat_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `gad7_history`
--
ALTER TABLE `gad7_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phq9_history`
--
ALTER TABLE `phq9_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resourceID`);

--
-- Indexes for table `saved_resources`
--
ALTER TABLE `saved_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_files`
--
ALTER TABLE `chat_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gad7_history`
--
ALTER TABLE `gad7_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `phq9_history`
--
ALTER TABLE `phq9_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resourceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `saved_resources`
--
ALTER TABLE `saved_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_files`
--
ALTER TABLE `chat_files`
  ADD CONSTRAINT `chat_files_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `gad7_history`
--
ALTER TABLE `gad7_history`
  ADD CONSTRAINT `gad7_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `phq9_history`
--
ALTER TABLE `phq9_history`
  ADD CONSTRAINT `phq9_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `saved_resources`
--
ALTER TABLE `saved_resources`
  ADD CONSTRAINT `saved_resources_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
