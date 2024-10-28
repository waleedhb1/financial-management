<?php
// index.php
require_once '../db/config.php';
require_once 'functions.php';
require_once '../login/session_check.php';

require_once '../admin/check_permissions.php';
checkPermission('employee');

$hasPermission = canEdit();

$budgetExists = budgetExists();

$error = null;
$minYears = 3; // الحد الأدنى لعدد السنوات

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !budgetExists()) {
    try {
        $years = [];
        $usedYears = [];
        foreach ($_POST['years'] as $yearData) {
            if (!empty($yearData['year']) && !empty($yearData['amount'])) {
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
        
        saveBudget($years);
        logAction('إدخال موازنة جديدة', 'تم إدخال موازنة جديدة بـ ' . count($years) . ' سنوات');
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
    <title>نظام الإدارة المالية - إدخال الموازنة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php include '../sidebar/sidebar.php'; ?>
    
    <div class="content">
        <h1 class="mb-4">إدخال الموازنة</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!$hasPermission): ?>
            <div class="alert alert-warning" role="alert">
                عذراً، ليس لديك الصلاحية لإدخال الموازنة.
            </div>
        <?php elseif ($budgetExists): ?>
            <div class="alert alert-warning" role="alert">
                يوجد موازنة مسجلة مسبقاً. لا يمكن إدخال موازنة جديدة. 
                يمكنك <a href="edit_budget.php" class="alert-link">تعديل الموازنة الحالية</a> أو <a href="view.php" class="alert-link">عرض تفاصيل الموازنة</a>.
            </div>
        <?php else: ?>
            <form id="budgetForm" method="POST">
                <div id="yearsContainer">
                    <?php for($i = 1; $i <= $minYears; $i++): ?>
                    <div class="year-entry mb-3">
                        <div class="input-group">
                            <select class="form-select year-select" name="years[<?=$i?>][year]" required>
                                <?php for($year = date('Y'); $year <= date('Y') + 10; $year++): ?>
                                    <option value="<?=$year?>"><?=$year?></option>
                                <?php endfor; ?>
                            </select>
                            <input type="number" class="form-control" name="years[<?=$i?>][amount]" placeholder="المبلغ" required>
                            <?php if ($i > $minYears): ?>
                            <button type="button" class="btn btn-danger delete-year">حذف</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <button type="button" class="btn btn-secondary" id="addYearBtn">إضافة سنة</button>
                <button type="button" class="btn btn-primary" id="saveBudgetBtn">حفظ الموازنة</button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include 'confirm_modal.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const yearsContainer = document.getElementById('yearsContainer');
        const addYearBtn = document.getElementById('addYearBtn');
        const saveBudgetBtn = document.getElementById('saveBudgetBtn');
        const budgetForm = document.getElementById('budgetForm');
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmAction = document.getElementById('confirmAction');
        let yearCount = <?= $minYears ?>;

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

        saveBudgetBtn.addEventListener('click', function() {
            if (validateForm()) {
                confirmMessage.textContent = 'هل أنت متأكد من حفظ الموازنة بهذه البيانات؟';
                confirmAction.onclick = function() {
                    budgetForm.submit();
                };
                modal.show();
            }
        });

        function addNewYearEntry() {
            yearCount++;
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
        }

        function generateYearOptions() {
            let options = '';
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year <= currentYear + 10; year++) {
                options += `<option value="${year}">${year}</option>`;
            }
            return options;
        }

        function updateYearIndices() {
            const yearEntries = yearsContainer.querySelectorAll('.year-entry');
            yearEntries.forEach((entry, index) => {
                const select = entry.querySelector('select');
                const input = entry.querySelector('input');
                select.name = `years[${index + 1}][year]`;
                input.name = `years[${index + 1}][amount]`;
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