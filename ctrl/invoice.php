<?php
	/***************************************************************************
	* ctrl/invoice.php :: Invoice Controller Page						*
	****************************************************************************
	* The invoice controller page										*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2015-02-02 									*
	* Last modified	: 2015-02-02									*
	*															*
	* 			Copyright (c) 2015 FireSnakeR							*
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