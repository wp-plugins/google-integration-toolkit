=== Plugin Name ===
Contributors: sirzooro
Tags: analytics, feed, feeds, google, gwt, integration, rss, seo, statistics, stats, tracking, section targeting, ad, ads, adsense, advertising, comment, comments, 404
Requires at least: 2.7
Tested up to: 2.9.9
Stable tag: 1.4

This plugin helps you to integrate Google services (Analytics, Webmaster Tools, etc.) with Your Blog.

== Description ==

Google provides a lot of useful services, which can be integrated with your blog. With them you can check how your site is indexed in Google (using Google Webmaster Tools), get detailed statistics (Google Analytics), earn money (Google AdSense) and more. This plugin allows to easily integrate them with your blog.

This version of Google Integration Toolkit plugin provides following features:

* Integration with Google Webmasters Tools (both verification methods are supported: meta tag and verification file);
* Integration with Google Analytics (with optional Google Analytics - Google AdSense integration);
* RSS/Atom Feeds tagging - you can track visitors coming from your feed using Google Analytics;
* 404 error tracking using Google Analytics;
* AdSense Section Targeting - improve AdSense ads targeting;

Available translations:

* English
* Polish (pl_PL) - done by me
* Russian (ru_RU) - thanks Fat Cower
* Belorussian (be_BY) - thanks [ilyuha](http://antsar.info/)
* Chinese (zn_CH) - thanks [BoB](http://wealthynetizen.com/)
* Italian (it_IT) - thanks [Stefan Des](http://www.stefandes.com/)
* Danish (da_DK) - thanks [GeorgWP](http://wordpress.blogos.dk/)
* Dutch (nl_NL) - thanks [Rene](http://wordpresspluginguide.com/)

More features soon!

[Changelog](http://wordpress.org/extend/plugins/google-integration-toolkit/changelog/)

== Installation ==

1. Upload `google-integration-toolkit` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure and enjoy :)

== Frequently Asked Questions ==

= Why I cannot verify my page in Google Webmasters Tools using verification file? =

Starting from October 2009 Google checks contents of file used for verification - it should contains something like this:

`google-site-verification: googleabcdefghijklmnop.html`

Please make sure that you use Google Integration Toolkit in version 1.3 or newer. If you do, open verification file in your web browser and check page source to make sure there is no extra content (e.g. HTML comment). I am aware of WP Super Cache plugin, which adds such comment in version 0.9.6.1 or older - if you use it, please upgrade to version 0.9.7 or newer. If you find another conflicting plugin, please inform its author about this issue, or me.

= Why I cannot verify my page in Google Webmasters Tools using meta tag? =

Starting from October 2009 Google changed name of its meta verification tag from `<meta name="verify-v1" content="..." />` to `<meta name="google-site-verification" content="..." />`. Please make sure that you are using Google Integration Toolkit in version 1.3 or newer, and check its configuration to make sure you use the new tag.

== Changelog ==

= 1.4 =
* Added Dutch translation (thanks Rene);
* Check if someone enters whole meta tag and correct this;
* Code cleanup

= 1.3.4 =
* Added Danish translation (thanks GeorgWP)

= 1.3.3 =
* Marked as compatible with WP 2.9.x

= 1.3.2 =
* Added Italian translation (thanks Stefan Des)

= 1.3.1 =
* Added Chinese translation (thanks BoB)

= 1.3 =
* Added support for new verification meta tag and new format of verification file for Google Webmasters Tools

= 1.2.2 =
* Added Belorussian translation (thanks ilyuha)

= 1.2.1 =
* Moved admin code to main file to avoid "Fatal Google Integration Toolkit error: $this->admin is not initialised!" error

= 1.2 =
* Added 404 error tracking using Google Analytics

= 1.1.3 =
* Added Russian translation (thanks Fat Cower)

= 1.1.2 =
* Make plugin compatible with WordPress 2.8

= 1.1.1 =
* Make plugin compatible with PHP4

= 1.1 =
* Added AdSense Section Targeting

= 1.0 =
* Initial version