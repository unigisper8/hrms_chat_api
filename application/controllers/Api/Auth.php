<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        if (!isset($email, $password)) {
            $this->FailedResponse('Invalid Param');
        }

        $user = $this->user_m->get_user($email, $password);
        if ($user) {
            $this->SuccessResponse($user);
        } else {
            $user = $this->user_m->get_user($email, $password);
            if ($user) {
                $this->SuccessResponse($user);
            } else {
                $this->FailedResponse('You did not register yet');
            }
        }
    }

    public function insert_token()
    {
        $email = $this->input->post('email');
        $type = $this->input->post('device');
        $token = $this->input->post('device_token');
        $this->user_m->insert_token($token, $email);
        echo json_encode(array(
            'message' => "success",
            'code' => 200
        ));
        die();
    }

    public function remove_token()
    {
        $email = $this->input->post('email');
        $type = $this->input->post('device');
        $this->user_m->remove_token($email);
        echo json_encode(array(
            'message' => "success",
            'code' => 200
        ));
        die();
    }
}