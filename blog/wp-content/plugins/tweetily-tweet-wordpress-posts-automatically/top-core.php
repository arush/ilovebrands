<?php

require_once( 'Include/top-oauth.php' );
global $top_oauth;
$top_oauth = new TOPOAuth;

if ( function_exists('w3tc_pgcache_flush') ) {
w3tc_pgcache_flush();
w3tc_dbcache_flush();
w3tc_minify_flush();
w3tc_objectcache_flush();
$cache = ' and W3TC Caches cleared';
}

function top_tweet_old_post() {
//check last tweet time against set interval and span
    if (top_opt_update_time()) {
            update_option('top_opt_last_update', time());
	    top_opt_tweet_old_post();
    }
}


function top_currentPageURL() {
      if(!isset($_SERVER['REQUEST_URI'])){
        $serverrequri = $_SERVER['PHP_SELF'];
    }else{
        $serverrequri =    $_SERVER['REQUEST_URI'];
    }
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = top_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$serverrequri;
    
}

function top_strleft($s1, $s2) {
	return substr($s1, 0, strpos($s1, $s2));
}

//get random post and tweet
function top_opt_tweet_old_post() {
    return top_generate_query();
}

function top_generate_query($can_requery = true)
{
    global $wpdb;
    $rtrn_msg="";
    $omitCats = get_option('top_opt_omit_cats');
    $maxAgeLimit = get_option('top_opt_max_age_limit');
    $ageLimit = get_option('top_opt_age_limit');
    $exposts = get_option('top_opt_excluded_post');
    $exposts = preg_replace('/,,+/', ',', $exposts);

    $top_opt_tweeted_posts = array();
    $top_opt_tweeted_posts = get_option('top_opt_tweeted_posts');
    
    if(!$top_opt_tweeted_posts)
        $top_opt_tweeted_posts = array();
        
    if($top_opt_tweeted_posts != null)
        $already_tweeted = implode(",", $top_opt_tweeted_posts);
    else
        $already_tweeted="";
    
    if (substr($exposts, 0, 1) == ",") {
        $exposts = substr($exposts, 1, strlen($exposts));
    }
    if (substr($exposts, -1, 1) == ",") {
        $exposts = substr($exposts, 0, strlen($exposts) - 1);
    }

    if (!(isset($ageLimit) && is_numeric($ageLimit))) {
        $ageLimit = top_opt_AGE_LIMIT;
    }

    if (!(isset($maxAgeLimit) && is_numeric($maxAgeLimit))) {
        $maxAgeLimit = top_opt_MAX_AGE_LIMIT;
    }
    if (!isset($omitCats)) {
        $omitCats = top_opt_OMIT_CATS;
    }
$as_post_type = get_option('as_post_type');
$as_number_tweet = get_option('as_number_tweet');
if($as_number_tweet<=0){$as_number_tweet = 1;}
if($as_number_tweet>10){$as_number_tweet = 10;}

//trying to fix multiposts
//if($last<1){$as_number_tweet = 0;}
if($as_post_type!='all'){
	$pt = "post_type = '$as_post_type' AND";
}
    $sql = "SELECT ID
            FROM $wpdb->posts
            WHERE $pt post_status = 'publish' ";
    
    if(is_numeric($ageLimit))
    {
        if($ageLimit > 0)
                $sql = $sql . " AND post_date <= curdate( ) - INTERVAL " . $ageLimit . " day";
    }
    
    if ($maxAgeLimit != 0) {
        $sql = $sql . " AND post_date >= curdate( ) - INTERVAL " . $maxAgeLimit . " day";
    }

    if (isset($exposts)) {
        if (trim($exposts) != '') {
            $sql = $sql . " AND ID Not IN (" . $exposts . ") ";
        }
    }

    if (isset($already_tweeted)) {
    if(trim($already_tweeted) !="")
    {
        $sql = $sql . " AND ID Not IN (" . $already_tweeted . ") ";
    }
    }
    if ($omitCats != '') {
        $sql = $sql . " AND NOT (ID IN (SELECT tr.object_id FROM " . $wpdb->prefix . "term_relationships AS tr INNER JOIN " . $wpdb->prefix . "term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'category' AND tt.term_id IN (" . $omitCats . ")))";
    }
    $sql = $sql . "
            ORDER BY RAND() 
            LIMIT $as_number_tweet ";

    $oldest_post = $wpdb->get_results($sql);
    
    if($oldest_post == null)
    {
        if($can_requery)
        {
            $top_opt_tweeted_posts=array();
            update_option('top_opt_tweeted_posts', $top_opt_tweeted_posts);
           return top_generate_query(false);
        }
        else
        {
           return "No post found to tweet. Please check your settings and try again."; 
        }
    }



    if(isset($oldest_post)){
		 $ret = '';
		 foreach($oldest_post as $k=>$odp){
                    array_push($top_opt_tweeted_posts, $odp->ID);
         	    $ret .= 'Tweet '.$k.' : '.top_opt_tweet_post($odp->ID).'<br/>';
		}
                update_option('top_opt_tweeted_posts', $top_opt_tweeted_posts);

if ( function_exists('w3tc_pgcache_flush') ) {
w3tc_pgcache_flush();
w3tc_dbcache_flush();
w3tc_minify_flush();
w3tc_objectcache_flush();
$cache = ' and W3TC Caches cleared';
}

		$next_tweet_time = time()+ get_option('top_opt_interval') * 60 * 60;
		update_option('next_tweet_time', $next_tweet_time);

		return $ret;
     }
     return $rtrn_msg;
   }

