<?php
	/************************************************************************
	* Artemis version 1.0													*
	*************************************************************************
	* Copyright (c) 2007-2018 Ricky Kurniawan ( FireSnakeR )				*
	*************************************************************************
	* This file is part of Artemis.											*
	*																		*
    * Artemis is free software: you can redistribute it and/or modify		*
    * it under the terms of the GNU General Public License as published by	*
    * the Free Software Foundation, either version 3 of the License, or		*
    * (at your option) any later version.									*
	*																		*
    * Artemis is distributed in the hope that it will be useful,			*
    * but WITHOUT ANY WARRANTY; without even the implied warranty of		*
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the			*
    * GNU General Public License for more details.							*
	*																		*
    * You should have received a copy of the GNU General Public License		*
    * along with Artemis.  If not, see <http://www.gnu.org/licenses/>.		*
    * 																		*
    *************************************************************************
	* siteConf.php :: Site Wide Configuration File						*
	*********************************************************************
	* This file contains all the configuration options for the site		*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR										*
	* Created		: 2007-09-29										*
	* Last modified	: 2015-02-12										*
	*																	*
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
