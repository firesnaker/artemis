<?php
	/***************************************************************************
	* master/reportSalesSave.php :: Master Report Sales Save Page				*
	****************************************************************************
	* The full sales report save page for master							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-08-08 									*
	* Last modified	: 2014-08-01									*
	*															*
	* 			Copyright (c) 2010-2014 FireSnakeR						*
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
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cPaymentType = new PaymentType;
	$cProduct = new Product;
	$cOutlet = new Outlet;
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
			"paymentType_ID" => ($_GET['reportPaymentType'])?$_GET['reportPaymentType']:"",
			"product_ID" => ($_GET['reportProduct'])?$_GET['reportProduct']:"",
			"productCategory_ID" => ($_GET['reportProductCategory'])?$_GET['reportProductCategory']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aSalesList = $cSales->GetSalesReport($aSearchByFieldArray);
		$aOutletList = $cOutlet->GetActiveOutletList();
		$aEmployeeList = $cEmployee->GetEmployeeList();
		$aClientList = $cClient->GetClientList();
		$aPaymentTypeList = $cPaymentType->GetPaymentTypeList();
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

		$sSearchPaymentTypeName = "All Payment Type";		
		if ($_GET['reportPaymentType'] > 0)
		{
			$aSearchPaymentTypeData = $cPaymentType->GetPaymentTypeByID($_GET['reportPaymentType']);		
			$sSearchPaymentTypeName = $aSearchPaymentTypeData[0]['Name'];
		}

		$sSearchProductName = "All Products";		
		if ($_GET['reportProduct'] > 0)
		{
			$aSearchProductData = $cProduct->GetProductByID($_GET['reportProduct']);		
			$sSearchProductName = $aSearchProductData[0]['Name'];
		}

		$sSearchProductCategoryName = "All Products Category";		
		if ($_GET['reportProductCategory'] > 0)
		{
			$aSearchProductCategoryData = $cProduct->GetCategoryByID($_GET['reportProductCategory']);		
			$sSearchProductCategoryName = $aSearchProductCategoryData[0]['Name'];
		}

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "master/reportSales.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	//inventoryListBlock
	$iGrandQuantity = 0;
	$iGrandTotal = 0;
	$reportListBlock = array();
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

		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SALESDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_SALESNAME" => $sEmployeeName,
			"VAR_CLIENTNAME" => $sClientName,
			"VAR_PAYMENTTYPENAME" => $sPaymentTypeName,
			"VAR_PRODUCTNAME" => $cProduct->GetProductNameByID($aSalesList[$i]['product_ID']),
			"VAR_NOTES" => ( (($aSalesList[$i]['sales_order_ID'] > 0)?"PP ":"") . $aSalesList[$i]['Notes'] ),
			"VAR_PRICE" => number_format($aSalesList[$i]['Price'], _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ),
			"VAR_QUANTITY" => number_format($aSalesList[$i]['Quantity'], _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ),
			"VAR_DISCOUNT" => number_format($aSalesList[$i]['Discount'], 2, _DecimalPoint_, _CommaSeparator_  ),
			"VAR_TOTAL" => number_format($iSubtotal, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ),
			"VAR_PRICE_CSV" => number_format($aSalesList[$i]['Price'], 0, "", ""  ),
			"VAR_QUANTITY_VIRGIN" => $aSalesList[$i]['Quantity'],
			"VAR_TOTAL_VIRGIN" => $iSubtotal,
			"VAR_CASH" => $i,
			"VAR_DEBIT" => $i,
			"VAR_TRANSFER" => $i
		);

		$iGrandQuantity += $iSubquantity;
		$iGrandTotal += $iSubtotal;
	}

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  )
	));

	//prepare the data
	$aContent = array();
	$aContent[] = array("Date", "Outlet", "Client", "Sales Person", "Notes", "Item", "Quantity", "Payment Type", "Price", "Discount", "Total");
	foreach ($reportListBlock as $iKey => $aData)
	{
		$aContent[] = array($aData["VAR_SALESDATE"], $aData["VAR_OUTLETNAME"], $aData["VAR_CLIENTNAME"], $aData["VAR_SALESNAME"], $aData["VAR_NOTES"], $aData["VAR_PRODUCTNAME"], $aData["VAR_QUANTITY_VIRGIN"], $aData["VAR_PAYMENTTYPENAME"], $aData["VAR_PRICE_CSV"], $aData["VAR_DISCOUNT"], $aData["TOTAL_VIRGIN"]);
	}
	//generate the grandtotal
	$aContent[] = array("", "", "", "", "", "", $iGrandQuantity, "", "", "", $iGrandTotal);

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('reportSave-' . $sSearchOutletName . '-' . $sBeginDate . '-' . $sEndDate . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>