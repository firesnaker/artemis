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
	* mkios/salesPayment.php :: MKios Sales Payment Verification Page		*
	****************************************************************************
	* The sales payment verification page for mkios						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan / FireSnakeR 					*
	* Created			: 2014-06-14 									*
	* Last modified	: 2014-06-14									*
	* 															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classMKios.php");

		//include_once($libPath . "/classSales.php");
		//include_once($libPath . "/classEmployee.php");
		//include_once($libPath . "/classClient.php");
		//include_once($libPath . "/classPaymentType.php");
		//include_once($libPath . "/classProduct.php");
		//include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classUser.php");
		include_once($libPath . "/classBank.php");

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
		$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		$cMKios = new MKios;

		//$cSales = new Sales;
		//$cEmployee = new Employee;
		//$cClient = new Client;
		//$cPaymentType = new PaymentType;
		//$cProduct = new Product;
		//$cOutlet = new Outlet;
		$cUser = new User($_SESSION['user_ID']);
		$cBank = new Bank;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//

		function formatNumber($iNumber)
		{
			$sFormattedNumber = '';
			if ($iNumber == 0)
			{
				$sFormattedNumber = '-';
			}
			elseif ($iNumber < 0)
			{
				$sFormattedNumber = '<span style="color:red">' . $iNumber . '</span>';
			}
			else
			{
				$sFormattedNumber = number_format($iNumber, _NbOfDigitBehindComma_ );
			}

			return $sFormattedNumber;
		}
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{

			//process verify
			if ( isset($_POST['paymentSave']) && $_POST['paymentSave'] == 'Save' )
			{
				$aSalesPaymentInsert = array(
					"ID" => $_POST["salesPaymentID"],
					"mkios_ID" => $_POST["ID"],
					"Date" => $_POST["paymentDate"],
					"Amount" => $_POST["paymentAmount"],
					"Notes" => $_POST["paymentNotes"],
					"IsCash" => $_POST["paymentIsCash"],
					"bank_ID" => $_POST["paymentBank"]
				);

				$cMKios->SaveMKiosPayment($aSalesPaymentInsert);
			}

			if ( isset($_POST['paymentEdit']) && $_POST['paymentEdit'] == 'Edit' )
			{
				$aSalesPaymentEdit = $cMKios->LoadMKiosPayment($_POST['paymentID']);
				list($sYear, $sMonth, $sDay) = explode("-",$aSalesPaymentEdit[0]['Date']);
			}

			if ( isset($_POST['paymentDelete']) && $_POST['paymentDelete'] == 'Delete' )
			{
				$cMKios->RemoveMKiosPayment($_POST['paymentID']);
			}

		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"ID" => (isset($_REQUEST['ID']))?$_REQUEST['ID']:"",
		);
		$aMKiosList = $cMKios->GetMKiosList($aSearchByFieldArray);

		if (count($aMKiosList) > 0)
		{
			//rewrite $aMKiosList to list grouped by same KodeSales and TxPeriod
			$aSearchByFieldArray = array(
				"KodeSales" => $aMKiosList[0]['KodeSales'],
				"TxPeriod" => " = '" . $aMKiosList[0]['TxPeriod'] . "'"
			);

			$aMKiosList = $cMKios->GetMKiosList($aSearchByFieldArray);
		}
	
		$aParam = array(
			"mkios_ID" => (" = " . ((isset($_REQUEST['ID']))?$_REQUEST['ID']:"0"))
		);
		$aSalesPaymentList = $cMKios->ListMKiosPayment($aParam);
