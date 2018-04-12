<?php
/**
 * This view builds a Spreadsheet file containing the list of overtime requests created by an employee (from HR menu).
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.2.0
 */

$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('hr_summary_title'), 0, 28, "..."));  //Maximum 31 characters allowed in sheet title.
$sheet->setCellValue('A1', "姓名:".$employee_name);
$sheet->setCellValue('A2', lang('hr_summary_thead_type'));
$sheet->setCellValue('B2', lang('hr_summary_thead_available'));
$sheet->setCellValue('C2', lang('hr_summary_thead_taken'));
$sheet->setCellValue('D2', lang('hr_summary_thead_entitled'));
$sheet->setCellValue('E2', lang('hr_summary_thead_description'));
$sheet->getStyle('A2:E2')->getFont()->setBold(true);
$sheet->getStyle('A2:E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$line = 3;
foreach ($summary as $key => $value) {
   // $date = new DateTime($request['date']);
   // $startdate = $date->format(lang('global_date_format'));
    $sheet->setCellValue('A' . $line, $key);
    $sheet->setCellValue('B' . $line, ((float) $value[1])-(float) $value[0]);
    $sheet->setCellValue('C' . $line, $value[0]);
    $sheet->setCellValue('D' . $line, $value[1]);
    $sheet->setCellValue('E' . $line, $value[2]);
    $line++;
}

//Autofit
foreach(range('A', 'E') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

exportSpreadsheet($this, 'hr');
