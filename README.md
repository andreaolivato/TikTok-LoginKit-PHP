# TikTok Login Kit for PHP
**TikTok Login Kit** implementation in PHP based on the [official documentation](https://developers.tiktok.com/doc/login-kit-web).

## Features

Current features include:

- Log in with TikTok
- Retrieve Basic User Information

### Currently Working on implementing

- Retrieve Video list

## Installation

Install via Composer

```
composer require gimucco/tiktok-loginkit:@dev
```

## Requirements

You need to have your app set up and approved in the [TikTok Developer Portal](https://developers.tiktok.com/). 

When you register your app, make sure that the **Redirect domain** is set to the actual **Redirect URI** you use to initialize the class. Even if the Official TikTok Documentation says "domain", they actually expect the full URL. 

## Code Example for Logging in and retrieving basic info
```
// Initialize the class. 
// $client_id and $client_secret are provided by TikTok. 
// $redirect_uri must be approved in the TikTok developer portal.
$_TK = new TikTokLoginKit($client_id, $client_secret, $redirect_uri);
if (TikTokLoginKit\Connector::receivingResponse()) { 
	try {
		$token = $_TK->verifyCode($_GET[TikTokLoginKit\Connector::CODE_PARAM]);
		// Your logic to store the access token
		$user = $_TK->getUser();
		// Your logic to manage the user info
		echo $user->getSampleHTML();
	} catch (Exception $e) {
		echo "Error: ".$e->getMessage();
		echo '<br /><a href="?">Retry</a>';
	}
} else {
	echo '<a href="'.$_TK->getRedirect().'">Log in with TikTok</a>';
}
```

## Alternative Constructor
If you prefer to use a .ini file to pass the api credentials, you can use the ```TikTokLoginKit\Connector::fromIni``` method. 
The .ini file should have this simple structure
```
client_id = [your client id]
client_secret = [your client secret]
redirect_uri = [your redirect uri]
```
And you call the alternative constructor by passing the path to the .ini file
```
$_TK = TikTokLoginKit\Connector::fromIni(__DIR__.'/env.ini');
```

## Examples
Refer to the examples folder for a quick examples of how to use the login
