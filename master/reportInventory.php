<?php
	/***************************************************************************
	* master/reportInventory.php :: Master Index Page						*
	****************************************************************************
	* The report Inventory page for master								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-07-07 									*
	* Last modified	: 2014-08-01									*
	*															*
	*			Copyright (c) 2010-2014 FireSnakeR						*
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
	include_once($libPath . "/classInventory.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classProduct.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cInventory = new Inventory;
	$cOutlet = new Outlet;
	$cProduct = new Product;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iInventoryProduct = 0;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	$sPageName = "Report Inventory";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sInventoryDate = $_POST['inventoryYear'] . "-" . $_POST['inventoryMonth'] . "-" . $_POST['inventoryDay'];
			$iInventoryProduct = $_POST['inventoryProduct'];
		}
		else
		{
			$sInventoryDate = date("Y-m-d");
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aOutletList = $cOutlet->GetOutletList();
		$aProductList = $cProduct->GetProductList();

		$aInventoryList = $cInventory->CalculateInventoryByProductID($iInventoryProduct, $sInventoryDate);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/reportInventory.htm"
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
		"VAR_INVENTORYPRODUCT" => (isset($_POST['inventoryProduct']))?$_POST['inventoryProduct']:"0",
		"VAR_INVENTORYDAY" => ( isset($_POST['inventoryDay']) )?$_POST['inventoryDay']:"",
		"VAR_INVENTORYMONTH" => ( isset($_POST['inventoryMonth']) )?$_POST['inventoryMonth']:"",
		"VAR_INVENTORYYEAR" => ( isset($_POST['inventoryYear']) )?$_POST['inventoryYear']:"",
		"VAR_INVENTORYDATE" => $sInventoryDate,
		"TEXT_INVENTORY" => "INVENTORY"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_PRODUCTSELECTED" => (isset($_POST['inventoryProduct']) && $aProductList[$i]['ID'] == $_POST['inventoryProduct'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productListBlock", $productListBlock);

	//inventoryDayBlock
	$inventoryDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['inventoryDay']) )
		{
			$sDefaultInventoryDay = $_POST['inventoryDay'];
		}
		else
		{
			$sDefaultInventoryDay = date("d");
		}
		$inventoryDayBlock[] = array(
			"VAR_INVENTORYDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_INVENTORYDAYSELECTED" => ( ($i+1) == $sDefaultInventoryDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "inventoryDayBlock", $inventoryDayBlock);

	//inventoryMonthBlock
	$inventoryMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['inventoryMonth']) )
		{
			$sDefaultInventoryMonth = $_POST['inventoryMonth'];
		}
		else
		{
			$sDefaultInventoryMonth = date("m");
		}
		$inventoryMonthBlock[] = array(
			"VAR_INVENTORYMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_INVENTORYMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_INVENTORYMONTHSELECTED" => ( ($i+1) == $sDefaultInventoryMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "inventoryMonthBlock", $inventoryMonthBlock);

	//inventoryYearBlock
	$inventoryYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['inventoryYear']) )
		{
			$sDefaultInventoryYear = $_POST['inventoryYear'];
		}
		else
		{
			$sDefaultInventoryYear = date("Y");
		}
		$inventoryYearBlock[] = array(
			"VAR_INVENTORYYEARVALUE" => $i,
			"VAR_INVENTORYYEARSELECTED" => ( $i == $sDefaultInventoryYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "inventoryYearBlock", $inventoryYearBlock);

	//inventoryListBlock
	$inventoryListBlock = array();
	$iTotalStock = 0;
	$iTotalPurchase = 0;
	$iTotalTransferIn = 0;
	$iTotalSales = 0;
	$iTotalTransferOut = 0;
	for ($i = 0; $i < count($aInventoryList); $i++)
	{
		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_OUTLETNAME" => $aInventoryList[$i]["OutletName"],
			"VAR_PURCHASE" => ($aInventoryList[$i]["Purchase"])?number_format($aInventoryList[$i]["Purchase"], _NbOfDigitBehindComma_):"-",
			"VAR_TRANSFERIN" => (isset($aInventoryList[$i]["TransferIn"]) && $aInventoryList[$i]["TransferIn"] > 0)?number_format($aInventoryList[$i]["TransferIn"], _NbOfDigitBehindComma_):"-",
			"VAR_SALES" => ($aInventoryList[$i]["Sales"])?number_format($aInventoryList[$i]["Sales"], _NbOfDigitBehindComma_):"-",
			"VAR_TRANSFEROUT" => (isset($aInventoryList[$i]["TransferOut"]) && $aInventoryList[$i]["TransferOut"] > 0)?number_format($aInventoryList[$i]["TransferOut"], _NbOfDigitBehindComma_):"-",
			"VAR_STOCKQTY" => ($aInventoryList[$i]["Stok"])?number_format($aInventoryList[$i]["Stok"], _NbOfDigitBehindComma_):"-"
		);
		if ( $i < count($aInventoryList)-1 )
		{
			$iTotalStock += $aInventoryList[$i]["Stok"];
			$iTotalPurchase += $aInventoryList[$i]["Purchase"];
			$iTotalTransferIn += $aInventoryList[$i]["TransferIn"];
			$iTotalSales += $aInventoryList[$i]["Sales"];
			$iTotalTransferOut += $aInventoryList[$i]["TransferOut"];
		}
	}
	$cWebsite->buildBlock("content", "inventoryListBlock", $inventoryListBlock);

	$cWebsite->template->set_var(array(
		"VAR_TOTALSTOCK" => number_format($iTotalStock, _NbOfDigitBehindComma_),
		"VAR_TOTALPURCHASE" => number_format($iTotalPurchase, _NbOfDigitBehindComma_),
		"VAR_TOTALTRANSFERIN" => number_format($iTotalTransferIn, _NbOfDigitBehindComma_),
		"VAR_TOTALSALES" => number_format($iTotalSales, _NbOfDigitBehindComma_),
		"VAR_TOTALTRANSFEROUT" => number_format($iTotalTransferOut, _NbOfDigitBehindComma_)
	));

	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>