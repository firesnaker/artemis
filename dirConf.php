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
	* dirConf.php :: Directory Configuration File						*
	*********************************************************************
	* This file contains the absolute local path of the site	 		*
	*																	*
	* Version		: 0.0												*
	* Author		: FireSnakeR 										*
	* Created		: 2007-09-29 										*
	* Last modified	: 2007-11-09										*
	* 																	*
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
