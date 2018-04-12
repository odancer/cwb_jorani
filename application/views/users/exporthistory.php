<?php
/**
 * This view builds a Spreadsheet file containing the list of users.
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.2.0
 */

$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('users_export_title4'), 0, 100, "..."));  //Maximum 31 characters allowed in sheet title.
$sheet->setCellValue('A1', lang('users_edit_field_firstname'));
$sheet->setCellValue('B1', lang('users_edit_field_lastname'));
$sheet->setCellValue('C1', lang('users_edit_field_position'));
$sheet->setCellValue('D1', lang('users_edit_field_jobcategory'));
$sheet->setCellValue('E1', lang('users_edit_field_rating'));
$sheet->setCellValue('F1', lang('users_edit_field_grade'));
$sheet->setCellValue('G1', lang('users_edit_field_salary'));
$sheet->setCellValue('H1', lang('users_edit_field_salarypoint'));
$sheet->setCellValue('I1', lang('users_edit_field_change_date'));
$sheet->setCellValue('J1', lang('users_edit_field_change_type_name'));
$sheet->getStyle('A1:J1')->getFont()->setBold(true);
$sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//$users = $this->users_model->getUsers();
$line = 2;
foreach ($users_history as $record) {
	error_log( print_r($record, TRUE) );
    $sheet->setCellValue('A' . $line, $users_item['firstname']);
    $sheet->setCellValue('B' . $line, $users_item['lastname']);
    $sheet->setCellValue('C' . $line, $record['position_name']);
    $sheet->setCellValue('D' . $line, $record['jobcategory']);
    $sheet->setCellValue('E' . $line, $record['rating']);
    $sheet->setCellValue('F' . $line, $record['grade']);
    $sheet->setCellValue('G' . $line, $record['salary']);
    $sheet->setCellValue('H' . $line, $record['salarypoint']);
    $sheet->setCellValue('I' . $line, $record['change_date']);
    $sheet->setCellValue('J' . $line, $record['change_type_name']);
    $line++;
}

//Autofit
foreach(range('A', 'J') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

exportSpreadsheet($this, 'users');
