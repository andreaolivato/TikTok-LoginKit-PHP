<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to structure the PublishStatus returned json
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit\response;

use Exception;

class PublishInfo {
	public const NO_ERRORS = 'ok';

	private bool $success;
	private string $publish_id;
	private string $upload_url;
	private string $error_code;
	private string $error_message;
	private string $log_id;

	public function __construct(bool $success, string $publish_id, string $upload_url, string $error_code, string $error_message = '', string $log_id = '') {
		$this->success = $success;
		$this->publish_id = $publish_id;
		$this->upload_url = $upload_url;
		$this->error_code = $error_code;
		$this->error_message = $error_message;
		$this->log_id = $log_id;
	}

	/**
	 * Parse information from the JSON returned and provide an object
	 *
	 * @param object $json
	 * @return PublishInfo
	 * @throws Exception
	 */
	public static function fromJson(object $json) {
		if (empty($json->error->code)) {
			throw new \Exception('Invalid TikTok JSON: '.var_export($json, 1));
		}
		if ($json->error->code == self::NO_ERRORS) {
			$success = true;
			$error_code = '';
		} else {
			$success = false;
			$error_code = $json->error->code;
		}
		$publish_id = '';
		if (!empty($json->data->publish_id)) {
			$publish_id = $json->data->publish_id;
		}
		$upload_url = '';
		if (!empty($json->data->upload_url)) {
			$upload_url = $json->data->upload_url;
		}
		$error_message = '';
		if (!empty($json->error->message)) {
			$error_message = $json->error->message;
		}
		$log_id = '';
		if (!empty($json->error->log_id)) {
			$log_id = $json->error->log_id;
		}
		return new self($success, $publish_id, $upload_url, $error_code, $error_message, $log_id);
	}

	/**
	 * Checks if the Call was successful
	 *
	 * @return bool success
	 */
	public function isSuccess() {
		return $this->success;
	}

	/**
	 * Get the temporary publish_id. Can be used to check the upload status.
	 * Is only present if status is true
	 *
	 * @return string publish id
	 */
	public function getPublishID() {
		return $this->publish_id;
	}

	/**
	 * Get the Upload URL to upload a local file
	 * Is only present if status is true and if you're using the VideoFromFile
	 *
	 * @return string publish id
	 */
	public function getUploadUrl() {
		return $this->upload_url;
	}

	/**
	 * Get the error code returned for the upload. To understand check https://developers.tiktok.com/doc/content-posting-api-reference-direct-post/#error_codes
	 *
	 * @return string the error code returned for the upload
	 */
	public function getErrorCode() {
		return $this->error_code;
	}

	/**
	 * Get a description of the error message
	 *
	 * @return string the error message
	 */
	public function getErrorMessage() {
		return $this->error_message;
	}

	/**
	 * Get the Log ID
	 *
	 * @return string the log id
	 */
	public function getLogID() {
		return $this->log_id;
	}
}
