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
	* master/reportSalesDailyPrint.php :: Master Index Page					*
	****************************************************************************
	* The full report print page for master								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
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
	include_once($libPath . "/classExpenses.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cExpenses = new Expenses;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Report Sales Daily Print";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_GET is always set, so we check by # of element
		{
			$sReportDate = $_GET['reportYear'] . "-" . $_GET['reportMonth'] . "-" . $_GET['reportDay'];
			$sBeginDate = $_GET['beginYear'] . "-" . $_GET['beginMonth'] . "-" . $_GET['beginDay'];
			$sEndDate = $_GET['endYear'] . "-" . $_GET['endMonth'] . "-" . $_GET['endDay'];
		}
		else
		{
			$sReportDate = date("Y-m-d");
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}

		//+++ END $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"outlet_ID" => ($_GET['reportOutlet'])?$_GET['reportOutlet']:"",
			"employee_ID" => ($_GET['reportEmployee'])?$_GET['reportEmployee']:"",
			"client_ID" => ($_GET['reportClient'])?$_GET['reportClient']:"",
			"product_ID" => ($_GET['reportProduct'])?$_GET['reportProduct']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aSalesList = $cSales->GetSalesReport($aSearchByFieldArray);
		$aOutletList = $cOutlet->GetActiveOutletList();
		$aEmployeeList = $cEmployee->GetEmployeeList();
		$aClientList = $cClient->GetClientList();
		$aProductList = $cProduct->GetProductList();

		$sSearchOutletName = "All Outlets";		
		if ($_GET['reportOutlet'] > 0)
		{
			$aSearchOutletData = $cOutlet->GetOutletByID($_GET['reportOutlet']);		
			$sSearchOutletName = $aSearchOutletData[0]['Name'];
		}

		$sSearchEmployeeName = "All Employees";		
		if ($_GET['reportEmployee'] > 0)
		{
			$aSearchEmployeeData = $cEmployee->GetEmployeeByID($_GET['reportEmployee']);		
			$sSearchEmployeeName = $aSearchEmployeeData[0]['Name'];
		}

		$sSearchClientName = "All Clients";		
		if ($_GET['reportClient'] > 0)
		{
			$aSearchClientData = $cClient->GetClientByID($_GET['reportClient']);		
			$sSearchClientName = $aSearchClientData[0]['Name'];
		}

		$sSearchProductName = "All Products";		
		if ($_GET['reportProduct'] > 0)
		{
			$aSearchProductData = $cProduct->GetProductByID($_GET['reportProduct']);		
			$sSearchProductName = $aSearchProductData[0]['Name'];
		}

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "master/reportSalesDailyPrint.htm"
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
		"VAR_SEARCHOUTLETNAME" => $sSearchOutletName,
		"VAR_SEARCHEMPLOYEENAME" => $sSearchEmployeeName,
		"VAR_SEARCHCLIENTNAME" => $sSearchClientName,
		"VAR_SEARCHPRODUCTNAME" => $sSearchProductName,
		"VAR_SEARCHBEGINDATE" => date("d-M-Y", mktime(0,0,0,$_GET['beginMonth'], $_GET['beginDay'], $_GET['beginYear'])), 
		"VAR_SEARCHENDDATE" => date("d-M-Y", mktime(0,0,0,$_GET['endMonth'], $_GET['endDay'], $_GET['endYear'])),
		"TEXT_REPORT" => "Report",
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));
	
	//beginDayBlock
	$beginDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_GET['beginDay']) )
		{
			$sDefaultBeginDay = $_GET['beginDay'];
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
		if ( isset($_GET['beginMonth']) )
		{
			$sDefaultBeginMonth = $_GET['beginMonth'];
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
	for ($i = 0; $i < 2; $i++)
	{
		if ( isset($_GET['beginYear']) )
		{
			$sDefaultBeginYear = $_GET['beginYear'];
		}
		else
		{
			$sDefaultBeginYear = date("Y");
		}
		$beginYearBlock[] = array(
			"VAR_BEGINYEARVALUE" => date("Y") - $i,
			"VAR_BEGINYEARSELECTED" => ( date("Y") - $i == $sDefaultBeginYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "beginYearBlock", $beginYearBlock);

	//endDayBlock
	$endDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_GET['endDay']) )
		{
			$sDefaultEndDay = $_GET['endDay'];
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
		if ( isset($_GET['endMonth']) )
		{
			$sDefaultEndMonth = $_GET['endMonth'];
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
	for ($i = 0; $i < 2; $i++)
	{
		if ( isset($_GET['endYear']) )
		{
			$sDefaultEndYear = $_GET['endYear'];
		}
		else
		{
			$sDefaultEndYear = date("Y");
		}
		$endYearBlock[] = array(
			"VAR_ENDYEARVALUE" => date("Y") - $i,
			"VAR_ENDYEARSELECTED" => ( date("Y") - $i == $sDefaultEndYear)?"selected":""
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
			"VAR_OUTLETSELECTED" => ($aOutletList[$i]['ID'] == $_GET['reportOutlet'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);

	//employeeListBlock
	$employeeListBlock = array();
	for ($i = 0; $i < count($aEmployeeList); $i++)
	{
		$employeeListBlock[] = array(
			"VAR_EMPLOYEEID" => $aEmployeeList[$i]['ID'],
			"VAR_EMPLOYEENAME" => $aEmployeeList[$i]['Name'],
			"VAR_EMPLOYEESELECTED" => ($aEmployeeList[$i]['ID'] == $_GET['reportEmployee'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "employeeListBlock", $employeeListBlock);

	//clientListBlock
	$clientListBlock = array();
	for ($i = 0; $i < count($aClientList); $i++)
	{
		$clientListBlock[] = array(
			"VAR_CLIENTID" => $aClientList[$i]['ID'],
			"VAR_CLIENTNAME" => $aClientList[$i]['Name'],
			"VAR_CLIENTSELECTED" => ($aClientList[$i]['ID'] == $_GET['reportClient'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "clientListBlock", $clientListBlock);

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_PRODUCTSELECTED" => ($aProductList[$i]['ID'] == $_GET['reportProduct'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productListBlock", $productListBlock);

	//inventoryListBlock
	$iGrandTotal = 0;
	$reportListBlock = array();
	for ($i = 0; $i < count($aSalesList); $i++)
	{
		$aEmployeeName = $cEmployee->GetEmployeeByID($aSalesList[$i]['employee_ID']);
		$sEmployeeName = $aEmployeeName[0]['Name'];

		$aClientName = $cClient->GetClientByID($aSalesList[$i]['client_ID']);
		$sClientName = $aClientName[0]['Name'];

		$aOutletName = $cOutlet->GetOutletByID($aSalesList[$i]['outlet_ID']);
		$sOutletName = $aOutletName[0]['Name'];

		list($sYear, $sMonth, $sDay) = explode("-",$aSalesList[$i]['Date']);
		
		$iSubtotal = $aSalesList[$i]['Price'] * $aSalesList[$i]['Quantity'] * ( (100 - $aSalesList[$i]['Discount']) / 100);

		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SALESDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_SALESDATEDB" => $aSalesList[$i]['Date'],
			"VAR_OUTLETID" => $aSalesList[$i]['outlet_ID'],
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_TOTAL" => $iSubtotal,
			"VAR_CASH" => $i,
			"VAR_DEBIT" => $i,
			"VAR_TRANSFER" => $i
		);

		$iGrandTotal += $iSubtotal;
	}

	$reportListBlockSummary = array();
	for($i = 0; $i < count($reportListBlock); $i++)
	{
		$sDate = $reportListBlock[$i]['VAR_SALESDATE'];
		$sDateDB = $reportListBlock[$i]['VAR_SALESDATEDB'];
		$iOutletID = $reportListBlock[$i]['VAR_OUTLETID'];
		$sOutletName = $reportListBlock[$i]['VAR_OUTLETNAME'];
		if ($i == 0)
		{
			$reportListBlockSummary[] = array(
				"Date" => $sDate,
				"DateDB" => $sDateDB,
				"OutletID" => $iOutletID,
				"OutletName" => $sOutletName,
				"Total" => $reportListBlock[$i]['VAR_TOTAL']
			);
		}
		else
		{
			$bMatchFound = FALSE;
			for ($j = 0; $j < count($reportListBlockSummary); $j++)
			{
				if (
					$reportListBlockSummary[$j]["Date"] == $reportListBlock[$i]['VAR_SALESDATE']
					&&
					$reportListBlockSummary[$j]["OutletName"] == $reportListBlock[$i]['VAR_OUTLETNAME']
				)
				{
					$reportListBlockSummary[$j]["Total"] += $reportListBlock[$i]['VAR_TOTAL'];
					$bMatchFound = TRUE;
				}
			}
			if ($bMatchFound == FALSE)
			{
				$reportListBlockSummary[] = array(
					"Date" => $sDate,
					"DateDB" => $sDateDB,
					"OutletID" => $iOutletID,
					"OutletName" => $sOutletName,
					"Total" => $reportListBlock[$i]['VAR_TOTAL']
				);
			}
		}
	}

	$iGrandTotalSales = 0;
	$iGrandTotalExpenses = 0;
	$aReportSalesListBlock = array();
	for ($i = 0; $i < count($reportListBlockSummary); $i++)
	{
		$aExpensesSearchByFieldArray = array(
			"outlet_ID" => $reportListBlockSummary[$i]["OutletID"],
			"Date" => $reportListBlockSummary[$i]["DateDB"]
		);
		$aExpensesList = $cExpenses->GetExpensesList($aExpensesSearchByFieldArray);
		$iTotalExpenses = 0;
		for ($j = 0; $j < count($aExpensesList); $j++)
		{
			$iTotalExpenses += $aExpensesList[$j]["Price"];
		}
		$iGrandTotal -= $iTotalExpenses;
		$iGrandTotalExpenses += $iTotalExpenses;

		$aReportSalesListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SALESDATE" => $reportListBlockSummary[$i]["Date"],
			"VAR_OUTLETNAME" => $reportListBlockSummary[$i]["OutletName"],
			"VAR_TOTALSALES" => number_format($reportListBlockSummary[$i]["Total"], _NbOfDigitBehindComma_ ),
			"VAR_TOTALEXPENSES" => number_format($iTotalExpenses, _NbOfDigitBehindComma_ ),
			"VAR_SUBTOTAL" => number_format( ($reportListBlockSummary[$i]["Total"] - $iTotalExpenses), _NbOfDigitBehindComma_ ),
			"VAR_CASH" => $i,
			"VAR_DEBIT" => $i,
			"VAR_TRANSFER" => $i
		);
		$iGrandTotalSales += $reportListBlockSummary[$i]["Total"];
	}
	$cWebsite->buildBlock("site", "reportListBlock", $aReportSalesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => ($_POST['reportOutlet'])?$_POST['reportOutlet']:"0",
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear,
		
		"VAR_GRANDTOTALSALES" => number_format($iGrandTotalSales, _NbOfDigitBehindComma_ ),
		"VAR_GRANDTOTALEXPENSES" => number_format($iGrandTotalExpenses, _NbOfDigitBehindComma_ ),
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
