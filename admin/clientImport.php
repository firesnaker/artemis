<?php
	/***************************************************************************
	* admin/clientImport.php :: Admin Outlet Import Page					*
	****************************************************************************
	* The client batch import page for admin							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ] 					*
	* Created			: 2014-03-04									*
	* Last modified	: 2014-08-01									*
	*															*
	* 				Copyright (c) 2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( $gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		$iClientID = FALSE;

		if ( isset($_SESSION['client_ID']) )
			$iClientID = $_SESSION['client_ID'];
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
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cClient = new Client;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Client Import";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process outlet quick add
			if ( isset($_POST['clientSubmit']) )
			{
				if (($handle = fopen($_FILES["clientFile"]["tmp_name"], "r")) != FALSE)
				{
					//we assume the file is csv safe and is using the following format:
					//outlet name
					$sSeparator = ";";
					while ($aData = fgetcsv($handle, 0, $sSeparator) )
					{
						$iNbColumn = count($aData);
						$aClient = array(
							"Name" => $aData[0]
						);
						$iInsertResult = $cClient->Insert($aClient);
					}
					$sMessages = "Success";
				}
			}
		}
		else
		{
			$sMessages = "Welcome!"; //TODO: change into language variables
			
			//reset all page variables here:
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the outletList
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/clientImport.htm"
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
		"VAR_FORM_ACTION" => "admin/clientImport.php",

		"TEXT_LEGEND_CLIENTIMPORT" => strtoupper("Client Batch Import"), //TODO: change into language variables
		"TEXT_LABEL_FILE" => "File", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables

	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>