-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 07:38 AM
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
-- Database: `wage_warriors`
--

-- --------------------------------------------------------

--
-- Structure for view `payroll_view`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payroll_view`  AS SELECT `e`.`employee_id` AS `employee_id`, concat(`e`.`first_name`,' ',`e`.`last_name`) AS `full_name`, `r`.`role` AS `role`, concat(time_format(`s`.`time_in`,'%l %p'),' - ',time_format(`s`.`time_out`,'%l %p')) AS `schedule`, `r`.`rate_per_hour` AS `rate_per_hour`, count(distinct `a`.`date`) AS `total_work_days`, round(sum(time_to_sec(timediff(`a`.`time_out`,`a`.`time_in`))) / 3600,2) AS `total_work_hours`, round(sum(time_to_sec(timediff(`a`.`time_out`,`a`.`time_in`))) / 3600 * `r`.`rate_per_hour`,2) AS `salary` FROM (((`attendance` `a` join `employees` `e` on(`e`.`employee_id` = `a`.`employee_id`)) join `roles` `r` on(`r`.`role_id` = `e`.`role_id`)) join `schedule` `s` on(`s`.`employee_id` = `e`.`employee_id`)) GROUP BY `e`.`employee_id` ;

--
-- VIEW `payroll_view`
-- Data: None
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
