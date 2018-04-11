<?php
/**
 * This view builds a Spreadsheet file containing the list of users.
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.2.0
 */
$sheet = $this->excel->setActiveSheetIndex(0);
$this->load->model('organization_model');
$this->load->model('positions_model');
$grp=$user_info['organization'];
$sheet->setTitle(mb_strimwidth(lang('users_export_title2'), 0, 100, "...")); 
$sheet->mergeCells('A1:H1');
$sheet->mergeCells('I1:O1');
$sheet->setCellValue('A1', lang('users_export_title3'));
$sheet->setCellValue('I1', lang('users_export_date'));
$sheet->setCellValue('A2', lang('users_export_thead_stationedorg'));
$sheet->setCellValue('B2', lang('users_export_thead_stationedunit'));
$sheet->setCellValue('C2', lang('users_export_thead_bidname'));
$sheet->setCellValue('D2', lang('users_export_thead_org'));
$sheet->setCellValue('E2', lang('users_export_thead_name'));
$sheet->setCellValue('F2', lang('users_export_thead_jobcategory'));
$sheet->setCellValue('G2', lang('users_export_thead_position'));
$sheet->setCellValue('H2', lang('users_export_thead_salarypoint'));
$sheet->setCellValue('I2', lang('users_export_thead_salary'));
$sheet->setCellValue('J2', lang('users_export_thead_hired'));
$sheet->setCellValue('K2', lang('users_export_thead_raise'));
$sheet->setCellValue('L2', lang('users_export_thead_rating'));
$sheet->setCellValue('M2', lang('users_export_thead_grade'));
$sheet->setCellValue('N2', lang('users_export_thead_rpapprov'));
$sheet->setCellValue('O2', lang('users_export_thead_rpapprov_nextyera'));
$sheet->getStyle('A2:O2')->getFont()->setBold(true);
$sheet->getStyle('A1:I1')->getFont()->setBold(true);
$sheet->getStyle('A2:O2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$users = $this->users_model->getUsers2(2,$grp);
$line = 3;
foreach ($users as $user) {
    $sheet->setCellValue('A' . $line, $user['stationedorg']);
    $sheet->setCellValue('B' . $line, $user['stationedunit']);
    $sheet->setCellValue('C' . $line, $user['bidname']);
    $sheet->setCellValue('D' . $line, $this->organization_model->getName($user['organization']));
    $sheet->setCellValue('E' . $line, $user['lastname'].$user['firstname']);
    $sheet->setCellValue('F' . $line, $user['jobcategory']);
	$sheet->setCellValue('G' . $line, $this->positions_model->getName($user['position']));
	$sheet->setCellValue('H' . $line, $user['salarypoint']);
	$sheet->setCellValue('I' . $line, $user['salary']);
	$sheet->setCellValue('J' . $line, $user['datehired']);
	$sheet->setCellValue('K' . $line, $user['datehired']);
	$sheet->setCellValue('L' . $line, $user['rating']);
	$sheet->setCellValue('M' . $line, $user['grade']);
	$sheet->setCellValue('N' . $line, "");
    $sheet->setCellValue('O' . $line, "");
    $line++;
}
$last = count($users)+3;
$last2=$last+1;
$sheet->mergeCells('A'.$last.':C'.$last2);
$sheet->setCellValue('A'.$last, lang('users_export_thead_sign'));

//Autofit
foreach(range('A', 'O') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

exportSpreadsheet($this, 'users');



