<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_letter_message_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_LETTER_MESSAGE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_employee_id($id)
    {
        $_table_name = 'MAN_MOB_HRMS_LETTER_MESSAGE';
        $sql = "select * from $_table_name where (RECEIVER = '' and SENDER = '$id') or RECEIVER = '$id'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_by_letter_id($employee_id, $letterID)
    {
        $_table_name = 'MAN_MOB_HRMS_LETTER_MESSAGE';
        // $sql = "select * from $_table_name where ((RECEIVER = '' and SENDER = '$employee_id') or RECEIVER = '$employee_id') and Letter_ID = '$letterID'";
        $sql = "select * from $_table_name where Letter_ID = '$letterID'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_list($employee_id, $userID, $letterID)
    {
        return $this->get_by_letter_id($employee_id, $letterID);

		// if ($userID == '') {
		// 	return $this->get_by_letter_id($employee_id, $letterID);
		// }
		//
		// return $this->get_by_letter_id($userID, $letterID);
    }
}