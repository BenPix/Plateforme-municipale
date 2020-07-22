<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Menu_model extends CI_Model
{
	static $_menu_elements = array();
	protected $table = 'menu';

	/*
	* builds the menu, according to the modules choices and the user's rank
	*/
	public function menu_custom($modules, $rang) {
		// first we prepare the structure
		$this->set_elements($modules, $rang);

		// then we put the content in the menu elements
		foreach ($modules as $value) {
			$function_to_call = 'menu_custom_options_' . $value;

			$this->$function_to_call($rang);
		}

		// last, we put the notif element content
		$this->set_options_notif();

		// building the final menu
		$menu = $this->menu_building();

		// creating the css code to include for blinking some menu elements
		$css_code_in_style_element = $this->creating_blinking_style_css($modules, $rang);

		return $menu.$css_code_in_style_element;
	}





	///////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////// METHODES PRIVATE /////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////





	// builds the first elements of the menu, and put it in the good order
	private function set_elements($modules, $rang)
	{
		$unordered_list = array();
		$ordered_list = array('accueil', 'demandes', 'notes', 'citoyen', 'statistiques', 'validation', 'gestion', 'documents', 'profil', 'deco', 'notif');
		/* if we want the same menu element for the citoyen module :
		$ordered_list = array('accueil', 'demandes', 'statistiques', 'validation', 'gestion', 'documents', 'profil', 'deco', 'notif');
		*/

		// default elements
		$this->set_default_elements($unordered_list, $rang);

		// module elements
		foreach ($modules as $key => $value) {
			$function_to_call = 'menu_custom_' . $value;

			$unordered_list += $this->$function_to_call();
		}

		// ordering and then injecting in the class variable $_menu_elements
		$this->ordering_elements($ordered_list, $unordered_list);
	}

	// set the default elements
	private function set_default_elements(&$unordered_list, $rang)
	{
		// default elements, displayed for everyone
		$unordered_list['accueil'] = new Menu_element('accueil', 'menu_accueil', site_url('accueil'));
		$unordered_list['profil'] = new Menu_element('<i class="material-icons" style="font-size:25px;color:#03a6c9;margin-top:2px;margin-top:0px">account_circle</i>', 'menu_profil', site_url('accueil/profil'));
		$unordered_list['deco'] = new Menu_element('<i class="material-icons" style="font-size:25px;color:red;">power_settings_new</i>', 'menu_deco', site_url('login/connexion'));
		$unordered_list['notif'] =  new Menu_element('<i class="material-icons" style="font-size:25px;color:orange;margin-top:2px;margin-top:0px">announcement</i>', 'menu_notif', '');

		// default elements, displayed according to the rank
		$this->set_default_validation($unordered_list, $rang);
		$this->set_default_gestion($unordered_list, $rang);
		
	}

	private function set_default_validation(&$unordered_list, $rang) {
		if (in_array($rang, array('admin', 'responsable'))) {
			$validation = new Menu_element('validation', 'menu_validation', site_url('validation'));

			if (in_array($rang, array('admin'))) {
				$validation_inscription = new Menu_element('inscriptions', 'menu_validation_inscriptions', site_url('validation/inscriptions'));

				$validation->insert_submenu($validation_inscription);
			} elseif (in_array($rang, array('responsable'))) {
				$validation_interservices = new Menu_element('interservices', 'menu_validation_interservices', site_url('validation/interservices'));
				$validation_news = new Menu_element('news', 'menu_validation_news', site_url('validation/news'));

				$validation->insert_submenu($validation_interservices);
				$validation->insert_submenu($validation_news);
			}

			$unordered_list['validation'] = $validation;
		}
	}

	private function set_default_gestion(&$unordered_list, $rang) {
		if (in_array($rang, array('admin', 'rh'))) {
			$gestion = new Menu_element('gestion', 'menu_gestion', site_url('gestion'));
			$gestion_utilisateur = new Menu_element('personnel', 'menu_gestion_personnel', site_url('gestion/utilisateurs'));

			$gestion->insert_submenu($gestion_utilisateur);

			if (in_array($rang, array('admin'))) {
				$gestion_poles = new Menu_element('pôles', 'menu_gestion_poles', site_url('gestion/poles'));
				$gestion_modules = new Menu_element('modules', 'menu_gestion_modules', site_url('gestion/modules'));
				$gestion_captcha = new Menu_element('captcha', 'menu_gestion_captcha', site_url('gestion/captcha'));

				$gestion->insert_submenu($gestion_poles);
				$gestion->insert_submenu($gestion_modules);
				$gestion->insert_submenu($gestion_captcha);
			}

			$unordered_list['gestion'] = $gestion;
		}
	}

	private function menu_custom_news() {
		$data['accueil'] = new Menu_element('accueil', 'menu_accueil', '');

		return $data;
	}

	private function menu_custom_interservices() {
		$data['demandes'] = new Menu_element('demandes', 'menu_demandes', '');
		$data['statistiques'] = new Menu_element('statistiques', 'menu_stats', site_url('statistique'));
		
		return $data;
	}

	private function menu_custom_note() {
		$data['notes'] = new Menu_element('notes', 'menu_notes', '');
		
		return $data;
	}

	private function menu_custom_citoyen() {
		$data['citoyen'] = new Menu_element('citoyenneté', 'menu_citoyen', '');
		/* if we want the same menu element for this module :
		$data['demandes'] = new Menu_element('demandes', 'menu_demandes', site_url('demande'));
		*/
		
		return $data;
	}

	// orders the menu elements according to an ordered list, and then injects them in the class variable $_menu_elements
	private function ordering_elements($ordered_list, $unordered_list)
	{
		foreach ($ordered_list as $value) {
			if (isset($unordered_list[$value]))
				self::$_menu_elements[$value] = $unordered_list[$value];
		}
	}

	// builds the menu options for the news module
	private function menu_custom_options_news($rang)
	{
		if (in_array($rang, array('admin', 'responsable', 'utilisateur_superieur', 'utilisateur_particulier', 'redacteur'))) {
			$page_accueil = new Menu_element('retour à la page d\'accueil', 'menu_accueil', site_url('accueil'));
			$news_create = new Menu_element('créer une news', 'menu_news_creer', site_url('accueil/create_news'));

			self::$_menu_elements['accueil']->insert_submenu($page_accueil);
			self::$_menu_elements['accueil']->insert_submenu($news_create);

			if (in_array($rang, array('admin', 'redacteur'))) {
				$create_categorie = new Menu_element('gérer les catégories', 'menu_news_categories', site_url('accueil/create_categorie'));
				$acces_special = new Menu_element('gérer les accès spéciaux', 'menu_news_acces_speciaux', site_url('accueil/acces_special'));

				self::$_menu_elements['accueil']->insert_submenu($create_categorie);
				self::$_menu_elements['accueil']->insert_submenu($acces_special);
			}
		}
	}

	// builds the menu options for the interservices module
	private function menu_custom_options_interservices($rang)
	{
		// loading models
		$this->load->model('interservices/interservices_model');

		$is_pole_sollicitable = $this->pole_query_model->is_sollicitable($this->session->userdata('pole'));
		
		if (in_array($rang, array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'))) {
			if (in_array($rang, array('responsable'))) {
				$gestion_sous_poles = new Menu_element('gestion des sous-pôles', 'menu_gestion_sous_poles', site_url('gestion/sous_poles'));

				self::$_menu_elements['demandes']->insert_submenu($gestion_sous_poles);
			}
			$interservices = new Menu_element('faire une demande', 'menu_demande_interservices', site_url('demande'));

			self::$_menu_elements['demandes']->insert_submenu($interservices);

			if ($is_pole_sollicitable) {
				$historique_is_recues = new Menu_element('demandes reçues', 'menu_demandes_recues', site_url('historique/demandes_recues'));

				self::$_menu_elements['demandes']->insert_submenu($historique_is_recues);
			}

			$historique_is_envoyes = new Menu_element('demandes envoyées', 'menu_demandes_envoyees', site_url('historique/demandes_envoyees'));
			$historique_is_termines = new Menu_element('demandes terminées', 'menu_demandes_terminees', site_url('historique/demandes_terminees'));

			self::$_menu_elements['demandes']->insert_submenu($historique_is_envoyes);
			self::$_menu_elements['demandes']->insert_submenu($historique_is_termines);
		}

		if (in_array($rang, array('admin'))) {
			$statis_IS = new Menu_element('demandes interservices', 'menu_stats_interservices', site_url('statistique/gestion_interservices'));

			self::$_menu_elements['statistiques']->insert_submenu($statis_IS);
		}
	}

	// builds the menu options for the note module
	private function menu_custom_options_note($rang)
	{
		if (in_array($rang, array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'))) {
			$creation_note = new Menu_element('créer une note', 'menu_creation_note', site_url('note/creation'));
			$notes_envoyees = new Menu_element('mes notes', 'menu_mes_notes', site_url('note/notes_envoyees'));
			$notes_recues = new Menu_element('notes reçues', 'menu_notes_recues', site_url('note/notes_recues'));
			$notes_terminees = new Menu_element('notes terminées', 'menu_notes_terminees', site_url('note/notes_terminees'));
			$notes_refusees = new Menu_element('notes refusées', 'menu_notes_refusees', site_url('note/notes_refusees'));
			$validation_note = new Menu_element('notes à valider', 'menu_validation_note', site_url('note/notes_a_valider'));

			self::$_menu_elements['notes']->insert_submenu($creation_note);
			self::$_menu_elements['notes']->insert_submenu($notes_envoyees);
			self::$_menu_elements['notes']->insert_submenu($notes_recues);
			self::$_menu_elements['notes']->insert_submenu($notes_terminees);
			self::$_menu_elements['notes']->insert_submenu($notes_refusees);
			self::$_menu_elements['notes']->insert_submenu($validation_note);
		}
	}

	// builds the menu options for the citoyen module
	private function menu_custom_options_citoyen($rang)
	{
		$is_pole_sollicitable = $this->pole_query_model->is_sollicitable($this->session->userdata('pole'));
		
		if (in_array($rang, array('utilisateur_superieur', 'responsable')) && $is_pole_sollicitable) {
			$recu = new Menu_element('demandes citoyen reçues', 'menu_demande_citoyen_recu', site_url('citoyen/demande_citoyen'));
			$traite = new Menu_element('demandes citoyen traitées', 'menu_demande_citoyen_traite', site_url('citoyen/demande_citoyen_traitee'));

			self::$_menu_elements['citoyen']->insert_submenu($recu);
			self::$_menu_elements['citoyen']->insert_submenu($traite);
			/* if we want the same menu element for this module :
			self::$_menu_elements['demandes']->insert_submenu($recu);
			self::$_menu_elements['demandes']->insert_submenu($traite);
			*/
		}

		if (in_array($rang, array('responsable'))) {
			/*$responsable_demandes_citoyen = $this->formulaire_contact_model->find_responsable_demandes_citoyen();
			$is_responsable_demandes_citoyen = empty($responsable_demandes_citoyen) ? FALSE : ($responsable_demandes_citoyen->id == $this->session->userdata('id'));*/
			$is_responsable_demandes_citoyen = TRUE;

			if ($is_responsable_demandes_citoyen) {
				$validation_citoyen = new Menu_element('demande citoyen', 'menu_validation_citoyen', site_url('validation/demande_citoyen'));

				self::$_menu_elements['validation']->insert_submenu($validation_citoyen);
			}
		}

		if (in_array($rang, array('admin'))) {
			$gestion_resp_citoyen = new Menu_element('responsable des demandes citoyen', 'menu_resp_cit', site_url('gestion/responsable_citoyen'));

			self::$_menu_elements['gestion']->insert_submenu($gestion_resp_citoyen);
		}
	}

	// builds the menu options for the citoyen module
	private function set_options_notif()
	{
		$notif_list = $this->notification_model->find_user_notifications($this->session->userdata('id'));

		if ( ! empty($notif_list) ) {
			$marked_as_read =  new Menu_element('TOUT MARQUER COMME LU', 'all-marked-as-read', site_url('notifications/marked_as_read'));
			self::$_menu_elements['notif']->insert_submenu($marked_as_read);
			self::$_menu_elements['notif']->set_notification(count($notif_list));

			foreach ($notif_list as $notif) {
				$notif_nom = $notif->get_source()->get_title();
				$notif_lien = $notif->get_source()->get_link();

				$notif_element = new Menu_element($notif_nom, '', $notif_lien);

				$notif_element->set_contenu($notif->get_source()->get_content());

				self::$_menu_elements['notif']->insert_submenu($notif_element);
			}
		}
	}

	// builds the final menu
	private function menu_building()
	{
		$menu = '';
		$potential_empty_elements_list = array('accueil', 'profil', 'deco');
		$google_icon_elements_list = array('profil', 'deco');
		$notif_elements_list = array('notif');
		
		foreach (self::$_menu_elements as $key => $value) {
			if ( ! empty($value->get_elements()) || in_array($key, $potential_empty_elements_list)) {
				if (in_array($key, $google_icon_elements_list))
					$menu .= $value->create_html_code_for_google_icon();
				elseif (in_array($key, $notif_elements_list))
					$menu .= $value->create_html_code_for_notif();
				else
					$menu .= $value->create_html_code();
			}
		}

		return $menu;
	}





	///////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////// BLINKING CSS CODE ////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////





	// find the user warnings, then creates some css code in a <style> element, to make some menu elements blink
	private function creating_blinking_style_css($modules, $rang)
	{
		// here the only ranks having potentiel warning for validation or treatment
		if ( ! in_array($rang, array('rh', 'responsable', 'admin', 'utilisateur_superieur')) )
			return '';
		
		$elements = $this->find_elements_to_blink($modules, $rang);

		return (empty($elements)) ? '' : $this->blinking_css_code($elements);
	}

	// find the user warnings and define the elements to blink
	private function find_elements_to_blink($modules, $rang)
	{
		// default elements
		$elements = $this->find_elements_to_blink_default($rang);

		// module elements
		foreach ($modules as $key => $value) {
			$function_to_call = 'find_elements_to_blink_for_module_' . $value;

			$elements += $this->$function_to_call($rang);
		}

		return $this->create_str_elements_for_css($elements);
	}

	private function find_elements_to_blink_default($rang)
	{
		return $this->find_elements_to_blink_for_subscription($rang);
	}

	private function find_elements_to_blink_for_subscription($rang)
	{
		$elements = array();

		if ($rang === 'admin') {
			$existe_inscription_a_valider = $this->utilisateur_query_model->table_des_inscriptions_a_valider()->num_rows() != 0;

			if ($existe_inscription_a_valider) $elements['#menu_validation_inscriptions'] = '#menu_validation_inscriptions';

			if ( ! empty($elements) ) $elements['#menu_validation'] = '#menu_validation';

			return $elements;
		}

		return $elements;
	}

	private function find_elements_to_blink_for_module_interservices($rang)
	{
		$elements = array();

		if ($rang === 'responsable') {
			$existe_interservices_a_valider = $this->demande_interservices_query_model->table_demandes_a_valider($this->session->userdata('id'))->num_rows() != 0;

			if ($existe_interservices_a_valider) $elements['#menu_validation_interservices'] = '#menu_validation_interservices';

			if ( ! empty($elements) ) $elements['#menu_validation'] = '#menu_validation';

			return $elements;
		}

		return $elements;
	}

	private function find_elements_to_blink_for_module_news($rang)
	{
		$elements = array();
		$this->load->model('news/news_model');

		if ($rang === 'responsable') {
			$existe_news_a_valider = $this->article_query_model->table_articles_a_valider($this->session->userdata('id'))->num_rows() != 0;

			if ($existe_news_a_valider) $elements['#menu_validation_news'] = '#menu_validation_news';

			if ( ! empty($elements) ) $elements['#menu_validation'] = '#menu_validation';

			return $elements;
		}

		return $elements;
	}

	private function find_elements_to_blink_for_module_note($rang)
	{
		$elements = array();
		$this->load->model('note/note_model');

		if (in_array($this->session->userdata('rang'), array('responsable', 'utilisateur_superieur', 'utilisateur_particulier'))) {
			$existe_note_a_valider = $this->note_query_model->find_notes_a_valider($this->session->userdata('id'))->num_rows() != 0;

			if ($existe_note_a_valider) $elements['#menu_note'] = '#menu_notes';

			if ( ! empty($elements) ) $elements['#menu_validation_note'] = '#menu_validation_note';

			return $elements;
		}

		return $elements;
	}

	private function find_elements_to_blink_for_module_citoyen($rang)
	{
		$elements = array();
		$this->load->model('citoyen/citoyen_model');

		if ($rang === 'admin') {
			return $elements;
		}

		if ($rang === 'responsable') {
			/*$is_responsable_demandes_citoyen = empty($this->formulaire_contact_model->find_responsable_demandes_citoyen()) ? FALSE : ($this->formulaire_contact_model->find_responsable_demandes_citoyen()->id == $this->session->userdata('id'));

			if ($is_responsable_demandes_citoyen) {
				$existe_demande_citoyen_a_valider = $this->formulaire_contact_model->formatage_demandes_a_valider_existe();
				$existe_demande_citoyen_urgente = $this->formulaire_contact_model->formatage_demandes_urgentes_existe($pole_id, TRUE);
			} else {
				$existe_demande_citoyen_a_valider = FALSE;
				$existe_demande_citoyen_urgente = $this->formulaire_contact_model->formatage_demandes_urgentes_existe($pole_id, FALSE);
			}

			if ($existe_demande_citoyen_a_valider) $elements['#menu_validation_citoyen'] = '#menu_validation_citoyen';

			if ( ! empty($elements) ) $elements['#menu_validation'] = '#menu_validation';

			if ($existe_demande_citoyen_urgente) {
				$elements['#menu_demande_citoyen_recu'] = '#menu_demande_citoyen_recu';
				$elements['#menu_citoyen'] = '#menu_citoyen';
			}*/

			return $elements;
		}

		if ($rang === 'utilisateur_superieur') {
			/*$existe_demande_citoyen_urgente = $this->formulaire_contact_model->formatage_demandes_urgentes_existe($pole_id, FALSE);

			if ($existe_demande_citoyen_urgente) {
				$elements['#menu_demande_citoyen_recu'] = '#menu_demande_citoyen_recu';
				$elements['#menu_citoyen'] = '#menu_citoyen';
			}*/

			return $elements;
		}

		if ($rang === 'utilisateur_particulier') {
			return $elements;
		}

		if ($rang === 'rh') {
			return $elements;
		}

		return $elements;
	}

	private function create_str_elements_for_css($elements)
	{
		$str_elements = '';

		foreach ($elements as $key => $value)
			$str_elements .= $value . ',';

		return rtrim($str_elements, ',');
	}

	// creates the css code for the blinking
	private function blinking_css_code($elements)
	{
		$blinkink_elements = $this->blinking_css_code_for_elements($elements);
		$blinkink_elements .= $this->blinking_css_code_for_notif($elements);

		return $blinkink_elements;
	}

	// creates the css code for the blinking elements
	private function blinking_css_code_for_elements($elements)
	{
		return '
			<style>
	            '.$elements.' {
	                animation:          blinkingText 0.8s infinite;
	                -webkit-animation:  blinkingText 0.8s infinite;
	                -moz-animation:     blinkingText 0.8s infinite;
	                -o-animation:       blinkingText 0.8s infinite;
	            }
	            @keyframes blinkingText {
	                0%{     color: white;   }
	                49%{    color: red; }
	                80%{    color: white; }
	                100%{   color: white;   }
	            }
            </style>
		';
	}

	// creates the css code for the blinking notif
	private function blinking_css_code_for_notif() 
	{
		return '
			<style>
	            .notif {
	                position: absolute;
					top: -5px;
					padding: 5px 10px;
					border-radius: 50%;
					background-color: red;
					color: white;
	            }
	            .notif-comms {
	                position: absolute;
					top: 10px;
					right: -7px;
					padding: 5px 10px;
					border-radius: 50%;
					background-color: blue;
					color: white;
	            }
            </style>
		';
	}
}





	///////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////// MENU ELEMENT CLASS ///////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////





	
/*
* objet pour manipuler les éléments du menu de navigation
*
* @param	string (nom du menu qui sera affiché dans la barre)
* @param 	string (id de l'élément pour le css)
* @param 	string (lien vers la page)
* @param 	array (contient des objet de la classe Menu_element qui seront des sous-menu de cet élément)
* return 	string (code html)
*
*/
class Menu_element
{
	private $_nom;
	private $_id;
	private $_lien;
	private $_target;
	private $_elements;
	private $_notification = 0;
	private $_contenu = array();

	public function __construct(string $nom, string $id, string $lien, $elements = array(), bool $target = FALSE) {
		$this->_nom = $nom;
		$this->_lien = $lien;
		$this->_id = $id;
		$this->_elements = $elements;
		$this->_target = $target ? ' target="_blank"' : '';
	}

	public function create_html_code() {
		$notif = empty($this->_notification) ? '' : '<span class="notif">'.$this->_notification.'</span>';

		if (empty($this->_elements))
			return '<div style="position:relative;"><a href="' . $this->_lien . '" id="' . $this->_id . '" ' . $this->_target . '><strong>' . $this->_nom . $notif.'</strong></a></div>';
		else {
			$htmlString = 
			'<div class="dropdownmenu">
		      <button class="dropbtn" id="'.$this->_id.'"><div style="position:relative;"><strong>'.$this->_nom.'<div class="icone-menu">&nbsp;&#8249;</div>'.$notif.'</strong></div></button>
		    <div class="dropdownmenu-content">';
			foreach ($this->_elements as $element) {
				$htmlString .= $element->create_html_code();
			}
			$htmlString .= '</div></div>';

			return $htmlString;
		}
	}

	public function create_html_code_for_notif() {
		if (empty($this->_elements)) {
			$content = '
			<div style="position:relative;" id="' . $this->_id . '">
				<a href="' . $this->_lien . '">
					<div>
						<p><strong>'. $this->_nom . '</strong></p>';

			foreach ($this->_contenu as $key => $value) {
				$content .= '<span>'. $value . '</span><br>';
			}

			$content .= '
					</div>
				</a>
			</div>';

			return $content;
		}
			
		else {
			$notif = empty($this->_notification) ? '' : '<span class="notif">' . $this->_notification . '</span>';

			$htmlString = 
			'<div class="dropdownmenu icone-notif">
		      <button class="dropbtn" id="' . $this->_id . '"><div style="position:relative;"><strong>' . $this->_nom . '<div class="icone-menu">&nbsp;&#8249;</div>' . $notif . '</strong></div></button>
		    <div class="dropdownmenu-content-notif ">';
			foreach ($this->_elements as $element) {
				$htmlString .= $element->create_html_code_for_notif();
			}
			$htmlString .= '</div></div>';

			return $htmlString;
		}
	}

	public function create_html_code_for_google_icon() {
		return '<a style="height:51px;align-item:center;" href="' . $this->_lien . '"><strong>' . $this->_nom . '</strong></a>';
	}

	public function insert_submenu($element) {
		array_push($this->_elements, $element);
	}

	public function get_elements() {
		return $this->_elements;
	}

	public function set_notification(int $notification) {
		$this->_notification = $notification;
	}

	public function set_contenu(array $contenu) {
		$this->_contenu = $contenu;
	}
}
