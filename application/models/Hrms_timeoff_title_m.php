<?php
defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

class Hrms_timeoff_title_m extends MY_Model
 {
    public $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';

    public function __construct() {
        parent::__construct();
    }

    public function get_list() {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "select * from $_table_name where 1 order by `Date` desc";
        $query = $this->db->query( $sql );
        return $query->result_array();
    }

    public function get_list_pending() {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "select * from $_table_name where SEND_STATUS = 0 order by `Date` desc";
        $query = $this->db->query( $sql );
        return $query->result_array();
    }
    public function update_send_status($id) {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "update $_table_name set SEND_STATUS = 1 where id = $id";
        $this->db->query( $sql );
    }
    public function get_by_employee_ids( $users ) {
        if ( isset( $users ) && sizeof( $users ) > 0 ) {
            $str_users = implode( "','", $users );
            $in = '';
            if ( $str_users != '' ) {
                $in = "EMPLOYEE_ID in ('$str_users') ";
            }
            $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
            $sql = "select * from $_table_name where $in order by `Date` desc";
            $query = $this->db->query( $sql );
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function get_by_employee_id( $id ) {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "select * from $_table_name where EMPLOYEE_ID = '$id' order by `Date` desc";
        $query = $this->db->query( $sql );
        return $query->result_array();
    }

    public function get_by_id( $id ) {
        $data = $this->get_by( array(
            ID => $id,
        ), true );

        return $data;
    }

    public function check_duplicated( $employeeID, $title, $country, $type, $date, $startTime, $endTime, $description, $status ) {
        $data = $this->get_by( array(
            EMPLOYEE_ID => $employeeID,
            TIMEOFF_TITLE => $title,
            COUNTRY => $country,
            TYPE => $type,
            DATE => $date,
            START_HOUR => $startTime,
            END_HOUR => $endTime,
            DESCRIPTION => $description,
            STATUS => $status
        ), false );

        if ( sizeof( $data ) > 0 )
        return true;
        else
        return false;
    }

    public function update_status( $id, $status ) {
        $result = $this->update( array(
            STATUS => $status,
            RECEIVE_STATUS => 0
        ), $id );

        return $result;
    }

    public function get_list_in_month( $employeeID ) {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "select `Type` as category, COUNT(*) as times, SUM(time_to_sec(TIMEDIFF(EndHour, StartHour)) / 3600) as total_hours from $_table_name where YEAR(`Date`) = YEAR(CURRENT_DATE()) AND MONTH(`Date`) = MONTH(CURRENT_DATE()) AND EMPLOYEE_ID = '$employeeID' AND STATUS = 'Approved' GROUP BY `Type`";
        $query = $this->db->query( $sql );
        return $query->result_array();
    }

    public function get_list_in_year( $employeeID ) {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "select `Type` as category, COUNT(*) as times, SUM(time_to_sec(TIMEDIFF(EndHour, StartHour)) / 3600) as total_hours from $_table_name where YEAR(`Date`) = YEAR(CURRENT_DATE()) AND EMPLOYEE_ID = '$employeeID' AND STATUS = 'Approved' GROUP BY `Type`";
        $query = $this->db->query( $sql );
        return $query->result_array();
    }

    // New CR

    public function get_pending_list( $managerID ) {
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "SELECT COUNT(STATUS) AS `timeoff_pending_count` FROM $_table_name WHERE FIND_IN_SET('$managerID',Manager_ID) AND STATUS LIKE 'Pending';";
        $query = $this->db->query( $sql );
        $ret = $query->row();
        return $ret->timeoff_pending_count;
    }

    public function get_by_receive_status(){
        $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
        $sql = "select * from $_table_name where RECEIVE_STATUS = 0 and SEND_STATUS = 1 order by `Date` desc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    // New CR

    public function get_by_employee_ids_pending( $users ) {
        if ( isset( $users ) && sizeof( $users ) > 0 ) {
            $str_users = implode( "','", $users );
            $in = '';
            if ( $str_users != '' ) {
                $in = "EMPLOYEE_ID in ('$str_users') ";
            }
            $_table_name = 'MAN_MOB_HRMS_TIMEOFF_TITLE';
            $sql = "select * from $_table_name where $in AND STATUS = 'Pending' order by `Date` desc";
            $query = $this->db->query( $sql );
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function timeOff_received($id) {
        $this->update(array(
            RECEIVE_STATUS => 1
        ), $id);
    }
}