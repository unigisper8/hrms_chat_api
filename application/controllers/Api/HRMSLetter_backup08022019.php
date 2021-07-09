<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HRMSLetter extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /*** HRMS (Second app) ***/

    public function submitLetter()
    {
        $from = $this->input->post('From');
        $to = $this->input->post('To');
        $date_issued = $this->input->post('Date_Issued');
        $template = $this->input->post('Template');
        $message = $this->input->post('message');
        $photo_path     = UploadPhoto('photo');

        if (!isset($from)
            || !isset($to)
            || !isset($date_issued)
            || !isset($template)
            || !isset($message)
        )
            $this->FailedResponse('Invalid Param');

        if ($this->hrms_letter_m->check_duplicated($from, $to, $date_issued, $template)) {
            $this->FailedResponse("Duplicate entry");
        }

        $employeeFrom = $this->user_m->get_by_employee_id($from);
        $employeeTo = $this->user_m->get_by_employee_id($to);
        $t = $this->hrms_letter_template_m->get_by_name($template);

        $managerApprovalStatus = 0;
        $employeeTopManagementEsc = $employeeTo['top_management_esc'];
        if ($employeeTopManagementEsc == 1) {
            $managerApprovalStatus = 1; //Should go to top management for approval
        }

        $data = array(
            EMPLOYEE_ID     => $from,
            FROM            => $from,
            FROM_NAME       => $employeeFrom[EMPLOYEE_NAME],
            TO              => $to,
            TO_NAME         => $employeeTo[EMPLOYEE_NAME],
            DATE_ISSUED     => $date_issued,
            TEMPLATE        => $template,
            MESSAGE         => $message,
            PARA_NO_1       => $t[PARA_NO_1],
            PARA_NO_2       => $t[PARA_NO_2],
            PARA_NO_3       => $t[PARA_NO_3],
            MANAGER_ID      => $employeeTo[MANAGER],
            TOP_MANAGEMENT_APPROVE_STATUS      => $managerApprovalStatus,
            PHOTO			=> $photo_path,
            DATE_CREATED	=> $this->currentTime()
        );

        $data_id = $this->hrms_letter_m->save($data);
        if ($data_id) {
            $data = $this->hrms_letter_m->get($data_id);
            $user = $this->user_m->get_by_employee_id($to);

            if ($employeeTopManagementEsc == 1) {
                $topManagerId = $employeeTo['top_management_emp'];
                $user = $this->user_m->get_by_employee_id($topManagerId);
            }

            // Send alert to top manager, direct manager, employee from HR
            $this->sendMessage2('Letter', "Success", $data, 16, $user);

            $this->SuccessResponse($data);
        } else {
            $this->FailedResponse('Something went wrong.');
        }
    }

    public function getUsersToSend()
    {
        $employee_id = $this->input->post('employeeID');
        $query = $this->input->post('query');

        $data = $this->user_m->get_users($employee_id, $query);
        $this->SuccessResponse($data);
    }

    public function deleteLetter()
    {
        $employeeID = $this->input->post('employeeID');
        $letterID = $this->input->post('Letter_ID');

        if (!isset($employeeID) || !isset($letterID))
            $this->FailedResponse('Invalid Param');

        $result = $this->hrms_letter_m->delete($letterID);
        if ($result) {
            $this->SuccessResponse("Success");
        } else {
            $this->FailedResponse("Failed");
        }
    }

    public function getList()
    {
        $employeeID = $this->input->post('employeeID');
        $userID = $this->input->post('userID');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');

        $data = $this->hrms_letter_m->get_list($employeeID, $userID, $startDate, $endDate);

        $this->SuccessResponse($data);
    }

    public function getTemplateList()
    {
        $data = $this->hrms_letter_template_m->get_list();

        $this->SuccessResponse($data);
    }

    public function updateLetterStatus()
    {
        $letterID = $this->input->post('Letter_ID');
        $status = $this->input->post('Status');

        if (!isset($letterID, $status))
            $this->FailedResponse('Invalid Param');

        $result = $this->hrms_letter_m->update_status($letterID, $status);
        if ($result) {
            $data = $this->hrms_letter_m->get($letterID);
            // send alert
            if ($status == 'Acknowledge') {
                $this->sendMessage2('', "Letter Acknowledge", $data, 18, null);
                $fields = array(
                    ID => $letterID
                );
                $this->post(BASE_URL . "ER_Letter.Mobile.aspx?id=" . $letterID, $fields);    // ???
            } else if ($status == 'Approved') {
                // New CR
                $managerApprovalStatus = $data[TOP_MANAGEMENT_APPROVE_STATUS];
                if ($managerApprovalStatus == 1) {
                    $managerApprovalStatus = 2; //Already approved
                    $this->hrms_letter_m->update_top_manager_status($letterID, $managerApprovalStatus);

                    $data = $this->hrms_letter_m->get($letterID);
                    $to = $data[TO];
                    $user = $this->user_m->get_by_employee_id($to);
                    // Send alert to top manager, direct manager, employee from HR
                    $this->sendMessage2('Letter', "Success", $data, 16, $user);
                } else {
                    $this->sendMessage2('', "Letter approved", $data, 18, null);
                    $fields = array(
                        ID => $letterID
                    );
                    $this->post(BASE_URL . "ER_Letter.Mobile.aspx?id=" . $letterID, $fields);    // ???
                }

            } else {
                $this->sendMessage2('', "Letter rejected", $data, 18, null);
            }

            $this->SuccessResponse("Success");
        } else {
            $this->FailedResponse("Failed");
        }
    }

    public function getMessageList()
    {
        $employeeID = $this->input->post('employee');
        $userID = $this->input->post('userID');
        $letterID = $this->input->post('letterID');

        if (!isset($employeeID) || !isset($letterID)) {
            $this->FailedResponse('Invalid Param');
        }

        $data_array = $this->hrms_letter_message_m->get_list($employeeID, $userID, $letterID);

        $this->SuccessResponse($data_array);
    }

    public function newMessage()
    {
        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');
        $letterID = $this->input->post('letterID');
        $message = $this->input->post('message');
        $photo_path = UploadPhoto('photo');

        if (!isset($sender) || !isset($receiver) || !isset($letterID)) {
            $this->FailedResponse('Invalid Param');
        }

        $data = array(
            SENDER          => $sender,
            RECEIVER        => $receiver,
            MESSAGE         => $message,
            LETTER_ID       => $letterID,
            DATE_CREATED    => $this->currentTime(),
        );
        if ($sender != '') {
            $user = $this->user_m->get_by_employee_id($sender);
            if ($user) {
                $data['SENDER_NAME'] = $user[EMPLOYEE_NAME];
                $data['SENDER_PHOTO'] = $user[PHOTO];
            }
        }
        // multiple receiver
        //     $user = $this->user_m->get_by_employee_id($receiver);
        //     if ($user) {
        //         $data['RECEIVER_NAME'] = $user[EMPLOYEE_NAME];
        //         $data['RECEIVER_PHOTO'] = $user[PHOTO];
        //     }

        if ($photo_path) {
//            $photo_path = createThumb($photo_path);
            if ($photo_path) {
                $data[PHOTO] = base_url($photo_path);
            }
        }

        $data_id = $this->hrms_letter_message_m->save($data);
        if ($data_id) {
            $data = $this->hrms_letter_message_m->get($data_id);

            $letter = $this->hrms_letter_m->get($letterID);
            $this->sendMessage2($receiver, "Message received from", $letter, 17, $data);

//            $this->newSendMessage($trigger[GROUP_NAME], $trigger);
            $this->SuccessResponse($data);
        } else {
            $this->FailedResponse('Something went wrong.');
        }
    }
}