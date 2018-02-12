<?php
	/***************************************************************************
	* master/reportTransferSave.php :: Master Transfer Save Page				*
	****************************************************************************
	* The transfer page for retail									*
	*															*
	* Version			: 1											*
	* Author			: FireSnakeR 									*
	* Created			: 2013-06-05 									*
	* Last modified	: 2014-08-01									*
	* 															*
	* 				Copyright (c) 2010-2014 FireSnakeR					*
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
	include_once($libPath . "/classTransfer.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cTransfer = new Transfer;
	$cUser = new User($_SESSION['user_ID']);
	$cOutlet = new Outlet;
	$cProduct = new Product;
	$cExport = new Export;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sViewType = "all";
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_GET / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		$aPostData = array(
			"From_outlet_ID" => 0,
			"To_outlet_ID" => 0,
			"product_category_ID" => 0,
			"product_ID" => 0
		);
		if ( count($_GET) > 0 ) //$_GET is always set, so we check by # of element
		{
			if (isset($_GET['dateFromDay']) && $_GET['dateFromDay'] > 0 
				&& isset($_GET['dateFromMonth']) && $_GET['dateFromMonth'] > 0 
				&& isset($_GET['dateFromYear']) && $_GET['dateFromYear'] > 0 )
			{
				$sDateFrom = $_GET['dateFromYear'] . "-" . $_GET['dateFromMonth'] . "-" . $_GET['dateFromDay'];
			}
			else
			{
				$sDateFrom = date("Y-m-d");
			}

			if (isset($_GET['dateToDay']) && $_GET['dateToDay'] > 0 
				&& isset($_GET['dateToMonth']) && $_GET['dateToMonth'] > 0 
				&& isset($_GET['dateToYear']) && $_GET['dateToYear'] > 0 )
			{
				$sDateTo = $_GET['dateToYear'] . "-" . $_GET['dateToMonth'] . "-" . $_GET['dateToDay'];
			}
			else
			{
				$sDateTo = date("Y-m-d");
			}

			$aPostData = array(
				"From_outlet_ID" => $_GET["transferOutletFrom"],
				"To_outlet_ID" => $_GET["transferOutletTo"],
				"DateFrom" => $sDateFrom,
				"DateTo" => $sDateTo,
				"product_category_ID" => $_GET["transferProductCategory"],
				"product_ID" => $_GET["transferProduct"]
			);
		}
		//+++ END $_GET / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		$aOutletList = $cOutlet->GetActiveOutletList();
		$aProductList = $cProduct->GetProductList();

		$aTransferData = $cTransfer->GetTransferReportListWithDetailByOutletID($aPostData);

		$sOutletFrom = "All";
		$sOutletTo = "All";
		for ($i = 0; $i < count($aOutletList); $i++)
		{
			if ($aOutletList[$i]["ID"] == $_GET["transferOutletFrom"])
			{
				$sOutletFrom = $aOutletList[$i]["name"];
				
			}
			if ($aOutletList[$i]["ID"] == $_GET["transferOutletTo"])
			{
				$sOutletTo = $aOutletList[$i]["name"];
				
			}
		}

	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "master/reportTransfer.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$iProductGrandTotal = 0;
	//transferList Block
	for ($j = 0; $j < count($aTransferData); $j++)
	{
		if ($_GET['transferProduct'] == 0)
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
			if ( ($bDisplayLine == FALSE) && ($aTransferData[$j]["Detail"][$k]['productID'] == $_GET['transferProduct']) )
			{
				$bDisplayLine = TRUE;
			}

			if ($bDisplayLine == TRUE)
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

	//prepare the data
	$aContent = array();
	$aContent[] = array("Date", "Notes", "Transfer Dari", "Transfer Ke", "Status", "Barang dan Jumlah");

	for ($i = 0; $i < count($aTransferData); $i++)
	{
		if ($_GET['transferProduct'] == 0)
		{
			$bDisplayLine = TRUE;
		}
		else
		{
			$bDisplayLine = FALSE;
		}

		$sProductAndQuantity = "";
		for ($k = 0; $k < count($aTransferData[$i]["Detail"]); $k++)
		{
			if ( ($bDisplayLine == FALSE) && ($aTransferData[$i]["Detail"][$k]['productID'] == $_GET['transferProduct']) )
			{
				$bDisplayLine = TRUE;
				$bDisplayLineDetail = TRUE;
			}
			else
			{
				$bDisplayLineDetail = FALSE;

				if ($_GET['transferProduct'] == 0)
				{
					$bDisplayLineDetail = TRUE;
				}
			}

			if ($bDisplayLineDetail == TRUE)
			{
				$sProductAndQuantity .= '"' . $aTransferData[$i]["Detail"][$k]['productName'] . '";"' . number_format($aTransferData[$i]["Detail"][$k]['quantity'], _NbOfDigitBehindComma_ ) . '";';
				$sProductAndQuantityVirgin .= '"' . $aTransferData[$i]["Detail"][$k]['productName'] . '";"' . number_format($aTransferData[$i]["Detail"][$k]['quantity'], 0 ) . '";';
			}
		}

		if ($bDisplayLine == TRUE)
		{
			//remove the extra ";" at the end of $sProductAndQuantity
			if ($sProductAndQuantity != "")
			{
				$sProductAndQuantity = substr($sProductAndQuantity, 0, strlen($sProductAndQuantity)-1);
			}

			$aContent[] = array($aTransferData[$i]["Date"], $aTransferData[$i]["Notes"], $aTransferData[$i]["From_outlet_name"], $aTransferData[$i]["To_outlet_name"], (($aTransferData[$i]["Status"] == 0)?"belum diverifikasi":"sudah diverifikasi"), $sProductAndQuantityVirgin);
		}
	}

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('reportTransferSave-from-' . $sOutletFrom . '-to-' . $sOutletTo . '-' . $sDateFrom . '-' . $sDateTo . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>