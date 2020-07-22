<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends CI_Controller {

	public function index()
	{
		show_404();
	}

	public function marked_as_read()
	{
		$this->notification_model->mark_all_notifications_as_read($this->session->userdata('id'));

		if (isset($_SERVER['HTTP_REFERER']))
			redirect($_SERVER['HTTP_REFERER']);
		else
			redirect('accueil');
	}

}
