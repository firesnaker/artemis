<?php
	/***************************************************************************
	* master/reportDepositSave.php :: Master Deposit report Save Page			*
	****************************************************************************
	* The full deposit report save page for master						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-08-08 									*
	* Last modified	: 2014-08-01									*
	*															*
	* 				Copyright (c) 2010-2014 FireSnakeR					*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classExpenses.php");
	include_once($libPath . "/classDeposit.php");
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cExpenses = new Expenses;
	$cDeposit = new Deposit;
	$cExport = new Export;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_GET is always set, so we check by # of element
		{
			$sReportDate = $_GET['reportYear'] . "-" . $_GET['reportMonth'] . "-" . $_GET['reportDay'];
			$sBeginDate = $_GET['beginYear'] . "-" . $_GET['beginMonth'] . "-" . $_GET['beginDay'];
			$sEndDate = $_GET['endYear'] . "-" . $_GET['endMonth'] . "-" . $_GET['endDay'];
		}
		else
		{
			$sReportDate = date("Y-m-d");
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}

		//+++ END $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"outlet_ID" => ($_GET['reportOutlet'])?$_GET['reportOutlet']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aDepositList = $cDeposit->GetDepositReport($aSearchByFieldArray);
		$aOutletList = $cOutlet->GetActiveOutletList();

		$sSearchOutletName = "All Outlets";		
		if ($_GET['reportOutlet'] > 0)
		{
			$aSearchOutletData = $cOutlet->GetOutletByID($_GET['reportOutlet']);		
			$sSearchOutletName = $aSearchOutletData[0]['Name'];
		}

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "master/reportDeposit.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	//depositListBlock
	$depositListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aDepositList); $i++)
	{
		list($iYear, $iMonth, $iDay) = explode("-", $aDepositList[$i]['Date']);
		$iGrandTotal += $aDepositList[$i]['Price'];

		$aOutletData = $cOutlet->GetOutletByID($aDepositList[$i]['outlet_ID']);
		$sOutletName = $aOutletData[0]["Name"];

		$depositListBlock[] = array(
			"VAR_COUNTER" => $i+1,
			"VAR_DEPOSITDATE" => date("d-M-Y", mktime(0,0,0, $iMonth, $iDay, $iYear)),
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_DEPOSITNOTES" => $aDepositList[$i]['Notes'],
			"VAR_TOTALDEPOSIT" => number_format($aDepositList[$i]['Price'], _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ) . (($aDepositList[$i]['salesPayment_ID'] > 0)?"*":""),
			"VAR_TOTALDEPOSIT_CSV" => number_format($aDepositList[$i]['Price'], 0, "", ""  ) . (($aDepositList[$i]['salesPayment_ID'] > 0)?"*":""),
		);
	}

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALDEPOSIT" => number_format($iGrandTotal, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  )
	));

	//prepare the data
	$aContent = array();
	$aContent[] = array("Date", "Outlet", "Notes", "Setoran");
	foreach ($depositListBlock as $iKey => $aData)
	{
		$aContent[] = array($aData["VAR_DEPOSITDATE"], $aData["VAR_OUTLETNAME"], $aData["VAR_DEPOSITNOTES"], $aData["VAR_TOTALDEPOSIT_CSV"]);
	}
	//generate the grandtotal
	$aContent[] = array("", "", "Grandtotal", $iGrandTotal);

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('reportDepositSave-' . $sSearchOutletName . '-' . $sBeginDate . '-' . $sEndDate . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>