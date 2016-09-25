<?php

// Temporarily disables access when more than 6 subsequent requests are received with the 
// correct email address but incorrect password in the past 24 hours.

define('MAX_FAILED_LOGINS', 6);
define('TIME_FRAME_SECONDS', 86400);

define('AUTHLOG_CONFIG_NAME', 'com.goldenhillsoftware.failed_login_attempt_timestamps');

class AuthLock {

	var $failed_login_attempt_timestamps;

	function __construct() {
		global $adapter_fever_instance;
		$this->failed_login_attempt_timestamps = $adapter_fever_instance->cfg[AUTHLOG_CONFIG_NAME];
		if ($this->failed_login_attempt_timestamps == null) {
			$this->failed_login_attempt_timestamps = array();
		}
	}
	
	function is_locked() {
		if (defined('DISABLE_AUTH_LOCK')) {
			if (DISABLE_AUTH_LOCK == true) {
				return false;
			}
		}
		return ((count($this->failed_login_attempt_timestamps) >= MAX_FAILED_LOGINS) 
			&& ($this->failed_login_attempt_timestamps[0] > time() - TIME_FRAME_SECONDS));
	}
	
	function record_failed() {
		$this->failed_login_attempt_timestamps[] = time();
		if (count($this->failed_login_attempt_timestamps) > MAX_FAILED_LOGINS) {
			$this->failed_login_attempt_timestamps = array_slice($this->failed_login_attempt_timestamps, count($this->failed_login_attempt_timestamps) - MAX_FAILED_LOGINS);
		}
		$this->save();
	}

	function record_success() {
		$this->failed_login_attempt_timestamps = array();
		$this->save();
	}

	private function save() {
		global $adapter_fever_instance;
		$adapter_fever_instance->cfg[AUTHLOG_CONFIG_NAME] = $this->failed_login_attempt_timestamps;
		$adapter_fever_instance->save();
	}

}
	
?>