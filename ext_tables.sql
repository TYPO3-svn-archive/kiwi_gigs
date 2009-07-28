#
# Table structure for table 'tx_kiwigigs_main'
#
CREATE TABLE tx_kiwigigs_main (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	date int(11) DEFAULT '0' NOT NULL,
	location tinytext NOT NULL,
	city tinytext NOT NULL,
	description text NOT NULL,
	flyer blob NOT NULL,
	location_address text NOT NULL,
	location_zip tinytext NOT NULL,
	location_url tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);