<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('mssql.connect_timeout',0);
ini_set('mssql.timeout',0);
set_time_limit(0);  
ini_set('memory_limit', -1);

//client_buffer_max_kb_size = '50240'
//sqlsrv.ClientBufferMaxKBSize = 50240

class Auto_ extends CI_Controller {
	
	public function __construct(){
		date_default_timezone_set('Asia/Manila');
		parent::__construct();
		$this->load->model("Auto_model_","Auto_model_");
		$this->load->model("Auto_sales_model_","Auto_sales_model_");
	}



	public function fix_type_24_wrong_ewt(){
		$database = $this->Auto_sales_model_->get_database();

		foreach ($database as $key ) {
			$branch_server = $key->aria_db;
			echo $branch_server.PHP_EOL;
			$from = '2018-01-01';
			$to = '2018-12-31';

			$ewt_diff =  $this->Auto_sales_model_->get_ewt_by_type_w_diff($branch_server,$from,$to);

				foreach ($ewt_diff as $dgl) {

					   echo $dgl->type.PHP_EOL;
				       echo $dgl->type_no.PHP_EOL;
				       echo "NET AMOUNT ->".$dgl->net.PHP_EOL;#5450
				       echo "OUTPUT ->".($dgl->net*0.12).PHP_EOL;#1410010
				       echo "EWT ->".($dgl->net*0.01).PHP_EOL;#23303158
				       $ewt = ($dgl->net*0.01);
				       $advances =($dgl->net + ($dgl->net*0.12)) - ($dgl->net*0.01) ;
				       echo "ADVANCES -> ".$advances.PHP_EOL;#1440
				       echo "+++++++++++++++++===++++++++++++".PHP_EOL;

				        $account = '1440'; #ewt
						$this->Auto_sales_model_->upd_recompute_gl($branch_server,$account,$dgl->type,$dgl->type_no,-$advances);
				        
				        $account = '23303158'; #ewt
						$this->Auto_sales_model_->upd_recompute_gl($branch_server,$account,$dgl->type,$dgl->type_no,-$ewt);

						#upd supp trans
						$this->Auto_sales_model_->upd_recompute_supp_trans($branch_server,$dgl->type,$dgl->type_no,$ewt);

				}	

		}


	}
	public function fix_gl_tb(){

		$database = $this->Auto_sales_model_->get_database('srs_aria_nova');

		foreach ($database as $key ) {
			# code...

			$branch_server = $key->aria_db;
			echo $branch_server.PHP_EOL;
			$from = '2018-01-01';
			$to = '2018-12-31';
			#debit
			$dif_gl =  $this->Auto_sales_model_->get_gl_by_type_w_diff($branch_server,$from,$to);


			foreach ($dif_gl as $dgl) {
				switch ($dgl->type) {
				    case '24':
				       echo $dgl->type.PHP_EOL;
				       echo $dgl->type_no.PHP_EOL;

				       $gl_account = $this->Auto_sales_model_->get_gl_account($branch_server,$dgl->type,$dgl->type_no,"and (account='5450' or account='5400' )");
				       $account = $gl_account->account;


				       if($account == '5450') {

				       		   $check = $this->Auto_sales_model_->check_supp_trans_if_paid($branch_server,$dgl->type,$dgl->type_no); # check if with cv id and already paid

				       		   if($check->cv_id == 0 ){
				       		   	$purchase =  $gl_account->amount;  #5450
							       $vat =  $purchase * 0.12; #1410010
							       $ewt = (($purchase + $vat) * 0.01); #23303158
								   $advances = (($purchase + $vat) - $ewt); #1440

								   $account = '23303158'; #ewt
								   $this->Auto_sales_model_->upd_recompute_gl($branch_server,$account,$dgl->type,$dgl->type_no,-$ewt);
								   
								   $account = '1440'; #advances to supplier
								   $this->Auto_sales_model_->upd_recompute_gl($branch_server,$account,$dgl->type,$dgl->type_no,-$advances);

								   #upd supp trans
								   $this->Auto_sales_model_->upd_recompute_supp_trans($branch_server,$dgl->type,$dgl->type_no,$ewt);

								   echo 'new purch = '.$purchase.PHP_EOL;
								   echo 'vat = '.$vat.PHP_EOL;
								   echo 'ewt = '.$ewt.PHP_EOL;
								   echo 'advances = '.$advances.PHP_EOL;
								  
				       		   }else{

				       		   	   echo "#######WITH CV ID PLEASE CHECK!######";
				       		   }
				       		   
				       		    echo '______________________'.PHP_EOL;

				       }elseif ($account == '5400') {
				       	# code...

				       			 $check = $this->Auto_sales_model_->check_supp_trans_if_paid($branch_server,$dgl->type,$dgl->type_no); # check if with cv id and already paid

				       		   if($check->cv_id == 0 ){

				       		   		$purchase =  $gl_account->amount;  #5400
									$ewt = ($purchase * 0.01); #23303158
									$advances = ($purchase - $ewt); #1440

									$account = '23303158'; #ewt
									$this->Auto_sales_model_->upd_recompute_gl($branch_server,$account,$dgl->type,$dgl->type_no,-$ewt);

									$account = '1440'; #advances to supplier
									$this->Auto_sales_model_->upd_recompute_gl($branch_server,$account,$dgl->type,$dgl->type_no,-$advances);

									#upd supp trans
									$this->Auto_sales_model_->upd_recompute_supp_trans($branch_server,$dgl->type,$dgl->type_no,$ewt);


									echo 'new purch = '.$purchase.PHP_EOL;
								    echo 'ewt = '.$ewt.PHP_EOL;
								    echo 'advances = '.$advances.PHP_EOL;
								   
				       		   }else{

				       		   		  echo "#######WITH CV ID PLEASE CHECK!######";

				       		   }

								 echo '______________________'.PHP_EOL;

				       }

				        break;
				    case '22':

				       echo $dgl->type.PHP_EOL;
				       echo $dgl->type_no.PHP_EOL;

				        $gl_account = $this->Auto_sales_model_->get_gl_account($branch_server,$dgl->type,$dgl->type_no,"and account != '2000' ");
				      	$account = $gl_account->account;
				      	$cash_in_bank_amount = abs($gl_account->amount);


				      	
				        $gl_account_2000 = $this->Auto_sales_model_->get_gl_account($branch_server,$dgl->type,$dgl->type_no,"and account = '2000' ");

				        if(isset($gl_account_2000->account)){

					      	$account = '2000'; #Accounts Payable
							$this->Auto_sales_model_->upd_recompute_gl($branch_server,$account,$dgl->type,$dgl->type_no,$cash_in_bank_amount);
					      	echo $account.'_>>'.$cash_in_bank_amount.PHP_EOL; 

				        }else{

				        	$date = $gl_account->tran_date;
							$this->Auto_sales_model_->insert_gl($branch_server,$dgl->type,$dgl->type_no,$date,'', -$cash_in_bank_amount ,'2000');
				        	echo "need to insert";
				        }



				        break;

				    default:
				    	 echo $dgl->type.PHP_EOL;
				     $batch_db ="";
				}
			}




		}

	}


