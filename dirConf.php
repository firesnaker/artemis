<?php
	/********************************************************************
	* dirConf.php :: Directory Configuration File						*
	*********************************************************************
	* This file contains the absolute local path of the site	 		*
	*																	*
	* Version		: 0.0												*
	* Author		: FireSnakeR 										*
	* Created		: 2007-09-29 										*
	* Last modified	: 2007-11-09										*
	* 																	*
	* 				Copyright (c) 2007 FireSnakeR						*
	*********************************************************************/
	
	//+++ BEGIN path initialization +++++++++++++++++++++++++++++++++++//
	$sSystemPath = str_replace("\\", "/", __FILE__);
	$sAbsolutePath =  substr($sSystemPath, 0, strrpos($sSystemPath, "/"));
	//+++ END path initialization +++++++++++++++++++++++++++++++++++++//

	$rootPath = $sAbsolutePath . "/";
	$cssPath  = $rootPath . "css";
	$htmPath  = $rootPath . "htm";
	$imgPath  = $rootPath . "img";
	$libPath  = $rootPath . "lib";
	$jvsPath  = $rootPath . "jvs";
	$driverPath = $rootPath . "drivers";

?>