<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$tanggalpivot=$_GET['tanggalpivot'];
$tanggalpivot2=$_GET['tanggalpivot2'];
if($_POST['tanggaljt']!='')$tanggaljt=  tanggalsystemn($_GET['tanggaljt']);
$tanggalv=  tanggalsystemn($_GET['tanggalpivot']);
$tanggalv2=  tanggalsystemn($_GET['tanggalpivot2']);
$kodesupplier=$_GET['kodesupplier'];
$nopodt=$_GET['nopodt'];
$rekening=$_GET['rekening'];
$jenis=$_GET['jenis'];
$unit=$_GET['unit'];
$status=$_GET['status'];

$statuspo=$_GET['statuspo'];


$supkontran=$_GET['supkontran'];

//exit("Error:$pt._.$gudang._.$tanggalpivot._.$tanggalv._.$statuspo._.$supkontran");
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";



$namapt='Seluruhnya';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $namapt=strtoupper($bar->namaorganisasi);
}
    $stream='';

/*if($gudang!='')
{
                $str="select * from ".$dbname.".aging_sch_vw
                where posting=1 and tanggal <= '".$tanggalv."' and kodeorg = '".$gudang."'and (nilaiinvoice > dibayar or dibayar is NULL)
                ";
}else
if($pt!='')
{
                $str="select * from ".$dbname.".aging_sch_vw
                where posting=1 and tanggal  <= '".$tanggalv."' and kodeorg = '".$pt."'and (nilaiinvoice > dibayar or dibayar is NULL)
                ";
}else
{
                $str="select * from ".$dbname.".aging_sch_vw
                where posting=1 and tanggal <= '".$tanggalv."' and (nilaiinvoice > dibayar or dibayar is NULL)
                ";
}*/
    
    
 if($gudang!=''){
  $whereGudang .= " and unit = '".$gudang."'";
  $wherePo .= " and nopo like '%".$gudang."%'"; 
}else{
  $whereGudang = "";
}

if($pt!=''){
    $wherePt .= " and kodeorg = '".$pt."'"; 
    $wherePo .= " and kodeorg = '".$pt."'"; 
}else{
  $wherePt = "";
}

// if($statuspo!='')
// {
//  if($statuspo==1)
//  {
//    $wherePo = " and b.lokalpusat = '1'"; 
//  }
//  else
//  {
//    $wherePo = " and (b.lokalpusat = '0' or b.lokalpusat is null)";
//  }
// }
// else
// {
//  $wherePo = "";
// }

if($supkontran!=''){
  $wheresupkontran = " and kodesupplier in (select supplierid from ".$dbname.".log_5supkelompok where tipe='".$supkontran."') ";
}else{
  $wheresupkontran = "";
}

if($kodesupplier!=''){
  $wheresup = " and kodesupplier like '".$kodesupplier."%' ";
}else{
  $wheresup = "";
}
if($tanggaljt!=''){
    $wheretanggaljt = " and jatuhtempo = '".$tanggaljt."' ";
}else{
    $wheretanggaljt = "";
}
if($jenis!=''){
    $wherejenis = " and tipeinvoice = '".$jenis."' ";
}else{
    $wherejenis = "";
}
if($unit!=''){
    $whereunit = " and unit = '".$unit."' ";
}else{
    $whereunit = "";
}


$qPpn = "select nilai from ".$dbname.".setup_parameterappl where  kodeaplikasi='TX' and kodeparameter='PPNINV' ";
$res=$owlPDO->query($qPpn) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrowsx=owlBaris($res);
$akunPpn = '';
if($numrowsx!=0) 
{
$bar=$res->fetch();
$akunPpn = $bar->nilai;
}


// $str = "select a.*,b.ppn as poppn,c.nilai as invppn from ".$dbname.".aging_sch_vw a 
//     left join ".$dbname.".log_poht b 
//     on a.nopo = b.nopo
//     left join ".$dbname.".keu_tagihandt c 
//     on a.noinvoice = c.noinvoice and c.noakun = '".$akunPpn."' 
//                 where a.posting=1 and a.tanggal <= '".$tanggalv."' and (a.nilaiinvoice > dibayar or a.dibayar is NULL)  and a.tipeinvoice not in ('um','p21','p22','p23','p25','ps4','upd','pjd') "
//         . " ".$whereGudang." ".$wherePt." ".$wherePo." ".$wheresupkontran." ";
	//where a.tanggal > '2011-12-31' and (a.nilaiinvoice > dibayar or a.dibayar is NULL) ".$whereGudang." ".$wherePt." ".$wherePo."";
