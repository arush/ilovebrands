<?php

/**
 * Plugin Name: Strictly Auto Tags
 * Version: 2.7
 * Plugin URI: http://www.strictly-software.com/plugins/strictly-auto-tags/
 * Description: This plugin automatically detects tags to place against posts using existing tags as well as a simple formula that detects common tag formats such as Acronyms, names and countries. Whereas other smart tag plugins only detect a single occurance of a tag within a post this plugin will search for the most used tags within the content so that only the most relevant tags get added.
 * Author: Rob Reid
 * Author URI: http://www.strictly-software.com 
 * =======================================================================
 */

/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

require_once(dirname(__FILE__) . "/strictlyautotagfuncs.php");

class StrictlyAutoTags{

	/**
	* current version of plugin 
	*
	* @access protected
	* @var string
	*/
	protected $version = "2.7";

	/**
	* whether or not to remove all the saved options on uninstallation
	*
	* @access protected
	* @var bool
	*/
	protected $uninstall;

   /**
	* look for new tags by searching for Acronyms and names 
	*
	* @access protected
	* @var bool
	*/
	protected $autodiscover; 

   /**
	* treat tags found in the post title as important and automatically add them to the post
	*
	* @access protected
	* @var bool
	*/
	protected $ranktitle; 

	/**
	* treat tags found in certain html tags such as headers or links as important and increase their ranking
	*
	* @access protected
	* @var bool
	*/
	protected $rankhtml; 

   /**
	* The maxiumum number of tags to add to a post
	*
	* @access protected
	* @var integer
	*/
	protected $maxtags; 

	/**
	* The percentage of content that is allowed to be capitalised when auto discovering new tags
	*
	* @access protected
	* @var integer
	*/
	protected $ignorepercentage;

	/**
	* The list of case sensitive noise words to use
	*
	* @access protected
	* @var string
	*/
	protected $noisewords;

	/**
	* The list of case sensitive noise words to use
	*
	* @access protected
	* @var string
	*/
	protected $noisewords_case_sensitive;


	/**
	* This setting determines how nested tags are handled e.g New York, New York City, New York City Fire Department all contain "New York"
	* AUTOTAG_BOTH = all 3 terms will be tagged 
	* AUTOTAG_SHORT= the shortest version "New York" will be tagged and the others dicarded
	* AUTOTAG_LONG = the longest version "New York City Fire Department" will be tagged and the others dicarded
	*/
	protected $nestedtags;


	/**
	* The default list of case insensitive noise words to use
	*
	* @access protected
	* @var string
	*/
	protected $defaultnoisewords = "about|after|a|all|also|an|and|another|any|are|as|at|be|because|been|before|being|between|both|but|by|came|can|come|could|did|do|each|even|for|from|further|furthermore|get|got|had|has|have|he|her|here|hi|him|himself|how|however|i|if|in|indeed|into|is|its|just|like|made|many|may|me|might|more|moreover|most|much|must|my|never|not|now|of|on|only|or|other|our|out|over|put|said|same|see|she|should|since|some|still|such|take|than|that|the|their|them|then|there|therefore|these|they|this|those|through|thus|to|too|under|up|very|was|way|we|well|were|what|when|where|which|while|will|why|with|would|you|your"; 


	/**
	* The default list of case sensitive noise words to use
	*
	* @access protected
	* @var string
	*/
	protected $defaultnoisewords_case_sensitive = "it|who"; 

	/**
	* Holds a regular expression for checking whether a word is a noise word or phrase
	*
	* @access protected
	* @var string
	*/
	protected $isnoisewordregex_case_sensitive;

	/**
	* Holds a regular expression for checking whether a word is a case sensitive noise word or phrase
	*
	* @access protected
	* @var string
	*/
	protected $isnoisewordregex;

	/**
	* Holds a regular expression for removing noise words from a string of words
	*
	* @access protected
	* @var string
	*/
	protected $removenoisewordsregex;

	/**
	* Holds a regular expression for removing case sensitive noise words from a string of words
	*
	* @access protected
	* @var string
	*/
	protected $removenoisewordsregex_case_sensitive;

	/**
	* Max no of words to contain in each tag
	*
	* @access protected
	* @var int
	*/
	protected $maxtagwords;


	/**
	* Whether or not to bold the tagged words
	*
	* @access protected
	* @var bool
	*/
	protected $boldtaggedwords;

	public function __construct(){

		// add any new options for users upgrading the plugin
		StrictlyAutoTagControl::UpgradedOptions();		

		// set up values for config options e.g autodiscover, ranktitle, maxtags
		$this->GetOptions();

		// create some regular expressions required by the parser

		// case insensitive noise noise word regex
		
		// create regex to identify a noise word
		$this->isnoisewordregex							= "/^(?:" . str_replace("\|","|",preg_quote($this->noisewords,"/")) . ")$/i";

		// create regex to replace all noise words in a string
		$this->removenoisewordsregex					= "/\b(" . str_replace("\|","|",preg_quote($this->noisewords,"/")) . ")\b/i";

		// now for case sensitive noise word regex

		// create regex to identify a noise word
		$this->isnoisewordregex_case_sensitive			= "/^(?:" . str_replace("\|","|",preg_quote($this->noisewords_case_sensitive,"/")) . ")$/";

		// create regex to replace all noise words in a string
		$this->removenoisewordsregex_case_sensitive		= "/\b(" . str_replace("\|","|",preg_quote($this->noisewords_case_sensitive,"/")) . ")\b/";

		// load any language specific text
		load_textdomain('strictlyautotags'	, dirname(__FILE__).'/language/'.get_locale().'.mo');

		// add options to admin menu
		add_action('admin_menu'				, array(&$this, 'RegisterAdminPage'));
		
		// set a function to run whenever posts are saved that will call our AutoTag function
		add_action('save_post'				, array(&$this, 'SaveAutoTags'));
		add_action('publish_post'			, array(&$this, 'SaveAutoTags'));
		add_action('post_syndicated_item'	, array(&$this, 'SaveAutoTags'));


	}

	
	/**
	 * Check post content for auto tags
	 *
	 * @param integer $post_id
	 * @param array $post_data
	 * @return boolean
	 */
	public function SaveAutoTags( $post_id = null, $post_data = null ) {

		ShowDebugAutoTag("IN SaveAutoTags post id = " . $post_id);

		global $wpdb;

		$object = get_post($post_id);
		if ( $object == false || $object == null ) {
			return false;
		}
		
		$posttags = $this->AutoTag( $object );

		// add tags to post
		// Append tags if tags to add
		if ( count($posttags) > 0) {


			ShowDebugAutoTag("do we bold auto tags? == " . $this->boldtaggedwords);
			
			ShowDebugAutoTag($posttags);

			if($this->boldtaggedwords){

				ShowDebugAutoTag("call bold tags");

			
				// help SEO by bolding our tags
				$newcontent = $this->AutoBold($object->post_content,$posttags);
				

				ShowDebugAutoTag("our new content is === " . $newcontent);

				$sql = $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = %s WHERE id = %d;", $newcontent,$object->ID);

				ShowDebugAutoTag("SQL is $sql");

				// resave content
				// from what I read all params should be unsanitized so that wordpress can run a prepare itself
				$r = $wpdb->update(
				  'posts',
				  array( 'post_content' => $newcontent ),
				  array( 'id' => $object->ID )
				);

				ShowDebugAutoTag("should have been updated rows = " . $r);


				$r = $wpdb->query($sql);
				
				ShowDebugAutoTag("should have been updated rows = " . $r);

			

			}
			
			// Add tags to posts
			wp_set_object_terms( $object->ID, $posttags, 'post_tag', true );

			ShowDebugAutoTag("after set object terms");
			
			// Clean cache
			if ( 'page' == $object->post_type ) {
				clean_page_cache($object->ID);
			} else {
				clean_post_cache($object->ID);
			}			
		}

		ShowDebugAutoTag("END OF AUTOTAG HOOK");

		return true;
	}

