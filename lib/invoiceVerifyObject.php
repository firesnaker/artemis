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
	* lib/invoiceVerifyObject.php :: INVOICE VERIFY CLASS								*
	****************************************************************************
	* The INVOICE VERIFY Object																*
	* Includes Purchase, Sales and Transfer Invoices									*
	* This object abstracts the database table structure and data for verify	*
	*																									*
	* Version			: 1																		*
	* Author				: Ricky Kurniawan [FireSnakeR] 									*
	* Created			: 2015-04-10 															*
	* Last modified	: 2015-04-10															*												*
	***************************************************************************/

	if ( !class_exists('FSR_Verify') )
	{
		class FSR_Verify
		{
			//purchase
			private $id;
			private $invoice_type;
			private $invoice_id;
			private $user_id;
			private $date;
			private $notes;
			//tables
			private $tableVerify;
			private $db;

			public function __construct()
			{
				require_once("classDatabase.php");

				include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
				include_once($rootPath . "config.php");

				$this->db = new FSR_Database(_DBTYPE_, _DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_);

				$this->tableVerify = "verification";

				//$this->convert_old_to_new();
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

			public function getVerify($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, $this->tableVerify);

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}
			}

			public function setVerify()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields($this->tableVerify)) )
					{
						$param[$key] = $value;
					}
				}
				if ( !$param['date'] )
				{
					$param['date'] = date("Y-m-d");
				}

				return $this->db->dbSave($param, $this->tableVerify);
			}

			public function listVerify($aData=array())
			{
				$result = FALSE;

				$param = array(
					"fields" => "id",
					"tables" => $this->tableVerify
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

				$result = $this->db->dbSearch($param);

				return $result;
			}

			private function convert_old_to_new()
			{
				$result = FALSE;

				$param = array(
					"fields" => "ID, Status, VerifyNotes",
					"tables" => "purchase",
					"query" => " Status = 1"
				);

				$aSearchResult = $this->db->dbSearch($param);

				if ($aSearchResult != FALSE)
				{
					$result = array();
					foreach ($aSearchResult as $aResultRow)
					{
						$param = array(
							"invoice_id" => " = '". $aResultRow['ID'] ."'"
						);
						$verify_list = $this->listVerify($param);

						if ($verify_list != FALSE)
						{
							$this->getVerify($verify_list[0]['id']);
						}

						//get the date by verifynotes
						if (is_numeric($aResultRow['VerifyNotes']))
						{
							$day = substr($aResultRow['VerifyNotes'], 0, 2);
							$month = substr($aResultRow['VerifyNotes'], 2, 2);
							$year = substr($aResultRow['VerifyNotes'], 4, 2);
							$date = date("Y-m-d", mktime(0,0,0, $month, $day, $year));
						}
						else
						{
							$date = date("Y-m-d");
						}


						$this->invoice_type = 1;
						$this->invoice_id = $aResultRow['ID'];
						$this->user_id = 0;
						$this->date = $date;
						$this->notes = $aResultRow['VerifyNotes'];

						$this->setVerify();

						include_once("invoiceObject.php");
						$cPurchase = new FSR_Invoice;

						$cPurchase->getPurchase($aResultRow['ID']);
						$cPurchase->setProperty("verified", 1);
						$cPurchase->setPurchase();
					}
				}
			}
		}
	}
?>
