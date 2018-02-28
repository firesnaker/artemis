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
	* admin/oulet.php :: Admin Page									*
	****************************************************************************
	* The employee add/edit/delete page for admin						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ] 					*
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
		$iEmployeeID = FALSE;

		if ( isset($_SESSION['employee_ID']) )
			$iEmployeeID = $_SESSION['employee_ID'];
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
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cEmployee = new Employee;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Employee";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process category add / update
			if ( isset($_POST['employeeSubmit']) )
			{
				$aValidType = array(
					"employeeID" => "numericOrEmpty",
					"employeeName" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{					
					$aEmployee = array(
						"ID" => $_POST['employeeID'],
						"Name" => $_POST['employeeName']
					);
					if ($aEmployee["ID"] == "")
					{
						$iEmployeeResult = $cEmployee->Insert($aEmployee);
					}
					else
					{
						$iEmployeeResult = $cEmployee->Update($aEmployee);
					}
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process employee list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$iPrevID = $cEmployee->GetNextPrevIDByCurrentID("prev", $iEmployeeID);

				if ( $iPrevID == $iEmployeeID )
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['employee_ID']= $iPrevID;
					$iEmployeeID = $iPrevID; //employeeID has changed, therefore we need to reinitialize the employee data by calling the constructor here
				}
			}
			//check and process employee delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aValidType = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$cEmployee->Remove($_POST['deleteID']);
					
					header("Location:employeeIndex.php"); //redirect to employee index page
	 				exit;
				}
			}
			//check and process category list next
			if ( isset($_POST['nextSubmit']) )
			{
				$iNextID = $cEmployee->GetNextPrevIDByCurrentID("next", $iEmployeeID);

				if ( $iNextID == $iEmployeeID )
				{
					$sErrorMessages = "End of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['employee_ID']= $iNextID;
					$iEmployeeID = $iNextID; //employeeID has changed, therefore we need to reinitialize the employee data by calling the constructor here
				}
			}
		}

		$sMessages = ($iEmployeeID == FALSE)?"INSERT":"EDIT"; //TODO: change into language variables

		//get the employee for display on page
		$cEmployee->Employee($iEmployeeID);
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the employee data
		$aEmployeeData = array(
			"ID" => ($iEmployeeID == FALSE)?"":$cEmployee->ID,
			"Name" => ($iEmployeeID == FALSE)?"":$cEmployee->Name
		);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/employee.htm"
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
		"VAR_FORM_ACTION" => "admin/employee.php",
		
		"TEXT_LEGEND_EMPLOYEE" => strtoupper("Employee"), //TODO: change into language variables
		"VAR_EMPLOYEE_ID" => $aEmployeeData["ID"],
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_EMPLOYEE_NAME" => $aEmployeeData["Name"],
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_DELETE" => "Delete", //TODO: change into language variables
		"TEXT_BUTTON_NEXT" => "->",
		"VAR_FORMELEMENTDISABLED" => ($cUser->Username == "admin")?"":"disabled='1'"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
