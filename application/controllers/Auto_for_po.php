<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('mssql.connect_timeout',0);
ini_set('mssql.timeout',0);
set_time_limit(0);  
ini_set('memory_limit', -1);

//client_buffer_max_kb_size = '50240'
//sqlsrv.ClientBufferMaxKBSize = 50240

class Auto_for_po extends CI_Controller {
	
	public function __construct(){
		date_default_timezone_set('Asia/Manila');
		parent::__construct();
		$this->load->model("Auto_for_po_model");
	}


	public function start_process(){
		echo 'data gathering please do not close'.PHP_EOL;
		$data = $this->Auto_model_->sample();
		echo $data.PHP_EOL;
	}
	
}
