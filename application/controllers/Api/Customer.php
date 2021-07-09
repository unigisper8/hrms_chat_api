<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {

        $data = $this->customer_m->get(null, true);
        if ($data) {
            $this->SuccessResponse(array('message' => $data['customer_name']));
        }
    }


}