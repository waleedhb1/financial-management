<?php
// view.php
require_once '../db/config.php';
require_once 'functions.php';
require_once '../login/session_check.php';

$budget = getBudget();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الإدارة المالية - عرض الموازنة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../sidebar/sidebar.php'; ?>
    
    <div class="content">
        <h1 class="mb-4">عرض الموازنة</h1>
        
        <?php if ($budget): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>السنة</th>
                            <th>المبلغ المعتمد</th>
                            <th>المنصرف</th>
                            <th>المتبقي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalApproved = 0;
                        $totalSpent = 0;
                        $totalRemaining = 0;
                        foreach ($budget['years'] as $year): 
                            $spent = 0; // يمكنك استبدال هذا بالقيمة الفعلية من قاعدة البيانات
                            $remaining = $year['amount'] - $spent;
                            $totalApproved += $year['amount'];
                            $totalSpent += $spent;
                            $totalRemaining += $remaining;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($year['year']) ?></td>
                            <td><?= number_format($year['amount'], 2) ?></td>
                            <td><?= number_format($spent, 2) ?></td>
                            <td><?= number_format($remaining, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td>المجموع</td>
                            <td><?= number_format($totalApproved, 2) ?></td>
                            <td><?= number_format($totalSpent, 2) ?></td>
                            <td><?= number_format($totalRemaining, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <a href="export_budget.php" class="btn btn-success">تصدير إلى Excel</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                لا توجد موازنة مسجلة حالياً. يرجى <a href="index.php" class="alert-link">إنشاء موازنة جديدة</a>.
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>