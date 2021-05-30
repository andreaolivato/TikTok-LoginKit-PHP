<?php

// Required to manage STATE parameter
session_start(); // Important! Required for STATE Variable check and prevent CSRF attacks
require_once __DIR__.'/../../../autoload.php';
use gimucco\TikTokLoginKit;

/*
Example passing the Configuration parameters Inline

$api_key = ''; // Your API Key, as obtained from TikTok Developers portal
$api_secret = ''; // Your API Secret, as obtained from TikTok Developers portal
$redirect_uri = ''; // Where to return after authorization. Must be approved in the TikTok Developers portal
$_TK = new TikTokLoginKit($api_key, $api_secret, $redirect_uri);
*/

// Example passing the Configuration parameters via .ini file
$_TK = TikTokLoginKit\Connector::fromIni(__DIR__.'/env.ini');


if (TikTokLoginKit\Connector::receivingResponse()) { // Check if you're receiving the Authorisation Code
	try {
		$token = $_TK->verifyCode($_GET[TikTokLoginKit\Connector::CODE_PARAM]); // Verify the Authorisation code and get the Access Token

		/****  Your logic to store the access token goes here ****/


		$user = $_TK->getUser(); // Retrieve the User Object

		/****  Your logic to store or use the User Info goes here ****/

		// Print some HTML as example
		echo <<<HTML
		<table width="500">
			<tr>
				<td with="200"><img src="{$user->getAvatarLarger()}"></td>
				<td>
					<br />
					<strong>ID</strong>: {$user->getOpenID()}<br /><br />
					<strong>Name</strong>: {$user->getDisplayName()}
				</td>
			</tr>
		</table>
HTML;
	} catch (Exception $e) {
		echo "Error: ".$e->getMessage();
		echo '<br /><a href="?">Retry</a>';
	}
} else { // Print the initial login button that redirects to TikTok
	echo '<a href="'.$_TK->getRedirect().'">Log in with TikTok</a>';
}
