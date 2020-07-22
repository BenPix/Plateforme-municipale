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

$lang['form_validation_required']		= 'Le champ {field} est requis.';
$lang['form_validation_isset']			= 'Le champ {field} doit contenir une valeur.';
$lang['form_validation_valid_email']		= 'Veuillez entrer une adresse mail valide.';
$lang['form_validation_valid_emails']		= 'Le champ {field} doit contenir des adresses email valides.';
$lang['form_validation_valid_url']		= 'Le champ {field} doit contenir une URL valide.';
$lang['form_validation_valid_ip']		= 'Le champ {field} doit contenir une IP valide.';
$lang['form_validation_min_length']		= 'Le champ "{field}" doit être composé d\'au moins {param} caractères.';
$lang['form_validation_max_length']		= 'Le champ {field} ne peut excéder {param} caractères en longueur.';
$lang['form_validation_exact_length']		= 'Le champ {field} doit posséder exactement {param} caractères en longueur.';
$lang['form_validation_alpha']			= 'Le champ {field} ne peut contenir que des caractères alphabéthiques.';
$lang['form_validation_alpha_numeric']		= 'Le champ {field} ne peut contenir que des caractères alpha-numériques.';
$lang['form_validation_alpha_numeric_spaces']	= 'Le champ {field} fne peut contenir que des caractères alpha-numériques et des espaces.';
$lang['form_validation_alpha_dash']		= 'Le champ {field} ne peut comporter que des lettres, des chiffres, des underscores et des tirets.';
$lang['form_validation_numeric']		= 'Le champ {field} ne peut comporter que des chiffres.';
$lang['form_validation_is_numeric']		= 'Le champ {field} ne peut contenir que des caractères numériques.';
$lang['form_validation_integer']		= 'Le champ {field} ne peut contenir que des entiers.';
$lang['form_validation_regex_match']		= 'Le champ {field} n\'est pas au format requis.';
$lang['form_validation_matches']		= 'Le champ {field} ne correspond pas au champ {param}.';
$lang['form_validation_differs']		= 'Le champ {field} doit être différent du champ {param}.';
$lang['form_validation_is_unique'] 		= 'Le champ {field} doit contenir une valeur unique.';
$lang['form_validation_is_natural']		= 'Le champ {field} ne peut contenir qu\'un nombre entier positif.';
$lang['form_validation_is_natural_no_zero']	= 'Le champ {field} doit contenir un nombre entier positif supérieur à zéro.';
$lang['form_validation_decimal']		= 'Le champ {field} doit contenir un nombre décimal.';
$lang['form_validation_less_than']		= 'Le champ {field} doit contenir un nombre inférieur à {param}.';
$lang['form_validation_less_than_equal_to']	= 'Le champ {field} doit contenir un nombre inférieur ou égal à {param}.';
$lang['form_validation_greater_than']		= 'Le champ {field} doit contenir un nombre supérieur à {param}.';
$lang['form_validation_greater_than_equal_to']	= 'Le champ {field} doit contenir un nombre supérieur ou égal à {param}.';
$lang['form_validation_error_message_not_set']	= 'Impossible d\'accéder au message d\'erreur du champ {field}.';
$lang['form_validation_in_list']		= 'Le champ {field} doit contenir une de ces valeurs: {param}.';
$lang['form_validation_multiple_of']		= 'Le champ {field} doit être un multiple de {param}.';
$lang['form_validation_french_names']		= 'Le champ {field} ne peut comporter que des lettres (accents autorisés), apostrophes, espace et tirets.';
$lang['form_validation_validate_date']		= 'La date saisie du champ {field} n\'est pas valide.';
$lang['form_validation_validate_time']		= 'L\'heure saisie du champ {field} n\'est pas valide.';
$lang['form_validation_coincide_date']		= 'La date de fin de congés ne correspond pas avec les jours posés, compte tenu de la date de début de congés.';
$lang['form_validation_coincide_heure']		= 'L\'heure de fin de congés ne correspond pas avec les heures posées, compte tenu de l\'heure de début de congés.';
$lang['form_validation_coincide_heures_prestees']		= 'Le total d\'heures prestées ne correspond pas avec l\'horaire hebdomadaire.';
$lang['form_validation_mariage']		= 'L\'agent mentionné dans la situation maritale n\'est pas enregistré dans la base de donnée.';
$lang['form_validation_valid_base64']		= 'Le champ {field} ne peut comporter que des lettres de l\'alphabet (sans accent) et des chiffres.';
$lang['form_validation_coincide_pole']		= 'Le champ {field} ne correspond pas à son Pôle mère {param}.';
$lang['form_validation_date_future']		= 'La date {field} doit être postérieure à la date de ce jour.';
$lang['form_validation_french_content']		= 'Le champ {field} ne peut contenir que les lettres de l\'alphabet, accents compris, ainsi que les symboles de base de votre clavier. En voici la liste , ? . : ; / ! * - + = ( ) [ ] & \' € # @ ° % " .';
$lang['form_validation_conges_coherents']		= 'La date de fin de congés ne peut pas être antérieure au début des congés.';
