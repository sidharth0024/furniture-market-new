<?php

$dbHost = 'localhost';
$dbName = 'furniture_market_demo';
$dbUser = 'root';
$dbPass = '';
// $dbHost = 'localhost';
// $dbName = 'nmcorporat_furniture_shoppers_db';
// $dbUser = 'nmcorporat_furniture_shoppers_user';
// $dbPass = 'Lgr[MI=r{?iak3f-';

$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
$pdoOptions = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $dbUser, $dbPass, $pdoOptions);
} catch (PDOException $e) {
  die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
