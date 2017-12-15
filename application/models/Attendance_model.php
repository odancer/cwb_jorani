<?php
class Attendance_model extends CI_Model{
  public function __construct(){
  }

      public function getAttendanceRecord($login_id) {
        $db2= $this->load->database('external', TRUE);
        $db2->select('*');
        $db2->from('PLTC_records');
        $db2->where('userid', $login_id);
        $records = $db2->get()->result_array();
        return $records;
    }
}
?>