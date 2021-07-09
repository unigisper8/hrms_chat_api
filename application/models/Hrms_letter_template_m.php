<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hrms_letter_template_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_HRMS_LETTER_TEMPLATE';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_list()
    {
        $data = $this->get();

        return $data;
    }

    public function get_by_name($name) {
        $data = $this->get_by(array(
            TEMPLATE => $name
        ), true);

        return $data;
    }
}