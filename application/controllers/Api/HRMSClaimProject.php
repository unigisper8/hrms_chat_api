<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// New CR
class HRMSClaimProject extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all project
     */
    public function getClaimProjectList()
    {
        $data = $this->hrms_claim_project_m->get_list();

        $this->SuccessResponse($data);
    }

}