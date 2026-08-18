<?php
include('master_validation.php'); 
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('../lib/zLib.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_bar.php');
require_once ('../jpgraph/jpgraph_line.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_pie.php');
require_once ('../jpgraph/jpgraph_pie3d.php');
require_once ('../jpgraph/jpgraph_table.php');
require_once ('../jpgraph/jpgraph_canvas.php');
require_once ("../jpgraph/jpgraph_mgraph.php");

 
$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$id = checkPostGet('id','');
$bln = checkPostGet('bln','');
$ptdetail = checkPostGet('ptdetail','');

switch($method){
case 'global':
    if($pt!=''){
        $sList="select kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
        $sortdt2="kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')";
    }else{
        $sList="select induk as kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
        $sortdt2="b.induk in (select induk from ".$dbname.".organisasi where tipe='PABRIK')";
    }
    $qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
    $qList->setFetchMode(PDO::FETCH_ASSOC);
    while ($rList = $qList->fetch()){
        $lstPt[]=$rList['kodeorganisasi'];
    }
     $dawl=0;
     foreach($lstPt as $dtPt){
         $str="select sum(tbsdiolah) as tbsdt,substr(tanggal,6,2) as bulan,b.induk from ".$dbname.".pabrik_produksi a
               left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
               where b.induk='".$dtPt."' and left(tanggal,4)='".$thn."'  and kodeorg is not null and kodeorg!=''  
               group by substr(tanggal,6,2),b.induk
               order by b.induk,substr(tanggal,6,2)  asc";
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
         $res->setFetchMode(PDO::FETCH_ASSOC);
         $row=owlBaris($res);
         if($row!=0){
            $no=1;
            while ($bar = $res->fetch()){
                ${'internal'.$dawl}[$no]=$bar['tbsdt']/1000;
                $dtPt2[$dawl]=$bar['induk'];
                $arrPrd[$thn."-".$bar['bulan']]=$thn."-".$bar['bulan'];
                $no++;
            }   
            $dawl++;   
         }
     }
     $cap=array();
     if(!empty($arrPrd)){
        $no=1;
        foreach($arrPrd as $lstBln){        
            $dtbln=substr($lstBln,-2,2);
            $df=mktime(0,0,0,intval($dtbln),15,$thn);
            $cap[$no]=date('M-y',$df);   
            $no++;
        }   
     }

    foreach($lstPt as $dtPt){
        $no=1;
         $str="select sum(jumlah) as rupiah,substr(tanggal,6,2) as bulan,b.induk,a.noakun from ".$dbname.".keu_jurnaldt a
               left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
               where b.induk='".$dtPt."' and left(tanggal,4)='".$thn."' and a.noakun in ('5110103','5110104')  and kodeorg is not null and kodeorg!=''
               group by substr(tanggal,6,2),b.induk,a.noakun order by b.induk,substr(tanggal,6,2),a.noakun  asc";  
         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
         $res->setFetchMode(PDO::FETCH_ASSOC);     
         while ($bar = $res->fetch()){
            if($bar['noakun']=='5110103'){
                @$oer[$no]=($bar['rupiah']/1000000)*-1;   
            }else if($bar['noakun']=='5110104'){
                @$oer2[$no]=($bar['rupiah']/1000000)*-1; 
            }
             $no++;
     
         }
    }
      
        // A new pie graph
        $graph = new Graph(580,240);
        $graph->img->SetMargin(50,50,50,50);    
        $graph->SetScale("textlin");
        $graph->SetShadow();
        $graph->SetY2Scale("lin");
        $graph->yaxis->scale->SetGrace(30);
        $graph->y2axis->scale->SetGrace(30);  
  
        $graph->xaxis->SetTickLabels($cap);
        $graph->xaxis->SetLabelAngle(90);              
        $graph->title->Set('SALES');

            $txt = new Text('Ton TBS');
       //     $txt->SetFont(FF_FONT2,FS_BOLD,14);
            $txt->SetPos(0.02,0.1,'left','bottom');
            $txt->SetBox('white','black');
            $txt->SetShadow();
            $graph->AddText($txt);

            $txt = new Text('Rp.');
       //     $txt->SetFont(FF_FONT2,FS_BOLD,14);
            $txt->SetPos(0.95,0.1,'left','bottom');
            $txt->SetBox('white','black');
            $txt->SetShadow();
            $graph->AddText($txt);

            if(!empty($internal0)){
                $plot1 = new BarPlot($internal0);   
            }
            if(!empty($internal1)){
                $plot2 = new BarPlot($internal1);   
            }
            if(!empty($internal2)){
                $plot3 = new BarPlot($internal2);   
            }
         

         $plot4 = new LinePlot($oer);
         $plot4->mark->SetType(MARK_FILLEDCIRCLE);   
         $plot4->mark->SetColor('white');
         $plot4->mark->SetFillColor('red');
         $plot5=new LinePlot($oer2);
         $plot5->mark->SetType(MARK_FILLEDCIRCLE);   
         $plot5->mark->SetColor('white');
         $plot5->mark->SetFillColor('blue');
         if(!empty($dtPt2[0])){
            $plot1->SetLegend($dtPt2[0]);   
         }
         if(!empty($dtPt2[1])){
            $plot2->SetLegend($dtPt2[1]);   
         }
         if(!empty($dtPt2[2])){
            $plot3->SetLegend($dtPt2[2]);   
         }
         $plot4->SetLegend('CPO');
         $plot5->SetLegend('KER');
         
        $graph->legend->SetPos(0.5,0.25,'center','bottom');
        if(!empty($internal0)){
            $plot1->SetFillColor("red");
        }
        if(!empty($internal1)){
            $plot2->SetFillColor("orange");
        }
        $gbar = new GroupbarPlot(array($plot1,$plot2));
        $graph->Add($gbar);
        $graph->AddY2($plot4); 
        $graph->AddY2($plot5);   
        $graph->StrokeCSIM();
        break;
}        
?>