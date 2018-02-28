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
	* login.php :: Application Sign In Page											*
	*************************************************************************
	* The Sign In page																		*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2007-09-29					 									*
	* Last modified	: 2015-02-09														*
	*																								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ do session check first ++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$_SESSION = array(); //reset all previous session
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classValidator.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new FSR_User;
	$cOutlet = new FSR_Outlet;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Sign In";
	$aModules = array();
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
		//+++ BEGIN $_POST processing ++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$aLogin = array(
				"loginName" => "alphanumeric",
				"loginPassword" => "alphanumeric"
			);
			if ( $cValidator->isValidType($_POST, $aLogin) )
			{
				if ( $iID = $cUser->SignIn($_POST, $aDefaultLogin) ) //is valid user
				{
					$cUser->getUser($iID);

					$_SESSION['user_ID'] = $cUser->getProperty("ID");
					$_SESSION['user_Name'] = $cUser->getProperty("Username");
					$redirect = ""; //redundant, to be removed later
					switch ($cUser->getProperty("Username"))
					{
						case "admin":
							$redirect = "admin";
						break;
						case "audit":
							$redirect = "audit";
						break;
						case "finance":
							$redirect = "finance";
						break;
						case "master":
							$redirect = "master";
						break;
						case "mkios":
							$redirect = "mkios";
						break;
						default:
							$cOutlet->getOutlet($cUser->getProperty("outlet_ID"));
							$_SESSION['outlet_ID'] = $cUser->getProperty("outlet_ID");
							$_SESSION['outlet_Name'] = $cOutlet->getProperty("Name");
							$_SESSION['allow_purchase_page'] = $cOutlet->getProperty("AllowPurchase");
							$redirect = "retail";
						break;
					}

					header("location:". $redirect ."/dashboard.php");
					exit;
				}
				else
				{
					$sErrorMessages = "Invalid Username and Password!"; //TODO: change into language variables
				}
			}
			else
			{
				$sErrorMessages = "Invalid datatype!"; //TODO: change into language variables
			}
		}
		else
		{
			$sMessages = ""; //TODO: change into language variables
		}
		//+++ END $_POST processing ++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING *********************************************//
	
	//*** BEGIN PAGE RENDERING ********************************************//

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
		"VAR_FORM_ACTION" => "index.php",
		"TEXT_LABEL_USERNAME" => "Username", //TODO: change into language variables
		"TEXT_LABEL_PASSWORD" => "Password", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => strtoupper("Login") //TODO: change into language variables
	));

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
	//*** END PAGE RENDERING **********************************************//
?>
