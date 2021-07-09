<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_claim_detail_type_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_CLAIM_DETAIL_TYPE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_list($employeeID)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID => $employeeID
        ));

        return $data;
    }

    public function get_limit_per_type($employeeID, $type)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID => $employeeID,
            TYPE => $type
        ), true);

        if ($data)
            return $data[LIMIT];
        else
            return 0;
    }
}