<?php
	/********************************************************************
	* master/accountReceivable.php :: MKios Page								*
	*********************************************************************
	* The account receivable page for mkios											*
	* Laporan piutang dari reseller
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-01-11 										*
	* Last modified	: 2014-01-11										*
	* 																	*
	* 				Copyright (c) 2014 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classReport.php");
		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 
		  && ($_SESSION['user_Name'] == "admin" ) )
		{
			//do nothing
		}
		else
		{
			$_SESSION = array();
			session_destroy(); //destroy all session
			//TODO: create a log file
	 		header("Location:index.php"); //redirect to index page
	 		exit;
		}
		//+++ END session initialization ++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN variable declaration and initialization +++++++++++++++++//
		$sErrorMessages = FALSE;
		$sMessages = FALSE;
		$sFixedBeginDate = date("Y-m-d");
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sBeginDate = $_POST['dateBegin2'];
		}
		else
		{
			$sBeginDate = $sFixedBeginDate;
		}

		list($iBeginYear, $iBeginMonth, $iBeginDay) = explode("-", $sBeginDate);

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/accountReceivable-mkios.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Laporan Piutang",
		"TEXT_NO" => "No",
		"TEXT_DATE" => "Tanggal Nota",
		"TEXT_SUBTOTAL" => "Subtotal",
		"TEXT_PAYMENT" => "Payment",
		"TEXT_KODESALES" => "Kode Sales",

		"VAR_DATEBEGINJS2" => date("m/d/Y", strtotime($sBeginDate)),
		"VAR_DATEBEGIN2" => $sBeginDate,

		"VAR_FORMACTION" => "master/accountReceivable-mkios.php"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	$cWebsite->buildContent("VAR_CONTENT");

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//
?>