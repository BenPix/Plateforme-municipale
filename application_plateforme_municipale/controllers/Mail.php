<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mail extends CI_Controller {

	public function sendmail()
	{
		$destinataire = $this->input->post('destinataire');
		$objet = $this->input->post('objet');
		$message = $this->input->post('body');
		$headers = $this->input->post('headers');

		mail($destinataire, $objet, $message, $headers);
	}
	
}
