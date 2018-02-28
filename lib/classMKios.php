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
	* lib/classMKios.php :: MKIOS CLASS									*
	*********************************************************************
	* All related mkios function											*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-09-02 										*
	* Last modified	: 2013-09-02										*
	* 																	*
	*********************************************************************/

	if ( !class_exists('MKios') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class MKios extends Database
		{
			var $ID		= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// MKios();
			// Insert()
			// LogError()
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function MKios($iMKiosID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					/*if ( is_numeric($iUserID) && $iUserID > 0 ) //check $iUserID is numeric and positive value
					{
						$aUser = $this->GetUserByID($iUserID);

						if (is_array($aUser) && count($aUser) == 1) //check $aUser is an array and has exactly one data
						{
							$this->ID = $aUser[0]['ID'];
							$this->Name = $aUser[0]['Name'];
							$this->Level = $aUser[0]['Level'];
							$this->Outlet_ID = $aUser[0]['outlet_ID'];
							$this->Username = $aUser[0]['Username'];
							$this->Email = $aUser[0]['Email'];
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
						if ( $iUserID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iUserID);
						}
					}*/
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function CleanUpBadData()
			{
				$sQuery = "DELETE FROM mkios ";
				$sQuery .= "WHERE TxPeriod = '1970-01-01'";
	
				$aResult = $this->dbAction($sQuery);
			}

			function Save($aMKios)
			{
				$aResult = FALSE;

				$db_fields = array();
				$db_values = array();
				foreach ($aMKios as $key => $value)
				{
					if ($key != "ID")
					{
						$db_fields[] = $key;
						$db_values[] = $value;
					}
				}

				if ( isset($aMKios['ID']) && $aMKios['ID'] > 0 )
				{
					$sQuery  = "UPDATE mkios";
					$sQuery .= " SET";
					foreach ($aMKios as $key => $value)
					{
						if ($key != "ID")
						{
							$sQuery .= " ". $key ." = '" . $value . "',";
						}
					}
					$sQuery = substr($sQuery, 0, strlen($sQuery)-1);
					$sQuery .= " WHERE ID = '". $aMKios['ID'] ."'";
				}
				else
				{
					$sQuery  = "INSERT INTO mkios";
					$sQuery .= " (";
					foreach ($db_fields as $key => $value)
					{
						$sQuery .= " " . $value .",";
					}
					$sQuery = substr($sQuery, 0, strlen($sQuery)-1);
					$sQuery .= " )";
					$sQuery .= " VALUES";
					$sQuery .= " (";
					foreach ($db_values as $key => $value)
					{
						$sQuery .= " '" . $value ."',";
					}
					$sQuery = substr($sQuery, 0, strlen($sQuery)-1);
					$sQuery .= " )";
				}

				$aResult = $this->dbAction($sQuery);

				return $aResult;
			}

			function Insert($aMKios)
			{
				$aResult = FALSE;

				if ( is_array($aMKios) ) //check that $aNewUser is an array
				{
					//check if data already exists, if yes, then do nothing
					if ($this->CheckDuplicates($aMKios) == 0 && $aMKios['TxPeriod'] != '1970-01-01') 
					{
						foreach( $aMKios as $key => $value ) //addslashes to avoid SQL Injection
						{
							$value = addslashes($value);
						}
		
						//if ( $this->validateDataInput($aNewUser) ) //validate data input
						//{
							$sQuery  = 'INSERT INTO mkios';
							$sQuery .= ' (`KodeWH`, `KodeSales`, `CustomerGroup`, `NamaCust`, `TxPeriod`, `KodeTerminal`, `NoHP`, `Subtotal`, `S005`, `S010`, `S020`, `S025`, `S050` , `S100`, `TxPeriodText`, `SubtotalText`)';
							$sQuery .= ' VALUES ("' . $aMKios['KodeWH'] .'", "';
							$sQuery .= $aMKios['KodeSales'] .'", "';
							$sQuery .= $aMKios['CustomerGroup'] .'", "';
							$sQuery .= $aMKios['NamaCust'] .'", "';
							$sQuery .= $aMKios['TxPeriod'] .'", "';
							$sQuery .= $aMKios['KodeTerminal'] .'", "';
							$sQuery .= $aMKios['NoHP'] .'", "';
							$sQuery .= $aMKios['Subtotal'] .'", "';
							$sQuery .= $aMKios['S005'] .'", "';
							$sQuery .= $aMKios['S010'] .'", "';
							$sQuery .= $aMKios['S020'] .'", "';
							$sQuery .= $aMKios['S025'] .'", "';
							$sQuery .= $aMKios['S050'] .'", "';
							$sQuery .= $aMKios['S100'] . '", "';
							$sQuery .= $aMKios['TxPeriodText'] . '", "';
							$sQuery .= $aMKios['SubtotalText'] . '")';
	
							$aResult = $this->dbAction($sQuery);
	
							//check result is success or failure
							if ($aResult == 0)
							{
								$this->LogError('FATAL::databaseError::' . $this->dbError);
							}
						//}
					}
				}
				return $aResult;
			}

			function Update($aMKios)
			{
				$aResult = FALSE;

				if ( is_array($aMKios) && $aMKios['TxPeriod'] != '1970-01-01') //check that $aNewUser is an array
				{
					foreach( $aMKios as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//if ( $aMKios['S005'] > 0 && $aMKios['S010'] > 0 && $aMKios['S020'] > 0 && $aMKios['S025'] > 0 && $aMKios['S050'] > 0 && $aMKios['S100'] > 0)
					//{
						$sQuery  = 'UPDATE mkios ';
						$sQuery .= ' SET';
						$sQuery .= ' `TxPeriod` = "' . $aMKios['TxPeriod'] . '",';
						$sQuery .= ' `S005` = "' . $aMKios['S005'] .'", ';
						$sQuery .= ' `S010` = "' . $aMKios['S010'] .'", ';
						$sQuery .= ' `S020` = "' . $aMKios['S020'] .'", ';
						$sQuery .= ' `S025` = "' . $aMKios['S025'] .'", ';
						$sQuery .= ' `S050` = "' . $aMKios['S050'] .'", ';
						$sQuery .= ' `S100` = "' . $aMKios['S100'] . '"';
						$sQuery .= ' WHERE';
						$sQuery .= ' ID="' . $aMKios['ID'] . '"';

						$aResult = $this->dbAction($sQuery);

						//check result is success or failure
						if ($aResult == 0)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
					//}
				}
				return $aResult;
			}

			function VerifyFinance($aMKios)
			{
				$aResult = FALSE;

				if ( is_array($aMKios) ) //check that $aNewUser is an array
				{
					foreach( $aMKios as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					$sQuery  = 'UPDATE mkios ';
					$sQuery .= ' SET';
					$sQuery .= ' `FinanceStatus` = "1",';
					$sQuery .= ' `FinanceNotes` = "' . $aMKios['FinanceNotes'] .'" ';
					$sQuery .= ' WHERE';
					$sQuery .= ' ID="' . $aMKios['ID'] . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == 0)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				}
				return $aResult;
			}

			function CheckDuplicates($aMKios)
			{
				$sQuery = 'SELECT COUNT(ID) AS dataCount';
				$sQuery .= ' FROM mkios';
				$sQuery .= ' WHERE `KodeWH` = "' . $aMKios['KodeWH'] .'" ';
				$sQuery .= ' AND `KodeSales` = "' . $aMKios['KodeSales'] .'" ';
				//$sQuery .= ' AND `CustomerGroup` = "' . $aMKios['CustomerGroup'] .'" ';
				$sQuery .= ' AND `NamaCust` = "' . $aMKios['NamaCust'] .'" ';
				$sQuery .= ' AND `TxPeriod` = "' . $aMKios['TxPeriod'] .'" ';
				$sQuery .= ' AND `KodeTerminal` = "' . $aMKios['KodeTerminal'] .'" ';
				$sQuery .= ' AND `NoHP` = "' . $aMKios['NoHP'] .'" ';
				$sQuery .= ' AND `Subtotal` = "' . $aMKios['Subtotal'] .'" ';
				$sQuery .= ' AND `S005` = "' . $aMKios['S005'] .'" ';
				$sQuery .= ' AND `S010` = "' . $aMKios['S010'] .'" ';
				$sQuery .= ' AND `S020` = "' . $aMKios['S020'] .'" ';
				$sQuery .= ' AND `S025` = "' . $aMKios['S025'] .'" ';
				$sQuery .= ' AND `S050` = "' . $aMKios['S050'] .'" ';
				$sQuery .= ' AND `S100` = "' . $aMKios['S100'] .'" ';
				$sQuery .= ' AND `TxPeriodText` = "' . $aMKios['TxPeriodText'] .'" ';
				$sQuery .= ' AND `SubtotalText` = "' . $aMKios['SubtotalText'] .'"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult[0]['dataCount'];

			}

			function GetMKiosList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				/*$sQuery  = 'SELECT ID, KodeWH, KodeSales, CustomerGroup, NamaCust, TxPeriod, KodeTerminal, NoHP, Subtotal';

				switch ($aSearchByFieldArray['Product'])
				{
					case 'S005':
						$sQuery .= ',S005 ';
					break;
					case 'S010':
						$sQuery .= ',S010 ';
					break;
					case 'S020':
						$sQuery .= ',S020 ';
					break;
					case 'S025':
						$sQuery .= ',S025 ';
					break;
					case 'S050':
						$sQuery .= ',S050 ';
					break;
					case 'S100':
						$sQuery .= ',S100 ';
					break;
					default: //case 0 is here
						$sQuery .= ',S005, S010, S020, S025, S050, S100 ';
					break;
				}*/

				$sQuery = 'SELECT *';
				$sQuery .= ' FROM mkios';

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
						switch($key)
						{
							case 'ID':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							case 'TxPeriod':
								$sQuery .= ' ' . $key . ' ' . $value . '';
							break;
							case 'Product':
								switch ($value)
								{
									case 'S005':
										$sQuery .= ' (S005 > 0 OR S005 < 0)';
									break;
									case 'S010':
										$sQuery .= ' (S010 > 0 OR S010 < 0)';
									break;
									case 'S020':
										$sQuery .= ' (S020 > 0 OR S020 < 0)';
									break;
									case 'S025':
										$sQuery .= ' (S025 > 0 OR S025 < 0)';
									break;
									case 'S050':
										$sQuery .= ' (S050 > 0 OR S050 < 0)';
									break;
									case 'S100':
										$sQuery .= ' (S100 > 0 OR S100 < 0)';
									break;
									default: //case 0 is here
										$sQuery .= ' 1';
									break;
								}
							break;
							case 'FinanceStatus':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							default:
								//we check if $key contains the word Exact,
								if ( substr_count($key, 'Exact') > 0)
								{
									$sQuery .= ' ' . str_replace('Exact', '', $key) . ' = "' . $value . '"';
								}
								else
								{
									$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
								}
							break;
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
					$sQuery .= ' ID ASC';
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

			function GetMKiosListVoucherOnly( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				/*$sQuery  = 'SELECT ID, KodeWH, KodeSales, CustomerGroup, NamaCust, TxPeriod, KodeTerminal, NoHP, Subtotal';

				switch ($aSearchByFieldArray['Product'])
				{
					case 'S005':
						$sQuery .= ',S005 ';
					break;
					case 'S010':
						$sQuery .= ',S010 ';
					break;
					case 'S020':
						$sQuery .= ',S020 ';
					break;
					case 'S025':
						$sQuery .= ',S025 ';
					break;
					case 'S050':
						$sQuery .= ',S050 ';
					break;
					case 'S100':
						$sQuery .= ',S100 ';
					break;
					default: //case 0 is here
						$sQuery .= ',S005, S010, S020, S025, S050, S100 ';
					break;
				}*/

				$sQuery = 'SELECT ID, S005, S010, S020, S025, S050, S100';
				$sQuery .= ' FROM mkios';

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
						switch($key)
						{
							case 'ID':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							case 'TxPeriod':
								$sQuery .= ' ' . $key . ' ' . $value . '';
							break;
							case 'Product':
								switch ($value)
								{
									case 'S005':
										$sQuery .= ' (S005 > 0 OR S005 < 0)';
									break;
									case 'S010':
										$sQuery .= ' (S010 > 0 OR S010 < 0)';
									break;
									case 'S020':
										$sQuery .= ' (S020 > 0 OR S020 < 0)';
									break;
									case 'S025':
										$sQuery .= ' (S025 > 0 OR S025 < 0)';
									break;
									case 'S050':
										$sQuery .= ' (S050 > 0 OR S050 < 0)';
									break;
									case 'S100':
										$sQuery .= ' (S100 > 0 OR S100 < 0)';
									break;
									default: //case 0 is here
										$sQuery .= ' 1';
									break;
								}
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
				
				//$sQuery .= ' GROUP BY ID';

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
					$sQuery .= ' ID ASC';
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

			function GetMKiosListCustomerSubtotalOnly( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
set_time_limit(0);
				$this->CleanUpBadData();

				$aNamaCust = $this->GetNamaCustList();

				$x = date("Y-m-d", $aSearchByFieldArray["beginStamp"]);
				$y = date("Ym", $aSearchByFieldArray["beginStamp"]);
				$z = date("Ym", $aSearchByFieldArray["endStamp"]);

				$aResultData = array(); //the result array
				for ($i = $y; $i <= $z; $i++)
				{
					//we check if the last two digits have gone over 12, which is 
					//the maximum month in a year
					if (substr($i, 4,6) == 13)
					{
						$i += 100; //increase year
						$i -= 12; //decrease month
					}
//echo $i . "|"; //debugging

					//we loop the customer for each month
					for ($j = 0; $j < count($aNamaCust); $j++)
					{
						$year = substr($i, 0, 4);
						$month = substr($i, 4, 6);
						switch ($month)
						{
							case 2 :
								if ( ($year % 4) == 0 )
									$endDay = 29;
								else
									$endDay = 28;
							break;
							case 4:
							case 6:
							case 9:
							case 11:
								$endDay = 30;
							break;
							default:
								$endDay = 31;
							break;
						}
						$begin = $year . "-" . $month . "-01";
						$end = $year . "-" . $month . "-" . $endDay;

						$sQuery = 'SELECT NamaCust, SUM(Subtotal) AS MonthTotal';
						$sQuery .= ' FROM mkios';
						$sQuery .= ' WHERE NamaCust = "' . $aNamaCust[$j]['NamaCust'] . '"';
						$sQuery .= ' AND TxPeriod BETWEEN "' . $begin . '" AND "' . $end . '"';

						$aResult = $this->dbQuery($sQuery);

						$aResultData[] = array(
							"NamaCust" => $aNamaCust[$j]['NamaCust'],
							"MonthYear" => $begin,
							"MonthTotal" => $aResult[0]["MonthTotal"]
						);
					}
				}

				//we sort the data for display
				//the end array should be:
				//$array['NamaCust']['Month'] = total
				$aReturnArray = array();
				for ($i = 0; $i < count($aResultData); $i++)
				{
					$aReturnArray[$aResultData[$i]['NamaCust']][$aResultData[$i]['MonthYear']] = $aResultData[$i]['MonthTotal'];
				}

				return $aReturnArray;
			}

			function GetMKiosListAccountReceivable( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery = 'SELECT ID, KodeSales, TxPeriod, SUM(Subtotal) AS SalesTotal';
				$sQuery .= ' FROM mkios';

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
						switch($key)
						{
							case 'ID':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							case 'TxPeriod':
								$sQuery .= ' ' . $key . ' ' . $value . '';
							break;
							case 'Product':
								switch ($value)
								{
									case 'S005':
										$sQuery .= ' (S005 > 0 OR S005 < 0)';
									break;
									case 'S010':
										$sQuery .= ' (S010 > 0 OR S010 < 0)';
									break;
									case 'S020':
										$sQuery .= ' (S020 > 0 OR S020 < 0)';
									break;
									case 'S025':
										$sQuery .= ' (S025 > 0 OR S025 < 0)';
									break;
									case 'S050':
										$sQuery .= ' (S050 > 0 OR S050 < 0)';
									break;
									case 'S100':
										$sQuery .= ' (S100 > 0 OR S100 < 0)';
									break;
									default: //case 0 is here
										$sQuery .= ' 1';
									break;
								}
							break;
							case 'FinanceStatus':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							default:
								//we check if $key contains the word Exact,
								if ( substr_count($key, 'Exact') > 0)
								{
									$sQuery .= ' ' . str_replace('Exact', '', $key) . ' = "' . $value . '"';
								}
								else
								{
									$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
								}
							break;
						}
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY KodeSales, TxPeriod';

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
					$sQuery .= ' ID ASC';
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

			function GetKodeWHList()
			{
				$sQuery  = 'SELECT KodeWH';
				$sQuery .= ' FROM mkios';
				$sQuery .= ' WHERE 1';
				$sQuery .= ' GROUP BY KodeWH';

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid

				return $aResult;
			}

			function GetKodeSalesList( )
			{
				$sQuery  = 'SELECT KodeSales';
				$sQuery .= ' FROM mkios';
				$sQuery .= ' WHERE 1';
				$sQuery .= ' GROUP BY KodeSales';

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

			function GetNamaCustList()
			{
				$sQuery  = 'SELECT NamaCust';
				$sQuery .= ' FROM mkios';
				$sQuery .= ' WHERE 1';
				$sQuery .= ' GROUP BY NamaCust';

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

			function InsertPurchase($aMKios)
			{
				$aResult = FALSE;

				if ( is_array($aMKios) ) //check that $aNewUser is an array
				{
					foreach( $aMKios as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//if ( $aMKios['S005'] > 0 && $aMKios['S010'] > 0 && $aMKios['S020'] > 0 && $aMKios['S025'] > 0 && $aMKios['S050'] > 0 && $aMKios['S100'] > 0)
					//{
						$sQuery  = 'INSERT INTO mkios_purchase';
						$sQuery .= ' (`Date`, `S005`, `S010`, `S020`, `S025`, `S050` , `S100`, `Notes`)';
						$sQuery .= ' VALUES ("' . $aMKios['Date'] .'", "';
						$sQuery .= $aMKios['S005'] .'", "';
						$sQuery .= $aMKios['S010'] .'", "';
						$sQuery .= $aMKios['S020'] .'", "';
						$sQuery .= $aMKios['S025'] .'", "';
						$sQuery .= $aMKios['S050'] .'", "';
						$sQuery .= $aMKios['S100'] . '", "';
						$sQuery .= $aMKios['Notes'] . '")';

						$aResult = $this->dbAction($sQuery);

						//check result is success or failure
						if ($aResult == 0)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
					//}
				}
				return $aResult;
			}

			function UpdatePurchase($aMKios)
			{
				$aResult = FALSE;

				if ( is_array($aMKios) ) //check that $aNewUser is an array
				{
					foreach( $aMKios as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					if ( $aMKios['S005'] >= 0 && $aMKios['S010'] >= 0 && $aMKios['S020'] >= 0 && $aMKios['S025'] >= 0 && $aMKios['S050'] >= 0 && $aMKios['S100'] >= 0)
					{
						$sQuery  = 'UPDATE mkios_purchase ';
						$sQuery .= ' SET';
						$sQuery .= ' `Date` = "' . $aMKios['Date'] . '",';
						$sQuery .= ' `S005` = "' . $aMKios['S005'] .'", ';
						$sQuery .= ' `S010` = "' . $aMKios['S010'] .'", ';
						$sQuery .= ' `S020` = "' . $aMKios['S020'] .'", ';
						$sQuery .= ' `S025` = "' . $aMKios['S025'] .'", ';
						$sQuery .= ' `S050` = "' . $aMKios['S050'] .'", ';
						$sQuery .= ' `S100` = "' . $aMKios['S100'] . '",';
						$sQuery .= ' `Notes` = "' . $aMKios['Notes'] . '"';
						$sQuery .= ' WHERE';
						$sQuery .= ' ID="' . $aMKios['ID'] . '"';

						$aResult = $this->dbAction($sQuery);

						//check result is success or failure
						if ($aResult == 0)
						{
							$this->LogError('FATAL::databaseError::' . $this->dbError);
						}
					}
				}
				return $aResult;
			}

			function GetMKiosPurchaseList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				/*$sQuery  = 'SELECT ID, KodeWH, KodeSales, CustomerGroup, NamaCust, TxPeriod, KodeTerminal, NoHP, Subtotal';

				switch ($aSearchByFieldArray['Product'])
				{
					case 'S005':
						$sQuery .= ',S005 ';
					break;
					case 'S010':
						$sQuery .= ',S010 ';
					break;
					case 'S020':
						$sQuery .= ',S020 ';
					break;
					case 'S025':
						$sQuery .= ',S025 ';
					break;
					case 'S050':
						$sQuery .= ',S050 ';
					break;
					case 'S100':
						$sQuery .= ',S100 ';
					break;
					default: //case 0 is here
						$sQuery .= ',S005, S010, S020, S025, S050, S100 ';
					break;
				}*/

				$sQuery = 'SELECT *';
				$sQuery .= ' FROM mkios_purchase';

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
						switch($key)
						{
							case 'ID':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							case 'Date':
								$sQuery .= ' ' . $key . ' ' . $value . '';
							break;
							case 'Product':
								switch ($value)
								{
									case 'S005':
										$sQuery .= ' (S005 > 0 OR S005 < 0)';
									break;
									case 'S010':
										$sQuery .= ' (S010 > 0 OR S010 < 0)';
									break;
									case 'S020':
										$sQuery .= ' (S020 > 0 OR S020 < 0)';
									break;
									case 'S025':
										$sQuery .= ' (S025 > 0 OR S025 < 0)';
									break;
									case 'S050':
										$sQuery .= ' (S050 > 0 OR S050 < 0)';
									break;
									case 'S100':
										$sQuery .= ' (S100 > 0 OR S100 < 0)';
									break;
									default: //case 0 is here
										$sQuery .= ' 1';
									break;
								}
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
				
				//$sQuery .= ' GROUP BY ID';

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
					$sQuery .= ' ID ASC';
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

			function InsertDeposit($aDeposit)
			{
				$iResult = 0;

				/*if ($aDeposit["outlet_ID"] == 0)
				{
					echo "cannot insert deposit without outletID";
					die();
				}*/

				if ( !isset($aDeposit["mkios_payment_ID"]) )
				{
					$aDeposit["mkios_payment_ID"] = 0;
				}

				$sQuery  = 'INSERT INTO mkios_deposit';
				$sQuery .= ' (`Notes`, `Price`, `Date`, `mkios_payment_ID`)';
				//$sQuery .= ' VALUES ("' . $aDeposit["outlet_ID"] . '"';
				$sQuery .= ' VALUES ("' . $aDeposit["Notes"] . '"';
				$sQuery .= ' ,"' . $aDeposit["Price"] . '"';
				$sQuery .= ' ,"' . $aDeposit["Date"] . '"';
				$sQuery .= ' ,"' . $aDeposit["mkios_payment_ID"] . '")';

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

			function UpdateDeposit($aDeposit)
			{
				$iResult = $aDeposit["ID"];
			
				$sQuery  = 'UPDATE mkios_deposit';
				$sQuery .= ' SET `Notes` = "' . $aDeposit["Notes"] . '"';
				$sQuery .= ' ,`Price` = "' . $aDeposit["Price"] . '"';
				$sQuery .= ' ,`Date` = "' . $aDeposit["Date"] . '"';
				if ( isset($aDeposit["mkios_payment_ID"]) )
				{
					$sQuery .= ' ,`mkios_payment_ID` = "' . $aDeposit["mkios_payment_ID"] . '"';
				}
				if ( isset($aDeposit["FinanceNotes"]) )
				{
					$sQuery .= ' ,`FinanceNotes` = "' . $aDeposit["FinanceNotes"] . '"';
				}
				if ( isset($aDeposit["Status"]) )
				{
					$sQuery .= ' ,`Status` = "' . $aDeposit["Status"] . '"';
				}
				$sQuery .= ' WHERE ID = "' . $aDeposit["ID"] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $iResult;
			}

			function RemoveDeposit($iDepositID)
			{
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM mkios_deposit';
					$sQuery .= ' WHERE ID = "' . $iDepositID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}

			function VerifyDeposit($iID, $sNotes)
			{
				$sQuery  = 'UPDATE mkios_deposit';
				$sQuery .= ' SET `Status` = "1"';
				$sQuery .= ' ,`FinanceNotes` = "' . $sNotes . '"';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function GetDepositList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM mkios_deposit';

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
							$sQuery .= ' ' . $key . '="' . $value . '"';
						}
						elseif ($key == "DateBetween")
						{
							$sQuery .= ' ' . 'Date' . ' ' . $value . '';
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
				$sQuery .= ' Date ASC';
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

			function GetDepositByID($iDepositID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM mkios_deposit';
				$sQuery .= ' WHERE ID = "' . $iDepositID . '"';

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

			function GetInventory($aData)
			{
				//parameter input is date
				//the end result is total purchase, total sales, stock quantity
				$aResult = array(
					"S005_Purchase" => 0,
					"S005_Sales" => 0,
					"S005_Stock" => 0,
					"S010_Purchase" => 0,
					"S010_Sales" => 0,
					"S010_Stock" => 0,
					"S020_Purchase" => 0,
					"S020_Sales" => 0,
					"S020_Stock" => 0,
					"S025_Purchase" => 0,
					"S025_Sales" => 0,
					"S025_Stock" => 0,
					"S050_Purchase" => 0,
					"S050_Sales" => 0,
					"S050_Stock" => 0,
					"S100_Purchase" => 0,
					"S100_Sales" => 0,
					"S100_Stock" => 0
				);

				//we setup the search query parameters
				$aSearchParam = array(
					"Date" => $aData["Date"]
				);

				//we get all the purchase data by date
				$aPurchaseResult = $this->GetMKiosPurchaseList($aSearchParam);
				for ($i = 0; $i < count($aPurchaseResult); $i++)
				{
					$aResult['S005_Purchase'] += $aPurchaseResult[$i]['S005'];
					$aResult['S010_Purchase'] += $aPurchaseResult[$i]['S010'];
					$aResult['S020_Purchase'] += $aPurchaseResult[$i]['S020'];
					$aResult['S025_Purchase'] += $aPurchaseResult[$i]['S025'];
					$aResult['S050_Purchase'] += $aPurchaseResult[$i]['S050'];
					$aResult['S100_Purchase'] += $aPurchaseResult[$i]['S100'];
				}

				//we get all the sales data by date
				//remember to change the parameter Date to TxPeriod for sales
				$aSearchParam = array(
					"TxPeriod" => $aData["Date"]
				);
				//masterweb limits to 96mb, so we need some workaround here
				/*$aSalesResult = $this->GetMKiosListVoucherOnly($aSearchParam);
				for ($i = 0; $i < count($aSalesResult); $i++)
				{
					$aResult['S005_Sales'] += $aSalesResult[$i]['S005'];
					$aResult['S010_Sales'] += $aSalesResult[$i]['S010'];
					$aResult['S020_Sales'] += $aSalesResult[$i]['S020'];
					$aResult['S025_Sales'] += $aSalesResult[$i]['S025'];
					$aResult['S050_Sales'] += $aSalesResult[$i]['S050'];
					$aResult['S100_Sales'] += $aSalesResult[$i]['S100'];
				}*/
				
				$aResult['S005_Sales'] += $this->getSumByDate($aData["Date"], 'S005');
				$aResult['S010_Sales'] += $this->getSumByDate($aData["Date"], 'S010');
				$aResult['S020_Sales'] += $this->getSumByDate($aData["Date"], 'S020');
				$aResult['S025_Sales'] += $this->getSumByDate($aData["Date"], 'S025');
				$aResult['S050_Sales'] += $this->getSumByDate($aData["Date"], 'S050');
				$aResult['S100_Sales'] += $this->getSumByDate($aData["Date"], 'S100');

				$aResult['S005_Stock'] = $aResult['S005_Purchase'] - $aResult['S005_Sales'];
				$aResult['S010_Stock'] = $aResult['S010_Purchase'] - $aResult['S010_Sales'];
				$aResult['S020_Stock'] = $aResult['S020_Purchase'] - $aResult['S020_Sales'];
				$aResult['S025_Stock'] = $aResult['S025_Purchase'] - $aResult['S025_Sales'];
				$aResult['S050_Stock'] = $aResult['S050_Purchase'] - $aResult['S050_Sales'];
				$aResult['S100_Stock'] = $aResult['S100_Purchase'] - $aResult['S100_Sales'];

				return $aResult;
			}

			function GetProfitLoss($aData)
			{
				//setup the return array
				$aProfitLoss = array(
					"Purchase" => 0,
					"Sales" => 0,
					"GrossPL" => 0,
					"Expenses" => 0,
					"NetPL" => 0
				);

				//parameter input :
				//$aData['KodeWH'] = if empty equals all outlet
				//$aData['VTSProduct'] = if empty equals all products
				//$aData['Date'] = date start BETWEEN date end

				//first, we check the parameters
				//parameter product ID
				if ( $aData["VTSProduct"] == "" ) //this means we want all product
				{
					//get product list
					$aProductData = array(
						"S005", "S010", "S020", "S025", "S050", "S100"
					);
				}
				else
				{
					//get product by ID
					$aProductData = array( $aData["VTSProduct"] );
				}

				//parameter outlet ID
				if ( $aData["KodeWH"] == "" ) //this means we want all WH
				{
					$sOutletName = "All WH";
				}
				else
				{
					$sOutletName = $aData["KodeWH"];
					$sOutletData = array( $aData["KodeWH"] );
				}

				//parameter date
				//we want to split the date for opening and closing inventory
				list($sDateBegin, $sDateEnd) = explode("AND", $aData["Date"]);
				$sDateBegin = str_replace("BETWEEN", "", $sDateBegin); //remove the "BETWEEN" from the beginning
				$sDateBegin = str_replace("'", "", $sDateBegin); //remove the ' (single quotes)
				$sDateBegin = str_replace("'", "", $sDateBegin); //remove the ' (single quotes)
				$sDateBegin = trim($sDateBegin); //remove the whitespace from begin and end of string
				$sDateEnd = str_replace("'", "", $sDateEnd); //remove the ' (single quotes)
				$sDateEnd = trim($sDateEnd); //remove the whitespace from begin and end of string

				//preparation
				//mkios pricing system is different from the regular pricing
				//mkios price are stored in a table with effective date
				//so, we need to get the effective date table and actually split
				//the actual date if necessary
				$aSearchByFieldArray = array(
					"Type" => 0, //buy
				);
				$aSortByFieldArray = array(
					"EffectiveDate" => "DESC", //buy
				);
				$aMKiosBuyPrice = $this->GetMKiosPriceList($aSearchByFieldArray, $aSortByFieldArray);

				$aSearchByFieldArray = array(
					"Type" => 1, //sell
				);
				$aMKiosSellPrice = $this->GetMKiosPriceList($aSearchByFieldArray, $aSortByFieldArray);

				//logic :
				//in its basic form, the equation for profit loss is:
				//gross profit = sales - cost of goods sold
				//net profit = gross profit - expenses
				//
				//cost of goods sold = opening inventory (cost of inventory at beginning of period)
				//plus inventory purchased during the period
				//equals total inventory available during the period
				//less closing inventory (cost of all unsold stock)

				$iTotalSales = 0;
				//we setup the search query parameters
				$aSearchParam = array(
					"TxPeriod" => $aData["Date"]
				);
				//we get all the sales data by date
				$aSalesResult = $this->GetMKiosList($aSearchParam);

				//now we need to calculate the sales value in currency
				//by multiplying the quantity with price
				for ($i = 0; $i < count($aSalesResult); $i++)
				{
					//locate the row containing the right price
					$priceCounter = 0;
					for ($j = 0; $j < count($aMKiosSellPrice); $j++)
					{
						//is the sales date > than the first value in $aMKiosSellPrice
						if ( $aSalesResult[$i]['TxPeriod'] > $aMKiosSellPrice[$j]['EffectiveDate'] )
						{
							$priceCounter = $j;
							$j = count($aMKiosSellPrice);
						}
					}

					foreach ($aSalesResult[$i] as $key => $value)
					{
						switch($key)
						{
							case 'S005':
								$iTotalSales += $value * $aMKiosSellPrice[$priceCounter]['S005'];
							break;
							case 'S010':
								$iTotalSales += $value * $aMKiosSellPrice[$priceCounter]['S010'];
							break;
							case 'S020':
								$iTotalSales += $value * $aMKiosSellPrice[$priceCounter]['S020'];
							break;
							case 'S025':
								$iTotalSales += $value * $aMKiosSellPrice[$priceCounter]['S025'];
							break;
							case 'S050':
								$iTotalSales += $value * $aMKiosSellPrice[$priceCounter]['S050'];
							break;
							case 'S100':
								$iTotalSales += $value * $aMKiosSellPrice[$priceCounter]['S100'];
							break;
							default:
							break;
						}
					}
				}
				$aProfitLoss['Sales'] = $iTotalSales;

				$iTotalPurchase = 0;
				//we get the cost of goods by date

				$iOpeningInventory = 0;
				//first we want the opening inventory
				//we simply update the search query param date to opening date
				$aSearchParam["Date"] = " < '" . $sDateBegin . "'";
				$aInventoryOpening = $this->GetInventory($aSearchParam);

				//locate the row containing the right price
				$priceCounter = 0;
				for ($j = 0; $j < count($aMKiosBuyPrice); $j++)
				{
					//is the sales date > than the first value in $aMKiosSellPrice
					if ( $sDateBegin > $aMKiosBuyPrice[$j]['EffectiveDate'] )
					{
						$priceCounter = $j;
						$j = count($aMKiosBuyPrice);
					}
				}

				foreach ($aInventoryOpening as $key => $value)
				{
					switch($key)
					{
						case 'S005_Stock':
							$iOpeningInventory += $value * $aMKiosBuyPrice[$priceCounter]['S005'];
						break;
						case 'S010_Stock':
							$iOpeningInventory += $value * $aMKiosBuyPrice[$priceCounter]['S010'];
						break;
						case 'S020_Stock':
							$iOpeningInventory += $value * $aMKiosBuyPrice[$priceCounter]['S020'];
						break;
						case 'S025_Stock':
							$iOpeningInventory += $value * $aMKiosBuyPrice[$priceCounter]['S025'];
						break;
						case 'S050_Stock':
							$iOpeningInventory += $value * $aMKiosBuyPrice[$priceCounter]['S050'];
						break;
						case 'S100_Stock':
							$iOpeningInventory += $value * $aMKiosBuyPrice[$priceCounter]['S100'];
						break;
						default:
						break;
					}
				}

				$iPurchasedInventory = 0;
				//next we get inventory purchased during the period
				$aSearchParam = array(
					"Date" => $aData["Date"]
				);
				$aPurchaseResult = $this->GetMKiosPurchaseList($aSearchParam);

				//now we need to calculate the sales value in currency
				//by multiplying the quantity with price
				for ($i = 0; $i < count($aPurchaseResult); $i++)
				{
					//locate the row containing the right price
					$priceCounter = 0;
					for ($j = 0; $j < count($aMKiosBuyPrice); $j++)
					{
						//is the sales date > than the first value in $aMKiosSellPrice
						if ( $aPurchaseResult[$i]['Date'] > $aMKiosBuyPrice[$j]['EffectiveDate'] )
						{
							$priceCounter = $j;
							$j = count($aMKiosBuyPrice);
						}
					}

					foreach ($aPurchaseResult[$i] as $key => $value)
					{
						switch($key)
						{
							case 'ID':
							break;
							case 'S005':
								$iPurchasedInventory += $value * $aMKiosBuyPrice[$priceCounter]['S005'];
							break;
							case 'S010':
								$iPurchasedInventory += $value * $aMKiosBuyPrice[$priceCounter]['S010'];
							break;
							case 'S020':
								$iPurchasedInventory += $value * $aMKiosBuyPrice[$priceCounter]['S020'];
							break;
							case 'S025':
								$iPurchasedInventory += $value * $aMKiosBuyPrice[$priceCounter]['S025'];
							break;
							case 'S050':
								$iPurchasedInventory += $value * $aMKiosBuyPrice[$priceCounter]['S050'];
							break;
							case 'S100':
								$iPurchasedInventory += $value * $aMKiosBuyPrice[$priceCounter]['S100'];
							break;
							default:
							break;
						}
					}
				}

				$iClosingInventory = 0;
				$aClosingStock = array(
					"S005_Stock" => 0,
					"S005_Value" => 0,
					"S010_Stock" => 0,
					"S010_Value" => 0,
					"S020_Stock" => 0,
					"S020_Value" => 0,
					"S025_Stock" => 0,
					"S025_Value" => 0,
					"S050_Stock" => 0,
					"S050_Value" => 0,
					"S100_Stock" => 0,
					"S100_Value" => 0
				);
				//finally we want the closing inventory
				//we again update the search query param date to closing date
				$aSearchParam["Date"] = " <= '" . $sDateEnd . "'";
				$aInventoryClosing = $this->GetInventory($aSearchParam);

				//locate the row containing the right price
				$priceCounter = 0;
				for ($j = 0; $j < count($aMKiosBuyPrice); $j++)
				{
					//is the sales date > than the first value in $aMKiosSellPrice
					if ( $sDateEnd > $aMKiosBuyPrice[$j]['EffectiveDate'] )
					{
						$priceCounter = $j;
						$j = count($aMKiosBuyPrice);
					}
				}

				foreach ($aInventoryClosing as $key => $value)
				{
					switch($key)
					{
						case 'S005_Stock':
							$aClosingStock['S005_Stock'] = $value;
							$aClosingStock['S005_Price'] = $aMKiosBuyPrice[$priceCounter]['S005'];
							$aClosingStock['S005_Value'] = $aClosingStock['S005_Stock'] * $aClosingStock['S005_Price'];
							
							$iClosingInventory += $aClosingStock['S005_Value'];
						break;
						case 'S010_Stock':
							$aClosingStock['S010_Stock'] = $value;
							$aClosingStock['S010_Price'] = $aMKiosBuyPrice[$priceCounter]['S010'];
							$aClosingStock['S010_Value'] = $aClosingStock['S010_Stock'] * $aClosingStock['S010_Price'];
							$iClosingInventory += $aClosingStock['S010_Value'];
						break;
						case 'S020_Stock':
							$aClosingStock['S020_Stock'] = $value;
							$aClosingStock['S020_Price'] = $aMKiosBuyPrice[$priceCounter]['S020'];
							$aClosingStock['S020_Value'] = $aClosingStock['S020_Stock'] * $aClosingStock['S020_Price'];
							$iClosingInventory += $aClosingStock['S020_Value'];
						break;
						case 'S025_Stock':
							$aClosingStock['S025_Stock'] = $value;
							$aClosingStock['S025_Price'] = $aMKiosBuyPrice[$priceCounter]['S025'];
							$aClosingStock['S025_Value'] = $aClosingStock['S025_Stock'] * $aClosingStock['S025_Price'];
							$iClosingInventory += $aClosingStock['S025_Value'];
						break;
						case 'S050_Stock':
							$aClosingStock['S050_Stock'] = $value;
							$aClosingStock['S050_Price'] = $aMKiosBuyPrice[$priceCounter]['S050'];
							$aClosingStock['S050_Value'] = $aClosingStock['S050_Stock'] * $aClosingStock['S050_Price'];
							$iClosingInventory += $aClosingStock['S050_Value'];
						break;
						case 'S100_Stock':
							$aClosingStock['S100_Stock'] = $value;
							$aClosingStock['S100_Price'] = $aMKiosBuyPrice[$priceCounter]['S100'];
							$aClosingStock['S100_Value'] = $aClosingStock['S100_Stock'] * $aClosingStock['S100_Price'];
							$iClosingInventory += $aClosingStock['S100_Value'];
						break;
						default:
						break;
					}
				}

				$aProfitLoss['Purchase'] = ($iOpeningInventory + $iPurchasedInventory) - $iClosingInventory;

				$aProfitLoss['GrossPL'] = $aProfitLoss['Sales'] - $aProfitLoss['Purchase'];
				$aProfitLoss['Expenses'] = 0;
				$aProfitLoss['NetPL'] = $aProfitLoss['GrossPL'] - $aProfitLoss['Expenses'];
				$aProfitLoss['Stock'] = $aClosingStock;

				return $aProfitLoss;

/*

				//we will need to know which payment type is cash, so we query the db

				//now we start the process
				$aProfitLoss = array();

				
print_r($aInventoryOpening);
echo "<hr />";
print_r($aInventoryClosing);
die();

				//we loop by product ID
				for ($i = 0; $i < count($aProductData); $i++)
				{
set_time_limit(0);
					
					//processing data
					//Now, we have four arrays to process
					//$aPurchaseResult = new purchase for the period
					//$aSalesResult = sales for the period
					//$aInventoryOpening = inventory on beginning period
					//$aInventoryClosing = inventory on closing period


					//the product purchase price is an average price of purchased products for all outlets
					//for the search parameter, we omit the outlet_ID parameter
					$aSearchAvgParam = array(
						"Date" => " <= '" . $sDateEnd . "'",
						"product_ID" => $aProductData[$i]["ID"],
						"Price" => 0
					);
					$aSortByParam = array(
						"Date" => "DESC"
					);
					$aLimitByParam = array(
						"start" => 0,
						"nbOfData" => 5
					);
					$iAvgPurchasePrice = $cPurchase->GetAveragePurchasePriceByProduct($aSearchAvgParam, $aSortByParam, $aLimitByParam);

					$iTotalPurchase = 0;
					$iTotalPurchaseDisplay = 0;
					$iTotalPurchaseNonCash = 0;
					$aPurchaseNonCash = array();
					//from the purchase result, we will need only the product ID, quantity and price
					foreach ($aPurchaseResult as $key => $value)
					{
						if ( in_array($value["paymentType_ID"], $aPaymentTypeCash)
							||  ($value["Status"] == 1 && !in_array($value["paymentType_ID"], $aPaymentTypeCash) )
						)
						{
							//do nothing, purchase value is not listed in the main page
						}
						else
						{
							$iTotalPurchaseNonCash += ($value["Quantity"] * $iAvgPurchasePrice); // $value["Price"]
							$aPurchaseNonCash[] = $value;
						}

						$iTotalPurchase += ($value["Quantity"] * $iAvgPurchasePrice); // $value["Price"]
					}

					$iTotalPurchaseDisplay = $iTotalPurchase; //this is only for display. because outlet can have zero purchase, only transfer in, however the value of transfer in is used to calculate total purchase

					//only happen for outlet specific result
					if ( $aData["outlet_ID"] > 0 )
					{
						foreach ($aTransferInResult as $key => $value)
						{
							$iTotalPurchase += ($value["quantity"] * $iAvgPurchasePrice); // $value["Price"]
						}

						foreach ($aTransferOutResult as $key => $value)
						{
							$iTotalPurchase -= ($value["quantity"] * $iAvgPurchasePrice); // $value["Price"]
						}
					}

					$iTotalSales = 0;
					$iTotalSalesCash = 0;
					$iTotalSalesNonCash = 0;
					$aSalesCash = array();
					$aSalesNonCash = array();
					//from the purchase result, we will need only the product ID, quantity and price
					foreach ($aSalesResult as $key => $value)
					{
						if ( in_array($value["paymentType_ID"], $aPaymentTypeCash)
							||  ($value["Status"] == 1 && !in_array($value["paymentType_ID"], $aPaymentTypeCash) )
						)
						{
							$iTotalSalesCash += ($value["Quantity"] * $value["Price"] * ((100 - $value["Discount"]) / 100) );
							$aSalesCash[] = $value;
						}
						else
						{
							$iTotalSalesNonCash += ($value["Quantity"] * $value["Price"] * ((100 - $value["Discount"]) / 100) );
							$aSalesNonCash[] = $value;
						}

						$iTotalSales += ($value["Quantity"] * $value["Price"] * ((100 - $value["Discount"]) / 100) );
					}

					$iInventoryOpening = 0;
					$iInventoryOpeningQuantity = 0;
					foreach($aInventoryOpening as $key => $value)
					{
						$iInventoryOpening += $value['total_quantity'] * $iAvgPurchasePrice;
						$iInventoryOpeningQuantity += $value['total_quantity'];
					}

					$iInventoryClosing = 0;
					$iInventoryClosingQuantity = 0;
					foreach($aInventoryClosing as $key => $value)
					{
						$iInventoryClosing += $value['total_quantity'] * $iAvgPurchasePrice;
						$iInventoryClosingQuantity += $value['total_quantity'];
					}

					$aProfitLoss[] = array(
						"Product_ID" => $aProductData[$i]["ID"],
						"Avg_Purchase_Price" => $iAvgPurchasePrice,
						"Total_Purchase" => $iTotalPurchase,
						"Total_Purchase_Display" => $iTotalPurchaseDisplay,
						"Total_Purchase_Non_Cash" => $iTotalPurchaseNonCash,
						"Data_Purchase_Non_Cash" => $aPurchaseNonCash,
						"Total_Sales" => $iTotalSales,
						"Total_Sales_Cash" => $iTotalSalesCash,
						"Data_Sales_Cash" => $aSalesCash,
						"Total_Sales_Non_Cash" => $iTotalSalesNonCash,
						"Data_Sales_Non_Cash" => $aSalesNonCash,
						"Opening_Inventory" => $iInventoryOpening,
						"Opening_Inventory_Quantity" => $iInventoryOpeningQuantity,
						"Closing_Inventory" => $iInventoryClosing,
						"Closing_Inventory_Quantity" => $iInventoryClosingQuantity
					);
				}

				return $aProfitLoss;
*/
			}

			function getSumByDate($aDate, $sVoucher)
			{
				$sQuery  = 'SELECT SUM('. $sVoucher .') AS amount';
				$sQuery .= ' FROM mkios';
				$sQuery .= ' WHERE 1 ';
				
				$sQuery .= 'AND TxPeriod ' . $aDate . '';

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid
				foreach( $aResult as $key => $value )
				{
					foreach( $value as $key2 => $value2 )
					{
						$value2 = stripslashes($value2);
					}
				}

				return $aResult[0]['amount'];
			}

			/*** MKIOS PAYMENT ***/
			function SaveMKiosPayment($aData)
			{
				$iResult = 0;

				//remember to reset the bank_ID to 0 if it is cash payment
				if ($aData['IsCash'] == 1)
				{
					$aData['bank_ID'] = 0;
				}

				if ($aData['ID'] == 0)
				{
					$sQuery  = 'INSERT INTO mkios_payment';
					$sQuery .= ' (`mkios_ID`, `Date`, `Amount`, `Notes`, `IsCash`, `bank_ID`)';
					$sQuery .= ' VALUES ("' . $aData["mkios_ID"] . '"';
					$sQuery .= ' ,"' . $aData['Date'] . '"';
					$sQuery .= ' ,"' . $aData["Amount"] . '"';
					$sQuery .= ' ,"' . $aData["Notes"] . '"';
					$sQuery .= ' ,"' . $aData["IsCash"] . '"';
					$sQuery .= ' ,"' . $aData["bank_ID"] . '")';
				}
				else
				{
					$sQuery  = 'UPDATE mkios_payment SET';
					$sQuery .= ' `mkios_ID` = "' . $aData["mkios_ID"] . '",';
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

					//we check if there is already a data on both deposit and bank_deposit tables
					//sales_payment and deposit is 1:1 relationship
					$aSearchArray = array(
						"mkios_payment_ID" => $iResult
					);
					$aDeposit = $this->GetDepositList($aSearchArray);

					//get the ID of related bank_deposit
					$aSearch = array(
						"mkios_payment_ID" => " = " . $iResult
					);
					include_once('classBank.php');
					$cBank = new Bank;
					$aBank = $cBank->GetMKiosDepositList($aSearch);

					//now we have to either insert or update the deposit / bank_deposit tables
					if ($aData['bank_ID'] == 0)
					//is cash
					{
						$aDepositData = array(
							"Notes" => $aData['Notes'],
							"Price" => $aData['Amount'],
							"Date" => $aData['Date'],
							"mkios_payment_ID" => $iResult
						);

						//insert/update deposit
						if ( count($aDeposit) > 0 )
						{
							//update deposit
							$aDepositData['ID'] = $aDeposit[0]['ID'];
							$this->UpdateDeposit($aDepositData);
						}
						else
						{
							//insert into deposit
							$this->InsertDeposit($aDepositData);
						}

						//check if there is a bank_deposit data, if yes, delete it
						if ( count($aBank) > 0)
						{
							$cBank->RemoveMKiosDeposit($aBank[0]['ID']);
						}
					}
					else
					//is bank deposit
					{
						$aBankDeposit = array(
							'bank_ID' => $aData["bank_ID"],
							'mkios_payment_ID' => $iResult,
							'Notes' => $aData['Notes'],
							'Price' => $aData['Amount'],
							'Date' => $aData['Date']
						);

						//insert/update bank_deposit
						if ( count($aBank) > 0 )
						{
							$aBankDeposit['ID'] = $aBank[0]['ID'];
						}

						$cBank->SaveMKiosDeposit($aBankDeposit);

						//check if there is a deposit data, if yes, delete it
						if ( count($aDeposit) > 0)
						{
							$this->RemoveDeposit($aDeposit[0]['ID']);
						}
					}
				}

				return $iResult;
			}

			function RemoveMKiosPayment($iID)
			{
				$sQuery  = 'DELETE FROM mkios_payment ';
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
					$sQuery  = 'DELETE FROM mkios_deposit ';
					$sQuery .= ' WHERE mkios_payment_ID = "' . $iID . '"';
	
					$aResult = $this->dbAction($sQuery);
					
					$sQuery  = 'DELETE FROM mkios_bank_deposit ';
					$sQuery .= ' WHERE mkios_payment_ID = "' . $iID . '"';
	
					$aResult = $this->dbAction($sQuery);
				}
			}

			public function LoadMKiosPayment($iID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM mkios_payment';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult;
			}

			function ListMKiosPayment($aParam)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM mkios_payment';
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

			/*** MKIOS PRICE ***/
			public function SavePrice($aData)
			{
				$iID = 0;
				//do we need to sanitize input ?

				//we check if $aData['ID'] is set and is > 0
				//if yes, we setup an update query
				//else, we setup an insert query
				if (isset($aData['ID']) && $aData['ID'] > 0 )
				{
					$sQuery  = 'UPDATE mkios_price';
					$sQuery .= ' SET `Type` = "' . $aData['Type'] . '"';
					$sQuery .= ' , `EffectiveDate` = "' . $aData['EffectiveDate'] . '"';
					$sQuery .= ' , `S005` = "' . $aData['S005'] . '"';
					$sQuery .= ' , `S010` = "' . $aData['S010'] . '"';
					$sQuery .= ' , `S020` = "' . $aData['S020'] . '"';
					$sQuery .= ' , `S025` = "' . $aData['S025'] . '"';
					$sQuery .= ' , `S050` = "' . $aData['S050'] . '"';
					$sQuery .= ' , `S100` = "' . $aData['S100'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';
					
					$iID = $aData['ID'];
				}
				else
				{
					$sQuery  = 'INSERT INTO mkios_price';
					$sQuery .= ' (`Type`, `EffectiveDate`, `S005`, `S010`, `S020`, `S025`, `S050`, `S100`)';
					$sQuery .= ' VALUES ("' . $aData['Type'] .'", "' . $aData['EffectiveDate'] .'", "' . $aData['S005'] .'", "' . $aData['S010'] .'", "' . $aData['S020'] .'", "' . $aData['S025'] .'", "' . $aData['S050'] .'", "' . $aData['S100'] .'")';
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

			function GetMKiosPriceList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery = 'SELECT *';
				$sQuery .= ' FROM mkios_price';

				//search by field
				if ( count($aSearchByFieldArray) > 0 )
				{
					$i = 0;
					$sQuery .= ' WHERE';
					foreach ($aSearchByFieldArray as $key => $value )
					{
						switch($key)
						{
							case 'ID':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							case 'Type':
								$sQuery .= ' ' . $key . '=' . $value . '';
							break;
							case 'EffectiveDate':
								$sQuery .= ' ' . $key . ' ' . $value . '';
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
				
				//$sQuery .= ' GROUP BY ID';

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
					$sQuery .= ' ID ASC';
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
			/*** MKIOS PRICE ***/

			/*** MKIOS VTS ***/
			function InsertVTS($aData)
			{
				$aResult = FALSE;

				$sQuery  = "SELECT ID";
				$sQuery .= " FROM VTS_Transactions ";
				$sQuery .= " WHERE 1";
				$sQuery .= " AND ID = '". $aData['ID'] ."'";
				$aResult = $this->dbQuery($sQuery);

				$aDBField = array();
				$aDBValues = array();
				foreach ($aData as $key => $value)
				{
					$aDBField[] = "`" . $key . "`";
					$aDBValues[] = "'" . $value . "'";
				}

				if (count($aResult) == 0 )
				{
					//INSERT
					$sQuery  = "INSERT INTO VTS_Transactions";
					$sQuery .= " (";
					foreach ($aDBField as $key => $value)
					{
						$sQuery .= $value . ",";
					}
					$sQuery = substr($sQuery, 0, strlen($sQuery)-1);
					$sQuery .= " )";
					$sQuery .= " VALUES";
					$sQuery .= " (";
					foreach ($aDBValues as $key => $value)
					{
						$sQuery .= $value . ",";
					}
					$sQuery = substr($sQuery, 0, strlen($sQuery)-1);
					$sQuery .= " )";
				}
				else
				{
					$sQuery  = "UPDATE VTS_Transactions";
					$sQuery .= " SET";
					foreach ($aData as $key => $value)
					{
						$sQuery .= "`" . $key . "` = '" . $value . "',";
					}
					$sQuery = substr($sQuery, 0, strlen($sQuery)-1);
					$sQuery .= " WHERE";
					$sQuery .= " ID = '". $aData['ID'] ."'";
				}

				$aResult = $this->dbAction($sQuery);

				//TODO:
				//for UPDATE, I have to create triggers if something changes
				//to update the table mkios, for example, the import only import
				//the last 3 days, so if any update is made after 3 days, it will
				//not be reflected in the mkios system.

				return $aResult;
			}

			function CheckVTSDelete($aIDs, $sDateStart, $sDateEnd)
			{
				if (is_array($aIDs))
				{
					$sQuery  = "SELECT ID, DocNumber";
					$sQuery .= " FROM VTS_Transactions ";
					$sQuery .= " WHERE 1";
					$sQuery .= " AND TxPeriod BETWEEN '". $sDateStart ."' AND '". $sDateEnd ."'";
					$aResult = $this->dbQuery($sQuery);
	
					$sQuery = "";
					for ($i = 0; $i < count($aResult);$i++)
					{
						if ( !in_array($aResult[$i]['ID'], $aIDs) )
						{
							$sQuery .= "DELETE FROM VTS_Transactions";
							$sQuery .= " WHERE ID = '". $aResult[$i]['ID'] ."';";
							$sQuery .= " DELETE FROM mkios";
							$sQuery .= " WHERE VTS_DocNumber LIKE '%". $aResult[$i]['DocNumber'] ."%';";
						}
					}

					if ($sQuery != "")
					{
						$this->dbAction($sQuery);
					}
				}
			}

			function ConvertVTSToMKios($sDateStart, $sDateEnd)
			{
				//DocNumber : VJ = Sales, VU = Sales Return
				//Status : MANUAL (can be edited), SENT (cannot be edited)
				//read VTS_Transactions
				//get corresponding mkios row
				//update the mkios row or insert

				$sQuery  = "SELECT DISTINCT(DocNumber)";
				$sQuery .= " FROM VTS_Transactions";
				$sQuery .= " WHERE 1";
				$sQuery .= " AND TxPeriod BETWEEN '". $sDateStart ."' AND '". $sDateEnd ."'";
				$sQuery .= " ORDER BY DocNumber ASC"; //we put this here, so VJ is always before VU

				$resultDocNumber = $this->dbQuery($sQuery);

				foreach ($resultDocNumber as $key => $value)
				{
					set_time_limit(0);

					$sQuery  = "SELECT KodeWH, KodeSales, CustomerGroup,";
					$sQuery .= " NamaCustomer, TxPeriod, KodeTerminal, NoHP,";
					$sQuery .= " Status, KodeBarang, Jumlah, Harga ";
					$sQuery .= " FROM VTS_Transactions";
					$sQuery .= " WHERE 1";
					$sQuery .= " AND DocNumber='". $value['DocNumber'] ."'";
					$result_VTS = $this->dbQuery($sQuery);

					$aMKios = array(
						"ID" => 0,
						"KodeWH" => $result_VTS[0]['KodeWH'],
						"KodeSales" => $result_VTS[0]['KodeSales'],
						"CustomerGroup" => $result_VTS[0]['CustomerGroup'],
						"NamaCust" => $result_VTS[0]['NamaCustomer'],
						"TxPeriod" => $result_VTS[0]['TxPeriod'],
						"KodeTerminal" => $result_VTS[0]['KodeTerminal'],
						"NoHP" => $result_VTS[0]['NoHP'],
						"Subtotal" => 0,
						"S005" => 0,
						"S010" => 0,
						"S020" => 0,
						"S025" => 0,
						"S050" => 0,
						"S100" => 0,
						"VTS_DocNumber" => $value['DocNumber']
					);

					foreach ($result_VTS as $VTSkey => $VTSvalue)
					{
						if ( substr_count($aMKios['VTS_DocNumber'], 'VU') > 0 )
						{
							$aMKios['Subtotal'] -= $VTSvalue['Jumlah'] * $VTSvalue['Harga'];
							$aMKios[$VTSvalue['KodeBarang']] -= $VTSvalue['Jumlah'];
						}
						else
						{
							$aMKios['Subtotal'] += $VTSvalue['Jumlah'] * $VTSvalue['Harga'];
							$aMKios[$VTSvalue['KodeBarang']] += $VTSvalue['Jumlah'];
						}
					}

					$sQuery  = "SELECT ID, Subtotal, S005, S010, S020, S025,";
					$sQuery .= " S050, S100, VTS_DocNumber";
					$sQuery .= " FROM mkios";
					$sQuery .= " WHERE 1";
					$sQuery .= " AND TxPeriod = '". $aMKios['TxPeriod'] ."'";
					$sQuery .= " AND NoHP = '". $aMKios['NoHP'] ."'";
					$result_mkios = $this->dbQuery($sQuery);

					if ( count($result_mkios) > 0 )
					{
						$aMKios['ID'] = $result_mkios[0]['ID'];
						if (substr_count($result_mkios[0]['VTS_DocNumber'], $aMKios['VTS_DocNumber']) > 0)
						{
							$aMKios['VTS_DocNumber'] = $result_mkios[0]['VTS_DocNumber'];

							$aMKios['Subtotal'] = $result_mkios[0]['Subtotal'];
							$aMKios['S005'] = $result_mkios[0]['S005'];
							$aMKios['S010'] = $result_mkios[0]['S010'];
							$aMKios['S020'] = $result_mkios[0]['S020'];
							$aMKios['S025'] = $result_mkios[0]['S025'];
							$aMKios['S050'] = $result_mkios[0]['S050'];
							$aMKios['S100'] = $result_mkios[0]['S100'];
						}
						else
						{
							$aMKios['Subtotal'] = $result_mkios[0]['Subtotal'] + $aMKios['Subtotal'];
							$aMKios['S005'] = $result_mkios[0]['S005'] + $aMKios['S005'];
							$aMKios['S010'] = $result_mkios[0]['S010'] + $aMKios['S010'];
							$aMKios['S020'] = $result_mkios[0]['S020'] + $aMKios['S020'];
							$aMKios['S025'] = $result_mkios[0]['S025'] + $aMKios['S025'];
							$aMKios['S050'] = $result_mkios[0]['S050'] + $aMKios['S050'];
							$aMKios['S100'] = $result_mkios[0]['S100'] + $aMKios['S100'];

							$aMKios['VTS_DocNumber'] .= "," . $result_mkios[0]['VTS_DocNumber'];
						}
					}

					$this->Save($aMKios);
				}
			}
			/*** MKIOS VTS ***/

			function LogError($sError)
			{
				/*include('dirConf.php');
				$fError = fopen($logPath . '/error.log', 'a');
				fwrite($fError, 'ERROR::' . $sError . '::IN::' . $_SERVER['SCRIPT_NAME'] . '::FROM::' . $_SERVER['REMOTE_ADDR'] . '::ON::' . date("D M j G:i:s T Y") . "\r\n" );
				fclose($fError);*/
				//header("location:error.php");
			}
			
			function debug_me()
			{
				$sQuery  = "SELECT * FROM `mkios` WHERE 1 AND TxPeriod = '2014-12-05'";
				$result = $this->dbQuery($sQuery);
$fp = fopen("../log/debug.txt", "w");
fclose($fp);
				foreach ($result as $key => $value)
				{
					$data = "";
					foreach($value as $key2 => $value2)
					{
						if ( is_numeric($key2) )
							$data .= $value2 . ";";
					}
$fp = fopen("../log/debug.txt", "a");
fwrite($fp, $data . "\r\n");
fclose($fp);					
				}
			}
		}
		//*** END FUNCTION **********************************************//
	}
?>