	public function start_process(){
		echo 'data gathering please do not close'.PHP_EOL;
		$data = $this->Auto_model_->sample();
		echo $data.PHP_EOL;
	}


	public function get_received_po(){
		echo 'data gathering please do not close'.PHP_EOL;
		$ms_res = $this->Auto_model_->get_po();
		foreach ($ms_res as $i => $row) {

			$PurchaseOrderNo = $row->PurchaseOrderNo;
			$ReceivingNo = $row->ReceivingNo;
			$Branch = 'srscain';
			$DateReceived = $row->DateReceived;
			$tot_extended = $row->tot_extended;
			$ReceivingID = $row->ReceivingID;

			$data = $this->Auto_model_->insert_ignore($PurchaseOrderNo,$ReceivingNo,$Branch,date('Y-m-d:h:i:s',strtotime($DateReceived)),$tot_extended,$ReceivingID);

			echo $PurchaseOrderNo.'~'.$ReceivingNo.'~'.$Branch.'~'.$DateReceived.'~'.$tot_extended.'~'.$ReceivingID.PHP_EOL;

			echo $data;
		}

	}

	 public function insert_update_inventory($db,$details){
         $this->ddb = $this->load->database($db, true);

         $sql="INSERT INTO consolidated_inventory 
                        (beg, 
                         end, 
                         months,
                         years) 
               VALUES(".$details['beg'].",
                      ".$details['end'].",
                      ".$details['months'].",
                      ".$details['years']."
                      ) 
               ON DUPLICATE KEY UPDATE   beg = ".$details['beg']." , end = ".$details['end'].", months = ".$details['months']." ,years =  ".$details['years']."";

        $res = $this->ddb->query($sql);
        
        return 'data has been inserted'.$res.'****';

    }
     

