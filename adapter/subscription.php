<?php

/*
	The Model object for a subscription to a feed.
*/

class Subscription {

	var $id;
	var $title;
	var $url;
	
	function __construct($in_id, $in_title, $in_url) {
		$this->id = $in_id;
		$this->title = $in_title;
		$this->url = $in_url;
	}
	
	function to_json() {
		$result = array();
		if ($this->id) {
			$result['id'] = $this->id;
		}
		if ($this->title) {
			$result['title'] = $this->title;
		}
		if ($this->url) {
			$result['url'] = $this->url;
		}
		return $result;
	}

	static function get_all() {
		global $adapter_fever_instance;
		$result = array();
		$feed_results = $adapter_fever_instance->get_all('feeds');
		foreach( $feed_results as &$feed_result ) {
			$result[] = new Subscription((int) $feed_result['id'], $feed_result['title'], $feed_result['url']);
		}
		return $result;
	}
	
	static function subscription_id_for_feed_url($feed_url) {
		global $adapter_fever_instance;
		$url_checksum = checksum(normalize_url($feed_url));
		$feed = $adapter_fever_instance->get_one('feeds', $adapter_fever_instance->prepare_sql('`url_checksum` = ?', $url_checksum));
		if ($feed) {
			return (int) $feed['id'];
		}
		return null;
	}
	
	static function add_subscription_with_feed_url($feed_url) {
		global $adapter_fever_instance;
		$feed_id = (int) $adapter_fever_instance->add_feed(array('url' => $feed_url));
		$adapter_fever_instance->refresh_feed($feed_id);
		$feed = $adapter_fever_instance->get_one('feeds', $adapter_fever_instance->prepare_sql('`id` = ?', $feed_id));
		$adapter_fever_instance->cache_favicon($feed);
		return $feed_id;
	}

	static function delete_feed_with_feed_id($feed_id) {
		global $adapter_fever_instance;
		$adapter_fever_instance->delete_feed($feed_id);
		return true;
	}
	
}

?>
