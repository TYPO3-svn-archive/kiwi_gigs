<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_kiwigigs_pi1 = < plugin.tx_kiwigigs_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_kiwigigs_pi1.php','_pi1','list_type',1);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_kiwigigs_pi2 = < plugin.tx_kiwigigs_pi2.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_kiwigigs_pi2.php','_pi2','list_type',1);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_kiwigigs_main=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_kiwigigs_main", field "description"
	# ***************************************************************************************
RTE.config.tx_kiwigigs_main.description {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
?>