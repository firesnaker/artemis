<?php
	/***************************************************************************
	* finance/sales.php :: Finance Sales Verification Page					*
	****************************************************************************
	* The sales verification page for finance							*
	*															*
	* Version			: 0.1										*
	* Author			: FireSnakeR 									*
	* Created			: 2012-05-05 									*
	* Last modified	: 2014-07-02									*
	* 															*
	* 			Copyright (c) 2012-2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classSales.php");
		include_once($libPath . "/classEmployee.php");
		include_once($libPath . "/classClient.php");
		include_once($libPath . "/classPaymentType.php");
		include_once($libPath . "/classProduct.php");
		include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classUser.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 
		  && ($_SESSION['user_Name'] == "admin" || strtolower($_SESSION['user_Name']) == "finance" || $_SESSION['user_IsFinance'] == 1) )
		{
			//do nothing
		}
		else
		{
			$_SESSION = array();
			session_destroy(); //destroy all session
			//TODO: create a log file
	 		header("Location:index.php"); //redirect to index page
	 		exit;
		}
		//+++ END session initialization ++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN variable declaration and initialization +++++++++++++++++//
		$sErrorMessages = FALSE;
		$sMessages = FALSE;
		$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cSales = new Sales;
		$cEmployee = new Employee;
		$cClient = new Client;
		$cPaymentType = new PaymentType;
		$cProduct = new Product;
		$cOutlet = new Outlet;
		$cUser = new User($_SESSION['user_ID']);
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sBeginDate = $_POST['beginYear'] . "-" . $_POST['beginMonth'] . "-" . $_POST['beginDay'];
			$sEndDate = $_POST['endYear'] . "-" . $_POST['endMonth'] . "-" . $_POST['endDay'];

			//process verify
			if ( isset($_POST['salesVerifySubmit']) && $_POST['salesVerifySubmit'] == 'Verify' )
			{
				foreach ($_POST as $key => $value)
				{
					if (substr_count($key, "salesVerifyID") > 0 )
					{
						//get the counter
						list($key1, $key2) = explode('_', $key);

						$aVerify = array(
							"ID" => $_POST["salesVerifyID_" . $key2],
							"Notes" => $_POST["salesVerifyNotes_" . $key2],
						);

						if ($aVerify["Notes"] != "" && $aVerify["ID"] > 0)
						{
							$cSales->Verify($aVerify["ID"], $aVerify["Notes"]);
						}
					}
				}
			}
		}
		else
		{
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"outlet_ID" => (isset($_POST['reportOutlet']))?$_POST['reportOutlet']:"",
			"employee_ID" => (isset($_POST['reportEmployee']))?$_POST['reportEmployee']:"",
			"client_ID" => (isset($_POST['reportClient']))?$_POST['reportClient']:"",
			"paymentType_ID" => (isset($_POST['reportPaymentType']))?$_POST['reportPaymentType']:"",
			"product_ID" => (isset($_POST['reportProduct']))?$_POST['reportProduct']:"",
			"productCategory_ID" => (isset($_POST['reportProductCategory']))?$_POST['reportProductCategory']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		if ($_SESSION['user_IsFinance'] == 1)
		{
			$aOutletList = $cOutlet->GetActiveOutletListByFinanceArea($_SESSION['user_ID']);

			$aSearchByFieldArray['AllOutlet'] = $aOutletList;
			$aSalesList = $cSales->GetSalesReportByFinanceArea($aSearchByFieldArray);
		}
		else
		{
			$aSalesList = $cSales->GetSalesReport($aSearchByFieldArray);
			$aOutletList = $cOutlet->GetActiveOutletList();
		}

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
		"content" => "finance/sales.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Sales"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_finance");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_finance");

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
			"VAR_OUTLETSELECTED" => (isset($_POST['reportOutlet']) && $aOutletList[$i]['ID'] == $_POST['reportOutlet'])?"selected":""
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
			"VAR_EMPLOYEESELECTED" => (isset($_POST['reportEmployee']) && $aEmployeeList[$i]['ID'] == $_POST['reportEmployee'])?"selected":""
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
			"VAR_CLIENTSELECTED" => (isset($_POST['reportClient']) && $aClientList[$i]['ID'] == $_POST['reportClient'])?"selected":""
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
			"VAR_PAYMENTTYPESELECTED" => (isset($_POST['reportPaymentType']) && $aPaymentTypeList[$i]['ID'] == $_POST['reportPaymentType'])?"selected":""
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
			"VAR_PRODUCTCATEGORYSELECTED" => (isset($_POST['reportProductCategory']) && $aProductList[$i]['ID'] == $_POST['reportProductCategory'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productCategoryListBlock", $productCategoryListBlock);

	//we need to regroup the sales list by sales_ID
	$aGroupedSalesList = array();
	$aSalesIDContainer = array();
	for ($i = 0; $i < count($aSalesList); $i++)
	{
		if ( !in_array($aSalesList[$i]['sales_ID'], $aSalesIDContainer) )
		{
			$aGroupedSalesList[$aSalesList[$i]['sales_ID']] = array();
			array_push($aSalesIDContainer, $aSalesList[$i]['sales_ID']);
		}

		array_push($aGroupedSalesList[$aSalesList[$i]['sales_ID']], $aSalesList[$i]);
	}

	$iGrandTotal = 0;
	$reportListBlock = array();
	$iGroupNo = 0;
	$iCounter = 0;
	foreach($aGroupedSalesList as $key => $value)
	{
		$bPrint = FALSE;
		$iSubTotalBySalesID = 0;
		for ($i = 0; $i < count($value); $i++)
		{
			if ( ($i+1) == count($value) )
			{
				$bPrint = TRUE;
			}

			$aEmployeeName = $cEmployee->GetEmployeeByID($value[$i]['employee_ID']);
			$sEmployeeName = $aEmployeeName[0]['Name'];

			$aClientName = $cClient->GetClientByID($value[$i]['client_ID']);
			$sClientName = $aClientName[0]['Name'];

			$aPaymentTypeName = $cPaymentType->GetPaymentTypeByID($value[$i]['paymentType_ID']);
			$sPaymentTypeName = $aPaymentTypeName[0]['Name'];

			$aOutletName = $cOutlet->GetOutletByID($value[$i]['outlet_ID']);
			$sOutletName = $aOutletName[0]['Name'];

			list($sYear, $sMonth, $sDay) = explode("-",$value[$i]['Date']);
			
			$iSubtotal = $value[$i]['Price'] * $value[$i]['Quantity'] * ( (100 - $value[$i]['Discount']) / 100);
			$iSubTotalBySalesID += $iSubtotal;

			$sSalesOrder_FinanceNotes = "-";
			//do this only if this is a sales order
			//we get the finance notes for display in the result row.
			if ($value[$i]['sales_order_ID'] > 0 )
			{
				$aSalesOrder = $cSales->GetSalesOrderByID($value[$i]['sales_order_ID']);
				$sSalesOrder_FinanceNotes = $aSalesOrder[0]['FinanceNotes'];
			}
			

			$reportListBlock[] = array(
				"VAR_COUNTERROW" => $iCounter,
				"VAR_ROWBGCOLOR" => (($iGroupNo % 2) == 1)?"#ffffff":"#cccccc",
				"VAR_COUNTER" => $iGroupNo + 1,
				"VAR_SALESDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
				"VAR_OUTLETNAME" => $sOutletName,
				"VAR_SALESNAME" => $sEmployeeName,
				"VAR_CLIENTNAME" => $sClientName,
				"VAR_PAYMENTTYPENAME" => $sPaymentTypeName,
				"VAR_PRODUCTNAME" => $cProduct->GetProductNameByID($value[$i]['product_ID']),
				"VAR_NOTES" => ( (($value[$i]['sales_order_ID'] > 0)?"<span style='color:red'>PP</span> ":"" ). $value[$i]['Notes'] ),
				"VAR_NOTES_SALESORDER" => $sSalesOrder_FinanceNotes,
				"VAR_PRICE" => number_format($value[$i]['Price'], _NbOfDigitBehindComma_ ),
				"VAR_QUANTITY" => number_format($value[$i]['Quantity'], _NbOfDigitBehindComma_ ),
				"VAR_DISCOUNT" => number_format($value[$i]['Discount'], 2 ),
				"VAR_TOTAL" => number_format($iSubtotal, _NbOfDigitBehindComma_ ),
				"VAR_SALESID" => $value[$i]['sales_ID'],
				"VAR_SALESVERIFYNOTES" => $value[$i]['FinanceNotes'],
				"VAR_VERIFYDISABLED" => ($value[$i]['Status'] == 1 || $bPrint != TRUE)?"disabled='1'":"",
				"VAR_SALES_SUBTOTAL_BY_ID" => ($bPrint == TRUE)?number_format($iSubTotalBySalesID, _NbOfDigitBehindComma_ ):"",
				"VAR_CASH" => $i,
				"VAR_DEBIT" => $i,
				"VAR_TRANSFER" => $i,
				"VAR_SN" => ($value[$i]['SnStart'] . (($value[$i]['SnEnd'] == "")?"":("-" . $value[$i]['SnEnd']) ) )
			);
		}
		$iGrandTotal += $iSubTotalBySalesID;

		$iGroupNo++;

		$iCounter++;
	}

	$cWebsite->buildBlock("content", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => (isset($_POST['reportOutlet']))?$_POST['reportOutlet']:"0",
		"VAR_REPORTEMPLOYEE" => (isset($_POST['reportEmployee']))?$_POST['reportEmployee']:"0",
		"VAR_REPORTCLIENT" => (isset($_POST['reportClient']))?$_POST['reportClient']:"0",
		"VAR_REPORTPAYMENTTYPE" => (isset($_POST['reportPaymentType']))?$_POST['reportPaymentType']:"0",
		"VAR_REPORTPRODUCT" => (isset($_POST['reportProduct']))?$_POST['reportProduct']:"0",
		"VAR_REPORTPRODUCTCATEGORY" => (isset($_POST['reportProductCategory']))?$_POST['reportProductCategory']:"0",
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear,
		
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>