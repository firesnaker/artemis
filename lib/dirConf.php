<?php
	/********************************************************************
	* dirConf.php :: Directory Configuration File						*
	*********************************************************************
	* This file contains the absolute local path of the site	 		*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2007-09-29 										*
	* Last modified	: 2007-11-20										*
	* 																	*
	* 				Copyright (c) 2007 FireSnakeR						*
	*********************************************************************/

	//+++ BEGIN path initialization +++++++++++++++++++++++++++++++++++//
	$sSystemPath = str_replace("\\", "/", __FILE__);
	$sAbsolutePath =  substr($sSystemPath, 0, strrpos($sSystemPath, "/lib"));
	//+++ END path initialization +++++++++++++++++++++++++++++++++++++//

	$rootPath = str_replace( "/lib", "", $sAbsolutePath ) . "/";

	include($rootPath . "dirConf.php");

?>