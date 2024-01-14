<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to structure the PublishInfo returned json
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit\response;

use Exception;

class PublishStatus {
	public const NO_ERRORS = 'ok';

	public const STATUS_DOWNLOADING = 'PROCESSING_DOWNLOAD';
	public const STATUS_UPLOADING = 'PROCESSING_UPLOAD';
	public const PUBLISH_COMPLETE = 'PUBLISH_COMPLETE';
	public const FAILED = 'FAILED';

	private bool $success;
	private string $status;
	private string $error_code;
	private string $error_message;
	private string $log_id;
	private string $public_post_id;

	//string(150) "{"data":{"downloaded_bytes":10062683,"status":"PROCESSING_DOWNLOAD"},"error":{"code":"ok","message":"","log_id":"202312280857597AA19C5FBDD4513C2E9A"}}"
	// string(119) "{"data":{"status":"PUBLISH_COMPLETE"},"error":{"code":"ok","message":"","log_id":"202312280858037AA19C5FBDD4513C3038"}}"

	public function __construct(bool $success, string $status, string $public_post_id, string $error_code, string $error_message = '', string $log_id = '') {
		$this->success = $success;
		$this->status = $status;
		$this->error_code = $error_code;
		$this->error_message = $error_message;
		$this->log_id = $log_id;
		$this->public_post_id = $public_post_id;
	}

	/**
	 * Parse information from the JSON returned and provide an object
	 *
	 * @param object $json
	 * @return PublishStatus
	 * @throws Exception
	 */
	public static function fromJson(object $json) {
		if (empty($json->error->code)) {
			throw new \Exception('Invalid TikTok JSON: '.var_export($json, 1));
		}
		$error_message = '';
		if ($json->error->code != self::NO_ERRORS) {
			$error_message = '';
			if (!empty($json->error->message)) {
				$error_message = $json->error->message;
			}
			$log_id = '';
			if (!empty($json->error->log_id)) {
				$log_id = $json->error->log_id;
			}
			return new self(false, '', '', $json->error->code, $error_message, $log_id);
		}
		if (empty($json->data->status)) {
			throw new \Exception('Invalid TikTok JSON: '.var_export($json, 1));
		}
		if ($json->data->status == self::FAILED) {
			$reason = '';
			if (!empty($json->data->fail_reason)) {
				$reason = $json->data->fail_reason;
			}
			return new self(false, '', '', $json->data->status, $reason);
		}
		$public_post_id = '';
		if (!empty($json->data->publicaly_available_post_id)) {
			if (is_array($json->data->publicaly_available_post_id)) {
				$public_post_id = $json->data->publicaly_available_post_id[0];
			} else {
				$public_post_id = (int) $json->data->publicaly_available_post_id;
			}
		}
		return new self(true, $json->data->status, $public_post_id, $json->error->code);
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
	 * Get the status of the Publish process
	 * To see what they mean: https://developers.tiktok.com/doc/content-posting-api-reference-get-video-status/
	 *
	 * @return string status
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Get the ID of the published post
	 * Is only available if you've been approved, if the post is public, and if the profile of the creator is public
	 *
	 * @return string public_post_id
	 */
	public function getPublicPostID() {
		return $this->public_post_id;
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
