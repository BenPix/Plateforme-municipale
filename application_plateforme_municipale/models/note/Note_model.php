<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Note_model extends CI_Model
{

	public function __construct() {
		parent::__construct();

		// the database is built, we check for the module interservices is activated, to load its models
		if ( ! $this->settings_query_model->empty_database() ) {
			if (in_array('note', $this->connexion_model->check_modules())) {
				$this->load->model('database_query/mysql/note/note_query_model');
				$this->load->model('database_query/mysql/note/note_upload_query_model');
				$this->load->model('database_query/mysql/note/note_workflow_query_model');
				$this->load->model('database_query/mysql/note/note_commentaire_query_model');
				$this->load->model('database_query/mysql/note/note_validation_query_model');
				$this->load->model('database_query/mysql/note/note_refus_query_model');
			}
		}
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
	public function register_note($data_escaped, $data_workflow, $data_uploads)
	{
		$note_data = array();

		$this->db->trans_start();

		// inserting the basic data
		$note_id = $this->note_query_model->create_and_find_id($data_escaped, array('horodateur' => 'NOW()'));
		if ($note_id === FALSE) return FALSE;

		// inserting workflow
		$this->inserting_workflow($note_id, $data_workflow);

		// uploading sent files
		$this->inserting_uploads($note_id, $data_uploads);

		$all_note_details = $this->find_all_note_details($note_id);

		$this->db->trans_complete();

		return ($this->db->trans_status() === FALSE) ? FALSE : $all_note_details;
	}

	// inserting workflow depending on the form entries
	private function inserting_workflow($note_id, $data_workflow)
	{
		$etape = 1;

		foreach ($data_workflow as $value) {
			$this->note_workflow_query_model->create(array('note_id' => $note_id, 'utilisateur_id' => $value, 'etape' => $etape++));
		}
	}

	// inserting uploads for each file sent
	private function inserting_uploads($note_id, $data_uploads)
	{
		if ( ! $data_uploads['userfile1_is_empty'] ) {
			$data_uploads['userfile1']['note_id'] = $note_id;
			$this->note_upload_query_model->create($data_uploads['userfile1']);
		}
		if ( ! $data_uploads['userfile2_is_empty'] ) {
			$data_uploads['userfile2']['note_id'] = $note_id;
			$this->note_upload_query_model->create($data_uploads['userfile2']);
		}
		if ( ! $data_uploads['userfile3_is_empty'] ) {
			$data_uploads['userfile3']['note_id'] = $note_id;
			$this->note_upload_query_model->create($data_uploads['userfile3']);
		}
	}

	// find all the details of that note
	public function find_all_note_details($note_id)
	{
		$note = $this->note_query_model->find_note_detail($note_id);
		if ($note === FALSE) return FALSE;

		$note->workflow = $this->note_workflow_query_model->find_workflow_detail($note_id)->result();

		$note = $this->complete_workflow_details($note);

		$note->commentaires = $this->note_commentaire_query_model->find_comment_detail($note_id)->result();

		return $note;
	}

	// completes the note details with the next user who has to validate
	private function complete_workflow_details($note)
	{
		if ($note->validated || $note->refused) {
			$note->valideur_attendu_id = NULL;
			$note->valideur_attendu = NULL;
			$note->valideur_attendu_email = NULL;
			$note->etape_actuelle = NULL;

			return $note;
		}

		foreach ($note->workflow as $row) {
			if ( ! $row->validated ) {
				$note->valideur_attendu_id = $row->workflow_utilisateur_id;
				$note->valideur_attendu = $row->workflow_utilisateur;
				$note->valideur_attendu_email = $row->workflow_utilisateur_email;
				$note->etape_actuelle = $row->etape;

				break;
			}
		}

		return $note;
	}

	// find all the details of that note
	public function find_note_details_for_table(&$heading, $note)
	{
		$workflow_for_table = $this->note_workflow_query_model->find_workflow_detail_for_table($note->id);

		$table = $this->building_validation_table($workflow_for_table);

		// add a delete workflow column if allowed
		$this->adjust_workflow_table($heading, $table, $note);

		return $table;
	}

	// builds the validation table with the data
	private function building_validation_table($workflow_data)
	{
		$icone_validation_start = '<i title="Validé le ';
		$icone_validation = '" class="material-icons" style="color:green;font-size:50px;">done</i>';
		$icone_refusal_start = '<i title="Refusé le ';
		$icone_refusal = '" style="color:red;font-size:40px;">&#10008;</i>';
		$icone_waiting = '<i title="En attente de validation" class="material-icons" style ="color:#0033CC;font-size:40px;">hourglass_empty</i>';
		$table = array();

		foreach ($workflow_data->result_array() as $row) {
			unset($row['utilisateur_id']);

			if ($row['validated']) {
				$row['validated'] = $icone_validation_start.$row['validation_date'].$icone_validation;
				unset($row['refused']);
				unset($row['refusal_date']);
				unset($row['validation_date']);
				$table[] = $row;
			} elseif ($row['refused']) {
				$row['refused'] = $icone_refusal_start.$row['refusal_date'].$icone_refusal;
				unset($row['validated']);
				unset($row['validation_date']);
				unset($row['refusal_date']);
				$table[] = $row;
				break; // breaking the workflow not to display the next steps
			} else {
				$row['validated'] = $icone_waiting;
				unset($row['refused']);
				unset($row['refusal_date']);
				unset($row['validation_date']);
				$table[] = $row;
			}
		}

		return $table;
	}

	// builds the array containing the allowed steps, according to the actuel step and the maximum step
	public function build_allowed_steps($note)
	{
		$min_step = (int)$note->etape_actuelle + 1;
		$max_step = count($note->workflow) + 1;

		for ($i=$min_step; $i <= $max_step; $i++) { 
			$allowed_steps[$i] = $i;
		}

		return $allowed_steps;
	}

	// adjust the workflow table with an additionnal column to delete a step workflow, if possible
	private function adjust_workflow_table(&$heading, &$table, $note)
	{
		if ($note->validated || $note->refused) // impossible because finished
			return;

		if ($note->redacteur_id != $this->session->userdata('id')) // impossible because user not allowed
			return;

		if (count($note->workflow) == $note->etape_actuelle) // impossible because last validation step
			return;

		$heading[] = 'Supprimer';
		$deleting_link_start = $this->lien_model->delete_workflow_step_start();
		$deleting_link_end = $this->lien_model->delete_workflow_step_end();
		$impossible_deleting_icone = '<i class="material-icons" style="font-size:30px;">lock</i>';

		foreach ($table as &$row) {
			if ($row['etape'] > $note->etape_actuelle)
				$row['delete'] = $deleting_link_start.$note->id.'/'.$row['etape'].$deleting_link_end;
			else
				$row['delete'] = $impossible_deleting_icone;
		}
	}

	// register the new user added in the workflow in the database, update the colum etape, and returns the note object if succes, else false
	public function register_new_workflow($note_id, $valideur_id, $etape)
	{
		$this->db->trans_start();

		$this->note_workflow_query_model->update(array('note_id' => $note_id, 'etape >=' => $etape), array(), array('etape' => 'etape + 1'));

		$this->note_workflow_query_model->create(
			array(
				'note_id' => $note_id, 
				'utilisateur_id' => $valideur_id, 
				'etape' => $etape
			)
		);

		$note = $this->find_all_note_details($note_id);

		$this->db->trans_complete();

		return $this->db->trans_status() ? $note : FALSE;
	}

	// deleting a user in the workflow in the database, and update the colum etape
	public function delete_workflow($note_id, $etape)
	{
		$this->db->trans_start();

		$this->note_workflow_query_model->delete(array('note_id' => $note_id, 'etape' => $etape));

		$this->note_workflow_query_model->update(array('note_id' => $note_id, 'etape >' => $etape), array(), array('etape' => 'etape - 1'));

		$note = $this->find_all_note_details($note_id);

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	// register the comment in the database and returns the note object if succes, else false
	public function register_comment($note_id, $commentaire)
	{
		$this->db->trans_start();

		$this->note_commentaire_query_model->create(
			array(
				'note_id' => $note_id, 
				'utilisateur_id' => $this->session->userdata('id'), 
				'commentaire' => $commentaire
			), 
			array(
				'horodateur' => 'NOW()'
			)
		);

		$note = $this->find_all_note_details($note_id);

		$this->db->trans_complete();

		return $this->db->trans_status() ? $note : FALSE;
	}

	// register the comment in the database and returns the note object if succes, else false
	public function register_validation($note_id, $commentaire, $validated)
	{
		if (empty($commentaire) && ! $validated ) // comment is required if it's a refusal
			return FALSE;

		$this->db->trans_start();

		if ( ! empty($commentaire) ) {
			$this->note_commentaire_query_model->create(
				array(
					'note_id' => $note_id, 
					'utilisateur_id' => $this->session->userdata('id'), 
					'commentaire' => $commentaire
				), 
				array(
					'horodateur' => 'NOW()'
				)
			);
		}

		if ($validated) {
			$this->note_validation_query_model->create(
				array(
					'note_id' => $note_id,
					'utilisateur_id' => $this->session->userdata('id')
				), 
				array(
					'horodateur' => 'NOW()'
				)
			);
		} else {
			$this->note_refus_query_model->create(
				array(
					'note_id' => $note_id,
					'utilisateur_id' => $this->session->userdata('id')
				), 
				array(
					'horodateur' => 'NOW()'
				)
			);
		}

		$note = $this->find_all_note_details($note_id);

		$this->db->trans_complete();

		return $this->db->trans_status() ? $note : FALSE;
	}

	// manage the filter, creating the flashdata with the filter entries, for each section
	public function manage_filter($page_section)
	{
		if ($this->input->post('filtrer') == 'Filtrer') {

			$filter_data = array(
				'n.id >=' => $this->input->post('id_depart'),
				'n.id <=' => $this->input->post('id_fin'),
				'n.horodateur >=' => $this->input->post('date_depart'),
				'n.horodateur <=' => $this->input->post('date_fin'),
				'n.redacteur_id' => $this->input->post('redacteur_id') == '0' ? '' : $this->input->post('redacteur_id'),
				'n.objet' => $this->input->post('objet'),
			);

			$filter_form_data = array(
				'id_depart' => $this->input->post('id_depart'),
				'id_fin' => $this->input->post('id_fin'),
				'date_depart' => $this->input->post('date_depart'),
				'date_fin' => $this->input->post('date_fin'),
				'redacteur_id' => $this->input->post('redacteur_id'),
				'objet' => $this->input->post('objet'),
			);

			foreach ($filter_data as $key => $value) {
				if (empty($value))
					unset($filter_data[$key]);
			}

			$this->session->set_flashdata('filtre_'.$page_section, $filter_data);
			$this->session->set_flashdata('filtre_form_'.$page_section, $filter_form_data);
		}

		$this->delete_old_filters($page_section);

		return;
	}

	// delete the filters not used in this actual page = other sections
	private function delete_old_filters($page_section)
	{
		switch ($page_section) {
			case 'notes_envoyees':
				$this->session->set_flashdata('filtre_notes_recues', NULL);
				$this->session->set_flashdata('filtre_form_notes_recues', NULL);
				$this->session->set_flashdata('filtre_notes_terminees', NULL);
				$this->session->set_flashdata('filtre_form_notes_terminees', NULL);
				$this->session->set_flashdata('filtre_notes_refusees', NULL);
				$this->session->set_flashdata('filtre_form_notes_refusees', NULL);
				break;

			case 'notes_recues':
				$this->session->set_flashdata('filtre_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_form_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_notes_terminees', NULL);
				$this->session->set_flashdata('filtre_form_notes_terminees', NULL);
				$this->session->set_flashdata('filtre_notes_refusees', NULL);
				$this->session->set_flashdata('filtre_form_notes_refusees', NULL);
				break;

			case 'notes_terminees':
				$this->session->set_flashdata('filtre_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_form_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_notes_recues', NULL);
				$this->session->set_flashdata('filtre_form_notes_recues', NULL);
				$this->session->set_flashdata('filtre_notes_refusees', NULL);
				$this->session->set_flashdata('filtre_form_notes_refusees', NULL);
				break;

			case 'notes_refusees':
				$this->session->set_flashdata('filtre_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_form_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_notes_recues', NULL);
				$this->session->set_flashdata('filtre_form_notes_recues', NULL);
				$this->session->set_flashdata('filtre_notes_terminees', NULL);
				$this->session->set_flashdata('filtre_form_notes_terminees', NULL);
				break;
			
			default:
				$this->session->set_flashdata('filtre_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_form_notes_envoyees', NULL);
				$this->session->set_flashdata('filtre_notes_recues', NULL);
				$this->session->set_flashdata('filtre_form_notes_recues', NULL);
				$this->session->set_flashdata('filtre_notes_terminees', NULL);
				$this->session->set_flashdata('filtre_form_notes_terminees', NULL);
				$this->session->set_flashdata('filtre_notes_refusees', NULL);
				$this->session->set_flashdata('filtre_form_notes_refusees', NULL);
				break;
		}		
	}
}