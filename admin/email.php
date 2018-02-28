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
	* admin/email.php :: Admin Daily Auto Email Page						*
	****************************************************************************
	* The daily auto email page for cron								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2011-09-20 									*
	* Last modified	: 2014-08-01									*
	*															*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classPDF.php");
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classPaymentType.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classOutlet.php");
	include_once($libPath . "/classInventory.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classExpenses.php");
	include_once($libPath . "/classDeposit.php");
	include_once($libPath . "/classReport.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cPDF = new PDF;
	$cSales = new Sales;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cPaymentType = new PaymentType;
	$cProduct = new Product;
	$cOutlet = new Outlet;
	$cInventory = new Inventory;
	$cUser = new User;
	$cExpenses = new Expenses;
	$cDeposit = new Deposit;
	$cReport = new Report;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	list($iHour, $iMinutes) = explode(":", _EmailTime_ );
	$sNow = time();
	$sDailyEmail = mktime( $iHour, $iMinutes, "00", date("m"), date("d"), date("Y") );
	$sLogFileName = $sAbsolutePath . "/log/emailLog.txt";
	$sPDFDirectory = $sAbsolutePath . "/pdf";
	$sReportDate = date("Y-m-d");
	$sReportDateForPrint = date("d-M-Y");
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get nb of outlet and their data. Only active outlet list are wanted
		$aOutletList = $cOutlet->GetActiveOutletList();

		//check time and date, email can go out after 18.30 or any other time set in config.php
		if ($sNow > $sDailyEmail)
		{
			//echo "sending email . <br />";
			//check email has been sent before by reading a text file log;
			//the content of emailLog.txt is the timestamp of email sent. New timestamp is appended to end of file.
			if ( file_exists($sLogFileName) )
			{
				//read the last line of the log, it contains the latest timestamp
				$aFileContent = file($sLogFileName);
				$sLastEmailSent = $aFileContent[ count($aFileContent) - 1 ];
				$sLastEmailSentDate = date("d-m-Y", $sLastEmailSent);
			}
			else
			{
				//if file does not exists, create it;
				$fh = fopen($sLogFileName, "w"); //attempt to open file, if not exist, create it.
				fclose($fh); //this is here just to create file if it does not exists, much simpler than using if then else
			}
	
			//if timestamp on file log not the same as today, create and send email
			if ( $sLastEmailSentDate <> date("d-m-Y", $sNow) )
			{
				//first, we generate the structure of the files
				//it will be stored in a folder with dates
				// inside the date folder, will be outlet ID and name folders
				//inside the outlet ID and name folder will be the sales and stock .pdf files
				//so, we create the date folder first
				$sPDFDateDir = $sPDFDirectory . "/" . date("Y-m-d");
				if ( !is_dir($sPDFDateDir) )
				{
					mkdir($sPDFDateDir);
				}
	
				//first, we create all sales and stock for each outlet.
				for ($iOutlet = 0; $iOutlet < count($aOutletList); $iOutlet++)
				{
					$iOutletID = $aOutletList[$iOutlet]["ID"];
					$sOutletName = $aOutletList[$iOutlet]["name"];
					$sOutletFolder = $iOutletID . "-" . str_replace( " ", "-", trim($sOutletName) );
					$sOutletFileName = $sOutletFolder . "-" . date("d-M-Y");
	
					//we assume that date dir is already exists
					//so next we check for outletID-outletName folder name
					$sPDFOutletDir = $sPDFDateDir . "/" . $sOutletFolder;
					if ( !is_dir($sPDFOutletDir) )
					{
						mkdir($sPDFOutletDir);
					}
	
					//get email address for user attached to this outlet
					$aUserList = $cUser->GetUserList(array("outlet_ID" => $iOutletID), array(), array());
					$sOutletEmail = $aUserList[0]["Email"];
	
					echo "processing outlet " . $iOutletID . " " . $sOutletName . "<br />\r\n";
	
					$cPDF_sales = new PDF;
					$cPDF_stock = new PDF;
	
					$cPDF_sales->outletName = $aOutletList[$iOutlet]["name"];
					$cPDF_sales->outletAddress = strip_tags($aOutletList[$iOutlet]["address"]);
					$cPDF_sales->reportDate = $sReportDateForPrint;
	
					$cPDF_stock->outletName = $cPDF_sales->outletName;
					$cPDF_stock->outletAddress = $cPDF_sales->outletAddress;
					$cPDF_stock->reportDate = $cPDF_sales->reportDate;
	
					echo "creating pdf files <br />\r\n";
	
					echo "save today sales to pdf <br />\r\n";
					$aSearchByFieldArray = array(
						"outlet_ID" => $iOutletID,
						"Date" => "BETWEEN '" . $sReportDate . "' AND '" . $sReportDate . "'"
					);
					$aSalesList = $cSales->GetSalesReport($aSearchByFieldArray);
					
					$aExpensesSearchByFieldArray = array(
						"outlet_ID" => $iOutletID,
						"Date" => $sReportDate
					);		
					$aExpensesList = $cExpenses->GetExpensesList($aExpensesSearchByFieldArray);
	
					$aDepositSearchByFieldArray = array(
						"outlet_ID" => $iOutletID,
						"Date" => $sReportDate
					);		
					$aDepositList = $cDeposit->GetDepositList($aDepositSearchByFieldArray);
		
					//todaysaleslist
					$reportListBlock = array();
					$iGrandtotal = 0;
					$iCashtotal = 0;
					$iDebittotal = 0;
					$iTransfertotal = 0;
					for ($i = 0; $i < count($aSalesList); $i++)
					{
						$aEmployeeName = $cEmployee->GetEmployeeByID($aSalesList[$i]['employee_ID']);
						$sEmployeeName = $aEmployeeName[0]['Name'];
				
						$aClientName = $cClient->GetClientByID($aSalesList[$i]['client_ID']);
						$sClientName = $aClientName[0]['Name'];
	
						$aPaymentTypeName = $cPaymentType->GetPaymentTypeByID($aSalesList[$i]['paymentType_ID']);
						$sPaymentTypeName = $aPaymentTypeName[0]['Name'];
				
						$iTotal = $aSalesList[$i]['Price'] * $aSalesList[$i]['Quantity'] * ( (100 - $aSalesList[$i]['Discount']) / 100 );
						$iGrandtotal += $iTotal;
				
						//reset all iCash, iDebit and iTransfer so new loop will show as 0
						$iCash = 0;
						$iDebit = 0;
						$iTransfer = 0;
						switch(strtolower($sClientName))
						{
							case "cash":
								$iCash = $iTotal;
								$iCashtotal += $iCash;
							break;
							case "debit":
								$iDebit = $iTotal;
								$iDebittotal += $iDebit;
							break;
							case "transfer":
								$iTransfer = $iTotal;
								$iTransfertotal += $iTransfer;
							break;
							default:
							break;
						}
				
						$reportListBlock[] = array(
							"0" => $i + 1,
							"1" => $sEmployeeName,
							"2" => $sClientName,
							"3" => $aSalesList[$i]['Notes'],
							"4" => $cProduct->GetProductNameByID($aSalesList[$i]['product_ID']),
							"5" => number_format($aSalesList[$i]['Price'], _NbOfDigitBehindComma_ ),
							"6" => $aSalesList[$i]['Quantity'],
							"7" => number_format($iTotal, _NbOfDigitBehindComma_ ),
							"8" => number_format($iCash, _NbOfDigitBehindComma_ ),
							"9" => number_format($iDebit, _NbOfDigitBehindComma_ ),
							"10" => number_format($iTransfer, _NbOfDigitBehindComma_ )
						);
					}
		
					//add last line, containing grand total;
					$reportListBlock[] = array(
							"0" => "",
							"1" => "",
							"2" => "",
							"3" => "",
							"4" => "",
							"5" => "",
							"6" => "GRANDTOTAL",
							"7" => number_format($iGrandtotal, _NbOfDigitBehindComma_ ),
							"8" => number_format($iCashtotal, _NbOfDigitBehindComma_ ),
							"9" => number_format($iDebittotal, _NbOfDigitBehindComma_ ),
							"10" => number_format($iTransfertotal, _NbOfDigitBehindComma_ )
					);
	
					//expensesListBlock
					$expensesListBlock = array();
					$iGrandTotal = 0;
					for ($i = 0; $i < count($aExpensesList); $i++)
					{
						$iGrandTotal += $aExpensesList[$i]['Price'];
						$expensesListBlock[] = array(
							"0" => $i+1,
							"1" => $aExpensesList[$i]['Name'],
							"2" => number_format($aExpensesList[$i]['Price'], _NbOfDigitBehindComma_ )
						);
					}
	
					//add last line, containing grand total;
					$expensesListBlock[] = array(
						"0" => "",
						"1" => "GRANDTOTAL",
						"2" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
					);
	
					//depositListBlock
					$depositListBlock = array();
					$iGrandTotal = 0;
					for ($i = 0; $i < count($aDepositList); $i++)
					{
						$iGrandTotal += $aDepositList[$i]['Price'];
						$depositListBlock[] = array(
							"0" => $i+1,
							"1" => $aDepositList[$i]['Notes'],
							"2" => number_format($aDepositList[$i]['Price'], _NbOfDigitBehindComma_ )
						);
					}
	
					//add last line, containing grand total;
					$depositListBlock[] = array(
						"0" => "",
						"1" => "GRANDTOTAL",
						"2" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
					);
	
					// Column headings
					$header = array('No', 'Sales', 'Client', 'Notes', 'Barang', 'Harga', 'Jumlah', 'Total', 'Cash', 'Debit', 'Transfer');
					$headerExpenses = array('No', 'Item', 'Total');
					$headerDeposit = array('No', 'Notes','Total');
		
					$cPDF_sales->SetFont('Arial','',14);
					$cPDF_sales->AliasNbPages();
					$cPDF_sales->AddPage();
					$cPDF_sales->ImprovedTableDailySales($header,$reportListBlock);
					$cPDF_sales->ImprovedTableDailyExpenses($headerExpenses,$expensesListBlock);
					$cPDF_sales->ImprovedTableDailyDeposit($headerDeposit,$depositListBlock);
					$cPDF_sales->Output($sPDFOutletDir . "/report-sales-outlet-" . $sOutletFileName . ".pdf", "F");
	
					echo "save today stock to pdf <br />\r\n";
					$aInventoryList = $cInventory->CalculateInventoryByOutletID($iOutletID, date("Y-m-d"));
	
					//inventoryListBlock
					$inventoryListBlock = array();
					for ($i = 0; $i < count($aInventoryList); $i++)
					{
						$inventoryListBlock[] = array(
							"0" => $i + 1,
							"1" => $aInventoryList[$i]['ProductName'],
							"2" => number_format( $aInventoryList[$i]['Quantity'], _NbOfDigitBehindComma_ ) .  (($aInventoryList[$i]['TransferInNotVerified'] > 0)?"+(".number_format( $aInventoryList[$i]['TransferInNotVerified'], _NbOfDigitBehindComma_ ).")":"")
						);
					}
		
					// Column headings
					$header = array('No', 'Barang', 'Jumlah');
		
					$cPDF_stock->SetFont('Arial','',14);
					$cPDF_stock->AliasNbPages();
					$cPDF_stock->AddPage();
					$cPDF_stock->ImprovedTableDailyStock($header,$inventoryListBlock);
					$cPDF_stock->Output($sPDFOutletDir . "/report-stock-outlet-" . $sOutletFileName . ".pdf", "F");
				}
	
				//first, we retrive the daily summary report
				$aReport = $cReport->GetDailySummary();
	
				//now, we generate the daily summary PDF file
				$aReportForPDF = array();
				for ($i = 0; $i < count($aReport); $i++)
				{
					$aReportForPDF[] = array(
						"0" => $i+1,
						"1" => $aReport[$i]["Date"],
						"2" => $aReport[$i]["OutletName"],
						"3" => $aReport[$i]["SalesTotal"],
						"4" => $aReport[$i]["ExpensesTotal"],
						"5" => $aReport[$i]["DepositTotal"]
					);
				}
	
				// Column headings
				$cPDF_dailySummary = new PDF;
				
				$cPDF_dailySummary->outletName = "Laporan Summary Harian";
				$cPDF_dailySummary->outletAddress = "Semua Outlet";
				$cPDF_dailySummary->reportDate = $sReportDateForPrint;
				$header = array('No', 'Tanggal', 'Outlet', 'Penjualan', 'Biaya', 'Deposit');
	
				$cPDF_dailySummary->SetFont('Arial','',14);
				$cPDF_dailySummary->AliasNbPages();
				$cPDF_dailySummary->AddPage();
				$cPDF_dailySummary->ImprovedTableDailySummary($header,$aReportForPDF);
				$cPDF_dailySummary->Output($sPDFDateDir . "/report-daily-summary-" . date("d-M-Y") . ".pdf", "F");
				//the daily summary is stored inside the date folder so it will be zipped.
				//we will also email it separately from the zip, thus we need to copy it to the folder directory.
				copy($sPDFDateDir . "/report-daily-summary-" . date("d-M-Y") . ".pdf", $sPDFDirectory . "/report-daily-summary-" . date("d-M-Y") . ".pdf");
	
				//now we zip all the files into one big file
				//save cwd (current working directory)
				$sCurrentDir = getcwd();
	
				//change cwd to pdf date directory for zip operation
				chdir($sPDFDateDir);
	
				//zip all the pdf files
				$sZipFileName = $sPDFDirectory . "/report-daily-" . date("d-M-Y") . ".zip";
				exec( "zip -R " . $sZipFileName . ' "*.pdf"' );
	
				//change back to previous cwd
				chdir($sCurrentDir);
	
				//next we remove all files in directory date
				exec( "rm -rf  " . $sPDFDateDir);
	
				//now we process the email send delivery mechanism 
				echo "Send email <br />\r\n";
				$from = _EmailReportFrom_;
				$to = _EmailReportTo_;
	
	/*				if ($sOutletEmail <> FALSE)
					{
						$to .= ";" . $sOutletEmail;
					}
	*/
				$subject = "Laporan Harian " . date("d-M-Y");
				$message = "Terlampir.";
	
				// a random hash will be necessary to send mixed content
				$separator = md5(time());
					 
				// carriage return type (we use a PHP end of line constant)
				$eol = PHP_EOL;
					 
				// attachment name
				$filename_outletZip = $sZipFileName;
				$filename_summaryPdf = $sPDFDirectory . "/report-daily-summary-" . date("d-M-Y") . ".pdf";
	
				// encode data (puts attachment in proper format)
				$fp_outletZip = fopen($filename_outletZip, 'rb');
				$attachment_outletZip = fread($fp_outletZip, filesize($filename_outletZip));
				$attachment_outletZip = chunk_split(base64_encode($attachment_outletZip));
				fclose($fp_outletZip);
				$fp_summaryPdf = fopen($filename_summaryPdf, 'rb');
				$attachment_summaryPdf = fread($fp_summaryPdf, filesize($filename_summaryPdf));
				$attachment_summaryPdf = chunk_split(base64_encode($attachment_summaryPdf));
				fclose($fp_summaryPdf);
	
				//overwrite filename for attachment sending.
				$filename_outletZip = "report-daily-" . date("d-M-Y") . ".zip";
				$filename_summaryPdf = "report-daily-summary-" . date("d-M-Y") . ".pdf";
	
				// main header (multipart mandatory)
				$headers = "From: ".$from.$eol;
				$headers .= "MIME-Version: 1.0".$eol;
				$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"".$eol.$eol;
				$headers .= "Content-Transfer-Encoding: 7bit".$eol;
				$headers .= "This is a MIME encoded message.".$eol.$eol;
	
				// message
				$headers .= "--".$separator.$eol;
				$headers .= "Content-Type: text/plain; charset=\"iso-8859-1\"".$eol;
				$headers .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
				$headers .= $message.$eol.$eol;
	
				// attachment_zip
				$headers .= "--".$separator.$eol;
				$headers .= "Content-Type: application/pdf; name=\"".$filename_outletZip."\"".$eol;
				$headers .= "Content-Transfer-Encoding: base64".$eol;
				$headers .= "Content-Disposition: attachment".$eol.$eol;
				$headers .= $attachment_outletZip.$eol.$eol;
	
				// attachment_summary
				$headers .= "--".$separator.$eol;
				$headers .= "Content-Type: application/pdf; name=\"".$filename_summaryPdf."\"".$eol;
				$headers .= "Content-Transfer-Encoding: base64".$eol;
				$headers .= "Content-Disposition: attachment".$eol.$eol;
				$headers .= $attachment_summaryPdf.$eol.$eol;
				$headers .= "--".$separator."--";
	
				mail($to, $subject, $message, $headers, $additional_parameters = null);
				echo "Email daily sent to " . $to . "<br />\r\n";
	
				echo "============================================= <br />\r\n";
	
				echo "put timestamp when email has been sent <br />\r\n";
				$fh = fopen($sLogFileName, "a");
				fwrite( $fh, time() . "\r\n" );
				fclose($fh);
			}
			else
			{
				echo "email already sent today";
			}
		}
		else
		{
			echo "not yet time";
		}
	
		//remove old files than one week
		if (is_dir($sPDFDirectory) )
		{
			$aFiles = scandir($sPDFDirectory);
	
			foreach ($aFiles as $key => $sFileName)
			{
				$sFullFileName = $sPDFDirectory . "/" . $sFileName;
				$iNow = time();
				$iOneWeek = 60*60*24*7;
	
				if (
					is_file($sFullFileName)
					&&  ( filemtime($sFullFileName) < ($iNow - $iOneWeek) )
				)
				{
					unlink($sFullFileName);
				}
			}
			
		}
	//*** END PAGE PROCESSING ***************************************************//

	//*** BEGIN PAGE RENDERING **************************************************//
	//*** END PAGE RENDERING ****************************************************//

?>
