<?php

/*!
 * Fetchwitter Class
 *
 * PHP framework to fetch tweets from Twitter API v1.1 using OAuth authentication/authorization.
 * Fetchwitter provides easy to use methods for tweets/hashtags search & user timeline feed.
 *
 * @author: hello@jabran.me
 * @version: 1.0.5
 * @license: MIT License
 *
 * License: MIT License
 *
 * Copyright (c) 2014 Jabran Rafique
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software 
 * and associated documentation files (the "Software"), to deal in the Software without restriction, 
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, 
 * subject to the following conditions: The above copyright notice and this permission notice shall be included 
 * in all copies or substantial portions of the Software. THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY 
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS 
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
 * FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */


/**
 * Fetchwitter class
 */

class Fetchwitter {


	/**
	 * Setup the class variables
	 */
	
	private $_api_key,
			$_api_secret,
			$_access_token,
			$_api_version,
			$_api_url,
			$_app_oauth_endpoint,
			$_api_endpoint,
			$_count;


	/**
	 * Setup class constructor method
	 *
	 * @param: Array $config takes set of 3 parameters (String api_key, String api_secret, Integer count)
	 * @return: Exception on missing/misconfigured paramters
	 */

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


	/**
	 * Method to setup an access token manually
	 * @param: String $access_token
	 */

	public function set_access_token( $access_token ) {
		return $this->_access_token = $access_token;
	}


	/**
	 * Method to get current set access token
	 * @return: String
	 */
	
	public function get_access_token() {
		return $this->_access_token;
	}


	/**
	 * Method to request a fresh access token from Twitter API
	 * @return: Boolean
	 */
	
	public function get_new_access_token() {
		return $this->_get_access_token();
	}


	/**
	 * Method to get user timeline feed
	 * @param: String $screen_name
	 * @param: Integer $count
	 * @return: Object | Boolean
	 */
	
	public function get_by_user( $screen_name = '@jabranr', $count = 10 ) {
		$this->_count = $count;
		$endpoint = $this->_api_endpoint . '/statuses/user_timeline.json';
		$options = array(
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->_access_token
			),
			CURLOPT_URL => $endpoint . '?screen_name=' . preg_replace('/@/', '', $screen_name) . '&count=' . $this->_count,
			CURLOPT_RETURNTRANSFER => true
		);
		$response = $this->_do_curl( $options );
		if ( $response )
			return $response;
		return false;
	}


	/**
	 * Method to get result of a search
	 * @param: Array $options takes 2 parameters (String q, String result_type)
	 * @return: Object | Boolean
	 */
	
	public function search_tweets( $options = array('q' => '#WebDevelopment', 'result_type' => 'recent') ) {

		// Remove all hash signs
		$options['q'] = preg_replace('/#/', '', $options['q']);

		// Replace with one at the beginning
		$options['q'] = '#' . $options['q'];

		// Setup API endpoint for tweet search
		$endpoint = $this->_api_endpoint . '/search/tweets.json';

		// Setup curl options
		$curl_options = array(
			CURLOPT_HEADER => false,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->_access_token
			),
			CURLOPT_RETURNTRANSFER => true
		);

		// Verify if options array is set
		if ( isset($options) && count($options) > 0 ) {

			// Verify if query parameter is set
			if ( isset($options['q']) && $options['q'] ) {

				// Set the maximum limit for tweets
				if ( ! isset($options['count']) )
					$options['count'] = $this->_count;

				// Build up tweet search query
				$curl_options[CURLOPT_URL] = $endpoint . '?' . http_build_query($options);
			}

			// Setup curl options if query is passed from pagination paramaters
			else if ( isset($options['query']) && $options['query'] ) {
				$curl_options[CURLOPT_URL] = $endpoint . $options['query'];
			}
		}
		
		if ( $response = $this->_do_curl( $curl_options ) )
			return $response;
		return false;
	}


	/**
	 * Method to convert tweet text to formatted tweet
	 * @param: String $text
	 * @return: String
	 */

	public function to_tweet( $text ) {
		return $this->_do_hashtags( $this->_do_mentions( $this->_do_links( $text ) ) );
	}


	/**
	 * Method to get tweet length
	 * @param: $tweet
	 * @return: Integer
	 */

	public function tweet_length( $tweet ) {
		return strlen( $tweet );
	}


	/**
	 * Class magic destruct method
	 * @return: Boolean
	 */
	
	public function __destruct() {
		return true;
	}


	/**
	 * Private method to request access token from Twitter API
	 * @return: String | Exception | Boolean
	 */
	
	private function _get_access_token() {

		// Setup curl options
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

		// Execute curl
		$response = $this->_do_curl( $options );

		// Verify if there is response then decode
		if ( $response ) {
			$response = json_decode($response);

			// Verify if valid token type was recieved
			if ( property_exists($response, 'token_type') && $response->token_type === 'bearer' ) {
				return $this->_access_token = $response->access_token;
			}

			// Otherwise throw Exception with error and message
			else if ( property_exists($response, 'errors') ) {
				throw new Exception($response->errors[0]->code . ': ' . $response->errors[0]->message);
				return;
			}
		}

		// Return false on rest
		return false;
	}


	/**
	 * Private method to use PHP CURL Extension.
	 * Throws exceptions if CURL not found.
	 * @return: String | Mix
	 */
	
	private function _do_curl( $options = array() ) {
		
		if ( ! in_array('curl', get_loaded_extensions()) )
			throw new Exception('CURL extension is required.');

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}


	/**
	 * Private method to format links in a string
	 * @param: String $text
	 * @return: String
	 */

	private function _do_links( $text ) {
		$urlRegEx = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/i";
		if ( preg_match($urlRegEx, $text, $url) ) {
			$text = preg_replace_callback($urlRegEx, function( $matches ) {
				return '<a target="_blank" href="' . $matches[0] . '" rel="nofollow">' . $matches[0] . '</a>';
			}, $text);
		}
		return $text;
	}


	/**
	 * Private method to format @mentions in a string
	 * @param: String $text
	 * @return: String
	 */

	private function _do_mentions( $text ) {
		$mentionRegEx = "/@([A-Z0-9_])+/i";
		if ( preg_match($mentionRegEx, $text, $mention) ) {
			$text = preg_replace_callback($mentionRegEx, function( $matches ) {
				return '<a target="_blank" href="https://twitter.com/' . substr($matches[0], 1) . '" rel="nofollow">' . $matches[0] . '</a>';
			}, $text);
		}
		return $text;
	}


	/**
	 * Private method to format #hashtags in a string
	 * @param: String $text
	 * @return: String
	 */

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