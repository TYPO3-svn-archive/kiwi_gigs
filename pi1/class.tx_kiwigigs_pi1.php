<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Andreas Kiefer <kiefer@kennziffer.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Gig Lister' for the 'kiwi_gigs' extension.
 *
 * @author	Andreas Kiefer <kiefer@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kiwigigs
 */
class tx_kiwigigs_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_kiwigigs_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_kiwigigs_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'kiwi_gigs';	// The extension key.
	var $pi_checkCHash = true;
	var $table = 'tx_kiwigigs_main';
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string	$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return			The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
	
		// get HTML template
		$this->templateFile = $this->conf['templateFile'];
		$this->templateCode = $this->cObj->fileResource($this->templateFile);
		
		// include css
		$this->cssFile = t3lib_extMgm::siteRelPath($this->extKey).'res/css/kiwi_gigs.css';
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= '<link rel="stylesheet" type="text/css" href="'.$this->cssFile.'" />';
	
		// generate content 
		if ($this->piVars['showUid']) $content = $this->showSingle();
		else $content = $this->listview();
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	
	/**
 	* Description:
 	* Author: Andreas Kiefer (kiefer@kennziffer.com)
 	*
 	*/ 
 	function listview() {
		
		$content = $this->cObj->getSubpart($this->templateCode,'###LISTVIEW###');
		
		// coming gigs
		$content = $this->cObj->substituteMarker($content,'###COMING_GIGS_HEADLINE###',$this->pi_getLL('label_coming_gigs'));
		$content = $this->cObj->substituteMarker($content,'###COMING_GIGS_LIST###',$this->renderGigList());
		
		// passed gigs
		if ($this->conf['showPassedGigs']) {
			$content = $this->cObj->substituteMarker($content,'###PASSED_GIGS_HEADLINE###',$this->pi_getLL('label_passed_gigs'));
			$content = $this->cObj->substituteMarker($content,'###PASSED_GIGS_LIST###',$this->renderGigList('passed'));
		}
		else $content = $this->cObj->substituteSubpart ($content, '###PASSED_GIGS###', '');
		
		return $content;    
 	}
	
	
	
	/**
	*  Description
	*  Author: Andreas Kiefer (kiefer@kennziffer.com)
	* 	  
	*  @param $mode string	'coming' or 'passed'
	*  @return string 		HTML content
	*/
	function  renderGigList($mode='coming') {
		
		$content = $this->cObj->getSubpart($this->templateCode,'###GIG_LIST###');
		
		// get db entries for coming or passed gigs
		if ($mode == 'coming') {
			$fields = '*';
			$where = 'date >= '.strtotime('tomorrow');
			$where .= $this->cObj->enableFields($this->table);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$this->table,$where,$groupBy='',$orderBy='date',$limit='');
			$anz = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		}
		else if ($mode == 'passed') {
			$fields = '*';
			$where = 'date < '.strtotime('tomorrow');
			$where .= $this->cObj->enableFields($this->table);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$this->table,$where,$groupBy='',$orderBy='date desc',$limit='');
			$anz = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		}
		
		$listviewEntries = '';
		
		while ($this->currentRow=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// generate link to singleview
			unset($linkconf);
			$linkconf['parameter'] = $GLOBALS['TSFE']->id;
			$linkconf['additionalParams'] = '&'.$this->prefixId.'[showUid]='.$this->currentRow['uid'];
			$linkconf['useCacheHash'] = true;
			$linkURL = $this->cObj->typoLink_URL($linkconf);
		
			// fill marker array
			$tempContent = $this->cObj->getSubpart($this->templateCode,'###LISTVIEW_ENTRY###');
			$tempMarker = array (
				'title' => $this->getFieldContent('title'),
				'date' => $this->getFieldContent('date'),
				'location' => $this->getFieldContent('location'),
				'city' => $this->getFieldContent('city'),
				'description' => $this->getFieldContent('description'),
				'flyer' => $this->getFieldContent('flyer'),
				'thumbnail' => $this->getFieldContent('thumbnail'),
				'single_url' => $linkURL,
			);
			$tempContent = $this->cObj->substituteMarkerArray($tempContent,$tempMarker,$wrap='###|###',$uppercase=1);
			$listviewEntries .= $tempContent;
		}
		
		// fill markers with content
		$listContent = $this->cObj->getSubpart($this->templateCode,'###LISTVIEW###');
		$listContent = $this->cObj->substituteSubpart ($content, '###LISTVIEW_ENTRY###', $listviewEntries);
		
		$content = $this->cObj->substituteSubpart ($content, '###LISTVIEW_ENTRY###', $listContent);
		
		return $content;
	}	
	
	
	/**
	*	 Description
	*	 Author: Andreas Kiefer (kiefer@kennziffer.com)
	*	  
	*	 @param
	*	 @return 
	*/
	function  showSingle() {
		
		// get data from db
		$fields = '*';
		$where = 'uid="'.intval($this->piVars['showUid']).'" ';
		$where .= $this->cObj->enableFields($this->table);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$this->table,$where,$groupBy='',$orderBy='',$limit='1');
		$this->currentRow=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		// generate backlink
		unset($linkconf);
		$backPid = $this->piVars['backPid'] ? $this->piVars['backPid'] : $GLOBALS['TSFE']->id;
		$linkconf['parameter'] = $backPid;
		$linkconf['useCacheHash'] = true;
 		$backlink = $this->cObj->typoLink($this->pi_getLL('label_backlink'),$linkconf);
		
		// fill marker array
		$markerArray = array(
			'title' => $this->getFieldContent('title'),
			'label_date' => $this->pi_getLL('label_date'),
			'date' => $this->getFieldContent('date'),
			'label_location' => $this->pi_getLL('label_location'),
			'location' => $this->getFieldContent('location'),
			'city' => $this->getFieldContent('city'),
			'label_description' => $this->pi_getLL('label_description'),
			'description' => $this->getFieldContent('description'),
			'label_flyer' => $this->pi_getLL('label_flyer'),
			'flyer' => $this->getFieldContent('flyer'),
			'backlink' => $backlink,
			'backlink_icon' => $this->cObj->IMAGE($this->conf['singleview.']['backlinkIcon.']),
		);
		
		// show map?
		if ($this->conf['showMap']) {
			// include api file
			require_once(dirname(__FILE__). '/../res/GoogleMapAPI.class.php');
			$markerArray['map'] = $this->renderGoogleMap(
				$this->getFieldContent('gmaps_address'),
				$this->getFieldContent('gmaps_company'), 
				1, 
				$this->getFieldContent('gmaps_htmladdress')
			);
			$markerArray['mapJS'] = $this->gmapsJSContent;
		}
		else {
			$markerArray['map'] = '';
			$markerArray['mapJS'] = '';
		}
		
		$GLOBALS['TSFE']->pSetup['bodyTagAdd'] = " onload=\"onLoad1();\" onunload=\"GUnload();\"";
		
		$content = $this->cObj->getSubpart($this->templateCode,'###SINGLEVIEW###');
		$content = $this->cObj->substituteMarkerArray($content,$markerArray,$wrap='###|###',$uppercase=1);
		
		#$content .= '<script language="text/javascript">onLoad1();</script>';
		
		// overwrite subparts without content
		if (empty($this->currentRow['description'])) $content = $this->cObj->substituteSubpart ($content, '###BLOCK_DESCRIPTION###', '');
		if (!$this->conf['showMap']) $content = $this->cObj->substituteSubpart ($content, '###BLOCK_MAP###', '');
		if (empty($this->currentRow['flyer'])) $content = $this->cObj->substituteSubpart ($content, '###BLOCK_FLYER###', '');
		
		
		return $content;
		
	}	
	
	
	
	/**
 	* Description:
 	* Author: Andreas Kiefer (kiefer@kennziffer.com)
 	*
 	*/ 
 	function renderGoogleMap($address,$company,$i,$htmladdress) {
		//Create dynamic DIV to show GoogleMaps-element in
		$gMaps = new GoogleMapAPI('kiwigigs_map_'.$i);

		//Set API-Key(s)
		$gMaps->setAPIKey($this->conf['gmaps.']['apiKey']);

		//GoogleMaps-Settings
		$gMaps->setWidth($this->conf['gmaps.']['width']);
		$gMaps->setHeight($this->conf['gmaps.']['height']);
		$gMaps->setZoomLevel($this->conf['gmaps.']['zoomLevel']);
		$gMaps->addMarkerByAddress($address,$company,$htmladdress,$company);
		$gMaps->setInfoWindowTrigger('mouseover');
		if ($this->conf['gmaps.']['disableMapControls']) $gMaps->disableMapControls();
		if ($this->conf['gmaps.']['enableTypeControls']) $gMaps->enableTypeControls();
		
		//Create cacheable, dynamical js-File
		$md5= md5($address.$i);
		$filename="typo3temp/gmap_{$md5}.js";
		$fh=fopen($filename,'w');
		fputs($fh,preg_replace('/<\/?script[^>]*>/i','',$gMaps->getMapJS($i)));
		fclose($fh);
		
		//Include requires JS and GoogleMap-element
		$sidebar_dummy='<div id="sidebar_kiwigigs_map_'.$i.'" style="display:none"></div>';
		$content= $sidebar_dummy.$gMaps->getMap();
		#$this->test .= $gMaps->getHeaderJS()."\n<script src='{$filename}' type='text/javascript' ></script>";
		$this->gmapsJSContent .= "\n\n".$gMaps->getHeaderJS()."\n<script src='{$filename}' type='text/javascript' ></script>";
		#$GLOBALS["TSFE"]->additionalHeaderData[$this->prefixId] .= "\n\n".$gMaps->getHeaderJS()."\n<script src='{$filename}' type='text/javascript' ></script>";
		$GLOBALS["TSFE"]->additionalHeaderData[$this->prefixId] .= '
			<script type="text/javascript" >
				function kiwigigs_popit_'.$i.'() {
					if(isArray(marker_html_'.$i.'[0])) { markers[0].openInfoWindowTabsHtml(marker_html_'.$i.'[0]); }
				else { markers[0].openInfoWindowHtml(marker_html_'.$i.'[0]); }
				}
			</script>';
		
		
		return $content;
 	}
	
	
	/**
	*	 Description
	*	 Author: Andreas Kiefer (kiefer@kennziffer.com)
	*	  
	*	 @param
	*	 @return 
	*/
	function  getFieldContent($fieldName) {
		switch ($fieldName) {
			case 'date':		
				return strftime('%d.%m.%y', $this->currentRow[$fieldName]);
				break;
				
			case 'description':	
				return $this->pi_RTEcssText ($this->currentRow[$fieldName]);
				break;
				
			case 'flyer':
				$flyerConf = $this->conf['singleview.']['flyer.'];
				$flyerConf['file'] = 'uploads/tx_kiwigigs/'.$this->currentRow[$fieldName];
				return $this->cObj->IMAGE($flyerConf);
				break;
			
			case 'thumbnail':
				$thumbConf = $this->conf['listview.']['thumbnail.'];
				$thumbConf['file'] = 'uploads/tx_kiwigigs/'.$this->currentRow['flyer'];
				return $this->cObj->IMAGE($thumbConf);
				break;
			
			case 'gmaps_address':
				$address = $this->currentRow['location_address'].' ';
				$address .= $this->currentRow['location_zip'].' ';
				$address .= $this->currentRow['city'];
				return $address;
				break;
				
			case 'gmaps_company':
				return $this->currentRow['location'];
				break;
				
			case 'gmaps_htmladdress':
				$htmlAddress = '<b>'.$this->currentRow['location'].'</b><br />';
				$htmlAddress .= $this->currentRow['location_address'].'<br />';
				$htmlAddress .= $this->currentRow['location_zip'].' '.$this->currentRow['city'].'<br />';
				return $htmlAddress;
				break;
			
			default:				
				return $this->currentRow[$fieldName];
				break;
		}
	}	
	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kiwi_gigs/pi1/class.tx_kiwigigs_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kiwi_gigs/pi1/class.tx_kiwigigs_pi1.php']);
}

?>