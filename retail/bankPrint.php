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
	* retail/bankPrint.php :: Retail Bank Print Page						*
	****************************************************************************
	* The bank deposit printed page for retail							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ] 					*
	* Created			: 2012-02-10 									*
	* Last modified	: 2014-08-21									*
	* 															*
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
	include_once("dirConf.php");
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classBank.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$oBank = new Bank;
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
			if ( isset($_GET["depositDate"]) )
			{
				$aDepositSearchParam = array(
					"outlet_ID" => ' = "' . $_SESSION['outlet_ID'] . '"',
					"Date" => ' = "' . $_GET["depositDate"] . '"'
				);
				$aDepositList = $oBank->GetDepositList($aDepositSearchParam);

				$aSearchParam = array(
					"ORDER BY" => " NAME ASC "
				);
				$aBankList = $oBank->GetList($aSearchParam);
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "retail/bankPrint.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"VAR_DEPOSITDATE" => $sDate,
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_OUTLETID" => $_SESSION['outlet_ID'],
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));

	//depositListBlock
	$depositListBlock = array();
	$iGrandTotal = 0;
	for ($i = 0; $i < count($aDepositList); $i++)
	{
		$sBankName = '';
		for ($j = 0; $j < count($aBankList); $j++)
		{
			if ( $aBankList[$j]['ID'] == $aDepositList[$i]['bank_ID'] )
			{
				$sBankName = $aBankList[$j]['Name'];
			}
		}

		$iGrandTotal += $aDepositList[$i]['Price'];
		$depositListBlock[] = array(
			"VAR_COUNTER" => $i+1,
			"VAR_ROWBGCOLOR" => (($i % 2) == 1)?"#ffffff":"#cccccc",
			"VAR_LISTDEPOSITID" => $aDepositList[$i]['ID'],
			"VAR_LISTDEPOSITNOTES" => $aDepositList[$i]['Notes'],
			"VAR_LISTDEPOSITPRICE" => number_format( $aDepositList[$i]['Price'], _NbOfDigitBehindComma_ ) . (($aDepositList[$i]['salesPayment_ID'] > 0)?"*":""),
			"VAR_LISTDEPOSITBANK" => $sBankName,
		);
	}
	$cWebsite->buildBlock("site", "depositList", $depositListBlock);

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTAL" => number_format( $iGrandTotal, _NbOfDigitBehindComma_ )
	));

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
