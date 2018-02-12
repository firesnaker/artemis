<?php
	/********************************************************************
	* audit/inventory.php :: Profit Loss Page								*
	*********************************************************************
	* The inventory page for audit											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2014-02-11 										*
	* Last modified	: 2014-07-29										*
	* 																	*
	* 				Copyright (c) 2014 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classReport.php");
		include_once($libPath . "/classProduct.php");
		include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classExpenses.php");
		include_once($libPath . "/classUser.php");
		include_once($libPath . "/classPurchase.php");
		include_once($libPath . "/classSales.php");

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
		$sFixedBeginDate = date("Y-m-d");
		$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
		$iOutlet = 0;
		$iProduct = 0;
		$iProductCategory = 0;
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cReport = new Report;
		$cProduct = new Product;
		$cOutlet = new Outlet;
		$cExpenses = new Expenses;
		$cUser = new User($_SESSION['user_ID']);
		$cPurchase = new Purchase;
		$cSales = new Sales;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sBeginDate = $_POST['dateBegin'];
			$sEndDate = $_POST['dateEnd'];

			$iOutlet = $_POST['reportOutlet'];
			$iProduct = $_POST['reportProduct'];
			$iProductCategory = $_POST['reportProductCategory'];
		}
		else
		{
			$sBeginDate = $sFixedBeginDate;
			$sEndDate = $sFixedBeginDate;
		}

		list($iBeginYear, $iBeginMonth, $iBeginDay) = explode("-", $sBeginDate);
		list($iEndYear, $iEndMonth, $iEndDay) = explode("-", $sEndDate);

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		//get data for page
		$aSearchByFieldArray = array(
			"outlet_ID" => ($iOutlet > 0)?$iOutlet:"",
			"product_ID" => ($iProduct > 0)?$iProduct:"",
			"productCategory_ID" => ($iProductCategory > 0)?$iProductCategory:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aSearchExpensesByFieldArray = array(
			"outlet_ID" => ($iOutlet > 0)?$iOutlet:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);
		if (count($_POST) > 0)
		{
			$aProfitLoss = $cReport->GetProfitLoss($aSearchByFieldArray);
		}
		else
		{
			$aProfitLoss = array();
		}

		$aExpensesList = $cExpenses->GetExpensesReport($aSearchExpensesByFieldArray);

		$aOutletList = $cOutlet->GetActiveOutletList();
		$aProductList = $cProduct->GetProductList();
		$aProductCategoryList = $cProduct->GetCategoryList();
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "audit/inventory.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Laporan Stok",
		"VAR_OLDESTYEAR" => _OldestYear_,
		"VAR_BEGINYEAR" => $iBeginYear,
		"VAR_BEGINMONTH" => $iBeginMonth,
		"VAR_BEGINDAY" => $iBeginDay,
		"VAR_ENDYEAR" => $iEndYear,
		"VAR_ENDMONTH" => $iEndMonth,
		"VAR_ENDDAY" => $iEndDay,

		"VAR_FORMACTION" => "audit/inventory.php"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_audit");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_audit");

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$outletListBlock[] = array(
			"VAR_OUTLETID" => $aOutletList[$i]['ID'],
			"VAR_OUTLETNAME" => $aOutletList[$i]['name'],
			"VAR_OUTLETSELECTED" => ($aOutletList[$i]['ID'] == $iOutlet)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_PRODUCTSELECTED" => ($aProductList[$i]['ID'] == $iProduct)?"selected":""
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

	$iTotalProfitLoss = 0;
	$iTotalSales = 0;
	$iTotalPurchase = 0;
	$iTotalPurchaseDisplay = 0;
	$iTotalCostOfGoods = 0;
	$iTotalStock = 0;
	$iTotalSalesNonCash = 0;
	$iTotalSalesCash = 0;
	$iTotalPurchaseNonCash = 0;
	$stockBlock = array();
	$salesCashBlock = array();
	$aPurchaseNonCash = array();
	$aSalesNonCash = array();
	$aSalesCash = array();
	for($i = 0; $i < count($aProfitLoss); $i++)
	{
		$iTotalCostOfGoods += ($aProfitLoss[$i]['Opening_Inventory'] + $aProfitLoss[$i]['Total_Purchase']) - $aProfitLoss[$i]['Closing_Inventory'];

		$iTotalSales += $aProfitLoss[$i]['Total_Sales'];
		$iTotalSalesCash += $aProfitLoss[$i]['Total_Sales_Cash'];
		$iTotalSalesNonCash += $aProfitLoss[$i]['Total_Sales_Non_Cash'];

		$iTotalPurchase += $aProfitLoss[$i]['Total_Purchase'];
		$iTotalPurchaseDisplay += $aProfitLoss[$i]['Total_Purchase_Display'];
		$iTotalPurchaseNonCash += $aProfitLoss[$i]['Total_Purchase_Non_Cash'];

		$iTotalStock += $aProfitLoss[$i]['Closing_Inventory'];

		//get the product name
		$sProductName = "";
		for ($j = 0; $j < count($aProductList); $j++)
		{
			if ($aProductList[$j]["ID"] == $aProfitLoss[$i]['Product_ID'])
			{
				$sProductName = $aProductList[$j]["name"];
				$j = count($aProductList) + 1; //exit loop by increasing $j
			}
		}

		$stockBlock[] = array(
			"VAR_PRODUCT_NAME" => $sProductName,
			"VAR_STOCK_QUANTITY" => number_format($aProfitLoss[$i]['Closing_Inventory_Quantity'], _NbOfDigitBehindComma_ ),
			"VAR_STOCK_VALUE" => number_format($aProfitLoss[$i]['Closing_Inventory'], _NbOfDigitBehindComma_ ),
			"VAR_STOCK_PRICE" => number_format($aProfitLoss[$i]['Avg_Purchase_Price'], 2 )
		);
	}

	//re-arrange the stock to show product with value at the top 
	$stockBlockWithQuantity = array();
	$stockBlockWithZeroQuantity = array();
	foreach($stockBlock as $key => $value)
	{
		if ($value['VAR_STOCK_QUANTITY'] == 0)
		{
			$stockBlockWithZeroQuantity[] = $value;
		}
		else
		{
			$stockBlockWithQuantity[] = $value;
		}
	}

	$stockBlock = array();
	foreach ($stockBlockWithQuantity as $key => $value)
	{
		$stockBlock[] = $value;
	}
	foreach ($stockBlockWithZeroQuantity as $key => $value)
	{
		$stockBlock[] = $value;
	}

	$cWebsite->buildBlock("content", "stockBlock", $stockBlock);

	$iTotalProfitLoss = $iTotalSales - $iTotalCostOfGoods;

	//expensesListBlock
	$iTotalExpenses = 0;
	//$expensesListBlock = array();
	for ($i = 0; $i < count($aExpensesList); $i++)
	{
		$iTotalExpenses += $aExpensesList[$i]['Price'];
	}
	//$cWebsite->buildBlock("content", "expensesBlock", $expensesListBlock);

	$iTotalProfitLossNet = $iTotalProfitLoss - $iTotalExpenses;
	$cWebsite->template->set_var(array(
		"VAR_SELECTEDOUTLETNAME" => "",
		"VAR_TOTAL_SALES_VALUE" => number_format($iTotalSales, _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_PURCHASE_VALUE" => number_format($iTotalPurchaseDisplay, _NbOfDigitBehindComma_ ) . " * For outlet with Purchase Data Only.",
		"VAR_TOTAL_PROFITLOSS_VALUE" => number_format($iTotalProfitLoss, _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_EXPENSES_VALUE" => number_format($iTotalExpenses, _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_PROFITLOSSNET_COLOR" => ($iTotalProfitLossNet > 0)?"green":"red",
		"VAR_TOTAL_PROFITLOSSNET_VALUE" => number_format($iTotalProfitLossNet, _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_STOCK_VALUE" => number_format($iTotalStock, _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_NON_CASH_SALES_VALUE" => number_format($iTotalSalesNonCash, _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_CASH_SALES_VALUE" => number_format($iTotalSalesCash, _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_NON_CASH_PURCHASE_VALUE" => number_format($iTotalPurchaseNonCash, _NbOfDigitBehindComma_ )
	));

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => ($iOutlet > 0)?$iOutlet:"0",
		"VAR_REPORTPRODUCT" => ($iProduct > 0)?$iProduct:"0",
		"VAR_REPORTPRODUCTCATEGORY" => ($iProductCategory > 0)?$iProductCategory:"0",
		"VAR_DATEBEGIN" => $sBeginDate,
		"VAR_DATEEND" => $sEndDate
	));

	$cWebsite->template->set_var(array(
		"TEXT_QUANTITY" => "Jumlah",
		"TEXT_NAME" => "Nama Barang",
		"TEXT_PRICE" => "Harga Satuan",
		"TEXT_VALUE" => "Nilai",
		"TEXT_SUBTOTAL" => "Subtotal",
		"TEXT_ACTION" => "Tindakan"
	));

	$cWebsite->buildContent("VAR_CONTENT");

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//
?>