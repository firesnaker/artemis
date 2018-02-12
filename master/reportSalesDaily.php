<?php
	/***************************************************************************
	* master/reportSalesDaily.php :: Master Index Page						*
	****************************************************************************
	* The daily report page for master									*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-08-08 									*
	* Last modified	: 2014-08-01									*
	*															*
	* 			Copyright (c) 2010-2014 FireSnakeR						*
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
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classExpenses.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cUser = new User($_SESSION['user_ID']);
	$cExpenses = new Expenses;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	$sPageName = "Report Sales Daily";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDate = $_POST['reportYear'] . "-" . $_POST['reportMonth'] . "-" . $_POST['reportDay'];
			$sBeginDate = $_POST['beginYear'] . "-" . $_POST['beginMonth'] . "-" . $_POST['beginDay'];
			$sEndDate = $_POST['endYear'] . "-" . $_POST['endMonth'] . "-" . $_POST['endDay'];
		}
		else
		{
			$sReportDate = date("Y-m-d");
			$sBeginDate = date("Y-m-d");
			$sEndDate = date("Y-m-d");
		}

		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
		$aSearchByFieldArray = array(
			"outlet_ID" => ($_POST['reportOutlet'])?$_POST['reportOutlet']:"",
			"employee_ID" => ($_POST['reportEmployee'])?$_POST['reportEmployee']:"",
			"client_ID" => ($_POST['reportClient'])?$_POST['reportClient']:"",
			"product_ID" => ($_POST['reportProduct'])?$_POST['reportProduct']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		$aSalesList = $cSales->GetSalesReport($aSearchByFieldArray);
		$aOutletList = $cOutlet->GetActiveOutletList();
		$aEmployeeList = $cEmployee->GetEmployeeList();
		$aClientList = $cClient->GetClientList();
		$aProductList = $cProduct->GetProductList();
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "master/reportSalesDaily.htm"
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
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Report"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_master");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_master");

	//beginDayBlock
	$beginDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['beginDay']) )
		{
			$sDefaultBeginDay = $_POST['beginDay'];
		}
		else
		{
			$sDefaultBeginDay = date("d");
		}
		$beginDayBlock[] = array(
			"VAR_BEGINDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_BEGINDAYSELECTED" => ( ($i+1) == $sDefaultBeginDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "beginDayBlock", $beginDayBlock);

	//beginMonthBlock
	$beginMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['beginMonth']) )
		{
			$sDefaultBeginMonth = $_POST['beginMonth'];
		}
		else
		{
			$sDefaultBeginMonth = date("m");
		}
		$beginMonthBlock[] = array(
			"VAR_BEGINMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_BEGINMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_BEGINMONTHSELECTED" => ( ($i+1) == $sDefaultBeginMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "beginMonthBlock", $beginMonthBlock);

	//beginYearBlock
	$beginYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['beginYear']) )
		{
			$sDefaultBeginYear = $_POST['beginYear'];
		}
		else
		{
			$sDefaultBeginYear = date("Y");
		}
		$beginYearBlock[] = array(
			"VAR_BEGINYEARVALUE" => $i,
			"VAR_BEGINYEARSELECTED" => ( $i == $sDefaultBeginYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "beginYearBlock", $beginYearBlock);

	//endDayBlock
	$endDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['endDay']) )
		{
			$sDefaultEndDay = $_POST['endDay'];
		}
		else
		{
			$sDefaultEndDay = date("d");
		}
		$endDayBlock[] = array(
			"VAR_ENDDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_ENDDAYSELECTED" => ( ($i+1) == $sDefaultEndDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "endDayBlock", $endDayBlock);

	//endMonthBlock
	$endMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['endMonth']) )
		{
			$sDefaultEndMonth = $_POST['endMonth'];
		}
		else
		{
			$sDefaultEndMonth = date("m");
		}
		$endMonthBlock[] = array(
			"VAR_ENDMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_ENDMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_ENDMONTHSELECTED" => ( ($i+1) == $sDefaultEndMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "endMonthBlock", $endMonthBlock);

	//endYearBlock
	$endYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['endYear']) )
		{
			$sDefaultEndYear = $_POST['endYear'];
		}
		else
		{
			$sDefaultEndYear = date("Y");
		}
		$endYearBlock[] = array(
			"VAR_ENDYEARVALUE" => $i,
			"VAR_ENDYEARSELECTED" => ( $i == $sDefaultEndYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "endYearBlock", $endYearBlock);

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$outletListBlock[] = array(
			"VAR_OUTLETID" => $aOutletList[$i]['ID'],
			"VAR_OUTLETNAME" => $aOutletList[$i]['name'],
			"VAR_OUTLETSELECTED" => ($aOutletList[$i]['ID'] == $_POST['reportOutlet'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);

	//inventoryListBlock
	$iGrandTotal = 0;
	$reportListBlock = array();
	for ($i = 0; $i < count($aSalesList); $i++)
	{
		$aOutletName = $cOutlet->GetOutletByID($aSalesList[$i]['outlet_ID']);
		$sOutletName = $aOutletName[0]['Name'];

		list($sYear, $sMonth, $sDay) = explode("-",$aSalesList[$i]['Date']);
		
		$iSubtotal = $aSalesList[$i]['Price'] * $aSalesList[$i]['Quantity'] * ( (100 - $aSalesList[$i]['Discount']) / 100);

		$reportListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SALESDATE" => date("d-M-Y", mktime(0, 0, 0, $sMonth, $sDay, $sYear) ),
			"VAR_SALESDATEDB" => $aSalesList[$i]['Date'],
			"VAR_OUTLETID" => $aSalesList[$i]['outlet_ID'],
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_TOTAL" => $iSubtotal,
			"VAR_CASH" => $i,
			"VAR_DEBIT" => $i,
			"VAR_TRANSFER" => $i
		);

		$iGrandTotal += $iSubtotal;
	}

	$reportListBlockSummary = array();
	for($i = 0; $i < count($reportListBlock); $i++)
	{
		$sDate = $reportListBlock[$i]['VAR_SALESDATE'];
		$sDateDB = $reportListBlock[$i]['VAR_SALESDATEDB'];
		$iOutletID = $reportListBlock[$i]['VAR_OUTLETID'];
		$sOutletName = $reportListBlock[$i]['VAR_OUTLETNAME'];

		if ($i == 0)
		{
			$reportListBlockSummary[] = array(
				"Date" => $sDate,
				"DateDB" => $sDateDB,
				"OutletID" => $iOutletID,
				"OutletName" => $sOutletName,
				"Total" => $reportListBlock[$i]['VAR_TOTAL']
			);
		}
		else
		{
			$bMatchFound = FALSE;
			for ($j = 0; $j < count($reportListBlockSummary); $j++)
			{
				if (
					$reportListBlockSummary[$j]["Date"] == $reportListBlock[$i]['VAR_SALESDATE']
					&&
					$reportListBlockSummary[$j]["OutletName"] == $reportListBlock[$i]['VAR_OUTLETNAME']
				)
				{
					$reportListBlockSummary[$j]["Total"] += $reportListBlock[$i]['VAR_TOTAL'];
					$bMatchFound = TRUE;
				}
			}
			if ($bMatchFound == FALSE)
			{
				$reportListBlockSummary[] = array(
					"Date" => $sDate,
					"DateDB" => $sDateDB,
					"OutletID" => $iOutletID,
					"OutletName" => $sOutletName,
					"Total" => $reportListBlock[$i]['VAR_TOTAL']
				);
			}
		}
	}

	$iGrandTotalSales = 0;
	$iGrandTotalExpenses = 0;
	$aReportSalesListBlock = array();
	for ($i = 0; $i < count($reportListBlockSummary); $i++)
	{
		$aExpensesSearchByFieldArray = array(
			"outlet_ID" => $reportListBlockSummary[$i]["OutletID"],
			"Date" => $reportListBlockSummary[$i]["DateDB"]
		);
		$aExpensesList = $cExpenses->GetExpensesList($aExpensesSearchByFieldArray);
		$iTotalExpenses = 0;
		for ($j = 0; $j < count($aExpensesList); $j++)
		{
			$iTotalExpenses += $aExpensesList[$j]["Price"];
		}
		$iGrandTotal -= $iTotalExpenses;
		$iGrandTotalExpenses += $iTotalExpenses;

		$aReportSalesListBlock[] = array(
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_COUNTER" => $i + 1,
			"VAR_SALESDATE" => $reportListBlockSummary[$i]["Date"],
			"VAR_OUTLETNAME" => $reportListBlockSummary[$i]["OutletName"],
			"VAR_TOTALSALES" => number_format($reportListBlockSummary[$i]["Total"], _NbOfDigitBehindComma_ ),
			"VAR_TOTALEXPENSES" => number_format($iTotalExpenses, _NbOfDigitBehindComma_ ),
			"VAR_SUBTOTAL" => number_format( ($reportListBlockSummary[$i]["Total"] - $iTotalExpenses), _NbOfDigitBehindComma_  ),
			"VAR_CASH" => $i,
			"VAR_DEBIT" => $i,
			"VAR_TRANSFER" => $i
		);
		$iGrandTotalSales += $reportListBlockSummary[$i]["Total"];
	}
	$cWebsite->buildBlock("content", "reportListBlock", $aReportSalesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => ($_POST['reportOutlet'])?$_POST['reportOutlet']:"0",
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear,
		
		"VAR_GRANDTOTALSALES" => number_format($iGrandTotalSales, _NbOfDigitBehindComma_ ),
		"VAR_GRANDTOTALEXPENSES" => number_format($iGrandTotalExpenses, _NbOfDigitBehindComma_ ),
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>