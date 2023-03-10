<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('mssql.connect_timeout',0);
ini_set('mssql.timeout',0);
set_time_limit(0);  
ini_set('memory_limit', -1);



//client_buffer_max_kb_size = '50240'
//sqlsrv.ClientBufferMaxKBSize = 50240

class Auto_po extends CI_Controller {
	
	public function __construct(){
		date_default_timezone_set('Asia/Manila');	
		parent::__construct();
		$this->load->model("Auto_po_model","db_con");
	}


	 public function trans_begin(){
        $this->trans_begin();
    }

    public function trans_status(){
        $res = $this->trans_status();
        return $res;
    }

        public function trans_rollback(){
        $this->trans_rollback();
    }

        public function trans_commit(){
        $this->trans_commit();
    }     
    public function create_consign_pdf($cons_id=null, $branch_name=null,$aria_db = null){
        $this->load->library('my_tcpdf');
        /* $branch_name = 'NOVALICHES';
        $aria_db = 'srs_aria_nova';
        $cons_id = 5870; */
        $header =  $this->db_con->get_consales_header($cons_id,$aria_db);
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'Letter', false, 'UTF-8', false);
 
         $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
         $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
         $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
         $pdf->SetFooterMargin(PDF_MARGIN_BOTTOM);
         $pdf->setPrintHeader(false);
         $pdf->setPrintFooter(true);

         $company = $this->db_con->get_company_profile();
         $pdf->AddPage();
         
