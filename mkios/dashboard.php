<?php
	/********************************************************************
	* mkios/dashboard.php :: Retail Dashboard Page								*
	*********************************************************************
	* The dashboard page for retail											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-09-02 										*
	* Last modified	: 2013-09-02										*
	* 																	*
	* 				Copyright (c) 2013 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classUser.php");
		include_once($libPath . "/classNews.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 )
		{
			//do nothing
		}
		else
		{
			$_SESSION = array();
			session_destroy(); //destroy all session
			//TODO: create a log file
	 		header("Location:index.php"); //redirect to index page
	 		exit;
		}
		//+++ END session initialization ++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN variable declaration and initialization +++++++++++++++++//
		$sErrorMessages = FALSE;
		$sMessages = FALSE;
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cNews = new News;
		//$cUser = new User($_SESSION['user_ID']);
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchBy = array();
		$aSortBy = array(
			"news.Created" => "DESC"
		);
		$aLimitBy =array(
			"start" => 0,
			"nbOfData" => 10
		);
		$aNews = $cNews->GetNewsList($aSearchBy, $aSortBy, $aLimitBy);

		$sMessages = "Berita Terbaru:";
		$sNewsUpdate = "";
		for ($i = 0; $i < count($aNews); $i++)
		{
			$sNewsUpdate .= $aNews[$i]["Created"];
			$sNewsUpdate .= "<br />";
			$sNewsUpdate .= $aNews[$i]["description"];
			$sNewsUpdate .= "<hr />";
		}
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/dashboard.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"CSS_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?"normal":"error",
		"VAR_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?$sMessages:$sErrorMessages,
		"VAR_NEWSUPDATE" => $sNewsUpdate
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>