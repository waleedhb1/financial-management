<?php
// project_functions.php

function addProject($projectData)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // إدخال بيانات المشروع الأساسية
        $stmt = $pdo->prepare("INSERT INTO projects (po_number, contract_name, tender_type, project_nature, start_date, end_date, contract_sign_date, start_minutes_date, vendor, owning_department, payment_method, connection_status, notes, work_completion_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $projectData['po_number'],
            $projectData['contract_name'],
            $projectData['tender_type'],
            $projectData['project_nature'],
            $projectData['start_date'],
            $projectData['end_date'],
            $projectData['contract_sign_date'],
            $projectData['start_minutes_date'],
            $projectData['vendor'],
            $projectData['owning_department'],
            $projectData['payment_method'],
            $projectData['connection_status'],
            $projectData['notes'],
            $projectData['work_completion_date']

        ]);

        $projectId = $pdo->lastInsertId();

        // إضافة الدفعات
        if (!empty($projectData['payments'])) {
            $stmt = $pdo->prepare("INSERT INTO project_payments (project_id, year, payment_date, amount) VALUES (?, ?, ?, ?)");
            foreach ($projectData['payments'] as $year => $payments) {
                foreach ($payments as $payment) {
                    $stmt->execute([$projectId, $year, $payment['date'], $payment['amount']]);
                }
            }
        }

        addUsedYears($projectData['related_years']);

        // تحديث القيم الإجمالية وإدخال السجلات في project_years
        updateProjectTotals($projectId);

        $pdo->commit();
        return $projectId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateProjectTotals($projectId)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT connection_status FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $connectionStatus = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT year, SUM(amount) as total FROM project_payments WHERE project_id = ? GROUP BY year");
    $stmt->execute([$projectId]);
    $yearTotals = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $totalAmount = array_sum($yearTotals);

    // حذف السجلات القديمة في جدول project_years
    $stmt = $pdo->prepare("DELETE FROM project_years WHERE project_id = ?");
    $stmt->execute([$projectId]);

    // إدخال السجلات الجديدة في جدول project_years
    $stmt = $pdo->prepare("INSERT INTO project_years (project_id, year, estimated_value, contractual_value, reserved_value) VALUES (?, ?, ?, ?, ?)");

    foreach ($yearTotals as $year => $amount) {
        $estimatedValue = 0;
        $contractualValue = 0;
        $reservedValue = 0;

        switch ($connectionStatus) {
            case 'تخطيط':
                $estimatedValue = $amount;
                break;
            case 'التزام':
                $contractualValue = $amount;
                break;
            case 'حجز':
                $reservedValue = $amount;
                break;
        }

        $stmt->execute([$projectId, $year, $estimatedValue, $contractualValue, $reservedValue]);
    }

    // تحديث القيم الإجمالية في جدول projects
    $stmt = $pdo->prepare("UPDATE projects SET 
                           total_estimated_value = (SELECT SUM(estimated_value) FROM project_years WHERE project_id = ?),
                           total_contractual_value = (SELECT SUM(contractual_value) FROM project_years WHERE project_id = ?),
                           total_reserved_value = (SELECT SUM(reserved_value) FROM project_years WHERE project_id = ?)
                           WHERE id = ?");
    $stmt->execute([$projectId, $projectId, $projectId, $projectId]);
}

function getProjectPayments($projectId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT year, payment_date, amount 
        FROM project_payments 
        WHERE project_id = ? 
        ORDER BY year ASC, payment_date ASC
    ");
    $stmt->execute([$projectId]);
    
    $payments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $payments[$row['year']][] = [
            'date' => $row['payment_date'],
            'amount' => $row['amount']
        ];
    }
    
    return $payments;
}

