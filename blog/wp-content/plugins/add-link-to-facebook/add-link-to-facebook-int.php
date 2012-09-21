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
if (!class_exists('WPAL2Int')) {
	class WPAL2Int {
		static $php_error = '';

		static function Get_main_file() {
			return str_replace('-int', '', __FILE__);
		}

		static function Get_plugin_url() {
			// Get plugin url
			$plugin_url = WP_PLUGIN_URL . '/' . basename(dirname(WPAL2Int::Get_main_file()));
			if (strpos($plugin_url, 'http') === 0 && is_ssl())
				$plugin_url = str_replace('http://', 'https://', $plugin_url);
			return $plugin_url;
		}

		// Get Facebook authorize address
		static function Authorize_url($user_ID) {
			// http://developers.facebook.com/docs/authentication/permissions
			$url = 'https://graph.facebook.com/oauth/authorize';
			$url = apply_filters('al2fb_url', $url);
			$url .= '?client_id=' . urlencode(get_user_meta($user_ID, c_al2fb_meta_client_id, true));
			$url .= '&redirect_uri=' . urlencode(WPAL2Int::Redirect_uri());
			$url .= '&scope=read_stream,publish_stream,offline_access,manage_pages,user_groups';
			$url .= '&state=' . WPAL2Int::Authorize_secret();
			return $url;
		}

		// Get Facebook return addess
		static function Redirect_uri() {
			// WordPress Address -> get_site_url() -> WordPress folder
			// Blog Address -> get_home_url() -> Home page
			if (get_option(c_al2fb_option_siteurl))
				return trailingslashit(get_site_url());
			else
				return trailingslashit(get_home_url());
		}

		// Generate authorization secret
		static function Authorize_secret() {
			return 'al2fb_auth_' . substr(md5(AUTH_KEY ? AUTH_KEY : get_bloginfo('url')), 0, 10);
		}

		// Handle Facebook authorization
		static function Authorize() {
			parse_str($_SERVER['QUERY_STRING'], $query);
			if (isset($query['state']) && strpos($query['state'], WPAL2Int::Authorize_secret()) !== false) {
				// Build new url
				$query['state'] = '';
				$query['al2fb_action'] = 'authorize';
				if (is_multisite()) {
					global $blog_id;
					$url = get_admin_url($blog_id, 'tools.php?page=' . plugin_basename(WPAL2Int::Get_main_file()), 'admin');
				}
				else
					$url = admin_url('tools.php?page=' . plugin_basename(WPAL2Int::Get_main_file()));
				$url .= '&' . http_build_query($query, '', '&');

				// Debug info
				update_option(c_al2fb_log_redir_time, date('c'));
				update_option(c_al2fb_log_redir_ref, (empty($_SERVER['HTTP_REFERER']) ? null : $_SERVER['HTTP_REFERER']));
				update_option(c_al2fb_log_redir_from, $_SERVER['REQUEST_URI']);
				update_option(c_al2fb_log_redir_to, $url);

				// Redirect
				wp_redirect($url);
				exit();
			}
		}

		// Request token
		static function Get_fb_token($user_ID) {
			$url = 'https://graph.facebook.com/oauth/access_token';
			$url = apply_filters('al2fb_url', $url);
			$query = http_build_query(array(
				'client_id' => get_user_meta($user_ID, c_al2fb_meta_client_id, true),
				'redirect_uri' => WPAL2Int::Redirect_uri(),
				'client_secret' => get_user_meta($user_ID, c_al2fb_meta_app_secret, true),
				'code' => $_REQUEST['code']
			), '', '&');
			update_option(c_al2fb_log_get_token, $url . '?' . $query);
			$response = WPAL2Int::Request($url, $query, 'GET');
			$access_token = WPAL2Int::Process_fb_token($response);
			update_user_meta($user_ID, c_al2fb_meta_access_token, $access_token);
			update_user_meta($user_ID, c_al2fb_meta_token_time, date('c'));
			return $access_token;
		}

		static function Refresh_fb_token($user_ID) {
			// https://developers.facebook.com/docs/offline-access-deprecation/
			$token = WPAL2Int::Get_access_token($user_ID);
			if ($token) {
				$url = 'https://graph.facebook.com/oauth/access_token';
				$url = apply_filters('al2fb_url', $url);
				$query = http_build_query(array(
					'client_id' => get_user_meta($user_ID, c_al2fb_meta_client_id, true),
					'client_secret' => get_user_meta($user_ID, c_al2fb_meta_app_secret, true),
					'grant_type' => 'fb_exchange_token',
					'fb_exchange_token' => $token
				), '', '&');
				$response = WPAL2Int::Request($url, $query, 'GET');
				$access_token = WPAL2Int::Process_fb_token($response);
				update_user_meta($user_ID, c_al2fb_meta_access_token, $access_token);
				update_user_meta($user_ID, c_al2fb_meta_token_time, date('c'));
				return $access_token;
			}
			return false;
		}

		static function Process_fb_token($response) {
			$key = 'access_token=';
			$access_token = substr($response, strpos($response, $key) + strlen($key));
			$access_token = explode('&', $access_token);
			$access_token = $access_token[0];
			return $access_token;
		}

		static function Get_fb_application_cached($user_ID) {
			global $blog_id;
			$app_key = c_al2fb_transient_cache . md5('app' . $blog_id . $user_ID);
			$app = get_transient($app_key);
			if (get_option(c_al2fb_option_debug))
				$app = false;
			if ($app === false) {
				$app = WPAL2Int::Get_fb_application($user_ID);
				$duration = WPAL2Int::Get_duration(false);
				set_transient($app_key, $app, $duration);
			}
			return $app;
		}

		// Get application properties
		static function Get_fb_application($user_ID) {
			$app_id = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
			$url = 'https://graph.facebook.com/' . $app_id;
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token($user_ID);
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$app = json_decode($response);
			return $app;
		}

		// Get wall, page or group name and cache
		static function Get_fb_me_cached($user_ID, $self) {
			$page_id = WPAL2Int::Get_page_id($user_ID, $self);
			return WPAL2Int::Get_fb_info_cached($user_ID, $page_id);
		}

		// Get wall, page or group name
		static function Get_fb_me($user_ID, $self) {
			$page_id = WPAL2Int::Get_page_id($user_ID, $self);
			return WPAL2Int::Get_fb_info($user_ID, $page_id);
		}

		static function Get_fb_info_cached($user_ID, $page_id) {
			global $blog_id;
			$info_key = c_al2fb_transient_cache . md5('inf' . $blog_id . $user_ID . $page_id);
			$info = get_transient($info_key);
			if (get_option(c_al2fb_option_debug))
				$info = false;
			if ($info === false) {
				$info = WPAL2Int::Get_fb_info($user_ID, $page_id);
				$duration = WPAL2Int::Get_duration(false);
				set_transient($info_key, $info, $duration);
			}
			return $info;
		}

		static function Get_fb_info($user_ID, $page_id) {
			$url = 'https://graph.facebook.com/' . $page_id;
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token_by_page($user_ID, $page_id);
			if (empty($token))
				return null;
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$me = json_decode($response);
			if ($me) {
				if (empty($me->link) && $page_id != 'me' && empty($me->category))	// Group
					$me->link = 'http://www.facebook.com/home.php?sk=group_' . $page_id;
				return $me;
			}
			else
				throw new Exception('Page "' . $page_id . '" not found');
		}

		static function Get_fb_pages_cached($user_ID) {
			global $blog_id;
			$pages_key = c_al2fb_transient_cache . md5('pgs' . $blog_id . $user_ID);
			$pages = get_transient($pages_key);
			if (get_option(c_al2fb_option_debug))
				$pages = false;
			if ($pages === false) {
				$pages = WPAL2Int::Get_fb_pages($user_ID);
				$duration = WPAL2Int::Get_duration(false);
				set_transient($pages_key, $pages, $duration);
			}
			return $pages;
		}

		static function Clear_fb_pages_cache($user_ID) {
			global $blog_id;
			$pages_key = c_al2fb_transient_cache . md5('pgs' . $blog_id . $user_ID);
			delete_transient($pages_key);
		}

		// Get page list
		static function Get_fb_pages($user_ID) {
			$url = 'https://graph.facebook.com/me/accounts';
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token($user_ID);
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$accounts = json_decode($response);
			return $accounts;
		}

		static function Get_fb_groups_cached($user_ID) {
			global $blog_id;
			$groups_key = c_al2fb_transient_cache . md5('grp' . $blog_id . $user_ID);
			$groups = get_transient($groups_key);
			if (get_option(c_al2fb_option_debug))
				$groups = false;
			if ($groups === false) {
				$groups = WPAL2Int::Get_fb_groups($user_ID);
				$duration = WPAL2Int::Get_duration(false);
				set_transient($groups_key, $groups, $duration);
			}
			return $groups;
		}

		static function Clear_fb_groups_cache($user_ID) {
			global $blog_id;
			$groups_key = c_al2fb_transient_cache . md5('grp' . $blog_id . $user_ID);
			delete_transient($groups_key);
		}

		// Get group list
		static function Get_fb_groups($user_ID) {
			$url = 'https://graph.facebook.com/me/groups';
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token($user_ID);
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$groups = json_decode($response);
			return $groups;
		}

		static function Get_fb_friends_cached($user_ID) {
			global $blog_id;
			$friends_key = c_al2fb_transient_cache . md5('frnd' . $blog_id . $user_ID);
			$friends = get_transient($friends_key);
			if (get_option(c_al2fb_option_debug))
				$friends = false;
			if ($friends === false) {
				$friends = WPAL2Int::Get_fb_friends($user_ID);
				$duration = WPAL2Int::Get_duration(false);
				set_transient($friends_key, $friends, $duration);
			}
			return $friends;
		}

		static function Clear_fb_friends_cache($user_ID) {
			global $blog_id;
			$friends_key = c_al2fb_transient_cache . md5('frnd' . $blog_id . $user_ID);
			delete_transient($friends_key);
		}

		// Get friend list
		static function Get_fb_friends($user_ID) {
			$url = 'https://graph.facebook.com/me/friends';
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token($user_ID);
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$friends = json_decode($response);
			return $friends;
		}

		static function Get_fb_permissions($user_ID, $id) {
			$url = 'https://graph.facebook.com/' . $id . '/permissions';
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token($user_ID);
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$permissions = json_decode($response);
			return $permissions;
		}

		// Get comments and cache
		static function Get_fb_comments_cached($user_ID, $link_id, $cached = true) {
			global $blog_id;
			$fb_key = c_al2fb_transient_cache . md5( 'c' . $blog_id . $user_ID . $link_id);
			$fb_comments = get_transient($fb_key);
			if (get_option(c_al2fb_option_debug) || !$cached)
				$fb_comments = false;
			if ($fb_comments === false) {
				$fb_comments = WPAL2Int::Get_fb_comments($user_ID, $link_id);
				$duration = WPAL2Int::Get_duration(true);
				set_transient($fb_key, $fb_comments, $duration);
			}
			return $fb_comments;
		}

		// Get comments
		static function Get_fb_comments($user_ID, $id) {
			$url = 'https://graph.facebook.com/' . $id . '/comments';
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token($user_ID);
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$comments = json_decode($response);
			$comments = apply_filters('al2fb_fb_comments', $comments);
			if ($comments)
				foreach ($comments->data as $comment) {
					$comment->message = WPAL2Int::Convert_encoding($user_ID, $comment->message, true);
					$comment->from->name = WPAL2Int::Convert_encoding($user_ID, $comment->from->name, true);
				}
			return $comments;
		}

		// Get likes and cache
		static function Get_fb_likes_cached($user_ID, $link_id, $cached = true) {
			global $blog_id;
			$fb_key = c_al2fb_transient_cache . md5('l' . $blog_id . $user_ID . $link_id);
			$fb_likes = get_transient($fb_key);
			if (get_option(c_al2fb_option_debug) || !$cached)
				$fb_likes = false;
			if ($fb_likes === false) {
				$fb_likes = WPAL2Int::Get_fb_likes($user_ID, $link_id);
				$duration = WPAL2Int::Get_duration(true);
				set_transient($fb_key, $fb_likes, $duration);
			}
			return $fb_likes;
		}

		// Get likes
		static function Get_fb_likes($user_ID, $id) {
			$url = 'https://graph.facebook.com/' . $id . '/likes';
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token($user_ID);
			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$likes = json_decode($response);
			$likes = apply_filters('al2fb_fb_likes', $likes);
			return $likes;
		}

		// Get messages and cache
		static function Get_fb_feed_cached($user_ID) {
			global $blog_id;
			$page_id = WPAL2Int::Get_page_id($user_ID, false);
			$fb_key = c_al2fb_transient_cache . md5( 'f' . $blog_id . $user_ID . $page_id);
			$fb_feed = get_transient($fb_key);
			if (get_option(c_al2fb_option_debug))
				$fb_feed = false;
			if ($fb_feed === false) {
				$fb_feed = WPAL2Int::Get_fb_feed($user_ID);
				$duration = WPAL2Int::Get_duration(false);
				set_transient($fb_key, $fb_feed, $duration);
			}
			return $fb_feed;
		}

		// Get messages
		static function Get_fb_feed($user_ID) {
			$page_id = WPAL2Int::Get_page_id($user_ID, false);
			$url = 'https://graph.facebook.com/' . $page_id . '/feed';
			$url = apply_filters('al2fb_url', $url);
			$token = WPAL2Int::Get_access_token_by_page($user_ID, $page_id);
			if (empty($token))
				return null;

			$query = http_build_query(array('access_token' => $token), '', '&');
			$response = WPAL2Int::Request($url, $query, 'GET');
			$posts = json_decode($response);
			$posts = apply_filters('al2fb_fb_feed', $posts);
			return $posts;
		}

		// Get Facebook picture
		static function Get_fb_picture_url_cached($id, $size) {
			$fb_key = c_al2fb_transient_cache . md5('p' . $id);
			$fb_url = get_transient($fb_key);
			if (get_option(c_al2fb_option_debug))
				$fb_url = false;
			if ($fb_url === false) {
				$fb_url = WPAL2Int::Get_fb_picture_url($id, 'normal');
				$duration = WPAL2Int::Get_duration(false);
				set_transient($fb_key, $fb_url, $duration);
			}
			return $fb_url;
		}

		// Get Facebook picture
		// Returns a HTTP 302 with the URL of the user's profile picture
		// (use ?type=square | small | normal | large to request a different photo)
		static function Get_fb_picture_url($id, $size) {
			$url = 'https://graph.facebook.com/' . $id . '/picture?' . $size;
			$url = apply_filters('al2fb_url', $url);
			if (function_exists('curl_init') && !get_option(c_al2fb_option_nocurl)) {
				$timeout = get_option(c_al2fb_option_timeout);
				if (!$timeout)
					$timeout = 25;

				$c = curl_init();
				curl_setopt($c, CURLOPT_URL, $url);
				curl_setopt($c, CURLOPT_HEADER, 1);
				curl_setopt($c, CURLOPT_NOBODY, 1);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
				if (get_option(c_al2fb_option_noverifypeer))
					curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
				else if (get_option(c_al2fb_option_use_cacerts))
					curl_setopt($c, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
				$headers = curl_exec($c);
				curl_close ($c);
				if (preg_match('/Location: (.*)/', $headers, $location)) {
					$location = trim($location[1]);
					$location = apply_filters('al2fb_fb_picture', $location);
					return $location;
				}
				else
					return false;
			}
			else if (function_exists('get_headers') && ini_get('allow_url_fopen')) {
				delete_option(c_al2fb_log_ua);
				$ua = $_SERVER['HTTP_USER_AGENT'];
				if (!empty($ua)) {
					ini_set('user_agent', $ua);
					update_option(c_al2fb_log_ua, $ua);
				}

				$headers = get_headers($url, true);
				if (isset($headers['Location'])) {
					$location = $headers['Location'];
					$location = apply_filters('al2fb_fb_picture', $location);
					return $location;
				}
				else
					return false;
			}
			else
				return false;
		}

		// Get link to Facebook profile
		static function Get_fb_profilelink($id) {
			if (empty($id))
				return '';
			return 'http://www.facebook.com/profile.php?id=' . $id;
		}

		static function Get_page_from_link_id($link_id) {
			if (empty($link_id))
				return '';
			$ids = explode('_', $link_id);
			return $ids[0];
		}

		static function Get_story_from_link_id($link_id) {
			if (empty($link_id))
				return '';
			$ids = explode('_', $link_id);
			return (count($ids) > 1 ? $ids[1] : $ids[0]);
		}

		// Get permalink to added link
		static function Get_fb_permalink($link_id) {
			if (empty($link_id))
				return '';
			$ids = explode('_', $link_id);
			return 'http://www.facebook.com/permalink.php?story_fbid=' . $ids[1] . '&id=' . $ids[0];
		}

		static function Get_fb_anchor($post) {
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if (empty($link_id))
				return '';

			$link = WPAL2Int::Get_fb_permalink($link_id);
			$title = __('Facebook', c_al2fb_text_domain);
			$title = apply_filters('al2fb_anchor', $title, $post);
			return '<div class="al2fb_anchor"><a href="' . $link . '" target="_blank">' . $title . '</div></a>';
		}

		static function Get_page_ids($user_ID) {
			$page_ids = array();

			// Groups
			if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true) && !get_option(c_al2fb_option_uselinks)) {
				$group = get_user_meta($user_ID, c_al2fb_meta_group, true);
				if (!empty($group)) {
					$page_ids[] = $group;
					if (WPAL2Int::Check_multiple()) {
						$extra = get_user_meta($user_ID, c_al2fb_meta_group_extra, true);
						if (is_array($extra))
							$page_ids = array_merge($page_ids, $extra);
						else if (!empty($extra))
							$page_ids[] = $extra;
					}
				}
			}

			// Pages
			if (empty($page_ids) || WPAL2Int::Check_multiple()) {
				$page = get_user_meta($user_ID, c_al2fb_meta_page, true);
				if ($page != '-')
					$page_ids[] = $page;
				if (WPAL2Int::Check_multiple()) {
					$extra = get_user_meta($user_ID, c_al2fb_meta_page_extra, true);
					if (is_array($extra))
						$page_ids = array_merge($page_ids, $extra);
					else if (!empty($extra))
						$page_ids[] = $extra;
				}
			}

			// Friends
			if (WPAL2Int::Check_multiple() && !get_option(c_al2fb_option_uselinks)) {
				$extra = get_user_meta($user_ID, c_al2fb_meta_friend_extra, true);
				if (is_array($extra))
					$page_ids = array_merge($page_ids, $extra);
				else if (!empty($extra))
					$page_ids[] = $extra;
			}

			// Default personal wall
			if (empty($page_ids))
				$page_ids[] = 'me';

			$page_ids = apply_filters('al2fb_page_ids', $page_ids, $user_ID);

			return $page_ids;
		}

		// Add Link to Facebook
		static function Add_fb_link($post) {
			$user_ID = WPAL2Facebook::Get_user_ID($post);

			// Get link URL
			if (get_user_meta($user_ID, c_al2fb_meta_shortlink, true))
				$link = wp_get_shortlink($post->ID);
			if (empty($link))
				$link = get_permalink($post->ID);
			$url_param_name = get_post_meta($post->ID, c_al2fb_meta_url_param_name, true);
			$url_param_value = get_post_meta($post->ID, c_al2fb_meta_url_param_value, true);
			if (!empty($url_param_name))
				$link = add_query_arg($url_param_name, $url_param_value, $link);
			$link = apply_filters('al2fb_link', $link, $post);

			// Get processed texts
			$texts = WPAL2Facebook::Get_texts($post);
			$excerpt = $texts['excerpt'];
			$content = $texts['content'];
			$description = $texts['description'];
			if (!$description)
				$description = ' ';

			// Convert character sets if needed
			$excerpt = WPAL2Int::Convert_encoding($user_ID, $excerpt);
			$content = WPAL2Int::Convert_encoding($user_ID, $content);
			$description = WPAL2Int::Convert_encoding($user_ID, $description);

			// Get name
			$name = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, get_bloginfo('charset'));
			$name = WPAL2Int::Convert_encoding($user_ID, $name);
			$name = apply_filters('al2fb_name', $name, $post);

			// Get caption
			$caption = '';
			if (get_user_meta($user_ID, c_al2fb_meta_caption, true)) {
				$caption = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset'));
				$caption = WPAL2Int::Convert_encoding($user_ID, $caption);
			}
			$caption = apply_filters('al2fb_caption', $caption, $post);

			// Get link picture
			$picture_info = WPAL2Facebook::Get_link_picture($post, $user_ID);
			$picture = $picture_info['picture'];
			$picture_type = $picture_info['picture_type'];

			// Get user note
			$message = '';
			if (get_user_meta($user_ID, c_al2fb_meta_msg, true)) {
				$message = $excerpt;
				if (empty($message))
					$message = html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset'));
				$message = WPAL2Int::Convert_encoding($user_ID, $message);
			}
			$message = apply_filters('al2fb_message', $message, $post);

			// Get wall
			$login = false;
			if (get_option(c_al2fb_option_login_add_links) &&
				WPAL2Int::Get_login_access_token($user_ID) &&
				get_user_meta($user_ID, c_al2fb_meta_facebook_page, true)) {
				$login = true;
				$page_ids = array();
				$page_ids[] = get_user_meta($user_ID, c_al2fb_meta_facebook_page, true);
			}
			else
				$page_ids = WPAL2Int::Get_page_ids($user_ID);

			// Build request
			$query_array = array(
				'link' => $link,
				'name' => $name,
				'caption' => $caption,
				'description' => $description,
				'message' => $message,
				'ref' => 'AL2FB'
				//'scrape' => 'true'
			);

			// Add home link
			$actions = array(
				name => __('Website', c_al2fb_text_domain),
				link => get_home_url());
			$query_array['actions'] = json_encode($actions);

			// Add video
			$video = WPAL2Facebook::Get_link_video($post, $user_ID);
			if (!empty($video)) {
				$query_array['source'] = $video;
				// Picture is mandatory
				if (!$picture)
					$picture = WPAL2Int::Redirect_uri() . '?al2fb_image=1';
			}

			// Add picture
			if ($picture)
				$query_array['picture'] = $picture;

			// Add icon
			$icon = get_user_meta($user_ID, c_al2fb_meta_icon, true);
			$icon = apply_filters('al2fb_icon', $icon, $post);
			if ($icon)
				$query_array['icon'] = $icon;

			// Add share link (overwrites how link)
			if (get_user_meta($user_ID, c_al2fb_meta_share_link, true)) {
				// http://forum.developers.facebook.net/viewtopic.php?id=50049
				// http://bugs.developers.facebook.net/show_bug.cgi?id=9075
				$actions = array(
					'name' => __('Share', c_al2fb_text_domain),
					'link' => 'http://www.facebook.com/share.php?u=' . urlencode($link) . '&t=' . rawurlencode($name)
				);
				$query_array['actions'] = json_encode($actions);
			}

			// Get me info (needed for malformed link id's)
			try {
				$me = WPAL2Int::Get_fb_me_cached($user_ID, true);
			}
			catch (Exception $e) {
				update_post_meta($post->ID, c_al2fb_meta_error, 'Get me: ' . $e->getMessage());
				update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
				return;
			}

			// Add link
			foreach ($page_ids as $page_id) {
				// Do not disturb WordPress
				try {
					// https://developers.facebook.com/docs/reference/api/user/#posts
					// https://developers.facebook.com/docs/reference/api/post/
					// https://developers.facebook.com/docs/reference/dialogs/feed/

					// Personal page
					if (empty($page_id))
						$page_id = 'me';

					// Get access tokendevded
					$token = WPAL2Int::Get_access_token_by_page($user_ID, $page_id);
					if ($token) {
						// Get URL
						$url = 'https://graph.facebook.com/' . $page_id . (get_option(c_al2fb_option_uselinks) ? '/links' : '/feed');
						$url = apply_filters('al2fb_url', $url);

						// Add privacy option
						if ($page_id == 'me') {
							$privacy = get_user_meta($user_ID, c_al2fb_meta_privacy, true);
							if ($privacy) {
								$p = array('value' => $privacy);
								if ($privacy == 'SOME_FRIENDS') {
									$p['value'] = 'CUSTOM';
									$p['friends'] = 'SOME_FRIENDS';
									$p['allow'] = get_user_meta($user_ID, c_al2fb_meta_some_friends, true);
									$p['deny'] = '';
								}
								$query_array['privacy'] = json_encode($p);
							}
						}
						else if (isset($query_array['privacy']))
							unset($query_array['privacy']);

						// Get access token
						$query_array['access_token'] = $token;

						// Build query
						$query = http_build_query($query_array, '', '&');

						// Log request
						update_option(c_al2fb_last_request, print_r($query_array, true) . $query);
						update_option(c_al2fb_last_request_time, date('c'));
						update_option(c_al2fb_last_texts, print_r($texts, true) . $query);
						if (get_option(c_al2fb_option_debug)) {
							add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' ' . $url . ' request=' . print_r($query_array, true));
							add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' texts=' . print_r($texts, true));
						}

						// Execute request
						$response = WPAL2Int::Request($url, $query, 'POST');

						// Log response
						update_option(c_al2fb_last_response, $response);
						update_option(c_al2fb_last_response_time, date('c'));
						if (get_option(c_al2fb_option_debug))
							add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' ' . $url . ' response=' . $response);

						// Decode response
						$fb_link = json_decode($response);

						// Workaround for some links
						if (strpos($fb_link->id, '_') === false)
							$fb_link->id = ($page_id == 'me' ? $me->id : $page_id) . '_' . $fb_link->id;

						// Register link/date
						add_post_meta($post->ID, c_al2fb_meta_link_id, $fb_link->id);
						update_post_meta($post->ID, c_al2fb_meta_link_time, date('c'));
						update_post_meta($post->ID, c_al2fb_meta_link_picture, $picture_type . '=' . $picture);
						delete_post_meta($post->ID, c_al2fb_meta_error);
						delete_post_meta($post->ID, c_al2fb_meta_error_time);
					}
				}
				catch (Exception $e) {
					update_post_meta($post->ID, c_al2fb_meta_error, 'Add link: ' . $e->getMessage());
					update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
					update_post_meta($post->ID, c_al2fb_meta_link_picture, $picture_type . '=' . $picture);
				}
			}

			// Auto refresh access token
			if (!$login && !get_option(c_al2fb_option_notoken_refresh))
				try {
					WPAL2Int::Refresh_fb_token($user_ID);
				}
				catch (Exception $e) {
					update_post_meta($post->ID, c_al2fb_meta_error, 'Refresh token: ' . $e->getMessage());
					update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
				}
		}

		// Convert charset
		static function Convert_encoding($user_ID, $text, $import = false) {
			$blog_encoding = get_option('blog_charset');
			$fb_encoding = get_user_meta($user_ID, c_al2fb_meta_fb_encoding, true);
			if (empty($fb_encoding))
				$fb_encoding = 'UTF-8';

			if ($blog_encoding != $fb_encoding && function_exists('mb_convert_encoding'))
				if ($import)
					return @mb_convert_encoding($text, $blog_encoding, $fb_encoding);
				else
					return @mb_convert_encoding($text, $fb_encoding, $blog_encoding);
			else
				return $text;
		}

		// Delete Link from Facebook
		static function Delete_fb_link($post) {
			$user_ID = WPAL2Facebook::Get_user_ID($post);

			// Delete added links
			$link_ids = get_post_meta($post->ID, c_al2fb_meta_link_id, false);
			foreach ($link_ids as $link_id) {
				// Do not disturb WordPress
				try {
					$url = 'https://graph.facebook.com/' . $link_id;
					$url = apply_filters('al2fb_url', $url);

					// Decode link id
					$ids = explode('_', $link_id);
					$page_id = $ids[0];

					// Build request
					$query = http_build_query(array(
						'access_token' => WPAL2Int::Get_access_token_by_page($user_ID, $page_id),
						'method' => 'delete'
					), '', '&');

					if (get_option(c_al2fb_option_debug))
						add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' ' . $url . ' request=' . print_r($query, true));

					// Execute request
					$response = WPAL2Int::Request($url, $query, 'POST');

					if (get_option(c_al2fb_option_debug))
						add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' ' . $url . ' response=' . $response);

					// Delete meta data
					delete_post_meta($post->ID, c_al2fb_meta_link_id, $link_id);
					delete_post_meta($post->ID, c_al2fb_meta_link_time);
					delete_post_meta($post->ID, c_al2fb_meta_link_picture);
					delete_post_meta($post->ID, c_al2fb_meta_error);
					delete_post_meta($post->ID, c_al2fb_meta_error_time);
				}
				catch (Exception $e) {
					update_post_meta($post->ID, c_al2fb_meta_error, 'Delete link: ' . $e->getMessage());
					update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
				}
			}
		}

		// Delete Link from Facebook
		static function Delete_fb_link_comment($comment) {
			// Get data
			$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
			if (empty($fb_comment_id))
				return;
			$post = get_post($comment->comment_post_ID);
			if (empty($post))
				return;

			// Do not disturb WordPress
			try {
				// Build request
				$url = 'https://graph.facebook.com/' . $fb_comment_id;
				$url = apply_filters('al2fb_url', $url);
				$query = http_build_query(array(
					'access_token' => WPAL2Int::Get_access_token_by_post($post),
					'method' => 'delete'
				), '', '&');

				// Execute request
				$response = WPAL2Int::Request($url, $query, 'POST');

				// Delete meta data
				delete_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id);

				if (get_option(c_al2fb_option_debug))
					add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' deleted comment=' . $comment->comment_ID . ' fib=' . $fb_comment_id);
			}
			catch (Exception $e) {
				update_post_meta($post->ID, c_al2fb_meta_error, 'Delete comment: ' . $e->getMessage());
				update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
			}
		}

		// Add comment to link
		static function Add_fb_link_comment($comment) {
			// Get data
			$fb_comment_id = get_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, true);
			if (!empty($fb_comment_id))
				return;
			$post = get_post($comment->comment_post_ID);
			if (empty($post))
				return;
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if (empty($link_id))
				return;
			if (get_post_meta($post->ID, c_al2fb_meta_nointegrate, true))
				return;
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if (!get_user_meta($user_ID, c_al2fb_meta_fb_comments_postback, true))
				return;
			if (get_user_meta($user_ID, c_al2fb_meta_fb_comments_only, true))
				if ($comment->comment_type == 'pingback' || $comment->comment_type == 'trackback')
					return;
			if (WPAL2Facebook::Is_excluded_post_type($post))
				return;

			// Build message
			$message = '';
			if ($post->post_author != $comment->user_id || $post->post_author != $user_ID) {
				$message .= $comment->comment_author . ' ' .  __('commented on', c_al2fb_text_domain) . ' ';
				$message .= html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset')) . ":\n\n";
			}
			$message .= $comment->comment_content;
			$message = apply_filters('al2fb_comment', $message, $comment, $post);
			$message = WPAL2Int::Convert_encoding($user_ID, $message);

			// Do not disturb WordPress
			try {
				$url = 'https://graph.facebook.com/' . $link_id . '/comments';
				$url = apply_filters('al2fb_url', $url);

				$query_array = array(
					'access_token' => WPAL2Int::Get_access_token_by_post($post),
					'message' => $message
				);

				// http://developers.facebook.com/docs/reference/api/Comment/
				$query = http_build_query($query_array, '', '&');

				// Execute request
				$response = WPAL2Int::Request($url, $query, 'POST');

				// Process response
				$fb_comment = json_decode($response);
				add_comment_meta($comment->comment_ID, c_al2fb_meta_fb_comment_id, $fb_comment->id);

				if (get_option(c_al2fb_option_debug))
					add_post_meta($post->ID, c_al2fb_meta_log, date('c') . ' added comment=' . $comment->comment_ID . ' fib=' . $fb_comment->id);

				// Remove previous errors
				$error = get_post_meta($post->ID, c_al2fb_meta_error, true);
				if (strpos($error, 'Add comment: ') !== false) {
					delete_post_meta($post->ID, c_al2fb_meta_error, $error);
					delete_post_meta($post->ID, c_al2fb_meta_error_time);
				}
			}
			catch (Exception $e) {
				update_post_meta($post->ID, c_al2fb_meta_error, 'Add comment: ' . $e->getMessage());
				update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
			}
		}

		// Add comment to link
		static function Add_fb_link_reply($reply) {
			// Get data
			$topic_id = bbp_get_reply_topic_id($reply->ID);
			$topic = bbp_get_topic($topic_id);
			$link_id = get_post_meta($topic->ID, c_al2fb_meta_link_id, true);
			if (empty($link_id))
				return;
			if (get_post_meta($topic->ID, c_al2fb_meta_nointegrate, true))
				return;
			$user_ID = WPAL2Facebook::Get_user_ID($topic);
			if (!get_user_meta($user_ID, c_al2fb_meta_fb_comments_postback, true))
				return;

			// Build message
			$message = bbp_get_reply_author($reply->ID) . ' ' .  __('commented on', c_al2fb_text_domain) . ' ';
			$message .= html_entity_decode(get_bloginfo('title'), ENT_QUOTES, get_bloginfo('charset')) . ":\n\n";
			$message .= $reply->post_content;
			$message = apply_filters('al2fb_reply', $message, $reply_id);

			// Do not disturb WordPress
			try {
				$url = 'https://graph.facebook.com/' . $link_id . '/comments';
				$url = apply_filters('al2fb_url', $url);

				$query_array = array(
					'access_token' => WPAL2Int::Get_access_token_by_post($topic),
					'message' => $message
				);

				// http://developers.facebook.com/docs/reference/api/Comment/
				$query = http_build_query($query_array, '', '&');

				// Execute request
				$response = WPAL2Int::Request($url, $query, 'POST');

				// Process response
				$fb_comment = json_decode($response);
				add_post_meta($topic->ID, c_al2fb_meta_fb_comment_id, $fb_comment->id);

				// Remove previous errors
				$error = get_post_meta($topic->ID, c_al2fb_meta_error, true);
				if (strpos($error, 'Add reply: ') !== false) {
					delete_post_meta($topic->ID, c_al2fb_meta_error, $error);
					delete_post_meta($topic->ID, c_al2fb_meta_error_time);
				}
			}
			catch (Exception $e) {
				update_post_meta($topic->ID, c_al2fb_meta_error, 'Add reply: ' . $e->getMessage());
				update_post_meta($topic->ID, c_al2fb_meta_error_time, date('c'));
			}
		}

		// Get selected page id
		static function Get_page_id($user_ID, $self) {
			if (get_user_meta($user_ID, c_al2fb_meta_use_groups, true))
				$page_id = get_user_meta($user_ID, c_al2fb_meta_group, true);
			if (empty($page_id)) {
				$page_id = get_user_meta($user_ID, c_al2fb_meta_page, true);
				if ($page_id == '-')
					unset($page_id);
			}
			if ($self || empty($page_id))
				$page_id = 'me';
			return $page_id;
		}

		// Get cache duration
		static function Get_duration($cron = false) {
			$duration = intval(get_option(c_al2fb_option_msg_refresh));
			if (!$duration)
				$duration = 10;
			if ($cron && get_option(c_al2fb_option_cron_enabled))
				$duration += 10;
			return $duration * 60;
		}

		static function Get_access_token($user_ID) {
			if (get_option(c_al2fb_option_login_add_links)) {
				$token = WPAL2Int::Get_login_access_token($user_ID);
				if ($token)
					return $token;
			}
			return get_user_meta($user_ID, c_al2fb_meta_access_token, true);
		}

		static function Get_login_access_token($user_ID) {
			$token = get_user_meta($user_ID, c_al2fb_meta_facebook_token, true);
			$token_time = get_user_meta($user_ID, c_al2fb_meta_facebook_token_time, true);
			if ($token && $token_time + 10 * 60 > time())
				return $token;
			else {
				delete_user_meta($user_ID, c_al2fb_meta_facebook_token);
				delete_user_meta($user_ID, c_al2fb_meta_facebook_token_time);
			}
			return false;
		}

		// Get correct access for post
		static function Get_access_token_by_post($post) {
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			$page_id = get_user_meta($user_ID, c_al2fb_meta_page, true);
			return WPAL2Int::Get_access_token_by_page($user_ID, $page_id);
		}

		// Get access token for page
		static function Get_access_token_by_page($user_ID, $page_id) {
			if ($page_id && $page_id != 'me') {
				$pages = WPAL2Int::Get_fb_pages($user_ID);
				if ($pages->data)
					foreach ($pages->data as $page)
						if ($page->id == $page_id)
							return $page->access_token;
			}
			return WPAL2Int::Get_access_token($user_ID);
		}

		// Get language code for Facebook
		static function Get_locale($user_ID) {
			$locale = get_user_meta($user_ID, c_al2fb_meta_fb_locale, true);
			if (empty($locale)) {
				$locale = get_bloginfo('language');
				$locale = str_replace('-', '_', $locale);
				if (empty($locale) || strlen($locale) != 5)
					$locale = 'en_US';
			}
			return $locale;
		}

		static function Get_fb_script($user_ID) {
			if (get_option(c_al2fb_option_noscript))
				return '<!-- AL2FB no script -->';

			$lang = WPAL2Int::Get_locale($user_ID);
			$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);

			if (get_option(c_al2fb_option_noasync)) {
				if ($appid)
					$url = 'http://connect.facebook.net/' . $lang . '/all.js#appId=' . $appid . '&amp;xfbml=1';
				else
					$url = 'http://connect.facebook.net/' . $lang . '/all.js#xfbml=1';
				return '<script src="' . $url . '" type="text/javascript"></script>' . PHP_EOL;
			}
			else {
				$result = '<script type="text/javascript">' . PHP_EOL;
				$result .= '(function(d, s, id) {' . PHP_EOL;
				$result .= '  var js, fjs = d.getElementsByTagName(s)[0];' . PHP_EOL;
				$result .= '  if (d.getElementById(id)) return;' . PHP_EOL;
				$result .= '  js = d.createElement(s); js.id = id;' . PHP_EOL;
				if ($appid)
					$result .= '  js.src = "//connect.facebook.net/' . $lang . '/all.js#xfbml=1&appId=' . $appid . '";' . PHP_EOL;
				else
					$result .= '  js.src = "//connect.facebook.net/' . $lang . '/all.js#xfbml=1";' . PHP_EOL;
				$result .= '  fjs.parentNode.insertBefore(js, fjs);' . PHP_EOL;
				$result .= '}(document, "script", "facebook-jssdk"));' . PHP_EOL;
				$result .= '</script>' . PHP_EOL;
				return $result;
			}
		}

		// Get HTML for like button
		static function Get_like_button($post, $box) {
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if ($user_ID && !WPAL2Facebook::Is_excluded_post_type($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				// Get options
				$layout = get_user_meta($user_ID, c_al2fb_meta_like_layout, true);
				$faces = get_user_meta($user_ID, c_al2fb_meta_like_faces, true);
				if ($box) {
					$width = get_user_meta($user_ID, c_al2fb_meta_like_box_width, true);
					$height = get_user_meta($user_ID, c_al2fb_meta_like_box_height, true);
				}
				else {
					$width = get_user_meta($user_ID, c_al2fb_meta_like_width, true);
					$height = false;
				}
				$action = get_user_meta($user_ID, c_al2fb_meta_like_action, true);
				$font = get_user_meta($user_ID, c_al2fb_meta_like_font, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$border = get_user_meta($user_ID, c_al2fb_meta_like_box_border, true);
				$noheader = get_user_meta($user_ID, c_al2fb_meta_like_box_noheader, true);
				$nostream = get_user_meta($user_ID, c_al2fb_meta_like_box_nostream, true);

				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					if ($box) {
						// Get page
						if (WPAL2Facebook::Is_authorized($user_ID) &&
							!get_user_meta($user_ID, c_al2fb_meta_use_groups, true) &&
							get_user_meta($user_ID, c_al2fb_meta_page, true))
							try {
								$page = WPAL2Int::Get_fb_me_cached($user_ID, false);
								$link = $page->link;
							}
							catch (Exception $e) {
							}
					}
					else
						$link = get_permalink($post->ID);

				$combine = get_user_meta($user_ID, c_al2fb_meta_post_combine_buttons, true);
				$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
				$lang = WPAL2Int::Get_locale($user_ID);
				$txtinfo = (empty($action) || $action == 'like' ? __('Like', c_al2fb_text_domain) : __('Recommend', c_al2fb_text_domain));
				$infolink = get_option(c_al2fb_option_ssp_info);
				if (empty($infolink))
					$infolink = 'http://yro.slashdot.org/story/11/09/03/0115241/Heises-Two-Clicks-For-More-Privacy-vs-Facebook';

				// Build content
				if ($appid && !$combine && !$box && get_option(c_al2fb_option_use_ssp)) {
					$content = '<div id="al2fb_ssp' . $post->ID . '"></div>' . PHP_EOL;
					$content .= '<script type="text/javascript">' . PHP_EOL;
					$content .= '	jQuery(document).ready(function($) {' . PHP_EOL;
					$content .= '		$("#al2fb_ssp' . $post->ID . '").socialSharePrivacy({' . PHP_EOL;
					$content .= '			services : {' . PHP_EOL;
					$content .= '				facebook : {' . PHP_EOL;
					$content .= '					"status" : "on",' . PHP_EOL;
					$content .= '					"dummy_img" : "' . WPAL2Int::Get_plugin_url() . '/js/socialshareprivacy/images/dummy_facebook.png",' . PHP_EOL;
					if ($lang != 'de_DE') {
						$content .= '					"txt_info" : "' . $txtinfo . '",';
						$content .= '					"txt_fb_off" : "",';
						$content .= '					"txt_fb_on" : "",';
					}
					$content .= '					"perma_option" : "off",' . PHP_EOL;
					if ($lang != 'de_DE')
						$content .= '					"display_name" : "Facebook",' . PHP_EOL;
					$content .= '					"referrer_track" : "",' . PHP_EOL;
					$content .= '					"language" : "' . $lang . '",' . PHP_EOL;
					$content .= '					"action" : "' . (empty($action) ? 'like' : $action) . '"' . PHP_EOL;
					$content .= '				},';
					$content .= '				twitter : {' . PHP_EOL;
					$content .= '					"status" : "off",' . PHP_EOL;
					$content .= '					"dummy_img" : "' . WPAL2Int::Get_plugin_url() . '/js/socialshareprivacy/images/dummy_twitter.png",' . PHP_EOL;
					$content .= '					"perma_option" : "off"' . PHP_EOL;
					$content .= '				 },' . PHP_EOL;
					$content .= '				gplus : {' . PHP_EOL;
					$content .= '					"status" : "off",' . PHP_EOL;
					$content .= '					"dummy_img" : "' . WPAL2Int::Get_plugin_url() . '/js/socialshareprivacy/images/dummy_gplus.png",' . PHP_EOL;
					$content .= '					"perma_option" : "off"' . PHP_EOL;
					$content .= '				 },' . PHP_EOL;
					$content .= '			},';
					$content .= '			"info_link" : "' . $infolink . '",';
					if ($lang != 'de_DE')
						$content .= '			"txt_help" : "' . __('Information', c_al2fb_text_domain) . '",' . PHP_EOL;
					$content .= '			"css_path" : "' . WPAL2Int::Get_plugin_url() . '/js/socialshareprivacy/socialshareprivacy.css",' . PHP_EOL;
					$content .= '			"uri" : "' . $link . '"' . PHP_EOL;
					$content .= '		});' . PHP_EOL;
					$content .= '	});' . PHP_EOL;
					$content .= '</script>' . PHP_EOL;
					$content = apply_filters('al2fb_heise', $content);
				}
				else {
					$content = ($box ? '<div class="al2fb_like_box">' : '<div class="al2fb_like_button">');
					$content .= '<div id="fb-root"></div>';
					$content .= WPAL2Int::Get_fb_script($user_ID);
					$content .= ($box ? '<fb:like-box' : '<fb:like');
					$content .= ' href="' . $link . '"';
					if (!$box && $combine)
						$content .= ' send="true"';
					if (!$box)
						$content .= ' layout="' . (empty($layout) ? 'standard' : $layout) . '"';
					$content .= ' show_faces="' . ($faces ? 'true' : 'false') . '"';
					$content .= ' width="' . (empty($width) ? ($box ? '292' : '450') : $width) . '"';
					if ($height)
						$content .= ' height="' . $height . '"';
					if (!$box) {
						$content .= ' action="' . (empty($action) ? 'like' : $action) . '"';
						$content .= ' font="' . (empty($font) ? 'arial' : $font) . '"';
					}
					$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
					if (!$box)
						$content .= ' ref="AL2FB"';
					if ($box) {
						$content .= ' border_color="' . $border . '"';
						$content .= ' stream="' . ($nostream ? 'false' : 'true') . '"';
						$content .= ' header="' . ($noheader ? 'false' : 'true') . '"';
					}
					$content .= ($box ? '></fb:like-box>' : '></fb:like>');
					$content .= '</div>';
				}

				return $content;
			}
			else
				return '';
		}

		// Get HTML for like button
		static function Get_send_button($post) {
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if ($user_ID && !WPAL2Facebook::Is_excluded_post_type($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				// Get options
				$font = get_user_meta($user_ID, c_al2fb_meta_like_font, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					$link = get_permalink($post->ID);

				// Send button
				$content = '<div class="al2fb_send_button">';
				$content .= '<div id="fb-root"></div>';
				$content .= WPAL2Int::Get_fb_script($user_ID);
				$content .= '<fb:send ref="AL2FB"';
				$content .= ' font="' . (empty($font) ? 'arial' : $font) . '"';
				$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
				$content .= ' href="' . $link . '"></fb:send>';
				$content .= '</div>';

				return $content;
			}
			else
				return '';
		}

		// Get HTML for comments plugin
		static function Get_comments_plugin($post) {
			if (get_post_meta($post->ID, c_al2fb_meta_nointegrate, true))
				return '';

			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if ($user_ID && !WPAL2Facebook::Is_excluded_post_type($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				// Get options
				$posts = get_user_meta($user_ID, c_al2fb_meta_comments_posts, true);
				$width = get_user_meta($user_ID, c_al2fb_meta_comments_width, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					$link = get_permalink($post->ID);

				// Send button
				$content = '<div class="al2fb_comments_plugin">';
				$content .= '<div id="fb-root"></div>';
				$content .= WPAL2Int::Get_fb_script($user_ID);
				$content .= '<fb:comments';
				$content .= ' num_posts="' . (empty($posts) ? '2' : $posts) . '"';
				$content .= ' width="' . (empty($width) ? '500' : $width) . '"';
				$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
				$content .= ' href="' . $link . '"></fb:comments>';
				$content .= '</div>';

				return $content;
			}
			else
				return '';
		}

		// Get HTML face pile
		static function Get_face_pile($post) {
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if ($user_ID && !WPAL2Facebook::Is_excluded_post_type($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				// Get options
				$size = get_user_meta($user_ID, c_al2fb_meta_pile_size, true);
				$width = get_user_meta($user_ID, c_al2fb_meta_pile_width, true);
				$rows = get_user_meta($user_ID, c_al2fb_meta_pile_rows, true);
				$link = get_user_meta($user_ID, c_al2fb_meta_like_link, true);
				if (empty($link))
					$link = get_permalink($post->ID);

				// Face pile
				$content = '<div class="al2fb_face_pile">';
				$content .= '<div id="fb-root"></div>';
				$content .= WPAL2Int::Get_fb_script($user_ID);
				$content .= '<fb:facepile';
				$content .= ' size="' . (empty($size) ? 'small' : $size) . '"';
				$content .= ' width="' . (empty($width) ? '200' : $width) . '"';
				$content .= ' max_rows="' . (empty($rows) ? '1' : $rows) . '"';
				$content .= ' href="' . $link . '"></fb:facepile>';
				$content .= '</div>';

				return $content;
			}
			else
				return '';
		}

		// Get HTML profile link
		static function Get_profile_link($post) {
			$content = '';
			try {
				$user_ID = WPAL2Facebook::Get_user_ID($post);
				$me = WPAL2Int::Get_fb_me_cached($user_ID, false);
				if (!empty($me)) {
					$img = 'http://creative.ak.fbcdn.net/ads3/creative/pressroom/jpg/b_1234209334_facebook_logo.jpg';
					$content .= '<div class="al2fb_profile"><a href="' . $me->link . '">';
					$content .= '<img src="' . $img . '" alt="Facebook profile" /></a></div>';
				}
			}
			catch (Exception $e) {
			}
			return $content;
		}

		// Get HTML Facebook registration
		static function Get_registration($post) {
			// Check if registration enabled
			if (!get_option('users_can_register'))
				return '<strong>' . __('User registration disabled', c_al2fb_text_domain) . '</strong>';

			// Get data
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if ($user_ID && !WPAL2Facebook::Is_excluded_post_type($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				// Check if user logged in
				if (is_user_logged_in())
					return do_shortcode(get_user_meta($user_ID, c_al2fb_meta_login_html, true));

				// Get options
				$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
				$width = get_user_meta($user_ID, c_al2fb_meta_reg_width, true);
				$border = get_user_meta($user_ID, c_al2fb_meta_like_box_border, true);
				$fields = "[{'name':'name'}";
				$fields .= ",{'name':'first_name'}";
				$fields .= ",{'name':'last_name'}";
				$fields .= ",{'name':'email'}";
				$fields .= ",{'name':'user_name','description':'" . __('WordPress user name', c_al2fb_text_domain) . "','type':'text'}";
				$fields .= ",{'name':'password'}]";

				// Build content
				if ($appid) {
					$content = '<div class="al2fb_registration">';
					$content .= '<div id="fb-root"></div>';
					$content .= WPAL2Int::Get_fb_script($user_ID);
					$content .= '<fb:registration';
					$content .= ' fields="' . $fields . '"';
					$content .= ' redirect-uri="' . WPAL2Int::Redirect_uri() . '?al2fb_reg=true&user=' . $user_ID . '&uri=' . urlencode($_SERVER['REQUEST_URI']) . '"';
					$content .= ' width="' . (empty($width) ? '530' : $width) . '"';
					$content .= ' border_color="' . (empty($border) ? '' : $border) . '">';
					$content .= '</fb:registration>';
					$content .= '</div>';
					return $content;
				}
				else
					return '<strong>' . __('Facebook App ID required', c_al2fb_text_domain) . '</strong>';
			}
			return '';
		}

		// Get HTML Facebook login
		static function Get_login($post) {
			// Get data
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if ($user_ID && !WPAL2Facebook::Is_excluded_post_type($post) && !WPAL2Int::social_in_excerpt($user_ID)) {
				// Check if user logged in
				if (is_user_logged_in())
					return do_shortcode(get_user_meta($user_ID, c_al2fb_meta_login_html, true));

				// Get options
				$appid = get_user_meta($user_ID, c_al2fb_meta_client_id, true);
				$regurl = get_user_meta($user_ID, c_al2fb_meta_login_regurl, true);
				$faces = false;
				$width = get_user_meta($user_ID, c_al2fb_meta_login_width, true);
				$rows = get_user_meta($user_ID, c_al2fb_meta_pile_rows, true);
				$permissions = '';
				if (get_option(c_al2fb_option_login_add_links))
					$permissions .= 'read_stream,publish_stream,manage_pages,user_groups';

				// Build content
				if ($appid) {
					$content = '<div class="al2fb_login">';
					$content .= '<div id="fb-root"></div>';
					$content .= WPAL2Int::Get_fb_script($user_ID);
					$content .= '<script type="text/javascript">' . PHP_EOL;
					$content .= 'function al2fb_login() {' . PHP_EOL;
					$content .= '	FB.getLoginStatus(function(response) {' . PHP_EOL;
					$content .= '		if (response.status == "unknown")' . PHP_EOL;
					$content .= '			alert("' . __('Please enable third-party cookies', c_al2fb_text_domain) . '");' . PHP_EOL;
					$content .= '		var uid = null;' . PHP_EOL;
					$content .= '		var token = null;' . PHP_EOL;
					$content .= '		if (response.status == "connected") {' . PHP_EOL;
					$content .= '			var uid = response.authResponse.userID;' . PHP_EOL;
					$content .= '			var token = response.authResponse.accessToken;' . PHP_EOL;
					$content .= '		}' . PHP_EOL;
					$content .= '		if (response.session) {' . PHP_EOL;
					$content .= '			var uid = response.session.access_token;' . PHP_EOL;
					$content .= '			var token = response.session.uid;' . PHP_EOL;
					$content .= '		}' . PHP_EOL;
					$content .= '		if (uid != null && token != null)' . PHP_EOL;
					$content .= '			window.location="' .  WPAL2Int::Redirect_uri() . '?al2fb_login=true';
					$content .= '&token=" + token + "&uid=" + uid + "&uri=" + encodeURI(window.location.pathname + window.location.search) + "&user=' . $user_ID . '";' . PHP_EOL;
					$content .= '	});' . PHP_EOL;
					$content .= '}' . PHP_EOL;
					$content .= '</script>' . PHP_EOL;
					$content .= '<fb:login-button';
					$content .= ' registration-url="' . $regurl . '"';
					$content .= ' show_faces="' . ($faces ? 'true' : 'false') . '"';
					$content .= ' width="' . (empty($width) ? '200' : $width) . '"';
					$content .= ' max_rows="' . (empty($rows) ? '1' : $rows) . '"';
					$content .= ' perms="' . $permissions . '"';
					$content .= ' onlogin="al2fb_login();">';
					$content .= '</fb:login-button>';
					$content .= '</div>';
					return $content;
				}
				else
					return '<strong>' . __('Facebook App ID required', c_al2fb_text_domain) . '</strong>';
			}
			return '';
		}

		// Get HTML Facebook activity feed
		static function Get_activity_feed($post) {
			// Get data
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			if ($user_ID && !WPAL2Facebook::Is_excluded_post_type($post) && !WPAL2Int::social_in_excerpt($user_ID)) {

				// Get options
				$domain = $_SERVER['HTTP_HOST'];
				$width = get_user_meta($user_ID, c_al2fb_meta_act_width, true);
				$height = get_user_meta($user_ID, c_al2fb_meta_act_height, true);
				$header = get_user_meta($user_ID, c_al2fb_meta_act_header, true);
				$colorscheme = get_user_meta($user_ID, c_al2fb_meta_like_colorscheme, true);
				$font = get_user_meta($user_ID, c_al2fb_meta_like_font, true);
				$border = get_user_meta($user_ID, c_al2fb_meta_like_box_border, true);
				$recommend = get_user_meta($user_ID, c_al2fb_meta_act_recommend, true);

				// Build content
				$content = '<div class="al2fb_activity_feed">';
				$content .= '<div id="fb-root"></div>';
				$content .= WPAL2Int::Get_fb_script($user_ID);
				$content .= '<fb:activity';
				$content .= ' site="' . $domain . '"';
				$content .= ' width="' . (empty($width) ? '300' : $width) . '"';
				$content .= ' height="' . (empty($height) ? '300' : $height) . '"';
				$content .= ' colorscheme="' . (empty($colorscheme) ? 'light' : $colorscheme) . '"';
				$content .= ' header="' . ($header ? 'true' : 'false') . '"';
				$content .= ' font="' . (empty($font) ? 'arial' : $font) . '"';
				$content .= ' border_color="' . (empty($border) ? '' : $border) . '"';
				$content .= ' recommendations="' . ($recommend ? 'true' : 'false') . '">';
				$content .= '</fb:activity>';
				$content .= '</div>';
				return $content;
			}
			return '';
		}

		// Handle Facebook registration
		static function Facebook_registration() {
			// Decode Facebook data
			$reg = WPAL2Int::Parse_signed_request($_REQUEST['user']);

			// Check result
			if ($reg == null) {
				header('Content-type: text/plain');
				_e('Facebook registration failed', c_al2fb_text_domain);
				echo PHP_EOL;
				if (get_option(c_al2fb_option_debug))
					print_r($_REQUEST);
			}
			else
				try {
					// Validate
					$url = 'https://graph.facebook.com/' . $reg['user_id'];
					$url = apply_filters('al2fb_url', $url);
					$query = http_build_query(array('access_token' => $reg['oauth_token']), '', '&');
					$response = WPAL2Int::Request($url, $query, 'GET');
					$me = json_decode($response);
					$email = (empty($me) ? null : $me->email);

					if (!get_option('users_can_register')) {
						// Registration not enabled
						header('Content-type: text/plain');
						_e('User registration disabled', c_al2fb_text_domain);
						echo PHP_EOL;
					}
					else if (empty($email)) {
						// E-mail missing
						header('Content-type: text/plain');
						_e('Facebook e-mail address missing', c_al2fb_text_domain);
						echo PHP_EOL;
						if (get_option(c_al2fb_option_debug)) {
							print_r($reg);
							print_r($me);
						}
					}
					else {
						$user_ID = false;
						if (email_exists($email)) {
							$user = get_user_by('email', $email);
							if ($user)
								$user_ID = $user->ID;
							else {
								header('Content-type: text/plain');
								_e('User not found', c_al2fb_text_domain);
								echo PHP_EOL;
								echo $email;
							}
						}
						else {
							// Create new WP user
							$user_ID = wp_insert_user(array(
								'first_name' => $reg['registration']['first_name'],
								'last_name' => $reg['registration']['last_name'],
								'user_email' => $email,
								'user_login' => $reg['registration']['user_name'],
								'user_pass' => $reg['registration']['password']
							)) ;

							// Check result
							if (is_wp_error($user_ID)) {
								header('Content-type: text/plain');
								_e($user_ID->get_error_message());
								echo PHP_EOL;
								if (get_option(c_al2fb_option_debug))
									print_r($reg);
								$user_ID = false;
							}
						}

						// Redirect
						if ($user_ID) {
							update_user_meta($user_ID, c_al2fb_meta_facebook_id, $me->id);
							$url = get_user_meta($user_ID, c_al2fb_meta_reg_success, true);
							if (empty($url))
								$url = get_home_url();
							wp_redirect($url);
						}
					}
				}
				catch (Exception $e) {
					// Communication error?
					header('Content-type: text/plain');
					_e('Could not verify Facebook registration', c_al2fb_text_domain);
					echo PHP_EOL;
					echo $e->getMessage();
					if (get_option(c_al2fb_option_debug)) {
						print_r($_REQUEST);
						print_r($response);
					}
				}
		}

		// Handle Facebook login
		static function Facebook_login() {
			header('Content-type: text/plain');
			try {
				// Check token
				$url = 'https://graph.facebook.com/' . $_REQUEST['uid'];
				$url = apply_filters('al2fb_url', $url);
				$query = http_build_query(array('access_token' => $_REQUEST['token']), '', '&');
				$response = WPAL2Int::Request($url, $query, 'GET');
				$me = json_decode($response);

				// Workaround if no e-mail present
				if (!empty($me) && empty($me->email)) {
					$users = get_users(array(
						'meta_key' => c_al2fb_meta_facebook_id,
						'meta_value' => $me->id
					));
					if (count($users) == 0) {
						$regurl = get_user_meta($_REQUEST['user'], c_al2fb_meta_login_regurl, true);
						if (!empty($regurl))
							wp_redirect($regurl);
					}
					else if (count($users) == 1)
						$me->email = $users[0]->user_email;
				}

				// Check Facebook user
				if (!empty($me) && !empty($me->id)) {
					// Find user by Facebook ID
					$users = get_users(array(
						'meta_key' => c_al2fb_meta_facebook_id,
						'meta_value' => $me->id
					));

					// Check if found one
					if (count($users) == 1) {
						// Try to login
						$user = WPAL2Int::Login_by_email($users[0]->user_email, true);

						// Check login
						if ($user) {
							// Persist token
							update_user_meta($user->ID, c_al2fb_meta_facebook_token, $_REQUEST['token']);
							update_user_meta($user->ID, c_al2fb_meta_facebook_token_time, time());

							// Redirect
							$self = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_REQUEST['uri'];
							$redir = get_user_meta($_REQUEST['user'], c_al2fb_meta_login_redir, true);
							wp_redirect($redir ? $redir : $self);
						}
						else {
							// User not found (anymore)
							header('Content-type: text/plain');
							_e('User not found', c_al2fb_text_domain);
							echo PHP_EOL;
							if (get_option(c_al2fb_option_debug))
								print_r($me);
						}
					}
					else {
						$self = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_REQUEST['uri'];
						$regurl = get_user_meta($_REQUEST['user'], c_al2fb_meta_login_regurl, true);
						wp_redirect($regurl ? $regurl : $self);
					}
				}
				else {
					// Something went wrong
					header('Content-type: text/plain');
					_e('Could not verify Facebook login', c_al2fb_text_domain);
					echo PHP_EOL;
					if (get_option(c_al2fb_option_debug)) {
						print_r($_REQUEST);
						print_r($response);
					}
				}
			}
			catch (Exception $e) {
				// Communication error?
				header('Content-type: text/plain');
				_e('Could not verify Facebook login', c_al2fb_text_domain);
				echo PHP_EOL;
				echo $e->getMessage();
				echo PHP_EOL;
			}
		}

		// Log WordPress user in using e-mail
		static function Login_by_email($email, $rememberme) {
			global $user;
			$user = null;

			$userdata = get_user_by('email', $email);
			if ($userdata) {
				$user = new WP_User($userdata->ID);
				wp_set_current_user($userdata->ID, $userdata->user_login);
				wp_set_auth_cookie($userdata->ID, $rememberme);
				do_action('wp_login', $userdata->user_login);
			}
			return $user;
		}

		// Decode Facebook registration response
		static function Parse_signed_request($user_ID) {
			$signed_request = $_REQUEST['signed_request'];
			$secret = get_user_meta($user_ID, c_al2fb_meta_app_secret, true);

			list($encoded_sig, $payload) = explode('.', $signed_request, 2);

			// Decode the data
			$sig = WPAL2Int::base64_url_decode($encoded_sig);
			$data = json_decode(WPAL2Int::base64_url_decode($payload), true);

			if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
				return null;

			// Check sig
			$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
			if ($sig !== $expected_sig)
				return null;

			return $data;
		}

		// Helper: base64 decode url
		static function base64_url_decode($input) {
			return base64_decode(strtr($input, '-_', '+/'));
		}

		static function Get_comments_or_likes($post, $likes, $cached = true) {
			$user_ID = WPAL2Facebook::Get_user_ID($post);
			$link_id = get_post_meta($post->ID, c_al2fb_meta_link_id, true);
			if ($link_id)
				try {
					if ($likes)
						$result = WPAL2Int::Get_fb_likes_cached($user_ID, $link_id, $cached);
					else
						$result = WPAL2Int::Get_fb_comments_cached($user_ID, $link_id, $cached);

					// Remove previous errors
					$error = get_post_meta($post->ID, c_al2fb_meta_error, true);
					if (strpos($error, 'Import comment: ') !== false) {
						delete_post_meta($post->ID, c_al2fb_meta_error, $error);
						delete_post_meta($post->ID, c_al2fb_meta_error_time);
					}

					return $result;
				}
				catch (Exception $e) {
					update_post_meta($post->ID, c_al2fb_meta_error, 'Import comment: ' . $e->getMessage());
					update_post_meta($post->ID, c_al2fb_meta_error_time, date('c'));
					return null;
				}
			return null;
		}

		static function in_excerpt() {
			return
				in_array('the_excerpt', $GLOBALS['wp_current_filter']) ||
				in_array('get_the_excerpt', $GLOBALS['wp_current_filter']);
		}

		static function social_in_excerpt($user_ID) {
			if (get_user_meta($user_ID, c_al2fb_meta_social_noexcerpt, true))
				return WPAL2Int::in_excerpt();
			else
				return false;
		}

		// Generic http request
		static function Request($url, $query, $type) {
			// Get timeout
			$timeout = get_option(c_al2fb_option_timeout);
			if (!$timeout)
				$timeout = 25;

			// Use cURL if available
			if (function_exists('curl_init') && !get_option(c_al2fb_option_nocurl))
				return WPAL2Int::Request_cURL($url, $query, $type, $timeout);

			if (version_compare(PHP_VERSION, '5.2.1') < 0)
				ini_set('default_socket_timeout', $timeout);

			delete_option(c_al2fb_log_ua);
			$ua = $_SERVER['HTTP_USER_AGENT'];
			if (!empty($ua)) {
				ini_set('user_agent', $ua);
				update_option(c_al2fb_log_ua, $ua);
			}

			WPAL2Int::$php_error = '';
			set_error_handler(array('WPAL2Int', 'PHP_error_handler'));
			if ($type == 'GET') {
				$context = stream_context_create(array(
				'http' => array(
					'method'  => 'GET',
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'timeout' => $timeout
					)
				));
				$content = file_get_contents($url . ($query ? '?' . $query : ''), false, $context);
			}
			else {
				$context = stream_context_create(array(
					'http' => array(
						'method'  => 'POST',
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'timeout' => $timeout,
						'content' => $query
					)
				));
				$content = file_get_contents($url, false, $context);
			}
			restore_error_handler();

			// Check for errors
			$status = false;
			$auth_error = '';
			if (!empty($http_response_header))
				foreach ($http_response_header as $h)
					if (strpos($h, 'HTTP/') === 0) {
						$status = explode(' ', $h);
						$status = intval($status[1]);
					}
					else if (strpos($h, 'WWW-Authenticate:') === 0)
						$auth_error = $h;

			if ($status == 200)
				return $content;
			else {
				if ($auth_error)
					$msg = 'Error ' . $status . ': ' . $auth_error;
				else
					$msg = 'Error ' . $status . ': ' . WPAL2Int::$php_error . ' ' . print_r($http_response_header, true);
				update_option(c_al2fb_last_error, $msg);
				update_option(c_al2fb_last_error_time, date('c'));
				throw new Exception($msg);
			}
		}

		// Persist PHP errors
		static function PHP_error_handler($errno, $errstr) {
			WPAL2Int::$php_error = $errstr;
		}

		// cURL http request
		static function Request_cURL($url, $query, $type, $timeout) {
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

			if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
				curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($c, CURLOPT_MAXREDIRS, 10);
			}
			curl_setopt($c, CURLOPT_TIMEOUT, $timeout);

			if ($type == 'GET')
				curl_setopt($c, CURLOPT_URL, $url . ($query ? '?' . $query : ''));
			else {
				curl_setopt($c, CURLOPT_URL, $url);
				curl_setopt($c, CURLOPT_POST, true);
				curl_setopt($c, CURLOPT_POSTFIELDS, $query);
			}

			if (get_option(c_al2fb_option_noverifypeer))
				curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
			else if (get_option(c_al2fb_option_use_cacerts))
				curl_setopt($c, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

			delete_option(c_al2fb_log_ua);
			$ua = $_SERVER['HTTP_USER_AGENT'];
			if (!empty($ua)) {
				curl_setopt($c,CURLOPT_USERAGENT, $ua);
				update_option(c_al2fb_log_ua, $ua);
			}

			$content = curl_exec($c);
			$errno = curl_errno($c);
			$errtext = curl_error($c);
			$info = curl_getinfo($c);
			curl_close($c);

			if ($errno === 0 && $info['http_code'] == 200)
				return $content;
			else {
				$error = json_decode($content);
				$error = empty($error->error->message) ? $content : $error->error->message;
				if (isset($info['url'])) {
					// Strip privacy sensitive info
					$url = explode('?', $info['url']);
					$info['url'] = $url[0] . '...';
				}
				if ($errno || !$error)
					$msg = 'cURL communication error ' . $errno . ' ' . $errtext . ': ' . $error . ' ' . print_r($info, true);
				else
					$msg = 'Facebook error: ' . $error;

				update_option(c_al2fb_last_error, $msg . ' ' . print_r(debug_backtrace(), true));
				update_option(c_al2fb_last_error_time, date('c'));
				throw new Exception($msg);
			}
		}

		static function Set_multiple($code, $count) {
			if (empty($code)) {
				delete_option(c_al2fb_option_multiple);
				delete_option(c_al2fb_option_multiple_count);
				if (is_multisite()) {
					delete_site_option(c_al2fb_option_multiple);
					delete_site_option(c_al2fb_option_multiple_count);
				}
			}
			else {
				update_site_option(c_al2fb_option_multiple, $code);
				if ($count > 1)
					update_site_option(c_al2fb_option_multiple_count, $count);
				else
					delete_site_option(c_al2fb_option_multiple_count);
			}
			return WPAL2Int::Check_multiple();
		}

		static function Check_multiple() {
			if (get_option(c_al2fb_option_multiple_disable))
				return false;

			// Backward compatibility
			if (is_multisite()) {
				$code = get_option(c_al2fb_option_multiple);
				$count = get_option(c_al2fb_option_multiple_count);
				if (!empty($code)) {
					update_site_option(c_al2fb_option_multiple, $code);
					update_site_option(c_al2fb_option_multiple_count, $count);
				}
			}

			$code = get_site_option(c_al2fb_option_multiple);
			$count = get_site_option(c_al2fb_option_multiple_count);
			if (is_multisite()) {
				$current_site = get_current_site();
				$blog_details = get_blog_details($current_site->blog_id, true);
				$main_site_url = strtolower(trailingslashit($blog_details->siteurl));
				$blog_count = get_blog_count();
				if (!$blog_count) {
					wp_update_network_counts();
					$blog_count = get_blog_count();
				}
				if (empty($count) && $blog_count == 1)
					$blog_count = '';
				return ($code == md5($main_site_url . $count) && $blog_count <= $count);
			}
			else
				return
					($code == md5(WPAL2Int::Redirect_uri()) ||
					$code == md5(strtolower(WPAL2Int::Redirect_uri())));
		}

		static function Check_updates() {
			return get_site_option(c_al2fb_option_multiple);
		}

		static function Get_multiple_url() {
			if (is_multisite()) {
				$current_site = get_current_site();
				$blog_details = get_blog_details($current_site->blog_id, true);
				$main_site_url = strtolower(trailingslashit($blog_details->siteurl));
				return $main_site_url;
			}
			return false;
		}

		static function Get_multiple_count() {
			if (is_multisite() && get_site_option(c_al2fb_option_multiple)) {
				$result = array();
				$result['count'] = get_site_option(c_al2fb_option_multiple_count);
				$result['blog_count'] = get_blog_count();
				return $result;
			}
			return false;
		}
	}
}

?>
