<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_kiwigigs_main"] = array (
	"ctrl" => $TCA["tx_kiwigigs_main"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,date,location,city,description,flyer,location_address,location_zip,location_url"
	),
	"feInterface" => $TCA["tx_kiwigigs_main"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.date",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"location" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.location",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"city" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.city",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"flyer" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.flyer",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_kiwigigs",
				"show_thumbs" => 1,	
				"size" => 2,	
				"minitems" => 0,
				"maxitems" => 2,
			)
		),
		"location_address" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.location_address",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"location_zip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.location_zip",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"location_url" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kiwi_gigs/locallang_db.xml:tx_kiwigigs_main.location_url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"wizards" => Array(
					"_PADDING" => 2,
					"link" => Array(
						"type" => "popup",
						"title" => "Link",
						"icon" => "link_popup.gif",
						"script" => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					),
				),
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, date;;;;3-3-3, location, city, description;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_kiwigigs/rte/], flyer, location_address, location_zip, location_url")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>