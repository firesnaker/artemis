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
	* retail/inventory.php :: Retail Inventory Page						*
	****************************************************************************
	* The inventory page for retail									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-07-07									*
	* Last modified	: 2014-08-21									*
	* 															*
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
	include_once($libPath . "/classInventory.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cInventory = new Inventory;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Stock Inventory";
	$iOldestYear = "2010"; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		$aInventoryList = $cInventory->CalculateInventoryByOutletID($_SESSION['outlet_ID']);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "retail/inventory.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_PAGEOUTLETNAME" => $_SESSION['outlet_Name'],

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name']
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

	//inventoryListBlock
	$inventoryListBlock = array();
	for ($i = 0; $i < count($aInventoryList); $i++)
	{
		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_PRODUCTNAME" => $aInventoryList[$i]['ProductName'],
			"VAR_QUANTITY" => number_format( $aInventoryList[$i]['Quantity'], _NbOfDigitBehindComma_ ) . "<span style=\"color:red\">" . (($aInventoryList[$i]['TransferInNotVerified'] > 0)?"+".number_format( $aInventoryList[$i]['TransferInNotVerified'], _NbOfDigitBehindComma_ ):"") . "</span>",

			"VAR_QTY_PURCHASE" => number_format( $aInventoryList[$i]['Purchase'], _NbOfDigitBehindComma_ ),
			"VAR_QTY_TRF_IN" => number_format( $aInventoryList[$i]['TransferIn'], _NbOfDigitBehindComma_ ),
			"VAR_QTY_TRF_IN_VERIFIED" => number_format( $aInventoryList[$i]['TransferInVerified'], _NbOfDigitBehindComma_ ),
			"VAR_QTY_TRF_IN_NOTVERIFIED" => ($aInventoryList[$i]['TransferInNotVerified'] > 0)?number_format( $aInventoryList[$i]['TransferInNotVerified'], _NbOfDigitBehindComma_ ):"",
			"VAR_QTY_SALES" => number_format( $aInventoryList[$i]['Sales'], _NbOfDigitBehindComma_ ),
			"VAR_QTY_TRF_OUT" => number_format( $aInventoryList[$i]['TransferOut'], _NbOfDigitBehindComma_ ),
			"VAR_QTY_TRF_OUT_VERIFIED" => number_format( $aInventoryList[$i]['TransferOutVerified'], _NbOfDigitBehindComma_ ),
			"VAR_QTY_TRF_OUT_NOTVERIFIED" => number_format( $aInventoryList[$i]['TransferOutNotVerified'], _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("content", "inventoryListBlock", $inventoryListBlock);
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//
?>
