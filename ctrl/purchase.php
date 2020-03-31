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
	* ctrl/purchase.php :: Purchase Invoice Controller Page							*
	****************************************************************************
	* The purchase invoice controller page													*
	*																									*
	* Version			: 1																		*
	* Author				: Ricky Kurniawan [ FireSnakeR ]									*
	* Created			: 2015-03-09 															*
	* Last modified	: 2015-03-13															*
	*																									*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/invoiceObject.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cPurchase = new FSR_Invoice;
	$cOutlet = new FSR_Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	session_start();
	$cOutlet->getOutlet($_SESSION['outlet_ID']);

	if ( isset($_POST) && count($_POST) > 0)
	{
		if (isset($_POST['deleteID']))
		{
			//$cUser->deletePurchase($_POST['deleteID']);
		}
		elseif ( isset($_POST["purchaseDetail_ID"]) )
		{
			//can be updated if admin
			//session_start();
			if (strtolower($_SESSION['user_Name']) == "admin")
			{
				$can_update = TRUE;
			}
			else
			{
				if ($_POST['purchaseDetail_purchaseID'] > 0)
				{
					$cPurchaseData = new FSR_Invoice;
					$cPurchaseData->getPurchase($_POST['purchaseDetail_purchaseID']);
					//Save can only be done if current month and current year from retail
					if (date("m", strtotime($cPurchaseData->getProperty("Date"))) == date("m")
						&& date("Y", strtotime($cPurchaseData->getProperty("Date"))) == date("Y")
					)
					{
						$can_update = TRUE;
					}
				}
			}

			if ( $can_update && $cOutlet->getProperty("AllowPurchaseNewAndEdit") == 1)
			{
				if ( isset($_POST['purchaseDetail_ID']) )
				{
					$cPurchase->getPurchaseDetail($_POST['purchaseDetail_ID']);
				}
				$cPurchase->setProperty("purchase_ID", $_POST['purchaseDetail_purchaseID']);
				$cPurchase->setProperty("product_ID", $_POST['productItem']);
				$cPurchase->setProperty("Quantity", $_POST['purchaseDetail_Quantity']);
				$cPurchase->setProperty("Price", $_POST['purchaseDetail_Price']);
				$cPurchase->setProperty("SnStart", $_POST['purchaseDetail_SnStart']);
				$cPurchase->setProperty("SnEnd", $_POST['purchaseDetail_SnEnd']);
	
				if ( $cPurchase->setPurchaseDetail() )
				{
					$result = "Save Success";
				}
				else
				{
					$result = "Save Failed";
				}
			}
			else
			{
				$result = "Save Denied";
			}
		}
		else
		{
			$can_update = FALSE;

			//can be updated if admin
			//session_start();
			if (strtolower($_SESSION['user_Name']) == "admin")
			{
				$can_update = TRUE;
			}
			else
			{
				//Save can only be done if current month and current year from retail
				if (date("m", strtotime($_POST['purchaseDate'])) == date("m")
					&& date("Y", strtotime($_POST['purchaseDate'])) == date("Y")
				)
				{
					$can_update = TRUE;
				}
			}

			if ( $can_update && $cOutlet->getProperty("AllowPurchaseNewAndEdit") == 1)
			{
				if ( isset($_POST['purchaseID']) )
				{
					$cPurchase->getPurchase($_POST['purchaseID']);
				}

				if ( (!isset($_POST['outletID']) || $_POST['outletID'] < 1) 
					&& isset($_SESSION['outlet_ID'])
				)
				{
					$_POST['outletID'] = $_SESSION['outlet_ID'];
				}
				$cPurchase->setProperty("outlet_ID", $_POST['outletID']);
				$cPurchase->setProperty("supplier_id", $_POST['supplierID']);
				$cPurchase->setProperty("Date", date("Y-m-d", strtotime($_POST['purchaseDate'])));
				$cPurchase->setProperty("Notes", $_POST['purchaseNotes']);

				if ($_POST['paymentTypeID'])
				{
					$cPurchase->setProperty("paymentType_ID", $_POST['paymentTypeID']);
				}

				if ( $iID = $cPurchase->setPurchase() )
				{
					//check if this is an update, in which case,
					if (isset($_POST['purchaseID']) && $_POST['purchaseID'] > 0 )
					{
						$iID = $_POST['purchaseID'];
					}
					$result = $iID;
				}
				else
				{
					$result = "Save Failed";
				}
			}
			else
			{
				$result = "Save Denied";
			}
		}
	}
	elseif ( isset($_GET) && count($_GET) > 0)
	{
		if (isset($_GET['id']) ) //only get one data
		{
			$cPurchase->getPurchase($_GET['id']);
			$result = array(
				"ID" => $cPurchase->getProperty("ID"),
				"outletID" => $cPurchase->getProperty("outlet_ID"),
				//"outletAllowPurchaseNewAndEdit" => $cOutlet->getProperty("AllowPurchaseNewAndEdit"),
				"supplierID" => $cPurchase->getProperty("supplier_id"),
				"paymentTypeID" => $cPurchase->getProperty("paymentType_ID"),
				"Date" => date("d-M-Y", strtotime($cPurchase->getProperty("Date"))),
				"Notes" => $cPurchase->getProperty("Notes"),
				"outletName" => $cPurchase->getProperty("outlet_name"),
				"supplierName" => $cPurchase->getProperty("supplier_name"),
				"paymentTypeName" => $cPurchase->getProperty("paymentType_name")
			);
		}
		elseif (isset($_GET['master_id']) ) //only get one data
		{
			$param = array(
				"purchase_ID" => "='" . $_GET['master_id'] . "'"
			);
			$purchaseData = $cPurchase->listPurchaseDetail($param);
			$result = array("data");
			$result['data'] = $purchaseData;
		}
		elseif (isset($_GET['detail_id']) ) //only get one data
		{
			$cPurchase->getPurchaseDetail($_GET['detail_id']);

			//get product name
			include_once($libPath . "/classProduct.php");
			$cProduct = new FSR_Product;
			$cProduct->getProduct($cPurchase->getProperty("product_ID"));

			$result = array(
				"ID" => $cPurchase->getProperty("ID"),
				"purchase_ID" => $cPurchase->getProperty("purchase_ID"),
				"product_ID" => $cPurchase->getProperty("product_ID"),
				"product_Name" => $cProduct->getProperty("Name"),
				"Quantity" => $cPurchase->getProperty("Quantity"),
				"Price" => $cPurchase->getProperty("Price"),
				"SnStart" => $cPurchase->getProperty("SnStart"),
				"SnEnd" => $cPurchase->getProperty("SnEnd")
			);
		}
		else //get everything
		{
			//session_start();

			$param = array(
				"outlet_ID" => "> 0"
			);
			if (isset($_SESSION['outlet_ID']) && $_SESSION['outlet_ID'] > 0)
			{
				$param = array(
					"outlet_ID" => "='" . $_SESSION['outlet_ID'] . "'"
				);
			}
			$purchaseData = $cPurchase->listPurchase($param);
			$result = array("data");

			//inject outlet allow purchase into the result
			//to be removed later on.
			$tmpPurchaseData = array();
			foreach ($purchaseData as $data)
			{
				$cOutletPurchase = new FSR_Outlet;
				$cOutletPurchase->getOutlet($data['outlet_ID']);
				$data["AllowPurchaseNewAndEdit"] = $cOutletPurchase->getProperty("AllowPurchaseNewAndEdit");

				$tmpPurchaseData[] = $data;
			}
			$purchaseData = $tmpPurchaseData;

			$result['data'] = $purchaseData;
		}
	}
	else
	{
		//ok, something is definitely wrong here
		$result = "unknown error";
	}
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//
	echo json_encode($result);
	//*** END PAGE RENDERING *************************************************//
?>
