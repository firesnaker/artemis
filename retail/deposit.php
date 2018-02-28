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
	* retail/deposit.php :: Retail Cash Deposit Page						*
	****************************************************************************
	* The deposit page for retail										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-02-10 									*
	* Last modified	: 2014-08-21									*
	* 															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_user('user_ID') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classDeposit.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cDeposit = new Deposit;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Cash Deposit";
	$sDate = date("d-M-Y");
	$sDatePrint = date("Y-m-d");
	$sDateDeposit = date("Y-m-d");
	$sFormElementDisabled = "";
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if (isset($_POST["depositSubmit"]) && $_POST["depositSubmit"] == "Save")
			{
				$aDepositForm = array(
					"ID" => $_POST["deposit_ID"],
					"outlet_ID" => $_POST["outlet_ID"],
					"Date" => $_POST["deposit_Edit_Date"],
					"Notes" => $_POST["deposit_notes"],
					"Price" => $_POST["deposit_price"]
				);
				if ( $_POST["deposit_ID"] == 0 )
				{
					$iDepositID = $cDeposit->Insert($aDepositForm);
				}
				else
				{
					$iDepositID = $cDeposit->Update($aDepositForm);
				}				
			}

			if (isset($_POST["depositEditSubmit"]) && $_POST["depositEditSubmit"] == "Edit")
			{
				$aDepositEdit = $cDeposit->GetDepositByID($_POST["depositEdit_ID"]);
			}

			if (isset($_POST["depositDeleteSubmit"]) && $_POST["depositDeleteSubmit"] == "Delete")
			{
				$iDepositID = $cDeposit->Remove($_POST["depositDelete_ID"]);
			}

			$sDepositMonth = date("m");
			if ( isset($_POST["deposit_month"]) && $_POST["deposit_month"] )
			{
				$sDepositMonth = $_POST["deposit_month"];
			}
			if ( isset($_POST["deposit_day"]) && $_POST["deposit_day"] && $sDepositMonth && isset($_POST["deposit_year"]) && $_POST["deposit_year"] )
			{
				$sDateDeposit = $_POST["deposit_year"] . "-" . $sDepositMonth . "-" . $_POST["deposit_day"]; 
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		//get product list visible on website
		$aDepositSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID'],
			"Date" => $sDateDeposit
		);
		$aDepositList = $cDeposit->GetDepositList($aDepositSearchByFieldArray);

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
		"content" => "retail/deposit.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	list($year, $month, $day) = explode("-", $sDateDeposit);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_PAGEOUTLETNAME" => $_SESSION['outlet_Name'],

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_DEPOSITDATE" => date( "d-M-Y", mktime(0,0,0, $month, $day, $year) ),
		"VAR_DEPOSITDATEPRINT" => $sDateDeposit,
		"VAR_DEPOSITDAY" => $day,
		"VAR_DEPOSITMONTH" => $month,
		"VAR_DEPOSITYEAR" => $year,
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_OUTLETID" => $_SESSION['outlet_ID'],
		"VAR_ELEMENTDISABLED" => $sFormElementDisabled,
		"VAR_DEPOSITID" => (isset($aDepositEdit) && $aDepositEdit)?$aDepositEdit[0]["ID"]:"",
		"VAR_DEPOSITEDITDATE" => (isset($aDepositEdit) && $aDepositEdit)?$aDepositEdit[0]["Date"]:$sDateDeposit,
		"VAR_DEPOSITNOTES" => (isset($aDepositEdit) && $aDepositEdit)?$aDepositEdit[0]["Notes"]:"",
		"VAR_DEPOSITPRICE" => (isset($aDepositEdit) && $aDepositEdit)?str_replace(",", "", number_format($aDepositEdit[0]["Price"], 0)):"",
		"VAR_DEPOSITMONTHDISABLED" => "disabled=1",
		"VAR_FORM_ACTION" => "retail/deposit.php"
	));

	$cWebsite->template->set_block("navigation", "navigation_top_retail");
	//hide purchase link in navigation if user is not allowed to do any purchase
	$cWebsite->template->set_block("navigation_top_retail", "purchaseLinkNav_block");
	$cWebsite->template->set_block("navigation_top_retail", "purchaseReportNav_block");
	if ($_SESSION['allow_purchase_page'] == 0)
	{
		$cWebsite->template->parse("purchaseLinkNav_block", "");
		$cWebsite->template->parse("purchaseReportNav_block", "");
	}
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_retail");
	
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
			"VAR_LISTDEPOSITPRICE" => number_format( $aDepositList[$i]['Price'], _NbOfDigitBehindComma_ ) . (($aDepositList[$i]['salesPayment_ID'] > 0)?"*":""),
			"VAR_EDIT_DISABLED" => ($aDepositList[$i]['salesPayment_ID'] > 0)?"disabled=1":""
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
