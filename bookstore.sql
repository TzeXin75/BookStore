-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 05:23 PM
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
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `cover_image` varchar(255) DEFAULT NULL,
  `images` varchar(500) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`id`, `title`, `description`, `author`, `publisher`, `category`, `subcategory`, `language`, `price`, `stock`, `cover_image`, `images`, `video`, `created_at`) VALUES
(1, 'The Alchemist', 'A worldwide bestseller, this mystical story follows Santiago, an Andalusian shepherd boy who yearns to travel in search of a worldly treasure. His quest leads him to riches far different—and far more satisfying—than he ever imagined.', 'Paulo Coelho', 'HarperOne', 'Fiction', 'Novel', 'English', 35.00, 450, NULL, 'The alchemist.jpg', NULL, '2025-12-25 09:38:53'),
(2, '1984', 'Set in a terrifyingly futuristic world where Big Brother is always watching, this classic dystopian novel explores the themes of government surveillance, totalitarianism, and the betrayal of the human spirit through the eyes of Winston Smith.', 'George Orwell', 'Secker & Warburg', 'Fiction', 'Novel', 'English', 28.00, 320, NULL, '1984.jpeg', NULL, '2025-12-25 09:38:53'),
(3, 'Brave New World', 'A chilling prophecy of a high-tech future where humans are genetically bred and pharmaceutically anesthetized to serve a passive social order. This masterpiece remains one of the most impactful social satires ever written.', 'Aldous Huxley', 'Chatto & Windus', 'Fiction', 'Novel', 'English', 32.00, 280, NULL, 'Brave New World.jpg', NULL, '2025-12-25 09:38:53'),
(4, 'The Kite Runner', 'An unforgettable story of a young boy from Kabul who betrays his closest friend, and the haunting guilt that follows him into adulthood. Set against the backdrop of a changing Afghanistan, it is a powerful tale of redemption.', 'Khaled Hosseini', 'Riverhead Books', 'Fiction', 'Novel', 'English', 38.00, 207, NULL, 'The Kite Runner.webp', NULL, '2025-12-25 09:38:53'),
(5, 'The Book Thief', 'Narrated by Death, this is the story of Liesel Meminger, a young girl living in Nazi Germany who finds solace by stealing books. With the help of her accordion-playing foster father, she learns to read and shares her stolen books with neighbors.', 'Markus Zusak', 'Picador', 'Fiction', 'Novel', 'English', 34.00, 315, NULL, 'The Book Theif.jpg', NULL, '2025-12-25 09:38:53'),
(6, 'Batman: Year One', 'Bruce Wayne returns to Gotham City after years of training, while Lieutenant James Gordon faces a corrupt police force. Witness the gritty origins of the Dark Knight and the alliance that would change Gotham forever.', 'Frank Miller', 'DC Comics', 'Fiction', 'Comic', 'English', 45.00, 179, NULL, 'Batman year one.webp', NULL, '2025-12-25 09:38:53'),
(7, 'Spider-Man: Blue', 'A touching retrospective on Peter Parker\'s early life and his first love, Gwen Stacy. This volume captures the melancholy and romance of the Silver Age of comics with beautiful artwork and a heart-wrenching narrative.', 'Jeph Loeb', 'Marvel Comics', 'Fiction', 'Comic', 'English', 42.00, 149, NULL, 'Spider-Man Blue.jpg', NULL, '2025-12-25 09:38:53'),
(8, 'Watchmen', 'The boundary-pushing graphic novel that redefined the superhero genre. In an alternate 1985, a group of retired heroes investigates the murder of one of their own, uncovering a conspiracy that threatens the world.', 'Alan Moore', 'DC Comics', 'Fiction', 'Comic', 'English', 55.00, 200, NULL, 'Watchmen.jpg', NULL, '2025-12-25 09:38:53'),
(9, 'V for Vendetta', 'In a post-apocalyptic Britain ruled by a fascist regime, a mysterious anarchist known only as \"V\" begins a violent campaign to bring down the government and inspire the people to reclaim their freedom.', 'Alan Moore', 'DC Comics', 'Fiction', 'Comic', 'English', 48.00, 139, NULL, 'V for vendetta.jpg', NULL, '2025-12-25 09:38:53'),
(10, 'Saga Vol 1', 'An epic space opera fantasy following two soldiers from opposite sides of a never-ending galactic war. They fall in love and risk everything to protect their newborn daughter from those who would see them destroyed.', 'Brian K. Vaughan', 'Image Comics', 'Fiction', 'Comic', 'English', 39.00, 219, NULL, 'Saga Vol 1.jpg', NULL, '2025-12-25 09:38:53'),
(11, 'Modern PHP', 'PHP is undergoing a renaissance. Learn about modern features like namespaces, traits, and generators. This book covers best practices for application architecture, security, and performance in the PHP 8 ecosystem.\r\n', 'Josh Lockhart', 'O\'Reilly', 'Education', 'Textbook', 'English', 85.00, 250, NULL, 'Modern PHP.png', NULL, '2025-12-25 09:38:53'),
(12, 'Clean Code', 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees. Learn how to write code that is readable, maintainable, and robust using agile software craftsmanship principles.', 'Robert C. Martin', 'Prentice Hall', 'Education', 'Textbook', 'English', 120.00, 189, NULL, 'Clean code.jpg', NULL, '2025-12-25 09:38:53'),
(13, 'Biology: A Global Approach', 'The world\'s most successful biology textbook. It provides a comprehensive and accurate overview of the biological sciences, from the molecular level to entire ecosystems, with a focus on global scientific challenges.', 'Neil Campbell', 'Pearson', 'Education', 'Textbook', 'English', 150.00, 120, NULL, 'Biology- A Global Approach.jpg', NULL, '2025-12-25 09:38:53'),
(14, 'Principles of Economics', 'A foundational guide to economic theory. This text explores micro and macroeconomics through real-world examples, helping students understand the trade-offs, market forces, and policy decisions that shape our world.', 'N. Gregory Mankiw', 'Cengage', 'Education', 'Textbook', 'English', 110.00, 160, NULL, 'Princeples of Economics.jpg', NULL, '2025-12-25 09:38:53'),
(15, 'Malay Language Mastery', 'A comprehensive guide to the Malay language, covering formal grammar, local idioms, and advanced vocabulary. Perfect for university students and professionals looking to achieve fluency in Bahasa Melayu.', 'Zainal Abidin', 'DBP', 'Education', 'Textbook', 'Malay', 45.00, 300, NULL, 'Malay Language Mastery.webp', NULL, '2025-12-25 09:38:53'),
(16, 'Modern Chinese Grammar', 'This innovative guide provides a systematic and accessible overview of Mandarin Chinese grammar. It focuses on the language as it is actually spoken today, with numerous examples and clear, non-technical explanations.', 'Claudia Ross', 'Routledge', 'Education', 'Textbook', 'Chinese', 75.00, 210, NULL, 'Modern Chinese Grammar.jpg', NULL, '2025-12-25 09:38:53'),
(17, 'Calculus Early Transcendentals', 'The gold standard in calculus textbooks. It provides a rigorous introduction to limits, derivatives, and integrals, with thousands of exercises that challenge students to apply mathematical concepts to real-world engineering.', 'James Stewart', 'Cengage', 'Education', 'Textbook', 'English', 140.00, 129, NULL, 'Calculus Early Transcendentals.jpg', NULL, '2025-12-25 09:38:53'),
(18, 'Atomic Habits', 'Small changes, remarkable results. James Clear reveals how to build good habits and break bad ones by focusing on tiny, consistent behaviors that lead to life-changing personal and professional transformations.', 'James Clear', 'Avery', 'Non-Fiction', 'Self-help', 'English', 42.00, 500, NULL, 'Atomic Habits.jpg', NULL, '2025-12-25 09:38:53'),
(19, 'Deep Work', 'In an age of constant distraction, the ability to focus without interruption is a superpower. Learn how to master cognitive demanding tasks and achieve \"deep work\" to produce better results in less time.', 'Cal Newport', 'Grand Central', 'Non-Fiction', 'Self-help', 'English', 38.00, 350, NULL, 'Deep Work.jpg', NULL, '2025-12-25 09:38:53'),
(20, 'The 48 Laws of Power', 'Amoral, cunning, ruthless, and instructive, this multi-million-copy New York Times bestseller is the definitive manual for anyone interested in gaining, observing, or defending against ultimate control.', 'Robert Greene', 'Viking', 'Non-Fiction', 'Self-help', 'English', 45.00, 240, NULL, 'The 48 Laws of Power.jpg', NULL, '2025-12-25 09:38:53'),
(21, 'How to Win Friends', 'The most famous confidence-boosting book ever published. Learn the six ways to make people like you, the twelve ways to win people to your way of thinking, and the nine ways to change people without giving offense.', 'Dale Carnegie', 'Simon & Schuster', 'Non-Fiction', 'Self-help', 'English', 35.00, 480, NULL, 'How to Win Friends.jpg', NULL, '2025-12-25 09:38:53'),
(22, 'Man\'s Search for Meaning', 'Psychiatrist Viktor Frankl\'s memoir has riveted generations of readers with its descriptions of life in Nazi death camps and its lessons for spiritual survival. Learn how to find meaning even in the face of suffering.', 'Viktor Frankl', 'Beacon Press', 'Non-Fiction', 'Self-help', 'English', 29.00, 320, NULL, 'Man\'s Search for Meaning.jpg', NULL, '2025-12-25 09:38:53'),
(23, 'Sapiens', 'How did our species succeed in the battle for dominance? Yuval Noah Harari takes us on a journey through the history of humankind, exploring how biology and history have defined us and enhanced our understanding of what it means to be \"human.\"', 'Yuval Noah Harari', 'Harper', 'Non-Fiction', 'Self-help', 'English', 48.00, 410, NULL, 'Sapiens.jpg', NULL, '2025-12-25 09:38:53'),
(24, 'The Power of Habit', 'Why do we do what we do in life and business? Pulitzer Prize–winning business reporter Charles Duhigg takes us to the thrilling edge of scientific discoveries that explain why habits exist and how they can be changed.', 'Charles Duhigg', 'Random House', 'Non-Fiction', 'Self-help', 'English', 36.00, 289, NULL, 'The Power of Habit.jpg', NULL, '2025-12-25 09:38:53'),
(25, 'Mindset', 'World-renowned Stanford University psychologist Carol S. Dweck, Ph.D., discovered a simple but groundbreaking idea: the power of mindset. Learn how a fixed vs. growth mindset can influence every aspect of your life.', 'Carol Dweck', 'Ballantine Books', 'Non-Fiction', 'Self-help', 'English', 34.00, 360, NULL, 'Mindset.jpg', NULL, '2025-12-25 09:38:53'),
(26, 'Steve Jobs', 'The exclusive biography of the creative entrepreneur whose passion for perfection and ferocious drive revolutionized six industries: personal computers, animated movies, music, phones, tablet computing, and digital publishing.', 'Walter Isaacson', 'Simon & Schuster', 'Non-Fiction', 'Biography', 'English', 55.00, 180, NULL, 'Steve Jobs.jpg', NULL, '2025-12-25 09:38:53'),
(27, 'Elon Musk', 'A veteran technology journalist provides an inside look at the life and times of the most audacious entrepreneur of our age, exploring the rise of Tesla, SpaceX, and SolarCity, and the quest for a fantastic future.', 'Ashlee Vance', 'Ecco', 'Non-Fiction', 'Biography', 'English', 52.00, 210, NULL, 'Elon Musk.jpg', NULL, '2025-12-25 09:38:53'),
(28, 'Becoming', 'An intimate, powerful, and inspiring memoir by the former First Lady of the United States. Michelle Obama invites readers into her world, chronicling the experiences that have shaped her—from her childhood to her time in the White House.', 'Michelle Obama', 'Crown', 'Non-Fiction', 'Biography', 'English', 58.00, 400, NULL, 'Becoming.jpg', NULL, '2025-12-25 09:38:53'),
(29, 'The Diary of a Young Girl', 'Discovered in the attic where she spent the last years of her life, Anne Frank\'s remarkable diary has since become a world classic—a powerful reminder of the horrors of war and an eloquent testament to the human spirit.', 'Anne Frank', 'Contact Publishing', 'Non-Fiction', 'Biography', 'English', 25.00, 450, NULL, 'The Diary of a Young Girl.jpg', NULL, '2025-12-25 09:38:53'),
(30, 'Long Walk to Freedom', 'The autobiography of Nelson Mandela, one of the great moral and political leaders of our time. It chronicles his early life, his move to Johannesburg, his incarceration, and his eventual triumph as President of South Africa.', 'Nelson Mandela', 'Little, Brown', 'Non-Fiction', 'Biography', 'English', 45.00, 170, NULL, 'Long Walk to Freedom.jpg', NULL, '2025-12-25 09:38:53'),
(31, 'Shoe Dog', 'In this candid and riveting memoir, Nike founder Phil Knight shares the inside story of the company\'s early days as an intrepid start-up and its evolution into one of the world\'s most iconic, game-changing, and profitable brands.', 'Phil Knight', 'Scribner', 'Non-Fiction', 'Biography', 'English', 42.00, 240, NULL, 'Shoe Dog.jpg', NULL, '2025-12-25 09:38:53'),
(32, 'Educated', 'Born to survivalists in the mountains of Idaho, Tara Westover was seventeen the first time she set foot in a classroom. This is her story of the struggle for self-invention and the power of education to change a life.', 'Tara Westover', 'Random House', 'Non-Fiction', 'Biography', 'English', 39.00, 330, NULL, 'Educated.jpg', NULL, '2025-12-25 09:38:53'),
(33, 'Einstein: His Life and Universe', 'Based on newly released personal letters of Albert Einstein, Isaacson explores how an imaginative, impertinent patent clerk unlocked the mysteries of the cosmos and the universe within.', 'Walter Isaacson', 'Simon & Schuster', 'Non-Fiction', 'Biography', 'English', 49.00, 150, NULL, 'Einstein- His Life and Universe.jpg', NULL, '2025-12-25 09:38:53'),
(34, 'Ocean Wonders', 'Dive into an underwater world! This coloring book features intricate illustrations of majestic whales, playful dolphins, and colorful coral reefs, designed to spark creativity in children of all ages.', 'Sarah Miller', 'Kids Press', 'Children', 'Color Book', 'English', 15.00, 500, NULL, 'Ocean Wonders.jpg', NULL, '2025-12-25 09:38:53'),
(35, 'Jungle Friends', 'Embark on a safari adventure with this delightful coloring book. Children will love bringing to life the lions, monkeys, and elephants that inhabit the lush jungles of our planet.', 'John Dean', 'Creative Kids', 'Children', 'Color Book', 'English', 15.00, 480, NULL, 'Jungle Friends.jpg', NULL, '2025-12-25 09:38:53'),
(37, 'Space Explorer', 'Blast off into the cosmos! Color your way through galaxies, planets, and stars. This educational coloring book introduces children to the wonders of our solar system and the mysteries beyond.', 'Anna White', 'Sky Books', 'Children', 'Color Book', 'English', 16.00, 450, NULL, 'Space Explorer.jpg', NULL, '2025-12-25 09:38:53'),
(38, 'Princess Tales', 'A magical coloring journey through enchanted forests and majestic castles. This book features beautiful princesses, brave knights, and fairy-tale scenes that will capture the imagination of every little dreamer.', 'Lucy Green', 'Fairy Books', 'Children', 'Color Book', 'English', 14.00, 390, NULL, 'Princess Tales.jpg', NULL, '2025-12-25 09:38:53'),
(39, 'Farm Animals', 'Introduce your toddler to life on the farm! With simple, bold outlines, this coloring book is perfect for little hands to practice their motor skills while learning about cows, sheep, and friendly farm dogs.', 'Tom Brown', 'Barn Press', 'Children', 'Color Book', 'English', 12.00, 500, NULL, 'Farm Animals.jpg', NULL, '2025-12-25 09:38:53'),
(43, 'NEW PRODUCT A', NULL, 'AUTHOR A', NULL, 'Non-Fiction', 'Biography', NULL, 20.00, 200, 'download.svg', NULL, NULL, '2025-12-26 04:43:57'),
(44, 'NEW PRODUCT B', NULL, 'AUTHOR B', NULL, 'Fiction', 'Novel', NULL, 50.00, 10, 'download.svg', NULL, NULL, '2025-12-26 04:43:57');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(20) DEFAULT 'Pending',
  `shipping_address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `order_status`, `shipping_address`) VALUES
(1, 1, '2025-12-26 02:10:31', 150.00, 'Completed', 'Jalan Perdana, Kuala Lumpur, 50566, Malaysia'),
(2, 1, '2025-12-26 02:12:25', 0.00, 'Shipped', 'Ibu Pejabat Polis Daerah Brickfields, 1, Jalan Selangor, Federal Hill, Bangsar, Kuala Lumpur, 50470, Malaysia'),
(3, 1, '2025-12-26 02:13:34', 0.00, 'Cancelled', 'Jalan Persekutuan, Federal Hill, Bangsar, Kuala Lumpur, 50566, Malaysia'),
(4, 4, '2025-12-26 02:15:41', 29.00, 'Shipped', '73, Jalan Bukit Bintang, Bukit Bintang, Kuala Lumpur, 55100, Malaysia'),
(5, 4, '2025-12-26 02:17:09', 61.12, 'Completed', 'Semarak, Kuala Lumpur, 53200, Malaysia'),
(8, 1, '2025-12-26 11:55:12', 575.00, 'Pending', 'Kuala Lumpur, Jalan Tun Sambanthan, Kampung Attap, Kuala Lumpur, 50000, Malaysia'),
(9, 3, '2025-12-26 12:30:18', 128.00, 'Cancelled', 'Tower Wing, Jalan Sultan Hishamuddin, Kuala Lumpur, 50566, Malaysia');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`detail_id`, `order_id`, `id`, `quantity`, `unit_price`) VALUES
(1, 1, 24, 1, 36.00),
(2, 1, 4, 3, 38.00),
(3, 2, 7, 1, 42.00),
(4, 3, 33, 2, 49.00),
(5, 4, 22, 1, 29.00),
(6, 5, 1, 2, 35.00),
(7, 6, 6, 1, 45.00),
(8, 6, 10, 1, 39.00),
(9, 6, 17, 1, 140.00),
(10, 7, 9, 1, 48.00),
(11, 7, 12, 1, 120.00),
(12, 8, 41, 21, 35.00),
(13, 9, 42, 5, 20.00),
(14, 9, 4, 1, 38.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_ref` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Success'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `transaction_ref`, `payment_date`, `amount`, `status`) VALUES
(1, 1, 'Credit Card', '1111222233334444', '2025-12-26 02:10:31', 150.00, 'Success'),
(2, 2, 'E-Wallet', '', '2025-12-26 02:12:25', 0.00, 'Success'),
(3, 3, 'Online Banking', '0000111100001111', '2025-12-26 02:13:34', 0.00, 'Success'),
(4, 4, 'E-Wallet', '', '2025-12-26 02:15:41', 29.00, 'Success'),
(5, 5, 'Online Banking', '1234432111112222', '2025-12-26 02:17:09', 61.12, 'Success'),
(6, 6, 'Credit Card', '1231212112341111', '2025-12-26 02:18:38', 74.00, 'Success'),
(7, 7, 'E-Wallet', '', '2025-12-26 02:19:15', 168.00, 'Success'),
(8, 8, 'E-Wallet', '', '2025-12-26 11:55:12', 575.00, 'Success'),
(9, 9, 'E-Wallet', '', '2025-12-26 12:30:18', 128.00, 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `id` varchar(100) NOT NULL,
  `expire` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_phone` int(11) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `user_photo` varchar(255) DEFAULT NULL,
  `user_role` enum('admin','member') DEFAULT 'member',
  `user_address` varchar(255) DEFAULT NULL,
  `user_registrationDate` datetime NOT NULL DEFAULT current_timestamp(),
  `user_status` tinyint(1) NOT NULL DEFAULT 1,
  `reward_points` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `user_phone`, `user_password`, `user_photo`, `user_role`, `user_address`, `user_registrationDate`, `user_status`, `reward_points`) VALUES
(1, 'RY', 'limrouyu9@gmail.com', 124458892, '8cb2237d0679ca88db6464eac60da96345513964', '', 'member', '12, Jalan SS2/1, Petaling Jaya, 47300 Selangor', '2025-12-25 22:14:22', 1, 575),
(3, 'lim', 'lim@gmail.com', 104451123, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'admin', '7, Jalan Pendidikan 1, 75450 Bukit Beruang, Melaka', '2025-12-25 22:20:01', 1, 128),
(4, 'Joanna', 'lim974818@gmail.com', 115548871, '8cb2237d0679ca88db6464eac60da96345513964', 'default.jpg', 'member', 'Jalan Usahawan 2 Taman Danau Kota, Wangsa Maju, 53300 Kuala Lumpur', '2025-12-25 23:19:44', 1, 90);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` varchar(10) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucher_id`, `code`, `discount_amount`, `expiry_date`, `status`) VALUES
(1, 'WELCOME10', 10.00, '2025-12-31', 'Active'),
(2, 'CNY2026', 8.88, '2026-02-15', 'Active'),
(3, 'WELCOME11', 10.00, '2025-12-31', 'Active'),
(4, 'EXPIRED50', 50.00, '2020-01-01', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `book_id` (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
