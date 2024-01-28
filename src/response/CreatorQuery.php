<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to structure the CreatorQuery returned json
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit\response;

use Exception;
use gimucco\TikTokLoginKit\Connector;

class CreatorQuery {
	private string $avatar_url;
	private string $nickname;
	private string $username;
	private bool $duet_off;
	private bool $stitch_off;
	private bool $comment_off;
	private int $max_video_duration_sec;
	private array $privacy_options;

	public function __construct(string $avatar_url, string $nickname, string $username, bool $duet_off, bool $stitch_off, bool $comment_off, int $max_video_duration_sec, array $privacy_options) {
		$this->avatar_url = $avatar_url;
		$this->nickname = $nickname;
		$this->username = $username;
		$this->duet_off = $duet_off;
		$this->stitch_off = $stitch_off;
		$this->comment_off = $comment_off;
		$this->max_video_duration_sec = $max_video_duration_sec;
		$this->privacy_options = $privacy_options;
	}

	/**
	 * Returns a CreatorQuery object containing all the info of the Creator necessary to publish content
	 *
	 * @param object $json returned by the `post/publish/creator_info/query/` endpoint
	 * @return CreatorQuery
	 * @throws Exception
	 */
	public static function fromJson(object $json) {
		if (empty($json->data) || empty($json->data->creator_nickname)) {
			throw new \Exception('Invalid TikTok JSON: '.var_export($json, 1));
		}
		$data = $json->data;
		$options = [];
		foreach ($data->privacy_level_options as $option_id) {
			if (Connector::isValidPrivacyLevel($option_id)) {
				$options[] = $option_id;
			}
		}
		return new self($data->creator_avatar_url, $data->creator_nickname, $data->creator_username, (bool) $data->duet_disabled, (bool) $data->stitch_disabled, (bool) $data->comment_disabled, (int) $data->max_video_post_duration_sec, $options);
	}

	/**
	 * Checks if the creator can publish with the provided privacy option
	 *
	 * @param string $option
	 * @return bool
	 */
	public function hasPrivacyOption(string $option) {
		if (in_array($option, $this->getPrivacyOptions())) {
			return true;
		}
		return false;
	}

	/**
	 * Get the Avatar URL
	 *
	 * @return string the avatar url
	 */
	public function getAvatarUrl() {
		return $this->avatar_url;
	}

	/**
	 * Get the Nickname value
	 *
	 * @return string the nickname
	 */
	public function getNickname() {
		return $this->nickname;
	}

	/**
	 * Get the Username
	 *
	 * @return string the username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Returns if Duet is turned off
	 *
	 * @return bool duet is off
	 */
	public function isDuetOff() {
		return $this->duet_off;
	}

	/**
	 * Returns if Stitch is turned off
	 *
	 * @return bool stitch is off
	 */
	public function isStitchOff() {
		return $this->stitch_off;
	}

	/**
	 * Returns if Comments are turned off
	 *
	 * @return bool comments are off
	 */
	public function areCommentsOff() {
		return $this->comment_off;
	}

	/**
	 * Get the maximum duration of videos in seconds
	 *
	 * @return int max duration of video in seconds
	 */
	public function getMaxVideoDuration() {
		return $this->max_video_duration_sec;
	}

	/**
	 * Get all the valid privacy options for the user
	 *
	 * @return array privacy options
	 */
	public function getPrivacyOptions() {
		return $this->privacy_options;
	}
}
