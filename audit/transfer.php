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
	* audit/transfer.php :: Retail Transfer Page								*
	*********************************************************************
	* The report transfer page for audit											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2014-02-20 										*
	* Last modified	: 2014-06-30										*
	* 																	*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classTransfer.php");
		include_once($libPath . "/classUser.php");
		include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classProduct.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 
		  && ($_SESSION['user_Name'] == "admin" || strtolower($_SESSION['user_Name']) == "audit") )
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
		$sViewType = "all";
		$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cTransfer = new Transfer;
		$cUser = new User($_SESSION['user_ID']);
		$cOutlet = new Outlet;
		$cProduct = new Product;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		$aPostData = array(
			"From_outlet_ID" => 0,
			"To_outlet_ID" => 0,
			"product_category_ID" => 0,
			"product_ID" => 0
		);
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if (isset($_POST['dateFromDay']) && $_POST['dateFromDay'] > 0 
				&& isset($_POST['dateFromMonth']) && $_POST['dateFromMonth'] > 0 
				&& isset($_POST['dateFromYear']) && $_POST['dateFromYear'] > 0 )
			{
				$sDateFrom = $_POST['dateFromYear'] . "-" . $_POST['dateFromMonth'] . "-" . $_POST['dateFromDay'];
			}
			else
			{
				$sDateFrom = date("Y-m-d");
			}

			if (isset($_POST['dateToDay']) && $_POST['dateToDay'] > 0 
				&& isset($_POST['dateToMonth']) && $_POST['dateToMonth'] > 0 
				&& isset($_POST['dateToYear']) && $_POST['dateToYear'] > 0 )
			{
				$sDateTo = $_POST['dateToYear'] . "-" . $_POST['dateToMonth'] . "-" . $_POST['dateToDay'];
			}
			else
			{
				$sDateTo = date("Y-m-d");
			}

			if ( isset($_POST["transferViewSort"]) && $_POST["transferViewSort"] == "Sortir")
			{
				$aPostData = array(
					"From_outlet_ID" => (isset($_POST["transferOutletFrom"]))?$_POST["transferOutletFrom"]:"",
					"To_outlet_ID" => (isset($_POST["transferOutletTo"]))?$_POST["transferOutletTo"]:"",
					"DateFrom" => $sDateFrom,
					"DateTo" => $sDateTo,
					"product_category_ID" => $_POST["transferProductCategory"],
					"product_ID" => $_POST["transferProduct"]
				);
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		$aOutletList = $cOutlet->GetActiveOutletList();
		$aProductList = $cProduct->GetProductList();
		$aProductCategoryList = $cProduct->GetCategoryList();

		$aTransferData = $cTransfer->GetTransferReportListWithDetailByOutletID($aPostData);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "audit/transfer.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		"VAR_OUTLETFROM" => (isset($_POST['transferOutletFrom']))?$_POST['transferOutletFrom']:"",
		"VAR_OUTLETTO" => (isset($_POST['transferOutletTo']))?$_POST['transferOutletTo']:"",
		"VAR_PRODUCTCATEGORY" => (isset($_POST['transferProductCategory']))?$_POST['transferProductCategory']:"",
		"VAR_PRODUCT" => (isset($_POST['transferProduct']))?$_POST['transferProduct']:"",
		"VAR_FROMDAY" => (isset($_POST['dateFromDay']))?$_POST['dateFromDay']:"",
		"VAR_FROMMONTH" => (isset($_POST['dateFromMonth']))?$_POST['dateFromMonth']:"",
		"VAR_FROMYEAR" => (isset($_POST['dateFromYear']))?$_POST['dateFromYear']:"",
		"VAR_TODAY" => (isset($_POST['dateToDay']))?$_POST['dateToDay']:"",
		"VAR_TOMONTH" => (isset($_POST['dateToMonth']))?$_POST['dateToMonth']:"",
		"VAR_TOYEAR" => (isset($_POST['dateToYear']))?$_POST['dateToYear']:"",

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y")
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_audit");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_audit");

	//dateFromDayBlock
	$beginDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['dateFromDay']) )
		{
			$sDefaultBeginDay = $_POST['dateFromDay'];
		}
		else
		{
			$sDefaultBeginDay = date("d");
		}
		$beginDayBlock[] = array(
			"VAR_FROMDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_FROMDAYSELECTED" => ( ($i+1) == $sDefaultBeginDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateFromDayBlock", $beginDayBlock);

	//dateFromMonthBlock
	$beginMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['dateFromMonth']) )
		{
			$sDefaultBeginMonth = $_POST['dateFromMonth'];
		}
		else
		{
			$sDefaultBeginMonth = date("m");
		}
		$beginMonthBlock[] = array(
			"VAR_FROMMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_FROMMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_FROMMONTHSELECTED" => ( ($i+1) == $sDefaultBeginMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateFromMonthBlock", $beginMonthBlock);

	//dateFromYearBlock
	$beginYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['dateFromYear']) )
		{
			$sDefaultBeginYear = $_POST['dateFromYear'];
		}
		else
		{
			$sDefaultBeginYear = date("Y");
		}
		$beginYearBlock[] = array(
			"VAR_FROMYEARVALUE" => $i,
			"VAR_FROMYEARSELECTED" => ( $i == $sDefaultBeginYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateFromYearBlock", $beginYearBlock);

	//dateToDayBlock
	$endDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['dateToDay']) )
		{
			$sDefaultEndDay = $_POST['dateToDay'];
		}
		else
		{
			$sDefaultEndDay = date("d");
		}
		$endDayBlock[] = array(
			"VAR_TODAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_TODAYSELECTED" => ( ($i+1) == $sDefaultEndDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateToDayBlock", $endDayBlock);

	//dateToMonthBlock
	$endMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['dateToMonth']) )
		{
			$sDefaultEndMonth = $_POST['dateToMonth'];
		}
		else
		{
			$sDefaultEndMonth = date("m");
		}
		$endMonthBlock[] = array(
			"VAR_TOMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_TOMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_TOMONTHSELECTED" => ( ($i+1) == $sDefaultEndMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateToMonthBlock", $endMonthBlock);

	//dateToYearBlock
	$endYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['dateToYear']) )
		{
			$sDefaultEndYear = $_POST['dateToYear'];
		}
		else
		{
			$sDefaultEndYear = date("Y");
		}
		$endYearBlock[] = array(
			"VAR_TOYEARVALUE" => $i,
			"VAR_TOYEARSELECTED" => ( $i == $sDefaultEndYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateToYearBlock", $endYearBlock);

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$outletListBlock[] = array(
			"VAR_OUTLETID" => $aOutletList[$i]['ID'],
			"VAR_OUTLETNAME" => $aOutletList[$i]['name'],
			"VAR_OUTLETSELECTED" => (isset($_POST['transferOutletFrom']) && $aOutletList[$i]['ID'] == $_POST['transferOutletFrom'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlockFrom", $outletListBlock);

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$outletListBlock[] = array(
			"VAR_OUTLETID" => $aOutletList[$i]['ID'],
			"VAR_OUTLETNAME" => $aOutletList[$i]['name'],
			"VAR_OUTLETSELECTED" => (isset($_POST['transferOutletTo']) && $aOutletList[$i]['ID'] == $_POST['transferOutletTo'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlockTo", $outletListBlock);

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
			"VAR_PRODUCTSELECTED" => (isset($_POST['transferProduct']) && $aProductList[$i]['ID'] == $_POST['transferProduct'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productListBlock", $productListBlock);

	//productCategoryListBlock
	$productCategoryListBlock = array();
	for ($i = 0; $i < count($aProductCategoryList); $i++)
	{
		$productCategoryListBlock[] = array(
			"VAR_PRODUCTCATEGORYID" => $aProductCategoryList[$i]['ID'],
			"VAR_PRODUCTCATEGORYNAME" => $aProductCategoryList[$i]['Name'],
			"VAR_PRODUCTCATEGORYSELECTED" => (isset($_POST['transferProductCategory']) && $aProductCategoryList[$i]['ID'] == $_POST['transferProductCategory'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "productCategoryListBlock", $productCategoryListBlock);

	$cWebsite->template->set_block("content", "transferDetailList", "transferDetailLists");
	$cWebsite->template->set_block("content", "transferList", "transferLists");
	$cWebsite->template->parse("transferLists", "");

	$iProductGrandTotal = 0;
	//transferList Block
	for ($j = 0; $j < count($aTransferData); $j++)
	{
		if ($_POST['transferProduct'] == 0)
		{
			$bDisplayLine = TRUE;
		}
		else
		{
			$bDisplayLine = FALSE;
		}
		
		$cWebsite->template->parse("transferDetailLists", "");
		//transferDetailList Block
		for ($k = 0; $k < count($aTransferData[$j]["Detail"]); $k++)
		{
			if ( ($bDisplayLine == FALSE) && ($aTransferData[$j]["Detail"][$k]['productID'] == $_POST['transferProduct']) )
			{
				$bDisplayLine = TRUE;
				$bDisplayLineDetail = TRUE;
			}
			else
			{
				$bDisplayLineDetail = FALSE;

				if ($_POST['transferProduct'] == 0)
				{
					$bDisplayLineDetail = TRUE;
				}
			}

			if ($bDisplayLineDetail == TRUE)
			{
				$cWebsite->template->set_var(array(
					"VAR_PRODUCT_NAME" => $aTransferData[$j]["Detail"][$k]['productName'],
					"VAR_QUANTITY" => number_format($aTransferData[$j]["Detail"][$k]['quantity'], _NbOfDigitBehindComma_ ),
				));

				$cWebsite->template->parse("transferDetailLists", "transferDetailList", TRUE);

				$iProductGrandTotal += $aTransferData[$j]["Detail"][$k]['quantity'];
			}
		}

		if ($bDisplayLine == TRUE)
		{
			$cWebsite->template->set_var(array(
				"VAR_COUNT" => $j+1,
				"VAR_TRANSFER_ID" => $aTransferData[$j]['ID'],
				"VAR_DATE" => $aTransferData[$j]['Date'],
				"VAR_NOTES" => $aTransferData[$j]['Notes'],
				"VAR_FROM_OUTLET_NAME" => $aTransferData[$j]['From_outlet_name'],
				"VAR_TO_OUTLET_NAME" => $aTransferData[$j]['To_outlet_name'],
				"VAR_STATUS" => ($aTransferData[$j]['Status'] == 0)?"belum diverifikasi":"sudah diverifikasi"
			));
	
			$cWebsite->template->parse("transferLists", "transferList", TRUE);
		}
	}

	$cWebsite->template->set_var(array(
		"TEXT_GRANDTOTAL" => "Grand Total",
		"VAR_GRANDTOTAL" => number_format($iProductGrandTotal, _NbOfDigitBehindComma_ ),
		"TEXT_NOTES" => "Grand Total berfungsi benar hanya bila ada produk yang dipilih. Tidak bisa All Product.",
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
