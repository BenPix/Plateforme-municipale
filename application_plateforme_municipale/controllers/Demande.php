<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Demande extends MY_Controller {

	const LIMIT = 40;

	public function index()
	{
		$this->demande_interservices();
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
	* ACCES AU FORMULAIRE DES DEMANDES INTERSERVICES
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
	public function demande_interservices($error = '')
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();


		$pole = $this->pole_query_model->liste_poles_sollicitables_for_dropdown();

		self::$_data += array(
			'error' => $error,
			'pole' => $pole,
			'titre' => 'Demande'
		);
		$page = 'interservices/formulaire_interservices';

		// vérification que l'utilisateur appartient bien a un et un seul sous pole dans le cas ou son pole est confidentiel
		$confidentiality_is_ok = $this->interservices_model->check_for_confidentiality($this->session->userdata('pole'), $this->session->userdata('id'));

		if ( ! $confidentiality_is_ok ) {
			self::$_data += array(
				'phrase' => 'Le responsable de votre Pôle ne vous a pas encore affecté à un service. Vous ne pourrez effectuer des demandes interservices qu\'après cette affectation.',
				'phrase_bouton' => 'Retour sur la page d\'accueil',
				'redirection' => 'accueil'
			);
			$page = 'validations/page_de_redirection';
		}

		$this->load->view('templates/header', self::$_data);
		$this->load->view($page);
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
	* TRAITEMENT DE LA DEMANDE
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
	public function demande_interservices_traitement()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();

		
		// check confidentiality
		$confidentiality_is_ok = $this->interservices_model->check_for_confidentiality($this->session->userdata('pole'), $this->session->userdata('id'));

		if ( ! $confidentiality_is_ok )
			redirect('demande/demande_interservices');

		// check entries
		$poles = $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$poles_rules = $this->form_validation_model->convert_dropdown_for_rules($poles);

		$this->form_validation->set_rules('direction_sollicitee[]', 'Direction sollicitée', 'required|in_list['.$poles_rules.']');
		$this->form_validation->set_rules('demande', 'Demande', 'trim|required|min_length[5]|max_length[3000]');
		$this->form_validation->set_rules('delai', 'Délai', 'in_list[au mieux,date précise,délai maximum]');
		$this->form_validation->set_rules('date_souhaitee', 'Date souhaitée', 'validate_date');
		$this->form_validation->set_rules('urgence', 'Degré d\'urgence', 'required|in_list[1,2,3,4,5]');

		if ($this->form_validation->run() == FALSE) {
			$this->demande_interservices();
			return;
		}

		// config and check the uploads
		$config['upload_path']          = $upload_path = 'uploads/interservices';
		$config['allowed_types']        = 'gif|jpg|jpeg|png|pdf|txt|doc|docx|xls|xlsx';
		$config['max_size']             = 8000;
		$config['max_width']            = 0;
		$config['max_height']           = 0;
		$config['file_name']        	= 'piece_jointe_interservices';
		$config['max_filename_increment']	= 9999;

		$data_uploads = $this->interservices_model->check_uploads($config);

		if ($data_uploads['check'] !== TRUE) {
			$this->demande_interservices($data_uploads['error']);
			return;
		}

		$pole_attache_id = $this->session->userdata('pole_id');

		// creates a different demande interservices for each selected pole
		$data_escaped = array(
			'pole_attache_id' => $pole_attache_id,
			'utilisateur_id' => $this->session->userdata('id'),
			'demande' => $this->input->post('demande'),
			'statut_id' => 1,
			'degre_urgence' => $this->input->post('urgence')
		);
		$data_deadlines = array(
			'delai' => $this->input->post('delai'),
			'date_souhaitee' => $this->input->post('date_souhaitee')
		);

		$demandes = $this->interservices_model->register_demande_interservices($this->input->post('direction_sollicitee'), $data_escaped, $data_deadlines, $data_uploads);

		// if any issue, warning page displayed
		if ($demandes === FALSE) {
			self::$_data += array('titre' => 'Echec', 'phrase' => 'Une erreur est survenue, et la demande n\'a pas pu être envoyée. Veuillez recommencer.', 'redirection' => 'demande', 'phrase_bouton' => 'Retour au formulaire des demandes interservices');

			$this->load->view('templates/header', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');

			return;
		}

		// email to the responsable if needed, for each demande
		foreach ($demandes as $demande) {
			if ($this->session->userdata('rang') !== 'responsable')
				$this->mail_model->envoi_demande_validation($demande);
		}

		$phrase_debut = count($demandes) === 1 ? 'La demande interservices a bien été envoyée.' : 'Les demandes interservices ont bien été envoyées.';
		$phrase = ($this->session->userdata('rang') === 'responsable') ? $phrase_debut : $phrase_debut.' Elle est en attente de validation par le responsable du service.';

		self::$_data += array('titre' => 'Demande effectuée', 'phrase' => $phrase, 'redirection' => 'accueil', 'phrase_bouton' => 'Retour sur la page d\'accueil');

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
	* DETAILS DE LA DEMANDE
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
	public function detail($id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur', 'admin')) )
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// checks the demande exists and the user is allowed to see it
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);
		$tab_demande = $this->demande_interservices_query_model->find_demande_detail($id);

		if ($demande === FALSE || $tab_demande === FALSE) {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucune de vos demandes interservices.',
				'redirection' => 'accueil',
				'phrase_bouton' => 'Retour à la page d\'accueil',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$commentaires = $this->commentaire_demande_interservices_query_model->find_for_detail($id);
		$sous_poles = array('Aucun' => array('0' => '---')) + $this->sous_pole_query_model->liste_poles_sollicitables_avec_opt_group_par_pole($demande->direction_sollicitee_id);

		// keeping flashdata for the come back to the page
		$this->interservices_model->manage_filter();

		// mark_notification_as_read in case of
		$notif_list = $this->notification_model->find_user_notifications($this->session->userdata('id'));
		$notification_has_succed = $this->notification_model->mark_notification_as_read($notif_list, $id, 'demande_interservices');

		// if a notif has succed by marking as read, we need to reload the menu, to update the notif icon
		if ($notification_has_succed) {
			$modules = $this->connexion_model->check_modules();
			self::$_data['menu_personnalise'] = $this->menu_model->menu_custom($modules, $this->session->userdata('rang'));
		}

		self::$_data += array(
			'titre' => 'Détail',
			'tab_demande' => $tab_demande,
			'demande' => $demande,
			'commentaires' => $commentaires,
			'form_open' => 'demande/maj/'.$id,
			'affectation_possible' => '',
			'direction_sollicitee' => '',
			'direction_attachee' => '',
			'commentaire_possible' => '',
			'sous_poles' => $sous_poles
		);
		
		// building data for some page sections, to define if they may be displayed
		if ($this->session->userdata('rang') === 'responsable' && $demande->responsable_sollicite_id === $this->session->userdata('id'))
			self::$_data['affectation_possible'] = 'ok';
		if ($this->session->userdata('pole') === $demande->direction_sollicitee && $this->session->userdata('rang') !== 'admin')
			self::$_data['direction_sollicitee'] = 'direction_sollicitee';		
		if ($this->session->userdata('pole') === $demande->direction_attachee && $this->session->userdata('rang') !== 'admin')
			self::$_data['direction_attachee'] = 'direction_attachee';
		if ($this->session->userdata('rang') !== 'admin')
			self::$_data['commentaire_possible'] = 'ok';
		if ($this->session->userdata('id') === $demande->demandeur_id)
			self::$_data['modif_delai_possible'] = 'ok';
		
		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/interservices_details');
		$this->load->view('templates/footer');
	}


	public function detail_final($id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur', 'admin')) )
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// checks the demande exists and the user is allowed to see it
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);
		$tab_demande = $this->demande_interservices_query_model->find_demande_detail($id);

		if ($demande === FALSE || $tab_demande === FALSE) {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucune de vos demandes interservices.',
				'redirection' => 'accueil',
				'phrase_bouton' => 'Retour à la page d\'accueil',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$commentaires = $this->commentaire_demande_interservices_query_model->find_for_detail($id);
		
		// keeping flashdata for the come back to the page
		$this->interservices_model->manage_filter();

		// mark_notification_as_read in case of
		$notif_list = $this->notification_model->find_user_notifications($this->session->userdata('id'));
		$notification_has_succed = $this->notification_model->mark_notification_as_read($notif_list, $id, 'demande_interservices');

		// if a notif has succed by marking as read, we need to reload the menu, to update the notif icon
		if ($notification_has_succed) {
			$modules = $this->connexion_model->check_modules();
			self::$_data['menu_personnalise'] = $this->menu_model->menu_custom($modules, $this->session->userdata('rang'));
		}

		self::$_data += array(
			'titre' => 'Détail',
			'tab_demande' => $tab_demande,
			'demande' => $demande,
			'commentaires' => $commentaires
		);
		
		$this->load->view('templates/header',self::$_data);
		$this->load->view('interservices/interservices_detail_final');
		$this->load->view('templates/footer');
	}
	/*
	*
	*
	*
	*
	*
	*
	* MISE A JOUR D'UNE DEMANDE (commentaire, maj statut ou relance)
	*
	*
	*
	*
	*
	*
	*/
	public function maj($id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// keeping flashdata for the come back to the page
		$this->interservices_model->manage_filter();

		if ($this->input->post('maj') == 'Poster le commentaire')
			$this->commentaire($id);
		elseif ($this->input->post('maj') == 'Mettre à jour')
			$this->statut($id);
		elseif ($this->input->post('maj') == 'Relancer')
			$this->relance($id);
		elseif ($this->input->post('maj') == 'Affecter')
			$this->affecter($id);
		elseif ($this->input->post('maj') == 'Modifier le délai')
			$this->delai($id);
		else
			$this->detail($id);
	}
	/*
	*
	*
	*
	*
	*
	*
	*RELANCE
	*
	*
	*
	*
	*
	*
	*/
	public function relance($id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// check entries
		$this->form_validation->set_rules('urgence', 'Degré d\'urgence', 'required|in_list[1,2,3,4,5]');

		if ($this->form_validation->run() == FALSE)
			show_404();

		// check if the demande is valid (appear in demandes envoyees)
		$check_demande = $this->demande_interservices_query_model->table_demandes_envoyees($this->session->userdata('id'), 0, 1, array('id' => $id));

		if ($check_demande->num_rows() === 0)
			show_404();
		
		$succed = $this->interservices_model->register_relance($id, $this->input->post('urgence'));

		$titre = $succed ? 'Relance réussie' : 'Erreur de traitement';
		$phrase_bouton = $succed ? 'Retour à la page des demandes envoyées' : 'Retour à la page de détail de la demande';
		$redirection = $succed ? 'historique/demandes_envoyees/'.$this->session->flashdata('page') : 'demande/detail/'.$id;
		$phrase = $succed ? 'La demande a été relancée avec succès !!' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		
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
	*STATUT
	*
	*
	*
	*
	*
	*
	*/
	public function statut($id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// check entries
		if ($this->input->post('statut') == '4')
			$this->form_validation->set_rules('refus', 'Raison du refus', 'trim|required|max_length[255]');
		$this->form_validation->set_rules('statut', 'Statut de la demande', 'required|in_list[1,2,3,4]');

		if ($this->form_validation->run() == FALSE) {
			$this->detail($id);
			return;
		}

		// check if the demande is valid (appear in demandes recues)
		$check_demande = $this->demande_interservices_query_model->table_demandes_recues($this->session->userdata('id'), 0, 1, array('id' => $id));

		if ($check_demande->num_rows() === 0)
			show_404();

		$data_sent = array(
			'statut_id' => $this->input->post('statut'),
			'raison_refus' => $this->input->post('refus')
		);
		$succed = $this->interservices_model->update_statut($id, $data_sent);

		$titre = $succed ? 'Mise à jour réussie' : 'Erreur de traitement';
		$phrase_bouton = $succed ? 'Retour à la page des demandes reçues' : 'Retour à la page de détail de la demande';
		$redirection = $succed ? 'historique/demandes_recues/'.$this->session->flashdata('page') : 'demande/detail/'.$id;
		$phrase = $succed ? 'La mise à jour de la demande a été effectuée avec succès !!' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		
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
	* COMMENTAIRES
	*
	*
	*
	*
	*
	*
	*/
	public function commentaire($id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// check entries
		$this->form_validation->set_rules('commentaire', 'Commentaire', 'trim|required|max_length[3000]');

		if ($this->form_validation->run() == FALSE) {
			$this->detail($id);
			return;
		}

		// checks the demande exists and the user is allowed to see it
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);
		$tab_demande = $this->demande_interservices_query_model->find_demande_detail($id);

		if ($demande === FALSE || $tab_demande === FALSE) {
			self::$_data += array(
				'phrase' => 'Cet ID ne correspond à aucune de vos demandes interservices.',
				'redirection' => 'accueil',
				'phrase_bouton' => 'Retour à la page d\'accueil',
				'titre' => 'Erreur'
			);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('validations/page_de_redirection');
			$this->load->view('templates/footer');
			return;
		}

		$data_sent = array(
			'utilisateur_id' => $this->session->userdata('id'),
			'commentaire' => $this->input->post('commentaire'),
			'demande_interservices_id' => $id
		);
		$succed = $this->interservices_model->register_comment($id, $data_sent);

		$titre = $succed ? 'Commentaire enregistré' : 'Erreur de traitement';
		$phrase_bouton = 'Retour à la page de détail de la demande';
		$redirection = 'demande/detail/'.$id;
		$phrase = $succed ? 'Le commentaire a été enregistré avec succès !!' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		
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
	* AFFECTER A UN SOUS POLE
	*
	*
	*
	*
	*
	*
	*/
	public function affecter($id)
	{
		if ($this->session->userdata('rang') !== 'responsable')
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();

		
		// check entries
		$poles = $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$poles_rules = $this->form_validation_model->convert_dropdown_for_rules($poles);
		$sous_poles = $this->sous_pole_query_model->liste_sous_poles_for_dropdown();
		$sous_poles_rules = $this->form_validation_model->convert_dropdown_for_rules($sous_poles);

		$this->form_validation->set_rules('direction_sollicitee_id', 'Direction sollicitée', 'in_list['.$poles_rules.']');
		$this->form_validation->set_rules('sous_pole_id', 'Sous-catégorie', 'in_list['.$sous_poles_rules.',0]|coincide_pole[direction_sollicitee_id]');

		if ($this->form_validation->run() == FALSE) {
			$this->detail($id);
			return;
		}

		$succed = $this->interservices_model->register_affectation($id, $this->input->post('sous_pole_id'));

		$titre = $succed ? 'Affectation enregistrée' : 'Erreur de traitement';
		$phrase_bouton = $succed ? 'Retour à la page des demandes reçues' : 'Retour à la page de détail de la demande';
		$redirection = $succed ? 'historique/demandes_recues/'.$this->session->flashdata('page') : 'demande/detail/'.$id;
		$phrase = $succed ? 'La demande a été affectée à un service avec succès !!' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		
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
	*
	* MODIFICATION DU DÉLAI
	*
	*
	*
	*
	*
	*
	*
	*/
	public function delai($id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();
		elseif ((string)((int)$id) !== $id)
			show_404();


		// check entries
		$this->form_validation->set_rules('delai', 'Délai', 'in_list[au mieux,date précise,délai maximum]');
		$this->form_validation->set_rules('date_souhaitee', 'Date souhaitée', 'validate_date');

		if ($this->form_validation->run() == FALSE) {
			$this->detail($id);
			return;
		}

		// checks if demande exists, has the good status, and may be updated by the user (he must be the demandeur)
		$demande = $this->demande_interservices_query_model->find_demande_complete($id);

		if ($demande === FALSE)
			show_404();

		if ($demande->demandeur_id !== $this->session->userdata('id') || ! in_array($demande->etat, array('en attente', 'en cours')))
			show_404();

		$succed = $this->interservices_model->update_delai($id, array('delai' => $this->input->post('delai'), 'date_souhaitee' => $this->input->post('date_souhaitee')));

		$titre = $succed ? 'Modification du délai enregistrée' : 'Erreur de traitement';
		$phrase_bouton = $succed ? 'Retour à la page des demandes envoyées' : 'Retour à la page de détail de la demande';
		$redirection = $succed ? 'historique/demandes_envoyees/'.$this->session->flashdata('page') : 'demande/detail/'.$id;
		$phrase = $succed ? 'Le délai de la demande a été modifié avec succès !!' : 'Une erreur est survenue lors du traitement. Veuillez recommencer.';
		
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
	*
	* DOWNLOAD
	*
	*
	*
	*
	*
	*
	*
	*/
	public function download($demande_interservices_id)
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur', 'admin')) )
			show_404();
		elseif ((string)((int)$demande_interservices_id) !== $demande_interservices_id)
			show_404();

		
		// check if the demande is valid (appear in demandes recues), exception for admin
		$check_demande = $this->demande_interservices_query_model->table_demandes_recues($this->session->userdata('id'), 0, 1, array('id' => $id));

		if ($check_demande->num_rows() === 0 && $this->session->userdata('rang') !== 'admin')
			show_404();

		$this->load->library('zip');
		$this->load->helper('file');
		$path = FCPATH.'uploads/interservices/';
		if (is_file($path.'pieces_jointes.zip'))
			unlink($path.'pieces_jointes.zip');
		$uploads = $this->upload_query_model->read('demande_interservices_id='.$demande_interservices_id);

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

}
