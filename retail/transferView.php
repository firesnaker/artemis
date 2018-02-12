<?php
	/***************************************************************************
	* retail/transferView.php :: Retail Transfer View Page					*
	****************************************************************************
	* The transfer view page for retail								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-02-27 									*
	* Last modified	: 2014-08-21									*
	* 															*
	* 			Copyright (c) 2010-2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ************************************************//
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
	include_once($libPath . "/classTransfer.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cTransfer = new Transfer;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Transfer View";
	$sViewType = "all";
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		$aPostData = array(
			"outlet_ID" => $_SESSION['outlet_ID'],
			"view_type" => "all",
			"dateBegin" => date("Y-m-d"),
			"dateEnd" => date("Y-m-d")
		);
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_POST["transferVerifySubmit"]) && $_POST["transferVerifyID"] > 0 )
			{
				$cTransfer->VerifyTransferByID($_POST["transferVerifyID"]);
			} 

			if ( isset($_POST["transferViewSort"]) && $_POST["transferViewSort"] == "Sortir")
			{
				$aPostData = array(
					"outlet_ID" => $_SESSION['outlet_ID'],
					"view_type" => $_POST["transferViewType"],
					"dateBegin" => $_POST["transferYearBegin"] . "-" . $_POST["transferMonthBegin"] . "-" . $_POST["transferDayBegin"],
					"dateEnd" => $_POST["transferYearEnd"] . "-" . $_POST["transferMonthEnd"] . "-" . $_POST["transferDayEnd"] 
				);
				$sViewType = $_POST["transferViewType"];
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		$aTransferData = $cTransfer->GetTransferListWithDetailByOutletID($aPostData);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "retail/transferView.htm"
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
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		
		"VAR_TYPEVALUE" => $sViewType,
		"VAR_FORM_ACTION" => "retail/transferView.php",
		"VAR_FORM_CREATE" => "retail/transferCreate.php",
		
		"VAR_TRANSFERVIEWTYPE" => $aPostData['view_type'],
		"VAR_TRANSFERYEARBEGIN" => date("Y", strtotime($aPostData['dateBegin'])),
		"VAR_TRANSFERMONTHBEGIN" => date("m", strtotime($aPostData['dateBegin'])),
		"VAR_TRANSFERDAYBEGIN" => date("d", strtotime($aPostData['dateBegin'])),
		"VAR_TRANSFERYEAREND" => date("Y", strtotime($aPostData['dateEnd'])),
		"VAR_TRANSFERMONTHEND" => date("m", strtotime($aPostData['dateEnd'])),
		"VAR_TRANSFERDAYEND" => date("d", strtotime($aPostData['dateEnd']))
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

	//dateDayBeginBlock
	$dateDayBeginBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['transferDayBegin']) )
		{
			$sDefaultDay = $_POST['transferDayBegin'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$dateDayBeginBlock[] = array(
			"VAR_DAYBEGINVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYBEGINSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateDayBeginBlock", $dateDayBeginBlock);

	//dateMonthBeginBlock
	$dateMonthBeginBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['transferMonthBegin']) )
		{
			$sDefaultMonth = $_POST['transferMonthBegin'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$dateMonthBeginBlock[] = array(
			"VAR_MONTHBEGINVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHBEGINTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHBEGINSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateMonthBeginBlock", $dateMonthBeginBlock);

	//dateYearBeginBlock
	$dateYearBeginBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['transferYearBegin']) )
		{
			$sDefaultYear = $_POST['transferYearBegin'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$dateYearBeginBlock[] = array(
			"VAR_YEARBEGINVALUE" => $i,
			"VAR_YEARBEGINSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateYearBeginBlock", $dateYearBeginBlock);

	//dateDayEndBlock
	$dateDayEndBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['transferDayEnd']) )
		{
			$sDefaultDay = $_POST['transferDayEnd'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$dateDayEndBlock[] = array(
			"VAR_DAYENDVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYENDSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateDayEndBlock", $dateDayEndBlock);

	//dateMonthEndBlock
	$dateMonthEndBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['transferMonthEnd']) )
		{
			$sDefaultMonth = $_POST['transferMonthEnd'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$dateMonthEndBlock[] = array(
			"VAR_MONTHENDVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHENDTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHENDSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateMonthEndBlock", $dateMonthEndBlock);

	//dateYearEndBlock
	$dateYearEndBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['transferYearEnd']) )
		{
			$sDefaultYear = $_POST['transferYearEnd'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$dateYearEndBlock[] = array(
			"VAR_YEARENDVALUE" => $i,
			"VAR_YEARENDSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateYearEndBlock", $dateYearEndBlock);


	$cWebsite->template->set_block("content", "transferDetailList", "transferDetailLists");
	$cWebsite->template->set_block("content", "transferList", "transferLists");
	$cWebsite->template->parse("transferLists", "");

	//transferList Block
	for ($j = 0; $j < count($aTransferData); $j++)
	{
		$cWebsite->template->parse("transferDetailLists", "");
		//transferDetailList Block
		for ($k = 0; $k < count($aTransferData[$j]["Detail"]); $k++)
		{
			$cWebsite->template->set_var(array(
				"VAR_PRODUCT_NAME" => $aTransferData[$j]["Detail"][$k]['productName'],
				"VAR_QUANTITY" => number_format($aTransferData[$j]["Detail"][$k]['quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SN" => ($aTransferData[$j]["Detail"][$k]['SnStart'] . (($aTransferData[$j]["Detail"][$k]['SnEnd'] == "")?"":("-" . $aTransferData[$j]["Detail"][$k]['SnEnd']) ) ),
			));

			$cWebsite->template->parse("transferDetailLists", "transferDetailList", TRUE);
		}

		list($year, $month, $day) = explode("-", $aTransferData[$j]['Date']);

		$cWebsite->template->set_var(array(
			"VAR_COUNT" => $j+1,
			"VAR_TRANSFER_ID" => $aTransferData[$j]['ID'],
			"VAR_DATE" => date("d-M-Y", mktime(0,0,0, $month, $day, $year)),
			"VAR_NOTES" => $aTransferData[$j]['Notes'],
			"VAR_FROM_OR_TO" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'])?"Keluar Ke":"Masuk Dari",
			"VAR_CSS_BOLD" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'])?"":"class='bold'",
			"VAR_OUTLET_NAME" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'])?$aTransferData[$j]['To_outlet_name']:$aTransferData[$j]['From_outlet_name'],
			"VAR_STATUS" => ($aTransferData[$j]['Status'] == 0)?"belum diverifikasi":"sudah diverifikasi",
			"VAR_DISABLE_EDIT" => ($aTransferData[$j]['To_outlet_ID'] == $_SESSION['outlet_ID'] ||$aTransferData[$j]['Status'] == 1)?"disabled='1'":"",
			"VAR_DISABLE_VERIFY" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'] || $aTransferData[$j]['Status'] == 1)?"disabled='1'":""
		));

		$cWebsite->template->parse("transferLists", "transferList", TRUE);
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>