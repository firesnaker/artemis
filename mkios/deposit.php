<?php
	/********************************************************************
	* mkios/deposit.php :: MKios Deposit Page								*
	*********************************************************************
	* The deposit page for mkios											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-10-04 										*
	* Last modified	: 2013-10-04										*
	* 																	*
	* 				Copyright (c) 2013 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classMKios.php");
		//include_once($libPath . "/classUser.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 )
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
		$sDate = date("d-M-Y");
		$sDatePrint = date("Y-m-d");
		$sDateDeposit = date("Y-m-d");
		$sFormElementDisabled = "";
		$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cMKios = new MKios;
		//$cUser = new User($_SESSION['user_ID']);
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ($_POST["depositSubmit"] == "Save")
			{
				$aDepositForm = array(
					"ID" => $_POST["deposit_ID"],
					//"outlet_ID" => $_POST["outlet_ID"],
					"Date" => $_POST["deposit_Edit_Date"],
					"Notes" => $_POST["deposit_notes"],
					"Price" => $_POST["deposit_price"]
				);
				if ( $_POST["deposit_ID"] == 0 )
				{
					$iDepositID = $cMKios->InsertDeposit($aDepositForm);
				}
				else
				{
					$iDepositID = $cMKios->UpdateDeposit($aDepositForm);
				}				
			}

			if ($_POST["depositEditSubmit"] == "Edit")
			{
				$aDepositEdit = $cMKios->GetDepositByID($_POST["depositEdit_ID"]);
			}

			if ($_POST["depositDeleteSubmit"] == "Delete")
			{
				$iDepositID = $cMKios->RemoveDeposit($_POST["depositDelete_ID"]);
			}

			$sDepositMonth = date("m");
			if ( isset($_POST["deposit_month"]) && $_POST["deposit_month"] )
			{
				$sDepositMonth = $_POST["deposit_month"];
			}
			if ( $_POST["deposit_day"] && $sDepositMonth && $_POST["deposit_year"] )
			{
				$sDateDeposit = $_POST["deposit_year"] . "-" . $sDepositMonth . "-" . $_POST["deposit_day"]; 
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		//get product list visible on website
		$aDepositSearchByFieldArray = array(
			//"outlet_ID" => $_SESSION['outlet_ID'],
			"Date" => $sDateDeposit
		);
		$aDepositList = $cMKios->GetDepositList($aDepositSearchByFieldArray);

		if (count($aDepositList) > 0)
		{
				$sFormElementDisabled = "";
		}
		else
		{
			//disable edit
			$sFormElementDisabled = "disabled='1'";
		}
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/deposit.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	list($year, $month, $day) = explode("-", $sDateDeposit);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_DEPOSITDATE" => date( "d-M-Y", mktime(0,0,0, $month, $day, $year) ),
		"VAR_DEPOSITDATEPRINT" => $sDateDeposit,
		"VAR_DEPOSITDAY" => $day,
		"VAR_DEPOSITMONTH" => $month,
		"VAR_DEPOSITYEAR" => $year,
		//"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		//"VAR_OUTLETID" => $_SESSION['outlet_ID'],
		"VAR_ELEMENTDISABLED" => $sFormElementDisabled,
		"VAR_DEPOSITID" => ($aDepositEdit)?$aDepositEdit[0]["ID"]:"",
		"VAR_DEPOSITEDITDATE" => ($aDepositEdit)?$aDepositEdit[0]["Date"]:$sDateDeposit,
		"VAR_DEPOSITNOTES" => ($aDepositEdit)?$aDepositEdit[0]["Notes"]:"",
		"VAR_DEPOSITPRICE" => ($aDepositEdit)?str_replace(",", "", number_format($aDepositEdit[0]["Price"], 0)):"",
		"VAR_DEPOSITMONTHDISABLED" => "disabled=1"
	));

	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");

	//depositDayBlock
	$depositDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['deposit_day']) )
		{
			$sDefaultDay = $_POST['deposit_day'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$depositDayBlock[] = array(
			"VAR_DAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "depositDayBlock", $depositDayBlock);

	//depositMonthBlock
	$depositMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['deposit_month']) )
		{
			$sDefaultMonth = $_POST['deposit_month'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$depositMonthBlock[] = array(
			"VAR_MONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "depositMonthBlock", $depositMonthBlock);

	//depositYearBlock
	$depositYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['deposit_year']) )
		{
			$sDefaultYear = $_POST['deposit_year'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$depositYearBlock[] = array(
			"VAR_YEARVALUE" => $i,
			"VAR_YEARSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "depositYearBlock", $depositYearBlock);

	//depositListBlock
	$depositListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aDepositList); $i++)
	{
		$iGrandTotal += $aDepositList[$i]['Price'];
		$depositListBlock[] = array(
			"VAR_COUNTER" => $i+1,
			"VAR_LISTDEPOSITID" => $aDepositList[$i]['ID'],
			"VAR_LISTDEPOSITNOTES" => $aDepositList[$i]['Notes'],
			"VAR_LISTDEPOSITPRICE" => number_format( $aDepositList[$i]['Price'], _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("content", "depositList", $depositListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTAL" => number_format( $iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>