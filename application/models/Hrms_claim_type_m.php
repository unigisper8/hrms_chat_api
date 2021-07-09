<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_claim_type_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_CLAIM_TYPE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_list()
    {
        $data = $this->get();

        return $data;
    }

    public function get_limit_per_type($type)
    {
        $data = $this->get_by(array(
            TYPE => $type
        ), true);

        if ($data)
            return $data[LIMIT];
        else
            return 0;
    }
}