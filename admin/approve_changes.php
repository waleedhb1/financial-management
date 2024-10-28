<?php
// approve_changes.php
require_once '../db/config.php';
require_once '../items/functions.php';
require_once '../login/session_check.php';

require_once '../admin/check_permissions.php';
checkPermission('department_manager');

$pendingChanges = getPendingChanges();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $changeId = $_POST['change_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        approveChange($changeId);
    } elseif ($action === 'reject') {
        rejectChange($changeId);
    }
    
    header('Location: approve_changes.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الموافقة على تغييرات الموازنة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../sidebar/sidebar.php'; ?>

<div class="content">
    <h1 class="mb-4">الموافقة على تغييرات الموازنة</h1>
    
    <?php if (empty($pendingChanges)): ?>
        <div class="alert alert-info">لا توجد تغييرات معلقة للموافقة عليها.</div>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>نوع التغيير</th>
                    <th>السنة</th>
                    <th>المبلغ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingChanges as $change): ?>
                    <tr>
                        <td><?= htmlspecialchars($change['username']) ?></td>
                        <td><?= getChangeTypeText($change['change_type']) ?></td>
                        <td><?= htmlspecialchars($change['year']) ?></td>
                        <td><?= number_format($change['amount'], 2) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="change_id" value="<?= $change['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">موافقة</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">رفض</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>