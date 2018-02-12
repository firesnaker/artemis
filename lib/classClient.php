<?php
	/********************************************************************
	* lib/classClient.php :: CLIENT CLASS								*
	*********************************************************************
	* All related client function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2011-10-08 										*
	* Last modified	: 2011-10-08										*
	* 																	*
	* 				Copyright (c) 2011 FireSnakeR						*
	*********************************************************************/

	if ( !class_exists('Client') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class Client extends Database
		{
			var $ID				= FALSE;
			var $Name			= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// Client($iClientID = 0)
			// Insert($aClient)
			// Update($aClient)
			// Remove($iClientID)
			// GetClientList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetClientByID($iClientID)
			// GetNextPrevIDByCurrentID($sDirection = "next", $iClientID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function Client($iClientID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iClientID) && $iClientID > 0 ) //check $iClientID is numeric and positive value
					{
						$aClient = $this->GetClientByID($iClientID);

						if (is_array($aClient) && count($aClient) == 1) //check $aClient is an array and has exactly one data
						{
							$this->ID = $aClient[0]['ID'];
							$this->Name = $aClient[0]['Name'];
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
						if ( $iClientID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iClientID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aClient)
			{
				if ( is_array($aClient) ) //check that $aClient is an array
				{
					foreach( $aClient as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO client';
						$sQuery .= ' (`Name`)';
						$sQuery .= ' VALUES ("' . $aClient['Name'] .'")';

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
			
			function Update($aClient)
			{
				$aResult = 0;
				if ( is_array($aClient) ) //check that $aClient is an array
				{
					foreach( $aClient as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					$sQuery  = 'UPDATE client';
					$sQuery .= ' SET `Name` = "' . $aClient['Name'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aClient['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}
			
			function Remove($iClientID)
			{
				include("dirConf.php");
				
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM client';
					$sQuery .= ' WHERE client.ID = "' . $iClientID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}

			function GetClientList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT client.ID AS ID, client.Name AS Name';
				$sQuery .= ' FROM client';

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
						if ($key == "ID")
						{
							$sQuery .= ' ' . $key . ' = "' . $value . '"';
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
				
				$sQuery .= ' GROUP BY client.ID';

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
					$sQuery .= ' Name ASC';
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

			function GetClientListExact( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT client.ID AS ID, client.Name AS Name';
				$sQuery .= ' FROM client';

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
						$sQuery .= ' ' . $key . ' = "' . $value . '"';
							
						if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
						{
							$sQuery .= ' AND ';
						}

						$i++;
					}
				}
				
				$sQuery .= ' GROUP BY client.ID';

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
					$sQuery .= ' Name ASC';
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

			function GetClientByID($iClientID)
			{
				$sQuery  = 'SELECT ID, Name';
				$sQuery .= ' FROM client';
				$sQuery .= ' WHERE ID = "' . $iClientID . '"';

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

			//*** client OUTLET ***//

			function AddClientOutlet($aClient)
			{
				if ( is_array($aClient) ) //check that $aClient is an array
				{
					foreach( $aClient as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO clientOutlet';
						$sQuery .= ' (`outlet_ID`, `client_ID`)';
						$sQuery .= ' VALUES ("' . $aClient['outlet_ID'] .'"';
						$sQuery .= ' , "' . $aClient['client_ID'] .'")';

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

			function RemoveClientOutlet($aClient)
			{
				$sQuery  = 'DELETE FROM clientOutlet';
				$sQuery .= ' WHERE outlet_ID = "' . $aClient['outlet_ID'] . '"';
				$sQuery .= ' AND client_ID = "' . $aClient['client_ID'] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function GetClientOutletList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM clientOutlet, client';
				$sQuery .= ' WHERE clientOutlet.client_ID = client.ID';

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
					$sQuery .= ' AND ';
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
				
				$sQuery .= ' GROUP BY clientOutlet.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' client.Name ASC';
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

			//*** client OUTLET ***//


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