<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

class US_Suggester {
	
/**
 * @protected Database - MediaWiki database object (http://svn.wikimedia.org/doc/classDatabase.html)
 */		
	protected $o_db;
		
/**
 * @protected int - maximum of entries which should be returned for each database query
 */	
	protected $i_dbRowsLimit;
	
/**
 * @public static array
 */	
	public static $a_nsToSearchIn = array();
	
/*********************************************************
 * The constructor - does nothing
 */
	public function __construct() {}
	
/*********************************************************	
 * Inits the instance with a reference to a MediaWiki database object 
 * and the maximum numbers of returned rows for each database query 
 * 
 * @param MediaWiki database object (http://svn.wikimedia.org/doc/classDatabase.html) $o_db
 * @param int $i_dbRowsLimit
 */
	public function init(&$o_db,$i_dbRowsLimit) {
		
		$this->o_db =& $o_db;
		$this->i_dbRowsLimit = $i_dbRowsLimit;
	} // init
	
	
/*********************************************************
 * Enter description here...
 *
 * @param string $s_charForExtraResults
 * @param string $s_wikiPageName
 * @param int $i_ns
 * @param string $s_extraNamepspaceIndicator
 * @param string $s_subterm
 *
 * @return string
 */
	public function getResultsForExtraParam($s_charForExtraResults, 
											$s_wikiPageName,
											$i_ns,
								 		 	$s_extraNamepspaceIndicator,
											$s_subterm,
											$b_resultsOnlyForCurrentPage) {
												
		return 	"<span class='ac_unselectable'>" . 
					wfMsg('error_param_not_supported_by_suggester', $s_charForExtraResults). 
				"</span>\n";
	} // getResultsForExtraParam
	
/*********************************************************
 * NOTE: This method must be overwritten in the inherting classes!
 * Returns a new line separated list of results using $s_mainTerm as a search phrase
 *
 * @param int $i_ns - an integer representing the MediaWiki namespace 
 *					  which limits the search results only to pages within this namespace
 * @param string $s_mainTerm - the term to search for
 *
 * @return string
 */
	public function getResults($i_ns ,$s_mainTerm) {
		
		return null;
	} // getResults
	
	
/*********************************************************
 * Returns an array of database search results
 * 
 * @param string $s_table - the database table to query
 * @param string $s_tableField - the database table field to query for $s_term
 * @param array $a_conditions - an array of conditions which apply to the query
 * @param array $a_selectFields - an array containing all fields that should get selected
 * @param string $s_term - a search term
 * 
 * @return array
 */
	public function getDbResults($s_table, $s_tableField, $a_conditions, $a_selectFields, $s_term) {
		
	/**
	 * @local array
	 */
		$a_results = array();
		
		if (!$s_table) {
			return 	'<span class="ac_unselectable">'.
						wfMsg('error_suggester_not_supported', $s_suggester) .
				   	'</span>';
		}
		
		$s_comparisionField = "LOWER( CONVERT(".$s_tableField." USING utf8 ))";

		$a_selectFields[] = $s_tableField;
	    $a_conditions[] = "(".$s_comparisionField." LIKE '".mysql_real_escape_string($s_term)."%') OR ".
	    				  "(".$s_comparisionField." LIKE '%".mysql_real_escape_string($s_term)."%')";
	    
	/**
	 * @local array
	 */
		$a_searchResults = array();
		
	/**
	 * @local resource 
     */ 
		$r_result = 
			$this->o_db->select($s_table,//db table
						  $a_selectFields,//fields to select
                          $a_conditions,
                          __METHOD__,//Calling function name
                          array('LIMIT' => $this->i_dbRowsLimit,
                          		'ORDER BY' => $s_comparisionField." LIKE ". 
                              	"'".mysql_real_escape_string($s_term)."%' DESC, ".$s_comparisionField." ASC"));
   	                  
	# An example query which might result from the above command:
	# 
	# SELECT smw_title FROM `smw_ids` WHERE smw_namespace = 202 
	# 	AND (LOWER( CONVERT(smw_title USING utf8)) LIKE '%Attribut%' OR 
	#		 LOWER( CONVERT(smw_title USING utf8)) LIKE 'Attribut%') 
	# ORDER BY smw_title LIKE 'Attribut%' DESC, smw_title ASC LIMIT 20
	##################################################################
	                      
        if ($this->o_db->numRows($r_result) > 0) {
			
			while($a_row = $this->o_db->fetchRow($r_result)){
				$a_results[] = $a_row;
			}
        }
        
        if ($r_result != null)
			$this->o_db->freeResult($r_result);
			
		return 	$a_results;
	} // getDbResults

	
/*********************************************************
 * Removes any appearance of wiki link markup in a given text
 * 
 * Handled cases are:
 * - semantic tags: e.g. [[tag::value]] returns 'value' or [[tag::value|altValue]] returns 'altValue'
 * - internal links: e.g. [[value]] returns 'value' or [[value|altValue]] returns 'altValue'
 *
 * @param string $s_text
 
 * @return string
 */	
	public function removeWikiLinks($s_text) {
		
	/*
	 * @local string
	 */
		$s_changedText = $s_text;
	/*
	 * @local array
	 */
		$a_matches = array();
		
	# match all semantic tags
	#########################
		if (preg_match_all('!\[\[([^:]*)\:\:([^\]]*)\]\]!i',$s_changedText,$a_matches)) {
			
		/*
		 * @local int|boolean
		 */	
			$m_posPipeSymbol = strpos($a_matches[2][0],"|");
			
			if ($m_posPipeSymbol !== FALSE) {
				
			/**
			 * @local string
			 */
				$s_altLinkText = substr($a_matches[2][0],$m_posPipeSymbol + 1);
				$s_changedText = str_replace($a_matches[0][0],$s_altLinkText,$s_changedText);	 
			}
			else {
				$s_changedText = str_replace($a_matches[0][0],$a_matches[2][0],$s_changedText);	
			}
		}
		
		$a_matches = array();
		
	# matches and replaces all internal links
	#########################################
		if (preg_match_all('!\[\[([^\]]*)\]\]!i',$s_changedText,$a_matches)) {
		/*
		 * @local int|boolean
		 */	
			$m_posPipeSymbol = strpos($a_matches[1][0],"|");
			
			if ($m_posPipeSymbol !== FALSE) {
				
			/**
			 * @local string
			 */
				$s_altLinkText = substr($a_matches[1][0],$m_posPipeSymbol + 1);
				$s_changedText = str_replace($a_matches[0][0],$s_altLinkText,$s_changedText);	 
			}
			else {
				$s_changedText = str_replace($a_matches[0][0],$a_matches[1][0],$s_changedText);	
			}
		}

		return $s_changedText;
	}//removeWikiLinks
	
	
/*******************************************************************
 * Check for $s_term in $s_text and mark it
 * If there was no match, return false
 *
 * @param string $s_text
 * @param string $s_term
 * 
 * @return string|boolean
 */
	public function checkAndMarkTerm($s_text,$s_term) {
	/**
	 * @local string
	 */
		$s_result = null;
										
		if(stripos($s_text,$s_term) !== FALSE) {
			
		/**
		 * @local string
		 */
			$s_originalTerm = substr($s_text,stripos($s_text, $s_term),strlen($s_term));
			$s_result = str_ireplace($s_term,"<strong>".$s_originalTerm."</strong>",$s_text)."\n";
		}
			
		return $s_result ? trim($s_result) : false;
	}//checkAndMarkTerm
	
}//US_Suggester

?>