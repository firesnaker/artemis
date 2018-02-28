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
	* admin/paymentType.php :: Admin Payment Type Page					*
	****************************************************************************
	* The paymentType add/edit/delete page for admin						*
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
		$iPaymentTypeID = FALSE;

		if ( isset($_SESSION['paymentType_ID']) )
			$iPaymentTypeID = $_SESSION['paymentType_ID'];
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
	include_once($libPath . "/classPaymentType.php");
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cPaymentType = new PaymentType;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Payment Type";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process category add / update
			if ( isset($_POST['paymentTypeSubmit']) )
			{
				$aValidType = array(
					"paymentTypeID" => "numericOrEmpty",
					"paymentTypeName" => "word",
					"paymentTypeIsCash" => "numericOrEmpty",
					"paymentTypePLNoCount" => "numericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{					
					$aPaymentType = array(
						"ID" => $_POST['paymentTypeID'],
						"Name" => $_POST['paymentTypeName'],
						"IsCash" => (isset($_POST["paymentTypeIsCash"]) && $_POST["paymentTypeIsCash"] == 1)?1:0,
						"PLNoCount" => (isset($_POST["paymentTypePLNoCount"]) && $_POST["paymentTypePLNoCount"] == 1)?1:0
					);
					if ($aPaymentType["ID"] == "")
					{
						$iPaymentTypeResult = $cPaymentType->Insert($aPaymentType);
					}
					else
					{
						$iPaymentTypeResult = $cPaymentType->Update($aPaymentType);
					}
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process paymentType list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$iPrevID = $cPaymentType->GetNextPrevIDByCurrentID("prev", $iPaymentTypeID);

				if ( $iPrevID == $iPaymentTypeID )
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['paymentType_ID']= $iPrevID;
					$iPaymentTypeID = $iPrevID; //paymentTypeID has changed, therefore we need to reinitialize the paymentType data by calling the constructor here
				}
			}
			//check and process paymentType delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aValidType = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$cPaymentType->Remove($_POST['deleteID']);
					
					header("Location:paymentTypeIndex.php"); //redirect to paymentType index page
	 				exit;
				}
			}
			//check and process category list next
			if ( isset($_POST['nextSubmit']) )
			{
				$iNextID = $cPaymentType->GetNextPrevIDByCurrentID("next", $iPaymentTypeID);

				if ( $iNextID == $iPaymentTypeID )
				{
					$sErrorMessages = "End of record reached!"; //TODO: change into language variables
				}
				else
				{
					$_SESSION['paymentType_ID']= $iNextID;
					$iPaymentTypeID = $iNextID; //paymentTypeID has changed, therefore we need to reinitialize the paymentType data by calling the constructor here
				}
			}
		}

		$sMessages = ($iPaymentTypeID == FALSE)?"INSERT":"EDIT"; //TODO: change into language variables

		//get the paymentType for display on page
		$cPaymentType->PaymentType($iPaymentTypeID);
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the paymentType data
		$aPaymentTypeData = array(
			"ID" => ($iPaymentTypeID == FALSE)?"":$cPaymentType->ID,
			"Name" => ($iPaymentTypeID == FALSE)?"":$cPaymentType->Name,
			"IsCash" => ($iPaymentTypeID == FALSE)?"":$cPaymentType->IsCash,
			"PLNoCount" => ($iPaymentTypeID == FALSE)?"":$cPaymentType->PLNoCount
		);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/paymentType.htm"
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
		"VAR_FORM_ACTION" => "admin/paymentType.php",
		
		"TEXT_LEGEND_PAYMENTTYPE" => strtoupper("PaymentType"), //TODO: change into language variables
		"VAR_PAYMENTTYPE_ID" => $aPaymentTypeData["ID"],
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_PAYMENTTYPE_NAME" => $aPaymentTypeData["Name"],
		"TEXT_LABEL_ISCASH" => "Is Cash (Used in Profit Loss Statement)",
		"VAR_PAYMENTTYPE_ISCASH" => (($aPaymentTypeData) && ($aPaymentTypeData["IsCash"] == 1))?"checked='checked'":"",
		"TEXT_LABEL_PLNOCOUNT" => "Do not count in profit-loss statement (Used in Profit Loss Statement)",
		"VAR_PAYMENTTYPE_PLNOCOUNT" => (($aPaymentTypeData) && ($aPaymentTypeData["PLNoCount"] == 1))?"checked='checked'":"",
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_DELETE" => "Delete", //TODO: change into language variables
		"TEXT_BUTTON_NEXT" => "->"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
