<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	static $_data;

	public function __construct() {
		parent::__construct();

		// check a session session exists
		if ( ! $this->session->has_userdata('nom') ) {
			$this->session->set_flashdata('visited_url', uri_string()); // recording the uri, to redirect to the page after next connexion
			redirect('login/index');
		}
		/* en cas de maintenance, remplacer le if au dessus par le code ci dessous
		if (!$this->session->has_userdata('nom')) {
			redirect('login/index');
		} else {
			redirect('login/maintenance');
		}*/

		$modules = $this->connexion_model->check_modules();

		// protection to deny access to pages according to the activated modules
		if ( ! $this->access_model->check_access($modules) ) show_404();

		// define settings of the site for a good display
		self::$_data['commune'] = $this->connexion_model->check_nom_commune();
		self::$_data['commune_uppercase'] = strtoupper(self::$_data['commune']);
		self::$_data['logo_icone'] = $this->connexion_model->check_logo_extension('logo_icone');
		self::$_data['logo_entete'] = $this->connexion_model->check_logo_extension('logo_entete');

		// build the menu according to the activated modules and the user's rank
		self::$_data['menu_personnalise'] = $this->menu_model->menu_custom($modules, $this->session->userdata('rang'));

		// loading models according to the activated modules
		foreach ($modules as $module)
			$this->load->model($module.'/'.$module.'_model');
	}

}
