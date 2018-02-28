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
	* master/purchaseEdit.php :: Master Index Page							*
	****************************************************************************
	* The index page for master										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-04-21									*
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
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classPurchase.php");
	include_once($libPath . "/classInventory.php");

	include_once($libPath . "/invoiceObject.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cUser = new User($_SESSION['user_ID']);
	$cProduct = new Product;
	$cPurchase = new Purchase;
	$cInventory = new Inventory;
	$oPurchaseInvoice = new Invoice('purchase');
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iCurrentRecordCounter = 0;
	$iCurrentPurchaseID = 0;
	$bGoToLastCounter = true;
	$sFormElementDisabled = "";
	$iOutletID = 0;
	$sPageName = "Purchase Edit";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_POST['purchaseSave']) && $_POST['purchaseSave'] == 'Save' )
			{
				$aPurchaseInsert = array(
					"ID" => $_POST["purchase_ID"],
					"outletID" => $_POST["outlet_ID"],
					"notes" => $_POST["purchase_notes"]
				);

				if ( $_POST["purchase_ID"] == 0 )
				{
					$iPurchaseID = $cPurchase->Insert($aPurchaseInsert);
				}
				else
				{
					$iPurchaseID = $cPurchase->Update($aPurchaseInsert);
				}

			}

			if (isset($_POST['purchaseDetailEdit']) && $_POST['purchaseDetail_ID'] > 0)
			{
				$aPurchaseEdit = $cPurchase->GetPurchaseDetailByID($_POST['purchaseDetail_ID']);
			}

			if ( isset($_POST['purchaseDetail_Save']) 
				&& $_POST["purchase_ID"] > 0 
				&& $_POST["purchaseDetail_ID"] >= 0  
				&& ( $_POST["quantity"] >= 0 ) )
			{
				//we check inside that sn_start is filled
				if ($_POST["sn_start"] <> "")
				{
					$aPurchaseDetailInsert = array(
						"purchase_ID" => $_POST["purchase_ID"],
						"purchaseDetail_ID" => $_POST["purchaseDetail_ID"],
						"product_ID" => isset($_POST["product"])?$_POST["product"]:0,
						"old_quantity" => $_POST["quantity"],
						"price" => 0,
						"quantity" => $_POST["quantity"],
						"sn_start" => $_POST["sn_start"],
						"sn_end" => $_POST["sn_end"]
					);

					if ( $_POST["purchaseDetail_ID"] == 0 )
					{
							$iPurchaseDetailID = $cPurchase->InsertDetail($aPurchaseDetailInsert);
					}
					else
					{
							$iPurchaseDetailID = $cPurchase->UpdateDetailAdmin($aPurchaseDetailInsert);
					}
				}
				else
				{
					echo "sn empty";
				}
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		//get product list visible on website
		$aSearchByFieldArray = array(
			
		);
		$aSortByArray=array(
			'product.Name' => 'asc'
		);
		$aProductList = $cProduct->GetProductList($aSearchByFieldArray, $aSortByArray);

		$aPurchaseData = $oPurchaseInvoice->Load($_POST['editPurchaseID']);

	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/purchaseEdit.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_USERLOGGEDIN" => ucfirst($_SESSION['user_Name']),
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_ACTIONURL" => 'master/purchaseEdit.php',

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_PURCHASEDATE" => (isset($aPurchaseData))?$aPurchaseData["Date"]:date("Y-m-d"),
		"VAR_PURCHASEDATEHUMAN" => (isset($aPurchaseData))?date( "d-M-Y", strtotime( $aPurchaseData["Date"] ) ):date("d-M-Y"),
		"VAR_PURCHASENOTES" => isset($aPurchaseData)?$aPurchaseData['Notes']:'',
		"VAR_OUTLETNAME" => isset($aPurchaseData)?$aPurchaseData['outlet_Name']:'',
		"VAR_OUTLETID" => isset($aPurchaseData)?$aPurchaseData['outlet_ID']:'',
		"VAR_PURCHASEID" => isset($aPurchaseData)?$aPurchaseData['ID']:'',

		"VAR_EDIT_PURCHASEDETAILID" => (isset($aPurchaseEdit))?$aPurchaseEdit[0]['ID']:'',
		"VAR_EDIT_QUANTITY" => (isset($aPurchaseEdit))?$aPurchaseEdit[0]['Quantity']:'',
		"VAR_EDIT_SNSTART" => (isset($aPurchaseEdit))?$aPurchaseEdit[0]['SnStart']:'',
		"VAR_EDIT_SNEND" => (isset($aPurchaseEdit))?$aPurchaseEdit[0]['SnEnd']:''
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	//productListBlock
	$productListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$productListBlock[] = array(
			"VAR_PRODUCTID" => $aProductList[$i]['ID'],
			"VAR_EDIT_PRODUCTSELECTED" => (isset($aPurchaseEdit) && $aProductList[$i]['ID'] == $aPurchaseEdit[0]['product_ID'])?'selected':'',
			"VAR_PRODUCTNAME" => $aProductList[$i]['name'],
		);
	}
	$cWebsite->buildBlock("content", "productListBlock", $productListBlock);

	
	if (isset($aPurchaseData) && count($aPurchaseData['Detail']) > 0)
	{
		//purchaseDetailRow
		$purchaseDetailRow = array();
		for ($i = 0; $i < count($aPurchaseData['Detail']); $i++)
		{
			$purchaseDetailRow[] = array(
				"VAR_COUNTER" => $i+1,
				"VAR_PURCHASEDETAILID" => $aPurchaseData['Detail'][$i]['ID'],
				"VAR_PRODUCTID" => $aPurchaseData['Detail'][$i]['product_ID'],
				"VAR_PRODUCTNAME" => $aPurchaseData['Detail'][$i]['product_Name'],
				"VAR_QUANTITY" => number_format( $aPurchaseData['Detail'][$i]['Quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SNSTART" => $aPurchaseData['Detail'][$i]['SnStart'],
				"VAR_SNEND" => $aPurchaseData['Detail'][$i]['SnEnd']
			);
		}
		$cWebsite->buildBlock("content", "purchaseDetailRow", $purchaseDetailRow);
	}
	else
	{
		$cWebsite->template->set_block("content", "purchaseDetailRow");
		$cWebsite->template->parse("purchaseDetailRow", "");
	}
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
