/*
 * Init necessary javascript stuff for the mediawiki extension 'UniversalSuggester'
 * requires jQuery (http://jquery.com)
 * 
 * Copyright (c) 2011 Thomas Klein - thomas.klein83@gmail.com
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 * @TODO:Funktionalität zur Darstellung der Description-Box aus jquery.autocomplete holen
 * Grund: ich muss mich um das Cache-Problem nicht kümmern, und keine wahsinnigen CSS-Tricks ausführen
 */

// set default values
// indicating the global scope with the letter g at the beginning of each var name
	var gs_pathToExtension 	     = 'extensions/UniversalSuggester/';
	var gs_suggesterSwitchTrigger = ":";
	var ga_suggesterSwitchValues = new Array();
	var gi_currentSuggester = 1;//set the current suggester by default to 'link'
								//see other values in ga_suggesterSwitchValues
	var gs_nameMwTextarea = 'wpTextbox1';
	var gs_selectedText = '';//selected text in the edit textarea
	var gi_posCaret = 0;//position of the cursor in the edit textarea
	
// to get other keycodes(!) you can use tools in the web like
// http://www.java2s.com/Code/JavaScriptDemo/DisplayingcharCodeandkeyCodePropertyValues.htm	
	var gKEY = {
		a: 65,
		b: 66,
		c: 67,
		d: 100,
		f: 70,
		i: 73,
		k: 75,
		l: 76,
		m: 77,
		r: 82,
		s: 83,
		t: 84,
		u: 85,
		v: 86,
		RETURN: 13,
		ESC: 27// hide/show UniversalSuggester
	};	
	
// set default suggester switch values	
// snippet entry numbers and when they apply:
// %res = result
// %subres = subresult
// %sel = selected text in editor window
// %c = cursor
// 1. no selection, no subresult
// 2. selection, no subresult
// 3. no selection, subresult 
// 4. selection, subresult
	ga_suggesterSwitchValues[0] = {"name":"User",
								  "shortcuts":[gKEY.u],
								  "cssClass":"us_userBg",
								  "snippet":["[[User:%res]]",
								  			 "[[User:%res|%selText]]"]};
	
	ga_suggesterSwitchValues[1] = {"name":"Link",
								  "shortcuts":[gKEY.l],
								  "cssClass":"us_linkBg",
								  "snippet":["[[%res]]",
								  			 "[[%res|%selText]]",
								  			 "[[%res#%subres]]",
								  			 "[[%res#%subres|%selText]]"]};
	
	ga_suggesterSwitchValues[2] = {"name":"Transclusion",
								  "shortcuts":[gKEY.r],
								  "cssClass":"us_transclusionBg",
								  "snippet":["{{%res}}",
								  			 "{{%res|%selText}}",
								  			 "{{%res#%subres}}",
								  			 "{{%res#%subres|%selText}}"]};
	
	ga_suggesterSwitchValues[3] = {"name":"Attribute", 
								  "shortcuts":[gKEY.a],
								  "cssClass":"us_attributeBg",
								  "snippet":["[[%res::%c]]",
								  			 "[[%res::%selText]]",
								  			 "[[%res::%subres]]"]};
	
	ga_suggesterSwitchValues[4] = {"name":"Category",
								  "shortcuts":[gKEY.c,gKEY.k],
								  "cssClass":"us_categoryBg",
								  "snippet":["[[Category:%res]]",
								  			 "[[Category:%res|%selText]]"]};
	
	ga_suggesterSwitchValues[5] = {"name":"Template",
								  "shortcuts":[gKEY.v,gKEY.t],
								  "cssClass":"us_templateBg",
								  "snippet":["{{Template:%res%options}}"]};
	
	ga_suggesterSwitchValues[6] = {"name":"Image",
								  "shortcuts":[gKEY.i,gKEY.b],
								  "cssClass":"us_imageBg",
								  "snippet":["[[Image:%res|frame|center|%c]]",
								  			 "[[Image:%res|frame|center|%selText]]"]};
	
    ga_suggesterSwitchValues[7] = {"name":"Media",
								  "shortcuts":[gKEY.f,gKEY.m],
								  "cssClass":"us_mediaBg",
								  "snippet":["[[Media:%res]]",
								  			 "[[Media:%res|%selText]]"]};  								  
	
