<?php

// Define constants
define('c_al2fb_text_domain', 'add-link-to-facebook');
define('c_al2fb_nonce_form', 'al2fb-nonce-form');

// Global options
define('c_al2fb_option_version', 'al2fb_version');
define('c_al2fb_option_timeout', 'al2fb_timeout');
define('c_al2fb_option_nonotice', 'al2fb_nonotice');
define('c_al2fb_option_min_cap', 'al2fb_min_cap');
define('c_al2fb_option_no_post_submit', 'al2fb_no_post_submit');
define('c_al2fb_option_min_cap_comment', 'al2fb_min_cap_comment');
define('c_al2fb_option_msg_refresh', 'al2fb_comment_refresh');
define('c_al2fb_option_msg_maxage', 'al2fb_msg_maxage');
define('c_al2fb_option_max_descr', 'al2fb_max_msg');
define('c_al2fb_option_max_text', 'al2fb_max_text');
define('c_al2fb_option_max_comment', 'al2fb_max_comment');
define('c_al2fb_option_exclude_type', 'al2fb_exclude_type');
define('c_al2fb_option_exclude_cat', 'al2fb_exclude_cat');
define('c_al2fb_option_exclude_tag', 'al2fb_exclude_tag');
define('c_al2fb_option_exclude_author', 'al2fb_exclude_author');
define('c_al2fb_option_metabox_type', 'al2fb_metabox_type');
define('c_al2fb_option_noverifypeer', 'al2fb_noverifypeer');
define('c_al2fb_option_use_cacerts', 'al2fb_use_cacerts');
define('c_al2fb_option_shortcode_widget', 'al2fb_shortcode_widget');
define('c_al2fb_option_noshortcode', 'al2fb_noshortcode');
define('c_al2fb_option_nofilter', 'al2fb_nofilter');
define('c_al2fb_option_nofilter_comments', 'al2fb_nofilter_comments');
define('c_al2fb_option_use_ssp', 'al2fb_use_ssp');
define('c_al2fb_option_ssp_info', 'al2fb_ssp_info');
define('c_al2fb_option_filter_prio', 'al2fb_filter_prio');
define('c_al2fb_option_noasync', 'al2fb_noasync');
define('c_al2fb_option_noscript', 'al2fb_noscript');
define('c_al2fb_option_uselinks', 'al2fb_uselinks');
define('c_al2fb_option_notoken_refresh', 'al2fb_notoken_refresh');
define('c_al2fb_option_clean', 'al2fb_clean');
define('c_al2fb_option_css', 'al2fb_css');
define('c_al2fb_option_siteurl', 'al2fb_siteurl');
define('c_al2fb_option_nocurl', 'al2fb_nocurl');
define('c_al2fb_option_use_pp', 'al2fb_use_pp');
define('c_al2fb_option_debug', 'al2fb_debug');
define('c_al2fb_option_multiple', 'al2fb_multiple');
define('c_al2fb_option_multiple_count', 'al2fb_multiple_count');
define('c_al2fb_option_multiple_disable', 'al2fb_multiple_disable');
define('c_al2fb_option_login_add_links', 'al2fb_login_add_links');

define('c_al2fb_option_cron_enabled', 'al2fb_cron_enabled');
define('c_al2fb_option_cron_time', 'al2fb_cron_time');
define('c_al2fb_option_cron_posts', 'al2fb_cron_posts');
define('c_al2fb_option_cron_comments', 'al2fb_cron_comments');
define('c_al2fb_option_cron_likes', 'al2fb_cron_likes');

// Site options
define('c_al2fb_option_app_share', 'al2fb_app_share');

// Transient options
define('c_al2fb_transient_cache', 'al2fb_cache_');

