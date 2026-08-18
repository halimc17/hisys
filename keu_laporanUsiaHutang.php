<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');

use Dompdf\Dompdf;
/*
$pt=$_POST['pt'];
$gudang=$_POST['gudang'];
$tanggalpivot=$_POST['tanggalpivot'];
$tanggalpivot2=$_POST['tanggalpivot2'];
$statuspo=$_POST['statuspo'];
$tanggalv=  tanggalsystemn($_POST['tanggalpivot']);
$tanggalv2=  tanggalsystemn($_POST['tanggalpivot2']);
if($_POST['tanggaljt']!='')$tanggaljt=  tanggalsystemn($_POST['tanggaljt']);
$supkontran=$_POST['supkontran'];
$kodesupplier=$_POST['kodesupplier'];
$nopodt=$_POST['nopodt'];
$rekening=$_POST['rekening'];
$jenis=$_POST['jenis'];
$unit=$_POST['unit'];
$status=$_POST['status'];
*/

$pt = checkPostGet('pt','');		 
$gudang = checkPostGet('gudang','');		 
$tanggalpivot = checkPostGet('tanggalpivot','');		 
$tanggalpivot2 = checkPostGet('tanggalpivot2','');	

if($tanggalpivot=='' || $tanggalpivot2==''){
	exit("Warning:Tanggal tidak boleh kosong");
}
	
$tanggalv=  tanggalsystemn($tanggalpivot);
$tanggalv2=  tanggalsystemn($tanggalpivot2);
$tanggaljt = checkPostGet('tanggaljt','');	

// if($tanggaljt!='' || ){
	// $tanggalv2=  tanggalsystemn($tanggaljt);
// }
$supkontran = checkPostGet('supkontran','');		 
$kodesupplier = checkPostGet('kodesupplier','');		 
$nopodt = checkPostGet('nopodt','');		 
$rekening = checkPostGet('rekening','');		 
$jenis = checkPostGet('jenis','');		 
$unit = checkPostGet('unit','');		 
$status = checkPostGet('status','');	        
$tipelaporan = checkPostGet('tipelaporan','');	        
$noinvoicesch = checkPostGet('noinvoicesch','');	        
		 

$stream='';		

	
		 
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
// 	if($statuspo==1)
// 	{
// 		$wherePo = " and b.lokalpusat = '1'";	
// 	}
// 	else
// 	{
// 		$wherePo = " and (b.lokalpusat = '0' or b.lokalpusat is null)";
// 	}
// }
// else
// {
// 	$wherePo = "";
// }

if($supkontran!=''){
    if($supkontran == 'S'){
        $wheresupkontran = " and kodesupplier in (select supplierid from ".$dbname.".log_5supkelompok where tipe not in ('KONTRAKTOR','TRANSPORTIR')) ";
    }else{
        $tipenya = '';
        if ($supkontran == 'K') {
            $tipenya = 'KONTRAKTOR';
        }else{
            $tipenya = 'TRANSPORTIR';
        }
        $wheresupkontran = " and kodesupplier in (select supplierid from ".$dbname.".log_5supkelompok where tipe='".$tipenya."') ";
    }
}else{
	$wheresupkontran = "";
}
if($kodesupplier!=''){
    $wheresup = " and kodesupplier like '".$kodesupplier."%' ";
}else{
    $wheresup = "";
}

