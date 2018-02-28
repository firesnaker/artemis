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
	* lib/classBank.php :: BANK CLASS								*
	***********************************************************************
	* All related bank function									*
	*														*
	* Version		: 0.1										*
	* Author		: FireSnakeR 									*
	* Created		: 2014-05-01 									*
	* Last modified	: 2014-05-01								*
	* 														*
	*********************************************************************/

	if ( !class_exists('Bank') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Bank extends Database
		{
			var $ID			= FALSE;
			var $Name			= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// __construct() :: connect to db
			// Save($aData) :: insert / update
			// Remove($iID) :: delete
			// Load($iID) :: load one data by ID
			// GetList($aSearch) :: load a list of data by search parameter

			// SaveDeposit($aData) :: insert / update
			// LoadDeposit($iID) :: load one data by ID
			// GetDepositList($aSearch)  :: load a list of data by search parameter
			// VerifyDeposit($aData) :: verify deposit bank

			// SaveMKiosDeposit($aData) :: insert / update
			// LoadMKiosDeposit($iID) :: load one data by ID
			// GetMKiosDepositList($aSearch)  :: load a list of data by search parameter

			// GetNextPrevIDByCurrentID($sDirection = "next", $iClientID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			public function __construct()
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError )
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			public function Save($aData)
			{
				$iID = 0;
				//do we need to sanitize input ?

				//we check if $aData['ID'] is set and is > 0
				//if yes, we setup an update query
				//else, we setup an insert query
				if (isset($aData['ID']) && $aData['ID'] > 0 )
				{
					$sQuery  = 'UPDATE bank';
					$sQuery .= ' SET `Name` = "' . $aData['Name'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';
					
					$iID = $aData['ID'];
				}
				else
				{
					$sQuery  = 'INSERT INTO bank';
					$sQuery .= ' (`Name`)';
					$sQuery .= ' VALUES ("' . $aData['Name'] .'")';
				}

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				//get the last insert id
				if ($iID == 0)
				{
					$iID = $this->dbLink->lastInsertId();
				}

				return $iID;
			}
			
			public function Remove($iID)
			{
				$sQuery  = 'DELETE FROM bank';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			public function Load($iID)
			{
				$sQuery  = 'SELECT ID, Name';
				$sQuery .= ' FROM bank';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult;
			}

			public function GetList($aSearch)
			{
				$sQuery  = 'SELECT ID, Name';
				$sQuery .= ' FROM bank';
				$sQuery .= ' WHERE 1';

				$sCountQuery = $sQuery;

				//by default we assume it is a = query
				//if passed ">", "<", "LIKE", "BETWEEN" in the value
				//then we redo the = query
				//$aSearch parameter is field and value
				//value contains the necessary notation
				if ( count($aSearch) > 0 )
				{
					$sGroupQuery = '';
					$sOrderByQuery = '';
					$sLimitQuery = '';
					foreach ($aSearch as $field => $value)
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

			function GetNextPrevIDByCurrentID($sDirection = "next", $iClientID)
			{
				$iResultID = $iClientID; //initialize the result ID to match the input parameter product ID to show end of record reached when both are the same number

				$sQuery  = 'SELECT';
				$sQuery .= " case when sign(ID - " . $iClientID . ") > 0 then 'next' else 'prev' end as dir,";
				$sQuery .= " case when sign(ID - " . $iClientID . ") > 0 then min(ID)";
				$sQuery .= " when sign(ID - " . $iClientID . ") < 0 then max(ID) end as ID";
				$sQuery .= " FROM client";
				$sQuery .= " where ID <> " . $iClientID;
				$sQuery .= " group by sign(ID - " . $iClientID . ")";
				$sQuery .= " order by sign(ID - " . $iClientID . ")";

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid

				for ($i = 0; $i < count($aResult); $i++)
				{
					if ($aResult[$i]['dir'] == $sDirection)
						$iResultID = $aResult[$i]['ID'];
				}

				return $iResultID;
			}

			//*** bank Deposit ***//
			public function SaveDeposit($aData)
			{
				//do we need to sanitize input ?

				//we check if $aData['ID'] is set and is > 0
				//if yes, we setup an update query
				//else, we setup an insert query
				if (isset($aData['ID']) && $aData['ID'] > 0 )
				{
					$sQuery  = 'UPDATE bank_deposit';
					$sQuery .= ' SET `outlet_ID` = "' . $aData['outlet_ID'] . '"';
					$sQuery .= ' ,`bank_ID` = "' . $aData['bank_ID'] . '"';
					$sQuery .= ' ,`Notes` = "' . $aData['Notes'] . '"';
					$sQuery .= ' ,`Price` = "' . $aData['Price'] . '"';
					$sQuery .= ' ,`Date` = "' . $aData['Date'] . '"';
					if ( isset($aData['FinanceNotes']) )
					{
						$sQuery .= ' ,`FinanceNotes` = "' . $aData['FinanceNotes'] . '"';
					}
					if ( isset($aData['Status']) )
					{
						$sQuery .= ' ,`Status` = "' . $aData['Status'] . '"';
					}
					if ( isset($aData['salesPayment_ID']) )
					{
						$sQuery .= ' ,`salesPayment_ID` = "' . $aData['salesPayment_ID'] . '"';
					}
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';
				}
				else
				{
					if (!isset($aData['salesPayment_ID']))
					{
						$aData['salesPayment_ID'] = 0;
					}

					$sQuery  = 'INSERT INTO bank_deposit';
					$sQuery .= ' (`outlet_ID`, `bank_ID`, `salesPayment_ID`, `Notes`, `Price`, `Date`)';
					$sQuery .= ' VALUES ("' . $aData['outlet_ID'] .'", "'. $aData['bank_ID'] .'", "' . $aData['salesPayment_ID'] .'", "'. $aData['Notes'] . '", "' . $aData['Price'] . '", "' . $aData['Date'] . '")';
				}

				$aResult = $this->dbAction($sQuery);

				if ( isset($aData['ID']) && $aData['ID'] > 0)
				{
					$iResult = $aData['ID'];
				}
				else
				{
					$iResult = $this->dbLink->lastInsertId();
				}

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $iResult;
			}

			public function RemoveDeposit($iID)
			{
				$sQuery  = 'DELETE FROM bank_deposit';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			public function LoadDeposit($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM bank_deposit';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult;
			}

			public function GetDepositList($aSearch)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM bank_deposit';
				$sQuery .= ' WHERE 1';

				//by default we assume it is a = query
				//if passed ">", "<", "LIKE", "BETWEEN" in the value
				//then we redo the = query
				//$aSearch parameter is field and value
				//value contains the necessary notation
				if ( count($aSearch) > 0 )
				{
					$sGroupQuery = '';
					$sOrderByQuery = '';
					$sLimitQuery = '';
					foreach ($aSearch as $field => $value)
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
							if ($value != '' )
							{
								$sQuery .= ' AND ' . $field . ' ' . $value;
							}
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

			function VerifyDeposit($aData)
			{
				$sQuery  = 'UPDATE bank_deposit';
				$sQuery .= ' SET `Status` = "1"';
				$sQuery .= ' ,`FinanceNotes` = "' . $aData['notes'] . '"';
				$sQuery .= ' WHERE ID = "' . $aData['ID'] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			//*** bank Deposit ***//

			//*** MKIOS bank Deposit ***//
			public function SaveMKiosDeposit($aData)
			{
				//do we need to sanitize input ?
				if (!isset($aData['mkios_payment_ID']))
				{
					$aData['mkios_payment_ID'] = 0;
				}


				//we check if $aData['ID'] is set and is > 0
				//if yes, we setup an update query
				//else, we setup an insert query
				if (isset($aData['ID']) && $aData['ID'] > 0 )
				{
					$sQuery  = 'UPDATE mkios_bank_deposit';
					$sQuery .= ' SET `bank_ID` = "' . $aData['bank_ID'] . '"';
					$sQuery .= ' ,`Notes` = "' . $aData['Notes'] . '"';
					$sQuery .= ' ,`Price` = "' . $aData['Price'] . '"';
					$sQuery .= ' ,`Date` = "' . $aData['Date'] . '"';
					$sQuery .= ' ,`mkios_payment_ID` = "' . $aData['mkios_payment_ID'] . '"';
					//$sQuery .= ' ,`FinanceNotes` = "' . $aData['FinanceNotes'] . '"';
					//$sQuery .= ' ,`Status` = "' . $aData['Status'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';
				}
				else
				{
					$sQuery  = 'INSERT INTO mkios_bank_deposit';
					$sQuery .= ' (`bank_ID`, `Notes`, `Price`, `Date`, `mkios_payment_ID`)';
					$sQuery .= ' VALUES ("'. $aData['bank_ID'] .'", "' . $aData['Notes'] . '", "' . $aData['Price'] . '", "' . $aData['Date'] . '", "' . $aData['mkios_payment_ID'] . '")';
				}

				$aResult = $this->dbAction($sQuery);

				if ( isset($aData['ID']) && $aData['ID'] > 0)
				{
					$iResult = $aData['ID'];
				}
				else
				{
					$iResult = $this->dbLink->lastInsertId();
				}

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $iResult;
			}

			public function RemoveMKiosDeposit($iID)
			{
				$sQuery  = 'DELETE FROM mkios_bank_deposit';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function VerifyMKiosDeposit($aData)
			{
				$sQuery  = 'UPDATE mkios_bank_deposit';
				$sQuery .= ' SET `Status` = "1"';
				$sQuery .= ' ,`FinanceNotes` = "' . $aData['notes'] . '"';
				$sQuery .= ' WHERE ID = "' . $aData['ID'] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			public function LoadMKiosDeposit($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM mkios_bank_deposit';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult;
			}

			public function GetMKiosDepositList($aSearch)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM mkios_bank_deposit';
				$sQuery .= ' WHERE 1';

				//by default we assume it is a = query
				//if passed ">", "<", "LIKE", "BETWEEN" in the value
				//then we redo the = query
				//$aSearch parameter is field and value
				//value contains the necessary notation
				if ( count($aSearch) > 0 )
				{
					$sGroupQuery = '';
					$sOrderByQuery = '';
					$sLimitQuery = '';
					foreach ($aSearch as $field => $value)
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

			//*** MKIOS bank Deposit ***//


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
