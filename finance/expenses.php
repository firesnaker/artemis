<?php
	/********************************************************************
	* finance/expenses.php :: Admin Index Page								*
	*********************************************************************
	* The expenses page for finance											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2012-05-03 										*
	* Last modified	: 2012-05-05										*
	* 																	*
	* 				Copyright (c) 2012 FireSnakeR						*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($libPath . "/classWebsite.php");
		include_once($libPath . "/classSales.php");
		include_once($libPath . "/classEmployee.php");
		include_once($libPath . "/classClient.php");
		include_once($libPath . "/classProduct.php");
		include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classUser.php");
		include_once($libPath . "/classExpenses.php");

		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//

		//+++ BEGIN session initialization ++++++++++++++++++++++++++++++++++//
		session_start();

		if ( count($_SESSION) > 0 && isset($_SESSION['user_ID']) && $_SESSION['user_ID'] > 0 
		  && ($_SESSION['user_Name'] == "admin" || strtolower($_SESSION['user_Name']) == "finance" || $_SESSION['user_IsFinance'] == 1) )
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
		$cSales = new Sales;
		$cEmployee = new Employee;
		$cClient = new Client;
		$cProduct = new Product;
		$cOutlet = new Outlet;
		$cUser = new User($_SESSION['user_ID']);
		$cExpenses = new Expenses;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDate = $_POST['reportYear'] . "-" . $_POST['reportMonth'] . "-" . $_POST['reportDay'];
			$sBeginDate = $_POST['beginYear'] . "-" . $_POST['beginMonth'] . "-" . $_POST['beginDay'];
			$sEndDate = $_POST['endYear'] . "-" . $_POST['endMonth'] . "-" . $_POST['endDay'];
			
			//process verify
			if ( isset($_POST['expensesVerifySubmit']) && $_POST['expensesVerifySubmit'] == 'VERIFY' )
			{
				foreach ($_POST as $key => $value)
				{
					if (substr_count($key, "expensesVerifyID") > 0 )
					{
						//get the counter
						list($key1, $key2) = explode('_', $key);

						$aVerify = array(
							"ID" => $_POST["expensesVerifyID_" . $key2],
							"Notes" => $_POST["expensesVerifyFinanceNotes_" . $key2],
						);

						if ($aVerify["Notes"] != "" && $aVerify["ID"] > 0)
						{
							$cExpenses->Verify($aVerify["ID"], $aVerify["Notes"]);
						}
					}
				}
			}
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
			"expenses_category_ID" => ($_POST['reportCategory'])?$_POST['reportCategory']:"",
			"Date" => "BETWEEN '" . $sBeginDate . "' AND '" . $sEndDate . "'"
		);

		if ($_SESSION['user_IsFinance'] == 1)
		{
			$aOutletList = $cOutlet->GetActiveOutletListByFinanceArea($_SESSION['user_ID']);

			$aSearchByFieldArray['AllOutlet'] = $aOutletList;
			$aExpensesList = $cExpenses->GetExpensesReportByFinanceArea($aSearchByFieldArray, array(), array());
		}
		else
		{
			$aOutletList = $cOutlet->GetActiveOutletList();
			$aExpensesList = $cExpenses->GetExpensesReport($aSearchByFieldArray, array(), array());
		}
		$aCategoryList = $cExpenses->GetExpensesCategoryList(array());
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "finance/expenses.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_TODAYDATE" => date("d-M-Y"),
		"TEXT_REPORT" => "Report"
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_finance");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_finance");

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

	//categoryListBlock
	$categoryListBlock = array();
	for ($i = 0; $i < count($aCategoryList); $i++)
	{
		$categoryListBlock[] = array(
			"VAR_CATEGORYID" => $aCategoryList[$i]['ID'],
			"VAR_CATEGORYNAME" => $aCategoryList[$i]['Name'],
			"VAR_CATEGORYSELECTED" => ($aCategoryList[$i]['ID'] == $_POST['reportCategory'])?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "categoryListBlock", $categoryListBlock);

	//expensesListBlock
	$expensesListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aExpensesList); $i++)
	{
		list($iYear, $iMonth, $iDay) = explode("-", $aExpensesList[$i]['Date']);
		$iGrandTotal += $aExpensesList[$i]['Price'];

		$aOutletData = $cOutlet->GetOutletByID($aExpensesList[$i]['outlet_ID']);
		$sOutletName = $aOutletData[0]["Name"];

		$aCategoryData = $cExpenses->LoadExpensesCategory($aExpensesList[$i]['expenses_category_ID']);
		$sCategoryName = $aCategoryData[0]["Name"];

		$expensesListBlock[] = array(
			"VAR_COUNTERROW" => $i,
			"VAR_COUNTER" => $i+1,
			"VAR_EXPENSESDATE" => date("d-M-Y", mktime(0,0,0, $iMonth, $iDay, $iYear)),
			"VAR_OUTLETNAME" => $sOutletName,
			"VAR_CATEGORYNAME" => ($sCategoryName == '')?'-':$sCategoryName,
			"VAR_EXPENSESNOTES" => $aExpensesList[$i]['Name'],
			"VAR_EXPENSESID" => $aExpensesList[$i]['ID'],
			"VAR_TOTALEXPENSES" => number_format($aExpensesList[$i]['Price'], _NbOfDigitBehindComma_ ),
			"VAR_EXPENSESFINANCENOTES" => $aExpensesList[$i]['FinanceNotes'],
			"VAR_VERIFYDISABLED" => ($aExpensesList[$i]['Status'] == 1)?"disabled='1'":""
		);
	}
	$cWebsite->buildBlock("content", "reportListBlock", $expensesListBlock);

	$cWebsite->template->set_var(array(
		"VAR_REPORTOUTLET" => ($_POST['reportOutlet'])?$_POST['reportOutlet']:"0",
		"VAR_BEGINDAY" => $sDefaultBeginDay,
		"VAR_BEGINMONTH" => $sDefaultBeginMonth,
		"VAR_BEGINYEAR" => $sDefaultBeginYear,
		"VAR_ENDDAY" => $sDefaultEndDay,
		"VAR_ENDMONTH" => $sDefaultEndMonth,
		"VAR_ENDYEAR" => $sDefaultEndYear,

		"VAR_GRANDTOTALEXPENSES" => number_format($iGrandTotal, _NbOfDigitBehindComma_)		
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>