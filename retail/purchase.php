<?php
	/***************************************************************************
	* retail/purchase.php :: Retail Purchase Input Page								*
	****************************************************************************
	* The purchase input page for retail													*
	*																									*
	* Version			: 2																		*
	* Author				: Ricky Kurniawan [ FireSnakeR ]									*
	* Created			: 2010-07-02 															*
	* Last modified	: 2015-04-10															*
	*																									*
	* 					Copyright (c) 2010-2015 FireSnakeR									*
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
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sPageName = "Purchase";
	$aModules = array("purchase");
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	//+++ BEGIN $_POST / $_GET processing ++++++++++++++++++++++++++++++++++++//
	//+++ END $_POST / $_GET processing ++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "purchase.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_ACTIONURL" => 'retail/purchase.php',

		//page text
		"TEXT_ADD" => "Tambah",
		"TEXT_CLOSE" => "Tutup",
		"TEXT_DATE" => "Tanggal",
		"TEXT_DELETE" => "Hapus",
		"TEXT_EDIT" => "Ubah",
		"TEXT_NO" => "No.",
		"TEXT_NOTES" => "Catatan",
		"TEXT_PRINT" => "Cetak",
		"TEXT_PRODUCT" => "Produk",
		"TEXT_PURCHASE" => "Pembelian",
		"TEXT_QUANTITY" => "Jumlah",
		"TEXT_SAVE_CHANGES" => "Simpan",
		"TEXT_SERIALNUMBER" => "No. Seri",
		"TEXT_SERIALNUMBER_START" => "No. Seri Awal",
		"TEXT_SERIALNUMBER_END" => "No. Seri Akhir",
		"TEXT_SUPPLIER" => "Supplier",
		"TEXT_VERIFY" => "Verify",

		"TEXT_LABEL_INVOICENO" => "Nomor"
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

	$cWebsite->template->set_block("content", "price_hidden");
	$cWebsite->template->set_block("content", "price_show");
	$cWebsite->template->set_block("content", "outlet_show");
	$cWebsite->template->set_block("content", "paymentType_show");

	$cWebsite->template->set_block("content", "verifyForm");

	$cWebsite->template->set_block("content", "purchase_button");

	if ($_SESSION['user_Name'] == "admin")
	{
		$cWebsite->template->parse("price_hidden", "");
		$cWebsite->template->parse("price_show", "price_show");
		$cWebsite->template->parse("outlet_show", "outlet_show");
		$cWebsite->template->parse("paymentType_show", "paymentType_show");

		$cWebsite->template->parse("verifyForm", "verifyForm");

		$cWebsite->template->parse("purchase_button", "purchase_button");
	}
	else
	{
		$cWebsite->template->parse("price_hidden", "price_hidden");
		$cWebsite->template->parse("price_show", "");
		$cWebsite->template->parse("outlet_show", "");
		$cWebsite->template->parse("paymentType_show", "");

		$cWebsite->template->parse("verifyForm", "");

		$cWebsite->template->parse("purchase_button", "purchase_button");
	}

	if (count($aModules) > 0)
	{
		$aJSController = array();
		foreach ($aModules as $sModule)
		{
			$aJSController[] = array(
				"VAR_MODULE" => $sModule
			);
		}
		$cWebsite->buildBlock("site", "javascriptModule", $aJSController);
	}
	else
	{
		$cWebsite->template->set_block("site", "javascriptModule");
		$cWebsite->template->parse("javascriptModule", "");
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING *************************************************//
?>