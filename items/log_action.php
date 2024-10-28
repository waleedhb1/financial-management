<?php
// log_action.php
require_once '../db/config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if ($action) {
        logAction($action, $description);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No action provided']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>