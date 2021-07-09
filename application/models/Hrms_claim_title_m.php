<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class Hrms_claim_title_m extends MY_Model
 {
    public $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';

    public function __construct()
 {
        parent::__construct();
    }

    public function get_list()
 {
        $data = $this->get();

        return $data;
    }

    public function get_by_employee_id( $id )
 {
        $data = $this->get_by( array(
            EMPLOYEE_ID => $id,
        ), false );

        return $data;
    }

    public function get_list_except_save_status( $users )
 {
        if ( isset( $users ) && sizeof( $users ) > 0 ) {
            $str_users = implode( "','", $users );
            $in = '';
            if ( $str_users != '' ) {
                // $in = "(EMPLOYEE_ID = $str_users) and ";
                $in = "EMPLOYEE_ID in ('$str_users') and ";
            }
            $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';
            $sql = "select * from $_table_name where $in STATUS != 'Save' order by Date_Start desc";
            $query = $this->db->query( $sql );
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function get_list_except_approved( $id )
 {
        $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';
        $sql = "select * from $_table_name where Employee_ID = '$id' and STATUS != 'Approved' order by Date_Start desc";
        $query = $this->db->query( $sql );
        return $query->result_array();
    }

    public function get_by_id( $id )
 {
        $data = $this->get_by( array(
            ID => $id,
        ), true );

        return $data;
    }

    public function check_duplicated( $employeeID, $title, $type, $country, $startDate, $endDate, $description, $project_name )
 {
        $data = $this->get_by( array(
            EMPLOYEE_ID => $employeeID,
            TRAVEL_TITLE => $title,
            TYPE => $type,
            COUNTRY => $country,
            DATE_START => $startDate,
            DATE_END => $endDate,
            DESCRIPTION => $description,
            PROJECT_NAME => $project_name
        ), false );

        if ( sizeof( $data ) > 0 )
        return true;
        else
        return false;
    }

    public function check_can_delete( $id )
 {
        $data = $this->get( $id );

        if ( $data[STATUS] == 'Pending' || $data[STATUS] == 'Save' )
        return true;

        return false;
    }

    public function update_status( $id, $status )
 {
        $result = $this->update( array(
            STATUS => $status,
            RECEIVE_STATUS => 0
        ), $id );

        return $result;
    }

    // New CR

    public function get_pending_list( $managerID )
 {
        $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';
        $sql = "SELECT COUNT(STATUS) AS `claim_pending_count` FROM $_table_name WHERE FIND_IN_SET('$managerID',Manager_ID) AND STATUS LIKE 'Pending';";
        $query = $this->db->query( $sql );
        $ret = $query->row();
        return $ret->claim_pending_count;
    }

    // New CR

    public function get_list_except_approved_status( $users )
 {
        if ( isset( $users ) && sizeof( $users ) > 0 ) {
            $str_users = implode( "','", $users );
            $in = '';
            if ( $str_users != '' ) {
                // $in = "(EMPLOYEE_ID = $str_users) and ";
                $in = "EMPLOYEE_ID in ('$str_users') and ";
            }
            $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';
            $sql = "select * from $_table_name where $in STATUS = 'Pending' order by Date_Start desc";
            $query = $this->db->query( $sql );
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function get_list_pending() {
        $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';
        $sql = "select * from $_table_name where SEND_STATUS = 0 and STATUS = 'Pending' order by Date_Start desc";
        $query = $this->db->query( $sql );
        return $query->result_array();
    }

    public function get_by_receive_status(){
        $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';
        $sql = "select * from $_table_name where RECEIVE_STATUS = 0 and SEND_STATUS = 1 order by `Date_Start` desc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function claim_received($id) {
        $this->update(array(
            RECEIVE_STATUS => 1
        ), $id);
    }
    public function update_send_status($claimID) {
        $_table_name = 'MAN_MOB_HRMS_CLAIM_TITLE';
        $sql = "update $_table_name set SEND_STATUS = 1 where id = $claimID";
        $this->db->query( $sql );
    }
    // New CR

    public function update_claim_manager_level( $id, $managerStatus ) {
        $data = $this->get_by( array(
            ID => $id,
        ), true );

        $manager_level = intval( $data[CLAIM_MANAGER_LEVEL] );

        if ( $manager_level == 1 && $managerStatus = 3 ) {
            $result = $this->update( array(
                CLAIM_MANAGER_STATUS_1 => $managerStatus,
            ), $id );
        } else if ( $manager_level == 2 && $managerStatus = 3 ) {
            $result = $this->update( array(
                CLAIM_MANAGER_STATUS_2 => $managerStatus,
            ), $id );
        } else if ( $manager_level == 3 && $managerStatus = 3 ) {
            $result = $this->update( array(
                CLAIM_MANAGER_STATUS_3 => $managerStatus,
            ), $id );
        } else if ( $manager_level == 4 && $managerStatus = 3 ) {
            $result = $this->update( array(
                CLAIM_MANAGER_STATUS_4 => $managerStatus,
            ), $id );
        } else if ( $manager_level == 5 && $managerStatus = 3 ) {
            $result = $this->update( array(
                CLAIM_MANAGER_STATUS_5 => $managerStatus,
            ), $id );
        }

        if ( $manager_level == 1 && $managerStatus = 2 ) {
            $manager_level = 2;
            $result = $this->update( array(
                CLAIM_MANAGER_LEVEL => $manager_level,
                CLAIM_MANAGER_STATUS_1 => $managerStatus,
                CLAIM_MANAGER_STATUS_2 => 1
            ), $id );
        } else if ( $manager_level == 2 && $managerStatus = 2 ) {
            $manager_level = 3;
            $result = $this->update( array(
                CLAIM_MANAGER_LEVEL => $manager_level,
                CLAIM_MANAGER_STATUS_2 => $managerStatus,
                CLAIM_MANAGER_STATUS_3 => 1
            ), $id );
        } else if ( $manager_level == 3 && $managerStatus = 2 ) {
            $manager_level = 4;
            $result = $this->update( array(
                CLAIM_MANAGER_LEVEL => $manager_level,
                CLAIM_MANAGER_STATUS_3 => $managerStatus,
                CLAIM_MANAGER_STATUS_4 => 1
            ), $id );
        } else if ( $manager_level == 4 && $managerStatus = 2 ) {
            $manager_level = 5;
            $result = $this->update( array(
                CLAIM_MANAGER_LEVEL => $manager_level,
                CLAIM_MANAGER_STATUS_4 => $managerStatus,
                CLAIM_MANAGER_STATUS_5 => 1
            ), $id );
        } else if ( $manager_level == 5 && $managerStatus = 2 ) {
            $result = $this->update( array(
                CLAIM_MANAGER_STATUS_5 => $managerStatus
            ), $id );
        }

        return $result;
    }

}