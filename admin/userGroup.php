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
	* admin/userGroup.php :: Admin User Page							*
	****************************************************************************
	* The user grouping by outlet page for admin							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-12-15									*
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
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iUserID = 0;
	$sPageName = "User Group";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_POST['userGroupSelectSubmit']) && $_POST['userID']  > 0 )
			{
				$iUserID = $_POST['userID'];
			}

			//check and process user add to group
			if ( isset($_POST['addSubmit']) )
			{
				$aUserOutletDataType = array(
					"addID" => "numeric",
					"outletID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aUserOutletDataType))
				{
					$aUserOutletData = array(
						"user_ID" => $_POST['userID'],
						"outlet_ID" => $_POST['addID']
					);
					$cUser->AddUserOutlet($aUserOutletData);

					header("location:userGroup.php?userID=" . $_POST['userID']);
					exit;
				}
			}

			//check and process user removal to group
			if ( isset($_POST['removeSubmit']) )
			{
				$aUserOutletDataType = array(
					"removeID" => "numeric",
					"userID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aUserOutletDataType))
				{
					$aUserOutletData = array(
						"user_ID" => $_POST['userID'],
						"outlet_ID" => $_POST['removeID']
					);
					$cUser->RemoveUserOutlet($aUserOutletData);

					header("location:userGroup.php?userID=" . $_POST['userID']);
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
			$iUserID = $_GET['userID'];
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the useroutlet list
		$aUserOutletSearchBy = array(
			"user_ID" => $iUserID
		);
		$aUserOutletSortBy = array();
		$aUserOutletLimitBy = array();
		$aUserOutletList = $cUser->GetUserOutletList($aUserOutletSearchBy, $aUserOutletSortBy, $aUserOutletLimitBy);

		//stop plug gap here, will need to find a new better method later on
		if ($iUserID == 0)
		{
			$aUserOutletList = array();
		}

		//get the userList
		$aUserSearchBy = array();
		$aUserSortBy = array();
		$aUserLimitBy = array();
		$aUserList = $cUser->GetUserList($aUserSearchBy, $aUserSortBy, $aUserLimitBy);

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
		"content" => "admin/userGroup.htm"
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
		"VAR_FORM_ACTION" => "admin/userGroup.php",

		"TEXT_LEGEND_USERGROUPTOOUTLET" => strtoupper("User Group To Outlet"), //TODO: change into language variables
		"TEXT_LABEL_USER_NAME" => "User Name", //TODO: change into language variables
		"TEXT_BUTTON_SELECT" => "Select User", //TODO: change into language variables
		
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("Name"), //TODO: change into language variables
		"TEXT_ADD" => strtoupper("Add"), //TODO: change into language variables
		"TEXT_REMOVE" => strtoupper("Remove"), //TODO: change into language variables
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//userListBlock
	$userListBlock = array();
	for ($i = 0; $i < count($aUserList); $i++)
	{
		$userListBlock[] = array(
			"VAR_USER_NAME" => $aUserList[$i]['Username'],
			"VAR_USER_ID" => $aUserList[$i]['ID'],
			"VAR_USER_SELECTED" => ($iUserID == $aUserList[$i]['ID'])?"selected":"",
		);
	}
	$cWebsite->buildBlock("content", "userListBlock", $userListBlock);

	//userOutletListBlock
	$userOutletListBlock = array();
	for ($i = 0; $i < count($aUserOutletList); $i++)
	{
		$aOutletName = $cOutlet->GetOutletByID($aUserOutletList[$i]['outlet_ID']);

		$userOutletListBlock[] = array(
			"VAR_GROUP_NO" => $i+1,
			"VAR_GROUP_NAME" => $aOutletName[0]["Name"]
		);
	}
	$cWebsite->buildBlock("content", "userOutletListBlock", $userOutletListBlock);

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$sFormAddDisabled = "";
		$sFormRemoveDisabled = "";
		if ($iUserID == 0)
		{
			$sFormAddDisabled = "disabled";
			$sFormRemoveDisabled = "disabled";
		}
		if ( count($aUserOutletList) == 0 )
		{
			$sFormRemoveDisabled = "disabled";
		}
		//if outlet already in the list, then disable add
		for ($j = 0; $j < count($aUserOutletList); $j++)
		{
			if ($aUserOutletList[$j]["outlet_ID"] == $aOutletList[$i]["ID"])
			{
				$sFormAddDisabled = "disabled";
			}
		}

		$outletListBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_USER_ID" => $iUserID,
			"VAR_LIST_NAME" => $aOutletList[$i]['name'],
			"VAR_LIST_ID" => $aOutletList[$i]['ID'],
			"TEXT_BUTTON_ADD" => "Add", //TODO: change into language variables
			"TEXT_BUTTON_REMOVE" => "Remove", //TODO: change into language variables
			"VAR_FORMADDDISABLED" => $sFormAddDisabled,
			"VAR_FORMREMOVEDISABLED" => $sFormRemoveDisabled
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
