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
	* retail/dashboard.php :: Retail Dashboard Page										*
	****************************************************************************
	* The dashboard page for retail															*
	*																									*
	* Version			: 1																		*
	* Author				: Ricky Kurniawan [ FireSnakeR ]									*
	* Created			: 2012-10-03 															*
	* Last modified	: 2014-08-21															*
	* 																									*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	//remember, the role value must always be lowercase
	if ( !$gate->is_valid_user('user_ID') )
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classNews.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cNews = new News;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Dashboard";
	$aModules = array();
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	//+++ page processing ++++++++++++++++++++++++++++++++++++++++++++++++++++//
	//+++ page loading +++++++++++++++++++++++++++++++++++++++++++++++++++++++//
	$aSearchBy = array();
	$aSortBy = array(
		"news.Created" => "DESC"
	);
	$aLimitBy =array(
		"start" => 0,
		"nbOfData" => 5
	);
	$aNews = $cNews->GetNewsList($aSearchBy, $aSortBy, $aLimitBy);
	//*** END PAGE PROCESSING ************************************************//
	
	//*** BEGIN PAGE RENDERING ***********************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "retail/dashboard.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		//page text
		"TEXT_LATESTNEWS" => "Berita Terbaru" //change this to multi language
	));

	//build the news_block
	$newsBlock = array();
	for ($i = 0; $i < count($aNews); $i++)
	{
		$newsBlock[] = array(
			"VAR_NEWSID" => "heading" . $i,
			"VAR_COLLAPSEID" => "collapse" . $i,
			"VAR_COLLAPSESHOW" => ($i == 0)?"in":"",
			"VAR_NEWSDATE" => date( "j M Y g a", strtotime($aNews[$i]["Created"]) ),
			"VAR_NEWSCONTENT" => $aNews[$i]["description"]
		);
	}
	$cWebsite->buildBlock("content", "news_block", $newsBlock);

	//hide purchase link in content if user is not allowed to do any purchase
	$cWebsite->template->set_block("content", "purchaseButton_block");
	$cWebsite->template->set_block("content", "purchaseReport_block");
	if ($_SESSION['allow_purchase_page'] == 0)
	{
		$cWebsite->template->parse("purchaseButton_block", "");
		$cWebsite->template->parse("purchaseReport_block", "");
	}

	$cWebsite->template->set_block("navigation", "navigation_top_retail");
	//hide purchase link in navigation if user is not allowed to do any purchase
	$cWebsite->template->set_block("navigation_top_retail", "purchaseLinkNav_block");
	$cWebsite->template->set_block("navigation_top_retail", "purchaseReportNav_block");
	if ($_SESSION['allow_purchase_page'] == 0)
	{
		$cWebsite->template->parse("purchaseLinkNav_block", "");
		$cWebsite->template->parse("purchaseReportNav_block", "");
	}
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_retail");

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
	//*** END PAGE RENDERING *************************************************//
?>
