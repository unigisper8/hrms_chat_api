<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Requisition_listing_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_REQUISITION_LISTING';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_list($group, $referencesInvited, $count = 1, $offset = 0, $start, $end)
    {
        $_table_name = 'MAN_MOB_REQUISITION_LISTING';
        $_table_name1 = 'MAN_MOB_REQUISITION';
        $where = "";

        $arr = array();
        foreach ($group as $item) {
            $arr[] = "a.GROUP_NAME = '$item'";
        }
        // foreach ($referencesInvited as $item) {
        //     $arr[] = "a.REFERENCE_NO = '$item'";
        // }
        if (sizeof($arr) > 0) {
            if ($where != '') $where .= ' and ';
            $where .= '(' . implode(' || ', $arr) . ')';
        }

        if ($start != null && $end != null) {
            if ($where != '') $where .= ' and ';
            $where .= " REQUEST >= '$start' and REQUEST <= '$end' ";
        }

        if ($where != '') $where = "where $where and SRF < '1900-00-00' and SRF_LOGIN = ''";
		else  $where = "where SRF < '1900-00-00' and SRF_LOGIN = ''";

        $sql = "select a.*, b.A_CUSTOMER as CUSTOMER_NAME from $_table_name as a inner join $_table_name1 as b on a.REFERENCE_NO = b.REFERENCE_NO $where ORDER BY REQUEST DESC limit $offset, $count;";
        $query = $this->db->query($sql);
        return $query->result_array();

        // $where = array();
        // if ($start != null)
        //     $where['REQUEST >= '] = $start;
        // if ($end != null)
        //     $where['REQUEST <= '] = $end;
        // $data = $this->get_limit($count, $offset, $where, REQUEST, true);

        // return $data;
    }

    public function get_by_reference_id($id)
    {
        $data = $this->get_by(array(
            REFERENCE_NO => $id,
        ), TRUE);
        return $data;
    }

    public function get_by_id($id)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), TRUE);
        return $data;
    }
}