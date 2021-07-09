<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class HRMSAlert extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    public function sendAlert()
    {
        $data = $this->hrms_alert_m->get_by(array(
            'sent' => 0
        ), true);

        if ($data) {
            $result = $this->sendMessage2('hrms', "Alert",
                "Name: {$data['employee_name']}\nType: {$data['type']}\nStart: {$data['start_time']}\nEnd: {$data['end_time']}\nDescription: {$data['description']}", 100, $data['alert_listing']);
            if ($result){
                $data['sent']  =1;
                $this->hrms_alert_m->save($data, $data['id']);
            }
            $this->SuccessResponse("Sent successfully");
        } else {
            $this->FailedResponse("No alert to send");
        }
    }


}