<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Note_query_model extends MY_Model
{

	protected $table = 'note';

	public function find_notes_envoyees($filter = NULL, $offset = 0, $limit = 99999)
	{
		if (is_array($filter) && array_key_exists('n.objet', $filter)) {
			$objet = $filter['n.objet'];
			unset($filter['n.objet']);
		}

		$lien_debut = $this->lien_model->detail_note_debut();
		$lien_fin = $this->lien_model->detail_note_fin();

		$select = array('CONCAT(\''.$lien_debut.'\',n.id,\''.$lien_fin.'\')', 'n.id', 'DATE_FORMAT(n.horodateur, \'%d/%m/%Y\')', 'n.objet', 'CASE WHEN LENGTH(n.note) > 30 THEN CONCAT(SUBSTRING(n.note, 1, 30), \'...\') ELSE n.note END');
		$where = array('n.redacteur_id' => $this->session->userdata('id'));
		$having_unescaped = 'count(DISTINCT nv.utilisateur_id) < count(DISTINCT nw.utilisateur_id) AND count(nr.note_id) = 0';

		$this->db->select($select, FALSE);
		$this->db->from('note AS n');
		$this->db->join('note_workflow AS nw', 'nw.note_id = n.id');
		$this->db->join('note_validation AS nv', 'nv.note_id = nw.note_id', 'left');
		$this->db->join('note_refus AS nr', 'nr.note_id = nw.note_id', 'left');
		$this->db->where($where);
		if (is_array($filter)) $this->db->where($filter);
		if (isset($objet)) $this->db->like('objet', $objet);
		$this->db->group_by('n.id');
		$this->db->having($having_unescaped, NULL, FALSE);
		$this->db->order_by('n.id', 'DESC');
		$this->db->offset($offset);
		$this->db->limit($limit);
		$query = $this->db->get();

		return $query;
	}

	public function find_notes_recues($filter = NULL, $offset = 0, $limit = 99999)
	{
		if (is_array($filter) && array_key_exists('n.objet', $filter)) {
			$objet = $filter['n.objet'];
			unset($filter['n.objet']);
		}

		$lien_debut = $this->lien_model->detail_note_debut();
		$lien_fin = $this->lien_model->detail_note_fin();

		$select = array('CONCAT(\''.$lien_debut.'\',n.id,\''.$lien_fin.'\')', 'n.id', 'DATE_FORMAT(n.horodateur, \'%d/%m/%Y\')', 'CONCAT(u.nom,\' \',u.prenom)', 'n.objet', 'CASE WHEN LENGTH(n.note) > 30 THEN CONCAT(SUBSTRING(n.note, 1, 30), \'...\') ELSE n.note END');
		$where_in = 'SELECT note.id FROM note JOIN note_workflow ON note_workflow.note_id = note.id WHERE note_workflow.utilisateur_id = '.$this->session->userdata('id');
		$having_unescaped = 'count(DISTINCT nv.utilisateur_id) < count(DISTINCT nw.utilisateur_id) AND count(nr.note_id) = 0';

		$this->db->select($select, FALSE);
		$this->db->from('note AS n');
		$this->db->join('utilisateur AS u', 'n.redacteur_id = u.id');
		$this->db->join('note_workflow AS nw', 'nw.note_id = n.id');
		$this->db->join('note_validation AS nv', 'nv.note_id = nw.note_id', 'left');
		$this->db->join('note_refus AS nr', 'nr.note_id = nw.note_id', 'left');
		$this->db->where_in('n.id', $where_in, FALSE);
		if (is_array($filter)) $this->db->where($filter);
		if (isset($objet)) $this->db->like('objet', $objet);
		$this->db->group_by('n.id');
		$this->db->having($having_unescaped, NULL, FALSE);
		$this->db->order_by('n.id', 'DESC');
		$this->db->offset($offset);
		$this->db->limit($limit);
		$query = $this->db->get();

		return $query;
	}

	public function find_notes_terminees($filter = NULL, $offset = 0, $limit = 99999)
	{
		if (is_array($filter) && array_key_exists('n.objet', $filter)) {
			$objet = $filter['n.objet'];
			unset($filter['n.objet']);
		}

		$lien_debut = $this->lien_model->detail_note_debut();
		$lien_fin = $this->lien_model->detail_note_fin();

		$select = array('CONCAT(\''.$lien_debut.'\',n.id,\''.$lien_fin.'\')', 'n.id', 'DATE_FORMAT(n.horodateur, \'%d/%m/%Y\')', 'CONCAT(u.nom,\' \',u.prenom)', 'n.objet', 'CASE WHEN LENGTH(n.note) > 30 THEN CONCAT(SUBSTRING(n.note, 1, 30), \'...\') ELSE n.note END');
		$where = array('n.redacteur_id' => $this->session->userdata('id'));
		$or_where_in = 'SELECT note.id FROM note JOIN note_workflow ON note_workflow.note_id = note.id WHERE note_workflow.utilisateur_id = '.$this->session->userdata('id');
		$having_unescaped = 'count(DISTINCT nv.utilisateur_id) = count(DISTINCT nw.utilisateur_id)';

		$this->db->select($select, FALSE);
		$this->db->from('note AS n');
		$this->db->join('utilisateur AS u', 'n.redacteur_id = u.id');
		$this->db->join('note_workflow AS nw', 'nw.note_id = n.id');
		$this->db->join('note_validation AS nv', 'nv.note_id = nw.note_id', 'left');
		$this->db->group_start();
		$this->db->where($where);
		$this->db->or_where_in('n.id', $or_where_in, FALSE);
		$this->db->group_end();
		if (is_array($filter)) $this->db->where($filter);
		if (isset($objet)) $this->db->like('objet', $objet);
		$this->db->group_by('n.id');
		$this->db->having($having_unescaped, NULL, FALSE);
		$this->db->order_by('n.id', 'DESC');
		$this->db->offset($offset);
		$this->db->limit($limit);
		$query = $this->db->get();

		return $query;
	}

	public function find_notes_refusees($filter = NULL, $offset = 0, $limit = 99999)
	{
		if (is_array($filter) && array_key_exists('n.objet', $filter)) {
			$objet = $filter['n.objet'];
			unset($filter['n.objet']);
		}

		$lien_debut = $this->lien_model->detail_note_debut();
		$lien_fin = $this->lien_model->detail_note_fin();

		$select = array('CONCAT(\''.$lien_debut.'\',n.id,\''.$lien_fin.'\')', 'n.id', 'DATE_FORMAT(n.horodateur, \'%d/%m/%Y\')', 'CONCAT(u.nom,\' \',u.prenom)', 'n.objet', 'CASE WHEN LENGTH(n.note) > 30 THEN CONCAT(SUBSTRING(n.note, 1, 30), \'...\') ELSE n.note END', 'CONCAT(ur.nom,\' \',ur.prenom)');
		$where = array('n.redacteur_id' => $this->session->userdata('id'));
		$or_where_in = 'SELECT note.id FROM note JOIN note_workflow ON note_workflow.note_id = note.id WHERE note_workflow.utilisateur_id = '.$this->session->userdata('id');
		$having_unescaped = 'count(nr.note_id) > 0';

		$this->db->select($select, FALSE);
		$this->db->from('note AS n');
		$this->db->join('utilisateur AS u', 'n.redacteur_id = u.id');
		$this->db->join('note_workflow AS nw', 'nw.note_id = n.id');
		$this->db->join('note_refus AS nr', 'nr.note_id = nw.note_id');
		$this->db->join('utilisateur AS ur', 'nr.utilisateur_id = ur.id');
		$this->db->group_start();
		$this->db->where($where);
		$this->db->or_where_in('n.id', $or_where_in, FALSE);
		$this->db->group_end();
		if (is_array($filter)) $this->db->where($filter);
		if (isset($objet)) $this->db->like('objet', $objet);
		$this->db->group_by('n.id');
		$this->db->having($having_unescaped, NULL, FALSE);
		$this->db->order_by('n.id', 'DESC');
		$this->db->offset($offset);
		$this->db->limit($limit);
		$query = $this->db->get();

		return $query;
	}

	// finds the notes the active user has to validate
	public function find_notes_a_valider($user_id)
	{
		$lien_debut = $this->lien_model->detail_note_debut();
		$lien_fin = $this->lien_model->detail_note_fin();

		$query = '
			SELECT CONCAT(\''.$lien_debut.'\',n.id,\''.$lien_fin.'\') AS detail,
			n.id,
			DATE_FORMAT(n.horodateur, "%d/%m/%Y à %Hh%i") AS horodateur,
			CONCAT(u.nom,\' \',u.prenom) AS redacteur,
			n.objet,
			n.note
			FROM note_workflow
			JOIN 
				(SELECT nw.note_id, count(DISTINCT nv.utilisateur_id) + 1 AS etape_suivante
				FROM `note_workflow` AS `nw` 
				LEFT JOIN `note_validation` AS `nv` ON `nv`.`note_id` = `nw`.`note_id`
				AND `nv`.`utilisateur_id` = `nw`.`utilisateur_id` 
				LEFT JOIN `note_refus` AS `nr` ON `nr`.`note_id` = `nw`.`note_id` 
				AND `nr`.`utilisateur_id` = `nw`.`utilisateur_id` 
				GROUP BY nw.note_id
				HAVING count(DISTINCT nv.utilisateur_id) < count(DISTINCT nw.utilisateur_id) 
				AND count(nr.note_id) = 0) AS temp
			ON temp.note_id = note_workflow.note_id
			AND note_workflow.etape = temp.etape_suivante
			JOIN note AS n ON n.id = note_workflow.note_id
			JOIN utilisateur AS u ON n.redacteur_id = u.id
			WHERE note_workflow.utilisateur_id = 
		'.$user_id;

		return $this->db->query($query);
	}

	/*
	* deprecated
	*
	*
	// find the basic note detail
	public function find_note_detail($note_id)
	{
		$select = array(
			'n.id',
			'n.note',
			'LENGTH(n.note)-LENGTH(replace(n.note,char(10),\'\'))+1 AS nombre_lignes',
			'DATE_FORMAT(n.horodateur, "%d/%m/%Y à %Hh%i") AS horodateur',
			'n.redacteur_id',
			'CONCAT(u.nom,\' \',u.prenom) AS redacteur',
			'CASE WHEN nu.id IS NULL THEN 0 ELSE 1 END AS has_upload',
			'count(DISTINCT nu.id) AS uploads_number',
			//'GROUP_CONCAT(DISTINCT nw.utilisateur_id,\',\') AS workflow_id',
			//'GROUP_CONCAT(DISTINCT CONCAT(uw.nom,\' \',uw.prenom),\',\') AS workflow',
			'CASE WHEN count(DISTINCT nv.utilisateur_id) = count(DISTINCT nw.utilisateur_id) THEN 1 ELSE 0 END AS validated',
			//'GROUP_CONCAT(DISTINCT nv.utilisateur_id,\',\') AS validateurs_id',
			//'GROUP_CONCAT(DISTINCT CONCAT(uv.nom,\' \',uv.prenom),\',\') AS validateurs',
			'CASE WHEN count(DISTINCT nr.utilisateur_id) = 0 THEN 0 ELSE 1 END AS refused',
			'(SELECT CONCAT(u.nom,\' \',u.prenom) FROM note_refus AS nr JOIN utilisateur AS u ON u.id = nr.utilisateur_id WHERE nr.note_id = '.$note_id.') AS refuseur',
			'(SELECT note_workflow.utilisateur_id
				FROM note_workflow 
				LEFT JOIN note_validation ON note_validation.utilisateur_id = note_workflow.utilisateur_id 
				AND note_validation.note_id = note_workflow.note_id
				WHERE note_workflow.note_id = '.$note_id.'
				AND note_validation.utilisateur_id IS NULL
				GROUP BY note_workflow.utilisateur_id
				ORDER BY note_workflow.etape
				LIMIT 1) AS valideur_attendu',
			'(SELECT CONCAT(utilisateur.email_nom,\'@\',email_domaine.domaine)
				FROM note_workflow 
				LEFT JOIN note_validation ON note_validation.utilisateur_id = note_workflow.utilisateur_id 
				AND note_validation.note_id = note_workflow.note_id
				LEFT JOIN utilisateur ON utilisateur.id = note_workflow.utilisateur_id
				LEFT JOIN email_domaine ON email_domaine.id = utilisateur.email_domaine_id
				WHERE note_workflow.note_id = '.$note_id.'
				AND note_validation.utilisateur_id IS NULL
				GROUP BY note_workflow.utilisateur_id
				ORDER BY note_workflow.etape
				LIMIT 1) AS email_valideur_attendu',
			'(SELECT note_workflow.etape
				FROM note_workflow 
				LEFT JOIN note_validation ON note_validation.utilisateur_id = note_workflow.utilisateur_id 
				AND note_validation.note_id = note_workflow.note_id
				LEFT JOIN utilisateur ON utilisateur.id = note_workflow.utilisateur_id
				LEFT JOIN email_domaine ON email_domaine.id = utilisateur.email_domaine_id
				WHERE note_workflow.note_id = '.$note_id.'
				AND note_validation.utilisateur_id IS NULL
				GROUP BY note_workflow.utilisateur_id
				ORDER BY note_workflow.etape
				LIMIT 1) AS etape_actuelle'

		);

		$this->db->select($select, FALSE);
		$this->db->from('note AS n');
		$this->db->join('note_upload AS nu', 'n.id = nu.note_id', 'left');
		$this->db->join('utilisateur AS u', 'n.redacteur_id = u.id');
		$this->db->join('note_workflow AS nw', 'nw.note_id = n.id');
		$this->db->join('utilisateur AS uw', 'nw.utilisateur_id = uw.id');
		$this->db->join('note_validation AS nv', 'nv.note_id = nw.note_id AND nv.utilisateur_id = nw.utilisateur_id', 'left');
		$this->db->join('utilisateur AS uv', 'nv.utilisateur_id = uv.id', 'left');
		$this->db->join('note_refus AS nr', 'nr.note_id = nw.note_id AND nr.utilisateur_id = nw.utilisateur_id', 'left');
		$this->db->join('utilisateur AS ur', 'nr.utilisateur_id = ur.id', 'left');
		$this->db->where('n.id', $note_id);
		$this->db->group_by('n.id');
		$this->db->order_by('n.id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get();

		return $query->num_rows() != 1 ? FALSE : $query->row();
	}
	*/

	// find the basic note detail
	public function find_note_detail($note_id)
	{
		$select = array(
			'n.id',
			'n.note',
			'n.objet',
			'LENGTH(n.note)-LENGTH(replace(n.note,char(10),\'\'))+1 AS nombre_lignes',
			'DATE_FORMAT(n.horodateur, "%d/%m/%Y à %Hh%i") AS horodateur',
			'n.redacteur_id',
			'CONCAT(u.nom,\' \',u.prenom) AS redacteur',
			'CONCAT(u.email_nom,\'@\',e.domaine) AS redacteur_email',
			'CASE WHEN COUNT(DISTINCT nu.id) > 0 THEN 1 ELSE 0 END AS has_uploads',
			'COUNT(DISTINCT nu.id) AS nombre_uploads',
			'CASE WHEN COUNT(DISTINCT nv.utilisateur_id) = COUNT(DISTINCT nw.utilisateur_id) THEN 1 ELSE 0 END AS validated',
			'CASE WHEN COUNT(DISTINCT nr.utilisateur_id) = 0 THEN 0 ELSE 1 END AS refused'
		);

		$this->db->select($select, FALSE);
		$this->db->from('note AS n');
		$this->db->join('note_upload AS nu', 'n.id = nu.note_id', 'left');
		$this->db->join('utilisateur AS u', 'n.redacteur_id = u.id');
		$this->db->join('email_domaine AS e', 'e.id = u.email_domaine_id');
		$this->db->join('note_workflow AS nw', 'nw.note_id = n.id');
		$this->db->join('note_validation AS nv', 'nv.note_id = nw.note_id AND nv.utilisateur_id = nw.utilisateur_id', 'left');
		$this->db->join('note_refus AS nr', 'nr.note_id = nw.note_id AND nr.utilisateur_id = nw.utilisateur_id', 'left');
		$this->db->where('n.id', $note_id);
		$this->db->group_by('n.id');
		$this->db->limit(1);
		$query = $this->db->get();

		return $query->num_rows() != 1 ? FALSE : $query->row();
	}

}