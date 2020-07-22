<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/form_validation.html
 */
class MY_Form_validation extends CI_Form_validation {

	/**
	 * Value should be within an array of values
	 *
	 * @param	float
	 * @param	float
	 * @return	bool
	 */
	public function multiple_of($value, $multiple)
	{
		return fmod($value, $multiple) == 0;
	}

	// --------------------------------------------------------------------

	/**
	 * ne permet que des lettres avec ou sans accent, et le trait d'union
	 *
	 * @param	string
	 * @return	bool
	 */
	public function french_names($str)
	{
		return (bool) preg_match('/^[a-zâàäéèêëîïôöùûüÿ \'-]+$/i', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * vérifie que c'est bien un format de date
	 *
	 * @param	date
	 * @return	bool
	 */
	public function validate_date($date) {
		return ($date == '' || date('Y-m-d', strtotime($date)) == $date);
	}

	// --------------------------------------------------------------------

	/**
	 * vérifie que c'est bien un format de date
	 *
	 * @param	date
	 * @return	bool
	 */
	public function date_future($date) {
		return ($date > date('Y-m-d'));
	}

	// --------------------------------------------------------------------

	/**
	 * vérifie que c'est bien un format de date
	 *
	 * @param	date
	 * @return	bool
	 */
	public function validate_time($temps) {
		return (bool) preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $temps);
	}

	// --------------------------------------------------------------------

	/**
	 * vérifie que c'est bien un format de date
	 *
	 * @param	date
	 * @param	string
	 * @return	bool
	 */
	public function conges_coherents($dateFin, $field) {
		$dateDebut = $this->_field_data[$field]['postdata'];

		return (strtotime($dateFin) >= strtotime($dateDebut));
	}

	// --------------------------------------------------------------------

	/**
	 * vérifie que c'est bien un format de date
	 *
	 * @param	date
	 * @param	string
	 * @return	bool
	 */
	public function coincide_date($dateFin, $data) {
		$fields = explode(',', $data);

		if (!isset($this->_field_data[$fields[0]]) || !isset($this->_field_data[$fields[1]])){
			return FALSE;
		}

		$dateDebut = $this->_field_data[$fields[0]]['postdata'];
		$nombreJours = $this->_field_data[$fields[1]]['postdata'];

		$dateFin = new DateTime($dateFin);
		$dateDebut = new DateTime($dateDebut);
		$difference = $dateFin->diff($dateDebut)->format('%a') + 1;

		return round($nombreJours, 0, PHP_ROUND_HALF_UP) == $difference;
	}


	// --------------------------------------------------------------------

	/**
	 * vérifie que c'est bien un format de date
	 *
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	public function coincide_heure($heureFin, $data) {
		$fields = explode(',', $data);

		if (!isset($this->_field_data[$fields[0]]) || !isset($this->_field_data[$fields[1]])){
			return FALSE;
		}

		$heureDebut = $this->_field_data[$fields[0]]['postdata'];
		$nombreHeures = $this->_field_data[$fields[1]]['postdata'];
		$difference = $heureFin - $heureDebut;

		return $nombreHeures == $difference;
	}


	// --------------------------------------------------------------------

	/**
	 * vérifie que l'horaire coïncide avec les heures prestées rentrées pour chaque jour (matin + aprem)
	 *
	 * @param	int
	 * @param	string
	 * @return	bool
	 */
	public function coincide_heures_prestees($horaireHebdomadaire, $data)
	{
		$fields = explode(',', $data);

		foreach ($fields as $index => $value) {
			$horaireHebdomadaire -= (float)$this->_field_data[$fields[$index]]['postdata'];
		}
		return $horaireHebdomadaire == 0;
	}


	// --------------------------------------------------------------------

	/**
	 * vérifie que l'agent est marié avec un agent enregistré dans la base de donnée
	 *
	 * @param	string
	 * @param	string	field
	 * @return	bool
	 */
	public function mariage($prenom, $field)
	{
		$nom = $this->_field_data[$field]['postdata'];
		$where = array('nom' => $nom, 'prenom' => $prenom);

		return isset($this->CI->db)
			? ($this->CI->db->limit(1)->get_where('utilisateur', $where)->num_rows() === 1)
			: FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * vérifie que le sous-pole et le pole correspondent bien
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function coincide_pole($sous_pole_id, $field)
	{
		if ($sous_pole_id == '0')
			return TRUE;

		$pole_id = $this->_field_data[$field]['postdata'];

		return $this->CI->sous_pole_query_model->form_validation_coincide_pole($sous_pole_id, $pole_id);
	}

	// --------------------------------------------------------------------

	/**
	 * vérifie que le string ne comprend pas de caractères spéciaux
	 * sont autorisés les lettres, chiffres, espace, ponctuation classique, signes mathématiques courants, tels que :
	 * , " ? . : ; / ! * - + = ( ) [ ] & ' € %
	 *
	 * @param	date
	 * @return	bool
	 */
	public function french_content($string) {
		return (bool) preg_match('#^[\p{Latin}\s\'" ,\?\.:;/\!\*\+=\(\)&>@\#°€%\[\]0-9-]+$#u', $string);
	}

}
