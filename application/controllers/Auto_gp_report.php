<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('mssql.connect_timeout',0);
ini_set('mssql.timeout',0);
set_time_limit(0);  
ini_set('memory_limit', -1);

//client_buffer_max_kb_size = '50240'
//sqlsrv.ClientBufferMaxKBSize = 50240

class Auto_gp_report extends CI_Controller {
	
	public function __construct(){
		date_default_timezone_set('Asia/Manila');	
		parent::__construct();
		$this->load->model("Auto_sales_model_","db_con");
		$this->load->library("excel");
	}

	public function generate_consolitated_movements(){

	$file_dir =  realpath(dirname(__FILE__). '/../../report');
	$this->load->library('excel');
	$objPHPExcel = new PHPExcel();
		$lettercol1 = 'A';
		$lettercol2 = 'B';
		$lettercol3 = 'C';
		$lettercol4 = 'D';
			
		$batch_db = false;
		$db = $this->db_con->gen_rep_database_retail($batch_db);
		$objPHPExcel->getActiveSheet()->setTitle('2018');
		foreach ($db as $i => $dbdetails) {
			$ms_name = $dbdetails->branch_name;
			$aria_db = $dbdetails->aria_db;
			$ms_db_133 = $dbdetails->ms_db_133;
			$ms_db = $dbdetails->ms_db;
			$accounts = $this->db_con->get_account_type($aria_db,array(6));
			$last_supp_id = $supp_name = $last_gst = '';
			$counter = $supp_total_ = $ov_total = 0;

			echo 'BRANCH:'.$ms_name.PHP_EOL;
			
			$startdate =  '2018-01-01';
			$enddate = '2018-01-31';
			$year  = date('Y',strtotime($startdate));

			$firstday =  date('m',strtotime($startdate));
			$lastday =  date('t',strtotime($startdate));

			$ms_db = $this->db_con->gen_rep_database_server($ms_db_133);
			$get_conso = $this->db_con->get_data($ms_db,$year,'Conso',$firstday,$lastday,$ms_db);
			if($get_conso){
				$sales = $get_conso[0];
				$after_adj = $get_conso[5];
			}else{
				$sales = 0;
				$after_adj =  0;	
			}
			
			$movs = null;
			$movs_res = $this->db_con->get_movements($ms_db,$movs,$startdate, $enddate);
			$num = 0;
			$num =  $num+4;	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'1',$startdate);	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'3',$ms_name);	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'4','Inventory Adjustments');	
			$inv_adj_total = 0;
			$exclude = array('D2BSR','SA2BO','PSV','PS','STI','STO','ITI','ITO','R2SSA');
			$negate = array('AIL','FDFB','IGNBO','IGNSA','PFBO','SW','NASA','STNA','NGBO','SWNA');
			if($movs_res){
				foreach ($movs_res as $movs_mskey => $movs_msvalue) {
					if($movs_mskey == 0){
						$num =  $movs_mskey+$num;
					}

					if(in_array($movs_msvalue->movementcode, $exclude))
						continue;
					if(in_array($movs_msvalue->movementcode, $negate)){
						$num =  $num+1;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.''.$num,$movs_msvalue->Description);
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.''.$num,-$movs_msvalue->total);
						$inv_adj_total = $inv_adj_total + -$movs_msvalue->total;

					}else{
						$num =  $num+1;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.''.$num,$movs_msvalue->Description);
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.''.$num,$movs_msvalue->total);
						$inv_adj_total = $inv_adj_total + $movs_msvalue->total;
					}
				}
				$num =  $num+3;	
				
			$inv_total = $inv_adj_total;
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'20','Total Movements');	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.'20', $inv_total);
			}

				$lettercol1 = $this->increment($lettercol1, 4);
				$lettercol2 = $this->increment($lettercol2, 4);
				$lettercol3 = $this->increment($lettercol3, 4);
			}

		
			
		$lettercol1 = 'A';
		$lettercol2 = 'B';
		$lettercol3 = 'C';
		$lettercol4 = 'D';
			
		$batch_db_ = false;
		$db = $this->db_con->gen_rep_database_retail($batch_db_);
		$objPHPExcel->createSheet()->setTitle('2019');
		
		foreach ($db as $i => $dbdetails) {
			
			$ms_name = $dbdetails->branch_name;
			$aria_db = $dbdetails->aria_db;
			$ms_db_133 = $dbdetails->ms_db_133;
			$ms_db = $dbdetails->ms_db;

			$last_supp_id = $supp_name = $last_gst = '';
			$counter = $supp_total_ = $ov_total = 0;


			echo 'BRANCH:'.$ms_name.PHP_EOL;

			$startdate =  '2019-01-01';
			$enddate = '2019-01-31';
			$objPHPExcel->setActiveSheetIndex(1);

			$firstday =  date('m',strtotime($startdate));
			$lastday =  date('t',strtotime($startdate));

			$ms_db = $this->db_con->gen_rep_database_server($ms_db_133);
			$get_conso = $this->db_con->get_data($ms_db,$year,'Conso',$firstday,$lastday,$ms_db);
			if($get_conso){
				$sales = $get_conso[0];
				$after_adj = $get_conso[5];
			}else{
				$sales = 0;
				$after_adj =  0;	
			}
			$movs = null;
			$movs_res = $this->db_con->get_movements($ms_db,$movs,$startdate, $enddate);
			$num = 0;
			$num =  $num+4;	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'1',$startdate);	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'3',$ms_name);	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'4','Inventory Adjustments');	
			$inv_adj_total = 0;
			$exclude = array('D2BSR','SA2BO','PSV','PS','STI','STO','ITI','ITO','R2SSA');
			$negate = array('AIL','FDFB','IGNBO','IGNSA','PFBO','SW','NASA','STNA','NGBO','SWNA');
			
			if($movs_res){
				foreach ($movs_res as $movs_mskey => $movs_msvalue) {
					if($movs_mskey == 0){
						$num =  $movs_mskey+$num;
					}

					if(in_array($movs_msvalue->movementcode, $exclude))
						continue;
					if(in_array($movs_msvalue->movementcode, $negate)){
						$num =  $num+1;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.''.$num,$movs_msvalue->Description);
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.''.$num,-$movs_msvalue->total);
						$inv_adj_total = $inv_adj_total + -$movs_msvalue->total;

					}else{
						$num =  $num+1;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.''.$num,$movs_msvalue->Description);
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.''.$num,$movs_msvalue->total);
						$inv_adj_total = $inv_adj_total + $movs_msvalue->total;

					}

				}
				$num =  $num+3;	

			$inv_total = $inv_adj_total;
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'20','Total Movements');	
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.'20', $inv_total);
			}
		
				$lettercol1 = $this->increment($lettercol1, 4);
				$lettercol2 = $this->increment($lettercol2, 4);
				$lettercol3 = $this->increment($lettercol3, 4);
			}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($file_dir."/movements.xlsx");

	}


	public function generate_file(){
		$file_dir =  realpath(dirname(__FILE__). '/../../report');
		$this->load->library('excel');
		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('Override');

		$db = $this->db_con->gen_rep_database();

		foreach ($db as $i => $dbdetails) {
			$ms_db_133 = $dbdetails->ms_db_133;
			$ms_db = $dbdetails->ms_db;
			$ms_name = $dbdetails->branch_name;

			if($i == 0){
				$num =  $i+3;
			}else{
				$num =  $num+2;
			}

			$num1 =  $num+1;

			$objPHPExcel->setActiveSheetIndex(0)
				    ->setCellValue('A'.$num1, $ms_db_133)
				    ->setCellValue('B'.$num1, $ms_name);

			$objPHPExcel->setActiveSheetIndex(0)
				    ->setCellValue('C'.$num,'2018')
				    ->setCellValue('C'.$num1,'2017');	

	   		$category_list = array('Override');
	        $letter_details = 'D';
	        $objPHPExcel->getActiveSheet()->setCellValue('D1','Override');
			foreach ($category_list as $key => $value) {

           	  $br = $objPHPExcel->getActiveSheet()->getCell('A'.$num1)->getValue();
           	  $year_2018 = $objPHPExcel->getActiveSheet()->getCell('C'.$num)->getValue();
           	  $year_2017 = $objPHPExcel->getActiveSheet()->getCell('C'.$num1)->getValue();

           	    $s = '08-01';
           	  	$e = '08-31';

           	  	$get_2018_override = $this->db_con->get_override($br, $year_2018 , $value,$s,$e);
           	  	$get_2017_override = $this->db_con->get_override($br, $year_2017 , $value,$s,$e);

	        	$objPHPExcel->getActiveSheet()->setCellValue('D'.$num,$get_2018_override[0]);
	        	$objPHPExcel->getActiveSheet()->setCellValue('D'.$num1,$get_2017_override[0]);

	           	 $letter_details++;
	           	 
				echo $value.'ok!'.PHP_EOL;		
	           }
				
			echo $ms_name.'ok!'.PHP_EOL;
			}	    

	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($file_dir."/override.xlsx");


	}
public function insert_esales_to_aria(){
	$dates = array('2018-10-01');
	//$dates = array('2018-07-01');

		foreach ($dates as $key => $date) {
			$datefrom = $date;
			$dateto = date("Y-m-t",strtotime($datefrom));

			echo $datefrom.'--->>'.PHP_EOL;

			$db = $this->db_con->gen_rep_database();

			foreach ($db as $sheet => $dbdetails) {
				$ms_db_133 = $dbdetails->ms_db_133;
				$ms_db = $dbdetails->ms_db;
				$aria_db = $dbdetails->aria_db;
				$branch_name = $dbdetails->branch_name;

				$esales = $this->db_con->get_total_esales($ms_db_133,$datefrom,$dateto);
				$month = $esales->MonthNo;
				$year = $esales->YearNo;
				$salesv = $esales->mNetVATSales;
				$salesnv = $esales->mNONVATSales;
				$saleszv = $esales->ZeroVatSles;
				$this->db_con->insert_aria_to_esales($aria_db,$month,$year,$salesv,$salesnv,$saleszv);
				
				echo $branch_name.'ok!'.PHP_EOL;
			}

		}

}

