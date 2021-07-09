<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');


function createThumb($img_path, $height = 100)
{
    if (!$img_path) {
        return false;
    }
    $CI = &get_instance();

    $CI->load->library('image_lib');
    $config['image_library'] = 'gd2';
    $config['source_image'] = $img_path;
    $config['create_thumb'] = TRUE;
    $config['maintain_ratio'] = TRUE;
    $config['height'] = $height;
    $CI->image_lib->initialize($config);

    $CI->image_lib->resize();
    $CI->image_lib->clear();

    return getThumbPath($img_path);
}

function getThumbPath($img_path)
{
    if (!$img_path) {
        return null;
    }
    $ext = pathinfo($img_path, PATHINFO_EXTENSION);
    $dirname = pathinfo($img_path, PATHINFO_DIRNAME);
    $filename = pathinfo($img_path, PATHINFO_FILENAME);

    $img_thumb_path = $dirname . "/" . $filename . '_thumb.' . $ext;
    return $img_thumb_path;
}

function getTimeStamp($datestr, $dateonly = TRUE, $datestart = TRUE)
{
    if ($dateonly) {
        if ($datestart)
            $dtime = DateTime::createFromFormat(CUSTOM_DATETIME_FORMAT, $datestr . " 00:00:00");
        else
            $dtime = DateTime::createFromFormat(CUSTOM_DATETIME_FORMAT, $datestr . " 23:59:59");
    } else {
        $dtime = DateTime::createFromFormat(CUSTOM_DATETIME_FORMAT, $datestr);
    }
    if (!$dtime)
        return 0;
    return $dtime->getTimestamp();
}

function UploadImage($data, $type, $path) {
    try {
        $data = str_replace('data:' . $type . ';base64,', '', $data);
        $data = str_replace(' ', '+', $data);
        $path = './uploads/photos/'.$path;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $file_name = $path . time() . '.' . str_replace('image/', '', $type);

        file_put_contents($file_name, base64_decode($data));
        $file_name = substr($file_name, 1);
        return $file_name;
    }
    catch (\Throwable $th) {
        return false;
    }
}

function UploadPhoto($name)
{
    $CI = &get_instance();
    $config['upload_path'] = 'uploads/photos/';
    $config['allowed_types'] = 'gif|jpg|png';
    $config['encrypt_name'] = TRUE;
    $config['max_size'] = '262144000';
    $CI->load->library('upload', $config);

    if (!$CI->upload->do_upload($name)) {
        $msg = "Photo File Error: " . $CI->upload->display_errors();
        logFile('photo_upload', $msg);
        return false;
    }
    $data = $CI->upload->data();
    $file_path = 'uploads/photos/' . $data['file_name'];

    return $file_path;
}

function UploadFile($name)
{
    $CI = &get_instance();
    $config['upload_path'] = 'uploads/photos/';
    $config['allowed_types'] = 'jpg|png|pdf|doc|docx';
    $config['encrypt_name'] = TRUE;
    $config['max_size'] = '262144000';
    $CI->load->library('upload', $config);

    if (!$CI->upload->do_upload($name)) {
        $msg = "File Error: " . $CI->upload->display_errors();
        logFile('file_upload', $msg);
        return false;
    }
    $data = $CI->upload->data();
    $file_path = 'uploads/files/' . $data['file_name'];

    return $file_path;
}

function logFile($title, $str)
{
    $fp = fopen('log.txt', 'a+');
    fwrite($fp, $_SERVER['REMOTE_ADDR'] . ' - ' . date('Y-m-d H:i:s') . ' - ' . $title . "\r\n");
    fwrite($fp, $str . "\r\n\r\n");
    fclose($fp);
}