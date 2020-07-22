<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Settings_model extends CI_Model
{

	public function saving_data()
	{
		$this->db->trans_start();

		// creating tables and default data
		$this->settings_query_model->build_database($this->input->post('modules'));

		// inserting user data
		$email = explode('@', $this->input->post('email'));
		$user_data = array(
			'nom' => $this->input->post('nom'),
			'prenom' => $this->input->post('prenom'),
			'pseudo' => $this->input->post('pseudo'),
			'email_nom' => $email[0],
			'email_domaine' => $email[1],
			'email_domaine_id' => 1,
			'rang_id' => 1,
			'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
			'pole_id' => 1,
			'commune' => $this->input->post('commune')
		);

		$this->settings_query_model->insert_user_data($user_data);

		// now the user is set, pole table may be updated
		$this->pole_query_model->update(array('id' => 1), array('responsable_id' => 1));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

}