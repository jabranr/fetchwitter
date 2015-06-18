<?php

/**
 * Fetchwitter Class
 *
 * PHP library to fetch tweets from Twitter API v1.1 using OAuth authentication/authorization.
 * Fetchwitter provides easy to use methods for basic functionality such as tweets or hashtag
 * search, or fetch a user timeline feed.
 *
 * @author: Jabran Rafique <hello@jabran.me>
 * @version: 1.0.8
 * @license: MIT License
 *
 * License: MIT License
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

class Fetchwitter {

	const API_VERSION = '1.1';
	const TWITTER_URI = 'https://twitter.com';
	const API_URI = 'https://api.twitter.com';
	const OAUTH_ENDPOINT = '/oauth2/token';
	const USERAGENT = 'Fetchwitter (+https://github.com/jabranr/fetchwitter)';
	const URL_REGEX = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/i';
	const MENTION_REGEX = '/@([A-Z0-9_])+/i';
	const HASHTAG_REGEX = '/#([A-Z0-9_])+/i';

	/** @var string $key Twitter API app key */
	protected $key;

	/** @var string $secret Twitter API app secret */
	protected $secret;

	/** @var string $accessToken Twitter API app access token */
	protected $accessToken;

	/** @var string $_api_endpoint Twitter API endpoint */
	protected $endpoint;

	/** @var int $httpCode HTTP code */
	protected $httpCode;

	/** @var float $requestTime CURL transfer total time */
	protected $requestTime;

	/** @var array $headers Response headers */
	protected $headers;

	/** @var mixed $data Response body */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param array|string
	 * @throws Exception
	 */
	public function __construct() {
		$args = func_get_args();

		if ( ! count($args) ) {
			throw new Exception('Fetchwitter is not properly configured.');
		}

		if ( is_array($args[0]) ) {
			$args = $args[0];
		}

		if ( count($args) < 2 ) {
			throw new Exception('A required argument is missing.');
		}

		return $this->_setCredential($args);
	}

	/**
	 * Setup credential
	 *
	 * @param array $config
	 * @return class|object
	 */
	private function _setCredential($config) {

		if (array_key_exists('api_key', $config)) {
			$this->setKey($config['api_key']);
		}
		else {
			$this->setKey($config[0]);
		}

		if (array_key_exists('api_secret', $config)) {
			$this->setSecret($config['api_secret']);
		}
		else {
			$this->setSecret($config[1]);
		}

		if ( count($config) > 2 ) {
			if (array_key_exists('access_token', $config)) {
				$this->setAccessToken($config['access_token']);
			}
			else {
				$this->setAccessToken($config[2]);
			}
			$this->setHttpCode(200);
		}
		else {
			$this->setHttpCode(400);
			$this->setAccessToken(null);
		}

		$this->setBody(null);

		if ( null === $this->getAccessToken() )
			return $this->_refreshAccessToken();
		return $this;
	}

	/**
	 * Set API key
	 * @param string $key
	 * @return class|object
	 */
	public function setKey($key) {
		$this->key = $key;
		return $this;
	}

	/**
	 * Get API key
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Set API secret
	 * @param string $secret
	 * @return class|object
	 */
	public function setSecret($secret) {
		$this->secret = $secret;
		return $this;
	}

	/**
	 * Get API secret
	 * @return string
	 */
	public function getSecret() {
		return $this->secret;
	}

	/**
	 * Set an access token
	 *
	 * @param string $accessToken
	 * @return class|object
	 */
	public function setAccessToken( $accessToken ) {
		$this->accessToken = $accessToken;
		return $this;
	}

	/**
	 * Get the access token
	 *
	 * @return string
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}

	/**
	 * Get the HTTP code
	 *
	 * @return int
	 */
	public function getHttpCode() {
		return $this->httpCode;
	}

	/**
	 * Set the HTTP code
	 *
	 * @param int $httpCode
	 * @return class|object
	 */
	public function sethttpCode($httpCode) {
		$this->httpCode = (int) $httpCode;
		return $this;
	}

	/**
	 * Get request total time
	 *
	 * @return float
	 */
	public function getRequestTime() {
		return $this->requestTime;
	}

	/**
	 * Set request total time
	 *
	 * @param float $requestTime
	 * @return class|object
	 */
	public function setRequestTime($requestTime) {
		$this->requestTime = (float) $requestTime;
		return $this;
	}

	/**
	 * Get endpoint
	 *
	 * @return string
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * Set an endpoint
	 *
	 * @param string $endpoint
	 * @return class|object
	 */
	public function setEndpoint($endpoint) {
		$this->endpoint = (string) $endpoint;
		return $this;
	}

	/**
	 * Get the response headers
	 *
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Set the response headers
	 *
	 * @param array $responseHeaders
	 * @return class|object
	 */
	public function setHeaders($responseHeaders) {
		$this->headers = (array) $responseHeaders;
		return $this;
	}

	/**
	 * Get the response body
	 *
	 * @return array
	 */
	public function getBody() {
		return $this->data;
	}

	/**
	 * Set the response body
	 *
	 * @param array $responseHeaders
	 * @return class|object
	 */
	public function setBody($responseBody) {
		$this->data = $responseBody;
		return $this;
	}

	/**
	 * Get the oauth endpoint
	 *
	 * @return string
	 */
	public function getOAuthEndpoint() {
		return sprintf("%s%s", self::API_URI, self::OAUTH_ENDPOINT);
	}

	/**
	 * Get the API endpoint
	 *
	 * @return string
	 */
	public function getApiEndpoint() {
		if ( ! empty($this->getEndpoint()) ) {
			return sprintf("%s/%s/%s", self::API_URI, self::API_VERSION, $this->getEndpoint());
		}
		return sprintf("%s/%s", self::API_URI, self::API_VERSION);
	}

	/**
	 * Parse given array of headers
	 *
	 * @param string $header
	 * @return array
	 */
	public function parseHeaders($header) {
		$headers = array();
		foreach (explode('\r\n', $header) as $line) {
			if (strpos($line, ':') !== false)	{
				list($key, $value) = explode(':', $line);
				$key = str_replace('-', '_', $key);
				$headers[$key] = trim($value);
			}
		}
		return $headers;
	}


	/**
	 * CURL helper method
	 *
	 * @param string $url
	 * @param string $method
	 * @param string $auth
	 * @param array $postfields
	 */
	private function _makeRequest( $url, $method, $auth, $postfields ) {
		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_TIMEOUT => 5,
			CURLOPT_ENCODING => 'gzip',
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => self::USERAGENT,
			CURLOPT_HTTPHEADER => array('Accept: application/json', $auth, 'Expect:')
		);

		switch ($method) {
			case 'GET':
				if ( !empty($postfields) ) {
					$options[CURLOPT_URL] .= '?' . $this->toHttpQuery($postfields);
				}
				break;

			case 'POST':
				$options[CURLOPT_POST] = true;
				$options[CURLOPT_POSTFIELDS] = $this->toHttpQuery($postfields);
				break;

			case 'DELETE';
				$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				if ( !empty($postfields) ) {
					$options[CURLOPT_URL] .= '?' . $this->toHttpQuery($postfields);
				}
				break;

			case 'PUT';
				$options[CURLOPT_CUSTOMREQUEST] = 'PUT';
				if ( !empty($postfields) ) {
					$options[CURLOPT_URL] .= '?' . $this->toHttpQuery($postfields);
				}
				break;
		}

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);

		if ( curl_error($ch) > 0 ) {
			throw new Exception(curl_error($ch), curl_errno($ch));
		}

		$this->setHttpCode(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		$this->setRequestTime(curl_getinfo($ch, CURLINFO_TOTAL_TIME));

		$parts = explode('\r\n\r\n', $response);
		$responseBody = array_pop($parts);

		if ( $this->getHttpCode() === 200 ) {
			$responseBody = $this->setBody($this->parseResponseBody($responseBody));
		}

		$responseHeader = array_pop($parts);
		$this->setHeaders($this->parseHeaders($responseHeader));

		curl_close($ch);
		return $this;
	}

	/**
	 * Get a fresh access token from Twitter API
	 *
	 * @return boolean
	 */
	private function _refreshAccessToken() {
		$auth = sprintf('Authorization: Basic %s', $this->encodeCredential());
		$params = array('grant_type' => 'client_credentials');
		$response = $this->_makeRequest($this->getOAuthEndpoint(), 'POST', $auth, $params);

		if ($this->getHttpCode() === 200 && array_key_exists('token_type', $this->getBody())) {
			$this->setAccessToken($this->getBody()->access_token);
			$this->setBody(null);
		}

		return $this;
	}

	/**
	 * Parse response and save to response body
	 *
	 * @return class|object
	 */
	public function parseResponseBody($responseBody) {
		if ( ! $responseBody ) return;
		return json_decode($responseBody);
	}

	/**
	 * Convert an array to HTTP query
	 *
	 * @param array $arr
	 * @return string
	 */
	public function toHttpQuery($arr) {
		if ( ! $arr ) return;
		$keys = $this->rfcUrlencode(array_keys($arr));
		$values = $this->rfcUrlencode(array_values($arr));

		$arr = array_combine($keys, $values);
		uksort($arr, 'strcmp');
		$params = array();
		foreach ($arr as $key => $value) {
			$params[] = sprintf('%s=%s', $key, $value);
		}
		return implode('&', $params);
	}

	/**
	 * RFC3986 urlencode method
	 *
	 * @param array|string|mixed $value
	 * @return array|string|mixed
	 */
	public function rfcUrlencode($value) {
		$output = '';

		if (is_array($value)) {
			$output = array_map(array($this, 'rfcUrlencode'), $value);
		}
		elseif ( is_scalar($value) ) {
			$output = rawurlencode($value);
		}
		return $output;
	}

	/**
	 * RFC3986 urldecode method
	 *
	 * @param string $string
	 * @return string
	 */
	public function rfcUrldecode($string) {
		return urldecode($string);
	}


	/**
	 * Base64 encode the key and secret
	 */
	public function encodeCredential() {
		return base64_encode( sprintf('%s:%s', $this->getKey(), $this->getSecret()) );
	}

	/**
	 * Make an API request
	 */
	public function request($method, $endpoint, $params = array()) {
		$this->setEndpoint($endpoint);
		$auth = sprintf('Authorization: Bearer %s', $this->getAccessToken());
		$this->_makeRequest($this->getApiEndpoint(), 'GET', $auth, $params);

		if ( $this->getHttpCode() === 200 ) {
			return $this->getBody();
		}
		return $this;
	}

	/**
	 * Make an API GET request
	 */
	public function get($endpoint, $params = array()) {
		return $this->request('GET', $endpoint, $params);
	}

	/**
	 * Make an API POST request
	 */
	public function post($endpoint, $params = array()) {
		return $this->request('POST', $endpoint, $params);
	}

	/**
	 * Format plain text to formatted tweet
	 *
	 * @param string $text
	 * @return string
	 */
	public function toTweet( $text ) {
		return $this->_formatHashtags(
			$this->_formatMentions(
				$this->_formatLinks( $text )
			)
		);
	}

	/**
	 * Format links in a string
	 *
	 * @param string $text
	 * @return string
	 */
	private function _formatLinks( $text ) {
		if ( preg_match(static::URL_REGEX, $text, $url) ) {
			$text = preg_replace_callback(static::URL_REGEX, function( $matches ) {
				return sprintf('<a target="_blank" href="%s" rel="nofollow">%s</a>', $matches[0], $matches[0]);
			}, $text);
		}
		return $text;
	}

	/**
	 * Format Twitter Mentions in a string
	 *
	 * @param string $text
	 * @return string
	 */
	private function _formatMentions( $text ) {
		if ( preg_match(static::MENTION_REGEX, $text, $mention) ) {
			$text = preg_replace_callback(static::MENTION_REGEX, function( $matches ) {
				return sprintf('<a target="_blank" href="%s/%s" rel="nofollow">%s</a>', static::TWITTER_URI, $matches[0], $matches[0]);
			}, $text);
		}
		return $text;
	}

	/**
	 * Format Hashtag(s) in a string
	 *
	 * @param string $text
	 * @return string
	 */
	private function _formatHashtags( $text ) {
		if ( preg_match(static::HASHTAG_REGEX, $text, $hashtags) ) {
			$text = preg_replace_callback(static::HASHTAG_REGEX, function( $matches ) {
				return sprintf('<a target="_blank" href="%s/search?q=%s" rel="nofollow">%s</a>', static::TWITTER_URI, $this->rfcUrlencode($matches[0]), $matches[0]);
			}, $text);
		}
		return $text;
	}
}