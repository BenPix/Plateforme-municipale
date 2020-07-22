<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Rang_utilisateur_model extends CI_Model
{

	/*
	* return an array containing ranks data to display the html code, to notice admin about ranks purpose
	*/
	public function define_available_ranks($modules)
	{
		// default ranks
		$rangs = $this->build_default_ranks();

		// ranks according to the activated modules
		foreach ($modules as $value) {
			$function_to_call = 'build_ranks_for_' . $value;

			$this->$function_to_call($rangs);
		}

		// reorder the ranks
		$rangs = $this->ordering_ranks($rangs);

		return $rangs;
	}

	private function build_default_ranks()
	{
		return array(
			'utilisateur_superieur' => array(
				'nom' => 'Utilisateur supérieur',
				'acces' => array(
					'à tout',
					'sauf la <u>gestion</u> et la <u>validation</u>'
				)
			),
			'responsable' => array(
				'nom' => 'Responsable de Service',
				'acces' => array(
					'à tout',
					'sauf la <u>gestion du site</u>'
				)
			),
			'admin' => array(
				'nom' => 'Administrateur du site',
				'acces' => array(
					'à la <u>gestion du site</u>'
				)
			)
		);

		return;
	}

	private function build_ranks_for_interservices(&$rangs)
	{
		if (isset($rangs['utilisateur_particulier']))
			array_push($rangs['utilisateur_particulier']['acces'], 'aux <u>demandes interservices</u>');
		else
			$rangs += array(
				'utilisateur_particulier' => array(
				'nom' => 'Utilisateur particulier',
				'acces' => array(
					'aux <u>demandes interservices</u>'
				)
			)
		);

		return;
	}

	private function build_ranks_for_news(&$rangs)
	{
		if (isset($rangs['utilisateur_particulier']))
				array_push($rangs['utilisateur_particulier']['acces'], 'à la <u>création de news</u>');
			else
				$rangs += array(
					'utilisateur_particulier' => array(
					'nom' => 'Utilisateur particulier',
					'acces' => array(
						'à la <u>création de news</u>'
					)
				)
			);

			if (isset($rangs['redacteur']))
				array_push($rangs['redacteur']['acces'], 'à la <u>gestion des news</u>');
			else
				$rangs += array(
					'redacteur' => array(
					'nom' => 'Rédacteur',
					'acces' => array(
						'à la <u>gestion des news</u>'
					)
				)
			);

			if (isset($rangs['utilisateur_simple']))
				array_push($rangs['utilisateur_simple']['acces'], 'aux <u>news</u>');
			else
				$rangs += array(
					'utilisateur_simple' => array(
					'nom' => 'Utilisateur simple',
					'acces' => array(
						'aux <u>news</u>'
					)
				)
			);

			return;
	}

	private function build_ranks_for_note(&$rangs)
	{
		if (isset($rangs['utilisateur_particulier']))
			array_push($rangs['utilisateur_particulier']['acces'], 'aux <u>notes</u>');
		else
			$rangs += array(
				'utilisateur_particulier' => array(
				'nom' => 'Utilisateur particulier',
				'acces' => array(
					'aux <u>notes</u>'
				)
			)
		);

		return;
	}

	private function build_ranks_for_citoyen(&$rangs)
	{
		return;
	}

	private function ordering_ranks($unordered_list)
	{
		$template_list = array('utilisateur_simple', 'utilisateur_particulier', 'utilisateur_superieur', 'responsable', 'rh', 'redacteur', 'admin');
		$ordered_list = array();

		foreach ($template_list as $seeked_key) {
			if (isset($unordered_list[$seeked_key])) $ordered_list[$seeked_key] = $unordered_list[$seeked_key];
		}

		return $ordered_list;
	}

	// find the rang_utilisateur.rang value according to the rang_utilisateur.nom of the user rank
	public function define_user_rank($rank_name)
	{
		$rank_data = $this->rang_utilisateur_query_model->find(array('nom' => $rank_name));

		return $rank_data === FALSE ? '' : $rank_data->rang;
	}

}