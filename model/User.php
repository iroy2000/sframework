<?php
class User {
	protected $_id = 0; 
	protected $_login = null; 
	protected $_email = null; 
	protected $_fname = null; 
	protected $_lname = null;
	
	// table name
	public static $userId = 'user_id';
	public static $login  = 'username';
	public static $email  = 'user_email';
	
	// Constructor
	public function __construct($id = 0, $login = null, $email = null, $fname = null, $lname = null) {
		$this->_id = $id;
		$this->_login = $login;
		$this->_email= $email;
		$this->_fname = $fname;
		$this->_lname = $lname;
	}
	
	public static function createUser($array) {
		return new User($array[User::$userId], $array[User::$login], $array[User::$email]);
	}
	
	// Setter Methods
	public function setId($id) {
		$this->_id = $id;
	}
	
	public function setLogin($login) {
		$this->_login = $login;
	}
	
	public function setEmail($email) {
		$this->_email= $email;
	}
	
	public function setFirstName($fname) {
		$this->_fname = $fname;
	}
	
	public function setLastName($lname) {
		$this->_lname = $lname;
	}
	
	// Getter Methods
	public function getId() {
		return $this->_id;
	}
	
	public function getLogin() {
		return $this->_login;
	}
	
	public function getEmail() {
		return $this->_email;
	}
	
	public function getFirstName() {
		return $this->_fname;
	}
	
	public function getLastName() {
		return $this->_lname;
	}	
}
?>