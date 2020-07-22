<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Recaptcha_query_model extends MY_Model
{

	protected $table = 'recaptcha';
	protected $cle = 'id';

	public function read_recaptcha()
	{
		$this->db->select();
		$this->db->from($this->table);
		$this->db->where('id', 1);
		$query = $this->db->get();

		return $query->num_rows() == 0 ? FALSE : $query->row();
	}

	public function check_recaptcha_sitekey()
	{
		$this->db->select('data_sitekey');
		$this->db->from($this->table);
		$this->db->where('id', 1);
		$query = $this->db->get();

		return $query->num_rows() == 0 ? '' : $query->row()->data_sitekey;
	}

	public function check_recaptcha_secretkey()
	{
		$this->db->select('data_secretkey');
		$this->db->from($this->table);
		$this->db->where('id', 1);
		$query = $this->db->get();

		return $query->num_rows() == 0 ? '' : $query->row()->data_secretkey;
	}

}