<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class MY_Model extends CI_Model
{
	/**
	 *	Insère une nouvelle ligne dans la base de données.
	 */
	public function create($options_echappees = array(), $options_non_echappees = array())
	{
	//	Vérification des données à insérer
		if(empty($options_echappees) AND empty($options_non_echappees))
		{
			return FALSE;
		}

		return (bool) $this->db
		->set($options_echappees)
		->set($options_non_echappees, NULL, FALSE)
		->insert($this->table);
	}

	/**
	 *	Insère une nouvelle ligne dans la base de données et trouve l'ID auto-incrémenté.
	 */
	public function create_and_find_id($options_echappees = array(), $options_non_echappees = array())
	{
	//	Vérification des données à insérer
		if(empty($options_echappees) AND empty($options_non_echappees))
		{
			return FALSE;
		}

		return (bool) $this->db
		->set($options_echappees)
		->set($options_non_echappees, NULL, FALSE)
		->insert($this->table) ? $this->db->insert_id() : FALSE;
	}

	/**
	 *	Insère une nouvelle ligne dans la base de données et renvoie le résultat de la ligne créée
	 *  Attention, il faut impérativement que cette ligne contienne une colonne 'id' qui l'identifie.
	 */
	public function create_and_find($options_echappees = array(), $options_non_echappees = array())
	{
	//	Vérification des données à insérer
		if(empty($options_echappees) AND empty($options_non_echappees))
		{
			return FALSE;
		}

		$check = (bool) $this->db
		->set($options_echappees)
		->set($options_non_echappees, NULL, FALSE)
		->insert($this->table);

		// si l'insert échoue, on renvoie faux
		if ($check === FALSE) return FALSE;

		// sinon on trouve la ligne insérée
		$query = $this->db->select()
		->from($this->table)
		->where('id',$this->db->insert_id())
		->get();

		// si on ne trouve pas de ligne, on renvoie faux
		if ($query->num_rows() == 0) return FALSE;

		// sinon on renvoie la ligne
		return $query->row();
	}

	/**
	 *	Récupère des données dans la base de données.
	 */
	public function read($where = array())
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where($where);
		$query = $this->db->get();

		return $query->result();
	}

	/**
	 *	Récupère des données dans la base de données pour créer une table.
	 */
	public function read_for_table($select = array(), $where = array(), $offset = 0, $limit = 1000)
	{
		$this->db->select($select, FALSE);
		$this->db->from($this->table);
		$this->db->where($where);
		$this->db->order_by('id', 'DESC');
		$this->db->limit($limit);
		$this->db->offset($offset);
		$query = $this->db->get();

		return $query;
	}
	
	/**
	 *	Modifie une ou plusieurs lignes dans la base de données.
	 */
	public function update($where, $options_echappees = array(), $options_non_echappees = array())
	{		
	//	Vérification des données à mettre à jour
		if(empty($options_echappees) AND empty($options_non_echappees))
		{
			return FALSE;
		}

		// Raccourci dans le cas où on sélectionne l'id
		if(is_integer($where))
		{
			$where = array('id' => $where);
		}

		$check = (bool) $this->db
		->set($options_echappees)
		->set($options_non_echappees, NULL, FALSE)
		->where($where)
		->update($this->table);

		return $check;
	}
	
	/**
	 *	Vide une table de la base de données.
	 */
	public function truncate()
	{
		return (bool) $this->db
		->truncate($this->table);
	}
	
	/**
	 *	Supprime une ou plusieurs lignes de la base de données.
	 */
	public function delete($where)
	{
		if(is_integer($where))
		{
			$where = array('id' => $where);
		}

		return (bool) $this->db
		->where($where)
		->delete($this->table);
	}

	/**
	 *	Retourne le nombre de résultats.
	 * Si aucun argument, renvoie le nombre totale d'entrées dans la table.
	 * Deux chaînes de caractères : le premier est le nom du champ, le second est sa valeur.
	 * Un tableau associatif en premier paramètre pour indiquer plusieurs conditions.
	 */
	public function count($champ = array(), $valeur = NULL)
	// Si $champ est un array, la variable $valeur sera ignorée par la méthode where()
	{
		return (int) $this->db
		->where($champ, $valeur)
		->from($this->table)
		->count_all_results();
	}

	public function count_max_field($champ)
	{
		return $this->db
		->select_max($champ)
		->get($this->table)
		->result_array();
	}

	/**
	 *	Trouve la ligne demandée.
	 */
	public function find($where = array())
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where($where);
		$this->db->limit(1);
		$query = $this->db->get();

		$row = $query->row();

		if (isset($row)) {
			return $row;
		} else {
			return FALSE;
		}
	}

	/**
	 *	Comme la fonction find mais retourne un boolean.
	 */
	public function exists($where = array())
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where($where);
		$this->db->limit(1);
		$query = $this->db->get();

		$row = $query->row();

		if (isset($row)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function exists_one_only($where = array())
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where($where);
		$rows = $this->db->count_all_results();

		if ($rows == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function liste($nomColonne) {
		$results = $this->db
		->select($nomColonne) 
		->from($this->table)
		->get()
		->result_array();

		$liste = array_column($results, $nomColonne);

		foreach ($liste as $key => $value) {
			$liste[$value] = $value;
			unset($liste[$key]);
		}

		return $liste;
	}

	public function insert_or_duplicate($data)
	{
		$data_non_echappees = empty($this->data_non_echappees) ? array() : $this->data_non_echappees;
		$requete = 'INSERT into '.$this->table.' (';

		foreach ($data as $key => $value) {
			$requete .= $key;
			$requete .= ',';
		}
		$requete = rtrim($requete, ',');
		$requete .= ') VALUES (';

		foreach ($data as $key => $value) {
			if (in_array($key,  $data_non_echappees)) {
				$requete .= $value;
				$requete .= ',';
			} else {
				$requete .= '\''.$value;
				$requete .= '\',';
			}
		}
		$requete = rtrim($requete, ',');
		$requete .= ') ON DUPLICATE KEY UPDATE ';

		foreach ($data as $key => $value) {
			if ($this->cle != $key) {
				if (in_array($key,  $data_non_echappees)) {
					$requete .= $key.'='.$value;
					$requete .= ',';
				} else {
					$requete .= $key.'=\''.$value;
					$requete .= '\',';
				}
			}
		}
		$requete = rtrim($requete, ',');

		$this->db->query($requete);
	}

	public function add_foreign_key($table, $foreign_key, $references, $on_delete = 'RESTRICT', $on_update = 'RESTRICT', $constraint_name = '')
    {
        $constraint = empty($constraint_name) ? "{$table}_{$foreign_key}_fk" : $constraint_name;

        // max length for a constraint name is 64, so we cut if too long
        if (strlen($constraint) >= 64) $constraint = substr($constraint, 0, 61) . '_fk';

        $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$constraint} FOREIGN KEY ({$foreign_key}) REFERENCES {$references} ON DELETE {$on_delete} ON UPDATE {$on_update}";
        
        return(bool) $this->db->query($sql);
    }



	// checks if any of this table data is used as foreign key.
	public function table_data_is_used_in_foreign_key($id)
	{
		$database_name = $this->db->database;

		$query = $this->db->query("
			SELECT
			  TABLE_NAME AS 'table',
			  COLUMN_NAME AS 'column',
			  CONSTRAINT_NAME,
			  REFERENCED_TABLE_NAME,
			  REFERENCED_COLUMN_NAME
			FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
			WHERE
			  REFERENCED_TABLE_NAME = '".$this->table."'
			AND TABLE_SCHEMA = '".$database_name."';"
		);

		foreach ($query->result() as $row) {
			$this->db->select('1');
			$this->db->from($row->table);
			$this->db->where(array($row->column => $id));
			$query = $this->db->get();

			if ($query->num_rows() !== 0) return TRUE;
		}

		return FALSE;
	}
}

/* End of file MY_Model.php */
