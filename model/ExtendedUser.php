<?php
/* This is a sample when you want to modify User Object. 
 * Instead of changing the user object, you can extends it and add / modify any functions. 
 */
class ExtendedUser extends User {
	private $_address = null;
	private $_phone = null;
	
	public function __construct($id = 0, $login = null, $email = null, $fname = null, $lname = null, 
							    $address = null, $phone = null) {
		parent::__construct($id, $login, $email, $fname, $lname);
		$this->_address = $address;
		$this->_phone = $phone;
	}
	
	public function setAddress($address) {
		$this->_address = $address;
	}
	
	public function setPhone($phone) {
		$this->_phone = $phone;
	}
	
	public function getAddress() {
		return $this->_address;
	}
	
	public function getPhone() {
		return $this->_phone;
	}	
}
?>