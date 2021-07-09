<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RequisitionTrigger extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList()
    {
        $group = $this->input->post('group');

        if (!isset($group)) {
            $this->FailedResponse('Invalid Param');
        }

        $data = $this->trigger_requisition_m->get_by(array(
            GROUP_NAME => $group
        ), false);

        $this->SuccessResponse($data);
    }

    public function achieveTrigger()
    {
        $reference = $this->input->post('reference');

        if (!isset($reference)) {
            $this->FailedResponse('Invalid Param');
        }

        $trigger = $this->trigger_requisition_m->get_by_reference_id($reference);
        if (!$trigger){
            $this->FailedResponse('No Trigger');
        }

        $data = array(
            STATUS => '1',
        );
        $this->trigger_requisition_m->update($data, $trigger[ID]);
        $this->sendMessage1($trigger[GROUP_NAME], "Requisition trigger archived", $trigger, 1);

//        $this->newSendMessage($trigger[GROUP_NAME], $trigger);

        $this->SuccessResponse(array(
            'message' => 'Achieved Trigger'
        ));
    }
}