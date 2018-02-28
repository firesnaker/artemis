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
	* mkios/reportCustomer.php :: Report Customer Page					*
	****************************************************************************
	* The report customer page for mkios								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2014-01-14 									*
	* Last modified	: 2014-08-02									*
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
				$sFormattedNumber = '<span style="color:red">' . number_format($iNumber, _NbOfDigitBehindComma_ ) . '</span>';
			}
			else
			{
				$sFormattedNumber = number_format($iNumber, _NbOfDigitBehindComma_ );
			}
			if ($sFormattedNumber == '-')
			{
				return '-';
			}
			else
			{
				return $sFormattedNumber;
			}
		}
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDateBegin = $_POST['reportYearBegin'] . "-" . $_POST['reportMonthBegin'] . "-01";
			$sReportDateEnd = $_POST['reportYearEnd'] . "-" . $_POST['reportMonthEnd'] . "-" . date('t', mktime(0,0,0, $_POST['reportMonthBegin'], 1, $_POST['reportYearBegin']));

		}
		else
		{
			$sReportDateBegin = date("Y-m-d");
			$sReportDateEnd = date("Y-m-t");
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//split the reportdateBegin to individual data
		list($sReportDateBeginYear, $sReportDateBeginMonth, $sReportDateBeginDay) = explode("-", $sReportDateBegin);
		//split the reportdateEnd to individual data
		list($sReportDateEndYear, $sReportDateEndMonth, $sReportDateEndDay) = explode("-", $sReportDateEnd);

		$aSearchByFieldArray = array(
			"beginStamp" => mktime(0,0,0, $sReportDateBeginMonth, 1, $sReportDateBeginYear),
			"endStamp" => mktime(0,0,0, $sReportDateEndMonth, date('t', mktime(0,0,0, $sReportDateBeginMonth, 1, $sReportDateBeginYear)), $sReportDateEndYear)
		);
		$aSortByFieldArray = array(
			"Date" => "ASC"
		);
		if ( count($_POST) > 0 || count($_GET) > 0 )
		{
			$aMKiosList = $cMKios->GetMKiosListCustomerSubtotalOnly($aSearchByFieldArray);
			//the return is as follow:
			//$array['NamaCust']['Month'] = total
		}
		else
		{
			$aMKiosList = array();
		}

	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/reportCustomer.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => "Report Customer Monthly",

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"VAR_REPORTYEARBEGIN" => $sReportDateBeginYear,
		"VAR_REPORTMONTHBEGIN" => $sReportDateBeginMonth,
		"VAR_REPORTDAYBEGIN" => $sReportDateBeginDay,
		"VAR_REPORTYEAREND" => $sReportDateEndYear,
		"VAR_REPORTMONTHEND" => $sReportDateEndMonth,
		"VAR_REPORTDAYEND" => $sReportDateEndDay,
		"VAR_SALESACTION" => "Rubah",
		"VAR_SALESEDITID" => (isset($aSalesDataToEdit))?$aSalesDataToEdit[0]['ID']:"",
		"VAR_SALESEDITS005" => (isset($aSalesDataToEdit))?$aSalesDataToEdit[0]['S005']:"",
		"VAR_SALESEDITS010" => (isset($aSalesDataToEdit))?$aSalesDataToEdit[0]['S010']:"",
		"VAR_SALESEDITS020" => (isset($aSalesDataToEdit))?$aSalesDataToEdit[0]['S020']:"",
		"VAR_SALESEDITS025" => (isset($aSalesDataToEdit))?$aSalesDataToEdit[0]['S025']:"",
		"VAR_SALESEDITS050" => (isset($aSalesDataToEdit))?$aSalesDataToEdit[0]['S050']:"",
		"VAR_SALESEDITS100" => (isset($aSalesDataToEdit))?$aSalesDataToEdit[0]['S100']:"",
		"VAR_FORM_EDIT_DISABLED" => (strtoupper($_SESSION['user_Name']) != "ADMIN")?"disabled":"", //disable edit mode to admin login only
		"VAR_FORM_FINANCEVERIFY_DISABLED" => (strtoupper($_SESSION['user_Name']) != "ADMIN")?"disabled":"", //disable finance verify  mode to admin login only
		"VAR_FORMURL" => "mkios/reportCustomer.php"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");

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

	//inventoryListBlock
	$reportListBlock = array();
	$iGrandSubtotal = 0;
	$i = 1;
	foreach ($aMKiosList as $sOutletName => $data)
	{
		$iTotal = 0;
		$sMonthlySummary = "";
		$sMonthlySummarySave = "";
		foreach ($data as $sMonthYear => $iMonthTotal)
		{
			list($iYear, $iMonth, $iDay) = explode("-", $sMonthYear);
			$sMonthYear = date( "M Y", mktime( 0,0,0, $iMonth, $iDay, $iYear) );

			$iMonthTotal = str_replace(".00", "", $iMonthTotal);

			$sMonthlySummary .= "
			<div style=\"float:left;padding:1em;\">
				" . $sMonthYear . " <br />
				" . formatNumber($iMonthTotal) . "
			</div>
			";

			$sMonthlySummarySave .= $sMonthYear. ";" . $iMonthTotal . ";";

			$iTotal += $iMonthTotal;
		}

		//we need to create the monthly summary and the 
		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#cccccc":"#ffffff",
			"VAR_COUNTER" => $i,
			"VAR_NAMACUST" => $sOutletName,
			"VAR_MONTHLYSUMMARY" => $sMonthlySummary,
			"VAR_MONTHLYSUMMARYSAVE" => $sMonthlySummarySave, //used in save file
			"VAR_TOTALNONFORMAT" => $iTotal, //used in save file
			"VAR_TOTAL" => formatNumber($iTotal),
		);

		$iGrandSubtotal += $iTotal;

		$i++;
	}
	$cWebsite->buildBlock("content", "reportListBlock", $reportListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDSUBTOTAL" => number_format($iGrandSubtotal, _NbOfDigitBehindComma_ )
	));

	//if user click on save button, then we create a csv file and send it to browser, so user can save it.
	if (isset($_POST['save']) && $_POST['save'] == 'Save')
	{
		//prepare the data
		$aContent = array();
		$aContent[] = array("NamaCust", "Total", "MonthlySummary");
		foreach ($reportListBlock as $iKey => $aData)
		{
			$aContent[] = array(
				$aData["VAR_NAMACUST"], 
				$aData["VAR_TOTALNONFORMAT"], 
				$aData["VAR_MONTHLYSUMMARYSAVE"]
			);
		}
		//generate the grandtotal
		$aContent[] = array("Grandtotal", $iGrandSubtotal);
	
		/*
		Make sure script execution doesn't time out.
		Set maximum execution time in seconds (0 means no limit).
		*/
		set_time_limit(0);
		$cExport->exportToCSV($aContent); //save to file
		$cExport->output_file('mkiosCustomerReport-' . date("d-M-Y") . '.csv', 'text/plain'); //output the file for download

	}



	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
