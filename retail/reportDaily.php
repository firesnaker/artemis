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
	* retail/reportDaily.php :: Retail Daily Report Page					*
	****************************************************************************
	* The daily report page for retail									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-08-08 									*
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
	$sPageName = "Daily Report";
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDateBegin = $_POST['reportYearBegin'] . "-" . $_POST['reportMonthBegin'] . "-" . $_POST['reportDayBegin'];
			$sReportDateEnd = $_POST['reportYearEnd'] . "-" . $_POST['reportMonthEnd'] . "-" . $_POST['reportDayEnd'];
		}
		else
		{
			$sReportDateBegin = date("Y-m-d");
			$sReportDateEnd = date("Y-m-d");
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"Date" => "BETWEEN '" . $sReportDateBegin . "' AND '" . $sReportDateEnd . "'",
			"sales.client_ID" => isset($_POST['reportClient'])?$_POST['reportClient']:"",
			"sales.employee_ID" => isset($_POST['reportEmployee'])?$_POST['reportEmployee']:"",
			"sales_detail.product_ID" => (isset($_POST['reportProduct']))?$_POST['reportProduct']:"",
			"productCategory_ID" => (isset($_POST['reportProductCategory']) && $_POST['reportProductCategory'])?$_POST['reportProductCategory']:"",
			"productSpecialTax" => "0",
		);
		if ( $_SESSION['outlet_ID'] > 0 )
		{
			$aSearchByFieldArray["outlet_ID"] = $_SESSION['outlet_ID'];
		}
		if (
			( isset($_POST['reportSpecialTax']) && $_POST['reportSpecialTax'] == 1 )
			|| ( isset($_POST['reportProduct']) && $_POST['reportProduct'] > 0 )
		)
		{
			unset($aSearchByFieldArray["productSpecialTax"]);
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

		//check that employeeList for this outlet does not exists in table employeeOutlet
		$aEmployeeOutletSearchBy = array(
			"outlet_ID" => $_SESSION['outlet_ID']
		);
		$aEmployeeOutletList = $cEmployee->GetEmployeeOutletList($aEmployeeOutletSearchBy, array(), array());
		if ( count($aEmployeeOutletList) == 0 )
		{//if employeeOutlet for this outlet is empty, then get all employee.
			$aEmployeeList = $cEmployee->GetEmployeeList();
		}
		else
		{
			for ($i = 0; $i < count($aEmployeeOutletList); $i++)
			{
				$aEmployeeName = $cEmployee->GetEmployeeByID($aEmployeeOutletList[$i]["employee_ID"]);
				$aEmployeeList[] = array(
					"ID" => $aEmployeeName[0]["ID"],
					"Name" => $aEmployeeName[0]["Name"]
				);
			}
		}

		//check that clientList for this outlet does not exists in table clientOutlet
		$aClientOutletSearchBy = array(
			"outlet_ID" => $_SESSION['outlet_ID']
		);
		$aClientOutletList = $cClient->GetClientOutletList($aClientOutletSearchBy, array(), array());
		if ( count($aClientOutletList) == 0 )
		{//if clientOutlet for this outlet is empty, then get all client.
			$aClientList = $cClient->GetClientList();
		}
		else
		{
			for ($i = 0; $i < count($aClientOutletList); $i++)
			{
				$aClientName = $cClient->GetClientByID($aClientOutletList[$i]["client_ID"]);
				$aClientList[] = array(
					"ID" => $aClientName[0]["ID"],
					"Name" => $aClientName[0]["Name"]
				);
			}
		}

		$aSearchProductByField = array(
			"outlet_ID" => $_SESSION['outlet_ID']
		);
		$aSortProductBy=array(
			'product.Name' => 'asc'
		);
		$aProductList = $cProduct->GetProductListForSalesRetail($aSearchProductByField, $aSortProductBy);
		$aProductCategoryList = $cProduct->GetCategoryList();
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "retail/reportDaily.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_PAGEOUTLETNAME" => $_SESSION['outlet_Name'],
		
		"VAR_FORM_ACTION" => "retail/reportDaily.php",

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_REPORTYEARBEGIN" => isset($_POST['reportYearBegin'])?$_POST['reportYearBegin']:date("Y"),
		"VAR_REPORTMONTHBEGIN" => isset($_POST['reportMonthBegin'])?$_POST['reportMonthBegin']:date("m"),
		"VAR_REPORTDAYBEGIN" => isset($_POST['reportDayBegin'])?$_POST['reportDayBegin']:date("d"),
		"VAR_REPORTYEAREND" => isset($_POST['reportYearEnd'])?$_POST['reportYearEnd']:date("Y"),
		"VAR_REPORTMONTHEND" => isset($_POST['reportMonthEnd'])?$_POST['reportMonthEnd']:date("m"),
		"VAR_REPORTDAYEND" => isset($_POST['reportDayEnd'])?$_POST['reportDayEnd']:date("d"),
		"VAR_REPORTCLIENT" => isset($_POST['reportClient'])?$_POST['reportClient']:"",
		"VAR_REPORTEMPLOYEE" => isset($_POST['reportEmployee'])?$_POST['reportEmployee']:"",
		"VAR_REPORTPRODUCT" => isset($_POST['reportProduct'])?$_POST['reportProduct']:"",
		"VAR_REPORTPRODUCTCATEGORY" => isset($_POST['reportProductCategory'])?$_POST['reportProductCategory']:"",
		"VAR_REPORTSPECIALTAX_SELECTED" => (isset($_POST['reportSpecialTax']) && $_POST['reportSpecialTax'] == "1")?"checked":"",
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
			"VAR_LISTEXPENSESPRICE" => number_format($aExpensesList[$i]['Price'], 0 )
		);
	}
	$cWebsite->buildBlock("content", "expensesList", $expensesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALEXPENSES" => number_format($iGrandTotal, 0 )
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
			"VAR_LISTDEPOSITNOTES" => $aDepositList[$i]['Notes'],
			"VAR_LISTDEPOSITDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_LISTDEPOSITPRICE" => number_format($aDepositList[$i]['Price'], 0 )
		);
	}
	$cWebsite->buildBlock("content", "depositList", $depositListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALDEPOSIT" => number_format($iGrandTotalDeposit, 0 )
	));

	//dateDayBeginBlock
	$dateDayBeginBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['reportDayBegin']) )
		{
			$sDefaultDay = $_POST['reportDayBegin'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$dateDayBeginBlock[] = array(
			"VAR_DAYBEGINVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYBEGINSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateDayBeginBlock", $dateDayBeginBlock);

	//dateMonthBeginBlock
	$dateMonthBeginBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['reportMonthBegin']) )
		{
			$sDefaultMonth = $_POST['reportMonthBegin'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$dateMonthBeginBlock[] = array(
			"VAR_MONTHBEGINVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHBEGINTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHBEGINSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateMonthBeginBlock", $dateMonthBeginBlock);

	//dateYearBeginBlock
	$dateYearBeginBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['reportYearBegin']) )
		{
			$sDefaultYear = $_POST['reportYearBegin'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$dateYearBeginBlock[] = array(
			"VAR_YEARBEGINVALUE" => $i,
			"VAR_YEARBEGINSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateYearBeginBlock", $dateYearBeginBlock);
	
	//dateDayEndBlock
	$dateDayEndBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['reportDayEnd']) )
		{
			$sDefaultDay = $_POST['reportDayEnd'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$dateDayEndBlock[] = array(
			"VAR_DAYENDVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYENDSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateDayEndBlock", $dateDayEndBlock);

	//dateMonthEndBlock
	$dateMonthEndBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['reportMonthEnd']) )
		{
			$sDefaultMonth = $_POST['reportMonthEnd'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$dateMonthEndBlock[] = array(
			"VAR_MONTHENDVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHENDTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHENDSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateMonthEndBlock", $dateMonthEndBlock);

	//dateYearEndBlock
	$dateYearEndBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['reportYearEnd']) )
		{
			$sDefaultYear = $_POST['reportYearEnd'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$dateYearEndBlock[] = array(
			"VAR_YEARENDVALUE" => $i,
			"VAR_YEARENDSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateYearEndBlock", $dateYearEndBlock);

	//employeeBlock
	$employeeBlock = array();
	for ($i = 0; $i < count($aEmployeeList); $i++)
	{
		$employeeBlock[] = array(
			"VAR_EMPLOYEEID" => $aEmployeeList[$i]['ID'],
			"VAR_EMPLOYEENAME" => $aEmployeeList[$i]['Name'],
			"VAR_EMPLOYEESELECTED" => (isset($_POST['reportEmployee']) && $aEmployeeList[$i]['ID'] == $_POST['reportEmployee'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "employeeListBlock", $employeeBlock);

	//clientBlock
	$clientBlock = array();
	for ($i = 0; $i < count($aClientList); $i++)
	{
		$clientBlock[] = array(
			"VAR_CLIENTID" => $aClientList[$i]['ID'],
			"VAR_CLIENTNAME" => $aClientList[$i]['Name'],
			"VAR_CLIENTSELECTED" => (isset($_POST['reportClient']) && $aClientList[$i]['ID'] == $_POST['reportClient'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "clientListBlock", $clientBlock);

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_PRODUCTSELECTED" => (isset($_POST['reportProduct']) && $aProductList[$i]['ID'] == $_POST['reportProduct'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productListBlock", $productListBlock);

	//productCategoryListBlock
	$productCategoryListBlock = array();
	for ($i = 0; $i < count($aProductCategoryList); $i++)
	{
		$productCategoryListBlock[] = array(
			"VAR_PRODUCTCATEGORYID" => $aProductCategoryList[$i]['ID'],
			"VAR_PRODUCTCATEGORYNAME" => $aProductCategoryList[$i]['Name'],
			"VAR_PRODUCTCATEGORYSELECTED" => (isset($_POST['reportProductCategory']) && $aProductCategoryList[$i]['ID'] == $_POST['reportProductCategory'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productCategoryListBlock", $productCategoryListBlock);

	//inventoryListBlock
	$reportListBlock = array();
	$iQuantitytotal = 0;
	$iGrandtotal = 0;
	$iCashtotal = 0;
	$iDebittotal = 0;
	$iTransfertotal = 0;
	for ($i = 0; $i < count($aSalesList); $i++)
	{
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
                        "VAR_INVOICENUMBER" => $aSalesList[$i]['number'],
			"VAR_CLIENTNAME" => $sClientName,
			"VAR_PAYMENTTYPENAME" => $sPaymentTypeName,
			"VAR_NOTES" => ( (($aSalesList[$i]['sales_order_ID'] > 0)?"<span style='color:red'>PP</span> ":"") . $aSalesList[$i]['Notes'] ),
			"VAR_PRODUCTNAME" => $cProduct->GetProductNameByID($aSalesList[$i]['product_ID']),
			"VAR_PRICE" => number_format($aSalesList[$i]['Price'], _NbOfDigitBehindComma_ ),
			"VAR_QUANTITY" => number_format($aSalesList[$i]['Quantity'], 0 ),
			"VAR_DISCOUNT" => number_format($aSalesList[$i]['Discount'], 0 ),
			"VAR_TOTAL" => number_format($iTotal, 0 ),
			"VAR_TAX" => number_format( ($iTotal * 0.1), 0 ),
			"VAR_INVOICETOTAL" => number_format($iTotal + ($iTotal * 0.1) ),
			"VAR_CASH" => number_format($iCash, 0 ),
			"VAR_DEBIT" => number_format($iDebit, 0 ),
			"VAR_TRANSFER" => number_format($iTransfer, 0 )
		);
	}
	$cWebsite->buildBlock("content", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_QANTITYTOTAL" => number_format($iQuantitytotal, 0 ),
		"VAR_GRANDTOTAL" => number_format($iGrandtotal, 0 ),
		"VAR_TAXTOTAL" => number_format( ($iGrandtotal * 0.1), 0 ),
		"VAR_SUMINVOICETOTAL" => number_format( $iGrandtotal + ($iGrandtotal * 0.1), 0 ),
		"VAR_CASHTOTAL" => number_format($iCashtotal, 0 ),
		"VAR_DEBITTOTAL" => number_format($iDebittotal, 0 ),
		"VAR_TRANSFERTOTAL" => number_format($iTransfertotal, 0 )
	));

	//salesRecapListBlock
	//group the sales by product
	$aResultRecap = array();
	$aProductRecap = array();
/*
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
				"VAR_SALESRECAPQUANTITY" => number_format($aProductData['quantity'], 0 ),
				"VAR_SALESRECAPSUBTOTAL" => number_format($aProductData['subtotal'], 0 )
			);
		}
	}
	$cWebsite->buildBlock("content", "salesRecapListBlock", $salesRecapListBlock);
*/
	for ($i = 0; $i < count($aSalesList); $i++)
	{
		if ( !in_array($aSalesList[$i]['product_ID'], $aProductRecap) )
		{
			array_push($aProductRecap, $aSalesList[$i]['product_ID']);
	
			$aResultRecap[$aSalesList[$i]['product_ID']] = array(
				"date" => $aSalesList[$i]['Date'],
				"quantity" => $aSalesList[$i]['Quantity'],
				"subtotal" => $aSalesList[$i]['Quantity'] * $aSalesList[$i]['Price']
			);
		}
		else
		{
				$aResultRecap[$aSalesList[$i]['product_ID']]['quantity'] += $aSalesList[$i]['Quantity'];
				$aResultRecap[$aSalesList[$i]['product_ID']]['subtotal'] += $aSalesList[$i]['Quantity'] * $aSalesList[$i]['Price'];
		}
	}
	$salesRecapListBlock = array();
	$iGrandtotal = 0;
	$i = 1;
	foreach ($aResultRecap as $iProductID => $aProductData)
	{
		$iGrandtotal += $aProductData['subtotal'];

		//list($sYear, $sMonth, $sDay) = explode("-",$aProductData['date']);

		$salesRecapListBlock[] = array(
			"VAR_SALESRECAPROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_SALESRECAPCOUNTER" => $i++,
			//"VAR_SALESRECAPDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_SALESRECAPPRODUCTNAME" => $cProduct->GetProductNameByID($iProductID),
			"VAR_SALESRECAPQUANTITY" => number_format($aProductData['quantity'], 0 ),
                        "VAR_SALESRECAPTAX" => number_format( ($aProductData['subtotal'] * 0.1), 0 ),
			"VAR_SALESRECAPSUBTOTAL" => number_format($aProductData['subtotal'], 0 )
		);
	}
	$cWebsite->buildBlock("content", "salesRecapListBlock", $salesRecapListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALTAXRECAP" => number_format( ($iGrandtotal * 0.1), 0 ),
		"VAR_GRANDTOTALSALESRECAP" => number_format($iGrandtotal, 0 ),
	));

	

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
