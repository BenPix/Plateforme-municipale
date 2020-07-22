<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Pagination_model extends CI_Model
{

	public function creer_ma_pagination($total_rows, $limit_per_page = 9999)
	{
		$this->load->library('pagination');

		$config['base_url'] = site_url().'/'.$this->uri->segment(1).'/'.$this->uri->segment(2);
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $limit_per_page;
		$config["uri_segment"] = 3;
		$config['use_page_numbers'] = TRUE;
		$config['num_links'] = 2;
		$config['full_tag_open'] = '<div style="font-size:20px; text-align:center;">';
		$config['full_tag_close'] = '</div>';
		$config['first_link'] = 'Première Page&nbsp;';
		$config['last_link'] = 'Dernière Page';
		$config['next_link'] = '&gt;&nbsp;';
		$config['prev_link'] = '&lt;&nbsp;';
		$config['num_tag_open'] = '<span>';
		$config['num_tag_close'] = '</span>&nbsp;';
		$config['cur_tag_open'] = '<b>';
		$config['cur_tag_close'] = '</b>&nbsp;';

		$this->pagination->initialize($config);

		return $this->pagination->create_links();
	}

}