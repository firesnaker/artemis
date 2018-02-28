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
	* lib/classDeposit.php :: DEPOSIT CLASS						*
	******************************************************************
	* All related deposit function							*
	*													*
	* Version		: 0.1									*
	* Author		: FireSnakeR 								*
	* Created		: 2012-02-10 								*
	* Last modified	: 2012-02-10							*
	* 													*
	*****************************************************************/

	if ( !class_exists('Deposit') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Deposit extends Database
		{
			var $ID				= FALSE;
			var $Notes			= FALSE;
			var $Price			= FALSE;
			var $Date				= FALSE;
			var $Status			= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Insert()
			// Deposit($iDepositID = 0)
			// GetDepositList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetDepositByID($iDepositID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Deposit($iDepositID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iDepositID) && $iDepositID > 0 ) //check $iDepositID is numeric and positive value
					{
						$aDeposit = $this->GetDepositByID($iDepositID);

						if (is_array($aDeposit) && count($aDeposit) == 1) //check $aDeposit is an array and has exactly one data
						{
							$this->ID = $aDeposit[0]['ID'];
							$this->Notes = $aDeposit[0]['Notes'];
							$this->Price = $aDeposit[0]['Price'];
							$this->Date = $aDeposit[0]['Date'];
							$this->Status = $aDeposit[0]['Status'];
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
						if ( $iDepositID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iDepositID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aDeposit)
			{
				$iResult = 0;

				if ($aDeposit["outlet_ID"] == 0)
				{
					echo "cannot insert deposit without outletID";
					die();
				}

				if (!isset($aDeposit["salesPayment_ID"]))
				{
					$aDeposit["salesPayment_ID"] = 0;
				}

				$sQuery  = 'INSERT INTO deposit';
				$sQuery .= ' (`outlet_ID`,  `salesPayment_ID`, `Notes`, `Price`, `Date`)';
				$sQuery .= ' VALUES ("' . $aDeposit["outlet_ID"] . '"';
				$sQuery .= ' ,"' . $aDeposit["salesPayment_ID"] . '"';
				$sQuery .= ' ,"' . $aDeposit["Notes"] . '"';
				$sQuery .= ' ,"' . $aDeposit["Price"] . '"';
				$sQuery .= ' ,"' . $aDeposit["Date"] . '")';

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

			function Update($aDeposit)
			{
				$iResult = $aDeposit["ID"];
			
				$sQuery  = 'UPDATE deposit';
				$sQuery .= ' SET `Notes` = "' . $aDeposit["Notes"] . '"';
				$sQuery .= ' ,`Price` = "' . $aDeposit["Price"] . '"';
				$sQuery .= ' ,`Date` = "' . $aDeposit["Date"] . '"';
				if ( isset($aDeposit["FinanceNotes"]) )
				{
					$sQuery .= ' ,`FinanceNotes` = "' . $aDeposit["FinanceNotes"] . '"';
				}
				if ( isset($aDeposit["Status"]) )
				{
					$sQuery .= ' ,`Status` = "' . $aDeposit["Status"] . '"';
				}
				if ( isset($aDeposit["salesPayment_ID"]) )
				{
					$sQuery .= ' ,`salesPayment_ID` = "' . $aDeposit["salesPayment_ID"] . '"';
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

			function Verify($iID, $sNotes)
			{
				$sQuery  = 'UPDATE deposit';
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

			function Remove($iDepositID)
			{
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM deposit';
					$sQuery .= ' WHERE deposit.ID = "' . $iDepositID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}

			function GetDepositList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM deposit';

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
						elseif ($key == "outlet_ID")
						{
							$sQuery .= ' ' . $key . '="' . $value . '"';
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
				
				$sQuery .= ' GROUP BY deposit.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' deposit.ID ASC';
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
			
			function GetDepositReport( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM deposit';

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
							$sQuery .= ' ' . $key . ' = ' . $value;
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
				
				$sQuery .= ' GROUP BY deposit.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' deposit.ID ASC';
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

			function GetDepositReportByFinanceArea( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM deposit';
				$sQuery .= ' WHERE 1 ';

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
					foreach ($aSearchByFieldArray as $key => $value )
					{
						if ($key == "Date")
						{
							$sQuery .= ' ' . $key . ' ' . $value;
						}
						elseif ($key == "outlet_ID")
						{
							$sQuery .= ' ' . $key . ' = ' . $value;
						}
						else
						{
							if ($key != "AllOutlet")
							{
								$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
							}
						}
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
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

				$sQuery .= ' GROUP BY deposit.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' deposit.ID ASC';
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

			function GetDepositByDateByOutlet($sDate, $iOutletID)
			{
				$sQuery  = 'SELECT SUM(Price) AS PriceSum';
				$sQuery .= ' FROM deposit';
				$sQuery .= ' WHERE outlet_ID = "' . $iOutletID . '"';
				$sQuery .= ' AND Date = "' . $sDate . '"';

				$aQueryResult = $this->dbQuery($sQuery);

				$aResult = array(
					"PriceSum" => ($aQueryResult[0]['PriceSum'] == '')?'0':$aQueryResult[0]['PriceSum']
				);

				return $aResult;
			}

			function GetDepositByID($iDepositID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM deposit';
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
