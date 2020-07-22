<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Delegation_query_model extends MY_Model
{

	protected $table = 'delegation';

	public function find_delegue($user_id)
	{
		$this->db->select(array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS delegue'));
		$this->db->from($this->table);
		$this->db->join('utilisateur AS u', 'u.id = delegation.delegue_id');
		$this->db->where(array('responsable_id' => $user_id));
		$this->db->limit(1);
		$query = $this->db->get();

		return $query->num_rows() == 0 ? '' : $query->row()->delegue;
	}

}