<?php
// delete_budget.php
require_once '../db/config.php';
require_once 'functions.php';
require_once '../login/session_check.php';

require_once '../admin/check_permissions.php';
checkPermission('employee');

if (!canEdit()) {
    echo "ليس لديك الصلاحية للتعديل.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $budgetId = $_GET['id'];
    
    try {
        deleteBudget($budgetId);
        $_SESSION['success_message'] = "تم حذف الموازنة بنجاح.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء محاولة حذف الموازنة: " . $e->getMessage();
    }
    
    header('Location: index.php');
    exit;
} else {
    header('Location: edit_budget.php');
    exit;
}
