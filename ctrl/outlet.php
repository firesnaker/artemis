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
	* ctrl/outlet.php :: Outlet Controller Page										*
	*************************************************************************
	* The outlet controller page															*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2015-02-12 														*
	* Last modified	: 2015-02-12														*
	*																								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cOutlet = new FSR_Outlet;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
		if (isset($_POST['deleteID']))
		{
			$cOutlet->deleteOutlet($_POST['deleteID']);
		}
		elseif (isset($_POST['restoreID']))
		{
			$cOutlet->restoreOutlet($_POST['restoreID']);
		}
		else
		{
			if (isset($_POST['outletID']))
			{
				$cOutlet->getOutlet($_POST['outletID']);
			}
			$cOutlet->setProperty("master_outlet_ID", $_POST['outletParentID']);
			$cOutlet->setProperty("code", strtoupper($_POST['outletCode']));
			$cOutlet->setProperty("Name", $_POST['outletName']);
			$cOutlet->setProperty("Address", $_POST['outletAddress']);
			$cOutlet->setProperty("Phone", $_POST['outletPhone']);
			$cOutlet->setProperty("Fax", $_POST['outletFax']);
			$cOutlet->setProperty("AllowPurchase", (isset($_POST['outletAllowPurchase']))?$_POST['outletAllowPurchase']:"0");
	
			if ( $cOutlet->setOutlet() )
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
			$cOutlet->getOutlet($_GET['id']);

			$result = array(
				"ID" => $cOutlet->getProperty("ID"),
				"parentID" => $cOutlet->getProperty("parentID"),
				"code" => $cOutlet->getProperty("code"),
				"Name" => $cOutlet->getProperty("Name"),
				"Address" => $cOutlet->getProperty("Address"),
				"Phone" => $cOutlet->getProperty("Phone"),
				"Fax" => $cOutlet->getProperty("Fax"),
				"AllowPurchase" => $cOutlet->getProperty("AllowPurchase"),
				"parentName" => $cOutlet->getProperty("parentName")
			);
		}
		elseif ( isset($_GET['ac_name']) )
		{
			$param = array(
				"Name" => "LIKE '%" . $_GET['ac_name'] . "%'",
				"Deleted" => "= '0'"
			);
			$aSearchResult = $cOutlet->listOutlet($param);
			foreach ($aSearchResult as $aResultRow)
			{
				$cOutletRow = new FSR_Outlet;
				$cOutletRow->getOutlet($aResultRow['ID']);

				//expected result for jqueryUI is label, value and category
				$result[] = array(
					"label" => $cOutletRow->getProperty('Name'),
					"value" => $cOutletRow->getProperty('ID')
				);
			}
			$result[] = array(
				"label" => "none",
				"value" => "0"
			);
		}
		elseif ( isset($_GET['acp_name']) )
		{
			$param = array(
				"Name" => "LIKE '%" . $_GET['acp_name'] . "%'",
				"Deleted" => "= '0'"
			);
			$aSearchResult = $cOutlet->listOutlet($param);
			foreach ($aSearchResult as $aResultRow)
			{
				$cOutletRow = new FSR_Outlet;
				$cOutletRow->getOutlet($aResultRow['ID']);

				//expected result for jqueryUI is label, value and category
				$result[] = array(
					"label" => $cOutletRow->getProperty('Name'),
					"value" => $cOutletRow->getProperty('ID')
				);
			}
		}
		elseif (isset($_GET['lookupName']) ) //only get one data
		{
			$param = array(
				"Name" => "LIKE '%" . $_GET['lookupName'] . "%'",
				"Deleted" => "= '0'"
			);
			$aSearchResult = $cOutlet->listOutlet($param);
			foreach ($aSearchResult as $aResultRow)
			{
				$cOutletRow = new FSR_Outlet;
				$cOutletRow->getOutlet($aResultRow['ID']);

				//expected result for jqueryUI is label, value and category
				$result[] = array(
					"label" => $cOutletRow->getProperty('Name'),
					"value" => $cOutletRow->getProperty('ID'),
					"parent" => $cOutletRow->getNameByID($cOutletRow->getProperty('ID'))
				);
			}
		}
		else //get everything
		{
			$outletData = $cOutlet->listOutlet();
			$result = array("data");
			$result['data'] = $outletData;
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
