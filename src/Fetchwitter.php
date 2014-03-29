<?php

/**
 * Fetchwitter Class document
 * @author: hello@jabran.me
 * @version: 1.0.0
 *
 */

class Fetchwitter {

	private $_api_key, $_api_secret, $_access_token;

	public function __construct( $config = array() ) {
		
		if ( !isset( $config ) || ! count( $config ) )
			throw new Exception('Error 100: Configurations not found.');

		if ( ! isset($config['api_key']) )
			throw new Exception('Error 101: Twitter API key cannot be found.');

		if ( ! isset($config['api_secret']) )
			throw new Exception('Error 102: Twitter API secret cannot be found.');

		$this->_api_key = $config['api_key'];
		$this->_api_secret = $config['api_secret'];
	}

	public function get_user_tweets($screen_name) {}

	public function get_hashtag_tweets($hashtag) {}

	public function search_tweets($query) {}

	public function to_tweet($text) {}

	public function get_mentions($tweet) {}

	public function get_links($tweet) {}

	public function get_media($tweet) {}

	private function get_access_token() {}

	private function set_access_token() {
		if (empty($this->_access_token)) 
			return $this->_access_token = $this->get_access_token();
		return true;
	}

	public function __destruct() {
		return true;
	}

}