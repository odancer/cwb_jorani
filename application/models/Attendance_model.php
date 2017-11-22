<?php
class Attendance_model extends CI_Model{
  public function __construct(){
  }

      public function getAttendanceRecord($login_id) {
        $pstest_db = $this->load->database('external', TRUE);
        //$this->$pstest_db->close();
        //$db = $this->load->database('default', TRUE);
          /**
        $this->db2->select('records.*');
        $this->db2->from('records');
        $this->db2->where('records.userid', $login_id);
        $this->dbw->order_by('records.tc_date', 'desc');
        return $this->db->get()->result_array(); */
        return;
    }
}
?>