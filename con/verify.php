<?php
	/***************************************************************************
	* con/verify.php :: Invoice Verification Controller Page							*
	****************************************************************************
	* The invoice verfication controller page												*
	*																									*
	* Version			: 1																		*
	* Author				: Ricky Kurniawan [ FireSnakeR ]									*
	* Created			: 2015-04-10 															*
	* Last modified	: 2015-04-10															*
	*																									*
	* 					Copyright (c) 2015-2015 FireSnakeR									*
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