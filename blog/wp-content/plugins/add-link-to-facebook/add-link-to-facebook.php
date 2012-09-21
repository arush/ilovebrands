<?php
/*
Plugin Name: Add Link to Facebook
Plugin URI: http://wordpress.org/extend/plugins/add-link-to-facebook/
Description: Automatically add links to published posts to your Facebook wall or pages
Version: 1.164
Author: Marcel Bokhorst
Author URI: http://blog.bokhorst.biz/about/
*/

/*
	GNU General Public License version 3

	Copyright (c) 2011, 2012 Marcel Bokhorst

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Check PHP version
if (version_compare(PHP_VERSION, '5.0.0', '<'))
	die('Add Link to Facebook requires at least PHP 5, installed version is ' . PHP_VERSION);

if (get_option('al2fb_debug')) {
	error_reporting(E_ALL);
	if (!defined('WP_DEBUG'))
		define('WP_DEBUG', true);
}

// Auto load classs
if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
	function __autoload_al2fb($class_name) {
		if ($class_name == 'WPAL2Int')
			require_once('add-link-to-facebook-int.php');
		else if ($class_name == 'AL2FB_Widget')
			require_once('add-link-to-facebook-widget.php');
		else if ($class_name == 'PluginUpdateChecker')
			require_once('plugin-update-checker.php');
	}
	spl_autoload_register('__autoload_al2fb');
}
else {
	if (function_exists('__autoload')) {
		// Another plugin is using __autoload too
		require_once('add-link-to-facebook-int.php');
		require_once('add-link-to-facebook-widget.php');
		require_once('plugin-update-checker.php');
	}
	else {
		function __autoload($class_name) {
			if ($class_name == 'WPAL2Int')
				require_once('add-link-to-facebook-int.php');
			else if ($class_name == 'AL2FB_Widget')
				require_once('add-link-to-facebook-widget.php');
			else if ($class_name == 'PluginUpdateChecker')
				require_once('plugin-update-checker.php');
		}
	}
}

// Include main class
require_once('add-link-to-facebook-class.php');

// Check pre-requisites
WPAL2Facebook::Check_prerequisites();

// Start plugin
global $wp_al2fb;
if (empty($wp_al2fb)) {
	$wp_al2fb = new WPAL2Facebook();
	register_activation_hook(__FILE__, array(&$wp_al2fb, 'Activate'));
}

// Pro version is not hosted on wordpress.org
if (WPAL2Int::Check_updates()) {
	global $updates_al2fb;
	if (empty($updates_al2fb)) {
		$uri = WPAL2Int::Get_multiple_url();
		if (!$uri)
			$uri = WPAL2Int::Redirect_uri();
		$updates_url = 'http://updates.faircode.eu/al2fbpro?action=update&plugin=al2fbpro&uri=' . urlencode($uri);
		if (is_multisite())
			$updates_url .= '&blogs=' . get_blog_count();
		$updates_al2fb = new PluginUpdateChecker($updates_url, __FILE__, 'add-link-to-facebook', 1);
	}
}

// Schedule cron if needed
if (get_option(c_al2fb_option_cron_enabled)) {
	if (!wp_next_scheduled('al2fb_cron')) {
		$min = intval(time() / 60) + 1;
		wp_schedule_event($min * 60, 'al2fb_schedule', 'al2fb_cron');
	}
}
else
	wp_clear_scheduled_hook('al2fb_cron');

add_action('al2fb_cron', 'al2fb_cron');
if (!function_exists('al2fb_cron')) {
	function al2fb_cron() {
		global $wp_al2fb;
		$wp_al2fb->Cron();
	}
}

// Template tag for likers
if (!function_exists('al2fb_likers')) {
	function al2fb_likers($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_likers($post);
	}
}

// Template tag for anchor
if (!function_exists('al2fb_anchor')) {
	function al2fb_anchor($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_fb_anchor($post);
	}
}

// Template tag for like count
if (!function_exists('al2fb_like_count')) {
	function al2fb_like_count($post_ID = null) {
		global $wp_al2fb;
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo $wp_al2fb->Get_like_count($post);
	}
}

// Template tag for Facebook like button
if (!function_exists('al2fb_like_button')) {
	function al2fb_like_button($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_like_button($post, false);
	}
}

// Template tag for Facebook like box
if (!function_exists('al2fb_like_box')) {
	function al2fb_like_box($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_like_button($post, true);
	}
}

// Template tag for Facebook send button
if (!function_exists('al2fb_send_button')) {
	function al2fb_send_button($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_send_button($post);
	}
}

// Template tag for Facebook comments plugins
if (!function_exists('al2fb_comments_plugin')) {
	function al2fb_comments_plugin($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_comments_plugin($post);
	}
}

// Template tag for Facebook face pile
if (!function_exists('al2fb_face_pile')) {
	function al2fb_face_pile($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_face_pile($post);
	}
}

// Template tag for profile link
if (!function_exists('al2fb_profile_link')) {
	function al2fb_profile_link($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_profile_link($post);
	}
}

// Template tag for Facebook registration
if (!function_exists('al2fb_registration')) {
	function al2fb_registration($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_registration($post);
	}
}

// Template tag for Facebook login
if (!function_exists('al2fb_login')) {
	function al2fb_login($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_login($post);
	}
}

// Template tag for Facebook activity feed
if (!function_exists('al2fb_activity_feed')) {
	function al2fb_activity_feed($post_ID = null) {
		if (empty($post_ID))
			global $post;
		else
			$post = get_post($post_ID);
		if (isset($post))
			echo WPAL2Int::Get_activity_feed($post);
	}
}

// User meta per blog (multi site installs)

add_filter('add_user_metadata', 'al2fb_add_user_metadata', 10, 5);
add_filter('update_user_metadata', 'al2fb_update_user_metadata', 10, 5);
add_filter('delete_user_metadata', 'al2fb_delete_user_metadata', 10, 4);
add_filter('get_user_metadata', 'al2fb_get_user_metadata', 10, 4);

if (!function_exists('al2fb_user_meta_prefix')) {
	function al2fb_user_meta_prefix() {
		global $blog_id;
		if (!empty($blog_id) && $blog_id > 1)
		{
			$site_id = false;
			if (is_multisite()) {
				$current_site = get_current_site();
				$site_id = $current_site->id;
			}
			if ($site_id && $site_id > 1)
				return 'blog_' . $blog_id . '_' . $site_id . '_';
			else
				return 'blog_' . $blog_id . '_';
		}
		else
			return false;
	}
}

if (!function_exists('al2fb_add_user_metadata')) {
	function al2fb_add_user_metadata($meta_type = null, $user_id, $meta_key, $meta_value, $unique = false) {
		$prefix = al2fb_user_meta_prefix();
		if ($prefix && strpos($meta_key, 'al2fb_') === 0)
			return add_user_meta($user_id, $prefix . $meta_key, $meta_value, $unique);
		return null;
	}
}

if (!function_exists('al2fb_update_user_metadata')) {
	function al2fb_update_user_metadata($meta_type = null, $user_id, $meta_key, $meta_value, $prev_value = '') {
		$prefix = al2fb_user_meta_prefix();
		if ($prefix && strpos($meta_key, 'al2fb_') === 0)
			return update_user_meta($user_id, $prefix . $meta_key, $meta_value, $prev_value);
		return null;
	}
}

if (!function_exists('al2fb_delete_user_metadata')) {
	function al2fb_delete_user_metadata($meta_type = null, $user_id, $meta_key, $meta_value = '') {
		$prefix = al2fb_user_meta_prefix();
		if ($prefix && strpos($meta_key, 'al2fb_') === 0)
			return delete_user_meta($user_id, $prefix . $meta_key, $meta_value);
		return null;
	}
}

if (!function_exists('al2fb_get_user_metadata')) {
	function al2fb_get_user_metadata($meta_type = null, $user_id, $meta_key, $single = false) {
		$prefix = al2fb_user_meta_prefix();
		if ($prefix && strpos($meta_key, 'al2fb_') === 0)
			return get_user_meta($user_id, $prefix . $meta_key, $single);
		return null;
	}
}

?>
