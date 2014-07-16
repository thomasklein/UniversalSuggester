<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

class US_SuggesterLink extends US_Suggester {

	
/*********************************************************
 * Enter description here...
 *
 * @param int $i_ns
 * @param string $s_mainTerm
 *
 * @return string
 */
	public function getResults($i_ns, $s_mainTerm) {
			
	/**
	 * @local string 
	 */
		$s_results = null;
	/**
	 * @local string
	 */
		$s_table = 'page';
  	/**
	 * @local string
	 */	
		$s_tableField = 'page_title';
  	/**
	 * @local array
	 */	
		$a_selectFields = array('page_id','page_namespace');
 	/**
	* @local array
	*/
		$a_searchResults = array();
  		
  		if ($i_ns==NS_MAIN)
  			$a_conditions[] = "page_namespace IN (".implode(',',array_keys(parent::$a_nsToSearchIn)).")";
  		else
  			$a_conditions[] = "page_namespace =".$i_ns;	
  		
  		$a_conditions[] = 'page_is_redirect = 0';
  		
  		$a_searchResults = parent::getDbResults($s_table, 
  										 		$s_tableField,
  										 		$a_conditions,
  										  		$a_selectFields,
  										  		$s_mainTerm);
  										  		
		foreach($a_searchResults as $a_searchResult){

		/**
		 * @local string
		 */
			$s_pageNamespace = null;
			
		# convert the underscores in page names back to spaces
		# for a better optic
		######################################################	
			$s_result = str_replace('_', ' ',$a_searchResult[$s_tableField]);
			
		# if it is searched for page links, also append the namespace
		#############################################################	
			if ($a_searchResult['page_namespace'] != NS_MAIN) {
				
		    	$s_pageNamespace = '<span class="us_nsMarker">'.
		    						parent::$a_nsToSearchIn[$a_searchResult['page_namespace']].
		    						':</span>';
			}
			
			$s_results .= $s_pageNamespace.$s_result."\n";
		}								  		
		
		return $s_results;
	}//getResults
	
	
/*********************************************************
 * Returns the sections of the wiki page $s_pageNameInTerm in the namespace $i_ns
 *  
 * @param string $s_charForExtraResults
 * @param string $s_wikiPageName
 * @param int $i_ns
 * @param string $s_extraNamepspaceIndicator
 * @param string $s_subterm
 * @param boolean $b_resultsOnlyForCurrentPage
 *
 * @return string
 */
	public function getResultsForExtraParam($s_charForExtraResults, 
											$s_wikiPageName,
											$i_ns,
								 		 	$s_extraNamepspaceIndicator,
											$s_subTerm,
											$b_resultsOnlyForCurrentPage) {
												
		switch($s_charForExtraResults) {
		
			case US_CHAR_FOR_SUBRESULTS_1:
			/**
			 * @local resource pointer
			 */
				$r_result = 
					$this->o_db->select('page',//db table
								  array('page_id'),
		                          array('page_namespace='.$i_ns,
		                          	    'page_is_redirect=0',
		                        	    'LOWER(CONVERT(page_title USING utf8))='.$this->o_db->addQuotes($s_wikiPageName)),
		                              	__METHOD__);
			/**
			 * @local array
			 */
				$a_result = $this->o_db->fetchRow($r_result);
				
				if (empty($a_result)) {
					
					$s_results =	"<span class='ac_unselectable'>" . 
										wfMsg('error_page_does_not_exist', ucfirst($s_wikiPageName)) .
									"</span>\n";
					return $s_results;
				}
				
			/**
			 * @local object
			 */	
				$o_mwRevision = Revision::loadFromPageId( $this->o_db, $a_result['page_id']);
				$o_mwRevision->getText();
				
			/**
			 * @local array which contains all headlines of a page, if matched
			 */
				$a_matchedHeadlines = array();
				
				preg_match_all('!(^={1,6})([^=\n]*)={1,6}!im', 
							   $o_mwRevision->mText,
							   $a_matchedHeadlines);
				
				if (!empty($a_matchedHeadlines[2])) {
						
				/**
				 * @local string
				 */	
					$s_matchedHeadline = null;
					
					for ($i = 0; $i < count($a_matchedHeadlines[2]); $i++) {
					
						$s_matchedHeadline = parent::removeWikiLinks($a_matchedHeadlines[2][$i]);
						
						if ($s_subTerm != '') {
							$s_matchedHeadline = parent::checkAndMarkTerm($s_matchedHeadline,$s_subTerm);
						}
						
					/**
					 * @local string
					 */	
						$s_leftIntend = "<span class='us_getsRemoved'>";
						
						for ($j = 0; $j < (strlen($a_matchedHeadlines[1][$i]) - 2); $j++)	
							$s_leftIntend .= "&hellip;"; 

						$s_leftIntend .= "</span>";	
							
						if ($s_matchedHeadline) {
							
							$s_results .= "<span class='us_invisible'>";
							
							if (!$b_resultsOnlyForCurrentPage) {
								$s_results .= $s_extraNamepspaceIndicator.
											  $o_mwRevision->mTitle->mTextform;
							}
							
							$s_results .= 
								"#</span>".$s_leftIntend.trim($s_matchedHeadline)."\n";
						}	
					}
					
					$s_results =	"<span class='ac_unselectable us_resultsHeader'>".
								 	"<em>".wfMsg('info_page_sections')."</em></span>\n".$s_results;
				}
				else
					$s_results = "<span class='ac_unselectable'>" . wfMsg('info_no_page_sections'). " </span>\n";
					
			break;
			
			case US_CHAR_FOR_SUBRESULTS_2:
				
				$s_results = "<span class='ac_unselectable'>".
					wfMsg('info_option_not_supported', US_CHAR_FOR_SUBRESULTS_2). "</span>\n";
				
				break;
		}
	
		return $s_results;
	}//getResultsForExtraParam
	
} // US_SuggesterLink

?>