<?php
defined( 'BASEPATH' ) or exit( 'No direct script access allowed' );

class HRMSClaim extends Api_Controller {
    public function __construct() {
        parent::__construct();
    }

    /*** HRMS ( Second app ) ***/

    public function getGstList() {
        $data = $this->hrms_gst_m->get();

        $this->SuccessResponse( $data );
    }

    public function getClaimTypeList() {
        $data = $this->hrms_claim_type_m->get_list();

        $this->SuccessResponse( $data );
    }

    public function getClaimDetailTypeList() {
        $employeeID = $this->input->post( 'employeeID' );

        if ( !isset( $employeeID ) )
        $this->FailedResponse( 'Invalid Param' );

        $data = $this->hrms_claim_detail_type_m->get_list( $employeeID );

        $this->SuccessResponse( $data );
    }

    public function getClaimList() {
        $employeeID = $this->input->post( 'employeeID' );

        if ( !isset( $employeeID ) )
        $this->FailedResponse( 'Invalid Param' );

        $data = $this->hrms_claim_title_m->get_list_except_approved( $employeeID );

        $this->SuccessResponse( $data );
    }

    public function submitClaim() {
        $employeeID = $this->input->post( 'employeeID' );
        $title = $this->input->post( 'title' );
        $type = $this->input->post( 'type' );
        $country = $this->input->post( 'country' );
        $startDate = $this->input->post( 'Date_Start' );
        $endDate = $this->input->post( 'Date_End' );
        $description = $this->input->post( 'description' );
        $project_name = $this->input->post( 'project_name' );
        $project_type = $this->input->post( 'project_type' );
        $contact = $this->input->post( 'contact' );

        if (
            !isset( $employeeID )
            || !isset( $title )
            || !isset( $type )
            || !isset( $country )
            || !isset( $startDate )
            || !isset( $endDate )
            || !isset( $description )
            || !isset( $project_name )
        )
        $this->FailedResponse( 'Invalid Param' );

        if ( $this->hrms_claim_title_m->check_duplicated( $employeeID, $title, $type, $country, $startDate, $endDate, $description, $project_name ) ) {
            $this->FailedResponse( 'Duplicate entry' );
        }

        $employee = $this->user_m->get_by_employee_id( $employeeID );
        $manager = $this->user_m->get_by_employee_id_and_group_name_HR( $employee[CLAIM_MANAGER] );

        $data = array(
            EMPLOYEE_ID     => $employeeID,
            EMPLOYEE_NAME   => $employee[EMPLOYEE_NAME],
            MANAGER_ID      => $employee[CLAIM_MANAGER],
            // MANAGER_NAME => $manager[EMPLOYEE_NAME],
            TRAVEL_TITLE    => $title,
            TYPE            => $type,
            COUNTRY         => $country,
            DATE_START      => $startDate,
            DATE_END        => $endDate,
            DESCRIPTION     => $description,
            PROJECT_NAME    => $project_name,
            PROJECT_TYPE    => $project_type,
            CONTACT         => $contact,
            STATUS             => 'Save',
            DATE_CREATED     => $this->currentTime()
        );

        $data_id = $this->hrms_claim_title_m->save( $data );
        if ( $data_id ) {
            $claim = $this->hrms_claim_title_m->get( $data_id );
            $total_amount = $this->hrms_claim_details_m->get_total_amount( $employeeID, $data_id );
            $claim[TOTAL_AMOUNT] = $total_amount;

            // Send alert to manager
            // $this->sendMessage2( 'Claim_Manager', 'Success', $data, 10, $employee );
            $user = $this->user_m->get_by_employee_id( $employeeID );

            // Send alert to manager
            // $this->sendMessage2( 'Claim_Manager', 'Success', $claim, 10, $user );
            $claim_manager_level = $claim['claim_manager_level'];
            $claimManagers = explode( ',', $user['CLAIM_MANAGER'] );
            // foreach ( $claimManagers as $key => $manager ) {
            //     $user = $this->user_m->get_by_employee_id_and_group_name_HR( $manager );
            //     $token = $user['device_token'];
            //     $title = '';
            //     $body = '';
            //     if ( $claim_manager_level == null ) {
            //         $title = '[Claim][' . $claim['EMPLOYEE_NAME'] . ' – ' . $claim['EMPLOYEE_ID'] . '][' . $claim['Type'] . '][' . $claim[TOTAL_AMOUNT] . ']';
            //     } else {
            //         $title = '[Level ' . $claim_manager_level . '][Claim][' . $claim['EMPLOYEE_NAME'] . ' – ' . $claim['EMPLOYEE_ID'] . '][' . $claim['Type'] . '][' . $claim[TOTAL_AMOUNT] . ']';
            //     }
            //     $body = $claim['Date_Start'] . ' - ' . $claim['Date_End'];

            //     $message = array(
            //         'TYPE' => 10
            //     );
            //     $message_data = array_merge( $claim, $message );
            //     $this->sendMessage3( $token, $title, $body, $message_data );
            // }
            $this->SuccessResponse( $data );
        } else {
            $this->FailedResponse( 'Something went wrong.' );
        }
    }

