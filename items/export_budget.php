<?php
// export_budget.php
require '../vendor/autoload.php';
require_once '../db/config.php';
require_once 'functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

$budget = getBudget();

if (!$budget) {
    die('لا توجد موازنة لتصديرها');
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// إعداد العنوان
$sheet->setCellValue('A1', 'الموازنة');
$sheet->mergeCells('A1:D1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// إعداد رؤوس الأعمدة
$headers = ['السنة', 'المبلغ المعتمد', 'المنصرف', 'المتبقي'];
foreach (range('A', 'D') as $col) {
    $sheet->setCellValue($col . '2', $headers[ord($col) - 65]);
}

// تنسيق رؤوس الأعمدة
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
];
$sheet->getStyle('A2:D2')->applyFromArray($headerStyle);

// إضافة البيانات
$row = 3;
$totalApproved = 0;
$totalSpent = 0;
$totalRemaining = 0;

foreach ($budget['years'] as $year) {
    $spent = 0; // استبدل هذا بالقيمة الفعلية من قاعدة البيانات
    $remaining = $year['amount'] - $spent;
    
    $sheet->setCellValue('A' . $row, $year['year']);
    $sheet->setCellValue('B' . $row, $year['amount']);
    $sheet->setCellValue('C' . $row, $spent);
    $sheet->setCellValue('D' . $row, $remaining);
    
    $totalApproved += $year['amount'];
    $totalSpent += $spent;
    $totalRemaining += $remaining;
    
    $row++;
}

// إضافة الإجمالي
$totalRow = $row;
$sheet->setCellValue('A' . $totalRow, 'المجموع');
$sheet->setCellValue('B' . $totalRow, $totalApproved);
$sheet->setCellValue('C' . $totalRow, $totalSpent);
$sheet->setCellValue('D' . $totalRow, $totalRemaining);

// تنسيق صف المجموع
$totalStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$sheet->getStyle('A' . $totalRow . ':D' . $totalRow)->applyFromArray($totalStyle);

// تنسيق البيانات
$dataStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
];
$sheet->getStyle('A3:D' . ($totalRow - 1))->applyFromArray($dataStyle);

// تنسيق الأرقام
$sheet->getStyle('B3:D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

// تعيين عرض الأعمدة
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// تطبيق التناوب في لون الخلفية للصفوف
$evenRowColor = 'F2F2F2';
$oddRowColor = 'FFFFFF';
for ($i = 3; $i < $totalRow; $i++) {
    $fillColor = ($i % 2 == 0) ? $evenRowColor : $oddRowColor;
    $sheet->getStyle('A' . $i . ':D' . $i)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB($fillColor);
}

// إنشاء الملف وتنزيله
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="budget_export.xlsx"');
$writer->save('php://output');
exit;
