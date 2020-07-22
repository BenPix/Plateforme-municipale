<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Settings_query_model extends MY_Model
{

	public function empty_database()
	{
		return $this->db->query('SHOW TABLES')->num_rows() === 0;
	}

	public function build_database($modules_choice)
	{
		// database structure
		$this->load->dbforge();

		$this->create_default_tables();

		$this->create_foreign_keys();

		$this->insert_default_data();

		// custom the data according to the user module choices
		$this->update_data_module($modules_choice);

		return TRUE;
	}

	public function insert_user_data($user_data)
	{
		$succed = $this->email_domaine_query_model->create(array('domaine' => $user_data['email_domaine']));

		if ( ! $succed ) return FALSE;

		$mail = $user_data['email_nom'].'@'.$user_data['email_domaine'];

		$succed = (bool) $this->db
		->set(array('mail' => $mail))
		->insert('mail_admin');

		if ( ! $succed ) return FALSE;

		$succed = (bool) $this->db
		->set(array('id' => 1, 'nom' => $user_data['commune']))
		->insert('commune');

		if ( ! $succed ) return FALSE;

		unset($user_data['email_domaine']);
		unset($user_data['commune']);

		return $this->utilisateur_query_model->create($user_data);
	}

	private function create_default_tables()
	{
		$this->create_tables_for_general_use();

		$this->create_tables_for_modules();
	}

	private function create_tables_for_general_use()
	{
		$this->create_table_categorie_article();
		$this->create_table_ci_captcha();
		$this->create_table_delegation();
		$this->create_table_email_domaine();
		$this->create_table_inscription_a_valider();
		$this->create_table_mail_admin();
		$this->create_table_module();
		$this->create_table_notif();
		$this->create_table_notif_recipient();
		$this->create_table_notif_source_categorie();
		$this->create_table_notif_source_type();
		$this->create_table_pole();
		$this->create_table_pole_autorise_au_bon_de_commande();
		$this->create_table_pole_delegue();
		$this->create_table_pole_inactive();
		$this->create_table_pole_sous_responsable();
		$this->create_table_rang_utilisateur();
		$this->create_table_recaptcha();
		$this->create_table_token_password();
		$this->create_table_utilisateur();
		$this->create_table_utilisateur_inactive();
		$this->create_table_ville();
	}

	private function create_table_rang_utilisateur()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 2,
				'auto_increment' => TRUE
			),
			'rang' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('rang_utilisateur', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_email_domaine()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 3,
				'auto_increment' => TRUE
			),
			'domaine' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('email_domaine', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_categorie_article()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 3,
				'auto_increment' => TRUE
			),
			'nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'cible' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('categorie_article', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_pole()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 3,
				'auto_increment' => TRUE
			),
			'nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'sollicitable_via_interservices' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 1
			),
			'sujet_aux_conges' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0
			),
			'responsable_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,
				'default' => NULL
			),
			'confidentialite' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0
			),
			'categorie_id' => array(
				'type' => 'INT',
				'constraint' => 3,
				'default' => 1
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('pole', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_pole_inactive()
	{
		$fields = array(
			'pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('pole_id', TRUE);
		$this->dbforge->create_table('pole_inactive', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_utilisateur()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'prenom' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'email_nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'email_domaine_id' => array(
				'type' => 'INT',
				'constraint' => 3
			),
			'pseudo' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'unique' => TRUE
			),
			'rang_id' => array(
				'type' => 'INT',
				'constraint' => 2,
				'null' => TRUE,
				'default' => NULL
			),
			'pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			),
			'password' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('utilisateur', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_utilisateur_inactive()
	{
		$fields = array(
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->create_table('utilisateur_inactive', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_inscription_a_valider()
	{
		$fields = array(
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->create_table('inscription_a_valider', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_mail_admin()
	{
		$fields = array(
			'id' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'auto_increment' => TRUE
			),
			'mail' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('mail_admin', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_ville()
	{
		$fields = array(
			'id' => array(
				'type' => 'TINYINT',
				'constraint' => 1
			),
			'nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('commune', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_module()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 2,
				'auto_increment' => TRUE
			),
			'module' => array(
				'type' => 'VARCHAR',
				'constraint' => 100
			),
			'actif' => array(
				'type' => 'TINYINT',
				'constraint' => 1
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('module', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_token_password()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			),
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unique' => TRUE
			),
			'date_token' => array(
				'type' => 'DATE'
			),
			'donnee_timestamp' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('token_for_password', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_notif()
	{
		$fields = array(
			'id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'auto_increment' => TRUE
			),
			'source_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'source_type_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('notif', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_notif_recipient()
	{
		$fields = array(
			'notif_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20
			),
			'recipient_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'read' => array(
				'type' => 'TINYINT',
				'constraint' => 1
			),
			'reading_date' => array(
				'type' => 'DATETIME'
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->create_table('notif_recipient', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_notif_source_categorie()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'categorie' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('notif_source_categorie', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_notif_source_type()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'categorie_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'type' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('notif_source_type', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_pole_autorise_au_bon_de_commande()
	{
		$fields = array(
			'pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			),
			'acces_ecriture' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('pole_id', TRUE);
		$this->dbforge->create_table('pole_autorise_au_bon_de_commande', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_pole_sous_responsable()
	{
		$fields = array(
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->add_key('pole_id', TRUE);
		$this->dbforge->create_table('pole_sous_responsable', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_delegation()
	{
		$fields = array(
			'responsable_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'delegue_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'ancien_rang_delegue_id' => array(
				'type' => 'INT',
				'constraint' => 2
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('responsable_id', TRUE);
		$this->dbforge->create_table('delegation', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_pole_delegue()
	{
		$fields = array(
			'pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			),
			'responsable_originel_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('pole_id', TRUE);
		$this->dbforge->create_table('pole_delegue', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_recaptcha()
	{
		$fields = array(
			'id' => array(
				'type' => 'TINYINT',
				'constraint' => 1
			),
			'data_sitekey' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			),
			'data_secretkey' => array(
				'type' => 'VARCHAR',
				'constraint' => 255
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('recaptcha', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_ci_captcha()
	{
		$fields = array(
			'captcha_id' => array(
				'type' => 'BIGINT',
				'constraint' => 13,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'captcha_time' => array(
				'type' => 'INT',
				'constraint' => 10,
				'unsigned' => TRUE
			),
			'ip_address' => array(
				'type' => 'VARCHAR',
				'constraint' => 45
			),
			'word' => array(
				'type' => 'VARCHAR',
				'constraint' => 20
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('captcha_id', TRUE);
		$this->dbforge->add_key('word');
		$this->dbforge->create_table('ci_captcha', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_tables_for_modules()
	{
		$this->create_tables_for_module_interservices();
		$this->create_tables_for_module_news();
		$this->create_tables_for_module_note();
	}

	private function create_tables_for_module_interservices()
	{
		$this->create_table_sous_pole();
		$this->create_table_sous_pole_inactive();
		$this->create_table_appartenance_sous_pole();
		$this->create_table_statut_demande_interservices();
		$this->create_table_demande_interservices();
		$this->create_table_echeance_demande_interservices();
		$this->create_table_upload();
		$this->create_table_demande_interservices_a_valider();
		$this->create_table_commentaire_demande_interservices();
		$this->create_table_relance_demande_interservices();
		$this->create_table_demande_interservices_affectee_a_sous_pole();
		$this->create_table_demande_interservices_confidentielle();
		$this->create_table_demande_interservices_origine_sous_pole();
		$this->create_table_demande_interservices_terminee();
	}

	private function create_table_sous_pole()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 3,
				'auto_increment' => TRUE
			),
			'nom' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'pole_mere_id' => array(
				'type' => 'INT',
				'constraint' => 3
			),
			'couleur' => array(
				'type' => 'VARCHAR',
				'constraint' => 10
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('sous_pole', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_sous_pole_inactive()
	{
		$fields = array(
			'sous_pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('sous_pole_id', TRUE);
		$this->dbforge->create_table('sous_pole_inactive', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_appartenance_sous_pole()
	{
		$fields = array(
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'sous_pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->add_key('sous_pole_id', TRUE);
		$this->dbforge->create_table('appartenance_sous_pole', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_statut_demande_interservices()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 2,
				'auto_increment' => TRUE
			),
			'etat' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('statut_demande_interservices', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_demande_interservices()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'num_dossier' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => 0
			),
			'horodateur' => array(
				'type' => 'DATE'
			),
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'pole_attache_id' => array(
				'type' => 'INT',
				'constraint' => 3
			),
			'pole_sollicite_id' => array(
				'type' => 'INT',
				'constraint' => 3
			),
			'demande' => array(
				'type' => 'VARCHAR',
				'constraint' => 3000
			),
			'statut_id' => array(
				'type' => 'INT',
				'constraint' => 2
			),
			'degre_urgence' => array(
				'type' => 'TINYINT',
				'constraint' => 1
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('demande_interservices', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_echeance_demande_interservices()
	{
		$fields = array(
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'echeance' => array(
				'type' => 'DATE'
			),
			'date_precise' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('demande_interservices_id', TRUE);
		$this->dbforge->create_table('echeance_demande_interservices', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_upload()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,
				'default' => NULL
			),
			'file_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255				
			),
			'file_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 255				
			),
			'file_size' => array(
				'type' => 'FLOAT',
				'constraint' => 1
			),
			'date_upload' => array(
				'type' => 'DATE'
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('upload', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_demande_interservices_a_valider()
	{
		$fields = array(
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('demande_interservices_id', TRUE);
		$this->dbforge->create_table('demande_interservices_a_valider', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_commentaire_demande_interservices()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'horodateur' => array(
				'type' => 'DATETIME'
			),
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'commentaire' => array(
				'type' => 'VARCHAR',
				'constraint' => 3000
			),
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('commentaire_demande_interservices', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_relance_demande_interservices()
	{
		$fields = array(
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'date_relance' => array(
				'type' => 'DATE'
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('demande_interservices_id', TRUE);
		$this->dbforge->create_table('relance_demande_interservices', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_demande_interservices_affectee_a_sous_pole()
	{
		$fields = array(
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'sous_pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('demande_interservices_id', TRUE);
		$this->dbforge->create_table('demande_interservices_affectee_a_sous_pole', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_demande_interservices_confidentielle()
	{
		$fields = array(
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'sous_pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('demande_interservices_id', TRUE);
		$this->dbforge->create_table('demande_interservices_confidentielle', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_demande_interservices_origine_sous_pole()
	{
		$fields = array(
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'sous_pole_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('demande_interservices_id', TRUE);
		$this->dbforge->create_table('demande_interservices_origine_sous_pole', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_demande_interservices_terminee()
	{
		$fields = array(
			'demande_interservices_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'date_fin' => array(
				'type' => 'DATE'
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('demande_interservices_id', TRUE);
		$this->dbforge->create_table('demande_interservices_terminee', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_tables_for_module_news()
	{
		$this->create_table_article();
		$this->create_table_article_a_valider();
		$this->create_table_appartenance_categorie_article();
		$this->create_table_appartenance_categorie_utilisateur();
	}

	private function create_table_article()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'titre' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => 0
			),
			'description' => array(
				'type' => 'VARCHAR',
				'constraint' => 1000,
			),
			'contenu' => array(
				'type' => 'MEDIUMTEXT'
			),
			'commentaire_autorise' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0
			),
			'redacteur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'nom_image' => array(
				'type' => 'VARCHAR',
				'constraint' => 100
			),
			'nom_banniere' => array(
				'type' => 'VARCHAR',
				'constraint' => 100
			),
			'date_creation' => array(
				'type' => 'DATE'
			),
			'date_suppression' => array(
				'type' => 'DATE'
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('article', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_article_a_valider()
	{
		$fields = array(
			'article_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('article_id', TRUE);
		$this->dbforge->create_table('article_a_valider', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_appartenance_categorie_article()
	{
		$fields = array(
			'article_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'categorie_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('article_id', TRUE);
		$this->dbforge->add_key('categorie_id', TRUE);
		$this->dbforge->create_table('appartenance_categorie_article', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_appartenance_categorie_utilisateur()
	{
		$fields = array(
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'categorie_id' => array(
				'type' => 'INT',
				'constraint' => 3
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->add_key('categorie_id', TRUE);
		$this->dbforge->create_table('appartenance_categorie_utilisateur', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_tables_for_module_note()
	{
		$this->create_table_note();
		$this->create_table_note_upload();
		$this->create_table_note_workflow();
		$this->create_table_note_validation();
		$this->create_table_note_refus();
		$this->create_table_note_commentaire();
	}

	private function create_table_note()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'objet' => array(
				'type' => 'VARCHAR',
				'constraint' => 50
			),
			'note' => array(
				'type' => 'TEXT'
			),
			'horodateur' => array(
				'type' => 'DATETIME'
			),
			'redacteur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('note', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_note_upload()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'note_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'file_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255				
			),
			'file_type' => array(
				'type' => 'VARCHAR',
				'constraint' => 255				
			),
			'file_size' => array(
				'type' => 'FLOAT',
				'constraint' => 1
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('note_upload', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_note_workflow()
	{
		$fields = array(
			'note_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'etape' => array(
				'type' => 'TINYINT',
				'constraint' => 2
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('note_id', TRUE);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->create_table('note_workflow', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_note_validation()
	{
		$fields = array(
			'note_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'horodateur' => array(
				'type' => 'DATETIME'
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('note_id', TRUE);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->create_table('note_validation', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_note_refus()
	{
		$fields = array(
			'note_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unique' => TRUE
			),
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'horodateur' => array(
				'type' => 'DATETIME'
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('note_id', TRUE);
		$this->dbforge->add_key('utilisateur_id', TRUE);
		$this->dbforge->create_table('note_refus', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_table_note_commentaire()
	{
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'note_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'utilisateur_id' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'horodateur' => array(
				'type' => 'DATETIME'
			),
			'commentaire' => array(
				'type' => 'VARCHAR',
				'constraint' => 3000				
			)
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('note_commentaire', FALSE, array('ENGINE' => 'InnoDB'));
	}

	private function create_foreign_keys()
	{
		$this->create_foreign_keys_for_general_use();

		$this->create_foreign_keys_for_modules();
	}

	private function create_foreign_keys_for_general_use()
	{
		$this->add_foreign_key('pole', 'responsable_id', 'utilisateur(id)');
		$this->add_foreign_key('pole', 'categorie_id', 'categorie_article(id)');

		$this->add_foreign_key('utilisateur', 'email_domaine_id', 'email_domaine(id)');
		$this->add_foreign_key('utilisateur', 'rang_id', 'rang_utilisateur(id)');
		$this->add_foreign_key('utilisateur', 'pole_id', 'pole(id)');

		$this->add_foreign_key('pole_inactive', 'pole_id', 'pole(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('inscription_a_valider', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('token_for_password', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		
		$this->add_foreign_key('utilisateur_inactive', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		
		$this->add_foreign_key('notif', 'source_type_id', 'notif_source_type(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('notif_recipient', 'notif_id', 'notif(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('notif_recipient', 'recipient_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('notif_source_type', 'categorie_id', 'notif_source_categorie(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('pole_autorise_au_bon_de_commande', 'pole_id', 'pole(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('pole_sous_responsable', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('pole_sous_responsable', 'pole_id', 'pole(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('delegation', 'responsable_id', 'utilisateur(id)');
		$this->add_foreign_key('delegation', 'delegue_id', 'utilisateur(id)');
		$this->add_foreign_key('delegation', 'ancien_rang_delegue_id', 'rang_utilisateur(id)');
		
		$this->add_foreign_key('pole_delegue', 'pole_id', 'pole(id)');
		$this->add_foreign_key('pole_delegue', 'responsable_originel_id', 'utilisateur(id)');
	}

	private function create_foreign_keys_for_modules()
	{
		$this->create_foreign_keys_for_module_interservices();
		$this->create_foreign_keys_for_module_news();
		$this->create_foreign_keys_for_module_note();
	}

	private function create_foreign_keys_for_module_interservices()
	{
		$this->add_foreign_key('sous_pole', 'pole_mere_id', 'pole(id)');

		$this->add_foreign_key('sous_pole_inactive', 'sous_pole_id', 'sous_pole(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('appartenance_sous_pole', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('appartenance_sous_pole', 'sous_pole_id', 'sous_pole(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('demande_interservices', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('demande_interservices', 'pole_attache_id', 'pole(id)');
		$this->add_foreign_key('demande_interservices', 'pole_sollicite_id', 'pole(id)');
		$this->add_foreign_key('demande_interservices', 'statut_id', 'statut_demande_interservices(id)');

		$this->add_foreign_key('echeance_demande_interservices', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('demande_interservices_a_valider', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('upload', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('commentaire_demande_interservices', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('commentaire_demande_interservices', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('relance_demande_interservices', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('demande_interservices_affectee_a_sous_pole', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE', 'demande_is_affectee_a_sous_pole_demande_is_id_fk');
		$this->add_foreign_key('demande_interservices_affectee_a_sous_pole', 'sous_pole_id', 'sous_pole(id)', 'CASCADE', 'CASCADE', 'demande_is_affectee_a_sous_pole_sous_pole_id_fk');

		$this->add_foreign_key('demande_interservices_confidentielle', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE', 'demande_is_confidentielle_demande_is_id_fk');
		$this->add_foreign_key('demande_interservices_confidentielle', 'sous_pole_id', 'sous_pole(id)', 'CASCADE', 'CASCADE', 'demande_is_confidentielle_sous_pole_id_fk');

		$this->add_foreign_key('demande_interservices_origine_sous_pole', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE', 'demande_is_origine_sous_pole_demande_is_id_fk');
		$this->add_foreign_key('demande_interservices_origine_sous_pole', 'sous_pole_id', 'sous_pole(id)', 'CASCADE', 'CASCADE', 'demande_is_origine_sous_pole_sous_pole_id_fk');
		
		$this->add_foreign_key('demande_interservices_terminee', 'demande_interservices_id', 'demande_interservices(id)', 'CASCADE', 'CASCADE', 'demande_is_terminee_demande_is_id_fk');
	}

	private function create_foreign_keys_for_module_news()
	{
		$this->add_foreign_key('article', 'redacteur_id', 'utilisateur(id)');

		$this->add_foreign_key('article_a_valider', 'article_id', 'article(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('appartenance_categorie_article', 'article_id', 'article(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('appartenance_categorie_article', 'categorie_id', 'categorie_article(id)');

		$this->add_foreign_key('appartenance_categorie_utilisateur', 'utilisateur_id', 'utilisateur(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('appartenance_categorie_utilisateur', 'categorie_id', 'categorie_article(id)');
	}

	private function create_foreign_keys_for_module_note()
	{
		$this->add_foreign_key('note', 'redacteur_id', 'utilisateur(id)');

		$this->add_foreign_key('note_upload', 'note_id', 'note(id)', 'CASCADE', 'CASCADE');

		$this->add_foreign_key('note_commentaire', 'note_id', 'note(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('note_commentaire', 'utilisateur_id', 'utilisateur(id)');

		$this->add_foreign_key('note_workflow', 'note_id', 'note(id)', 'CASCADE', 'CASCADE');
		$this->add_foreign_key('note_workflow', 'utilisateur_id', 'utilisateur(id)');

		$this->add_foreign_key('note_validation', 'note_id, utilisateur_id', 'note_workflow(note_id, utilisateur_id)', 'CASCADE', 'CASCADE', 'note_validation_note_id_utilisateur_id');

		$this->add_foreign_key('note_refus', 'note_id, utilisateur_id', 'note_workflow(note_id, utilisateur_id)', 'CASCADE', 'CASCADE', 'note_refus_note_id_utilisateur_id');
	}

	private function insert_default_data()
	{
		$this->insert_default_data_for_general_use();

		$this->insert_default_data_for_modules();
	}

	private function insert_default_data_for_general_use()
	{
		$this->insert_default_data_categorie_article();
		$this->insert_default_data_module();
		$this->insert_default_data_notif_source_categorie();
		$this->insert_default_data_notif_source_type();
		$this->insert_default_data_pole();
		$this->insert_default_data_rang_utilisateur();
	}

	private function insert_default_data_rang_utilisateur()
	{
		$this->rang_utilisateur_query_model->create(array('rang' => 'admin', 'nom' => 'Administrateur du site'));
		$this->rang_utilisateur_query_model->create(array('rang' => 'rh', 'nom' => 'Responsable aux ressources humaines'));
		$this->rang_utilisateur_query_model->create(array('rang' => 'responsable', 'nom' => 'Responsable de Service'));
		$this->rang_utilisateur_query_model->create(array('rang' => 'utilisateur_superieur', 'nom' => 'Utilisateur supérieur'));
		$this->rang_utilisateur_query_model->create(array('rang' => 'utilisateur_simple', 'nom' => 'Utilisateur simple'));
		$this->rang_utilisateur_query_model->create(array('rang' => 'utilisateur_particulier', 'nom' => 'Utilisateur particulier'));
		$this->rang_utilisateur_query_model->create(array('rang' => 'redacteur', 'nom' => 'Rédacteur'));
	}

	private function insert_default_data_categorie_article()
	{
		$this->categorie_article_query_model->create(array('nom' => 'Agent', 'cible' => 'agents municipaux'));
	}

	private function insert_default_data_pole()
	{
		$this->pole_query_model->create(array(
			'nom' => 'Administration du site',
			'sollicitable_via_interservices' => 0,
			'sujet_aux_conges' => 0,
			'confidentialite' => 0,
			'categorie_id' => 1
		));
	}

	private function insert_default_data_module()
	{
		$this->module_query_model->create(array('id' => 1, 'module' => 'interservices', 'actif' => 0));
		$this->module_query_model->create(array('id' => 2, 'module' => 'news', 'actif' => 0));
		$this->module_query_model->create(array('id' => 3, 'module' => 'note', 'actif' => 0));
	}

	private function insert_default_data_notif_source_categorie()
	{
		$this->notif_source_categorie_query_model->create(array('id' => 1, 'categorie' => 'demande_interservices'));
		$this->notif_source_categorie_query_model->create(array('id' => 2, 'categorie' => 'demande_citoyen_app_cfdb7'));
		$this->notif_source_categorie_query_model->create(array('id' => 3, 'categorie' => 'demande_citoyen_site'));
		$this->notif_source_categorie_query_model->create(array('id' => 4, 'categorie' => 'conges'));
		$this->notif_source_categorie_query_model->create(array('id' => 5, 'categorie' => 'bon_de_commande'));
		$this->notif_source_categorie_query_model->create(array('id' => 6, 'categorie' => 'demande_citoyen_site_cfdb7'));
		$this->notif_source_categorie_query_model->create(array('id' => 7, 'categorie' => 'news'));
	}

	private function insert_default_data_notif_source_type()
	{
		$this->notif_source_type_query_model->create(array('id' => 1, 'categorie_id' => 1, 'type' => 'valide'));
		$this->notif_source_type_query_model->create(array('id' => 2, 'categorie_id' => 1, 'type' => 'relance'));
		$this->notif_source_type_query_model->create(array('id' => 3, 'categorie_id' => 1, 'type' => 'affecte_sous_pole'));
		$this->notif_source_type_query_model->create(array('id' => 4, 'categorie_id' => 1, 'type' => 'changement_de_statut'));
		$this->notif_source_type_query_model->create(array('id' => 5, 'categorie_id' => 1, 'type' => 'commentaire'));
		$this->notif_source_type_query_model->create(array('id' => 6, 'categorie_id' => 1, 'type' => 'delai'));
		$this->notif_source_type_query_model->create(array('id' => 7, 'categorie_id' => 2, 'type' => 'transfere_pole'));
		$this->notif_source_type_query_model->create(array('id' => 8, 'categorie_id' => 2, 'type' => 'transfere_sous_pole'));
		$this->notif_source_type_query_model->create(array('id' => 9, 'categorie_id' => 2, 'type' => 'reponse'));
		$this->notif_source_type_query_model->create(array('id' => 10, 'categorie_id' => 3, 'type' => 'transfere_pole'));
		$this->notif_source_type_query_model->create(array('id' => 11, 'categorie_id' => 3, 'type' => 'transfere_sous_pole'));
		$this->notif_source_type_query_model->create(array('id' => 12, 'categorie_id' => 3, 'type' => 'reponse'));
		$this->notif_source_type_query_model->create(array('id' => 13, 'categorie_id' => 4, 'type' => 'valide'));
		$this->notif_source_type_query_model->create(array('id' => 14, 'categorie_id' => 4, 'type' => 'heure_supp_validee'));
		$this->notif_source_type_query_model->create(array('id' => 15, 'categorie_id' => 5, 'type' => 'valide'));
		$this->notif_source_type_query_model->create(array('id' => 16, 'categorie_id' => 5, 'type' => 'refuse'));
		$this->notif_source_type_query_model->create(array('id' => 17, 'categorie_id' => 6, 'type' => 'transfere_pole'));
		$this->notif_source_type_query_model->create(array('id' => 18, 'categorie_id' => 6, 'type' => 'transfere_sous_pole'));
		$this->notif_source_type_query_model->create(array('id' => 19, 'categorie_id' => 6, 'type' => 'reponse'));
		$this->notif_source_type_query_model->create(array('id' => 20, 'categorie_id' => 7, 'type' => 'valide'));
	}

	private function insert_default_data_for_modules()
	{
		$this->insert_default_data_statut_demande_interservices();
	}

	private function insert_default_data_statut_demande_interservices()
	{
		$this->load->model('database_query/mysql/interservices/statut_demande_interservices_query_model');

		$this->statut_demande_interservices_query_model->create(array('id' => 1, 'etat' => 'en attente'));
		$this->statut_demande_interservices_query_model->create(array('id' => 2, 'etat' => 'en cours'));
		$this->statut_demande_interservices_query_model->create(array('id' => 3, 'etat' => 'terminé'));
		$this->statut_demande_interservices_query_model->create(array('id' => 4, 'etat' => 'refusé'));
	}

	private function update_data_module($modules_choice)
	{
		foreach ($modules_choice as $value) {
			$this->module_query_model->update(array('module' => $value), array('actif' => 1));
		}
	}

}