public function generate_esales_per_branch(){
		$file_dir =  realpath(dirname(__FILE__). '/../../report');
		$this->load->library('excel');
		$objPHPExcel = new PHPExcel();
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


		$db = $this->db_con->gen_rep_database("'TMAL'");	
		
		foreach ($db as $sheet => $dbdetails) {
			
			$ms_db_133 = $dbdetails->ms_db_133;
			$ms_db = $dbdetails->ms_db;
			$ms_name = $dbdetails->branch_name;
			$aria_db = $dbdetails->aria_db;

			$dates = array('2018-12-01');

			foreach ($dates as $sheet => $date) {

				$datefrom = $date;
				$dateto = date("Y-m-t",strtotime($datefrom));
				$month = date('m',strtotime($datefrom));

				$objPHPExcel->createSheet();
				$objPHPExcel->setActiveSheetIndex($sheet);
          		$objPHPExcel->getActiveSheet()->setTitle($month);

				$esales = $this->db_con->get_esales($ms_db_133,$datefrom,$dateto);

				$objPHPExcel->getActiveSheet()->setCellValue("A1","TIN");
				$objPHPExcel->getActiveSheet()->setCellValue("B1","BranchNo");
				$objPHPExcel->getActiveSheet()->setCellValue("C1","Month");
				$objPHPExcel->getActiveSheet()->setCellValue("D1","Year");
				$objPHPExcel->getActiveSheet()->setCellValue("E1","Min");
				$objPHPExcel->getActiveSheet()->setCellValue("F1","Last OR");
				$objPHPExcel->getActiveSheet()->setCellValue("G1","Vat");
				$objPHPExcel->getActiveSheet()->setCellValue("H1","Zero Vat");
				$objPHPExcel->getActiveSheet()->setCellValue("I1","Non Vat");
				$objPHPExcel->getActiveSheet()->setCellValue("J1","SST");


				foreach ($esales as $key => $value) {

					if($key == 0){
						$num =  $key+2;
					}else{
						$num =  $num+1;
					}

					$num1 =  $num;

					echo 'key :'.$key.PHP_EOL;	

					$min = "'".$value->Min_Info;

					$objPHPExcel->getActiveSheet()->setCellValue("A".$num1,$value->Tin);
					$objPHPExcel->getActiveSheet()->setCellValue("B".$num1,$value->BranchNo);
					$objPHPExcel->getActiveSheet()->setCellValue("C".$num1,$value->MonthNo);
					$objPHPExcel->getActiveSheet()->setCellValue("D".$num1,$value->YearNo);
					$objPHPExcel->getActiveSheet()->setCellValue("E".$num1,$min);
					$objPHPExcel->getActiveSheet()->setCellValue("F".$num1,$value->LastORNo);
					$objPHPExcel->getActiveSheet()->setCellValue("G".$num1,$value->mNetVATSales);
					$objPHPExcel->getActiveSheet()->setCellValue("H".$num1,$value->ZeroVatSles);
					$objPHPExcel->getActiveSheet()->setCellValue("I".$num1,$value->mNONVATSales);
					$objPHPExcel->getActiveSheet()->setCellValue("J".$num1,$value->SST);

					
				}
				echo 'from :'.$datefrom.'to :'.$dateto.'~~'.$ms_name.'ok!'.PHP_EOL;	

				$esales = $this->db_con->get_total_esales($ms_db_133,$datefrom,$dateto);
				$month = $esales->MonthNo;
				$year = $esales->YearNo;
				$salesv = $esales->mNetVATSales;
				$salesnv = $esales->mNONVATSales;
				$saleszv = $esales->ZeroVatSles;
				$this->db_con->insert_aria_to_esales($aria_db,$month,$year,$salesv,$salesnv,$saleszv);

			}


			$objWriter->save($file_dir."/"."ESALES -".$ms_name.".xlsx");

			echo $ms_name.'~ok!~'.PHP_EOL;	


		}

}

public function generate_esales(){

		$file_dir =  realpath(dirname(__FILE__). '/../../report');
		$this->load->library('excel');
		$objPHPExcel = new PHPExcel();
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$dates = array('2019-01-01');
		$objPHPExcel->createSheet();
		foreach ($dates as $key => $date) {
			$datefrom = $date;
			$dateto = date("Y-m-t",strtotime($datefrom));
			$batch_db = '';
			$db = $this->db_con->gen_rep_database($batch_db);

			foreach ($db as $sheet => $dbdetails) {

				$ms_db_133 = $dbdetails->ms_db_133;
				$ms_db = $dbdetails->ms_db;
				$ms_name = $dbdetails->branch_name;

				$objPHPExcel->createSheet();
				$objPHPExcel->setActiveSheetIndex($sheet);
          		$objPHPExcel->getActiveSheet()->setTitle($ms_name);

				$esales = $this->db_con->get_esales($ms_db_133,$datefrom,$dateto);

				$objPHPExcel->getActiveSheet()->setCellValue("A1","TIN");
				$objPHPExcel->getActiveSheet()->setCellValue("B1","BranchNo");
				$objPHPExcel->getActiveSheet()->setCellValue("C1","Month");
				$objPHPExcel->getActiveSheet()->setCellValue("D1","Year");
				$objPHPExcel->getActiveSheet()->setCellValue("E1","Min");
				$objPHPExcel->getActiveSheet()->setCellValue("F1","Last OR");
				$objPHPExcel->getActiveSheet()->setCellValue("G1","Vat");
				$objPHPExcel->getActiveSheet()->setCellValue("H1","Zero Vat");
				$objPHPExcel->getActiveSheet()->setCellValue("I1","Non Vat");
				$objPHPExcel->getActiveSheet()->setCellValue("J1","SST");

				foreach ($esales as $key => $value) {

				if($key == 0){
					$num =  $key+2;
				}else{
					$num =  $num+1;
				}

				$num1 =  $num;

				echo 'key :'.$key.PHP_EOL;	

				$min = "'".$value->Min_Info;

				$objPHPExcel->getActiveSheet()->setCellValue("A".$num1,$value->Tin);
				$objPHPExcel->getActiveSheet()->setCellValue("B".$num1,$value->BranchNo);
				$objPHPExcel->getActiveSheet()->setCellValue("C".$num1,$value->MonthNo);
				$objPHPExcel->getActiveSheet()->setCellValue("D".$num1,$value->YearNo);
				$objPHPExcel->getActiveSheet()->setCellValue("E".$num1,$min);
				$objPHPExcel->getActiveSheet()->setCellValue("F".$num1,$value->LastORNo);
				$objPHPExcel->getActiveSheet()->setCellValue("G".$num1,$value->mNetVATSales);
				$objPHPExcel->getActiveSheet()->setCellValue("H".$num1,$value->ZeroVatSles);
				$objPHPExcel->getActiveSheet()->setCellValue("I".$num1,$value->mNONVATSales);
				$objPHPExcel->getActiveSheet()->setCellValue("J".$num1,$value->SST);

					
				}


				echo 'from :'.$datefrom.PHP_EOL;	
				echo 'to :'.$dateto.PHP_EOL;	
				echo $ms_name.'ok!'.PHP_EOL;	

			}

		    $objWriter->save($file_dir."/"."ESALES -".$datefrom."~".$dateto.".xlsx");

		    echo 'from :'.$datefrom.'- to :'.$dateto.' ok!'.PHP_EOL;	
		}


}


