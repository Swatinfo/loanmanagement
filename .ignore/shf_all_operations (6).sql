-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 16, 2026 at 06:55 PM
-- Server version: 10.3.39-MariaDB-0+deb10u2
-- PHP Version: 7.4.33

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shf_all_operations`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `activity_logs`
--

TRUNCATE TABLE `activity_logs`;
--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `subject_type`, `subject_id`, `properties`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'auto_assign_stage', 'App\\Models\\StageAssignment', 3, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"document_collection\",\"assigned_to_name\":\"JAYDEEP THESHIYA\"}', '127.0.0.1', 'Symfony', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(2, 1, 'convert_quotation_to_loan', 'App\\Models\\LoanDetail', 1, '{\"quotation_id\":1,\"loan_number\":\"SHF-202604-0001\",\"customer_name\":\"Vipul Parsana\",\"loan_amount\":5000000,\"bank_name\":\"ICICI Bank\"}', '127.0.0.1', 'Symfony', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(3, 1, 'login', 'App\\Models\\User', 1, '{\"name\":\"Super Admin\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-15 16:47:55', '2026-04-15 16:47:55'),
(4, 1, 'login', 'App\\Models\\User', 1, '{\"name\":\"Super Admin\"}', '47.11.115.208', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-15 16:49:45', '2026-04-15 16:49:45'),
(5, 4, 'login', 'App\\Models\\User', 4, '{\"name\":\"KRUPALI SHILU\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1', '2026-04-16 04:14:41', '2026-04-16 04:14:41'),
(6, 4, 'logout', 'App\\Models\\User', 4, '{\"name\":\"KRUPALI SHILU\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1', '2026-04-16 04:14:46', '2026-04-16 04:14:46'),
(7, 5, 'login', 'App\\Models\\User', 5, '{\"name\":\"Denish BDH\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 07:24:18', '2026-04-16 07:24:18'),
(8, 5, 'login', 'App\\Models\\User', 5, '{\"name\":\"Denish BDH\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-16 07:24:31', '2026-04-16 07:24:31'),
(9, 4, 'login', 'App\\Models\\User', 4, '{\"name\":\"KRUPALI SHILU\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1', '2026-04-16 07:42:24', '2026-04-16 07:42:24'),
(10, 3, 'login', 'App\\Models\\User', 3, '{\"name\":\"HARDIK NASIT\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:23:42', '2026-04-16 09:23:42'),
(11, 5, 'login', 'App\\Models\\User', 5, '{\"name\":\"Denish BDH\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 09:24:08', '2026-04-16 09:24:08'),
(12, 21, 'impersonate_start', 'App\\Models\\User', 21, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"RUSHIKA JADAV\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:24:19', '2026-04-16 09:24:19'),
(13, 13, 'login', 'App\\Models\\User', 13, '{\"name\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 09:24:54', '2026-04-16 09:24:54'),
(14, 11, 'login', 'App\\Models\\User', 11, '{\"name\":\"CHIRAG DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:25:20', '2026-04-16 09:25:20'),
(15, 13, 'login', 'App\\Models\\User', 13, '{\"name\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:25:51', '2026-04-16 09:25:51'),
(16, 13, 'create_quotation', 'App\\Models\\Quotation', 2, '{\"customer_name\":\"chirag dholakiya\",\"loan_amount\":20000000,\"filename\":\"Loan_Proposal_chirag_dholakiya_2026-04-16_14_58_14.pdf\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(17, 13, 'auto_assign_stage', 'App\\Models\\StageAssignment', 19, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"document_collection\",\"assigned_to_name\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:30:56', '2026-04-16 09:30:56'),
(18, 13, 'convert_quotation_to_loan', 'App\\Models\\LoanDetail', 2, '{\"quotation_id\":2,\"loan_number\":\"SHF-202604-0002\",\"customer_name\":\"chirag dholakiya\",\"loan_amount\":20000000,\"bank_name\":\"ICICI Bank\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:30:56', '2026-04-16 09:30:56'),
(19, 13, 'update_document_status', 'App\\Models\\LoanDocument', 10, '{\"document_name\":\"Passport Size Photographs Both\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:07', '2026-04-16 09:31:07'),
(20, 13, 'update_document_status', 'App\\Models\\LoanDocument', 11, '{\"document_name\":\"PAN Card Both\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:08', '2026-04-16 09:31:08'),
(21, 13, 'update_document_status', 'App\\Models\\LoanDocument', 13, '{\"document_name\":\"GST Registration Certificate\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:08', '2026-04-16 09:31:08'),
(22, 13, 'update_document_status', 'App\\Models\\LoanDocument', 17, '{\"document_name\":\"Current Loan Statement ( if applicable )\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:10', '2026-04-16 09:31:10'),
(23, 13, 'update_document_status', 'App\\Models\\LoanDocument', 16, '{\"document_name\":\"Bank Statement (Last 12 months)\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:12', '2026-04-16 09:31:12'),
(24, 13, 'update_document_status', 'App\\Models\\LoanDocument', 14, '{\"document_name\":\"Udyam Registration Certificate\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:12', '2026-04-16 09:31:12'),
(25, 13, 'update_document_status', 'App\\Models\\LoanDocument', 12, '{\"document_name\":\"Aadhaar Card Both\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:13', '2026-04-16 09:31:13'),
(26, 13, 'update_document_status', 'App\\Models\\LoanDocument', 15, '{\"document_name\":\"ITR (Last 3 years)\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:14', '2026-04-16 09:31:14'),
(27, 13, 'update_document_status', 'App\\Models\\LoanDocument', 18, '{\"document_name\":\"Property File Xerox\",\"loan_number\":\"SHF-202604-0002\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:15', '2026-04-16 09:31:15'),
(28, 13, 'update_stage_status', 'App\\Models\\StageAssignment', 19, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"document_collection\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:15', '2026-04-16 09:31:15'),
(29, 13, 'update_stage_status', 'App\\Models\\StageAssignment', 21, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"app_number\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:31:42', '2026-04-16 09:31:42'),
(30, 21, 'update_stage_status', 'App\\Models\\StageAssignment', 22, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"bsm_osv\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:32:16', '2026-04-16 09:32:16'),
(31, 13, 'transfer_stage', 'App\\Models\\StageAssignment', 24, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"legal_verification\",\"from_user\":\"MILAN DHOLAKIYA\",\"to_user\":\"RUSHIKA JADAV\",\"reason\":\"Sent to bank for legal verification\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:32:56', '2026-04-16 09:32:56'),
(32, 3, 'login', 'App\\Models\\User', 3, '{\"name\":\"HARDIK NASIT\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:32:58', '2026-04-16 09:32:58'),
(33, 13, 'transfer_stage', 'App\\Models\\StageAssignment', 25, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"technical_valuation\",\"from_user\":\"MILAN DHOLAKIYA\",\"to_user\":\"MANTHAN THUMMAR\",\"reason\":\"Sent for technical valuation\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:33:06', '2026-04-16 09:33:06'),
(34, 23, 'impersonate_start', 'App\\Models\\User', 23, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"MANTHAN THUMMAR\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:33:14', '2026-04-16 09:33:14'),
(35, 23, 'update_stage_status', 'App\\Models\\StageAssignment', 23, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"sanction_decision\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:33:21', '2026-04-16 09:33:21'),
(36, 7, 'login', 'App\\Models\\User', 7, '{\"name\":\"KULDEEP VAISHNAV\"}', '49.36.88.204', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 09:33:46', '2026-04-16 09:33:46'),
(37, 23, 'save_valuation', 'App\\Models\\ValuationDetail', 1, '{\"loan_number\":\"SHF-202604-0002\",\"valuation_type\":\"property\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:34:01', '2026-04-16 09:34:01'),
(38, 23, 'update_stage_status', 'App\\Models\\StageAssignment', 25, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"technical_valuation\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:34:01', '2026-04-16 09:34:01'),
(39, 7, 'logout', 'App\\Models\\User', 7, '{\"name\":\"KULDEEP VAISHNAV\"}', '49.36.88.204', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 09:34:03', '2026-04-16 09:34:03'),
(40, 21, 'transfer_stage', 'App\\Models\\StageAssignment', 24, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"legal_verification\",\"from_user\":\"RUSHIKA JADAV\",\"to_user\":\"MILAN DHOLAKIYA\",\"reason\":\"Legal initiated, transferred back to task owner\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:34:11', '2026-04-16 09:34:11'),
(41, 13, 'update_stage_status', 'App\\Models\\StageAssignment', 24, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"legal_verification\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:34:22', '2026-04-16 09:34:22'),
(42, 13, 'transfer_stage', 'App\\Models\\StageAssignment', 26, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"rate_pf\",\"from_user\":\"MILAN DHOLAKIYA\",\"to_user\":\"RUSHIKA JADAV\",\"reason\":\"Sent for bank rate review\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:35:07', '2026-04-16 09:35:07'),
(43, 21, 'transfer_stage', 'App\\Models\\StageAssignment', 26, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"rate_pf\",\"from_user\":\"RUSHIKA JADAV\",\"to_user\":\"MILAN DHOLAKIYA\",\"reason\":\"Bank reviewed rate details, returned to task owner\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:35:35', '2026-04-16 09:35:35'),
(44, 13, 'update_stage_status', 'App\\Models\\StageAssignment', 26, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"rate_pf\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:35:53', '2026-04-16 09:35:53'),
(45, 13, 'transfer_stage', 'App\\Models\\StageAssignment', 27, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"sanction\",\"from_user\":\"MILAN DHOLAKIYA\",\"to_user\":\"RUSHIKA JADAV\",\"reason\":\"Sent for sanction letter generation\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:36:01', '2026-04-16 09:36:01'),
(46, 21, 'transfer_stage', 'App\\Models\\StageAssignment', 27, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"sanction\",\"from_user\":\"RUSHIKA JADAV\",\"to_user\":\"MILAN DHOLAKIYA\",\"reason\":\"Sanction letter generated\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:36:09', '2026-04-16 09:36:09'),
(47, 13, 'update_stage_status', 'App\\Models\\StageAssignment', 27, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"sanction\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:36:31', '2026-04-16 09:36:31'),
(48, 7, 'login', 'App\\Models\\User', 7, '{\"name\":\"KULDEEP VAISHNAV\"}', '49.36.88.204', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 09:36:34', '2026-04-16 09:36:34'),
(49, 13, 'transfer_stage', 'App\\Models\\StageAssignment', 28, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"docket\",\"from_user\":\"MILAN DHOLAKIYA\",\"to_user\":\"MANTHAN THUMMAR\",\"reason\":\"Sent for docket login\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:36:46', '2026-04-16 09:36:46'),
(50, 23, 'update_stage_status', 'App\\Models\\StageAssignment', 28, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"docket\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:37:24', '2026-04-16 09:37:24'),
(51, 13, 'update_stage_status', 'App\\Models\\StageAssignment', 29, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"kfs\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:37:31', '2026-04-16 09:37:31'),
(52, 13, 'transfer_stage', 'App\\Models\\StageAssignment', 30, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"esign\",\"from_user\":\"MILAN DHOLAKIYA\",\"to_user\":\"RUSHIKA JADAV\",\"reason\":\"Sent for E-Sign & eNACH generation\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:37:40', '2026-04-16 09:37:40'),
(53, 21, 'transfer_stage', 'App\\Models\\StageAssignment', 30, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"esign\",\"from_user\":\"RUSHIKA JADAV\",\"to_user\":\"MILAN DHOLAKIYA\",\"reason\":\"E-Sign & eNACH generated, sent for customer completion\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:37:49', '2026-04-16 09:37:49'),
(54, 13, 'transfer_stage', 'App\\Models\\StageAssignment', 30, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"esign\",\"from_user\":\"MILAN DHOLAKIYA\",\"to_user\":\"RUSHIKA JADAV\",\"reason\":\"E-Sign completed with customer, returned to bank\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:38:02', '2026-04-16 09:38:02'),
(55, 21, 'update_stage_status', 'App\\Models\\StageAssignment', 30, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"esign\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:38:07', '2026-04-16 09:38:07'),
(56, 23, 'update_stage_status', 'App\\Models\\StageAssignment', 31, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"disbursement\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:38:52', '2026-04-16 09:38:52'),
(57, 23, 'process_disbursement', 'App\\Models\\DisbursementDetail', 1, '{\"loan_number\":\"SHF-202604-0002\",\"type\":\"cheque\",\"amount\":20000000}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:38:52', '2026-04-16 09:38:52'),
(58, 23, 'process_disbursement', 'App\\Models\\DisbursementDetail', 1, '{\"loan_number\":\"SHF-202604-0002\",\"type\":\"cheque\",\"amount\":20000000}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 09:39:40', '2026-04-16 09:39:40'),
(59, 13, 'update_stage_status', 'App\\Models\\StageAssignment', 32, '{\"loan_number\":\"SHF-202604-0002\",\"stage_key\":\"otc_clearance\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:39:43', '2026-04-16 09:39:43'),
(60, 13, 'create_task', 'App\\Models\\GeneralTask', 1, '{\"assigned_to\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:41:07', '2026-04-16 09:41:07'),
(61, 13, 'update_task_status', 'App\\Models\\GeneralTask', 1, '{\"from\":\"pending\",\"to\":\"in_progress\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:41:20', '2026-04-16 09:41:20'),
(62, 13, 'update_task', 'App\\Models\\GeneralTask', 1, NULL, '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:41:38', '2026-04-16 09:41:38'),
(63, 5, 'update_task_status', 'App\\Models\\GeneralTask', 1, '{\"from\":\"in_progress\",\"to\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 09:42:04', '2026-04-16 09:42:04'),
(64, 13, 'delete_task', NULL, NULL, '{\"title\":\"od case\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:42:53', '2026-04-16 09:42:53'),
(65, 3, 'impersonate_end', 'App\\Models\\User', 21, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"RUSHIKA JADAV\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:44:55', '2026-04-16 09:44:55'),
(66, 3, 'create_quotation', 'App\\Models\\Quotation', 3, '{\"customer_name\":\"HARDIK NASIT\",\"loan_amount\":200000000,\"filename\":\"Loan_Proposal_HARDIK_NASIT_2026-04-16_15_15_33.pdf\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(67, 5, 'impersonate_start', 'App\\Models\\User', 5, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"Denish BDH\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:54:29', '2026-04-16 09:54:29'),
(68, 5, 'change_loan_status', 'App\\Models\\LoanDetail', 1, '{\"loan_number\":\"SHF-202604-0001\",\"old_status\":\"active\",\"new_status\":\"cancelled\",\"reason\":\"OHK\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:54:49', '2026-04-16 09:54:49'),
(69, 3, 'impersonate_end', 'App\\Models\\User', 5, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"Denish BDH\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:55:00', '2026-04-16 09:55:00'),
(70, 7, 'impersonate_start', 'App\\Models\\User', 7, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"KULDEEP VAISHNAV\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:55:07', '2026-04-16 09:55:07'),
(71, 3, 'impersonate_end', 'App\\Models\\User', 7, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"KULDEEP VAISHNAV\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:55:10', '2026-04-16 09:55:10'),
(72, 3, 'auto_assign_stage', 'App\\Models\\StageAssignment', 35, '{\"loan_number\":\"SHF-202604-0003\",\"stage_key\":\"document_collection\",\"assigned_to_name\":\"HARDIK NASIT\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:56:09', '2026-04-16 09:56:09'),
(73, 3, 'convert_quotation_to_loan', 'App\\Models\\LoanDetail', 3, '{\"quotation_id\":3,\"loan_number\":\"SHF-202604-0003\",\"customer_name\":\"HARDIK NASIT\",\"loan_amount\":200000000,\"bank_name\":\"ICICI Bank\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:56:09', '2026-04-16 09:56:09'),
(74, 3, 'change_loan_status', 'App\\Models\\LoanDetail', 3, '{\"loan_number\":\"SHF-202604-0003\",\"old_status\":\"active\",\"new_status\":\"cancelled\",\"reason\":\"ohk\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:56:16', '2026-04-16 09:56:16'),
(75, 1, 'login', 'App\\Models\\User', 1, '{\"name\":\"Super Admin\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:57:56', '2026-04-16 09:57:56'),
(76, 13, 'impersonate_start', 'App\\Models\\User', 13, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:04', '2026-04-16 09:58:04'),
(77, 11, 'impersonate_start', 'App\\Models\\User', 11, '{\"impersonator\":\"Super Admin\",\"impersonated\":\"CHIRAG DHOLAKIYA\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:04', '2026-04-16 09:58:04'),
(78, 3, 'impersonate_end', 'App\\Models\\User', 13, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:12', '2026-04-16 09:58:12'),
(79, 1, 'impersonate_end', 'App\\Models\\User', 11, '{\"impersonator\":\"Super Admin\",\"impersonated\":\"CHIRAG DHOLAKIYA\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:20', '2026-04-16 09:58:20'),
(80, 11, 'impersonate_start', 'App\\Models\\User', 11, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"CHIRAG DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:21', '2026-04-16 09:58:21'),
(81, 3, 'impersonate_end', 'App\\Models\\User', 11, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"CHIRAG DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:24', '2026-04-16 09:58:24'),
(82, 13, 'impersonate_start', 'App\\Models\\User', 13, '{\"impersonator\":\"Super Admin\",\"impersonated\":\"MILAN DHOLAKIYA\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:27', '2026-04-16 09:58:27'),
(83, 13, 'impersonate_start', 'App\\Models\\User', 13, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:32', '2026-04-16 09:58:32'),
(84, 1, 'impersonate_end', 'App\\Models\\User', 13, '{\"impersonator\":\"Super Admin\",\"impersonated\":\"MILAN DHOLAKIYA\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:58:53', '2026-04-16 09:58:53'),
(85, 3, 'impersonate_end', 'App\\Models\\User', 13, '{\"impersonator\":\"HARDIK NASIT\",\"impersonated\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:59:02', '2026-04-16 09:59:02'),
(86, 3, 'create_quotation', 'App\\Models\\Quotation', 4, '{\"customer_name\":\"hardik nasit\",\"loan_amount\":4500000,\"filename\":\"Loan_Proposal_hardik_nasit_2026-04-16_15_29_41.pdf\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(87, 1, 'permissions_updated', NULL, NULL, '{\"roles\":[\"admin\",\"branch_manager\",\"bdh\",\"loan_advisor\",\"bank_employee\",\"office_employee\"]}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 10:07:48', '2026-04-16 10:07:48'),
(88, 3, 'impersonate_start', 'App\\Models\\User', 3, '{\"impersonator\":\"Super Admin\",\"impersonated\":\"HARDIK NASIT\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 10:07:59', '2026-04-16 10:07:59'),
(89, 1, 'impersonate_end', 'App\\Models\\User', 3, '{\"impersonator\":\"Super Admin\",\"impersonated\":\"HARDIK NASIT\"}', '43.242.117.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 10:08:50', '2026-04-16 10:08:50'),
(90, 3, 'reject_loan', 'App\\Models\\LoanDetail', 3, '{\"loan_number\":\"SHF-202604-0003\",\"rejected_stage\":\"document_collection\",\"reason\":\"ohk\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 10:10:15', '2026-04-16 10:10:15'),
(91, 4, 'login', 'App\\Models\\User', 4, '{\"name\":\"KRUPALI SHILU\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; U; Android 14; en-in; CPH2359 Build/UKQ1.230924.001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.5970.168 Mobile Safari/537.36 HeyTapBrowser/45.14.0.1', '2026-04-16 12:52:09', '2026-04-16 12:52:09'),
(92, 18, 'login', 'App\\Models\\User', 18, '{\"name\":\"BHARGAV VIRANI\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', '2026-04-16 13:01:56', '2026-04-16 13:01:56'),
(93, 23, 'login', 'App\\Models\\User', 23, '{\"name\":\"MANTHAN THUMMAR\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-04-16 13:01:57', '2026-04-16 13:01:57'),
(94, 23, 'login', 'App\\Models\\User', 23, '{\"name\":\"MANTHAN THUMMAR\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-04-16 13:01:57', '2026-04-16 13:01:57'),
(95, 6, 'login', 'App\\Models\\User', 6, '{\"name\":\"JAYDEEP THESHIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:02:34', '2026-04-16 13:02:34'),
(96, 6, 'login', 'App\\Models\\User', 6, '{\"name\":\"JAYDEEP THESHIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 13:03:55', '2026-04-16 13:03:55'),
(97, 12, 'login', 'App\\Models\\User', 12, '{\"name\":\"DAXIT MALAVIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', '2026-04-16 13:04:02', '2026-04-16 13:04:02'),
(98, 8, 'login', 'App\\Models\\User', 8, '{\"name\":\"RAHUL MARAKANA\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', '2026-04-16 13:04:27', '2026-04-16 13:04:27'),
(99, 9, 'login', 'App\\Models\\User', 9, '{\"name\":\"DIPAK VIRANI\"}', '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', '2026-04-16 13:05:17', '2026-04-16 13:05:17'),
(100, 13, 'logout', 'App\\Models\\User', 13, '{\"name\":\"MILAN DHOLAKIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:05:28', '2026-04-16 13:05:28'),
(101, 21, 'login', 'App\\Models\\User', 21, '{\"name\":\"RUSHIKA JADAV\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:05:45', '2026-04-16 13:05:45'),
(102, 6, 'create_quotation', 'App\\Models\\Quotation', 5, '{\"customer_name\":\"THESIYA JAYDEEP\",\"loan_amount\":45000000,\"filename\":\"Loan_Proposal_THESIYA_JAYDEEP_2026-04-16_18_37_14.pdf\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(103, 6, 'update_document_status', 'App\\Models\\LoanDocument', 1, '{\"document_name\":\"Passport Size Photographs Both\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:07:53', '2026-04-16 13:07:53'),
(104, 6, 'update_document_status', 'App\\Models\\LoanDocument', 3, '{\"document_name\":\"Aadhaar Card Both\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:07:55', '2026-04-16 13:07:55'),
(105, 6, 'update_document_status', 'App\\Models\\LoanDocument', 5, '{\"document_name\":\"Udyam Registration Certificate\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:07:56', '2026-04-16 13:07:56'),
(106, 6, 'update_document_status', 'App\\Models\\LoanDocument', 7, '{\"document_name\":\"Bank Statement (Last 12 months)\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:07:57', '2026-04-16 13:07:57'),
(107, 6, 'update_document_status', 'App\\Models\\LoanDocument', 9, '{\"document_name\":\"Property File Xerox\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:07:59', '2026-04-16 13:07:59'),
(108, 6, 'update_document_status', 'App\\Models\\LoanDocument', 2, '{\"document_name\":\"PAN Card Both\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:08:00', '2026-04-16 13:08:00'),
(109, 6, 'update_document_status', 'App\\Models\\LoanDocument', 4, '{\"document_name\":\"GST Registration Certificate\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:08:01', '2026-04-16 13:08:01'),
(110, 6, 'update_document_status', 'App\\Models\\LoanDocument', 6, '{\"document_name\":\"ITR (Last 3 years)\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:08:03', '2026-04-16 13:08:03'),
(111, 6, 'update_document_status', 'App\\Models\\LoanDocument', 8, '{\"document_name\":\"Current Loan Statement ( if applicable )\",\"loan_number\":\"SHF-202604-0001\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:08:04', '2026-04-16 13:08:04'),
(112, 6, 'update_stage_status', 'App\\Models\\StageAssignment', 3, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"document_collection\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:08:04', '2026-04-16 13:08:04'),
(113, 6, 'update_stage_status', 'App\\Models\\StageAssignment', 5, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"app_number\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:09:20', '2026-04-16 13:09:20'),
(114, 21, 'update_stage_status', 'App\\Models\\StageAssignment', 6, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"bsm_osv\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:09:48', '2026-04-16 13:09:48'),
(115, 23, 'update_stage_status', 'App\\Models\\StageAssignment', 7, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"sanction_decision\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-04-16 13:10:23', '2026-04-16 13:10:23'),
(116, 6, 'transfer_stage', 'App\\Models\\StageAssignment', 8, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"legal_verification\",\"from_user\":\"JAYDEEP THESHIYA\",\"to_user\":\"RUSHIKA JADAV\",\"reason\":\"Sent to bank for legal verification\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:10:38', '2026-04-16 13:10:38'),
(117, 21, 'transfer_stage', 'App\\Models\\StageAssignment', 8, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"legal_verification\",\"from_user\":\"RUSHIKA JADAV\",\"to_user\":\"JAYDEEP THESHIYA\",\"reason\":\"Legal initiated, transferred back to task owner\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:10:44', '2026-04-16 13:10:44'),
(118, 6, 'transfer_stage', 'App\\Models\\StageAssignment', 9, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"technical_valuation\",\"from_user\":\"JAYDEEP THESHIYA\",\"to_user\":\"MANTHAN THUMMAR\",\"reason\":\"Sent for technical valuation\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:10:55', '2026-04-16 13:10:55'),
(119, 6, 'update_stage_status', 'App\\Models\\StageAssignment', 8, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"legal_verification\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:12:08', '2026-04-16 13:12:08'),
(120, 23, 'login', 'App\\Models\\User', 23, '{\"name\":\"MANTHAN THUMMAR\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:13:47', '2026-04-16 13:13:47'),
(121, 6, 'logout', 'App\\Models\\User', 6, '{\"name\":\"JAYDEEP THESHIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:18:47', '2026-04-16 13:18:47'),
(122, 5, 'login', 'App\\Models\\User', 5, '{\"name\":\"Denish BDH\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:19:02', '2026-04-16 13:19:02'),
(123, 23, 'raise_query', 'App\\Models\\StageQuery', 1, '{\"loan_number\":\"SHF-202604-0001\",\"stage_key\":\"technical_valuation\",\"preview\":\"Hdiifvd\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-04-16 13:20:57', '2026-04-16 13:20:57'),
(124, 6, 'impersonate_start', 'App\\Models\\User', 6, '{\"impersonator\":\"Denish BDH\",\"impersonated\":\"JAYDEEP THESHIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:21:11', '2026-04-16 13:21:11'),
(125, 6, 'create_quotation', 'App\\Models\\Quotation', 6, '{\"customer_name\":\"DENISH MALAVIYA\",\"loan_amount\":10000000,\"filename\":\"Loan_Proposal_DENISH_MALAVIYA_2026-04-16_18_52_16.pdf\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(126, 6, 'auto_assign_stage', 'App\\Models\\StageAssignment', 51, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"document_collection\",\"assigned_to_name\":\"JAYDEEP THESHIYA\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:07', '2026-04-16 13:23:07'),
(127, 6, 'convert_quotation_to_loan', 'App\\Models\\LoanDetail', 4, '{\"quotation_id\":6,\"loan_number\":\"SHF-202604-0004\",\"customer_name\":\"DENISH MALAVIYA\",\"loan_amount\":10000000,\"bank_name\":\"ICICI Bank\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:07', '2026-04-16 13:23:07'),
(128, 6, 'update_document_status', 'App\\Models\\LoanDocument', 28, '{\"document_name\":\"Passport Size Photographs Both\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:13', '2026-04-16 13:23:13'),
(129, 6, 'update_document_status', 'App\\Models\\LoanDocument', 30, '{\"document_name\":\"Aadhaar Card Both\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:14', '2026-04-16 13:23:14'),
(130, 6, 'update_document_status', 'App\\Models\\LoanDocument', 32, '{\"document_name\":\"Udyam Registration Certificate\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:15', '2026-04-16 13:23:15'),
(131, 6, 'update_document_status', 'App\\Models\\LoanDocument', 34, '{\"document_name\":\"Bank Statement (Last 12 months)\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:17', '2026-04-16 13:23:17'),
(132, 6, 'update_document_status', 'App\\Models\\LoanDocument', 36, '{\"document_name\":\"Property File Xerox\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:18', '2026-04-16 13:23:18'),
(133, 6, 'update_document_status', 'App\\Models\\LoanDocument', 29, '{\"document_name\":\"PAN Card Both\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:20', '2026-04-16 13:23:20'),
(134, 6, 'update_document_status', 'App\\Models\\LoanDocument', 31, '{\"document_name\":\"GST Registration Certificate\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:21', '2026-04-16 13:23:21'),
(135, 6, 'update_document_status', 'App\\Models\\LoanDocument', 35, '{\"document_name\":\"Current Loan Statement ( if applicable )\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:22', '2026-04-16 13:23:22'),
(136, 6, 'update_document_status', 'App\\Models\\LoanDocument', 33, '{\"document_name\":\"ITR (Last 3 years)\",\"loan_number\":\"SHF-202604-0004\",\"new_status\":\"received\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:22', '2026-04-16 13:23:22'),
(137, 6, 'update_stage_status', 'App\\Models\\StageAssignment', 51, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"document_collection\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:22', '2026-04-16 13:23:22'),
(138, 6, 'update_stage_status', 'App\\Models\\StageAssignment', 53, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"app_number\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:23:34', '2026-04-16 13:23:34'),
(139, 21, 'update_stage_status', 'App\\Models\\StageAssignment', 54, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"bsm_osv\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:23:49', '2026-04-16 13:23:49'),
(140, 23, 'update_stage_status', 'App\\Models\\StageAssignment', 55, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"sanction_decision\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2026-04-16 13:23:59', '2026-04-16 13:23:59'),
(141, 6, 'transfer_stage', 'App\\Models\\StageAssignment', 56, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"legal_verification\",\"from_user\":\"JAYDEEP THESHIYA\",\"to_user\":\"RUSHIKA JADAV\",\"reason\":\"Sent to bank for legal verification\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:24:03', '2026-04-16 13:24:03'),
(142, 6, 'transfer_stage', 'App\\Models\\StageAssignment', 57, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"technical_valuation\",\"from_user\":\"JAYDEEP THESHIYA\",\"to_user\":\"MANTHAN THUMMAR\",\"reason\":\"Sent for technical valuation\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:24:07', '2026-04-16 13:24:07'),
(143, 21, 'transfer_stage', 'App\\Models\\StageAssignment', 56, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"legal_verification\",\"from_user\":\"RUSHIKA JADAV\",\"to_user\":\"JAYDEEP THESHIYA\",\"reason\":\"Legal initiated, transferred back to task owner\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:24:09', '2026-04-16 13:24:09'),
(144, 6, 'update_stage_status', 'App\\Models\\StageAssignment', 56, '{\"loan_number\":\"SHF-202604-0004\",\"stage_key\":\"legal_verification\",\"old_status\":\"in_progress\",\"new_status\":\"completed\"}', '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', '2026-04-16 13:24:18', '2026-04-16 13:24:18');

-- --------------------------------------------------------

--
-- Table structure for table `app_config`
--

DROP TABLE IF EXISTS `app_config`;
CREATE TABLE `app_config` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `config_key` varchar(255) NOT NULL,
  `config_json` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `app_config`
--

TRUNCATE TABLE `app_config`;
--
-- Dumping data for table `app_config`
--

INSERT INTO `app_config` (`id`, `config_key`, `config_json`, `created_at`, `updated_at`) VALUES
(1, 'main', '{\"companyName\":\"Shreenathji Home Finance\",\"companyAddress\":\"OFFICE NO 911, R K PRIME, CIRCLE, next to SILVER HEIGHT, Nehru Nagar Co operative Society, Nana Mava, Rajkot, Gujarat 360004\",\"companyPhone\":\"+91 99747 89089\",\"companyEmail\":\"info@shf.com\",\"banks\":[\"HDFC Bank\",\"ICICI Bank\",\"Axis Bank\",\"Kotak Mahindra Bank\"],\"iomCharges\":{\"thresholdAmount\":10000000,\"fixedCharge\":7000,\"percentageAbove\":0.35},\"tenures\":[5,10,15,20],\"documents_en\":{\"proprietor\":[\"Passport Size Photographs Both\",\"PAN Card Both\",\"Aadhaar Card Both\",\"GST Registration Certificate\",\"Udyam Registration Certificate\",\"ITR (Last 3 years)\",\"Bank Statement (Last 12 months)\",\"Current Loan Statement ( if applicable )\",\"Property File Xerox\"],\"partnership_llp\":[\"PAN Card of Firm\",\"Partnership Deed\",\"GST Registration Certificate\",\"ITR With Audit of Firm (Last 3 years)\",\"Firm Current A\\/c Bank Statement (Last 12 months)\",\"Current Loan Statement ( if applicable )\",\"Passport Size Photographs of All Partners\",\"PAN Card of All Partners\",\"Aadhaar Card of All Partners\",\"ITR of Partners (Last 3 years)\",\"Bank Statement of Partners (Last 12 months)\"],\"pvt_ltd\":[\"PAN Card of Company\",\"Memorandum of Association (MOA)\",\"Articles of Association (AOA)\",\"GST Registration Certificate\",\"ITR With Audit Report of Company (Last 3 years)\",\"Current Loan Statement ( if applicable )\",\"Company Current A\\/C Statement (Last 12 months)\",\"Passport Size Photographs of All Director\",\"PAN Card of All Directors\",\"Aadhaar Card of All Directors\",\"ITR of Directors (Last 3 years)\",\"Bank Statement of Directors (Last 12 months)\"],\"salaried\":[\"Passport Size Photographs Both\",\"PAN Card Both\",\"Aadhaar Card Both\",\"Salary Slips (Last 6 months)\",\"ITR (Last 2 years)\",\"Form 16 (Last 2 years)\",\"Bank Statement (Last 6 months)\",\"Property Documents (if applicable)\"]},\"documents_gu\":{\"proprietor\":[\"Passport Size Photographs Both\",\"PAN Card Both\",\"Aadhaar Card Both\",\"GST Registration Certificate\",\"Udyam Registration Certificate\",\"ITR (Last 3 years)\",\"Bank Statement (Last 12 months)\",\"Current Loan Statement (if applicable)\",\"Property File Xerox\"],\"partnership_llp\":[\"PAN Card of Firm\",\"Partnership Deed\",\"GST Registration Certificate\",\"ITR With Audit of Firm (Last 3 years)\",\"Firm Current A\\/c Bank Statement (Last 12 months)\",\"Current Loan Statement (if applicable)\",\"Passport Size Photographs of All Partners\",\"PAN Card of All Partners\",\"Aadhaar Card of All Partners\",\"ITR of Partners (Last 3 years)\",\"Bank Statement of Partners (Last 12 months)\"],\"pvt_ltd\":[\"PAN Card of Company\",\"Memorandum of Association (MOA)\",\"Articles of Association (AOA)\",\"GST Registration Certificate\",\"ITR With Audit Report of Company (Last 3 years)\",\"Current Loan Statement (if applicable)\",\"Company Current A\\/C Statement (Last 12 months)\",\"Passport Size Photographs of All Director\",\"PAN Card of All Directors\",\"Aadhaar Card of All Directors\",\"ITR of Directors (Last 3 years)\",\"Bank Statement of Directors (Last 12 months)\"],\"salaried\":[\"Passport Size Photographs Both\",\"PAN Card Both\",\"Aadhaar Card Both\",\"Salary Slips (Last 6 months)\",\"ITR (Last 2 years)\",\"Form 16 (Last 2 years)\",\"Bank Statement (Last 6 months)\",\"Property Documents (if applicable)\"]},\"gstPercent\":18,\"ourServices\":\"Home Loan, Mortgage Loan, Commercial Loan, Industrial Loan,Land Loan, Over Draft(OD)\"}', '2026-02-27 10:29:58', '2026-04-15 16:46:36');

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
CREATE TABLE `app_settings` (
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `app_settings`
--

TRUNCATE TABLE `app_settings`;
--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('additional_notes', 'Loan amount may vary based on bank\'s visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required', '2026-04-16 13:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

DROP TABLE IF EXISTS `banks`;
CREATE TABLE `banks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `banks`
--

