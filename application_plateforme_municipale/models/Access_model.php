<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Access_model extends CI_Model
{

	// protection to deny access to pages which are dependencies for some modules, needing to be activated
	public function check_access($activated_modules)
	{
		$controller = $this->uri->segment(1);
		$function = $this->uri->segment(2);
		$modules = $this->module_query_model->liste_modules_for_dropdown();

		foreach ($modules as $value) {
			$function_to_call = 'dependence_for_module_' . $value;

			$dependencies = $this->$function_to_call();

			if (in_array($controller, array_keys($dependencies))) {
				// we find the dependency, now check if the module is activated
				if ($dependencies[$controller] === TRUE || in_array($function, $dependencies[$controller]))
					return in_array($value, $activated_modules);
			}
		}

		// nothing found, so it's not a dependency
		return TRUE;
	}

	// gives the controllers (and their functions) being dependencies for the interservices module
	private function dependence_for_module_interservices()
	{
		return array(
			'demande' => TRUE,
			'historique' => TRUE,
			'statistique' => TRUE,
			'validation' => array(
				'interservices',
				'validation_demande',
				'confirmer_validation_demande',
				'refus_demande',
				'confirmer_refus_demande'
			),
			'gestion' => array(
				'sous_poles',
				'sous_pole_create',
				'sous_pole_delete',
				'sous_pole_update',
				'sous_pole_update_go',
				'sous_pole_update_modifier',
				'sous_pole_update_affecter',
				'sous_pole_update_desaffecter'
			)
		);
	}

	// gives the controllers (and their functions) being dependencies for the news module
	private function dependence_for_module_news()
	{
		return array(
			'accueil' => array(
				'article',
				'create_news',
				'publier_news',
				'update_news',
				'delete_news',
				'delete_news_go',
				'create_categorie',
				'delete_categorie',
				'acces_special',
				'attribuer_acces_special',
				'enlever_acces_special'
			),
			'validation' => array(
				'news',
				'previsualiser_news',
				'valider_news',
				'publier_news'
			)
		);
	}

	// gives the controllers (and their functions) being dependencies for the citoyen module
	private function dependence_for_module_note()
	{
		return array(
			'note' => TRUE
		);
	}

	// gives the controllers (and their functions) being dependencies for the citoyen module
	private function dependence_for_module_citoyen()
	{
		return array(
			'citoyen' => TRUE,
			'gestion' => array(
				'responsable_citoyen',
				'affecter_responsable_citoyen'
			)
		);
	}

}
