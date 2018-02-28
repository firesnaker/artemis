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
	* master/reportPurchaseSave.php :: Master Report Purchase Save Page		*
	****************************************************************************
	* The report purchase save page for master							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-11-15 									*
	* Last modified	: 2014-08-01									*
	*															*
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
	include_once($libPath . "/classPurchase.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cPurchase = new Purchase;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cExport = new Export;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_GET is always set, so we check by # of element
		{
			$sBeginDate = $_GET['beginYear'] . "-" . $_GET['beginMonth'] . "-" . $_GET['beginDay'];
			$sEndDate = $_GET['endYear'] . "-" . $_GET['endMonth'] . "-" . $_GET['endDay'];
		}
		else
		{
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}

		//+++ END $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"outlet_ID" => ($_GET['reportOutlet'])?$_GET['reportOutlet']:"",
			"product_ID" => ($_GET['reportProduct'])?$_GET['reportProduct']:"",
			"productCategory_ID" => (isset($_GET['reportProductCategory']) && $_GET['reportProductCategory'])?$_GET['reportProductCategory']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aPurchaseList = $cPurchase->GetPurchaseReport($aSearchByFieldArray);
		$aOutletList = $cOutlet->GetActiveOutletList();
		$aProductList = $cProduct->GetProductList();

		$sSearchOutletName = "All Outlets";		
		if ($_GET['reportOutlet'] > 0)
		{
			$aSearchOutletData = $cOutlet->GetOutletByID($_GET['reportOutlet']);		
			$sSearchOutletName = $aSearchOutletData[0]['Name'];
		}

		$sSearchProductName = "All Products";		
		if ($_GET['reportProduct'] > 0)
		{
			$aSearchProductData = $cProduct->GetProductByID($_GET['reportProduct']);		
			$sSearchProductName = $aSearchProductData[0]['Name'];
		}

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "master/reportPurchase.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	//inventoryListBlock
	$reportListBlock = array();
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
			"VAR_QUANTITY" => number_format( $aPurchaseList[$i]['Quantity'], _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ),
			"VAR_QUANTITY_VIRGIN" => $aPurchaseList[$i]['Quantity'],
			"VAR_NOTES" => $aPurchaseList[$i]['Notes'],
		);
	}
	$cWebsite->buildBlock("site", "reportListBlock", $reportListBlock);

	//prepare the data
	$aContent = array();
	$aContent[] = array("Date", "Outlet", "Item", "Quantity", "Notes");
	foreach ($reportListBlock as $iKey => $aData)
	{
		$aContent[] = array($aData["VAR_PURCHASEDATE"], $aData["VAR_OUTLETNAME"], $aData["VAR_PRODUCTNAME"], $aData["VAR_QUANTITY_VIRGIN"], $aData["VAR_NOTES"]);
	}

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('reportPurchaseSave-' . $sSearchOutletName . '-' . $sBeginDate . '-' . $sEndDate . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
