<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_leave_entitlement_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_LEAVE_ENTITLEMENT';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_employee_id($id)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID => $id,
        ), false);

        return $data;
    }

    public function get_by_id($id)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), false);

        return $data;
    }

    public function get_balance($employeeID, $leaveType)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID => $employeeID,
            LEAVE_TYPE => $leaveType,
        ), true);

        if ($data)
            return $data[CURRENT_LEAVE_BALANCE];
        else
            return 0;
    }

    public function get_carry($employeeID, $leaveType)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID => $employeeID,
            LEAVE_TYPE => $leaveType,
        ), true);

        if ($data)
            return $data[CARRY_FORWARD_LEAVE];
        else
            return 0;
    }
}