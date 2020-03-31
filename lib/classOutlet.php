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
	* lib/classOutlet.php :: OUTLET CLASS												*
	*************************************************************************
	* Outlet object																			*
	*																								*
	* List of Tables :																		*
	* outlet				: stores outlet information									*
	*																								*
	* Version			: 2																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2009-03-28 														*
	* Last modified	: 2015-02-12														*
	* 																								*
	************************************************************************/

	if ( !class_exists('FSR_Outlet') )
	{
		class FSR_Outlet
		{
			//product
			private $ID;
			private $master_outlet_ID;
			private $parentID;
			private $code;
			private $Name;
			private $Address;
			private $Phone;
			private $Fax;
			private $AllowPurchase;
			private $AllowPurchaseNewAndEdit;
			private $Deleted;
			private $Created;
			private $Modified;
			private $parentName;
			//tables
			private $tableOutlet;
			private $db;

			public function __construct()
			{
				require_once("classDatabase.php");

				include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
				include_once($rootPath . "config.php");

				$this->db = new FSR_Database(_DBTYPE_, _DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_);

				$this->tableOutlet = "outlet";
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

			public function getOutlet($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, $this->tableOutlet);

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}
				$this->parentID = $this->master_outlet_ID;
				//get parent name
				$this->parentName = $this->getNameByID($this->parentID);
			}

			public function setOutlet()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields($this->tableOutlet)) )
					{
						$param[$key] = $value;
					}
				}

				return $this->db->dbSave($param, $this->tableOutlet);
			}

			public function deleteOutlet($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getOutlet($iID);
				$this->Deleted = 1;

				return $this->setOutlet();
			}

			public function restoreOutlet($iID)
			{
				//set Deleted field as 1
				//this is to avoid orphaned data in other tables 
				$this->getOutlet($iID);
				$this->Deleted = 0;

				return $this->setOutlet();
			}

			public function listOutlet($aData=array())
			{
				$result = FALSE;

				$param = array(
					"fields" => "ID",
					"tables" => $this->tableOutlet
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
						$this->getOutlet($aResultRow['ID']);

						$result[] = array(
							"ID" => $this->ID,
							"parentID" => $this->parentID,
							"code" => $this->code,
							"Name" => $this->Name,
							"Address" => $this->Address,
							"Phone" => $this->Phone,
							"Fax" => $this->Fax,
							"AllowPurchase" => $this->AllowPurchase,
							"Deleted" => $this->Deleted,
							"Created" => $this->Created,
							"Modified" => $this->Modified,
							"parentName" => $this->parentName
						);
					}
				}

				return $result;
			}

			public function getNameByID($iID)
			{
				$sResult = FALSE;

				$param = array(
					"fields" => "Name",
					"tables" => $this->tableOutlet,
					"query" => "ID = '" . $iID . "'"
				);
				$aSearchResult = $this->db->dbSearch($param);
				if ($aSearchResult)
				{
					$sResult = $aSearchResult[0]['Name'];
				}
				return $sResult;
			}

			public function getIDByName($sName)
			{
				$sResult = FALSE;

				$param = array(
					"fields" => "ID",
					"tables" => $this->tableOutlet,
					"query" => "Name LIKE '%" . $sName . "%'"
				);
				$sResult = $this->db->dbSearch($param);

				return $sResult;
			}

			public function getIDByFullName($sName)
			{
				$sResult = FALSE;

				$param = array(
					"fields" => "ID",
					"tables" => $this->tableOutlet,
					"query" => "Name = '" . $sName . "'"
				);
				$aSearchResult = $this->db->dbSearch($param);

				if ($aSearchResult)
				{
					$sResult = $aSearchResult[0]['ID'];
				}

				return $sResult;
			}

			public function getParentIDDB($outlet_ID)
			{
				$result = FALSE;
				$child_ID = $outlet_ID;
				do
				{
					$param = array(
						"fields" => "ID, master_outlet_ID",
						"tables" => $this->tableOutlet,
						"query" => "ID = '" . $child_ID . "'"
					);
					$aResult = $this->db->dbSearch($param);

					if ( $aResult[0]['master_outlet_ID'] == 0)
					{
						$result = $aResult[0]['ID'];
					}
				} while ($result == FALSE && count($aResult) > 0);

				return $result;
			}

			public function getParentID($outlet_ID)
			{
				$result = FALSE;

				//check to make sure the outlet_ID exists in the database
				$param = array(
					"fields" => "ID, master_outlet_ID",
					"tables" => $this->tableOutlet,
					"query" => "ID = '" . $outlet_ID . "'"
				);
				$aResult = $this->db->dbSearch($param);

				if ( count($aResult) > 0)
				{
					$param = array(
						"fields" => "ID, master_outlet_ID",
						"tables" => $this->tableOutlet
					);
					$aResultAll = $this->db->dbSearch($param);
	
					if ( count($aResultAll) > 0 )
					{
						//build the tree
						$tree = array();
						foreach ($aResultAll as $aRow)
						{
							$tree[$aRow['master_outlet_ID']][] = $aRow['ID'];
						}

						//search the tree
						$needle = $outlet_ID;
						do
						{
							foreach ($tree as $parent => $aRow)
							{
								if ( in_array($needle, $aRow) )
								{
									if ($parent == 0)
									{
										//exit the loop
										$result = $needle;
									}
									else
									{
										$needle = $parent;
									}
								}
							}
						}
						while ($result == FALSE);
					}
				}

				return $result;
			}
		}
	}

	if ( !class_exists('Outlet') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Outlet extends Database
		{
			var $ID			= FALSE;
			var $masterOutletID	= FALSE;
			var $Name			= FALSE;
			var $Address		= FALSE;
			var $Phone		= FALSE;
			var $Fax			= FALSE;
			var $Status		= FALSE;
			var $Viewable		= FALSE;
			var $AllowPurchase	= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Outlet($iOutletID = 0)
			// Insert($aOutlet)
			// Update($aOutlet)
			// Remove($iOutletID)
			// GetOutletList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetOutletByID($iOutletID)
			// GetNextPrevIDByCurrentID($sDirection = "next", $iOutletID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Outlet($iOutletID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iOutletID) && $iOutletID > 0 ) //check $iOutletID is numeric and positive value
					{
						$aOutlet = $this->GetOutletByID($iOutletID);

						if (is_array($aOutlet) && count($aOutlet) == 1) //check $aOutlet is an array and has exactly one data
						{
							$this->ID = $aOutlet[0]['ID'];
							$this->masterOutletID = $aOutlet[0]['master_outlet_ID'];
							$this->Name = $aOutlet[0]['Name'];
							$this->Address = $aOutlet[0]['Address'];
							$this->Phone = $aOutlet[0]['Phone'];
							$this->Fax = $aOutlet[0]['Fax'];
							$this->Status = $aOutlet[0]['Status'];
							$this->Viewable = $aOutlet[0]['Viewable'];
							$this->AllowPurchase = $aOutlet[0]['AllowPurchase'];
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
						if ( $iOutletID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iOutletID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aOutlet)
			{
				if ( is_array($aOutlet) ) //check that $aOutlet is an array
				{
					foreach( $aOutlet as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO outlet';
						$sQuery .= ' (`Name`, `master_outlet_ID`, `Address`, `Phone`, `Fax`, `Viewable`, `Created`, `Modified`)';
						$sQuery .= ' VALUES ("' . $aOutlet['Name'] .'",';
						$sQuery .= ' "' . $aOutlet['master_outlet_ID'] .'", ';
						$sQuery .= ' "' . $aOutlet['Address'] .'", ';
						$sQuery .= ' "' . ( ( array_key_exists("Phone", $aOutlet) )?$aOutlet["Phone"]:NULL ) .'",';
						$sQuery .= ' "' . ( ( array_key_exists("Fax", $aOutlet) )?$aOutlet["Fax"]:NULL ) .'",';
						$sQuery .= ' "' . ( ( array_key_exists("Viewable", $aOutlet) )?$aOutlet["Viewable"]:"1" ) .'",';
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
			
			function Update($aOutlet)
			{
				$aResult = 0;
				if ( is_array($aOutlet) ) //check that $aOutlet is an array
				{
					foreach( $aOutlet as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					$sQuery  = 'UPDATE outlet';
					$sQuery .= ' SET `Name` = "' . $aOutlet['Name'] . '",';
					$sQuery .= ' `master_outlet_ID` = "' . $aOutlet['master_outlet_ID'] . '",';
					$sQuery .= ' `Address` = "' . $aOutlet['Address'] . '",';
					$sQuery .= ' `Phone` = "' . $aOutlet['Phone'] . '",';
					$sQuery .= ' `Fax` = "' . $aOutlet['Fax'] . '",';
					$sQuery .= ' `Viewable` = "' . $aOutlet['Viewable'] . '",';
					$sQuery .= ' `AllowPurchase` = "' . $aOutlet['AllowPurchase'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aOutlet['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}

			function SetInactive($iDeactivateID)
			{
				$aResult = 0;
	
				$sQuery  = 'UPDATE outlet';
				$sQuery .= ' SET `Status` = "-1"';
				$sQuery .= ' WHERE `ID` = "' . $iDeactivateID . '"';

				$aResult = $this->dbAction($sQuery);
	
				//check result is success or failure
				if ($aResult == 0)
				{
					$this->logError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function SetActive($iActivateID)
			{
				$aResult = 0;
	
				$sQuery  = 'UPDATE outlet';
				$sQuery .= ' SET `Status` = "0"';
				$sQuery .= ' WHERE `ID` = "' . $iActivateID . '"';

				$aResult = $this->dbAction($sQuery);
	
				//check result is success or failure
				if ($aResult == 0)
				{
					$this->logError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function SetAllowPurchase($iOutletID, $iStatus)
			{
				//$iStatus 0 = not allowed, 1 = allowed

				$aResult = 0;
	
				$sQuery  = 'UPDATE outlet';
				$sQuery .= ' SET `AllowPurchase` = "' . $iStatus . '"';
				$sQuery .= ' WHERE `ID` = "' . $iOutletID . '"';

				$aResult = $this->dbAction($sQuery);
	
				//check result is success or failure
				if ($aResult == 0)
				{
					$this->logError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function Remove($iOutletID)
			{
				include("dirConf.php");
				
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM outlet';
					$sQuery .= ' WHERE outlet.ID = "' . $iOutletID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}

			function GetOutletList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT outlet.ID AS ID, outlet.master_outlet_ID AS master_outlet_ID, outlet.Name AS name, outlet.Address AS address, outlet.Phone AS phone, outlet.Fax AS fax, outlet.Status AS status, outlet.Viewable as viewable';
				$sQuery .= ' FROM outlet';

				//verify that $aSearchByFieldArray value is not empty
				//$aSearchByFieldArray = array_unique($aSearchByFieldArray);
				arsort($aSearchByFieldArray);
				end($aSearchByFieldArray);
				if (current($aSearchByFieldArray) === "")
					array_pop($aSearchByFieldArray);

				//search by field
				if ( count($aSearchByFieldArray) > 0 )
				{
					$i = 0;
					$sQuery .= ' WHERE';
					foreach ($aSearchByFieldArray as $key => $value )
					{
						switch ($key)
						{
							case "master_outlet_ID":
								$sQuery .= ' ' . $key . ' = "' . $value . '"';
							break;
							case "Status":
								$sQuery .= ' ' . $key . ' = "' . $value . '"';
							break;
							default:
								$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
							break;
						}
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY outlet.ID';

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
					$sQuery .= ' outlet.Name ASC';
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

			function GetOutletByID($iOutletID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM outlet';
				$sQuery .= ' WHERE ID = "' . $iOutletID . '"';

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

			function GetNextPrevIDByCurrentID($sDirection = "next", $iOutletID)
			{
				$iResultID = $iOutletID; //initialize the result ID to match the input parameter product ID to show end of record reached when both are the same number

				$sQuery  = 'SELECT';
				$sQuery .= " case when sign(ID - " . $iOutletID . ") > 0 then 'next' else 'prev' end as dir,";
				$sQuery .= " case when sign(ID - " . $iOutletID . ") > 0 then min(ID)";
				$sQuery .= " when sign(ID - " . $iOutletID . ") < 0 then max(ID) end as ID";
				$sQuery .= " FROM outlet";
				$sQuery .= " where ID <> " . $iOutletID;
				$sQuery .= " group by sign(ID - " . $iOutletID . ")";
				$sQuery .= " order by sign(ID - " . $iOutletID . ")";

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid

				for ($i = 0; $i < count($aResult); $i++)
				{
					if ($aResult[$i]['dir'] == $sDirection)
						$iResultID = $aResult[$i]['ID'];
				}

				return $iResultID;
			}

			function GetActiveOutletList()
			{
				$aOutlet = $this->GetOutletList(array("Status" => 0), array("name" => "ASC"));

				return $aOutlet;
			}

			function GetActiveOutletListByFinanceArea($iUserID)
			{
				$sQuery  = 'SELECT outlet.ID AS ID, outlet.Name AS name';
				$sQuery .= ' FROM outlet, userOutlet';
				$sQuery .= ' WHERE ';
				$sQuery .= ' outlet.ID = userOutlet.outlet_ID ';
				$sQuery .= ' AND userOutlet.user_ID = "' . $iUserID . '"';

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid
				return $aResult;
			}

			function GetActiveOutletWithMasterOutletList($iOutletID)
			{
				$aOutlet = $this->GetOutletList(array("Status" => 0, "master_outlet_ID" => $iOutletID), array("name" => "ASC"));

				return $aOutlet;
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
