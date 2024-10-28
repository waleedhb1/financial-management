<?php
require_once '../db/config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // تخزين دور المستخدم في الجلسة

        // تسجيل عملية الدخول
        $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, username, login_time) VALUES (?, ?, NOW())");
        $stmt->execute([$user['id'], $user['username']]);

        // توجيه المستخدم بناءً على دوره
        if ($user['role'] === 'admin') {
            header("Location: ../admin/admin_dashboard.php");
        } else {
            header("Location: " . BASE_PATH . "../items/view.php");
        }
        exit;
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام الإدارة المالية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 240px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="logo-container">
                    <img src="../images/logo.png" alt="شعار النظام" class="logo">
                </div>
                <h2 class="mb-4 text-center">تسجيل الدخول</h2>
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                if ($error) {
                    echo '<div class="alert alert-danger">' . $error . '</div>';
                }
                ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">تسجيل الدخول</button>
                    </div>
                </form>
                <p class="mt-3 text-center">ليس لديك حساب؟ <a href="register.php">التسجيل</a></p>
            </div>
        </div>
    </div>
</body>
</html>