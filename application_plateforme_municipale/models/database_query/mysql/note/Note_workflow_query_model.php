<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Note_workflow_query_model extends MY_Model
{

	protected $table = 'note_workflow';

	public function find_workflow_detail_for_table($note_id)
	{
		$select = array(
			'nw.etape',
			'nw.utilisateur_id',
			'CONCAT(uw.nom," ",uw.prenom) AS utilisateur',
			'CASE WHEN nv.utilisateur_id IS NULL THEN 0 ELSE 1 END AS validated',
			'DATE_FORMAT(nv.horodateur, "%d/%m/%Y à %Hh%i") AS validation_date',
			'CASE WHEN nr.utilisateur_id IS NULL THEN 0 ELSE 1 END AS refused',
			'DATE_FORMAT(nr.horodateur, "%d/%m/%Y à %Hh%i") AS refusal_date'
		);

		$this->db->select($select, FALSE);
		$this->db->from('note_workflow AS nw');
		$this->db->join('utilisateur AS uw', 'nw.utilisateur_id = uw.id');
		$this->db->join('note_validation AS nv', 'nv.utilisateur_id = nw.utilisateur_id AND nv.note_id = nw.note_id', 'left');
		$this->db->join('note_refus AS nr', 'nr.utilisateur_id = nw.utilisateur_id AND nr.note_id = nw.note_id', 'left');
		$this->db->where('nw.note_id', $note_id);
		$this->db->group_by('nw.utilisateur_id');
		$this->db->order_by('nw.etape');
		$query = $this->db->get();

		return $query;
	}

	public function find_workflow_detail($note_id)
	{
		$select = array(
			'nw.etape',
			'nw.utilisateur_id AS workflow_utilisateur_id',
			'CONCAT(uw.nom," ",uw.prenom) AS workflow_utilisateur',
			'CONCAT(uw.email_nom,"@",ew.domaine) AS workflow_utilisateur_email',
			'CASE WHEN nv.utilisateur_id IS NULL THEN 0 ELSE 1 END AS validated',
			'nv.utilisateur_id AS valideur_id',
			'CONCAT(uv.nom," ",uv.prenom) AS valideur',
			'DATE_FORMAT(nv.horodateur, "%d/%m/%Y à %Hh%i") AS validation_datetime',
			'DATE_FORMAT(nv.horodateur, "%d/%m/%Y") AS validation_date',
			'CASE WHEN nr.utilisateur_id IS NULL THEN 0 ELSE 1 END AS refused',
			'nr.utilisateur_id AS refuseur_id',
			'CONCAT(ur.nom," ",ur.prenom) AS refuseur',
			'DATE_FORMAT(nr.horodateur, "%d/%m/%Y à %Hh%i") AS refusal_datetime'
		);

		$this->db->select($select, FALSE);
		$this->db->from('note_workflow AS nw');
		$this->db->join('utilisateur AS uw', 'nw.utilisateur_id = uw.id');
		$this->db->join('email_domaine AS ew', 'ew.id = uw.email_domaine_id');
		$this->db->join('note_validation AS nv', 'nv.utilisateur_id = nw.utilisateur_id AND nv.note_id = nw.note_id', 'left');
		$this->db->join('utilisateur AS uv', 'nv.utilisateur_id = uv.id', 'left');
		$this->db->join('note_refus AS nr', 'nr.utilisateur_id = nw.utilisateur_id AND nr.note_id = nw.note_id', 'left');
		$this->db->join('utilisateur AS ur', 'nr.utilisateur_id = ur.id', 'left');
		$this->db->where('nw.note_id', $note_id);
		$this->db->order_by('nw.etape');
		$query = $this->db->get();

		return $query;
	}
}
