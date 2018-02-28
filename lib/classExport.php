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
	* lib/classExport.php :: Export CLASS						*
	****************************************************************************
	* This library creates excel / csv file for exporting data				*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan / FireSnakeR					*
	* Created			: 2014-05-23 									*
	* Last modified	: 2014-05-23									*
	*															*
	***************************************************************************/

	if ( !class_exists('Export') )
	{
		//+++ BEGIN library inclusion +++++++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		//+++ END library inclusion +++++++++++++++++++++++++++++++++++++++++//
	
		class Export
		{
			private $sFileName;

			public $sSeparator;
			public $sQuoteCharacter;

			//*** BEGIN FUNCTION LIST **************************************//
			// exportToCSV($sFileName, $aContent)
			// downloadFile($file, $name, $mime_type='')
			//*** END FUNCTION LIST ****************************************//

			public function __construct()
			{
				$this->sSeparator = ";";
				$this->sQuoteCharacter = "\"";
			}

			//*** BEGIN FUNCTION *******************************************//
			function exportToCSV($aContent)
			{
				include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally

				$sRandom = time() * rand(0, 99);
				$this->sFileName = $sAbsolutePath . "/pdf/file-" . $sRandom . ".tmp";
				//save to file
				$fp = fopen($this->sFileName, "w");
				foreach ($aContent as $aData)
				{
					$sLineContent = "";
					foreach ($aData as $sData)
					{
						$sLineContent .= $this->sQuoteCharacter . $sData . $this->sQuoteCharacter . $this->sSeparator;
					}
					fwrite($fp, $sLineContent . "\r\n");
				}

				fclose($fp);
			}

			function output_file($name, $mime_type='')
			{
				$file = $this->sFileName;
				/*
				This function takes a path to a file to output ($file),
				the filename that the browser will see ($name) and
				the MIME type of the file ($mime_type, optional).
	
				If you want to do something on download abort/finish,
				register_shutdown_function('function_name');
				*/

				if(!is_readable($file)) die('File not found or inaccessible!');

				$size = filesize($file);
				$name = rawurldecode($name);

				/* Figure out the MIME type (if not specified) */
				$known_mime_types=array(
					"pdf" => "application/pdf",
					"txt" => "text/plain",
					"html" => "text/html",
					"htm" => "text/html",
					"exe" => "application/octet-stream",
					"zip" => "application/zip",
					"doc" => "application/msword",
					"xls" => "application/vnd.ms-excel",
					"ppt" => "application/vnd.ms-powerpoint",
					"gif" => "image/gif",
					"png" => "image/png",
					"jpeg"=> "image/jpg",
					"jpg" =>  "image/jpg",
					"php" => "text/plain"
				);

				if($mime_type==''){
						$file_extension = strtolower(substr(strrchr($file,"."),1));
						if(array_key_exists($file_extension, $known_mime_types)){
							$mime_type=$known_mime_types[$file_extension];
					} else {
						$mime_type="application/force-download";
					};
				};

				@ob_end_clean(); //turn off output buffering to decrease cpu usage

				// required for IE, otherwise Content-Disposition may be ignored
				if(ini_get('zlib.output_compression'))
					ini_set('zlib.output_compression', 'Off');

				header('Content-Type: ' . $mime_type);
				header('Content-Disposition: attachment; filename="'.$name.'"');
				header("Content-Transfer-Encoding: binary");
				header('Accept-Ranges: bytes');

				/* The three lines below basically make the
				download non-cacheable */
				header("Cache-control: private");
				header('Pragma: private');
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			 
				// multipart-download and download resuming support
				if(isset($_SERVER['HTTP_RANGE']))
				{
					list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
					list($range) = explode(",",$range,2);
					list($range, $range_end) = explode("-", $range);
					$range=intval($range);
					if(!$range_end) {
						$range_end=$size-1;
					} else {
						$range_end=intval($range_end);
					}

					$new_length = $range_end-$range+1;
					header("HTTP/1.1 206 Partial Content");
					header("Content-Length: $new_length");
					header("Content-Range: bytes $range-$range_end/$size");
				} else {
					$new_length=$size;
					header("Content-Length: ".$size);
				}

				/* output the file itself */
				$chunksize = 1*(1024*1024); //you may want to change this
				$bytes_send = 0;
				if ($file = fopen($file, 'r'))
				{
					if(isset($_SERVER['HTTP_RANGE']))
						fseek($file, $range);

					while(!feof($file) &&
						(!connection_aborted()) &&
						($bytes_send<$new_length)
					)
					{
						$buffer = fread($file, $chunksize);
						print($buffer); //echo($buffer); // is also possible
						flush();
						$bytes_send += strlen($buffer);
					}
					fclose($file);
				} else die('Error - can not open file.');

				die();
			}
			//*** END FUNCTION *********************************************//
		}
	}
?>
