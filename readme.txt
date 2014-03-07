=== Google Drive WP Media ===
Contributors: moch-a
Donate link: http://www.mochamir.com/
Tags: google drive, google drive upload, media library, google drive plugin, gallery, featured image, download, files hosting, image, media, pictures, links, images, post, upload, hosting storage, google
Requires at least: 3.1
Tested up to: 3.8.1
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Google Drive integration plugin. Google Drive on Wordpress Media Publishing. Upload files to Google Drive from WordPress blog.

== Description ==

Google Drive on Wordpress Media Publishing. Direct access to your Google Drive, allow you to manage your files remotely from your WordPress blog.
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

Required:

* PHP 5.3.0
* cURL

== Installation ==

1. Extract google-drive-wp-media into your WordPress plugins directory (wp-content/plugins).
1. Log in to WordPress Admin. Go to the Plugins page and click Activate for Google Drive WP Media
1. On Admin backend, go to Media >> Google Drive WP Media
1. Follow the instructions.

== Frequently Asked Questions ==

= How to insert zip file into post as a Google Drive link, not linked to attachment page? =
Click file which you want to attach, on ATTACHMENT DISPLAY SETTINGS, change Link to Media File. Click Insert into post.

== Changelog ==

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

== Screenshots ==

1. Media Upload Tab
2. Files & Folder List
3. Media Menu
4. Media Library Attachment