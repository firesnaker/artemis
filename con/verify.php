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
	* con/verify.php :: Invoice Verification Controller Page							*
	****************************************************************************
	* The invoice verfication controller page												*
	*																									*
	* Version			: 1																		*
	* Author				: Ricky Kurniawan [ FireSnakeR ]									*
	* Created			: 2015-04-10 															*
	* Last modified	: 2015-04-10															*
	*																									*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/invoiceVerifyObject.php");
	include_once($libPath . "/invoiceObject.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cVerify = new FSR_Verify;
	$cPurchase = new FSR_Invoice;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
		session_start();

		if ( isset($_POST["verifyPurchaseID"]) && $_POST["verifyPurchaseID"] > 0
			&& ($_SESSION['user_Name'] == "admin" || $_SESSION['user_Name'] == "FINANCE")
		)
		{
			if (!isset($_POST['verifyNotes']))
			{
				$_POST['verifyNotes'] = "";
			}

			$cVerify->setProperty("invoice_type", 1);
			$cVerify->setProperty("invoice_id", $_POST['verifyPurchaseID']);
			$cVerify->setProperty("user_id", $_SESSION['user_ID']);
			$cVerify->setProperty("date", date("Y-m-d"));
			$cVerify->setProperty("notes", $_POST['verifyNotes']);

			if ( $iID = $cVerify->setVerify() )
			{
				$cPurchase->getPurchase($_POST['verifyPurchaseID']);

				$cPurchase->setProperty("verified", 1);

				$cPurchase->setPurchase();

				$result = "Save Success";
			}
			else
			{
				$result = "Save Failed";
			}
		}
	}
	elseif ( isset($_GET) && count($_GET) > 0)
	{
		if (isset($_GET['purchase_id']) ) //only get one data
		{
			$param = array(
				"invoice_id" => " ='" . $_GET['purchase_id'] . "'"
			);
			$verify_list = $cVerify->listVerify($param);

			if ($verify_list == FALSE)
			{
				$result = "Load Failed";
			}
			else
			{
				$cVerify->getVerify($verify_list[0]['id']);

				$result = array(
					"user_id" => $cVerify->getProperty("user_id"),
					"date" => date("d-M-Y", strtotime($cVerify->getProperty("date"))),
					"notes" => $cVerify->getProperty("notes")
				);
			}
		}
		else //get everything
		{
			
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
