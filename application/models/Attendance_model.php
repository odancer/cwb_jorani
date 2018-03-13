<?php
class Attendance_model extends CI_Model{
  public function __construct(){
  }

      public function getAttendanceRecord($login_id,$date) {
        $db2= $this->load->database('external', TRUE);
        $db2->select('*');
        $db2->from('PLTC_records');
        $db2->where('userid', $login_id);
        $db2->like('tc_date', $date,'after');
        $records = $db2->get()->result_array();
        return $records;
    }

    public function getAttendanceName($id) {
        $record = $this->getAttendanceUsers($id);
        if (count($record) > 0) {
            return $record['firstname'] . ' ' . $record['lastname'];
        }
    }

    public function getAttendanceUsers($id = 0) {
        $this->db->select('users.*');
        if ($id === 0) {
            $query = $this->db->get('users');
            return $query->result_array();
        }
        $query = $this->db->get_where('users', array('users.id' => $id));
        return $query->row_array();
    }

    public function getLeaveDuring($date,$id) {
        $this->db->select('duration');
        $this->db->from('leaves');
        $this->db->where();
        $query = $this->db->get_where('users', array('users.id' => $id));
        return $query->row_array();
    }
    
}
?>