public function generate_gp_file($batch){

	$file_dir =  realpath(dirname(__FILE__). '/../../report');
		$this->load->library('excel');
		$objPHPExcel = new PHPExcel();

		$batch_db = '';

		switch ($batch) {
		    case 'batch1':
		       $batch_db = "'TALA,'TAQU','TCAI'";
		        break;
		    case 'batch2':
		       $batch_db = "'TBAG','TBGB','TCAT','TIMU'";
		        break;
		    case 'batch3':
		        $batch_db = "'TCAM','TCOM','TGAG','TGVL'";
		        break;
		    case 'batch4':
		       $batch_db = "'TLAP','TMAL','TMALR'";
		        break;
	        case 'batch5':
	       		$batch_db = "'TMOL','TNAV','TNOV','TPAT'";
	        break;
	        case 'batch6':
	       		$batch_db = "'TPUN','TSAN','TVAL','TTON'"; 
	        break;
	         case 'batch7':
	       		$batch_db = "'TAMA'";
	        break;
		    default:

		    // $batch_db = "'TCAM','TAMA','TIMU','TALA','TAQU','TNOV','TTON','TCAI','TNAV','TLAP','TGAG','TBAG','TBGB','TCAT','TCOM','TGVL','TMAL','TMOL','TPAT','TPUN','TSAN','TVAL','TMONT'";
		   $batch_db ="'TCAT','TCAI','TCOM'";
		}


		//'TAMA', 'TCAM',
	$dates = array('01/1/2019');
	//$dates = array('8/1/2018');

		foreach ($dates as $key => $date) {

			$objPHPExcel->disconnectWorksheets();
			$objPHPExcel->createSheet();


				$db = $this->db_con->gen_rep_database($batch_db);

				foreach ($db as $i => $dbdetails) {
					
					$ms_db_133 = $dbdetails->ms_db_133;
					$ms_db = $dbdetails->ms_db;
					$ms_db_my = $dbdetails->ms_db.'_MY';
						$ms_name = $dbdetails->branch_name;
					$aria_db = $dbdetails->aria_db;

					echo $ms_name.'Branch'.PHP_EOL;

					$objPHPExcel->createSheet();
					$objPHPExcel->setActiveSheetIndex($i);
					$objPHPExcel->getActiveSheet()->setTitle($ms_name);
				
					### 2017 ###	
					$startdate2017_ =  date("Y-m-d",strtotime('-1 year',strtotime($date))); 
					$startdate2017 =  date("Y-m-d",strtotime('-1 year',strtotime($date)));
					$enddate2017 = date("Y-m-t",strtotime($startdate2017));

					$objPHPExcel->getActiveSheet()->setCellValue('A1','Branch : ');
					$objPHPExcel->getActiveSheet()->setCellValue('B1',$ms_name);
					$objPHPExcel->getActiveSheet()->setCellValue('A2','Period : ');
					$objPHPExcel->getActiveSheet()->setCellValue('B2',$startdate2017.'~'.$enddate2017);

					$objPHPExcel->getActiveSheet()->setCellValue('A4', 'Account');
					$objPHPExcel->getActiveSheet()->setCellValue('B4','Account Name');
					$objPHPExcel->getActiveSheet()->setCellValue('C4','Amount');
					$objPHPExcel->getActiveSheet()->setCellValue('D4','Percentage');

					#####FORMULA 1######
					
					$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Formula #1');

					$res = $this->db_con->get_finished_sales_for_formula_1_conso_ms($ms_db, $startdate2017,$enddate2017);
					//old in ms
					// $sales = $res[1];
					// $CostofSales = $res[0];
					// $sukipoints = $this->db_con->get_sukipoints($ms_db, $startdate2017,$enddate2017);
					// $GrossProfit = ($res[1] - $res[0])+$sukipoints;
					if($res){
						$sales = $res->total_sales;
						$CostofSales = $res->total_cost;
						$sukipoints =  $res->sukipoints;
						$GrossProfit = ($sales -$CostofSales)+$sukipoints;
						$GPlessSK = $GrossProfit - $sukipoints;
					}else{

						$sales = 0;
						$CostofSales = 0;
						$sukipoints =  0;
						$GrossProfit = 0;
						$GPlessSK = 0;

					}
					

					$objPHPExcel->getActiveSheet()->setCellValue('B6', 'Sales :');
					$objPHPExcel->getActiveSheet()->setCellValue('C6', $sales);
					$objPHPExcel->getActiveSheet()->setCellValue('B7', 'Suki Points :');
					$objPHPExcel->getActiveSheet()->setCellValue('C7', $sukipoints);
					$objPHPExcel->getActiveSheet()->setCellValue('B8', 'Cost of Sales :');
					$objPHPExcel->getActiveSheet()->setCellValue('C8', $CostofSales);
					$objPHPExcel->getActiveSheet()->setCellValue('B9', 'Gross Profit :');
					$objPHPExcel->getActiveSheet()->setCellValue('C9', $GrossProfit);
					$objPHPExcel->getActiveSheet()->setCellValue('B10', 'Gross Profit  - Suki Points :');
					$objPHPExcel->getActiveSheet()->setCellValue('C10', $GPlessSK );

					#####FORMULA 2######


					#####REVENUES######
					$objPHPExcel->getActiveSheet()->setCellValue('A13','A.Revenues' );
					$objPHPExcel->getActiveSheet()->setCellValue('A14','Revenue Accounts' );

					$accounts = $this->db_con->get_account_type($aria_db,array(6));

					$rev_account_total = 0;

					foreach ($accounts as $key => $value) {
						if($key == 0){
							$num =  $key+15;
						}

					$gl_sum = $this->db_con->get_gl_trans_from_to($aria_db,$value->account_code,$startdate2017,$enddate2017);
						if(abs($gl_sum) > 0){
							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,$value->account_code);
							$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$value->account_name);
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,abs($gl_sum));
							$rev_account_total = $rev_account_total + abs($gl_sum);
						}
						
					}	
							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Total Gross Sales');
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,$rev_account_total);

							$suki_points_gl_sum = $this->db_con->get_gl_trans_from_to($aria_db,'4900',$startdate2017,$enddate2017);
							$num =  $num+2;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'4900');
							$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,'Sales - Suki Points');
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,$suki_points_gl_sum);
							$num =  $num+2;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Total Revenues');
							$total_rev = $rev_account_total + $suki_points_gl_sum;
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,$total_rev);

							#***# insert rev sales #***#

					##### Cost of Goods Sold######
					##### Purchases ######
							$num =  $num+3;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'B.Cost of Goods Sold');
							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Purchases');

							$purch_account_total = 0;
							$accounts1 = $this->db_con->get_account_type($aria_db,array(71));
							foreach ($accounts1 as $keys => $values) {
								if($key == 0){
									$num =  $keys+$num;
								}
							$gl_sum_purch = $this->db_con->get_gl_trans_from_to($aria_db,$values->account_code,$startdate2017,$enddate2017);
								if(abs($gl_sum_purch) > 0){
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,$values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$values->account_name);

									if($values->account_code == '5500'){
										$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,-abs($gl_sum_purch));
										$purch_account_total = $purch_account_total + $gl_sum_purch;
									}else{
										$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,abs($gl_sum_purch));
										$purch_account_total = $purch_account_total + abs($gl_sum_purch);
									}
								}

							}
							echo $purch_account_total.'$purch_account_total-----'.PHP_EOL;

							$movs = array('PSV','PS');
							$ms_res = $this->db_con->get_movements($ms_db,$movs,$startdate2017, $enddate2017);
							if($ms_res){

								foreach ($ms_res as $mskey => $msvalue) {
									if($mskey == 0){
										$num =  $mskey+$num;
									}
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$msvalue->Description);
									$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,abs($msvalue->total));
									$purch_account_total = $purch_account_total + $msvalue->total;

								}
								echo $purch_account_total.'$purch_account_total'.PHP_EOL;

							}


					##### Transfers ######

							$num =  $num+2;

							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Transfers');	


							$trans_account_total = 0;
							$remove_acc = array('570001','570002');
							$accounts1 = $this->db_con->get_account_type($aria_db,array(73),$remove_acc);

							foreach ($accounts1 as $keys => $values) {
								if($key == 0){
									$num =  $keys+$num;
								}
								$gl_sum_trans = $this->db_con->get_gl_trans_from_to($aria_db,$values->account_code,$startdate2017,$enddate2017);
								
								if(abs($gl_sum_trans) > 0){

									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,$values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$values->account_name);
									$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,abs($gl_sum_trans));
									$trans_account_total = $trans_account_total + abs($gl_sum_trans);
								}

							}



							$movs = array('STI','STO','SA2KI','SA2KO');
							$neg = array('STO','SA2KO');

							$ms_tr_res = $this->db_con->get_movements($ms_db,$movs,$startdate2017, $enddate2017);

							if($ms_tr_res){

								foreach ($ms_tr_res as $tr_mskey => $tr_msvalue) {
									if($tr_mskey == 0){
										$num =  $tr_mskey+$num;
									}

									if(in_array($tr_msvalue->movementcode, $neg)){
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$tr_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,-$tr_msvalue->total);
										$trans_account_total = $trans_account_total + -$tr_msvalue->total;

									}else{
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$tr_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,$tr_msvalue->total);
										$trans_account_total = $trans_account_total + $tr_msvalue->total;

									}
									

								}

								echo $trans_account_total.'$trans_account_total'.PHP_EOL;
							}



					##### Rebates & B.O. Allowance ######

							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Rebates & B.O. Allowance');

							$rebs_account_total = 0;
							$accounts1 = $this->db_con->get_account_type($aria_db,array(72));
							foreach ($accounts1 as $reb_keys => $reb_values) {
								if($key == 0){
									$num =  $reb_keys+$num;
								}
							$gl_sum_rebs = $this->db_con->get_gl_trans_from_to($aria_db,$reb_values->account_code,$startdate2017,$enddate2017);
								if(abs($gl_sum_purch) > 0){
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,$reb_values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$reb_values->account_name);
									$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,abs($gl_sum_rebs));
									$rebs_account_total = $rebs_account_total + abs($gl_sum_rebs);
								}

							}

							echo $rebs_account_total.'$rebs_account_total'.PHP_EOL;

					##### Inventory Adjustments ######

							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Inventory Adjustments');	
							$inv_account_total = 0;
							$accounts1 = $this->db_con->get_account_type($aria_db,array(74));
							foreach ($accounts1 as $inv_keys => $inv_values) {
								if($key == 0){
									$num =  $inv_keys+$num;
								}
							$gl_sum_inv = $this->db_con->get_gl_trans_from_to($aria_db,$inv_values->account_code,$startdate2017,$enddate2017);
								if(abs($gl_sum_inv) > 0){
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,$inv_values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$inv_values->account_name);
									$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,abs($gl_sum_inv));
									$inv_account_total = $inv_account_total + abs($gl_sum_inv);
								}

							}

							echo $inv_account_total.'$inv_account_total'.PHP_EOL;
					
							$num =  $num+2;
							$total_purchases = $purch_account_total+$trans_account_total+$rebs_account_total+$inv_account_total;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Total Purchases and Transfers');	
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,$total_purchases);
							
							##BEG INV###
							$num =  $num+2;
							$Beginning = $this->db_con->get_ms_inv($ms_db,$startdate2017);
							if($Beginning){
								$Beginning = $Beginning->beg;
							}else{
								$Beginning = 0;
							}
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Add: Beginning Inventory');	
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $Beginning);	
							echo $Beginning.'$Beginning'.PHP_EOL;
							

							##END INV###
							$num =  $num+3;
							$Ending = $this->db_con->get_ms_inv($ms_db,$startdate2017);
							if($Ending){
								$Ending = $Ending->end;
							}else{
								$Ending = 0;
							}
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Less: Ending Inventory');	
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $Ending);	
							echo $Ending.'$Ending'.PHP_EOL;

							##COST OF GOOD SOLD###
							
							$num =  $num+3;
							$costofgsold = ($Beginning+$total_purchases) - $Ending;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Cost of Goods Sold');	
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $costofgsold);

							$num =  $num+3;	

							##GROSS PROFIT###
							$grossprofit_ = $total_rev - $costofgsold;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Gross Profit');	
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $grossprofit_);

							#***# insert gp adjustment gross profit #***#

							##INV ADJ###
							$num =  $num+3;	
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Inventory Adjustments');	
							$inv_adj_total = 0;
							$exclude = array('D2BSR','SA2BO','PSV','PS','STI','STO','ITI','ITO','R2SSA');
							$negate = array('AIL','FDFB','IGNBO','IGNSA','PFBO','SW','NASA','STNA','NGBO','SWNA');
							$movs = null;
							$movs_res = $this->db_con->get_movements($ms_db,$movs,$startdate2017, $enddate2017);

							if($movs_res){
								foreach ($movs_res as $movs_mskey => $movs_msvalue) {
									if($movs_mskey == 0){
										$num =  $movs_mskey+$num;
									}

									if(in_array($movs_msvalue->movementcode, $exclude))
										continue;
									if(in_array($movs_msvalue->movementcode, $negate)){
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$movs_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,-$movs_msvalue->total);
										$inv_adj_total = $inv_adj_total + -$movs_msvalue->total;

									}else{
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('B'.$num,$movs_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,$movs_msvalue->total);
										$inv_adj_total = $inv_adj_total + $movs_msvalue->total;

									}
								}	
							}


							$num =  $num+2;	

							##INV TOTAL###
							$inv_total = $inv_adj_total;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Total Movements');	
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $inv_total);

							##GP BEFORE ADJ###
							$num =  $num+3;	
							$gp_b_adj = $grossprofit_ - $inv_total;
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$num,'Gross Profit Before adjustment ');	
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $gp_b_adj);




							#***# insert gp before adjustment gross profit #***#

							$details_ = array('month_'=>date('m',strtotime($startdate2017)),
											'year_'=>date('Y',strtotime($startdate2017)),
											'sales_revunue'=>$total_rev,
											'inventory_adjustment'=>$inv_total,
											'before_adjustment'=>$gp_b_adj,
											'database_'=>$ms_db
											);



							$res = $this->db_con->insert_consolidated_gp($details_);

					

					##### 2018 #### 		

					$startdate2018_ = $date;
					$startdate2018 = date("Y-m-d",strtotime($date));
					$enddate2018 = date("Y-m-t",strtotime($startdate2018));


					$objPHPExcel->getActiveSheet()->setCellValue('H1','Branch : ');
					$objPHPExcel->getActiveSheet()->setCellValue('I1',$ms_name);
					$objPHPExcel->getActiveSheet()->setCellValue('H2','Period : ');
					$objPHPExcel->getActiveSheet()->setCellValue('I2',$startdate2018.'~'.$enddate2018);

					$objPHPExcel->getActiveSheet()->setCellValue('H4', 'Account');
					$objPHPExcel->getActiveSheet()->setCellValue('I4','Account Name');
					$objPHPExcel->getActiveSheet()->setCellValue('J4','Amount');
					$objPHPExcel->getActiveSheet()->setCellValue('K4','Percentage');

					#####FORMULA 1######
					
					$objPHPExcel->getActiveSheet()->setCellValue('H5', 'Formula #1');

					$res = $this->db_con->get_finished_sales_for_formula_1_conso_ms($ms_db, $startdate2018,$enddate2018);
					
					/*old in ms
					$res = $this->db_con->get_finished_sales_for_formula_1($ms_db, $startdate2018,$enddate2018);
					// $sales = $res[1];
					// $CostofSales = $res[0];
					// $sukipoints = $this->db_con->get_sukipoints($ms_db, $startdate2017,$enddate2017);
					// $GrossProfit = ($res[1] - $res[0])+$sukipoints;
					*/
					if($res){
						$sales = $res->total_sales;
						$CostofSales = $res->total_cost;
						$sukipoints =  $res->sukipoints;
						$GrossProfit = ($sales -$CostofSales)+$sukipoints;
						$GPlessSK = $GrossProfit - $sukipoints;
					}else{
						$sales = 0;
						$CostofSales = 0;
						$sukipoints =  0;
						$GrossProfit = 0;
						$GPlessSK = 0;
					}
					

					$objPHPExcel->getActiveSheet()->setCellValue('I6', 'Sales :');
					$objPHPExcel->getActiveSheet()->setCellValue('J6', $sales);
					$objPHPExcel->getActiveSheet()->setCellValue('I7', 'Suki Points :');
					$objPHPExcel->getActiveSheet()->setCellValue('J7', $sukipoints);
					$objPHPExcel->getActiveSheet()->setCellValue('I8', 'Cost of Sales :');
					$objPHPExcel->getActiveSheet()->setCellValue('J8', $CostofSales);
					$objPHPExcel->getActiveSheet()->setCellValue('I9', 'Gross Profit :');
					$objPHPExcel->getActiveSheet()->setCellValue('J9', $GrossProfit);
					$objPHPExcel->getActiveSheet()->setCellValue('I10', 'Gross Profit  - Suki Points :');
					$objPHPExcel->getActiveSheet()->setCellValue('J10', $GPlessSK );

					#####FORMULA 2######


					#####REVENUES######
					$objPHPExcel->getActiveSheet()->setCellValue('H13','A.Revenues' );
					$objPHPExcel->getActiveSheet()->setCellValue('H14','Revenue Accounts' );

					$accounts = $this->db_con->get_account_type($aria_db,array(6));

					$rev_account_total = 0;

					foreach ($accounts as $key => $value) {
						if($key == 0){
							$num =  $key+15;
						}

					$gl_sum = $this->db_con->get_gl_trans_from_to($aria_db,$value->account_code,$startdate2018,$enddate2018);
						if(abs($gl_sum) > 0){
							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,$value->account_code);
							$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$value->account_name);
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,abs($gl_sum));
							$rev_account_total = $rev_account_total + abs($gl_sum);
						}
						
					}	
							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Total Gross Sales');
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,$rev_account_total);

							$suki_points_gl_sum = $this->db_con->get_gl_trans_from_to($aria_db,'4900',$startdate2018,$enddate2018);
							$num =  $num+2;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'4900');
							$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,'Sales - Suki Points');
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,$suki_points_gl_sum);
							$num =  $num+2;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Total Revenues');
							$total_rev = $rev_account_total + $suki_points_gl_sum;
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,$total_rev);

							#***# insert rev sales #***#

					##### Cost of Goods Sold######

					##### Purchases ######
							$num =  $num+3;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'B.Cost of Goods Sold');
							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Purchases');	

							$purch_account_total = 0;
							$accounts1 = $this->db_con->get_account_type($aria_db,array(71));
							foreach ($accounts1 as $keys => $values) {
								if($key == 0){
									$num =  $keys+$num;
								}
							$gl_sum_purch = $this->db_con->get_gl_trans_from_to($aria_db,$values->account_code,$startdate2018,$enddate2018);
								if(abs($gl_sum_purch) > 0){
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,$values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$values->account_name);

									if($values->account_code == '5500'){
										$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,-abs($gl_sum_purch));
										$purch_account_total = $purch_account_total + $gl_sum_purch;
									}else{
										$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,abs($gl_sum_purch));
										$purch_account_total = $purch_account_total + abs($gl_sum_purch);
									}
								}

							}
							echo $purch_account_total.'$purch_account_total-----'.PHP_EOL;

							$movs = array('PSV','PS');
							$ms_res = $this->db_con->get_movements($ms_db,$movs,$startdate2018, $enddate2018);
							if($ms_res){

								foreach ($ms_res as $mskey => $msvalue) {
									if($mskey == 0){
										$num =  $mskey+$num;
									}
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$msvalue->Description);
									$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,abs($msvalue->total));
									$purch_account_total = $purch_account_total + $msvalue->total;

								}
								echo $purch_account_total.'$purch_account_total'.PHP_EOL;
							}

					##### Transfers ######

							$num =  $num+2;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Transfers');	


							$trans_account_total = 0;
							$remove_acc = array('570001','570002');
							$accounts1 = $this->db_con->get_account_type($aria_db,array(73),$remove_acc);
							foreach ($accounts1 as $keys => $values) {
								if($key == 0){
									$num =  $keys+$num;
								}
							$gl_sum_trans = $this->db_con->get_gl_trans_from_to($aria_db,$values->account_code,$startdate2018,$enddate2018);
								if(abs($gl_sum_trans) > 0){
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,$values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$values->account_name);
									$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,abs($gl_sum_trans));
									$trans_account_total = $trans_account_total + abs($gl_sum_trans);
								}

							}

							$movs = array('STI','STO','SA2KI','SA2KO');
							$neg = array('STO','SA2KO');
							$ms_tr_res = $this->db_con->get_movements($ms_db,$movs,$startdate2018, $enddate2018);
							
							if($ms_tr_res){

								foreach ($ms_tr_res as $tr_mskey => $tr_msvalue) {
									if($tr_mskey == 0){
										$num =  $tr_mskey+$num;
									}

									if(in_array($tr_msvalue->movementcode, $neg)){
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$tr_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,-$tr_msvalue->total);
										$trans_account_total = $trans_account_total + -$tr_msvalue->total;

									}else{
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$tr_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,$tr_msvalue->total);
										$trans_account_total = $trans_account_total + $tr_msvalue->total;

									}
									
								}

								echo $trans_account_total.'$trans_account_total'.PHP_EOL;

							}


					##### Rebates & B.O. Allowance ######

							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Rebates & B.O. Allowance');

							$rebs_account_total = 0;
							$accounts1 = $this->db_con->get_account_type($aria_db,array(72));
							foreach ($accounts1 as $reb_keys => $reb_values) {
								if($key == 0){
									$num =  $reb_keys+$num;
								}
							$gl_sum_rebs = $this->db_con->get_gl_trans_from_to($aria_db,$reb_values->account_code,$startdate2018,$enddate2018);
								if(abs($gl_sum_purch) > 0){
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,$reb_values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$reb_values->account_name);
									$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,abs($gl_sum_rebs));
									$rebs_account_total = $rebs_account_total + abs($gl_sum_rebs);
								}

							}

							echo $rebs_account_total.'$rebs_account_total'.PHP_EOL;

					##### Inventory Adjustments ######

							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Inventory Adjustments');	
							$inv_account_total = 0;
							$accounts1 = $this->db_con->get_account_type($aria_db,array(74));
							foreach ($accounts1 as $inv_keys => $inv_values) {
								if($key == 0){
									$num =  $inv_keys+$num;
								}
								
								$gl_sum_inv = $this->db_con->get_gl_trans_from_to($aria_db,$inv_values->account_code,$startdate2018,$enddate2018);
								
								if(abs($gl_sum_inv) > 0){
									$num =  $num+1;
									$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,$inv_values->account_code);
									$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$inv_values->account_name);
									$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,abs($gl_sum_inv));
									$inv_account_total = $inv_account_total + abs($gl_sum_inv);
								}

							}

							echo $inv_account_total.'$inv_account_total'.PHP_EOL;
					
							$num =  $num+2;
							$total_purchases = $purch_account_total+$trans_account_total+$rebs_account_total+$inv_account_total;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Total Purchases and Transfers');	
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,$total_purchases);	

							
							##BEG INV###
							$num =  $num+2;
							$Beginning = $this->db_con->get_ms_inv($ms_db,$startdate2018);
							if($Beginning){
								$Beginning = $Beginning->beg;
							}else{
								$Beginning = 0;
							}
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Add: Beginning Inventory');	
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $Beginning);	
							echo $Beginning.'$Beginning'.PHP_EOL;
							

							##END INV###
							$num =  $num+3;
							//$enddate2018_ = date('Y-m-d', strtotime($enddate2018. ' + 1 days'));
							$Ending = $this->db_con->get_ms_inv($ms_db,$startdate2018);
							if($Ending){
								$Ending = $Ending->end;
							}else{
								$Ending = 0;
							}
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Less: Ending Inventory');	
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $Ending);	
							echo $Ending.'$Ending'.PHP_EOL;


							##COST OF GOOD SOLD###
							
							$num =  $num+3;
							$costofgsold = ($Beginning+$total_purchases) - $Ending;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Cost of Goods Sold');	
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $costofgsold);

							$num =  $num+3;	

							##GROSS PROFIT###
							$grossprofit_ = $total_rev - $costofgsold;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Gross Profit');	
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $grossprofit_);

							#***# insert gp adjustment gross profit #***#

							##INV ADJ###
							$num =  $num+3;	
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Inventory Adjustments');	
							$inv_adj_total = 0;
							$exclude = array('D2BSR','SA2BO','PSV','PS','STI','STO','ITI','ITO','R2SSA');
							$negate = array('AIL','FDFB','IGNBO','IGNSA','PFBO','SW','NASA','STNA','NGBO','SWNA');
							$movs = null;
							$movs_res = $this->db_con->get_movements($ms_db,$movs,$startdate2018, $enddate2018);

							if($movs_res){
								foreach ($movs_res as $movs_mskey => $movs_msvalue) {
									if($movs_mskey == 0){
										$num =  $movs_mskey+$num;
									}

									if(in_array($movs_msvalue->movementcode, $exclude))
										continue;
									if(in_array($movs_msvalue->movementcode, $negate)){
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$movs_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,-$movs_msvalue->total);
										$inv_adj_total = $inv_adj_total + -$movs_msvalue->total;

									}else{
										$num =  $num+1;
										$objPHPExcel->getActiveSheet()->setCellValue('I'.$num,$movs_msvalue->Description);
										$objPHPExcel->getActiveSheet()->setCellValue('J'.$num,$movs_msvalue->total);
										$inv_adj_total = $inv_adj_total + $movs_msvalue->total;

									}
								}

							}


							$num =  $num+2;	

							##INV TOTAL###
							$inv_total = $inv_adj_total;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Total Movements');
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $inv_total);

							##GP BEFORE ADJ###
							$num =  $num+3;	
							$gp_b_adj = $grossprofit_ - $inv_total;
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$num,'Gross Profit Before adjustment ');
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$num, $gp_b_adj);

							#***# insert gp before adjustment gross profit #***#


							$details_ = array('month_'=>date('m',strtotime($startdate2018)),
											'year_'=>date('Y',strtotime($startdate2018)),
											'sales_revunue'=>$total_rev,
											'inventory_adjustment'=>$inv_total,
											'before_adjustment'=>$gp_b_adj,
											'database_'=>$ms_db);


							$res = $this->db_con->insert_consolidated_gp($details_);



					echo $ms_name.'~'.$i.PHP_EOL;	
				}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($file_dir."/GP_".date('Y-m-d',strtotime($date)).".xlsx");
	}

}

