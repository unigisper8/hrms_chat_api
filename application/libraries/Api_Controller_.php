<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_Controller extends MY_Controller
{

    protected $user_data;
    protected $user_list;
    protected $cat_list;
    protected $store_list;
    protected $country_list;

    public function __construct()
    {
        parent::__construct();

        if ($this->router->method != "GetNotification") {
            logFile($this->router->class . "/" . $this->router->method, json_encode($this->input->post()));
        }
    }

    protected function SuccessResponse($data = array('message' => ''))
    {
        header('Content-Type: application/json');
//        echo json_encode(array('response' => $data));
        echo json_encode($data);
        die();
    }

    protected function FailedResponse($message)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        header('Content-Type: application/json');
        echo json_encode(array(
            'message' => $message,
            'code' => 500
        ));
        die();
    }

    protected function sendMessage1($group, $title, $body, $type = 0, $extra = null)
    {
        $group = 'groupchat';
        if ($extra) {
            $fields = array(
                'data' => array(
                    'title' => $title,
                    'body' => $body,
                    'extra' => $extra,
                    'type' => $type
                ),
                'to' => "/topics/" . $group,
                // 'delay_while_idle' => false,
                'priority' => 'high',
                // 'content_available' => true
            );
        } else {
            $fields = array(
                'data' => array(
                    'title' => $title,
                    'body' => $body,
                    'type' => $type
                ),
                'to' => "/topics/" . $group,
                // 'delay_while_idle' => false,
                'priority' => 'high',
                // 'content_available' => true
            );
        }
        $headers = array(
            'Authorization: key=' . 'AIzaSyDhYg_g_HXHM-L-Hn_fY7WTNEWIPAwQIuw',
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            return false;
        }
        curl_close($ch);
        return $result;
    }

    protected function sendMessage2($group, $title, $body, $type = 0, $extra = null)
    {
        $group = 'groupchat';
        if ($extra) {
            $fields = array(
                'data' => array(
                    'title' => $title,
                    'body' => $body,
                    'extra' => $extra,
                    'type' => $type
                ),
                'to' => "/topics/" . $group,
                // 'delay_while_idle' => false,
                'priority' => 'high',
                // 'content_available' => true
            );
        } else {
            $fields = array(
                'data' => array(
                    'title' => $title,
                    'body' => $body,
                    'type' => $type
                ),
                'to' => "/topics/" . $group,
                // 'delay_while_idle' => false,
                'priority' => 'high',
                // 'content_available' => true
            );
        }
        $headers = array(
            'Authorization: key=' . 'AIzaSyDmCnmgJNASYyNOTnQJ9R_lofK__bPbnSo',
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            return false;
        }
        curl_close($ch);
        return $result;
    }

    protected function newSendMessage($group, $body)
    {
        $fields = array(
            'data' => $body,
            'to' => "/topics/" . $group
        );

        $headers = array(
            'Authorization: key=' . 'AIzaSyDhYg_g_HXHM-L-Hn_fY7WTNEWIPAwQIuw',
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            return false;
        }
        curl_close($ch);
        return $result;
    }

    protected function post($url, $fields, $post = true) {
        $headers = array(
            // 'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($post)
            curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            $error_msg = curl_error($ch);
            return false;
        }
        curl_close($ch);
        return $result;
    }

}