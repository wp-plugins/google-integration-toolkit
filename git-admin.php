<?php
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


class GoogleIntegrationToolkitAdmin {
	// Constructor
	function GoogleIntegrationToolkitAdmin() {
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
	}
	
	// Sanitize GWT mode
	function sanitize_gwt_mode( $mode ) {
		if ( ( $mode != 'meta' ) && ( $mode != 'file' ) ) {
			return 'meta';
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
<h2><?php _e('Google Integration Toolkit - Options', 'wypiekacz'); ?></h2>

<form name="dofollow" action="options.php" method="post">
<?php settings_fields( 'google-integration-toolkit' ); ?>
<table class="form-table">

<tr><th colspan="2"><h3><?php _e('Google Webmasters Tools:', 'google-integration-toolkit'); ?></h3></th></tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label><?php _e('Page verification method:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="radio" id="git_gwt_mode_meta" name="git_gwt_mode" value="meta" <?php echo get_option( 'git_gwt_mode' ) == 'meta' ? 'checked="checked"' : ''; ?> /><label for="git_gwt_mode_meta"><?php _e('Meta tag', 'google-integration-toolkit'); ?></label><br />
<input type="radio" id="git_gwt_mode_file" name="git_gwt_mode" value="file" <?php echo get_option( 'git_gwt_mode' ) == 'file' ? 'checked="checked"' : ''; ?> /><label for="git_gwt_mode_file"><?php _e('File', 'google-integration-toolkit'); ?></label>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_gwt_meta"><?php _e('Meta tag value:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="100" size="50" id="git_gwt_meta" name="git_gwt_meta" value="<?php echo stripcslashes( get_option( 'git_gwt_meta' ) ); ?>" /><br />
<?php _e('This tag looks like this:', 'google-integration-toolkit'); ?><br />
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
<input type="checkbox" id="git_analytics_adsense" name="git_analytics_adsense" value="yes" <?php echo get_option( 'git_analytics_adsense' ) ? 'checked="checked"' : ''; ?> />
</td>
</tr>

<tr><th colspan="2"><h3><?php _e('RSS/Atom Feeds tagging:', 'google-integration-toolkit'); ?></h3></th></tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<label for="git_rss_tagging"><?php _e('Enable RSS/Atom Feeds tagging:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="checkbox" id="git_rss_tagging" name="git_rss_tagging" value="yes" <?php echo get_option( 'git_rss_tagging' ) ? 'checked="checked"' : ''; ?> /><br />
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
<label for="git_rss_tag_campaign"><?php _e('Source name:', 'google-integration-toolkit'); ?></label>
</th>
<td>
<input type="text" maxlength="20" size="20" id="git_rss_tag_campaign" name="git_rss_tag_campaign" value="<?php echo stripcslashes( get_option( 'git_rss_tag_campaign' ) ); ?>" /><br />
<?php _e('This value will be used as a value for the <code>utm_campaign</code> parameter.', 'google-integration-toolkit'); ?>
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
}

?>