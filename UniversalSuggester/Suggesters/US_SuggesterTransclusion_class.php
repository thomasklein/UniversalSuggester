<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

class US_SuggesterTransclusion extends US_Suggester {
	
	
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
} // US_SuggesterTransclusion

?>