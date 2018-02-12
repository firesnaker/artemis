<?php
	/***************************************************************************
	* master/mkiosPrice.php :: MKIOS Price Setup for Masters				*
	****************************************************************************
	* The MKIOS price setup for master									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan (FireSnakeR) 					*
	* Created			: 2014-07-18 									*
	* Last modified	: 2014-07-18									*
	* 															*
	* 				Copyright (c) 2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classMKios.php");
	//+++ END library inclusion ++++++++++++++++++++++++++++++++++++++++++++++//

	//+++ BEGIN session initialization +++++++++++++++++++++++++++++++++++++++//
	session_start();

	//check session is valid
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
	//+++ END session initialization +++++++++++++++++++++++++++++++++++++++++//

	//+++ BEGIN variable declaration and initialization ++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//+++ END variable declaration and initialization ++++++++++++++++++++++++//

	//+++ BEGIN class initialization +++++++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cUser = new User($_SESSION['user_ID']);
	$cMKios = new MKios;
	//+++ END class initialization +++++++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	//+++ BEGIN $_POST processing ++++++++++++++++++++++++++++++++++++++++++++//
	if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
	{
		//prepare and update data to database
		if (isset($_POST["editAction"]) && $_POST["editAction"] == "Rubah")
		{
			$aMKIOSPrice = array(
				"ID" => $_POST["editID"],
				"EffectiveDate" => $_POST["editYear"] . "-" . $_POST["editMonth"] . "-" . $_POST["editDay"],
				"Type" => $_POST["editType"],
				"S005" => $_POST["editS005"],
				"S010" => $_POST["editS010"],
				"S020" => $_POST["editS020"],
				"S025" => $_POST["editS025"],
				"S050" => $_POST["editS050"],
				"S100" => $_POST["editS100"]
			);

			$iPriceID = $cMKios->SavePrice($aMKIOSPrice);
		}

		//load edit data from database
		if ( isset($_POST["mkiosPriceID"]) && $_POST["mkiosPriceID"] > 0)
		{
			$aSearchBy = array(
				"ID" => $_POST['mkiosPriceID']
			);
			$aMKIOSPriceToEdit = $cMKios->GetMKiosPriceList($aSearchBy);

			//split the date into individual date
			list($sEditDateYear, $sEditDateMonth, $sEditDateDay) = explode("-", $aMKIOSPriceToEdit[0]['EffectiveDate']);
		}
	}
	//+++ END $_POST processing ++++++++++++++++++++++++++++++++++++++++++++++//
	$aSearchByFieldArray = array(
		"Type" => 0, //buy
	);
	$aMKiosBuyList = $cMKios->GetMKiosPriceList($aSearchByFieldArray);
	$aSearchByFieldArray = array(
		"Type" => 1, //sell
	);
	$aMKiosSellList = $cMKios->GetMKiosPriceList($aSearchByFieldArray);
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//
	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/mkiosPrice.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_PAGENAME" => "MKIOS Price Setup",
		"VAR_FORMACTION" => "master/mkiosPrice.php",
		"VAR_EDITACTION" => "Rubah",
		"VAR_FORM_EDIT_DISABLED" => "",

		"VAR_EDITID" => (isset($aMKIOSPriceToEdit[0]['ID']))?$aMKIOSPriceToEdit[0]['ID']:0,
		"VAR_EDITS005" => (isset($aMKIOSPriceToEdit[0]['S005']))?$aMKIOSPriceToEdit[0]['S005']:0,
		"VAR_EDITS010" => (isset($aMKIOSPriceToEdit[0]['S010']))?$aMKIOSPriceToEdit[0]['S010']:0,
		"VAR_EDITS020" => (isset($aMKIOSPriceToEdit[0]['S020']))?$aMKIOSPriceToEdit[0]['S020']:0,
		"VAR_EDITS025" => (isset($aMKIOSPriceToEdit[0]['S025']))?$aMKIOSPriceToEdit[0]['S025']:0,
		"VAR_EDITS050" => (isset($aMKIOSPriceToEdit[0]['S050']))?$aMKIOSPriceToEdit[0]['S050']:0,
		"VAR_EDITS100" => (isset($aMKIOSPriceToEdit[0]['S100']))?$aMKIOSPriceToEdit[0]['S100']:0,
	));

	//typeBlock
	$typeBlock = array();
	for ($i = 0; $i < 2; $i++)
	{
		if ( isset($_POST['mkiosPriceID']) )
		{
			$sDefaultType = $aMKIOSPriceToEdit[0]['Type'];
		}
		else
		{
			$sDefaultType = 1;
		}
		$typeBlock[] = array(
			"VAR_TYPEVALUE" => $i,
			"VAR_TYPETEXT" => ($i == 0)?"Buy":"Sell",
			"VAR_TYPESELECTED" => ( $i == $sDefaultType )?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "typeBlock", $typeBlock);

	//editDayBlock
	$editDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['mkiosPriceID']) )
		{
			$sDefaultBeginDay = $sEditDateDay;
		}
		else
		{
			$sDefaultBeginDay = date("d");
		}
		$editDayBlock[] = array(
			"VAR_EDITDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_EDITDAYSELECTED" => ( ($i+1) == $sDefaultBeginDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "editDayBlock", $editDayBlock);

	//editMonthBlock
	$editMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['mkiosPriceID']) )
		{
			$sDefaultBeginMonth = $sEditDateMonth;
		}
		else
		{
			$sDefaultBeginMonth = date("m");
		}
		$editMonthBlock[] = array(
			"VAR_EDITMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_EDITMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_EDITMONTHSELECTED" => ( ($i+1) == $sDefaultBeginMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "editMonthBlock", $editMonthBlock);

	//editYearBlock
	$editYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['mkiosPriceID']) )
		{
			$sDefaultBeginYear = $sEditDateYear;
		}
		else
		{
			$sDefaultBeginYear = date("Y");
		}
		$editYearBlock[] = array(
			"VAR_EDITYEARVALUE" => $i,
			"VAR_EDITYEARSELECTED" => ( $i == $sDefaultBeginYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "editYearBlock", $editYearBlock);

	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	$buyListBlock = array();
	for ($i = 0; $i < count($aMKiosBuyList); $i++)
	{
		$buyListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_MKIOSPRICEID" => $aMKiosBuyList[$i]['ID'],
			"VAR_EFFECTIVEDATE" => date("d-M-Y" , strtotime($aMKiosBuyList[$i]['EffectiveDate'])),
			"VAR_S005" => $aMKiosBuyList[$i]['S005'],
			"VAR_S010" => $aMKiosBuyList[$i]['S010'],
			"VAR_S020" => $aMKiosBuyList[$i]['S020'],
			"VAR_S025" => $aMKiosBuyList[$i]['S025'],
			"VAR_S050" => $aMKiosBuyList[$i]['S050'],
			"VAR_S100" => $aMKiosBuyList[$i]['S100']
		);
	}
	$cWebsite->buildBlock("content", "buyListBlock", $buyListBlock);

	$sellListBlock = array();
	for ($i = 0; $i < count($aMKiosSellList); $i++)
	{
		$sellListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_MKIOSPRICEID" => $aMKiosSellList[$i]['ID'],
			"VAR_EFFECTIVEDATE" => date("d-M-Y" , strtotime($aMKiosSellList[$i]['EffectiveDate'])),
			"VAR_S005" => $aMKiosSellList[$i]['S005'],
			"VAR_S010" => $aMKiosSellList[$i]['S010'],
			"VAR_S020" => $aMKiosSellList[$i]['S020'],
			"VAR_S025" => $aMKiosSellList[$i]['S025'],
			"VAR_S050" => $aMKiosSellList[$i]['S050'],
			"VAR_S100" => $aMKiosSellList[$i]['S100']
		);
	}
	$cWebsite->buildBlock("content", "sellListBlock", $sellListBlock);

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING *************************************************//
?>