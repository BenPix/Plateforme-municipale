<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Notification_model extends MY_Model
{

	/**
	 * Create the notification object and do the inserts in the database
	 *
	 * @param	int	$source_id
	 * @param	string	$source_type_name
	 * @return	Notification or FALSE if it fails
	 */
	public function add_notification(int $source_id, string $source_type_name, string $source_categorie_name)
	{
		$notif = new Notification();

		$source = $this->define_source($source_id, $source_type_name, $source_categorie_name);

		if ($source === FALSE) return FALSE;

		$notif->set_source($source);

		$users_id = $this->define_user_from_source($source);

		foreach ($users_id as $key => $value) 
			$notif->add_recipients(new Notification_recipient($value));

		// delete all old similar notification to avoid duplicates (to update could be a solution too, but to delete is easier)
		$old_notifs = $this->search_similar_notification($source_id, $source_type_name, $source_categorie_name);		

		foreach ($old_notifs as $old_notif) {
			if ($old_notif !== FALSE) $this->notification_model->delete_notification($old_notif->get_id());
		}

		$notif = $this->create_notification($notif);
		
		return $notif;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the Notification object(s) depending on the source
	 *
	 * @param	int	$source_id
	 * @param	string $source_type_name
	 * @param	string $source_categorie_name
	 * @return	Notification or array(Notification) if several occurences (should not happen) or FALSE if it fails
	 */
	public function search_notification(int $source_id, string $source_type_name, string $source_categorie_name)
	{
		$source = $this->define_source($source_id, $source_type_name, $source_categorie_name);

		return $this->find_notification($source);
	}

	// --------------------------------------------------------------------

	/**
	 * Search and find a notification depending on the source, and then delete it from the database
	 *
	 * @param	int	$source_id
	 * @param	string $source_type_name
	 * @param	string $source_categorie_name
	 * @return	bool
	 */
	public function search_and_delete_notification(int $source_id, string $source_type_name, string $source_categorie_name)
	{
		$notif = $this->search_notification($source_id, $source_type_name, $source_categorie_name);

		if ($notif !== FALSE) $this->notification_model->delete_notification($notif->get_id());

		return $notif !== FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update the notif_recipient table and the Notification object(s) for only one user reading the notification
	 *
	 * If the $source_type_names var is set, all the notifications from that type will be marked as read
	 * It's usefull for pages containing list, but no detail pages
	 *
	 * @param	array	$user_notif_list
	 * @param	int	$source_id
	 * @param	string	$source_categorie_name
	 * @param	array	$source_type_names
	 */
	public function mark_notification_as_read(array &$user_notif_list, int $source_id, string $source_categorie_name, array $source_type_names = array())
	{
		$notification_has_succed = FALSE;

		foreach ($user_notif_list as $notif) {

			if ( ($notif->get_source()->get_id() === $source_id && $notif->get_source()->get_categorie_name() === $source_categorie_name) ||  in_array($notif->get_source()->get_type_name(), $source_type_names) ) {

				foreach ($notif->get_recipients() as $recipient) {

					$recipient->set_marked_as_read(TRUE);
					$recipient->set_date(new DateTime());

					$succed = $this->update_notification_as_read($notif, $recipient);

					if ($notification_has_succed === FALSE && $succed) $notification_has_succed = TRUE;
				}
			}
		}

		return $notification_has_succed;
	}

	// --------------------------------------------------------------------

	/**
	 * Update the notif_recipient table and the Notification objects for only one user, for all his/her notifications
	 *
	 * @param	int	$user_id
	 * @return	array
	 */
	public function mark_all_notifications_as_read(int $user_id)
	{
		$all_user_notifications = $this->find_user_notifications($user_id);

		foreach ($all_user_notifications as $notif) {

			foreach ($notif->get_recipients() as $recipient) {

				$recipient->set_marked_as_read(TRUE);
				$recipient->set_date(new DateTime());

				$this->update_notification_as_read($notif, $recipient);
			}
		}

		return $all_user_notifications;
	}

	// --------------------------------------------------------------------

	/**
	 * Find all the unread user notification
	 *
	 * @param	int	$user_id
	 * @return	array(Notification)
	 */
	public function find_user_notifications(int $user_id)
	{
		$notification_list = array();

		$notifs = $this->find_user_notifications_data($user_id);

		foreach ($notifs as $row) {
			$notif = new Notification();

			$notif->set_id($row->id);

			$source = new Source((int)$row->source_id, (int)$row->categorie_id, $row->categorie_name, (int)$row->source_type_id, $row->source_type_name);

			$this->create_notification_menu_content($source);

			$notif->set_source($source);

			$notification_recipient = new Notification_recipient((int)$user_id);

			$notification_recipient->set_date(new DateTime($row->reading_date));

			$notif->add_recipients($notification_recipient);

			array_push($notification_list, $notif);
		}

		return $notification_list;
	}

	// --------------------------------------------------------------------

	/**
	 * Find all the unread user notification for some kind of source
	 *
	 * @param	int	$user_id
	 * @param	array(string)	$sources_type_name
	 * @param	string	$source_categorie_name
	 * @return	array(int) the notifications source IDs
	 */
	public function find_user_notifications_for_source(int $user_id, array $sources_type_names, string $source_categorie_name)
	{
		$notifs_id = array();
		$unread_notification_list = $this->find_user_notifications($user_id);

		foreach ($unread_notification_list as $notif) {
			if (in_array($notif->get_source()->get_type_name(), $sources_type_names) && $notif->get_source()->get_categorie_name() === $source_categorie_name)
				array_push($notifs_id, $notif->get_source()->get_id());
		}

		return $notifs_id;
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////// METHODES PRIVATE ////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	/**
	 * Find the users ids of all users needing to be notified
	 *
	 * @param	Source	$source
	 * @return	array
	 */
	private function define_user_from_source(Source $source)
	{
		$users_id = array();
		$unwanted_users = array($this->session->userdata('id')); // the active user is always removed from the recipients

		// finding the source details depending on its category, and then the recipient users
		switch ($source->get_categorie_name()) {

			case 'demande_interservices':
				$this->load->model('interservices/interservices_model');
				$source_detail = $this->demande_interservices_query_model->find_demande_complete($source->get_id());

				// the 2 managers + (sous-)pole users + asking user + users with special acces to the asking pole => are notified
				array_push($users_id, $source_detail->demandeur_id, $source_detail->responsable_sollicite_id, $source_detail->responsable_attache_id);
				$sous_responsables = $this->pole_sous_responsable_query_model->liste_sous_responsables($source_detail->direction_attachee_id);
				$users_id = array_merge($users_id, array_keys($sous_responsables));

				if (empty($this->sous_pole_query_model->liste_sous_poles($source_detail->direction_sollicitee_id)))
					$this->add_recipients_from_pole($source_detail->direction_sollicitee_id, $users_id);
				elseif ( ! empty($source_detail->sous_pole_id) )
					$this->add_recipients_from_sous_pole($source_detail->sous_pole_id, $users_id);

				// others exceptions
				if (in_array($source->get_type_name(), array('affecte_sous_pole'))) {
					array_push($unwanted_users, $source_detail->demandeur_id, $source_detail->responsable_attache_id);
					$unwanted_users = array_merge($unwanted_users, array_keys($sous_responsables));
				}

				break;

			case 'demande_citoyen_app_cfdb7':
				$source_detail = $this->formulaire_contact_query_model->demande_detail_app_cfdb7($source->get_id());

				// the 2 managers + (sous-)pole users are notified
				array_push($users_id, $source_detail->responsable_sollicite_id, $source_detail->responsable_demandes_citoyen_id);

				if (empty($this->sous_pole_query_model->liste_sous_poles($source_detail->transfert_id)))
					$this->add_recipients_from_pole($source_detail->transfert_id, $users_id);
				elseif ( ! empty($source_detail->transfert_sous_pole_id) )
					$this->add_recipients_from_sous_pole($source_detail->transfert_sous_pole_id, $users_id);

				break;

			case 'demande_citoyen_site_cfdb7':
				$source_detail = $this->formulaire_contact_query_model->demande_detail_site_cfdb7($source->get_id());

				// the 2 managers + (sous-)pole users are notified
				array_push($users_id, $source_detail->responsable_sollicite_id, $source_detail->responsable_demandes_citoyen_id);

				if (empty($this->sous_pole_query_model->liste_sous_poles($source_detail->transfert_id)))
					$this->add_recipients_from_pole($source_detail->transfert_id, $users_id);
				elseif ( ! empty($source_detail->transfert_sous_pole_id) )
					$this->add_recipients_from_sous_pole($source_detail->transfert_sous_pole_id, $users_id);

				break;

			case 'demande_citoyen_site':
				$source_detail = $this->formulaire_contact_query_model->demande_citoyen_site_detail($source->get_id());

				// the 2 managers + (sous-)pole users are notified
				array_push($users_id, $source_detail['responsable_sollicite_id'], $source_detail['responsable_demandes_citoyen_id']);

				if (empty($this->sous_pole_query_model->liste_sous_poles($source_detail['transfert_id'])))
					$this->add_recipients_from_pole($source_detail['transfert_id'], $users_id);
				elseif ( ! empty($source_detail['transfert_sous_pole_id']) )
					$this->add_recipients_from_sous_pole($source_detail['transfert_sous_pole_id'], $users_id);

				break;

			case 'conges':
				
				switch ($source->get_type_name()) {

					case 'valide':
						$source_detail = $this->conges_query_model->findDemandeCongesComplete($source->get_id());
						$demandeur_id = $source_detail->utilisateur_id;
						$responsable_id = $this->utilisateur_query_model->infosUtilisateur($source_detail->utilisateur_id)->responsable_id;

						break;

					case 'heure_supp_validee':
						$source_detail = $this->recup_conges_query_model->detail($source->get_id());
						$demandeur_id = $source_detail->demandeur_id;
						$responsable_id = $this->utilisateur_query_model->infosUtilisateur($source_detail->demandeur_id)->responsable_id; 

						break;
					
					default:
						$demandeur_id = 0;
						break;
				}

				// the manager + the asking user are notified
				array_push($users_id, $responsable_id, $demandeur_id);

				break;

			case 'bon_de_commande': // case when the bon_de_commande is accepted OR refused
				$source_detail = $this->bon_de_commande_query_model->findDetail($source->get_id());

				// everyone is notified
				empty($source_detail->demandeur_id) OR array_push($users_id, $source_detail->demandeur_id);
				empty($source_detail->responsable_qui_valide_id) OR array_push($users_id, $source_detail->responsable_qui_valide_id);
				empty($source_detail->elu_qui_valide_id) OR array_push($users_id, $source_detail->elu_qui_valide_id);

				if ($source_detail->niveau === '4') {
					$commission = $this->bdc_valide_par_commission_query_model->detailsValidations($source_detail->id);

					foreach ($commission as $membre)
						array_push($users_id, $membre->id);
				}

				break;

			case 'news': // case when the news is accepted OR refused
				$this->load->model('news/news_model');
				$source_detail = $this->article_query_model->find_detail($source->get_id());

				// the news creater is notified
				array_push($users_id, $source_detail->redacteur_id);

				break;
			
			default:
				break;
		}

		$users_id = $this->purge_users_list($users_id, $unwanted_users); // removes duplicates and unwanted

		return $users_id;
	}

	// --------------------------------------------------------------------

	/**
	 * Push users id in the list, where users are from the defined pole, and cares for no duplicates created
	 *
	 * @param	int	$pole_id
	 * @param	array $list to be pushed
	 */
	private function add_recipients_from_pole(int $pole_id, array &$list)
	{
		$user_list = $this->utilisateur_query_model->liste_pole_users($pole_id, TRUE);

			foreach ($user_list as $id => $nom) {
				if ( ! in_array($id, $list) )
					array_push($list, $id);
			}
	}

	// --------------------------------------------------------------------

	/**
	 * Push users id in the list, where users are from the defined sous-pole, and cares for no duplicates created
	 *
	 * @param	int	$sous_pole_id
	 * @param	array $list to be pushed
	 */
	private function add_recipients_from_sous_pole(int $sous_pole_id, array &$list)
	{
		$user_list = $this->utilisateur_query_model->liste_appartenance_sous_pole($sous_pole_id);

			foreach ($user_list as $id => $nom) {
				if ( ! in_array($id, $list) )
					array_push($list, $id);
			}
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the Source object
	 *
	 * @param	int	$source_id
	 * @param	string $source_type_name
	 * @param	string $source_categorie_name
	 * @return	Source or FALSE if it fails
	 */
	private function define_source(int $source_id, string $source_type_name, string $source_categorie_name)
	{
		// selon le nom, trouver le type, puis créer l'objet
		$this->db->select(array('nt.id', 'nt.categorie_id'));
		$this->db->from('notif_source_type AS nt');
		$this->db->join('notif_source_categorie AS nc', 'nt.categorie_id = nc.id');
		$this->db->where(array('nt.type' => $source_type_name, 'nc.categorie' => $source_categorie_name));
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() === 0) return FALSE;

		$source = new Source($source_id, (int)$query->row()->categorie_id, $source_categorie_name, (int)$query->row()->id, $source_type_name);

		return $source;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the Notification object(s) depending on the source
	 *
	 * @param	Source	$source
	 * @return	Notification OR array(Notification) OR FALSE if it fails
	 */
	private function find_notification(Source $source)
	{
		$this->db->select(array('n.id', 'n.source_id', 'n.source_type_id', 'nt.type', 'nc.categorie'));
		$this->db->from('notif AS n');
		$this->db->join('notif_source_type AS nt', 'n.source_type_id = nt.id');
		$this->db->join('notif_source_categorie AS nc', 'nt.categorie_id = nc.id');
		$this->db->where(array('source_id' => $source->get_id(), 'source_type_id' => $source->get_type_id()));
		$query = $this->db->get();

		if ($query->num_rows() === 0) {
			return FALSE;

		} elseif ($query->num_rows() === 1) {
			$result = $query->row();
			$notif = new Notification();

			$notif->set_id($result->id);
			$notif->set_source($source);

			$this->find_and_add_notification_recipients($notif);

			return $notif;

		} else {
			$result = $query->result();
			$notification_list = array();

			foreach ($result as $row) {
				$notif = new Notification();

				$notif->set_id($row->id);
				$notif->set_source($source);

				$this->find_and_add_notification_recipients($notif);

				array_push($notification_list, $notif);
			}

			return $notification_list;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Find the recipients linked to the notification, and add them to Notification object
	 *
	 * @param	Notification	$notif
	 */
	private function find_and_add_notification_recipients(Notification &$notif)
	{
		$this->db->select(array('recipient_id', 'read', 'reading_date'));
		$this->db->from('notif_recipient');
		$this->db->where(array('notif_id' => $notif->get_id()));
		$query = $this->db->get();

		foreach ($query->result() as $row) {
			$notification_recipient = new Notification_recipient($row->recipient_id);

			$notification_recipient->set_marked_as_read($row->read === '1');
			$notification_recipient->set_date(new DateTime($row->reading_date));

			$notif->add_recipients($notification_recipient);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a notification from the database, and the bounded notification_recipients
	 *
	 * @param	int	$notif_id
	 * @return	bool
	 */
	private function delete_notification(int $notif_id)
	{
		return $this->delete_notification_data($notif_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Creates an array containing Notification object(s) being similar to some kind of Notification
	 *
	 * @param	int	$source_id
	 * @param	string $source_type_name
	 * @param	string $source_categorie_name
	 * @return	array(Notification)
	 */
	private function search_similar_notification(int $source_id, string $source_type_name, string $source_categorie_name)
	{
		// first, some sources cannot have similar notifications
		if (in_array($source_categorie_name, array('conges')))
			return array();

		// then, older notif with exact same source
		$similar_notif_list = array();
		$old_notif = $this->search_notification($source_id, $source_type_name, $source_categorie_name); // same notif (same source) but older

		if(is_array($old_notif))
			$similar_notif_list += $old_notif;
		else
			array_push($similar_notif_list, $old_notif);

		// and finally, similar notif, having a same source_id and categorie, but a different type
		switch ($source_categorie_name) {
			case 'demande_interservices':
				switch ($source_type_name) {
					case 'changement_de_statut':
						array_push($similar_notif_list, $this->search_notification($source_id, 'valide', $source_categorie_name));
						array_push($similar_notif_list, $this->search_notification($source_id, 'relance', $source_categorie_name));
						array_push($similar_notif_list, $this->search_notification($source_id, 'affecte_sous_pole', $source_categorie_name));
						array_push($similar_notif_list, $this->search_notification($source_id, 'delai', $source_categorie_name));
						array_push($similar_notif_list, $this->search_notification($source_id, 'commentaire', $source_categorie_name));
						break;

					default:
						break;
				}
				break;
			case 'demande_citoyen_app_cfdb7':
				switch ($source_type_name) {
					case 'transfere_pole':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_sous_pole', $source_categorie_name));
						break;

					case 'transfere_sous_pole':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_pole', $source_categorie_name));
						break;

					case 'reponse':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_pole', $source_categorie_name));
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_sous_pole', $source_categorie_name));
						break;
					
					default:
						break;
				}
				break;
			case 'demande_citoyen_site_cfdb7':
				switch ($source_type_name) {
					case 'transfere_pole':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_sous_pole', $source_categorie_name));
						break;

					case 'transfere_sous_pole':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_pole', $source_categorie_name));
						break;

					case 'reponse':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_pole', $source_categorie_name));
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_sous_pole', $source_categorie_name));
						break;
					
					default:
						break;
				}
				break;
			case 'demande_citoyen_site':
				switch ($source_type_name) {
					case 'transfere_pole':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_sous_pole', $source_categorie_name));
						break;

					case 'transfere_sous_pole':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_pole', $source_categorie_name));
						break;

					case 'reponse':
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_pole', $source_categorie_name));
						array_push($similar_notif_list, $this->search_notification($source_id, 'transfere_sous_pole', $source_categorie_name));
						break;
					
					default:
						break;
				}
				break;
		}

		return $similar_notif_list;
	}

	// --------------------------------------------------------------------

	/**
	 * Purge the users list from duplicates, empty and FALSE values and unwanted values
	 *
	 * @param	array	$users_list
	 * @param	array	$unwanted_values
	 * @return	array
	 */
	private function purge_users_list(array $users_list, array $unwanted_values = array())
	{
		$final_users_list = array();

		foreach (array_count_values(array_filter($users_list)) as $value => $count) {
			if ( ! in_array($value, $unwanted_values) )
				array_push($final_users_list, $value);
		}

		return $final_users_list;
	}

	// --------------------------------------------------------------------

	/**
	 * Purge the users list from duplicates, empty and FALSE values and unwanted values
	 *
	 * @param	Source	$source
	 */
	private function create_notification_menu_content(Source &$source)
	{
		// defines the title and the link of the notification menu, depending on its category and type
		switch ($source->get_categorie_name()) {

			case 'demande_interservices':
				$this->load->model('interservices/interservices_model');
				$demande = $this->demande_interservices_query_model->find_demande_complete($source->get_id());

				$num_dossier = $demande->num_dossier;
				$contenu[] = '<u>Demande de '.htmlspecialchars($demande->demandeur).'</u>';
				$fin_du_contenu = strlen($demande->demande) > 80 ? '...' : '';
				$contenu[] = htmlspecialchars(substr($demande->demande, 0, 80)).$fin_du_contenu;

				$source->set_link(site_url('demande/detail/'.$source->get_id()));
				$source->set_content($contenu);

				switch ($source->get_type_name()) {
					case 'valide':
						if ($demande->demandeur_id === $this->session->userdata('id'))
							$source->set_title('Validation de votre demande interservices N°'.$num_dossier);
						else
							$source->set_title('Nouvelle demande interservices N°'.$num_dossier);
						break;

					case 'relance':
						$source->set_title('Relance de la demande interservices N°'.$num_dossier);
						break;

					case 'affecte_sous_pole':
						$source->set_title('Demande interservices N°'.$num_dossier.' affectée à votre Service');
						break;

					case 'changement_de_statut':
						$source->set_title('Demande interservices N°'.$num_dossier.' terminée');
						$source->set_link(site_url('demande/detail_final/'.$source->get_id()));
						break;

					case 'commentaire':
						$last_comment = $this->commentaire_demande_interservices_query_model->find_last_comment($source->get_id());

						$end_of_comment = strlen($last_comment->commentaire) > 80 ? '...' : '';
						$comment = htmlspecialchars(substr($last_comment->commentaire, 0, 80)).$end_of_comment;

						$source->set_title('Nouveau commentaire dans la demande interservices  N°'.$num_dossier);
						$source->add_content('<u>Nouveau commentaire de '.$last_comment->commentateur.'</u>');
						$source->add_content($comment);
						break;

					case 'delai':
						$source->set_title('Délai modifié dans la demande interservices N°'.$num_dossier);
						$source->add_content('<u>Modification du délai</u> : '.$demande->delai.' '.$demande->date_souhaitee);
						break;
					
					default:
						break;
				}

				break;

			case 'demande_citoyen_app_cfdb7':
				$demande = $this->formulaire_contact_query_model->demande_detail_app_cfdb7($source->get_id());

				$contenu[] = 'Demande du citoyen<u> '.htmlspecialchars($demande->citoyen).'</u> :';
				$contenu[] = htmlspecialchars($demande->formulaire);

				$source->set_link(site_url('citoyen/demande_citoyen_detail/app_cfdb7/'.$source->get_id()));
				$source->set_content($contenu);

				switch ($source->get_type_name()) {

					case 'transfere_pole':
						$source->set_title('Demande citoyen N°'.$source->get_id().' transférée à votre Service');
						break;

					case 'transfere_sous_pole':
						$source->set_title('Demande citoyen N°'.$source->get_id().' transférée à votre Service');
						break;

					case 'reponse':
						$source->set_title('Réponse apportée à la demande citoyen N°'.$source->get_id());
						break;
					
					default:
						break;
				}

				break;

			case 'demande_citoyen_site_cfdb7':
				$demande = $this->formulaire_contact_query_model->demande_detail_site_cfdb7($source->get_id());

				$contenu[] = 'Demande du citoyen<u> '.htmlspecialchars($demande->citoyen).'</u> :';
				$contenu[] = htmlspecialchars($demande->formulaire);

				$source->set_link(site_url('citoyen/demande_citoyen_detail/site_cfdb7/'.$source->get_id()));
				$source->set_content($contenu);

				switch ($source->get_type_name()) {

					case 'transfere_pole':
						$source->set_title('Demande citoyen N°'.$source->get_id().' transférée à votre Service');
						break;

					case 'transfere_sous_pole':
						$source->set_title('Demande citoyen N°'.$source->get_id().' transférée à votre Service');
						break;

					case 'reponse':
						$source->set_title('Réponse apportée à la demande citoyen N°'.$source->get_id());
						break;
					
					default:
						break;
				}

				break;

			case 'demande_citoyen_site':
				$demande = $this->formulaire_contact_query_model->demande_citoyen_site_detail($source->get_id());

				$contenu[] = 'Demande du citoyen<u> '.htmlspecialchars($demande['Nom*'].' '.$demande['Prénom*']).'</u> :';
				$contenu[] = htmlspecialchars($demande['submitted_on']);

				$source->set_link(site_url('citoyen/demande_citoyen_site_detail/'.$source->get_id()));
				$source->set_content($contenu);

				switch ($source->get_type_name()) {
					case 'transfere_pole':
						$source->set_title('Demande citoyen N°'.$source->get_id().' transférée à votre Service');
						$source->set_link(site_url('citoyen/demande_citoyen_site_detail/'.$source->get_id()));
						break;

					case 'transfere_sous_pole':
						$source->set_title('Demande citoyen N°'.$source->get_id().' transférée à votre Service');
						$source->set_link(site_url('citoyen/demande_citoyen_site_detail/'.$source->get_id()));
						break;

					case 'reponse':
						$source->set_title('Réponse apportée à la demande citoyen N°'.$source->get_id());
						$source->set_link(site_url('citoyen/demande_citoyen_site_detail/'.$source->get_id()));	
						break;
					
					default:
						break;			

				}
				break;

			case 'conges':
				switch ($source->get_type_name()) {
					case 'valide':
						$demande = $this->conges_query_model->findDemandeCongesComplete($source->get_id());

						if ($demande->utilisateur_id != $this->session->userdata('id')) {
							$source->set_title('Demande de congés N°'.$source->get_id().' validée');
							$source->set_link(site_url('conges/detail_conges_personnel/'.$demande->utilisateur_id));
							$source->add_content('Demande concernant l\'agent '.$demande->demandeur.':');	
						} else {
							$source->set_title('Demande de congés N°'.$source->get_id().' validée');
							$source->set_link(site_url('conges/historique_conges'));
						}
						
						$source->add_content($demande->type_conges.' du '.$demande->debut_jour.' au '.$demande->fin_jour);	

						break;

					case 'heure_supp_validee':
						$demande = $this->recup_conges_query_model->detail($source->get_id());

						$contenu = htmlspecialchars(substr($demande->motif, 0, 80));
						$fin_du_contenu = strlen($demande->motif) > 80 ? '...' : '';

						$source->set_title('Demande d\'heures supplémentaires N°'.$source->get_id().' validée');
						$source->set_link(site_url('conges/heures_supp_recap'));
						$source->add_content($demande->heures_supp.' ajoutée pour le motif suivant :');
						$source->add_content($contenu.$fin_du_contenu);

						break;
					
					default:
						break;
				}

				break;

			case 'bon_de_commande':
				$demande = $this->bon_de_commande_query_model->findDetail($source->get_id());

				$contenu[] = 'Demande de <u>'.htmlspecialchars($demande->demandeur).'</u> :';
				$fin_du_contenu = strlen($demande->expression) > 80 ? '...' : '';
				$contenu[] = htmlspecialchars(substr($demande->expression, 0, 80)).$fin_du_contenu;

				$source->set_content($contenu);

				switch ($source->get_type_name()) {
					case 'valide':
						$source->set_title('Bon de commande N°'.$source->get_id().' validé et finalisé');
						$source->set_link(site_url('documents/recapitulatif_bon_de_commande/'.$source->get_id()));	

						break;

					case 'refuse':
						$source->set_title('Bon de commande N°'.$source->get_id().' refusé');
						$source->set_link(site_url('validation/detail_bon_de_commande/'.$source->get_id()));	

						break;
					
					default:
						break;
				}

				break;

			case 'news':
				$this->load->model('news/news_model');
				$article = $this->article_query_model->find_detail($source->get_id());

				if ($article === FALSE) { // article supprimé (date de suppression passée ou validation refusée)
					$contenu[] = 'La publication de votre article a été refusée par votre responsable.';

					$source->set_title('Article N°'.$source->get_id().' refusé et supprimé');
					$source->set_content($contenu);
					$source->set_link(site_url('accueil/notif_marked_as_read_for_news/'.$source->get_id()));
				} else {
					$contenu[] = '<u>'.htmlspecialchars($article->titre).'</u> :';
					$fin_du_contenu = strlen($article->description) > 80 ? '...' : '';
					$contenu[] = htmlspecialchars(substr($article->description, 0, 80), ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8').$fin_du_contenu;
					$contenu[] = 'Il sera supprimé automatiquement le '.$article->date_suppression.', comme vous l\'avez spécifié.';

					$source->set_title('Article N°'.$source->get_id().' validé et publié');
					$source->set_content($contenu);
					$source->set_link(site_url('accueil/article/'.$source->get_id()));
				}

				break;
			
			default:
				break;
		}
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////// METHODES DE BDD /////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	/**
	 * Inserts all the data for one notification
	 *
	 * @param	Notification $notif
	 * @return	Notification or FALSE if it fails
	 */
	private function create_notification(Notification $notif)
	{
		$this->db->trans_start();

		// table notif
		$notif_id = (bool) $this->db
		->set(array('source_id' => $notif->get_source()->get_id(), 'source_type_id' => $notif->get_source()->get_type_id()))
		->insert('notif') ? $this->db->insert_id() : FALSE;

		if ($notif_id !== FALSE)
			$notif->set_id($notif_id);

		// table notif_recipient
		foreach ($notif->get_recipients() as $recipient) {
			$this->db
			->set(array('notif_id' => $notif_id, 'recipient_id' => $recipient->get_user_id(), 'read' => 0))
			->set(array('reading_date' => 'NOW()'), null, false)
			->insert('notif_recipient');
		}

		$this->db->trans_complete();

		return $this->db->trans_status() ? $notif : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the Source object
	 *
	 * @param	Notification	$notif
	 * @param	Notification_recipient $recipient
	 * @return	bool
	 */
	private function update_notification_as_read(Notification $notif, Notification_recipient $recipient)
	{
		$this->db->set(array('read' => 1, 'reading_date' => $recipient->get_date()->format('Y-m-d H:i:s')));
		$this->db->where(array('notif_id' => $notif->get_id(), 'recipient_id' => $recipient->get_user_id()));
		return (bool) $this->db->update('notif_recipient');
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a notification from the database, and the notification_recipients bounded
	 *
	 * @param	int	$notif_id
	 * @return	bool
	 */
	private function delete_notification_data(int $notif_id)
	{
		$this->db->where(array('notif_id' => $notif_id));
		$this->db->delete('notif_recipient');

		$this->db->where(array('id' => $notif_id));
		return (bool) $this->db->delete('notif');
	}

	// --------------------------------------------------------------------

	/**
	 * Find all the unread user notification
	 *
	 * @param	int	$user_id
	 * @return	array
	 */
	private function find_user_notifications_data(int $user_id)
	{
		$this->db->select(array('n.id', 'n.source_id', 'n.source_type_id', 'nt.type AS source_type_name', 'nt.categorie_id', 'nc.categorie AS categorie_name', 'nr.recipient_id', 'nr.read', 'nr.reading_date'));
		$this->db->from('notif_recipient AS nr');
		$this->db->join('notif AS n', 'nr.notif_id = n.id');
		$this->db->join('notif_source_type AS nt', 'n.source_type_id = nt.id');
		$this->db->join('notif_source_categorie AS nc', 'nt.categorie_id = nc.id');
		$this->db->where(array('nr.recipient_id' => $user_id, 'nr.read' => 0));
		$query = $this->db->get();

		if ($query->num_rows() === 0) return array();

		return $query->result();
	}
	

}