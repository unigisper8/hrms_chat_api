<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('user_m');
        $this->load->model('messenger_m');
        $this->load->model('trigger_m');
        $this->load->model('customer_m');
        $this->load->model('requisition_m');
        $this->load->model('requisition_listing_m');
        $this->load->model('requisition_messenger_m');
        $this->load->model('hrms_attendance_m');
        $this->load->model('hrms_salary_m');
        $this->load->model('hrms_leave_m');
        $this->load->model('hrms_message_m');
        $this->load->model('cac_type_m');
        $this->load->model('app_m');
        $this->load->model('app_hrms_m');
        $this->load->model('hrms_leave_application_m');
        $this->load->model('hrms_leave_entitlement_m');
        $this->load->model('hrms_leave_message_m');
        $this->load->model('hrms_leave_type_m');
        $this->load->model('hrms_claim_title_m');
        $this->load->model('hrms_claim_details_m');
        $this->load->model('hrms_claim_message_m');
        $this->load->model('hrms_claim_type_m');
        $this->load->model('hrms_claim_detail_type_m');
        $this->load->model('hrms_claim_project_m');
        $this->load->model('hrms_gst_m');
        $this->load->model('hrms_timeoff_title_m');
        $this->load->model('hrms_timeoff_details_m');
        $this->load->model('hrms_timeoff_message_m');
        $this->load->model('hrms_timeoff_type_m');
        $this->load->model('hrms_letter_m');
        $this->load->model('hrms_letter_template_m');
        $this->load->model('hrms_letter_message_m');

        $this->load->model('hrms_alert_m');
        $this->load->model('hrms_emergency_leave_m');
        $this->load->model('hrms_payroll_m');
        $this->load->model('hrms_attendance_report_m');
        $this->load->model('hrms_attendance_late_message_m');
        
        //------------------------------//
        // purchase models
        //------------------------------//
                
        $this->load->model('purchase_approval_limit');
		$this->load->model('purchase_cost_center');
		$this->load->model('purchase_currency');
		$this->load->model('purchase_delivery');
		$this->load->model('purchase_delivery_mode');
		$this->load->model('purchase_level');
		$this->load->model('purchase_requisition');
		$this->load->model('purchase_supplier');
		$this->load->model('purchase_uom');
		$this->load->model('purchase_track');
		$this->load->model('purchase_approval_team');
		$this->load->model('purchase_message');
		$this->load->model('purchase_report');
		$this->load->model('purchase_product');
		$this->load->model('purchase_priority_level');
    }

    public function currentTime(){
        $nowish = new DateTime();
        $la = new DateTimeZone('Asia/Kuala_Lumpur');
        $nowish->setTimeZone($la);
        return $nowish->format('Y-m-d H:i:s');
    }
}