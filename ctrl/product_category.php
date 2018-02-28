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
	* ctrl/product_category.php :: Product Category Controller Page			*
	*************************************************************************
	* The product category controller page												*
	*																								*
	* Version			: 2																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2014-11-28 														*
	* Last modified	: 2015-02-12														*
	*																								*
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
