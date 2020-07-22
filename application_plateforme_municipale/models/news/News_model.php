<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class News_model extends CI_Model
{

	public function __construct() {
		parent::__construct();

		// the database is built, we check for the module interservices is activated, to load its models
		if ( ! $this->settings_query_model->empty_database() ) {
			if (in_array('news', $this->connexion_model->check_modules())) {
				$this->load->model('database_query/mysql/categorie_article_query_model');
				$this->load->model('database_query/mysql/news/appartenance_categorie_article_query_model');
				$this->load->model('database_query/mysql/news/appartenance_categorie_utilisateur_query_model');
				$this->load->model('database_query/mysql/news/article_a_valider_query_model');
				$this->load->model('database_query/mysql/news/article_query_model');
			}
		}
	}

	// building the home page according to the news modules activation
	public function building_home_page()
	{
		// define which categories may be displayed for this user
		$categories_ids = $this->define_categories_to_display();

		// finding news to display and each news categories
		$data['articles'] = $this->article_query_model->find_news($categories_ids)->result();
		$data['articles_categories'] = $this->appartenance_categorie_article_query_model->read_news_categories();

		$data['articles_recents'] = $this->find_recent_news($categories_ids);

		// if user's rank is redacteur, links for delete and update are displayed
		if ($this->session->userdata('rang') == 'redacteur') {
			$data['liens']['lien_delete_debut'] = $this->lien_model->delete_news_debut();
			$data['liens']['lien_delete_fin'] = $this->lien_model->delete_news_fin();
		}

		return $data;
	}

	// define which categories may be displayed for this user
	// display conditions = user rank / pole belonging / special access
	private function define_categories_to_display()
	{
		// admin and redacteur acces to all
		if (in_array($this->session->userdata('rang'), array('admin', 'redacteur')))
			return array();

		$categories_ids = $this->appartenance_categorie_utilisateur_query_model->find_categories($this->session->userdata('id'));
		array_push($categories_ids, $this->pole_query_model->find(array('id' => $this->session->userdata('pole_id')))->categorie_id);

		return $categories_ids;
	}

	// finds the recent news, according to a fixed duration
	private function find_recent_news($categories_ids = FALSE)
	{
		if ($categories_ids === FALSE) $categories_ids = $this->define_categories_to_display();

		$date_max = new DateTime();
		$date_max = $date_max->modify('-1 month');

		return $this->article_query_model->find_recent_news($date_max->format('Y-m-d'), $categories_ids);
	}

	// find the news details to display in the detail page
	public function find_news_detail($article_id)
	{
		$article = $this->article_query_model->find_detail($article_id);
		$categories = $this->categorie_article_query_model->read();
		$categories_article = $this->appartenance_categorie_article_query_model->find_categories_article($article_id);
		$articles_recents = $this->find_recent_news();
		
		$data = array(
			'titre' => 'Article - '.$article->titre,
			'article' => $article,
			'categories' => $categories,
			'categories_article' => $categories_article,
			'articles_recents' => $articles_recents,
			'redacteur' => $this->session->userdata('rang') == 'redacteur',
			'delete' => FALSE
		);

		// form to update the news, only displayed to the redacteur
		if ($this->session->userdata('rang') == 'redacteur') {
			$data['submit'] = array('value'=>'Modifier','type'=>'submit');
			$data['action'] = 'accueil/update_news/'.$article_id;
		}

		return $data;
	}

	// returns TRUE if the news exists and may be displayed for this user, else FALSE
	public function check_news($article_id)
	{
		if ($this->article_query_model->find_detail($article_id) === FALSE)
			return FALSE;

		// finds the allowed news to display
		$categories_ids = $this->define_categories_to_display();
		$news = $this->article_query_model->find_news($categories_ids)->result_array();

		return in_array($article_id, array_column($news, 'id'));
	}

	// check uploads and delete them if not ok, and returns an error message
	public function check_uploads($config)
	{
		$this->upload->initialize($config);

		$data_uploads['error'] = '';
		$data_uploads['check'] = TRUE;
		$data_uploads['userfile_is_empty'] = $userfile_is_empty = empty($_FILES['image_article']['name']) || $_FILES['image_article']['size'] == 0;

		if ( ! $userfile_is_empty ) {
			$userfile_is_uploaded = $this->upload->do_upload('image_article');

			if ( ! $userfile_is_uploaded ) {
				$data_uploads['error'] .= '<p class="error">Erreur sur la pièce jointe '.$this->upload->data('file_name').'</p>';
				$data_uploads['error'] .= $this->upload->display_errors('<p class="error">', '</p>');
				$data_uploads['check'] = FALSE;
			} else {
				$data_uploads['userfile'] = array(
					'file_name' => $this->upload->data('file_name'),
					'file_type' => $this->upload->data('file_type'),
					'file_size' => $this->upload->data('file_size')
				);
			}
		}

		return $data_uploads;
	}

	// register the news
	public function register_news($data_sent, $data_uploads, $categories)
	{
		$this->db->trans_start();

		// resized image creation for the banner
		$image_banniere = $data_uploads['userfile_is_empty'] ? 'default_banniere_article.jpg' : $this->news_model->image_creation($this->upload->data());
		// define images names
		$nom_banniere = is_array($image_banniere) ? $image_banniere['file_name'] : $image_banniere;
		$nom_image = $data_uploads['userfile_is_empty'] || $data_uploads['check'] === FALSE ? 'default_image_article.jpg' : $data_uploads['userfile']['file_name'];

		$data_sent += array(
			'commentaire_autorise' => 0, // not allowed for now, potential update in the future
			'nom_image' => $nom_image,
			'nom_banniere' => $nom_banniere
		);

		// protection against XSS injection with html_purifier
		$this->load->helper('htmlpurifier');
		$data_sent['contenu'] = html_purify($data_sent['contenu'], 'contenu_news');

		// inserting data
		$returning_data['article'] = $this->insert_news($data_sent, $categories);

		// data for mailing
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'redacteur', 'admin')) )
			$returning_data['user'] = $this->utilisateur_query_model->infos_utilisateur($this->session->userdata('id'));

		$this->db->trans_complete();

		$returning_data['succed'] = $this->db->trans_status();

		return $returning_data;
	}

	// do the inserts in the database
	private function insert_news($data_sent, $categories) {
		$article_id = $this->article_query_model->create_and_find_id($data_sent, array('date_creation' => 'CURDATE()'));

		foreach ($categories as $categorie_id)
			$this->appartenance_categorie_article_query_model->create(array('article_id' => $article_id, 'categorie_id' => $categorie_id));

		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'redacteur', 'admin')) )
			$this->article_a_valider_query_model->create(array('article_id' => $article_id));

		return $this->article_query_model->find_detail($article_id);
	}

	// creates a resized image from the uploaded image to fill the banner image
	private function image_creation($image_array)
	{
		if ( ! is_array($image_array) || empty($image_array))
			return FALSE;
		if ($image_array['is_image'] != 1)
			return FALSE;

		$thumb_width = 1140;
  		$thumb_height = 400;
  		$thumb_name = '-thumb';

  		$gallery_path = 'uploads/news/banniere/';

		// load de la lib + config
		$this->load->library('image_lib');
		$config['image_library'] = 'gd2';
		$config['source_image'] = $image_array['full_path'];
		$config['maintain_ratio'] = FALSE;

		/*
		* algo pour réaliser le CROP de l'image
		*/
		//calculate the source image's ratio
		$source_ratio = $image_array['image_width'] / $image_array['image_height'];
		//calculate the ratio of the new image
		$new_ratio = $thumb_width / $thumb_height;
		//if the source image's ratio is not the same with the new image's ratio, then we do the cropping. else we just do a resize
		if($source_ratio!=$new_ratio)
		{
		  // if the new image' ratio is bigger than the source image's ratio or the new image is a square and the source image's height is bigger than it's width, we will take source's width as the width of the image
		  if($new_ratio > $source_ratio || (($new_ratio == 1) && ($source_ratio < 1)))
		  {
		    $config['width'] = $image_array['image_width'];
		    $config['height'] = round($image_array['image_width']/$new_ratio);
		    // now we will tell the library to crop from a certain y axis coordinate so that the new image is taken from the vertical center of the source image
		    $config['y_axis'] = round(($image_array['image_height'] - $config['height'])/2);
		    $config['x_axis'] = 0;
		  }
		  else
		  {
		    $config['width'] = round($image_array['image_height'] * $new_ratio);
		    $config['height'] = $image_array['image_height'];
		    // now we will tell the library to crop from a certain x axis coordinate so that the new image is taken from the horizontal center of the source image
		    $size_config['x_axis'] = round(($image_array['image_width'] - $config['width'])/2);
		    $size_config['y_axis'] = 0;
		  }
		}

		/*
		* on renomme le fichier avec un boucle pour éviter les doublons
		*/
		$thumb_path = $gallery_path.$image_array['raw_name'].$thumb_name.$image_array['file_ext'];
		$new_thumb = $image_array['raw_name'].$thumb_name.$image_array['file_ext'];
		if(file_exists($thumb_path))
		{
		  // we will give it 100 tries. if after 100 tries it can't find a suitable name, then the problem is your imagination in naming the files that you've uploaded
		  for($i=1;$i<=100;$i++)
		  {
		    $new_thumb = $image_array['raw_name'].'-'.$i.$thumb_name.$image_array['file_ext'];
		    if(!file_exists($new_thumb))
		    {
		      $thumb_path = $gallery_path.$new_thumb;
		    }
		  }
		}
		$config['new_image'] = $thumb_path;
		// on garde 100% de qualité
		$config['quality'] = '100%';

		// on intitialise la config et crop l'image pour de bon, en enregistrant les erreurs éventuelles
		$this->image_lib->initialize($config);
		$errors = array();
		// doing the cropping
		if(!$this->image_lib->crop())
		{
		  // if errors occured, we must see what those errors were
		  $errors[] = $this->image_lib->display_errors();
		}
		// on vide la lib
		$this->image_lib->clear();
		// et enfin, on crée la variable de la nouvelle image
		$new_images = array('file_name'=>$new_thumb,'path'=>$config['new_image'],'errors'=>$errors);

		return $new_images;
	}

	// update the news
	public function update_news($article_id, $data_sent, $categories)
	{
		$this->db->trans_start();

		// protection against XSS injection with html_purifier
		$this->load->helper('htmlpurifier');
		$data_sent['contenu'] = html_purify($data_sent['contenu'], 'contenu_news');

		$this->update_data_news($article_id, $data_sent, $categories);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// update the news data in the database
	private function update_data_news($article_id, $data_sent, $categories)
	{
		$this->article_query_model->update(array('id' => $article_id), $data_sent);

		// delete and then create, because it's simplier than update
		$this->appartenance_categorie_article_query_model->delete(array('article_id' => $article_id));

		foreach ($categories as $categorie_id)
			$this->appartenance_categorie_article_query_model->create(array('article_id' => $article_id, 'categorie_id' => $categorie_id));
	}

	// find the news details to display in the detail page for a deleting
	public function find_news_detail_for_delete($article_id)
	{
		$article = $this->article_query_model->find_detail($article_id);
		$articles_recents = $this->find_recent_news();
		
		$data = array(
			'titre' => 'Article - '.$article->titre,
			'article' => $article,
			'articles_recents' => $articles_recents,
			'redacteur' => FALSE, // to let close the textbox
			'delete' => TRUE,
			'submit' => array('value'=>'Supprimer','type'=>'submit'),
			'action' => 'accueil/delete_news_go/'.$article_id,
		);

		return $data;
	}

	// delete the uploads and the data in the database
	// the function accepts string or integer for article_id, but also a stdClass Object (= result of a database query)
	public function delete_news($article_id)
	{
		$this->db->trans_start();
		
		$article = is_object($article_id) ? $article_id : $this->article_query_model->find(array('id' => $article_id));
		$article_id = is_object($article_id) ? $article->id : $article_id;

		// deletes the uploaded images if not default ones
		if ($article->nom_image != 'default_image_article.jpg') {
			unlink('uploads/news/'.$article->nom_image);
			unlink('uploads/news/banniere/'.$article->nom_banniere);
		}

		$this->appartenance_categorie_article_query_model->delete(array('article_id' => $article_id));
		$this->article_query_model->delete(array('id' => $article_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// check if the news exists and is waiting for validation from the user
	public function check_news_to_validate($article_id)
	{
		if (($article = $this->article_query_model->find_detail($article_id)) === FALSE)
			return FALSE;

		return $article->a_valider;
	}

	// find the news details to display in the detail for validation page
	public function find_news_detail_for_validation($article_id)
	{
		$article = $this->article_query_model->find_detail($article_id);
		$categories = $this->categorie_article_query_model->read();
		$categories_article = $this->appartenance_categorie_article_query_model->find_categories_article($article_id);
		$articles_recents = $this->find_recent_news();
		
		$data = array(
			'titre' => 'Article - '.$article->titre,
			'article' => $article,
			'categories' => $categories,
			'categories_article' => $categories_article,
			'articles_recents' => $articles_recents,
			'action_valider' => 'validation/valider_news/'.$article_id,
			'action_refuser' => 'validation/refuser_news/'.$article_id
		);

		return $data;
	}

	// update the news and validate it
	public function validate_news($article_id, $data_sent, $categories)
	{
		$this->db->trans_start();

		// protection against XSS injection with html_purifier
		$this->load->helper('htmlpurifier');
		$data_sent['contenu'] = html_purify($data_sent['contenu'], 'contenu_news');

		$this->update_data_news($article_id, $data_sent, $categories);

		$this->article_a_valider_query_model->delete(array('article_id' => $article_id));

		$this->article_query_model->find_detail($article_id);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// delete the uploads and the data in the database
	public function refuse_news($article_id)
	{
		$this->db->trans_start();

		$this->notification_model->add_notification($article_id, 'valide', 'news');
		
		$article = $this->article_query_model->find(array('id' => $article_id));

		// deletes the uploaded images if not default ones
		if ($article->nom_image != 'default_image_article.jpg') {
			unlink('uploads/news/'.$article->nom_image);
			unlink('uploads/news/banniere/'.$article->nom_banniere);
		}

		$this->appartenance_categorie_article_query_model->delete(array('article_id'=>$article_id));
		$this->article_query_model->delete(array('id' => $article_id));

		$this->article_a_valider_query_model->delete(array('article_id' => $article_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// inserts a news category for news
	public function register_category($data_sent)
	{
		$this->db->trans_start();

		$this->db->trans_complete();

		$this->categorie_article_query_model->create(array('nom' => $data_sent['categorie'], 'cible' => $data_sent['cible']));

		return $this->db->trans_status();
	}

	// inserts a news category for news
	public function check_category_is_deletable($categorie_id)
	{
		return $this->pole_query_model->exists(array('categorie_id' => $categorie_id)) === FALSE;
	}

	// deleting a category with no associated poles
	public function delete_category($categorie_id)
	{
		$this->db->trans_start();

		$this->db->trans_complete();

		$where = array('categorie_id' => $categorie_id);

		$this->appartenance_categorie_article_query_model->delete($where);
		$this->appartenance_categorie_utilisateur_query_model->delete($where);
		$this->categorie_article_query_model->delete(array('id' => $categorie_id));

		return $this->db->trans_status();
	}

	// builds the page for the special access to category for users
	public function building_special_access_page()
	{
		$categories = $this->categorie_article_query_model->read();
		$agents = $this->utilisateur_query_model->liste_utilisateurs_valides();
		$agents_avec_acces_special = $this->appartenance_categorie_utilisateur_query_model->liste_utilisateurs();

		return array(
			'categories' => $categories,
			'agents' => $agents,
			'tableau' => $agents_avec_acces_special
		);
	}

	// register the special category access for a user
	public function register_special_access($data_sent)
	{
		$this->db->trans_start();

		$this->db->trans_complete();

		// first deleting to replace correctly => simpliest than check + update
		$this->appartenance_categorie_utilisateur_query_model->delete(array('utilisateur_id' => $data_sent['agent']));

		foreach ($data_sent['categorie'] as $categorie_id) {
			$data = array(
				'utilisateur_id' => $data_sent['agent'],
				'categorie_id' => $categorie_id
			);
			$this->appartenance_categorie_utilisateur_query_model->create($data);
		}

		return $this->db->trans_status();
	}
}