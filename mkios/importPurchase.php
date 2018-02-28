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
	* mkios/importPurchase.php :: Import Page								*
	*********************************************************************
	* The purchase import page for mkios											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2014-01-24 										*
	* Last modified	: 2014-01-24										*
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
		if ( count($_FILES) > 0 )
		{
			//if ($_FILES['importfile']['type'] == 'text/csv' )
			//{
				$aDataRows = file($_FILES['importfile']['tmp_name']);
				$sCustomer = ""; //we set the initial value for customer name
				foreach ($aDataRows as $iLineNo => $sDataLine)
				{
					$sSeparator = ";";
					$aLineRow = explode($sSeparator, $sDataLine);

					//the first line contains the structure
					if ($iLineNo == 0)
					{
						//simply ignore it.
					}
					else
					{

						if ( trim($aLineRow[0]) != $sCustomer )
						{
							//now we insert to database
							$aMKiosData = array(
								'Notes' => "Retur " . $sMonth . " " . $sCustomer,
								'S005' => $S005,
								'S010' => $S010,
								'S020' => $S020,
								'S025' => $S025,
								'S050' => $S050,
								'S100' => $S100,
								'Date' => date("Y-m-d")
							);
							//we ignore the first line, because it will be empty
							if ($iLineNo > 1)
							{
								$cMKios->InsertPurchase($aMKiosData);
							}

							//we reset the quantity
							$S005 = 0;
							$S010 = 0;
							$S020 = 0;
							$S025 = 0;
							$S050 = 0;
							$S100 = 0;
						}

						$sCustomer = trim($aLineRow[0]);
						$sMonth = trim($aLineRow[3]);
						$sProduct = trim($aLineRow[1]);
						$sQuantity = trim($aLineRow[2]);
						$sQuantity = str_replace(".00", "", $sQuantity);
						$sQuantity = str_replace(",", "", $sQuantity);

						switch($sProduct)
						{
							case "S005":
								$S005 = $sQuantity;
							break;
							case "S010":
								$S010 = $sQuantity;
							break;
							case "S020":
								$S020 = $sQuantity;
							break;
							case "S025":
								$S025 = $sQuantity;
							break;
							case "S050":
								$S050 = $sQuantity;
							break;
							case "S100":
								$S100 = $sQuantity;
							break;
							default:
							break;
						}
					}
				}

				//at the end of the loop, we need to insert the last data.
				$aMKiosData = array(
					'Notes' => "Retur " . $sMonth . " " . $sCustomer,
					'S005' => $S005,
					'S010' => $S010,
					'S020' => $S020,
					'S025' => $S025,
					'S050' => $S050,
					'S100' => $S100,
					'Date' => date("Y-m-d")
				);
				$cMKios->InsertPurchase($aMKiosData);


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

		"VAR_FORM_ACTION" => "mkios/importPurchase.php",
		"VAR_MESSAGES" => $sMessages,
		"VAR_ERRORMESSAGES" => $sErrorMessages,
		"VAR_PAGENAME" => "Import Purchase"
		//page text
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_mkios");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_mkios");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
