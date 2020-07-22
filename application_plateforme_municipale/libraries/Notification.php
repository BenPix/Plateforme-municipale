<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Notification
{

	protected $_id;
	protected $_source;
	protected $_recipients = array();

	public function get_id() {
		return $this->_id;
	}

	public function set_id(int $id) {
		$this->_id = $id;
	}

	public function get_source() {
		return $this->_source;
	}

	public function set_source(Source $source) {
		$this->_source = $source;
	}

	public function get_recipients() {
		return $this->_recipients;
	}

	public function add_recipients(Notification_recipient $recipient) {
		array_push($this->_recipients, $recipient);
	}

	
}

class Source
{

	protected $_id;
	protected $_categorie_id;
	protected $_categorie_name;
	protected $_type_id;
	protected $_type_name;
	protected $_title = '';
	protected $_link = '';
	protected $_content = array();

	public function __construct(int $id, int $categorie_id, string $categorie_name, int $type_id, string $type_name)
	{
		$this->_id = $id;
		$this->_categorie_id = $categorie_id;
		$this->_categorie_name = $categorie_name;
		$this->_type_id = $type_id;
		$this->_type_name = $type_name;
	}

	public function get_id() {
		return $this->_id;
	}

	public function set_id(int $id) {
		$this->_id = $id;
	}

	public function get_categorie_id() {
		return $this->_categorie_id;
	}

	public function set_categorie_id(int $categorie_id) {
		$this->_categorie_id = $categorie_id;
	}

	public function get_categorie_name() {
		return $this->_categorie_name;
	}

	public function set_categorie_name(string $categorie_name) {
		$this->_categorie_name = $categorie_name;
	}

	public function get_type_id() {
		return $this->_type_id;
	}

	public function set_type_id(int $type_id) {
		$this->_type_id = $type_id;
	}

	public function get_type_name() {
		return $this->_type_name;
	}

	public function set_type_name(string $type_name) {
		$this->_type_name = $type_name;
	}

	public function get_title() {
		return $this->_title;
	}

	public function set_title(string $title) {
		$this->_title = $title;
	}

	public function get_link() {
		return $this->_link;
	}

	public function set_link(string $link) {
		$this->_link = $link;
	}

	public function get_content() {
		return $this->_content;
	}

	public function set_content(array $content) {
		$this->_content = $content;
	}

	public function add_content(string $content) {
		array_push($this->_content, $content);
	}

	
}

class Notification_recipient
{

	protected $_user_id;
	protected $_marked_as_read = FALSE;
	protected $_date = NULL;

	public function __construct($user_id = 0)
	{
		$this->_user_id = $user_id;
	}

	public function get_user_id() {
		return $this->_user_id;
	}

	public function set_id(int $user_id) {
		$this->_user_id = $user_id;
	}

	public function get_marked_as_read() {
		return $this->_marked_as_read;
	}

	public function set_marked_as_read(bool $marked_as_read) {
		$this->_marked_as_read = $marked_as_read;
	}

	public function get_date() {
		return $this->_date;
	}

	public function set_date(DateTime $date) {
		$this->_date = $date;
	}

	
}

/* End of file Notification.php */
/* Location: ./application_sains_en_gohelle.xyz/library/Notification.php */