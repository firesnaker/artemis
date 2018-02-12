<?php
	/***************************************************************************
	* mkios/sales.php :: MKios Sales Page								*
	****************************************************************************
	* The sales page for mkios										*
	*															*
	* Version			: 0.1										*
	* Author			: FireSnakeR 									*
	* Created			: 2013-09-02 									*
	* Last modified	: 2014-06-14									*
	*															*
	* 			Copyright (c) 2013-2014 FireSnakeR						*
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
				$sFormattedNumber = number_format($iNumber, _NbOfDigitBehindComma_ );
			}

			return $sFormattedNumber;
		}
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			$sReportDateBegin = $_POST['reportYearBegin'] . "-" . $_POST['reportMonthBegin'] . "-" . $_POST['reportDayBegin'];
			$sReportDateEnd = $_POST['reportYearEnd'] . "-" . $_POST['reportMonthEnd'] . "-" . $_POST['reportDayEnd'];

			//prepare and update data to database
			if (isset($_POST["salesFinanceVerify"]) && $_POST["salesFinanceVerify"] == "Finance Verify")
			{
				$aSalesInsert = array(
					"ID" => $_POST["salesFinanceVerifyID"],
					"FinanceNotes" => $_POST["salesFinanceVerifyNotes"]
				);

				if ($aSalesInsert['ID'] > 0)
				{
					$iSalesID = $cMKios->VerifyFinance($aSalesInsert);
				}
			}

			//prepare and update data to database
			if (isset($_POST["salesAction"]) && $_POST["salesAction"] == "Rubah")
			{
				$aSalesInsert = array(
					"ID" => $_POST["salesEditID"],
					"TxPeriod" => $_POST["salesYear"] . "-" . $_POST["salesMonth"] . "-" . $_POST["salesDay"],
					"S005" => $_POST["salesS005"],
					"S010" => $_POST["salesS010"],
					"S020" => $_POST["salesS020"],
					"S025" => $_POST["salesS025"],
					"S050" => $_POST["salesS050"],
					"S100" => $_POST["salesS100"]
				);

				if ($aSalesInsert['ID'] > 0)
				{
					$iSalesID = $cMKios->Update($aSalesInsert);
				}
			}

			//load edit data from database
			if ( isset($_POST["salesID"]) && $_POST["salesID"] > 0)
			{
				$aSearchByFieldArray = array(
					"ID" => $_POST['salesID']
				);
				$aSalesDataToEdit = $cMKios->GetMKiosList($aSearchByFieldArray);

				//split the date into individual date
				list($sSalesDateYear, $sSalesDateMonth, $sSalesDateDay) = explode("-", $aSalesDataToEdit[0]['TxPeriod']);
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
			"TxPeriod" => "BETWEEN '" . $sReportDateBegin . "' AND '" . $sReportDateEnd . "'",
			"Product" => $_POST['VTSProduct'],
			"KodeWH" => ($_POST['KodeWH'] != 'All')?$_POST['KodeWH']:"%",
			"KodeSales" => ($_POST['KodeSales'] != 'All')?$_POST['KodeSales']:"%",
			"NamaCust" => ($_POST['NamaCust'] != 'All')?$_POST['NamaCust']:"%"
			//"sales.client_ID" => ($_POST['reportClient'])?$_POST['reportClient']:"",
			//"sales.employee_ID" => ($_POST['reportEmployee'])?$_POST['reportEmployee']:"",
			//"sales_detail.product_ID" => ($_POST['reportProduct'])?$_POST['reportProduct']:"",
		);
		//we inject the FinStatus only if its value is > -1
		if ($_POST['FinStatus'] >= 0)
		{
			$aSearchByFieldArray['FinanceStatus'] = $_POST['FinStatus'];
		}
		$aSortByFieldArray = array(
			"Date" => "ASC"
		);

		$aMKiosList = $cMKios->GetMKiosList($aSearchByFieldArray);
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/sales.htm"
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
		"VAR_FORMURL" => "mkios/sales.php"
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

	//KodeWHBlock
	$KodeWHBlock = array();
	//we get the data grouped by KodeWH
	$aKodeWH = $cMKios->GetKodeWHList();
	for ($i = 0; $i < count($aKodeWH); $i++)
	{
		if ( isset($_POST['KodeWH']) )
		{
			$sProductValue = $_POST['KodeWH'];
		}
		else
		{
			$sProductValue = 'All';
		}

		//create the All selection for the first line
		if ($i == 0)
		{
			$KodeWHBlock[] = array(
				"VAR_KODEWHVALUE" => 'All',
				"VAR_KODEWHSELECTED" => ( 'All' == $sProductValue)?"selected":""
			);
		}

		$iValue = $aKodeWH[$i]['KodeWH'];
		$KodeWHBlock[] = array(
			"VAR_KODEWHVALUE" => $iValue,
			"VAR_KODEWHSELECTED" => ( $iValue == $sProductValue)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "KodeWHBlock", $KodeWHBlock);

	//KodeSalesBlock
	$KodeSalesBlock = array();
	//we get the data grouped by KodeSales
	$aKodeSales = $cMKios->GetKodeSalesList();
	for ($i = 0; $i < count($aKodeSales); $i++)
	{
		if ( isset($_POST['KodeSales']) )
		{
			$sProductValue = $_POST['KodeSales'];
		}
		else
		{
			$sProductValue = 'All';
		}

		//create the All selection for the first line
		if ($i == 0)
		{
			$KodeSalesBlock[] = array(
				"VAR_KODESALESVALUE" => 'All',
				"VAR_KODESALESSELECTED" => ( 'All' == $sProductValue)?"selected":""
			);
		}

		$iValue = $aKodeSales[$i]['KodeSales'];
		$KodeSalesBlock[] = array(
			"VAR_KODESALESVALUE" => $iValue,
			"VAR_KODESALESSELECTED" => ( $iValue == $sProductValue)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "KodeSalesBlock", $KodeSalesBlock);

	//NamaCustBlock
	$NamaCustBlock = array();
	//we get the data grouped by NamaCust
	$aNamaCust = $cMKios->GetNamaCustList();
	for ($i = 0; $i < count($aNamaCust); $i++)
	{
		if ( isset($_POST['NamaCust']) )
		{
			$sProductValue = $_POST['NamaCust'];
		}
		else
		{
			$sProductValue = 'All';
		}

		//create the All selection for the first line
		if ($i == 0)
		{
			$NamaCustBlock[] = array(
				"VAR_NAMACUSTVALUE" => 'All',
				"VAR_NAMACUSTSELECTED" => ( 'All' == $sProductValue)?"selected":""
			);
		}

		$iValue = $aNamaCust[$i]['NamaCust'];
		$NamaCustBlock[] = array(
			"VAR_NAMACUSTVALUE" => $iValue,
			"VAR_NAMACUSTSELECTED" => ( $iValue == $sProductValue)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "NamaCustBlock", $NamaCustBlock);

	//FinStatusBlock
	if ( isset($_POST['FinStatus']) )
	{
		$sSelectedValue = $_POST['FinStatus'];
	}
	else
	{
		$sSelectedValue = -1;
	}
	$FinStatusBlock = array();
	for ($i = -1; $i < 2; $i++)
	{
		//create the All selection for the first line
		if ($i == -1)
		{
			$FinStatusBlock[] = array(
				"VAR_FINSTATUSVALUE" => $i,
				"VAR_FINSTATUSTEXT" => 'All',
				"VAR_FINSTATUSSELECTED" => ( $i == $sSelectedValue)?"selected":""
			);
		}
		else
		{
			$FinStatusBlock[] = array(
				"VAR_FINSTATUSVALUE" => $i,
				"VAR_FINSTATUSTEXT" => ($i == 1)?"Verified":"Not Verified",
				"VAR_FINSTATUSSELECTED" => ( $i == $sSelectedValue)?"selected":""
			);
		}
	}
	$cWebsite->buildBlock("content", "FinStatusBlock", $FinStatusBlock);

	//inventoryListBlock
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
		set_time_limit(0);
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
			"VAR_S100_SAVE" => $aMKiosList[$i]['S100'],
			"VAR_FINANCESTATUS" => ($aMKiosList[$i]['FinanceStatus'] == 1)?"Verified":"Not verified",
			"VAR_FINANCEVERIFYNOTES" => $aMKiosList[$i]['FinanceNotes']
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

	//dateSalesDayBlock
	$dateSalesDayBlock = array();
	for ($i = 0; $i < 31; $i++)
	{
		if ( isset($_POST['salesID']) )
		{
			$sDefaultDay = $sSalesDateDay;
		}
		else
		{
			$sDefaultDay = date("d");
		}

		$dateSalesDayBlock[] = array(
			"VAR_SALESDAYVALUE" => ($i+1 < 10)?'0' . ($i + 1):$i + 1,
			"VAR_SALESDAYSELECTED" => ( ($i+1) == $sDefaultDay)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateSalesDayBlock", $dateSalesDayBlock);

	//dateSalesMonthBlock
	$dateSalesMonthBlock = array();
	for ($i = 0; $i < 12; $i++)
	{
		if ( isset($_POST['salesID']) )
		{
			$sDefaultMonth = $sSalesDateMonth;
		}
		else
		{
			$sDefaultMonth = date("m");
		}
		$dateSalesMonthBlock[] = array(
			"VAR_SALESMONTHVALUE" => ( ($i+1) < 10)?"0" . ($i+1):$i+1,
			"VAR_SALESMONTHTEXT" => date("M", mktime(0,0,0,$i+1,1,2010)),
			"VAR_SALESMONTHSELECTED" => ( ($i+1) == $sDefaultMonth)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateSalesMonthBlock", $dateSalesMonthBlock);

	//dateSalesYearBlock
	$dateSalesYearBlock = array();
	for ($i = $iOldestYear; $i <= date("Y"); $i++)
	{
		if ( isset($_POST['salesID']) )
		{
			$sDefaultYear = $sSalesDateYear;
		}
		else
		{
			$sDefaultYear = date("Y");
		}
		$dateSalesYearBlock[] = array(
			"VAR_SALESYEARVALUE" => $i,
			"VAR_SALESYEARSELECTED" => ( $i == $sDefaultYear)?"selected":""
		);
	}
	$cWebsite->buildBlock("content", "dateSalesYearBlock", $dateSalesYearBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDSUBTOTAL" => number_format($iGrandSubtotal, _NbOfDigitBehindComma_ ),
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
		$aContent[] = array("KodeWH", "KodeSales", "CustomerGroup", "NamaCust", "TxPeriod", "KodeTerminal", "NoHP", "Subtotal", "S005", "S010", "S020", "S025", "S050", "S100", "FinanceStatus", "FinanceNotes");
		foreach ($reportListBlock as $iKey => $aData)
		{
			$aContent[] = array(
				$aData["VAR_KODEWH"], 
				$aData["VAR_KODESALES"], 
				$aData["VAR_CUSTOMERGROUP"], 
				$aData["VAR_NAMACUST"], 
				$aData["VAR_TXPERIOD"], 
				trim($aData["VAR_KODETERMINAL"]), 
				trim($aData["VAR_NOHP"]), 
				str_replace(",", "", $aData["VAR_SUBTOTAL"]),
				$aData["VAR_S005_SAVE"],
				$aData["VAR_S010_SAVE"],
				$aData["VAR_S020_SAVE"],
				$aData["VAR_S025_SAVE"],
				$aData["VAR_S050_SAVE"],
				$aData["VAR_S100_SAVE"],
				$aData["VAR_FINANCESTATUS"],
				$aData["VAR_FINANCEVERIFYNOTES"]
			);
		}
		//generate the grandtotal
		$aContent[] = array("", "", "", "", "", "", "Grandtotal", $iGrandSubtotal, $iS005, $iS010, $iS020, $iS025, $iS050, $iS100);
	
		/*
		Make sure script execution doesn't time out.
		Set maximum execution time in seconds (0 means no limit).
		*/
		set_time_limit(0);
		$cExport->exportToCSV($aContent); //save to file
		$cExport->output_file('mkiosSales-' . date("d-M-Y") . '.csv', 'text/plain'); //output the file for download

	}



	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>