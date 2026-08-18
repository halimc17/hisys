<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');


$param = $_POST;
if(count($param)==0){$param = $_GET;}

$kodeorg=$param['kodeorg'];
$thnbudget=$param['thnbudget'];
$kelompokbiaya=$param['kelompokbiaya'];
$jenis=$param['jenis'];
#ambil produksi pks
$tbs=0;
$cpo=0;
$pk=0;

$tipeorg = getNamaOrg($kodeorg,'tipe');

if($tipeorg=='BULKING'){
  $str="select sum(kgolah) as tbs,sum(kgcpo) as cpo,sum(kgkernel) as kernel from ".$dbname.".bgt_produksi_bulk 
      where tahunbudget=".$thnbudget." and millcode = '".$kodeorg."'";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch()){
    $tbs=$bar->tbs;
    $cpo=$bar->cpo;
    $pk=$bar->kernel;
  }
}else{  
  $str="select sum(kgolah) as tbs,sum(kgcpo) as cpo,sum(kgkernel) as kernel from ".$dbname.".bgt_produksi_pks_vw 
      where tahunbudget=".$thnbudget." and millcode = '".$kodeorg."'";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while($bar=$res->fetch()){
    $tbs=$bar->tbs;
    $cpo=$bar->cpo;
    $pk=$bar->kernel;
  }
}

$str="select a.tahunbudget,a.station,a.kdbudget, sum(a.rupiah) as rupiah,
	  sum(a.rp01) as rp01,sum(a.rp02) as rp02,sum(a.rp03) as rp03,sum(a.rp04) as rp04,sum(a.rp05) as rp05,
	  sum(a.rp06) as rp06,sum(a.rp07) as rp07,sum(a.rp08) as rp08,sum(a.rp09) as rp09,sum(a.rp10) as rp10,
	  sum(a.rp11) as rp11,sum(a.rp12) as rp12,
	  b.namaorganisasi,c.nama from ".$dbname.".bgt_pks_station_vw a left join
      ".$dbname.".organisasi b on a.station=b.kodeorganisasi left join ".$dbname.".bgt_kode c on a.kdbudget=c.kodebudget
      where tahunbudget=".$thnbudget." and a.station like '".$kodeorg."%' and a.noakun like '".$kelompokbiaya."%' group by a.tahunbudget,a.station,a.kdbudget
      ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no=0;
$rpperha=0;

if($jenis=='excel'){
	$stream.="<table class=sortable cellspacing=1 border='1' cellpadding=5>";
}else{	
	$stream.="<table class=sortable cellspacing=1 border='0' cellpadding=5>";
}
$stream.="<thead>
     <tr class=rowheader>
      <th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
      <th align=center rowspan=2>".$_SESSION['lang']['station']."</th>
      <th align=center rowspan=2>".$_SESSION['lang']['kodeabs']."</th>
      <th align=center rowspan=2>".$_SESSION['lang']['jumlahrp']."</th>
      <th align=center colspan=12>".$_SESSION['lang']['sebaran']."</th>
      <th align=center>".$_SESSION['lang']['rpperkg']."<br>TBS</th> 
      <th align=center>".$_SESSION['lang']['rpperkg']."<br>CPO</th>
      <th align=center>".$_SESSION['lang']['rpperkg']."<br>KER</th>
      <th align=center>".$_SESSION['lang']['rpperkg']."<br>PP</th>
    </tr>
    <tr class=rowheader>";
      for ($i=1; $i < 13; $i++) { 
      $stream.="<th align=center>".numToMonth($i,"I",'long')."</th>";
      }
      $stream.="
      <th align=right>".number_format(fixnan($tbs/1000),0,".",",")."</th>
      <th align=right>".number_format(fixnan($cpo/1000),0,".",",")."</th>
      <th align=right>".number_format(fixnan($pk/1000),0,".",",")."</th>
      <th align=right>".number_format(fixnan(($cpo+$pk)/1000),0,".",",")."</th>    
    </tr>
   </thead>
 <tbody>"; 
$old='';
$jumlah=0;