//tweet for the passed random post
function top_opt_tweet_post($oldest_post) {
    global $wpdb;
    $post = get_post($oldest_post);
    $content = "";
    $to_short_url = true;
    $shorturl = "";
    $tweet_type = get_option('top_opt_tweet_type');
    $additional_text = get_option('top_opt_add_text');
    $additional_text_at = get_option('top_opt_add_text_at');
    $include_link = get_option('top_opt_include_link');
    $custom_hashtag_option = get_option('top_opt_custom_hashtag_option');
    $custom_hashtag_field = get_option('top_opt_custom_hashtag_field');
    $twitter_hashtags = get_option('top_opt_hashtags');
    $url_shortener = get_option('top_opt_url_shortener');
    $custom_url_option = get_option('top_opt_custom_url_option');
    $to_short_url = get_option('top_opt_use_url_shortner');
    $use_inline_hashtags = get_option('top_opt_use_inline_hashtags');
    $hashtag_length = get_option('top_opt_hashtag_length');

    if ($include_link != "false") {
        $permalink = get_permalink($oldest_post);

        if ($custom_url_option) {
            $custom_url_field = get_option('top_opt_custom_url_field');
            if (trim($custom_url_field) != "") {
                $permalink = trim(get_post_meta($post->ID, $custom_url_field, true));
            }
        }

        if ($to_short_url) {

            if ($url_shortener == "bit.ly") {
                $bitly_key = get_option('top_opt_bitly_key');
                $bitly_user = get_option('top_opt_bitly_user');
                $shorturl = shorten_url($permalink, $url_shortener, $bitly_key, $bitly_user);
            } else {
                $shorturl = shorten_url($permalink, $url_shortener);
            }
        } else {
            $shorturl = $permalink;
        }
    }

    if ($tweet_type == "title" || $tweet_type == "titlenbody") {
        $title = stripslashes($post->post_title);
        $title = strip_tags($title);
        $title = preg_replace('/\s\s+/', ' ', $title);
    } else {
        $title = "";
    }

    if ($tweet_type == "body" || $tweet_type == "titlenbody") {
        $body = stripslashes($post->post_content);
        $body = strip_tags($body);
        $body = preg_replace('/\s\s+/', ' ', $body);
    } else {
        $body = "";
    }

    if ($tweet_type == "titlenbody") {
        if ($title == null) {
            $content = $body;
        } elseif ($body == null) {
            $content = $title;
        } else {
            $content = $title . " - " . $body;
        }
    } elseif ($tweet_type == "title") {
        $content = $title;
    } elseif ($tweet_type == "body") {
        $content = $body;
    }

    if ($additional_text != "") {
        if ($additional_text_at == "end") {
            $content = $content . " - " . $additional_text;
        } elseif ($additional_text_at == "beginning") {
            $content = $additional_text . ": " . $content;
        }
    }

    $hashtags = "";
    $newcontent = "";
    if ($custom_hashtag_option != "nohashtag") {

        if ($custom_hashtag_option == "common") {
//common hashtag
            $hashtags = $twitter_hashtags;
        }
//post custom field hashtag
        elseif ($custom_hashtag_option == "custom") {
            if (trim($custom_hashtag_field) != "") {
                $hashtags = trim(get_post_meta($post->ID, $custom_hashtag_field, true));
            }
        } elseif ($custom_hashtag_option == "categories") {
            $post_categories = get_the_category($post->ID);
            if ($post_categories) {
                foreach ($post_categories as $category) {
                    $tagname = str_replace(".", "", str_replace(" ", "", $category->cat_name));
                    if ($use_inline_hashtags) {
                        if (strrpos($content, $tagname) === false) {
                            $hashtags = $hashtags . "#" . $tagname . " ";
                        } else {
                            $newcontent = preg_replace('/\b' . $tagname . '\b/i', "#" . $tagname, $content, 1);
                        }
                    } else {
                        $hashtags = $hashtags . "#" . $tagname . " ";
                    }
                }
            }
        } elseif ($custom_hashtag_option == "tags") {
            $post_tags = get_the_tags($post->ID);
            if ($post_tags) {
                foreach ($post_tags as $tag) {
                    $tagname = str_replace(".", "", str_replace(" ", "", $tag->name));
                    if ($use_inline_hashtags) {
                        if (strrpos($content, $tagname) === false) {
                            $hashtags = $hashtags . "#" . $tagname . " ";
                        } else {
                            $newcontent = preg_replace('/\b' . $tagname . '\b/i', "#" . $tagname, $content, 1);
                        }
                    } else {
                        $hashtags = $hashtags . "#" . $tagname . " ";
                    }
                }
            }
        }

        if ($newcontent != "")
            $content = $newcontent;
    }



    if ($include_link != "false") {
        if (!is_numeric($shorturl) && (strncmp($shorturl, "http", strlen("http")) == 0)) {
            
        } else {
            return "OOPS!!! problem with your URL shortning service. Some signs of error " . $shorturl . ".";
        }
    }

    $message = set_tweet_length($content, $shorturl, $hashtags, $hashtag_length);
    $status = urlencode(stripslashes(urldecode($message)));
    if ($status) {
        $poststatus = top_update_status($message);
        if ($poststatus == true)
        {
            return "Success! Just tweeted successfully.";
        }
        else {
            return "Oh no! Something went wrong. Please try again.";
        }
    }
    return "Oh no! Looks like there are problems. Please email flavio@themana.gr.";
}

