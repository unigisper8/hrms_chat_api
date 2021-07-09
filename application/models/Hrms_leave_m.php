<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_leave_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_LEAVE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_employee_id($id)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID => $id,
        ), false);

        $result = array();
        foreach ($data as $row) {
            $result[] = array(
                LEAVE_TYPE => $row[LEAVE_TYPE],
                LEAVE_BALANCE => $row[LEAVE_BALANCE]
            );
        }
        return $result;
    }

    public function get_by_id($id)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), false);

        $result = array();
        foreach ($data as $row) {
            $result[] = array(
                LEAVE_TYPE => $row[LEAVE_TYPE],
                BALANCE => $row[BALANCE]
            );
        }
        return $result;
    }
}