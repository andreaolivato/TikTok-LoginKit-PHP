# v0.6.6 (2024-02-28)

Fix inclusion path on examples to reflect the new structure

# v0.6.5 (2024-02-21)

Added two additional parameters for video/photo upload: brand_content_toggle, brand_organic_toggle.
This was updated to the 3 classes VideoFromFile, VideoFromUrl, and ImagesFromURls.
Also added the related getters.

# v0.6.4 (2024-01-28)

BugFix: Method getMaxVideoDuration in class CreatorQuery was named incorrectly

# v0.6.3 (2024-01-16)

BugFix: Duplicated title and caption when publishing images. Currently we'll ignore the title

# v0.6.2 (2024-01-14)

BugFix: publicaly_available_post_id returned by the `publish/status/fetch` sometimes is an array, even if not documented.

# v0.6.1 (2024-01-11)

Improved errors on missing STATE variable during login

# v0.6 (2023-12-29)

Major update to include Direct Publishing
- Publish a new video via url
- Publish a new image/carousel via url
- Publish a new video via file upload

** NOTE **
The TokenInfo class has been moved to the response namespace. 
If you include/use this class directly, make sure you update its namespace.

# v0.5.3 (2023-06-02)

Improved custom function to retrieve the @handle of the user

# v0.5.2 (2023-06-02)

Improved return function for the handle

# v0.5.1 (2023-06-02)

Removed unused debugging info

# v0.5 (2023-06-02)

Migrated the library to the new v2 TikTok api protocol

# v0.4.1 (2022-11-24)

Added function to retrieve Token if needed

# v0.4 (2022-08-07)

Implemented refreshToken method

# v0.3 (2022-07-28)

Migrated Video/List from GET to POST so that it allows for more fields to be retrieved