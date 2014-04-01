<?php

require_once('./src/Fetchwitter.php');
require_once('./vendor/autoload.php');

class FetchwitterTest extends PHPUnit_Framework_TestCase {	
	public $fetchwitter, $config;

	public function setUp() {
		$this->config = array(
			'api_key' => 'hqXaOd33grIEIOizaUvpcQ',
			'api_secret' => 'Kxs68MJGHvtTe9CArjLntsc9iVDEygIfqTJlrcqMhLM'
		);
		$this->fetchwitter = new Fetchwitter( $this->config );
	}

	public function tearDown() {
		unset( $this->fetchwitter );
	}

	public function testConfigProvided() {
		$this->assertTrue( isset( $this->config ) );
	}

	public function testAPIKeyIsSet() {
		$this->assertArrayHasKey( 'api_key', $this->config );
	}

	public function testAPISecretIsSet() {
		$this->assertArrayHasKey( 'api_secret', $this->config );
	}

	/**
	 * @dataProvider testCaseTweetsForToTweetMethod
	 */

	public function testToTweet($staticTweet, $formattedTweet) {
		$resultTweet = $this->fetchwitter->to_tweet( $staticTweet );
		$this->assertEquals($resultTweet, $formattedTweet);
	}

	public function testCaseTweetsForToTweetMethod() {
		return array(
			array('test tweet', 'test tweet')
			// array('#Test #tweet', '<a target="_blank" href="https://twitter.com/search?q=%23Test" ref="nofollow">#Test</a> <a target="_blank" href="https://twitter.com/search?q=%23tweet" ref="nofollow">#tweet</a>')
			// array('@Test @tweet', '<a target="_blank" href="https://twitter.com/Test" ref="nofollow">@Test</a> <a href="https://twitter.com/tweet" ref="nofollow">@tweet</a>'),
			// array('@Test #tweet', '<a target="_blank" href="https://twitter.com/Test" ref="nofollow">@Test</a> <a href="https://twitter.com/search?q=%23tweet" ref="nofollow">#tweet</a>'),
			// array('Test tweet at http://example.com', 'Test tweet at <a target="_blank" href="http://example.com" ref="nofollow">http://example.com</a>')
		);
	}
}