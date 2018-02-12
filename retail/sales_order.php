<?php
	/***************************************************************************
	* retail/sales_order.php :: Retail Sales Order Page					*
	****************************************************************************
	* The sales order page for retail									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]	 				*
	* Created			: 2013-10-05 									*
	* Last modified	: 2014-08-21									*
	* 															*
	* 			Copyright (c) 2013-2014 FireSnakeR						*
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
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classInventory.php");
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classPaymentType.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cProduct = new Product;
	$cSales = new Sales;
	$cInventory = new Inventory;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cPaymentType = new PaymentType;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Sales Order";
	$iCurrentRecordCounter = 0;
	$iSalesRecordCounter = 0;
	$iCurrentSalesID = 0;
	$bGoToLastCounter = true;
	$sFormElementDisabled = "";
	$iAjaxPostID = time();
	$iGrandTotal = 0;
	$iCurrentSalesStatus = 'N/A';
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if (isset($_POST["salesNew"]) && $_POST["salesNew"])
			{
				header("Location: sales_order.php");
			}

			if (isset($_POST["salesSave"]) && $_POST["salesSave"])
			{
				$aData = array(
					"ID" => $_POST["sales_ID"],
					"outletID" => $_POST["outlet_ID"],
					"notes" => $_POST["sales_notes"],
					"employeeID" => $_POST["employee_ID"],
					"clientID" => $_POST["client_ID"],
					"paymentTypeID" => $_POST["paymentType_ID"],
					"date" => ($_POST["sales_date"])?$_POST["sales_date"]:date("Y-m-d")
				);

				if ( $aData["ID"] > 0 )
				{
					$iSalesID = $cSales->UpdateSalesOrder($aData);
					$iSalesRecordCounter = $_POST["currentRecordCounter"];
				}
				else
				{
					$iSalesID = $cSales->InsertSalesOrder($aData);
					$iSalesRecordCounter = $_POST["currentRecordCounter"];
				}				
			}

			if (isset($_POST["salesDetail_Save"]) && $_POST["salesDetail_Save"])
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
						"sn_end" => $_POST["sn_end"]
					);

					//make sure sn is filled
					if ( $_POST["sn_start"] != "" )
					{
						if ( $aSalesDetailInsert["salesDetail_ID"] == 0 )
						{
							$iSalesDetailID = $cSales->InsertSalesOrderDetail($aSalesDetailInsert);
						}
						else
						{
							$iSalesDetailID = $cSales->UpdateSalesOrderDetail($aSalesDetailInsert);
						}
					}	
					
					$iSalesID = $_POST["sales_ID"];
					$iSalesRecordCounter = $_POST["currentRecordCounter"];
				}
			}

			if (isset($_POST["salesDetailEdit"]) && $_POST["salesDetailEdit"])
			{
				$aSalesDetailEdit = $cSales->GetSalesOrderDetailByDetailID($_POST["salesDetail_ID"]);

				$iSalesID = $_POST["sales_ID"];
				$iSalesRecordCounter = $_POST["currentRecordCounter"];
			}

			if ( isset($_POST["firstRecord"]) && $_POST["firstRecord"] )
			{
				$iCurrentRecordCounter = 0;
				$bGoToLastCounter = false;
			}

			if ( isset($_POST["prevRecord"]) && $_POST["prevRecord"] 
				&& isset($_POST["currentRecordCounter"]) && $_POST["currentRecordCounter"] )
			{
				$iCurrentRecordCounter = $_POST["currentRecordCounter"];
				$bGoToLastCounter = false;
			}

			if ( isset($_POST["gotoRecord"]) && $_POST["gotoRecord"] 
				&& isset($_POST["currentRecordCounter"]) && $_POST["currentRecordCounter"] )
			{
				$iCurrentRecordCounter = $_POST["currentRecordCounter"] - 1;
				$bGoToLastCounter = false;
			}

			if ( isset($_POST["nextRecord"]) && $_POST["nextRecord"] 
				&& isset($_POST["currentRecordCounter"]) && $_POST["currentRecordCounter"] )
			{
				$iCurrentRecordCounter = $_POST["currentRecordCounter"];
				$bGoToLastCounter = false;
			}

			if ( isset($_POST["lastRecord"]) && $_POST["lastRecord"] )
			{
				$bGoToLastCounter = true;
			}

			if ( isset($_POST["Print"]) && $_POST["Print"] 
				&& isset($_POST["currentRecordCounter"]) && $_POST["currentRecordCounter"] )
			{
				$iCurrentRecordCounter = $_POST["currentRecordCounter"];
				$iCurrentRecordCounter -= 1;
				$bGoToLastCounter = false;
			}

			if ( isset($_GET["gotoRecord"]) && $_GET["gotoRecord"] )
			{
				$iCurrentRecordCounter = $_GET["gotoRecord"] - 1;
				$bGoToLastCounter = false;
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		//get product list visible on website
		$aSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID']
		);
		$aSortByArray=array(
			'product.Name' => 'asc'
		);
		$aProductList = $cProduct->GetProductListForSalesRetail($aSearchByFieldArray, $aSortByArray);

		$aSalesSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID']
		);		
		$aSalesList = $cSales->GetSalesOrderList($aSalesSearchByFieldArray);

		$iTotalSales = count($aSalesList);
		
		if ( $iTotalSales > 0 )
		{
			if ($bGoToLastCounter == true)
			{
				$iCurrentRecordCounter = $iTotalSales;
			}

			//this is here because if the end of record is reached, the system will simply add new numbers to the end.
			//While it is more desireable to just go to end of record.
			if ( $iCurrentRecordCounter > $iTotalSales)
			{
				$iCurrentRecordCounter = $iTotalSales;
			}

			//if this is after an update or insert, then $iSalesID must exist, we set back the currentrecordcounter to
			//the old position.
			if ( isset($iSalesID) && $iSalesID > 0 )
			{
				$iCurrentRecordCounter = $iSalesRecordCounter - 1;
			}

			$iCurrentSalesID = ($iCurrentRecordCounter < count($aSalesList))?$aSalesList[$iCurrentRecordCounter]['ID']:'';
			$iCurrentSalesNotes = ($iCurrentRecordCounter < count($aSalesList))?$aSalesList[$iCurrentRecordCounter]['notes']:'';
			$iCurrentSalesEmployeeID = ($iCurrentRecordCounter < count($aSalesList))?$aSalesList[$iCurrentRecordCounter]['employeeID']:'';
			$iCurrentSalesClientID = ($iCurrentRecordCounter < count($aSalesList))?$aSalesList[$iCurrentRecordCounter]['clientID']:'';
			$iCurrentSalesPaymentTypeID = ($iCurrentRecordCounter < count($aSalesList))?$aSalesList[$iCurrentRecordCounter]['paymentTypeID']:'';
			if ( $iCurrentRecordCounter < count($aSalesList) )
			{
				switch ($aSalesList[$iCurrentRecordCounter]['Status'])
				{
					case 0:
						$iCurrentSalesStatus = "<span style='color:red;'>Baru</span>";
					break;
					case 1:
						$iCurrentSalesStatus = "<span style='color:red;'>Verified</span>";
					break;
					default:
						//$iCurrentSalesStatus = "";
					break;
				}
			}
			$iCurrentRecordCounter += 1;

			$aSalesData = $cSales->GetSalesOrderWithDetail($iCurrentSalesID);
			$iSalesDetailCount = count($aSalesData);

			if (count($aSalesData) < 1 )
			{
				$iSalesDetailCount = 0;
				$aSalesData = $cSales->GetSalesOrderByID($iCurrentSalesID);
			}
		}

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

		$aPaymentTypeList = $cPaymentType->GetPaymentTypeList();

		//here we check to see if an update is allowed, currently only today data is allowed
		if ($iCurrentSalesID > 0)
		{
			$sSales_Date = date( "d-M-Y", strtotime( $aSalesData[0]["Date"] ) );
			//check the date, if data updated is not today, denied the change.
			list($iDay, $sMonth, $iYear) = explode("-", $sSales_Date);
			if ( $iDay == date("d") && $sMonth == date("M") )
			{
				//allow edit or input
				$sFormElementDisabled = "";

			}
			else
			{
				//disable edit
				$sFormElementDisabled = "disabled='1'";
			}

			//check also for the status of the data, if status != 0, then disable edit
			if ($sFormElementDisabled == "")
			{
				if ($aSalesData[0]["Status"] != 0)
				{
					//disable edit
					$sFormElementDisabled = "disabled='1'";
				}
				else
				{
					$sFormElementDisabled = "";
				}
			}

		}
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "retail/sales_order.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGEURL" => "retail/sales_order.php",
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_PAGEOUTLETNAME" => $_SESSION['outlet_Name'],

		//page text
		"VAR_PAGETITLE" => "Pesanan Penjualan",
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"TEXT_STATUS" => "Status :",
		"VAR_SALESSTATUS" => $iCurrentSalesStatus,
		"VAR_SALESDATE" => ($iCurrentSalesID > 0)?date( "d-M-Y", strtotime( $aSalesData[0]["Date"] ) ):date("d-M-Y"),
		"VAR_SALESDATEVALUE" => ($iCurrentSalesID > 0)?date( "Y-m-d", strtotime( $aSalesData[0]["Date"] ) ):date("Y-m-d"),
		"VAR_SALESNOTES" => $iCurrentSalesNotes,
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_SALES" => $iCurrentRecordCounter,
		"VAR_SALESTOTAL" => $iTotalSales,
		"VAR_TMPROW" => count($aSalesData),
		"VAR_AJAXPOSTID" => $iAjaxPostID,
		"VAR_OUTLETID" => $_SESSION['outlet_ID'],
		"VAR_SALESID" => $iCurrentSalesID,
		"VAR_EDITALLOWED" => ($sFormElementDisabled == "")?"1":"0",
		"VAR_EDIT_SALESDETAILID" => (isset($aSalesDetailEdit))?$aSalesDetailEdit[0]["detail_ID"]:'',
		"VAR_EDIT_QUANTITY" => (isset($aSalesDetailEdit))?$aSalesDetailEdit[0]["Quantity"]:'',
		"VAR_EDIT_PRICE" => (isset($aSalesDetailEdit))?$aSalesDetailEdit[0]["Price"]:'',
		"VAR_EDIT_DISCOUNT" => (isset($aSalesDetailEdit))?$aSalesDetailEdit[0]["Discount"]:'',
		"VAR_EDIT_SNSTART" => (isset($aSalesDetailEdit))?$aSalesDetailEdit[0]["SnStart"]:'',
		"VAR_EDIT_SNEND" => (isset($aSalesDetailEdit))?$aSalesDetailEdit[0]["SnEnd"]:'',
		"VAR_PREVRECORDCOUNTER" => ($iCurrentRecordCounter <= 1)?0:($iCurrentRecordCounter - 2),
		"VAR_NEXTRECORDCOUNTER" => ($iCurrentRecordCounter > $iTotalSales)? $iTotalSales:$iCurrentRecordCounter,
		"VAR_CURRENTRECORDCOUNTER" => $iCurrentRecordCounter,
		"VAR_ELEMENTDISABLED" => $sFormElementDisabled
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
				"VAR_PRODUCTID" => $aSalesData[$i]['productID'],
				"VAR_PRODUCTNAME" => $aSalesData[$i]['productName'],
				"VAR_QUANTITY" => number_format( $aSalesData[$i]['Quantity'], _NbOfDigitBehindComma_ ),
				"VAR_DISCOUNT" => number_format( $aSalesData[$i]['Discount'], 2 ),
				"VAR_PRICE" => number_format( $aSalesData[$i]['Price'], _NbOfDigitBehindComma_ ),
				"VAR_SUBTOTAL" => number_format( $iSubtotal, _NbOfDigitBehindComma_ ),
				"VAR_SN" => ($aSalesData[$i]['SnStart'] . (($aSalesData[$i]['SnEnd'] == "")?"":("-" . $aSalesData[$i]['SnEnd']) ) ),
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