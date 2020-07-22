<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Note extends My_Controller {

	const LIMIT = 40;

	public function __construct() {
		parent::__construct();
		$this->session->keep_flashdata('note_page');
		$this->session->keep_flashdata('note_section');
		$this->session->keep_flashdata('filtre_notes_envoyees');
		$this->session->keep_flashdata('filtre_form_notes_envoyees');
		$this->session->keep_flashdata('filtre_notes_recues');
		$this->session->keep_flashdata('filtre_form_notes_recues');
		$this->session->keep_flashdata('filtre_notes_terminees');
		$this->session->keep_flashdata('filtre_form_notes_terminees');
		$this->session->keep_flashdata('filtre_notes_refusees');
		$this->session->keep_flashdata('filtre_form_notes_refusees');
	}

	public function index()
	{
		$this->notes_envoyees();
	}

	public function creation($error = '')
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();


		$users = $this->utilisateur_query_model->utilisateurs_pour_valider_note_dropdown();
		unset($users[$this->session->userdata('id')]); // remove the active user from the list

		self::$_data += array(
			'titre' => 'Création de note',
			'error' => $error,
			'users' => $users
		);
		$this->load->view('templates/header', self::$_data);
		$this->load->view('note/formulaire_creation');
		$this->load->view('templates/footer');
	}

	public function creation_go()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();


		// check entries
		$users = $this->utilisateur_query_model->utilisateurs_pour_valider_note_dropdown();
		$users_rules = $this->form_validation_model->convert_dropdown_for_rules($users);

		$this->form_validation->set_rules('note', 'Note', 'trim|required|max_length[21000]');
		$this->form_validation->set_rules('objet', 'Objet', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('workflow[]', 'Valideur', 'required|in_list['.$users_rules.']');

		// checks if same user selected several times, and force the rule breaking if TRUE to avoid this behaviour
		if (count(array_count_values($this->input->post('workflow'))) != count($this->input->post('workflow')))
			$this->form_validation->set_rules('forcing_error', 'Valideur', 'required', array('required' => 'Un même utilisateur ne peut pas être sélectionné plusieurs fois comme valideur'));

		if ($this->form_validation->run() == FALSE) {
			$this->creation();
			return;
		}

		// config and check the uploads
		$config['upload_path']          = $upload_path = 'uploads/note';
		$config['allowed_types']        = 'gif|jpg|jpeg|png|pdf|txt|doc|docx|xls|xlsx';
		$config['max_size']             = 3000;
		$config['file_name']        	= 'piece_jointe_note';
		$config['max_filename_increment']	= 999999;

		$data_uploads = $this->note_model->check_uploads($config);

		if ($data_uploads['check'] !== TRUE) {
			$this->creation($data_uploads['error']);
			return;
		}

		$data_escaped = array(
			'redacteur_id' => $this->session->userdata('id'),
			'objet' => $this->input->post('objet'),
			'note' => $this->input->post('note')
		);

		$note = $this->note_model->register_note($data_escaped, $this->input->post('workflow'), $data_uploads);

		// if any issue, warning page displayed
		if ($note === FALSE) {
			self::$_data += array('titre' => 'Echec', 'phrase' => 'Une erreur est survenue, et la note n\'a pas pu être envoyée. Veuillez recommencer.', 'redirection' => 'demande', 'phrase_bouton' => 'Retour au formulaire de création des notes');

			$this->load->view('templates/header', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');

			return;
		}

		// mail to the first user in the workflow
		$this->mail_model->envoi_note_a_valider($note);

		$phrase = 'La note a bien été envoyée. La chaîne de validation a démarré.<br>Vous serez informé par notification dès qu\'un commentaire sera posté, qu\'une validation sera effectuée, ou qu\'un refus sera introduit.';

		self::$_data += array('titre' => 'Demande effectuée', 'phrase' => $phrase, 'redirection' => 'accueil', 'phrase_bouton' => 'Retour sur la page d\'accueil');

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function notes_envoyees()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();


		
		// filter section
		$this->note_model->manage_filter('notes_envoyees');
		$filter = $this->session->flashdata('filtre_notes_envoyees');
		$filter_form = $this->session->flashdata('filtre_form_notes_envoyees');
		$agents = array($this->session->userdata('id') => $this->session->userdata('nom').' '.$this->session->userdata('prenom'));
		$filter_page = $this->load->view('note/note_filter', array('agents' => $agents, 'filter_form' => $filter_form, 'form_action' => 'notes_envoyees'), TRUE);

		$tab_entier = $this->note_query_model->find_notes_envoyees($filter);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();
		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);

		// the page is set in flashdata to redirect to the good page after any update by the user
		$this->session->set_flashdata('note_page', $page + 1);
		$this->session->set_flashdata('note_section', 'notes_envoyees');

		$tab = $this->note_query_model->find_notes_envoyees($filter, $page * self::LIMIT, self::LIMIT);

		$agents = $this->utilisateur_query_model->liste_utilisateurs_valides(array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'));

		self::$_data += array(
			'titre' => 'Mes notes',
			'tab_title' => 'Mes notes envoyées | en cours',
			'heading' => array('Détail', 'N°', 'Date de création', 'Objet', 'Contenu'),
			'escaped_columns' => array(3, 4),
			'tab' => $tab,
			'pagination' => $pagination,
			'filter_page' => $filter_page,
			'empty_table_message' => 'Aucune note envoyée'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('templates/table_display');
		$this->load->view('templates/footer');
	}

	public function notes_recues()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();

		
		
		// filter section
		$this->note_model->manage_filter('notes_recues');
		$filter = $this->session->flashdata('filtre_notes_recues');
		$filter_form = $this->session->flashdata('filtre_form_notes_recues');
		$agents = array('0' => 'Tous')+$this->utilisateur_query_model->liste_utilisateurs_valides(array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'));
		$filter_page = $this->load->view('note/note_filter', array('agents' => $agents, 'filter_form' => $filter_form, 'form_action' => 'notes_recues'), TRUE);

		$tab_entier = $this->note_query_model->find_notes_recues($filter);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();
		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);

		// the page is set in flashdata to redirect to the good page after any update by the user
		$this->session->set_flashdata('note_page', $page + 1);
		$this->session->set_flashdata('note_section', 'notes_recues');

		$tab = $this->note_query_model->find_notes_recues($filter, $page * self::LIMIT, self::LIMIT);

		self::$_data += array(
			'titre' => 'Notes reçues',
			'tab_title' => 'Mes notes reçues | en cours',
			'heading' => array('Détail', 'N°', 'Date de création', 'Régigée par', 'Objet', 'Contenu'),
			'escaped_columns' => array(3, 4, 5),
			'tab' => $tab,
			'pagination' => $pagination,
			'filter_page' => $filter_page,
			'empty_table_message' => 'Aucune note reçue'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('templates/table_display');
		$this->load->view('templates/footer');
	}

	public function notes_terminees()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();

		
		
		// filter section
		$this->note_model->manage_filter('notes_terminees');
		$filter = $this->session->flashdata('filtre_notes_terminees');
		$filter_form = $this->session->flashdata('filtre_form_notes_terminees');
		$agents = array('0' => 'Tous')+$this->utilisateur_query_model->liste_utilisateurs_valides(array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'));
		$filter_page = $this->load->view('note/note_filter', array('agents' => $agents, 'filter_form' => $filter_form, 'form_action' => 'notes_terminees'), TRUE);

		$tab_entier = $this->note_query_model->find_notes_terminees($filter);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();
		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);

		// the page is set in flashdata to redirect to the good page after any update by the user
		$this->session->set_flashdata('note_page', $page + 1);
		$this->session->set_flashdata('note_section', 'notes_terminees');

		$tab = $this->note_query_model->find_notes_terminees($filter, $page * self::LIMIT, self::LIMIT);

		self::$_data += array(
			'titre' => 'Notes terminées',
			'tab_title' => 'Les notes terminées',
			'heading' => array('Détail', 'N°', 'Date de création', 'Régigée par', 'Objet', 'Contenu'),
			'escaped_columns' => array(3, 4, 5),
			'tab' => $tab,
			'pagination' => $pagination,
			'filter_page' => $filter_page,
			'empty_table_message' => 'Aucune note terminée'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('templates/table_display');
		$this->load->view('templates/footer');
	}

	public function notes_refusees()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();

		
		
		// filter section
		$this->note_model->manage_filter('notes_refusees');
		$filter = $this->session->flashdata('filtre_notes_refusees');
		$filter_form = $this->session->flashdata('filtre_form_notes_refusees');
		$agents = array('0' => 'Tous')+$this->utilisateur_query_model->liste_utilisateurs_valides(array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'));
		$filter_page = $this->load->view('note/note_filter', array('agents' => $agents, 'filter_form' => $filter_form, 'form_action' => 'notes_refusees'), TRUE);

		$tab_entier = $this->note_query_model->find_notes_refusees($filter);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();
		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);

		// the page is set in flashdata to redirect to the good page after any update by the user
		$this->session->set_flashdata('note_page', $page + 1);
		$this->session->set_flashdata('note_section', 'notes_refusees');

		$tab = $this->note_query_model->find_notes_refusees($filter, $page * self::LIMIT, self::LIMIT);

		self::$_data += array(
			'titre' => 'Notes refusées',
			'tab_title' => 'Les notes refusées',
			'heading' => array('Détail', 'N°', 'Date de création', 'Régigée par', 'Objet', 'Contenu', 'Refusée par'),
			'escaped_columns' => array(3, 4, 5, 6),
			'tab' => $tab,
			'pagination' => $pagination,
			'filter_page' => $filter_page,
			'empty_table_message' => 'Aucune note refusée'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('templates/table_display');
		$this->load->view('templates/footer');
	}

	public function notes_a_valider()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();

		
		// canceling filters in flashdata
		$this->note_model->manage_filter('notes_a_valider');

		$tab = $this->note_query_model->find_notes_a_valider($this->session->userdata('id'));

		self::$_data += array(
			'titre' => 'Notes à valider',
			'tab_title' => 'Les notes à valider',
			'heading' => array('Détail', 'N°', 'Date de création', 'Régigée par', 'Objet', 'Contenu'),
			'escaped_columns' => array(3, 4, 5),
			'tab' => $tab,
			'pagination' => '',
			'empty_table_message' => 'Aucune note à valider'
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('templates/table_display');
		$this->load->view('templates/footer');
	}

	public function detail($note_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();


		if (($note = $this->note_model->find_all_note_details($note_id)) === FALSE)
			show_404();


		$heading = array('Étape N°', 'Nom', 'Validation');
		$table = $this->note_model->find_note_details_for_table($heading, $note);
		$users = $this->utilisateur_query_model->utilisateurs_pour_valider_note_dropdown();
		unset($users[$this->session->userdata('id')]); // remove the active user from the list
		// remove the users already in the workflow
		foreach ($note->workflow as $row) {
			if (in_array($row->workflow_utilisateur_id, array_keys($users)))
				unset($users[$row->workflow_utilisateur_id]);
		}
		$allowed_steps = $this->note_model->build_allowed_steps($note);

		// sections display conditions
		$is_not_finished = ( ! $note->validated && ! $note->refused );
		$may_add_new_validator = $note->redacteur_id == $this->session->userdata('id') && $is_not_finished;
		$may_validate = $note->valideur_attendu_id == $this->session->userdata('id') && $is_not_finished;
		$may_comment = $is_not_finished;
		$may_see_pdf = $note->validated;

		self::$_data += array(
			'titre' => 'Détail de la note',
			'heading' => $heading,
			'table' => $table,
			'escaped_columns' => array(1),
			'note' => $note,
			'may_add_new_validator' => $may_add_new_validator,
			'may_validate' => $may_validate,
			'may_comment' => $may_comment,
			'may_see_pdf' => $may_see_pdf,
			'users' => $users,
			'allowed_steps' => $allowed_steps
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('note/note_detail');
		$this->load->view('templates/footer');
	}

	public function soumission($note_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();


		// dispatching when the form has been filled
		if ( ! empty($this->input->post('soumission')) ) {
			switch ($this->input->post('soumission')) {
				case 'Ajouter':
					$this->ajouter_valideur($note_id);
					break;
				case 'Poster le commentaire':
					$this->commenter($note_id);
					break;
				case 'Poster le commentaire (sans valider ni refuser)':
					$this->commenter($note_id);
					break;
				case 'Valider':
					$this->valider($note_id, TRUE);
					break;
				case 'Refuser':
					$this->valider($note_id, FALSE);
					break;
				
				default:
					show_404();
					break;
			}
			return;
		}

		show_404(); // form not filled
	}

	private function ajouter_valideur($note_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();
		elseif ( ! $this->note_query_model->exists(array('id' => $note_id)) ) // checks the note exists
			show_404();

		
		// check if the note may have a new validator
		$note = $this->note_model->find_all_note_details($note_id);

		if ($note->redacteur_id != $this->session->userdata('id') || $note->validated || $note->refused)
			show_404();

		//check entries
		$allowed_steps = $this->note_model->build_allowed_steps($note);
		$allowed_steps_for_rules = $this->form_validation_model->convert_dropdown_for_rules($allowed_steps);

		$users = $this->utilisateur_query_model->utilisateurs_pour_valider_note_dropdown();
		unset($users[$this->session->userdata('id')]); // remove the active user from the list
		// remove the users already in the workflow
		foreach ($note->workflow as $row) {
			if (in_array($row->workflow_utilisateur_id, array_keys($users)))
				unset($users[$row->workflow_utilisateur_id]);
		}
		$users_for_rules = $this->form_validation_model->convert_dropdown_for_rules($users);

		$this->form_validation->set_rules('valideur_id', 'Nouveau valideur', 'required|in_list['.$users_for_rules.']');
		$this->form_validation->set_rules('etape', 'Etape', 'required|in_list['.$allowed_steps_for_rules.']');

		if ($this->form_validation->run() == FALSE) {
			$this->detail($note_id);
			return;
		}

		$this->note_model->register_new_workflow($note_id, $this->input->post('valideur_id'), $this->input->post('etape'));

		redirect('note/detail/'.$note_id);
	}

	public function delete_workflow($note_id, $etape)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();
		elseif ( ! $this->note_query_model->exists(array('id' => $note_id)) ) // checks the note exists
			show_404();


		// check if the note may have this step validator being deleted
		$note = $this->note_model->find_all_note_details($note_id);

		if ($note->validated || $note->refused) // impossible because finished
			show_404();

		if ($note->redacteur_id != $this->session->userdata('id')) // impossible because user not allowed
			show_404();

		if ($etape > count($note->workflow)) // impossible because does not exists)
			show_404();

		if ($etape <= $note->etape_actuelle) // impossible because this step is already validated, or waiting to be
			show_404();

		$this->note_model->delete_workflow($note_id, $etape);

		redirect('note/detail/'.$note_id);
	}

	private function commenter($note_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();
		elseif ( ! $this->note_query_model->exists(array('id' => $note_id)) ) // checks the note exists
			show_404();

		
		// check if the note may be commented by a user
		$note = $this->note_model->find_all_note_details($note_id);

		if ($note->validated || $note->refused)
			show_404();

		//check entries
		$this->form_validation->set_rules('commentaire', 'Commentaire', 'trim|required|max_length[3000]');

		if ($this->form_validation->run() == FALSE) {
			$this->detail($note_id);
			return;
		}

		$note = $this->note_model->register_comment($note_id, $this->input->post('commentaire'));

		// mail to each user in the workflow + the note creator
		if ($note !== FALSE)
			$this->mail_model->envoi_note_new_comment($note);

		$titre = ($note !== FALSE) ? 'Commentaire enregistré' : 'Erreur';
		$phrase = ($note !== FALSE) ? 'Le commentaire a été enregistré avec succès. Tous les utilisateurs ont été prévenus par email.' : 'Une erreur d\'enrgistrement est survenue. Veuillez recommencer.';

		self::$_data += array('titre' => $titre, 'phrase' => $phrase, 'redirection' => 'note/detail/'.$note_id, 'phrase_bouton' => 'Retour à la page de détail de la note');

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	private function valider($note_id, $validated)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();
		elseif ( ! $this->note_query_model->exists(array('id' => $note_id)) ) // checks the note exists
			show_404();

		
		// check if the note may be validated by this user
		$note = $this->note_model->find_all_note_details($note_id);

		if ($note->validated || $note->refused || $note->valideur_attendu_id != $this->session->userdata('id'))
			show_404();

		//check entries
		$rules = $validated ? 'trim|max_length[3000]' : 'trim|required|max_length[3000]';
		$this->form_validation->set_rules('commentaire', 'Commentaire', $rules);

		if ($this->form_validation->run() == FALSE) {
			$this->detail($note_id);
			return;
		}

		$note = $this->note_model->register_validation($note_id, $this->input->post('commentaire'), $validated);

		if ($note === FALSE) {
			self::$_data += array('titre' => 'Erreur', 'phrase' => 'Une erreur d\'enrgistrement est survenue. Veuillez recommencer.', 'redirection' => 'note/detail/'.$note_id, 'phrase_bouton' => 'Retour à la page de détail de la note');

			$this->load->view('templates/header', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}
		
		if ($note->validated) { // last validation
			// mail to each user in the workflow + the note creator
			$this->mail_model->envoi_note_terminee($note);
		} elseif ($note->refused) { // refusal
			// mail to each user in the workflow + the note creator
			$this->mail_model->envoi_note_refusee($note);
		} else { // validation but still other validations required
			// mail to the next user in the workflow
			$this->mail_model->envoi_note_a_valider($note);
		}

		// if a comment was posted, but not for a refusal, we also send a mail
		if ( ! $note->refused && ! empty($this->input->post('commentaire')) ) {
			// mail to each user in the workflow + the note creator
			$this->mail_model->envoi_note_new_comment($note);
		}

		$titre = ($validated) ? 'Validation enregistrée' : 'Refus enregistré';
		$phrase = ($validated) ? 'Votre validation a été enregistrée avec succès.' : 'Votre refus a été enregistré avec succès.';

		self::$_data += array('titre' => $titre, 'phrase' => $phrase, 'redirection' => 'note/detail/'.$note_id, 'phrase_bouton' => 'Retour à la page de détail de la note');

		$this->load->view('templates/header', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function download_pj($note_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();
		elseif ( ! $this->note_query_model->exists(array('id' => $note_id)) ) // checks the note exists
			show_404();

		
		$this->load->library('zip');
		$this->load->helper('file');
		$path = FCPATH.'uploads/note/';
		if (is_file($path.'pieces_jointes.zip'))
			unlink($path.'pieces_jointes.zip');
		$uploads = $this->note_upload_query_model->read(array('note_id' => $note_id));

		if (count($uploads) == 1) {
			foreach ($uploads as $upload)
				force_download($path.$upload->file_name, NULL);
		} else {
			ob_start();
			$dataZip = array();
			foreach ($uploads as $upload) {
				$data = file_get_contents($path.$upload->file_name);
				$this->zip->add_data($upload->file_name, $data);
			}
			$this->zip->archive($path.'pieces_jointes.zip');
			$this->zip->download('pieces_jointes.zip');
		}
	}

	public function download_pdf($note_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier')) )
			show_404();
		elseif ((string)((int)$note_id) !== $note_id)
			show_404();
		elseif ( ! $this->note_query_model->exists(array('id' => $note_id)) ) // checks the note exists
			show_404();

		
		// check if the note has been validated by all users
		$note = $this->note_model->find_all_note_details($note_id);

		if ( ! $note->validated )
			show_404();

		self::$_data += array('titre' => 'Note N°'.$note_id.' au format PDF', 'note' => $note);

		$this->load->view('note/note_pdf', self::$_data);
	}

}
