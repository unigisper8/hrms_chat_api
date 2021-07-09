<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model
{

    protected $_table_name = '';
    protected $_primary_key = 'id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'id';
    protected $_direction = 'ASC';
    public $rules = array();
    protected $_timestamps = FALSE;

    public function __construct()
    {
        parent::__construct();
    }

    public function array_from_post($fields)
    {
        $data = array();
        foreach ($fields as $field) {
            $data[$field] = $this->input->post($field);
            if ($data[$field] === NULL) {
                $data[$field] = 0;
            }
        }
        return $data;
    }

    public function get($id = NULL, $single = FALSE)
    {

        if ($id != NULL) {
            $filter = $this->_primary_filter;
            $id = $filter($id);
            $this->db->where($this->_primary_key, $id);
            $method = 'row_array';
        } else if ($single == TRUE) {
            $method = 'row_array';
        } else {
            $method = 'result_array';
        }

        $this->db->order_by($this->_order_by, $this->_direction);

        return $this->db->get($this->_table_name)->$method();
    }

    public function get_by($where, $single = FALSE)
    {
        $this->db->where($where);
        return $this->get(NULL, $single);
    }

    public function get_by_sort($where, $single = FALSE)
    {
        $this->db->where($where)->order_by('Date_Created', 'DESC');
        return $this->get(NULL, $single);
    }

    public function get_limit($limit = 1, $offset = 0, $where = NULL, $ordering = NULL, $desc = true)
    {
        if ($where)
            $this->db->where($where);
        if ($ordering) {
            if ($desc)
                $this->db->order_by($ordering, "DESC");
            else
                $this->db->order_by($ordering, "ASC");
        }
        $this->db->limit($limit, $offset);
        return $this->get();
    }

    // get total of query
    public
    function getTotal()
    {
        return $this->db->select()->get($this->_table_name)->num_rows();
    }

    public
    function get_count($where)
    {
        $this->db->where($where);
        return $this->db->get($this->_table_name)->num_rows();
    }

    //
    public
    function getPagination($method, $total, $suffix, $segment = 3)
    {
        $CI =& get_instance();
        $CI->load->library('pagination');
        $CI->load->helper('url');

        $config['base_url'] = base_url($method);
        $config['suffix'] = $suffix;
        $config['first_url'] = $config['base_url'] . '/0/' . $suffix;
        $config['total_rows'] = $total;
        $config['uri_segment'] = $segment;
        $config['next_link'] = lang('next');
        $config['prev_link'] = lang('prev');
        $config['first_link'] = lang('first');
        $config['last_link'] = lang('last');
        $config['num_links'] = 2;

        $per_page = $CI->input->get('limit');
        if ((int)$per_page == 0) $per_page = 12;

        $config['per_page'] = $per_page;

        $CI->pagination->initialize($config);

        $pagination = $CI->pagination->create_links();

        return $pagination;
    }


    public
    function save($data, $id = NULL)
    {
        // Set timestamps
        if ($this->_timestamps == TRUE) {
            $id || $data['created'] = time();
        }

        // Insert
        if ($id === NULL) {
            !isset($data[$this->_primary_key]) || $data[$this->_primary_key] = NULL;
            $this->db->set($data);
            $this->db->insert($this->_table_name);
            $id = $this->db->insert_id();
        } // Update
        else {
            $filter = $this->_primary_filter;
            $id = $filter($id);
            $this->db->set($data);
            $this->db->where($this->_primary_key, $id);
            $this->db->update($this->_table_name);
        }

        return $id;
    }

    public
    function delete($id)
    {
        $filter = $this->_primary_filter;
        $id = $filter($id);

        if (!$id) {
            return FALSE;
        }
        $this->db->where($this->_primary_key, $id);
        $this->db->limit(1);
        $this->db->delete($this->_table_name);
    }

    public
    function delete_by($where, $single = FALSE)
    {
        $this->db->where($where);
        if ($single) {
            $this->db->limit(1);
        }
        $this->db->delete($this->_table_name);
    }

    public
    function update($data, $id)
    {
        $filter = $this->_primary_filter;
        $id = $filter($id);

        if (!$id) {
            return FALSE;
        }
        $this->db->where($this->_primary_key, $id);
        $this->db->limit(1);
        return $this->db->update($this->_table_name, $data);
    }
    public
    function update_by($data, $where)
    {
        $this->db->where($where);
        return $this->db->update($this->_table_name, $data);
    }

    public
    function getIdList()
    {
        $result = array();
        $list = $this->get();
        foreach ($list as $item) {
            $result[$item['id']] = $item;
        }

        return $result;
    }

	public function checkTableFiled($data){
    	$sqlstr = "SHOW COLUMNS FROM ".$this->_table_name;
	    $fieldList = $this->getQuerySqlList($sqlstr);
	    if($fieldList!=null && count($fieldList)){
	    	foreach ($data as $fkey => $fval) {
	    		$flag = true;
	            for($i=0 ; $i<count($fieldList) ; $i++){
	            	if($fieldList[$i]['Field'] == $fkey){
	            		$flag = false;
	            		break;
	            	}
	            }
	            if($flag) unset($data[$fkey]);
	        }
	    } else {
	    	$data = null;
	    }
	    return $data;
    }

    public function getQuerySqlList($sqlstr){
        return $this->db->query($sqlstr)->result_array();
	}

	public function getQuerySqlCount($sqlstr){
        $data = $this->db->query($sqlstr)->row_array();
        if(is_array($data) && array_key_exists("cn", $data)) return $data['cn'];
        else return 0;
	}

}