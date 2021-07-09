<?php
defined( 'BASEPATH' ) or exit( 'No direct script access allowed' );

class Admin extends Api_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function newUser() {
        $group = $this->input->get( 'group' );
        $email = $this->input->get( 'email' );
        $password = $this->input->get( 'password' );
        $photo = $this->input->get( 'photo' );
        $empl_id = $this->input->get( 'employee_id' );

        if ( !isset( $group, $email, $password, $photo, $empl_id ) ) {
            $this->FailedResponse( 'Invalid Param' );
        }

        if ( $this->user_m->get_by_email( $email ) ) {
            $this->FailedResponse( 'This email has been registered already' );
        }
        if ( $this->user_m->get_by_employee_id( $empl_id ) ) {
            $this->FailedResponse( 'This employee id has been registered already' );
        }

        $data = array(
            GROUP_NAME => $group,
            EMAIL => $email,
            PASSWORD => $password,
            EMPLOYEE_ID => $empl_id,
            PHOTO => $photo,
            DATE_CREATED => $this->currentTime(),
        );

        $user_id = $this->user_m->save( $data );
        if ( $user_id ) {
            $user_data = $this->user_m->get( $user_id );
            $this->SuccessResponse( $user_data );
        } else {
            $this->FailedResponse( 'Something went wrong.' );
        }
    }

    public function newTrigger() {
        $reference = $this->input->get( 'reference' );
        $group = $this->input->get( 'group' );
        $title = $this->input->get( 'title' );
        $description = $this->input->get( 'description' );

        if ( !isset( $reference, $group, $title, $description ) ) {
            $this->FailedResponse( 'Invalid Param' );
        }

        if ( $this->trigger_m->get_by_reference_id( $reference ) ) {
            $this->FailedResponse( 'This reference id has been registered already' );
        }

        $data = array(
            REFERENCE_NO => $reference,
            GROUP_NAME => $group,
            TITLE => $title,
            DESCRIPTION => $description,
            DATE_CREATED => $this->currentTime(),
        );

        $data_id = $this->trigger_m->save( $data );
        if ( $data_id ) {
            $data = $this->trigger_m->get( $data_id );
            $this->SuccessResponse( $data );
        } else {
            $this->FailedResponse( 'Something went wrong.' );
        }
    }

    public function checkTrigger() {
        // $result1 = $this->checkCACTrigger();
        // $result2 = $this->checkRequisitionTrigger();
        // $result3 = $this->checkNewRequisition();
        $this->checkLeaveApproval();
        $this->checkLeaveMessage();
        $this->checkAbsentAttendance();
        $this->checkLateInAttendance();
        $this->checkAttendanceMessage();
        $this->checkLeaveStatus();
        $this->checkClaimStatus();
        $this->checkClaimMessage();
        $this->checkTimeoffMessage();
        $this->checkClaimModule();
        $this->checkTimeoff();
        $this->checkTimeOffStatus();
        // $this->SuccessResponse( array(
        //     'message1' => $result1,
        //     'message2' => $result2,
        //     'message3' => $result3,
        //     'message4' => $result4,
        //     'message5' => $result5
        // ) );
    }

    public function checkCACTrigger() {
        // return $this->sendMessage1( '', 'New trigger added', '', 0 );

        $triggers = $this->trigger_m->get_by( array(
            SEND_STATUS => '0'
        ), false );

        // var_dump( $triggers );

        if ( !$triggers || sizeof( $triggers ) == 0 ) {
            return 'No new Triggers';
        }

        foreach ( $triggers as $trigger ) {
            $data = array(
                SEND_STATUS => '1',
            );
            $this->trigger_m->update( $data, $trigger[ID] );

            $result = $this->sendMessage1( $trigger[GROUP_NAME], 'New trigger added', $trigger, 0 );
        }

        return 'Added new trigger';
    }

    public function checkRequisitionTrigger() {
        $requisitions = $this->requisition_m->get_by( array(
            STATUS => 'Rejected',
            SEND_STATUS => '0'
        ), false );

        if ( !$requisitions || sizeof( $requisitions ) == 0 ) {
            return 'No new Triggers';
        }

        foreach ( $requisitions as $requisition ) {
            $requisitionListing = $this->requisition_listing_m->get_by_reference_id( $requisition[REFERENCE_NO] );

            $data = array(
                SEND_STATUS => '1',
            );
            $this->requisition_m->update( $data, $requisition[ID] );

            // $title = 'New requisition trigger added';
            $title = '[REQUISITION FORM][SAMPLE][' . $requisition[A_CUSTOMER] . ']';
            $trigger = array(
                REFERENCE_NO => $requisition[REFERENCE_NO],
                GROUP_NAME => $requisitionListing[GROUP_NAME],
                TITLE => $title,
                DESCRIPTION => '',
                SEND_STATUS => '1',
                STATUS => 'Rejected',
                DATE_CREATED => $this->currentTime()
            );

            $this->sendMessage1( $requisitionListing[GROUP_NAME], 'New requisition trigger added', $trigger, 1 );
        }

        return 'Added new trigger';
    }

    public function checkNewRequisition() {
        $requisitions = $this->requisition_m->get_by( array(
            STATUS => 'Pending',
            SEND_STATUS => '0'
        ), false );

        if ( !$requisitions || sizeof( $requisitions ) == 0 ) {
            return 'No new requisition';
        }

        foreach ( $requisitions as $requisition ) {
            $requisitionListing = $this->requisition_listing_m->get_by_reference_id( $requisition[REFERENCE_NO] );
            $data = array(
                SEND_STATUS => '1',
            );
            $this->requisition_m->update( $data, $requisition[ID] );

            $this->sendMessage1( $requisition[GROUP_NAME], 'New requisition added', $requisitionListing, 2 );
        }

        return 'New requisition added';
    }

    public function checkLateInAttendance() {
        $attendances = $this->hrms_attendance_m->get_by( array(
            'Late_In > ' => 0,
            SEND_STATUS => '0'
        ), false );

        if ( !$attendances || sizeof( $attendances ) == 0 ) {
            return 'No late in attendance';
        }
        foreach ( $attendances as $attendance ) {
            // $data = array(
            //     SEND_STATUS => '1',
            // );
            
            // $this->hrms_attendance_m->update( $data, $attendance[ID] );
            $user = $this->user_m->get_by_employee_id( $attendance[EMPLOYEE_ID] );
            if ( empty( $user ) ) {
                continue;
            }
            $employee_title = $attendance[EMPLOYEE_NAME] . ' - Late In';
            $employee_body = $attendance[EMPLOYEE_NAME] . ' are Late';
            // $data = $user;
            $employee_data = array(
                'TYPE' => 5,
                'attendance_id' => $attendance[ID],
                'SENDER' => $user['EMPLOYEE_ID'],
                'title' => $employee_title,
                'body' => $employee_body
            );
            $managers_id = explode( ',', $user['MANAGER'] );
            
            foreach ( $managers_id as $manager_id ) {
                $manager = $this->user_m->get_by_employee_id( $manager_id );
                if(strpos($attendance['Receive_Users'], $manager[EMPLOYEE_ID]) === false) {
                    $this->sendMessage2('manager', $attendance[EMPLOYEE_NAME] . ' - Late In', $attendance, 5, $manager);
                    $token = $manager['device_token'];
                    if ( $token == '' || $token == null ) {
                        continue;
                    }
                    $this->sendMessage3( $token, $employee_title, $employee_body, $employee_data );
                }
            }
        }
        return 'Sent an attendance late in alert';
    }

    public function checkAbsentAttendance() {
        $attendances = $this->hrms_attendance_m->get_by( array(
            'TYPE' => 'Absent',
            SEND_STATUS => '0'
        ), false );

        if ( !$attendances || sizeof( $attendances ) == 0 ) {
            return 'No Absent attendance';
        }

        foreach ( $attendances as $attendance ) {
            // $data = array(
            //     SEND_STATUS => '1',
            // );
            // $this->hrms_attendance_m->update( $data, $attendance[ID] );
            $user = $this->user_m->get_by_employee_id( $attendance[EMPLOYEE_ID] );
            if ( empty( $user ) ) {
                continue;
            }

            $employee_title = $attendance[EMPLOYEE_NAME] . ' - Absent';
            $employee_body = 'You are Absent';
            // $data = $user;
            $employee_data = array(
                'TYPE' => 5,
                'attendance_id' => $attendance[ID],
                'SENDER' => $user['EMPLOYEE_ID'],
                'title' => $employee_title,
                'body' => $employee_body
            );
            $managers_id = explode( ',', $user['MANAGER'] );
            
            foreach ( $managers_id as $manager_id ) {
                $manager = $this->user_m->get_by_employee_id( $manager_id );
                if(strpos($attendance['Receive_Users'], $manager[EMPLOYEE_ID]) === false) {
                    $this->sendMessage2('manager', $attendance[EMPLOYEE_NAME] . ' - Absent', $attendance, 5, $manager);
                    $token = $manager['device_token'];
                    if ( $token == '' || $token == null ) {
                        continue;
                    }
                    $this->sendMessage3( $token, $employee_title, $employee_body, $employee_data );
                }
            }
        }

        return 'Sent an attendance absent alert';
    }

    public function checkAttendanceMessage() {
        $attendances = $this->hrms_message_m->get_attendance();
        foreach ($attendances as $key => $message) {
            $user = $this->user_m->get_by_employee_id($message['RECEIVER']);
            $this->sendMessage2($message['RECEIVER'], "Message received from", "", 3, $message);
            $token = $user['device_token'];
            $title = "Message received from: " . $message['SENDER'];
            if ( $token == '' || $token == null ) {
                continue;
            }
            $message_data = array
                (
                'ID' => $message['id'],
                'SENDER' => $message['SENDER'],
                'DATE_CREATED' => $message['DATE_CREATED'],
                'MESSAGE' => $message['MESSAGE'],
                'SENDER_PHOTO' => $message['SENDER_PHOTO'],
                'PHOTO' => $message['PHOTO'],
                'TYPE' => 3
            );
            $body = $message['MESSAGE'];
            $this->sendMessage3($token, $title, $body, $message_data);
        }
    }

    public function checkLeaveMessage() {
        $receive_users = $this->hrms_leave_message_m->get_all_not_receive_users();
        foreach ( $receive_users as $receive_user ) {
            $user = $this->user_m->get_by_employee_id( $receive_user[RECEIVER] );
            $token = $user['device_token'];
            $leave = $this->hrms_leave_application_m->get( $receive_user[LEAVE_ID] );
            $temp = $this->sendMessage2($user, "Message received from", $leave, 8, $receive_user);
            if ( $token == '' || $token == null ) {
                continue;
            }
            $title = '[Leave Chat]';
            if ( empty( $leave ) ) {
                continue;
            }
            $body = 'Message received from '.$receive_user[SENDER];
            $data = array(
                'TYPE' => 21,
            );
            $message_data = array_merge( $leave, $data );
            $this->sendMessage3( $token, $title, $body, $message_data );
        }
        return 'checkLeaveMessage';
    }

    public function checkTimeoffMessage() {
        $receive_users = $this->hrms_timeoff_message_m->get_all_not_receive_users();
        foreach ( $receive_users as $receive_user ) {
            $user = $this->user_m->get_by_employee_id( $receive_user[RECEIVER] );
            $timeoff = $this->hrms_timeoff_title_m->get( $receive_user[TIMEOFF_ID] );
            $this->sendMessage2($user, "Message received from", $timeoff, 14, $receive_user);
            $token = $user['device_token'];
            if ( $token == '' || $token == null ) {
                continue;
            }
            $title = '[Gate Pass Chat]';
            if ( empty( $timeoff ) ) {
                continue;
            }
            $body = 'Message received from '.$receive_user[SENDER];
            $data = array(
                'TYPE' => 22,
            );
            $message_data = array_merge( $timeoff, $data );
            $this->sendMessage3( $token, $title, $body, $message_data );
        }
        return 'checkTimeoffMessage';
    }

    public function checkClaimMessage() {
        $receive_users = $this->hrms_claim_message_m->get_all_not_receive_users();
        foreach ( $receive_users as $receive_user ) {
            $user = $this->user_m->get_by_employee_id( $receive_user[RECEIVER] );
            if ( empty( $user ) ) {
                continue;
            }
            $claim = $this->hrms_claim_title_m->get( $receive_user[CLAIM_ID] );
            $this->sendMessage2( $user, 'Message received from', $claim, 11, $receive_user );
            $token = $user['device_token'];
            if ( $token == '' || $token == null ) {
                continue;
            }
            $title = '[Claim Chat]';
            if ( empty( $claim ) ) {
                continue;
            }
            $body = 'Message received from '.$receive_user[SENDER];
            $data = array(
                'TYPE' => 23,
            );
            $message_data = array_merge( $claim, $data );
            $this->sendMessage3( $token, $title, $body, $message_data );
        }
        return 'checkClaimMessage';
    }

    public function checkLeaveApproval() {
        $data = $this->hrms_leave_application_m->get_by_status();
        foreach ( $data as $approval ) {
            $managers_id = explode( ',', $approval[MANAGER_ID] );
            foreach ( $managers_id as $manager_id ) {
                $manager = $this->user_m->get_by_employee_id_and_group_name_HR( $manager_id );
                if ( empty( $manager ) ) {
                    continue;
                }
                $token = $manager['device_token'];
                $this->sendMessage2( 'Leave_Manager', 'leave', $approval, 7, $manager );
                if ( $token == '' || $token == null ) {
                    continue;
                }
                $title = '[Leave]['.$approval['EMPLOYEE_NAME'].' – '.$approval['EMPLOYEE_ID'].']['.$approval['Leave_Type'].']['.$approval['Date_Start'].']['.$approval['Date_End'].']';
                $body = $approval['Date_Created'];

                $approval_data = $this->hrms_leave_application_m->get( $approval['id'] );
                if ( empty( $approval_data ) ) {
                    continue;
                }
                $message = array
                (
                    'TYPE' => 6
                );
                $message_data = array_merge( $approval_data, $message );
                $this->sendMessage3( $token, $title, $body, $message_data );
            }
        }
        return 'checkLeaveApproval';
    }

    public function checkLeaveStatus() {
        $data = $this->hrms_leave_application_m->get_by_receive_status();
        foreach ( $data as $approval ) {
            $manager = $this->user_m->get_by_employee_id_and_group_name_HR( $approval['EMPLOYEE_ID'] );
            if ( empty( $manager ) ) {
                continue;
            }
            $title = '';
            if ( $approval['Leave_Status'] == 'Approved' ) {
                $title = '[Leave approved]';
            } else {
                $title = '[Leave rejected]';
            }

            $this->sendMessage2( '', $title, $approval, 9, null );

            $token = $manager['device_token'];
            if ( $token == '' || $token == null ) {
                continue;
            }

            $body = '[' . $approval['Date_Start'] . '-' . $approval['Date_End'] . ']' . $approval['Leave_Type'];
            $message = array
            (
                'TYPE' => 9
            );
            $message_data = array_merge( $approval, $message );
            $this->sendMessage3( $token, $title, $body, $message_data );
        }
        return 'checkLeaveStatus';
    }

    public function checkClaimModule() {
        $pendingList = $this->hrms_claim_title_m->get_list_pending();
        foreach ( $pendingList as $claim ) {
            $managers_id = explode( ',', $claim[MANAGER_ID] );
            $total_amount = $this->hrms_claim_details_m->get_total_amount( $claim[EMPLOYEE_ID], $claim[ID] );
            $claim[TOTAL_AMOUNT] = $total_amount;
            foreach ( $managers_id as $manager_id ) {
                $manager = $this->user_m->get_by_employee_id_and_group_name_HR( $manager_id );
                $this->sendMessage2( 'Claim_Manager', 'claim', $claim, 10, $manager );
                if ( empty( $manager ) ) {
                    continue;
                }
                $token = $manager['device_token'];

                if ( $token == '' || $token == null ) {
                    continue;
                }
                $title = '';
                $body = '';
                $claim_manager_level = $claim['claim_manager_level'];
                if ( $claim_manager_level == null ) {
                    $title = '[Claim][' . $claim['EMPLOYEE_NAME'] . ' – ' . $claim['EMPLOYEE_ID'] . '][' . $claim['Type'] . '][' . $claim[TOTAL_AMOUNT] . ']';
                } else {
                    $title = '[Level ' . $claim_manager_level . '][Claim][' . $claim['EMPLOYEE_NAME'] . ' – ' . $claim['EMPLOYEE_ID'] . '][' . $claim['Type'] . '][' . $claim[TOTAL_AMOUNT] . ']';
                }
                $body = $claim['Date_Start'] . ' - ' . $claim['Date_End'];

                $message = array(
                    'TYPE' => 10
                );
                $message_data = array_merge( $claim, $message );
                $this->sendMessage3( $token, $title, $body, $message_data );
            }
        }
        return 'checkClaimModule';
    }

    public function checkClaimStatus() {
        $pendingList = $this->hrms_claim_title_m->get_by_receive_status();
        foreach ( $pendingList as $claim ) {
            $user = $this->user_m->get_by_employee_id( $claim[EMPLOYEE_ID] );
            $token = $user['device_token'];
            $title = 'Claim Rejected';
            if ( $claim[STATUS] == 'Approved' ) {
                $title = 'Claim Approved';
            }
            $this->sendMessage2( '', $title, $claim, 12, null );
            if ( $token == '' || $token == null ) {
                continue;
            }
            $total_amount = $this->hrms_claim_details_m->get_total_amount( $claim[EMPLOYEE_ID], $claim[ID] );
            $claim[TOTAL_AMOUNT] = $total_amount;

            $body = $claim['Travel_Title'];
            $message = array(
                'TYPE' => 12
            );

            $message_data = array_merge( $claim, $message );
            $this->sendMessage3( $token, $title, $body, $message_data );
        }
        return 'checkClaimStatus';
    }

    public function checkTimeOff() {
        $pendingList = $this->hrms_timeoff_title_m->get_list_pending();
        foreach ( $pendingList as $data ) {
            if ( empty( $data ) ) {
                continue;
            }
            $managers_id = explode( ',', $data[MANAGER_ID] );
            foreach ( $managers_id as $manager_id ) {
                $manager = $this->user_m->get_by_employee_id_and_group_name_HR( $manager_id );
                $this->sendMessage2( 'TimeOff_Manager', 'gate', $data, 13, $manager );
                $token = $manager['device_token'];
                if ( $token == '' || $token == null ) {
                    continue;
                }
                $title = '[GatePass][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[START_HOUR] . '][' . $data[END_HOUR] . ']';
                $body = $data['Date'];
                $message = array(
                    'TYPE' => 13
                );
                $message_data = array_merge( $data, $message );

                $this->sendMessage3( $token, $title, $body, $message_data );
            }
        }
        return 'checkTimeOff';
    }

    public function checkTimeOffStatus() {
        $pendingList = $this->hrms_timeoff_title_m->get_by_receive_status();
        foreach ( $pendingList as $timeOff ) {
            $user = $this->user_m->get_by_employee_id( $timeOff[EMPLOYEE_ID] );
            $token = $user['device_token'];
            $title = 'Gate Pass Rejected';
            if ( $timeOff[STATUS] == 'Approved' ) {
                $title = 'Gate Pass Approved';
            }
            $this->sendMessage2('', $title, $timeOff, 15, null);
            if ( $token == '' || $token == null ) {
                continue;
            }
            $body = $timeOff['Timeoff_Title'];
            $message = array(
                'TYPE' => 15
            );
            $message_data = array_merge( $timeOff, $message );
            $this->sendMessage3( $token, $title, $body, $message_data );
        }
        return 'checkTimeOffStatus';
    }
}
