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
	* lib/invoiceObject.php :: INVOICE CLASS												*
	****************************************************************************
	* The INVOICE Object																			*
	* Includes Purchase, Sales and Transfer Invoices									*
	* This object abstracts the database table structure and data for invoices	*
	*																									*
	* Version			: 2																		*
	* Author				: Ricky Kurniawan [FireSnakeR] 									*
	* Created			: 2014-05-09 															*
	* Last modified	: 2015-03-13															*												*
	***************************************************************************/

/*
IDEAL::the ideal table structure is like this
invoice
id, invoice_number, invoice_type (purchase, sales, transfer), invoice_date, invoice_total, payment_status (0/1), customer_name 

invoice_detail
id, invoice_id, item_name, item_quantity, item_price, item_subtotal

invoice_payment
id, invoice_id, payment_type (cash, credit card, transfer, debit), payment_date, payment_amount

invoice_delivery
id, invoice_id, delivery_method (courier/TIKI), sent_date, sent_by, received_date, received_by 

NEW SYSTEM is this invoiceObject
Both system works, all new sales are in new tables
Old tables are migrated after the new system works.
*/

	if ( !class_exists('FSR_Invoice') )
	{
		class FSR_Invoice
		{
			//purchase
			private $ID;
			private $outlet_ID;
			private $supplier_id;
			private $paymentType_ID;
			private $Date;
			private $Notes;
			private $VerifyNotes;
			private $Status;
			private $verified;
			private $Created;
			private $outlet_name;
			private $supplier_name;
			private $paymentType_name;
			//purchase_detail
			private $purchase_ID; //redundant, remove this when database field changes to parentID
			private $product_ID;
			private $Quantity;
			private $Price;
			private $SnStart;
			private $SnEnd;
			//tables
			private $tablePurchase;
			private $tablePurchaseDetail;
			private $db;

			public function __construct()
			{
				require_once("classDatabase.php");

				include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
				include_once($rootPath . "config.php");

				$this->db = new FSR_Database(_DBTYPE_, _DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_);

				$this->tablePurchase = "purchase";
				$this->tablePurchaseDetail = "purchase_detail";
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

			public function getPurchase($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, $this->tablePurchase);

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}

				//get outlet name
				$this->outlet_name = "none";
				if ($this->outlet_ID > 0)
				{
					require_once("classOutlet.php");
					$cOutlet = new FSR_Outlet;
					$cOutlet->getOutlet($this->outlet_ID);
					$this->outlet_name = $cOutlet->getProperty("Name");
				}

				//get supplier name
				$this->supplier_name = "none";
				if ($this->supplier_id > 0)
				{
					require_once("classSupplier.php");
					$cSupplier = new FSR_Supplier;
					$cSupplier->getSupplier($this->supplier_id);
					$this->supplier_name = $cSupplier->getProperty("name");
				}

				//get payment type name
				$this->paymentType_name = "none";
				if ($this->paymentType_ID > 0)
				{
					require_once("classPaymentType.php");
					$cPaymentType = new PaymentType;
					$aPaymentType = $cPaymentType->GetPaymentTypeByID($this->paymentType_ID);
					$this->paymentType_name = $aPaymentType[0]['Name'];
				}
			}

			public function setPurchase()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields($this->tablePurchase)) )
					{
						$param[$key] = $value;
					}
				}
				if ( !$param['Created'] )
				{
					$param['Created'] = date("Y-m-d H:i:s");
				}

				return $this->db->dbSave($param, $this->tablePurchase);
			}

			public function deletePurchase($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getPurchase($iID);
				//$this->Deleted = 1;

				return $this->setProduct();
			}

			public function restorePurchase($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getPurchase($iID);
				//$this->Deleted = 0;

				return $this->setProduct();
			}

			public function listPurchase($aData=array())
			{
				$result = FALSE;

				$param = array(
					"fields" => "ID",
					"tables" => $this->tablePurchase
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
						$this->getPurchase($aResultRow['ID']);

						$result[] = array(
							"ID" => $this->ID,
							"outlet_ID" => $this->outlet_ID,
							"supplier_id" => $this->supplier_id,
							"paymentType_ID" => $this->paymentType_ID,
							"Date" => $this->Date,
							"DateDT" => array(
								"display" => date("d-M-Y", strtotime($this->Date)),
								"timestamp" => strtotime($this->Date)
							),
							"Notes" => $this->Notes,
							"VerifyNotes" => $this->VerifyNotes,
							"Status" => $this->Status,
							"verified" => $this->verified,
							"outletName" => $this->outlet_name,
						);
					}
				}

				return $result;
			}

			/*** PURCHASE DETAIL ***/
			public function getPurchaseDetail($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, $this->tablePurchaseDetail);

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}
			}

			public function setPurchaseDetail()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields($this->tablePurchaseDetail)) )
					{
						$param[$key] = $value;
					}
				}

				return $this->db->dbSave($param, $this->tablePurchaseDetail);
			}

			public function listPurchaseDetail($aData=array())
			{
				$result = FALSE;

				$param = array(
					"fields" => "ID",
					"tables" => $this->tablePurchaseDetail
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
						$this->getPurchaseDetail($aResultRow['ID']);

						include_once("classProduct.php");
						$cProduct = new FSR_Product;
						$cProduct->getProduct($this->product_ID);

						$result[] = array(
							"ID" => $this->ID,
							"purchase_ID" => $this->purchase_ID,
							"product_ID" => $this->product_ID,
							"product_Name" => $cProduct->getProperty("Name"),
							"Quantity" => $this->Quantity,
							"Price" => $this->Price,
							"SnStart" => $this->SnStart,
							"SnEnd" => $this->SnEnd,
							"SnRange" => $this->SnStart . " - " . $this->SnEnd
						);
					}
				}

				return $result;
			}
			/*** PURCHASE DETAIL ***/
		}
	}

	if ( !class_exists('Invoice') )
	{
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//
	
		class Invoice extends Database
		{
			private $Type;
			//*** BEGIN FUNCTION LIST **************************************//
			// PUBLIC FUNCTIONS ++++++++++++++++++++++++++++++++++++++++++++//
			// __construct()
			// Save() :: Save One Invoice
			// Load() :: Load One Invoice
			// Search($aParam) :: Search Invoices, return value are array of Invoice IDs
			//
			// PRIVATE FUNCTIONS +++++++++++++++++++++++++++++++++++++++++++//
			// SavePurchase() :: Save One Purchase Invoice
			// LoadPurchase() :: Load One Purchase Invoice
			// SearchPurchase($aParam) :: Search Purchase Invoices, return value are array of Invoice IDs
			//*** END FUNCTION LIST ****************************************//

			//*** BEGIN FUNCTION *******************************************//

			public function __construct($sInvoiceType)
			{
				//attempt to open database
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_);

				if ( $this->dbError == FALSE )
				{
					if ($sInvoiceType == 'purchase' || $sInvoiceType == 'sales')
					{
						$this->Type = $sInvoiceType;
					}
					else
					{
						//unknown invoice type
					}
				}
				else
				{
					//failed to open database
				}
			}

			public function Save()
			{
				switch ($this->Type)
				{
					case 'purchase':
					break;
					case 'sales':
					break;
					default:
					break;
				}
			}

			public function Load($iID)
			{
				switch ($this->Type)
				{
					case 'purchase':
						$aResult = $this->LoadPurchase($iID);
					break;
					case 'sales':
					break;
					default:
					break;
				}

				return $aResult;
			}

			public function Search($aParam=array())
			{
				switch ($this->Type)
				{
					case 'purchase':
						$aResult = $this->SearchPurchase($aParam);
					break;
					case 'sales':
						echo "y";
					break;
					default:
					break;
				}

				return $aResult;
			}

			private function SavePurchase()
			{
				
			}

			private function LoadPurchase($iID)
			{
				//get the master
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM purchase';
				$sQuery .= ' WHERE purchase.ID = "' . $iID . '"';

				$aMaster = $this->dbQuery($sQuery);

				//get the outlet name
				$sOutletName = 'NOT SET';
				if ( $aMaster[0]['outlet_ID'] > 0 )
				{
					$sQuery = 'SELECT Name FROM outlet WHERE ID = "' . $aMaster[0]['outlet_ID'] . '"';
					$aOutlet = $this->dbQuery($sQuery);

					$sOutletName = $aOutlet[0]['Name'];
				}

				$sPaymentTypeName = 'NOT SET';
				if ( $aMaster[0]['paymentType_ID'] > 0 )
				{
					$sQuery = 'SELECT Name FROM paymentType WHERE ID = "' . $aMaster[0]['paymentType_ID'] . '"';
					$aPaymentType = $this->dbQuery($sQuery);

					$sPaymentTypeName = $aPaymentType[0]['Name'];
				}

				//get the detail
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM purchase_detail';
				$sQuery .= ' WHERE purchase_detail.purchase_ID = "' . $iID . '"';

				$aDetail = $this->dbQuery($sQuery);

				for ( $i = 0; $i < count($aDetail); $i++ )
				{
					$sQuery  = 'SELECT Name FROM product WHERE ID = "' . $aDetail[$i]['product_ID'] . '"';
					$aProduct = $this->dbQuery($sQuery);

					$aDetail[$i]['product_Name'] = $aProduct[0]['Name'];
					$aDetail[$i][count($aDetail[$i]) / 2] = $aProduct[0]['Name'];
				}

				//now we create the result array
				$aResult = array(
					"ID" => $aMaster[0]['ID'],
					"outlet_ID" => $aMaster[0]['outlet_ID'],
					"outlet_Name" => $sOutletName,
					"paymentType_ID" => $aMaster[0]['paymentType_ID'],
					"paymentType_Name" => $sPaymentTypeName,
					"Date" => $aMaster[0]['Date'],
					"Notes" => $aMaster[0]['Notes'],
					"VerifyNotes" => $aMaster[0]['VerifyNotes'],
					"Status" => $aMaster[0]['Status'],
					"Detail" => $aDetail
				);

				return $aResult;
			}

			private function SearchPurchase($aParam = array())
			{
				$sQuery  = 'SELECT purchase.ID AS ID';
				$sQuery .= ' FROM purchase';
				$sQuery .= ' WHERE 1';

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

				return $aResult;
			}

			//*** END FUNCTION *********************************************//
		}
	}
?>
