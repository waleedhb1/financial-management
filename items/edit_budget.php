<?php
// edit_budget.php
require_once '../db/config.php';
require_once 'functions.php';
require_once '../login/session_check.php';

require_once '../admin/check_permissions.php';
checkPermission('employee');

$hasPermission = canEdit();


$budget = getBudget();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST'){ 
    try {
        $years = [];
        $usedYears = [];
        foreach ($_POST['years'] as $yearData) {
            if (!empty($yearData['year']) && isset($yearData['amount'])) {
                if (in_array($yearData['year'], $usedYears)) {
                    throw new Exception("خطأ: السنة " . $yearData['year'] . " مكررة. يرجى التأكد من عدم تكرار السنوات.");
                }
                $usedYears[] = $yearData['year'];
                $years[] = [
                    'year' => $yearData['year'],
                    'amount' => $yearData['amount']
                ];
            }
        }
        
        // بدلاً من تحديث الموازنة مباشرة، نرسل التغييرات إلى جدول التغييرات المعلقة
        sendPendingChanges(budgetId: $budget['id'], years: $years);
        $_SESSION['success_message'] = "تم إرسال التغييرات للموافقة عليها من قبل مدير القسم.";
        header('Location: view.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الإدارة المالية - تعديل الموازنة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php include '../sidebar/sidebar.php'; ?>
    
    <div class="content">
        <h1 class="mb-4">تعديل الموازنة</h1>

        <?php if (!$hasPermission): ?>
        <div class="alert alert-warning" role="alert">
            عذراً، ليس لديك الصلاحية لتعديل الموازنة.
        </div>

        <?php elseif ($budget): ?>
            <form id="budgetForm" method="POST">
                <div id="yearsContainer">
                    <?php foreach ($budget['years'] as $index => $year): ?>
                    <div class="year-entry mb-3">
                        <div class="input-group">
                            <select class="form-select year-select" name="years[<?=$index?>][year]" required>
                                <?php for($y = date('Y') - 5; $y <= date('Y') + 10; $y++): ?>
                                    <option value="<?=$y?>" <?= $year['year'] == $y ? 'selected' : '' ?>><?=$y?></option>
                                <?php endfor; ?>
                            </select>
                            <input type="number" class="form-control" name="years[<?=$index?>][amount]" value="<?= $year['amount'] ?>" placeholder="المبلغ" required>
                            <button type="button" class="btn btn-danger delete-year">حذف</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-secondary" id="addYearBtn">إضافة سنة</button>
                <button type="button" class="btn btn-primary" id="saveChangesBtn">حفظ التغييرات</button>
            </form>
            
            <hr class="my-4">
            
            <h2 class="mb-3">حذف الموازنة</h2>
            <p class="text-danger">تحذير: سيؤدي هذا الإجراء إلى حذف الموازنة الحالية بشكل نهائي.</p>
            <button type="button" class="btn btn-danger" id="deleteBudgetBtn">حذف الموازنة</button>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                لا توجد موازنة مسجلة حالياً. يرجى <a href="index.php">إنشاء موازنة جديدة</a>.
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'confirm_modal.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const yearsContainer = document.getElementById('yearsContainer');
        const addYearBtn = document.getElementById('addYearBtn');
        const saveChangesBtn = document.getElementById('saveChangesBtn');
        const deleteBudgetBtn = document.getElementById('deleteBudgetBtn');
        const budgetForm = document.getElementById('budgetForm');
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmAction = document.getElementById('confirmAction');
        let yearCount = <?= count($budget['years']) ?>;

        addYearBtn.addEventListener('click', function() {
            confirmMessage.textContent = 'هل أنت متأكد من إضافة سنة جديدة؟';
            confirmAction.onclick = function() {
                addNewYearEntry();
                modal.hide();
            };
            modal.show();
        });

        yearsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-year')) {
                const yearEntry = e.target.closest('.year-entry');
                const yearValue = yearEntry.querySelector('.year-select').value;
                confirmMessage.textContent = `هل أنت متأكد من حذف السنة ${yearValue}؟`;
                confirmAction.onclick = function() {
                    yearEntry.remove();
                    updateYearIndices();
                    modal.hide();
                };
                modal.show();
            }
        });


        saveChangesBtn.addEventListener('click', function() {
            if (validateForm()) {
                confirmMessage.textContent = 'هل أنت متأكد من حفظ التغييرات على الموازنة؟';
                confirmAction.onclick = function() {
                    budgetForm.submit();
                };
                modal.show();
            }
        });

        deleteBudgetBtn.addEventListener('click', function() {
            confirmMessage.textContent = 'هل أنت متأكد أنك تريد حذف الموازنة الحالية؟ هذا الإجراء لا يمكن التراجع عنه.';
            confirmAction.onclick = function() {
                window.location.href = 'delete_budget.php?id=<?= $budget['id'] ?>';
            };
            modal.show();
        });

        function addNewYearEntry() {
            const newYearEntry = document.createElement('div');
            newYearEntry.className = 'year-entry mb-3';
            newYearEntry.innerHTML = `
                <div class="input-group">
                    <select class="form-select year-select" name="years[${yearCount}][year]" required>
                        ${generateYearOptions()}
                    </select>
                    <input type="number" class="form-control" name="years[${yearCount}][amount]" placeholder="المبلغ" required>
                    <button type="button" class="btn btn-danger delete-year">حذف</button>
                </div>
            `;
            yearsContainer.appendChild(newYearEntry);
            yearCount++;
        }

        
        

        function generateYearOptions() {
            let options = '';
            const currentYear = new Date().getFullYear();
            for (let year = currentYear - 5; year <= currentYear + 10; year++) {
                options += `<option value="${year}">${year}</option>`;
            }
            return options;
        }

        function updateYearIndices() {
            const yearEntries = yearsContainer.querySelectorAll('.year-entry');
            yearEntries.forEach((entry, index) => {
                const select = entry.querySelector('select');
                const input = entry.querySelector('input');
                select.name = `years[${index}][year]`;
                input.name = `years[${index}][amount]`;
            });
        }

        function validateForm() {
            const yearSelects = document.querySelectorAll('.year-select');
            const selectedYears = new Set();
            let hasDuplicate = false;

            yearSelects.forEach(select => {
                if (selectedYears.has(select.value)) {
                    hasDuplicate = true;
                } else {
                    selectedYears.add(select.value);
                }
            });

            if (hasDuplicate) {
                alert('خطأ: يوجد تكرار في السنوات. يرجى التأكد من اختيار سنوات مختلفة لكل إدخال.');
                return false;
            }

            return true;
        }
    });
    </script>
</body>
</html>