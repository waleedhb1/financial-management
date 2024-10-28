<?php
// edit_project.php
require_once '../db/config.php';
require_once '../items/functions.php';
require_once '../login/session_check.php';
require_once 'project_functions.php';

// التحقق من الصلاحيات
require_once '../admin/check_permissions.php';
checkPermission('employee');

$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    header('Location: view_projects.php');
    exit;
}

$project = getProjectById($projectId);
if (!$project) {
    header('Location: view_projects.php');
    exit;
}

$budget = getBudget();
$budgetYears = array_column($budget['years'], 'year');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $projectData = [
            'po_number' => $_POST['po_number'] ?? '-',
            'contract_name' => $_POST['contract_name'],
            'tender_type' => $_POST['tender_type'],
            'project_nature' => $_POST['project_nature'] ?? '-',
            'start_date' => $_POST['start_date'] ?? '-',
            'end_date' => $_POST['end_date'] ?? '-',
            'contract_sign_date' => $_POST['contract_sign_date'] ?? '-',
            'start_minutes_date' => $_POST['start_minutes_date'] ?? '-',
            'vendor' => $_POST['vendor'] ?? '-',
            'owning_department' => $_POST['owning_department'],
            'related_years' => $_POST['related_years'] ?? [],
            'connection_status' => $_POST['connection_status'],
            'payment_method' => $_POST['payment_method'] ?? null,
            'notes' => $_POST['notes'] ?? '',
            'work_completion_date' => $_POST['work_completion_date'] ?? '-',
            'payments' => $_POST['payments'] ?? []
        ];

        updateProject($projectId, $projectData);
        $_SESSION['success_message'] = "تم تحديث المشروع بنجاح.";
        header('Location: view_projects.php');
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
    <title>تعديل المشروع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

        .year-total {
      color: #2d72fc;
      font-weight: bold;
       
    }
    

    .form-section h4 {
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }

    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .payment-summary {
        background-color: #e9ecef;
        padding: 8px 15px;
        border-radius: 4px;
        display: inline-block;
    }

    .actions-group {
        margin-top: 10px;
        display: flex;
        gap: 10px;
    }

    .table td, .table th {
        vertical-align: middle;
    }

   
   

    /* تحسين مظهر الأزرار */
    .btn-group-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #dee2e6;
    }   
    </style>
    <?php include 'confirm_modal.php'; ?>
