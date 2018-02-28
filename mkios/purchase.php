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
	* mkios/purchase.php :: MKIOS Purchase Page							*
	****************************************************************************
	* The purchase page for mkios										*
	*																*
	* Version			: 0.1										*
	* Author			: FireSnakeR 									*
	* Created			: 2013-09-30 									*
	* Last modified	: 2014-05-23									*
	*															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classMKios.php");
		include_once($libPath . "/classExport.php");

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
		$cExport = new Export;
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
				$sFormattedNumber = $iNumber;
			}

			return number_format($sFormattedNumber, _NbOfDigitBehindComma_ );
		}
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDateBegin = $_POST['reportYearBegin'] . "-" . $_POST['reportMonthBegin'] . "-" . $_POST['reportDayBegin'];
			$sReportDateEnd = $_POST['reportYearEnd'] . "-" . $_POST['reportMonthEnd'] . "-" . $_POST['reportDayEnd'];

			//prepare and insert data to database
			if ($_POST["purchaseAction"] == "Tambah" || $_POST["purchaseAction"] == "Rubah")
			{
				$aPurchaseInsert = array(
					"ID" => $_POST["purchaseEditID"],
					"Date" => $_POST["purchaseYear"] . "-" . $_POST["purchaseMonth"] . "-" . $_POST["purchaseDay"],
					"S005" => $_POST["purchaseS005"],
					"S010" => $_POST["purchaseS010"],
					"S020" => $_POST["purchaseS020"],
					"S025" => $_POST["purchaseS025"],
					"S050" => $_POST["purchaseS050"],
					"S100" => $_POST["purchaseS100"],
					"Notes" => $_POST["purchaseNotes"]
				);

				if ($aPurchaseInsert['ID'] > 0)
				{
					$iPurchaseID = $cMKios->UpdatePurchase($aPurchaseInsert);
				}
				else
				{
					$iPurchaseID = $cMKios->InsertPurchase($aPurchaseInsert);
				}
			}

			//load edit data from database
			if ( isset($_POST["purchaseID"]) && $_POST["purchaseID"] > 0)
			{
				$aSearchByFieldArray = array(
					"ID" => $_POST['purchaseID']
				);
				$aPurchaseDataToEdit = $cMKios->GetMKiosPurchaseList($aSearchByFieldArray);

				//split the date into individual date
				list($sPurchaseDateYear, $sPurchaseDateMonth, $sPurchaseDateDay) = explode("-", $aPurchaseDataToEdit[0]['Date']);
			}
		}
		else
		{
			$sReportDateBegin = date("Y-m-d");
			$sReportDateEnd = date("Y-m-d");
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		//split the reportdateBegin to individual data
		list($sReportDateBeginYear, $sReportDateBeginMonth, $sReportDateBeginDay) = explode("-", $sReportDateBegin);
		//split the reportdateEnd to individual data
		list($sReportDateEndYear, $sReportDateEndMonth, $sReportDateEndDay) = explode("-", $sReportDateEnd);

		$aSearchByFieldArray = array(
			"Date" => "BETWEEN '" . $sReportDateBegin . "' AND '" . $sReportDateEnd . "'",
			"Product" => $_POST['VTSProduct']
			//"sales.client_ID" => ($_POST['reportClient'])?$_POST['reportClient']:"",
			//"sales.employee_ID" => ($_POST['reportEmployee'])?$_POST['reportEmployee']:"",
			//"sales_detail.product_ID" => ($_POST['reportProduct'])?$_POST['reportProduct']:"",
		);
		$aSortByFieldArray = array(
			"Date" => "ASC"
		);

		$aMKiosList = $cMKios->GetMKiosPurchaseList($aSearchByFieldArray, $aSortByFieldArray);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/purchase.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"VAR_REPORTYEARBEGIN" => $sReportDateBeginYear,
		"VAR_REPORTMONTHBEGIN" => $sReportDateBeginMonth,
		"VAR_REPORTDAYBEGIN" => $sReportDateBeginDay,
		"VAR_REPORTYEAREND" => $sReportDateEndYear,
		"VAR_REPORTMONTHEND" => $sReportDateEndMonth,
		"VAR_REPORTDAYEND" => $sReportDateEndDay,
		"VAR_PURCHASEACTION" => ($_POST["purchaseID"] > 0)?"Rubah":"Tambah",
		"VAR_PURCHASEEDITID" => $aPurchaseDataToEdit[0]['ID'],
		"VAR_PURCHASEEDITNOTES" => $aPurchaseDataToEdit[0]['Notes'],
		"VAR_PURCHASEEDITS005" => $aPurchaseDataToEdit[0]['S005'],
		"VAR_PURCHASEEDITS010" => $aPurchaseDataToEdit[0]['S010'],
		"VAR_PURCHASEEDITS020" => $aPurchaseDataToEdit[0]['S020'],
		"VAR_PURCHASEEDITS025" => $aPurchaseDataToEdit[0]['S025'],
		"VAR_PURCHASEEDITS050" => $aPurchaseDataToEdit[0]['S050'],
		"VAR_PURCHASEEDITS100" => $aPurchaseDataToEdit[0]['S100'],
		"VAR_FORM_EDIT_DISABLED" => (strtoupper($_SESSION['user_Name']) != "ADMIN")?"disabled":"" //disable edit mode to admin login only
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");

	//dateDayBeginBlock
	$dateDayBeginBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['reportDayBegin']) )
		{
			$sDefaultDay = $_POST['reportDayBegin'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$dateDayBeginBlock[] = array(
			"VAR_DAYBEGINVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYBEGINSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateDayBeginBlock", $dateDayBeginBlock);

	//dateMonthBeginBlock
	$dateMonthBeginBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['reportMonthBegin']) )
		{
			$sDefaultMonth = $_POST['reportMonthBegin'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$dateMonthBeginBlock[] = array(
			"VAR_MONTHBEGINVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHBEGINTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHBEGINSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateMonthBeginBlock", $dateMonthBeginBlock);

	//dateYearBeginBlock
	$dateYearBeginBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['reportYearBegin']) )
		{
			$sDefaultYear = $_POST['reportYearBegin'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$dateYearBeginBlock[] = array(
			"VAR_YEARBEGINVALUE" => $i,
			"VAR_YEARBEGINSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateYearBeginBlock", $dateYearBeginBlock);
	
	//dateDayEndBlock
	$dateDayEndBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['reportDayEnd']) )
		{
			$sDefaultDay = $_POST['reportDayEnd'];
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$dateDayEndBlock[] = array(
			"VAR_DAYENDVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_DAYENDSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateDayEndBlock", $dateDayEndBlock);

	//dateMonthEndBlock
	$dateMonthEndBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['reportMonthEnd']) )
		{
			$sDefaultMonth = $_POST['reportMonthEnd'];
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$dateMonthEndBlock[] = array(
			"VAR_MONTHENDVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_MONTHENDTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_MONTHENDSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateMonthEndBlock", $dateMonthEndBlock);

	//dateYearEndBlock
	$dateYearEndBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['reportYearEnd']) )
		{
			$sDefaultYear = $_POST['reportYearEnd'];
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$dateYearEndBlock[] = array(
			"VAR_YEARENDVALUE" => $i,
			"VAR_YEARENDSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateYearEndBlock", $dateYearEndBlock);

	//VTSProductBlock
	$VTSProductBlock = array();
	for ($i = 0; $i < 7; $i++)
	{
		if ( isset($_POST['VTSProduct']) )
		{
			$sProductValue = $_POST['VTSProduct'];
		}
		else
		{
			$sProductValue = 'All';
		}

		switch ($i)
		{
			case 1:
				$iValue = "S005";
			break;
			case 2:
				$iValue = "S010";
			break;
			case 3:
				$iValue = "S020";
			break;
			case 4:
				$iValue = "S025";
			break;
			case 5:
				$iValue = "S050";
			break;
			case 6:
				$iValue = "S100";
			break;
			default: //case 0 is here
				$iValue = "All";
			break;
		}
		$VTSProductBlock[] = array(
			"VAR_VTSPRODUCTVALUE" => $iValue,
			"VAR_VTSPRODUCTSELECTED" => ( $iValue == $sProductValue)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "VTSProductBlock", $VTSProductBlock);

	//datePurchaseDayBlock
	$datePurchaseDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['purchaseID']) )
		{
			$sDefaultDay = $sPurchaseDateDay;
		}
		else
		{
			$sDefaultDay = date("d");
		}
		$datePurchaseDayBlock[] = array(
			"VAR_PURCHASEDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_PURCHASEDAYSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "datePurchaseDayBlock", $datePurchaseDayBlock);

	//datePurchaseMonthBlock
	$datePurchaseMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['purchaseID']) )
		{
			$sDefaultMonth = $sPurchaseDateMonth;
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$datePurchaseMonthBlock[] = array(
			"VAR_PURCHASEMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_PURCHASEMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_PURCHASEMONTHSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "datePurchaseMonthBlock", $datePurchaseMonthBlock);

	//datePurchaseYearBlock
	$datePurchaseYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['purchaseID']) )
		{
			$sDefaultYear = $sPurchaseDateYear;
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$datePurchaseYearBlock[] = array(
			"VAR_PURCHASEYEARVALUE" => $i,
			"VAR_PURCHASEYEARSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "datePurchaseYearBlock", $datePurchaseYearBlock);

	//inventoryListBlock
	$reportListBlock = array();
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
			"VAR_PURCHASEID" => $aMKiosList[$i]['ID'],
			"VAR_DATE" => $aMKiosList[$i]['Date'],
			"VAR_NOTES" => $aMKiosList[$i]['Notes'],
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

		$iS005 += $aMKiosList[$i]['S005'];
		$iS010 += $aMKiosList[$i]['S010'];
		$iS020 += $aMKiosList[$i]['S020'];
		$iS025 += $aMKiosList[$i]['S025'];
		$iS050 += $aMKiosList[$i]['S050'];
		$iS100 += $aMKiosList[$i]['S100'];
	}
	$cWebsite->buildBlock("content", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_S005TOTAL" => number_format($iS005, _NbOfDigitBehindComma_ ),
		"VAR_S010TOTAL" => number_format($iS010, _NbOfDigitBehindComma_ ),
		"VAR_S020TOTAL" => number_format($iS020, _NbOfDigitBehindComma_ ),
		"VAR_S025TOTAL" => number_format($iS025, _NbOfDigitBehindComma_ ),
		"VAR_S050TOTAL" => number_format($iS050, _NbOfDigitBehindComma_ ),
		"VAR_S100TOTAL" => number_format($iS100, _NbOfDigitBehindComma_ )
	));

	//if user click on save button, then we create a csv file and send it to browser, so user can save it.
	if (isset($_POST['save']) && $_POST['save'] == 'Save')
	{
		//prepare the data
		$aContent = array();
		$aContent[] = array("Tanggal", "Notes", "S005", "S010", "S020", "S025", "S050", "S100");
		foreach ($reportListBlock as $iKey => $aData)
		{
			$aContent[] = array($aData["VAR_DATE"], $aData["VAR_NOTES"], $aData["VAR_S005_SAVE"], $aData["VAR_S010_SAVE"], $aData["VAR_S020_SAVE"], $aData["VAR_S025_SAVE"], $aData["VAR_S050_SAVE"], $aData["VAR_S100_SAVE"]);
		}
		//generate the grandtotal
		$aContent[] = array("", "Grandtotal", $iS005, $iS010, $iS020, $iS025, $iS050, $iS100);
	
		/*
		Make sure script execution doesn't time out.
		Set maximum execution time in seconds (0 means no limit).
		*/
		set_time_limit(0);
		$cExport->exportToCSV($aContent); //save to file
		$cExport->output_file('mkiosPurchase-' . date("d-M-Y") . '.csv', 'text/plain'); //output the file for download

	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();

	//*** END PAGE RENDERING ****************************************************//

?>
