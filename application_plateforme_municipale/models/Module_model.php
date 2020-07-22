<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Module_model extends CI_Model
{

	public function activate_modules($modules_to_activate)
	{
		$this->module_query_model->update(array(), array('actif' => 0));

		if (NULL == $modules_to_activate) $modules_to_activate = array();

		foreach ($modules_to_activate as $value) {
			$this->module_query_model->update(array('id' => $value), array('actif' => 1));
		}
	}

}