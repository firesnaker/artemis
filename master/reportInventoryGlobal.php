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
	* master/reportInventoryGlobal.php :: Master Global Inventory Page				*
	****************************************************************************
	* The global inventory page for master									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-07-07 									*
	* Last modified	: 2015-03-25									*
	*															*
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
	include_once($libPath . "/classInventory.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classReport.php");
	include_once($libPath . "/classProduct.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cInventory = new Inventory;
	$cOutlet = new Outlet;
	$cReport = new Report;
	$cProduct = new Product;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iInventoryOutlet = 0;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	$sPageName = "Inventory";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sInventoryDate = date("Y-m-d", strtotime($_POST['inventoryDate']));
			$iInventoryOutlet = $_POST['inventoryOutlet'];
		}
		else
		{
			$sInventoryDate = date("Y-m-d");
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aOutletList = $cOutlet->GetActiveOutletList();

		$aInventoryList = $cInventory->CalculateInventoryByOutletID($iInventoryOutlet, $sInventoryDate);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/reportInventoryGlobal.htm"
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
		"VAR_INVENTORYOUTLET" => (isset($_POST['inventoryOutlet']))?$_POST['inventoryOutlet']:"0",
		"VAR_INVENTORYDATE" => $sInventoryDate,
		"VAR_INVENTORYDATEVALUE" => date("d-M-Y", strtotime($sInventoryDate)),
		"TEXT_INVENTORY" => "INVENTORY"
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
			"VAR_OUTLETSELECTED" => (isset($_POST['inventoryOutlet']) && $aOutletList[$i]['ID'] == $_POST['inventoryOutlet'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);

	//inventoryListBlock
	$iTotal = 0;
	$iTotalValue = 0;
	$inventoryListBlock = array();
	for ($i = 0; $i < count($aInventoryList); $i++)
	{
		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_PRODUCTNAME" => $aInventoryList[$i]['ProductName'],
			"VAR_QUANTITY" => number_format( $aInventoryList[$i]['Quantity'], _NbOfDigitBehindComma_ ) . "<span style=\"color:red\">" . (($aInventoryList[$i]['TransferInNotVerified'] > 0)?"+".number_format( $aInventoryList[$i]['TransferInNotVerified'], _NbOfDigitBehindComma_ ):"") . "</span> [Rp" . number_format($aInventoryList[$i]['Value'], _NbOfDigitBehindComma_ ) . "]"
		);
		$iTotal += $aInventoryList[$i]['Quantity'];
		$iTotalValue += $aInventoryList[$i]['Value'];
	}
	$cWebsite->buildBlock("content", "inventoryListBlock", $inventoryListBlock);

	$cWebsite->template->set_var(array(
		"VAR_QUANTITYTOTAL" => number_format( $iTotal, _NbOfDigitBehindComma_ ) .  "[Rp" . number_format($iTotalValue, _NbOfDigitBehindComma_ ) . "]"
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
