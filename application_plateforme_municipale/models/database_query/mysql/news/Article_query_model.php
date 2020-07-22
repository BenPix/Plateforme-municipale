<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Article_query_model extends MY_Model
{

	protected $table = 'article';

	/*
	 *	Récupère des articles dans l'ordre du plus récent au plus vieux.
	 */
	public function find_news($categories = array())
	{
		$this->db->select();
		$this->db->from($this->table);
		$this->db->join('article_a_valider AS av', 'av.article_id = article.id', 'left');
		$this->db->join('appartenance_categorie_article AS app', 'app.article_id = article.id');
		$this->db->where(array('av.article_id' => NULL));
		if ( ! empty($categories) ) $this->db->where_in('app.categorie_id', $categories);
		$this->db->group_by('article.id');
		$this->db->order_by('date_creation,id', 'DESC');
		$query = $this->db->get();

		return $query;
	}
	
	/*
	 *	Récupère des articles récents.
	 */
	public function find_recent_news($date_max, $categories = array())
	{
		$this->db->select();
		$this->db->from($this->table);
		$this->db->join('article_a_valider AS av', 'av.article_id = article.id', 'left');
		$this->db->join('appartenance_categorie_article AS app', 'app.article_id = article.id');
		$this->db->where(array('date_creation > ' => $date_max));
		$this->db->where('av.article_id IS NULL');
		if ( ! empty($categories) ) $this->db->where_in('app.categorie_id', $categories);
		$this->db->group_by('article.id');
		$this->db->order_by('date_creation,id', 'DESC');
		$query = $this->db->get();

		return $query->num_rows() == 0 ? array() : $query->result();
	}
	/*
	 *	trouve les détails d'un article.
	*/
	 public function find_detail($id) {
		$select = array('a.id', 'a.titre', 'a.description', 'a.contenu', 'a.commentaire_autorise', 'a.redacteur_id', 'a.nom_image', 'a.nom_banniere', 'DATE_FORMAT(a.date_creation,"%d/%m/%Y") AS date_creation', 'DATE_FORMAT(a.date_suppression,"%d/%m/%Y") AS date_suppression', 'a.date_creation AS date_creation_for_form', 'a.date_suppression AS date_suppression_for_form', 'CONCAT(u.nom,\' \',u.prenom) AS redacteur', 'CONCAT(u.email_nom,\'@\',e.domaine) AS email_redacteur', 'IF (av.article_id IS NULL, \'0\', \'1\') AS a_valider');

		$this->db->select($select, FALSE);
		$this->db->from('article AS a');
		$this->db->join('utilisateur AS u', 'u.id = a.redacteur_id');
		$this->db->join('email_domaine AS e', 'u.email_domaine_id = e.id');
		$this->db->join('article_a_valider AS av', 'av.article_id = a.id', 'left');
		$this->db->where(array('a.id' => $id));
		$this->db->limit(1);
		$query = $this->db->get();

		return $query->num_rows() == 0 ? FALSE : $query->row();
	}

	public function table_articles_a_valider($responsable_id)
	{
		// lien pour valider la demande
		$lienPrevisualisationDebut = $this->lien_model->previsualiser_news_debut();
		$lienPrevisualisationFin = $this->lien_model->previsualiser_news_fin();
		// select des colonnes a afficher
		$select = array('CONCAT(\''.$lienPrevisualisationDebut.'\',a.id,\''.$lienPrevisualisationFin.'\')  AS previsualiser', 'DATE_FORMAT(a.date_creation,"%d/%m/%Y") AS date_creation', 'CONCAT(u.nom,\' \',u.prenom) AS redacteur', 'a.titre', 'a.description', 'DATE_FORMAT(a.date_suppression,"%d/%m/%Y") AS date_suppression');

		$this->db->select($select, FALSE);
		$this->db->from('article AS a');
		$this->db->join('utilisateur AS u', 'u.id = a.redacteur_id');
		$this->db->join('pole AS p', 'p.id = u.pole_id');
		$this->db->join('article_a_valider AS av', 'a.id = av.article_id');
		$this->db->where(array('p.responsable_id' => $responsable_id));
		$query = $this->db->get();

		return $query;
	}

	public function read_for_delete()
	{
		$this->db->select(array('article.id', 'article.nom_banniere', 'article.nom_image'));
		$this->db->from($this->table);
		$this->db->where('article.date_suppression <= CURDATE()', NULL, FALSE);
		$query = $this->db->get();

		return $query->result();
	}

	public function create_news_syndicale($article_id)
	{
		return (bool) $this->db
		->set(array('article_id' => $article_id))
		->insert('article_syndical');
	}

}