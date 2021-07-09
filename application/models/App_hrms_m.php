<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_hrms_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_APP_HRMS';

    public function __construct()
    {
        parent::__construct();
    }

    public function getInfo()
    {
		$_table_name = 'MAN_MOB_APP_HRMS';
        $sql = "select * from $_table_name where 1";
        $query = $this->db->query($sql);
        $data = $query->result_array();
		if (sizeof($data) > 0) return $data[0];
        return NULL;
    }
}