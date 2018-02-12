<?php
	/***************************************************************************
	* retail/expensesPrint.php :: Retail Expenses Print Page				*
	****************************************************************************
	* The expenses print page for retail								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-12-14 									*
	* Last modified	: 2014-08-21									*
	* 															*
	* 			Copyright (c) 2010-2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_user('user_ID') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classExpenses.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cExpenses = new Expenses;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sDate = date("d-M-Y");
	$sFormElementDisabled = "";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_GET["expensesDate"]) )
			{
				$aExpensesSearchByFieldArray = array(
					"outlet_ID" => $_SESSION['outlet_ID'],
					"Date" => $_GET["expensesDate"]
				);		
				$aExpensesList = $cExpenses->GetExpensesList($aExpensesSearchByFieldArray);
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "retail/expensesPrint.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_EXPENSESDATE" => $sDate,
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_OUTLETID" => $_SESSION['outlet_ID'],
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));

	//expensesListBlock
	$expensesListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aExpensesList); $i++)
	{
		$iGrandTotal += $aExpensesList[$i]['Price'];

		$aCategoryData = $cExpenses->LoadExpensesCategory($aExpensesList[$i]['expenses_category_ID']);
		$sCategoryName = $aCategoryData[0]["Name"];

		$expensesListBlock[] = array(
			"VAR_COUNTER" => $i+1,
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_LISTEXPENSESNAME" => $aExpensesList[$i]['Name'],
			"VAR_LISTEXPENSESCATEGORY" => ($sCategoryName == '')?'-':$sCategoryName,
			"VAR_LISTEXPENSESPRICE" => number_format( $aExpensesList[$i]['Price'], _NbOfDigitBehindComma_ )
		);
	}
	$cWebsite->buildBlock("site", "expensesList", $expensesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTAL" => number_format( $iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>