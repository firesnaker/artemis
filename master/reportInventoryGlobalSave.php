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
	* master/reportInventoryGlobalSave.php :: Master Global Inventory Page		*
	****************************************************************************
	* The inventory global page for master													*
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
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cInventory = new Inventory;
	$cOutlet = new Outlet;
	$cExport = new Export;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sInventoryDate = $_GET['inventoryDate'];
			$iInventoryOutlet = $_GET['inventoryOutlet'];
		}
		else
		{
			$sInventoryDate = date("Y-m-d");
		}
		list($iYear, $iMonth, $iDay) = explode("-", $sInventoryDate);
		$sPrintDate = mktime(0,0,0, $iMonth, $iDay, $iYear);
		//+++ END $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		$aOutletList = $cOutlet->GetActiveOutletList();

		$aInventoryList = $cInventory->CalculateInventoryByOutletID($iInventoryOutlet, $sInventoryDate);

		if ($_GET["inventoryOutlet"] > 0)
		{
			$aOutletData = $cOutlet->GetOutletByID($_GET["inventoryOutlet"]);
			$sOutletName = $aOutletData[0]["Name"];
		}
		else
		{
			$sOutletName = "All Outlet";
		}
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "master/reportInventoryGlobal.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	//inventoryListBlock
	$inventoryListBlock = array();
	for ($i = 0; $i < count($aInventoryList); $i++)
	{
		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_PRODUCTNAME" => $aInventoryList[$i]['ProductName'],
			"VAR_QUANTITY" => number_format( $aInventoryList[$i]['Quantity'], _NbOfDigitBehindComma_ ) . "" . (($aInventoryList[$i]['TransferInNotVerified'] > 0)?"+".number_format( $aInventoryList[$i]['TransferInNotVerified'], _NbOfDigitBehindComma_ ):"") . "[Rp" . number_format($aInventoryList[$i]['Value'], _NbOfDigitBehindComma_ ) . "]"
		);
	}

	//prepare the data
	$aContent = array();
	$aContent[] = array("Item", "Quantity");
	foreach ($inventoryListBlock as $iKey => $aData)
	{
		$aContent[] = array($aData["VAR_PRODUCTNAME"], $aData["VAR_QUANTITY"]);
	}

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('inventorySave-' . $sOutletName . '-' . date("d-M-Y", $sPrintDate).'.csv', 'text/plain');

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
