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
	* master/reportPurchasePrint.php :: Master Index Page					*
	****************************************************************************
	* The full report print page for master								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]	 				*
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
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cPurchase = new Purchase;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Report Purchase Print";
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

		$sSearchProductName = "All Product Categories";		
		if ($_GET['reportProductCategory'] > 0)
		{
			$aSearchProductCategoryData = $cProduct->GetCategoryByID($_GET['reportProductCategory']);		
			$sSearchProductCategoryName = $aSearchProductCategoryData[0]['Name'];
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
		"site" => "master/reportPurchasePrint.htm"
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
		"VAR_SEARCHOUTLETNAME" => $sSearchOutletName,
		"VAR_SEARCHPRODUCTCATEGORYNAME" => $sSearchProductCategoryName,
		"VAR_SEARCHPRODUCTNAME" => $sSearchProductName,
		"VAR_SEARCHBEGINDATE" => date("d-M-Y", mktime(0,0,0,$_GET['beginMonth'], $_GET['beginDay'], $_GET['beginYear'])), 
		"VAR_SEARCHENDDATE" => date("d-M-Y", mktime(0,0,0,$_GET['endMonth'], $_GET['endDay'], $_GET['endYear'])),
		"TEXT_REPORT" => "Report Purchase",
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));

	//inventoryListBlock
	$iGrandQuantity = 0;
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
			"VAR_QUANTITY" => number_format( $aPurchaseList[$i]['Quantity'], _NbOfDigitBehindComma_ ),
			"VAR_NOTES" => $aPurchaseList[$i]['Notes'],
		);

		$iGrandQuantity += $aPurchaseList[$i]['Quantity'];
	}
	$cWebsite->buildBlock("site", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDQUANTITY" => $iGrandQuantity
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
