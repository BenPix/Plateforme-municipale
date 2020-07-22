<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Relance_demande_interservices_query_model extends MY_Model
{

	protected $table = 'relance_demande_interservices';
	protected $cle = 'demande_interservices_id';
	protected $data_non_echappees = array('date_relance');

}