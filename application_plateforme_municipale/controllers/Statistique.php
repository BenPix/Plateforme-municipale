<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Statistique extends MY_Controller {

	const LIMIT = 40;

	public function index()
	{
		$this->gestion_interservices();
	}
	/*
	*
	*
	*
	*
	*
	*
	*
	*
	*
	* STATISTIQUES
	*
	*
	*
	*
	*
	*
	*
	*
	*
	*/
	public function gestion_interservices($page = '')
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();

		
		$pole_attache = array('Tous' => 'Tous') + $this->pole_query_model->liste_tous_poles_for_drop_down();
		$pole_sollicite = array('Tous' => 'Tous') + $this->pole_query_model->liste_poles_sollicitables_for_dropdown();

		// if filter activated, setting data in flashdata, for the page navigation
		$data_sent = array();
		if ($this->check_post())
			$data_sent = $this->set_values();
		
		$this->session->keep_flashdata('values');

		$tab_entier = $this->demande_interservices_query_model->table_demandes_a_filtrer($data_sent);
		$page = ($this->uri->segment(3)) ? ($this->uri->segment(3) - 1) : 0;
		$total_rows = $tab_entier->num_rows();

		$pagination = $this->pagination_model->creer_ma_pagination($total_rows, self::LIMIT);
		$tab = $this->demande_interservices_query_model->table_demandes_a_filtrer($data_sent, $page * self::LIMIT, self::LIMIT);

		self::$_data += array(
			'total' => $total_rows,
			'pole_attache' => $pole_attache,
			'pole_sollicite' => $pole_sollicite,
			'titre' => 'Statistiques - Interservices',
			'tableau' => $tab,
			'pagination' => $pagination
		);

		$this->load->view('templates/header', self::$_data);
		$this->load->view('interservices/statistiques');
		$this->load->view('templates/footer');
	}

	/*
	*
	*
	* EXPORT
	*
	*
	*/

	public function export()
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();

		
		$this->session->keep_flashdata('values');

		// generate data from filter
		$data_sent = (NULL !== $this->session->flashdata('values')) ? $this->session->flashdata('values') : array();
		$data = $this->demande_interservices_query_model->table_demandes_a_filtrer_pour_export($data_sent)->result_array();

		// adding column title
		$titre_colonne = array(
			'num_dossier' => 'Dossier N°',
			'horodateur' => 'Horodateur',
			'direction_attachee' => 'Direction Attachée',
			'direction_sollicitee' => 'Direction Sollicitée',
			'demande' => 'Demande',
			'date_souhaitee' => 'Date souhaitée',
			'date_relance' => 'Date de Relance',
			'statut' => 'Statut'
		);
		array_unshift($data, $titre_colonne);

		// generate the sheet with the data
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->fromArray($data, NULL, 'A1');
		$this->load->model('sheet_model');
        $sheet = $this->sheet_model->designTheSheet($sheet); // styling the sheet, color, borders etc
        $writer = new Xlsx($spreadsheet);
        $filename = 'Statistiques Demandes inter-Services';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output'); // download file
    }
	/*
	*
	*
	* GRAPHIQUE
	*
	*
	*/
	public function graphique()
	{
		if ($this->session->userdata('rang') !== 'admin')
			show_404();

		
		$data_sent = (NULL !== $this->session->flashdata('values')) ? $this->session->flashdata('values') : array();
		$this->session->keep_flashdata('values');
		$select = $this->input->post('part');
		$group_by = $this->input->post('part');

		$graph = $this->demande_interservices_query_model->read_for_chart($select, $data_sent);

		self::$_data += array(
			'colonne' => $group_by,
			'graph' => $graph,
			'titre' => 'Graphique'
		);
		$this->load->view('interservices/graphique', self::$_data);
	}
    /*
	*
	*
	*
	*
	*
	*
	*
	*
	*
	* METHODES PRIVATE
	*
	*
	*
	*
	*
	*
	*
	*
	*
	*/
	private function set_values() {
		$en_attente = is_array($this->input->post('statut')) ? in_array('en attente', $this->input->post('statut')) : FALSE;
		$en_cours = is_array($this->input->post('statut')) ? in_array('en cours', $this->input->post('statut')) : FALSE;
		$termine = is_array($this->input->post('statut')) ? in_array('terminé', $this->input->post('statut')) : FALSE;

		$data_sent = array(
			'dossier_depart' => $this->input->post('dossier_depart'),
			'dossier_fin' => $this->input->post('dossier_fin'),
			'date_depart' => $this->input->post('date_depart'),
			'date_fin' => $this->input->post('date_fin'),
			'expediteur_id' => $this->input->post('expediteur_id'),
			'destinataire_id' => $this->input->post('destinataire_id'),
			'agent_id' => $this->input->post('agent_id'),
			'type_conges' => $this->input->post('type_conges'),
			'motif_conge' => $this->input->post('motif_conge'),
			'conges_debut' => $this->input->post('conges_debut'),
			'conges_fin' => $this->input->post('conges_fin'),
			'les_statuts' => $this->input->post('statut'),
			'en_attente' => $en_attente,
			'en_cours' => $en_cours,
			'termine' => $termine
		);
		$this->session->set_flashdata('values', $data_sent);

		return $data_sent;
	}
	/*
	* fonction permettant de savoir si le filtre a été activé
	*/
	private function check_post() {
		$data_sent = array(
			'dossier_depart' => $this->input->post('dossier_depart'),
			'dossier_fin' => $this->input->post('dossier_fin'),
			'date_depart' => $this->input->post('date_depart'),
			'date_fin' => $this->input->post('date_fin'),
			'expediteur_id' => $this->input->post('expediteur_id'),
			'destinataire_id' => $this->input->post('destinataire_id'),
			'les_statuts' => $this->input->post('statut'),
			'agent_id' => $this->input->post('agent_id'),
			'type_conges' => $this->input->post('type_conges'),
			'motif_conge' => $this->input->post('motif_conge'),
			'conges_debut' => $this->input->post('conges_debut'),
			'conges_fin' => $this->input->post('conges_fin')
		);

		return $this->interservices_model->filter_is_activated($data_sent);
	}

}
