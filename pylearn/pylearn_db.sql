-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2026 at 09:55 AM
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
-- Database: `pylearn_db`
--
CREATE DATABASE IF NOT EXISTS `pylearn_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pylearn_db`;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_name` varchar(50) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `user_id`, `badge_name`, `earned_at`) VALUES
(1, 1, 'First Step', '2026-05-05 07:12:16'),
(2, 1, 'Quiz Master', '2026-05-05 07:12:34'),
(3, 1, 'Quiz Whiz', '2026-05-05 07:12:34');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `order_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `title`, `slug`, `content`, `order_no`) VALUES
(1, 'Variables and Data Types', 'python-variables', '<h2>Variables</h2><p>Variables store data. In Python you do not need to declare a type.</p><pre>name = \"Ali\"\nage = 25</pre>', 1),
(2, 'Conditionals (if/else)', 'python-conditionals', '<h2>Conditionals</h2><p>Make decisions in code.</p><pre>if age > 18:\n    print(\"Adult\")\nelse:\n    print(\"Minor\")</pre>', 2),
(3, 'Loops (for, while)', 'python-loops', '<h2>Loops</h2><p>Repeat actions.</p><pre>for i in range(5):\n    print(i)</pre>', 3),
(4, 'Functions', 'python-functions', '<h2>Functions</h2><p>Reusable blocks of code.</p><pre>def greet(name):\n    return \"Hello \" + name</pre>', 4),
(5, 'Lists', 'python-lists', '<h2>Lists</h2><p>Ordered collections.</p><pre>fruits = [\"apple\", \"banana\"]\nfruits.append(\"cherry\")</pre>', 5),
(6, 'Dictionaries', 'python-dictionaries', '<h2>Dictionaries</h2><p>Key-value pairs.</p><pre>student = {\"name\": \"Hammad\", \"grade\": \"A\"}\nprint(student[\"name\"])</pre>', 6),
(7, 'File Handling', 'python-file-handling', '<h2>File Handling</h2><p>Read/write files.</p><pre>with open(\"test.txt\", \"w\") as f:\n    f.write(\"Hello\")</pre>', 7),
(8, 'Modules', 'python-modules', '<h2>Modules</h2><p>Import external code.</p><pre>import math\nprint(math.sqrt(16))</pre>', 8),
(9, 'OOP Basics', 'python-oop-basics', '<h2>OOP</h2><p>Classes and objects.</p><pre>class Car:\n    def __init__(self, brand):\n        self.brand = brand</pre>', 9),
(10, 'Exception Handling', 'python-exceptions', '<h2>Exceptions</h2><p>Handle errors gracefully.</p><pre>try:\n    x = 1/0\nexcept ZeroDivisionError:\n    print(\"Cannot divide by zero\")</pre>', 10);

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed` tinyint(4) DEFAULT 0,
  `score_percent` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id`, `user_id`, `lesson_id`, `completed`, `score_percent`) VALUES
(1, 1, 1, 1, 100.00),
(4, 1, 3, 1, 100.00),
(10, 1, 2, 1, 100.00),
(13, 1, 4, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `lesson_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct`) VALUES
(1, 1, 'What is the output of print(type(5))?', '<class \'int\'>', '<class \'float\'>', '<class \'str\'>', 'int', 'a'),
(2, 1, 'What is the output of print(2 + 3 * 4)?', '14', '20', '24', 'Error', 'a'),
(3, 2, 'What keyword starts an if statement in Python?', 'if', 'when', 'check', 'then', 'a'),
(4, 2, 'Which of these is the correct syntax for an else statement?', 'else (condition):', 'else:', 'else condition:', 'else { }', 'b'),
(5, 3, 'How many times will \"for i in range(3): print(i)\" run?', '2', '3', '4', '1', 'b'),
(6, 3, 'What does while True: create?', 'Finite loop', 'Infinite loop', 'No loop', 'Error', 'b'),
(7, 4, 'What keyword defines a function in Python?', 'func', 'def', 'function', 'define', 'b'),
(8, 4, 'What does a function return if there is no return statement?', '0', 'None', 'False', 'Error', 'b'),
(9, 5, 'How do you add \"mango\" to list fruits = [\"apple\",\"banana\"]?', 'fruits.add(\"mango\")', 'fruits.append(\"mango\")', 'fruits.insert(\"mango\")', 'fruits.push(\"mango\")', 'b'),
(10, 5, 'How do you access the first element of a list?', 'list[1]', 'list[0]', 'list.first()', 'list.get(1)', 'b'),
(11, 6, 'How do you access value of key \"name\" in dict student?', 'student.name', 'student[\"name\"]', 'student->name', 'student(name)', 'b'),
(12, 6, 'How do you add a new key-value pair to a dictionary?', 'd.add()', 'd[\"key\"] = value', 'd.push()', 'd.insert()', 'b'),
(13, 7, 'Which mode opens a file for writing?', 'r', 'w', 'a', 'x', 'b'),
(14, 7, 'Which method reads the entire file content?', 'read()', 'readline()', 'readlines()', 'readfile()', 'a'),
(15, 8, 'How do you import the math module?', 'include math', 'import math', 'using math', 'require math', 'b'),
(16, 8, 'Which statement is used to import only a specific function from a module?', 'import function', 'from module import function', 'import function from module', 'using function', 'b'),
(17, 9, 'What creates a class in Python?', 'new class', 'class', 'def class', 'object', 'b'),
(18, 9, 'What is the first parameter of a method inside a class?', 'this', 'self', 'cls', 'obj', 'b'),
(19, 10, 'Which block catches errors in Python?', 'catch', 'except', 'error', 'handle', 'b'),
(20, 10, 'Which exception is raised when dividing by zero?', 'ValueError', 'TypeError', 'ZeroDivisionError', 'OverflowError', 'c');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `selected` char(1) NOT NULL,
  `is_correct` tinyint(4) NOT NULL,
  `attempt_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `user_id`, `quiz_id`, `selected`, `is_correct`, `attempt_date`) VALUES
(2, 1, 2, 'a', 1, '2026-05-05 07:12:34'),
(7, 1, 6, 'b', 1, '2026-05-05 07:13:32'),
(9, 1, 4, 'b', 1, '2026-05-05 07:32:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `total_xp` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `total_xp`, `created_at`) VALUES
(1, 'KHADIJA KHALID', 'khadija@gmail.com', '$2y$10$eJmJVyoDPhHCim8ni3tmVOdU21ogQ27Yir4GuDnVjh6PtECgGojpi', 0, '2026-05-05 07:11:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_progress` (`user_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quiz_id` (`quiz_id`);

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
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `badges`
--
ALTER TABLE `badges`
  ADD CONSTRAINT `badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
