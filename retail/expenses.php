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
	* retail/expenses.php :: Retail Expenses Page						*
	****************************************************************************
	* The expenses page for retail									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-12-14 									*
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
	include_once($libPath . "/classExpenses.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cExpenses = new Expenses;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Expenses";
	$sDate = date("d-M-Y");
	$sDatePrint = date("Y-m-d");
	$sDateExpenses = date("Y-m-d");
	$sFormElementDisabled = "";
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if (isset($_POST["expensesSubmit"]) && $_POST["expensesSubmit"] == "Save")
			{
				$aExpensesForm = array(
					"ID" => $_POST["expenses_ID"],
					"outlet_ID" => $_POST["outlet_ID"],
					"expenses_category_ID" => $_POST["expenses_category"],
					"Date" => $_POST["expenses_Edit_Date"],
					"Name" => $_POST["expenses_name"],
					"Price" => $_POST["expenses_price"]
				);
				if ( $_POST["expenses_ID"] == 0 )
				{
					$iExpensesID = $cExpenses->Insert($aExpensesForm);
				}
				else
				{
					$iExpensesID = $cExpenses->Update($aExpensesForm);
				}				
			}

			if (isset($_POST["expensesEditSubmit"]) && $_POST["expensesEditSubmit"] == "Edit")
			{
				$aExpensesEdit = $cExpenses->GetExpensesByID($_POST["expensesEdit_ID"]);
			}

			if (isset($_POST["expensesDeleteSubmit"]) && $_POST["expensesDeleteSubmit"] == "Delete")
			{
				$iExpensesID = $cExpenses->Remove($_POST["expensesDelete_ID"]);
			}

			$sExpensesMonth = date("m");
			if ( isset($_POST["expenses_month"]) && $_POST["expenses_month"] )
			{
				$sExpensesMonth = $_POST["expenses_month"];
			}
			if ( isset($_POST["expenses_day"]) && $_POST["expenses_day"] && $sExpensesMonth && isset($_POST["expenses_year"]) && $_POST["expenses_year"] )
			{
				$sDateExpenses = $_POST["expenses_year"] . "-" . $sExpensesMonth . "-" . $_POST["expenses_day"]; 
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		//get product list visible on website
		$aExpensesSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID'],
			"Date" => $sDateExpenses
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
		
		//DISABLED CURRENT MONTH FORM ELEMENT


		$aSearchParam = array();
		$aExpensesCategoryList = $cExpenses->GetExpensesCategoryList($aSearchParam);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "retail/expenses.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	list($year, $month, $day) = explode("-", $sDateExpenses);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_PAGEOUTLETNAME" => $_SESSION['outlet_Name'],

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_EXPENSESDATE" => date( "d-M-Y", mktime(0,0,0, $month, $day, $year) ),
		"VAR_EXPENSESDATEPRINT" => $sDateExpenses,
		"VAR_EXPENSESDAY" => $day,
		"VAR_EXPENSESMONTH" => $month,
		"VAR_EXPENSESYEAR" => $year,
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_OUTLETID" => $_SESSION['outlet_ID'],
		"VAR_ELEMENTDISABLED" => $sFormElementDisabled,
		"VAR_EXPENSESID" => (isset($aExpensesEdit) && $aExpensesEdit)?$aExpensesEdit[0]["ID"]:"",
		"VAR_EXPENSESEDITDATE" => (isset($aExpensesEdit) && $aExpensesEdit)?$aExpensesEdit[0]["Date"]:$sDateExpenses,
		"VAR_EXPENSESNAME" => (isset($aExpensesEdit) && $aExpensesEdit)?$aExpensesEdit[0]["Name"]:"",
		"VAR_EXPENSESPRICE" => (isset($aExpensesEdit) && $aExpensesEdit)?number_format( $aExpensesEdit[0]["Price"], 0, "", "" ):"",
		"VAR_EXPENSESMONTHDISABLED" => "disabled=1",
		"VAR_FORM_ACTION" => "retail/expenses.php"
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
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
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
			"VAR_YEARVALUE" => $i,
			"VAR_YEARSELECTED" => ( $i == $sDefaultYear)?"selected":""
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
			"VAR_EXPENSESCATEGORYSELECTED" => (isset($aExpensesEdit) && $aExpensesCategoryList[$i]['ID'] == $aExpensesEdit[0]["expenses_category_ID"])?"selected":""
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
			"VAR_LISTEXPENSESNAME" => $aExpensesList[$i]['Name'],
			"VAR_LISTEXPENSESCATEGORY" => ($sCategoryName == '')?'-':$sCategoryName,
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
