<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Historique extends MY_Controller {

	const LIMIT = 40;

	public function index()
	{
		$this->demandes_recues();
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
	* HISTORIQUE DES DEMANDES RECUES
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
	public function demandes_recues()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();


		// vérification des entrées
		$poles = $this->pole_query_model->liste_tous_poles_for_drop_down();
		$poles_rules = $this->form_validation_model->convert_dropdown_for_rules($poles);
		$sous_poles = $this->sous_pole_query_model->liste_sous_poles_for_dropdown();
		$sous_poles_rules = $this->form_validation_model->convert_dropdown_for_rules($sous_poles);

		$this->form_validation->set_rules('dossier_depart', '', 'is_natural');
		$this->form_validation->set_rules('dossier_fin', '', 'is_natural');
		$this->form_validation->set_rules('date_depart', '', 'validate_date');
		$this->form_validation->set_rules('date_fin', '', 'validate_date');
		$this->form_validation->set_rules('expediteur', '', 'in_list['.$poles_rules.',Tous]');
		$this->form_validation->set_rules('sous_pole', '', 'in_list['.$sous_poles_rules.',Tous]');
		$this->form_validation->set_rules('statut', '', 'in_list[en cours,en attente]');
		
		$data_sent = array(
			'dossier_depart' => $this->input->post('dossier_depart'),
			'dossier_fin' => $this->input->post('dossier_fin'),
			'date_depart' => $this->input->post('date_depart'),
			'date_fin' => $this->input->post('date_fin'),
			'expediteur' => $this->input->post('expediteur'),
			'sous_pole' => $this->input->post('sous_pole'),
			'les_statuts' => $this->input->post('statut')
		);

		$filter_is_activated = $this->interservices_model->filter_is_activated($data_sent);

		// if it fails, it's an hack attempt, because entry fields types are imposed
		if ($filter_is_activated && $this->form_validation->run() == FALSE)
			show_404();

		$filtre = $this->interservices_model->manage_filter($filter_is_activated, $data_sent, 'demandes_recues');

		// generate the data
		$tab_entier = $this->demande_interservices_query_model->table_demandes_recues($this->session->userdata('id'), 0, 99999, $filtre);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();

		// the page is set in flashdata to redirect to the good page after any update by the user
		$this->session->set_flashdata('page', $page + 1);

		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);
		$legende_couleurs = $this->demande_interservices_query_model->legende_couleur_demandes_recues($this->session->userdata('id'), $page * self::LIMIT, self::LIMIT, $filtre);
		$tab_reception_avec_couleur = $this->demande_interservices_query_model->table_demandes_recues_avec_couleur($this->session->userdata('id'), $page * self::LIMIT, self::LIMIT, $filtre);

		$pole_attache = array('Tous' => 'Tous') + $this->pole_query_model->liste_tous_poles_for_drop_down();
		$sous_pole = $this->interservices_model->build_sous_poles_dropdown();

		self::$_data += array(
			'titre' => 'Historique',
			'pole_attache' => $pole_attache,
			'sous_pole' => $sous_pole,
			'pagination' => $pagination,
			'legende_couleurs' => $legende_couleurs,
			'tab_reception_avec_couleur' => $tab_reception_avec_couleur
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/historique_demandes_recues');
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
	* HISTORIQUE DES DEMANDES ENVOYEES
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
	public function demandes_envoyees()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();


		// check entries
		$poles = $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$poles_rules = $this->form_validation_model->convert_dropdown_for_rules($poles);
		$sous_poles = $this->sous_pole_query_model->liste_sous_poles_for_dropdown();
		$sous_poles_rules = $this->form_validation_model->convert_dropdown_for_rules($sous_poles);

		$this->form_validation->set_rules('dossier_depart', '', 'is_natural');
		$this->form_validation->set_rules('dossier_fin', '', 'is_natural');
		$this->form_validation->set_rules('date_depart', '', 'validate_date');
		$this->form_validation->set_rules('date_fin', '', 'validate_date');
		$this->form_validation->set_rules('destinataire', '', 'in_list['.$poles_rules.',Tous]');
		$this->form_validation->set_rules('sous_pole', '', 'in_list['.$sous_poles_rules.',Tous]');
		$this->form_validation->set_rules('statut', '', 'in_list[en cours,en attente]');

		$data_sent = array(
			'dossier_depart' => $this->input->post('dossier_depart'),
			'dossier_fin' => $this->input->post('dossier_fin'),
			'date_depart' => $this->input->post('date_depart'),
			'date_fin' => $this->input->post('date_fin'),
			'destinataire' => $this->input->post('destinataire'),
			'sous_pole' => $this->input->post('sous_pole'),
			'les_statuts' => $this->input->post('statut')
		);

		$filter_is_activated = $this->interservices_model->filter_is_activated($data_sent);

		// if it fails, it's an hack attempt, because entry fields types are imposed
		if ($filter_is_activated && $this->form_validation->run() == FALSE)
			show_404();

		$filtre = $this->interservices_model->manage_filter($filter_is_activated, $data_sent, 'demandes_envoyees');
		
		// generate the data
		$tab_entier = $this->demande_interservices_query_model->table_demandes_envoyees($this->session->userdata('id'), 0, 99999, $filtre);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();

		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);
		$legende_couleurs = $this->demande_interservices_query_model->legende_couleur_demandes_envoyees($this->session->userdata('id'), $page * self::LIMIT, self::LIMIT, $filtre);
		$tab_envoi_avec_couleur = $this->demande_interservices_query_model->table_demandes_envoyees_avec_couleur($this->session->userdata('id'), $page * self::LIMIT, self::LIMIT, $filtre);

		$pole_sollicite = array('Tous' => 'Tous') + $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$sous_pole = $this->interservices_model->build_sous_poles_dropdown();

		// envoi des données + load de la page
		self::$_data += array(
			'titre' => 'Historique',
			'menu' => 'envoi',
			'pole_sollicite' => $pole_sollicite,
			'sous_pole' => $sous_pole,
			'legende_couleurs' => $legende_couleurs,
			'tab_envoi_avec_couleur' => $tab_envoi_avec_couleur,
			'pagination' => $pagination
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/historique_demandes_envoyees');
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
	* HISTORIQUE DES DEMANDES ENVOYEES
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
	public function demandes_terminees()
	{
		if ( ! in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_particulier', 'utilisateur_superieur')) )
			show_404();


		// check entries
		$poles_expediteur = $this->pole_query_model->liste_tous_poles_for_drop_down();
		$poles_destinataire = $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$poles_expediteur_rules = $this->form_validation_model->convert_dropdown_for_rules($poles_expediteur);
		$poles_destinataire_rules = $this->form_validation_model->convert_dropdown_for_rules($poles_destinataire);

		$this->form_validation->set_rules('dossier_depart', '', 'is_natural');
		$this->form_validation->set_rules('dossier_fin', '', 'is_natural');
		$this->form_validation->set_rules('date_depart', '', 'validate_date');
		$this->form_validation->set_rules('date_fin', '', 'validate_date');
		$this->form_validation->set_rules('expediteur', '', 'in_list['.$poles_expediteur_rules.',Tous]');
		$this->form_validation->set_rules('destinataire', '', 'in_list['.$poles_destinataire_rules.',Tous]');

		$data_sent = array(
			'dossier_depart' => $this->input->post('dossier_depart'),
			'dossier_fin' => $this->input->post('dossier_fin'),
			'date_depart' => $this->input->post('date_depart'),
			'date_fin' => $this->input->post('date_fin'),
			'destinataire' => $this->input->post('destinataire'),
			'expediteur' => $this->input->post('expediteur')
		);

		$filter_is_activated = $this->interservices_model->filter_is_activated($data_sent);

		// if it fails, it's an hack attempt, because entry fields types are imposed
		if ($filter_is_activated && $this->form_validation->run() == FALSE)
			show_404();

		$filtre = $this->interservices_model->manage_filter($filter_is_activated, $data_sent, 'demandes_traitees');

		// generate data
		$tab_entier = $this->demande_interservices_query_model->table_demandes_terminees($this->session->userdata('id'), 0, 99999, $filtre);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();

		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);
		$tab = $this->demande_interservices_query_model->table_demandes_terminees($this->session->userdata('id'), $page * self::LIMIT, self::LIMIT, $filtre);

		$pole_sollicite = array('Tous' => 'Tous') + $this->pole_query_model->liste_poles_sollicitables_for_dropdown();
		$pole_attache = array('Tous' => 'Tous') + $this->pole_query_model->liste_tous_poles_for_drop_down();

		self::$_data += array(
			'titre' => 'Historique',
			'pole_attache' => $pole_attache,
			'pole_sollicite' => $pole_sollicite,
			'tab' => $tab,
			'pagination' => $pagination
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/historique_demandes_terminees');
		$this->load->view('templates/footer');
	}
}
