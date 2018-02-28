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
	* mkios/inventory.php :: MKios Inventory Page								*
	*********************************************************************
	* The inventory page for mkios											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-10-15 										*
	* Last modified	: 2013-10-15										*
	* 																	*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classUser.php");
		include_once($libPath . "/classMKios.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 )
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
		$iOldestYear = "2010"; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cMKios = new MKios;
		//$cUser = new User($_SESSION['user_ID']);
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		$aSearchParam = array(
			"Date" => ' <= "' . date('Y-m-d') . '"' 
		);
		$aInventoryList = $cMKios->GetInventory($aSearchParam);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/inventory.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");

	//inventoryListBlock
	$aProductList = array("S005", "S010", "S020", "S025", "S050", "S100");
	$inventoryListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_PRODUCTNAME" => $aProductList[$i],
			"VAR_QUANTITY" => number_format( $aInventoryList[ $aProductList[$i] . '_Stock'], _NbOfDigitBehindComma_ ),
			//"VAR_QUANTITY" => number_format( $aInventoryList[ $aProductList[$i] . '_Purchase'], _NbOfDigitBehindComma_ ) . ' - ' . number_format( $aInventoryList[ $aProductList[$i] . '_Sales'], _NbOfDigitBehindComma_ ). ' = ' . number_format( $aInventoryList[ $aProductList[$i] . '_Stock'], _NbOfDigitBehindComma_ ),

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
