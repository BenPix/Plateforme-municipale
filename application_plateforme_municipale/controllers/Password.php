<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password extends CI_Controller {

	static $_data;

	public function __construct() {
		parent::__construct();

		self::$_data['commune'] = $this->connexion_model->check_nom_commune();
		self::$_data['commune_uppercase'] = strtoupper(self::$_data['commune']);
		self::$_data['logo_icone'] = $this->connexion_model->check_logo_extension('logo_icone');
		self::$_data['logo_entete'] = $this->connexion_model->check_logo_extension('logo_entete');
	}

	public function index()
	{
		show_404();
	}

	public function new_password($token, $utilisateur_id)
	{
		self::$_data += array(
			'error' => $this->session->tempdata('error')
		);

		// check du token
		if ($this->connexion_model->check_token($token, $utilisateur_id)) {
			self::$_data += array('titre' => 'New Password', 'token' => $token, 'utilisateur_id' => $utilisateur_id);

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('pages/new_password');
			$this->load->view('templates/footer');
		} else {
			// rediriger vers page expliquant que les 10 min sont écoulées
			self::$_data['titre'] = 'Échec';

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('errors/reset_password');
			$this->load->view('templates/footer');
		}
	}

	public function reset()
	{
		$this->form_validation->set_rules('utilisateur_id', 'ID utilisateur', 'trim|required|integer|max_length[11]');
		$this->form_validation->set_rules('token', 'Token', 'required|valid_base64');
		$this->form_validation->set_rules('password', 'Mot de passe', 'trim|required|min_length[9]');
		$this->form_validation->set_rules('password_confirm', 'Confirmation du Mot de passe', 'trim|required|min_length[9]|matches[password]');

		$this->form_validation->set_message('matches', 'Vous n\'avez pas confirmé correctement votre mot de passe.');

		$token = $this->input->post('token');
		$utilisateur_id = $this->input->post('utilisateur_id');

		if ($this->form_validation->run() == FALSE) {
			$this->new_password($token, $utilisateur_id);
			return;
		}

		// check if the password differs from pseudo/email/nom/prenom
		if ( ! $this->utilisateur_query_model->check_password($this->input->post('password_confirm'), $utilisateur_id) ) {
			$this->session->set_tempdata('error', 'Votre mot de passe DOIT être différent de vos nom, prénom, pseudo et email.');
			$this->new_password($token, $utilisateur_id);
			return;
		}

		if ( ! $this->connexion_model->check_token($token, $utilisateur_id) ) {
			// rediriger vers page expliquant que les 10 min sont écoulées
			self::$_data['titre'] = 'Échec';

			$this->load->view('templates/header_login', self::$_data);
			$this->load->view('errors/reset_password');
			$this->load->view('templates/footer');
			return;
		}

		$succes = $this->connexion_model->reset_password($token, $utilisateur_id, $this->input->post('password'));

		if ($succes) {
			$titre = 'Succès';
			$phrase = 'Votre mot de passe a été réinitialisé avec succès !! Vous pouvez désormais vous connecter comme auparavant.';
			$redirection = 'login';
			$phrase_bouton = 'Retour à la page de connexion';
		} else {
			$titre = 'Échec';
			$phrase = 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.';
			$redirection = 'password/new_password/'.$token.'/'.$utilisateur_id;
			$phrase_bouton = 'Retour à la page de réinitialisation du mot de passe';
		}

		self::$_data += array(
			'titre' => $titre,
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton
		);

		$this->load->view('templates/header_login', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

}
