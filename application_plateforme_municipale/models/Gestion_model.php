<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Gestion_model extends CI_Model
{

	// creates a pole
	public function create_pole($pole_data, $autorise_au_bdc)
	{
		$this->db->trans_start();

		// depending on the disabled modules, we set some data
		$modules = $this->connexion_model->check_modules();
		$pole_data = $this->set_pole_data_according_to_modules($pole_data, $modules);

		$pole_id = $this->pole_query_model->create_and_find_id($pole_data);
		if ($pole_id === FALSE) return FALSE;

		// check if the insert must be done for the module bon_de_commande
		if ($autorise_au_bdc === '1') {
			$succed = $this->create_pole_for_bdc_module($pole_id, $modules);
			if ($succed === FALSE) return FALSE;
		}

		$succed = $this->set_responsable_rank($pole_data['responsable_id']);
		if ($succed === FALSE) return FALSE;

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// check if any of this pole data is used as foreign key. If not, it's deleted from the database. Otherwise, it's disabled, to keep the data persistence
	public function delete_pole($pole_id)
	{
		$this->db->trans_start();

		// check if any of this pole data is used as foreign key.
		$pole_data_is_used = $this->pole_query_model->table_data_is_used_in_foreign_key($pole_id);

		if ($pole_data_is_used) {
			$this->pole_inactive_query_model->create(array('pole_id' => $pole_id));
		} else {
			$this->pole_query_model->delete(array('id' => $pole_id));
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	
	// we set the user rank to responsable, to let him have the associated menu
	// we also set the previous responsable rank to 'utilisateur_superieur' if he's not an admin
	// exception for the default pole with ID 1, only admin users
	private function set_responsable_rank($user_id, $pole_id = 0)
	{
		if ($pole_id == 1) //  default pole update
			return $this->utilisateur_query_model->update(array('id' => $user_id), array('rang_id' => 1));

		if ($pole_id == 0) { // new pole creation
			if ($user_id == $this->session->userdata('id')) return TRUE; // no users have subscribe yet, the admin is set as pole responsable but keep his rank
			else return $this->utilisateur_query_model->update(array('id' => $user_id), array('rang_id' => 3));
		}

		// other pole update
		$pole = $this->pole_query_model->detail_pole_data($pole_id)->row();
		$previous_responsable = $this->utilisateur_query_model->infos_utilisateur($pole->responsable_id);
		$new_responsable = $this->utilisateur_query_model->infos_utilisateur($user_id);

		// an admin making update of a pole, and still being the responsable, must stay with admin rank
		// and to avoid any issue (losing last admin user) we forbid to change the admin user rank to anything
		if ($new_responsable->rang == 'admin')
			return TRUE;

		// an admin may be the previous responsable if he has created a pole without any user subscription yet
		// in this case, this user keeps the 'admin' rank, otherwise he gets the 'utilisateur_superieur' rank
		if ($previous_responsable->rang != 'admin')
			$this->utilisateur_query_model->update(array('id' => $pole->responsable_id), array('rang_id' => 4));

		return $this->utilisateur_query_model->update(array('id' => $user_id), array('rang_id' => 3));
	}

	// depending on the disabled modules, some pole data must be set
	private function set_pole_data_according_to_modules($pole_data, $modules)
	{
		if ( ! in_array('interservices', $modules) && ! in_array('citoyen', $modules) ) $pole_data['sollicitable_via_interservices'] = 0;

		return $pole_data;
	}

	// if the module is activated, inserting the data
	private function create_pole_for_bdc_module($pole_id, $modules)
	{
		if (in_array('bon_de_commande', $modules))
			return $this->pole_autorise_au_bon_de_commande_query_model->create(array('pole_id' => $pole_id));
	}

	// builds the table with headings and columns depending on the activated modules
	public function building_pole_table($pole_id)
	{
		$heading = array('Désignation', 'Email', 'Responsable');
		$pole_data = $this->pole_query_model->detail_pole($pole_id)->row();
		$row = array($pole_data->nom, $pole_data->email, $pole_data->responsable);

		// depending on the activated modules, we display some data
		$modules = $this->connexion_model->check_modules();

		if (in_array('interservices', $modules) || in_array('citoyen', $modules)) {
			$heading[] = 'Sollicitable';
			$row[] = $pole_data->sollicitable;
		}

		if (in_array('interservices', $modules)) {
			$heading[] = 'Confidentiel';
			$row[] = $pole_data->confidentiel;
		}

		if (in_array('bon_de_commande', $modules)) {
			$heading[] = 'Création de Bons de commande';
			$row[] = $pole_data->bdc;
		}

		$tableau = array(0 => $row);

		return array('heading' => $heading, 'tableau' => $tableau);
	}

	// if the module is activated, inserting the data
	public function update_pole($pole_id, $pole_data)
	{
		$this->db->trans_start();

		$succed = $this->set_responsable_rank($pole_data['responsable_id'], $pole_id);
		if ($succed === FALSE) return FALSE;

		if ($pole_id == 1) { // no updates allowed for the default pole, except to set a new responsable
			$succed = $this->pole_query_model->update(array('id' => $pole_id), array('responsable_id' => $pole_data['responsable_id']));

			$this->db->trans_complete();

			return $this->db->trans_status();
		}

		// update the pole_autorise_au_bon_de_commande table with a delete and create if value = 1
		$succed = $this->pole_autorise_au_bon_de_commande_query_model->delete(array('pole_id' => $pole_id));
		if ( ! $succed) return FALSE;
		
		if ($pole_data['bdc'] === '1') {
			$succed = $this->pole_autorise_au_bon_de_commande_query_model->create(array('pole_id' => $pole_id));
			if ( ! $succed) return FALSE;
		}

		// update the pole table
		unset($pole_data['bdc']);
		$succed = $this->pole_query_model->update(array('id' => $pole_id), $pole_data);
		if ( ! $succed) return FALSE;

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// creates a sous_pole
	public function create_sous_pole($sous_pole_data)
	{
		$this->load->model('database_query/mysql/interservices/sous_pole_query_model');

		$this->db->trans_start();

		$this->sous_pole_query_model->create($sous_pole_data);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// check if any of this pole data is used as foreign key. If not, it's deleted from the database. Otherwise, it's disabled, to keep the data persistence
	public function delete_sous_pole($sous_pole_id)
	{
		$this->load->model('database_query/mysql/interservices/sous_pole_query_model');
		$this->load->model('database_query/mysql/interservices/sous_pole_inactive_query_model');

		$this->db->trans_start();

		// check if any of this sous-pole data is used as foreign key.
		$sous_pole_data_is_used = $this->sous_pole_query_model->table_data_is_used_in_foreign_key($sous_pole_id);

		if ($sous_pole_data_is_used) {
			$this->sous_pole_inactive_query_model->create(array('sous_pole_id' => $sous_pole_id));
		} else {
			$this->sous_pole_query_model->delete(array('id' => $sous_pole_id));
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// update the sous pole
	public function update_sous_pole($sous_pole_id, $updated_data)
	{
		$this->load->model('database_query/mysql/interservices/sous_pole_query_model');

		$this->db->trans_start();

		$this->sous_pole_query_model->update(array('id' => $sous_pole_id), $updated_data);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// creates the user belonging to the sous pole
	public function create_appartenance_sous_pole($data)
	{
		$this->load->model('database_query/mysql/interservices/appartenance_sous_pole_query_model');

		$this->db->trans_start();

		$this->appartenance_sous_pole_query_model->create($data);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// deletes the user belonging to the sous pole
	public function delete_appartenance_sous_pole($data)
	{
		$this->load->model('database_query/mysql/interservices/appartenance_sous_pole_query_model');

		$this->db->trans_start();

		$this->appartenance_sous_pole_query_model->delete($data);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// list of all the potential user for a sous_pole, according to it's pole_mere confidentiality status
	public function list_potential_user_for_sous_pole_for_dropdown($sous_pole_id, $pole_mere_confidentialite)
	{
		if ($pole_mere_confidentialite)
			return $this->utilisateur_query_model->list_for_confidential_sous_pole($sous_pole_id);

		return $users = $this->utilisateur_query_model->list_for_sous_pole($sous_pole_id);
	}

	// a user rank is set to 'responsable', so we update the pole (with some conditions)
	// return the final user rank
	public function register_pole_responsable($user_id, $rank, $pole_id)
	{
		// the pole with ID 1 is a default pole. It cannot have anything else than admins, and all admins belong to this pole
		if ($pole_id == 1 || $rank == 'admin')
			return array('rang' => 'admin', 'pole_id' => 1);

		$pole = $this->pole_query_model->detail_pole_data($pole_id)->row();
		$responsable = $this->utilisateur_query_model->infos_utilisateur($pole->responsable_id);

		// an admin may be the previous responsable if he has created a pole without any user subscription yet
		// in this case, this user keeps the 'admin' rank, otherwise he gets the 'utilisateur_superieur' rank
		if ($responsable->rang != 'admin')
			$this->utilisateur_query_model->update(array('id' => $pole->responsable_id), array('rang_id' => 4));

		$this->pole_query_model->update(array('id' => $pole_id), array('responsable_id' => $user_id));

		return array('rang' => 'responsable', 'pole_id' => $pole_id);
	}

}