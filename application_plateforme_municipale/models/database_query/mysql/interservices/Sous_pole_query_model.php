<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Sous_pole_query_model extends MY_Model
{

	protected $table = 'sous_pole';

	/*
	* liste de tous les sous-pôles existants, avec possibilité de réduire à un pôle parent
	*/
	public function liste_sous_poles($pole_id = 0) {
		$this->db->select('sp.nom');
		$this->db->from('sous_pole AS sp');
		$this->db->join('sous_pole_inactive AS spi', 'sp.id = spi.sous_pole_id', 'left');
		$this->db->join('pole AS p', 'sp.pole_mere_id = p.id');
		$this->db->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left');
		$this->db->where('spi.sous_pole_id IS NULL');
		$this->db->where('pi.pole_id IS NULL');
		if ( ! empty($pole_id) )
			$this->db->where(array('p.id' => $pole_id));
		$this->db->order_by('sp.nom');
		$query = $this->db->get();
		$results = $query->result_array();

		$liste = array_column($results, 'nom');

		foreach ($liste as $key => $value) {
			$liste[$value] = $value;
			unset($liste[$key]);
		}

		return $liste;
	}

	/*
	* liste de tous les sous-pôles existants pour un élément <select>
	*/
	public function liste_sous_poles_for_dropdown() {
		$results = $this->db
		->select(array('sp.nom', 'sp.id')) 
		->from('sous_pole AS sp')
		->join('sous_pole_inactive AS spi', 'sp.id = spi.sous_pole_id', 'left')
		->join('pole AS p', 'sp.pole_mere_id = p.id')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where('spi.sous_pole_id IS NULL')
		->where('pi.pole_id IS NULL')
		->order_by('sp.nom')
		->get()
		->result();

		$liste = array();

		foreach ($results as $row) {
			$liste[$row->id] = $row->nom;
		}

		return $liste;
	}

	/*
	* liste de tous les pôles sollicitables pour les demandes interservices dans un select avec optGroup
	*/
	public function liste_poles_sollicitables_avec_opt_group_par_pole($pole_id = 0) {
		$where = array('spi.sous_pole_id' => NULL, 'pi.pole_id' => NULL, 'p.sollicitable_via_interservices' => 1);
		if ($pole_id !== 0) $where['p.id'] = $pole_id;

		$results = $this->db
		->select(array('p.nom as pole_mere', 'sp.id', 'sp.nom'))
		->from('sous_pole AS sp')
		->join('sous_pole_inactive AS spi', 'sp.id = spi.sous_pole_id', 'left')
		->join('pole AS p', 'sp.pole_mere_id = p.id')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where($where)
		->order_by('p.nom')
		->order_by('sp.nom')
		->get();

		$liste = array();

		foreach ($results->result() as $row)
			$liste[(string)$row->pole_mere][$row->id] = $row->nom;
		

		return $liste;
	}

	/*
	* liste de tous les pôles originaires de demandes interservices dans un select avec optGroup, pour un pole mère défini (ou un array des pole)
	*/
	public function liste_sous_poles_origine_avec_opt_group_par_pole($pole_id) {
		$results = $this->db
		->select(array('p.nom as pole_mere', 'sp.nom'))
		->from('sous_pole AS sp')
		->join('sous_pole_inactive AS spi', 'sp.id = spi.sous_pole_id', 'left')
		->join('pole AS p', 'sp.pole_mere_id = p.id')
		->join('pole_inactive AS pi', 'p.id = pi.pole_id', 'left')
		->where(array('spi.sous_pole_id' => NULL, 'pi.pole_id' => NULL))
		->where_in('p.id', is_array($pole_id) ? $pole_id : array(0))
		->order_by('p.nom')
		->order_by('sp.nom')
		->get();

		$liste = array();

		foreach ($results->result() as $row) {
			$pole_mere;
			foreach ($row as $key => $value) {
				if ($key == 'pole_mere' && array_key_exists($value, $liste) == FALSE) {
					$liste[(string)$value] = array();
					$pole_mere = $value;
				} elseif ($key == 'nom')
					$liste[$pole_mere][(string)$value] = $value;
			}
		}

		return $liste;
	}

	/*
	* construction du tableau des sous-poles pour leur gestion
	*/
	public function tableau_sous_poles($responsable_id) {
		// lien pour modifier le sous pole
		$lienUpdateSousPoleDebut = $this->lien_model->update_sous_pole_debut();
		$lienUpdateSousPoleFin = $this->lien_model->update_sous_pole_fin();
		// lien pour rendre inactif le sous pole
		$lienDeleteSousPoleDebut = $this->lien_model->delete_sous_pole_debut();
		$lienDeleteSousPoleFin = $this->lien_model->delete_sous_pole_fin();
		// génère le tableau des poles actifs
		$select = array('CONCAT(\''.$lienUpdateSousPoleDebut.'\',sp.id,\''.$lienUpdateSousPoleFin.'\') AS Modifier', 'CONCAT(\'<strong style="color:\',sp.couleur,\';">\',sp.nom,\'</strong>\')', 'p.nom AS pole_mere', 'CONCAT(\''.$lienDeleteSousPoleDebut.'\',sp.id,\''.$lienDeleteSousPoleFin.'\') AS Supprimer');

		$this->db->select($select, FALSE);
		$this->db->from('sous_pole AS sp');
		$this->db->join('sous_pole_inactive AS spi', 'sp.id = spi.sous_pole_id', 'left');
		$this->db->join('pole AS p', 'sp.pole_mere_id = p.id');
		$this->db->where(array('spi.sous_pole_id' => NULL, 'p.responsable_id' => $responsable_id));
		$this->db->order_by('pole_mere,sp.nom');
		$query = $this->db->get();

		return $query;
	}

	/*
	* construction du tableau de détail du sous-poles
	*/
	public function tableau_detail_sous_pole($sous_pole_id, $responsable_id) {
		$select = array('sp.nom', 'p.nom AS pole_mere', 'CONCAT(\'<strong style="color:\',sp.couleur,\';">\',sp.couleur,\'</strong>\')');
		$where = array('spi.sous_pole_id' => NULL, 'sp.id' => $sous_pole_id, 'p.responsable_id' => $responsable_id);

		$this->db->select($select, FALSE);
		$this->db->from('sous_pole AS sp');
		$this->db->join('sous_pole_inactive AS spi', 'sp.id = spi.sous_pole_id', 'left');
		$this->db->join('pole AS p', 'sp.pole_mere_id = p.id');
		$this->db->where($where);
		$query = $this->db->get();

		return $query;
	}

	/*
	* gives the sous-poles details
	* the 2nd parameter is an option to avoid giving the data to a responsable for a pole he does not manage
	*/
	public function detail_sous_pole($sous_pole_id, $responsable_id = '') {
		$select = array('sp.nom', 'p.nom AS pole_mere', 'p.id AS pole_mere_id', 'p.confidentialite AS pole_mere_confidentialite', 'sp.couleur');
		$where = array('spi.sous_pole_id' => NULL, 'sp.id' => $sous_pole_id);

		if ( ! empty($responsable_id) ) // to prevent any other responsable to get the data if he's not the pole's responsable
			$where['p.responsable_id'] = $responsable_id;

		$this->db->select($select, FALSE);
		$this->db->from('sous_pole AS sp');
		$this->db->join('sous_pole_inactive AS spi', 'sp.id = spi.sous_pole_id', 'left');
		$this->db->join('pole AS p', 'sp.pole_mere_id = p.id');
		$this->db->where($where);
		$query = $this->db->get();

		return $query;
	}

	/*
	* query to check if the sous-pole belongs to the given pole
	*/
	public function form_validation_coincide_pole($sous_pole_id, $pole_id)
	{
		return $this->db->limit(1)
		->from('sous_pole as sp')
		->join('pole AS p', 'p.id = sp.pole_mere_id')
		->where(array('sp.id' => $sous_pole_id, 'p.id' => $pole_id))
		->get()
		->num_rows() === 1;
	}

}