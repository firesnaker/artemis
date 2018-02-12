<?php
	/***************************************************************************
	* admin/news.php :: Admin News Page								*
	****************************************************************************
	* The news page for admin										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-10-03 									*
	* Last modified	: 2013-08-01									*
	*															*
	* 			Copyright (c) 2009-2014 FireSnakeR						*
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
		//clear the news_ID
		if ( isset($_SESSION['news_ID']) )
			$_SESSION['news_ID']= "";
		
		if ( !isset($_SESSION['newsIndex_searchBy']) )
		{
			$_SESSION['newsIndex_searchBy'] = "";
		}
		if ( !isset($_SESSION['newsIndex_sortBy']) )
			$_SESSION['newsIndex_sortBy'] = "";
		if ( !isset($_SESSION['newsIndex_limitBy']) )
			$_SESSION['newsIndex_limitBy'] = "";
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
	include_once($libPath . "/classNews.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cNews = new News;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;

	$aSearchBy = $_SESSION['newsIndex_searchBy'];
	$aSortBy = $_SESSION['newsIndex_sortBy'];
	$sSortDescription = "ASC";
	$aLimitBy = $_SESSION['newsIndex_limitBy'];
	$sPageName = "News";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process news quick add
			if ( isset($_POST['newsSubmit']) )
			{
				$aValidType = array(
					"newsDescription" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aNews = array(
						"ID" => $_POST['newsID'],
						"Description" => $_POST['newsDescription']
					);

					if ($_POST["newsID"] > 0)
					{
						$iUpdateResult = $cNews->Update($aNews);
						if ($iUpdateResult == FALSE)
						{
							$sErrorMessages = "Update failed, please check data again!"; //TODO: change into language variables
						}
					}
					else
					{
						$iInsertResult = $cNews->Insert($aNews);
						if ($iInsertResult == FALSE)
						{
							$sErrorMessages = "Insert failed, please check data again!"; //TODO: change into language variables
						}
					}
					
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process news search
			if ( isset($_POST['searchSubmit']) )
			{
				$aSearchBy = array(
					"newsDescription" => "alphanumericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aSearchBy))
				{
					$aSearchBy = array(
						"news.Description" => $_POST['searchDescription']
					);
					$_SESSION['newsIndex_searchBy'] = $aSearchBy;
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
			
			//check and process news sort description
			if ( isset($_POST['sortDescriptionSubmit']) )
			{
				if ( array_key_exists('news.Description', $_SESSION['newsIndex_sortBy']) )
				{
					if ( $_SESSION['newsIndex_sortBy']['news.Description'] == "ASC" )
					{
						$sSortDescription = "DESC";
					}
				}

				$aSortBy = array(
					"news.Description" => $sSortDescription
				);
				$_SESSION['newsIndex_sortBy'] = $aSortBy;
				$sMessages = "Sort by Description!"; //TODO: change into language variables
			}

			//check and process news edit
			if ( isset($_POST['editSubmit']) )
			{
				$aNewsID = array(
					"editID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aNewsID))
				{
					$aNewsEdit = $cNews->GetNewsByID($_POST['editID']);
				}
			}
			
			//check and process news delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aNewsID = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aNewsID))
				{
					$cNews->Remove($_POST['deleteID']);
				}
			}
			
			//check and process news list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['newsIndex_limitBy']['start'] - $_SESSION['newsIndex_limitBy']['nbOfData']) < 0 )?0:$_SESSION['newsIndex_limitBy']['start'] - $_SESSION['newsIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['newsIndex_limitBy']['nbOfData']
				);
				$_SESSION['newsIndex_limitBy'] = $aLimitBy;
				
				if ( ($_SESSION['newsIndex_limitBy']['start'] - $_SESSION['newsIndex_limitBy']['nbOfData']) < 0)
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
			}
			
			//check and process news list next
			if ( isset($_POST['nextSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['newsIndex_limitBy']['start'] + $_SESSION['newsIndex_limitBy']['nbOfData']) > count($cNews->GetNewsList($aSearchBy, $aSortBy)) )?$_SESSION['newsIndex_limitBy']['start']:$_SESSION['newsIndex_limitBy']['start'] + $_SESSION['newsIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['newsIndex_limitBy']['nbOfData']
				);
				$_SESSION['newsIndex_limitBy'] = $aLimitBy;
				if ( ( $_SESSION['newsIndex_limitBy']['start'] + $_SESSION['newsIndex_limitBy']['nbOfData']) > count($cNews->GetNewsList($aSearchBy, $aSortBy)) )
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
				"news.Description" => ""
			);
			$aSortBy = array();
			$sSortDescription = "ASC";
			$aLimitBy =array(
				"start" => 0,
				"nbOfData" => 10
			);
			$_SESSION['newsIndex_searchBy'] = $aSearchBy;
			$_SESSION['newsIndex_sortBy'] = $aSortBy;
			$_SESSION['newsIndex_limitBy'] = $aLimitBy;
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the outletList
		$aNewsList = $cNews->GetNewsList($aSearchBy, $aSortBy, $aLimitBy);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/news.htm"
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
		"VAR_FORM_ACTION" => "admin/news.php",

		"TEXT_LEGEND_NEWSFORM" => strtoupper("News Form"), //TODO: change into language variables
		"TEXT_LABEL_DESCRIPTION" => "Description", //TODO: change into language variables
		"VAR_NEWS_ID" => ( isset($aNewsEdit) )?$aNewsEdit[0]["ID"]:"", 
		"VAR_NEWS_DESCRIPTION" => ( isset($aNewsEdit) )?$aNewsEdit[0]["Description"]:"", 
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_NEWSADD" => "Add News", //TODO: change into language variables
		
		"TEXT_LEGEND_NEWSSEARCH" => strtoupper("News Search"),  //TODO: change into language variables
		"TEXT_BUTTON_SEARCH" => "Search", //TODO: change into language variables
		"VAR_SEARCH_LOCATION" => (count($aSearchBy) > 0)?$aSearchBy['news.Description']:"",

		"TEXT_BUTTON_SORTDESCRIPTION" => $sSortDescription, //TODO: change into language variables
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_DESCRIPTION" => strtoupper("Description"), //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_NEXT" => "->",
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//newsListBlock
	$newsListBlock = array();
	for ($i = 0; $i < count($aNewsList); $i++)
	{
		$newsListBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_LIST_DESCRIPTION" => $aNewsList[$i]['description'],
			"VAR_LIST_ID" => $aNewsList[$i]['ID'],
			"TEXT_BUTTON_EDIT" => "Edit", //TODO: change into language variables
			"TEXT_BUTTON_DELETE" => "Delete" //TODO: change into language variables
		);
	}
	$cWebsite->buildBlock("content", "newsListBlock", $newsListBlock);
	
	$cWebsite->template->set_block("content", "newsListEmptyBlock");
	//newsListEmptyBlock
	if ( count($aNewsList) == 0)
	{
		$cWebsite->template->set_var(array(
			"TEXT_LIST_EMPTY" => "List is empty, please add data or change search parameter." //TODO: change into language variables
		));
		$cWebsite->template->parse("newsListEmptyBlock", "newsListEmptyBlock");
	}
	else
	{
		$cWebsite->template->set_var("newsListEmptyBlock", "");	
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>