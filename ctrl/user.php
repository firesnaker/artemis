<?php
	/************************************************************************
	* ctrl/user.php :: User Controller Page											*
	*************************************************************************
	* The user controller page																*
	*																								*
	* Version			: 1																	*
	* Author				: Ricky Kurniawan [ FireSnakeR ]								*
	* Created			: 2015-02-10 														*
	* Last modified	: 2015-02-10														*
	*																								*
	* 					Copyright (c) 2014-2015 FireSnakeR								*
	************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cUser = new FSR_User;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$result = "";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	if ( isset($_POST) && count($_POST) > 0)
	{
		if (isset($_POST['deleteID']))
		{
			$cUser->deleteUser($_POST['deleteID']);
		}
		else
		{
			if (isset($_POST['userID']))
			{
				$cUser->getUser($_POST['userID']);
			}
			$cUser->setProperty("Username", $_POST['userUsername']);
			$cUser->setProperty("Password", md5($_POST['userPassword']));
			if ( $cUser->setUser() )
			{
				$result = "Save Success";
			}
			else
			{
				$result = "Save Failed";
			}
		}
	}
	elseif ( isset($_GET) && count($_GET) > 0)
	{
		if (isset($_GET['id']) ) //only get one data
		{
			$cUser->getUser($_GET['id']);
			$result = array(
				"ID" => $cUser->getProperty("ID"),
				"Username" => $cUser->getProperty("Username"),
				"Password" => $cUser->getProperty("Password")
			);
		}
		else //get everything
		{
			$userData = $cUser->listUser();
			$result = array("data");
			$result['data'] = $userData;
		}
	}
	else
	{
		//ok, something is definitely wrong here
		$result = "unknown error";
	}
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//
	echo json_encode($result);
	//*** END PAGE RENDERING *************************************************//
?>
