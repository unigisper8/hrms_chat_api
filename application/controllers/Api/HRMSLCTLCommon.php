<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HRMSLCTLCommon extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // New CR
    public function getLTCLPendingList()
    {
        $managerID = $this->input->post('managerID');

//        $data[LEAVE_PENDING_COUNT] = $this->hrms_leave_application_m->get_pending_list($managerID);
//        $data[TIMEOFF_PENDING_COUNT] = $this->hrms_timeoff_title_m->get_pending_list($managerID);
//        $data[CLAIM_PENDING_COUNT] = $this->hrms_claim_title_m->get_pending_list($managerID);
//        $data[LETTER_PENDING_COUNT] = $this->hrms_letter_m->get_pending_list($managerID);

        $data[LEAVE_PENDING_COUNT] = $this->getLeaveApprovalList($managerID);
        $data[TIMEOFF_PENDING_COUNT] = $this->getTimeOffApprovalList($managerID);
        $data[CLAIM_PENDING_COUNT] = $this->getClaimApprovalList($managerID);
        $data[LETTER_PENDING_COUNT] = $this->getLetterApprovalList($managerID);

        $this->SuccessResponse($data);
    }

    // New CR
    public function getLeaveApprovalList($managerID)
    {
        $users = $this->user_m->get_users_by_leave_manager($managerID);
        $data = $this->hrms_leave_application_m->get_by_employee_ids_pending($users);

        return count($data);
    }

    // New CR
    public function getClaimApprovalList($managerID)
    {
        $users = $this->user_m->get_users_by_claim_manager($managerID);
        $data = $this->hrms_claim_title_m->get_list_except_approved_status($users);

        return count($data);
    }

    // New CR
    public function getTimeOffApprovalList($managerID)
    {
        $users = $this->user_m->get_users_by_timeoff_manager($managerID);
        $data = $this->hrms_timeoff_title_m->get_by_employee_ids_pending($users);

        return count($data);
    }

    // New CR
    public function getLetterApprovalList($managerID)
    {
        $users = $this->user_m->get_users_by_letter_manager($managerID);
        $data = $this->hrms_letter_m->get_by_employee_ids_pending($users);

        return count($data);
    }

}