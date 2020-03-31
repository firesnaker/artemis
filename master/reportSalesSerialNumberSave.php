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
	* master/reportSalesSerialNumberSave.php :: Master Sold SN Report Page		*
	****************************************************************************
	* The sold serial number report page for master						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2014-07-10 									*
	* Last modified	: 2014-08-01									*
	* 															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') && !$gate->is_valid_role('user_ID', 'user_Name', 'master') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classExport.php");
	include_once($libPath . "/classClient.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cOutlet = new Outlet;
	$cProduct = new Product;
	$cUser = new User($_SESSION['user_ID']);
	$cSales = new Sales;
	$cExport = new Export;
	$cClient = new Client;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iInventoryProduct = 0;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sInventoryDate = $_GET['inventoryYear'] . "-" . $_GET['inventoryMonth'] . "-" . $_GET['inventoryDay'];
			$sInventoryDateEnd = $_GET['inventoryYearEnd'] . "-" . $_GET['inventoryMonthEnd'] . "-" . $_GET['inventoryDayEnd'];
			$iInventoryProduct = $_GET['inventoryProduct'];
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

		$aProduct = $cProduct->GetProductByID( $iInventoryProduct );
		$sProductName = $aProduct[0]['Name'];

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/reportSalesSerialNumber.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"VAR_FORMACTION" => "master/reportSalesSerialNumber.php",
		"VAR_FORMSAVE" => "master/reportSalesSerialNumberSave.php",
		"VAR_INVENTORYPRODUCT" => (isset($_GET['inventoryProduct']))?$_GET['inventoryProduct']:"0",
		"VAR_INVENTORYDAY" => ( isset($_GET['inventoryDay']) )?$_GET['inventoryDay']:"",
		"VAR_INVENTORYMONTH" => ( isset($_GET['inventoryMonth']) )?$_GET['inventoryMonth']:"",
		"VAR_INVENTORYYEAR" => ( isset($_GET['inventoryYear']) )?$_GET['inventoryYear']:"",
		"VAR_INVENTORYDAYEND" => ( isset($_GET['inventoryDayEnd']) )?$_GET['inventoryDayEnd']:"",
		"VAR_INVENTORYMONTHEND" => ( isset($_GET['inventoryMonthEnd']) )?$_GET['inventoryMonthEnd']:"",
		"VAR_INVENTORYYEAREND" => ( isset($_GET['inventoryYearEnd']) )?$_GET['inventoryYearEnd']:"",
		"VAR_INVENTORYDATE" => $sInventoryDate,
		"TEXT_INVENTORY" => "SOLD SERIAL NUMBER"
	));
	
	if ( $cUser->Username == "admin" )
	{
		$cWebsite->template->set_block("navigation", "navigation_top_master");
		$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

		$cWebsite->template->set_block("navigation", "navigation_left");
		$cWebsite->template->parse("VAR_NAVIGATIONLEFT", "navigation_left");
	}
	else
	{
		$cWebsite->template->set_block("navigation", "navigation_top");
		$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top");

		$cWebsite->template->set_block("navigation", "navigation_left");
		$cWebsite->template->parse("", "navigation_left");
	}

	/*$cWebsite->template->set_block("navigation", "language");
	$cWebsite->template->parse("VAR_LANGUAGE", "language");

	$cWebsite->template->set_block("navigation", "navigation_bottom");
	$cWebsite->template->parse("VAR_NAVIGATIONBOTTOM", "navigation_bottom");*/

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_PRODUCTSELECTED" => (isset($_GET['inventoryProduct']) && $aProductList[$i]['ID'] == $_GET['inventoryProduct'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productListBlock", $productListBlock);

	//inventoryDayBlock
	$inventoryDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_GET['inventoryDay']) )
		{
			$sDefaultInventoryDay = $_GET['inventoryDay'];
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
		if ( isset($_GET['inventoryMonth']) )
		{
			$sDefaultInventoryMonth = $_GET['inventoryMonth'];
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
		if ( isset($_GET['inventoryYear']) )
		{
			$sDefaultInventoryYear = $_GET['inventoryYear'];
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
		if ( isset($_GET['inventoryDayEnd']) )
		{
			$sDefaultInventoryDay = $_GET['inventoryDayEnd'];
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
		if ( isset($_GET['inventoryMonthEnd']) )
		{
			$sDefaultInventoryMonth = $_GET['inventoryMonthEnd'];
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
		if ( isset($_GET['inventoryYearEnd']) )
		{
			$sDefaultInventoryYear = $_GET['inventoryYearEnd'];
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

	//prepare the data
	$aContent = array();
	$aContent[] = array("SNStart", "SNEnd", "Quantity", "OutletName", "ClientName", "Date");
	foreach ($inventoryListBlock as $iKey => $aData)
	{
		$aContent[] = array($aData["VAR_SNSTART"], $aData["VAR_SNEND"], $aData["VAR_QUANTITY"], $aData["VAR_OUTLETNAME"], $aData["VAR_CLIENTNAME"], $aData["VAR_DATE"]);
	}

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('reportSoldSN-' . $sProductName . '-' . $sInventoryDate . '-' . $sInventoryDateEnd . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
