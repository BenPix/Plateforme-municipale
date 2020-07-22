<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Pole_query_model extends MY_Model
{

	protected $table = 'pole';

	/*
	* liste de tous les pôles existants
	*/
	public function liste_tous_poles_for_drop_down() {
		$results = $this->db
		->select(array('p.nom', 'p.id'))
		->from('pole AS p')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where('pi.pole_id IS NULL')
		->get()
		->result();

		$liste = array();

		foreach ($results as $row)
			$liste[$row->id] = $row->nom;

		return $liste;
	}

	/*
	* liste de tous les pôles sollicitables pour les demandes interservices
	*/
	public function liste_poles_sollicitables_for_dropdown() {
		$results = $this->db
		->select(array('p.nom', 'p.id'))
		->from('pole AS p')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where(array('pi.pole_id' => NULL, 'sollicitable_via_interservices' => 1))
		->get()
		->result();

		$liste = array();

		foreach ($results as $row)
			$liste[$row->id] = $row->nom;

		return $liste;
	}
	/*
	* return TRUE if the pole is sollicitable, else FALSE, same if it's inactivated
	*/
	public function is_sollicitable($pole) {
		$where = array('sollicitable_via_interservices' => 1, 'p.nom' => $pole, 'pi.pole_id' => NULL);

		$query = $this->db
		->select('1')
		->from('pole AS p')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where($where)
		->get();

		return $query->num_rows() === 1;
	}

	/*
	* construction du tableau des poles pour leur gestion
	*/
	public function tableau_poles() {
		// lien pour détailler la demande
		$lienUpdatePoleDebut = $this->lien_model->update_pole_debut();
		$lienUpdatePoleFin = $this->lien_model->update_pole_fin();
		// lien pour visualiser la demande en détail
		$lienDeletePoleDebut = $this->lien_model->delete_pole_debut();
		$lienDeletePoleFin = $this->lien_model->delete_pole_fin();
		// génère le tableau des poles actis
		$select = array('CONCAT(\''.$lienUpdatePoleDebut.'\',p.id,\''.$lienUpdatePoleFin.'\') AS Modifier', 'p.nom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'CONCAT(u.nom,\' \',u.prenom)', 'CONCAT(\''.$lienDeletePoleDebut.'\',p.id,\''.$lienDeletePoleFin.'\') AS Supprimer');
		$where = ('pi.pole_id IS NULL');

		$this->db->select($select, FALSE);
		$this->db->from('pole AS p');
		$this->db->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left');
		$this->db->join('utilisateur AS u', 'p.responsable_id = u.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->where($where);
		$query = $this->db->get();

		return $query;
	}
	/*
	* construction du tableau de détail du pole
	*/
	public function detail_pole($id) {
		// génère le tableau du pole
		$select = array('p.nom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'CONCAT(u.nom,\' \',u.prenom) AS responsable', 'CASE p.sujet_aux_conges WHEN 1 THEN \'Oui\' ELSE \'Non\' END AS sujet_aux_conges', 'CASE p.sollicitable_via_interservices WHEN 1 THEN \'Oui\' ELSE \'Non\' END AS sollicitable', 'CASE p.confidentialite WHEN 1 THEN \'Oui\' ELSE \'Non\' END AS confidentiel', 'CASE WHEN bdc.pole_id IS NOT NULL THEN \'Oui\' ELSE \'Non\' END AS bdc');
		$where = array('p.id' => $id);

		$this->db->select($select, FALSE);
		$this->db->from('pole AS p');
		$this->db->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left');
		$this->db->join('utilisateur AS u', 'p.responsable_id = u.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('pole_autorise_au_bon_de_commande AS bdc', 'bdc.pole_id = p.id', 'left');
		$this->db->where($where);
		$this->db->limit(1);
		$query = $this->db->get();

		return $query;
	}
	/*
	* données du pole pour sa gestion dans la page de détail
	*/
	public function detail_pole_data($data) {
		// génère le tableau du pole
		$select = array('p.id', 'u.id AS responsable_id', 'p.nom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'CONCAT(u.nom,\' \',u.prenom) AS responsable', 'p.sujet_aux_conges AS conges', 'p.sollicitable_via_interservices AS sollicitable', 'p.confidentialite', 'p.categorie_id', 'CASE WHEN bdc.pole_id IS NULL THEN 0 ELSE 1 END AS bdc');
		if ((string)((int)$data) === $data || (int)$data === $data)
			$where = array('p.id' => $data);
		else
			$where = array('p.nom' => $data);

		$this->db->select($select, FALSE);
		$this->db->from('pole AS p');
		$this->db->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left');
		$this->db->join('utilisateur AS u', 'p.responsable_id = u.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('pole_autorise_au_bon_de_commande AS bdc', 'bdc.pole_id = p.id', 'left');
		$this->db->where($where);
		$this->db->limit(1);
		$query = $this->db->get();

		return $query;
	}

	/*
	* liste des poles associés au responsable pour un menu dropdown de formulaire
	*/
	public function liste_poles_du_responsable_for_dropdown($responsable_id)
	{
		$results = $this->db
		->select(array('p.id', 'p.nom')) 
		->from('pole AS p')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where(array('pi.pole_id' => NULL, 'p.responsable_id' => $responsable_id))
		->get()
		->result();

		$liste = array();

		foreach ($results as $row)
			$liste[$row->id] = $row->nom;

		return $liste;
	}

	/*
	* données des poles associés au responsable
	*/
	public function poles_du_responsable($responsable_id)
	{
		$results = $this->db
		->select(array('p.id', 'p.nom', 'p.sollicitable_via_interservices', 'p.sujet_aux_conges', 'p.responsable_id', 'p.confidentialite'))
		->from('pole AS p')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where(array('pi.pole_id' => NULL, 'p.responsable_id' => $responsable_id))
		->get()
		->result();

		return $results;
	}
	/*
	* returns the poles ID having the user as responsable or as belonging user
	* if he has none, return FALSE
	* else, return an arrray of the ID(s)
	*/
	public function find_belonging_user_or_responsable_pole_ids($responsable_id)
	{
		$query = $this->db
		->select(array('p.id'))
		->from('pole AS p')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->join('utilisateur AS u', 'p.id = u.pole_id', 'left')
		->where(array('pi.pole_id' => NULL))
		->group_start()
		->or_where(array('u.id' => $responsable_id, 'p.responsable_id' => $responsable_id))
		->group_end()
		->group_by('p.id')
		->get();

		if ($query->num_rows() === 0) {
			return FALSE;
		} else {
			$liste = array();
			foreach ($query->result() as $row) {
				array_push($liste, $row->id);
			}
			return $liste;
		}
	}
	/*
	* array(pole_id => pole_nom) des pôles permettant un accès exceptionnels aux bon de commande acceptés pour leurs agents
	*/
	public function find_poles_acces_special_bdc_acceptes()
	{
		$list = array();

		$this->db->select(array('p.id', 'p.nom'));
		$this->db->from('pole AS p');
		$this->db->join('bdc_acces_special_pole AS b', 'b.pole_id = p.id');
		$query = $this->db->get();

		foreach ($query->result() as $row) {
			$list[$row->id] = $row->nom;
		}

		return $list;
	}

}