/*
		$aEmployeeList = $cEmployee->GetEmployeeList();
		$aClientList = $cClient->GetClientList();
		$aPaymentTypeList = $cPaymentType->GetPaymentTypeList();
		$aProductList = $cProduct->GetProductList();
*/
		$aParam = array();
		$aBankList = $cBank->GetList($aParam);

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/salesPayment.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Sales Payment",
		"VAR_BEGINYEAR" => (isset($aSalesPaymentEdit))?date("Y", mktime(0,0,0, $sMonth, $sDay, $sYear)):date("Y"),
		"VAR_BEGINMONTH" => (isset($aSalesPaymentEdit))?date("m", mktime(0,0,0, $sMonth, $sDay, $sYear)):date("m"),
		"VAR_BEGINDAY" => (isset($aSalesPaymentEdit))?date("d", mktime(0,0,0, $sMonth, $sDay, $sYear)):date("d"),
		"VAR_OLDESTYEAR" => _OldestYear_,
		"VAR_FORMACTION" => "mkios/salesPayment.php",
		"VAR_SALESID" => $_REQUEST['ID'],

		"VAR_SALESPAYMENT_ID" => (isset($aSalesPaymentEdit))?$aSalesPaymentEdit[0]['ID']:"",
		"VAR_SALESPAYMENT_AMOUNT" => (isset($aSalesPaymentEdit))?str_replace(",", "", number_format($aSalesPaymentEdit[0]['Amount'], 0)):"",
		"VAR_SALESPAYMENT_NOTES" => (isset($aSalesPaymentEdit))?$aSalesPaymentEdit[0]['Notes']:"",
		"VAR_SALESPAYMENT_CASHSELECTED" => (isset($aSalesPaymentEdit) && $aSalesPaymentEdit[0]['IsCash'] == 1)?"checked":"",
		"VAR_SALESPAYMENT_BANKSELECTED" => (isset($aSalesPaymentEdit) && $aSalesPaymentEdit[0]['IsCash'] == 0)?"checked":""
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");

	//bankListBlock
	$bankListBlock = array();
	for ($i = 0; $i < count($aBankList); $i++)
	{
		$bankListBlock[] = array(
			"VAR_BANKID" => $aBankList[$i]['ID'],
			"VAR_BANKNAME" => $aBankList[$i]['Name'],
			"VAR_BANKSELECTED" => (isset($aSalesPaymentEdit) && $aSalesPaymentEdit[0]['bank_ID'] == $aBankList[$i]['ID'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "paymentBankBlock", $bankListBlock);

	$reportListBlock = array();
	$iGrandSubtotal = 0;
	$iS005 = 0;
	$iS010 = 0;
	$iS020 = 0;
	$iS025 = 0;
	$iS050 = 0;
	$iS100 = 0;
	for ($i = 0; $i < count($aMKiosList); $i++)
	{
		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SALESID" => $aMKiosList[$i]['ID'],
			"VAR_KODEWH" => $aMKiosList[$i]['KodeWH'],
			"VAR_KODESALES" => $aMKiosList[$i]['KodeSales'],
			"VAR_CUSTOMERGROUP" => $aMKiosList[$i]['CustomerGroup'],
			"VAR_NAMACUST" => $aMKiosList[$i]['NamaCust'],
			"VAR_TXPERIOD" => date("d-M-Y" , strtotime($aMKiosList[$i]['TxPeriod'])),
			"VAR_KODETERMINAL" => $aMKiosList[$i]['KodeTerminal'],
			"VAR_NOHP" => $aMKiosList[$i]['NoHP'],
			//"VAR_SUBTOTAL" => number_format($aMKiosList[$i]['Subtotal'], _NbOfDigitBehindComma_ ),
			"VAR_SUBTOTAL" => formatNumber($aMKiosList[$i]['Subtotal']),
			"VAR_S005" => formatNumber($aMKiosList[$i]['S005']),
			"VAR_S005_SAVE" => $aMKiosList[$i]['S005'],
			"VAR_S010" => formatNumber($aMKiosList[$i]['S010']),
			"VAR_S010_SAVE" => $aMKiosList[$i]['S010'],
			"VAR_S020" => formatNumber($aMKiosList[$i]['S020']),
			"VAR_S020_SAVE" => $aMKiosList[$i]['S020'],
			"VAR_S025" => formatNumber($aMKiosList[$i]['S025']),
			"VAR_S025_SAVE" => $aMKiosList[$i]['S025'],
			"VAR_S050" => formatNumber($aMKiosList[$i]['S050']),
			"VAR_S050_SAVE" => $aMKiosList[$i]['S050'],
			"VAR_S100" => formatNumber($aMKiosList[$i]['S100']),
			"VAR_S100_SAVE" => $aMKiosList[$i]['S100']
		);

		$iGrandSubtotal += $aMKiosList[$i]['Subtotal'];
		$iS005 += $aMKiosList[$i]['S005'];
		$iS010 += $aMKiosList[$i]['S010'];
		$iS020 += $aMKiosList[$i]['S020'];
		$iS025 += $aMKiosList[$i]['S025'];
		$iS050 += $aMKiosList[$i]['S050'];
		$iS100 += $aMKiosList[$i]['S100'];
	}
	$cWebsite->buildBlock("content", "reportListBlock", $reportListBlock);


	$cWebsite->template->set_var(array(
		/*"VAR_REPORTOUTLET" => (isset($_POST['reportOutlet']))?$_POST['reportOutlet']:"0",
		"VAR_REPORTEMPLOYEE" => (isset($_POST['reportEmployee']))?$_POST['reportEmployee']:"0",
		"VAR_REPORTCLIENT" => (isset($_POST['reportClient']))?$_POST['reportClient']:"0",
		"VAR_REPORTPAYMENTTYPE" => (isset($_POST['reportPaymentType']))?$_POST['reportPaymentType']:"0",
		"VAR_REPORTPRODUCT" => (isset($_POST['reportProduct']))?$_POST['reportProduct']:"0",
		*/
		
		"VAR_GRANDTOTAL" => number_format($iGrandSubtotal, _NbOfDigitBehindComma_ )
	));

	//paymentListBlock
	$iPaymentGrandtotal = 0;
	$paymentListBlock = array();
	for ($i = 0; $i < count($aSalesPaymentList); $i++)
	{
		$sBankName = '';
		if ( $aSalesPaymentList[$i]['IsCash'] == 0)
		{
			$aBank = $cBank->Load($aSalesPaymentList[$i]['bank_ID']);
			$sBankName = $aBank[0]['Name'];
		}

		list($sYear, $sMonth, $sDay) = explode("-",$aSalesPaymentList[$i]['Date']);

		$paymentListBlock[] = array(
			"VAR_PAYMENTLIST_NUMBER" => $i+1,
			"VAR_PAYMENTLISTROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_PAYMENTLIST_ID" => $aSalesPaymentList[$i]['ID'],
			"VAR_PAYMENTLIST_DATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_PAYMENTLIST_CASH_BANK" => ($aSalesPaymentList[$i]['IsCash'] == 1)?"Cash":"Bank ". $sBankName,
			"VAR_PAYMENTLIST_AMOUNT" => number_format($aSalesPaymentList[$i]['Amount'], _NbOfDigitBehindComma_ ),
			"VAR_PAYMENTLIST_NOTES" => $aSalesPaymentList[$i]['Notes']
		);
		$iPaymentGrandtotal += $aSalesPaymentList[$i]['Amount'];
	}
	$cWebsite->buildBlock("content", "paymentListBlock", $paymentListBlock);

	$cWebsite->template->set_var(array(
		"VAR_PAYMENTLIST_GRANDTOTAL" => number_format($iPaymentGrandtotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