if($tanggaljt!=''){
    $wheretanggaljt = " and jatuhtempo = '".tanggalsystemn($tanggaljt)."' ";
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
if($noinvoicesch!=''){
    $whereinvoice = " and noinvoice like '%".$noinvoicesch."%' ";
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

$arrJnsTag=array();
$sjenisTag="select * from ".$dbname.".keu_5jenistagihan ";
$rJenisTag=fetchData($sjenisTag);
foreach ($rJenisTag as $key => $val) {
  $arrJnsTag[$val['kode']]=$val['jurnal'];
}

$where2='';

$dibyarkan=array();
$sDetKas="select sum(jumlah) as jumlah,keterangan1,notransaksi,posting from ".$dbname.".keu_kasbankdtht_vw 
          where tanggal >= '".$tanggalv."' and tanggal <= '".$tanggalv2."' and keterangan1<>''  and pembayaran = '1' 
          group by keterangan1,notransaksi";
/*$sDetKas="select sum(jumlah) as jumlah,keterangan1,notransaksi,posting from ".$dbname.".keu_kasbankdtht_vw 
          where tanggal >= '".$tanggalv."' and tanggal <= '".$tanggalv2."' and keterangan1<>'' and left(noakun,3) in ('211','118','121','213','117','219') and pembayaran = '1' 
          group by keterangan1,notransaksi";*/		  
$rDetKas=fetchData($sDetKas);
foreach($rDetKas as $row=>$lst){
        $dibyarkan[$lst['keterangan1']]['dibayar']+=$lst['jumlah'];
}

$rDet=array();
$nilPPn=array();
$nilUangMuka=array();
$nilpph=array();
/*$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
       ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." 
       and left(a.noakun,3) in ('117','118','213','711','116','115') group by a.noinvoice,a.noakun";*/
$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
       ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." group by a.noinvoice,a.noakun";
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
            // $nilUangMuka[$lstData['noinvoice']]+=$lstData['nilai'];
            $nilUangMuka[$lstData['noinvoice']]+=0; //uang muka di0kan karna sudah memotong nilai dpp di invoice
        } 
        if(substr($lstData['noakun'],0,3)=='213'){
            $nilpph[$lstData['noinvoice']]+=$lstData['nilai'];
        } 
        if((substr($lstData['noakun'],0,3)=='711')||(substr($lstData['noakun'],0,3)=='116')||(substr($lstData['noakun'],0,3)=='115')){
            $bylain[$lstData['noinvoice']]+=$lstData['nilai'];
        }   
    }
}

// $optNmSupptipe=makeOption($dbname,'log_5supkelompok','supplierid,tipe');
$ayatsilang=array();
$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
       ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." 
       and nilai<0 and a.noakun='1110401' group by a.noinvoice,b.noakun";
// exit('warning:'.$sDet);
$rDet=fetchdata($sDet);
foreach($rDet as $row=>$lstData){
    $ayatsilang[$lstData['noinvoice']]+=$lstData['nilai'];
}


$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
       ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." 
       and nilai<0 and a.noakun like '118%' group by a.noinvoice,b.noakun";
// exit('warning:'.$sDet);
$rDet=fetchdata($sDet);
foreach($rDet as $row=>$lstData){
    $nilaium[$lstData['noinvoice']]+=$lstData['nilai'];
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
        ".$whereGudang." ".$wherePt." ".$whereinvoice." ".$wherePo." ".$wheresupkontran." ".$wheresup." ".$wheretanggaljt." ".$wherejenis." ".$whereunit." ".$wherestatus." and (nopo like '%".$nopodt."%' or noinvoicesupplier like '%".$nopodt."%') ";
// echo $str;
  // echo"<pre>";
  // print_r($dibyarkan);
  // echo"</pre>";

function tanggalbiasa($_q)
{
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,4,4)."-".substr($_q,2,2)."-".substr($_q,0,2);
 return($_retval);
}

$akun=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query("select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='HOLDING'");
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$unit=$bar['kodeorganisasi'];

$akun=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query("select distinct norekening, c.namabank from ".$dbname.".keu_posisisaldobank a 
  left join ".$dbname.".keu_5akunbank b on a.norekening=b.noakun
  left join ".$dbname.".keu_5daftarbank c on b.namabank=c.kodebank 
  where kodeorg='".$unit."' order by c.namabank ");
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
  if ($bar['norekening']==$rekening) {
    $akun.="<option value='".$bar['norekening']."' selected>".$bar['norekening']." - ".$bar['namabank']."</option>";
  }else{
    $akun.="<option value='".$bar['norekening']."'>".$bar['norekening']." - ".$bar['namabank']."</option>";
  }
}



$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");


