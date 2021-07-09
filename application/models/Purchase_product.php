<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_product extends MY_Model
{
    public $_table_name = 'man_mob_purchase_product';

    public function __construct()
    {
        parent::__construct();
    }
}