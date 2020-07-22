<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Interservices_model extends CI_Model
{

	public function __construct() {
		parent::__construct();

		// the database is built, we check for the module interservices is activated, to load its models
		if ( ! $this->settings_query_model->empty_database() ) {
			if (in_array('interservices', $this->connexion_model->check_modules())) {
				$this->load->model('database_query/mysql/interservices/appartenance_sous_pole_query_model');
				$this->load->model('database_query/mysql/interservices/commentaire_demande_interservices_query_model');
				$this->load->model('database_query/mysql/interservices/demande_interservices_a_valider_query_model');
				$this->load->model('database_query/mysql/interservices/demande_interservices_affectee_a_sous_pole_query_model');
				$this->load->model('database_query/mysql/interservices/demande_interservices_confidentielle_query_model');
				$this->load->model('database_query/mysql/interservices/demande_interservices_origine_sous_pole_query_model');
				$this->load->model('database_query/mysql/interservices/demande_interservices_query_model');
				$this->load->model('database_query/mysql/interservices/demande_interservices_terminee_query_model');
				$this->load->model('database_query/mysql/interservices/echeance_demande_interservices_query_model');
				$this->load->model('database_query/mysql/interservices/relance_demande_interservices_query_model');
				$this->load->model('database_query/mysql/interservices/sous_pole_query_model');
				$this->load->model('database_query/mysql/interservices/sous_pole_inactive_query_model');
				$this->load->model('database_query/mysql/interservices/statut_demande_interservices_query_model');
				$this->load->model('database_query/mysql/interservices/upload_query_model');
			}
		}
	}

	// checks if the user belongs to only one sous_pole in case of his pole has confidentiality condition
	public function check_for_confidentiality($pole_nom, $user_id)
	{
		$pole = $this->pole_query_model->find(array('nom' => $pole_nom));

		if ($pole === FALSE) return FALSE;

		$pole_is_confidentiel = $pole->confidentialite === '1';

		return $pole_is_confidentiel ? $this->appartenance_sous_pole_query_model->exists_one_only(array('utilisateur_id' => $user_id)) : TRUE;
	}

	// check uploads and delete them if not ok, and returns an error message
	public function check_uploads($config)
	{
		$this->upload->initialize($config);

		$error = '';
		$userfile1_is_uploaded = TRUE;
		$userfile2_is_uploaded = TRUE;
		$userfile3_is_uploaded = TRUE;
		// check if a file exists for the 3 uploads
		$userfile1_is_empty = empty($_FILES['userfile1']['name']);
		$userfile2_is_empty = empty($_FILES['userfile2']['name']);
		$userfile3_is_empty = empty($_FILES['userfile3']['name']);

		// variables usefull for the next inserts in database
		$data_uploads = array(
			'check' => FALSE,
			'error' => '',
			'userfile1_is_empty' => $userfile1_is_empty,
			'userfile2_is_empty' => $userfile2_is_empty,
			'userfile3_is_empty' => $userfile3_is_empty
		);

		// on fait l'upload des CHAQUE pièce jointe, même si la 1ere renvoie une erreur
		// cela permet de préciser le type d'erreur pour CHAQUE pièce jointe
		// 1ere PJ
		if ( ! $userfile1_is_empty ) {
			$userfile1_is_uploaded = $this->upload->do_upload('userfile1');

			if ( ! $userfile1_is_uploaded ) {
				$error .= '<p class="error">Erreur sur la pièce jointe '.$this->upload->data('file_name').'</p>';
			} else {
				$userfile1 = array(
					'file_name' => $this->upload->data('file_name'),
					'file_type' => $this->upload->data('file_type'),
					'file_size' => $this->upload->data('file_size')
				);
				$data_uploads['userfile1'] = $userfile1;
			}
		}
		// 2e PJ
		if ( ! $userfile2_is_empty ) {
			$userfile2_is_uploaded = $this->upload->do_upload('userfile2');

			if ( ! $userfile2_is_uploaded ) {
				$error .= '<p class="error">Erreur sur la pièce jointe '.$this->upload->data('file_name').'</p>';
			} else {
				$userfile2 = array(
					'file_name' => $this->upload->data('file_name'),
					'file_type' => $this->upload->data('file_type'),
					'file_size' => $this->upload->data('file_size')
				);
				$data_uploads['userfile2'] = $userfile2;
			}
		}
		// 3e PJ
		if ( ! $userfile3_is_empty ) {
			$userfile3_is_uploaded = $this->upload->do_upload('userfile3');

			if ( ! $userfile3_is_uploaded ) {
				$error .= '<p class="error">Erreur sur la pièce jointe '.$this->upload->data('file_name').'</p>';
			} else {
				$userfile3 = array(
					'file_name' => $this->upload->data('file_name'),
					'file_type' => $this->upload->data('file_type'),
					'file_size' => $this->upload->data('file_size')
				);
				$data_uploads['userfile3'] = $userfile3;
			}
		}

		// si un des 3 upload a échoué, on efface ceux qui ont réussi
		if ( ! $userfile1_is_uploaded ||  ! $userfile2_is_uploaded || ! $userfile3_is_uploaded ) {
			if ($userfile1_is_uploaded && ! $userfile1_is_empty )
				unlink($upload_path.$userfile1['file_name']);
			if ($userfile2_is_uploaded && ! $userfile2_is_empty )
				unlink($upload_path.$userfile2['file_name']);
			if ($userfile3_is_uploaded && ! $userfile3_is_empty )
				unlink($upload_path.$userfile3['file_name']);

			$error .= $this->upload->display_errors('<p class="error">', '</p>');

			$data_uploads['error'] = $error;

			return $data_uploads;
		}

		$data_uploads['check'] = TRUE;

		return $data_uploads;
	}

	// do the inserts in the database and returns succed or failed
	public function register_demande_interservices($poles_sollicites, $data_escaped, $data_deadlines, $data_uploads)
	{
		$demande_data = array();

		$this->db->trans_start();

		foreach ($poles_sollicites as $pole_sollicite_id) :

			$data_escaped['pole_sollicite_id'] = $pole_sollicite_id;

			// if the user rank is responsable, num_dossier is set
			if ($this->session->userdata('rang') === 'responsable')
				$data_escaped['num_dossier'] = $this->increment_num_dossier();

			// inserting the basic data
			$demande_id = $this->demande_interservices_query_model->create_and_find_id($data_escaped, array('horodateur' => 'CURDATE()'));
			if ($demande_id === FALSE) return FALSE;

			// check and inserts for confidentiality
			$succed = $this->inserting_for_confidentiality($demande_id, $data_escaped);
			if ($succed === FALSE) return FALSE;

			// inserting deadlines
			$succed = $this->inserting_deadlines($demande_id, $data_deadlines);
			if ($succed === FALSE) return FALSE;

			// if the user rank is not responsable, the demande must be validated, so insert in the table demande_interservices_a_valider
			if ($this->session->userdata('rang') !== 'responsable') {
				$succed = $this->demande_interservices_a_valider_query_model->create(array('demande_interservices_id' => $demande_id));
				if ($succed === FALSE) return FALSE;
			}

			// uploading sent files
			$succed = $this->inserting_uploads($demande_id, $data_uploads);
			if ($succed === FALSE) return FALSE;

			// notifice users if validated demande
			if ($this->session->userdata('rang') === 'responsable')
				$this->notification_model->add_notification($demande_id, 'valide', 'demande_interservices');

			// preparing the data to return
			$demande_data[] = $this->demande_interservices_query_model->find_demande_complete($demande_id);

		endforeach;

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) return FALSE;

		return $demande_data;
	}

	// if the sending pole has confidentiality condition, the confidential status is set for the demande
	private function inserting_for_confidentiality($demande_id, $data_escaped)
	{
		$pole = $this->pole_query_model->find(array('id' => $data_escaped['pole_attache_id']));
		if ($pole === FALSE) return FALSE;

		if ($pole->confidentialite === '1') {
			$appartenance = $this->appartenance_sous_pole_query_model->find(array('utilisateur_id' => $data_escaped['utilisateur_id']));
			if ($appartenance === FALSE) return FALSE;

			return $this->demande_interservices_confidentielle_query_model->create(array('demande_interservices_id' => $demande_id, 'sous_pole_id' => $appartenance->sous_pole_id));
		}

		return TRUE;
	}

	// inserting deadlines depending on the form entries
	private function inserting_deadlines($demande_id, $data_deadlines)
	{
		if ($data_deadlines['delai'] == 'délai maximum' || $data_deadlines['delai'] == 'date précise') {
			$echeance = array(
				'echeance' => $data_deadlines['date_souhaitee'],
				'date_precise' => ($data_deadlines['delai'] == 'délai maximum') ? 0 : 1,
				'demande_interservices_id' => $demande_id
			);

			return $this->echeance_demande_interservices_query_model->create($echeance);
		}

		return TRUE;
	}

	// inserting uploads for each file sent
	private function inserting_uploads($demande_id, $data_uploads)
	{
		if ( ! $data_uploads['userfile1_is_empty'] ) {
			$data_uploads['userfile1']['demande_interservices_id'] = $demande_id;
			$succed = $this->upload_query_model->create($data_uploads['userfile1'], array('date_upload' => 'CURDATE()'));
			if ($succed === FALSE) return FALSE;
		}
		if ( ! $data_uploads['userfile2_is_empty'] ) {
			$data_uploads['userfile2']['demande_interservices_id'] = $demande_id;
			$succed = $this->upload_query_model->create($data_uploads['userfile2'], array('date_upload' => 'CURDATE()'));
			if ($succed === FALSE) return FALSE;
		}
		if ( ! $data_uploads['userfile3_is_empty'] ) {
			$data_uploads['userfile3']['demande_interservices_id'] = $demande_id;
			$succed = $this->upload_query_model->create($data_uploads['userfile3'], array('date_upload' => 'CURDATE()'));
			if ($succed === FALSE) return FALSE;
		}

		return TRUE;
	}

	// increments the num_dossier by 1
	private function increment_num_dossier() {
		$table = $this->demande_interservices_query_model->count_max_field('num_dossier');
		$row = $table[0];

		return (int)$row['num_dossier'] + 1;
	}

	// manage all the flashdata with the filter, if some data have been sent
	public function manage_filter($filter_is_activated = FALSE, $data_sent = array(), $demandes_recues = '')
	{
		switch ($demandes_recues) {
			case 'demandes_recues':
				return $this->manage_filter_demandes_recues($filter_is_activated, $data_sent);
				break;

			case 'demandes_envoyees':
				return $this->manage_filter_demandes_envoyees($filter_is_activated, $data_sent);
				break;

			case 'demandes_traitees':
				return $this->manage_filter_demandes_traitees($filter_is_activated, $data_sent);
				break;
			
			default:
				return $this->manage_all_filter();
				break;
		}
	}

	// manage the filter, for demande_recues
	private function manage_filter_demandes_recues($filter_is_activated, $data_sent)
	{
		// keeping the filter in flashdata for the page navigation
		$this->session->keep_flashdata('demandes_recues_en_attente');
		$this->session->keep_flashdata('demandes_recues_en_cours');
		$this->session->keep_flashdata('filtre_demandes_recues');
		$this->session->keep_flashdata('page');

		if ($filter_is_activated) {
			$en_attente = is_array($this->input->post('statut')) ? in_array('en attente', $this->input->post('statut')) : FALSE;
			$en_cours = is_array($this->input->post('statut')) ? in_array('en cours', $this->input->post('statut')) : FALSE;

			$this->session->set_flashdata('demandes_recues_en_attente', $en_attente);
			$this->session->set_flashdata('demandes_recues_en_cours', $en_cours);
			$this->session->set_flashdata('filtre_demandes_recues', $data_sent);
		}

		return $data_sent;
	}

	// manage the filter, for demandes_envoyees
	private function manage_filter_demandes_envoyees($filter_is_activated, $data_sent)
	{
		// keeping the filter in flashdata for the page navigation
		$this->session->keep_flashdata('demandes_envoyees_en_attente');
		$this->session->keep_flashdata('demandes_envoyees_en_cours');
		$this->session->keep_flashdata('filtre_demandes_envoyees');

		if ($filter_is_activated) {
			$en_attente = is_array($this->input->post('statut')) ? in_array('en attente', $this->input->post('statut')) : FALSE;
			$en_cours = is_array($this->input->post('statut')) ? in_array('en cours', $this->input->post('statut')) : FALSE;

			$this->session->set_flashdata('demandes_envoyees_en_attente', $en_attente);
			$this->session->set_flashdata('demandes_envoyees_en_cours', $en_cours);
			$this->session->set_flashdata('filtre_demandes_envoyees', $data_sent);
		}

		return $data_sent;
	}

	// manage the filter, for demandes_traitees
	private function manage_filter_demandes_traitees($filter_is_activated, $data_sent)
	{
		// keeping the filter in flashdata for the page navigation
		$this->session->keep_flashdata('filtre_demandes_terminees');

		if ($filter_is_activated) {
			$this->session->set_flashdata('filtre_demandes_terminees', $data_sent);
		}

		return $data_sent;
	}

	// manage all the filter by keeping each data in flashdata
	private function manage_all_filter()
	{
		$this->session->keep_flashdata('demandes_recues_en_attente');
		$this->session->keep_flashdata('demandes_recues_en_cours');
		$this->session->keep_flashdata('filtre_demandes_recues');
		$this->session->keep_flashdata('demandes_envoyees_en_attente');
		$this->session->keep_flashdata('demandes_envoyees_en_cours');
		$this->session->keep_flashdata('filtre_demandes_envoyees');
		$this->session->keep_flashdata('filtre_demandes_terminees');
		$this->session->keep_flashdata('page');

		return array();
	}

	// check each data, and if any is not empty, return TRUE
	public function filter_is_activated($data_sent) {
		foreach ($data_sent as $key => $value) {
			if ( ! empty($value) ) return TRUE;
		}

		return FALSE;
	}

	// builds the sous_pole dropdown according to the user rank and his pole(s)
	public function build_sous_poles_dropdown()
	{
		$poles = $this->pole_query_model->find_belonging_user_or_responsable_pole_ids($this->session->userdata('id'));
		$sous_pole = $this->sous_pole_query_model->liste_sous_poles_origine_avec_opt_group_par_pole($poles);
		$sous_pole = array('Tous' => 'Tous') + $sous_pole;

		return $sous_pole;
	}

	// register the relance and creates the notif
	public function register_relance($demande_id, $degre_urgence)
	{
		$this->db->trans_start();

		$this->relance_demande_interservices_query_model->insert_or_duplicate(array('demande_interservices_id' => $demande_id, 'date_relance' => 'CURDATE()'));

		$succed = $this->demande_interservices_query_model->update(array('id' => $demande_id), array('degre_urgence' => $degre_urgence));
		if ($succed === FALSE) return FALSE;

		$this->notification_model->add_notification($demande_id, 'relance', 'demande_interservices');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// update the demande statut and creates the notif
	public function update_statut($demande_id, $data_sent)
	{
		$this->db->trans_start();

		$this->demande_interservices_query_model->update(array('id' => $demande_id), array('statut_id' => $data_sent['statut_id']));
		$demande = $this->demande_interservices_query_model->find_demande_complete($demande_id);

		// if refused, register the comment (will be sent to explain the reason)
		if ($data_sent['statut_id'] == '4') { // refusé
			$comment_data = array(
				'utilisateur_id' => $this->session->userdata('id'),
				'commentaire' => $data_sent['raison_refus'],
				'demande_interservices_id' => $demande_id
			);
			$this->commentaire_demande_interservices_query_model->create($comment_data, array('horodateur' => 'NOW()'));

			$this->delete_uploads($demande_id);

			$this->mail_model->envoi_demande_refusee($demande, $data_sent['raison_refus']);
		}

		// if finished, register the end date
		elseif ($data_sent['statut_id'] == '3') { // terminé
			$this->demande_interservices_terminee_query_model->create(array('demande_interservices_id' => $demande_id), array('date_fin' => 'CURDATE()'));

			$this->notification_model->add_notification($demande_id, 'changement_de_statut', 'demande_interservices');

			$this->delete_uploads($demande_id);
		}
		
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// deletes the uploads
	private function delete_uploads($demande_id)
	{
		$uploads = $this->upload_query_model->read(array('demande_interservices_id' => $demande_id));
		$path = FCPATH.'uploads/interservices/';

		foreach ($uploads as $upload) {
			if (file_exists($path.$upload->file_name))
				unlink($path.$upload->file_name);
		}

		$this->upload_query_model->delete(array('demande_interservices_id' => $demande_id));
	}

	// register the comment
	public function register_comment($demande_id, $data_sent)
	{
		$this->db->trans_start();

		$this->commentaire_demande_interservices_query_model->create($data_sent, array('horodateur' => 'NOW()'));

		$this->notification_model->add_notification($demande_id, 'commentaire', 'demande_interservices');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// register/update/delete affectation
	public function register_affectation($demande_id, $sous_pole_id)
	{
		$this->db->trans_start();

		if ($sous_pole_id == '0') {
			$this->demande_interservices_affectee_a_sous_pole_model->delete(array('demande_interservices_id' => $demande_id));
		} else {
			$data = array('demande_interservices_id' => $demande_id, 'sous_pole_id' => $sous_pole_id);
			$this->demande_interservices_affectee_a_sous_pole_query_model->insert_or_duplicate($data);
		}

		$this->notification_model->add_notification($demande_id, 'affecte_sous_pole', 'demande_interservices');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// deletes the delai, then register the new delai if needed
	public function update_delai($demande_id, $data_sent)
	{
		$this->db->trans_start();

		$this->echeance_demande_interservices_query_model->delete(array('demande_interservices_id' => $demande_id));

		if ($data_sent['delai'] !== 'au mieux') {
			$new_data = array(
				'demande_interservices_id' => $demande_id,
				'echeance' => $data_sent['date_souhaitee'],
				'date_precise' => $data_sent['delai'] === 'date précise' ? 1 : 0
			);
			$this->echeance_demande_interservices_query_model->create($new_data);
		}

		$this->notification_model->add_notification($demande_id, 'delai', 'demande_interservices');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// validate + update the demande + notifice
	public function validate_demande_interservices($demande_id, $data_escaped, $data_deadlines, $sous_pole_id)
	{
		$this->db->trans_start();

		$data_escaped['num_dossier'] = $this->increment_num_dossier();

		// updating the basic data
		$this->demande_interservices_query_model->update(array('id' => $demande_id), $data_escaped);

		// inserting deadlines
		$this->updating_deadlines($demande_id, $data_deadlines);

		// inserting demande origin if mentionned
		if ($sous_pole_id != '0') {
			$this->demande_interservices_origine_sous_pole_query_model->create(array('demande_interservices_id' => $demande_id, 'sous_pole_id' => $sous_pole_id));
		}

		// delete in demandes_a_valider table
		$this->demande_interservices_a_valider_query_model->delete(array('demande_interservices_id' => $demande_id));

		// notifice users
		$this->notification_model->add_notification($demande_id, 'valide', 'demande_interservices');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// create/update/delete deadlines
	private function updating_deadlines($demande_id, $data_deadlines)
	{
		if ($data_deadlines['delai'] === 'délai maximum' || $data_deadlines['delai'] === 'date précise') {
			$echeance = array('echeance' => $data_deadlines['echeance'], 'date_precise' => ($data_deadlines['delai'] == 'délai maximum') ? 0 : 1, 'demande_interservices_id' => $demande_id);
			$this->echeance_demande_interservices_query_model->insert_or_duplicate($echeance);
		} else {
			$this->echeance_demande_interservices_query_model->delete(array('demande_interservices_id' => $demande_id));
		}
	}

	// refuse + update the demande + email
	public function refuse_demande_interservices($demande_id, $data_sent)
	{
		$this->db->trans_start();

		// updating the basic data
		$this->demande_interservices_query_model->update(array('id' => $demande_id), array('statut_id' => 4));

		// inserting comment (refusal)
		$this->commentaire_demande_interservices_query_model->create($data_sent, array('horodateur' => 'NOW()'));

		// delete in demandes_a_valider table
		$this->demande_interservices_a_valider_query_model->delete(array('demande_interservices_id' => $demande_id));

		// mailing to the demandeur
		$demande = $this->demande_interservices_query_model->find_demande_complete($demande_id);
		$this->mail_model->envoi_demande_refusee($demande, $data_sent['commentaire']);

		// deleting uploads
		$this->delete_uploads($demande_id);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

}