<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Requisition extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

//    public function getList()
//    {
//        $offset = $this->input->post('offset');
//        $count = $this->input->post('count');
//        $start = $this->input->post('start');
//        $end = $this->input->post('end');
//
//        if (!isset($offset))
//            $offset = 0;
//        if (!isset($count))
//            $count = 0;
//        if (!isset($start))
//            $start = null;
//        if (!isset($end))
//            $end = null;
//
//        $data = $this->requisition_listing_m->get_list($count, $offset, $start, $end);
//
//        $this->SuccessResponse($data);
//    }

    public function getList()
    {
        $request_body = file_get_contents('php://input');
        $request_json = json_decode($request_body, true);
        $group = $request_json['group'];
        $referencesInvited = $request_json['referencesInvited'];
        $offset = $request_json['offset'];
        $count = $request_json['count'];
        $start = $request_json['start'];
        $end = $request_json['end'];

        if (!isset($offset))
            $offset = 0;
        if (!isset($count))
            $count = 0;
        if (!isset($start))
            $start = null;
        if (!isset($end))
            $end = null;

        $data = $this->requisition_listing_m->get_list($group, $referencesInvited, $count, $offset, $start, $end);
		
		foreach ($data as $item) {
			$approval = $item['APPROVAL_LOGIN'];
			$request = $item['REQUEST_LOGIN'];
			$prd = $item['PRD_LOGIN'];
			$prd_compltn = $item['PRD_COMPLTD_LOGIN'];
			$qa = $item['QA_LOGIN'];
			$oqc = $item['OQC_LOGIN'];
			$deld = $item['DELD_LOGIN'];
			$srf = $item['SRF_LOGIN'];

			$user = $this->user_m->get_by_employee_id($approval);
			if ($user != null) {
				$item['APPROVAL_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['APPROVAL_LOGIN'] = '';
			}
			$user = $this->user_m->get_by_employee_id($request);
			if ($user != null) {
				$item['REQUEST_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['REQUEST_LOGIN'] = '';
			}
			$user = $this->user_m->get_by_employee_id($prd);
			if ($user != null) {
				$item['PRD_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['PRD_LOGIN'] = '';
			}
			$user = $this->user_m->get_by_employee_id($prd_compltn);
			if ($user != null) {
				$item['PRD_COMPLTD_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['PRD_COMPLTD_LOGIN'] = '';
			}
			$user = $this->user_m->get_by_employee_id($qa);
			if ($user != null) {
				$item['QA_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['QA_LOGIN'] = '';
			}
			$user = $this->user_m->get_by_employee_id($oqc);
			if ($user != null) {
				$item['OQC_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['OQC_LOGIN'] = '';
			}
			$user = $this->user_m->get_by_employee_id($deld);
			if ($user != null) {
				$item['DELD_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['DELD_LOGIN'] = '';
			}
			$user = $this->user_m->get_by_employee_id($srf);
			if ($user != null) {
				$item['SRF_LOGIN'] = $user[EMPLOYEE_NAME];
			} else {
				$item['SRF_LOGIN'] = '';
			}
		}

        $this->SuccessResponse($data);
    }

    public function getDetail()
    {
        $reference = $this->input->post('reference');

        if (!isset($reference)) {
            $this->FailedResponse('Invalid Param');
        }

        $data = $this->requisition_m->get_by_reference_id($reference);

        $this->SuccessResponse($data);
    }

    public function updateApproved()
    {
        $referenceNo = $this->input->post('referenceNo');
        $userID = $this->input->post('userID');
        $isApproved = $this->input->post('isApproved');
        $mark = $this->input->post('mark');

        if (!isset($referenceNo) || !isset($userID) || !isset($isApproved) || !isset($mark)) {
            $this->FailedResponse('Invalid Param');
        }

        // requisition listing ID
        $requisitionListing = $this->requisition_listing_m->get_by_reference_id($referenceNo);
        if (!$requisitionListing) {
            $this->FailedResponse('No Requisition Listing');
        }
        // requisition ID
        $requisition = $this->requisition_m->get_by_reference_id($referenceNo);
        if (!$requisition) {
            $this->FailedResponse('No Requisition');
        }

        $isApproved = ($isApproved == 'true');

        // update requisition listing
        $data = array(
            APPROVAL_DATE => date('Y-m-d'),
            APPROVAL_LOGIN => $userID,
        );
        $this->requisition_listing_m->update($data, $requisitionListing[ID]);

        // update requisition
        $data = array(
            STATUS => $isApproved ? 'Approved' : 'Rejected',
            REMARK => $mark,
        );
		if (!$isApproved)
			$data[SEND_STATUS] = '1';
        $this->requisition_m->update($data, $requisition[ID]);
        $requisition = $this->requisition_m->get_by_reference_id($referenceNo);

        // if rejected, then add trigger & send alert
        if (!$isApproved) {
            $title = "[REQUISITION FORM][SAMPLE][" . $requisition[A_CUSTOMER] . "]";
            $trigger = array(
                REFERENCE_NO => $requisitionListing[REFERENCE_NO],
                GROUP_NAME => $requisitionListing[GROUP_NAME],
                TITLE => $title,
                DESCRIPTION => '',
                SEND_STATUS => '1',
                STATUS => 'Rejected',
                DATE_CREATED => $this->currentTime()
            );

            $this->sendMessage1($requisitionListing[GROUP_NAME], $title, $trigger, 1);
        }

        $this->SuccessResponse($requisition);
    }

    public function invite()
    {
        $employeeID = $this->input->post('employeeID');
        $referenceNo = $this->input->post('referenceNo');

        if (!isset($employeeID, $referenceNo)) {
            $this->FailedResponse("Invalid Param");
        }

        $user = $this->user_m->get_by_employee_id($employeeID);
        $references = $user[REFERENCES_INVITED];
		
        if (!in_array($referenceNo, $references)) {
            if (sizeof($references) == 1 && $references[0] == '')
                $references[0] = $referenceNo;
            else
                $references[] = $referenceNo;
            $strReferences = implode(',', $references);
			$data = array(
				REFERENCES_INVITED => $strReferences
			);
            $this->user_m->update($data, $user[ID]);
			$user[REFERENCES_INVITED] = $references;

			$requisitionListing = $this->requisition_listing_m->get_by_reference_id($referenceNo);
			$requisition = $this->requisition_m->get_by_reference_id($referenceNo);
			$title = "[REQUISITION FORM][SAMPLE][" . $requisition[A_CUSTOMER] . "]";
            $trigger = array(
                REFERENCE_NO => $referenceNo,
                GROUP_NAME => $requisitionListing[GROUP_NAME],
                TITLE => $title,
            );
            $this->sendMessage1("Trigger Invite", 'Invite', $user, 6, $trigger);
        }
        $this->SuccessResponse('Invite success');
    }

    public function getUsersToInvite()
    {
        $name = $this->input->post('name');
        $group = $this->input->post('group');
        $referenceNo = $this->input->post('referenceNo');
        $query = $this->input->post('query');

        $data = $this->user_m->get_users_to_invite($name, $group, $referenceNo, $query);
        $this->SuccessResponse($data);
    }
}