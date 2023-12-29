# TikTok Login Kit for PHP
**TikTok Login Kit** implementation in PHP based on the [official documentation](https://developers.tiktok.com/doc/login-kit-web/).

This is an unofficial SDK for the official Login Kit APIs.

## Features

**The library has been updated to include video/image publish via Direct Post**

Current features include:

- Log in with TikTok
- Retrieve Basic User Information
- Retrieve Advanced User Information
- Retrieve Videos
- Paginate Videos
- Refresh Expired Token
- **NEW** Publish a Video with "Direct Post" via URL
- **NEW** Publish a Video with "Direct Post" via File
- **NEW** Publish one or more Images with "Direct Post" via URLs

## Installation

Install via Composer

```
composer require gimucco/tiktok-loginkit
```

## Requirements

You need to have your app set up and approved in the [TikTok Developer Portal](https://developers.tiktok.com/). 

If you're upgrading to the v2 TikTok API version, make sure you've added your *Redirect URLs* and selected the proper scopes. 

## Requirements for Direct Posts

If you're planning to publish videos/photos via Direct Post, you need to undergo an audit. More info [here](https://developers.tiktok.com/application/content-posting-api)

Until you're approved:
- You can only publish private videos
- The account that you use for testing must also be private

## Code Example for Logging in and retrieving basic info
```
// Initialize the class. 
// $client_id and $client_secret are provided by TikTok. 
// $redirect_uri must be approved in the TikTok developer portal.
$_TK = new TikTokLoginKit\Connector($client_id, $client_secret, $redirect_uri);
if (TikTokLoginKit\Connector::receivingResponse()) { 
	try {
		$token = $_TK->verifyCode($_GET[TikTokLoginKit\Connector::CODE_PARAM]);
		// Your logic to store the access token
		$user = $_TK->getUser();
		// Your logic to manage the User info
		$videos = $_TK->getUserVideoPages();
		// Your logic to manage the Video info
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
Refer to the examples folder for a quick examples of how to use the login, fetch and paginate videos, publish videos
