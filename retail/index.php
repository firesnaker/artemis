<?php
	/***************************************************************************
	* retail/index.php :: Retail Login Page								*
	****************************************************************************
	* The login page for retail										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ] 					*
	* Created			: 2010-07-02 									*
	* Last modified	: 2014-08-21									*
	*															*
	* 			Copyright (c) 2010-2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$_SESSION = array(); //reset all previous session
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classValidator.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "LogIn";
	$aModules = array();
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
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
					$_SESSION['user_ID'] = $cUser->ID;
					$_SESSION['user_Name'] = $cUser->Name;
					$_SESSION['user_Level'] = $cUser->Level;

					//get the outlet list, this user is allowed in
					$user_outlet_list = $cUser->GetUserOutletList(array("user_ID" => $cUser->ID));

					$_SESSION['outlet_ID'] = $user_outlet_list[0]['outlet_ID'];

					//there is a possibility that the user outlet is more than 1, for now we will get the first one.
					$cOutlet->Outlet($user_outlet_list[0]['outlet_ID']);
					$_SESSION['outlet_Name'] = $cOutlet->Name;
					//set the purchase page availability here
					$_SESSION['allow_purchase_page'] = $cOutlet->AllowPurchase;

					header("location:dashboard.php");
					exit;
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
	//*** END PAGE PROCESSING ************************************************//
	
	//*** BEGIN PAGE RENDERING ***********************************************//

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
		"VAR_COPYRIGHTYEAR" => date("Y"),

		//page text
		"CSS_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?"normal":"error",
		"VAR_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?$sMessages:$sErrorMessages,
		"VAR_FORM_ACTION" => "retail/index.php",
		"TEXT_LABEL_USERNAME" => "Username", //TODO: change into language variables
		"TEXT_LABEL_PASSWORD" => "Password", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => strtoupper("Login") //TODO: change into language variables
	));
	
	//in index.php, show only if logged in
	if ( isset($_SESSION['user_ID']) and $_SESSION['user_ID'] > 0)
	{
		$cWebsite->template->set_block("navigation", "navigation_top_retail");
		$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_retail");
	}

	if (count($aModules) > 0)
	{
		$aJSController = array();
		foreach ($aModules as $sModule)
		{
			$aJSController[] = array(
				"VAR_MODULE" => $sModule
			);
		}
		$cWebsite->buildBlock("site", "javascriptModule", $aJSController);
	}
	else
	{
		$cWebsite->template->set_block("site", "javascriptModule");
		$cWebsite->template->parse("javascriptModule", "");
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING *************************************************//

?>
