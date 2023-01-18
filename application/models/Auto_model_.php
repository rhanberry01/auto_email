<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Auto_model_ extends CI_Model {

     
     public function sample(){

        $end_date = date('Y-m-d',strtotime('today - 1 day')); 
        $past_30days = date('Y-m-d',strtotime('today - 30 days')); 
        $past_2days = date('Y-m-d',strtotime('today - 3 days')); 
        $end_past_2days = date('Y-m-d',strtotime('today - 2 days')); 

        $this->ddb = $this->load->database('branch_server', true);

                     $sql1 = " insert into possible_no_display (date_,productid,descripiton,currentinventory,past30days,average,past2days)                   
                                select 
                                a.date_,
                                a.ProductID,
                                a.Description,
                                a.CurrentInventory,
                                a.totalqty,
                                a.aveg,
                                a.past2dayssales
                                FROM
                                (select
                                '".$end_date."' as date_,
                                b.ProductID,b.Description,b.CurrentInventory,b.totalqty,b.aveg,
                                isnull(past2days.totalqty,0) as past2dayssales
                                from(select
                                p.Description as Description,
                                fs.ProductID as ProductID,
                                p.SellingArea as CurrentInventory,
                                sum(case when [return] = 1 then convert(money,0-fs.totalqty) else fs.totalqty end) as totalqty,
                                ((sum(case when [return] = 1 then convert(money,0-fs.totalqty) else fs.totalqty end) / (select sum(case when [return] = 1 then convert(money,0-fs.totalqty) else fs.totalqty end) as totalqty From finishedsales  as fs where cast(logdate as date) >= '".$past_30days."'  and cast(logdate as date) <= '".$end_date."' and fs.voided = 0 ))*100) as aveg
                                From finishedsales  as fs   
                                INNER JOIN (select Description,ProductID,SellingArea
                                from products where (SellingArea > 0 or SellingArea <> null)) as p  on fs.ProductID = p.ProductID
                                where cast(logdate as date) >= '".$past_30days."' and cast(logdate as date) <= '".$end_date."' and fs.voided = 0
                                GROUP BY  fs.ProductID,p.Description,p.SellingArea)AS b
                                LEFT JOIN(select fs.ProductID,sum(case when [return] = 1 then convert(money,0-fs.totalqty) else fs.totalqty end) as totalqty from FinishedSales as fs
                                INNER JOIN (select ProductID,SellingArea from products where (SellingArea > 0 or SellingArea <> null)) as p  on fs.ProductID = p.ProductID
                                where cast(logdate as date) >= '".$past_2days."' and cast(logdate as date) <= '".$end_past_2days."' and fs.voided = 0
                                GROUP BY fs.ProductID) as past2days on  b.ProductID = past2days.ProductID
                                where b.aveg > 0 and isnull(past2days.totalqty,0) = 0) as a
                                where a.CurrentInventory > 0 and  a.aveg > 0";
                                
             $this->ddb->query($sql1);
        


        return 'data has been inserted';
     }

    public function get_po(){
        $end_date = date('Y-m-d'); 
        $past_5days = date('Y-m-d',strtotime('today - 10 days')); 
        $past_2days = date('Y-m-d',strtotime('today - 2 days')); 
        $end_past_2days = date('Y-m-d',strtotime('today - 2 days')); 

        $this->ddb = $this->load->database('branch_server', true);
        $sql1 = "SELECT a.PurchaseOrderNo,a.ReceivingNo, 'srsn' as Branch,
                a.DateReceived,SUM(b.extended)AS tot_extended,b.ReceivingID
                FROM Receiving a 
                JOIN ReceivingLine b ON (a.ReceivingID = b.ReceivingID)
                WHERE a.DateReceived >= '".$past_5days."' and  a.DateReceived <= '".$end_date."' and
                a.PurchaseOrderNo !='' and a.PurchaseOrderNo != '0' and a.PurchaseOrderNo IS NOT NULL
                and (a.PurchaseOrderNo LIKE '%PO%' OR a.PurchaseOrderNo LIKE '%OP%')
                GROUP BY b.ReceivingID,a.ReceivingNo,a.DateReceived,a.PurchaseOrderNo";
                                                
        $result = $this->ddb->query($sql1); 
        $result = $result->result();
        return  $result;

      }

      public function insert_consolidated_gp($details_){
        $this->ddb = $this->load->database('default', true);
        $this->db->insert("consolidated_gp", $details_);

      }

      public function insert_ignore($PurchaseOrderNo,$ReceivingNo,$Branch,$DateReceived,$tot_extended,$ReceivingID){

        $this->ddb = $this->load->database('po_received_server', true);

        $sql1 = "INSERT IGNORE INTO received_purcahses(PurchaseOrderNo,ReceivingNo,Branch,DateReceived,tot_extended,ReceivingID)
                VALUES('".$PurchaseOrderNo."','".$ReceivingNo."','".$Branch."','".$DateReceived."',$tot_extended,'".$ReceivingID."')";
                                
        $res = $this->ddb->query($sql1);
        
        return 'data has been inserted'.$res.'****';
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

    public function insert_update_sales($db,$details){
         $this->ddb = $this->load->database($db, true);

         $sql="INSERT INTO consolidated_sales 
                        (years, 
                         months, 
                         vat_cost,
                         vat_sales,
                         non_vat_cost,
                         non_vat_sales,
                         zero_vat_cost,
                         zero_vat_sales,
                         total_sales,
                         total_cost,
                         sukipoints) 
               VALUES(".$details['years'].",
                      ".$details['months'].",
                      ".$details['vat_cost'].",
                      ".$details['vat_sales'].",
                      ".$details['non_vat_cost'].",
                      ".$details['non_vat_sales'].",
                      ".$details['zero_vat_cost'].",
                      ".$details['zero_vat_sales'].",
                      ".$details['total_sales'].",
                      ".$details['total_cost'].",
                      ".$details['sukipoints']."
                      )";
              

        $res = $this->ddb->query($sql);
        
        return 'data has been inserted'.$res.'****';

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


        return array(round($sukipoints),round($non_vat_cost+$vat_cost+$special_vat_cost,4),round($non_vat_sales+$vat_sales+$special_vat_sales,4),round($non_vat_sales),round($vat_sales),round($special_vat_sales),round($non_vat_cost),round($vat_cost),round($special_vat_cost));
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




}