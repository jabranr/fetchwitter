## Fetchwitter [![Build Status](https://travis-ci.org/jabranr/fetchwitter.svg?branch=master)](https://travis-ci.org/jabranr/fetchwitter) [![Latest Stable Version](https://poser.pugx.org/fetchwitter/fetchwitter/v/stable.svg)](https://packagist.org/packages/fetchwitter/fetchwitter) [![Total Downloads](https://poser.pugx.org/fetchwitter/fetchwitter/downloads.svg)](https://packagist.org/packages/fetchwitter/fetchwitter)

![Fetchwitter](branding/fetchwitter-512x288.png)

PHP framework to fetch tweets from Twitter API v1.1 using OAuth authentication/authorization

<blockquote>The authentication flow uses Twitter’s App-Only Authentication.</blockquote>

## Install

Fetchwitter can be installed using one of following methods:

#### [Download latest release](https://github.com/jabranr/Fetchwitter/releases)

#### Using Composer

Add as dependency into your `composer.json` file.

``` json
{
	"require": {
		"fetchwitter/fetchwitter": "~1.0.4"
	}
}
```

Use [Composer](http://getcomposer.org) to install.

``` shell
$ composer install
```

## Basic Example

Here is a very basic example to start with.

``` php
// Configurations
$config = array(
	'api_key' => 'API_KEY_HERE',
	'api_secret' => 'API_SECRET_HERE',
	'count' => '5'
);

// Setup a new instance of Fetchwitter
try {
	$fw = new Fetchwitter($config);
}
catch(Exception $e) {
	echo $e->getMessage();
}

// Make sure that you have the instance ready
if ( isset($fw) && $fw ) {
	
	// Get an access token for first time
	try {
		$accessToken = $fw->get_new_access_token();
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}

	# ... OR

	// Assign an existing cached access token
	$fw->set_access_token($existingAccessToken);

	if ( ! empty($fw->get_access_token()) ) {
		# do all magic here...
		# ...
	}
}

```

## Documentation

### Configuration &amp; Initialization

+ Register a new app at [Twitter Application Manager](https://apps.facebook.com) and get API key and secret.
+ Initialize a new instance of Fetchwitter as follows:

``` php
$config = array( 
	'api_key' => API_KEY,
	'api_secret' => API_SECRET,
	'count' => 5 // optional, default is 10
);

try {
	$fetchwitter = new Fetchwitter( array $config );
}
catch(Exception $e) {
	echo $e->getMessage();
}
```

===

### Get or set access token

+ If this is a fresh setup, then you can get a new access token using following method:

``` php
try {
	$the_access_token = $fetchwitter->get_new_access_token();
}
catch(Exception $e) {
	echo $e->getMessage();
}
```

+ You can set an access token using following method:

``` php
# Already have an access token in cache or database
$fetchwitter->set_access_token( string $existingAccessToken );
```

+ You can access token from current active instance using following method:

``` php
$the_access_token = $fetchwitter->get_access_token();
```

===

### How it works?

Once a valid instance of Fetchwitter is created, it automatically goes through an [App-Only Authentication](https://dev.twitter.com/docs/auth/application-only-auth) and gets a valid `access_token` from Twitter API or returns appropriate error message in case of failure. 

<blockquote>The method will throw an Exception in case of any missing parameters or returns error message from API in JSON format otherwise.</blockquote>

Following methods are available for a valid and successfully established connection with API.

#### Converts static Tweet to formatted Tweet

Use `to_tweet( $text )` method to convert the static Tweet to formatted Tweet with Mentions, Hashtags and Links properly linked. The method takes the Tweet in string format as parameter and returns a formatted Tweet.

**Use:**

``` php
$tweet = $fetchwitter->to_tweet( string $tweet );
```

**Example:**

**Static Tweet as it comes from the API feed:**

This is a #test tweet by @jabranr to confirm methods from #Fetchwitter. More at https://github.com/jabranr/fetchwitter

**Formatted Tweet using `to_tweet()` method:**

This is a [#test](https://twitter.com/search?q=%23test) tweet by [@jabranr](https://twitter.com/jabranr) to confirm methods from [#Fetchwitter](https://twitter.com/search?q=%23Fetchwitter). More at [https://github.com/jabranr/fetchwitter](https://github.com/jabranr/fetchwitter)

A [JavaScript version](https://gist.github.com/jabranr/68515719cde0653d641d) is also available.

===

#### Get Tweets of a Twitter user

You can use the method `get_by_user( $screen_name, $count )` to fetch the list of Tweets for a given user. The method accepts two parameters as follows:

**Use:**

``` php
get_by_user( string $screen_name, int $count )

$screen_name - // Twitter handle for the user
$count - // Number of Tweets to fetch (optional, default is 10)
```

**Example:**

``` php
$fetchwitter = new Fetchwitter( $config );
$tweets = $fetchwitter->get_by_user('jabranr', 5);

if ( $tweets ) {
	$tweets = json_decode($tweets);
	if ( $tweets && count($tweets) ) {
		echo '<ul>';
		foreach( $tweets as $tweet ) {
			echo '<li>' . $fetchwitter->to_tweet( $tweet->text ) . '</li>';
			// See above for details on "to_tweet()" method
		}
		echo '</ul>';
	}
}
```
This above example code will produce the unordered list of 5 recent Tweets for user [@jabranr](https://twitter.com/jabranr)

===

#### Get Tweets for a query or hashtag

You can use the method `search_tweets( $params )` to fetch the list of Tweets for a given query i.e. hashtag. The method accepts an array of parameters as follows:

**Use:**

``` php
search_tweets( array $params )

$params['q'] - // Any query to search relevant Tweets i.e. #London
$params['count'] - // Number of Tweets to return (The results will be paginated) - (optional, default is 10)
$params['result_type'] - // Can be any of the three: mixed, recent, popular - (optional, default is recent)
```
**Example:**

``` php
$fetchwitter = new Fetchwitter( $config );
$params = array(
	'q' => '#London',
	'result_type' => 'mixed',
	'count' => 15
);

$results = $fetchwitter->search_tweets( $params );

if ( $results ) {
	$tweets = json_decode($results);
	if ( $tweets->statuses && count($tweets->statuses) ) {
		echo '<ul>';
		foreach( $tweets->statuses as $tweet ) {
			echo '<li>' . $fetchwitter->to_tweet( $tweet->text ) . '</li>';
			// See above for details on "to_tweet()" method
		}
		echo '</ul>';
	}
}
```
This above example code will produce the unordered list of 15 mixed Tweets for #London hashtag.

===

#### Paginate through search results

You can use the method `search_tweets( $params )` to paginate through the results of search performed by the same method earlier. The first use of this method produces the `search_metadata` object in return result from API. Use the `next_results` to paginate to next set of results or `previous_results` to previous set of results. The method accepts an array of single parameter as follows:

**Use:**

``` php 
search_tweets( array $params ) 

$params['query'] - // The query generated by previous search results by Twitter API
```
**Example:**

``` php

$params = array(
	'query' => $tweets->search_metadata->next_results
	// $tweets from above example
);

$results = $fetchwitter->search_tweets( $params );
```
The predefined query from `search_metadata` property of existing search results set has everything inherited from original settings provided in `$params` array of parameters.

===


Issues reporting/tracking: [Github Repo Issues](https://github.com/jabranr/fetchwitter/issues)

**Contributions are welcome!**
In order to contribute, fork the repository, create a new branch and after when you have a contribution ready then make a pull request. I will be reviewed and merged with contributor's credit. To keep track of the updates and contribution history, no branch will be removed.

MIT License - [http://opensource.org/licenses/MIT](http://opensource.org/licenses/MIT)

&copy; [@jabranr](https://twitter.com/jabranr) - 2014