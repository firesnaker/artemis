<?php
	/************************************************************************
	* ctrl/product_category.php :: Product Category Controller Page			*
	*************************************************************************
	* The product category controller page												*
	*																								*
	* Version			: 2																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2014-11-28 														*
	* Last modified	: 2015-02-12														*
	*																								*
	* 						Copyright (c) 2014-2015 FireSnakeR							*
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
			$cProduct->deleteCategory($_POST['deleteID']);
		}
		else
		{
			if ( isset($_POST['productCategoryID']) )
			{
				$cProduct->getCategory( $_POST['productCategoryID'] );
			}
			$cProduct->setProperty("ID", $_POST['productCategoryID']);
			$cProduct->setProperty("parent_ID", $_POST['productCategoryParentID']);
			$cProduct->setProperty("Name", $_POST['productCategoryName']);
	
			if ( $cProduct->setCategory() )
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
			$cProduct->getCategory($_GET['id']);
			$result = array(
				"ID" => $cProduct->getProperty("ID"),
				"parent_ID" => $cProduct->getProperty("parentID"),
				"Name" => $cProduct->getProperty("Name")
			);
			$result['parent_name'] = 'None';

			//do a check for parent ID, if yes, get the parent name
			if ($result['parent_ID'] > 0)
			{
				$result['parent_name'] = $cProduct->getNameByCategory($result['parent_ID']);
			}
		}
		elseif ( isset($_GET['ac_name']) )
		{
			$productCatData = $cProduct->getCategoryByName($_GET['ac_name']);
			for ($i = 0; $i < count($productCatData); $i++)
			{
				$cProductRow = new FSR_Product;
				$cProductRow->getCategory($productCatData[$i]['ID']);
				//expected result for jqueryUI is label and value
				$result[] = array(
					"label" => $cProductRow->getProperty('Name'),
					"value" => $cProductRow->getProperty('ID')
				);
			}
			$result[] = array(
				"label" => 'None',
				"value" => 0
			);
		}
		elseif ( isset($_GET['acp_name']) )
		{
			$productCatData = $cProduct->getCategoryByName($_GET['acp_name']);
			for ($i = 0; $i < count($productCatData); $i++)
			{
				$cProductRow = new FSR_Product;
				$cProductRow->getCategory($productCatData[$i]['ID']);
				//expected result for jqueryUI is label and value
				$result[] = array(
					"label" => $cProductRow->getProperty('Name'),
					"value" => $cProductRow->getProperty('ID')
				);
			}
			$result[] = array(
				"label" => 'None',
				"value" => 0
			);
		}
		else //get everything
		{
			$productCatData = $cProduct->listCategory();
			$result = array("data");
			$result['data'] = $productCatData;
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
