<?php
	/************************************************************************
	* lib/classDatabase.php :: DATABASE CLASS											*
	*************************************************************************
	* A database interface between application and official PHP	database 	*
	* extensions																				*
	*																								*
	* Data input are assumed to be valid and clean. No checking are done		* 
	* here.																						*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2004-09-?? 														*
	* Last modified	: 2015-03-13														*
	* 																								*
	* 					Copyright (c) 2004-2015 FireSnakeR								*
	************************************************************************/

	if ( !class_exists("FSR_Database") )
	{
		class FSR_Database
		{
			private $dbLink;
			private $dbError;
			private $dbType;
			private $dbHost;
			private $dbUser;
			private $dbPass;
			private $dbName;

			public function __construct($sType='mysql', $sHost='localhost', $sUser='', $sPass='', $sDBName='')
			{
				$aData = array(
					"dbType" => $sType,
					"dbHost" => $sHost,
					"dbUser" => $sUser,
					"dbPass" => $sPass,
					"dbName" => $sDBName
				);
				$this->dbSetup($aData); 

				return TRUE;
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

			public function dbSave($aData, $sTableName)
			{
				$result = FALSE;

				//prepare the data
				$aFields = array();
				$aValues = array();
				$aFieldsValuesPair = array();
				foreach ($aData as $sField => $sValue)
				{
					//split the data into fields and values pair
					$aFields[] = "`" . $sField . "`";
					$aValues[] = "'" . $sValue . "'";
					if ($sField != "ID" && $sField != "id")
					{
						$aFieldsValuesPair[] = "`" . $sField . "` = '" . $sValue . "'";
					}
				}

				if ((!isset($aData['ID']) || $aData['ID'] == "")
					&& (!isset($aData['id']) || $aData['id'] == ""))
				{
					$sQuery  = "INSERT INTO " . $sTableName;
					$sQuery .= " (". implode(",", $aFields) .")";
					$sQuery .= " VALUES";
					$sQuery .= " (". implode(",", $aValues) .")";
				}
				else
				{
					$sQuery  = "UPDATE " . $sTableName;
					$sQuery .= " SET";
					$sQuery .= " " . implode(",", $aFieldsValuesPair);
					$sQuery .= (($sTableName == "supplier" || $sTableName == "verification")?" WHERE id = '" . $aData['id'] . "'":" WHERE ID = '" . $aData['ID'] . "'");
				}

				$result = $this->dbAction($sQuery);

				return $result;
			}

			public function dbDelete($iID, $sTableName)
			{
				$result = FALSE;

				$sQuery  = "DELETE FROM " . $sTableName;
				$sQuery .= (($sTableName == "supplier")?" WHERE id = '":" WHERE ID = '") . $iID . "'";

				$result = $this->dbAction($sQuery);

				return $result;
			}

			public function dbLoad($iID, $sTableName)
			{
				$result = FALSE;

				$aData = array(
					"fields" => "*",
					"tables" => $sTableName,
					"query" => (($sTableName == "supplier")?" id = '":" ID = '") . $iID . "'"
				);

				$result = $this->dbSearch($aData);

				if ( count($result) > 0 )
				{
					$result = $result[0];
				}

				return $result;
			}

			public function dbSearch($aData)
			{
				$result = FALSE;

				$sQuery  = " SELECT " . $aData['fields'];
				$sQuery .= " FROM " . $aData['tables'];
				$sQuery .= " WHERE 1";
				if ( isset($aData['query']) )
				{
					$sQuery .= " AND " . $aData['query'];
				}
				if ( isset($aData['groupBy']) )
				{
					$sQuery .= " GROUP BY " . $aData['groupBy'];
				}
				if ( isset($aData['orderBy']) )
				{
					$sQuery .= " ORDER BY " . $aData['orderBy'];
				}
				if ( isset($aData['limit']) )
				{
					$sQuery .= " LIMIT " . $aData['limit'];
				}

				$result = $this->dbQuery($sQuery);

				return $result;
			}

			public function dbTableFields($sTableName)
			{
				$result = FALSE;

				$sQuery  = " DESCRIBE " . $sTableName;
				$resultFields = $this->dbQuery($sQuery);

				if ( is_array($resultFields) && count($resultFields) > 0)
				{
					$result = array();
					foreach ($resultFields as $result_rows)
					{
						$result[] = $result_rows['Field'];
					}
				}

				return $result;
			}

			private function dbSetup($aData)
			{
				$this->dbType = $aData['dbType'];
				$this->dbHost = $aData['dbHost'];
				$this->dbUser = $aData['dbUser'];
				$this->dbPass = $aData['dbPass'];
				$this->dbName = $aData['dbName'];

				return TRUE;
			}

			private function dbConnect()
			{
				$result = FALSE;

				switch ($this->dbType)
				{
					case 'mysql':
						try
						{
							$this->dbLink = new PDO('mysql:host='. $this->dbHost .';dbname='. $this->dbName, $this->dbUser, $this->dbPass, array(
PDO::ATTR_PERSISTENT => true
));
						}
						catch (Exception $e)
						{
							$this->dbError = $e->getMessage();
						}
					break;
					case 'sqlsrv':
						try
						{
							$this->dbLink = new PDO('sqlsrv:Server='. $this->dbHost .';Database='. $this->dbName, $this->dbUser, $this->dbPass);
						}
						catch (Exception $e)
						{
							$this->dbError = $e->getMessage();
						}
					break;
					case 'mssql':
						try
						{
							$this->dbLink = mssql_connect($this->dbHost, $this->dbUser, $this->dbPass);
							mssql_select_db($this->dbName, $this->dbLink);
						}
						catch (Exception $e)
						{
							$this->dbError = $e->getMessage();
						}
					break;
					default:
						$this->dbError = "Undefined DB Type";
					break;
				}

				if ($this->dbLink)
				{
					$result = TRUE;
				}

				return $result;
			}

			private function dbQuery($sQuery)
			{
				$result = FALSE;

				if ( !$this->dbLink )
				{
					$this->dbConnect();
				}

				if ( $this->dbLink )
				{
					switch ($this->dbType)
					{
						case 'mssql':
							$rSQL = mssql_query($sQuery);
	
							$result = array();
							while ($row = mssql_fetch_array($rSQL))
							{
						        $result[] = $row;
							}
						break;
						default: //PDO based query
							$stmt = $this->dbLink->prepare($sQuery);
							$stmt->execute();
							$result = $stmt->fetchAll();
						break;
					}
				}

				return $result;
			}

			private function dbAction($sAction)
			{
				$result = FALSE;

				if ( !$this->dbLink )
				{
					$this->dbConnect();
				}

				if ( $this->dbLink )
				{
					switch ($this->dbType)
					{
						case 'mssql':
							
						break;
						default: //PDO based query
							$stmt = $this->dbLink->prepare($sAction);
							$result = $stmt->execute(); //return TRUE or FALSE

							if (substr_count($sAction, "INSERT"))
							{
								$result = $this->dbLink->lastInsertId();
							}
						break;
					}
				}

				return $result;
			}
		}
	}

	//the old system, redundant, will be removed once migration are complete
	if ( !class_exists('Database') )
	{
		class Database
		{
			var $dbLink; //this variable hold the resource id of valid connections
			var $dbType; //this variable hold the database type. valid type are 'MySQL'
			var $dbError = FALSE; //this variable hold the error code
	
			function DB($sDBType='MYSQL')
			{
				$this->dbType = strtoupper($sDBType);
			}

			function dbOpen($sHost, $sUser, $sPassword, $sDBName)
			{
				if ( $this->dbType = 'MYSQL' )
				{
					try
					{
						$this->dbLink = new PDO('mysql:host='.$sHost.';dbname='.$sDBName.'', $sUser, $sPassword, array(
PDO::ATTR_PERSISTENT => true
));
					} catch (PDOException $e) {
						$this->dbError = $e->getMessage();
					}
				}
			}
	
			function dbQuery($sQuery)
			{
				if ( $this->dbType = 'MYSQL' )
				{
					//$result = $this->mySQLQuery($sQuery);
					$oStatement = $this->dbLink->prepare( $sQuery );

					$oStatement->execute();

					$result = $oStatement->fetchAll();
				}

				return $result;
			}

			function dbAction($sQuery)
			{
				if ( $this->dbType = 'MYSQL' )
				{
					$result = $this->dbLink->exec( $sQuery );
				}

				return $result;
			}
		}
	}
?>