$dibyarkan=array();
$sDetKas="select sum(jumlah) as jumlah,keterangan1,notransaksi,posting from ".$dbname.".keu_kasbankdtht_vw 
          where tanggal >= '".$tanggalv."' and tanggal <= '".$tanggalv2."' and keterangan1<>'' and left(noakun,3) in ('211','118','121','213','117') and pembayaran = '1' 
          group by keterangan1,notransaksi";
$rDetKas=fetchData($sDetKas);
foreach($rDetKas as $row=>$lst){
        $dibyarkan[$lst['keterangan1']]['dibayar']+=$lst['jumlah'];
}
$rDet=array();
$nilPPn=array();
$nilUangMuka=array();
$nilpph=array();
$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
       ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." 
       and left(a.noakun,3) in ('117','118','213','711','116','115') group by a.noinvoice,a.noakun";
//echo $sDet;
// exit('warning:'.$sDet);
$rDet=fetchdata($sDet);
foreach($rDet as $row=>$lstData){
    // if($lstData['tipeinvoice']=='ffb'){
    //     continue;
    // }
    // $sJenis="select jurnal from ".$dbname.".keu_5jenistagihan where status=1 and kode='".$lstData['tipeinvoice']."'";
    // $rJenis=fetchData($sJenis);
    // if($rJenis[0]['jurnal']!=1){
        if(substr($lstData['noakun'],0,3)=='117'){
            $nilPPn[$lstData['noinvoice']]+=$lstData['nilai'];    
        }
    //}
   
    if($lstData['nilai']<0){
        if(substr($lstData['noakun'],0,3)=='118'){
            $nilUangMuka[$lstData['noinvoice']]+=$lstData['nilai'];
        } 
        if(substr($lstData['noakun'],0,3)=='213'){
            $nilpph[$lstData['noinvoice']]+=$lstData['nilai'];
        } 
        if((substr($lstData['noakun'],0,3)=='711')||(substr($lstData['noakun'],0,3)=='116')||(substr($lstData['noakun'],0,3)=='115')){
            $bylain[$lstData['noinvoice']]+=$lstData['nilai'];
        }   
    }
    
    
    
}

$ayatsilang=array();
$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
       ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." 
       and nilai<0 and a.noakun='1110401' group by a.noinvoice,b.noakun";
// exit('warning:'.$sDet);
$rDet=fetchdata($sDet);
foreach($rDet as $row=>$lstData){
    $ayatsilang[$lstData['noinvoice']]+=$lstData['nilai'];
}
$listPo=array();
$sPo="select * from ".$dbname.".log_poht where 1=1 and statuspo=3 ".$wherePo."";
$rPo=fetchData($sPo);
foreach($rPo as $rw=>$lst){
  $listPo[$lst['nopo']]['subtotal']=$lst['subtotal']*$lst['kurs'];
  $listPo[$lst['nopo']]['ppn']=$lst['ppn']*$lst['kurs'];
  $listPo[$lst['nopo']]['pph']=$lst['pph']*$lst['kurs'];
}

$str="select * from ".$dbname.".keu_tagihanht where posting=1 and tanggal >= '".$tanggalv."' and tanggal <= '".$tanggalv2."' and tipeinvoice not in ('p21','p22','p23','p25','ps4','upd','pjd')
        ".$whereGudang." ".$wherePt." ".$wherePo." ".$wheresupkontran." ".$wheresup." ".$wheretanggaljt." ".$wherejenis." ".$whereunit." ".$wherestatus." and nopo like '%".$nopodt."%' ";
$arrJnsTag=array();
$sjenisTag="select * from ".$dbname.".keu_5jenistagihan ";
$rJenisTag=fetchData($sjenisTag);
foreach ($rJenisTag as $key => $val) {
  $arrJnsTag[$val['kode']]=$val['jurnal'];
}
    

function tanggalbiasa($_q)
{
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,4,4)."-".substr($_q,2,2)."-".substr($_q,0,2);
 return($_retval);
}

