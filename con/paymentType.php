<?php
	/************************************************************************
	* ctrl/paymentType.php :: PaymentType Controller Page							*
	*************************************************************************
	* The paymentType controller page													*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2015-03-27 														*
	* Last modified	: 2015-03-27														*
	*																								*
	* 					Copyright (c) 2014-2015 FireSnakeR								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classPaymentType.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cPaymentType = new PaymentType;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
		/*if (isset($_POST['deleteID']))
		{
			$cPaymentType->deletePaymentType($_POST['deleteID']);
		}
		elseif (isset($_POST['restoreID']))
		{
			$cPaymentType->restorePaymentType($_POST['restoreID']);
		}
		else
		{
			if ( isset($_POST['paymentTypeID']) )
			{
				$cPaymentType->getPaymentType( $_POST['paymentTypeID'] );
			}
			$cPaymentType->setProperty("paymentTypeCategory_ID", $_POST['paymentTypeCategory']);
			$cPaymentType->setProperty("Name", $_POST['paymentTypeName']);
			$cPaymentType->setProperty("Description", $_POST['paymentTypeDescription']);
	
			if ( $cPaymentType->setPaymentType() )
			{
				$result = "Save Success";
			}
			else
			{
				$result = "Save Failed";
			}
		}*/
	}
	elseif ( isset($_GET) && count($_GET) > 0)
	{
		if (isset($_GET['id']) ) //only get one data
		{
			/*$cPaymentType->getPaymentType($_GET['id']);
			$result = array(
				"ID" => $cPaymentType->getProperty("ID"),
				"paymentTypeCategory_ID" => $cPaymentType->getProperty("paymentTypeCategory_ID"),
				"Name" => $cPaymentType->getProperty("Name"),
				"Description" => $cPaymentType->getProperty("Description"),
				"categoryName" => $cPaymentType->getProperty("paymentTypeCategory_Name")
			);*/
		}
		elseif (isset($_GET['lookupName']) ) //only get one data
		{
			$param = array(
				"Name" => $_GET['lookupName']
			);
			$aSearchResult = $cPaymentType->GetPaymentTypeList($param);

			foreach ($aSearchResult as $aResultRow)
			{
				//expected result for jqueryUI is label, value and category
				$result[] = array(
					"label" => $aResultRow['Name'],
					"value" => $aResultRow['ID']
				);
			}
		}
		else //get everything
		{
			/*$paymentTypeData = $cPaymentType->listPaymentType();
			$result = array("data");
			$result['data'] = $paymentTypeData;*/
		}
	}
	else
	{
		//ok, something is definitely wrong here
		$result = "unknown error";
	}
	//*** END PAGE PROCESSING *********************************************//

	//*** BEGIN PAGE RENDERING ********************************************//
	echo json_encode($result);
	//*** END PAGE RENDERING **********************************************//
?>