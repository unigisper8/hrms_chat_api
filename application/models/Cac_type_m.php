<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cac_type_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_CAC_TYPE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_list($group, $query = '')
    {
        $_table_name = 'MAN_MOB_CAC_TYPE';
        $where = "where CAC_TYPE like '%$query%'";
        $sql = "select * from $_table_name $where";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $result[] = $item['CAC_TYPE'];
        }
        return $result;
    }
}