<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('mssql.connect_timeout',0);
ini_set('mssql.timeout',0);
set_time_limit(0);  
ini_set('memory_limit', -1);

class Auto_po_summary extends CI_Controller {

	public function __construct(){
		date_default_timezone_set('Asia/Manila');	
		parent::__construct();
		$this->load->model("Auto_po_summary_model","auto_po_summary");
	}

	private function create_po_pdf($email, $supplier_id) {
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

		$html = '<h2>SAN ROQUE SUPERMARKET RETAIL SYSTEMS, INC.</h2>
		<table border="1" cellspacing="2" cellpadding="3" with="100%">
			<tr>
				<th colspan="3">Purchase Order Summary</th>
			</tr>
		    <tr>
		        <th>No</th>
		        <th>Date Created</th>
		        <th>Delivery Date</th>
		    </tr>';
		$po_list = $this->auto_po_summary->fetch_po_list($email, $supplier_id);
		$count   = 1;
		foreach ($po_list as $row) {
			$html .= '<tr>
					  <td>'.$row->po_no.'</td>
					  <td>'.$row->created_date.'</td>
					  <td>'.$row->delivery_date.'</td></tr>
			';
		}
		$html .= '</table>';

		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->lastPage();
        return $pdf->Output('TESTING.pdf', 'S');
	}

	public function send_auto_po_summary() {
		$emails = $this->auto_po_summary->fetch_email_po();
	 	foreach ($emails as $row) {
          $supplier_email = $row->supplier_email;

          $email_adds  = explode(';', $supplier_email);

          if(is_array($email_adds)){
	            foreach ($email_adds as $rec) {
	            if($rec != null)
	                $summary_report = $this->create_po_pdf($rec);
          			$this->email_send($rec, $summary_report);
	            }
	      	}
        }
	}


	public function email_send($supplier_email, $summary_report){
		$this->load->library('my_phpmailer');

		$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
		try {
		    $mail->isSMTP();   
		    $mail->SMTPAuth   = true; // enabled SMTP authentication maam dito n p akosige
       		$mail->SMTPSecure = 'tls';                                   // Set mailer to use SMTP
            $mail->Host = 'mail.srssulit.com'; //"smtp.gmail.com";      // setting GMail as our SMTP 
       		$mail->Port       = 587;  //465; 587 ';  // Specify main and backup SMTP servers
		    $mail->Username = "no-reply@srssulit.com";
       		$mail->Password   = 'isdnoreply2019';  
		    $mail->setFrom('no-reply@srssulit.com', 'SAN ROQUE SUPERMARKET');
		    $mail->addReplyTo('no-reply@srssulit.com', 'SRS');
            $mail->addCC('sulitsrs12009@gmail.com');
		    //Content
		    $mail->isHTML(true);

	     	$mail->Subject    = "Purchase Order ";
			$body = " <p>Please see P.O summary attachment.</p> ";
			$mail->MsgHTML($body);  

			$mail->AddAddress($supplier_email);
			$mail->AddStringAttachment($summary_report, "TESTING.pdf");
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
}


