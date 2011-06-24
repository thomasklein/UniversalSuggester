<?php
/*
 * Part of the Mediawiki extension "UniversalSuggester" 
 * Please see the file UniversalSuggester/UniversalSuggester.php for license and copyright details.
 * @author Thomas Klein <thomas.klein83@gmail.com>
 */	

class US_Basics {

/**
 * @private array $a_filesToInclude - contains css file paths to include for this extension 
 */
	private $a_filesToInclude;
	private $a_supportedIncludeFileExtensions;
	private $a_javaScriptCodeLines;
	private $s_suggesterBox;
	private $s_pathToExtension;
	
/*********************************************************
 * Constructor
 *	
 * @param array $a_suggesters
 * @param string $s_pathToExtension
 */
	public function __construct($s_pathToExtension) {
		
		$this->s_pathToExtension = $s_pathToExtension;
		$this->a_supportedIncludeFileExtensions = array("css","js");
		$this->a_filesToInclude = array();
		$this->a_javaScriptCodeLines =  array();
	}//__construct
	
		
/*********************************************************
 * Add a file path for a file of the type $s_fileType ('css', 'javascript')
 * which gets inlcuded later on
 *
 * @param string $s_cssFileName
 */
	public function addIncludeFile($s_fileName) {
		
		$s_fileType = $this->getFileExtension($s_fileName); 
		
		if (!in_array($s_fileType,$this->a_supportedIncludeFileExtensions)) {
			throw new Exception(
					wfMsg( 	'error_unsupported_file_extension', 
							__CLASS__,
							__LINE__,
							$s_fileType,
							implode(",", $this->a_supportedIncludeFileExtensions)
					)
			);
		}
		
		$this->a_filesToInclude[$s_fileType][] = $s_fileName;
		
	}//addIncludeFile
	

/*********************************************************
 * Add a javascript code line which get included later on
 *
 * @param string $s_javaScriptCodeLine
 */	
	public function addJavascriptCodeLine($s_javaScriptCodeLine) {
		
		$this->a_javaScriptCodeLines[] = $s_javaScriptCodeLine;
	}//addJavascriptCodeLine
	
	
/*********************************************************
 * Init the extension by using the mediawiki gloabl $wgOut and
 * - register all exteneral files at the wiki
 * - include javascript code which get executed immediately 
 *
 * @param $wgOut
 */
	public function init() {
		
		global $wgHooks, $wgExtraNamespaces, $wgContLang, $smwgContLang, $wgOut;
		
		$s_suggesterHelp = wfMsg('info_suggester_help');
		
		$this->s_suggesterBox = "<div id='us_box'>\n" .
								"<label class='suggesterHelp' for='us_button'><span>".$s_suggesterHelp."</span></label>" .
						  		"	<img src='".$this->s_pathToExtension."us_button.png' id='us_button' " .
						  		"alt='UniversalSuggester(Click here)' />\n" .
								"	<input id='us_userInput' type='text' />" . 
						  		"</div><!-- us_box-->\n";
	
		foreach ($this->a_filesToInclude as $s_fileType => $a_filesToInclude) {
			
			foreach ($a_filesToInclude as $s_file) {
				
				switch ($s_fileType) {
					
					case 'css':
						$wgOut->addLink(
							array('rel' => 'stylesheet',
								  'type' => 'text/css',
								  'href' => $this->s_pathToExtension.$s_file,
							)
						);
						break;
					
					case 'js':
						$wgOut->addScript(
							"<script type=\"text/javascript\" src=\"".$this->s_pathToExtension.$s_file."\">"."</script>\n");
						break;	
				}
			}
		}
		
		$wgOut->addScript("<script type=\"text/javascript\">".implode("",$this->a_javaScriptCodeLines)."</script>\n");
		$wgOut->addHTML($this->s_suggesterBox);
	}//init

	
/*******************************************************************
* returns the file extension of a given file path string
*
* @param string $s_file
*
* @return string $ext
*/
	private function getFileExtension($s_file) {

        $i = strrpos($s_file,".");
        if (!$i) { return null; }

        $l = strlen($s_file) - $i;
        $ext = substr($s_file,$i+1,$l);

        return $ext;

 	}//getFileExtension
	
} // US_Basics
?>