</head>
<body>
    <?php include '../sidebar/sidebar.php'; ?>

    

    
    <div class="content">
    <h1 class="mb-4">تعديل المشروع</h1>


        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="editProjectForm" method="POST">

        <div class="form-section">
            <!-- الحقول الأساسية -->
            <div class="row">


            <div class="mb-3">
                    <label for="connection_status" class="form-label">حالة الارتباط</label>
                    <select class="form-select" id="connection_status" name="connection_status" required>
                        <option value="">اختر حالة الارتباط</option>
                        <option value="التزام" <?php echo $project['connection_status'] == 'التزام' ? 'selected' : ''; ?>>التزام</option>
                        <option value="تخطيط" <?php echo $project['connection_status'] == 'تخطيط' ? 'selected' : ''; ?>>تخطيط</option>
                        <option value="حجز" <?php echo $project['connection_status'] == 'حجز' ? 'selected' : ''; ?>>حجز</option>
                        <option value="منتهي" <?php echo $project['connection_status'] == 'منتهي' ? 'selected' : ''; ?>>منتهي</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="po_number" class="form-label">رقم أمر الشراء (PO)</label>
                    <input type="number" class="form-control" id="po_number" name="po_number"
                        value="<?php echo htmlspecialchars($project['po_number']); ?>">
                </div>

                <div class="mb-3">
                    <label for="contract_name" class="form-label">اسم العقد</label>
                    <input type="text" class="form-control" id="contract_name" name="contract_name" required
                        value="<?php echo htmlspecialchars($project['contract_name']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="mb-3">
                    <label for="tender_type" class="form-label">نوع الطرح</label>
                    <select class="form-select" id="tender_type" name="tender_type" required>
                        <option value="">اختر نوع الطرح</option>
                        <option value="منافسة عامة" <?php echo $project['tender_type'] == 'منافسة عامة' ? 'selected' : ''; ?>>منافسة عامة</option>
                        <option value="شراء مباشر" <?php echo $project['tender_type'] == 'شراء مباشر' ? 'selected' : ''; ?>>شراء مباشر</option>
                        <option value="سوق الكتروني" <?php echo $project['tender_type'] == 'سوق الكتروني' ? 'selected' : ''; ?>>سوق الكتروني</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="project_nature" class="form-label">طبيعة المشروع</label>
                    <select class="form-select" id="project_nature" name="project_nature">
                        <option value="">اختر طبيعة المشروع</option>
                        <option value="توريد" <?php echo $project['project_nature'] == 'توريد' ? 'selected' : ''; ?>>توريد</option>
                        <option value="تشغيل" <?php echo $project['project_nature'] == 'تشغيل' ? 'selected' : ''; ?>>تشغيل</option>
                        <option value="-" <?php echo $project['project_nature'] == '-' ? 'selected' : ''; ?>>-</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="vendor" class="form-label">المورد</label>
                    <input type="text" class="form-control" id="vendor" name="vendor"
                        value="<?php echo htmlspecialchars($project['vendor']); ?>">
                </div>
            </div>
            </div>

            <!-- التواريخ -->
            <div class="form-section">
            <div class="row">
                <div class="mb-3">
                    <label for="start_date" class="form-label">تاريخ البداية</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                        value="<?php echo $project['start_date'] != '-' ? $project['start_date'] : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="end_date" class="form-label">تاريخ النهاية</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                        value="<?php echo $project['end_date'] != '-' ? $project['end_date'] : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="contract_sign_date" class="form-label">تاريخ توقيع العقد</label>
                    <input type="date" class="form-control" id="contract_sign_date" name="contract_sign_date"
                        value="<?php echo $project['contract_sign_date'] != '-' ? $project['contract_sign_date'] : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="start_minutes_date" class="form-label">تاريخ محضر البدء</label>
                    <input type="date" class="form-control" id="start_minutes_date" name="start_minutes_date"
                        value="<?php echo $project['start_minutes_date'] != '-' ? $project['start_minutes_date'] : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="work_completion_date" class="form-label">تاريخ انهاء الاعمال</label>
                    <input type="date" class="form-control" id="work_completion_date" name="work_completion_date"
                        value="<?php echo $project['work_completion_date'] != '-' ? $project['work_completion_date'] : ''; ?>">
                </div>
            </div>
            </div>

            <div class="form-section">
            <!-- معلومات إضافية -->
            <div class="row">
                

                <div class="mb-3">
                    <label for="owning_department" class="form-label">الإدارة المالكة</label>
                    <select class="form-select" id="owning_department" name="owning_department" required>
                        <option value="">اختر الإدارة المالكة</option>
                        <option value="خدمات تقنية" <?php echo $project['owning_department'] == 'خدمات تقنية' ? 'selected' : ''; ?>>خدمات تقنية</option>
                        <option value="التكامل" <?php echo $project['owning_department'] == 'التكامل' ? 'selected' : ''; ?>>التكامل</option>
                        <option value="البنية التحتية" <?php echo $project['owning_department'] == 'البنية التحتية' ? 'selected' : ''; ?>>البنية التحتية</option>
                        <option value="التطبيقات" <?php echo $project['owning_department'] == 'التطبيقات' ? 'selected' : ''; ?>>التطبيقات</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="mb-3">
                    <label for="payment_method" class="form-label">طريقة الدفع</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="">اختر طريقة الدفع</option>
                        <option value="سنوي" <?php echo $project['payment_method'] == 'سنوي' ? 'selected' : ''; ?>>سنوي</option>
                        <option value="نصف سنوي" <?php echo $project['payment_method'] == 'نصف سنوي' ? 'selected' : ''; ?>>نصف سنوي</option>
                        <option value="ربع سنوي" <?php echo $project['payment_method'] == 'ربع سنوي' ? 'selected' : ''; ?>>ربع سنوي</option>
                        <option value="شهري" <?php echo $project['payment_method'] == 'شهري' ? 'selected' : ''; ?>>شهري</option>
                        <option value="80% - 20%" <?php echo $project['payment_method'] == '80% - 20%' ? 'selected' : ''; ?>>80% - 20%</option>
                        <option value="34% - 33% - 33%" <?php echo $project['payment_method'] == '34% - 33% - 33%' ? 'selected' : ''; ?>>34% - 33% - 33%</option>
                        <option value="80%-10%-10%" <?php echo $project['payment_method'] == '80%-10%-10%' ? 'selected' : ''; ?>>80%-10%-10%</option>
                    </select>
                </div>

                
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">الملاحظات</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($project['notes'] ?? ''); ?></textarea>
            </div>
            </div>

            <div class="form-section">
            <!-- السنوات المرتبطة -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">السنوات المرتبطة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $relatedYears = $project['related_years'] ?? [];
                        foreach ($budgetYears as $year):
                        ?>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input related-year" type="checkbox" 
                                        name="related_years[]" value="<?php echo $year; ?>" 
                                        id="year_<?php echo $year; ?>" 
                                        <?php echo in_array($year, $relatedYears) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="year_<?php echo $year; ?>">
                                        <?php echo $year; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            </div>
            <!-- جداول الدفعات -->
            <div id="paymentsContainer">
                <?php foreach ($relatedYears as $year): ?>
                    <div class="card mb-3" data-year="<?php echo $year; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">دفعات سنة <?php echo $year; ?></h5>
                            <div class="payment-summary">
                                <strong>مجموع المبالغ: </strong>
                                <span class="year-total" data-year="<?php echo $year; ?>">0</span> ريال
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>تاريخ الدفعة</th>
                                            <th>مبلغ الدفعة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="payments-table-body" data-year="<?php echo $year; ?>">
                                        <?php
                                        if (isset($project['payments'][$year])) {
                                            foreach ($project['payments'][$year] as $index => $payment):
                                        ?>
                                            <tr>
                                                <td>
                                                    <input type="date" 
                                                        name="payments[<?php echo $year; ?>][<?php echo $index; ?>][date]"
                                                        class="form-control" 
                                                        value="<?php echo $payment['date']; ?>" 
                                                        required>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        name="payments[<?php echo $year; ?>][<?php echo $index; ?>][amount]"
                                                        class="form-control payment-amount" 
                                                        value="<?php echo $payment['amount']; ?>" 
                                                        oninput="updateYearTotal('<?php echo $year; ?>')"
                                                        required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-payment">
                                                        حذف
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                            endforeach;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="actions-group">
                            <button type="button" class="btn btn-secondary add-payment" data-year="<?php echo $year; ?>">
                                إضافة دفعة
                            </button>
                            <button type="button" class="btn btn-danger remove-all-payments" data-year="<?php echo $year; ?>">
            إزالة جميع الدفعات
        </button>
                        </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="btn-group-actions">
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                <a href="view_projects.php" class="btn btn-secondary">عودة</a>
            </div>
        </form>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editProjectForm');
    const connectionStatus = document.getElementById('connection_status');
    const relatedYears = document.querySelectorAll('.related-year');
    const paymentsContainer = document.getElementById('paymentsContainer');
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmAction = document.getElementById('confirmAction');

    // تحديث الحقول المطلوبة بناءً على حالة الارتباط
    connectionStatus.addEventListener('change', updateRequiredFields);

    relatedYears.forEach(checkbox => {
    checkbox.addEventListener('change', function(e) {
        e.preventDefault(); // منع السلوك الافتراضي
        
        const year = this.value;
        const existingCard = document.querySelector(`.card[data-year="${year}"]`);
        
        if (!this.checked && existingCard) {
            // التحقق من وجود دفعات بقيم
            const paymentInputs = existingCard.querySelectorAll('input[type="number"]');
            let hasPayments = false;
            
            paymentInputs.forEach(input => {
                if (parseFloat(input.value) > 0) {
                    hasPayments = true;
                }
            });

            if (hasPayments) {
                confirmMessage.textContent = `لا يمكن إزالة سنة ${year} لأنها تحتوي على دفعات موزعة. يجب حذف الدفعات أولاً.`;
                confirmAction.style.display = 'none';
                modal.show();
                this.checked = true;
            } else {
                confirmMessage.textContent = `هل أنت متأكد من إزالة سنة ${year}؟`;
                confirmAction.style.display = 'block';
                confirmAction.onclick = () => {
                    existingCard.remove();
                    modal.hide();
                };
                modal.show();
            }
        } else if (this.checked && !existingCard) {
            confirmMessage.textContent = `هل تريد إضافة سنة ${year}؟`;
            confirmAction.style.display = 'block';
            confirmAction.onclick = () => {
                const newCard = createNewCard(year);
                paymentsContainer.appendChild(newCard);
                setupCardEventListeners(newCard, year);
                modal.hide();
            };
            modal.show();
        }
    });
});


// دالة إنشاء كارد جديد
function createNewCard(year) {
    const newCard = document.createElement('div');
    newCard.className = 'card mb-3';
    newCard.setAttribute('data-year', year);

    newCard.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">دفعات سنة ${year}</h5>
            <div class="payment-summary">
                <strong>مجموع المبالغ: </strong>
                <span class="year-total" data-year="${year}">0</span> ريال
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>تاريخ الدفعة</th>
                            <th>مبلغ الدفعة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="payments-table-body" data-year="${year}">
                        <tr>
                            <td>
                                <input type="date" name="payments[${year}][0][date]" class="form-control" required>
                            </td>
                            <td>
                                <input type="number" name="payments[${year}][0][amount]" 
                                    class="form-control payment-amount" 
                                    oninput="updateYearTotal('${year}')" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-payment">حذف</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-secondary add-payment" data-year="${year}">
                    إضافة دفعة
                </button>
                <button type="button" class="btn btn-danger remove-all-payments" data-year="${year}">
                    إزالة جميع الدفعات
                </button>
            </div>
        </div>
    `;

    return newCard;
}

    // دالة إضافة كارد جديد
    function addNewCard(year) {
        const newCard = document.createElement('div');
        newCard.className = 'card mb-3';
        newCard.setAttribute('data-year', year);

        newCard.innerHTML = `
            <div class="card-header">
                <h5>دفعات سنة ${year}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>تاريخ الدفعة</th>
                            <th>مبلغ الدفعة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="payments-table-body" data-year="${year}">
                        <tr>
                            <td>
                                <input type="date" name="payments[${year}][0][date]" class="form-control" required>
                            </td>
                            <td>
                                <input type="number" name="payments[${year}][0][amount]" 
                                    class="form-control payment-amount" 
                                    oninput="updateYearTotal('${year}')" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-payment">حذف</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary add-payment" data-year="${year}">إضافة دفعة</button>
            </div>
        `;

        // إعداد مستمعات الأحداث للكارد الجديد
        setupCardEventListeners(newCard, year);
        paymentsContainer.appendChild(newCard);
    }

    // إعداد مستمعات الأحداث للكارد
    function setupCardEventListeners(card, year) {
        const addButton = card.querySelector('.add-payment');
        const removeAllButton = card.querySelector('.remove-all-payments');
        const tbody = card.querySelector('.payments-table-body');


       
        // إضافة دفعة جديدة
        addButton.addEventListener('click', function() {
            const rowCount = tbody.children.length;
            const newRow = createNewPaymentRow(year, rowCount);
            tbody.appendChild(newRow);
            updateYearTotal(year);
        });
        

         // إزالة جميع الدفعات
    removeAllButton.addEventListener('click', function() {
        const paymentInputs = tbody.querySelectorAll('input[type="number"]');
        let hasPayments = false;
        
        paymentInputs.forEach(input => {
            if (parseFloat(input.value) > 0) {
                hasPayments = true;
            }
        });

        if (hasPayments) {
            confirmMessage.textContent = `هل أنت متأكد من إزالة جميع الدفعات لسنة ${year}؟`;
            confirmAction.style.display = 'block';
            confirmAction.onclick = () => {
                while (tbody.children.length > 1) {
                    tbody.removeChild(tbody.lastChild);
                }
                const firstRowInputs = tbody.firstElementChild.querySelectorAll('input');
                firstRowInputs.forEach(input => {
                    if (input.type === 'date') {
                        input.value = '';
                    } else if (input.type === 'number') {
                        input.value = '0';
                    }
                });
                updateYearTotal(year);
                modal.hide();
            };
            modal.show();
        } else {
            confirmMessage.textContent = 'لا توجد دفعات لإزالتها.';
            confirmAction.style.display = 'none';
            modal.show();
        }
    });



        // حذف دفعة واحدة
    tbody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-payment')) {
            e.preventDefault();
            const row = e.target.closest('tr');
            
            if (tbody.children.length > 1) {
                confirmMessage.textContent = 'هل أنت متأكد من حذف هذه الدفعة؟';
                confirmAction.style.display = 'block';
                confirmAction.onclick = () => {
                    row.remove();
                    reindexPaymentRows(tbody, year);
                    updateYearTotal(year);
                    modal.hide();
                };
                modal.show();
            } else {
                confirmMessage.textContent = 'لا يمكن حذف آخر دفعة في الجدول';
                confirmAction.style.display = 'none';
                modal.show();
            }
        }
    });
}
    // إنشاء صف دفعة جديد
    function createNewPaymentRow(year, index) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <input type="date" name="payments[${year}][${index}][date]" class="form-control" required>
            </td>
            <td>
                <input type="number" name="payments[${year}][${index}][amount]" 
                    class="form-control payment-amount" 
                    oninput="updateYearTotal('${year}')" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-payment">حذف</button>
            </td>
        `;
        return tr;
    }

    // إعادة ترقيم صفوف الدفعات
    function reindexPaymentRows(tbody, year) {
        Array.from(tbody.children).forEach((row, index) => {
            const dateInput = row.querySelector('input[type="date"]');
            const amountInput = row.querySelector('input[type="number"]');
            
            dateInput.name = `payments[${year}][${index}][date]`;
            amountInput.name = `payments[${year}][${index}][amount]`;
        });
    }

    // تحديث إجمالي السنة
    window.updateYearTotal = function(year) {
        const tbody = document.querySelector(`.payments-table-body[data-year="${year}"]`);
        if (!tbody) return;

        const inputs = tbody.querySelectorAll('.payment-amount');
        let total = 0;

        inputs.forEach(input => {
            total += Number(input.value) || 0;
        });

        const totalSpan = document.querySelector(`.year-total[data-year="${year}"]`);
        if (totalSpan) {
            totalSpan.textContent = total.toLocaleString('en-SA');
        }
    };

    // تحديث الحقول المطلوبة
    function updateRequiredFields() {
        const status = connectionStatus.value;
        const fields = form.elements;

        const allFields = [
            'po_number', 'start_date', 'end_date', 'contract_sign_date', 
            'start_minutes_date', 'project_nature', 'vendor', 'work_completion_date',
            'contract_name', 'tender_type', 'owning_department'
        ];

        // إعادة تعيين جميع الحقول
        allFields.forEach(fieldName => {
            const field = fields[fieldName];
            if (field) field.required = false;
        });

        // تعيين الحقول المطلوبة حسب الحالة
        if (status === 'التزام') {
            [
                'po_number', 'start_date', 'end_date', 'contract_sign_date',
                'start_minutes_date', 'project_nature', 'vendor', 'work_completion_date'
            ].forEach(fieldName => {
                const field = fields[fieldName];
                if (field) field.required = true;
            });
        }

        // الحقول المطلوبة دائماً
        ['contract_name', 'tender_type', 'owning_department'].forEach(fieldName => {
            const field = fields[fieldName];
            if (field) field.required = true;
        });
    }

    // تأكيد حفظ التغييرات
    form.onsubmit = function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            confirmMessage.textContent = 'هل أنت متأكد من حفظ التغييرات على هذا المشروع؟';
            confirmAction.style.display = 'block';
            confirmAction.onclick = () => {
                form.submit();
            };
            modal.show();
        } else {
            this.reportValidity();
        }
        return false;
    };

    // إعداد مستمعات الأحداث للكروت الموجودة
    document.querySelectorAll('.card[data-year]').forEach(card => {
        setupCardEventListeners(card, card.getAttribute('data-year'));
    });

    // تحديث إجمالي كل السنوات الموجودة
    document.querySelectorAll('.payments-table-body').forEach(tbody => {
        updateYearTotal(tbody.getAttribute('data-year'));
    });

    // التحديث الأولي للحقول المطلوبة
    updateRequiredFields();
});
</script>
