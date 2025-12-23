-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2025-12-23 07:00:27
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `bookstore`
--

-- --------------------------------------------------------

--
-- 表的结构 `book`
--

CREATE TABLE `book` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `cover_image` varchar(255) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `book`
--

INSERT INTO `book` (`id`, `title`, `description`, `author`, `publisher`, `category`, `subcategory`, `language`, `price`, `stock`, `cover_image`, `images`, `video`) VALUES
(3, 'SIRI NOVEL INSPIRASI SAHABAT RASULULLAH : SAYA NAK JADI CEKAL SEPERTI BILAL BIN RABBAH', 'SIRI NOVEL INSPIRASI SAHABAT RASULULLAH SERIES', 'IBnu Batoota', 'gempak starz', 'Fiction', 'Novel', 'Malay', 27.00, 87, 'cover_694909564b695.jpg', 'img_694909564b8a0.jpg,img_694909564bc60.jpg,img_694909564c010.jpg,img_694909564c1b5.jpg', NULL),
(4, 'How to Talk to Anyone: 92 Little Tricks for Big Success in Relationships', '', 'Leil Lowndes', 'Dewy pb', 'Non-Fiction', 'Self-help', 'English', 24.00, 49, 'cover_69490b0cba628.jpg', 'img_69490b0cba7bc.jpg', NULL),
(5, 'Hatsune Miku 初音未来官方填色画册 动漫粉丝与插画师必备', '全新推出「初音未来」涂色书，是标志性虚拟歌姬粉丝必入的收藏。书中共收录16幅精美插画，其中包括官方画师KEI全新绘制的作品，以及受人气Vocaloid歌曲「千本樱」启发的插画。走进初音未来的世界，用你喜爱的色彩为这些唯美画面赋予新的生命。', 'yamaha', 'WAFUU JAPAN', 'Children', 'Color Book', 'Chinese', 39.39, 178, 'cover_694909a3417ec.jpg', 'img_694909a341989.jpg', NULL),
(6, 'PROFESSION SERIES 74 VIRUS VORTEX ( VIROLOGIST)', 'Chinese version', 'Annie', 'gempakstarz@cemerlang', 'Fiction', 'Comic', 'Chinese', 24.00, 79, 'cover_694909e151b19.jpg', 'img_694909e151cb2.jpg,img_694909e151ddf.jpg,img_694909e1524c7.jpg,img_694909e152635.jpg', NULL),
(13, '曼巴精神（特贈限量全球獨家超大型73公分乘52公分華麗書衣海報', 'The Mamba Mentality：How I Play', 'Kobe Bryant', 'laker@publish', 'Non-Fiction', 'Biography', 'Chinese', 8.00, 8, 'cover_69490983b43a9.jpg', 'img_69490983b4547.jpg,img_69490983b4a9b.jpg,img_69490983b4ef9.jpg,img_69490983b5098.jpg', 'vid_69498c84a0f4e.mp4'),
(14, 'English Workout', 'CEFR A2 BOOK 1', 'EPM', 'EPM@pb', 'Education', 'Textbook', 'English', 27.00, 500, 'cover_69490a3279a38.jpg', 'img_69490a3279b2c.jpg,img_69490a3279bd0.jpg,img_69490a3279de4.jpg,img_69490a327a2ed.jpg', NULL),
(15, 'test', 'alt', 't', 't', 'Fiction', 'Novel', 'English', 1.00, 1, '', '', 'vid_6949abd3e096c.mp4'),
(17, 'The Great Gatsby', 'A classic novel about the American dream in the 1920s.', 'F. Scott Fitzgerald', 'Scribner', 'Fiction', 'Novel', 'English', 35.00, 100, 'gatsby.jpg', NULL, NULL),
(18, 'Norwegian Wood', 'A nostalgic story of loss and sexuality.', 'Haruki Murakami', 'Kodansha', 'Fiction', 'Novel', 'English', 45.50, 80, 'norwegian_wood.jpg', NULL, NULL),
(19, 'Spider-Man: Blue', 'A melancholy look back at Peter Parkers first love.', 'Jeph Loeb', 'Marvel', 'Fiction', 'Comic', 'English', 65.00, 50, 'spiderman_blue.jpg', NULL, NULL),
(20, 'Watchmen', 'A deconstruction of the superhero genre.', 'Alan Moore', 'DC Comics', 'Fiction', 'Comic', 'English', 85.00, 40, 'watchmen.jpg', NULL, NULL),
(21, 'Steve Jobs', 'The authorized self-titled biography of Steve Jobs.', 'Walter Isaacson', 'Simon & Schuster', 'Non-Fiction', 'Biography', 'English', 55.00, 120, 'steve_jobs.jpg', NULL, NULL),
(22, 'Becoming', 'A deeply personal reckoning of the former First Lady.', 'Michelle Obama', 'Crown', 'Non-Fiction', 'Biography', 'English', 60.00, 95, 'becoming.jpg', NULL, NULL),
(23, 'Atomic Habits', 'An easy way to build good habits and break bad ones.', 'James Clear', 'Avery', 'Non-Fiction', 'Self-help', 'English', 40.00, 200, 'atomic_habits.jpg', NULL, NULL),
(24, 'The Silent Patient', 'A psychological thriller about a womans act of violence.', 'Alex Michaelides', 'Celadon Books', 'Non-Fiction', 'Self-help', 'English', 38.00, 150, 'silent_patient.jpg', NULL, NULL),
(25, 'Biology: A Global Approach', 'The most successful majors biology textbook in the world.', 'Neil Campbell', 'Pearson', 'Education', 'Textbook', 'English', 150.00, 60, 'biology_textbook.jpg', NULL, NULL),
(26, 'Modern Physics', 'Covers the foundations of modern physics.', 'Stephen Thornton', 'Cengage', 'Education', 'Textbook', 'English', 135.00, 30, 'physics_textbook.jpg', NULL, NULL),
(27, 'Disney Princess Coloring', 'Fun coloring pages with all your favorite princesses.', 'Disney', 'Parragon', 'Children', 'Color Book', 'English', 15.00, 300, 'disney_color.jpg', NULL, NULL),
(28, 'Animals of the World', 'Learn about animals while you color them!', 'Creative Play', 'Scholastic', 'Children', 'Color Book', 'English', 12.50, 250, 'animal_color.jpg', NULL, NULL);

--
-- 转储表的索引
--

--
-- 表的索引 `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `book`
--
ALTER TABLE `book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
