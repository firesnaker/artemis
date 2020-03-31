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
	* audit/index.php :: Audit Login Page								*
	*********************************************************************
	* The login page for audit											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-12-06 										*
	* Last modified	: 2018-03-07										*
	* 																	*
	*********************************************************************/

	//*** Immediate redirect to main index.php ***//
	header("Location:../index.php");
	exit;

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
					if ( ($cUser->Name == "admin" || $cUser->Name == "AUDIT") )
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
		"VAR_FORM_ACTION" => "audit/index.php",
		"TEXT_LABEL_USERNAME" => "Username", //TODO: change into language variables
		"TEXT_LABEL_PASSWORD" => "Password", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => strtoupper("Login") //TODO: change into language variables
	));
	
	//in index.php, show only if logged in
	if ( isset($_SESSION['user_ID']) and $_SESSION['user_ID'] > 0)
	{
		$cWebsite->template->set_block("navigation", "navigation_top_audit");
		$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_audit");
	}
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
