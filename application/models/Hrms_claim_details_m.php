<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_claim_details_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_CLAIM_DETAILS';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_list()
    {
        $data = $this->get();

        return $data;
    }

    public function get_by_claim_id($employee_id, $claim_id)
    {
//        $data = $this->get_by(array(
//            EMPLOYEE_ID     => $employee_id,
//            CLAIM_ID        => $claim_id
//        ), false);
//
//        return $data;

        $_table_name = 'MAN_MOB_HRMS_CLAIM_DETAILS';
        // $sql = "select * from $_table_name where Employee_ID = '$employee_id' and Claim_ID = '$claim_id' order by `Date` asc";
        $sql = "select * from $_table_name where Claim_ID = '$claim_id' order by `Date` asc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function check_duplicated($employeeID, $claimID, $date, $type, $description, $amount, $remark)
    {
        $data = $this->get_by(array(
            EMPLOYEE_ID     => $employeeID,
            CLAIM_ID        => $claimID,
            DATE            => $date,
            TYPE            => $type,
            AMOUNT          => $amount,
            REMARK          => $remark,
            DESCRIPTION     => $description
        ), false);

        if (sizeof($data) > 0)
            return true;
        else
            return false;
    }

    public function get_total_amount($employeeID, $claimID)
    {
        $total = 0;
        $list = $this->get_by_claim_id($employeeID, $claimID);
        foreach ($list as $item) {
            $total += $item[AMOUNT];
            $total += $item[SERVICE_CHARGE];
            $total += $item[TAX_AMOUNT];
        }

        return $total;
    }
}