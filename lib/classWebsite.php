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
	* lib/classWebsite.php :: WEBSITE CLASS								*
	*********************************************************************
	* This class holds all website presentation functions				*
	* ASSUMPTION:	All local path are stored in "dirconf.php"			*
	*			All template files have the extension of ".htm"			*
	*			site and navigation template file are named site.htm 	*
	*			and navigation.htm										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2006-07-16 										*
	* Last modified	: 2007-11-15										*
	* 																	*
	*********************************************************************/
	   
	if ( !class_exists("Website") )
	{
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($libPath."/classTemplate.php");
		
		class Website extends Template
		{
			var $template = "";

			function init($pageFiles, $templatePath)
			{
				//define the HTML file
				$this->template = new template($templatePath);

				$templateFiles = array();
				foreach( $pageFiles as $key => $value )
				{
					$templateFiles[$key] = $value;
				}
				$this->template->set_file($templateFiles);
			}
			
			function buildNavigation($navigation)
			{
				$this->template->parse($navigation, "navigation");
			}
			
			function buildContent($content)
			{
				$this->template->parse($content, "content");
			}
			
			function display()
			{
				//build the content of the HTML file
				$this->template->pparse("OUT", "site");
			}

			function buildBlock($fileRef, $blockName, $blockContent)
			{
				$blockInternalName = $blockName . "s";
				$this->template->set_block($fileRef, $blockName, $blockInternalName);
				$this->template->parse($blockInternalName, "");

				if ( count($blockContent) <= 0 )
				{
					$this->template->parse($blockName, "");
				}
				else
				{
					for ($i = 0; $i < count($blockContent); $i++)
					{
						foreach ($blockContent[$i] as $key => $value)
						{
							$this->template->set_var( $key, $value );
						}
						$this->template->parse($blockInternalName, $blockName, TRUE);
					}
				}
			}
		}
	}
?>
