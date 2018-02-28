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
	* lib/classSales.php :: SALES CLASS									*
	*********************************************************************
	* All related sales function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2010-07-02 										*
	* Last modified	: 2013-01-05										*
	* 																	*
	*********************************************************************/

	if ( !class_exists('Sales') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		include_once($libPath . "/classProduct.php");
		include_once($libPath . "/classOutlet.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Sales extends Database
		{
			var $ID		= FALSE;
			var $Date		= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Insert()
			// Sales($iSalesID = 0)
			// GetSalesList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetSalesByID($iSalesID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Sales($iSalesID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iSalesID) && $iSalesID > 0 ) //check $iSalesID is numeric and positive value
					{
						$aSales = $this->GetSalesByID($iSalesID);

						if (is_array($aSales) && count($aSales) == 1) //check $aSales is an array and has exactly one data
						{
							$this->ID = $aSales[0]['ID'];
							$this->Date = $aSales[0]['Date'];
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
						if ( $iSalesID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iSalesID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aSales)
			{
				$iResult = 0;

				if ($aSales["outletID"] == 0)
				{
					echo "cannot insert sales without outletID";
					die();
				}

				$iSalesOrderID = 0;
				if (isset($aSales["salesOrderID"]) && $aSales["salesOrderID"] > 0)
				{
					$iSalesOrderID = $aSales["salesOrderID"];
				}

				$iSalesNumber = $this->generateSalesNumber($aSales["outletID"]);

				$sQuery  = 'INSERT INTO sales';
				$sQuery .= ' (`number`, `outlet_ID`, `sales_order_ID`, `employee_ID`, `client_ID`, `paymentType_ID`, `ajaxPostID`, `Date`, `Notes`)';
				$sQuery .= ' VALUES ("' . $iSalesNumber . '"';
				$sQuery .= ' ,"' . $aSales["outletID"] . '"';
				$sQuery .= ' ,"' . $iSalesOrderID . '"';
				$sQuery .= ' ,"' . $aSales["employeeID"] . '"';
				$sQuery .= ' ,"' . $aSales["clientID"] . '"';
				$sQuery .= ' ,"' . $aSales["paymentTypeID"] . '"';
				$sQuery .= ' ,"' . $aSales["ajaxPostID"] . '"';
				$sQuery .= ' ,"' . date("Ymd") . '"';
				$sQuery .= ' ,"' . $aSales["notes"] . '")';

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

			function Update($aSales)
			{
				$iResult = $aSales["ID"];
			
				$sQuery  = 'UPDATE sales';
				$sQuery .= ' SET `Notes` = "' . $aSales["notes"] . '"';
				$sQuery .= ' ,`employee_ID` = "' . $aSales["employeeID"] . '"';
				$sQuery .= ' ,`client_ID` = "' . $aSales["clientID"] . '"';
				$sQuery .= ' ,`paymentType_ID` = "' . $aSales["paymentTypeID"] . '"';
				$sQuery .= ' ,`Date` = "' . $aSales["date"] . '"';
				$sQuery .= ' WHERE ID = "' . $aSales["ID"] . '"';

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
				$sQuery  = 'UPDATE sales';
				$sQuery .= ' SET `Status` = "1"';
				$sQuery .= ' ,`FinanceNotes` = "' . $sNotes . '"';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $iResult;
			}

			function GetSalesList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT sales.ID AS ID, sales.number AS number, sales_order_ID, sales.Outlet_ID AS outletID, sales.Date AS date, sales.Notes AS notes, sales.employee_ID AS employeeID, sales.client_ID AS clientID, sales.paymentType_ID AS paymentTypeID';
				$sQuery .= ' FROM sales';

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
						elseif ($key == "outlet_ID" || $key == "outletID")
						{
							$sQuery .= ' ' . 'outlet_ID' . '=' . $value;
						}
						elseif ($key == "employee_ID" || $key == "employeeID")
						{
							$sQuery .= ' ' . 'employee_ID' . '=' . $value;
						}
						elseif ($key == "sales_order_ID" || $key == "salesOrderID")
						{
							$sQuery .= ' ' . 'sales_order_ID' . '=' . $value;
						}
						elseif ($key == "client_ID" || $key == "clientID")
						{
							$sQuery .= ' ' . 'client_ID' . '=' . $value;
						}
						elseif ($key == "payment_type_ID" || $key == "paymentTypeID")
						{
							$sQuery .= ' ' . 'paymentType_ID' . '=' . $value;
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
				
				//$sQuery .= ' GROUP BY sales.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' sales.ID ASC';
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

				return $aResult;
			}

			function GetSalesReport( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales, sales_detail';
				$sQuery .= ' WHERE sales.ID = sales_detail.sales_ID';
				$sQuery .= ' AND outlet_ID > 0 ';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray); this is disabled because it is
				//possible for outlet_ID to be the same as client_ID or employee_ID, in which case, the last
				//data will be removed, making the query all wrong.
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
							case "sales_detail.product_ID" :
								if (trim($value) != "" && $value != 0) //if not empty
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
									$sQuery .= ' AND (' . $key . '="' . $value . '"';
									//must create a query to get all master_outlet_ID
									$cOutlet = new Outlet;
									$aOutletWithMasterList = $cOutlet->GetActiveOutletWithMasterOutletList($value);
									if (count($aOutletWithMasterList) > 0)
									{
										for ($i = 0; $i < count($aOutletWithMasterList); $i++)
										{
											$sQuery .= ' OR ' . $key . '="' . $aOutletWithMasterList[$i]['ID'] . '"';
										}
									}
									$sQuery .= ')';
								}
							break;
							case "client_ID" :
							case "sales.client_ID":
								if (trim($value) != "" && $value != 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "employee_ID" :
							case "sales.employee_ID":
								if (trim($value) != "" && $value != 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "paymentType_ID":
								if (trim($value) != "" && $value != 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "sales.ID":
								if (trim($value) != "" && $value != 0) //if not empty
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

				//$sQuery .= ' GROUP BY outlet_ID';

				//sort by
				$sQuery .= ' ORDER BY';
				if (isset($aOutletWithMasterList) && count($aOutletWithMasterList) > 0)
				{
					$sQuery .= ' outlet_ID ASC,';
				}
				$sQuery .= ' sales.Date DESC';

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

			function GetSalesReportByFinanceArea( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales, sales_detail';
				$sQuery .= ' WHERE sales.ID = sales_detail.sales_ID';
				$sQuery .= ' AND outlet_ID > 0 ';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray); this is disabled because it is
				//possible for outlet_ID to be the same as client_ID or employee_ID, in which case, the last
				//data will be removed, making the query all wrong.
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
							case "sales_detail.product_ID" :
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
									$sQuery .= ' AND (' . $key . '="' . $value . '"';
									//must create a query to get all master_outlet_ID
									$cOutlet = new Outlet;
									$aOutletWithMasterList = $cOutlet->GetActiveOutletWithMasterOutletList($value);
									if (count($aOutletWithMasterList) > 0)
									{
										for ($i = 0; $i < count($aOutletWithMasterList); $i++)
										{
											$sQuery .= ' OR ' . $key . '="' . $aOutletWithMasterList[$i]['ID'] . '"';
										}
									}
									$sQuery .= ')';
								}
							break;
							case "client_ID" :
							case "sales.client_ID":
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "employee_ID" :
							case "sales.employee_ID":
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							default:
								if ($key != "AllOutlet")
								{
									if (trim($value) != "") //if not empty
									{
										$sQuery .= ' AND ' . $key . ' like "%' . $value . '%"';
									}
								}
							break;
						}
					}
					if (!in_array("outlet_ID", $aSearchByFieldArray))
					{
						$sQuery .= ' AND (';
						for ( $i = 0; $i < count($aSearchByFieldArray["AllOutlet"]); $i++ )
						{
							$sQuery .= ' outlet_ID = "' . $aSearchByFieldArray['AllOutlet'][$i]['ID'] . '" OR ';
						}
						$sQuery = substr($sQuery, 0, strlen($sQuery)-3);
						$sQuery .= ')';
					}
				}

				//$sQuery .= ' GROUP BY outlet_ID';

				//sort by
				$sQuery .= ' ORDER BY';
				if (isset($aOutletWithMasterList) && count($aOutletWithMasterList) > 0)
				{
					$sQuery .= ' outlet_ID ASC,';
				}
				$sQuery .= ' sales.Date DESC';

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

			function GetSalesReportWithoutMasterList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales, sales_detail';
				$sQuery .= ' WHERE sales.ID = sales_detail.sales_ID';
				$sQuery .= ' AND outlet_ID > 0 ';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray); this is disabled because it is
				//possible for outlet_ID to be the same as client_ID or employee_ID, in which case, the last
				//data will be removed, making the query all wrong.
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
							case "sales_detail.product_ID" :
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
									$sQuery .= ' AND (' . $key . '="' . $value . '"';
									//must create a query to get all master_outlet_ID
									$cOutlet = new Outlet;
									$aOutletWithMasterList = $cOutlet->GetActiveOutletWithMasterOutletList($value);
									if (count($aOutletWithMasterList) > 0)
									{
										for ($i = 0; $i < count($aOutletWithMasterList); $i++)
										{
											//$sQuery .= ' OR ' . $key . '="' . $aOutletWithMasterList[$i]['ID'] . '"';
										}
									}
									$sQuery .= ')';
								}
							break;
							case "client_ID" :
							case "sales.client_ID":
								if (trim($value) != "") //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "employee_ID" :
							case "sales.employee_ID":
								if (trim($value) != "") //if not empty
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

				//$sQuery .= ' GROUP BY outlet_ID';

				//sort by
				$sQuery .= ' ORDER BY';
				if (isset($aOutletWithMasterList) && count($aOutletWithMasterList) > 0)
				{
					$sQuery .= ' outlet_ID ASC,';
				}
				$sQuery .= ' sales.Date DESC';

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

			function GetSalesByID($iSalesID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales';
				$sQuery .= ' WHERE ID = "' . $iSalesID . '"';

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

			function GetSalesWithDetail($iSalesID)
			{
				$sQuery  = 'SELECT sales.ID AS ID, sales.number AS number, sales_order_ID, outlet_ID, Date, Notes, employee_ID, client_ID, paymentType_ID';
				$sQuery .= ' ,sales_detail.ID AS detail_ID, sales_detail.ajaxPostID AS detail_ajaxPostID, product.ID AS productID, product.Name AS productName, Quantity, Discount, sales_detail.Price';
				$sQuery .= ' ,SnStart, SnEnd';
				$sQuery .= ' FROM sales, sales_detail, product';
				$sQuery .= ' WHERE sales.ID = "' . $iSalesID . '"';
				$sQuery .= ' AND sales_detail.sales_ID ="' . $iSalesID . '"';
				$sQuery .= ' AND product.ID = sales_detail.product_ID';

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

			function GetSalesDetailByDetailID($iID)
			{
				$sQuery  = 'SELECT sales.ID AS ID, sales_order_ID, outlet_ID, Date, Notes, employee_ID, client_ID, paymentType_ID';
				$sQuery .= ' ,sales_detail.ID AS detail_ID, sales_detail.ajaxPostID AS detail_ajaxPostID, product.ID AS productID, product.Name AS productName, Quantity, Discount, sales_detail.Price';
				$sQuery .= ' ,SnStart, SnEnd';
				$sQuery .= ' FROM sales, sales_detail, product';
				$sQuery .= ' WHERE sales_detail.ID = "' . $iID . '"';
				$sQuery .= ' AND sales_detail.sales_ID = sales.ID';
				$sQuery .= ' AND product.ID = sales_detail.product_ID';

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

			function InsertDetail($aSalesDetail)
			{
				$iResult = 0;

				if ( is_array($aSalesDetail) ) //check that $aSalesDetail is an array
				{
					foreach( $aSalesDetail as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ( $aSalesDetail['sales_ID'] <= 0)
					{
						echo "cannot insert sales detail without proper sales ID";
						die();
					}

					if ( $aSalesDetail['product_ID'] <= 0)
					{
						echo "cannot insert sales detail without proper product ID";
						die();
					}

					$iSalesOrderDetailID = 0;
					if (isset($aSalesDetail['sales_order_detail_ID']) && $aSalesDetail['sales_order_detail_ID'] > 0)
					{
						$iSalesOrderDetailID = $aSalesDetail['sales_order_detail_ID'];
					}

					if ( $aSalesDetail['sn_start'] == "")
					{
						echo "cannot insert sales detail without proper serial number start";
						die();
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO sales_detail';
						$sQuery .= ' (`sales_ID`, `sales_order_detail_ID`, `product_ID`, `Quantity`, `Discount`, `Price`, `SnStart`, `SnEnd`)';
						$sQuery .= ' VALUES ("' . $aSalesDetail['sales_ID'] .'",';
						$sQuery .= ' "' . $iSalesOrderDetailID .'",';
						$sQuery .= ' "' . $aSalesDetail['product_ID'] .'",';
						$sQuery .= ' "' . $aSalesDetail['quantity'] .'",';
						$sQuery .= ' "' . $aSalesDetail['discount'] .'",';
						$sQuery .= ' "' . $aSalesDetail['price'] .'",';
						$sQuery .= ' "' . $aSalesDetail['sn_start'] .'",';
						$sQuery .= ' "' . $aSalesDetail['sn_end'] .'")';

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

			function UpdateDetail($aSalesDetail)
			{
				$iResult = 0;

				if ( is_array($aSalesDetail) ) //check that $aProduct is an array
				{
					foreach( $aSalesDetail as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ( $aSalesDetail['product_ID'] <= 0)
					{
						echo "cannot insert sales detail without proper product ID";
						die();
					}

					if ( $aSalesDetail['sn_start'] == "")
					{
						echo "cannot insert sales detail without proper serial number start";
						die();
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'UPDATE sales_detail';
						$sQuery .= ' SET `product_ID` ="' . $aSalesDetail['product_ID'] .'",';
						$sQuery .= ' `Quantity` ="' . $aSalesDetail['quantity'] .'",';
						$sQuery .= ' `Discount` ="' . $aSalesDetail['discount'] .'",';
						$sQuery .= ' `Price` ="' . $aSalesDetail['price'] .'",';
						$sQuery .= ' `SnStart` ="' . $aSalesDetail['sn_start'] .'",';
						$sQuery .= ' `SnEnd` ="' . $aSalesDetail['sn_end'] .'"';
						$sQuery .= ' WHERE ID = "' . $aSalesDetail['salesDetail_ID'] . '"';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							$iResult = $aSalesDetail['salesDetail_ID'];
						}
					//}
					return $iResult;
				}
			}

			function GetSalesDetailList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_detail';

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
						elseif ($key == "quantity" || $key == "sales_ID" || $key == "product_ID" || $key == "sales_order_detail_ID" || $key == "discount" || $key == "price")
						{
							$sQuery .= ' ' . $key . ' = "' . $value . '"';
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
				
				//$sQuery .= ' GROUP BY ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' ID ASC';
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

			function GetTotalSalesByProduct($iProductID, $sDate)
			{
				$sQuery  = 'SELECT sales_detail.product_ID AS productID, SUM(sales_detail.Quantity) AS quantity';
				$sQuery .= ' FROM sales, sales_detail';
				$sQuery .= ' WHERE sales.ID = sales_detail.sales_ID';
				$sQuery .= ' AND sales_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND sales.Date <= "' . $sDate . '"';
				$sQuery .= ' GROUP BY sales_detail.product_ID';

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

			function GetTotalSalesByProductAndOutlet($iProductID, $iOutletID, $sDate)
			{
				$sQuery  = 'SELECT sales_detail.product_ID AS productID, SUM(sales_detail.Quantity) AS quantity';
				$sQuery .= ' FROM sales, sales_detail';
				$sQuery .= ' WHERE sales.ID = sales_detail.sales_ID';
				$sQuery .= ' AND sales_detail.product_ID = "' . $iProductID . '"';
				$sQuery .= ' AND sales.outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND sales.Date <= "' . $sDate . '"';
				$sQuery .= ' GROUP BY sales_detail.product_ID';

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

// SALES ORDER BEGIN
			function InsertSalesOrder($aSales)
			{
				$iResult = 0;

				if ($aSales["outletID"] == 0)
				{
					echo "cannot insert sales without outletID";
					die();
				}

				$sQuery  = 'INSERT INTO sales_order';
				$sQuery .= ' (`outlet_ID`, `employee_ID`, `client_ID`, `paymentType_ID`, `Date`, `Notes`)';
				$sQuery .= ' VALUES ("' . $aSales["outletID"] . '"';
				$sQuery .= ' ,"' . $aSales["employeeID"] . '"';
				$sQuery .= ' ,"' . $aSales["clientID"] . '"';
				$sQuery .= ' ,"' . $aSales["paymentTypeID"] . '"';
				$sQuery .= ' ,"' . date("Ymd") . '"';
				$sQuery .= ' ,"' . $aSales["notes"] . '")';

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

			function UpdateSalesOrder($aSales)
			{
				$iResult = $aSales["ID"];
			
				$sQuery  = 'UPDATE sales_order';
				$sQuery .= ' SET `Notes` = "' . $aSales["notes"] . '"';
				$sQuery .= ' ,`employee_ID` = "' . $aSales["employeeID"] . '"';
				$sQuery .= ' ,`client_ID` = "' . $aSales["clientID"] . '"';
				$sQuery .= ' ,`paymentType_ID` = "' . $aSales["paymentTypeID"] . '"';
				$sQuery .= ' ,`Date` = "' . $aSales["date"] . '"';
				$sQuery .= ' WHERE ID = "' . $aSales["ID"] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				//any update to the sales order must be reflected in table sales
				$sQuery  = 'UPDATE sales';
				$sQuery .= ' SET `Notes` = "' . $aSales["notes"] . '"';
				$sQuery .= ' ,`employee_ID` = "' . $aSales["employeeID"] . '"';
				$sQuery .= ' ,`client_ID` = "' . $aSales["clientID"] . '"';
				$sQuery .= ' ,`paymentType_ID` = "' . $aSales["paymentTypeID"] . '"';
				$sQuery .= ' ,`Date` = "' . $aSales["date"] . '"';
				$sQuery .= ' WHERE sales_order_ID = "' . $aSales["ID"] . '"';

				$aResult = $this->dbAction($sQuery);

				return $iResult;
			}

			function GetSalesOrderList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT ID AS ID, Outlet_ID AS outletID, Date AS date, Notes AS notes, employee_ID AS employeeID, client_ID AS clientID, paymentType_ID AS paymentTypeID, Status';
				$sQuery .= ' FROM sales_order';

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
						elseif ($key == "outlet_ID")
						{
							$sQuery .= ' ' . $key . '=' . $value;
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
				
				//$sQuery .= ' GROUP BY ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' ID ASC';
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

			function GetSalesOrderByID($iSalesID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_order';
				$sQuery .= ' WHERE ID = "' . $iSalesID . '"';

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

			function GetSalesOrderWithDetail($iSalesID)
			{
				$sQuery  = 'SELECT sales_order.ID AS ID, outlet_ID, Date, Notes, employee_ID, client_ID, paymentType_ID, Status';
				$sQuery .= ' ,sales_order_detail.ID AS detail_ID, product.ID AS productID, product.Name AS productName, Quantity, Discount, sales_order_detail.Price';
				$sQuery .= ' ,SnStart, SnEnd';
				$sQuery .= ' FROM sales_order, sales_order_detail, product';
				$sQuery .= ' WHERE sales_order.ID = "' . $iSalesID . '"';
				$sQuery .= ' AND sales_order_detail.sales_order_ID = sales_order.ID';
				$sQuery .= ' AND product.ID = sales_order_detail.product_ID';

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

			function InsertSalesOrderDetail($aSalesDetail)
			{
				$iResult = 0;

				if ( is_array($aSalesDetail) ) //check that $aSalesDetail is an array
				{
					foreach( $aSalesDetail as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ( $aSalesDetail['sales_ID'] <= 0)
					{
						echo "cannot insert sales order detail without proper sales ID";
						die();
					}

					if ( $aSalesDetail['product_ID'] <= 0)
					{
						echo "cannot insert sales order detail without proper product ID";
						die();
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO sales_order_detail';
						$sQuery .= ' (`sales_order_ID`, `product_ID`, `Quantity`, `Discount`, `Price`, `SnStart`, `SnEnd`)';
						$sQuery .= ' VALUES ("' . $aSalesDetail['sales_ID'] .'",';
						$sQuery .= ' "' . $aSalesDetail['product_ID'] .'",';
						$sQuery .= ' "' . $aSalesDetail['quantity'] .'",';
						$sQuery .= ' "' . $aSalesDetail['discount'] .'",';
						$sQuery .= ' "' . $aSalesDetail['price'] .'",';
						$sQuery .= ' "' . $aSalesDetail['sn_start'] .'",';
						$sQuery .= ' "' . $aSalesDetail['sn_end'] .'")';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							$iResult = $this->dbLink->lastInsertId();

							//we need to query all necessary tables to get the information
							//we need the sales.ID to insert into sales detail
							//we need the sales_order_detail_ID to insert into sales detail
							$aSalesParam = array(
								"sales_order_ID" => $aSalesDetail['sales_ID']
							);
							$aSalesData = $this->GetSalesList($aSalesParam);

							//we only insert into sales_detail if sales_ID existed.
							if ( count($aSalesData) == 1 )
							{
								$sQuery  = 'INSERT INTO sales_detail';
								$sQuery .= ' (`sales_order_detail_ID`, `sales_ID`, `product_ID`, `Quantity`, `Discount`, `Price`, `SnStart`, `SnEnd`)';
								$sQuery .= ' VALUES ("' . $iResult .'",';
								$sQuery .= ' "' . $aSalesData[0]['ID'] .'",';
								$sQuery .= ' "' . $aSalesDetail['product_ID'] .'",';
								$sQuery .= ' "' . $aSalesDetail['quantity'] .'",';
								$sQuery .= ' "' . $aSalesDetail['discount'] .'",';
								$sQuery .= ' "' . $aSalesDetail['price'] .'",';
								$sQuery .= ' "' . $aSalesDetail['SnStart'] .'",';
								$sQuery .= ' "' . $aSalesDetail['SnEnd'] .'")';
	
								$aResult = $this->dbAction($sQuery);
							}
						}
					//}

					return $iResult;
				}
			}

			function UpdateSalesOrderDetail($aSalesDetail)
			{
				$iResult = 0;

				if ( is_array($aSalesDetail) ) //check that $aProduct is an array
				{
					foreach( $aSalesDetail as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					if ( $aSalesDetail['product_ID'] <= 0)
					{
						echo "cannot update sales order detail without proper product ID";
						die();
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'UPDATE sales_order_detail';
						$sQuery .= ' SET `product_ID` ="' . $aSalesDetail['product_ID'] .'",';
						$sQuery .= ' `Quantity` ="' . $aSalesDetail['quantity'] .'",';
						$sQuery .= ' `Discount` ="' . $aSalesDetail['discount'] .'",';
						$sQuery .= ' `Price` ="' . $aSalesDetail['price'] .'",';
						$sQuery .= ' `SnStart` ="' . $aSalesDetail['sn_start'] .'",';
						$sQuery .= ' `SnEnd` ="' . $aSalesDetail['sn_end'] .'"';
						$sQuery .= ' WHERE ID = "' . $aSalesDetail['salesDetail_ID'] . '"';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == FALSE)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							$iResult = $aSalesDetail['salesDetail_ID'];

							$sQuery  = 'UPDATE sales_detail';
							$sQuery .= ' SET `product_ID` ="' . $aSalesDetail['product_ID'] .'",';
							$sQuery .= ' `Quantity` ="' . $aSalesDetail['quantity'] .'",';
							$sQuery .= ' `Discount` ="' . $aSalesDetail['discount'] .'",';
							$sQuery .= ' `Price` ="' . $aSalesDetail['price'] .'"';
							$sQuery .= ' `SnStart` ="' . $aSalesDetail['sn_start'] .'"';
							$sQuery .= ' `SnEnd` ="' . $aSalesDetail['sn_end'] .'"';
							$sQuery .= ' WHERE sales_order_detail_ID = "' . $aSalesDetail['salesDetail_ID'] . '"';
	
							$aResult = $this->dbAction($sQuery);
						}
					//}
					return $iResult;
				}
			}

			function GetSalesOrderDetailByID($iSalesID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_order_detail';
				$sQuery .= ' WHERE ID = "' . $iSalesID . '"';

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

			function GetSalesOrderDetailByDetailID($iID)
			{
				$sQuery  = 'SELECT sales_order.ID AS ID, outlet_ID, Date, Notes, employee_ID, client_ID, paymentType_ID, Status';
				$sQuery .= ' ,sales_order_detail.ID AS detail_ID, product.ID AS productID, product.Name AS productName, Quantity, Discount, sales_order_detail.Price';
				$sQuery .= ' , SnStart, SnEnd';
				$sQuery .= ' FROM sales_order, sales_order_detail, product';
				$sQuery .= ' WHERE sales_order_detail.ID = "' . $iID . '"';
				$sQuery .= ' AND sales_order_detail.sales_order_ID = sales_order.ID';
				$sQuery .= ' AND product.ID = sales_order_detail.product_ID';

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

			function GetSalesOrderReport( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_order, sales_order_detail';
				$sQuery .= ' WHERE sales_order.ID = sales_order_detail.sales_order_ID';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray); this is disabled because it is
				//possible for outlet_ID to be the same as client_ID or employee_ID, in which case, the last
				//data will be removed, making the query all wrong.
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
							case "sales_order.ID":
								if (trim($value) != "" && trim($value) > 0) //if not empty
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
							case "product_ID" :
							case "sales_order_detail.product_ID" :
								if (trim($value) != "" && trim($value)) //if not empty
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
									$sQuery .= ' AND (' . $key . '="' . $value . '"';
									//must create a query to get all master_outlet_ID
									$cOutlet = new Outlet;
									$aOutletWithMasterList = $cOutlet->GetActiveOutletWithMasterOutletList($value);
									if (count($aOutletWithMasterList) > 0)
									{
										for ($i = 0; $i < count($aOutletWithMasterList); $i++)
										{
											$sQuery .= ' OR ' . $key . '="' . $aOutletWithMasterList[$i]['ID'] . '"';
										}
									}
									$sQuery .= ')';
								}
							break;
							case "client_ID" :
							case "sales_order.client_ID":
								if (trim($value) != "" && trim($value) > 0 ) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "employee_ID" :
							case "sales_order.employee_ID":
								if (trim($value) != "" && trim($value) > 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "paymentType_ID" :
							case "sales_order.paymentType_ID":
								if (trim($value) != "" && trim($value) > 0 ) //if not empty
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

				//$sQuery .= ' GROUP BY outlet_ID';

				//sort by
				$sQuery .= ' ORDER BY';
				if (isset($aOutletWithMasterList) && count($aOutletWithMasterList) > 0)
				{
					$sQuery .= ' outlet_ID ASC,';
				}
				$sQuery .= ' sales_order.Date DESC';

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

			function GetSalesOrderReportByFinanceArea( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_order, sales_order_detail';
				$sQuery .= ' WHERE sales_order.ID = sales_order_detail.sales_order_ID';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray); this is disabled because it is
				//possible for outlet_ID to be the same as client_ID or employee_ID, in which case, the last
				//data will be removed, making the query all wrong.
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
							case "sales_order.ID":
								if (trim($value) != "" && trim($value) > 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "product_ID" :
							case "sales_order_detail.product_ID" :
								if (trim($value) != "" && trim($value) > 0) //if not empty
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
									$sQuery .= ' AND (' . $key . '="' . $value . '"';
									//must create a query to get all master_outlet_ID
									$cOutlet = new Outlet;
									$aOutletWithMasterList = $cOutlet->GetActiveOutletWithMasterOutletList($value);
									if (count($aOutletWithMasterList) > 0)
									{
										for ($i = 0; $i < count($aOutletWithMasterList); $i++)
										{
											$sQuery .= ' OR ' . $key . '="' . $aOutletWithMasterList[$i]['ID'] . '"';
										}
									}
									$sQuery .= ')';
								}
							break;
							case "client_ID" :
							case "sales_order.client_ID":
								if (trim($value) != "" && trim($value) > 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "employee_ID" :
							case "sales_order.employee_ID":
								if (trim($value) != "" && trim($value) > 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							case "paymentType_ID" :
							case "sales_order.paymentType_ID":
								if (trim($value) != "" && trim($value) > 0) //if not empty
								{
									$sQuery .= ' AND ' . $key . '="' . $value . '"';
								}
							break;
							default:
								if ($key != "AllOutlet")
								{
									if (trim($value) != "") //if not empty
									{
										$sQuery .= ' AND ' . $key . ' like "%' . $value . '%"';
									}
								}
							break;
						}
					}
					if (!in_array("outlet_ID", $aSearchByFieldArray))
					{
						$sQuery .= ' AND (';
						for ( $i = 0; $i < count($aSearchByFieldArray["AllOutlet"]); $i++ )
						{
							$sQuery .= ' outlet_ID = "' . $aSearchByFieldArray['AllOutlet'][$i]['ID'] . '" OR ';
						}
						$sQuery = substr($sQuery, 0, strlen($sQuery)-3);
						$sQuery .= ')';
					}
				}

				//$sQuery .= ' GROUP BY outlet_ID';

				//sort by
				$sQuery .= ' ORDER BY';
				if (count($aOutletWithMasterList) > 0)
				{
					$sQuery .= ' outlet_ID ASC,';
				}
				$sQuery .= ' sales_order.Date DESC';

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

			function VerifySalesOrder($iID, $sNotes)
			{
				$sQuery  = 'UPDATE sales_order';
				$sQuery .= ' SET `Status` = "2"';//set the status to "in progress"
				$sQuery .= ' ,`FinanceNotes` = "' . $sNotes . '"';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
				else
				{
					//if result success, then we create a sales and sales_detail record
					//first we get the sales_order ID, get all the fields and input to sales
					//next we get all sales_order_detail related and input to sales_detail

					//here we go, put sales_order into sales
					//we loop until it is successful
					$iSalesID = 0;
					do
					{
						$aSalesOrderRecords = $this->GetSalesOrderByID($iID);
						$aSales = array(
							"outletID" => $aSalesOrderRecords[0]["outlet_ID"],
							"salesOrderID" => $aSalesOrderRecords[0]["ID"],
							"employeeID" => $aSalesOrderRecords[0]["employee_ID"],
							"clientID" => $aSalesOrderRecords[0]["client_ID"],
							"paymentTypeID" => $aSalesOrderRecords[0]["paymentType_ID"],
							"ajaxPostID" => 0,
							"notes" => $aSalesOrderRecords[0]["Notes"]
							
						);
						//check the exact records does not exists before
						$aSalesRecordExists = $this->GetSalesList($aSales);

						if ( $aSalesRecordsExists <> FALSE)
						{
							$iSalesID = $aSalesRecordExists[0]['ID'];
						}
						else
						{
							$iSalesID = $this->Insert($aSales);
						}
					}
					while($iSalesID == 0);

					//now we input the sales_detail records
					//we know the $iSalesID is > 0, so we do not check anymore
					//we get all the sales_order_detail based on sales_order.ID
					$aSearchParam = array(
						"sales_order.ID" => $iID
					);
					$aSalesOrderDetail = $this->GetSalesOrderWithDetail($iID);

					//we only insert if there are sales_order_detail records attached to the sales_order
					if ( count($aSalesOrderDetail) > 0 )
					{
						//we loop all the records
						for ($i = 0; $i < count($aSalesOrderDetail); $i++)
						{
							//we loop until successful
							$iSalesDetailID = 0;
							do
							{
								$aSalesOrderDetailRecords = $this->GetSalesOrderDetailByID($aSalesOrderDetail[$i]['detail_ID']);
								$aSalesDetail = array(
									"sales_ID" => $iSalesID,
									"sales_order_detail_ID" => $aSalesOrderDetailRecords[0]["ID"],
									"product_ID" => $aSalesOrderDetailRecords[0]["product_ID"],
									"quantity" => $aSalesOrderDetailRecords[0]["Quantity"],
									"discount" => $aSalesOrderDetailRecords[0]["Discount"],
									"price" => $aSalesOrderDetailRecords[0]["Price"],
									"sn_start" => $aSalesOrderDetailRecords[0]["SnStart"],
									"sn_end" => $aSalesOrderDetailRecords[0]["SnEnd"],
								);
								//check the exact records does not exists before
								$aSalesDetailRecordExists = $this->GetSalesDetailList($aSalesDetail);

								if ( $aSalesDetailRecordExists <> FALSE )
								{
									$iSalesDetailID = $aSalesDetailRecordExists[0]['ID'];
								}
								else
								{
									$iSalesDetailID = $this->InsertDetail($aSalesDetail);
								}
							}
							while ($iSalesDetailID == 0);
						}
					}
				}

				//when all the above process is complete, then we set the status to verified
				$sQuery  = 'UPDATE sales_order';
				$sQuery .= ' SET `Status` = "1"';//set the status to "verified"
				$sQuery .= ' ,`FinanceNotes` = "' . $sNotes . '"';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				return $iResult;
			}

// SALES ORDER END




/*
			

			

			

			

			

			

			

			function GetSalesDetailList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_detail';

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
				
				$sQuery .= ' GROUP BY ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' ID ASC';
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
*/

			/*** SALES PAYMENT ***/
			function SaveSalesPayment($aData)
			{
				$iResult = 0;

				//remember to reset the bank_ID to 0 if it is cash payment
				if ($aData['IsCash'] == 1)
				{
					$aData['bank_ID'] = 0;
				}

				if ($aData['ID'] == 0)
				{
					$sQuery  = 'INSERT INTO sales_payment';
					$sQuery .= ' (`sales_ID`, `Date`, `Amount`, `Notes`, `IsCash`, `bank_ID`)';
					$sQuery .= ' VALUES ("' . $aData["sales_ID"] . '"';
					$sQuery .= ' ,"' . $aData['Date'] . '"';
					$sQuery .= ' ,"' . $aData["Amount"] . '"';
					$sQuery .= ' ,"' . $aData["Notes"] . '"';
					$sQuery .= ' ,"' . $aData["IsCash"] . '"';
					$sQuery .= ' ,"' . $aData["bank_ID"] . '")';
				}
				else
				{
					$sQuery  = 'UPDATE sales_payment SET';
					$sQuery .= ' `sales_ID` = "' . $aData["sales_ID"] . '",';
					$sQuery .= ' `Date` = "' . $aData["Date"] . '",';
					$sQuery .= ' `Amount` = "' . $aData["Amount"] . '",';
					$sQuery .= ' `Notes` = "' . $aData["Notes"] . '",';
					$sQuery .= ' `IsCash` = "' . $aData["IsCash"] . '",';
					$sQuery .= ' `bank_ID` = "' . $aData["bank_ID"] . '"';
					$sQuery .= ' WHERE ID = "' . $aData['ID'] . '"';
				}

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
				else
				{
					//now we have to either insert or update the deposit / bank_deposit tables
					if ($aData['ID'] == 0)
					{
						$iResult = $this->dbLink->lastInsertId();
					}
					else
					{
						$iResult = $aData['ID'];
					}

					//retrieve the sales Data
					$aSalesData = $this->GetSalesWithDetail($aData["sales_ID"]);

					//we check if there is already a data on both deposit and bank_deposit tables
					//sales_payment and deposit is 1:1 relationship
					include_once('classDeposit.php');
					$cDeposit = new Deposit;
					$aSearchArray = array(
						"salesPayment_ID" => $iResult
					);
					$aDeposit = $cDeposit->GetDepositList($aSearchArray);

					//get the ID of related bank_deposit
					$aSearch = array(
						"salesPayment_ID" => " = " . $iResult
					);
					include_once('classBank.php');
					$cBank = new Bank;
					$aBank = $cBank->GetDepositList($aSearch);

					//now we have to either insert or update the deposit / bank_deposit tables
					if ($aData['bank_ID'] == 0)
					//is cash
					{
						$aDepositData = array(
							"outlet_ID" => $aSalesData[0]['outlet_ID'],
							"Notes" => $aData['Notes'],
							"Price" => $aData['Amount'],
							"Date" => $aData['Date'],
							"salesPayment_ID" => $iResult
						);

						//insert/update deposit
						if ( count($aDeposit) > 0 )
						{
							//update deposit
							$aDepositData['ID'] = $aDeposit[0]['ID'];
							$cDeposit->Update($aDepositData);
						}
						else
						{
							//insert into deposit
							$cDeposit->Insert($aDepositData);
						}

						//check if there is a bank_deposit data, if yes, delete it
						if ( count($aBank) > 0)
						{
							$cBank->RemoveDeposit($aBank[0]['ID']);
						}
					}
					else
					//is bank deposit
					{
						$aBankDeposit = array(
							'outlet_ID'=> $aSalesData[0]['outlet_ID'],
							'bank_ID' => $aData["bank_ID"],
							'salesPayment_ID' => $iResult,
							'Notes' => $aData['Notes'],
							'Price' => $aData['Amount'],
							'Date' => $aData['Date']
						);

						//insert/update bank_deposit
						if ( count($aBank) > 0 )
						{
							$aBankDeposit['ID'] = $aBank[0]['ID'];
						}

						$cBank->SaveDeposit($aBankDeposit);

						//check if there is a deposit data, if yes, delete it
						if ( count($aDeposit) > 0)
						{
							$cDeposit->Remove($aDeposit[0]['ID']);
						}
					}
				}

				return $iResult;
			}

			function RemoveSalesPayment($iID)
			{
				$sQuery  = 'DELETE FROM sales_payment ';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
				else
				{
					//remove the corresponding deposit and bank deposit data
					$sQuery  = 'DELETE FROM deposit ';
					$sQuery .= ' WHERE salesPayment_ID = "' . $iID . '"';
	
					$aResult = $this->dbAction($sQuery);
					
					$sQuery  = 'DELETE FROM bank_deposit ';
					$sQuery .= ' WHERE salesPayment_ID = "' . $iID . '"';
	
					$aResult = $this->dbAction($sQuery);
				}
			}

			public function Load($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_payment';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult;
			}

			function ListSalesPayment($aParam)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM sales_payment';
				$sQuery .= ' WHERE 1';

				$sCountQuery = $sQuery;

				//by default we assume it is a = query
				//if passed ">", "<", "LIKE", "BETWEEN" in the value
				//then we redo the = query
				//$aSearch parameter is field and value
				//value contains the necessary notation
				if ( count($aParam) > 0 )
				{
					$sGroupQuery = '';
					$sOrderByQuery = '';
					$sLimitQuery = '';
					foreach ($aParam as $field => $value)
					{
						if (substr_count($field, 'GROUP BY') > 0)
						{
							$sGroupQuery = ' ' . $field . ' ' . $value;
						}
						elseif (substr_count($field, 'ORDER BY') > 0)
						{
							$sOrderByQuery = ' ' . $field . ' ' . $value;
						}
						elseif (substr_count($field, 'LIMIT') > 0)
						{
							$sLimitQuery = ' ' . $field . ' ' . $value;
						}
						else
						{
							$sQuery .= ' AND ' . $field . ' ' . $value;
						}
					}
					$sCountQuery = $sQuery . $sGroupQuery . $sOrderByQuery;
					$sQuery = $sCountQuery . $sLimitQuery;
				}

				$aResult = $this->dbQuery($sQuery);

				//get the total amount of data inside the query
				$iResultCount = count($this->dbQuery($sCountQuery));

				//TODO:check result is valid
				if ( count($aResult) > 0 )
				{
					$aResult[0]['Count'] = $iResultCount;
				}

				return $aResult;
			}

			function generateSalesNumber($iOutletID, $iID = "")
			{
				//kode cabang + bulan (2 digit) + tahun (4 digit) + 4 digit nomor
				//valid cabang: hobk, somj, sock, bekasi, gudang bdg, gudang jkt

				//search for parent of $iOutletID
				include_once("classOutlet.php");
				$cOutlet = new FSR_Outlet;
				$iOutletParentID = $cOutlet->getParentID($iOutletID);

				//get all outlet list under $iOutletParentID
				$sOutletList = "";
				$aOutlet = $cOutlet->listOutlet(array("master_outlet_ID" => " = '". $iOutletParentID ."'"));

				foreach ($aOutlet as $aRow)
				{
					$sOutletList .= "'". $aRow['ID'] ."',";
				}

				$sOutletList = substr($sOutletList, 0, -1);

				//get child outlet
				$sOutletQuery = " AND outlet_ID IN('". $iOutletParentID ."', ". $sOutletList .")";

				//put this here, to avoid corruption during process above.
				$cOutlet->getOutlet($iOutletParentID);

				$iSalesNumber = 1;

				//check and auto create sales.number
				$sExtraQuery = "";
				if ($iID > 0)
				{
					$sQuery = "SELECT Date FROM sales WHERE ID = '". $iID ."'";
					$aResult = $this->dbQuery($sQuery);

					if ( count($aResult) )
					{
						$iDateBegin = date("Y-m", strtotime($aResult[0]['Date'])) . "-01";
						$sExtraQuery = " AND ID < '". $iID ."' ";
					}
				}
				else
				{
					$iDateBegin = date("Y-m") . "-01";
				}

				$iDateEnd = date("Y-m", strtotime("+1 month", strtotime($iDateBegin))) . "-01";

				$sQuery = "SELECT ID, number FROM sales WHERE sales.Date >= '". $iDateBegin ."' AND sales.Date < '". $iDateEnd ."' ". $sExtraQuery . $sOutletQuery . " ORDER BY ID DESC LIMIT 0,1";

				$aResult = $this->dbQuery($sQuery);

				if ( count($aResult) )
				{
					$iNumber = substr($aResult[0]['number'], -4, 4);

					$iSalesNumber = $iNumber + 1;
				}

				$iSalesNumber = $this->zeroPadding($iSalesNumber, 4);

				$iSalesNumber = date("mY", strtotime($iDateBegin)) . $iSalesNumber;

				$iSalesNumber = substr(str_replace(" ", "", $cOutlet->getProperty("code")),0,4) . $iSalesNumber;

				return $iSalesNumber;
			}

			function zeroPadding($iNumber, $iPadding)
			{
				while (strlen($iNumber) < $iPadding)
				{
					$iNumber = '0' . $iNumber;
				}

				return $iNumber;
			}

			function resetNumbering()
			{
				$sQuery = "UPDATE sales SET number = ''";
				$aResult = $this->dbAction($sQuery);

				$sQuery = "SELECT ID, outlet_ID FROM sales ORDER BY ID ASC";
				$aResult = $this->dbQuery($sQuery);

				foreach ($aResult as $aRow)
				{
					set_time_limit(0);

					$newNumber = $this->generateSalesNumber($aRow['outlet_ID'], $aRow['ID']);

					$sQuery  = "UPDATE sales SET ";
					$sQuery .= " number = '". $newNumber ."'";
					$sQuery .= " WHERE ID = '". $aRow['ID'] ."'";

					$this->dbAction($sQuery);
				}
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