function getProjects($filters = [])
{
    global $pdo;

    $activeYearsQuery = "
        SELECT DISTINCT py.year 
        FROM project_years py
        WHERE (
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM projects p 
                    WHERE p.id = py.project_id 
                    AND p.connection_status = 'التزام'
                ) THEN py.contractual_value > 0
                WHEN EXISTS (
                    SELECT 1 
                    FROM projects p 
                    WHERE p.id = py.project_id 
                    AND p.connection_status = 'تخطيط'
                ) THEN py.estimated_value > 0
                WHEN EXISTS (
                    SELECT 1 
                    FROM projects p 
                    WHERE p.id = py.project_id 
                    AND p.connection_status = 'حجز'
                ) THEN py.reserved_value > 0
                ELSE FALSE
            END
        )
        ORDER BY py.year ASC";

    $yearsStmt = $pdo->query($activeYearsQuery);
    $years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

    $yearCases = [];
    foreach ($years as $year) {
        $yearCases[] = "MAX(CASE WHEN py.year = $year THEN
                           CASE 
                             WHEN p.connection_status = 'التزام' THEN NULLIF(py.contractual_value, 0)
                             WHEN p.connection_status = 'تخطيط' THEN NULLIF(py.estimated_value, 0)
                             WHEN p.connection_status = 'حجز' THEN NULLIF(py.reserved_value, 0)
                             ELSE NULL
                           END
                        END) as year_$year";
    }
    $yearCasesStr = implode(', ', $yearCases);

    $sql = "SELECT p.*, 
                   GROUP_CONCAT(DISTINCT py.year ORDER BY py.year) as related_years,
                   $yearCasesStr
            FROM projects p
            LEFT JOIN project_years py ON p.id = py.project_id";

    $where = [];
    $params = [];

    if (!empty($filters['year'])) {
        $where[] = "py.year = ?";
        $params[] = $filters['year'];
    }

    if (!empty($filters['connection_status'])) {
        $where[] = "p.connection_status = ?";
        $params[] = $filters['connection_status'];
    }

    if (!empty($filters['tender_type'])) {
        $where[] = "p.tender_type = ?";
        $params[] = $filters['tender_type'];
    }

    if (!empty($filters['project_nature'])) {
        $where[] = "p.project_nature = ?";
        $params[] = $filters['project_nature'];
    }

    if (!empty($filters['payment_method'])) {
        $where[] = "p.payment_method = ?";
        $params[] = $filters['payment_method'];
    }

    if (!empty($filters['owning_department'])) {
        $where[] = "p.owning_department = ?";
        $params[] = $filters['owning_department'];
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " GROUP BY p.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // إضافة حسابات المدد للمشاريع
    foreach ($projects as &$project) {

        $project['payments'] = getProjectPayments($project['id']);
        
        if ($project['connection_status'] === 'التزام') {
            $project['project_duration'] = calculateProjectDuration(
                $project['start_minutes_date'],
                $project['work_completion_date']
            );

            $project['elapsed_duration'] = calculateElapsedDuration(
                $project['start_minutes_date']
            );

            $project['remaining_duration'] = calculateRemainingDuration(
                $project['project_duration'],
                $project['elapsed_duration']
            );
        } else {
            $project['project_duration'] = '-';
            $project['elapsed_duration'] = '-';
            $project['remaining_duration'] = '-';
        }
    }


    // ترتيب المشاريع حسب المدة المتبقية إذا تم تحديد خيار الترتيب
    if (!empty($filters['remaining_duration_sort'])) {
        usort($projects, function($a, $b) use ($filters) {
            // تحويل القيم '-' إلى -1 للترتيب
            $aValue = $a['remaining_duration'] === '-' ? -1 : (float)$a['remaining_duration'];
            $bValue = $b['remaining_duration'] === '-' ? -1 : (float)$b['remaining_duration'];
            
            // ترتيب تصاعدي أو تنازلي حسب الاختيار
            if ($filters['remaining_duration_sort'] === 'asc') {
                return $aValue <=> $bValue;
            } else {
                return $bValue <=> $aValue;
            }
        });
    }

    return ['projects' => $projects, 'years' => $years];


}



