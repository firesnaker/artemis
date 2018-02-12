<?php
	/********************************************************************
	* lib/classInventory.php :: INVENTORY CLASS									*
	*********************************************************************
	* All related inventory function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2010-07-07 										*
	* Last modified	: 2013-01-08										*
	* 																	*
	* 				Copyright (c) 2010 FireSnakeR						*
	*********************************************************************/

	if ( !class_exists('Inventory') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Inventory extends Database
		{
			var $ID				= FALSE;
			var $Quantity		= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Insert()
			// Inventory($iInventoryID = 0)
			// GetInventoryList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetInventoryByID($iPurchaseID)
			// CalculateInventoryByOutletID($iID);
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Inventory($iInventoryID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iInventoryID) && $iInventoryID > 0 ) //check $iInventoryID is numeric and positive value
					{
						$aInventory = $this->GetInventoryByID($iInventoryID);

						if (is_array($aInventory) && count($aInventory) == 1) //check $aInventory is an array and has exactly one data
						{
							$this->ID = $aInventory[0]['ID'];
							$this->Quantity = $aInventory[0]['Quantity'];
						}
						else
						{
							//log and report that user does not exists
							$this->LogError('WARNING::Invalid user ID::' . $iUserID);
						}
					}
					else
					{
						//log and report that a non numeric value has been inserted
						if ( $iInventoryID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iInventoryID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aInventory)
			{
				$iResult = 0;

				if ( is_array($aInventory) ) //check that $aInventory is an array
				{
					foreach( $aInventory as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO inventory';
						$sQuery .= ' (`outlet_ID`, `product_ID`, `Quantity`)';
						$sQuery .= ' VALUES ("' . $aInventory['outlet_ID'] .'",';
						$sQuery .= ' "' . $aInventory['product_ID'] .'",';
						$sQuery .= ' "' . $aInventory['quantity'] .'")';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							$iResult = $this->dbLink->lastInsertId();
						}
					//}

					return $iResult;
				}
			}

			function Update($aInventory)
			{
				$iResult = 0;

				if ( is_array($aInventory) ) //check that $aProduct is an array
				{
					foreach( $aInventory as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'UPDATE inventory';
						$sQuery .= ' SET `Quantity` ="' . $aInventory['quantity'] .'"';
						$sQuery .= ' WHERE ID = "' . $aInventory['inventory_ID'] . '"';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							$iResult = $aInventory['inventory_ID'];
						}
					//}
					return $iResult;
				}
			}

			function updateStockByPurchaseDetailID($iPurchaseDetailID, $iOldQuantity)
			{/* this function is disabled due to enormous error
				//get data from database, to check if user is updating quantity on record or is this a new purchase record.
				$sQuery = "SELECT outlet_ID, product_ID, Quantity FROM purchase, purchase_detail WHERE purchase.ID = purchase_detail.purchase_ID AND purchase_detail.ID = '" . $iPurchaseDetailID . "'";
				$aResult = $this->dbQuery($sQuery);

				for ( $i = 0; $i < count($aResult); $i++ )
				{
					//offset quantity from purchase record with old quantity
					$iQuantityRealForUpdate = $aResult[$i]["Quantity"] - $iOldQuantity;

					//select if stock already exists in database
					$sQuery = "SELECT ID, Quantity FROM inventory WHERE outlet_ID = '" . $aResult[$i]["outlet_ID"] . "' AND product_ID = '" . $aResult[$i]["product_ID"] . "'";
					$aResult_select = $this->dbQuery($sQuery);

					$aInventoryData = array(
						"inventory_ID" => (count($aResult_select) > 0)?$aResult_select[0]["ID"]:"0",
						"outlet_ID" => $aResult[$i]["outlet_ID"],
						"product_ID" => $aResult[$i]["product_ID"],
						"quantity" => (count($aResult_select) > 0)?$aResult_select[0]["Quantity"] + $iQuantityRealForUpdate:$iQuantityRealForUpdate
					);

					if ( $aInventoryData["inventory_ID"] > 0 )
					{ //if yes then update
						$this->Update($aInventoryData);
						
					}
					else
					{ //if not then insert
						$this->Insert($aInventoryData);
					}
				}*/
			}

			function updateStockBySalesDetailID($iSalesDetailID, $iOldQuantity)
			{/* this function is disabled due to enormous error
				//get data from database, to check if user is updating quantity on record or is this a new sales record.
				$sQuery = "SELECT outlet_ID, product_ID, Quantity FROM sales, sales_detail WHERE sales.ID = sales_detail.sales_ID AND sales_detail.ID = '" . $iSalesDetailID . "'";
				$aResult = $this->dbQuery($sQuery);

				for ( $i = 0; $i < count($aResult); $i++ )
				{
					//check if this is a quantity update or price update
					if ($aResult[$i]["Quantity"] <> $iOldQuantity)
					{//if quantity update then update stock
						//offset quantity from purchase record with old quantity
						$iQuantityRealForUpdate = $iOldQuantity - $aResult[$i]["Quantity"];

						//select if stock already exists in database
						$sQuery = "SELECT ID, Quantity FROM inventory WHERE outlet_ID = '" . $aResult[$i]["outlet_ID"] . "' AND product_ID = '" . $aResult[$i]["product_ID"] . "'";
						$aResult_select = $this->dbQuery($sQuery);

						$aInventoryData = array(
							"inventory_ID" => (count($aResult_select) > 0)?$aResult_select[0]["ID"]:"0",
							"outlet_ID" => $aResult[$i]["outlet_ID"],
							"product_ID" => $aResult[$i]["product_ID"],
							"quantity" => (count($aResult_select) > 0)?$aResult_select[0]["Quantity"] + $iQuantityRealForUpdate:$iQuantityRealForUpdate
						);

						if ( $aInventoryData["inventory_ID"] > 0 )
						{ //if yes then update
							$this->Update($aInventoryData);
						}
						else
						{ //if not then insert
							$this->Insert($aInventoryData);
						}
//echo $aResult_select[0]["Quantity"] . "+" . $iQuantityRealForUpdate . "=" . $aInventoryData['quantity'];die();
					}
					else
					{//if not quantity update, then ignore
						return 0;
					}
				}*/
			}

			function GetInventoryList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT inventory.ID AS ID, inventory.Outlet_ID AS outletID, product.Name AS productName, inventory.Quantity AS quantity';
				$sQuery .= ' FROM inventory, product';
				$sQuery .= ' WHERE inventory.product_ID = product.ID';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray);
				arsort($aSearchByFieldArray);
				end($aSearchByFieldArray);
				if (current($aSearchByFieldArray) == "")
					array_pop($aSearchByFieldArray);

				//search by field
				if ( count($aSearchByFieldArray) > 0 )
				{
					$i = 0;
					$sQuery .= ' AND';
					foreach ($aSearchByFieldArray as $key => $value )
					{
						$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY inventory.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' inventory.ID ASC';
				if ( count($aSortByArray) > 0 )
				{
					
					foreach($aSortByArray as $key => $value)
					{
						$sQuery .= ', ' . $key . ' ' . $value;
					}
				}

				//limit data
				if ( count($aLimitByArray) > 0 )
				{
					$sQuery .= ' LIMIT ' . $aLimitByArray['start'] . ', ' . $aLimitByArray['nbOfData']; //from position, nb of records to show
				}

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid
				foreach( $aResult as $key => $value )
				{
					foreach( $value as $key2 => $value2 )
					{
						$value2 = stripslashes($value2);
					}
				}

				return $aResult;
			}

			function GetInventoryByID($iInventoryID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM inventory';
				$sQuery .= ' WHERE ID = "' . $iInventoryID . '"';

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid
				foreach( $aResult as $key => $value )
				{
					foreach( $value as $key2 => $value2 )
					{
						$value2 = stripslashes($value2);
					}
				}

				return $aResult;
			}

			function GetMaximumSalesAmountByProduct($iProductID = 0, $iOutletID = 0)
			{//TODO not implemented yet.This is to enable checking that stock is enough for sales and transfer out.
				$aResult = $this->CalculateInventoryByOutletID($iOutletID);
			}

			function CalculateInventoryByOutletID($iID = 0, $sDate = "")
			{
				include_once("classProduct.php");
				include_once("classPurchase.php");
				include_once("classSales.php");
				include_once("classTransfer.php");

				$cProduct = new Product;
				$cPurchase = new Purchase;
				$cSales = new Sales;
				$cTransfer = new Transfer;

				if ($sDate == "")
				{
					$sDate = date("Y-m-d");
				}

				//get product list
				$aProductData = $cProduct->GetProductList();


				$aTransferInTotalVerified = array();
				$aTransferOutTotal = array();
				$aTransferInTotal = array();
				$aTransferInTotalNotVerified = array();
				$aTransferOutTotalVerified = array();
				$aTransferOutTotalNotVerified = array();
				for ($i = 0; $i < count($aProductData); $i++)
				{
					//if $iID is 0, then we want to get inventory for all outlet
					if ($iID == 0)
					{
						//get purchase
						$aPurchaseTotal = $cPurchase->GetTotalPurchaseByProduct($aProductData[$i]["ID"], $sDate);

						//get sales
						$aSalesTotal = $cSales->GetTotalSalesByProduct($aProductData[$i]["ID"], $sDate);
		
						//get transfer
						//I am confused as to what I should put here...
						/*$iTransferTotal = $cTransfer->GetTotalTransferByProduct($aProductData[$i]["ID"], $sDate);
						$aTransferInTotalVerified[] = array(
							"quantity" => $iTransferTotal
						);
echo "<hr />\r\n";*/ 
					}
					else
					{
						//get purchase
						$aPurchaseTotal = $cPurchase->GetTotalPurchaseByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);

						//get sales
						$aSalesTotal = $cSales->GetTotalSalesByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);
		
						//get transfer in
						$aTransferInTotal = $cTransfer->GetTotalTransferInByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);
						$aTransferInTotalVerified = $cTransfer->GetTotalVerifiedTransferInByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);
						$aTransferInTotalNotVerified = $cTransfer->GetTotalNotVerifiedTransferInByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);

						//get transfer out
						$aTransferOutTotal = $cTransfer->GetTotalTransferOutByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);
						$aTransferOutTotalVerified = $cTransfer->GetTotalVerifiedTransferOutByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);
						$aTransferOutTotalNotVerified = $cTransfer->GetTotalNotVerifiedTransferOutByProductAndOutlet($aProductData[$i]["ID"], $iID, $sDate);
					}

					$iPurchaseTotalQuantity = 0;
					if ( count($aPurchaseTotal) > 0 )
					{
						$iPurchaseTotalQuantity = $aPurchaseTotal[0]["quantity"];
					}
					$iTransferInTotalVerifiedQuantity = 0;
					if ( count($aTransferInTotalVerified) > 0 )
					{
						$iTransferInTotalVerifiedQuantity = $aTransferInTotalVerified[0]["quantity"];
					}
					$iSalesTotalQuantity = 0;
					if ( count($aSalesTotal) > 0 )
					{
						$iSalesTotalQuantity = $aSalesTotal[0]["quantity"];
					}
					$iTransferOutTotalQuantity = 0;
					if ( count($aTransferOutTotal) > 0 )
					{
						$iTransferOutTotalQuantity = $aTransferOutTotal[0]["quantity"];
					}

					//get average purchase price for this product
					$aSearchAvgPrice = array(
						"product_ID" => $aProductData[$i]["ID"],
						"Date" => " <= '" . $sDate . "'",
						"Price" => 0
					);
					$iAveragePurchasePrice = $cPurchase->GetAveragePurchasePriceByProduct( $aSearchAvgPrice );

					$aInventoryTotal[] = array(
						"ProductID" => $aProductData[$i]["ID"],
						"ProductName" => $aProductData[$i]["name"],

						"Purchase" => $iPurchaseTotalQuantity,
						"Sales" => $iSalesTotalQuantity,

						"TransferIn" => (count($aTransferInTotal))?$aTransferInTotal[0]["quantity"]:0,
						"TransferInVerified" => (count($aTransferInTotalVerified))?$aTransferInTotalVerified[0]["quantity"]:0,
						"TransferInNotVerified" => (count($aTransferInTotalNotVerified))?$aTransferInTotalNotVerified[0]["quantity"]:0,

						"TransferOut" => (count($aTransferOutTotal))?$aTransferOutTotal[0]["quantity"]:0,
						"TransferOutVerified" => (count($aTransferOutTotalVerified))?$aTransferOutTotalVerified[0]["quantity"]:0,
						"TransferOutNotVerified" =>(count($aTransferOutTotalNotVerified))? $aTransferOutTotalNotVerified[0]["quantity"]:0,

						"Quantity" => ($iPurchaseTotalQuantity + $iTransferInTotalVerifiedQuantity) - ($iSalesTotalQuantity + $iTransferOutTotalQuantity),
						"AveragePrice" => $iAveragePurchasePrice,
						"Value" => ( ($iPurchaseTotalQuantity + $iTransferInTotalVerifiedQuantity) - ($iSalesTotalQuantity + $iTransferOutTotalQuantity) ) * $iAveragePurchasePrice
						/*"quantity = (purchase + transfer in) - (sales + transfer out)"*/
					);
