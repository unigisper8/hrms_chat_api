<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_leave_application_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_employee_id($id)
    {
        // $data = $this->get_by(array(
        //     EMPLOYEE_ID => $id,
        // ), false);
        //
        // return $data;

        $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
        $sql = "select * from $_table_name where EMPLOYEE_ID = '$id' order by `Date_Start` desc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_by_employee_ids($users)
    {
        if (isset($users) && sizeof($users) > 0) {
            $str_users = implode("','", $users);
            $in = '';
            if ($str_users != '') {
                $in = "EMPLOYEE_ID in ('$str_users') ";
            }
            $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
            $sql = "select * from $_table_name where $in order by `Date_Start` desc";
            $query = $this->db->query($sql);
            return $query->result_array();
        } else {
            return array();
        }
    }
    
    public function set_status_by_employee_ids($users)
    {
        if (isset($users) && sizeof($users) > 0) {
            $str_users = implode("','", $users);
            $in = '';
            if ($str_users != '') {
                $in = "EMPLOYEE_ID in ('$str_users') ";
            }
            $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
            $sql = "update $_table_name set SEND_STATUS = 1 where $in order by `Date_Start` desc";
            $query = $this->db->query($sql);
        }
    }

    public function get_by_status()
    {
        $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
        $sql = "select * from $_table_name where SEND_STATUS = 0 order by `Date_Start` desc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_by_receive_status(){
        $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
        $sql = "select * from $_table_name where RECEIVE_STATUS = 0 and SEND_STATUS = 1 order by `Date_Start` desc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_by_id($id)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), true);

        return $data;
    }

    public function check_duplicated($employeeID, $startDate, $endDate)
    {
//        $data = $this->get_by(array(
//            EMPLOYEE_ID => $employeeID,
//            DATE_START => $startDate,
//            DATE_END => $endDate,
//        ), false);
//
//        if (sizeof($data) > 0)
//            return true;
//        else
//            return false;

        $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
        $sql = "select * from $_table_name where Employee_ID = '$employeeID' and ((Date_Start >= '$startDate' and Date_End <= '$endDate') or (Date_Start <= '$startDate' and Date_End >= '$startDate') or (Date_Start <= '$endDate' and Date_End >= '$endDate')) and Leave_Status in ('Pending', 'Approved')";
        $query = $this->db->query($sql);
        $data = $query->result_array();

        if (sizeof($data) > 0)
            return true;
        else
            return false;
    }

    public function get_by_leave_type($id, $type)
    {
        $data = $this->get_by_sort(array(
            EMPLOYEE_ID => $id,
            LEAVE_TYPE => $type
        ), false);

        return $data;
    }

    public function delete_pending($id)
    {
        $data = $this->get_by(array(
            ID => $id,
            LEAVE_STATUS => 'Pending'
        ));

        if (!isset($data) || sizeof($data) == 0) return false;

        $this->delete_by(array(
            ID => $id,
            LEAVE_STATUS => 'Pending'
        ), true);

        return true;
    }

    public function update_status($id, $status, $date)
    {
        $result = $this->update(array(
            LEAVE_STATUS => $status,
            DATE_APPROVAL => $date,
            LEAVE_PENDING_STATUS => 1,
            RECEIVE_STATUS => 0
        ), $id);

        return $result;
    }

    public function leave_received($id) {
        $this->update(array(
            RECEIVE_STATUS => 1
        ), $id);
    }
    // multi level manager
    public function update_pending_level($id, $employee)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), true);

        $pending_status = intval($data[LEAVE_PENDING_STATUS]);

        if ($pending_status <= 1 && $employee[LEAVE_MANAGER_2] != '') {
            $pending_status = 2;
            $result = $this->update(array(
                LEAVE_PENDING_STATUS => $pending_status
            ), $id);
        } else if ($pending_status == 2 && $employee[LEAVE_MANAGER_3] != '') {
            $pending_status = 3;
            $result = $this->update(array(
                LEAVE_PENDING_STATUS => $pending_status
            ), $id);
        } else {
            $pending_status = 4;
            $result = $this->update(array(
                LEAVE_PENDING_STATUS => $pending_status
            ), $id);
        }
        return $result;
    }

    // New CR
    public function get_pending_list($managerID)
    {
        $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
        $sql = "SELECT count(Leave_Status) AS 'leave_pending_count' FROM $_table_name WHERE FIND_IN_SET('$managerID',Manager_ID) AND Leave_Status LIKE 'Pending';";
        $query = $this->db->query($sql);
        $ret = $query->row();
        return $ret->leave_pending_count;
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
            $_table_name = 'MAN_MOB_HRMS_LEAVE_APPLICATION';
            $sql = "select * from $_table_name where $in AND Leave_Status = 'Pending' order by `Date_Start` desc";
            $query = $this->db->query($sql);
            return $query->result_array();
        } else {
            return array();
        }
    }

    // New CR
    public function update_leave_manager_level($id, $managerStatus)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), true);

        $manager_level = intval($data[LEAVE_APPROVE_MANAGER_LEVEL]);

        //If rejected
        if ($manager_level == 1 && $managerStatus = 3) {
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_STATUS_1 => $managerStatus,
            ), $id);
        } else if ($manager_level == 2 && $managerStatus = 3) {
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_STATUS_2 => $managerStatus,
            ), $id);
        } else if ($manager_level == 3 && $managerStatus = 3) {
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_STATUS_3 => $managerStatus,
            ), $id);
        } else if ($manager_level == 4 && $managerStatus = 3) {
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_STATUS_4 => $managerStatus,
            ), $id);
        } else if ($manager_level == 5 && $managerStatus = 3) {
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_STATUS_5 => $managerStatus,
            ), $id);
        }

        //If Approved
        if ($manager_level == 1 && $managerStatus = 2) {
            $manager_level = 2;
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_LEVEL => $manager_level,
                LEAVE_APPROVE_MANAGER_STATUS_1 => $managerStatus,
                LEAVE_APPROVE_MANAGER_STATUS_2 => 1
            ), $id);
        } else if ($manager_level == 2 && $managerStatus = 2) {
            $manager_level = 3;
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_LEVEL => $manager_level,
                LEAVE_APPROVE_MANAGER_STATUS_2 => $managerStatus,
                LEAVE_APPROVE_MANAGER_STATUS_3 => 1
            ), $id);
        } else if ($manager_level == 3 && $managerStatus = 2) {
            $manager_level = 4;
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_LEVEL => $manager_level,
                LEAVE_APPROVE_MANAGER_STATUS_3 => $managerStatus,
                LEAVE_APPROVE_MANAGER_STATUS_4 => 1
            ), $id);
        } else if ($manager_level == 4 && $managerStatus = 2) {
            $manager_level = 5;
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_LEVEL => $manager_level,
                LEAVE_APPROVE_MANAGER_STATUS_4 => $managerStatus,
                LEAVE_APPROVE_MANAGER_STATUS_5 => 1
            ), $id);
        } else if ($manager_level == 5 && $managerStatus = 2) {
            $result = $this->update(array(
                LEAVE_APPROVE_MANAGER_STATUS_5 => $managerStatus
            ), $id);
        }

        return $result;
    }

}