<?php
// save_budget.php
require_once '../db/config.php';
require_once 'functions.php';
require_once '../login/session_check.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $years = $_POST['years'];
    
    try {
        saveBudget($years);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}