// User meta
define('c_al2fb_meta_client_id', 'al2fb_client_id');
define('c_al2fb_meta_app_secret', 'al2fb_app_secret');
define('c_al2fb_meta_access_token', 'al2fb_access_token');
define('c_al2fb_meta_token_time', 'al2fb_token_time');
define('c_al2fb_meta_picture_type', 'al2fb_picture_type');
define('c_al2fb_meta_picture', 'al2fb_picture');
define('c_al2fb_meta_picture_default', 'al2fb_picture_default');
define('c_al2fb_meta_picture_size', 'al2fb_picture_size');
define('c_al2fb_meta_icon', 'al2fb_icon');
define('c_al2fb_meta_page', 'al2fb_page');
define('c_al2fb_meta_page_extra', 'al2fb_page_extra');
define('c_al2fb_meta_use_groups', 'al2fb_use_groups');
define('c_al2fb_meta_group', 'al2fb_group');
define('c_al2fb_meta_group_extra', 'al2fb_group_extra');
define('c_al2fb_meta_friend_extra', 'al2fb_friend_extra');
define('c_al2fb_meta_caption', 'al2fb_caption');
define('c_al2fb_meta_msg', 'al2fb_msg');
define('c_al2fb_meta_auto_excerpt', 'al2fb_auto_excerpt');
define('c_al2fb_meta_shortlink', 'al2fb_shortlink');
define('c_al2fb_meta_privacy', 'al2fb_privacy');
define('c_al2fb_meta_some_friends', 'al2fb_some_friends');
define('c_al2fb_meta_add_new_page', 'al2fb_add_to_page');
define('c_al2fb_meta_show_permalink', 'al2fb_show_permalink');
define('c_al2fb_meta_social_noexcerpt', 'al2fb_social_noexcerpt');
define('c_al2fb_meta_trailer', 'al2fb_trailer');
define('c_al2fb_meta_hyperlink', 'al2fb_hyperlink');
define('c_al2fb_meta_share_link', 'al2fb_share_link');
define('c_al2fb_meta_fb_comments', 'al2fb_fb_comments');
define('c_al2fb_meta_fb_comments_trailer', 'al2fb_fb_comments_trailer');
define('c_al2fb_meta_fb_comments_postback', 'al2fb_fb_comments_postback');
define('c_al2fb_meta_fb_comments_only', 'al2fb_fb_comments_only');
define('c_al2fb_meta_fb_comments_copy', 'al2fb_fb_comments_copy');
define('c_al2fb_meta_fb_comments_nolink', 'al2fb_fb_comments_nolink');
define('c_al2fb_meta_fb_likes', 'al2fb_fb_likes');
define('c_al2fb_meta_post_likers', 'al2fb_post_likers');
define('c_al2fb_meta_post_like_button', 'al2fb_post_like_button');
define('c_al2fb_meta_like_nohome', 'al2fb_like_nohome');
define('c_al2fb_meta_like_noposts', 'al2fb_like_noposts');
define('c_al2fb_meta_like_nopages', 'al2fb_like_nopages');
define('c_al2fb_meta_like_noarchives', 'al2fb_like_noarchives');
define('c_al2fb_meta_like_nocategories', 'al2fb_like_nocategories');
define('c_al2fb_meta_like_layout', 'al2fb_like_layout');
define('c_al2fb_meta_like_faces', 'al2fb_like_faces');
define('c_al2fb_meta_like_width', 'al2fb_like_width');
define('c_al2fb_meta_like_action', 'al2fb_like_action');
define('c_al2fb_meta_like_font', 'al2fb_like_font');
define('c_al2fb_meta_like_colorscheme', 'al2fb_like_colorscheme');
define('c_al2fb_meta_like_link', 'al2fb_like_link');
define('c_al2fb_meta_like_top', 'al2fb_like_top');
define('c_al2fb_meta_like_iframe', 'al2fb_like_iframe');
define('c_al2fb_meta_post_send_button', 'al2fb_post_send_button');
define('c_al2fb_meta_post_combine_buttons', 'al2fb_post_combine_buttons');
define('c_al2fb_meta_like_box_width', 'al2fb_box_width');
define('c_al2fb_meta_like_box_height', 'al2fb_box_height');
define('c_al2fb_meta_like_box_border', 'al2fb_box_border');
define('c_al2fb_meta_like_box_noheader', 'al2fb_box_noheader');
define('c_al2fb_meta_like_box_nostream', 'al2fb_box_nostream');
define('c_al2fb_meta_comments_posts', 'al2fb_comments_posts');
define('c_al2fb_meta_comments_width', 'al2fb_comments_width');
define('c_al2fb_meta_comments_auto', 'al2fb_comments_auto');
define('c_al2fb_meta_pile_size', 'al2fb_pile_size');
define('c_al2fb_meta_pile_width', 'al2fb_pile_width');
define('c_al2fb_meta_pile_rows', 'al2fb_pile_rows');
define('c_al2fb_meta_reg_width', 'al2fb_reg_width');
define('c_al2fb_meta_login_width', 'al2fb_login_width');
define('c_al2fb_meta_reg_success', 'al2fb_reg_success');
define('c_al2fb_meta_login_regurl', 'al2fb_login_regurl');
define('c_al2fb_meta_login_redir', 'al2fb_login_redir');
define('c_al2fb_meta_login_html', 'al2fb_login_html');
define('c_al2fb_meta_act_width', 'al2fb_act_width');
define('c_al2fb_meta_act_height', 'al2fb_act_height');
define('c_al2fb_meta_act_header', 'al2fb_act_header');
define('c_al2fb_meta_act_recommend', 'al2fb_act_recommend');
define('c_al2fb_meta_open_graph', 'al2fb_open_graph');
define('c_al2fb_meta_open_graph_type', 'al2fb_open_graph_type');
define('c_al2fb_meta_open_graph_admins', 'al2fb_open_graph_admins');
define('c_al2fb_meta_exclude_default', 'al2fb_exclude_default');
define('c_al2fb_meta_exclude_default_video', 'al2fb_exclude_default_video');
define('c_al2fb_meta_not_post_list', 'al2fb_like_not_list');
define('c_al2fb_meta_fb_encoding', 'al2fb_fb_encoding');
define('c_al2fb_meta_fb_locale', 'al2fb_fb_locale');
define('c_al2fb_meta_clear_errors', 'al2fb_clear_errors');
define('c_al2fb_meta_donated', 'al2fb_donated');
define('c_al2fb_meta_rated0', 'al2fb_rated');
define('c_al2fb_meta_rated', 'al2fb_rated1');
define('c_al2fb_meta_stat', 'al2fb_stat');
define('c_al2fb_meta_week', 'al2fb_week');