/*********************************************************	
* Init the extension if the page is fully loaded.
* Function gets called via the addOnloadHook of the MediaWiki
*/
	initUniversalSuggester = function () {
		
	// only proceed if the wiki toolbar element exists
	// and the necessary components of the 'UniversalSuggester',
	// which are 'us_box' and 'us_userInput' are alreay elements on the page
	
		if($('#toolbar').length && $('#us_box').length && $("#us_userInput").length){
			
			var s_usBox = $('#us_box');
			
			$('#us_box').remove();
			$("#toolbar").append(s_usBox);
			$('#us_box').css({display: 'inline'});
			
		//no url to callback script given, because mediawiki takes care of it 
			$("#us_userInput").autocomplete(
				' ',
				{
					pageTitle: document.title,
					width: 450,
					minChars: 1,
					max:41,
					selectFirst:false
				}
			);
			
			initListeners();
			initKeyCommands();
		}
	}//initUniversalSuggester
	
	
/*********************************************************	
*  Register event listeners 
*/
	function initListeners() {
		
		$("#us_button").click(function(){
			showUniversalSuggester();
		});
		
	// hide the suggester input field if it contains no value and looses focus
		$("#us_userInput").blur(function(){
			if ($("#us_userInput").val()=='') {
				hideUniversalSuggester();
			}
		});
		
	}//initListeners
	

/*********************************************************	
*  Register key listeners 
*/	
	function initKeyCommands() {
		
		var b_shiftPressed     = false;
		var b_altPressed       = false;
		var b_strgPressed      = false;
		var b_commandPressed   = false;//command key on a mac
		var b_switchAction     = false;
		var b_suggesterChanged = false;
		
		document.onkeydown = function(e){   
			
		// check if one of the following keys is pressed	
			b_shiftPressed   = e.shiftKey;
			b_altPressed     = e.altKey;
			b_strgPressed    = e.ctrlKey;
			b_commandPressed = e.metaKey;	
			
		// define keycombo to hide or show the UniversalSuggester 
		// of course this can be changed!
		
			if (e == null) { // ie
				keycode  = event.keyCode;
			} else { // mozilla
				keycode = e.which;
			}
			
			//define the ESC key for all OS's
			if (keycode == gKEY.ESC) {
				b_switchAction = true;
			}
			//for mac define command+strg
			else if (navigator.appVersion.indexOf("Mac")!=-1) {
				b_switchAction = b_commandPressed && b_strgPressed;
			}
			//for windows define strg+alt
			else if (navigator.appVersion.indexOf("Win")!=-1) {
				b_switchAction = b_strgPressed && b_altPressed && (e.keyCode == gKEY.s);//for windows users
			}
			
			//hide or show(and set focus to) the UniversalSuggester	
		    if(b_switchAction){ 
	        	
	        	if ($("#us_userInput:hidden").length) {
	        		showUniversalSuggester();
	        	} else {
		        	
		        	if ($("#us_userInput").val()=='') {
		        		hideUniversalSuggester();
		        	}
		        	else {
		        		if ($("#us_userInput").hasClass("hasFocus")) {
		        			$("#" + gs_nameMwTextarea).focus();
		        			$("div.ac_results").hide();
		        			$("#us_userInput").removeClass("hasFocus");
		        		}else {
		        			$("#us_userInput").focus();
		        			$("#us_userInput").addClass("hasFocus");
		        		}
		        	}
	        	}
	        }
		}
			
		$("#us_userInput").keydown(function(e){
			
			/*console.log('keycode:' + getKeyCode(e));
			
			if (b_commandPressed && (getKeyCode(e) == gKEY.b)) {
				e.preventDefault();
				alert("test!!");
			}*/
			
			b_suggesterChanged = checkForSuggesterSwitch(getKeyCode(e));
		});
		
		$("#us_userInput").keyup(function(e){
			
		// insert the selection on the current position 
		// of the mediawiki editor textarea
			if (getKeyCode(e) == gKEY.RETURN) {
				$("#"+gs_nameMwTextarea).
					insertValueAtCaret($("#us_userInput").val(),
									ga_suggesterSwitchValues[gi_currentSuggester]["snippet"]);
				hideUniversalSuggester();
			}
			
		//if the suggester changed
	    //init it again	
	    	if (b_suggesterChanged) {
	        	initSuggester();
	        	b_suggesterChanged = false;
	        }
		});
		
	}//initKeyCommands
	
	
