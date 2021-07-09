<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RequisitionChat extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function completeData($data){
        $empl_id = $data[EMPLOYEE_ID];
        $user = $this->user_m->get_by_employee_id($empl_id);
        if ($user){
            $data['USER_PHOTO'] = $user[PHOTO];
            $data['EMPLOYEE_NAME'] = $user[EMPLOYEE_NAME];
        }
        return $data;
    }

    public function getList()
    {
        $reference = $this->input->post('reference');

        if (!isset($reference)) {
            $this->FailedResponse('Invalid Param');
        }

        $data_array = $this->requisition_messenger_m->get_by_reference_id($reference);

        foreach ($data_array as $idx => $data){
            $data_array[$idx] = $this->completeData($data);
        }

        $this->SuccessResponse($data_array);
    }

    public function newMessage()
    {
        $reference = $this->input->post('reference');
        $empl_id = $this->input->post('employee');
        $message = $this->input->post('message');
        $photo_path = UploadPhoto('photo');

        if (!isset($reference, $empl_id)) {
            $this->FailedResponse('Invalid Param');
        }

		$requisition = $this->requisition_m->get_by_reference_id($reference);

        if (!$requisition){
            $this->FailedResponse("There is no requisition for the reference");
        }

        $requisitionListing = $this->requisition_listing_m->get_by_reference_id($reference);

        if (!$requisitionListing){
            $this->FailedResponse("There is no requisition for the reference");
        }

        $data = array(
            EMPLOYEE_ID => $empl_id,
            REFERENCE_NO => $reference,
            MESSENGER => $message,
            DATE_CREATED => $this->currentTime(),
        );

        if ($photo_path) {
//            $photo_path = createThumb($photo_path);
            if ($photo_path) {
                $data[PHOTO] = base_url($photo_path);
            }
        }

        $data_id = $this->requisition_messenger_m->save($data);
        if ($data_id) {
            $data = $this->requisition_messenger_m->get($data_id);
            $data = $this->completeData($data);

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

            $this->sendMessage1($requisitionListing[GROUP_NAME], "Message received from", $trigger, 1, $data);

            $this->SuccessResponse($data);
        } else {
            $this->FailedResponse('Something went wrong.');
        }
    }
}