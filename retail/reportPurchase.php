<?php
	/***************************************************************************
	* retail/reportPurchase.php :: Retail Purchase Report Page				*
	****************************************************************************
	* The purchase report page for retail								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-11-15 									*
	* Last modified	: 2014-08-21									*
	* 															*
	* 			Copyright (c) 2011-2014 FireSnakeR						*
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
	include_once($libPath . "/classPurchase.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cPurchase = new Purchase;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Purchase Report";
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sBeginDate = $_POST['beginYear'] . "-" . $_POST['beginMonth'] . "-" . $_POST['beginDay'];
			$sEndDate = $_POST['endYear'] . "-" . $_POST['endMonth'] . "-" . $_POST['endDay'];
		}
		else
		{
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID'],
			"product_ID" => (isset($_POST['reportProduct']) && $_POST['reportProduct'])?$_POST['reportProduct']:"",
			"productCategory_ID" => (isset($_POST['reportProductCategory']) && $_POST['reportProductCategory'])?$_POST['reportProductCategory']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aPurchaseList = $cPurchase->GetPurchaseReport($aSearchByFieldArray);

		$aOutletList = $cOutlet->GetOutletList();

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
		"content" => "retail/reportPurchase.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Report Purchase",
		"VAR_FORM_ACTION" => "retail/reportPurchase.php"
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
	$iTotalQuantity = 0;
	for ($i = 0; $i < count($aPurchaseList); $i++)
	{
		$aOutletName = $cOutlet->GetOutletByID($aPurchaseList[$i]['outlet_ID']);
		$sOutletName = $aOutletName[0]['Name'];

		list($sYear, $sMonth, $sDay) = explode("-",$aPurchaseList[$i]['Date']);

		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_PURCHASEDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_PRODUCTNAME" => $cProduct->GetProductNameByID($aPurchaseList[$i]['product_ID']),
			"VAR_QUANTITY" => number_format( $aPurchaseList[$i]['Quantity'], _NbOfDigitBehindComma_ ),
			"VAR_NOTES" => $aPurchaseList[$i]['Notes'],
			"VAR_SN" => ($aPurchaseList[$i]['SnStart'] . (($aPurchaseList[$i]['SnEnd'] == "")?"":("-" . $aPurchaseList[$i]['SnEnd']) ) )
		);
		$iTotalQuantity += $aPurchaseList[$i]['Quantity'];
	}
	$cWebsite->buildBlock("content", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => (isset($_POST['reportOutlet']) && $_POST['reportOutlet'])?$_POST['reportOutlet']:"0",
		"VAR_REPORTPRODUCT" => (isset($_POST['reportProduct']))?$_POST['reportProduct']:"0",
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear,
		"VAR_TOTALQUANTITY" => number_format( $iTotalQuantity, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>