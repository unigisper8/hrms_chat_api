<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trigger extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

//    public function getList()
//    {
//        $group = $this->input->post('group');
//        $start = $this->input->post('start');
//        $end = $this->input->post('end');
//        $cacType = $this->input->post('cacType');
//        $custName = $this->input->post('custName');
//
//        if (!isset($group)) {
//            $this->FailedResponse('Invalid Param');
//        }
//
//        $data = $this->trigger_m->get_list($group, $start, $end, $cacType, $custName);
//
//        $this->SuccessResponse($data);
//    }

    public function getList()
    {
        $request_body = file_get_contents('php://input');
        $request_json = json_decode($request_body, true);
        $group = $request_json['group'];
        $referencesInvited = $request_json['referencesInvited'];
        $start = $request_json['start'];
        $end = $request_json['end'];
        $cacType = $request_json['cacType'];
        $custName = $request_json['custName'];

        if (!isset($group) || sizeof($group) == 0) {
            $this->FailedResponse('Invalid Param');
        }

        $data = $this->trigger_m->get_list($group, $referencesInvited, $start, $end, $cacType, $custName);

        $this->SuccessResponse($data);
    }

    public function achieveTrigger()
    {
        $reference = $this->input->post('reference');
        $employeeName = $this->input->post('employeeName');
        $remark = $this->input->post('remark');

        if (!isset($reference)) {
            $this->FailedResponse('Invalid Param');
        }

        $trigger = $this->trigger_m->get_by_reference_id($reference);
        if (!$trigger) {
            $this->FailedResponse('No Trigger');
        }

        $data = array(
            STATUS => '1',
			ARCHIVE_BY => $employeeName,
			REMARK => $remark,
        );
        $this->trigger_m->update($data, $trigger[ID]);
        $this->sendMessage1($trigger[GROUP_NAME], "Trigger archived", $trigger, 0);

//        $this->newSendMessage($trigger[GROUP_NAME], $trigger);

        $this->SuccessResponse(array(
            'message' => 'Achieved Trigger'
        ));
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

			$trigger = $this->trigger_m->get_by_reference_id($referenceNo);
            $this->sendMessage1("Trigger Invite", 'Invite', $user, 4, $trigger);
        }
        $this->SuccessResponse('Invite success');
    }

    public function getUsersToInvite()
    {
        $name = $this->input->post('name');
        $group = $this->input->post('group');
        $referenceNo = $this->input->post('referenceNo');
        $query = $this->input->post('query');

        $data = $this->user_m->get_all_users_to_invite($name, $group, $referenceNo, $query);
        $this->SuccessResponse($data);
    }

//    public function getUsers()
//    {
//        $group = $this->input->post('group');
//        $query = $this->input->post('query');
//
//        $data = $this->trigger_m->get_cust_list($group, $query);
//        $this->SuccessResponse($data);
//    }

    public function getUsers()
    {
        $request_body = file_get_contents('php://input');
        $request_json = json_decode($request_body, true);
        $group = $request_json['group'];
        $query = $request_json['query'];

        $data = $this->trigger_m->get_cust_list($group, $query);
        $this->SuccessResponse($data);
    }

//    public function getCACTypeList()
//    {
//        $group = $this->input->post('group');
//        $query = $this->input->post('query');
//
//        $data = $this->trigger_m->get_cac_type_list($group, $query);
//        $this->SuccessResponse($data);
//    }

    public function getCACTypeList()
    {
        $request_body = file_get_contents('php://input');
        $request_json = json_decode($request_body, true);
        $group = $request_json['group'];
        $query = $request_json['query'];

        $data = $this->trigger_m->get_cac_type_list($group, $query);
        $this->SuccessResponse($data);
    }
}