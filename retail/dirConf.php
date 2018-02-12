<?php
	/********************************************************************
	* dirConf.php :: Directory Configuration File						*
	*********************************************************************
	* This file contains the absolute local path of the site	 		*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2010-07-02 										*
	* Last modified	: 2010-07-02										*
	* 																	*
	* 				Copyright (c) 2010 FireSnakeR						*
	*********************************************************************/

	//+++ BEGIN path initialization +++++++++++++++++++++++++++++++++++//
	$sSystemPath = str_replace("\\", "/", __FILE__);
	$sAbsolutePath =  substr($sSystemPath, 0, strrpos($sSystemPath, "/retail"));
	//+++ END path initialization +++++++++++++++++++++++++++++++++++++//

	$rootPath = str_replace( "/retail", "", $sAbsolutePath ) . "/";

	include($rootPath . "dirConf.php");

?>