    public   function get_inventory($ms_db,$start_date,$end_date){
           $this->ddb = $this->load->database($ms_db, true);
           $beg = 0;
           $end = 0;

           $sql ="select
                SUM(
                  CASE
                  WHEN p.pVatable = 1 THEN
                   (((pb.sellingarea + pb.Damaged) * pb.costofsales)/ 1.12)
                  ELSE
                   ((pb.sellingarea + pb.Damaged) * pb.costofsales)
                  END) AS net_of_vat,
                SUM(pb.sellingarea + pb.Damaged)
                from ProductsBackUpNew as pb
                LEFT JOIN Products as p
                on pb.ProductID = p.ProductID
                where cast(pb.BackUpDate as date) ='".$start_date."'";

                $result = $this->ddb->query($sql);
                $result = $result->row();
                if($result){
                  $beg =  $result->net_of_vat;
                }else{
                  $beg =  0;
                }


             $sql ="select
                SUM(
                  CASE
                  WHEN p.pVatable = 1 THEN
                   (((pb.sellingarea + pb.Damaged) * pb.costofsales)/ 1.12)
                  ELSE
                   ((pb.sellingarea + pb.Damaged) * pb.costofsales)
                  END) AS net_of_vat,
                SUM(pb.sellingarea + pb.Damaged)
                from ProductsBackUpNew as pb
                LEFT JOIN Products as p
                on pb.ProductID = p.ProductID
                where cast(pb.BackUpDate as date) ='".$end_date."'";

                $result = $this->ddb->query($sql);
                $result = $result->row();
                if($result){
                  $end =  $result->net_of_vat;
                }else{
                  $end =  0;
                }

            return array($beg,$end);


    }




    	public function consolidate_inventory(){
		//

			$dates = array('2018-01-01','2018-02-01','2018-03-01','2018-04-01','2018-05-01','2018-06-01','2018-07-01','2018-08-01');
			$ms_db ='branch_server';
			$db = 'gp';
			foreach ($dates as $key => $date) {
			 echo 'data gathering please do not close sa 148 nakatutok and database'.PHP_EOL;

				$start_date = $date;
			    echo "DATE:".$start_date.PHP_EOL;
				$dateto = date("Y-m-t",strtotime($start_date));
				$end_date = date('Y-m-d', strtotime($dateto. ' + 1 days'));

				$data = $this->Auto_model_->get_inventory($ms_db,$start_date,$end_date);
				
				$details = array(
					'years' => date('Y',strtotime($start_date)),
					'months' => date('m',strtotime($start_date)),
					'beg' => $data[0],
					'end' => $data[1]
					);
				

				$this->Auto_model_->insert_update_inventory($db,$details);

			}


		}

			public function consolidate_gp(){

			$dates = array('2018-12-01');
		//  $dates = array('2017-01-01','2017-02-01','2017-03-01','2017-04-01','2017-05-01','2017-06-01','2017-07-01','2017-08-01');
			$ms_db = '148_server';
			$db = 'GP_MY';
			$details = array();
			foreach ($dates as $key => $date) {
			 echo 'data gathering please do not close'.PHP_EOL;

				$datefrom = $date;
				 echo "DATE:".$datefrom.PHP_EOL;
				$dateto = date("Y-m-t",strtotime($datefrom));
				$data = $this->Auto_model_->get_finished_sales_for_formula_1($ms_db,$datefrom,$dateto);
				
				$details = array(
					'years' => date('Y',strtotime($datefrom)),
					'months' => date('m',strtotime($datefrom)),
					'sukipoints' => $data[0],
					'total_cost' => $data[1],
					'total_sales' => $data[2],
					'non_vat_sales' => $data[3],
					'vat_sales' => $data[4],
					'zero_vat_sales' => $data[5],
					'non_vat_cost' => $data[6],
					'vat_cost' => $data[7],
					'zero_vat_cost' => $data[8]);
				

				$this->Auto_model_->insert_update_sales($ms_db,$details);

			}
		}

	// 	public function consolidate_inventory(){
	// 	//
	// 	$dates = array('2018-01-01','2018-02-01','2018-03-01','2018-04-01','2018-05-01','2018-06-01','2018-07-01','2018-08-01');	
	// 	// $dates = array('2017-01-01','2017-02-01','2017-03-01','2017-04-01','2017-05-01','2017-06-01','2017-07-01','2017-08-01');
	// 	$ms_db ='branch_server';
	// 	$db = 'gp';
	// 	foreach ($dates as $key => $date) {
	// 	 echo 'data gathering please do not close sa 148 nakatutok and database'.PHP_EOL;

	// 		$start_date = $date;
	// 	    echo "DATE:".$start_date.PHP_EOL;
	// 		$dateto = date("Y-m-t",strtotime($start_date));
	// 		$end_date = date('Y-m-d', strtotime($dateto. ' + 1 days'));

	// 		$data = $this->Auto_model_->get_inventory($ms_db,$start_date,$end_date);
			
	// 		$details = array(
	// 			'years' => date('Y',strtotime($start_date)),
	// 			'months' => date('m',strtotime($start_date)),
	// 			'beg' => $data[0],
	// 			'end' => $data[1]
	// 			);
			

	// 		$this->Auto_model_->insert_update_inventory($db,$details);

	// 	}

	// }


	
}
