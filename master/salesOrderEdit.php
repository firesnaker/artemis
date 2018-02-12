<?php
	/***************************************************************************
	* master/salesOrderEdit.php :: Retail Sales Page						*
	****************************************************************************
	* The sales page for retail										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2013-10-30									*
	* Last modified	: 2014-08-01									*
	*															*
	* 			Copyright (c) 2013-2014 FireSnakeR						*
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
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classInventory.php");
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classPaymentType.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cUser = new User($_SESSION['user_ID']);
	$cProduct = new Product;
	$cSales = new Sales;
	$cInventory = new Inventory;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cPaymentType = new PaymentType;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iCurrentRecordCounter = 0;
	$iCurrentSalesID = 0;
	$bGoToLastCounter = true;
	$sFormElementDisabled = "";
	$iAjaxPostID = time();
	$iOutletID = 0;
	$sPageName = "Sales Order Edit";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ($_POST["salesSave"])
			{
				$aData = array(
					"ID" => $_POST["sales_ID"],
					"outletID" => $_POST["outlet_ID"],
					"notes" => $_POST["sales_notes"],
					"employeeID" => $_POST["employee_ID"],
					"clientID" => $_POST["client_ID"],
					"paymentTypeID" => $_POST["paymentType_ID"],
					"date" => $_POST["sales_dateYear"] . "-" .  $_POST["sales_dateMonth"] . "-" . $_POST["sales_dateDay"]
				);

				if ( $aData["ID"] > 0 )
				{
					$iSalesID = $cSales->UpdateSalesOrder($aData);
				}
				else
				{
					//insertion is not possible here 
					//$iSalesID = $cSales->InsertSalesOrder($aData);
				}				
			}

			if ($_POST["salesDetail_Save"])
			{
				if ( $_POST["sales_ID"] >= 0 && $_POST["salesDetail_ID"] >= 0 && $_POST["product"] && ( $_POST["quantity"] >= 0 ) && ( $_POST["price"] >= 0 ) && ( $_POST["discount"] >= 0 ) )
				{
					//TODO:check the quantity and product ID, make sure that the end result is above 0
					//if the end result is below 0, deny the update

					$aSalesDetailInsert = array(
						"sales_ID" => $_POST["sales_ID"],
						"salesDetail_ID" => $_POST["salesDetail_ID"],
						"product_ID" => $_POST["product"],
						"quantity" => $_POST["quantity"],
						"discount" => $_POST["discount"],
						"price" => $_POST["price"],
						"sn_start" => $_POST["sn_start"],
						"sn_end" => $_POST["sn_end"],
					);
	
					if ( $aSalesDetailInsert["salesDetail_ID"] == 0 )
					{
						$iSalesDetailID = $cSales->InsertSalesOrderDetail($aSalesDetailInsert);
					}
					else
					{
						$iSalesDetailID = $cSales->UpdateSalesOrderDetail($aSalesDetailInsert);
					}
				}
			}

			if ($_POST["salesDetailEdit"])
			{
				$aSalesDetailEdit = $cSales->GetSalesOrderDetailByDetailID($_POST["salesDetail_ID"]);
			}

			if ( isset($_POST['salesEditSubmit']) && $_POST['salesEditID'] > 0 )
			{
				$iCurrentSalesID = $_POST['salesEditID'];
				$aSalesData = $cSales->GetSalesOrderWithDetail($iCurrentSalesID);
				$iSalesDetailCount = count($aSalesData);
				if (count($aSalesData) < 1 )
				{
					$iSalesDetailCount = 0;
					$aSalesData = $cSales->GetSalesOrderByID($iCurrentSalesID);
				}

				$iCurrentSalesNotes = $aSalesData[0]['Notes'];
				$iCurrentSalesEmployeeID = $aSalesData[0]['employee_ID'];
				$iCurrentSalesClientID = $aSalesData[0]['client_ID'];
				$iCurrentSalesPaymentTypeID = $aSalesData[0]['paymentType_ID'];
				$iOutletID = $aSalesData[0]['outlet_ID'];
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		//get product list visible on website
		$aSearchByFieldArray = array(
			
		);
		$aSortByArray=array(
			'product.Name' => 'asc'
		);
		$aProductList = $cProduct->GetProductList($aSearchByFieldArray, $aSortByArray);

		//check that employeeList for this outlet does not exists in table employeeOutlet
		$aEmployeeOutletSearchBy = array(
			"outlet_ID" => $iOutletID
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
			"outlet_ID" => $iOutletID
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

		$aPaymentTypeList = $cPaymentType->GetPaymentTypeList();
		
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/salesOrderEdit.htm"
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
		"VAR_SALESDATE" => ($iCurrentSalesID > 0)?date( "d-M-Y", strtotime( $aSalesData[0]["Date"] ) ):date("d-M-Y"),
		"VAR_SALESNOTES" => $iCurrentSalesNotes,
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_SALES" => $iCurrentRecordCounter,
		"VAR_SALESTOTAL" => $iTotalSales,
		"VAR_TMPROW" => count($aSalesData),
		"VAR_AJAXPOSTID" => $iAjaxPostID,
		"VAR_OUTLETID" => $iOutletID,
		"VAR_SALESID" => $iCurrentSalesID,
		"VAR_EDIT_SALESDETAILID" => $aSalesDetailEdit[0]["detail_ID"],
		"VAR_EDIT_QUANTITY" => $aSalesDetailEdit[0]["Quantity"],
		"VAR_EDIT_PRICE" => $aSalesDetailEdit[0]["Price"],
		"VAR_EDIT_DISCOUNT" => $aSalesDetailEdit[0]["Discount"],
		"VAR_EDIT_SNSTART" => $aSalesDetailEdit[0]["SnStart"],
		"VAR_EDIT_SNEND" => $aSalesDetailEdit[0]["SnEnd"],

	));

	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	//get the sales date of the receipt. Because this is an edit, a sales date must already exists.
	$sSalesDate = date( "d-m-Y", strtotime( $aSalesData[0]["Date"] ) );
	list($sDateDay, $sDateMonth, $sDateYear) = explode("-", $sSalesDate);
	//dateDayBlock
	$dateDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		$dateDayBlock[] = array(
			"VAR_DATEDAYVALUE" => $i+1,
			"VAR_DATEDAYSELECTED" => ($sDateDay == ($i + 1))?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateDay", $dateDayBlock);

	//dateMonthBlock
	$dateMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		$dateMonthBlock[] = array(
			"VAR_DATEMONTHVALUE" => $i+1,
			"VAR_DATEMONTHTEXT" => date("M", mktime(0,0,0, $i+1, 1, 2012) ),
			"VAR_DATEMONTHSELECTED" => ($sDateMonth == ( $i + 1 ) )?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateMonth", $dateMonthBlock);

	//dateYearBlock
	$dateYearBlock = array();
	for ($i = 2010; $i < date("Y"); $i++)
	{
		$dateYearBlock[] = array(
			"VAR_DATEYEARVALUE" => $i+1,
			"VAR_DATEYEARSELECTED" => ($sDateYear == ($i + 1) )?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateYear", $dateYearBlock);

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_EDIT_PRODUCTSELECTED" => ( isset($aSalesDetailEdit) && ($aSalesDetailEdit[0]["productID"] == $aProductList[$i]['ID']) )?"selected":"",
		);
	}
	$cWebsite->buildBlock("content", "productListBlock", $productListBlock);

	//productListPriceBlock
	$productListPriceBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListPriceBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTPRICE" => number_format($aProductList[$i]['price'], 0, '.', '')
		);
	}
	$cWebsite->buildBlock("content", "productListPriceBlock", $productListPriceBlock);

	//employeeBlock
	$employeeBlock = array();
	for ($i = 0; $i < count($aEmployeeList); $i++)
	{
		$employeeBlock[] = array(
			"VAR_EMPLOYEEID" => $aEmployeeList[$i]['ID'],
			"VAR_EMPLOYEENAME" => $aEmployeeList[$i]['Name'],
			"VAR_EMPLOYEESELECTED" => ($aEmployeeList[$i]['ID'] == $iCurrentSalesEmployeeID)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "employeeBlock", $employeeBlock);

	//clientBlock
	$clientBlock = array();
	for ($i = 0; $i < count($aClientList); $i++)
	{
		$clientBlock[] = array(
			"VAR_CLIENTID" => $aClientList[$i]['ID'],
			"VAR_CLIENTNAME" => $aClientList[$i]['Name'],
			"VAR_CLIENTSELECTED" => ($aClientList[$i]['ID'] == $iCurrentSalesClientID)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "clientBlock", $clientBlock);

	//paymentTypeBlock
	$paymentTypeBlock = array();
	for ($i = 0; $i < count($aPaymentTypeList); $i++)
	{
		$paymentTypeBlock[] = array(
			"VAR_PAYMENTTYPEID" => $aPaymentTypeList[$i]['ID'],
			"VAR_PAYMENTTYPENAME" => $aPaymentTypeList[$i]['Name'],
			"VAR_PAYMENTTYPESELECTED" => ($aPaymentTypeList[$i]['ID'] == $iCurrentSalesPaymentTypeID)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "paymentTypeBlock", $paymentTypeBlock);

	if ($iCurrentSalesID == 0 || $iSalesDetailCount == 0)
	{
		$cWebsite->template->set_block("content", "salesDetailRow");
		$cWebsite->template->parse("salesDetailRow", "");
	}
	else
	{
		$iGrandTotal = 0;
		//salesDetailRow
		$salesDetailRow = array();
		for ($i = 0; $i < count($aSalesData); $i++)
		{
			$iSubtotal = $aSalesData[$i]['Quantity'] * $aSalesData[$i]['Price'] * ( (100 - $aSalesData[$i]['Discount'] ) / 100);
			$salesDetailRow[] = array(
				"VAR_COUNTER" => $i+1,
				"VAR_SALESDETAILID" => $aSalesData[$i]['detail_ID'],
				"VAR_SALESDETAILAJAXPOSTID" => $aSalesData[$i]['detail_ajaxPostID'],
				"VAR_PRODUCTID" => $aSalesData[$i]['productID'],
				"VAR_PRODUCTNAME" => $aSalesData[$i]['productName'],
				"VAR_QUANTITY" => number_format( $aSalesData[$i]['Quantity'], _NbOfDigitBehindComma_ ),
				"VAR_DISCOUNT" => number_format( $aSalesData[$i]['Discount'], 2 ),
				"VAR_PRICE" => number_format( $aSalesData[$i]['Price'], _NbOfDigitBehindComma_ ),
				"VAR_SUBTOTAL" => number_format( $iSubtotal, _NbOfDigitBehindComma_ ),
				"VAR_SNSTART" => $aSalesData[$i]['SnStart'],
				"VAR_SNEND" => $aSalesData[$i]['SnEnd'],
			);

			$iGrandTotal += $iSubtotal;
		}
		$cWebsite->buildBlock("content", "salesDetailRow", $salesDetailRow);
	}

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_)
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>