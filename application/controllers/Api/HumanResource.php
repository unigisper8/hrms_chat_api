<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HumanResource extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAttendance()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_attendance_m->get_by_employee_id($employeeID);

        $this->SuccessResponse($data);
    }

    public function getSalary()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_salary_m->get_by_employee_id($employeeID);

        $this->SuccessResponse($data);
    }

    public function getLeave()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_leave_m->get_by_employee_id($employeeID);

        $this->SuccessResponse($data);
    }
}