public function increment($val, $increment = 5)
{
    for ($i = 1; $i <= $increment; $i++) {
        $val++;
    }

    return $val;
}




public function decrement($string)
{
	$last = substr($string, -1);
	    $part=substr($string, 0, -1);
	    if(strtoupper($last)=='A'){
	        $l = substr($part, -1);
	        if($l=='A'){
	            return substr($part, 0, -1)."Z";
	        }
	        return $part.chr(ord($l)-1);
	    }else{
	        return $part.chr(ord($last)-1);
	    }
}


public function generate_conso_movements(){

	$file_dir =  realpath(dirname(__FILE__). '/../../report');
	$this->load->library('excel');
	$objPHPExcel = new PHPExcel();


	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save($file_dir."/movements.xlsx");

	


}


public function generate_opex_new(){

	$file_dir =  realpath(dirname(__FILE__). '/../../report');
	$this->load->library('excel');
	$objPHPExcel = new PHPExcel();
	
			$lettercol1 = 'A';
			$lettercol2 = 'B';
			$lettercol3 = 'C';
			$lettercol4 = 'D';
			
			$batch_db = false;
		$db = $this->db_con->gen_rep_database_retail($batch_db);
	
		foreach ($db as $i => $dbdetails) {

			$ms_name = $dbdetails->branch_name;
			$aria_db = $dbdetails->aria_db;
			$ms_db_133 = $dbdetails->ms_db_133;
			$ms_db = $dbdetails->ms_db;

			$last_supp_id = $supp_name = $last_gst = '';
			$counter = $supp_total_ = $ov_total = 0;

			echo 'BRANCH:'.$ms_name.PHP_EOL;

			$startdate =  '2018-01-01';
			$enddate = '2018-01-31';
			

			$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.'1', $ms_name); // col + 2
			$year  = date('Y',strtotime($startdate));
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.'2', $year);

			$firstday =  date('m',strtotime($startdate));
			$lastday =  date('t',strtotime($startdate));

			$ms_db = $this->db_con->gen_rep_database_server($ms_db_133);
			$get_conso = $this->db_con->get_data($ms_db,$year,'Conso',$firstday,$lastday,$ms_db);
			if($get_conso){
				$sales = $get_conso[0];
				$after_adj = $get_conso[5];
			}else{
				$sales = 0;
				$after_adj =  0;	
			}
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.'3', $sales);
			$objPHPExcel->getActiveSheet()->getStyle($lettercol4.'3')->getNumberFormat()->setFormatCode('#,##0.00');
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.'4', $after_adj);
			$objPHPExcel->getActiveSheet()->getStyle($lettercol4.'4')->getNumberFormat()->setFormatCode('#,##0.00');

			$exp_total = 0;
			$exp_g_total = 0;
			$expense_res = $this->db_con->get_exp($aria_db,$startdate,$enddate);
			foreach ($expense_res as $key => $value) {


							if($key == 0){
								$num =  $key+6;
							}else{
								$num =  $num+1;
							}


						if($last_supp_id != $value->expense_id ){

							if($last_supp_id != ''){
								
								if($i == 0){
									$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, 'TOTAL:');
								}
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_total);
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');
								$num =  $num+2;

								$exp_total = 0;

							}
							if($i == 0){
								$objPHPExcel->getActiveSheet()->setCellValue('A'.$num, $value->expense_desc);	
							}
							
						}

						$exp_total = $exp_total+$value->mnt;
						$exp_g_total = $exp_g_total+$value->mnt;

						if($i == 0){
							$objPHPExcel->getActiveSheet()->setCellValue('B'.$num, $value->acc_code);
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, $value->account_name);	
						}

						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $value->mnt);
						$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');

						$last_supp_id  = $value->expense_id;

					}

						$num =  $num+2;
						if($i == 0){
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, 'TOTAL:');
							
						}
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_total);
						$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');
						$num =  $num+2;

							if($i == 0){
								$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, 'BRANCH OPEX:');
								//$num =  $num+1;
							}
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_g_total);
							$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');

						$num =  $num+2;
							if($i == 0){
								$objPHPExcel->getActiveSheet()->setCellValue('C'.$num, 'HEAD OFFICE EXPENSE:');
							}

							$total_branch_sales = $this->db_con->get_data('total',$year,'Conso',$firstday,$lastday,$ms_db);
							$branch_sales = $get_conso[0];
							$branch_sales_percentage = (($branch_sales/$total_branch_sales[0]));
							$total_ho_exp = $this->db_con->get_exp_retail($aria_db,$startdate,$enddate);
							$ho_branch_part_exp = $branch_sales_percentage * $total_ho_exp->amount;

							// echo $total_branch_sales[0].'TOTAL SALES'.PHP_EOL;
							// echo $branch_sales.'BRANCH SALES'.PHP_EOL;
							// echo $branch_sales_percentage.'BRANCH SALES PERCENT'.PHP_EOL;
							// echo $total_ho_exp->amount.'RETAIL EXP'.PHP_EOL;
							// echo $ho_branch_part_exp.'BRANCH HO EXP PART'.PHP_EOL;

							$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $ho_branch_part_exp);
							$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');

							
						$num =  $num+2;
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$num,'OTHER INCOME:');

						$otherincome_total = 0;
						$accounts1 = $this->db_con->get_account_type($aria_db,array(4020));

						foreach ($accounts1 as $keys => $values) {
								if($key == 0){
									$num =  $keys+$num;
								}
								$gl_other_purch = $this->db_con->get_gl_trans_from_to($aria_db,$values->account_code,$startdate,$enddate);

								$num =  $num+1;

								$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.$num,$values->account_code);
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num,$values->account_name);
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num,abs($gl_other_purch));
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');
								$otherincome_total = $otherincome_total + abs($gl_other_purch);

						}
								$num =  $num+2;
							if($i == 0){
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num, 'OTHER INCOME TOTAL:');
							}
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $otherincome_total);
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');

							
								$num =  $num+2;
								$accounts ='23001606,2470,2471,2472,2473,2474,2475,2476,2477,2478,2479,2480,2481,
											2482,2483,4020020,4020025,4020030,402005,4020050,4020051,4020052,4020060,4020061';
								$pf_balance = $this->db_con->get_gl_trans_from_to_array_r($aria_db,$startdate,$enddate,$accounts);

							if($i == 0){
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.$num, 'PROMOFUND:');	
							}
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, abs($pf_balance));
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');



						$lettercol4 = $this->increment($lettercol4, 2);

			############2018############


			$last_supp_id = $supp_name = $last_gst = '';
			$counter = $supp_total_ = $ov_total = 0;

			$startdate =  '2019-01-01';
			$enddate = '2019-01-31';

			$expense_res = $this->db_con->get_exp($aria_db,$startdate,$enddate);

			$exp_total = 0;
			$exp_g_total = 0;
			$num = 0;
			$year  = date('Y',strtotime($startdate));
			$firstday =  date('m',strtotime($startdate));
			$lastday =  date('t',strtotime($startdate));

			$ms_db = $this->db_con->gen_rep_database_server($ms_db_133);
			$get_conso = $this->db_con->get_data($ms_db,$year,'Conso',$firstday,$lastday,$ms_db);
			if($get_conso){
				$sales = $get_conso[0];
				$after_adj = $get_conso[5];
			}else{
				$sales = 0;
				$after_adj =  0;	
			}
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.'3', $sales);
			$objPHPExcel->getActiveSheet()->getStyle($lettercol4.'3')->getNumberFormat()->setFormatCode('#,##0.00');
			$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.'4', $after_adj);
			$objPHPExcel->getActiveSheet()->getStyle($lettercol4.'4')->getNumberFormat()->setFormatCode('#,##0.00');

			$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.'2', $year);
			
			foreach ($expense_res as $key => $value) {

							if($key == 0){
								$num =  $key+6;
							}else{
								$num =  $num+1;
							}


						if($last_supp_id != $value->expense_id ){

							if($last_supp_id != ''){
								
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_total);
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');
								$num =  $num+2;

								$exp_total = 0;

							}
							
						}

						$exp_total = $exp_total+$value->mnt;
						$exp_g_total = $exp_g_total+$value->mnt;

						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $value->mnt);

						$last_supp_id  = $value->expense_id;

					}

						$num =  $num+2;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_total);
						$num =  $num+2;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_g_total);

						$num =  $num+2;

						$total_branch_sales = $this->db_con->get_data('total',$year,'Conso',$firstday,$lastday,$ms_db);
						$branch_sales = $get_conso[0];
						$branch_sales_percentage = (($branch_sales/$total_branch_sales[0]));
						$total_ho_exp = $this->db_con->get_exp_retail($aria_db,$startdate,$enddate);
						$ho_branch_part_exp = $branch_sales_percentage * $total_ho_exp->amount;

						// echo $total_branch_sales[0].'TOTAL SALES'.PHP_EOL;
						// echo $branch_sales.'BRANCH SALES'.PHP_EOL;
						// echo $branch_sales_percentage.'BRANCH SALES PERCENT'.PHP_EOL;
						// echo $total_ho_exp->amount.'RETAIL EXP'.PHP_EOL;
						// echo $ho_branch_part_exp.'BRANCH HO EXP PART'.PHP_EOL;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $ho_branch_part_exp);
						$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');

					    $num =  $num+2;
						$otherincome_total = 0;
						$accounts1 = $this->db_con->get_account_type($aria_db,array(4020));

						foreach ($accounts1 as $keys => $values) {
								if($key == 0){
									$num =  $keys+$num;
								}
								$gl_other_purch = $this->db_con->get_gl_trans_from_to($aria_db,$values->account_code,$startdate,$enddate);
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');

								$num =  $num+1;

								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num,abs($gl_other_purch));
								$otherincome_total = $otherincome_total + abs($gl_other_purch);

						}
							
								$num =  $num+2;
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $otherincome_total);
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');
								
								$num =  $num+2;
								$accounts ='23001606,2470,2471,2472,2473,2474,2475,2476,2477,2478,2479,2480,2481,
											2482,2483,4020020,4020025,4020030,402005,4020050,4020051,4020052,4020060,4020061';
								$pf_balance = $this->db_con->get_gl_trans_from_to_array_r($aria_db,$startdate,$enddate,$accounts);
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, abs($pf_balance));
								$objPHPExcel->getActiveSheet()->getStyle($lettercol4.$num)->getNumberFormat()->setFormatCode('#,##0.00');
							


					    $lettercol4 = $this->increment($lettercol4, 2);	
			}

						

	

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($file_dir."/OPEX_NEW_TEST.xlsx");



}



