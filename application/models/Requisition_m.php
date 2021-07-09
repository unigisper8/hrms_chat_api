<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Requisition_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_REQUISITION';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_reference_id($id)
    {
        $data = $this->get_by(array(
            REFERENCE_NO => $id,
        ), true);
        return $data;
    }

    public function get_by_id($id)
    {
        $data = $this->get_by(array(
            ID => $id,
        ), true);
        return $data;
    }
}