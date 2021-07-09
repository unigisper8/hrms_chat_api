<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trigger_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_TRIGGER';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_reference_id($id)
    {
        $data = $this->get_by(array(
            REFERENCE_NO => $id,
        ), TRUE);
        return $data;
    }
//    public function get_list($group, $start, $end, $cacType, $custName)
//    {
//        $where = array();
//        if ($group != null)
//            $where['GROUP_NAME = '] = $group;
//        if ($start != null && $start != '')
//            $where['DATE_CREATED >= '] = $start;
//        if ($end != null && $end != '')
//            $where['DATE_CREATED <= '] = $end;
//        if ($cacType != null && $cacType != '')
//            $where['CAC_Type = '] = $cacType;
//        if ($custName != null && $custName != '')
//            $where['Cust_Name = '] = $custName;
//
//        $data = $this->get_by($where, false);
//
//        return $data;
//    }
    public function get_list($group, $referencesInvited, $start, $end, $cacType, $custName)
    {
        $_table_name = 'MAN_MOB_TRIGGER';
        $where = '';
        if ($group != null) {
            $arr = array();
            foreach ($group as $item) {
                $arr[] = "GROUP_NAME = '$item'";
            }
            // foreach ($referencesInvited as $item) {
            //     $arr[] = "REFERENCE_NO = '$item'";
            // }
            if (sizeof($arr) > 0) {
                $query = implode(' || ', $arr);
                $where = "(" . $query . ")";
            }
        }
        if ($start != null && $start != '') {
            if ($where != '') $where .= " and ";
            $where .= "Date >= '$start'";
        }
        if ($end != null && $end != '') {
            if ($where != '') $where .= " and ";
            $where .= "Date <= '$end'";
        }
        if ($cacType != null && $cacType != '') {
            if ($where != '') $where .= " and ";
            $where .= "CAC_Type = '$cacType'";
        }
        if ($custName != null && $custName != '') {
            if ($where != '') $where .= " and ";
            $where .= "Cust_Name = '$custName'";
        }

        if ($where != '')
            $where = 'where ' . $where;

        $sql = "select * from $_table_name $where";
        $query = $this->db->query($sql);
        $result = $query->result_array();

        return $result;
    }

//    public function get_cust_list($group, $query)
//    {
//        $_table_name = 'MAN_MOB_TRIGGER';
//        $where = "where GROUP_NAME = '$group' and Cust_Name like '%$query%' group by Cust_Name order by Cust_Name asc";
//        $sql = "select * from $_table_name $where";
//        $query = $this->db->query($sql);
//        $arr = $query->result_array();
//        $result = array();
//        foreach ($arr as $item) {
//            $result[] = $item['Cust_Name'];
//        }
//        return $result;
//    }

    public function get_cust_list($group, $query)
    {
        $_table_name = 'MAN_MOB_TRIGGER';
        $query_group = '';
        if ($group != null) {
            $arr = array();
            foreach ($group as $item) {
                $arr[] = "GROUP_NAME = '$item'";
            }
            if (sizeof($arr) > 0) {
                $query_group = implode(' || ', $arr);
                $query_group = "(" . $query_group . ")";
            }
        }
        $where = 'where ';
        if ($query_group != '')
            $where .= "$query_group and ";
        $where .= "Cust_Name like '%$query%' group by Cust_Name order by Cust_Name asc";
        $sql = "select * from $_table_name $where";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $result[] = $item['Cust_Name'];
        }
        return $result;
    }

    public function get_cac_type_list($group, $query)
    {
        $_table_name = 'MAN_MOB_TRIGGER';
        $query_group = '';
        if ($group != null) {
            $arr = array();
            foreach ($group as $item) {
                $arr[] = "GROUP_NAME = '$item'";
            }
            if (sizeof($arr) > 0) {
                $query_group = implode(' || ', $arr);
                $query_group = "(" . $query_group . ")";
            }
        }
        $where = 'where ';
        if ($query_group != '')
            $where .= "$query_group and ";
        $where .= "CAC_Type like '%$query%' group by CAC_Type order by CAC_Type asc";
        $sql = "select * from $_table_name $where";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $result[] = $item['CAC_Type'];
        }
        return $result;
    }
}