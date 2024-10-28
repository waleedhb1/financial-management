<?php
// add_projects.php
require_once '../db/config.php';
require_once '../items/functions.php';
require_once '../login/session_check.php';
require_once 'project_functions.php';

// التحقق من الصلاحيات
require_once '../admin/check_permissions.php';
checkPermission('employee');

$budget = getBudget();
$budgetYears = [];
foreach ($budget['years'] as $year) {
    $budgetYears[] = $year['year'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $projectData = [
            'po_number' => $_POST['po_number'] ?? '-',
            'contract_name' => $_POST['contract_name'],
            'tender_type' => $_POST['tender_type'] ?? '-',
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
            'notes' => $_POST['notes'],
            'work_completion_date' => $_POST['work_completion_date'] ?? '-',
            'payments' => $_POST['payments'] ?? []

        ];

        // التحقق من صحة البيانات حسب حالة الارتباط
        validateProjectData($projectData);

        addProject($projectData);
        $_SESSION['success_message'] = "تمت إضافة المشروع بنجاح.";
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
    <title>إضافة مشروع جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hidden {
            display: none;
        }

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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
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
        <h1 class="mb-4">إضافة مشروع جديد</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="addProjectForm" method="POST">
            <!-- حالة الارتباط -->
            <div class="mb-3">
                <label for="connection_status" class="form-label">حالة الارتباط</label>
                <select class="form-select" id="connection_status" name="connection_status" required>
                    <option value="">اختر حالة الارتباط</option>
                    <option value="التزام">التزام</option>
                    <option value="تخطيط">تخطيط</option>
                    <option value="حجز">حجز</option>
                </select>
            </div>

            <!-- الحقول الأساسية -->
            <div id="basicFields">
                <div class="mb-3">
                    <label for="po_number" class="form-label">رقم أمر الشراء (PO)</label>
                    <input type="number" class="form-control" id="po_number" name="po_number">
                </div>

                <div class="mb-3">
                    <label for="contract_name" class="form-label">اسم العقد</label>
                    <input type="text" class="form-control" id="contract_name" name="contract_name" required>
                </div>

                <div class="mb-3">
                    <label for="tender_type" class="form-label">نوع الطرح</label>
                    <select class="form-select" id="tender_type" name="tender_type">
                        <option value="">اختر نوع الطرح</option>
                        <option value="منافسة عامة">منافسة عامة</option>
                        <option value="شراء مباشر">شراء مباشر</option>
                        <option value="سوق الكتروني">سوق الكتروني</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="project_nature" class="form-label">طبيعة المشروع</label>
                    <select class="form-select" id="project_nature" name="project_nature">
                        <option value="">اختر طبيعة المشروع</option>
                        <option value="توريد">توريد</option>
                        <option value="تشغيل">تشغيل</option>
                        <option value="-">-</option>
                    </select>
                </div>
            </div>

            <div class="mb-3" id="startDateField">
                <label for="start_date" class="form-label">تاريخ البداية</label>
                <input type="date" class="form-control" id="start_date" name="start_date">
            </div>

            <!-- التواريخ -->
            <div id="otherDateFields" class="hidden">
                <div class="mb-3">
                    <label for="end_date" class="form-label">تاريخ النهاية</label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                </div>

                <div class="mb-3">
                    <label for="contract_sign_date" class="form-label">تاريخ توقيع العقد</label>
                    <input type="date" class="form-control" id="contract_sign_date" name="contract_sign_date">
                </div>

                <div class="mb-3">
                    <label for="start_minutes_date" class="form-label">تاريخ محضر البدء</label>
                    <input type="date" class="form-control" id="start_minutes_date" name="start_minutes_date">
                </div>

                <div class="mb-3">
                    <label for="work_completion_date" class="form-label">تاريخ انهاء الاعمال</label>
                    <input type="date" class="form-control" id="work_completion_date" name="work_completion_date">
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div id="additionalFields">
                <div class="mb-3">
                    <label for="vendor" class="form-label">المورد</label>
                    <input type="text" class="form-control" id="vendor" name="vendor">
                </div>

                <div class="mb-3">
                    <label for="owning_department" class="form-label">الإدارة المالكة</label>
                    <select class="form-select" id="owning_department" name="owning_department" required>
                        <option value="">اختر الإدارة المالكة</option>
                        <option value="خدمات تقنية">خدمات تقنية</option>
                        <option value="التكامل">التكامل</option>
                        <option value="البنية التحتية">البنية التحتية</option>
                        <option value="التطبيقات">التطبيقات</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="payment_method" class="form-label">طريقة الدفع</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="">اختر طريقة الدفع</option>
                        <option value="سنوي">سنوي</option>
                        <option value="نصف سنوي">نصف سنوي</option>
                        <option value="ربع سنوي">ربع سنوي</option>
                        <option value="شهري">شهري</option>
                        <option value="80% - 20%">80% - 20%</option>
                        <option value="34% - 33% - 33%">34% - 33% - 33%</option>
                        <option value="80%-10%-10%">80%-10%-10%</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">الملاحظات</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

            </div>

            <!-- السنوات المرتبطة -->
            <div class="form-section">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">السنوات المرتبطة</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($budgetYears as $year): ?>
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input related-year" type="checkbox" name="related_years[]"
                                            value="<?php echo $year; ?>" id="year_<?php echo $year; ?>">
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

            <!-- قسم جداول الدفعات -->
            <div id="paymentsContainer">
                <!-- سيتم إضافة جداول الدفعات هنا ديناميكياً -->
            </div>

            <button type="submit" class="btn btn-primary">إضافة المشروع</button>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('addProjectForm');
                    const connectionStatus = document.getElementById('connection_status');
                    const relatedYears = document.querySelectorAll('.related-year');
                    const paymentsContainer = document.getElementById('paymentsContainer');
                    const startDateField = document.getElementById('startDateField');
                    const otherDateFields = document.getElementById('otherDateFields');

                    // تحديث الحقول المطلوبة بناءً على حالة الارتباط
                    connectionStatus.addEventListener('change', updateRequiredFields);



                    function updateRequiredFields() {
                        const status = connectionStatus.value;
                        const fields = form.elements;

                        // إعادة تعيين جميع الحقول إلى غير مطلوبة
                        for (let i = 0; i < fields.length; i++) {
                            fields[i].required = false;
                        }

                        // إظهار حقل تاريخ البداية دائمًا
                        startDateField.classList.remove('hidden');

                        // إخفاء التواريخ الأخرى بشكل افتراضي
                        otherDateFields.classList.add('hidden');

                        if (status === 'التزام') {
                            otherDateFields.classList.remove('hidden');
                            fields['start_date'].required = true;
                            fields['work_completion_date'].required = true;
                            fields['po_number'].required = true;
                            fields['end_date'].required = true;
                            fields['contract_sign_date'].required = true;
                            fields['start_minutes_date'].required = true;
                            fields['project_nature'].required = true;
                            fields['vendor'].required = true;
                            fields['tender_type'].required = true;
                        } else if (status === 'تخطيط' || status === 'حجز') {
                            fields['tender_type'].required = true;
                        }

                        // الحقول المطلوبة دائماً
                        fields['contract_name'].required = true;
                        fields['owning_department'].required = true;
                    }
                };
            );

            </script>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('addProjectForm');
                    const relatedYears = document.querySelectorAll('.related-year');
                    const paymentsContainer = document.getElementById('paymentsContainer');
                    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    const confirmMessage = document.getElementById('confirmMessage');
                    const confirmAction = document.getElementById('confirmAction');
                    const connectionStatus = document.getElementById('connection_status');
                    const startDateField = document.getElementById('startDateField');
                    const otherDateFields = document.getElementById('otherDateFields');

                    // تحديث الحقول المطلوبة بناءً على حالة الارتباط
                    connectionStatus.addEventListener('change', updateRequiredFields);



                    function updateRequiredFields() {
                        const status = connectionStatus.value;
                        const fields = form.elements;

                        // إعادة تعيين جميع الحقول إلى غير مطلوبة
                        for (let i = 0; i < fields.length; i++) {
                            fields[i].required = false;
                        }

                        // إظهار حقل تاريخ البداية دائمًا
                        startDateField.classList.remove('hidden');

                        // إخفاء التواريخ الأخرى بشكل افتراضي
                        otherDateFields.classList.add('hidden');

                        if (status === 'التزام') {
                            otherDateFields.classList.remove('hidden');
                            fields['start_date'].required = true;
                            fields['work_completion_date'].required = true;
                            fields['po_number'].required = true;
                            fields['end_date'].required = true;
                            fields['contract_sign_date'].required = true;
                            fields['start_minutes_date'].required = true;
                            fields['project_nature'].required = true;
                            fields['vendor'].required = true;
                            fields['tender_type'].required = true;
                        } else if (status === 'تخطيط' || status === 'حجز') {
                            fields['tender_type'].required = true;
                        }

                        // الحقول المطلوبة دائماً
                        fields['contract_name'].required = true;
                        fields['owning_department'].required = true;
                    }

                    // مراقبة تغييرات اختيار السنوات
                    relatedYears.forEach(checkbox => {
                        checkbox.addEventListener('change', function (e) {
                            e.preventDefault();

                            const year = this.value;
                            const existingCard = document.querySelector(`.card[data-year="${year}"]`);

                            if (!this.checked && existingCard) {
                                confirmMessage.textContent = `هل أنت متأكد من إزالة سنة ${year}؟`;
                                confirmAction.style.display = 'block';
                                confirmAction.onclick = () => {
                                    existingCard.remove();
                                    modal.hide();
                                };
                                modal.show();
                            } else if (this.checked && !existingCard) {
                                confirmMessage.textContent = `هل تريد إضافة سنة ${year}؟`;
                                confirmAction.style.display = 'block';
                                confirmAction.onclick = () => {
                                    const newCard = createPaymentCard(year);
                                    paymentsContainer.appendChild(newCard);
                                    setupCardEventListeners(newCard, year);
                                    modal.hide();
                                };
                                modal.show();
                            }
                        });
                    });

                    // إنشاء كارد جديد للدفعات
                    function createPaymentCard(year) {
                        const card = document.createElement('div');
                        card.className = 'card mb-3';
                        card.setAttribute('data-year', year);

                        card.innerHTML = `
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
                <div class="actions-group">
                    <button type="button" class="btn btn-secondary add-payment" data-year="${year}">إضافة دفعة</button>
                    <button type="button" class="btn btn-danger remove-all-payments" data-year="${year}">إزالة جميع الدفعات</button>
                </div>
            </div>
        `;

                        return card;
                    }

                    // إعداد مستمعات الأحداث للكارد
                    function setupCardEventListeners(card, year) {
                        const addButton = card.querySelector('.add-payment');
                        const removeAllButton = card.querySelector('.remove-all-payments');
                        const tbody = card.querySelector('.payments-table-body');

                        // إضافة دفعة جديدة
                        addButton.addEventListener('click', function () {
                            const rowCount = tbody.children.length;
                            const newRow = createNewPaymentRow(year, rowCount);
                            tbody.appendChild(newRow);
                            updateYearTotal(year);
                        });

                        // إزالة جميع الدفعات
                        removeAllButton.addEventListener('click', function () {
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
                        tbody.addEventListener('click', function (e) {
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

                    // تحديث إجمالي السنة - تعريف عام
                    window.updateYearTotal = function (year) {
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
                });
            </script>

</body>

</html>