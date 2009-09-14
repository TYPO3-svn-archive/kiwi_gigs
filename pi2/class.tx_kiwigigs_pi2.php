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
 * Plugin 'Gig Teaser' for the 'kiwi_gigs' extension.
 *
 * @author	Andreas Kiefer <kiefer@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kiwigigs
 */
class tx_kiwigigs_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_kiwigigs_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_kiwigigs_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'kiwi_gigs';	// The extension key.
	var $pi_checkCHash = true;
	var $table = 'tx_kiwigigs_main';
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
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
		
		// get startingpoint
		$pages = $this->cObj->data['pages'] ? $this->cObj->data['pages'] : ( $this->conf['dataPids'] ? $this->conf['dataPids'] : $GLOBALS['TSFE']->id);
		$this->pids = $this->pi_getPidList($pages, $this->cObj->data['recursive']);
		
		/**********************************************
		* Get flexform data
		**********************************************/ 
		// Init and get the flexform data of the plugin
		$this->pi_initPIflexForm(); 
		
		// Assign the flexform data to a local variable for easier access
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		// Traverse the entire flexform array based on the language
		// and write the content to an array
		if (is_array($piFlexForm['data'])) {
			foreach ( $piFlexForm['data'] as $sheet => $data ) {
				foreach ( $data as $lang => $value ) {
					foreach ( $value as $key => $val ) {
						$this->ffdata[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
					}
				}
			}
		}
		
		#require_once(t3lib_extMgm::extPath('kiwi_gigs').'pi1/class.tx_kiwigigs_pi1.php');
		#$gigLister = t3lib_div::makeInstance('tx_kiwigigs_pi1');
		#debug($gigLister);
		
		// generate content 
		if ($this->piVars['showUid']) $content = $this->showSingle();
		else $content = $this->teaserview();
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	
	/**
 	* Description:
 	* Author: Andreas Kiefer (kiefer@kennziffer.com)
 	*
 	*/ 
 	function teaserview() {
		
		$limit = $this->ffdata['teaserEntries'] ? $this->ffdata['teaserEntries'] : $this->conf['numberOfGigs'];
		
		// get next shows from db
		$fields = '*';
 		$where = 'date >= '.strtotime('tomorrow');
		$where .= ' AND pid in('.$this->pids.') ';
 		$where .= $this->cObj->enableFields($this->table);
 		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$this->table,$where,$groupBy='',$orderBy='',$limit);
 		$anz = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		// return empty content if no future gigs found
		if ($anz == 0) return '';
 		while ($this->currentRow=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				
			// generate link to singleview
			unset($linkconf);
			$linkconf['parameter'] = $this->conf['singleviewPID'];
			$linkconf['additionalParams'] = '&tx_kiwigigs_pi1[showUid]='.$this->currentRow['uid'];
			$linkconf['additionalParams'] .= '&tx_kiwigigs_pi1[backPid]='.$GLOBALS['TSFE']->id;
			$linkconf['useCacheHash'] = true;
			$linkURL = $this->cObj->typoLink_URL($linkconf);
			
			$tempMarker = array (
				'single_url' => $linkURL,
				'date' => $this->getFieldContent('date'),
				'city' => $this->getFieldContent('city'),
				'title' => $this->getFieldContent('title'),
				'location' => $this->getFieldContent('location'),
				'thumbnail' => $this->getFieldContent('thumbnail'),
			);
			
			$tempContent = $this->cObj->getSubpart($this->templateCode,'###TEASERVIEW_ENTRY###');
			$tempContent = $this->cObj->substituteMarkerArray($tempContent,$tempMarker,$wrap='###|###',$uppercase=1);
			$teaserEntries .= $tempContent;
 		}
		
		$content = $this->cObj->getSubpart($this->templateCode,'###TEASERVIEW###');
		$content = $this->cObj->substituteMarker($content,'###TEASER_HEADLINE###',$this->pi_getLL('teaser_headline'));
		$content = $this->cObj->substituteSubpart ($content, '###TEASERVIEW_ENTRY###', $teaserEntries, $recursive=1);
		
		
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
			
			default:				
				return $this->currentRow[$fieldName];
				break;
		}
	}	
	
	
	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kiwi_gigs/pi2/class.tx_kiwigigs_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kiwi_gigs/pi2/class.tx_kiwigigs_pi2.php']);
}

?>