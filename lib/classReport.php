<?php
	/********************************************************************
	* lib/classUser.php :: USER CLASS									*
	*********************************************************************
	* All related user function											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2007-09-30 										*
	* Last modified	: 2014-07-29										*
	* 																	*
	* 				Copyright (c) 2007 FireSnakeR						*
	*********************************************************************/

	if ( !class_exists('Report') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classSales.php");
		include_once($libPath . "/classExpenses.php");
		include_once($libPath . "/classDeposit.php");
		include_once($libPath . "/classPurchase.php");
		include_once($libPath . "/classInventory.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Report extends Database
		{
			//*** BEGIN FUNCTION LIST ***********************************//
			// getDailySummary()
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Report()
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					//do nothing
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function GetDailySummary()
			{
				$sToday = date("Y-m-d");
				/*** This function gets the data of all outlet for today ***/
				$aResult = array();

				//get outlet data, because we are going to iterate all outlet
				$cOutlet = new Outlet;
				$aOutlet = $cOutlet->GetActiveOutletList();
				$iGrandTotalSales = 0;
				$iGrandTotalExpenses = 0;
				$iGrandTotalDeposit = 0;
				for ($i = 0; $i < count($aOutlet); $i++)
				{
					//get sales data for the outlet for today
					$aSearchSalesBy = array(
						"outlet_ID" => $aOutlet[$i]['ID'], 
						"Date" => "BETWEEN '" .  $sToday . "' AND '" . $sToday . "'"
					);
					$cSales = new Sales;
					$aSales = $cSales->GetSalesReport($aSearchSalesBy);
					$iTotalSales = 0;
					for ($j = 0; $j < count($aSales); $j++)
					{
						$iTotalSales += $aSales[$j]['Price'] * $aSales[$j]['Quantity'] * ( (100 - $aSales[$j]['Discount']) / 100 );
					}

					//get expenses data
					$aExpensesSearchBy = array(
						"outlet_ID" => $aOutlet[$i]['ID'],
						"Date" => $sToday
					);
					$cExpenses = new Expenses;
					$aExpenses = $cExpenses->GetExpensesList($aExpensesSearchBy);
					$iTotalExpenses = 0;
					for ($j = 0; $j < count($aExpenses); $j++)
					{
						$iTotalExpenses += $aExpenses[$j]['Price'];
					}
					
					//get deposit data
					$aDepositSearchBy = array(
						"outlet_ID" => $aOutlet[$i]['ID'],
						"Date" => $sToday
					);
					$cDeposit = new Deposit;
					$aDeposit = $cDeposit->GetDepositList($aDepositSearchBy);
					$iTotalDeposit = 0;
					for ($j = 0; $j < count($aDeposit); $j++)
					{
						$iTotalDeposit += $aDeposit[$j]['Price'];
					}
					
					//save to result array
					list($year, $month, $day) = explode("-", $sToday);
					$aResult[] = array(
						"Date" => date("d-M-Y", mktime(0,0,0, $month, $day, $year)),
						"OutletName" => $aOutlet[$i]['name'],
						"SalesTotal" => number_format($iTotalSales, _NbOfDigitBehindComma_ ),
						"ExpensesTotal" => number_format($iTotalExpenses, _NbOfDigitBehindComma_ ),
						"DepositTotal" => number_format($iTotalDeposit, _NbOfDigitBehindComma_ ),
					);
					$iGrandTotalSales += $iTotalSales;
					$iGrandTotalExpenses += $iTotalExpenses;
					$iGrandTotalDeposit += $iTotalDeposit;
				}

				//add last row containing grandtotal
				$aResult[] = array(
					"Date" => "",
					"OutletName" => "GRANDTOTAL",
					"SalesTotal" => number_format($iGrandTotalSales, _NbOfDigitBehindComma_ ),
					"ExpensesTotal" => number_format($iGrandTotalExpenses, _NbOfDigitBehindComma_ ),
					"DepositTotal" => number_format($iGrandTotalDeposit, _NbOfDigitBehindComma_ ),
				); 

				return $aResult;
			}

			function GetTotalPurchase($aData)
			{
				$iTotalPurchase = 0;

				//get sales data for the outlet for today
				$cPurchase = new Purchase;
				$aPurchase = $cPurchase->GetPurchaseReport($aData);
				$iTotalPurchase = 0;
				for ($j = 0; $j < count($aPurchase); $j++)
				{
					$iTotalPurchase += ( $aPurchase[$j]['Price'] * $aPurchase[$j]['Quantity'] );
				}

				return $iTotalPurchase;
			}

			function GetTotalSales($aData)
			{
				$iTotalSales = 0;

				//get sales data for the outlet for today
				$cSales = new Sales;
				$aSales = $cSales->GetSalesReport($aData);
				$iTotalSales = 0;
				for ($j = 0; $j < count($aSales); $j++)
				{
					$iTotalSales += $aSales[$j]['Price'] * $aSales[$j]['Quantity'] * ( (100 - $aSales[$j]['Discount']) / 100 );
				}

				return $iTotalSales;
			}

			function GetTotalStock($aData)
			{//redundant, possibly not used
				$iTotalStock = 0;

				//get sales data for the outlet for today
				$cInventory = new Inventory;
				$aInventory = $cInventory->GetInventoryReport($aData);

				for ($i=0; $i < count($aInventory); $i++)
				{
					$iTotalStock += $aInventory[$i]["Quantity"];
				}

				return $iTotalStock;
			}
			
			function GetProfitLoss($aData)
			{
				//parameter input :
				//$aData['outlet_ID'] = if empty equals all outlet
				//$aData['product_ID'] = if empty equals all products
				//$aData['Date'] = date start BETWEEN date end

				//logic :
				//in its basic form, the equation for profit loss is:
				//gross profit = sales - cost of goods sold
				//net profit = gross profit - expenses
				//
				//cost of goods sold = opening inventory (cost of inventory at beginning of period)
				//plus inventory purchased during the period
				//equals total inventory available during the period
				//less closing inventory (cost of all unsold stock)

				$aProfitLoss = array(); //this is the return array

				include_once("classOutlet.php");
				include_once("classProduct.php");				
				include_once("classInventory.php");
				include_once("classPurchase.php");
				include_once("classSales.php");
				include_once("classPaymentType.php");
				include_once("classTransfer.php");

				$cOutlet = new Outlet;
				$cProduct = new Product;
				$cInventory = new Inventory;
				$cPurchase = new Purchase;
				$cSales = new Sales;
				$cPaymentType = new PaymentType;
				$cTransfer = new Transfer;

				//first, we check the parameters
				//parameter product ID
				if ( $aData["product_ID"] == "" ) //this means we want all product
				{
					if (isset($aData["productCategory_ID"]) && $aData["productCategory_ID"] > 0)
					{
						$aProductData = $cProduct->GetProductByCategory( $aData["productCategory_ID"] );
					}
					else
					{
						//get all product list
						$aProductData = $cProduct->GetProductList();
					}
				}
				else
				{
					//get product by ID
					$aProductData = $cProduct->GetProductByID($aData["product_ID"]);
				}

				//parameter outlet ID
				if ( $aData["outlet_ID"] == "" ) //this means we want all outlet
				{
					$sOutletName = "All Outlet";
				}
				else
				{
					//get product by ID
					$aOutletData = $cOutlet->GetOutletByID($aData["outlet_ID"]);
					$sOutletName = $aOutletData[0]["Name"];
				}

				//parameter date
				//we want to split the date for opening and closing inventory
				list($sDateBegin, $sDateEnd) = explode("AND", $aData["Date"]);
				$sDateBegin = str_replace("BETWEEN", "", $sDateBegin); //remove the "BETWEEN" from the beginning
				$sDateBegin = str_replace("'", "", $sDateBegin); //remove the ' (single quotes)
				$sDateBegin = str_replace("'", "", $sDateBegin); //remove the ' (single quotes)
				$sDateBegin = trim($sDateBegin); //remove the whitespace from begin and end of string
				$sDateEnd = str_replace("'", "", $sDateEnd); //remove the ' (single quotes)
				$sDateEnd = trim($sDateEnd); //remove the whitespace from begin and end of string

				//we will need to know which payment type is cash, so we query the db
				$aSearchPaymentType = array(
					"IsCash" => 1
				);
				$aPaymentType = $cPaymentType->GetPaymentTypeList($aSearchPaymentType);
				$aPaymentTypeCash = array();
				foreach ($aPaymentType as $key => $value)
				{
					array_push($aPaymentTypeCash, $value['ID']);
				}

				//we will need to know which payment type is plnocount, so we query the db
				$aSearchPaymentType = array(
					"PLNoCount" => 1
				);
				$aPaymentType = $cPaymentType->GetPaymentTypeList($aSearchPaymentType);
				$aPaymentTypePLNoCount = array();
				foreach ($aPaymentType as $key => $value)
				{
					array_push($aPaymentTypePLNoCount, $value['ID']);
				}

				//now we start the process
				$aProfitLoss = array();
				//we loop by product ID
				for ($i = 0; $i < count($aProductData); $i++)
				{
set_time_limit(0);
					//we setup the search query parameters
					$aSearchParam = array(
						"Date" => $aData["Date"],
						"product_ID" => $aProductData[$i]["ID"],
						"outlet_ID" => $aData["outlet_ID"]
					);
					$aPurchaseResult = $cPurchase->GetPurchaseReport($aSearchParam);

					//we simply re-use the search query parameter
					$aSalesResult = $cSales->GetSalesReport($aSearchParam);

					//then we want the opening inventory
					//we simply update the search query param date to opening date
					$aSearchParam["Date"] = " < '" . $sDateBegin . "'";
					$aInventoryOpening = $cInventory->GetInventoryByProductID($aSearchParam);

					//finally we want the closing inventory
					//we again update the search query param date to closing date
					$aSearchParam["Date"] = " <= '" . $sDateEnd . "'";
					$aInventoryClosing = $cInventory->GetInventoryByProductID($aSearchParam);

//for outlet specific data, there is one more thing to consider, transfer in, because purchase are centralized
					if ( $aData["outlet_ID"] > 0 )
					{
						$aSearchParam["Date"] = " >= '" . $sDateBegin . "' AND transfer.Date <= '" . $sDateEnd . "'";
						$aTransferInResult = $cTransfer->GetTransferReport("In", $aSearchParam);
						$aTransferOutResult = $cTransfer->GetTransferReport("Out", $aSearchParam);
					}

					//processing data
					//Now, we have four arrays to process
					//$aPurchaseResult = new purchase for the period
					//$aSalesResult = sales for the period
					//$aInventoryOpening = inventory on beginning period
					//$aInventoryClosing = inventory on closing period


					//the product purchase price is an average price of purchased products for all outlets
					//for the search parameter, we omit the outlet_ID parameter
					$aSearchAvgParam = array(
						"Date" => " <= '" . $sDateEnd . "'",
						"product_ID" => $aProductData[$i]["ID"],
						"Price" => 0
					);
					$aSortByParam = array(
						"Date" => "DESC"
					);
					$aLimitByParam = array(
						"start" => 0,
						"nbOfData" => 5
					);
					$iAvgPurchasePrice = $cPurchase->GetAveragePurchasePriceByProduct($aSearchAvgParam, $aSortByParam, $aLimitByParam);

					$iTotalPurchase = 0;
					$iTotalPurchaseDisplay = 0;
					$iTotalPurchaseNonCash = 0;
					$aPurchaseNonCash = array();
					//from the purchase result, we will need only the product ID, quantity and price
					foreach ($aPurchaseResult as $key => $value)
					{
						//we do not count those row marked with PLNoCount
						if ( !in_array($value["paymentType_ID"], $aPaymentTypePLNoCount) )
						{
							if ( in_array($value["paymentType_ID"], $aPaymentTypeCash)
								||  ($value["Status"] == 1 && !in_array($value["paymentType_ID"], $aPaymentTypeCash) )
							)
							{
								//do nothing, purchase value is not listed in the main page
							}
							else
							{
								$iTotalPurchaseNonCash += ($value["Quantity"] * $iAvgPurchasePrice); // $value["Price"]
								$aPurchaseNonCash[] = $value;
							}
	
							$iTotalPurchase += ($value["Quantity"] * $iAvgPurchasePrice); // $value["Price"]
						}
					}

					$iTotalPurchaseDisplay = $iTotalPurchase; //this is only for display. because outlet can have zero purchase, only transfer in, however the value of transfer in is used to calculate total purchase

					//only happen for outlet specific result
					if ( $aData["outlet_ID"] > 0 )
					{
						foreach ($aTransferInResult as $key => $value)
						{
							$iTotalPurchase += ($value["quantity"] * $iAvgPurchasePrice); // $value["Price"]
						}

						foreach ($aTransferOutResult as $key => $value)
						{
							$iTotalPurchase -= ($value["quantity"] * $iAvgPurchasePrice); // $value["Price"]
						}
					}

					$iTotalSales = 0;
					$iTotalSalesCash = 0;
					$iTotalSalesNonCash = 0;
					$aSalesCash = array();
					$aSalesNonCash = array();
					//from the purchase result, we will need only the product ID, quantity and price
					foreach ($aSalesResult as $key => $value)
					{
						//we do not count those marked as PLNoCount
						if ( !in_array($value["paymentType_ID"], $aPaymentTypePLNoCount ) )
						{
							if ( in_array($value["paymentType_ID"], $aPaymentTypeCash)
								||  ($value["Status"] == 1 && !in_array($value["paymentType_ID"], $aPaymentTypeCash) )
							)
							{
								$iTotalSalesCash += ($value["Quantity"] * $value["Price"] * ((100 - $value["Discount"]) / 100) );
								$aSalesCash[] = $value;
							}
							else
							{
								$iTotalSalesNonCash += ($value["Quantity"] * $value["Price"] * ((100 - $value["Discount"]) / 100) );
								$aSalesNonCash[] = $value;
							}
	
							$iTotalSales += ($value["Quantity"] * $value["Price"] * ((100 - $value["Discount"]) / 100) );
						}
					}

					$iInventoryOpening = 0;
					$iInventoryOpeningQuantity = 0;
					foreach($aInventoryOpening as $key => $value)
					{
						$iInventoryOpening += $value['total_quantity'] * $iAvgPurchasePrice;
						$iInventoryOpeningQuantity += $value['total_quantity'];
					}

					$iInventoryClosing = 0;
					$iInventoryClosingQuantity = 0;
					foreach($aInventoryClosing as $key => $value)
					{
						$iInventoryClosing += $value['total_quantity'] * $iAvgPurchasePrice;
						$iInventoryClosingQuantity += $value['total_quantity'];
					}

					$aProfitLoss[] = array(
						"Product_ID" => $aProductData[$i]["ID"],
						"Avg_Purchase_Price" => $iAvgPurchasePrice,
						"Total_Purchase" => $iTotalPurchase,
						"Total_Purchase_Display" => $iTotalPurchaseDisplay,
						"Total_Purchase_Non_Cash" => $iTotalPurchaseNonCash,
						"Data_Purchase_Non_Cash" => $aPurchaseNonCash,
						"Total_Sales" => $iTotalSales,
						"Total_Sales_Cash" => $iTotalSalesCash,
						"Data_Sales_Cash" => $aSalesCash,
						"Total_Sales_Non_Cash" => $iTotalSalesNonCash,
						"Data_Sales_Non_Cash" => $aSalesNonCash,
						"Opening_Inventory" => $iInventoryOpening,
						"Opening_Inventory_Quantity" => $iInventoryOpeningQuantity,
						"Closing_Inventory" => $iInventoryClosing,
						"Closing_Inventory_Quantity" => $iInventoryClosingQuantity
					);
				}

				return $aProfitLoss;
			}
			//*** END FUNCTION ****************************************//
		}
	}
			
?>