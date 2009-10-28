<?php
/*
Plugin Name: Google Integration Toolkit
Plugin URI: http://www.poradnik-webmastera.com/projekty/google_integration_toolkit/
Description: Integrate Google services (Analytics, Webmaster Tools, etc.) with Your Blog.
Author: Daniel Frużyński
Version: 1.3.2
Author URI: http://www.poradnik-webmastera.com/
Text Domain: google-integration-toolkit
*/

/*  Copyright 2009  Daniel Frużyński  (email : daniel [A-T] poradnik-webmastera.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( !class_exists( 'GoogleIntegrationToolkit' ) ) {
	class GoogleIntegrationToolkit {
		// Constructor
		function GoogleIntegrationToolkit() {
			// Initialisation
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			
			// Add option to Admin menu
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			
			// URL handler for GWT verification
			add_filter( 'status_header', array( &$this, 'status_header' ), 1, 2 );
			
			// Extra entries in <head> section
			add_action( 'wp_head', array( &$this, 'wp_head' ) );
			
			// Extra content just before </body>
			add_action( 'wp_footer', array( &$this, 'wp_footer' ) );
			
			// RSS tagging
			add_filter( 'the_permalink_rss', array( &$this, 'the_permalink_rss' ) );
			add_filter( 'the_content', array( &$this, 'the_content' ) );
			
			// Modify post excerpt and comments for various reasons
			add_filter( 'the_excerpt', array( &$this, 'the_excerpt' ) );
			
			// Modify post content and comments for various reasons
			add_filter( 'comment_text', array( &$this, 'comment_text' ) );
		}
		
		// Initialise plugin
		function init() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'google-integration-toolkit', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) );
			}
		}
		
		// Plugin initialisation - admin
		function admin_init() {
			// Register plugin options
			register_setting( 'google-integration-toolkit', 'git_gwt_mode', array( &$this, 'sanitize_gwt_mode' ) );
			register_setting( 'google-integration-toolkit', 'git_gwt_meta', 'trim' );
			register_setting( 'google-integration-toolkit', 'git_gwt_filename', 'trim' );
			register_setting( 'google-integration-toolkit', 'git_analytics_id', 'trim' );
			register_setting( 'google-integration-toolkit', 'git_analytics_adsense', array( &$this, 'sanitize_bool' ) );
			register_setting( 'google-integration-toolkit', 'git_rss_tagging', array( &$this, 'sanitize_bool' ) );
			register_setting( 'google-integration-toolkit', 'git_rss_tag_source', 'trim' );
			register_setting( 'google-integration-toolkit', 'git_rss_tag_medium', 'trim' );
			register_setting( 'google-integration-toolkit', 'git_rss_tag_campaign', 'trim' );
			register_setting( 'google-integration-toolkit', 'git_adsense_tag_posts', array( &$this, 'sanitize_bool' ) );
			register_setting( 'google-integration-toolkit', 'git_adsense_tag_comments', array( &$this, 'sanitize_bool' ) );
			register_setting( 'google-integration-toolkit', 'git_analytics_track_404', array( &$this, 'sanitize_bool' ) );
			register_setting( 'google-integration-toolkit', 'git_analytics_track_404_prefix', 'trim' );
		}
		
		// Add Admin menu option
		function admin_menu() {
			$file = __FILE__;
			
			// hack for 1.5
			global $wp_version;
			if ( '1.5' == substr( $wp_version, 0, 3 ) ) {
				$file = 'google-integration-toolkit/google-integration-toolkit.php';
			}
			// admin_init is called later, so need to use proxy method here
			add_submenu_page( 'options-general.php', 'Google Integration Toolkit', 
				'Google Integration Toolkit', 10, $file, array( $this, 'options_panel' ) );
		}
		
		// URL handler for GWT verification
		function status_header( $status_header, $header ) {
			if ( ( $header == 404 ) && ( get_option( 'git_gwt_mode' ) == 'file' ) ) {
				$filename = get_option( 'git_gwt_filename' );
				if ( $filename != '' ) {
					// Extract root dir from blog url
					$root = '/';
					if ( preg_match( '#^http://[^/]+(/.+)$#', get_option( 'siteurl' ), $matches ) ) {
						$root = $matches[1];
					}
					// Make sure it ends with slash
					if ( $root[ strlen($root) - 1 ] != '/' ) {
						$root .= '/';
					}
					// Check if request is for GWT verification file
					if ( $root.$filename == $_SERVER['REQUEST_URI'] ) {
						//wp_die( 'Welcome, Google!', '200 OK', array( 'response' => 200 ) );
						echo 'google-site-verification: ', $filename;
						exit();
					}
				}
			}
			
			return $status_header;
		}
		
		// Extra entries in <head> section
		function wp_head() {
			// Google Webmasters Tools
			$gwt_mode = get_option( 'git_gwt_mode' );
			$meta = get_option( 'git_gwt_meta' );
			if ( ( $gwt_mode == 'meta2' ) && ( $meta != '' ) ) {
				echo '<meta name="google-site-verification" content="', $meta, '" />', "\n";
			}
			elseif ( ( $gwt_mode == 'meta' ) && ( $meta != '' ) ) {
				echo '<meta name="verify-v1" content="', $meta, '" />', "\n";
			}
			
			// Google Analytics integration with Google AdSense
			$analytics_id = get_option( 'git_analytics_id' );
			if ( ( $analytics_id != '' ) && get_option( 'git_analytics_adsense' ) ) {
				echo <<<EOT
<script type="text/javascript">
window.google_analytics_uacct = "$analytics_id";
</script>

EOT;
			}
		}
		
		// Extra content just before </body>
		function wp_footer() {
			$analytics_id = get_option( 'git_analytics_id' );
			if ( $analytics_id != '' ) {
				echo <<<EOT
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("$analytics_id");

EOT;
				if ( is_404() && get_option( 'git_analytics_track_404' ) ) {
					echo 'pageTracker._trackPageview("' . get_option( 'git_analytics_track_404_prefix' ) . 
						'?page=" + document.location.pathname + document.location.search + "&from=" + document.referrer);', "\n";
				} else {
					echo 'pageTracker._setAllowAnchor(true);', "\n";
					echo 'pageTracker._trackPageview();', "\n";
				}
				echo <<<EOT
} catch(err) {}</script>

EOT;
			}
		}
		
		// Add tags to the rss links
		function tag_rss_link( $link, $use_hash ) {
			$tag = 'utm_source='.get_option( 'git_rss_tag_source' )
				.'&amp;utm_medium='.get_option( 'git_rss_tag_medium' )
				.'&amp;utm_campaign='.get_option( 'git_rss_tag_campaign' );
			
			if ( $use_hash ) {
				return $link.'#'.$tag;
			} elseif ( strpos( $link, '?' ) === false ) {
				return $link.'?'.$tag;
			} else {
				return $link.'&amp;'.$tag;
			}
		}
		
		// RSS tagging - tag links in RSS
		function the_permalink_rss( $link ) {
			if ( get_option( 'git_rss_tagging' ) ) {
				return $this->tag_rss_link( $link, true );
			} else {
				return $link;
			}
		}
		
		// Helper function for tagging links in RSS - tag links in text
		function update_rss_link( $matches ) {
			$url = $matches[2];
			if ( preg_match( '/^https?:/', $url ) ) {
				// Tag links from this blog only
				$blogurl = get_option( 'siteurl' );
				if ( substr( $url, 0, strlen( $blogurl ) ) == $blogurl ) {
					return $matches[1].$this->tag_rss_link( $url, true );
				} else {
					return $matches[1].$url;
				}
			} else {
				return $matches[1].$this->tag_rss_link( $url, true );
			}
		}
		
		// Content modification function
		function the_content( $content ) {
			// RSS tagging - tag links in RSS' text
			if ( is_feed() && get_option( 'git_rss_tagging' ) ) {
				$content = preg_replace_callback( '/(<\s*a\s[^>]*?\bhref\s*=\s*")([^"]+)/', 
					array( &$this, 'update_rss_link' ), $content );
			}
			
			// AdSense section targetting
			if ( !is_feed() && get_option( 'git_adsense_tag_posts' ) ) {
				return '<!-- google_ad_section_start -->'.$content.'<!-- google_ad_section_end -->';
			}
			
			return $content;
		}
		
		// Content excerpts modification function
		function the_excerpt( $content ) {
			// AdSense section targetting
			if ( !is_feed() && get_option( 'git_adsense_tag_posts' ) ) {
				return '<!-- google_ad_section_start -->'.$content.'<!-- google_ad_section_end -->';
			}
			
			return $content;
		}
		
		// Comments modification function
		function comment_text( $content ) {
			// AdSense section targetting
			if ( !is_feed() && get_option( 'git_adsense_tag_comments' ) ) {
				return '<!-- google_ad_section_start -->'.$content.'<!-- google_ad_section_end -->';
			}
			
			return $content;
		}
		
		// Handle options panel
		function options_panel() {
			$message = null;
			if ( isset($_POST['action']) ) {
				check_admin_referer( 'google-integration-toolkit-options' );
				$message = __('Configuration has been saved.', 'google-integration-toolkit');
				echo '<div id="message" class="updated fade"><p>', $message, '</p></div>', "\n";
			}
			
			// HTML settings form here
?>
<div id="dropmessage" class="updated" style="display:none;"></div>
<div class="wrap">
<h2><?php _e('Google Integration Toolkit - Options', 'google-integration-toolkit'); ?></h2>

<form name="dofollow" action="options.php" method="post">
<?php settings_fields( 'google-integration-toolkit' ); ?>
<table class="form-table">

<!-- Google Webmasters Tools -->
<tr><th colspan="2"><h3><?php _e('Google Webmasters Tools:', 'google-integration-toolkit'); ?></h3></th></tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label><?php _e('Page verification method:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="radio" id="git_gwt_mode_meta2" name="git_gwt_mode" value="meta2" <?php checked( 'meta2', get_option( 'git_gwt_mode' ) ); ?> /><label for="git_gwt_mode_meta2"><?php _e('Meta tag <code>&lt;meta name=&quot;google-site-verification&quot; content=&quot;...&quot; /&gt;</code>', 'google-integration-toolkit'); ?></label><br />
<input type="radio" id="git_gwt_mode_meta" name="git_gwt_mode" value="meta" <?php checked( 'meta', get_option( 'git_gwt_mode' ) ); ?> /><label for="git_gwt_mode_meta"><?php _e('Meta tag <code>&lt;meta name=&quot;verify-v1&quot; content=&quot;...&quot; /&gt;</code>', 'google-integration-toolkit'); ?></label><br />
<input type="radio" id="git_gwt_mode_file" name="git_gwt_mode" value="file" <?php checked( 'file', get_option( 'git_gwt_mode' ) ); ?> /><label for="git_gwt_mode_file"><?php _e('File', 'google-integration-toolkit'); ?></label><br />
<?php _e('<b>Note:</b> Please use <code>google-site-verification</code> meta tag or file to verify new websites. Meta tag <code>verify-v1</code> is supported for backward compatibility only.', 'google-integration-toolkit'); ?>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_gwt_meta"><?php _e('Meta tag value:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="100" size="50" id="git_gwt_meta" name="git_gwt_meta" value="<?php echo stripcslashes( get_option( 'git_gwt_meta' ) ); ?>" /><br />
<?php _e('This tag looks like this:', 'google-integration-toolkit'); ?><br />
<code>&lt;meta name=&quot;google-site-verification&quot; content=&quot;<b>abcdefghijklmnopqrstuvwzyz123456789abcdefghi</b>&quot; /&gt;</code><br />
<?php _e('or', 'google-integration-toolkit'); ?><br />
<code>&lt;meta name=&quot;verify-v1&quot; content=&quot;<b>abcdefghijklmnopqrstuvwzyz123456789abcdefghi</b>&quot; /&gt;</code><br />
<?php _e('Please put bolded part only to the field above.', 'google-integration-toolkit'); ?>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_gwt_filename"><?php _e('Verification file name:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="50" size="30" id="git_gwt_filename" name="git_gwt_filename" value="<?php echo stripcslashes( get_option( 'git_gwt_filename' ) ); ?>" /><br />
<?php printf( __('Name of this file starts with \'google\', e.g. %s', 'google-integration-toolkit'), '<code><b>googleabcdefghijklmnop.html</b></code>' ); ?>
</td>
</tr>

<!-- Google Analytics -->
<tr><th colspan="2"><h3><?php _e('Google Analytics:', 'google-integration-toolkit'); ?></h3></th></tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_analytics_id"><?php _e('Google Analytics ID:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="15" size="15" id="git_analytics_id" name="git_analytics_id" value="<?php echo stripcslashes( get_option( 'git_analytics_id' ) ); ?>" /><br />
<?php _e('Please find following line in your GA tracking code and copy bolded part to the field above:', 'google-integration-toolkit'); ?><br />
<code>var pageTracker = _gat._getTracker(&quot;<b>UA-0000000-0</b>&quot;);</code>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_analytics_adsense"><?php _e('Enable Google AdSense integration:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="checkbox" id="git_analytics_adsense" name="git_analytics_adsense" value="yes" <?php checked( true, get_option( 'git_analytics_adsense' ) ); ?> />
</td>
</tr>

<!-- RSS/Atom Feeds tagging -->
<tr><th colspan="2"><h3><?php _e('RSS/Atom Feeds tagging:', 'google-integration-toolkit'); ?></h3></th></tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_rss_tagging"><?php _e('Enable RSS/Atom Feeds tagging:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="checkbox" id="git_rss_tagging" name="git_rss_tagging" value="yes" <?php checked( true, get_option( 'git_rss_tagging' ) ); ?> /><br />
<?php _e('This option tags all links in RSS/Atom feeds. This allows to track visitors from your feeds using Google Analytics.', 'google-integration-toolkit'); ?>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_rss_tag_source"><?php _e('Source name:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="20" size="20" id="git_rss_tag_source" name="git_rss_tag_source" value="<?php echo stripcslashes( get_option( 'git_rss_tag_source' ) ); ?>" /><br />
<?php _e('This value will be used as a value for the <code>utm_source</code> parameter.', 'google-integration-toolkit'); ?>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_rss_tag_medium"><?php _e('Medium name:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="20" size="20" id="git_rss_tag_medium" name="git_rss_tag_medium" value="<?php echo stripcslashes( get_option( 'git_rss_tag_medium' ) ); ?>" /><br />
<?php _e('This value will be used as a value for the <code>utm_medium</code> parameter.', 'google-integration-toolkit'); ?>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_rss_tag_campaign"><?php _e('Campaign name:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="20" size="20" id="git_rss_tag_campaign" name="git_rss_tag_campaign" value="<?php echo stripcslashes( get_option( 'git_rss_tag_campaign' ) ); ?>" /><br />
<?php _e('This value will be used as a value for the <code>utm_campaign</code> parameter.', 'google-integration-toolkit'); ?>
</td>
</tr>

<!-- AdSense Section Targeting -->
<tr><th colspan="2"><h3><?php _e('AdSense Section Targeting:', 'google-integration-toolkit'); ?></h3></th></tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_adsense_tag_posts"><?php _e('Enable AdSense Section Targetting for Content:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="checkbox" id="git_adsense_tag_posts" name="git_adsense_tag_posts" value="yes" <?php checked( true, get_option( 'git_adsense_tag_posts' ) ); ?> /><br />
<?php _e('This option ads special HTML comment tags around posts, pages and excerpts. This may improve AdSense ads targeting. You can find more informations <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&answer=23168">here</a>.', 'google-integration-toolkit'); ?>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_adsense_tag_comments"><?php _e('Enable AdSense Section Targetting for Comments:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="checkbox" id="git_adsense_tag_comments" name="git_adsense_tag_comments" value="yes" <?php checked( true, get_option( 'git_adsense_tag_comments' ) ); ?> /><br />
<?php _e('This option ads special HTML comment tags around comments.', 'google-integration-toolkit'); ?>
</td>
</tr>

<!-- 404 errors tracking -->
<tr><th colspan="2"><h3><?php _e('404 errors tracking:', 'google-integration-toolkit'); ?></h3></th></tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_analytics_track_404"><?php _e('Track 404 errors with Google Analytics:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="checkbox" id="git_analytics_track_404" name="git_analytics_track_404" value="yes" <?php checked( true, get_option( 'git_analytics_track_404' ) ); ?> /><br /><?php _e('Enable this option to track "Page not found" errors using Google Analytics', 'google-integration-toolkit'); ?>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_analytics_track_404_prefix"><?php _e('URL prefix:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="100" size="15" id="git_analytics_track_404_prefix" name="git_analytics_track_404_prefix" value="<?php echo stripcslashes( get_option( 'git_analytics_track_404_prefix' ) ); ?>" /><br />
<?php _e('All 404 errors will appear in Analytics Reports as visits to URL like <code>/404.html?page=[pagename.html?queryparameter]&from=[referrer]</code>, where <code>[pagename.html?queryparameters]</code> is the missing page name and referrer is the page URL from where the user reached the 404 page. You can change prefix <code>/404.html</code> to something else using this option.', 'google-integration-toolkit'); ?>
</td>
</tr>

</table>

<p class="submit">
<input type="hidden" name="action" value="update" />
<input type="submit" name="Submit" value="<?php _e('Save settings', 'google-integration-toolkit'); ?>" /> 
</p>

</form>
</div>
<?php
		}
		
		// Sanitize GWT mode
		function sanitize_gwt_mode( $mode ) {
			if ( ( $mode != 'meta' ) && ( $mode != 'meta2' ) && ( $mode != 'file' ) ) {
				return 'meta2';
			} else {
				return $mode;
			}
		}
		
		// Sanitize bools (checkboxes)
		function sanitize_bool( $value ) {
			if ( isset( $value ) && ( $value == 'yes' ) ) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	add_option( 'git_gwt_mode', 'meta2' ); // GWT: add meta tag 'google-site-verification' ('meta2') or use file ('file'). 'meta'/'verify-v1' is supported for backward compatibility only
	add_option( 'git_gwt_meta', '' ); // GWT ID
	add_option( 'git_gwt_filename', '' ); // GWT FileName
	add_option( 'git_analytics_id', '' ); // Analytics ID
	add_option( 'git_analytics_adsense', true ); // Enable Analytics-AdSense integration
	add_option( 'git_rss_tagging', true ); // Enable RSS links tagging
	add_option( 'git_rss_tag_source', 'feed' ); // RSS tags - Campaign Source
	add_option( 'git_rss_tag_medium', 'feed' ); // RSS tags - Campaign Medium
	add_option( 'git_rss_tag_campaign', 'feed' ); // RSS tags - Campaign Name
	add_option( 'git_adsense_tag_posts', false ); // AdSense Section Targetting - posts
	add_option( 'git_adsense_tag_comments', false ); // AdSense Section Targetting - comments
	add_option( 'git_analytics_track_404', false ); // Track 404 errors using Analytics
	add_option( 'git_analytics_track_404_prefix', '/404.html' ); // Prefix for 404 tracking using Analytics
	
	$wp_google_integration_toolkit = new GoogleIntegrationToolkit();
}

?>