//echo $aProductData[$i]["ID"] . "=" . $aInventoryTotal["Quantity"] . "=" . $aPurchaseTotal[0]["quantity"] ."+". $aTransferInTotalVerified[0]["quantity"] ."-". $aSalesTotal[0]["quantity"] ."+". $aTransferOutTotal[0]["quantity"] . "<br />\r\n";
				}

				//separate the product that has quantity and those that are empty
				$aInventoryEmpty = array();
				$aInventoryFilled = array();
				for ($i = 0; $i < count($aInventoryTotal); $i++)
				{
					if ($aInventoryTotal[$i]["Quantity"] == 0 && $aInventoryTotal[$i]["TransferInNotVerified"] == 0)
					{
						$aInventoryEmpty[] = $aInventoryTotal[$i];
					}
					else
					{
						$aInventoryFilled[] = $aInventoryTotal[$i];
					}
				}

				return $aInventoryFilled;

			}

			function CalculateInventoryByProductID($iID = 0, $sDate = "")
			{
				include_once("classPurchase.php");
				include_once("classSales.php");
				include_once("classOutlet.php");
				include_once("classTransfer.php");

				$cPurchase = new Purchase;
				$cSales = new Sales;
				$cOutlet = new Outlet;
				$cTransfer = new Transfer;

				if ($sDate == "")
				{
					$sDate = date("Y-m-d");
				}

				$aInventory = array();

				//now we process by outlet
				//first we get all the outlet list
				$aOutletData = $cOutlet->GetOutletList();


				$iTotalPurchase = 0;
				$iTotalSales = 0;
				for ($i = 0; $i < count($aOutletData); $i++)
				{
					//we get the total inventory by product, outlet and date
					$aPurchaseTotal = $cPurchase->GetTotalPurchaseByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);

					//get sales
					$aSalesTotal = $cSales->GetTotalSalesByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);

					//get transfer in
					$aTransferInTotal = $cTransfer->GetTotalTransferInByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);
					$aTransferInTotalVerified = $cTransfer->GetTotalVerifiedTransferInByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);
					$aTransferInTotalNotVerified = $cTransfer->GetTotalNotVerifiedTransferInByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);

					//get transfer out
					$aTransferOutTotal = $cTransfer->GetTotalTransferOutByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);
					$aTransferOutTotalVerified = $cTransfer->GetTotalVerifiedTransferOutByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);
					$aTransferOutTotalNotVerified = $cTransfer->GetTotalNotVerifiedTransferOutByProductAndOutlet($iID, $aOutletData[$i]["ID"], $sDate);



					$aInventory[] = array(
						"OutletName" => $aOutletData[$i]["name"],
						"Purchase" => (count($aPurchaseTotal) > 0)?$aPurchaseTotal[0]["quantity"]:"",
						"Sales" => (count($aSalesTotal) > 0)?$aSalesTotal[0]["quantity"]:"",
						"Stok" => (((count($aPurchaseTotal) > 0)?$aPurchaseTotal[0]["quantity"]:0) + ((count($aTransferInTotal) > 0)?$aTransferInTotal[0]["quantity"]:0)) - (((count($aSalesTotal) > 0)?$aSalesTotal[0]["quantity"]:0) + ((count($aTransferOutTotal) > 0)?$aTransferOutTotal[0]["quantity"]:0)),

						"TransferIn" => (count($aTransferInTotal) > 0)?$aTransferInTotal[0]["quantity"]:"",
						"TransferInVerified" => (count($aTransferInTotalVerified) > 0)?$aTransferInTotalVerified[0]["quantity"]:"",
						"TransferInNotVerified" => (count($aTransferInTotalNotVerified) > 0)?$aTransferInTotalNotVerified[0]["quantity"]:"",
	
						"TransferOut" => (count($aTransferOutTotal) > 0)?$aTransferOutTotal[0]["quantity"]:"",
						"TransferOutVerified" => (count($aTransferOutTotalVerified) > 0)?$aTransferOutTotalVerified[0]["quantity"]:"",
						"TransferOutNotVerified" => (count($aTransferOutTotalNotVerified) > 0)?$aTransferOutTotalNotVerified[0]["quantity"]:""
					);

					//here, we create a counter for purchase and sales total
					$iTotalPurchase += ((count($aPurchaseTotal) > 0)?$aPurchaseTotal[0]["quantity"]:0);
					$iTotalSales += ((count($aSalesTotal) > 0)?$aSalesTotal[0]["quantity"]:0);
				}

