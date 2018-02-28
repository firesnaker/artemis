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
	* admin/clientGroup.php :: Admin Client Page							*
	****************************************************************************
	* The client grouping by outlet page for admin						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-12-15 									*
	* Last modified	: 2014-08-01									*
	*															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classValidator.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cClient = new Client;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iOutletID = 0;
	$sPageName = "Client Group";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_POST['clientGroupSelectSubmit']) && $_POST['outletID']  > 0 )
			{
				$iOutletID = $_POST['outletID'];
			}

			//check and process client add to group
			if ( isset($_POST['addSubmit']) )
			{
				$aClientOutletDataType = array(
					"addID" => "numeric",
					"outletID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aClientOutletDataType))
				{
					$aClientOutletData = array(
						"outlet_ID" => $_POST['outletID'],
						"client_ID" => $_POST['addID']
					);
					$cClient->AddClientOutlet($aClientOutletData);

					header("location:clientGroup.php?outletID=" . $_POST['outletID']);
					exit;
				}
			}

			//check and process client removal to group
			if ( isset($_POST['removeSubmit']) )
			{
				$aClientOutletDataType = array(
					"removeID" => "numeric",
					"outletID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aClientOutletDataType))
				{
					$aClientOutletData = array(
						"outlet_ID" => $_POST['outletID'],
						"client_ID" => $_POST['removeID']
					);
					$cClient->RemoveClientOutlet($aClientOutletData);

					header("location:clientGroup.php?outletID=" . $_POST['outletID']);
					exit;
				}
			}
		}
		else
		{
			$sMessages = "Welcome!"; //TODO: change into language variables
		}

		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			$iOutletID = $_GET['outletID'];
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the clientoutlet list
		$aClientOutletSearchBy = array(
			"outlet_ID" => $iOutletID
		);
		$aClientOutletSortBy = array();
		$aClientOutletLimitBy = array();
		$aClientOutletList = $cClient->GetClientOutletList($aClientOutletSearchBy, $aClientOutletSortBy, $aClientOutletLimitBy);

		//stop plug gap here, will need to find a new better method later on
		if ($iOutletID == 0)
		{
			$aClientOutletList = array();
		}

		//get the clientList
		$aClientSearchBy = array();
		$aClientSortBy = array();
		$aClientLimitBy = array();
		$aClientList = $cClient->GetClientList($aClientSearchBy, $aClientSortBy, $aClientLimitBy);

		//get the outletList
		$aOutletSearchBy = array();
		$aOutletSortBy = array();
		$aOutletLimitBy = array();
		$aOutletList = $cOutlet->GetOutletList($aOutletSearchBy, $aOutletSortBy, $aOutletLimitBy);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/clientGroup.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_USERLOGGEDIN" => ucfirst($_SESSION['user_Name']),
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		//page text
		"CSS_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?"normal":"error",
		"VAR_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?$sMessages:$sErrorMessages,
		"VAR_FORM_ACTION" => "admin/clientGroup.php",

		"TEXT_LEGEND_CLIENTGROUPTOOUTLET" => strtoupper("Client Group To Outlet"), //TODO: change into language variables
		"TEXT_LABEL_OUTLET_NAME" => "Outlet Name", //TODO: change into language variables
		"TEXT_BUTTON_SELECT" => "Select Outlet", //TODO: change into language variables
		
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("Name"), //TODO: change into language variables
		"TEXT_ADD" => strtoupper("Add"), //TODO: change into language variables
		"TEXT_REMOVE" => strtoupper("Remove"), //TODO: change into language variables
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$outletListBlock[] = array(
			"VAR_OUTLET_NAME" => $aOutletList[$i]['name'],
			"VAR_OUTLET_ID" => $aOutletList[$i]['ID'],
			"VAR_OUTLET_SELECTED" => ($iOutletID == $aOutletList[$i]['ID'])?"selected":"",
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);

	//clientOutletListBlock
	$clientOutletListBlock = array();
	for ($i = 0; $i < count($aClientOutletList); $i++)
	{
		$aClientName = $cClient->GetClientByID($aClientOutletList[$i]['client_ID']);
		$clientOutletListBlock[] = array(
			"VAR_GROUP_NO" => $i+1,
			"VAR_GROUP_NAME" => $aClientName[0]["Name"]
		);
	}
	$cWebsite->buildBlock("content", "clientOutletListBlock", $clientOutletListBlock);

	//clientListBlock
	$clientListBlock = array();
	for ($i = 0; $i < count($aClientList); $i++)
	{
		$sFormAddDisabled = "";
		$sFormRemoveDisabled = "";
		if ($iOutletID == 0)
		{
			$sFormAddDisabled = "disabled";
			$sFormRemoveDisabled = "disabled";
		}
		if ( count($aClientOutletList) == 0 )
		{
			$sFormRemoveDisabled = "disabled";
		}
		//if client already in the list, then disable add
		for ($j = 0; $j < count($aClientOutletList); $j++)
		{
			if ($aClientOutletList[$j]["client_ID"] == $aClientList[$i]["ID"])
			{
				$sFormAddDisabled = "disabled";
			}
		}

		$clientListBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_OUTLET_ID" => $iOutletID,
			"VAR_LIST_NAME" => $aClientList[$i]['Name'],
			"VAR_LIST_ID" => $aClientList[$i]['ID'],
			"TEXT_BUTTON_ADD" => "Add", //TODO: change into language variables
			"TEXT_BUTTON_REMOVE" => "Remove", //TODO: change into language variables
			"VAR_FORMADDDISABLED" => $sFormAddDisabled,
			"VAR_FORMREMOVEDISABLED" => $sFormRemoveDisabled
		);
	}
	$cWebsite->buildBlock("content", "clientListBlock", $clientListBlock);
	
	$cWebsite->template->set_block("content", "clientListEmptyBlock");
	//clientListEmptyBlock
	if ( count($aClientList) == 0)
	{
		$cWebsite->template->set_var(array(
			"TEXT_LIST_EMPTY" => "List is empty, please add data or change search parameter." //TODO: change into language variables
		));
		$cWebsite->template->parse("clientListEmptyBlock", "clientListEmptyBlock");
	}
	else
	{
		$cWebsite->template->set_var("clientListEmptyBlock", "");	
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
