<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Validation extends MY_Controller {

	
	public function index()
	{
		show_404();
	}

	/*
	*
	*
	*
	*
	*
	*
	* INSCRIPTION
	*
	*
	*
	*
	*
	*
	*/
	public function inscriptions()
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();

		$tableau_inscription = $this->utilisateur_query_model->table_des_inscriptions_a_valider();

		self::$_data += array(
			'titre' => 'Validation',
			'tableau_inscription' => $tableau_inscription
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/inscriptions');
		$this->load->view('templates/footer');
	}

	public function validation_inscription($user_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();


		// checks the user to validate exists and is waiting to be validated
		$user = $this->utilisateur_query_model->inscription_a_valider($user_id);

		if ($user !== FALSE) {
			$pole = $this->pole_query_model->liste_tous_poles_for_drop_down(); // the pole dropdownm menu
			$rangs = $this->inscription_model->liste_rangs_for_drop_down(); // the rank dropdownm menu
			$notice_attribution_rang = $this->inscription_model->build_rank_attribution_note(); // possible ranks according to the activated modules

			self::$_data += array(
				'titre' => 'Valider',
				'utilisateur' => $user->row(),
				'id' => $user_id,
				'rangs' => $rangs,
				'pole' => $pole,
				'notice_attribution_rang' => $notice_attribution_rang,
				'form_action' => 'validation/poursuivre_inscription/'.$user_id,
				'instructions' => 'Veuillez attribuer un rang à l\'utilisateur, et vérifier (voire modifier) ses données.',
				'button' => 'Valider'
			);

			$this->load->view('templates/header', self::$_data);
			$this->load->view('formulaires/update_user');
			$this->load->view('templates/footer');
		} else {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucun utilisateur parmi les utilisateurs à valider.',
				'redirection' => 'validation/inscriptions',
				'phrase_bouton' => 'Retour à la page de validation des inscriptions',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
		}
	}

	public function poursuivre_inscription($user_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();
		elseif ($this->input->post('confirmation') !== 'Valider') // button "Annuler" clicked
			redirect('validation/inscriptions');

		
		// check entries
		$poles = $this->pole_query_model->liste_tous_poles_for_drop_down();
		$poles_rules = $this->form_validation_model->convert_dropdown_for_rules($poles);
		$rank = $this->inscription_model->liste_rangs_for_drop_down();
		$rank_list = $this->form_validation_model->convert_dropdown_for_rules($rank);

		$this->form_validation->set_rules('pole', 'Appartenance', 'in_list['.$poles_rules.']|required');
		$this->form_validation->set_rules('nom', 'Nom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('prenom', 'Prénom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]');
		$this->form_validation->set_rules('rang', 'Rang', 'required|in_list['.$rank_list.']');

		if ($this->form_validation->run() == FALSE) {
			$this->validation_inscription($user_id);
			return;
		}

		// checks the user to validate exists and is waiting to be validated
		$check_user = $this->utilisateur_query_model->inscription_a_valider($user_id);

		if ($check_user === FALSE) {
			$this->validation_inscription($user_id);
			return;
		}

		// update and validate the user
		$data_utilisateur = array(
			'nom' => $this->input->post('nom'),
			'prenom' => $this->input->post('prenom'),
			'email' => $this->input->post('email'),
			'pole_id' => $this->input->post('pole'),
			'rang' => $this->input->post('rang')
		);

		$user = $this->inscription_model->update_and_validate_user($user_id, $data_utilisateur);

		$phrase = ($user === FALSE) ? 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.' : 'La demande d\'inscription a été validée avec succès.';
		$phrase_bouton = 'Retour à la page de validation';
		$redirection = 'validation/inscriptions';

		if ($user !== FALSE) {
			// user is warned by email
			$this->mail_model->envoi_inscription_validee($user);
		}

		self::$_data += array(
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton,
			'titre' => 'Inscription'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function refus_inscription($user_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();


		// checks the user to validate exists and is waiting to be validated
		$check_user = $this->utilisateur_query_model->inscription_a_valider($user_id);

		if ($check_user === FALSE) {
			$this->validation_inscription($user_id);
			return;
		}

		// creating a good data of the user to display the table correctly
		$tab[] = array(
			'nom' => $check_user->row()->nom,
			'prenom' => $check_user->row()->prenom,
			'email' => $check_user->row()->email,
			'pole' => $check_user->row()->pole
		);

		// loading a page to confirm the refusal
		self::$_data += array(
			'titre' => 'Valider',
			'tab' => $tab,
			'titre_tableau' => strtoupper('l\'utilisateur'),
			'heading' => array('Nom', 'Prénom', 'Email', 'Pôle'),
			'to_escape' => array(0, 1, 2, 3),
			'phrase' => 'Refuser cette inscription et supprimer cet utilisateur ?',
			'form_action' => 'validation/confirmer_refus_inscription/'.$user_id
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/confirmation_template');
		$this->load->view('templates/footer');
	}

	public function confirmer_refus_inscription($user_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();
		elseif ($this->input->post('confirmation') !== 'Confirmer') // button "Annuler" clicked
			redirect('validation/inscriptions');


		// checks the user to validate exists and is waiting to be validated
		$check_user = $this->utilisateur_query_model->inscription_a_valider($user_id);

		if ($check_user === FALSE) {
			$this->validation_inscription($user_id);
			return;
		}

		// deleting in the database
		$succed = $this->inscription_model->delete_user($user_id);

		$phrase = ($succed === FALSE) ? 'Une erreur est survenue lors du traitement. Veuillez recommencer.' : 'La demande d\'inscription a été refusée et l\'utilisateur supprimé avec succès.';
		$phrase_bouton = 'Retour à la page de validation';
		$redirection = 'validation/inscriptions';

		$this->mail_model->envoi_inscription_refusee($check_user->row()->email);

		self::$_data += array(
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton,
			'titre' => 'Inscription'
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
	*
	*
	* VALIDATION DES DEMANDES
	*
	*
	*
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
	public function interservices()
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();

		
		$tableau_demande = $this->demande_interservices_query_model->table_demandes_a_valider($this->session->userdata('id'));

		self::$_data += array(
			'titre' => 'Validation',
			'tableau_demande' => $tableau_demande
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/tableau_interservices_a_valider');
		$this->load->view('templates/footer');
	}

	public function validation_demande($id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// check the demande needs to be validated
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);

		if ( ! $demande->a_valider ) {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucune demande à valider.',
				'redirection' => 'validation/interservices',
				'phrase_bouton' => 'Retour à la page de validation des demandes interservices',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$tab_demande = array(
			array(
				$demande->horodateur,
				$demande->demandeur,
				$demande->direction_sollicitee,
				$demande->demande,
				$demande->delai,
				$demande->degre_urgence
			)
		);
		$poles = $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$sous_poles = array('Aucun' => array('0' => '---')) + $this->sous_pole_query_model->liste_poles_sollicitables_avec_opt_group_par_pole($demande->direction_attachee_id);

		self::$_data += array(
			'tab_demande' => $tab_demande,
			'demande' => $demande,
			'titre' => 'Confirmation',
			'id' => $id,
			'poles' => $poles,
			'sous_poles' => $sous_poles
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/validation_demande');
		$this->load->view('templates/footer');
	}

	public function confirmer_validation_demande($id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();
		elseif ($this->input->post('confirmation') !== 'Confirmer')
			redirect('validation/interservices');


		// check entries
		$poles_sollicites = $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$poles_sollicites_for_rules = $this->form_validation_model->convert_dropdown_for_rules($poles_sollicites);
		$poles_attaches = $this->pole_query_model->liste_tous_poles_for_drop_down();
		$poles_attaches_for_rules = $this->form_validation_model->convert_dropdown_for_rules($poles_attaches);
		$sous_poles = $this->sous_pole_query_model->liste_sous_poles_for_dropdown();
		$sous_poles_rules = $this->form_validation_model->convert_dropdown_for_rules($sous_poles);

		$this->form_validation->set_rules('direction_sollicitee_id', 'Direction sollicitée', 'in_list['.$poles_sollicites_for_rules.']');
		$this->form_validation->set_rules('direction_attachee_id', 'Direction attachée', 'in_list['.$poles_attaches_for_rules.']');
		$this->form_validation->set_rules('demande', 'Demande', 'trim|required|min_length[5]|max_length[3000]');
		$this->form_validation->set_rules('delai', 'Délai', 'in_list[au mieux,date précise,délai maximum]');
		$this->form_validation->set_rules('date_souhaitee', 'Date souhaitée', 'validate_date');
		$this->form_validation->set_rules('sous_pole_id', 'Sous-catégorie', 'in_list['.$sous_poles_rules.',0]|coincide_pole[direction_attachee_id]');

		if ($this->form_validation->run() == FALSE) {
			$this->validation_demande($id);
			return;
		}

		// check the demande needs to be validated
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);

		if ( ! $demande->a_valider ) {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucune demande à valider.',
				'redirection' => 'validation/interservices',
				'phrase_bouton' => 'Retour à la page de validation des demandes interservices',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$data_escaped = array(
			'demande' => $this->input->post('demande'),
			'pole_sollicite_id' => $this->input->post('direction_sollicitee_id')
		);
		$data_deadlines = array(
			'delai' => $this->input->post('delai'),
			'echeance' => $this->input->post('date_souhaitee')
		);
		$succed = $this->interservices_model->validate_demande_interservices($id, $data_escaped, $data_deadlines, $this->input->post('sous_pole_id'));

		$titre = $succed ? 'Demande validée' : 'Erreur de traitement';
		$phrase_bouton = $succed ? 'Retour à la page de validation des demandes' : 'Retour à la page de détail de la demande';
		$redirection = $succed ? 'validation/interservices' : 'demande/detail/'.$id;
		$phrase = $succed ? 'La demande a été validée avec succès !!' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		
		self::$_data += array(
			'titre' => $titre,
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function refus_demande($id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// check the demande needs to be validated
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);

		if ( ! $demande->a_valider ) {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucune demande à valider.',
				'redirection' => 'validation/interservices',
				'phrase_bouton' => 'Retour à la page de validation des demandes interservices',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$tab_demande = array(
			array(
				$demande->horodateur,
				$demande->demandeur,
				$demande->direction_sollicitee,
				$demande->demande,
				$demande->delai,
				$demande->degre_urgence
			)
		);

		self::$_data += array(
			'tab_demande' => $tab_demande,
			'titre' => 'Confirmation',
			'id' => $id
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/refus_demande');
		$this->load->view('templates/footer');
	}

	public function confirmer_refus_demande($id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();
		elseif ($this->input->post('confirmation') !== 'Confirmer')
			redirect('validation/interservices');


		// check entries
		$this->form_validation->set_rules('refus', 'Raison du refus', 'trim|required|max_length[255]');

		if ($this->form_validation->run() == FALSE) {
			$this->refus_demande($id);
			return;
		}

		// check the demande needs to be validated
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);

		if ( ! $demande->a_valider ) {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucune demande à valider.',
				'redirection' => 'validation/interservices',
				'phrase_bouton' => 'Retour à la page de validation des demandes interservices',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$data_sent = array(
			'utilisateur_id' => $this->session->userdata('id'),
			'commentaire' => $this->input->post('refus'),
			'demande_interservices_id' => $id
		);
		$succed = $this->interservices_model->refuse_demande_interservices($id, $data_sent);

		$titre = $succed ? 'Demande refusée' : 'Erreur de traitement';
		$phrase_bouton = $succed ? 'Retour à la page de validation des demandes' : 'Retour à la page de détail de la demande';
		$redirection = $succed ? 'validation/interservices' : 'demande/detail/'.$id;
		$phrase = $succed ? 'La demande a été refusée avec succès !!' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		
		self::$_data += array(
			'titre' => $titre,
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton
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
	* VALIDATION DES NEWS
	*
	*
	*
	*
	*
	*
	*/
	public function news()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable')) )
			show_404();

		
		$tableau_news = $this->article_query_model->table_articles_a_valider($this->session->userdata('id'));
		$heading = array('Aperçu', 'Date de création', 'Rédacteur', 'Titre', 'Description', 'Date de suppression');

		self::$_data += array(
			'titre' => 'Validation',
			'tableau_news' => $tableau_news,
			'heading' => $heading
		);
		$this->load->view('templates/header', self::$_data);
		$this->load->view('news/validation_news');
		$this->load->view('templates/footer');
	}

	public function previsualiser_news($article_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable')) )
			show_404();
		elseif ((string)((int)$article_id) !== $article_id)
			show_404();
		elseif ( ! $this->news_model->check_news_to_validate($article_id) ) // check if the news exists and may be displayed for this user
			show_404();

		
		self::$_data += $this->news_model->find_news_detail_for_validation($article_id);

		$this->load->view('news/previsualisation_news', self::$_data);
	}

	public function valider_news($article_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable')) )
			show_404();
		elseif ((string)((int)$article_id) !== $article_id)
			show_404();
		elseif ( ! $this->news_model->check_news_to_validate($article_id) ) // check if the news exists and may be displayed for this user
			show_404();


		$categories = $this->categorie_article_query_model->liste_categories();
		$categories_for_rules = $this->form_validation_model->convert_dropdown_for_rules($categories);

		$this->form_validation->set_rules('titre', 'Titre', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('description', 'Description', 'trim|required|max_length[1000]');
		$this->form_validation->set_rules('contenu', 'Contenu', 'trim|required');
		$this->form_validation->set_rules('date_suppression', 'Date de suppression de l\'article', 'trim|required|validate_date|date_future');
		$this->form_validation->set_rules('categorie[]', 'Catégorie', 'required|in_list['.$categories_for_rules.']');
		/* quand les commentaires seront activés :
		$this->form_validation->set_rules('commentaire_autorise', 'Commentaires autorisés', 'required|in_list[0,1]');
		*/

		if ($this->form_validation->run() == FALSE) {
			$this->previsualiser_news($article_id);
			return;
		}

		$data_sent = array(
			'titre' => $this->input->post('titre'),
			'description' => $this->input->post('description'),
			'contenu' => $this->input->post('contenu'),
			'date_suppression' => $this->input->post('date_suppression')
		);
		$succed = $this->news_model->validate_news($article_id, $data_sent, $this->input->post('categorie'));

		if ($succed)
			$this->notification_model->add_notification($article_id, 'valide', 'news');

		$titre = $succed ? 'News validée' : 'Erreur';
		$phrase = $succed ? 'L\'article a été validé et publié avec succès !!' : 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.';
		$phrase_bouton = $succed ? 'Retour à la page de validation' : 'Retour à la news';
		$redirection = $succed ? 'validation/news' : 'validation/previsualiser_news/'.$article_id;

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

	public function refuser_news($article_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable')) )
			show_404();
		elseif ((string)((int)$article_id) !== $article_id)
			show_404();
		elseif ( ! $this->news_model->check_news_to_validate($article_id) ) // check if the news exists and may be displayed for this user
			show_404();


		$succed = $this->news_model->refuse_news($article_id);

		$titre = $succed ? 'News refusée et supprimée' : 'Erreur';
		$phrase = $succed ? 'L\'article a été refusé et supprimé avec succès !!' : 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.';
		$phrase_bouton = $succed ? 'Retour à la page de validation' : 'Retour à la news';
		$redirection = $succed ? 'validation/news' : 'validation/previsualiser_news/'.$article_id;

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
}
