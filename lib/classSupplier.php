<?php
	/************************************************************************
	* lib/classSupplier.php :: SUPPLIER CLASS											*
	*************************************************************************
	* Supplier object																			*
	*																								*
	* List of Tables :																		*
	* supplier			: stores supplier information									*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2015-03-23 														*
	* Last modified	: 2015-03-23														*
	* 																								*
	* 						Copyright (c) 2015 FireSnakeR									*
	************************************************************************/

	if ( !class_exists('FSR_Supplier') )
	{
		class FSR_Supplier
		{
			//supplier
			private $id;
			private $name;
			private $deleted;
			//tables
			private $tableSupplier;
			private $db;

			public function __construct()
			{
				require_once("classDatabase.php");

				//dirConf is to be included without _once because it must always 
				//be called locally
				include("dirConf.php"); 
				include_once($rootPath . "config.php");

				$this->db = new FSR_Database(_DBTYPE_, _DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_);

				$this->tableSupplier = "supplier";
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

			public function getSupplier($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, $this->tableSupplier);

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}
			}

			public function setSupplier()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields($this->tableSupplier)) )
					{
						$param[$key] = $value;
					}
				}

				return $this->db->dbSave($param, $this->tableSupplier);
			}

			public function deleteSupplier($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getSupplier($iID);
				$this->deleted = 1;

				return $this->setSupplier();
			}

			public function restoreSupplier($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getSupplier($iID);
				$this->deleted = 0;

				return $this->setSupplier();
			}

			public function listSupplier($aData=array())
			{
				$result = FALSE;

				$param = array(
					"fields" => "id",
					"tables" => $this->tableSupplier
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
						$this->getSupplier($aResultRow['id']);

						$result[] = array(
							"id" => $this->id,
							"name" => $this->name,
							"deleted" => $this->deleted
						);
					}
				}

				return $result;
			}

			public function getIDByName($sName)
			{
				$sResult = FALSE;

				$param = array(
					"fields" => "id",
					"tables" => $this->tableSupplier,
					"query" => "name LIKE '%" . $sName . "%'"
				);
				$sResult = $this->db->dbSearch($param);

				return $sResult;
			}
		}
	}
?>