<?php

	error_reporting(0);

	/*
	 * Routines to return very simple responses.
	 */
	function render_json($json) {
		header('Content-Type: application/json');
		die(json_encode($json));
	}
	
	function return_internal_server_error() {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
		header('Content-Type: text/plain');
		die('Internal Server Error');
	}

	function return_bad_request() {
		header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
		header('Content-Type: text/plain');
		die('Bad Request');
	}

	function fever_relative_path($path) {
		if (substr(FEVER_ROOT, strlen(FEVER_ROOT)-1, 1) == '/') {
			return FEVER_ROOT . $path;
		} else {
			return FEVER_ROOT . "/" . $path;
		}
	}
	
	/*
	 * Include the config file. Verify that the FEVER_ROOT configuration looks 
	 * correct.
	 */
	include("config.php");

	if (!file_exists(FEVER_ROOT)) {
		error_log('Invalid FEVER_ROOT configuration.');
		return_internal_server_error();
	}

	/*
	 * Include relevant parts of Fever and create our Fever instance. The order of 
	 * operations is important to avoid unnecessary warning messages in the log file.
	 */
	define('FIREWALL_ROOT', fever_relative_path("firewall/"));
	
	include(fever_relative_path("firewall/config/db.php"));
	include(fever_relative_path("firewall/app/libs/SIDB423.php"));
	include(fever_relative_path("firewall/app/libs/fever.php"));
	include(fever_relative_path("firewall/app/libs/util.php"));

	$adapter_fever_instance = new Fever();

	include(fever_relative_path("firewall/app/libs/request.php"));

	include("authlock.php");
	include("login.php");
	include("subscription.php");

	/*
	 * Functions to handle incoming requests.
	 */
	define('__ADAPTER_API_VERSION__', 1);

	function verify_url() {
		render_json(array('adapter_api_version' => __ADAPTER_API_VERSION__));
	}
	
	// Returns true if the request is authenticated based on the incoming email address and
	// password. Temporarily disables access when more than 6 subsequent requests are received with the 
	// correct email address but incorrect password in the past 24 hours.
	function is_user_authenticated($post_obj) {
		
		$submitted_email = null;
		$submitted_password = null;
		
		$auth_lock = new AuthLock();
		
		// If the account is locked based on the number of consecutive failed login attempts
		// within the past 24 hours, record another failed login attempt and return false.
		if ($auth_lock->is_locked()) {
			// Record another failed login attempt.
			$auth_lock->record_failed();
			return false;
		}
		
		if ((isset($post_obj['auth'])) && (is_array($post_obj['auth']))) {
			$auth = $post_obj['auth'];
			if ((isset($auth['email'])) && (is_string($auth['email']))) {
				$submitted_email = $auth['email'];
			}
			if ((isset($auth['password'])) && (is_string($auth['password']))) {
				$submitted_password = $auth['password'];
			}
		}
		
		if ((!$submitted_email) || (!$submitted_password)) {
			return false;
		}
		
		$login = Login::get();
		if (!$login) {
			error_log('Unable to retrieve login credentials.');
			return_internal_server_error();
		}
	
		if (strtolower($login->email) == strtolower($submitted_email)) {
			if ($login->password == $submitted_password) {
				$auth_lock->record_success();
				return true;
			} else {
				// Record a failed login attempt if the email address was right but the 
				// password was wrong.
				$auth_lock->record_failed();
			}
		}
		return false;
	}

	// Authenticate the user.
	function authenticate_user($post_obj, &$response_obj) {
		if (is_user_authenticated($post_obj)) {
			$response_obj['authenticated'] = true;
		} else {
			$bad_auth = array('authenticated' => false);
			render_json($bad_auth);
		}
	}
	
	/*
	 * Functions to manage subscriptions.
	 */
	function add_subscription($feed, &$response_obj) {
		if ((!is_array($feed)) || (!isset($feed['url'])) || (!is_string($feed['url']))) {
			return_bad_request();
		}
		$feed_url = $feed['url'];
		$existing_id = Subscription::subscription_id_for_feed_url($feed_url);
		if ($existing_id) {
			$response_obj["response"] = $existing_id;
			return;
		}
		$subscription_id = Subscription::add_subscription_with_feed_url($feed_url); 
		if ($subscription_id) {
			$response_obj["response"] = $subscription_id;
		} else {
			error_log('Unable to add subscription.');
			return_internal_server_error();
		}
	}
	
	function remove_subscription($feed, &$response_obj) {
		if ((!is_array($feed)) || (!isset($feed['id'])) || (!is_int($feed['id']))) {
			return_bad_request();
		}
		$feed_id = $feed['id'];
		if (Subscription::delete_feed_with_feed_id($feed_id)) {
			$response_obj["response"] = true;
		} else {
			error_log('Unable to remove subscription.');
			return_internal_server_error();
		}
	}
	
	function list_subscriptions(&$response_obj) {
		$subscriptionList = Subscription::get_all();
		$result = [];
		foreach ($subscriptionList as &$subscription) {
			$result[] = $subscription->to_json();
		}
		$response_obj['response'] = $result;
	}
	
	/*
	 * Prohibit requests that do not use HTTPS.
	 */
	if(!isset($_SERVER['HTTPS'])) {
		return_bad_request();
	}
	
	/*
	 * Dispatch the request.
	 */
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$json = file_get_contents('php://input');
		$post_obj = json_decode($json, true);

		$operation = '';
		if ((isset($post_obj['operation'])) && (is_string($post_obj['operation']))) {
			$operation = $post_obj['operation'];
		}
		
		$response = array();
		authenticate_user($post_obj, $response);
		
		if ($operation == 'list_subscriptions') {
			list_subscriptions($response);
		} elseif ($operation == 'add_subscription') {
			if (!isset($post_obj['input'])) {
				return_bad_request();
			}
			add_subscription($post_obj['input'], $response);
		} elseif ($operation == 'remove_subscription') {
			if (!isset($post_obj['input'])) {
				return_bad_request();
			}
			remove_subscription($post_obj['input'], $response);
		} else if ($operation != '') {
			return_bad_request();
		}
		
		render_json($response);

	} else {
	
		verify_url();

	}

?>
