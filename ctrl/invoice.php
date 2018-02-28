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
	* ctrl/invoice.php :: Invoice Controller Page						*
	****************************************************************************
	* The invoice controller page										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2015-02-02 									*
	* Last modified	: 2015-02-02									*
	*															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classMKios.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cMKios = new MKios;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
	}
	elseif ( isset($_GET) && count($_GET) > 0)
	{
		if (isset($_GET['id']) ) //only get one data
		{
		}
		elseif (isset($_GET['sub']))
		{
			switch ($_GET['sub'])
			{
				case "account_receivable_mkios":
					$date = $_GET['date'];
					$aResult = array();

					$result_kodesales = $cMKios->GetKodeSalesList();
					foreach ($result_kodesales as $kodesales)
					{
						if ($kodesales['KodeSales'] != "")
						{
							$search_by = array(
								"KodeSales" => $kodesales['KodeSales'],
								"TxPeriod" => " = '". $date ."'"
							);
							$result_mkios = $cMKios->GetMKiosList($search_by);
	
							$total = 0;
							$array_id = array();
							foreach ($result_mkios as $mkios)
							{
								$array_id[] = $mkios['ID'];
								$total += $mkios['Subtotal'];
							}
	
							$total_payment = 0;
							if ( $total > 0 )
							{
								foreach ($array_id AS $ids)
								{
									$param = array(
										"mkios_ID" => " = '" . $ids . "'"
									);
									$result_payment = $cMKios->ListMKiosPayment($param);
									
									foreach ($result_payment as $payment)
									{
										$total_payment += $payment['Amount'];
									}
								}
							}
							
							if ($total != $total_payment )
							{
								$aResult[] = array(
									"ID" => 0,
									"date" => $date,
									"subtotal" => number_format($total, 2),
									"payment" => number_format($total_payment, 2),
									"kodesales" => $kodesales['KodeSales']
								);
							}
						}
					}

					$result['data'] = $aResult;
				break;
				default:
				break;
			}
		}
		else //get everything
		{
		}
	}
	else
	{
		//ok, something is definitely wrong here
		$result = "unknown error";
	}
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//
	echo json_encode($result);
	//*** END PAGE RENDERING *************************************************//
?>
