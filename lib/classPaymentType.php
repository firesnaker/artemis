<?php
	/********************************************************************
	* lib/classPaymentType.php :: PAYMENTTYPE CLASS								*
	*********************************************************************
	* All related paymentType function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2011-12-14 										*
	* Last modified	: 2011-12-14										*
	* 																	*
	* 				Copyright (c) 2011 FireSnakeR						*
	*********************************************************************/

	if ( !class_exists('PaymentType') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class PaymentType extends Database
		{
			var $ID				= FALSE;
			var $Name			= FALSE;
			var $IsCash			= FALSE;
			var $PLNoCount			= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// PaymentType($iPaymentTypeID = 0)
			// Insert($aPaymentType)
			// Update($aPaymentType)
			// Remove($iPaymentTypeID)
			// GetPaymentTypeList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			// GetPaymentTypeByID($iPaymentTypeID)
			// GetNextPrevIDByCurrentID($sDirection = "next", $iPaymentTypeID)
			// LogError($sError)
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function PaymentType($iPaymentTypeID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iPaymentTypeID) && $iPaymentTypeID > 0 ) //check $iPaymentTypeID is numeric and positive value
					{
						$aPaymentType = $this->GetPaymentTypeByID($iPaymentTypeID);

						if (is_array($aPaymentType) && count($aPaymentType) == 1) //check $aPaymentType is an array and has exactly one data
						{
							$this->ID = $aPaymentType[0]['ID'];
							$this->Name = $aPaymentType[0]['Name'];
							$this->IsCash = $aPaymentType[0]['IsCash'];
							$this->PLNoCount = $aPaymentType[0]['PLNoCount'];
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
						if ( $iPaymentTypeID <> -1 )
						{
							$this->LogError('WARNING::Invalid numeric value::' . $iPaymentTypeID);
						}
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aPaymentType)
			{
				if ( is_array($aPaymentType) ) //check that $aPaymentType is an array
				{
					foreach( $aPaymentType as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO paymentType';
						$sQuery .= ' (`Name`, `IsCash`, `PLNoCount`)';
						$sQuery .= ' VALUES ("' . $aPaymentType['Name'] .'",';
						$sQuery .= ' "' . $aPaymentType['IsCash'] .'",';
						$sQuery .= ' "' . $aPaymentType['PLNoCount'] .'")';

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
			
			function Update($aPaymentType)
			{
				$aResult = 0;
				if ( is_array($aPaymentType) ) //check that $aPaymentType is an array
				{
					foreach( $aPaymentType as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					$sQuery  = 'UPDATE paymentType';
					$sQuery .= ' SET `Name` = "' . $aPaymentType['Name'] . '",';
					$sQuery .= ' `IsCash` = "' . $aPaymentType['IsCash'] . '",';
					$sQuery .= ' `PLNoCount` = "' . $aPaymentType['PLNoCount'] . '"';
					$sQuery .= ' WHERE `ID` = "' . $aPaymentType['ID'] . '"';

					$aResult = $this->dbAction($sQuery);
	
					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}

					return $aResult;
				}
			}
			
			function Remove($iPaymentTypeID)
			{
				include("dirConf.php");
				
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM paymentType';
					$sQuery .= ' WHERE paymentType.ID = "' . $iPaymentTypeID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}

			function GetPaymentTypeList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT paymentType.ID AS ID, paymentType.Name AS Name, paymentType.IsCash AS IsCash, PLNoCount';
				$sQuery .= ' FROM paymentType';

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
				
				$sQuery .= ' GROUP BY paymentType.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				if ( count($aSortByArray) > 0 )
				{
					foreach($aSortByArray as $key => $value)
					{
						$sQuery .= ' ' . $key . ' ' . $value;
						$sQuery .= ' , ';
					}
					$sQuery = substr($sQuery, 0, strlen($sQuery)-2 );
				}
				else
				{
					$sQuery .= ' paymentType.Name ASC';
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
			
			function GetPaymentTypeByID($iPaymentTypeID)
			{
				$sQuery  = 'SELECT ID, Name, IsCash, PLNoCount';
				$sQuery .= ' FROM paymentType';
				$sQuery .= ' WHERE ID = "' . $iPaymentTypeID . '"';

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
			
			function GetIDByName($sName)
			{
				$sQuery  = 'SELECT ID';
				$sQuery .= ' FROM paymentType';
				$sQuery .= ' WHERE Name LIKE "%' . $sName . '%"';

				$aResult = $this->dbQuery($sQuery);

				return $aResult;
			}
			
			function GetNextPrevIDByCurrentID($sDirection = "next", $iPaymentTypeID)
			{
				$iResultID = $iPaymentTypeID; //initialize the result ID to match the input parameter product ID to show end of record reached when both are the same number

				$sQuery  = 'SELECT';
				$sQuery .= " case when sign(ID - " . $iPaymentTypeID . ") > 0 then 'next' else 'prev' end as dir,";
				$sQuery .= " case when sign(ID - " . $iPaymentTypeID . ") > 0 then min(ID)";
				$sQuery .= " when sign(ID - " . $iPaymentTypeID . ") < 0 then max(ID) end as ID";
				$sQuery .= " FROM paymentType";
				$sQuery .= " where ID <> " . $iPaymentTypeID;
				$sQuery .= " group by sign(ID - " . $iPaymentTypeID . ")";
				$sQuery .= " order by sign(ID - " . $iPaymentTypeID . ")";

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