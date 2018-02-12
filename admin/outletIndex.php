<?php
	/***************************************************************************
	* admin/outletIndex.php :: Admin Outlet Page							*
	****************************************************************************
	* The outlet quick add/edit/delete page for admin						*
	*															*
	* Version			: 1											*
	* Author			: Ricky Kurniawan [FireSnakeR ]					*
	* Created			: 2009-03-28 									*
	* Last modified	: 2014-08-01									*
	*															*
	* 			Copyright (c) 2009-2014 FireSnakeR						*
	***************************************************************************/

	//*** BEGIN INITIALIZATION ***********************************************//
	//+++ load the absolute necessities ++++++++++++++++++++++++++++++++++++++//
	include_once("dirConf.php");
	include_once($libPath . "/gateObject.php");
	//+++ do session check first +++++++++++++++++++++++++++++++++++++++++++++//
	session_start();
	$gate = new gate($_SESSION);
	if ( $gate->is_valid_role('user_ID', 'user_Name', 'admin') ) //remember, the role value must always be lowercase
	{
		//clear the outlet_ID
		if ( isset($_SESSION['outlet_ID']) )
			$_SESSION['outlet_ID']= "";
		
		if ( !isset($_SESSION['outletIndex_searchBy']) )
		{
			$_SESSION['outletIndex_searchBy'] = "";
		}
		if ( !isset($_SESSION['outletIndex_sortBy']) )
			$_SESSION['outletIndex_sortBy'] = "";
		if ( !isset($_SESSION['outletIndex_limitBy']) )
			$_SESSION['outletIndex_limitBy'] = "";
	}
	else
	{
		$_SESSION = array();
		session_destroy();
		header("Location:index.php");
		exit;
	}
	//+++ include necessary libraries ++++++++++++++++++++++++++++++++++++++++//
	include_once($libPath . "/classWebsite.php");
	include_once($libPath . "/classValidator.php");
	include_once($libPath . "/classUser.php");
	include_once($libPath . "/classOutlet.php");
	//+++ initialize objects and classes +++++++++++++++++++++++++++++++++++++//
	$cWebsite = new Website;
	$cValidator = new Validator;
	$cUser = new User($_SESSION['user_ID']);
	$cOutlet = new Outlet;
	//+++ declare and initialize page variables ++++++++++++++++++++++++++++++//
	$sErrorMessages = FALSE;
	$sMessages = FALSE;

	$aSearchBy = $_SESSION['outletIndex_searchBy'];
	$aSortBy = $_SESSION['outletIndex_sortBy'];
	$sSortName = "ASC";
	$sSortAddress = "ASC";
	$aLimitBy = $_SESSION['outletIndex_limitBy'];
	$sPageName = "Outlet";
	//*** END INITIALIZATION *************************************************//

	//*** BEGIN PAGE PROCESSING *************************************************//
		//+++ BEGIN $_POST processing +++++++++++++++++++++++++++++++++++++++//
		if ( count($_POST) > 0 ) //$_POST is always set, so we check by # of element
		{
			//check and process outlet quick add
			if ( isset($_POST['outletSubmit']) )
			{
				$aValidType = array(
					"outletName" => "word",
					"outletAddress" => "word"
				);
				if ( $cValidator->isValidType($_POST, $aValidType))
				{
					$aOutlet = array(
						"Name" => $_POST['outletName'],
						"Address" => $_POST['outletAddress']
					);
					$iInsertResult = $cOutlet->Insert($aOutlet);
					if ($iInsertResult == FALSE)
					{
						$sErrorMessages = "Insert failed, please check data again!"; //TODO: change into language variables
					}
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}

			//check and process add outlet
			if ( isset($_POST['outletAddSubmit']) )
			{
				header("location:outlet.php");
				exit;
			}
			
			//check and process add outlet
			if ( isset($_POST['outletImportSubmit']) )
			{
				header("location:outletImport.php");
				exit;
			}
			
			//check and process outlet search
			if ( isset($_POST['searchSubmit']) )
			{
				$aSearchBy = array(
					"searchName" => "alphanumericOrEmpty",
					"searchAddress" => "alphanumericOrEmpty"
				);
				if ( $cValidator->isValidType($_POST, $aSearchBy))
				{
					$aSearchBy = array(
						"outlet.Name" => $_POST['searchName'],
						"outlet.Address" => $_POST['searchAddress'],
					);
					$_SESSION['outletIndex_searchBy'] = $aSearchBy;
					$sMessages = "Search!"; //TODO: change into language variables
					$aLimitBy =array(
						"start" => 0,
						"nbOfData" => 10
					);
				}
				else
				{
					$sErrorMessages = "Invalid datatype, please check again!"; //TODO: change into language variables
				}
			}
			
			//check and process outlet sort name
			if ( isset($_POST['sortNameSubmit']) )
			{
				if ( array_key_exists('outlet.Name', $_SESSION['outletIndex_sortBy']) )
				{
					if ( $_SESSION['outletIndex_sortBy']['outlet.Name'] == "ASC" )
					{
						$sSortName = "DESC";
					}
				}

				$aSortBy = array(
					"outlet.Name" => $sSortName
				);
				$_SESSION['outletIndex_sortBy'] = $aSortBy;
				$sMessages = "Sort by Name!"; //TODO: change into language variables
			}
			
			//check and process outlet sort address
			if ( isset($_POST['sortAddressSubmit']) )
			{
				if ( array_key_exists('outlet.Address', $_SESSION['outletIndex_sortBy']) )
				{
					if ( $_SESSION['outletIndex_sortBy']['outlet.Address'] == "ASC" )
					{
						$sSortCategory = "DESC";
					}
				}
				
				$aSortBy = array(
					"outlet.Address" => $sSortAddress
				);
				$_SESSION['outletIndex_sortBy'] = $aSortBy;
				$sMessages = "Sort by Address!"; //TODO: change into language variables
			}
			
			//check and process outlet edit
			if ( isset($_POST['editSubmit']) )
			{
				$aOutletID = array(
					"editID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aOutletID))
				{
					$_SESSION['outlet_ID'] = $_POST['editID'];
					header("location:outlet.php");
					exit;
				}
			}
			
			//check and process outlet delete
			if ( isset($_POST['deleteSubmit']) )
			{
				$aOutletID = array(
					"deleteID" => "numeric"
				);
				if ( $cValidator->isValidType($_POST, $aOutletID))
				{
					$cOutlet->Remove($_POST['deleteID']);
				}
			}
			
			//check and process outlet list previous
			if ( isset($_POST['previousSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['outletIndex_limitBy']['start'] - $_SESSION['outletIndex_limitBy']['nbOfData']) < 0 )?0:$_SESSION['outletIndex_limitBy']['start'] - $_SESSION['outletIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['outletIndex_limitBy']['nbOfData']
				);
				$_SESSION['outletIndex_limitBy'] = $aLimitBy;
				
				if ( ($_SESSION['outletIndex_limitBy']['start'] - $_SESSION['outletIndex_limitBy']['nbOfData']) < 0)
				{
					$sErrorMessages = "Start of record reached!"; //TODO: change into language variables
				}
			}
			
			//check and process outlet list next
			if ( isset($_POST['nextSubmit']) )
			{
				$aLimitBy = array(
					"start" => ( ($_SESSION['outletIndex_limitBy']['start'] + $_SESSION['outletIndex_limitBy']['nbOfData']) > count($cOutlet->GetOutletList($aSearchBy, $aSortBy)) )?$_SESSION['outletIndex_limitBy']['start']:$_SESSION['outletIndex_limitBy']['start'] + $_SESSION['outletIndex_limitBy']['nbOfData'],
					"nbOfData" => $_SESSION['outletIndex_limitBy']['nbOfData']
				);
				$_SESSION['outletIndex_limitBy'] = $aLimitBy;
				if ( ( $_SESSION['outletIndex_limitBy']['start'] + $_SESSION['outletIndex_limitBy']['nbOfData']) > count($cOutlet->GetOutletList($aSearchBy, $aSortBy)) )
				{
					$sErrorMessages = "End of record reached!"; //TODO: change into language variables
				}
			}
		}
		else
		{
			$sMessages = "Welcome!"; //TODO: change into language variables
			
			//reset all page variables here:
			$aSearchBy = array(
				"outlet.Name" => "",
				"outlet.Address" => ""
			);
			$aSortBy = array();
			$sSortName = "ASC";
			$sSortAddress = "ASC";
			$aLimitBy =array(
				"start" => 0,
				"nbOfData" => 10
			);
			$_SESSION['outletIndex_searchBy'] = $aSearchBy;
			$_SESSION['outletIndex_sortBy'] = $aSortBy;
			$_SESSION['outletIndex_limitBy'] = $aLimitBy;
		}
		//+++ END $_POST processing +++++++++++++++++++++++++++++++++++++++++//

		//get the outletList
		$aOutletList = $cOutlet->GetOutletList($aSearchBy, $aSortBy, $aLimitBy);
		$aFullOutletList = $cOutlet->GetOutletList();
	//*** END PAGE PROCESSING ***************************************************//
	
	//*** BEGIN PAGE RENDERING **************************************************//

	$websiteFiles = array(
		"site" => "site.htm",
		"navigation" => "navigation.htm",
		"content" => "admin/outletIndex.htm"
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
		"CSS_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?"normal":"error",
		"VAR_MESSAGE_GREETING" => ($sErrorMessages === FALSE)?$sMessages:$sErrorMessages,
		"VAR_FORM_ACTION" => "admin/outletIndex.php",

		"TEXT_LEGEND_OUTLETQUICKADD" => strtoupper("Outlet Quick Add"), //TODO: change into language variables
		"TEXT_LABEL_NAME" => "Name", //TODO: change into language variables
		"VAR_OUTLET_NAME" => "", //always empty because quick add only, cannot edit
		"TEXT_LABEL_ADDRESS" => "Address", //TODO: change into language variables
		"VAR_OUTLET_ADDRESS" => "", //always empty, because quick add only, cannot edit
		"TEXT_BUTTON_SUBMIT" => "Save", //TODO: change into language variables
		
		"TEXT_BUTTON_OUTLETADD" => "Add Outlet", //TODO: change into language variables
		
		"TEXT_BUTTON_OUTLETIMPORT" => "Import Outlet", //TODO: change into language variables
		
		"TEXT_LEGEND_OUTLETSEARCH" => strtoupper("Outlet Search"),  //TODO: change into language variables
		"TEXT_BUTTON_SEARCH" => "Search", //TODO: change into language variables
		"VAR_SEARCH_NAME" => (count($aSearchBy) > 0)?$aSearchBy['outlet.Name']:"",
		"VAR_SEARCH_ADDRESS" => (count($aSearchBy) > 0)?$aSearchBy['outlet.Address']:"",

		"TEXT_BUTTON_SORTNAME" => $sSortName, //TODO: change into language variables
		"TEXT_BUTTON_SORTADDRESS" => $sSortAddress, //TODO: change into language variables
		"TEXT_NO" => strtoupper("No."), //TODO: change into language variables
		"TEXT_NAME" => strtoupper("Name"), //TODO: change into language variables
		"TEXT_ADDRESS" => strtoupper("Address"), //TODO: change into language variables
		"TEXT_MASTEROUTLET" => strtoupper("Master Outlet"), //TODO: change into language variables
		//"TEXT_PHONE" => strtoupper("Phone"), //TODO: change into language variables
		//"TEXT_FAX" => strtoupper("Fax"), //TODO: change into language variables
		"TEXT_STATUS" => strtoupper("Status"), //TODO: change into language variables
		"TEXT_EDIT" => strtoupper("Edit"), //TODO: change into language variables
		"TEXT_DELETE" => strtoupper("Delete"), //TODO: change into language variables
		"TEXT_BUTTON_PREVIOUS" => "<-",
		"TEXT_BUTTON_NEXT" => "->",
	));
	
	$cWebsite->template->set_block("navigation", "navigation_top_admin");
	$cWebsite->template->parse("VAR_NAVIGATIONTOP", "navigation_top_admin");

	//outletListBlock
	$outletListBlock = array();
	for ($i = 0; $i < count($aOutletList); $i++)
	{
		$sMasterOutletName = '-';
		for ($j = 0; $j < count($aFullOutletList); $j++)
		{
			//echo $aOutletList[$i]['master_outlet_ID'] . "=" . $aOutletList[$j]['ID'];
			//echo "<br />";
			if ( $aOutletList[$i]['master_outlet_ID'] == $aFullOutletList[$j]['ID'] )
			{
				$sMasterOutletName = $aFullOutletList[$j]['name'];
				$j = count($aFullOutletList);
			}
		}

		$outletListBlock[] = array(
			"VAR_LIST_NO" => $i+1,
			"VAR_LIST_NAME" => $aOutletList[$i]['name'],
			"VAR_LIST_ADDRESS" => $aOutletList[$i]['address'],
			"VAR_LIST_MASTEROUTLET" => $sMasterOutletName,
			//"VAR_LIST_PHONE" => $aOutletList[$i]['phone'],
			//"VAR_LIST_FAX" => $aOutletList[$i]['fax'],
			"VAR_LIST_STATUS" => ($aOutletList[$i]['status'] >= 0)?"AKTIF":"TIDAK AKTIF",
			"VAR_LIST_ID" => $aOutletList[$i]['ID'],
			"TEXT_BUTTON_EDIT" => "Edit", //TODO: change into language variables
			"TEXT_BUTTON_DELETE" => "Delete" //TODO: change into language variables
		);
	}
	$cWebsite->buildBlock("content", "outletListBlock", $outletListBlock);
	
	$cWebsite->template->set_block("content", "outletListEmptyBlock");
	//outletListEmptyBlock
	if ( count($aOutletList) == 0)
	{
		$cWebsite->template->set_var(array(
			"TEXT_LIST_EMPTY" => "List is empty, please add data or change search parameter." //TODO: change into language variables
		));
		$cWebsite->template->parse("outletListEmptyBlock", "outletListEmptyBlock");
	}
	else
	{
		$cWebsite->template->set_var("outletListEmptyBlock", "");	
	}

	$cWebsite->buildContent("VAR_CONTENT");
	$cWebsite->display();
	//*** END PAGE RENDERING ****************************************************//

?>