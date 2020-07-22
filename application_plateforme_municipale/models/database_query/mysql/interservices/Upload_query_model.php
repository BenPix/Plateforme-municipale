<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Upload_query_model extends MY_Model
{

	protected $table = 'upload';

	public function read_for_email($where = array())
	{
		$path = str_replace('\\', '/', FCPATH).'uploads/interservices/';
		$select = array('u.file_name', 'u.file_type', 'CONCAT(\''.$path.'\',u.file_name) AS full_path');

		$this->db->select($select);
		$this->db->from('upload AS u');
		$this->db->where($where);
		$query = $this->db->get();

		return $result = $query->result();
	}

}