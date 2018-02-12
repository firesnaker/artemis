<?php
	/***************************************************************************
	* admin/oulet.php :: Admin Page									*
	****************************************************************************
	* The client add/edit/delete page for admin							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2009-03-28 									*
	* Last modified	: 2014-08-01									*
	*															*
	* 			Copyright (c) 2009-2014 FireSnakeR						*
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
	$sPageName = "Client";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process category add / update
			if ( isset($_POST['clientSubmit']) )
			{
				$aValidType = array(
					"clientID" => "numericOrEmpty",
					"clientName" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{					
					$aClient = array(
						"ID" => $_POST['clientID'],
						"Name" => $_POST['clientName']
					);
					if ($aClient["ID"] == "")
					{
						$iClientResult = $cClient->Insert($aClient);
					}
					else
					{
						$iClientResult = $cClient->Update($aClient);
					}
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process client list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$iPrevID = $cClient->GetNextPrevIDByCurrentID("prev", $iClientID);

				if ( $iPrevID == $iClientID )
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['client_ID']= $iPrevID;
					$iClientID = $iPrevID; //clientID has changed, therefore we need to reinitialize the client data by calling the constructor here
				}
			}
			//check and process client delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aValidType = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$cClient->Remove($_POST['deleteID']);
					
					header("Location:clientIndex.php"); //redirect to client index page
	 				exit;
				}
			}
			//check and process category list next
			if ( isset($_POST['nextSubmit']) )
			{
				$iNextID = $cClient->GetNextPrevIDByCurrentID("next", $iClientID);

				if ( $iNextID == $iClientID )
				{
					$sErrorMessages = "End of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['client_ID']= $iNextID;
					$iClientID = $iNextID; //clientID has changed, therefore we need to reinitialize the client data by calling the constructor here
				}
			}
		}

		$sMessages = ($iClientID == FALSE)?"INSERT":"EDIT"; //TODO: change into language variables

		//get the client for display on page
		$cClient->Client($iClientID);
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the client data
		$aClientData = array(
			"ID" => ($iClientID == FALSE)?"":$cClient->ID,
			"Name" => ($iClientID == FALSE)?"":$cClient->Name
		);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/client.htm"
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
		"VAR_FORM_ACTION" => "admin/client.php",
		
		"TEXT_LEGEND_CLIENT" => strtoupper("Client"), //TODO: change into language variables
		"VAR_CLIENT_ID" => $aClientData["ID"],
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_CLIENT_NAME" => $aClientData["Name"],
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_DELETE" => "Delete", //TODO: change into language variables
		"TEXT_BUTTON_NEXT" => "->"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->template->set_block("navigation", "navigation_left");
	$cWebsite->template->parse("VAR_NAVIGATIONLEFT", "navigation_left");
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>