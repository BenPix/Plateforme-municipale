<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Note_commentaire_query_model extends MY_Model
{

	protected $table = 'note_commentaire';

	/*
	* trouve les commentaires associés à une note
	* classé par date de post croissante
	* pour un affichage <input> dans une boucle
	* ainsi que le nombre de lignes pour que le textarea s'adapte en hauteur
	*/
	public function find_comment_detail($note_id)
	{
		$select = array('DATE_FORMAT(nc.horodateur, "%d/%m/%Y à %Hh%i") AS horodateur', 'CONCAT(u.nom, \' \', u.prenom) AS utilisateur', 'nc.commentaire', 'LENGTH(nc.commentaire)-LENGTH(replace(nc.commentaire,char(10),\'\'))+1 AS nombre_lignes');

		$this->db->select($select, FALSE);
		$this->db->from('note_commentaire AS nc');
		$this->db->join('utilisateur AS u', 'u.id = nc.utilisateur_id');
		$this->db->where('nc.note_id', $note_id);
		$this->db->order_by('nc.horodateur', 'ASC');
		$query = $this->db->get();

		return $query;
	}
}
