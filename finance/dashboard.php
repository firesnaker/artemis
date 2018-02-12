<?php
	/********************************************************************
	* finance/dashboard.php :: Finance Dashboard Page								*
	*********************************************************************
	* The dashboard page for finance											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2012-04-29 										*
	* Last modified	: 2012-05-03										*
	* 																	*
	* 				Copyright (c) 2012 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classUser.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 
		  && ($_SESSION['user_Name'] == "admin" || strtolower($_SESSION['user_Name']) == "finance" || $_SESSION['user_IsFinance'] == 1) )
		{
			//do nothing
		}
		else
		{
			$_SESSION = array();
			session_destroy(); //destroy all session
			//TODO: create a log file
	 		header("Location:../index.php"); //redirect to index page
	 		exit;
		}
		//+++ END session initialization ++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN variable declaration and initialization +++++++++++++++++//
		$sErrorMessages = FALSE;
		$sMessages = FALSE;
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cUser = new User($_SESSION['user_ID']);
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "finance/dashboard.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome " . $cUser->Name . "!"
	));
	

	$cWebsite->template->set_block("navigation", "navigation_top_finance");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_finance");
	
	
	/*$cWebsite->template->set_block("navigation", "language");
	$cWebsite->template->parse("VAR_LANGUAGE", "language");
	
	$cWebsite->template->set_block("navigation", "navigation_left");
	$cWebsite->template->parse("VAR_NAVIGATIONLEFT", "navigation_left");
	
	$cWebsite->template->set_block("navigation", "navigation_bottom");
	$cWebsite->template->parse("VAR_NAVIGATIONBOTTOM", "navigation_bottom");*/
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>