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
	* lib/classNews.php :: NEWS CLASS								*
	*********************************************************************
	* All related news function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2012-10-03 										*
	* Last modified	: 2012-10-03										*
	* 																	*
	*********************************************************************/

	if ( !class_exists('News') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class News extends Database
		{
			var $ID			= FALSE;
			var $Description	= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// News($iID = 0)
			// Insert($aEvent)
			// Update($aEvent)
			// Remove($iEventID)
			// GetEventList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetNewsByID($iID)
			// GetNextPrevIDByCurrentID($sDirection = "next", $iEventID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function News($iID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iID) && $iID > 0 ) //check $iID is numeric and positive value
					{
						$aNews = $this->GetNewsByID($iID);

						if (is_array($aNews) && count($aNews) == 1) //check $aNews is an array and has exactly one data
						{
							$this->ID = $aNews[0]['ID'];
							$this->Description = $aNews[0]['Description'];
						}
						else
						{
							//log and report that news ID does not exists
							$this->LogError('WARNING::Invalid news ID::' . $iID);
						}
					}
					else
					{
						//log and report that a non numeric value has been inserted
						if ( $iID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aData)
			{
				if ( is_array($aData) ) //check that $aEvent is an array
				{
					$sQuery  = 'INSERT INTO news';
					$sQuery .= ' (`Description`, `Created`, `Modified`)';
					$sQuery .= ' VALUES ("' . $aData['Description'] .'",';
					$sQuery .= ' "' . date('YmdHis') .'",';
					$sQuery .= ' "' . date('YmdHis') .'")';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
					return $aResult;
				}
			}
			
			function Update($aData)
			{
				$aResult = 0;
				if ( is_array($aData) ) //check that $aEvent is an array
				{
					$sQuery  = 'UPDATE news';
					$sQuery .= ' SET `Description` = "' . $aData['Description'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aData['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}
			
			function Remove($iID)
			{
				$sQuery  = 'DELETE FROM news';
				$sQuery .= ' WHERE news.ID = "' . $iID . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function GetNewsList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT ID, Description AS description, Created';
				$sQuery .= ' FROM news';

				//verify that $aSearchByFieldArray value is not empty
				foreach ($aSearchByFieldArray as $key => $value)
				{
					if ($value == "")
					{
						array_pop($aSearchByFieldArray);
					}
				}
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
				
				$sQuery .= ' GROUP BY ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' ID DESC';
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
			
			function GetNewsByID($iID)
			{
				$sQuery  = 'SELECT ID, Description';
				$sQuery .= ' FROM news';
				$sQuery .= ' WHERE ID = "' . $iID . '"';

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
			
			function GetNextPrevIDByCurrentID($sDirection = "next", $iID)
			{
				$iResultID = $iID; //initialize the result ID to match the input parameter product ID to show end of record reached when both are the same number

				$sQuery  = 'SELECT';
				$sQuery .= " case when sign(ID - " . $iID . ") > 0 then 'next' else 'prev' end as dir,";
				$sQuery .= " case when sign(ID - " . $iID . ") > 0 then min(ID)";
				$sQuery .= " when sign(ID - " . $iID . ") < 0 then max(ID) end as ID";
				$sQuery .= " FROM news";
				$sQuery .= " where ID <> " . $iID;
				$sQuery .= " group by sign(ID - " . $iID . ")";
				$sQuery .= " order by sign(ID - " . $iID . ")";

				$aResult = $this->dbQuery($sQuery);

				//TODO:check result is valid

				for ($i = 0; $i < count($aResult); $i++)
				{
					if ($aResult[$i]['dir'] == $sDirection)
						$iResultID = $aResult[$i]['ID'];
				}

				return $iResultID;
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
