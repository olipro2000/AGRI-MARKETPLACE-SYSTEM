-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 10:14 PM
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
-- Database: `curuzamuhinzi`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckAdminPermission` (IN `p_admin_id` BIGINT, IN `p_module` VARCHAR(50), IN `p_action` VARCHAR(20), OUT `p_has_permission` BOOLEAN)   BEGIN
    DECLARE
        v_department VARCHAR(50) ; DECLARE v_permissions JSON ; DECLARE v_module_permissions JSON ;
        -- Get admin department
    SELECT
        department
    INTO v_department
FROM
    admins
WHERE
    id = p_admin_id AND deleted_at IS NULL ;
    -- Super admin has all permissions
    IF v_department = 'super_admin' THEN
SET
    p_has_permission = TRUE ; ELSE
    -- Get department permissions
SELECT
    permissions
INTO v_permissions
FROM
    department_roles
WHERE
    department = v_department ;
    -- Extract module permissions
SET
    v_module_permissions = JSON_EXTRACT(
        v_permissions,
        CONCAT('$.', p_module)
    ) ;
    -- Check specific action permission
    IF v_module_permissions IS NOT NULL THEN
SET
    p_has_permission = JSON_EXTRACT(
        v_module_permissions,
        CONCAT('$.', p_action)
    ) = TRUE ; ELSE
SET
    p_has_permission = FALSE ;
END IF ;
        END IF ;
        END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `LogAdminActivity` (IN `p_admin_id` BIGINT, IN `p_action` VARCHAR(100), IN `p_module` VARCHAR(50), IN `p_target_type` VARCHAR(50), IN `p_target_id` BIGINT, IN `p_description` TEXT, IN `p_ip_address` VARCHAR(45), IN `p_user_agent` TEXT)   BEGIN
    INSERT INTO admin_activity_logs(
        admin_id,
        ACTION,
        module,
        target_type,
        target_id,
        description,
        ip_address,
        user_agent
    )
VALUES(
    p_admin_id,
    p_action,
    p_module,
    p_target_type,
    p_target_id,
    p_description,
    p_ip_address,
    p_user_agent
) ;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) NOT NULL,
  `UUID` varchar(36) DEFAULT uuid(),
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `department` enum('super_admin','management','operations','finance','support','technical','marketing') NOT NULL,
  `POSITION` varchar(100) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `STATUS` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `UUID`, `email`, `phone`, `password_hash`, `first_name`, `last_name`, `avatar_url`, `department`, `POSITION`, `employee_id`, `hire_date`, `salary`, `emergency_contact`, `address`, `STATUS`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `login_attempts`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1b035e81-cc7b-11f0-b7ce-98e7f429106a', 'superadmin@curuzamuhinzi.com', '+250788123456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Administrator', NULL, 'super_admin', 'Super Administrator', 'SA001', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', '2025-12-03 19:46:36', 0, '2025-11-28 16:55:55', '2025-12-03 19:46:36', NULL),
(2, '1b05abc3-cc7b-11f0-b7ce-98e7f429106a', 'ceo@curuzamuhinzi.com', '+250788123457', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean Baptiste', 'Uwimana', NULL, 'management', 'Chief Executive Officer', 'MG001', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(3, '1b05b3c5-cc7b-11f0-b7ce-98e7f429106a', 'coo@curuzamuhinzi.com', '+250788123458', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie Claire', 'Mukamana', NULL, 'management', 'Chief Operations Officer', 'MG002', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(4, '1b07849c-cc7b-11f0-b7ce-98e7f429106a', 'operations@curuzamuhinzi.com', '+250788123459', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Paul', 'Niyonzima', NULL, 'operations', 'Operations Manager', 'OP001', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(5, '1b078b00-cc7b-11f0-b7ce-98e7f429106a', 'ops.assistant@curuzamuhinzi.com', '+250788123460', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grace', 'Uwimana', NULL, 'operations', 'Operations Assistant', 'OP002', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(6, '1b092fb7-cc7b-11f0-b7ce-98e7f429106a', 'finance@curuzamuhinzi.com', '+250788123461', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emmanuel', 'Habimana', NULL, 'finance', 'Finance Manager', 'FN001', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(7, '1b093662-cc7b-11f0-b7ce-98e7f429106a', 'accountant@curuzamuhinzi.com', '+250788123462', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diane', 'Mutesi', NULL, 'finance', 'Senior Accountant', 'FN002', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(8, '1b0ad589-cc7b-11f0-b7ce-98e7f429106a', 'support@curuzamuhinzi.com', '+250788123463', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Uwimana', NULL, 'support', 'Support Manager', 'SP001', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', '2025-12-02 21:05:55', 0, '2025-11-28 16:55:55', '2025-12-02 21:05:55', NULL),
(9, '1b0adf9c-cc7b-11f0-b7ce-98e7f429106a', 'support.agent@curuzamuhinzi.com', '+250788123464', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Nkurunziza', NULL, 'support', 'Support Agent', 'SP002', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(10, '1b0c68e7-cc7b-11f0-b7ce-98e7f429106a', 'tech@curuzamuhinzi.com', '+250788123465', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eric', 'Mugisha', NULL, 'technical', 'Technical Lead', 'TC001', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(11, '1b0c701e-cc7b-11f0-b7ce-98e7f429106a', 'developer@curuzamuhinzi.com', '+250788123466', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Uwimana', NULL, 'technical', 'Senior Developer', 'TC002', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(12, '1b0db7d3-cc7b-11f0-b7ce-98e7f429106a', 'marketing@curuzamuhinzi.com', '+250788123467', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Frank', 'Niyonzima', NULL, 'marketing', 'Marketing Manager', 'MK001', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL),
(13, '1b0dbd1f-cc7b-11f0-b7ce-98e7f429106a', 'social.media@curuzamuhinzi.com', '+250788123468', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Linda', 'Mukamana', NULL, 'marketing', 'Social Media Specialist', 'MK002', '2025-11-28', NULL, NULL, NULL, 'active', '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL, 0, '2025-11-28 16:55:55', '2025-11-28 16:55:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` bigint(20) NOT NULL,
  `admin_id` bigint(20) NOT NULL,
  `ACTION` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_data`)),
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_dashboard_widgets`
--

