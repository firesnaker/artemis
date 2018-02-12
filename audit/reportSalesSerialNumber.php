<?php
	/********************************************************************
	* audit/reportSalesSerialNumber.php :: Audit Sold Serial Number Report Page*
	*********************************************************************
	* The sold serial number report page for audit						*
	*																	*
	* Version			: 0.1												*
	* Author			: FireSnakeR 										*
	* Created			: 2014-07-10 										*
	* Last modified	: 2014-07-29										*
	* 																	*
	* 				Copyright (c) 2014 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classUser.php");
		//include_once($libPath . "/classInventory.php");
		include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classProduct.php");
		include_once($libPath . "/classSales.php");
		include_once($libPath . "/classClient.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 
		  && ($_SESSION['user_Name'] == "admin" || strtolower($_SESSION['user_Name']) == "audit") )
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
		$iInventoryProduct = 0;
		$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		//$cInventory = new Inventory;
		$cOutlet = new Outlet;
		$cProduct = new Product;
		$cUser = new User($_SESSION['user_ID']);
		$cSales = new Sales;
		$cClient = new Client;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sInventoryDate = $_POST['inventoryYear'] . "-" . $_POST['inventoryMonth'] . "-" . $_POST['inventoryDay'];
			$sInventoryDateEnd = $_POST['inventoryYearEnd'] . "-" . $_POST['inventoryMonthEnd'] . "-" . $_POST['inventoryDayEnd'];
			$iInventoryProduct = $_POST['inventoryProduct'];
		}
		else
		{
			$sInventoryDate = date("Y-m-d");
			$sInventoryDateEnd = date("Y-m-d");
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aOutletList = $cOutlet->GetOutletList();
		$aProductList = $cProduct->GetProductList();

		$aSearchParam = array(
			"product_ID" => $iInventoryProduct,
			"Date" => " BETWEEN '" . $sInventoryDate . "' AND '" . $sInventoryDateEnd . "' "
		);
		$aInventoryList = $cSales->GetSalesReport( $aSearchParam );

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "audit/reportSalesSerialNumber.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"VAR_FORMACTION" => "audit/reportSalesSerialNumber.php",
		"VAR_INVENTORYPRODUCT" => (isset($_POST['inventoryProduct']))?$_POST['inventoryProduct']:"0",
		"VAR_INVENTORYDAY" => ( isset($_POST['inventoryDay']) )?$_POST['inventoryDay']:"",
		"VAR_INVENTORYMONTH" => ( isset($_POST['inventoryMonth']) )?$_POST['inventoryMonth']:"",
		"VAR_INVENTORYYEAR" => ( isset($_POST['inventoryYear']) )?$_POST['inventoryYear']:"",
		"VAR_INVENTORYDAYEND" => ( isset($_POST['inventoryDayEnd']) )?$_POST['inventoryDayEnd']:"",
		"VAR_INVENTORYMONTHEND" => ( isset($_POST['inventoryMonthEnd']) )?$_POST['inventoryMonthEnd']:"",
		"VAR_INVENTORYYEAREND" => ( isset($_POST['inventoryYearEnd']) )?$_POST['inventoryYearEnd']:"",
		"VAR_INVENTORYDATE" => $sInventoryDate,
		"TEXT_INVENTORY" => "SOLD SERIAL NUMBER"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_audit");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_audit");

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

	//inventoryDayBlock
	$inventoryDayEndBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['inventoryDayEnd']) )
		{
			$sDefaultInventoryDay = $_POST['inventoryDayEnd'];
		}
		else
		{
			$sDefaultInventoryDay = date("d");
		}
		$inventoryDayEndBlock[] = array(
			"VAR_INVENTORYDAYENDVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_INVENTORYDAYENDSELECTED" => ( ($i+1) == $sDefaultInventoryDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "inventoryDayEndBlock", $inventoryDayEndBlock);

	//inventoryMonthEndBlock
	$inventoryMonthEndBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['inventoryMonthEnd']) )
		{
			$sDefaultInventoryMonth = $_POST['inventoryMonthEnd'];
		}
		else
		{
			$sDefaultInventoryMonth = date("m");
		}
		$inventoryMonthEndBlock[] = array(
			"VAR_INVENTORYMONTHENDVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_INVENTORYMONTHENDTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_INVENTORYMONTHENDSELECTED" => ( ($i+1) == $sDefaultInventoryMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "inventoryMonthEndBlock", $inventoryMonthEndBlock);

	//inventoryYearEndBlock
	$inventoryYearEndBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['inventoryYearEnd']) )
		{
			$sDefaultInventoryYear = $_POST['inventoryYearEnd'];
		}
		else
		{
			$sDefaultInventoryYear = date("Y");
		}
		$inventoryYearEndBlock[] = array(
			"VAR_INVENTORYYEARENDVALUE" => $i,
			"VAR_INVENTORYYEARENDSELECTED" => ( $i == $sDefaultInventoryYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "inventoryYearEndBlock", $inventoryYearEndBlock);

	//inventoryListBlock
	$inventoryListBlock = array();
	$iTotalStock = 0;
	$iTotalPurchase = 0;
	$iTotalTransferIn = 0;
	$iTotalSales = 0;
	$iTotalTransferOut = 0;
	for ($i = 0; $i < count($aInventoryList); $i++)
	{
		$aOutlet = $cOutlet->GetOutletByID( $aInventoryList[$i]["outlet_ID"] );
		$sOutletName = $aOutlet[0]['Name'];

		$aClient = $cClient->GetClientByID( $aInventoryList[$i]["client_ID"] );
		$sClientName = $aClient[0]['Name'];

		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SNSTART" => $aInventoryList[$i]["SnStart"],
			"VAR_SNEND" => $aInventoryList[$i]["SnEnd"],
			"VAR_QUANTITY" => $aInventoryList[$i]["Quantity"],
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_CLIENTNAME" => $sClientName,
			"VAR_DATE" => $aInventoryList[$i]["Date"],
		);
	}
	$cWebsite->buildBlock("content", "inventoryListBlock", $inventoryListBlock);

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>