//send request to passed url and return the response
function send_request($url, $method='GET', $data='', $auth_user='', $auth_pass='') {
    $ch = curl_init($url);
    if (strtoupper($method) == "POST") {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    }
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($auth_user != '' && $auth_pass != '') {
        curl_setopt($ch, CURLOPT_USERPWD, "{$auth_user}:{$auth_pass}");
    }
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpcode != 200) {
        return $httpcode;
    }

    return $response;
}


/* returns a result form url */
function top_curl_get_result($url) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

function top_get_bitly_short_url($url,$login,$appkey,$format='txt') {
  $connectURL = 'http://api.bit.ly/v3/shorten?login='.$login.'&apiKey='.$appkey.'&uri='.urlencode($url).'&format='.$format;
  return top_curl_get_result($connectURL);
}

//Shorten long URLs with is.gd or bit.ly.
function shorten_url($the_url, $shortener='is.gd', $api_key='', $user='') {

    if (($shortener == "bit.ly") && isset($api_key) && isset($user)) {
            $response = top_get_bitly_short_url($the_url, $user, $api_key);
    } elseif ($shortener == "su.pr") {
        $url = "http://su.pr/api/simpleshorten?url={$the_url}";
        $response = send_request($url, 'GET');
    } elseif ($shortener == "tr.im") {
        $url = "http://api.tr.im/api/trim_simple?url={$the_url}";
        $response = send_request($url, 'GET');
    } elseif ($shortener == "3.ly") {
        $url = "http://3.ly/?api=em5893833&u={$the_url}";
        $response = send_request($url, 'GET');
    } elseif ($shortener == "tinyurl") {
        $url = "http://tinyurl.com/api-create.php?url={$the_url}";
        $response = send_request($url, 'GET');
    } elseif ($shortener == "u.nu") {
        $url = "http://u.nu/unu-api-simple?url={$the_url}";
        $response = send_request($url, 'GET');
    } elseif ($shortener == "1click.at") {
        $url = "http://1click.at/api.php?action=shorturl&url={$the_url}&format=simple";
        $response = send_request($url, 'GET');
    } else {
        $url = "http://is.gd/api.php?longurl={$the_url}";
        $response = send_request($url, 'GET');
    }

    return $response;
}

//Shrink a tweet and accompanying URL down to 140 chars.
function set_tweet_length($message, $url, $twitter_hashtags="", $hashtag_length=0) {

    $tags = $twitter_hashtags;
    $message_length = strlen($message);
    $url_length = strlen($url);
    //$cur_length = strlen($tags);
    if ($hashtag_length == 0)
        $hashtag_length = strlen($tags);

    if ($twitter_hashtags != "") {
        if (strlen($tags) > $hashtag_length) {
            $tags = substr($tags, 0, $hashtag_length);
            $tags = substr($tags, 0, strrpos($tags, ' '));
        }
        $hashtag_length = strlen($tags);
    }

    if ($message_length + $url_length + $hashtag_length > 140) {


        $shorten_message_to = 140 - $url_length - $hashtag_length;
        $shorten_message_to = $shorten_message_to - 4;
//$message = $message." ";
        if (strlen($message) > $shorten_message_to) {
            $message = substr($message, 0, $shorten_message_to);
            $message = substr($message, 0, strrpos($message, ' '));
        }
        $message = $message . "...";
    }
    return $message . " " . $url . " " . $tags;
}