/*
if ($_SESSION['empl']['bagian']=='FNC' && $_SESSION['empl']['tipekaryawan']=='9') {
  $stream.="<table border=0 >"; 
  $stream.="</tr>  
      <td>".$_SESSION['lang']['saldo']." <select id=rekening style=width:100px; onchange=getnilaisaldodireksi('".$unit."')>".$akun."</select></td>
      <td>:</td>
      <td><input type=text id='saldo' class=myinputtextnumber style=width:90px; onkeypress=\"return angka_doang(event);\" disabled=disabled></td>
      <td><input type=text id='ketsaldo' class=myinputtext style=width:120px; placeholder='Keterangan' disabled=disabled></td>
     </tr>"; 
  $stream.="</tr>  
          <td>".$_SESSION['lang']['estimasi']."</td>
          <td>:</td>
          <td><input type=text id='estimasi' class=myinputtextnumber style=width:90px; onkeypress=\"return angka_doang(event);\" disabled=disabled></td>
          <td><input type=text id='ketestimasi' class=myinputtext style=width:400px; placeholder='Keterangan' disabled=disabled></td>
       </tr>";
  $stream.="</tr>  
          <td>".$_SESSION['lang']['saldo']." Blokir</td>
          <td>:</td>
          <td><input type=text id='saldoblokir' class=myinputtextnumber style='width:90px;' onkeyup=\"z.numberFormat('saldoblokir',2); return angka_doang(event);\"></td>
          <td><input type=text id='ketblokir' class=myinputtext style=width:400px; placeholder='Keterangan'></td>
       </tr>";
  $stream.="</table>"; 
}else{
  $stream.="<table border=0 >"; 
  $stream.="</tr>  
      <td>".$_SESSION['lang']['rekening']."</td>
      <td>:</td>
      <td><select id=rekening style=width:100px; onchange=getnilaisaldo()>".$akun."</select></td>
     </tr>"; 
  $stream.="</table>"; 
}
 $stream.="######";
*/

 
 if($tipelaporan=='html'){
	$stylekolom='border=0 cellspacing=1';
} else if($tipelaporan=='excel'){
	$stylekolom='border=1 cellspacing=1';
	
	$stream.="<table class=sortable cellspacing=1>";
	 $stream.="<tr><td>Laporan Aging Schedule</td></tr>";
	 $stream.="<tr><td>".$pt." - ".$nmorg[$pt]."</td></tr>";
	 $stream.="<tr><td>".$tanggalpivot." - ".$tanggalpivot2."<tr><td>";
	 $stream.="<table><br><br>";
}
 
 
$stream.="<table class=sortable cellspacing=1 ".$stylekolom.">
      <thead>
        <tr>
          <th rowspan=2 align=center width=50>".$_SESSION['lang']['nourut']."</th>
          <th rowspan=2 align=center width=50>".$_SESSION['lang']['tanggalinvoice']."</th>
          <th rowspan=2 align=center width=50>Tipe ".$_SESSION['lang']['supplier']."</th>
          <th rowspan=2 align=center width=50>".$_SESSION['lang']['namasupplier']."</th>
          <th rowspan=2 align=center width=50>".$_SESSION['lang']['tipeinvoice']."</th>
          <th rowspan=2 align=center width=200>".$_SESSION['lang']['noinvoice']."</th>
          <th rowspan=2 align=center width=200>".$_SESSION['lang']['jatuhtempo']."</th>
          <th rowspan=2 align=center width=75>".$_SESSION['lang']['nodok']."</th>
          <th colspan=3 align=center width=75>".$_SESSION['lang']['nilaipokontrak']."/Referensi</th>
          <th colspan=5 align=center width=75>".$_SESSION['lang']['nilaiinvoice']."</th>
		  <th rowspan=2 align=center width=50>".$_SESSION['lang']['sekarang']."</th>
          <th align=center colspan=4 width=400>".$_SESSION['lang']['sudahjatuhtempo']."</th>
          <th rowspan=2 align=center width=100>".$_SESSION['lang']['dibayar']."</th>
          <th rowspan=2 align=center width=50>".$_SESSION['lang']['jmlh_hari_outstanding']."</th>";
        if ($_SESSION['empl']['bagian']=='FNC' && $_SESSION['empl']['tipekaryawan']=='9') {
          $stream.="<th rowspan=2 align=center><input type='checkbox' id='btnall' onclick='checkAll()'></th>
                <th rowspan=2 align=center>Jumlah yang diproses bayar</th>";
        }else{
          $stream.="<th rowspan=2 align=center>Jumlah yang diproses bayar</th>";
          $stream.="<th rowspan=2 align=center>Saldo Blokir</th>";
          $stream.="<th rowspan=2 align=center>Keterangan (Blokir)</th>";
        }
          
    $stream.="</tr>  
        <tr>
          <th align=center width=50>DPP</th>
          <th align=center width=50>PPN</th>
          <th align=center width=50>Total</th>
          <th align=center width=50>DPP</th>
          <th align=center width=50>PPN</th>
          <th align=center width=50 nowrap>".strtoupper($_SESSION['lang']['pengurang'])."</th>
          <th align=center width=50 nowrap>".strtoupper($_SESSION['lang']['uangmuka'])."</th>
          <th align=center width=50 nowrap>Total</th>
          <th align=center width=50 nowrap>1-30 ".$_SESSION['lang']['hari']."</th>
          <th align=center width=50 nowrap>31-60 ".$_SESSION['lang']['hari']."</th>
          <th align=center width=50 nowrap>61-90 ".$_SESSION['lang']['hari']."</th>
          <th align=center width=50 nowrap>over 90 ".$_SESSION['lang']['hari']."</th>
        </tr>  
      </thead>
      <tFNCy>";