/*********************************************************	
* Returns the keycode of the pressed key
*
* @param event e
*
* @return int keycode
*/	
	function getKeyCode(e) {
		
		if (e == null) { // ie
			keycode  = event.keyCode;
		} else { // mozilla
			keycode = e.which;
		}
		
		return keycode;
	}//getKeyCode
	
	
/*********************************************************	
* Show the trigger image and hide the input field for the UniversalSuggester 
*
* @param int i_currentKeycode
*/
	function checkForSuggesterSwitch(i_currentKeycode) {
	
		var b_suggesterChanged = false;
	
	// check the value before the current key hits the input field
	// if it is the suggest switch value
	// check for the next char  	
		if ($("#us_userInput").val() == gs_suggesterSwitchTrigger) {
			
			for (var i = 0; i < ga_suggesterSwitchValues.length; i++) {
				
				for (var j = 0; j < ga_suggesterSwitchValues[i]["shortcuts"].length; j++) {
					
				// if the suggester index was found, save it and break the two loops	
					if (ga_suggesterSwitchValues[i]["shortcuts"][j] == i_currentKeycode) {
						gi_currentSuggester = i;
						b_suggesterChanged = true;
						break;
					}
				}
			// exit loop, if the suggester was switched	
				if (b_suggesterChanged) {
					break;
				}
			}
		}	
		
		return b_suggesterChanged;
	}//checkForSuggesterSwitch

/*********************************************************	
*  Init the user input field of the UniversalSuggester  
*/
	function initSuggester() {
		
		$("#us_userInput").val('');
		$("#us_userInput").removeClass();
        $("#us_userInput").addClass("hasFocus");
		$("#us_userInput").addClass(ga_suggesterSwitchValues[gi_currentSuggester]["cssClass"]);
		$("#us_userInput").flushCache();
		
	//change to url params to fit the current suggester
		$("#us_userInput").setOptions({extraParams:{suggester:ga_suggesterSwitchValues[gi_currentSuggester]['name']}});
		
	}//initSuggester
		

/*********************************************************	
*  Hide the trigger image and show the input field for the UniversalSuggester 
*/
	function showUniversalSuggester() {
		
		checkForSelectedText();
		
		$("#us_button").hide();
		$("#us_box .suggesterHelp").css({display: 'none'});
		$("#us_userInput").show().focus();
		initSuggester();
	}//showUniversalSuggester
		
/*********************************************************	
*  Show the trigger image and hide the input field for the UniversalSuggester 
*/
	function hideUniversalSuggester() {
		
		$("#us_userInput").hide();
		$("#us_box .suggesterHelp").css({display: 'block'});
		$("#us_button").show();
		$("#" + gs_nameMwTextarea).focus();
	}//hideUniversalSuggester
	
	
	function checkForSelectedText() {
		
		gs_selectedText = '';
		gi_posCaret = 0;
		
		var o_mwTextarea = $("#" + gs_nameMwTextarea)[0];
		
	//IE and Opera support	
		if (document.selection) {
				
			var o_selection = document.selection.createRange();
			o_selection.moveStart ('character', -o_mwTextarea.value.length);
			
		// The caret position is selection length
			gs_selectedText = document.selection.createRange().text;
			gi_posCaret = o_selection.text.length - gs_selectedText.length;
		}
	//MOZILLA/NETSCAPE support
		else if (o_mwTextarea.selectionStart || o_mwTextarea.selectionStart == '0') {
			
			var i_startPos = o_mwTextarea.selectionStart;
			var i_endPos = o_mwTextarea.selectionEnd;
			      
			if (i_startPos != i_endPos) {
				for (var j = i_startPos; j < i_endPos; j++) gs_selectedText += o_mwTextarea.value[j];
			}
			
			gi_posCaret = i_startPos;
		}
	}//checkForSelectedText
	
	