	/** Reformats the main article by highlighting the tagged words
	*
	* @param string $content;
	* @returns string 
	*/
	protected function AutoBold($content,$tags){

		set_time_limit(200);

		ShowDebugAutoTag("IN AutoBold $content we have " . count($tags) . " to bold");

		ShowDebugAutoTag($tags);

		if(!empty($content) && is_array($tags) && count($tags)>0){

			ShowDebugAutoTag("lets loop through our post tags");

			//loop and bold unless they are already inside a bold tag
			foreach($tags as $tag){


				ShowDebugAutoTag("bold all matches of $tag");

				// instead of doing negative lookaheads and entering a world of doom match and then clean	
				// easier to do a positive match than a negative especially with nested matches

				// wrap tags in strong and keep the formatting e.g dont upper case if the tag is lowercase as it might be inside
				// an href or src which might screw it up
				$content = preg_replace("@\b(" . preg_quote($tag) . ")\b@","<strong>$1</strong>",$content);


				// remove bold tags that have been put inside attributes e.g <a href="http://www.<strong>MSNBC</strong>.com">	
				// this can be a bit of killer on large pieces of content so if its causing problems then turn auto bold off
				// anything that has to do negative lookaheads can kill webservers (see my blog for details) but its a lot better
				// for me to match first (by bolding) and then clean up by looping through attributes and stripping than trying
				// to do it all in one negative lookahead regex. I've tried tightening it up and extending the timeout.
				$content = preg_replace_callback("@(\w+)(=['\"][^'\"]*?)(<strong>)([\s\S]+?)(</strong>)([^'\"]*['\"][/> ])@",
							create_function(
							'$matches',					
							'$res = preg_replace("@<\/?strong>@","",$matches[0] );					
							return $res;')
						,$content);
				
				
				// remove any tags that are now in strong that are also inside other "bold" tags
				$content = preg_replace("@(<(h[1-6]|strong|b|em|i|a)[^>]*>[^<]*?)(<strong>" .  preg_quote($tag) . "<\/strong>)(.*?<\/?\\2>)@i","$1{$tag}$4",$content);

			

				ShowDebugAutoTag("look at current bolded content == $content");

			}

		}

		ShowDebugAutoTag("return $content");

		return $content;

	}
				
	/**
	 * Removes any noise words from the system if they are already used as post tags
	 *
	 * @param string $noisewords
	 * @return bool
	 */
	protected function RemoveSavedNoiseWords($noisewords=""){

		ShowDebugAutoTag("IN RemoveSavedNoiseWords");

		set_time_limit(0);

		global $wpdb,$wp_object_cache;

		$deleted = 0;

		if(!empty($noisewords)){

			ShowDebugAutoTag("Format noise words = '$noisewords'");

			// ensure we don't have pipes at beginning or end
			if(substr($noisewords,0,1) == "|"){

				ShowDebugAutoTag("remove starting pipe");

				$noisewords = substr($noisewords,1,strlen($noisewords));
			}
			if(substr($noisewords,-1) == "|"){

				ShowDebugAutoTag("remove trailing pipe");

				$noisewords = substr($noisewords,0,strlen($noisewords)-1);
			}
			
			
			// wrap in quotes for IN statement and make sure each noise word values is escaped
			$sqlin = "'" . preg_replace("@\|@","','",addslashes($noisewords)) . "'";

			
			ShowDebugAutoTag("IN is now $sqlin");
			
			// cannot use the prepare function as it will add extra slashes and quotes
			$sql = sprintf("DELETE a,c
							FROM	{$wpdb->terms} AS a
							LEFT JOIN {$wpdb->term_taxonomy} AS c ON a.term_id = c.term_id				
							WHERE (
									c.taxonomy = 'post_tag'
									AND  a.Name IN(%s)
								);",$sqlin);
		

			ShowDebugAutoTag($sql);

			$deleted = $wpdb->query($sql);	
		
			if($deleted >0){
				
				// clear object cache
				
				unset($wp_object_cache->cache);
				
				$wp_object_cache->cache = array();

			}

			ShowDebugAutoTag("SQL Query deleted this no of rows == " . $deleted);


		}

		return $deleted;
	}


	/**
	 * Deletes unused posts or under used tags	 
	 * $notags is the number of posts a tag must be related to e.g 0 we remove all tags not associated with any post
	 *
	 * @param int  $notags
	 * @return int
	 */
	protected function CleanTags( $notags=1) {
		
		set_time_limit(0);

		global $wpdb,$wp_object_cache;

		$updated = 0;

		// in future rewrite this with a branch so that if we are looking at posts with no tags then
		// we only return from the DB those posts that have no tags

		$sql = $wpdb->prepare("DELETE a,c
								FROM	{$wpdb->terms} AS a
								JOIN	{$wpdb->term_taxonomy} AS c ON a.term_id = c.term_id				
								WHERE (
										c.taxonomy = 'post_tag'
										AND  c.count <= %d
									);",$notags);
		

		ShowDebugAutoTag($sql);

		$updated = $wpdb->query($sql);	
		
		if($updated >0){
			
			// clear object cache
				
			unset($wp_object_cache->cache);
			
			$wp_object_cache->cache = array();

		}

		ShowDebugAutoTag("SQL Query returns " . $updated);

		return $updated;
	}

	/**
	 * Finds the number of under used tags in system
	 *
	 * @return int
	 */
	protected function GetUnderusedTags($notags=1)			
	{
		global $wpdb;

		$tags = 0;

		$sql =  $wpdb->prepare("SELECT	COUNT(*) as Tags
								FROM	{$wpdb->terms} wt
								INNER JOIN {$wpdb->term_taxonomy} wtt 
									ON	wt.term_id=wtt.term_id
								WHERE	wtt.taxonomy='post_tag' 
										AND wtt.count<=%d;",$notags);
		

		ShowDebugAutoTag($sql);

		$tags = $wpdb->get_var(($sql));		

		return $tags;
	}

	

	
	/**
	 * Updates existing posts with tags
	 * The $all_posts param specifies whether all posts are re-tagged or only those without tags
	 *
	 * @param bool  $all_posts
	 * @return int
	 */
	protected function ReTagPosts( $all_posts=false ) {
		
		set_time_limit(0);

		global $wpdb;

		$updated = 0;

		// in future rewrite this with a branch so that if we are looking at posts with no tags then
		// we only return from the DB those posts that have no tags

		$sql = "SELECT id 
				FROM {$wpdb->posts}
				WHERE post_password='' AND post_status='publish' AND post_type='post' 
				ORDER BY post_modified_gmt DESC;";


		ShowDebugAutoTag($sql);

		$posts = $wpdb->get_results($sql);
		
		foreach($posts as $post){

			// definitley a better way to do this but would involve a major rewrite!

			ShowDebugAutoTag("get post id " . $post->id);

			$object = get_post($post->id);
			if ( $object == false || $object == null ) {
				return false;
			}		
			

			$posttags = $this->AutoTag( $object,  $all_posts );

			if($posttags !== false){
			
				$updated++;
				
				ShowDebugAutoTag("we have " .  count($posttags) . " tags to add to this post");

				// add tags to post
				// Append tags if tags to add
				if ( count($posttags) > 0) {
					
					// Add tags to posts
					wp_set_object_terms( $object->ID, $posttags, 'post_tag', true );
					
					// Clean cache
					if ( 'page' == $object->post_type ) {
						clean_page_cache($object->ID);
					} else {
						clean_post_cache($object->ID);
					}			
				}
			}

			unset($object,$posttags);
		}

		unset($posts);		

		return $updated;
	}

	/**
	 * Format content to make searching for new tags easier
	 *
	 * @param string $content
	 * @return string
	 */
	protected function FormatContent($content=""){

		if(!empty($content)){

			// if we are auto discovering tags then we need to reformat words next to full stops so that we don't get false positives
			if($this->autodiscover){
				// ensure capitals next to full stops are decapitalised but only if the word is single e.g
				// change ". The world" to ". the" but not ". United States"
				$content = preg_replace("/(\.[”’\"]?\s*[A-Z][a-z]+\s[a-z])/e","strtolower('$1')",$content);
			}

			// remove plurals
			$content = preg_replace("/(\w)([‘'’]s )/i","$1 ",$content);

			// now remove anything not a letter or number
			$content = preg_replace("/[^\w\d\s\.,\?]/"," ",$content);
			
			// replace new lines with a full stop so we don't get cases of two unrelated strings being matched
			$content = preg_replace("/\r\n/",". ",$content);

			// remove excess space
			$content = preg_replace("/\s{2,}/"," ",$content);			

		}

		return $content;

	}
	
	/**
	 * Checks a word to see if its a known noise word
	 * 
	 * @param string $word
	 * @return boolean
	 */
	protected function IsNoiseWord($word){
		
		//ShowDebugAutoTag("Is $word a noise word == " . $this->isnoisewordregex);

		$count = preg_match($this->isnoisewordregex,$word,$match);

		if(count($match)>0){
			return true;
		}else{			

			// check the case sensitive list
			$count = preg_match($this->isnoisewordregex_case_sensitive,$word,$match);

			if(count($match)>0){
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * Checks whether a word is a roman numeral
	 *
	 * @param string $word
	 * @return boolean
	 */
	function IsRomanNumeral($word){

		if(preg_match("/^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/",$word)){
			return true;
		}else{
			return false;
		}
	}

	/*
	 * removes noise words from a given string
	 *
	 * @param string
	 * @return string
	 */
	protected function RemoveNoiseWords($content){		

		$content = preg_replace($this->removenoisewordsregex," ",$content);

		// remove case sensitive noise words

		$content = preg_replace($this->removenoisewordsregex_case_sensitive," ",$content);

		return $content;
	}

	/*
	 * counts the number of words that capitalised in a string
	 *
	 * @param string
	 * @return integer
	 */
	protected function CountCapitals($words){
		
		$no_caps =	preg_match_all("/\b[A-Z][A-Za-z]*\b/",$words,$matches);			

		return $no_caps;
	}
	
	/*
	 * strips all non words from a string
	 *
	 * @param string
	 * @return string
	 */
	protected function StripNonWords($words){

		// strip everything not space or uppercase/lowercase
		$words = preg_replace("/[^A-Za-z\s]/","",$words);
	
		return $words;
	}

	/**
	 * Searches the passed in content looking for Acronyms to add to the search tags array
	 * 
	 * @param string $content
	 * @param array $searchtags
	 */
	protected function MatchAcronyms($content,&$searchtags){
		
		// easiest way to look for keywords without some sort of list is to look for Acronyms like CIA, AIG, JAVA etc.
		// so use a regex to match all words that are pure capitals 2 chars or more to skip over I A etc
		preg_match_all("/\b([A-Z]{2,})\b/u",$content,$matches,PREG_SET_ORDER);
	
		if($matches){
		
			foreach($matches as $match){
				
				$pat = $match[1];

				// ignore noise words who someone has capitalised as well as roman numerals which may be part of something else e.g World War II
				if(!$this->IsNoiseWord($pat) && !$this->IsRomanNumeral($pat)){
					// add in the format key=value to make removing items easy and quick plus we don't waste overhead running
					// array_unique to remove duplicates!					
					$searchtags[$pat] = trim($pat);
				}
			}
		}

		unset($match,$matches);

	}

	/**
	 * Searches the passed in content looking for Countries to add to the search tags array
	 * 
	 * @param string $content
	 * @param array $searchtags
	 */
	protected function MatchCountries($content,&$searchtags){
		preg_match_all("/\s(Afghanistan|Albania|Algeria|American\sSamoa|Andorra|Angola|Anguilla|Antarctica|Antigua\sand\sBarbuda|Arctic\sOcean|Argentina|Armenia|Aruba|Ashmore\sand\sCartier\sIslands|Australia|Austria|Azerbaijan|Bahrain|Baker\sIsland|Bangladesh|Barbados|Bassas\sda\sIndia|Belarus|Belgium|Belize|Benin|Bermuda|Bhutan|Bolivia|Bosnia\sand\sHerzegovina|Botswana|Bouvet\sIsland|Brazil|British\sVirgin\sIslands|Brunei|Bulgaria|Burkina\sFaso|Burma|Burundi|Cambodia|Cameroon|Canada|Cape\sVerde|Cayman\sIslands|Central\sAfrican\sRepublic|Chad|Chile|China|Christmas\sIsland|Clipperton\sIsland|Cocos\s(Keeling)\sIslands|Colombia|Comoros|Congo|Cook\sIslands|Coral\sSea\sIslands|Costa\sRica|Croatia|Cuba|Cyprus|Czech\sRepublic|Denmark|Djibouti|Dominica|Dominican\sRepublic|Ecuador|Eire|Egypt|El\sSalvador|Equatorial\sGuinea|England|Eritrea|Estonia|Ethiopia|Europa\sIsland|Falkland\sIslands\s|Islas\sMalvinas|Faroe\sIslands|Fiji|Finland|France|French\sGuiana|French\sPolynesia|French\sSouthern\sand\sAntarctic\sLands|Gabon|Gaza\sStrip|Georgia|Germany|Ghana|Gibraltar|Glorioso\sIslands|Greece|Greenland|Grenada|Guadeloupe|Guam|Guatemala|Guernsey|Guinea|Guinea-Bissau|Guyana|Haiti|Heard\sIsland\sand\sMcDonald\sIslands|Holy\sSee\s(Vatican\sCity)|Honduras|Hong\sKong|Howland\sIsland|Hungary|Iceland|India|Indonesia|Iran|Iraq|Ireland|Israel|Italy|Ivory\sCoast|Jamaica|Jan\sMayen|Japan|Jarvis\sIsland|Jersey|Johnston\sAtoll|Jordan|Juan\sde\sNova\sIsland|Kazakstan|Kenya|Kingman\sReef|Kiribati|Korea|Korea|Kuwait|Kyrgyzstan|Laos|Latvia|Lebanon|Lesotho|Liberia|Libya|Liechtenstein|Lithuania|Luxembourg|Macau|Macedonia\sThe\sFormer\sYugoslav\sRepublic\sof|Madagascar|Malawi|Malaysia|Maldives|Mali|Malta|Man\sIsle\sof|Marshall\sIslands|Martinique|Mauritania|Mauritius|Mayotte|Mexico|Micronesia\sFederated\sStates\sof|Midway\sIslands|Moldova|Monaco|Mongolia|Montenegro|Montserrat|Morocco|Mozambique|Namibia|Nauru|Navassa\sIsland|Nepal|Netherlands|Netherlands\sAntilles|New\sCaledonia|New\sZealand|Nicaragua|Nigeria|Niue|Norfolk\sIsland|Northern\sIreland|Northern\sMariana\sIslands|Norway|Oman|Pakistan|Palau|Palmyra\sAtoll|Panama|Papua\sNew\sGuinea|Paracel\sIslands|Paraguay|Peru|Philippines|Pitcairn\sIslands|Poland|Portugal|Puerto\sRico|Qatar|Reunion|Romania|Russia|Rwanda|Saint\sHelena|Saint\sKitts\sand\sNevis|Saint\sLucia|Saint\sPierre\sand\sMiquelon|Saint\sVincent\sand\sthe\sGrenadines|San\sMarino|Sao\sTome\sand\sPrincipe|Saudi\sArabia|Scotland|Senegal|Serbia|Seychelles|Sierra\sLeone|Singapore|Slovakia|Slovenia|Solomon\sIslands|Somalia|South\sAfrica|South\sGeorgia\sand\sthe\sSouth\sSandwich\sIslands|Spain|Spratly\sIslands|Sri\sLanka|Sudan|Suriname|Svalbard|Swaziland|Sweden|Switzerland|Syria|Taiwan|Tajikistan|Tanzania|Thailand|The\sBahamas|The\sGambia|Togo|Tokelau|Tonga|Trinidad\sand\sTobago|Tromelin\sIsland|Tunisia|Turkey|Turkmenistan|Turks\sand\sCaicos\sIslands|Tuvalu|Uganda|Ukraine|United\sArab\sEmirates|UAE|United\sKingdom|UK|United\sStates\sof\sAmerica|USA|Uruguay|Uzbekistan|Vanuatu|Venezuela|Vietnam|Virgin\sIslands|Wake\sIsland|Wales|Wallis\sand\sFutuna|West\sBank|Western\sSahara|Western\sSamoa|Yemen|Zaire|Zambia|Zimbabwe|Europe|Western\sEurope|North\sAmerica|South\sAmerica|Asia|South\sEast\sAsia|Central\sAsia|The\sCaucasus|Middle\sEast|Far\sEast|Scandinavia|Africa|North\sAfrica|North\sPole|South\sPole|Central\sAmerica|Caribbean|London|New\sYork|Paris|Moscow|Beijing|Tokyo|Washington\sDC|Los\sAngeles|Miami|Rome|Sydney|Mumbai|Baghdad|Kabul|Islamabad|Berlin|Palestine|Dublin|Belfast|Tel\sAviv)\s/i",$content,$matches, PREG_SET_ORDER);


		if($matches){
		
			foreach($matches as $match){
				
				$pat = $match[1];

				$searchtags[$pat] = trim($pat);
			}
		}

		unset($match,$matches);

	}

	/**
	 * Searches the passed in content looking for Countries to add to the search tags array
	 * 
	 * @param string $content
	 * @param array $searchtags
	 */
	protected function MatchNames($content,&$searchtags){

		ShowDebugAutoTag("IN MatchNames");

		// look for names of people or important strings of 2+ words that start with capitals e.g Federal Reserve Bank or Barack Hussein Obama
		// this is not perfect and will not handle Irish type surnames O'Hara etc
		@preg_match_all("/((\b[A-Z][^A-Z\s\.,;:\?]+)(\s+[A-Z][^A-Z\s\.,;:\?]+)+\b)/u",$content,$matches,PREG_SET_ORDER);

		// found some results
		if($matches){
		
			foreach($matches as $match){
				
				$pat = $match[1];

				ShowDebugAutoTag("found possible name tag to our stack " . $pat);

				$searchtags[$pat] = trim($pat);
			}
		}
		
		unset($match,$matches);
	}


	/**
	 * check the content to see if the amount of content that is parsable is above the allowed threshold
	 *
	 * @param string
	 * @return boolean
	 */
	protected function ValidContent($content){

		// strip everything not space or uppercase/lowercase letters
		$content	= $this->StripNonWords($content);

		// count the total number of words
		$word_count = str_word_count($content);

		// no words? nothing to analyse
		if($word_count == 0){
			return false;
		}

		// count the number of capitalised words
		$capital_count = $this->CountCapitals($content);

		if($capital_count > 0){
			// check percentage - if its set to 0 then we can only skip the content if its all capitals
			if($this->ignorepercentage > 0){
				$per = round(($capital_count / $word_count) * 100);

				if($per > $this->ignorepercentage){
					return false;	
				}
			}else{
				if($word_count == $capital_count){
					return false;
				}
			}
		}

		return true;
	}


	/**
	 * Parse post content to discover new tags and then rank matching tags so that only the most appropriate are added to a post
	 *
	 * @param object $object
	 * @return array
	 */
	public function AutoTag($object,$all_posts=false){

		if(!$all_posts){
			// skip posts with tags already added
			if ( get_the_tags($object->ID) != false) {
				return false;
			}
		}

		// tags to add to post
		$addtags = array();

		// stack used for working out which tags to add
		$tagstack = array();

		// potential tags to add
		$searchtags = array();

		
		// ensure all html entities have been decoded
		$html		= $object->post_content;
		$article	= html_entity_decode(strip_tags($html));
		$excerpt	= html_entity_decode($object->post_excerpt);
		$title		= html_entity_decode($object->post_title);

		ShowDebugAutoTag("our title is " . $title);

		// no need to trim as empty checks for space
		if(empty($article) && empty($excerpt) && empty($title)){		
			return $addtags;	
		}

		// if we are looking for new tags then check the major sections to see what percentage of words are capitalised
		// as that makes it hard to look for important names and strings
		if($this->autodiscover){
			
			$discovercontent = "";

			ShowDebugAutoTag("do we add the title to our discover content == " . $title);

			// ensure title is not full of capitals
			if($this->ValidContent($title)){

				ShowDebugAutoTag("title is valid so add to discover content");

				// add a full stop to ensure words at the end of the title don't accidentally match those in the content during auto discovery
				$discovercontent .= " " . $title . ". ";				
			}


			// ensure article is not full of capitals
			if($this->ValidContent($article)){
				$discovercontent .= " " . $article . " ";					
			}

			// ensure excerpt  is not full of capitals
			if($this->ValidContent($excerpt)){
				$discovercontent .= " " . $excerpt . " ";					
			}
			
		}else{			
			$discovercontent	= "";
		}

		ShowDebugAutoTag("do we rank the title = " . $this->ranktitle);

		// if we are doing a special parse of the title we don't need to add it to our content as well
		if($this->ranktitle){
			$content			= " " . $article . " " . $excerpt . " ";
		}else{
			$content			= " " . $article . " " . $excerpt . " " . $title . " ";
		}

		// set working variable which will be decreased when tags have been found
		$maxtags			= $this->maxtags;


		// reformat content to remove plurals and punctuation
		$content			= $this->FormatContent($content);
		$discovercontent	= $this->FormatContent($discovercontent);

		// remove noise words from our auto discover content
		// they pose a problem for new tags and not tags that have already been saved as they might legitamitley exist
		// therefore we just want to prevent new tags containing noise words getting added

		ShowDebugAutoTag("Remove Noise words from == $discovercontent");

		$discovercontent	= $this->RemoveNoiseWords($discovercontent);

		ShowDebugAutoTag("our discover content is now == $discovercontent");

		// now if we are looking for new tags and we actually have some valid content to check
		if($this->autodiscover && !empty($discovercontent)){

			ShowDebugAutoTag("look for acronyms and names");
			
			// look for Acronyms in content
			// the searchtag array is passed by reference to prevent copies of arrays and merges later on
			$this->MatchAcronyms($discovercontent,$searchtags);		
			
			// look for countries as these are used as tags quite a lot
			$this->MatchCountries($discovercontent,$searchtags);

			// look for names and important sentences 2-4 words all capitalised
			$this->MatchNames($discovercontent,$searchtags);
		}
		
		//ShowDebugAutoTag("After auto discover our tags are");

		//ShowDebugAutoTag($searchtags);

		// get existing tags from the DB as we can use these as well as any new ones we just discovered
		global $wpdb;

		// just get all the terms from the DB in array format
	
		$dbterms = $wpdb->get_col("
				SELECT	DISTINCT name
				FROM	{$wpdb->terms} AS t
				JOIN	{$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
				WHERE	tt.taxonomy = 'post_tag'
			");
		
		// if we have got some names and Acronyms then add them to our DB terms
		// as well as the search terms we found
		$c = count($searchtags);
		$d = count($dbterms);
		
		if($c > 0 && $d > 0){

			// join the db terms to those we found earlier
			$terms = array_merge($dbterms,$searchtags);
		
			// remove duplicates which come from discovering new tags that already match existing stored tags
			$terms = array_unique($terms);
			
		}elseif($c > 0){

			// just set terms to those we found through autodiscovery
			$terms = $searchtags;

		}elseif($d > 0){

			// just set terms to db results
			$terms = $dbterms;
		}

		// clean up		
		unset($searchtags,$dbterms);
		
		// if we have no terms to search with then quit now
		if(!isset($terms) || !is_array($terms)){
			// return empty array
			return $addtags;
		}
		                          
		
		// do we rank terms in the title higher?
		if($this->ranktitle){

			ShowDebugAutoTag("look inside our title for terms");

			// make it easier to match word boundaries
		//	$title = " " . $title . " ";

			ShowDebugAutoTag("our title is '" . $title . "'");

			// parse the title with our terms adding tags by reference into the tagstack
			// as we want to ensure tags in the title are always tagged we tweak the hitcount by adding 1000
			// in future expand this so we can add other content to search e.g anchors, headers each with their own ranking
			$this->SearchContent($title,$terms,$tagstack,1000);

		}

		ShowDebugAutoTag("so we just searched our title now check html");

		// do we rank terms in html tags such as headers or links higher?
		if($this->rankhtml){

			// get other important content
			@preg_match_all("@<(h[1-6])>([\S\s]+?)<\/?\\1>@i",$html,$matches,PREG_SET_ORDER);
			
			
			if($matches){
			
				foreach($matches as $match){
					
					ShowDebugAutoTag("HEADER MATCH == " . $match[2]);

					if($match[1] == "h1"){
						$score = 500;
					}elseif($match[1] == "h2"){
						$score = 400;
					}elseif($match[1] == "h3"){
						$score = 350;
					}elseif($match[1] == "h4"){
						$score = 300;
					}elseif($match[1] == "h5"){
						$score = 275;
					}elseif($match[1] == "h6"){
						$score = 250;
					}

					$important_content = html_entity_decode(strip_tags($match[2]));

					$this->SearchContent($important_content,$terms,$tagstack,$score);

				}

			}

			ShowDebugAutoTag("get other important tags");

			// get other important content
			preg_match_all("@<(b|em|strong|a)>([\S\s]+?)<\/?\\1>@i",$html,$matches,PREG_SET_ORDER);			
			

			if($matches){
			
				foreach($matches as $match){
					
					ShowDebugAutoTag("HEADER MATCH == " . $match[2]);

					$important_content = html_entity_decode(strip_tags($match[2]));

					$this->SearchContent($important_content,$terms,$tagstack,200);
				}

			}
		}

		ShowDebugAutoTag("now parse our main bit of content");
		
		// now parse the main piece of content
		$this->SearchContent($content,$terms,$tagstack,0);
		
		// cleanup
		unset($terms,$term);
	
		// take the top X items
		if($maxtags != -1 && count($tagstack) > $maxtags){

			ShowDebugAutoTag("take the top $maxtags from the " . count($tagstack) . " we have got for this article");

			// sort our results in decending order using our hitcount
			uasort($tagstack, array($this,'HitCount'));
			
			// return only the results we need
			$tagstack = array_slice($tagstack, 0, $maxtags);
		}

		ShowDebugAutoTag($tagstack);

		// add our results to the array we return which will be added to the post
		foreach($tagstack as $item=>$tag){
			$addtags[] = $tag['term'];
		}
		

		// we don't need to worry about dupes e.g tags added when the rank title check ran and then also added later
		// as Wordpress ensures duplicate taxonomies are not added to the DB
	
		//ShowDebugAutoTag("our full list of tags to add");
		
		//ShowDebugAutoTag($addtags);

		// update counter with the number of tags our plugin has added
		$newtags = count($addtags);

		//ShowDebugAutoTag("we are adding $newtags to the system");

		// add to existing tag count
		update_option('strictlyautotagcount',get_option('strictlyautotagcount') + $newtags);


		// return array of post tags
		return $addtags;

	}

	/**
	 * parses content with a supplied array of terms looking for matches
	 *
	 * @param string content
	 * @param array $terms
	 * @param array $tagstack	
	 * @param integer $tweak	 
	 */
	protected function SearchContent($content,$terms,&$tagstack,$tweak){

		if(empty($content) || !is_array($terms) || !is_array($tagstack)){
			return;
		}

		// remove noise words now so that any tags that we discovered earlier will match
		//$content = $this->RemoveNoiseWords($content);

		// now loop through our content looking for the highest number of matching tags as we don't want to add a tag
		// just because it appears once as that single word would possibly be irrelevant to the posts context.
		foreach($terms as $term){

			// safety check in case some BS gets into the DB!
			if(strlen($term) > 1){

				// for an accurate search use preg_match_all with word boundaries
				// as substr_count doesn't always return the correct number from tests I did
				
				// for exact matches we want to ensure that New York City Fire Department only matches that and not New York City
				if($this->nestedtags == AUTOTAG_LONG){

					$regex = "@(^|[.,;:?]\s*|\s+[a-z1-9]+\s+)" . preg_quote( $term ) . "([.,;:?]|\s+[a-z1-9]+|$)@";

				}else{
					$regex = "@\b" . preg_quote( $term ) . "\b@";
				}

				$addtag		= false;
				$addarray	= array();

				//ShowDebugAutoTag("look for $regex");

				$i = @preg_match_all($regex,$content,$matches);

				// if found then store it with the no of occurances it appeared e.g its hit count
				if($i > 0){

					// if we are tweaking the hitcount e.g for ranking title tags higher
					if($tweak > 0){
						$i = $i + $tweak;
					}

					// do we add all tags whether or not they appear nested inside other matches
					// do we add all tags whether or not they appear nested inside other matches
					if($this->nestedtags == AUTOTAG_BOTH || $this->nestedtags == AUTOTAG_LONG){
	
						// if we already have this term in our stack then update the counter
						if(isset($tagstack[$term])){
						
							$oldcount= $tagstack[$term]['count'];
							$newcount= $oldcount+$i;
							
							// ensure noise words are never added
							if(!$this->IsNoiseWord($term)){

								ShowDebugAutoTag("Add term = $term count = $newcount");

								$addarray	= array("term"=>$term,"count"=>$newcount);
								$addtag		= true;									
							}
						}else{

							// ensure noise words are never added
							if(!$this->IsNoiseWord($term)){

								ShowDebugAutoTag("Add term = $term count = $newcount");

								// add term and hit count to our array
								$addarray	= array("term"=>$term,"count"=>$i);
								$addtag		= true;	

							}
						}
					// must be AUTOTAG_SHORT
					}else{

						$ignore = false;
						
						// loop through existing tags checking for nested matches e.g New York appears in New York City 						
						foreach($tagstack as $key=>$value){

							$oldterm = $value['term'];
							$oldcount= $value['count'];
			
							// check whether our new term is already in one of our old terms
							if(stripos($oldterm,$term)!==false){
								
								// we found our term inside a longer one and as we are keeping the shortest version we need to add
								// the other tags hit count before deletng it as if it was a ranked title we want this version to show
								$i = $i + (int)$oldcount;

								// remove our previously stored tag as we only want the smallest version						
								unset($tagstack[$key]);
							
							// check whether our old term is in our new one
							}elseif(stripos($term,$oldterm)!==false){
								
								// yes it is so keep our short version in the stack and ignore our new term								
								$ignore = true;
								break;
							}
						}
					
						// do we add our new term
						if(!$ignore){		
							// ensure noise words are never added
							if(!$this->IsNoiseWord($term)){

								ShowDebugAutoTag("Add term = $term count = $i");

								// add term and hit count to our array
								$addarray	= array("term"=>$term,"count"=>$i);
								$addtag		= true;	
							}
						}
					}

					if($addtag){

						if($this->maxtagwords > 0){

							//ShowDebugAutoTag("make sure the tag = " .$addarray['term'] . " is less than " . $this->maxtagwords . " words long");

							$wordcount = str_word_count($addarray['term']);

							if($wordcount > $this->maxtagwords){

								ShowDebugAutoTag("this tag has TOO MANY words in it!");
								$addtag = false;
							}
						}
						
						if($addtag){
							$tagstack[$term] = $addarray;
						}							
					}

					unset($addarray);
				}
			}
		}

		// the $tagstack was passed by reference so no need to return it
	}


	/**
	 * used when sorting tag hit count to compare two array items hitcount
	 *
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	protected function HitCount($a, $b) {
		return $b['count'] - $a['count'];
	}

	/**
	 * Register AdminOptions with Wordpress
	 *
	 */
	public function RegisterAdminPage() {
		add_options_page('Strictly Auto Tags', 'Strictly Auto Tags', 10, basename(__FILE__), array(&$this,'AdminOptions'));	
	}

	/**
	 * get saved options otherwise use defaults
	 *	 
	 * @return array
	 */
	protected function GetOptions(){

		$this->uninstall = get_option('strictlyautotag_uninstall');

		// get saved options from wordpress DB
		$options = get_option('strictlyautotags');

		
		// if there are no saved options then use defaults
		if ( !is_array($options) )
		{
			// This array sets the default options for the plugin when it is first activated.
			$options = array('autodiscover'=>true, 'ranktitle'=>true, 'maxtags'=>4, 'ignorepercentage'=>50, 'noisewords'=>$this->defaultnoisewords, 'nestedtags'=>AUTOTAG_LONG, 'rankhtml'=>true, 'maxtagwords'=>3, 'boldtaggedwords' => false, 'noisewords_case_sensitive'=>$this->defaultnoisewords_case_sensitive);
		}else{

			// check defaults in case of new functionality added to plugin after installation
			if(IsNothing($options['nestedtags'])){
				$options['nestedtags'] = AUTOTAGLONG;
			}

			if(IsNothing($options['noisewords'])){
				$options['noisewords'] = $this->defaultnoisewords;
			}

			if(IsNothing($options['noisewords_case_sensitive'])){
				$options['noisewords_case_sensitive'] = $this->defaultnoisewords_case_sensitive;
			}

			if(IsNothing($options['ignorepercentage'])){
				$options['ignorepercentage'] = 50;
			}

			if(IsNothing($options['rankhtml'])){
				$options['rankhtml'] = true;
			}

			if(IsNothing($options['maxtagwords'])){
				$options['maxtagwords'] = 0;
			}

			if(IsNothing($options['boldtaggedwords'])){
				$options['boldtaggedwords'] = false;
			}

			
			
		}

		// set internal members		
		$this->SetValues($options);

		// return options
		return $options;
	}

	/**
	 * save new options to the DB and reset internal members
	 *
	 * @param object $object
	 */
	protected function SaveOptions($options){

		update_option('strictlyautotag_uninstall', $this->uninstall);

		update_option('strictlyautotags', $options);

		// set internal members
		$this->SetValues($options);
	}
	
	/**
	 * sets internal member properties with the values from the options array
	 *
	 * @param object $object
	 */
	protected function SetValues($options){
		
		$this->autodiscover					= $options['autodiscover'];

		$this->ranktitle					= $options['ranktitle'];

		$this->maxtags						= $options['maxtags'];

		$this->ignorepercentage				= $options['ignorepercentage'];

		$this->noisewords					= $options['noisewords'];

		$this->noisewords_case_sensitive	= $options['noisewords_case_sensitive'];

		$this->nestedtags					= $options['nestedtags'];

		$this->rankhtml						= $options['rankhtml'];

		$this->maxtagwords					= $options['maxtagwords'];

		$this->boldtaggedwords				= $options['boldtaggedwords'];

	}

	
	/**
	 * Admin page for backend management of plugin
	 *
	 */
	public function AdminOptions(){

		// ensure we are in admin area
		if(!is_admin()){
			die("You are not allowed to view this page");
		}

		// get saved options
		$options		= $this->GetOptions();

		// get the no of under used tags
		$notags			= get_option('strictlyautotags_underused');

		if(empty($notags) || !is_numeric($notags)){
			$notags = 1;
		}
		

		// message to show to admin if input is invalid
		$noisemsg = $errmsg = $msg	= "";



		if ( $_POST['CleanSubmit'] )
		{

			ShowDebugAutoTag("Clean Tags");

			// check nonce
			check_admin_referer("cleanup","strictlycleanupnonce");

			// do we retag all posts?
			$notags	=  strip_tags(stripslashes($_POST['strictlyautotags-cleanupposts']));	

			ShowDebugAutoTag("notags = " . $notags);

			if(!is_numeric($notags)){
				$errmsg .= __('The value you entered for No of Tagged Posts was invalid.<br />','strictlyautotags');

				$notags = 1;
			}else{

				// save new values to the DB
				update_option('strictlyautotags_underused', $notags);

				ShowDebugAutoTag("Delete all tags related to " . $notags . " or less posts");

				$deleted = $this->CleanTags($notags);

				ShowDebugAutoTag("We deleted " . $deleted . " tags");

				if($deleted == 0){
					$msg = sprintf(__('No Tags were removed','strictlyautotags'),$deleted);
				}else{
					$msg = __('All relevant Tags have been removed','strictlyautotags');
				}
			}
		}

		if ( $_POST['RepostSubmit'] )
		{

			ShowDebugAutoTag("ReTag Posts");

			// check nonce
			check_admin_referer("retag","strictlyretagnonce");

			// do we retag all posts?
			$allposts	= (bool) strip_tags(stripslashes($_POST['strictlyautotags-tagless']));	

			ShowDebugAutoTag("allposts = " . $allposts);

			$updated = $this->ReTagPosts($allposts);

			if($updated == 0){
				$msg = sprintf(__('No Posts were re-tagged','strictlyautotags'),$updated);
			}else if($updated == 1){
				$msg = sprintf(__('1 Post was re-tagged','strictlyautotags'),$updated);
			}else{
				$msg = sprintf(__('%d Posts have been re-tagged','strictlyautotags'),$updated);
			}
		}


		// if our option form has been submitted then save new values
		if ( $_POST['SaveOptionsSubmit'] )
		{
			// check nonce
			check_admin_referer("tagoptions","strictlytagoptionsnonce");

			$this->uninstall			= (bool) strip_tags(stripslashes($_POST['strictlyautotags-uninstall']));

			$options['autodiscover']	= strip_tags(stripslashes($_POST['strictlyautotags-autodiscover']));
			$options['ranktitle']		= strip_tags(stripslashes($_POST['strictlyautotags-ranktitle']));			
			$options['nestedtags']		= strip_tags(stripslashes($_POST['strictlyautotags-nestedtags']));
			$options['rankhtml']		= strip_tags(stripslashes($_POST['strictlyautotags-rankhtml']));
			$options['boldtaggedwords']	= strip_tags(stripslashes($_POST['strictlyautotags-boldtaggedwords']));			
			$options['maxtagwords']		= strip_tags(stripslashes($_POST['strictlyautotags-maxtagwords']));					
			$ignorepercentage			= trim(strip_tags(stripslashes($_POST['strictlyautotags-ignorepercentage'])));			
			$noisewords					= trim(strip_tags(stripslashes($_POST['strictlyautotags-noisewords'])));	
			$noisewords_case_sensitive	= trim(strip_tags(stripslashes($_POST['strictlyautotags-noisewords-case-sensitive'])));	
			$removenoise				= (bool) strip_tags(stripslashes($_POST['strictlyautotags-removenoise']));
				
			// check format is word|word|word
			if(empty($noisewords)){
				$noisewords = $this->defaultnoisewords;
			}else{
				$noisewords = strtolower($noisewords);

				// make sure the noise words don't start or end with pipes
				if( preg_match("/^([-a-z'1-9 ]+\|[-a-z'1-9 ]*)+$/",$noisewords)){	
					$options['noisewords']	= $noisewords;

					ShowDebugAutoTag("do we remove any saved noise words = " . $removenoise);

					// do we try and remove any saved noise words?
					if($removenoise){

						ShowDebugAutoTag("Remove any saved noise words");

						if($this->RemoveSavedNoiseWords( $noisewords )){
							$noisemsg = __('The system has removed all saved noise words from your saved post tag list.<br />','strictlyautotags');
						}else{
							$errmsg .= __('The system couldn\'t find any saved post tags matching your current noise word list.<br />','strictlyautotags');
						}
					}
				}else{
					$errmsg .= __('The noise words you entered are in an invalid format.<br />','strictlyautotags');
				}
			}

			// handle case sensitive words			

			if(empty($noisewords_case_sensitive)){
				$noisewords_case_sensitive = $this->defaultnoisewords_case_sensitive;
			}else{			

				// make sure the noise words don't start or end with pipes
				if( preg_match("/^([-a-z'1-9 ]+\|[-a-z'1-9 ]*)+$/i",$noisewords_case_sensitive)){	
					$options['noisewords_case_sensitive']	= $noisewords_case_sensitive;

					ShowDebugAutoTag("do we remove any saved noise words = " . $removenoise);

					// do we try and remove any saved noise words?
					if($removenoise){

						ShowDebugAutoTag("Remove any saved noise words");

						if($this->RemoveSavedNoiseWords( $noisewords_case_sensitive )){
							$noisemsg = __('The system has removed all saved case sensitive noise words from your saved post tag list.<br />','strictlyautotags');
						}else{
							$errmsg .= __('The system couldn\'t find any saved post tags matching your current case sensitive noise word list.<br />','strictlyautotags');
						}
					}
				}else{
					$errmsg .= __('The noise words you entered are in an invalid format.<br />','strictlyautotags');
				}
			}

			// only set if its numeric
			$maxtags = strip_tags(stripslashes($_POST['strictlyautotags-maxtags']));

			if(is_numeric($maxtags) && $maxtags > 0 && $maxtags <= 20){
				$options['maxtags']		= $maxtags;
			}else{
				$errmsg .= __('The value you entered for Max Tags was invalid: (1 - 20)<br />','strictlyautotags');
				$options['maxtags'] = 4;
			}
			$maxtagwords = strip_tags(stripslashes($_POST['strictlyautotags-maxtagwords']));

			if(is_numeric($maxtagwords) && $maxtagwords >= 0 ){
				$options['maxtagwords']		= $maxtagwords;
			}else{
				$errmsg .= __('The value you entered for Max Tag Words was invalid: (> 0)<br />','strictlyautotags');
				$options['maxtagwords']		= 0;
			}


			if(is_numeric($ignorepercentage) && ($ignorepercentage >= 0 || $ignorepercentage <= 100)){
				$options['ignorepercentage']		= $ignorepercentage;
			}else{
				$errmsg .= __('The value your entered for the Ignore Capitals Percentage was invalid: (0 - 100)<br />','strictlyautotags');
				$options['ignorepercentage']	= 50;
			}
			
			if(!empty($errmsg)){
				$errmsg = substr($errmsg,0,strlen($errmsg)-6);
			}

			// save new values to the DB
			update_option('strictlyautotags', $options);

			$msg = __('Options Saved','strictlyautotags');

			if(!empty($noisemsg)){
				$msg .= "<br />" . $noisemsg;
			}
		}

		echo	'<style type="text/css">
				#StrictlyAutoTagsAdmin h3 {
					font-size:12px;
					font-weight:bold;
					line-height:1;
					margin:0;
					padding:7px 9px 4px;
				}
				div.inside{
					padding: 10px;
				}
				div.tagopt{
					margin-top:17px;
				}
				.donate{
					margin-top:30px;
				}					
				span.notes{
					display:		block;
					padding-left:	5px;
					font-size:		0.8em;	
					margin-top:		7px;
				}
				p.error{
					font-weight:bold;
					color:red;
				}
				p.msg{
					font-weight:bold;
					color:green;
				}
				#StrictlyAutoTagsAdmin ul{
					list-style-type:circle !important;
					padding-left:18px;
				}
				#StrictlyAutoTagsAdmin label{
					font-weight:bold;
				}
				#strictlyautotags-noisewords{
					width:600px;
					height:250px;
				}
				div label:first-child{					
					display:	inline-block;
					width:		275px;
				}
				#lblnoisewords{
					vertical-align:top;
				}
				#supportstrictly{
					margin-bottom: 15px;
				}
				</style>';

		echo	'<div class="wrap" id="StrictlyAutoTagsAdmin">';

		echo	'<div class="postbox">						
					<h3 class="hndle">'.sprintf(__('Strictly AutoTags - Version %s', 'strictlyautotags'),$this->version).'</h3>					
					<div class="inside">';		

		// get no of underused tags
		$underused		= $this->GetUnderusedTags($notags);

		if(!empty($msg)){
			echo '<p class="msg">' . $msg . '</p>';
		}
		if(!empty($errmsg)){
			echo '<p class="error">' . $errmsg . '</p>';
		}

		echo	'<p>'.__('Strictly AutoTags is designed to do one thing and one thing only - automatically add relevant tags to your posts.', 'strictlyautotags').'</p>';

		$installdate = get_option('strictlyautotag_install_date');
		$installtype = get_option('strictlyautotag_install_type');
		$now		 = date('Y-m-d\TH:i:s+00:00',time());		
		$diff		 = (int)((strtotime($now) - strtotime($installdate)) / 60);
	
		$tagged = get_option('strictlyautotagcount');

		ShowDebugAutoTag("we have tagged $tagged tags so far in the $diff minutes since our $installtype on $installdate");

		
		// for all tests we ensure at least 5 mins have passed to prevent hammering
		if(($diff > 10080 && $tagged > 100) || get_option('strictlyautotagcount') > 250){
		//if(1==1){
			

			if($installtype == "upgrade"){
				echo '<p>'. sprintf(__('Strictly AutoTags has automatically generated <strong>%s tags</strong> since upgrading on %s.', 'strictlyautotags'),number_format($tagged),$installdate).'</p>';
			}else{
				echo '<p>'. sprintf(__('Strictly AutoTags has automatically generated <strong>%s tags</strong> since installation on %s.', 'strictlyautotags'),number_format($tagged),$installdate).'</p>';
			}

			$rnd = (rand()%10);

			if($rnd == 1 || $rnd == 3){

				echo  __('<p><strong>How much is your time worth?</strong></p><p>Time is money as the famous saying goes and this plugin must be worth at least a small percentage of the time it has saved you. Why not show your appreciation for this plugin which just keeps getting better by making <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6427652" title="Make a donation">a small donation to help cover my development time?</a>. </p>','strictlyautotags') ;

			}else if(($rnd == 4 || $rnd == 8)  && $tagged > 500){

				echo  __('<p>Plugin developers like myself spend a large portion of their free time to make great code only to give it away for free to people like you. In no other industry does this happen. Can you imagine a builder offering to build an extension on your house for free in the hope that you &quot;might pay him&quot; or hire him later for another job? This is what Wordpress developers do when they give their plugins away for free. If you appreciate our work and want to help us continue to work in this industry
				then please consider <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6427652" title="Make a donation">making a donation</a> to help cover my development time. <strong>Any amount no matter how small would be appreciated.</strong> Thanks for your support. . </p>','strictlyautotags') ;

			}else if(($rnd == 0 || $rnd == 2) && $tagged > 1000){

				$n = floor($tagged / 1000);

				echo sprintf(__('<p><strong>This plugin has saved over %d thousand tags for your site!</strong></p><p>This must be worth at least a small donation to show your appreciation. Remember all donations help me to continue to offer plugins like Strictly AutoTags, <a href="http://wordpress.org/extend/plugins/strictly-tweetbot/">Strictly Tweetbot</a>, <a href="http://wordpress.org/extend/plugins/strictly-google-sitemap/">Strictly Google Sitemap</a> and <a href="http://wordpress.org/extend/plugins/strictly-system-check/">Strictly System Check</a> for free! You can show your support for my development by making <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6427652" title="Make a donation">a donation.</a> Any amount would be appreciated even if its just pennies or cents!</p>','strictlyautotags'),$n);
			}else{
			
				
				echo '<p>' . __('<strong>Support Strictly Software Wordpress Plugin Development by:</strong>','strictlyautotags') . '</p>
					 <ul id="supportstrictly">
						<li><a href="http://www.strictly-software.com/plugins/strictly-auto-tags">Linking to the plugin from your own site or blog so that other people can find out about it.</a></li>
						<li><a href="http://wordpress.org/extend/plugins/strictly-autotags/">Give the plugin a good rating on Wordpress.org or other websites that discuss Wordpress plugins.</a></li>	
						<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6427652">Pleaae make a donation on PayPal. Any amount no matter how small is appreciated!</a></li>
					 </ul>';
			}			
		}			
		else
		{
		
			
			echo '<p>' . __('<strong>You can help Strictly Software Wordpress Plugin Development by:</strong>','strictlyautotags') . '</p>
				 <ul id="supportstrictly">
					<li><a href="http://www.strictly-software.com/plugins/strictly-auto-tags">Linking to the plugin from your own site or blog so that other people can find out about it. If you think this plugin is great then please tell the world about it.</a></li>
					<li><a href="http://wordpress.org/extend/plugins/strictly-autotags/">Give the plugin a good rating on Wordpress.org so that other users download and use it.</a></li>	
					<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=6427652">Please make a donation on PayPal. I have spent considerable time developing this plugin for your free use and a donation would show your appreciation for my hard work and allow me to keep on updating this valuable tool with great new features.</a></li>
				 </ul>';
		}	
		
		echo '<p>'.__('Please remember that this plugin has been developed for the <strong>English language</strong> and will only work with standard English characters e.g A-Z. If you have any problems with the plugin please check that it is not due to UTF-8 characters within the articles you are trying to auto tag.', 'strictlyautotags').'</p>
				<ul><li>'.__('Enable Auto Discovery to find new tags.', 'strictlyautotags').'</li>
				<li>'.__('Suitable words such as Acronyms, Names, Countries and other important keywords will then be identified within the post.', 'strictlyautotags').'</li>
				<li>'.__('Existing tags will also be used to find relevant tags within the post.', 'strictlyautotags').'</li>
				<li>'.__('Set the maximum number of tags to append to a post to a suitable amount. Setting the number too high could mean that tags that only appear once might be added.', 'strictlyautotags').'</li>
				<li>'.__('Treat tags found in the post title, H1 or strong tags as especially important by enabling the Rank Title and Rank HTML options.', 'strictlyautotags').'</li>
				<li>'.__('Handle badly formatted content by setting the Ignore Capitals Percentage to an appropiate amount.', 'strictlyautotags').'</li>
				<li>'.__('Aid Search Engine Optimisation by bolding your matched tags to emphasis to search engines the important terms within your articles.', 'strictlyautotags').'</li>
				<li>'.__('Set the Max Tag Words setting to an appropriate value to prevent long capitalised sentences from matching during auto discovery.', 'strictlyautotags').'</li>					
				<li>'.__('Only the most frequently occurring tags will be added against the post.', 'strictlyautotags').'</li>
				<li>'.__('Re-Tag all your existing posts in one go or just those currently without tags.','strictlyautotags').'</li>
				<li>'.__('Quickly clean up your system by removing under used saved tags or noise words that have already been tagged.','strictlyautotags').'</li></ul>
				</div>
				</div>';

		

		
		echo	'<form name="retag" id="retag" method="post">
				<div class="postbox">						
					<h3 class="hndle">'.__('Re-Tag Existing Posts', 'strictlyautotags').'</h3>					
					<div class="inside">
				'. wp_nonce_field("retag","strictlyretagnonce",false,false) .'
				<div class="tagopt">
				<label for="strictlyautotags-tagless">'.__('Re-Tag All Posts','strictlyautotags').'</label>
				<input type="checkbox" name="strictlyautotags-tagless" id="strictlyautotags-tagless" value="true" ' . ((!IsNothing($allposts)) ? 'checked="checked"' : '') . '/>
				<span class="notes">'.__('Checking this will option will mean that all your posts will be re-tagged otherwise only posts without any current tags will be parsed for appropriate tags.', 'strictlyautotags').'</span>
				</div>
				<p class="submit"><input value="'.__('Re-Tag Posts', 'strictlyautotags').'" type="submit" name="RepostSubmit" id="RepostSubmit"></p>
				</div></div></form>';

				

		echo	'<form name="cleanup" id="cleanup" method="post">
				<div class="postbox">						
				<h3 class="hndle">'.__('Clean Up Tag Database', 'strictlyautotags').'</h3>					
				<div class="inside">
				'. wp_nonce_field("cleanup","strictlycleanupnonce",false,false) .'				
				<p>'.sprintf(__('You currently have %d tags that are only associated with %d or less posts.', 'strictlyautotags'), $underused,$notags).'</p>
				<div class="tagopt">
				<label for="strictlyautotags-cleanupposts">'.__('No of Tagged Posts','strictlyautotags').'</label>
				<input type="text" name="strictlyautotags-cleanupposts" id="strictlyautotags-cleanupposts" value="' . esc_attr($notags) . '" />		
				<span class="notes">'.__('Strictly AutoTags can add a lot of tags into your system very quickly and to keep things fast and your tag numbers down it is advisable to clean up your tags reguarly. You may find that you have lots of articles with only one post related to a tag or you may have deleted articles which will have created orphan tags not associated with any articles at all. You should consider deleting redudant or under used tags if you feel they are not providing any benefit to your site. Change the number to the amount of posts to delete tags for e.g selecting 1 means any tags that are associated with 0 or 1 posts will be removed. If you want to know which tags to delete you should use the standard Wordpress Post Tags admin option to manually check and remove tags one by one.', 'strictlyautotags').'</span>
				</div>
				<p class="submit"><input value="'.__('Clean Tags', 'strictlyautotags').'" type="submit" name="CleanSubmit" id="CleanSubmit" onclick="return confirm(\'Are you sure you want to remove these tags from your system?\');"></p>
				</div></div></form>';

		
		echo	'<form method="post">
				<div class="postbox">						
				<h3 class="hndle">'.__('AutoTag Options', 'strictlyautotags').'</h3>					
				<div class="inside">
				'. wp_nonce_field("tagoptions","strictlytagoptionsnonce",false,false) ;

		echo	'<div class="tagopt">
				<label for="strictlyautotags-uninstall">'.__('Uninstall Plugin when deactivated', 'strictlyautotags').'</label><input type="checkbox" name="strictlyautotags-uninstall" id="strictlyautotags-uninstall" value="true" ' . (($this->uninstall) ? 'checked="checked"' : '') . '/>
				<span class="notes">'.__('Remove all plugin related data and configuration options when the plugin is de-activated.', 'strictlyautotags').'</span>
				</div>';
	
		echo	'<div class="tagopt">
				<label for="strictlyautotags-autodiscover">'.__('Auto Discovery','strictlyautotags').'</label>
				<input type="checkbox" name="strictlyautotags-autodiscover" id="strictlyautotags-autodiscover" value="true" ' . (($options['autodiscover']) ? 'checked="checked"' : '') . '/>				
				<span class="notes">'.__('Automatically discover new tags on each post.', 'strictlyautotags').'</span>
				</div>';

		echo	'<div class="tagopt">
				<label for="strictlyautotags-ranktitle">'.__('Rank Title','strictlyautotags').'</label>
				<input type="checkbox" name="strictlyautotags-ranktitle" id="strictlyautotags-ranktitle" value="true" ' . (($options['ranktitle']) ? 'checked="checked"' : '') . '/>				
				<span class="notes">'.__('Rank tags found within the post title over those found within the article content.', 'strictlyautotags').'</span>
				</div>';

		echo	'<div class="tagopt">
				<label for="strictlyautotags-rankhtml">'.__('Rank HTML','strictlyautotags').'</label>
				<input type="checkbox" name="strictlyautotags-rankhtml" id="strictlyautotags-rankhtml" value="true" ' . (($options['rankhtml']) ? 'checked="checked"' : '') . '/>				
				<span class="notes">'.__('Rank tags found in H1,H2,H3,H4,H5,H6,STRONG,EM,A and B tags more importantly than those found in other content. The score given to each match is weighted so that a match found within an H1 tag is ranked higher than a match within an H6 or strong tag.', 'strictlyautotags').'</span>
				</div>';


		echo	'<div class="tagopt">
				<label for="strictlyautotags-maxtags">'.__('Max Tags','strictlyautotags').'</label>
				<input type="text" name="strictlyautotags-maxtags" id="strictlyautotags-maxtags" value="' . esc_attr($options['maxtags']) . '" />
				<span class="notes">'.__('Maximum no of tags to save (20 max).', 'strictlyautotags').'</span>
				</div>';

		
		echo	'<div class="tagopt">
				<label for="strictlyautotags-maxtagwords">'.__('Max Tag Words','strictlyautotags').'</label>
				<input type="text" name="strictlyautotags-maxtagwords" id="strictlyautotags-maxtagwords" value="' . esc_attr($options['maxtagwords']) . '" />
				<span class="notes">'.__('Set the maximum number of words a saved tag can have or set it to 0 to save tags of all sizes.', 'strictlyautotags').'</span>
				</div>';



		echo	'<div class="tagopt">
				<label for="strictlyautotags-boldtaggedwords">'.__('Bold Tagged Words','strictlyautotags').'</label>
				<input type="checkbox" name="strictlyautotags-boldtaggedwords" id="strictlyautotags-boldtaggedwords" value="true" ' . (($options['boldtaggedwords']) ? 'checked="checked"' : '') . '/>				
				<span class="notes">'.__('Wrap matched tags found within the post article with &lt;strong&gt; tags to aid SEO and empahsis your tags to readers.', 'strictlyautotags').'</span>
				</div>';


		echo	'<div class="tagopt">
				<label for="strictlyautotags-ignorepercentage">'.__('Ignore Capitals Percentage','strictlyautotags').'</label>
				<input type="text" name="strictlyautotags-ignorepercentage" id="strictlyautotags-ignorepercentage" value="' . $options['ignorepercentage'] . '" />				
				<span class="notes">'.__('Badly formatted content that contains too many capitalised words can cause false positives when discovering new tags. This option allows you to tell the system to ignore auto discovery if the percentage of capitalised words is greater than the specified threshold.', 'strictlyautotags').'</span>
				</div>';

		echo	'<div class="tagopt">
				<input type="radio" name="strictlyautotags-nestedtags" id="strictlyautotags-nestedtagslong" value="' . AUTOTAG_LONG . '" ' . (($options['nestedtags'] == AUTOTAG_LONG) ? 'checked="checked"' : '') . '/><label for="strictlyautotags-nestedtagslong">'.__('Tag Longest Version','strictlyautotags').'</label>
				<input type="radio" name="strictlyautotags-nestedtags" id="strictlyautotags-nestedtagsboth" value="' . AUTOTAG_BOTH . '" ' . ((IsNothing($options['nestedtags']) || $options['nestedtags']==AUTOTAG_BOTH  ) ? 'checked="checked"' : '') . '/><label for="strictlyautotags-nestedtagsboth">'.__('Tag All Versions','strictlyautotags').'</label>
				<input type="radio" name="strictlyautotags-nestedtags" id="strictlyautotags-nestedtagsshort" value="' . AUTOTAG_SHORT . '" ' . (($options['nestedtags'] == AUTOTAG_SHORT) ? 'checked="checked"' : '') . '/><label for="strictlyautotags-nestedtagsshort">'.__('Tag Shortest Version','strictlyautotags').'</label>				
				<span class="notes">'.__('This option determines how nested tags are handled e.g <strong><em>New York, New York City, New York City Fire Department</em></strong> all contain the words <strong><em>New York</em></strong>. Setting this option to <strong>Tag All</strong> will mean all 3 get tagged. Setting it to <strong>Tag shortest</strong> will mean the shortest match e.g <strong><em>New York</em></strong> gets tagged and setting it to <strong>Tag Longest</strong> means that only exact matches get tagged.', 'strictlyautotags').'</span>
				</div>';

		echo	'<div class="tagopt">
				<label id="lblnoisewords" for="strictlyautotags-noisewords">'.__('Noise Words','strictlyautotags').'</label>
				<textarea name="strictlyautotags-noisewords" id="strictlyautotags-noisewords" style="width:100%;">' . esc_attr($options['noisewords']) . '</textarea>
				</div>
				<div class="tagopt">
				<label id="lblnoisewords" for="strictlyautotags-noisewords-case-sensitive">'.__('Case Sensitive Noise Words','strictlyautotags').'</label>
				<textarea name="strictlyautotags-noisewords-case-sensitive" id="strictlyautotags-noisewords-case-sensitive" style="width:100%;" >' . esc_attr($options['noisewords_case_sensitive']) . '</textarea>
				</div>
				<div class="tagopt">
				<label for="strictlyautotags-removenoise">'.__('Remove Saved Noise Tags','strictlyautotags').'</label>
				<input type="checkbox" name="strictlyautotags-removenoise" id="strictlyautotags-removenoise" value="false" />				
				<span class="notes">'.__('Noise words or stop words, are commonly used English words like <strong><em>any, or, and</em></strong> that are stripped from the content before analysis as you wouldn\'t want these words being used as tags. Please ensure all words are separated by a pipe | character e.g <strong>a|and|at|as</strong>.) <strong>Whenever you add new noise words to the list you should make sure they are removed from the existing list of saved post tags otherwise they might still get matched. Ticking the Remove Saved Noise Tags option when saving will do this for you. </strong><br />If you want to treat particular words or phrases in a case sensitive manner then add them to the <strong>Case Sensitive Noise Words List.</strong>', 'strictlyautotags').'</span>
				</div>';

		echo	'<p class="submit"><input value="'.__('Save Options', 'strictlyautotags').'" type="submit" name="SaveOptionsSubmit" id="SaveOptionsSubmit"></p></div></div></form>';

		echo	'<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<div class="postbox">						
				<h3 class="hndle">'.__('Donate to Stictly Software', 'strictlyautotags').'</h3>					
				<div class="inside donate">';		

		echo	'<p>'.__('Your help ensures that my work continues to be free and any amount is appreciated.', 'strictlyautotags').'</p>';
		
		echo	'<div style="text-align:center;"><br /><br />
				<input type="hidden" name="cmd" value="_s-xclick"><br />
				<input type="hidden" name="hosted_button_id" value="6427652"><br />
				<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
				<br /></div></div></div></form>
				
				<div class="postbox">						
				<h3 class="hndle">'.__('Stictly Software Recommendations', 'strictlyautotags').'</h3>					
				<div class="inside">				
					<p>'.__('If you enjoy using this Wordpress plugin you might be interested in some other websites, tools and plugins I have developed.', 'strictlyautotags').'</p>
					<ul>
						<li><a href="http://www.strictly-software.com/applications/twitter-hash-tag-hunter" title="'.__('Strictly Software Hash Tag Hunter Application','strictlyautotags').'">'.__('Twitter Hash Tag Hunter Application','strictlyautotags').'</a>
							<p>'.__('Strictly Hash Tag Hunter is a Windows application that is designed to aid Auto Bloggers or Site Owners that make use of Strictly AutoTags and my <a href="http://wordpress.org/extend/plugins/strictly-tweetbot/">Strictly TweetBot plugin</a>. It allows people with new or existing Twitter accounts to find out the #HashTags and @Accounts relevant to the keywords and search terms that your website is based around. Don\'t waste time by Tweeting Hash Tags that aren\'t followed, or by following accounts that are not relevant to your sites content. Save yourself time and energy by letting the <a href="http://www.strictly-software.com/applications/twitter-hash-tag-hunter">Twitter Hash Tag Hunter</a> to do all the important SEO work for you, tracking down key #HashTags and @Accounts your site should be following and tweeting to.','strictlyautotags').'</p>
						</li>	
						<li><a href="http://www.strictly-software.com/plugins/strictly-google-sitemap">'.__('Strictly Google Sitemap','strictlyautotags').'</a>
							<p>'.__('Strictly Google Sitemap is a feature rich Wordpress plugin built for sites requiring high performance. Not only does it use a tiny number of database queries compared to other plugins it uses less memory and was designed specifically for under performing or low spec systems. As well as offering all the features of other sitemap plugins it brings all those missing features such as sitemap index files, XML validation, scheduled builds, configuration analysis and SEO reports.','strictlyautotags').'</p>
						</li>
						<li><a href="http://wordpress.org/extend/plugins/strictly-tweetbot/">'.__('Strictly Tweetbot','strictlyautotags').'</a>
							<p>'.__('Strictly Tweetbot is a Wordpress plugin that allows you to automatically post tweets to multiple accounts or multiple tweets to the same account whenever a post is added to your site. Features include: Content Analysis, Tweet formatting and the ability to use tags or categories as hash tags, OAuth PIN code authorisation and Tweet Reports.','strictlyautotags').'</p>
						</li>
						<li><a href="http://wordpress.org/extend/plugins/strictly-system-check/">'.__('Strictly System Check','strictlyautotags').'</a>
							<p>'.__('Strictly System Check is a Wordpress plugin that allows you to automatically check your sites status at scheduled intervals to ensure it\'s running smoothly and it will run some system checks and send you an email if anything doesn\'t meet your requirements.','strictlyautotags').'</p>
						</li>
						<li><a href="http://www.strictly-software.com/online-tools">'.__('Strictly Online Tools','strictlyautotags').'</a>
							<p>'.__('Strictly Online Tools is a suite of free online tools I have developed which include encoders, unpackers, translators, compressors, scanners and much more.','strictlyautotags').'</p>
						</li>
						<li><a href="http://www.hattrickheaven.com">'.__('Hattrick Heaven','strictlyautotags').'</a>
							<p>'.__('If you like football then this site is for you. Get the latest football news, scores and league standings from around the web on one site. All content is crawled, scraped and reformated in real time so there is no need to leave the site when following news links. Check it out for yourself. ','strictlyautotags').'</p>
						</li>
						<li><a href="http://www.fromthestables.com">'.__('From The Stables','strictlyautotags').'</a>
							<p>'.__('If you like horse racing or betting and want that extra edge when using Betfair then this site is for you. It\'s a members only site that gives you inside information straight from the UK\'s top racing trainers every day. We reguarly post up to 5 winners a day and our members have won thousands since we started in 2010.','StrictlySystemCheck').'</p>
						</li>
						<li><a href="http://www.darkpolitricks.com">'.__('Dark Politricks  ','strictlyautotags').'</a>
							<p>'.__('Tired of being fed news from inside the box? Want to know the important news that the mainstream media doesn\'t want to report on? Then this site is for you. Alternative news, comment and analysis all in one place.','strictlyautotags').'</p>
						</li>						
					</ul>
				</div>			
				</div>';

	}
}