    public function deleteClaim() {
        $employeeID = $this->input->post( 'employeeID' );
        $claimID = $this->input->post( 'Claim_ID' );

        if ( !isset( $employeeID ) || !isset( $claimID ) )
        $this->FailedResponse( 'Invalid Param' );

        $data = $this->hrms_claim_details_m->get_by_claim_id( $employeeID, $claimID );
        if ( $data != null && sizeof( $data ) > 0 )
        $this->FailedResponse( 'Failed to delete as breakdown records exist.' );

        $pending = $this->hrms_claim_title_m->check_can_delete( $claimID );
        if ( !$pending ) {
            $this->FailedResponse( 'Failed as it is not in pending status.' );
        }

        $this->hrms_claim_title_m->delete( $claimID );
        // $fields = array(
        //     ID => $claimID
        // );
        // $this->post( 'LM_CLAIM_MOB.aspx', $fields );
        // ???
        $this->SuccessResponse( 'Success' );
    }

    public function getClaimModuleList() {
        $employeeID = $this->input->post( 'employeeID' );
        $claimID = $this->input->post( 'Claim_ID' );

        if ( !isset( $employeeID ) || !isset( $claimID ) )
        $this->FailedResponse( 'Invalid Param' );

        $this->hrms_claim_title_m->update_send_status( $claimID );
        $data = $this->hrms_claim_details_m->get_by_claim_id( $employeeID, $claimID );

        $this->SuccessResponse( $data );
    }

