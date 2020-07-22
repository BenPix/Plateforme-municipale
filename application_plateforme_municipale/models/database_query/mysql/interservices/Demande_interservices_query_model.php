<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Demande_interservices_query_model extends MY_Model
{

	protected $table = 'demande_interservices';

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes reçues, classées par numéros de dossier.
	 */
	public function table_demandes_envoyees($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		// lien pour visualiser la demande en détail
		$lienDetailsDebut = $this->lien_model->details_demande_debut();
		$lienDetailsMilieu = $this->lien_model->details_demande_milieu();
		$lienDetailsFin = $this->lien_model->details_demande_fin();
		// génère le tableau des demandes envoyées
		$select = array('CONCAT(\''.$lienDetailsDebut.'\',d.id,\''.$lienDetailsMilieu.'\',d.num_dossier,\''.$lienDetailsFin.'\') AS num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'ps.nom AS pole_sollicite', 'd.demande', 'CONCAT(CASE WHEN e.date_precise = 0 THEN \'Délai maximum\' WHEN e.date_precise = 1 THEN \'Date précise\' ELSE \'Au mieux\' END,\' \',CASE WHEN e.echeance IS NULL THEN \'\' ELSE DATE_FORMAT(e.echeance,"%d/%m/%Y") END)', 'd.degre_urgence', 's.etat');
		$where1 = ('dv.demande_interservices_id IS NULL');
		$where2 = ('(d.statut_id = 1 OR d.statut_id = 2)');
		$where3 = ('(d.pole_attache_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
			OR d.pole_attache_id IN (SELECT pole.id FROM pole WHERE pole.responsable_id = '.$utilisateur_id.'))');
		$where4 = (
			'(d.pole_attache_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
			AND 1 = 
			CASE pa.confidentialite
			WHEN 1 THEN
				CASE WHEN dc.sous_pole_id IN (SELECT sous_pole_id FROM appartenance_sous_pole WHERE utilisateur_id = '.$utilisateur_id.') THEN 1
				ELSE 0 END
			ELSE 1 END)'
		);
		$where5 = 'd.pole_attache_id IN (SELECT psr.pole_id from pole_sous_responsable as psr where psr.utilisateur_id = '.$utilisateur_id.')';
		$conditions = $this->filtrage($filtre);

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('relance_demande_interservices AS r', 'd.id = r.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->join('demande_interservices_affectee_a_sous_pole AS d_aff', 'd.id = d_aff.demande_interservices_id', 'left');
		$this->db->join('demande_interservices_confidentielle AS dc', 'd.id = dc.demande_interservices_id', 'left');
		$this->db->join('demande_interservices_origine_sous_pole AS do', 'd.id = do.demande_interservices_id', 'left');
		$this->db->join('sous_pole AS sp', 'do.sous_pole_id = sp.id', 'left');
		$this->db->where($where1);
		$this->db->where($where2);
		$this->db->where($conditions['conditions']);
		if (!empty($conditions['condition_statut']))
			$this->db->where($conditions['condition_statut']);
		if ($this->session->userdata('rang') == 'responsable')
			$this->db->where($where3);
		else {
			$this->db->group_start();
			$this->db->where($where4);
			$this->db->or_where($where5);
			$this->db->group_end();
		}
		$this->db->order_by('d.num_dossier', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes reçues, classées par numéros de dossier.
	 */
	private function table_demandes_envoyees_avec_couleur_query($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		// lien pour visualiser la demande en détail
		$lienDetailsDebut = $this->lien_model->details_demande_debut();
		$lienDetailsMilieu = $this->lien_model->details_demande_milieu();
		$lienDetailsFin = $this->lien_model->details_demande_fin();
		// génère le tableau des demandes envoyées
		$select = array('CONCAT(\''.$lienDetailsDebut.'\',d.id,\''.$lienDetailsMilieu.'\',\'<strong style="color:\',CASE WHEN sp.couleur IS NULL THEN \'#000000\' ELSE sp.couleur END,\';">\',d.num_dossier,\'</strong>\',\''.$lienDetailsFin.'\') AS num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'ps.nom AS pole_sollicite', 'd.demande', 'CONCAT(CASE WHEN e.date_precise = 0 THEN \'Délai maximum\' WHEN e.date_precise = 1 THEN \'Date précise\' ELSE \'Au mieux\' END,\' \',CASE WHEN e.echeance IS NULL THEN \'\' ELSE DATE_FORMAT(e.echeance,"%d/%m/%Y") END) AS delai', 'd.degre_urgence', 's.etat', 'sp.couleur', 'sp.nom');
		$where1 = ('dv.demande_interservices_id IS NULL');
		$where2 = ('(d.statut_id = 1 OR d.statut_id = 2)');
		$where3 = ('(d.pole_attache_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
			OR d.pole_attache_id IN (SELECT pole.id FROM pole WHERE pole.responsable_id = '.$utilisateur_id.'))');
		$where4 = (
			'(d.pole_attache_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
			AND 1 = 
			CASE pa.confidentialite
			WHEN 1 THEN
				CASE WHEN dc.sous_pole_id IN (SELECT sous_pole_id FROM appartenance_sous_pole WHERE utilisateur_id = '.$utilisateur_id.') THEN 1
				ELSE 0 END
			ELSE 1 END)'
		);
		$where5 = 'd.pole_attache_id IN (SELECT psr.pole_id from pole_sous_responsable as psr where psr.utilisateur_id = '.$utilisateur_id.')';
		$conditions = $this->filtrage($filtre);

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('relance_demande_interservices AS r', 'd.id = r.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->join('demande_interservices_confidentielle AS dc', 'd.id = dc.demande_interservices_id', 'left');
		$this->db->join('demande_interservices_origine_sous_pole AS do', 'd.id = do.demande_interservices_id', 'left');
		$this->db->join('sous_pole AS sp', 'do.sous_pole_id = sp.id', 'left');
		$this->db->where($where1);
		$this->db->where($where2);
		$this->db->where($conditions['conditions']);
		if (!empty($conditions['condition_statut']))
			$this->db->where($conditions['condition_statut']);
		if ($this->session->userdata('rang') == 'responsable')
			$this->db->where($where3);
		else {
			$this->db->group_start();
			$this->db->where($where4);
			$this->db->or_where($where5);
			$this->db->group_end();
		}
		$this->db->order_by('d.num_dossier', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get_compiled_select();

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes reçues, classées par numéros de dossier.
	 */
	public function table_demandes_envoyees_avec_couleur($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		$compiled_select = $this->table_demandes_envoyees_avec_couleur_query($utilisateur_id, $offset, $limit, $filtre);

		$query = $this->db->query($compiled_select);

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes reçues, classées par numéros de dossier.
	 */
	public function table_demandes_recues($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		// lien pour les pièces jointes
		$lienDebut = $this->lien_model->download_interservice_debut();
		$lienFin = $this->lien_model->download_interservice_fin();
		// lien pour visualiser la demande en détail
		$lienDetailsDebut = $this->lien_model->details_demande_debut();
		$lienDetailsMilieu = $this->lien_model->details_demande_milieu();
		$lienDetailsFin = $this->lien_model->details_demande_fin();
		// génère le tableau des demandes reçues
		$select = array('CONCAT(\''.$lienDetailsDebut.'\',d.id,\''.$lienDetailsMilieu.'\',\'<strong style="color:\',CASE WHEN sp.couleur IS NULL THEN \'#000000\' ELSE sp.couleur END,\';">\',d.num_dossier,\'</strong>\',\''.$lienDetailsFin.'\') AS num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'pa.nom AS pole_attache', 'd.demande', 'CONCAT(CASE WHEN e.date_precise = 0 THEN \'Délai maximum\' WHEN e.date_precise = 1 THEN \'Date précise\' ELSE \'Au mieux\' END,\' \',CASE WHEN e.echeance IS NULL THEN \'\' ELSE DATE_FORMAT(e.echeance,"%d/%m/%Y") END)', 'd.degre_urgence', 's.etat');
		$where = 'd.pole_sollicite_id = (SELECT pole_id FROM utilisateur WHERE id = '.$utilisateur_id.')
			AND (d.statut_id = 1 OR d.statut_id = 2)
			AND dv.demande_interservices_id IS NULL';
		$conditions = $this->filtrage($filtre);

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('utilisateur AS u', 'd.utilisateur_id = u.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('relance_demande_interservices AS r', 'd.id = r.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->join('demande_interservices_affectee_a_sous_pole AS d_aff', 'd.id = d_aff.demande_interservices_id', 'left');
		$this->db->join('sous_pole AS sp', 'd_aff.sous_pole_id = sp.id', 'left');
		$this->db->where($where);
		$this->db->where($conditions['conditions']);
		if (!empty($conditions['condition_statut']))
			$this->db->where($conditions['condition_statut']);
		$this->db->order_by('d.degre_urgence', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes reçues, classées par numéros de dossier.
	 */
	private function table_demandes_recues_avec_couleur_query($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		// lien pour les pièces jointes
		$lienDebut = $this->lien_model->download_interservice_debut();
		$lienFin = $this->lien_model->download_interservice_fin();
		// lien pour visualiser la demande en détail
		$lienDetailsDebut = $this->lien_model->details_demande_debut();
		$lienDetailsMilieu = $this->lien_model->details_demande_milieu();
		$lienDetailsFin = $this->lien_model->details_demande_fin();
		// contenu du formulaire
		$form_open_debut = '<form action="'.site_url();
		$form_open_milieu = '/demande/maj/';
		$form_open_fin = '" method="post" accept-charset="utf-8">';
		$select_debut = '<select name="statut">';
		$option1_debut = '<option value="1"';
		$option1_fin = 'en attente</option>';
		$option2_debut = '<option value="2"';
		$option2_fin = 'en cours</option>';
		$select_fin = '<option value="3">terminé</option> </select>';
		$selected = ' selected="selected">';
		$input = '<input type="submit" name="maj" value="Mettre à jour"/>';

		// génère le tableau des demandes reçues
		$select = array(
			'd.id', 
			'CONCAT(
				\''.$lienDetailsDebut.'\',
				d.id,
				\''.$lienDetailsMilieu.'\',
				\'<strong style="color:\',
				CASE WHEN sp.couleur IS NULL 
					THEN \'#000000\' 
					ELSE sp.couleur 
					END,\';">\',
				d.num_dossier,
				\'</strong>\',
				\''.$lienDetailsFin.'\') 
				AS num_dossier', 
			'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 
			'pa.nom AS pole_attache', 
			'd.demande', 
			'CONCAT(
				CASE WHEN e.date_precise = 0 
					THEN \'Délai maximum\' 
					WHEN e.date_precise = 1 
					THEN \'Date précise\' 
					ELSE \'Au mieux\' 
					END,\' \',
				CASE WHEN e.echeance IS NULL 
					THEN \'\' 
					ELSE DATE_FORMAT(e.echeance,"%d/%m/%Y") 
					END) 
				AS delai', 
			'd.degre_urgence', 
			'CONCAT(
				\''.$form_open_debut.'\',
				\''.$form_open_milieu.'\',
				d.id,
				\''.$form_open_fin.'\',
				\''.$select_debut.'\',
				\''.$option1_debut.'\',
				CASE WHEN s.etat LIKE \'en attente\' THEN \''.$selected.'\' ELSE \'>\' END,
				\''.$option1_fin.'\',
				\''.$option2_debut.'\',
				CASE WHEN s.etat LIKE \'en cours\' THEN \''.$selected.'\' ELSE \'>\' END,
				\''.$option2_fin.'\',
				\''.$select_fin.'\',
				\''.$input.'\',
				\''.form_close().'\') 
				AS etat', 
			'sp.couleur',
			'sp.nom');
		$where = 'd.pole_sollicite_id = (SELECT pole_id FROM utilisateur WHERE id = '.$utilisateur_id.')
			AND (d.statut_id = 1 OR d.statut_id = 2)
			AND dv.demande_interservices_id IS NULL';
		$conditions = $this->filtrage($filtre);

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('utilisateur AS u', 'd.utilisateur_id = u.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('relance_demande_interservices AS r', 'd.id = r.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->join('demande_interservices_affectee_a_sous_pole AS d_aff', 'd.id = d_aff.demande_interservices_id', 'left');
		$this->db->join('sous_pole AS sp', 'd_aff.sous_pole_id = sp.id', 'left');
		$this->db->where($where);
		$this->db->where($conditions['conditions']);
		if (!empty($conditions['condition_statut']))
			$this->db->where($conditions['condition_statut']);
		$this->db->order_by('d.degre_urgence', 'DESC');
		$this->db->order_by('d.num_dossier', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get_compiled_select();

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes reçues, classées par numéros de dossier.
	 */
	public function table_demandes_recues_avec_couleur($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		$compiled_select = $this->table_demandes_recues_avec_couleur_query($utilisateur_id, $offset, $limit, $filtre);

		$query = $this->db->query($compiled_select);

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la légende de la table des demandes reçues.
	 */
	public function legende_couleur_demandes_recues($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		// génère le tableau des couleurs différentes affichée pour les demandes reçues
		$compiled_select = $this->table_demandes_recues_avec_couleur_query($utilisateur_id, $offset, $limit, $filtre);
		$requete = 'SELECT MIN(t.couleur) AS couleur, t.nom FROM ('.$compiled_select.') AS t GROUP BY t.nom';

		$query = $this->db->query($requete);

		return ($query->num_rows() == 0) ? array() : $query->result();
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes reçues, classées par numéros de dossier.
	 */
	public function legende_couleur_demandes_envoyees($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		// génère le tableau des couleurs différentes affichée pour les demandes envoyées
		$compiled_select = $this->table_demandes_envoyees_avec_couleur_query($utilisateur_id, $offset, $limit, $filtre);
		$requete = 'SELECT MIN(t.couleur) AS couleur, t.nom FROM ('.$compiled_select.') AS t GROUP BY t.nom';

		$query = $this->db->query($requete);

		return ($query->num_rows() == 0) ? array() : $query->result();
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes termiénes, classées par numéros de dossier.
	 */
	public function table_demandes_terminees($utilisateur_id, $offset = 0, $limit = 99999, $filtre = array())
	{
		// lien pour visualiser la demande en détail
		$lienDetailsDebut = $this->lien_model->details_demande_terminee_debut();
		$lienDetailsMilieu = $this->lien_model->details_demande_terminee_milieu();
		$lienDetailsFin = $this->lien_model->details_demande_terminee_fin();
		// génère le tableau des demandes envoyées
		$select = array('CONCAT(\''.$lienDetailsDebut.'\',d.id,\''.$lienDetailsMilieu.'\',d.num_dossier,\''.$lienDetailsFin.'\') AS num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'CONCAT(u.nom, \' \', u.prenom) AS demandeur', 'pa.nom AS pole_attache', 'ps.nom AS pole_sollicite', 'd.demande');
		$where1 = array(
			'dv.demande_interservices_id' => NULL,
			'd.statut_id' => '3'
		);
		$where2 = '(d.pole_attache_id IN (SELECT pole.id FROM pole WHERE pole.responsable_id = '.$utilisateur_id.'))';
		$where3 = '(d.pole_sollicite_id IN (SELECT pole.id FROM pole WHERE pole.responsable_id = '.$utilisateur_id.'))';
		$where4 = '(d.pole_attache_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
			AND 1 = 
			CASE pa.confidentialite
			WHEN 1 THEN
				CASE WHEN dc.sous_pole_id IN (SELECT sous_pole_id FROM appartenance_sous_pole WHERE utilisateur_id = '.$utilisateur_id.') THEN 1
				ELSE 0 END
			ELSE 1 END)';
		$where5 = '(d.pole_sollicite_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.'))';
		$conditions = $this->filtrage($filtre);

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('utilisateur AS u', 'd.utilisateur_id = u.id');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('demande_interservices_confidentielle AS dc', 'd.id = dc.demande_interservices_id', 'left');
		$this->db->join('demande_interservices_terminee AS dt', 'd.id = dt.demande_interservices_id', 'left');
		$this->db->where($where1);
		$this->db->where($conditions['conditions']);
			$this->db->group_start();
		if ($this->session->userdata('rang') === 'responsable') {
			$this->db->where($where2, NULL, FALSE);
			$this->db->or_where($where3, NULL, FALSE);
		 } else {
			$this->db->where($where4, NULL, FALSE);
			$this->db->or_where($where5, NULL, FALSE);
		}
			$this->db->group_end();
		$this->db->order_by('dt.date_fin', 'DESC');
		$this->db->order_by('d.num_dossier', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes à valider.
	 */
	public function table_demandes_a_valider($utilisateur_id)
	{
		// lien pour valider la demande
		$lienValidationDebut = $this->lien_model->valider_demande_debut();
		$lienValidationFin = $this->lien_model->valider_demande_fin();
		// lien pour refuser la demande
		$lienRefusDebut = $this->lien_model->refuser_demande_debut();
		$lienRefusFin = $this->lien_model->refuser_demande_fin();
		// lien pour les pièces jointes
		$lienPJDebut = $this->lien_model->download_interservice_debut();
		$lienPJFin = $this->lien_model->download_interservice_fin();

		$select = array('CONCAT(\''.$lienValidationDebut.'\',d.id,\''.$lienValidationFin.'\') AS Valider', 'CONCAT(\''.$lienRefusDebut.'\',d.id,\''.$lienRefusFin.'\') AS Refuser', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'CONCAT(u.nom, \' \', u.prenom) AS demandeur', 'p.nom AS pole_sollicite', 'd.demande', 'CONCAT(CASE WHEN e.date_precise = 0 THEN \'Délai maximum\' WHEN e.date_precise = 1 THEN \'Date précise\' ELSE \'Au mieux\' END,\' \',CASE WHEN e.echeance IS NULL THEN \'\' ELSE DATE_FORMAT(e.echeance,"%d/%m/%Y") END)', 'd.degre_urgence', 'IF (up.demande_interservices_id IS NULL, up.demande_interservices_id, CONCAT(\''.$lienPJDebut.'\',d.id,\''.$lienPJFin.'\')) AS pieces_jointes');

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id');
		$this->db->join('utilisateur AS u', 'd.utilisateur_id = u.id');
		$this->db->join('pole AS p', 'd.pole_sollicite_id = p.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('upload AS up', 'd.id = up.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->where(array('pa.responsable_id' => $utilisateur_id, 'd.statut_id !=' => 4));
		$this->db->order_by('d.id', 'DESC');
		$this->db->group_by('d.id');
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes filtrées dans les stattistiques.
	 */
	public function table_demandes_a_filtrer($mes_post, $offset = 0, $limit = 99999)
	{
		// lien pour visualiser la demande en détail
		$lienDetailsDebut = $this->lien_model->details_demande_debut();
		$lienDetailsMilieu = $this->lien_model->details_demande_milieu();
		$lienDetailsFin = $this->lien_model->details_demande_fin();
		// génère le tableau des demandes filtrées
		$select = array('CONCAT(\''.$lienDetailsDebut.'\',d.id,\''.$lienDetailsMilieu.'\',d.num_dossier,\''.$lienDetailsFin.'\') AS num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'pa.nom AS pole_attache', 'ps.nom AS pole_sollicite', 'd.demande', 'DATE_FORMAT(e.echeance,"%d/%m/%Y") AS date_souhaitee', 's.etat');

		// crée les conditions pour le where pour chaque donnée post envoyée (=filtre)
		$conditions = $this->filtrage($mes_post);
		$where = array('d.statut_id !=' => 4, 'dv.demande_interservices_id' => NULL);

		$this->db->select($select, FALSE);
		$this->db->from($this->table.' AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->where($where);
		$this->db->where($conditions['conditions']);
		if ( ! empty($conditions['condition_statut']) )
			$this->db->where($conditions['condition_statut']);
		$this->db->order_by('d.num_dossier', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}

	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes filtrées dans les stattistiques.
	 */
	public function table_demandes_a_filtrer_pour_export($mes_post, $offset = 0, $limit = 99999)
	{
		// génère le tableau des demandes envoyées
		$select = array('d.num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'pa.nom AS pole_attache', 'ps.nom AS pole_sollicite', 'd.demande', 'DATE_FORMAT(e.echeance,"%d/%m/%Y") AS date_souhaitee', 'DATE_FORMAT(r.date_relance,"%d/%m/%Y") AS date_relance', 's.etat');

		// crée les conditions pour le where pour chaque donnée post envoyée (=filtre)
		$conditions = $this->filtrage($mes_post);
		$where = array('d.statut_id !=' => 4, 'dv.demande_interservices_id' => NULL);

		// génère le tableau des demandes envoyées
		$this->db->select($select, FALSE);
		$this->db->from($this->table.' AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('relance_demande_interservices AS r', 'd.id = r.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->where($where);
		$this->db->where($conditions['conditions']);
		if (!empty($conditions['condition_statut']))
			$this->db->where($conditions['condition_statut']);
		$this->db->order_by('d.num_dossier', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}
	/**
	 *	Récupère des données dans la base de données pour créer la table des demandes filtrées pour afficher le graphique.
	 */
	public function read_for_chart($choix, $mes_post)
	{
		// génère le tableau des demandes filtrées
		if ($choix == 'direction_attachee')
			$select = array('pa.nom AS portion', 'count(*) as total');
		elseif ($choix == 'direction_sollicitee')
			$select = array('ps.nom AS portion', 'count(*) as total');
		elseif ($choix == 'statut')
			$select = array('s.etat AS portion', 'count(*) as total');

		// crée les conditions pour le where pour chaque donnée post envoyée (=filtre)
		$conditions = $this->filtrage($mes_post);
		$where = array('d.statut_id !=' => 4, 'dv.demande_interservices_id' => NULL);

		$this->db->select($select, FALSE);
		$this->db->from($this->table.' AS d');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->where($where);
		$this->db->where($conditions['conditions']);
		if (!empty($conditions['condition_statut']))
			$this->db->where($conditions['condition_statut']);
		$this->db->group_by('portion');
		$query = $this->db->get();

		return $query->result();
	}

	// find the details to fill the form or to do some data checking
	public function find_demande_complete($id)
	{
		// lien pour les pièces jointes
		$lienPJDebut = $this->lien_model->download_interservice_debut();
		$lienPJFin = $this->lien_model->download_interservice_fin();
		$select = array('d.id', 'd.num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'CONCAT(u.nom, \' \', u.prenom) AS demandeur', 'u.id AS demandeur_id', 'CONCAT(u.email_nom,\'@\',ed.domaine) AS mail_demandeur', 'CONCAT(ut.nom,\' \',ut.prenom) AS responsable_attache', 'ut.id AS responsable_attache_id', 'CONCAT(ut.email_nom,\'@\',edo.domaine) AS mail_responsable_attache', 'CONCAT(uti.email_nom,\'@\',edom.domaine) AS mail_sollicite', 'ps.responsable_id AS responsable_sollicite_id', 'CASE WHEN e.date_precise = 0 THEN \'délai maximum\' WHEN e.date_precise = 1 THEN \'date précise\' ELSE \'au mieux\' END AS delai', 'd.degre_urgence', 'pa.nom AS direction_attachee', 'pa.id AS direction_attachee_id', 'ps.nom AS direction_sollicitee', 'ps.id AS direction_sollicitee_id', 'd.demande', 'DATE_FORMAT(e.echeance,"%d/%m/%Y") AS date_souhaitee', 'e.echeance AS date_souhaitee_form', 'DATE_FORMAT(r.date_relance,"%d/%m/%Y") AS date_relance', 's.etat', '(select c.commentaire from commentaire_demande_interservices as c where c.demande_interservices_id=d.id order by c.id DESC limit 1) as raison_refus', 'CONCAT(\''.$this->session->userdata('nom').'\',\' \',\''.$this->session->userdata('prenom').'\') AS refuseur', 'IF (up.demande_interservices_id IS NULL, up.demande_interservices_id, CONCAT(\''.$lienPJDebut.'\',d.id,\''.$lienPJFin.'\')) AS pieces_jointes', 'da.sous_pole_id', 'sp.nom AS sous_pole', 'IF (dav.demande_interservices_id IS NULL, 0, 1) AS a_valider');

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('utilisateur AS u', 'd.utilisateur_id = u.id');
		$this->db->join('email_domaine AS ed', 'u.email_domaine_id = ed.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('utilisateur AS ut', 'pa.responsable_id = ut.id');
		$this->db->join('email_domaine AS edo', 'ut.email_domaine_id = edo.id');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('utilisateur AS uti', 'ps.responsable_id = uti.id');
		$this->db->join('email_domaine AS edom', 'uti.email_domaine_id = edom.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('relance_demande_interservices AS r', 'd.id = r.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->join('upload AS up', 'd.id = up.demande_interservices_id', 'left');
		$this->db->join('demande_interservices_affectee_a_sous_pole AS da', 'd.id = da.demande_interservices_id', 'left');
		$this->db->join('demande_interservices_a_valider AS dav', 'd.id = dav.demande_interservices_id', 'left');
		$this->db->join('sous_pole AS sp', 'da.sous_pole_id = sp.id', 'left');
		$this->db->where('d.id', $id);
		$this->db->limit(1);
		$query = $this->db->get();

		return $query->num_rows() === 0 ? FALSE : $query->row();
	}

	// find the details to display in the table
	public function find_demande_detail($id)
	{
		$utilisateur_id = $this->session->userdata('id');
		$select = array('d.num_dossier', 'DATE_FORMAT(d.horodateur, "%d/%m/%Y") AS horodateur', 'CONCAT(u.nom, \' \', u.prenom) AS demandeur', 'pa.nom AS direction_attachee', 'ps.nom AS direction_sollicitee', 'CONCAT(CASE WHEN e.date_precise = 0 THEN \'Délai maximum\' WHEN e.date_precise = 1 THEN \'Date précise\' ELSE \'Au mieux\' END,\' \',CASE WHEN e.echeance IS NULL THEN \'\' ELSE DATE_FORMAT(e.echeance,"%d/%m/%Y") END)', 'd.degre_urgence', 'DATE_FORMAT(r.date_relance,"%d/%m/%Y") AS date_relance', 's.etat');
		// conditions si l'utilisateur est un admin (accès à tout)
		$where1 = array('d.id' => $id);
		// conditions si l'utilisateur est un responsable (accès à tout dont le pole attaché ou sollicité lui appartient)
		$where2 = (
			'd.id='.$id.' AND
			(d.pole_attache_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
			OR
			d.pole_sollicite_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
			OR
			d.pole_attache_id IN (SELECT pole.id FROM pole WHERE pole.responsable_id = '.$utilisateur_id.')
			OR
			d.pole_sollicite_id IN (SELECT pole.id FROM pole WHERE pole.responsable_id = '.$utilisateur_id.')
			)'
		);
		// conditions si l'utilisateur n'est ni admin ni responsable
		$where3 = (
			'd.id = '.$id.'
			AND
			(
				(d.pole_attache_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
					AND 1 = 
						CASE pa.confidentialite
						WHEN 1 THEN
							CASE WHEN dc.sous_pole_id IN (SELECT sous_pole_id FROM appartenance_sous_pole WHERE utilisateur_id = '.$utilisateur_id.') THEN 1
							ELSE 0 END
						ELSE 1 END)
					OR
				d.pole_sollicite_id = (SELECT u.pole_id FROM utilisateur AS u WHERE u.id = '.$utilisateur_id.')
					OR
				d.pole_attache_id IN (SELECT psr.pole_id from pole_sous_responsable as psr where psr.utilisateur_id = '.$utilisateur_id.')
			)'
		);

		$this->db->select($select, FALSE);
		$this->db->from('demande_interservices AS d');
		$this->db->join('utilisateur AS u', 'd.utilisateur_id = u.id');
		$this->db->join('demande_interservices_a_valider AS dv', 'd.id = dv.demande_interservices_id', 'left');
		$this->db->join('email_domaine AS ed', 'u.email_domaine_id = ed.id');
		$this->db->join('pole AS pa', 'd.pole_attache_id = pa.id');
		$this->db->join('utilisateur AS ut', 'pa.responsable_id = ut.id');
		$this->db->join('email_domaine AS edo', 'ut.email_domaine_id = edo.id');
		$this->db->join('pole AS ps', 'd.pole_sollicite_id = ps.id');
		$this->db->join('utilisateur AS uti', 'ps.responsable_id = uti.id');
		$this->db->join('email_domaine AS edom', 'uti.email_domaine_id = edom.id');
		$this->db->join('echeance_demande_interservices AS e', 'd.id = e.demande_interservices_id', 'left');
		$this->db->join('relance_demande_interservices AS r', 'd.id = r.demande_interservices_id', 'left');
		$this->db->join('statut_demande_interservices AS s', 'd.statut_id = s.id');
		$this->db->join('demande_interservices_affectee_a_sous_pole AS d_aff', 'd.id = d_aff.demande_interservices_id', 'left');
		$this->db->join('demande_interservices_confidentielle AS dc', 'd.id = dc.demande_interservices_id', 'left');
		if ($this->session->userdata('rang') == 'admin')
			$this->db->where($where1);
		elseif ($this->session->userdata('rang') == 'responsable')
			$this->db->where($where2);
		else
			$this->db->where($where3);
		$this->db->limit(1);
		$query = $this->db->get();

		return $query->num_rows() === 0 ? FALSE : $query;
	}
	/*
	*
	*
	*
	*
	*
	*
	*
	* FONCTIONS PRIVEES
	*
	*
	*
	*
	*
	*
	*
	*/
	/**
	 *	Crée les conditions qui seront utilisées dans le where pour le filtre (et l'export après filtrage).
	 */
	private function filtrage($mes_post) {
		$conditions = array();
		$condition_statut = '';

		foreach ($mes_post as $key => $value) {
			if (!empty($value)) {
				switch ($key) {
					case 'dossier_depart':
						$conditions['d.num_dossier >='] = $value;
						break;
					case 'dossier_fin':
						$conditions['d.num_dossier <='] = $value;
						break;
					case 'date_depart':
						$conditions['d.horodateur >='] = $value;
						break;
					case 'date_fin':
						$conditions['d.horodateur <='] = $value;
						break;
					case 'destinataire':
						if ($value != 'Tous')
							$conditions['ps.nom'] = $value;
						break;
					case 'expediteur':
						if ($value != 'Tous')
							$conditions['pa.nom'] = $value;
						break;
					case 'destinataire_id':
						if ($value != 'Tous')
							$conditions['ps.id'] = $value;
						break;
					case 'expediteur_id':
						if ($value != 'Tous')
							$conditions['pa.id'] = $value;
						break;
					case 'sous_pole':
						if ($value != 'Tous')
							$conditions['sp.nom'] = $value;
						break;
					case 'les_statuts':
						$i = 0;
						$len = count($value);
						$condition_statut = '(s.etat LIKE \'';

						foreach ($value as $checked) {
							$condition_statut .= $checked.'\'';

							if ($i++ == $len - 1) {
								$condition_statut .= ')';
							} else {
								$condition_statut .= ' OR s.etat LIKE \'';
							}
						}
						break;
					case 'id':
						$conditions['d.id'] = $value;
						break;
					
					default:
						# code...
						break;
				}
			}
		}
		return array('conditions'=>$conditions, 'condition_statut'=>$condition_statut);
	}

}