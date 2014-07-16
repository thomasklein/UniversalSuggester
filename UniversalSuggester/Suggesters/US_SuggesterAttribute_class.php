<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

class US_SuggesterAttribute extends US_Suggester {
	
	
/*********************************************************
 * Returns the results 
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
		$s_table = 'smw_ids';
  	/**
	 * @local string
	 */	
		$s_tableField = 'smw_title';
	/*
	 * @local array
	 */	
		$a_conditions[] = 'smw_namespace = ' . SMW_NS_PROPERTY;
  	/**
	 * @local array
	 */	
		$a_selectFields = array('smw_id');
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
 *
 * @return string
 */
	public function getResultsForExtraParam($s_charForExtraResults, 
											$s_wikiPageName,
											$i_ns,
								 		 	$s_extraNamepspaceIndicator,
											$s_subTerm,
											$b_resultsOnlyForCurrentPage) {
	/*
	 * @local string
	 */
		$s_results = null;
												
		switch($s_charForExtraResults) {
		
			case US_CHAR_FOR_SUBRESULTS_1:
			/**
			 * @local string
			 */
				$s_dbSmwShortTexts = $this->o_db->tableName('smw_atts2');
			/**
			 * @local string
			 */	
				$s_dbSmwIds = $this->o_db->tableName('smw_ids');
			
				$s_sql = "SELECT DISTINCT atts.value_xsd,smw_title ".
						 "FROM $s_dbSmwShortTexts as atts ".
						 "JOIN $s_dbSmwIds ON smw_id = atts.p_id ".
						 "WHERE LOWER(CONVERT(smw_title USING utf8))='".$s_wikiPageName."' AND atts.value_xsd != '' ". 
						 "ORDER BY atts.value_xsd DESC ".
						 "LIMIT " . $this->i_dbRowsLimit;

				$r_resultAtts = $this->o_db->query( $s_sql, __METHOD__ );
				
				if ($this->o_db->numRows($r_resultAtts) > 0) {
					
					while ( $a_rowAtt = $this->o_db->fetchRow( $r_resultAtts ) ) {
						$s_results .= "<span class='us_invisible'>".
									  $a_rowAtt['smw_title'].
									  "#</span>".trim($a_rowAtt['value_xsd'])."\n";
					}
					
					$s_results = "<span class='ac_unselectable us_resultsHeader'>".
								  "<em>" . wfMsg('info_assigned_attribute_values') . "</em></span>\n".$s_results;
				}
				else 
					$s_results = "<span class='ac_unselectable'>".
									wfMsg('info_no_attribute_values_assigned') ."</span>\n";
					
					
					
			break;
			
			case US_CHAR_FOR_SUBRESULTS_2:
				
				$s_results = "<span class='ac_unselectable'>".
					wfMsg('info_option_not_supported', US_CHAR_FOR_SUBRESULTS_2). "</span>\n";
				
				break;
		}
	
		return $s_results;
	}//getResultsForExtraParam
	
	
} // US_SuggesterAttribute

?>