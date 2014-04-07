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
		$this->fetchwitter->set_access_token($this->fetchwitter->get_new_access_token());
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
			array('test tweet', 'test tweet'),
			array('Follow @jabranr for interesting updates', 'Follow <a target="_blank" href="https://twitter.com/jabranr" rel="nofollow">@jabranr</a> for interesting updates'),
			array('This tweet includes a #hashtag', 'This tweet includes a <a target="_blank" href="https://twitter.com/search?q=%23hashtag" rel="nofollow">#hashtag</a>'),
			array('Follow @jabranr for interesting updates. This tweet includes a #hashtag', 'Follow <a target="_blank" href="https://twitter.com/jabranr" rel="nofollow">@jabranr</a> for interesting updates. This tweet includes a <a target="_blank" href="https://twitter.com/search?q=%23hashtag" rel="nofollow">#hashtag</a>'),
			array('Fetchwitter is available at http://j.mp/fetchwitter', 'Fetchwitter is available at <a target="_blank" href="http://j.mp/fetchwitter" rel="nofollow">http://j.mp/fetchwitter</a>')
		);
	}
}