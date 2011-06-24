<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

class US_SuggesterTemplate extends US_Suggester {
	
/*********************************************************
 * Return results matching $s_mainTerm
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
		$a_conditions[] = 'page_namespace = '.NS_TEMPLATE;
		
	/**
	 * @local array
	 */
		$a_selectFields = array('page_id','page_namespace');
 	/**
	* @local array
	*/
		$a_searchResults = array();
  		
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
			$s_results .= $s_result."\n";
		}								  		
		
		return $s_results;
	}//getResults
	
	
/*********************************************************
 * Returns all possible params of the template $s_wikiPageName
 *  
 * @param string $s_charForExtraResults
 * @param string $s_wikiPageName
 * @param int $i_ns - should be the Template namespace which is most probably 10
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

	/**	
	 * @var int
	 */
		$i_ns = NS_TEMPLATE;

	/**	
	 * @var string
	 */
		$s_results = "";
		
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
					
					$s_results =	"<span class='ac_unselectable'>".
										wfMsg('error_template_does_not_exist', ucfirst($s_wikiPageName)) .
								 	"</span>\n";
					return $s_results;
				}
				
			/**
			 * @local object
			 */	
				$o_mwRevision = Revision::loadFromPageId( $this->o_db, $a_result['page_id']);
				$o_mwRevision->getText();
				
			/**
			 * @local array
			 */
				$a_matches = array();
				
			# fetch template params	e.g. {{{param}}} via regular expression
				preg_match_all('!\{{3}([^{}]*)\}{3}!im', 
							   $o_mwRevision->mText,
							   $a_matches);
				
				if (!empty($a_matches[1])) {

					$s_results .= "<span class='us_invisible'>" . $o_mwRevision->mTitle->mTextform . "|</span>";
					
				/**
			 	* @local array
			 	*/	
					$a_templateParams = array();
					
				# avoid multiple occurrences of the same param name	
					foreach ($a_matches[1] as $s_match) {
						
						if (!in_array($s_match, $a_templateParams))
							$a_templateParams[] = $s_match;
					}
					
					$s_results .= implode("|", $a_templateParams);
					
					$s_results = "<span class='ac_unselectable us_resultsHeader'>".
								  "<em>" . wfMsg('info_template_params') . "</em></span>\n".$s_results;
				}
				else
					$s_results = "<span class='ac_unselectable'>" . wfMsg('info_no_template_params')  . "</span>\n";
					
			break;
			
			case US_CHAR_FOR_SUBRESULTS_2:
				
				$s_results = "<span class='ac_unselectable'>".
					wfMsg('info_option_not_supported', US_CHAR_FOR_SUBRESULTS_2). "</span>\n";
				
				break;
		}
	
		return $s_results;
	}//getResultsForExtraParam
	
} // US_SuggesterTemplate

?>