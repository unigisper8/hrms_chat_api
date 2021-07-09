<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HRMSPayroll extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
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

    public function getPayrollData()
    {
        $employeeID = $this->input->post('EMPLOYEE_ID');
        $month = $this->input->post('month');

		//First date of current month.
		$firstDateOfMonth = date("Y-m-01", strtotime($month));
		//Last date of current month.
		$lastDateOfMonth = date("Y-m-t", strtotime($month));

		$reporting = $this->db->select("Reporting")
			->from("MAN_MOB_GROUP_NAME")
			->where("EMPLOYEE_ID=", $employeeID)
			->where("GROUP_NAME=", "HUMAN RESOURCE")
			->get()
            ->row_array();
		$reporting = $reporting['Reporting'];

        $query = $this->db->select("A.*")
            ->from("MAN_MOB_HRMS_PAYROLL as A")
            ->join("MAN_MOB_GROUP_NAME as B", "A.EMPLOYEE_ID=B.EMPLOYEE_ID")
			->where("FIND_IN_SET(A.EMPLOYEE_ID, '$reporting')")
            ->where("A.Date>=", $firstDateOfMonth)
            ->where("A.Date<=", $lastDateOfMonth);

        $data = $query->group_by("A.EMPLOYEE_ID")
            ->get()->result_array();

		$result = array();
		foreach ($data as $item) {
			$query = $this->db->select("A.*")
                ->from("MAN_MOB_HRMS_PAYROLL as A")
                ->join("MAN_MOB_GROUP_NAME as B", "A.EMPLOYEE_ID=B.EMPLOYEE_ID")
    			->where("FIND_IN_SET(A.EMPLOYEE_ID, '$reporting')")
                ->where("A.Date>=", $firstDateOfMonth)
                ->where("A.Date<=", $lastDateOfMonth)
				->where("A.EMPLOYEE_ID=", $item[EMPLOYEE_ID]);
			$data = $query->get()->result_array();
			$result[] = $data;
		}

        // $this->SuccessResponse($data);
        $this->SuccessResponse($result);
    }
}