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
	* admin/supplier.php :: Admin Supplier Page										*
	*************************************************************************
	* The supplier CRUD page for admin													*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2015-03-23 														*
	* Last modified	: 2015-03-23														*
	*																								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first ++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$sPageName = "Supplier Management";

	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
		//+++ BEGIN $_POST processing ++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing ++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING *********************************************//

	//*** BEGIN PAGE RENDERING ********************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/supplier.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_USERLOGGEDIN" => ucfirst($_SESSION['user_Name']),
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		//page text
		//supplier modal dialog text and variables
		"TEXT_SUPPLIER_FORM" => "Supplier Form", //TODO: change into language variables
		"TEXT_CLOSE" => "Close", //TODO: change into language variables
		"TEXT_SAVE_CHANGES" => "Save Changes", //TODO: change into language variables

		"TEXT_NAME" => "Name", //TODO: change into language variables

		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("Name"), //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
	));

	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING **********************************************//
?>
