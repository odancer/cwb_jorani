<?php
class Attendance_model extends CI_Model{
  public function __construct(){
  }

      public function getExtrasOfEmployee($employee) {
        $this->db2->select('overtime.*');
        $this->db2->from('overtime');
        $this->db2->join('status', 'overtime.status = status.id');
        $this->db2->where('overtime.employee', $employee);
        $this->dbw->order_by('overtime.id', 'desc');
        return $this->db->get()->result_array();
    }
}
?>