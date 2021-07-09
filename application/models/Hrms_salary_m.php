<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_salary_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_SALARY';

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
}