CREATE TABLE `admin_dashboard_widgets` (
  `id` bigint(20) NOT NULL,
  `admin_id` bigint(20) NOT NULL,
  `widget_type` varchar(50) NOT NULL,
  `widget_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`widget_config`)),
  `position_x` int(11) DEFAULT 0,
  `position_y` int(11) DEFAULT 0,
  `width` int(11) DEFAULT 1,
  `height` int(11) DEFAULT 1,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint(20) NOT NULL,
  `admin_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `TYPE` enum('info','warning','error','success') DEFAULT 'info',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `is_read` tinyint(1) DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` bigint(20) NOT NULL,
  `admin_id` bigint(20) NOT NULL,
  `permission_module` varchar(50) NOT NULL,
  `can_create` tinyint(1) DEFAULT 0,
  `can_read` tinyint(1) DEFAULT 0,
  `can_update` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0,
  `can_export` tinyint(1) DEFAULT 0,
  `can_approve` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `admin_permissions_view`
-- (See below for the actual view)
--
CREATE TABLE `admin_permissions_view` (
`id` bigint(20)
,`email` varchar(255)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`department` enum('super_admin','management','operations','finance','support','technical','marketing')
,`position` varchar(100)
,`status` enum('active','inactive','suspended')
,`department_permissions` longtext
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `department_roles`
--

CREATE TABLE `department_roles` (
  `id` bigint(20) NOT NULL,
  `department` enum('super_admin','management','operations','finance','support','technical','marketing') NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_roles`
--

INSERT INTO `department_roles` (`id`, `department`, `role_name`, `description`, `permissions`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Administrator', 'Full system access and control', '{\"users\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"subscriptions\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"payments\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"listings\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"orders\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"cooperatives\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"analytics\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"system\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"admins\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}}', 1, '2025-11-28 16:55:55', '2025-11-28 16:55:55'),
(2, 'management', 'Management Team', 'Senior management with broad access', '{\"users\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": true}, \"subscriptions\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": true}, \"payments\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": true}, \"listings\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"orders\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": true}, \"cooperatives\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": true}, \"analytics\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"system\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}}', 1, '2025-11-28 16:55:55', '2025-11-28 16:55:55'),
(3, 'operations', 'Operations Team', 'Daily operations and user management', '{\"users\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": false}, \"subscriptions\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": false}, \"payments\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"listings\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": true, \"export\": true, \"approve\": true}, \"orders\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": false}, \"cooperatives\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": false}, \"analytics\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}}', 1, '2025-11-28 16:55:55', '2025-11-28 16:55:55'),
(4, 'finance', 'Finance Team', 'Financial operations and payment management', '{\"users\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"subscriptions\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": true}, \"payments\": {\"create\": true, \"read\": true, \"update\": true, \"delete\": false, \"export\": true, \"approve\": true}, \"listings\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"orders\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"cooperatives\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"analytics\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}}', 1, '2025-11-28 16:55:55', '2025-11-28 16:55:55'),
(5, 'support', 'Support Team', 'Customer support and basic operations', '{\"users\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": false, \"approve\": false}, \"subscriptions\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"payments\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"listings\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"orders\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": false, \"approve\": false}, \"cooperatives\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"analytics\": {\"create\": false, \"read\": false, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}}', 1, '2025-11-28 16:55:55', '2025-11-28 16:55:55'),
(6, 'technical', 'Technical Team', 'System maintenance and technical support', '{\"users\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"subscriptions\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"payments\": {\"create\": false, \"read\": false, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"listings\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"orders\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"cooperatives\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"analytics\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"system\": {\"create\": false, \"read\": true, \"update\": true, \"delete\": false, \"export\": false, \"approve\": false}}', 1, '2025-11-28 16:55:55', '2025-11-28 16:55:55'),
(7, 'marketing', 'Marketing Team', 'Marketing and promotional activities', '{\"users\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"subscriptions\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"payments\": {\"create\": false, \"read\": false, \"update\": false, \"delete\": false, \"export\": false, \"approve\": false}, \"listings\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"orders\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"cooperatives\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}, \"analytics\": {\"create\": false, \"read\": true, \"update\": false, \"delete\": false, \"export\": true, \"approve\": false}}', 1, '2025-11-28 16:55:55', '2025-11-28 16:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('success','error','info','warning') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 7, 'Subscription Approved!', 'Congratulations! Your Premium subscription has been approved. You can now add products to the marketplace.', 'success', 1, '2025-12-03 00:09:00'),
(2, 5, 'Subscription Approved!', 'Congratulations! Your Premium subscription has been approved. You can now add products to the marketplace.', 'success', 1, '2025-12-03 00:10:20'),
(3, 7, 'Subscription Rejected', 'Your Premium subscription payment was not approved. Please check your payment details or contact Curuza Muhinzi support for assistance.', 'error', 1, '2025-12-03 00:19:08'),
(4, 7, 'Subscription Approved!', 'Congratulations! Your Premium subscription has been approved. You can now add products to the marketplace.', 'success', 1, '2025-12-03 00:20:16'),
(5, 7, 'Subscription Approved!', 'Congratulations! Your Premium subscription has been approved. You can now add products to the marketplace.', 'success', 1, '2025-12-03 15:25:30'),
(6, 5, 'Subscription Approved!', 'Congratulations! Your Premium subscription has been approved. You can now add products to the marketplace.', 'success', 1, '2025-12-03 15:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('mobile_money','bank_transfer','cash') NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `type`, `account_number`, `account_name`, `instructions`, `is_active`, `created_at`) VALUES
(1, 'MTN Mobile Money', 'mobile_money', '0788123456', 'Curuza Muhinzi', 'Send payment to MTN Mobile Money number and upload screenshot', 1, '2025-12-02 22:01:16'),
(2, 'Airtel Money', 'mobile_money', '0733123456', 'Curuza Muhinzi', 'Send payment to Airtel Money number and upload screenshot', 1, '2025-12-02 22:01:16'),
(3, 'MOMO Code', 'mobile_money', 'MOMO123', 'Curuza Muhinzi', 'Use MOMO Code to send payment and upload screenshot', 1, '2025-12-02 22:01:16'),
(4, 'Bank of Kigali', 'bank_transfer', '00012345678', 'Curuza Muhinzi Ltd', 'Transfer to BK account and upload bank slip', 1, '2025-12-02 22:01:16');

-- --------------------------------------------------------

--
-- Table structure for table `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `screenshot_url` varchar(500) NOT NULL,
  `status` enum('pending','verified','rejected','spam') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `is_critical_issue` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_proofs`
--

INSERT INTO `payment_proofs` (`id`, `user_id`, `subscription_id`, `payment_method_id`, `amount`, `reference_number`, `screenshot_url`, `status`, `verified_by`, `verified_at`, `rejection_reason`, `admin_notes`, `is_critical_issue`, `created_at`, `updated_at`) VALUES
(14, 7, 15, 1, 15000.00, NULL, '6930565b9989f.png', 'pending', NULL, NULL, NULL, NULL, 0, '2025-12-03 15:25:15', '2025-12-03 15:25:15'),
(15, 5, 16, 1, 15000.00, NULL, '693056aa33c64.jpg', 'pending', NULL, NULL, NULL, NULL, 0, '2025-12-03 15:26:34', '2025-12-03 15:26:34');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category` enum('finished_crops','seeds','livestock','equipment','fertilizers','pesticides','tools','services') NOT NULL,
  `product_type` enum('cassava','rice','maize','beans','irish_potatoes','sweet_potatoes','bananas','tomatoes','onions','carrots','cabbage','wheat','sorghum','millet','groundnuts','soybeans','coffee','tea','avocado','passion_fruit','pineapple','watermelon','cucumber','pepper','eggplant','spinach','lettuce','cow','goat','sheep','pig','chicken','duck','rabbit','fish','tractor','plough','hoe','sprayer','harvester','irrigation_system','greenhouse','storage_facility','npk_fertilizer','urea','dap','organic_fertilizer','compost','manure','insecticide','herbicide','fungicide','hand_tools','farm_consultation','transport_service','processing_service','other') NOT NULL,
  `price` decimal(12,2) NOT NULL COMMENT 'Price in Rwandan Francs (RWF)',
  `unit` enum('kg','ton','bag_50kg','bag_25kg','piece','liter','hectare','hour','day','month','service') NOT NULL DEFAULT 'kg',
  `quantity_available` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `quality_grade` enum('premium','standard','basic') DEFAULT 'standard',
  `organic_certified` tinyint(1) DEFAULT 0,
  `main_image` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `image_4` varchar(255) DEFAULT NULL,
  `harvest_season` enum('dry_season','rainy_season','year_round') DEFAULT 'year_round',
  `available_from` date DEFAULT NULL,
  `available_until` date DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `preferred_contact` enum('phone','whatsapp','visit_farm','any') DEFAULT 'any',
  `delivery_available` tinyint(1) DEFAULT 0,
  `pickup_available` tinyint(1) DEFAULT 1,
  `status` enum('active','sold_out','seasonal_break','draft') DEFAULT 'active',
  `views_count` int(11) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'RWF',
  `duration_days` int(11) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `max_products` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `description`, `price`, `currency`, `duration_days`, `features`, `max_products`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Basic', 'Post up to 10 products per month', 5000.00, 'RWF', 30, '[\"Post 10 products/month\", \"Basic analytics\", \"Verified badge\", \"Priority support\"]', 20, 1, '2025-12-02 22:01:16', '2025-12-02 22:38:57'),
(3, 'Premium', '30 product posting with advanced features', 15000.00, 'RWF', 1, '[\"Unlimited products\", \"Advanced analytics\", \"Featured listings\", \"Verified badge\", \"Priority support\", \"Marketing tools\"]', 30, 1, '2025-12-02 22:01:16', '2025-12-02 23:18:22'),
(5, 'Advanced', NULL, 35000.00, 'RWF', 30, '[\"Unlimited products\", \"Advanced analytics\", \"Featured listings\", \"Verified badge\", \"Priority support\", \"Marketing tools\"]', NULL, 1, '2025-12-02 22:50:34', '2025-12-02 22:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `user_type` enum('farmer','buyer','cooperative_member','supplier') NOT NULL,
  `province` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `sector` varchar(50) DEFAULT NULL,
  `cell` varchar(50) DEFAULT NULL,
  `village` varchar(50) DEFAULT NULL,
  `address_details` text DEFAULT NULL,
  `farm_size` varchar(20) DEFAULT NULL,
  `what_do_you_grow` text DEFAULT NULL,
  `mobile_money_number` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `language_preference` enum('kinyarwanda','english','french') DEFAULT 'kinyarwanda',
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),
  `status` enum('active','suspended','banned') DEFAULT 'active',
  `profile_completion_percentage` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verification_status` enum('unverified','verified','flagged') DEFAULT 'unverified',
  `account_flags` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `gender`, `password_hash`, `user_type`, `province`, `district`, `sector`, `cell`, `village`, `address_details`, `farm_size`, `what_do_you_grow`, `mobile_money_number`, `profile_picture`, `language_preference`, `notification_preferences`, `status`, `profile_completion_percentage`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`, `verification_status`, `account_flags`) VALUES
(5, 'Mucyo', 'Clebere', 'mucyoclebere@gmail.com', '+250791291988', '2006-07-07', 'male', '$2y$10$Kygocrya4hECnJRIB770P.Aqc/L2kVKyrBBJsPeC8jg.go2hzWARK', 'farmer', 'Kigali City', 'Nyarugenge', 'Nyamirambo', 'Gaki', 'Kabe', 'Kicukiro\r\nkabuga', NULL, 'Lorem', '+250791291988', 'profile_5_1764586748.jpg', 'kinyarwanda', NULL, 'active', 90, '2025-12-03 17:19:59', 0, NULL, '2025-11-29 00:17:35', '2025-12-03 15:19:59', 'unverified', 0),
(7, 'rugwiro', 'delice', 'rugwiro@gmail.com', '+250791291980', '2017-07-15', NULL, '$2y$10$uxjPu1VFx6Q0NFgU9tuDnOMqnLAH5eSkTzWl1Ha5iOd9XKT71L1Em', 'farmer', 'Eastern', 'kigali', 'rwama', 'regu', 'kagugu', NULL, 'Small (< 1 hectare)', NULL, NULL, 'profile_7_1764586826.jpg', 'kinyarwanda', NULL, 'active', 80, '2025-12-03 17:23:42', 0, NULL, '2025-11-29 01:13:32', '2025-12-03 19:48:09', 'unverified', 0),
(8, 'Nadege', 'Gikundiro', 'mforever092@gmail.com', '0789723898', '2004-07-12', 'female', '$2y$10$DXcZAHE/dUfx2vff5D0xF.dqem4p7jfqKWMogTG0NGXDIKA2cK19q', 'cooperative_member', 'Eastern', 'Kayonza', 'Jabana', 'Shyogwe', 'Gishari', NULL, NULL, NULL, '0789723898', 'profile_8_1764597773.png', 'kinyarwanda', NULL, 'active', 80, NULL, 0, NULL, '2025-12-01 14:01:24', '2025-12-01 14:02:53', 'unverified', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_issues`
--

CREATE TABLE `user_issues` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_type` enum('spam_payment','fake_screenshot','multiple_accounts','fraudulent_activity','other') NOT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('open','investigating','resolved','closed') DEFAULT 'open',
  `reported_by` int(11) NOT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `device_info` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('pending','active','expired','cancelled') DEFAULT 'pending',
  `starts_at` date DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 0,
  `payment_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `user_id`, `plan_id`, `status`, `starts_at`, `expires_at`, `auto_renew`, `payment_reference`, `created_at`, `updated_at`) VALUES
(15, 7, 3, 'active', '2025-12-03', '2025-12-04', 0, NULL, '2025-12-03 15:25:15', '2025-12-03 15:25:30'),
(16, 5, 3, 'active', '2025-12-03', '2025-12-04', 0, NULL, '2025-12-03 15:26:34', '2025-12-03 15:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `user_verifications`
--

CREATE TABLE `user_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verification_type` enum('subscription_verified','admin_verified','community_verified') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `verified_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `admin_permissions_view`
--
DROP TABLE IF EXISTS `admin_permissions_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_permissions_view`  AS SELECT `a`.`id` AS `id`, `a`.`email` AS `email`, `a`.`first_name` AS `first_name`, `a`.`last_name` AS `last_name`, `a`.`department` AS `department`, `a`.`POSITION` AS `position`, `a`.`STATUS` AS `status`, `dr`.`permissions` AS `department_permissions`, `a`.`created_at` AS `created_at` FROM (`admins` `a` left join `department_roles` `dr` on(`a`.`department` = `dr`.`department`)) WHERE `a`.`deleted_at` is null ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `UUID` (`UUID`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_status` (`STATUS`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_action` (`admin_id`,`ACTION`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_dashboard_widgets`
--
ALTER TABLE `admin_dashboard_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_widget` (`admin_id`,`widget_type`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_read` (`admin_id`,`is_read`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_module` (`admin_id`,`permission_module`),
  ADD KEY `idx_admin_module` (`admin_id`,`permission_module`);

--
-- Indexes for table `department_roles`
--
ALTER TABLE `department_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department` (`department`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_critical` (`is_critical_issue`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_product_type` (`product_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`province`,`district`),
  ADD KEY `idx_price_range` (`price`,`unit`),
  ADD KEY `idx_availability` (`available_from`,`available_until`),
  ADD KEY `idx_featured` (`featured`,`status`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_price` (`price`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`province`,`district`),
  ADD KEY `idx_verification` (`verification_status`),
  ADD KEY `idx_flags` (`account_flags`);

--
-- Indexes for table `user_issues`
--
ALTER TABLE `user_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`session_token`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_verification` (`user_id`,`verification_type`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_user_active` (`user_id`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_dashboard_widgets`
--
ALTER TABLE `admin_dashboard_widgets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department_roles`
--
ALTER TABLE `department_roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_issues`
--
ALTER TABLE `user_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_verifications`
--
ALTER TABLE `user_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_dashboard_widgets`
--
ALTER TABLE `admin_dashboard_widgets`
  ADD CONSTRAINT `admin_dashboard_widgets_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD CONSTRAINT `payment_proofs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_proofs_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `user_subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_proofs_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  ADD CONSTRAINT `payment_proofs_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_issues`
--
ALTER TABLE `user_issues`
  ADD CONSTRAINT `user_issues_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_issues_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_issues_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD CONSTRAINT `user_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_verifications_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
