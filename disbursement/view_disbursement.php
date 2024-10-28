<?php
// view_disbursement.php
require_once '../db/config.php';
require_once '../login/session_check.php';

// التحقق من الصلاحيات
require_once '../admin/check_permissions.php';
checkPermission('employee');

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطة الصرف المالي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .content {
            margin-right: 280px;
            padding: 2rem;
            transition: margin-right 0.3s ease-in-out;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.125);
            padding: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .placeholder-content {
            padding: 2rem;
            text-align: center;
            color: #6c757d;
        }

        .placeholder-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }
        
    </style>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>

    <div class="content">
        <div class="container">
            <h1 class="mb-4">خطة الصرف المالي</h1>
            
            <!-- بطاقة موجز خطة الصرف -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">موجز خطة الصرف</h5>
                </div>
                <div class="card-body">
                    <div class="placeholder-content">
                        <i class="bi bi-cash-coin placeholder-icon"></i>
                        <h4>لم يتم إضافة خطة صرف بعد</h4>
                        <p>يمكنك البدء بإضافة خطة الصرف المالي الخاصة بك</p>
                        <!-- يمكن إضافة أزرار أو روابط للإجراءات هنا -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>