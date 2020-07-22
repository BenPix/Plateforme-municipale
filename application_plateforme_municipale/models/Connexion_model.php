<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Connexion_model extends CI_Model
{

	/*
	* create a token to reset the password user
	*/
	public function create_token($user)
	{
		$token = bin2hex(openssl_random_pseudo_bytes(16));

		$now = new DateTime();
		$timestamp_now = $now->getTimestamp();
		$options_echappees = array('utilisateur_id' => $user->id, 'nom' => $token, 'donnee_timestamp' => $timestamp_now);
		$options_non_echappees = array('date_token' => 'NOW()');

		$this->token_for_password_query_model->delete(array('utilisateur_id' => $user->id));
		$this->token_for_password_query_model->create($options_echappees, $options_non_echappees);
		
		return $token;
	}

	/*
	* check the token according to its name, the user and the timing
	*/
	public function check_token($token, $utilisateur_id)
	{
		$now = new DateTime();
		$timestamp_max = $now->getTimestamp() - 600; // 10 minutes validity
		$data = array(
			'nom' => $token,
			'utilisateur_id' => $utilisateur_id,
			'donnee_timestamp > ' => $timestamp_max
		);

		return $this->token_for_password_query_model->exists($data);
	}

	/*
	* check the token according to its name, the user and the timing
	*/
	public function reset_password($token, $utilisateur_id, $password)
	{
		$this->db->trans_start();

		$where = array('id' => $utilisateur_id);
		$options_echappees = array('password' => password_hash($password, PASSWORD_DEFAULT));

		$succed = $this->utilisateur_query_model->update($where, $options_echappees);
		if ( ! $succed ) return FALSE;

		$this->token_for_password_query_model->delete(array('utilisateur_id' => $utilisateur_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/*
	* define the name of the municipality to display it correctly
	*/
	public function check_nom_commune()
	{
		return $this->db->select('nom')
		->from('commune')
		->where('id=1')
		->get()
		->row()
		->nom;
	}

	/*
	* define the logo image extension to display it correctly
	*/
	public function check_logo_extension($logo_name)
	{
		$extensions = array('png', 'jpg', 'jpeg', 'svg');

		foreach ($extensions as $value) {
			if (file_exists('assets/images/'.$logo_name.'.'.$value)) return $logo_name.'.'.$value;
		}
	}

	/*
	* check if the user may connect to the platform
	*/
	public function check_connexion($pseudo, $password)
	{
		// check if the user's pseudo and password are ok, if the account is validated, and if the user has not been disactivated
		$user = $this->utilisateur_query_model->find_user_for_connexion($pseudo, $password);
		if ($user === FALSE) return 'unknown_pseudo';

		$check = password_verify($password, $user->password);

		if ( ! $check ) return 'wrong_password';

		if ( ! $user->valide ) return 'unvalidated_account';

		if ( ! $user->actif ) return 'disactivated_account';

		return $user;
	}

	/*
	* find which modules are activated
	*/
	public function check_modules()
	{
		$result = $this->module_query_model->read(array('actif' => 1));

		return array_column($result, 'module');
	}

}