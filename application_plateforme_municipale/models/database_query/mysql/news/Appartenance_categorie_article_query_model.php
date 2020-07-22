<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Appartenance_categorie_article_query_model extends MY_Model
{

	protected $table = 'appartenance_categorie_article';

	/*
	* génère un array des catégories associées à l'article
	* renvoie un array avec pour $key l'id de la categorie et pour $value son nom
	* parfait pour être utilisé comme menu dropdown
	*/
	public function find_categories_article($article_id)
	{
		$this->db->select('ca.nom');
		$this->db->from('appartenance_categorie_article AS app');
		$this->db->join('article AS a', 'app.article_id = a.id', 'left');
		$this->db->join('categorie_article AS ca', 'app.categorie_id = ca.id', 'left');
		$this->db->where(array('app.article_id' => $article_id));
		$query = $this->db->get();

		$liste = array_column($query->result_array(), 'nom');

		foreach ($liste as $key => $value) {
			$liste[$value] = $value;
			unset($liste[$key]);
		}

		return $liste;
	}
	/*
	* génère un array des catégories associées à chaque article
	* renvoie un array contenant un autre array représentant la ligne
	* ce 2e array contient article_id (pour vérifier a quel article il appartient) et cat_nom (pour l'afficher)
	*/
	public function read_news_categories()
	{
		$select = array('a.id', 'ca.nom');

		$this->db->select($select);
		$this->db->from('appartenance_categorie_article AS app');
		$this->db->join('article AS a', 'app.article_id = a.id', 'left');
		$this->db->join('categorie_article AS ca', 'app.categorie_id = ca.id', 'left');
		$query = $this->db->get();

		return $query->result();
	}

}