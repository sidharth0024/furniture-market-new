-- ============================================================
-- Furniture Market — Final Database
-- Database: furniture_market_demo
-- Import: phpMyAdmin → Import → Select this file → Go
-- ============================================================
-- This is the single file you need. It creates the database,
-- all tables, and inserts all demo data with fixes applied:
--   • All hero sliders active (status=1)
--   • All categories Active
--   • All testimonials visible (status=1)
--   • is_featured set correctly on products
--   • slider_1.jpg path matches the fixed image in uploads/
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `furniture_market_demo`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `furniture_market_demo`;

-- ── admins ────────────────────────────────────────────────
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `full_name`     varchar(255) NOT NULL,
  `username`      varchar(255) NOT NULL,
  `email`         varchar(255) NOT NULL,
  `password`      varchar(255) NOT NULL,
  `phone`         varchar(20)  DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status`        enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at`    timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`    timestamp    NULL     DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` VALUES
(1,'Om Patil','admin','admin@furnituremarket.com','admin123','9876543210','admin.jpg','Active',NOW(),NOW());

-- ── categories ────────────────────────────────────────────
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `slug`          varchar(255) NOT NULL,
  `description`   text         DEFAULT NULL,
  `image`         varchar(255) DEFAULT NULL,
  `status`        enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at`    timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`    timestamp    NULL     DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` VALUES
(1,'Office Furniture','office-furniture','Ergonomic desks, executive chairs, conference tables and workstations for modern offices.','office-furniture.jpg','Active',NOW(),NOW()),
(2,'Home Furniture','home-furniture','Timeless sofas, dining sets, and bedroom furniture designed for everyday comfort and style.','home-furniture.jpg','Active',NOW(),NOW()),
(3,'Bedroom','bedroom','Premium beds, wardrobes, dressing tables and storage designed for restful living spaces.','bedroom.jpg','Active',NOW(),NOW()),
(4,'Restaurant & Cafe','restaurant-cafe','Durable, design-forward seating and tables built for high-footfall hospitality spaces.','restaurant-cafe.jpg','Active',NOW(),NOW()),
(5,'Outdoor Furniture','outdoor-furniture','Weather-ready patio and garden furniture built to last through every season.','outdoor-furniture.jpg','Active',NOW(),NOW()),
(6,'Storage & Cabinets','storage-cabinets','Smart storage solutions including bookshelves, TV units, sideboards and cabinets.','storage-cabinets.jpg','Active',NOW(),NOW());

-- ── subcategories ─────────────────────────────────────────
DROP TABLE IF EXISTS `subcategories`;
CREATE TABLE `subcategories` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `category_id`      int(11)      NOT NULL,
  `subcategory_name` varchar(255) NOT NULL,
  `slug`             varchar(255) DEFAULT NULL,
  `description`      text         DEFAULT NULL,
  `image`            varchar(255) DEFAULT NULL,
  `status`           enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at`       timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`       timestamp    NULL     DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_subcategories_category` (`category_id`),
  CONSTRAINT `fk_subcategories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subcategories` (id, category_id, subcategory_name, description, image, status, created_at, updated_at) VALUES
