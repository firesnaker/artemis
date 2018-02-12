<?php

	/***************************************************************************
	* lib/gateObject.php :: GATE CLASS									*
	****************************************************************************
	* The GATE Object												*
	* Makes sure only valid user and roles can access restricted pages		*
	* Utilizes the standard PHP $_SESSION variables						*
	* It read PHP $_SESSION variables and process them					*
	*															*
	* Version			: 2											*
	* Author			: Ricky Kurniawan [ FireSnakeR ] 					*
	* Created			: 2014-07-31 									*
	* Last modified	: 2014-08-01									* 		*															*
	* 				Copyright (c) 2014 FireSnakeR						*
	***************************************************************************/

	if ( !class_exists('gate') )
	{
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");

		class gate
		{
			private $door = FALSE;

			public function __construct($param)
			{
				$this->door = $param;
			} // end of __construct()

			public function is_valid_user($user_key)
			{
				if (isset($this->door[$user_key]) && $this->door[$user_key] > 0)
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}//end of is_valid()

			//make sure the $role_value is lowercase
			public function is_valid_role($user_key, $role_key, $role_value)
			{
				$role = FALSE;
				if ( $this->is_valid_user($user_key) )
				{
					if ( isset($this->door[$role_key]) && strtolower($this->door[$role_key]) == strtolower($role_value) )
					{
						$role = TRUE;
					}
				}
				return $role;
			}

		} //end of class
	} //end of class_exists()

?>