function getProjectById($projectId)
{
    global $pdo;

    $sql = "SELECT p.*, 
                   GROUP_CONCAT(DISTINCT py.year) as related_years
            FROM projects p
            LEFT JOIN project_years py ON p.id = py.project_id
            WHERE p.id = ?
            GROUP BY p.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($project) {
        // استرجاع الدفعات
        $stmt = $pdo->prepare("SELECT year, payment_date, amount FROM project_payments WHERE project_id = ? ORDER BY year, payment_date");
        $stmt->execute([$projectId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // تنظيم الدفعات حسب السنة
        $project['payments'] = [];
        foreach ($payments as $payment) {
            $project['payments'][$payment['year']][] = [
                'date' => $payment['payment_date'],
                'amount' => $payment['amount']
            ];
        }

        // تحويل related_years إلى مصفوفة
        $project['related_years'] = $project['related_years'] ? explode(',', $project['related_years']) : [];
    }

    return $project;
}



// أضف هذه الدالة إلى ملف project_functions.php

function validateProjectData($projectData)
{
    $errors = [];

    // التحقق من الحقول المطلوبة لجميع الحالات
    if (empty($projectData['contract_name'])) {
        $errors[] = "اسم العقد مطلوب";
    }
    if (empty($projectData['owning_department'])) {
        $errors[] = "الإدارة المالكة مطلوبة";
    }
    if (empty($projectData['related_years'])) {
        $errors[] = "يجب اختيار سنة واحدة على الأقل";
    }

    // التحقق من الحقول المطلوبة حسب حالة الارتباط
    switch ($projectData['connection_status']) {
        case 'التزام':
            if (empty($projectData['po_number'])) {
                $errors[] = "رقم أمر الشراء (PO) مطلوب للالتزام";
            }
            if (empty($projectData['start_date'])) {
                $errors[] = "تاريخ البداية مطلوب للالتزام";
            }
            if (empty($projectData['end_date'])) {
                $errors[] = "تاريخ النهاية مطلوب للالتزام";
            }
            if (empty($projectData['contract_sign_date'])) {
                $errors[] = "تاريخ توقيع العقد مطلوب للالتزام";
            }
            if (empty($projectData['start_minutes_date'])) {
                $errors[] = "تاريخ محضر البدء مطلوب للالتزام";
            }
            if (empty($projectData['work_completion_date'])) {
                $errors[] = "تاريخ نهاية الاعمال مطلوب للالتزام";
            }
            if (empty($projectData['project_nature'])) {
                $errors[] = "طبيعة المشروع مطلوبة للالتزام";
            }
            if (empty($projectData['vendor'])) {
                $errors[] = "المورد مطلوب للالتزام";
            }
            if (empty($projectData['tender_type'])) {
                $errors[] = "نوع الطرح مطلوب للالتزام";
            }
            break;
        case 'تخطيط':
            if (empty($projectData['tender_type'])) {
                $errors[] = "نوع الطرح مطلوب للتخطيط";
            }
            break;
        case 'حجز':
            // يمكن إضافة تحققات إضافية للحجز إذا لزم الأمر
            break;
    }

    // التحقق من وجود دفعات لكل سنة مختارة
    foreach ($projectData['related_years'] as $year) {
        if (empty($projectData['payments'][$year])) {
            $errors[] = "يجب إدخال دفعة واحدة على الأقل لسنة $year";
        }
    }

    if (!empty($errors)) {
        throw new Exception(implode("<br>", $errors));
    }
}



function updateProject($projectId, $projectData)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // تحديث بيانات المشروع الأساسية
        $stmt = $pdo->prepare("UPDATE projects SET po_number = ?, contract_name = ?, tender_type = ?, project_nature = ?, start_date = ?, end_date = ?, contract_sign_date = ?, start_minutes_date = ?, vendor = ?, owning_department = ?, connection_status = ?, payment_method = ?, notes = ?, work_completion_date = ? WHERE id = ?");
        $stmt->execute([
            $projectData['po_number'],
            $projectData['contract_name'],
            $projectData['tender_type'],
            $projectData['project_nature'],
            $projectData['start_date'],
            $projectData['end_date'],
            $projectData['contract_sign_date'],
            $projectData['start_minutes_date'],
            $projectData['vendor'],
            $projectData['owning_department'],
            $projectData['connection_status'],
            $projectData['payment_method'],
            $projectData['notes'],
            $projectData['work_completion_date'],
            $projectId
        ]);

        // حذف جميع الدفعات القديمة
        $stmt = $pdo->prepare("DELETE FROM project_payments WHERE project_id = ?");
        $stmt->execute([$projectId]);

        // إضافة الدفعات الجديدة
        if (!empty($projectData['payments'])) {
            $stmt = $pdo->prepare("INSERT INTO project_payments (project_id, year, payment_date, amount) VALUES (?, ?, ?, ?)");
            foreach ($projectData['payments'] as $year => $payments) {
                foreach ($payments as $payment) {
                    $stmt->execute([$projectId, $year, $payment['date'], $payment['amount']]);
                }
            }
        }

        // تحديث السنوات المرتبطة
        $stmt = $pdo->prepare("DELETE FROM project_years WHERE project_id = ?");
        $stmt->execute([$projectId]);

        if (!empty($projectData['related_years'])) {
            $stmt = $pdo->prepare("INSERT INTO project_years (project_id, year) VALUES (?, ?)");
            foreach ($projectData['related_years'] as $year) {
                $stmt->execute([$projectId, $year]);
            }
        }

        // إعادة حساب وتحديث القيم الإجمالية
        updateProjectTotals($projectId);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function deleteProject($projectId)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // حذف الدفعات المرتبطة
        $stmt = $pdo->prepare("DELETE FROM project_payments WHERE project_id = ?");
        $stmt->execute([$projectId]);

        // حذف السنوات المرتبطة
        $stmt = $pdo->prepare("DELETE FROM project_years WHERE project_id = ?");
        $stmt->execute([$projectId]);

        // حذف المشروع نفسه
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function calculateBudgetImpact($year)
{
    global $pdo;

    $sql = "SELECT 
                SUM(CASE WHEN connection_status = 'تخطيط' THEN COALESCE(estimated_value_$year, 0) ELSE 0 END) as total_estimated,
                SUM(CASE WHEN connection_status IN ('التزام', 'حجز') THEN COALESCE(contractual_value_$year, 0) + COALESCE(reserved_value_$year, 0) ELSE 0 END) as total_committed
            FROM projects
            JOIN project_years ON projects.id = project_years.project_id
            WHERE project_years.year = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function addUsedYears($years)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT IGNORE INTO used_years (year) VALUES (?)");
    foreach ($years as $year) {
        $stmt->execute([$year]);
    }
}


function calculateProjectDuration($startMinutesDate, $workCompletionDate)
{
    if (
        empty($startMinutesDate) || empty($workCompletionDate) ||
        $startMinutesDate === '-' || $workCompletionDate === '-'
    ) {
        return '-';
    }

    try {
        $start = new DateTime($startMinutesDate);
        $end = new DateTime($workCompletionDate);

        $interval = $start->diff($end);
        $months = ($interval->y * 12) + $interval->m;
        if ($interval->d > 0) {
            $months++; // تقريب للشهر الأعلى إذا كان هناك أيام إضافية
        }

        return $months;
    } catch (Exception $e) {
        return '-';
    }
}

function calculateElapsedDuration($startMinutesDate)
{
    if (empty($startMinutesDate) || $startMinutesDate === '-') {
        return '-';
    }

    try {
        $start = new DateTime($startMinutesDate);
        $now = new DateTime();

        $interval = $start->diff($now);
        $months = ($interval->y * 12) + $interval->m;
        if ($interval->d > 0) {
            $months++; // تقريب للشهر الأعلى إذا كان هناك أيام إضافية
        }

        return $months;
    } catch (Exception $e) {
        return '-';
    }
}

function calculateRemainingDuration($projectDuration, $elapsedDuration)
{
    if (!is_numeric($projectDuration) || !is_numeric($elapsedDuration)) {
        return '-';
    }

    $remaining = $projectDuration - $elapsedDuration;
    return max(0, $remaining); // لا يمكن أن تكون المدة المتبقية سالبة
}

