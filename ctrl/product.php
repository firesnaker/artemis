<?php
	/************************************************************************
	* ctrl/product.php :: Product Controller Page									*
	*************************************************************************
	* The product controller page															*
	*																								*
	* Version			: 2																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2014-11-19 														*
	* Last modified	: 2015-02-10														*
	*																								*
	* 					Copyright (c) 2014-2015 FireSnakeR								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classProduct.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cProduct = new FSR_Product;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
		if (isset($_POST['deleteID']))
		{
			$cProduct->deleteProduct($_POST['deleteID']);
		}
		elseif (isset($_POST['restoreID']))
		{
			$cProduct->restoreProduct($_POST['restoreID']);
		}
		else
		{
			if ( isset($_POST['productID']) )
			{
				$cProduct->getProduct( $_POST['productID'] );
			}
			$cProduct->setProperty("productCategory_ID", $_POST['productCategory']);
			$cProduct->setProperty("Name", $_POST['productName']);
			$cProduct->setProperty("Description", $_POST['productDescription']);
	
			if ( $cProduct->setProduct() )
			{
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
		if (isset($_GET['id']) ) //only get one data
		{
			$cProduct->getProduct($_GET['id']);
			$result = array(
				"ID" => $cProduct->getProperty("ID"),
				"productCategory_ID" => $cProduct->getProperty("productCategory_ID"),
				"Name" => $cProduct->getProperty("Name"),
				"Description" => $cProduct->getProperty("Description"),
				"categoryName" => $cProduct->getProperty("productCategory_Name")
			);
		}
		elseif (isset($_GET['lookupName']) ) //only get one data
		{
			$param = array(
				"Name" => "LIKE '%" . $_GET['lookupName'] . "%'",
				"Deleted" => "= '0'"
			);
			$aSearchResult = $cProduct->listProduct($param);
			foreach ($aSearchResult as $aResultRow)
			{
				$cProductRow = new FSR_Product;
				$cProductRow->getProduct($aResultRow['ID']);

				//expected result for jqueryUI is label, value and category
				$result[] = array(
					"label" => $cProductRow->getProperty('Name'),
					"value" => $cProductRow->getProperty('ID'),
					"category" => $cProductRow->getNameByCategory($cProductRow->getProperty('productCategory_ID'))
				);
			}
		}
		else //get everything
		{
			$productData = $cProduct->listProduct();
			$result = array("data");
			$result['data'] = $productData;
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