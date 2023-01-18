<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Auto_po_summary_model extends CI_Model {

	// public function fetch_email_po(){
 //        $this->ddb = $this->load->database('default_56', true);
 //        $sql = "select trim(supplier_email) as supplier_email, supplier_name from
 //                (select trans_id as ref,reference from refs
 //                where trans_id IN
 //                (
 //                select order_no from purch_orders where 
 //                cast(trans_date as date) >= '2019-06-07'
 //                and cast(trans_date as date) <= '2019-06-07'
 //                and trans_type = '16'
 //                ) and trans_type = '16'
 //                ) as a INNER JOIN purch_orders as p
 //                on a.ref = p.order_no and trans_type = '16' and email = 0  AND supplier_email != '' AND auto_generate = 1 GROUP BY supplier_email ORDER BY trans_date DESC "; 
 //        $query = $this->ddb->query($sql);
 //        return $query->result();
 //    }

    // public function fetch_po_list($email){
    //     $this->ddb = $this->load->database('default_56', true);
    //     $sql = "select  reference as ref, cast(trans_date as date) as date_created, cast(delivery_date as date) as delivery_date from
    //             (select trans_id as ref,reference from refs
    //             where trans_id IN
    //             (
    //             select order_no from purch_orders where 
    //             cast(trans_date as date) >= '2019-06-07'
    //             and cast(trans_date as date) <= '2019-06-07'
    //             and trans_type = '16'
    //             ) and trans_type = '16'
    //             ) as a INNER JOIN purch_orders as p
    //             on a.ref = p.order_no and trans_type = '16' and email = 0  AND supplier_email != '' AND auto_generate = 1 AND TRIM(supplier_email) = '".$email."'  ORDER BY trans_date DESC"; 
    //     $query = $this->ddb->query($sql);
    //     return $query->result();
    // }

    public function fetch_email_po() {
    	$this->ddb = $this->load->database('default_56', true);
    	$sql   = "SELECT supplier_email
    			  FROM 0_po_email_sent 
		    	  WHERE cast(created_date as date) >= '".date('Y-m-d')."'
		          AND cast(created_date as date) <= '".date('Y-m-d')."'
		   		  AND trans_type = '16' AND status = 0 GROUP BY supplier_email";
    	$query = $this->ddb->query($sql);
        return $query->result();
    }

     public function fetch_po_list($email, $supplier_id){
    	$this->ddb = $this->load->database('default_56', true);
    	$sql   = "SELECT po_no, delivery_date, created_date, b.name as branch_name, net_total FROM 0_po_email_sent a
                  INNER JOIN branches b ON b.code = a.br_code
                  WHERE TRIM(supplier_email) = '".$email."' 
                  AND status = 0 
                  AND trans_type = 16 
                  AND TRIM(supplier_id) = '".$supplier_id."' ";
    	$query = $this->ddb->query($sql);

        $this->ddb->where('supplier_id',$supplier_id);
        $this->ddb->where('trans_type',16);
        $this->ddb->where('status',0);
        $this->ddb->update('0_po_email_sent', array("status" => 1));
        return $query->result();
    }
}