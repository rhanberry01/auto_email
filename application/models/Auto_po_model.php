<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Auto_po_model extends CI_Model {

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

    public function update_consig_email($cons_id,$aria_db,$mail_items){
        $this->db = $this->load->database('aria_db', TRUE);
        $this->db->where('cons_sales_id',$cons_id);
        $this->db->update($aria_db.'.0_cons_sales_header',$mail_items);
    } 


    public function update_to_purch_orders($items,$id,$trans_type=null){
        $this->db = $this->load->database('default_56', TRUE);
        $this->db->where('order_no',$id);
        $this->db->where('trans_type',$trans_type);
        $this->db->update('purch_orders',$items);
    }

    public function get_po_prepared_by($trans_type, $order_no)
    {
        $this->db = $this->load->database('default_56', TRUE);
        $sql = "SELECT CONCAT(d.fname,' ',d.lname) as prepared_by, sign FROM purch_orders a, purch_orders b, refs c, users d
                    WHERE a.trans_type=16 
                    AND a.order_no = $order_no
                    AND a.trans_type = $trans_type
                    AND a.trans_ref = b.order_no
                    AND b.trans_type = 15
                    AND c.trans_id = b.order_no
                    AND c.trans_type = 15
                    AND c.user_id=d.id;";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function get_consignment($aria_db)
    {
        $this->db = $this->load->database('aria_db', TRUE);
        $sql = "select cs.*,cv.cv_date from $aria_db.0_cons_sales_header as cs 
        LEFT JOIN $aria_db.0_cv_header as cv
        on cs.cv_id=cv.id where start_date='2022-12-01' and cs.email_sent = 0 ";
        $query = $this->db->query($sql);
        return $query->result();
    }
    public function get_cons_details($cons_id,$aria_db)
    {
        $this->db = $this->load->database('aria_db', TRUE);
        $sql = "select * from  $aria_db.0_cons_sales_details where cons_det_id = ".$cons_id." ";
        $query = $this->db->query($sql);
        return $query->result();
    }
    

    public function get_consales_header($cons_id,$aria_db)
    {
        $this->db = $this->load->database('aria_db', TRUE);
        $sql = "select * from $aria_db.0_cons_sales_header as cs where cons_sales_id =".$cons_id." ";
        $query = $this->db->query($sql);
        return $query->row();
    }
    
    public function get_po_approved_by($trans_type, $order_no)
    {
        $this->db = $this->load->database('default_56', TRUE);
        $this->db->select("CONCAT(fname,' ',lname) as approved_by, users.sign",FALSE);
        $this->db->from('refs');
        $this->db->join('users','refs.user_id = users.id');
        $this->db->where('refs.trans_type =',$trans_type);
        $this->db->where('refs.trans_id =',$order_no);
        $query =  $this->db->get();
        return $query->result();
    }

    public function get_po_comments($order_no,$trans_type){
        $this->db = $this->load->database('default_56', TRUE);
        $this->db->select('comments.*');
        $this->db->from('comments');
        $this->db->where('comments.id =',$order_no);
        $this->db->where('comments.trans_type =',$trans_type);
        $query =  $this->db->get();
        return $query->result();
    }
    public function get_po_details($order_no = null,$trans_type=null){
        $this->db = $this->load->database('default_56', TRUE);
        $this->db->select('purch_order_details.*');
        $this->db->from('purch_order_details');
        $this->db->where('purch_order_details.order_no =',$order_no);
        $this->db->where('purch_order_details.trans_type =',$trans_type);
        $query =  $this->db->get();
        // echo $this->db->last_query();
        return $query->result();
    }

    public function get_company_profile(){
        $this->db = $this->load->database('default_56', TRUE);
        $this->db->trans_start();
            $this->db->select('company_profile.*');
            $this->db->from('company_profile');
            $query = $this->db->get();
            $result = $query->result();
        $this->db->trans_complete();
        return $result[0];
    }

    public function get_srs_suppliers_details($code=null){
        $this->db = $this->load->database('default_ms', TRUE);
       /*  $branch = $this->session->userdata('srs_branch');
        $this->sdb = $branch['database'];
        $this->db = $this->load->database($this->sdb, TRUE); */
        $this->db->select('vendor.vendorcode,
                      vendor.description,
                      vendor.address,
                      vendor.city,
                      vendor.zipcode,
                      vendor.contactperson,
                      vendor.country,
                      vendor.email,
                      terms.description as term_desc
                     ');
        $this->db->from('vendor');
        $this->db->join('terms','vendor.terms = terms.TermID','left');
    
        if($code != null)
            $this->db->where('vendor.vendorcode =',$code);
        $query =  $this->db->get();
        return $query->result();
    }
    


     public function get_custom_val($tbl,$col,$where,$val){
        if(is_array($col)){
            $colTxt = "";
            foreach ($col as $col_txt) {
                $colTxt .= $col_txt.",";
            }
            $colTxt = substr($colTxt,0,-1);
            $this->db->select($tbl.".".$colTxt);
        }
        else{
            $this->db->select($tbl.".".$col);
        }

        $this->db->from($tbl);
        $this->db->where($tbl.".".$where,$val);
        $query = $this->db->get();
        $result = $query->result();
        if(count($result) > 0){
            return $result[0];
        }
        else
            return "";
    }


    public function get_po_list_breif($ref=null,$trans_type=null,$status=null,$args=array(),$limit=array(),$join=array(),$sel=null,$valid = false){
        $this->db = $this->load->database('default_56', TRUE);
        $select = 'purch_orders.*,refs.reference as reference,branches.code as branch_code,
                   branches.name as branch_name, branches.tin as tin,
                   users.fname,users.mname,users.lname,users.suffix,(case purch_orders.auto_generate when 1 then 1 else 0 end) AS generate
                  ';
        if($sel != null)
            $select .= ', '.$sel;                 
        $this->db->select($select);
        $this->db->from('purch_orders');
        $and = "";
        if($trans_type != null)
            $and = " AND refs.trans_type = '".$trans_type."'";
        $this->db->join('refs','purch_orders.order_no = refs.trans_id '.$and);
        $this->db->join('branches','purch_orders.br_code = branches.code');
        $this->db->join('users','refs.user_id = users.id');
        if(!empty($join)){
            foreach ($join as $tbl => $use) {
                $this->db->join($tbl,$use['text'],$use['third']);
            }
        }
        if($ref != null )
            $this->db->where('refs.reference =',$ref);
        if($trans_type != null )
            $this->db->where('purch_orders.trans_type =',$trans_type);      
        if($status !== null){
            // echo "asd";
            $this->db->where('purch_orders.status =',$status);
        }

        if($valid !== false){
            $this->db->where('purch_orders.valid_date is NOT NULL', NULL, FALSE);
            $this->db->where('purch_orders.valid_date >=', date('Y-m-d'));
        }
                 $user = $this->session->userdata("user");
                if($user["role_id"] == 1) $this->db->order_by("draft", "desc");



        if(!empty($args)){
            foreach ($args as $col => $val) {
                
                if(is_array($val)){
                    if(!isset($val['use'])){
                        if(count($val)>0)
                            $this->db->where_in($col,$val);
                    }
                    else{
                        $func = $val['use'];
                        if(isset($val['third']))
                            $this->db->$func($col,$val['val'],$val['third']);
                        else
                            $this->db->$func($col,$val['val']);
                    }
                }
                else if ($col == 'purch_orders.supplier_name'){
                    $this->db->like($col,$val);
                }

                else if ($col == 'pr_ref.user_id' || $col == 'refs.user_id'){ 

                    if($args['pr_ref.user_id']){
                        if( $args['pr_ref.user_id'] == '56' || $args['pr_ref.user_id'] == '57' || $args['pr_ref.user_id'] == '58' ){
                            $this->db->where("pr_ref.user_id =".$val."");
                        }else{
                            $this->db->where("`purch_orders`.`supplier_id` IN (select vendor from user_vendor where user_id =".$val.")");
                        }

                    }else if($args['refs.user_id']){
                        if( $args['refs.user_id'] == '56' || $args['refs.user_id'] == '57' || $args['refs.user_id'] == '58' ){
                            $this->db->where("refs.user_id =".$val."");
                        }else{
                            $this->db->where("`purch_orders`.`supplier_id` IN (select vendor from user_vendor where user_id =".$val.")");
                        }

                    }else{
                        $this->db->where($col,$val);
                    }
                }else{
                    $this->db->where($col,$val);
                }
                

            }
        }
        $this->db->order_by('purch_orders.order_no desc');
        if(!empty($limit)){
            $this->db->limit($limit[0],$limit[1]);  
        }
        $query =  $this->db->get();

        //echo $this->db->last_query();
        return $query->result();
    }
 
    public function select_unsent_po(){
        $this->ddb = $this->load->database('default_56', true);
        $sql = "select trim(supplier_email) as supplier_email,supplier_id from
                (select trans_id as ref,reference from refs
                where trans_id IN
                (
                select order_no from purch_orders where 
                cast(trans_date as date) >= '2023-01-01'
                and trans_type = '16'
                ) and trans_type = '16'
                ) as a INNER JOIN purch_orders as p
                on a.ref = p.order_no and trans_type = '16' and email = 0  AND supplier_email != '' AND auto_generate = 1 GROUP BY supplier_email, supplier_id ORDER BY trans_date DESC "; 
        $query = $this->ddb->query($sql);
        return $query->result();

    }

    public function select_list_po_id_not_send($email, $supplier_id){

        $this->ddb = $this->load->database('default_56', true);
        $sql = "select  reference as ref,order_no, trans_type, supplier_id, cast(trans_date as date) as date_created, cast(delivery_date as date) as delivery_date, br_code, net_total from
                (select trans_id as ref,reference from refs
                where trans_id IN
                (
                select order_no from purch_orders where 
                cast(trans_date as date) >= '2023-01-01'
                and trans_type = '16'
                ) and trans_type = '16'
                ) as a INNER JOIN purch_orders as p
                on a.ref = p.order_no and trans_type = '16' and email = 0  AND supplier_email != '' AND auto_generate = 1 AND TRIM(supplier_email) = '".$email."' AND supplier_id = '".$supplier_id."'  ORDER BY trans_date DESC"; 
        $query = $this->ddb->query($sql);
        return $query->result();
    }

    public function add_po_email_send($data) {
        $this->ddb = $this->load->database('default_56', true);
        return $this->ddb->insert("0_po_email_sent", $data);
    }

    public function cc_supplier($supplier_id) {
        $this->ddb = $this->load->database('default_56', true);
        $sql    = "SELECT a.vendor,b.fname, b.lname, b.email FROM user_vendor a INNER JOIN users b ON a.user_id = b.id
                   WHERE a.vendor = '$supplier_id'";
        $query  = $this->ddb->query($sql);
        $result = $query->row();
        return ($result) ? $result : '' ;
    }

    public function send_only_one_supplier() {
        $this->ddb = $this->load->database('default_56', true);
        $sql    = "SELECT a.vendor FROM user_vendor a INNER JOIN users b ON a.user_id = b.id";
                 //  WHERE a.user_id = '33'";
        $query  = $this->ddb->query($sql);
        $result = $query->result();
        return ($result) ? $result : '' ;
    }


 public function get_total_esales($ms_db_133,$datefrom,$dateto){

       $month = date('m',strtotime($datefrom));
       $year = date('Y',strtotime($datefrom));

        $sql="select 
            '".$month."' as MonthNo,
            '".$year."' as YearNo,
            SUM(ISNULL(dt_3.mNetVATSales - isnull(dt_7.sukipoints,'0'),'0')) as mNetVATSales,
            SUM(ISNULL(dt_5.ZeroVatSles,'0')) as ZeroVatSles,
            SUM(ISNULL(dt_4.mNONVATSales,'0')) as mNONVATSales

            from 
            (SELECT Pos_No as TerminalNo, Tin as Tin ,Branch as Branch,Min_No as Min_No FROM Min_Info )as terminal

            LEFT JOIN
            (select TerminalNo as TerminalNo, sum(extended * multiplier) as mNetVATSales ,
            (sum(extended * multiplier) / 1.12) as DailyVatsales from finishedsales 
            where layaway=0 and voided=0 and ProductID IN (SELECT ProductID FROM [dbo].[Products] WHERE pVatable = 1) and pVatable != 2 and LogDate between '".$datefrom."' and '".$dateto."' group by TerminalNo ) AS dt_3
            ON terminal.TerminalNo = dt_3.TerminalNo

            LEFT JOIN
            (select TerminalNo as TerminalNo, sum(extended * multiplier) as mNONVATSales from finishedsales
            where layaway=0 and voided=0 and ProductID IN (SELECT ProductID FROM [dbo].[Products] WHERE pVatable = 0) and pVatable != 2
            and LogDate between '".$datefrom."' and '".$dateto."'
            group by TerminalNo )as dt_4
            ON terminal.TerminalNo = dt_4.TerminalNo

            LEFT JOIN
            (select TerminalNo as TerminalNo, sum(extended * multiplier) as ZeroVatSles from finishedsales
            where layaway=0 and voided=0 and pvatable=2  
            and LogDate between '".$datefrom."' and '".$dateto."'
            group by TerminalNo)as dt_5
            ON terminal.TerminalNo = dt_5.TerminalNo

            LEFT JOIN
            (select TerminalNo as TerminalNo, max(transactionno) as LastORNo from FinishedTransaction 
            where LogDate between '".$datefrom."' and '".$dateto."'
            group by TerminalNo)as dt_6
            ON terminal.TerminalNo = dt_6.TerminalNo


            LEFT JOIN
            (select distinct terminalno as TerminalNo ,sum(amount) as sukipoints  from FinishedPayments 
            where tendercode ='004'and voided=0 and LogDate between '".$datefrom."' and '".$dateto."' GROUP BY terminalno)as dt_7
            ON terminal.TerminalNo = dt_7.TerminalNo";

        $this->ddb = $this->load->database($ms_db_133, true);
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return $result;
        }else{
            return false;
        }



     }


      public function insert_consolidated_gp($details_){

        $this->ddb = $this->load->database('default', true);

          $this->ddb->where("month_", $details_['month_']);
          $this->ddb->where("year_", $details_['year_']);
          $this->ddb->where("database_", $details_['database_']);
          $this->ddb->delete("consolidated_gp");  

          $this->ddb->insert("consolidated_gp", $details_);

      }

     public function get_esales($ms_db_133,$datefrom,$dateto){

       $month = date('m',strtotime($datefrom));
       $year = date('Y',strtotime($datefrom));

        $sql="select 
            terminal.Tin,
            terminal.Branch as BranchNo,
            '".$month."' as MonthNo,
            '".$year."' as YearNo,
            terminal.Min_No as Min_Info,
            ISNULL(dt_6.LastORNo,'0') as LastORNo,
            ISNULL(dt_3.mNetVATSales - isnull(dt_7.sukipoints,'0'),'0') as mNetVATSales,
            ISNULL(dt_5.ZeroVatSles,'0') as ZeroVatSles,
            ISNULL(dt_4.mNONVATSales,'0') as mNONVATSales,
            0 as SST

            from 
            (SELECT Pos_No as TerminalNo, Tin as Tin ,Branch as Branch,Min_No as Min_No FROM Min_Info )as terminal

            LEFT JOIN
            (select TerminalNo as TerminalNo, sum(extended * multiplier) as mNetVATSales ,
            (sum(extended * multiplier) / 1.12) as DailyVatsales from finishedsales 
            where layaway=0 and voided=0 and ProductID IN (SELECT ProductID FROM [dbo].[Products] WHERE pVatable = 1) and pVatable != 2   and LogDate between '".$datefrom."' and '".$dateto."' group by TerminalNo ) AS dt_3
            ON terminal.TerminalNo = dt_3.TerminalNo

            LEFT JOIN
            (select TerminalNo as TerminalNo, sum(extended * multiplier) as mNONVATSales from finishedsales
            where layaway=0 and voided=0 and ProductID IN (SELECT ProductID FROM [dbo].[Products] WHERE pVatable = 0) and pVatable != 2  
            and LogDate between '".$datefrom."' and '".$dateto."'
            group by TerminalNo )as dt_4
            ON terminal.TerminalNo = dt_4.TerminalNo

            LEFT JOIN
            (select TerminalNo as TerminalNo, sum(extended * multiplier) as ZeroVatSles from finishedsales
            where layaway=0 and voided=0 and pvatable=2     
            and LogDate between '".$datefrom."' and '".$dateto."'
            group by TerminalNo)as dt_5
            ON terminal.TerminalNo = dt_5.TerminalNo

            LEFT JOIN
            (select TerminalNo as TerminalNo, max(transactionno) as LastORNo from FinishedTransaction 
            where LogDate between '".$datefrom."' and '".$dateto."'
            group by TerminalNo)as dt_6
            ON terminal.TerminalNo = dt_6.TerminalNo


            LEFT JOIN
            (select distinct terminalno as TerminalNo ,sum(amount) as sukipoints  from FinishedPayments 
            where tendercode ='004'and voided=0 and LogDate between '".$datefrom."' and '".$dateto."' GROUP BY terminalno)as dt_7
            ON terminal.TerminalNo = dt_7.TerminalNo
            order by terminal.TerminalNo";

        $this->ddb = $this->load->database($ms_db_133, true);
        $result = $this->ddb->query($sql);
        $result = $result->result();
        if($result){
            return $result;
        }else{
            return false;
        }



     }




     public function get_data($br,$year,$value,$s,$e,$ms_db){

        if($br == 'organic' || $br == 'total'){

            $month = $s;
            $total_days_of_the_month =  $e;
            $year = $year;

             $sql_cat = "select sum(a.sales) as sales,sum(a.GP) as GP from
            (
            select 'TALA' as br,sales,GP from SRSMALAM.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TAQU' as br,sales,GP from SRSMANT1GF.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TAMA' as br,sales,GP from SRSMANT2EM.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TBGB' as br,sales,GP from SRSMBAG.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TBAG' as br,sales,GP from SRSMBSL.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TCAI' as br,sales,GP from SRSMCAINTA.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TCAT' as br,sales,GP from SRSMCAINTA2.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TCAM' as br,sales,GP from SRSMCAMA.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TGAG' as br,sales,GP from SRSMGAL.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TIMU' as br,sales,GP from SRSMIMU.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TCOM' as br,sales,GP from SRSMKUM.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TMAL' as br,sales,GP from SRSMMALA.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TMOL' as br,sales,GP from SRSMMOL.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TGVL' as br,sales,GP from SRSMMUZ.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TNAV' as br,sales,GP from SRSMNAVO.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TNOV' as br,sales,GP from SRSMNOVA.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TPAT' as br,sales,GP from SRSMPAT.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TSAN' as br,sales,GP from SRSMPEDRO.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TLAP' as br,sales,GP from SRSMPINAS.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TPUN' as br,sales,GP from SRSMPUN.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TTON' as br,sales,GP from SRSMTON.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            UNION ALL
            select 'TVAL' as br,sales,GP from SRSMVAL.dbo.consolidated_cat_for_gp
            where months = ".$month." and years = ".$year." and desc_code ='".$value."'
            )  as a
            ";

            if($br =='organic'){
                $sql_cat .=" where a.br not in('TBGB','TGVL','TMOL')";
            }

            $sql_sales ="select ROUND(sum(sales_revunue),2) as sales_revunue,
                    ROUND(sum(sales_revunue)/31,2) as DAS,
                    ROUND(sum(before_adjustment),2) as before_adjustment,
                    ROUND(((sum(before_adjustment)/sum(sales_revunue)) *100),2) as GBP,
                    ROUND(sum(inventory_adjustment),2) as inventory_adjustment,
                    ROUND((sum(before_adjustment) + sum(inventory_adjustment)),2) as after_adjustment,
                    ROUND((((sum(before_adjustment) + sum(inventory_adjustment))/ sum(sales_revunue)) *100),2) as GAP
                    from consolidated_gp
                    where month_ =".$month." and year_ = ".$year."";

            if($br =='organic'){
                $sql_sales .=" and database_ not in('TBGB','TGVL','TMOL')";
            }

            if($value == 'Conso'){

                $this->ddb = $this->load->database('default', true);
                $result = $this->ddb->query($sql_sales);
                $result = $result->row();
                if($result){
                    return array($result->sales_revunue,
                                $result->DAS,
                                $result->before_adjustment,
                                $result->GBP,
                                $result->inventory_adjustment,
                                $result->after_adjustment,
                                $result->GAP);
                }else{

                    return array(0,0,0,0,0,0);
                }

            }else{

                $this->ddb = $this->load->database('srsnov', true);
                $result = $this->ddb->query($sql_cat);
                $result = $result->row();
                if($result){
                    return array($result->sales,$result->GP);
                }else{

                    return array(0,0);
                }
            }



        }else{

            $month = $s;
            $total_days_of_the_month =  $e;
            $year = $year;

            switch ($value) {

            case 'Liquor':
                $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='9045'";

                break;
            case 'Cigarette':
                $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='9019'";
                break;
            case 'Depstore':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='DEP101'";
                break; 
            case 'Rice':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='RICE101'";
                break;
            case 'Egg':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='EGG101'";
                break;
            case 'Vegetable':
                $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='VEG101'";
                break;
            case 'Pork':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='PORK101'";
                break;
            case 'Chicken':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='CHICK101'";
                break;
            case 'Beef':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='BEEF101'";
                break;
            case 'Fish':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='FISH101'";
                break;
            case 'Conso':
                    $month = $s;
                    $total_days_of_the_month =  $e;
                    $year = $year;
                    $ms_db = $ms_db;
                    $sql ="select ROUND(sales_revunue,2) as sales_revunue,
                            ROUND(sales_revunue/31,2) as DAS,
                            ROUND(before_adjustment,2) as before_adjustment,
                            ROUND(before_adjustment/sales_revunue *100,2) as GBP,
                            ROUND(inventory_adjustment,2) as inventory_adjustment,
                            ROUND((before_adjustment + inventory_adjustment),2) as after_adjustment,
                            ROUND((before_adjustment + inventory_adjustment) / sales_revunue *100,2) as GAP
                            from consolidated_gp
                            where month_ =".$month." and year_ = ".$year." and database_ ='".$ms_db."' ";
                   
                break;
            case 'Override':
                 $sql = "select sales,GP from consolidated_cat_for_gp
                        where months = ".$month." and years = ".$year." and code ='OVR101'";
                break;
           
            default:
               return array(0,0);
                
        }

         // return  $sql;

            if($value == 'Conso'){
                $this->ddb = $this->load->database('default', true);
                $result = $this->ddb->query($sql);
                $result = $result->row();
                if($result){
                    return array($result->sales_revunue,
                                $result->DAS,
                                $result->before_adjustment,
                                $result->GBP,
                                $result->inventory_adjustment,
                                $result->after_adjustment,
                                $result->GAP);
                }else{

                    return array(0,0,0,0,0,0,0);
                }

            }else{
                $this->ddb = $this->load->database($br, true);
                $result = $this->ddb->query($sql);
                $result = $result->row();
                if($result){
                    return array($result->sales,$result->GP);
                }else{

                    return array(0,0);
                }
            }
        }

     }


     public function get_data_($br,$year,$value,$s,$e,$ms_db){



        $sdate = $year.'-'.$s;
        $edate = $year.'-'.$e;

        switch ($value) {
            case 'Liquor':
                $sql = "select 
                        sum(round(extended / case when fs.Pvatable = 1 then 1.12 else 1 end,4)) as sales,
                        sum(round(extended / case when fs.Pvatable = 1 then 1.12 else 1 end,4)) - sum((averageunitcost / case when fs.Pvatable = 0 then 1 else 1.12 end) * case when [return]= 1 then convert(money,0-totalqty) else totalqty end) as GP                   
                        from FinishedSales as fs
                        where cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."'
                        AND fs.ProductID IN
                        (select ProductID from Products where LevelField1Code ='9045') AND Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Cigarette':
                $sql = "select 
                        sum(round(extended / case when fs.Pvatable = 1 then 1.12 else 1 end,4)) as sales,
                        sum(round(extended / case when fs.Pvatable = 1 then 1.12 else 1 end,4)) - sum((averageunitcost / case when fs.Pvatable = 0 then 1 else 1.12 end) * case when [return]= 1 then convert(money,0-totalqty) else totalqty end) as GP                   
                        from FinishedSales as fs
                        where cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."'
                        AND fs.ProductID IN
                        (select ProductID from Products where LevelField1Code ='9019') AND  Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Depstore':
                $sql = "select sum(Extended)/1.12 as sales, 
                        ((sum(Extended)/1.12) - (SUM((averageunitcost /1.12) * (case when [return]=1 then convert(money,0-totalqty) else totalqty end)))) as GP
                        from FinishedSales as fs
                        where  cast(LogDate as date) >= '".$sdate."'  
                        AND cast(LogDate as date) <= '".$edate."'
                        AND fs.ProductID IN
                        (select ProductID from 
                        Products where 
                         LevelField1Code in ('9074', '9075', '9082', '10001', '10025', '9099', '9007', '9087', '10023','9070',
                        '9071','10042','9072','9069','10048','9073',
                        '9078','9998', '10000', '9025', '9080', '9079', '9081', '9077', '10050','G001','G002','9038','9999','9046'))
                        AND Voided = 0
                        ";
                break; 
            case 'Rice':
                $sql = "select sum(Extended) as 'sales',((sum(Extended)) - (SUM((averageunitcost) * (case when [return]=1 then convert(money,0-totalqty) else totalqty end)))) as GP
                        from FinishedSales as fs
                        where  cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."' AND fs.Barcode IN
                        ('999000715727','999000748169','999000480816','999000594933','999000749227','999000749210','999000783719','999000783696','999000783702','999000781791','999000787656','999000787649',
                        '999000797489','1018','1024','1028','1035','1038','1039','1047','1048','1052','1055','1092','1215','1227','7001','7002','7008','7009','7016','7066','7070','7071','7072','7073','7074',
                        '7075','7076','7077','7078','7088','7286','7287','7288','7289','7291','7292','7293','7294','1300','7307','7308','7309','7310','7312','7367','7368','7370','7371','7374','7382','7383','7400','7401','7393',
                        '7394','7408','7409','7410','7411','7412','7413','7414','7415','7416','7417','7418','7419','7423','7424','7425','7426','7439','7440','7441','7442','7443','7444','7446','7447','7448','7449','7450','7451','7452','7486','7487','7489','7490','7491','7492','7493','7494','7495','7496','7497','7498','7499','7502','7503','7504','7505','7506','7531','7422','7631','7632','8919','8921','8923','8924','8925','8926','8928','8929','8930','8931','8932','8933','8934','8935',
                        '8936','8937','8938','8939','8940','8941','8942','8943','8945','8944','8946','8947','8948','8949','8952','8953','8954','8955','8956','8957','8958','8959','8960','8961','8962','8965','8966','8963','8964','8974','8969','8972','8973','8968',
                        '8971','8970','8975','8976','8978','8979','8980','8982','8983','8984','8981','9000','9001','9002','9005','9006','9007','9008','9009','9010','9011','9012','9013','9014','9015','9016','9017','9019','9020','9021','9022',
                        '9026','9027','9028','9029','9030','9031','9035','9036','9037','9038','9039','9040','9041','9036','9047','9045','9046','9049','9048','9043','9044','9042','9050','9051','9052','9053','9056','9057','9057','9059','9060','9061','9062','9063',
                        '9076','9077','9078','9079','9079','9080','9081','9073','9074','9084','9085','9090','9064','9071','9066','9067','9092','9291','9302','9301','9303','9309','9310','9311','9312','9315','9319','9320','9321','9633','9634','6651','6581','6411',
                        '6341','9696','3702','3696','3719','1791','7649','7656','9827', '6109'
                        ) 
                        AND Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Egg':
                $sql = "select sum(Extended) as 'sales',((sum(Extended)) - (SUM((averageunitcost) * (case when [return]=1 then convert(money,0-totalqty) else totalqty end)))) as GP
                        from FinishedSales as fs
                        where cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."'
                        AND fs.Barcode IN
                        ('999000464656',
                        '999000464670',
                        '999000464694',
                        '999000464717',
                        '999000605783',
                        '999000464663',
                        '999000464687',
                        '999000464700',
                        '999000464724'
                        ) AND  Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Vegetable':
                $sql = "select sum(Extended) as 'sales',
                        ((sum(Extended)) - (SUM((averageunitcost) * (case when [return]=1 then convert(money,0-totalqty) else totalqty end)))) as GP
                        from FinishedSales as fs
                        where cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."' 
                        AND  fs.Barcode IN
                        ('1007','1013','1056','1057','1058','1059','1060','1062','1063','1064','1065','1066','1067','1068','1069','1072','1073','1074','1075','1076','1077','1078','1079','1080','1081',
                        '1082','1083','1084','1085','1086','1088','1090','1091','1095','1097','1100','1200','1206','1210','1217','1218','1220','1221','1226','7004','7030','7033','7034','7037','7038','7039',
                        '7045','7046','7050','7051','7059','7065','7079','7086','7295','1098','7300','7306','7328','7329','7361','8920','8921') AND Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Pork':
                $sql = "select sum(Extended) as 'sales',((sum(Extended)) - (SUM((averageunitcost) * (case when [return]=1 then convert(money,0-totalqty) else totalqty end)))) as GP
                        from FinishedSales as fs
                        where cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."'
                        AND fs.Barcode IN
                        ('7120','7129','7161','7130','7151','7164','7154','7133','7152','7160','7134','9023','7135','7166','7136','7137','7138','7139','9024',
                        '7140','7141','7142','7155','7143','7144','7159','7281','7290','7298','7145','7406','8111','7501','8501','8516','9032', '9058'
                        ) AND Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Chicken':
                $sql = "select sum(Extended) as 'sales',((sum(Extended)) - (SUM((averageunitcost) * (case when [return]=1 then convert(money,0-totalqty) else totalqty end)))) as GP
                        from FinishedSales as fs
                        where cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."'
                        AND  fs.Barcode IN
                        ('7321','8992','8993','8994','7325','8995','7319','7318','8996','9004','8518','8951','8967','9033', '9091'
                        ) AND Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Beef':
                $sql = "select sum(Extended) as 'sales',((sum(Extended)) - (SUM((averageunitcost) * (case when [return]=1 then convert(money,0-totalqty) else totalqty end)))) as GP
                        from FinishedSales as fs
                        where cast(LogDate as date) >= '".$sdate."' 
                        AND cast(LogDate as date) <= '".$edate."'
                        AND fs.Barcode IN
                        ('7090','7091','7092','7096','7097','7167','7156','7098','7168','7099','7100','7101','9025','7094','7121','7117','7345','7375','7376','7377','7378','7462','9917','8910','8911','8927',
                        '9034') AND Voided = 0 GROUP BY MONTH(fs.logdate)";
                break;
            case 'Conso':
                    $month = $s;
                    $total_days_of_the_month =  $e;
                    $year = $year;
                    $ms_db = $ms_db;
                    $sql ="select ROUND(sales_revunue,2) as sales_revunue,
                            ROUND(sales_revunue/31,2) as DAS,
                            ROUND(before_adjustment,2) as before_adjustment,
                            ROUND(before_adjustment/sales_revunue *100,2) as GBP,
                            ROUND(inventory_adjustment,2) as inventory_adjustment,
                            ROUND((before_adjustment + inventory_adjustment),2) as after_adjustment,
                            ROUND((before_adjustment + inventory_adjustment) / sales_revunue *100,2) as GAP
                            from consolidated_gp
                            where month_ =".$month." and year_ = ".$year." and database_ ='".$ms_db."' ";
                   
                break;
            case 'Override':
                $sql = "select
                        sum(y.proverride) as sales,
                        0 as GP
                        from 
                        (select FinishedSales.lineid as fid, 
                        MONTH(Finishedsales.logdate) AS monthS,
                        YEAR(Finishedsales.logdate) AS yearS,
                        pricechangehistory.dateposted as dateposted, 
                        FinishedSales.productid as productid ,
                        FinishedSales.barcode as barcode, 
                        FinishedSales.Price as Price,
                        FinishedSales.TotalQty as TotalQty,
                        FinishedSales.packing as packing,
                        FinishedSales.Extended as Extended,
                        ISNULL(pricechangehistory.tosrp, pp.srp) as ShouldBeSRP,
                        ((CASE WHEN FinishedSales.Extended < 0 THEN 0 - (FinishedSales.TotalQty / FinishedSales.packing) ELSE (FinishedSales.TotalQty / FinishedSales.packing) END)  * CASE WHEN FinishedSales.pVatable = 2 THEN ISNULL(pricechangehistory.tosrp, pp.srp) / 1.12 ELSE ISNULL(pricechangehistory.tosrp, pp.srp) END ) as ShouldBeExtended,
                        ((CASE WHEN FinishedSales.Extended < 0 THEN 0 - (FinishedSales.TotalQty / FinishedSales.packing) ELSE (FinishedSales.TotalQty / FinishedSales.packing) END)  * CASE WHEN FinishedSales.pVatable = 2 THEN ISNULL(pricechangehistory.tosrp, pp.srp) / 1.12 ELSE ISNULL(pricechangehistory.tosrp, pp.srp) END ) - FinishedSales.Extended  as proverride
                        from FinishedSales
                        LEFT JOIN  POS_Products as pp on FinishedSales.ProductID = pp.ProductID and  FinishedSales.Barcode = pp.Barcode 
                        left join pricechangehistory on pricechangehistory.productid = FinishedSales.ProductID 
                        and pricechangehistory.barcode = FinishedSales.Barcode 
                        and pricechangehistory.lineid = (select max(lineid) from pricechangehistory where productid = FinishedSales.ProductID and 
                        barcode = FinishedSales.Barcode and cast(dateposted as date)<= cast(FinishedSales.LogDate as date))
                        where cast(FinishedSales.LogDate as date) >='".$sdate."'  and  cast(FinishedSales.LogDate as date) <='".$edate."'
                        and  FinishedSales.voided = 0 and PriceOverride = 1  
                        ) as y
                        GROUP BY y.monthS,y.yearS ORDER BY  y.yearS desc";
                break;
            default:
               return array(0,0);
                
        }

        if($value == 'Conso'){
            $this->ddb = $this->load->database('default', true);
            $result = $this->ddb->query($sql);
            $result = $result->row();
            if($result){
                return array($result->sales_revunue,
                            $result->DAS,
                            $result->before_adjustment,
                            $result->GBP,
                            $result->inventory_adjustment,
                            $result->after_adjustment,
                            $result->GAP);
            }else{

                return array(0,0);
            }

        }else{
            $this->ddb = $this->load->database($br, true);
            $result = $this->ddb->query($sql);
            $result = $result->row();
            if($result){
                return array($result->sales,$result->GP);
            }else{

                return array(0,0);
            }

        }

       

     }

     public function get_override($br,$year,$value,$s,$e){
        $sdate = $year.'-'.$s;
        $edate = $year.'-'.$e;
        $this->ddb = $this->load->database($br, true);
        $sql = "
            select
            sum(y.proverride) as override
            from 
            (select FinishedSales.lineid as fid, 
            MONTH(Finishedsales.logdate) AS monthS,
            YEAR(Finishedsales.logdate) AS yearS,
            pricechangehistory.dateposted as dateposted, 
            FinishedSales.productid as productid ,
            FinishedSales.barcode as barcode, 
            FinishedSales.Price as Price,
            FinishedSales.TotalQty as TotalQty,
            FinishedSales.packing as packing,
            FinishedSales.Extended as Extended,
            ISNULL(pricechangehistory.tosrp, pp.srp) as ShouldBeSRP,
            ((CASE WHEN FinishedSales.Extended < 0 THEN 0 - (FinishedSales.TotalQty / FinishedSales.packing) ELSE (FinishedSales.TotalQty / FinishedSales.packing) END)  * CASE WHEN FinishedSales.pVatable = 2 THEN ISNULL(pricechangehistory.tosrp, pp.srp) / 1.12 ELSE ISNULL(pricechangehistory.tosrp, pp.srp) END ) as ShouldBeExtended,
            ((CASE WHEN FinishedSales.Extended < 0 THEN 0 - (FinishedSales.TotalQty / FinishedSales.packing) ELSE (FinishedSales.TotalQty / FinishedSales.packing) END)  * CASE WHEN FinishedSales.pVatable = 2 THEN ISNULL(pricechangehistory.tosrp, pp.srp) / 1.12 ELSE ISNULL(pricechangehistory.tosrp, pp.srp) END ) - FinishedSales.Extended  as proverride
            from FinishedSales
            LEFT JOIN  POS_Products as pp on FinishedSales.ProductID = pp.ProductID and  FinishedSales.Barcode = pp.Barcode 
            left join pricechangehistory on pricechangehistory.productid = FinishedSales.ProductID 
            and pricechangehistory.barcode = FinishedSales.Barcode 
            and pricechangehistory.lineid = (select max(lineid) from pricechangehistory where productid = FinishedSales.ProductID and 
            barcode = FinishedSales.Barcode and cast(dateposted as date)<= cast(FinishedSales.LogDate as date))
            where cast(FinishedSales.LogDate as date) >='".$sdate."'  and  cast(FinishedSales.LogDate as date) <='".$edate."'
            and  FinishedSales.voided = 0 and PriceOverride = 1  
            ) as y
            GROUP BY y.monthS,y.yearS ORDER BY  y.yearS desc";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return array($result->override);
        }else{

            return array(0);
        }

     }


     public function insert_aria_to_esales($aria_db,$month,$year,$salesv,$salesnv,$saleszv){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "INSERT INTO x_fsales_monthly (month, year_, sales_vat, sales_nv, sales_zero_vat)
                  VALUES ($month,$year, $salesv, $salesnv,$saleszv)";
        $result = $this->ddb->query($sql);
        return  $result;
     }


      public function gen_rep_database_all(){
        //where ms_db = 'TGVL'
       // where ms_db IN('TNOV','TGVL') and ms_db in('TIMU','TSAN')
        $this->ddb = $this->load->database('default', true);
        $sql  ="select aria_db,branch_name, ms_db,ms_db_133 from auto_aria_branches 
            where aria_db NOT IN('srs_aria_retail','organic','total')";
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;
     }

     public function gen_rep_database_retail($batch_db){
        $this->ddb = $this->load->database('default', true);
        //where ms_db in('TIMU','TSAN')
        $sql = "select aria_db,branch_name, ms_db,ms_db_133 from auto_aria_branches where";
             if($batch_db){
             $sql .=" ms_db in(".$batch_db.") and ";    
            }
         $sql .="  aria_db NOT IN('organic','total','srsmalr') ORDER BY branch_name";
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;
     }
     public function gen_rep_database_server($batch_db){
        $this->ddb = $this->load->database('default', true);
        //where ms_db in('TIMU','TSAN')
        $sql = "select ms_db from auto_aria_branches where ms_db_133 = '".$batch_db."' ";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        return  $result->ms_db;

     }


     public function gen_rep_database($batch_db = null){
        //where ms_db = 'TGVL'
       // where ms_db IN('TNOV','TGVL') and ms_db in('TIMU','TSAN')
        $this->ddb = $this->load->database('default', true);
        $sql  ="select aria_db,branch_name, ms_db,ms_db_133 from auto_aria_branches where aria_db !='srs_aria_retail' and ms_db not in('ZORGANIC','ZTOTAL')";
            if($batch_db){
             $sql .="and ms_db in(".$batch_db.")";    
            }
        $sql .="ORDER BY branch_name";
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;
     }

     
     public function get_gl_account($branch_server,$type,$type_no,$account){
        $this->ddb = $this->load->database($branch_server, true);
        $sql = "select tran_date,type,type_no,account,amount from 0_gl_trans as gl
                where cast(gl.tran_date as date) >='2018-01-01' 
                and cast(gl.tran_date as date) <='2018-12-31'
                and gl.type = '".$type."' and  type_no ='".$type_no."' ";
        if($account){
            $sql .= " ".$account." ";
        }

        $result = $this->ddb->query($sql);
        $result = $result->row();
        return  $result;

     }


     public function check_supp_trans_if_paid($branch_server,$type,$type_no){
        $this->ddb = $this->load->database($branch_server, true);
        $sql = "select cv_id, bank_trans_id from 0_supp_trans
                where type = '".$type."' and trans_no ='".$type_no."'";

        $result = $this->ddb->query($sql);
        $result = $result->row();
        return  $result;
        echo $sql.PHP_EOL;


     }


     public function upd_recompute_supp_trans($branch_server,$type,$type_no,$amount){

        $this->ddb = $this->load->database($branch_server, true);
        $sql = "UPDATE  0_supp_trans 
                SET ewt = ".$amount."
                where trans_no ='".$type_no."' and  type = '".$type."'";

        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }


     }


     public function upd_recompute_gl($branch_server,$account,$type,$type_no,$amount){
        $this->ddb = $this->load->database($branch_server, true);
        $sql = "UPDATE  0_gl_trans as gl
                set amount = ".$amount."
                where cast(gl.tran_date as date) >='2018-01-01' 
                and cast(gl.tran_date as date) <='2018-12-31'
                and gl.type = '".$type."' and  type_no ='".$type_no."' and account = '".$account."' ";

        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }

     }


     public function get_ewt_by_type_w_diff($branch_server,$from,$to){

        $this->ddb = $this->load->database($branch_server, true);
        $sql = "SELECT a.type,a.type_no,a.amount as net, a.amount *0.01 AS EWT, (b.amount + a.amount *0.01) as diff FROM 
                (SELECT type,type_no,account,amount from 0_gl_trans
                where cast(tran_date as date) >='".$from."' and cast(tran_date as date) <='".$to."'
                and type ='24' and account = '5450') as a
                INNER JOIN 
                (SELECT type,type_no,account,amount from 0_gl_trans
                where cast(tran_date as date) >='".$from."' and cast(tran_date as date) <='".$to."'
                and type ='24' and account = '23303158') as b
                on a.type_no = b.type_no
                 having diff != 0 ";
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;

     }


     public function get_gl_by_type_w_diff($branch_server,$from,$to){

        $this->ddb = $this->load->database($branch_server, true);
        $sql = "select tran_date,type,type_no,amount from (SELECT tran_date,type,type_no,sum(amount) as amount 
                FROM 0_gl_trans
                where tran_date>='".$from."' and tran_date<='".$to."' 
                GROUP BY type,type_no) as a
                where amount<-1
                UNION ALL
                select tran_date,type,type_no,amount from (SELECT tran_date,type,type_no,sum(amount) as amount 
                FROM 0_gl_trans
                where tran_date>='".$from."' and tran_date<='".$to."' 
                GROUP BY type,type_no) as a
                where amount>1 ORDER BY type";
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;


     }


     public function get_database($batch_db = null){

        $this->ddb = $this->load->database('migration', true);
        $sql = "select aria_db, ms_db from auto_aria_branches where ms_db not in('ZORGANIC','ZTOTAL')";
         if($batch_db){
             $sql .=" and aria_db in('".$batch_db."')";    
            }
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;
     }


      public function get_database_order(){

        $this->ddb = $this->load->database('default', true);
        $sql = "select aria_db, ms_db from auto_aria_branches order by branch_name";
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;
     }



    public function delete_gl_60($sales_date = null,$aria_db = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "DELETE FROM 0_gl_trans where type = '60' and tran_date between '".$sales_date."' and '".$sales_date."' and account in('4000040','4000020','4000','2310','4000050')";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }
    }

    public function delete_gl_60_1060000($sales_date = null,$aria_db = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "DELETE FROM 0_gl_trans where type = '60' and tran_date between '".$sales_date."' and '".$sales_date."' 
        and account in(1060000)";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }
    }



    public function insert_gl_60($sales_date = null,$aria_db = null, $get_old_trans_no = null,$gross_sales = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "INSERT INTO 0_gl_trans (type, type_no, tran_date,account,amount,memo_) VALUES ('60', ".$get_old_trans_no.", '".$sales_date."','1060000',".-$gross_sales.",'')";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }
    }



    public function get_old_trans_no($sales_date = null,$aria_db = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "Select type_no from 0_gl_trans where tran_date between '".$sales_date."' and '".$sales_date."' and type ='60' and amount != 0 limit 1";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return  $result->type_no;
        }
    }


    public function get_old_sales($sales_date = null,$aria_db = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "Select account from 0_gl_trans where tran_date between '".$sales_date."' and '".$sales_date."' and type ='60' and account ='1060000' and amount != 0 limit 1";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return  $result->account;
        }
    }



    public function get_ms_sales($ms_db){

        $past_30days = '2018-05-01'; 
        $end_date = '2018-05-31';

        $this->ddb = $this->load->database($ms_db, true);
        $sql = "SELECT cast (LogDate as Date) as LogDate ,(SUM(ft.GrandTotal)-SUM(ft.ReturnSubtotal)) as total
                FROM FinishedTransaction as ft
                WHERE LogDate >= '".$past_30days."' and LogDate<= '".$end_date."'
                AND Voided='0'
                group by LogDate
                order by LogDate";
        $result = $this->ddb->query($sql);
        $result = $result->result();
        return  $result;
     }



     public function get_nv_sales($sales_date = null,$ms_db = null){
        $this->ddb = $this->load->database($ms_db, true);
        $sql = "SELECT SUM(Extended) as sales FROM [dbo].[FinishedSales] 
                WHERE LogDate = '".$sales_date."'
                AND Voided = 0 AND ProductID IN (SELECT ProductID FROM [dbo].[Products] WHERE pVatable = 0) and pVatable != 2";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        return  $result->sales;
     }



    public function get_zr_sales($sales_date = null,$ms_db = null){
        $this->ddb = $this->load->database($ms_db, true);
        $sql = "SELECT SUM(Extended) as sales FROM [dbo].[FinishedSales] 
                WHERE LogDate = '".$sales_date."'
                AND Voided = 0 
                AND pVatable = 2";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        return  $result->sales;
     }


    public function get_sales_collection($sales_date = null,$aria_db = null,$type = null, $account = null){

        $this->ddb = $this->load->database($aria_db, true);
        $sql = "SELECT SUM(ABS(amount))+0 as gl_amount FROM  0_gl_trans where tran_date = '".$sales_date."' and type ='".$type."' and account IN (".$account.")";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return  $result->gl_amount;
        }else{
            return  0;
        }

    }


    public function get_suki_points_sales($sales_date = null,$ms_db = null){
        $this->ddb = $this->load->database($ms_db, true);
        $sql = "select sum(amount) as sukipoints  from FinishedPayments where tendercode ='004'and voided=0 and LogDate='".$sales_date."'";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return  $result->sukipoints;
        }else{
            return  false;
        }
    }

    public function get_existed_sales($sales_date = null,$aria_db = null){

        $this->ddb = $this->load->database($aria_db, true);
        $sql = "SELECT type_no FROM  0_gl_trans where tran_date = '".$sales_date."' and type ='100'";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return  $result->type_no;
        }else{
            return  false;
        }
    }

    public function get_ref($aria_db = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "select  max(CAST(reference AS UNSIGNED)) as max_ref from 0_refs  where type ='100'";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return  $result->max_ref + 1;
        }else{
            return false;
        }

    }


    public function get_next_trans_no($aria_db = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "SELECT max(type_no) as max_type_no from 0_gl_trans WHERE type ='100'";
        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return  $result->max_type_no + 1;
        }else{
            return false;
        }

    }


    public function delete_gl($sales_date = null,$aria_db = null,$get_existed_sales = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "DELETE FROM 0_gl_trans where tran_date = '".$sales_date."' and type ='100' and type_no ='".$get_existed_sales."'";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }
    }


     public function insert_gl($aria_db = null,$type = null, $type_no = null, $sales_date = null,$memo =null, $amount = null,$account = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "INSERT INTO 0_gl_trans (type, type_no, tran_date,account,amount,memo_) VALUES ('".$type."', ".$type_no.", '".$sales_date."','".$account."',".-$amount.",'".$memo."')";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }
    }

     public function add_comments($aria_db = null,$type = null, $max_type_no = null, $sales_date = null, $memo = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "INSERT INTO 0_comments (type, id, date_, memo_) VALUES (".$type.",".$max_type_no.",'".$sales_date."','".$memo."')";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }

     }

     public function add_refs($aria_db = null,$type = null, $max_type_no = null, $refs = null){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "INSERT INTO 0_comments (id,type,reference) VALUES (".$max_type_no.",".$type.",'".$refs."')";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }


     }



    public function add_audit_trail($aria_db,$trans_type, $trans_no, $trans_date, $descr='')
    {
        
        $ip = $this->input->ip_address();

        $this->ddb = $this->load->database($aria_db, true);
        $sql = "INSERT INTO 0_audit_trail"
                . " (type, trans_no, user, fiscal_year, gl_date, description, gl_seq, remote_address)
                VALUES(".$trans_type.", ".$trans_no.","
                .'1'. ","
                .'1'.","
                . "'".date("Y-m-d h:i:sa")."',"
                . $descr. ", NULL,"
                . $ip. ")";
        $result = $this->ddb->query($sql);
        if($result){
            return  true;
        }else{
            return false;
        }

    }

    public function get_ms_inv($ms_db,$date){
        $this->ddb = $this->load->database($ms_db, true);
        $month = date('m',strtotime($date));
        $year = date('Y',strtotime($date));
        $details = array();
        $sql ="select begs as [beg],ends as [end],months,years from consolidated_inventory where months =".$month."  and years = ".$year."";
        $result = $this->ddb->query($sql);

        $result = $result->row();
           if($result){
             return $result;
           }

    }


    public function get_ms_inv_($ms_db,$date){
        
        $this->ddb = $this->load->database($ms_db, true);
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
                        on pb.ProductID = p.productid
                where cast(pb.BackUpDate as date) ='".$date."'";

                $result = $this->ddb->query($sql);
                $result = $result->row();
                if($result){
                    return $result->net_of_vat;
                }else{
                    return 0;
                }


    }
    public function get_exp_retail($aria_db,$from,$to){
        $this->ddb = $this->load->database($aria_db, true);
        $sql = "SELECT  SUM(IFNULL(y.amount,0)) as amount   FROM (SELECT a.expense_id, a.acc_code, b.expense_desc FROM srs_aria_retail.0_chart_expense_group as a
                LEFT JOIN srs_aria_retail.0_chart_expense_category as b
                on a.expense_id=b.id) as x
                LEFT JOIN 
                (SELECT account, sum(amount) as amount FROM srs_aria_retail.`0_gl_trans`
                where  tran_date>='".$from."'
                and tran_date<= '".$to."'  and  (memo_ not like '%to record closing entries%')
                GROUP BY account) as y
                ON x.acc_code=y.account
                LEFT JOIN srs_aria_retail.0_chart_master as z
                ON x.acc_code=z.account_code
                ORDER BY expense_id,acc_code
                ";
         $result = $this->ddb->query($sql);
            $result = $result->row();
            if($result){
                return $result;
            }else{
                return false;
            }

    }

    public function get_exp($aria_db,$from,$to){

        $this->ddb = $this->load->database($aria_db, true);

        $sql = "SELECT x.*, y.*, z.account_name,IFNULL(y.amount,0) as mnt   FROM (SELECT a.expense_id, a.acc_code, b.expense_desc FROM 0_chart_expense_group as a
                LEFT JOIN 0_chart_expense_category as b
                on a.expense_id=b.id) as x
                LEFT JOIN 
                (SELECT account, sum(amount) as amount FROM `0_gl_trans`
                where  tran_date>='".$from."'
                and tran_date<= '".$to."'  and  (memo_ not like '%to record closing entries%')
                GROUP BY account) as y
                ON x.acc_code=y.account
                LEFT JOIN 0_chart_master as z
                ON x.acc_code=z.account_code
                ORDER BY expense_id,acc_code";

         $result = $this->ddb->query($sql);
            $result = $result->result();
            if($result){
                return $result;
            }else{
                return false;
            }

    }


    public function get_movements_total_mov($movementcode,$from,$to){

        $month = date('m',strtotime($from));
        $year = date('Y',strtotime($from));

        $db = $this->gen_rep_database_all();
        $db_ = array();
        $total_movs = 0;

        foreach ($db as $i => $value) {
           
            $database =  $value->ms_db;
            $this->ddb = $this->load->database($database, true);
            $month = date('m',strtotime($from));
            $year = date('Y',strtotime($from));

           switch ($movementcode) {

               case 'SALES':
                $sql = "select ROUND(sales_revunue,2) as total
                            from consolidated_gp
                            where month_ =".$month." and year_ = ".$year." 
                   break;";
                  
               case 'GAIN/LOSS':
                    $sql = "select SUM(total) as total from consolidated_movements
                    where months =".$month."  and years = ".$year ."
                    and movementcode IN('AIG','AIL','IGNSA','IGSA')";
                   break;
               
               default:
                    $sql = "select SUM(total) as total from consolidated_movements
                    where months =".$month."  and years = ".$year ."
                    and movementcode IN('".$movementcode."')";
                   break;
           }

             $result = $this->ddb->query($sql);
             $result = $result->row();
             if($result){
                $total_movs = $total_movs + $result->total;
             }else{
                $total_movs = $total_movs + 0;
             } 
       
        }

       return $total_movs;
        
    }

    public function get_movements($ms_db,$movs,$from,$to){
        $this->ddb = $this->load->database($ms_db, true);
        $month = date('m',strtotime($from));
        $year = date('Y',strtotime($from));

        $sql ="select movementcode,description as Description,total,total_qty as tot_qty from consolidated_movements
                where months =".$month."  and years =".$year."";

        if($movs){
            $sql .= "and movementcode in ('".implode("','", $movs)."')";
        }

        $result = $this->ddb->query($sql);
        $result = $result->result();
        if($result){
            return $result;
        }else{
            return false;
        }

    }


    public function get_movements_($ms_db,$movs,$from,$to){

        $this->ddb = $this->load->database($ms_db, true);

        $sql = "SELECT movements.movementcode, MovementTypes.Description,
        ROUND(SUM(CASE
        WHEN Products.pVatable = 1
        THEN ROUND((ROUND((extended/1.12),4)),4)
        ELSE
        ROUND((ROUND((extended),4)),4)
        END),4) AS total, SUM(qty*pack) as tot_qty
        from MovementLine inner join Movements
        on MovementLine.MovementID = Movements.MovementID inner join
        Products on Products.ProductID = MovementLine.ProductID
        inner join MovementTypes on Movements.MovementCode = MovementTypes.MovementCode
        where CAST (Movements.PostedDate  as DATE) between  '".$from."' and  '".$to."' and  Movements.status = 2";
        if($movs){
            $sql .="AND  movements.movementcode IN ('".implode("','", $movs)."')";
        }
        $sql .= "group by  movements.movementcode, MovementTypes.Description
                 order by  MovementTypes.Description";

        $result = $this->ddb->query($sql);
        $result = $result->result();
        if($result){
            return $result;
        }else{
            return false;
        }



    }
    
    public function get_gl_trans_from_to($aria_db,$account,$from,$to){
        $this->ddb = $this->load->database($aria_db, true);

        if($account == '4000040'){
            if($from >= '2018-01-01')
                $account = '4900';  
        }

        if($account == '4900'){
            if($from < '2018-01-01')
                $account = '4000040';  
        }
        
        $sql ="SELECT SUM(amount) as gl_sum FROM 0_gl_trans 
                WHERE account='".$account."' and type !=0 
                AND tran_date >= '".$from."'
                AND tran_date <= '".$to."'
                AND dimension_id = 0
                AND dimension2_id = 0";

        if($account =='4900'){
            $sql .=" and type ='100' ";
        }

        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            return $result->gl_sum;
        }


    }

     public function get_gl_trans_from_to_array_r($aria_db,$from,$to,$accounts){

           $this->ddb = $this->load->database($aria_db, true);

            $sql = "SELECT sum(amount) as total
                    FROM 0_gl_trans a
                        JOIN 0_chart_master b ON a.account = b.account_code
                        LEFT OUTER JOIN  0_suppliers c ON (a.person_id = c.supplier_id AND a.person_type_id = 3)
                        WHERE a.tran_date >='".$from."'
                        AND a.tran_date <= '".$to."'
                        AND a.account IN(".$accounts.") and  (memo_ not like '%to record closing entries%')
                    ";

            $result = $this->ddb->query($sql);
            $result = $result->row();
            if($result){
                return $result->total;
            }else{
                return 0;
            }
        }




    public function get_account_type($aria_db,$type,$remove_acc=null){
        $this->ddb = $this->load->database($aria_db, true);

        $sql = "SELECT 0_chart_master.*,0_chart_types.name AS AccountTypeName
                FROM 0_chart_master,0_chart_types
                WHERE 0_chart_master.account_type=0_chart_types.id
                AND account_type IN (".implode(',', $type).")";

        if($remove_acc){
            $sql .= " AND account_code NOT IN (".implode(',', $remove_acc).") ";
        }

        $result = $this->ddb->query($sql);
        $result = $result->result();
        if($result){
            return $result;
        }


    }
    public function get_sukipoints($ms_db,$date1, $date2)
    {
        
        $this->ddb = $this->load->database($ms_db, true);
        $sql = "select sum(amount) as sukipoints  from FinishedPayments where tendercode ='004'and voided=0
                                and LogDate between'".$date1."' and '".$date2."'";

       $result = $this->ddb->query($sql);
       $result = $result->row();
       if($result){
         $sukipoints = $result->sukipoints;
         return round($sukipoints,2);
       }else{
         return 0;   
       }
    }


     public   function get_finished_sales_for_formula_1_conso_ms($ms_db,$date1, $date2){
        $this->ddb = $this->load->database($ms_db, true);
        $month = date('m',strtotime($date1));
        $year = date('Y',strtotime($date1));
        $details = array();

        $sql = "SELECT total_sales,total_cost,sukipoints from consolidated_sales where months =".$month." and years =".$year."";

        $result = $this->ddb->query($sql);
        $result = $result->row();
           if($result){
             return $result;
           }
     }


     

     public   function get_finished_sales_for_formula_1($ms_db,$date1, $date2)
     {
        $this->ddb = $this->load->database($ms_db, true);
        $tax_rate = 12;

        $sql = "SELECT SUM(
                (CASE WHEN fs.[Return] = 0
                THEN
                    fs.AverageUnitCost
                ELSE
                    -fs.AverageUnitCost
                END)
                * fs.TotalQty) as non_vat_cost, SUM(fs.Extended) as non_vat_sales,
                SUM(
                (CASE WHEN fs.[Return] = 0
                THEN
                    fs.TotalQty
                ELSE
                    -fs.TotalQty
                END)) as qty
                FROM [dbo].[FinishedSales] as fs LEFT JOIN Products as p
                on fs.ProductID = p.ProductID
                WHERE CAST(fs.LogDate AS DATE) >=  '".$date1."'
                AND CAST(fs.LogDate AS DATE) <= '".$date2."'
                AND fs.Voided = 0  AND p.pVatable = 0 AND fs.pVatable ! = '2'";

        $result = $this->ddb->query($sql);
        $result = $result->row();
        if($result){
            $non_vat_cost  =  $result->non_vat_cost;
            $non_vat_sales  =  $result->non_vat_sales;    
        }else{
             $non_vat_cost  = 0;
             $non_vat_sales  = 0;
        }
        


        $sql2 = "SELECT SUM(
                    (CASE WHEN fs.[Return] = 0
                    THEN
                        fs.AverageUnitCost
                    ELSE
                        -fs.AverageUnitCost
                    END)
                    * fs.TotalQty) as vat_cost, SUM(fs.Extended) as vat_sales,
                    SUM(
                    (CASE WHEN fs.[Return] = 0
                    THEN
                        fs.TotalQty
                    ELSE
                        -fs.TotalQty
                    END)) as qty
                FROM [dbo].[FinishedSales] as fs LEFT JOIN Products as p
                ON fs.ProductID = p.ProductID
                WHERE CAST(fs.LogDate AS DATE) >= '".$date1."'
                AND CAST(fs.LogDate AS DATE) <= '".$date2."'
                AND fs.Voided = 0  AND p.pVatable = 1 AND fs.pVatable ! = '2'";

        $result2 = $this->ddb->query($sql2);
        $result2 = $result2->row();
        $sukipoints = $this->get_sukipoints($ms_db,$date1, $date2);
        if($result2){
            $vat_cost = $result2->vat_cost /(1+($tax_rate/100));
            $vat_sales = ($result2->vat_sales-$sukipoints)/(1+($tax_rate/100));
        }else{
             $vat_cost = 0;
             $vat_sales = 0;
        }


        $sql3 = "SELECT SUM(
                        (CASE WHEN [Return] = 0
                        THEN
                            AverageUnitCost
                        ELSE
                            -AverageUnitCost
                        END)
                        *TotalQty) as vat_cost, SUM(Extended) as vat_sales,
                        SUM(
                        (CASE WHEN [Return] = 0
                        THEN
                            TotalQty
                        ELSE
                            -TotalQty
                        END)) as qty
                    FROM [dbo].[FinishedSales]
                    WHERE CAST(LogDate AS DATE) >= '".$date1."'
                    AND CAST(LogDate AS DATE) <= '".$date2."'
                    AND Voided = 0  AND pVatable = 2";


        $result3 = $this->ddb->query($sql3);
        $result3 = $result3->row();
        if($result3){
            $special_vat_cost = $result3->vat_cost/(1+($tax_rate/100));
            $special_vat_sales = $result3->vat_sales;
        }else{
            $special_vat_cost = 0;
            $special_vat_sales = 0;
        }


        return array(round($non_vat_cost+$vat_cost+$special_vat_cost,4), round($non_vat_sales+$vat_sales+$special_vat_sales,4),round($non_vat_sales),round($vat_sales),round($special_vat_sales));
    }



}