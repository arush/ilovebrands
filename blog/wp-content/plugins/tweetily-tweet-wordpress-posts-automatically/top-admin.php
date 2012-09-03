<?php
require_once('tweetily.php');
require_once('top-core.php');
require_once( 'Include/top-oauth.php' );
require_once('xml.php');
require_once( 'Include/top-debug.php' );


function top_admin() {
    //check permission
    if (current_user_can('manage_options')) 
        {
        $message = null;
        $message_updated = __("Tweetily options have been updated!", 'Tweetily');
        $response = null;
        $save = true;
        $settings = top_get_settings();

        //on authorize
        if (isset($_GET['TOP_oauth'])) {
            global $top_oauth;

            $result = $top_oauth->get_access_token($settings['oauth_request_token'], $settings['oauth_request_token_secret'], $_GET['oauth_verifier']);

            if ($result) {
                $settings['oauth_access_token'] = $result['oauth_token'];
                $settings['oauth_access_token_secret'] = $result['oauth_token_secret'];
                $settings['user_id'] = $result['user_id'];

                $result = $top_oauth->get_user_info($result['user_id']);
                if ($result) {
                    $settings['profile_image_url'] = $result['user']['profile_image_url'];
                    $settings['screen_name'] = $result['user']['screen_name'];
                    if (isset($result['user']['location'])) {
                        $settings['location'] = $result['user']['location'];
                    } else {
                        $settings['location'] = false;
                    }
                }

                top_save_settings($settings);
                echo '<script language="javascript">window.open ("' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=Tweetily","_self")</script>';
                die;
            }
        }
        //on deauthorize
        else if (isset($_GET['top']) && $_GET['top'] == 'deauthorize') {
            $settings = top_get_settings();
            $settings['oauth_access_token'] = '';
            $settings['oauth_access_token_secret'] = '';
            $settings['user_id'] = '';
            $settings['tweet_queue'] = array();

            top_save_settings($settings);
            echo '<script language="javascript">window.open ("' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=Tweetily","_self")</script>';
            die;
        }
         else if (isset($_GET['top']) && $_GET['top'] == 'reset') {
              print('
			<div id="message" class="updated fade">
				<p>' . __("All settings have been reset. Please update the settings for Tweetily to start tweeting again.", 'Tweetily') . '</p>
			</div>');
         }
        //check if username and key provided if bitly selected
        if (isset($_POST['top_opt_url_shortener'])) {
            if ($_POST['top_opt_url_shortener'] == "bit.ly") {

                //check bitly username
                if (!isset($_POST['top_opt_bitly_user'])) {
                    print('
			<div id="message" class="updated fade">
				<p>' . __('Please enter bit.ly username.', 'Tweetily') . '</p>
			</div>');
                    $save = false;
                }
                //check bitly key
                elseif (!isset($_POST['top_opt_bitly_key'])) {
                    print('
			<div id="message" class="updated fade">
				<p>' . __('Please enter bit.ly API Key.', 'Tweetily') . '</p>
			</div>');
                    $save = false;
                }
                //if both the good to save
                else {
                    $save = true;
                }
            }
        }

		
		if(get_option('next_tweet_time')=='0'){
			$next_tweet_time = time()+ get_option('top_opt_interval') * 60 * 60;
			update_option('next_tweet_time', $next_tweet_time);
		}
        //if submit and if bitly selected its fields are filled then save
        if (isset($_POST['submit']) && $save) {
            $message = $message_updated;

			//
            if (isset($_POST['as_number_tweet'])) {
				if($_POST['as_number_tweet']>0 && $_POST['as_number_tweet']<=10){
					update_option('as_number_tweet', $_POST['as_number_tweet']);
				}elseif($_POST['as_number_tweet']>10){
					update_option('as_number_tweet', 10);
				}else{
					update_option('as_number_tweet', 1);
				}
            }
			if (isset($_POST['as_post_type'])) {
                update_option('as_post_type', $_POST['as_post_type']);
            }
			
			
            //TOP admin URL (current url)
            if (isset($_POST['top_opt_admin_url'])) {
                update_option('top_opt_admin_url', $_POST['top_opt_admin_url']);
            }
            
            //what to tweet 
            if (isset($_POST['top_opt_tweet_type'])) {
                update_option('top_opt_tweet_type', $_POST['top_opt_tweet_type']);
            }

            //additional data
            if (isset($_POST['top_opt_add_text'])) {
                update_option('top_opt_add_text', $_POST['top_opt_add_text']);
            }

            //place of additional data
            if (isset($_POST['top_opt_add_text_at'])) {
                update_option('top_opt_add_text_at', $_POST['top_opt_add_text_at']);
            }

            //include link
            if (isset($_POST['top_opt_include_link'])) {
                update_option('top_opt_include_link', $_POST['top_opt_include_link']);
            }

            //fetch url from custom field?
            if (isset($_POST['top_opt_custom_url_option'])) {
                update_option('top_opt_custom_url_option', true);
            } else {

                update_option('top_opt_custom_url_option', false);
            }

            //custom field to fetch URL from 
            if (isset($_POST['top_opt_custom_url_field'])) {
                update_option('top_opt_custom_url_field', $_POST['top_opt_custom_url_field']);
            } else {

                update_option('top_opt_custom_url_field', '');
            }

            //use URL shortner?
            if (isset($_POST['top_opt_use_url_shortner'])) {
                update_option('top_opt_use_url_shortner', true);
            } else {

                update_option('top_opt_use_url_shortner', false);
            }

            //url shortener to use
            if (isset($_POST['top_opt_url_shortener'])) {
                update_option('top_opt_url_shortener', $_POST['top_opt_url_shortener']);
                if ($_POST['top_opt_url_shortener'] == "bit.ly") {
                    if (isset($_POST['top_opt_bitly_user'])) {
                        update_option('top_opt_bitly_user', $_POST['top_opt_bitly_user']);
                    }
                    if (isset($_POST['top_opt_bitly_key'])) {
                        update_option('top_opt_bitly_key', $_POST['top_opt_bitly_key']);
                    }
                }
            }

            //hashtags option
            if (isset($_POST['top_opt_custom_hashtag_option'])) {
                update_option('top_opt_custom_hashtag_option', $_POST['top_opt_custom_hashtag_option']);
            } else {
                update_option('top_opt_custom_hashtag_option', "nohashtag");
            }

            //use inline hashtags
            if (isset($_POST['top_opt_use_inline_hashtags'])) {
                update_option('top_opt_use_inline_hashtags', true);
            } else {
                update_option('top_opt_use_inline_hashtags', false);
            }

             //hashtag length
            if (isset($_POST['top_opt_hashtag_length'])) {
                update_option('top_opt_hashtag_length', $_POST['top_opt_hashtag_length']);
            } else {
                update_option('top_opt_hashtag_length', 0);
            }
            
            //custom field name to fetch hashtag from 
            if (isset($_POST['top_opt_custom_hashtag_field'])) {
                update_option('top_opt_custom_hashtag_field', $_POST['top_opt_custom_hashtag_field']);
            } else {
                update_option('top_opt_custom_hashtag_field', '');
            }

            //default hashtags for tweets
            if (isset($_POST['top_opt_hashtags'])) {
                update_option('top_opt_hashtags', $_POST['top_opt_hashtags']);
            } else {
                update_option('top_opt_hashtags', '');
            }
			
            //tweet interval 
            if (isset($_POST['top_opt_interval'])) {
                if (is_numeric($_POST['top_opt_interval']) && $_POST['top_opt_interval'] > 0) {
                    update_option('top_opt_interval', $_POST['top_opt_interval']);
                } else {
                    update_option('top_opt_interval', "4");
                }
            }
			
		$next_tweet_time = time()+ get_option('top_opt_interval') * 60 * 60;
		update_option('next_tweet_time', $next_tweet_time);

            //random interval
            if (isset($_POST['top_opt_interval_slop'])) {
                if (is_numeric($_POST['top_opt_interval_slop']) && $_POST['top_opt_interval_slop'] > 0) {
                    update_option('top_opt_interval_slop', $_POST['top_opt_interval_slop']);
                } else {
                    update_option('top_opt_interval_slop', "4");
                }
            }

            //minimum post age to tweet
            if (isset($_POST['top_opt_age_limit'])) {
                if (is_numeric($_POST['top_opt_age_limit']) && $_POST['top_opt_age_limit'] >= 0) {
                    update_option('top_opt_age_limit', $_POST['top_opt_age_limit']);
                } else {
                    update_option('top_opt_age_limit', "30");
                }
            }

            //maximum post age to tweet
            if (isset($_POST['top_opt_max_age_limit'])) {
                if (is_numeric($_POST['top_opt_max_age_limit']) && $_POST['top_opt_max_age_limit'] > 0) {
                    update_option('top_opt_max_age_limit', $_POST['top_opt_max_age_limit']);
                } else {
                    update_option('top_opt_max_age_limit', "0");
                }
            }
//option as_number_tweet

		
		
            //option to enable log
            if ( isset($_POST['top_enable_log'])) {
                update_option('top_enable_log', true);
		global $top_debug; 												
		$top_debug->enable( true );
                
            }
            else{
                update_option('top_enable_log', false);
                global $top_debug;
		$top_debug->enable( false );	
            }
        
            //categories to omit from tweet
            if (isset($_POST['post_category'])) {
                update_option('top_opt_omit_cats', implode(',', $_POST['post_category']));
            } else {
                update_option('top_opt_omit_cats', '');
            }

            //successful update message
            print('
			<div id="message" class="updated fade">
				<p>' . __('Tweetily Options Updated.', 'Tweetily') . '</p>
			</div>');
        }
        //tweet now clicked
        elseif (isset($_POST['tweet'])) {
			update_option('top_opt_last_update',time());
            $tweet_msg = top_opt_tweet_old_post();
            print('
			<div id="message" class="updated fade">
				<p>' . __($tweet_msg, 'Tweetily') . '</p>
			</div>');
        }
        elseif (isset($_POST['reset'])) {
           top_reset_settings();
           echo '<script language="javascript">window.open ("' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=Tweetily&top=reset","_self")</script>';
                die;
        }


	//set up data into fields from db
        	global $wpdb;

		$admin_url = site_url('/wp-admin/admin.php?page=Tweetily');        	

		//Current URL - updated querie for those with caching plugins
		//$admin_url = $wpdb->get_var("select option_value from wp_options where option_name = 'top_opt_admin_url';");
        	//$admin_url = get_option('top_opt_admin_url');
        
        if (!isset($admin_url)) {
            $admin_url = top_currentPageURL();
			update_option('top_opt_admin_url', $admin_url);
        }
        
        //what to tweet?
        $tweet_type = get_option('top_opt_tweet_type');
        if (!isset($tweet_type)) {
            $tweet_type = "title";
        }

        //additional text
        $additional_text = get_option('top_opt_add_text');
        if (!isset($additional_text)) {
            $additional_text = "";
        }

        //position of additional text
        $additional_text_at = get_option('top_opt_add_text_at');
        if (!isset($additional_text_at)) {
            $additional_text_at = "beginning";
        }

        //include link in tweet
        $include_link = get_option('top_opt_include_link');
        if (!isset($include_link)) {
            $include_link = "no";
        }

        //use custom field to fetch url
        $custom_url_option = get_option('top_opt_custom_url_option');
        if (!isset($custom_url_option)) {
            $custom_url_option = "";
        } elseif ($custom_url_option)
            $custom_url_option = "checked";
        else
            $custom_url_option="";

        //custom field name for url
        $custom_url_field = get_option('top_opt_custom_url_field');
        if (!isset($custom_url_field)) {
            $custom_url_field = "";
        }

        //use url shortner?
        $use_url_shortner = get_option('top_opt_use_url_shortner');
        if (!isset($use_url_shortner)) {
            $use_url_shortner = "";
        } elseif ($use_url_shortner)
            $use_url_shortner = "checked";
        else
            $use_url_shortner="";

        //url shortner
        $url_shortener = get_option('top_opt_url_shortener');
        if (!isset($url_shortener)) {
            $url_shortener = top_opt_URL_SHORTENER;
        }

        //bitly key
        $bitly_api = get_option('top_opt_bitly_key');
        if (!isset($bitly_api)) {
            $bitly_api = "";
        }

        //bitly username
        $bitly_username = get_option('top_opt_bitly_user');
        if (!isset($bitly_username)) {
            $bitly_username = "";
        }

        //hashtag option
        $custom_hashtag_option = get_option('top_opt_custom_hashtag_option');
        if (!isset($custom_hashtag_option)) {
            $custom_hashtag_option = "nohashtag";
        }

        //use inline hashtag
        $use_inline_hashtags = get_option('top_opt_use_inline_hashtags');
        if (!isset($use_inline_hashtags)) {
            $use_inline_hashtags = "";
        } elseif ($use_inline_hashtags)
            $use_inline_hashtags = "checked";
        else
            $use_inline_hashtags="";

         //hashtag length
        $hashtag_length = get_option('top_opt_hashtag_length');
        if (!isset($hashtag_length)) {
            $hashtag_length = "20";
        }
        
        //custom field 
        $custom_hashtag_field = get_option('top_opt_custom_hashtag_field');
        if (!isset($custom_hashtag_field)) {
            $custom_hashtag_field = "";
        }

        //default hashtag
        $twitter_hashtags = get_option('top_opt_hashtags');
        if (!isset($twitter_hashtags)) {
            $twitter_hashtags = top_opt_HASHTAGS;
        }

        //interval
        $interval = get_option('top_opt_interval');
        if (!(isset($interval) && is_numeric($interval))) {
            $interval = top_opt_INTERVAL;
        }

        //random interval
        $slop = get_option('top_opt_interval_slop');
        if (!(isset($slop) && is_numeric($slop))) {
            $slop = top_opt_INTERVAL_SLOP;
        }

        //min age limit
        $ageLimit = get_option('top_opt_age_limit');
        if (!(isset($ageLimit) && is_numeric($ageLimit))) {
            $ageLimit = top_opt_AGE_LIMIT;
        }

        //max age limit
        $maxAgeLimit = get_option('top_opt_max_age_limit');
        if (!(isset($maxAgeLimit) && is_numeric($maxAgeLimit))) {
            $maxAgeLimit = top_opt_MAX_AGE_LIMIT;
        }

        
        //check enable log
        $top_enable_log = get_option('top_enable_log');
        if (!isset($top_enable_log)) {
            $top_enable_log = "";
        } elseif ($top_enable_log)
            $top_enable_log = "checked";
        else
            $top_enable_log="";
        
        //set omitted categories
        $omitCats = get_option('top_opt_omit_cats');
        if (!isset($omitCats)) {
            $omitCats = top_opt_OMIT_CATS;
        }

        $x = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__));

        print('
			<div class="wrap">
				<h2>' . __('Tweetily - Tweet WP Posts Automatically by - ', 'Tweetily') . ' <a href="http://www.themana.gr">Flavio Martins</a></h2>
<h3>If you like this plugin, follow <a href="http://www.twitter.com/themanagr">@themanagr</a> on Twitter to help keep this plugin free...FOREVER!</h3>

<a href="https://twitter.com/themanagr" class="twitter-follow-button" data-show-count="true" data-size="large">Follow @themanagr</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<br /><br />

				<form id="top_opt" name="top_TweetOldPost" action="" method="post">
					<input type="hidden" name="top_opt_action" value="top_opt_update_settings" />
					<fieldset class="options">
						<div class="option">
							<label for="top_opt_twitter_username">' . __('', 'Tweetily') . '</label>


<div id="profile-box">');
        if (!$settings["oauth_access_token"]) {

            echo '<a href="' . top_get_auth_url() . '" class="auth-twitter">Sign in with Twitter</a>';
        } else {
            echo '<img class="avatar" src="' . $settings["profile_image_url"] . '" alt="" />
							<h4>' . $settings["screen_name"] . '</h4>';
            if ($settings["location"]) {
                echo '<h5>' . $settings["location"] . '</h5>';
            }
            echo '<p>

								You\'re Connected! <a href="' . $_SERVER["REQUEST_URI"] . '&top=deauthorize" onclick=\'return confirm("Are you sure you want to deauthorize your Twitter account?");\'>Click here to deauthorize</a>.<br />

							</p>

							<div class="retweet-clear"></div>
					';
        }
		$as_number_tweet = get_option('as_number_tweet');
		$as_post_type = get_option('as_post_type');
		
		
        print('</div>
						</div>
                                               
						<div class="countdown_opt" style="width:100%;height:auto;overflow: hidden;float:none;border-bottom: dashed 1px #ccc;"><br />
						<label style="margin-left:40px;"><strong>Next Tweet coming in:</strong></label>
						<div id="defaultCountdown" style="width:20%;margin-left:15%;margin-bottom:40px;"></div>
						</div>
						
						<div class="option" >
							<label for="top_opt_tweet_type" >' . __('Tweet Content:<br /><span class="desc">What do you want to share?<span>', 'Tweetily') . '</label>
							<select id="top_opt_tweet_type" name="top_opt_tweet_type" style="width:150px">
								<option value="title" ' . top_opt_optionselected("title", $tweet_type) . '>' . __(' Post Title Only ', 'Tweetily') . ' </option>
								<option value="body" ' . top_opt_optionselected("body", $tweet_type) . '>' . __(' Post Body Only ', 'Tweetily') . ' </option>
								<option value="titlenbody" ' . top_opt_optionselected("titlenbody", $tweet_type) . '>' . __(' Both Title & Body ', 'Tweetily') . ' </option>
							</select>
                                                        
						</div>
						
						
						<div class="option" >
							<label for="top_opt_add_text">' . __('Additional Text:<br /><span class="desc">Text added to your auto posts.<span>', 'Tweetily') . '</label>
							<input type="text" size="25" name="top_opt_add_text" id="top_opt_add_text" value="' . $additional_text . '" autocomplete="off" />
						</div>
						<div class="option" >
							<label for="top_opt_add_text_at">' . __('Additional Text Location:<br /><span class="desc">Where you want the added text.<span>', 'Tweetily') . ':</label>
							<select id="top_opt_add_text_at" name="top_opt_add_text_at" style="width:175px">
								<option value="beginning" ' . top_opt_optionselected("beginning", $additional_text_at) . '>' . __(' Beginning of the tweet ', 'Tweetily') . '</option>
								<option value="end" ' . top_opt_optionselected("end", $additional_text_at) . '>' . __(' End of the tweet ', 'Tweetily') . '</option>
							</select>
						</div>
						
						<div class="option">
							<label for="top_opt_include_link">' . __('Include Link:<br /><span class="desc">Include a link to your post?<span>', 'Tweetily') . '</label>
							<select id="top_opt_include_link" name="top_opt_include_link" style="width:150px" onchange="javascript:showURLOptions()">
								<option value="false" ' . top_opt_optionselected("false", $include_link) . '>' . __(' No ', 'Tweetily') . '</option>
								<option value="true" ' . top_opt_optionselected("true", $include_link) . '>' . __(' Yes ', 'Tweetily') . '</option>
							</select>
						</div>
                                                
						<div id="urloptions" style="display:none">
						
                                                
						
						<div class="option">
							<label for="top_opt_use_url_shortner">' . __('Use URL shortner?:<br /><span class="desc">Shorten the link to your post.<span>', 'Tweetily') . '</label>
							<input onchange="return showshortener()" type="checkbox" name="top_opt_use_url_shortner" id="top_opt_use_url_shortner" ' . $use_url_shortner . ' />
							
						</div>
						
						<div  id="urlshortener">
						<div class="option">
							<label for="top_opt_url_shortener">' . __('URL Shortener Service', 'Tweetily') . ':</label>
							<select name="top_opt_url_shortener" id="top_opt_url_shortener" onchange="javascript:showURLAPI()" style="width:100px;">
									<option value="is.gd" ' . top_opt_optionselected('is.gd', $url_shortener) . '>' . __('is.gd', 'Tweetily') . '</option>
									<option value="su.pr" ' . top_opt_optionselected('su.pr', $url_shortener) . '>' . __('su.pr', 'Tweetily') . '</option>
									<option value="bit.ly" ' . top_opt_optionselected('bit.ly', $url_shortener) . '>' . __('bit.ly', 'Tweetily') . '</option>
									<option value="tr.im" ' . top_opt_optionselected('tr.im', $url_shortener) . '>' . __('tr.im', 'Tweetily') . '</option>
									<option value="3.ly" ' . top_opt_optionselected('3.ly', $url_shortener) . '>' . __('3.ly', 'Tweetily') . '</option>
									<option value="u.nu" ' . top_opt_optionselected('u.nu', $url_shortener) . '>' . __('u.nu', 'Tweetily') . '</option>
									<option value="1click.at" ' . top_opt_optionselected('1click.at', $url_shortener) . '>' . __('1click.at', 'Tweetily') . '</option>
									<option value="tinyurl" ' . top_opt_optionselected('tinyurl', $url_shortener) . '>' . __('tinyurl', 'Tweetily') . '</option>
							</select>
						</div>
						<div id="showDetail" style="display:none">
							<div class="option">
								<label for="top_opt_bitly_user">' . __('bit.ly Username', 'Tweetily') . ':</label>
								<input type="text" size="25" name="top_opt_bitly_user" id="top_opt_bitly_user" value="' . $bitly_username . '" autocomplete="off" />
							</div>
							
							<div class="option">
								<label for="top_opt_bitly_key">' . __('bit.ly API Key', 'Tweetily') . ':</label>
								<input type="text" size="25" name="top_opt_bitly_key" id="top_opt_bitly_key" value="' . $bitly_api . '" autocomplete="off" />
							</div>
						</div>
                                                </div>
					</div>
						

                                                <div class="option" >
							<label for="top_opt_custom_hashtag_option">' . __('#Hashtags:<br /><span class="desc">Include #hashtags in your auto posts.<span>', 'Tweetily') . '</label>
                                                        <select name="top_opt_custom_hashtag_option" id="top_opt_custom_hashtag_option" onchange="javascript:return showHashtagCustomField()" style="width:275px;">
									<option value="nohashtag" ' . top_opt_optionselected('nohashtag', $custom_hashtag_option) . '>' . __('No. Don\'t add any hashtags', 'Tweetily') . '</option>
                                                                        <option value="common" ' . top_opt_optionselected('common', $custom_hashtag_option) . '>' . __('Yes. Use common hashtags for all tweets', 'Tweetily') . '</option>    
									<option value="categories" ' . top_opt_optionselected('categories', $custom_hashtag_option) . '>' . __('Yes, Use hashtags from post categories', 'Tweetily') . '</option>
									<option value="tags" ' . top_opt_optionselected('tags', $custom_hashtag_option) . '>' . __('Yes. Use create hashtags from post tags', 'Tweetily') . '</option>
									
									
							</select>
							
                                                        
						</div>
						<div id="inlinehashtag" style="display:none;">
						<div class="option">
							<label for="top_opt_use_inline_hashtags">' . __('Use inline hashtags: ', 'Tweetily') . '</label>
							<input type="checkbox" name="top_opt_use_inline_hashtags" id="top_opt_use_inline_hashtags" ' . $use_inline_hashtags . ' /> 
                                                       
						</div>
                                                
                                                <div class="option">
							<label for="top_opt_hashtag_length">' . __('Maximum characters for hashtags: ', 'Tweetily') . '</label>
							<input type="text" size="25" name="top_opt_hashtag_length" id="top_opt_hashtag_length" value="' . $hashtag_length . '" /> 
                                                       <strong>(If 0, all hashtags will be included.)</strong>
						</div>
						</div>
						<div id="customhashtag" style="display:none;">
						<div class="option">
							<label for="top_opt_custom_hashtag_field">' . __('Custom field name', 'Tweetily') . ':</label>
							<input type="text" size="25" name="top_opt_custom_hashtag_field" id="top_opt_custom_hashtag_field" value="' . $custom_hashtag_field . '" autocomplete="off" />
							<strong>Get hashtags from this custom field</strong>
						</div>
						
						</div>
                                                <div id="commonhashtag" style="display:none;">
						<div class="option">
							<label for="top_opt_hashtags">' . __('Common #hashtags for your tweets', 'Tweetily') . ':</label>
							<input type="text" size="25" name="top_opt_hashtags" id="top_opt_hashtags" value="' . $twitter_hashtags . '" autocomplete="off" />
							<strong>Include #. (e.g. #marketing, #blogging, #custserv)</strong>
						</div>
						</div>
						<div class="option" >
							<label for="top_opt_interval">' . __('Time between tweets: <br /><span class="desc">Minimum time between your tweets?<span>', 'Tweetily') . '</label>
							<input type="text" id="top_opt_interval" maxlength="5" value="' . $interval . '" name="top_opt_interval" /> Hour / Hours <strong>(If 0, it will default to 4 hours.)</strong>
                                                       
						</div>
						<div class="option" >
							<label for="top_opt_interval_slop">' . __('Random Time Added: <br /><span class="desc">Random time added to make your post normal.<span>', 'Tweetily') . '</label>
							<input type="text" id="top_opt_interval_slop" maxlength="5" value="' . $slop . '" name="top_opt_interval_slop" /> Hour / Hours <strong>(If 0, it will default to 4 hours.)</strong>
                                                            
						</div>
						<div class="option" >
							<label for="top_opt_age_limit">' . __('Minimum age of post: <br /><span class="desc">Include post in tweets if at least this age.<span>', 'Tweetily') . '</label>
							<input type="text" id="top_opt_age_limit" maxlength="5" value="' . $ageLimit . '" name="top_opt_age_limit" /> Day / Days
							<strong>(If 0, it will include today.)</strong>
                                                           
						</div>
						
						<div class="option" >
							<label for="top_opt_max_age_limit">' . __('Maximum age of post: <br /><span class="desc">Don\'t include posts older than this.<span>', 'Tweetily') . '</label>
                                                        <input type="text" id="top_opt_max_age_limit" maxlength="5" value="' . $maxAgeLimit . '" name="top_opt_max_age_limit" /> Day / Days
                                                       <strong>(If 0, all posts will be included.)</strong>
						</div>
						
                                                <div class="option" >
							<label for="top_enable_log">' . __('Enable Logging: ', 'Tweetily') . '</label>
							<input type="checkbox" name="top_enable_log" id="top_enable_log" ' . $top_enable_log . ' /> 
                                                        <strong>Yes, save a log of actions in log file.</strong>    
                                                       
						</div>
						<div class="option">
						<label class="ttip">Number of Tweets: <span class="desc">Number of tweets to share each time.<span></label>
						  <input type="text" value="'.$as_number_tweet.'" name="as_number_tweet"/>
						</div>
						
						<div class="option">
						<label class="ttip">Select post type: <span class="desc">What type of items do you want to share?<span></label>


						<select name="as_post_type">
							<option value="post">Only Posts</option>
							<option value="page">Only Pages</option>
							<option value="all">Both Posts & Pages</option>
						</select> Currently sharing:&nbsp;'.$as_post_type.'
						</div>
                                        
				    	<div class="option category">
				    	<div style="float:left">
						    	<label class="catlabel">' . __('Exclude Categories: <span class="desc">Check categories not to share.<span>', 'Tweetily') . '</label> </div>
						    	<div style="float:left">
						    		<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
								');
        wp_category_checklist(0, 0, explode(',', $omitCats));
        print('				    		</ul>
              <div style="clear:both;padding-top:20px;">
                                                          <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=ExcludePosts">Exclude specific posts</a> from selected categories.
                                                              </div>
                                                              

								</div>
                                                               
								</div>
					</fieldset>
					
                    	<div class="option">
							<label for="top_opt_admin_url">' . __('Your Tweetily Plugin Admin URL', 'Tweetily') . ':</label>
							<input type="text" style="width:500px" id="top_opt_admin_url" value="' . $admin_url . '" name="top_opt_admin_url" /><br /><strong>(Note: If this does not show your current URL in this textbox, paste the current URL in this textbox, then click "Update Options".)</strong>  
						</div>                  

                                                
						<p class="submit"><input type="submit" name="submit" onclick="javascript:return validate()" value="' . __('Update Tweetily Options', 'Tweetily') . '" />
						<input type="submit" name="tweet" value="' . __('Tweet Now!', 'Tweetily') . '" />
                                                <input type="submit" onclick=\'return confirm("This will reset all the setting, including your account, omitted categories and excluded posts. Are you sure you want to reset all the settings?");\' name="reset" value="' . __('Reset Settings', 'Tweetily') . '" /><br /><br /><strong>Note: Please remember to click "Update Settings" after making any changes.</strong>
					</p>
						
				</form><script language="javascript" type="text/javascript">
function showURLAPI()
{
	var urlShortener=document.getElementById("top_opt_url_shortener").value;
	if(urlShortener=="bit.ly")
	{
		document.getElementById("showDetail").style.display="block";
		
	}
	else
	{
		document.getElementById("showDetail").style.display="none";
		
	}
	
}

function validate()
{

	if(document.getElementById("showDetail").style.display=="block" && document.getElementById("top_opt_url_shortener").value=="bit.ly")
	{
		if(trim(document.getElementById("top_opt_bitly_user").value)=="")
		{
			alert("Please enter bit.ly username.");
			document.getElementById("top_opt_bitly_user").focus();
			return false;
		}

		if(trim(document.getElementById("top_opt_bitly_key").value)=="")
		{
			alert("Please enter bit.ly API key.");
			document.getElementById("top_opt_bitly_key").focus();
			return false;
		}
	}
 if(trim(document.getElementById("top_opt_interval").value) != "" && !isNumber(trim(document.getElementById("top_opt_interval").value)))
        {
            alert("Enter only numeric in Minimum interval between tweet");
		document.getElementById("top_opt_interval").focus();
		return false;
        }
         if(trim(document.getElementById("top_opt_interval_slop").value) != "" && !isNumber(trim(document.getElementById("top_opt_interval_slop").value)))
        {
            alert("Enter only numeric in Random interval");
		document.getElementById("top_opt_interval_slop").focus();
		return false;
        }
        if(trim(document.getElementById("top_opt_age_limit").value) != "" && !isNumber(trim(document.getElementById("top_opt_age_limit").value)))
        {
            alert("Enter only numeric in Minimum age of post");
		document.getElementById("top_opt_age_limit").focus();
		return false;
        }
 if(trim(document.getElementById("top_opt_max_age_limit").value) != "" && !isNumber(trim(document.getElementById("top_opt_max_age_limit").value)))
        {
            alert("Enter only numeric in Maximum age of post");
		document.getElementById("top_opt_max_age_limit").focus();
		return false;
        }
	if(trim(document.getElementById("top_opt_max_age_limit").value) != "" && trim(document.getElementById("top_opt_max_age_limit").value) != 0)
	{
	if(eval(document.getElementById("top_opt_age_limit").value) > eval(document.getElementById("top_opt_max_age_limit").value))
	{
		alert("Post max age limit cannot be less than Post min age iimit");
		document.getElementById("top_opt_age_limit").focus();
		return false;
	}
	}
}

function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function showCustomField()
{
	if(document.getElementById("top_opt_custom_url_option").checked)
	{
		document.getElementById("customurl").style.display="block";
	}
	else
	{
		document.getElementById("customurl").style.display="none";
	}
}

function showHashtagCustomField()
{
	if(document.getElementById("top_opt_custom_hashtag_option").value=="custom")
	{
		document.getElementById("customhashtag").style.display="block";
                document.getElementById("commonhashtag").style.display="none";
                 document.getElementById("inlinehashtag").style.display="block";
	}
        else if(document.getElementById("top_opt_custom_hashtag_option").value=="common")
	{
		document.getElementById("customhashtag").style.display="none";
                document.getElementById("commonhashtag").style.display="block";
                document.getElementById("inlinehashtag").style.display="block";
	}
        else if(document.getElementById("top_opt_custom_hashtag_option").value=="nohashtag")
	{
		document.getElementById("customhashtag").style.display="none";
                document.getElementById("commonhashtag").style.display="none";
                document.getElementById("inlinehashtag").style.display="none";
	}
	else
	{
                document.getElementById("inlinehashtag").style.display="block";
		document.getElementById("customhashtag").style.display="none";
                document.getElementById("commonhashtag").style.display="none";
	}
}

function showURLOptions()
{
    if(document.getElementById("top_opt_include_link").value=="true")
	{
		document.getElementById("urloptions").style.display="block";
	}
	else
	{
		document.getElementById("urloptions").style.display="none";
	}
}

function isNumber(val)
{
    if(isNaN(val)){
        return false;
    }
    else{
        return true;
    }
}

function showshortener()
{
						

	if((document.getElementById("top_opt_use_url_shortner").checked))
		{
			document.getElementById("urlshortener").style.display="block";
		}
		else
		{
			document.getElementById("urlshortener").style.display="none";
		}
}
function setFormAction()
{
    if(document.getElementById("top_opt_admin_url").value == "")
    {
        document.getElementById("top_opt_admin_url").value=location.href;
        document.getElementById("top_opt").action=location.href;
    }
    else
    {
        document.getElementById("top_opt").action=document.getElementById("top_opt_admin_url").value;
    }
}

setFormAction();
showURLAPI();
showshortener();
showCustomField();
showHashtagCustomField();
showURLOptions();

</script>');


echo "<script type='text/javascript' src='".plugins_url('countdown/jquery-1.7.1.min.js', __FILE__)."'></script>";

echo "<script type='text/javascript' src='".plugins_url('countdown/jquery.countdown.pack.js', __FILE__)."'></script>";

$next_tweet_time = get_option('next_tweet_time') ;
echo "<script type='text/javascript'>
$(function () {
	var untilDay = new Date($next_tweet_time * 1000);
	$('#defaultCountdown').countdown({until: untilDay , format: 'HMS'});
});
</script>";

    } else {
        print('
			<div id="message" class="updated fade">
				<p>' . __('Oh no! Permission error, please contact your Web site administrator.', 'Tweetily') . '</p>
			</div>');
    }
}

function top_opt_optionselected($opValue, $value) {
    if ($opValue == $value) {
        return 'selected="selected"';
    }
    return '';
}

function top_opt_head_admin() {
    $home = get_settings('siteurl');
    $base = '/' . end(explode('/', str_replace(array('\\', '/top-admin.php'), array('/', ''), __FILE__)));
    $stylesheet = $home . '/wp-content/plugins' . $base . '/css/tweet-old-post.css';
    echo('<link rel="stylesheet" href="' . $stylesheet . '" type="text/css" media="screen" />');
}

?>
