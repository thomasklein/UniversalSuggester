<?php
/*
 * UniversalSuggester 1.0 - an extension for the MediaWiki Software (http://mediawiki.org) 
 * allowing fast paced information insertion with an AJAX powered inline search bar
 *
 * @copyright 2011 Thomas Klein (thomas.klein83@gmail.com)
 * @creditsTo Erweiterungen im Auftrag der Twoonix Software GmbH (http://www.twoonix.com/)
 * @version 1.0 (2011-06-23)
 * @licence Dual licensed under the MIT and GPL licenses:
 *   - http://www.opensource.org/licenses/mit-license.php
 *   - http://www.gnu.org/licenses/gpl.html
 * 
 * @uses slightly modified version of the jQuery plugin Autocomplete 1.0.2 
 * by Dylan Verheul, Dan G. Switzer, Anjesh Tuladhar, Jörn Zaefferer 
 * (http://bassistance.de/jquery-plugins/jquery-plugin-autocomplete/)
 * @uses jquery 1.4.2 (http://jquery.com/)
 *
 * @similar MediaWiki extensions CategorySuggest, Link Suggest
 * @addtogroup Extensions
 * 
 * @TODO - change results to links by holding the command button
 * 		  - by holding the alt button, display the page content in an inline if possible 
 */

/**
 * Setup and Hooks for the UniversalSuggester extension
 */
	if( !defined( 'MEDIAWIKI' ) ) {
		echo( "This file is part of an extension to the MediaWiki software and cannot be used standalone.\n" );
		die( 1 );
	}
	
	global $wgUseAjax, $wgExtensionCredits, $wgHooks, $wgAjaxExportList;
	
# check for AJAX support, otherwise this extension won't work	
	if (!$wgUseAjax) {
		wfDebug('$wgUseAjax is not enabled, aborting extension setup for UniversalSuggester.');
		return;
	}
		
###################################
# 	CONFIGURE THE EXTENSION	
###################################
	
# set path to i18n file		
	$wgExtensionMessagesFiles['UniversalSuggester'] = dirname( __FILE__ ) . '/UniversalSuggester.i18n.php';
	
# don't change this path var, unless you renamed something
	define('US_SUGGESTER_CLASSES_INCLUDE_PATH',dirname(__FILE__) . '/'.'Suggesters');
	define('US_CHAR_FOR_SUBRESULTS_1','#');
	define('US_CHAR_FOR_SUBRESULTS_2','\'');
	
###################################	
# 	HOOK TO INITIATE BASICS ON THE EDIT PAGE	
###################################
	$wgHooks['AlternateEdit'][]  = "init_usBasic";
	$wgExtensionCredits['other'][] = array(
		'version'     => '1.0',
		'name'        => 'UniversalSuggester',
		'author'      => 'Thomas Klein',
		'email'       => 'thomas dot klein83 at gmail dot com',
		'url'         => 'http://www.mediawiki.org/wiki/Extension:UniversalSuggester',
		'description' => 'Allowing fast paced information insertion with an AJAX powered inline search bar'
	);
	
###################################	
# 	HOOK FOR AJAX CALLBACK FUNCTION
###################################	
	$wgAjaxExportList[] = 'initUS_RequestManager';
	
/*********************************************************
 * Ajax callback function to get results depending on the choosen suggester
 *
 * @param string $s_term
 * @param string $s_suggester
 * @param int $i_timestamp
 * @param int $i_limit
 * @param string $s_pageTitle
 * 
 * @return string
 */
	function initUS_RequestManager($s_term,$s_suggester,$i_timestamp,$i_limit,$s_pageTitle) {
		
		require_once("US_RequestManager_class.php");
		
		# set the default suggesters
		# excluded the suggester "Attribute" as the SMW extension might not be installed.
		# Suggesters MUST meet the following criterias: 
		# - be located in the dir 'Suggesters'
		# - file name is 'US_<NAME>_class.php'
		# - class name is 'US_<NAME>'
		# - class extends class 'US_Suggester'
			$a_suggesters = array("Category","Image","Media",
			  					  "Link","Template","Transclusion","User");		 	
			
		# Set the namespaces to search in. Check 
		# http://www.mediawiki.org/wiki/Manual:Namespace#Built-in_namespaces
		# for reference. Make sure to use the namespace CONSTANTS!
			$a_defaultNS = array(NS_MAIN, NS_TALK);
			
		# reset $i_limit to 2000 to get mostly all results
		# until i found a better solution
		################################################## 	
			$i_defaultDbRowsLimit = 2000;
	/**
	 * @local US_RequestManager
	 */
		$o_usRM = null;		
			
		try {
			
		# init the reauest manager	
			$o_usRM = new US_RequestManager($a_suggesters,$a_defaultNS,$i_defaultDbRowsLimit);		
			
		# return the results	
			return $o_usRM->getResults($s_term,$s_suggester,$i_timestamp,$i_limit,$s_pageTitle);
			
		} catch (Exception $e) {
			return "<p>".$e->getMessage()."</p>";
		}
			
	} // initUS_RequestManager
	
	
/*********************************************************
 * Hook for the MediaWiki when the user starts editing a page
 *
 * @return boolean
 */	
	function init_usBasic() {
		
		require_once("US_Basics_class.php");
		
		global $wgScriptPath, $wgOut;
	/**
 	* @local string $s_pathToExtension
 	*/ 
 		$s_pathToExtension = $wgScriptPath."/extensions/UniversalSuggester/";
 		
	/**
	 * @local object (US_Basics)
	 */
		$o_usBasics = null;		
			
		try {
			$o_usBasics = new US_Basics($s_pathToExtension);
			
		// uncomment this line, if you already have jquery (> version 1.3.1) linked elsewhere!!
			$o_usBasics->addIncludeFile("jquery-1.4.2.min.js");
			$o_usBasics->addIncludeFile("jquery.autocomplete.js");
			$o_usBasics->addIncludeFile("universalSuggester.css");
			$o_usBasics->addIncludeFile("universalSuggester.js");
			
		# init the javascript part of the extension
		# using this instead of jQuery's $(document).ready(function(){ 
		# to do it the 'MediaWiki way'
		# 
		# 'initUniversalSuggester' defined in universalSuggester.js
		##############################################################
			$o_usBasics->addJavascriptCodeLine(
				'addOnloadHook(
					//wait for the mediawiki buttons to load to avoid positioning problems
					function() {initUniversalSuggester();}
				);'
			);
			
			$o_usBasics->init();
		} catch (Exception $e) {
			die("<p>".$e->getMessage()."</p>");
		}
		
		return true;
	} // init_usBasic
?>