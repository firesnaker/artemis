<?php
	/***************************************************************************
	* retail/purchasePrint.php :: Retail Purchase Print Page							*
	****************************************************************************
	* The purchase print for retail															*
	*																									*
	* Version			: 2																		*
	* Author				: Ricky Kurniawan [ FireSnakeR ] 								*
	* Created			: 2010-07-02 															*
	* Last modified	: 2015-03-13															*
	* 																									*
	* 							Copyright (c) 2010-2015 FireSnakeR							*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	//remember, the role value must always be lowercase
	if ( !$gate->is_valid_user('user_ID') )
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/invoiceObject.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cPurchase = new FSR_Invoice;
	$cOutlet = new FSR_Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iCurrentPurchaseID = 0;
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			$iCurrentPurchaseID = $_GET["purchaseID"];
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++//
		$cPurchase->getPurchase($iCurrentPurchaseID);
		$sPurchaseDate = date("d-M-Y", strtotime($cPurchase->getProperty("Date")));
		$sPurchaseNotes = $cPurchase->getProperty("Notes");
		$cOutlet->getOutlet($cPurchase->getProperty("outlet_ID"));
		$sOutletName = $cOutlet->getProperty("Name");

		$param = array(
			"purchase_ID" => "='" . $_GET['purchaseID'] . "'"
		);
		$aPurchaseData = $cPurchase->listPurchaseDetail($param);
	//*** END PAGE PROCESSING ************************************************//
	
	//*** BEGIN PAGE RENDERING ***********************************************//

	$websiteFiles = array(
		"site" => "retail/purchasePrint.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => "Pembelian",

		//page text
		"TEXT_NO" => "No.",
		"TEXT_PRODUCT" => "Barang",
		"TEXT_QUANTITY" => "Jumlah",
		"TEXT_SERIALNUMBER" => "Serial Number",
		"TEXT_DATE" => "Tanggal",
		"TEXT_NOTES" => "Catatan",
		"TEXT_DATEPRINT" => "Tanggal Cetak",
		"TEXT_NODATA" => "Tidak ada data.",

		"VAR_PURCHASEDATE" => $sPurchaseDate,
		"VAR_PURCHASENOTES" => $sPurchaseNotes,
		"VAR_OUTLETNAME" => $sOutletName,
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));
	
	if ($iCurrentPurchaseID == 0 || $aPurchaseData == FALSE)
	{
		$cWebsite->template->set_block("site", "purchaseDetailRow");
		$cWebsite->template->parse("purchaseDetailRow", "");
		$cWebsite->template->set_block("site", "purchaseDetailRow_empty");
		$cWebsite->template->parse("purchaseDetailRow_empty", "purchaseDetailRow_empty");
	}
	else
	{
		$cWebsite->template->set_block("site", "purchaseDetailRow_empty");
		$cWebsite->template->parse("purchaseDetailRow_empty", "");

		//purchaseDetailRow
		$purchaseDetailRow = array();
		for ($i = 0; $i < count($aPurchaseData); $i++)
		{
			$purchaseDetailRow[] = array(
				"VAR_COUNTER" => $i+1,
				"VAR_PRODUCTNAME" => $aPurchaseData[$i]['product_Name'],
				"VAR_QUANTITY" => number_format( $aPurchaseData[$i]['Quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SN" => ($aPurchaseData[$i]['SnStart'] . (($aPurchaseData[$i]['SnEnd'] == "")?"":("-" . $aPurchaseData[$i]['SnEnd']) ) ), 
			);
		}
		$cWebsite->buildBlock("site", "purchaseDetailRow", $purchaseDetailRow);
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING *************************************************//
?>