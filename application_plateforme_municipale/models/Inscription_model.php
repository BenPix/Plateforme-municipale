<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Inscription_model extends CI_Model
{

	// check the Google recaptcha
	public function check_recaptcha($captcha)
	{
		// clé secrète correspondante au domaine sains-en-gohelle.pro
        $secret_key = $this->recaptcha_query_model->check_recaptcha_secretkey();
        $ip = $_SERVER['REMOTE_ADDR'];
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$captcha."&remoteip=".$ip);
        $response_keys = json_decode($response, TRUE);

        return intval($response_keys["success"]) === 1;
	}

	// creates a captcha with the CI library
	public function create_my_captcha()
	{
		$vals = array(
			'img_path' => 'captcha/',
			'img_url' => my_captcha_url(),
			'img_width' => 200,
			'img_height' => 40,
			'expiration' => 600
		);

		$captcha = create_captcha($vals);

		$captcha_data = array(
			'captcha_time' => $captcha['time'],
			'ip_address' => $this->input->ip_address(),
			'word' => $captcha['word']
		);
		$this->ci_captcha_query_model->create($captcha_data);

		$config['image_library'] = 'gd2';
		$config['source_image'] = $captcha['image'];
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width']         = 400;
		$config['height']       = 80;
		$this->load->library('image_lib', $config);
		$this->image_lib->resize();

		return $captcha['image'];
	}

	// checks the captcha user entry with the database record
	public function check_my_captcha($captcha_user_entry)
	{
		$expiration = time() - 600; // 10 min limit
		$this->ci_captcha_query_model->delete(array('captcha_time <' => $expiration)); // delete invalid captchas

		return $this->ci_captcha_query_model->exists(array(
			'word' => $captcha_user_entry,
			'ip_address' => $this->input->ip_address(),
			'captcha_time >' => $expiration
		));
	}

	// insert all the user's data
	public function register_user($data_utilisateur)
	{
		$this->db->trans_start();

		$email_data = $this->build_email_domaine($data_utilisateur['email']);

		$new_data_utilisateur = array(
			'nom' => $data_utilisateur['nom'],
			'prenom' => $data_utilisateur['prenom'],
			'pseudo' => $data_utilisateur['pseudo'],
			'email_nom' => $email_data['email_nom'],
			'email_domaine_id' => $email_data['email_domaine_id'],
			'password' => password_hash($data_utilisateur['password'], PASSWORD_DEFAULT),
			'pole_id' => $data_utilisateur['pole_id']
		);

		$user = $this->utilisateur_query_model->create_and_find($new_data_utilisateur);

		$this->inscription_a_valider_query_model->create(array('utilisateur_id' => $user->id));

		$user->email = $data_utilisateur['email'];
		$user->pole = $this->pole_query_model->find(array('id' => $data_utilisateur['pole_id']))->nom;

		$this->db->trans_complete();

		return $this->db->trans_status() ? $user : FALSE;
	}

	// update the user's data and delete in the validation and inactive table
	public function update_and_validate_user($user_id, $data_utilisateur)
	{
		$this->db->trans_start();

		$email_data = $this->build_email_domaine($data_utilisateur['email']);

		// managing the user rank and pole belonging in 3 cases
		if (in_array($data_utilisateur['rang'], array('responsable', 'admin')) || $data_utilisateur['pole_id'] == 1) {
			$managed_data = $this->gestion_model->register_pole_responsable($user_id, $data_utilisateur['rang'], $data_utilisateur['pole_id']);
			$data_utilisateur['rang'] = $managed_data['rang'];
			$data_utilisateur['pole_id'] = $managed_data['pole_id'];
		}

		$rang = $this->rang_utilisateur_query_model->find(array('rang' => $data_utilisateur['rang']));
		if ($rang === FALSE) return FALSE;

		$rang_id = $rang->id;

		$new_data_utilisateur = array(
			'nom' => $data_utilisateur['nom'],
			'prenom' => $data_utilisateur['prenom'],
			'email_nom' => $email_data['email_nom'],
			'email_domaine_id' => $email_data['email_domaine_id'],
			'pole_id' => $data_utilisateur['pole_id'],
			'rang_id' => $rang_id
		);

		$this->utilisateur_query_model->update(array('id' => $user_id), $new_data_utilisateur);

		$this->inscription_a_valider_query_model->delete(array('utilisateur_id' => $user_id)); // to define the user has been validated
		$this->utilisateur_inactive_query_model->delete(array('utilisateur_id' => $user_id)); // to define the user is active

		$user = $this->utilisateur_query_model->find(array('id' => $user_id));
		$user->email = $data_utilisateur['email'];
		$user->pole = $this->pole_query_model->find(array('id' => $data_utilisateur['pole_id']))->nom;

		$this->db->trans_complete();

		return $this->db->trans_status() ? $user : FALSE;
	}

	// delete the user
	public function delete_user($user_id)
	{
		$this->db->trans_start();
		
		$this->inscription_a_valider_query_model->delete(array('utilisateur_id' => $user_id));
		$this->utilisateur_query_model->delete(array('id' => $user_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// inactivate the user
	public function inactivate_user($user_id)
	{
		$this->db->trans_start();
		
		$this->utilisateur_inactive_query_model->create(array('utilisateur_id' => $user_id));

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// extract the email adress to find the domain and insert the user's data
	private function build_email_domaine($email)
	{
		$email = explode('@', $email);
		$email_nom = $email[0];
		$email_domaine = $email[1];

		$email_domaine_bdd = $this->email_domaine_query_model->find(array('domaine' => $email_domaine));

		$email_domaine_id = ($email_domaine_bdd !== FALSE) ? $email_domaine_bdd->id : $this->email_domaine_query_model->create_and_find_id(array('domaine' => $email_domaine));

		return array('email_domaine_id' => $email_domaine_id, 'email_nom' => $email_nom);
	}

	// create the dropdown menu with the available ranks, according to the chosen modules
	public function liste_rangs_for_drop_down()
	{
		$modules = $this->connexion_model->check_modules();
		$rangs = $this->rang_utilisateur_model->define_available_ranks($modules);
		$liste = array();

		foreach ($rangs as $key => $value)
			$liste[$key] = $value['nom'];
	    
	    return $liste;
	}

	// builds the html code to notice admin about ranks purpose
	public function build_rank_attribution_note()
	{
		$modules = $this->connexion_model->check_modules();
		$rangs = $this->rang_utilisateur_model->define_available_ranks($modules);

		// builds the html code
		$note = '<ul>';

		foreach ($rangs as $key => $value) {
			$note .= '<li><strong>' . $value['nom'] . '</strong> : ';

			if (count($value['acces']) === 1) {
				$note .= 'a uniquement accès ' . $value['acces'][0];
			} else {
				$note .= 'a accès ';

				foreach ($value['acces'] as $acces) {
					$note .= $acces . ', ';
				}

				$note = rtrim($note, ', ');
			}

			$note .= '.</li>';
		}
			
        $note .= '</ul>';

        return $note;
	}

	// finds the potential user for delegation, or in case of delegation, the delegate user
	public function manage_delegation($user_id)
	{
		$is_pole_responsable = $this->is_pole_responsable($user_id);
		$data = array(
			'is_ancien_responsable' => FALSE,
			'delegue_potentiel' => array(),
			'delegue' => ''
		);

		if ($is_pole_responsable) {
			$data['delegue_potentiel'] = $this->utilisateur_query_model->list_potential_delegue($user_id, $this->session->userdata('pole_id'));

			return $data;
		}

		$data['is_ancien_responsable'] = $this->delegation_query_model->exists(array('responsable_id' => $user_id));

		if ($data['is_ancien_responsable'])
			$data['delegue'] = $this->delegation_query_model->find_delegue($user_id);
		
		return $data;
	}

	// checks if the user is responsable for any pole
	public function is_pole_responsable($user_id)
	{
		return $this->pole_query_model->exists(array('responsable_id' => $user_id));
	}

	// update the user profil
	public function update_user_profil($data_sent)
	{
		$this->db->trans_start();
		
		if ($data_sent['nom'] == $this->session->userdata('nom')) unset($data_sent['nom']);
		if ($data_sent['prenom'] == $this->session->userdata('prenom')) unset($data_sent['prenom']);

		if ($data_sent['email'] != $this->session->userdata('email')) $data_sent += $this->build_email_domaine($data_sent['email']);
		unset($data_sent['email']);

		if (empty($data_sent['password'])) unset($data_sent['password']);
		else $data_sent['password'] = password_hash($data_sent['password'], PASSWORD_DEFAULT);

		if ( ! empty($data_sent) ) {
			$this->utilisateur_query_model->update(array('id' => $this->session->userdata('id')), $data_sent);
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// register the delegation
	public function register_delegation($delegue_id)
	{
		$this->db->trans_start();

		// on trouve le rang du futur délégué car il faudra insérer cette donnée
		$ancien_rang_delegue_id = $this->define_real_delegate_rank($delegue_id);

		// on vérifie avant que le responsable qui veut déléguer n'est pas un délégué
		$responsable_is_already_delegate = $this->delegation_query_model->exists(array('delegue_id' => $this->session->userdata('id')));

		// register the user's delegation
		$this->register_user_delegation($delegue_id, $ancien_rang_delegue_id, $responsable_is_already_delegate);

		// updates the poles data and register this update
		$this->creates_delegation_in_pole($delegue_id, $responsable_is_already_delegate);

		// update du rang du délégué dans la table utilisateur => responsable
		$this->utilisateur_query_model->update(array('id' => $delegue_id), array('rang_id' => 3));

		// returning data
		$data['poles_delegues'] = $this->pole_delegue_query_model->find_poles($this->session->userdata('id'));
		$data['delegue'] = $this->utilisateur_query_model->infos_utilisateur($delegue_id);

		$this->db->trans_complete();

		$data['succed'] = $this->db->trans_status();

		return $data;
	}

	// define the old rank of the user delegue
	private function register_user_delegation($delegue_id, $ancien_rang_delegue_id, $responsable_is_already_delegate)
	{
		// s'il l'est déjà, on met à jour la table delegation avec les données du nouveau délégué
		if ($responsable_is_already_delegate) {
			// mais avant, on rend son rang à l'utilisateur qui délègue
			$rang_id = $this->delegation_query_model->find(array('delegue_id' => $this->session->userdata('id')))->ancien_rang_delegue_id;

			$this->utilisateur_query_model->update(array('id' => $this->session->userdata('id')), array('rang_id' => $rang_id));

			// ensuite update de la table
			$data = array(
				'delegue_id' => $delegue_id,
				'ancien_rang_delegue_id' => $ancien_rang_delegue_id
			);

			$this->delegation_query_model->update(array('delegue_id' => $this->session->userdata('id')), $data);

		// sinon, simple insert dans la table delegation
		} else {
			$data = array(
				'responsable_id' => $this->session->userdata('id'),
				'delegue_id' => $delegue_id,
				'ancien_rang_delegue_id' => $ancien_rang_delegue_id
			);
		
			$this->delegation_query_model->create($data);
		}

		return $responsable_is_already_delegate;
	}

	// define the old rank of the user delegue
	private function define_real_delegate_rank($delegue_id)
	{
		// si l'agent a qui on souhaite déléguer est déjà le délégué d'un autre responsable, il va y avoir une erreur
		// le rang trouvé sera celui de responsable, alors que ce n'est pas le rang originel
		// il faut donc d'abord écarter cette possibilité, ou trouver la valeur réelle
		$delegate_is_already_delegate = $this->delegation_query_model->exists(array('delegue_id' => $delegue_id));

		// s'il l'est déjà, on met à jour la valeur de son ancien rang
		if ($delegate_is_already_delegate) {
			$delegue = $this->delegation_query_model->find(array('delegue_id' => $delegue_id));

			if ($delegue === FALSE) return FALSE;

			return $delegue->ancien_rang_delegue_id;
		}

		// on trouve le rang du futur délégué car il faudra insérer cette donnée
		$delegue = $this->utilisateur_query_model->find(array('id' => $delegue_id));

		if ($delegue === FALSE) return FALSE;

		return $delegue->rang_id;
	}

	// replace the responsable in the pole tables with the delegate
	private function creates_delegation_in_pole($delegue_id, $responsable_is_already_delegate)
	{
		// insert dans la table pole_delegue pour chaque pole dont l'agent déléguant est le responsable
		// et la boucle étant la même, on fait également l'update des poles pour la colonne du responsable
		$poles = $this->pole_query_model->poles_du_responsable($this->session->userdata('id'));

		foreach ($poles as $pole) :
			// insert dans la table pole_delegue seulement si le responsable n'est pas déjà un délégué
			if ( ! $responsable_is_already_delegate ) {
				$data = array(
					'pole_id' => $pole->id,
					'responsable_originel_id' => $this->session->userdata('id')
				);

				$this->pole_delegue_query_model->create($data);
			}

			// update dans la table pole dans tous les cas
			$this->pole_query_model->update(array('id' => $pole->id), array('responsable_id' => $delegue_id));

		endforeach;
	}

	// deletes the delegation and set back the pole data and the users data
	public function delete_delegation($user_id)
	{
		$this->db->trans_start();

		// returning data
		$data['poles_delegues'] = $this->pole_delegue_query_model->find_poles($user_id);

		// poles having delegation
		$poles = $this->pole_delegue_query_model->read(array('responsable_originel_id' => $user_id));

		foreach ($poles as $pole) {
			// deletes the pole delegation
			$this->pole_delegue_query_model->delete(array('pole_id' => $pole->pole_id));

			// set back the pole data
			$this->pole_query_model->update(array('id' => $pole->pole_id), array('responsable_id' => $user_id));
		}

		/// set back the users data
		$delegation = $this->delegation_query_model->find(array('responsable_id' => $user_id));
		if ($delegation === FALSE) return FALSE;

		$this->utilisateur_query_model->update(array('id' => $delegation->delegue_id), array('rang_id' => $delegation->ancien_rang_delegue_id));

		// deletes the user delegations
		$this->delegation_query_model->delete(array('responsable_id' => $user_id));

		// returning data
		$data['delegue'] = $this->utilisateur_query_model->infos_utilisateur($delegation->delegue_id);

		$this->db->trans_complete();

		$data['succed'] = $this->db->trans_status();

		return $data;
	}

}