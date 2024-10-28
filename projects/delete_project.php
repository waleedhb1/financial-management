<?php
// delete_project.php
require_once '../db/config.php';
require_once '../items/functions.php';
require_once '../login/session_check.php';
require_once 'project_functions.php';

// التحقق من الصلاحيات
require_once '../admin/check_permissions.php';
checkPermission('employee');

$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    $_SESSION['error_message'] = "لم يتم تحديد المشروع المراد حذفه.";
    header('Location: view_projects.php');
    exit;
}

try {
    $project = getProjectById($projectId);
    if (!$project) {
        throw new Exception("المشروع غير موجود.");
    }

    deleteProject($projectId);
    $_SESSION['success_message'] = "تم حذف المشروع بنجاح.";
} catch (Exception $e) {
    $_SESSION['error_message'] = "حدث خطأ أثناء محاولة حذف المشروع: " . $e->getMessage();
}

header('Location: view_projects.php');
exit;