//check time and update the last tweet time
function top_opt_update_time() {

   
        return top_to_update();
    
}

if ( function_exists('w3tc_pgcache_flush') ) {
w3tc_pgcache_flush();
w3tc_dbcache_flush();
w3tc_minify_flush();
w3tc_objectcache_flush();
$cache = ' and W3TC Caches cleared';
}

function top_to_update() {
    global $wpdb;
    //have to use normal query to prevent the caching plug-in from caching the last update time
    $last  = $wpdb->get_var("select SQL_NO_CACHE option_value from $wpdb->options where option_name = 'top_opt_last_update';");
    //$last_test = get_option('top_opt_last_update');
    $interval = get_option('top_opt_interval');
    $slop = get_option('top_opt_interval_slop');

    if (!(isset($interval))) {
        $interval = top_opt_INTERVAL;
    }
    else if(!(is_numeric($interval)))
    {
        $interval = top_opt_INTERVAL;
    }
    
    
    if (!(isset($slop))) {
        $slop = top_opt_INTERVAL_SLOP;
    }
    else if(!(is_numeric($slop)))
    {
        $slop = top_opt_INTERVAL_SLOP;
    }
    
    
    $passed = time() - $last;
    	//old testing code to test the actual values going into the DB
	//$wpdb->query("insert into wp_timetable (time,last,ret,url) values('".time()."',$last,$passed,'".$_SERVER['PHP_SELF']."');");

    $interval = $interval * 60 * 60;
    $slop = $slop * 60 * 60;
    if (false === $last) {
        $ret = 1;
    } else if (is_numeric($last)) {
        $ret = (time() - $last) > $interval;
    }

    return $ret;        
}

function top_get_auth_url() {
    global $top_oauth;
    $settings = top_get_settings();

    $token = $top_oauth->get_request_token();
    if ($token) {
        $settings['oauth_request_token'] = $token['oauth_token'];
        $settings['oauth_request_token_secret'] = $token['oauth_token_secret'];

        top_save_settings($settings);

        return $top_oauth->get_auth_url($token['oauth_token']);
    }
}

function top_update_status($new_status) {
    global $top_oauth;
    $settings = top_get_settings();

    if (isset($settings['oauth_access_token']) && isset($settings['oauth_access_token_secret'])) {
        return $top_oauth->update_status($settings['oauth_access_token'], $settings['oauth_access_token_secret'], $new_status);
    }

    return false;
}

function top_has_tokens() {
    $settings = top_get_settings();

    return ( $settings['oauth_access_token'] && $settings['oauth_access_token_secret'] );
}

function top_is_valid() {
    return twit_has_tokens();
}

function top_do_tweet($post_id) {
    $settings = top_get_settings();

    $message = top_get_message($post_id);

// If we have a valid message, Tweet it
// this will fail if the Tiny URL service is done
    if ($message) {
// If we successfully posted this to Twitter, then we can remove it from the queue eventually
        if (twit_update_status($message)) {
            return true;
        }
    }

    return false;
}

function top_get_settings() {
    global $top_defaults;

    $settings = $top_defaults;

    $wordpress_settings = get_option('top_settings');
    if ($wordpress_settings) {
        foreach ($wordpress_settings as $key => $value) {
            $settings[$key] = $value;
        }
    }

    return $settings;
}

function top_save_settings($settings) {
    update_option('top_settings', $settings);
}

function top_reset_settings()
{
    delete_option('top_settings');
    update_option('top_enable_log','');
update_option('top_opt_add_text','');
update_option('top_opt_add_text_at','beginning');
update_option('top_opt_age_limit',30);
update_option('top_opt_bitly_key','');
update_option('top_opt_bitly_user','');
update_option('top_opt_custom_hashtag_field','');
update_option('top_opt_custom_hashtag_option','nohashtag');
update_option('top_opt_custom_url_field','');
update_option('top_opt_custom_url_option','');
update_option('top_opt_excluded_post','');
update_option('top_opt_hashtags','');
update_option('top_opt_hashtag_length','20');
update_option('top_opt_include_link','no');
update_option('top_opt_interval',4);
update_option('top_opt_interval_slop',4);
delete_option('top_opt_last_update');
update_option('top_opt_max_age_limit',60);
update_option('top_opt_omit_cats','');
update_option('top_opt_tweet_type','title');
delete_option('top_opt_tweeted_posts');
update_option('top_opt_url_shortener','is.gd');
update_option('top_opt_use_inline_hashtags','');
update_option('top_opt_use_url_shortner','');
}





?>