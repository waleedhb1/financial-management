<?php
function saveBudget($years) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO budgets () VALUES ()");
        $stmt->execute();
        $budgetId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO budget_years (budget_id, year, amount) VALUES (?, ?, ?)");
        foreach ($years as $year) {
            $stmt->execute([$budgetId, $year['year'], $year['amount']]);
        }
        
        $pdo->commit();
        
        logAction('إدخال موازنة', 'تم إدخال موازنة جديدة بـ ' . count($years) . ' سنوات');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getBudget() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT b.id, budget_y.year, budget_y.amount 
                         FROM budgets b 
                         LEFT JOIN budget_years budget_y ON b.id = budget_y.budget_id 
                         ORDER BY b.id DESC, budget_y.year ASC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$result) {
        return null;
    }
    
    $budget = [
        'id' => $result[0]['id'],
        'years' => []
    ];
    
    foreach ($result as $row) {
        if ($row['year'] !== null) {
            $budget['years'][] = [
                'year' => $row['year'],
                'amount' => $row['amount']
            ];
        }
    }
    
    return $budget;
}

function updateBudget($budgetId, $years) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM budget_years WHERE budget_id = ?");
        $stmt->execute([$budgetId]);
        
        $stmt = $pdo->prepare("INSERT INTO budget_years (budget_id, year, amount) VALUES (?, ?, ?)");
        foreach ($years as $year) {
            $stmt->execute([$budgetId, $year['year'], $year['amount']]);
        }
        
        $pdo->commit();
        
        logAction('تعديل موازنة', 'تم تعديل الموازنة رقم ' . $budgetId . ' بـ ' . count($years) . ' سنوات');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function budgetExists() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM budgets");
    return $stmt->fetchColumn() > 0;
}

function deleteBudget($budgetId) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM budgets WHERE id = ?");
        $stmt->execute([$budgetId]);
        
        $pdo->commit();
        
        logAction('حذف موازنة', 'تم حذف الموازنة رقم ' . $budgetId);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function logAction($action, $description = '') {
    global $pdo;
    
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'غير مسجل';
    
    $stmt = $pdo->prepare("INSERT INTO logs (username, action, description) VALUES (?, ?, ?)");
    $stmt->execute([$username, $action, $description]);
}

function getPendingChanges() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT pc.*, u.username FROM pending_changes pc
                         JOIN users u ON pc.user_id = u.id
                         WHERE pc.status = 'pending'
                         ORDER BY pc.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function approveChange($changeId) {
    global $pdo;
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM pending_changes WHERE id = ?");
        $stmt->execute([$changeId]);
        $change = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($change['change_type'] === 'update') {
            $stmt = $pdo->prepare("UPDATE budget_years SET amount = ? WHERE id = ?");
            $stmt->execute([$change['amount'], $change['budget_year_id']]);
        } elseif ($change['change_type'] === 'insert') {
            $stmt = $pdo->prepare("INSERT INTO budget_years (budget_id, year, amount) VALUES (?, ?, ?)");
            $stmt->execute([getBudgetId(), $change['year'], $change['amount']]);
        }
        
        $stmt = $pdo->prepare("UPDATE pending_changes SET status = 'approved' WHERE id = ?");
        $stmt->execute([$changeId]);
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function rejectChange($changeId) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE pending_changes SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$changeId]);
}

function getChangeTypeText($changeType) {
    switch ($changeType) {
        case 'insert':
            return 'إضافة';
        case 'update':
            return 'تحديث';
        case 'delete':
            return 'حذف';
        default:
            return 'غير معروف';
    }
}

function getBudgetId() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT id FROM budgets ORDER BY id DESC LIMIT 1");
    return $stmt->fetchColumn();
}

function sendPendingChanges($budgetId, $years) {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO pending_changes (user_id, change_type, budget_year_id, year, amount) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($years as $year) {
            $existingYear = $pdo->query("SELECT id FROM budget_years WHERE budget_id = $budgetId AND year = {$year['year']}")->fetch();
            
            if ($existingYear) {
                // إذا كانت السنة موجودة، نقوم بتحديثها
                $stmt->execute([$userId, 'update', $existingYear['id'], $year['year'], $year['amount']]);
            } else {
                // إذا كانت السنة جديدة، نقوم بإضافتها
                $stmt->execute([$userId, 'insert', null, $year['year'], $year['amount']]);
            }
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}