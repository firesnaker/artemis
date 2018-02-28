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
	* master/reportSalesDailySave.php :: Master Sales Daily Report Page		*
	****************************************************************************
	* The full sales daily report print page for master						*
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
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
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
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cExpenses = new Expenses;
	$cExport = new Export;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
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
		"site" => "master/reportSalesDaily.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

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
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_SALESDATEDB" => $aSalesList[$i]['Date'],
			"VAR_OUTLETID" => $aSalesList[$i]['outlet_ID'],
			"VAR_SALESNAME" => $sEmployeeName,
			"VAR_CLIENTNAME" => $sClientName,
			"VAR_PRODUCTNAME" => $cProduct->GetProductNameByID($aSalesList[$i]['product_ID']),
			"VAR_NOTES" => $aSalesList[$i]['Notes'],
			"VAR_PRICE" => $aSalesList[$i]['Price'],
			"VAR_QUANTITY" => $aSalesList[$i]['Quantity'],
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
			"VAR_TOTALSALES" => number_format($reportListBlockSummary[$i]["Total"], _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_ ),
			"VAR_TOTALEXPENSES" => number_format($iTotalExpenses, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ),
			"VAR_SUBTOTAL" => number_format( ($reportListBlockSummary[$i]["Total"] - $iTotalExpenses), _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_   ),
			"VAR_TOTALSALES_VIRGIN" => $reportListBlockSummary[$i]["Total"],
			"VAR_TOTALEXPENSES_VIRGIN" => $iTotalExpenses,
			"VAR_SUBTOTAL_VIRGIN" =>  ($reportListBlockSummary[$i]["Total"] - $iTotalExpenses),
			"VAR_CASH" => $i,
			"VAR_DEBIT" => $i,
			"VAR_TRANSFER" => $i
		);
		$iGrandTotalSales += $reportListBlockSummary[$i]["Total"];
	}
	
	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALSALES" => number_format($iGrandTotalSales, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ),
		"VAR_GRANDTOTALEXPENSES" => number_format($iGrandTotalExpenses, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ),
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  )
	));

	//prepare the data
	$aContent = array();
	$aContent[] = array("Date", "Outlet", "Total Sales", "Total Expenses", "Subtotal");
	foreach ($aReportSalesListBlock as $iKey => $aData)
	{
		$aContent[] = array($aData["VAR_SALESDATE"], $aData["VAR_OUTLETNAME"], $aData["VAR_TOTALSALES_VIRGIN"], $aData["VAR_TOTALEXPENSES_VIRGIN"], $aData["VAR_SUBTOTAL_VIRGIN"]);
	}
	//generate the grandtotal
	$aContent[] = array("", "", $iGrandTotalSales, $iGrandTotalExpenses, $iGrandTotal);

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('reportSalesDailySave-' . $sSearchOutletName . '-' . $sBeginDate . '-' . $sEndDate . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
