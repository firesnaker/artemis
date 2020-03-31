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
	* master/transferEdit.php :: Retail Transfer Page						*
	****************************************************************************
	* The transfer page for retail									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-04-13									*
	* Last modified	: 2014-08-01									*
	*															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') && !$gate->is_valid_role('user_ID', 'user_Name', 'master')) //remember, the role value must always be lowercase
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
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cTransfer = new Transfer;
	$cUser = new User($_SESSION['user_ID']);
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//	
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iTransferID = 0;
	$sPageName = "Transfer Edit";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset( $_POST['transferSave'] ) && $_POST['transferSave'] == "Save" )
			{
				$aData = array(
					"ID" => $_POST['transferOutID'],
					"Notes" => $_POST['tranferNotes'],
					"Source" => $_POST['transferSource'],
					"Destination" => $_POST['transferDestination']
				);

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

			if ( isset( $_POST['transferDetailSave'] ) && $_POST['transferDetailSave'] == "Save" )
			{
				$aDataDetail = array(
					"ID" => $_POST['transferDetailID'],
					"transfer_ID" => $_POST['transferOutID'],
					"product_ID" => $_POST['transferDetailProduct'],
					"quantity" => $_POST['transferDetailQuantity'],
					"sn_start" => $_POST['transferDetailSnStart'],
					"sn_end" => $_POST['transferDetailSnEnd'],
				);

				if ($aDataDetail["ID"] > 0 )
				{
					$cTransfer->UpdateDetail($aDataDetail);
				} 
				else
				{
					$cTransfer->InsertDetail($aDataDetail);
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

			if (isset ( $_POST["transferEditAdmin"] ) && $_POST["transferEditID"] > 0 )
			{
				$iTransferID = $_POST["transferEditID"];
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		$aTranferData = $cTransfer->GetTransferByID($iTransferID);
		$aTransferDetailData = $cTransfer->GetTransferDetailByTransferID($iTransferID);

		$aOutletSource = $cTransfer->GetTransferDestination();
		for ($i = 0; $i < count($aOutletSource); $i++)
		{
			if ($aTranferData[0]["From_outlet_ID"] == $aOutletSource[$i]["ID"])
			{
				$iOutletSourceID = $aOutletSource[$i]["ID"];
				$sOutletSourceName = $aOutletSource[$i]["name"];
			}
		}
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/transferEdit.htm"
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
		"VAR_OUTLETNAME" => $sOutletSourceName,
		"VAR_TRANSFERSOURCE_OUTLETID" => $aTranferData[0]["From_outlet_ID"],
		
		"VAR_TRANSFEROUTID" => $aTranferData[0]["ID"],
		"VAR_TRANSFEROUTNOTES" => $aTranferData[0]["Notes"],

		"VAR_TRANSFERDETAILID" => $aTransferDetailEdit[0]["ID"],
		"VAR_TRANSFERDETAILQUANTITY" => $aTransferDetailEdit[0]["quantity"],
		"VAR_TRANSFERDETAILSNSTART" => $aTransferDetailEdit[0]["SnStart"],
		"VAR_TRANSFERDETAILSNEND" => $aTransferDetailEdit[0]["SnEnd"]

	));

	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	$aOutlet = $cTransfer->GetTranferGroupListByFromOutletID($iOutletSourceID);
	if (count($aOutlet) > 0)
	{
		//transferDestinationList Block
		$transferDestinationList = array();
		for ($i = 0; $i < count($aOutlet); $i++)
		{
			$aOutletDetail = $cOutlet->GetOutletByID($aOutlet[$i]["To_outlet_ID"]);
			if ($aOutlet[$i]['To_outlet_ID'] <> $iOutletSourceID)
			{
				$transferDestinationList[] = array(
					"VAR_OUTLET_ID" => $aOutlet[$i]['To_outlet_ID'],
					"VAR_OUTLET_SELECTED" => ($aTranferData[0]["To_outlet_ID"] == $aOutlet[$i]['To_outlet_ID'])?"selected":"",
					"VAR_OUTLET_NAME" => $aOutletDetail[0]["Name"]
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
					"VAR_OUTLET_SELECTED" => ($aTranferData[0]["To_outlet_ID"] == $aOutlet[$i]['ID'])?"selected":"",
					"VAR_OUTLET_NAME" => $aOutlet[$i]['name']
				);
			}
		}
		$cWebsite->buildBlock("content", "transferDestinationList", $transferDestinationList);
	}

	$aProduct = $cTransfer->GetProductForTransfer();
	//transferProduct Block
	$transferProduct = array();
	for ($j = 0; $j < count($aProduct); $j++)
	{
		$transferProduct[] = array(
			"VAR_PRODUCT_ID" => $aProduct[$j]['ID'],
			"VAR_PRODUCT_SELECTED" => ($aProduct[$j]['ID'] == $aTransferDetailEdit[0]["product_ID"])?"selected":"",
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
