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
	* admin/user.php :: Admin Users Page												*
	*************************************************************************
	* The users CRUD page for admin														*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2009-03-28 														*
	* Last modified	: 2015-02-09														*
	*																								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first ++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$sPageName = "User Management";

	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
		//+++ BEGIN $_POST processing ++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing ++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING *********************************************//

	//*** BEGIN PAGE RENDERING ********************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/user.htm"
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
		//user modal dialog text and variables
		"TEXT_USER_FORM" => "User Form", //TODO: change into language variables
		"TEXT_CLOSE" => "Close", //TODO: change into language variables
		"TEXT_SAVE_CHANGES" => "Save Changes", //TODO: change into language variables

		"TEXT_LABEL_USERNAME" => "Username", //TODO: change into language variables
		"TEXT_LABEL_PASSWORD" => "Password", //TODO: change into language variables

		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("Username"), //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
	));

	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING **********************************************//











	/***************************************************************************
	* admin/user.php :: Admin Page									*
	****************************************************************************
	* The user outlet add/edit/delete page for admin						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]		 			*
	* Created			: 2009-03-28 									*
	* Last modified	: 2014-08-01									*
	*															*
	* 			Copyright (c) 2009-2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
/*
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( $gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		$iUserID = FALSE;

		if ( isset($_SESSION['user_user_ID']) )
			$iUserID = $_SESSION['user_user_ID'];
	}
	else
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($rootPath . "config.php");
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classValidator.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUserLoggedIn = new User($_SESSION['user_ID']);
	$cUser = new User;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "User";
*/
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
/*
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process category add / update
			if ( isset($_POST['userSubmit']) )
			{
				$aValidType = array(
					"userID" => "numericOrEmpty",
					"userName" => "word",
					"userEmail" => "emailOrEmpty",
					"userOutlet" => "numericOrEmpty",
					"userIsFinance" => "numericOrEmpty",
					"userUsername" => "alphanumeric",
					"userPassword" => "alphanumericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{					
					$aUser = array(
						"ID" => $_POST['userID'],
						"Name" => $_POST['userName'],
						"Email" => $_POST['userEmail'],
						"Outlet" => $_POST['userOutlet'],
						"IsFinance" => $_POST['userIsFinance'],
						"Username" => $_POST['userUsername'],
						"Password" => $_POST['userPassword']
					);
					if ($aUser["ID"] == "")
					{
						$iUserResult = $cUser->Insert($aUser);
					}
					else
					{
						$iUserResult = $cUser->UpdateWithoutValidation($aUser);
					}
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process user list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$iPrevID = $cUser->GetNextPrevIDByCurrentID("prev", $iUserID);

				if ( $iPrevID == $iUserID )
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['user_user_ID']= $iPrevID;
					$iUserID = $iPrevID; //userID has changed, therefore we need to reinitialize the user data by calling the constructor here
				}
			}
			//check and process user delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aValidType = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$cUser->Remove($_POST['deleteID']);
					
					header("Location:userIndex.php"); //redirect to user index page
	 				exit;
				}
			}
			//check and process category list next
			if ( isset($_POST['nextSubmit']) )
			{
				$iNextID = $cUser->GetNextPrevIDByCurrentID("next", $iUserID);

				if ( $iNextID == $iUserID )
				{
					$sErrorMessages = "End of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['user_user_ID']= $iNextID;
					$iUserID = $iNextID; //userID has changed, therefore we need to reinitialize the user data by calling the constructor here
				}
			}
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		$sMessages = ($iUserID == FALSE)?"INSERT":"EDIT"; //TODO: change into language variables

		if ($iUserID)
		{
			//get the user for display on page
			$cUser->User($iUserID);
	
			//get the user data
			$aUserData = array(
				"ID" => ($iUserID == FALSE)?"":$cUser->ID,
				"Name" => ($iUserID == FALSE)?"":$cUser->Name,
				"Username" => ($iUserID == FALSE)?"":$cUser->Username,
				"OutletID" => ($iUserID == FALSE)?"":$cUser->Outlet_ID,
				"IsFinance" => ($iUserID == FALSE)?"":$cUser->IsFinance,
				"Email" => ($iUserID == FALSE)?"":$cUser->Email
			);
		}

		$aOutletList = $cOutlet->GetOutletList();
*/
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//
/*
	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/user.htm"
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
		"VAR_FORM_ACTION" => "admin/user.php",
		
		"TEXT_LEGEND_USER" => strtoupper("User"), //TODO: change into language variables
		"VAR_USER_ID" => $aUserData["ID"],
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_USER_NAME" => $aUserData["Name"],
		"TEXT_LABEL_EMAIL" => "Email", //TODO: change into language variables
		"VAR_USER_EMAIL" => $aUserData["Email"],
		"TEXT_LABEL_OUTLET" => "Outlet", //TODO: change into language variables
		"TEXT_LABEL_ISFINANCE" => "Is Finance Login ?", //TODO: change into language variables
		"TEXT_YES" => "Yes", //TODO: change into language variables
		"VAR_ISFINANCE" => ($aUserData["IsFinance"] == 1)?"checked":"",
		"TEXT_NO" => "No", //TODO: change into language variables
		"VAR_NOTISFINANCE" => ($aUserData["IsFinance"] <> 1)?"checked":"",
		"TEXT_LABEL_USERNAME" => "Username", //TODO: change into language variables
		"VAR_USER_USERNAME" => $aUserData["Username"], //TODO: change into language variables
		"TEXT_LABEL_PASSWORD" => "Password", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		"TEXT_NOTEFORADMINUSERCANNOTEDIT" => "Note: Untuk Admin, silahkan gunakan profil untuk mengganti username dan password.",
		"VAR_DISABLEDSTATUS" => ($aUserData["Username"] == "admin")?'disabled="1"':'',
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_DELETE" => "Delete", //TODO: change into language variables
		"TEXT_BUTTON_NEXT" => "->"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$outletListBlock[] = array(
			"VAR_OUTLETID" => $aOutletList[$i]['ID'],
			"VAR_OUTLETNAME" => $aOutletList[$i]['name'],
			"VAR_OUTLETSELECTED" => ($aOutletList[$i]['ID'] == $aUserData["OutletID"])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);	

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
*/
	//*** END PAGE RENDERING ****************************************************//
?>
