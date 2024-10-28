<?php
// view_projects.php
require_once '../db/config.php';
require_once '../items/functions.php';
require_once '../login/session_check.php';
require_once 'project_functions.php';

// التحقق من الصلاحيات
require_once '../admin/check_permissions.php';
checkPermission('employee');

$budget = getBudget();
$budgetYears = array_column($budget['years'], 'year');

$filters = [
    'year' => $_GET['year'] ?? null,
    'connection_status' => $_GET['connection_status'] ?? null,
    'tender_type' => $_GET['tender_type'] ?? null,
    'project_nature' => $_GET['project_nature'] ?? null,
    'payment_method' => $_GET['payment_method'] ?? null,
    'owning_department' => $_GET['owning_department'] ?? null,
    'remaining_duration_sort' => $_GET['remaining_duration_sort'] ?? null // إضافة معامل الترتيب الجديد
];

$result = getProjects($filters);
$projects = $result['projects'];
$years = $result['years'];

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المشاريع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }




        .table-container {
            overflow-x: auto;
            max-width: 100%;

        }

        .table {
            min-width: 100%;
        }

        .table th,
        .table td {
            white-space: nowrap;
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
        }

        .table th {
            background-color: #ffffff !important;
            /* تأكيد على الخلفية البيضاء */
            color: #333;
            /* لون النص الداكن للتباين */
            font-weight: bold;
            vertical-align: middle;
            padding: 12px;
            /* زيادة المسافة الداخلية */
            white-space: nowrap;
        }

        .table td[data-year] {
            font-weight: bold;
        }

        .table td[data-year="2024"] {
            background-color: #e8f5e9;
        }

        .table td[data-year="2025"] {
            background-color: #fff3e0;
        }

        .table td[data-year="2026"] {
            background-color: #e1f5fe;
        }

        .table td[data-year] {
            font-weight: bold;
        }

        .table td[data-year]:not(:empty) {
            font-weight: bold;
        }

        .table thead tr {
            background-color: #ffffff;
            /* خلفية بيضاء */
            border-bottom: 2px solid #dee2e6;
            /* إضافة خط سفلي للتمييز */
            font-weight: bold;
            /* جعل النص عريض */
        }

        <?php
        // تعريف مصفوفة الألوان للسنوات
        $colors = [
            '#e8f5e9', // أخضر فاتح
            '#fff3e0', // برتقالي فاتح
            '#e1f5fe', // أزرق فاتح
            '#f3e5f5', // بنفسجي فاتح
            '#e8eaf6', // نيلي فاتح
            '#e0f2f1'  // تركواز فاتح
        ];

        // إضافة نمط لكل سنة موجودة فعلياً
        foreach ($years as $index => $year) {
            $hasValues = false;
            foreach ($projects as $project) {
                if (!empty($project["year_$year"]) && $project["year_$year"] > 0) {
                    $hasValues = true;
                    break;
                }
            }
            if ($hasValues) {
                $color = $colors[$index % count($colors)];
                echo ".table td[data-year='$year'] { background-color: $color; }\n";
            }
        }
        ?>

        .payments-icon {
            cursor: pointer;
            color: #0d6efd;
            margin-right: 8px;
            transition: color 0.3s;
        }

        .payments-icon:hover {
            color: #0a58ca;
        }

        .payments-table th {
            background-color: #f8f9fa;
        }

        .year-section {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .year-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .payment-modal .modal-content {
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
    <?php include 'confirm_modal.php'; ?>
</head>

<body>
    <?php include '../sidebar/sidebar.php'; ?>

    <div class="content">

        <div class="main-content">
            <h1 class="mb-4">عرض المشاريع</h1>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <a href="add_projects.php" class="btn btn-primary">إضافة مشروع جديد</a>
            </div>

            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="ابحث في المشاريع...">
            </div>

            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select name="year" class="form-select">
                            <option value="">جميع السنوات</option>
                            <?php foreach ($budgetYears as $year): ?>
                                <option value="<?php echo $year; ?>" <?php echo $filters['year'] == $year ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="connection_status" class="form-select">
                            <option value="">جميع حالات الارتباط</option>
                            <option value="التزام" <?php echo $filters['connection_status'] == 'التزام' ? 'selected' : ''; ?>>
                                التزام</option>
                            <option value="تخطيط" <?php echo $filters['connection_status'] == 'تخطيط' ? 'selected' : ''; ?>>
                                تخطيط</option>
                            <option value="حجز" <?php echo $filters['connection_status'] == 'حجز' ? 'selected' : ''; ?>>
                                حجز
                            </option>
                            <option value="منتهي" <?php echo $filters['connection_status'] == 'منتهي' ? 'selected' : ''; ?>>
                                منتهي</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="tender_type" class="form-select">
                            <option value="">جميع أنواع الطرح</option>
                            <option value="منافسة عامة" <?php echo $filters['tender_type'] == 'منافسة عامة' ? 'selected' : ''; ?>>منافسة عامة</option>
                            <option value="شراء مباشر" <?php echo $filters['tender_type'] == 'شراء مباشر' ? 'selected' : ''; ?>>شراء مباشر</option>
                            <option value="سوق الكتروني" <?php echo $filters['tender_type'] == 'سوق الكتروني' ? 'selected' : ''; ?>>سوق الكتروني</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="project_nature" class="form-select">
                            <option value="">جميع طبيعات المشروع</option>
                            <option value="توريد" <?php echo $filters['project_nature'] == 'توريد' ? 'selected' : ''; ?>>
                                توريد
                            </option>
                            <option value="تشغيل" <?php echo $filters['project_nature'] == 'تشغيل' ? 'selected' : ''; ?>>
                                تشغيل
                            </option>
                            <option value="-" <?php echo $filters['project_nature'] == '-' ? 'selected' : ''; ?>>-
                            </option>
                        </select><br>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_method" class="form-select">
                            <option value="">جميع طرق الدفع</option>
                            <option value="سنوي" <?php echo $filters['payment_method'] == 'سنوي' ? 'selected' : ''; ?>>
                                سنوي
                            </option>
                            <option value="نصف سنوي" <?php echo $filters['payment_method'] == 'نصف سنوي' ? 'selected' : ''; ?>>نصف سنوي</option>
                            <option value="ربع سنوي" <?php echo $filters['payment_method'] == 'ربع سنوي' ? 'selected' : ''; ?>>ربع سنوي</option>
                            <option value="شهري" <?php echo $filters['payment_method'] == 'شهري' ? 'selected' : ''; ?>>
                                شهري
                            </option>
                            <option value="80% - 20%" <?php echo $filters['payment_method'] == '80% - 20%' ? 'selected' : ''; ?>>80% - 20%</option>
                            <option value="34% - 33% - 33%" <?php echo $filters['payment_method'] == '34% - 33% - 33%' ? 'selected' : ''; ?>>34% - 33% - 33%</option>
                            <option value="80%-10%-10%" <?php echo $filters['payment_method'] == '80%-10%-10%' ? 'selected' : ''; ?>>80%-10%-10%</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="owning_department" class="form-select">
                            <option value="">جميع الإدارات المالكة</option>
                            <option value="خدمات تقنية" <?php echo $filters['owning_department'] == 'خدمات تقنية' ? 'selected' : ''; ?>>خدمات تقنية</option>
                            <option value="التكامل" <?php echo $filters['owning_department'] == 'التكامل' ? 'selected' : ''; ?>>التكامل</option>
                            <option value="البنية التحتية" <?php echo $filters['owning_department'] == 'البنية التحتية' ? 'selected' : ''; ?>>البنية التحتية</option>
                            <option value="التطبيقات" <?php echo $filters['owning_department'] == 'التطبيقات' ? 'selected' : ''; ?>>التطبيقات</option>
                        </select><br>
                    </div>

                    <div class="col-md-2">
                        <select name="remaining_duration_sort" class="form-select">
                            <option value="">ترتيب المدة المتبقية</option>
                            <option value="asc" <?php echo isset($_GET['remaining_duration_sort']) && $_GET['remaining_duration_sort'] == 'asc' ? 'selected' : ''; ?>>
                                من الأقل إلى الأعلى
                            </option>
                            <option value="desc" <?php echo isset($_GET['remaining_duration_sort']) && $_GET['remaining_duration_sort'] == 'desc' ? 'selected' : ''; ?>>
                                من الأعلى إلى الأقل
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="project_type" class="form-select">
                            <option value="">جميع أنواع المشاريع</option>
                            <option value="renewed" <?php echo $filters['project_type'] == 'renewed' ? 'selected' : ''; ?>>المشاريع المجددة</option>
                            <option value="changed" <?php echo $filters['project_type'] == 'changed' ? 'selected' : ''; ?>>المشاريع المتغيرة</option>
                            <option value="original" <?php echo $filters['project_type'] == 'original' ? 'selected' : ''; ?>>المشاريع الأصلية</option>
                        </select>
                    </div>

                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">تصفية</button>
                    <a href="view_projects.php" class="btn btn-secondary">إعادة تعيين</a>
                </div>
        </div>
        </form>

        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>رقم PO</th>
                        <th>اسم العقد</th>
                        <th>السنوات المرتبطة</th>
                        <th>حالة الارتباط</th>
                        <th>نوع الطرح</th>
                        <th>طبيعة المشروع</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>تاريخ توقيع العقد</th>
                        <th>تاريخ محضر البدء</th>
                        <th>تاريخ انهاء الاعمال</th>
                        <th>مدة المشروع</th>
                        <th>المدة المنقضية</th>
                        <th>المدة المتبقية</th>
                        <th>طريقة الدفع</th>
                        <?php
                        // التحقق من وجود قيم فعلية لكل سنة
                        foreach ($years as $year):
                            $hasValues = false;
                            foreach ($projects as $p) {
                                if (!empty($p["year_$year"]) && $p["year_$year"] > 0) {
                                    $hasValues = true;
                                    break;
                                }
                            }
                            // عرض العمود فقط إذا كانت هناك قيم فعلية
                            if ($hasValues):
                                ?>
                                <th><?php echo $year; ?></th>
                                <?php
                            endif;
                        endforeach;
                        ?>
                        <th>المورد</th>
                        <th>الإدارة المالكة</th>
                        <th>الملاحظات</th>
                        <th>القيمة الإجمالية</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['po_number']); ?></td>
                            <td><?php echo htmlspecialchars($project['contract_name']); ?></td>
                            <td><?php echo htmlspecialchars($project['related_years']); ?></td>
                            <td><?php echo htmlspecialchars($project['connection_status']); ?></td>
                            <td><?php echo htmlspecialchars($project['tender_type']); ?></td>
                            <td><?php echo htmlspecialchars($project['project_nature']); ?></td>
                            <td><?php echo htmlspecialchars($project['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($project['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($project['contract_sign_date']); ?></td>
                            <td><?php echo htmlspecialchars($project['start_minutes_date']); ?></td>
                            <td><?php echo htmlspecialchars($project['work_completion_date']); ?></td>
                            <!-- مدة المشروع -->
                            <td>
                                <?php
                                if ($project['connection_status'] === 'التزام') {
                                    echo is_numeric($project['project_duration']) ?
                                        $project['project_duration'] . ' شهر' : $project['project_duration'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <!-- المدة المنقضية -->
                            <td>
                                <?php
                                if ($project['connection_status'] === 'التزام') {
                                    echo is_numeric($project['elapsed_duration']) ?
                                        $project['elapsed_duration'] . ' شهر' : $project['elapsed_duration'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <!-- المدة المتبقية -->
                            <td>
                                <?php
                                if ($project['connection_status'] === 'التزام') {
                                    if (is_numeric($project['remaining_duration'])) {
                                        echo $project['remaining_duration'] . ' شهر';
                                        // إضافة تنبيه بصري للمدة المتبقية
                                        if ($project['remaining_duration'] <= 6) {
                                            echo ' <span class="badge bg-danger">قريب من الانتهاء</span>';
                                        } elseif ($project['remaining_duration'] <= 10) {
                                            echo ' <span class="badge bg-warning">تنبيه</span>';
                                        }
                                    } else {
                                        echo $project['remaining_duration'];
                                    }
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($project['payment_method']); ?></td>
                            <?php
                            foreach ($years as $year):
                                // نفس التحقق كما في العناوين
                                $hasValues = false;
                                foreach ($projects as $p) {
                                    if (!empty($p["year_$year"]) && $p["year_$year"] > 0) {
                                        $hasValues = true;
                                        break;
                                    }
                                }
                                if ($hasValues):
                                    $value = $project["year_$year"] ?? 0;
                                    ?>
                                    <td data-year="<?php echo $year; ?>">
                                        <?php echo number_format($value, 2); ?>
                                    </td>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                            <td><?php echo htmlspecialchars($project['vendor']); ?></td>
                            <td><?php echo htmlspecialchars($project['owning_department']); ?></td>
                            <td><?php echo htmlspecialchars($project['notes']); ?></td>
                            <td>

                                <i class="bi bi-info-circle-fill payments-icon"
                                    onclick="showPayments(<?php echo htmlspecialchars(json_encode($project)); ?>)"
                                    title="عرض تفاصيل الدفعات"></i>
                                <?php
                                switch ($project['connection_status']) {
                                    case 'تخطيط':
                                        echo number_format($project['total_estimated_value'], 2);
                                        break;
                                    case 'التزام':
                                        echo number_format($project['total_contractual_value'], 2);
                                        break;
                                    case 'حجز':
                                        echo number_format($project['total_reserved_value'], 2);
                                        break;
                                    default:
                                        echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="edit_project.php?id=<?php echo $project['id']; ?>"
                                    class="btn btn-sm btn-primary">تعديل</a>
                                <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('هل أنت متأكد من حذف هذا المشروع؟');">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const table = document.querySelector('.table');
            const rows = table.querySelectorAll('tbody tr');

            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        });

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            const confirmMessage = document.getElementById('confirmMessage');
            const confirmAction = document.getElementById('confirmAction');

            // تأكيد الحذف
            document.querySelectorAll('.btn-danger').forEach(button => {
                button.onclick = function (e) {
                    e.preventDefault();
                    const projectId = this.getAttribute('href').split('=')[1];
                    confirmMessage.textContent = 'هل أنت متأكد من حذف هذا المشروع؟ هذا الإجراء لا يمكن التراجع عنه.';
                    confirmAction.onclick = function () {
                        window.location.href = `delete_project.php?id=${projectId}`;
                    };
                    modal.show();
                    return false;
                };
            });

            // تأكيد التعديل
            document.querySelectorAll('.btn-primary').forEach(button => {
                if (button.textContent === 'تعديل') {
                    button.onclick = function (e) {
                        e.preventDefault();
                        const href = this.getAttribute('href');
                        confirmMessage.textContent = 'هل تريد الانتقال إلى صفحة تعديل هذا المشروع؟';
                        confirmAction.onclick = function () {
                            window.location.href = href;
                        };
                        modal.show();
                        return false;
                    };
                }
            });
        });


    </script>

    <script>
        function showPayments(project) {
            const modalBody = document.getElementById('paymentsModalBody');
            const modalTitle = document.getElementById('paymentsModalLabel');

            // تحديث عنوان Modal
            modalTitle.textContent = `تفاصيل الدفعات - ${project.contract_name}`;

            let htmlContent = '';
            let totalAllYears = 0;

            // ترتيب السنوات تصاعدياً
            const years = Object.keys(project.payments).sort();

            years.forEach(year => {
                const payments = project.payments[year];
                let yearTotal = 0;

                htmlContent += `
            <div class="year-section">
                <div class="year-header">
                    <h5 class="mb-0">سنة ${year}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped payments-table mb-0">
                        <thead>
                            <tr>
                                <th>تاريخ الدفعة</th>
                                <th>المبلغ</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

                payments.forEach(payment => {
                    const amount = parseFloat(payment.amount);
                    yearTotal += amount;
                    totalAllYears += amount;

                    htmlContent += `
                <tr>
                    <td>${payment.date}</td>
                    <td>${amount.toLocaleString('en-SA', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</td>
                </tr>
            `;
                });

                htmlContent += `
                            <tr class="total-row">
                                <td>مجموع السنة</td>
                                <td>${yearTotal.toLocaleString('en-SA', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
            });

            // إضافة المجموع الكلي
            htmlContent += `
        <div class="alert alert-primary mt-3">
            <strong>المجموع الكلي للمشروع: </strong>
            ${totalAllYears.toLocaleString('en-SA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}
        </div>
    `;

            modalBody.innerHTML = htmlContent;

            // عرض Modal
            new bootstrap.Modal(document.getElementById('paymentsModal')).show();
        }
    </script>


    <div class="modal fade" id="paymentsModal" tabindex="-1" aria-labelledby="paymentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content payment-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentsModalLabel">تفاصيل الدفعات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body" id="paymentsModalBody">
                    <!-- سيتم إضافة محتوى الدفعات هنا عبر JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>


</body>

</html>