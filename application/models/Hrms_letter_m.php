<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_letter_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_LETTER';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_id($id)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), true);

        return $data;
    }

    public function get_list($employeeID, $userID, $start, $end)
    {
        $_table_name = 'MAN_MOB_HRMS_LETTER';
        // $sql = "select * from $_table_name where EMPLOYEE_ID != '$employeeID'";
        $sql = "select * from $_table_name where 1";
        if ($userID != '')
            $sql .= " and EMPLOYEE_ID = '$userID'";
        if ($start != '')
            $sql .= " and Date_Issued >= '$start'";
        if ($end != '')
            $sql .= " and Date_Issued <= '$end'";
        $sql .= " order by `Date_Issued` desc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function check_duplicated($from, $to, $date_issued, $template)
    {
        $data = $this->get_by(array(
            FROM            => $from,
            TO              => $to,
            DATE_ISSUED     => $date_issued,
            TEMPLATE        => $template,
        ), false);

        if (sizeof($data) > 0)
            return true;
        else
            return false;
    }

    public function update_status($id, $status)
    {
        $result = $this->update(array(
            STATUS => $status
        ), $id);

        return $result;
    }

    // New CR
    public function get_pending_list($managerID)
    {
        $_table_name = 'MAN_MOB_HRMS_LETTER';
        $sql = "SELECT COUNT(STATUS) AS `letter_pending_count` FROM $_table_name WHERE FIND_IN_SET('$managerID',Manager_ID) AND STATUS LIKE 'Pending';";
        $query = $this->db->query($sql);
        $ret = $query->row();
        return $ret->letter_pending_count;
    }

    // New CR
    public function get_by_employee_ids_pending($users)
    {
        if (isset($users) && sizeof($users) > 0) {
            $str_users = implode("','", $users);
            $in = '';
            if ($str_users != '') {
                $in = "EMPLOYEE_ID in ('$str_users') ";
            }
            $_table_name = 'MAN_MOB_HRMS_LETTER';
            $sql = "select * from $_table_name where $in AND STATUS = 'Pending'";
            $query = $this->db->query($sql);
            return $query->result_array();
        } else {
            return array();
        }
    }

    // New CR
    public function update_top_manager_status($id, $status)
    {
        $result = $this->update(array(
            TOP_MANAGEMENT_APPROVE_STATUS => $status
        ), $id);

        return $result;
    }
}