<?php
/*
Plugin Name: Super Switch
Plugin URI: http://goto8848.net/projects/super-switch/
Description: As you see, these are a set of switches.
Author: Crazy Loong
Version: 1.5
Author URI: http://goto8848.net/

Copyright 2008 - 2009 Crazy Loong  (email : crazyloong@gmail.com)

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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define('CLSSVER', '1.5');

// Pre-2.6 compatibility
if (!defined('WP_CONTENT_URL'))
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL'))
	define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if (!defined('WP_PLUGIN_DIR'))
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

if (!defined('WP_ABSDIR'))
	define('WP_ABSDIR', substr(str_replace('\\', '/', ABSPATH), 0, -1));

define('CLSSABSPATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('CLSSABSFILE', str_replace('\\', '/', dirname(__FILE__)) . '/' . basename(__FILE__));
define('CLSSRELDIR', str_replace(WP_ABSDIR . '/', '', CLSSABSPATH));
define('CLSSRELURL', CLSSRELDIR);
define('CLSSURL', site_url(CLSSRELDIR));
define('CLSSDIR', CLSSABSPATH);

global $clss_options_array;
$clss_options_array = array('wp-generator', 'themes-preview', 'core-update', 'plugins-update', 'themes-update', 'revisions', 'browse-happy', 'autosave', 'recently-active-plugins');

if (function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain('super_switch', CLSSRELDIR . '/lang');
}

function clss_options_init() {
	global $clss_options_array;
	$clss_options = array();
	if ($clss_old_options = get_option('clss_options')) {
		$clss_options = $clss_old_options;
		foreach ($clss_options_array as $item) {
			if (!array_key_exists($item, $clss_old_options)) {
				$clss_options[$item] = true;
			}
		}
	} else {
		foreach ($clss_options_array as $item) {
			$clss_options[$item] = true;
		}
	}
	update_option('clss_options', $clss_options);
	return $clss_options;
}

if (false === $clss_options = get_option('clss_options')) {
	$clss_options = clss_options_init();
}
/*if (!is_array($clss_options)) {
	$clss_options = clss_options_init();
}*/

register_activation_hook(__FILE__, 'super_switch_activate');
function super_switch_activate() {
	if (function_exists('delete_transient')) {
		delete_transient('update_core');
		delete_transient('update_plugins');
		delete_transient('update_themes');
	}
	if (function_exists('delete_site_transient')) {
		delete_site_transient('update_core');
		delete_site_transient('update_plugins');
		delete_site_transient('update_themes');
	}
	delete_option('update_core');
	delete_option('update_plugins');
	clss_options_init();
}

// add admin pages
add_action('admin_menu', 'clss_add_pages');

function clss_add_pages() {
	add_options_page(__('Super Switch', 'super_switch'), __('Super Switch', 'super_switch'), 8, __FILE__, 'clss_options');
}

// add Super Switch options page
function clss_options() {
	global $clss_options, $clss_options_array;
	if ($_POST['update-options']) {
		foreach ($_POST as $item_key => $item_value) {
			if (in_array($item_key, $clss_options_array)) {
				if ($item_value == '1') {
					$clss_options[$item_key] = true;
				} else {
					$clss_options[$item_key] = false;
					if ($item_key == 'core-update') {
						if (function_exists('delete_transient')) {
							delete_transient('update_core');
						}
						if (function_exists('delete_site_transient')) {
							delete_site_transient('update_core');
						}
						delete_option('update_core');
					} elseif ($item_key == 'plugins-update') {
						if (function_exists('delete_transient')) {
							delete_transient('update_plugins');
						}
						if (function_exists('delete_site_transient')) {
							delete_site_transient('update_plugins');
						}
						delete_option('update_plugins');
					} elseif ($item_key == 'themes-update') {
						if (function_exists('delete_transient')) {
							delete_transient('update_themes');
						}
						if (function_exists('delete_site_transient')) {
							delete_site_transient('update_themes');
						}
						delete_option('update_themes');
					} elseif ($item_key == 'recently-active-plugins') {
						update_option('recently_activated', array());
					}
				}
			}
		}
		update_option('clss_options', $clss_options);
		echo '<div id="message" class="updated fade"><p><b>' . __('Updated successfully.', 'super_switch') . '</b></p></div>';
	}
?>
<div class="wrap">
	<h2><?php _e('Super Switch Options', 'super_switch'); ?></h2>
	<form id="switch-options-form" method="post" action="" name="switch-options-form">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Display the version of WP in the head of your blog.', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="wp-generator" type="radio" value="1"<?php if ($clss_options['wp-generator'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="wp-generator" type="radio" value="0"<?php if ($clss_options['wp-generator'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Preview themes.', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="themes-preview" type="radio" value="1"<?php if ($clss_options['themes-preview'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="themes-preview" type="radio" value="0"<?php if ($clss_options['themes-preview'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Core update checking and notification system.', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="core-update" type="radio" value="1"<?php if ($clss_options['core-update'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="core-update" type="radio" value="0"<?php if ($clss_options['core-update'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Check the update of the plugins.', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="plugins-update" type="radio" value="1"<?php if ($clss_options['plugins-update'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="plugins-update" type="radio" value="0"<?php if ($clss_options['plugins-update'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Check the update of the themes.', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="themes-update" type="radio" value="1"<?php if ($clss_options['themes-update'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="themes-update" type="radio" value="0"<?php if ($clss_options['themes-update'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Save revisions', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="revisions" type="radio" value="1"<?php if ($clss_options['revisions'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="revisions" type="radio" value="0"<?php if ($clss_options['revisions'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Browse Happy', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="browse-happy" type="radio" value="1"<?php if ($clss_options['browse-happy'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="browse-happy" type="radio" value="0"<?php if ($clss_options['browse-happy'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Autosave', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="autosave" type="radio" value="1"<?php if ($clss_options['autosave'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="autosave" type="radio" value="0"<?php if ($clss_options['autosave'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Display Recently Active Plugins', 'super_switch'); ?></th>
				<td>
					<fieldset>
						<p><label>
							<input name="recently-active-plugins" type="radio" value="1"<?php if ($clss_options['recently-active-plugins'] == true) { ?> checked="checked"<?php } ?> />
							<?php _e('Enable', 'super_switch'); ?>
						</label>
						<label>
							<input name="recently-active-plugins" type="radio" value="0"<?php if ($clss_options['recently-active-plugins'] == false) { ?> checked="checked"<?php } ?> />
							<?php _e('Disable', 'super_switch'); ?>
						</label></p>
					</fieldset>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" value="<?php _e('Update Options', 'super_switch'); ?>" name="update-options" class="button-primary" />
		</p>
	</form>
	<h2><?php _e('About', 'super_switch'); ?></h2>
	<p><?php _e('If you have any question, you can visit <a href="http://goto8848.net/projects/super-switch/">homepage</a>. I will help you. ^0^ <br />If you find any error in my english, please tell me. I&#39;d be glad of your help.', 'super_switch'); ?></p>
</div>
<?php
}

if (version_compare($wp_version, '2.8', '<')) { // For WP 2.7
	include('for27.php');
} elseif (version_compare($wp_version, '3.0', '<')) { // For WP 2.8
	include('for28.php');
} else {
	include('for30.php'); // For WP 3.0
}
?>