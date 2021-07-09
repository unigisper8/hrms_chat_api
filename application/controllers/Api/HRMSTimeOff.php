<?php

use SebastianBergmann\Environment\Console;

defined('BASEPATH') or exit('No direct script access allowed');

class HRMSTimeOff extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /*** HRMS (Second app) ***/

    public function getTimeOffTypeList()
    {
        $data = $this->hrms_timeoff_type_m->get_list();

        $this->SuccessResponse($data);
    }
    public function setTimeOffReceived()
    {
        $timeOffID = $this->input->post('timeOffID');
        $this->hrms_timeoff_title_m->timeOff_received($timeOffID);
    }
    public function getTimeOffModuleList()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_timeoff_title_m->get_by_employee_id($employeeID);

        $this->SuccessResponse($data);
    }

    public function submitTimeOffModule()
    {
        $employeeID = $this->input->post('employeeID');
        $timeoff_id = $this->input->post('Timeoff_ID');
        $title = $this->input->post('title');
        $country = $this->input->post('country');
        $type = $this->input->post('type');
        $date = $this->input->post('date');
        $startTime = $this->input->post('startTime');
        $endTime = $this->input->post('endTime');
        $description = $this->input->post('description');
        $status = $this->input->post('status');
        $photo_path = UploadPhoto('photo');
        if (
            !isset($employeeID)
            || !isset($title)
            || !isset($country)
            || !isset($type)
            || !isset($date)
            || !isset($startTime)
            || !isset($endTime)
            || !isset($description)
            || !isset($status)
        )
            $this->FailedResponse('Invalid Param');

        if ($this->hrms_timeoff_title_m->check_duplicated($employeeID, $title, $country, $type, $date, $startTime, $endTime, $description, $status)) {
            $this->FailedResponse("Duplicate entry");
        }

        $employee = $this->user_m->get_by_employee_id($employeeID);
        $manager = $this->user_m->get_by_employee_id($employee[TIMEOFF_MANAGER_ID]);

        $data = array(
            EMPLOYEE_ID => $employeeID,
            EMPLOYEE_NAME => $employee[EMPLOYEE_NAME],
            MANAGER_ID => $employee[TIMEOFF_MANAGER_ID],
            // MANAGER_NAME        => $manager[EMPLOYEE_NAME],
            TIMEOFF_TITLE => $title,
            COUNTRY => $country,
            TYPE => $type,
            DATE => $date,
            START_HOUR => $startTime,
            END_HOUR => $endTime,
            DESCRIPTION => $description,
            PHOTO => $photo_path,
            STATUS => $status,                 // ???????????????????????????????????????
            DATE_CREATED => $this->currentTime()
        );

        if ($photo_path) {
            // $photo_path = createThumb($photo_path);
            // if ($photo_path) {
            $data[PHOTO] = $photo_path;
            // }
        }

        $data_id = null;
        if (isset($timeoff_id) && $timeoff_id != '') {
            $data_id = $this->hrms_timeoff_title_m->update($data, $timeoff_id);
        } else {
            $data_id = $this->hrms_timeoff_title_m->save($data);
        }
        if ($data_id) {
            $data = "";
            if ($timeoff_id == "" || $timeoff_id == null) {
                $data = $this->hrms_timeoff_title_m->get($data_id);
            } else {
                $data = $this->hrms_timeoff_title_m->get($timeoff_id);
            }
            $user = $this->user_m->get_by_employee_id($employeeID);
            $timeoffManagers = explode(',', $user[TIMEOFF_MANAGER_ID]);
            // Send alert to manager
            foreach ($timeoffManagers as $key => $manager) {
                $employee = $this->user_m->get_by_employee_id($manager);
                $token = $employee['device_token'];
                $title = "[GatePass][" . $data['EMPLOYEE_NAME'] . " – " . $data['EMPLOYEE_ID'] . "][" . $data['Type'] . "][" . $data[START_HOUR] . "][" . $data[END_HOUR] . "]";
                $body = $data['Date'];

                $message = array(
                    'TYPE' => 13
                );

                $message_data = array_merge($data, $message);

                $this->sendMessage3($token, $title, $body, $message_data);
            }
            if ($status == "Pending") {
                $this->sendMessage2('TimeOff_Manager', "Success", $data, 13, $user);
                $fields = array(
                    ID => $timeoff_id
                );
                // if (!TEST_MODE)
                // $this->post(BASE_URL . "TA_Timeoff_Entry.aspx?id=" . $timeoff_id, $fields);    // ???
            }

            $this->SuccessResponse($data);
        } else {
            $this->FailedResponse('Something went wrong.');
        }
    }

    public function getTimeOffApprovalList()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $users = $this->user_m->get_users_by_timeoff_manager($employeeID);
        $data = $this->hrms_timeoff_title_m->get_by_employee_ids($users);

        $this->SuccessResponse($data);
    }

    public function updateTimeOffStatus()
    {
        $timeoff_ID = $this->input->post('Timeoff_ID');
        $status = $this->input->post('Status');

        if (!isset($timeoff_ID, $status))
            $this->FailedResponse('Invalid Param');

        $result = $this->hrms_timeoff_title_m->update_status($timeoff_ID, $status);
        if ($result) {
            $data = $this->hrms_timeoff_title_m->get($timeoff_ID);
            // Send alert to employee
            if ($status == 'Approved') {
                $this->sendMessage2('', "Gate Pass approved", $data, 15, null);
                $employee = $this->user_m->get_by_employee_id($data[EMPLOYEE_ID]);
                $token = $employee['device_token'];
                $title = "Gate Pass Approved";
                $body = $data[TIMEOFF_TITLE];
                $message = array(
                    'TYPE' => 15
                );
                $message_data = array_merge($data, $message);

                $this->sendMessage3($token, $title, $body, $message_data);
                // $fields = array(
                //     ID => $timeoff_ID
                // );
                // if (!TEST_MODE)
                // $this->post(BASE_URL . "TA_Timeoff_Mobile.aspx?id=" . $timeoff_ID, $fields);    // ???
            } else {
                $employee = $this->user_m->get_by_employee_id($data[EMPLOYEE_ID]);
                $token = $employee['device_token'];
                $title = "Gate Pass Rejected";
                $body = $data[TIMEOFF_TITLE];
                $message = array(
                    'TYPE' => 15
                );
                $message_data = array_merge($data, $message);
                $this->sendMessage3($token, $title, $body, $message_data);
                $this->sendMessage2('', "Gate Pass rejected", $data, 15, null);
            }

            $this->SuccessResponse("Success");
        } else {
            $this->FailedResponse("Failed");
        }
    }

    public function deleteTimeOff()
    {
        $employeeID = $this->input->post('employeeID');
        $timeoff_ID = $this->input->post('Timeoff_ID');

        if (!isset($employeeID) || !isset($timeoff_ID))
            $this->FailedResponse('Invalid Param');

        $result = $this->hrms_timeoff_title_m->delete($timeoff_ID);
        $fields = array(
            ID => $timeoff_ID
        );
        // $this->post(BASE_URL . "TA_Timeoff_Delete.aspx?id=" . $timeoff_ID, $fields);    // ???
        $this->SuccessResponse("Success");
    }

    public function getHRMSTimeOffStatistics()
    {
        $employeeID = $this->input->post('employeeID');
        $id = $this->input->post('id');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');
        $this->hrms_timeoff_title_m->update_send_status($id);
        $month = $this->hrms_timeoff_title_m->get_list_in_month($employeeID);
        $year = $this->hrms_timeoff_title_m->get_list_in_year($employeeID);

        $result = array(
            TIMEOFF_MONTH_STATISTICS => $month,
            TIMEOFF_YEAR_STATISTICS => $year
        );
        $this->SuccessResponse($result);
    }

    public function getMessageList()
    {
        $employeeID = $this->input->post('employee');
        $userID = $this->input->post('userID');
        $timeoffID = $this->input->post('Timeoff_ID');

        if (!isset($employeeID) || !isset($timeoffID)) {
            $this->FailedResponse('Invalid Param');
        }
        $this->hrms_timeoff_message_m->receive_message($employeeID, $timeoffID, 1);
        $data_array = $this->hrms_timeoff_message_m->get_list($employeeID, $userID, $timeoffID);

        $this->SuccessResponse($data_array);
    }

    public function refreshTimeoffChat()
    {
        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');
        $timeoffID = $this->input->post('timeoffID');
        $this->hrms_timeoff_message_m->receive_message($sender, $timeoffID, 1);
        $count = $this->hrms_timeoff_message_m->get_message_status($sender, $receiver, $timeoffID);
        echo json_encode($count);
        die();
    }

    public function newMessage()
    {
        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');
        $timeoffID = $this->input->post('Timeoff_ID');
        $message = $this->input->post('message');
        $photo = $this->input->post('photo');
        $type = $this->input->post('type');
        $photo_path = '';
        if ($photo != '') {
            $photo_path = UploadImage($photo, $type, 'timeoff/');
            if (!isset($sender) || !isset($receiver)) {
                $this->FailedResponse('Invalid Param');
            }
        }

        $data = array(
            SENDER => $sender,
            RECEIVER => $receiver,
            MESSAGE => $message,
            TIMEOFF_ID => $timeoffID,
            DATE_CREATED => $this->currentTime(),
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
            $data[PHOTO] = $photo_path;
        }

        $data_id = $this->hrms_timeoff_message_m->save($data);
        if ($data_id) {
            $data = $this->hrms_timeoff_message_m->get($data_id);
            $user = $this->user_m->get_by_employee_id($receiver);
            $token = $user['device_token'];
            $title = "Message received from: " . $data['SENDER_NAME'] . "[" . $data['SENDER'] . "]";
            $timeoff = $this->hrms_timeoff_title_m->get($timeoffID);
            $temp = $this->sendMessage2($receiver, "Message received from", $timeoff, 14, $data);
            $message = array(
                'ID' => $data['id'],
                'SENDER' => $data['SENDER'],
                'DATE_CREATED' => $data['DATE_CREATED'],
                'MESSAGE' => $data['MESSAGE'],
                'SENDER_PHOTO' => $data['SENDER_PHOTO'],
                'PHOTO' => $data['PHOTO'],
                'TYPE' => 14
            );
            $message_data = array_merge($timeoff, $message);
            $body = $data['MESSAGE'];
            $result =  $this->sendMessage3($token, $title, $body, $message_data);
            $flag = false;
            if (strpos($result, "\"success\":0") || $result == false || $token == null || $token == ""){
                $this->hrms_timeoff_message_m->send_message($data_id, 0);
            } else {
                $flag = true;
                $this->hrms_timeoff_message_m->send_message($data_id, 1);
            }
            if ((strpos($temp, "\"success\":0") || $temp == false) && $flag == false){
                $this->hrms_timeoff_message_m->send_message($data_id, 0);
            } else {
                $this->hrms_timeoff_message_m->send_message($data_id, 1);
            }

            $data = $this->hrms_timeoff_message_m->get($data_id);
            $this->SuccessResponse($data);
        } else {
            $this->FailedResponse('Something went wrong.');
        }
    }

    public function updateLocation()
    {
        $timeoff_id = $this->input->post('timeoff_id');
        $location = $this->input->post('location');
        $lat = $this->input->post('lat');
        $lng = $this->input->post('lng');

        // $date = date('Y-m-d H:i:s');	// 2018-12-02 16:16:19
        // $date = date('Y-m-d H:i A');	// 2018-12-02 16:17 PM
        $date = $this->currentTime();    // UTC Time

        $timeoff = $this->hrms_timeoff_title_m->get($timeoff_id);
        if ($timeoff) {
            $timeoff['location'] = $location;
            $timeoff['lat'] = $lat;
            $timeoff['lng'] = $lng;
            $timeoff['location_time'] = $date;
            $this->hrms_timeoff_title_m->save($timeoff, $timeoff_id);
            $this->SuccessResponse("Success");
        } else {
            $this->FailedResponse("There is no timeout");
        }

        //todo
    }

    public function updateLocation2()
    {
        $timeoff_id = $this->input->post('timeoff_id');
        $location = $this->input->post('location');
        $lat = $this->input->post('lat');
        $lng = $this->input->post('lng');

        // $date = date('Y-m-d H:i:s');	// 2018-12-02 16:16:19
        // $date = date('Y-m-d H:i A');	// 2018-12-02 16:17 PM
        $date = $this->currentTime();    // UTC Time

        $timeoff = $this->hrms_timeoff_title_m->get($timeoff_id);
        if ($timeoff) {
            $timeoff['location2'] = $location;
            $timeoff['lat2'] = $lat;
            $timeoff['lng2'] = $lng;
            $timeoff['location_time2'] = $date;
            $this->hrms_timeoff_title_m->save($timeoff, $timeoff_id);
            $this->SuccessResponse("Success");
        } else {
            $this->FailedResponse("There is no timeout");
        }

        //todo
    }
}
