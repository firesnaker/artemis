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
	* admin/paymentTypeIndex.php :: Admin PaymentType Page					*
	****************************************************************************
	* The paymentType quick add/edit/delete page for admin					*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-12-14 									*
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
		//clear the paymentType_ID
		if ( isset($_SESSION['paymentType_ID']) )
			$_SESSION['paymentType_ID']= "";
		
		if ( !isset($_SESSION['paymentTypeIndex_searchBy']) )
		{
			$_SESSION['paymentTypeIndex_searchBy'] = "";
		}
		if ( !isset($_SESSION['paymentTypeIndex_sortBy']) )
			$_SESSION['paymentTypeIndex_sortBy'] = "";
		if ( !isset($_SESSION['paymentTypeIndex_limitBy']) )
			$_SESSION['paymentTypeIndex_limitBy'] = "";
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
	include_once($libPath . "/classPaymentType.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cPaymentType = new PaymentType;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;

	$aSearchBy = $_SESSION['paymentTypeIndex_searchBy'];
	$aSortBy = $_SESSION['paymentTypeIndex_sortBy'];
	$sSortName = "ASC";
	$sSortAddress = "ASC";
	$aLimitBy = $_SESSION['paymentTypeIndex_limitBy'];
	$sPageName = "Payment Type";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process paymentType quick add
			if ( isset($_POST['paymentTypeSubmit']) )
			{
				$aValidType = array(
					"paymentTypeName" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aPaymentType = array(
						"Name" => $_POST['paymentTypeName']
					);
					$iInsertResult = $cPaymentType->Insert($aPaymentType);
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

			//check and process add paymentType
			if ( isset($_POST['paymentTypeAddSubmit']) )
			{
				header("location:paymentType.php");
				exit;
			}
			
			//check and process paymentType search
			if ( isset($_POST['searchSubmit']) )
			{
				$aSearchBy = array(
					"searchName" => "alphanumericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aSearchBy))
				{
					$aSearchBy = array(
						"paymentType.Name" => $_POST['searchName']
					);
					$_SESSION['paymentTypeIndex_searchBy'] = $aSearchBy;
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
			
			//check and process paymentType sort name
			if ( isset($_POST['sortNameSubmit']) )
			{
				if ( array_key_exists('paymentType.Name', $_SESSION['paymentTypeIndex_sortBy']) )
				{
					if ( $_SESSION['paymentTypeIndex_sortBy']['paymentType.Name'] == "ASC" )
					{
						$sSortName = "DESC";
					}
				}

				$aSortBy = array(
					"paymentType.Name" => $sSortName
				);
				$_SESSION['paymentTypeIndex_sortBy'] = $aSortBy;
				$sMessages = "Sort by Name!"; //TODO: change into language variables
			}
			
			//check and process paymentType edit
			if ( isset($_POST['editSubmit']) )
			{
				$aPaymentTypeID = array(
					"editID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aPaymentTypeID))
				{
					$_SESSION['paymentType_ID'] = $_POST['editID'];
					header("location:paymentType.php");
					exit;
				}
			}
			
			//check and process paymentType delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aPaymentTypeID = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aPaymentTypeID))
				{
					$cPaymentType->Remove($_POST['deleteID']);
				}
			}
			
			//check and process paymentType list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['paymentTypeIndex_limitBy']['start'] - $_SESSION['paymentTypeIndex_limitBy']['nbOfData']) < 0 )?0:$_SESSION['paymentTypeIndex_limitBy']['start'] - $_SESSION['paymentTypeIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['paymentTypeIndex_limitBy']['nbOfData']
				);
				$_SESSION['paymentTypeIndex_limitBy'] = $aLimitBy;
				
				if ( ($_SESSION['paymentTypeIndex_limitBy']['start'] - $_SESSION['paymentTypeIndex_limitBy']['nbOfData']) < 0)
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
			}
			
			//check and process paymentType list next
			if ( isset($_POST['nextSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['paymentTypeIndex_limitBy']['start'] + $_SESSION['paymentTypeIndex_limitBy']['nbOfData']) > count($cPaymentType->GetPaymentTypeList($aSearchBy, $aSortBy)) )?$_SESSION['paymentTypeIndex_limitBy']['start']:$_SESSION['paymentTypeIndex_limitBy']['start'] + $_SESSION['paymentTypeIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['paymentTypeIndex_limitBy']['nbOfData']
				);
				$_SESSION['paymentTypeIndex_limitBy'] = $aLimitBy;
				if ( ( $_SESSION['paymentTypeIndex_limitBy']['start'] + $_SESSION['paymentTypeIndex_limitBy']['nbOfData']) > count($cPaymentType->GetPaymentTypeList($aSearchBy, $aSortBy)) )
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
				"paymentType.Name" => ""
			);
			$aSortBy = array(
				"paymentType.Name" => "ASC"
			);
			$sSortName = "ASC";
			$sSortAddress = "ASC";
			$aLimitBy =array(
				"start" => 0,
				"nbOfData" => 10
			);
			$_SESSION['paymentTypeIndex_searchBy'] = $aSearchBy;
			$_SESSION['paymentTypeIndex_sortBy'] = $aSortBy;
			$_SESSION['paymentTypeIndex_limitBy'] = $aLimitBy;
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the paymentTypeList
		$aPaymentTypeList = $cPaymentType->GetPaymentTypeList($aSearchBy, $aSortBy, $aLimitBy);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/paymentTypeIndex.htm"
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
		"VAR_FORM_ACTION" => "admin/paymentTypeIndex.php",

		"TEXT_LEGEND_PAYMENTTYPEQUICKADD" => strtoupper("PaymentType Quick Add"), //TODO: change into language variables
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_PAYMENTTYPE_NAME" => "", //always empty because quick add only, cannot edit
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_PAYMENTTYPEADD" => "Add PaymentType", //TODO: change into language variables
		
		"TEXT_LEGEND_PAYMENTTYPESEARCH" => strtoupper("PaymentType Search"),  //TODO: change into language variables
		"TEXT_BUTTON_SEARCH" => "Search", //TODO: change into language variables
		"VAR_SEARCH_NAME" => (count($aSearchBy) > 0)?$aSearchBy['paymentType.Name']:"",

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

	//paymentTypeListBlock
	$paymentTypeListBlock = array();
	for ($i = 0; $i < count($aPaymentTypeList); $i++)
	{
		$paymentTypeListBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_LIST_NAME" => $aPaymentTypeList[$i]['Name'],
			"VAR_LIST_ID" => $aPaymentTypeList[$i]['ID'],
			"TEXT_BUTTON_EDIT" => "Edit", //TODO: change into language variables
			"TEXT_BUTTON_DELETE" => "Delete" //TODO: change into language variables
		);
	}
	$cWebsite->buildBlock("content", "paymentTypeListBlock", $paymentTypeListBlock);
	
	$cWebsite->template->set_block("content", "paymentTypeListEmptyBlock");
	//paymentTypeListEmptyBlock
	if ( count($aPaymentTypeList) == 0)
	{
		$cWebsite->template->set_var(array(
			"TEXT_LIST_EMPTY" => "List is empty, please add data or change search parameter." //TODO: change into language variables
		));
		$cWebsite->template->parse("paymentTypeListEmptyBlock", "paymentTypeListEmptyBlock");
	}
	else
	{
		$cWebsite->template->set_var("paymentTypeListEmptyBlock", "");	
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