    public function submitClaimModule() {
        $employeeID     = $this->input->post( 'employeeID' );
        $claimID        = $this->input->post( 'Claim_ID' );
        $claimDetailID  = $this->input->post( 'Claim_Detail_ID' );
        $date           = $this->input->post( 'date' );
        $type           = $this->input->post( 'type' );
        $description    = $this->input->post( 'description' );
        $amount         = $this->input->post( 'amount' );
        $tax_amount         = $this->input->post( 'tax_amount' );
        $service_charge         = $this->input->post( 'service_charge' );
        $remark         = $this->input->post( 'remark' );
        $tax_code       = $this->input->post( 'tax_code' );
        $tax_rate       = $this->input->post( 'tax_rate' );
        $status         = $this->input->post( 'status' );
        $photo = $this->input->post( 'photo' );
        $photoType = $this->input->post( 'photoType' );
        $photo_path = '';
        if ( $photo != '' ) {
            $photo_path = UploadImage( $photo, $photoType, 'claimModule/' );
        }

        $claim = $this->hrms_claim_title_m->get( $claimID );

        if ( $status == 'Pending' ) {
            // update claim title record status ( Pending/Save )
            $this->hrms_claim_title_m->update_status( $claimID, $status );
            $total_amount = $this->hrms_claim_details_m->get_total_amount( $employeeID, $claimID );
            $claim[TOTAL_AMOUNT] = $total_amount;
            $user = $this->user_m->get_by_employee_id( $employeeID );

            $claim_manager_level = $claim['claim_manager_level'];
            $claimManagers = explode( ',', $user['CLAIM_MANAGER'] );
            $this->sendMessage2( 'Claim_Manager', 'Success', $claim, 10, $user );
            foreach ( $claimManagers as $key => $manager ) {
                $user = $this->user_m->get_by_employee_id_and_group_name_HR( $manager );
                $token = $user['device_token'];
                $title = '';
                $body = '';
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
            // $fields = array(
            //     ID => $claimID
            // );
            // $this->post( 'PY_Claim_Mobile.aspx', $fields );
            // ???

            $this->SuccessResponse( 'Success' );
        } else {
            if (
                !isset( $date )
                || !isset( $type )
                || !isset( $description )
                || !isset( $amount )
                || !isset( $remark )
            )
            $this->FailedResponse( 'Invalid Param' );

            $limit = $this->hrms_claim_detail_type_m->get_limit_per_type( $employeeID, $type );
            if ( $amount + $service_charge + $tax_amount > $limit ) {
                $this->FailedResponse( 'Amount limit is ' . $limit );
            }

            $claimType = $claim[TYPE];
            $total_amount = $this->hrms_claim_details_m->get_total_amount( $employeeID, $claimID );
            $limit = $this->hrms_claim_type_m->get_limit_per_type( $claimType );
            if ( ( $total_amount + $amount + $service_charge + $tax_amount ) > $limit ) {
                $this->FailedResponse( 'Available amount is ' . ( $limit - $total_amount ) );
            }

            $employee = $this->user_m->get_by_employee_id( $employeeID );
            $manager = $this->user_m->get_by_employee_id_and_group_name_HR( $employee[CLAIM_MANAGER] );

            $data = array(
                EMPLOYEE_ID => $employeeID,
                EMPLOYEE_NAME => $employee[EMPLOYEE_NAME],
                MANAGER_ID => $employee[CLAIM_MANAGER],
                // MANAGER_NAME        => $manager[EMPLOYEE_NAME],
                CLAIM_ID => $claimID,
                DATE => $date,
                TYPE => $type,
                DESCRIPTION => $description,
                AMOUNT => $amount,
                TAX_AMOUNT => $tax_amount,
                SERVICE_CHARGE => $service_charge,
                REMARK => $remark,
                TAX_CODE => $tax_code,
                TAX_RATE => $tax_rate,
                DATE_CREATED => $this->currentTime()
            );

            if ( $photo_path ) {
                // $photo_path = createThumb( $photo_path );
                $data[PHOTO] = $photo_path;
            }

            $data_id = null;
            if ( isset( $claimDetailID ) && $claimDetailID != '' ) {
                $data_id = $this->hrms_claim_details_m->save( $data, $claimDetailID );
            } else {
                if ( $this->hrms_claim_details_m->check_duplicated( $employeeID, $claimID, $date, $type, $description, $amount, $remark ) ) {
                    $this->FailedResponse( 'Duplicate entry' );
                }

                $data_id = $this->hrms_claim_details_m->save( $data );
            }
            if ( $data_id ) {
                $data = $this->hrms_claim_details_m->get( $data_id );
                $this->SuccessResponse( $data );
            } else {
                $this->FailedResponse( 'Something went wrong.' );
            }
        }
    }

    public function setClaimReceived() {
        $claimID = $this->input->post( 'claimID' );
        $this->hrms_claim_title_m->claim_received( $claimID );
        $this->SuccessResponse("Success");
    }

    public function deleteClaimModule() {
        $employeeID = $this->input->post( 'employeeID' );
        $claimModuleID = $this->input->post( 'Claim_Module_ID' );

        if ( !isset( $employeeID ) || !isset( $claimModuleID ) )
        $this->FailedResponse( 'Invalid Param' );

        $result = $this->hrms_claim_details_m->delete( $claimModuleID );
        // $fields = array(
        //     ID => $claimID
        // );
        // $this->post( 'LM_CLAIM_MOB.aspx', $fields );
        // ???
        $this->SuccessResponse( 'Success' );
    }

    public function getClaimApprovalList() {
        $employeeID = $this->input->post( 'employeeID' );

        if ( !isset( $employeeID ) )
        $this->FailedResponse( 'Invalid Param' );

        $users = $this->user_m->get_users_by_claim_manager( $employeeID );
        $data = $this->hrms_claim_title_m->get_list_except_save_status( $users );

        $this->SuccessResponse( $data );
    }

    public function updateClaimStatus() {
        $employeeID = $this->input->post( 'employeeID' );
        $claimID = $this->input->post( 'Claim_ID' );
        $status = $this->input->post( 'Status' );

        if ( !isset( $claimID, $status ) )
        $this->FailedResponse( 'Invalid Param' );
        $result = $this->hrms_claim_title_m->update_status( $claimID, $status );
        // New CR
        // if ( $status == 'Approved' ) {
        //     $data = $this->hrms_claim_title_m->get( $claimID );
        //     $managerLevel = $data['claim_manager_level'];
        //     $id = $data['id'];
        //     $managerStatus = 2;
        //Approved
        //     $dataManager = $this->user_m->get_by_employee_id_and_group_name_HR( $data['EMPLOYEE_ID'] );

        //     $total_amount = $this->hrms_claim_details_m->get_total_amount( $employeeID, $claimID );
        //     $data[TOTAL_AMOUNT] = $total_amount;
        //     $this->hrms_claim_title_m->update_claim_manager_level( $id, $managerStatus );
        //     $data = $this->hrms_claim_title_m->get( $claimID );
        //     if ( $managerLevel == 1 && $dataManager['claim_manager_2'] != 'N/A' ) {
        //         $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $dataManager['claim_manager_2'] );
        //         // $this->sendMessage2( '', 'Waiting for approval', $data, 10, $employee );
        //         $claim_manager_level = $data['claim_manager_level'];
        //         $claimManagers = explode( ',', $employee['CLAIM_MANAGER'] );
        //         foreach ( $claimManagers as $key => $manager ) {
        //             $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $manager );
        //             $token = $employee['device_token'];
        //             $title = '';
        //             $body = '';
        //             if ( $claim_manager_level == null ) {
        //                 $title = '[Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             } else {
        //                 $title = '[Level ' . $claim_manager_level . '][Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             }
        //             $body = $data['Date_Start'] . ' - ' . $data['Date_End'];

        //             $message = array(
        //                 'id' => $claimID,
        //                 'EMPLOYEE_ID' => $data['EMPLOYEE_ID'],
        //                 'Travel_Title' => $data['Travel_Title'],
        //                 'EMPLOYEE_NAME' => $data['EMPLOYEE_NAME'],
        //                 'project_name' => $data['project_name'],
        //                 'project_type' => $data['project_type'],
        //                 'Type' => $data['Type'],
        //                 'contact' => $data['contact'],
        //                 'Country' => $data['Country'],
        //                 'Date_Start' => $data['Date_Start'],
        //                 'Date_End' => $data['Date_End'],
        //                 'total_amount' => $data[TOTAL_AMOUNT],
        //                 'STATUS' => $data['STATUS'],
        //                 'TYPE' => 10
        // );
        //             $this->sendMessage3( $token, $title, $body, $message );
        //         }
        //         $this->SuccessResponse( 'Waiting for next approval manager 2' );
        //     } else if ( $managerLevel == 2 && $dataManager['claim_manager_3'] != 'N/A' ) {
        //         $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $dataManager['claim_manager_3'] );
        //         //$this->sendMessage2( '', 'Waiting for approval', $data, 10, $employee );
        //         $claim_manager_level = $data['claim_manager_level'];
        //         $claimManagers = explode( ',', $employee['CLAIM_MANAGER'] );
        //         foreach ( $claimManagers as $key => $manager ) {
        //             $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $manager );
        //             $token = $employee['device_token'];
        //             $title = '';
        //             $body = '';
        //             if ( $claim_manager_level == null ) {
        //                 $title = '[Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             } else {
        //                 $title = '[Level ' . $claim_manager_level . '][Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             }
        //             $body = $data['Date_Start'] . ' - ' . $data['Date_End'];

        //             $message = array(
        //                 'id' => $claimID,
        //                 'EMPLOYEE_ID' => $data['EMPLOYEE_ID'],
        //                 'Travel_Title' => $data['Travel_Title'],
        //                 'EMPLOYEE_NAME' => $data['EMPLOYEE_NAME'],
        //                 'project_name' => $data['project_name'],
        //                 'project_type' => $data['project_type'],
        //                 'Type' => $data['Type'],
        //                 'contact' => $data['contact'],
        //                 'Country' => $data['Country'],
        //                 'Date_Start' => $data['Date_Start'],
        //                 'Date_End' => $data['Date_End'],
        //                 'total_amount' => $data[TOTAL_AMOUNT],
        //                 'STATUS' => $data['STATUS'],
        //                 'TYPE' => 10
        // );
        //             $this->sendMessage3( $token, $title, $body, $message );
        //         }
        //         $this->SuccessResponse( 'Waiting for next approval manager 3' );
        //     } else if ( $managerLevel == 3 && $dataManager['claim_manager_4'] != 'N/A' ) {
        //         $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $dataManager['claim_manager_4'] );
        //         // $this->sendMessage2( '', 'Waiting for approval', $data, 10, $employee );
        //         $claim_manager_level = $data['claim_manager_level'];
        //         $claimManagers = explode( ',', $employee['CLAIM_MANAGER'] );
        //         foreach ( $claimManagers as $key => $manager ) {
        //             $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $manager );
        //             $token = $employee['device_token'];
        //             $title = '';
        //             $body = '';
        //             if ( $claim_manager_level == null ) {
        //                 $title = '[Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             } else {
        //                 $title = '[Level ' . $claim_manager_level . '][Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             }
        //             $body = $data['Date_Start'] . ' - ' . $data['Date_End'];

        //             $message = array(
        //                 'id' => $claimID,
        //                 'EMPLOYEE_ID' => $data['EMPLOYEE_ID'],
        //                 'Travel_Title' => $data['Travel_Title'],
        //                 'EMPLOYEE_NAME' => $data['EMPLOYEE_NAME'],
        //                 'project_name' => $data['project_name'],
        //                 'project_type' => $data['project_type'],
        //                 'Type' => $data['Type'],
        //                 'contact' => $data['contact'],
        //                 'Country' => $data['Country'],
        //                 'Date_Start' => $data['Date_Start'],
        //                 'Date_End' => $data['Date_End'],
        //                 'total_amount' => $data[TOTAL_AMOUNT],
        //                 'STATUS' => $data['STATUS'],
        //                 'TYPE' => 10
        // );
        //             $this->sendMessage3( $token, $title, $body, $message );
        //         }
        //         $this->SuccessResponse( 'Waiting for next approval manager 4' );
        //     } else if ( $managerLevel == 4 && $dataManager['claim_manager_5'] != 'N/A' ) {
        //         $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $dataManager['claim_manager_5'] );
        //         // $this->sendMessage2( '', 'Waiting for approval', $data, 10, $employee );
        //         $claim_manager_level = $data['claim_manager_level'];
        //         $claimManagers = explode( ',', $employee['CLAIM_MANAGER'] );
        //         foreach ( $claimManagers as $key => $manager ) {
        //             $employee = $this->user_m->get_by_employee_id_and_group_name_HR( $manager );
        //             $token = $employee['device_token'];
        //             $title = '';
        //             $body = '';
        //             if ( $claim_manager_level == null ) {
        //                 $title = '[Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             } else {
        //                 $title = '[Level ' . $claim_manager_level . '][Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
        //             }
        //             $body = $data['Date_Start'] . ' - ' . $data['Date_End'];

        //             $message = array(
        //                 'id' => $claimID,
        //                 'EMPLOYEE_ID' => $data['EMPLOYEE_ID'],
        //                 'Travel_Title' => $data['Travel_Title'],
        //                 'EMPLOYEE_NAME' => $data['EMPLOYEE_NAME'],
        //                 'project_name' => $data['project_name'],
        //                 'project_type' => $data['project_type'],
        //                 'Type' => $data['Type'],
        //                 'contact' => $data['contact'],
        //                 'Country' => $data['Country'],
        //                 'Date_Start' => $data['Date_Start'],
        //                 'Date_End' => $data['Date_End'],
        //                 'total_amount' => $data[TOTAL_AMOUNT],
        //                 'STATUS' => $data['STATUS'],
        //                 'TYPE' => 10
        // );
        //             $this->sendMessage3( $token, $title, $body, $message );
        //         }
        //         $this->SuccessResponse( 'Waiting for next approval manager 5' );
        //     }
        // } else if ( $status == 'Rejected' ) {
        //     $data = $this->hrms_claim_title_m->get( $claimID );
        //     $employeeID = $data['EMPLOYEE_ID'];
        //     $user = $this->user_m->get_by_employee_id_and_group_name_HR( $employeeID );
        //     $token = $user['device_token'];
        //     $id = $data['id'];
        //     $title = 'Claim rejected';
        //     // $this->sendMessage2( '', 'Claim rejected', $data, 12, null );
        //     $body = $data['Travel_Title'];
        //     $message = array(
        //         'id' => $claimID,
        //         'EMPLOYEE_ID' => $data['EMPLOYEE_ID'],
        //         'Travel_Title' => $data['Travel_Title'],
        //         'EMPLOYEE_NAME' => $data['EMPLOYEE_NAME'],
        //         'project_name' => $data['project_name'],
        //         'project_type' => $data['project_type'],
        //         'Type' => $data['Type'],
        //         'contact' => $data['contact'],
        //         'Country' => $data['Country'],
        //         'Date_Start' => $data['Date_Start'],
        //         'Date_End' => $data['Date_End'],
        //         'STATUS' => $data['STATUS'],
        //         'TYPE' => 12
        // );
        //     $this->sendMessage3( $token, $title, $body, $message );

        //     $managerStatus = 3;
        //     $this->hrms_claim_title_m->update_claim_manager_level( $id, $managerStatus );

        //     $this->SuccessResponse( 'Claim rejected' );
        // }

        if ( $result ) {
            $data = $this->hrms_claim_title_m->get( $claimID );
            $employeeID = $data['EMPLOYEE_ID'];
            $total_amount = $this->hrms_claim_details_m->get_total_amount( $employeeID, $claimID );
            $data[TOTAL_AMOUNT] = $total_amount;
            $user = $this->user_m->get_by_employee_id( $employeeID );
            $token = $user['device_token'];

            // Send alert to employee
            if ( $status == 'Approved' ) {
                $this->sendMessage2( '', 'Claim approved', $data, 12, null );

                $title = 'Claim approved';
                $body = $data['Travel_Title'];
                $message = array(
                    'TYPE' => 12
                );
                $message_data = array_merge( $data, $message );
                $this->sendMessage3( $token, $title, $body, $message_data );
                // $fields = array(
                //     ID => $claimID
                // );
                // $this->post( BASE_URL . 'PY_Claim_Mobile.aspx?id=' . $claimID, $fields );
                // ???
            } else if ( $status == 'Rejected' ) {
                $title = 'Claim rejected';
                $this->sendMessage2( '', 'Claim rejected', $data, 12, null );
                $body = $data['Travel_Title'];
                $message = array(
                    'TYPE' => 12
                );
                $message_data = array_merge( $data, $message );
                $this->sendMessage3( $token, $title, $body, $message_data );
            } else if ( $status == 'Pending' ) {
                $claim_manager_level = $data['claim_manager_level'];
                $claimManagers = explode( ',', $user['CLAIM_MANAGER'] );
                foreach ( $claimManagers as $key => $manager ) {
                    $user = $this->user_m->get_by_employee_id_and_group_name_HR( $manager );
                    $token = $user['device_token'];
                    $title = '';
                    $body = '';
                    if ( $claim_manager_level == null ) {
                        $title = '[Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
                    } else {
                        $title = '[Level ' . $claim_manager_level . '][Claim][' . $data['EMPLOYEE_NAME'] . ' – ' . $data['EMPLOYEE_ID'] . '][' . $data['Type'] . '][' . $data[TOTAL_AMOUNT] . ']';
                    }
                    $body = $data['Date_Start'] . ' - ' . $data['Date_End'];

                    $message = array(
                        'TYPE' => 10
                    );
                    $message_data = array_merge( $data, $message );
                    $this->sendMessage3( $token, $title, $body, $message_data );
                }
                $this->sendMessage2( '', 'Claim Submitted', $data, 10, $user );
            }

            $this->SuccessResponse( 'Success' );
        } else {
            $this->FailedResponse( 'Failed' );
        }
    }

    public function getMessageList() {
        $employeeID = $this->input->post( 'employee' );
        $userID = $this->input->post( 'userID' );
        $claimID = $this->input->post( 'Claim_ID' );

        if ( !isset( $employeeID ) || !isset( $claimID ) ) {
            $this->FailedResponse( 'Invalid Param' );
        }
        $this->hrms_claim_message_m->receive_message( $employeeID, $claimID, 1 );
        $data_array = $this->hrms_claim_message_m->get_list( $employeeID, $userID, $claimID );

        $this->SuccessResponse( $data_array );
    }

    public function refreshClaimChat() {
        $sender = $this->input->post( 'sender' );
        $receiver = $this->input->post( 'receiver' );
        $claimID = $this->input->post( 'claimID' );
        $this->hrms_claim_message_m->receive_message( $sender, $claimID, 1 );
        $count = $this->hrms_claim_message_m->get_message_status( $sender, $receiver, $claimID );
        echo json_encode( $count );
        die();
    }

    public function newMessage() {
        $sender = $this->input->post( 'sender' );
        $receiver = $this->input->post( 'receiver' );
        $claimID = $this->input->post( 'Claim_ID' );
        $message = $this->input->post( 'message' );
        $photo = $this->input->post( 'photo' );
        $type = $this->input->post( 'type' );
        $photo_path = '';
        if ( $photo != '' ) {
            $photo_path = UploadImage( $photo, $type, 'claim/' );
            if ( !isset( $sender ) || !isset( $receiver ) ) {
                $this->FailedResponse( 'Invalid Param' );
            }
        }
        $data = array(
            SENDER          => $sender,
            RECEIVER        => $receiver,
            MESSAGE         => $message,
            CLAIM_ID        => $claimID,
            DATE_CREATED    => $this->currentTime(),
        );
        if ( $sender != '' ) {
            $user = $this->user_m->get_by_employee_id( $sender );
            if ( $user ) {
                $data['SENDER_NAME'] = $user[EMPLOYEE_NAME];
                $data['SENDER_PHOTO'] = $user[PHOTO];
            }
        }
        // multiple receiver
        //     $user = $this->user_m->get_by_employee_id( $receiver );
        //     if ( $user ) {
        //         $data['RECEIVER_NAME'] = $user[EMPLOYEE_NAME];
        //         $data['RECEIVER_PHOTO'] = $user[PHOTO];
        //     }

        $data[PHOTO] = $photo_path;

        $data_id = $this->hrms_claim_message_m->save( $data );
        if ( $data_id ) {
            $data = $this->hrms_claim_message_m->get( $data_id );
            $user = $this->user_m->get_by_employee_id( $receiver );
            $token = $user['device_token'];
            $claim = $this->hrms_claim_title_m->get( $claimID );
            $title = 'Message received from: '. $data['SENDER_NAME'] ."[". $data['SENDER']."]";
            $message = array(
                'ID' => $data['id'],
                'SENDER' => $data['SENDER'],
                'DATE_CREATED' => $data['DATE_CREATED'],
                'MESSAGE' => $data['MESSAGE'],
                'SENDER_PHOTO' => $data['SENDER_PHOTO'],
                'PHOTO' => $data['PHOTO'],
                'TYPE' => 11
            );
            $message_data = array_merge( $claim, $message );
            $body = $data['MESSAGE'];
            $temp = $this->sendMessage2( $receiver, 'Message received from', $claim, 11, $data );
            $result =  $this->sendMessage3( $token, $title, $body, $message_data );
            $flag = false;
            if (strpos($result, "\"success\":0") || $result == false || $token == null || $token == ""){
                $this->hrms_claim_message_m->send_message($data_id, 0);
            } else {
                $flag = true;
                $this->hrms_claim_message_m->send_message($data_id, 1);
            }
            if ((strpos($temp, "\"success\":0") || $temp == false) && $flag == false){
                $this->hrms_claim_message_m->send_message($data_id, 0);
            } else {
                $this->hrms_claim_message_m->send_message($data_id, 1);
            }
            $data = $this->hrms_claim_message_m->get( $data_id );
            $this->SuccessResponse( $data );
        } else {
            $this->FailedResponse( 'Something went wrong.' );
        }
    }

    function updateManagerName( $arr_claim, $employeeID ) {
        $result = array();
        foreach ( $arr_claim as $item ) {
            $item[MANAGER_NAME] = $this->getClaimManagerName( $employeeID );
            $result[] = $item;
        }
        return $result;
    }

    function getClaimManagerName( $employeeID ) {
        $employee = $this->user_m->get_by_employee_id( $employeeID );
        $arr_manager_name = array();
        $arr_manager_ids = explode( ',', $employee[CLAIM_MANAGER] );
        foreach ( $arr_manager_ids as $id ) {
            $manager = $this->user_m->get_by_employee_id( $id );
            $arr_manager_name[] = $manager[EMPLOYEE_NAME];
        }
        $manager_name = implode( ', ', $arr_manager_name );
        return $manager_name;
    }
}
