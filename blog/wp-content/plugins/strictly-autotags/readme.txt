=== Strictly Auto Tags ===
Contributors: Strictly Software
Donate link: http://www.strictly-software.com/donate
Plugin Home: http://www.strictly-software.com/plugins/strictly-auto-tags
Tags: tags, autotag, taxonomy, smarttag
Requires at least: 2.0.2                    
Tested up to: 3.1.0
Stable tag: 2.7

Strictly AutoTags is a plugin that automatically adds the most relevant tags to posts.


== Description ==

Strictly AutoTags is a plugin that scans an English language post for words that could be used as tags and then orders them so that the most relevant
words get added against the post. Just because a word appears in a post that is already in your list of tags does not mean that it should
automatically be added against the article. Therefore the plugin orders all matching tags in descending order and picks only those that occur the most.

As well as using existing tags to work out which words to tag posts with this plugin automatically detects new words to use as tags 
by using a simple rule of thumb I have discovered during my time using Wordpress as a blogging tool. I have found that over 90% of all
tags I use fall into one of the following three categories: Acronyms e.g CIA, FBI, AIG, IT, SQL, ASP, names of people or places and countries.
Therefore using the power of regular expressions I scan posts for words or sentences that match these three groups and then store them
as potential tag candidates.

The more posts are added to a blog the more tags will get added but the good thing about this plugin is that having no existing tags 
stored in your Wordpress DB isn't a bar from using it as it will auto detect suitable tags whenever it comes across them.

Whereas other tag plugins only detect a single occurance of a tag this plugin will search for the most used tags within the content so that 
only relevant tags get added. If you set the MaxTags option to 5 then it will pick the top 5 occurring tags within the post and ignore all others.
The RankTitle option when set means that tags found in the post title are automatically added to the post even if they only occur once and only within
the title.

This plugin is not a replacement for other tag related plugims such as Smart Tags as it doesn't even try to manage the tags within your blog.
The plugin is designed to do one thing and one thing only which is to add the most relevant and appropriate tags to your posts as well as discovering new
tags on the way with as little effort as possible.
As this plugin doesn't rely on HTTP requests to 3rd party tag sites to obtain lists of tags it should be quicker and will find new tags that haven't already
been added to external lists e.g someones name in a news story for example.

Please note this plugin is designed for the English language and will not work with UTF-8 characters.

== Installation ==

This section describes how to install the plugin and get it working.

1. Download the plugin.
2. Unzip the strictly-autotags compressed file.
3. Upload the directory strictlyautotags to the /wp-content/plugins directory on your WordPress blog.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Use the newly created Admin option within Wordpress titled Strictly Auto Tags to set the configuration for the plugin.
6. Tags will now automatically be added to all posts that are added or edited that don't currently have tags associated with it.

Help 

1. If you add a post but no tags are added then it does not mean the plugin is not working just that no tags could be found to associate with the post.

2. Test the plugin is working by creating a new post with the following content:

Title: CIA admits responsibility for torture at Guantanamo Bay

Content: Today the CIA admitted it was responsible for the recent accusations of torture at Guantanamo Bay.

Billy Bob Johnson, the chief station manager at the Guantanamo Bay prison said that the United States of America had to hold its hands up and admit that it had allowed its CIA operatives to feed the prisoners nothing but McDonalds and Kentucky Fried Chicken meals whilst forcing them to listen to Christian Rock Music for up to 20 hour periods at a time without any break.

The CIA apologised for the allegations and promised to review its policy of using fast food and Christian Rock Music as a method of torture.

3. Save the post and check the number of tags that get added. The plugin should have found a number of words to use even if you have no existing saved tags in your site.

4. Some people have complained that they have added words to the stop/noise word list which still get tagged and think the plugin is broken. This is not the case and the problem is usually that the user hasn't removed any new noise words from the systems saved post tags first before re-scanning. The noise words are only used in the auto discovery stage of the auto tagging and if tags have already been saved then the site will use them in it's relevancy check whether or not they have been marked as noise. Version 2.0 has a new option to aid the easy removal of noise words from the saved post tag list and this option should be run whenever new noise words are added.

5. If you have any error messages installing the plugin then please try the following to rule out conflicts with other plugins
	-Disable all other plugins and then try to re-activate the Strictly AutoTag plugin - some caching plugins can cause issues.
	-If that worked, re-enable the plugins one by one to find the plugin causing the problem. Decide which plugin you want to use.
	-If that didn't work check you have the latest version of the plugin software (from Wordpress) and the latest version of Wordpress installed
	-Check you have Javascript and Cookies enabled.
	-If you can code turn on the DEBUG constant and debug the code to find the problem otherwise contact me and offer me some money to fix the issue :)
	-Please remember that you get what you pay for so you cannot expect 24 hour support for a free product. Please bear that in mind if you decide to email me. A donation button
	 is on my site and in the plugin admin page.
	-If you must email me and haven't chosen to donate even the smallest amount of money please read this >> http://blog.strictly-software.com/2011/10/naming-and-shaming-of-programming.html

== Changelog ==

= 1.1 =
* Changed internal members from private to protected.
* Fixed bug in which an empty post returned an unitialised array.
* Split up the main AutoTag method so that the 3 AutoDiscovery tests are in their own methods.
* Put compatibility functions into their own include file.
* Changed comments to phpdoc format.