         $pdf->SetFont('helvetica', 'B', 15);
         $pdf->Cell(150, 15, 'Consignment', 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $pdf->Ln(10);
         $pdf->SetFont('helvetica', null, 12);
         $pdf->Cell(150, 15, SRS_NAME.'-'.$branch_name, 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $pdf->Ln(10);
         $pdf->SetFont('helvetica', null, 10);
         $pdf->Cell(70, 15, "Period : ".date('Y-m-d',strtotime($header->start_date))." - ".date('Y-m-d',strtotime($header->end_date)), 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $pdf->Ln(5);
         $pdf->Cell(70, 15, "Commision : ".$header->t_commission, 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $pdf->Ln(5);
         $pdf->Cell(70, 15, "Consignor : ".$header->supp_name, 0, false, 'L', 0, '', 0, false, 'M', 'M');
         $pdf->Ln(5);
         $pdf->Cell(70, 15, "Reference : CS".$header->cons_sales_id, 0, false, 'L', 0, '', 0, false, 'M', 'M');

         $pdf->Ln(10);
         $pdf->SetFont('helvetica', 'B', 12);
         $header = array('ProductCode', 'Description ', 'UOM ',' Qty', 'Sales ','Cost' , 'Commission');
         // Header
         $w = array(30, 50, 25 ,10, 25,  20,30 );
         $num_headers = count($header);
        
         for($i = 0; $i < $num_headers; $i++) {
             if($i > 2){
                 $pdf->Cell($w[$i], 8, $header[$i], 0, 0, 'R', 0);
             }
             else{
                $pdf->Cell($w[$i], 8, $header[$i], 0, 0, 'L', 0);
             }
               
         }
         $pdf->Ln(6);
 
         $fill = 0;
         $cons_details_res = $this->db_con->get_cons_details($cons_id,$aria_db);
         $totqty = 0;
         $totsales = 0;
         $totcos = 0;
         $totcom = 0;
         foreach($cons_details_res as $row) {
            $pdf->SetFont('courier', null, 8);
            $pdf->Cell($w[0], 6, $row->prod_code, 0, 0, 'L', $fill);
            $pdf->SetFont('courier', 'B', 9);
            $pdf->Cell($w[1], 6, ' '.$row->description, 0, 0, 'L', $fill);
            $pdf->Cell($w[2], 6, ' '.$row->uom, 0, 0, 'L', $fill);
            $pdf->Cell($w[3], 6, ' '.number_format($row->qty,2).' ', 0, 0, 'R', $fill);
            $pdf->Cell($w[4], 6, ' '.number_format($row->sales,2).' ', 0, 0, 'R', $fill);
            $pdf->Cell($w[5], 6, ' '.number_format($row->cos,2).' ', 0, 0, 'R', $fill);
            $pdf->Cell($w[6], 6, ' '.number_format($row->sales,2) - number_format($row->cos,2).' ', 0, 0, 'R', $fill);
            $totqty =  $totqty + $row->qty;
            $totsales = $totsales + $row->sales;
            $totcos = $totcos + $row->cos;
            $totcom =  $totcom + ($row->sales-$row->cos);
            $pdf->Ln(4);
            $pdf->Ln(8);
           
         }
         if ($pdf->GetY() > $pdf->getPageHeight() - (PDF_MARGIN_BOTTOM+10))
         $pdf->AddPage();

         $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell($w[0], 6, '', 0, 0, 'R', $fill);
            $pdf->Cell($w[1], 6, '', 0, 0, 'R', $fill);
            $pdf->Cell($w[2], 6, 'Total', 0, 0, 'R', $fill);
            $pdf->Cell($w[3], 6, $totqty, 0, 0, 'R', $fill);
            $pdf->Cell($w[4], 6, $totsales, 0, 0, 'R', $fill);
            $pdf->Cell($w[5], 6,  $totcos, 0, 0, 'R', $fill);
            $pdf->Cell($w[6], 6, $totcom, 0, 0, 'R', $fill);
         $pdf->Ln(4);
         $pdf->Cell($w[0], 6, '*NOTE: (Please create invoice amounting )'.$totcos, 0, 0, 'L', $fill);
         $pdf->Ln(8);
         $pdf->Cell($w[1], 6, 'Requested By:', 0, 0, 'L', $fill);
         $pdf->Cell($w[3], 6, 'Noted By:', 0, 0, 'L', $fill);
         $pdf->Cell($w[4], 6, '', 0, 0, 'L', $fill);
         $pdf->Cell($w[5], 6, '', 0, 0, 'L', $fill);
         $pdf->Cell($w[6], 6, 'Approved By:', 0, 0, 'L', $fill);
         $pdf->Ln(10);
         $pdf->Cell($w[1], 6, '', 0, 0, 'L', $fill);
         $pdf->Cell($w[3], 6, 'Rowena Villar', 0, 0, 'L', $fill);
         $pdf->Cell($w[4], 6, '', 0, 0, 'L', $fill);
         $pdf->Cell($w[5], 6, '', 0, 0, 'L', $fill);
         $pdf->Cell($w[6], 6, 'Dustin Uy', 0, 0, 'L', $fill);
         $pdf->Ln(10);
         $pdf->Footer();
        //$pdf->Output($branch_name.'.pdf', 'I');
        return $pdf->Output(date('Y-m-d').' CS'.$cons_id.'.pdf', 'S');

    }

        public function create_po_pdf($po=null){
        	 $this->load->library('my_tcpdf');

       $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'Letter', false, 'UTF-8', false);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_BOTTOM);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        // $branch = $this->po_model->get_po_list_breif($po);
       // $po = 'PO1197845 ';
        $branch = $this->db_con->get_po_list_breif($po,16);
        $branch = $branch[0];
         //echo var_dump($branch);
       // exit();
        $pdf->po_no = $branch->reference;

        $bra = $branch->br_code;
        $brnch = $this->db_con->get_custom_val("branches",array("address","tin","name","database"),"code",$bra);
        $branch_det = array("address"=>$brnch->address,"tin"=>$brnch->tin,"name"=>$brnch->name,"database"=>$brnch->database,"code"=>$bra);
        $this->session->set_userdata('srs_branch',$branch_det);

        $suppl = $this->db_con->get_srs_suppliers_details($branch->supplier_id);
        $supp = $suppl[0];
        /* echo var_dump($supp);
        exit();
 */

        // echo var_dump($branch);
        // echo $this->db->last_query();
        // $branch = $branch[0];
        // echo var_dump($branch);
        $company = $this->db_con->get_company_profile();
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 15);
        $pdf->Cell(150, 15, SRS_NAME, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(70, 15, "Purchase Order", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', null, 12);
        $pdf->Cell(150, 15, $branch->delivery_address, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', null, 10);
        $pdf->Cell(70, 15, "No.", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(150, 15, $branch_det['tin'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(70, 15, $branch->reference, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', null, 10);
                $pdf->Cell(150, 15, "", 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(70, 15, "Date Created", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
                $pdf->Cell(150, 15, "", 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(70, 15, date('Y-m-d',strtotime($branch->trans_date)), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', null, 10);
        //$pdf->Cell(150, 15, "Valid Until", 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(150, 15, "", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(70, 15, "Delivery Date", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 10);
        //$pdf->Cell(150, 15, sql2Date($branch->valid_date), 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(150, 15, "" , 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(70, 15, date('Y-m-d',strtotime($branch->delivery_date)), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell(0, 15, , 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $w = array(70, 90, 55,60);
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', null, 10);
        $pdf->Cell(80, 15, "Vendor", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(100, 15, "Recieve At", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell($w[2], 15, "No.", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(1.5);
        $pdf->SetFont('helvetica', 'B', 12);
        // $pdf->Cell($w[0], 15,$supp->description, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell($w[1], 15, SRS_NAME, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->MultiCell(80, 15,$supp->description,0,'L',false,0);
        $pdf->MultiCell(100, 15, SRS_NAME,0,'L',false,1);
        
        // $pdf->Ln(1);
        $pdf->SetFont('helvetica', null, 9);
        $pdf->Cell(80, 15, $supp->address, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(100, 15, $branch->delivery_address, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', null, 9);
        $pdf->Cell(80, 15, $supp->contactperson, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(100, 15, $branch->tin, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Ln(10);
        if($supp->term_desc != ''){
            $pdf->SetFont('helvetica', null, 10);
            $pdf->Cell($w[0], 15, "Terms", 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell($w[1], 15, "", 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell($w[3], 15, "", 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(4);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell($w[0], 15, $supp->term_desc, 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell($w[1], 15, "", 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell($w[3], 15, "", 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(5);
        }

        $pdf->SetFont('helvetica', 'B', 12);
        $header = array('Code', '   Particulars ', ' UOM ',' Qty ', 'Price  ' , '  Extended');
        // Header
        $w = array(25, 90, 15 , 15, 20 ,  20 );
        $num_headers = count($header);
        for($i = 0; $i < $num_headers; $i++) {
            if($i > 2){
                $pdf->Cell($w[$i], 7, $header[$i], 0, 0, 'R', 0);
            }
            else
                $pdf->Cell($w[$i], 7, $header[$i], 0, 0, 'L', 0);
        }
        $pdf->Ln(6);

        $fill = 0;
        $get_po_details = array();
        $get_po_details = $this->db_con->get_po_details($branch->order_no,'16');

        $pdf->SetFont('courier', 'B', 9);
        $po_total=0;
        $qty_total=0;

        foreach($get_po_details as $row) {
            $pdf->SetFont('courier', null, 8);
            $pdf->Cell($w[0], 6, $row->barcode, 0, 0, 'L', $fill);
            $pdf->SetFont('courier', 'B', 9);
            $pdf->Cell($w[1], 6, ' '.$row->description, 0, 0, 'L', $fill);
            $pdf->Cell($w[2], 6, ' '.$row->unit_id, 0, 0, 'L', $fill);
            $pdf->Cell($w[3], 6, ' '.number_format($row->ord_qty,2).' ', 0, 0, 'R', $fill);
            $pdf->Cell($w[4], 6, ' '.number_format($row->unit_price,2).' ', 0, 0, 'R', $fill);
            $total = $row->ord_qty * $row->unit_price;
            $discTxt = "";
            if($row->discounts != "" || $row->discounts != 0){
                $discounts = explode(',', $row->discounts);
                foreach ($discounts as $discs) {
                    $disc = explode('=>',$discs);
                    if (count($disc) <= 1)
                        continue;
                    $discTxt .= $disc[0];
                    $total -= $disc[1];
                }
            }
            // $pdf->Cell($w[5], 6, $discTxt, 0, 0, 'R', $fill);
            $pdf->Cell($w[5], 6, number_format($total,2), 0, 0, 'R', $fill);
            $po_total += $total;
            $qty_total += $row->ord_qty;
            $pdf->Ln(4);
        }
        $pdf->SetFont('courier', null, 9);
        // $pdf->Ln(6);
        // $pdf->Cell($w[0], 6, '', 0, 0, 'L', $fill);
        // $pdf->Cell($w[1], 6, '', 0, 0, 'L', $fill);
        // $pdf->Cell($w[2], 6, '', 0, 0, 'L', $fill);
        // $pdf->SetFont('helvetica', 'B', 14);
        // $pdf->Cell($w[3], 6, null, 0, 0, 'R', $fill);
        // $pdf->Cell($w[4], 6, null, 0, 0, 'R', $fill);
        // // $pdf->SetFont('courier', null, 8);
        // $pdf->Cell($w[5], 6, '', 0, 0, 'R', $fill);
        // $pdf->SetFont('helvetica', 'B', 14);
        // $pdf->Cell($w[6], 6, 'Total Qty', 0, 0, 'R', $fill);
        // // $pdf->Cell($w[5], 6, null, 0, 0, 'R', $fill);
        // $pdf->Ln(6);
        // $pdf->Cell($w[0], 6, '', 0, 0, 'L', $fill);
        // $pdf->Cell($w[1], 6, '', 0, 0, 'L', $fill);
        // $pdf->Cell($w[2], 6, '', 0, 0, 'L', $fill);
        // $pdf->SetFont('helvetica', 'B', 14);
        // $pdf->Cell($w[3], 6, null, 0, 0, 'R', $fill);
        // $pdf->Cell($w[4], 6, null, 0, 0, 'R', $fill);
        // // $pdf->SetFont('courier', null, 8);
        // $pdf->Cell($w[5], 6, '', 0, 0, 'R', $fill);
        // $pdf->SetFont('helvetica', '', 12);
        // $pdf->Cell($w[6], 6, number_format($qty_total), 0, 0, 'R', $fill);
        // // $pdf->Cell($w[5], 6, null, 0, 0, 'R', $fill);
        
        $pdf->Ln(6);
        $pdf->Cell($w[0], 6, '', 0, 0, 'L', $fill);
        $pdf->Cell($w[1], 6, '', 0, 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell($w[2], 6, '', 0, 0, 'R', $fill);
        $pdf->Cell($w[3], 6, 'Total Qty', 0, 0, 'R', $fill);
        $pdf->Cell($w[4], 6, null, 0, 0, 'R', $fill);
        // $pdf->SetFont('courier', null, 8);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell($w[5], 6, 'Grand Total', 0, 0, 'R', $fill);
        // $pdf->Cell($w[6], 6, 'Grand Total', 0, 0, 'R', $fill);
        // $pdf->Cell($w[5], 6, null, 0, 0, 'R', $fill);
        $pdf->Ln(6);
        $pdf->Cell($w[0], 6, '', 0, 0, 'L', $fill);
        $pdf->Cell($w[1], 6, '', 0, 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell($w[2], 6, '', 0, 0, 'R', $fill);
        $pdf->Cell($w[3], 6, number_format($qty_total,2), 0, 0, 'R', $fill);
        // $pdf->Cell($w[3], 6, number_format($pdf->getPageHeight() - (PDF_MARGIN_BOTTOM+13)), 0, 0, 'R', $fill);
        $pdf->Cell($w[4], 6, null, 0, 0, 'R', $fill);
        // $pdf->SetFont('courier', null, 8);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell($w[5], 6,  number_format($po_total,2), 0, 0, 'R', $fill);
        // $pdf->Cell($w[5], 6,  number_format($pdf->GetY()), 0, 0, 'R', $fill);
        // $pdf->Cell($w[6], 6, number_format($po_total), 0, 0, 'R', $fill);
        // $pdf->Cell($w[5], 6, null, 0, 0, 'R', $fill);

        $pdf->Ln(8);
        if ($pdf->GetY() > $pdf->getPageHeight() - (PDF_MARGIN_BOTTOM+13))
            $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($w[0], 6, "Remarks", 0, 0, 'L', $fill);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell($w[1], 6, null, 0, 0, 'R', $fill);
        $pdf->Cell($w[2], 6, null, 0, 0, 'R', $fill);
        $pdf->Cell($w[3], 6, null, 0, 0, 'R', $fill);
        $pdf->Cell($w[4], 6, null, 0, 0, 'R', $fill);
        $pdf->Cell($w[5], 6, null, 0, 0, 'L', $fill);

        $comment  = "";
        $comments = $this->db_con->get_po_comments($branch->order_no,'16');
        // echo $this->db->last_query();
        if(count($comments) > 0 )
        {
            
        
            $comment = $comments[0];
        
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', null, 11);
            $pdf->Cell($w[0], 6, $comment->memo_, 0, 0, 'L', $fill);
            $pdf->Cell($w[1], 6, null, 0, 0, 'R', $fill);
            $pdf->Cell($w[2], 6, null, 0, 0, 'L', $fill);
            $pdf->Cell($w[3], 6, null, 0, 0, 'R', $fill);
            $pdf->Cell($w[4], 6, null, 0, 0, 'R', $fill);
            $pdf->Cell($w[5], 6, null, 0, 0, 'R', $fill);
        }
        
        $pdf->Ln();
        $prepared_by = $this->db_con->get_po_prepared_by(16, $branch->order_no);
        $prepared_by = $prepared_by[0];
        $prepared_by_sign = $prepared_by->sign;
        $prepared_by = $prepared_by->prepared_by;
        
        $approved_by = $this->db_con->get_po_approved_by(16, $branch->order_no);
        $approved_by = $approved_by[0];
        $approved_by_sign = $approved_by->sign;
        $approved_by = $approved_by->approved_by;

        $p_sign = BASEPATH.'signatures/'.$prepared_by_sign;
        $a_sign = BASEPATH.'signatures/'.$approved_by_sign;

        if ($pdf->GetY() > $pdf->getPageHeight() - (PDF_MARGIN_BOTTOM+18))
            $pdf->AddPage();
            
        $pdf->SetY($pdf->getPageHeight() - (PDF_MARGIN_BOTTOM+18));
        $ww = 35;
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell($ww-7, 6, '', 'LT', 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($ww, 6, '', 'RT', 0, 'L', $fill);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell($ww-8, 6, '', 'LT', 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($ww, 6, '', 'RT', 0, 'L', $fill);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell($ww-8, 6, '', 'LT', 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($ww, 6, '', 'RT', 0, 'L', $fill);
        $pdf->Ln();
        
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell($ww-13, 6, 'Prepared by: ', 'LB', 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($ww+6, 6, $prepared_by, 'RB', 0, 'L', $fill);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell($ww-8, 6, 'Checked by: ', 'LB', 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($ww, 6, '', 'RB', 0, 'L', $fill);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell($ww-13, 6, 'Approved by: ', 'LB', 0, 'L', $fill);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($ww+5, 6, $approved_by, 'RB', 0, 'L', $fill);
        
        if ($prepared_by_sign != '')
            $pdf->Image($p_sign,20,230,0,20);

        if ($approved_by_sign != '')
            $pdf->Image($a_sign,154,230,0,20);
        
        
        
        $pdf->SetFont('helvetica', null, 8);
        $pdf->SetY($pdf->getPageHeight() - (PDF_MARGIN_BOTTOM+4));
       //  $pdf->Footer();
         //$pdf->Output($po.'.pdf', 'I');
        return $pdf->Output(date('Y-m-d').$po.'.pdf', 'S');
    }

    public function email_consignment(){
        $db = $this->db_con->get_database();

		foreach ($db as $i => $dbdetails) {
			# code...
			$ms_db = $dbdetails->ms_db;
			$aria_db = $dbdetails->aria_db;
            $branch_name = $dbdetails->branch_name;
            echo $aria_db.PHP_EOL;
            $consignment = $this->db_con->get_consignment($aria_db);
            foreach ($consignment as $i => $condetails) {
                $cons_id = $condetails->cons_sales_id;
                $this->send_consignment_email($cons_id, $branch_name,$aria_db);
            }
            echo PHP_EOL;
        }
    }

	  public function email_sample(){ 
        $list_supplier = $this->db_con->send_only_one_supplier();
        $vendor_id = implode(',', array_map(function($row) {
            return "'".$row->vendor."'";
        }, $list_supplier));
        $unset_po_list = $this->db_con->select_unsent_po($vendor_id);
        foreach ($unset_po_list as $list) {
          $supplier_email = $list->supplier_email;
          $supplier_id = $list->supplier_id;
          $list_po     = $this->db_con->select_list_po_id_not_send($supplier_email, $supplier_id);
          $email_adds  = explode(';',  $list->supplier_email); 
          $this->send_po_mail_test($supplier_email,$list_po, $email_adds, $supplier_id);
        }

 
    }    
  

    public function send_consignment_email($cons_id = null, $branch_name = null,$aria_db=null){
        $this->load->library('my_phpmailer');
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
           try {
               $mail->isSMTP();   
               $mail->SMTPAuth   = true; // enabled SMTP authentication maam 
                  $mail->SMTPSecure = 'tls';                                   // Set mailer to use SMTP
               $mail->Host = 'mail.srssulit.com'; //"smtp.gmail.com";      // setting GMail as our SMTP 
                  $mail->Port       = 587;  //465; 587 ';  // Specify main and backup SMTP servers
               $mail->Username = "no-reply@srssulit.com";
                  $mail->Password   = 'Srs01212009srs';  
               $mail->setFrom('no-reply@srssulit.com', 'SAN ROQUE SUPERMARKET');
               $mail->addReplyTo('no-reply@srssulit.com', 'SRS');
               
               //Content
               $mail->isHTML(true);

                $mail->Subject    = "Consignment Sales Report                ";
                $body = "   <p>Please see attachment  Consignment Sales Report.</p> ";
                $mail->MsgHTML($body);
                $header =  $this->db_con->get_consales_header($cons_id,$aria_db);
                $email_adds  = explode(';',  $header->supp_email); 
                $recipient =  $email_adds;
                $recipientcc =  'srsdevteam2021@gmail.com';
                $mail->addCC($recipientcc);
                     $recs = "";
                    if(is_array($recipient)){
                       $count = 0;
                       foreach ($recipient as $rec) {
                       if($rec != null)
                       echo $rec;
                           $mail->AddAddress($rec);
                           $recs .= $rec.",";
                       }
                   }
                   else{ 
                       $mail->AddAddress($recipient);
                       $recs .= $recipient.",";
                       echo $recipient;
                } 

           /*  foreach ($list_po_ref as $row) { */
            $pdf = $this->create_consign_pdf($cons_id, $branch_name,$aria_db);
               $mail->AddStringAttachment($pdf, 'CS'.$cons_id.".pdf");
          /*  } */

           $mail_mail = 0;
           if(!$mail->Send()) {
               $mail->ClearAddresses();
               $mail->ClearAttachments();
               echo $mail->ErrorInfo."error".PHP_EOL;
           } else {
               $mail_mail = 1;
               $mail->ClearAddresses();
               $mail->ClearAttachments();
               $recs = substr($recs, 0,-1); 
               echo "success".PHP_EOL;

              /*   foreach ($list_po_ref as $row) { */
                   $mail_items = array(
                               "email_sent"=>1
                           );
                   $this->db_con->update_consig_email($cons_id,$aria_db,$mail_items);
            /*    }  */
               

           }
               echo 'Message has been sent';
           } catch (Exception $e) {
               echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
           }

    }


	public function send_po_mail_test($po_id ='',$list_po_ref='',$recipient=null, $supplier_id=null){
        $this->load->model("Auto_po_summary_model","auto_po_summary");
		 $this->load->library('my_phpmailer');
		 $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
			try {
			    $mail->isSMTP();   
			    $mail->SMTPAuth   = true; // enabled SMTP authentication maam 
           		$mail->SMTPSecure = 'tls';                                   // Set mailer to use SMTP
                $mail->Host = 'mail.srssulit.com'; //"smtp.gmail.com";      // setting GMail as our SMTP 
           		$mail->Port       = 587;  //465; 587 ';  // Specify main and backup SMTP servers
			    $mail->Username = "no-reply@srssulit.com";
           		$mail->Password   = 'Srs01212009srs';  
			    $mail->setFrom('no-reply@srssulit.com', 'SAN ROQUE SUPERMARKET');
			    $mail->addReplyTo('no-reply@srssulit.com', 'SRS');
                // $mail->addCC('sulitsrs12009@gmail.com');
                // $mail->addCC('alemania.nhelle28@gmail.com');
                /*  $cc_supplier = $this->db_con->cc_supplier($supplier_id);
                    if($cc_supplier !="") {
                     if($cc_supplier->email != "" || $cc_supplier->email != null){
                         $mail->addCC($cc_supplier->email, $cc_supplier->fname.' '.$cc_supplier->lname);
                     }
                 } */
			    //Content
			    $mail->isHTML(true);

			     $mail->Subject    = "Purchase Order ";
				 $body = "   <p>Please see attachment  Purchase Order .</p> ";
				 $mail->MsgHTML($body);  
                // $recipient = 'srsdevteam2021@gmail.com';

				      $recs = "";
		            if(is_array($recipient)){
                        $count = 0;
		                foreach ($recipient as $rec) {
		                if($rec != null)
                        echo $rec;
		                    $mail->AddAddress($rec);
		                    $recs .= $rec.",";

                            //modify
                            foreach ($list_po_ref as $row) {
                               $data  = array(
                                    "po_no" => $row->ref,
                                    "order_no" => $row->order_no,
                                    "supplier_email" => $rec,
                                    "supplier_id" => $row->supplier_id,
                                    "delivery_date" => $row->delivery_date,
                                    "created_date" => $row->date_created,
                                    "status" => 0,
                                    "br_code"=> $row->br_code,
                                    "net_total" => $row->net_total,
                                    "trans_type" => $row->trans_type
                                );
                                $this->db_con->add_po_email_send($data);
                            }
                            // end modify
                            if ($count++ == 0){
                                 $summary_report = $this->create_po_pdf_summary($rec, $supplier_id);
                                 $mail->AddStringAttachment($summary_report, date('Y-m-d').".pdf");
                            }
		                }
		            }
		            else{
		                $mail->AddAddress($recipient);
		                $recs .= $recipient.",";
                        echo $recipient;
		            }
             foreach ($list_po_ref as $row) {
                $pdf = $this->create_po_pdf($row->ref);
                $mail->AddStringAttachment($pdf, $row->ref.".pdf");
            }

            $mail_mail = 0;
            if(!$mail->Send()) {
                $mail->ClearAddresses();
                $mail->ClearAttachments();
                echo $mail->ErrorInfo."error".PHP_EOL;
            } else {
                $mail_mail = 1;
                $mail->ClearAddresses();
                $mail->ClearAttachments();
                $recs = substr($recs, 0,-1); 
                echo "success".PHP_EOL;

                 foreach ($list_po_ref as $row) {
                    $mail_items = array(
                                "email"=>1
                            );
                    $this->db_con->update_to_purch_orders($mail_items,$row->order_no,16);
                } 
	            

            }
			    echo 'Message has been sent';
			} catch (Exception $e) {
			    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
			}


	}

    private function create_po_pdf_summary($email, $supplier_id) {
        $this->load->model("Auto_po_summary_model","auto_po_summary");
        $this->load->library('my_tcpdf');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_BOTTOM);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 10);

        // add a page
        $pdf->AddPage();
        $po_list = $this->auto_po_summary->fetch_po_list($email, $supplier_id);

        $html = '<h2>SAN ROQUE SUPERMARKET RETAIL SYSTEMS, INC.</h2>
        <table border="1" cellspacing="2" cellpadding="3" with="100%">
            <tr>
                <th colspan="5">Purchase Order Summary</th>
            </tr>
            <tr>
                 <th colspan="5">Total P.O '.count($po_list).'</th>
            </tr>
            <tr>
                <th>No</th>
                <th>Net Total</th>
                <th>Branch Name</th>
                <th>Created Date</th>
                <th>Delivery Date</th>
            </tr>';
        $count   = 1;
        foreach ($po_list as $row) {
            $html .= '<tr>
                      <td>'.$row->po_no.'</td>
                      <td>'.number_format($row->net_total, 2).'</td>
                      <td>'.$row->branch_name.'</td>
                      <td>'.$row->created_date.'</td>
                      <td>'.$row->delivery_date.'</td></tr>
            ';
        }
        $html .= '</table>';

        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->lastPage();
        return $pdf->Output(date('Y-m-d').'.pdf', 'S');
    }


    public function email_send_summary_po($supplier_email, $summary_report){
        $this->load->model("Auto_po_summary_model","auto_po_summary");
        $this->load->library('my_phpmailer');

        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            $mail->isSMTP();   
            $mail->SMTPAuth   = true; // enabled SMTP authentication maam dito n p akosige
            $mail->SMTPSecure = 'tls';                                   // Set mailer to use SMTP
            $mail->Host = 'mail.srssulit.com'; //"smtp.gmail.com";      // setting GMail as our SMTP 
            $mail->Port       = 587;  //465; 587 ';  // Specify main and backup SMTP servers
            $mail->Username = "no-reply@srssulit.com";
            $mail->Password   = 'Srs01212009srs';  
            $mail->setFrom('no-reply@srssulit.com', 'SAN ROQUE SUPERMARKET');
            $mail->addReplyTo('no-reply@srssulit.com', 'SRS');
            $mail->addCC('sulitsrs12009@gmail.com');
            //Content
            $mail->isHTML(true);

            $mail->Subject    = "Purchase Order ";
            $body = " <p>Please see P.O summary attachment.</p> ";
            $mail->MsgHTML($body);  

            $mail->AddAddress($supplier_email);
            $mail->AddStringAttachment($summary_report, date('Y-m-d').".pdf");
            if(!$mail->Send()) {
                $mail->ClearAddresses();
                $mail->ClearAttachments();
                echo $mail->ErrorInfo."error".PHP_EOL;
            } else {
                $mail->ClearAddresses();
                $mail->ClearAttachments();
                echo "success".PHP_EOL;
            }

        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }


	public function fix_sales(){
		echo 'fix  sales in aria'.PHP_EOL;
		$db = $this->db_con->get_database('srs_aria_valenzuela');

		foreach ($db as $i => $dbdetails) {
			# code...
			$ms_db = $dbdetails->ms_db;
			$aria_db = $dbdetails->aria_db;

			$ms_res = $this->db_con->get_ms_sales($ms_db);

			echo $aria_db.PHP_EOL;
			echo "*************************".PHP_EOL;
			foreach ($ms_res as $i => $row) {
				# code...
				echo $row->LogDate.PHP_EOL;
				$sales_date =  $row->LogDate;
				$gross_sales =  $row->total;

				## DEBIT ##
						$sales_collection_gross = $this->db_con->get_sales_collection($sales_date,$aria_db,'100', '1060000');
				## DEBIT ##

				## CREDIT ##
						$sales_collection_per_type = $this->db_con->get_sales_collection($sales_date,$aria_db,'100', '4900,4000020,4000050,4000,2310');		 				
				## CREDIT ##

		 		## NON VAT ##
						$sales_collection_nv = $this->db_con->get_sales_collection($sales_date,$aria_db,'100', '4000020');
						$sales_ms_nv = $this->db_con->get_nv_sales($sales_date,$ms_db);
				## NON VAT ##

		 		## ZERO VAT ##
						$sales_collection_zv = $this->db_con->get_sales_collection($sales_date,$aria_db,'100', '4000050');
						$sales_ms_zr = $this->db_con->get_zr_sales($sales_date,$ms_db);
				## ZERO VAT ##

				## SK ##
						$sales_collection_sk = $this->db_con->get_sales_collection($sales_date,$aria_db,'100', '4900');	
						$get_suki_points_sales = $this->db_con->get_suki_points_sales($sales_date,$ms_db);	 				
				## SK ##


				if(!(round($sales_collection_sk,2) == round($get_suki_points_sales,2) && round($gross_sales,2) == round($sales_collection_gross,2) && round($gross_sales,2) == round($sales_collection_per_type,2) && round($sales_collection_nv,2) == round($sales_ms_nv,2) && round($sales_collection_zv,2) == round($sales_ms_zr,2) )){

					$get_existed_sales = $this->db_con->get_existed_sales($sales_date,$aria_db);
					//## delete  existed_sales
					 $this->db_con->delete_gl($sales_date,$aria_db,$get_existed_sales);

					$get_suki_points_sales = $this->db_con->get_suki_points_sales($sales_date,$ms_db);

						
					$ref = $this->db_con->get_ref($aria_db);
					$memo = "Sales";
					$max_type_no = $this->db_con->get_next_trans_no($aria_db);
					$net_sales = $gross_sales - $get_suki_points_sales;
					$gross_vatable = $net_sales - $sales_ms_nv-$sales_ms_zr;
					$sales_vat = round($gross_vatable/1.12,2);
					$output_vat = $gross_vatable - $sales_vat;

					//echo $ref.'---'.$max_type_no.PHP_EOL;
					//	die();
					## insert gl trans gross account 1060000
					$this->db_con->insert_gl($aria_db,'100', $max_type_no, $sales_date,'Gross', -$gross_sales,'1060000');

					if($get_suki_points_sales != 0){
					## insert gl trans suki points account 1060000
						$this->db_con->insert_gl($aria_db,'100', $max_type_no, $sales_date,'Suki Points', $get_suki_points_sales,'4900');
					}

					if($sales_ms_nv != 0){
					## insert gl trans non vat account 4000020
						$this->db_con->insert_gl($aria_db,'100', $max_type_no, $sales_date,'Sales Non-Vat', $sales_ms_nv,'4000020');
					}

					if($sales_ms_zr != 0){
					## insert gl trans zero vat account 4000020
						$this->db_con->insert_gl($aria_db,'100', $max_type_no, $sales_date,'Zero Vat', $sales_ms_zr,'4000050');
					}
						$this->db_con->insert_gl($aria_db,'100', $max_type_no, $sales_date,'Sales Vat', $sales_vat,'4000');
						$this->db_con->insert_gl($aria_db,'100', $max_type_no, $sales_date,'Output Vat', $output_vat,'2310');

					if($memo != '')
					{
						$this->db_con->add_comments($aria_db,'100', $max_type_no, $sales_date, $memo);
					}

						$this->db_con->add_refs($aria_db,'100', $max_type_no,$ref);
						$this->db_con->add_audit_trail($aria_db,'100', $max_type_no, $sales_date,'');

						echo 'ARIA AND MS GROSS '.round($sales_collection_gross,2).'=='.round($gross_sales,2).PHP_EOL;
						echo 'GROSS DEBIT AND CREDIT'.round($gross_sales,2).'=='.round($sales_collection_per_type,2).PHP_EOL;
						echo 'ARIA AND MS NVAT '.round($sales_collection_nv,2).'=='.round($sales_ms_nv,2).PHP_EOL;
						echo 'ARIA AND MS ZVAT '.round($sales_collection_zv,2).'=='.round($sales_ms_zr,2).PHP_EOL;

						echo $row->LogDate.' Has been Transfered'.PHP_EOL;

				}else{
					echo 'ARIA AND MS GROSS '.round($sales_collection_gross,2).'=='.round($gross_sales,2).PHP_EOL;
					echo 'GROSS DEBIT AND CREDIT'.round($gross_sales,2).'=='.round($sales_collection_per_type,2).PHP_EOL;
					echo 'ARIA AND MS NVAT '.round($sales_collection_nv,2).'=='.round($sales_ms_nv,2).PHP_EOL;
					echo 'ARIA AND MS ZVAT '.round($sales_collection_zv,2).'=='.round($sales_ms_zr,2).PHP_EOL;
					echo $row->LogDate.' Already Transfered and Equal'.PHP_EOL;

				}

				

			}

		}

		echo 'Success'.PHP_EOL;
	
	}

	public function fix_old_sales(){
		
		echo 'fix old sales in aria'.PHP_EOL;
		$db = $this->db_con->get_database('srs_aria_valenzuela');

		foreach ($db as $i => $dbdetails) {
			# code...
			$ms_db = $dbdetails->ms_db;
			$aria_db = $dbdetails->aria_db;

			$ms_res = $this->db_con->get_ms_sales($ms_db);
			echo $aria_db.PHP_EOL;
			echo "*************************".PHP_EOL;
				foreach ($ms_res as $i => $row) {

					# code...
					$sales_date =  $row->LogDate;
					$gross_sales =  $row->total;

					//## delete  type 60  gl trans old transactions account in('4900','4000020','4000','2310','4000050')
					 $this->db_con->delete_gl_60($sales_date,$aria_db);

					//get old sales collection trans no in gl
					$get_old_trans_no = $this->db_con->get_old_trans_no($sales_date,$aria_db);
					$get_old_sales = $this->db_con->get_old_sales($sales_date,$aria_db);

					if(!$get_old_sales){
						//## insert new gl type 60
						$this->db_con->insert_gl_60($sales_date,$aria_db,$get_old_trans_no,$gross_sales);
					}else{
						## IF EXISTED
						//## delete  type 60  gl trans old transactions account in('1060000')
						$this->db_con->delete_gl_60_1060000($sales_date,$aria_db);

						//## insert new gl type 60
						$this->db_con->insert_gl_60($sales_date,$aria_db,$get_old_trans_no,$gross_sales);

					}

					echo $row->LogDate.' Has been Fixed'.PHP_EOL;

				}

		}
		
		echo 'Success'.PHP_EOL;
	}



	
	
}
	