## Fetchwitter [![Build Status](https://travis-ci.org/jabranr/fetchwitter.svg?branch=master)](https://travis-ci.org/jabranr/fetchwitter) [![Latest Stable Version](https://poser.pugx.org/fetchwitter/fetchwitter/v/stable.svg)](https://packagist.org/packages/fetchwitter/fetchwitter) [![Total Downloads](https://poser.pugx.org/fetchwitter/fetchwitter/downloads.svg)](https://packagist.org/packages/fetchwitter/fetchwitter) [![Analytics](https://ga-beacon.appspot.com/UA-50688851-1/fetchwitter)](https://github.com/igrigorik/ga-beacon)

PHP library to fetch tweets from Twitter API v1.1 using OAuth authentication/authorization. Fetchwitter provides easy to use methods for basic functionality such as tweets or hashtag search, or fetch a user timeline feed. All you need are Twitter app key and secret to get you going.

<blockquote>Note: The authentication flow uses Twitter’s App-Only Authentication.</blockquote>

## Install
Fetchwitter can be installed using one of following methods:

* [Download latest release](https://github.com/jabranr/Fetchwitter/releases/latest)

#### Using Composer (Recommended)
Add as dependency into your `composer.json` file.
``` json
{
	"require": {
		"fetchwitter/fetchwitter": ">=1.0.8"
	}
}
```

Use [Composer](http://getcomposer.org) to install.
``` shell
$ composer install
```

Require/include the file into your project
```php
require 'path/to/vendor/fetchwitter/autoload.php'
```

## Basic Example
Here is a very basic example to start with.

Setup a new Fetchwitter instance. The configuration arguments can be passed as string, indexed array or associative array.

``` php
$config = array(
	'api_key' => 'API_KEY',
	'api_secret' => 'API_SECRET'
);

try {
	$fw = new Fetchwitter($config);
}
catch(Exception $e) {
	echo $e->getMessage();
}

```

or  
```php
$config = array('API_KEY', 'API_SECRET');

try {
	$fw = new Fetchwitter($config);
}
catch(Exception $e) {
	echo $e->getMessage();
}

```

or 
```php
try {
	$fw = new Fetchwitter('API_KEY', 'API_SECRET');
}
catch(Exception $e) {
	echo $e->getMessage();
}

```

The initialization automatically fetches the access token using OAuth flow and makes it available to the class. An existing access token can also be set manually as follow.

```php
if ( isset($fw) && $fw->getHttpCode() === 200 ) {
	
	// Assign an existing cached access token
	$fw->setAccessToken($anExistingAccessToken);

	// OR start making API requests

}

```

## Documentation

### Configuration &amp; Initialization

+ Register a new app at [Twitter Application Manager](https://apps.facebook.com) and get API key and secret.
+ Initialize a new instance of Fetchwitter as explained above.
+ Make API requests as exampled below:

Make any GET request i.e. fetch user timeline
```php
$fw->get('statuses/user_timeline', array(
	'count' => 2,
	'screen_name' => 'jabranr'
	)
);
```

Search tweets with a hashtag
```php
$fw->get('search/tweets', array(
	'count' => 2,
	'q' => '#TwitterAPI',
	'result_type' => 'recent'
	)
);
```


===

### How it works?
Once a valid instance of Fetchwitter is created, it automatically goes through an [App-Only Authentication](https://dev.twitter.com/docs/auth/application-only-auth) and gets a valid `access_token` from Twitter API or returns appropriate error message in case of failure. 

<blockquote>The method will throw an Exception in case of any missing parameters or returns error message from API in JSON format otherwise.</blockquote>

Following methods are available for a valid and successfully established connection with API.

#### Converts a static Tweet to formatted Tweet
Use `toTweet( $text )` method to convert the static Tweet to formatted Tweet with Mentions, Hashtags and Links properly linked. The method takes the Tweet in string format as parameter and returns a formatted Tweet.

**Use:**

``` php
$tweet = $fetchwitter->toTweet( string $tweet );
```

**Example:**

**Static Tweet as it comes from the API feed:**

This is a #test tweet by @jabranr to confirm methods from #Fetchwitter. More at https://github.com/jabranr/fetchwitter

**Formatted Tweet using `toTweet()` method:**

This is a [#test](https://twitter.com/search?q=%23test) tweet by [@jabranr](https://twitter.com/jabranr) to confirm methods from [#Fetchwitter](https://twitter.com/search?q=%23Fetchwitter). More at [https://github.com/jabranr/fetchwitter](https://github.com/jabranr/fetchwitter)

A [JavaScript version](https://gist.github.com/jabranr/68515719cde0653d641d) is also available.

#### Disclaimer
I made this library just for learning purpose and have been improving it. A lot of inspiration has come from [Abraham's comprehensive TwitterOAuth](https://github.com/abraham/twitteroauth) library. If you need a complete Twitter API support then please use Abraham's library instead.

#### Issues reporting/tracking
[Github Repo Issues](https://github.com/jabranr/fetchwitter/issues)

#### Contribution
In order to contribute:

1. Fork the repository
2. Create a new branch
3. Once you are ready then make a pull request

#### License
MIT License - [http://opensource.org/licenses/MIT](http://opensource.org/licenses/MIT)

&copy; [@jabranr](https://twitter.com/jabranr) - 2014-2-15
