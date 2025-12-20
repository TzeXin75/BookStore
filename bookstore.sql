-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 20, 2025 at 01:58 PM
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
  `images` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`id`, `title`, `description`, `author`, `publisher`, `category`, `subcategory`, `language`, `price`, `stock`, `images`, `created_at`) VALUES
(1, 'The Great Gatsby', 'A classic novel about the American Dream in the Jazz Age.\nFeatures Jay Gatsby and his obsession with Daisy Buchanan.\nExplores themes of wealth, class, and the American Dream.', 'F. Scott Fitzgerald', 'Scribner', 'Fiction', 'Novel', 'English', 15.50, 94, 'book1.jpg', '2025-12-16 15:36:48'),
(2, 'PHP Programming for Beginners', 'Learn PHP from scratch with practical examples.\nCovers PHP 8 features and best practices.\nIncludes MySQL database integration.', 'John Smith', 'Tech Press', 'Education', 'Textbook', 'English', 45.00, 16, 'book2.jpg', '2025-12-16 15:36:48'),
(3, 'Web Development Mastery', 'Complete guide to modern web development.\nCovers HTML5, CSS3, JavaScript, and frameworks.\nIncludes responsive design and SEO optimization.', 'Jane Doe', 'Web Publishers', 'Education', 'Textbook', 'English', 120.00, 4, 'book3.jpg', '2025-12-16 15:36:48'),
(4, 'Harry Potter and the Sorcerer Stone', 'First book in the Harry Potter series.\nFollows Harry\'s first year at Hogwarts.\nIntroduces magical world and characters.', 'J.K. Rowling', 'Bloomsbury', 'Fiction', 'Novel', 'English', 35.90, 50, 'book4.jpg', '2025-12-16 15:36:48'),
(5, 'Database Design Guide', 'Learn database design principles and normalization.\nCovers SQL queries, indexing, and optimization.\nIncludes real-world case studies.', 'Michael Chen', 'Database Press', 'Education', 'Textbook', 'English', 55.00, 0, 'book5.jpg', '2025-12-16 15:36:48'),
(6, 'Spider-Man: Homecoming', 'Amazing Spider-Man comic collection.\nFeatures classic storylines and villains.\nFull-color illustrations throughout.', 'Stan Lee', 'Marvel Comics', 'Fiction', 'Comic', 'English', 12.99, 25, 'book6.jpg', '2025-12-16 15:36:48'),
(7, 'The Art of War', 'Ancient Chinese military treatise.\nTimeless strategies for conflict resolution.\nApplies to business and personal life.', 'Sun Tzu', 'Penguin Classics', 'Non-Fiction', 'Self-help', 'Chinese', 9.99, 30, 'book7.jpg', '2025-12-16 15:36:48'),
(8, 'Malay Grammar Guide', 'Comprehensive Malay language guide.\nCovers grammar, vocabulary, and usage.\nSuitable for beginners to advanced learners.', 'Ahmad Hassan', 'Dewan Bahasa', 'Education', 'Textbook', 'Malay', 28.50, 15, 'book8.jpg', '2025-12-16 15:36:48'),
(9, 'My First Coloring Book', 'Fun coloring book for children ages 3-6.\nFeatures animals, vehicles, and shapes.\nDevelops fine motor skills and creativity.', 'Sarah Johnson', 'Kids Press', 'Children', 'Color Book', 'English', 8.99, 100, 'book9.jpg', '2025-12-16 15:36:48'),
(10, 'Steve Jobs Biography', 'The life story of Apple co-founder.\nDetailed account of his career and innovations.\nInsights into his leadership style.', 'Walter Isaacson', 'Simon & Schuster', 'Non-Fiction', 'Biography', 'English', 18.99, 40, 'book10.jpg', '2025-12-16 15:36:48'),
(11, 'To Kill a Mockingbird', 'Classic novel about racial injustice.\nSet in the American South during the 1930s.\nTold from a child\'s perspective.', 'Harper Lee', 'J.B. Lippincott', 'Fiction', 'Novel', 'English', 14.99, 75, 'book11.jpg', '2025-12-16 15:36:48'),
(12, 'Batman: The Dark Knight Returns', 'Iconic Batman graphic novel.\nFeatures aging Bruce Wayne returning as Batman.\nDark and gritty storyline.', 'Frank Miller', 'DC Comics', 'Fiction', 'Comic', 'English', 19.99, 20, 'book12.jpg', '2025-12-16 15:36:48'),
(13, 'Atomic Habits', 'Guide to building good habits and breaking bad ones.\nPractical strategies for behavior change.\nBased on scientific research.', 'James Clear', 'Avery', 'Non-Fiction', 'Self-help', 'English', 16.99, 60, 'book13.jpg', '2025-12-16 15:36:48'),
(14, 'Mathematics for Engineers', 'Advanced mathematics textbook for engineering students.\nCovers calculus, differential equations, and linear algebra.\nIncludes practice problems with solutions.', 'Dr. Robert Chang', 'Engineering Press', 'Education', 'Textbook', 'English', 89.99, 12, 'book14.jpg', '2025-12-16 15:36:48'),
(15, 'Mandarin Chinese for Beginners', 'Complete Mandarin Chinese language course.\nIncludes pronunciation guide and basic characters.\nComes with audio CD for practice.', 'Li Wei', 'Language World', 'Education', 'Textbook', 'Chinese', 34.99, 25, 'book15.jpg', '2025-12-16 15:36:48'),
(16, 'Animal Friends Coloring Book', 'Coloring book with cute animal illustrations.\nIncludes jungle, farm, and ocean animals.\nThick paper prevents bleed-through.', 'Emily Brown', 'Creative Kids', 'Children', 'Color Book', 'English', 7.99, 150, 'book16.jpg', '2025-12-16 15:36:48'),
(17, 'Elon Musk: Tesla, SpaceX, and the Quest for a Fantastic Future', 'Biography of Elon Musk and his companies.\nCovers his vision for sustainable energy and space exploration.\nInsider look at his work ethic and challenges.', 'Ashlee Vance', 'Ecco', 'Non-Fiction', 'Biography', 'English', 17.99, 35, 'book17.jpg', '2025-12-16 15:36:48'),
(18, 'X-Men: Days of Future Past', 'Classic X-Men comic storyline.\nFeatures time travel and alternate futures.\nIncludes Wolverine, Storm, and other mutants.', 'Chris Claremont', 'Marvel Comics', 'Fiction', 'Comic', 'English', 15.99, 18, 'book18.jpg', '2025-12-16 15:36:48'),
(19, 'How to Win Friends and Influence People', 'Timeless classic on interpersonal skills.\nPrinciples for effective communication and leadership.\nReal-world examples and applications.', 'Dale Carnegie', 'Simon & Schuster', 'Non-Fiction', 'Self-help', 'English', 12.99, 85, 'book19.jpg', '2025-12-16 15:36:48'),
(20, 'Physics for Scientists and Engineers', 'Comprehensive physics textbook.\nCovers mechanics, thermodynamics, electromagnetism, and optics.\nIncludes laboratory experiments and problem sets.', 'Raymond Serway', 'Cengage Learning', 'Education', 'Textbook', 'English', 125.00, 8, 'book20.jpg', '2025-12-16 15:36:48'),
(21, 'Pride and Prejudice', 'Classic romance novel about Elizabeth Bennet and Mr. Darcy.\nExplores themes of love, reputation, and class in Georgian England.\nWitty social commentary and memorable characters.', 'Jane Austen', 'T. Egerton', 'Fiction', 'Novel', 'English', 12.99, 45, 'book21.jpg', '2025-12-16 15:36:48'),
(22, 'The Silent Patient', 'Psychological thriller about a woman who shoots her husband and stops speaking.\nTherapist becomes obsessed with uncovering the truth.\nTwist ending that surprises readers.', 'Alex Michaelides', 'Celadon Books', 'Fiction', 'Novel', 'English', 14.99, 30, 'book22.jpg', '2025-12-16 15:36:48'),
(23, 'Superman: Red Son', 'Alternate history where Superman lands in Soviet Ukraine instead of Kansas.\nExplores political ideologies and moral dilemmas.\nUnique take on the Superman mythos.', 'Mark Millar', 'DC Comics', 'Fiction', 'Comic', 'English', 17.99, 15, 'book23.jpg', '2025-12-16 15:36:48'),
(24, 'Watchmen', 'Groundbreaking graphic novel about retired superheroes.\nComplex narrative with philosophical themes.\nDeconstruction of the superhero genre.', 'Alan Moore', 'DC Comics', 'Fiction', 'Comic', 'English', 22.99, 12, 'book24.jpg', '2025-12-16 15:36:48'),
(25, 'Becoming', 'Memoir by former First Lady Michelle Obama.\nCovers her childhood, career, and time in the White House.\nPersonal insights and inspiring journey.', 'Michelle Obama', 'Crown Publishing', 'Non-Fiction', 'Biography', 'English', 21.99, 55, 'book25.jpg', '2025-12-16 15:36:48'),
(26, 'Born a Crime', 'Trevor Noah\'s memoir about growing up in apartheid South Africa.\nHumorous and poignant stories about race and identity.\nInsights into his journey to becoming a comedian.', 'Trevor Noah', 'Spiegel & Grau', 'Non-Fiction', 'Biography', 'English', 16.99, 40, 'book26.jpg', '2025-12-16 15:36:48'),
(27, 'The 7 Habits of Highly Effective People', 'Classic self-help book about personal and professional effectiveness.\nPrinciples for achieving goals and building relationships.\nTimeless advice for personal development.', 'Stephen R. Covey', 'Free Press', 'Non-Fiction', 'Self-help', 'English', 15.99, 70, 'book27.jpg', '2025-12-16 15:36:48'),
(28, 'Thinking, Fast and Slow', 'Explores two systems of thinking: fast, intuitive, and slow, deliberate.\nNobel Prize-winning insights into human psychology.\nChallenges assumptions about decision-making.', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 'Non-Fiction', 'Self-help', 'English', 18.99, 25, 'book28.jpg', '2025-12-16 15:36:48'),
(29, 'Chemistry for High School Students', 'Comprehensive chemistry textbook aligned with national curriculum.\nClear explanations with diagrams and examples.\nPractice questions and experiments included.', 'Dr. Susan Wong', 'Academic Press', 'Education', 'Textbook', 'English', 65.00, 20, 'book29.jpg', '2025-12-16 15:36:48'),
(30, 'Business Management Principles', 'Essential guide to modern business management.\nCovers leadership, strategy, operations, and finance.\nCase studies from successful companies.', 'Peter Drucker', 'Harvard Business Review', 'Education', 'Textbook', 'English', 85.00, 18, 'book30.jpg', '2025-12-16 15:36:48'),
(31, 'English-Malay Dictionary', 'Comprehensive bilingual dictionary with over 50,000 entries.\nIncludes idioms, phrases, and cultural notes.\nUseful for students and professionals.', 'Dewan Bahasa Team', 'Dewan Bahasa', 'Education', 'Textbook', 'Malay', 45.00, 35, 'book31.jpg', '2025-12-16 15:36:48'),
(32, 'Advanced Chinese Characters', 'Guide to mastering complex Chinese characters.\nStroke order, radicals, and vocabulary building.\nSuitable for intermediate to advanced learners.', 'Professor Zhang Wei', 'Beijing Language Press', 'Education', 'Textbook', 'Chinese', 38.99, 22, 'book32.jpg', '2025-12-16 15:36:48'),
(33, 'Dinosaur Adventures Coloring Book', 'Coloring book featuring various dinosaurs in prehistoric scenes.\nEducational facts about each dinosaur included.\nFun way to learn about paleontology.', 'Tommy Lee', 'Dino Press', 'Children', 'Color Book', 'English', 9.99, 120, 'book33.jpg', '2025-12-16 15:36:48'),
(34, 'Princess Castle Activity Book', 'Activity book with princess-themed puzzles, mazes, and coloring pages.\nIncludes stickers and cut-out crowns.\nEncourages creativity and problem-solving.', 'Princess Publishing', 'Fairy Tale Books', 'Children', 'Color Book', 'English', 8.50, 95, 'book34.jpg', '2025-12-16 15:36:48');

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
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `member_id` int(11) NOT NULL,
  `member_pay` int(11) NOT NULL,
  `member_subscribeDate` datetime NOT NULL DEFAULT current_timestamp()
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
(1, 1, '2025-12-12 14:40:50', 15.50, 'Shipped', 'Desa Aman Puri'),
(2, 1, '2025-12-12 14:54:57', 45.00, 'Cancelled', 'tarc'),
(3, 1, '2025-12-12 14:58:49', 15.50, 'Completed', 'asg'),
(4, 1, '2025-12-12 15:17:56', 6.62, 'Pending', 'address123'),
(5, 1, '2025-12-12 15:27:31', 6.62, 'Pending', '123'),
(6, 1, '2025-12-12 15:28:14', 45.00, 'Pending', '123'),
(7, 1, '2025-12-12 15:29:03', 210.00, 'Pending', '123'),
(8, 1, '2025-12-12 15:30:05', 15.50, 'Pending', '123'),
(9, 1, '2025-12-12 15:31:53', 0.00, 'Pending', '123'),
(10, 1, '2025-12-12 15:35:05', 31.00, 'Pending', '12345');

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
(1, 1, 1, 1, 15.50),
(2, 2, 2, 1, 45.00),
(3, 3, 1, 1, 15.50),
(4, 4, 1, 1, 15.50),
(5, 5, 1, 1, 15.50),
(6, 6, 2, 1, 45.00),
(7, 7, 2, 2, 45.00),
(8, 7, 3, 1, 120.00),
(9, 8, 1, 1, 15.50),
(10, 10, 1, 2, 15.50);

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
(1, 1, 'E-Wallet', '1234-5678-9101-1121', '2025-12-12 14:40:50', 15.50, 'Success'),
(2, 2, 'Credit Card', '1234567812345678', '2025-12-12 14:54:57', 45.00, 'Success'),
(3, 3, 'Credit Card', '1234567891234567', '2025-12-12 14:58:49', 15.50, 'Success'),
(4, 4, 'Credit Card', '1234123412341234', '2025-12-12 15:17:56', 6.62, 'Success'),
(5, 5, 'Credit Card', '1234123412341234', '2025-12-12 15:27:31', 6.62, 'Success'),
(6, 6, 'Credit Card', '1234123412341234', '2025-12-12 15:28:14', 45.00, 'Success'),
(7, 7, 'Credit Card', '1234123412341234', '2025-12-12 15:29:03', 210.00, 'Success'),
(8, 8, 'Credit Card', '1234123412341234', '2025-12-12 15:30:05', 15.50, 'Success'),
(9, 9, 'Credit Card', '1234123412341234', '2025-12-12 15:31:53', 0.00, 'Success'),
(10, 10, 'Credit Card', '1234123412341234', '2025-12-12 15:35:05', 31.00, 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_phone` varchar(20) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `user_photo` varchar(255) DEFAULT NULL,
  `user_role` enum('admin','member') DEFAULT 'member',
  `user_address` varchar(255) DEFAULT NULL,
  `user_registrationDate` datetime NOT NULL DEFAULT current_timestamp(),
  `user_status` tinyint(1) NOT NULL DEFAULT 1,
  `member_pay` decimal(10,0) NOT NULL,
  `member_subscribeDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `member_id`, `username`, `email`, `user_phone`, `user_password`, `user_photo`, `user_role`, `user_address`, `user_registrationDate`, `user_status`, `member_pay`, `member_subscribeDate`) VALUES