TRUNCATE TABLE `banks`;
--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(1, 'HDFC Bank', 'HDFC', 1, '2026-04-06 09:54:26', '2026-04-06 09:54:26', NULL, NULL, NULL),
(2, 'ICICI Bank', 'ICICI', 1, '2026-04-06 09:54:26', '2026-04-06 09:54:26', NULL, NULL, NULL),
(3, 'Axis Bank', 'AXIS', 1, '2026-04-06 09:54:26', '2026-04-06 09:54:26', NULL, NULL, NULL),
(4, 'Kotak Mahindra Bank', 'KOTAK', 1, '2026-04-06 09:54:26', '2026-04-06 09:54:26', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bank_charges`
--

DROP TABLE IF EXISTS `bank_charges`;
CREATE TABLE `bank_charges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `pf` decimal(5,2) NOT NULL DEFAULT 0.00,
  `admin` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `stamp_notary` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `registration_fee` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `advocate` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `tc` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `extra1_name` varchar(255) DEFAULT NULL,
  `extra1_amt` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `extra2_name` varchar(255) DEFAULT NULL,
  `extra2_amt` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `bank_charges`
--

TRUNCATE TABLE `bank_charges`;
--
-- Dumping data for table `bank_charges`
--

INSERT INTO `bank_charges` (`id`, `bank_name`, `pf`, `admin`, `stamp_notary`, `registration_fee`, `advocate`, `tc`, `extra1_name`, `extra1_amt`, `extra2_name`, `extra2_amt`, `created_at`, `updated_at`) VALUES
(1, 'Axis Bank', '0.50', 0, 2500, 5900, 1000, 4500, NULL, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(2, 'HDFC Bank', '0.60', 0, 1500, 5900, 2500, 0, NULL, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(3, 'ICICI Bank', '0.15', 0, 600, 5900, 3000, 2500, NULL, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-16 13:07:16'),
(4, 'Kotak Mahindra Bank', '0.50', 0, 2500, 5900, 2500, 0, NULL, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `bank_employees`
--

DROP TABLE IF EXISTS `bank_employees`;
CREATE TABLE `bank_employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `bank_employees`
--

TRUNCATE TABLE `bank_employees`;
--
-- Dumping data for table `bank_employees`
--

INSERT INTO `bank_employees` (`id`, `bank_id`, `user_id`, `is_default`, `location_id`, `created_at`, `updated_at`) VALUES
(1, 3, 15, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(2, 3, 16, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(3, 3, 17, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(4, 3, 18, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(5, 1, 19, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(6, 1, 20, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(7, 2, 21, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(8, 2, 22, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(9, 2, 23, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(10, 4, 24, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(11, 4, 25, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(12, 1, 26, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(13, 4, 26, 0, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(14, 1, 19, 1, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(15, 2, 21, 1, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(16, 3, 15, 1, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(17, 4, 24, 1, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `bank_location`
--

DROP TABLE IF EXISTS `bank_location`;
CREATE TABLE `bank_location` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `bank_location`
--

TRUNCATE TABLE `bank_location`;
--
-- Dumping data for table `bank_location`
--

INSERT INTO `bank_location` (`id`, `bank_id`, `location_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(2, 2, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(3, 3, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(4, 4, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `branches`
--

TRUNCATE TABLE `branches`;
--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `code`, `address`, `city`, `phone`, `is_active`, `manager_id`, `location_id`, `created_at`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(1, 'Rajkot Main Office', 'RJK-MAIN', 'OFFICE NO 911, R K PRIME, CIRCLE, next to SILVER HEIGHT, Nehru Nagar Co operative Society, Nana Mava, Rajkot, Gujarat 360004', 'Rajkot', '+91 99747 89089', 1, 2, 2, '2026-04-06 09:54:26', '2026-04-07 17:52:31', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `cache`
--

TRUNCATE TABLE `cache`;
--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('shf-world-cache-advisor_eligible_roles', 'a:3:{i:0;s:14:\"branch_manager\";i:1;s:3:\"bdh\";i:2;s:12:\"loan_advisor\";}', 1776345854),
('shf-world-cache-daxit@shfworld.com|103.240.208.228', 'i:1;', 1776344678),
('shf-world-cache-daxit@shfworld.com|103.240.208.228:timer', 'i:1776344678;', 1776344678),
('shf-world-cache-denish@shfworld.com|43.242.117.89', 'i:1;', 1776333534),
('shf-world-cache-denish@shfworld.com|43.242.117.89:timer', 'i:1776333534;', 1776333534),
('shf-world-cache-role_perms:3', 'a:30:{i:0;s:16:\"manage_customers\";i:1;s:14:\"view_customers\";i:2;s:14:\"view_dashboard\";i:3;s:20:\"manage_notifications\";i:4;s:20:\"transfer_loan_stages\";i:5;s:11:\"reject_loan\";i:6;s:18:\"change_loan_status\";i:7;s:18:\"view_loan_timeline\";i:8;s:19:\"manage_disbursement\";i:9;s:16:\"manage_valuation\";i:10;s:11:\"raise_query\";i:11;s:13:\"resolve_query\";i:12;s:20:\"download_pdf_branded\";i:13;s:18:\"download_pdf_plain\";i:14;s:16:\"create_quotation\";i:15;s:12:\"generate_pdf\";i:16;s:19:\"view_own_quotations\";i:17;s:19:\"view_all_quotations\";i:18;s:12:\"download_pdf\";i:19;s:10:\"view_users\";i:20;s:19:\"change_own_password\";i:21;s:17:\"view_activity_log\";i:22;s:15:\"convert_to_loan\";i:23;s:10:\"view_loans\";i:24;s:14:\"view_all_loans\";i:25;s:11:\"create_loan\";i:26;s:9:\"edit_loan\";i:27;s:21:\"manage_loan_documents\";i:28;s:18:\"manage_loan_stages\";i:29;s:11:\"add_remarks\";}', 1776346128),
('shf-world-cache-role_perms:4', 'a:30:{i:0;s:16:\"manage_customers\";i:1;s:14:\"view_customers\";i:2;s:14:\"view_dashboard\";i:3;s:20:\"manage_notifications\";i:4;s:20:\"transfer_loan_stages\";i:5;s:11:\"reject_loan\";i:6;s:18:\"change_loan_status\";i:7;s:18:\"view_loan_timeline\";i:8;s:19:\"manage_disbursement\";i:9;s:16:\"manage_valuation\";i:10;s:11:\"raise_query\";i:11;s:13:\"resolve_query\";i:12;s:20:\"download_pdf_branded\";i:13;s:18:\"download_pdf_plain\";i:14;s:16:\"create_quotation\";i:15;s:12:\"generate_pdf\";i:16;s:19:\"view_own_quotations\";i:17;s:19:\"view_all_quotations\";i:18;s:12:\"download_pdf\";i:19;s:10:\"view_users\";i:20;s:19:\"change_own_password\";i:21;s:17:\"view_activity_log\";i:22;s:15:\"convert_to_loan\";i:23;s:10:\"view_loans\";i:24;s:14:\"view_all_loans\";i:25;s:11:\"create_loan\";i:26;s:9:\"edit_loan\";i:27;s:21:\"manage_loan_documents\";i:28;s:18:\"manage_loan_stages\";i:29;s:11:\"add_remarks\";}', 1776345842),
('shf-world-cache-role_perms:5', 'a:24:{i:0;s:16:\"manage_customers\";i:1;s:14:\"view_customers\";i:2;s:14:\"view_dashboard\";i:3;s:20:\"manage_notifications\";i:4;s:20:\"transfer_loan_stages\";i:5;s:18:\"change_loan_status\";i:6;s:18:\"view_loan_timeline\";i:7;s:19:\"manage_disbursement\";i:8;s:11:\"raise_query\";i:9;s:13:\"resolve_query\";i:10;s:20:\"download_pdf_branded\";i:11;s:18:\"download_pdf_plain\";i:12;s:16:\"create_quotation\";i:13;s:12:\"generate_pdf\";i:14;s:19:\"view_own_quotations\";i:15;s:12:\"download_pdf\";i:16;s:19:\"change_own_password\";i:17;s:15:\"convert_to_loan\";i:18;s:10:\"view_loans\";i:19;s:11:\"create_loan\";i:20;s:9:\"edit_loan\";i:21;s:21:\"manage_loan_documents\";i:22;s:18:\"manage_loan_stages\";i:23;s:11:\"add_remarks\";}', 1776346046),
('shf-world-cache-role_perms:6', 'a:9:{i:0;s:14:\"view_customers\";i:1;s:14:\"view_dashboard\";i:2;s:20:\"manage_notifications\";i:3;s:18:\"view_loan_timeline\";i:4;s:11:\"raise_query\";i:5;s:19:\"change_own_password\";i:6;s:10:\"view_loans\";i:7;s:18:\"manage_loan_stages\";i:8;s:11:\"add_remarks\";}', 1776346123),
('shf-world-cache-role_perms:7', 'a:13:{i:0;s:14:\"view_customers\";i:1;s:14:\"view_dashboard\";i:2;s:20:\"manage_notifications\";i:3;s:20:\"transfer_loan_stages\";i:4;s:18:\"view_loan_timeline\";i:5;s:16:\"manage_valuation\";i:6;s:11:\"raise_query\";i:7;s:19:\"change_own_password\";i:8;s:10:\"view_loans\";i:9;s:9:\"edit_loan\";i:10;s:21:\"manage_loan_documents\";i:11;s:18:\"manage_loan_stages\";i:12;s:11:\"add_remarks\";}', 1776345953),
('shf-world-cache-user_perms:12', 'a:0:{}', 1776346091),
('shf-world-cache-user_perms:13', 'a:0:{}', 1776344810),
('shf-world-cache-user_perms:18', 'a:0:{}', 1776344816),
('shf-world-cache-user_perms:21', 'a:0:{}', 1776346123),
('shf-world-cache-user_perms:23', 'a:0:{}', 1776345953),
('shf-world-cache-user_perms:3', 'a:0:{}', 1776334379),
('shf-world-cache-user_perms:4', 'a:0:{}', 1776346128),
('shf-world-cache-user_perms:5', 'a:0:{}', 1776345842),
('shf-world-cache-user_perms:6', 'a:0:{}', 1776345972),
('shf-world-cache-user_perms:8', 'a:0:{}', 1776344967),
('shf-world-cache-user_perms:9', 'a:0:{}', 1776345457),
('shf-world-cache-user_role_ids:12', 'a:1:{i:0;i:5;}', 1776346091),
('shf-world-cache-user_role_ids:13', 'a:1:{i:0;i:5;}', 1776344810),
('shf-world-cache-user_role_ids:18', 'a:1:{i:0;i:7;}', 1776344816),
('shf-world-cache-user_role_ids:21', 'a:1:{i:0;i:6;}', 1776346123),
('shf-world-cache-user_role_ids:23', 'a:1:{i:0;i:7;}', 1776345953),
('shf-world-cache-user_role_ids:3', 'a:1:{i:0;i:3;}', 1776334379),
('shf-world-cache-user_role_ids:4', 'a:1:{i:0;i:3;}', 1776346128),
('shf-world-cache-user_role_ids:5', 'a:1:{i:0;i:4;}', 1776345842),
('shf-world-cache-user_role_ids:6', 'a:1:{i:0;i:5;}', 1776345972),
('shf-world-cache-user_role_ids:8', 'a:1:{i:0;i:5;}', 1776344967),
('shf-world-cache-user_role_ids:9', 'a:1:{i:0;i:5;}', 1776345457);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `cache_locks`
--

TRUNCATE TABLE `cache_locks`;
-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `customers`
--

TRUNCATE TABLE `customers`;
--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_name`, `mobile`, `email`, `date_of_birth`, `pan_number`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Vipul Parsana', '9510717999', NULL, '1990-01-15', 'AODPP1247F', 1, 1, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', NULL),
(2, 'chirag dholakiya', '9016345138', 'chirhb@gmail.com', '1988-05-15', 'GGFYG5125G', 13, 13, NULL, '2026-04-16 09:30:56', '2026-04-16 09:30:56', NULL),
(3, 'HARDIK NASIT', '5321330', 'SDFASDFASDF@gmail.com', '2026-04-16', 'ASBAS2333S', 3, 3, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', NULL),
(4, 'DENISH MALAVIYA', '9974789089', 'denish.malaviya@gmail.com', '1988-11-29', 'BFBPM7332E', 6, 6, NULL, '2026-04-16 13:23:06', '2026-04-16 13:23:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `daily_visit_reports`
--

DROP TABLE IF EXISTS `daily_visit_reports`;
CREATE TABLE `daily_visit_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `visit_date` date NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_type` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `outcome` text DEFAULT NULL,
  `follow_up_needed` tinyint(1) NOT NULL DEFAULT 0,
  `follow_up_date` date DEFAULT NULL,
  `follow_up_notes` text DEFAULT NULL,
  `is_follow_up_done` tinyint(1) NOT NULL DEFAULT 0,
  `parent_visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `follow_up_visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quotation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `daily_visit_reports`
--

TRUNCATE TABLE `daily_visit_reports`;
-- --------------------------------------------------------

--
-- Table structure for table `disbursement_details`
--

DROP TABLE IF EXISTS `disbursement_details`;
CREATE TABLE `disbursement_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `disbursement_type` varchar(255) NOT NULL,
  `disbursement_date` date DEFAULT NULL,
  `amount_disbursed` bigint(20) UNSIGNED DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `ifsc_code` varchar(255) DEFAULT NULL,
  `cheque_number` varchar(255) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `cheques` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `dd_number` varchar(255) DEFAULT NULL,
  `dd_date` date DEFAULT NULL,
  `is_otc` tinyint(1) NOT NULL DEFAULT 0,
  `otc_branch` varchar(255) DEFAULT NULL,
  `otc_cleared` tinyint(1) NOT NULL DEFAULT 0,
  `otc_cleared_date` date DEFAULT NULL,
  `otc_cleared_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `disbursement_details`
--

TRUNCATE TABLE `disbursement_details`;
--
-- Dumping data for table `disbursement_details`
--

INSERT INTO `disbursement_details` (`id`, `loan_id`, `disbursement_type`, `disbursement_date`, `amount_disbursed`, `bank_account_number`, `ifsc_code`, `cheque_number`, `cheque_date`, `cheques`, `dd_number`, `dd_date`, `is_otc`, `otc_branch`, `otc_cleared`, `otc_cleared_date`, `otc_cleared_by`, `reference_number`, `notes`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 2, 'cheque', '2026-04-16', 20000000, 'Bshajann', NULL, NULL, NULL, '[{\"cheque_name\":\"HajNNnbs\",\"cheque_number\":\"5487976\",\"cheque_date\":\"16\\/04\\/2026\",\"cheque_amount\":\"20000000\"}]', NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, '2026-04-16 09:38:52', '2026-04-16 09:38:52', 23);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `failed_jobs`
--

TRUNCATE TABLE `failed_jobs`;
-- --------------------------------------------------------

--
-- Table structure for table `general_tasks`
--

DROP TABLE IF EXISTS `general_tasks`;
CREATE TABLE `general_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `loan_detail_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `priority` varchar(255) NOT NULL DEFAULT 'normal',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `general_tasks`
--

TRUNCATE TABLE `general_tasks`;
-- --------------------------------------------------------

--
-- Table structure for table `general_task_comments`
--

DROP TABLE IF EXISTS `general_task_comments`;
CREATE TABLE `general_task_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `general_task_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `general_task_comments`
--

TRUNCATE TABLE `general_task_comments`;
-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `jobs`
--

TRUNCATE TABLE `jobs`;
-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `job_batches`
--

TRUNCATE TABLE `job_batches`;
-- --------------------------------------------------------

--
-- Table structure for table `loan_details`
--

DROP TABLE IF EXISTS `loan_details`;
CREATE TABLE `loan_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_number` varchar(255) NOT NULL,
  `quotation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_type` varchar(255) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `pan_number` varchar(10) DEFAULT NULL,
  `loan_amount` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `is_sanctioned` tinyint(1) NOT NULL DEFAULT 0,
  `current_stage` varchar(255) NOT NULL DEFAULT 'inquiry',
  `bank_name` varchar(255) DEFAULT NULL,
  `roi_min` decimal(5,2) DEFAULT NULL,
  `roi_max` decimal(5,2) DEFAULT NULL,
  `total_charges` varchar(255) DEFAULT NULL,
  `application_number` varchar(255) DEFAULT NULL,
  `assigned_bank_employee` bigint(20) UNSIGNED DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `expected_docket_date` date DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_stage` varchar(255) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `status_reason` text DEFAULT NULL,
  `status_changed_at` timestamp NULL DEFAULT NULL,
  `status_changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_advisor` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `loan_details`
--

TRUNCATE TABLE `loan_details`;
--
-- Dumping data for table `loan_details`
--

INSERT INTO `loan_details` (`id`, `loan_number`, `quotation_id`, `customer_id`, `branch_id`, `location_id`, `bank_id`, `product_id`, `customer_name`, `customer_type`, `customer_phone`, `customer_email`, `date_of_birth`, `pan_number`, `loan_amount`, `status`, `is_sanctioned`, `current_stage`, `bank_name`, `roi_min`, `roi_max`, `total_charges`, `application_number`, `assigned_bank_employee`, `due_date`, `expected_docket_date`, `rejected_at`, `rejected_by`, `rejected_stage`, `rejection_reason`, `status_reason`, `status_changed_at`, `status_changed_by`, `created_by`, `assigned_advisor`, `notes`, `created_at`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(1, 'SHF-202604-0001', 1, 1, 1, NULL, 2, 1, 'Vipul Parsana', 'proprietor', '9510717999', NULL, '1990-01-15', 'AODPP1247F', 5000000, 'cancelled', 1, 'parallel_processing', 'ICICI Bank', '9.00', '9.15', '4500', '123123123', NULL, '2026-04-22', NULL, NULL, NULL, NULL, NULL, 'OHK', '2026-04-16 09:54:49', 5, 1, 6, 'Sample loan created by seeder', '2026-04-15 16:46:37', '2026-04-16 13:12:08', 6, NULL, NULL),
(2, 'SHF-202604-0002', 2, 2, 1, NULL, 2, 1, 'chirag dholakiya', 'proprietor', '9016345138', 'chirhb@gmail.com', '1988-05-15', 'GGFYG5125G', 20000000, 'completed', 1, 'otc_clearance', 'ICICI Bank', '6.00', '10.00', '105600', '77211545614', NULL, '2026-04-29', '2026-04-29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 13, 13, 'Loan amount may vary based on bank\'s visit\r\nROI may vary based on your CIBIL score\r\nNo charges for part payment or loan foreclosure\r\nLogin fee to be paid online, will be deducted from total processing fee\r\nLogin fee 3000/- non-refundable\r\nAxis Bank account opening required\r\nHealth Insurance & property insurance required', '2026-04-16 09:30:56', '2026-04-16 09:39:43', 13, NULL, NULL),
(3, 'SHF-202604-0003', 3, 3, 1, NULL, 2, 2, 'HARDIK NASIT', 'proprietor', '5321330', 'SDFASDFASDF@gmail.com', '2026-04-16', 'ASBAS2333S', 200000000, 'rejected', 0, 'document_collection', 'ICICI Bank', '6.00', '6.50', '948000', NULL, NULL, '2026-04-23', NULL, '2026-04-16 10:10:15', 3, 'document_collection', 'ohk', 'ohk', '2026-04-16 09:56:16', 3, 3, 3, 'Loan amount may vary based on bank\'s visit\r\nROI may vary based on your CIBIL score\r\nNo charges for part payment or loan foreclosure\r\nLogin fee to be paid online, will be deducted from total processing fee\r\nLogin fee 3000/- non-refundable\r\nAxis Bank account opening required\r\nHealth Insurance & property insurance required', '2026-04-16 09:56:09', '2026-04-16 10:10:15', 3, NULL, NULL),
(4, 'SHF-202604-0004', 6, 4, 1, NULL, 2, 1, 'DENISH MALAVIYA', 'proprietor', '9974789089', 'denish.malaviya@gmail.com', '1988-11-29', 'BFBPM7332E', 10000000, 'active', 1, 'rate_pf', 'ICICI Bank', '7.15', '7.25', '36700', '7721215454', NULL, '2026-04-23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 6, 'Loan amount may vary based on bank\'s visit\r\nROI may vary based on your CIBIL score\r\nNo charges for part payment or loan foreclosure\r\nLogin fee to be paid online, will be deducted from total processing fee\r\nLogin fee 3000/- non-refundable\r\nAxis Bank account opening required\r\nHealth Insurance & property insurance required', '2026-04-16 13:23:06', '2026-04-16 13:25:26', 23, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `loan_documents`
--

DROP TABLE IF EXISTS `loan_documents`;
CREATE TABLE `loan_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `document_name_en` varchar(255) NOT NULL,
  `document_name_gu` varchar(255) DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `received_date` date DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `file_mime` varchar(100) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `loan_documents`
--

TRUNCATE TABLE `loan_documents`;
--
-- Dumping data for table `loan_documents`
--

INSERT INTO `loan_documents` (`id`, `loan_id`, `document_name_en`, `document_name_gu`, `is_required`, `status`, `received_date`, `received_by`, `rejected_reason`, `notes`, `file_path`, `file_name`, `file_size`, `file_mime`, `uploaded_by`, `uploaded_at`, `sort_order`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 1, 'Passport Size Photographs Both', 'Passport Size Photographs Both', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-04-15 16:46:37', '2026-04-16 13:07:53', 6),
(2, 1, 'PAN Card Both', 'PAN Card Both', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-15 16:46:37', '2026-04-16 13:08:00', 6),
(3, 1, 'Aadhaar Card Both', 'Aadhaar Card Both', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-04-15 16:46:37', '2026-04-16 13:07:55', 6),
(4, 1, 'GST Registration Certificate', 'GST Registration Certificate', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-15 16:46:37', '2026-04-16 13:08:01', 6),
(5, 1, 'Udyam Registration Certificate', 'Udyam Registration Certificate', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-04-15 16:46:37', '2026-04-16 13:07:56', 6),
(6, 1, 'ITR (Last 3 years)', 'ITR (Last 3 years)', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-04-15 16:46:37', '2026-04-16 13:08:03', 6),
(7, 1, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, '2026-04-15 16:46:37', '2026-04-16 13:07:57', 6),
(8, 1, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, '2026-04-15 16:46:37', '2026-04-16 13:08:04', 6),
(9, 1, 'Property File Xerox', 'Property File Xerox', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2026-04-15 16:46:37', '2026-04-16 13:07:59', 6),
(10, 2, 'Passport Size Photographs Both', 'Passport Size Photographs Both', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-04-16 09:30:56', '2026-04-16 09:31:07', 13),
(11, 2, 'PAN Card Both', 'PAN Card Both', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-16 09:30:56', '2026-04-16 09:31:08', 13),
(12, 2, 'Aadhaar Card Both', 'Aadhaar Card Both', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-04-16 09:30:56', '2026-04-16 09:31:13', 13),
(13, 2, 'GST Registration Certificate', 'GST Registration Certificate', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-16 09:30:56', '2026-04-16 09:31:08', 13),
(14, 2, 'Udyam Registration Certificate', 'Udyam Registration Certificate', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-04-16 09:30:56', '2026-04-16 09:31:12', 13),
(15, 2, 'ITR (Last 3 years)', 'ITR (Last 3 years)', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-04-16 09:30:56', '2026-04-16 09:31:14', 13),
(16, 2, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, '2026-04-16 09:30:56', '2026-04-16 09:31:12', 13),
(17, 2, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, '2026-04-16 09:30:56', '2026-04-16 09:31:10', 13),
(18, 2, 'Property File Xerox', 'Property File Xerox', 1, 'received', '2026-04-16', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2026-04-16 09:30:56', '2026-04-16 09:31:15', 13),
(19, 3, 'Passport Size Photographs Both', 'Passport Size Photographs Both', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(20, 3, 'PAN Card Both', 'PAN Card Both', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(21, 3, 'Aadhaar Card Both', 'Aadhaar Card Both', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(22, 3, 'GST Registration Certificate', 'GST Registration Certificate', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(23, 3, 'Udyam Registration Certificate', 'Udyam Registration Certificate', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(24, 3, 'ITR (Last 3 years)', 'ITR (Last 3 years)', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(25, 3, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(26, 3, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(27, 3, 'Property File Xerox', 'Property File Xerox', 1, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(28, 4, 'Passport Size Photographs Both', 'Passport Size Photographs Both', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-04-16 13:23:06', '2026-04-16 13:23:13', 6),
(29, 4, 'PAN Card Both', 'PAN Card Both', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-16 13:23:06', '2026-04-16 13:23:20', 6),
(30, 4, 'Aadhaar Card Both', 'Aadhaar Card Both', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-04-16 13:23:06', '2026-04-16 13:23:14', 6),
(31, 4, 'GST Registration Certificate', 'GST Registration Certificate', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-04-16 13:23:06', '2026-04-16 13:23:21', 6),
(32, 4, 'Udyam Registration Certificate', 'Udyam Registration Certificate', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-04-16 13:23:06', '2026-04-16 13:23:15', 6),
(33, 4, 'ITR (Last 3 years)', 'ITR (Last 3 years)', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-04-16 13:23:07', '2026-04-16 13:23:22', 6),
(34, 4, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, '2026-04-16 13:23:07', '2026-04-16 13:23:17', 6),
(35, 4, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 7, '2026-04-16 13:23:07', '2026-04-16 13:23:22', 6),
(36, 4, 'Property File Xerox', 'Property File Xerox', 1, 'received', '2026-04-16', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, '2026-04-16 13:23:07', '2026-04-16 13:23:18', 6);

-- --------------------------------------------------------

--
-- Table structure for table `loan_progress`
--

DROP TABLE IF EXISTS `loan_progress`;
CREATE TABLE `loan_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `total_stages` int(11) NOT NULL DEFAULT 10,
  `completed_stages` int(11) NOT NULL DEFAULT 0,
  `overall_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `estimated_completion` date DEFAULT NULL,
  `workflow_snapshot` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `loan_progress`
--

TRUNCATE TABLE `loan_progress`;
--
-- Dumping data for table `loan_progress`
--

INSERT INTO `loan_progress` (`id`, `loan_id`, `total_stages`, `completed_stages`, `overall_percentage`, `estimated_completion`, `workflow_snapshot`, `created_at`, `updated_at`) VALUES
(1, 1, 11, 3, '27.27', NULL, '[{\"stage_key\":\"app_number\",\"status\":\"completed\",\"assigned_to\":6},{\"stage_key\":\"bsm_osv\",\"status\":\"completed\",\"assigned_to\":21},{\"stage_key\":\"disbursement\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"docket\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"document_collection\",\"status\":\"completed\",\"assigned_to\":6},{\"stage_key\":\"document_selection\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"esign\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"inquiry\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"kfs\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"legal_verification\",\"status\":\"completed\",\"assigned_to\":6},{\"stage_key\":\"otc_clearance\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"parallel_processing\",\"status\":\"in_progress\",\"assigned_to\":null},{\"stage_key\":\"rate_pf\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"sanction\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"sanction_decision\",\"status\":\"completed\",\"assigned_to\":23},{\"stage_key\":\"technical_valuation\",\"status\":\"in_progress\",\"assigned_to\":23}]', '2026-04-15 16:46:37', '2026-04-16 13:12:08'),
(2, 2, 11, 11, '100.00', NULL, '[{\"stage_key\":\"app_number\",\"status\":\"completed\",\"assigned_to\":13},{\"stage_key\":\"bsm_osv\",\"status\":\"completed\",\"assigned_to\":21},{\"stage_key\":\"disbursement\",\"status\":\"completed\",\"assigned_to\":23},{\"stage_key\":\"docket\",\"status\":\"completed\",\"assigned_to\":23},{\"stage_key\":\"document_collection\",\"status\":\"completed\",\"assigned_to\":13},{\"stage_key\":\"document_selection\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"esign\",\"status\":\"completed\",\"assigned_to\":21},{\"stage_key\":\"inquiry\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"kfs\",\"status\":\"completed\",\"assigned_to\":13},{\"stage_key\":\"legal_verification\",\"status\":\"completed\",\"assigned_to\":13},{\"stage_key\":\"otc_clearance\",\"status\":\"completed\",\"assigned_to\":13},{\"stage_key\":\"parallel_processing\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"rate_pf\",\"status\":\"completed\",\"assigned_to\":13},{\"stage_key\":\"sanction\",\"status\":\"completed\",\"assigned_to\":13},{\"stage_key\":\"sanction_decision\",\"status\":\"completed\",\"assigned_to\":23},{\"stage_key\":\"technical_valuation\",\"status\":\"completed\",\"assigned_to\":23}]', '2026-04-16 09:30:56', '2026-04-16 09:39:43'),
(3, 3, 11, 2, '18.18', NULL, '[{\"stage_key\":\"app_number\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"bsm_osv\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"disbursement\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"docket\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"document_collection\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"document_selection\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"esign\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"inquiry\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"kfs\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"legal_verification\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"otc_clearance\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"parallel_processing\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"rate_pf\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"sanction\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"sanction_decision\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"technical_valuation\",\"status\":\"pending\",\"assigned_to\":null}]', '2026-04-16 09:56:09', '2026-04-16 09:56:09'),
(4, 4, 11, 4, '36.36', NULL, '[{\"stage_key\":\"app_number\",\"status\":\"completed\",\"assigned_to\":6},{\"stage_key\":\"bsm_osv\",\"status\":\"completed\",\"assigned_to\":21},{\"stage_key\":\"disbursement\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"docket\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"document_collection\",\"status\":\"completed\",\"assigned_to\":6},{\"stage_key\":\"document_selection\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"esign\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"inquiry\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"kfs\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"legal_verification\",\"status\":\"completed\",\"assigned_to\":6},{\"stage_key\":\"otc_clearance\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"parallel_processing\",\"status\":\"completed\",\"assigned_to\":null},{\"stage_key\":\"rate_pf\",\"status\":\"in_progress\",\"assigned_to\":6},{\"stage_key\":\"sanction\",\"status\":\"pending\",\"assigned_to\":null},{\"stage_key\":\"sanction_decision\",\"status\":\"completed\",\"assigned_to\":23},{\"stage_key\":\"technical_valuation\",\"status\":\"completed\",\"assigned_to\":23}]', '2026-04-16 13:23:07', '2026-04-16 13:25:26');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('state','city') NOT NULL DEFAULT 'city',
  `code` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `locations`
--

TRUNCATE TABLE `locations`;
--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `parent_id`, `name`, `type`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Gujarat', 'state', 'GJ', 1, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(2, 1, 'Rajkot', 'city', 'RJT', 1, '2026-04-15 16:46:34', '2026-04-15 16:46:34');

-- --------------------------------------------------------

--
-- Table structure for table `location_product`
--

DROP TABLE IF EXISTS `location_product`;
CREATE TABLE `location_product` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `location_product`
--

TRUNCATE TABLE `location_product`;
-- --------------------------------------------------------

--
-- Table structure for table `location_user`
--

DROP TABLE IF EXISTS `location_user`;
CREATE TABLE `location_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `location_user`
--

TRUNCATE TABLE `location_user`;
--
-- Dumping data for table `location_user`
--

INSERT INTO `location_user` (`id`, `location_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(2, 2, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(3, 1, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(4, 2, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(5, 1, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(6, 2, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(7, 1, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(8, 2, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(9, 1, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(10, 2, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(11, 1, 17, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(12, 2, 17, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(13, 1, 19, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(14, 2, 19, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(15, 1, 20, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(16, 2, 20, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(17, 1, 21, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(18, 2, 21, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(19, 1, 22, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(20, 2, 22, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(21, 1, 24, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(22, 2, 24, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(23, 1, 25, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(24, 2, 25, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(25, 1, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(26, 2, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(27, 1, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(28, 2, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(29, 1, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(30, 2, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(31, 1, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(32, 2, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(33, 1, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(34, 2, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(35, 1, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(36, 2, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(37, 1, 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(38, 2, 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(39, 1, 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(40, 2, 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(41, 1, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(42, 2, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(43, 1, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(44, 2, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(45, 1, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(46, 2, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(47, 1, 18, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(48, 2, 18, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(49, 1, 23, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(50, 2, 23, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(51, 1, 26, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(52, 2, 26, '2026-04-15 16:46:35', '2026-04-15 16:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `migrations`
--

TRUNCATE TABLE `migrations`;
--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '0001_01_01_000003_create_permissions_tables', 1),
(5, '0001_01_01_000004_create_activity_logs_table', 1),
(6, '0001_01_01_000005_create_app_config_tables', 1),
(7, '0001_01_01_000006_create_quotation_tables', 1),
(8, '2026_02_26_140000_add_missing_columns_to_quotation_tables', 1),
(9, '2026_02_27_153509_rename_stamp_notary_charge_columns', 1),
(10, '2026_04_06_200000_create_banks_table', 1),
(11, '2026_04_06_200001_create_branches_table', 1),
(12, '2026_04_06_200002_create_products_table', 1),
(13, '2026_04_06_200003_create_stages_table', 1),
(14, '2026_04_06_200004_create_user_branches_table', 1),
(15, '2026_04_06_200005_add_task_fields_to_users_table', 1),
(16, '2026_04_06_210000_create_loan_details_table', 1),
(17, '2026_04_06_210001_add_loan_id_to_quotations_table', 1),
(18, '2026_04_06_220000_create_loan_documents_table', 1),
(19, '2026_04_07_084256_add_is_default_office_employee_to_user_branches', 1),
(20, '2026_04_07_100000_create_stage_assignments_table', 1),
(21, '2026_04_07_100001_create_loan_progress_table', 1),
(22, '2026_04_07_100002_create_stage_transfers_table', 1),
(23, '2026_04_07_100003_create_stage_queries_table', 1),
(24, '2026_04_07_100004_create_query_responses_table', 1),
(25, '2026_04_07_110000_create_valuation_details_table', 1),
(26, '2026_04_07_110001_create_remarks_table', 1),
(27, '2026_04_07_110002_create_notifications_table', 1),
(28, '2026_04_07_120000_create_disbursement_details_table', 1),
(29, '2026_04_07_120001_create_product_stages_table', 1),
(30, '2026_04_07_120044_add_file_columns_to_loan_documents_table', 1),
(31, '2026_04_07_130000_add_soft_deletes_to_tables', 1),
(32, '2026_04_07_140000_add_audit_columns_to_tables', 1),
(33, '2026_04_07_150000_add_default_employee_to_banks_table', 1),
(34, '2026_04_07_160000_add_default_role_to_stages_table', 1),
(35, '2026_04_07_160001_add_manager_to_branches_table', 1),
(36, '2026_04_07_170000_change_default_role_to_json_on_stages', 1),
(37, '2026_04_07_180000_create_product_stage_users_table', 1),
(38, '2026_04_07_190000_create_bank_employees_table', 1),
(39, '2026_04_07_195942_add_allow_skip_to_product_stages_table', 1),
(40, '2026_04_07_205721_seed_default_roles_and_add_sub_actions', 1),
(41, '2026_04_07_212459_add_is_enabled_to_stages_table', 1),
(42, '2026_04_07_214232_add_is_default_to_product_stage_users', 1),
(43, '2026_04_07_231539_create_locations_and_pivots', 1),
(44, '2026_04_07_234028_fix_product_stage_users_constraint', 1),
(45, '2026_04_08_002307_add_location_id_to_quotations', 1),
(46, '2026_04_08_003826_create_bank_location_table', 1),
(47, '2026_04_08_132340_create_task_role_permissions_table', 1),
(48, '2026_04_08_134923_remove_legal_advisor_role', 1),
(49, '2026_04_08_150505_update_valuation_details_for_land_construction', 1),
(50, '2026_04_08_153948_add_cheques_json_to_disbursement_details', 1),
(51, '2026_04_08_154546_add_otc_clearance_stage', 1),
(52, '2026_04_08_161030_remove_optional_stages', 1),
(53, '2026_04_08_223020_add_bank_employee_to_users_role_enum', 1),
(54, '2026_04_09_163545_add_status_reason_to_loan_details', 1),
(55, '2026_04_09_183728_add_expected_docket_date_to_loan_details', 1),
(56, '2026_04_09_193050_add_dob_pan_to_loan_details', 1),
(57, '2026_04_09_193808_create_customers_table', 1),
(58, '2026_04_09_211216_create_unified_roles_system', 1),
(59, '2026_04_11_104847_remove_loan_creation_permissions_from_office_employee', 1),
(60, '2026_04_11_113906_add_can_be_advisor_to_roles_table', 1),
(61, '2026_04_11_190544_add_sanction_decision_stage', 1),
(62, '2026_04_11_211606_fix_office_employee_permissions', 1),
(63, '2026_04_13_112307_add_branding_pdf_permissions', 1),
(64, '2026_04_13_124552_disable_skip_stages_for_all_roles', 1),
(65, '2026_04_13_132822_add_previous_status_to_stage_assignments', 1),
(66, '2026_04_13_170342_add_landmark_to_valuation_details', 1),
(67, '2026_04_13_180000_add_location_to_bank_employees_and_drop_bank_default', 1),
(68, '2026_04_14_103909_create_general_tasks_tables', 1),
(69, '2026_04_14_124345_add_branch_id_to_quotations', 1),
(70, '2026_04_14_134127_remove_file_permissions_from_non_admin_roles', 1),
(71, '2026_04_14_190210_add_bank_employee_to_stage_default_roles', 1),
(72, '2026_04_14_200000_create_daily_visit_reports_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `password_reset_tokens`
--

TRUNCATE TABLE `password_reset_tokens`;
-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `permissions`
--

TRUNCATE TABLE `permissions`;
--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `group`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Manage Customers', 'manage_customers', 'Customers', 'Create and edit customer records', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(2, 'View Customers', 'view_customers', 'Customers', 'View customer list and details', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(3, 'Impersonate Users', 'impersonate_users', 'System', 'Log in as another user', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(4, 'View Dashboard', 'view_dashboard', 'System', 'Access the dashboard', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(5, 'Manage Notifications', 'manage_notifications', 'System', 'View and manage notifications', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(6, 'Transfer Loan Stages', 'transfer_loan_stages', 'Loans', 'Transfer stage assignment to another user', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(7, 'Reject Loan', 'reject_loan', 'Loans', 'Reject a loan application', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(8, 'Change Loan Status', 'change_loan_status', 'Loans', 'Put loan on hold or cancel', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(9, 'View Loan Timeline', 'view_loan_timeline', 'Loans', 'View loan stage timeline history', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(10, 'Manage Disbursement', 'manage_disbursement', 'Loans', 'Process loan disbursement', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(11, 'Manage Valuation', 'manage_valuation', 'Loans', 'Fill and edit valuation details', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(12, 'Raise Query', 'raise_query', 'Loans', 'Raise queries on loan stages', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(13, 'Resolve Query', 'resolve_query', 'Loans', 'Resolve raised queries', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(14, 'Download Branded PDF', 'download_pdf_branded', 'Quotations', 'Download PDF with SHF branding', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(15, 'Download Plain PDF', 'download_pdf_plain', 'Quotations', 'Download PDF without SHF branding', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(16, 'View All Tasks', 'view_all_tasks', 'Tasks', 'View all general tasks across users (read-only)', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(17, 'View DVR', 'view_dvr', 'DVR', 'View daily visit reports', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(18, 'Create DVR', 'create_dvr', 'DVR', 'Create daily visit reports', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(19, 'Edit DVR', 'edit_dvr', 'DVR', 'Edit daily visit reports', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(20, 'Delete DVR', 'delete_dvr', 'DVR', 'Delete daily visit reports', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(21, 'View All DVR', 'view_all_dvr', 'DVR', 'View all daily visit reports across users', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(22, 'View Settings', 'view_settings', 'Settings', 'View the settings page', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(23, 'Edit Company Info', 'edit_company_info', 'Settings', 'Edit company information', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(24, 'Edit Banks', 'edit_banks', 'Settings', 'Add/edit/remove banks', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(25, 'Edit Documents', 'edit_documents', 'Settings', 'Add/edit/remove required documents', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(26, 'Edit Tenures', 'edit_tenures', 'Settings', 'Add/edit/remove loan tenures', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(27, 'Edit Charges', 'edit_charges', 'Settings', 'Edit bank charges', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(28, 'Edit Services', 'edit_services', 'Settings', 'Edit service charges', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(29, 'Edit GST', 'edit_gst', 'Settings', 'Edit GST percentage', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(30, 'Create Quotation', 'create_quotation', 'Quotations', 'Create new loan quotations', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(31, 'Generate PDF', 'generate_pdf', 'Quotations', 'Generate PDF for quotations', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(32, 'View Own Quotations', 'view_own_quotations', 'Quotations', 'View quotations created by self', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(33, 'View All Quotations', 'view_all_quotations', 'Quotations', 'View all quotations across users', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(34, 'Delete Quotations', 'delete_quotations', 'Quotations', 'Delete quotations', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(35, 'Download PDF', 'download_pdf', 'Quotations', 'Download generated PDFs', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(36, 'View Users', 'view_users', 'Users', 'View the users list', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(37, 'Create Users', 'create_users', 'Users', 'Create new user accounts', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(38, 'Edit Users', 'edit_users', 'Users', 'Edit existing user accounts', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(39, 'Delete Users', 'delete_users', 'Users', 'Delete user accounts', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(40, 'Assign Roles', 'assign_roles', 'Users', 'Assign roles to users', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(41, 'Change Own Password', 'change_own_password', 'System', 'Change own password', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(42, 'Manage Permissions', 'manage_permissions', 'System', 'Manage role and user permissions', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(43, 'View Activity Log', 'view_activity_log', 'System', 'View system activity log', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(44, 'Convert to Loan', 'convert_to_loan', 'Loans', 'Convert quotation to loan task', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(45, 'View Loans', 'view_loans', 'Loans', 'View loan task list', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(46, 'View All Loans', 'view_all_loans', 'Loans', 'View all loans across users/branches', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(47, 'Create Loan', 'create_loan', 'Loans', 'Create loan tasks directly', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(48, 'Edit Loan', 'edit_loan', 'Loans', 'Edit loan details', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(49, 'Delete Loan', 'delete_loan', 'Loans', 'Delete loan tasks', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(50, 'Manage Loan Documents', 'manage_loan_documents', 'Loans', 'Mark documents as received/pending, add/remove documents', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(51, 'Manage Loan Stages', 'manage_loan_stages', 'Loans', 'Update stage status and assignments', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(52, 'Skip Loan Stages', 'skip_loan_stages', 'Loans', 'Skip stages in loan workflow', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(53, 'Add Remarks', 'add_remarks', 'Loans', 'Add remarks to loan stages', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(54, 'Manage Workflow Config', 'manage_workflow_config', 'Loans', 'Configure banks, products, branches, stage workflows', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(55, 'Upload Loan Documents', 'upload_loan_documents', 'Loans', 'Upload document files to loan documents', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(56, 'Download Loan Documents', 'download_loan_documents', 'Loans', 'Download/preview uploaded document files', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(57, 'Delete Loan Files', 'delete_loan_files', 'Loans', 'Remove uploaded document files', '2026-04-15 16:46:34', '2026-04-15 16:46:34');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bank_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `products`
--

TRUNCATE TABLE `products`;
--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `bank_id`, `name`, `code`, `is_active`, `created_at`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(1, 2, 'Home Loan', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(2, 2, 'LAP', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(3, 2, 'OD', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(4, 2, 'PRATHAM', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(5, 3, 'Home Loan', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(6, 3, 'LAP', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(7, 3, 'ASHA', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(8, 1, 'Home Loan', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(9, 1, 'LAP', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(10, 4, 'Home Loan', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL),
(11, 4, 'LAP', NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_stages`
--

DROP TABLE IF EXISTS `product_stages`;
CREATE TABLE `product_stages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `stage_id` bigint(20) UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `default_assignee_role` varchar(255) DEFAULT NULL,
  `default_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `auto_skip` tinyint(1) NOT NULL DEFAULT 0,
  `allow_skip` tinyint(1) NOT NULL DEFAULT 0,
  `sub_actions_override` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `product_stages`
--

TRUNCATE TABLE `product_stages`;
--
-- Dumping data for table `product_stages`
--

INSERT INTO `product_stages` (`id`, `product_id`, `stage_id`, `is_enabled`, `default_assignee_role`, `default_user_id`, `auto_skip`, `allow_skip`, `sub_actions_override`, `sort_order`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 1, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(2, 1, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(3, 1, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(4, 1, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(5, 1, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(6, 1, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(7, 1, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(8, 1, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(9, 1, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(10, 1, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(11, 1, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[23],\"default\":23}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(12, 1, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(13, 1, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(14, 1, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(15, 1, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(16, 1, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(17, 1, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(18, 2, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(19, 2, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(20, 2, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(21, 2, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(22, 2, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(23, 2, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(24, 2, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(25, 2, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(26, 2, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(27, 2, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(28, 2, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[23],\"default\":23}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(29, 2, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(30, 2, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(31, 2, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(32, 2, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(33, 2, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(34, 2, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(35, 3, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(36, 3, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(37, 3, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(38, 3, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(39, 3, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(40, 3, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(41, 3, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(42, 3, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(43, 3, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(44, 3, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(45, 3, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[23],\"default\":23}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(46, 3, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(47, 3, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(48, 3, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(49, 3, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(50, 3, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(51, 3, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(52, 4, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(53, 4, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(54, 4, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(55, 4, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(56, 4, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(57, 4, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(58, 4, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(59, 4, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(60, 4, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(61, 4, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(62, 4, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[23],\"default\":23}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(63, 4, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[21],\"default\":21}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(64, 4, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(65, 4, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(66, 4, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(67, 4, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(68, 4, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(69, 5, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(70, 5, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(71, 5, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(72, 5, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(73, 5, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(74, 5, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(75, 5, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(76, 5, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(77, 5, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(78, 5, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(79, 5, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[15],\"default\":15}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[18],\"default\":18}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(80, 5, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[15],\"default\":15}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(81, 5, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(82, 5, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(83, 5, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(84, 5, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(85, 5, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(86, 6, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(87, 6, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(88, 6, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(89, 6, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(90, 6, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(91, 6, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(92, 6, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(93, 6, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(94, 6, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(95, 6, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(96, 6, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[16],\"default\":16}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[18],\"default\":18}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(97, 6, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[16],\"default\":16}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(98, 6, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(99, 6, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(100, 6, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(101, 6, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(102, 6, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(103, 7, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(104, 7, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(105, 7, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(106, 7, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(107, 7, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(108, 7, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(109, 7, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(110, 7, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(111, 7, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(112, 7, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(113, 7, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[17],\"default\":17}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[18],\"default\":18}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(114, 7, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[17],\"default\":17}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(115, 7, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(116, 7, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(117, 7, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(118, 7, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(119, 7, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(120, 8, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(121, 8, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(122, 8, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(123, 8, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(124, 8, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(125, 8, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(126, 8, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(127, 8, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(128, 8, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(129, 8, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(130, 8, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[19],\"default\":19}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[26],\"default\":26}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(131, 8, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[19],\"default\":19}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(132, 8, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(133, 8, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(134, 8, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(135, 8, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(136, 8, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(137, 9, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(138, 9, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(139, 9, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(140, 9, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(141, 9, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(142, 9, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(143, 9, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(144, 9, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(145, 9, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(146, 9, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(147, 9, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[20],\"default\":20}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[26],\"default\":26}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(148, 9, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[20],\"default\":20}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(149, 9, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(150, 9, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(151, 9, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(152, 9, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(153, 9, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(154, 10, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(155, 10, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(156, 10, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(157, 10, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(158, 10, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(159, 10, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(160, 10, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(161, 10, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(162, 10, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(163, 10, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(164, 10, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[24],\"default\":24}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[26],\"default\":26}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(165, 10, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[24],\"default\":24}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(166, 10, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(167, 10, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(168, 10, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(169, 10, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(170, 10, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(171, 11, 1, 1, NULL, NULL, 0, 0, NULL, 0, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(172, 11, 2, 1, NULL, NULL, 0, 0, NULL, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(173, 11, 3, 1, NULL, NULL, 0, 0, NULL, 2, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(174, 11, 4, 1, NULL, NULL, 0, 0, NULL, 3, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(175, 11, 5, 1, NULL, NULL, 0, 0, NULL, 4, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(176, 11, 6, 1, NULL, NULL, 0, 0, NULL, 5, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(177, 11, 7, 1, NULL, NULL, 0, 0, NULL, 6, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(178, 11, 8, 1, NULL, NULL, 0, 0, NULL, 7, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(179, 11, 9, 1, NULL, NULL, 0, 0, NULL, 8, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(180, 11, 10, 1, NULL, NULL, 0, 0, NULL, 9, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(181, 11, 11, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[25],\"default\":25}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[26],\"default\":26}]}]', 10, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(182, 11, 12, 1, NULL, NULL, 0, 0, '[{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[{\"location_id\":2,\"users\":[25],\"default\":25}]},{\"is_enabled\":true,\"roles\":[],\"users\":[],\"default_user\":null,\"location_overrides\":[]}]', 11, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(183, 11, 13, 1, NULL, NULL, 0, 0, NULL, 12, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(184, 11, 14, 1, NULL, NULL, 0, 0, NULL, 13, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(185, 11, 15, 1, NULL, NULL, 0, 0, NULL, 14, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(186, 11, 16, 1, NULL, NULL, 0, 0, NULL, 15, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL),
(187, 11, 17, 1, NULL, NULL, 0, 0, NULL, 16, '2026-04-15 16:46:35', '2026-04-15 16:46:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_stage_users`
--

DROP TABLE IF EXISTS `product_stage_users`;
CREATE TABLE `product_stage_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_stage_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `product_stage_users`
--

TRUNCATE TABLE `product_stage_users`;
--
-- Dumping data for table `product_stage_users`
--

INSERT INTO `product_stage_users` (`id`, `product_stage_id`, `branch_id`, `location_id`, `user_id`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 6, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(2, 7, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(3, 8, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(4, 9, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(5, 10, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(6, 11, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(7, 12, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(8, 13, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(9, 14, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(10, 15, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(11, 16, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(12, 17, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(13, 23, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(14, 24, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(15, 25, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(16, 26, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(17, 27, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(18, 28, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(19, 29, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(20, 30, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(21, 31, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(22, 32, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(23, 33, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(24, 34, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(25, 40, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(26, 41, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(27, 42, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(28, 43, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(29, 44, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(30, 45, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(31, 46, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(32, 47, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(33, 48, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(34, 49, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(35, 50, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(36, 51, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(37, 57, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(38, 58, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(39, 59, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(40, 60, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(41, 61, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(42, 62, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(43, 63, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(44, 64, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(45, 65, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(46, 66, NULL, 2, 21, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(47, 67, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(48, 68, NULL, 2, 23, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(49, 74, NULL, 2, 15, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(50, 75, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(51, 76, NULL, 2, 15, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(52, 77, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(53, 78, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(54, 79, NULL, 2, 15, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(55, 80, NULL, 2, 15, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(56, 81, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(57, 82, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(58, 83, NULL, 2, 15, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(59, 84, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(60, 85, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(61, 91, NULL, 2, 16, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(62, 92, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(63, 93, NULL, 2, 16, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(64, 94, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(65, 95, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(66, 96, NULL, 2, 16, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(67, 97, NULL, 2, 16, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(68, 98, NULL, 2, 18, 1, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(69, 99, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(70, 100, NULL, 2, 16, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(71, 101, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(72, 102, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(73, 108, NULL, 2, 17, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(74, 109, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(75, 110, NULL, 2, 17, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(76, 111, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(77, 112, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(78, 113, NULL, 2, 17, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(79, 114, NULL, 2, 17, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(80, 115, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(81, 116, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(82, 117, NULL, 2, 17, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(83, 118, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(84, 119, NULL, 2, 18, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(85, 125, NULL, 2, 19, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(86, 126, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(87, 127, NULL, 2, 19, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(88, 128, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(89, 129, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(90, 130, NULL, 2, 19, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(91, 131, NULL, 2, 19, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(92, 132, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(93, 133, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(94, 134, NULL, 2, 19, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(95, 135, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(96, 136, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(97, 142, NULL, 2, 20, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(98, 143, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(99, 144, NULL, 2, 20, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(100, 145, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(101, 146, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(102, 147, NULL, 2, 20, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(103, 148, NULL, 2, 20, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(104, 149, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(105, 150, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(106, 151, NULL, 2, 20, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(107, 152, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(108, 153, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(109, 159, NULL, 2, 24, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(110, 160, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(111, 161, NULL, 2, 24, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(112, 162, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(113, 163, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(114, 164, NULL, 2, 24, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(115, 165, NULL, 2, 24, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(116, 166, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(117, 167, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(118, 168, NULL, 2, 24, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(119, 169, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(120, 170, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(121, 176, NULL, 2, 25, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(122, 177, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(123, 178, NULL, 2, 25, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(124, 179, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(125, 180, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(126, 181, NULL, 2, 25, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(127, 182, NULL, 2, 25, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(128, 183, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(129, 184, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(130, 185, NULL, 2, 25, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(131, 186, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36'),
(132, 187, NULL, 2, 26, 1, '2026-04-15 16:46:36', '2026-04-15 16:46:36');

-- --------------------------------------------------------

--
-- Table structure for table `query_responses`
--

DROP TABLE IF EXISTS `query_responses`;
CREATE TABLE `query_responses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stage_query_id` bigint(20) UNSIGNED NOT NULL,
  `response_text` text NOT NULL,
  `responded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `query_responses`
--

TRUNCATE TABLE `query_responses`;
--
-- Dumping data for table `query_responses`
--

INSERT INTO `query_responses` (`id`, `stage_query_id`, `response_text`, `responded_by`, `created_at`) VALUES
(1, 1, 'Hdbbdn', 23, '2026-04-16 13:21:05');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_type` varchar(255) NOT NULL,
  `loan_amount` bigint(20) UNSIGNED NOT NULL,
  `pdf_filename` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `prepared_by_name` varchar(255) DEFAULT NULL,
  `prepared_by_mobile` varchar(255) DEFAULT NULL,
  `selected_tenures` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `quotations`
--

TRUNCATE TABLE `quotations`;
--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `loan_id`, `location_id`, `branch_id`, `user_id`, `customer_name`, `customer_type`, `loan_amount`, `pdf_filename`, `pdf_path`, `additional_notes`, `prepared_by_name`, `prepared_by_mobile`, `selected_tenures`, `created_at`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(1, 1, NULL, NULL, 1, 'Vipul Parsana', 'proprietor', 5000000, 'Loan_Proposal_Vipul_Parsana_2026-04-15_22_16_36.pdf', '/home/admin/web/loans.shfworld.com/public_html/storage/app/pdfs/Loan_Proposal_Vipul_Parsana_2026-04-15_22_16_36.pdf', NULL, 'Super Admin', '+91 99747 89089', '[5,10,15,20]', '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1, NULL, NULL),
(2, 2, NULL, NULL, 13, 'chirag dholakiya', 'proprietor', 20000000, 'Loan_Proposal_chirag_dholakiya_2026-04-16_14_58_14.pdf', '/home/admin/web/loans.shfworld.com/public_html/storage/app/pdfs/Loan_Proposal_chirag_dholakiya_2026-04-16_14_58_14.pdf', 'Loan amount may vary based on bank\'s visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required', 'MILAN DHOLAKIYA', '8401277654', '[5,10,15,20]', '2026-04-16 09:28:15', '2026-04-16 09:30:56', 13, NULL, NULL),
(3, 3, NULL, NULL, 3, 'HARDIK NASIT', 'proprietor', 200000000, 'Loan_Proposal_HARDIK_NASIT_2026-04-16_15_15_33.pdf', '/home/admin/web/loans.shfworld.com/public_html/storage/app/pdfs/Loan_Proposal_HARDIK_NASIT_2026-04-16_15_15_33.pdf', 'Loan amount may vary based on bank\'s visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required', 'HARDIK NASIT', '9726179351', '[5,10,15,20]', '2026-04-16 09:45:34', '2026-04-16 09:56:09', 3, NULL, NULL),
(4, NULL, NULL, NULL, 3, 'hardik nasit', 'proprietor', 4500000, 'Loan_Proposal_hardik_nasit_2026-04-16_15_29_41.pdf', '/home/admin/web/loans.shfworld.com/public_html/storage/app/pdfs/Loan_Proposal_hardik_nasit_2026-04-16_15_29_41.pdf', 'Loan amount may vary based on bank\'s visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required', 'HARDIK NASIT', '9726179351', '[5,10,15,20]', '2026-04-16 09:59:42', '2026-04-16 09:59:42', 3, NULL, NULL),
(5, NULL, NULL, NULL, 6, 'THESIYA JAYDEEP', 'proprietor', 45000000, 'Loan_Proposal_THESIYA_JAYDEEP_2026-04-16_18_37_14.pdf', '/home/admin/web/loans.shfworld.com/public_html/storage/app/pdfs/Loan_Proposal_THESIYA_JAYDEEP_2026-04-16_18_37_14.pdf', 'Loan amount may vary based on bank\'s visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required', 'JAYDEEP THESHIYA', '9725248300', '[5,10,15,20]', '2026-04-16 13:07:16', '2026-04-16 13:07:16', 6, NULL, NULL),
(6, 4, NULL, NULL, 6, 'DENISH MALAVIYA', 'proprietor', 10000000, 'Loan_Proposal_DENISH_MALAVIYA_2026-04-16_18_52_16.pdf', '/home/admin/web/loans.shfworld.com/public_html/storage/app/pdfs/Loan_Proposal_DENISH_MALAVIYA_2026-04-16_18_52_16.pdf', 'Loan amount may vary based on bank\'s visit\nROI may vary based on your CIBIL score\nNo charges for part payment or loan foreclosure\nLogin fee to be paid online, will be deducted from total processing fee\nLogin fee 3000/- non-refundable\nAxis Bank account opening required\nHealth Insurance & property insurance required', 'JAYDEEP THESHIYA', '9725248300', '[5,10,15,20]', '2026-04-16 13:22:17', '2026-04-16 13:23:06', 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_banks`
--

DROP TABLE IF EXISTS `quotation_banks`;
CREATE TABLE `quotation_banks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_id` bigint(20) UNSIGNED NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `roi_min` decimal(5,2) NOT NULL DEFAULT 0.00,
  `roi_max` decimal(5,2) NOT NULL DEFAULT 0.00,
  `pf_charge` decimal(5,2) NOT NULL DEFAULT 0.00,
  `admin_charge` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `stamp_notary` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `registration_fee` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `advocate_fees` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `iom_charge` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `tc_report` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `extra1_name` varchar(255) DEFAULT NULL,
  `extra1_amount` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `extra2_name` varchar(255) DEFAULT NULL,
  `extra2_amount` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `total_charges` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `quotation_banks`
--

TRUNCATE TABLE `quotation_banks`;
--
-- Dumping data for table `quotation_banks`
--

INSERT INTO `quotation_banks` (`id`, `quotation_id`, `bank_name`, `roi_min`, `roi_max`, `pf_charge`, `admin_charge`, `stamp_notary`, `registration_fee`, `advocate_fees`, `iom_charge`, `tc_report`, `extra1_name`, `extra1_amount`, `extra2_name`, `extra2_amount`, `total_charges`, `created_at`, `updated_at`) VALUES
(1, 1, 'ICICI Bank', '9.00', '9.15', '0.00', 0, 0, 0, 2000, 0, 2500, NULL, 0, NULL, 0, 4500, '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(2, 2, 'ICICI Bank', '6.00', '10.00', '0.10', 0, 600, 5900, 3000, 70000, 2500, NULL, 0, NULL, 0, 105600, '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(3, 3, 'ICICI Bank', '6.00', '6.50', '0.10', 0, 600, 5900, 3000, 700000, 2500, NULL, 0, NULL, 0, 948000, '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(4, 4, 'ICICI Bank', '7.00', '7.50', '0.10', 0, 600, 5900, 3000, 7000, 2500, NULL, 0, NULL, 0, 24310, '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(5, 5, 'ICICI Bank', '7.25', '7.50', '0.15', 0, 600, 5900, 3000, 157500, 2500, NULL, 0, NULL, 0, 249150, '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(6, 6, 'ICICI Bank', '7.15', '7.25', '0.15', 0, 600, 5900, 3000, 7000, 2500, NULL, 0, NULL, 0, 36700, '2026-04-16 13:22:17', '2026-04-16 13:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_documents`
--

DROP TABLE IF EXISTS `quotation_documents`;
CREATE TABLE `quotation_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_id` bigint(20) UNSIGNED NOT NULL,
  `document_name_en` varchar(255) NOT NULL,
  `document_name_gu` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `quotation_documents`
--

TRUNCATE TABLE `quotation_documents`;
--
-- Dumping data for table `quotation_documents`
--

INSERT INTO `quotation_documents` (`id`, `quotation_id`, `document_name_en`, `document_name_gu`, `created_at`, `updated_at`) VALUES
(1, 1, 'Passport Size Photographs Both', 'Passport Size Photographs Both', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(2, 1, 'PAN Card Both', 'PAN Card Both', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(3, 1, 'Aadhaar Card Both', 'Aadhaar Card Both', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(4, 1, 'GST Registration Certificate', 'GST Registration Certificate', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(5, 1, 'Udyam Registration Certificate', 'Udyam Registration Certificate', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(6, 1, 'ITR (Last 3 years)', 'ITR (Last 3 years)', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(7, 1, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(8, 1, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(9, 1, 'Property File Xerox', 'Property File Xerox', '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(10, 2, 'Passport Size Photographs Both', 'Passport Size Photographs Both', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(11, 2, 'PAN Card Both', 'PAN Card Both', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(12, 2, 'Aadhaar Card Both', 'Aadhaar Card Both', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(13, 2, 'GST Registration Certificate', 'GST Registration Certificate', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(14, 2, 'Udyam Registration Certificate', 'Udyam Registration Certificate', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(15, 2, 'ITR (Last 3 years)', 'ITR (Last 3 years)', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(16, 2, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(17, 2, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(18, 2, 'Property File Xerox', 'Property File Xerox', '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(19, 3, 'Passport Size Photographs Both', 'Passport Size Photographs Both', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(20, 3, 'PAN Card Both', 'PAN Card Both', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(21, 3, 'Aadhaar Card Both', 'Aadhaar Card Both', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(22, 3, 'GST Registration Certificate', 'GST Registration Certificate', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(23, 3, 'Udyam Registration Certificate', 'Udyam Registration Certificate', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(24, 3, 'ITR (Last 3 years)', 'ITR (Last 3 years)', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(25, 3, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(26, 3, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(27, 3, 'Property File Xerox', 'Property File Xerox', '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(28, 4, 'Passport Size Photographs Both', 'Passport Size Photographs Both', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(29, 4, 'PAN Card Both', 'PAN Card Both', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(30, 4, 'Aadhaar Card Both', 'Aadhaar Card Both', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(31, 4, 'GST Registration Certificate', 'GST Registration Certificate', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(32, 4, 'Udyam Registration Certificate', 'Udyam Registration Certificate', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(33, 4, 'ITR (Last 3 years)', 'ITR (Last 3 years)', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(34, 4, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(35, 4, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(36, 4, 'Property File Xerox', 'Property File Xerox', '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(37, 5, 'Passport Size Photographs Both', 'Passport Size Photographs Both', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(38, 5, 'PAN Card Both', 'PAN Card Both', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(39, 5, 'Aadhaar Card Both', 'Aadhaar Card Both', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(40, 5, 'GST Registration Certificate', 'GST Registration Certificate', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(41, 5, 'Udyam Registration Certificate', 'Udyam Registration Certificate', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(42, 5, 'ITR (Last 3 years)', 'ITR (Last 3 years)', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(43, 5, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(44, 5, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(45, 5, 'Property File Xerox', 'Property File Xerox', '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(46, 6, 'Passport Size Photographs Both', 'Passport Size Photographs Both', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(47, 6, 'PAN Card Both', 'PAN Card Both', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(48, 6, 'Aadhaar Card Both', 'Aadhaar Card Both', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(49, 6, 'GST Registration Certificate', 'GST Registration Certificate', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(50, 6, 'Udyam Registration Certificate', 'Udyam Registration Certificate', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(51, 6, 'ITR (Last 3 years)', 'ITR (Last 3 years)', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(52, 6, 'Bank Statement (Last 12 months)', 'Bank Statement (Last 12 months)', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(53, 6, 'Current Loan Statement ( if applicable )', 'Current Loan Statement (if applicable)', '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(54, 6, 'Property File Xerox', 'Property File Xerox', '2026-04-16 13:22:17', '2026-04-16 13:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_emi`
--

DROP TABLE IF EXISTS `quotation_emi`;
CREATE TABLE `quotation_emi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quotation_bank_id` bigint(20) UNSIGNED NOT NULL,
  `tenure_years` int(11) NOT NULL,
  `monthly_emi` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `total_interest` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `total_payment` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `quotation_emi`
--

TRUNCATE TABLE `quotation_emi`;
--
-- Dumping data for table `quotation_emi`
--

INSERT INTO `quotation_emi` (`id`, `quotation_bank_id`, `tenure_years`, `monthly_emi`, `total_interest`, `total_payment`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 103974, 1238440, 6238440, '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(2, 1, 10, 63542, 2625040, 7625040, '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(3, 1, 15, 50937, 4168660, 9168660, '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(4, 1, 20, 45228, 5854720, 10854720, '2026-04-15 16:46:37', '2026-04-15 16:46:37'),
(5, 2, 5, 386656, 3199362, 23199362, '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(6, 2, 10, 222041, 6644920, 26644920, '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(7, 2, 15, 168771, 10378846, 30378846, '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(8, 2, 20, 143286, 14388691, 34388691, '2026-04-16 09:28:15', '2026-04-16 09:28:15'),
(9, 3, 5, 3866560, 31993618, 231993618, '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(10, 3, 10, 2220410, 66449205, 266449205, '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(11, 3, 15, 1687714, 103788458, 303788458, '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(12, 3, 20, 1432862, 143886908, 343886908, '2026-04-16 09:45:34', '2026-04-16 09:45:34'),
(13, 4, 5, 89105, 846324, 5346324, '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(14, 4, 10, 52249, 1769858, 6269858, '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(15, 4, 15, 40447, 2780509, 7280509, '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(16, 4, 20, 34888, 3873229, 8373229, '2026-04-16 09:59:42', '2026-04-16 09:59:42'),
(17, 5, 5, 896371, 8782276, 53782276, '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(18, 5, 10, 528305, 18396562, 63396562, '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(19, 5, 15, 410788, 28941893, 73941893, '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(20, 5, 20, 355669, 40360606, 85360606, '2026-04-16 13:07:16', '2026-04-16 13:07:16'),
(21, 6, 5, 198720, 1923227, 11923227, '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(22, 6, 10, 116883, 4025964, 14025964, '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(23, 6, 15, 90724, 6330234, 16330234, '2026-04-16 13:22:17', '2026-04-16 13:22:17'),
(24, 6, 20, 78433, 8823878, 18823878, '2026-04-16 13:22:17', '2026-04-16 13:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `remarks`
--

DROP TABLE IF EXISTS `remarks`;
CREATE TABLE `remarks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `stage_key` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `remark` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `remarks`
--

TRUNCATE TABLE `remarks`;
-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `can_be_advisor` tinyint(1) NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `roles`
--

TRUNCATE TABLE `roles`;
--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `can_be_advisor`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super_admin', 'Full system access, bypasses all permissions', 0, 1, '2026-04-15 16:46:33', '2026-04-15 16:46:33'),
(2, 'Admin', 'admin', 'System administration, settings, user management', 0, 1, '2026-04-15 16:46:33', '2026-04-15 16:46:33'),
(3, 'Branch Manager', 'branch_manager', 'Branch-level management, quotations, loan stages', 1, 0, '2026-04-15 16:46:33', '2026-04-15 16:46:33'),
(4, 'Business Development Head', 'bdh', 'Same access as Branch Manager', 1, 0, '2026-04-15 16:46:33', '2026-04-15 16:46:33'),
(5, 'Loan Advisor', 'loan_advisor', 'Quotation creation, loan processing stages', 1, 0, '2026-04-15 16:46:33', '2026-04-15 16:46:33'),
(6, 'Bank Employee', 'bank_employee', 'Bank-side loan processing only', 0, 0, '2026-04-15 16:46:33', '2026-04-15 16:46:33'),
(7, 'Office Employee', 'office_employee', 'Office operations, loan stages, document handling', 0, 0, '2026-04-15 16:46:33', '2026-04-15 16:46:33');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

DROP TABLE IF EXISTS `role_permission`;
CREATE TABLE `role_permission` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `role_permission`
--

TRUNCATE TABLE `role_permission`;
--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(2, 32),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 37),
(2, 38),
(2, 40),
(2, 41),
(2, 42),
(2, 43),
(2, 44),
(2, 45),
(2, 46),
(2, 47),
(2, 48),
(2, 49),
(2, 50),
(2, 51),
(2, 53),
(2, 54),
(2, 55),
(2, 56),
(2, 57),
(3, 1),
(3, 2),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 30),
(3, 31),
(3, 32),
(3, 33),
(3, 35),
(3, 36),
(3, 41),
(3, 43),
(3, 44),
(3, 45),
(3, 46),
(3, 47),
(3, 48),
(3, 50),
(3, 51),
(3, 53),
(4, 1),
(4, 2),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(4, 8),
(4, 9),
(4, 10),
(4, 11),
(4, 12),
(4, 13),
(4, 14),
(4, 15),
(4, 30),
(4, 31),
(4, 32),
(4, 33),
(4, 35),
(4, 36),
(4, 41),
(4, 43),
(4, 44),
(4, 45),
(4, 46),
(4, 47),
(4, 48),
(4, 50),
(4, 51),
(4, 53),
(5, 1),
(5, 2),
(5, 4),
(5, 5),
(5, 6),
(5, 8),
(5, 9),
(5, 10),
(5, 12),
(5, 13),
(5, 14),
(5, 15),
(5, 30),
(5, 31),
(5, 32),
(5, 35),
(5, 41),
(5, 44),
(5, 45),
(5, 47),
(5, 48),
(5, 50),
(5, 51),
(5, 53),
(6, 2),
(6, 4),
(6, 5),
(6, 9),
(6, 12),
(6, 41),
(6, 45),
(6, 51),
(6, 53),
(7, 2),
(7, 4),
(7, 5),
(7, 6),
(7, 9),
(7, 11),
(7, 12),
(7, 41),
(7, 45),
(7, 48),
(7, 50),
(7, 51),
(7, 53);

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `role_user`
--

TRUNCATE TABLE `role_user`;
--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 3),
(5, 4),
(6, 5),
(7, 5),
(8, 5),
(9, 5),
(10, 5),
(11, 5),
(12, 5),
(13, 5),
(14, 5),
(15, 6),
(16, 6),
(17, 6),
(18, 7),
(19, 6),
(20, 6),
(21, 6),
(22, 6),
(23, 7),
(24, 6),
(25, 6),
(26, 7);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `sessions`
--

TRUNCATE TABLE `sessions`;
--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0hI36BVu4NcuRHnsRRC3jTWV8gP6W5F7rJITffbl', 8, '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoicE81UW4xb0drQ1NDNVNEZ29oNnVuZVVzUDlRQkJoekRjSUxJTkhlWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl8zZGM3YTkxM2VmNWZkNGI4OTBlY2FiZTM0ODcwODU1NzNlMTZjZjgyIjtpOjg7fQ==', 1776344817),
('7dyj8m6ry1CmpkEQHNvINfVVkIWaAAI3HH1U4KqL', NULL, '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibm5EeW9sZm54cmNEdzN6dnlnR2M1Wm80MFl5TVVDSFJwM3d3ZVJqeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNjoiaHR0cHM6Ly9sb2Fucy5zaGZ3b3JsZC5jb20vZGFzaGJvYXJkIjt9fQ==', 1776343288),
('A2wG06Au56FWNvzmFtpxrHQz8W2TNymwEiR6iBs1', 23, '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiN2JJMzk0cGw5Zjc5b2hoNnFnVE96TVNkSFF1Y3JzaUlNaHVTbHJNSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl8zZGM3YTkxM2VmNWZkNGI4OTBlY2FiZTM0ODcwODU1NzNlMTZjZjgyIjtpOjIzO30=', 1776344517),
('AwdJB8AISdlkKRXUl34RxFDVPTBXrkEZASJHkR6H', 6, '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTzFYOWx0RklVaWRmZUxNT3pZeEVnQXpnQzFmWkZqQUVIVTdMc1U5NCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aTo2O30=', 1776344777),
('BLo3XkZ3UJzQNITkDrUzIz0yrA05wK37SVOl3qNr', 1, '122.173.87.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZldveVlKUUVPVVBhSEE4bGJkYnR2TGVhcTNYQjllSjcxU0VGdmExZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjEyOiJyZW1lbWJlcl93ZWIiO2E6Mjp7aTowO3M6NTM6InJlbWVtYmVyX3dlYl8zZGM3YTkxM2VmNWZkNGI4OTBlY2FiZTM0ODcwODU1NzNlMTZjZjgyIjtpOjE7czoxMjc6IjF8RHQxVnh3ejR0TWJoNlAzV1RGSU9ZOTU1dVB6b29wZGFHdldqcFJoU3YyeFp4UWpDbnZ5RUdod2N3VkRTfDllMmRmYWRjZGEwMjJhNzQxZTRlYzA5MjMwNTM3YjE2ZDViOTVjZTA5MDU5OGJiNDhiN2UyNzk5N2I2MjQ5YTAiO31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aToxO30=', 1776345901),
('e1SpyYgzvXKzM2lTjnbcMXMZy67tDhw85R9odFmC', NULL, '103.240.208.228', 'WhatsApp/2.23.20.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUhPWndZVEdlQUNGdmFiTHRVUXNXdXQ0VVAyVmY3QVZYampINGNxUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776344592),
('EYL6Orb1wQzqQc0z5PUJQDzkSq7XEvXTt54w1PID', 3, '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVWlHaVE4SU9nbjBENk9vdmZDY2haREJlMm5ZZXR0dzcyQTczczNzdyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjUwOiJodHRwczovL2xvYW5zLnNoZndvcmxkLmNvbS9hcGkvbm90aWZpY2F0aW9ucy9jb3VudCI7czo1OiJyb3V0ZSI7czoyMzoiYXBpLm5vdGlmaWNhdGlvbnMuY291bnQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjEyOiJyZW1lbWJlcl93ZWIiO2E6Mjp7aTowO3M6NTM6InJlbWVtYmVyX3dlYl8zZGM3YTkxM2VmNWZkNGI4OTBlY2FiZTM0ODcwODU1NzNlMTZjZjgyIjtpOjE7czoxMjc6IjN8MklQaWtlVWhSYXlOWTFPZDBabUUwRmNGMkxudkltVG52NTlOSDRQMDBiQ1NOdjFubGg3SUVxcXk5Y0hGfGY2OTkwMGU2MzY0NDJmOGU2MzI0MmU4OGRiZmU2ZTk4NjAzYzk4NWVhODc4MGFjMTEwZThkMzk3ZTY5NWNiNGEiO31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aTozO30=', 1776342421),
('GS3MZtwq6Blg2VfAfdW1PSrAOEWnlaOm7GiYwWLF', NULL, '103.240.208.228', 'WhatsApp/2.23.20.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVThvOXRkSTlIRjlraVJBSHBIT3I5YXBJYzRrMHR6ejFUNjI1cFRJUCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNjoiaHR0cHM6Ly9sb2Fucy5zaGZ3b3JsZC5jb20vZGFzaGJvYXJkIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2Rhc2hib2FyZCI7czo1OiJyb3V0ZSI7czo5OiJkYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776344592),
('JjvNhCNCnI5ycm9pi2ZW6e8MCAGa0YeRPiDyn8kU', 9, '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Mobile Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoicENNbWE2NW1KNFJmV2psdUt5TDh2dG9oVWpqazBGd1hzZ1hndHZ0ZSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aTo5O30=', 1776345218),
('Jn0s3lJSe2l7ASpeH1pjdkLFzRkEuaC8CIZE7Bxf', 23, '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiY1BqWkNPY3RUMnpCZEtzM0hoSU1xc1pyM01oRlR5UElnSXlmcm9xbCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2xvYW5zLzQvc3RhZ2VzIjtzOjU6InJvdXRlIjtzOjEyOiJsb2Fucy5zdGFnZXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzNkYzdhOTEzZWY1ZmQ0Yjg5MGVjYWJlMzQ4NzA4NTU3M2UxNmNmODIiO2k6MjM7fQ==', 1776345926),
('jUhWoz7Lmr93AbQZcgCaqgzGCkINyMlXtqZtMynL', NULL, '103.240.208.228', 'WhatsApp/2.23.20.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTzRLQWFFRnJzRmFvSktXcm1CYTdrb3pxMDRId01LVlRBY3FWRjdNRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776344592),
('osl2GDsvqM1P1h0Pu1ZOMMhVfxfSTEYU3DIDW0Gq', 4, '103.240.208.228', 'Mozilla/5.0 (Linux; U; Android 14; en-in; CPH2359 Build/UKQ1.230924.001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.5970.168 Mobile Safari/537.36 HeyTapBrowser/45.14.0.1', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUEx4a0xOVFYxa0VwU3ZCTm9TWDVjd3hqT3EwYjE3NXM4VWNMczBjViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aTo0O30=', 1776345912),
('qZdwK9WvE6FNz7W7H74BkQpUr3n1yFGQGcElTzk9', 13, '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiajQ3SkhSRzdmM3BJTXd1RUFENnBZckIyU3dsVTZ1dmFFNVFWWlp2eiI7czo1MDoibG9naW5fd2ViXzNkYzdhOTEzZWY1ZmQ0Yjg5MGVjYWJlMzQ4NzA4NTU3M2UxNmNmODIiO2k6MTM7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1776344891),
('TKipjV9XFJxfdy761ON1jn1OvQ7vTBiYtRgB1bQu', 21, '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiS2kxZ01pY2wyVUkwaGZYV3dKb0JHZTNveDFTNEFva0NWRXNNQWE0cCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aToyMTt9', 1776345911),
('uTK4RyBPBsnHMWmOnMLtnWzkx4GhKw3PTSeMzbMm', 18, '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRzF4VTd6ZWRCMDBpSGRzTjZINDUxcGhYcDg5djJDR1ZiMm1odDFwciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aToxODt9', 1776345180),
('vDW8FPXlru5sLSP3DJajf1qDUxfbfacQeHJsbvjo', 12, '103.240.208.228', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVk9oZUVCMlRsRlNNZ21XUXBuUFFpRFduNU8xWmpFdVBHSWNtMzdPTiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aToxMjt9', 1776345880),
('WkeTFvzCP5aADGBcqtbryYAHjToL4HhDTUp5ksVR', NULL, '103.240.208.228', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZnJYa3BoN0lrcW9CNWFzV3gwTEVWVGEzaG42cEJLQXQ5T09HSEdHMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776340058),
('WZ3w8s4AesufZ8SSuzjYFZslG0KCnpNYwZitWkUw', 6, '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiWlpOWE8zNHhFQktUWktuTXhoZUE2ZXVMUDhyTVNmaTV1cWFuRlcxcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6MzoidXJsIjthOjA6e31zOjEyOiJyZW1lbWJlcl93ZWIiO2E6Mjp7aTowO3M6NTM6InJlbWVtYmVyX3dlYl8zZGM3YTkxM2VmNWZkNGI4OTBlY2FiZTM0ODcwODU1NzNlMTZjZjgyIjtpOjE7czoxMjc6IjV8SEphQlFhaWJqM3BDSHZ3bUV5WEJnYjJaNFdrUzNSVXUzMG0zWno3RmxwQldSVHNWUFhtSUZzeDZyMmhGfGY2OTkwMGU2MzY0NDJmOGU2MzI0MmU4OGRiZmU2ZTk4NjAzYzk4NWVhODc4MGFjMTEwZThkMzk3ZTY5NWNiNGEiO31zOjE1OiJpbXBlcnNvbmF0ZWRfYnkiO2k6NTtzOjE4OiJpbXBlcnNvbmF0b3JfZ3VhcmQiO3M6Mzoid2ViIjtzOjI0OiJpbXBlcnNvbmF0b3JfZ3VhcmRfdXNpbmciO047czo1MDoibG9naW5fd2ViXzNkYzdhOTEzZWY1ZmQ0Yjg5MGVjYWJlMzQ4NzA4NTU3M2UxNmNmODIiO2k6Njt9', 1776345920),
('YXiJ7nuyEXFjgmHj8AuAMT0Oh50lJh46DC8mhM2D', 23, '103.240.208.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSUM1b2tnSzROUDBPS2lmbGhBa2toMHJPR2I0VW9HajlPYUNQU0JmZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vbG9hbnMuc2hmd29ybGQuY29tL2FwaS9ub3RpZmljYXRpb25zL2NvdW50IjtzOjU6InJvdXRlIjtzOjIzOiJhcGkubm90aWZpY2F0aW9ucy5jb3VudCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfM2RjN2E5MTNlZjVmZDRiODkwZWNhYmUzNDg3MDg1NTczZTE2Y2Y4MiI7aToyMzt9', 1776345907);

-- --------------------------------------------------------

--
-- Table structure for table `shf_notifications`
--

DROP TABLE IF EXISTS `shf_notifications`;
CREATE TABLE `shf_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `loan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `stage_key` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `shf_notifications`
--

TRUNCATE TABLE `shf_notifications`;
--
-- Dumping data for table `shf_notifications`
--

INSERT INTO `shf_notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `loan_id`, `stage_key`, `link`, `created_at`, `updated_at`) VALUES
(1, 13, 'Task Assigned', 'You have been assigned a task: \"od case\"', 'task', 0, NULL, NULL, 'https://loans.shfworld.com/general-tasks/1', '2026-04-16 09:41:07', '2026-04-16 09:41:07'),
(2, 5, 'Task Assigned', 'You have been assigned a task: \"od case\"', 'task', 1, NULL, NULL, 'https://loans.shfworld.com/general-tasks/1', '2026-04-16 09:41:38', '2026-04-16 13:20:48'),
(3, 13, 'Task Completed', 'Task \"od case\" has been completed by Denish BDH', 'task', 0, NULL, NULL, 'https://loans.shfworld.com/general-tasks/1', '2026-04-16 09:42:04', '2026-04-16 09:42:04'),
(4, 23, 'Query Raised', 'A query was raised on technical_valuation: Hdiifvd', 'warning', 0, 1, 'technical_valuation', NULL, '2026-04-16 13:20:57', '2026-04-16 13:20:57');

-- --------------------------------------------------------

--
-- Table structure for table `stages`
--

DROP TABLE IF EXISTS `stages`;
CREATE TABLE `stages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stage_key` varchar(255) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `stage_name_en` varchar(255) NOT NULL,
  `stage_name_gu` varchar(255) DEFAULT NULL,
  `sequence_order` int(11) NOT NULL,
  `is_parallel` tinyint(1) NOT NULL DEFAULT 0,
  `parent_stage_key` varchar(255) DEFAULT NULL,
  `stage_type` varchar(255) NOT NULL DEFAULT 'sequential',
  `description_en` text DEFAULT NULL,
  `description_gu` text DEFAULT NULL,
  `default_role` varchar(255) DEFAULT NULL,
  `sub_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `stages`
--

TRUNCATE TABLE `stages`;
--
-- Dumping data for table `stages`
--

INSERT INTO `stages` (`id`, `stage_key`, `is_enabled`, `stage_name_en`, `stage_name_gu`, `sequence_order`, `is_parallel`, `parent_stage_key`, `stage_type`, `description_en`, `description_gu`, `default_role`, `sub_actions`, `created_at`, `updated_at`) VALUES
(1, 'inquiry', 1, 'Loan Inquiry', 'Loan Inquiry', 1, 0, NULL, 'sequential', 'Initial customer and loan details entry', NULL, '[\"branch_manager\",\"loan_advisor\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(2, 'document_selection', 1, 'Document Selection', 'Document Selection', 2, 0, NULL, 'sequential', 'Select required documents for the loan', NULL, '[\"branch_manager\",\"loan_advisor\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(3, 'document_collection', 1, 'Document Collection', 'Document Collection', 3, 0, NULL, 'sequential', 'Collect and verify all required documents', NULL, '[\"branch_manager\",\"loan_advisor\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(4, 'parallel_processing', 1, 'Parallel Processing', 'Parallel Processing', 4, 1, NULL, 'parallel', 'Four parallel tracks processed simultaneously', NULL, NULL, NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(5, 'app_number', 1, 'Application Number', 'Application Number', 4, 0, 'parallel_processing', 'sequential', 'Enter bank application number', NULL, '[\"branch_manager\",\"loan_advisor\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(6, 'bsm_osv', 1, 'BSM/OSV Approval', 'BSM/OSV Approval', 4, 0, 'parallel_processing', 'sequential', 'Bank site and office verification', NULL, '[\"bank_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(7, 'sanction_decision', 1, 'Loan Sanction Decision', 'લોન મંજૂરી નિર્ણય', 4, 0, 'parallel_processing', 'sequential', 'Loan sanction approval with escalation ladder', NULL, '[\"office_employee\",\"branch_manager\",\"bdh\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(8, 'legal_verification', 1, 'Legal Verification', 'Legal Verification', 4, 0, 'parallel_processing', 'sequential', 'Legal document verification', NULL, '[\"branch_manager\",\"loan_advisor\",\"bank_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(9, 'property_valuation', 1, 'Property Valuation', 'Property Valuation', 4, 0, 'parallel_processing', 'sequential', 'Dedicated property valuation for LAP', NULL, '[\"branch_manager\",\"office_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(10, 'technical_valuation', 1, 'Technical Valuation', 'Technical Valuation', 4, 0, 'parallel_processing', 'sequential', 'Property/asset technical valuation', NULL, '[\"branch_manager\",\"office_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(11, 'rate_pf', 1, 'Rate & PF Request', 'Rate & PF Request', 5, 0, NULL, 'sequential', 'Request interest rate and processing fee from bank', NULL, '[\"branch_manager\",\"loan_advisor\",\"bank_employee\"]', '[{\"key\":\"bank_rate_details\",\"name\":\"Bank Rate Details\",\"sequence\":1,\"roles\":[\"bank_employee\"],\"type\":\"form\",\"is_enabled\":true},{\"key\":\"processing_charges\",\"name\":\"Processing & Charges\",\"sequence\":2,\"roles\":[\"branch_manager\",\"loan_advisor\",\"office_employee\"],\"type\":\"form\",\"is_enabled\":true}]', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(12, 'sanction', 1, 'Sanction Letter', 'Sanction Letter', 6, 0, NULL, 'sequential', 'Bank issues sanction letter', NULL, '[\"branch_manager\",\"loan_advisor\",\"bank_employee\"]', '[{\"key\":\"send_for_sanction\",\"name\":\"Send for Sanction Letter\",\"sequence\":1,\"roles\":[\"branch_manager\",\"loan_advisor\"],\"type\":\"action_button\",\"action\":\"send_for_sanction\",\"transfer_to_role\":\"bank_employee\",\"is_enabled\":true},{\"key\":\"sanction_generated\",\"name\":\"Sanction Letter Generated\",\"sequence\":2,\"roles\":[\"bank_employee\"],\"type\":\"action_button\",\"action\":\"sanction_generated\",\"transfer_to_role\":\"loan_advisor\",\"is_enabled\":true},{\"key\":\"sanction_details\",\"name\":\"Sanction Details\",\"sequence\":3,\"roles\":[\"branch_manager\",\"loan_advisor\"],\"type\":\"form\",\"is_enabled\":true}]', '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(13, 'docket', 1, 'Docket Login', 'Docket Login', 7, 0, NULL, 'sequential', 'Physical document processing and docket creation', NULL, '[\"branch_manager\",\"loan_advisor\",\"office_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(14, 'kfs', 1, 'KFS Generation', 'KFS Generation', 8, 0, NULL, 'sequential', 'Key Fact Statement generation', NULL, '[\"branch_manager\",\"loan_advisor\",\"office_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(15, 'esign', 1, 'E-Sign & eNACH', 'E-Sign & eNACH', 9, 0, NULL, 'sequential', 'Digital signature and eNACH mandate', NULL, '[\"branch_manager\",\"loan_advisor\",\"bank_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(16, 'disbursement', 1, 'Disbursement', 'Disbursement', 10, 0, NULL, 'decision', 'Fund disbursement - transfer or cheque with OTC handling', NULL, '[\"branch_manager\",\"loan_advisor\",\"office_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34'),
(17, 'otc_clearance', 1, 'OTC Clearance', 'OTC Clearance', 11, 0, NULL, 'sequential', 'Cheque handover and OTC clearance', NULL, '[\"branch_manager\",\"loan_advisor\",\"office_employee\"]', NULL, '2026-04-15 16:46:34', '2026-04-15 16:46:34');

-- --------------------------------------------------------

--
-- Table structure for table `stage_assignments`
--

DROP TABLE IF EXISTS `stage_assignments`;
CREATE TABLE `stage_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `stage_key` varchar(255) NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `previous_status` varchar(255) DEFAULT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'normal',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `is_parallel_stage` tinyint(1) NOT NULL DEFAULT 0,
  `parent_stage_key` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `stage_assignments`
--

TRUNCATE TABLE `stage_assignments`;
--
-- Dumping data for table `stage_assignments`
--

INSERT INTO `stage_assignments` (`id`, `loan_id`, `stage_key`, `assigned_to`, `status`, `previous_status`, `priority`, `started_at`, `completed_at`, `completed_by`, `is_parallel_stage`, `parent_stage_key`, `notes`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 1, 'inquiry', NULL, 'completed', NULL, 'normal', '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(2, 1, 'document_selection', NULL, 'completed', NULL, 'normal', '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(3, 1, 'document_collection', 6, 'completed', NULL, 'normal', '2026-04-15 16:46:37', '2026-04-16 13:08:04', 6, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-16 13:08:04', 6),
(4, 1, 'parallel_processing', NULL, 'in_progress', NULL, 'normal', '2026-04-16 13:08:04', NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-16 13:08:04', 6),
(5, 1, 'app_number', 6, 'completed', NULL, 'normal', '2026-04-16 13:08:04', '2026-04-16 13:09:20', 6, 1, 'parallel_processing', '{\"application_number\":\"123123123\",\"docket_days_offset\":\"3\",\"custom_docket_date\":null,\"stageRemarks\":null}', '2026-04-15 16:46:37', '2026-04-16 13:09:20', 6),
(6, 1, 'bsm_osv', 21, 'completed', NULL, 'normal', '2026-04-16 13:09:20', '2026-04-16 13:09:48', 21, 1, 'parallel_processing', NULL, '2026-04-15 16:46:37', '2026-04-16 13:09:48', 21),
(7, 1, 'sanction_decision', 23, 'completed', NULL, 'normal', '2026-04-16 13:09:48', '2026-04-16 13:10:23', 23, 1, 'parallel_processing', '{\"decision_action\":\"approved\",\"decided_by\":23}', '2026-04-15 16:46:37', '2026-04-16 13:10:23', 23),
(8, 1, 'legal_verification', 6, 'completed', NULL, 'normal', '2026-04-16 13:09:48', '2026-04-16 13:12:08', 6, 1, 'parallel_processing', '{\"legal_phase\":\"3\",\"legal_original_assignee\":6,\"suggested_legal_advisor\":\"RAHUL SHAH\",\"confirmed_legal_advisor\":\"RAHUL SHAH\"}', '2026-04-15 16:46:37', '2026-04-16 13:12:08', 6),
(9, 1, 'technical_valuation', 23, 'in_progress', NULL, 'normal', '2026-04-16 13:09:48', NULL, NULL, 1, 'parallel_processing', '{\"tv_phase\":\"2\",\"tv_original_assignee\":6}', '2026-04-15 16:46:37', '2026-04-16 13:10:55', 6),
(10, 1, 'rate_pf', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(11, 1, 'sanction', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(12, 1, 'docket', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(13, 1, 'kfs', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(14, 1, 'esign', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(15, 1, 'disbursement', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(16, 1, 'otc_clearance', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-15 16:46:37', '2026-04-15 16:46:37', 1),
(17, 2, 'inquiry', NULL, 'completed', NULL, 'normal', '2026-04-16 09:30:56', '2026-04-16 09:30:56', 13, 0, NULL, NULL, '2026-04-16 09:30:56', '2026-04-16 09:30:56', 13),
(18, 2, 'document_selection', NULL, 'completed', NULL, 'normal', '2026-04-16 09:30:56', '2026-04-16 09:30:56', 13, 0, NULL, NULL, '2026-04-16 09:30:56', '2026-04-16 09:30:56', 13),
(19, 2, 'document_collection', 13, 'completed', NULL, 'normal', '2026-04-16 09:30:56', '2026-04-16 09:31:15', 13, 0, NULL, NULL, '2026-04-16 09:30:56', '2026-04-16 09:31:15', 13),
(20, 2, 'parallel_processing', NULL, 'completed', NULL, 'normal', '2026-04-16 09:31:15', '2026-04-16 09:34:22', 13, 0, NULL, NULL, '2026-04-16 09:30:56', '2026-04-16 09:34:22', 13),
(21, 2, 'app_number', 13, 'completed', NULL, 'normal', '2026-04-16 09:31:15', '2026-04-16 09:31:42', 13, 1, 'parallel_processing', '{\"application_number\":\"77211545614\",\"docket_days_offset\":\"0\",\"custom_docket_date\":\"29\\/04\\/2026\",\"stageRemarks\":null}', '2026-04-16 09:30:56', '2026-04-16 09:31:42', 13),
(22, 2, 'bsm_osv', 21, 'completed', NULL, 'normal', '2026-04-16 09:31:42', '2026-04-16 09:32:16', 21, 1, 'parallel_processing', NULL, '2026-04-16 09:30:56', '2026-04-16 09:32:16', 21),
(23, 2, 'sanction_decision', 23, 'completed', NULL, 'normal', '2026-04-16 09:32:16', '2026-04-16 09:33:21', 23, 1, 'parallel_processing', '{\"decision_action\":\"approved\",\"decided_by\":23}', '2026-04-16 09:30:56', '2026-04-16 09:33:21', 23),
(24, 2, 'legal_verification', 13, 'completed', NULL, 'normal', '2026-04-16 09:32:16', '2026-04-16 09:34:22', 13, 1, 'parallel_processing', '{\"legal_phase\":\"3\",\"legal_original_assignee\":13,\"suggested_legal_advisor\":\"vijay dave\",\"confirmed_legal_advisor\":\"vijay dave\"}', '2026-04-16 09:30:56', '2026-04-16 09:34:22', 13),
(25, 2, 'technical_valuation', 23, 'completed', NULL, 'normal', '2026-04-16 09:32:16', '2026-04-16 09:34:01', 23, 1, 'parallel_processing', '{\"tv_phase\":\"2\",\"tv_original_assignee\":13}', '2026-04-16 09:30:56', '2026-04-16 09:34:01', 23),
(26, 2, 'rate_pf', 13, 'completed', NULL, 'normal', '2026-04-16 09:34:22', '2026-04-16 09:35:53', 13, 0, NULL, '{\"interest_rate\":\"7.00\",\"repo_rate\":\"5\",\"bank_rate\":\"2.00\",\"rate_offered_date\":\"16\\/04\\/2026\",\"rate_valid_until\":\"01\\/05\\/2026\",\"bank_reference\":null,\"processing_fee_type\":\"percent\",\"processing_fee\":\"0.10\",\"processing_fee_amount\":\"20000\",\"gst_percent\":\"18\",\"pf_gst_amount\":\"3600\",\"total_pf\":\"23600\",\"admin_charges\":\"0\",\"admin_charges_gst_percent\":\"18\",\"admin_charges_gst_amount\":\"0\",\"total_admin_charges\":\"0\",\"special_conditions\":null,\"stageRemarks\":null,\"rate_pf_phase\":\"3\",\"rate_pf_original_assignee\":13,\"original_values\":{\"interest_rate\":\"6.00\",\"repo_rate\":\"5\",\"bank_rate\":\"1.00\",\"rate_offered_date\":\"16\\/04\\/2026\",\"rate_valid_until\":\"01\\/05\\/2026\",\"processing_fee_type\":\"percent\",\"processing_fee\":\"0.10\",\"gst_percent\":\"18\",\"admin_charges\":\"0\",\"admin_charges_gst_percent\":\"18\",\"processing_fee_amount\":\"20000\",\"pf_gst_amount\":\"3600\",\"total_pf\":\"23600\",\"admin_charges_gst_amount\":\"0\",\"total_admin_charges\":\"0\",\"bank_reference\":\"\",\"special_conditions\":\"\"}}', '2026-04-16 09:30:56', '2026-04-16 09:35:53', 13),
(27, 2, 'sanction', 13, 'completed', NULL, 'normal', '2026-04-16 09:35:53', '2026-04-16 09:36:31', 13, 0, NULL, '{\"sanction_phase\":\"3\",\"sanction_original_assignee\":13,\"sanction_date\":\"16\\/04\\/2026\",\"sanctioned_amount\":\"20000000\",\"sanctioned_rate\":\"7.00\",\"tenure_months\":\"240\",\"emi_amount\":\"155060\",\"conditions\":null,\"stageRemarks\":null}', '2026-04-16 09:30:56', '2026-04-16 09:36:31', 13),
(28, 2, 'docket', 23, 'completed', NULL, 'normal', '2026-04-16 09:36:31', '2026-04-16 09:37:24', 23, 0, NULL, '{\"stageRemarks\":null,\"docket_phase\":\"2\",\"docket_original_assignee\":13,\"login_date\":\"16\\/04\\/2026\"}', '2026-04-16 09:30:56', '2026-04-16 09:37:24', 23),
(29, 2, 'kfs', 13, 'completed', NULL, 'normal', '2026-04-16 09:37:24', '2026-04-16 09:37:31', 13, 0, NULL, NULL, '2026-04-16 09:30:56', '2026-04-16 09:37:31', 13),
(30, 2, 'esign', 21, 'completed', NULL, 'normal', '2026-04-16 09:37:31', '2026-04-16 09:38:07', 21, 0, NULL, '{\"esign_phase\":\"4\",\"esign_original_assignee\":13,\"esign_bank_employee\":21}', '2026-04-16 09:30:56', '2026-04-16 09:38:07', 21),
(31, 2, 'disbursement', 23, 'completed', NULL, 'normal', '2026-04-16 09:38:07', '2026-04-16 09:38:52', 23, 0, NULL, NULL, '2026-04-16 09:30:56', '2026-04-16 09:38:52', 23),
(32, 2, 'otc_clearance', 13, 'completed', NULL, 'normal', '2026-04-16 09:38:52', '2026-04-16 09:39:43', 13, 0, NULL, '{\"handover_date\":\"16\\/04\\/2026\",\"stageRemarks\":null}', '2026-04-16 09:30:56', '2026-04-16 09:39:43', 13),
(33, 3, 'inquiry', NULL, 'completed', NULL, 'normal', '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(34, 3, 'document_selection', NULL, 'completed', NULL, 'normal', '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(35, 3, 'document_collection', 3, 'rejected', 'in_progress', 'normal', '2026-04-16 09:56:09', '2026-04-16 10:10:15', 3, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 10:10:15', 3),
(36, 3, 'parallel_processing', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(37, 3, 'app_number', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 1, 'parallel_processing', NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(38, 3, 'bsm_osv', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 1, 'parallel_processing', NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(39, 3, 'sanction_decision', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 1, 'parallel_processing', NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(40, 3, 'legal_verification', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 1, 'parallel_processing', NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(41, 3, 'technical_valuation', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 1, 'parallel_processing', NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(42, 3, 'rate_pf', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(43, 3, 'sanction', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(44, 3, 'docket', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(45, 3, 'kfs', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(46, 3, 'esign', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(47, 3, 'disbursement', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(48, 3, 'otc_clearance', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 09:56:09', '2026-04-16 09:56:09', 3),
(49, 4, 'inquiry', NULL, 'completed', NULL, 'normal', '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6),
(50, 4, 'document_selection', NULL, 'completed', NULL, 'normal', '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6),
(51, 4, 'document_collection', 6, 'completed', NULL, 'normal', '2026-04-16 13:23:07', '2026-04-16 13:23:22', 6, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:22', 6),
(52, 4, 'parallel_processing', NULL, 'completed', NULL, 'normal', '2026-04-16 13:23:22', '2026-04-16 13:25:26', 23, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:25:26', 23),
(53, 4, 'app_number', 6, 'completed', NULL, 'normal', '2026-04-16 13:23:22', '2026-04-16 13:23:34', 6, 1, 'parallel_processing', '{\"application_number\":\"7721215454\",\"docket_days_offset\":\"2\",\"custom_docket_date\":null,\"stageRemarks\":null}', '2026-04-16 13:23:07', '2026-04-16 13:23:34', 6),
(54, 4, 'bsm_osv', 21, 'completed', NULL, 'normal', '2026-04-16 13:23:34', '2026-04-16 13:23:49', 21, 1, 'parallel_processing', NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:49', 21),
(55, 4, 'sanction_decision', 23, 'completed', NULL, 'normal', '2026-04-16 13:23:49', '2026-04-16 13:23:59', 23, 1, 'parallel_processing', '{\"decision_action\":\"approved\",\"decided_by\":23}', '2026-04-16 13:23:07', '2026-04-16 13:23:59', 23),
(56, 4, 'legal_verification', 6, 'completed', NULL, 'normal', '2026-04-16 13:23:49', '2026-04-16 13:24:18', 6, 1, 'parallel_processing', '{\"legal_phase\":\"3\",\"legal_original_assignee\":6,\"suggested_legal_advisor\":\"rahul shah\",\"confirmed_legal_advisor\":\"rahul shah\"}', '2026-04-16 13:23:07', '2026-04-16 13:24:18', 6),
(57, 4, 'technical_valuation', 23, 'completed', NULL, 'normal', '2026-04-16 13:23:49', '2026-04-16 13:25:26', 23, 1, 'parallel_processing', '{\"tv_phase\":\"2\",\"tv_original_assignee\":6}', '2026-04-16 13:23:07', '2026-04-16 13:25:26', 23),
(58, 4, 'rate_pf', 6, 'in_progress', NULL, 'normal', '2026-04-16 13:25:26', NULL, NULL, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:25:26', 23),
(59, 4, 'sanction', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6),
(60, 4, 'docket', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6),
(61, 4, 'kfs', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6),
(62, 4, 'esign', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6),
(63, 4, 'disbursement', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6),
(64, 4, 'otc_clearance', NULL, 'pending', NULL, 'normal', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-16 13:23:07', '2026-04-16 13:23:07', 6);

-- --------------------------------------------------------

--
-- Table structure for table `stage_queries`
--

DROP TABLE IF EXISTS `stage_queries`;
CREATE TABLE `stage_queries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stage_assignment_id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `stage_key` varchar(255) NOT NULL,
  `query_text` text NOT NULL,
  `raised_by` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `stage_queries`
--

TRUNCATE TABLE `stage_queries`;
--
-- Dumping data for table `stage_queries`
--

INSERT INTO `stage_queries` (`id`, `stage_assignment_id`, `loan_id`, `stage_key`, `query_text`, `raised_by`, `status`, `resolved_at`, `resolved_by`, `created_at`, `updated_at`) VALUES
(1, 9, 1, 'technical_valuation', 'Hdiifvd', 23, 'resolved', '2026-04-16 13:21:07', 23, '2026-04-16 13:20:57', '2026-04-16 13:21:07');

-- --------------------------------------------------------

--
-- Table structure for table `stage_transfers`
--

DROP TABLE IF EXISTS `stage_transfers`;
CREATE TABLE `stage_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stage_assignment_id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `stage_key` varchar(255) NOT NULL,
  `transferred_from` bigint(20) UNSIGNED NOT NULL,
  `transferred_to` bigint(20) UNSIGNED NOT NULL,
  `reason` text DEFAULT NULL,
  `transfer_type` varchar(255) NOT NULL DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `stage_transfers`
--

TRUNCATE TABLE `stage_transfers`;
--
-- Dumping data for table `stage_transfers`
--

INSERT INTO `stage_transfers` (`id`, `stage_assignment_id`, `loan_id`, `stage_key`, `transferred_from`, `transferred_to`, `reason`, `transfer_type`, `created_at`) VALUES
(1, 3, 1, 'document_collection', 1, 6, 'Auto-assigned on stage advance', 'auto', '2026-04-15 16:46:37'),
(2, 19, 2, 'document_collection', 13, 13, 'Auto-assigned on stage advance', 'auto', '2026-04-16 09:30:56'),
(3, 24, 2, 'legal_verification', 21, 13, 'Auto-assigned to task owner for legal verification', 'auto', '2026-04-16 09:32:16'),
(4, 25, 2, 'technical_valuation', 21, 13, 'Auto-assigned to task owner for technical valuation', 'auto', '2026-04-16 09:32:16'),
(5, 24, 2, 'legal_verification', 13, 21, 'Sent to bank for legal verification', 'manual', '2026-04-16 09:32:56'),
(6, 25, 2, 'technical_valuation', 13, 23, 'Sent for technical valuation', 'manual', '2026-04-16 09:33:06'),
(7, 24, 2, 'legal_verification', 21, 13, 'Legal initiated, transferred back to task owner', 'manual', '2026-04-16 09:34:11'),
(8, 26, 2, 'rate_pf', 13, 13, 'Auto-assigned to task owner', 'auto', '2026-04-16 09:34:22'),
(9, 26, 2, 'rate_pf', 13, 21, 'Sent for bank rate review', 'manual', '2026-04-16 09:35:07'),
(10, 26, 2, 'rate_pf', 21, 13, 'Bank reviewed rate details, returned to task owner', 'manual', '2026-04-16 09:35:35'),
(11, 27, 2, 'sanction', 13, 13, 'Auto-assigned to task owner', 'auto', '2026-04-16 09:35:53'),
(12, 27, 2, 'sanction', 13, 21, 'Sent for sanction letter generation', 'manual', '2026-04-16 09:36:01'),
(13, 27, 2, 'sanction', 21, 13, 'Sanction letter generated', 'manual', '2026-04-16 09:36:09'),
(14, 28, 2, 'docket', 13, 13, 'Auto-assigned to task owner', 'auto', '2026-04-16 09:36:31'),
(15, 28, 2, 'docket', 13, 23, 'Sent for docket login', 'manual', '2026-04-16 09:36:46'),
(16, 29, 2, 'kfs', 23, 13, 'Auto-assigned to task owner', 'auto', '2026-04-16 09:37:24'),
(17, 30, 2, 'esign', 13, 13, 'Auto-assigned to task owner', 'auto', '2026-04-16 09:37:31'),
(18, 30, 2, 'esign', 13, 21, 'Sent for E-Sign & eNACH generation', 'manual', '2026-04-16 09:37:40'),
(19, 30, 2, 'esign', 21, 13, 'E-Sign & eNACH generated, sent for customer completion', 'manual', '2026-04-16 09:37:49'),
(20, 30, 2, 'esign', 13, 21, 'E-Sign completed with customer, returned to bank', 'manual', '2026-04-16 09:38:02'),
(21, 31, 2, 'disbursement', 21, 23, 'Auto-assigned to office employee', 'auto', '2026-04-16 09:38:07'),
(22, 32, 2, 'otc_clearance', 23, 13, 'Auto-assigned to task owner', 'auto', '2026-04-16 09:38:52'),
(23, 35, 3, 'document_collection', 3, 3, 'Auto-assigned on stage advance', 'auto', '2026-04-16 09:56:09'),
(24, 8, 1, 'legal_verification', 21, 6, 'Auto-assigned to task owner for legal verification', 'auto', '2026-04-16 13:09:48'),
(25, 9, 1, 'technical_valuation', 21, 6, 'Auto-assigned to task owner for technical valuation', 'auto', '2026-04-16 13:09:48'),
(26, 8, 1, 'legal_verification', 6, 21, 'Sent to bank for legal verification', 'manual', '2026-04-16 13:10:38'),
(27, 8, 1, 'legal_verification', 21, 6, 'Legal initiated, transferred back to task owner', 'manual', '2026-04-16 13:10:44'),
(28, 9, 1, 'technical_valuation', 6, 23, 'Sent for technical valuation', 'manual', '2026-04-16 13:10:55'),
(29, 51, 4, 'document_collection', 6, 6, 'Auto-assigned on stage advance', 'auto', '2026-04-16 13:23:07'),
(30, 56, 4, 'legal_verification', 21, 6, 'Auto-assigned to task owner for legal verification', 'auto', '2026-04-16 13:23:49'),
(31, 57, 4, 'technical_valuation', 21, 6, 'Auto-assigned to task owner for technical valuation', 'auto', '2026-04-16 13:23:49'),
(32, 56, 4, 'legal_verification', 6, 21, 'Sent to bank for legal verification', 'manual', '2026-04-16 13:24:03'),
(33, 57, 4, 'technical_valuation', 6, 23, 'Sent for technical valuation', 'manual', '2026-04-16 13:24:07'),
(34, 56, 4, 'legal_verification', 21, 6, 'Legal initiated, transferred back to task owner', 'manual', '2026-04-16 13:24:09'),
(35, 58, 4, 'rate_pf', 23, 6, 'Auto-assigned to task owner', 'auto', '2026-04-16 13:25:26');

-- --------------------------------------------------------

--
-- Table structure for table `task_role_permissions`
--

DROP TABLE IF EXISTS `task_role_permissions`;
CREATE TABLE `task_role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_role` varchar(255) NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `task_role_permissions`
--

TRUNCATE TABLE `task_role_permissions`;
-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `default_branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `task_bank_id` bigint(20) UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `users`
--

TRUNCATE TABLE `users`;
--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_active`, `created_by`, `phone`, `employee_id`, `default_branch_id`, `task_bank_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@shfworld.com', NULL, '$2y$12$yh3YunN1crCLzJRHcf5XuuRc/Ha0R5a.brls6cNwnLl4NGyBRmUxm', 1, NULL, '+91 99747 89089', NULL, 1, NULL, 'Dt1Vxwz4tMbh6P3WTFIOY955uPzoopdaGvWjpRhSv2xZxQjCnvyEGhwcwVDS', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(2, 'Admin', 'admin@shfworld.com', NULL, '$2y$12$yh3YunN1crCLzJRHcf5XuuRc/Ha0R5a.brls6cNwnLl4NGyBRmUxm', 1, NULL, '+91 99747 89089', NULL, 1, NULL, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(3, 'HARDIK NASIT', 'hardik@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '9726179351', NULL, 1, NULL, '2IPikeUhRayNY1Od0ZmE0FcF2LnvImTnv59NH4P00bCSNv1nlh7IEqqy9cHF', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(4, 'KRUPALI SHILU', 'krupali@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '9099089072', NULL, 1, NULL, 'vrRpFu0Xqwe3EEnA5T60R5YEz1QemIddqR8zEJoHnt4JnywcNGfJTYPgICu9', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(5, 'Denish BDH', 'denish@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, NULL, 'HJaBQaibj3pCHvwmEyXBgb2Z4WkS3RUu30m3Zz7FlpBWRTsVPXmIFsx6r2hF', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(6, 'JAYDEEP THESHIYA', 'jaydeep@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '9725248300', NULL, 1, NULL, '7ad1Nqecvt9R4EeJr2loOUXMcSOy8heacx8xDPR4RQwcvWrdryrcxThMo1SM', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(7, 'KULDEEP VAISHNAV', 'kuldeep@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '8866236688', NULL, 1, NULL, 'dKPKpZvrJrCxdfsvjOD2MwyPGWq55zpgMkEEEicgr263iABOIhoaSyYeM17C', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(8, 'RAHUL MARAKANA', 'rahul@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '9913744162', NULL, 1, NULL, 'fdlUVh2Day73aUlLHs4sVSzMrTwqk08qqFSQnvWTttED8Rdy5BX0r4talTPG', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(9, 'DIPAK VIRANI', 'dipak@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '7600143537', NULL, 1, NULL, '4txETd8NJlmhTTUoyzUf8oPkwW2tPpXvAREhnzvTNxCU88WGMdsGA77HbkNO', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(10, 'JAYESH MORI', 'jayesh@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '8000232586', NULL, 1, NULL, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(11, 'CHIRAG DHOLAKIYA', 'chirag@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '9016348138', NULL, 1, NULL, 'tSOUWVcZSBsQ3qN7F3T1t12VtBrv4YRYskjiVfe3yI70awsV3CRP8FYz6mRx', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(12, 'DAXIT MALAVIYA', 'daxit@shfworld.som', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '81600000286', NULL, 1, NULL, 'dGTaI6SzPEdHa9rtW7xGcGRj2kJc8KPIr6ySxHLvk6CSBAZI5qKQ0JSG7Ebe', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(13, 'MILAN DHOLAKIYA', 'milan@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '8401277654', NULL, 1, NULL, '73q84RBq30CUQaOYgE3sYaJ7Nh0Z1LnxKuqJ2ulCO5gi4yDXUxFVo8q59tgW', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(14, 'NITIN FALDU', 'nitin@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '968701525', NULL, 1, NULL, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(15, 'PARTH VORA', 'parth.axis@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 3, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(16, 'KARTIK PARIKH', 'kartik.axis@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 3, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(17, 'MAYAN PANSURIYA', 'mayan.axis@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 3, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(18, 'BHARGAV VIRANI', 'bhargav@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '6355717561', NULL, 1, NULL, 'jW1y2MEkmtfHp7AX7bS7ddjhjBPULFVP13sMVk7OTu7xWUDMjAzUZeXCxRe3', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(19, 'PRATIK GADHIYA', 'pratik.hdfc@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 1, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(20, 'RAKSHIT GANDHI', 'rakshit.hdfc@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 1, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(21, 'RUSHIKA JADAV', 'rushika.icici@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 2, 'dnSNTDYJIw2UDbfRUNJXbePz0hQsT5WVsuwjS9rqn587kSrEiu8Nb2bCuhG1', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(22, 'AVINASH PANDYA', 'avinash.icici@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 2, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(23, 'MANTHAN THUMMAR', 'manthan@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '9904408239', NULL, 1, NULL, 'GLJGJkRgMEbWA0qbR6MTpCWTJglhlvpHvta66TITYZUHGkme5hRYxDPTVwJ7', '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(24, 'VISHAL VYAS', 'vishal.kotak@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 4, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(25, 'JAYDIP KARMATA', 'jaydeep.kotak@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 1, NULL, NULL, 1, 4, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35'),
(26, 'HARSHIT NAKUM', 'harshit@shfworld.com', NULL, '$2y$12$MkYT274c/5fYyIDzlla9R.06b6Y99apTIG1CW19YzUW4PDNG32UtS', 1, 2, '8511381814', NULL, 1, NULL, NULL, '2026-04-15 16:46:35', '2026-04-15 16:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_branches`
--

DROP TABLE IF EXISTS `user_branches`;
CREATE TABLE `user_branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `is_default_office_employee` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `user_branches`
--

TRUNCATE TABLE `user_branches`;
--
-- Dumping data for table `user_branches`
--

INSERT INTO `user_branches` (`id`, `user_id`, `branch_id`, `is_default_office_employee`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, NULL, NULL),
(2, 2, 1, 0, NULL, NULL),
(3, 3, 1, 0, NULL, NULL),
(4, 4, 1, 0, NULL, NULL),
(5, 5, 1, 0, NULL, NULL),
(6, 6, 1, 0, NULL, NULL),
(7, 7, 1, 0, NULL, NULL),
(8, 8, 1, 0, NULL, NULL),
(9, 9, 1, 0, NULL, NULL),
(10, 10, 1, 0, NULL, NULL),
(11, 11, 1, 0, NULL, NULL),
(12, 12, 1, 0, NULL, NULL),
(13, 13, 1, 0, NULL, NULL),
(14, 14, 1, 0, NULL, NULL),
(15, 15, 1, 0, NULL, NULL),
(16, 16, 1, 0, NULL, NULL),
(17, 17, 1, 0, NULL, NULL),
(18, 18, 1, 0, NULL, NULL),
(19, 19, 1, 0, NULL, NULL),
(20, 20, 1, 0, NULL, NULL),
(21, 21, 1, 0, NULL, NULL),
(22, 22, 1, 0, NULL, NULL),
(23, 23, 1, 0, NULL, NULL),
(24, 24, 1, 0, NULL, NULL),
(25, 25, 1, 0, NULL, NULL),
(26, 26, 1, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('grant','deny') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `user_permissions`
--

TRUNCATE TABLE `user_permissions`;
-- --------------------------------------------------------

--
-- Table structure for table `valuation_details`
--

DROP TABLE IF EXISTS `valuation_details`;
CREATE TABLE `valuation_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loan_id` bigint(20) UNSIGNED NOT NULL,
  `valuation_type` varchar(255) NOT NULL DEFAULT 'property',
  `property_address` text DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `property_type` varchar(255) DEFAULT NULL,
  `land_area` varchar(255) DEFAULT NULL,
  `land_rate` decimal(12,2) DEFAULT NULL,
  `land_valuation` bigint(20) UNSIGNED DEFAULT NULL,
  `construction_area` varchar(255) DEFAULT NULL,
  `construction_rate` decimal(12,2) DEFAULT NULL,
  `construction_valuation` bigint(20) UNSIGNED DEFAULT NULL,
  `final_valuation` bigint(20) UNSIGNED DEFAULT NULL,
  `market_value` bigint(20) UNSIGNED DEFAULT NULL,
  `government_value` bigint(20) UNSIGNED DEFAULT NULL,
  `valuation_date` date DEFAULT NULL,
  `valuator_name` varchar(255) DEFAULT NULL,
  `valuator_report_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Truncate table before insert `valuation_details`
--

TRUNCATE TABLE `valuation_details`;
--
-- Dumping data for table `valuation_details`
--

INSERT INTO `valuation_details` (`id`, `loan_id`, `valuation_type`, `property_address`, `landmark`, `latitude`, `longitude`, `property_type`, `land_area`, `land_rate`, `land_valuation`, `construction_area`, `construction_rate`, `construction_valuation`, `final_valuation`, `market_value`, `government_value`, `valuation_date`, `valuator_name`, `valuator_report_number`, `notes`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 2, 'property', 'Ganjivad', 'Bhavnagar road', NULL, NULL, 'residential_flat', '12580', '21548.00', 271073840, NULL, NULL, 0, 271073840, 271073840, NULL, '2026-04-16', 'Hardik', NULL, NULL, '2026-04-16 09:34:01', '2026-04-16 09:34:01', 23),
(2, 4, 'property', 'Rajkot Taluka, Gujarat', 'Rajkot Taluka', '22.069288', '71.028869', 'residential_flat', '6380', '3000.00', 19140000, '550', '600.00', 330000, 19470000, 19470000, NULL, '2026-04-16', 'Manthan', NULL, NULL, '2026-04-16 13:25:26', '2026-04-16 13:25:26', 23);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `activity_logs_user_id_index` (`user_id`),
  ADD KEY `activity_logs_created_at_index` (`created_at`);

--
-- Indexes for table `app_config`
--
ALTER TABLE `app_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_config_config_key_unique` (`config_key`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `banks_name_unique` (`name`),
  ADD KEY `banks_updated_by_foreign` (`updated_by`),
  ADD KEY `banks_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `bank_charges`
--
ALTER TABLE `bank_charges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank_employees`
--
ALTER TABLE `bank_employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bank_employees_bank_user_location_unique` (`bank_id`,`user_id`,`location_id`),
  ADD KEY `bank_employees_location_id_foreign` (`location_id`),
  ADD KEY `bank_employees_user_id_foreign` (`user_id`);

--
-- Indexes for table `bank_location`
--
ALTER TABLE `bank_location`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bank_location_bank_id_location_id_unique` (`bank_id`,`location_id`),
  ADD KEY `bank_location_location_id_foreign` (`location_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_code_unique` (`code`),
  ADD KEY `branches_updated_by_foreign` (`updated_by`),
  ADD KEY `branches_deleted_by_foreign` (`deleted_by`),
  ADD KEY `branches_manager_id_foreign` (`manager_id`),
  ADD KEY `branches_location_id_foreign` (`location_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_created_by_foreign` (`created_by`),
  ADD KEY `customers_updated_by_foreign` (`updated_by`),
  ADD KEY `customers_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `daily_visit_reports`
--
ALTER TABLE `daily_visit_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_visit_reports_parent_visit_id_foreign` (`parent_visit_id`),
  ADD KEY `daily_visit_reports_follow_up_visit_id_foreign` (`follow_up_visit_id`),
  ADD KEY `daily_visit_reports_quotation_id_foreign` (`quotation_id`),
  ADD KEY `daily_visit_reports_loan_id_foreign` (`loan_id`),
  ADD KEY `daily_visit_reports_branch_id_foreign` (`branch_id`),
  ADD KEY `daily_visit_reports_user_id_visit_date_index` (`user_id`,`visit_date`),
  ADD KEY `daily_visit_reports_follow_up_date_index` (`follow_up_date`),
  ADD KEY `daily_visit_reports_follow_up_needed_is_follow_up_done_index` (`follow_up_needed`,`is_follow_up_done`);

--
-- Indexes for table `disbursement_details`
--
ALTER TABLE `disbursement_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `disbursement_details_loan_id_unique` (`loan_id`),
  ADD KEY `disbursement_details_otc_cleared_by_foreign` (`otc_cleared_by`),
  ADD KEY `disbursement_details_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `general_tasks`
--
ALTER TABLE `general_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `general_tasks_loan_detail_id_foreign` (`loan_detail_id`),
  ADD KEY `general_tasks_created_by_status_index` (`created_by`,`status`),
  ADD KEY `general_tasks_assigned_to_status_index` (`assigned_to`,`status`);

--
-- Indexes for table `general_task_comments`
--
ALTER TABLE `general_task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `general_task_comments_general_task_id_foreign` (`general_task_id`),
  ADD KEY `general_task_comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loan_details`
--
ALTER TABLE `loan_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_details_loan_number_unique` (`loan_number`),
  ADD KEY `loan_details_quotation_id_foreign` (`quotation_id`),
  ADD KEY `loan_details_branch_id_foreign` (`branch_id`),
  ADD KEY `loan_details_bank_id_foreign` (`bank_id`),
  ADD KEY `loan_details_product_id_foreign` (`product_id`),
  ADD KEY `loan_details_assigned_bank_employee_foreign` (`assigned_bank_employee`),
  ADD KEY `loan_details_rejected_by_foreign` (`rejected_by`),
  ADD KEY `loan_details_created_by_foreign` (`created_by`),
  ADD KEY `loan_details_assigned_advisor_foreign` (`assigned_advisor`),
  ADD KEY `loan_details_status_index` (`status`),
  ADD KEY `loan_details_current_stage_index` (`current_stage`),
  ADD KEY `loan_details_customer_type_index` (`customer_type`),
  ADD KEY `loan_details_updated_by_foreign` (`updated_by`),
  ADD KEY `loan_details_deleted_by_foreign` (`deleted_by`),
  ADD KEY `loan_details_location_id_foreign` (`location_id`),
  ADD KEY `loan_details_status_changed_by_foreign` (`status_changed_by`),
  ADD KEY `loan_details_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `loan_documents`
--
ALTER TABLE `loan_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loan_documents_received_by_foreign` (`received_by`),
  ADD KEY `loan_documents_loan_id_index` (`loan_id`),
  ADD KEY `loan_documents_loan_id_status_index` (`loan_id`,`status`),
  ADD KEY `loan_documents_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `loan_documents_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `loan_progress`
--
ALTER TABLE `loan_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loan_progress_loan_id_unique` (`loan_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `locations_name_parent_id_unique` (`name`,`parent_id`),
  ADD KEY `locations_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `location_product`
--
ALTER TABLE `location_product`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location_product_location_id_product_id_unique` (`location_id`,`product_id`),
  ADD KEY `location_product_product_id_foreign` (`product_id`);

--
-- Indexes for table `location_user`
--
ALTER TABLE `location_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `location_user_location_id_user_id_unique` (`location_id`,`user_id`),
  ADD KEY `location_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_bank_id_name_unique` (`bank_id`,`name`),
  ADD KEY `products_updated_by_foreign` (`updated_by`),
  ADD KEY `products_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `product_stages`
--
ALTER TABLE `product_stages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_stages_product_id_stage_id_unique` (`product_id`,`stage_id`),
  ADD KEY `product_stages_stage_id_foreign` (`stage_id`),
  ADD KEY `product_stages_updated_by_foreign` (`updated_by`),
  ADD KEY `product_stages_default_user_id_foreign` (`default_user_id`);

--
-- Indexes for table `product_stage_users`
--
ALTER TABLE `product_stage_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_stage_users_product_stage_id_branch_id_unique` (`product_stage_id`,`branch_id`),
  ADD KEY `product_stage_users_branch_id_foreign` (`branch_id`),
  ADD KEY `product_stage_users_user_id_foreign` (`user_id`),
  ADD KEY `product_stage_users_location_id_foreign` (`location_id`);

--
-- Indexes for table `query_responses`
--
ALTER TABLE `query_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `query_responses_stage_query_id_foreign` (`stage_query_id`),
  ADD KEY `query_responses_responded_by_foreign` (`responded_by`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotations_user_id_index` (`user_id`),
  ADD KEY `quotations_created_at_index` (`created_at`),
  ADD KEY `quotations_loan_id_foreign` (`loan_id`),
  ADD KEY `quotations_updated_by_foreign` (`updated_by`),
  ADD KEY `quotations_deleted_by_foreign` (`deleted_by`),
  ADD KEY `quotations_location_id_foreign` (`location_id`),
  ADD KEY `quotations_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `quotation_banks`
--
ALTER TABLE `quotation_banks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_banks_quotation_id_foreign` (`quotation_id`);

--
-- Indexes for table `quotation_documents`
--
ALTER TABLE `quotation_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_documents_quotation_id_foreign` (`quotation_id`);

--
-- Indexes for table `quotation_emi`
--
ALTER TABLE `quotation_emi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_emi_quotation_bank_id_foreign` (`quotation_bank_id`);

--
-- Indexes for table `remarks`
--
ALTER TABLE `remarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remarks_user_id_foreign` (`user_id`),
  ADD KEY `remarks_loan_id_index` (`loan_id`),
  ADD KEY `remarks_stage_key_index` (`stage_key`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permission_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shf_notifications`
--
ALTER TABLE `shf_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shf_notifications_user_id_is_read_index` (`user_id`,`is_read`),
  ADD KEY `shf_notifications_loan_id_index` (`loan_id`);

--
-- Indexes for table `stages`
--
ALTER TABLE `stages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stages_stage_key_unique` (`stage_key`),
  ADD KEY `stages_sequence_order_index` (`sequence_order`),
  ADD KEY `stages_parent_stage_key_index` (`parent_stage_key`);

--
-- Indexes for table `stage_assignments`
--
ALTER TABLE `stage_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stage_assignments_loan_id_stage_key_unique` (`loan_id`,`stage_key`),
  ADD KEY `stage_assignments_completed_by_foreign` (`completed_by`),
  ADD KEY `stage_assignments_stage_key_index` (`stage_key`),
  ADD KEY `stage_assignments_assigned_to_index` (`assigned_to`),
  ADD KEY `stage_assignments_status_index` (`status`),
  ADD KEY `stage_assignments_parent_stage_key_index` (`parent_stage_key`),
  ADD KEY `stage_assignments_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `stage_queries`
--
ALTER TABLE `stage_queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_queries_raised_by_foreign` (`raised_by`),
  ADD KEY `stage_queries_resolved_by_foreign` (`resolved_by`),
  ADD KEY `stage_queries_stage_assignment_id_status_index` (`stage_assignment_id`,`status`),
  ADD KEY `stage_queries_loan_id_index` (`loan_id`);

--
-- Indexes for table `stage_transfers`
--
ALTER TABLE `stage_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_transfers_stage_assignment_id_foreign` (`stage_assignment_id`),
  ADD KEY `stage_transfers_loan_id_foreign` (`loan_id`),
  ADD KEY `stage_transfers_transferred_from_foreign` (`transferred_from`),
  ADD KEY `stage_transfers_transferred_to_foreign` (`transferred_to`);

--
-- Indexes for table `task_role_permissions`
--
ALTER TABLE `task_role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_role_permissions_task_role_permission_id_unique` (`task_role`,`permission_id`),
  ADD KEY `task_role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_created_by_foreign` (`created_by`),
  ADD KEY `users_default_branch_id_foreign` (`default_branch_id`),
  ADD KEY `users_task_bank_id_foreign` (`task_bank_id`);

--
-- Indexes for table `user_branches`
--
ALTER TABLE `user_branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_branches_user_id_branch_id_unique` (`user_id`,`branch_id`),
  ADD KEY `user_branches_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_permissions_user_id_permission_id_unique` (`user_id`,`permission_id`),
  ADD KEY `user_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `valuation_details`
--
ALTER TABLE `valuation_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `valuation_details_loan_id_index` (`loan_id`),
  ADD KEY `valuation_details_updated_by_foreign` (`updated_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `app_config`
--
ALTER TABLE `app_config`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bank_charges`
--
ALTER TABLE `bank_charges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bank_employees`
--
ALTER TABLE `bank_employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `bank_location`
--
ALTER TABLE `bank_location`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `daily_visit_reports`
--
ALTER TABLE `daily_visit_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disbursement_details`
--
ALTER TABLE `disbursement_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_tasks`
--
ALTER TABLE `general_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `general_task_comments`
--
ALTER TABLE `general_task_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_details`
--
ALTER TABLE `loan_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loan_documents`
--
ALTER TABLE `loan_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `loan_progress`
--
ALTER TABLE `loan_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `location_product`
--
ALTER TABLE `location_product`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location_user`
--
ALTER TABLE `location_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_stages`
--
ALTER TABLE `product_stages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `product_stage_users`
--
ALTER TABLE `product_stage_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `query_responses`
--
ALTER TABLE `query_responses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quotation_banks`
--
ALTER TABLE `quotation_banks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quotation_documents`
--
ALTER TABLE `quotation_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `quotation_emi`
--
ALTER TABLE `quotation_emi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `remarks`
--
ALTER TABLE `remarks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `shf_notifications`
--
ALTER TABLE `shf_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stages`
--
ALTER TABLE `stages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `stage_assignments`
--
ALTER TABLE `stage_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `stage_queries`
--
ALTER TABLE `stage_queries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stage_transfers`
--
ALTER TABLE `stage_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `task_role_permissions`
--
ALTER TABLE `task_role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_branches`
--
ALTER TABLE `user_branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `valuation_details`
--
ALTER TABLE `valuation_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `banks`
--
ALTER TABLE `banks`
  ADD CONSTRAINT `banks_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `banks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bank_employees`
--
ALTER TABLE `bank_employees`
  ADD CONSTRAINT `bank_employees_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bank_employees_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bank_employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bank_location`
--
ALTER TABLE `bank_location`
  ADD CONSTRAINT `bank_location_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bank_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `branches_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `branches_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `branches_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_visit_reports`
--
ALTER TABLE `daily_visit_reports`
  ADD CONSTRAINT `daily_visit_reports_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_visit_reports_follow_up_visit_id_foreign` FOREIGN KEY (`follow_up_visit_id`) REFERENCES `daily_visit_reports` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_visit_reports_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_visit_reports_parent_visit_id_foreign` FOREIGN KEY (`parent_visit_id`) REFERENCES `daily_visit_reports` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_visit_reports_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_visit_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disbursement_details`
--
ALTER TABLE `disbursement_details`
  ADD CONSTRAINT `disbursement_details_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disbursement_details_otc_cleared_by_foreign` FOREIGN KEY (`otc_cleared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `disbursement_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `general_tasks`
--
ALTER TABLE `general_tasks`
  ADD CONSTRAINT `general_tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `general_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `general_tasks_loan_detail_id_foreign` FOREIGN KEY (`loan_detail_id`) REFERENCES `loan_details` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `general_task_comments`
--
ALTER TABLE `general_task_comments`
  ADD CONSTRAINT `general_task_comments_general_task_id_foreign` FOREIGN KEY (`general_task_id`) REFERENCES `general_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `general_task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loan_details`
--
ALTER TABLE `loan_details`
  ADD CONSTRAINT `loan_details_assigned_advisor_foreign` FOREIGN KEY (`assigned_advisor`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_assigned_bank_employee_foreign` FOREIGN KEY (`assigned_bank_employee`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_details_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_status_changed_by_foreign` FOREIGN KEY (`status_changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loan_documents`
--
ALTER TABLE `loan_documents`
  ADD CONSTRAINT `loan_documents_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loan_documents_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loan_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `loan_progress`
--
ALTER TABLE `loan_progress`
  ADD CONSTRAINT `loan_progress_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `location_product`
--
ALTER TABLE `location_product`
  ADD CONSTRAINT `location_product_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `location_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `location_user`
--
ALTER TABLE `location_user`
  ADD CONSTRAINT `location_user_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `location_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_stages`
--
ALTER TABLE `product_stages`
  ADD CONSTRAINT `product_stages_default_user_id_foreign` FOREIGN KEY (`default_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_stages_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_stages_stage_id_foreign` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_stages_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_stage_users`
--
ALTER TABLE `product_stage_users`
  ADD CONSTRAINT `product_stage_users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_stage_users_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_stage_users_product_stage_id_foreign` FOREIGN KEY (`product_stage_id`) REFERENCES `product_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_stage_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `query_responses`
--
ALTER TABLE `query_responses`
  ADD CONSTRAINT `query_responses_responded_by_foreign` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `query_responses_stage_query_id_foreign` FOREIGN KEY (`stage_query_id`) REFERENCES `stage_queries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_banks`
--
ALTER TABLE `quotation_banks`
  ADD CONSTRAINT `quotation_banks_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_documents`
--
ALTER TABLE `quotation_documents`
  ADD CONSTRAINT `quotation_documents_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_emi`
--
ALTER TABLE `quotation_emi`
  ADD CONSTRAINT `quotation_emi_quotation_bank_id_foreign` FOREIGN KEY (`quotation_bank_id`) REFERENCES `quotation_banks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remarks`
--
ALTER TABLE `remarks`
  ADD CONSTRAINT `remarks_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `remarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shf_notifications`
--
ALTER TABLE `shf_notifications`
  ADD CONSTRAINT `shf_notifications_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shf_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_assignments`
--
ALTER TABLE `stage_assignments`
  ADD CONSTRAINT `stage_assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stage_assignments_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stage_assignments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_assignments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stage_queries`
--
ALTER TABLE `stage_queries`
  ADD CONSTRAINT `stage_queries_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_queries_raised_by_foreign` FOREIGN KEY (`raised_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_queries_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stage_queries_stage_assignment_id_foreign` FOREIGN KEY (`stage_assignment_id`) REFERENCES `stage_assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_transfers`
--
ALTER TABLE `stage_transfers`
  ADD CONSTRAINT `stage_transfers_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_transfers_stage_assignment_id_foreign` FOREIGN KEY (`stage_assignment_id`) REFERENCES `stage_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_transfers_transferred_from_foreign` FOREIGN KEY (`transferred_from`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_transfers_transferred_to_foreign` FOREIGN KEY (`transferred_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_role_permissions`
--
ALTER TABLE `task_role_permissions`
  ADD CONSTRAINT `task_role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_default_branch_id_foreign` FOREIGN KEY (`default_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_task_bank_id_foreign` FOREIGN KEY (`task_bank_id`) REFERENCES `banks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_branches`
--
ALTER TABLE `user_branches`
  ADD CONSTRAINT `user_branches_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_branches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `valuation_details`
--
ALTER TABLE `valuation_details`
  ADD CONSTRAINT `valuation_details_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `loan_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `valuation_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
