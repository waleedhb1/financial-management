<!-- sidebar.php -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الإدارة المالية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #f8f9fa;
            --sidebar-hover: #e9ecef;
            --sidebar-active: #dee2e6;
            --primary-color: #0d6efd;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: #f5f6fa;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            top: 0;
            right: 0;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid var(--sidebar-active);
            flex-shrink: 0;
        }

        .sidebar-content {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 1rem;
        }

        /* تنسيق شريط التمرير المخصص */
        .sidebar-content::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: var(--sidebar-bg);
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: #cbd3da;
            border-radius: 10px;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: #a8b2bc;
        }

        .sidebar-logo {
            width: 180px;
            height: auto;
            margin-bottom: 1rem;
        }

        .nav-pills .nav-link {
            color: #495057;
            border-radius: 0;
            padding: 0.8rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link:hover {
            background-color: var(--sidebar-hover);
        }

        .nav-pills .nav-link.active {
            background-color: var(--sidebar-active);
            color: var(--primary-color);
            font-weight: 600;
        }

        .nav-pills .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .submenu {
            padding-right: 2rem;
            background: rgba(0, 0, 0, 0.02);
        }

        .submenu .nav-link {
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
        }

        .nav-link.special {
            background: #e3f2fd;
            margin: 0.5rem 1rem;
            border-radius: 4px;
            color: var(--primary-color);
        }

        .nav-link.logout {
            background: #fff5f5;
            margin: 0.5rem 1rem;
            border-radius: 4px;
            color: var(--danger-color);
        }

        .content {
            margin-right: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            margin-top: 45px;
        }

        /* إضافة أنماط جديدة للمؤشر */
        .nav-link[data-bs-toggle="collapse"] {
            cursor: pointer;
        }

        /* تحسين حالة الأيقونة */
        .collapse-icon {
            transition: transform 0.3s ease;
        }

        .nav-link[aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }

        .nav-link[data-bs-toggle="collapse"] {
            position: relative;
            transition: all 0.3s ease;
        }

        /* تنسيق النافذة النشطة */
        .nav-link[data-bs-toggle="collapse"].active-parent {
            background-color: rgba(149, 191, 252, 0.1);
            /* لون خفيف من Primary Color */
            color: var(--primary-color);
            font-weight: 500;
        }

        .nav-link[data-bs-toggle="collapse"].active-parent::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--primary-color);
            border-radius: 0 4px 4px 0;
        }

        .nav-link[data-bs-toggle="collapse"]:hover {
            background-color: var(--sidebar-hover);
        }

        .nav-link[data-bs-toggle="collapse"].active-parent:hover {
            background-color: rgba(13, 110, 253, 0.15);
        }
    </style>
</head>

