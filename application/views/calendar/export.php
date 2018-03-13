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
$sheet->setCellValue('F1', lang('attendance_notify_abnormal'));
$sheet->getStyle('A1:E1')->getFont()->setBold(true);
$sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$fullname = $this->attendance_model->getAttendanceName($this->user_id);
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
        if(is_int($key) == TRUE) {
                  $first = $records[$key]['first'];
                  $final = $records[$key]['final'];
                  if(empty($records[$key]['first'])) $first = '無紀錄';
                  if(empty($records[$key]['final'])) $final = '無紀錄';
                  if(empty($records[$key]['first']) ||empty($records[$key]['final'])) {
                        $abnormal = '刷卡異常';
                    }else {
                        $first_str = substr($first,0,2).':'.substr($first,2,2);
                        $final_str = substr($final,0,2).':'.substr($final,2,2);
                        $first_time=strtotime($first_str); 
                        $final_time=strtotime($final_str);
                        $work_time=$final_time-$first_time; 
                        $work_hours=$work_time/3600;
                        if($work_hours >= 9) {
                            $abnormal = '';
                        }else {
                             $abnormal = '時數異常';
                        }
                    }
                  array_push($dataArray,array('userid'=>$login_id,'tc_date'=>$date2,'first'=>$first,'final'=>$final, 'abnormal'=>$abnormal));
                }else {
                  $first = '無紀錄'; $final = '無紀錄'; $abnormal='刷卡異常';
                  array_push($dataArray,array('userid'=>$login_id,'tc_date'=>$date2,'first'=>$first,'final'=>$final, 'abnormal'=>$abnormal));
                }
        }

$line = 2;
foreach ($dataArray as $item) {
    $sheet->setCellValue('A' . $line, $item['userid']);
    $sheet->setCellValue('B' . $line, $fullname);
    $sheet->setCellValue('C' . $line, $item['tc_date']);
    $sheet->setCellValue('D' . $line, $item['first']);
    $sheet->setCellValue('E' . $line, $item['final']);
    $sheet->setCellValue('E' . $line, $item['abnormal']);
    $line++;
}

//Autofit
foreach(range('A', 'E') as $colD) {
    $sheet->getColumnDimension($colD)->setAutoSize(TRUE);
}

exportSpreadsheet($this, 'calendar');
