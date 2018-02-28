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
	* lib/classEmployee.php :: EMPLOYEE CLASS								*
	*********************************************************************
	* All related employee and employeeOutlet function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2011-09-12 										*
	* Last modified	: 2011-09-12										*
	* 																	*
	*********************************************************************/

	if ( !class_exists('Employee') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Employee extends Database
		{
			var $ID				= FALSE;
			var $Name			= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Employee($iEmployeeID = 0)
			// Insert($aEmployee)
			// Update($aEmployee)
			// Remove($iEmployeeID)
			// GetEmployeeList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetEmployeeByID($iEmployeeID)
			// GetNextPrevIDByCurrentID($sDirection = "next", $iEmployeeID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Employee($iEmployeeID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iEmployeeID) && $iEmployeeID > 0 ) //check $iEmployeeID is numeric and positive value
					{
						$aEmployee = $this->GetEmployeeByID($iEmployeeID);

						if (is_array($aEmployee) && count($aEmployee) == 1) //check $aEmployee is an array and has exactly one data
						{
							$this->ID = $aEmployee[0]['ID'];
							$this->Name = $aEmployee[0]['Name'];
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
						if ( $iEmployeeID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iEmployeeID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aEmployee)
			{
				if ( is_array($aEmployee) ) //check that $aEmployee is an array
				{
					foreach( $aEmployee as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO employee';
						$sQuery .= ' (`Name`)';
						$sQuery .= ' VALUES ("' . $aEmployee['Name'] .'")';

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
			
			function Update($aEmployee)
			{
				$aResult = 0;
				if ( is_array($aEmployee) ) //check that $aEmployee is an array
				{
					foreach( $aEmployee as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					$sQuery  = 'UPDATE employee';
					$sQuery .= ' SET `Name` = "' . $aEmployee['Name'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aEmployee['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}
			
			function Remove($iEmployeeID)
			{
				include("dirConf.php");
				
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM employee';
					$sQuery .= ' WHERE employee.ID = "' . $iEmployeeID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}

			function GetEmployeeList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT employee.ID AS ID, employee.Name AS Name';
				$sQuery .= ' FROM employee';

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
				
				$sQuery .= ' GROUP BY employee.ID';

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
					$sQuery .= ' employee.Name ASC';
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
			
			function GetEmployeeByID($iEmployeeID)
			{
				$sQuery  = 'SELECT ID, Name';
				$sQuery .= ' FROM employee';
				$sQuery .= ' WHERE ID = "' . $iEmployeeID . '"';

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
			
			function GetNextPrevIDByCurrentID($sDirection = "next", $iEmployeeID)
			{
				$iResultID = $iEmployeeID; //initialize the result ID to match the input parameter product ID to show end of record reached when both are the same number

				$sQuery  = 'SELECT';
				$sQuery .= " case when sign(ID - " . $iEmployeeID . ") > 0 then 'next' else 'prev' end as dir,";
				$sQuery .= " case when sign(ID - " . $iEmployeeID . ") > 0 then min(ID)";
				$sQuery .= " when sign(ID - " . $iEmployeeID . ") < 0 then max(ID) end as ID";
				$sQuery .= " FROM employee";
				$sQuery .= " where ID <> " . $iEmployeeID;
				$sQuery .= " group by sign(ID - " . $iEmployeeID . ")";
				$sQuery .= " order by sign(ID - " . $iEmployeeID . ")";

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid

				for ($i = 0; $i < count($aResult); $i++)
				{
					if ($aResult[$i]['dir'] == $sDirection)
						$iResultID = $aResult[$i]['ID'];
				}

				return $iResultID;
			}

			//*** employee OUTLET ***//

			function AddEmployeeOutlet($aEmployee)
			{
				if ( is_array($aEmployee) ) //check that $aEmployee is an array
				{
					if ($aEmployee['outlet_ID'] == 0)
					{
						echo "cannot insert employee outlet without outletID";
						die();
					}

					if ($aEmployee['employee_ID'] == 0)
					{
						echo "cannot insert employee outlet without employeeID";
						die();
					}

					foreach( $aEmployee as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO employeeOutlet';
						$sQuery .= ' (`outlet_ID`, `employee_ID`)';
						$sQuery .= ' VALUES ("' . $aEmployee['outlet_ID'] .'"';
						$sQuery .= ' , "' . $aEmployee['employee_ID'] .'")';

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

			function RemoveEmployeeOutlet($aEmployee)
			{
				$sQuery  = 'DELETE FROM employeeOutlet';
				$sQuery .= ' WHERE outlet_ID = "' . $aEmployee['outlet_ID'] . '"';
				$sQuery .= ' AND employee_ID = "' . $aEmployee['employee_ID'] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function GetEmployeeOutletList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM employeeOutlet, employee';
				$sQuery .= ' WHERE employeeOutlet.employee_ID = employee.ID';

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
					$sQuery .= ' AND';
					foreach ($aSearchByFieldArray as $key => $value )
					{
						//$sQuery .= ' ' . $key . ' like "%' . $value . '%"';
						$sQuery .= ' ' . $key . ' = "' . $value . '"';
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY employeeOutlet.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' employee.Name ASC';
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

			//*** employee OUTLET ***//

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
