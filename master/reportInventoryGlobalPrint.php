<?php
	/***************************************************************************
	* master/reportInventoryGlobalPrint.php :: Master Global Inventory Print Page	*
	****************************************************************************
	* The inventory global print page for master									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-07-07 									*
	* Last modified	: 2015-03-25									*
	*															*
	* 			Copyright (c) 2010-2015 FireSnakeR						*
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
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classInventory.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cInventory = new Inventory;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Inventory Print";
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
		"site" => "master/reportInventoryGlobalPrint.htm"
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
		"VAR_OUTLETNAME" => $sOutletName,
		"VAR_SEARCHINVENTORYDATE" => date("d-M-Y", $sPrintDate),
		"TEXT_INVENTORY" => "INVENTORY",
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));
	
	//inventoryListBlock
	$inventoryListBlock = array();
	for ($i = 0; $i < count($aInventoryList); $i++)
	{
		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_PRODUCTNAME" => $aInventoryList[$i]['ProductName'],
			"VAR_QUANTITY" => number_format( $aInventoryList[$i]['Quantity'], _NbOfDigitBehindComma_ ) . "<span style=\"color:red\">" . (($aInventoryList[$i]['TransferInNotVerified'] > 0)?"+".number_format( $aInventoryList[$i]['TransferInNotVerified'], _NbOfDigitBehindComma_ ):"") . "</span> [Rp" . number_format($aInventoryList[$i]['Value'], _NbOfDigitBehindComma_ ) . "]"
		);
	}
	$cWebsite->buildBlock("site", "inventoryListBlock", $inventoryListBlock);
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>