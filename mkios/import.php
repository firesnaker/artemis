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
	* retail/import.php :: Import Page									*
	****************************************************************************
	* The import page for admin										*
	*															*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-07-22 										*
	* Last modified	: 2013-07-24										*
	* 																	*
	*********************************************************************/

	//*** BEGIN INITIALIZATION ********************************************************//
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include_once("dirConf.php");
		include_once($rootPath . "config.php");
		include_once($libPath . "/classWebsite.php");
		//include_once($libPath . "/classUser.php");
		include_once($libPath . "/classMKios.php");
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
		//+++ END variable declaration and initialization +++++++++++++++++++//

		//+++ BEGIN class initialization ++++++++++++++++++++++++++++++++++++//
		$cWebsite = new Website;
		//$cUser = new User($_SESSION['user_ID']);
		$cMKios = new MKios;
		//+++ END class initialization ++++++++++++++++++++++++++++++++++++++//
	//*** END INITIALIZATION ****************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		echo "MKios Import disabled";
		if ( count($_FILES) > 0  && 1 == 2)
		{
			//if ($_FILES['importfile']['type'] == 'text/csv' )
			//{
				$aDataRows = file($_FILES['importfile']['tmp_name']);
				$aStructure = array();
				foreach ($aDataRows as $iLineNo => $sDataLine)
				{
					set_time_limit(0);
					$sSeparator = ";";
					$aLineRow = explode($sSeparator, $sDataLine);

					//the first line contains the structure
					if ($iLineNo == 0)
					{
						//simply ignore it.
						//we define the structure array
						foreach ($aLineRow as $key => $value)
						{
							$aStructure[trim($value)] = trim($key);
						}
					}
					else
					{
						$sSubtotal = str_replace(',','', $aLineRow[$aStructure['SubTotal']]);
						$sSubtotal = str_replace('.','', $sSubtotal);

						//we need to replace all indo month with english month
						$sDate = str_replace('Agu','Aug', $aLineRow[$aStructure['TxPeriod']]);
						$sDate = str_replace('Mei','May', $sDate);
						$sDate = str_replace('Okt','Oct', $sDate);
						$sDate = str_replace('Des','Dec', $sDate);

						//there was an error in previous import where the 
						//location of S005 to S100 turns out to be a variable
						//so we must re-import again. To make that automatic
						//we search for the same data and update instead of insert
						$aSearchData = array(
							'KodeWH' => trim($aLineRow[$aStructure['KodeWH']]),
							'KodeSales' => trim($aLineRow[$aStructure['KodeSales']]),
							//'CustomerGroup' => trim($aLineRow[$aStructure['CustomerGroup']]),
							'NamaCust' => trim($aLineRow[$aStructure['NamaCust']]),
							'TxPeriod' => "='" . date( 'Y-m-d', strtotime(trim($sDate)) ) . "'",
							'KodeTerminal' => trim($aLineRow[$aStructure['KodeTerminal']]),
							'NoHP' => trim($aLineRow[$aStructure['NoHP']]),
							'Subtotal' => trim($sSubtotal) 
						); 
						$aSearchResult = $cMKios->GetMKiosList($aSearchData);

						//now we insert to database
						$aMKiosData = array(
							'KodeWH' => trim($aLineRow[$aStructure['KodeWH']]),
							'KodeSales' => trim($aLineRow[$aStructure['KodeSales']]),
							'CustomerGroup' => trim($aLineRow[$aStructure['CustomerGroup']]),
							'NamaCust' => trim($aLineRow[$aStructure['NamaCust']]),
							'TxPeriod' => date( 'Y-m-d', strtotime(trim($sDate)) ),
							'KodeTerminal' => trim($aLineRow[$aStructure['KodeTerminal']]),
							'NoHP' => trim($aLineRow[$aStructure['NoHP']]),
							'Subtotal' => trim($sSubtotal),
							'S005' => (isset($aStructure['S005']) && $aStructure['S005'] > 0)?trim($aLineRow[$aStructure['S005']]):0,
							'S010' => (isset($aStructure['S010']) && $aStructure['S010'] > 0)?trim($aLineRow[$aStructure['S010']]):0,
							'S020' => (isset($aStructure['S020']) && $aStructure['S020'] > 0)?trim($aLineRow[$aStructure['S020']]):0,
							'S025' => (isset($aStructure['S025']) && $aStructure['S025'] > 0)?trim($aLineRow[$aStructure['S025']]):0,
							'S050' => (isset($aStructure['S050']) && $aStructure['S050'] > 0)?trim($aLineRow[$aStructure['S050']]):0,
							'S100' => (isset($aStructure['S100']) && $aStructure['S100'] > 0)?trim($aLineRow[$aStructure['S100']]):0,
							'TxPeriodText' => trim($aLineRow[$aStructure['TxPeriod']]),
							'SubtotalText' => trim($aLineRow[$aStructure['SubTotal']])
						);

						if (count($aSearchResult) > 0 )
						{
							$aMKiosData['ID'] = $aSearchResult[0]['ID'];
							//$cMKios->Update($aMKiosData);
						}
						else
						{
							//$cMKios->Insert($aMKiosData);
						}
						echo "MKios Import disabled";
					}
				}
				$sMessages = ( count($aDataRows) - 1 ) . " data imported";
			//}
			//else
			//{
			//	$sErrorMessages = "File must be in .csv format";
			//}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "mkios/import.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		"VAR_FORM_ACTION" => "mkios/import.php",
		"VAR_MESSAGES" => $sMessages,
		"VAR_ERRORMESSAGES" => $sErrorMessages,
		"VAR_PAGENAME" => "Import Sales"
		//page text
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
