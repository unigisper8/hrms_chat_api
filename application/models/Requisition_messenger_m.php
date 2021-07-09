<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Requisition_messenger_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_REQUISITION_MESSENGER';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_reference_id($id)
    {
        $data = $this->get_by(array(
            REFERENCE_NO => $id,
        ), false);
        return $data;
    }
}