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
	* retail/sales_orderPrint.php :: Retail Sales Order Print Page			*
	****************************************************************************
	* The sales order print page for retail								*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2010-07-06									*
	* Last modified	: 2014-08-21									*
	* 															*
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
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classProduct.php");
	include_once($libPath . "/classSales.php");
	include_once($libPath . "/classInventory.php");
	include_once($libPath . "/classEmployee.php");
	include_once($libPath . "/classClient.php");
	include_once($libPath . "/classPaymentType.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cProduct = new Product;
	$cSales = new Sales;
	$cInventory = new Inventory;
	$cEmployee = new Employee;
	$cClient = new Client;
	$cPaymentType = new PaymentType;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$iCurrentRecordCounter = 0;
	$iCurrentSalesID = 0;
	$bGoToLastCounter = false;
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_GET) > 0 ) //$_POST is always set, so we check by # of element
		{
			$iCurrentSalesID = $_GET["salesID"];
		}
		//+++ END $_POST / $_GET processing +++++++++++++++++++++++++++++++++++++++++//

		//get product list visible on website
		$aSearchByFieldArray = array(
			"Viewable" => 1
		);
		$aSortByArray=array(
			'product.Name' => 'asc'
		);
		$aProductList = $cProduct->GetProductList($aSearchByFieldArray, $aSortByArray);

		$aSalesSearchByFieldArray = array(
			"outlet_ID" => $_SESSION['outlet_ID']
		);		
		$aSalesList = $cSales->GetSalesOrderList($aSalesSearchByFieldArray);

		switch ($aSalesList[0]['Status'])
		{
			case 0:
				$iCurrentSalesStatus = "<span style='color:red;'>Baru</span>";
			break;
			case 1:
				$iCurrentSalesStatus = "<span style='color:red;'>Verified</span>";
			break;
			default:
				//$iCurrentSalesStatus = "";
			break;
			
		}


		$aSalesData = $cSales->GetSalesOrderWithDetail($iCurrentSalesID);
		$iSalesDetailCount = count($aSalesData);
		if (count($aSalesData) < 1 )
		{
			$iSalesDetailCount = 0;
			$aSalesData = $cSales->GetSalesOrderByID($iCurrentSalesID);
		}
		
		$aEmployeeData = $cEmployee->GetEmployeeByID($aSalesData[0]["employee_ID"]);
		$aClientData = $cClient->GetClientByID($aSalesData[0]["client_ID"]);
		$aPaymentTypeData = $cPaymentType->GetPaymentTypeByID($aSalesData[0]["paymentType_ID"]);
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "retail/sales_orderPrint.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,

		//page text
		"VAR_PAGETITLE" => "Pesanan Penjualan",
		"VAR_MESSAGE_GREETING" => "Welcome "/* . $cUser->Name . "!"*/,
		"TEXT_STATUS" => "Status :",
		"VAR_SALESSTATUS" => $iCurrentSalesStatus,
		"VAR_SALESDATE" => ($iCurrentSalesID > 0)?date( "d-M-Y", strtotime( $aSalesData[0]["Date"] ) ):date("d-M-Y"),
		"VAR_SALESNOTES" => $aSalesData[0]["Notes"],
		"VAR_OUTLETNAME" => $_SESSION['outlet_Name'],
		"VAR_EMPLOYEENAME" => $aEmployeeData[0]["Name"],
		"VAR_CLIENTNAME" => $aClientData[0]["Name"],
		"VAR_PAYMENTTYPENAME" => $aPaymentTypeData[0]["Name"],
		"VAR_SALES" => $iCurrentRecordCounter,
		"VAR_SALESTOTAL" => $iTotalSales,
		"VAR_TMPROW" => count($aSalesData),
		"VAR_OUTLETID" => $_SESSION['outlet_ID'],
		"VAR_SALESID" => $iCurrentSalesID,
		"VAR_PREVRECORDCOUNTER" => ($iCurrentRecordCounter <= 1)?0:($iCurrentRecordCounter - 2),
		"VAR_NEXTRECORDCOUNTER" => ($iCurrentRecordCounter > $iTotalSales)? $iTotalSales:$iCurrentRecordCounter,
		"VAR_PRINTDATE" => date("d-m-Y H:i")
	));
	
	if ($iCurrentSalesID == 0 || $iSalesDetailCount == 0)
	{
		$cWebsite->template->set_block("content", "salesDetailRow");
		$cWebsite->template->parse("salesDetailRow", "");
	}
	else
	{
		$iGrandTotal = 0;
		//salesDetailRow
		$salesDetailRow = array();
		for ($i = 0; $i < count($aSalesData); $i++)
		{
			$iSubtotal = $aSalesData[$i]['Quantity'] * $aSalesData[$i]['Price'] * ( (100 - $aSalesData[$i]['Discount'] ) / 100);
			$salesDetailRow[] = array(
				"VAR_COUNTER" => $i+1,
				"VAR_SALESDETAILID" => $aSalesData[$i]['detail_ID'],
				"VAR_PRODUCTID" => $aSalesData[$i]['productID'],
				"VAR_PRODUCTNAME" => $aSalesData[$i]['productName'],
				"VAR_QUANTITY" => number_format($aSalesData[$i]['Quantity'], _NbOfDigitBehindComma_ ),
				"VAR_DISCOUNT" => number_format($aSalesData[$i]['Discount'], 2 ),
				"VAR_PRICE" => number_format($aSalesData[$i]['Price'], _NbOfDigitBehindComma_ ),
				"VAR_SUBTOTAL" => number_format( $iSubtotal, _NbOfDigitBehindComma_ ),
				"VAR_SN" => ($aSalesData[$i]['SnStart'] . (($aSalesData[$i]['SnEnd'] == "")?"":("-" . $aSalesData[$i]['SnEnd']) ) ),
			);

			$iGrandTotal += $iSubtotal;
		}
		$cWebsite->buildBlock("site", "salesDetailRow", $salesDetailRow);
	}

	$cWebsite->template->set_var(array(
		"VAR_GRANDTOTAL" => number_format($iGrandTotal, _NbOfDigitBehindComma_ )
	));
	
	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>
