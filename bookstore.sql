-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 12:15 AM
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
(9, 'My First Coloring Book', 'Fun coloring book for children ages 3-6.\nFeatures animals, vehicles, and shapes.\nDevelops fine motor skills and creativity.', 'Sarah Johnson', 'Kids Press', 'Children', 'Color Book', 'English', 8.99, 99, 'book9.jpg', '2025-12-16 15:36:48'),
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
(21, 'Pride and Prejudice', 'Classic romance novel about Elizabeth Bennet and Mr. Darcy.\nExplores themes of love, reputation, and class in Georgian England.\nWitty social commentary and memorable characters.', 'Jane Austen', 'T. Egerton', 'Fiction', 'Novel', 'English', 12.99, 44, 'book21.jpg', '2025-12-16 15:36:48'),
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
(10, 1, '2025-12-12 15:35:05', 31.00, 'Pending', '12345'),
(12, 1, '2025-12-20 23:33:31', 0.00, 'Pending', 'Desa Aman Puri\r\n'),
(13, 1, '2025-12-20 23:44:52', 19.99, 'Cancelled', 'Lorong Bukit Pantai, Pantai Hills, Bangsar, Kuala Lumpur, 59100, Malaysia');

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
(10, 10, 1, 2, 15.50),
(11, 12, 9, 1, 8.99),
(12, 13, 12, 1, 19.99);

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
(10, 10, 'Credit Card', '1234123412341234', '2025-12-12 15:35:05', 31.00, 'Success'),
(11, 12, 'Credit Card', '1234567812345678', '2025-12-20 23:33:31', 0.00, 'Success'),
(12, 13, 'Credit Card', '1234567812345678', '2025-12-20 23:44:52', 19.99, 'Success');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_phone` varchar(20) DEFAULT NULL,
  `user_dob` date DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `user_photo` varchar(255) DEFAULT NULL,
  `user_role` enum('admin','member','customer') DEFAULT 'customer',
  `user_address` varchar(255) DEFAULT NULL,
  `user_registrationDate` datetime NOT NULL DEFAULT current_timestamp(),
  `user_status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `member_id`, `username`, `email`, `user_phone`, `user_dob`, `user_password`, `user_photo`, `user_role`, `user_address`, `user_registrationDate`, `user_status`) VALUES