= 1.2 =
* Added Admin page description text into language specific text handler.
* Added continents and major regions into the MatchCountries method.
* Added noise word removal before name matching in the MatchNames method.
* strip all HTML tags from article content before parsing.
* updated regular expression that decapitalises words next to periods to only affect single capitalised words.

= 1.3 =
* Added TrustTitle method to check whether it can be used to auto discover new tags or not.
* Removed all HTML entities before checking content.
* Added extra safety check to term parser to handle previosuly entered bad terms.
* Added IsNoiseWord function in to check for capitalised noise words.
* Changed regular expressions to remove all non word characters instead of some punctuation.

= 1.4 =
* Added new config option Ignore Percentage which sets the percentage of content when capitalised to ignore during auto discovery.
* Added new config option Noise Words which allows user to set the list of noise words to ignore during auto discovery.
* Added new config option Nested Tags which sets how multiple occurring nested tags such as New York, New York City, New York City Fire Department are handled.
* changed regular expression that matches names to match any number of words.
* Added new functions IsNoiseWord, CountCapitals, StripNonWords, ValidContent, SearchContent to strictlyautotags.class.php.
* Added new function IsNothing to strictlyautotagfuncs.php.
* Removed unneccessary rsort call.
* Changed the coding for merging the stored tags and newly discovered ones.

= 1.5 =
* Added IsRomanNumeral function to skip values identified as Aconyms that are Roman Numerals.
* Modifed a few regular expressions using /u which cause errors in cerain cases.
* Added some error handling on some preg_match statements to prevent errors where unknowable tags or patterns cause issues.
* Updated the ShowDebug statement in the function library to handle arrays.

= 1.6 =
* Modified MatchNames method so that noise words are not removed from the auto discovery text before hand in one go but instead from each match. This prevent two seperate tags from being added together when they shouldn't have been due to a noise word separating the two.
* Updated the SearchContent method so that the noise words are removed before matching.
* Updated FormatContent so newlines are replaced with periods to reduce false combinations.
* Added a period between the title and content when creating initial search strings.

= 1.7 =
* Added option to re-tag all existing posts or just those currently without tags.
* Modified the MatchNames function so that noise words are not removed from potential matches as this can make too many tags nonsensical.

= 1.8 =
* Removed my own FormatRegEx method and replaced it's usage with preg_quote.
* Removed usage of the non standard add_actions and replaced it's usage with multiple add_action calls.
* Added some major cities to the MatchCountries method.
* Added nonces to admin page to improve security.
* Added esc_attr to HTML input values.

= 1.9 =
* Added new admin option which allows users to remove under used tags and keep their saved tag list to a manageable size.
* Ability to specify how many articles a tag has to belong to when being cleaned.
* Added extra help text for noise word list to remind people that when they add noise words they should remove them from the saved post tags as well.

= 2.0 =
* Added new checkbox option to admin config page called "Remove Saved Noise Tags" which on saving will remove any noise words in the list that are currently saved as post tags. This should help the problem where people have thought the system wasn't working due to noise words being matched.
* Changed the format of the admin save page to make it look nicer.

= 2.1 =
* Fixed issue with noise word validator in the admin area so that it handles apostrophes, numbers and dashes.
* Updated the text on the admin page to remind people that this plugin only works with English characters e.g A-Z.

= 2.2 =
* Added new "Rank Important Content" option which ranks matches inside certain HTML tags as more important.
* Added new AUTOTAG_LONG option to increase accuracy so that for content such as New York City Fire Department only an exact match is allowed rather than partials such as New York or New York City.
* Removed the RemoveNoiseWords function call from SearchContent and instead added a check for IsNoiseWord before saving tags.
* Added the RemoveNoiseWords function call to the main AutoTag method to remove noise words from the content used for new tag discovery.
* Updated the SearchContent method to increment hitcounts for previously matched tags.
* Fixed bug with noise word regular expression that was caused by using preg_quote which was adding a slash in front of the pipe delimiters.

= 2.3 =
* Added question mark to the regular expression that matches names and looks for full tag matches.
* Added uninstall option.
* Added counter to show how many tags the plugin has saved since upgrade / installation.
* Added support Strictly-Software.com links to help users support the plugin more easily.
* Added date of installation / upgrade.
* Added register_activation_hook and register_deactivation_hook and the StrictlyAutoTagControl static class.

= 2.4 =
* Added new "Max Tag Words" option which specifies the maximum number of words a tag can have to help prevent long capitalised sentences getting matched.
* Added new "Bold Tagged Words" option to wrap tagged words in strong tags to aid SEO.
* Modified code in the SearchContent method to handle the new Max Tag Words setting.
* Added a few more default locations to the the MatchCountries function including Palestine, Tel Aviv and Belfast.

= 2.5 =
* Added new "Case Sensitive" Noise Word list so that words or phrases that should only be ignored if the exact case matches are handled e.g "it" is a noise word "IT" is an acronym.
* Fixed bug that prevented phrases from being added to the noise word list.
* Updated Bold function so only exact matches of the tag are wrapped in bold tags. This is to prevent incorrect case matches of a tag from being bolded.


= 2.6 =
* Fixed a bug with the case-insensitive noise word list that was lower casing the words before storing them.
* Fixed a bug that tested the format of case-insensitive noise words were correct.
* Added my new Hash Tag Hunter Application to the Stictly Software Recommendations section.

= 2.7 =
* Removed the sponsorship message as per the issue outlined in http://gregsplugins.com/lib/2011/11/26/automattic-bullies/