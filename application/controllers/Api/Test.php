<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function date()
    {
        $output_format = 'Y-m-d';
        $days = 4;
        $startDate = "2018-09-20";
        $emergency = $this->hrms_emergency_leave_m->get(null, true);
        if ($emergency) {
            $days = $emergency['value'];
            $current = strtotime($startDate);
            $current = strtotime('-' . $days . ' days', $current);
            $emergencyDate = date($output_format, $current);
            $today = date($output_format);
            if ($emergencyDate < $today)
                $this->FailedResponse("Annual Leave needs to apply $days days earlier $emergencyDate , $today");
            else $this->SuccessResponse("OK");
        }
    }


    public function getDepartments()
    {
        $data = $this->db->select("department")
            ->where("department!=", null)
            ->where("department!=", '')
            ->group_by("department")
            ->get("MAN_MOB_GROUP_NAME")
            ->result_array();

        $result = array();
        foreach ($data as $item) $result[] = $item["department"];
        $this->SuccessResponse($result);
    }
}