<body>
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="شعار نظام الإدارة المالية" class="sidebar-logo">
        </div>

        <div class="sidebar-content">
            <ul class="nav nav-pills flex-column">

                <!-- الموازنة -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        data-bs-target="#budgetMenu" role="button"
                        aria-expanded="<?= in_array($current_page, ['index.php', 'view.php', 'edit_budget.php', 'log_view.php']) ? 'true' : 'false' ?>">
                        <span><i class="bi bi-calculator"></i> الموازنة</span>
                        <i class="bi bi-chevron-down collapse-icon"></i>
                    </a>
                    <div class="collapse <?= in_array($current_page, ['index.php', 'view.php', 'edit_budget.php', 'log_view.php']) ? 'show' : '' ?>"
                        id="budgetMenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'view.php' ? 'active' : '' ?>"
                                    href="../items/view.php">
                                    <i class="bi bi-eye"></i> عرض الموازنة
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>"
                                    href="../items/index.php">
                                    <i class="bi bi-plus-circle"></i> إدخال الموازنة
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'edit_budget.php' ? 'active' : '' ?>"
                                    href="../items/edit_budget.php">
                                    <i class="bi bi-pencil"></i> تعديل الموازنة
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'log_view.php' ? 'active' : '' ?>"
                                    href="../items/log_view.php">
                                    <i class="bi bi-journal-text"></i> سجل الأحداث
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- المشاريع -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        data-bs-target="#projectsMenu" role="button"
                        aria-expanded="<?= in_array($current_page, ['view_projects.php']) ? 'true' : 'false' ?>">
                        <span><i class="bi bi-briefcase"></i> المشاريع</span>
                        <i class="bi bi-chevron-down collapse-icon"></i>
                    </a>
                    <div class="collapse <?= in_array($current_page, ['view_projects.php']) ? 'show' : '' ?>"
                        id="projectsMenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'view_projects.php' ? 'active' : '' ?>"
                                    href="../projects/view_projects.php">
                                    <i class="bi bi-eye"></i> عرض المشاريع
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'renewal_request.php' ? 'active' : '' ?>"
                                    href="../projects/renewal_request.php">
                                    <i class="bi bi-arrow-repeat"></i> طلب تجديد
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'change_request.php' ? 'active' : '' ?>"
                                    href="../projects/change_request.php">
                                    <i class="bi bi-pencil-square"></i> طلب تغيير
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>


                <!-- الصرف -->
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        data-bs-target="#disbursementMenu" role="button"
                        aria-expanded="<?= in_array($current_page, ['view_disbursement.php']) ? 'true' : 'false' ?>">
                        <span><i class="bi bi-cash-coin"></i> الصرف</span>
                        <i class="bi bi-chevron-down collapse-icon"></i>
                    </a>
                    <div class="collapse <?= in_array($current_page, ['view_disbursement.php']) ? 'show' : '' ?>"
                        id="disbursementMenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link <?= $current_page == 'view_disbursement.php' ? 'active' : '' ?>"
                                    href="../disbursement/view_disbursement.php">
                                    <i class="bi bi-wallet2"></i> خطة الصرف
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- سجلات الدخول -->
                <li class="nav-item">
                    <a class="nav-link special <?= $current_page == 'login_logs.php' ? 'active' : '' ?>"
                        href="../login/login_logs.php">
                        <i class="bi bi-clock-history"></i> سجلات الدخول
                    </a>
                </li>

                <!-- تسجيل الخروج -->
                <li class="nav-item mt-auto">
                    <a class="nav-link logout" href="../login/logout.php">
                        <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navLinks = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');

            // دالة لتحديث حالة النافذة النشطة
            function updateActiveParent() {
                const currentPage = '<?= $current_page ?>';

                navLinks.forEach(link => {
                    const targetId = link.getAttribute('data-bs-target');
                    const submenuLinks = document.querySelector(targetId)?.querySelectorAll('.nav-link');

                    // إزالة الصنف النشط من جميع النوافذ
                    link.classList.remove('active-parent');

                    // التحقق من وجود رابط نشط في القائمة الفرعية
                    if (submenuLinks) {
                        submenuLinks.forEach(submenuLink => {
                            const href = submenuLink.getAttribute('href');
                            if (href && href.includes(currentPage)) {
                                link.classList.add('active-parent');
                            }
                        });
                    }
                });
            }

            // تهيئة جميع عناصر Collapse
            navLinks.forEach(link => {
                const targetId = link.getAttribute('data-bs-target');
                const collapseElement = document.querySelector(targetId);

                // إنشاء كائن Collapse لكل عنصر
                new bootstrap.Collapse(collapseElement, {
                    toggle: false
                });

                // إضافة معالج الحدث للنقر
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // إغلاق جميع القوائم الأخرى
                    navLinks.forEach(otherLink => {
                        if (otherLink !== link) {
                            const otherId = otherLink.getAttribute('data-bs-target');
                            const otherCollapse = document.querySelector(otherId);
                            const bsCollapse = bootstrap.Collapse.getInstance(otherCollapse);
                            if (bsCollapse && otherCollapse.classList.contains('show')) {
                                bsCollapse.hide();
                            }
                        }
                    });

                    // تبديل حالة القائمة الحالية
                    const collapse = bootstrap.Collapse.getInstance(collapseElement);
                    collapse.toggle();
                });
            });

            // تعيين الحالة الأولية للقائمة النشطة
            const currentPage = '<?= $current_page ?>';
            navLinks.forEach(link => {
                const targetId = link.getAttribute('data-bs-target');
                if (link.getAttribute('aria-expanded') === 'true' && targetId) {
                    const collapseElement = document.querySelector(targetId);
                    const collapse = bootstrap.Collapse.getInstance(collapseElement);
                    if (collapse) {
                        collapse.show();
                    }
                }
            });

            // معالجة دوران الأيقونات وتحديث النافذة النشطة
            document.querySelectorAll('.collapse').forEach(collapseElement => {
                collapseElement.addEventListener('show.bs.collapse', function () {
                    const trigger = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    if (trigger) {
                        trigger.querySelector('.collapse-icon').style.transform = 'rotate(180deg)';
                    }
                });

                collapseElement.addEventListener('hide.bs.collapse', function () {
                    const trigger = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    if (trigger) {
                        trigger.querySelector('.collapse-icon').style.transform = 'rotate(0)';
                    }
                });
            });

            // تحديث حالة النافذة النشطة عند تحميل الصفحة
            updateActiveParent();
        });
    </script>
</body>

</html>