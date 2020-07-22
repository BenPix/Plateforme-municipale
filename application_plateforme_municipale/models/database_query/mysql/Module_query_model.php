<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Module_query_model extends MY_Model
{

	protected $table = 'module';

	public function liste_modules()
	{
		$this->db->select(array('id', 'module'));
		$this->db->from($this->table);
		$query = $this->db->get();

		return $query->result();
	}

	public function liste_modules_for_dropdown()
	{
		$this->db->select(array('id', 'module'));
		$this->db->from($this->table);
		$query = $this->db->get();

		foreach ($query->result() as $row) {
			$list[$row->id] = $row->module;
		}

		return isset($list) ? $list : array();
	}

	public function modules_actives()
	{
		$this->db->select('id');
		$this->db->from($this->table);
		$this->db->where(array('actif' => 1));
		$query = $this->db->get();

		return array_column($query->result_array(), 'id');
	}

}