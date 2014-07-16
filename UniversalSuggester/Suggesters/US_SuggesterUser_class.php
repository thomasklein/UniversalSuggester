<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

class US_SuggesterUser extends US_Suggester {
	
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
		$s_table = 'user';
  	/**
	 * @local string
	 */	
		$s_tableField = 'user_name';
  	/**
	 * @local array
	 */
		
		$a_searchResults = parent::getDbResults($s_table, 
  										 		$s_tableField,
  										 		null,
  										  		null,
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
	
} // US_SuggesterUser

?>