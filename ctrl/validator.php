<?php
	/************************************************************************
	* ctrl/validator.php :: Outlet Controller Page									*
	*************************************************************************
	* The outlet controller page															*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2015-02-12 														*
	* Last modified	: 2015-02-12														*
	*																								*
	* 						Copyright (c) 2015 FireSnakeR									*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classSupplier.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classPaymentType.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cOutlet = new FSR_Outlet;
	$cProduct = new FSR_Product;
	$cSupplier = new FSR_Supplier;
	$cOutlet = new FSR_Outlet;
	$cPaymentType = new PaymentType;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
		if ( isset($_POST['outletParentIDAutoComplete']) )
		{
			$outletExists = FALSE;

			$outletData = $cOutlet->getIDByName($_POST['outletParentIDAutoComplete']);
			if (count($outletData) > 0)
			{
				$outletExists = TRUE;
			}

			if (strtolower($_POST['outletParentIDAutoComplete']) == 'none')
			{
				$outletExists = TRUE;
			}

			$result = array("valid" => $outletExists);
		}

		if ( isset($_POST['productCategoryAutoComplete']) || isset($_POST['productCategoryParentAutoComplete']) )
		{
			if ( isset($_POST['productCategoryAutoComplete']) )
			{
				$sPOSTParam = $_POST['productCategoryAutoComplete'];
			}

			if ( isset($_POST['productCategoryParentAutoComplete']) )
			{
				$sPOSTParam = $_POST['productCategoryParentAutoComplete'];
			}

			$categoryExists = FALSE;
			$productCatData = $cProduct->getCategoryByName($sPOSTParam);
			if (count($productCatData) > 0)
			{
				$categoryExists = TRUE;
			}

			if (strtolower($sPOSTParam) == 'none')
			{
				$categoryExists = TRUE;
			}

			$result = array("valid" => $categoryExists);
		}

		if ( isset($_POST['supplierLookUp']) )
		{
			$supplierExists = FALSE;

			$supplierData = $cSupplier->getIDByName($_POST['supplierLookUp']);
			if (count($supplierData) > 0)
			{
				$supplierExists = TRUE;
			}

			if (strtolower($_POST['supplierLookUp']) == 'none')
			{
				$supplierExists = TRUE;
			}

			$result = array("valid" => $supplierExists);
		}

		if ( isset($_POST['outletLookUp']) )
		{
			$outletExists = FALSE;

			$outletData = $cOutlet->getIDByName($_POST['outletLookUp']);
			if (count($outletData) > 0)
			{
				$outletExists = TRUE;
			}

			if (strtolower($_POST['outletLookUp']) == 'none')
			{
				$outletExists = TRUE;
			}

			$result = array("valid" => $outletExists);
		}

		if ( isset($_POST['paymentTypeLookUp']) )
		{
			$paymentTypeExists = FALSE;

			$paymentTypeData = $cPaymentType->getIDByName($_POST['paymentTypeLookUp']);
			if (count($paymentTypeData) > 0)
			{
				$paymentTypeExists = TRUE;
			}

			if (strtolower($_POST['paymentTypeLookUp']) == 'none')
			{
				$paymentTypeExists = TRUE;
			}

			$result = array("valid" => $paymentTypeExists);
		}
	}
	elseif ( isset($_GET) && count($_GET) > 0)
	{
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