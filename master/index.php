<?php
	/********************************************************************
	* master/index.php :: Purchase Login Page								*
	*********************************************************************
	* The login page for master											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2012-07-21 										*
	* Last modified	: 2012-07-21										*
	* 																	*
	* 				Copyright (c) 2012 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classValidator.php");
		include_once($libPath . "/classUser.php");
		include_once($libPath . "/classOutlet.php");
		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();
		$_SESSION = array(); //reset all previous session
		//+++ END session initialization ++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN variable declaration and initialization +++++++++++++++++//
		$sErrorMessages = FALSE;
		$sMessages = FALSE;
		$sPageName = "LogIn";
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cValidator = new Validator;
		$cUser = new User;
		$cOutlet = new Outlet;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$aLogin = array(
				"loginName" => "alphanumeric",
				"loginPassword" => "alphanumeric"
			);
			if ( $cValidator->isValidType($_POST, $aLogin) )
			{
				$sLoginResult = $cUser->Login($_POST, $aDefaultLogin);
				if ( $sLoginResult > 0 ) //is valid user
				{
					$cUser->User($sLoginResult);
					if ( ($cUser->Name == "admin") )
					{
						$_SESSION['user_ID'] = $cUser->ID;
						$_SESSION['user_Name'] = $cUser->Name;
						$_SESSION['user_Level'] = $cUser->Level;
						$_SESSION['outlet_ID'] = $cUser->Outlet_ID;
	
						$cOutlet->Outlet($cUser->Outlet_ID);
						$_SESSION['outlet_Name'] = $cOutlet->Name . "<br />" . $cOutlet->Address;
						header("location:dashboard.php");
						exit;
					}
					else
					{
						$sErrorMessages = "Invalid Username and Password too, please check again!"; //TODO: change into language variables
					}
				}
				else
				{
					$sErrorMessages = "Invalid Username and Password, please check again!"; //TODO: change into language variables
				}
			}
			else
			{
				$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
			}
		}
		else
		{
			$sMessages = ""; //TODO: change into language variables
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "login.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		//page text
		"CSS_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?"normal":"error",
		"VAR_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?$sMessages:$sErrorMessages,
		"VAR_FORM_ACTION" => "master/index.php",
		"TEXT_LABEL_USERNAME" => "Username", //TODO: change into language variables
		"TEXT_LABEL_PASSWORD" => "Password", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => strtoupper("Login") //TODO: change into language variables
	));
	
	//in index.php, show only if logged in
	if ( isset($_SESSION['user_ID']) and $_SESSION['user_ID'] > 0)
	{
		$cWebsite->template->set_block("navigation", "navigation_top_master");
		$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");
	}
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>