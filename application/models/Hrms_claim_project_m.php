<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_claim_project_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_CLAIM_PROJECT';

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