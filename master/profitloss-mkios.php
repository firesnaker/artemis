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
	* master/profitloss-mkios.php :: Profit Loss for MKIOS Page				*
	****************************************************************************
	* The profit loss page for master									*
	*															*
	* Version			: 0.1										*
	* Author			: Ricky Kurniawan (FireSnakeR)					*
	* Created			: 2013-10-15 									*
	* Last modified	: 2014-07-18									*
	* 															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classMKios.php");
	//+++ END library inclusion ++++++++++++++++++++++++++++++++++++++++++++++//

	//+++ BEGIN session initialization +++++++++++++++++++++++++++++++++++++++//
	session_start();

	if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 
	  && ($_SESSION['user_Name'] == "admin" || $_SESSION['user_Name'] == "master") )
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
	//+++ END session initialization +++++++++++++++++++++++++++++++++++++++++//

	//+++ BEGIN variable declaration and initialization ++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//+++ END variable declaration and initialization ++++++++++++++++++++++++//

	//+++ BEGIN class initialization +++++++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cMKios = new MKios;
	//+++ END class initialization +++++++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	//+++ BEGIN $_POST processing ++++++++++++++++++++++++++++++++++++++++++++//
	if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
	{
		$sBeginDate = $_POST['beginYear'] . "-" . $_POST['beginMonth'] . "-" . $_POST['beginDay'];
		$sEndDate = $_POST['endYear'] . "-" . $_POST['endMonth'] . "-" . $_POST['endDay'];

	}
	else
	{
		$sBeginDate = date("Y-m-d");;
		$sEndDate = date("Y-m-d");;
		
	}

	list($iInitialBeginYear, $iInitialBeginMonth, $iInitialBeginDay) = explode("-", $sBeginDate);
	list($iInitialEndYear, $iInitialEndMonth, $iInitialEndDay) = explode("-", $sEndDate);

	//+++ END $_POST processing ++++++++++++++++++++++++++++++++++++++++++++++//

	//get data for page
	$aSearchByFieldArray = array(
		"KodeWH" => (isset($_POST['KodeWH']) && $_POST['KodeWH'])?$_POST['KodeWH']:"",
		"VTSProduct" => (isset($_POST['VTSProduct']) && $_POST['VTSProduct'])?$_POST['VTSProduct']:"",
		"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
	);

	$aSearchExpensesByFieldArray = array(
		"KodeWH" => (isset($_POST['KodeWH']) && $_POST['KodeWH'])?$_POST['KodeWH']:"",
		"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
	);

	$aProfitLoss = $cMKios->GetProfitLoss($aSearchByFieldArray);
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/profitloss-mkios.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Laporan Laba Rugi MKIOS",
		"VAR_FORMACTION" => "master/profitloss-mkios.php",
		"VAR_FORMDISABLED" => "disabled=1",

		"VAR_TOTAL_SALES_VALUE" => number_format($aProfitLoss['Sales'], _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_PURCHASE_VALUE" => number_format($aProfitLoss['Purchase'], _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_PROFITLOSS_VALUE" => number_format($aProfitLoss['GrossPL'], _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_EXPENSES_VALUE" => number_format($aProfitLoss['Expenses'], _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_PROFITLOSSNET_VALUE" => number_format($aProfitLoss['NetPL'], _NbOfDigitBehindComma_ ),
		"VAR_TOTAL_PROFITLOSSNET_COLOR" => ($aProfitLoss['NetPL'] > 0)?"green":"red"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");
	
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
			$sDefaultBeginDay = $iInitialBeginDay;
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
			$sDefaultBeginMonth = $iInitialBeginMonth;
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
			$sDefaultBeginYear = $iInitialBeginYear;
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
			$sDefaultEndDay = $iInitialEndDay;
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
			$sDefaultEndMonth = $iInitialEndMonth;
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
			$sDefaultEndYear = $iInitialEndYear;
		}
		$endYearBlock[] = array(
			"VAR_ENDYEARVALUE" => $i,
			"VAR_ENDYEARSELECTED" => ( $i == $sDefaultEndYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "endYearBlock", $endYearBlock);

	//KodeWHBlock
	$KodeWHBlock = array();
	//we get the data grouped by KodeWH
	$aKodeWH = $cMKios->GetKodeWHList();
	for ($i = 0; $i < count($aKodeWH); $i++)
	{
		if ( isset($_POST['KodeWH']) )
		{
			$sProductValue = $_POST['KodeWH'];
		}
		else
		{
			$sProductValue = 'All';
		}

		//create the All selection for the first line
		if ($i == 0)
		{
			$KodeWHBlock[] = array(
				"VAR_KODEWHVALUE" => 'All',
				"VAR_KODEWHSELECTED" => ( 'All' == $sProductValue)?"selected":""
			);
		}

		$iValue = $aKodeWH[$i]['KodeWH'];
		$KodeWHBlock[] = array(
			"VAR_KODEWHVALUE" => $iValue,
			"VAR_KODEWHSELECTED" => ( $iValue == $sProductValue)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "KodeWHBlock", $KodeWHBlock);

	//VTSProductBlock
	$VTSProductBlock = array();
	for ($i = 0; $i < 7; $i++)
	{
		if ( isset($_POST['VTSProduct']) )
		{
			$sProductValue = $_POST['VTSProduct'];
		}
		else
		{
			$sProductValue = 'All';
		}

		switch ($i)
		{
			case 1:
				$iValue = "S005";
			break;
			case 2:
				$iValue = "S010";
			break;
			case 3:
				$iValue = "S020";
			break;
			case 4:
				$iValue = "S025";
			break;
			case 5:
				$iValue = "S050";
			break;
			case 6:
				$iValue = "S100";
			break;
			default: //case 0 is here
				$iValue = "All";
			break;
		}
		$VTSProductBlock[] = array(
			"VAR_VTSPRODUCTVALUE" => $iValue,
			"VAR_VTSPRODUCTSELECTED" => ( $iValue == $sProductValue)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "VTSProductBlock", $VTSProductBlock);


	//stockBlock
	$stockBlock = array();
	$iStockValue = 0;
	foreach ($aProfitLoss['Stock'] as $key => $value)
	{
		switch ($key)
		{
			case 'S005_Value':
				$iStockValue += $value;
				$stockBlock[] = array(
					"VAR_STOCK_QUANTITY" => number_format($aProfitLoss['Stock']['S005_Stock'], _NbOfDigitBehindComma_),
					"VAR_PRODUCT_NAME" => 'S005',
					"VAR_STOCK_PRICE" => number_format($aProfitLoss['Stock']['S005_Price'], _NbOfDigitBehindComma_),
					"VAR_STOCK_VALUE" => number_format($aProfitLoss['Stock']['S005_Value'], _NbOfDigitBehindComma_)
				);
			break;
			case 'S010_Value':
				$iStockValue += $value;
				$stockBlock[] = array(
					"VAR_STOCK_QUANTITY" => number_format($aProfitLoss['Stock']['S010_Stock'], _NbOfDigitBehindComma_),
					"VAR_PRODUCT_NAME" => 'S010',
					"VAR_STOCK_PRICE" => number_format($aProfitLoss['Stock']['S010_Price'], _NbOfDigitBehindComma_),
					"VAR_STOCK_VALUE" => number_format($aProfitLoss['Stock']['S010_Value'], _NbOfDigitBehindComma_)
				);
			break;
			case 'S020_Value':
				$iStockValue += $value;
				$stockBlock[] = array(
					"VAR_STOCK_QUANTITY" => number_format($aProfitLoss['Stock']['S020_Stock'], _NbOfDigitBehindComma_),
					"VAR_PRODUCT_NAME" => 'S020',
					"VAR_STOCK_PRICE" => number_format($aProfitLoss['Stock']['S020_Price'], _NbOfDigitBehindComma_),
					"VAR_STOCK_VALUE" => number_format($aProfitLoss['Stock']['S020_Value'], _NbOfDigitBehindComma_)
				);
			break;
			case 'S025_Value':
				$iStockValue += $value;
				$stockBlock[] = array(
					"VAR_STOCK_QUANTITY" => number_format($aProfitLoss['Stock']['S025_Stock'], _NbOfDigitBehindComma_),
					"VAR_PRODUCT_NAME" => 'S025',
					"VAR_STOCK_PRICE" => number_format($aProfitLoss['Stock']['S025_Price'], _NbOfDigitBehindComma_),
					"VAR_STOCK_VALUE" => number_format($aProfitLoss['Stock']['S025_Value'], _NbOfDigitBehindComma_)
				);
			break;
			case 'S050_Value':
				$iStockValue += $value;
				$stockBlock[] = array(
					"VAR_STOCK_QUANTITY" => number_format($aProfitLoss['Stock']['S050_Stock'], _NbOfDigitBehindComma_),
					"VAR_PRODUCT_NAME" => 'S050',
					"VAR_STOCK_PRICE" => number_format($aProfitLoss['Stock']['S050_Price'], _NbOfDigitBehindComma_),
					"VAR_STOCK_VALUE" => number_format($aProfitLoss['Stock']['S050_Value'], _NbOfDigitBehindComma_)
				);
			break;
			case 'S100_Value':
				$iStockValue += $value;
				$stockBlock[] = array(
					"VAR_STOCK_QUANTITY" => number_format($aProfitLoss['Stock']['S100_Stock'], _NbOfDigitBehindComma_),
					"VAR_PRODUCT_NAME" => 'S100',
					"VAR_STOCK_PRICE" => number_format($aProfitLoss['Stock']['S100_Price'], _NbOfDigitBehindComma_),
					"VAR_STOCK_VALUE" => number_format($aProfitLoss['Stock']['S100_Value'], _NbOfDigitBehindComma_)
				);
			break;
			default:
			break;
		}
	}
	$cWebsite->buildBlock("content", "stockBlock", $stockBlock);

	$cWebsite->template->set_var(array(
		"VAR_TOTAL_STOCK_VALUE" => number_format($iStockValue, _NbOfDigitBehindComma_ ),
	));

/*
	$iTotalProfitLoss = 0;
	$iTotalSales = 0;
	$iTotalPurchase = 0;
	$iTotalPurchaseDisplay = 0;
	$iTotalCostOfGoods = 0;
	$iTotalStock = 0;
	$stockBlock = array();
	$salesCashBlock = array();
	$aPurchaseNonCash = array();
	$aSalesNonCash = array();
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
	$iTotalExpenses = 0; //this is always zero

	$iTotalProfitLossNet = $iTotalProfitLoss - $iTotalExpenses;
	$cWebsite->template->set_var(array(
		"VAR_SELECTEDOUTLETNAME" => $aProfitLoss[0]["outlet_name"],
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
*/
	$cWebsite->template->set_var(array(
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear
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
