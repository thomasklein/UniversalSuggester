<?php
$messages = array();
$messages['en'] = array(
	
	'info_assigned_attribute_values' => "Already assigned attribute values",

	'info_current_page_categories' => "Current page categories",

	'info_no_attribute_values_assigned' => "No attribute values assigned...",

	'info_no_categories_assigned' => "This page has no assigned categories",

	'info_no_categories_with_subterm' => "This page has no categories having <em>$1</em> in the name",

	'info_no_page_sections' => 'No page sections...',

	'info_no_results' => 'No results...',

	'info_no_super_nor_subcategories' => "Has no super or sub categories...",

	'info_no_super_nor_subcategories_term' => "Has no super- or subcategories containing the term <em>$1</em>",

	'info_no_template_params' => 'No params...',

	'info_option_not_supported' => 'Option "$1" not supported...',	

	'info_page_sections' => 'Page sections',
	
	'info_subcategories' => "Subcategories",

	'info_supercategories' => "Super categories",

	'info_suggester_help' => 
		'<strong>UniversalSuggester</strong><br />'.
			'To activate press <em>ESC</em> or click on this icon. <br />' .
			'To switch between suggesters, input a colon (:) followed by <ul>' .
			'<li>a (semantic attributes)</li>' .
			'<li>c (categories)</li>' .
			'<li>i (images)</li>' .
			'<li>f (files)</li>' .
			'<li>l (links)</li>' .
			'<li>r (transclusions)</li>' .
			'<li>t (templates)</li>' .
			'<li>u (users)</li>'.
			'</ul>' .
			'As you enter a search term, a list of suggestions will show up. ' .
			'Use the arrow keys to navigate in the list. ' .
			'Press <em>Enter</em> to directly insert the selected result ' .
			'on the last cursor position in the page edit field ' .
			'or press <em>Tab</em> to complete the search term in the search box.<br />' .
			'<em>(C) 2011 Thomas Klein - thomas.klein83@gmail.com</em>',

	'info_template_params' => 'Template params',	

	'error_page_does_not_exist' => 	"Page <em>$1</em> does not exist",

	'error_template_does_not_exist' => 	"Template <em>$1</em> does not exist",

    'error_suggester_file_does_not_exist' => 
    	"<span class='ac_unselectable'><h1>Error in extension <em>UniversalSuggester!</em></h1>" . 
		"<em>$1</em> on line $2.<br /><br />" .
		"The overgiven suggester <strong><em>$3</em></strong> does not belong to an existing class file. " .
		"The class file should be named <em>US_Suggester$3_class.php</em> and ".
		"shoud be located in the directory <em>$4</em>.</span>",

	'error_suggester_does_not_exist' =>
		"<span class='ac_unselectable'><h1>Error in extension <em>UniversalSuggester!</em></h1>" . 
		"<em>$1</em> on line $2.<br /><br />" .
		"The overgiven suggester <strong><em>$3</em></strong> does not belong to an existing class file. " .
		"Recheck the values of the array 'ga_suggesterSwitchValues' in <em>universalSuggester.js</em>.<br />".
		"An entry with one of the following names must be available: $4</span>",

	'error_suggester_not_supported' => "Suggester <em>$1</em> not supported.",

	'error_param_not_supported_by_suggester' => "Param '$1' is not supported by this suggester.",

	'error_unsupported_file_extension' =>
		"<strong>Error $1-$2 -</strong> " . "File extension \"$3\ not supported! " . "Please use one of the following $4"	
);

$messages['de'] = array(

	'info_assigned_attribute_values' => "Bereits vergebene Attributwerte",

	'info_current_page_categories' => "Aktuelle Kategorien dieser Seite",
    
	'info_no_attribute_values_assigned' => "Bisher keine Attributwerte vergeben...",

	'info_no_categories_assigned' => "Dieser Seite wurden noch keine Kategorien zugewiesen",

	'info_no_categories_with_subterm' => "Diese Seite hat keine Kategorien mit <em>$1</em> im Namen",

	'info_no_page_sections' => 'Keine Sektionen...',	

	'info_no_results' => 'Keine Ergebnisse...',

	'info_no_super_nor_subcategories' => "Hat weder Ober- noch Unterkategorien...",

	'info_no_super_nor_subcategories_term' => "Hat keine Ober- noch Unterkategorien mit <em>$1</em> im Namen",

	'info_no_template_params' => 'Keine Paramater...',

	'info_option_not_supported' => 'Option "$1" nicht unterstützt...',

	'info_page_sections' => 'Sektionen der Seite',

	'info_subcategories' => "Unterkategorien",

	'info_supercategories' => "Oberkategorien",

	'info_suggester_help' => 
		'<strong>UniversalSuggester</strong><br />'.
			'Zum Aktivieren <em>ESC</em> drücken und auf dieses Icon klicken. <br />' .
			'Um zwischen den Suggestern zu wechseln einfach ein Doppelpunkt (:) eingeben, gefolgt von <ul>' .
			'<li>a (semantic attributes)</li>' .
			'<li>c (categories)</li>' .
			'<li>i (images)</li>' .
			'<li>f (files)</li>' .
			'<li>l (links)</li>' .
			'<li>r (transclusions)</li>' .
			'<li>t (templates)</li>' .
			'<li>u (users)</li>'.
			'</ul>' .
			'Beim Eingeben des Suchbegriffs wird eine Auswahlliste an Ergebnissen angezeigt.' .
			'Benutze die Pfeiltasten oder die Maus um in der Liste zu navigieren. ' .
			'Drücke <em>Enter</em> um den selektierten Eintrag direkt in das Feld für den Seitentext' .
			'einzufügen oder drücke <em>Tab</em> um den Suchbegriff mit der Auswahl zu kompletieren.<br />' .
			'<em>(C) 2011 Thomas Klein - thomas.klein83@gmail.com</em>',

	'info_template_params' => 'Parameter des Templates',

	'error_page_does_not_exist' => 	"Seite <em>$1</em> existiert nicht",

	'error_template_does_not_exist' => 	"Template <em>$1</em> existiert nicht",

	'error_suggester_file_does_not_exist' => 
    	"<span class='ac_unselectable'><h1>Error in extension <em>UniversalSuggester!</em></h1>" . 
		"<em>$1</em> on line $2.<br /><br />" .
		"The overgiven suggester <strong><em>$3</em></strong> does not belong to an existing class file. " .
		"The class file should be named <em>US_$3_class.php</em> and ".
		"shoud be located in the directory <em>$4</em>.</span>",

	'error_suggester_does_not_exist' =>
		"<span class='ac_unselectable'><h1>Error in extension <em>UniversalSuggester!</em></h1>" . 
		"<em>$1</em> on line $2.<br /><br />" .
		"The overgiven suggester <strong><em>$3</em></strong> does not belong to an existing class file. " .
		"Recheck the values of the array 'ga_suggesterSwitchValues' in <em>universalSuggester.js</em>.<br />".
		"An entry with one of the following names must be available: $4</span>",

	'error_suggester_not_supported' => "Suggester <em>$1</em> wird nicht unterstützt...",

	'error_param_not_supported_by_suggester' => "Parameter '$1' wird von diesem Suggester nicht unterstützt.",

	'error_unsupported_file_extension' => 
		"<strong>Error $1-$2 -</strong> " . "File extension \"$3\ not supported! " . "Please use one of the following $4"
);

