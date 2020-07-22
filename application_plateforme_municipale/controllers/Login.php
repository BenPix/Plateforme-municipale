<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	static $_data;

	public function __construct() {
		parent::__construct();

		// if it's the first connexion, redirect to the settings page to build the app
		if ($this->settings_query_model->empty_database()) {
			redirect('settings');
		} else {
			self::$_data['commune'] = $this->connexion_model->check_nom_commune();
			self::$_data['commune_uppercase'] = strtoupper(self::$_data['commune']);
			self::$_data['logo_icone'] = $this->connexion_model->check_logo_extension('logo_icone');
			self::$_data['logo_entete'] = $this->connexion_model->check_logo_extension('logo_entete');
		}
	}

	public function index()
	{
		$this->connexion();
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
	* CONNEXION
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
	public function connexion()
	{
		// destroy user session avant tout
		$this->session->unset_userdata(array('id', 'nom', 'prenom', 'pseudo', 'email', 'pole_id', 'pole', 'rang'));

		$this->session->keep_flashdata('visited_url'); // keep the uri, to redirect to the page after connexion

		self::$_data['error'] = $this->session->tempdata('error');
		self::$_data['titre'] = 'Connexion';

		$this->load->view('templates/header_login', self::$_data);
    	$this->load->view('pages/connexion');
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
	* INSCRIPTION
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
	public function inscription($error = '')
	{
		$pole = $this->pole_query_model->liste_tous_poles_for_drop_down();
		$google_recaptcha = $this->recaptcha_query_model->check_recaptcha_sitekey();
		$ci_captcha = empty($google_recaptcha) ? $this->inscription_model->create_my_captcha() : '';
		$error = empty($error) ? '' : '<div class="error">Vous n\'avez pas saisi correctement les caractères du catpcha. Veuillez recommencer.</div>';

		self::$_data += array(
			'titre' => 'Inscription',
			'error' => $error,
			'pole' => $pole,
			'google_recaptcha' => $google_recaptcha,
			'ci_captcha' => $ci_captcha,
		);

		$this->load->view('templates/header_login', self::$_data);
		$this->load->view('formulaires/inscription');
		$this->load->view('templates/footer');
	}

	public function inscrire()
	{
		// check ci_captcha if no google recaptcha registered
		if (empty($this->recaptcha_query_model->check_recaptcha_sitekey())) {
			if ( ! $this->inscription_model->check_my_captcha($this->input->post('ci_captcha')) ) {
	        	$this->inscription('erreur');
	        	return;
			}
		}
		// else, check the google recaptcha 
		else {
			$captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : FALSE;

	        if ( ! $captcha ) {
	        	echo ('<script>alert(\'Veuillez cocher la case \"Je ne suis pas un robot\" et saisir à nouveau vos mots de passe\');</script>');
	        	$this->inscription();
	        	return;
	        }
	        
	        if ( ! $this->inscription_model->check_recaptcha($captcha) )
	        	redirect('login/connexion');
		}


		// vérification des saisies
		$poles = $this->pole_query_model->liste_tous_poles_for_drop_down();
		$poles_rules = $this->form_validation_model->convert_dropdown_for_rules($poles);
		
		$this->form_validation->set_rules('pole', 'Appartenance', 'in_list['.$poles_rules.']|required');
		$this->form_validation->set_rules('nom', 'Nom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('prenom', 'Prénom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]');
		$this->form_validation->set_rules('pseudo', 'Pseudonyme', 'trim|required|french_names|max_length[50]|is_unique[utilisateur.pseudo]');
		$this->form_validation->set_rules('password', 'Mot de passe', 'trim|required|min_length[9]|differs[email]|differs[pseudo]|differs[prenom]|differs[nom]');
		$this->form_validation->set_rules('passwordConfirm', 'Confirmation du Mot de passe', 'trim|required|min_length[9]|matches[password]');		

		$this->form_validation->set_message('is_unique', 'Ce pseudonyme existe déjà.');
		$this->form_validation->set_message('matches', 'Vous n\'avez pas confirmé correctement votre mot de passe.');
		
		if ($this->form_validation->run() == FALSE) {
			$this->inscription();
			return;
		}

		// si la validation du formulaire est ok, on enregistre l'utilisateur dans la table utilisateur
		$data_utilisateur = array(
			'nom' => ucwords($this->input->post('nom'), ' -'),
			'prenom' => ucwords($this->input->post('prenom'), ' -'),
			'pseudo' => $this->input->post('pseudo'),
			'email' => $this->input->post('email'),
			'password' => $this->input->post('password'),
			'pole_id' => $this->input->post('pole')
		);

		$user = $this->inscription_model->register_user($data_utilisateur);

		$phrase = ($user === FALSE) ? 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.' : 'Inscription réussie !!';
		$phrase_bouton = ($user === FALSE) ? 'Retour à la page d\'inscription' : 'Retour à la page de connexion';
		$redirection = ($user === FALSE) ? 'login/inscription' : 'login/connexion';

		if ($user !== FALSE) {
			// envoi du mail pour prévenir l'admin
			$this->mail_model->envoi_demande_inscription($user, $user->email, $user->pole);
			// envoi du mail à l'utilisateur pour prévenir de l'enregistrement et de l'attente de validation
			$this->mail_model->envoi_inscription_en_attente($user, $user->email, $user->pole);
		}

		self::$_data += array(
			'phrase' => $phrase,
			'redirection' => $redirection,
			'phrase_bouton' => $phrase_bouton,
			'titre' => 'Inscription'
		);

		$this->load->view('templates/header_login', self::$_data);
		$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer');
	}

	public function check()
	{
		$this->form_validation->set_rules('pseudo', 'Pseudonyme', 'trim|required|french_names|max_length[50]');
		$this->form_validation->set_rules('password', 'Mot de passe', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$this->connexion();
			return;
		}

		$user = $this->connexion_model->check_connexion($this->input->post('pseudo'), $this->input->post('password'));

		if (is_string($user)) {
			switch ($user) {
				case 'unknown_pseudo':
					$error = 'Vos identifiants (pseudonyme et/ou mot de passe) n\'ont pas été saisis correctement. Veuillez recommencer.';
					break;
				case 'wrong_password':
					$error = 'Vos identifiants (pseudonyme et/ou mot de passe) n\'ont pas été saisis correctement. Veuillez recommencer.';
					break;
				case 'unvalidated_account':
					$error = 'Votre compte n\'a pas encore été validé par la Direction. Veuillez réessayer ultérieurement.';
					break;
				case 'disactivated_account':
					$error = 'Votre compte a été désactivé. Veuillez contacter la Direction, ou créer un nouveau compte.';
					break;
				
				default:
					$error = 'Erreur inconnue. Veuillez réessayer. Si le problème persiste, veuillez contacter la Direction.';
					break;
			}

			$this->session->set_tempdata('error', '<div class="error">'.$error.'</div>');
			$this->connexion();
			return;
		}

		// stocking userdata in the session
		foreach ($user as $key => $value)
			if ( ! in_array($key, array('password', 'actif', 'valide')) ) $userdata[$key] = html_escape($value);
		
		if (isset($userdata)) $this->session->set_userdata($userdata);

		var_dump($this->session->flashdata());

		if (NULL !== $this->session->flashdata('visited_url'))
			redirect($this->session->flashdata('visited_url'));
		else
			redirect('accueil');
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
	* MOT DE PASSE OUBLIE
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
	public function mot_de_passe_oublie()
	{
		self::$_data += array(
			'titre' => 'Mot de passe oublié',
			'error' => $this->session->tempdata('error')
		);

		$this->load->view('templates/header_login', self::$_data);
		$this->load->view('pages/reset_password');
		$this->load->view('templates/footer');
	}

	public function reset_password()
	{
		$this->form_validation->set_rules('pseudo', 'Pseudo', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]');

		if ($this->form_validation->run() == FALSE) {
			$this->mot_de_passe_oublie();
			return;
		}

		$user = $this->utilisateur_query_model->check_user_for_new_password($this->input->post('pseudo'), $this->input->post('email'));

		// check if the user exists, and if the user data are correct
		if ($user === FALSE) {
			$this->session->set_tempdata('error', 'Pseudonyme ou mot de passe incorrect');

			redirect('login/mot_de_passe_oublie');
		}

		$token = $this->connexion_model->create_token($user);
		
		$this->mail_model->envoi_new_password($user->email, $token, $user->id);

		self::$_data += array(
			'titre' => 'Mot de passe oublié',
			'error' => $this->session->tempdata('error')
		);

		$this->load->view('templates/header_login', self::$_data);
		$this->load->view('validations/password_sent');
		$this->load->view('templates/footer');
	}
}
