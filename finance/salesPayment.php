<?php
	/***************************************************************************
	* finance/salesPayment.php :: Finance Sales Payment Verification Page		*
	****************************************************************************
	* The sales payment verification page for finance						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan / FireSnakeR 					*
	* Created			: 2014-06-02 									*
	* Last modified	: 2014-06-02									*
	* 															*
	* 			Copyright (c) 2014 FireSnakeR							*
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
		include_once($libPath . "/classBank.php");

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
		$cBank = new Bank;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//process verify
			if ( isset($_POST['paymentSave']) && $_POST['paymentSave'] == 'Save' )
			{
				$aSalesPaymentInsert = array(
					"ID" => $_POST["salesPaymentID"],
					"sales_ID" => $_POST["ID"],
					"Date" => $_POST["paymentDate"],
					"Amount" => $_POST["paymentAmount"],
					"Notes" => $_POST["paymentNotes"],
					"IsCash" => $_POST["paymentIsCash"],
					"bank_ID" => $_POST["paymentBank"]
				);

				$cSales->SaveSalesPayment($aSalesPaymentInsert);
			}

			if ( isset($_POST['paymentEdit']) && $_POST['paymentEdit'] == 'Edit' )
			{
				$aSalesPaymentEdit = $cSales->Load($_POST['paymentID']);
				list($sYear, $sMonth, $sDay) = explode("-",$aSalesPaymentEdit[0]['Date']);
			}
			if ( isset($_POST['paymentDelete']) && $_POST['paymentDelete'] == 'Delete' )
			{
				$cSales->RemoveSalesPayment($_POST['paymentID']);
			}
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"sales.ID" => (isset($_REQUEST['ID']))?$_REQUEST['ID']:""
		);

		$aSalesList = $cSales->GetSalesReport($aSearchByFieldArray);


		$aParam = array(
			"sales_ID" => (" = " . ((isset($_REQUEST['ID']))?$_REQUEST['ID']:"0"))
		);
		$aSalesPaymentList = $cSales->ListSalesPayment($aParam);

		$aEmployeeList = $cEmployee->GetEmployeeList();
		$aClientList = $cClient->GetClientList();
		$aPaymentTypeList = $cPaymentType->GetPaymentTypeList();
		$aProductList = $cProduct->GetProductList();
		$aParam = array();
		$aBankList = $cBank->GetList($aParam);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "finance/salesPayment.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Sales Payment",
		"VAR_BEGINYEAR" => (isset($aSalesPaymentEdit))?date("Y", mktime(0,0,0, $sMonth, $sDay, $sYear)):date("Y"),
		"VAR_BEGINMONTH" => (isset($aSalesPaymentEdit))?date("m", mktime(0,0,0, $sMonth, $sDay, $sYear)):date("m"),
		"VAR_BEGINDAY" => (isset($aSalesPaymentEdit))?date("d", mktime(0,0,0, $sMonth, $sDay, $sYear)):date("d"),
		"VAR_OLDESTYEAR" => _OldestYear_,
		"VAR_FORMACTION" => "finance/salesPayment.php",
		"VAR_SALESID" => $_REQUEST['ID'],

		"VAR_SALESPAYMENT_ID" => (isset($aSalesPaymentEdit))?$aSalesPaymentEdit[0]['ID']:"",
		"VAR_SALESPAYMENT_AMOUNT" => (isset($aSalesPaymentEdit))?str_replace(",", "", number_format($aSalesPaymentEdit[0]['Amount'], 0)):"",
		"VAR_SALESPAYMENT_NOTES" => (isset($aSalesPaymentEdit))?$aSalesPaymentEdit[0]['Notes']:"",
		"VAR_SALESPAYMENT_CASHSELECTED" => (isset($aSalesPaymentEdit) && $aSalesPaymentEdit[0]['IsCash'] == 1)?"checked":"",
		"VAR_SALESPAYMENT_BANKSELECTED" => (isset($aSalesPaymentEdit) && $aSalesPaymentEdit[0]['IsCash'] == 0)?"checked":""
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_finance");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_finance");

	//bankListBlock
	$bankListBlock = array();
	for ($i = 0; $i < count($aBankList); $i++)
	{
		$bankListBlock[] = array(
			"VAR_BANKID" => $aBankList[$i]['ID'],
			"VAR_BANKNAME" => $aBankList[$i]['Name'],
			"VAR_BANKSELECTED" => (isset($aSalesPaymentEdit) && $aSalesPaymentEdit[0]['bank_ID'] == $aBankList[$i]['ID'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "paymentBankBlock", $bankListBlock);

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
		
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
	));

	//paymentListBlock
	$iPaymentGrandtotal = 0;
	$paymentListBlock = array();
	for ($i = 0; $i < count($aSalesPaymentList); $i++)
	{
		$sBankName = '';
		if ( $aSalesPaymentList[$i]['IsCash'] == 0)
		{
			$aBank = $cBank->Load($aSalesPaymentList[$i]['bank_ID']);
			$sBankName = $aBank[0]['Name'];
		}

		list($sYear, $sMonth, $sDay) = explode("-",$aSalesPaymentList[$i]['Date']);

		$paymentListBlock[] = array(
			"VAR_PAYMENTLIST_NUMBER" => $i+1,
			"VAR_PAYMENTLISTROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_PAYMENTLIST_ID" => $aSalesPaymentList[$i]['ID'],
			"VAR_PAYMENTLIST_DATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_PAYMENTLIST_CASH_BANK" => ($aSalesPaymentList[$i]['IsCash'] == 1)?"Cash":"Bank ". $sBankName,
			"VAR_PAYMENTLIST_AMOUNT" => number_format($aSalesPaymentList[$i]['Amount'], _NbOfDigitBehindComma_ ),
			"VAR_PAYMENTLIST_NOTES" => $aSalesPaymentList[$i]['Notes']
		);
		$iPaymentGrandtotal += $aSalesPaymentList[$i]['Amount'];
	}
	$cWebsite->buildBlock("content", "paymentListBlock", $paymentListBlock);

	$cWebsite->template->set_var(array(
		"VAR_PAYMENTLIST_GRANDTOTAL" => number_format($iPaymentGrandtotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>