<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hrms_message_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_MESSAGE';

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
        $employee_id = $id;
        $user_group = $this->user_m->get_users_by_group_name_HR($id, 'EMPLOYEE_ID');
        
        $_table_name = 'MAN_MOB_HRMS_MESSAGE';
        $result = array();
        foreach ($user_group as $key => $user) {
            $id = $user['EMPLOYEE_ID'];
            $sql = "select * from $_table_name where SENDER = '$id'";
            $query = $this->db->query($sql);
            $result = array_merge($result, $query->result_array());
        }
        foreach ($result as $key => $message) {
            $id = $message['id'];
            $group_id = $message['RECEIVER_USERS'];
            if(strpos($group_id, $employee_id) === false){
                $this->db->where('id', $id);
                $this->db->update($_table_name, array('RECEIVER_USERS'=>$group_id?$group_id.','.$employee_id:$employee_id));
            }
        }
        function DATE_CREATED($a, $b)
        {
            $t1 = strtotime($a['DATE_CREATED']);
            $t2 = strtotime($b['DATE_CREATED']);
            return $t1 - $t2;
        }
        usort($result, 'DATE_CREATED');

        $len = count($result);
        if($len >= 50){
            $result = array_slice($result, $len - 50, $len);
        }
        return $result;
    }

    public function get_list($id, $userID)
    {
        if ($userID == '') {
            return $this->get_by_employee_id($id);
        }

        return $this->get_by_employee_id($userID);
    // $_table_name = 'MAN_MOB_HRMS_MESSAGE';
    // $sql = "select * from $_table_name where SENDER = '$id' and RECEIVER = '$userID'";
    // $query = $this->db->query($sql);
    // return $query->result_array();
    }

    public function get_attendance() {
        $_table_name = 'MAN_MOB_HRMS_MESSAGE';
        $this->db->select('MAN_MOB_HRMS_MESSAGE.*');
        $this->db->where('SEND_STATUS', 0);
        $query = $this->db->get($_table_name);
        if($query->num_rows()>0)
            $data = $query->result_array();
        else 
            $data = array();
        $attendance_message = array();
        foreach($data as $key => $message) {
            $sender_id = $message['SENDER'];
            $user_group = $this->user_m->get_users_by_group_name_HR($sender_id, 'EMPLOYEE_ID');
            $temp = array();
            foreach ($user_group as $key => $user) {
                if(in_array($user['EMPLOYEE_ID'], $temp)){
                    continue;
                }
                array_push($temp, $user['EMPLOYEE_ID']);
                $id = $user['EMPLOYEE_ID'];
                $group_id = $message['RECEIVER_USERS'];
                
                if(strpos($group_id, $id) === false){
                    $message['RECEIVER'] = $id;
                    array_push($attendance_message, $message);
                }
            }
        }
        return $attendance_message;
    }
}