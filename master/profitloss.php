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
	* master/profitloss.php :: Profit Loss Page								*
	*********************************************************************
	* The profit loss page for master											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2012-07-24 										*
	* Last modified	: 2013-01-05										*
	* 																	*
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
		  && ($_SESSION['user_Name'] == "admin" ) )
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

			//process verify purchase non cash
			if ( isset($_POST['verifyPurchaseNonCashSubmit']) && $_POST['verifyPurchaseNonCashSubmit'] == 'Verify' )
			{
				$cPurchase->Verify($_POST['purchaseID'], $_POST['verifyNotes']);
			}

			//process update Payment Type
			if ( isset($_POST['verifySalesNonCashSubmit']) && $_POST['verifySalesNonCashSubmit'] == 'Verify' )
			{
				$cSales->Verify($_POST['salesID'], $_POST['financeNotes']);
			}

			$iOutlet = $_POST['reportOutlet'];
			$iProduct = $_POST['reportProduct'];
			$iProductCategory = $_POST['reportProductCategory'];
		}
		else
		{
			//we trap the get here
			if ( count($_GET) > 0 )
			{
				$sBeginDate = $_GET['dateBegin'];
				$sEndDate = $_GET['dateEnd'];
				$iOutlet = $_GET['reportOutlet'];
				$iProduct = $_GET['reportProduct'];
				$iProductCategory = $_GET['reportProductCategory'];
			}


			$sBeginDate = $sFixedBeginDate;
			$sEndDate = $sFixedBeginDate;
		}

		list($iBeginYear, $iBeginMonth, $iBeginDay) = explode("-", $sBeginDate);
		list($iEndYear, $iEndMonth, $iEndDay) = explode("-", $sEndDate);

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		//get data for page
		$aSearchByFieldArray = array(
			"outlet_ID" => ($iOutlet > 0)?$iOutlet:"",
			"productCategory_ID" => ($iProductCategory > 0)?$iProductCategory:"",
			"product_ID" => ($iProduct > 0)?$iProduct:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aSearchExpensesByFieldArray = array(
			"outlet_ID" => ($iOutlet > 0)?$iOutlet:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);
		if ( count($_POST) > 0 || count($_GET) > 0 )
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
		"content" => "master/profitloss.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Laporan Laba Rugi",
		"VAR_OLDESTYEAR" => _OldestYear_,
		"VAR_BEGINYEAR" => $iBeginYear,
		"VAR_BEGINMONTH" => $iBeginMonth,
		"VAR_BEGINDAY" => $iBeginDay,
		"VAR_ENDYEAR" => $iEndYear,
		"VAR_ENDMONTH" => $iEndMonth,
		"VAR_ENDDAY" => $iEndDay,
		"VAR_HUMANDATEBEGIN" => date("d-M-Y", mktime(0,0,0,$iBeginMonth, $iBeginDay, $iBeginYear )),
		"VAR_HUMANDATEEND" => date("d-M-Y", mktime(0,0,0, $iEndMonth, $iEndDay, $iEndYear))
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

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
				$iProductID = $aProductList[$j]["ID"];
				$sProductName = $aProductList[$j]["name"];
				$j = count($aProductList) + 1; //exit loop by increasing $j
			}
		}

		//prepare data for purchase non cash block
		if ( count($aProfitLoss[$i]['Data_Purchase_Non_Cash']) > 0 )
		{
			for ($j = 0; $j < count($aProfitLoss[$i]['Data_Purchase_Non_Cash']); $j++)
			{
				//we will need the sales ID, product name, quanitty, price, value, subtotal
				$aPurchaseNonCash[] = array(
					"purchase_ID" => $aProfitLoss[$i]["Data_Purchase_Non_Cash"][$j]['purchase_ID'],
				 	"product_Name" => $sProductName,
				 	"quantity" => $aProfitLoss[$i]["Data_Purchase_Non_Cash"][$j]['Quantity'],
				 	"price" => $aProfitLoss[$i]["Data_Purchase_Non_Cash"][$j]['Price']
				);
			}
		}
		
		//prepare data for sales non cash block
		if ( count($aProfitLoss[$i]["Data_Sales_Non_Cash"]) > 0 )
		{
			for ($j = 0; $j < count($aProfitLoss[$i]["Data_Sales_Non_Cash"]); $j++)
			{
				//we will need the sales ID, product name, quanitty, price, value, subtotal
				$aSalesNonCash[] = array(
					"sales_ID" => $aProfitLoss[$i]["Data_Sales_Non_Cash"][$j]['sales_ID'],
				 	"product_Name" => $sProductName,
				 	"quantity" => $aProfitLoss[$i]["Data_Sales_Non_Cash"][$j]['Quantity'],
				 	"price" => $aProfitLoss[$i]["Data_Sales_Non_Cash"][$j]['Price'],
				 	"discount" => $aProfitLoss[$i]["Data_Sales_Non_Cash"][$j]['Discount']
				);
			}
		}

		//prepare data for sales cash block
		if ( count($aProfitLoss[$i]["Data_Sales_Cash"]) > 0 )
		{
			for ($j = 0; $j < count($aProfitLoss[$i]["Data_Sales_Cash"]); $j++)
			{
				//we will need the sales ID, product name, quanitty, price, value, subtotal
				$aSalesCash[] = array(
					"sales_ID" => $aProfitLoss[$i]["Data_Sales_Cash"][$j]['sales_ID'],
				 	"product_Name" => $sProductName,
				 	"quantity" => $aProfitLoss[$i]["Data_Sales_Cash"][$j]['Quantity'],
				 	"price" => $aProfitLoss[$i]["Data_Sales_Cash"][$j]['Price'],
				 	"discount" => $aProfitLoss[$i]["Data_Sales_Cash"][$j]['Discount']
				);
			}
		}

		$stockBlock[] = array(
			"VAR_STOCKPRODUCTID" => $iProductID,
			"VAR_PRODUCT_NAME" => $sProductName,
			"VAR_STOCK_QUANTITY" => number_format($aProfitLoss[$i]['Closing_Inventory_Quantity'], _NbOfDigitBehindComma_ ),
			"VAR_STOCK_VALUE" => number_format($aProfitLoss[$i]['Closing_Inventory'], _NbOfDigitBehindComma_ ),
			"VAR_STOCK_PRICE" => number_format($aProfitLoss[$i]['Avg_Purchase_Price'], 2 )
		);
	}

	//first we need to group the $aPurchaseNonCash by purchase_ID
	$aGroupedPurchaseNonCash = array();
	$aPurchaseNonCashID = array();
	for ($i = 0; $i < count($aPurchaseNonCash); $i++)
	{
		if ( !in_array($aPurchaseNonCash[$i]['purchase_ID'], $aPurchaseNonCashID) )
		{
			$aGroupedPurchaseNonCash[$aPurchaseNonCash[$i]['purchase_ID']] = array();
			array_push($aPurchaseNonCashID, $aPurchaseNonCash[$i]['purchase_ID']);
		}

		array_push($aGroupedPurchaseNonCash[$aPurchaseNonCash[$i]['purchase_ID']], $aPurchaseNonCash[$i]);
	}

	$purchaseNonCashBlock = array();
	foreach($aGroupedPurchaseNonCash as $key => $value)
	{
		$bPrint = FALSE;
		$iSubTotalByPurchaseID = 0;
		for ($i = 0; $i < count($value); $i++)
		{
			$iPurchaseValue = $value[$i]['price'] * $value[$i]['quantity'];
			$iSubTotalByPurchaseID += $iPurchaseValue;

			if ( ($i+1) == count($value) )
			{
				$bPrint = TRUE;
			}

			$purchaseNonCashBlock[] = array(
				"VAR_PURCHASEID" => $value[$i]['purchase_ID'],
				"VAR_PRODUCT_NAME" => $value[$i]['product_Name'],
				"VAR_PURCHASE_QUANTITY" => number_format($value[$i]['quantity'], _NbOfDigitBehindComma_ ),
				"VAR_PURCHASE_PRICE" => number_format($value[$i]['price'], _NbOfDigitBehindComma_ ),
				"VAR_PURCHASE_VALUE" => number_format( $iPurchaseValue, _NbOfDigitBehindComma_ ),
				"VAR_PURCHASE_SUBTOTAL_BY_ID" => ($bPrint == TRUE)?number_format($iSubTotalByPurchaseID, _NbOfDigitBehindComma_ ):"",
				"VAR_VERIFY_DISABLED" => ($bPrint == TRUE)?"":"disabled=1"
			);
		}
	}

	//first we need to group the $aSalesNonCash by sales_ID
	$aGroupedSalesNonCash = array();
	$aSalesNonCashID = array();
	for ($i = 0; $i < count($aSalesNonCash); $i++)
	{
		if ( !in_array($aSalesNonCash[$i]['sales_ID'], $aSalesNonCashID) )
		{
			$aGroupedSalesNonCash[$aSalesNonCash[$i]['sales_ID']] = array();
			array_push($aSalesNonCashID, $aSalesNonCash[$i]['sales_ID']);
		}

		array_push($aGroupedSalesNonCash[$aSalesNonCash[$i]['sales_ID']], $aSalesNonCash[$i]);
	}

	$salesNonCashBlock = array();
	foreach($aGroupedSalesNonCash as $key => $value)
	{
		$bPrint = FALSE;
		$iSubTotalBySalesID = 0;
		for ($i = 0; $i < count($value); $i++)
		{
			$iSalesValue = $value[$i]['price'] * $value[$i]['quantity'] * ((100 - $value[$i]['discount']) / 100);
			
			$iSubTotalBySalesID += $iSalesValue;

			if ( ($i+1) == count($value) )
			{
				$bPrint = TRUE;
			}

			$salesNonCashBlock[] = array(
				"VAR_SALESID" => $value[$i]['sales_ID'],
				"VAR_PRODUCT_NAME" => $value[$i]['product_Name'],
				"VAR_SALES_QUANTITY" => number_format($value[$i]['quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SALES_PRICE" => number_format($value[$i]['price'], _NbOfDigitBehindComma_ ),
				"VAR_SALES_VALUE" => number_format( $iSalesValue, _NbOfDigitBehindComma_ ),
				"VAR_SALES_SUBTOTAL_BY_ID" => ($bPrint == TRUE)?number_format($iSubTotalBySalesID, _NbOfDigitBehindComma_ ):"",
				"VAR_VERIFY_DISABLED" => ($bPrint == TRUE)?"":"disabled=1"
			);
		}
	}

	//first we need to group the $aSalesCash by sales_ID
	$aGroupedSalesCash = array();
	$aSalesCashID = array();
	for ($i = 0; $i < count($aSalesCash); $i++)
	{
		if ( !in_array($aSalesCash[$i]['sales_ID'], $aSalesCashID) )
		{
			$aGroupedSalesCash[$aSalesCash[$i]['sales_ID']] = array();
			array_push($aSalesCashID, $aSalesCash[$i]['sales_ID']);
		}

		array_push($aGroupedSalesCash[$aSalesCash[$i]['sales_ID']], $aSalesCash[$i]);
	}

	$salesCashBlock = array();
	foreach($aGroupedSalesCash as $key => $value)
	{
		$bPrint = FALSE;
		$iSubTotalBySalesID = 0;
		for ($i = 0; $i < count($value); $i++)
		{
			$iSalesValue = $value[$i]['price'] * $value[$i]['quantity'] * ((100 - $value[$i]['discount']) / 100);
			
			$iSubTotalBySalesID += $iSalesValue;

			if ( ($i+1) == count($value) )
			{
				$bPrint = TRUE;
			}

			$salesCashBlock[] = array(
				"VAR_SALESID" => $value[$i]['sales_ID'],
				"VAR_PRODUCT_NAME" => $value[$i]['product_Name'],
				"VAR_SALES_QUANTITY" => number_format($value[$i]['quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SALES_PRICE" => number_format($value[$i]['price'], _NbOfDigitBehindComma_ ),
				"VAR_SALES_VALUE" => number_format( $iSalesValue, _NbOfDigitBehindComma_ ),
				"VAR_SALES_SUBTOTAL_BY_ID" => ($bPrint == TRUE)?number_format($iSubTotalBySalesID, _NbOfDigitBehindComma_ ):"",
				"VAR_VERIFY_DISABLED" => ($bPrint == TRUE)?"":"disabled=1"
			);
		}
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

	$cWebsite->buildBlock("content", "purchaseBlock", $purchaseNonCashBlock);
	$cWebsite->buildBlock("content", "salesNonCashBlock", $salesNonCashBlock);
	$cWebsite->buildBlock("content", "salesCashBlock", $salesCashBlock);
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
