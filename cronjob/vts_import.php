<?php
	/***************************************************************************
	* cronjob/vts_import.php :: MKios VTS Import Page						*
	****************************************************************************
	* The cronjob page for MKios VTS Import								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2014-11-29 									*
	* Last modified	: 2014-11-29									*
	*															*
	* 			Copyright (c) 2014 FireSnakeR							*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classMKios.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cMKios = new MKios;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$ftp_server = "ftp.server.local";
	$ftp_user_name = "ftpuser";
	$ftp_user_pass = "ftppass";
	$local_path = $rootPath . "pdf/vts";
	$archive_path = $local_path . "/archive";
	$iDateStart = strtotime("-1 week", strtotime(date("d-M-Y")));
	$iDateEnd = strtotime(date("d-M-Y"));
	/*$iDateStart = strtotime("19-Dec-2014");
	$iDateEnd = strtotime("20-Dec-2014");*/
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING **********************************************//
	//setup the environment
	if (!is_dir($rootPath . "pdf"))
	{
		mkdir ($rootPath . "pdf");
	}

	if (!is_dir($local_path))
	{
		mkdir ($local_path);
	}

	if (!is_dir($archive_path))
	{
		mkdir ($archive_path);
	}

	// set up basic connection
	$conn_id = ftp_connect($ftp_server); 
	
	// login with username and password
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
	
	// check connection
	if ((!$conn_id) || (!$login_result)) { 
		echo "FTP connection has failed!";
		echo "Attempted to connect to $ftp_server for user $ftp_user_name"; 
		exit; 
	}

	if (!ftp_chdir($conn_id, "www")) {
		echo "Couldn't change directory\n";
	}

	// get contents of the current directory
	$contents = ftp_nlist($conn_id, ".");
	foreach ($contents as $key => $server_file)
	{
		$local_file = $local_path . "/" . $server_file;
		if (!ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
			echo "There was a problem\n";
		}

		// try to delete $file
		if (!ftp_delete($conn_id, $server_file)) {
			echo "could not delete $file\n";
		}
	}

	// close the FTP stream 
	ftp_close($conn_id);

	//scan for zip files and unzip them plus move to archive folder
	$ftp_files = scandir($local_path);
	foreach ($ftp_files as $key => $file_zip)
	{
		if ( !is_dir($local_path . "/" . $file_zip) )
		{
			$local_file = $local_path . "/" . $file_zip;
			$local_archive = $archive_path . "/" . $file_zip;
	
			$filename = explode(".", $file_zip);
			if ($filename[count($filename)-1] == "zip")
			{
				exec("unzip " . $local_file . " -d " . $local_path);
			}
	
			//copy
			copy($local_file, $local_archive);
			//delete zip
			unlink($local_file);
		}
	}

	//do a second scan for csv files
	$csv_files = scandir($local_path);
	foreach ($csv_files as $key => $file_csv)
	{
		if ( !is_dir($local_path . "/" . $file_csv) )
		{
			$local_file = $local_path . "/" . $file_csv;
	
			$filename = explode(".", $file_csv);
			if ($filename[count($filename)-1] == "csv")
			{
				set_time_limit(0);
				//save to database
				if (($handle = fopen($local_path . "/" . $file_csv, "r")) !== FALSE)
				{
					$aIDs = array();
					while (($data = fgetcsv($handle, 0, ";")) !== FALSE)
					{
						if ( count($data) == 15
							&& strtotime(trim($data[6])) >= $iDateStart
							&& strtotime(trim($data[6])) <= $iDateEnd
						)
						{
					    		$aMKios = array(
					    			"ID" => trim($data[0]),
					    			"KodeWH" => trim($data[1]),
					    			"KodeSales" => trim($data[2]),
					    			"CustomerGroup" => trim($data[3]),
					    			"KodeCustomer" => trim($data[4]),
					    			"NamaCustomer" => trim($data[5]),
					    			"TxPeriod" => trim($data[6]),
					    			"NoHP" => trim($data[7]),
					    			"KodeBarang" => trim($data[8]),
					    			"Jumlah" => trim($data[9]),
					    			"Harga" => trim($data[10]),
					    			"KodeTerminal" => trim($data[11]),
					    			"DocNumber" => trim($data[12]),
					    			"Status" => trim($data[13]),
					    			"StatusTime" => trim($data[14])
					    		);
							$cMKios->InsertVTS($aMKios);
							array_push($aIDs, $aMKios['ID']);
						}
					}
					fclose($handle);
				}
			}

			//delete csv
			unlink($local_file);
		}
	}

	if ( isset($aIDs) )
	{
		//check for data to be deleted
		$cMKios->CheckVTSDelete($aIDs, date("Y-m-d", $iDateStart), date("Y-m-d", $iDateEnd));
	}

	$cMKios->ConvertVTSToMKios(date('Y-m-d', $iDateStart), date("Y-m-d", $iDateEnd));
	//*** END PAGE PROCESSING ************************************************//

	//*** BEGIN PAGE RENDERING ***********************************************//
	//*** END PAGE RENDERING *************************************************//
?>
