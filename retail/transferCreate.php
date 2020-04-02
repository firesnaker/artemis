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
	* retail/transferCreate.php :: Retail Transfer Create Page				*
	****************************************************************************
	* The transfer create page for retail								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-02-27 									*
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
	include_once($libPath . "/classTransfer.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classPDF.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cTransfer = new Transfer;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Create Transfer Out";
	$iTransferID = 0;
	$sPDFDirectory = $sAbsolutePath . "/pdf";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset( $_POST['transferCreateNew'] ) )
			{
				header("Location: transferCreate.php");
				exit();
			}

			if ( isset( $_POST['transferSave'] ) && $_POST['transferSave'] == "Save" )
			{
				$aData = array(
					"ID" => $_POST['transferOutID'],
					"Notes" => $_POST['tranferNotes'],
					"Source" => $_SESSION['outlet_ID'],
					"Destination" => $_POST['transferDestination']
				);

				if ($aData["Source"] > 0 && $aData["Destination"] > 0 )
				{
					if ($aData["ID"] > 0 )
					{
						$cTransfer->Update($aData);
						$iTransferID = $aData["ID"];
					} 
					else
					{
						$iTransferID = $cTransfer->Insert($aData);
					}
				}
				else
				{
					$sErrorMessages .= "Cannot save data. System error. Source Outlet and/or Destination Outlet does not exists!";
				}
			}

			if ( isset( $_POST['transferDetailSave'] ) && $_POST['transferDetailSave'] == "Save" )
			{
				$aDataDetail = array(
					"ID" => $_POST['transferDetailID'],
					"transfer_ID" => $_POST['transferOutID'],
					"product_ID" => $_POST['transferDetailProduct'],
					"quantity" => $_POST['transferDetailQuantity'],
					"sn_start" => $_POST['sn_start'],
					"sn_end" => $_POST['sn_end']
				);

				//check the sn_start is filled
				if ( $_POST['sn_start'] != "" )
				{
					if ($aDataDetail["ID"] > 0 )
					{
						$cTransfer->UpdateDetail($aDataDetail);
					} 
					else
					{
						$cTransfer->InsertDetail($aDataDetail);
					}
				}

				$iTransferID = $aDataDetail["transfer_ID"];
			}

			//this loop is the edit transfer from the transfer ID
			if ( isset( $_POST['transferDetailEditSubmit'] ) && $_POST['transferDetailEditSubmit'] == "Edit" )
			{
				$aTransferEdit = $cTransfer->GetTransferByID($_POST['transferDetailEditID']);
				$iTransferID = $aTransferEdit[0]["ID"];
			}

			if ( isset( $_POST['transferDetailDetailEditSubmit'] ) && $_POST['transferDetailDetailEditSubmit'] == "Edit" )
			{
				$aTransferDetailEdit = $cTransfer->GetTransferDetailByID($_POST['transferDetailDetailEditID']);
				$iTransferID = $aTransferDetailEdit[0]["transfer_ID"];
			}
		}

		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		$aTransferData = $cTransfer->GetTransferByID($iTransferID);

		$aTransferDetailData = $cTransfer->GetTransferDetailByTransferID($iTransferID);

		//only generate pdf file when transferID exists. If not, it will interfere with the redirection on button "buat baru"
		if ($iTransferID > 0 )
		{
			//generate PDF file for download.
			$aOutletDetail = $cOutlet->GetOutletByID($aTransferData[0]["To_outlet_ID"]);
	
			/* PDF Creation */
			$aTransferListForPDF = array();
			for ($i = 0; $i < count($aTransferDetailData); $i++)
			{
				$aTransferListForPDF[] = array(
					"0" => $i+1,
					"1" => $aTransferDetailData[$i]['productName'],
					"2" => number_format($aTransferDetailData[$i]['quantity'], _NbOfDigitBehindComma_ ),
					"3" => ($aTransferDetailData[$i]['SnStart'] . (($aTransferDetailData[$i]['SnEnd'] == "")?"":("-" . $aTransferDetailData[$i]['SnEnd']) ) )
				);
			}
	
			list($year, $month, $day) = explode("-", $aTransferData[0]["Date"]);
			$sTransferDate = date("d-M-Y", mktime(0, 0, 0, $month, $day, $year));
	
			$sFileName = $sPDFDirectory . "/transfer-print-" . date("d-m-Y") . "-" . $iTransferID . ".pdf";
			
			if ( file_exists($sFileName) )
			{
				unlink($sFileName);
			}
	
			$cPDF = new PDF('P', 'mm', 'A5');
	
			// Column headings
			$cPDF->outletName = "Transfer Dari " . strip_tags($_SESSION['outlet_Name']) . " Untuk " . $aOutletDetail[0]["Name"] . "";
			$cPDF->outletAddress = "Note:" . $aTransferData[0]["Notes"];
			$cPDF->reportDate = "Tanggal:" . $sTransferDate;
			$cPDF->printDate = "Tanggal Cetak:" . date("d-m-Y H:i");
			$header = array('No', 'Barang', 'Jumlah', 'Serial Number');
		
			$cPDF->SetFont('Arial','',14);
			$cPDF->AliasNbPages();
			$cPDF->AddPage();
			$cPDF->ImprovedTableTransferCreate($header,$aTransferListForPDF);
			$cPDF->Footer2();
			$cPDF->Output($sFileName, "F");
			/*PDF Creation */
		}
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "retail/transferCreate.htm"
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
		"VAR_ERRORMESSAGES" => $sErrorMessages,
		
		"VAR_TRANSFEROUTID" => (count($aTransferData))?$aTransferData[0]["ID"]:'',
		"VAR_TRANSFEROUTNOTES" => (count($aTransferData))?$aTransferData[0]["Notes"]:'',

		"VAR_TRANSFERDETAILID" => (isset($aTransferDetailEdit))?$aTransferDetailEdit[0]["ID"]:'',
		"VAR_TRANSFERDETAILQUANTITY" => (isset($aTransferDetailEdit))?$aTransferDetailEdit[0]["quantity"]:'',
		"VAR_SNSTART" => (isset($aTransferDetailEdit))?$aTransferDetailEdit[0]["SnStart"]:'',
		"VAR_SNEND" => (isset($aTransferDetailEdit))?$aTransferDetailEdit[0]["SnEnd"]:'',
		
		"VAR_FORM_ACTION" => "retail/transferCreate.php",
		"VAR_FORM_VIEW" => "retail/transferView.php"

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
	

	$aOutlet = $cTransfer->GetTranferGroupListByFromOutletID($_SESSION['outlet_ID']);
	if (count($aOutlet) > 0)
	{
		//transferDestinationList Block
		$transferDestinationList = array();
		for ($i = 0; $i < count($aOutlet); $i++)
		{
			$aOutletDetail = $cOutlet->GetOutletByID($aOutlet[$i]["To_outlet_ID"]);
			if ($aOutlet[$i]['To_outlet_ID'] <> $_SESSION['outlet_ID'])
			{
				$transferDestinationList[] = array(
					"VAR_OUTLET_ID" => $aOutlet[$i]['To_outlet_ID'],
					"VAR_OUTLET_SELECTED" => (isset($aTransferData) && $aTransferData[0]["To_outlet_ID"] == $aOutlet[$i]['To_outlet_ID'])?"selected":"",
					"VAR_OUTLET_NAME" => $aOutletDetail[0]['code'] . "-" . $aOutletDetail[0]["Name"]
				);
			}
		}
		$cWebsite->buildBlock("content", "transferDestinationList", $transferDestinationList);
	}
	else
	{
		$aOutlet = $cTransfer->GetTransferDestination();
		//transferDestinationList Block
		$transferDestinationList = array();
		for ($i = 0; $i < count($aOutlet); $i++)
		{
			if ($aOutlet[$i]['ID'] <> $_SESSION['outlet_ID'])
			{
				$transferDestinationList[] = array(
					"VAR_OUTLET_ID" => $aOutlet[$i]['ID'],
					"VAR_OUTLET_SELECTED" => (isset($aTransferData) && $aTransferData[0]["To_outlet_ID"] == $aOutlet[$i]['ID'])?"selected":"",
					"VAR_OUTLET_NAME" => $aOutlet[$i]['code'] . "-" . $aOutlet[$i]['name']
				);
			}
		}
		$cWebsite->buildBlock("content", "transferDestinationList", $transferDestinationList);
	}

	$aProduct = $cTransfer->GetProductForTransferByOutletID($_SESSION['outlet_ID']);
	//transferProduct Block
	$transferProduct = array();
	for ($j = 0; $j < count($aProduct); $j++)
	{
		$transferProduct[] = array(
			"VAR_PRODUCT_ID" => $aProduct[$j]['ID'],
			"VAR_PRODUCT_SELECTED" => (isset($aTransferDetailEdit) && $aProduct[$j]['ID'] == $aTransferDetailEdit[0]["product_ID"])?"selected":"",
			"VAR_PRODUCT_NAME" => $aProduct[$j]['name'],
		);
	}
	$cWebsite->buildBlock("content", "transferProduct", $transferProduct);

	if ( count($aTransferDetailData) > 0 )
	{
		//transferProductList Block
		$aTransferProductList = array();
		for ($j = 0; $j < count($aTransferDetailData); $j++)
		{
			$aTransferProductList[] = array(
				"VAR_ID" => $aTransferDetailData[$j]['ID'],
				"VAR_PRODUCT" => $aTransferDetailData[$j]['productName'],
				"VAR_QUANTITY" => number_format($aTransferDetailData[$j]['quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SN" => ($aTransferDetailData[$j]['SnStart'] . (($aTransferDetailData[$j]['SnEnd'] == "")?"":("-" . $aTransferDetailData[$j]['SnEnd']) ) )
			);
		}
		$cWebsite->buildBlock("content", "transferProductList", $aTransferProductList);
		$cWebsite->template->set_block("content", "transferProductListEmpty");
		$cWebsite->template->parse("transferProductListEmpty", "");
	}
	else
	{
		$cWebsite->template->set_block("content", "transferProductList");
		$cWebsite->template->parse("transferProductList", "");
	}


	$cWebsite->template->parse("PAGE_CONTENT", "createTransferOut");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