(1, 0, 'test_member', 'member@gmail.com', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(2, 0, 'test_admin', 'admin@gmail.com', NULL, '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', NULL, 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(3, 0, 'Alice Tan', 'alice.tan@gmail.com', '012-3456789', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'alice.jpg', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(4, 0, 'Brian Lee', 'brian.lee@gmail.com', '013-4567890', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'brian.png', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(5, 0, 'lim', 'lim@gmail.com', '014-5678901', '8cb2237d0679ca88db6464eac60da96345513964', '694337215cf33.jpg', 'admin', 'll', '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(6, 0, 'Daniel Lim', 'daniel.lim@gmail.com', '015-6789012', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'daniel.png', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(7, 0, 'Emily Ng', 'emily.ng@gmail.com', '016-7890123', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'emily.jpg', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(8, 0, 'Farah Ahmad', 'farah.ahmad@gmail.com', '017-8901234', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 'farah.png', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(9, 0, 'George Tan', 'george.tan@gmail.com', '018-9012345', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'george.jpg', 'admin', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(10, 0, 'Hannah Lee', 'hannah.lee@gmail.com', '019-0123456', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'hannah.png', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(11, 0, 'Ivan Chong', 'ivan.chong@gmail.com', '011-1234567', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'ivan.jpg', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(12, 0, 'Julia Teo', 'julia.teo@gmail.com', '010-2345678', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'julia.png', 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(13, 0, 'Lina', 'lina@gmail.com', '011-12345678', '7c4a8d09ca3762af61e59520943dc26494f8941b', '694138b55ceed.jpg', 'member', 'haha', '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(15, 0, 'hui', 'hui@gmail.com', '', '601f1889667efaebb33b8c12572835da3f027f78', NULL, 'member', NULL, '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(16, 0, 'test', 'test@gmail.com', '011-12345678', '7c4a8d09ca3762af61e59520943dc26494f8941b', '69418e33c7a90.jpg', 'member', 'wowwww', '0000-00-00 00:00:00', 1, 0, '2025-12-20 18:17:52'),
(19, 0, 'Jason', 'jason@gmail.com', NULL, '601f1889667efaebb33b8c12572835da3f027f78', '', 'member', NULL, '2025-12-18 04:16:29', 1, 0, '2025-12-20 18:17:52'),
(20, 0, 'haha', 'haha@gmail.com', '014-5678901', '601f1889667efaebb33b8c12572835da3f027f78', '', 'member', 'wowwwww', '2025-12-18 04:28:27', 1, 0, '2025-12-20 18:17:52');

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
(7, 'WELCOME11', 10.00, '2025-12-31', 'Active'),
(8, 'EXPIRED50', 50.00, '2020-01-01', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `book_id` (`id`),
  ADD KEY `cart_ibfk_1` (`user_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`member_id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
