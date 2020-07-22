<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Appartenance_categorie_utilisateur_query_model extends MY_Model
{

	protected $table = 'appartenance_categorie_utilisateur';

	public function find_categories($user_id)
	{
		$this->db->select('categorie_id');
		$this->db->from($this->table);
		$this->db->where(array('utilisateur_id' => $user_id));
		$query = $this->db->get();

		return array_column($query->result_array(), 'categorie_id');

		$liste = array();

		foreach ($query->result() as $row)
			array_push($liste, $row->categorie_id);
		
		return $liste;
	}

	public function liste_utilisateurs()
	{
		// lien pour delete l'accès spécial
		$lienDeleteDebut = $this->lien_model->delete_acces_special_debut();
		$lienDeletePoleFin = $this->lien_model->delete_acces_special_fin();
		$select = array('CONCAT(u.nom,\' \',u.prenom) AS agent', 'GROUP_CONCAT(c.nom SEPARATOR \', \')', 'CONCAT(\''.$lienDeleteDebut.'\',u.id,\''.$lienDeletePoleFin.'\')');

		$this->db->select($select);
		$this->db->from('appartenance_categorie_utilisateur AS app');
		$this->db->join('utilisateur AS u' , 'u.id = app.utilisateur_id');
		$this->db->join('categorie_article AS c' , 'c.id = app.categorie_id');
		$this->db->group_by('u.id');
		$query = $this->db->get();

		return ($query->num_rows() == 0) ? array() : $query;
	}

}