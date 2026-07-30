-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 30, 2026 at 04:58 PM
-- Server version: 10.6.27-MariaDB-cll-lve
-- PHP Version: 8.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nmcorporat_furniture_shoppers_db`
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
(1, 'Om Patil', 'admin', 'admin@furnituremarket.com', 'admin123', '9876543210', 'admin.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31');

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
(1, 'Commercial Furniture', 'commercial-furniture', 'Ergonomic desks, executive chairs, conference tables and workstations for modern offices.', 'office-furniture.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-28 09:52:00'),
(2, 'Residential Furniture', 'residential-furniture', 'Furniture designed for homes, offering comfort, functionality, and modern style for every living space.', 'home-furniture.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-28 10:34:01'),
(4, 'Restaurant & Cafe', 'restaurant-cafe', 'Durable, design-forward seating and tables built for high-footfall hospitality spaces.', 'restaurant-cafe.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(5, 'Educational Furniture', 'educational-furniture', 'Weather-ready patio and garden furniture built to last through every season.', 'cat_1785394871_6a6af6b74be95.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-30 07:01:11'),
(6, 'Storage & Cabinets', 'storage-cabinets', 'Smart storage solutions including bookshelves, TV units, sideboards and cabinets.', 'storage-cabinets.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31');

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
(1, 'mobile', NULL, '+91 1800-123-4567', NULL, NULL, NULL, NULL, NULL, 1, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(2, 'email', NULL, 'support@furnituremarket.in', NULL, NULL, NULL, NULL, NULL, 2, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(3, 'address', 'Main Office', NULL, 'Main Office', '4th Floor, Commerce Tower, MG Road', 'Bengaluru', 'Karnataka', '560001', 3, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `product_name`, `slug`, `sku`, `short_description`, `description`, `specifications`, `regular_price`, `sale_price`, `gst_percentage`, `stock_quantity`, `thumbnail`, `product_status`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Luxury L Shape Director Office Table with Side Storage Unit', 'luxury-l-shape-director-table', 'SKU-OT-001', 'Premium L-shaped director desk with side storage unit and cable management.', NULL, NULL, 134399.00, 102999.00, 18.00, 5, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(2, 1, 1, 'Modern Executive Office Desk with Side Storage Cabinet for CEO', 'modern-executive-desk-ceo', 'SKU-OT-002', 'Spacious CEO desk with integrated side cabinet and modesty panel.', NULL, NULL, 60899.00, 46899.00, 18.00, 8, 'https://images.unsplash.com/photo-1596162954151-cdcb4c0f70fb?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(3, 1, 1, 'Luxury CEO Office Table with Integrated Credenza Storage', 'luxury-ceo-credenza-table', 'SKU-OT-003', 'Executive table with attached credenza for maximum storage.', NULL, NULL, 158200.00, 121799.00, 18.00, 4, 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(4, 1, 1, 'CEO Office Table with Attached Storage Credenza Contemporary', 'ceo-attached-credenza-desk', 'SKU-OT-004', 'Contemporary CEO desk with side credenza in engineered wood.', NULL, NULL, 163099.00, 125199.00, 18.00, 6, 'https://images.unsplash.com/photo-1497366754035-f200586c4bd4?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(5, 1, 1, 'Designer Executive Office Table with Side Storage Extension', 'designer-exec-side-storage', 'SKU-OT-005', 'Director-grade table with extension unit and laminate finish.', NULL, NULL, 168099.00, 129399.00, 18.00, 3, 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(6, 1, 2, 'Luxury High-Back Leatherette Executive Chair with Lumbar Support', 'luxury-exec-chair-lumbar', 'SKU-OC-001', 'High-back executive chair in premium leatherette with lumbar support.', NULL, NULL, 29999.00, 23199.00, 18.00, 15, 'https://images.unsplash.com/photo-1541558869434-2840d308329a?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(7, 1, 2, 'Premium Ergonomic Mesh Chair with Adjustable Armrests', 'premium-ergonomic-mesh-chair', 'SKU-OC-002', 'Full-mesh ergonomic chair with adjustable lumbar and 4D armrests.', NULL, NULL, 24000.00, 18500.00, 18.00, 20, 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(8, 1, 2, 'High-Back Leatherette Director Chair Wooden Base', 'high-back-director-wooden', 'SKU-OC-003', 'Classic director chair with wooden base and premium leatherette upholstery.', NULL, NULL, 90499.00, 69399.00, 18.00, 10, 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(9, 1, 2, 'Comfortable Director Chair Velvet Upholstery Adjustable Arm', 'director-chair-velvet', 'SKU-OC-004', 'Velvet-upholstered director chair with adjustable armrests.', NULL, NULL, 15999.00, 12399.00, 18.00, 25, 'https://images.unsplash.com/photo-1596162954151-cdcb4c0f70fb?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(10, 1, 2, 'High-Back Office Chair Recliner Swivel Ergonomic Lumbar', 'recliner-swivel-ergonomic', 'SKU-OC-005', 'Recliner office chair with swivel base and lumbar support.', NULL, NULL, 102599.00, 78999.00, 18.00, 8, 'https://images.unsplash.com/photo-1574180566232-aaad1b5b8450?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(11, 1, 3, '12-Seater Premium Conference Table Solid Wood Veneer Finish', '12-seater-conference-table', 'SKU-CT-001', 'Boardroom conference table with solid wood veneer and cable ports.', NULL, NULL, 210000.00, 162000.00, 18.00, 2, 'https://images.unsplash.com/photo-1564069114553-7215e1ff1890?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(12, 1, 3, '8-Seater Boat Shape Conference Table with Central Cable Tray', '8-seater-boat-conference', 'SKU-CT-002', 'Boat-shaped meeting table with integrated cable management tray.', NULL, NULL, 145000.00, 112000.00, 18.00, 3, 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(13, 1, 4, '4-Seater Premium Collaborative Workstation with Cable Management', '4-seater-collab-workstation', 'SKU-WS-001', 'Open-plan 4-seater desk system with modesty panels and cable trunking.', NULL, NULL, 110000.00, 85000.00, 18.00, 4, 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(14, 1, 4, '6-Seater Open Plan Office Bench Desk with Storage Pods', '6-seater-bench-desk', 'SKU-WS-002', 'Bench-style 6-seater workstation with under-desk storage pedestals.', NULL, NULL, 122500.00, 94500.00, 18.00, 3, 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(15, 2, 6, '3 Seater Sofa Italian Fabric Premium Upholstery Luxury Comfort', '3-seater-sofa-italian-fabric', 'SKU-SF-001', 'Premium 3-seater sofa in Italian fabric with high-density foam cushions.', NULL, NULL, 92399.00, 71199.00, 18.00, 10, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(16, 2, 6, '3 Seater Sofa Tufted Design Leatherette High Quality', '3-seater-tufted-leatherette', 'SKU-SF-002', 'Tufted 3-seater sofa in premium leatherette with wooden legs.', NULL, NULL, 64999.00, 49899.00, 18.00, 12, 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(17, 2, 6, 'Modern Luxury 1 to 3-Seater Sofa Upholstered Fabric Cushioned', 'modern-luxury-sofa-3seater', 'SKU-SF-003', 'Modular sofa available in 1, 2 and 3-seater configurations.', NULL, NULL, 108400.00, 83499.00, 18.00, 8, 'https://images.unsplash.com/photo-1567538096621-38d2284b23ff?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(18, 2, 6, '3 Seater Sofa Plyboard Upholster Leatherette Modern Design', '3-seater-plyboard-leatherette', 'SKU-SF-004', 'Modern sofa with plyboard frame and durable leatherette finish.', NULL, NULL, 92399.00, 71199.00, 18.00, 9, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(19, 2, 6, '3 Seater Chesterfield Sofa Premium Suede Fabric Durable', '3-seater-chesterfield-suede', 'SKU-SF-005', 'Classic Chesterfield 3-seater in premium suede with button tufting.', NULL, NULL, 86800.00, 66899.00, 18.00, 6, 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(20, 2, 9, '6-Seater Solid Wood Dining Table Set with Upholstered Chairs', '6-seater-solid-wood-dining', 'SKU-DT-001', 'Solid sheesham wood dining table with 6 upholstered dining chairs.', NULL, NULL, 87400.00, 61200.00, 18.00, 5, 'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(21, 2, 9, '4-Seater Marble Top Dining Table with Metal Legs Modern', '4-seater-marble-dining', 'SKU-DT-002', 'Contemporary marble top dining table with hairpin metal legs.', NULL, NULL, 68000.00, 52500.00, 18.00, 6, 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(22, 3, 11, 'King Size Upholstered Bed Premium Fabric Headboard Storage', 'king-upholstered-storage-bed', 'SKU-BD-001', 'King-size upholstered bed with hydraulic storage and cushioned headboard.', NULL, NULL, 70700.00, 49500.00, 18.00, 7, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(23, 3, 11, 'Queen Size Solid Wood Bed Sheesham Natural Finish', 'queen-solid-wood-sheesham', 'SKU-BD-002', 'Queen-size solid sheesham wood bed with natural finish.', NULL, NULL, 58000.00, 44700.00, 18.00, 9, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(24, 3, 11, 'Modern Platform Bed Low Height Walnut Finish Storage', 'modern-platform-bed-walnut', 'SKU-BD-003', 'Low-profile platform bed in walnut finish with under-bed storage drawers.', NULL, NULL, 75000.00, 57900.00, 18.00, 5, 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(25, 3, 12, '3-Door Sliding Wardrobe with Mirror Premium Finish', '3-door-sliding-mirror-wardrobe', 'SKU-WD-001', '3-door sliding wardrobe with full-length mirror and internal shelving.', NULL, NULL, 65000.00, 50000.00, 18.00, 8, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(26, 3, 12, '4-Door Hinged Wardrobe with Drawers Melamine Finish', '4-door-hinged-wardrobe', 'SKU-WD-002', 'Spacious 4-door hinged wardrobe with multiple drawers and shelves.', NULL, NULL, 78000.00, 60100.00, 18.00, 6, 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(27, 4, 16, 'Stackable Metal Dining Chair Upholstered Seat Restaurant', 'stackable-metal-dining-chair', 'SKU-RC-001', 'Commercial-grade stackable metal chair with upholstered seat.', NULL, NULL, 3500.00, 2700.00, 18.00, 50, 'https://images.unsplash.com/photo-1567538096621-38d2284b23ff?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(28, 4, 16, 'Wooden Cafeteria Chair with Cushion High-Footfall Durable', 'wooden-cafeteria-chair', 'SKU-RC-002', 'Solid wood cafeteria chair with padded seat — ideal for high-footfall venues.', NULL, NULL, 4800.00, 3700.00, 18.00, 40, 'https://images.unsplash.com/photo-1566312922674-b8ead9dd9f7f?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(29, 4, 18, 'Round Cafe Table HPL Top with Tulip Base Indoor Outdoor', 'round-cafe-tulip-table', 'SKU-CT-R01', 'Round HPL-top cafe table with powder-coated tulip base.', NULL, NULL, 8500.00, 6600.00, 18.00, 30, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(30, 4, 18, 'Square Laminate Cafe Table with Metal Frame 2-Seater', 'square-laminate-cafe-table', 'SKU-CT-S02', '2-seater square cafe table with laminate top and black metal frame.', NULL, NULL, 6800.00, 5200.00, 18.00, 25, 'https://images.unsplash.com/photo-1550966871-3ed3cbe818fb?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(31, 5, 20, '5-Piece Rattan Outdoor Patio Set with Tempered Glass Table', '5-piece-rattan-patio-set', 'SKU-OP-001', 'Complete 5-piece rattan patio set with weather-resistant cushions.', NULL, NULL, 95000.00, 73000.00, 18.00, 4, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(32, 5, 20, '4-Piece Aluminium Garden Set Foldable Sunbed Outdoor', 'aluminium-garden-foldable-set', 'SKU-OP-002', 'Lightweight aluminium outdoor set — foldable for easy storage.', NULL, NULL, 68000.00, 52500.00, 18.00, 6, 'https://images.unsplash.com/photo-1588854337236-6889d631faa8?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(33, 6, 23, '5-Shelf Open Bookcase Solid Wood Walnut Finish', '5-shelf-open-bookcase-walnut', 'SKU-BS-001', '5-tier open bookcase in solid wood with walnut finish.', NULL, NULL, 39700.00, 27800.00, 18.00, 12, 'https://images.unsplash.com/photo-1594620302200-9a762244a156?w=400&h=300&fit=crop', 'Active', 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(34, 6, 23, 'Modular Wall-Mounted Shelving System Metal Frame White', 'modular-wall-shelving-metal', 'SKU-BS-002', 'Modular wall-mounted shelving with adjustable metal brackets.', NULL, NULL, 28000.00, 21600.00, 18.00, 15, 'https://images.unsplash.com/photo-1467043237213-65f2da53396f?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(35, 6, 25, 'Narrow Console Hallway Table with 2 Drawers Walnut', 'narrow-console-2-drawer', 'SKU-CS-001', 'Slim console table with 2 drawers — perfect for hallways and entryways.', NULL, NULL, 22000.00, 17000.00, 18.00, 18, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop', 'Active', 0, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(37, 1, 1, 'Prime Executive Table', 'prime-executive-table', 'PET', 'This executive table features a rich teak wood finish, a curved black leather modesty panel, and dual side cabinets, creating a warm, classic look for executive cabins. Its elegant design combines premium style with everyday functionality.', '<p class=\"font-claude-response-body break-words whitespace-normal\" style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\">This director table brings a warm, classic look to any executive cabin. The rich teak wood finish covers the tabletop and side panels, giving the desk a traditional, high-end feel. A curved black leather modesty panel sits at the front centre, adding a bold, stitched design accent. Two side cabinets flank the centre panel, each with a clean wood-panel door. The bullnose edge on the tabletop adds a smooth, rounded finish. This design suits directors who want a warm, elegant desk with a strong classic character.</p><p class=\"font-claude-response-body break-words whitespace-normal\" style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Material 1st (Particle Board):</strong> Made from high-quality <strong style=\"box-sizing: inherit;\">Particle Board</strong> with PVC edge banding. It gives the side cabinet frames and inner structure a firm, even base. The surface stays smooth and holds its shape well. It resists daily wear and needs little upkeep.</p><p class=\"font-claude-response-body break-words whitespace-normal\" style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Material 2nd (MDF with Laminate PU):</strong> Made from high-quality <strong style=\"box-sizing: inherit;\">MDF</strong> with laminate or PU polish finish and PVC edge banding. It gives the cabinet door panels their smooth, warm-toned finish. The surface resists scratches from daily use. It holds its colour and shine for years.</p><p class=\"font-claude-response-body break-words whitespace-normal\" style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Material 3rd (PLY/Solid with Veneer and Polish):</strong> Made from high-quality <strong style=\"box-sizing: inherit;\">plywood and solid wood</strong> with veneer and PU polish. It gives the tabletop its rich teak wood grain and bullnose-edge finish. The finish adds strength and a warm, natural feel. It stays firm through years of daily use.</p><p class=\"font-claude-response-body break-words whitespace-normal\" style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Features:</strong> Wide executive tabletop, bullnose rounded edge, 2 side storage cabinets, curved stitched-leather modesty panel, smooth polished finish, 25MM thick tabletop, and a classic warm-wood design.</p><p class=\"font-claude-response-body break-words whitespace-normal\" style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Durability:</strong> The solid wood frame, thick tabletop, and sturdy side cabinets keep the table steady. It stays firm even under daily office use.</p><p class=\"font-claude-response-body break-words whitespace-normal\" style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Applicable:</strong> Ideal for director cabins, executive offices, and corporate spaces. It suits offices that need a strong, classic desk with side storage for daily work.</p>', '<p style=\"box-sizing: inherit; margin-bottom: 12px; color: rgb(102, 102, 102); font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Length:</strong> <span class=\"metafield-multi_line_text_field\" style=\"box-sizing: inherit;\">72 to 96 Inch</span></p><p style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; color: rgb(102, 102, 102); font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Width: </strong><span class=\"metafield-multi_line_text_field\" style=\"box-sizing: inherit;\">36 Inch</span></p><p style=\"box-sizing: inherit; margin-bottom: 12px; margin-top: 12px; color: rgb(102, 102, 102); font-family: &quot;Instrument Sans&quot;, sans-serif; font-size: 15px; background-color: rgb(255, 255, 255);\"><strong style=\"box-sizing: inherit;\">Height:</strong> <span class=\"metafield-multi_line_text_field\" style=\"box-sizing: inherit;\">30 Inch</span></p>', 58599.00, 58599.00, 18.00, 10, 'uploads/prod_37_f5fa3b191ae5.webp', 'Active', 0, '2026-07-30 11:01:55', '2026-07-30 11:01:55');

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

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `alt_text`, `sort_order`, `is_thumbnail`) VALUES
(1, 36, 'admin_panel/uploads/productsprod_36_827cb4f51a1b.jpg', 'fdsfds', 0, 1),
(2, 37, 'admin_panel/uploads/products/prod_37_f5fa3b191ae5.webp', 'Prime Executive Table', 0, 1),
(3, 37, 'admin_panel/uploads/products/prod_37_9e017e2a79c7.webp', 'Prime Executive Table', 1, 0),
(4, 37, 'admin_panel/uploads/products/prod_37_f5768f2017e9.webp', 'Prime Executive Table', 2, 0),
(5, 37, 'admin_panel/uploads/products/prod_37_9c04f3657e2c.webp', 'Prime Executive Table', 3, 0);

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
(1, 'Design Your Space. Elevate Your Life.', 'PREMIUM COLLECTION', 'Explore a wide range of premium furniture for every space — office, home, hospitality and beyond.', 'uploads/sliders/slider_1.jpg', 1, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(2, 'Comfort Meets. Crafted Style.', 'HOME FURNITURE', 'Timeless sofas, dining sets, and bedroom furniture designed for the way you actually live.', 'uploads/sliders/slider_2.jpg', 2, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(3, 'Built for Scale. Made to Last.', 'FOR PROJECTS', 'End-to-end furniture solutions for offices, hotels, hospitals and hospitality projects across India.', 'uploads/sliders/slider_3.jpg', 3, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31');

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
(1, 1, 'Executive Tables', 'executive-tables', 'Premium executive tables crafted for style, comfort, and lasting performance in every workspace.', 'subcat_6a6aefe28b1f38.72049821.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-30 06:32:02'),
(2, 1, 'Office Chairs', 'office-chairs', 'Ergonomic chairs for every workspace.', 'subcat_6a6afcf0e11c24.08243146.jpg', 'Active', '2026-07-28 09:45:31', '2026-07-30 07:27:44'),
(4, 1, 'Workstations', 'workstations', 'Collaborative open-plan desk systems.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(5, 1, 'Reception Tables', 'reception-tables', 'Front-desk counters and reception units.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(6, 2, 'Sofas', 'sofas', 'Living room sofas in fabric and leatherette.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(7, 2, 'Coffee Tables', 'coffee-tables', 'Stylish coffee and centre tables.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(8, 2, 'TV Units', 'tv-units', 'Wall-mounted and floor-standing TV units.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(9, 2, 'Dining Sets', 'dining-sets', 'Dining tables and chair combinations.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(10, 2, 'Side Tables', 'side-tables', 'Accent and bedside tables for every room.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(16, 4, 'Dining Chairs', 'dining-chairs', 'Stackable and upholstered dining chairs.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(17, 4, 'Bar Stools', 'bar-stools', 'Counter-height stools for bars and cafes.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(18, 4, 'Cafe Tables', 'cafe-tables', 'Round and square cafe-style tables.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(19, 4, 'Booth Seating', 'booth-seating', 'Fixed booth seating for restaurants.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(20, 5, 'Patio Sets', 'patio-sets', 'Complete outdoor dining and lounge sets.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(21, 5, 'Garden Chairs', 'garden-chairs', 'Weather-resistant garden chairs.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(22, 5, 'Outdoor Sofas', 'outdoor-sofas', 'Rattan and aluminium outdoor sofas.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(23, 6, 'Bookshelves', 'bookshelves', 'Open and closed bookshelves.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(24, 6, 'Shoe Racks', 'shoe-racks', 'Entryway and bedroom shoe storage.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(25, 6, 'Console Tables', 'console-tables', 'Narrow console tables for hallways.', NULL, 'Active', '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(27, 1, 'Workstation Table', 'workstation-table', 'A modern workstation table designed for comfortable, organized, and productive workspaces.', 'subcat_6a6af08d074e04.55317657.jpg', 'Active', '2026-07-30 06:22:35', '2026-07-30 06:34:53'),
(28, 1, 'Conference Tables', 'conference-tables', 'Premium conference tables designed for modern meeting spaces, combining durability, functionality, and elegant style for productive collaborations.', 'subcat_6a6aee6c4db1d3.54005078.png', 'Active', '2026-07-30 06:25:48', '2026-07-30 06:25:48'),
(29, 1, 'Computer Tables', 'computer-tables', 'Functional and stylish computer tables designed to maximize workspace, comfort, and productivity for your home or office.', 'subcat_6a6aef4372fb81.44244961.jpg', 'Active', '2026-07-30 06:29:23', '2026-07-30 06:29:23'),
(30, 1, 'Reception Table', 'reception-table', 'A stylish and durable reception table designed to create a welcoming first impression while providing a functional workspace for offices, hotels, clinics, and commercial spaces.', 'subcat_6a6af062f308c6.71186300.jpg', 'Active', '2026-07-30 06:34:10', '2026-07-30 06:34:10'),
(31, 4, 'Restaurant and Cafeteria Chairs', 'restaurant-and-cafeteria-chairs', 'Stylish, durable, and comfortable seating solutions designed to enhance dining spaces with lasting quality and modern appeal.', 'subcat_6a6af1eda4b558.54961819.jpg', 'Active', '2026-07-30 06:40:45', '2026-07-30 06:40:45'),
(32, 1, 'Boss Chairs', 'boss-chairs', 'Elegant executive chairs offering comfort, durability, and ergonomic support for modern workspaces.', 'subcat_6a6afc71539208.95544758.jpg', 'Active', '2026-07-30 06:41:29', '2026-07-30 07:25:37'),
(33, 1, 'Mess Chairs', 'mess-chairs', 'Breathable mesh chairs built for comfort, support, and all-day productivity.', 'subcat_6a6afaec49c233.97374743.jpg', 'Active', '2026-07-30 06:45:03', '2026-07-30 07:19:08'),
(34, 4, 'Restaurant and Cafeteria Tables', 'restaurant-and-cafeteria-tables', 'Stylish, durable, and space-efficient tables designed to enhance dining experiences in restaurants, cafés, and food courts.', 'subcat_6a6af318d461d7.23340201.jpg', 'Active', '2026-07-30 06:45:44', '2026-07-30 06:45:44'),
(35, 1, 'Cantilever Chairs', 'cantilever-chairs', 'Modern cantilever chairs designed for comfort, durability, and professional office spaces.', 'subcat_6a6afb04ad02f5.48824465.jpg', 'Active', '2026-07-30 06:57:44', '2026-07-30 07:19:32'),
(36, 1, 'Conference & Trainig Chairs', 'conference-trainig-chairs', 'Comfortable and durable chairs for conference rooms, training sessions, and collaborative workspaces.', 'subcat_6a6af89e0b7e69.93889461.jpg', 'Active', '2026-07-30 07:00:10', '2026-07-30 07:09:18'),
(37, 1, 'Bar Chairs', 'bar-chairs', 'Modern bar chairs crafted with durable materials and elegant finishes, offering comfort and style for every seating area.', 'subcat_6a6af9c00308b0.14458544.jpg', 'Active', '2026-07-30 07:01:23', '2026-07-30 07:14:08'),
(38, 1, 'Waiting Seater', 'waiting-seater', 'Comfortable and durable waiting seaters for reception areas, offices, and public spaces.', 'subcat_6a6af9ee1ebc41.87258042.jpg', 'Active', '2026-07-30 07:03:15', '2026-07-30 07:14:54'),
(39, 5, 'Benches', 'benches', 'Stylish and durable benches crafted to provide comfortable seating while enhancing the beauty of your home or outdoor space.', 'subcat_6a6af735307457.03796978.jpg', 'Active', '2026-07-30 07:03:17', '2026-07-30 07:03:17'),
(40, 5, 'Little World', 'little-world', 'Furniture Shoppers – Bringing Comfort, Style, and Quality Together.', 'subcat_6a6af8e427bdc9.13204997.jpg', 'Active', '2026-07-30 07:10:28', '2026-07-30 07:10:28'),
(41, 5, 'Hostel Setup', 'hostel-setup', 'Complete hostel furniture solutions designed for comfort, durability, and efficient space utilization.', 'subcat_6a6af948cf9688.24129156.jpg', 'Active', '2026-07-30 07:12:08', '2026-07-30 07:12:08'),
(42, 5, 'Auditorium Chairs', 'auditorium-chairs', 'Premium auditorium chairs designed for exceptional comfort, durability, and elegant seating solutions for theaters, schools, colleges, conference halls, and auditoriums.', 'subcat_6a6af9b6ec9bf6.42166124.jpg', 'Active', '2026-07-30 07:13:58', '2026-07-30 07:13:58'),
(43, 2, 'Soft Seating', 'soft-seating', 'Discover stylish and comfortable soft seating solutions designed to add elegance, relaxation, and lasting comfort to every living space.', 'subcat_6a6afb4406dcf4.82006527.jpg', 'Active', '2026-07-30 07:20:36', '2026-07-30 07:20:36'),
(44, 2, 'Table', 'table', 'Transform your home with elegant furniture designed for every space.', 'subcat_6a6afb98c12864.61644926.jpg', 'Active', '2026-07-30 07:22:00', '2026-07-30 07:22:00'),
(45, 2, 'Bed', 'bed', 'Transform your bedroom into a cozy retreat with our stylish, durable, and premium-quality beds designed for exceptional comfort and lasting elegance.', 'subcat_6a6afbd5983ac3.74382795.jpg', 'Active', '2026-07-30 07:23:01', '2026-07-30 07:23:01'),
(46, 2, 'Dinning', 'dinning', 'Elegant dining furniture crafted to bring comfort, style, and togetherness to every meal.', 'subcat_6a6afc1b0aeec2.76243458.jpg', 'Active', '2026-07-30 07:24:11', '2026-07-30 07:24:11'),
(47, 2, 'Outdoor', 'outdoor', 'Create stylish and comfortable outdoor spaces with premium furniture designed for every home.', 'subcat_6a6afc73b20fc5.38452499.jpg', 'Active', '2026-07-30 07:25:39', '2026-07-30 07:25:39'),
(48, 2, 'Kids', 'kids', 'Bright, safe, and stylish furniture designed to create the perfect space for every child.', 'subcat_6a6afcda0b1129.02845135.jpg', 'Active', '2026-07-30 07:27:22', '2026-07-30 07:27:22'),
(49, 2, 'Solid Wood Furniture', 'solid-wood-furniture', 'Premium solid wood furniture crafted to bring timeless elegance, comfort, and durability to your home.', 'subcat_6a6b13191daf89.78813546.jpg', 'Active', '2026-07-30 09:02:17', '2026-07-30 09:52:25');

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
(1, 'uploads/testimonials/testimonial_1.jpg', 'Aakarsh Nair', 'Studio Vertex Design', 'We outfitted our entire office floor through Furniture Market. The range and the quality of the executive collection were far beyond what we expected.', 4, 1, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(2, 'uploads/testimonials/testimonial_2.jpg', 'Karan Mehta', 'Dining & More', 'We sourced our living room and dining sets here. The wood collection felt genuinely premium — nothing like a typical catalogue.', 5, 2, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(3, 'uploads/testimonials/testimonial_3.jpg', 'Priya Nandan', 'The Nest Café', 'Great experience in furnishing our café. Support was exceptional — they helped us pick the right pieces for a high-footfall space.', 5, 3, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31'),
(4, 'uploads/testimonials/testimonial_4.jpg', 'Rohit Sinha', 'Sinha Architecture', 'Flawless. We recommend Furniture Market to every client now. The custom furniture desk has never missed a lead.', 5, 4, 1, '2026-07-28 09:45:31', '2026-07-28 09:45:31');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `contact_details`
--
ALTER TABLE `contact_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `product_faqs`
--
ALTER TABLE `product_faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

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
