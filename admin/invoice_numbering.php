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
	* admin/invoice_numbering.php :: Admin Invoice Numbering Page				*
	*************************************************************************
	* The invoice numbering page for admin												*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2007-10-05 														*
	* Last modified	: 2014-08-01														*
	*																								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ********************************************//
	//+++ load the absolute necessities +++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first ++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	//remember, the role value must always be lowercase
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') )
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries +++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classValidator.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classSales.php");
	//+++ initialize objects and classes ++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cSales = new Sales;
	//+++ declare and initialize page variables +++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Invoice Numbering";
	//*** END INITIALIZATION **********************************************//

	//*** BEGIN PAGE PROCESSING *******************************************//
		//+++ BEGIN $_POST processing ++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_POST['resetSalesSubmit']) )
			{
				$cSales->resetNumbering();
			}
			/*$aValidType = array(
				"profileName" => "word",
				"profileUsername" => "alphanumeric",
				"profileNewPassword" => "isAlphanumericOrEmpty",
				"profileConfirmNewPassword" => "isAlphanumericOrEmpty",
				"profilePassword" => "alphanumeric"
			);
			if ( $cValidator->isValidType($_POST, $aValidType))
			{
				$aUser = array(
					"ID" => $_SESSION['user_ID'],
					"Name" => $_POST['profileName'],
					"Username" => $_POST['profileUsername'],
					"Password" => ( $_POST['profileNewPassword'] <> "" && $_POST['profileNewPassword'] == $_POST['profileConfirmNewPassword'] )?$_POST['profileNewPassword']:$_POST['profilePassword'],
					"OldPassword" => $_POST['profilePassword']
				);
				$iUpdateResult = $cUser->Update($aUser);
				if ($iUpdateResult > 0)
				{
					$_SESSION['user_ID'] = $cUser->ID;
					$_SESSION['user_Name'] = $cUser->Name;
					$_SESSION['user_Level'] = $cUser->Level;
				}
				else
				{
					$sErrorMessages = "Update failed, please check data again!"; //TODO: change into language variables
				}
			}
			else
			{
				$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
			}*/
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
		"content" => "admin/invoice_numbering.htm"
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
		//page text
		"CSS_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?"normal":"error",
		"VAR_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?$sMessages:$sErrorMessages,
		"VAR_FORM_ACTION" => "admin/invoice_numbering.php",
		
		"TEXT_LEGEND_INVOICE_NUMBERING" => strtoupper("Invoice Numbering"), //TODO: change into language variables
		"TEXT_SALES_NUMBER" => "Sales Numbering Format", //TODO: change into language variables
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		"TEXT_BUTTON_RESET_SALES_NUMBER" => "Reset Sales Number", //TODO: change into language variables

		"VAR_SALES_NUMBER" => "[outlet_code][mmyyyy][9999]"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING **********************************************//

?>
