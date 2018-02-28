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
	* admin/clientIndex.php :: Admin Client Page							*
	****************************************************************************
	* The client quick add/edit/delete page for admin						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2009-03-28 									*
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
	if ( $gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		//clear the client_ID
		if ( isset($_SESSION['client_ID']) )
			$_SESSION['client_ID']= "";
		
		if ( !isset($_SESSION['clientIndex_searchBy']) )
		{
			$_SESSION['clientIndex_searchBy'] = "";
		}
		if ( !isset($_SESSION['clientIndex_sortBy']) )
			$_SESSION['clientIndex_sortBy'] = "";
		if ( !isset($_SESSION['clientIndex_limitBy']) )
			$_SESSION['clientIndex_limitBy'] = "";
	}
	else
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
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cClient = new Client;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;

	$aSearchBy = $_SESSION['clientIndex_searchBy'];
	$aSortBy = $_SESSION['clientIndex_sortBy'];
	$sSortName = "ASC";
	$sSortAddress = "ASC";
	$aLimitBy = $_SESSION['clientIndex_limitBy'];
	$sPageName = "Client Index";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process client quick add
			if ( isset($_POST['clientSubmit']) )
			{
				$aValidType = array(
					"clientName" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aClient = array(
						"Name" => $_POST['clientName']
					);
					$iInsertResult = $cClient->Insert($aClient);
					if ($iInsertResult == FALSE)
					{
						$sErrorMessages = "Insert failed, please check data again!"; //TODO: change into language variables
					}
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process add client
			if ( isset($_POST['clientAddSubmit']) )
			{
				header("location:client.php");
				exit;
			}

			//check and process add client
			if ( isset($_POST['clientImportSubmit']) )
			{
				header("location:clientImport.php");
				exit;
			}

			//check and process employee group
			if ( isset($_POST['clientGroupSubmit']) )
			{
				header("location:clientGroup.php");
				exit;
			}
			
			//check and process client search
			if ( isset($_POST['searchSubmit']) )
			{
				$aSearchBy = array(
					"searchName" => "alphanumericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aSearchBy))
				{
					$aSearchBy = array(
						"client.Name" => $_POST['searchName']
					);
					$_SESSION['clientIndex_searchBy'] = $aSearchBy;
					$sMessages = "Search!"; //TODO: change into language variables
					$aLimitBy =array(
						"start" => 0,
						"nbOfData" => 10
					);
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}
			
			//check and process client sort name
			if ( isset($_POST['sortNameSubmit']) )
			{
				if ( array_key_exists('client.Name', $_SESSION['clientIndex_sortBy']) )
				{
					if ( $_SESSION['clientIndex_sortBy']['client.Name'] == "ASC" )
					{
						$sSortName = "DESC";
					}
				}

				$aSortBy = array(
					"client.Name" => $sSortName
				);
				$_SESSION['clientIndex_sortBy'] = $aSortBy;
				$sMessages = "Sort by Name!"; //TODO: change into language variables
			}
			
			//check and process client edit
			if ( isset($_POST['editSubmit']) )
			{
				$aClientID = array(
					"editID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aClientID))
				{
					$_SESSION['client_ID'] = $_POST['editID'];
					header("location:client.php");
					exit;
				}
			}
			
			//check and process client delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aClientID = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aClientID))
				{
					$cClient->Remove($_POST['deleteID']);
				}
			}
			
			//check and process client list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['clientIndex_limitBy']['start'] - $_SESSION['clientIndex_limitBy']['nbOfData']) < 0 )?0:$_SESSION['clientIndex_limitBy']['start'] - $_SESSION['clientIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['clientIndex_limitBy']['nbOfData']
				);
				$_SESSION['clientIndex_limitBy'] = $aLimitBy;
				
				if ( ($_SESSION['clientIndex_limitBy']['start'] - $_SESSION['clientIndex_limitBy']['nbOfData']) < 0)
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
			}
			
			//check and process client list next
			if ( isset($_POST['nextSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['clientIndex_limitBy']['start'] + $_SESSION['clientIndex_limitBy']['nbOfData']) > count($cClient->GetClientList($aSearchBy, $aSortBy)) )?$_SESSION['clientIndex_limitBy']['start']:$_SESSION['clientIndex_limitBy']['start'] + $_SESSION['clientIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['clientIndex_limitBy']['nbOfData']
				);
				$_SESSION['clientIndex_limitBy'] = $aLimitBy;
				if ( ( $_SESSION['clientIndex_limitBy']['start'] + $_SESSION['clientIndex_limitBy']['nbOfData']) > count($cClient->GetClientList($aSearchBy, $aSortBy)) )
				{
					$sErrorMessages = "End of record reached!"; //TODO: change into language variables
				}
			}
		}
		else
		{
			$sMessages = "Welcome!"; //TODO: change into language variables
			
			//reset all page variables here:
			$aSearchBy = array(
				"client.Name" => ""
			);
			$aSortBy = array();
			$sSortName = "ASC";
			$sSortAddress = "ASC";
			$aLimitBy =array(
				"start" => 0,
				"nbOfData" => 10
			);
			$_SESSION['clientIndex_searchBy'] = $aSearchBy;
			$_SESSION['clientIndex_sortBy'] = $aSortBy;
			$_SESSION['clientIndex_limitBy'] = $aLimitBy;
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the clientList
		$aClientList = $cClient->GetClientList($aSearchBy, $aSortBy, $aLimitBy);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/clientIndex.htm"
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
		"VAR_FORM_ACTION" => "admin/clientIndex.php",

		"TEXT_LEGEND_CLIENTQUICKADD" => strtoupper("Client Quick Add"), //TODO: change into language variables
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_CLIENT_NAME" => "", //always empty because quick add only, cannot edit
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_CLIENTADD" => "Add Client", //TODO: change into language variables
		"TEXT_BUTTON_CLIENTIMPORT" => "Import Client", //TODO: change into language variables
		"TEXT_BUTTON_CLIENTGROUP" => "Client Group", //TODO: change into language variables
		
		"TEXT_LEGEND_CLIENTSEARCH" => strtoupper("Client Search"),  //TODO: change into language variables
		"TEXT_BUTTON_SEARCH" => "Search", //TODO: change into language variables
		"VAR_SEARCH_NAME" => (count($aSearchBy) > 0)?$aSearchBy['client.Name']:"",

		"TEXT_BUTTON_SORTNAME" => $sSortName, //TODO: change into language variables
		"TEXT_BUTTON_SORTADDRESS" => $sSortAddress, //TODO: change into language variables
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("Name"), //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_NEXT" => "->",
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//clientListBlock
	$clientListBlock = array();
	for ($i = 0; $i < count($aClientList); $i++)
	{
		$clientListBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_LIST_NAME" => $aClientList[$i]['Name'],
			"VAR_LIST_ID" => $aClientList[$i]['ID'],
			"TEXT_BUTTON_EDIT" => "Edit", //TODO: change into language variables
			"TEXT_BUTTON_DELETE" => "Delete" //TODO: change into language variables
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
