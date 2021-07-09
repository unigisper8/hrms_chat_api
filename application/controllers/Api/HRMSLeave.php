<?php
defined('BASEPATH') or exit('No direct script access allowed');

class HRMSLeave extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getLeave()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_leave_m->get_by_employee_id($employeeID);

        $this->SuccessResponse($data);
    }

    /*** HRMS (Second app) ***/

    public function getLeaveTypeList()
    {
        $data = $this->hrms_leave_type_m->get_list();

        $this->SuccessResponse($data);
    }

    public function getLeaveApplicationList()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_leave_application_m->get_by_employee_id($employeeID);

        $result = $this->updateManagerName($data, $employeeID);

        $this->SuccessResponse($result);
    }


    public function submitLeaveApplication()
    {
        $employeeID = $this->input->post('employeeID');
        $startDate = $this->input->post('Date_Start');
        $endDate = $this->input->post('Date_End');
        $halfDay = $this->input->post('Half_Day');
        $session = $this->input->post('Session');
        $leaveType = $this->input->post('Leave_Type');
        $reason = $this->input->post('Reason');

        if (
            !isset($employeeID)
            || !isset($startDate)
            || !isset($endDate)
            || !isset($halfDay)
            || !isset($leaveType)
            || !isset($reason)
        )
            $this->FailedResponse('Invalid Param');

        if ($leaveType == 0 || strtolower($leaveType) == 'annual leave') {
            $output_format = 'Y-m-d';

            $emergency = $this->hrms_emergency_leave_m->get(null, true);
            if ($emergency) {
                $days = $emergency['value'];

                $current = strtotime($startDate);
                $current = strtotime('-' . $days . ' days', $current);
                $emergencyDate = date($output_format, $current);
                $today = date($output_format);
                if ($emergencyDate < $today)
                    $this->FailedResponse("Annual Leave needs to apply $days days earlier");
            }
        }

        if ($this->hrms_leave_application_m->check_duplicated($employeeID, $startDate, $endDate)) {
            $this->FailedResponse("Duplicate entry");
        }

        $now = time(); // now
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        $days = round(($end - $start) / (60 * 60 * 24));
        if ($halfDay == true)
            $days += 0.5;
        if ($leaveType != 2 && strtolower($leaveType) != 'unpaid leave') {
            $balance = $this->hrms_leave_entitlement_m->get_balance($employeeID, $leaveType);
            //$carry = $this->hrms_leave_entitlement_m->get_carry($employeeID, $leaveType);
            $data = $this->hrms_leave_application_m->get_by_employee_id($employeeID);
            $result = $this->updateManagerName($data, $employeeID);
            $leave_days = 0;
           
            foreach($result  as $key => $item){
                if($item['Leave_Type'] == $leaveType){
                    $date_start = $item['Date_Start'];
                    $date_end = $item['Date_End'];
                    $isHalfDay = $item['Half_Day'];
                    $start = strtotime($date_start);
                    $end = strtotime($date_end);
                    $leave_days += round(($end - $start) / (60 * 60 * 24));
                    if($isHalfDay == "true"){
                        $leave_days += 0.5;
                    }
                }
            }
            $balance = $balance - $leave_days;
            if ($balance < $days) {
                $this->FailedResponse("Leave Balance Not Sufficient");
            }
        }
        
        $employee = $this->user_m->get_by_employee_id($employeeID);
        $manager_name = $this->getLeaveManagerName($employeeID);

        $data = array(
            EMPLOYEE_ID => $employeeID,
            EMPLOYEE_NAME => $employee[EMPLOYEE_NAME],
            MANAGER_ID => $employee[LEAVE_MANAGER],
            MANAGER_ID_2 => $employee[LEAVE_MANAGER_2],
            MANAGER_ID_3 => $employee[LEAVE_MANAGER_3],
            MANAGER_NAME => $manager_name,
            DATE_START => $startDate,
            DATE_END => $endDate,
            HALF_DAY => $halfDay,
            SESSION => $session,
            LEAVE_TYPE => $leaveType,
            REASON => $reason,
            LEAVE_STATUS => 'Pending',
            LEAVE_PENDING_STATUS => 1, // Manager 1
            DATE_CREATED => $this->currentTime()
        );

        $data_id = $this->hrms_leave_application_m->save($data);
        if ($data_id) {
            $data = $this->hrms_leave_application_m->get($data_id);
            $user = $this->user_m->get_by_employee_id($employeeID);

            // Send alert to manager
            $this->sendMessage2('Leave_Manager', "Success", $data, 7, $user);

            $token = $user['device_token'];
            $title = '[Leave]['.$data['EMPLOYEE_NAME'].' – '.$data['EMPLOYEE_ID'].']['.$data['Leave_Type'].']['.$data['Date_Start'].']['.$data['Date_End'].']';
            $body = $data['Date_Created'];
            $message = array
            (
                'TYPE' => 6
            );
            $message_data = array_merge($data, $message);
            $this->sendMessage3($token, $title, $body, $message_data);

            // $fields = array(
            //     ID => $data_id
            // );
            // $this->post(BASE_URL . "LM_LEAVE_ENTITLEMENT_Mobile.aspx?id=" . $data_id, $fields); // ???

            $this->SuccessResponse($data);
        } else {
            $this->FailedResponse('Something went wrong.');
        }
    }

    public function getLeaveSummaryList()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_leave_entitlement_m->get_by_employee_id($employeeID);

        $this->SuccessResponse($data);
    }

    public function getLeaveSummaryDetailList()
    {
        $employeeID = $this->input->post('employeeID');
        $leaveType = $this->input->post('Leave_Type');

        if (!isset($employeeID) || !isset($leaveType))
            $this->FailedResponse('Invalid Param');

        $data = $this->hrms_leave_application_m->get_by_leave_type($employeeID, $leaveType);

        $result = $this->updateManagerName($data, $employeeID);

        $this->SuccessResponse($result);
    }

    public function deleteLeave()
    {
        $employeeID = $this->input->post('employeeID');
        $leaveID = $this->input->post('Leave_ID');

        if (!isset($employeeID) || !isset($leaveID))
            $this->FailedResponse('Invalid Param');

        $result = $this->hrms_leave_application_m->delete_pending($leaveID);
        if ($result) {
            // $fields = array(
            //     ID => $leaveID
            // );
            // $this->post(BASE_URL . "LM_Leave_Delete.aspx?id=" . $leaveID, $fields); // ???
            $this->SuccessResponse("Success");
        } else {
            $this->FailedResponse("Failed");
        }
    }

    public function getLeaveApprovalList()
    {
        $employeeID = $this->input->post('employeeID');

        if (!isset($employeeID))
            $this->FailedResponse('Invalid Param');

        $users = $this->user_m->get_users_by_leave_manager($employeeID);
        $data = $this->hrms_leave_application_m->get_by_employee_ids($users);
        $this->hrms_leave_application_m->set_status_by_employee_ids($users);
        $result = $this->updateManagerName($data, $employeeID);

        $this->SuccessResponse($result);
    }

    public function updateLeaveStatus()
    {
        /*
         $leaveID = $this->input->post('Leave_ID');
         $status = $this->input->post('Leave_Status');
         if (!isset($leaveID, $status))
         $this->FailedResponse('Invalid Param');
         
         if ($status == 'Approved') {
         $fields = array(
         ID => $leaveID
         );
         $this->post(BASE_URL . "LM_Approved_Mobile.aspx?id=" . $leaveID, $fields);    // ???
         }
         $current_time = $this->currentTime();
         $result = $this->hrms_leave_application_m->update_status($leaveID, $status, $current_time);
         if ($result) {
         $data = $this->hrms_leave_application_m->get($leaveID);
         if ($status == 'Approved') {
         $this->sendMessage2('', "Leave approved", $data, 9, null);
         } else {
         $this->sendMessage2('', "Leave rejected", $data, 9, null);
         }
         $this->SuccessResponse("Success");
         } else {
         $this->FailedResponse("Failed");
         } */

        $leaveID = $this->input->post('Leave_ID');
        $status = $this->input->post('Leave_Status');
        if (!isset($leaveID, $status))
            $this->FailedResponse('Invalid Param');

        // check 3 level leave managers and send alerts to high level managers
        if ($status == 'Approved') {
            $current_time = $this->currentTime();
            $data = $this->hrms_leave_application_m->get($leaveID);
            $employeeID = $data['EMPLOYEE_ID'];

            // New CR
            $managerLevel = $data['leave_approve_manager_level'];
            $id = $data['id'];
            $managerStatus = 2; //Approve
            $dataManager = $this->user_m->get_by_employee_id_and_group_name_HR($employeeID);

            if (
                $managerLevel == 1 &&
                $dataManager['leave_approve_manager_2'] != 'N/A'
            ) {
                $employee = $this->user_m->get_by_employee_id_and_group_name_HR($dataManager['leave_approve_manager_2']);
                $this->sendMessage2('', "Waiting for approval", $data, 7, $employee);
                $leave_manager_level = $data['leave_manager_level'];
                $leaveManagers = '';
                if ($leave_manager_level == 1) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER']);
                } elseif ($leave_manager_level == 2) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_2']);
                } elseif ($leave_manager_level == 3) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_3']);
                }
                foreach ($leaveManagers as $key => $manager) {
                    $employee = $this->user_m->get_by_employee_id_and_group_name_HR($manager);
                    $token = $employee['device_token'];
                    $title = "[Leave][" . $data['EMPLOYEE_NAME'] . " – " . $data['EMPLOYEE_ID'] . "][" . $data['Leave_Type'] . "][" . $data['Date_Start'] . "][" . $data['Date_End'] . "]";
                    $body = "";
                    $body = $data['Date_Created'];
                    $message = array(
                        'TYPE' => 7
                    );
                    $message_data = array_merge($data, $message);
                    $this->sendMessage3($token, $title, $body, $message_data);
                }
                $this->SuccessResponse("Success");
                $this->hrms_leave_application_m->update_leave_manager_level($id, $managerStatus);
                $this->SuccessResponse("Waiting for next approval manager 2");
            } else if ($managerLevel == 2 && $dataManager['leave_approve_manager_3'] != 'N/A') {
                $employee = $this->user_m->get_by_employee_id_and_group_name_HR($dataManager['leave_approve_manager_3']);
                $this->sendMessage2('', "Waiting for approval", $data, 7, $employee);
                $leave_manager_level = $data['leave_manager_level'];
                $leaveManagers = '';
                if ($leave_manager_level == 1) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER']);
                } elseif ($leave_manager_level == 2) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_2']);
                } elseif ($leave_manager_level == 3) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_3']);
                }
                foreach ($leaveManagers as $key => $manager) {
                    $employee = $this->user_m->get_by_employee_id_and_group_name_HR($manager);
                    $token = $employee['device_token'];
                    $title = "[Leave][" . $data['EMPLOYEE_NAME'] . " – " . $data['EMPLOYEE_ID'] . "][" . $data['Leave_Type'] . "][" . $data['Date_Start'] . "][" . $data['Date_End'] . "]";
                    $body = "";
                    $body = $data['Date_Created'];
                    $message = array(
                        'TYPE' => 7
                    );
                    $message_data = array_merge($data, $message);
                    $this->sendMessage3($token, $title, $body, $message_data);
                }
                $this->SuccessResponse("Success");
                $this->hrms_leave_application_m->update_leave_manager_level($id, $managerStatus);
                $this->SuccessResponse("Waiting for next approval manager 3");
            } else if ($managerLevel == 3 && $dataManager['leave_approve_manager_4'] != 'N/A') {
                $employee = $this->user_m->get_by_employee_id_and_group_name_HR($dataManager['leave_approve_manager_4']);
                $this->sendMessage2('', "Waiting for approval", $data, 7, $employee);
                $leave_manager_level = $data['leave_manager_level'];
                $leaveManagers = '';
                if ($leave_manager_level == 1) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER']);
                } elseif ($leave_manager_level == 2) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_2']);
                } elseif ($leave_manager_level == 3) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_3']);
                }
                foreach ($leaveManagers as $key => $manager) {
                    $employee = $this->user_m->get_by_employee_id_and_group_name_HR($manager);
                    $token = $employee['device_token'];
                    $title = "[Leave][" . $data['EMPLOYEE_NAME'] . " – " . $data['EMPLOYEE_ID'] . "][" . $data['Leave_Type'] . "][" . $data['Date_Start'] . "][" . $data['Date_End'] . "]";
                    $body = "";
                    $body = $data['Date_Created'];
                    $message = array(
                        'TYPE' => 7
                    );
                    $message_data = array_merge($data, $message);
                    $this->sendMessage3($token, $title, $body, $message_data);
                }
                $this->SuccessResponse("Success");
                $this->hrms_leave_application_m->update_leave_manager_level($id, $managerStatus);
                $this->SuccessResponse("Waiting for next approval manager 4");
            } else if ($managerLevel == 4 && $dataManager['leave_approve_manager_5'] != 'N/A') {
                $employee = $this->user_m->get_by_employee_id_and_group_name_HR($dataManager['leave_approve_manager_5']);
                $this->sendMessage2('', "Waiting for approval", $data, 7, $employee);
                $leave_manager_level = $data['leave_manager_level'];
                $leaveManagers = '';
                if ($leave_manager_level == 1) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER']);
                } elseif ($leave_manager_level == 2) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_2']);
                } elseif ($leave_manager_level == 3) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_3']);
                }
                foreach ($leaveManagers as $key => $manager) {
                    $employee = $this->user_m->get_by_employee_id_and_group_name_HR($manager);
                    $token = $employee['device_token'];
                    $title = "[Leave][" . $data['EMPLOYEE_NAME'] . " – " . $data['EMPLOYEE_ID'] . "][" . $data['Leave_Type'] . "][" . $data['Date_Start'] . "][" . $data['Date_End'] . "]";
                    $body = "";
                    $body = $data['Date_Created'];
                    $message = array(
                        'TYPE' => 7
                    );
                    $message_data = array_merge($data, $message);
                    $this->sendMessage3($token, $title, $body, $message_data);
                }
                $this->SuccessResponse("Success");
                $this->hrms_leave_application_m->update_leave_manager_level($id, $managerStatus);
                $this->SuccessResponse("Waiting for next approval manager 5");
            }

            $employee = $this->user_m->get_by_employee_id_and_group_name_HR($employeeID);
            $this->hrms_leave_application_m->update_pending_level($leaveID, $employee);
            $data = $this->hrms_leave_application_m->get($leaveID);
            $high_manager_level = $data[LEAVE_PENDING_STATUS];


            if ($high_manager_level <= 1) {
                $this->sendMessage2('', "Leave approved", $data, 7, $employee);
                $leave_manager_level = $data['leave_manager_level'];
                $leaveManagers = '';
                if ($leave_manager_level == 1) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER']);
                } elseif ($leave_manager_level == 2) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_2']);
                } elseif ($leave_manager_level == 3) {
                    $leaveManagers = explode(',', $employee['LEAVE_MANAGER_3']);
                }
                foreach ($leaveManagers as $key => $manager) {
                    $employee = $this->user_m->get_by_employee_id_and_group_name_HR($manager);
                    $token = $employee['device_token'];
                    $title = "[Leave][" . $data['EMPLOYEE_NAME'] . " – " . $data['EMPLOYEE_ID'] . "][" . $data['Leave_Type'] . "][" . $data['Date_Start'] . "][" . $data['Date_End'] . "]";
                    $body = "";
                    $body = $data['Date_Created'];
                    $message = array(
                        'TYPE' => 7
                    );
                    $message_data = array_merge($data, $message);
                    $this->sendMessage3($token, $title, $body, $message_data);
                }
                $this->SuccessResponse("Success");
            } else {
                $result = $this->hrms_leave_application_m->update_status($leaveID, $status, $current_time);
                if ($result) {
                    if ($status == 'Approved') {
                        $this->sendMessage2('', "Leave approved", $data, 9, null);
                        $token = $employee['device_token'];
                        $title = "[Leave approved]";
                        $body = "[" . $data['Date_Start'] . "-" . $data['Date_End'] . "]" . $data['Leave_Type'];
                        $message = array(
                            'TYPE' => 9
                        );
                        $message_data = array_merge($data, $message);
                        $this->sendMessage3($token, $title, $body, $message_data);
                    } else {
                        $token = $employee['device_token'];
                        $title = "[Leave rejected]";
                        $body = "[" . $data['Date_Start'] . "-" . $data['Date_End'] . "]" . $data['Leave_Type'];
                        $message = array(
                            'TYPE' => 9
                        );
                        $message_data = array_merge($data, $message);
                        $this->sendMessage3($token, $title, $body, $message_data);
                        $this->sendMessage2('', "Leave rejected", $data, 9, null);
                    }

                    $fields = array(
                        ID => $leaveID
                    );
                    // $this->post(BASE_URL . "LM_Approved_Mobile.aspx?id=" . $leaveID, $fields); // ???

                    $this->SuccessResponse("Success");
                } else {
                    $this->FailedResponse("Failed");
                }
            }
        } else {
            // New CR
            $data = $this->hrms_leave_application_m->get($leaveID);
            $employeeID = $data['EMPLOYEE_ID'];

            $id = $data['id'];
            $managerStatus = 3; //Reject
            $this->hrms_leave_application_m->update_leave_manager_level($id, $managerStatus);

            $current_time = $this->currentTime();
            $result = $this->hrms_leave_application_m->update_status($leaveID, $status, $current_time);
            $employee = $this->user_m->get_by_employee_id_and_group_name_HR($employeeID);
            $token = $employee['device_token'];
            $title = "[Leave rejected]";
            $body = "[" . $data['Date_Start'] . "-" . $data['Date_End'] . "]" . $data['Leave_Type'];
            $message = array(
                'TYPE' => 9
            );
            $message_data = array_merge($data, $message);

            $this->sendMessage3($token, $title, $body, $message_data);
            $this->sendMessage2('', "Leave rejected", $data, 9, null);
            $this->SuccessResponse("Success");
        }
    }
    public function setLeaveReceived() {
        $leaveID = $this->input->post('leaveID');
            $this->hrms_leave_application_m->leave_received($leaveID);
    }
    public function getMessageList()
    {
        $employeeID = $this->input->post('employee');
        $userID = $this->input->post('userID');
        $leaveID = $this->input->post('leaveID');

        if (!isset($employeeID, $leaveID)) {
            $this->FailedResponse('Invalid Param');
        }
        $this->hrms_leave_message_m->receive_message($employeeID, $leaveID, 1);
        $data_array = $this->hrms_leave_message_m->get_list($employeeID, $userID, $leaveID);

        $this->SuccessResponse($data_array);
    }

    public function refreshLeaveChat(){
        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');
        $leaveID = $this->input->post('leaveID');
        $this->hrms_leave_message_m->receive_message($sender, $leaveID, 1);
        $count = $this->hrms_leave_message_m->get_message_status($sender, $receiver, $leaveID);
        echo json_encode($count);
        die();
    }

    public function newMessage()
    {
        $sender = $this->input->post('sender');
        $receiver = $this->input->post('receiver');
        $leaveID = $this->input->post('leaveID');
        $message = $this->input->post('message');
        $photo = $this->input->post('photo');
        $type = $this->input->post('type');
        $photo_path = '';
        if ($photo != '') {
            $photo_path = UploadImage($photo, $type, 'leave/');
            if (!isset($sender) || !isset($receiver)) {
                $this->FailedResponse('Invalid Param');
            }
        }

        $data = array(
            SENDER => $sender,
            RECEIVER => $receiver,
            MESSAGE => $message,
            LEAVE_ID => $leaveID,
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
            $data[PHOTO] = $photo_path;
        }

        $data_id = $this->hrms_leave_message_m->save($data);
        if ($data_id) {
            $data = $this->hrms_leave_message_m->get($data_id);
            $user = $this->user_m->get_by_employee_id($receiver);
            $token = $user['device_token'];
            $title = "Message received from: ". $data['SENDER_NAME'] ."[". $data['SENDER']."]";
            $leave = $this->hrms_leave_application_m->get($leaveID);
            $leave[MANAGER_NAME] = $this->getLeaveManagerName($leave[EMPLOYEE_ID]);

            $message = array(
                'ID' => $data['id'],
                'SENDER' => $data['SENDER'],
                'DATE_CREATED' => $data['DATE_CREATED'],
                'MESSAGE' => $data['MESSAGE'],
                'SENDER_PHOTO' => $data['SENDER_PHOTO'],
                'PHOTO' => $data['PHOTO'],
                'send_status' => $data['send_status'],
                'receive_status' => $data['receive_status'],
                'TYPE' => 8
            );
            $message_data = array_merge($leave, $message);
            $body = $data['MESSAGE'];
            $temp = $this->sendMessage2($receiver, "Message received from", $leave, 8, $data);
            $result = "";
            if($token != '' && $token != null) {
                $result =  $this->sendMessage3($token, $title, $body, $message_data);
            }
            $flag = false;
            if (strpos($result, "\"success\":0") || $result == false || $token == null || $token == ""){
                $this->hrms_leave_message_m->send_message($data_id, 0);
            } else {
                $flag = true;
                $this->hrms_leave_message_m->send_message($data_id, 1);
            }
            if ((strpos($temp, "\"success\":0") || $temp == false) && $flag == false){
                $this->hrms_leave_message_m->send_message($data_id, 0);
            } else {
                $this->hrms_leave_message_m->send_message($data_id, 1);
            }
            $data = $this->hrms_leave_message_m->get($data_id);
            $this->SuccessResponse($data);
        } else {
            $this->FailedResponse('Something went wrong.');
        }
    }

    function updateManagerName($arr_leave_application, $employeeID)
    {
        $result = array();
        foreach ($arr_leave_application as $item) {
            $item[MANAGER_NAME] = $this->getLeaveManagerName($employeeID);
            $result[] = $item;
        }
        return $result;
    }

    function getLeaveManagerName($employeeID)
    {
        $employee = $this->user_m->get_by_employee_id($employeeID);
        $arr_manager_name = array();
        $arr_manager_ids = explode(",", $employee[LEAVE_MANAGER]);
        foreach ($arr_manager_ids as $id) {
            $manager = $this->user_m->get_by_employee_id($id);
            $arr_manager_name[] = $manager[EMPLOYEE_NAME];
        }
        $manager_name = implode(", ", $arr_manager_name);
        return $manager_name;
    }
}
