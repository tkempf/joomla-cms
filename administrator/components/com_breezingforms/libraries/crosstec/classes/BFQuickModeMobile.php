<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.8
* @package BreezingForms
* @copyright (C) 2008-2012 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once(JPATH_SITE.'/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
require_once(JPATH_SITE.'/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

class BFQuickModeMobile{
	
	public $forceMobileUrl = '';
	/**
	 * @var HTML_facileFormsProcessor
	 */
	private $p = null;
	
	private $dataObject = array();
	
	private $rootMdata = array();
	
	private $useErrorAlerts = false;

        private $useDefaultErrors = false;

        private $useBalloonErrors = false;
	
	private $rollover = false;
	
	private $rolloverColor = '';
	
	private $toggleFields = '';
        
        private $hasFlashUpload = false;
	
	private $flashUploadTicket = '';
	
	private $cancelImagePath = '';
	
	private $uploadImagePath = '';
        private $language_tag = '';
        
        private $hasResponsiveDatePicker = false;
        
	function __construct( HTML_facileFormsProcessor $p ){
            
                jimport('joomla.version');
                $version = new JVersion();
                
                if(version_compare($version->getShortVersion(), '2.5', '>=')){
                    $this->language_tag = JFactory::getLanguage()->getTag() != JFactory::getLanguage()->getDefault() ? JFactory::getLanguage()->getTag() : 'zz-ZZ';
                }
            
                $head = JFactory::getDocument()->getHeadData();
                $head['styleSheets'] = array();
                $head['style'] = array();
                $head['scripts'] = array();
                $head['script'] = array();
                $head['custom'] = array();
                JFactory::getDocument()->setHeadData( $head );
                // will make sure mootools loads first, important 4 jquery
                JHtml::_('behavior.framework');
                jimport('joomla.version');
                $version = new JVersion();
                if(version_compare($version->getShortVersion(), '3.0', '<')){
                    JHTML::_('behavior.mootools');
                }
                
                $this->p = $p;
		$this->dataObject = Zend_Json::decode( bf_b64dec($this->p->formrow->template_code) );
                
		$this->rootMdata = $this->dataObject['properties'];
                
                if(JRequest::getVar('ff_applic','') != 'mod_facileforms' && JRequest::getVar('ff_applic','') != 'plg_facileforms'){
                    /* translatables */
                    if(isset($this->rootMdata['title_translation'.$this->language_tag]) && $this->rootMdata['title_translation'.$this->language_tag] != ''){
                        $this->rootMdata['title'] = $this->rootMdata['title_translation'.$this->language_tag];
                        JFactory::getDocument()->setTitle($this->rootMdata['title']);
                    }
                    /* translatables end */
                }
                
		$this->useErrorAlerts = $this->rootMdata['useErrorAlerts'];
                $this->useDefaultErrors = isset($this->rootMdata['useDefaultErrors']) ? $this->rootMdata['useDefaultErrors'] : false;
                $this->useBalloonErrors = isset($this->rootMdata['useBalloonErrors']) ? $this->rootMdata['useBalloonErrors'] : false;
		$this->rollover = $this->rootMdata['rollover'];
		$this->rolloverColor = $this->rootMdata['rolloverColor'];
		$this->toggleFields = $this->parseToggleFields( isset($this->rootMdata['toggleFields']) ? $this->rootMdata['toggleFields'] : '[]' );
		// loading theme
		$this->cancelImagePath = JURI::root(true) . '/media/breezingforms/themes/cancel.png';
		$this->uploadImagePath = JURI::root(true) . '/media/breezingforms/themes/upload.png';
		
                mt_srand();
		$this->flashUploadTicket = md5( strtotime('now') .  mt_rand( 0, mt_getrandmax() ) );
                
	}
        
	public function parseToggleFields( $code ){
		/*
		 	example codes:

			turn on element bla if blub is on
			turn off section bla if blub is on
			turn on section bla if blub is off
			turn off element bla if blub is off

                        if element opener is off set opener huhuu

			syntax:
			ACTION STATE TARGETCATEGORY TARGETNAME if SRCNAME is VALUE
		 */

		$parsed = '';
		$code = str_replace("\r", '', $code);
		$lines = explode( "\n", $code );
		$linesCnt = count( $lines );

		for($i = 0; $i < $linesCnt;$i++){
			$tokens = explode( ' ', trim($lines[$i]) );
			$tokensCnt = count($tokens);
			if($tokensCnt >= 8){
				$state = '';
				// rebuilding the state as it could be a value containing blanks
				for($j = 7; $j < $tokensCnt; $j++){
					if($j+1 < $tokensCnt)
						$state .= $tokens[$j] . ' ';
					else
						$state .= $tokens[$j];
				}
				$parsed .= '{ action: "'.$tokens[0].'", state: "'.$tokens[1].'", tCat: "'.$tokens[2].'", tName: "'.$tokens[3].'", statement: "'.$tokens[4].'", sName: "'.$tokens[5].'", condition: "'.$tokens[6].'", value: "'.addslashes($state).'" },';
			}
		}

		return "[".rtrim($parsed, ",")."]";
	}

        public function fetchHead($head)
	{
		$app = JFactory::getApplication();
                
		// Get line endings
		$lnEnd = JFactory::getDocument()->_getLineEnd();
		$tab = JFactory::getDocument()->_getTab();
		$tagEnd = ' />';
		$buffer = '';

		// Generate stylesheet links
		foreach ($head['styleSheets'] as $strSrc => $strAttr)
		{
			$buffer .= $tab . '<link rel="stylesheet" href="' . $strSrc . '" type="' . $strAttr['mime'] . '"';
			if (!is_null($strAttr['media']))
			{
				$buffer .= ' media="' . $strAttr['media'] . '" ';
			}
			if ($temp = JArrayHelper::toString($strAttr['attribs']))
			{
				$buffer .= ' ' . $temp;
			}
			$buffer .= $tagEnd . $lnEnd;
		}

		// Generate stylesheet declarations
		foreach ($head['style'] as $type => $content)
		{
			$buffer .= $tab . '<style type="' . $type . '">' . $lnEnd;

			// This is for full XHTML support.
			if (isset($document) && $document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . $lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if (isset($document) && $document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . $lnEnd;
			}
			$buffer .= $tab . '</style>' . $lnEnd;
		}

		// Generate script file links
		foreach ($head['scripts'] as $strSrc => $strAttr)
		{
			$buffer .= $tab . '<script src="' . $strSrc . '"';
			if (isset($strAttr['mime']) && !is_null($strAttr['mime']))
			{
				$buffer .= ' type="' . ( $strAttr['mime'] == 't' ? 'text/javascript' : $strAttr['mime'] ) . '"';
			}
			$buffer .= '></script>' . $lnEnd;
		}

		// Generate script declarations
		foreach ($head['script'] as $type => $content)
		{
			$buffer .= $tab . '<script type="' . $type . '">' . $lnEnd;

			// This is for full XHTML support.
			if (isset($document) && $document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . $lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if (isset($document) && $document->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . $lnEnd;
			}
			$buffer .= $tab . '</script>' . $lnEnd;
		}

		foreach ($head['custom'] as $custom)
		{
			$buffer .= $tab . $custom . $lnEnd;
		}

		return $buffer;
	}
	
	public function headers(){

		if( $this->hasFlashUpload ){
			$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/plupload/moxie.js');
			$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/plupload/plupload.js');
		}

		// loading system css
		$this->addStyleSheet( JURI::root(true) . '/components/com_breezingforms/themes/quickmode/mobile-system.css' );

		$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/jq.min.legacy.js');

		if($this->hasResponsiveDatePicker){
			$this->addScript(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/picker.js');
			$this->addScript(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/picker.date.js');

			$lang = JFactory::getLanguage()->getTag();
			$lang = explode('-', $lang);
			$lang = strtolower($lang[0]);
			if(JFile::exists(JPATH_SITE.'/components/com_breezingforms/libraries/jquery/pickadate/translations/'.$lang.'.js')){
				$this->addScript(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/translations/'.$lang.'.js');
			}

			$this->addStyleSheet(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/themes/default.css');
			$this->addStyleSheet(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/themes/default.date.css');
		}

		if(JFile::exists(JPATH_SITE . '/media/breezingforms/themes/jq.mobile.external-png.1.4.5.min.css')){
			$this->addStyleSheet( JURI::root(true) . '/media/breezingforms/themes/jq.mobile.external-png.1.4.5.min.css' );
		}else{
			$this->addStyleSheet( JURI::root(true) . '/media/breezingforms/themes/jq.mobile.1.4.5.icons.min.css' );
		}

		$this->addStyleSheet( JURI::root(true) . '/media/breezingforms/themes/jq.mobile.1.4.5.min.css' );

		if(JFile::exists(JPATH_SITE . '/media/breezingforms/themes/jq.mobile.1.4.5.custom.css')){
			$this->addStyleSheet( JURI::root(true) . '/media/breezingforms/themes/jq.mobile.1.4.5.custom.css' );
		}

		$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/jq.mobile.min.js');

		$this->addStyleSheet( JURI::root(true) . '/components/com_breezingforms/libraries/jquery/tooltip.css' );
		$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/tooltip.js');

		if( $this->hasFlashUpload ){
			$tickets = JFactory::getSession()->get('bfFlashUploadTickets', array());
			$tickets[$this->flashUploadTicket] = array(); // stores file info for later processing
			JFactory::getSession()->set('bfFlashUploadTickets', $tickets);
			$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/center.js');
			$this->addScriptDeclaration('
                        var bfUploaders = [];
                        var bfUploaderErrorElements = [];
			var bfFlashUploadInterval = null;
			var bfFlashUploaders = new Array();
                        var bfFlashUploadersLength = 0;
                        function bfRefreshAll(){
                            for( var i = 0; i < bfUploaders.length; i++ ){
                                bfUploaders[i].refresh();
                            }
                        }
                        function bfInitAll(){
                            for( var i = 0; i < bfUploaders.length; i++ ){
                                bfUploaders[i].init();
                            }
                        }
			function bfDoFlashUpload(){
                                JQuery("#bfSubmitMessage").css("visibility","hidden");
                                JQuery("#bfSubmitMessage").css("z-index","999999");
				JQuery(".bfErrorMessage").html("");
                                JQuery(".bfErrorMessage").css("display","none");
                                for(var i = 0; i < bfUploaderErrorElements.length; i++){
                                    JQuery("#"+bfUploaderErrorElements[i]).html("");
                                }
                                bfUploaderErrorElements = [];
                                if(ff_validation(0) == ""){
					try{
                                            bfFlashUploadInterval = window.setInterval( bfCheckFlashUploadProgress, 1000 );
                                            if(bfFlashUploadersLength > 0){
                                                JQuery("#bfFileQueue").bfcenter(true);
                                                JQuery("#bfFileQueue").css("visibility","visible");
                                                for( var i = 0; i < bfUploaders.length; i++ ){
                                                    bfUploaders[i].start();
                                                }
                                            }
					} catch(e){alert(e)}
				} else {
					if(typeof bfUseErrorAlerts == "undefined"){
                                            alert(error);
                                        } else {
                                            bfShowErrors(error);
                                        }
                                        ff_validationFocus("");
                                        document.getElementById("bfSubmitButton").disabled = false;
				}
			}
			function bfCheckFlashUploadProgress(){
				if( JQuery("#bfFileQueue").html() == "" ){ // empty indicates that all queues are uploaded or in any way cancelled
					JQuery("#bfFileQueue").css("visibility","hidden");
					window.clearInterval( bfFlashUploadInterval );
                                        if(typeof bfAjaxObject101 != \'undefined\' || typeof bfReCaptchaLoaded != \'undefined\'){
                                            ff_submitForm2();
                                        }else{
                                            ff_validate_submit(document.getElementById("bfSubmitButton"), "click");
                                        }
					JQuery(".bfFlashFileQueueClass").html("");
                                        if(bfFlashUploadersLength > 0){
                                            JQuery("#bfSubmitMessage").bfcenter(true);
                                            JQuery("#bfSubmitMessage").css("visibility","visible");
                                            JQuery("#bfSubmitMessage").css("z-index","999999");
                                        }
                                        
				}
			}
			');
		}

		if($this->useBalloonErrors){
			$this->addStyleSheet( JURI::root(true) . '/components/com_breezingforms/libraries/jquery/validationEngine.jquery.css' );
			$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/jquery.validationEngine-en.js');
			$this->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/jquery.validationEngine.js');
		}

		$this->addStyleDeclaration('.tooltip { margin-left: 2%; margin-top: 5px; }');

		$toggleCode = '';
		if($this->toggleFields != '[]'){
			$toggleCode = '
			var toggleFieldsArray = '.$this->toggleFields.';
			String.prototype.beginsWith = function(t, i) { if (i==false) { return
			(t == this.substring(0, t.length)); } else { return (t.toLowerCase()
			== this.substring(0, t.length).toLowerCase()); } } 
			function bfDeactivateSectionFields(){
				for( var i = 0; i < bfDeactivateSection.length; i++ ){
                                        bfSetFieldValue(bfDeactivateSection[i], "off");
					JQuery("#"+bfDeactivateSection[i]+" .ff_elem").each(function(i){
                                            if( JQuery(this).get(0).name && JQuery(this).get(0).name.beginsWith("ff_nm_", true) ){
                                                bfDeactivateField[JQuery(this).get(0).name] = true;
                                            }
					});
				}
                                for( var i = 0; i < toggleFieldsArray.length; i++ ){
                                    if(toggleFieldsArray[i].state == "turn"){
                                        bfSetFieldValue(toggleFieldsArray[i].tName, "off");
                                    }
                                }
			}
			function bfToggleFields(state, tCat, tName, thisBfDeactivateField){
                                if(state == "on"){
					if(tCat == "element"){
                                                JQuery("[name=\"ff_nm_"+tName+"[]\"]").closest(".bfElemWrap").css("display","");
						thisBfDeactivateField["ff_nm_"+tName+"[]"] = false;
                                                bfSetFieldValue(tName, "on");
					} else {
						JQuery("#"+tName).css("display", "");
                                                bfSetFieldValue(tName, "on");
						JQuery("#"+tName).find(".ff_elem").each(function(i){
                                                    if( JQuery(this).get(0).name && JQuery(this).get(0).name.beginsWith("ff_nm_", true) ){
                                                        thisBfDeactivateField[JQuery(this).get(0).name] = false;
                                                    }
						});
					}
				} else {
					if(tCat == "element"){
                                                JQuery("[name=\"ff_nm_"+tName+"[]\"]").closest(".bfElemWrap").css("display","none");
						thisBfDeactivateField["ff_nm_"+tName+"[]"] = true;
                                                bfSetFieldValue(tName, "off");
					} else {
						JQuery("#"+tName).css("display", "none");
                                                bfSetFieldValue(tName, "off");
						JQuery("#"+tName+" .ff_elem").each(function(i){
                                                    if( JQuery(this).get(0).name && JQuery(this).get(0).name.beginsWith("ff_nm_", true) ){
                                                        thisBfDeactivateField[JQuery(this).get(0).name] = true;
                                                    }
						});
					}
				}
                                if(typeof bfRefreshAll != "undefined"){
                                    bfRefreshAll();
                                }
			}
                        function bfSetFieldValue(name, condition){
                            for( var i = 0; i < toggleFieldsArray.length; i++ ){
                                if( toggleFieldsArray[i].action == "if" ) {
                                    //alert(toggleFieldsArray[i].condition);
                                    if(name == toggleFieldsArray[i].tCat && condition == toggleFieldsArray[i].statement){
                                    
                                        var element = JQuery("[name=\"ff_nm_"+toggleFieldsArray[i].condition+"[]\"]");
                                        
                                        switch(element.get(0).type){
                                            case "text":
                                            case "textarea":
                                                if(toggleFieldsArray[i].value == "!empty"){
                                                    element.val("");
                                                } else {
                                                    element.val(toggleFieldsArray[i].value);
                                                }
                                            break;
                                            case "select-multiple":
                                            case "select-one":
                                                if(toggleFieldsArray[i].value == "!empty"){
                                                    for(var j = 0; j < element.get(0).options.length; j++){
                                                        element.get(0).options[j].selected = false;
                                                    }
                                                }
                                                for(var j = 0; j < element.get(0).options.length; j++){
                                                    if(element.get(0).options[j].value == toggleFieldsArray[i].value){
                                                        element.get(0).options[j].selected = true;
                                                    }
                                                }
                                            break;
                                            case "radio":
                                            case "checkbox":
                                                var radioLength = element.size();
                                                if(toggleFieldsArray[i].value == "!empty"){
                                                    for(var j = 0; j < radioLength; j++){
                                                        element.get(j).checked = false;
                                                    }
                                                }
						for(var j = 0; j < radioLength; j++){
                                                    if( element.get(j).value == toggleFieldsArray[i].value ){
                                                        element.get(j).checked = true;
                                                    }
                                                }
                                            break;
                                        }
                                    }
                                }
                            }
                        }
			function bfRegisterToggleFields(){
                        
                                var offset = 0;
                                var last_offset = 0;
                                var limit  = 10;
                                var limit_cnt = 0;
                                
                                if( arguments.length == 1 ){
                                    offset = arguments[0];
                                }

                                var thisToggleFieldsArray = toggleFieldsArray;
				var thisBfDeactivateField = bfDeactivateField;
                                var thisBfToggleFields = bfToggleFields;
                                
				for( var i = offset; limit_cnt < limit && i < toggleFieldsArray.length; i++ ){
                                //  for( var i = 0; i < toggleFieldsArray.length; i++ ){
                                              if( toggleFieldsArray[i].action == "turn" && (toggleFieldsArray[i].tCat == "element" || toggleFieldsArray[i].tCat == "section") ){
						var toggleField = toggleFieldsArray[i];
						var element = JQuery("[name=\"ff_nm_"+toggleFieldsArray[i].sName+"[]\"]");
						if(element.get(0)){
							switch(element.get(0).type){
								case "text":
								case "textarea":
                                                                        JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").unbind("blur");
									JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").blur(
										function(){
											for( var k = 0; k < thisToggleFieldsArray.length; k++ ){
												var regExp = "";
												if(thisToggleFieldsArray[k].value.beginsWith("!", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 			regExp = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length);
                                                                                                        testRegExp = new RegExp(regExp);
										 		}

                                                                                                if(thisToggleFieldsArray[k].condition == "isnot"){
                                                                                                    if(
                                                                                                            ( ( regExp != "" && testRegExp.test(JQuery(this).val()) == false ) || JQuery(this).val() != thisToggleFieldsArray[k].value ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                    ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                } else if(thisToggleFieldsArray[k].condition == "is"){
                                                                                                    if(
                                                                                                            ( ( regExp != "" && testRegExp.test(JQuery(this).val()) == true ) || JQuery(this).val() == thisToggleFieldsArray[k].value ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                    ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                }
											}
										}
									);
									break;
								case "select-multiple":
								case "select-one":
                                                                        JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").unbind("change");
									JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").change(
										function(){
											var res = JQuery.isArray( JQuery(this).val() ) == false ? [ JQuery(this).val() ] : JQuery(this).val();
											for( var k = 0; k < thisToggleFieldsArray.length; k++ ){
												
												// The or-case in lists 
												var found = false;
												var chkGrpValues = new Array();
										 		if(thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 			chkGrpValues = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split("|");
										 			for(var l = 0; l < chkGrpValues.length; l++){
										 				if( JQuery.inArray(chkGrpValues[l], res) != -1 ){
										 					found = true;
										 					break;
										 				}
										 			}
										 		}
												// the and-case in lists
												var foundCount = 0;
												chkGrpValues2 = new Array();
										 		if(thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 			chkGrpValues2 = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split(";");
										 			for(var l = 0; l < res.length; l++){
										 				if( JQuery.inArray(res[l], chkGrpValues2) != -1 ){
										 					foundCount++;
										 				}
										 			}
										 		}
                                                                                                
                                                                                                if(thisToggleFieldsArray[k].condition == "isnot"){
                                                                                                
                                                                                                    if(
                                                                                                            (
                                                                                                                    !JQuery.isArray(res) && JQuery(this).val() != thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                            ||
                                                                                                            (
                                                                                                                    JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) == -1 || !found || ( foundCount == 0 || foundCount != chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                     ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                } else if(thisToggleFieldsArray[k].condition == "is"){
                                                                                                    if(
                                                                                                            (
                                                                                                                    !JQuery.isArray(res) && JQuery(this).val() == thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                            ||
                                                                                                            (
                                                                                                                    JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) != -1 || found || ( foundCount != 0 && foundCount == chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                     ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                }
											}
										}
									);
									break;
								case "radio":
								case "checkbox":
									var radioLength = JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").size();
									for(var j = 0; j < radioLength; j++){
                                                                                 JQuery("#" + JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").get(j).id ).unbind("click");
										 JQuery("#" + JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").get(j).id ).click(										 	
										 	function(){
										 		// NOT O(n^2) since its ony executed on click event!
                                                                                                var res = [];
                                                                                                JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]:checked").each(function() {
                                                                                                  res.push(JQuery(this).val());
                                                                                                });
										 		for( var k = 0; k < thisToggleFieldsArray.length; k++ ){
										 			
										 			// used for complex checkbox group case below
										 			var chkGrpValues = new Array();
                                                                                                        var found = false;
										 			if(JQuery(this).get(0).checked && thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 				chkGrpValues = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split("|");
                                                                                                                for(var l = 0; l < chkGrpValues.length; l++){
                                                                                                                    if( JQuery.inArray(chkGrpValues[l], res) != -1 ){
                                                                                                                            found = true;
                                                                                                                            break;
                                                                                                                    }
                                                                                                                }
										 			}
                                                                                                        
                                                                                                        // the and-case
                                                                                                        var foundCount = 0;
                                                                                                        chkGrpValues2 = new Array();
                                                                                                        if(thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
                                                                                                                chkGrpValues2 = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split(";");
                                                                                                                for(var l = 0; l < res.length; l++){
                                                                                                                        if( JQuery.inArray(res[l], chkGrpValues2) != -1 ){
                                                                                                                                foundCount++;
                                                                                                                        }
                                                                                                                }
                                                                                                        }

                                                                                                        if(thisToggleFieldsArray[k].condition == "isnot"){

                                                                                                            if(
                                                                                                                    // simple radio case for selected value
                                                                                                                    ( JQuery(this).get(0).type == "radio" && JQuery(this).get(0).checked && JQuery(this).val() != thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" )
                                                                                                                    ||
                                                                                                                    // single checkbox case for checked/unchecked
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" &&
                                                                                                                            JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" &&
                                                                                                                            ( JQuery(this).get(0).checked && thisToggleFieldsArray[k].value != "!checked"
                                                                                                                             ||
                                                                                                                              JQuery(this).get(0).checked && thisToggleFieldsArray[k].value == "!unchecked"
                                                                                                                            )
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // complex checkbox/radio group case by multiple values
                                                                                                                    (
                                                                                                                            JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) == -1 || !found || ( foundCount == 0 || foundCount != chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // simple checkbox group case by single value
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" && JQuery(this).get(0).checked && JQuery(this).val() != thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                            ){
                                                                                                                    var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                                    for(var n = 0; n < names.length; n++){
                                                                                                                        thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                                    }
                                                                                                                    //break;
                                                                                                            }
                                                                                                        }
                                                                                                        else
                                                                                                        if(thisToggleFieldsArray[k].condition == "is"){
                                                                                                            if(
                                                                                                                    // simple radio case for selected value
                                                                                                                    ( JQuery(this).get(0).type == "radio" && JQuery(this).get(0).checked && JQuery(this).val() == thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" )
                                                                                                                    ||
                                                                                                                    // single checkbox case for checked/unchecked
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" &&
                                                                                                                            JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" &&
                                                                                                                            ( JQuery(this).get(0).checked && thisToggleFieldsArray[k].value == "!checked"
                                                                                                                             ||
                                                                                                                              !JQuery(this).get(0).checked && thisToggleFieldsArray[k].value == "!unchecked"
                                                                                                                            )
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // complex checkbox/radio group case by multiple values
                                                                                                                    (
                                                                                                                            JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) != -1 || found || ( foundCount != 0 && foundCount == chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // simple checkbox group case by single value
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" && JQuery(this).get(0).checked && JQuery(this).val() == thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                            ){
                                                                                                                    var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                                    for(var n = 0; n < names.length; n++){
                                                                                                                        thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                                    }
                                                                                                                    //break;
                                                                                                            }
                                                                                                        }
												}
												
											}
										 );
									}
									break;
							}
						}
					}
                                        
                                        limit_cnt++;
                                        last_offset = i;
                                }
                                
                                if( last_offset+1 < toggleFieldsArray.length ){ setTimeout("bfRegisterToggleFields( "+last_offset+" )", 350); }
                        }';

		}

		$this->addScriptDeclaration(
			'
			var inlineErrorElements = new Array();
			var bfSummarizers = new Array();
			var bfDeactivateField = new Array();
			var bfDeactivateSection = new Array();
			'.$toggleCode.'
			function bfCheckMaxlength(id, maxlength, showMaxlength){
				if( JQuery("#ff_elem"+id).val().length > maxlength ){
					JQuery("#ff_elem"+id).val( JQuery("#ff_elem"+id).val().substring(0, maxlength) );
				}
				if(showMaxlength){
					JQuery("#bfMaxLengthCounter"+id).text( "(" + (maxlength - JQuery("#ff_elem"+id).val().length) + " '.BFText::_('COM_BREEZINGFORMS_CHARS_LEFT').')" );
				}
			}
			function bfRegisterSummarize(id, connectWith, type, emptyMessage, hideIfEmpty){
				bfSummarizers.push( { id : id, connectWith : connectWith, type : type, emptyMessage : emptyMessage, hideIfEmpty : hideIfEmpty } );
			}
			function bfField(name){
				var value = "";
				switch(ff_getElementByName(name).type){
					case "radio":
						if(JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val() != "" && typeof JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val() != "undefined"){
							value = JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val();
							if(!isNaN(value)){
								value = Number(value);
							}
						}
						break;
					case "checkbox":
					case "select-one":
					case "select-multiple":
						var nodeList = document["'.$this->p->form_id.'"][""+ff_getElementByName(name).name+""];
						if(ff_getElementByName(name).type == "checkbox" && typeof nodeList.length == "undefined"){
							if(typeof JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val() != "undefined"){
								value = JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val();
								if(!isNaN(value)){
									value = Number(value);
								}
							}
						} else {
							var val = "";
							for(var j = 0; j < nodeList.length; j++){
								if(nodeList[j].checked || nodeList[j].selected){
									val += nodeList[j].value + ", ";
								}
							}
							if(val != ""){
								value = val.substr(0, val.length - 2);
								if(!isNaN(value)){
									value = Number(value);
								}
							}
						}
						break;
					default:
						if(!isNaN(ff_getElementByName(name).value)){
							value = Number(ff_getElementByName(name).value);
						} else {
							value = ff_getElementByName(name).value;
						}
				}
				return value;
			}
			function populateSummarizers(){
				// cleaning first
                                
				for(var i = 0; i < bfSummarizers.length; i++){
					JQuery("#"+bfSummarizers[i].id).parent().css("display", "");
					JQuery("#"+bfSummarizers[i].id).html("<span class=\"bfNotAvailable\">"+bfSummarizers[i].emptyMessage+"</span>");
				}
				for(var i = 0; i < bfSummarizers.length; i++){
					var summVal = "";
					switch(bfSummarizers[i].type){
						case "bfTextfield":
						case "bfTextarea":
						case "bfHidden":
						case "bfCalendar":
                                                case "bfCalendarResponsive":
						case "bfFile":
							if(JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]").val() != ""){
								JQuery("#"+bfSummarizers[i].id).text( JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]").val() ).html();
								var breakableText = JQuery("#"+bfSummarizers[i].id).html().replace(/\\r/g, "").replace(/\\n/g, "<br/>");
								
								if(breakableText != ""){
									var calc = null;
									eval( "calc = typeof bfFieldCalc"+bfSummarizers[i].id+" != \"undefined\" ? bfFieldCalc"+bfSummarizers[i].id+" : null" );
									if(calc){
										breakableText = calc(breakableText);
									}
								}
								
								JQuery("#"+bfSummarizers[i].id).html(breakableText);
								summVal = breakableText;
							}
						break;
						case "bfRadioGroup":
						case "bfCheckbox":
							if(JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]:checked").val() != "" && typeof JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]:checked").val() != "undefined"){
								var theText = JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]:checked").val();
								if(theText != ""){
									var calc = null;
									eval( "calc = typeof bfFieldCalc"+bfSummarizers[i].id+" != \"undefined\" ? bfFieldCalc"+bfSummarizers[i].id+" : null" );
									if(calc){
										theText = calc(theText);
									}
								}
								JQuery("#"+bfSummarizers[i].id).html( theText );
								summVal = theText;
							}
						break;
						case "bfCheckboxGroup":
						case "bfSelect":
							var val = "";
							var nodeList = document["'.$this->p->form_id.'"]["ff_nm_"+bfSummarizers[i].connectWith+"[]"];
							
							for(var j = 0; j < nodeList.length; j++){
								if(nodeList[j].checked || nodeList[j].selected){
									val += nodeList[j].value + ", ";
								}
							}
							if(val != ""){
								var theText = val.substr(0, val.length - 2);
								if(theText != ""){
									var calc = null;
									eval( "calc = typeof bfFieldCalc"+bfSummarizers[i].id+" != \"undefined\" ? bfFieldCalc"+bfSummarizers[i].id+" : null" );
									if(calc){
										theText = calc(theText);
									}
								}
								JQuery("#"+bfSummarizers[i].id).html( theText );
								summVal = theText;
							}
						break;
					}
					
					if( ( bfSummarizers[i].hideIfEmpty && summVal == "" ) || ( typeof bfDeactivateField != "undefined" && bfDeactivateField["ff_nm_"+bfSummarizers[i].connectWith+"[]"] ) ){
                                            JQuery("#"+bfSummarizers[i].id).parent().css("display", "none");
					}
				}
			}
                ');

		if(!$this->useErrorAlerts || $this->rollover){
			if(!$this->useErrorAlerts){
				$defaultErrors = '';
				if($this->useDefaultErrors || (!$this->useDefaultErrors && !$this->useBalloonErrors)){
					$defaultErrors = 'JQuery(".bfErrorMessage").html("");
					JQuery(".bfErrorMessage").css("display","none");
					JQuery(".bfErrorMessage").fadeIn(1500);
					var allErrors = "";
					var errors = error.split("\n");
					for(var i = 0; i < errors.length; i++){
						allErrors += "<div class=\"bfError\">" + errors[i] + "</div>";
					}
					JQuery(".bfErrorMessage").html(allErrors);
					JQuery(".bfErrorMessage").css("display","");';
				}
				$this->addScriptDeclaration('var bfUseErrorAlerts = false;'."\n");
				$this->addScriptDeclaration('
				function bfShowErrors(error){
                                        '.$defaultErrors.'

                                        if(JQuery.bfvalidationEngine)
                                        {
                                            JQuery("#'.$this->p->form_id.'").bfvalidationEngine({
                                              promptPosition: "bottomLeft",
                                              success :  false,
                                              failure : function() {}
                                            });

                                            for(var i = 0; i < inlineErrorElements.length; i++)
                                            {
                                                if(inlineErrorElements[i][1] != "")
                                                {
                                                    var prompt = null;
                                                    
                                                    if(inlineErrorElements[i][0] == "bfCaptchaEntry"){
                                                        prompt = JQuery.bfvalidationEngine.buildPrompt("#bfCaptchaEntry",inlineErrorElements[i][1],"error");
                                                    }
                                                    else if(inlineErrorElements[i][0] == "bfReCaptchaEntry"){
                                                        // nothing here yet for recaptcha, alert is default
                                                        alert(inlineErrorElements[i][1]);
                                                    }
                                                    else if(typeof JQuery("#bfUploader"+inlineErrorElements[i][0]).get(0) != "undefined")
                                                    {
                                                        alert(inlineErrorElements[i][1]);
                                                        //prompt = JQuery.bfvalidationEngine.buildPrompt("#"+JQuery("#bfUploader"+inlineErrorElements[i][0]).val(),inlineErrorElements[i][1],"error");
                                                    }
                                                    else
                                                    {
                                                        if(ff_getElementByName(inlineErrorElements[i][0])){
                                                            prompt = JQuery.bfvalidationEngine.buildPrompt("#"+ff_getElementByName(inlineErrorElements[i][0]).id,inlineErrorElements[i][1],"error");
                                                        }else{
                                                            alert(inlineErrorElements[i][1]);
                                                        }
                                                    }
                                                    
                                                    JQuery(prompt).mouseover(
                                                        function(){
                                                            var inlineError = JQuery(this).attr("class").split(" ");
                                                            if(inlineError && inlineError.length && inlineError.length == 2){
                                                                var result = inlineError[1].split("formError");
                                                                if(result && result.length && result.length >= 1){
                                                                    JQuery.bfvalidationEngine.closePrompt("#"+result[0]);
                                                                }
                                                            }
                                                        }
                                                    );
                                                }
                                                else
                                                {
                                                    if(typeof JQuery("#bfUploader"+inlineErrorElements[i][0]).get(0) != "undefined")
                                                    {
                                                        //JQuery.bfvalidationEngine.closePrompt("#"+JQuery("#bfUploader"+inlineErrorElements[i][0]).val());
                                                    }
                                                    else
                                                    {
                                                        if(ff_getElementByName(inlineErrorElements[i][0])){
                                                            JQuery.bfvalidationEngine.closePrompt("#"+ff_getElementByName(inlineErrorElements[i][0]).id);
                                                        }
                                                    }
                                                }
                                            }
                                            inlineErrorElements = new Array();
                                        }
				}');
			}
		}
		$this->addScriptDeclaration('
			JQuery(document).bind("pageinit", function() {
				if(typeof bfSetElemWrapBg != "undefined")bfSetElemWrapBg();
				if(typeof bfRegisterToggleFields != "undefined")bfRegisterToggleFields();
				if(typeof bfDeactivateSectionFields != "undefined")bfDeactivateSectionFields();
                                if(JQuery.bfvalidationEngine)
                                {
                                    JQuery.bfvalidationEngineLanguage.newLang();
                                    JQuery(".ff_elem").change(
                                        function(){
                                            JQuery.bfvalidationEngine.closePrompt(this);
                                        }
                                    );
                                }
				JQuery(".hasTip").css("color","inherit"); // fixing label text color issue
				JQuery(".bfTooltip").css("color","inherit"); // fixing label text color issue
    
                                JQuery("input[type=text]").bind("keypress", function(evt) {
                                    if(evt.keyCode == 13) {
                                        evt.preventDefault();
                                    }
                                });
                                JQuery(".tooltip").hide();
                                setInterval(
                                    function(){
                                        JQuery("input[type=\'checkbox\']").checkboxradio("refresh");
                                        JQuery("input[type=\'radio\']").checkboxradio("refresh");
                                        JQuery("input[type=\'text\']").textinput();
                                        JQuery("select").selectmenu("refresh");
                                        JQuery("textarea").textinput();
                                    }
                                    ,
                                    500
                                );
			});
                      
                        JQuery(document).bind("mobileinit", function(){
                            JQuery.mobile.loadingMessage = false;
                            JQuery.mobile.ignoreContentEnabled = false;
                            JQuery.mobile.selectmenu.prototype.options.nativeMenu = false;
                        });
		');

	}

	public function addScript($script){
		echo '<script type="text/javascript" src="'.$script.'"/>'."\n".'</script>'."\n";
	}

	public function addStyleSheet($sheet){
		echo '<link rel="stylesheet" href="'.$sheet.'" type="text/css" />'."\n";
	}

	public function addScriptDeclaration($declaration){
		echo '<script type="text/javascript"/><!--'."\n".$declaration."\n".'//--></script>'."\n";
	}

	public function addStyleDeclaration($declaration){
		echo '<style type="text/css">'."\n".$declaration."\n".'</style>'."\n";
	}

	public function render(){

		echo '<div data-role="page" data-theme="a" class="ui-page ui-page-theme-a ui-page-active">';

		//  data-position="fixed"
		echo '<div data-role="header" class="ui-header ui-bar-inherit">';
		echo '<h1>'.JFactory::getDocument()->getTitle().'</h1>';
		jimport('joomla.version');
		$version = new JVersion();
		$current_url = JURI::getInstance()->toString();
		if (version_compare($version->getShortVersion(), '3.0', '<')) {
			if(strstr($current_url,'?') !== false){
				$current_url_exploded = explode('?', $current_url);
				$current_url = '';
				$c_length = count($current_url_exploded);
				if($c_length > 1){
					for($c = 1; $c < $c_length; $c++){
						$current_params_exploded = explode('&', $current_url_exploded[$c]);
						$current_params_length = count($current_params_exploded);
						for($p = 0; $p < $current_params_length; $p++){
							$param_key_value = explode('=',$current_params_exploded[$p], 2);
							if(count($param_key_value) >= 2){
								$current_url .= urlencode($param_key_value[0]).'='.urlencode($param_key_value[1]).'&';
							} else {
								$current_url .= urlencode($param_key_value[0]).'&';
							}
						}
						break;
					}
					$current_url = rtrim($current_url,'&');
					$current_url = $current_url_exploded[0].'?'.$current_url;
				}
			}
		}
		$return_url = $current_url;
		$return_url = (strstr($return_url,'?mobile=1') !== false ? str_replace('?mobile=1','',$return_url) : str_replace('&mobile=1','',$return_url));
		$return_url = $return_url.(strstr($return_url,'?') !== false ? '&' : '?') . 'non_mobile=1';
		echo '<a rel="external" href="'.($this->forceMobileUrl != '' ? $this->forceMobileUrl : $return_url).'" data-role="button" data-icon="back">'.BFText::_('COM_BREEZINGFORMS_DESKTOP').'</a>';
		echo '</div>';

		echo '<div data-role="content" class="ui-content ui-body-a" role="main">';

		$this->process($this->dataObject);
		echo '</div>'."\n"; // closing last page

		if($this->hasFlashUpload){
			echo '<input type="hidden" name="bfFlashUploadTicket" value="'.$this->flashUploadTicket.'"/>'."\n";
			echo "<div style=\"visibility:hidden;\" id=\"bfFileQueue\"></div>";
			echo "<div style=\"visibility:hidden;\" id=\"bfSubmitMessage\">".BFText::_('COM_BREEZINGFORMS_SUBMIT_MESSAGE')."</div>";
		}

		echo '<noscript>Please turn on javascript to submit your data. Thank you!</noscript>'."\n";

		echo '</div>';

		echo '</div>';

		echo '<input type="hidden" name="tmpl" value="component"/>';

	}

	public function process(&$dataObject, $parent = null, $parentPage = null, $index = 0, $childrenLength = 0){
		if(isset($dataObject['attributes']) && isset($dataObject['properties']) ){
			
			$options = array('type' => 'normal', 'displayType' => 'breaks');
			if($parent != null && $parent['type'] == 'section'){
				$options['type'] = $parent['bfType'];
				$options['displayType'] = $parent['displayType'];
			}
			
			$class = ' class="bfBlock"';
			$wrapper = 'bfWrapperBlock';
			if($options['displayType'] == 'inline'){
				$class = ' class="bfInline"';
				$wrapper = 'bfWrapperInline';
			}
			
			$mdata = $dataObject['properties'];
			
			if($mdata['type'] == 'page'){
				
				$parentPage = $mdata;
				if($parentPage['pageNumber'] > 1){
					echo '</div><!-- bfPage end -->'."\n"; // closing previous pages
				}
				// Tarifrechner
				$requestedpage=$this->p->page;
				
				$display = ' style="display:none;"';
                                if(JRequest::getInt('ff_form_submitted',0) == 0 && JRequest::getInt('ff_page',1) == $parentPage['pageNumber']){
                                    $display = '';
                                } else if( JRequest::getInt('ff_form_submitted',0) == 1 && $this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == count($this->dataObject['children']) ){
                                    $display = '';
                                } else if(JRequest::getInt('ff_form_submitted',0) == 1 && false == $this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == $requestedpage){
                                    $display = '';
                                }
                                
				echo '<div id="bfPage'.$parentPage['pageNumber'].'" class="bfPage"'.$display.'>'."\n"; // opening current page
				
                                /* translatables */
                                if(isset($mdata['pageIntro_translation'.$this->language_tag]) && $mdata['pageIntro_translation'.$this->language_tag] != ''){
                                    $mdata['pageIntro'] = $mdata['pageIntro_translation'.$this->language_tag];
                                }
                                /* translatables end */
                                
				if(trim($mdata['pageIntro'])!=''){
					
                                        echo '<div class="bfPageIntro">'."\n";
                                        
                                        $regex		= '/{loadposition\s+(.*?)}/i';
                                        $introtext = $mdata['pageIntro'];
                                        
                                        preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);
                                        
                                        jimport('joomla.version');
                                        $version = new JVersion();
                                        
                                        if ($matches && version_compare($version->getShortVersion(), '1.6', '>=')) {
                                            
                                            $document	= JFactory::getDocument();
                                            $renderer	= $document->loadRenderer('modules');
                                            $options	= array('style' => 'xhtml');
                                            
                                            foreach ($matches as $match) {
                                        
                                                $matcheslist =  explode(',', $match[1]);
                                                $position = trim($matcheslist[0]);
                                                $output = $renderer->render($position, $options, null);
                                                $introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
                                            }
                                        }
                                        
                                        echo $introtext."\n";
                                        
					echo '</div><div style="padding-bottom: 10px;"></div>'."\n";
				}
				
				if(!$this->useErrorAlerts){
					echo '<span class="bfErrorMessage" style="color: red; display:none;"></span>'."\n";
				}
				
			} else if($mdata['type'] == 'section'){

				if(isset($dataObject['properties']['name']) && isset($mdata['off']) && $mdata['off']){
					echo '<script type="text/javascript"><!--'."\n".'bfDeactivateSection.push("'.$dataObject['properties']['name'].'");'."\n".'//--></script>'."\n";
				}
				
                                /* translatables */
                                if(isset($mdata['title_translation'.$this->language_tag]) && $mdata['title_translation'.$this->language_tag] != ''){
                                    $mdata['title'] = $mdata['title_translation'.$this->language_tag];
                                }
                                /* translatables end */
                                
				if($mdata['bfType'] == 'section'){
					echo '<div data-theme="a" data-role="collapsible-set"'.(isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '').(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="'.$dataObject['properties']['name'].'"' : '').'><div data-role="collapsible" data-collapsed="false">'."\n";
					if(trim($mdata['title']) != ''){
						echo '<h3>'.htmlentities(trim($mdata['title']), ENT_QUOTES, 'UTF-8').'</h3>'."\n";
					}
				} 
				else if( $mdata['bfType'] == 'normal' ) {
					if(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != ''){
						echo '<div '.(isset($mdata['off']) && $mdata['off'] ? 'style="display:none" ' : '').'class="bfNoSection"'.(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="'.$dataObject['properties']['name'].'"' : '').'>'."\n";
					}
				}
                                
                                /* translatables */
                                if(isset($mdata['description_translation'.$this->language_tag]) && $mdata['description_translation'.$this->language_tag] != ''){
                                    $mdata['description'] = $mdata['description_translation'.$this->language_tag];
                                }
                                /* translatables end */
				
				if(trim($mdata['description'])!=''){
					echo '<div>'."\n";
                                        
                                        $regex		= '/{loadposition\s+(.*?)}/i';
                                        $introtext = $mdata['description'];
                                        
                                        preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);
                                        
                                        jimport('joomla.version');
                                        $version = new JVersion();
                                        
                                        if ($matches && version_compare($version->getShortVersion(), '1.6', '>=')) {
                                            
                                            $document	= JFactory::getDocument();
                                            $renderer	= $document->loadRenderer('modules');
                                            $options	= array('style' => 'xhtml');
                                            
                                            foreach ($matches as $match) {
                                        
                                                $matcheslist =  explode(',', $match[1]);
                                                $position = trim($matcheslist[0]);
                                                $output = $renderer->render($position, $options, null);
                                                $introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
                                            }
                                        }
                                        
					echo $introtext."\n";
					echo '</div><div style="padding-bottom: 10px;"></div>'."\n";
				}
				
			} else if($mdata['type'] == 'element'){
				
                                //echo '<div class="bfElemWrap"'.(isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '').'>';
                            
                                // if labels left 
                                if( true ) {
                                    echo '<div'.(isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '').' id="fieldcontain'.$mdata['bfName'].'" class="bfElemWrap" data-role="fieldcontain">';
                                }
                                
				$onclick = '';
				if($mdata['actionClick'] == 1){
					$onclick = 'onclick="'.$mdata['actionFunctionName'] . '(this,\'click\');" ';	
				}
				
				$onblur = '';
				if($mdata['actionBlur'] == 1){
					$onblur = 'onblur="'.$mdata['actionFunctionName'] . '(this,\'blur\');" ';	
				}
				
				$onchange = '';
				if($mdata['actionChange'] == 1){
					$onchange = 'onchange="'.$mdata['actionFunctionName'] . '(this,\'change\');" ';	
				}
				
				$onfocus = '';
				if($mdata['actionFocus'] == 1){
					$onfocus = 'onfocus="'.$mdata['actionFunctionName'] . '(this,\'focus\');" ';	
				}
				
				$onselect = '';
				if(isset($mdata['actionSelect']) && $mdata['actionSelect'] == 1){
					$onselect = 'onselect="'.$mdata['actionFunctionName'] . '(this,\'select\');" ';	
				}
				
                                $legend = '';
                                
				if(!$mdata['hideLabel'] && $mdata['bfType'] != 'bfPayPal' && $mdata['bfType'] != 'bfSofortueberweisung'){
					
					$maxlengthCounter = '';
					if($mdata['bfType'] == 'bfTextarea' && isset($mdata['maxlength']) && $mdata['maxlength'] > 0 && isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter']){
						$maxlengthCounter = ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter'.$mdata['dbId'].'***>('.$mdata['maxlength'].' '.BFText::_('COM_BREEZINGFORMS_CHARS_LEFT').')</span>';
					}

                                        $for = '';
                                        if( $mdata['bfType'] == 'bfTextfield' ||
                                            $mdata['bfType'] == 'bfTextarea' ||
                                            $mdata['bfType'] == 'bfCheckbox' ||
                                            $mdata['bfType'] == 'bfCheckboxGroup' ||
                                            $mdata['bfType'] == 'bfCalendar' ||
                                            $mdata['bfType'] == 'bfCalendarReponsive' ||
                                            $mdata['bfType'] == 'bfSelect' ||
                                            $mdata['bfType'] == 'bfRadioGroup' || 
                                            ( $mdata['bfType'] == 'bfFile' && ( ( !isset( $mdata['flashUploader'] ) && !isset( $mdata['html5']) ) || ( isset( $mdata['flashUploader'] ) && !$mdata['flashUploader'] ) && ( isset( $mdata['html5']) && !$mdata['html5'] ) ) ) ){
                                            $for = 'for="ff_elem'.$mdata['dbId'].'"';
                                        }
                                        
                                        if($mdata['bfType'] == 'bfCaptcha' || $mdata['bfType'] == 'bfReCaptcha'){
                                            $for = 'for="bfCaptchaEntry"';
                                        }
                                        else if($mdata['bfType'] == 'bfReCaptcha'){
                                            $for = 'for="recaptcha_response_field"';
                                        }

                                        $req = '';
                                        if($mdata['required']){
                                            $req = '<span class="bfRequired"> * </span>'."\n";
                                        }
                                        
                                        /* translatables */
                                        if(isset($mdata['label_translation'.$this->language_tag]) && $mdata['label_translation'.$this->language_tag] != ''){
                                            $mdata['label'] = $mdata['label_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
                                        
                                        $labelText = trim($mdata['label']) . $req . str_replace("***","\"",$maxlengthCounter);
                                        
                                        if( true && ( $mdata['bfType'] == 'bfCheckboxGroup' || $mdata['bfType'] == 'bfRadioGroup' ) ){
                                            $legend = '<legend id="bfLabel'.$mdata['dbId'].'">'.str_replace("***","\"",$labelText).'</legend>'."\n";
                                        } else if( $mdata['bfType'] == 'bfSummarize' ){
                                            $legend = $labelText;
                                        }else {
                                            echo '<label id="bfLabel'.$mdata['dbId'].'" '.$for.'>'.str_replace("***","\"",$labelText).'</label>'."\n";
                                        }
				}
				
				$readonly = '';
				if(isset($mdata['readonly']) && $mdata['readonly']){
					$readonly = 'readonly="readonly" ';
				}
				
				$tabIndex = '';
				if($mdata['tabIndex'] != -1 && is_numeric($mdata['tabIndex'])){
					$tabIndex = 'tabindex="'.intval($mdata['tabIndex']).'" ';
				}
			
				for($i = 0; $i < $this->p->rowcount; $i++) {
					$row = $this->p->rows[$i];
					if($mdata['bfName'] == $row->name){
						if( ( isset($mdata['value']) || isset($mdata['list']) || isset($mdata['group']))
							&& 
							( 
								$mdata['bfType'] == 'bfTextfield' ||
								$mdata['bfType'] == 'bfTextarea' ||
								$mdata['bfType'] == 'bfCheckbox' ||
								$mdata['bfType'] == 'bfCheckboxGroup' ||
								$mdata['bfType'] == 'bfSubmitButton' ||
								$mdata['bfType'] == 'bfHidden' ||
								$mdata['bfType'] == 'bfCalendar' ||
                                                                $mdata['bfType'] == 'bfCalendarResponsive' ||
								$mdata['bfType'] == 'bfSelect' ||
								$mdata['bfType'] == 'bfRadioGroup'
							)
						){
							$trans_value = '';
							if (isset($mdata['value_translation' . $this->language_tag]) && $mdata['value_translation' . $this->language_tag] != '') {
								$trans_value = $mdata['value_translation' . $this->language_tag];
							}

							$group_value = '';
							if (isset($mdata['group_translation' . $this->language_tag]) && $mdata['group_translation' . $this->language_tag] != '') {
								$group_value = $mdata['group_translation' . $this->language_tag];
							}

							$list_value = '';
							if (isset($mdata['list_translation' . $this->language_tag]) && $mdata['list_translation' . $this->language_tag] != '') {
								$list_value = $mdata['list_translation' . $this->language_tag];
							}

							if($mdata['bfType'] == 'bfSelect')
							{
								$mdata['list'] = $this->p->replaceCode($list_value ? $list_value : $row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							} 
							else if($mdata['bfType'] == 'bfCheckboxGroup' || $mdata['bfType'] == 'bfRadioGroup')
							{
								$mdata['group'] = $this->p->replaceCode($group_value ? $group_value : $row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							} 
							else
							{
								$mdata['value'] = $this->p->replaceCode($trans_value ? $trans_value : $row->data1, "data1 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							}
						}
						if(isset($mdata['checked']) && $mdata['bfType'] == 'bfCheckbox'){
							$mdata['checked'] = $row->flag1 == 1 ? true : false;
						}
						break;
					}
				}
                                
                                $flashUploader = '';
				
				switch($mdata['bfType']){
					
					case 'bfTextfield':
						$type = 'text';
						
						if($mdata['password']){
							$type = 'password';
						}
						$maxlength = '';
						if(is_numeric($mdata['maxLength'])){
							$maxlength = 'maxlength="'.intval($mdata['maxLength']).'" ';
						}
                                                
                                                /* translatables */
						//if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
						//    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
						//}
                                                
                                                if(isset($mdata['placeholder_translation'.$this->language_tag]) && $mdata['placeholder_translation'.$this->language_tag] != ''){
                                                    $mdata['placeholder'] = $mdata['placeholder_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
						
						echo '<input '.(isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="'.htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8').'" ' : '').'class="ff_elem" '.$tabIndex.$maxlength.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						if($mdata['mailbackAsSender']){
							echo '<input type="hidden" name="mailbackSender['.$mdata['bfName'].']" value="true"/>'."\n";
						}
						break;
						
					case 'bfTextarea':
                                                $width = '';
						if($mdata['width']!=''){
							$width = 'width:'.htmlentities(strip_tags($mdata['width'])).' !important; min-width:'.htmlentities(strip_tags($mdata['width'])).' !important;';
						}
						$height = '';
						if($mdata['height']!=''){
							$height = 'height:'.htmlentities(strip_tags($mdata['height'])).';';
						}
						$size = '';
						if($height != '' || $width != ''){
							$size = 'style="'.$width.$height.'" ';
						}
                                                $onkeyup = '';
						if(isset($mdata['maxlength']) && $mdata['maxlength'] > 0){
							$onkeyup = 'onkeyup="bfCheckMaxlength('.intval($mdata['dbId']).', '.intval($mdata['maxlength']).', '.(isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter'] ? 'true' : 'false').')" ';	
						}
                                                /* translatables */
                                                if(isset($mdata['placeholder_translation'.$this->language_tag]) && $mdata['placeholder_translation'.$this->language_tag] != ''){
                                                    $mdata['placeholder'] = $mdata['placeholder_translation'.$this->language_tag];
                                                }
						//if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
						//    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
						//}
                                                /* translatables end */
						echo '<textarea '.(isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="'.htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8').'" ' : '').'cols="20" rows="5" class="ff_elem" '.$onkeyup.$size.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'">'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'</textarea>'."\n";
						break;
						
					case 'bfRadioGroup':
						/* translatables */
						//if(isset($mdata['group_translation'.$this->language_tag]) && $mdata['group_translation'.$this->language_tag] != ''){
						//    $mdata['group'] = $mdata['group_translation'.$this->language_tag];
						//}
                                                /* translatables end */
						if($mdata['group'] != ''){
                                                    
                                                        $mdata['group'] = str_replace("\r", '', $mdata['group']);
							$gEx = explode("\n", $mdata['group']);
							$lines = count($gEx);
                                                    
							$wrapOpen = '<div data-role="fieldcontain">'."\n".'<fieldset '.($lines <= 3 ? 'data-type="horizontal" ' : '').'data-role="controlgroup">'.$legend."\n";
							$wrapClose = '</fieldset>'."\n".'</div>'."\n";
							
							echo $wrapOpen;
							for($i = 0; $i < $lines; $i++){
								$idExt = $i != 0 ? '_'.$i : '';
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if($iCnt < 3)
									continue;
								if($iCnt == 3){
									$lblRight = '<label class="bfGroupLabel" id="bfGroupLabel'.$mdata['dbId'].$idExt.'" for="ff_elem'.$mdata['dbId'].$idExt.'">'.trim($iEx[1]).'</label>';
									echo '<input '.($iEx[0] == 1 ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="radio" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>'.$lblRight."\n";
								}
								else{
									require(JPATH_SITE.'/media/breezingforms/tarifrechner/'.trim($iEx[3]).'mobile.php');
								}				
							}
							echo $wrapClose;
						}
						
						break;
						
					case 'bfCheckboxGroup':
						/* translatables */
						//if(isset($mdata['group_translation'.$this->language_tag]) && $mdata['group_translation'.$this->language_tag] != ''){
						//    $mdata['group'] = $mdata['group_translation'.$this->language_tag];
						//}
                                                /* translatables end */
						if($mdata['group'] != ''){
                                                    
                                                        $mdata['group'] = str_replace("\r", '', $mdata['group']);
							$gEx = explode("\n", $mdata['group']);
							$lines = count($gEx);
                                                    
                                                        $wrapOpen = '<div data-role="fieldcontain">'."\n".'<fieldset data-role="controlgroup">'.$legend."\n";
							$wrapClose = '</fieldset>'."\n".'</div>'."\n";
							
							echo $wrapOpen;
							for($i = 0; $i < $lines; $i++){
								$idExt = $i != 0 ? '_'.$i : '';
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if($iCnt == 3){
									$lbl = '<label class="bfGroupLabel" id="bfGroupLabel'.$mdata['dbId'].$idExt.'" for="ff_elem'.$mdata['dbId'].$idExt.'">'.trim($iEx[1]).'</label>';
                                                                        echo '<input '.($iEx[0] == 1 ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="checkbox" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>'.$lbl."\n";
                                                                }
							}
							echo $wrapClose;
                                                         
                                                }
						
						break;
					
					case 'bfCheckbox':
						
                                                echo '<input class="ff_elem" '.($mdata['checked'] ? 'checked="checked" ' : '').$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="checkbox" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						if($mdata['mailbackAccept']){
							echo '<input type="hidden" class="ff_elem" name="mailbackConnectWith['.$mdata['mailbackConnectWith'].']" value="true_'.$mdata['bfName'].'"/>'."\n";
						}
						break;
						
					case 'bfSelect':
						/* translatables */
						//if(isset($mdata['list_translation'.$this->language_tag]) && $mdata['list_translation'.$this->language_tag] != ''){
						//    $mdata['list'] = $mdata['list_translation'.$this->language_tag];
						//}
                                                /* translatables end */
						if($mdata['list'] != ''){
							
							$mdata['list'] = str_replace("\r", '', $mdata['list']);
							$gEx = explode("\n", $mdata['list']);
							$lines = count($gEx);
                                                        // data-native-menu="false" 
							echo '<select class="ff_elem" '.($mdata['multiple'] ? 'multiple="multiple" data-native-menu="false" ' : '').$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'">'."\n";
							for($i = 0; $i < $lines; $i++){
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if($iCnt == 3){
									echo '<option '.($iEx[0] == 1 ? 'selected="selected" ' : '').'value="'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8').'">'.htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8').'</option>'."\n";
								}
							}
							echo '</select>'."\n";
						}
						
						break;
						
					case 'bfFile':
                                                if( ( isset( $mdata['flashUploader'] ) && $mdata['flashUploader'] ) || ( isset( $mdata['html5'] ) && $mdata['html5'] ) ){
                                                        
                                                        $base = explode('/',JURI::base());
                                                        if(isset($base[count($base)-2]) && $base[count($base)-2] == 'administrator'){
                                                            unset($base[count($base)-2]);
                                                            $base = array_merge($base);
                                                        }
                                                        $base = implode('/', $base);
                                                    
                                                        echo '<input type="hidden" id="flashUpload'.$mdata['bfName'].'" name="flashUpload'.$mdata['bfName'].'" value="bfFlashFileQueue'.$mdata['dbId'].'"/>'."\n";
							$this->hasFlashUpload = true;
							//allowedFileExtensions
							$allowedExts = explode(',',$mdata['allowedFileExtensions']);
							$allowedExtsCnt = count($allowedExts);
							for($i = 0; $i < $allowedExtsCnt;$i++){
								$allowedExts[$i] = $allowedExts[$i];
							}
							$exts = '';
							if($allowedExtsCnt != 0){
								$exts = implode(',',$allowedExts);
							}
                                                        $bytes = (isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? "max_file_size : '" . intval($mdata['flashUploaderBytes']) ."'," : '');
							$flashUploader = "
                                                        <span id=\"bfUploadContainer".$mdata['dbId']."\">
							<img style=\"cursor: pointer;\" id=\"bfPickFiles".$mdata['dbId']."\" src=\"".$this->uploadImagePath."\" border=\"0\" width=\"".(isset($mdata['flashUploaderWidth']) && is_numeric($mdata['flashUploaderWidth']) && $mdata['flashUploaderWidth'] > 0 ? intval($mdata['flashUploaderWidth']) : '64')."\" height=\"".(isset($mdata['flashUploaderHeight']) && is_numeric($mdata['flashUploaderHeight']) && $mdata['flashUploaderHeight'] > 0 ? intval($mdata['flashUploaderHeight']) : '64')."\"/>
                                                            <div id=\"bfPickFiles".$mdata['dbId']."holder\" style=\"display:none;\">&nbsp;</div>
                                                        </span>
                                                        <span id=\"bfUploader".$mdata['bfName']."\"></span>
                                                        <div class=\"bfFlashFileQueueClass\" id=\"bfFlashFileQueue".$mdata['dbId']."\"></div>
                                                        <script type=\"text/javascript\">
                                                        <!--
							bfFlashUploaders.push('ff_elem".$mdata['dbId']."');
                                                        var bfFlashFileQueue".$mdata['dbId']." = {};
                                                        function bfUploadImageThumb(file) {
                                                                var img;
                                                                img = new ctplupload.Image;
                                                                img.onload = function() {
                                                                        img.embed(JQuery('#' + file.id+'thumb').get(0), { 
                                                                                width: 100, 
                                                                                height: 60, 
                                                                                crop: true,
                                                                                swf_url: mOxie.resolveUrl('".$base."components/com_breezingforms/libraries/jquery/plupload/Moxie.swf')
                                                                        });
                                                                };

                                                                img.onembedded = function() {
                                                                        img.destroy();
                                                                };

                                                                img.onerror = function() {
                                                                        
                                                                };
                                                                
                                                                img.load(file.getSource());
                                                                
                                                        }
                                                        JQuery(document).ready(
                                                            function() {
                                                                var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/i) ? true : false );
                                                                var uploader = new plupload.Uploader({
                                                                        multi_selection: ".( isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false' ).",
                                                                        unique_names: iOS,
                                                                        chunk_size: '10kb',
                                                                        runtimes : '".( isset( $mdata['html5'] ) && $mdata['html5'] ? 'html5,' : '' ).( isset( $mdata['flashUploader'] ) && $mdata['flashUploader'] ? 'flash,' : '')."html4',
                                                                        browse_button : 'bfPickFiles".$mdata['dbId']."',
                                                                        container: 'bfUploadContainer".$mdata['dbId']."',
                                                                        file_data_name: 'Filedata',
                                                                        multipart_params: { form: ".$this->p->form.", itemName : '".$mdata['bfName']."', bfFlashUploadTicket: '".$this->flashUploadTicket."', option: 'com_breezingforms', format: 'html', flashUpload: 'true', Itemid: 0 },
                                                                        url : '".$base.(BFJoomlaConfig::get('config.sef') && !BFJoomlaConfig::get('config.sef_rewrite') ? 'index.php/' : '').(JRequest::getCmd('lang','') && BFJoomlaConfig::get('config.sef') ? JRequest::getCmd('lang','') . ( BFJoomlaConfig::get('config.sef_rewrite') ? 'index.php' : '' ) : 'index.php')."',
                                                                        flash_swf_url : '".$base."components/com_breezingforms/libraries/jquery/plupload/Moxie.swf',
                                                                        filters : [
                                                                                {title : '".addslashes(BFText::_('COM_BREEZINGFORMS_CHOOSE_FILE'))."', extensions : '".$exts."'}
                                                                        ]
                                                                });
                                                                uploader.bind('FilesAdded', function(up, files) {
                                                                        for (var i in files) {
                                                                                if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                    var fsize = '';
                                                                                    if(typeof files[i].size != 'undefined'){
                                                                                        fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                    }
                                                                                    if(typeof bfUploadFileAdded == 'function'){
                                                                                        bfUploadFileAdded(files[i]);
                                                                                    }
                                                                                    JQuery('#bfFileQueue').append( '<div id=\"' + files[i].id + 'queue\">' + (iOS ? '' : files[i].name) + ' '+fsize+'<b></b></div>' );
                                                                                }
                                                                        }
                                                                        for (var i in files) {
                                                                            if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                var error = false;
                                                                                var fsize = '';
                                                                                if(typeof files[i].size != 'undefined'){
                                                                                    fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                }
                                                                                JQuery('#bfFlashFileQueue".$mdata['dbId']."').append('<div class=\"bfFileQueueItem\" id=\"' + files[i].id + 'queueitem\"><div id=\"' + files[i].id + 'thumb\"></div><div id=\"' + files[i].id + '\"><img id=\"' + files[i].id + 'cancel\" src=\"".$this->cancelImagePath."\" style=\"cursor: pointer; padding-right: 10px;\" border=\"0\"/>' + (iOS ? '' : files[i].name) + ' ' + fsize + '<b id=\"' + files[i].id + 'msg\" style=\"color:red;\"></b></div></div>');
                                                                                var file_ = files[i];
                                                                                var uploader_ = uploader;
                                                                                var bfUploaders_ = bfUploaders;
                                                                                JQuery('#' + files[i].id + 'cancel').click( 
                                                                                    function(){
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].stop();
                                                                                        }
                                                                                        var id_ = this.id.split('cancel');
                                                                                        id_ = id_[0];
                                                                                        uploader_.removeFileById(id_);
                                                                                        JQuery('#'+id_+'queue').remove();
                                                                                        JQuery('#'+id_+'queueitem').remove();
                                                                                        bfFlashUploadersLength--;
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].start();
                                                                                        }
                                                                                        // re-enable button if there is none left
                                                                                        if( ".( isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false' )." == false ){
                                                                                            var the_size = JQuery('#bfFlashFileQueue".$mdata['dbId']." .bfFileQueueItem').size();
                                                                                            if( the_size == 0 ){
                                                                                                JQuery('#bfPickFiles".$mdata['dbId']."').css('display','block');
                                                                                                JQuery('#bfPickFiles".$mdata['dbId']."holder').css('display','none');
                                                                                            }
                                                                                        }
                                                                                    } 
                                                                                );
                                                                                var thebytes = ".(isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? intval($mdata['flashUploaderBytes']) : '0').";
                                                                                if(thebytes > 0 && typeof files[i].size != 'undefined' && files[i].size > thebytes){
                                                                                     alert(' ".addslashes(BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_TOO_LARGE'))."');
                                                                                     error = true;
                                                                                }
                                                                                var ext = files[i].name.split('.').pop().toLowerCase();
                                                                                var exts = '".strtolower($exts)."'.split(',');
                                                                                var found = 0;
                                                                                for (var x in exts){
                                                                                    if(exts[x] == ext){
                                                                                        found++;
                                                                                    }
                                                                                }
                                                                                if(found == 0){
                                                                                    alert( ' ".addslashes(BFText::_('COM_BREEZINGFORMS_FILE_EXTENSION_NOT_ALLOWED'))."' );
                                                                                    error = true;
                                                                                }
                                                                                if(error){
                                                                                    JQuery('#'+files[i].id+'queue').remove();
                                                                                    JQuery('#'+files[i].id+'queueitem').remove();
                                                                                }else{
                                                                                    bfFlashUploadersLength++;
                                                                                }
                                                                                bfUploadImageThumb(files[i]);
                                                                            }
                                                                        }
                                                                        // disable the button if no multi upload
                                                                        if( ".( isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false' )." == false ){
                                                                            var the_size = JQuery('#bfFlashFileQueue".$mdata['dbId']." .bfFileQueueItem').size();
                                                                            if( the_size > 0 ){
                                                                                JQuery('#bfPickFiles".$mdata['dbId']."').css('display','none');
                                                                                JQuery('#bfPickFiles".$mdata['dbId']."holder').css('display','block');
                                                                            }
                                                                        }
                                                                });
                                                                uploader.bind('UploadProgress', function(up, file) {
                                                                    if(typeof JQuery('#'+file.id+'queue').get(0) != 'undefined'){
                                                                        JQuery('#'+file.id+'queue').get(0).getElementsByTagName('b')[0].innerHTML = file.percent + '% <div style=\"height: 5px;width: ' + (file.percent*1.5) + 'px;background-color: #9de24f;\"></div>';
                                                                    }
                                                                });
                                                                uploader.bind('FileUploaded', function(up, file, response) {
                                                                    if(response.response!=''){
                                                                        if(response.response !== null){
                                                                            alert(response.response);
                                                                        }
                                                                    }
                                                                    JQuery('#'+file.id+'queue').remove();
                                                                });
                                                                uploader.init();
                                                                bfUploaders.push(uploader);
                                                            });
							//-->
                                                        </script>
							";
                                                        // on mobiles, file uploads are forced not to be mandatory, since we cannot determin safely for all handsets if they are even allowed
                                                        echo '<script type="text/javascript"><!--'."\n".'var bfIsValidMobile = ( navigator.userAgent.match(/(iPad|iPhone|iPod|Android)/i) ? true : false )'."\n".'if(!bfIsValidMobile){bfDeactivateField["ff_nm_'.$mdata['bfName'].'[]"]=true;}'."\n".'//--></script>'."\n";
                                                
                                                        echo '<input class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="hidden" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
							echo '<div style="clear: both;"></div>';
						}else{
                                                    echo '<input class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="file" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						}
						if($mdata['attachToAdminMail']){
							echo '<input type="hidden" name="attachToAdminMail['.$mdata['bfName'].']" value="true"/>'."\n";
						}
						if($mdata['attachToUserMail']){
							echo '<input type="hidden" name="attachToUserMail['.$mdata['bfName'].']" value="true"/>'."\n";
						}
						break;
                                                
                                                // on mobiles, file uploads are forced not to be mandatory, since we cannot determin safely for all handsets if they are even allowed
                                                echo '<script type="text/javascript"><!--'."\n".'bfDeactivateField["ff_nm_'.$mdata['bfName'].'[]"]=true;'."\n".'//--></script>'."\n";
                                                
						break;
						
					case 'bfSubmitButton':
						
                                                /* translatables */
                                                if(isset($mdata['src_translation'.$this->language_tag]) && $mdata['src_translation'.$this->language_tag] != ''){
                                                    $mdata['src'] = $mdata['src_translation'.$this->language_tag];
                                                }
						//if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
						//    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
						//}
                                                /* translatables end */
                                            
						$value = '';
						$type = 'submit';
						$src = '';
                                                
						if($mdata['src'] != ''){
							$type = 'image';
							$src = 'src="'.$mdata['src'].'" ';
						}
						if($mdata['value'] != ''){
							$value = 'value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" ';
						}
						if($mdata['actionClick'] == 1){
							$onclick = 'onclick="populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};'.$mdata['actionFunctionName'] . '(this,\'click\');return false;" ';
						} else {
							$onclick = 'onclick="populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};return false;" ';
						}
                                                if($src == ''){
                                                    echo '<button data-theme="a" class="ff_elem bfCustomSubmitButton" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"><span>'.$mdata['value'].'</span></button>'."\n";
                                                }else{
                                                    echo '<input class="ff_elem bfCustomSubmitButton" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'" value="'.$mdata['value'].'"/>'."\n";
                                                }
						break;
						
					case 'bfHidden':
						
						echo '<input class="ff_elem" type="hidden" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						break;
						
					case 'bfSummarize':
						
                                                /* translatables */
                                                if(isset($mdata['emptyMessage_translation'.$this->language_tag]) && $mdata['emptyMessage_translation'.$this->language_tag] != ''){
                                                    $mdata['emptyMessage'] = $mdata['emptyMessage_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                            
                                                echo '<div class="ui-grid-a">
                                                            <div class="ui-block-a"><strong>'.$legend.'</strong></div>
                                                            <div class="ui-block-b ff_elem bfSummarize" id="ff_elem'.$mdata['dbId'].'"></div>
                                                    </div>';
                                            	echo '<script type="text/javascript"><!--'."\n".'bfRegisterSummarize("ff_elem'.$mdata['dbId'].'", "'.$mdata['connectWith'].'", "'.$mdata['connectType'].'", "'.addslashes($mdata['emptyMessage']).'", '.($mdata['hideIfEmpty'] ? 'true' : 'false').')'."\n".'//--></script>';
						if(trim($mdata['fieldCalc']) != ''){
							echo '<script type="text/javascript">
                                                        <!--
							function bfFieldCalcff_elem'.$mdata['dbId'].'(value){
								if(!isNaN(value)){
									value = Number(value);
								}
								'.$mdata['fieldCalc'].'
								return value;
							}
                                                        //-->
							</script>';
						}
						break;
                                        
                                        case 'bfReCaptcha':

                                            if(isset($mdata['pubkey']) && $mdata['pubkey'] != ''){

                                                if(isset($mdata['newCaptcha']) && $mdata['newCaptcha']){
                                                    
                                                    $http = 'http';
                                                    if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
                                                        $http .= 's';
                                                    }
                                                    $lang = JRequest::getVar('lang','');
                                                    if($lang != ''){
                                                        $lang = ',lang: '.json_encode($lang).'';
                                                    }
                                                    
                                                    //JFactory::getDocument()->addScript($http.'://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit', $type = "text/javascript", true, true);
                                                    
                                                    echo '
                                                    <div style="display: inline-block !important; vertical-align: middle;">
                                                        <div id="newrecaptcha"></div>
                                                        <div class="g-recaptcha" data-sitekey="'.$mdata['pubkey'].'"></div>
                                                    </div>
                                                    <script type="text/javascript">
                                                    <!--
                                                    var onloadBFNewRecaptchaCallback = function() {
                                                      grecaptcha.render("newrecaptcha", {
                                                        "sitekey" : "'.$mdata['pubkey'].'",
                                                        "theme" : "'.(trim($mdata['theme']) == '' ? 'light' : trim($mdata['theme'])).'",
                                                      });
                                                    };
                                                    JQuery(document).ready(function(){
                                                        var rc_loaded = JQuery("script").filter(function () {
														    return ((typeof JQuery(this).attr("src") != "undefined" && JQuery(this).attr("src").indexOf("recaptcha\/api.js") > 0) ? true : false);
														}).length;
														
														if (rc_loaded === 0) {
															JQuery.getScript("'.$http.'://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit");
														}
                                                    });
                                                    -->
                                                  </script>';
                                                    
                                                } else {
                                                
                                                    if(strtolower(trim($mdata['theme'])) == 'custom'){

                                                        $http = 'http';

                                                        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
                                                            $http .= 's';
                                                        }
                                                        $lang = JRequest::getVar('lang','');
                                                        if($lang != ''){
                                                            $lang = ',lang: '.json_encode($lang).'';
                                                        }
                                                        JFactory::getDocument()->addScript($http.'://www.google.com/recaptcha/api/js/recaptcha.js');
                                                        echo '<div class="bfRecaptchaCustom" id="recaptcha_widget" style="display: inline-block !important;">

                                                            <div id="recaptcha_image"></div>

                                                            <div class="bfRecaptchaCustomResponseField" style="clear:both;">
                                                                <input type="text" value="" id="recaptcha_response_field" name="recaptcha_response_field" />
                                                            </div>

                                                            <div class="bfRecaptchaCustomControls" style="clear:both;">

                                                                <button type="button" class="button bfRecaptchaCustomRefresh" onclick="Recaptcha.reload()"><span>'.BFText::_('COM_BREEZINGFORMS_RC_REFRESH').'</span></button>
                                                                <button type="button" class="button recaptcha_only_if_image bfRecaptchaCustomAudio" onclick="Recaptcha.switch_type(\'audio\')"><span>'.BFText::_('COM_BREEZINGFORMS_RC_AUDIO').'</span></button>
                                                                <button type="button" class="button recaptcha_only_if_audio bfRecaptchaCustomPicture" onclick="Recaptcha.switch_type(\'image\')"><span>'.BFText::_('COM_BREEZINGFORMS_RC_PICTURE').'</span></button>
                                                                <button type="button" class="button bfRecaptchaCustomHelp" onclick="Recaptcha.showhelp()"><span>'.BFText::_('COM_BREEZINGFORMS_RC_HELP').'</span></button>

                                                            </div>

                                                        </div>
                                                        <script type="text/javascript">
                                                        <!--
                                                        Recaptcha.create("'.$mdata['pubkey'].'",
                                                            "bfReCaptchaDiv", {
                                                            theme: "custom",
                                                            custom_theme_widget: "recaptcha_widget"
                                                            '.$lang.'
                                                            }
                                                        );
                                                        -->
                                                        </script>'."\n";

                                                    }else{

                                                        $http = 'http';
                                                        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
                                                            $http .= 's';
                                                        }
                                                        $lang = JRequest::getVar('lang','');
                                                        if($lang != ''){
                                                            $lang = ',lang: '.json_encode($lang).'';
                                                        }
                                                        JFactory::getDocument()->addScript($http.'://www.google.com/recaptcha/api/js/recaptcha.js');
                                                        JFactory::getDocument()->addScriptDeclaration(
                                                        '   
                                                            JQuery(document).ready(
                                                                function() {
                                                                    document.getElementById("bfReCaptchaWrap").style.display = "";
                                                                    Recaptcha.create("'.$mdata['pubkey'].'",
                                                                        "bfReCaptchaDiv", {
                                                                        theme: '.json_encode($mdata['theme']).'
                                                                        '.$lang.'
                                                                        }
                                                                    );
                                                                    setTimeout("document.getElementById(\"bfReCaptchaSpan\").appendChild(document.getElementById(\"bfReCaptchaWrap\"))",100);
                                                                }
                                                            );
                                                        ');

                                                        echo '<span id="bfReCaptchaSpan" class="bfCaptcha">'."\n";
                                                        echo '</span>'."\n";

                                                    }
                                                
                                                }
                                            }
                                            else
                                            {
                                                echo '<span class="bfCaptcha">'."\n";
                                                echo 'WARNING: No public key given for ReCaptcha element!';
                                                echo '</span>'."\n";
                                            }
                                            break;
                                        case 'bfCaptcha':
                                            
                                                if(JFactory::getApplication()->isSite())
                                                {
                                                    $captcha_url = JURI::root(true).'/components/com_breezingforms/images/captcha/securimage_show.php';
                                                }
                                                else
                                                {
                                                    $captcha_url = JURI::root(true).'/administrator/components/com_breezingforms/images/captcha/securimage_show.php';
                                                }

                                                echo '<div class="ui-grid-a">';
                                                echo '<img alt="" border="0" width="230" id="ff_capimgValue" class="ff_capimg" src="'.$captcha_url.'"/><br/><br/>'."\n";

                                        
                                                echo '<input autocomplete="off" class="ff_elem" type="text" name="bfCaptchaEntry" id="bfCaptchaEntry" />'."\n";
						echo '<button data-role="button" data-icon="refresh" data-inline="true" data-iconpos="notext" data-theme="a" id="bfCaptchaReload" onclick="document.getElementById(\'bfCaptchaEntry\').value=\'\';document.getElementById(\'bfCaptchaEntry\').focus();document.getElementById(\'ff_capimgValue\').src = \''.$captcha_url.'?bfMathRandom=\' + Math.random(); return false"><span>Reload Captcha</span></button>';
                                                echo '</div>';
                                                break;
					
                                        case 'bfCalendar':
					
                                                /* translatables */
						//if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
						//    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
						//}
                                                if(isset($mdata['format_translation'.$this->language_tag]) && $mdata['format_translation'.$this->language_tag] != ''){
                                                    $mdata['format'] = $mdata['format_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                            
                                                JHTML::_( 'behavior.calendar' ); 
                                            
						$exploded = explode('::',trim($mdata['value']));
                                                
                                                $left = '';
                                                $right = '';
                                                if(count($exploded) == 2){
                                                    $left = trim($exploded[0]);
                                                    $right = trim($exploded[1]); 
                                                }else{
                                                    $right = trim($exploded[0]);
                                                }
                                                
                                                
						echo '<input autocomplete="off" class="ff_elem" type="text" name="ff_nm_'.$mdata['bfName'].'[]"  id="ff_elem'.$mdata['dbId'].'" value="'.htmlentities($left, ENT_QUOTES, 'UTF-8').'"/>'."\n";
                                                echo '<label for="ff_elem'.$mdata['dbId'].'_calendarButton"></label>';
                                                echo '<button data-theme="a" id="ff_elem'.$mdata['dbId'].'_calendarButton" type="submit" class="bfCalendar" value="'.htmlentities($right, ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities($right, ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
		
						
                                                echo '<script type="text/javascript">
                                                <!--
                                                JQuery(document).bind("pageinit", function() {
                                                Calendar.setup({
                                                        inputField     :    "ff_elem'.$mdata['dbId'].'",
                                                        ifFormat       :    "'.$mdata['format'].'",
                                                        button         :    "ff_elem'.$mdata['dbId'].'_calendarButton",
                                                        align          :    "Bl",
                                                        singleClick    :    true
                                                    });
                                                });
                                                //-->
                                                </script>'."\n";
                                                
						break;	
                                            
					case 'bfCalendarResponsive':
					
                                                /* translatables */
						//if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
						//    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
						//}
                                                if(isset($mdata['format_translation'.$this->language_tag]) && $mdata['format_translation'.$this->language_tag] != ''){
                                                    $mdata['format'] = $mdata['format_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                            
						$exploded = explode('::',trim($mdata['value']));
                                                
                                                $left = '';
                                                $right = '';
                                                if(count($exploded) == 2){
                                                    $left = trim($exploded[0]);
                                                    $right = trim($exploded[1]); 
                                                }else{
                                                    $right = trim($exploded[0]);
                                                }
                                                
                                                echo '<input autocomplete="off" class="ff_elem" type="text" name="ff_nm_'.$mdata['bfName'].'[]"  id="ff_elem'.$mdata['dbId'].'" value="'.htmlentities($left, ENT_QUOTES, 'UTF-8').'"/>'."\n";
                                                echo '<label for="ff_elem'.$mdata['dbId'].'_calendarButton"></label>';
                                                echo '<button data-theme="a" id="ff_elem'.$mdata['dbId'].'_calendarButton" type="submit" class="bfCalendar" value="'.htmlentities($right, ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities($right, ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
		
						echo '<script type="text/javascript">
                                                <!--
                                                JQuery(document).ready(function () {
                                                    JQuery("#ff_elem'.$mdata['dbId'].'_calendarButton").pickadate({
                                                        format: "'.$mdata['format'].'", 
                                                        selectYears: 60, 
                                                        selectMonths: true,
                                                        editable: true,
                                                        firstDay: 1,
                                                        onSet: function() {
                                                            JQuery("#ff_elem'.$mdata['dbId'].'").val(this.get("value"));
                                                        }
                                                    });
                                                });
                                                //-->
                                                </script>'."\n";
                                                
                                                $this->hasResponsiveDatePicker = true;
                                                
						break;

					case 'bfSignature':

						JFactory::getDocument()->addScript(Juri::root(true).'/components/com_breezingforms/libraries/js/signature.js');
						JFactory::getDocument()->addScriptDeclaration('
						var bf_signaturePad' . $mdata['dbId'] . ' = null;
						var bf_canvas' . $mdata['dbId'] . ' = null;
						
						function bf_resizeCanvas' . $mdata['dbId'] . 'Func() {
							
							if(arguments[0] !== false){
							
								var data = bf_signaturePad' . $mdata['dbId'] . '.toDataURL();
							
							}
							
						    var ratio =  Math.max(window.devicePixelRatio || 1, 1);
						    bf_canvas' . $mdata['dbId'] . '.width = bf_canvas' . $mdata['dbId'] . '.offsetWidth * ratio;
						    bf_canvas' . $mdata['dbId'] . '.height = bf_canvas' . $mdata['dbId'] . '.offsetHeight * ratio;
						    bf_canvas' . $mdata['dbId'] . '.getContext("2d").scale(ratio, ratio);
						    
						    if(arguments[0] !== false){
						    
						        bf_signaturePad' . $mdata['dbId'] . '.fromDataURL(data);
						        jQuery("#ff_elem' . $mdata['dbId'] . '").val(data.replace("data:image/png;base64,",""));
							}
						    
						    bf_signaturePad' . $mdata['dbId'] . ' = new SignaturePad(bf_canvas' . $mdata['dbId'] . ', {
							    backgroundColor: "rgb(255,255,255)",
							    penColor: "rgb(0,0,0)",
							    onEnd: function(){
							        var data = bf_signaturePad' . $mdata['dbId'] . '.toDataURL();
							        jQuery("#ff_elem' . $mdata['dbId'] . '").val(data.replace("data:image/png;base64,",""));
						}
							});
						}
						
						function bf_Signature' . $mdata['dbId'] . 'Reset(sig) {
							sig.clear();
							jQuery("#ff_elem' . $mdata['dbId'] . '").val("");
						}
						
						jQuery(document).ready(function(){
							bf_canvas' . $mdata['dbId'] . ' = document.querySelector("#bfSignature' . $mdata['dbId'] . ' canvas");
							
							// causes trouble as swipes will falsely triggered as resize events
							jQuery(window).on("resize", bf_resizeCanvas' . $mdata['dbId'] . 'Func);
							
							bf_resizeCanvas' . $mdata['dbId'] . 'Func(false);
							
							// make sure the canvas is resized if dimensions are zero
							setInterval(function(){
								if( bf_canvas' . $mdata['dbId'] . '.width == 0 && bf_canvas' . $mdata['dbId'] . '.height == 0 ){
									bf_resizeCanvas' . $mdata['dbId'] . 'Func(false);
							    }
							}, 500);
							
						});
						');

						echo '<div class="bfSignature" id="bfSignature' . $mdata['dbId'] . '"><div class="bfSignatureCanvasBorder"><canvas></canvas></div>'."\n";
						echo '<button onclick="bf_Signature' . $mdata['dbId'] . 'Reset(bf_signaturePad' . $mdata['dbId'] . ');" class="bfSignatureResetButton button btn btn-primary"><span>'.JText::_('COM_BREEZINGFORMS_SIGNATURE_RESET_BUTTON').'</span></button>'."\n";
						echo '</div>';
						echo '<input class="ff_elem" type="hidden" name="ff_nm_' . $mdata['bfName'] . '[]" value="" id="ff_elem' . $mdata['dbId'] . '"/>' . "\n";

						break;

					case 'bfStripe':

						/* translatables */
						if(isset($mdata['image_translation'.$this->language_tag]) && $mdata['image_translation'.$this->language_tag] != ''){
							$mdata['image'] = $mdata['image_translation'.$this->language_tag];
						}
						/* translatables end */

						$value = '';
						$type = 'submit';
						$src = '';
						if($mdata['image'] != ''){
							$type = 'image';
							$src = 'src="'.$mdata['image'].'" ';
						}else{
							$value = 'value="Stripe" ';
						}
						if($mdata['actionClick'] == 1){
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Stripe\';'.$mdata['actionFunctionName'] . '(this,\'click\');" ';
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Stripe\';" ';
						}
						echo '<div align="center"><input data-role="none" class="ff_elem" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/></div>'."\n";
						break;
						
					case 'bfPayPal':
						
                                                /* translatables */
                                                if(isset($mdata['image_translation'.$this->language_tag]) && $mdata['image_translation'.$this->language_tag] != ''){
                                                    $mdata['image'] = $mdata['image_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                            
						$value = '';
						$type = 'submit';
						$src = '';
						if($mdata['image'] != ''){
							$type = 'image';
							$src = 'src="'.$mdata['image'].'" ';
						}else{
							$value = 'value="PayPal" ';
						}
						if($mdata['actionClick'] == 1){
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';'.$mdata['actionFunctionName'] . '(this,\'click\');" ';	
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';" ';
						}
						echo '<div align="center"><input data-role="none" class="ff_elem" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/></div>'."\n";
						break;
						
					case 'bfSofortueberweisung':
						
                                                /* translatables */
                                                if(isset($mdata['image_translation'.$this->language_tag]) && $mdata['image_translation'.$this->language_tag] != ''){
                                                    $mdata['image'] = $mdata['image_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                            
						$value = '';
						$type = 'submit';
						$src = '';
						if($mdata['image'] != ''){
							$type = 'image';
							$src = 'src="'.$mdata['image'].'" ';
						}else{
							$value = 'value="Sofortueberweisung" ';
						}
						if($mdata['actionClick'] == 1){
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';'.$mdata['actionFunctionName'] . '(this,\'click\');" ';	
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';" ';
						}
						echo '<div align="center"><input data-role="none" class="ff_elem" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/></div>'."\n";
						break;
				}
                                
                                /* translatables */
                                if(isset($mdata['hint_translation'.$this->language_tag]) && $mdata['hint_translation'.$this->language_tag] != ''){
                                    $mdata['hint'] = $mdata['hint_translation'.$this->language_tag];
                                }
                                /* translatables end */
                                
                                if(trim($mdata['hint']) != ''){
                                    $labid = uniqid();
                                    echo '<div><button class="bfTooltipButton" data-theme="a" onclick="JQuery(\'.tooltip\').hide(\'fast\');JQuery(\'#'.$labid.'_tip\').show(\'fast\');" data-role="button" data-icon="info" data-inline="true" data-iconpos="notext" id="'.$labid.'">'.trim($mdata['hint']).'</button><span id="'.$labid.'_tip" class="tooltip">'.trim($mdata['hint']).'</span></div>';
                                }
				
				if(isset($mdata['bfName']) && isset($mdata['off']) && $mdata['off']){
					echo '<script type="text/javascript"><!--'."\n".'bfDeactivateField["ff_nm_'.$mdata['bfName'].'[]"]=true;'."\n".'//--></script>'."\n";
				}
				
				if($mdata['bfType'] == 'bfFile'){
                                    echo '<span id="ff_elem'.$mdata['dbId'].'_files"></span>';
                                }
                                
                                echo $flashUploader;
                                
                                // if labels left 
                                if( true ) {
                                    echo '</div>';
                                }
                                
                                //echo '</div>';
			}
		}

		/**
		 * Paging and wrapping of inline element containers
		 */
		if(isset($dataObject['children']) && count($dataObject['children']) != 0){
			$childrenAmount = count($dataObject['children']);
			for($i = 0; $i < $childrenAmount; $i++){
				$this->process( $dataObject['children'][$i], $mdata, $parentPage, $i, $childrenAmount );
			}
		}	
		
		if(isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'section'){
			
			echo '</div></div>'."\n";
			
		} else if(isset($dataObject['properties']) &&  $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'normal' ) {
			if(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != ''){
				echo '</div>'."\n";
			}
		}
		else if(isset($dataObject['properties']) && $dataObject['properties']['type'] == 'page'){

			$isLastPage = false;
			if($this->rootMdata['lastPageThankYou'] && $dataObject['properties']['pageNumber'] == count($this->dataObject['children']) && count($this->dataObject['children']) > 1){
				$isLastPage = true;
			}
			
			if(!$isLastPage){
			
				$last = 0;
				if($this->rootMdata['lastPageThankYou']){
					$last = 1;
				}
				
				if($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] > 1){
                                        /* translatables */
                                        if(isset($this->rootMdata['pagingPrevLabel_translation'.$this->language_tag]) && $this->rootMdata['pagingPrevLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['pagingPrevLabel'] = $this->rootMdata['pagingPrevLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button data-theme="a" class="bfPrevButton" type="submit" onclick="ff_validate_prevpage(this, \'click\');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="'.htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
	
				if($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] < count($this->dataObject['children']) - $last){
                                        /* translatables */
                                        if(isset($this->rootMdata['pagingNextLabel_translation'.$this->language_tag]) && $this->rootMdata['pagingNextLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['pagingNextLabel'] = $this->rootMdata['pagingNextLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button data-theme="a" class="bfNextButton" type="submit" onclick="ff_validate_nextpage(this, \'click\');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="'.htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
	
				if($this->rootMdata['cancelInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last){
                                        /* translatables */
                                        if(isset($this->rootMdata['cancelLabel_translation'.$this->language_tag]) && $this->rootMdata['cancelLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['cancelLabel'] = $this->rootMdata['cancelLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button data-theme="a" class="bfCancelButton" type="submit" onclick="ff_resetForm(this, \'click\');"  value="'.htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
				
				$callSubmit = 'ff_validate_submit(this, \'click\')';
				if( $this->hasFlashUpload ){
					$callSubmit = 'if(typeof bfAjaxObject101 == \'undefined\' && typeof bfReCaptchaLoaded == \'undefined\'){bfDoFlashUpload()}else{ff_validate_submit(this, \'click\')}';
				}
				if($this->rootMdata['submitInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last){
                                        /* translatables */
                                        if(isset($this->rootMdata['submitLabel_translation'.$this->language_tag]) && $this->rootMdata['submitLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['submitLabel'] = $this->rootMdata['submitLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button data-theme="a" id="bfSubmitButton" class="bfSubmitButton" type="submit" onclick="if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};'.$callSubmit.';" value="'.htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
			
			}
		}
	}
}