/*********************************************************
 * Custom function to insert custom text (param 's_value') on the position of the text cursor
 * Original from user variaas - http://www.mail-archive.com/jquery-en@googlegroups.com/msg08708.html
 * Modified to replace placeholder in s_pattern.
 *
 * Possible placeholder in s_pattern are:
 * - %res  (result): replace with s_value
 * - %subres (sub-result): sub-result is part of result and starts with the sub-result char (#) 
 * - %c  (cursor): set text cursor after replacement on this position
 * - %options used for templates - sets placeholders values into insertion
*/
	$.fn.insertValueAtCaret = function (s_value,a_patterns) {
        return this.each(function(){
                
            var s_subResult = s_options = '';
		    var i_patternToUse = 0;
		    var b_hasSubResult = b_hasOptions = false;
		    var i_newCursorPos = 0;
		
		// save the scrollbar position before any to the element change happens
		    var i_scrollTop = this.scrollTop;
			
		// in case a sub-result was also returned	
		// get rid of the sub-results trigger char '#'
		    var i_pos = s_value.indexOf('#');
			
			if (i_pos  != -1) {
				s_subResult = s_value.substring(i_pos + 1);
				s_value = s_value.substring(0,i_pos);
				b_hasSubResult = true;
			}
			
			var i_posOptions = s_value.indexOf('|');
			var a_optionKeys = new Array();
			
			if (i_posOptions  != -1) {
				
				s_options = s_value.substring(i_posOptions + 1);
				s_value = s_value.substring(0,i_posOptions);
				b_hasOptions = true;
				
				a_optionKeys = s_options.split("|");
				
				s_options = "";
				
				for(var i_key in a_optionKeys) {
					s_options += a_optionKeys[i_key] + "=\n|";
				}
				
				s_options = "\n|" + s_options; 
				
				if (s_options[s_options.length - 1] == "|") {
					s_options = s_options.slice(0, -1);
				}
			}
			
		// check which pattern applies	
			if (b_hasSubResult && gs_selectedText) i_patternToUse = 3;
			else if (b_hasSubResult && !gs_selectedText) i_patternToUse = 2;
			else if (!b_hasSubResult && gs_selectedText) i_patternToUse = 1;
			else i_patternToUse = 0;
		
		// check if the pattern actually exists in the array a_patterns
		// if not, try the previous pattern 	
			for (var j = i_patternToUse; j >= 0; j--) {
				
				if (a_patterns[j] != null) {
					i_patternToUse = j;
					break;
				}
			}
		   
		// replace the placeholders by their actual values
		// if the placeholders exist in the current pattern  
			s_value = a_patterns[i_patternToUse].replace(/%res/i, s_value);
			s_value = s_value.replace(/%subres/i, s_subResult);
			s_value = s_value.replace(/%selText/i, gs_selectedText);
			s_value = s_value.replace(/%options/i, s_options);
			
		// check for the placeholder of the cursor position
		// if positive, reset the cursor postion based on it
			if (s_value.indexOf('%c') != -1) {
				i_newCursorPos = gi_posCaret + s_value.indexOf('%c');
				
				s_value = s_value.replace(/%c/i,'');
			}
			else i_newCursorPos = gi_posCaret + s_value.length;
			    
		// replace the value of the textarea field by inserting
		// the constructed s_value on the last cursor position
			this.value = this.value.substring(0, gi_posCaret) + s_value +
						 this.value.substring((gi_posCaret + gs_selectedText.length),this.value.length);
			
			this.scrollTop = i_scrollTop;
			this.selectionStart = i_newCursorPos;
			this.selectionEnd	= i_newCursorPos;
        });
	};//insertValueAtCaret
	
	
/*********************************************************
 * Returns the equivalent to the PHP container var $_GET
*/	
	function get_GET_params() {
		var GET = new Array();
		if(location.search.length > 0) {
			var get_param_str = location.search.substring(1, location.search.length);
			var get_params = get_param_str.split("&");
			
			for(i = 0; i < get_params.length; i++) {
				var key_value = get_params[i].split("=");
				if(key_value.length == 2) {
					var key = key_value[0];
		            var value = key_value[1];
	            	GET[key] = value;
				}
			}
		}
		return(GET);
	}//get_GET_params
 

/*********************************************************
 * Returns the with key specified GET param
 *
 * @param string s_key
*/
	function get_GET_param(s_key) {
		var get_params = get_GET_params();
		
		if(get_params[s_key])
			return(get_params[s_key]);
		else
			return false;
	}//get_GET_param
	
	