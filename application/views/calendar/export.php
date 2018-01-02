<?php
/**
 * This view builds an Excel5 file containing the list of leave requests declared by the connected employee.
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.2.0
 */
$this->lang->load('calendar', $this->language);
$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('attendance_title'), 0, 28, "..."));  //Maximum 31 characters allowed in sheet title.
$sheet->setCellValue('A1', lang('attendance_index_user_id'));
$sheet->setCellValue('B1', lang('attendance_index_user_fullname'));
$sheet->setCellValue('C1', lang('attendance_index_date'));
$sheet->setCellValue('D1', lang('attendance_index_in'));
$sheet->setCellValue('E1', lang('attendance_index_out'));
$sheet->getStyle('A1:E1')->getFont()->setBold(true);
$sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$records = $this->attendance_model->getAttendanceRecord($login_id,$date);
$date_convr = substr($date,0,2)."-".substr($date,2); 
$daycount = date("t",strtotime($date_convr));
$dataArray = array();
$tcdate = array();
    foreach($records as $record) {
        $tcdate[]=$record['tc_date'];
    }
    for ($i=1;$i<=$daycount;$i++) {
        $i = str_pad($i,2,'0',STR_PAD_LEFT);
        $date2 = $date.$i;
        $key = array_search($date2,$tcdate);
        if($key) {
            $first = $records[$key]['first'];
            $final = $records[$key]['final'];
            if(empty($records[$key]['first'])) $first = '無紀錄';
            if(empty($records[$key]['final'])) $final = '無紀錄';
            array_push($dataArray,array('userid'=>$login_id,'tc_date'=>$date2,'first'=>$first,'final'=>$final));
            }else {
            array_push($dataArray,array('userid'=>$login_id,'tc_date'=>$date2,'first'=>'無紀錄','final'=>'無紀錄'));
            }
        }

$line = 2;
foreach ($dataArray as $item) {
    $sheet->setCellValue('A' . $line, $item['userid']);
    $sheet->setCellValue('B' . $line, $item['userid']);
    $sheet->setCellValue('C' . $line, $item['tc_date']);
    $sheet->setCellValue('D' . $line, $item['first']);
    $sheet->setCellValue('E' . $line, $item['final']);
    $line++;
}

//Autofit
foreach(range('A', 'E') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

exportSpreadsheet($this, 'calendar');
