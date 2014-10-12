=== Rss Post Importer ===
Contributors: jenswaern, feedsapi
Donate link: https://www.feedsapi.org/
Tags: rss, feeds, import, feed, autoblog, feed aggregation, rss-feed
Requires at least: 3.5
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin lets you set up an import posts from one or several rss-feeds and save them as posts on your site, simple and flexible.

== Description ==

This plugin is not a shortcode for just displaying displaying a bunch of links from an rss feeds, it imports data from a feed and saves it as posts.

Features include:

* Importing feeds automatically using cron.
* Set number of posts and category per feed.
* Set what author to assign imported content to.
* Simple template for formatting imported content.

== Installation ==

1. Upload the files to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set up what feeds to import and when!

== Screenshots ==

1. This is the combined settings- and feed-management-screen.

== Change Log ==

= Version 1.0.6 =
* Removed a bug causing save to reset and trigger a new cron.

= Version 1.0.5 =
* Minor improvements.

= Version 1.0.4 =
* Fixed bug that kept cron from running correctly.

= Version 1.0.3 =
* Made the log available through UI instead of just over ftp.
* Design improvements.

= Version 1.0.2 =
* Fixed bug that caused posts to be duplicated when post status was set to anything but 'Publish'.
* Added possibility to log each time imports are made in a textfile (for debugging purposes).

= Version 1.0.1 =
* Fixed some localization issues.