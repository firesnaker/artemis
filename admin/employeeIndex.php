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
	* admin/employeeIndex.php :: Admin Employee Page						*
	****************************************************************************
	* The employee quick add/edit/delete page for admin					*
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
		//clear the employee_ID
		if ( isset($_SESSION['employee_ID']) )
			$_SESSION['employee_ID']= "";
		
		if ( !isset($_SESSION['employeeIndex_searchBy']) )
		{
			$_SESSION['employeeIndex_searchBy'] = "";
		}
		if ( !isset($_SESSION['employeeIndex_sortBy']) )
			$_SESSION['employeeIndex_sortBy'] = "";
		if ( !isset($_SESSION['employeeIndex_limitBy']) )
			$_SESSION['employeeIndex_limitBy'] = "";
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
	include_once($libPath . "/classEmployee.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cEmployee = new Employee;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;

	$aSearchBy = $_SESSION['employeeIndex_searchBy'];
	$aSortBy = $_SESSION['employeeIndex_sortBy'];
	$sSortName = "ASC";
	$sSortAddress = "ASC";
	$aLimitBy = $_SESSION['employeeIndex_limitBy'];
	$sPageName = "Employee Index";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process employee quick add
			if ( isset($_POST['employeeSubmit']) )
			{
				$aValidType = array(
					"employeeName" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aEmployee = array(
						"Name" => $_POST['employeeName']
					);
					$iInsertResult = $cEmployee->Insert($aEmployee);
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

			//check and process add employee
			if ( isset($_POST['employeeAddSubmit']) )
			{
				header("location:employee.php");
				exit;
			}

			//check and process employee group
			if ( isset($_POST['employeeGroupSubmit']) )
			{
				header("location:employeeGroup.php");
				exit;
			}
			
			//check and process employee search
			if ( isset($_POST['searchSubmit']) )
			{
				$aSearchBy = array(
					"searchName" => "alphanumericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aSearchBy))
				{
					$aSearchBy = array(
						"employee.Name" => $_POST['searchName']
					);
					$_SESSION['employeeIndex_searchBy'] = $aSearchBy;
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
			
			//check and process employee sort name
			if ( isset($_POST['sortNameSubmit']) )
			{
				if ( array_key_exists('employee.Name', $_SESSION['employeeIndex_sortBy']) )
				{
					if ( $_SESSION['employeeIndex_sortBy']['employee.Name'] == "ASC" )
					{
						$sSortName = "DESC";
					}
				}

				$aSortBy = array(
					"employee.Name" => $sSortName
				);
				$_SESSION['employeeIndex_sortBy'] = $aSortBy;
				$sMessages = "Sort by Name!"; //TODO: change into language variables
			}
			
			//check and process employee edit
			if ( isset($_POST['editSubmit']) )
			{
				$aEmployeeID = array(
					"editID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aEmployeeID))
				{
					$_SESSION['employee_ID'] = $_POST['editID'];
					header("location:employee.php");
					exit;
				}
			}
			
			//check and process employee delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aEmployeeID = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aEmployeeID))
				{
					$cEmployee->Remove($_POST['deleteID']);
				}
			}
			
			//check and process employee list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['employeeIndex_limitBy']['start'] - $_SESSION['employeeIndex_limitBy']['nbOfData']) < 0 )?0:$_SESSION['employeeIndex_limitBy']['start'] - $_SESSION['employeeIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['employeeIndex_limitBy']['nbOfData']
				);
				$_SESSION['employeeIndex_limitBy'] = $aLimitBy;
				
				if ( ($_SESSION['employeeIndex_limitBy']['start'] - $_SESSION['employeeIndex_limitBy']['nbOfData']) < 0)
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
			}
			
			//check and process employee list next
			if ( isset($_POST['nextSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['employeeIndex_limitBy']['start'] + $_SESSION['employeeIndex_limitBy']['nbOfData']) > count($cEmployee->GetEmployeeList($aSearchBy, $aSortBy)) )?$_SESSION['employeeIndex_limitBy']['start']:$_SESSION['employeeIndex_limitBy']['start'] + $_SESSION['employeeIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['employeeIndex_limitBy']['nbOfData']
				);
				$_SESSION['employeeIndex_limitBy'] = $aLimitBy;
				if ( ( $_SESSION['employeeIndex_limitBy']['start'] + $_SESSION['employeeIndex_limitBy']['nbOfData']) > count($cEmployee->GetEmployeeList($aSearchBy, $aSortBy)) )
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
				"employee.Name" => ""
			);
			$aSortBy = array();
			$sSortName = "ASC";
			$sSortAddress = "ASC";
			$aLimitBy =array(
				"start" => 0,
				"nbOfData" => 10
			);
			$_SESSION['employeeIndex_searchBy'] = $aSearchBy;
			$_SESSION['employeeIndex_sortBy'] = $aSortBy;
			$_SESSION['employeeIndex_limitBy'] = $aLimitBy;
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the employeeList
		$aEmployeeList = $cEmployee->GetEmployeeList($aSearchBy, $aSortBy, $aLimitBy);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/employeeIndex.htm"
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
		"VAR_FORM_ACTION" => "admin/employeeIndex.php",

		"TEXT_LEGEND_EMPLOYEEQUICKADD" => strtoupper("Employee Quick Add"), //TODO: change into language variables
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_EMPLOYEE_NAME" => "", //always empty because quick add only, cannot edit
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_EMPLOYEEADD" => "Add Employee", //TODO: change into language variables

		"TEXT_BUTTON_EMPLOYEEGROUP" => "Group Employee", //TODO: change into language variables
		
		"TEXT_LEGEND_EMPLOYEESEARCH" => strtoupper("Employee Search"),  //TODO: change into language variables
		"TEXT_BUTTON_SEARCH" => "Search", //TODO: change into language variables
		"VAR_SEARCH_NAME" => (count($aSearchBy) > 0)?$aSearchBy['employee.Name']:"",

		"TEXT_BUTTON_SORTNAME" => $sSortName, //TODO: change into language variables
		"TEXT_BUTTON_SORTADDRESS" => $sSortAddress, //TODO: change into language variables
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("Name"), //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_NEXT" => "->",
		"VAR_FORMELEMENTDISABLED" => ($cUser->Username == "admin")?"":"disabled='1'"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//employeeListBlock
	$employeeListBlock = array();
	for ($i = 0; $i < count($aEmployeeList); $i++)
	{
		$employeeListBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_LIST_NAME" => $aEmployeeList[$i]['Name'],
			"VAR_LIST_ID" => $aEmployeeList[$i]['ID'],
			"TEXT_BUTTON_EDIT" => "Edit", //TODO: change into language variables
			"TEXT_BUTTON_DELETE" => "Delete" //TODO: change into language variables
		);
	}
	$cWebsite->buildBlock("content", "employeeListBlock", $employeeListBlock);
	
	$cWebsite->template->set_block("content", "employeeListEmptyBlock");
	//employeeListEmptyBlock
	if ( count($aEmployeeList) == 0)
	{
		$cWebsite->template->set_var(array(
			"TEXT_LIST_EMPTY" => "List is empty, please add data or change search parameter." //TODO: change into language variables
		));
		$cWebsite->template->parse("employeeListEmptyBlock", "employeeListEmptyBlock");
	}
	else
	{
		$cWebsite->template->set_var("employeeListEmptyBlock", "");	
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