class StrictlyAutoTagControl{

	/**
	 * Called when plugin is deactivated and removes all the settings related to the plugin
	 *
	 */
	public static function Deactivate(){

		if(get_option('strictlyautotag_uninstall')){

			delete_option("strictlyautotags");
			delete_option("strictlyautotagcount");
			delete_option("strictlyautotag_uninstall");
			delete_option("strictlyautotag_install_type");
			delete_option("strictlyautotag_install_date");

		}

	}

	/**
	 * Called when plugin is deactivated and removes all the settings related to the plugin
	 *
	 */
	public static function Activate(){

		// if we havent got this value set then its a new install
		if(!get_option('strictlyautotag_install_type')){
			update_option('strictlyautotag_install_type', 'install');		
		}		

		StrictlyAutoTagControl::UpgradedOptions();		

	}

	/**
	 * Add and set any new options for upgraded plugins
	 *
	 */
	public static function UpgradedOptions(){

		// added in version 2.3

		// if we havent got this set then its from an existing plugin already activated so its not a new install its an upgrade
		if(!get_option('strictlyautotag_install_type')){
			update_option('strictlyautotag_install_type', 'upgrade');		
		}

		// log the install date if we haven't already got one
		if(!get_option('strictlyautotag_install_date')){
			update_option('strictlyautotag_install_date', current_time('mysql'));
		}

		// create and initialise counter
		if(!get_option('strictlyautotagcount')){
			update_option('strictlyautotagcount',0);
		}
	}
}

// register my activate hook to setup the plugin
register_activation_hook(__FILE__, 'StrictlyAutoTagControl::Activate');

// register my deactivate hook to ensure when the plugin is deactivated everything is cleaned up
register_deactivation_hook(__FILE__, 'StrictlyAutoTagControl::Deactivate');

// create auto tag object
$strictlyautotags = new StrictlyAutoTags();

?>
