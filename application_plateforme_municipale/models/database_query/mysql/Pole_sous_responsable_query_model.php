<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Pole_sous_responsable_query_model extends MY_Model
{

	protected $table = 'pole_sous_responsable';

	
	/**
	 *	Récupère tous les utilisateurs sous-responsables d'un pole donné
	 */
	public function liste_sous_responsables($pole_id)
	{
		// génère le tableau des utilisateurs qui seront sélectionnés pour être responsable d'un pole
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS sous_responsable');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->where(array('ui.utilisateur_id' => NULL));
		$this->db->where('u.id IN (SELECT psr.utilisateur_id FROM pole_sous_responsable AS psr WHERE psr.pole_id = '.$pole_id.')', NULL, FALSE);
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->sous_responsable;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs potentiellement sous-responsables
	 */
	public function liste_sous_responsables_potentiels($pole_id)
	{
		// génère le tableau des utilisateurs qui seront sélectionnés pour être responsable d'un pole
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS sous_responsable');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('rang_utilisateur AS r', 'u.rang_id = r.id');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'u.pole_id !=' => $pole_id));
		$this->db->where_in('r.rang', array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'));
		$this->db->where('u.id NOT IN (SELECT psr.utilisateur_id FROM pole_sous_responsable AS psr WHERE psr.pole_id = '.$pole_id.')', NULL, FALSE);
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->sous_responsable;
		}

		return $liste;
	}

}