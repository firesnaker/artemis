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
	* retail/reportDailyPrint.php :: Retail Report Daily Print Page			*
	****************************************************************************
	* The daily report print page for retail							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-10-05 									*
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
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classPaymentType.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classExpenses.php");
	include_once($libPath . "/classDeposit.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cPaymentType = new PaymentType;
	$cProduct = new Product;
	$cExpenses = new Expenses;
	$cDeposit = new Deposit;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 && $_GET['reportYearBegin'] <> "") //$_GET is always set, so we check by # of element
		{
			$sReportDateBegin = $_GET['reportYearBegin'] . "-" . $_GET['reportMonthBegin'] . "-" . $_GET['reportDayBegin'];
			$sReportDateBeginForPrint = date( "d-M-Y", mktime(0,0,0, $_GET['reportMonthBegin'], $_GET['reportDayBegin'], $_GET['reportYearBegin']) );
			
			$sReportDateEnd = $_GET['reportYearEnd'] . "-" . $_GET['reportMonthEnd'] . "-" . $_GET['reportDayEnd'];
			$sReportDateEndForPrint = date( "d-M-Y", mktime(0,0,0, $_GET['reportMonthEnd'], $_GET['reportDayEnd'], $_GET['reportYearEnd']) );
		}
		else
		{
			$sReportDateBegin = date("Y-m-d");
			$sReportDateBeginForPrint = date("d-M-Y");

			$sReportDateEnd = date("Y-m-d");
			$sReportDateEndForPrint = date("d-M-Y");
		}

		if ( count($_GET) > 0 && $_GET['reportClient'] <> "") //$_GET is always set, so we check by # of element
		{
			$aClientData = $cClient->GetClientByID($_GET['reportClient']);
			$sReportClient = $aClientData[0]["Name"];
		}
		else
		{
			$sReportClient = "All Client";
		}

		if ( count($_GET) > 0 && $_GET['reportEmployee'] <> "") //$_GET is always set, so we check by # of element
		{
			$aEmployeeData = $cEmployee->GetEmployeeByID($_GET['reportEmployee']);
			$sReportEmployee = $aEmployeeData[0]["Name"];
		}
		else
		{
			$sReportEmployee = "All Employee";
		}

		if ( count($_GET) > 0 && $_GET['reportProduct'] <> "") //$_GET is always set, so we check by # of element
		{
			$aProductData = $cProduct->GetProductByID($_GET['reportProduct']);
			$sReportProduct = $aProductData[0]["Name"];
		}
		else
		{
			$sReportProduct = "All Product";
		}

		if ( count($_GET) > 0 && $_GET['reportProductCategory'] <> "") //$_GET is always set, so we check by # of element
		{
			$aProductCategoryData = $cProduct->GetCategoryByID($_GET['reportProductCategory']);		
			$sReportProductCategory = $aProductCategoryData[0]['Name'];
		}
		else
		{
			$sReportProductCategory = "All Product Category";
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array();		
		if ( $_SESSION['outlet_ID'] > 0 )
		{
			$aSearchByFieldArray = array(
				"outlet_ID" => $_SESSION['outlet_ID'],
				"Date" => "BETWEEN '" . $sReportDateBegin . "' AND '" . $sReportDateEnd . "'",
				"sales.client_ID" => $_GET['reportClient'],
				"sales.employee_ID" => $_GET['reportEmployee'],
				"sales_detail.product_ID" => $_GET['reportProduct'],
				"productCategory_ID" => (isset($_GET['reportProductCategory']) && $_GET['reportProductCategory'])?$_GET['reportProductCategory']:"",
			);
		}
		$aSalesList = $cSales->GetSalesReport($aSearchByFieldArray);

		$aExpensesSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID'],
			"Date" => "BETWEEN '" . $sReportDateBegin . "' AND '" . $sReportDateEnd . "'"
		);		
		$aExpensesList = $cExpenses->GetExpensesReport($aExpensesSearchByFieldArray);

		$aDepositSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID'],
			"Date" => "BETWEEN '" . $sReportDateBegin . "' AND '" . $sReportDateEnd . "'"
		);		
		$aDepositList = $cDeposit->GetDepositReport($aDepositSearchByFieldArray);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "retail/reportDailyPrint.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_REPORTDATE" => $sReportDateBeginForPrint . " To " . $sReportDateEndForPrint,
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_REPORTCLIENT" => $sReportClient,
		"VAR_REPORTEMPLOYEE" => $sReportEmployee,
		"VAR_REPORTPRODUCT" => $sReportProduct,
		"VAR_REPORTPRODUCTCATEGORY" => $sReportProductCategory,
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));

	//expensesListBlock
	$expensesListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aExpensesList); $i++)
	{
		$iGrandTotal += $aExpensesList[$i]['Price'];

		list($sYear, $sMonth, $sDay) = explode("-",$aExpensesList[$i]['Date']);

		$expensesListBlock[] = array(
			"VAR_EXPENSESCOUNTER" => $i+1,
			"VAR_EXPENSESROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_LISTEXPENSESID" => $aExpensesList[$i]['ID'],
			"VAR_LISTEXPENSESDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_LISTEXPENSESNAME" => $aExpensesList[$i]['Name'],
			"VAR_LISTEXPENSESPRICE" => number_format($aExpensesList[$i]['Price'], _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("site", "expensesList", $expensesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALEXPENSES" => number_format($iGrandTotal, _NbOfDigitBehindComma_)
	));

	//depositListBlock
	$depositListBlock = array();
	$iGrandTotalDeposit = 0;
	for ($i = 0; $i < count($aDepositList); $i++)
	{
		$iGrandTotalDeposit += $aDepositList[$i]['Price'];
		
		list($sYear, $sMonth, $sDay) = explode("-",$aDepositList[$i]['Date']);

		$depositListBlock[] = array(
			"VAR_DEPOSITCOUNTER" => $i+1,
			"VAR_DEPOSITROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_LISTDEPOSITID" => $aDepositList[$i]['ID'],
			"VAR_LISTDEPOSITDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_LISTDEPOSITPRICE" => number_format($aDepositList[$i]['Price'], _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("site", "depositList", $depositListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALDEPOSIT" => number_format($iGrandTotalDeposit, _NbOfDigitBehindComma_)
	));

	//salesRecapListBlock
	//group the sales by product
	$aResultRecap = array();
	$aProductRecap = array();
	for ($i = 0; $i < count($aSalesList); $i++)
	{
		if ( !array_key_exists($aSalesList[$i]['product_ID'], $aProductRecap) )
		{
			$aProductRecap[$aSalesList[$i]['product_ID']] = $aSalesList[$i]['Date'];

			$aResultRecap[$aSalesList[$i]['product_ID']] = array();

			$aResultRecap[$aSalesList[$i]['product_ID']][$aSalesList[$i]['Date']] = array(
				"quantity" => $aSalesList[$i]['Quantity'],
				"subtotal" => $aSalesList[$i]['Quantity'] * $aSalesList[$i]['Price']
			);
		}
		else
		{
			//check for the date
			if ( !array_key_exists($aSalesList[$i]['Date'], $aResultRecap[$aSalesList[$i]['product_ID']]) )
			{
				$aResultRecap[$aSalesList[$i]['product_ID']][$aSalesList[$i]['Date']] = array(
					"quantity" => $aSalesList[$i]['Quantity'],
					"subtotal" => $aSalesList[$i]['Quantity'] * $aSalesList[$i]['Price']
				);
			}
			else
			{
				$aResultRecap[$aSalesList[$i]['product_ID']][$aSalesList[$i]['Date']]['quantity'] += $aSalesList[$i]['Quantity'];
				$aResultRecap[$aSalesList[$i]['product_ID']][$aSalesList[$i]['Date']]['subtotal'] += $aSalesList[$i]['Quantity'] * $aSalesList[$i]['Price'];
			}
		}
	}
	$salesRecapListBlock = array();
	$iGrandtotal = 0;
	$i = 1;
	foreach ($aResultRecap as $iProductID => $aProductDate)
	{
		foreach ($aProductDate as $sDate => $aProductData)
		{
			$iGrandtotal += $aProductData['subtotal'];
	
			list($sYear, $sMonth, $sDay) = explode("-",$sDate);
	
			$salesRecapListBlock[] = array(
				"VAR_SALESRECAPROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
				"VAR_SALESRECAPCOUNTER" => $i++,
				"VAR_SALESRECAPDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
				"VAR_SALESRECAPPRODUCTNAME" => $cProduct->GetProductNameByID($iProductID),
				"VAR_SALESRECAPQUANTITY" => number_format($aProductData['quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SALESRECAPSUBTOTAL" => number_format($aProductData['subtotal'], _NbOfDigitBehindComma_ )
			);
		}
	}
	$cWebsite->buildBlock("site", "salesRecapListBlock", $salesRecapListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALSALESRECAP" => number_format($iGrandtotal, _NbOfDigitBehindComma_ ),
	));

	//inventoryListBlock
	$reportListBlock = array();
	$iQuantitytotal = 0;
	$iGrandtotal = 0;
	$iCashtotal = 0;
	$iDebittotal = 0;
	$iTransfertotal = 0;
	for ($i = 0; $i < count($aSalesList); $i++)
	{
		$aProductName = $cProduct->GetProductByID($aSalesList[$i]['product_ID']);
		$sProductName = $aProductName[0]['Name'];

		$aEmployeeName = $cEmployee->GetEmployeeByID($aSalesList[$i]['employee_ID']);
		$sEmployeeName = $aEmployeeName[0]['Name'];

		$aClientName = $cClient->GetClientByID($aSalesList[$i]['client_ID']);
		$sClientName = $aClientName[0]['Name'];

		$aPaymentTypeName = $cPaymentType->GetPaymentTypeByID($aSalesList[$i]['paymentType_ID']);
		$sPaymentTypeName = $aPaymentTypeName[0]['Name'];

		$iQuantitytotal += $aSalesList[$i]['Quantity'];

		$iTotal = $aSalesList[$i]['Price'] * $aSalesList[$i]['Quantity'] * ( (100 - $aSalesList[$i]['Discount']) / 100 );
		$iGrandtotal += $iTotal;

		//reset all iCash, iDebit and iTransfer so new loop will show as 0
		$iCash = 0;
		$iDebit = 0;
		$iTransfer = 0;
		switch(strtolower($sPaymentTypeName))
		{
			case "cash":
				$iCash = $iTotal;
				$iCashtotal += $iCash;
			break;
			case "debit":
				$iDebit = $iTotal;
				$iDebittotal += $iDebit;
			break;
			case "transfer":
				$iTransfer = $iTotal;
				$iTransfertotal += $iTransfer;
			break;
			default:
			break;
		}

		list($sYear, $sMonth, $sDay) = explode("-",$aSalesList[$i]['Date']);

		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_DATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_SALESNAME" => $sEmployeeName,
			"VAR_PRODUCTNAME" => $sProductName,
			"VAR_CLIENTNAME" => $sClientName,
			"VAR_PAYMENTTYPENAME" => $sPaymentTypeName,
			"VAR_NOTES" => ( (($aSalesList[$i]['sales_order_ID'] > 0)?"<span style='color:red'>PP</span> ":"") . $aSalesList[$i]['Notes'] ),
			"VAR_PRODUCTNAME" => $cProduct->GetProductNameByID($aSalesList[$i]['product_ID']),
			"VAR_PRICE" => number_format($aSalesList[$i]['Price'], _NbOfDigitBehindComma_ ),
			"VAR_DISCOUNT" => number_format($aSalesList[$i]['Discount'], 2 ),
			"VAR_QUANTITY" => number_format($aSalesList[$i]['Quantity'], _NbOfDigitBehindComma_ ),
			"VAR_TOTAL" => number_format($iTotal, _NbOfDigitBehindComma_ ),
			"VAR_CASH" => number_format($iCash, _NbOfDigitBehindComma_ ),
			"VAR_DEBIT" => number_format($iDebit, _NbOfDigitBehindComma_ ),
			"VAR_TRANSFER" => number_format($iTransfer, _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("site", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_QANTITYTOTAL" => number_format($iQuantitytotal, _NbOfDigitBehindComma_ ),
		"VAR_GRANDTOTAL" => number_format($iGrandtotal, _NbOfDigitBehindComma_ ),
		"VAR_CASHTOTAL" => number_format($iCashtotal, _NbOfDigitBehindComma_ ),
		"VAR_DEBITTOTAL" => number_format($iDebittotal, _NbOfDigitBehindComma_ ),
		"VAR_TRANSFERTOTAL" => number_format($iTransfertotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
