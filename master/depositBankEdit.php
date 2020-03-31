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
	* master/depositBankEdit.php :: Master Bank Deposit Edit Page				*
	****************************************************************************
	* The bank deposit edit page for Master								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2014-05-07 									*
	* Last modified	: 2014-08-01									*
	*															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') && !$gate->is_valid_role('user_ID', 'user_Name', 'master') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classBank.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$oBank = new Bank;
	$cUser = new User($_SESSION['user_ID']);
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sDate = date("d-M-Y");
	$sDatePrint = date("Y-m-d");
	$sDateDeposit = date("Y-m-d");
	$sFormElementDisabled = "";
	$sPageName = "Deposit Bank Edit";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ($_POST["depositSubmit"] == "Save")
			{
				$aDepositForm = array(
					"ID" => $_POST["deposit_ID"],
					"outlet_ID" => $_POST["outlet_ID"],
					"bank_ID" => $_POST["deposit_bank"],
					"Date" => $_POST["deposit_Edit_Date"],
					"Notes" => $_POST["deposit_notes"],
					"Price" => $_POST["deposit_price"],
					"FinanceNotes" => $_POST["deposit_financeNotes"],
					"Status" => (isset($_POST["deposit_status"]) && $_POST["deposit_status"] > 0)?$_POST["deposit_status"]:0
				);
				$iDepositID = $oBank->SaveDeposit($aDepositForm);
			}

			if ($_POST["depositDeleteSubmit"] == "Delete")
			{
				$iDepositID = $cDeposit->Remove($_POST["depositDelete_ID"]);
				header("location:reportDepositBank.php");
				exit();
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

			if ( isset($_POST["depositEditSubmit"]) && $_POST['depositEdit_ID'] > 0 )
			{
				$iDepositID = $_POST['depositEdit_ID'];
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//
/*		//get product list visible on website
		$aDepositList = $oBank->LoadDeposit($iDepositID);

		if (count($aDepositList) > 0)
		{
				$sFormElementDisabled = "";
		}
		else
		{
			//disable edit
			$sFormElementDisabled = "disabled='1'";
		}
*/		
		$aDepositEdit = $oBank->LoadDeposit($iDepositID);
		$aOutletData = $cOutlet->GetOutletByID($aDepositEdit[0]['outlet_ID']);
		$aSearchParam = array();
		$aBankList = $oBank->GetList($aSearchParam);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/depositBankEdit.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	list($year, $month, $day) = explode("-", $sDateDeposit);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_USERLOGGEDIN" => ucfirst($_SESSION['user_Name']),
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		//page text
		"VAR_ACTIONURL" => 'master/depositBankEdit.php',
		"VAR_BACKTOLISTURL" => 'master/reportDepositBank.php',
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_DEPOSITDATE" => date( "d-M-Y", mktime(0,0,0, $month, $day, $year) ),
		"VAR_DEPOSITDATEPRINT" => $sDateDeposit,
		"VAR_DEPOSITDAY" => $day,
		"VAR_DEPOSITMONTH" => $month,
		"VAR_DEPOSITYEAR" => $year,
		"VAR_OUTLETNAME" => $aOutletData[0]["Name"],
		"VAR_OUTLETID" => $aDepositEdit[0]['outlet_ID'],
		"VAR_ELEMENTDISABLED" => $sFormElementDisabled,
		"VAR_DEPOSITID" => ($aDepositEdit)?$aDepositEdit[0]["ID"]:"",
		"VAR_DEPOSITEDITDATE" => ($aDepositEdit)?$aDepositEdit[0]["Date"]:$sDateDeposit,
		"VAR_DEPOSITNOTES" => ($aDepositEdit)?$aDepositEdit[0]["Notes"]:"",
		"VAR_DEPOSITPRICE" => ($aDepositEdit)?str_replace(",", "", number_format($aDepositEdit[0]["Price"], 0)):"",
		"VAR_DEPOSITFINANCENOTES" => ($aDepositEdit)?$aDepositEdit[0]["FinanceNotes"]:"",
		"VAR_DEPOSITSTATUS" => (($aDepositEdit) && ($aDepositEdit[0]["Status"] > 0))?"checked='checked'":"",
		//"VAR_DEPOSITMONTHDISABLED" => "disabled=1"
		"VAR_LISTOUTLET" => $_POST['reportOutlet'],
		"VAR_LISTBEGINYEAR" => $_POST['beginYear'],
		"VAR_LISTBEGINMONTH" => $_POST['beginMonth'],
		"VAR_LISTBEGINDAY" => $_POST['beginDay'],
		"VAR_LISTENDYEAR" => $_POST['endYear'],
		"VAR_LISTENDMONTH" => $_POST['endMonth'],
		"VAR_LISTENDDAY" => $_POST['endDay'],
	));

	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

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
	for ($i = 0; $i < (date("Y") - 2010); $i++)
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
			"VAR_YEARVALUE" => date("Y") - $i,
			"VAR_YEARSELECTED" => ( date("Y") - $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "depositYearBlock", $depositYearBlock);

	//bankListBlock
	$bankListBlock = array();
	for ($i = 0; $i < count($aBankList); $i++)
	{
		$bankListBlock[] = array(
			"VAR_BANKID" => $aBankList[$i]['ID'],
			"VAR_BANKSELECTED" => ( $aBankList[$i]['ID'] == $aDepositEdit[0]["bank_ID"])?"selected":"",
			"VAR_BANKNAME" => $aBankList[$i]['Name']
		);
	}
	$cWebsite->buildBlock("content", "bankListBlock", $bankListBlock);

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