-- Office Furniture
(1, 1,'Executive Tables','Premium director and CEO desks.',NULL,'Active',NOW(),NOW()),
(2, 1,'Office Chairs','Ergonomic chairs for every workspace.',NULL,'Active',NOW(),NOW()),
(3, 1,'Conference Tables','Large tables for meeting rooms.',NULL,'Active',NOW(),NOW()),
(4, 1,'Workstations','Collaborative open-plan desk systems.',NULL,'Active',NOW(),NOW()),
(5, 1,'Reception Tables','Front-desk counters and reception units.',NULL,'Active',NOW(),NOW()),
-- Home Furniture
(6, 2,'Sofas','Living room sofas in fabric and leatherette.',NULL,'Active',NOW(),NOW()),
(7, 2,'Coffee Tables','Stylish coffee and centre tables.',NULL,'Active',NOW(),NOW()),
(8, 2,'TV Units','Wall-mounted and floor-standing TV units.',NULL,'Active',NOW(),NOW()),
(9, 2,'Dining Sets','Dining tables and chair combinations.',NULL,'Active',NOW(),NOW()),
(10,2,'Side Tables','Accent and bedside tables for every room.',NULL,'Active',NOW(),NOW()),
-- Bedroom
(11,3,'Beds','King, queen and single beds in wood and upholstered.',NULL,'Active',NOW(),NOW()),
(12,3,'Wardrobes','Sliding and hinged wardrobes with mirror.',NULL,'Active',NOW(),NOW()),
(13,3,'Dressing Tables','Vanity tables with mirrors and storage.',NULL,'Active',NOW(),NOW()),
(14,3,'Bedside Tables','Compact side tables for the bedroom.',NULL,'Active',NOW(),NOW()),
(15,3,'Storage Ottomans','Multi-purpose ottomans with hidden storage.',NULL,'Active',NOW(),NOW()),
-- Restaurant & Cafe
(16,4,'Dining Chairs','Stackable and upholstered dining chairs.',NULL,'Active',NOW(),NOW()),
(17,4,'Bar Stools','Counter-height stools for bars and cafes.',NULL,'Active',NOW(),NOW()),
(18,4,'Cafe Tables','Round and square cafe-style tables.',NULL,'Active',NOW(),NOW()),
(19,4,'Booth Seating','Fixed booth seating for restaurants.',NULL,'Active',NOW(),NOW()),
-- Outdoor
(20,5,'Patio Sets','Complete outdoor dining and lounge sets.',NULL,'Active',NOW(),NOW()),
(21,5,'Garden Chairs','Weather-resistant garden chairs.',NULL,'Active',NOW(),NOW()),
(22,5,'Outdoor Sofas','Rattan and aluminium outdoor sofas.',NULL,'Active',NOW(),NOW()),
-- Storage & Cabinets
(23,6,'Bookshelves','Open and closed bookshelves.',NULL,'Active',NOW(),NOW()),
(24,6,'Shoe Racks','Entryway and bedroom shoe storage.',NULL,'Active',NOW(),NOW()),
(25,6,'Console Tables','Narrow console tables for hallways.',NULL,'Active',NOW(),NOW());

