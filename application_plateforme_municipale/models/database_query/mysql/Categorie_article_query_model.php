<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Categorie_article_query_model extends MY_Model
{

	protected $table = 'categorie_article';

	/*
	* liste de toutes les catégories pour des menu drop down par exemple
	*/
	public function liste_categories() {
		$results = $this->db
		->select(array('id', 'nom')) 
		->from($this->table)
		->get()
		->result();

		$liste = array();

		foreach ($results as $row) {
			$liste[$row->id] = $row->nom;
		}

		return $liste;
	}

	public function liste_categories_for_tableau()
	{
		// lien pour delete l'accès spécial
		$lienDeleteDebut = $this->lien_model->delete_categorie_debut();
		$lienDeletePoleFin = $this->lien_model->delete_categorie_fin();
		$select = array('c.nom', 'c.cible', 'GROUP_CONCAT(p.nom SEPARATOR \', \')', 'CONCAT(\''.$lienDeleteDebut.'\',c.id,\''.$lienDeletePoleFin.'\')');

		$this->db->select($select);
		$this->db->from('categorie_article AS c');
		$this->db->join('pole AS p' , 'c.id = p.categorie_id', 'left');
		$this->db->group_by('c.id');
		$query = $this->db->get();

		return ($query->num_rows() == 0) ? array() : $query;
	}

}