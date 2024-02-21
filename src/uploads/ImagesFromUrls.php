<?php

/*
 * (c) Andrea Olivato <andrea@lnk.bio>
 *
 * Helper class to create a Video to Publish on TikTok
 *
 * This source file is subject to the GNU General Public License v3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace gimucco\TikTokLoginKit\uploads;

use Exception;
use gimucco\TikTokLoginKit\Connector;
use gimucco\TikTokLoginKit\response\PublishInfo;

class ImagesFromUrls {
	private array $urls;
	private string $title;
	private string $privacy_level;
	private bool $comments_off;
	private bool $auto_add_music;
	private bool $is_brand_content;
	private bool $is_brand_organic;
	public function __construct(array $urls, string $title, string $privacy_level = Connector::PRIVACY_PRIVATE, bool $comments_off = false, bool $auto_add_music = false, bool $is_brand_content = false, bool $is_brand_organic = false) {
		if (!Connector::isValidPrivacyLevel($privacy_level)) {
			throw new Exception('TikTok Invalid Privacy Level Provided: '.$privacy_level.". Must be: ".implode(', ', Connector::VALID_PRIVACY));
		}
		if (!sizeof($urls)) {
			throw new Exception('Please provide at least 1 image URL to Publish');
		}
		foreach ($urls as $url) {
			if (!filter_var($url, FILTER_VALIDATE_URL)) {
				throw new Exception('Invalid Image URL: '.$url);
			}
		}
		$this->urls = $urls;
		$this->title = $title;
		$this->privacy_level = $privacy_level;
		$this->comments_off = $comments_off;
		$this->auto_add_music = $auto_add_music;
		$this->is_brand_content = $is_brand_content;
		$this->is_brand_organic = $is_brand_organic;
	}


	/**
	 * Publish a video to TikTok via a Public URL
	 * This method validates the privacy, comments, etc, based on the capabilities returned by the CreatorQuery
	 * Throws exceptions if the Creator doesn't have the capability available
	 *
	 * @param Connector $tk
	 * @return PublishInfo
	 * @throws Exception
	 */
	public function publish(Connector $tk) {
		$CreatorQuery = $tk->getCreatorQuery();
		if (!$CreatorQuery->hasPrivacyOption($this->getPrivacyLevel())) {
			throw new Exception('TikTok Error: This Creator cannot publish with the privacy level '.implode(', ', $CreatorQuery->getPrivacyOptions()));
		}
		if ($CreatorQuery->areCommentsOff() && !$this->getCommentsOff()) {
			throw new Exception('TikTok Error: This Creator cannot publish without turning off the Comments');
		}
		return $this->publishWithoutChecks($tk);
	}

	/**
	 * Publish to TikTok by automatically replacing any invalid value based on the Creator's capabilities
	 * Warning: this changes the privacy, comment settings, without telling you anything
	 * The recommended way is to use the `publish` method and manage exceptions.
	 *
	 * @param Connector $tk
	 * @return PublishInfo
	 * @throws Exception
	 */
	public function publishReplacingInvalidValues(Connector $tk) {
		$CreatorQuery = $tk->getCreatorQuery();
		if (!$CreatorQuery->hasPrivacyOption($this->getPrivacyLevel())) {
			$this->setPrivacyLevel(Connector::PRIVACY_PRIVATE);
		}
		if ($CreatorQuery->areCommentsOff() && !$this->getCommentsOff()) {
			$this->setCommentsOff(true);
		}
		return $this->publishWithoutChecks($tk);
	}

	/**
	 * Directly publish to TikTok without performing any checks on the Creator's capabilities.
	 * Warning: This is only recommended if you had previously checked and don't want to repeat the same checks.
	 *
	 * @param Connector $tk
	 * @return PublishInfo
	 * @throws Exception
	 */
	public function publishWithoutChecks(Connector $tk) {
		try {
			$data = [
				'post_info' => [
					'title' => '',
					'description' => $this->getTitle(),
					'privacy_level' => $this->getPrivacyLevel(),
					'disable_comment' => $this->getCommentsOff(),
					'auto_add_music' => $this->getAutoAddMusic(),
					'brand_content_toggle' => $this->getIsBrandContent(),
					'brand_organic_toggle' => $this->getIsBrandOrganic()
				],
				'source_info' => [
					'source' => 'PULL_FROM_URL',
					'photo_cover_index' => 0,
					'photo_images' => $this->getUrls()
				],
				'post_mode' => 'DIRECT_POST',
				'media_type' => 'PHOTO'
			];
			$res = $tk->postWithAuth(Connector::BASE_PHOTO_PUBLSH, $data);
			if (!$res) {
				throw new Exception('TikTok Api Error, invalid returned value '.var_export($res, 1));
			}
			$res = json_decode($res);
			if (!$res) {
				throw new Exception('TikTok Api Error, invalid JSON '.$res);
			}
			return PublishInfo::fromJSON($res);
		} catch (Exception $e) {
			throw new Exception('TikTok Api Error: '.$e->getMessage());
		}
	}

	/**
	 * Get the array containing the URLs of all the images to upload
	 *
	 * @return array urls
	 */
	public function getUrls() {
		return $this->urls;
	}

	/**
	 * Get the Title to be added to the Video
	 *
	 * @return string title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the Privacy Level of the Video
	 *
	 * @return string privacy level
	 */
	public function getPrivacyLevel() {
		return $this->privacy_level;
	}

	/**
	 * Get if the Comments are turned Off. True means they are off.
	 *
	 * @return bool comments off
	 */
	public function getCommentsOff() {
		return $this->comments_off;
	}

	/**
	 * Get if the Creator wants to automatically add music
	 *
	 * @return bool auto_add_music
	 */
	public function getAutoAddMusic() {
		return $this->auto_add_music;
	}

	/**
	 * Get if the Post is a paid partnership to promote a third-party business.
	 *
	 * @return bool brand_content
	 */
	public function getIsBrandContent() {
		return $this->is_brand_content;
	}

	/**
	 * Get if the Post is promoting the creator's own business.
	 *
	 * @return bool brand organic
	 */
	public function getIsBrandOrganic() {
		return $this->is_brand_organic;
	}

	/**
	 * Sets the Privacy Level
	 *
	 * @param string $privacy_level
	 * @return void
	 * @throws Exception
	 */
	public function setPrivacyLevel(string $privacy_level) {
		if (!Connector::isValidPrivacyLevel($privacy_level)) {
			throw new Exception('TikTok Invalid Privacy Level Provided: '.$privacy_level.". Must be: ".implode(', ', Connector::VALID_PRIVACY));
		}
		$this->privacy_level = $privacy_level;
	}

	/**
	 * Set if the comments are turned off. True = off
	 *
	 * @param bool $comments_off
	 * @return void
	 */
	public function setCommentsOff(bool $comments_off) {
		$this->comments_off = $comments_off;
	}
}
