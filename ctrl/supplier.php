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
	* ctrl/supplier.php :: Supplier Controller Page									*
	*************************************************************************
	* The supplier controller page														*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2015-03-23 														*
	* Last modified	: 2015-03-23														*
	*																								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classSupplier.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cSupplier = new FSR_Supplier;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
		if (isset($_POST['deleteID']))
		{
			$cSupplier->deleteSupplier($_POST['deleteID']);
		}
		elseif (isset($_POST['restoreID']))
		{
			$cSupplier->restoreSupplier($_POST['restoreID']);
		}
		else
		{
			if ( isset($_POST['supplierID']) )
			{
				$cSupplier->getSupplier( $_POST['supplierID'] );
			}
			$cSupplier->setProperty("name", $_POST['supplierName']);
	
			if ( $cSupplier->setSupplier() )
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
			$cSupplier->getSupplier($_GET['id']);
			$result = array(
				"id" => $cSupplier->getProperty("id"),
				"name" => $cSupplier->getProperty("name"),
				"deleted" => $cSupplier->getProperty("deleted")
			);
		}
		elseif (isset($_GET['lookupName']) ) //only get one data
		{
			$param = array(
				"name" => "LIKE '%" . $_GET['lookupName'] . "%'",
				"deleted" => "= '0'"
			);
			$aSearchResult = $cSupplier->listSupplier($param);
			foreach ($aSearchResult as $aResultRow)
			{
				$cSupplierRow = new FSR_Supplier;
				$cSupplierRow->getSupplier($aResultRow['id']);

				//expected result for jqueryUI is label and value
				$result[] = array(
					"label" => ucfirst($cSupplierRow->getProperty('name')),
					"value" => $cSupplierRow->getProperty('id')
				);
			}
			$result[] = array(
				"label" => "None",
				"value" => 0
			);
		}
		else //get everything
		{
			$supplierData = $cSupplier->listSupplier();
			$result = array("data");
			$result['data'] = $supplierData;
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
