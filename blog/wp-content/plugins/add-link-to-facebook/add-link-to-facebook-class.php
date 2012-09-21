<?php

/*
	Support class Add Link to Facebook plugin
	Copyright (c) 2011, 2012 by Marcel Bokhorst
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

require_once('add-link-to-facebook-const.php');

// Define class
if (!class_exists('WPAL2Facebook')) {
	class WPAL2Facebook {
		// Class variables
		var $main_file = null;
		var $debug = null;
		var $site_id = '';
		var $blog_id = '';

		// Constructor
		function __construct() {
			global $wp_version, $blog_id;

			// Get main file name
			$this->main_file = str_replace('-class', '', __FILE__);

			// Log
			$this->debug = get_option(c_al2fb_option_debug);

			// Get site & blog id
			if (is_multisite()) {
				$current_site = get_current_site();
				$this->site_id = $current_site->id;
			}
			$this->blog_id = $blog_id;

			// register for de-activation
			register_deactivation_hook($this->main_file, array(&$this, 'Deactivate'));

			// Register actions
			add_action('init', array(&$this, 'Init'), 0);
			if (is_admin()) {
				add_action('admin_menu', array(&$this, 'Admin_menu'));
				add_filter('plugin_action_links', array(&$this, 'Plugin_action_links'), 10, 2);
				add_action('admin_notices', array(&$this, 'Admin_notices'));
				add_action('post_submitbox_misc_actions', array(&$this, 'Post_submitbox_misc_actions'));
				add_filter('manage_posts_columns', array(&$this, 'Manage_posts_columns'));
				add_action('manage_posts_custom_column', array(&$this, 'Manage_posts_custom_column'), 10, 2);
				add_filter('manage_pages_columns', array(&$this, 'Manage_posts_columns'));
				add_action('manage_pages_custom_column', array(&$this, 'Manage_posts_custom_column'), 10, 2);
				add_action('add_meta_boxes', array(&$this, 'Add_meta_boxes'));
				add_action('personal_options', array(&$this, 'Personal_options'));
				add_action('personal_options_update', array(&$this, 'Personal_options_update'));
				add_action('edit_user_profile_update', array(&$this, 'Personal_options_update'));
			}

			add_action('transition_post_status', array(&$this, 'Transition_post_status'), 10, 3);
			add_action('xmlrpc_publish_post', array(&$this, 'Remote_publish'));
			add_action('app_publish_post', array(&$this, 'Remote_publish'));
			add_action('future_to_publish', array(&$this, 'Future_to_publish'));
			add_action('before_delete_post', array(&$this, 'Before_delete_post'));
			add_action('al2fb_publish', array(&$this, 'Remote_publish'));

			if (get_option(c_al2fb_option_use_pp))
				add_action('publish_post', array(&$this, 'Remote_publish'));

			add_action('comment_post', array(&$this, 'Comment_post'), 999);
			add_action('comment_unapproved_to_approved', array(&$this, 'Comment_approved'));
			add_action('comment_approved_to_unapproved', array(&$this, 'Comment_unapproved'));
			add_action('trash_comment', array(&$this, 'Comment_trash'));
			add_action('untrash_comment', array(&$this, 'Comment_untrash'));
			add_action('spam_comment',  array(&$this, 'Comment_spam'));
			add_action('unspam_comment',  array(&$this, 'Comment_unspam'));
			add_action('delete_comment', array(&$this, 'Delete_comment'));

			$fprio = intval(get_option(c_al2fb_option_filter_prio));
			if ($fprio <= 0)
				$fprio = 999;

			// Content
			add_action('wp_head', array(&$this, 'WP_head'));
			add_filter('the_content', array(&$this, 'The_content'), $fprio);
			add_filter('bbp_get_topic_content', array(&$this, 'The_content'), $fprio);
			add_filter('bbp_get_reply_content', array(&$this, 'The_content'), $fprio);
			add_filter('comments_array', array(&$this, 'Comments_array'), 10, 2);
			add_filter('get_comments_number', array(&$this, 'Get_comments_number'), 10, 2);
			add_filter('comment_class', array(&$this, 'Comment_class'));
			add_filter('get_avatar', array(&$this, 'Get_avatar'), 10, 5);

			// Shortcodes
			add_shortcode('al2fb_likers', array(&$this, 'Shortcode_likers'));
			add_shortcode('al2fb_anchor', array(&$this, 'Shortcode_anchor'));
			add_shortcode('al2fb_like_count', array(&$this, 'Shortcode_like_count'));
			add_shortcode('al2fb_like_button', array(&$this, 'Shortcode_like_button'));
			add_shortcode('al2fb_like_box', array(&$this, 'Shortcode_like_box'));
			add_shortcode('al2fb_send_button', array(&$this, 'Shortcode_send_button'));
			add_shortcode('al2fb_comments_plugin', array(&$this, 'Shortcode_comments_plugin'));
			add_shortcode('al2fb_face_pile', array(&$this, 'Shortcode_face_pile'));
			add_shortcode('al2fb_profile_link', array(&$this, 'Shortcode_profile_link'));
			add_shortcode('al2fb_registration', array(&$this, 'Shortcode_registration'));
			add_shortcode('al2fb_login', array(&$this, 'Shortcode_login'));
			add_shortcode('al2fb_activity_feed', array(&$this, 'Shortcode_activity_feed'));
			if (get_option(c_al2fb_option_shortcode_widget))
				add_filter('widget_text', 'do_shortcode');

			// Custom filters
			add_filter('al2fb_excerpt', array(&$this, 'Filter_excerpt'), 10, 2);
			add_filter('al2fb_content', array(&$this, 'Filter_content'), 10, 2);
			add_filter('al2fb_comment', array(&$this, 'Filter_comment'), 10, 3);
			add_filter('al2fb_fb_feed', array(&$this, 'Filter_feed'), 10, 1);
			add_filter('al2fb_preprocess_comment', array(&$this, 'Preprocess_comment'), 10, 2);
			add_filter('al2fb_video', array(&$this, 'Filter_video'), 10, 2);

			// Widget
			add_action('widgets_init', create_function('', 'return register_widget("AL2FB_Widget");'));
			if (!is_admin())
				add_action('wp_print_styles', array(&$this, 'WP_print_styles'));

			// Cron
			add_filter('cron_schedules', array(&$this, 'Cron_schedules'));

			// Misc.
			add_filter('puc_request_info_result-add-link-to-facebook', array(&$this, 'Update'), 10, 2);
		}

		// Handle plugin activation
		function Activate() {
			self::Upgrade();
		}

		function Upgrade() {
			global $wpdb;
			$version = get_option(c_al2fb_option_version);
			if (empty($version))
				update_option(c_al2fb_option_siteurl, true);
			if ($version <= 1) {
				delete_option(c_al2fb_meta_client_id);
				delete_option(c_al2fb_meta_app_secret);
				delete_option(c_al2fb_meta_access_token);
				delete_option(c_al2fb_meta_picture_type);
				delete_option(c_al2fb_meta_picture);
				delete_option(c_al2fb_meta_page);
				delete_option(c_al2fb_meta_donated);
			}
			if ($version <= 2) {
				$rows = $wpdb->get_results("SELECT user_id, meta_value FROM " . $wpdb->usermeta . " WHERE meta_key='al2fb_integrate'");
				foreach ($rows as $row) {
					update_user_meta($row->user_id, c_al2fb_meta_fb_comments, $row->meta_value);
					update_user_meta($row->user_id, c_al2fb_meta_fb_likes, $row->meta_value);
					delete_user_meta($row->user_id, 'al2fb_integrate');
				}
			}
			if ($version <= 3) {
				global $wpdb;
				$rows = $wpdb->get_results("SELECT ID FROM " . $wpdb->users);
				foreach ($rows as $row)
					update_user_meta($row->ID, c_al2fb_meta_like_faces, true);
			}
			if ($version <= 4) {
				$rows = $wpdb->get_results("SELECT user_id, meta_value FROM " . $wpdb->usermeta . " WHERE meta_key='" . c_al2fb_meta_trailer . "'");
				foreach ($rows as $row) {
					$value = get_user_meta($row->user_id, c_al2fb_meta_trailer, true);
					update_user_meta($row->user_id, c_al2fb_meta_trailer, ' ' . $value);
				}
			}
			if ($version <= 5) {
				if (!get_option(c_al2fb_option_css))
					update_option(c_al2fb_option_css,
'.al2fb_widget_comments { }
.al2fb_widget_comments li { }
.al2fb_widget_picture { width: 32px; height: 32px; }
.al2fb_widget_name { }
.al2fb_widget_comment { }
.al2fb_widget_date { font-size: smaller; }
');
			}
			if ($version <= 7) {
				update_option(c_al2fb_option_noshortcode, true);
				update_option(c_al2fb_option_nofilter, true);
			}
			if ($version <= 8)
				update_option(c_al2fb_option_nofilter_comments, true);

			if ($version < 10)
				update_option(c_al2fb_option_version, 10);

			// 11 when authorizing with 10

			if ($version == 11) {
				//update_option(c_al2fb_option_uselinks, true);
				update_option(c_al2fb_option_version, 12);
			}
		}

		// Handle plugin deactivation
		function Deactivate() {
			// Stop cron job
			wp_clear_scheduled_hook('al2fb_cron');

			// Cleanup data
			if (get_option(c_al2fb_option_clean)) {
				global $wpdb;
				// Delete options
				$rows = $wpdb->get_results("SELECT option_name FROM " . $wpdb->options . " WHERE option_name LIKE 'al2fb_%'");
				foreach ($rows as $row)
					delete_option($row->option_name);

				// Delete user meta values
				$rows = $wpdb->get_results("SELECT user_id, meta_key FROM " . $wpdb->usermeta . " WHERE meta_key LIKE 'al2fb_%'");
				foreach ($rows as $row)
					delete_user_meta($row->user_id, $row->meta_key);
			}
		}

		// Initialization
		function Init() {
			// I18n
			load_plugin_textdomain(c_al2fb_text_domain, false, dirname(plugin_basename(__FILE__)) . '/language/');

			// Image request
			if (isset($_GET['al2fb_image'])) {
				$img = dirname(__FILE__) . '/wp-blue-s.png';
				header('Content-type: image/png');
				readfile($img);
				exit();
			}

			// Data URI request
			if (isset($_GET['al2fb_data_uri'])) {
				$post = get_post($_GET['al2fb_data_uri']);
				$data_uri = self::Get_first_image($post);
				// data:image/png;base64,
				// data:[<MIME-type>][;charset=<encoding>][;base64],<data>
				$semi = strpos($data_uri, ';');
				$comma = strpos($data_uri, ',');
				$content_type = substr($data_uri, 5, $semi - 5);
				$data = substr($data_uri, $comma + 1);
				header('Content-type: ' . $content_type);
				echo base64_decode($data);
				exit();
			}

			// Facebook registration
			if (isset($_REQUEST['al2fb_reg'])) {
				WPAL2Int::Facebook_registration();
				exit();
			}

			// Facebook login
			if (isset($_REQUEST['al2fb_login'])) {
				WPAL2Int::Facebook_login();
				exit();
			}

			// Facebook subscription
			if (isset($_REQUEST['al2fb_subscription'])) {
				self::Handle_fb_subscription();
				exit();
			}

			// Set default capability
			if (!get_option(c_al2fb_option_min_cap))
				update_option(c_al2fb_option_min_cap, 'edit_posts');

			// Enqueue style sheet
			if (is_admin()) {
				$css_name = $this->Change_extension(basename($this->main_file), '-admin.css');
				$css_url = plugins_url($css_name, __FILE__);
				wp_register_style('al2fb_style_admin', $css_url);
				wp_enqueue_style('al2fb_style_admin');
			}
			else {
				$upload_dir = wp_upload_dir();
				$css_name = $this->Change_extension(basename($this->main_file), '.css');
				if (file_exists($upload_dir['basedir'] . '/' . $css_name))
					$css_url = $upload_dir['baseurl'] . '/' . $css_name;
				else if (file_exists(TEMPLATEPATH . '/' . $css_name))
					$css_url = get_bloginfo('template_directory') . '/' . $css_name;
				else
					$css_url = plugins_url($css_name, __FILE__);
				wp_register_style('al2fb_style', $css_url);
				wp_enqueue_style('al2fb_style');
			}

			if (get_option(c_al2fb_option_use_ssp) || is_admin())
				wp_enqueue_script('jquery');

			// Social share privacy
			if (get_option(c_al2fb_option_use_ssp))
				wp_enqueue_script('socialshareprivacy', plugins_url('/js/jquery.socialshareprivacy.js', __FILE__), array('jquery'));

			// Check user capability
			if (current_user_can(get_option(c_al2fb_option_min_cap))) {
				if (is_admin()) {
					// Initiate Facebook authorization
					if (isset($_REQUEST['al2fb_action']) && $_REQUEST['al2fb_action'] == 'init') {
						// Debug info
						update_option(c_al2fb_log_redir_init, date('c'));

						// Get current user
						global $user_ID;
						get_currentuserinfo();

						// Clear cache
						WPAL2Int::Clear_fb_pages_cache($user_ID);
						WPAL2Int::Clear_fb_groups_cache($user_ID);
						WPAL2Int::Clear_fb_friends_cache($user_ID);

						// Redirect
						$auth_url = WPAL2Int::Authorize_url($user_ID);
						try {
							// Check
							if (ini_get('safe_mode') || ini_get('open_basedir') || $this->debug)
								update_option(c_al2fb_log_redir_check, 'No');
							else {
								$response = WPAL2Int::Request($auth_url, '', 'GET');
								update_option(c_al2fb_log_redir_check, date('c'));
							}
							// Redirect
							wp_redirect($auth_url);
							exit();
						}
						catch (Exception $e) {
							// Register error
							update_option(c_al2fb_log_redir_check, $e->getMessage());
							update_option(c_al2fb_last_error, $e->getMessage());
							update_option(c_al2fb_last_error_time, date('c'));
							// Redirect
							if (is_multisite()) {
								global $blog_id;
								$error_url = get_admin_url($blog_id, 'tools.php?page=' . plugin_basename($this->main_file), 'admin');
							}
							else
								$error_url = admin_url('tools.php?page=' . plugin_basename($this->main_file));
							$error_url .= '&al2fb_action=error';
							$error_url .= '&error=' . urlencode($e->getMessage());
							wp_redirect($error_url);
							exit();
						}
					}
				}

				// Handle Facebook authorization
				WPAL2Int::Authorize();
			}

			self::Upgrade();
		}

		// Display admin messages
		function Admin_notices() {
			// Check user capability
			if (current_user_can(get_option(c_al2fb_option_min_cap))) {
				// Get current user
				global $user_ID;
				get_currentuserinfo();

				// Check actions
				if (isset($_REQUEST['al2fb_action'])) {
					// Configuration
					if ($_REQUEST['al2fb_action'] == 'config')
						self::Action_config();

					// Authorization
					else if ($_REQUEST['al2fb_action'] == 'authorize')
						self::Action_authorize();

					// Mail debug info
					else if ($_REQUEST['al2fb_action'] == 'mail')
						self::Action_mail();
				}

				self::Check_config();
			}
		}

		// Save settings
		function Action_config() {
			// Security check
			check_admin_referer(c_al2fb_nonce_form);

			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Default values
			$consts = get_defined_constants(true);
			foreach ($consts['user'] as $name => $value) {
				if (strpos($value, 'al2fb_') === 0 &&
					$value != c_al2fb_meta_trailer &&
					$value != c_al2fb_meta_fb_comments_trailer)
					if (isset($_POST[$value]) && is_string($_POST[$value]))
						$_POST[$value] = trim($_POST[$value]);
					else if (empty($_POST[$value]))
						$_POST[$value] = null;
			}

			if (empty($_POST[c_al2fb_meta_picture_type]))
				$_POST[c_al2fb_meta_picture_type] = 'post';

			// Prevent losing selected page
			if (!self::Is_authorized($user_ID) ||
				(!WPAL2Int::Check_multiple() &&
				get_user_meta($user_ID, c_al2fb_meta_use_groups, true) &&
				get_user_meta($user_ID, c_al2fb_meta_group, true))) {
				$_POST[c_al2fb_meta_page] = get_user_meta($user_ID, c_al2fb_meta_page, true);
				$_POST[c_al2fb_meta_page_extra] = get_user_meta($user_ID, c_al2fb_meta_page_extra, true);
			}

			// Prevent losing selected group
			if (!self::Is_authorized($user_ID) || !get_user_meta($user_ID, c_al2fb_meta_use_groups, true)) {
				$_POST[c_al2fb_meta_group] = get_user_meta($user_ID, c_al2fb_meta_group, true);
				$_POST[c_al2fb_meta_group_extra] = get_user_meta($user_ID, c_al2fb_meta_group_extra, true);
			}

			// Prevent losing selected friends
			if (!self::Is_authorized($user_ID))
				$_POST[c_al2fb_meta_friend_extra] = get_user_meta($user_ID, c_al2fb_meta_friend_extra, true);

			// App ID or secret changed
			if (get_user_meta($user_ID, c_al2fb_meta_client_id, true) != $_POST[c_al2fb_meta_client_id] ||
				get_user_meta($user_ID, c_al2fb_meta_app_secret, true) != $_POST[c_al2fb_meta_app_secret]) {
				delete_user_meta($user_ID, c_al2fb_meta_access_token);
				WPAL2Int::Clear_fb_pages_cache($user_ID);
				WPAL2Int::Clear_fb_groups_cache($user_ID);
				WPAL2Int::Clear_fb_friends_cache($user_ID);
			}

			// Like or send button enabled
			if ((!get_user_meta($user_ID, c_al2fb_meta_post_like_button, true) && !empty($_POST[c_al2fb_meta_post_like_button])) ||
				(!get_user_meta($user_ID, c_al2fb_meta_post_send_button, true) && !empty($_POST[c_al2fb_meta_post_send_button])))
				$_POST[c_al2fb_meta_open_graph] = true;

			// Update user options
			update_user_meta($user_ID, c_al2fb_meta_client_id, $_POST[c_al2fb_meta_client_id]);
			update_user_meta($user_ID, c_al2fb_meta_app_secret, $_POST[c_al2fb_meta_app_secret]);
			update_user_meta($user_ID, c_al2fb_meta_picture_type, $_POST[c_al2fb_meta_picture_type]);
			update_user_meta($user_ID, c_al2fb_meta_picture, $_POST[c_al2fb_meta_picture]);
			update_user_meta($user_ID, c_al2fb_meta_picture_default, $_POST[c_al2fb_meta_picture_default]);
			update_user_meta($user_ID, c_al2fb_meta_picture_size, $_POST[c_al2fb_meta_picture_size]);
			update_user_meta($user_ID, c_al2fb_meta_icon, $_POST[c_al2fb_meta_icon]);
			update_user_meta($user_ID, c_al2fb_meta_page, $_POST[c_al2fb_meta_page]);
			update_user_meta($user_ID, c_al2fb_meta_page_extra, $_POST[c_al2fb_meta_page_extra]);
			update_user_meta($user_ID, c_al2fb_meta_use_groups, $_POST[c_al2fb_meta_use_groups]);
			update_user_meta($user_ID, c_al2fb_meta_group, $_POST[c_al2fb_meta_group]);
			update_user_meta($user_ID, c_al2fb_meta_group_extra, $_POST[c_al2fb_meta_group_extra]);
			update_user_meta($user_ID, c_al2fb_meta_friend_extra, $_POST[c_al2fb_meta_friend_extra]);
			update_user_meta($user_ID, c_al2fb_meta_caption, $_POST[c_al2fb_meta_caption]);
			update_user_meta($user_ID, c_al2fb_meta_msg, $_POST[c_al2fb_meta_msg]);
			update_user_meta($user_ID, c_al2fb_meta_auto_excerpt, $_POST[c_al2fb_meta_auto_excerpt]);
			update_user_meta($user_ID, c_al2fb_meta_shortlink, $_POST[c_al2fb_meta_shortlink]);
			update_user_meta($user_ID, c_al2fb_meta_privacy, $_POST[c_al2fb_meta_privacy]);
			update_user_meta($user_ID, c_al2fb_meta_some_friends, $_POST[c_al2fb_meta_some_friends]);
			update_user_meta($user_ID, c_al2fb_meta_add_new_page, $_POST[c_al2fb_meta_add_new_page]);
			update_user_meta($user_ID, c_al2fb_meta_show_permalink, $_POST[c_al2fb_meta_show_permalink]);
			update_user_meta($user_ID, c_al2fb_meta_social_noexcerpt, $_POST[c_al2fb_meta_social_noexcerpt]);
			update_user_meta($user_ID, c_al2fb_meta_trailer, $_POST[c_al2fb_meta_trailer]);
			update_user_meta($user_ID, c_al2fb_meta_hyperlink, $_POST[c_al2fb_meta_hyperlink]);
			update_user_meta($user_ID, c_al2fb_meta_share_link, $_POST[c_al2fb_meta_share_link]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments, $_POST[c_al2fb_meta_fb_comments]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_trailer, $_POST[c_al2fb_meta_fb_comments_trailer]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_postback, $_POST[c_al2fb_meta_fb_comments_postback]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_only, $_POST[c_al2fb_meta_fb_comments_only]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_copy, $_POST[c_al2fb_meta_fb_comments_copy]);
			update_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, $_POST[c_al2fb_meta_fb_comments_nolink]);
			update_user_meta($user_ID, c_al2fb_meta_fb_likes, $_POST[c_al2fb_meta_fb_likes]);
			update_user_meta($user_ID, c_al2fb_meta_post_likers, $_POST[c_al2fb_meta_post_likers]);
			update_user_meta($user_ID, c_al2fb_meta_post_like_button, $_POST[c_al2fb_meta_post_like_button]);
			update_user_meta($user_ID, c_al2fb_meta_like_nohome, $_POST[c_al2fb_meta_like_nohome]);
			update_user_meta($user_ID, c_al2fb_meta_like_noposts, $_POST[c_al2fb_meta_like_noposts]);
			update_user_meta($user_ID, c_al2fb_meta_like_nopages, $_POST[c_al2fb_meta_like_nopages]);
			update_user_meta($user_ID, c_al2fb_meta_like_noarchives, $_POST[c_al2fb_meta_like_noarchives]);
			update_user_meta($user_ID, c_al2fb_meta_like_nocategories, $_POST[c_al2fb_meta_like_nocategories]);
			update_user_meta($user_ID, c_al2fb_meta_like_layout, $_POST[c_al2fb_meta_like_layout]);
			update_user_meta($user_ID, c_al2fb_meta_like_faces, $_POST[c_al2fb_meta_like_faces]);
			update_user_meta($user_ID, c_al2fb_meta_like_width, $_POST[c_al2fb_meta_like_width]);
			update_user_meta($user_ID, c_al2fb_meta_like_action, $_POST[c_al2fb_meta_like_action]);
			update_user_meta($user_ID, c_al2fb_meta_like_font, $_POST[c_al2fb_meta_like_font]);
			update_user_meta($user_ID, c_al2fb_meta_like_colorscheme, $_POST[c_al2fb_meta_like_colorscheme]);
			update_user_meta($user_ID, c_al2fb_meta_like_link, $_POST[c_al2fb_meta_like_link]);
			update_user_meta($user_ID, c_al2fb_meta_like_top, $_POST[c_al2fb_meta_like_top]);
			update_user_meta($user_ID, c_al2fb_meta_post_send_button, $_POST[c_al2fb_meta_post_send_button]);
			update_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, $_POST[c_al2fb_meta_post_combine_buttons]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_width, $_POST[c_al2fb_meta_like_box_width]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_height, $_POST[c_al2fb_meta_like_box_height]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_border, $_POST[c_al2fb_meta_like_box_border]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_noheader, $_POST[c_al2fb_meta_like_box_noheader]);
			update_user_meta($user_ID, c_al2fb_meta_like_box_nostream, $_POST[c_al2fb_meta_like_box_nostream]);
			update_user_meta($user_ID, c_al2fb_meta_comments_posts, $_POST[c_al2fb_meta_comments_posts]);
			update_user_meta($user_ID, c_al2fb_meta_comments_width, $_POST[c_al2fb_meta_comments_width]);
			update_user_meta($user_ID, c_al2fb_meta_comments_auto, $_POST[c_al2fb_meta_comments_auto]);
			update_user_meta($user_ID, c_al2fb_meta_pile_size, $_POST[c_al2fb_meta_pile_size]);
			update_user_meta($user_ID, c_al2fb_meta_pile_width, $_POST[c_al2fb_meta_pile_width]);
			update_user_meta($user_ID, c_al2fb_meta_pile_rows, $_POST[c_al2fb_meta_pile_rows]);
			update_user_meta($user_ID, c_al2fb_meta_reg_width, $_POST[c_al2fb_meta_reg_width]);
			update_user_meta($user_ID, c_al2fb_meta_login_width, $_POST[c_al2fb_meta_login_width]);
			update_user_meta($user_ID, c_al2fb_meta_reg_success, $_POST[c_al2fb_meta_reg_success]);
			update_user_meta($user_ID, c_al2fb_meta_login_regurl, $_POST[c_al2fb_meta_login_regurl]);
			update_user_meta($user_ID, c_al2fb_meta_login_redir, $_POST[c_al2fb_meta_login_redir]);
			update_user_meta($user_ID, c_al2fb_meta_login_html, $_POST[c_al2fb_meta_login_html]);
			update_user_meta($user_ID, c_al2fb_meta_act_width, $_POST[c_al2fb_meta_act_width]);
			update_user_meta($user_ID, c_al2fb_meta_act_height, $_POST[c_al2fb_meta_act_height]);
			update_user_meta($user_ID, c_al2fb_meta_act_header, $_POST[c_al2fb_meta_act_header]);
			update_user_meta($user_ID, c_al2fb_meta_act_recommend, $_POST[c_al2fb_meta_act_recommend]);
			update_user_meta($user_ID, c_al2fb_meta_open_graph, $_POST[c_al2fb_meta_open_graph]);
			update_user_meta($user_ID, c_al2fb_meta_open_graph_type, $_POST[c_al2fb_meta_open_graph_type]);
			update_user_meta($user_ID, c_al2fb_meta_open_graph_admins, $_POST[c_al2fb_meta_open_graph_admins]);
			update_user_meta($user_ID, c_al2fb_meta_exclude_default, $_POST[c_al2fb_meta_exclude_default]);
			update_user_meta($user_ID, c_al2fb_meta_exclude_default_video, $_POST[c_al2fb_meta_exclude_default_video]);
			update_user_meta($user_ID, c_al2fb_meta_not_post_list, $_POST[c_al2fb_meta_not_post_list]);
			update_user_meta($user_ID, c_al2fb_meta_fb_encoding, $_POST[c_al2fb_meta_fb_encoding]);
			update_user_meta($user_ID, c_al2fb_meta_fb_locale, $_POST[c_al2fb_meta_fb_locale]);
			update_user_meta($user_ID, c_al2fb_meta_donated, $_POST[c_al2fb_meta_donated]);
			update_user_meta($user_ID, c_al2fb_meta_rated, $_POST[c_al2fb_meta_rated]);
			if ($_POST[c_al2fb_meta_rated])
				delete_user_meta($user_ID, c_al2fb_meta_rated0);

			if (isset($_REQUEST['debug'])) {
				if (empty($_POST[c_al2fb_meta_access_token]))
					$_POST[c_al2fb_meta_access_token] = null;
				$_POST[c_al2fb_meta_access_token] = trim($_POST[c_al2fb_meta_access_token]);
				update_user_meta($user_ID, c_al2fb_meta_access_token, $_POST[c_al2fb_meta_access_token]);
				update_user_meta($user_ID, c_al2fb_meta_token_time, date('c'));
			}

			// Update admin options
			if (current_user_can('manage_options')) {
				if (empty($_POST[c_al2fb_option_app_share]))
					$_POST[c_al2fb_option_app_share] = null;
				else
					$_POST[c_al2fb_option_app_share] = $user_ID;
				if (is_multisite())
					update_site_option(c_al2fb_option_app_share, $_POST[c_al2fb_option_app_share]);
				else
					update_option(c_al2fb_option_app_share, $_POST[c_al2fb_option_app_share]);

				update_option(c_al2fb_option_timeout, $_POST[c_al2fb_option_timeout]);
				update_option(c_al2fb_option_nonotice, $_POST[c_al2fb_option_nonotice]);
				update_option(c_al2fb_option_min_cap, $_POST[c_al2fb_option_min_cap]);
				update_option(c_al2fb_option_no_post_submit, $_POST[c_al2fb_option_no_post_submit]);
				update_option(c_al2fb_option_min_cap_comment, $_POST[c_al2fb_option_min_cap_comment]);
				update_option(c_al2fb_option_msg_refresh, $_POST[c_al2fb_option_msg_refresh]);
				update_option(c_al2fb_option_msg_maxage, $_POST[c_al2fb_option_msg_maxage]);
				update_option(c_al2fb_option_cron_enabled, $_POST[c_al2fb_option_cron_enabled]);
				update_option(c_al2fb_option_max_descr, $_POST[c_al2fb_option_max_descr]);
				update_option(c_al2fb_option_max_text, $_POST[c_al2fb_option_max_text]);
				update_option(c_al2fb_option_max_comment, $_POST[c_al2fb_option_max_comment]);
				update_option(c_al2fb_option_exclude_type, $_POST[c_al2fb_option_exclude_type]);
				update_option(c_al2fb_option_exclude_cat, $_POST[c_al2fb_option_exclude_cat]);
				update_option(c_al2fb_option_exclude_tag, $_POST[c_al2fb_option_exclude_tag]);
				update_option(c_al2fb_option_exclude_author, $_POST[c_al2fb_option_exclude_author]);
				update_option(c_al2fb_option_metabox_type, $_POST[c_al2fb_option_metabox_type]);
				update_option(c_al2fb_option_noverifypeer, $_POST[c_al2fb_option_noverifypeer]);
				update_option(c_al2fb_option_use_cacerts, $_POST[c_al2fb_option_use_cacerts]);
				update_option(c_al2fb_option_shortcode_widget, $_POST[c_al2fb_option_shortcode_widget]);
				update_option(c_al2fb_option_noshortcode, $_POST[c_al2fb_option_noshortcode]);
				update_option(c_al2fb_option_nofilter, $_POST[c_al2fb_option_nofilter]);
				update_option(c_al2fb_option_nofilter_comments, $_POST[c_al2fb_option_nofilter_comments]);
				update_option(c_al2fb_option_use_ssp, $_POST[c_al2fb_option_use_ssp]);
				update_option(c_al2fb_option_ssp_info, $_POST[c_al2fb_option_ssp_info]);
				update_option(c_al2fb_option_filter_prio, $_POST[c_al2fb_option_filter_prio]);
				update_option(c_al2fb_option_noasync, $_POST[c_al2fb_option_noasync]);
				update_option(c_al2fb_option_noscript, $_POST[c_al2fb_option_noscript]);
				update_option(c_al2fb_option_uselinks, $_POST[c_al2fb_option_uselinks]);
				update_option(c_al2fb_option_notoken_refresh, $_POST[c_al2fb_option_notoken_refresh]);
				update_option(c_al2fb_option_clean, $_POST[c_al2fb_option_clean]);
				update_option(c_al2fb_option_css, $_POST[c_al2fb_option_css]);
				update_option(c_al2fb_option_login_add_links, $_POST[c_al2fb_option_login_add_links]);

				if (isset($_REQUEST['debug'])) {
					update_option(c_al2fb_option_siteurl, $_POST[c_al2fb_option_siteurl]);
					update_option(c_al2fb_option_nocurl, $_POST[c_al2fb_option_nocurl]);
					update_option(c_al2fb_option_use_pp, $_POST[c_al2fb_option_use_pp]);
					update_option(c_al2fb_option_debug, $_POST[c_al2fb_option_debug]);
				}
			}

			// Show result
			echo '<div id="message" class="updated fade al2fb_notice"><p>' . __('Settings updated', c_al2fb_text_domain) . '</p></div>';

			// Clear all errors
			if ($_POST[c_al2fb_meta_clear_errors]) {
				$query = array(
					'author' => $user_ID,
					'meta_key' => c_al2fb_meta_error,
					'posts_per_page' => -1);
				$posts = new WP_Query($query);
				while ($posts->have_posts()) {
					$posts->next_post();
					delete_post_meta($posts->post->ID, c_al2fb_meta_error);
				}
			}
		}

		// Get token
		function Action_authorize() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Server-side flow authorization
			if (isset($_REQUEST['code'])) {
				try {
					// Get & store token
					WPAL2Int::Get_fb_token($user_ID);
					update_option(c_al2fb_log_auth_time, date('c'));
					if (get_option(c_al2fb_option_version) <= 6)
						update_option(c_al2fb_option_version, 7);
					if (get_option(c_al2fb_option_version) == 10)
						update_option(c_al2fb_option_version, 11);
					delete_option(c_al2fb_last_error);
					delete_option(c_al2fb_last_error_time);
					echo '<div id="message" class="updated fade al2fb_notice"><p>' . __('Authorized, go posting!', c_al2fb_text_domain) . '</p></div>';
				}
				catch (Exception $e) {
					delete_user_meta($user_ID, c_al2fb_meta_access_token);
					update_option(c_al2fb_last_error, $e->getMessage());
					update_option(c_al2fb_last_error_time, date('c'));
					echo '<div id="message" class="error fade al2fb_error"><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, get_bloginfo('charset')) . '</p></div>';
				}
			}

			// Authorization error
			else if (isset($_REQUEST['error'])) {
				delete_user_meta($user_ID, c_al2fb_meta_access_token);
				$faq = 'http://wordpress.org/extend/plugins/add-link-to-facebook/faq/';
				$msg = stripslashes($_REQUEST['error_description']);
				$msg .= ' error: ' . stripslashes($_REQUEST['error']);
				$msg .= ' reason: ' . stripslashes($_REQUEST['error_reason']);
				update_option(c_al2fb_last_error, $msg);
				update_option(c_al2fb_last_error_time, date('c'));
				$msg .= '<br /><br />Most errors are described in <a href="' . $faq . '" target="_blank">the FAQ</a>';
				echo '<div id="message" class="error fade al2fb_error"><p>' . htmlspecialchars($msg, ENT_QUOTES, get_bloginfo('charset')) . '</p></div>';
			}
		}

		// Send debug info
		function Action_mail() {
			// Check security
			check_admin_referer(c_al2fb_nonce_form);
			require_once('add-link-to-facebook-debug.php');

			if (empty($_POST[c_al2fb_mail_topic]) ||
				$_POST[c_al2fb_mail_topic] == 'http://forum.faircode.eu/' ||
				!(strpos($_POST[c_al2fb_mail_topic], 'http://forum.faircode.eu/') === 0))
				echo '<div id="message" class="error fade al2fb_error"><p>' . __('Forum topic link is mandatory', c_al2fb_text_domain) . '</p></div>';
			else {
				// Build headers
				$headers = 'From: ' . stripslashes($_POST[c_al2fb_mail_name]) . ' <' . stripslashes($_POST[c_al2fb_mail_email]) . '>' . "\n";
				$headers .= 'Reply-To: ' . stripslashes($_POST[c_al2fb_mail_name]) . ' <' . stripslashes($_POST[c_al2fb_mail_email]) . '>' . "\n";
				$headers .= 'MIME-Version: 1.0' . "\n";
				$headers .= 'Content-type: text/html; charset=' . get_bloginfo('charset') . "\n";

				// Build message
				$message = '<html><head><title>Add Link to Facebook</title></head><body>';
				$message .= '<p>' . nl2br(htmlspecialchars(stripslashes($_POST[c_al2fb_mail_msg]), ENT_QUOTES, get_bloginfo('charset'))) . '</p>';
				$message .= '<a href="' . stripslashes($_POST[c_al2fb_mail_topic]) . '">' . stripslashes($_POST[c_al2fb_mail_topic]) . '</a>';
				$message .= '<hr />';
				$message .= al2fb_debug_info($this);
				$message .= '<hr />';
				$message .= '</body></html>';
				if (wp_mail('noreply@mail.faircode.eu', '[Add Link to Facebook] Debug information', $message, $headers)) {
					echo '<div id="message" class="updated fade al2fb_notice"><p>' . __('Debug information sent', c_al2fb_text_domain) . '</p></div>';
					if ($this->debug)
						echo '<pre>' . nl2br(htmlspecialchars($headers, ENT_QUOTES, get_bloginfo('charset'))) . '</pre>';
				}
				else
					echo '<div id="message" class="error fade al2fb_error"><p>' . __('Sending debug information failed', c_al2fb_text_domain) . '</p></div>';
			}
		}

		// Display notices
		function Check_config() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			// Check if reporting errors
			$uri = $_SERVER['REQUEST_URI'];
			$url = 'tools.php?page=' . plugin_basename($this->main_file);
			$nonotice = get_option(c_al2fb_option_nonotice);
			if (is_multisite())
				$nonotice = $nonotice || get_site_option(c_al2fb_option_app_share);
			else
				$nonotice = $nonotice || get_option(c_al2fb_option_app_share);
			$donotice = ($nonotice ? strpos($uri, $url) !== false : true);

			if ($donotice) {
				// Check configuration
				if (!get_user_meta($user_ID, c_al2fb_meta_client_id, true) ||
					!get_user_meta($user_ID, c_al2fb_meta_app_secret, true)) {
					$notice = __('needs configuration', c_al2fb_text_domain);
					$anchor = 'configure';
				}
				else if (!self::Is_authorized($user_ID) || get_option(c_al2fb_option_version) == 10) {
					$notice = __('needs authorization', c_al2fb_text_domain);
					$anchor = 'authorize';
				}
				else {
					$version = get_option(c_al2fb_option_version);
					if ($version && $version <= 6) {
						$notice = __('should be authorized again to show Facebook messages in the widget', c_al2fb_text_domain);
						$anchor = 'authorize';
					}
				}

				// Report configuration problems
				if (!empty($notice)) {
					echo '<div class="error fade al2fb_error"><p>';
					_e('Add Link to Facebook', c_al2fb_text_domain);
					echo ' <a href="' . $url . '#' . $anchor . '">' . $notice . '</a></p></div>';
				}
			}

			// Check for post related errors
			global $post;
			$ispost = ($post && strpos($uri, 'post.php') !== false);
			if (!get_option(c_al2fb_option_nonotice) || $donotice || $ispost) {
				$query = array(
					'author' => $user_ID,
					'meta_key' => c_al2fb_meta_error,
					'posts_per_page' => 5);
				if ($ispost)
					$query['p'] = $post->ID;
				$posts = new WP_Query($query);
				while ($posts->have_posts()) {
					$posts->next_post();
					$error = get_post_meta($posts->post->ID, c_al2fb_meta_error, true);
					if (!empty($error)) {
						echo '<div id="message" class="error fade al2fb_error"><p>';
						echo __('Add Link to Facebook', c_al2fb_text_domain) . ' - ';
						edit_post_link(get_the_title($posts->post->ID), null, null, $posts->post->ID);
						echo ': ' . htmlspecialchars($error, ENT_QUOTES, get_bloginfo('charset'));
						echo ' @ ' . get_post_meta($posts->post->ID, c_al2fb_meta_error_time, true);
						echo '</p></div>';
					}
				}
			}

			// Check for error
			if (isset($_REQUEST['al2fb_action']) && $_REQUEST['al2fb_action'] == 'error') {
				$faq = 'http://wordpress.org/extend/plugins/add-link-to-facebook/faq/';
				$msg = htmlspecialchars(stripslashes($_REQUEST['error']), ENT_QUOTES, get_bloginfo('charset'));
				$msg .= '<br /><br />Most errors are described in <a href="' . $faq . '" target="_blank">the FAQ</a>';
				echo '<div id="message" class="error fade al2fb_error"><p>' . $msg . '</p></div>';
			}

			// Check for rating notice
			if ($donotice && !get_user_meta($user_ID, c_al2fb_meta_rated, true)) {
				echo '<div id="message" class="error fade al2fb_error"><p>';
				$msg = __('If you like the Add Link to Facebook plugin, please rate it on <a href="[wordpress]" target="_blank">wordpress.org</a>.<br />If the average rating is low, it makes no sense to support this plugin any longer.<br />You can disable this notice by checking the option "I have rated this plugin" on the <a href="[settings]">settings page</a>.', c_al2fb_text_domain);
				if (get_user_meta($user_ID, c_al2fb_meta_rated0, true)) {
					$msg .= '<br /><br /><em>';
					$msg .= __('Through a mishap on the WordPress.org systems, previous ratings for the plugin were lost.<br />If you\'ve rated the plugin in the past, your rating was accidentally removed.<br />So if you would be so kind as to rate the plugin again, I\'d appreciate it. Thanks!', c_al2fb_text_domain);
					$msg .= '</em>';
				}
				$msg = str_replace('[wordpress]', 'http://wordpress.org/extend/plugins/add-link-to-facebook/', $msg);
				$msg = str_replace('[settings]', $url . '&rate', $msg);
				echo $msg . '</p></div>';
			}

			// Check for multiple count
			$x = WPAL2Int::Get_multiple_count();
			if ($x && $x['blog_count'] > $x['count']) {
				echo '<div id="message" class="error fade al2fb_error"><p>';
				echo __('Maximum number of sites exceeded', c_al2fb_text_domain);
				echo ' (' . $x['blog_count'] . '/' . $x['count'] . ')';
				echo '</p></div>';
			}
		}

		// Register options page
		function Admin_menu() {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			if (function_exists('add_management_page'))
				add_management_page(
					__('Add Link to Facebook', c_al2fb_text_domain) . ' ' . __('Administration', c_al2fb_text_domain),
					__('Add Link to Facebook', c_al2fb_text_domain),
					get_option(c_al2fb_option_min_cap),
					$this->main_file,
					array(&$this, 'Administration'));
		}

		function Plugin_action_links($links, $file) {
			if ($file == plugin_basename($this->main_file)) {
				if (current_user_can(get_option(c_al2fb_option_min_cap))) {
					// Get current user
					global $user_ID;
					get_currentuserinfo();

					// Check for shared app
					if (is_multisite())
						$shared_user_ID = get_site_option(c_al2fb_option_app_share);
					else
						$shared_user_ID = get_option(c_al2fb_option_app_share);
					if (!$shared_user_ID || $shared_user_ID == $user_ID) {
						// Add settings link
						if (is_multisite()) {
							global $blog_id;
							$config_url = get_admin_url($blog_id, 'tools.php?page=' . plugin_basename($this->main_file), 'admin');
						}
						else
							$config_url = admin_url('tools.php?page=' . plugin_basename($this->main_file));
						$links[] = '<a href="' . $config_url . '">' . __('Settings', c_al2fb_text_domain) . '</a>';
					}
				}
			}
			return $links;
		}

		// Handle option page
		function Administration() {
			// Security check
			if (!current_user_can(get_option(c_al2fb_option_min_cap)))
				die('Unauthorized');

			require_once('add-link-to-facebook-admin.php');
			al2fb_render_admin($this);

			global $updates_al2fb;
			if (isset($updates_al2fb))
				$updates_al2fb->checkForUpdates();
		}

		// Add checkboxes
		function Post_submitbox_misc_actions() {
			global $post;

			// Check exclusion
			$ex_custom_types = explode(',', get_option(c_al2fb_option_exclude_type));
			if (in_array($post->post_type, $ex_custom_types))
				return;

			// Security
			if (get_option(c_al2fb_option_no_post_submit) &&
				!current_user_can(get_option(c_al2fb_option_min_cap)))
				return;

			// Get user/link
			$user_ID = self::Get_user_ID($post);
			$link_ids = get_post_meta($post->ID, c_al2fb_meta_link_id, false);
			$charset = get_bloginfo('charset');

			// Get exclude indication
			$exclude = get_post_meta($post->ID, c_al2fb_meta_exclude, true);
			if (!$link_ids && get_user_meta($user_ID, c_al2fb_meta_exclude_default, true))
				$exclude = true;
			$chk_exclude = ($exclude ? ' checked' : '');

			$exclude_video = get_post_meta($post->ID, c_al2fb_meta_exclude_video, true);
			if (!$link_ids && get_user_meta($user_ID, c_al2fb_meta_exclude_default_video, true))
				$exclude_video = true;
			$chk_exclude_video = ($exclude_video ? ' checked' : '');

			// Get no like button indication
			$chk_nolike = (get_post_meta($post->ID, c_al2fb_meta_nolike, true) ? ' checked' : '');
			$chk_nointegrate = (get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) ? ' checked' : '');

			// Check if errors
			$error = get_post_meta($post->ID, c_al2fb_meta_error, true);
?>
			<div class="al2fb_post_submit">
			<div class="misc-pub-section">
			<input type="hidden" id="al2fb_form" name="al2fb_form" value="true">
<?php
			wp_nonce_field(plugin_basename(__FILE__), c_al2fb_nonce_form);

			if (get_option(c_al2fb_option_login_add_links))
				if (self::Is_login_authorized($user_ID, false)) {
					// Get personal page
					try {
						$me = WPAL2Int::Get_fb_me_cached($user_ID, true);
					}
					catch (Exception $e) {
						$me = null;
					}

					// Get other pages
					try {
						$pages = WPAL2Int::Get_fb_pages_cached($user_ID);
					}
					catch (Exception $e) {
						$pages = null;
					}

					// Get groups
					try {
						$groups = WPAL2Int::Get_fb_groups_cached($user_ID);
					}
					catch (Exception $e) {
						$groups = null;
					}

					// Get previous selected page
					$selected_page = get_user_meta($user_ID, c_al2fb_meta_facebook_page, true);

					// Debug info
					if ($this->debug) {
						echo 'sel=' . $selected_page . '<br />';
						echo 'me=' . print_r($me, true) . '<br />';
					}
?>
					<label for="al2fb_page"><?php _e('Add to page:', c_al2fb_text_domain); ?></label>
					<select class="al2fb_select" id="al2fb_page" name="<?php echo c_al2fb_meta_facebook_page; ?>">
<?php
					echo '<option value=""' . ($selected_page ? '' : ' selected') . '>' . __('None', c_al2fb_text_domain) . '</option>';
					if ($me)
						echo '<option value="' . $me->id . '"' . ($selected_page == $me->id ? ' selected' : '') . '>' . htmlspecialchars($me->name, ENT_QUOTES, $charset) . ' (' . __('Personal', c_al2fb_text_domain) . ')</option>';
					if ($pages && $pages->data)
						foreach ($pages->data as $page) {
							echo '<option value="' . $page->id . '"';
							if ($page->id == $selected_page)
								echo ' selected';
							if (empty($page->name))
								$page->name = '?';
							echo '>' . htmlspecialchars($page->name, ENT_QUOTES, $charset) . ' (' . htmlspecialchars($page->category, ENT_QUOTES, $charset) . ')</option>';
						}
					if ($groups && $groups->data)
						foreach ($groups->data as $group) {
							echo '<option value="' . $group->id . '"';
							if ($group->id == $selected_page)
								echo ' selected';
							echo '>' . htmlspecialchars($group->name, ENT_QUOTES, $charset) . ' (' . __('Group', c_al2fb_text_domain) . ')</option>';
						}
?>
					</select>
					<br />
<?php
				}
				else
					echo '<strong>' . __('Not logged in with Facebook (anymore)', c_al2fb_text_domain) . '</strong><br />';
?>
			<input id="al2fb_exclude" type="checkbox" name="<?php echo c_al2fb_meta_exclude; ?>"<?php echo $chk_exclude; ?> />
			<label for="al2fb_exclude"><?php _e('Do not add link to Facebook', c_al2fb_text_domain); ?></label>
			<br />
			<input id="al2fb_exclude_video" type="checkbox" name="<?php echo c_al2fb_meta_exclude_video; ?>"<?php echo $chk_exclude_video; ?> />
			<label for="al2fb_exclude_video"><?php _e('Do not add video to Facebook', c_al2fb_text_domain); ?></label>
			<br />
			<input id="al2fb_nolike" type="checkbox" name="<?php echo c_al2fb_meta_nolike; ?>"<?php echo $chk_nolike; ?> />
			<label for="al2fb_nolike"><?php _e('Do not add like button', c_al2fb_text_domain); ?></label>
			<br />
			<input id="al2fb_nointegrate" type="checkbox" name="<?php echo c_al2fb_meta_nointegrate; ?>"<?php echo $chk_nointegrate; ?> />
			<label for="al2fb_nointegrate"><?php _e('Do not integrate comments', c_al2fb_text_domain); ?></label>

<?php
			if (!empty($link_ids)) {
?>
				<br />
				<input id="al2fb_update" type="checkbox" name="<?php echo c_al2fb_action_update; ?>"/>
				<label for="al2fb_update"><?php _e('Update existing Facebook link', c_al2fb_text_domain); ?></label>
				<br />
				<span class="al2fb_explanation"><strong><?php _e('Comments and likes will be lost!', c_al2fb_text_domain); ?></strong></span>
				<br />
				<input id="al2fb_delete" type="checkbox" name="<?php echo c_al2fb_action_delete; ?>"/>
				<label for="al2fb_delete"><?php _e('Delete existing Facebook link', c_al2fb_text_domain); ?></label>
<?php
				foreach ($link_ids as $link_id) {
					$page_id = WPAL2Int::Get_page_from_link_id($link_id);
					try {
						$info = WPAL2Int::Get_fb_info_cached($user_ID, empty($page_id) ? 'me' : $page_id);
					}
					catch (Exception $e) {
						$info = false;
					}
?>
					<br />
					<a href="<?php echo WPAL2Int::Get_fb_permalink($link_id); ?>" target="_blank"><?php _e('Link on Facebook', c_al2fb_text_domain); ?></a>
<?php
					if ($info)
						echo ' (<a href="' . $info->link . '" target="_blank">' . htmlspecialchars($info->name, ENT_QUOTES, $charset) . '</a>)';
				}
?>
				<br />
				<span class="al2fb_explanation"><em><?php _e('Due to limitations of Facebook not all links might work', c_al2fb_text_domain); ?></em></span>
<?php
			}

			if (!empty($error)) {
?>
				<br />
				<input id="al2fb_clear" type="checkbox" name="<?php echo c_al2fb_action_clear; ?>"/>
				<label for="al2fb_clear"><?php _e('Clear error messages', c_al2fb_text_domain); ?></label>
<?php
			}
?>
			</div>
			</div>
<?php
		}

		// Add post Facebook column
		function Manage_posts_columns($posts_columns) {
			// Get current user
			global $user_ID;
			get_currentuserinfo();

			if (current_user_can(get_option(c_al2fb_option_min_cap)) &&
				!get_user_meta($user_ID, c_al2fb_meta_not_post_list, true))
				$posts_columns['al2fb'] = __('Facebook', c_al2fb_text_domain);
			return $posts_columns;
		}

		function Is_recent($post) {
			// Maximum age for Facebook comments/likes
			$maxage = intval(get_option(c_al2fb_option_msg_maxage));
			if (!$maxage)
				$maxage = 7;

			// Link added time
			$link_time = strtotime(get_post_meta($post->ID, c_al2fb_meta_link_time, true));
			if ($link_time <= 0)
				$link_time = strtotime($post->post_date_gmt);

			$old = ($link_time + ($maxage * 24 * 60 * 60) < time());

			return !$old;
		}

		// Populate post facebook column
		function Manage_posts_custom_column($column_name, $post_ID) {
			if ($column_name == 'al2fb') {
				$charset = get_bloginfo('charset');
				$post = get_post($post_ID);
				$user_ID = self::Get_user_ID($post);

				$link_ids = get_post_meta($post->ID, c_al2fb_meta_link_id, false);
				if ($link_ids)
					foreach ($link_ids as $link_id)
						try {
							$page_id = WPAL2Int::Get_page_from_link_id($link_id);
							$link = WPAL2Int::Get_fb_permalink($link_id);
							$info = WPAL2Int::Get_fb_info_cached($user_ID, $page_id);
							echo '<a href="' . $link . '" target="_blank">' . htmlspecialchars($info->name, ENT_QUOTES, $charset) . '</a><br />';
						}
						catch (Exception $e) {
							echo htmlspecialchars($e->getMessage(), ENT_QUOTES, $charset);
						}
				else
					echo '<span>' . __('No', c_al2fb_text_domain) . '</span>';

				$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
				if ($link_id && self::Is_recent($post)) {
					// Show number of comments
					if (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
						$count = 0;
						$fb_comments = WPAL2Int::Get_comments_or_likes($post, false);
						if (!empty($fb_comments) && !empty($fb_comments->data))
							$count = count($fb_comments->data);
						echo '<span>' . $count . ' ' . __('comments', c_al2fb_text_domain) . '</span><br />';
					}

					// Show number of likes
					if ($post->ping_status == 'open' &&
						get_user_meta($user_ID, c_al2fb_meta_fb_likes, true)) {
						$count = 0;
						$fb_likes = WPAL2Int::Get_comments_or_likes($post, true);
						if (!empty($fb_likes) && !empty($fb_likes->data))
							$count = count($fb_likes->data);
						echo '<span>' . $count . ' ' . __('likes', c_al2fb_text_domain) . '</span><br />';
					}
				}
			}
		}

		// Add post meta box
		function Add_meta_boxes() {
			$types = explode(',', get_option(c_al2fb_option_metabox_type));
			$types[] = 'post';
			$types[] = 'page';
			foreach ($types as $type)
				add_meta_box(
					'al2fb_meta',
					__('Add Link to Facebook', c_al2fb_text_domain),
					array(&$this, 'Meta_box'),
					$type);
		}

		// Display attached image selector
		function Meta_box() {
			global $post;
			if (!empty($post)) {
				$user_ID = self::Get_user_ID($post);
				$texts = self::Get_texts($post);

				// Security
				wp_nonce_field(plugin_basename(__FILE__), c_al2fb_nonce_form);

				if ($this->debug) {
					echo '<strong>Type:</strong> ' . $post->post_type . '<br />';;
					$texts = self::Get_texts($post);
					echo '<strong>Original:</strong> ' . htmlspecialchars($post->post_content, ENT_QUOTES, get_bloginfo('charset')) . '<br />';
					echo '<strong>Processed:</strong> ' . htmlspecialchars($texts['content'], ENT_QUOTES, get_bloginfo('charset')) . '<br />';
					echo '<strong>Video:</strong> ' . htmlspecialchars(self::Get_link_video($post, $user_ID), ENT_QUOTES, get_bloginfo('charset')) . '<br />';
				}

				if (function_exists('wp_get_attachment_image_src')) {
					// Get attached images
					$images = &get_children('post_type=attachment&post_mime_type=image&order=ASC&post_parent=' . $post->ID);
					if (empty($images))
						echo '<span>' . __('No images in the media library for this post', c_al2fb_text_domain) . '</span><br />';
					else {
						// Display image selector
						$image_id = get_post_meta($post->ID, c_al2fb_meta_image_id, true);

						// Header
						echo '<h4>' . __('Select link image:', c_al2fb_text_domain) . '</h4>';
						echo '<div class="al2fb_images">';

						// None
						echo '<div class="al2fb_image">';
						echo '<input type="radio" name="al2fb_image_id" id="al2fb_image_0"';
						if (empty($image_id))
							echo ' checked';
						echo ' value="0">';
						echo '<br />';
						echo '<label for="al2fb_image_0">';
						echo __('None', c_al2fb_text_domain) . '</label>';
						echo '</div>';

						// Images
						if ($images)
							foreach ($images as $attachment_id => $attachment) {
								// Get image size
								$image_size = get_user_meta($user_ID, c_al2fb_meta_picture_size, true);
								if (empty($image_size))
									$image_size = 'medium';

								$picture = wp_get_attachment_image_src($attachment_id, $image_size);

								echo '<div class="al2fb_image">';
								echo '<input type="radio" name="al2fb_image_id" id="al2fb_image_' . $attachment_id . '"';
								if ($attachment_id == $image_id)
									echo ' checked';
								echo ' value="' . $attachment_id . '">';
								echo '<br />';
								echo '<label for="al2fb_image_' . $attachment_id . '">';
								echo '<img src="' . $picture[0] . '" alt=""></label>';
								echo '<br />';
								echo '<span>' . $picture[1] . ' x ' . $picture[2] . '</span>';
								echo '</div>';
							}
						echo '</div>';
					}
				}
				else
					echo 'wp_get_attachment_image_src does not exist';

				// Debug texts
				if ($this->debug)
					echo '<pre>' . print_r($texts, true) . '</pre>';

				// Custom excerpt
				$excerpt = get_post_meta($post->ID, c_al2fb_meta_excerpt, true);
				echo '<h4>' . __('Custom excerpt', c_al2fb_text_domain) . '</h4>';
				echo '<textarea id="al2fb_excerpt" name="al2fb_excerpt" cols="40" rows="1" class="attachmentlinks">';
				echo $excerpt . '</textarea>';

				// Custom text
				$text = get_post_meta($post->ID, c_al2fb_meta_text, true);
				echo '<h4>' . __('Custom text', c_al2fb_text_domain) . '</h4>';
				echo '<textarea id="al2fb_text" name="al2fb_text" cols="40" rows="1" class="attachmentlinks">';
				echo $text . '</textarea>';

				// URL parameters
				$url_param_name = get_post_meta($post->ID, c_al2fb_meta_url_param_name, true);
				$url_param_value = get_post_meta($post->ID, c_al2fb_meta_url_param_value, true);
				echo '<h4>' . __('Extra URL parameter', c_al2fb_text_domain) . '</h4>';
				echo __('For example for Google Anaylytics', c_al2fb_text_domain) . '<br>';
				echo '<input type="text" id="al2fb_url_param_name" name="al2fb_url_param_name" value="' . $url_param_name . '" />';
				echo '&nbsp;=&nbsp;';
				echo '<input type="text" id="al2fb_url_param_value" name="al2fb_url_param_value" value="' . $url_param_value . '" />';

				// Video link
				$video = get_post_meta($post->ID, c_al2fb_meta_video, true);
				echo '<h4>' . __('Video URL', c_al2fb_text_domain) . '</h4>';
				echo '<input type="text" id="al2fb_video" name="al2fb_video" value="' . $video . '" />';

				// Current link picture
				echo '<h4>' . __('Link picture', c_al2fb_text_domain) . '</h4>';

				$picture_info = self::Get_link_picture($post, $user_ID);
				if (!empty($picture_info['picture']))
					echo '<img src="' . $picture_info['picture'] . '" alt="Link picture">';
				if ($this->debug)
					echo '<br /><span style="font-size: smaller;">' . $picture_info['picture_type'] . ': ' . $picture_info['picture'] . '</span>';

				// Error messages
				if ($this->debug) {
					$logs = get_post_meta($post->ID, c_al2fb_meta_log, false);
					echo '<pre>log=' . print_r($logs, true) . '</pre>';

					$logs = get_post_meta($post->ID, c_al2fb_meta_link_id, false);
					echo '<pre>fbid=' . print_r($logs, true) . '</pre>';

					$logs = get_post_meta($post->ID, c_al2fb_meta_fb_comment_id, false);
					echo '<pre>fbcid=' . print_r($logs, true) . '</pre>';
				}
			}
		}

		// Save indications & selected attached image
		function Save_post($post_id) {
			if ($this->debug)
				add_post_meta($post_id, c_al2fb_meta_log, date('c') . ' Save post');

			// Security checks
			$nonce = (isset($_POST[c_al2fb_nonce_form]) ? $_POST[c_al2fb_nonce_form] : null);
			if (!wp_verify_nonce($nonce, plugin_basename(__FILE__)))
				return $post_id;
			if (!current_user_can('edit_post', $post_id))
				return $post_id;

			// Skip auto save
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return $post_id;

			// Check exclusion
			$post = get_post($post_id);
			$ex_custom_types = explode(',', get_option(c_al2fb_option_exclude_type));
			if (in_array($post->post_type, $ex_custom_types))
				return $post_id;

			// Persist selected page
			$user_ID = self::Get_user_ID($post);
			if (get_option(c_al2fb_option_login_add_links) &&
				self::Is_login_authorized($user_ID, false))
				update_user_meta($user_ID, c_al2fb_meta_facebook_page, $_POST[c_al2fb_meta_facebook_page]);

			// Process exclude indication
			if (isset($_POST[c_al2fb_meta_exclude]) && $_POST[c_al2fb_meta_exclude])
				update_post_meta($post_id, c_al2fb_meta_exclude, true);
			else
				delete_post_meta($post_id, c_al2fb_meta_exclude);
			if (isset($_POST[c_al2fb_meta_exclude_video]) && $_POST[c_al2fb_meta_exclude_video])
				update_post_meta($post_id, c_al2fb_meta_exclude_video, true);
			else
				delete_post_meta($post_id, c_al2fb_meta_exclude_video);

			// Process no like indication
			if (isset($_POST[c_al2fb_meta_nolike]) && $_POST[c_al2fb_meta_nolike])
				update_post_meta($post_id, c_al2fb_meta_nolike, true);
			else
				delete_post_meta($post_id, c_al2fb_meta_nolike);

			// Process no integrate indication
			if (isset($_POST[c_al2fb_meta_nointegrate]) && $_POST[c_al2fb_meta_nointegrate])
				update_post_meta($post_id, c_al2fb_meta_nointegrate, true);
			else
				delete_post_meta($post_id, c_al2fb_meta_nointegrate);

			// Clear errors
			if (isset($_POST[c_al2fb_action_clear]) && $_POST[c_al2fb_action_clear]) {
				delete_post_meta($post_id, c_al2fb_meta_error);
				delete_post_meta($post_id, c_al2fb_meta_error_time);
			}

			// Persist data
			if (empty($_POST['al2fb_image_id']))
				delete_post_meta($post_id, c_al2fb_meta_image_id);
			else
				update_post_meta($post_id, c_al2fb_meta_image_id, $_POST['al2fb_image_id']);

			if (isset($_POST['al2fb_excerpt']) && !empty($_POST['al2fb_excerpt']))
				update_post_meta($post_id, c_al2fb_meta_excerpt, trim($_POST['al2fb_excerpt']));
			else
				delete_post_meta($post_id, c_al2fb_meta_excerpt);

			if (isset($_POST['al2fb_text']) && !empty($_POST['al2fb_text']))
				update_post_meta($post_id, c_al2fb_meta_text, trim($_POST['al2fb_text']));
			else
				delete_post_meta($post_id, c_al2fb_meta_text);

			if (isset($_POST['al2fb_url_param_name']) && !empty($_POST['al2fb_url_param_name']))
				update_post_meta($post_id, c_al2fb_meta_url_param_name, trim($_POST['al2fb_url_param_name']));
			else
				delete_post_meta($post_id, c_al2fb_meta_url_param_name);

			if (isset($_POST['al2fb_url_param_value']) && !empty($_POST['al2fb_url_param_value']))
				update_post_meta($post_id, c_al2fb_meta_url_param_value, trim($_POST['al2fb_url_param_value']));
			else
				delete_post_meta($post_id, c_al2fb_meta_url_param_value);

			if (isset($_POST['al2fb_video']) && !empty($_POST['al2fb_video']))
				update_post_meta($post_id, c_al2fb_meta_video, trim($_POST['al2fb_video']));
			else
				delete_post_meta($post_id, c_al2fb_meta_video);
		}

		// Remote publish & custom action
		function Remote_publish($post_ID) {
			if ($this->debug)
				add_post_meta($post_ID, c_al2fb_meta_log, date('c') . ' Remote publish');

			$post = get_post($post_ID);

			// Only if published
			if ($post->post_status == 'publish')
				self::Publish_post($post);
		}

		// Workaround
		function Future_to_publish($post_ID) {
			if ($this->debug)
				add_post_meta($post_ID, c_al2fb_meta_log, date('c') . ' Future to publish');

			$post = get_post($post_ID);

			// Delegate
			self::Transition_post_status('publish', 'future', $post);
		}

		function Before_delete_post($post_ID) {
			if ($this->debug)
				add_post_meta($post_ID, c_al2fb_meta_log, date('c') . ' Before delete post');

			$post = get_post($post_ID);
			$user_ID = self::Get_user_ID($post);
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if (!empty($link_id) &&
				(self::Is_authorized($user_ID) || self::Is_login_authorized($user_ID, false)))
				WPAL2Int::Delete_fb_link($post);
		}

		// Handle post status change
		function Transition_post_status($new_status, $old_status, $post) {
			if ($this->debug)
				add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' ' . $old_status . '->' . $new_status);

			self::Save_post($post->ID);

			$user_ID = self::Get_user_ID($post);
			$update = (isset($_POST[c_al2fb_action_update]) && $_POST[c_al2fb_action_update]);
			$delete = (isset($_POST[c_al2fb_action_delete]) && $_POST[c_al2fb_action_delete]);
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);

			// Security check
			if (self::user_can($user_ID, get_option(c_al2fb_option_min_cap))) {
				// Add, update or delete link
				if ($update || $delete || $new_status == 'trash') {
					if (!empty($link_id) &&
						(self::Is_authorized($user_ID) || self::Is_login_authorized($user_ID, false))) {
						WPAL2Int::Delete_fb_link($post);
						$link_id = null;
					}
				}
				if (!$delete) {
					// Check post status
					if (empty($link_id) &&
						(self::Is_authorized($user_ID) || self::Is_login_authorized($user_ID, true)) &&
						$new_status == 'publish' &&
						($new_status != $old_status || $update ||
						get_post_meta($post->ID, c_al2fb_meta_error, true)))
						self::Publish_post($post);
				}
			}
		}

		// Handle publish post / XML-RPC publish post
		function Publish_post($post) {
			if ($this->debug)
				add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' Publish');

			$user_ID = self::Get_user_ID($post);

			// Checks
			if (self::user_can($user_ID, get_option(c_al2fb_option_min_cap)) &&
				(self::Is_authorized($user_ID) || self::Is_login_authorized($user_ID, true))) {
				// Apply defaults if no form
				if (!isset($_POST['al2fb_form'])) {
					update_post_meta($post->ID, c_al2fb_meta_exclude, get_user_meta($user_ID, c_al2fb_meta_exclude_default, true));
					update_post_meta($post->ID, c_al2fb_meta_exclude_video, get_user_meta($user_ID, c_al2fb_meta_exclude_default_video, true));
				}

				// Check if not added/excluded
				if (!get_post_meta($post->ID, c_al2fb_meta_link_id, true) &&
					!get_post_meta($post->ID, c_al2fb_meta_exclude, true)) {

					$add_new_page = get_user_meta($user_ID, c_al2fb_meta_add_new_page, true);

					// Check if public post
					if (empty($post->post_password) &&
						($post->post_type != 'page' || $add_new_page) &&
						!self::Is_excluded($post))
						if ($post->post_type == 'reply')
							WPAL2Int::Add_fb_link_reply($post);
						else
							WPAL2Int::Add_fb_link($post);
				}
			}
		}

		function Is_excluded($post) {
			return
				self::Is_excluded_post_type($post) ||
				self::Is_excluded_tag($post) ||
				self::Is_excluded_category($post) ||
				self::Is_excluded_author($post);
		}

		function Is_excluded_post_type($post) {
			$ex_custom_types = explode(',', get_option(c_al2fb_option_exclude_type));

			// Compatibility
			$ex_custom_types[] = 'nav_menu_item';
			$ex_custom_types[] = 'recipe';
			$ex_custom_types[] = 'recipeingredient';
			$ex_custom_types[] = 'recipestep';
			$ex_custom_types[] = 'wpcf7_contact_form';
			$ex_custom_types[] = 'feedback';
			$ex_custom_types[] = 'spam';
			$ex_custom_types[] = 'twitter';
			$ex_custom_types[] = 'mscr_ban';
			// bbPress
			$ex_custom_types[] = 'forum';
			//$ex_custom_types[] = 'topic';
			//$ex_custom_types[] = 'reply';
			$ex_custom_types[] = 'tweet';

			$ex_custom_types = apply_filters('al2fb_excluded_post_types', $ex_custom_types);

			return in_array($post->post_type, $ex_custom_types);
		}

		function Is_excluded_tag($post) {
			// Exclude tags
			$exclude_tag = false;
			$tags = get_the_tags($post->ID);
			$excluding_tags = explode(',', get_option(c_al2fb_option_exclude_tag));
			$excluding_tags = apply_filters('al2fb_excluded_tags', $excluding_tags);
			if ($tags)
				foreach ($tags as $tag)
					if (in_array($tag->name, $excluding_tags))
						$exclude_tag = true;
			return $exclude_tag;
		}

		function Is_excluded_category($post) {
			$exclude_category = false;
			$categories = get_the_category($post->ID);
			$excluding_categories = explode(',', get_option(c_al2fb_option_exclude_cat));
			$excluding_categories = apply_filters('al2fb_excluded_categories', $excluding_categories);
			if ($categories)
				foreach ($categories as $category)
					if (in_array($category->cat_ID, $excluding_categories))
						$exclude_category = true;
			return $exclude_category;
		}

		function Is_excluded_author($post) {
			if (empty($post->post_author))
				return false;
			$excluding_authors = explode(',', get_option(c_al2fb_option_exclude_author));
			$excluding_authors = apply_filters('al2fb_excluded_authors', $excluding_authors);
			$author = get_the_author_meta('user_login', $post->post_author);
			return in_array($author, $excluding_authors);
		}

		// Build texts for link/ogp
		function Get_texts($post) {
			$user_ID = self::Get_user_ID($post);

			// Filter excerpt
			$excerpt = get_post_meta($post->ID, c_al2fb_meta_excerpt, true);
			if (empty($excerpt)) {
				$excerpt = $post->post_excerpt;
				if (!get_option(c_al2fb_option_nofilter))
					$excerpt = apply_filters('the_excerpt', $excerpt);
				if (empty($excerpt) && get_user_meta($user_ID, c_al2fb_meta_auto_excerpt, true)) {
					$excerpt = strip_tags(strip_shortcodes($post->post_content));
					$words = explode(' ', $excerpt, 55 + 1);
					if (count($words) > 55) {
						array_pop($words);
						array_push($words, '…');
						$excerpt = implode(' ', $words);
					}
				}
			}
			$excerpt = apply_filters('al2fb_excerpt', $excerpt, $post);

			// Filter post text
			$content = get_post_meta($post->ID, c_al2fb_meta_text, true);
			if (empty($content)) {
				$content = $post->post_content;
				if (!get_option(c_al2fb_option_nofilter))
					$content = apply_filters('the_content', $content);
			}
			$content = apply_filters('al2fb_content', $content, $post);

			// Get body
			$description = '';
			if (get_user_meta($user_ID, c_al2fb_meta_msg, true))
				$description = $content;
			else
				$description = ($excerpt ? $excerpt : $content);

			// Trailer
			$trailer = get_user_meta($user_ID, c_al2fb_meta_trailer, true);
			if ($trailer) {
				// Get maximum FB text size
				$maxlen = get_option(c_al2fb_option_max_descr);
				if (!$maxlen)
					$maxlen = 256;
				// Limit body size
				$description = self::Limit_text_size($description, $trailer, $maxlen);
			}

			// Build result
			$texts = array(
				'excerpt' => $excerpt,
				'content' => $content,
				'description' => $description
			);
			return $texts;
		}

		// Limit text size
		function Limit_text_size($text, $trailer, $maxlen) {
			if (self::_strlen($text) > $maxlen) {
				// Filter HTML
				$trailer = preg_replace('/<[^>]*>/', '', $trailer);

				// Add maximum number of sentences
				$text = trim($text);
				$lines = explode('.', $text);
				if ($lines) {
					$count = 0;
					$text = '';
					foreach ($lines as $sentence) {
						$count++;
						$line = $sentence;
						if ($count < count($lines) || self::_substr($text, -1, 1) == '.')
							$line .= '.';
						if (self::_strlen($text) + self::_strlen($line) + self::_strlen($trailer) < $maxlen)
							$text .= $line;
						else
							break;
					}
					if (empty($text) && count($lines) > 0)
						$text = self::_substr($lines[0], 0, $maxlen - self::_strlen($trailer));

					// Append trailer
					$text .= $trailer;
				}
			}

			return $text;
		}

		// Get link picture
		function Get_link_picture($post, $user_ID) {
			// Get image size
			$image_size = get_user_meta($user_ID, c_al2fb_meta_picture_size, true);
			if (empty($image_size))
				$image_size = 'medium';

			// Get selected image
			$image_id = get_post_meta($post->ID, c_al2fb_meta_image_id, true);
			if (!empty($image_id) && function_exists('wp_get_attachment_image_src')) {
				$picture_type = 'meta';
				$picture = wp_get_attachment_image_src($image_id, $image_size);
				if ($picture)
					$picture = $picture[0]; // url
			}

			if (empty($picture)) {
				// Default picture
				$picture = get_user_meta($user_ID, c_al2fb_meta_picture_default, true);
				if (empty($picture))
					$picture = WPAL2Int::Redirect_uri() . '?al2fb_image=1';

				// Check picture type
				$picture_type = get_user_meta($user_ID, c_al2fb_meta_picture_type, true);
				if ($picture_type == 'media') {
					$images = array_values(get_children('post_type=attachment&post_mime_type=image&order=ASC&post_parent=' . $post->ID));
					if (!empty($images) && function_exists('wp_get_attachment_image_src')) {
						$picture = wp_get_attachment_image_src($images[0]->ID, $image_size);
						if ($picture && $picture[0])
							$picture = $picture[0];
					}
				}
				else if ($picture_type == 'featured') {
					if (current_theme_supports('post-thumbnails') &&
						function_exists('get_post_thumbnail_id') &&
						function_exists('wp_get_attachment_image_src')) {
						$picture_id = get_post_thumbnail_id($post->ID);
						if ($picture_id) {
							if (stripos($picture_id, 'ngg-') !== false && class_exists('nggdb')) {
								$nggMeta = new nggMeta(str_replace('ngg-', '', $picture_id));
								$picture = $nggMeta->image->imageURL;
							}
							else {
								$picture = wp_get_attachment_image_src($picture_id, $image_size);
								if ($picture && $picture[0])
									$picture = $picture[0];
							}
						}
					}
				}
				else if ($picture_type == 'facebook')
					$picture = '';
				else if ($picture_type == 'post' || empty($picture_type)) {
					$picture_post = self::Get_first_image($post);
					if (strpos($picture_post, 'data:') === 0 && strpos($picture_post, 'base64') > 0)
						$picture = WPAL2Int::Redirect_uri() . '?al2fb_data_uri=' . $post->ID;
					else if ($picture_post)
						$picture = $picture_post;
				}
				else if ($picture_type == 'avatar') {
					$userdata = get_userdata($post->post_author);
					$avatar = get_avatar($userdata->user_email);
					if (!empty($avatar))
						if (preg_match('/< *img[^>]*src *= *["\']([^"\']*)["\']/i', $avatar, $matches))
							$picture = $matches[1];
				}
				else if ($picture_type == 'userphoto') {
					$userdata = get_userdata($post->post_author);
					if ($userdata->userphoto_approvalstatus == USERPHOTO_APPROVED) {
						$image_file = $userdata->userphoto_image_file;
						$upload_dir = wp_upload_dir();
						$picture = trailingslashit($upload_dir['baseurl']) . 'userphoto/' . $image_file;
					}
				}
				else if ($picture_type == 'custom') {
					$custom = get_user_meta($user_ID, c_al2fb_meta_picture, true);
					if ($custom)
						$picture = $custom;
				}
			}

			$picture = apply_filters('al2fb_picture', $picture, $post);

			return array(
				'picture' => $picture,
				'picture_type' => $picture_type
			);
		}

		function Get_first_image($post) {
			$content = $post->post_content;
			if (!get_option(c_al2fb_option_nofilter))
				$content = apply_filters('the_content', $content);
			if (preg_match('/< *img[^>]*src *= *["\']([^"\']*)["\']/i', $content, $matches))
				return $matches[1];
			return false;
		}

		// Get link video
		function Get_link_video($post, $user_ID) {
			if (get_post_meta($post->ID, c_al2fb_meta_exclude_video, true))
				return;

			$video = get_post_meta($post->ID, c_al2fb_meta_video, true);
			if (empty($video)) {
				// http://wordpress.org/extend/plugins/vipers-video-quicktags/
				global $VipersVideoQuicktags;
				if (isset($VipersVideoQuicktags)) {
					do_shortcode($post->post_content);
					$video = reset($VipersVideoQuicktags->swfobjects);
					if (!empty($video))
						$video = $video['url'];
				}
			}
			$video = apply_filters('al2fb_video', $video, $post);
			return $video;
		}

		function Filter_excerpt($excerpt, $post) {
			return self::Filter_standard($excerpt, $post);
		}

		function Filter_content($content, $post) {
			return self::Filter_standard($content, $post);
		}

		function Filter_comment($message, $comment, $post) {
			return self::Filter_standard($message, $post);
		}

		// Filter messages
		function Filter_feed($fb_messages) {
			if (isset($fb_messages) && isset($fb_messages->data))
				for ($i = 0; $i < count($fb_messages->data); $i++)
					if ($fb_messages->data[$i]->type != 'status')
						unset($fb_messages->data[$i]);
			return $fb_messages;
		}

		function Filter_standard($text, $post) {
			$user_ID = self::Get_user_ID($post);

			// Execute shortcodes
			if (get_option(c_al2fb_option_noshortcode))
				$text = strip_shortcodes($text);
			else
				$text = do_shortcode($text);

			// http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php

			// Remove scripts
			$text = preg_replace('/<script.+?<\/script>/ims', '', $text);

			// Remove styles
			$text = preg_replace('/<style.+?<\/style>/ims', '', $text);

			// Replace hyperlinks
			if (get_user_meta($user_ID, c_al2fb_meta_hyperlink, true))
				$text = preg_replace('/< *a[^>]*href *= *["\']([^"\']*)["\'][^<]*/i', '$1<a>', $text);

			// Remove image captions
			$text = preg_replace('/<p[^>]*class="wp-caption-text"[^>]*>[^<]*<\/p>/i', '', $text);

			// Get plain texts
			$text = preg_replace('/<[^>]*>/', '', $text);

			// Decode HTML entities
			$text = html_entity_decode($text, ENT_QUOTES, get_bloginfo('charset'));

			// Prevent starting with with space
			$text = trim($text);

			// Truncate text
			if (!empty($text)) {
				$maxtext = get_option(c_al2fb_option_max_text);
				if (!$maxtext)
					$maxtext = 10000;
				$text = self::_substr($text, 0, $maxtext);
			}

			return $text;
		}

		function Filter_video($video, $post) {
			$components = parse_url($video);

			if (isset($components['host'])) {
				// Normalize YouTube URL
				if ($components['host'] == 'www.youtube.com') {
					// http://www.youtube.com/watch?v=RVUxgqH-y4s -> http://www.youtube.com/v/RVUxgqH-y4s
					parse_str($components['query']);
					if (isset($v))
						return $components['scheme'] . '://' . $components['host'] . '/v/' . $v;
				}

				// Normalize Vimeo URL
				if ($components['host'] == 'vimeo.com') {
					// http://vimeo.com/240975 -> http://www.vimeo.com/moogaloop.swf?server=www.vimeo.com&clip_id=240975
					return $components['scheme'] . '://www.' . $components['host'] . '/moogaloop.swf?server=www.vimeo.com&clip_id=' . substr($components['path'], 1);
				}
			}

			return $video;
		}

		// New comment
		function Comment_post($comment_ID) {
			$comment = get_comment($comment_ID);
			if ($comment->comment_approved == '1' &&
				$comment->comment_agent != 'AL2FB')
				WPAL2Int::Add_fb_link_comment($comment);
		}

		// Approved comment
		function Comment_approved($comment) {
			if ($comment->comment_agent != 'AL2FB')
				WPAL2Int::Add_fb_link_comment($comment);
		}

		// Disapproved comment
		function Comment_unapproved($comment) {
			if ($comment->comment_agent != 'AL2FB')
				WPAL2Int::Delete_fb_link_comment($comment);
		}

		function Comment_trash($comment_ID) {
			$comment = get_comment($comment_ID);
			self::Comment_unapproved($comment);
		}

		function Comment_untrash($comment_ID) {
			$comment = get_comment($comment_ID);
			self::Comment_approved($comment);
		}

		function Comment_spam($comment_ID) {
			self::Comment_trash($comment_ID);
		}

		function Comment_unspam($comment_ID) {
			self::Comment_untrash($comment_ID);
		}

		// Permanently delete comment
		function Delete_comment($comment_ID) {
			// Get data
			$comment = get_comment($comment_ID);
			$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);

			// Save Facebook ID to prevent import again
			if (!empty($fb_comment_id))
				if ($comment->comment_agent == 'AL2FB')
					add_post_meta($comment->comment_post_ID, c_al2fb_meta_fb_comment_id, $fb_comment_id, false);
		}

		function Is_authorized($user_ID) {
			return get_user_meta($user_ID, c_al2fb_meta_access_token, true);
		}

		function Is_login_authorized($user_ID, $page_selected) {
			if ($page_selected &&
				!get_user_meta($user_ID, c_al2fb_meta_facebook_page, true))
				return false;

			return WPAL2Int::Get_login_access_token($user_ID);
		}

		// HTML header
		function WP_head() {
			if (is_single() || is_page()) {
				global $post;
				$user_ID = self::Get_user_ID($post);
				if (get_user_meta($user_ID, c_al2fb_meta_open_graph, true)) {
					$charset = get_bloginfo('charset');
					$title = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset'));
					$post_title = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, get_bloginfo('charset'));

					// Get link picture
					$link_picture = get_post_meta($post->ID, c_al2fb_meta_link_picture, true);
					if (empty($link_picture)) {
						$picture_info = self::Get_link_picture($post, $user_ID);
						$picture = $picture_info['picture'];
					}
					else
						$picture = substr($link_picture, strpos($link_picture, '=') + 1);
					if (empty($picture))
						$picture = WPAL2Int::Redirect_uri() . '?al2fb_image=1';

					// Video
					$video = self::Get_link_video($post, $user_ID);

					// Get type
					$ogp_type = get_user_meta($user_ID, c_al2fb_meta_open_graph_type, true);
					if (empty($ogp_type))
						$ogp_type = 'article';

					// Generate meta tags
					echo '<!-- Start AL2FB OGP -->' . PHP_EOL;
					echo '<meta property="og:title" content="' . htmlspecialchars($post_title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;
					echo '<meta property="og:type" content="' . $ogp_type . '" />' . PHP_EOL;
					echo '<meta property="og:image" content="' . $picture . '" />' . PHP_EOL;
					echo '<meta property="og:url" content="' . get_permalink($post->ID) . '" />' . PHP_EOL;
					echo '<meta property="og:site_name" content="' . htmlspecialchars($title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;
					if ($video)
						echo '<meta property="og:video" content="' . $video . '" />' . PHP_EOL;

					$texts = self::Get_texts($post);
					$maxlen = get_option(c_al2fb_option_max_descr);
					$description = self::_substr($texts['description'], 0, $maxlen ? $maxlen : 256);
					echo '<meta property="og:description" content="' . htmlspecialchars($description, ENT_COMPAT, $charset) . '" />' . PHP_EOL;

					$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
					if (!empty($appid))
						echo '<meta property="fb:app_id" content="' . $appid . '" />' . PHP_EOL;

					$admins = get_user_meta($user_ID, c_al2fb_meta_open_graph_admins, true);
					if (!empty($admins))
						echo '<meta property="fb:admins" content="' . $admins . '" />' . PHP_EOL;

					// Facebook i18n
					echo '<meta property="og:locale" content="' . WPAL2Int::Get_locale($user_ID) . '" />' . PHP_EOL;
					echo '<!-- End AL2FB OGP -->' . PHP_EOL;
				}
			}

			else if (is_home())
			{
				// Check if any user has enabled the OGP
				global $wpdb;
				$opg = 0;
				$user_ID = null;
				$rows = $wpdb->get_results("SELECT user_id, meta_value FROM " . $wpdb->usermeta . " WHERE meta_key='" . c_al2fb_meta_open_graph . "'");
				foreach ($rows as $row)
					if ($row->meta_value) {
						$opg++;
						$user_ID = $row->user_id;
					}

				if ($opg) {
					$charset = get_bloginfo('charset');
					$title = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, $charset);
					$description = html_entity_decode(get_bloginfo('description'), ENT_QUOTES, $charset);

					// Get link picture
					$picture_type = get_user_meta($user_ID, c_al2fb_meta_picture_type, true);
					if ($picture_type == 'custom')
						$picture = get_user_meta($user_ID, c_al2fb_meta_picture, true);
					if (empty($picture)) {
						$picture = get_user_meta($user_ID, c_al2fb_meta_picture_default, true);
						if (empty($picture))
							$picture = WPAL2Int::Redirect_uri() . '?al2fb_image=1';
					}

					// Generate meta tags
					echo '<!-- Start AL2FB OGP -->' . PHP_EOL;
					echo '<meta property="og:title" content="' . htmlspecialchars($title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;
					echo '<meta property="og:type" content="blog" />' . PHP_EOL;
					echo '<meta property="og:image" content="' . $picture . '" />' . PHP_EOL;
					echo '<meta property="og:url" content="' . get_home_url() . '" />' . PHP_EOL;
					echo '<meta property="og:site_name" content="' . htmlspecialchars($title, ENT_COMPAT, $charset) . '" />' . PHP_EOL;
					echo '<meta property="og:description" content="' . htmlspecialchars(empty($description) ? $title : $description, ENT_COMPAT, $charset) . '" />' . PHP_EOL;

					// Single user blog
					if ($opg == 1) {
						$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
						if (!empty($appid))
							echo '<meta property="fb:app_id" content="' . $appid . '" />' . PHP_EOL;

						$admins = get_user_meta($user_ID, c_al2fb_meta_open_graph_admins, true);
						if (!empty($admins))
							echo '<meta property="fb:admins" content="' . $admins . '" />' . PHP_EOL;

						// Facebook i18n
						echo '<meta property="og:locale" content="' . WPAL2Int::Get_locale($user_ID) . '" />' . PHP_EOL;
					}
					else
						echo '<meta property="og:locale" content="' . WPAL2Int::Get_locale(-1) . '" />' . PHP_EOL;
					echo '<!-- End AL2FB OGP -->' . PHP_EOL;
				}
			}
		}

		// Additional styles
		function WP_print_styles() {
			$css = get_option(c_al2fb_option_css);
			if (!empty($css)) {
				echo '<!-- AL2FB CSS -->' . PHP_EOL;
				echo '<style type="text/css" media="screen">' . PHP_EOL;
				echo $css;
				echo '</style>' . PHP_EOL;
			}
		}

		// Post content
		function The_content($content = '') {
			global $post;

			// Do not process feed / excerpt
			if (is_feed() || WPAL2Int::in_excerpt())
				return $content;

			$user_ID = self::Get_user_ID($post);
			if (!self::Is_excluded($post) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_nohome, true) && is_home()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_noposts, true) && is_single()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_nopages, true) && is_page()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_noarchives, true) && is_archive()) &&
				!(get_user_meta($user_ID, c_al2fb_meta_like_nocategories, true) && is_category())) {

				// Show likers
				if (get_user_meta($user_ID, c_al2fb_meta_post_likers, true)) {
					$likers = self::Get_likers($post);
					if (!empty($likers))
						if (get_user_meta($user_ID, c_al2fb_meta_like_top, true))
							$content = $likers . $content;
						else
							$content .= $likers;
				}

				// Show permalink
				if (get_user_meta($user_ID, c_al2fb_meta_show_permalink, true)) {
					$anchor = WPAL2Int::Get_fb_anchor($post);
					if (get_user_meta($user_ID, c_al2fb_meta_like_top, true))
						$content = $anchor . $content;
					else
						$content .= $anchor;
				}

				// Show like button
				if (!get_post_meta($post->ID, c_al2fb_meta_nolike, true)) {
					if (get_user_meta($user_ID, c_al2fb_meta_post_like_button, true))
						$button = WPAL2Int::Get_like_button($post, false);
					if (get_user_meta($user_ID, c_al2fb_meta_post_send_button, true) &&
						!get_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, true))
						$button .= WPAL2Int::Get_send_button($post);
				}
				if (!empty($button))
					if (get_user_meta($user_ID, c_al2fb_meta_like_top, true))
						$content = $button . $content;
					else
						$content .= $button;

				// Show comments plugin
				if (get_user_meta($user_ID, c_al2fb_meta_comments_auto, true))
					$content .= WPAL2Int::Get_comments_plugin($post);
			}

			return $content;
		}

		// Shortcode likers names
		function Shortcode_likers($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_likers($post);
			else
				return '';
		}

		// Shortcode amchor
		function Shortcode_anchor($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_fb_anchor($post);
			else
				return '';
		}

		// Shortcode like count
		function Shortcode_like_count($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return self::Get_like_count($post);
			else
				return '';
		}

		// Shortcode like button
		function Shortcode_like_button($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_like_button($post, false);
			else
				return '';
		}

		// Shortcode like box
		function Shortcode_like_box($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_like_button($post, true);
			else
				return '';
		}

		// Shortcode send button
		function Shortcode_send_button($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_send_button($post);
			else
				return '';
		}

		// Shortcode comments plugin
		function Shortcode_comments_plugin($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_comments_plugin($post);
			else
				return '';
		}

		// Shortcode face pile
		function Shortcode_face_pile($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_face_pile($post);
			else
				return '';
		}

		// Shortcode profile link
		function Shortcode_profile_link($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_profile_link($post);
			else
				return '';
		}

		// Shortcode Facebook registration
		function Shortcode_registration($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_registration($post);
			else
				return '';
		}

		// Shortcode Facebook login
		function Shortcode_login($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_login($post);
			else
				return '';
		}

		// Shortcode Facebook activity feed
		function Shortcode_activity_feed($atts) {
			extract(shortcode_atts(array('post_id' => null), $atts));
			if (empty($post_id))
				global $post;
			else
				$post = get_post($post_id);
			if (isset($post))
				return WPAL2Int::Get_activity_feed($post);
			else
				return '';
		}

		// Get HTML for likers
		function Get_likers($post) {
			$likers = '';
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				$charset = get_bloginfo('charset');
				$fb_likes = WPAL2Int::Get_comments_or_likes($post, true);
				if ($fb_likes)
					foreach ($fb_likes->data as $fb_like) {
						if (!empty($likers))
							$likers .= ', ';
						if (get_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, true) == 'author') {
							$link = WPAL2Int::Get_fb_profilelink($fb_like->id);
							$likers .= '<a href="' . $link . '" rel="nofollow">' . htmlspecialchars($fb_like->name, ENT_QUOTES, $charset) . '</a>';
						}
						else
							$likers .= htmlspecialchars($fb_like->name, ENT_QUOTES, $charset);
					}

				if (!empty($likers)) {
					$likers .= ' <span class="al2fb_liked">' . _n('liked this post', 'liked this post', count($fb_likes->data), c_al2fb_text_domain) . '</span>';
					$likers = '<div class="al2fb_likers">' . $likers . '</div>';
				}
			}
			return $likers;
		}

		// Get HTML for like count
		function Get_like_count($post) {
			$user_ID = self::Get_user_ID($post);
			if ($user_ID && !self::Is_excluded($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
				$fb_likes = WPAL2Int::Get_comments_or_likes($post, true);
				if ($fb_likes && count($fb_likes->data) > 0)
					return '<div class="al2fb_like_count"><a href="' . WPAL2Int::Get_fb_permalink($link_id) . '" rel="nofollow">' . count($fb_likes->data) . ' ' . _n('liked this post', 'liked this post', count($fb_likes->data), c_al2fb_text_domain) . '</a></div>';
			}
			return '';
		}

		// Profile personal options
		function Personal_options($user) {
			$fid = get_user_meta($user->ID, c_al2fb_meta_facebook_id, true);
			echo '<th scope="row">' . __('Facebook ID', c_al2fb_text_domain) . '</th><td>';
			echo '<input type="text" name="' . c_al2fb_meta_facebook_id . '" id="' . c_al2fb_meta_facebook_id . '" value="' . $fid . '">';
			if ($fid)
				echo '<br><a href="' . WPAL2Int::Get_fb_profilelink($fid) . '" target="_blank">' . $fid . '</a>';
			if ($this->debug)
				echo '<br><span>token=' . get_user_meta($user->ID, c_al2fb_meta_facebook_token, true) . '</span>';
			echo '</td></tr>';
		}

		// Handle personal options change
		function Personal_options_update($user_id) {
			update_user_meta($user_id, c_al2fb_meta_facebook_id, trim($_REQUEST[c_al2fb_meta_facebook_id]));
		}

		// Modify comment list
		function Comments_array($comments, $post_ID) {
			$post = get_post($post_ID);
			$user_ID = self::Get_user_ID($post);
			update_option(c_al2fb_log_importing, true);

			// Integration?
			if ($user_ID && !self::Is_excluded($post) &&
				$post->post_type != 'reply' &&
				!get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) &&
				$post->comment_status == 'open') {

				// Get time zone offset
				$tz_off = get_option('gmt_offset');
				if (empty($tz_off))
					$tz_off = 0;
				$tz_off = apply_filters('al2fb_gmt_offset', $tz_off);
				$tz_off = $tz_off * 3600;

				// Get Facebook comments
				if (self::Is_recent($post) && get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
					$fb_comments = WPAL2Int::Get_comments_or_likes($post, false);
					if ($fb_comments) {
						// Get WordPress comments
						$stored_comments = get_comments('post_id=' . $post->ID);
						$stored_comments = array_merge($stored_comments,
							get_comments('status=spam&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=trash&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=hold&post_id=' . $post->ID));
						$deleted_fb_comment_ids = get_post_meta($post->ID, c_al2fb_meta_fb_comment_id, false);

						foreach ($fb_comments->data as $fb_comment)
							if (!empty($fb_comment->id)) {
								// Check if stored comment
								$stored = false;
								if ($stored_comments)
									foreach ($stored_comments as $comment) {
										$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
										if ($fb_comment_id == $fb_comment->id) {
											$stored = true;
											break;
										}
									}
								$stored = $stored || in_array($fb_comment->id, $deleted_fb_comment_ids);

								// Create new comment
								if (!$stored) {
									$name = $fb_comment->from->name . ' ' . __('on Facebook', c_al2fb_text_domain);
									if ($post->post_type == 'topic') {
										// bbPress
										$reply_id = bbp_insert_reply(array(
											'post_parent' => $post_ID,
											'post_content' => $fb_comment->message,
											'post_status' => 'draft'
										),
										array(
											'forum_id' => bbp_get_topic_forum_id($post_ID),
											'topic_id' => $post_ID,
											'anonymous_name' => $name
										));

										// Add data
										add_post_meta($reply_id, c_al2fb_meta_link_id, $fb_comment->id);
										add_post_meta($post_ID, c_al2fb_meta_fb_comment_id, $fb_comment->id);

										// Publish
										$reply = array();
										$reply['ID'] = $reply_id;
										$reply['post_status'] = 'publish';
										wp_update_post($reply);
									}
									else {
										$comment_ID = $fb_comment->id;
										$commentdata = array(
											'comment_post_ID' => $post_ID,
											'comment_author' => $name,
											'comment_author_email' => $fb_comment->from->id . '@facebook.com',
											'comment_author_url' => WPAL2Int::Get_fb_profilelink($fb_comment->from->id),
											'comment_author_IP' => '',
											'comment_date' => date('Y-m-d H:i:s', strtotime($fb_comment->created_time) + $tz_off),
											'comment_date_gmt' => date('Y-m-d H:i:s', strtotime($fb_comment->created_time)),
											'comment_content' => $fb_comment->message,
											'comment_karma' => 0,
											'comment_approved' => 1,
											'comment_agent' => 'AL2FB',
											'comment_type' => '', // pingback|trackback
											'comment_parent' => 0,
											'user_id' => 0
										);

										$commentdata = apply_filters('al2fb_preprocess_comment', $commentdata, $post);

										// Copy Facebook comment to WordPress database
										if (get_user_meta($user_ID, c_al2fb_meta_fb_comments_copy, true)) {
											// Apply filters
											if (get_option(c_al2fb_option_nofilter_comments))
												$commentdata['comment_approved'] = '1';
											else {
												$commentdata = apply_filters('preprocess_comment', $commentdata);
												$commentdata = wp_filter_comment($commentdata);
												$commentdata['comment_approved'] = wp_allow_comment($commentdata);
											}

											// Insert comment in database
											$comment_ID = wp_insert_comment($commentdata);
											add_comment_meta($comment_ID, c_al2fb_meta_fb_comment_id, $fb_comment->id);
											do_action('comment_post', $comment_ID, $commentdata['comment_approved']);

											// Notify
											if ('spam' !== $commentdata['comment_approved']) {
												if ('0' == $commentdata['comment_approved'])
													wp_notify_moderator($comment_ID);
												if (get_option('comments_notify') && $commentdata['comment_approved'])
													wp_notify_postauthor($comment_ID, $commentdata['comment_type']);
											}
										}
										else
											$commentdata['comment_approved'] = '1';

										// Add comment to array
										if ($commentdata['comment_approved'] == 1) {
											$new = new stdClass();
											$new->comment_ID = $comment_ID;
											$new->comment_post_ID = $commentdata['comment_post_ID'];
											$new->comment_author = $commentdata['comment_author'];
											$new->comment_author_email = $commentdata['comment_author_email'];
											$new->comment_author_url = $commentdata['comment_author_url'];
											$new->comment_author_ip = $commentdata['comment_author_IP'];
											$new->comment_date = $commentdata['comment_date'];
											$new->comment_date_gmt = $commentdata['comment_date_gmt'];
											$new->comment_content = stripslashes($commentdata['comment_content']);
											$new->comment_karma = $commentdata['comment_karma'];
											$new->comment_approved = $commentdata['comment_approved'];
											$new->comment_agent = $commentdata['comment_agent'];
											$new->comment_type = $commentdata['comment_type'];
											$new->comment_parent = $commentdata['comment_parent'];
											$new->user_id = $commentdata['user_id'];
											$comments[] = $new;
										}
									}
								}
							}
							else
								if ($this->debug)
									add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' Missing FB comment id: ' . print_r($fb_comment, true));
					}
				}

				// Get likes
				if (self::Is_recent($post) &&
					$post->ping_status == 'open' &&
					get_user_meta($user_ID, c_al2fb_meta_fb_likes, true)) {
					$fb_likes = WPAL2Int::Get_comments_or_likes($post, true);
					if ($fb_likes)
						foreach ($fb_likes->data as $fb_like) {
							// Create new virtual comment
							$link = WPAL2Int::Get_fb_profilelink($fb_like->id);
							$new = new stdClass();
							$new->comment_ID = $fb_like->id;
							$new->comment_post_ID = $post_ID;
							$new->comment_author = $fb_like->name . ' ' . __('on Facebook', c_al2fb_text_domain);
							$new->comment_author_email = '';
							$new->comment_author_url = $link;
							$new->comment_author_ip = '';
							$new->comment_date_gmt = date('Y-m-d H:i:s', time());
							$new->comment_date = $new->comment_date_gmt;
							$new->comment_content = '<em>' . __('Liked this post', c_al2fb_text_domain) . '</em>';
							$new->comment_karma = 0;
							$new->comment_approved = 1;
							$new->comment_agent = 'AL2FB';
							$new->comment_type = 'pingback';
							$new->comment_parent = 0;
							$new->user_id = 0;
							$comments[] = $new;
						}
				}

				// Sort comments by time
				if (!empty($fb_comments) || !empty($fb_likes)) {
					usort($comments, array(&$this, 'Comment_compare'));
					if (get_option('comment_order') == 'desc')
						array_reverse($comments);
				}
			}

			// Comment link type
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			$comments_nolink = get_user_meta($user_ID, c_al2fb_meta_fb_comments_nolink, true);
			if (empty($comments_nolink))
				$comments_nolink = 'author';
			else if ($comments_nolink == 'on' || empty($link_id))
				$comments_nolink = 'none';

			if ($comments_nolink == 'none' || $comments_nolink == 'link') {
				$link = WPAL2Int::Get_fb_permalink($link_id);
				if ($comments)
					foreach ($comments as $comment)
						if ($comment->comment_agent == 'AL2FB')
							if ($comments_nolink == 'none')
								$comment->comment_author_url = '';
							else if ($comments_nolink == 'link')
								$comment->comment_author_url = $link;
			}

			// Permission to view?
			$min_cap = get_option(c_al2fb_option_min_cap_comment);
			if ($min_cap && !current_user_can($min_cap))
				if ($comments)
					for ($i = 0; $i < count($comments); $i++)
						if ($comments[$i]->comment_agent == 'AL2FB')
							unset($comments[$i]);

			return $comments;
		}

		// Pre process comment: limit text size
		function Preprocess_comment($commentdata, $post) {
			$user_ID = self::Get_user_ID($post);
			$trailer = get_user_meta($user_ID, c_al2fb_meta_fb_comments_trailer, true);
			if ($trailer) {
				// Get maximum comment text size
				$maxlen = get_option(c_al2fb_option_max_comment);
				if (!$maxlen)
					$maxlen = 256;
				// Limit comment size
				$commentdata['comment_content'] = self::Limit_text_size($commentdata['comment_content'], $trailer, $maxlen);
			}
			return $commentdata;
		}

		// Sort helper
		function Comment_compare($a, $b) {
			return strcmp($a->comment_date_gmt, $b->comment_date_gmt);
		}

		// Get comment count with FB comments/likes
		function Get_comments_number($count, $post_ID) {
			$post = get_post($post_ID);
			$user_ID = self::Get_user_ID($post);

			// Permission to view?
			$min_cap = get_option(c_al2fb_option_min_cap_comment);
			if ($min_cap && !current_user_can($min_cap)) {
				$stored_comments = get_comments('post_id=' . $post->ID);
				if ($stored_comments)
					foreach ($stored_comments as $comment)
						if ($comment->comment_agent == 'AL2FB')
							$count--;
			}

			// Integration turned off?
			if (!$user_ID || self::Is_excluded($post) ||
				get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) ||
				$post->comment_status != 'open')
				return $count;

			if (self::Is_recent($post)) {
				// Comment count
				if (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
					$fb_comments = WPAL2Int::Get_comments_or_likes($post, false);
					if ($fb_comments) {
						$stored_comments = get_comments('post_id=' . $post->ID);
						$stored_comments = array_merge($stored_comments,
							get_comments('status=spam&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=trash&post_id=' . $post->ID));
						$stored_comments =  array_merge($stored_comments,
							get_comments('status=hold&post_id=' . $post->ID));
						$deleted_fb_comment_ids = get_post_meta($post->ID, c_al2fb_meta_fb_comment_id, false);

						foreach ($fb_comments->data as $fb_comment) {
							// Check if comment in database
							$stored = false;
							if ($stored_comments)
								foreach ($stored_comments as $comment) {
									$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
									if ($fb_comment_id == $fb_comment->id) {
										$stored = true;
										break;
									}
								}

							// Check if comment deleted
							$stored = $stored || in_array($fb_comment->id, $deleted_fb_comment_ids);

							// Only count if not in database or deleted
							if (!$stored)
								$count++;
						}
					}
				}

				// Like count
				if (self::Is_recent($post) &&
					$post->ping_status == 'open' &&
					get_user_meta($user_ID, c_al2fb_meta_fb_likes, true))
					$fb_likes = WPAL2Int::Get_comments_or_likes($post, true);
				if (!empty($fb_likes))
					$count += count($fb_likes->data);
			}

			return $count;
		}

		// Annotate FB comments/likes
		function Comment_class($classes) {
			global $comment;
			if (!empty($comment) && $comment->comment_agent == 'AL2FB')
				$classes[] = 'facebook-comment';
			return $classes;
		}

		// Get FB picture as avatar
		function Get_avatar($avatar, $id_or_email, $size, $default) {
			if (is_object($id_or_email)) {
				$comment = $id_or_email;
				if ($comment->comment_agent == 'AL2FB' &&
					($comment->comment_type == '' || $comment->comment_type == 'comment')) {

					// Get picture url
					$id = explode('id=', $comment->comment_author_url);
					if (count($id) == 2) {
						$fb_picture_url = WPAL2Int::Get_fb_picture_url_cached($id[1], 'normal');

						// Build avatar image
						if ($fb_picture_url) {
							$avatar = '<img alt="' . esc_attr($comment->comment_author) . '"';
							$avatar .= ' src="' . $fb_picture_url . '"';
							$avatar .= ' class="avatar avatar-' . $size . ' photo al2fb"';
							$avatar .= ' height="' . $size . '"';
							$avatar .= ' width="' . $size . '"';
							$avatar .= ' />';
						}
					}
				}
			}
			return $avatar;
		}

		function Get_user_ID($post) {
			if (is_multisite())
				$shared_user_ID = get_site_option(c_al2fb_option_app_share);
			else
				$shared_user_ID = get_option(c_al2fb_option_app_share);
			if ($shared_user_ID)
				return $shared_user_ID;
			return $post->post_author;
		}

		function user_can($user, $capability) {
			if (!is_object($user))
				$user = new WP_User($user);

			if (!$user || !$user->ID)
				return false;

			$args = array_slice(func_get_args(), 2 );
			$args = array_merge(array($capability), $args);

			return call_user_func_array(array(&$user, 'has_cap'), $args);
		}

		// Add cron schedule
		function Cron_schedules($schedules) {
			if (get_option(c_al2fb_option_cron_enabled)) {
				$duration = WPAL2Int::Get_duration(false);
				$schedules['al2fb_schedule'] = array(
					'interval' => $duration,
					'display' => __('Add Link to Facebook', c_al2fb_text_domain));
			}
			return $schedules;
		}

		function Cron_filter($where = '') {
			$maxage = intval(get_option(c_al2fb_option_msg_maxage));
			if (!$maxage)
				$maxage = 7;

			return $where . " AND post_date > '" . date('Y-m-d', strtotime('-' . $maxage . ' days')) . "'";
		}

		function Cron() {
			$posts = 0;
			$comments = 0;
			$likes = 0;

			// Query recent posts
			add_filter('posts_where', array(&$this, 'Cron_filter'));
			$query = new WP_Query('post_type=any&meta_key=' . c_al2fb_meta_link_id);
			remove_filter('posts_where', array(&$this, 'Cron_filter'));

			while ($query->have_posts()) {
				$posts++;
				$query->the_post();
				$post = $query->post;

				// Integration?
				if (!get_post_meta($post->ID, c_al2fb_meta_nointegrate, true) &&
					$post->comment_status == 'open') {
					$user_ID = self::Get_user_ID($post);

					// Get Facebook comments
					if (get_user_meta($user_ID, c_al2fb_meta_fb_comments, true)) {
						$fb_comments = WPAL2Int::Get_comments_or_likes($post, false, false);
						$comments += count($fb_comments->data);
					}

					// Get likes
					if ($post->ping_status == 'open' &&
						get_user_meta($user_ID, c_al2fb_meta_fb_likes, true)) {
						$fb_likes = WPAL2Int::Get_comments_or_likes($post, true, false);
						$likes += count($fb_likes->data);
					}
				}
			}

			// Debug info
			update_option(c_al2fb_option_cron_time, date('c'));
			update_option(c_al2fb_option_cron_posts, $posts);
			update_option(c_al2fb_option_cron_comments, $comments);
			update_option(c_al2fb_option_cron_likes, $likes);
		}

		function Update($pluginInfo, $result) {
			if (isset($pluginInfo->disable))
				update_option(c_al2fb_option_multiple_disable, $pluginInfo->disable);
			else
				delete_option(c_al2fb_option_multiple_disable);
			return $pluginInfo;
		}

		// String helpers
		function _strlen($str) {
			if (function_exists('mb_strlen'))
				return mb_strlen($str);
			else
				return strlen($str);
		}

		function _substr($str, $start, $length) {
			if (function_exists('mb_substr'))
				return mb_substr($str, $start, $length);
			else
				return substr($str, $start, $length);
		}

		// Check environment
		static function Check_prerequisites() {
			// Check WordPress version
			global $wp_version;
			if (version_compare($wp_version, '3.0') < 0)
				die('Add Link to Facebook requires at least WordPress 3.0');

			// Check basic prerequisities
			self::Check_function('add_action');
			self::Check_function('add_filter');
			self::Check_function('wp_register_style');
			self::Check_function('wp_enqueue_style');
			self::Check_function('file_get_contents');
			self::Check_function('json_decode');
			self::Check_function('md5');
		}

		static function Check_function($name) {
			if (!function_exists($name))
				die('Required WordPress function "' . $name . '" does not exist');
		}

		// Change file extension
		function Change_extension($filename, $new_extension) {
			return preg_replace('/\..+$/', $new_extension, $filename);
		}
	}
}

?>
