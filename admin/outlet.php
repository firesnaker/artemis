<?php
	/************************************************************************
	* admin/oulet.php :: Admin Outlets Page											*
	*************************************************************************
	* The products CRUD page for admin													*
	*																								*
	* Version			: 2																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2009-03-28 														*
	* Last modified	: 2015-02-12														*
	*																								*
	* 						Copyright (c) 2009-2015 FireSnakeR							*
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
	$sPageName = "Outlet Management";
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
		//+++ BEGIN $_POST processing ++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing ++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING *********************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/outlet.htm"
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
		//product modal dialog text and variables
		"TEXT_OUTLET_FORM" => "Outlet Form", //TODO: change into language variables
		"TEXT_PHONE" => "No. Telepon", //TODO: change into language variables
		"TEXT_FAX" => "No. Faximile", //TODO: change into language variables
		"TEXT_ALLOWPURCHASE" => "Buka Pembelian", //TODO: change into language variables
		"TEXT_CLOSE" => "Close", //TODO: change into language variables
		"TEXT_SAVE_CHANGES" => "Save Changes", //TODO: change into language variables

		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_CODE" => "Kode", //TODO: change into language variables
		"TEXT_NAME" => "Name", //TODO: change into language variables
		"TEXT_ADDRESS" => "Address", //TODO: change into language variables
		"TEXT_MASTEROUTLET" => "Master Outlet", //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>