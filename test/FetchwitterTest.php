<?php

require_once('./src/Fetchwitter.php');

class FetchwitterTest extends PHPUnit_Framework_TestCase {
	public $fetchwitter, $config;

	public function setUp() {
		$this->config = array();
		$this->fetchwitter = null;
	}

	public function tearDown() {
		unset( $this->fetchwitter );
	}

	/**
	 * @expectedException Exception
	 */
	public function testNoArgumentsException() {
		$this->fetchwitter = new Fetchwitter();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage Fetchwitter is not properly configured.
	 */
	public function testNoArgumentsExceptionMessage() {
		$this->fetchwitter = new Fetchwitter();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage A required argument is missing.
	 */
	public function testFewArgumentsExceptionMessage() {
		$this->fetchwitter = new Fetchwitter('API_KEY');
	}

	/**
	 * @dataProvider testCaseTweetsForToTweetMethod
	 */
	public function testToTweet($staticTweet, $formattedTweet) {
		if ( ! $this->fetchwitter ) return;
		$resultTweet = $this->fetchwitter->toTweet( $staticTweet );
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