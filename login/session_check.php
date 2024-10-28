<?php
require_once '../db/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_PATH . "login.php");
    exit;
}
