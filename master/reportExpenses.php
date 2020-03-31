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
	* master/reportExpenses.php :: Master Index Page						*
	****************************************************************************
	* The daily report page for master									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [FireSnakeR ]					*
	* Created			: 2010-08-08 									*
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
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classExpenses.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cUser = new User($_SESSION['user_ID']);
	$cExpenses = new Expenses;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	$sPageName = "Report Expenses";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDate = $_POST['reportYear'] . "-" . $_POST['reportMonth'] . "-" . $_POST['reportDay'];
			$sBeginDate = $_POST['beginYear'] . "-" . $_POST['beginMonth'] . "-" . $_POST['beginDay'];
			$sEndDate = $_POST['endYear'] . "-" . $_POST['endMonth'] . "-" . $_POST['endDay'];

			//process verify
			if ( isset($_POST['expensesVerifySubmit']) && $_POST['expensesVerifySubmit'] == 'EDIT' )
			{
				$cExpenses->Verify($_POST['expensesVerify_ID'], $_POST['expensesVerify_FinanceNotes']);
			}
		}
		else
		{
			$sReportDate = date("Y-m-d");
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"outlet_ID" => ($_POST['reportOutlet'])?$_POST['reportOutlet']:"",
			"expenses_category_ID" => ($_POST['reportCategory'])?$_POST['reportCategory']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aOutletList = $cOutlet->GetActiveOutletList();
		$aCategoryList = $cExpenses->GetExpensesCategoryList(array());
		$aExpensesList = $cExpenses->GetExpensesReport($aSearchByFieldArray, array(), array());
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/reportExpenses.htm"
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
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Report"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	//beginDayBlock
	$beginDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['beginDay']) )
		{
			$sDefaultBeginDay = $_POST['beginDay'];
		}
		else
		{
			$sDefaultBeginDay = date("d");
		}
		$beginDayBlock[] = array(
			"VAR_BEGINDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_BEGINDAYSELECTED" => ( ($i+1) == $sDefaultBeginDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "beginDayBlock", $beginDayBlock);

	//beginMonthBlock
	$beginMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['beginMonth']) )
		{
			$sDefaultBeginMonth = $_POST['beginMonth'];
		}
		else
		{
			$sDefaultBeginMonth = date("m");
		}
		$beginMonthBlock[] = array(
			"VAR_BEGINMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_BEGINMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_BEGINMONTHSELECTED" => ( ($i+1) == $sDefaultBeginMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "beginMonthBlock", $beginMonthBlock);

	//beginYearBlock
	$beginYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['beginYear']) )
		{
			$sDefaultBeginYear = $_POST['beginYear'];
		}
		else
		{
			$sDefaultBeginYear = date("Y");
		}
		$beginYearBlock[] = array(
			"VAR_BEGINYEARVALUE" => $i,
			"VAR_BEGINYEARSELECTED" => ( $i == $sDefaultBeginYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "beginYearBlock", $beginYearBlock);

	//endDayBlock
	$endDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['endDay']) )
		{
			$sDefaultEndDay = $_POST['endDay'];
		}
		else
		{
			$sDefaultEndDay = date("d");
		}
		$endDayBlock[] = array(
			"VAR_ENDDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_ENDDAYSELECTED" => ( ($i+1) == $sDefaultEndDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "endDayBlock", $endDayBlock);

	//endMonthBlock
	$endMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['endMonth']) )
		{
			$sDefaultEndMonth = $_POST['endMonth'];
		}
		else
		{
			$sDefaultEndMonth = date("m");
		}
		$endMonthBlock[] = array(
			"VAR_ENDMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_ENDMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_ENDMONTHSELECTED" => ( ($i+1) == $sDefaultEndMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "endMonthBlock", $endMonthBlock);

	//endYearBlock
	$endYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['endYear']) )
		{
			$sDefaultEndYear = $_POST['endYear'];
		}
		else
		{
			$sDefaultEndYear = date("Y");
		}
		$endYearBlock[] = array(
			"VAR_ENDYEARVALUE" => $i,
			"VAR_ENDYEARSELECTED" => ( $i == $sDefaultEndYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "endYearBlock", $endYearBlock);

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$outletListBlock[] = array(
			"VAR_OUTLETID" => $aOutletList[$i]['ID'],
			"VAR_OUTLETNAME" => $aOutletList[$i]['name'],
			"VAR_OUTLETSELECTED" => ($aOutletList[$i]['ID'] == $_POST['reportOutlet'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);

	//categoryListBlock
	$categoryListBlock = array();
	for ($i = 0; $i < count($aCategoryList); $i++)
	{
		$categoryListBlock[] = array(
			"VAR_CATEGORYID" => $aCategoryList[$i]['ID'],
			"VAR_CATEGORYNAME" => $aCategoryList[$i]['Name'],
			"VAR_CATEGORYSELECTED" => ($aCategoryList[$i]['ID'] == $_POST['reportCategory'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "categoryListBlock", $categoryListBlock);

	//expensesListBlock
	$expensesListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aExpensesList); $i++)
	{
		list($iYear, $iMonth, $iDay) = explode("-", $aExpensesList[$i]['Date']);
		$iGrandTotal += $aExpensesList[$i]['Price'];

		$aOutletData = $cOutlet->GetOutletByID($aExpensesList[$i]['outlet_ID']);
		$sOutletName = $aOutletData[0]["Name"];

		$aCategoryData = $cExpenses->LoadExpensesCategory($aExpensesList[$i]['expenses_category_ID']);
		$sCategoryName = $aCategoryData[0]["Name"];

		$expensesListBlock[] = array(
			"VAR_COUNTER" => $i+1,
			"VAR_EXPENSESDATE" => date("d-M-Y", mktime(0,0,0, $iMonth, $iDay, $iYear)),
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_CATEGORYNAME" => ($sCategoryName == '')?'-':$sCategoryName,
			"VAR_EXPENSESNAME" => $aExpensesList[$i]['Name'],
			"VAR_EXPENSESID" => $aExpensesList[$i]['ID'],
			"VAR_TOTALEXPENSES" => number_format($aExpensesList[$i]['Price'], _NbOfDigitBehindComma_ ),
			"VAR_STATUS" => ($aExpensesList[$i]['Status'] == 1)?'Verified':'Unverified',
			"VAR_EXPENSESFINANCENOTES" => $aExpensesList[$i]['FinanceNotes'],
		);
	}
	$cWebsite->buildBlock("content", "reportListBlock", $expensesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => ($_POST['reportOutlet'])?$_POST['reportOutlet']:"0",
		"VAR_REPORTCATEGORY" => ($_POST['reportCategory'])?$_POST['reportCategory']:"0",
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear,

		"VAR_GRANDTOTALEXPENSES" => number_format($iGrandTotal, _NbOfDigitBehindComma_)		
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
