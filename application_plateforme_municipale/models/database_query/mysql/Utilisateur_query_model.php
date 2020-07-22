<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Utilisateur_query_model extends MY_Model
{

	protected $table = 'utilisateur';

	
	/**
	 *	Récupère l'utilisateur pour envoi d'email
	 */
	public function check_user_for_new_password($pseudo, $email)
	{
		$select = array('u.id', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email');
		$email = explode('@', $email);
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->where(array('u.pseudo' => $pseudo, 'u.email_nom' => $email[0], 'e.domaine' => $email[1]));
		$query = $this->db->get();

		return $query->num_rows() === 1 ? $query->row() : FALSE;
	}

	/**
	 *	check if the password differs from the user's pseudo/email/nom/prenom
	 */
	public function check_password($password, $utilisateur_id)
	{
		$this->db->select('1');
		$this->db->from('utilisateur AS u');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->where(array('u.id' => $utilisateur_id));
		$this->db->group_start();
		$this->db->or_where(array('u.pseudo' => $password, 'u.nom' => $password, 'u.prenom' => $password, 'CONCAT(u.email_nom,\'@\',e.domaine)' => $password));
		$this->db->group_end();
		$query = $this->db->get();

		return $query->num_rows() !== 1;
	}

	/**
	 *	find user's info for the connexion
	 */
	public function find_user_for_connexion($pseudo, $password)
	{
		$select = array('u.id', 'u.nom', 'u.prenom', 'u.pseudo', 'u.password', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.id AS pole_id', 'p.nom AS pole', 'r.rang', 'CASE WHEN ui.utilisateur_id IS NULL THEN 1 ELSE 0 END AS actif', 'CASE WHEN i.utilisateur_id IS NULL THEN 1 ELSE 0 END AS valide');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('rang_utilisateur AS r', 'u.rang_id = r.id');
		$this->db->where(array('u.pseudo' => $pseudo));
		$query = $this->db->get();

		return ($query->num_rows() === 0) ? FALSE : $query->row();
	}

	/**
	 *	Récupère tous les utilisateurs dont l'inscription doit encore être validée
	 */
	public function table_des_inscriptions_a_valider()
	{
		// lien pour valider la demande
		$lienValidationDebut = $this->lien_model->valider_inscription_debut();
		$lienValidationFin = $this->lien_model->valider_inscription_fin();
		// lien pour refuser la demande
		$lienRefusDebut = $this->lien_model->refuser_inscription_debut();
		$lienRefusFin = $this->lien_model->refuser_inscription_fin();
		// génère le tableau des demandes envoyées
		$select = array('CONCAT(\''.$lienValidationDebut.'\',u.id,\''.$lienValidationFin.'\') AS Valider', 'CONCAT(\''.$lienRefusDebut.'\',u.id,\''.$lienRefusFin.'\') AS Refuser', 'u.nom', 'u.prenom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.nom AS pole');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->where(array('ui.utilisateur_id' => NULL));
		$this->db->order_by('u.id', 'DESC');
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère tous l'utilisateur pour afficher son profil
	 */
	public function table_profil($id)
	{
		$select = array('u.nom', 'u.prenom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.nom AS pole');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->where(array('u.id' => $id));
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère l'utilisateur pour afficher son profil (page de confirmation)
	 */
	public function tableUtilisateurAValider($id)
	{
		// génère le tableau du profil de l'utilisateur
		$select = array('u.nom', 'u.prenom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.nom AS pole');
		$where = array('u.id' => $id);
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->where('ui.utilisateur_id IS NULL');
		$this->db->where($where);
		$query = $this->db->get();

		return ($query->num_rows() == 1) ? $query : FALSE;
	}

	/**
	 *	Récupère l'utilisateur dont l'inscription est à valider (pour poursuivre l'inscription)
	 */
	public function inscription_a_valider($id)
	{
		// génère le tableau de l'utilisateur à valider
		$select = array('u.nom', 'u.prenom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.id AS pole_id', 'p.nom AS pole', 'u.rang_id AS rang');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id');
		$this->db->where(array('u.id' => $id, 'ui.utilisateur_id' => NULL));
		$query = $this->db->get();

		return ($query->num_rows() === 1) ? $query : FALSE;
	}
	/**
	 *	Récupère l'utilisateur actif et toutes les infos à son sujet
	 */
	public function infos_utilisateur($id, $is_utilisateur_inactive = FALSE)
	{
		// génère le tableau de l'utilisateur à valider
		$select = array('u.nom', 'u.prenom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'u.pseudo', 'p.id AS pole_id', 'p.nom AS pole', 'ut0.id AS responsable_id', 'ut0.nom AS nom_responsable', 'ut0.prenom AS prenom_responsable', 'CONCAT(ut0.email_nom,\'@\',e1.domaine) AS email_responsable', 'u.rang_id', 'r.rang');
		$where = array('u.id' => $id);
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('utilisateur AS ut0', 'p.responsable_id = ut0.id');
		$this->db->join('rang_utilisateur AS r', 'u.rang_id = r.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('email_domaine AS e1', 'ut0.email_domaine_id = e1.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		if ( ! $is_utilisateur_inactive ) $this->db->where('ui.utilisateur_id IS NULL');
		$this->db->where($where);
		$query = $this->db->get();

		return ($query->num_rows() == 1) ? $query->row() : FALSE;
	}
	/**
	 *	Récupère l'utilisateur pour envoi d'email
	 */
	public function findUserForEmail($id)
	{
		$select = array('u.nom', 'u.prenom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.nom AS pole', 'u.pseudo');
		$where = array('u.id' => $id);
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->where($where);
		$query = $this->db->get();

		return ($query->num_rows() == 1) ? $query->row() : FALSE;
	}
	/**
	 *	Récupère l'utilisateur pour un tableau
	 */
	public function find_user_for_managing($id, $is_utilisateur_inactive)
	{
		$select = array('u.nom', 'u.prenom', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.id AS pole_id', 'p.nom AS pole', 'r.nom AS rang');
		$condition_for_active_or_not = $is_utilisateur_inactive ? 'ui.utilisateur_id !=' : 'ui.utilisateur_id';
		$where = array('u.id' => $id, $condition_for_active_or_not => NULL, 'i.utilisateur_id' => NULL);
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('rang_utilisateur AS r', 'u.rang_id = r.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->where($where);
		$query = $this->db->get();

		return $query;
	}
	/**
	 *	Find all the users having their subscription validated, and whom account has not been disactivated
	 */
	public function table_des_utilisateurs_valides($offset = 0, $limit = 1000, $nom = '')
	{
		// lien pour modifier l'utilisateur
		$lienModifDebut = $this->lien_model->modifier_profil_debut();
		$lienModifFin = $this->lien_model->modifier_profil_fin();
		// lien pour rendre l'utilisateur inactif
		$lienDeleteDebut = $this->lien_model->supprimer_profil_debut();
		$lienDeleteFin = $this->lien_model->supprimer_profil_fin();
		// génère le tableau
		$select = array('CONCAT(\''.$lienModifDebut.'\',u.id,\''.$lienModifFin.'\') AS Modifier', 'CONCAT(\''.$lienDeleteDebut.'\',u.id,\''.$lienDeleteFin.'\') AS Supprimer', 'u.nom', 'u.prenom', 'u.pseudo', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.nom AS pole');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'i.utilisateur_id' => NULL));
		if ( ! empty($nom) )
			$this->db->like('u.nom', $nom, 'both');
		$this->db->order_by('u.nom');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Find all the users whom account has been disactivated
	 */
	public function table_des_utilisateurs_inactives($offset = 0, $limit = 1000, $nom = '')
	{
		// lien pour modifier + réactiver l'utilisateur
		$lienModifDebut = $this->lien_model->modifier_et_reactiver_profil_debut();
		$lienModifFin = $this->lien_model->modifier_et_reactiver_profil_fin();
		// génère le tableau
		$select = array('CONCAT(\''.$lienModifDebut.'\',u.id,\''.$lienModifFin.'\') AS Modifier', 'u.nom', 'u.prenom', 'u.pseudo', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email', 'p.nom AS pole');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id');
		if ( ! empty($nom) )
			$this->db->like('u.nom', $nom, 'both');
		$this->db->order_by('u.nom');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère tous les utilisateurs potentiellement responsables
	 */
	public function liste_responsables_potentiels()
	{
		// génère le tableau des utilisateurs qui seront sélectionnés pour être responsable d'un pole
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS responsable');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id', 'left');
		$this->db->join('rang_utilisateur AS r', 'u.rang_id = r.id');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'r.rang' => 'responsable'));
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->responsable;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs responsables d'un pôle
	 */
	public function liste_des_responsables()
	{
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS responsable');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.id = p.responsable_id');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'u.rang_id' => 3));
		$this->db->order_by('u.nom');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->responsable;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs potentiellement délégués responsables, donc appartenant au pole du responsable qui délègue
	 */
	public function list_potential_delegue($user_id, $pole_id)
	{
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS responsable');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('delegation AS d', 'u.id = d.responsable_id', 'left');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'u.pole_id' => $pole_id, 'u.id !=' => $user_id));
		$this->db->group_start();
		$this->db->or_where(array('d.delegue_id' => NULL, 'd.delegue_id !=' => $user_id));
		$this->db->group_end();
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->responsable;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs appartenant au pole mère du sous-pole indiqué mais à aucun sous-pole
	 */
	public function list_for_confidential_sous_pole($id)
	{
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS user');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('sous_pole AS sp', 'sp.pole_mere_id = p.id', 'left');
		$this->db->join('appartenance_sous_pole AS asp', 'u.id = asp.utilisateur_id', 'left');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'asp.utilisateur_id' => NULL, 'u.pole_id' =>'(SELECT sp.pole_mere_id FROM sous_pole AS sp WHERE sp.id = '.$id.')'), NULL, FALSE);
		// $this->db->where($id.' NOT IN (SELECT sous_pole_id FROM appartenance_sous_pole AS asp WHERE asp.utilisateur_id=u.id)'); // it seems this line is useless
		$this->db->group_by('u.id');
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->user;
		}

		return $liste;
	}
	/**
	 *	Récupère tous les utilisateurs appartenant au pole mère du sous-pole indiqué, et ayant un rang permettant les interservices
	 */
	public function list_for_sous_pole($id)
	{
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS user');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('pole AS p', 'u.pole_id = p.id');
		$this->db->join('sous_pole AS sp', 'sp.pole_mere_id = p.id', 'left');
		$this->db->join('appartenance_sous_pole AS asp', 'u.id = asp.utilisateur_id', 'left');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'u.pole_id' =>'(SELECT sp.pole_mere_id FROM sous_pole AS sp WHERE sp.id = '.$id.')'), NULL, FALSE);
		$this->db->where_in('u.rang_id', array('3', '4', '6'));
		$this->db->group_start();
		$this->db->or_where(array('asp.sous_pole_id !=' => $id, 'asp.sous_pole_id' => NULL));
		$this->db->group_end();
		$this->db->group_by('u.id');
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->user;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs actifs appartenant au sous-pole
	 */
	public function liste_appartenance_sous_pole($id)
	{
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS user');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('appartenance_sous_pole AS asp', 'u.id = asp.utilisateur_id');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'asp.sous_pole_id' => $id));
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->user;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs valide pour un menu de type dropdown
	 */
	public function liste_utilisateurs_valides($ranks = array(), $not_ranks = array())
	{
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS agent');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id', 'left');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->join('rang_utilisateur AS r', 'u.rang_id = r.id');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'i.utilisateur_id' => NULL));
		if ( ! empty($ranks) ) $this->db->where_in('r.rang', $ranks);
		if ( ! empty($not_ranks) ) $this->db->where_not_in('r.rang', $not_ranks);
		$this->db->order_by('u.nom');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->agent;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs valide pouvant être sélectionnés pour valider une note
	 */
	public function utilisateurs_pour_valider_note_dropdown()
	{
		// génère le tableau
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS agent');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('inscription_a_valider AS i', 'u.id = i.utilisateur_id', 'left');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'i.utilisateur_id' => NULL));
		$this->db->where_in('u.rang_id', array(3, 4, 6)); // responsable', utilisateur_superieur, utilisateur_particulier
		$this->db->order_by('u.nom');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->agent;
		}

		return $liste;
	}

	/**
	 *	Récupère tous les utilisateurs actifs appartenant a un pole
	 * possibilité d'avoir une occurence pour ceux ayant accès aux interservices
	 */
	// public function listeAgentDuPole($pole_id, $has_acces_interservices = FALSE) ancien nom
	public function liste_pole_users($pole_id, $has_acces_interservices = FALSE)
	{
		$select = array('u.id', 'CONCAT(u.nom,\' \',u.prenom) AS agent');
		
		$this->db->select($select, FALSE);
		$this->db->from('utilisateur AS u');
		$this->db->join('utilisateur_inactive AS ui', 'u.id = ui.utilisateur_id', 'left');
		$this->db->where(array('ui.utilisateur_id' => NULL, 'u.pole_id' => $pole_id));
		if ($has_acces_interservices)
			$this->db->where_in('u.rang_id', array('3', '4', '6'));
		$this->db->order_by('u.nom', 'ASC');
		$query = $this->db->get();

		$liste = array();

		foreach ($query->result() as $row) {
			$liste[$row->id] = $row->agent;
		}

		return $liste;
	}

}