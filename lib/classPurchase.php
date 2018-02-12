<?php
	/********************************************************************
	* lib/classPurchase.php :: PURCHASE CLASS									*
	*********************************************************************
	* All related purchase function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2010-07-02 										*
	* Last modified	: 2013-02-08										*
	* 																	*
	* 				Copyright (c) 2010 FireSnakeR						*
	*********************************************************************/

	if ( !class_exists('Purchase') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Purchase extends Database
		{
			var $ID				= FALSE;
			var $Date			= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Insert()
			// Purchase($iPurchaseID = 0)
			// GetPurchaseList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetPurchaseByID($iPurchaseID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Purchase($iPurchaseID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iPurchaseID) && $iPurchaseID > 0 ) //check $iPurchaseID is numeric and positive value
					{
						$aPurchase = $this->GetPurchaseByID($iPurchaseID);

						if (is_array($aPurchase) && count($aPurchase) == 1) //check $aPurchase is an array and has exactly one data
						{
							$this->ID = $aPurchase[0]['ID'];
							$this->Date = $aPurchase[0]['Date'];
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
						if ( $iPurchaseID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iPurchaseID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aPurchase)
			{
				$iResult = 0;

				if ($aPurchase["outletID"] == 0)
				{
					echo "cannot insert purchase without outletID";
					die();
				}

				$sQuery  = 'INSERT INTO purchase';
				$sQuery .= ' (`outlet_ID`, `Date`, `Notes`)';
				$sQuery .= ' VALUES ("' . $aPurchase["outletID"] . '"';
				$sQuery .= ' ,"' . date("Ymd") . '"';
				$sQuery .= ' ,"' . $aPurchase["notes"] . '")';

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

				return $iResult;
			}

			function Update($aPurchase)
			{
				$iResult = $aPurchase["ID"];
			
				$sQuery  = 'UPDATE purchase';
				$sQuery .= ' SET `Notes` = "' . $aPurchase["notes"] . '"';
				$sQuery .= ' WHERE ID = "' . $aPurchase["ID"] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $iResult;
			}

			function Verify($iID, $sNotes)
			{
				$sQuery  = 'UPDATE purchase';
				$sQuery .= ' SET `Status` = "1"';
				$sQuery .= ' ,`VerifyNotes` = "' . $sNotes . '"';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $iResult;
			}

			function GetPurchaseList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT purchase.ID AS ID, purchase.Outlet_ID AS outletID, purchase.Date AS date, purchase.Notes AS notes';
				$sQuery .= ' FROM purchase';

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
					$sQuery .= ' WHERE';
					foreach ($aSearchByFieldArray as $key => $value )
					{
						if ($key == "Date")
						{
							$sQuery .= ' ' . $key . ' ' . $value;
						}
						else
						{
							$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
						}
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY purchase.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' purchase.ID ASC';
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

			function GetPurchaseReport( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM purchase, purchase_detail';
				$sQuery .= ' WHERE purchase.ID = purchase_detail.purchase_ID';
				$sQuery .= ' AND outlet_ID > 0';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray);
				//arsort($aSearchByFieldArray);
				//end($aSearchByFieldArray);
				//if (current($aSearchByFieldArray) == "")
					//array_pop($aSearchByFieldArray);

				//search by field
				if ( count($aSearchByFieldArray) > 0 )
				{
					foreach ($aSearchByFieldArray as $key => $value )
					{
						switch($key)
						{
							case "Date" :
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . ' ' . $value;
								}
							break;
							case "product_ID" :
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "productCategory_ID" :
								if ($value > 0)
								{
									//create a mini query to get the product_ID inside the productCategory_ID
									$cProduct = new Product;
	
									$aProductByCategoryList = $cProduct->GetProductByCategory( $value );
									if (count($aProductByCategoryList) > 0)
									{
										$sQuery .= ' AND (';
										for($i = 0; $i < count($aProductByCategoryList); $i++)
										{
											$sQuery .= 'product_ID="' . $aProductByCategoryList[$i]['ID'] . '"';
											if ($i < (count($aProductByCategoryList) -1) )
											{
												$sQuery .= ' OR ';
											}
										}
										$sQuery .= ' )';
									}
								}
							break;
							case "outlet_ID" :
								if ( (trim($value) != "") AND ($value <> 0) ) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							default:
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . ' like "%' . $value . '%"';
								}
							break;
						}
					}
				}

				//$sQuery .= ' GROUP BY sales.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' purchase.ID ASC';
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

			function GetAveragePurchasePriceByProduct( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT purchase_detail.Quantity AS Quantity, purchase_detail.Price AS Price';
				$sQuery .= ' FROM purchase, purchase_detail';
				$sQuery .= ' WHERE purchase.ID = purchase_detail.purchase_ID';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray);
				//arsort($aSearchByFieldArray);
				//end($aSearchByFieldArray);
				//if (current($aSearchByFieldArray) == "")
					//array_pop($aSearchByFieldArray);

				//search by field
				if ( count($aSearchByFieldArray) > 0 )
				{
					foreach ($aSearchByFieldArray as $key => $value )
					{
						switch($key)
						{
							case "Date" :
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . ' ' . $value;
								}
							break;
							case "product_ID" :
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "outlet_ID" :
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "Price" :
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . ' > ' . $value;
								}
							break;
							default:
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . ' like "%' . $value . '%"';
								}
							break;
						}
					}
				}

				//$sQuery .= ' GROUP BY sales.ID';

				//sort by
				$sQuery .= ' ORDER BY ';
				
				if ( count($aSortByArray) > 0 )
				{
					$i = 0;
					foreach($aSortByArray as $key => $value)
					{
						if ($i > 0)
						{
							$sQuery .= ', ';
						}
						$sQuery .= $key . ' ' . $value;
						$i++;
					}
				}
				else
				{
					$sQuery .= ' purchase.ID ASC';
				}

				//limit data
				if ( count($aLimitByArray) > 0 )
				{
					$sQuery .= ' LIMIT ' . $aLimitByArray['start'] . ', ' . $aLimitByArray['nbOfData']; //from position, nb of records to show
				}

				$aResult = $this->dbQuery($sQuery);

				//calculate the result
				$iSumQuantity = 0;
				$iSumTotal = 0;
				for ( $i = 0; $i < count($aResult); $i++ )
				{
					$iSumQuantity += $aResult[$i]['Quantity'];
					$iSumTotal += $aResult[$i]['Quantity'] * $aResult[$i]['Price'];
				}

				//the result has to be one array only. So simply query the first one.
				if ($iSumQuantity > 0)
				{
					$iAveragePrice =  $iSumTotal / $iSumQuantity;
				}
				else
				{
					$iAveragePrice = 0;
				}

				return $iAveragePrice;
			}

			function GetPurchaseByID($iPurchaseID)
			{
				$sQuery  = 'SELECT ID, outlet_ID, Date, Notes';
				$sQuery .= ' FROM purchase';
				$sQuery .= ' WHERE ID = "' . $iPurchaseID . '"';

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

			function GetPurchaseWithDetail($iPurchaseID)
			{
				$sQuery  = 'SELECT purchase.ID AS ID, outlet_ID, Date, Notes';
				$sQuery .= ' ,purchase_detail.ID AS detail_ID, product.ID AS productID, product.Name AS productName, Quantity, purchase_detail.Price AS purchasePrice, SnStart, SnEnd';
				$sQuery .= ' FROM purchase, purchase_detail, product';
				$sQuery .= ' WHERE purchase.ID = "' . $iPurchaseID . '"';
				$sQuery .= ' AND purchase_detail.purchase_ID ="' . $iPurchaseID . '"';
				$sQuery .= ' AND product.ID = purchase_detail.product_ID';

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

			function InsertDetail($aPurchaseDetail)
			{
				$iResult = 0;

				if ( is_array($aPurchaseDetail) ) //check that $aPurchaseDetail is an array
				{
					foreach( $aPurchaseDetail as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ($aPurchaseDetail['purchase_ID'] <= 0)
					{
						echo "cannot insert purchase detail without proper purchase ID";
						die();
					}

					if ($aPurchaseDetail['product_ID'] <= 0)
					{
						echo "cannot insert purchase detail without proper product ID";
						die();
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO purchase_detail';
						$sQuery .= ' (`purchase_ID`, `product_ID`, `Quantity`, `Price`, `SnStart`, `SnEnd`)';
						$sQuery .= ' VALUES ("' . $aPurchaseDetail['purchase_ID'] .'",';
						$sQuery .= ' "' . $aPurchaseDetail['product_ID'] .'",';
						$sQuery .= ' "' . $aPurchaseDetail['quantity'] .'",';
						$sQuery .= ' "' . $aPurchaseDetail['price'] .'",';
						$sQuery .= ' "' . $aPurchaseDetail['sn_start'] .'",';
						$sQuery .= ' "' . $aPurchaseDetail['sn_end'] .'")';

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

			function UpdateDetail($aPurchaseDetail)
			{
				$iResult = 0;

				if ( is_array($aPurchaseDetail) ) //check that $aProduct is an array
				{
					foreach( $aPurchaseDetail as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'UPDATE purchase_detail';
						$sQuery .= ' SET `Quantity` ="' . $aPurchaseDetail['quantity'] .'",';
						$sQuery .= ' `SnStart` ="' . $aPurchaseDetail['sn_start'] .'",';
						$sQuery .= ' `SnEnd` ="' . $aPurchaseDetail['sn_end'] .'"';
						$sQuery .= ' WHERE ID = "' . $aPurchaseDetail['purchaseDetail_ID'] . '"';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							$iResult = $aPurchaseDetail['purchaseDetail_ID'];
						}
					//}
					
					return $iResult;
				}
			}

			function UpdateDetailAdmin($aPurchaseDetail)
			{
				$iResult = 0;

				if ( is_array($aPurchaseDetail) ) //check that $aProduct is an array
				{
					foreach( $aPurchaseDetail as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'UPDATE purchase_detail';
						$sQuery .= ' SET `Quantity` ="' . $aPurchaseDetail['quantity'] .'",';
						$sQuery .= ' `product_ID` ="' . $aPurchaseDetail['product_ID'] .'",';
						$sQuery .= ' `SnStart` ="' . $aPurchaseDetail['sn_start'] .'",';
						$sQuery .= ' `SnEnd` ="' . $aPurchaseDetail['sn_end'] .'"';
						$sQuery .= ' WHERE ID = "' . $aPurchaseDetail['purchaseDetail_ID'] . '"';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							$iResult = $aPurchaseDetail['purchaseDetail_ID'];
						}
					//}
					
					return $iResult;
				}
			}

			function GetPurchaseDetailByID($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM purchase_detail';
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

			function UpdatePrice($iID, $iPrice)
			{
				$iResult = 0;

				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'UPDATE purchase_detail';
					$sQuery .= ' SET `Price` ="' . $iPrice .'"';
					$sQuery .= ' WHERE ID = "' . $iID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
					else
					{
						$iResult = FALSE;
					}
				//}
				return $iResult;
			}

			function UpdatePaymentType($iID, $iPaymentType)
			{
				$iResult = 0;

				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'UPDATE purchase';
					$sQuery .= ' SET `PaymentType_ID` ="' . $iPaymentType .'"';
					$sQuery .= ' WHERE ID = "' . $iID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
					else
					{
						$iResult = FALSE;
					}
				//}
				return $iResult;
			}

			function GetTotalPurchaseByProduct($iProductID, $sDate)
			{
				$sQuery  = 'SELECT purchase_detail.product_ID as productID, SUM(purchase_detail.Quantity) AS quantity';
				$sQuery .= ' FROM purchase, purchase_detail';
				$sQuery .= ' WHERE purchase.ID = purchase_detail.purchase_ID';
				$sQuery .= ' AND purchase_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND purchase.Date <= "' . $sDate . '"';
				$sQuery .= ' GROUP BY purchase_detail.product_ID';

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

			function GetTotalPurchaseByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT purchase_detail.product_ID as productID, SUM(purchase_detail.Quantity) AS quantity';
				$sQuery .= ' FROM purchase, purchase_detail';
				$sQuery .= ' WHERE purchase.ID = purchase_detail.purchase_ID';
				$sQuery .= ' AND purchase_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND purchase.outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND purchase.Date <= "' . $sDate . '"';
				$sQuery .= ' GROUP BY purchase_detail.product_ID';

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
		}
		//*** END FUNCTION **********************************************//
	}
?>