//commented for now, because if there is a missing outlet, the result are wrong
/*
				//now we get all, for the total
				//get purchase
				$aPurchaseTotal = $cPurchase->GetTotalPurchaseByProduct($iID, $sDate);

				//get sales
				$aSalesTotal = $cSales->GetTotalSalesByProduct($iID, $sDate);
*/
				$aInventory[] = array(
					"OutletName" => "All",
					//"Purchase" => $aPurchaseTotal[0]["quantity"],
					//"Sales" => $aSalesTotal[0]["quantity"],
					//"Stok" => ($aPurchaseTotal[0]["quantity"] - $aSalesTotal[0]["quantity"])
					"Purchase" => $iTotalPurchase,
					"Sales" => $iTotalSales,
					 "Stok" => ($iTotalPurchase - $iTotalSales)
				);

				return $aInventory;

			}

			//this function is a part of GetProfitLoss in classReport, so the search is by "product_ID"
			function GetInventoryByProductID($aData)
			{

				//parameter input
				//$aData["Date"] = single date
				//$aData["product_ID"] = if empty, will get data for all product
				//$aData["outlet_ID"] = if empty, will get data for all outlet

				$aInventory = array();

				include_once("classPurchase.php");
				include_once("classSales.php");
				include_once("classTransfer.php");

				$cPurchase = new Purchase;
				$cSales = new Sales;
				$cTransfer = new Transfer;

				//get all purchase
				$aPurchaseData = $cPurchase->GetPurchaseReport($aData);

				//get all sales
				//there are two salesReport, this one is without master list which omits slave/master outlet relationship
				$aSalesData = $cSales->GetSalesReportWithoutMasterList($aData);

				$aTransferIn = array();
				$aTransferOut = array();
				//we get transfer data, only for specific outlet, otherwise, we can skip this
				if ($aData["outlet_ID"] > 0)
				{
					//get all transfer in
					$aTransferIn = $cTransfer->GetTransferReport("In", $aData);
	
					//get all transfer out
					$aTransferOut = $cTransfer->GetTransferReport("Out", $aData);
				}

				//now we do some processing
				//the expected output is sorted by product, outlet
				//contents will be nb of unit and value of unit
				//we have $aPurhaseData
				//$aSalesData
				//$aTransferIn
				//$aTransferOut

				$iTotalPurchase = 0;
				foreach($aPurchaseData as $key => $value)
				{
					$iTotalPurchase += $value['Quantity'];
				}

				foreach($aTransferIn as $key => $value)
				{
					$iTotalPurchase += $value['quantity'];
				}

				$iTotalSales = 0;
				$iTotalSalesReal = 0;
				$iTotalSalesTransfer = 0;
				foreach($aSalesData as $key => $value)
				{
					$iTotalSales += $value['Quantity'];
					$iTotalSalesReal += $value['Quantity'];
				}
				foreach($aTransferOut as $key => $value)
				{
					$iTotalSales += $value['quantity'];
					$iTotalSalesTransfer  += $value['quantity'];
				}

				//the inventory value is (purchase qty - sales qty) * purchase_price
				$iTotalQuantity = $iTotalPurchase - $iTotalSales;

				//for now we ignore the transfer quantity
				$aInventory[] = array(
					"product_ID" => $aData['product_ID'],
					"total_quantity" => $iTotalQuantity
				);
//echo $aData["product_ID"] . "=" . $iTotalQuantity . "=" . $iTotalPurchase . "-" . $iTotalSales . "($iTotalSalesReal + $iTotalSalesTransfer)" . "<br />\r\n";
				return $aInventory;
			}

			function LogError($sError)
			{
				//include('dirConf.php');
				//$fError = fopen($logPath . '/error.log', 'a');
				//fwrite($fError, 'ERROR::' . $sError . '::IN::' . $_SERVER['SCRIPT_NAME'] . '::FROM::' . $_SERVER['REMOTE_ADDR'] . '::ON::' . date("D M j G:i:s T Y") . "\r\n" );
				//fclose($fError);
				//header("location:error.php");
			}
		//*** END FUNCTION **********************************************//
		}
	}
?>