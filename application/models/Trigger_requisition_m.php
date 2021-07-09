<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trigger_requisition_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_TRIGGER_REQUISITION';

    public function __construct()
    {
        parent::__construct();
    }
    public function get_by_reference_id($id)
    {
        $data = $this->get_by(array(
            REFERENCE_NO => $id,
        ), TRUE);
        return $data;
    }
}