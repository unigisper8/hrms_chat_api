<?php
defined('BASEPATH') or exit('No direct script access allowed');

class HRMSAttendance extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList()
    {
        $employeeID = $this->input->post('employee');
        $userID = $this->input->post('userID');

        if (!isset($employeeID)) {
            $this->FailedResponse('Invalid Param');
        }

        // $data_array = $this->hrms_message_m->get_by_employee_id($employeeID);
        $data_array = $this->hrms_message_m->get_list($employeeID, $userID);

        // foreach ($data_array as $idx => $data){
        //     $data_array[$idx] = $this->completeData($data);
        // }

        $this->SuccessResponse($data_array);
    }

    public function getMessageList()
    {
        $employeeID = $this->input->post('employee');
        $userID = $this->input->post('userID');

        if (!isset($employeeID)) {
            $this->FailedResponse('Invalid Param');
        }

        // $data_array = $this->hrms_message_m->get_by_employee_id($employeeID);
        $data_array = $this->hrms_message_m->get_list($employeeID, $userID);

        // foreach ($data_array as $idx => $data){
        //     $data_array[$idx] = $this->completeData($data);
        // }

        $this->SuccessResponse($data_array);
    }

    public function newMessage()
    {
        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');
        $message = $this->input->post('message');
        $photo = $this->input->post('photo');
        $type = $this->input->post('type');
        $photo_path = '';
        if ($photo != '') {
            $photo_path = UploadImage($photo, $type, 'attendance/');
            if (!isset($sender) || !isset($receiver)) {
                $this->FailedResponse('Invalid Param');
            }
        }

        $data = array(
            SENDER => $sender,
            RECEIVER => $receiver,
            MESSAGE => $message,
            DATE_CREATED => $this->currentTime(),
        );
        if ($sender != '') {
            $user = $this->user_m->get_by_employee_id($sender);
            if ($user) {
                $data['SENDER_NAME'] = $user[EMPLOYEE_NAME];
                $data['SENDER_PHOTO'] = $user[PHOTO];
            }
        }
        if ($receiver != '') {
            $user = $this->user_m->get_by_employee_id($receiver);
            if ($user) {
                $data['RECEIVER_NAME'] = $user[EMPLOYEE_NAME];
                $data['RECEIVER_PHOTO'] = $user[PHOTO];
            }
        }
        if ($photo_path) {
            $data[PHOTO] = $photo_path;
        }

        $data_id = $this->hrms_message_m->save($data);
        if ($data_id) {
            $data = $this->hrms_message_m->get($data_id);
            $array_token = $this->user_m->get_users_by_group_name_HR($sender, 'device_token');
            foreach ($array_token as $key => $value) {
                # code...
                $token = $value['device_token'];
                $group_user = $value['EMPLOYEE_ID'];
                $title = "Message received from: " . $data['SENDER'];
                $this->sendMessage2($group_user, "Message received from", "", 3, $data);
                $message_data = array
                    (
                    'ID' => $data['id'],
                    'SENDER' => $data['SENDER'],
                    'DATE_CREATED' => $data['DATE_CREATED'],
                    'MESSAGE' => $data['MESSAGE'],
                    'SENDER_PHOTO' => $data['SENDER_PHOTO'],
                    'PHOTO' => $data['PHOTO'],
                    'TYPE' => 3
                );
                $body = $data['MESSAGE'];
                $this->sendMessage3($token, $title, $body, $message_data);
            }
            //    $this->newSendMessage($trigger[GROUP_NAME], $trigger);
            $this->SuccessResponse($data);
        }
        else {
            $this->FailedResponse('Something went wrong.');
        }
    }

    public function getLateMessageList()
    {
        $employeeID = $this->input->post('employee');
        $userID = $this->input->post('userID');
        $attendance_id = $this->input->post('attendance_id');
        if (!isset($employeeID, $userID)) {
            $this->FailedResponse('Invalid Param');
        }
        $this->hrms_attendance_m->set_receive_users($attendance_id, $employeeID);
        $data_array = $this->hrms_attendance_late_message_m->get_list($employeeID, $userID);

        // foreach ($data_array as $idx => $data){
        //     $data_array[$idx] = $this->completeData($data);
        // }

        $this->SuccessResponse($data_array);
    }

    public function newLateMessage()
    {
        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');
        $message = $this->input->post('message');
        $photo = $this->input->post('photo');
        $type = $this->input->post('type');
        $photo_path = '';
        if ($photo != '') {
            $photo_path = UploadImage($photo, $type, 'attendance_late/');
            if (!isset($sender) || !isset($receiver)) {
                $this->FailedResponse('Invalid Param');
            }
        }

        $data = array(
            SENDER => $sender,
            RECEIVER => $receiver,
            MESSAGE => $message,
            DATE_CREATED => $this->currentTime(),
        );
        if ($sender != '') {
            $user = $this->user_m->get_by_employee_id($sender);
            if ($user) {
                $data['SENDER_NAME'] = $user[EMPLOYEE_NAME];
                $data['SENDER_PHOTO'] = $user[PHOTO];
            }
        }
        
        if ($receiver != '') {
            $user = $this->user_m->get_by_employee_id($receiver);
            if ($user) {
                $data['RECEIVER_NAME'] = $user[EMPLOYEE_NAME];
                $data['RECEIVER_PHOTO'] = $user[PHOTO];
            }
        }

        if ($photo_path) {
            $data[PHOTO] = $photo_path;
        }

        $data_id = $this->hrms_attendance_late_message_m->save($data);
        if ($data_id) {
            $data = $this->hrms_attendance_late_message_m->get($data_id);
            $user = $this->user_m->get_by_employee_id($receiver);
            $token = $user['device_token'];

            $title = "Message received from: " . $data['SENDER'];
            $message_data = array
                (
                'ID' => $data['id'],
                'SENDER' => $data['SENDER'],
                'DATE_CREATED' => $data['DATE_CREATED'],
                'MESSAGE' => $data['MESSAGE'],
                'SENDER_PHOTO' => $data['SENDER_PHOTO'],
                'PHOTO' => $data['PHOTO'],
                'TYPE' => 20
            );
            $body = $data['MESSAGE'];

            $this->sendMessage3($token, $title, $body, $message_data);
            if (isset($receiver))
                $this->sendMessage2($receiver, "Message received from", "", 20, $data);

            $this->SuccessResponse($data);
        }
        else {
            $this->FailedResponse('Something went wrong.');
        }
    }
}