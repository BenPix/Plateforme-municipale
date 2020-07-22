<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Pole_delegue_query_model extends MY_Model
{

	protected $table = 'pole_delegue';

	public function find_poles($responsable_originel_id)
	{
		$this->db->select(array('p.nom'));
		$this->db->from($this->table);
		$this->db->join('pole AS p', 'p.id = pole_delegue.pole_id');
		$this->db->where(array('responsable_originel_id' => $responsable_originel_id));
		$query = $this->db->get();

		return $query->result_array();
	}

}