public function generate_opex(){

		$file_dir =  realpath(dirname(__FILE__). '/../../report');
		$this->load->library('excel');
		$objPHPExcel = new PHPExcel();


		$dates = array('2018-08-01','2017-08-01');

		foreach ($dates as $key => $date) {


				$startdate =  $date;
				$enddate = date("Y-m-t",strtotime($startdate));

				$objPHPExcel->createSheet();
				$objPHPExcel->setActiveSheetIndex($key);
				$objPHPExcel->getActiveSheet()->setTitle($date);

				$db = $this->db_con->gen_rep_database_retail();
				
				echo 'DATE:'.$date.PHP_EOL;

				$lettercol1 = 'A';
				$lettercol2 = 'B';
				$lettercol3 = 'C';
				$lettercol4 = 'D';

				foreach ($db as $i => $dbdetails) {

					$ms_name = $dbdetails->branch_name;
					$aria_db = $dbdetails->aria_db;

					$last_supp_id = $supp_name = $last_gst = '';
					$counter = $supp_total_ = $ov_total = 0;

					echo 'BRANCH:'.$ms_name.PHP_EOL;

					$expense_res = $this->db_con->get_exp($aria_db,$startdate,$enddate);

					$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'1','Branch : ');
					$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.'1',$ms_name);
					$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'2','Period : ');
					$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.'2',$startdate.'~'.$enddate);
					$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.'4', 'Account Code');
					$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.'4','Account Name');
					$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.'4','Amount');


					$exp_total = 0;
					$exp_g_total = 0;

					foreach ($expense_res as $key => $value) {

							if($key == 0){
								$num =  $key+5;
							}else{
								$num =  $num+1;
							}


						if($last_supp_id != $value->expense_id ){

							if($last_supp_id != ''){
								
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num, 'TOTAL:');
								$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_total);
								$num =  $num+2;

								$exp_total = 0;

							}

							$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.$num, $value->expense_desc);
							
						}

						$exp_total = $exp_total+$value->mnt;
						$exp_g_total = $exp_g_total+$value->mnt;

						$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.$num, $value->acc_code);
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num, $value->account_name);
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $value->mnt);

						$last_supp_id  = $value->expense_id;

					}

						$num =  $num+2;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num, 'TOTAL:');
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_total);
						$num =  $num+2;
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num, 'GRAND TOTAL:');
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $exp_g_total);


					$num =  $num+2;
					$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.$num,'OTHER INCOME:');
					$otherincome_total = 0;
					$accounts1 = $this->db_con->get_account_type($aria_db,array(4020));

					foreach ($accounts1 as $keys => $values) {
							if($key == 0){
								$num =  $keys+$num;
							}
							$gl_other_purch = $this->db_con->get_gl_trans_from_to($aria_db,$values->account_code,$startdate,$enddate);

							$num =  $num+1;
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol2.$num,$values->account_code);
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num,$values->account_name);
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num,abs($gl_other_purch));
							$otherincome_total = $otherincome_total + abs($gl_other_purch);

					}
							$num =  $num+2;
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol3.$num, 'OTHER INCOME TOTAL:');
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, $otherincome_total);

							$num =  $num+2;
							$accounts ='23001606,
										2470,
										2471,
										2472,
										2473,
										2474,
										2475,
										2476,
										2477,
										2478,
										2479,
										2480,
										2481,
										2482,
										2483,
										4020020,
										4020025,
										4020030,
										402005,
										4020050,
										4020051,
										4020052,
										4020060,
										4020061';
							$pf_balance = $this->db_con->get_gl_trans_from_to_array_r($aria_db,$startdate,$enddate,$accounts);
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.$num, 'PROMOFUND:');
							$objPHPExcel->getActiveSheet()->setCellValue($lettercol4.$num, abs($pf_balance));




						$lettercol1 = $this->increment($lettercol1, 5);
						$lettercol2 = $this->increment($lettercol2, 5);
						$lettercol3 = $this->increment($lettercol3, 5);
						$lettercol4 = $this->increment($lettercol4, 5);

				}

		}


		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($file_dir."/OPEX_NEW_9-18-18.xlsx");

}



