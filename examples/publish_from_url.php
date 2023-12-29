<?php

require_once __DIR__.'/../vendor/autoload.php';
use gimucco\TikTokLoginKit;
use gimucco\TikTokLoginKit\Connector;
use gimucco\TikTokLoginKit\response\PublishStatus;
use gimucco\TikTokLoginKit\uploads\VideoFromUrl;

$video_url = 'https://cdn2.lnk.bi/exampletiktok.mp4';
$video_caption = 'This is a sample Video uploaded from APIs';
$access_token = getenv('TIKTOKTOKEN'); // act.abc...

// Init the connector
$_TK = TikTokLoginKit\Connector::fromIni(__DIR__.'/env.ini');

// We assume the user is already logged and you have the access token
// If you don't, check the login.php example
$_TK->setToken($access_token);

// Create a new Video Publish Object
// Note that the Privacy can only be Public if you've been approved
// Your test account also must be set to Private
$video = new VideoFromUrl($video_url, $video_caption, Connector::PRIVACY_PRIVATE);

// Directly publish the Video to TikTok
// Will throw exceptions if you don't have permissions or if the user doesn't have certain capabilities active
$publishInfo = $video->publish($_TK);

// Wait for the video to be published
$PublishStatus = $_TK->waitUntilPublished($publishInfo->getPublishID());
if ($PublishStatus->getStatus() == PublishStatus::PUBLISH_COMPLETE) {
	echo "UPLOADED".PHP_EOL;
	// Only available if the video is public and the account is public and you've been approved
	echo "Video Public Id: ".$PublishStatus->getPublicPostID();
} else {
	echo "ERROR".PHP_EOL;
	echo $PublishStatus->getErrorCode().": ".$PublishStatus->getErrorMessage().PHP_EOL;
}
