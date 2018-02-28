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
	* master/reportSalesOrder.php :: Master Index Page						*
	****************************************************************************
	* The daily report page for master									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2013-10-30 									*
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
	include_once($libPath . "/classPaymentType.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cPaymentType = new PaymentType;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	$sPageName = "Report Sales Order";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDate = $_POST['reportYear'] . "-" . $_POST['reportMonth'] . "-" . $_POST['reportDay'];
			$sBeginDate = $_POST['beginYear'] . "-" . $_POST['beginMonth'] . "-" . $_POST['beginDay'];
			$sEndDate = $_POST['endYear'] . "-" . $_POST['endMonth'] . "-" . $_POST['endDay'];

			//process verify
			if ( isset($_POST['salesVerifySubmit']) && $_POST['salesVerifySubmit'] == 'Edit' )
			{
				$cSales->VerifySalesOrder($_POST['salesVerifyID'], $_POST['salesVerifyNotes']);
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
			"employee_ID" => ($_POST['reportEmployee'])?$_POST['reportEmployee']:"",
			"client_ID" => ($_POST['reportClient'])?$_POST['reportClient']:"",
			"paymentType_ID" => ($_POST['reportPaymentType'])?$_POST['reportPaymentType']:"",
			"product_ID" => ($_POST['reportProduct'])?$_POST['reportProduct']:"",
			"productCategory_ID" => ($_POST['reportProductCategory'])?$_POST['reportProductCategory']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aSalesList = $cSales->GetSalesOrderReport($aSearchByFieldArray);
		$aOutletList = $cOutlet->GetActiveOutletList();
		$aEmployeeList = $cEmployee->GetEmployeeList();
		$aClientList = $cClient->GetClientList();
		$aPaymentTypeList = $cPaymentType->GetPaymentTypeList();
		$aProductList = $cProduct->GetProductList();
		$aProductCategoryList = $cProduct->GetCategoryList();
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/reportSalesOrder.htm"
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
		"TEXT_REPORT" => "Report Sales Order"
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

	//employeeListBlock
	$employeeListBlock = array();
	for ($i = 0; $i < count($aEmployeeList); $i++)
	{
		$employeeListBlock[] = array(
			"VAR_EMPLOYEEID" => $aEmployeeList[$i]['ID'],
			"VAR_EMPLOYEENAME" => $aEmployeeList[$i]['Name'],
			"VAR_EMPLOYEESELECTED" => ($aEmployeeList[$i]['ID'] == $_POST['reportEmployee'])?"selected":""
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
			"VAR_CLIENTSELECTED" => ($aClientList[$i]['ID'] == $_POST['reportClient'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "clientListBlock", $clientListBlock);

	//paymentTypeListBlock
	$paymentTypeListBlock = array();
	for ($i = 0; $i < count($aPaymentTypeList); $i++)
	{
		$paymentTypeListBlock[] = array(
			"VAR_PAYMENTTYPEID" => $aPaymentTypeList[$i]['ID'],
			"VAR_PAYMENTTYPENAME" => $aPaymentTypeList[$i]['Name'],
			"VAR_PAYMENTTYPESELECTED" => ($aPaymentTypeList[$i]['ID'] == $_POST['reportPaymentType'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "paymentTypeListBlock", $paymentTypeListBlock);

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_PRODUCTSELECTED" => ($aProductList[$i]['ID'] == $_POST['reportProduct'])?"selected":""
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
			"VAR_PRODUCTCATEGORYSELECTED" => ($aProductCategoryList[$i]['ID'] == $_POST['reportProductCategory'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productCategoryListBlock", $productCategoryListBlock);

	//inventoryListBlock
	$iGrandQuantity = 0;
	$iGrandTotal = 0;
	$reportListBlock = array();
	$iOldSalesID = 0;
	$iSubTotalBySalesID = 0;
	$bShow = FALSE;
	for ($i = 0; $i < count($aSalesList); $i++)
	{
		$aEmployeeName = $cEmployee->GetEmployeeByID($aSalesList[$i]['employee_ID']);
		$sEmployeeName = $aEmployeeName[0]['Name'];

		$aClientName = $cClient->GetClientByID($aSalesList[$i]['client_ID']);
		$sClientName = $aClientName[0]['Name'];

		$aPaymentTypeName = $cPaymentType->GetPaymentTypeByID($aSalesList[$i]['paymentType_ID']);
		$sPaymentTypeName = $aPaymentTypeName[0]['Name'];

		$aOutletName = $cOutlet->GetOutletByID($aSalesList[$i]['outlet_ID']);
		$sOutletName = $aOutletName[0]['Name'];

		list($sYear, $sMonth, $sDay) = explode("-",$aSalesList[$i]['Date']);
		
		$iSubtotal = $aSalesList[$i]['Price'] * $aSalesList[$i]['Quantity'] * ( (100 - $aSalesList[$i]['Discount']) / 100);

		$iSubquantity = $aSalesList[$i]['Quantity'];

		//check for calculation of subtotal by sales ID
		if ($iOldSalesID == $aSalesList[$i]['sales_order_ID'])
		{
			$iSubTotalBySalesID += $iSubtotal;
		}
		else
		{
			$iSubTotalBySalesID = $iSubtotal;
		}

		$iOldSalesID = $aSalesList[$i]['sales_order_ID'];

		//check for the next record to check if it is still the same sales_ID
		$bShow = FALSE;
		if ( ( ($i+1) == count($aSalesList)) || $iOldSalesID <> $aSalesList[$i+1]['sales_order_ID'])
		{
			$bShow = TRUE;
		}

		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SALESDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_SALESNAME" => $sEmployeeName,
			"VAR_CLIENTNAME" => $sClientName,
			"VAR_PAYMENTTYPENAME" => $sPaymentTypeName,
			"VAR_PRODUCTNAME" => $cProduct->GetProductNameByID($aSalesList[$i]['product_ID']),
			"VAR_NOTES" => ( (($aSalesList[$i]['sales_order_ID'] > 0)?"<span style='color:red'>PP</span> ":"") . $aSalesList[$i]['Notes'] ),
			"VAR_PRICE" => number_format($aSalesList[$i]['Price'], _NbOfDigitBehindComma_ ),
			"VAR_QUANTITY" => number_format($aSalesList[$i]['Quantity'], _NbOfDigitBehindComma_ ),
			"VAR_DISCOUNT" => number_format($aSalesList[$i]['Discount'], 2 ),
			"VAR_TOTAL" => number_format($iSubtotal, _NbOfDigitBehindComma_ ),
			"VAR_TOTALBYSALESID" => ($bShow == TRUE)?number_format($iSubTotalBySalesID, _NbOfDigitBehindComma_ ):'',
			"VAR_SALESID" => $aSalesList[$i]['sales_order_ID'],
			"VAR_STATUS" => ($aSalesList[$i]['Status'] == 1)?'Verified':'Unverified',
			"VAR_SALESFINANCENOTES" => $aSalesList[$i]['FinanceNotes'],
			"VAR_CASH" => $i,
			"VAR_DEBIT" => $i,
			"VAR_TRANSFER" => $i
		);

		$iGrandQuantity += $iSubquantity;
		$iGrandTotal += $iSubtotal;
	}
	$cWebsite->buildBlock("content", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => ($_POST['reportOutlet'])?$_POST['reportOutlet']:"0",
		"VAR_REPORTEMPLOYEE" => ($_POST['reportEmployee'])?$_POST['reportEmployee']:"0",
		"VAR_REPORTCLIENT" => ($_POST['reportClient'])?$_POST['reportClient']:"0",
		"VAR_REPORTPAYMENTTYPE" => ($_POST['reportPaymentType'])?$_POST['reportPaymentType']:"0",
		"VAR_REPORTPRODUCT" => ($_POST['reportProduct'])?$_POST['reportProduct']:"0",
		"VAR_REPORTPRODUCTCATEGORY" => ($_POST['reportProductCategory'])?$_POST['reportProductCategory']:"0",
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear,

		"VAR_GRANDQUANTITY" => number_format($iGrandQuantity, _NbOfDigitBehindComma_ ),
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
