<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Api_Controller extends MY_Controller
{

    protected $user_data;
    protected $user_list;
    protected $cat_list;
    protected $store_list;
    protected $country_list;
    public static $API_ACCESS_KEY = 'AAAADdSSqZA:APA91bGYG_slmbmcO3x4gzV0ceFNBpy7wQDFBteAjsl3T4TEl_1JhXj7fesY4tfglQwTwJ231H2fk_1cGwQ0mjwcEQEHiOX4IiXFF4PodZvH8MdEGTlHBRxaOfAzZhXX4xJm_r-FVkX4';

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

    protected function sendMessage($title, $body, $type = 0, $extra = null)
    {
        $topic_prefix = preg_replace("/[^a-zA-Z0-9]+/i", "", API_URL);
        $group = $topic_prefix . 'purchase';
        //$group = 'disb';
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
        }
        else {
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
            'Authorization: key=' . 'AIzaSyDhYg_g_HXHM-L-Hn_fY7WTNEWIPAwQIuw', //'AIzaSyAP5fwPuQKQQ4R-3jcyv5yjhKg_zIGU6d4',
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

    protected function sendMessage1($group, $title, $body, $type = 0, $extra = null)
    {
        $topic_prefix = preg_replace("/[^a-zA-Z0-9]+/i", "", API_URL);
        $group = $topic_prefix . 'groupchat';
        // return $group;
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
        }
        else {
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

        $KEY_LIVE = "AIzaSyDmCnmgJNASYyNOTnQJ9R_lofK__bPbnSo";
        $KEY_TEST = "AIzaSyAP5fwPuQKQQ4R-3jcyv5yjhKg_zIGU6d4";
        if (TEST_MODE) {
            $KEY = $KEY_TEST;
            $URL = API_TEST_URL;
        }
        else {
            $KEY = $KEY_LIVE;
            $URL = API_URL;
        }

        $topic_prefix = preg_replace("/[^a-zA-Z0-9]+/i", "", $URL);
        $group = $topic_prefix . 'groupchat';
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
        }
        else {
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
            'Authorization: key=' . $KEY,
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
    public function sendMessage3($token, $title, $body, $data)
    {
        $msg = array
            (
            'title' => "$title",
            'body' => $body,
        );
        
        $message = array
            (
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'notification' => $msg,
            'data' => $data,
        );

        // Print Output in JSON Format
        $data_string = json_encode($message);

        // FCM API Token URL
        $url = "https://fcm.googleapis.com/fcm/send";

        //Curl Headers
        $headers = array
            (
            'Authorization: key=' . self::$API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        // Variable for Print the Result
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

    protected function post($url, $fields)
    {
        $headers = array(
            //            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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

}