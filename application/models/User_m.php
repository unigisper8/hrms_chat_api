<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_m extends MY_Model
{
    public $_table_name = 'MAN_MOB_GROUP_NAME';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_user($email, $password)
    {
        //        $user_data = $this->get_by(array(
//            EMAIL => $email,
//            PASSWORD => $password
//        ), TRUE);
//
//        $group = array();
//        $references = '';
//        $arr = $this->get_by(array(
//            EMAIL => $email,
//            PASSWORD => $password
//        ), FALSE);
//        foreach ($arr as $item) {
//            $group[] = $item[GROUP_NAME];
//            if ($references != '') $references .= ';';
//            $references .= $item[REFERENCES_INVITED];
//        }
//        $references = explode(',', $references);
//        $references_final = array();
//        foreach ($references as $item) {
//            if (!in_array(trim($item), $references_final))
//                $references_final[] = trim($item);
//        }
//        $user_data[GROUP_NAME] = $group;
//        $user_data[REFERENCES_INVITED] = $references_final;
//
//        return $user_data;

        $_table_name = 'MAN_MOB_GROUP_NAME';
        $where = "where EMAIL_ADDRESS = '$email' and PASSWORD = '$password'";
        $sql = "select *, GROUP_CONCAT(GROUP_NAME) as groups, GROUP_CONCAT(REFERENCES_INVITED) as references_i from $_table_name $where group by EMAIL_ADDRESS";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $item[GROUP_NAME] = explode(',', $item['groups']);
            $references = explode(',', $item['references_i']);
            $references_final = array();
            foreach ($references as $item1) {
                if (!in_array($item1, $references_final) && trim($item1) != '')
                    $references_final[] = $item1;
            }
            $item[REFERENCES_INVITED] = $references_final;

            $result[] = $item;
        }
        if (sizeof($result) == 0)
            return null;
        return $result[0];
    }

    public function insert_token($token, $email)
    {
        $_table_name = 'MAN_MOB_GROUP_NAME';
        $sql = "update $_table_name set device_token = '$token' where EMAIL_ADDRESS = '$email'";
        $this->db->query($sql);
    }

    public function remove_token($email)
    {
        $_table_name = 'MAN_MOB_GROUP_NAME';
        $sql = "update $_table_name set device_token = '' where EMAIL_ADDRESS = '$email'";
        $this->db->query($sql);
    }

    public function get_by_email($email)
    {
        //        $user_data = $this->get_by(array(
//            EMAIL => $email,
//        ), TRUE);
//
//        $group = array();
//        $references = '';
//        $arr = $this->get_by(array(
//            EMAIL => $email
//        ), FALSE);
//        foreach ($arr as $item) {
//            $group[] = $item[GROUP_NAME];
//            if ($references != '') $references .= ';';
//            $references .= $item[REFERENCES_INVITED];
//        }
//        $references = explode(',', $references);
//        $references_final = array();
//        foreach ($references as $item) {
//            if (!in_array(trim($item), $references_final))
//                $references_final[] = trim($item);
//        }
//        $user_data[GROUP_NAME] = $group;
//        $user_data[REFERENCES_INVITED] = $references_final;
//
//        return $user_data;

        $_table_name = 'MAN_MOB_GROUP_NAME';
        $where = "where EMAIL = '$email'";
        $sql = "select *, GROUP_CONCAT(GROUP_NAME) as groups, GROUP_CONCAT(REFERENCES_INVITED) as references_i from $_table_name $where group by EMAIL_ADDRESS";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $item[GROUP_NAME] = explode(',', $item['groups']);
            $references = explode(',', $item['references_i']);
            $references_final = array();
            foreach ($references as $item1) {
                if (!in_array($item1, $references_final) && trim($item1) != '')
                    $references_final[] = $item1;
            }
            $item[REFERENCES_INVITED] = $references_final;

            $result[] = $item;
        }
        if (sizeof($result) == 0)
            return null;
        return $result[0];
    }

    public function get_by_employee_id($id)
    {
        //        $user_data = $this->get_by(array(
//            EMPLOYEE_ID => $id,
//        ), TRUE);
//
//        $group = array();
//        $references = '';
//        $arr = $this->get_by(array(
//            EMPLOYEE_ID => $id,
//        ), FALSE);
//        foreach ($arr as $item) {
//            $group[] = $item[GROUP_NAME];
//            if ($references != '') $references .= ';';
//            $references .= $item[REFERENCES_INVITED];
//        }
//        $references = explode(',', $references);
//        $references_final = array();
//        foreach ($references as $item) {
//            if (!in_array(trim($item), $references_final))
//                $references_final[] = trim($item);
//        }
//        $user_data[GROUP_NAME] = $group;
//        $user_data[REFERENCES_INVITED] = $references_final;
//
//        return $user_data;


        $_table_name = 'MAN_MOB_GROUP_NAME';
        $where = "where EMPLOYEE_ID = '$id'";
        $sql = "select *, GROUP_CONCAT(GROUP_NAME) as groups, GROUP_CONCAT(REFERENCES_INVITED) as references_i from $_table_name $where group by EMAIL_ADDRESS";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $item[GROUP_NAME] = explode(',', $item['groups']);
            $references = explode(',', $item['references_i']);
            $references_final = array();
            foreach ($references as $item1) {
                if (!in_array($item1, $references_final) && trim($item1) != '')
                    $references_final[] = $item1;
            }
            $item[REFERENCES_INVITED] = $references_final;

            $result[] = $item;
        }
        if (sizeof($result) == 0)
            return null;
        return $result[0];
    }

    public function get_users_by_group_name_HR($id, $get_name)
    {
        $_table_name = 'MAN_MOB_GROUP_NAME';
        $sql = "select GROUP_NAME from $_table_name where EMPLOYEE_ID = '$id' group by GROUP_NAME";
        $query = $this->db->query($sql);
        $group_names = $query->result_array();

        $result = array();
        foreach ($group_names as $index => $group_name) {
            $name = $group_name['GROUP_NAME'];
            $sql = '';
            if($get_name == 'EMPLOYEE_ID'){
                $sql = "SELECT $get_name FROM $_table_name WHERE group_name = '$name'";
            }else if($get_name == 'device_token'){
                $sql = "SELECT $get_name, 'EMPLOYEE_ID' FROM $_table_name WHERE group_name = '$name'";
            }
            // $sql = "SELECT $get_name FROM $_table_name WHERE group_name = '$name'";
            $query = $this->db->query($sql);
            $result = array_merge($result, $query->result_array());
        }
        $result = array_unique($result, SORT_REGULAR);
        return $result;
    }

    public function get_by_employee_id_and_group_name_HR($id)
    {
        $_table_name = 'MAN_MOB_GROUP_NAME';
        $where = "where EMPLOYEE_ID = '$id' AND GROUP_NAME = 'HUMAN RESOURCE'";
        $sql = "select *, GROUP_CONCAT(GROUP_NAME) as groups, GROUP_CONCAT(REFERENCES_INVITED) as references_i from $_table_name $where group by EMAIL_ADDRESS";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $item[GROUP_NAME] = explode(',', $item['groups']);
            $references = explode(',', $item['references_i']);
            $references_final = array();
            foreach ($references as $item1) {
                if (!in_array($item1, $references_final) && trim($item1) != '')
                    $references_final[] = $item1;
            }
            $item[REFERENCES_INVITED] = $references_final;

            $result[] = $item;
        }
        if (sizeof($result) == 0)
            return null;
        return $result[0];
    }

    public function get_users_to_invite($name, $group, $referenceNo = '', $query = '')
    {
        $_table_name = 'MAN_MOB_GROUP_NAME';
        $where = "where GROUP_NAME != '$group' and EMPLOYEE_NAME != '$name' and EMPLOYEE_NAME like '%$query%'";
        $where .= " and (REFERENCES_INVITED != '$referenceNo' and REFERENCES_INVITED not like '$referenceNo,%' and REFERENCES_INVITED not like '%,$referenceNo,%' and REFERENCES_INVITED not like '%,$referenceNo')";
        $sql = "select *, GROUP_CONCAT(GROUP_NAME) as groups, GROUP_CONCAT(REFERENCES_INVITED) as references_i from $_table_name $where group by EMAIL_ADDRESS order by EMPLOYEE_NAME";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $item[GROUP_NAME] = explode(',', $item['groups']);
            $references = explode(',', $item['references_i']);
            $references_final = array();
            foreach ($references as $item1) {
                if (!in_array($item1, $references_final) && trim($item1) != '')
                    $references_final[] = $item1;
            }
            $item[REFERENCES_INVITED] = $references_final;

            $result[] = $item;
        }
        return $result;
    }

    public function get_all_users_to_invite($name, $group, $referenceNo = '', $query = '')
    {
        $_table_name = 'MAN_MOB_GROUP_NAME';
        $where = "where EMPLOYEE_NAME != '$name' and EMPLOYEE_NAME like '%$query%'";
        // $where = "where GROUP_NAME != '$group' and EMPLOYEE_NAME != '$name' and EMPLOYEE_NAME like '%$query%'";
        // $where .= " and (REFERENCES_INVITED != '$referenceNo' and REFERENCES_INVITED not like '$referenceNo,%' and REFERENCES_INVITED not like '%,$referenceNo,%' and REFERENCES_INVITED not like '%,$referenceNo')";
        $sql = "select *, GROUP_CONCAT(GROUP_NAME) as groups, GROUP_CONCAT(REFERENCES_INVITED) as references_i from $_table_name $where group by EMAIL_ADDRESS order by EMPLOYEE_NAME";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $item[GROUP_NAME] = explode(',', $item['groups']);
            $references = explode(',', $item['references_i']);
            $references_final = array();
            foreach ($references as $item1) {
                if (!in_array($item1, $references_final) && trim($item1) != '')
                    $references_final[] = $item1;
            }
            $item[REFERENCES_INVITED] = $references_final;

            $result[] = $item;
        }
        return $result;
    }

    public function get_users($employee_id, $query = '')
    {
        $_table_name = 'MAN_MOB_GROUP_NAME';
        $where = "where EMPLOYEE_ID != '$employee_id' and EMPLOYEE_NAME like '%$query%'";
        $sql = "select *, GROUP_CONCAT(GROUP_NAME) as groups, GROUP_CONCAT(REFERENCES_INVITED) as references_i from $_table_name $where group by EMAIL_ADDRESS order by EMPLOYEE_NAME";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $item[GROUP_NAME] = explode(',', $item['groups']);
            $references = explode(',', $item['references_i']);
            $references_final = array();
            foreach ($references as $item1) {
                if (!in_array($item1, $references_final) && trim($item1) != '')
                    $references_final[] = $item1;
            }
            $item[REFERENCES_INVITED] = $references_final;

            $result[] = $item;
        }
        return $result;
    }

    public function get_users_by_claim_manager($employee_id)
    {
        if (!isset($employee_id) || $employee_id == '')
            return array();

        $_table_name = 'MAN_MOB_GROUP_NAME';
        $sql = "select * from $_table_name where CLAIM_MANAGER like '%$employee_id%'";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $managers = explode(',', $item[CLAIM_MANAGER]);
            foreach ($managers as $manager) {
                if ($manager == $employee_id) {
                    $result[] = $item[EMPLOYEE_ID];
                    break;
                }
            }
        }
        return $result;
    }

    public function get_users_by_leave_manager($employee_id)
    {
        if (!isset($employee_id) || $employee_id == '')
            return array();

        $_table_name = 'MAN_MOB_GROUP_NAME';
        $sql = "select * from $_table_name where LEAVE_MANAGER like '%$employee_id%'";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $managers = explode(',', $item[LEAVE_MANAGER]);
            foreach ($managers as $manager) {
                if ($manager == $employee_id) {
                    $result[] = $item[EMPLOYEE_ID];
                    break;
                }
            }
        }
        return $result;
    }

    public function get_users_by_timeoff_manager($employee_id)
    {
        if (!isset($employee_id) || $employee_id == '')
            return array();

        $_table_name = 'MAN_MOB_GROUP_NAME';
        $sql = "select * from $_table_name where TIMEOFF_MANAGER like '%$employee_id%'";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $managers = explode(',', $item[TIMEOFF_MANAGER_ID]);
            foreach ($managers as $manager) {
                if ($manager == $employee_id) {
                    $result[] = $item[EMPLOYEE_ID];
                    break;
                }
            }
        }
        return $result;
    }

    // New CR
    public function get_users_by_letter_manager($employee_id)
    {
        if (!isset($employee_id) || $employee_id == '')
            return array();

        $_table_name = 'MAN_MOB_GROUP_NAME';
        $sql = "select * from $_table_name where MANAGER like '%$employee_id%'";
        $query = $this->db->query($sql);
        $arr = $query->result_array();
        $result = array();
        foreach ($arr as $item) {
            $managers = explode(',', $item[TIMEOFF_MANAGER_ID]);
            foreach ($managers as $manager) {
                if ($manager == $employee_id) {
                    $result[] = $item[EMPLOYEE_ID];
                    break;
                }
            }
        }
        return $result;
    }

}