(21, '', 'ahmad_ali', 'ahmad.ali88@gmail.com', '012-3456789', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '12, Jalan Tun Razak, 50400 Kuala Lumpur, Wilayah Persekutuan', '2025-12-22 07:06:48', 1),
(22, '', 'siti_nurhaliza', 'siti.nur90@gmail.com', '019-8765432', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '45, Lorong Bahagia, Taman Melawati, 53100 Kuala Lumpur', '2025-12-22 07:06:48', 1),
(23, 'M-003', 'tan_wei_ming', 'tan.weiming@gmail.com', '016-1234567', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'member', '88, Jalan SS2/24, 47300 Petaling Jaya, Selangor', '2025-12-22 07:06:48', 1),
(24, '', 'subramaniam_k', 'subra.k@gmail.com', '017-5551234', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '5, Jalan Gasing, 46000 Petaling Jaya, Selangor', '2025-12-22 07:06:48', 1),
(25, '', 'lee_chong_wei', 'lee.chongwei@gmail.com', '012-9988776', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '101, Jalan Burma, 10050 Georgetown, Pulau Pinang', '2025-12-22 07:06:48', 1),
(26, '', 'nor_azman', 'azman.nor@gmail.com', '013-3344556', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '23, Jalan Skudai, 81300 Skudai, Johor', '2025-12-22 07:06:48', 1),
(27, '', 'lim_mei_ling', 'lim.meiling@gmail.com', '014-6677889', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', 'B-12-3, Kondominium Indah, Jalan Ampang, 50450 Kuala Lumpur', '2025-12-22 07:06:48', 1),
(28, 'M-004', 'rajesh_kumar', 'rajesh.kumar@gmail.com', '018-7766554', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'member', '7, Lebuhraya Persekutuan, 40000 Shah Alam, Selangor', '2025-12-22 07:06:48', 1),
(29, '', 'fatimah_yusof', 'fatimah.y@gmail.com', '011-12349876', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '15, Jalan Sultan Ismail, 20200 Kuala Terengganu, Terengganu', '2025-12-22 07:06:48', 1),
(30, '', 'wong_kah_seng', 'wong.kahseng@gmail.com', '010-2233445', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '99, Jalan Sultan Azlan Shah, 31400 Ipoh, Perak', '2025-12-22 07:06:48', 1),
(31, '', 'nurul_ain', 'nurul.ain@gmail.com', '019-1122334', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '34, Taman Universiti, 81300 Skudai, Johor', '2025-12-22 07:06:48', 1),
(32, '', 'ganesh_m', 'ganesh.m@gmail.com', '016-9988112', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '21, Jalan Tengku Kelana, 41000 Klang, Selangor', '2025-12-22 07:06:48', 1),
(33, 'M-005', 'sarah_lee', 'sarah.lee@gmail.com', '017-4455667', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'member', '12, Lorong Selamat, 10400 Georgetown, Pulau Pinang', '2025-12-22 07:06:48', 1),
(34, '', 'mohammad_zaki', 'm.zaki@gmail.com', '013-7788990', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '56, Jalan Long Yunus, 15200 Kota Bharu, Kelantan', '2025-12-22 07:06:48', 1),
(35, '', 'chin_xiao_wei', 'chin.xw@gmail.com', '012-6655443', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '8, Jalan Tebrau, 80250 Johor Bahru, Johor', '2025-12-22 07:06:48', 1),
(36, '', 'kavita_devi', 'kavita.devi@gmail.com', '018-2233441', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '44, Jalan Brickfields, 50470 Kuala Lumpur', '2025-12-22 07:06:48', 1),
(37, '', 'zainal_abidin', 'zainal.a@gmail.com', '014-9988771', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '77, Jalan Tunku Abdul Rahman, 93100 Kuching, Sarawak', '2025-12-22 07:06:48', 1),
(38, '', 'ng_kok_leong', 'ng.kokleong@gmail.com', '011-55667788', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '3, Jalan Gaya, 88000 Kota Kinabalu, Sabah', '2025-12-22 07:06:48', 1),
(39, '', 'aishah_binti_omar', 'aishah.omar@gmail.com', '010-8877665', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '19, Jalan Meru, 41050 Klang, Selangor', '2025-12-22 07:06:48', 1),
(40, '', 'vincent_tan', 'vincent.tan@gmail.com', '012-3322110', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '10, Persiaran Gurney, 10250 Georgetown, Pulau Pinang', '2025-12-22 07:06:48', 1),
(41, 'M-006', 'siti_sarah', 'siti.sarah@gmail.com', '019-4433221', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'member', '5, Jalan Hang Tuah, 75300 Melaka, Melaka', '2025-12-22 07:06:48', 1),
(42, '', 'jason_lim', 'jason.lim@gmail.com', '016-5566443', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '22, Jalan Templer, 46050 Petaling Jaya, Selangor', '2025-12-22 07:06:48', 1),
(43, '', 'thirumalai_r', 'thiru.r@gmail.com', '017-8899001', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '11, Jalan Silibin, 30100 Ipoh, Perak', '2025-12-22 07:06:48', 1),
(44, '', 'hazwan_hashim', 'hazwan.h@gmail.com', '013-1122998', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '67, Jalan Beserah, 25300 Kuantan, Pahang', '2025-12-22 07:06:48', 1),
(45, '', 'chan_yee_ling', 'chan.yl@gmail.com', '012-7788665', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '33, Jalan Cheras, 56100 Kuala Lumpur', '2025-12-22 07:06:48', 1),
(46, '', 'amanda_wong', 'amanda.wong@gmail.com', '018-9900112', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', 'Lot 45, Jalan Penampang, 88300 Kota Kinabalu, Sabah', '2025-12-22 07:06:48', 1),
(47, '', 'faizal_hussein', 'faizal.h@gmail.com', '011-33441122', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '8, Jalan Satok, 93400 Kuching, Sarawak', '2025-12-22 07:06:48', 1),
(48, '', 'devan_nair', 'devan.nair@gmail.com', '014-2233990', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '14, Jalan Sungai Besi, 57100 Kuala Lumpur', '2025-12-22 07:06:48', 1),
(49, '', 'koh_li_ann', 'koh.liann@gmail.com', '010-6677443', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '9, Jalan Molek 2/1, Taman Molek, 81100 Johor Bahru, Johor', '2025-12-22 07:06:48', 1),
(50, '', 'syed_mokhtar', 'syed.mokhtar@gmail.com', '019-5544332', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '50, Jalan Putra, 05150 Alor Setar, Kedah', '2025-12-22 07:06:48', 1),
(51, 'M-001', 'jason', 'jason@gmail.com', '012-4567890', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'member', '10, Jalan Kiara 3, Mont Kiara, 50480 Kuala Lumpur', '2025-12-22 07:13:13', 1),
(52, '', 'lim', 'lim@gmail.com', '017-3322445', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'admin', '88, Jalan Seremban 2, 70300 Seremban, Negeri Sembilan', '2025-12-22 07:13:13', 1),
(53, 'M-002', 'test_member', 'member@gmail.com', '016-1122334', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'member', '5, Jalan Batu Caves, 68100 Batu Caves, Selangor', '2025-12-22 07:13:13', 1),
(54, '', 'test_admin', 'admin@gmail.com', '011-99887766', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'admin', '12, Jalan Tanjung Bungah, 11200 Tanjung Bungah, Pulau Pinang', '2025-12-22 07:13:13', 1),
(55, '', 'test_customer', 'customer@gmail.com', '013-5544667', NULL, '8cb2237d0679ca88db6464eac60da96345513964', NULL, 'customer', '3, Presint 9, 62250 Putrajaya, Wilayah Persekutuan', '2025-12-22 07:13:13', 1);

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
