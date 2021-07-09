<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_timeoff_type_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TYPE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_list()
    {
        $data = $this->get();

        return $data;
    }
}