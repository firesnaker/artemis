<?php
	/***************************************************************************
	* admin/userFinance.php :: Admin User Finance Page					*
	****************************************************************************
	* The user finance profile view/edit page for admin					*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ] 					*
	* Created			: 2012-05-03									*
	* Last modified	: 2014-08-01									*
	*															*
	* 			Copyright (c) 2012-2014 FireSnakeR						*
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
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$sSESSIONUsername = $cUser->Username;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "User Finance";
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$aValidType = array(
				"profileName" => "word",
				"profileUsername" => "alphanumeric",
				"profileNewPassword" => "isAlphanumericOrEmpty",
				"profileConfirmNewPassword" => "isAlphanumericOrEmpty",
				"profilePassword" => "alphanumeric"
			);
			if ( $cValidator->isValidType($_POST, $aValidType))
			{
				//retrieve from database, user with username="finance"
				$aUserData = $cUser->GetUserByUsername("finance");
				if (count($aUserData) == 0)
				{
					$aUser = array(
						"Outlet" => 0,
						"Name" => "FINANCE",
						"Username" => "finance",
						"Password" => ( $_POST['profileNewPassword'] <> "" && $_POST['profileNewPassword'] == $_POST['profileConfirmNewPassword'] )?$_POST['profileNewPassword']:$_POST['profilePassword'],
						"Email" => "",
					);
					$iUpdateResult = $cUser->Insert($aUser);
				}
				else
				{
					$aUser = array(
						"ID" => $aUserData[0]["ID"],
						"Name" => "FINANCE",
						"Username" => "finance",
						"Password" => ( $_POST['profileNewPassword'] <> "" && $_POST['profileNewPassword'] == $_POST['profileConfirmNewPassword'] )?$_POST['profileNewPassword']:$_POST['profilePassword'],
						"OldPassword" => $_POST['profilePassword']
					);
					$iUpdateResult = $cUser->Update($aUser);
					if ($iUpdateResult > 0)
					{
						//reset the user to session user
						$cUser->User($_SESSION['user_ID']);
					}
					else
					{
						$sErrorMessages .= " Update failed, please check data again!"; //TODO: change into language variables
					}
				}
			}
			else
			{
				$sErrorMessages .= " Invalid datatype, please check again!"; //TODO: change into language variables
			}
		}
		else
		{
			$sMessages = "Welcome! Please check and update your profile if needed."; //TODO: change into language variables
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/userFinance.htm"
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
		//page text
		"CSS_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?"normal":"error",
		"VAR_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?$sMessages:$sErrorMessages,
		"VAR_FORM_ACTION" => "admin/userFinance.php",
		
		"TEXT_LEGEND_PROFILE" => strtoupper("Profile"), //TODO: change into language variables
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_USER_NAME" => "FINANCE", //TODO: change into language variables
		"TEXT_LABEL_USERNAME" => "Username", //TODO: change into language variables
		"VAR_USER_USERNAME" => "finance", //TODO: change into language variables
		"TEXT_LABEL_NEWPASSWORD" => "New Password", //TODO: change into language variables
		"TEXT_LABEL_CONFIRMNEWPASSWORD" => "Confirm New Password", //TODO: change into language variables
		"TEXT_LABEL_PASSWORD" => "Password, please fill in to change data", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => "Save" //TODO: change into language variables
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>