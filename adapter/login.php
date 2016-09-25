<?php

// The username and password of the authorized user.

class Login {
	
	var $email;
	var $password;
	
	function __construct($in_email, $in_password) {
		$this->email = $in_email;
		$this->password = $in_password;
	}

	static function get() {
		global $adapter_fever_instance;
		$cfg = $adapter_fever_instance->cfg;
		$email = $cfg['email'];
		$password = $cfg['password'];
		if (($email) && ($password)) {
			return new Login($email, $password);
		}
		return null;
	}
	
}

?>