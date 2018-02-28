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
	* admin/expensesCategory.php :: Admin Expenses Category Page			*
	****************************************************************************
	* The expenses category add/edit/delete page and list for admin			*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2014-05-18 									*
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
		if ( !isset($_SESSION['expensesCategory_searchParam']) )
		{
			$_SESSION['expensesCategory_searchParam'] = "";
		}
		else
		{
			$aSearchParam = $_SESSION['expensesCategory_searchParam'];
		}

		if ( !isset($_SESSION['page_searchParam']) )
		{
			$_SESSION['page_searchParam'] = "";
		}
		else
		{
			$aPageParam = $_SESSION['page_searchParam'];
		}
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
	include_once($libPath . "/classExpenses.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$oExpensesCategory = new Expenses;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iDataPerPage = 10;
	$sPageName = "Expenses Category";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process expensesCategory save (insert/edit)
			if ( isset($_POST['formExpensesCategorySubmit']) )
			{

				$aValidType = array(
					"expensesCategoryName" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aExpensesCategory = array(
						"ID" => $_POST['expensesCategoryID'],
						"Name" => $_POST['expensesCategoryName']
					);
					$iSaveResult = $oExpensesCategory->SaveExpensesCategory($aExpensesCategory);
					if ($iSaveResult == FALSE)
					{
						$sErrorMessages = "Save failed, please check data again!"; //TODO: change into language variables
					}
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process expensesCategory delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aValidType = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$oExpensesCategory->RemoveExpensesCategory($_POST['deleteID']);
				}
			}

			//check and load expensesCategory data to edit
			if ( isset($_POST['editSubmit']) )
			{
				$aValidType = array(
					"editID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aExpensesCategoryToEdit = $oExpensesCategory->LoadExpensesCategory($_POST['editID']);
				}
			}

			//check and process client search
			if ( isset($_POST['searchSubmit']) )
			{
				$aValidType = array(
					"searchName" => "alphanumericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aPageParam['Name'] = $_POST['searchName'];
					//remember to rest the Start value to 0 because this is 
					//a new search
					$aPageParam['Start'] = 0;
					
					$sMessages = "Search!"; //TODO: change into language variables
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process expensesCategory list sort by name
			if ( isset($_POST['sortNameSubmit']) )
			{
				if ( $aPageParam['sortName'] == "ASC" )
				{
					$aPageParam['sortName'] = "DESC";
				}
				else
				{
					$aPageParam['sortName'] = 'ASC';
				}

				$sMessages = "ExpensesCategory Sort by Name!"; //TODO: change into language variables
			}

			//check and process expensesCategory list previous
			if ( isset($_POST['previousSubmit']) )
			{
				//we check the pageParam
				$aPageParam['Start'] -= $aPageParam['DataPerPage'];

				if ( $aPageParam['Start'] <= 0 )
				{
					$aPageParam['Start'] = 0;
					$aPageParam['disablePrev'] = TRUE;

					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
			}

			//check and process expensesCategory list next
			if ( isset($_POST['nextSubmit']) )
			{
				//we check the pageParam
				$iOldStart = $aPageParam['Start'];
				$aPageParam['Start'] += $aPageParam['DataPerPage'];

				if ( $aPageParam['Start'] >= $aPageParam['TotalData'] )
				{
					//$aPageParam['Start'] = floor($aPageParam['TotalData'] / $aPageParam['DataPerPage']);
					$aPageParam['Start'] = $iOldStart;
					$aPageParam['disableNext'] = TRUE;

					$sErrorMessages = "End of record reached!"; //TODO: change into language variables
				}
			}
		}
		else
		{
			$sMessages = "Welcome to Admin ExpensesCategorying Page!"; //TODO: change into language variables

			//reset all page variables here:
			$aPageParam = array(
				'Name' => '',
				'sortName' => 'ASC',
				'Start' => 0,
				'DataPerPage' => $iDataPerPage,
				'TotalData' => $iDataPerPage,
				'disablePrev' => FALSE,
				'disableNext' => FALSE
			);
		}

		//we change only the $aPageparam in the if else statement above
		//that way, we can update $aSearchParam only once here
		//instead of updating it inside the if else statement above
		$aSearchParam = array(
			'LIMIT' => ' ' . $aPageParam['Start'] . ', ' . $aPageParam['DataPerPage'] . ' ',
			'ORDER BY' => ' NAME ' . $aPageParam['sortName'] . ' '
		);
		//now, if Name is searched, we have to inject into $aSearchParam
		if ($aPageParam['Name'] != '')
		{
			$aSearchParam['Name'] = ' LIKE "%' . $aPageParam['Name'] . '%"';
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		//get the list of ExpensesCategorys
		$aExpensesCategoryList = $oExpensesCategory->GetExpensesCategoryList($aSearchParam);

		//now we update the TotalData
		//the count is injected in the ExpensesCategoryList[0]["Count"]
		$aPageParam["TotalData"] = $aExpensesCategoryList[0]['Count'];

		//button enable/disable check
		$aPageParam['disablePrev'] = FALSE;
		$aPageParam['disableNext'] = FALSE;
		if ($aPageParam['Start'] <= 0)
		{
			$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
			$aPageParam['disablePrev'] = TRUE;
		}

		if ( ($aPageParam['Start'] + $aPageParam['DataPerPage']) >= $aPageParam['TotalData'])
		{
			$sErrorMessages = "End of record reached!"; //TODO: change into language variables
			$aPageParam['disableNext'] = TRUE;
		}

		//we save the page parameter and search parameter into session variables
		//here because we need to update the TotalData parameter
		$_SESSION['expensesCategory_searchParam'] = $aSearchParam;
		$_SESSION['page_searchParam'] = $aPageParam;
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/expensesCategory.htm"
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
		"VAR_FORM_ACTION" => "admin/expensesCategory.php",

		"TEXT_LEGEND_EXPENSESCATEGORY" => strtoupper("Insert / Update"), //TODO: change into language variables
		"TEXT_LABEL_NAME" => "ExpensesCategory Name", //TODO: change into language variables
		"VAR_EXPENSESCATEGORY_ID" => (isset($aExpensesCategoryToEdit) && $aExpensesCategoryToEdit[0]['ID'] > 0)?$aExpensesCategoryToEdit[0]['ID']:"", 
		"VAR_EXPENSESCATEGORY_NAME" => (isset($aExpensesCategoryToEdit) && $aExpensesCategoryToEdit[0]['ID'] > 0)?$aExpensesCategoryToEdit[0]['Name']:"",
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables

		"TEXT_LEGEND_SEARCH" => strtoupper("Search"),  //TODO: change into language variables
		"TEXT_BUTTON_SEARCH" => "Search", //TODO: change into language variables
		"VAR_SEARCH_NAME" => $aPageParam['Name'],

		"TEXT_BUTTON_SORTNAME" => $aPageParam['sortName'],
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("ExpensesCategory Name"), //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"DISABLE_BUTTON_PREV" => ($aPageParam['disablePrev'] == TRUE)?"disabled = 1":"",
		"TEXT_BUTTON_NEXT" => "->",
		"DISABLE_BUTTON_NEXT" => ($aPageParam['disableNext'] == TRUE)?"disabled = 1":""
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//expensesCategoryListBlock
	$expensesCategoryListBlock = array();
	for ($i = 0; $i < count($aExpensesCategoryList); $i++)
	{
		$expensesCategoryListBlock[] = array(
			"VAR_LIST_NO" => $i+1+$aPageParam['Start'],
			"VAR_LIST_NAME" => $aExpensesCategoryList[$i]['Name'],
			"VAR_LIST_ID" => $aExpensesCategoryList[$i]['ID'],
			"TEXT_BUTTON_EDIT" => "Edit", //TODO: change into language variables
			"TEXT_BUTTON_DELETE" => "Delete" //TODO: change into language variables
		);
	}
	$cWebsite->buildBlock("content", "expensesCategoryListBlock", $expensesCategoryListBlock);
	
	$cWebsite->template->set_block("content", "expensesCategoryListEmptyBlock");
	//expensesCategoryListEmptyBlock
	if ( count($aExpensesCategoryList) == 0)
	{
		$cWebsite->template->set_var(array(
			"TEXT_LIST_EMPTY" => "List is empty, please add data or change search parameter." //TODO: change into language variables
		));
		$cWebsite->template->parse("expensesCategoryListEmptyBlock", "expensesCategoryListEmptyBlock");
	}
	else
	{
		$cWebsite->template->set_var("expensesCategoryListEmptyBlock", "");	
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
