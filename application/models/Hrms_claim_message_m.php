<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_claim_message_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_CLAIM_MESSAGE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_employee_id($id)
    {
        $_table_name = 'MAN_MOB_HRMS_CLAIM_MESSAGE';
        $sql = "select * from $_table_name where (RECEIVER = '' and SENDER = '$id') or RECEIVER = '$id'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function receive_message($employee_id, $claimID, $status){
        $_table_name = 'MAN_MOB_HRMS_CLAIM_MESSAGE';
        $sql = "update $_table_name set receive_status = $status, send_status = $status where Claim_ID = '$claimID' and RECEIVER = '$employee_id'";
        $this->db->query($sql);
    }

    public function send_message($id, $status){
        $_table_name = 'MAN_MOB_HRMS_CLAIM_MESSAGE';
        $sql = "update $_table_name set send_status = $status where id = '$id'";
        $this->db->query($sql);
    }

    public function get_message_status($sender, $receiver, $claimID) {
        $_table_name = 'MAN_MOB_HRMS_CLAIM_MESSAGE';
        $sql = "select count(*) AS count from $_table_name where Claim_ID = '$claimID' and SENDER = '$sender' and receive_status = 0";
        $count = $this->db->query($sql);
        return $count->result_array();
    }

    public function get_by_claim_id($employee_id, $claimID)
    {
        $_table_name = 'MAN_MOB_HRMS_CLAIM_MESSAGE';
        // $sql = "select * from $_table_name where ((RECEIVER = '' and SENDER = '$employee_id') or RECEIVER = '$employee_id') and Claim_ID = '$claimID'";
        $sql = "select * from $_table_name where Claim_ID = '$claimID'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_list($employee_id, $userID, $claimID)
    {
        return $this->get_by_claim_id($employee_id, $claimID);

		// if ($userID == '') {
		// 	return $this->get_by_claim_id($employee_id, $claimID);
		// }
		//
		// return $this->get_by_claim_id($userID, $claimID);
    }

    public function get_all_not_receive_users(){
        $_table_name = 'MAN_MOB_HRMS_CLAIM_MESSAGE';
        // $sql = "select * from $_table_name where ((RECEIVER = '' and SENDER = '$employee_id') or RECEIVER = '$employee_id') and Leave_ID = '$leaveID'";
        $sql = "select * from $_table_name where receive_status = 0 group by Claim_ID";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}