// Post meta
define('c_al2fb_meta_link_id', 'al2fb_facebook_link_id');
define('c_al2fb_meta_link_time', 'al2fb_facebook_link_time');
define('c_al2fb_meta_link_picture', 'al2fb_facebook_link_picture');
define('c_al2fb_meta_exclude', 'al2fb_facebook_exclude');
define('c_al2fb_meta_exclude_video', 'al2fb_facebook_exclude_video');
define('c_al2fb_meta_error', 'al2fb_facebook_error');
define('c_al2fb_meta_error_time', 'al2fb_facebook_error_time');
define('c_al2fb_meta_image_id', 'al2fb_facebook_image_id');
define('c_al2fb_meta_nolike', 'al2fb_facebook_nolike');
define('c_al2fb_meta_nointegrate', 'al2fb_facebook_nointegrate');
define('c_al2fb_meta_excerpt', 'al2fb_facebook_excerpt');
define('c_al2fb_meta_text', 'al2fb_facebook_text');
define('c_al2fb_meta_video', 'al2fb_facebook_video');
define('c_al2fb_meta_url_param_name', 'al2fb_facebook_url_param_name');
define('c_al2fb_meta_url_param_value', 'al2fb_facebook_url_param_value');
define('c_al2fb_meta_log', 'al2fb_log');

define('c_al2fb_action_update', 'al2fb_action_update');
define('c_al2fb_action_delete', 'al2fb_action_delete');
define('c_al2fb_action_clear', 'al2fb_action_clear');

// Comment meta
define('c_al2fb_meta_fb_comment_id', 'al2fb_facebook_comment_id');

// Logging
define('c_al2fb_log_redir_init', 'al2fb_redir_init');
define('c_al2fb_log_redir_check', 'al2fb_redir_check');
define('c_al2fb_log_redir_time', 'al2fb_redir_time');
define('c_al2fb_log_redir_ref', 'al2fb_redir_ref');
define('c_al2fb_log_redir_from', 'al2fb_redir_from');
define('c_al2fb_log_redir_to', 'al2fb_redir_to');
define('c_al2fb_log_get_token', 'al2fb_get_token');
define('c_al2fb_log_auth_time', 'al2fb_auth_time');
define('c_al2fb_log_ua', 'al2fb_ua');
define('c_al2fb_log_importing', 'al2fb_importing');
define('c_al2fb_last_error', 'al2fb_last_error');
define('c_al2fb_last_error_time', 'al2fb_last_error_time');
define('c_al2fb_last_request', 'al2fb_last_request');
define('c_al2fb_last_request_time', 'al2fb_last_request_time');
define('c_al2fb_last_response', 'al2fb_last_response');
define('c_al2fb_last_response_time', 'al2fb_last_response_time');
define('c_al2fb_last_texts', 'al2fb_last_texts');

// User meta
define('c_al2fb_meta_facebook_id', 'al2fb_facebook_id');
define('c_al2fb_meta_facebook_token', 'al2fb_facebook_token');
define('c_al2fb_meta_facebook_token_time', 'al2fb_facebook_token_time');
define('c_al2fb_meta_facebook_page', 'al2fb_facebook_page');

// Mail
define('c_al2fb_mail_name', 'al2fb_debug_name');
define('c_al2fb_mail_email', 'al2fb_debug_email');
define('c_al2fb_mail_topic', 'al2fb_debug_topic');
define('c_al2fb_mail_msg', 'al2fb_debug_msg');

define('USERPHOTO_APPROVED', 2);

?>
