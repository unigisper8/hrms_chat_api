<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_timeoff_message_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_TIMEOFF_MESSAGE';

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

        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_MESSAGE';
		$sql = "select * from $_table_name where (RECEIVER = '' and SENDER = '$id') or RECEIVER = '$id'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function receive_message($employee_id, $timeoffID, $status){
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_MESSAGE';
        $sql = "update $_table_name set receive_status = $status, send_status = $status where Timeoff_ID = '$timeoffID' and RECEIVER = '$employee_id'";
        $this->db->query($sql);
    }

    public function send_message($id, $status){
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_MESSAGE';
        $sql = "update $_table_name set send_status = $status where id = '$id'";
        $this->db->query($sql);
    }

    public function get_message_status($sender, $receiver, $timeoffID) {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_MESSAGE';
        $sql = "select count(*) AS count from $_table_name where Timeoff_ID = '$timeoffID' and SENDER = '$sender' and receive_status = 0";
        $count = $this->db->query($sql);
        return $count->result_array();
    }

    public function get_by_timeoff_id($employee_id, $timeoffID)
    {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_MESSAGE';
        // $sql = "select * from $_table_name where ((RECEIVER = '' and SENDER = '$employee_id') or RECEIVER = '$employee_id') and Timeoff_ID = '$timeoffID'";
        $sql = "select * from $_table_name where Timeoff_ID = '$timeoffID'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_list($employee_id, $userID, $timeoffID)
    {
        return $this->get_by_timeoff_id($employee_id, $timeoffID);

        // if ($userID == '') {
        //     return $this->get_by_timeoff_id($employee_id, $timeoffID);
        // }
        //
        // return $this->get_by_timeoff_id($userID, $timeoffID);
    }

    public function get_all_not_receive_users(){
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_MESSAGE';
        // $sql = "select * from $_table_name where ((RECEIVER = '' and SENDER = '$employee_id') or RECEIVER = '$employee_id') and Leave_ID = '$leaveID'";
        $sql = "select * from $_table_name where receive_status = 0 group by Timeoff_ID";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}