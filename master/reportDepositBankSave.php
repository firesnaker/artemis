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
	* master/reportDepositBankSave.php :: Master Bank Deposit Save Page		*
	****************************************************************************
	* The full report bank deposit save page for master					*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]	 				*
	* Created			: 2014-05-07 									*
	* Last modified	: 2014-08-01									*
	* 															*
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
	include_once($libPath . "/classBank.php");
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cExpenses = new Expenses;
	$oBank = new Bank;
	$cExport = new Export;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_GET is always set, so we check by # of element
		{
			$sBeginDate = $_GET['beginYear'] . "-" . $_GET['beginMonth'] . "-" . $_GET['beginDay'];
			$sEndDate = $_GET['endYear'] . "-" . $_GET['endMonth'] . "-" . $_GET['endDay'];
		}
		else
		{
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}

		//+++ END $_GET processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchParam = array(
			"outlet_ID" => ($_GET['reportOutlet'])?" = " . $_GET['reportOutlet']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aDepositList = $oBank->GetDepositList($aSearchParam);
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

		$aBank = $oBank->Load($aDepositList[$i]['bank_ID']);
		$sBankName = $aBank[0]['Name'];

		$depositListBlock[] = array(
			"VAR_COUNTER" => $i+1,
			"VAR_DEPOSITDATE" => date("d-M-Y", mktime(0,0,0, $iMonth, $iDay, $iYear)),
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_DEPOSITNOTES" => $aDepositList[$i]['Notes'],
			"VAR_TOTALDEPOSIT" => number_format($aDepositList[$i]['Price'], _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  ) . (($aDepositList[$i]['salesPayment_ID'] > 0)?"*":""),
			"VAR_TOTALDEPOSIT_CSV" => number_format($aDepositList[$i]['Price'], 0, "", ""  ) . (($aDepositList[$i]['salesPayment_ID'] > 0)?"*":""),
			"VAR_DEPOSITBANK" => $sBankName,
		);
	}
	$cWebsite->buildBlock("content", "reportListBlock", $depositListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTALDEPOSIT" => number_format($iGrandTotal, _NbOfDigitBehindComma_, _DecimalPoint_, _CommaSeparator_  )
	));

	//prepare the data
	$aContent = array();
	$aContent[] = array("Date", "Outlet", "Notes", "Setoran", "Bank");
	foreach ($depositListBlock as $iKey => $aData)
	{
		$aContent[] = array($aData["VAR_DEPOSITDATE"], $aData["VAR_OUTLETNAME"], $aData["VAR_DEPOSITNOTES"], $aData["VAR_TOTALDEPOSIT_CSV"], $aData["VAR_DEPOSITBANK"]);
	}
	//generate the grandtotal
	$aContent[] = array("", "", "Grandtotal", $iGrandTotal);

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('reportDepositBankSave-' . $sSearchOutletName . '-' . $sBeginDate . '-' . $sEndDate . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
