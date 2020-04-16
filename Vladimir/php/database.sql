SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `database`
--

-- --------------------------------------------------------

--
-- Структура таблицы `api_users`
--

DROP TABLE IF EXISTS `api_users`;
CREATE TABLE `api_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `api_users`
--

INSERT INTO `api_users` (`id`, `username`, `password`) VALUES
(1, 'admin', '827ccb0eea8a706c4c34a16891f84e7b');

-- --------------------------------------------------------

--
-- Структура таблицы `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `groups`
--

INSERT INTO `groups` (`id`, `name`) VALUES
(1, 'Default Group'),
(2, 'Custom Group 1'),
(3, 'Custom group 2'),
(4, 'Custom group 3'),
(5, 'Custom group 4');

-- --------------------------------------------------------

--
-- Структура таблицы `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `students`
--

INSERT INTO `students` (`id`, `username`, `firstname`, `lastname`, `group_id`) VALUES
(2, 'student1', 'Firstname1', 'Lastname1', 3),
(3, 'student2', 'Firstname2', 'Lastname2', 3),
(4, 'student3', 'Firstname3', 'Lastname3', 2),
(5, 'student4', 'Firstname4', 'Lastname4', 5),
(6, 'student5', 'Firstname5', 'Lastname5', 1),
(7, 'student6', 'Firstname6', 'Lastname6', 3),
(8, 'student7', 'Firstname7', 'Lastname7', 1),
(9, 'student8', 'Firstname8', 'Lastname8', 4),
(10, 'student9', 'Firstname9', 'Lastname9', 2),
(11, 'student10', 'Firstname10', 'Lastname10', 2),
(12, 'student11', 'Firstname11', 'Lastname11', 3),
(13, 'student12', 'Firstname12', 'Lastname12', 2),
(14, 'student13', 'Firstname13', 'Lastname13', 2),
(15, 'student14', 'Firstname14', 'Lastname14', 4),
(16, 'student15', 'Firstname15', 'Lastname15', 4),
(17, 'student16', 'Firstname16', 'Lastname16', 4),
(18, 'student17', 'Firstname17', 'Lastname17', 3),
(19, 'student18', 'Firstname18', 'Lastname18', 2),
(20, 'student19', 'Firstname19', 'Lastname19', 2),
(21, 'student20', 'Firstname20', 'Lastname20', 2),
(22, 'student21', 'Firstname21', 'Lastname21', 2),
(23, 'student22', 'Firstname22', 'Lastname22', 3);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `api_users`
--
ALTER TABLE `api_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Индексы таблицы `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `api_users`
--
ALTER TABLE `api_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
