<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_attendance_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_ATTENDANCE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_employee_id($id)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID => $id,
        ), true);
        return $data;
    }

    public function get_by_id($id)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), true);
        return $data;
    }

    public function set_receive_users($attendance_id, $employeeID) {
        $_table_name = 'MAN_MOB_HRMS_ATTENDANCE';
        $this->db->select('*');
        $this->db->where('id', $attendance_id);
        $query = $this->db->get($_table_name);
        $attendance = array();
        if($query->num_rows() > 0)
            $attendance = $query->result_array();
        else 
            return false;
        $receive_users = $attendance[0]['Receive_Users'];
        if(strpos($receive_users, $employeeID) === false) {
            $this->db->where('id', $attendance_id);
            $this->db->update($_table_name, array('Receive_Users'=>$receive_users?$receive_users.','.$employeeID:$employeeID));
        }
        return true;
    }
}