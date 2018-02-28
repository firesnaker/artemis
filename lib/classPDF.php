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
	* lib/classPDF.php :: PDF CLASS								*
	*********************************************************************
	* All related pdf creation function										*
	*																	*
	* Version		: 0.1												*
	* Author		: FireSnakeR 										*
	* Created		: 2011-11-19 										*
	* Last modified	: 2013-02-03										*
	* 																	*
	*********************************************************************/

	if ( !class_exists('PDF') )
	{
		//+++ BEGIN library inclusion ++++++++++++++++++++++++++++++++++//
		include("dirConf.php"); //dirConf is to be included without _once because it must always be called locally
		include_once($rootPath . "config.php");
		include_once($libPath . "/mc_table.php");
		//+++ END library inclusion ++++++++++++++++++++++++++++++++++++//
	
		class PDF extends PDF_MC_Table
		{
			var $outletName			= FALSE;
			var $outletAddress			= FALSE;
			var $reportDate			= FALSE;
			var $printDate				= FALSE;

			//*** BEGIN FUNCTION LIST ***********************************//
			// ImprovedTableDailySales($header, $data)
			// Header()
			//*** END FUNCTION LIST *************************************//

			//*** BEGIN FUNCTION ****************************************//
			// Better table
			function ImprovedTableDailySales($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);

			    // Column widths
			    $w = array(10, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18);
			    // Header
			    for($i=0;$i<count($header);$i++)
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    $this->Ln();
			    
			    // Column widths
			    $this->SetWidths(array(10, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18));
			    $this->SetAligns(array('R', 'L', 'L', 'L', 'L', 'R', 'R', 'R', 'R', 'R', 'R'));

				// Data
				for ($i = 0; $i < count($data); $i++)
				{
			    		$this->Row($data[$i]);
			   	}
			}
			
			function ImprovedTableDailyExpenses($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);

				// Title
				$this->Ln(5);
			    $this->MultiCell(0,7,"Expenses",1,'C');
			    // Line break
			    $this->Ln(5);

			    // Column widths
			    $w = array(10, 140, 40);
			    // Header
			    for($i=0;$i<count($header);$i++)
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    $this->Ln();

			    // Column widths
			    $this->SetWidths(array(10, 140, 40));
			    $this->SetAligns(array('R', 'L', 'R'));

				// Data
				for ($i = 0; $i < count($data); $i++)
				{
			    		$this->Row($data[$i]);
			   	}
			}
			
			function ImprovedTableDailyDeposit($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);

				// Title
				$this->Ln(5);
			    $this->MultiCell(0,7,"Deposit",1,'C');
			    // Line break
			    $this->Ln(5);

			    // Column widths
			    $w = array(10, 140, 40);
			    // Header
			    for($i=0;$i<count($header);$i++)
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    $this->Ln();

			    // Column widths
			    $this->SetWidths(array(10, 140, 40));
			    $this->SetAligns(array('R', 'L', 'R'));

				// Data
				for ($i = 0; $i < count($data); $i++)
				{
			    		$this->Row($data[$i]);
			   	}
			}
			
			function ImprovedTableDailySalesOld($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);
			    
			    // Column widths
			    $w = array(6, 15, 15, 15, 28, 20, 20, 20, 20, 20, 20);
			    // Header
			    for($i=0;$i<count($header);$i++)
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    $this->Ln();
			    
			    // Data
			    foreach($data as $row)
			    {
			    		//max_row_height is used to consolidate the y-coordinate for the beginning of next row.
			    		$max_row_height = 6;
			    		for ($i = 0; $i < count($row); $i++)
			    		{
			    			$x = $this->GetX();
							$y = $this->GetY();
			    			$this->MultiCell($w[$i],6,$row[$i],1);
			    			$new_x = $x + $w[$i];
			    			$new_y = $this->GetY();
			    			if ($new_y > $max_row_height)
			    				$max_row_height = $new_y;
				         $this->SetXY($new_x, $y);
				      } 
			         $this->Ln($max_row_height - $y);
			         
			         //check $y, if it is close to end of page, then force page break;
			         if ( $y > 200 )
			         {
			         	$this->AddPage();
			         } 
			    }
			    // Closing line
			    //$this->Cell(array_sum($w),0,'','T');
			}
			
			// Better table
			function ImprovedTableDailyStock($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);

			    // Column widths
			    $w = array(10, 140, 40);
			    // Header
			    for($i=0;$i<count($header);$i++)
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    $this->Ln();
			    
			    // Column widths
			    $this->SetWidths(array(10, 140, 40));
			    $this->SetAligns(array('R', 'L', 'R'));

				// Data
				for ($i = 0; $i < count($data); $i++)
				{
			    		$this->Row($data[$i]);
			   	}
			}
			
			function ImprovedTableDailyStockOld($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);

			    // Column widths
			    $w = array(10, 80, 20);
			    // Header
			    for($i=0;$i<count($header);$i++)
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    $this->Ln();
			    
			    // Data
			    foreach($data as $row)
			    {
			    		//max_row_height is used to consolidate the y-coordinate for the beginning of next row.
			    		$max_row_height = 6;
			    		for ($i = 0; $i < count($row); $i++)
			    		{
			    			$x = $this->GetX();
							$y = $this->GetY();
			    			$this->MultiCell($w[$i],6,$row[$i],1);
			    			$new_x = $x + $w[$i];
			    			$new_y = $this->GetY();
			    			if ($new_y > $max_row_height)
			    				$max_row_height = $new_y;
				         $this->SetXY($new_x, $y);
				      } 
			         $this->Ln($max_row_height - $y);
			         
			         //check $y, if it is close to end of page, then force page break;
			         if ( $y > 200 )
			         {
			         	$this->AddPage();
			         } 
			    }
			    // Closing line
			    //$this->Cell(array_sum($w),0,'','T');
			}

			// Better table
			function ImprovedTableDailySummary($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);

			    // Column widths
			    $w = array(10, 30, 45, 35, 35, 35);
			    // Header
			    for($i=0;$i<count($header);$i++)
			    {
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    }
			    $this->Ln();
			    
			    // Column widths
			    $this->SetWidths(array(10, 30, 45, 35, 35, 35));

				// Data
				for ($i = 0; $i < count($data); $i++)
				{
					$this->SetAligns(array('R', 'C', 'L', 'R', 'R', 'R'));
			    		$this->Row($data[$i]);
			   	}
			}

			// Better table
			function ImprovedTableTransferCreate($header, $data)
			{
				// Arial 10
			    $this->SetFont('Arial','',10);

			    // Column widths
			    $w = array(10, 52, 33, 33);
			    // Header
			    for($i=0;$i<count($header);$i++)
			        $this->Cell($w[$i],7,$header[$i],1,0,'C');
			    $this->Ln();
			    
			    // Column widths
			    $this->SetWidths(array(10, 52, 33, 33));
			    $this->SetAligns(array('C', 'L', 'R', 'C'));

				// Data
				for ($i = 0; $i < count($data); $i++)
				{			    
			    		$this->Row($data[$i]);
			   	}
			}

			function Header()
			{
			    // Arial bold 15
			    $this->SetFont('Arial','B',15);
			    // Title
			    $this->MultiCell(0,7,$this->outletName . "\n" . $this->outletAddress . "\n" . $this->reportDate,1,'C');
			    // Line break
			    $this->Ln(5);
			}

			function Footer2()
			{
				// Line break
			    $this->Ln(5);
			    // Arial bold 15
			    $this->SetFont('Arial','B',10);
			    // Title
			    // Column widths
			    $this->SetWidths(array(64, 64));
			    $this->SetAligns(array('C', 'C'));

				$data = array();
				$data[] = array("Tanda Terima", "Hormat Kami");
				$data[] = array("", "");
				$data[] = array("", "");
				$data[] = array("", "");
				$data[] = array("", "");
				$data[] = array("", "");
				$data[] = array("(..............................)", "(..............................)");
				$data[] = array("", "");
				$data[] = array($this->printDate);

				// Data
				for ($i = 0; $i < count($data); $i++)
				{			    
			    		$this->RowNoBorder($data[$i]);
			   	}
			    // Line break
			    $this->Ln(5);
			}

		}
		//*** END FUNCTION **********************************************//
	}
?>
