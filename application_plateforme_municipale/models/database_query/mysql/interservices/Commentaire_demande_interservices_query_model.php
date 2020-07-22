<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Commentaire_demande_interservices_query_model extends MY_Model
{

	protected $table = 'commentaire_demande_interservices';

	/*
	* trouve les commentaires associés à une demande interservices
	* classé par date de post croissante
	* pour un affichage <input> dans une boucle
	* ainsi que le nombre de lignes pour que le textarea s'adapte en hauteur
	*/
	public function find_for_detail($demande_interservices_id)
	{
		$select = array('DATE_FORMAT(c.horodateur, "%d/%m/%Y à %Hh%i") AS horodateur', 'CONCAT(u.nom, \' \', u.prenom) AS agent', 'c.commentaire', 'LENGTH(c.commentaire)-LENGTH(replace(c.commentaire,char(10),\'\'))+1 AS nombreLignes');

		$this->db->select($select, FALSE);
		$this->db->from('commentaire_demande_interservices AS c');
		$this->db->join('utilisateur AS u', 'u.id = c.utilisateur_id');
		$this->db->where('c.demande_interservices_id', $demande_interservices_id);
		$this->db->order_by('c.horodateur', 'ASC');
		$query = $this->db->get();

		return ($query->num_rows() == 0) ? array() : $query->result();
	}

	/*
	* trouve le dernier commentaire et les détails associés à celui-ci
	*/
	public function find_last_comment($demande_interservices_id)
	{
		$select = array('DATE_FORMAT(c.horodateur, "%d/%m/%Y à %Hh%i") AS horodateur', 'CONCAT(u.nom, \' \', u.prenom) AS commentateur', 'u.id AS commentateur_id', 'c.commentaire', 'LENGTH(c.commentaire)-LENGTH(replace(c.commentaire,char(10),\'\'))+1 AS nombreLignes');

		$this->db->select($select, FALSE);
		$this->db->from('commentaire_demande_interservices AS c');
		$this->db->join('utilisateur AS u', 'u.id = c.utilisateur_id');
		$this->db->where('c.demande_interservices_id', $demande_interservices_id);
		$this->db->order_by('c.id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get();

		return ($query->num_rows() == 0) ? array() : $query->row();
	}

}