-- ── contact_details ───────────────────────────────────────
DROP TABLE IF EXISTS `contact_details`;
CREATE TABLE `contact_details` (
  `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`         enum('mobile','landline','email','address') NOT NULL,
  `label`        varchar(150) DEFAULT NULL,
  `value`        varchar(255) DEFAULT NULL,
  `address_type` enum('Main Office','Branch','Godown') DEFAULT NULL,
  `address_line` varchar(500) DEFAULT NULL,
  `city`         varchar(100) DEFAULT NULL,
  `state`        varchar(100) DEFAULT NULL,
  `pincode`      varchar(10)  DEFAULT NULL,
  `sort_order`   int(11) DEFAULT 0,
  `status`       tinyint(1)   DEFAULT 1,
  `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`   timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `contact_details` VALUES
(1,'mobile',NULL,'+91 1800-123-4567',NULL,NULL,NULL,NULL,NULL,1,1,NOW(),NOW()),
(2,'email',NULL,'support@furnituremarket.in',NULL,NULL,NULL,NULL,NULL,2,1,NOW(),NOW()),
(3,'address','Main Office',NULL,'Main Office','4th Floor, Commerce Tower, MG Road','Bengaluru','Karnataka','560001',3,1,NOW(),NOW());

-- ── sliders ───────────────────────────────────────────────
-- All 3 sliders active (status=1). slider_1.jpg is fixed in
-- the uploads/sliders/ folder (was a corrupt 29-byte file).
DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders` (
  `id`          bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       varchar(255) NOT NULL,
  `subtitle`    varchar(255) DEFAULT NULL,
  `description` text         DEFAULT NULL,
  `image`       varchar(255) NOT NULL,
  `sort_order`  int(11)      DEFAULT 0,
  `status`      tinyint(1)   DEFAULT 1,
  `created_at`  timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`  timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sliders` VALUES
(1,'Design Your Space. Elevate Your Life.','PREMIUM COLLECTION','Explore a wide range of premium furniture for every space — office, home, hospitality and beyond.','uploads/sliders/slider_1.jpg',1,1,NOW(),NOW()),
(2,'Comfort Meets. Crafted Style.','HOME FURNITURE','Timeless sofas, dining sets, and bedroom furniture designed for the way you actually live.','uploads/sliders/slider_2.jpg',2,1,NOW(),NOW()),
(3,'Built for Scale. Made to Last.','FOR PROJECTS','End-to-end furniture solutions for offices, hotels, hospitals and hospitality projects across India.','uploads/sliders/slider_3.jpg',3,1,NOW(),NOW());

-- ── products ──────────────────────────────────────────────
-- is_featured=1 on one product per subcategory so the
-- Featured Collections section on the homepage is populated.
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id`              bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`     bigint(20) UNSIGNED NOT NULL,
  `subcategory_id`  bigint(20) UNSIGNED DEFAULT NULL,
  `product_name`    varchar(255) NOT NULL,
  `slug`            varchar(255) NOT NULL,
  `sku`             varchar(100) DEFAULT NULL,
  `short_description` text       DEFAULT NULL,
  `description`     longtext     DEFAULT NULL,
  `specifications`  longtext     DEFAULT NULL,
  `regular_price`   decimal(12,2) NOT NULL,
  `sale_price`      decimal(12,2) DEFAULT NULL,
  `gst_percentage`  decimal(5,2)  DEFAULT 18.00,
  `stock_quantity`  int(11)       DEFAULT 10,
  `thumbnail`       varchar(255)  DEFAULT NULL,
  `product_status`  enum('Active','Inactive','Draft') DEFAULT 'Active',
  `is_featured`     tinyint(1)    DEFAULT 0,
  `created_at`      timestamp     NOT NULL DEFAULT current_timestamp(),
  `updated_at`      timestamp     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products`
  (`id`,`category_id`,`subcategory_id`,`product_name`,`slug`,`sku`,`short_description`,`regular_price`,`sale_price`,`stock_quantity`,`thumbnail`,`product_status`,`is_featured`)
VALUES
-- ── OFFICE FURNITURE — Executive Tables ──────────────────
(1, 1,1,'Luxury L Shape Director Office Table with Side Storage Unit','luxury-l-shape-director-table','SKU-OT-001','Premium L-shaped director desk with side storage unit and cable management.',134399,102999,5,'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=300&fit=crop','Active',1),
(2, 1,1,'Modern Executive Office Desk with Side Storage Cabinet for CEO','modern-executive-desk-ceo','SKU-OT-002','Spacious CEO desk with integrated side cabinet and modesty panel.',60899,46899,8,'https://images.unsplash.com/photo-1596162954151-cdcb4c0f70fb?w=400&h=300&fit=crop','Active',1),
(3, 1,1,'Luxury CEO Office Table with Integrated Credenza Storage','luxury-ceo-credenza-table','SKU-OT-003','Executive table with attached credenza for maximum storage.',158200,121799,4,'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=400&h=300&fit=crop','Active',0),
(4, 1,1,'CEO Office Table with Attached Storage Credenza Contemporary','ceo-attached-credenza-desk','SKU-OT-004','Contemporary CEO desk with side credenza in engineered wood.',163099,125199,6,'https://images.unsplash.com/photo-1497366754035-f200586c4bd4?w=400&h=300&fit=crop','Active',0),
(5, 1,1,'Designer Executive Office Table with Side Storage Extension','designer-exec-side-storage','SKU-OT-005','Director-grade table with extension unit and laminate finish.',168099,129399,3,'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=400&h=300&fit=crop','Active',0),
-- ── OFFICE FURNITURE — Office Chairs ─────────────────────
(6, 1,2,'Luxury High-Back Leatherette Executive Chair with Lumbar Support','luxury-exec-chair-lumbar','SKU-OC-001','High-back executive chair in premium leatherette with lumbar support.',29999,23199,15,'https://images.unsplash.com/photo-1541558869434-2840d308329a?w=400&h=300&fit=crop','Active',1),
(7, 1,2,'Premium Ergonomic Mesh Chair with Adjustable Armrests','premium-ergonomic-mesh-chair','SKU-OC-002','Full-mesh ergonomic chair with adjustable lumbar and 4D armrests.',24000,18500,20,'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=300&fit=crop','Active',1),
(8, 1,2,'High-Back Leatherette Director Chair Wooden Base','high-back-director-wooden','SKU-OC-003','Classic director chair with wooden base and premium leatherette upholstery.',90499,69399,10,'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=400&h=300&fit=crop','Active',0),
(9, 1,2,'Comfortable Director Chair Velvet Upholstery Adjustable Arm','director-chair-velvet','SKU-OC-004','Velvet-upholstered director chair with adjustable armrests.',15999,12399,25,'https://images.unsplash.com/photo-1596162954151-cdcb4c0f70fb?w=400&h=300&fit=crop','Active',0),
(10,1,2,'High-Back Office Chair Recliner Swivel Ergonomic Lumbar','recliner-swivel-ergonomic','SKU-OC-005','Recliner office chair with swivel base and lumbar support.',102599,78999,8,'https://images.unsplash.com/photo-1574180566232-aaad1b5b8450?w=400&h=300&fit=crop','Active',0),
-- ── OFFICE FURNITURE — Conference Tables ─────────────────
(11,1,3,'12-Seater Premium Conference Table Solid Wood Veneer Finish','12-seater-conference-table','SKU-CT-001','Boardroom conference table with solid wood veneer and cable ports.',210000,162000,2,'https://images.unsplash.com/photo-1564069114553-7215e1ff1890?w=400&h=300&fit=crop','Active',1),
(12,1,3,'8-Seater Boat Shape Conference Table with Central Cable Tray','8-seater-boat-conference','SKU-CT-002','Boat-shaped meeting table with integrated cable management tray.',145000,112000,3,'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=400&h=300&fit=crop','Active',0),
-- ── OFFICE FURNITURE — Workstations ──────────────────────
(13,1,4,'4-Seater Premium Collaborative Workstation with Cable Management','4-seater-collab-workstation','SKU-WS-001','Open-plan 4-seater desk system with modesty panels and cable trunking.',110000,85000,4,'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=300&fit=crop','Active',0),
(14,1,4,'6-Seater Open Plan Office Bench Desk with Storage Pods','6-seater-bench-desk','SKU-WS-002','Bench-style 6-seater workstation with under-desk storage pedestals.',122500,94500,3,'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=400&h=300&fit=crop','Active',0),
-- ── HOME FURNITURE — Sofas ────────────────────────────────
(15,2,6,'3 Seater Sofa Italian Fabric Premium Upholstery Luxury Comfort','3-seater-sofa-italian-fabric','SKU-SF-001','Premium 3-seater sofa in Italian fabric with high-density foam cushions.',92399,71199,10,'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop','Active',1),
(16,2,6,'3 Seater Sofa Tufted Design Leatherette High Quality','3-seater-tufted-leatherette','SKU-SF-002','Tufted 3-seater sofa in premium leatherette with wooden legs.',64999,49899,12,'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=400&h=300&fit=crop','Active',1),
(17,2,6,'Modern Luxury 1 to 3-Seater Sofa Upholstered Fabric Cushioned','modern-luxury-sofa-3seater','SKU-SF-003','Modular sofa available in 1, 2 and 3-seater configurations.',108400,83499,8,'https://images.unsplash.com/photo-1567538096621-38d2284b23ff?w=400&h=300&fit=crop','Active',0),
(18,2,6,'3 Seater Sofa Plyboard Upholster Leatherette Modern Design','3-seater-plyboard-leatherette','SKU-SF-004','Modern sofa with plyboard frame and durable leatherette finish.',92399,71199,9,'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop','Active',0),
(19,2,6,'3 Seater Chesterfield Sofa Premium Suede Fabric Durable','3-seater-chesterfield-suede','SKU-SF-005','Classic Chesterfield 3-seater in premium suede with button tufting.',86800,66899,6,'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=300&fit=crop','Active',0),
-- ── HOME FURNITURE — Dining Sets ─────────────────────────
(20,2,9,'6-Seater Solid Wood Dining Table Set with Upholstered Chairs','6-seater-solid-wood-dining','SKU-DT-001','Solid sheesham wood dining table with 6 upholstered dining chairs.',87400,61200,5,'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=400&h=300&fit=crop','Active',1),
(21,2,9,'4-Seater Marble Top Dining Table with Metal Legs Modern','4-seater-marble-dining','SKU-DT-002','Contemporary marble top dining table with hairpin metal legs.',68000,52500,6,'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=400&h=300&fit=crop','Active',0),
-- ── BEDROOM — Beds ────────────────────────────────────────
(22,3,11,'King Size Upholstered Bed Premium Fabric Headboard Storage','king-upholstered-storage-bed','SKU-BD-001','King-size upholstered bed with hydraulic storage and cushioned headboard.',70700,49500,7,'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&h=300&fit=crop','Active',1),
(23,3,11,'Queen Size Solid Wood Bed Sheesham Natural Finish','queen-solid-wood-sheesham','SKU-BD-002','Queen-size solid sheesham wood bed with natural finish.',58000,44700,9,'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop','Active',1),
(24,3,11,'Modern Platform Bed Low Height Walnut Finish Storage','modern-platform-bed-walnut','SKU-BD-003','Low-profile platform bed in walnut finish with under-bed storage drawers.',75000,57900,5,'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=400&h=300&fit=crop','Active',0),
-- ── BEDROOM — Wardrobes ───────────────────────────────────
(25,3,12,'3-Door Sliding Wardrobe with Mirror Premium Finish','3-door-sliding-mirror-wardrobe','SKU-WD-001','3-door sliding wardrobe with full-length mirror and internal shelving.',65000,50000,8,'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop','Active',1),
(26,3,12,'4-Door Hinged Wardrobe with Drawers Melamine Finish','4-door-hinged-wardrobe','SKU-WD-002','Spacious 4-door hinged wardrobe with multiple drawers and shelves.',78000,60100,6,'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=400&h=300&fit=crop','Active',0),
-- ── RESTAURANT & CAFE — Dining Chairs ────────────────────
(27,4,16,'Stackable Metal Dining Chair Upholstered Seat Restaurant','stackable-metal-dining-chair','SKU-RC-001','Commercial-grade stackable metal chair with upholstered seat.',3500,2700,50,'https://images.unsplash.com/photo-1567538096621-38d2284b23ff?w=400&h=300&fit=crop','Active',1),
(28,4,16,'Wooden Cafeteria Chair with Cushion High-Footfall Durable','wooden-cafeteria-chair','SKU-RC-002','Solid wood cafeteria chair with padded seat — ideal for high-footfall venues.',4800,3700,40,'https://images.unsplash.com/photo-1566312922674-b8ead9dd9f7f?w=400&h=300&fit=crop','Active',0),
-- ── RESTAURANT & CAFE — Cafe Tables ──────────────────────
(29,4,18,'Round Cafe Table HPL Top with Tulip Base Indoor Outdoor','round-cafe-tulip-table','SKU-CT-R01','Round HPL-top cafe table with powder-coated tulip base.',8500,6600,30,'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop','Active',1),
(30,4,18,'Square Laminate Cafe Table with Metal Frame 2-Seater','square-laminate-cafe-table','SKU-CT-S02','2-seater square cafe table with laminate top and black metal frame.',6800,5200,25,'https://images.unsplash.com/photo-1550966871-3ed3cbe818fb?w=400&h=300&fit=crop','Active',0),
-- ── OUTDOOR FURNITURE — Patio Sets ───────────────────────
(31,5,20,'5-Piece Rattan Outdoor Patio Set with Tempered Glass Table','5-piece-rattan-patio-set','SKU-OP-001','Complete 5-piece rattan patio set with weather-resistant cushions.',95000,73000,4,'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=400&h=300&fit=crop','Active',1),
(32,5,20,'4-Piece Aluminium Garden Set Foldable Sunbed Outdoor','aluminium-garden-foldable-set','SKU-OP-002','Lightweight aluminium outdoor set — foldable for easy storage.',68000,52500,6,'https://images.unsplash.com/photo-1588854337236-6889d631faa8?w=400&h=300&fit=crop','Active',0),
-- ── STORAGE & CABINETS — Bookshelves ─────────────────────
(33,6,23,'5-Shelf Open Bookcase Solid Wood Walnut Finish','5-shelf-open-bookcase-walnut','SKU-BS-001','5-tier open bookcase in solid wood with walnut finish.',39700,27800,12,'https://images.unsplash.com/photo-1594620302200-9a762244a156?w=400&h=300&fit=crop','Active',1),
(34,6,23,'Modular Wall-Mounted Shelving System Metal Frame White','modular-wall-shelving-metal','SKU-BS-002','Modular wall-mounted shelving with adjustable metal brackets.',28000,21600,15,'https://images.unsplash.com/photo-1467043237213-65f2da53396f?w=400&h=300&fit=crop','Active',0),
-- ── STORAGE & CABINETS — Console Tables ──────────────────
(35,6,25,'Narrow Console Hallway Table with 2 Drawers Walnut','narrow-console-2-drawer','SKU-CS-001','Slim console table with 2 drawers — perfect for hallways and entryways.',22000,17000,18,'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=400&h=300&fit=crop','Active',0);

-- ── product_images ────────────────────────────────────────
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id`           bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`   bigint(20) UNSIGNED NOT NULL,
  `image`        varchar(255) NOT NULL,
  `alt_text`     varchar(255) DEFAULT NULL,
  `sort_order`   int(11)      DEFAULT 0,
  `is_thumbnail` tinyint(1)   DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── product_specifications ────────────────────────────────
DROP TABLE IF EXISTS `product_specifications`;
CREATE TABLE `product_specifications` (
  `id`                  bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`          bigint(20) UNSIGNED NOT NULL,
  `specification_name`  varchar(100) DEFAULT NULL,
  `specification_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── product_variants ──────────────────────────────────────
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
  `id`            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`    bigint(20) UNSIGNED NOT NULL,
  `variant_name`  varchar(100) DEFAULT NULL,
  `variant_value` varchar(100) DEFAULT NULL,
  `sku`           varchar(100) DEFAULT NULL,
  `price`         decimal(12,2) DEFAULT NULL,
  `stock`         int(11) DEFAULT 0,
  `image`         varchar(255) DEFAULT NULL,
  `status`        tinyint(1)   DEFAULT 1,
  `created_at`    timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── testimonials ──────────────────────────────────────────
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id`          bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `image`       varchar(255) NOT NULL,
  `name`        varchar(150) NOT NULL,
  `company`     varchar(150) DEFAULT NULL,
  `review`      text         NOT NULL,
  `stars`       tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `sort_order`  int(11)      DEFAULT 0,
  `status`      tinyint(1)   DEFAULT 1,
  `created_at`  timestamp    NOT NULL DEFAULT current_timestamp(),
  `updated_at`  timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `testimonials` VALUES
(1,'uploads/testimonials/testimonial_1.jpg','Aakarsh Nair','Studio Vertex Design','We outfitted our entire office floor through Furniture Market. The range and the quality of the executive collection were far beyond what we expected.',4,1,1,NOW(),NOW()),
(2,'uploads/testimonials/testimonial_2.jpg','Karan Mehta','Dining & More','We sourced our living room and dining sets here. The wood collection felt genuinely premium — nothing like a typical catalogue.',5,2,1,NOW(),NOW()),
(3,'uploads/testimonials/testimonial_3.jpg','Priya Nandan','The Nest Café','Great experience in furnishing our café. Support was exceptional — they helped us pick the right pieces for a high-footfall space.',5,3,1,NOW(),NOW()),
(4,'uploads/testimonials/testimonial_4.jpg','Rohit Sinha','Sinha Architecture','Flawless. We recommend Furniture Market to every client now. The custom furniture desk has never missed a lead.',5,4,1,NOW(),NOW());

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
