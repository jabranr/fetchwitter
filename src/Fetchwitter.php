<?php

/**
 * Fetchwitter Class
 * @author: hello@jabran.me
 * @version: 1.0.2
 *
 */

class Fetchwitter {

	private $_api_key,
			$_api_secret,
			$_access_token,
			$_api_version,
			$_api_url,
			$_app_oauth_endpoint,
			$_api_endpoint,
			$_count;

	public function __construct( $config = array() ) {
		
		if ( !isset( $config ) || ! count( $config ) )
			throw new Exception('Error 100: Fetchwitter is not properly configured.');

		if ( ! isset($config['api_key']) || empty($config['api_key']) )
			throw new Exception('Error 101: A valid Twitter API key is required.');

		if ( ! isset($config['api_secret']) || empty($config['api_secret']) )
			throw new Exception('Error 102: A valid Twitter API secret is required.');

		$this->_count = (isset($config['count']) && $config['count']) ? (int)$config['count'] : 10;

		$this->_api_key = $config['api_key'];
		$this->_api_secret = $config['api_secret'];
		$this->_api_version = '/1.1';
		$this->_api_url = 'https://api.twitter.com';
		$this->_app_oauth_endpoint = '/oauth2/token';
		$this->_api_endpoint = $this->_api_url . $this->_api_version;
	}

	public function set_access_token( $access_token ) {
		return $this->_access_token = $access_token;
	}

	public function get_access_token() {
		return $this->_access_token;
	}

	public function get_new_access_token() {
		return $this->_get_access_token();
	}

	public function get_by_user( $screen_name = 'jabranr', $count = 10 ) {
		$this->_count = $count;
		$endpoint = $this->_api_endpoint . '/statuses/user_timeline.json';
		$options = array(
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->_access_token
			),
			CURLOPT_URL => $endpoint . '?screen_name=' . $screen_name . '&count=' . $this->_count,
			CURLOPT_RETURNTRANSFER => true
		);
		$response = $this->_do_curl( $options );
		if ( $response )
			return $response;
		return false;
	}

	public function search_tweets( $options = array('q' => '#WebDevelopment', 'result_type' => 'recent') ) {
		$endpoint = $this->_api_endpoint . '/search/tweets.json';
		$curl_options = array(
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->_access_token
			),
			CURLOPT_RETURNTRANSFER => true
		);
		if ( isset($options) && count($options) > 0 ) {
			if ( isset($options['q']) && $options['q'] ) {
				if ( ! isset($options['count']) )
					$options['count'] = $this->_count;
				$curl_options[CURLOPT_URL] = $endpoint . '?' . http_build_query($options);
			}
			else if ( isset($options['query']) && $options['query'] ) {
				$curl_options[CURLOPT_URL] = $endpoint . $options['query'];
			}
		}
		
		if ( $response = $this->_do_curl( $curl_options ) )
			return $response;
		return false;
	}

	public function to_tweet( $text ) {
		return $this->_do_hashtags( $this->_do_mentions( $this->_do_links( $text ) ) );
	}

	public function tweet_length( $tweet ) {
		return strlen( $tweet );
	}
	
	public function __destruct() {
		return true;
	}

	/**
	 * Private functions in this class
	 */

	private function _get_access_token() {
		$options = array(
			CURLOPT_POSTFIELDS => array(
				'grant_type' => 'client_credentials'
			),
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic ' . base64_encode( $this->_api_key . ':' . $this->_api_secret ) 
			),
			CURLOPT_HEADER => false,
			CURLOPT_URL => $this->_api_url . $this->_app_oauth_endpoint,
			CURLOPT_RETURNTRANSFER => true
		);
		$response = $this->_do_curl( $options );
		if ( $response ) {
			$response = json_decode($response);

			if ( property_exists($response, 'token_type') && $response->token_type === 'bearer' ) {
				return $this->_access_token = $response->access_token;
			}
			else if ( property_exists($response, 'errors') ) {
				throw new Exception($response->errors[0]->code . ': ' . $response->errors[0]->message);
				return;
			}
		}
		return false;
	}

	private function _do_curl( $options = array() ) {
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	private function _do_links( $text ) {
		$urlRegEx = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/i";
		if ( preg_match($urlRegEx, $text, $url) ) {
			$text = preg_replace_callback($urlRegEx, function( $matches ) {
				return '<a target="_blank" href="' . $matches[0] . '" rel="nofollow">' . $matches[0] . '</a>';
			}, $text);
		}
		return $text;
	}

	private function _do_mentions( $text ) {
		$mentionRegEx = "/@([A-Z0-9_])+/i";
		if ( preg_match($mentionRegEx, $text, $mention) ) {
			$text = preg_replace_callback($mentionRegEx, function( $matches ) {
				return '<a target="_blank" href="https://twitter.com/' . substr($matches[0], 1) . '" rel="nofollow">' . $matches[0] . '</a>';
			}, $text);
		}
		return $text;
	}

	private function _do_hashtags( $text ) {
		$hashtagRegEx = "/#([A-Z0-9_])+/i";
		if ( preg_match($hashtagRegEx, $text, $hashtags) ) {
			$text = preg_replace_callback($hashtagRegEx, function( $matches ) {
				return '<a target="_blank" href="https://twitter.com/search?q=' . urlencode($matches[0]) . '" rel="nofollow">' . $matches[0] . '</a>';
			}, $text);
		}
		return $text;
	}
}