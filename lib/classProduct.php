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
	* lib/classProduct.php :: PRODUCT CLASS											*
	*************************************************************************
	* Product object																			*
	*																								*
	* List of Tables :																		*
	* product				: stores product information								*
	* product_category	: stores product category information					*
	*																								*
	* Version			: 2																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2007-10-13 														*
	* Last modified	: 2015-02-10														*
	* 																								*
	************************************************************************/

	if ( !class_exists('FSR_Product') )
	{
		class FSR_Product
		{
			//product
			private $ID;
			private $productCategory_ID;
			private $Name;
			private $Description;
			private $Deleted;
			private $Created;
			private $Modified;
			private $productCategory_Name;
			//product category
			private $parent_ID; //redundant, remove this when database field changes to parentID
			private $parentID;
			//tables
			private $tableProduct;
			private $tableProductCategory;
			private $db;

			public function __construct()
			{
				require_once("classDatabase.php");

				include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
				include_once($rootPath . "config.php");

				$this->db = new FSR_Database(_DBTYPE_, _DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_);

				$this->tableProduct = "product";
				$this->tableProductCategory = "product_category";
			}

			public function __destruct()
			{
				
			}

			public function getProperty($sProperty)
			{
				return $this->$sProperty;
			}

			public function setProperty($sProperty, $mValue)
			{
				$this->$sProperty = $mValue;
			}

			public function getProduct($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, $this->tableProduct);

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}
				//get product category name
				$this->productCategory_Name = $this->getNameByCategory($this->productCategory_ID);
			}

			public function setProduct()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields($this->tableProduct)) )
					{
						$param[$key] = $value;
					}
				}

				return $this->db->dbSave($param, $this->tableProduct);
			}

			public function deleteProduct($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getProduct($iID);
				$this->Deleted = 1;

				return $this->setProduct();
			}

			public function restoreProduct($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getProduct($iID);
				$this->Deleted = 0;

				return $this->setProduct();
			}

			public function listActiveProduct($aData=array())
			{
				$param = array(
					"fields" => "product.ID AS ID, product.Name AS Name, product_category.Name AS categoryName",
					"tables" => $this->tableProduct . ", " . $this->tableProductCategory,
					"query" => " product.productCategory_ID = product_category.ID AND Deleted = '0'"
				);
				return $this->db->dbSearch($param);
			}

			public function listProduct($aData=array())
			{
				$result = FALSE;

				$param = array(
					"fields" => "ID",
					"tables" => $this->tableProduct
				);
				if ( count($aData) > 0 )
				{
					$param["query"] = "";
					foreach ($aData as $key => $value)
					{
						$param["query"] .= $key . " " . $value . " AND ";
					}
					$param['query'] = substr($param['query'], 0, -5);
				}

				$aSearchResult = $this->db->dbSearch($param);

				if ($aSearchResult != FALSE)
				{
					$result = array();
					foreach ($aSearchResult as $aResultRow)
					{
						$this->getProduct($aResultRow['ID']);

						$result[] = array(
							"ID" => $this->ID,
							"productCategory_ID" => $this->productCategory_ID,
							"Name" => $this->Name,
							"Description" => $this->Description,
							"Deleted" => $this->Deleted,
							"Created" => $this->Created,
							"Modified" => $this->Modified,
							"categoryName" => $this->productCategory_Name
						);
					}
				}

				return $result;
			}

			//product category
			public function getCategory($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, $this->tableProductCategory);

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}
				$this->parentID = $this->parent_ID;
			}

			public function setCategory()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields($this->tableProductCategory)) )
					{
						$param[$key] = $value;
					}
				}

				return $this->db->dbSave($param, $this->tableProductCategory);
			}

			public function deleteCategory($iID)
			{
				return $this->db->dbDelete($iID, $this->tableProductCategory);
			}

			public function listCategory($aData=array())
			{
				$param = array(
					"fields" => "*",
					"tables" => $this->tableProductCategory
				);
				return $this->db->dbSearch($param);
			}

			public function getNameByCategory($iID)
			{
				$sResult = FALSE;

				$param = array(
					"fields" => "Name",
					"tables" => $this->tableProductCategory,
					"query" => "ID = '" . $iID . "'"
				);
				$aSearchResult = $this->db->dbSearch($param);
				if ($aSearchResult)
				{
					$sResult = $aSearchResult[0]['Name'];
				}
				return $sResult;
			}

			public function getCategoryByName($sName)
			{
				$sResult = FALSE;

				$param = array(
					"fields" => "ID",
					"tables" => $this->tableProductCategory,
					"query" => "Name LIKE '%" . $sName . "%'"
				);
				$sResult = $this->db->dbSearch($param);

				return $sResult;
			}
		}
	}

	if ( !class_exists('Product') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Product extends Database
		{
			var $ID				= FALSE;
			var $CategoryID		= FALSE;
			var $Name			= FALSE;
			var $Price			= FALSE;
			var $Image			= FALSE;
			var $Thumbs			= FALSE;
			var $Driver			= FALSE;
			var $Viewable		= FALSE;
			var $viewPriority	= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Product($iProductID = 0)
			// Insert($aProduct)
			// Update($aProduct)
			// Remove($iProductID)
			// GetProductList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetProductByID($iProductID)
			// GetProductByName($sProductName)
			// GetNextPrevIDByCurrentID($sDirection = "next", $iProductID)
			// GetProductByCategory( $iCategoryID )
			// GetVisibleProductByCategory( $iCategoryID )
			// InsertCategory($aCategory)
			// UpdateCategory($aCategory)
			// RemoveCategory($iCategoryID)
			// GetCategoryList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetCategoryByID( $iCategoryID )
			// GetCategoryByName( $sCategoryName )
			// InsertSpecification($aSpecification)
			// UpdateSpecification($aSpecification)
			// RemoveSpecification($iSpecificationID)
			// GetProductSpecificationByProductID( $iProductID )
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Product($iProductID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iProductID) && $iProductID > 0 ) //check $iProductID is numeric and positive value
					{
						$aProduct = $this->GetProductByID($iProductID);

						if (is_array($aProduct) && count($aProduct) == 1) //check $aProduct is an array and has exactly one data
						{
							$this->ID = $aProduct[0]['ID'];
							$this->CategoryID = $aProduct[0]['productCategory_ID'];
							$this->Name = $aProduct[0]['Name'];
							$this->Price = $aProduct[0]['Price'];
							$this->Description = $aProduct[0]['Description'];
							$this->Image = $aProduct[0]['Image'];
							$this->Thumbs = $aProduct[0]['Thumbs'];
							$this->Driver = $aProduct[0]['Driver'];
							$this->Viewable = $aProduct[0]['Viewable'];
							$this->ViewPriority = $aProduct[0]['ViewPriority'];
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
						if ( $iProductID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iProductID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aProduct)
			{
				if ( is_array($aProduct) ) //check that $aProduct is an array
				{
					foreach( $aProduct as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO product';
						$sQuery .= ' (`productCategory_ID`, `Name`, `Price`, `Description`, `Image`, `Thumbs`, `Driver`, `Viewable`, `ViewPriority`, `Created`, `Modified`)';
						$sQuery .= ' VALUES ("' . ( ( array_key_exists("productCategory_ID", $aProduct) )?$aProduct["productCategory_ID"]:"0" ) . '",';
						$sQuery .= ' "' . $aProduct['Name'] .'",';
						$sQuery .= ' "' . $aProduct['Price'] .'",';
						$sQuery .= ' "' . $aProduct['Description'] .'", ';
						$sQuery .= ' "' . ( ( array_key_exists("Image", $aProduct) )?$aProduct["Image"]:NULL ) .'",';
						$sQuery .= ' "' . ( ( array_key_exists("Thumbs", $aProduct) )?$aProduct["Thumbs"]:NULL ) .'",';
						$sQuery .= ' "' . ( ( array_key_exists("Driver", $aProduct) )?$aProduct["Driver"]:NULL ) .'",';
						$sQuery .= ' "' . ( ( array_key_exists("Viewable", $aProduct) )?$aProduct["Viewable"]:"1" ) .'",';
						$sQuery .= ' "' . ( ( array_key_exists("ViewPriority", $aProduct) )?$aProduct["ViewPriority"]:"" ) .'",';
						$sQuery .= ' "' . date('YmdHis') .'",';
						$sQuery .= ' "' . date('YmdHis') .'")';

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
			
			function Update($aProduct)
			{
				$aResult = 0;
				if ( is_array($aProduct) ) //check that $aProduct is an array
				{
					foreach( $aProduct as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					$sQuery  = 'UPDATE product';
					$sQuery .= ' SET `productCategory_ID` = "' . $aProduct['productCategory_ID'] . '",';
					$sQuery .= ' `Name` = "' . $aProduct['Name'] . '",';
					$sQuery .= ' `Price` = "' . $aProduct['Price'] . '",';
					$sQuery .= ' `Description` = "' . $aProduct['Description'] . '",';
					$sQuery .= ' `Image` = "' . $aProduct['Image'] . '",';
					$sQuery .= ' `Thumbs` = "' . $aProduct['Thumbs'] . '",';
					$sQuery .= ' `Driver` = "' . $aProduct['Driver'] . '",';
					$sQuery .= ' `Viewable` = "' . $aProduct['Viewable'] . '",';
					$sQuery .= ' `ViewPriority` = "' . $aProduct['ViewPriority'] . '",';
					$sQuery .= ' `Modified` = "' . date('YmdHis') . '"';
					$sQuery .= ' WHERE `ID` = "' . $aProduct['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}
			
			function Remove($iProductID)
			{
				include("dirConf.php");

				//check for child (specification, setting, faq)
				
				//check for files (img, thumbs, driver)
				$aProduct = $this->GetProductByID($iProductID);
				if ( $aProduct[0]['Image'] && file_exists($imgPath . "/" . $aProduct[0]['Image'])  )
				{
					unlink( $imgPath . "/" . $aProduct[0]['Image'] );
				}
				if ( $aProduct[0]['Thumbs'] && file_exists($imgPath . "/thumbs/" . $aProduct[0]['Thumbs']) )
				{
					unlink( $imgPath . "/thumbs/" . $aProduct[0]['Thumbs'] );
				}
				if ( $aProduct[0]['Driver'] && file_exists($driverPath . "/" . $aProduct[0]['Driver']) )
				{
					unlink( $driverPath . "/" . $aProduct[0]['Driver'] );
				}
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM product';
					$sQuery .= ' WHERE product.ID = "' . $iProductID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}

				//remember that deleting a product means deleting everything related, including Purchase, Sales, etc.
				//related tables: purchase_detail, sales_detail, sales_order_detail, transfer_detail

				return $aResult;
			}

			function GetProductList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT product.ID AS ID, product.Name AS name, product.Price AS price, product.Description AS description, product.Thumbs AS thumbnail, product.ViewPriority AS ViewPriority,';
				$sQuery .= ' product_category.Name AS category ';
				$sQuery .= ' FROM product LEFT JOIN product_category ON product.productCategory_ID = product_category.ID';

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
						if ($key == "product_category.ID")
						{
							if ($value == "0")
							{
								$sQuery .= " 1 = 1";
							}
							else
							{
								$sQuery .= ' ' . $key . ' = "' . $value . '"';
							}
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
				
				$sQuery .= ' GROUP BY product.ID';

				//sort by
				$sQuery .= ' ORDER BY';

				if ( count($aSortByArray) > 0 )
				{
					$bFirstTime = TRUE;
					foreach($aSortByArray as $key => $value)
					{
						if ( $bFirstTime == FALSE )
						{
							$sQuery .= ',';
						}

						$sQuery .= ' ' . $key . ' ' . $value;
						
						$bFirstTime = FALSE;
					}
				}
				else
				{
					//$sQuery .= ' product.ViewPriority ASC';
					$sQuery .= ' product.Name ASC';
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

			function GetProductListExact( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT product.ID AS ID, product.Name AS name, product.Price AS price, product.Description AS description, product.Thumbs AS thumbnail, product.ViewPriority AS ViewPriority,';
				$sQuery .= ' product_category.Name AS category ';
				$sQuery .= ' FROM product LEFT JOIN product_category ON product.productCategory_ID = product_category.ID';

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
						if ($key == "product_category.ID")
						{
							if ($value == "0")
							{
								$sQuery .= " 1 = 1";
							}
							else
							{
								$sQuery .= ' ' . $key . ' = "' . $value . '"';
							}
						}
						else
						{
							$sQuery .= ' ' . $key . ' = "' . $value . '"';
						}
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY product.ID';

				//sort by
				$sQuery .= ' ORDER BY';

				if ( count($aSortByArray) > 0 )
				{
					$bFirstTime = TRUE;
					foreach($aSortByArray as $key => $value)
					{
						if ( $bFirstTime == FALSE )
						{
							$sQuery .= ',';
						}

						$sQuery .= ' ' . $key . ' ' . $value;
						
						$bFirstTime = FALSE;
					}
				}
				else
				{
					//$sQuery .= ' product.ViewPriority ASC';
					$sQuery .= ' product.Name ASC';
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

			function GetProductListForSalesRetail( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT product.ID AS ID, product.Name AS name, product.Price AS price, product.Description AS description, product.Thumbs AS thumbnail, product.ViewPriority AS ViewPriority,';
				$sQuery .= ' product_category.Name AS category ';
				$sQuery .= ' FROM product LEFT JOIN product_category ON product.productCategory_ID = product_category.ID';

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
						if ($key == "product_category.ID")
						{
							if ($value == "0")
							{
								$sQuery .= " 1 = 1";
							}
							else
							{
								$sQuery .= ' ' . $key . ' = "' . $value . '"';
							}
						}
						elseif($key == "outlet_ID")
						{
							$sQuery .= " 1 = 1"; //do not use this field
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
				
				$sQuery .= ' GROUP BY product.ID';

				//sort by
				$sQuery .= ' ORDER BY';

				if ( count($aSortByArray) > 0 )
				{
					$bFirstTime = TRUE;
					foreach($aSortByArray as $key => $value)
					{
						if ( $bFirstTime == FALSE )
						{
							$sQuery .= ',';
						}

						$sQuery .= ' ' . $key . ' ' . $value;
						
						$bFirstTime = FALSE;
					}
				}
				else
				{
					//$sQuery .= ' product.ViewPriority ASC';
					$sQuery .= ' product.Name ASC';
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

				$iOutletID = 0;
				$iOutletID = $aSearchByFieldArray["outlet_ID"];

				if ($iOutletID > 0 )
				{
					$aAvailableProduct = array();
					
					$sQuery  = 'SELECT purchase_detail.product_ID as productID';
					$sQuery .= ' FROM purchase, purchase_detail';
					$sQuery .= ' WHERE purchase.ID = purchase_detail.purchase_ID';
					$sQuery .= ' AND purchase.outlet_ID = "' . $iOutletID . '"';
					$sQuery .= ' GROUP BY purchase_detail.product_ID';
	
					$aResult_purchase = $this->dbQuery($sQuery);

					foreach ($aResult_purchase as $key => $value)
					{
						foreach ($value as $key2 => $value2)
						{
							if ( !in_array($value2, $aAvailableProduct) )
							{
								$aAvailableProduct[] = $value2;
							}

						}
					}

					$sQuery  = 'SELECT transfer_detail.product_ID as productID';
					$sQuery .= ' FROM transfer, transfer_detail';
					$sQuery .= ' WHERE 1';
					$sQuery .= ' AND transfer.ID = transfer_detail.transfer_ID';
					$sQuery .= ' AND To_outlet_ID = "' . $iOutletID . '"';
					$sQuery .= ' GROUP BY transfer_detail.product_ID';
	
					$aResult_transfer = $this->dbQuery($sQuery);

					foreach ($aResult_transfer as $key => $value)
					{
						foreach ($value as $key2 => $value2)
						{
							if ( !in_array($value2, $aAvailableProduct) )
							{
								$aAvailableProduct[] = $value2;
							}
						}
					}
				}

				$aFinalResult = array();

				foreach ($aResult as $key => $value)
				{
					if (in_array($value["ID"], $aAvailableProduct) )
					{
						$aFinalResult[] = $value;
					}
				}

				return $aFinalResult;
			}

			function GetProductByID($iProductID)
			{
				$sQuery  = 'SELECT ID, productCategory_ID, Name, Price, Description, Image, Thumbs, Driver, Viewable, ViewPriority';
				$sQuery .= ' FROM product';
				$sQuery .= ' WHERE ID = "' . $iProductID . '"';

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

			function GetProductNameByID($iProductID)
			{
				$sQuery  = 'SELECT Name';
				$sQuery .= ' FROM product';
				$sQuery .= ' WHERE ID = "' . $iProductID . '"';

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid
				foreach( $aResult as $key => $value )
				{
					foreach( $value as $key2 => $value2 )
					{
						$value2 = stripslashes($value2);
					}
				}

				return $aResult[0]['Name'];
			}

			function GetProductByName($sProductName)
			{
				//temporary patch
				$sProductName = str_replace(" ", "%", $sProductName);
				$sQuery  = 'SELECT ID, productCategory_ID, Name, Price, Description, Image, Thumbs, Driver, Viewable, ViewPriority';
				$sQuery .= ' FROM product';
				$sQuery .= ' WHERE Name LIKE "' . $sProductName . '"';

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

			function GetNextPrevIDByCurrentID($sDirection = "next", $iProductID)
			{
				$iResultID = $iProductID; //initialize the result ID to match the input parameter product ID to show end of record reached when both are the same number

				$sQuery  = 'SELECT';
				$sQuery .= " case when sign(ID - " . $iProductID . ") > 0 then 'next' else 'prev' end as dir,";
				$sQuery .= " case when sign(ID - " . $iProductID . ") > 0 then min(ID)";
				$sQuery .= " when sign(ID - " . $iProductID . ") < 0 then max(ID) end as ID";
				$sQuery .= " FROM product";
				$sQuery .= " where ID <> " . $iProductID;
				$sQuery .= " group by sign(ID - " . $iProductID . ")";
				$sQuery .= " order by sign(ID - " . $iProductID . ")";

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid

				for ($i = 0; $i < count($aResult); $i++)
				{
					if ($aResult[$i]['dir'] == $sDirection)
						$iResultID = $aResult[$i]['ID'];
				}
				
				return $iResultID;
			}

			//the output is a bunch of product data
			function GetProductByCategory( $iCategoryID )
			{
				$aResult = FALSE;

				$aCategory = $this->getChildCategoryByParent($iCategoryID);

				$sQuery  = 'SELECT ID, Name, Name AS name, Price as price, Description AS description, Thumbs AS thumbnail';
				$sQuery .= ' FROM product';
				$sQuery .= ' WHERE 1 ';

				//with the new product category system of parents, we need to check for all product categories under the same parent. This must be recursive
				$sQuery .= ' AND ( ';
				foreach ($aCategory as $key => $value)
				{
					$sQuery .= ' productCategory_ID = "' . $value . '" OR';
				}
				//remove the extra OR
				$sQuery = substr($sQuery, 0, strlen($sQuery) - 3);
				$sQuery .= ' ) ';

				$sQuery .= ' ORDER BY Name ASC';

				$aResult = $this->dbQuery($sQuery);

				if ( count($aResult) > 0 )
				{
					//TODO:check result is valid
					foreach( $aResult as $key => $value )
					{
						foreach( $value as $key2 => $value2 )
						{
							$value2 = stripslashes($value2);
						}
					}
				}

				return $aResult;
			}
			
			function GetVisibleProductByCategory( $iCategoryID )
			{
				$aResult = FALSE;

				$sQuery  = 'SELECT ID, Name AS name, Price AS price, Description AS description, Thumbs AS thumbnail';
				$sQuery .= ' FROM product';
				$sQuery .= ' WHERE productCategory_ID = "' . $iCategoryID . '"';
				$sQuery .= ' AND Viewable = 1';
				
				$sQuery .= ' ORDER BY ViewPriority ASC, Modified DESC';

				$aResult = $this->dbQuery($sQuery);

				if ( count($aResult) > 0 )
				{
					//TODO:check result is valid
					foreach( $aResult as $key => $value )
					{
						foreach( $value as $key2 => $value2 )
						{
							$value2 = stripslashes($value2);
						}
					}
				}

				return $aResult;
			}
			
			//CATEGORY FUNCTIONS
			function InsertCategory($aCategory)
			{
				if ( is_array($aCategory) ) //check that $aProduct is an array
				{
					foreach( $aCategory as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO product_category';
						$sQuery .= ' (`Name`, `parent_ID`, `Created`, `Modified`)';
						$sQuery .= ' VALUES ("' . $aCategory['Name'] .'",';
						$sQuery .= ' "' . $aCategory['parent_ID'] .'",';
						$sQuery .= ' "' . date('YmdHis') .'",';
						$sQuery .= ' "' . date('YmdHis') .'")';
	
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
			
			function UpdateCategory($aCategory)
			{
				if ( is_array($aCategory) ) //check that $aProduct is an array
				{
					foreach( $aCategory as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

/*I will need to check to make sure that the relationship is not circular.
For example, Voucher has Parent of Perdana. Perdana has Parent of Voucher, this is circular
so, will need to check the parent of parent_ID is not the same as ID*/
					if ($aCategory['parent_ID'] > 0)
					{ 
						$aParentProduct = $this->GetCategoryByID( $aCategory['parent_ID'] );
						if ($aParentProduct[0]['parent_ID'] == $aCategory['ID'] )
						{
							return FALSE;//"FAIL, CIRCULAR DETECTED";
						}
					}
					

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'UPDATE product_category';
						$sQuery .= ' SET `Name` ="' . $aCategory['Name'] .'",';
						$sQuery .= ' `parent_ID` ="' . $aCategory['parent_ID'] .'",';
						$sQuery .= ' `Modified` ="' . date('YmdHis') .'"';
						$sQuery .= ' WHERE ID = "' . $aCategory['ID'] . '"';

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
			
			function RemoveCategory($iCategoryID)
			{
				$aResult = FALSE;

				//check if category is used by products, because if it's used, category will not be deleted
				$aProductList = $this->GetProductByCategory($iCategoryID);
				
				if (count($aProductList) === 0)
				{
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM product_category';
					$sQuery .= ' WHERE ID = "' . $iCategoryID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				}
				
				return $aResult;
			}
			
			function GetCategoryList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT ID, parent_ID, Name';
				$sQuery .= ' FROM product_category';

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
						$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY ID';

				//sort by
				if ( count($aSortByArray) > 0 )
				{
					$sQuery .= ' ORDER BY';
					foreach($aSortByArray as $key => $value)
					{
						$sQuery .= ' ' . $key . ' ' . $value;
					}
				}
				else
				{
					$sQuery .= ' ORDER BY Name ASC';
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
			
			function GetCategoryByID( $iCategoryID )
			{
				$sQuery  = 'SELECT ID, parent_ID, Name';
				$sQuery .= ' FROM product_category';
				$sQuery .= ' WHERE ID = "' . $iCategoryID . '"';

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

			function GetCategoryByName( $sCategoryName )
			{
				$sQuery  = 'SELECT ID, parent_ID, Name';
				$sQuery .= ' FROM product_category';
				$sQuery .= ' WHERE Name LIKE "' . $sCategoryName . '"';

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

			function getChildCategoryByParent($iParentID)
			{
				$aCategory = array($iParentID);

				$sQuery = 'SELECT ID FROM product_category WHERE parent_ID = "' . $iParentID . '"';
				$aResult = $this->dbQuery($sQuery);

				for ($i = 0; $i < count($aResult); $i++)
				{
					$aCategory[] = $aResult[$i]['ID'];
					//I need this to be recursive
					$aSubCategory = $this->getChildCategoryByParent($aResult[$i]['ID']);
					if (count($aSubCategory) > 0 )
					{
						foreach ($aSubCategory as $key => $value)
						{
							if ( !in_array($value, $aCategory) )
							{
								$aCategory[] = $value;
							}
						}
					}
				}

				return $aCategory;
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
