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
	* lib/classExpenses.php :: EXPENSES CLASS					*
	******************************************************************
	* All related expenses function							*
	*													*
	* Version		: 0.1									*
	* Author		: FireSnakeR 								*
	* Created		: 2011-12-14 								*
	* Last modified	: 2012-05-05							*
	* 													*
	*****************************************************************/

	if ( !class_exists('Expenses') )
	{
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion +++++++++++++++++++++++++++++++//
	
		class Expenses extends Database
		{
			var $ID				= FALSE;
			var $Name			= FALSE;
			var $Price			= FALSE;
			var $Date			= FALSE;

			//*** BEGIN FUNCTION LIST ****************************//
			// Insert()
			// Expenses($iExpensesID = 0)
			// GetExpensesList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetExpensesByID($iExpensesID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Expenses($iExpensesID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iExpensesID) && $iExpensesID > 0 ) //check $iExpensesID is numeric and positive value
					{
						$aExpenses = $this->GetExpensesByID($iExpensesID);

						if (is_array($aExpenses) && count($aExpenses) == 1) //check $aExpenses is an array and has exactly one data
						{
							$this->ID = $aExpenses[0]['ID'];
							$this->Name = $aExpenses[0]['Name'];
							$this->Price = $aExpenses[0]['Price'];
							$this->Date = $aExpenses[0]['Date'];
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
						if ( $iExpensesID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iExpensesID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aExpenses)
			{
				$iResult = 0;

				if ($aExpenses["outlet_ID"] == 0)
				{
					echo "cannot insert expenses without outletID";
					die();
				}

				$sQuery  = 'INSERT INTO expenses';
				$sQuery .= ' (`outlet_ID`, `expenses_category_ID`,  `Name`, `Price`, `Date`)';
				$sQuery .= ' VALUES ("' . $aExpenses["outlet_ID"] . '"';
				$sQuery .= ' ,"' . $aExpenses["expenses_category_ID"] . '"';
				$sQuery .= ' ,"' . $aExpenses["Name"] . '"';
				$sQuery .= ' ,"' . $aExpenses["Price"] . '"';
				$sQuery .= ' ,"' . $aExpenses["Date"] . '")';

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

			function Update($aExpenses)
			{
				$iResult = $aExpenses["ID"];
			
				$sQuery  = 'UPDATE expenses';
				$sQuery .= ' SET `Name` = "' . $aExpenses["Name"] . '"';
				$sQuery .= ' ,`expenses_category_ID` = "' . $aExpenses["expenses_category_ID"] . '"';
				//$sQuery .= ' ,`Notes` = "' . $aExpenses["Notes"] . '"';
				$sQuery .= ' ,`Price` = "' . $aExpenses["Price"] . '"';
				$sQuery .= ' ,`Date` = "' . $aExpenses["Date"] . '"';
				$sQuery .= ' WHERE ID = "' . $aExpenses["ID"] . '"';

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
				$sQuery  = 'UPDATE expenses';
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

			function Remove($iExpensesID)
			{
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM expenses';
					$sQuery .= ' WHERE expenses.ID = "' . $iExpensesID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}

			function GetExpensesList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM expenses';

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
				
				$sQuery .= ' GROUP BY expenses.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' expenses.ID ASC';
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
			
			function GetExpensesReport( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM expenses';

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
						elseif ($key == "expenses_category_ID")
						{
							if ($value != "")
							{
								$sQuery .= ' ' . $key . ' = ' . $value;
							}
							else
							{
								$sQuery .= ' 1 ';
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
				
				$sQuery .= ' GROUP BY expenses.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' expenses.ID ASC';
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

			function GetExpensesReportByFinanceArea( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM expenses';
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
	
				$sQuery .= ' GROUP BY expenses.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' expenses.ID ASC';
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

			function GetExpensesByID($iExpensesID)
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM expenses';
				$sQuery .= ' WHERE ID = "' . $iExpensesID . '"';

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

			//Expenses Category
			function SaveExpensesCategory($aData)
			{
				$iID = 0;
				//do we need to sanitize input ?

				//we check if $aData['ID'] is set and is > 0
				//if yes, we setup an update query
				//else, we setup an insert query
				if (isset($aData['ID']) && $aData['ID'] > 0 )
				{
					$sQuery  = 'UPDATE expenses_category';
					$sQuery .= ' SET `Name` = "' . $aData['Name'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';
					
					$iID = $aData['ID'];
				}
				else
				{
					$sQuery  = 'INSERT INTO expenses_category';
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

			function RemoveExpensesCategory($iID)
			{
				$sQuery  = 'DELETE FROM expenses_category';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function LoadExpensesCategory($iID)
			{
				$sQuery  = 'SELECT ID, Name';
				$sQuery .= ' FROM expenses_category';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult;
			}

			function GetExpensesCategoryList( $aSearch )
			{
				$sQuery  = 'SELECT ID, Name';
				$sQuery .= ' FROM expenses_category';
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
			//Expenses Category

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
