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
	* lib/classTransfer.php :: TRANSFER CLASS									*
	*********************************************************************
	* All related transfer table function											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2012-02-27 										*
	* Last modified	: 2013-01-05										*
	* 																	*
	*********************************************************************/

	if ( !class_exists('Transfer') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		include_once($libPath . "/classOutlet.php");
		include_once($libPath . "/classProduct.php");
		/*
		include_once($libPath . "/classSales.php");
		include_once($libPath . "/classExpenses.php");
		include_once($libPath . "/classDeposit.php");*/
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Transfer extends Database
		{
			//*** BEGIN FUNCTION LIST ***********************************//
			// Transfer()
			// Insert($aData)
			// Update($aData)
			// InsertDetail($aData)
			// UpdateDetail($aData)
			
			//AddTransferGroup($aData)
			//RemoveTransferOutlet($aData)
			//GetTranferGroupListByFromOutletID($iOutletID)
			
			// VerifyTransferByID($iID)
			// GetTransferByID($iTransferID);
			// GetTransferDetailByID($iID);
			// GetTransferDetailByTransferID($iID)
			// GetTransferListWithDetailByOutletID($aData)
			// GetTransferReportListWithDetailByOutletID($aData)
			// GetTransferDestination()
			// GetProductForTransfer()
			// GetTotalTransferByProduct($iProductID, $sDate)
			// GetTotalTransferInByProductAndOutlet($iProductID, $iOutletID)
			// GetTotalTransferOutByProductAndOutlet($iProductID, $iOutletID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Transfer()
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

			function Insert($aData)
			{
				if ( is_array($aData) ) //check that $aClient is an array
				{

					if ($aData['Source'] == 0)
					{
						echo "cannot insert transfer without proper source outletID";
						die();
					}

					if ($aData['Destination'] == 0)
					{
						echo "cannot insert transfer without proper destination outletID";
						die();
					}

					foreach( $aData as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO transfer';
						$sQuery .= ' (`From_outlet_ID`, `To_outlet_ID`, `Notes`, `Date`)';
						$sQuery .= ' VALUES ("' . $aData['Source'] .'",';
						$sQuery .= ' "' . $aData['Destination'] .'",';
						$sQuery .= ' "' . $aData['Notes'] .'",';
						$sQuery .= ' "' . date("Y-m-d") .'")';

						$aResult = $this->dbAction($sQuery);

						$iLastID = $this->dbLink->lastInsertId();
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
					//}
					return $iLastID;
				}
			}

			function Update($aData)
			{
				$aResult = 0;
				if ( is_array($aData) ) //check that $aClient is an array
				{
					foreach( $aData as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ($aData['Source'] == 0)
					{
						echo "cannot insert transfer without proper source outletID";
						die();
					}

					if ($aData['Destination'] == 0)
					{
						echo "cannot insert transfer without proper destination outletID";
						die();
					}

					$sQuery  = 'UPDATE transfer';
					$sQuery .= ' SET `From_outlet_ID` = "' . $aData['Source'] . '"';
					$sQuery .= ' ,`To_outlet_ID` = "' . $aData['Destination'] . '"';
					$sQuery .= ' ,`Notes` = "' . $aData['Notes'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}

			function VerifyTransferByID($iID)
			{
				$sQuery  = 'UPDATE transfer';
				$sQuery .= ' SET `Status` = "1"';
				$sQuery .= ' WHERE `ID` = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == 0)
				{
					$this->logError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function InsertDetail($aData)
			{
				if ( is_array($aData) ) //check that $aClient is an array
				{
					foreach( $aData as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ( $aData['transfer_ID'] <= 0 )
					{
						echo "cannot insert transfer detail without proper transfer ID";
						die();
					}

					if ( $aData['product_ID'] <= 0 )
					{
						echo "cannot insert transfer detail without proper product ID";
						die();
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO transfer_detail';
						$sQuery .= ' (`transfer_ID`, `product_ID`, `quantity`, `SnStart`, `SnEnd`)';
						$sQuery .= ' VALUES ("' . $aData['transfer_ID'] .'",';
						$sQuery .= ' "' . $aData['product_ID'] .'",';
						$sQuery .= ' "' . $aData['quantity'] .'",';
						$sQuery .= ' "' . $aData['sn_start'] .'",';
						$sQuery .= ' "' . $aData['sn_end'] .'")';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}

					//}
					return $aResult;
				}
			}

			function UpdateDetail($aData)
			{
				$aResult = 0;
				if ( is_array($aData) ) //check that $aClient is an array
				{
					foreach( $aData as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					if ( $aData['transfer_ID'] <= 0 )
					{
						echo "cannot insert transfer detail without proper transfer ID";
						die();
					}

					if ( $aData['product_ID'] <= 0 )
					{
						echo "cannot insert transfer detail without proper product ID";
						die();
					}
	
					$sQuery  = 'UPDATE transfer_detail';
					$sQuery .= ' SET `transfer_ID` = "' . $aData['transfer_ID'] . '"';
					$sQuery .= ' ,`product_ID` = "' . $aData['product_ID'] . '"';
					$sQuery .= ' ,`quantity` = "' . $aData['quantity'] . '"';
					$sQuery .= ' ,`SnStart` = "' . $aData['sn_start'] . '"';
					$sQuery .= ' ,`SnEnd` = "' . $aData['sn_end'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}

			function AddTransferOutlet($aData)
			{
				if ( is_array($aData) ) //check that $aClient is an array
				{
					foreach( $aData as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ($aData['From_outlet_ID'] == 0)
					{
						echo "cannot insert transfer without proper from outletID";
						die();
					}

					if ($aData['To_outlet_ID'] == 0)
					{
						echo "cannot insert transfer without proper to outletID";
						die();
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO transferOutlet';
						$sQuery .= ' (`From_outlet_ID`, `To_outlet_ID`)';
						$sQuery .= ' VALUES ("' . $aData['From_outlet_ID'] .'",';
						$sQuery .= ' "' . $aData['To_outlet_ID'] .'")';

						$aResult = $this->dbAction($sQuery);

						$iLastID = mysql_insert_id();
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
					//}
					return $iLastID;
				}
			}

			function RemoveTransferOutlet($aData)
			{
				if ( is_array($aData) ) //check that $aClient is an array
				{
					foreach( $aData as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'DELETE FROM transferOutlet';
						$sQuery .= ' WHERE `From_outlet_ID` = "'. $aData['From_outlet_ID'] .'"';
						$sQuery .= ' AND `To_outlet_ID` = "'. $aData['To_outlet_ID'] .'"';

						$aResult = $this->dbAction($sQuery);

						$iLastID = $this->dbLink->lastInsertId();
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
					//}
					return $iLastID;
				}
			}

			function GetTranferGroupListByFromOutletID($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM transferOutlet';
				$sQuery .= ' WHERE From_outlet_ID = "' . $iID . '"';

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


			function GetTransferByID($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM transfer';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

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

			function GetTransferDetailByID($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM transfer_detail';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

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

			function GetTransferDetailByTransferID($iID)
			{
				$sQuery  = 'SELECT transfer_detail.*, product.Name AS productName, product.ID AS productID';
				$sQuery .= ' FROM transfer_detail, product';
				$sQuery .= ' WHERE transfer_ID = "' . $iID . '"';
				$sQuery .= ' AND transfer_detail.product_ID = product.ID';

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

			function GetTransferListWithDetailByOutletID($aData)
			{
				switch ($aData["view_type"])
				{
					case "all" :
						if ($aData["outlet_ID"] > 0)
						{
							$sViewQuery = ' AND (From_outlet_ID = "' . $aData["outlet_ID"] . '"';
							$sViewQuery .= ' OR To_outlet_ID = "' . $aData["outlet_ID"] . '")';
						}
					break;
					case "in" :
						if ($aData["outlet_ID"] > 0)
						{
							$sViewQuery = ' AND To_outlet_ID = "' . $aData["outlet_ID"] . '"';
						}
					break;
					case "out" :
						if ($aData["outlet_ID"] > 0)
						{
							$sViewQuery = ' AND From_outlet_ID = "' . $aData["outlet_ID"] . '"';
						}
					break;
					default:
					break;
				}

				$sDateQuery = "";
				if ( isset($aData["dateBegin"]) )
				{
					$sDateQuery .= " AND Date >= \"" . $aData["dateBegin"] . "\"";
				}
				if ( isset($aData["dateEnd"]) )
				{
					$sDateQuery .= " AND Date <= \"" . $aData["dateEnd"] . "\"";
				}

				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM transfer';
				$sQuery .= ' WHERE 1';
				$sQuery .= $sViewQuery;
				$sQuery .= $sDateQuery;

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid
				foreach( $aResult as $key => $value )
				{
					foreach( $value as $key2 => $value2 )
					{
						$value2 = stripslashes($value2);
					}
				}

				foreach ($aResult as $key => $value)
				{
					foreach ($value as $key2 => $value2)
					{
						if ( $key2 === "From_outlet_ID" || $key2 === "To_outlet_ID" )
						{
							$sQuery  = 'SELECT outlet.Name AS Name';
							$sQuery .= ' FROM transfer, outlet';
							$sQuery .= ' WHERE ' . $key2 . ' = outlet.ID';
							$sQuery .= ' AND ' . $key2 . ' = "' . $value2 . '"';

							$aResultOutlet = $this->dbQuery($sQuery);

							switch($key2)
							{
								case "From_outlet_ID":
									array_push($aResult[$key], $aResultOutlet[0]["Name"]);
									$aResult[$key]["From_outlet_name"] = $aResultOutlet[0]["Name"];
								break;
								case "To_outlet_ID":
									array_push($aResult[$key], $aResultOutlet[0]["Name"]);
									$aResult[$key]["To_outlet_name"] = $aResultOutlet[0]["Name"];
								break;
								default:
								break;
							}
						}
					}
				}

				foreach ($aResult as $key => $value)
				{
					foreach ($value as $key2 => $value2)
					{
						if ($key2 == "ID")
						{
							$aResultDetail = $this->GetTransferDetailByTransferID($value2);
						}
					}

					array_push($aResult[$key], $aResultDetail);
					$aResult[$key]["Detail"] = $aResultDetail;
				}

				return $aResult;
			}

			function GetTransferReportListWithDetailByOutletID($aData)
			{
				$sViewQuery = "";

				if ($aData["To_outlet_ID"] > 0)
				{
					$sViewQuery .= ' AND To_outlet_ID = "' . $aData["To_outlet_ID"] . '"';
				}
				if ($aData["From_outlet_ID"] > 0)
				{
					$sViewQuery .= ' AND From_outlet_ID = "' . $aData["From_outlet_ID"] . '"';
				}

				if (isset($aData["DateFrom"]) && isset($aData["DateTo"]))
				{
					$sViewQuery .= ' AND Date >= "' . $aData["DateFrom"] . '"';
					$sViewQuery .= ' AND Date <= "' . $aData["DateTo"] . '"';
				}
				else
				{
					$sViewQuery .= ' AND Date >= "' . date("Y-m-d") . '"';
					$sViewQuery .= ' AND Date <= "' . date("Y-m-d") . '"';
				}

				if ($aData["product_category_ID"] > 0)
				{
					//create a mini query to get the product_ID inside the productCategory_ID
					$cProduct = new Product;

					$aProductByCategoryList = $cProduct->GetProductByCategory( $aData["product_category_ID"] );
					if (count($aProductByCategoryList) > 0)
					{
						$sViewQuery .= ' AND (';
						for($i = 0; $i < count($aProductByCategoryList); $i++)
						{
							$sViewQuery .= 'transfer_detail.product_ID="' . $aProductByCategoryList[$i]['ID'] . '"';
							if ($i < (count($aProductByCategoryList) -1) )
							{
								$sViewQuery .= ' OR ';
							}
						}
						$sViewQuery .= ' )';
					}
				}

				if ($aData["product_ID"] > 0)
				{
					$sViewQuery .= "AND transfer_detail.product_ID = '" . $aData["product_ID"] . "'";
				}

				$sQuery  = 'SELECT DISTINCT transfer.*';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE 1';
				$sQuery .= ' AND transfer.ID = transfer_detail.transfer_ID ';
				$sQuery .= $sViewQuery;

				$aResult = $this->dbQuery($sQuery);

				//change outlet ID to outlet Name
				foreach ($aResult as $key => $value)
				{
					foreach ($value as $key2 => $value2)
					{
						if ( trim($key2) == "From_outlet_ID" || trim($key2) == "To_outlet_ID" )
						{
							$sQuery  = 'SELECT outlet.Name AS Name';
							$sQuery .= ' FROM transfer, outlet';
							$sQuery .= ' WHERE ' . $key2 . ' = outlet.ID';
							$sQuery .= ' AND ' . $key2 . ' = "' . $value2 . '"';

							$aResultOutlet = $this->dbQuery($sQuery);
						}

						switch(trim($key2))
						{
							case "From_outlet_ID":
								array_push($aResult[$key], $aResultOutlet[0]["Name"]);
								$aResult[$key]["From_outlet_name"] = $aResultOutlet[0]["Name"];
							break;
							case "To_outlet_ID":
								array_push($aResult[$key], $aResultOutlet[0]["Name"]);
								$aResult[$key]["To_outlet_name"] = $aResultOutlet[0]["Name"];
							break;
							default:
							break;
						}
						
						if (trim($key2) == "ID")
						{
							$aResultDetail = $this->GetTransferDetailByTransferID($value2);
							array_push($aResult[$key], $aResultDetail);
							$aResult[$key]["Detail"] = $aResultDetail;
						}
					}
				}

				return $aResult;
			}

			function GetTransferDestination()
			{
				
				$cOutlet = new Outlet;
				$aOutlet = $cOutlet->GetActiveOutletList();

				return $aOutlet;
			}

			function GetProductForTransfer()
			{
				$cProduct = new Product;
				$aProduct = $cProduct->GetProductList();

				return $aProduct;
			}

			function GetProductForTransferByOutletID($iOutletID)
			{
				$aSearchByFieldArray = array(
					"outlet_ID" => $iOutletID
				);
				$aSortByArray=array(
					'product.Name' => 'asc'
				);
				$cProduct = new Product;
				$aProduct = $cProduct->GetProductListForSalesRetail($aSearchByFieldArray, $aSortByArray);

				return $aProduct;
			}

			function GetTotalTransferByProduct($iProductID, $sDate)
			{
/* THIS function is incomplete
				//get all outlet
				$aOutletList = $this->GetTransferDestination();
				$iTotalTransfer = 0;
				//loop for each outlet, total transfer in and total transfer out
				for ($i = 0; $i < count($aOutletList); $i++)
				{
					$iOutletID = $aOutletList[$i]["ID"];

					$aTotalTransferIn = $this->GetTotalTransferInByProductAndOutlet($iProductID, $iOutletID, $sDate);

					$aTotalTransferOut = $this->GetTotalTransferOutByProductAndOutlet($iProductID, $iOutletID, $sDate);

					$iTotalTransfer2 = $aTotalTransferIn[0]["quantity"] - $aTotalTransferOut[0]["quantity"]; 
					$iTotalTransfer += $iTotalTransfer2;

echo $iOutletID;
echo "=";
echo $aTotalTransferIn[0]["quantity"];
echo "-";
echo $aTotalTransferOut[0]["quantity"];
echo "=";
echo $iTotalTransfer2;
echo "==";
echo $iTotalTransfer;
echo "<br />\r\n";
				}
				
				return $iTotalTransfer;
*/
			}

			function GetTotalTransferInByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT transfer_detail.product_ID AS productID, SUM(transfer_detail.quantity) AS quantity';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE transfer.ID = transfer_detail.transfer_ID';
				$sQuery .= ' AND transfer_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND transfer.To_outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND transfer.Date <= "' . $sDate . '"';
				$sQuery .= ' GROUP BY transfer_detail.product_ID';

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

			function GetTotalVerifiedTransferInByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT transfer_detail.product_ID AS productID, SUM(transfer_detail.quantity) AS quantity';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE transfer.ID = transfer_detail.transfer_ID';
				$sQuery .= ' AND transfer_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND transfer.To_outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND transfer.Date <= "' . $sDate . '"';
				$sQuery .= ' AND transfer.Status = "1"';
				$sQuery .= ' GROUP BY transfer_detail.product_ID';

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

			function GetTotalNotVerifiedTransferInByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT transfer_detail.product_ID AS productID, SUM(transfer_detail.quantity) AS quantity';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE transfer.ID = transfer_detail.transfer_ID';
				$sQuery .= ' AND transfer_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND transfer.To_outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND transfer.Date <= "' . $sDate . '"';
				$sQuery .= ' AND transfer.Status = "0"';
				$sQuery .= ' GROUP BY transfer_detail.product_ID';

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

			function GetTotalTransferOutByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT transfer_detail.product_ID AS productID, SUM(transfer_detail.quantity) AS quantity, transfer.Status AS status';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE transfer.ID = transfer_detail.transfer_ID';
				$sQuery .= ' AND transfer_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND transfer.From_outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND transfer.Date <= "' . $sDate . '"';
				$sQuery .= ' GROUP BY transfer_detail.product_ID';

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

			function GetTotalVerifiedTransferOutByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT transfer_detail.product_ID AS productID, SUM(transfer_detail.quantity) AS quantity, transfer.Status AS status';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE transfer.ID = transfer_detail.transfer_ID';
				$sQuery .= ' AND transfer_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND transfer.From_outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND transfer.Date <= "' . $sDate . '"';
				$sQuery .= ' AND transfer.Status = "1"';
				$sQuery .= ' GROUP BY transfer_detail.product_ID';

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

			function GetTotalNotVerifiedTransferOutByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT transfer_detail.product_ID AS productID, SUM(transfer_detail.quantity) AS quantity, transfer.Status AS status';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE transfer.ID = transfer_detail.transfer_ID';
				$sQuery .= ' AND transfer_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND transfer.From_outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND transfer.Date <= "' . $sDate . '"';
				$sQuery .= ' AND transfer.Status = "0"';
				$sQuery .= ' GROUP BY transfer_detail.product_ID';

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

			function GetTransferReport($sDirection, $aData)
			{
				//parameter input

				//$sDirection is either In or Out
				//In = To_outlet_ID
				//Out = From_outlet_ID

				//$aData["Date"] = single date, will get data upto (including) the date
				//$aData["product_ID"] = if empty, will get data for all product
				//$aData["outlet_ID"] = if empty, will get data for all outlet

				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM transfer, transfer_detail';
				$sQuery .= ' WHERE transfer.ID = transfer_detail.transfer_ID';

				foreach ($aData as $key => $value )
				{
					switch($key)
					{
						case "Date" :
							$sQuery .= ' AND transfer.Date ' . $value;
						break;
						case "product_ID" :
							$sQuery .= ' AND transfer_detail.product_ID = "' . $value . '"';
						break;
						case "outlet_ID" :
							if ($sDirection == "In")
							{
								$sQuery .= ' AND transfer.To_outlet_ID = "' . $value . '"';
							}
							if ($sDirection == "Out")
							{
								$sQuery .= ' AND transfer.From_outlet_ID = "' . $value . '"';
							}
						break;
						default:
						break;
					}
				}
				$sQuery .= ' ORDER BY transfer_detail.product_ID';

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

			function LogError($sError)
			{
				/*include('dirConf.php');
				$fError = fopen($logPath . '/error.log', 'a');
				fwrite($fError, 'ERROR::' . $sError . '::IN::' . $_SERVER['SCRIPT_NAME'] . '::FROM::' . $_SERVER['REMOTE_ADDR'] . '::ON::' . date("D M j G:i:s T Y") . "\r\n" );
				fclose($fError);*/
				//header("location:error.php");
			}
			//*** END FUNCTION ****************************************//
		}
	}
			
?>
