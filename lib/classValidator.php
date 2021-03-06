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
	* lib/classValidator.php :: VALIDATOR CLASS							*
	*********************************************************************
	* Will check for valid entry from user input						*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2007-09-30 										*
	* Last modified	: 2007-11-20										*
	* 																	*
	*********************************************************************/
	   
	if ( !class_exists("Validator") )
	{
		class Validator
		{
			//*** BEGIN FUNCTION LIST ***********************************//
			// isAlphanumeric()
			// isValidType()
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			function isAlphanumeric($sString)
			{
				//valid characters are any word character
				return preg_match( '/^[\w]+$/i' , $sString );
			}

			function isAlphanumericOrEmpty($sString)
			{
				//valid characters are any word character
				return preg_match( '/^[\w\s]*$/i' , $sString );
			}
			
			function isWord($formValue)
			{
				//valid characters are any word character, white space and single quotes
				return preg_match( '/[\w\s\\<\>\']+/i' , $formValue );
			}

			function isNumbers($formValue)
			{
				return preg_match( '/^\d+$/i' , $formValue );
			}
			
			function isNumbersOrEmpty($formValue)
			{
				return preg_match( '/^\d*$/i' , $formValue );
			}
			
			function isEmail($sEmail)
			{
				//preg match will return 0 (no match) or 1 (1 match)
				return preg_match( '/^(\w+@\w+\.\w+){1}(\.\w+)?$/i' , $sEmail );
			}
			
			function isEmailOrEmpty($sEmail)
			{
				if ($Email == "")
					return TRUE;
				else
				{
					//preg match will return 0 (no match) or 1 (1 match)
					return preg_match( '/^(\w+@\w+\.\w+){1}(\.\w+)?$/i' , $sEmail );
				}
			}
				
			//$aPost[key] must equal $aLogin[key]
			function isValidType($aData, $aDataType)
			{
				$bValidValue = TRUE;
	
				foreach($aData as $sDataKey => $sDataValue)
				{
					foreach($aDataType as $sDataTypeKey => $sDataTypeValue)
					{
						if ($sDataKey === $sDataTypeKey)
						{
							switch($sDataTypeValue)
							{
								case "alphanumeric":
									if ( $this->isAlphanumeric($sDataValue) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								case "alphanumericOrEmpty":
									if ( $this->isAlphanumericOrEmpty($sDataValue) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								case "numeric":
									if ( $this->isNumbers($sDataValue) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								case "numericOrEmpty":
									if ( $this->isNumbersOrEmpty($sDataValue) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								case "file":
									if ( $this->isAlphanumericOrEmpty($sDataValue['name']) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								case "word":
									if ( $this->isWord($sDataValue) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								case "email":
									if ( $this->isEmail($sDataValue) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								case "emailOrEmpty":
									if ( $this->isEmailOrEmpty($sDataValue) == FALSE )
									{
										$bValidValue = FALSE;
									}
								break;
								default:
								break;
							}
						}
					}
				}
				
				return $bValidValue;
			}

		}
		//*** END FUNCTION **********************************************//
	}

/*
			function isAlphabet($formValue)
			{
				$result = TRUE;

				if ( $this->isWord($formValue) == 0)
				{
					if (preg_match( '/[@|\d]/i' , $formValue ) )
						$result = FALSE;
				}
				return $result;
			}

		*/
?>
