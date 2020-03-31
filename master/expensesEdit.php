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
	* master/expensesEdit.php :: Master Expenses Page						*
	****************************************************************************
	* The expenses page for master										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-02-10 									*
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
	include_once($libPath . "/classExpenses.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cExpenses = new Expenses;
	$cUser = new User($_SESSION['user_ID']);
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sDate = date("d-M-Y");
	$sDatePrint = date("Y-m-d");
	$sDateExpenses = date("Y-m-d");
	$sFormElementDisabled = "";
	$sPageName = "Expenses Edit";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ($_POST["expensesSubmit"] == "Save")
			{
				$aExpensesForm = array(
					"ID" => $_POST["expenses_ID"],
					"outlet_ID" => $_POST["outlet_ID"],
					"expenses_category_ID" => $_POST["expenses_category"],
					"Date" => $_POST["expenses_Edit_Date"],
					"Name" => $_POST["expenses_notes"],
					"Price" => $_POST["expenses_price"]
				);
				if ( $_POST["expenses_ID"] == 0 )
				{
					//$iExpensesID = $cExpenses->Insert($aExpensesForm);
					$iExpensesID = $aExpensesForm['ID'];
				}
				else
				{
					$iExpensesID = $cExpenses->Update($aExpensesForm);
				}
				$iExpensesID = $aExpensesForm['ID'];
			}

			if ($_POST["expensesEditSubmit"] == "Edit")
			{
				$aExpensesEdit = $cExpenses->GetExpensesByID($_POST["expensesEdit_ID"]);
			}

			if ($_POST["expensesDeleteSubmit"] == "Delete")
			{
				$iExpensesID = $cExpenses->Remove($_POST["expensesDelete_ID"]);
				header("location:reportExpenses.php");
				exit();
			}

			$sExpensesMonth = date("m");
			if ( isset($_POST["expenses_month"]) && $_POST["expenses_month"] )
			{
				$sExpensesMonth = $_POST["expenses_month"];
			}
			if ( $_POST["expenses_day"] && $sExpensesMonth && $_POST["expenses_year"] )
			{
				$sDateExpenses = $_POST["expenses_year"] . "-" . $sExpensesMonth . "-" . $_POST["expenses_day"]; 
			}

			if ( isset($_POST["expensesEditSubmit"]) && $_POST['expensesEdit_ID'] > 0 )
			{
				$iExpensesID = $_POST['expensesEdit_ID'];
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		//get product list visible on website
		$aExpensesSearchByFieldArray = array(
			"ID" => $iExpensesID
		);		
		$aExpensesList = $cExpenses->GetExpensesList($aExpensesSearchByFieldArray);

		if (count($aExpensesList) > 0)
		{
				$sFormElementDisabled = "";
		}
		else
		{
			//disable edit
			$sFormElementDisabled = "disabled='1'";
		}
		
		$aOutletData = $cOutlet->GetOutletByID($aExpensesList[0]['outlet_ID']);

		$aSearchParam = array();
		$aExpensesCategoryList = $cExpenses->GetExpensesCategoryList($aSearchParam);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/expensesEdit.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	list($year, $month, $day) = explode("-", $sDateExpenses);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_USERLOGGEDIN" => ucfirst($_SESSION['user_Name']),
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_EXPENSESDATE" => date( "d-M-Y", mktime(0,0,0, $month, $day, $year) ),
		"VAR_EXPENSESDATEPRINT" => $sDateExpenses,
		"VAR_EXPENSESDAY" => $day,
		"VAR_EXPENSESMONTH" => $month,
		"VAR_EXPENSESYEAR" => $year,
		"VAR_OUTLETNAME" => $aOutletData[0]["Name"],
		"VAR_OUTLETID" => $aExpensesList[0]['outlet_ID'],
		"VAR_ELEMENTDISABLED" => $sFormElementDisabled,
		"VAR_SAVEDISABLED" => ($aExpensesEdit[0]["ID"] <> 0 )?"":"disabled=1",
		"VAR_EXPENSESID" => ($aExpensesEdit)?$aExpensesEdit[0]["ID"]:"",
		"VAR_EXPENSESEDITDATE" => ($aExpensesEdit)?$aExpensesEdit[0]["Date"]:$sDateExpenses,
		"VAR_EXPENSESNOTES" => ($aExpensesEdit)?$aExpensesEdit[0]["Name"]:"",
		"VAR_EXPENSESPRICE" => ($aExpensesEdit)?str_replace(",", "", number_format($aExpensesEdit[0]["Price"], 0)):"",
		//"VAR_EXPENSESMONTHDISABLED" => "disabled=1"
	));

	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	//expensesDayBlock
	$expensesDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['expenses_day']) )
		{
			$sDefaultDay = $_POST['expenses_day'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$expensesDayBlock[] = array(
			"VAR_DAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "expensesDayBlock", $expensesDayBlock);

	//expensesMonthBlock
	$expensesMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['expenses_month']) )
		{
			$sDefaultMonth = $_POST['expenses_month'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$expensesMonthBlock[] = array(
			"VAR_MONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "expensesMonthBlock", $expensesMonthBlock);

	//expensesYearBlock
	$expensesYearBlock = array();
	for ($i = 0; $i < (date("Y") - 2010); $i++)
	{
		if ( isset($_POST['expenses_year']) )
		{
			$sDefaultYear = $_POST['expenses_year'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$expensesYearBlock[] = array(
			"VAR_YEARVALUE" => date("Y") - $i,
			"VAR_YEARSELECTED" => ( date("Y") - $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "expensesYearBlock", $expensesYearBlock);

	//expensesCategoryListBlock
	$expensesCategoryListBlock = array();
	for ($i = 0; $i < count($aExpensesCategoryList); $i++)
	{
		$expensesCategoryListBlock[] = array(
			"VAR_EXPENSESCATEGORYVALUE" => $aExpensesCategoryList[$i]['ID'],
			"VAR_EXPENSESCATEGORYNAME" => $aExpensesCategoryList[$i]['Name'],
			"VAR_EXPENSESCATEGORYSELECTED" => ($aExpensesCategoryList[$i]['ID'] == $aExpensesEdit[0]["expenses_category_ID"])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "expensesCategoryListBlock", $expensesCategoryListBlock);

	//expensesListBlock
	$expensesListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aExpensesList); $i++)
	{
		$iGrandTotal += $aExpensesList[$i]['Price'];

		$aCategoryData = $cExpenses->LoadExpensesCategory($aExpensesList[$i]['expenses_category_ID']);
		$sCategoryName = $aCategoryData[0]["Name"];

		$expensesListBlock[] = array(
			"VAR_COUNTER" => $i+1,
			"VAR_LISTEXPENSESID" => $aExpensesList[$i]['ID'],
			"VAR_LISTEXPENSESCATEGORY" => ($sCategoryName == '')?'-':$sCategoryName,
			"VAR_LISTEXPENSESNOTES" => $aExpensesList[$i]['Name'],
			"VAR_LISTEXPENSESPRICE" => number_format( $aExpensesList[$i]['Price'], _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("content", "expensesList", $expensesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTAL" => number_format( $iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
