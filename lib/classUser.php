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
	* lib/classUser.php :: USER CLASS													*
	*************************************************************************
	* User object including user roles													*
	*																								*
	* List of Tables :																		*
	* user				: stores login credentials										*
	* userRole			: stores the user role											*
	* userRoleMap		: stores the mapping between user and userRole			*
	* userPermission	: stores map of userAction with userRoleMap				*
	* userAction		: stores available user actions		 						*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2007-09-30				 										*
	* Last modified	: 2015-02-09														*
	* 																								*
	************************************************************************/

	if ( !class_exists('FSR_User') )
	{
		class FSR_User
		{
			private $ID;
			private $Username;
			private $Password;
			private $Created;
			private $Modified;
			private $db;
			private $outlet_ID; //redundant, to be removed once the user RBAC is established properly.

			private $isSignedIn;

			public function __construct()
			{
				require_once("classDatabase.php");

				include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
				include_once($rootPath . "config.php");

				$this->db = new FSR_Database(_DBTYPE_, _DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_);
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

			public function SignIn($aUserLogin, $aDefaultLogin)
			{
				$result = FALSE;

				//is table user empty ?
				//if yes, insert $aDefaultLogin from siteConf.php
				$aParam = array(
					"fields" => "COUNT(ID) as totalUser",
					"tables" => "user"
				);
				$result_user = $this->db->dbSearch($aParam);
				if ( $result_user[0]['totalUser'] == 0 ) //user table is empty
				{
					$this->Username = $aDefaultLogin['username'];
					$this->Created = date("Y-m-d H:i:s");
					$this->Modified = time();
					$this->Password = $this->encryptValue($aDefaultLogin['password']);
					$this->setUser();
				}

				$aParam = array(
					"fields" => "ID, Password",
					"tables" => "user",
					"query" => "Username = '" . $aUserLogin['loginName'] . "'"
				);
				$result_user = $this->db->dbSearch($aParam);

				if (
					count($result_user) > 0 
					&& $result_user[0]['Password'] == $this->encryptValue($aUserLogin['loginPassword'])
				)
				{
					$result = $result_user[0]['ID'];
				}

				return $result;
			}

			public function getUser($iID)
			{
				//load all properties from db
				$result = $this->db->dbLoad($iID, "user");

				foreach ($this as $key => $value)
				{
					if ( isset($result[$key]) )
					{
						$this->setProperty($key, $result[$key]);
					}
				}
			}

			public function setUser()
			{
				//save all properties to db
				$param = array();
				foreach ($this as $key => $value)
				{
					if ( in_array($key, $this->db->dbTableFields("user")) )
					{
						$param[$key] = $value;
					}
				}

				return $this->db->dbSave($param, "user");
			}

			public function deleteUser($iID)
			{
				return $this->db->dbDelete($iID, "user");
			}

			public function listUser($aData=array())
			{
				$param = array(
					"fields" => "ID, Username, Password",
					"tables" => "user"
				);
				return $this->db->dbSearch($param);
			}

			private function encryptValue($sValue)
			{
				return md5($sValue);
			}

			private function decryptValue($sValue)
			{
				return $sValue;
			}
		}
	}

	if ( !class_exists('User') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/classDatabase.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class User extends Database
		{
			var $ID			= FALSE;
			var $Name		= FALSE;
			var $Level		= FALSE;
			var $Outlet_ID	= FALSE;
			var $IsFinance	= FALSE;
			var $Username	= FALSE;
			var $Email		= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// User();
			// Insert()
			// Update()
			// UpdateWithoutValidation()
			// Remove
			// Login()
			// GetUserList()
			// GetUserByID()
			// GetUserByUsername()
			// LogError()
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function User($iUserID = 0)
			{
				$this->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

				if ( $this->dbError == FALSE )
				{
					if ( is_numeric($iUserID) && $iUserID > 0 ) //check $iUserID is numeric and positive value
					{
						$aUser = $this->GetUserByID($iUserID);

						if (is_array($aUser) && count($aUser) == 1) //check $aUser is an array and has exactly one data
						{
							$this->ID = $aUser[0]['ID'];
							$this->Name = $aUser[0]['Name'];
							$this->Level = $aUser[0]['Level'];
							$this->Outlet_ID = $aUser[0]['outlet_ID'];
							$this->IsFinance = $aUser[0]['IsFinance'];
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
					}
				}
				else
				{
					//log and report that database cannot be opened
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}
			}

			function Insert($aNewUser)
			{
				$aResult = FALSE;

				if ( is_array($aNewUser) ) //check that $aNewUser is an array
				{
					foreach( $aNewUser as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}
	
					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO user';
						$sQuery .= ' (`Name`, `outlet_ID`, `IsFinance`, `Username`, `Password`, `Email`, `Created`, `Modified`)';
						$sQuery .= ' VALUES ("' . $aNewUser['Name'] .'", "';
						$sQuery .= $aNewUser['Outlet'] .'", "';
						$sQuery .= $aNewUser['IsFinance'] .'", "';
						$sQuery .= $aNewUser['Username'] .'", "';
						$sQuery .= md5($aNewUser['Password']) .'", "';
						$sQuery .= $aNewUser['Email'] .'",';
						$sQuery .= ' "' . date('YmdHis') . '",';
						$sQuery .= ' "' . date('YmdHis') . '")';

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

			function Update($aUser)
			{
				$aResult = 0;
				if ( is_array($aUser) ) //check that $aNewUser is an array
				{
					foreach( $aUser as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//get UserData for validation of password
					$aUserData = $this->GetUserByID($aUser['ID']);
					if ( count($aUserData) > 0 && $aUserData[0]['Password'] == md5($aUser['OldPassword']) )
					{
						$sQuery  = 'UPDATE user';
						$sQuery .= ' SET `Name` = "' . $aUser['Name'] . '",';
						$sQuery .= ' `Username` = "' . $aUser['Username'] . '",';
						$sQuery .= ' `Password` = "' . md5($aUser['Password']) . '",';
						$sQuery .= ' `Email` = "' . $aUser['Email'] . '",';
						$sQuery .= ' `Modified` = "' . date('YmdHis') . '"';
						$sQuery .= ' WHERE `ID` = "' . $aUser['ID'] . '"';

						$aResult = $this->dbAction($sQuery);
	
						//check result is success or failure
						if ($aResult == 0)
						{
							$this->logError('FATAL::databaseError::' . $this->dbError);
						}
						else
						{
							//update data into user class variables
							$this->User($aUser['ID']);
						}
					}
					return $aResult;
				}
			}
			
			function UpdateWithoutValidation($aUser)
			{
				$aResult = 0;
				if ( is_array($aUser) ) //check that $aNewUser is an array
				{
					foreach( $aUser as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					$sQuery  = 'UPDATE user';
					$sQuery .= ' SET `Name` = "' . $aUser['Name'] . '",';
					$sQuery .= ' `outlet_ID` = "' . $aUser['Outlet'] . '",';
					$sQuery .= ' `IsFinance` = "' . $aUser['IsFinance'] . '",';
					$sQuery .= ' `Username` = "' . $aUser['Username'] . '",';
					$sQuery .= ' `Password` = "' . md5($aUser['Password']) . '",';
					$sQuery .= ' `Email` = "' . $aUser['Email'] . '",';
					$sQuery .= ' `Modified` = "' . date('YmdHis') . '"';
					$sQuery .= ' WHERE `ID` = "' . $aUser['ID'] . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == 0)
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}
					else
					{
						//update data into user class variables
						$this->User($aUser['ID']);
					}

					return $aResult;
				}
			}
			
			function Remove($iUserID)
			{
				include("dirConf.php");
				
				//if ( $this->validateDataInput($aNewUser) ) //validate data input
				//{
					$sQuery  = 'DELETE FROM user';
					$sQuery .= ' WHERE user.ID = "' . $iUserID . '"';

					$aResult = $this->dbAction($sQuery);

					//check result is success or failure
					if ($aResult == FALSE)
					{
						$this->LogError('FATAL::databaseError::' . $this->dbError);
					}
				//}
				return $aResult;
			}
			
			function Login($aUserLogin, $aDefaultLogin)
			{
				$loginStatus = 0;

				//is table user empty ? if yes, cross-check with siteConf.php - $aDefaultLogin.
				$sQuery = 'SELECT COUNT(ID) as totalUser FROM user';
				$aResult = $this->dbQuery($sQuery);
	
				if ( $aResult[0]['totalUser'] == 0 ) //user table is empty
				{
					$aUserRecord = array(
						"Name" => $aDefaultLogin['username'],
						"Level" => 9,
						"Username" => $aDefaultLogin['username'],
						"Password" => $aDefaultLogin['password']
					);
					$this->Insert($aUserRecord);
				}

				$sQuery  = 'SELECT ID FROM user';
				$sQuery .= ' WHERE user.Username = "'.$aUserLogin['loginName'].'"';
				$sQuery .= ' AND user.Password = "'. md5($aUserLogin['loginPassword']) .'"';
				$aResult = $this->dbQuery($sQuery);

				if ( count($aResult) > 0 )
				{
					$loginStatus = $aResult[0]['ID'];
				}

				return $loginStatus;
			}
			
			function GetUserList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT *';
				$sQuery .= ' FROM user';

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
				
				$sQuery .= ' GROUP BY user.ID';

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
					$sQuery .= ' user.Name ASC';
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
			
			function GetUserByID($iUserID)
			{
				if ( is_numeric($iUserID) && $iUserID > 0 ) //check $iUserID is numeric and positive value
				{
					$sQuery = 'SELECT * FROM user WHERE ID="'. $iUserID . '"';

					$aResult = $this->dbQuery($sQuery);

					if ($aResult <> 0)
					{
						foreach( $aResult as $key => $value )
						{
							foreach( $value as $key2 => $value2 )
							{
								$value2 = stripslashes($value2);
							}
						}
					}
					else
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}
	
					return $aResult;
				}
			}

			function GetUserByUsername($sUsername)
			{
				$sQuery = 'SELECT * FROM user WHERE Username="'. $sUsername . '"';

				$aResult = $this->dbQuery($sQuery);

				if ($aResult <> 0)
				{
					foreach( $aResult as $key => $value )
					{
						foreach( $value as $key2 => $value2 )
						{
							$value2 = stripslashes($value2);
						}
					}
				}
				else
				{
					$this->logError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			//*** user OUTLET ***//

			function AddUserOutlet($aUser)
			{
				if ( is_array($aUser) ) //check that $aUser is an array
				{
					if ($aUser['outlet_ID'] == 0)
					{
						echo "cannot insert user outlet without outletID";
						die();
					}

					if ($aUser['user_ID'] == 0)
					{
						echo "cannot insert user outlet without userID";
						die();
					}

					foreach( $aUser as $key => $value ) //addslashes to avoid SQL Injection
					{
						$value = addslashes($value);
					}

					//if ( $this->validateDataInput($aNewUser) ) //validate data input
					//{
						$sQuery  = 'INSERT INTO userOutlet';
						$sQuery .= ' (`outlet_ID`, `user_ID`)';
						$sQuery .= ' VALUES ("' . $aUser['outlet_ID'] .'"';
						$sQuery .= ' , "' . $aUser['user_ID'] .'")';

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

			function RemoveUserOutlet($aUser)
			{
				$sQuery  = 'DELETE FROM userOutlet';
				$sQuery .= ' WHERE outlet_ID = "' . $aUser['outlet_ID'] . '"';
				$sQuery .= ' AND user_ID = "' . $aUser['user_ID'] . '"';

				$aResult = $this->dbAction($sQuery);

				//check result is success or failure
				if ($aResult == FALSE)
				{
					$this->LogError('FATAL::databaseError::' . $this->dbError);
				}

				return $aResult;
			}

			function GetUserOutletList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
			{
				$sQuery  = 'SELECT userOutlet.ID AS ID, userOutlet.user_ID AS user_ID, userOutlet.outlet_ID as outlet_ID';
				$sQuery .= ' FROM userOutlet, user';
				$sQuery .= ' WHERE userOutlet.user_ID = user.ID';

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
				
				$sQuery .= ' GROUP BY userOutlet.ID';

				//sort by
				$sQuery .= ' ORDER BY';
				$sQuery .= ' user.Name ASC';
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

			//*** user OUTLET ***//



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
	
			
	
			//+++ BEGIN Constructor +++++++++++++++++++++++++++++++++++++//
/*
			
*/
			//+++ END Constructor +++++++++++++++++++++++++++++++++++++++//
			//+++ BEGIN Data Validation +++++++++++++++++++++++++++++++++//
	/*
			function validateDataInput($aData)
			{
				include("_dirConf.php");
				include_once($_libPath . 'classValidator.php');
				$cValidator = new Validator;
				
				$bValidData = TRUE;
				$this->error = "Data invalid! Please check your data.\n";
	
				foreach ( $aData as $key => $value )
				{
					if ( $value <> '' && $key == 'userID' && !is_numeric($value) ) //userID is_numeric
					{
						$bValidData = FALSE;
						$this->logError('WARNING::Invalid numeric value::' . $value);
						
					}
	
					if ( $key == 'userName' && $cValidator->isAlphanumericPlus($value) == FALSE ) //userName is alphanumeric + special character '
					{
						$bValidData = FALSE;
						$this->error .= "user name must be alphanumeric and/or contain the character '.\n";
					}
	
					if ( $key == 'userLogin' && $cValidator->isAlphanumeric($value) == FALSE ) //userLogin is in alphanumeric without special character
					{
						$bValidData = FALSE;
						$this->error .= "user login must be alphanumeric.\n";
					}
	
					if ( $key == 'userPass' && $cValidator->isAlphanumeric($value) == FALSE ) //userPass is in alphanumeric without special character
					{
						$bValidData = FALSE;
						$this->error .= "user password must be alphanumeric.\n";
					}
				}
	
				if ( $bValidData == TRUE )
				{
					$this->error = ''; //reset error variable if no error is encountered
				}
	
				return $bValidData;
			}
	*/
			//+++ END Data Validation +++++++++++++++++++++++++++++++++++//
			//+++ BEGIN User Action +++++++++++++++++++++++++++++++++++++//

			
	/*
			
	
			function delete($iUserID)
			{
				if ( is_numeric($iUserID) && $iUserID >= 0 ) //check $iUserID is numeric and positive value
				{
					$sQuery  = 'DELETE FROM user';
					$sQuery .= ' WHERE `ID` = "' . $iUserID . '"';
	
					$aResult = $this->dbAction($sQuery);
				}
				else
				{
					//log and report that a non numeric value has been inserted
					$this->logError('WARNING::Invalid numeric value::' . $iUserID);
				}
			}
	*/
			//+++ END User Action +++++++++++++++++++++++++++++++++++++++//
			//+++ BEGIN User Quesies ++++++++++++++++++++++++++++++++++++//
	
			
	/*
			
	
			function getUserByLogin($iUserLogin)
			{
				$aUser = array(
					'userLogin' => $iUserLogin
				);
	
				if ( $iUserLogin <> '' && $this->validateDataInput($aUser) )
				{
					$sQuery = 'SELECT * FROM user WHERE Login="'. $iUserLogin . '"';
	
					$aResult = $this->dbQuery($sQuery);
		   
					if ($aResult <> 0)
					{
						foreach( $aResult as $key => $value )
						{
							foreach( $value as $key2 => $value2 )
							{
								$value2 = stripslashes($value2);
							}
						}
					}
					else
					{
						$this->logError('FATAL::databaseError::' . $this->dbError);
					}
	
					return $aResult;
				}
			}
				
				function getUserList( $aSearchByFieldArray=array(), $aSortByArray=array(), $aLimitByArray=array() )
				{
					$sQuery  = 'SELECT user.ID AS ID, user.Name AS name, user.Login AS login';
					$sQuery .= ' FROM user LEFT JOIN account ON account.userID = user.ID';
	
					//search by field
					if ( count($aSearchByFieldArray) > 0 )
					{
						$i = 0;
						$sQuery .= ' WHERE';
						foreach ($aSearchByFieldArray as $key => $value )
						{
							$sQuery .= ' user.' . $key;
							$sQuery .= ' like "%' . $value . '%"';
								
							if ( $i >= 0 && $i < (count($aSearchByFieldArray) - 1) )
							{
								$sQuery .= ' AND';
							}
	
							$i++;
						}
						
					}
					
					$sQuery .= ' GROUP BY user.ID';
	
					//sort by
					if ( count($aSortByArray) > 0 )
					{
						$sQuery .= ' ORDER BY';
						$value = $aSortByArray[$aSortByArray['priority']];
						
						switch( strtolower($value) )
						{
							case 'ascending':
								$value = 'asc';
							break;
							case 'descending':
								$value = 'desc';
							break;
						}
	
						$sQuery .= ' ' . $aSortByArray['priority'] . ' ' . $value;
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
	*/
				//error & logging
/*
				
*/
			//+++ END User Quesies ++++++++++++++++++++++++++++++++++++//
?>
