<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */

class US_SuggesterCategory extends US_Suggester {

	
/*********************************************************
 * Enter description here...
 *
 * @param int $i_ns
 * @param string $s_mainTerm
 *
 * @return string
 */
	public function getResults($i_ns ,$s_mainTerm) {
		
	/**
	 * @local string
	 */	
		$s_table = 'category';
	/**
	 * @local string
	 */
		$s_tableField = 'cat_title';
	
 	/**
	* @local array
	*/
		$a_searchResults = parent::getDbResults($s_table, 
  										 		$s_tableField,
  										 		$a_conditions,
  										  		$a_selectFields,
  										  		$s_mainTerm);
  										  		
		foreach($a_searchResults as $a_searchResult){

		# convert the underscores in page names back to spaces
		# for a better optic
		######################################################	
			$s_results .= str_replace('_', ' ',$a_searchResult[$s_tableField])."\n";
		}								  		
		
		return $s_results;
	}//getResults
	
	
	public function getResultsForExtraParam($s_charForExtraResults, 
											$s_wikiPageName,
											$i_ns,
								 		 	$s_extraNamepspaceIndicator,
											$s_subterm,
											$b_resultsOnlyForCurrentPage) {
												
		switch($s_charForExtraResults) {
		
			case US_CHAR_FOR_SUBRESULTS_1:										
			/**
			 * @local boolean
			 */
				$b_hadUnfilteredSubResults = false; 
				
			/**
			 * @local string
			 */
				$s_dbPage = $this->o_db->tableName('page');
			/**
			 * @local string 
			 */	
				$s_dbCategoryLinks = $this->o_db->tableName('categorylinks');
				
			# if only the applied categories of the current page should get returned
			########################################################################	
				if ($b_resultsOnlyForCurrentPage) {
					
				/**
				 * @local string
				 */
					$s_pageCategories = null;
					
					$s_sql = "SELECT cl_to ".
							 "FROM $s_dbCategoryLinks ".
							 "WHERE cl_from = ". 
							 	"(SELECT page_id FROM $s_dbPage ".
								"WHERE LOWER(CONVERT(page_title USING utf8)) = ".$this->o_db->addQuotes($s_wikiPageName)." ".
								"AND page_namespace = ". $i_ns . ") ".
							 "ORDER BY cl_sortkey ".
							 "LIMIT " . $this->i_dbRowsLimit;
							 
					$r_resultCat = $this->o_db->query( $s_sql, __METHOD__ );
					
					if ($this->o_db->numRows($r_resultCat) > 0) {
						
						$b_hadUnfilteredSubResults = true;
						
					/*
					 * @local string|boolean
					 */	
						$m_result = null;
						
						while ( $a_rowCat = $this->o_db->fetchRow( $r_resultCat ) ) {
							
							$m_result = $a_rowCat['cl_to'];
							
							if ($s_subTerm != '') {
								$m_result = self::checkAndMarkTerm($m_result,$s_subTerm);
							}
							
							if ($m_result) {
								$s_pageCategories .= $m_result."\n";
							}
						}
						
					}
					$this->o_db->freeResult($r_resultCat);
					
					if ($s_pageCategories) {
						$s_results = "<span class='ac_unselectable us_resultsHeader'>".
									  "<em>" . wfMsg('info_current_page_categories') . "</em></span>\n".$s_pageCategories;
					}
					
					else {
						if (!$b_hadUnfilteredSubResults) {
							$s_results = 
								"<span class='ac_unselectable'>".
								wfMsg('info_no_categories_assigned') . "</span>\n";
						}
						else {
							$s_results = 
								"<span class='ac_unselectable'>".
								wfMsg('info_no_categories_with_subterm', $s_subTerm) . "</span>\n";
						}
					}
					
				}
				else {
				/**
				 * @local string
				 */
					$s_subCategories = null;
				/**
				 * @local string
				 */
					$s_parentCategories = null;
					
				# get all subcategories
				#######################	
					$s_sql = "SELECT cat.page_title ".
							 "FROM $s_dbPage as cat ".
							 "JOIN $s_dbCategoryLinks ON cl_from = cat.page_id ".
							 "WHERE LOWER(CONVERT(cl_to USING utf8)) = " . $this->o_db->addQuotes($s_wikiPageName) ." ". 
							 " AND cat.page_namespace = ". NS_CATEGORY . " ".
							 "ORDER BY cl_sortkey ".
							 "LIMIT " . $this->i_dbRowsLimit;
							 
					$r_resultCat = $this->o_db->query( $s_sql, __METHOD__ );
					
					if ($this->o_db->numRows($r_resultCat) > 0) {
						
						$b_hadUnfilteredSubResults = true;
					/*
					 * @local string|boolean
					 */	
						$m_result = null;
						
						while ( $a_rowCat = $this->o_db->fetchRow( $r_resultCat ) ) {
							
							$m_result = $a_rowCat['page_title'];
							
							if ($s_subTerm)
								$m_result = self::checkAndMarkTerm($m_result,$s_subTerm);
							
							if ($m_result)
								$s_subCategories .= $m_result."\n";
						}
					}
					$this->o_db->freeResult($r_resultCat);
					
				# get all parent categories
				###########################	
					$s_sql = "SELECT cl_to ".
							 "FROM $s_dbCategoryLinks ".
							 "WHERE cl_from = ". 
							 	"(SELECT page_id FROM $s_dbPage ".
								"WHERE LOWER(CONVERT(page_title USING utf8)) = ".$this->o_db->addQuotes($s_wikiPageName).
								" AND page_namespace = ". NS_CATEGORY . ") ".
							 "ORDER BY cl_sortkey ".
							 "LIMIT " . $this->i_dbRowsLimit;
					
					$r_resultCat = $this->o_db->query( $s_sql, __METHOD__ );
					
					if ($this->o_db->numRows($r_resultCat) > 0) {
						
						$b_hadUnfilteredSubResults = true;
						
					/*
					 * @local string|boolean
					 */	
						$m_result = null;
						
						while ( $a_rowCat = $this->o_db->fetchRow( $r_resultCat ) ) {
							
							$m_result = $a_rowCat['cl_to'];
							
							if ($s_subTerm)
								$m_result = self::checkAndMarkTerm($m_result,$s_subTerm);
							
							if ($m_result)
								$s_parentCategories .= $m_result."\n";
						}
						
					}
					$this->o_db->freeResult($r_resultCat);	
		
				# prepare results for the output
				################################	
					if ($s_subCategories) {
						$s_results .= "<span class='ac_unselectable us_resultsHeader'>".
									  "<em>" . wfMsg('info_subcategories'). "</em></span>\n".$s_subCategories;
					}
					
					if ($s_parentCategories) {
						$s_results .= "<span class='ac_unselectable us_resultsHeader'>".
									  "<em>".wfMsg('info_supercategories')."</em></span>\n".$s_parentCategories;
					}
					
					if (!$s_subCategories && !$s_parentCategories) {
						
						if (!$b_hadUnfilteredSubResults) {
							$s_results = "<span class='ac_unselectable'>".
										 wfMsg('info_no_super_nor_subcategories') . "</span>\n";
						}
						else {
							$s_results = 
								"<span class='ac_unselectable'>".
								wfMsg('info_no_super_nor_subcategories_term', $s_subTerm) . "</span>\n";
						}
					}
				}
			break;
			
			case US_CHAR_FOR_SUBRESULTS_2:
				
				$s_results = "<span class='ac_unselectable'>".
					wfMsg('info_option_not_supported', US_CHAR_FOR_SUBRESULTS_2). "</span>\n";
				
				break;
		}
		return $s_results;
	}//getResultsForExtraParam
		
} // US_SuggesterCategory

?>