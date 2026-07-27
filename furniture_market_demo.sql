-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 01:55 PM
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
-- Database: `furniture_market_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `username`, `email`, `password`, `phone`, `profile_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Om Patil', 'admin', 'admin@furnituremarket.com', 'admin123', '9876543210', 'admin.jpg', 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `slug`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Commercial Furniture', 'commercial-furniture', 'Ergonomic desks, executive chairs, conference tables and workstations for modern offices.', 'office-furniture.jpg', 'Active', '2026-07-25 08:54:50', '2026-07-25 10:08:22'),
(2, 'Home Furniture', 'home-furniture', 'Timeless sofas, dining sets, and bedroom furniture designed for everyday comfort and style.', 'home-furniture.jpg', 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(3, 'Bedroom', 'bedroom', 'Premium beds, wardrobes, dressing tables and storage designed for restful living spaces.', 'bedroom.jpg', 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(4, 'Restaurant & Cafe', 'restaurant-cafe', 'Durable, design-forward seating and tables built for high-footfall hospitality spaces.', 'restaurant-cafe.jpg', 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(5, 'Outdoor Furniture', 'outdoor-furniture', 'Weather-ready patio and garden furniture built to last through every season.', 'outdoor-furniture.jpg', 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(6, 'Storage & Cabinets', 'storage-cabinets', 'Smart storage solutions including bookshelves, TV units, sideboards and cabinets.', 'storage-cabinets.jpg', 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `contact_details`
--

CREATE TABLE `contact_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('mobile','landline','email','address') NOT NULL,
  `label` varchar(150) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `address_type` enum('Main Office','Branch','Godown') DEFAULT NULL,
  `address_line` varchar(500) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_details`
--

INSERT INTO `contact_details` (`id`, `type`, `label`, `value`, `address_type`, `address_line`, `city`, `state`, `pincode`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'mobile', NULL, '+91 1800-123-4567', NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(2, 'email', NULL, 'support@furnituremarket.in', NULL, NULL, NULL, NULL, NULL, 2, 1, '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(3, 'address', 'Main Office', NULL, 'Main Office', '4th Floor, Commerce Tower, MG Road', 'Bengaluru', 'Karnataka', '560001', 3, 1, '2026-07-25 08:54:50', '2026-07-25 08:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `subcategory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `specifications` longtext DEFAULT NULL,
  `regular_price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `gst_percentage` decimal(5,2) DEFAULT 18.00,
  `stock_quantity` int(11) DEFAULT 10,
  `thumbnail` varchar(255) DEFAULT NULL,
  `product_status` enum('Active','Inactive','Draft') DEFAULT 'Active',
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `width` decimal(8,2) DEFAULT NULL COMMENT 'Width in cm',
  `height` decimal(8,2) DEFAULT NULL COMMENT 'Height in cm',
  `depth` decimal(8,2) DEFAULT NULL COMMENT 'Depth / Length in cm',
  `seat_height` decimal(8,2) DEFAULT NULL COMMENT 'Seat height in cm',
  `dimension_image` varchar(500) DEFAULT NULL COMMENT 'Path to dimension diagram image',
  `features` text DEFAULT NULL COMMENT 'Newline-separated product features / bullet points',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` varchar(500) DEFAULT NULL,
  `care_instruction` text DEFAULT NULL,
  `shipping_details` text DEFAULT NULL,
  `warranty_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `product_name`, `slug`, `sku`, `short_description`, `description`, `specifications`, `regular_price`, `sale_price`, `gst_percentage`, `stock_quantity`, `thumbnail`, `product_status`, `is_featured`, `created_at`, `updated_at`, `width`, `height`, `depth`, `seat_height`, `dimension_image`, `features`, `seo_title`, `seo_description`, `seo_keywords`, `care_instruction`, `shipping_details`, `warranty_details`) VALUES
(1, 1, 1, 'Luxury L Shape Director Office Table with Side Storage Unit', 'luxury-l-shape-director-table', 'SKU-OT-001', 'Premium L-shaped director desk with side storage unit and cable management.', NULL, NULL, 134399.00, 102999.00, 18.00, 5, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, 1, 'Modern Executive Office Desk with Side Storage Cabinet for CEO', 'modern-executive-desk-ceo', 'SKU-OT-002', 'Spacious CEO desk with integrated side cabinet and modesty panel.', NULL, NULL, 60899.00, 46899.00, 18.00, 8, 'https://images.unsplash.com/photo-1596162954151-cdcb4c0f70fb?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, 1, 'Luxury CEO Office Table with Integrated Credenza Storage', 'luxury-ceo-credenza-table', 'SKU-OT-003', 'Executive table with attached credenza for maximum storage.', NULL, NULL, 158200.00, 121799.00, 18.00, 4, 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, 1, 'CEO Office Table with Attached Storage Credenza Contemporary', 'ceo-attached-credenza-desk', 'SKU-OT-004', 'Contemporary CEO desk with side credenza in engineered wood.', NULL, NULL, 163099.00, 125199.00, 18.00, 6, 'https://images.unsplash.com/photo-1497366754035-f200586c4bd4?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 1, 1, 'Designer Executive Office Table with Side Storage Extension', 'designer-exec-side-storage', 'SKU-OT-005', 'Director-grade table with extension unit and laminate finish.', NULL, NULL, 168099.00, 129399.00, 18.00, 3, 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 1, 2, 'Luxury High-Back Leatherette Executive Chair with Lumbar Support', 'luxury-exec-chair-lumbar', 'SKU-OC-001', 'High-back executive chair in premium leatherette with lumbar support.', NULL, NULL, 29999.00, 23199.00, 18.00, 15, 'https://images.unsplash.com/photo-1541558869434-2840d308329a?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 1, 2, 'Premium Ergonomic Mesh Chair with Adjustable Armrests', 'premium-ergonomic-mesh-chair', 'SKU-OC-002', 'Full-mesh ergonomic chair with adjustable lumbar and 4D armrests.', NULL, NULL, 24000.00, 18500.00, 18.00, 20, 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 1, 2, 'High-Back Leatherette Director Chair Wooden Base', 'high-back-director-wooden', 'SKU-OC-003', 'Classic director chair with wooden base and premium leatherette upholstery.', NULL, NULL, 90499.00, 69399.00, 18.00, 10, 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 1, 2, 'Comfortable Director Chair Velvet Upholstery Adjustable Arm', 'director-chair-velvet', 'SKU-OC-004', 'Velvet-upholstered director chair with adjustable armrests.', NULL, NULL, 15999.00, 12399.00, 18.00, 25, 'https://images.unsplash.com/photo-1596162954151-cdcb4c0f70fb?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 1, 2, 'High-Back Office Chair Recliner Swivel Ergonomic Lumbar', 'recliner-swivel-ergonomic', 'SKU-OC-005', 'Recliner office chair with swivel base and lumbar support.', NULL, NULL, 102599.00, 78999.00, 18.00, 8, 'https://images.unsplash.com/photo-1574180566232-aaad1b5b8450?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 1, 3, '12-Seater Premium Conference Table Solid Wood Veneer Finish', '12-seater-conference-table', 'SKU-CT-001', 'Boardroom conference table with solid wood veneer and cable ports.', NULL, NULL, 210000.00, 162000.00, 18.00, 2, 'https://images.unsplash.com/photo-1564069114553-7215e1ff1890?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 1, 3, '8-Seater Boat Shape Conference Table with Central Cable Tray', '8-seater-boat-conference', 'SKU-CT-002', 'Boat-shaped meeting table with integrated cable management tray.', NULL, NULL, 145000.00, 112000.00, 18.00, 3, 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 1, 4, '4-Seater Premium Collaborative Workstation with Cable Management', '4-seater-collab-workstation', 'SKU-WS-001', 'Open-plan 4-seater desk system with modesty panels and cable trunking.', NULL, NULL, 110000.00, 85000.00, 18.00, 4, 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 1, 4, '6-Seater Open Plan Office Bench Desk with Storage Pods', '6-seater-bench-desk', 'SKU-WS-002', 'Bench-style 6-seater workstation with under-desk storage pedestals.', NULL, NULL, 122500.00, 94500.00, 18.00, 3, 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 2, 6, '3 Seater Sofa Italian Fabric Premium Upholstery Luxury Comfort', '3-seater-sofa-italian-fabric', 'SKU-SF-001', 'Premium 3-seater sofa in Italian fabric with high-density foam cushions.', NULL, NULL, 92399.00, 71199.00, 18.00, 10, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 2, 6, '3 Seater Sofa Tufted Design Leatherette High Quality', '3-seater-tufted-leatherette', 'SKU-SF-002', 'Tufted 3-seater sofa in premium leatherette with wooden legs.', NULL, NULL, 64999.00, 49899.00, 18.00, 12, 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 2, 6, 'Modern Luxury 1 to 3-Seater Sofa Upholstered Fabric Cushioned', 'modern-luxury-sofa-3seater', 'SKU-SF-003', 'Modular sofa available in 1, 2 and 3-seater configurations.', NULL, NULL, 108400.00, 83499.00, 18.00, 8, 'https://images.unsplash.com/photo-1567538096621-38d2284b23ff?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 2, 6, '3 Seater Sofa Plyboard Upholster Leatherette Modern Design', '3-seater-plyboard-leatherette', 'SKU-SF-004', 'Modern sofa with plyboard frame and durable leatherette finish.', NULL, NULL, 92399.00, 71199.00, 18.00, 9, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 2, 6, '3 Seater Chesterfield Sofa Premium Suede Fabric Durable', '3-seater-chesterfield-suede', 'SKU-SF-005', 'Classic Chesterfield 3-seater in premium suede with button tufting.', NULL, NULL, 86800.00, 66899.00, 18.00, 6, 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 2, 9, '6-Seater Solid Wood Dining Table Set with Upholstered Chairs', '6-seater-solid-wood-dining', 'SKU-DT-001', 'Solid sheesham wood dining table with 6 upholstered dining chairs.', NULL, NULL, 87400.00, 61200.00, 18.00, 5, 'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 2, 9, '4-Seater Marble Top Dining Table with Metal Legs Modern', '4-seater-marble-dining', 'SKU-DT-002', 'Contemporary marble top dining table with hairpin metal legs.', NULL, NULL, 68000.00, 52500.00, 18.00, 6, 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 3, 11, 'King Size Upholstered Bed Premium Fabric Headboard Storage', 'king-upholstered-storage-bed', 'SKU-BD-001', 'King-size upholstered bed with hydraulic storage and cushioned headboard.', NULL, NULL, 70700.00, 49500.00, 18.00, 7, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 3, 11, 'Queen Size Solid Wood Bed Sheesham Natural Finish', 'queen-solid-wood-sheesham', 'SKU-BD-002', 'Queen-size solid sheesham wood bed with natural finish.', NULL, NULL, 58000.00, 44700.00, 18.00, 9, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 3, 11, 'Modern Platform Bed Low Height Walnut Finish Storage', 'modern-platform-bed-walnut', 'SKU-BD-003', 'Low-profile platform bed in walnut finish with under-bed storage drawers.', NULL, NULL, 75000.00, 57900.00, 18.00, 5, 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 3, 12, '3-Door Sliding Wardrobe with Mirror Premium Finish', '3-door-sliding-mirror-wardrobe', 'SKU-WD-001', '3-door sliding wardrobe with full-length mirror and internal shelving.', NULL, NULL, 65000.00, 50000.00, 18.00, 8, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 3, 12, '4-Door Hinged Wardrobe with Drawers Melamine Finish', '4-door-hinged-wardrobe', 'SKU-WD-002', 'Spacious 4-door hinged wardrobe with multiple drawers and shelves.', NULL, NULL, 78000.00, 60100.00, 18.00, 6, 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 4, 16, 'Stackable Metal Dining Chair Upholstered Seat Restaurant', 'stackable-metal-dining-chair', 'SKU-RC-001', 'Commercial-grade stackable metal chair with upholstered seat.', NULL, NULL, 3500.00, 2700.00, 18.00, 50, 'https://images.unsplash.com/photo-1567538096621-38d2284b23ff?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 4, 16, 'Wooden Cafeteria Chair with Cushion High-Footfall Durable', 'wooden-cafeteria-chair', 'SKU-RC-002', 'Solid wood cafeteria chair with padded seat — ideal for high-footfall venues.', NULL, NULL, 4800.00, 3700.00, 18.00, 40, 'https://images.unsplash.com/photo-1566312922674-b8ead9dd9f7f?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 4, 18, 'Round Cafe Table HPL Top with Tulip Base Indoor Outdoor', 'round-cafe-tulip-table', 'SKU-CT-R01', 'Round HPL-top cafe table with powder-coated tulip base.', NULL, NULL, 8500.00, 6600.00, 18.00, 30, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 4, 18, 'Square Laminate Cafe Table with Metal Frame 2-Seater', 'square-laminate-cafe-table', 'SKU-CT-S02', '2-seater square cafe table with laminate top and black metal frame.', NULL, NULL, 6800.00, 5200.00, 18.00, 25, 'https://images.unsplash.com/photo-1550966871-3ed3cbe818fb?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 5, 20, '5-Piece Rattan Outdoor Patio Set with Tempered Glass Table', '5-piece-rattan-patio-set', 'SKU-OP-001', 'Complete 5-piece rattan patio set with weather-resistant cushions.', NULL, NULL, 95000.00, 73000.00, 18.00, 4, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 5, 20, '4-Piece Aluminium Garden Set Foldable Sunbed Outdoor', 'aluminium-garden-foldable-set', 'SKU-OP-002', 'Lightweight aluminium outdoor set — foldable for easy storage.', NULL, NULL, 68000.00, 52500.00, 18.00, 6, 'https://images.unsplash.com/photo-1588854337236-6889d631faa8?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 6, 23, '5-Shelf Open Bookcase Solid Wood Walnut Finish', '5-shelf-open-bookcase-walnut', 'SKU-BS-001', '5-tier open bookcase in solid wood with walnut finish.', NULL, NULL, 39700.00, 27800.00, 18.00, 12, 'https://images.unsplash.com/photo-1594620302200-9a762244a156?w=400&h=300&fit=crop', 'Active', 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 6, 23, 'Modular Wall-Mounted Shelving System Metal Frame White', 'modular-wall-shelving-metal', 'SKU-BS-002', 'Modular wall-mounted shelving with adjustable metal brackets.', NULL, NULL, 28000.00, 21600.00, 18.00, 15, 'https://images.unsplash.com/photo-1467043237213-65f2da53396f?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 6, 25, 'Narrow Console Hallway Table with 2 Drawers Walnut', 'narrow-console-2-drawer', 'SKU-CS-001', 'Slim console table with 2 drawers — perfect for hallways and entryways.', NULL, NULL, 22000.00, 17000.00, 18.00, 18, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop', 'Active', 0, '2026-07-25 08:54:51', '2026-07-25 08:54:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_faqs`
--

CREATE TABLE `product_faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Common FAQs shown on every product page';

--
-- Dumping data for table `product_faqs`
--

INSERT INTO `product_faqs` (`id`, `question`, `answer`, `status`, `sort_order`, `created_at`) VALUES
(1, 'What is the weight capacity of this product?', 'Each product is designed to meet standard weight requirements. Please refer to the specifications section for exact details, or contact our team for clarification.', 1, 10, '2026-07-27 17:25:15'),
(2, 'Is assembly required?', 'Minimal assembly is required for most products. All necessary hardware and a step-by-step instruction manual are included in the package.', 1, 20, '2026-07-27 17:25:15'),
(3, 'Can I customise the colour or fabric?', 'Yes. We offer customisation in size, material, fabric, and finish. Share your requirements and our design team will assist you within 24 hours.', 1, 30, '2026-07-27 17:25:15'),
(4, 'What is the warranty period?', 'All our products carry a minimum 1-year warranty against manufacturing defects. Premium collections include extended coverage of up to 5 years.', 1, 40, '2026-07-27 17:25:15'),
(5, 'How do I clean and maintain the furniture?', 'Wipe with a clean, dry cloth. Avoid harsh chemicals or abrasive cleaners. For upholstered products, use a soft brush vacuum attachment and spot-clean stains immediately.', 1, 50, '2026-07-27 17:25:15'),
(6, 'Do you offer bulk / corporate pricing?', 'Yes. Special pricing and dedicated project managers are available for bulk orders of 5+ pieces. Contact our Bulk Orders team for a custom quote.', 1, 60, '2026-07-27 17:25:15');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_thumbnail` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `reviewer_name` varchar(150) NOT NULL,
  `review_text` text DEFAULT NULL,
  `stars` tinyint(3) UNSIGNED NOT NULL DEFAULT 5 COMMENT '1-5',
  `review_images` text DEFAULT NULL COMMENT 'Comma-separated relative paths to review photos',
  `avatar` varchar(500) DEFAULT NULL COMMENT 'Relative path to reviewer photo',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Per-product customer reviews managed via admin panel';

-- --------------------------------------------------------

--
-- Table structure for table `product_specifications`
--

CREATE TABLE `product_specifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `specification_name` varchar(100) DEFAULT NULL,
  `specification_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_name` varchar(100) DEFAULT NULL,
  `variant_value` varchar(100) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title`, `subtitle`, `description`, `image`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Design Your Space. Elevate Your Life.', 'PREMIUM COLLECTION', 'Explore a wide range of premium furniture for every space — office, home, hospitality and beyond.', 'uploads/sliders/slider_1.jpg', 1, 1, '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(2, 'Comfort Meets. Crafted Style.', 'HOME FURNITURE', 'Timeless sofas, dining sets, and bedroom furniture designed for the way you actually live.', 'uploads/sliders/slider_2.jpg', 2, 1, '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(3, 'Built for Scale. Made to Last.', 'FOR PROJECTS', 'End-to-end furniture solutions for offices, hotels, hospitals and hospitality projects across India.', 'uploads/sliders/slider_3.jpg', 3, 1, '2026-07-25 08:54:50', '2026-07-25 08:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `subcategory_name`, `slug`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Executive Tables', NULL, 'Premium director and CEO desks.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(2, 1, 'Office Chairs', NULL, 'Ergonomic chairs for every workspace.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(3, 1, 'Conference Tables', NULL, 'Large tables for meeting rooms.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(4, 1, 'Workstations', NULL, 'Collaborative open-plan desk systems.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(5, 1, 'Reception Tables', NULL, 'Front-desk counters and reception units.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(6, 2, 'Sofas', NULL, 'Living room sofas in fabric and leatherette.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(7, 2, 'Coffee Tables', NULL, 'Stylish coffee and centre tables.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(8, 2, 'TV Units', NULL, 'Wall-mounted and floor-standing TV units.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(9, 2, 'Dining Sets', NULL, 'Dining tables and chair combinations.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(10, 2, 'Side Tables', NULL, 'Accent and bedside tables for every room.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(11, 3, 'Beds', NULL, 'King, queen and single beds in wood and upholstered.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(12, 3, 'Wardrobes', NULL, 'Sliding and hinged wardrobes with mirror.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(13, 3, 'Dressing Tables', NULL, 'Vanity tables with mirrors and storage.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(14, 3, 'Bedside Tables', NULL, 'Compact side tables for the bedroom.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(15, 3, 'Storage Ottomans', NULL, 'Multi-purpose ottomans with hidden storage.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(16, 4, 'Dining Chairs', NULL, 'Stackable and upholstered dining chairs.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(17, 4, 'Bar Stools', NULL, 'Counter-height stools for bars and cafes.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(18, 4, 'Cafe Tables', NULL, 'Round and square cafe-style tables.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(19, 4, 'Booth Seating', NULL, 'Fixed booth seating for restaurants.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(20, 5, 'Patio Sets', NULL, 'Complete outdoor dining and lounge sets.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(21, 5, 'Garden Chairs', NULL, 'Weather-resistant garden chairs.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(22, 5, 'Outdoor Sofas', NULL, 'Rattan and aluminium outdoor sofas.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(23, 6, 'Bookshelves', NULL, 'Open and closed bookshelves.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(24, 6, 'Shoe Racks', NULL, 'Entryway and bedroom shoe storage.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50'),
(25, 6, 'Console Tables', NULL, 'Narrow console tables for hallways.', NULL, 'Active', '2026-07-25 08:54:50', '2026-07-25 08:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `name` varchar(150) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `review` text NOT NULL,
  `stars` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `image`, `name`, `company`, `review`, `stars`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'uploads/testimonials/testimonial_1.jpg', 'Aakarsh Nair', 'Studio Vertex Design', 'We outfitted our entire office floor through Furniture Market. The range and the quality of the executive collection were far beyond what we expected.', 4, 1, 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51'),
(2, 'uploads/testimonials/testimonial_2.jpg', 'Karan Mehta', 'Dining & More', 'We sourced our living room and dining sets here. The wood collection felt genuinely premium — nothing like a typical catalogue.', 5, 2, 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51'),
(3, 'uploads/testimonials/testimonial_3.jpg', 'Priya Nandan', 'The Nest Café', 'Great experience in furnishing our café. Support was exceptional — they helped us pick the right pieces for a high-footfall space.', 5, 3, 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51'),
(4, 'uploads/testimonials/testimonial_4.jpg', 'Rohit Sinha', 'Sinha Architecture', 'Flawless. We recommend Furniture Market to every client now. The custom furniture desk has never missed a lead.', 5, 4, 1, '2026-07-25 08:54:51', '2026-07-25 08:54:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `contact_details`
--
ALTER TABLE `contact_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `product_faqs`
--
ALTER TABLE `product_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subcategories_category` (`category_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_details`
--
ALTER TABLE `contact_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `product_faqs`
--
ALTER TABLE `product_faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `fk_subcategories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
