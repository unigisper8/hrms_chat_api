<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_attendance_late_message_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_ATTENDANCE_LATE_MESSAGE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_employee_id($id)
    {
        // $data = $this->get_by(array(
        //     EMPLOYEE_ID => $id,
        // ), false);
        // return $data;

        $_table_name = 'MAN_MOB_HRMS_ATTENDANCE_LATE_MESSAGE';
		// $sql = "select * from $_table_name where (RECEIVER = '' and SENDER = '$id') or RECEIVER = '$id'";
		$sql = "select * from $_table_name where SENDER = '$id' or RECEIVER = '$id'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_list_by_attendance_id($attendanceID)
    {
        $_table_name = 'MAN_MOB_HRMS_ATTENDANCE_LATE_MESSAGE';
        $sql = "select * from $_table_name where Attendance_ID = '$attendanceID'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_list($employee_id, $userID)
    {
        $_table_name = 'MAN_MOB_HRMS_ATTENDANCE_LATE_MESSAGE';
		$sql = "select * from $_table_name where (SENDER = '$userID' and RECEIVER = '$employee_id') or (SENDER = '$employee_id' and RECEIVER = '$userID')";
        $query = $this->db->query($sql);
        return $query->result_array(); 
    }
}