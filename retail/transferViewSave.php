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
	* retail/transferViewSave.php :: Retail Transfer View Save Page					*
	****************************************************************************
	* The transfer view save page for retail								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2015-02-10 									*
	* Last modified	: 2015-02-10									*
	* 															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ************************************************//
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
	include_once($libPath . "/classTransfer.php");
	include_once($libPath . "/classExport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cTransfer = new Transfer;
	$cExport = new Export;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Transfer View";
	$sViewType = "all";
	$iOldestYear = _OldestYear_; //this variable is for setting the minimum year the program is available. Used in year selection for search and sort.
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		$aPostData = array(
			"outlet_ID" => $_SESSION['outlet_ID'],
			"view_type" => "all",
			"dateBegin" => date("Y-m-d"),
			"dateEnd" => date("Y-m-d")
		);
		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			$aPostData = array(
				"outlet_ID" => $_SESSION['outlet_ID'],
				"view_type" => $_GET["transferViewType"],
				"dateBegin" => $_GET["transferYearBegin"] . "-" . $_GET["transferMonthBegin"] . "-" . $_GET["transferDayBegin"],
				"dateEnd" => $_GET["transferYearEnd"] . "-" . $_GET["transferMonthEnd"] . "-" . $_GET["transferDayEnd"] 
			);
			$sViewType = $_GET["transferViewType"];
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		$aTransferData = $cTransfer->GetTransferListWithDetailByOutletID($aPostData);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "retail/transferView.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$iProductGrandTotal = 0;
	//transferList Block
	//transferList Block
	for ($j = 0; $j < count($aTransferData); $j++)
	{
		$cWebsite->template->parse("transferDetailLists", "");
		//transferDetailList Block
		for ($k = 0; $k < count($aTransferData[$j]["Detail"]); $k++)
		{
			$cWebsite->template->set_var(array(
				"VAR_PRODUCT_NAME" => $aTransferData[$j]["Detail"][$k]['productName'],
				"VAR_QUANTITY" => number_format($aTransferData[$j]["Detail"][$k]['quantity'], _NbOfDigitBehindComma_ ),
				"VAR_SN" => ($aTransferData[$j]["Detail"][$k]['SnStart'] . (($aTransferData[$j]["Detail"][$k]['SnEnd'] == "")?"":("-" . $aTransferData[$j]["Detail"][$k]['SnEnd']) ) ),
			));

			$cWebsite->template->parse("transferDetailLists", "transferDetailList", TRUE);
		}

		list($year, $month, $day) = explode("-", $aTransferData[$j]['Date']);

		$cWebsite->template->set_var(array(
			"VAR_COUNT" => $j+1,
			"VAR_TRANSFER_ID" => $aTransferData[$j]['ID'],
			"VAR_DATE" => date("d-M-Y", mktime(0,0,0, $month, $day, $year)),
			"VAR_NOTES" => $aTransferData[$j]['Notes'],
			"VAR_FROM_OR_TO" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'])?"Keluar Ke":"Masuk Dari",
			"VAR_CSS_BOLD" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'])?"":"class='bold'",
			"VAR_OUTLET_NAME" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'])?$aTransferData[$j]['To_outlet_name']:$aTransferData[$j]['From_outlet_name'],
			"VAR_STATUS" => ($aTransferData[$j]['Status'] == 0)?"belum diverifikasi":"sudah diverifikasi",
			"VAR_DISABLE_EDIT" => ($aTransferData[$j]['To_outlet_ID'] == $_SESSION['outlet_ID'] ||$aTransferData[$j]['Status'] == 1)?"disabled='1'":"",
			"VAR_DISABLE_VERIFY" => ($aTransferData[$j]['From_outlet_ID'] == $_SESSION['outlet_ID'] || $aTransferData[$j]['Status'] == 1)?"disabled='1'":""
		));

		$cWebsite->template->parse("transferLists", "transferList", TRUE);
	}

	//prepare the data
	$aContent = array();
	$aContent[] = array("Date", "Notes", "Transfer Dari", "Transfer Ke", "Status", "Barang dan Jumlah");

	for ($i = 0; $i < count($aTransferData); $i++)
	{
		$sProductAndQuantity = "";
		$sProductAndQuantityVirgin = "";
		for ($k = 0; $k < count($aTransferData[$i]["Detail"]); $k++)
		{
			$sProductAndQuantity .= '"' . $aTransferData[$i]["Detail"][$k]['productName'] . '";"' . number_format($aTransferData[$i]["Detail"][$k]['quantity'], _NbOfDigitBehindComma_ ) . '";';
			$sProductAndQuantityVirgin .= '"' . $aTransferData[$i]["Detail"][$k]['productName'] . '";"' . number_format($aTransferData[$i]["Detail"][$k]['quantity'], 0 ) . '";';
		}

		//remove the extra ";" at the end of $sProductAndQuantity
		if ($sProductAndQuantity != "")
		{
			$sProductAndQuantity = substr($sProductAndQuantity, 0, strlen($sProductAndQuantity)-1);
		}

		$aContent[] = array($aTransferData[$i]["Date"], $aTransferData[$i]["Notes"], $aTransferData[$i]["From_outlet_name"], $aTransferData[$i]["To_outlet_name"], (($aTransferData[$i]["Status"] == 0)?"belum diverifikasi":"sudah diverifikasi"), $sProductAndQuantityVirgin);
	}

	/*
	Make sure script execution doesn't time out.
	Set maximum execution time in seconds (0 means no limit).
	*/
	set_time_limit(0);
	$cExport->exportToCSV($aContent); //save to file
	$cExport->output_file('transferViewSave-' . $aPostData['dateBegin'] . '-' . $aPostData['dateEnd'] . '.csv', 'text/plain'); //output the file for download

	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
