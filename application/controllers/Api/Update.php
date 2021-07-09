<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Update extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getInfo()
    {
        $data = $this->app_m->getInfo();
        if ($data) {
            $this->SuccessResponse($data);
        }
    }

    public function getHrmsInfo()
    {
        $data = $this->app_hrms_m->getInfo();
        if ($data) {
            $this->SuccessResponse($data);
        }
    }
}