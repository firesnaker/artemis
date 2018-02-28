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
	* admin/transferGroup.php :: Admin Transfer Grouping Page				*
	****************************************************************************
	* The transfer grouping by outlet page for admin						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-05-02									*
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
	include_once($libPath . "/classTransfer.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cTransfer = new Transfer;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iOutletID = 0;
	$sPageName = "Transfer Group";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_POST['transferGroupSelectSubmit']) && $_POST['outletID']  > 0 )
			{
				$iOutletID = $_POST['outletID'];
			}

			//check and process employee add to group
			if ( isset($_POST['addSubmit']) )
			{
				$aTransferGroupDataType = array(
					"addToOutletID" => "numeric",
					"FromOutletID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aTransferGroupDataType))
				{
					$aTransferGroupData = array(
						"From_outlet_ID" => $_POST['FromOutletID'],
						"To_outlet_ID" => $_POST['addToOutletID']
					);
					$cTransfer->AddTransferOutlet($aTransferGroupData);

					header("location:transferGroup.php?outletID=" . $_POST['FromOutletID']);
					exit;
				}
			}

			//check and process employee removal to group
			if ( isset($_POST['removeSubmit']) )
			{
				$aTransferGroupDataType = array(
					"removeToOutletID" => "numeric",
					"FromOutletID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aTransferGroupDataType))
				{
					$TransferGroupData = array(
						"From_outlet_ID" => $_POST['FromOutletID'],
						"To_outlet_ID" => $_POST['removeToOutletID']
					);
					$cTransfer->RemoveTransferOutlet($TransferGroupData);

					header("location:transferGroup.php?outletID=" . $_POST['FromOutletID']);
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

		//get the employeeoutlet list
		$aTransferGroupList = $cTransfer->GetTranferGroupListByFromOutletID($iOutletID);

		//stop plug gap here, will need to find a new better method later on
		if ($iOutletID == 0)
		{
			$aTransferGroupList = array();
		}

		//get the outletList
		$aOutletList = $cOutlet->GetActiveOutletList();
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/transferGroup.htm"
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
		"VAR_FORM_ACTION" => "admin/transferGroup.php",

		"TEXT_LEGEND_EMPLOYEEGROUPTOOUTLET" => strtoupper("Transfer Outlet Grouping"), //TODO: change into language variables
		"TEXT_LABEL_OUTLET_NAME" => "From Outlet Name", //TODO: change into language variables
		"TEXT_BUTTON_SELECT" => "Select Outlet", //TODO: change into language variables
		
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("To Outlet Name"), //TODO: change into language variables
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

	//transferOutletListBlock
	$transferOutletListBlock = array();
	for ($i = 0; $i < count($aTransferGroupList); $i++)
	{
		$aOutletData = $cOutlet->GetOutletByID($aTransferGroupList[$i]["To_outlet_ID"]);
		$transferOutletListBlock[] = array(
			"VAR_GROUP_NO" => $i+1,
			"VAR_GROUP_NAME" => $aOutletData[0]["Name"]
		);
	}
	$cWebsite->buildBlock("content", "transferOutletListBlock", $transferOutletListBlock);

	//transferToOutletBlock
	$transferToOutletBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$sFormAddDisabled = "";
		$sFormRemoveDisabled = "";
		if ($iOutletID == 0 || $iOutletID == $aOutletList[$i]["ID"])
		{
			$sFormAddDisabled = "disabled";
			$sFormRemoveDisabled = "disabled";
		}
		if ( count($aTransferGroupList) == 0 )
		{
			$sFormRemoveDisabled = "disabled";
		}
		//if To Outlet already in the list, then disable add
		for ($j = 0; $j < count($aTransferGroupList); $j++)
		{
			if ($aTransferGroupList[$j]["To_outlet_ID"] == $aOutletList[$i]["ID"])
			{
				$sFormAddDisabled = "disabled";
			}
		}

		$transferToOutletBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_OUTLET_ID" => $iOutletID,
			"VAR_LIST_NAME" => $aOutletList[$i]['name'],
			"VAR_LIST_ID" => $aOutletList[$i]['ID'],
			"TEXT_BUTTON_ADD" => "Add", //TODO: change into language variables
			"TEXT_BUTTON_REMOVE" => "Remove", //TODO: change into language variables
			"VAR_FORMADDDISABLED" => $sFormAddDisabled,
			"VAR_FORMREMOVEDISABLED" => $sFormRemoveDisabled
		);
	}
	$cWebsite->buildBlock("content", "transferToOutletBlock", $transferToOutletBlock);
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
