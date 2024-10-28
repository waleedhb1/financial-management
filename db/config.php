<?php
// config.php

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'financial_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// مسار الأساس للتطبيق
define('BASE_PATH', '../login/');

// إنشاء اتصال PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// دالة للحصول على اتصال قاعدة البيانات
function getDBConnection() {
    global $pdo;
    return $pdo;
}