<?php
function checkPermission($requiredRole) {
    if (!isset($_SESSION['role'])) {
        header("Location: ../login/login.php");
        exit;
    }

    $userRole = $_SESSION['role'];
    $roles = ['employee', 'department_manager', 'admin_manager', 'general_manager', 'admin'];
    $userRoleIndex = array_search($userRole, $roles);
    $requiredRoleIndex = array_search($requiredRole, $roles);

    if ($userRoleIndex === false || $requiredRoleIndex === false || $userRoleIndex < $requiredRoleIndex) {
        header("Location: unauthorized.php");
        exit;
    }
}

function canEdit() {
    return in_array($_SESSION['role'], ['employee', 'department_manager']);
}

function canApprove() {
    return $_SESSION['role'] === 'department_manager';
}
