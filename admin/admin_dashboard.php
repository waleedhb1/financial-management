<?php
require_once '../db/config.php';
session_start();

// التحقق من أن المستخدم هو الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/login.php");
    exit;
}

// استعلام لجلب جميع المستخدمين
$stmt = $pdo->query("SELECT id, username, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// معالجة تحديث الصلاحيات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $newRole = $_POST['new_role'];
    
    $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $updateStmt->execute([$newRole, $userId]);
    
    // إعادة توجيه لتحديث الصفحة
    header("Location: ../admin/admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن - نظام الإدارة المالية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            width: 250px;
            padding-top: 20px;
            background-color: #f8f9fa;
            border-left: 1px solid #dee2e6;
        }
        .content {
            margin-right: 250px;
            padding: 20px;
        }

        .nav-link.logout-link {
            background-color: #fff0f0; /* لون خلفية فاتح للخروج */
            border-left: 4px solid #dc3545; /* شريط أحمر على الجانب الأيسر */
            margin-top: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            color: #dc3545 !important; /* لون النص أحمر */
            font-weight: bold;
        }

        .nav-link.logout-link:hover {
            background-color: #ffe6e6; /* لون خلفية أغمق قليلاً عند التحويم */
            border-left-color: #bd2130; /* لون أغمق للشريط الجانبي عند التحويم */
            color: #bd2130 !important; /* لون النص أغمق عند التحويم */
        }

        .nav-link.logout-link i {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3 class="text-center mb-4">لوحة تحكم الأدمن</h3>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin_dashboard.php">تصنيف المستخدمين</a>
            </li>
            <li class="nav-item">
        <a href="../login/logout.php" class="nav-link logout-link">
            <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
        </a>
    </li>
        </ul>
    </div>

    <div class="content">
        <h2 class="mb-4">تصنيف المستخدمين</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>اسم المستخدم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الصلاحية الحالية</th>
                    <th>تغيير الصلاحية</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="new_role" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="employee" <?= $user['role'] == 'employee' ? 'selected' : '' ?>>موظف</option>
                                <option value="department_manager" <?= $user['role'] == 'department_manager' ? 'selected' : '' ?>>مدير قسم</option>
                                <option value="admin_manager" <?= $user['role'] == 'admin_manager' ? 'selected' : '' ?>>مدير الإدارة</option>
                                <option value="general_manager" <?= $user['role'] == 'general_manager' ? 'selected' : '' ?>>مدير عام</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">تحديث</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>