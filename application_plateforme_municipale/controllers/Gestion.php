<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Gestion extends MY_Controller {

	const LIMIT = 40;

	public function index()
	{
		$this->utilisateurs();
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
	* UTILISATEURS
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
	public function utilisateurs($page = '')
	{
		if ($this->session->userdata('rang') !== 'admin' && $this->session->userdata('rang') !== 'rh')
			show_404();

		
		// variable for the searching
		$nom = '';

		// if the search form has been filled, we check the entry
		if ($page === 'recherche') {
			$this->form_validation->set_rules('nom', 'Nom', 'required|trim|french_names');

			if ($this->form_validation->run() != FALSE)
				$nom = $this->input->post('nom');
		}

		// we generate the users table (with the searching value if the form has been filled)
		$tableau_entier = $this->utilisateur_query_model->table_des_utilisateurs_valides(0, 99999, $nom);
		$page = $page === 'recherche' ? ($this->uri->segment(4) ? ($this->uri->segment(4) - 1) : 0) : ($this->uri->segment(3) ? ($this->uri->segment(3) - 1) : 0);
		$total_rows = is_array($tableau_entier) ? 0 : $tableau_entier->num_rows();

		$tableau = $this->utilisateur_query_model->table_des_utilisateurs_valides($page * self::LIMIT, self::LIMIT, $nom);
		$heading = array('Modifier', 'Supprimer', 'Nom', 'Prénom', 'Pseudonyme', 'Email', 'Pôle');
		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);

		self::$_data += array(
			'titre' => 'Personnel',
			'search_title' => 'Rechercher un utilisateur',
			'anchor' => anchor('gestion/utilisateurs_inactives', '(Utilisateurs inactivés)'),
			'form_action' => 'gestion/utilisateurs/recherche',
			'table_title' => 'UTILISATEURS',
			'heading' => $heading,
			'pagination' => $pagination,
			'tableau' => $tableau
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('pages/gestion_utilisateurs');
		$this->load->view('templates/footer');
	}
	/*
	*
	*
	* DELETE UTILISATEUR
	*
	*
	*/
	public function delete_user($user_id)
	{
		if ($this->session->userdata('rang') !== 'admin' && $this->session->userdata('rang') !== 'rh')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();

		// check user
		$utilisateur = $this->utilisateur_query_model->find_user_for_managing($user_id, FALSE);

		if ($utilisateur->num_rows() !== 1 || $user_id == $this->session->userdata('id')) {
			$this->utilisateurs();
			return;
		}

		// creating a good data of the user to display the table correctly
		$tab[] = array(
			'nom' => $utilisateur->row()->nom,
			'prenom' => $utilisateur->row()->prenom,
			'email' => $utilisateur->row()->email,
			'pole' => $utilisateur->row()->pole,
			'rang' => $utilisateur->row()->rang
		);

		// loading a page to confirm the delete
		self::$_data += array(
			'titre' => 'Supprimer un utilisateur',
			'tab' => $tab,
			'titre_tableau' => strtoupper('l\'utilisateur'),
			'heading' => array('Nom', 'Prénom', 'Email', 'Pôle', 'Rang'),
			'to_escape' => array(0, 1, 2, 3),
			'phrase' => 'Supprimer cet utilisateur ?',
			'form_action' => 'gestion/confirmer_delete_user/'.$user_id
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/confirmation_template');
		$this->load->view('templates/footer');
	}

	public function confirmer_delete_user($user_id)
	{
		if ($this->session->userdata('rang') !== 'admin' && $this->session->userdata('rang') !== 'rh')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();
		elseif ($this->input->post('confirmation') !== 'Confirmer') // button "Annuler" clicked
			redirect('gestion/utilisateurs');


		// check user
		$utilisateur = $this->utilisateur_query_model->find_user_for_managing($user_id, FALSE);

		if ($utilisateur->num_rows() !== 1) {
			$this->utilisateurs();
			return;
		}

		// user is disactivated
		$this->inscription_model->inactivate_user($user_id);

		self::$_data += array('titre' => 'Utilisateur supprimé', 'phrase' => 'L\'utilisateur a bien été supprimé.', 'redirection' => 'gestion/utilisateurs', 'phrase_bouton' => 'Retour à la page de gestion du personnel');

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}
	/*
	*
	*
	* MODIFICATIONS UTILISATEUR
	*
	*
	*/
	public function update_user($user_id, $is_utilisateur_inactive = FALSE)
	{
		if ($this->session->userdata('rang') !== 'admin' && $this->session->userdata('rang') !== 'rh')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();


		// check user
		$user = $this->utilisateur_query_model->find_user_for_managing($user_id, $is_utilisateur_inactive);

		if ($user->num_rows() !== 1) {
			$redirection = $is_utilisateur_inactive ? 'gestion/utilisateurs' : 'gestion/utilisateurs_inactives';

			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucun utilisateur parmi les utilisateurs à valider.',
				'redirection' => $redirection,
				'phrase_bouton' => 'Retour à la page de gestion des utilisateurs',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$pole = $this->pole_query_model->liste_tous_poles_for_drop_down();
		$rangs = $this->inscription_model->liste_rangs_for_drop_down();
		$notice_attribution_rang = $this->inscription_model->build_rank_attribution_note(); // possible ranks according to the activated modules
		$user->row()->rang = $this->rang_utilisateur_model->define_user_rank($user->row()->rang); // transform the readable name in the real value for a good registration
		$form_action = $is_utilisateur_inactive ? 'gestion/confirmer_update_and_reactivate_user/' : 'gestion/confirmer_update_user/';

		self::$_data += array(
			'titre' => 'Valider',
			'utilisateur' => $user->row(),
			'id' => $user_id,
			'rangs' => $rangs,
			'pole' => $pole,
			'notice_attribution_rang' => $notice_attribution_rang,
			'form_action' => $form_action.$user_id,
			'instructions' => 'Vous pouvez modifier les données suivantes de l\'utilisateur.',
			'button' => 'Modifier'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('formulaires/update_user');
		$this->load->view('templates/footer');
	}

	public function confirmer_update_user($user_id, $is_utilisateur_inactive = FALSE)
	{
		if ($this->session->userdata('rang') != 'admin' && $this->session->userdata('rang') != 'rh')
			show_404();
		elseif ((string)((int)$user_id) !== $user_id)
			show_404();
		elseif ($this->input->post('confirmation') !== 'Modifier') {
			if ($is_utilisateur_inactive) redirect('gestion/utilisateurs_inactives');
			else redirect('gestion/utilisateurs');
		}


		// check user
		$user = $this->utilisateur_query_model->find_user_for_managing($user_id, $is_utilisateur_inactive);

		if ($user->num_rows() !== 1) {
			$redirection = $is_utilisateur_inactive ? 'gestion/utilisateurs' : 'gestion/utilisateurs_inactives';

			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucun utilisateur parmi les utilisateurs à valider.',
				'redirection' => $redirection,
				'phrase_bouton' => 'Retour à la page de gestion des utilisateurs',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

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
			if ($is_utilisateur_inactive)
				$this->update_and_reactivate_user($user_id);
			else
				$this->update_user($user_id);
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

		$phrase = ($user === FALSE) ? 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.' : 'La modification de l\'utilisateur a été effectuée avec succès.';
		$phrase_bouton = 'Retour à la page de gestion des utilisateurs';
		$redirection = 'gestion/utilisateurs';

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
	* UTILISATEURs INACTIVES
	*
	*
	*/
	public function utilisateurs_inactives($page = '')
	{
		if ($this->session->userdata('rang') !== 'admin' && $this->session->userdata('rang') !== 'rh')
			show_404();


		// variable for the searching
		$nom = '';

		// if the search form has been filled, we check the entry
		if ($page === 'recherche') {
			$this->form_validation->set_rules('nom', 'Nom', 'required|trim|french_names');

			if ($this->form_validation->run() != FALSE)
				$nom = $this->input->post('nom');
		}

		// we generate the users table (with the searching value if the form has been filled)
		$tableau_entier = $this->utilisateur_query_model->table_des_utilisateurs_inactives(0, 99999, $nom);
		$page = $page === 'recherche' ? ($this->uri->segment(4) ? ($this->uri->segment(4) - 1) : 0) : ($this->uri->segment(3) ? ($this->uri->segment(3) - 1) : 0);
		$total_rows = is_array($tableau_entier) ? 0 : $tableau_entier->num_rows();

		$tableau = $this->utilisateur_query_model->table_des_utilisateurs_inactives($page * self::LIMIT, self::LIMIT, $nom);
		$heading = array('Restaurer', 'Nom', 'Prénom', 'Pseudonyme', 'Email', 'Pôle');
		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);

		self::$_data += array(
			'titre' => 'Personnel',
			'search_title' => 'Rechercher un utilisateur inactivé',
			'anchor' => anchor('gestion/utilisateurs', '(Utilisateurs)'),
			'form_action' => 'gestion/utilisateurs_inactives/recherche',
			'table_title' => 'UTILISATEURS INACTIVÉS',
			'heading' => $heading,
			'tableau' => $tableau,
			'pagination' => $pagination
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('pages/gestion_utilisateurs');
		$this->load->view('templates/footer');
	}

	/*
	* this function is necessary to send the $is_utilisateur_inactive data
	*/
	public function update_and_reactivate_user($utilisateur_id)
	{
		$this->update_user($utilisateur_id, TRUE);
	}

	/*
	* this function is necessary to send the $is_utilisateur_inactive data
	*/
	public function confirmer_update_and_reactivate_user($utilisateur_id)
	{
		$this->confirmer_update_user($utilisateur_id, TRUE);
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
	* POLES
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
	public function poles()
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();

		
		$heading = array('Modifier', 'Désignation', 'Email', 'Responsable', 'Supprimer');
		$tableau = $this->pole_query_model->tableau_poles();
		$categories = $this->categorie_article_query_model->liste_categories();
		$users = $this->utilisateur_query_model->liste_utilisateurs_valides(array(), array('admin'));
		// to be able to create poles even if no other user are created, we set a default value (the active user)
		if (empty($users)) $users[$this->session->userdata('id')] = $this->session->userdata('nom').' '.$this->session->userdata('prenom');

		// some options are displayed according to the activated modules
		$modules = $this->connexion_model->check_modules();
		$module_interservices_activated = in_array('interservices', $modules) ? 'block' : 'none';
		$module_bon_de_commande_activated = in_array('bon_de_commande', $modules) ? 'block' : 'none';
		$module_news_activated = in_array('news', $modules) ? 'block' : 'none';
		$module_interservices_or_citoyen_activated = (in_array('interservices', $modules) || in_array('citoyen', $modules)) ? 'block' : 'none';

		// envoi des données + load de la page
		self::$_data += array(
			'titre' => 'Pôles',
			'heading' => $heading,
			'tab' => $tableau,
			'tab_title' => 'DESCRIPTION DES PÔLES',
			'escaped_columns' => array(1, 2, 3),
			'users' => $users,
			'modules' => $modules,
			'categories' => $categories,
			'module_interservices_activated' => $module_interservices_activated,
			'module_bon_de_commande_activated' => $module_bon_de_commande_activated,
			'module_news_activated' => $module_news_activated,
			'module_interservices_or_citoyen_activated' => $module_interservices_or_citoyen_activated,
			'module_interservices_activated' => $module_interservices_activated
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('pages/gestion_poles');
		$this->load->view('formulaires/new_pole');
		$this->load->view('templates/footer');
	}

	public function pole_create()
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();

		
		// check entries
		$categories = $this->categorie_article_query_model->liste_categories();
		$categories = $this->form_validation_model->convert_dropdown_for_rules($categories);

		$users = $this->utilisateur_query_model->liste_utilisateurs_valides(array(), array('admin'));
		// to be able to create poles even if no other user are created, we set a default value (the active user)
		if (empty($users)) $users[$this->session->userdata('id')] = $this->session->userdata('nom').' '.$this->session->userdata('prenom');
		$users = $this->form_validation_model->convert_dropdown_for_rules($users);

		$this->form_validation->set_rules('responsable_id', 'Responsable', 'required|in_list['.$users.']');
		$this->form_validation->set_rules('designation', 'Désignation', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('sollicitable_via_interservices', '', 'required|in_list[0,1]');
		$this->form_validation->set_rules('bdc', '', 'required|in_list[0,1]');
		$this->form_validation->set_rules('confidentialite', '', 'required|in_list[0,1]');
		$this->form_validation->set_rules('categorie', 'Catégorie', 'required|in_list['.$categories.']');

		if ($this->form_validation->run() == FALSE) {
			$this->poles();
			return;
		}

		$pole_data = array(
			'nom' => $this->input->post('designation'),
			'responsable_id' => $this->input->post('responsable_id'),
			'sollicitable_via_interservices' => $this->input->post('sollicitable_via_interservices'),
			'sujet_aux_conges' => 0,
			'confidentialite' => $this->input->post('confidentialite'),
			'categorie_id' => $this->input->post('categorie')
		);

		$succed = $this->gestion_model->create_pole($pole_data, $this->input->post('bdc'));

		if ($succed)
			redirect('gestion/poles');

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'gestion/poles',
			'phrase_bouton' => 'Accéder à la page de gestion des pôles'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function pole_delete($pole_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$pole_id) !== $pole_id)
			show_404();


		// check the pole exists
		if ( ! $this->pole_query_model->exists(array('id' => $pole_id)) ) redirect('gestion/poles');
		
		// if at least one active user belongs to this pole, restrict the delete and warning
		$users = $this->utilisateur_query_model->liste_pole_users($pole_id);

		if ( ! empty($users) ) {
			$titre = 'Suppression impossible';
			$phrase_bouton = 'Accéder à la page de gestion des utilisateurs';
			$redirection = 'gestion/utilisateurs';
			$phrase = 'Des utilisateurs sont encore affectés à ce pôle. Il est donc impossible de le supprimer. Veuillez d\'abord affecter ces utilisateurs à un autre pôle, ou désactiver leur compte s\'ils ne sont plus actifs, puis recommencer la procédure de suppression.<br><br>';
			$phrase .= '<ol><u>Liste des utilisateurs actifs affectés à ce pôle :</u>';

			foreach ($users as $key => $value) {
				$phrase .= '<li>';
				$phrase .= htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8', FALSE);
				$phrase .= '</li>';
			}
			$phrase .= '</ol>';

			self::$_data += array('titre' => 'Suppression impossible', 'phrase' => $phrase, 'redirection' => 'gestion/utilisateurs', 'phrase_bouton' => 'Accéder à la page de gestion des utilisateurs');
		} else {
			// delete or disable the pole
			$succed = $this->gestion_model->delete_pole($pole_id);

			$titre = $succed ? 'Pôle supprimé' : 'Erreur de traitement';
			$phrase_bouton = 'Accéder à la page de gestion des Pôles';
			$redirection = 'gestion/poles';
			$phrase = $succed ? 'Le Pôle a été supprimé avec succès.' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		}

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

	public function pole_update($pole_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$pole_id) !== $pole_id)
			show_404();

		
		$table_data = $this->gestion_model->building_pole_table($pole_id);
		$pole = $this->pole_query_model->detail_pole_data($pole_id)->row();
		$users = $this->utilisateur_query_model->liste_utilisateurs_valides(array(), array('admin'));
		// to be able to create poles even if no other user are created, we set a default value (the active user)
		if (empty($users)) $users[$this->session->userdata('id')] = $this->session->userdata('nom').' '.$this->session->userdata('prenom');

		$sous_responsables_potentiels = $this->pole_sous_responsable_query_model->liste_sous_responsables_potentiels($pole_id);
		$sous_responsables = $this->pole_sous_responsable_query_model->liste_sous_responsables($pole_id);
		$categories = $this->categorie_article_query_model->liste_categories();

		// some options are displayed according to the activated modules
		$modules = $this->connexion_model->check_modules();
		$module_interservices_activated = in_array('interservices', $modules) ? 'block' : 'none';
		$module_bon_de_commande_activated = in_array('bon_de_commande', $modules) ? 'block' : 'none';
		$module_news_activated = in_array('news', $modules) ? 'block' : 'none';
		$module_interservices_or_citoyen_activated = (in_array('interservices', $modules) || in_array('citoyen', $modules)) ? 'block' : 'none';

		// envoi des données + load de la page
		self::$_data += array(
			'titre' => 'Pôles',
			'heading' => $table_data['heading'],
			'tab' => $table_data['tableau'],
			'tab_title' => 'DESCRIPTION DU PÔLE',
			'escaped_columns' => array(0, 1, 2),
			'id' => $pole_id,
			'users' => $users,
			'sous_responsables_potentiels' => $sous_responsables_potentiels,
			'sous_responsables' => $sous_responsables,
			'pole' => $pole,
			'categories' => $categories,
			'module_interservices_activated' => $module_interservices_activated,
			'module_bon_de_commande_activated' => $module_bon_de_commande_activated,
			'module_news_activated' => $module_news_activated,
			'module_interservices_or_citoyen_activated' => $module_interservices_or_citoyen_activated,
			'module_interservices_activated' => $module_interservices_activated
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('pages/gestion_poles');
		$this->load->view('formulaires/update_pole');
		$this->load->view('templates/footer');
	}

	public function pole_update_go($pole_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$pole_id) !== $pole_id)
			show_404();


		// check entries
		$categories = $this->categorie_article_query_model->liste_categories();
		$categories = $this->form_validation_model->convert_dropdown_for_rules($categories);
		
		$users = $this->utilisateur_query_model->liste_utilisateurs_valides(array(), array('admin'));
		// to be able to create poles even if no other user are created, we set a default value (the active user)
		if (empty($users)) $users[$this->session->userdata('id')] = $this->session->userdata('nom').' '.$this->session->userdata('prenom');
		$users = $this->form_validation_model->convert_dropdown_for_rules($users);

		$this->form_validation->set_rules('responsable_id', 'Responsable', 'required|in_list['.$users.']');
		$this->form_validation->set_rules('sollicitable_via_interservices', '', 'required|in_list[0,1]');
		$this->form_validation->set_rules('bdc', '', 'required|in_list[0,1]');
		$this->form_validation->set_rules('confidentialite', '', 'required|in_list[0,1]');
		$this->form_validation->set_rules('categorie', 'Catégorie', 'required|in_list['.$categories.']');

		if ($this->form_validation->run() == FALSE) {
			$this->pole_update($pole_id);
			return;
		}

		// on update le pole
		$pole_data = array(
			'responsable_id' => $this->input->post('responsable_id'),
			'sollicitable_via_interservices' => $this->input->post('sollicitable_via_interservices'),
			'confidentialite' => $this->input->post('confidentialite'),
			'categorie_id' => $this->input->post('categorie'),
			'bdc' => $this->input->post('bdc')
		);

		$succed = $this->gestion_model->update_pole($pole_id, $pole_data);
		
		if ($succed)
			redirect('gestion/poles');

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'gestion/pole_update/'.$pole_id,
			'phrase_bouton' => 'Accéder à la page de gestion du Pôles'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function pole_sous_responsable($pole_id)
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();
		elseif ((string)((int)$pole_id) !== $pole_id)
			show_404();


		// check entries
		$sous_responsables_potentiels = $this->pole_sous_responsable_query_model->liste_sous_responsables_potentiels($pole_id);
		$sous_responsables_potentiels = $this->form_validation_model->convert_dropdown_for_rules($sous_responsables_potentiels);
		$sous_responsables = $this->pole_sous_responsable_query_model->liste_sous_responsables($pole_id);
		$sous_responsables = $this->form_validation_model->convert_dropdown_for_rules($sous_responsables);
		
		$this->form_validation->set_rules('sous_responsable_potentiel_id', 'Agent à attribuer', 'in_list['.$sous_responsables_potentiels.']');
		$this->form_validation->set_rules('sous_responsable_id', 'Agent à retirer', 'in_list['.$sous_responsables.']');
		
		if ($this->form_validation->run() == FALSE) {
			$this->pole_update($pole_id);
		} else {
			// attribuer l'accès
			if ( ! empty($this->input->post('attribuer')) ) {
				$form_data = array(
					'utilisateur_id' => $this->input->post('sous_responsable_potentiel_id'),
					'pole_id' => $pole_id
				);
				$this->pole_sous_responsable_query_model->create($form_data);
			}
			// retirer l'accès
			elseif ( ! empty($this->input->post('retirer')) ) {
				$form_data = array(
					'utilisateur_id' => $this->input->post('sous_responsable_id'),
					'pole_id' => $pole_id
				);
				$this->pole_sous_responsable_query_model->delete($form_data);
			}
			// redirection
			$this->pole_update($pole_id);
		}
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
	* MODULES
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
	public function modules()
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();


		if ($this->input->post('modifier') === 'Modifier') {
			// check entries
			$modules = $this->module_query_model->liste_modules_for_dropdown();
			$modules_for_rules = $this->form_validation_model->convert_dropdown_for_rules($modules);
			
			$this->form_validation->set_rules('modules[]', 'modules', 'in_list['.$modules_for_rules.']');

			if ($this->form_validation->run() === FALSE)
				show_404();

			$this->module_model->activate_modules($this->input->post('modules'));

			self::$_data += array(
				'titre' => 'Gestion des modules',
				'phrase' => 'Les modules (dé)cochés ont été (dés)activés avec succès.',
				'redirection' => 'gestion/modules/',
				'phrase_bouton' => 'Accéder à la page de gestion des modules'
			);

			$this->load->view('templates/header', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');

			return;
		}


		$modules = $this->module_query_model->liste_modules();
		$activated_modules = $this->module_query_model->modules_actives();

		self::$_data += array(
			'titre' => 'Gestion des modules',
			'modules' => $modules,
			'activated_modules' => $activated_modules,
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('pages/gestion_modules');
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
	* CAPTCHA
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
	public function captcha()
	{
		// check if the form has been filled to check + register the entries
		if ($this->input->post('enregistrer') === 'Enregistrer') :
			// if one is filled, the second is required
			$rules = ( ! empty($this->input->post('sitekey')) | ! empty($this->input->post('secretkey')) ) ? 'required|alpha_dash|max_length[255]' : 'alpha_dash|max_length[255]';
			$this->form_validation->set_rules('sitekey', 'Clé du site', $rules);
			$this->form_validation->set_rules('secretkey', 'Clé secrète', $rules);

			if ($this->form_validation->run() != FALSE) {
				// we delete if empty entries, else insert or update the keys
				if (empty($this->input->post('sitekey')) && empty($this->input->post('secretkey')))
					$this->recaptcha_query_model->delete(array('id' => 1));
				else
					$this->recaptcha_query_model->insert_or_duplicate(array('id' => 1, 'data_sitekey' => $this->input->post('sitekey'), 'data_secretkey' => $this->input->post('secretkey')));

				self::$_data['enregistrement'] = '<p style="color: green;">Enregistrement effectué avec succès.</p>';
			}
		endif;

		$recaptcha = $this->recaptcha_query_model->read_recaptcha();
		$data_secretkey = $recaptcha === FALSE ? '' : $recaptcha->data_secretkey;
		$data_sitekey = $recaptcha === FALSE ? '' : $recaptcha->data_sitekey;
		$domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];

		self::$_data += array(
			'titre' => 'Enregistrement Recaptcha',
			'data_secretkey' => $data_secretkey,
			'data_sitekey' => $data_sitekey,
			'domain' => $domain
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('formulaires/captcha');
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
	* SOUS-POLES
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
	public function sous_poles()
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();

		
		$heading = array('Détail', 'Désignation', 'Pôle mère', 'Supprimer');
		$tableau = $this->sous_pole_query_model->tableau_sous_poles($this->session->userdata('id'))->result_array();
		$pole = $this->pole_query_model->liste_poles_du_responsable_for_dropdown($this->session->userdata('id'));

		self::$_data += array(
			'titre' => 'Gestion',
			'heading' => $heading,
			'tab' => $tableau,
			'pole' => $pole
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/gestion_sous_poles');
		$this->load->view('templates/footer');
	}

	public function sous_pole_create()
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();

		
		$poles = $this->pole_query_model->liste_poles_du_responsable_for_dropdown($this->session->userdata('id'));
		$poles = $this->form_validation_model->convert_dropdown_for_rules($poles);

		$this->form_validation->set_rules('pole_mere', 'Pôle mère', 'in_list['.$poles.']');
		$this->form_validation->set_rules('designation', 'Désignation', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('couleur', 'Couleur', 'required|regex_match[/^#(?:[0-9a-fA-F]{3}){1,2}$/]');

		if ($this->form_validation->run() == FALSE) {
			$this->sous_poles();
			return;
		}

		$sous_pole_data = array(
			'nom' => $this->input->post('designation'),
			'pole_mere_id' => $this->input->post('pole_mere'),
			'couleur' => $this->input->post('couleur')
		);

		$succed = $this->gestion_model->create_sous_pole($sous_pole_data);

		if ($succed)
			redirect('gestion/sous_poles');

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'gestion/sous_poles',
			'phrase_bouton' => 'Accéder à la page de gestion des Sous-Pôles'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function sous_pole_delete($sous_pole_id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$sous_pole_id) !== $sous_pole_id)
			show_404();


		// if at least one active user belongs to this sous pole, restrict the delete and warning
		$users = $this->utilisateur_query_model->liste_appartenance_sous_pole($sous_pole_id);

		if ( ! empty($users) ) {
			$titre = 'Suppression impossible';
			$phrase_bouton = 'Accéder à la page de gestion du Sous-Pôle';
			$redirection = 'gestion/sous_pole_update/'.$sous_pole_id;
			$phrase = 'Des utilisateurs sont encore affectés à ce sous-pôle. Il est donc impossible de le supprimer. Veuillez d\'abord supprimer l\'affectation de ces utilisateurs à ce sous-pôle, ou désactiver leur compte s\'ils ne sont plus actifs, puis recommencer la procédure de suppression.<br><br>';
			$phrase .= '<ol><u>Liste des utilisateurs actifs affectés à ce pôle :</u>';

			foreach ($users as $key => $value) {
				$phrase .= '<li>';
				$phrase .= htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8', FALSE);
				$phrase .= '</li>';
			}
			$phrase .= '</ol>';

		} else {
			// delete or disable the sous-pole
			$succed = $this->gestion_model->delete_sous_pole($sous_pole_id);

			$titre = $succed ? 'Sous-Pôle supprimé' : 'Erreur de traitement';
			$phrase_bouton = 'Accéder à la page de gestion des Sous-Pôles';
			$redirection = 'gestion/sous_poles';
			$phrase = $succed ? 'Le Sous-Pôle a été supprimé avec succès.' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		}

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

	public function sous_pole_update($sous_pole_id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$sous_pole_id) !== $sous_pole_id)
			show_404();

		
		// check sous_pole exists
		$tab_sous_pole = $this->sous_pole_query_model->tableau_detail_sous_pole($sous_pole_id, $this->session->userdata('id'));

		if ($tab_sous_pole->num_rows() !== 1)
			show_404();

		$heading = array('Désignation', 'Pôle mère', 'Couleur');
		$tableau = $tab_sous_pole;
		$sous_pole = $this->sous_pole_query_model->detail_sous_pole($sous_pole_id, $this->session->userdata('id'))->row();
		$pole = $this->pole_query_model->liste_poles_du_responsable_for_dropdown($this->session->userdata('id'));
		$users = $this->gestion_model->list_potential_user_for_sous_pole_for_dropdown($sous_pole_id, $sous_pole->pole_mere_confidentialite);
		$affectations = $this->utilisateur_query_model->liste_appartenance_sous_pole($sous_pole_id);

		self::$_data += array(
			'titre' => 'Gestion',
			'heading' => $heading,
			'tab' => $tableau,
			'id' => $sous_pole_id,
			'sous_pole' => $sous_pole,
			'pole' => $pole,
			'users' => $users,
			'affectations' => $affectations
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/update_sous_poles');
		$this->load->view('templates/footer');
	}

	public function sous_pole_update_go($sous_pole_id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$sous_pole_id) !== $sous_pole_id)
			show_404();


		// on redirige selon l'action : modifier, affecter ou annuler
		if ($this->input->post('modifier') == 'Modifier')
			$this->sous_pole_update_modifier($sous_pole_id);
		elseif ($this->input->post('modifier') == 'Affecter')
			$this->sous_pole_update_affecter($sous_pole_id);
		elseif ($this->input->post('modifier') == 'Désaffecter')
			$this->sous_pole_update_desaffecter($sous_pole_id);
		else
			$this->sous_poles();
	}

	public function sous_pole_update_modifier($sous_pole_id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$sous_pole_id) !== $sous_pole_id)
			show_404();

		
		$poles = $this->pole_query_model->liste_poles_du_responsable_for_dropdown($this->session->userdata('id'));
		$poles = $this->form_validation_model->convert_dropdown_for_rules($poles);

		$this->form_validation->set_rules('pole_mere', 'Pôle mère', 'in_list['.$poles.']');
		$this->form_validation->set_rules('designation', 'Désignation', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('couleur', 'Couleur', 'required|regex_match[/^#(?:[0-9a-fA-F]{3}){1,2}$/]');

		if ($this->form_validation->run() == FALSE) {
			$this->sous_pole_update($sous_pole_id);
			return;
		}

		$updated_data = array(
			'nom' => $this->input->post('designation'),
			'pole_mere_id' => $this->input->post('pole_mere'),
			'couleur' => $this->input->post('couleur')
		);
		$succed = $this->gestion_model->update_sous_pole($sous_pole_id, $updated_data);

		if ($succed)
			redirect('gestion/sous_poles');

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'gestion/sous_pole_update/'.$sous_pole_id,
			'phrase_bouton' => 'Accéder à la page de modification du Sous-Pôles'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function sous_pole_update_affecter($sous_pole_id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$sous_pole_id) !== $sous_pole_id)
			show_404();

		
		// check sous_pole exists
		$tab_sous_pole = $this->sous_pole_query_model->tableau_detail_sous_pole($sous_pole_id, $this->session->userdata('id'));

		if ($tab_sous_pole->num_rows() !== 1)
			show_404();

		// check entries
		$sous_pole = $this->sous_pole_query_model->detail_sous_pole($sous_pole_id, $this->session->userdata('id'))->row();
		$users = $this->gestion_model->list_potential_user_for_sous_pole_for_dropdown($sous_pole_id, $sous_pole->pole_mere_confidentialite);
		$users = $this->form_validation_model->convert_dropdown_for_rules($users);

		$this->form_validation->set_rules('user', 'Utilisateur', 'required|in_list['.$users.']');

		if ($this->form_validation->run() == FALSE) {
			$this->sous_pole_update($sous_pole_id);
			return;
		}

		$data = array(
			'utilisateur_id' => $this->input->post('user'),
			'sous_pole_id' => $sous_pole_id
		);
		$succed = $this->gestion_model->create_appartenance_sous_pole($data);

		if ($succed)
			redirect('gestion/sous_pole_update/'.$sous_pole_id);

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'gestion/sous_pole_update/'.$sous_pole_id,
			'phrase_bouton' => 'Accéder à la page de modification du Sous-Pôles'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function sous_pole_update_desaffecter($sous_pole_id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$sous_pole_id) !== $sous_pole_id)
			show_404();

		
		// check sous_pole exists
		$tab_sous_pole = $this->sous_pole_query_model->tableau_detail_sous_pole($sous_pole_id, $this->session->userdata('id'));

		if ($tab_sous_pole->num_rows() !== 1)
			show_404();

		// check entries
		$sous_pole = $this->sous_pole_query_model->detail_sous_pole($sous_pole_id, $this->session->userdata('id'))->row();
		$affectations = $this->utilisateur_query_model->liste_appartenance_sous_pole($sous_pole_id);
		$affectations = $this->form_validation_model->convert_dropdown_for_rules($affectations);

		$this->form_validation->set_rules('desaffecter', 'Désaffectation', 'required|in_list['.$affectations.']');

		if ($this->form_validation->run() == FALSE) {
			$this->sous_pole_update($sous_pole_id);
			return;
		}

		$data = array(
			'utilisateur_id' => $this->input->post('desaffecter'),
			'sous_pole_id' => $sous_pole_id
		);
		$succed = $this->gestion_model->delete_appartenance_sous_pole($data);

		if ($succed)
			redirect('gestion/sous_pole_update/'.$sous_pole_id);

		self::$_data += array(
			'titre' => 'Erreur de traitement',
			'phrase' => 'Une erreur est survenue lors du traitement. Veuillez recommencer.',
			'redirection' => 'gestion/sous_pole_update/'.$sous_pole_id,
			'phrase_bouton' => 'Accéder à la page de modification du Sous-Pôles'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}	
}
