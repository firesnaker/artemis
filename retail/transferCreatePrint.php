<?php
	/***************************************************************************
	* retail/transferCreatePrint.php :: Retail Transfer Create Print Page		*
	****************************************************************************
	* The transfer create print page for retail							*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2012-02-27 									*
	* Last modified	: 2014-08-21									*
	* 															*
	* 			Copyright (c) 2010-2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_user('user_ID') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classTransfer.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cTransfer = new Transfer;
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iTransferID = 0;
	$sPDFDirectory = $sAbsolutePath . "/pdf";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			if ( isset($_GET["transferOutID"]) && $_GET["transferOutID"] > 0 )
			{
				$iTransferID = $_GET["transferOutID"];
			}
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		$sFileName = $sPDFDirectory . "/transfer-print-" . date("d-m-Y") . "-" . $iTransferID . ".pdf";

		if ( !file_exists($sFileName) )
		{
			die("file not found");
		}
		else
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename='.basename($sFileName));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Cache-Control: private',false); // required for certain browsers
			header('Content-Length: ' . filesize($sFileName));
			ob_clean();
			flush();
			readfile($sFileName);
			//the echo code below is a workaround. I have not been able to identify why instead of showing an
			//open / download dialog box, it simply outputs the content of pdf to browser...
			echo "\r\n<br /><a href=\"" . _siteBaseURI_ . "pdf/" . basename($sFileName) . "\">Manual download</a>";
			exit();
		}

	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//
	
	//*** END PAGE RENDERING ****************************************************//

?>