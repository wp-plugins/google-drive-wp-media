=== Google Drive WP Media ===
Contributors: moch-a
Donate link: http://www.mochamir.com/
Tags: google drive, google drive upload, media library, google drive plugin, gallery, featured image, download, files hosting, image, media, pictures, links, images, post, upload, hosting storage, google
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Google Drive integration plugin. Google Drive on Wordpress Media Publishing. Upload files to Google Drive from WordPress blog.

== Description ==

Google Drive on Wordpress Media Publishing. Direct access to your Google Drive, allows you to manage your files remotely from your WordPress blog.
Upload and share your files directly from your WordPress blog to Google Drive.

Features:

* Option to auto insert your Google Drive files into your WordPress Media Library.
* Attach your Google Drive files to your posts.
* Upload your files from your WordPress Administration to Google Drive.
* Create folder to storing your files.
* Enable to attach any files to posts.
* 700kb chunked for handle uploading large files.
* Displaying current storage space.
* Create WordPress image galleries.
* Dummy internal urls.
* Delete Google Drive file and folder
* Media Library categories
* Auto create category based on Google Drive name
* Filtering files by its categories in Add Media upload tab

Required:

* PHP 5.3.0
* cURL

== Installation ==

1. Extract google-drive-wp-media into your WordPress plugins directory (wp-content/plugins).
1. Log in to WordPress Admin. Go to the Plugins page and click Activate for Google Drive WP Media
1. On Admin backend, go to Media >> Google Drive WP Media
1. Follow the instructions.

== Frequently Asked Questions ==

= I got a problem and the message is "An error occurred: Unable to parse the p12 file. Is this a .p12 file? Is the password correct? OpenSSL error:". what I missed? =
The error reported that was 'Unable to parse the p12 file', have you write the correct url path of p12 file.? can you access or download it via web browser? in addition if your domain setting use non-www or www only, then you have to add/remove www in url path.

= I got a problem and the message is "An error occurred: Error refreshing the OAuth2 token, message: '{ "error" : "invalid_grant" }'". what I missed? =
There's something wrong (typo or something) w/ your Service Account Name setting. Please make sure, there's no white space in the form fields. especially Service Account Name field or create New Client ID.

= How to insert zip file into post as a Google Drive link, not linked to attachment page? =
Click file which you want to attach, on ATTACHMENT DISPLAY SETTINGS, change Link to Media File. Click Insert into post.

== Changelog ==

= 1.5 =

* Feature to delete files/folder added
* Media Library categories added

= 1.4 =

* Url Rewrite bug fixed and optimized
* Added CSS files

= 1.3 =

* maxResults parameter added
* WP Enqueue style added
* Added new option to rewrite urls

= 1.2 =

* Image size bug fixed

= 1.1 =

* Bug fixed on default storage option
* Option to 'Add to Media Library' on default storage option added

= 1.0 =

* Plugin images location moved/changed to local plugin directory
* Added icon tabs

= 0.9 =

* Added Sanitizing for API Key form fields

= 0.8 =

* jQuery UI Themes added
* Added wpnonce
* Custom Google Api config

= 0.7 =

* Google Drive Account Information added
* jQueryui ajax tabs added

= 0.6 =

* Option to reload page after folder creation added
* Typo fixed
* jQuery noConflict() mode compatibility problems fixed

= 0.5 =

* Minimum requirement error handling added
* mkdir function removed

= 0.4 =

* Added 700kb chunked for handle uploading large files activity.
* finfo problem fixed.

= 0.3 =

* Override default media upload dir added
* GUI and CSS changed
* JQuery upload added

= 0.2 =

* Sanitize input text when creating folder
* Error create folder missing argument fixed

= 0.1 =

* Initial release, it's a beta version.

== Upgrade Notice ==

None

== Screenshots ==

1. Media Upload Tab
2. Files & Folder List
3. Media Menu
4. Media Library Attachment
5. Media Library Categories