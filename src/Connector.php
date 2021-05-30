<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Main Connector Class: manages login, redirect and retrieval of information
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit;

class Connector {

	// Base URLs used for the API calls
	public const BASE_REDIRECT_URL = 'https://open-api.tiktok.com/platform/oauth/connect/?client_key=%s&scope=%s&response_type=code&redirect_uri=%s&state=%s';
	public const BASE_AUTH_URL = 'https://open-api.tiktok.com/oauth/access_token/?client_key=%s&client_secret=%s&code=%s&grant_type=authorization_code';
	public const BASE_USER_URL = 'https://open-api.tiktok.com/oauth/userinfo/?open_id=%s&access_token=%s';

	// Name of the Session used to store the State. This is required to prevent CSRF attacks
	public const SESS_STATE = 'TIKTOK_STATE';

	// Name of the GET parameter we expect containing the authorization code to verify
	public const CODE_PARAM = 'code';

	// Permissions List
	public const PERMISSION_USER_BASIC = 'user.info.basic';
	public const PERMISSION_VIDEO_LIST = 'video.list';
	public const PERMISSION_SHARE_SOUND = 'share.sound.create';
	public const VALID_PERMISSIONS = [self::PERMISSION_USER_BASIC, self::PERMISSION_VIDEO_LIST, self::PERMISSION_SHARE_SOUND];

	// .ini file configuration
	public const INI_CLIENTID = 'client_id';
	public const INI_CLIENTSECRET = 'client_secret';
	public const INI_REDIRECTURI = 'redirect_uri';
	public const INI_REQUIRED = [self::INI_CLIENTID, self::INI_CLIENTSECRET, self::INI_REDIRECTURI];

	private $client_id;
	private $client_secret;
	private $redirect;
	private $token;
	private $openid;

	/**
	 * Main constructor
	 *
	 * Requires the basic configuration required by TikTok Apis.
	 *
	 * @param string $client_id The Client ID provided by TikTok Developer Portal
	 * @param string $client_secret The Client Secret provided by TikTok Developer Portal
	 * @param string $redirect_uri Redirect URI approved on the Developer Portal
	 */
	public function __construct(string $client_id, string $client_secret, string $redirect_uri) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->redirect = $redirect_uri;
	}

	/**
	 * Alternative constructor based on an .ini file
	 *
	 * Retrieves the .ini file, parses the required parameters, then returns the object via the standard constructor
	 *
	 * @param string $path
	 * @return Connector self
	 * @throws Exception If the path is not found or the .ini file doesn't contain all the required parameters
	 */
	public static function fromIni(string $path) {
		if (!file_exists($path)) {
			throw new \Exception('Ini file not found in requested path: '.$path);
		}
		$cfg = parse_ini_file($path);
		foreach (self::INI_REQUIRED as $required_info) {
			if (!isset($cfg[$required_info])) {
				throw new \Exception('Ini file is missing required info: '.$required_info);
			}
		}
		return new self($cfg[self::INI_CLIENTID], $cfg[self::INI_CLIENTSECRET], $cfg[self::INI_REDIRECTURI]);
	}

	/**
	 * Gets the redirect URI for frontend usage
	 *
	 * Generates the Redirect URI. This should be used in the frontend to redirect the user to TikTok to accept the API connection/permissions
	 *
	 * @param array $permissions an array containing all the permissions you want to use. Your app must be approved for these permissions
	 * @return string the URL to which you need to redirect the user
	 * @throws Exception If the requested permissions are wrongly formatter
	 */
	public function getRedirect(array $permissions = [self::PERMISSION_USER_BASIC]) {
		foreach ($permissions as $permission) {
			if (!in_array($permission, self::VALID_PERMISSIONS)) {
				throw new \Exception('Invalid Permission Requested. Valid permissions are: '.implode(", ", self::VALID_PERMISSIONS));
			}
		}
		$state = uniqid();
		$_SESSION[self::SESS_STATE] = $state;
		return sprintf(self::BASE_REDIRECT_URL, $this->client_id, implode(",", $permissions), urlencode($this->redirect), $state);
	}

	/**
	 * Checks the GET parameters to see if I am receiving a response from TikTok with the authorisation code
	 *
	 * @return bool true if I am receiving an authorisation code and I should validate it
	 */
	public static function receivingResponse() {
		if (isset($_GET[self::CODE_PARAM]) && $_GET[self::CODE_PARAM]) {
			return true;
		}
		return false;
	}

	/**
	 * Calls the TikTok APIs to verify an authorisation code and retrieve the access token
	 *
	 * First checks to validate the STATE variable. The GET of the state should be the same as the SESSION to prevent CSRF attacks
	 * Then calls the TikTok APIs to verify the received authorisation code and exchange it for an Access Token
	 * Set the Token and User ID within the class for further use
	 *
	 * @param string $code contains the code received via the GET parameter
	 * @return string the URL to which you need to redirect the user
	 * @throws Exception If the STATE is not valid or if the API return error
	 */
	public function verifyCode(string $code) {
		if (!isset($_SESSION[self::SESS_STATE]) || !isset($_GET['state']) || $_SESSION[self::SESS_STATE] != $_GET['state']) {
			throw new \Exception('Invalid State Variable: Session: '.$_SESSION[self::SESS_STATE].' VS GET : '.$_GET['state']);
			return false;
		}

		try {
			$url = sprintf(self::BASE_AUTH_URL, $this->client_id, $this->client_secret, $code);
			$res = self::get($url);
			$json = json_decode($res);
			if (isset($json->data->access_token) && $json->data->access_token) {
				$this->setToken($json->data->access_token);
				$this->setOpenID($json->data->open_id);
				return $json;
			} else {
				throw new \Exception('TikTok Api Error: '.$json->data->description);
			}
		} catch (\Exception $e) {
			throw new \Exception('TikTok Api Error: '.$e->getMessage());
		}
	}

	/**
	 * Sets the Access Token received after authorisation for further use
	 *
	 * @param string $token The access Token received by the APIs
	 * @return void
	 */
	public function setToken(string $token) {
		$this->token = $token;
	}

	/**
	 * Sets the User ID received after authorisation for further use
	 *
	 * @param string $openid The User ID received by the APIs
	 * @return void
	 */
	public function setOpenID(string $openid) {
		$this->openid = $openid;
	}

	/**
	 * Calls the TikTok APIs to retrieve all available user information for the logged user
	 *
	 * @return object the JSON containing the user data
	 * @throws Exception If the API returns an error
	 */
	public function getUserInfo() {
		try {
			$url = sprintf(self::BASE_USER_URL, $this->openid, $this->token);
			$res = self::get($url);
			return json_decode($res);
		} catch (\Exception $e) {
			throw new \Exception('TikTok Api Error: '.$e->getMessage());
		}
	}

	/**
	 * After Calling the TikTok API via the getUserInfo() method, builds and returns the User object of this class for easier handling
	 *
	 * @return User the User object
	 * @throws Exception If the API returns an error
	 */
	public function getUser() {
		try {
			$json = $this->getUserInfo();
			return User::fromJson($json);
		} catch (\Exception $e) {
			throw new \Exception('TikTok Api Error: '.$e->getMessage());
		}
	}

	/**
	 * Basic HTTP wrapper to perform calls to the TikTok Api
	 *
	 * @param string $url The URL to call
	 * @return string the response of the call or false
	 */
	private static function get($url) {
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET"
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return false;
		} else {
			return $response;
		}
	}
}