//=================================================
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res);
		$no=0;
        if($numrows<1)
        {
                echo"<tr class=rowcontent><td colspan=13>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
        }
        else
        {
                $stream.=$_SESSION['lang']['usiahutang'].": ".$namapt."<br>
                tanggal: ".$tanggalpivot." - ".$tanggalpivot2."
                <table border=1>
                    <tr>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['nourut']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['noinvoice']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['namasupplier']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['jatuhtempo']."</td>
                          <td nowrap rowspan=2 align=center >".$_SESSION['lang']['nopokontrak']."/Referensi</td>
                          <td nowrap align=center colspan=3>".$_SESSION['lang']['nilaipokontrak']."/Referensi</td>
                          <td nowrap align=center colspan=4>".$_SESSION['lang']['nilaiinvoice']."</td>
                          <td nowrap align=center colspan=4>".$_SESSION['lang']['sudahjatuhtempo']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['dibayar']."</td>
                          <td nowrap rowspan=2 align=center>".$_SESSION['lang']['jmlh_hari_outstanding']."</td>
                        </tr>  
                    <tr>
                          <td nowrap align=center>DPP</td>
                          <td nowrap align=center>PPN</td>
                          <td nowrap align=center>Total</td>
                          <td nowrap align=center>DPP</td>
                          <td nowrap align=center>PPN</td>
                          <td nowrap align=center width=50>".strtoupper($_SESSION['lang']['pengurang'])."</td>
                          <td nowrap align=center>Total</td>
                          <td nowrap align=center>1-30 ".$_SESSION['lang']['hari']."</td>
                          <td nowrap align=center>31-60 ".$_SESSION['lang']['hari']."</td>
                          <td nowrap align=center>61-90 ".$_SESSION['lang']['hari']."</td>
                          <td nowrap align=center>over 90 ".$_SESSION['lang']['hari']."</td>
                        </tr>";  
             $total0=$total30=$total60=$total90=$total100=$totaldibayar=0;
            $totalinvoice=0;
                while($bar=$res->fetch())
                {
                       $optNmSup=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$bar->kodesupplier."'");
                        $namasupplier =$optNmSup[$bar->kodesupplier];
                        if($namasupplier=='')$namasupplier='&nbsp;';
                        $noinvoice  =$bar->noinvoice;
                        $tanggal  =$bar->tanggal; 
                        $jatuhtempo   =$bar->jatuhtempo;
                        $nopokontrak    =$bar->nopo;
                        $sDetnota="select sum(nilaiinvoice*-1) as nildebet from ".$dbname.".keu_notadebet_ht where noinvoice_referensi='".$noinvoice."' group by noinvoice_referensi";
                        $rDetnota=fetchdata($sDetnota);
                        $pengurang=$ayatsilang[$noinvoice]+$nilUangMuka[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet'];
            $nilaidpp=$bar->nilaidpp;
                        $nilaiinvoice=$bar->nilaiinvoice;
						
						if($nilaiinvoice==0){
				$pengurang=0;
			}
						
						
            if($nilaidpp == $nilaiinvoice)$nilaidpp=$nilaidpp-$nilPPn[$noinvoice];                        
                        //$ayatsilang[$noinvoice]+$nilUangMuka[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet'];
                        // $nilcek=$bar->nilaiinvoice+$pengurang+$nilPPn[$noinvoice];
            $nilcek=$bar->nilaidpp+$pengurang+$nilPPn[$noinvoice];
                        //+$ayatsilang[$noinvoice]+$nilUangMuka[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet']+$nilPPn[$noinvoice];
                        if($arrJnsTag[$bar->tipeinvoice]==1){
                          $nopokontrak    =$bar->noinvoicesupplier;
                          $nilaiinvoice=($bar->nilaiinvoice-$nilPPn[$noinvoice]);//+$nilUangMuka[$noinvoice]+$ayatsilang[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet'];
                          // $nilcek=$bar->nilaiinvoice+$pengurang;
              $nilcek=$bar->nilaidpp+$pengurang;
                          //$ayatsilang[$noinvoice]+$nilUangMuka[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet'];
                        }
                        
                        $nilaipo        =$listPo[$nopokontrak]['subtotal'];
                        $nilaippnpo     =$listPo[$nopokontrak]['ppn'];
                        $nilaikontrak   =$bar->kurs*$bar->nilaikontrak;
                       // $nilaiinvoice   =$bar->kurs*$bar->nilaiinvoice;
                        $nilaippninv   =$nilPPn[$noinvoice];
                        if($bar->tipeinvoice=='ffb'){
                            if(strlen($noinvoice)!=14){
                                if(($bar->kodesupplier=='S201801275') || ($bar->kodesupplier=='S201801460')){
                                    $nilPPn[$noinvoice]=0;    
                                }
                            }else{
                                if($nilPPn[$noinvoice]!=0){
                                    $nilaiinvoice=0;
                                    $nilcek=$nilPPn[$noinvoice];
                                }
                            }
                        }
                        
                        $nilaipokontrak =$nilaipo;
                        if($nilaikontrak>0)$nilaipokontrak=$nilaikontrak;
//      $date1=date('Y-m-d');
                        $date1=tanggalbiasa($tanggalpivot2);
                        $diff =(strtotime($tanggal)-strtotime($date1));
                        $outstd =floor(($diff)/(60*60*24));
                        //if($outstd<1)$outstd=0;
                        $dibayar  =$dibyarkan[$noinvoice]['dibayar'];
                        // $sisainvoice    =$nilcek-$dibayar;
            $sisainvoice    =$nilaidpp+$nilaippninv+$pengurang-$dibayar;
                        if(number_format($sisainvoice)==0){
                          continue;
                        }
$arrOpt=array("2"=>"Sudah Terbayar","3"=>"Outstanding");
            if($status=='2'){
                if($dibayar==0)continue;
            }
            if($status=='3'){
                $outstd2=$outstd*(-1);
                // echo '</br>'.$outstd2;
                if($outstd2<=30)continue;
            }
                        
                        $totalinvoice+=($nilaidpp+$nilaippninv+$pengurang);
 
                        
                        
                             $flag0=$flag30=$flag60=$flag90=$flag100=0;
                            if($outstd!=0)$outstd*=-1;
                            if($outstd<=0)$flag0=1; 
                            if(($outstd>=1)and($outstd<=30))$flag30=1;
                            if(($outstd>=31)and($outstd<=60))$flag60=1;
                            if(($outstd>=61)and($outstd<=90))$flag90=1;
                            if($outstd>90)$flag100=1;
                            if($flag0==1){$total0+=$sisainvoice;}
                            if($flag30==1){$total30+=$sisainvoice;}
                            if($flag60==1){$total60+=$sisainvoice;}
                            if($flag90==1){$total90+=$sisainvoice;}
                            if($flag100==1){$total100+=$sisainvoice;}
                        
                        
                        $totaldibayar+=$dibayar;
                        if($jatuhtempo=='0000-00-00'){ $outstd=''; $jatuhtempo=''; }
//			if($dibayar>=$nilaiinvoice)continue;
                        $no+=1;

                                  // <td nowrap align=right>".number_format($nilaiinvoice,2)."</td>
                                  // <td nowrap align=right>".number_format(($nilaiinvoice+$nilaippninv+$pengurang),2)."</td>
                        $stream.="<tr>
                                  <td nowrap align=center>".$no."</td>
                                  <td nowrap align=center>".$tanggal."</td>
                                  <td nowrap align=left nowrap>&nbsp;".$noinvoice."</td> 
                                  <td nowrap align=left nowrap>".$namasupplier."</td> 
                                  <td nowrap align=center>".$jatuhtempo."</td>
                                  <td nowrap>".$nopokontrak."</td>
                                  <td nowrap align=right>".number_format($nilaipokontrak,2)."</td>
                                  <td nowrap align=right>".number_format($nilaippnpo,2)."</td>
                                  <td nowrap align=right>".number_format(($nilaippnpo+$nilaipokontrak),2)."</td>
                                  <td nowrap align=right>".number_format($nilaidpp,2)."</td>
                                  <td nowrap align=right>".number_format($nilaippninv,2)."</td>
                                  <td nowrap align=right>".number_format($pengurang,2)."</td>
                                  <td nowrap align=right>".number_format(($nilaidpp+$nilaippninv+$pengurang),2)."</td>
                                  <td nowrap align=right>";
                                  if($flag30==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>";
                                  if($flag60==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>";
                                  if($flag90==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>";
                                  if($flag100==1)$stream.=number_format($sisainvoice,2); $stream.="</td>
                                  <td nowrap align=right>".number_format($dibayar,2)."</td>
                                  <td nowrap align=right>".$outstd."</td>
                                </tr>";
                }
                $stream.="<tr>
                                  <td colspan=12 align=center>TOTAL</td>
                                  <td align=right>";
                                  $stream.= number_format($totalinvoice,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total30,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total60,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total90,2); $stream.="</td>
                                  <td align=right>";
                                  $stream.= number_format($total100,2); $stream.="</td>
                                  <td align=right>".number_format($totaldibayar,2)."</td>
                                  <td align=right>&nbsp;</td>
                        </tr>";                 
          $stream.="</table>";	
        }
$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];

$nop_="DaftarUsiaHutang";
if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != "index.html") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls",'w');
 if(!fwrite($handle,$stream))
 {
  echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
   exit;
 }
 else
 {
  echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
 }
fclose($handle);
}
?>