public function generate_movs_(){

	$file_dir =  realpath(dirname(__FILE__). '/../../report');
		$this->load->library('excel');
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);

		$fisrtmonth = date('Y-m-01');
		$currentmonth = date('Y-m-d',strtotime('-1 month',strtotime(date('01-m-Y'))));

		$lettercol1 = 'A';

		$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'1','SALES ');
		$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'4','GAIN/LOSS');
		$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'5','DISPOSAL');
		$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'6','BUNDLING');
		$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'7', 'STOCKS WITHDRAWAL');
		$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'9','TOTAL');

		$lettercol1 = 'B';

		while($fisrtmonth != $currentmonth){

			$movments = array('SALES','GAIN/LOSS','FDFB','PFBO','SW','FDFB');

			//$db = $this->db_con->gen_rep_database_all();
			$firstday =  date('m',strtotime($fisrtmonth));
			$lastday =  date('m-t',strtotime($fisrtmonth));
			$year =  date('Y',strtotime($fisrtmonth));
			$year2017 =  date('Y',strtotime('-1 year',strtotime($year)));

			
			foreach ($movments as $key => $movs) {

				$total_movs = $this->db_con->get_movements_total_mov($movs,$firstday,$lastday);
				
				switch($movs){
					case 'SALES':
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'1',$total_movs);
						break;
					case 'GAIN/LOSS':
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'4',$total_movs);
						break;
					case 'FDFB':
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'5',$total_movs);
						break;
					case 'PFBO':
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'6',$total_movs);
						break;
					case 'SW':
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'7',$total_movs);
						break;
					case 'FDFB':
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'9',$total_movs);
						break;

					default:
						$objPHPExcel->getActiveSheet()->setCellValue($lettercol1.'5','');

				}



			}

			$lettercol1 = $this->increment($lettercol1, 4);


			$fisrtmonth = date('Y-m-d',strtotime('+1 month',strtotime($fisrtmonth)));


		}




	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($file_dir."/MOVS_9-18-18.xlsx");

}
public function generate_file_(){

		$file_dir =  realpath(dirname(__FILE__). '/../../report');
		$this->load->library('excel');
		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('Category');
		//$objPHPExcel->getActiveSheet()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);

		$batch_db = "'TCAM','TAMA','TIMU','TALA','TAQU','TBAG','TBGB','TCAI','TCAT','TCOM','TGAG','TGVL','TLAP','TMAL','TMOL','TNAV','TNOV','TPAT','TPUN','TSAN','TTON','TVAL','TMONT'";
		 // $batch_db = "'TMONT'";
		//$batch_db = "'ZTOTAL','ZORGANIC'";
		//$batch_db = "'TALA'";
		$db = $this->db_con->gen_rep_database($batch_db);

		foreach ($db as $i => $dbdetails) {

			$ms_db_133 = $dbdetails->ms_db_133;
			$ms_db = $dbdetails->ms_db;
			$ms_name = $dbdetails->branch_name;

			if($i == 0){
				$num =  $i+3;
			}else{
				$num =  $num+2;
			}


			$num1 =  $num+1;

	   			$fisrtmonth = '2019-01-01';
				$currentmonth = '2019-02-01';
				 //$currentmonth = date('Y-m-d',strtotime(date('01-m-Y')));

				//echo $fisrtmonth.'<>'.$currentmonth;

					while($fisrtmonth != $currentmonth){

						echo $fisrtmonth.'<><>'.PHP_EOL;
						// ,'Liquor','Cigarette','SALES & GP WITHOUT LIQUOR & CIGARETTE','Override','Depstore','Rice','Egg','Vegetable','Pork','Chicken','Beef'
	   					$category_list = array('Conso','Liquor','Cigarette','SALES & GP WITHOUT LIQUOR & CIGARETTE','Override','Depstore','Rice','Egg','Vegetable','Pork','Chicken','Beef','Fish');
	   					//$category_list = array('Conso');


						$objPHPExcel->setActiveSheetIndex(0)
					    ->setCellValue('C'.$num,$fisrtmonth);

					    $currentmonth_ = date('Y-m-d',strtotime('-1 month',strtotime($currentmonth)));

							
					    if($fisrtmonth == $currentmonth_){
							$objPHPExcel->setActiveSheetIndex(0)
						    ->setCellValue('A'.$num1, $ms_db_133)
						    ->setCellValue('B'.$num1, $ms_name);
					    	$objPHPExcel->setActiveSheetIndex(0)
					   		 ->setCellValue('C'.$num1,date('Y-m-d',strtotime('-1 year',strtotime($fisrtmonth))));
					   		 $nums = $num1 +1;
					   		 $objPHPExcel->setActiveSheetIndex(0)
					   		 ->setCellValue('C'.$nums,'GROWTH');

					    }
						
						$letter_details = 'D';
						foreach ($category_list as $Dey => $value) {

							$br = $ms_db_133;
							//$br = $objPHPExcel->getActiveSheet()->getCell('A'.$num1)->getValue();
							$firstday =  date('m',strtotime($fisrtmonth));
							echo $firstday.'$firstday';
							$lastday =  date('m-t',strtotime($fisrtmonth));
							$year =  date('Y',strtotime($fisrtmonth));

							echo $value.'--'.$firstday.'!!!!'.$year.'~~~~'.$br;

							$get_2018_data = $this->db_con->get_data($br,$year,$value,$firstday,$lastday,'');
							//echo var_dump($get_2018_data).PHP_EOL;
							echo $get_2018_data.PHP_EOL;
							//die();

							//die();


							if($fisrtmonth == $currentmonth_){
								$year_2017 =  date('Y',strtotime('-1 year',strtotime($fisrtmonth)));
								echo $year_2017.'----YEAR --';
								$get_2017_data = $this->db_con->get_data($br,$year_2017,$value,$firstday,$lastday,'');
							echo $get_2017_data.PHP_EOL;
							}

			           	    $cat =array('Liquor','Cigarette','Depstore','Rice','Egg','Vegetable','Pork','Chicken','Beef','Fish');

	    		           	  if(in_array($value,$cat)){
  	
					           	   $c = 0;
						           $category_details = array('Sales','%Sales','GP','%','GP%');
						           $c1 = count($category_details);

						           while ($c1 > $c) {
						           //	echo $category_details[$c].PHP_EOL;

						           	switch ($category_details[$c]) {
									    case 'Sales':
									    	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
									        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_2018_data[0]);
									        if($fisrtmonth == $currentmonth_){
									        	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
									        	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_2017_data[0]);	

									        	#GROWTH
							            		$num2 = $num1 + 1;
							            		$cat_sales_growth = ((($get_2018_data[0] / $get_2017_data[0])-1)*100);
								            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num2,number_format($cat_sales_growth,3));
									        }
									        break;
									    case '%Sales':

									    	$sales = $objPHPExcel->getActiveSheet()->getCell('D'.$num)->getValue();
									    	$cat_letter_details = $this->decrement($letter_details);
									    	$category_sales = $objPHPExcel->getActiveSheet()->getCell($cat_letter_details.$num)->getValue();
									    	$cat_per = (($category_sales / $sales)*100); 
									        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,number_format($cat_per,3));
									        echo $num.'<><><>num<><><'.$num1.'<><><>num1';

									        if($fisrtmonth == $currentmonth_){

									        	$sales = $objPHPExcel->getActiveSheet()->getCell('D'.$num1)->getValue();
										    	$cat_letter_details = $this->decrement($letter_details);
										    	$category_sales = $objPHPExcel->getActiveSheet()->getCell($cat_letter_details.$num1)->getValue();
										    	$cat_per = (($category_sales / $sales)*100); 
										        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,number_format($cat_per,3));

									        }
									    	
									    	/*echo $letter_details.'>>>OLD LETTER'.PHP_EOL;
									    	echo $cat_letter_details.'>>>LETTER'.PHP_EOL;
									    	echo $num.'>>>num'.PHP_EOL;
									    	echo $category_sales.'cat sales'.PHP_EOL;*/


									        break;
									    case 'GP':
									    	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
									        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_2018_data[1]);
									         if($fisrtmonth == $currentmonth_){
									         	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
									        	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_2017_data[1]);

									        	#GROWTH
							            		$num2 = $num1 + 1;
							            		$cat_sales_growth = ((($get_2018_data[1] / $get_2017_data[1])-1)*100);
								            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num2,number_format($cat_sales_growth,3));
									         }
									        break;

									    case '%':
									    	$p_2018 =(($get_2018_data[1] / ($get_2018_data[0] == 0 ? 1 : $get_2018_data[0]))*100);
									        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,number_format($p_2018,3));
									        if($fisrtmonth == $currentmonth_){
									        	$p_2017 =(($get_2017_data[1] / ($get_2017_data[0] == 0 ? 1 : $get_2017_data[0]))*100);
									        	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,number_format($p_2017,3));
									        }

									        break;

									    default:

									    	$gp = $objPHPExcel->getActiveSheet()->getCell('I'.$num)->getValue();
									    	$gpcat_letter_details_ = $this->decrement($letter_details);
									    	$gpcat_letter_details = $this->decrement($gpcat_letter_details_);
									    	$gpcategory_gp = $objPHPExcel->getActiveSheet()->getCell($gpcat_letter_details.$num)->getValue();
									    	$gpcat_per = ($gpcategory_gp / $gp)*100;
									        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,number_format($gpcat_per,3));
									      

									      	if($fisrtmonth == $currentmonth_){
									      		$gp = $objPHPExcel->getActiveSheet()->getCell('I'.$num1)->getValue();
										    	$gpcat_letter_details_ = $this->decrement($letter_details);
										    	$gpcat_letter_details = $this->decrement($gpcat_letter_details_);
										    	$gpcategory_gp = $objPHPExcel->getActiveSheet()->getCell($gpcat_letter_details.$num1)->getValue();
										    	$gpcat_per = ($gpcategory_gp / $gp)*100;

										    	echo $gp.'gp'.PHP_EOL;
										    	echo $gpcategory_gp.'gpcategory_gp'.PHP_EOL;
										    	echo $gpcat_per.'gpcat_per'.PHP_EOL;
										        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,number_format($gpcat_per,3));

									      	}
									     	
									    	// echo $letter_details.'#~~~~~~letter_details~~~#'.PHP_EOL;
									    	// echo $gpcat_letter_details_.'#~~~~~gpcat_letter_details_~~~~#'.PHP_EOL;
									    	// echo $gpcat_letter_details.'#~~~~gpcat_letter_details~~~~~#'.PHP_EOL;
									     	//$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,'');

									}


						           	 $letter_details++;
						           	 $c++;
						           }

			           	  }else if($value =='SALES & GP WITHOUT LIQUOR & CIGARETTE'){

			               $c3 = 0;
				           $wocategory_details = array('Sales','GP','%');
				           $c2 = count($wocategory_details);

				           while ($c2 > $c3) {

				           	 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.'2',$wocategory_details[$c3]);
				           	    switch ($wocategory_details[$c3]) {

				           			 case 'Sales':
				           			 	$sales = $objPHPExcel->getActiveSheet()->getCell('D'.$num)->getValue();
				           			 	$liqour_s = $objPHPExcel->getActiveSheet()->getCell('K'.$num)->getValue();
				           			 	$cigarette_s = $objPHPExcel->getActiveSheet()->getCell('P'.$num)->getValue();
				           			 	$saleswols = $sales - ($liqour_s+$cigarette_s);
				           			 	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
				           			 	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$saleswols);

				           			 		if($fisrtmonth == $currentmonth_){

			           			 				$sales = $objPHPExcel->getActiveSheet()->getCell('D'.$num1)->getValue();
						           			 	$liqour_s = $objPHPExcel->getActiveSheet()->getCell('K'.$num1)->getValue();
						           			 	$cigarette_s = $objPHPExcel->getActiveSheet()->getCell('P'.$num1)->getValue();
						           			 	$saleswols = $sales - ($liqour_s+$cigarette_s);
						           			 	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
						           			 	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$saleswols);

				           			 		}
				           			 break;

				           			 case 'GP':
				           			 	$gp = $objPHPExcel->getActiveSheet()->getCell('I'.$num)->getValue();
				           			 	$liqour_g = $objPHPExcel->getActiveSheet()->getCell('M'.$num)->getValue();
				           			 	$cigarette_g = $objPHPExcel->getActiveSheet()->getCell('R'.$num)->getValue();
				           			 	$gpwols = $gp - ($liqour_g+$cigarette_g);
				           			 	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
				           			 	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$gpwols);

				           			 		if($fisrtmonth == $currentmonth_){
				           			 			$gp = $objPHPExcel->getActiveSheet()->getCell('I'.$num1)->getValue();
						           			 	$liqour_g = $objPHPExcel->getActiveSheet()->getCell('M'.$num1)->getValue();
						           			 	$cigarette_g = $objPHPExcel->getActiveSheet()->getCell('R'.$num1)->getValue();
						           			 	$gpwols = $gp - ($liqour_g+$cigarette_g);
						           			 	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
						           			 	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$gpwols);
				           			 			
				           			 		}
				           			 
				           			 break;

				           			 default;
				           			 		$gp_letter  = $this->decrement($letter_details);
									    	$sales_letter = $this->decrement($gp_letter);
									    	$sales = $objPHPExcel->getActiveSheet()->getCell($sales_letter.$num)->getValue();
				           			 	    $gp = $objPHPExcel->getActiveSheet()->getCell($gp_letter.$num)->getValue();
				           			 	    $percent = ($gp / $sales) * 100;
				           			 		$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,number_format($percent,3));

				           			 			if($fisrtmonth == $currentmonth_){
				           			 				$gp_letter  = $this->decrement($letter_details);
											    	$sales_letter = $this->decrement($gp_letter);
											    	$sales = $objPHPExcel->getActiveSheet()->getCell($sales_letter.$num1)->getValue();
						           			 	    $gp = $objPHPExcel->getActiveSheet()->getCell($gp_letter.$num1)->getValue();
						           			 	    $percent = ($gp / $sales) * 100;
						           			 		$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,number_format($percent,3));
				           			 			
				           			 			}



				           		}
				           	 $letter_details++;
				           	 $c3++;
				           }

			            }else if($value =='Override'){
			            	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
			            	 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_2018_data[0]);
			            	 	if($fisrtmonth == $currentmonth_){
			            	 		 $objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
			            			 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_2017_data[0]); 			
				           		}

				           	 $letter_details++;
						}else{
							//'Sales','DAS','GP BEFOR ADJ','%','INV ADJ','GP AFTER ADJ','%',
							$firstday =  date('m',strtotime($fisrtmonth));
							$lastday =  date('t',strtotime($fisrtmonth));

							$ms_db = $this->db_con->gen_rep_database_server($br);
							$get_conso = $this->db_con->get_data($br,$year,$value,$firstday,$lastday,$ms_db);
							var_dump($get_conso);
							if($fisrtmonth == $currentmonth_){
								$year2017 =  date('Y',strtotime('-1 year',strtotime($year)));
								$get_conso2017 = $this->db_con->get_data($br,$year2017,$value,$firstday,$lastday,$ms_db);	
							}
			            	//###SALES
			            	$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
			            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_conso[0]);
			            	if($fisrtmonth == $currentmonth_){
			            		$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_conso2017[0]);
			            		#GROWTH
			            		$num2 = $num1 + 1;
			            		$sales_growth = ((($get_conso[0] / $get_conso2017[0])-1)*100);
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num2,number_format($sales_growth,3));
			            	}
							$letter_details++;

							//##DAS
							$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
							$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_conso[1]);
							if($fisrtmonth == $currentmonth_){
								$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_conso2017[1]);
			            	}
							$letter_details++;

							//##GP BEFORE
							$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
							$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_conso[2]);
							if($fisrtmonth == $currentmonth_){
								$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_conso2017[2]);

				            	#GROWTH
			            		$num2 = $num1 + 1;
			            		$gbp_growth = ((($get_conso[2] / $get_conso2017[2])-1)*100);
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num2,number_format($gbp_growth,3));
			            	}
							$letter_details++;

							//##GP BEFORE PERCENT
							$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,number_format($get_conso[3],3));
							if($fisrtmonth == $currentmonth_){
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,number_format($get_conso2017[3],3));
			            	}
							$letter_details++;

							//##INV ADJ
							$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
							$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_conso[4]);
							if($fisrtmonth == $currentmonth_){
								$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_conso2017[4]);

				            	$num2 = $num1 + 1;
			            		$inv_growth = ((($get_conso[4] / $get_conso2017[4])-1)*100);
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num2,number_format($inv_growth,3));
			            	}
							$letter_details++;
							//##GP AFTER 
							$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num)->getNumberFormat()->setFormatCode('#,##0.00');
							$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_conso[5]);
							if($fisrtmonth == $currentmonth_){
								$objPHPExcel->getActiveSheet()->getStyle($letter_details.$num1)->getNumberFormat()->setFormatCode('#,##0.00');
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_conso2017[5]);

				            	$num2 = $num1 + 1;
			            		$gpa_growth = ((($get_conso[5] / $get_conso2017[5])-1)*100);
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num2,number_format($gpa_growth,3));
			            	}
							$letter_details++;
							//##GP AFTER PERCENT
							$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,number_format($get_conso[6],3));
							if($fisrtmonth == $currentmonth_){
				            	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,number_format($get_conso2017[6],3));
			            	}
							$letter_details++;

						
						}

							echo $value.'ok!'.PHP_EOL;		
						}
						
						//echo $fisrtmonth.'<><>'.$s.'<><>'.$e.'<>'.PHP_EOL;

						$fisrtmonth = date('Y-m-d',strtotime('+1 month',strtotime($fisrtmonth)));
						$num1 =  $num1+1;
						$num++;
					}


				}

	        //$letter_details = 'D';
			//foreach ($category_list as $Dey => $value) {

			 //    $br = $objPHPExcel->getActiveSheet()->getCell('A'.$num1)->getValue();



					/*
			           	$br = $objPHPExcel->getActiveSheet()->getCell('A'.$num1)->getValue();
			           	$year_2018 = $objPHPExcel->getActiveSheet()->getCell('C'.$num)->getValue();
			           	$year_2017 = $objPHPExcel->getActiveSheet()->getCell('C'.$num1)->getValue();
		           	  	$s = $s;
		           	  	$e = $e;
		           	    $cat =array('Liquor','Cigarette','Depstore','Rice','Egg','Vegetable','Pork','Chicken','Beef');
		           	  	$get_2018_data = $this->db_con->get_data($br, $year_2018 , $value,$s,$e);
		           	    $get_2017_data = $this->db_con->get_data($br, $year_2017 , $value,$s,$e);


		           	  if(in_array($value,$cat)){

				           	   $c = 0;
					           $category_details = array('Sales','%Sales','GP','%','GP%');
					           $c1 = count($category_details);

					           while ($c1 > $c) {

					           	switch ($category_details[$c]) {
								    case 'Sales':
								        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_2018_data[0]);
					           			 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_2017_data[0]);
								        break;
								    case '%Sales':
								        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,'');
					           			$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,'');
								        break;
								    case 'GP':
								        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_2018_data[1]);
					           			$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_2017_data[1]);
								        break;

								    case '%':
								    	$p_2018 =(($get_2018_data[1] / ($get_2018_data[0] == 0 ? 1 : $get_2018_data[0]))*100);
								    	$p_2017 =(($get_2017_data[1] / ($get_2017_data[0] == 0 ? 1 : $get_2017_data[0]))*100);
								        $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$p_2018);
					           			$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$p_2017);
								        break;

								    default:
								     	$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,'');
					           			$objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,'');
								}


					           	 $letter_details++;
					           	 $c++;
					           }

		           	  }else if($value =='SALES & GP WITHOUT LIQUOR & CIGARETTE'){

		               $c3 = 0;
			           $wocategory_details = array('Sales','GP','%');
			           $c2 = count($wocategory_details);

			           while ($c2 > $c3) {
			           	 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.'2',$wocategory_details[$c3]);
			           	 $letter_details++;
			           	 $c3++;
			           }

		            }else if($value =='Override'){
		            	 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num,$get_2018_data[0]);
					     $objPHPExcel->getActiveSheet()->setCellValue($letter_details.$num1,$get_2017_data[0]);
			           	 $letter_details++;
					}else{

						$letter_details++;
					
					}
				*/


				//	echo $value.'ok!'.PHP_EOL;		
				
	//		}	


		//	echo $ms_name.'ok!'.PHP_EOL;

	//  }
		 $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        	)
    	);

	    $category_list = array('Sales','DAS','GP BEFOR ADJ','%','INV ADJ','GP AFTER ADJ','%','Liquor','Cigarette','SALES & GP WITHOUT LIQUOR & CIGARETTE','Override','Depstore','Rice','Egg','Vegetable','Pork','Chicken','Beef','Fish');


		$letter = 'D';
        $letter_details = 'K';
		foreach ($category_list as $key => $value) {
		   $nkey = $key+1;
		   $start_letter = $letter;

          $objPHPExcel->getActiveSheet()->setCellValue($start_letter.'1',$value);

          $cat =array('Liquor','Cigarette','Depstore','Rice','Egg','Vegetable','Pork','Chicken','Beef','Fish');
          if(in_array($value,$cat)){
	           $c = 0;
	           $category_details = array('Sales','%Sales','GP','%','GP%');
	           $c1 = count($category_details);
	           $fcol = $letter_details;
	           while ($c1 > $c) {
	           	 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.'2',$category_details[$c]);
	           	 $objPHPExcel->getActiveSheet()->getStyle($letter_details.'2')->applyFromArray($style);
	           	 $letter_details++;
				 $letter++;
	           	 $c++;
	           }
	           $lcol = $this->decrement($letter_details);
	           $objPHPExcel->getActiveSheet()->mergeCells($fcol.'1:'.$lcol.'1');
	           $objPHPExcel->getActiveSheet()->getStyle($fcol.'1:'.$lcol.'1')->applyFromArray($style);
	           $objPHPExcel->getActiveSheet()->setCellValue($start_letter.'1',$value);
            }else if($value =='SALES & GP WITHOUT LIQUOR & CIGARETTE'){
               $c3 = 0;
	           $wocategory_details = array('Sales','GP','%');
	           $c2 = count($wocategory_details);
	           $fcol = $letter_details;
	           while ($c2 > $c3) {
	           	 $objPHPExcel->getActiveSheet()->setCellValue($letter_details.'2',$wocategory_details[$c3]);
	           	 $objPHPExcel->getActiveSheet()->getStyle($letter_details.'2')->applyFromArray($style);
	           	 $letter_details++;
				 $letter++;
	           	 $c3++;
	           }
	           $lcol = $this->decrement($letter_details);
	           $objPHPExcel->getActiveSheet()->mergeCells($fcol.'1:'.$lcol.'1');
	           $objPHPExcel->getActiveSheet()->getStyle($fcol.'1:'.$lcol.'1')->applyFromArray($style);
	           $objPHPExcel->getActiveSheet()->setCellValue($start_letter.'1',$value);

            }else if($value =='Override'){
				 $letter++;
	           	 $letter_details++;
			}else{
				 $letter++;
			}

		}
       

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setTitle('SUMMARY');

	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save($file_dir."/NEW_.xlsx");

	}

###############


}


?>
