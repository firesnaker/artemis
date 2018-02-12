<?php
	/********************************************************************
	* dirConf.php :: Directory Configuration File						*
	*********************************************************************
	* This file contains the absolute local path of the site	 		*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2013-09-02 										*
	* Last modified	: 2013-09-02										*
	* 																	*
	* 				Copyright (c) 2013 FireSnakeR						*
	*********************************************************************/

	//+++ BEGIN path initialization +++++++++++++++++++++++++++++++++++//
	$sSystemPath = str_replace("\\", "/", __FILE__);
	$sAbsolutePath =  substr($sSystemPath, 0, strrpos($sSystemPath, "/mkios"));
	//+++ END path initialization +++++++++++++++++++++++++++++++++++++//

	$rootPath = str_replace( "/mkios", "", $sAbsolutePath ) . "/";

	include($rootPath . "dirConf.php");

?>