$grandtt=0;
$grp01=0;
$grp02=0;
$grp03=0;
$grp04=0;
$grp05=0;
$grp06=0;
$grp07=0;
$grp08=0;
$grp09=0;
$grp10=0;
$grp11=0;
$grp12=0;
while($bar=$res->fetch()){
    $no+=1;
    $new=$bar->station;
    $rupiah=$bar->rupiah;
    $rp01=$bar->rp01;
    $rp02=$bar->rp02;
    $rp03=$bar->rp03;
    $rp04=$bar->rp04;
    $rp05=$bar->rp05;
    $rp06=$bar->rp06;
    $rp07=$bar->rp07;
    $rp08=$bar->rp08;
    $rp09=$bar->rp09;
    $rp10=$bar->rp10;
    $rp11=$bar->rp11;
    $rp12=$bar->rp12;
    
  
    $nox+=1;
    $new=$bar->station;



    //$jumlah+=$bar->rupiah;
    $grandtt+=$bar->rupiah;
    $grp01+=$bar->rp02;
    $grp02+=$bar->rp02;
    $grp03+=$bar->rp03;
    $grp04+=$bar->rp04;
    $grp05+=$bar->rp05;
    $grp06+=$bar->rp06;
    $grp07+=$bar->rp07;
    $grp08+=$bar->rp08;
    $grp09+=$bar->rp09;
    $grp10+=$bar->rp10;
    $grp11+=$bar->rp11;
    $grp12+=$bar->rp12;

    if($bar->kdbudget=='M')
        $nama_komponen="Material";
    else
        $nama_komponen=$bar->nama;
    
    if($old!='' and $old!=$new){
        #subtotal
    
        @$jumlahpercpo=fixnan($jumlah/($cpo+$pk));
        @$jumlahpertbs=fixnan($jumlah/$tbs);
        @$jmlhCpo=fixnan($jumlah/$cpo);
        @$jmlhker=fixnan($jumlah/$pk);
    $stream.="<tr class=rowcontent style=background:#ccfffd>
           <td colspan=3>".$_SESSION['lang']['total']."</td>
           <td align=right>".number_format($jumlah,0,'.',',')."</td>
           <td align=right>".number_format($trp01,0,'.',',')."</td>
           <td align=right>".number_format($trp02,0,'.',',')."</td>
           <td align=right>".number_format($trp03,0,'.',',')."</td>
           <td align=right>".number_format($trp04,0,'.',',')."</td>
           <td align=right>".number_format($trp05,0,'.',',')."</td>
           <td align=right>".number_format($trp06,0,'.',',')."</td>
           <td align=right>".number_format($trp07,0,'.',',')."</td>
           <td align=right>".number_format($trp08,0,'.',',')."</td>
           <td align=right>".number_format($trp09,0,'.',',')."</td>
           <td align=right>".number_format($trp10,0,'.',',')."</td>
           <td align=right>".number_format($trp11,0,'.',',')."</td>
           <td align=right>".number_format($trp12,0,'.',',')."</td>
          
           <td align=right>".number_format($jumlahpertbs,3,'.',',')."</td> 
           <td align=right>".number_format($jmlhCpo,3,'.',',')."</td> 
           <td align=right>".number_format($jmlhker,3,'.',',')."</td> 
           <td align=right>".number_format($jumlahpercpo,3,'.',',')."</td>
         </tr>";        
        $jumlah=0;
        $trp01=0;
        $trp02=0;
        $trp03=0;
        $trp04=0;
        $trp05=0;
        $trp06=0;
        $trp07=0;
        $trp08=0;
        $trp09=0;
        $trp10=0;
        $trp11=0;
        $trp12=0;
        $jumlah+=$bar->rupiah;
        $trp01+=$bar->rp01;
        $trp02+=$bar->rp02;
        $trp03+=$bar->rp03;
        $trp04+=$bar->rp04;
        $trp05+=$bar->rp05;
        $trp06+=$bar->rp06;
        $trp07+=$bar->rp07;
        $trp08+=$bar->rp08;
        $trp09+=$bar->rp09;
        $trp10+=$bar->rp10;
        $trp11+=$bar->rp11;
        $trp12+=$bar->rp12;
    }
    else
    {
        $jumlah+=$bar->rupiah;
        $trp01+=$bar->rp01;
        $trp02+=$bar->rp02;
        $trp03+=$bar->rp03;
        $trp04+=$bar->rp04;
        $trp05+=$bar->rp05;
        $trp06+=$bar->rp06;
        $trp07+=$bar->rp07;
        $trp08+=$bar->rp08;
        $trp09+=$bar->rp09;
        $trp10+=$bar->rp10;
        $trp11+=$bar->rp11;
        $trp12+=$bar->rp12;
    }
    
    @$rupiahpercpo=fixnan($bar->rupiah/($cpo+$pk));
    @$rupiahpertbs=fixnan($bar->rupiah/$tbs);
    @$rupiahpercpo2=fixnan($bar->rupiah/$cpo);
    @$rupiahperpk=fixnan($bar->rupiah/$pk);
    $stream.="<tr class=rowcontent >
           <td align=center>".$nox."</td>
           <td>".$bar->namaorganisasi."</td>
           <td>".$nama_komponen."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt('".$bar->station."','".$bar->kdbudget."','".$thnbudget."',event)\">".number_format($rupiah,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp01',event)\">".number_format($rp01,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp02',event)\">".number_format($rp02,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp03',event)\">".number_format($rp03,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp04',event)\">".number_format($rp04,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp05',event)\">".number_format($rp05,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp06',event)\">".number_format($rp06,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp07',event)\">".number_format($rp07,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp08',event)\">".number_format($rp08,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp09',event)\">".number_format($rp09,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp10',event)\">".number_format($rp10,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp11',event)\">".number_format($rp11,0,'.',',')."</td>
           <td align=right style='cursor:pointer;' onclick=\"showDt2('".$bar->station."','".$bar->kdbudget."','".$thnbudget."','rp12',event)\">".number_format($rp12,0,'.',',')."</td>
           <td align=right>".number_format($rupiahpertbs,3,'.',',')."</td>     
           <td align=right>".number_format($rupiahpercpo2,3,'.',',')."</td> 
           <td align=right>".number_format($rupiahperpk,3,'.',',')."</td>     
           <td align=right>".number_format($rupiahpercpo,3,'.',',')."</td>
           
            
         </tr>";
    $old=$bar->station;
}
#subtotal terakhir
        @$jumlahpercpo=fixnan($jumlah/($cpo+$pk));
        @$jumlahpertbs=fixnan($jumlah/$tbs);
        @$jumlahperpk=fixnan($jumlah/$pk);
        @$jumlahpercpo2=fixnan($jumlah/$cpo);
    $stream.="<tr class=rowcontent style=background:#ccfffd>
           <td colspan=3 align=center>".$_SESSION['lang']['total']."</td>
           <td align=right>".number_format($jumlah,0,'.',',')."</td> 
           <td align=right>".number_format($trp01,0,'.',',')."</td> 
           <td align=right>".number_format($trp02,0,'.',',')."</td> 
           <td align=right>".number_format($trp03,0,'.',',')."</td> 
           <td align=right>".number_format($trp04,0,'.',',')."</td> 
           <td align=right>".number_format($trp05,0,'.',',')."</td> 
           <td align=right>".number_format($trp06,0,'.',',')."</td> 
           <td align=right>".number_format($trp07,0,'.',',')."</td> 
           <td align=right>".number_format($trp08,0,'.',',')."</td> 
           <td align=right>".number_format($trp09,0,'.',',')."</td> 
           <td align=right>".number_format($trp10,0,'.',',')."</td> 
           <td align=right>".number_format($trp11,0,'.',',')."</td> 
           <td align=right>".number_format($trp12,0,'.',',')."</td> 
           <td align=right>".number_format($jumlahpertbs,0,'.',',')."</td>
           <td align=right>".number_format($jumlahpercpo2,3,'.',',')."</td> 
           <td align=right>".number_format($jumlahperpk,3,'.',',')."</td> 
           <td align=right>".number_format($jumlahpercpo,3,'.',',')."</td>
              
         </tr>"; 
    @$grandttpercpo=fixnan($grandtt/($cpo+$pk));
    @$grandttpertbs=fixnan($grandtt/$tbs);
    @$grandttpercpo2=fixnan($grandtt/$cpo);
    @$grandttperpk=fixnan($grandtt/$pk);
        $stream.="<tr class=rowcontent>
           <td colspan=3 align=center>".$_SESSION['lang']['grnd_total']."</td>
           <td align=right>".number_format($grandtt,0,'.',',')."</td>
           <td align=right>".number_format($grp01,0,'.',',')."</td>
           <td align=right>".number_format($grp02,0,'.',',')."</td>
           <td align=right>".number_format($grp03,0,'.',',')."</td>
           <td align=right>".number_format($grp04,0,'.',',')."</td>
           <td align=right>".number_format($grp05,0,'.',',')."</td>
           <td align=right>".number_format($grp06,0,'.',',')."</td>
           <td align=right>".number_format($grp07,0,'.',',')."</td>
           <td align=right>".number_format($grp08,0,'.',',')."</td>
           <td align=right>".number_format($grp09,0,'.',',')."</td>
           <td align=right>".number_format($grp10,0,'.',',')."</td>
           <td align=right>".number_format($grp11,0,'.',',')."</td>
           <td align=right>".number_format($grp12,0,'.',',')."</td>
           <td align=right>".number_format($grandttpertbs,3,'.',',')."</td> 
           <td align=right>".number_format($grandttpercpo2,3,'.',',')."</td>    
           <td align=right>".number_format($grandttperpk,3,'.',',')."</td>
           <td align=right>".number_format($grandttpercpo,3,'.',',')."</td>
         </tr>";     
$stream.="</tbody>
     <tfoot>
     </tfoot>
     </table>";
	 
if($jenis=='excel'){
	$nop_ = "biaya_langsung";
	if (strlen($stream) > 0) {
	    if ($handle = opendir('tempExcel')) {
	        while (false !== ($file = readdir($handle))) {
	            if ($file != "." && $file != ".." && $file != "index.html") {
	                 @ unlink('tempExcel/'.$file);
	            }
	        }
	        closedir($handle);
	    }
	    $handle = fopen("tempExcel/".$nop_.".xls", 'w');
	    if (!fwrite($handle, $stream)) {
	        echo "<script language=javascript1.2>
	        parent.window.alert('Can't convert to excel format');
	        </script>";
	        exit;
	    } else {
	        echo "<script language=javascript1.2>
	        window.location='tempExcel/".$nop_.".xls';
	        </script>";
	    }
	    fclose($handle);
	}
}else{	
	echo $stream; 
}	 
?>