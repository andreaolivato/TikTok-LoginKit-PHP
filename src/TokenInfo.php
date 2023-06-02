<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to structurise the Token object
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit;

class TokenInfo {
	private string $access_token;
	private string $refresh_token;
	private int $expires_in;
	private string $open_id;
	private int $refresh_expires_in;
	private array $scope;
	private string $token_type;

	public function __construct(string $access_token, string $refresh_token, int $expires_in, string $open_id, int $refresh_expires_in, array $scope, string $token_type) {
		$this->access_token = $access_token;
		$this->refresh_token = $refresh_token;
		$this->expires_in = $expires_in;
		$this->open_id = $open_id;
		$this->refresh_expires_in = $refresh_expires_in;
		$this->scope = $scope;
		$this->token_type = $token_type;
	}

	public static function fromJson(object $json) {
		if (empty($json->access_token) || empty($json->open_id)) {
			return;
		}
		$access_token = $json->access_token;
		$open_id = $json->open_id;
		$refresh_token = '';
		$expires_in = 0;
		$refresh_expires_in = 0;
		$scope = [];
		$token_type = 'Bearer';
		if (!empty($json->refresh_token)) {
			$refresh_token = $json->refresh_token;
		}
		if (!empty($json->expires_in)) {
			$expires_in = (int) $json->expires_in;
		}
		if (!empty($json->refresh_expires_in)) {
			$refresh_expires_in = (int) $json->refresh_expires_in;
		}
		if (!empty($json->scope)) {
			$scope = explode(',', $json->scope);
		}
		if (!empty($json->token_type)) {
			$token_type = $json->token_type;
		}
		return new self($access_token, $refresh_token, $expires_in, $open_id, $refresh_expires_in, $scope, $token_type);
	}

	/**
	 * Get the Access Token value
	 *
	 * @return string access token
	 */
	public function getAccessToken() {
		return $this->access_token;
	}

	/**
	 * Get the Refresh Token value
	 *
	 * @return string refresh token
	 */
	public function getRefreshToken() {
		return $this->refresh_token;
	}

	/**
	 * Get the Access Token expiration time in seconds
	 *
	 * @return int expiration time of access token
	 */
	public function getExpiresIn() {
		return $this->expires_in;
	}

	/**
	 * Get the user id of the owner of the Access Token
	 *
	 * @return string user id
	 */
	public function getOpenId() {
		return $this->open_id;
	}

	/**
	 * Get the Refresh Token expiration time in seconds
	 *
	 * @return int expiration time of refresh token
	 */
	public function getRefreshExpiresIn() {
		return $this->refresh_expires_in;
	}

	/**
	 * Get the list of scopes, in an array
	 *
	 * @return array list of scopes
	 */
	public function getScope() {
		return $this->scope;
	}

	/**
	 * Get the type of Token, usually just Bearer
	 *
	 * @return string Token Type
	 */
	public function getTokenType() {
		return $this->token_type;
	}
}
