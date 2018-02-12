<?php
	/********************************************************************
	* mkios/inventoryPrint.php :: MKios Inventory Print Page								*
	*********************************************************************
	* The inventory print page for mkios											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-10-15 										*
	* Last modified	: 2013-10-15										*
	* 																	*
	* 				Copyright (c) 2013 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classMKios.php");

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
		$cMKios = new MKios;
		//$cUser = new User($_SESSION['user_ID']);
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		$aSearchParam = array(
			"Date" => ' <= "' . date('Y-m-d') . '"' 
		);
		$aInventoryList = $cMKios->GetInventory($aSearchParam);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "mkios/inventoryPrint.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));
	
	//inventoryListBlock
	$aProductList = array("S005", "S010", "S020", "S025", "S050", "S100");
	$inventoryListBlock = array();
	for ($i = 0; $i < count($aProductList); $i++)
	{
		$inventoryListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_PRODUCTNAME" => $aProductList[$i],
			"VAR_QUANTITY" => number_format( $aInventoryList[ $aProductList[$i] . '_Stock'], _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("site", "inventoryListBlock", $inventoryListBlock);
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>