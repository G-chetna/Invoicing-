-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2025 at 04:00 PM
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
-- Database: `invoicing`
--

-- --------------------------------------------------------

--
-- Table structure for table `clientinvoice`
--

CREATE TABLE `clientinvoice` (
  `InvoiceId` int(11) NOT NULL,
  `ClientId` int(11) NOT NULL,
  `InvoiceNumber` bigint(20) UNSIGNED DEFAULT NULL,
  `corporateId` int(11) NOT NULL,
  `InvoiceDate` date NOT NULL,
  `DueDate` date NOT NULL,
  `Status` enum('FullDue','PartialPaid','Closed') NOT NULL DEFAULT 'FullDue',
  `BillAmount` decimal(13,2) NOT NULL,
  `Discounts` decimal(13,2) NOT NULL,
  `Discountedamount` decimal(13,2) NOT NULL,
  `DiscountsDescriptions` varchar(1024) DEFAULT NULL,
  `TotalPaid` decimal(13,2) NOT NULL,
  `PaymentDate` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `clientinvoice`
--

INSERT INTO `clientinvoice` (`InvoiceId`, `ClientId`, `InvoiceNumber`, `corporateId`, `InvoiceDate`, `DueDate`, `Status`, `BillAmount`, `Discounts`, `Discountedamount`, `DiscountsDescriptions`, `TotalPaid`, `PaymentDate`) VALUES
(81, 4, 4038676489699713, 1, '2024-11-27', '2024-12-11', 'PartialPaid', 2004.00, 5.00, 1903.80, 'ttec-martin', 1800.00, '2024-11-27'),
(82, 7, 4369701588325456, 2, '2024-11-27', '2024-12-11', 'Closed', 300.00, 4.00, 288.00, 'Microsoft-Erin', 288.00, '2024-11-27'),
(83, 0, 1237102063548676, 0, '2024-11-27', '2024-12-11', 'FullDue', 0.00, 0.00, 0.00, '', 0.00, '2024-11-27'),
(84, 14, 3193154150822127, 1, '2024-11-27', '2024-12-11', 'PartialPaid', 408.00, 4.00, 391.68, 'siemans-sara', 340.00, '2024-11-27'),
(85, 14, 1229378494054549, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 1088.00, 4.00, 1044.48, 'sara-sie', 1000.00, '2024-11-28'),
(86, 14, 9957762280017554, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 1088.00, 4.00, 1044.48, 'sara-sie', 1000.00, '2024-11-28'),
(87, 5, 5323965650675851, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 400.00, 3.00, 388.00, 'wedfgh', 345.00, '2024-11-28'),
(88, 5, 8329669428661560, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 400.00, 3.00, 388.00, 'wedfgh', 345.00, '2024-11-28'),
(89, 14, 4001632374765930, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 714.00, 4.00, 685.44, '', 222.00, '2024-11-28'),
(90, 9, 5498750117560414, 2, '2024-11-28', '2024-12-12', 'PartialPaid', 60.00, 3.00, 58.20, '', 56.00, '2024-11-28'),
(91, 15, 9125790639850517, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 66.00, 2.00, 64.68, '', 23.00, '2024-11-28'),
(92, 14, 8474630541976298, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 196.00, 7.00, 182.28, '', 180.00, '2024-11-28'),
(93, 9, 5346342621858367, 2, '2024-11-28', '2024-12-12', 'PartialPaid', 600.00, 3.00, 582.00, '', 543.00, '2024-11-28'),
(94, 15, 5481188870980784, 1, '2024-11-28', '2024-12-12', 'Closed', 44.00, 0.00, 44.00, '', 44.00, '2024-11-28'),
(95, 14, 9136638044890976, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 196.00, 0.00, 196.00, '', 190.00, '2024-11-28'),
(96, 14, 6458328781176365, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 196.00, 0.00, 196.00, '', 190.00, '2024-11-28'),
(97, 14, 7307289878708487, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 196.00, 0.00, 196.00, '', 190.00, '2024-11-28'),
(98, 14, 9394506450185693, 1, '2024-11-28', '2024-12-12', 'PartialPaid', 196.00, 0.00, 196.00, '', 190.00, '2024-11-28'),
(99, 7, 113646876380144, 2, '2024-11-28', '2024-12-12', 'PartialPaid', 140.00, 5.00, 133.00, 'ttec-martin', 112.00, '2024-11-28'),
(100, 7, 5899868589889560, 2, '2024-11-28', '2024-12-12', 'PartialPaid', 140.00, 5.00, 133.00, 'ttec-martin', 112.00, '2024-11-28'),
(101, 9, 1629916281748430, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(102, 9, 9998681444660958, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(103, 9, 2137795021934408, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(104, 9, 3945236178849473, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(105, 9, 7500418475033613, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(106, 9, 4047064933253245, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(107, 9, 7316856739095828, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(108, 9, 3918785968383542, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(109, 9, 1462633130134558, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(110, 9, 3356509482531188, 2, '2024-11-29', '2024-12-13', 'PartialPaid', 500.00, 6.00, 470.00, 'stela- amazon', 450.00, '2024-11-29'),
(111, 4, 0, 1, '2024-11-29', '2024-12-13', 'PartialPaid', 150.00, 6.00, 141.00, 'ttec-martin', 120.00, '2024-11-29'),
(112, 4, 9458107958071353, 1, '2024-11-29', '2024-12-13', 'PartialPaid', 150.00, 6.00, 141.00, 'ttec-martin', 120.00, '2024-11-29'),
(113, 14, 7808741519856604, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(114, 14, 1574194617420157, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(115, 14, 8818868481275650, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(116, 14, 4873196898754699, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(117, 14, 1437464466855073, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(118, 14, 354613544249329, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(119, 14, 6755360373725659, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(120, 14, 7359466585944468, 1, '2024-12-01', '2024-12-15', 'PartialPaid', 136.00, 1.00, 134.64, 'sara', 123.00, '2024-12-01'),
(121, 5, 64996612295882, 1, '2024-12-01', '2024-12-15', 'Closed', 60.00, 0.00, 60.00, '00', 60.00, '2024-12-01'),
(122, 5, 4242173166443150, 1, '2024-12-01', '2024-12-15', 'Closed', 60.00, 0.00, 60.00, '00', 60.00, '2024-12-01'),
(123, 5, 3734501552162742, 1, '2024-12-01', '2024-12-15', 'Closed', 60.00, 0.00, 60.00, '00', 60.00, '2024-12-01'),
(124, 5, 3902119216146000, 1, '2024-12-01', '2024-12-15', 'Closed', 60.00, 0.00, 60.00, '00', 60.00, '2024-12-01'),
(125, 9, 5411216295043825, 2, '2024-12-05', '2024-12-19', 'Closed', 150.00, 0.00, 150.00, 'sdf', 150.00, '2024-12-05'),
(126, 22, 5536711264408319, 2, '2024-12-05', '2024-12-19', 'Closed', 90.00, 0.00, 90.00, 'erin', 90.00, '2024-12-05'),
(127, 22, 4624412598561606, 2, '2024-12-05', '2024-12-19', 'Closed', 90.00, 0.00, 90.00, 'erin', 90.00, '2024-12-05'),
(128, 22, 103824452829858, 2, '2024-12-05', '2024-12-19', 'Closed', 90.00, 0.00, 90.00, 'erin', 90.00, '2024-12-05'),
(129, 22, 3516995521599740, 2, '2024-12-05', '2024-12-19', 'Closed', 90.00, 0.00, 90.00, 'erin', 90.00, '2024-12-05'),
(130, 22, 4367971353555957, 2, '2024-12-05', '2024-12-19', 'Closed', 90.00, 0.00, 90.00, 'erin', 90.00, '2024-12-05'),
(131, 22, 5774272623587921, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 900.00, 5.00, 855.00, 'Matt', 600.00, '2024-12-05'),
(132, 22, 5451532437702808, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 600.00, 4.00, 576.00, 'erin', 234.00, '2024-12-05'),
(133, 15, 1931660121137993, 1, '2024-12-05', '2024-12-19', 'PartialPaid', 66.00, 2.00, 64.68, 'sdfghy', 56.00, '2024-12-05'),
(134, 15, 2581972732440941, 1, '2024-12-05', '2024-12-19', 'PartialPaid', 66.00, 2.00, 64.68, 'sdfghy', 56.00, '2024-12-05'),
(135, 15, 3769210947861110, 1, '2024-12-05', '2024-12-19', 'PartialPaid', 66.00, 2.00, 64.68, 'sdfghy', 56.00, '2024-12-05'),
(136, 9, 9492999481833780, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 150.00, 0.00, 150.00, '3456', 123.00, '2024-12-05'),
(137, 9, 2290543820652508, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 150.00, 0.00, 150.00, '3456', 123.00, '2024-12-05'),
(138, 4, 7898446533729261, 1, '2024-12-05', '2024-12-19', 'Closed', 90.00, 0.00, 90.00, 'wertyu', 90.00, '2024-12-05'),
(139, 4, 6843023281753484, 1, '2024-12-05', '2024-12-19', 'Closed', 90.00, 0.00, 90.00, 'wertyu', 90.00, '2024-12-05'),
(140, 7, 2267547852138715, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 80.00, 4.00, 76.80, 'stela', 60.00, '2024-12-05'),
(141, 7, 8665120212480739, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 80.00, 4.00, 76.80, 'stela', 60.00, '2024-12-05'),
(148, 9, 4879392654500748, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 140.00, 0.00, 140.00, 'wertyuiopoiuytrew', 130.00, '2024-12-05'),
(149, 9, 9620152812345769, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 450.00, 4.00, 432.00, 'rttyu', 200.00, '2024-12-05'),
(150, 22, 3303684432375101, 2, '2024-12-05', '2024-12-19', 'PartialPaid', 1350.00, 2.00, 1323.00, 'dfghjkl;\\\';lkjhgfds', 1200.00, '2024-12-05'),
(151, 14, 4955146647352903, 1, '2024-12-07', '2024-12-21', 'PartialPaid', 84.00, 2.00, 82.32, 'test', 82.00, '2024-12-07'),
(152, 14, 6431979416291712, 1, '2024-12-07', '2024-12-21', 'Closed', 170.00, 5.00, 161.50, 'test- 170', 161.50, '2024-12-07'),
(153, 16, 7047119110008414, 2, '2024-12-07', '2024-12-21', 'Closed', 138.00, 5.00, 131.10, 'test-138', 131.10, '2024-12-07'),
(154, 16, 3804485987870889, 2, '2024-12-07', '2024-12-21', 'Closed', 138.00, 5.00, 131.10, 'test-138', 131.10, '2024-12-07'),
(155, 16, 3937287392552521, 2, '2024-12-09', '2024-12-23', 'PartialPaid', 138.00, 2.00, 135.24, 'erin', 135.00, '2024-12-09'),
(156, 7, 9963770764631375, 2, '2024-12-11', '2024-12-25', 'PartialPaid', 80.00, 2.00, 78.40, '3', 75.00, '2024-12-11'),
(157, 7, 2024121105205, 2, '2024-12-11', '2024-12-25', 'FullDue', 125.00, 5.00, 118.75, 'test', 119.00, '2024-12-11'),
(158, 4, 2024121126784, 1, '2024-12-11', '2024-12-25', 'Closed', 120.00, 15.00, 102.00, 'test', 102.00, '2024-12-11'),
(159, 7, 2024121164160, 2, '2024-12-11', '2024-12-25', 'PartialPaid', 75.00, 2.00, 73.50, 'test3', 73.00, '2024-12-11'),
(160, 9, 2024121554210, 2, '2024-12-15', '2024-12-29', 'Closed', 225.00, 1.00, 222.75, 'amazon-matt', 222.75, '2024-12-15'),
(161, 14, 2024121523918, 1, '2024-12-15', '2024-12-29', 'Closed', 112.00, 1.00, 110.88, 'sara-seimans', 110.88, '2024-12-15'),
(162, 9, 2024121533669, 2, '2024-12-15', '2024-12-29', 'PartialPaid', 100.00, 2.00, 98.00, 'sdfg', 97.00, '2024-12-15'),
(163, 4, 2024121581657, 1, '2024-12-15', '2024-12-29', 'PartialPaid', 168.00, 0.00, 168.00, 'tyui', 160.00, '2024-12-15'),
(164, 4, 2024121585084, 2, '2024-12-15', '2024-12-29', 'PartialPaid', 150.00, 2.00, 147.00, 'ttec- stela', 140.00, '2024-12-15'),
(165, 14, 2024122428220, 1, '2024-12-24', '2025-01-07', 'PartialPaid', 272.00, 0.00, 272.00, 'test4', 160.00, '2024-12-24'),
(166, 4, 2025020165734, 0, '2025-02-01', '2025-02-15', 'FullDue', 210.00, 2.00, 205.80, 'test', 452.00, '2025-02-01'),
(167, 4, 2025020153543, 0, '2025-02-01', '2025-02-15', 'PartialPaid', 250.00, 5.00, 237.50, 'test', 234.00, '2025-02-01'),
(169, 0, 2025021546524, 0, '2025-02-15', '2025-03-01', 'Closed', 0.00, 0.00, 0.00, '', 0.00, '2025-02-15'),
(170, 7, 2025021561817, 2, '2025-02-15', '2025-03-01', 'FullDue', 40.00, 0.00, 40.00, '', 45.00, '2025-02-15'),
(171, 0, 2025022195562, 0, '2025-02-21', '2025-03-07', 'Closed', 0.00, 0.00, 0.00, '', 0.00, '2025-02-21');

-- --------------------------------------------------------

--
-- Table structure for table `clientinvoicedetails`
--

CREATE TABLE `clientinvoicedetails` (
  `InvoiceId` int(11) NOT NULL,
  `ClientEmployeeStateId` int(11) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `Hours` float NOT NULL,
  `TotalAmount` decimal(12,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `clientinvoicedetails`
--

INSERT INTO `clientinvoicedetails` (`InvoiceId`, `ClientEmployeeStateId`, `StartDate`, `EndDate`, `Hours`, `TotalAmount`) VALUES
(85, 16, '2024-10-31', '2024-11-12', 32, 1088.00),
(84, 16, '2024-11-16', '2024-11-25', 12, 408.00),
(82, 7, '2024-11-01', '2024-11-05', 12, 300.00),
(81, 5, '2024-11-01', '2024-11-14', 12, 504.00),
(81, 2, '2024-11-06', '2024-11-16', 30, 1500.00),
(86, 16, '2024-10-31', '2024-11-12', 32, 1088.00),
(87, 10, '2024-11-20', '2024-11-30', 10, 400.00),
(88, 10, '2024-11-20', '2024-11-30', 10, 400.00),
(89, 16, '2024-11-12', '2024-11-23', 21, 714.00),
(90, 11, '2024-11-06', '2024-11-15', 3, 60.00),
(91, 14, '2024-11-15', '2024-11-19', 3, 66.00),
(92, 12, '2024-11-06', '2024-11-08', 7, 196.00),
(93, 6, '2024-11-07', '2024-11-30', 12, 600.00),
(94, 13, '2024-10-30', '2024-11-20', 2, 44.00),
(95, 12, '2024-10-30', '2024-11-15', 7, 196.00),
(96, 12, '2024-10-30', '2024-11-15', 7, 196.00),
(97, 12, '2024-10-30', '2024-11-15', 7, 196.00),
(98, 12, '2024-10-30', '2024-11-15', 7, 196.00),
(99, 9, '2024-11-06', '2024-11-06', 7, 140.00),
(100, 9, '2024-11-06', '2024-11-06', 7, 140.00),
(101, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(102, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(103, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(104, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(105, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(106, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(107, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(108, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(109, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(110, 6, '2024-10-30', '2024-11-26', 10, 500.00),
(111, 2, '2024-11-12', '2024-11-15', 3, 150.00),
(112, 2, '2024-11-12', '2024-11-15', 3, 150.00),
(113, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(114, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(115, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(116, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(117, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(118, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(119, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(120, 16, '2024-12-19', '2024-12-28', 4, 136.00),
(121, 1, '2024-12-05', '2024-12-14', 4, 60.00),
(122, 1, '2024-12-05', '2024-12-14', 4, 60.00),
(123, 1, '2024-12-05', '2024-12-14', 4, 60.00),
(124, 1, '2024-12-05', '2024-12-14', 4, 60.00),
(125, 6, '2024-12-05', '2025-01-02', 3, 150.00),
(126, 17, '2024-12-04', '2024-12-13', 3, 90.00),
(127, 17, '2024-12-04', '2024-12-13', 3, 90.00),
(128, 17, '2024-12-04', '2024-12-13', 3, 90.00),
(129, 17, '2024-12-04', '2024-12-13', 3, 90.00),
(130, 17, '2024-12-04', '2024-12-13', 3, 90.00),
(131, 18, '2024-12-17', '2024-12-20', 30, 900.00),
(132, 17, '2024-12-13', '2024-12-29', 20, 600.00),
(133, 13, '2024-12-03', '2024-12-07', 3, 66.00),
(134, 13, '2024-12-03', '2024-12-07', 3, 66.00),
(135, 13, '2024-12-03', '2024-12-07', 3, 66.00),
(136, 6, '2024-12-11', '2024-12-28', 3, 150.00),
(137, 6, '2024-12-11', '2024-12-28', 3, 150.00),
(138, 4, '2024-12-11', '2024-12-25', 3, 90.00),
(139, 4, '2024-12-11', '2024-12-25', 3, 90.00),
(140, 9, '2024-12-09', '2024-12-17', 4, 80.00),
(141, 9, '2024-12-09', '2024-12-17', 4, 80.00),
(142, 11, '2024-12-11', '2024-12-27', 7, 140.00),
(143, 11, '2024-12-11', '2024-12-27', 7, 140.00),
(144, 11, '2024-12-11', '2024-12-27', 7, 140.00),
(145, 11, '2024-12-11', '2024-12-27', 7, 140.00),
(146, 11, '2024-12-11', '2024-12-27', 7, 140.00),
(147, 11, '2024-12-11', '2024-12-27', 7, 140.00),
(148, 11, '2024-12-11', '2024-12-27', 7, 140.00),
(149, 6, '2024-12-11', '2024-12-11', 9, 450.00),
(150, 18, '2024-12-11', '2024-12-21', 45, 1350.00),
(151, 12, '2024-12-18', '2024-12-27', 3, 84.00),
(152, 16, '2024-12-17', '2024-12-21', 5, 170.00),
(153, 15, '2024-12-11', '2025-01-07', 6, 138.00),
(154, 15, '2024-12-11', '2025-01-07', 6, 138.00),
(155, 15, '2024-12-19', '2024-12-27', 6, 138.00),
(156, 9, '2024-12-06', '2024-12-20', 4, 80.00),
(157, 7, '2024-12-06', '2024-12-14', 5, 125.00),
(158, 4, '2024-12-12', '2024-12-21', 4, 120.00),
(159, 7, '2024-12-07', '2024-12-25', 3, 75.00),
(160, 8, '2024-12-19', '2024-12-22', 5, 225.00),
(161, 12, '2024-12-18', '2024-12-19', 4, 112.00),
(162, 11, '2024-12-19', '2025-01-01', 5, 100.00),
(163, 5, '2024-12-12', '2024-12-21', 4, 168.00),
(164, 21, '2024-12-16', '2024-12-20', 6, 150.00),
(165, 16, '2024-12-04', '2024-12-19', 8, 272.00),
(166, 5, '2024-12-16', '2025-01-15', 5, 210.00),
(167, 22, '2025-02-02', '2025-03-04', 10, 250.00),
(170, 9, '2025-02-06', '2025-02-11', 2, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `clientpayments`
--

CREATE TABLE `clientpayments` (
  `InvoiceId` int(11) NOT NULL,
  `PaymentDate` date NOT NULL,
  `PaymentAmount` decimal(13,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `ClientId` int(11) NOT NULL,
  `ClientName` varchar(256) NOT NULL,
  `Phone` int(11) NOT NULL,
  `EmailId` varchar(256) NOT NULL,
  `Country` varchar(256) NOT NULL,
  `Addr1` varchar(256) NOT NULL,
  `Addr2` varchar(256) NOT NULL,
  `Addr3` varchar(256) NOT NULL,
  `City` varchar(256) NOT NULL,
  `State` varchar(256) NOT NULL,
  `Zip` varchar(18) NOT NULL,
  `EIN` decimal(9,0) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`ClientId`, `ClientName`, `Phone`, `EmailId`, `Country`, `Addr1`, `Addr2`, `Addr3`, `City`, `State`, `Zip`, `EIN`) VALUES
(6, 'Accenture', 1789456190, 'Accenture', 'US', '', '', '', '', '', '', 0),
(5, 'IMS', 456123355, 'ims.com', 'US', '', '', '', 'Dallas', 'Texas', '789955', 345),
(4, 'ttec', 1789456127, 'ttec.com', 'India', 'raidurg', 'Durgam Chervu', '', 'hyd', 'ts', '456142', 234),
(7, 'Microsoft', 456789123, 'Microsoft@microsoft.com', 'US', 'xyz', 'abc', 'esf', 'LA', 'albama', '23444', 23456),
(9, 'Amazon', 0, 'Aws@amazon.com', 'Us', '2517 Suncrest Dr', '', '', '', '', '3456789', 6789),
(10, 'servicenow', 2147483647, 'servicenow@microsoft.com', 'US', 'ertyu', 'xcvbnm,.', 'xgnh', 'LA', '345678', '23444', 23457),
(14, 'Siemans', 2147483647, 'sie@siemans.com', 'Canada', 'abc', '', '', 'Alberta', 'calgary', '789652', 412062),
(15, 'Kschema', 2147483, 'kschema@kschema.com', 'Canada', 'def', '', '', 'Alberta', 'calgary', '789658', 4120596),
(16, 'state street', 2147483647, 'statestreet@statestreet.com', 'Canada', 'calgary', '', '', 'ontario', 'calgary', '789678', 412051),
(17, 'Novartis', 2147483647, 'Novartis@nova.com', 'Canada', 'abc', '', '', 'Alberta', 'calgary', '789652', 412052),
(18, 'Accenture', 2147483647, 'Accenture@accenture.com', 'India', 'Durgam Chervu', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '500079', 234521),
(19, 'HPE', 2147483647, 'hewlett@hpe.com', 'India', 'Durgam Chervu', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '500079', 2345122),
(20, 'Bosch', 2147483647, 'Bosch@bosch.com', 'India', 'Durgam Chervu', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '500079', 234511),
(21, 'Alphastream', 2147483647, 'Alpha@stream.com', 'India', 'Durgam Chervu', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '500079', 234509),
(22, 'bosch12', 2147483647, 'Bosch@bosch.com', 'India', 'Durgam Chervu', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '500079', 234522),
(23, 'bosch12', 2147483647, 'Bosch12@bosch.com', 'India', 'Durgam Chervu', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '777777', 234512),
(24, 'bain', 2147483647, 'bain@gmail.com', 'India', 'sattva knowledgecity', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '345678', 345634),
(26, 'Vhr', 2147483647, 'Vhr@gmail.com', 'India', 'sattva knowledgecity', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '345678', 345643),
(27, 'verzio', 2147483647, 'verzio@gmail.com', 'India', 'sattva knowledgecity', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '345673', 345633),
(28, 'Test', 798972757, 'Test@gmail.com', 'India', 'sattva knowledgecity', 'Raidurg', 'Hitech city road', 'hyderabad', 'Telangana', '500232', 2446255);

-- --------------------------------------------------------

--
-- Table structure for table `clientscorporates`
--

CREATE TABLE `clientscorporates` (
  `ClientId` int(11) NOT NULL,
  `CorporateId` int(11) NOT NULL,
  `DateAdded` date NOT NULL,
  `Billingperiod` tinyint(4) NOT NULL DEFAULT 15,
  `Billingfrequency` enum('One week','Two Weeks','Half Month','Full Month','Custom') NOT NULL DEFAULT 'Custom',
  `BillingFrequencyCode` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `clientscorporates`
--

INSERT INTO `clientscorporates` (`ClientId`, `CorporateId`, `DateAdded`, `Billingperiod`, `Billingfrequency`, `BillingFrequencyCode`) VALUES
(5, 1, '2024-05-01', 15, 'Custom', 5),
(4, 1, '2024-05-03', 15, 'One week', 3),
(9, 2, '2024-05-03', 15, 'Custom', 5),
(4, 2, '2024-04-01', 15, 'One week', 3),
(7, 2, '2024-05-04', 15, 'Custom', 5),
(17, 1, '2024-09-29', 10, 'Custom', 5),
(14, 1, '0000-00-00', 15, 'Custom', 5),
(15, 1, '2024-08-04', 15, 'Custom', 5),
(16, 2, '2024-08-04', 20, 'Custom', 5),
(18, 2, '2024-11-27', 20, 'Custom', 5),
(19, 2, '2024-11-27', 35, 'Custom', 5),
(20, 1, '2024-11-27', 45, 'Custom', 5),
(21, 1, '2024-11-27', 34, 'Custom', 5),
(22, 2, '2024-12-05', 45, 'Custom', 5),
(23, 1, '2024-12-15', 45, 'Custom', 5),
(28, 2, '2024-12-28', 45, 'Half Month', 2);

-- --------------------------------------------------------

--
-- Table structure for table `clientsemployeestate`
--

CREATE TABLE `clientsemployeestate` (
  `ClientEmployeeStateId` int(11) NOT NULL,
  `ClientId` int(11) NOT NULL,
  `EmployeeStateId` int(11) NOT NULL,
  `StartDate` date NOT NULL,
  `Status` enum('Active','Closed','','') NOT NULL DEFAULT 'Active',
  `BillingRate` decimal(9,2) NOT NULL,
  `DueDays` tinyint(4) NOT NULL DEFAULT 14,
  `EndDate` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `clientsemployeestate`
--

INSERT INTO `clientsemployeestate` (`ClientEmployeeStateId`, `ClientId`, `EmployeeStateId`, `StartDate`, `Status`, `BillingRate`, `DueDays`, `EndDate`) VALUES
(1, 5, 1, '2024-05-03', 'Active', 15.00, 14, '2024-05-31'),
(2, 4, 3, '2024-04-18', 'Active', 50.00, 14, '2024-05-18'),
(3, 5, 4, '2024-03-16', 'Active', 35.00, 14, '2024-05-23'),
(4, 4, 7, '2024-04-15', 'Active', 30.00, 14, '2024-04-15'),
(5, 4, 4, '0000-00-00', 'Active', 42.00, 14, NULL),
(6, 9, 5, '0000-00-00', 'Active', 50.00, 22, NULL),
(7, 7, 6, '0000-00-00', 'Active', 25.00, 14, NULL),
(8, 9, 4, '2024-08-01', 'Active', 45.00, 14, '2024-08-20'),
(9, 7, 5, '2024-08-25', 'Active', 20.00, 14, '2024-08-31'),
(10, 5, 2, '2024-10-11', 'Active', 40.00, 14, '2024-10-12'),
(11, 9, 6, '2024-10-02', 'Active', 20.00, 20, '2024-10-16'),
(12, 14, 7, '2024-11-23', 'Active', 28.00, 40, '2024-11-29'),
(13, 15, 3, '2024-11-24', 'Active', 22.00, 30, '2024-11-30'),
(14, 15, 3, '2024-11-07', 'Active', 22.00, 30, '2024-11-02'),
(15, 16, 6, '2024-11-09', 'Active', 23.00, 21, '2024-11-22'),
(16, 14, 7, '2024-11-30', 'Active', 34.00, 20, '2024-12-07'),
(17, 22, 6, '2024-12-07', 'Active', 30.00, 20, '2024-12-14'),
(18, 22, 4, '2024-12-06', 'Active', 30.00, 20, '2024-12-18'),
(19, 17, 3, '2024-12-20', 'Active', 30.00, 20, '2024-12-27'),
(20, 7, 4, '2024-12-06', '', 0.00, 0, '2024-12-12'),
(21, 4, 5, '2024-12-12', 'Active', 25.00, 20, '2024-12-27'),
(22, 4, 4, '2024-12-06', 'Active', 25.00, 20, '2024-12-21');

-- --------------------------------------------------------

--
-- Table structure for table `corporates`
--

CREATE TABLE `corporates` (
  `CorporateID` int(11) NOT NULL,
  `CorporateName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `corporates`
--

INSERT INTO `corporates` (`CorporateID`, `CorporateName`) VALUES
(1, 'ABC solutions'),
(2, 'SG Solutions');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EmployeeStateId` int(11) NOT NULL,
  `CorporateID` int(11) NOT NULL,
  `EmployeeName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EmployeeStateId`, `CorporateID`, `EmployeeName`) VALUES
(1, 1, 'John'),
(2, 1, 'Jack'),
(3, 1, 'Martin'),
(4, 2, 'Matt'),
(5, 2, 'Stela'),
(6, 2, 'Erin'),
(7, 1, 'Sara');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clientinvoice`
--
ALTER TABLE `clientinvoice`
  ADD PRIMARY KEY (`InvoiceId`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_invoice_number` (`InvoiceNumber`);

--
-- Indexes for table `clientinvoicedetails`
--
ALTER TABLE `clientinvoicedetails`
  ADD UNIQUE KEY `InvoiceId` (`InvoiceId`,`ClientEmployeeStateId`,`StartDate`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`ClientId`),
  ADD UNIQUE KEY `ClientName` (`ClientName`,`Zip`),
  ADD UNIQUE KEY `unique_ein` (`EIN`);

--
-- Indexes for table `clientsemployeestate`
--
ALTER TABLE `clientsemployeestate`
  ADD PRIMARY KEY (`ClientEmployeeStateId`),
  ADD UNIQUE KEY `ClientId` (`ClientId`,`EmployeeStateId`,`StartDate`);

--
-- Indexes for table `corporates`
--
ALTER TABLE `corporates`
  ADD PRIMARY KEY (`CorporateID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeStateId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clientinvoice`
--
ALTER TABLE `clientinvoice`
  MODIFY `InvoiceId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `ClientId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `clientsemployeestate`
--
ALTER TABLE `clientsemployeestate`
  MODIFY `ClientEmployeeStateId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `corporates`
--
ALTER TABLE `corporates`
  MODIFY `CorporateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `EmployeeStateId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
