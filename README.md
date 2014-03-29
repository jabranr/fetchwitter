# Fetchwitter

PHP library to use with Twitter API - v1.1

## Documentation

### Configuration &amp; Initialization

+ Register a new app at (Twitter Apps)[https://apps.facebook.com] and get API key and secret.
+ Initialize a new instance of Fetchwitter as follows:

``` php

$config = array( 'api_key' => API_KEY, 'api_secret' => API_SECRET );
$fetchwitter = new Fetchwitter( $config );

```

### Methods