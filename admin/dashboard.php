<?php
	/***************************************************************************
	* admin/dashboard.php :: Admin Dashboard Page						*
	****************************************************************************
	* The dashboard page for admin									*
	*															*
	* Version			: 2											*
	* Author			: Ricky Kurniawan [ FireSnakeR ]					*
	* Created			: 2014-06-29 									*
	* Last modified	: 2014-08-01									*
	*															*
	* 				Copyright (c) 2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( !$gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classUser.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cUser = new User($_SESSION['user_ID']);
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;
	$sPageName = "Dashboard";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/dashboard.htm"
	);
	$cWebsite->init($websiteFiles, $htmPath);

	$cWebsite->template->set_var(array(
		//site variables
		"VAR_SITENAME" => _siteName_,
		"VAR_SITEURL" => _siteBaseURI_,
		"VAR_PAGENAME" => $sPageName,
		"VAR_USERLOGGEDIN" => ucfirst($_SESSION['user_Name']),
		"VAR_COPYRIGHTYEAR" => "2005-".date("Y"),

		//page text
		"VAR_MESSAGE_GREETING" => "Welcome " . $cUser->Name . "!"
	));
	

	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

/*
echo "System Debugging <br />";

include_once("dirConf.php");
include_once($rootPath . "config.php");
include_once($libPath . "/classDatabase.php");

$cDB = new Database;
$cDB->dbOpen(_DBHOST_, _DBUSER_, _DBPASS_, _DBNAME_); //attempt to open database

function is_field_zero($cDB, $sField, $sTable)
{
	$sQuery  = 'SELECT *';
	$sQuery .= ' FROM ' . $sTable . '';
	$sQuery .= ' WHERE ' . $sField . ' = 0';

	$aResult = $cDB->dbQuery($sQuery);

	if ( count($aResult) > 0 )
	{
		return $aResult;
	}
	else
	{
		return FALSE;
	}
}

function is_data_missing($cDB, $sField, $sTable)
{
	$aReturnResult = array();

	$sQuery  = 'SELECT ' . $sField .'';
	$sQuery .= ' FROM ' . $sTable . '';
	$sQuery .= ' GROUP BY ' . $sField . '';
	
	$aResult = $cDB->dbQuery($sQuery);
	
	if ( count($aResult) > 0 )
	{
		for ($i = 0; $i < count($aResult); $i++)
		{
			if ($aResult[$i][$sField] > 0)
			{
				$sFieldTable = str_replace('_ID','', $sField);
				$sQuery  = 'SELECT ID';
				$sQuery .= ' FROM ' . $sFieldTable . '';
				$sQuery .= ' WHERE ID = "' . $aResult[$i][$sField] . '"';
		
				$aResult2 = $cDB->dbQuery($sQuery);
		
				if ( count($aResult2) <= 0 )
				{
					//now we search for how many data are affected
					$sQuery  = 'SELECT *';
					$sQuery .= ' FROM ' . $sTable;
					$sQuery .= ' WHERE ' . $sField .' = ' . $aResult[$i][$sField];

					$aResult3 = $cDB->dbQuery($sQuery);

					$aReturnResult[] = array(
						$aResult[$i][$sField] => $aResult3
					);
				}
			}
		}
	}

	if ( count($aReturnResult) == 0 )
	{
		return FALSE;
	}
	else
	{
		return $aReturnResult;
	}
}

function get_details($cDB, $sIDValue, $sTable)
{
	$aReturnResult = array();

	$sQuery = 'SELECT * FROM ' . $sTable . ' WHERE ' . $sTable . '.ID=' . $sIDValue;

	$aResult = $cDB->dbQuery($sQuery);

	switch ($sTable)
	{
		case 'deposit':
			for ($i = 0; $i < count($aResult); $i++)
			{
				$sQuery = 'SELECT Name FROM outlet WHERE ID=' . $aResult[$i]['outlet_ID'];
				$aResult2 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"outlet_ID" => $aResult[$i]['outlet_ID'] . "::" . $aResult2[0]['Name'],
					"Notes" => $aResult[$i]['Notes'],
					"Finance Notes" => $aResult[$i]['FinanceNotes'],
					"Price" => number_format($aResult[$i]['Price'], _NbOfDigitBehindComma_),
					"Date" => date('d-M-Y' ,strtotime($aResult[$i]['Date']) ),
					"Status" => ($aResult[$i]['Status'] == 0)?'Unverified':'Verified'
				);
			}
		break;
		case 'expenses':
			for ($i = 0; $i < count($aResult); $i++)
			{
				$sQuery = 'SELECT Name FROM outlet WHERE ID=' . $aResult[$i]['outlet_ID'];
				$aResult2 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"outlet_ID" => $aResult[$i]['outlet_ID'] . "::" . $aResult2[0]['Name'],
					"Name" => $aResult[$i]['Name'],
					"Notes" => $aResult[$i]['Notes'],
					"Finance Notes" => $aResult[$i]['FinanceNotes'],
					"Price" => number_format($aResult[$i]['Price'], _NbOfDigitBehindComma_),
					"Date" => date('d-M-Y' ,strtotime($aResult[$i]['Date']) ),
					"Status" => ($aResult[$i]['Status'] == 0)?'Unverified':'Verified'
				);
			}
		break;
		case 'purchase':
			for ($i = 0; $i < count($aResult); $i++)
			{
				//get the purchase detail data (if any)
				$sQuery = 'SELECT * FROM purchase_detail WHERE purchase_ID=' . $sIDValue;
				$aResult2 = $cDB->dbQuery($sQuery);

				$sDetail = '-' . "\r\n<br />";
				for ($j = 0; $j < count($aResult2); $j++)
				{
					$sQuery = 'SELECT Name FROM product WHERE ID=' . $aResult2[$j]['product_ID'];
					$aResult3 = $cDB->dbQuery($sQuery);

					$sDetail .= 'ID =>' . $aResult2[$j]['ID'] . "\r\n<br />";
					$sDetail .= 'purchase_ID =>' . $aResult2[$j]['purchase_ID'] . "\r\n<br />";
					$sDetail .= 'product =>' . $aResult2[$j]['product_ID'] . "::" . $aResult3[0]['Name'] . "\r\n<br />";
					$sDetail .= 'Quantity =>' . number_format($aResult2[$j]['Quantity'], _NbOfDigitBehindComma_) . "\r\n<br />";
					$sDetail .= 'Price =>' . number_format($aResult2[$j]['Price'], _NbOfDigitBehindComma_) . "\r\n<br />";
					$sDetail .= '===' . "\r\n<br />";
				}

				$sQuery = 'SELECT Name FROM outlet WHERE ID=' . $aResult[$i]['outlet_ID'];
				$aResult4 = $cDB->dbQuery($sQuery);

				$sQuery = 'SELECT Name FROM paymentType WHERE ID=' . $aResult[$i]['paymentType_ID'];
				$aResult5 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"outlet_ID" => $aResult[$i]['outlet_ID'] . "::" . $aResult4[0]['Name'],
					"paymentType_ID" => $aResult[$i]['paymentType_ID'] . "::" . $aResult5[0]['Name'],
					"Date" => date('d-M-Y' ,strtotime($aResult[$i]['Date']) ),
					"Notes" => $aResult[$i]['Notes'],
					"Verify Notes" => $aResult[$i]['VerifyNotes'],
					"Status" => ($aResult[$i]['Status'] == 0)?'Unverified':'Verified',
					"Detail" => $sDetail
				);
			}
		break;
		case 'purchase_detail':
			for ($i = 0; $i < count($aResult); $i++)
			{
				$sQuery = 'SELECT Name FROM product WHERE ID=' . $aResult[$i]['product_ID'];
				$aResult2 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"purchase_ID" => $aResult[$i]['purchase_ID'],
					"product" => $aResult[$i]['product_ID'] . "::" . $aResult2[0]['Name'],
					"Quantity" => number_format($aResult[$i]['Quantity'], _NbOfDigitBehindComma_),
					"Price" => number_format($aResult[$i]['Price'], _NbOfDigitBehindComma_)
				);
			}
		break;
		case 'sales':
			for ($i = 0; $i < count($aResult); $i++)
			{
				//get the purchase detail data (if any)
				$sQuery = 'SELECT * FROM sales_detail WHERE sales_ID=' . $sIDValue;
				$aResult2 = $cDB->dbQuery($sQuery);

				$sDetail = '-' . "\r\n<br />";
				for ($j = 0; $j < count($aResult2); $j++)
				{
					$sQuery = 'SELECT Name FROM product WHERE ID=' . $aResult2[$j]['product_ID'];
					$aResult3 = $cDB->dbQuery($sQuery);

					$sDetail .= 'ID =>' . $aResult2[$j]['ID'] . "\r\n<br />";
					$sDetail .= 'sales_ID =>' . $aResult2[$j]['sales_ID'] . "\r\n<br />";
					$sDetail .= 'product =>' . $aResult2[$j]['product_ID'] . "::" . $aResult3[0]['Name'] . "\r\n<br />";
					$sDetail .= 'Quantity =>' . number_format($aResult2[$j]['Quantity'], _NbOfDigitBehindComma_) . "\r\n<br />";
					$sDetail .= 'Discount =>' . $aResult2[$j]['Discount'] . "\r\n<br />";
					$sDetail .= 'Price =>' . number_format($aResult2[$j]['Price'], _NbOfDigitBehindComma_) . "\r\n<br />";
					$sDetail .= 'ajaxPostID =>' . $aResult2[$j]['ajaxPostID'] . "\r\n<br />";
					$sDetail .= '===' . "\r\n<br />";
				}

				$sQuery = 'SELECT Name FROM outlet WHERE ID=' . $aResult[$i]['outlet_ID'];
				$aResult4 = $cDB->dbQuery($sQuery);

				$sQuery = 'SELECT Name FROM paymentType WHERE ID=' . $aResult[$i]['paymentType_ID'];
				$aResult5 = $cDB->dbQuery($sQuery);

				$sQuery = 'SELECT Name FROM employee WHERE ID=' . $aResult[$i]['employee_ID'];
				$aResult6 = $cDB->dbQuery($sQuery);

				$sQuery = 'SELECT Name FROM client WHERE ID=' . $aResult[$i]['client_ID'];
				$aResult7 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"outlet_ID" => $aResult[$i]['outlet_ID'] . "::" . $aResult4[0]['Name'],
					"employee_ID" => $aResult[$i]['employee_ID'] . "::" . $aResult6[0]['Name'],
					"client_ID" => $aResult[$i]['client_ID'] . "::" . $aResult7[0]['Name'],
					"paymentType_ID" => $aResult[$i]['paymentType_ID'] . "::" . $aResult5[0]['Name'],
					"Date" => date('d-M-Y' ,strtotime($aResult[$i]['Date']) ),
					"Notes" => $aResult[$i]['Notes'],
					"Finance Notes" => $aResult[$i]['FinanceNotes'],
					"ajaxPostID" => $aResult[$i]['ajaxPostID'],
					"Status" => ($aResult[$i]['Status'] == 0)?'Unverified':'Verified',
					"Detail" => $sDetail
				);
			}
		break;
		case 'sales_detail':
			for ($i = 0; $i < count($aResult); $i++)
			{
				$sQuery = 'SELECT Name FROM product WHERE ID=' . $aResult[$i]['product_ID'];
				$aResult2 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"sales_ID" => $aResult[$i]['sales_ID'],
					"product_ID" => $aResult[$i]['product_ID'] . "::" . $aResult2[0]['Name'],
					"Quantity" => number_format($aResult[$i]['Quantity'], _NbOfDigitBehindComma_),
					"Discount" => $aResult[$i]['Discount'],
					"Price" => number_format($aResult[$i]['Price'], _NbOfDigitBehindComma_),
					"ajaxPostID" => $aResult[$i]['ajaxPostID']
				);
			}
		break;
		case 'transfer':
			for ($i = 0; $i < count($aResult); $i++)
			{
				//get the purchase detail data (if any)
				$sQuery = 'SELECT * FROM transfer_detail WHERE transfer_ID=' . $sIDValue;
				$aResult2 = $cDB->dbQuery($sQuery);

				$sDetail = '-' . "\r\n<br />";
				for ($j = 0; $j < count($aResult2); $j++)
				{
					$sQuery = 'SELECT Name FROM product WHERE ID=' . $aResult2[$j]['product_ID'];
					$aResult3 = $cDB->dbQuery($sQuery);

					$sDetail .= 'ID =>' . $aResult2[$j]['ID'] . "\r\n<br />";
					$sDetail .= 'transfer_ID =>' . $aResult2[$j]['transfer_ID'] . "\r\n<br />";
					$sDetail .= 'product =>' . $aResult2[$j]['product_ID'] . "::" . $aResult3[0]['Name'] . "\r\n<br />";
					$sDetail .= 'quantity =>' . number_format($aResult2[$j]['quantity'], _NbOfDigitBehindComma_) . "\r\n<br />";
					$sDetail .= '===' . "\r\n<br />";
				}

				$sQuery = 'SELECT Name FROM outlet WHERE ID=' . $aResult[$i]['From_outlet_ID'];
				$aResult4 = $cDB->dbQuery($sQuery);

				$sQuery = 'SELECT Name FROM outlet WHERE ID=' . $aResult[$i]['To_outlet_ID'];
				$aResult5 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"From_outlet_ID" => $aResult[$i]['From_outlet_ID'] . "::" . $aResult4[0]['Name'],
					"To_outlet_ID" => $aResult[$i]['To_outlet_ID'] . "::" . $aResult5[0]['Name'],
					"Notes" => $aResult[$i]['Notes'],
					"Date" => date('d-M-Y' ,strtotime($aResult[$i]['Date']) ),
					"Status" => ($aResult[$i]['Status'] == 0)?'Unverified':'Verified',
					"Detail" => $sDetail
				);
			}
		break;
		case 'transfer_detail':
			for ($i = 0; $i < count($aResult); $i++)
			{
				$sQuery = 'SELECT Name FROM product WHERE ID=' . $aResult[$i]['product_ID'];
				$aResult2 = $cDB->dbQuery($sQuery);

				$aReturnResult[] = array(
					"ID" => $aResult[$i]['ID'],
					"transfer_ID" => $aResult[$i]['transfer_ID'],
					"product_ID" => $aResult[$i]['product_ID'] . "::" . $aResult2[0]['Name'],
					"quantity" => number_format($aResult[$i]['quantity'])
				);
			}
		break;
		default:
		break;
	}

	return $aReturnResult;
}

//what can go wrong in purchase, sales, transfer:
//deposit: outlet_ID = 0 or outlet_ID does not exists any more
//expenses: outlet_ID = 0 or outlet_ID does not exists any more
//purchase : outlet_ID = 0 or outlet_ID does not exists any more
//purchase_detail: purchase_ID = 0 or purchase_ID does not exists any more
//purchase_detail: product_ID = 0 or product_ID does not exists any more
//sales : outlet_ID = 0 or outlet_ID does not exists any more
//sales_detail: sales_ID = 0 or sales_ID does not exists any more
//sales_detail: product_ID = 0 or product_ID does not exists any more
//transfer: from_outlet_ID = 0 or from_outlet_ID does not exists any more
//transfer: to_outlet_ID = 0 or to_outlet_ID does not exists any more
//transfer_detail: transfer_ID = 0 or transfer_ID does not exists any more
//transfer_detail: product_ID = 0 or product_ID does not exists any more
$aTableAndField = array(
	"deposit" => "outlet_ID",
	"expenses" => "outlet_ID",
	"purchase" => "outlet_ID",
	"purchase_detail" => "purchase_ID",
	"purchase_detail" => "product_ID",
	"sales" => "outlet_ID",
	"sales_detail" => "sales_ID",
	"sales_detail" => "product_ID",
	"transfer" => "from_outlet_ID",
	"transfer" => "to_outlet_ID",
	"transfer_detail" => "transfer_ID",
	"transfer_detail" => "product_ID"
);

foreach ($aTableAndField as $sTable => $sField)
{
	$x = is_field_zero($cDB, $sField, $sTable);
	if ( $x != FALSE )
	{
		echo 'Table ' . strtoupper($sTable) . ' '. $sField . '=0 :: YES :: ' . count($x) . ' Data<br /><br />';
		for ($i = 0; $i < count($x); $i++)
		{
			$z = get_details($cDB, $x[$i]['ID'], $sTable);
			for ($k = 0; $k < count($z); $k++)
			{
				foreach ($z[$k] as $key => $value)
				{
					echo $key . "=>" . $value . "<br />";
				}
			}
			echo "<br />";
		}
		echo "<hr />";
	}

	$y = is_data_missing($cDB, $sField, $sTable);
	if ( $y != FALSE )
	{
		for ($i = 0; $i < count($y); $i++)
		{
			foreach ($y[$i] as $key => $value)
			{
				echo 'Table ' . strtoupper($sTable) . ' ' . $sField . ' ' . $key . " is missing :: " . count($value) . " Data<br /><br />";

				for ($j = 0; $j < count($value); $j++)
				{
					$z = get_details($cDB, $value[$j]['ID'], $sTable);
					for ($k = 0; $k < count($z); $k++)
					{
						foreach ($z[$k] as $key2 => $value2)
						{
							echo $key2 . "=>" . $value2 . "<br />";
						}
					}
					echo "<br />";
				}
			}
		}
		
		
		echo "<hr />";
	}
}

echo "System Debugging Sales Order <br />";
//in sales order, we do check if verified data from sales_order is properly inserted to sales
//first we retrieve all verified sales order
include_once($libPath . "/classSales.php");
$cSales = new Sales;

$aSearchParam = array(
	"Status" => "1"
);
$aSalesOrder = $cSales->GetSalesOrderList($aSearchParam);

for ($i = 0; $i < count($aSalesOrder); $i++)
{
	echo $aSalesOrder[$i]['ID'] . " ";

	$aSearchParam2 = array(
		"sales_order_ID" => $aSalesOrder[$i]['ID']
	);
	$aSales = $cSales->GetSalesList($aSearchParam2);

	switch (count($aSales) )
	{
		case 0:
			echo "not inserted";
		break;
		case 1:
			echo "OK";
		break;
		default:
			echo "duplicate";
		break;
	}

	$aSalesOrderDetail = $cSales->GetSalesOrderWithDetail($aSalesOrder[$i]['ID']);

	echo " detail =" . count($aSalesOrderDetail) . " record(s) ";

	for ($j = 0; $j < count($aSalesOrderDetail); $j++)
	{
		$aSearchParam3 = array(
			"sales_order_detail_ID" => $aSalesOrderDetail[$j]['detail_ID']
		);
		$aSalesDetail = $cSales->GetSalesDetailList($aSearchParam3);

		switch (count($aSalesDetail) )
		{
			case 0:
				echo "not inserted";
			break;
			case 1:
				echo "OK";
			break;
			default:
				echo "duplicate";
			break;
		}
	}

	echo "<br />";

}

//get all sales where sales_order_ID > 0
//cross check the sales_order_ID exists

$sQuery = 'SELECT * FROM sales WHERE sales_order_ID > 0';
$aResult = $cDB->dbQuery($sQuery);

for ($i = 0; $i < count($aResult); $i++)
{
	$sQuery = 'SELECT * FROM sales_order WHERE ID = "' . $aResult[$i]['sales_order_ID'] . '"';
	$aResult2 = $cDB->dbQuery($sQuery);

	switch ( count($aResult2) )
	{
		case 0:
			echo $aResult[$i]['sales_order_ID'] . " not found <br />";
		break;
		default:
			//do nothing
		break;
	}
}
*/


?>