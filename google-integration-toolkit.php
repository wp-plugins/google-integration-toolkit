<?php
/*
Plugin Name: Google Integration Toolkit
Plugin URI: http://www.poradnik-webmastera.com/projekty/google_integration_toolkit/
Description: Integrate Google services (Analytics, Webmaster Tools, etc.) with Your Blog.
Author: Daniel Frużyński
Version: 1.1
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
		var $admin = null;
		
		// Constructor
		function GoogleIntegrationToolkit() {
			// Initialization
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
		
		// Initialize plugin
		function init() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'google-integration-toolkit', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)) );
			}
		}
		
		// Plugin initialization - admin
		function admin_init() {
			require_once( dirname( __FILE__ ) . '/git-admin.php' );
			$this->admin = new GoogleIntegrationToolkitAdmin();
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
		
		// Check if $this->admin is initialized
		function check_admin_helper() {
			if ( !$this->admin ) {
				wp_die( '<b style="color:red">'.
					__('Fatal Google Integration Toolkit error: $this->admin is not initialized!',
					'google-integration-toolkit').'</b>' );
			}
		}
		
		// Handle options panel
		function options_panel() {
			$this->check_admin_helper();
			
			$this->admin->options_panel();
		}
		
		// URL handler for GWT verification
		function status_header( $status_header, $header ) {
			if ( ( $header == 404 ) && ( get_option( 'git_gwt_mode' ) == 'file' ) ) {
				$filename = get_option( 'git_gwt_filename' );
				if ( $filename != '' ) {
					// Extract root dir from blog url
					$root = '/';
					if ( preg_match( '#http://[^/]+(.+)#', get_option( 'url' ), $matches ) ) {
						$root = $matches[1];
					}
					// Make sure it ends with slash
					if ( $root[ strlen($root) - 1 ] != '/' ) {
						$root .= '/';
					}
					// Check if request is for GWT verification file
					if ( $root.$filename == $_SERVER['REQUEST_URI'] ) {
						wp_die( 'Welcome, Google!', '200 OK', array( 'response' => 200 ) );
						exit();
					}
				}
			}
			
			return $status_header;
		}
		
		// Extra entries in <head> section
		function wp_head() {
			// Google Webmasters Tools
			if ( get_option( 'git_gwt_mode' ) == 'meta' ) {
				$meta = get_option( 'git_gwt_meta' );
				if ( $meta != '' ) {
					echo '<meta name="verify-v1" content="', $meta, '" />', "\n";
				}
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
pageTracker._setAllowAnchor(true);
pageTracker._trackPageview();
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
				$blogurl = get_option( 'url' );
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
	}
	
	add_option( 'git_gwt_mode', 'meta' ); // GWT: add meta tag ('meta') or use file ('file')
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
	
	$wp_google_integration_toolkit = new GoogleIntegrationToolkit();
}

?>