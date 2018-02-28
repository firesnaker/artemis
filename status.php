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
	* status.php :: Status Page										*
	****************************************************************************
	* This page shows database status, is it up or down					*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan / FireSnakeR					*
	* Created			: 2014-07-29									*
	* Last modified	: 2014-07-29									*
	* 															*
	* 			Copyright (c) 2014 FireSnakeR							*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($rootPath . "config.php");
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classDatabase.php");
	include_once($libPath . "/classProduct.php");
	//+++ END library inclusion ++++++++++++++++++++++++++++++++++++++++++++++//

	//+++ BEGIN session initialization +++++++++++++++++++++++++++++++++++++++//
	//+++ END session initialization +++++++++++++++++++++++++++++++++++++++++//

	//+++ BEGIN variable declaration and initialization ++++++++++++++++++++++//
	//+++ END variable declaration and initialization ++++++++++++++++++++++++//

	//+++ BEGIN class initialization +++++++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cDB = new Database;
	$cProduct = new Product;
	//+++ END class initialization +++++++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	//+++ BEGIN Data Loading For Page ++++++++++++++++++++++++++++++++++++++++//
	$sDatabaseStatus = "UP";
	$cDB->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

	if ( $cDB->dbError )
	{
		$sDatabaseStatus = "DOWN" . "::" . $cDB->dbError;
	}

	$aCategoryList = $cProduct->GetCategoryList();
	//+++ END Data Loading For Page ++++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "status.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => "Status",
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_DATABASESTATUS" => $sDatabaseStatus,
		"CSS_DATABASESTATUS" => ($sDatabaseStatus == "UP")?"normal":"error",
	));

	$cWebsite->template->set_block("navigation", "navigation_top");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top");
	
	$cWebsite->template->set_block("navigation", "language");
	$cWebsite->template->parse("VAR_LANGUAGE", "language");
	
	$cWebsite->template->set_block("navigation", "navigation_left");
	//productCategoryBlock
	$productCategoryBlock = array();
	for ($i = 0; $i < count($aCategoryList); $i++)
	{
		$productCategoryBlock[] = array(
			"VAR_NAVIGATION_CATEGORYNAME_LINK" => strtolower( str_replace(" ", "-", $aCategoryList[$i]['Name']) ),
			"VAR_NAVIGATION_CATEGORYNAME" => strtoupper($aCategoryList[$i]['Name'])			
		);
	}
	$cWebsite->buildBlock("navigation_left", "productCategoryBlock", $productCategoryBlock);
	$cWebsite->template->parse("VAR_NAVIGATIONLEFT", "navigation_left");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING *************************************************//

?>
