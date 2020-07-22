<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Form_validation_model extends CI_Model
{

	// builds the string list for the in_list validation rule from a dropdown menu array
	// for example : the users drop down menu : $users = array('1' => 'Username', '3', 'Otherusername') : '1' is the user ID, and 'Username' is his name
	// in this example, it will return : 1,3
	// when chekcing entries, the set_validation_rules will be set like this : 'in_list['.$return_value.']'
	public function convert_dropdown_for_rules(array $dropdown_menu_array, $key_or_value_to_assign = 'key')
	{
		$liste = '';

		foreach ($dropdown_menu_array as $key => $value) {
			if ($key_or_value_to_assign = 'key') $liste .= $key.',';
			elseif ($key_or_value_to_assign = 'value') $liste .= $value.',';
		}

		return rtrim($liste, ',');
	}

}