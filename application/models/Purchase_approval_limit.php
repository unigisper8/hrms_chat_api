<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_approval_limit extends MY_Model
{
    public $_table_name = 'man_mob_purchase_approval_limit';

    public function __construct()
    {
        parent::__construct();
    }
}