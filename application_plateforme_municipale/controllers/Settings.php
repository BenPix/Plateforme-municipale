<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

	public function index($error = '')
	{
		// check if it's the first connexion (empty database)
		if ( ! $this->settings_query_model->empty_database() )
			redirect('login');

		$modules = array('interservices' => 'Interservices', 'news' => 'News', 'note' => 'Note');

		$this->load->view('templates/header_settings', array('error' => $error, 'modules' => $modules));
		$this->load->view('formulaires/settings');
		$this->load->view('templates/footer_settings');
	}

	public function start()
	{
		// check if it's the first connexion (empty database)
		if ( ! $this->settings_query_model->empty_database() )
			redirect('login');

		// check form entries
		$this->form_validation->set_rules('nom', 'Nom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('prenom', 'Prénom', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|max_length[50]');
		$this->form_validation->set_rules('pseudo', 'Pseudonyme', 'trim|required|french_names|max_length[50]');
		$this->form_validation->set_rules('password', 'Mot de passe', 'trim|required|min_length[9]|differs[email]|differs[pseudo]|differs[prenom]|differs[nom]');
		$this->form_validation->set_rules('password_confirm', 'Confirmation du Mot de passe', 'trim|required|min_length[9]|matches[password]', 'Vous n\'avez pas confirmé correctement votre mot de passe.');
		$this->form_validation->set_rules('commune', 'Nom de la Ville', 'trim|required|max_length[50]|french_names');
		$this->form_validation->set_rules('modules[]', 'Modules', 'required|in_list[interservices,citoyen,news,note]');

		if ($this->form_validation->run() == FALSE) {
			$this->index();
			return;
		}

		// check uploads
		if ($this->check_upload() !== TRUE) {
			$this->index($this->check_upload());
			return;
		}

		// creating data in the database
		$data_ok = $this->settings_model->saving_data();

		// display succed/failled page
		$phrase = ( ! $data_ok ) ? 'Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer.' : 'Le site est maintenant opérationnel. Cliquez sur le bouton ci-dessous, puis connectez-vous pour terminer de configurer le site.';
		$phrase_bouton = ( ! $data_ok ) ? 'Retour à la page de création' : 'Accéder à la page de connexion';

		$data = array('phrase' => $phrase, 'redirection' => 'settings', 'phrase_bouton' => $phrase_bouton);

		$this->load->view('templates/header_settings', $data);
    	$this->load->view('validations/page_de_redirection');
		$this->load->view('templates/footer_settings');
	}

	private function check_upload()
	{
		$error = '';
		$userfile1_is_uploaded = FALSE;
		$userfile2_is_uploaded = FALSE;
		$userfile1_is_empty = empty($_FILES['logo_entete']['name']);
		$userfile2_is_empty = empty($_FILES['logo_icone']['name']);

		// on fait l'upload des CHAQUE pièce jointe, même si la 1ere renvoie une erreur
		// cela permet de préciser le type d'erreur pour CHAQUE pièce jointe

		// 1ere PJ
		$config['upload_path']          = $upload_path = 'assets/images/';
		$config['allowed_types']        = 'jpg|jpeg|png';
		$config['file_ext_tolower']     = TRUE;
		$config['max_size']             = 100;
		$config['max_width']            = 0;
		$config['max_height']           = 0;
		$config['overwrite']        	= TRUE;
		$config['file_name']        	= 'logo_entete';

		$this->upload->initialize($config);

		if ( ! $userfile1_is_empty ) {
			$userfile1_is_uploaded = $this->upload->do_upload('logo_entete');

			if ( ! $userfile1_is_uploaded ) {
				$error .= '<p class="error">Erreur sur la pièce jointe '.$this->upload->data('file_name').'</p>';
				$error .= $this->upload->display_errors('<p class="error">', '</p>');
			} else {
				$userfile1 = array(
					'file_name' => $this->upload->data('file_name'),
					'file_type' => $this->upload->data('file_type'),
					'file_size' => $this->upload->data('file_size')
				);
			}
		}

		// 2e PJ
		$config['upload_path']          = $upload_path = 'assets/images/';
		$config['allowed_types']        = 'jpg|jpeg|png|svg';
		$config['file_ext_tolower']     = TRUE;
		$config['max_size']             = 100;
		$config['max_width']            = 0;
		$config['max_height']           = 0;
		$config['overwrite']        	= TRUE;
		$config['file_name']        	= 'logo_icone';

		$this->upload->initialize($config);

		if ( ! $userfile2_is_empty ) {
			$userfile2_is_uploaded = $this->upload->do_upload('logo_icone');

			if ( ! $userfile2_is_uploaded ) {
				$error .= '<p class="error">Erreur sur la pièce jointe '.$this->upload->data('file_name').'</p>';
				$error .= $this->upload->display_errors('<p class="error">', '</p>');
				//
			} else {
				$userfile2 = array(
					'file_name' => $this->upload->data('file_name'),
					'file_type' => $this->upload->data('file_type'),
					'file_size' => $this->upload->data('file_size')
				);
			}
		}

		// si un des 2 upload a échoué, on delete ceux qui ont réussi
		if ( ! $userfile1_is_uploaded || ! $userfile2_is_uploaded ) {
			if ($userfile1_is_uploaded && ! $userfile1_is_empty )
				unlink($upload_path.$userfile1['file_name']);
			if ($userfile2_is_uploaded && ! $userfile2_is_empty )
				unlink($upload_path.$userfile2['file_name']);

			return $error;
		}

		return TRUE;
	}
}