//=================================================
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
    $no=0;
    if($numrows<1)
    {
        $stream.="<tr class=rowcontent><td colspan=26>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    else
    {
        $total0=$total30=$total60=$total90=$total100=$totaldibayar=$totalinvoice=$totalnilaium=0;
        while($bar=$res->fetch()){
            $optNmSup=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$bar->kodesupplier."'");
            $namasupplier	=$optNmSup[$bar->kodesupplier];
            if($namasupplier=='')$namasupplier='&nbsp;';
            $noinvoice	=$bar->noinvoice;
            $tanggal	=$bar->tanggalinvoice; 
            $kodesupplier    =$bar->kodesupplier;
            $jatuhtempo 	=$bar->jatuhtempo;
            $nopokontrak    =$bar->nopo;
            $sDetnota="select sum(nilaiinvoice*-1) as nildebet from ".$dbname.".keu_notadebet_ht where noinvoice_referensi='".$noinvoice."' group by noinvoice_referensi";
            $rDetnota=fetchdata($sDetnota);
            $pengurang=$ayatsilang[$noinvoice]+$nilUangMuka[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet'];
            $nilaidpp=($bar->nilaidpp+abs($nilaium[$noinvoice]));
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
              $nilaiinvoice=($bar->nilaiinvoice-$nilPPn[$noinvoice]);
              //+$nilUangMuka[$noinvoice]+$ayatsilang[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet'];
              // $nilcek=$bar->nilaiinvoice+$pengurang;
              $nilcek=$bar->nilaidpp+$pengurang;
              //$ayatsilang[$noinvoice]+$nilUangMuka[$noinvoice]+$nilpph[$noinvoice]+$bylain[$noinvoice]+$rDetnota[0]['nildebet'];
            }
            
            $nilaipo        =$listPo[$nopokontrak]['subtotal'];
            $nilaippnpo     =$listPo[$nopokontrak]['ppn'];
            $nilaikontrak   =$bar->kurs*$bar->nilaikontrak;
            // $nilaiinvoice 	=$bar->kurs*$bar->nilaiinvoice;
            $nilaippninv   =$nilPPn[$noinvoice];
			/*
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
			*/
			
			if($bar->tipeinvoice=='ffb'){
				if($nilPPn[$noinvoice]!=0){
                        $nilaiinvoice=0;
                        $nilcek=$nilPPn[$noinvoice];
				}
			}
            
            $nilaipokontrak =$nilaipo;
            if($nilaikontrak>0)$nilaipokontrak=$nilaikontrak;
            //$date1=date('Y-m-d');
            $date1=tanggalbiasa($tanggalpivot2);
            // $diff =(strtotime($tanggal)-strtotime($date1));
            $diff =(strtotime($jatuhtempo)-strtotime($date1)); //request bu vini tgl 6/6/23
            $outstd =floor(($diff)/(60*60*24));
			// if($outstd<1)$outstd=0;
            
			$dibayar  =$dibyarkan[$noinvoice]['dibayar'];
            // $sisainvoice    =$nilcek-$dibayar;
            $sisainvoice    =$nilaidpp+$nilaippninv+$pengurang+$nilaium[$noinvoice]-$dibayar;
			// echo $sisainvoice.____________;
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

            if ($flag30==1) {
              $nilaidibayar=0;
            }else{
              $nilaidibayar=$sisainvoice;
            }

            $totaldibayar+=$dibayar;
            if($jatuhtempo=='0000-00-00'){ $outstd=''; $jatuhtempo=''; }else{ $jatuhtempo=tanggalnormal($jatuhtempo); }
            //if($dibayar>=$nilaiinvoice)continue;
            $no+=1;
            //<td align=right width=100>".number_format($nilaiinvoice,2)."</td>
			$optNmSupptipe=makeOption($dbname,'log_5supkelompok','supplierid,tipe',"supplierid='".$kodesupplier."'");
          $stream.="<tr class=rowcontent>
                <td align=center width=20>".$no."</td>
                <td>".tanggalnormal($tanggal)."</td>
                <td>".$optNmSupptipe[$kodesupplier]."</td>  
				<td>".$namasupplier."</td> 
                <td>".$bar->tipeinvoice."</td> 
                <td id='noinv_".$no."'>".$noinvoice."</td> 
                <td align=center>".$jatuhtempo."</td>
                <td>".$nopokontrak."</td>
                <td  align=right>".hidezerodecimal($nilaipokontrak,2)."</td>
                <td  align=right>".hidezerodecimal($nilaippnpo,2)."</td>
                <td  align=right>".hidezerodecimal(($nilaippnpo+$nilaipokontrak),2)."</td>
                <td  align=right>".hidezerodecimal($nilaidpp,2)."</td>
                <td  align=right>".hidezerodecimal($nilaippninv,2)."</td>
                <td  align=right>".hidezerodecimal($pengurang,2)."</td>";
				$stream.="<td align=right>".hidezerodecimal($nilaium[$noinvoice],2)."</td>";
                $stream.="<td  align=right>".hidezerodecimal(($nilaidpp+$nilaippninv+$pengurang+$nilaium[$noinvoice]),2)."</td>"; 

				//buat total uang muka
				$totalnilaium+=$nilaium[$noinvoice];
				
                @$totalinvoice+=$nilaidpp+$nilaippninv+$pengurang;
				if($flag0==1){
					$stream.="<td align=right>".hidezerodecimal($sisainvoice,2)."</td>";
				}else{
					$stream.="<td></td>";
				}
				
				
                if($flag30==1){
					$stream.="<td align=right>".hidezerodecimal($sisainvoice,2)."</td>";
				}else{
					$stream.="<td></td>";
				}
				
				 if($flag60==1){
					$stream.="<td align=right>".hidezerodecimal($sisainvoice,2)."</td>";
				}else{
					$stream.="<td></td>";
				}
				
				 if($flag90==1){
					$stream.="<td align=right>".hidezerodecimal($sisainvoice,2)."</td>";
				}else{
					$stream.="<td></td>";
				}
				
				if($flag100==1){
					$stream.="<td align=right>".hidezerodecimal($sisainvoice,2)."</td>";
				}else{
					$stream.="<td></td>";
				}
				
				$stream.="<td align=right>".hidezerodecimal($dibayar,2)."</td>";
				if($outstd<1)$outstd=0; //indra nanti di0kan
                $stream.="<td  align=right>".$outstd."</td>";
				
				
            if ($_SESSION['empl']['bagian']=='FNC' && $_SESSION['empl']['tipekaryawan']=='9') {

              $str1="select tanggal,bayar from ".$dbname.".keu_prosesaging where rekening='".$rekening."' 
              and noinvoice='".$noinvoice."' order by tanggal desc limit 1 ";
              $res1 = fetchData($str1);
              $bar1=$res1[0];
              $nilaiprosesbayar=$bar1['bayar'];
              $tanggalprosesbayar=$bar1['tanggal'];

              $statbayar=1;##jika belum pernah diproses jg statusnya 1
              $disable="";
              if (count($res1)>0) {
                $statbayar=0;
                $disable="disabled";
                $str2="select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1='".$noinvoice."' and tanggal>='$tanggalprosesbayar' and posting='1'";
                $res2 = fetchData($str2);
                if (count($res2)>0){
                  $nilaiprosesbayar='';
                  $statbayar=1;
                  $disable="";
                }
              }
              
              $stream.="<input type=hidden id='nilai_".$no."' value='".$nilaidibayar."' >
                <input type=hidden id='statbayar_".$no."' value='".$statbayar."' >
                <td   style=cursor:pointer><input type='checkbox' onclick=check1('".$no."') id='no_".$no."' ".$disable."></td>
                <td   style=cursor:pointer><input type=text id='bayar_".$no."' value='".$nilaiprosesbayar."' class=myinputtextnumber style=width:90px; onkeypress=\"return angka_doang(event);\" disabled=disabled></td>";
            }else{

              $str1="select bayar,saldoblokir,ketblokir from ".$dbname.".keu_prosesaging where tanggal>='".tanggalsystemn($tanggalpivot)."' and tanggal<='".tanggalsystemn($tanggalpivot2)."' and rekening='".$rekening."' 
              and noinvoice='".$noinvoice."' order by tanggal desc limit 1 ";
              $res1=$owlPDO->query($str1);
              $res1->setFetchMode(PDO::FETCH_ASSOC);
              $bar1=$res1->fetch();
              $nilaiprosesbayar=$bar1['bayar'];
              $nilaisaldoblokir=$bar1['saldoblokir'];
              $ketsaldoblokir=$bar1['ketblokir'];

              $stream.="<td align=right>".hidezerodecimal($nilaiprosesbayar,2)."</td>";
              $stream.="<td align=right>".hidezerodecimal($nilaisaldoblokir,2)."</td>";
              $stream.="<td align=right>".$ketsaldoblokir."</td>";
            }
            
         
        }
		$stream.="<tr class=rowcontent>";
		  $stream.="<input type=hidden id=totrow value=".$no.">";
		  $stream.="<td colspan=15 align=center width=20>TOTAL</td>";
		  $stream.="<td  align=center>".hidezerodecimal($totalinvoice,2)."</td>";
		   
		   $stream.="<td align=right>".hidezerodecimal($total0,2)."</td>";
		   $stream.="<td align=right>".hidezerodecimal($total30,2)."</td>";
		   $stream.="<td align=right>".hidezerodecimal($total60,2)."</td>";
		   $stream.="<td align=right>".hidezerodecimal($total90,2)."</td>";
		   $stream.="<td align=right>".hidezerodecimal($total100,2)."</td>";
		   $stream.="<td align=right width=100>".hidezerodecimal($totaldibayar,2)."</td>";
		  
		if ($_SESSION['empl']['bagian']=='FNC' && $_SESSION['empl']['tipekaryawan']=='9') {
		  $stream.="<td colspan=3 align='center'><button class=mybutton onclick=adddetail('".$param['nokontrak']."','".$param['kodebarang']."','".$param['kdcust']."')>".$_SESSION['lang']['addtodetail']."</button></td>";
		}else{
		  $stream.="<td colspan=4></td>";
		}
		
		$stream.="</tr>";                 
    }
	 $stream.="</table>";          
	// exit("Error:$stream");
	if($tipelaporan=='excel'){
		$nop = "Aging_Finance_".$pt."_".$tanggalv."_".$tanggalv2.".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("data", $stream);
		$xls->headers($nop);
		echo $xls->buildFile();
	}else{
		echo $stream;
	}
	
	

?>