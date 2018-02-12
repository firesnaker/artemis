<?php
	/********************************************************************
	* siteConf.php :: Site Wide Configuration File						*
	*********************************************************************
	* This file contains all the configuration options for the site		*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR										*
	* Created		: 2007-09-29										*
	* Last modified	: 2015-02-12										*
	*																	*
	* 				Copyright (c) 2007-2015 FireSnakeR						*
	********************************************************************/

	$aDefaultLogin = array(
		'username' => 'admin',
		'password' => 'admin'
	); //the default login if database is empty

	define("_siteName_", "Artemis Retail System");
	define("_siteBaseURI_", "http://localhost/artemis/");

	//database configuration
	define('_DBHOST_', 'localhost');
	define('_DBTYPE_', 'mysql');
	define('_DBUSER_', 'dbuser');
	define('_DBPASS_', 'dbpass');
	define('_DBNAME_', 'dbname');
	
	//page configuration
	define('_NbOfDigitBehindComma_', '0');
	define('_DecimalPoint_', ',');
	define('_CommaSeparator_', '.');
	define('_EmailTime_', '18:30');
	define('_EmailReportTo_', 'report@artemis.local');
	define('_EmailReportFrom_', 'report@artemis.local');
	define('_OldestYear_', '2011');
	define('_OutletPurchaseEnabled_' , '16,32,96' );

	date_default_timezone_set("Asia/Jakarta");

	//error reporting and logging
	error_reporting(E_ALL); //set error reporting level, 0 for production, E_ALL for development
	ini_set( 'display_errors', 1 ); //set to 1 for development, 0 for production

	//set this to emulate masterweb memory limit of 90MB
	ini_set( 'memory_limit', "64M" );

?>
