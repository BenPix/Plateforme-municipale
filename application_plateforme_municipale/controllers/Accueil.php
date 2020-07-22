<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accueil extends MY_Controller {

	const LIMIT = 40;

	public function index()
	{
		$this->page_accueil();
	}

	private function page_accueil()
	{
		self::$_data['titre'] = 'Accueil';

		// display the home page according to the news module
		if (in_array('news', $this->connexion_model->check_modules())) {
			// automatic deleting old news
			$this->delete_news_automatique();

			self::$_data += $this->news_model->building_home_page();

			$this->load->view('news/accueil', self::$_data);

			return;
		}

		$this->load->view('templates/header', self::$_data);
		$this->load->view('pages/accueil');
		$this->load->view('templates/footer');
	}
	/*
	*
	*
	*
	*
	*
	*
	*
	*
	*
	* PROFIL
	*
	*
	*
	*
	*
	*
	*
	*
	*
	*/
	public function profil($error = '')
	{
		$tableau = $this->utilisateur_query_model->table_profil($this->session->userdata('id'));

		if ($tableau->num_rows() == 0) {
			show_404();
		}

		// managing the delegation for responsable users
		self::$_data += $this->inscription_model->manage_delegation($this->session->userdata('id'));

		self::$_data += array(
			'titre' => 'Accueil',
			'tableau' => $tableau,
			'error' => $error
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('pages/profil');
		$this->load->view('templates/footer');
	}

	public function modifier_profil()
	{
		$this->form_validation->set_rules('pseudo', 'Pseudo', 'required');
		$this->form_validation->set_rules('nom', 'Nom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('prenom', 'Prénom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]');
		$this->form_validation->set_rules('password', 'Mot de passe', 'trim|differs[email]|differs[prenom]|differs[nom]|differs[pseudo]|min_length[9]');
		$this->form_validation->set_rules('password_confirm', 'Confirmation du Mot de passe', 'trim|matches[password]|min_length[9]', array('matches' => 'Vous n\'avez pas confirmé correctement votre mot de passe.'));

		// forcing the error message if same password and pseudo
		if ($this->input->post('password') == $this->session->userdata('pseudo'))
			$this->form_validation->set_rules('forcing_error', '', 'required', array('required' => 'Le mot de passe doit être différent de votre pseudonyme.'));

		if ($this->form_validation->run() == FALSE) {
			$this->profil();
			return;
		}

		$data_sent = array(
			'nom' => $this->input->post('nom'),
			'prenom' => $this->input->post('prenom'),
			'email' => $this->input->post('email'),
			'password' => $this->input->post('password')
		);
		$succed = $this->inscription_model->update_user_profil($data_sent);

		if ( ! $succed ) {
			self::$_data += array(
				'titre' => 'Erreur de traitement',
				'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
				'redirection' => 'gestion/poles',
				'phrase_bouton' => 'Accéder à la page de gestion des pôles'
			);

			$this->load->view('templates/header', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		self::$_data += array(
			'titre' => 'Modification',
			'phrase' => 'Modification réussie !!',
			'redirection' => 'login',
			'phrase_bouton' => 'Retour à la page de connexion'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');

		// then sess destroy to be sure the user will have to reconnect
		$this->session->sess_destroy();
	}
	/*
	*
	*
	*
	*
	*
	*
	*
	*
	*
	* DELEGUER SON ROLE DE RESPONSABLE
	*
	*
	*
	*
	*
	*
	*
	*
	*
	*/
	public function deleguer()
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		if ( ! $this->inscription_model->is_pole_responsable($this->session->userdata('id')) )
			show_404();


		// check entries
		$delegue_potentiel = $this->utilisateur_query_model->list_potential_delegue($this->session->userdata('id'), $this->session->userdata('pole_id'));
		$delegue_potentiel = $this->form_validation_model->convert_dropdown_for_rules($delegue_potentiel);

		$this->form_validation->set_rules('delegue_id', 'Délégué', 'required|in_list['.$delegue_potentiel.']');

		if ($this->form_validation->run() == FALSE) {
			$this->profil();
			return;
		}

		$data = $this->inscription_model->register_delegation($this->input->post('delegue_id'));

		if ($data['succed']) {
			// mailing delegate and delegating responsable
			$responsable = $this->session->userdata('nom').' '.$this->session->userdata('prenom');

			$this->mail_model->envoi_delegation_activee_delegue($data['delegue']->email, $responsable, $data['poles_delegues']);
			$this->mail_model->envoi_delegation_activee_responsable($this->session->userdata('email'), $data['delegue'], $data['poles_delegues']);

			redirect('accueil/profil');
		}

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'accueil/profil',
			'phrase_bouton' => 'Accéder à la page de profil'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function desactiver_delegation()
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();


		// check if the user is a responsable having delegated
		$delegation_data = $this->inscription_model->manage_delegation($this->session->userdata('id'));

		if ( ! $delegation_data['is_ancien_responsable'] )
			show_404();

		$data = $this->inscription_model->delete_delegation($this->session->userdata('id'));		

		if ($data['succed']) {
			// mailing delegate and delegating responsable
			$responsable = $this->session->userdata('nom').' '.$this->session->userdata('prenom');

			$this->mail_model->envoi_delegation_desactivee_delegue($data['delegue']->email, $responsable, $data['poles_delegues']);
			$this->mail_model->envoi_delegation_desactivee_responsable($this->session->userdata('email'), $data['delegue'], $data['poles_delegues']);

			redirect('accueil/profil');
		}

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'accueil/profil',
			'phrase_bouton' => 'Accéder à la page de profil'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}
	/*
	*
	*
	*
	*
	*
	*
	*
	*
	*
	* NEWS - ARTICLES
	*
	*
	*
	*
	*
	*
	*
	*
	*
	*/
	public function article($article_id)
	{
		if ((string)((int)$article_id) !== $article_id)
			show_404();
		elseif ( ! $this->news_model->check_news($article_id) ) // check if the news exists and may be displayed for this user
			show_404();

		
		// find the news data used in the page for the display
		self::$_data += $this->news_model->find_news_detail($article_id);

		// mark_notification_as_read in case of
		$notif_list = $this->notification_model->find_user_notifications($this->session->userdata('id'));
		$notification_has_succed = $this->notification_model->mark_notification_as_read($notif_list, $article_id, 'news');

		// if a notif has succed by marking as read, we need to reload the menu, to update the notif icon
		if ($notification_has_succed) {
			$modules = $this->connexion_model->check_modules();
			self::$_data['menu_personnalise'] = $this->menu_model->menu_custom($modules, $this->session->userdata('rang'));
		}

		$this->load->view('news/article_detail', self::$_data);
	}

	public function create_news($error = '')
	{
		$categories = $this->categorie_article_query_model->read();

		self::$_data += array(
			'titre' => 'Créer une news',
			'categories' => $categories,
			'error' => $error
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('news/create_news');
		$this->load->view('news/footer_news');
	}

	public function publier_news()
	{
		// check entries
		$categories = $this->categorie_article_query_model->liste_categories();
		$categories_for_rules = $this->form_validation_model->convert_dropdown_for_rules($categories);

		$this->form_validation->set_rules('titre', 'Titre', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1000]');
		$this->form_validation->set_rules('contenu', 'Contenu', 'trim|required');
		$this->form_validation->set_rules('date_suppression', 'Date de suppression de l\'article', 'trim|required|validate_date|date_future');
		$this->form_validation->set_rules('categorie[]', 'Catégorie', 'required|in_list['.$categories_for_rules.']');

		/*
		* Comments could be added in the future. The database table article content a column 'commentaire_autorise' with default value = 0
		* The form could be updated with a dropdown (commentaire_autorise : 0 => Non, 1 => Oui).
		* in the rules $this->form_validation->set_rules('commentaire_autorise', 'Commentaires autorisés', 'required|in_list[0,1]');
		* then $commentaire_autorise = $this->input->post('commentaire_autorise');
		* and added to the $data_sent
		*/

		if ($this->form_validation->run() == FALSE) {
			$this->create_news();
			return;
		}

		// file uploading
		$config['upload_path']         		= 'uploads/news/';
		$config['allowed_types']       		= 'jpg|jpeg|png';
		$config['max_size']            		= 1000;
		$config['max_width']           		= 0;
		$config['max_height']           	= 0;
		$config['file_name']        		= 'image_article';
		$config['max_filename_increment']	= 9999;

		$data_uploads = $this->news_model->check_uploads($config);

		if ($data_uploads['check'] !== TRUE) {
			$this->create_news($data_uploads['error']);
			return;
		}

		$data_sent = array(
			'titre' => $this->input->post('titre'),
			'description' => $this->input->post('description'),
			'contenu' => $this->input->post('contenu'),
			'redacteur_id' => $this->session->userdata('id'),
			'date_suppression' => $this->input->post('date_suppression')
		);
		$returning_data = $this->news_model->register_news($data_sent, $data_uploads, $this->input->post('categorie'));

		// mailing
		if ($returning_data['succed'] && isset($returning_data['user']))
			$this->mail_model->envoi_news_a_valider($returning_data['user']->email_responsable, $returning_data['article']);

		$titre = $returning_data['succed'] ? 'News créée' : 'Erreur';
		$phrase = $returning_data['succed'] ? 'L\'article a été publié avec succès !!' : 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.';
		$phrase_bouton = $returning_data['succed'] ? 'Retour à la page d\'accueil' : 'Retour à la page de création de news';
		$redirection = $returning_data['succed'] ? 'accueil' : 'accueil/create_news';

		self::$_data += array(
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton,
			'titre' => $titre
		);
		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function update_news($article_id)
	{
		if ($this->session->userdata('rang') != 'redacteur')
			show_404();
		elseif ((string)((int)$article_id) !== $article_id)
			show_404();
		elseif ( ! $this->news_model->check_news($article_id) ) // check if the news exists and may be displayed for this user
			show_404();


		// check entries
		$categories = $this->categorie_article_query_model->liste_categories();
		$categories_for_rules = $this->form_validation_model->convert_dropdown_for_rules($categories);

		$this->form_validation->set_rules('titre', 'Titre', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1000]');
		$this->form_validation->set_rules('contenu', 'Contenu', 'trim|required');
		$this->form_validation->set_rules('date_suppression', 'Date de suppression de l\'article', 'trim|required|validate_date|date_future');
		$this->form_validation->set_rules('categorie[]', 'Catégorie', 'required|in_list['.$categories_for_rules.']');

		if ($this->form_validation->run() == FALSE) {
			$this->article($article_id);
			return;
		}

		$data_sent = array(
			'titre' => $this->input->post('titre'),
			'description' => $this->input->post('description'),
			'contenu' => $this->input->post('contenu'),
			'redacteur_id' => $this->session->userdata('id'),
			'date_suppression' => $this->input->post('date_suppression')
		);
		$succed = $this->news_model->update_news($article_id, $data_sent, $this->input->post('categorie'));

		$titre = $succed ? 'News modifiée' : 'Erreur';
		$phrase = $succed ? 'L\'article a été modifé avec succès !!' : 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.';
		$phrase_bouton = $succed ? 'Retour à la page d\'accueil' : 'Retour à la news';
		$redirection = $succed ? 'accueil' : 'accueil/article/'.$article_id;

		self::$_data += array(
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton,
			'titre' => $titre
		);
		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function delete_news($article_id)
	{
		if ($this->session->userdata('rang') != 'redacteur')
			show_404();
		elseif ((string)((int)$article_id) !== $article_id)
			show_404();
		elseif ( ! $this->news_model->check_news($article_id) ) // check if the news exists and may be displayed for this user
			show_404();


		self::$_data += $this->news_model->find_news_detail_for_delete($article_id);

		$this->load->view('news/article_detail', self::$_data);
	}

	public function delete_news_go($article_id)
	{
		if ($this->session->userdata('rang') != 'redacteur')
			show_404();
		elseif ((string)((int)$article_id) !== $article_id)
			show_404();
		elseif ( ! $this->news_model->check_news($article_id) ) // check if the news exists and may be displayed for this user
			show_404();


		$succed = $this->news_model->delete_news($article_id);

		$titre = $succed ? 'News supprimée' : 'Erreur';
		$phrase = $succed ? 'L\'article a été supprimé avec succès !!' : 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.';
		$phrase_bouton = $succed ? 'Retour à la page d\'accueil' : 'Retour à la news';
		$redirection = $succed ? 'accueil' : 'accueil/delete_news/'.$article_id;

		self::$_data += array(
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton,
			'titre' => $titre
		);
		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	// mark a notif as read for a deleted news
	public function notif_marked_as_read_for_news($article_id)
	{
		$notif_list = $this->notification_model->find_user_notifications($this->session->userdata('id'));
		$this->notification_model->mark_notification_as_read($notif_list, $article_id, 'news');

		redirect('accueil');
	}

	// similar to a CRON task to delete old news when the date has passed
	private function delete_news_automatique()
	{
		$articles = $this->article_query_model->read_for_delete();

		foreach ($articles as $article)
			$this->news_model->delete_news($article);
	}
	/*
	*
	*
	*
	*
	*
	*
	*
	*
	*
	* CATEGORIES DES NEWS
	*
	*
	*
	*
	*
	*
	*
	*
	*
	*/
	public function create_categorie()
	{
		if ($this->session->userdata('rang') != 'redacteur' && $this->session->userdata('rang') != 'admin')
			show_404();


		// check if the form has been filled, to register the news category
		if ( ! empty($this->input->post('confirmation')) && empty(validation_errors())) :
			$this->form_validation->set_rules('categorie', 'Nom de la catégorie', 'trim|required|french_names|max_length[50]');
			$this->form_validation->set_rules('cible', 'Cible de la catégorie', 'trim|required|french_names|max_length[50]');

			if ($this->form_validation->run() == FALSE) {
				$this->create_categorie();
				return;
			}

			// insert dans la bdd
			$succed = $this->news_model->register_category(array('categorie' => $this->input->post('categorie'), 'cible' => $this->input->post('cible')));

			// redirection
			self::$_data += array('titre' => 'Catégorie', 'phrase' => 'La nouvelle catégorie a été créée avec succès !!', 'redirection' => 'accueil/create_categorie', 'phrase_bouton' => 'Retour à la page de création des catégories');

			$this->load->view('templates/header', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		endif;

		// form not filled, page displaying categories
		$headings = array('Catégorie', 'Destinée aux', 'Pôles associés', 'Supprimer la catégorie');
		$tableau = $this->categorie_article_query_model->liste_categories_for_tableau();

		self::$_data += array('titre' => 'Catégorie', 'headings' => $headings, 'tableau' => $tableau);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('news/create_categorie');
		$this->load->view('templates/footer');
	}

	public function delete_categorie($id)
	{
		if ($this->session->userdata('rang') != 'redacteur' && $this->session->userdata('rang') != 'admin')
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// a category can be deleted only if no associated poles to it
		$check = $this->news_model->check_category_is_deletable($id);

		if ($check) {
			$this->news_model->delete_category($id);

			$this->create_categorie();
			return;
		}
		
		self::$_data += array('titre' => 'Catégorie', 'phrase' => 'Cette catégorie ne peut pas être supprimée car des Pôles y sont encore associés. Veuillez d\'abord les associer à une autre catégorie.', 'redirection' => 'accueil/create_categorie', 'phrase_bouton' => 'Retour à la page des catégories');

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function acces_special()
	{
		if ($this->session->userdata('rang') != 'redacteur' && $this->session->userdata('rang') != 'admin')
			show_404();


		self::$_data += $this->news_model->building_special_access_page();
		self::$_data += array('titre' => 'Accès spécial', 'headings' => array('Agent', 'Catégories autorisées', 'Supprimer l\'accès spécial'));

		$this->load->view('templates/header', self::$_data);
		$this->load->view('news/acces_special');
		$this->load->view('templates/footer');
	}

	public function attribuer_acces_special()
	{
		if ($this->session->userdata('rang') != 'redacteur' && $this->session->userdata('rang') != 'admin')
			show_404();


		// check entries
		$categories = $this->categorie_article_query_model->liste_categories();
		$categories_for_rules = $this->form_validation_model->convert_dropdown_for_rules($categories);
		$utilisateurs = $this->utilisateur_query_model->liste_utilisateurs_valides();
		$utilisateurs_for_rules = $this->form_validation_model->convert_dropdown_for_rules($utilisateurs);

		$this->form_validation->set_rules('agent', 'Agent', 'required|in_list['.$utilisateurs_for_rules.']');
		$this->form_validation->set_rules('categorie[]', 'Catégorie', 'required|in_list['.$categories_for_rules.']');

		if ($this->form_validation->run() == FALSE) {
			$this->acces_special();
			return;
		}

		$this->news_model->register_special_access(array('agent' => $this->input->post('agent'), 'categorie' => $this->input->post('categorie')));

		$this->acces_special();
	}

	public function enlever_acces_special($id)
	{
		if ($this->session->userdata('rang') != 'redacteur' && $this->session->userdata('rang') != 'admin')
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		$this->appartenance_categorie_utilisateur_query_model->delete(array('utilisateur_id' => $id));

		$this->acces_special();
	}
}
