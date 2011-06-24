<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

require_once(US_SUGGESTER_CLASSES_INCLUDE_PATH."/US_Suggester_class.php");

class US_RequestManager {
	
	private $a_suggesters;
	private $o_db;
	private $s_initErrors;
	
/*********************************************************
 * Constructor
 *	
 * @param array $a_suggesters
 * @param string $s_pathToExtension
 */
	public function __construct($a_suggesters,$a_defaultNS, $i_dbRowsLimit) {
		
		global $wgContLang;
		
		$this->o_db =& wfGetDB(DB_SLAVE);
		
		foreach ($a_suggesters as $s_suggester) {

		/**
		 * @local string 
		 */	
			$s_expectedFileName = US_SUGGESTER_CLASSES_INCLUDE_PATH. '/US_Suggester' . $s_suggester . "_class.php"; 
			
			if (file_exists($s_expectedFileName)) {
				
				require_once($s_expectedFileName);
				
				$s_suggesterClass = 'US_Suggester'.$s_suggester;
				$this->a_suggesters[$s_suggester] = new $s_suggesterClass;
				$this->a_suggesters[$s_suggester]->init($this->o_db,$i_dbRowsLimit);
			}
			else {
				throw new Exception(
					wfMsg( 	'error_suggester_file_does_not_exist', 
							__FILE__,
							__LINE__,
							$s_suggester,
							US_SUGGESTER_CLASSES_INCLUDE_PATH
					)
				);
			}
			
			
		}
		
		foreach ($a_defaultNS as $i_ns) {
			US_Suggester::$a_nsToSearchIn[$i_ns] = $wgContLang->getFormattedNsText($i_ns);
		}
		
	}//__construct
	
		
/*********************************************************
 * Ajax-Callback function to get database content
 *
 * @param string $s_term
 * @param string $s_suggester
 * @param int $i_timestamp
 * @param int $i_limit
 * @param string $s_pageTitle url param title (might have url encoded chars)
 * 
 * @return string
 */	
	public function getResults($s_term,$s_suggester,$i_timestamp,$i_limit,$s_pageTitle) {
		
		if (!isset($this->a_suggesters[$s_suggester])) {
		
		/**
		 * @local array
		 */
			$a_suggesters = array_keys($this->a_suggesters);
			
			throw new Exception(
				wfMsg( 	'error_suggester_does_not_exist', 
						__FILE__,
						__LINE__,
						$s_suggester,
						implode(", ",$a_suggesters)
				)
			);
		}
		
	# in case there were any encoded chars in the url
		$s_pageTitle = urldecode($s_pageTitle);
		
	# replace all blanks with underscores to match standard wiki page names 
		$s_pageTitle = mb_strtolower(str_replace(' ', '_', trim($s_pageTitle)));
		
	/**
	 * @local string
	 */
		$s_mainTerm = null;
		
	/**
	 * @local string|int
	 */	
		$s_subTerm  = -1;
		
	# check if the term should get divided into a mainterm and subterm
	# results of the subterm direclty derive from the mainterm
	##################################################################
		if (strpos($s_term,US_CHAR_FOR_SUBRESULTS_1) !== FALSE) {
			
			list($s_mainTerm,$s_subTerm) = explode(US_CHAR_FOR_SUBRESULTS_1,$s_term);
			$s_charForExtraResults = US_CHAR_FOR_SUBRESULTS_1;
		}
		elseif(strpos($s_term,US_CHAR_FOR_SUBRESULTS_2) !== FALSE) {
			
			list($s_mainTerm,$s_subTerm) = explode(US_CHAR_FOR_SUBRESULTS_2,$s_term);
			$s_charForExtraResults = US_CHAR_FOR_SUBRESULTS_2;
		}
		else {
			
			$s_mainTerm = $s_term;
		}
		
		$s_mainTerm = str_replace(' ', '_', $s_mainTerm);
		
	/**
	 * @local string 
	 */	
		$s_pageNameInTerm = null;
		
	/**
	 * @local boolean
	 */
		$b_resultsOnlyForCurrentPage = false;
	/**
	 * @local string
	 */
		$s_extraNamepspaceIndicator = null;

	/**
	 * @local int default is main namespace
	 */
		$i_ns = NS_MAIN;
		
		if ($s_mainTerm) {
		
		# extract namespace, page name (without namespace)
		# from the main term
		# e.g.: from "Help:Wiki Syntax" we can extract  
		# 		$i_ns => some number
		# 		$s_pageNameInTerm => 'Wiki Syntax' 
		# 	    $s_extraNamepspaceIndicator => 'Help:'
		#################################################
			$this->extractPageNameDetails($s_mainTerm,
								 		  $i_ns,
								 		  $s_pageNameInTerm,
								 		  $s_extraNamepspaceIndicator);
		}
		else {
			$b_resultsOnlyForCurrentPage = true;
			$this->extractPageNameDetails($s_pageTitle,
								 		  $i_ns,
								 		  $s_pageNameInTerm,
								 		  $s_extraNamepspaceIndicator);
		}
		
	/**
	 * @local string
	 */
		$s_results = null;
	/**
	 * @local array containing conditons
	 */
		$a_conditions = array();
	/**
	 * @local array
	 */
		$a_selectFields = array();
	/**
	 * @local array
	 */
		$a_searchResults = array();
		
	/**
	 * @local database result pointer
	 */
		$r_result = null;
		
	    try {
			
	    	if ($s_subTerm != -1) {
	    		
	    		$s_results = $this->a_suggesters[$s_suggester]->getResultsForExtraParam(
	    			$s_charForExtraResults, 
					$s_pageNameInTerm,
					$i_ns,
					$s_extraNamepspaceIndicator,
					$s_subTerm,
					$b_resultsOnlyForCurrentPage);
	    	}
	    	else {
	    		
	    		$s_results = $this->a_suggesters[$s_suggester]->getResults(
	    			$i_ns,
					$s_mainTerm);
	    	}
			
		}
		catch (Exception $e) {
			throw $e;
		}
		
		if (!$s_results)
			$s_results = "<span class='ac_unselectable'>".wfMsg('info_no_results')."</span>\n";			
			
		return $s_results;
	}//getResults
		
	
/*********************************************************
 * Checks if the page name in the given term $s_term contains a valid namespace
 * if not, the main namespace is returned
 *
 * @param string $s_term
 * @param reference $i_ns
 * @param reference $s_pageNameInTerm
 * @param reference $s_extraNamepspaceIndicator
 * @return void
 */
	private function extractPageNameDetails($s_term, &$i_ns, &$s_pageNameInTerm, &$s_extraNamepspaceIndicator) {
		
		if (strpos($s_term,":")) {
		/*
		 * @local string 
		 */	
			$s_nsName = null;
			
			list($s_nsName,$s_pageNameInTerm) = explode(":",$s_term);
			
			if (in_array(ucfirst($s_nsName),US_Suggester::$a_nsToSearchIn)) {
				
				$s_nsName = ucfirst($s_nsName);
				
				foreach (US_Suggester::$a_nsToSearchIn as $i_possibleNs => $s_possibleNsName) {
					
					if ($s_nsName == $s_possibleNsName) {
						$i_ns = $i_possibleNs;
						$s_extraNamepspaceIndicator = $s_nsName.":";
						break;
					}
				}
			}
			else {
				$s_pageNameInTerm = $s_term;
			}
		}
		
		else { 
			$s_pageNameInTerm = $s_term;
		}
		
	}//extractPageNameDetails
	
} // US_RequestManager
?>