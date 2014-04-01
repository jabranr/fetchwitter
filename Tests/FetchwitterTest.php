<?php

include_once('../src/Fetchwitter.php');
include_once('../vendor/autoload.php');

class FetchwitterTest extends PHPUnit_Framework_TestCase {	
	public $fetchwitter, $config;

	public function setUp() {
		$this->config = array(
			'api_key' => 'INSERT_API_KEY_HERE',
			'api_secret' => 'INSERT_API_SECRET_HERE'
		);
	}

	public function tearDown() {
		unset($this->fetchwitter);
	}

	public function testSetUpInstance() {
		$this->fetchwitter = new FetchwitterTest( $this->config );
	}

	public function testConfigProvided() {
		$this->assertTrue( isset($this->config) );
	}

	public function testAPIKeyIsSet() {
		$this->assertArrayHasKey( 'api_key', $this->config );
	}

	public function testAPISecretIsSet() {
		$this->assertArrayHasKey( 'api_secret', $this->config );
	}
}