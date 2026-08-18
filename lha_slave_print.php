<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');



$proses=checkPostGet('proses','');
$lksiTgs=$_SESSION['empl']['lokasitugas'];
$kdOrg=checkPostGet('kdOrg','');
$kdAfd=checkPostGet('kdAfd','');
$tgl1_=checkPostGet('tgl1','');
$tgl2_=checkPostGet('tgl2','');
if(($proses=='excel')or($proses=='pdf')){
	$kdOrg=$_GET['kdOrg'];
	$kdAfd=$_GET['kdAfd'];
	$tgl1_=$_GET['tgl1'];  
	$tgl2_=$_GET['tgl2'];  
}
if($kdAfd=='')
    $kdAfd=$kdOrg;
$lha=true; if($tgl2_!='')$lha=false;

// luas areal
$luas=0;
          $str="select luasareaproduktif from ".$dbname.".setup_blok 
                where kodeorg like '".$kdAfd."%'";
          $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		  $res->setFetchMode(PDO::FETCH_OBJ);
		  while($bar=$res->fetch())

          {
              $luas+=$bar->luasareaproduktif;
          }
//          echo $luas;

$tgl1_=tanggalsystem($tgl1_); $tgl1=substr($tgl1_,0,4).'-'.substr($tgl1_,4,2).'-'.substr($tgl1_,6,2);
$tgl2_=tanggalsystem($tgl2_); $tgl2=substr($tgl2_,0,4).'-'.substr($tgl2_,4,2).'-'.substr($tgl2_,6,2);
// $tglqwe1=juliantojd(substr($tgl1_,4,2),substr($tgl1_,6,2),substr($tgl1_,0,4));
// $tglqwe2=juliantojd(substr($tgl2_,4,2),substr($tgl2_,6,2),substr($tgl2_,0,4));
// $jumlahhari=1+$tglqwe2-$tglqwe1;

if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if($kdOrg==''){
            echo"Error: Organization/estate required."; exit;
    }

    if($tgl1_==''){
            echo"Error: date required."; exit;
    }
	
}
    

 
$str="select kodekegiatan,namakegiatan,namakegiatan1 from ".$dbname.".setup_kegiatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())

	{
    if($_SESSION['language']=='EN'){
        $kegiatanx[$bar->kodekegiatan]=$bar->namakegiatan1;
    }else
    {
                $kegiatanx[$bar->kodekegiatan]=$bar->namakegiatan;
    }
}

    if($proses=='getAfdAll'){ 
          $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                where kodeorganisasi like '".$kdAfd."%' and length(kodeorganisasi)=6 and tipe ='AFDELING' order by namaorganisasi";
          $op="<option value=''>".$_SESSION['lang']['all']."</option>";
          $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		  $res->setFetchMode(PDO::FETCH_OBJ);
		  while($bar=$res->fetch())

          {
              $op.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
          }
          echo $op;
    }
    else{
        if($_SESSION['language']=='EN'){
            $caption='<b><font size=5>DAILY DIVISION REPORT</font></b>';
        }else{
            $caption='<b><font size=5>LAPORAN HARIAN DIVISI</font></b>';
        }
        $str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in(select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kdAfd,0,4)."') limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){

            $namapt=$bar->namaorganisasi;
        }
        
        if($lha)$tanggalsampai=''; else $tanggalsampai=tanggalnormal($tgl2_);
        $stream.="<table width=100% border=0>
                 <tr>
                     <td colspan=6><b>".$namapt."</b></td>
                 </tr>
                 <tr>
                     <td align=center colspan=6>
                    ".$caption."
                     </td>
                 </tr>
                 <tr>
                     <td style='width:75px;'>".$_SESSION['lang']['kebun']."</td>
					 <td width=10px>:</td>
					 <td style='width:75px;'>".substr($kdAfd,0,4)."</td>
					 <td></td>
					 <td style='width:200px;' align=center>".$_SESSION['lang']['diperiksa']."</td>
					 <td style='width:200px;' align=center>".$_SESSION['lang']['dibuat']."</td>
                 </tr>
                 <tr>
                    <td>".$_SESSION['lang']['afdeling']."</td>
					<td width=10px>:</td>
					<td>".$kdAfd."</td>
					<td></td>
					<td> </td>
					<td> </td>
                 </tr>
                 <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
					<td width=10px>:</td>
					<td>".tanggalnormal($tgl1_)."</td>
					<td>".$tanggalsampai."</td>
					<td> </td>
					<td> </td>
                 </tr>   
                 <tr>
                    <td>Luas</td>
					<td>:</td>
					<td>".@number_format($luas,2)." Ha</td>
					<td></td>
					<td align=center>".$_SESSION['lang']['askep']."</td>
					<td align=center>".$_SESSION['lang']['asisten']."</td>
                 </tr>                   
                </table>";
	if($proses=='excel')
                $stream.="<table border='1'>";
        else {
              $stream.="<table cellspacing='1' border='0' class=sortable width=100%>";
            }
	$stream.="<thead>
	<tr class=rowheader>
        <td rowspan=2 align=center  >".$_SESSION['lang']['kode']."</td>
        <td rowspan=2 align=center>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>    
		<td rowspan=2 align=center>".$_SESSION['lang']['satuan']."</td>
		<td colspan=2 align=center>".$_SESSION['lang']['kodeblok']."</td>            
		<td rowspan=2 align=center>".$_SESSION['lang']['thntnm']."</td>
		<td colspan=2 align=center>HK KHT/KHL</td>    
		<td colspan=2 align=center>HK PB</td>
		<td colspan=2 align=center>".$_SESSION['lang']['upahkerja']."</td>
		<td colspan=2 align=center>".$_SESSION['lang']['upahpremi']."</td>
		<td colspan=2 align=center>Total Upah</td>
		<td colspan=2 align=center>".$_SESSION['lang']['hasilkerjajumlah']."</td>
		<td colspan=4 align=center>".$_SESSION['lang']['pemakaianBarang']."</td>
        <td colspan=2 align=center>".$_SESSION['lang']['material']." ".$_SESSION['lang']['biaya']."</td>
		<td rowspan=2 align=center>".$_SESSION['lang']['totalbiaya']."</td>
        <td rowspan=2 align=center>Rp/".$_SESSION['lang']['satuan']."</td>    
        <td rowspan=2 align=center>HK/".$_SESSION['lang']['satuan']."</td>    
    </tr>
        
 	<tr class=rowheader>
        <td align=center>".$_SESSION['lang']['blok']."</td>
        <td align=center>".$_SESSION['lang']['luas']."</td>";    
        
		if($lha)$stream.="<td align=center>".$_SESSION['lang']['hi']."</td>    
		<td align=center>".$_SESSION['lang']['sdhi']."</td>"; else $stream.="<td align=center colspan=2></td>";
        
		if($lha)$stream.="<td align=center>".$_SESSION['lang']['hi']."</td>    
		<td align=center>".$_SESSION['lang']['sdhi']."</td>"; else $stream.="<td align=center colspan=2></td>";
        
		$stream.="<td align=center>Rp/unit</td>            
		<td align=center>".$_SESSION['lang']['jumlah']."</td>";
		$stream.="<td align=center>Rp/unit</td>            
		<td align=center>".$_SESSION['lang']['jumlah']."</td>";
		$stream.="<td align=center>Rp/unit</td>            
		<td align=center>".$_SESSION['lang']['jumlah']."</td>";
		
		if($lha)$stream.="<td align=center>".$_SESSION['lang']['hi']."</td>
        <td align=center>".$_SESSION['lang']['sdhi']."</td>"; else $stream.="<td align=center colspan=2></td>";
		
		$stream.="<td align=center>".$_SESSION['lang']['namabarang']."</td>
		<td align=center>".$_SESSION['lang']['satuan']."</td>";
		
		if($lha)$stream.="<td align=center>".$_SESSION['lang']['hi']."</td>
        <td align=center>".$_SESSION['lang']['sdhi']."</td>"; else $stream.="<td align=center colspan=2>".$_SESSION['lang']['kuantitas']."</td>";
        
		$stream.="<td align=center>Rp/unit</td>            
		<td align=center>".$_SESSION['lang']['jumlah']."</td>   
	</tr>       
	</thead>
	<tbody>";
	
	$luasblok=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif');
	$master=Array();  
    if($lha)$str="select distinct kodekegiatan,kodeorg,namakegiatan,satuan from ".$dbname.".kebun_perawatan_dan_spk_vw where kodeorg like '".$kdAfd."%' 
             and tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."'";
    else $str="select distinct kodekegiatan,kodeorg,namakegiatan,satuan from ".$dbname.".kebun_perawatan_dan_spk_vw where kodeorg like '".$kdAfd."%' 
             and tanggal between '".$tgl1_."' and '".$tgl2_."'";
			 
			 
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $master['kegiatan'][]=$bar->kodekegiatan;
            $master['blok'][]=$bar->kodeorg;
            $master['namakegiatan'][]=$bar->namakegiatan;
            $master['satuankegiatan'][]=$bar->satuan;
        }
    $str="select kodeorg,tahuntanam,luasareaproduktif from ".$dbname.".setup_blok where kodeorg  like '".$kdAfd."%'";   

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
            $blok[$bar->kodeorg]['kode']=$bar->kodeorg;
            $blok[$bar->kodeorg]['thntnm']=$bar->tahuntanam;
            $blok[$bar->kodeorg]['luas']=$bar->luasareaproduktif;
        } 
        if(!empty($master['blok']))foreach($master['blok'] as $key=>$val)
        {          
            @$master['luas'][$key]=$bar->luasareaproduktif;
            @$master['thntnm'][$key]=$bar->tahuntanam;
            if($val==@$blok[$val]['kode'])
            {
                $master['luas'][$key]=$blok[$val]['luas'];
                $master['thntnm'][$key]=$blok[$val]['thntnm'];
            }

        }
//RKH -==========================================   
	$rkh="select notransaksi from ".$dbname.".kebun_rkhht ";
    if($lha){
		$rkh .= "where divisi like '".$kdAfd."%' and tanggal='".$tgl1_."' and status = '1';";  
	}else{
		$rkh .= "where divisi like '".$kdAfd."%' and tanggal between '".$tgl1_."' and '".$tgl2_."' and status = '1';";  
	}
	
	$datarkh=Array();
	$res=$owlPDO->query($rkh) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$datarkh[] = "'".$bar->notransaksi."'";
	}
	$in = "notransaksi in (".implode(',',$datarkh).")";
	$dtrkh=Array();
	$matrkh=Array();
	//detail
	
	if(count($datarkh) > 0){
		$rkh="select * from ".$dbname.".kebun_rkh_dt where ".$in;
		$res=$owlPDO->query($rkh) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$d=Array();
		while($bar=$res->fetch()){
			$d['notransaksi'] 	= $bar->notransaksi;
			$d['kodekegiatan'] 	= $bar->kodekegiatan;
			$d['kodeblok'] 	   	= $bar->kodeblok;
			$d['statusblok'] 	= $bar->statusblok;
			$d['rotasi'] 		= $bar->rotasi;
			$d['target'] 		= $bar->target;
			$d['hk_pb'] 		= $bar->hk_pb;
			$d['hk_khl'] 		= $bar->hk_khl;
			$d['hk_bor'] 		= $bar->hk_bor;
			$d['jmlh_tbs'] 		= $bar->jmlh_tbs;
			$d['angkutan'] 		= $bar->angkutan;
			$dtrkh[$bar->kodekegiatan.$bar->kodeblok]	= $d;
		}
		//material
		/* tidak terpakai
		$rkh="select * from ".$dbname.".kebun_rkh_dtmaterial where ".$in;
		$res=$owlPDO->query($rkh) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$d=Array();
		while($bar=$res->fetch()){
			$d['notransaksi'] 	= $bar->notransaksi;
			$d['kodebarang']	= $bar->kodebarang;
			$d['jumlah'] 		= $bar->statusblok;
			$matrkh[]			= $d;
		}
		*/
	}

//upah KBL-==========================================        
    if($lha)$str="select kodeorg,kodekegiatan,sum(jhk) as jhk,sum(umr) as upah, sum(insentif) as premi, sum(umr + insentif) as totalupah
          from ".$dbname.".kebun_kehadiran_vw
          where  kodeorg like '".$kdAfd."%' and tanggal='".$tgl1_."' and kodetipekaryawan='1' 
          group by kodeorg,kodekegiatan;";  
    else $str="select kodeorg,kodekegiatan,sum(jhk) as jhk, sum(umr) as upah, sum(insentif) as premi, sum(umr + insentif) as totalupah
          from ".$dbname.".kebun_kehadiran_vw
          where  kodeorg like '".$kdAfd."%' and tanggal between '".$tgl1_."' and '".$tgl2_."' and kodetipekaryawan='1' 
          group by kodeorg,kodekegiatan;";  
//    echo $str;
		$hkKBL=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hkKBL['kegiatan'][]=$bar->kodekegiatan;
            $hkKBL['blok'][]=$bar->kodeorg;
            $hkKBL['jhk'][]=$bar->jhk;
            $hkKBL['upah'][]=$bar->upah;
			$hkKBL['premi'][]=$bar->premi;
			$hkKBL['totalupah'][]=$bar->totalupah;
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            $master['hkkbl'][$key]=0;
            $master['upahkbl'][$key]=0;
			$master['premikbl'][$key]=0;
			$master['totalupahkbl'][$key]=0;
            if(!empty($hkKBL)){
                if(!empty($hkKBL['kegiatan']))foreach($hkKBL['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hkKBL['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hkkbl'][$key]=$hkKBL['jhk'][$g];
                        $master['upahkbl'][$key]=$hkKBL['upah'][$g];
						$master['premikbl'][$key]=$hkKBL['premi'][$g];
						$master['totalupahkbl'][$key]=$hkKBL['totalupah'][$g];
                    }
            }
            }
        }  
     //====================sdbi
    $str="select kodeorg,kodekegiatan,sum(jhk) as jhk,sum(umr) as upah, sum(insentif) as premi, sum(umr + insentif) as totalupah
          from ".$dbname.".kebun_kehadiran_vw
          where  kodeorg like '".$kdAfd."%' and tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."' and kodetipekaryawan='1' and umr != '0'
          group by kodeorg,kodekegiatan;";  
  
     $hkKBL=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hkKBL['kegiatan'][]=$bar->kodekegiatan;
            $hkKBL['blok'][]=$bar->kodeorg;
            $hkKBL['jhk'][]=$bar->jhk;
            $hkKBL['upah'][]=$bar->upah;
			$hkKBL['premi'][]=$bar->premi;
			$hkKBL['totalupah'][]=$bar->totalupah;
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            $master['hkkblsbi'][$key]=0;
            $master['upahkblsbi'][$key]=0;
			$master['premikblsbi'][$key]=0;
			$master['totalupahkblsbi'][$key]=0;
			if(!empty($hkKBL)){
                if(!empty($hkKBL['kegiatan']))foreach($hkKBL['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hkKBL['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hkkblsbi'][$key]=$hkKBL['jhk'][$g];
                        $master['upahkblsbi'][$key]=$hkKBL['upah'][$g];
						$master['premikblsbi'][$key]=$hkKBL['premi'][$g];
						$master['totalupahkblsbi'][$key]=$hkKBL['totalupah'][$g];
                    }
            }
            }
        }    
//upah KHT/KHL-==========================================
        if($lha)$str="select kodeorg,kodekegiatan,sum(jhk) as jhk,sum(umr) as upah, sum(insentif) as premi, sum(umr + insentif) as totalupah
		  from ".$dbname.".kebun_kehadiran_vw
          where  kodeorg like '".$kdAfd."%' and tanggal='".$tgl1_."' and kodetipekaryawan in('4','3','2','6')
          group by kodeorg,kodekegiatan;";  
        else $str="select kodeorg,kodekegiatan,sum(jhk) as jhk,sum(umr) as upah, sum(insentif) as premi, sum(umr + insentif) as totalupah
		  from ".$dbname.".kebun_kehadiran_vw
          where  kodeorg like '".$kdAfd."%' and tanggal between '".$tgl1_."' and '".$tgl2_."' and kodetipekaryawan in('4','3','2','6') 
          group by kodeorg,kodekegiatan;";  
        //echo $str;
     $hkKHL=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hkKHL['kegiatan'][]=$bar->kodekegiatan;
            $hkKHL['blok'][]=$bar->kodeorg;
            $hkKHL['jhk'][]=$bar->jhk;
            $hkKHL['upah'][]=$bar->upah;  
			$hkKHL['premi'][]=$bar->premi;
			$hkKHL['totalupah'][]=$bar->totalupah;          
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            $master['hkkhl'][$key]=0;
            $master['upahkhl'][$key]=0;
			$master['premikhl'][$key]=0;
			$master['totalupahkhl'][$key]=0;
            if(count($hkKHL['kegiatan'])>0){
                if(!empty($hkKHL['kegiatan']))foreach($hkKHL['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hkKHL['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hkkhl'][$key]=$hkKHL['jhk'][$g];
                        $master['upahkhl'][$key]=$hkKHL['upah'][$g];
						$master['premikhl'][$key]=$hkKHL['premi'][$g];
						$master['totalupahkhl'][$key]=$hkKHL['totalupah'][$g];
                    }
            }
            }
        }       
//   tambahkan biaya dari kontrak ke bhl
        if($lha)$str="select kodeblok as kodeorg,kodekegiatan,sum(hkrealisasi) as jhk,sum(jumlahrealisasi) as upah from ".$dbname.".log_baspk
          where  kodeblok like '".$kdAfd."%' and tanggal='".$tgl1_."'
          group by kodeblok,kodekegiatan;";  
        else $str="select kodeblok as kodeorg,kodekegiatan,sum(hkrealisasi) as jhk,sum(jumlahrealisasi) as upah from ".$dbname.".log_baspk
          where  kodeblok like '".$kdAfd."%' and tanggal between '".$tgl1_."' and '".$tgl2_."'
          group by kodeblok,kodekegiatan;";  
     $hkKHL=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hkKHL['kegiatan'][]=$bar->kodekegiatan;
            $hkKHL['blok'][]=$bar->kodeorg;
            $hkKHL['jhk'][]=$bar->jhk;
            $hkKHL['upah'][]=$bar->upah;
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            if(count(@$hkKHL['kegiatan'])>0){
                if(!empty($hkKHL['kegiatan']))foreach($hkKHL['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hkKHL['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hkkhl'][$key]+=$hkKHL['jhk'][$g];
                        $master['upahkhl'][$key]+=$hkKHL['upah'][$g];
						$master['totalupahkhl'][$key]+=$hkKHL['upah'][$g];
                    }
            }
            }
        }           
  //=======sbi
        $str="select kodeorg,kodekegiatan,sum(jhk) as jhk,sum(umr) as upah, sum(insentif) as premi, sum(umr + insentif) as totalupah
		  from ".$dbname.".kebun_kehadiran_vw
          where  kodeorg like '".$kdAfd."%' and tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."' 
		  and kodetipekaryawan in('4','3','2','6') and umr != '0'
          group by kodeorg,kodekegiatan;";  
     $hkKHL=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hkKHL['kegiatan'][]=$bar->kodekegiatan;
            $hkKHL['blok'][]=$bar->kodeorg;
            $hkKHL['jhk'][]=$bar->jhk;
            $hkKHL['upah'][]=$bar->upah; 
			$hkKHL['premi'][]=$bar->premi;
			$hkKHL['totalupah'][]=$bar->totalupah;
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            $master['hkkhlsbi'][$key]=0;
            $master['upahkhlsbi'][$key]=0;
			$master['premikhlsbi'][$key]=0;
			$master['totalupahkhlsbi'][$key]=0;
            if(count(@$hkKHL['kegiatan'])>0){
                if(!empty($hkKHL['kegiatan']))foreach($hkKHL['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hkKHL['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hkkhlsbi'][$key]=$hkKHL['jhk'][$g];
                        $master['upahkhlsbi'][$key]=$hkKHL['upah'][$g];
						$master['premikhlsbi'][$key]=$hkKHL['premi'][$g];
						$master['totalupahkhlsbi'][$key]=$hkKHL['totalupah'][$g];
                    }
            }
            }
        }       
//   tambahkan biaya dari kontrak ke bhl
        $str="select kodeblok as kodeorg,kodekegiatan,sum(hkrealisasi) as jhk,sum(jumlahrealisasi) as upah from ".$dbname.".log_baspk
          where  kodeblok like '".$kdAfd."%' and tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."'
          group by kodeblok,kodekegiatan;";  
     $hkKHL=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hkKHL['kegiatan'][]=$bar->kodekegiatan;
            $hkKHL['blok'][]=$bar->kodeorg;
            $hkKHL['jhk'][]=$bar->jhk;
            $hkKHL['upah'][]=$bar->upah;            
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            if(count(@$hkKHL['kegiatan'])>0){
                if(!empty($hkKHL['kegiatan']))foreach($hkKHL['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hkKHL['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hkkhlsbi'][$key]+=$hkKHL['jhk'][$g];
                        $master['upahkhlsbi'][$key]+=$hkKHL['upah'][$g];
						$master['totalupahkhlsbi'][$key]+=$hkKHL['upah'][$g];
                    }
            }
            }
        }    
//total biaya upah==========================================================
    if(!empty($master['totalupahkhl']))foreach($master['totalupahkhl'] as $kut=>$uk)
    {
		$master['totalupah'][$kut]=$master['upahkbl'][$kut]+$master['upahkhl'][$kut];
		$master['totalpremi'][$kut]=$master['premikbl'][$kut]+$master['premikhl'][$kut];
        $master['totalsumupah'][$kut]=$master['totalupahkbl'][$kut]+$master['totalupahkhl'][$kut];
		@$master['upahperhk'][$kut]=$master['totalupah'][$kut]/($master['hkkhl'][$kut]+$master['hkkbl'][$kut]);
		@$master['premiperhk'][$kut]=$master['totalpremi'][$kut]/($master['hkkhl'][$kut]+$master['hkkbl'][$kut]);
        @$master['rpperhk'][$kut]=$master['totalsumupah'][$kut]/($master['hkkhl'][$kut]+$master['hkkbl'][$kut]);
        $master['totbiaya'][$kut]=$master['totalsumupah'][$kut];
    }
//============================================================================        
//Hasil Kerja=========================================
        if($lha)$str="select kodeorg,kodekegiatan,sum(hasilkerja) as hasil from 
            ".$dbname.".kebun_perawatan_vw
          where  kodeorg like '".$kdAfd."%' and tanggal='".$tgl1_."' 
          group by kodeorg,kodekegiatan;";  
        else $str="select kodeorg,kodekegiatan,sum(hasilkerja) as hasil from 
            ".$dbname.".kebun_perawatan_vw
          where  kodeorg like '".$kdAfd."%' and tanggal between '".$tgl1_."' and '".$tgl2_."'
          group by kodeorg,kodekegiatan;";  
     $hasil=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hasil['kegiatan'][]=$bar->kodekegiatan;
            $hasil['blok'][]=$bar->kodeorg;
            $hasil['hasil'][]=$bar->hasil;           
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            $master['hasilbi'][$key]=0;
            if(count($hasil['kegiatan'])>0){
                if(!empty($hasil['kegiatan']))foreach($hasil['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hasil['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hasilbi'][$key]=$hasil['hasil'][$g];
                    }
            }
            }
        }       
//   tambahkan hasil dari spk
        if($lha)$str="select kodeblok as kodeorg,kodekegiatan,sum(hasilkerjarealisasi) as hasil
            from ".$dbname.".log_baspk
          where  kodeblok like '".$kdAfd."%' and tanggal='".$tgl1_."'
          group by kodeblok,kodekegiatan;";  
        else $str="select kodeblok as kodeorg,kodekegiatan,sum(hasilkerjarealisasi) as hasil
            from ".$dbname.".log_baspk
          where  kodeblok like '".$kdAfd."%' and tanggal between '".$tgl1_."' and '".$tgl2_."'
          group by kodeblok,kodekegiatan;";  
     $hasil=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hasil['kegiatan'][]=$bar->kodekegiatan;
            $hasil['blok'][]=$bar->kodeorg;
            $hasil['hasil'][]=$bar->hasil;           
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            if(count(@$hasil['kegiatan'])>0){
                if(!empty($hasil['kegiatan']))foreach($hasil['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hasil['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hasilbi'][$key]+=$hasil['hasil'][$g];
                    }
            }
            }
        }
// ============sbi
        $str="select kodeorg,kodekegiatan,sum(hasilkerja) as hasil from 
            ".$dbname.".kebun_perawatan_vw
          where  kodeorg like '".$kdAfd."%' and tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."' 
          group by kodeorg,kodekegiatan";   
		  //echo $str;
     $hasil=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hasil['kegiatan'][]=$bar->kodekegiatan;
            $hasil['blok'][]=$bar->kodeorg;
            $hasil['hasil'][]=$bar->hasil;           
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            $master['hasilsbi'][$key]=0;
            if(count(@$hasil['kegiatan'])>0){
                if(!empty($hasil['kegiatan']))foreach($hasil['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hasil['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hasilsbi'][$key]=$hasil['hasil'][$g];
                    }
            }
            }
        }       
//   tambahkan hasil dari spk
        $str="select kodeblok as kodeorg,kodekegiatan,sum(hasilkerjarealisasi) as hasil
            from ".$dbname.".log_baspk
          where  kodeblok like '".$kdAfd."%' and tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."'
          group by kodeblok,kodekegiatan;";  
		 
     $hasil=Array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())

        {
            $hasil['kegiatan'][]=$bar->kodekegiatan;
            $hasil['blok'][]=$bar->kodeorg;
            $hasil['hasil'][]=$bar->hasil;           
        }
        if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
        {          
            if(count(@$hasil['kegiatan'])>0){
                if(!empty($hasil['kegiatan']))foreach($hasil['kegiatan'] as $g=>$h){ 
                    if($val==$h and  $hasil['blok'][$g]==$master['blok'][$key])
                    {
                        $master['hasilsbi'][$key]+=$hasil['hasil'][$g];
                    }
            }
            }
        }
        
//========================bahan sbi
if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
{           
     if($lha)$str="SELECT a.kodekegiatan,a.kodeorg,a.kodebarang,sum(a.kwantitas) as qty,b.namabarang,b.satuan 
           FROM ".$dbname.".kebun_pakai_material_vw a left join ".$dbname.".log_5masterbarang b
           on a.kodebarang=b.kodebarang    
           where  tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."'       
           and a.kodeorg='".$master['blok'][$key]."' and a.kodekegiatan='".$val."'
           group by kodekegiatan,kodeorg,a.kodebarang";
     else $str="SELECT a.kodekegiatan,a.kodeorg,a.kodebarang,sum(a.kwantitas) as qty,b.namabarang,b.satuan 
           FROM ".$dbname.".kebun_pakai_material_vw a left join ".$dbname.".log_5masterbarang b
           on a.kodebarang=b.kodebarang    
           where tanggal between '".$tgl1_."'  and '".$tgl2_."'        
           and a.kodeorg='".$master['blok'][$key]."' and a.kodekegiatan='".$val."'
           group by kodekegiatan,kodeorg,a.kodebarang";
         $barang=Array();	 			
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$row=owlBaris($res);	 
	if($row<1)
    {
                $master['barangsbi'][$key][]=0;
                $master['kodebarangsbi'][$key][]=0;
                $master['satuanbarangsbi'][$key][]=0; 
                $master['qtysbi'][$key][]=0;          
     }   
     else
     {    
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
                $master['barangsbi'][$key][]=$bar->namabarang;
                $master['kodebarangsbi'][$key][]=$bar->kodebarang;
                $master['satuanbarangsbi'][$key][]=$bar->satuan; 
                $master['qtysbi'][$key][]=$bar->qty; 
        }
     }
}

//echo "<pre>";
//print_r($master);
//echo "</pre>";

if(!empty($master['kegiatan']))foreach($master['kegiatan'] as $key=>$val)
{           
     if($lha)$str="SELECT a.kodekegiatan,a.kodeorg,a.kodebarang,sum(a.kwantitas) as qty,b.namabarang,b.satuan 
           FROM ".$dbname.".kebun_pakai_material_vw a left join ".$dbname.".log_5masterbarang b
           on a.kodebarang=b.kodebarang    
           where  tanggal='".$tgl1_."'       
           and a.kodeorg='".$master['blok'][$key]."' and a.kodekegiatan='".$val."'
           group by kodekegiatan,kodeorg,a.kodebarang";
     else $str="SELECT a.kodekegiatan,a.kodeorg,a.kodebarang,sum(a.kwantitas) as qty,b.namabarang,b.satuan 
           FROM ".$dbname.".kebun_pakai_material_vw a left join ".$dbname.".log_5masterbarang b
           on a.kodebarang=b.kodebarang    
           where  tanggal between '".$tgl1_."'  and '".$tgl2_."'      
           and a.kodeorg='".$master['blok'][$key]."' and a.kodekegiatan='".$val."'
           group by kodekegiatan,kodeorg,a.kodebarang";

         $barang=Array();
     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	 $row=owlBaris($res);	 
	 if($row<1)
     {
           $master['qtybi'][$key][]=0;         
     }   
     else
     {   
         $res->setFetchMode(PDO::FETCH_OBJ);
		 while($bar=$res->fetch())
         {
                if(!empty($master['kodebarangsbi'][$key]))foreach($master['kodebarangsbi'][$key] as $kunci=>$isi)
                {
                    if($bar->kodebarang==$isi)
                         $master['qtybi'][$key][$kunci]=$bar->qty;
// kalo 2 baris di bawah ga disabled, barang dengan kunci 0 (urutan pertama) = 0                    
//                    else
//                         $master['qtybi'][$key][$kunci]=0;
                }
         }
     }
}

// echo"<pre>";
// print_r($master['kodebarangsbi']);
// echo"</pre>";

// ambil harga barang
$t=mktime(0,0,0,intval(substr($tgl1_,4,2)),15,intval(substr($tgl1_,0,4)));
$bl=date('Y-m',$t);
if($lha)$qwetgl=$tgl1_; else $qwetgl=$tgl2_;
$str="SELECT distinct b.kodebarang,a.hargarata FROM ".$dbname.".kebun_pakai_material_vw b
      left join ".$dbname.".log_5saldobulanan a
      on b.kodebarang=a.kodebarang
      where b.kodeorg like '".$kdAfd."%' and b.tanggal between '".substr($tgl1_,0,6)."01' and '".$qwetgl."'
      and a.periode='".$bl."'
      and a.kodeorg in(select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kdAfd,0,4)."')";           
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())

{
    $harga[$bar->kodebarang]=$bar->hargarata;
}
if(!empty($master['kodebarangsbi']))foreach($master['kodebarangsbi'] as $kuku=>$vx)
{
	if(!empty($master['kodebarangsbi'][$kuku]))foreach($master['kodebarangsbi'][$kuku] as $jack=>$pot)
	{
		setIt($harga[$pot],0);
		@$master['hargabarangbi'][$kuku][$jack]=$harga[$pot];
		@$master['bybarangbi'][$kuku][$jack]=$harga[$pot]*$master['qtybi'][$kuku][$jack];
		@$master['totbiaya'][$kuku]+=$master['bybarangbi'][$kuku][$jack];
	}
}




 
//============rupiah/ha
 if(!empty($master['totbiaya']))foreach($master['totbiaya'] as $kun=>$tak)
 {
     @$master['rppersatuan'][$kun]=$tak/$master['hasilbi'][$kun];
 }
//============hk/ha
if(!empty($master['totbiaya']))foreach($master['totbiaya'] as $kun=>$tak)
{
	@$master['hkpersatuan'][$kun]=($master['hkkhl'][$kun]+$master['hkkbl'][$kun])/$master['hasilbi'][$kun];
}
//Grand Total
if(!empty($master['blok']))foreach($master['blok'] as $kun=>$tak)
{
	$initTotal = array(
		'luas' => 0,
		'hkkhlbi' => 0,'hkkhlsbi' => 0,
		'hkkblbi' => 0,'hkkblsbi' => 0,
		'totalupah' => 0,
		'totalpremi' => 0,
		'totalsumupah' => 0,
		'hasilbi' => 0,'hasilsbi' => 0,
		'totalbiaya' => 0,
		'rppersatuan' => 0,'hkpersatuan' => 0,
		'totalbiayabarang' => 0
	);
	setIt($TOTAL,$initTotal);
	$TOTAL['luas']+=$master['luas'][$kun];
	$TOTAL['hkkhlbi']+=$master['hkkhl'][$kun];
	$TOTAL['hkkhlsbi']+=$master['hkkhlsbi'][$kun];
	$TOTAL['hkkblbi']+=$master['hkkbl'][$kun];
	$TOTAL['hkkblsbi']+=$master['hkkblsbi'][$kun];     
	$TOTAL['totalupah']+=$master['totalupah'][$kun];
	$TOTAL['totalpremi']+=$master['totalpremi'][$kun];
	$TOTAL['totalsumupah']+=$master['totalsumupah'][$kun];
	$TOTAL['hasilbi']+=$master['hasilbi'][$kun];
	$TOTAL['hasilsbi']+=$master['hasilsbi'][$kun];
	$TOTAL['totalbiaya']+=$master['totbiaya'][$kun];
	$TOTAL['rppersatuan']+=$master['rppersatuan'][$kun];
	$TOTAL['hkpersatuan']+=$master['hkpersatuan'][$kun];
	if(!empty($master['bybarangbi'][$kun]))foreach($master['bybarangbi'][$kun] as $la=>$li)
	{
	   $TOTAL['totalbiayabarang']+=$li; 
	}    
} 
@$TOTAL['upahperhk']=$TOTAL['totalupah']/($TOTAL['hkkhlbi']+$TOTAL['hkkblbi']);
@$TOTAL['premiperhk']=$TOTAL['totalpremi']/($TOTAL['hkkhlbi']+$TOTAL['hkkblbi']);
@$TOTAL['rpperhk']=$TOTAL['totalsumupah']/($TOTAL['hkkhlbi']+$TOTAL['hkkblbi']);
 
// echo"<pre>";
 // print_r($master['barangsbi']);
 // echo"</pre>";
 //PRINT OUT============================================
   if(!empty($master['blok']))foreach($master['blok'] as $kunc=>$va){
			$tidakadadiRKH = "";
			if(empty($dtrkh[$master['kegiatan'][$kunc].$master['blok'][$kunc]])){
				$tidakadadiRKH = "style='background:orange;'";
			}
            $stream.="<tr class=rowcontent ".$tidakadadiRKH.">
            <td>".$master['kegiatan'][$kunc]."</td>
            <td>".$kegiatanx[$master['kegiatan'][$kunc]]."</td>    
            <td>".$master['satuankegiatan'][$kunc]."</td>
            <td>".$master['blok'][$kunc]."</td>
            <td align=right>".@number_format($luasblok[$master['blok'][$kunc]],2)."</td>
            <td>".$master['thntnm'][$kunc]."</td>";
			setIt($tunasdata[$master['kegiatan'][$kunc]]['hk'],0);
			setIt($tunasdata[$master['kegiatan'][$kunc]]['hksbi'],0);
			setIt($tunasdata[$master['kegiatan'][$kunc]]['hasilbi'],0);
			setIt($tunasdata[$master['kegiatan'][$kunc]]['hasilsbi'],0);
			$tunasdata[$master['kegiatan'][$kunc]]['hk']+=$master['hkkhl'][$kunc];
			$tunasdata[$master['kegiatan'][$kunc]]['hksbi']+=$master['hkkhlsbi'][$kunc];
			$tunasdata[$master['kegiatan'][$kunc]]['hk']+=$master['hkkbl'][$kunc];
			$tunasdata[$master['kegiatan'][$kunc]]['hksbi']+=$master['hkkblsbi'][$kunc];
			$tunasdata[$master['kegiatan'][$kunc]]['hasilbi']+=$master['hasilbi'][$kunc];
			$tunasdata[$master['kegiatan'][$kunc]]['hasilsbi']+=$master['hasilsbi'][$kunc];
            if($lha)$stream.="<td align=right>".@number_format($master['hkkhl'][$kunc],2)."</td>         
            <td align=right>".@number_format($master['hkkhlsbi'][$kunc],2)."</td>"; else $stream.="<td align=right colspan=2>".@number_format($master['hkkhl'][$kunc],2)."</td>";
            if($lha)$stream.="<td align=right>".$master['hkkbl'][$kunc]."</td>
            <td align=right>".$master['hkkblsbi'][$kunc]."</td>"; else $stream.="<td align=right colspan=2>".@number_format($master['hkkbl'][$kunc],2)."</td>";
            
			$stream.="<td align=right>".@number_format($master['upahperhk'][$kunc],0)."</td>    
            <td align=right>".@number_format($master['totalupah'][$kunc],0)."</td>";
			$stream.="<td align=right>".@number_format($master['premiperhk'][$kunc],0)."</td>    
            <td align=right>".@number_format($master['totalpremi'][$kunc],0)."</td>";
			$stream.="<td align=right>".@number_format($master['rpperhk'][$kunc],0)."</td>    
            <td align=right>".@number_format($master['totalsumupah'][$kunc],0)."</td>"; 
			
			$warnasel='';
            if($master['satuankegiatan'][$kunc]=='HA'){ // satuan HA
                if(round($master['hasilbi'][$kunc],2)>round($master['luas'][$kunc],2)){ // prestasi > luas
                    $warnasel=' bgcolor="red"';
                }                
            }
            if($lha)$stream.="<td align=right".$warnasel.">".@number_format($master['hasilbi'][$kunc],2)."</td>    
            <td align=right>".@number_format($master['hasilsbi'][$kunc],2)."</td>"; else $stream.="<td align=right colspan=2".$warnasel.">".@number_format($master['hasilbi'][$kunc],2)."</td>";               
            $stream.="<td></td>
            <td></td>"; 
            if($lha)$stream.="<td></td>
            <td></td>"; else $stream.="<td colspan=2></td>";
            $stream.="<td></td>
            <td></td>
            <td align=right>".@number_format($master['totbiaya'][$kunc],0)."</td>   
            <td align=right>".@number_format($master['rppersatuan'][$kunc],0)."</td>                 
            <td align=right>".@number_format($master['hkpersatuan'][$kunc],2)."</td>                 
            </tr>";
                  if(!empty($master['barangsbi'][$kunc]))
					  foreach($master['barangsbi'][$kunc] as $dd=>$ee){
						  if($master['barangsbi'][$kunc][$dd]!=''){
							$stream.="<tr class=rowcontent>
							<td></td>
							<td></td>    
							<td></td>
							<td></td>
							<td></td>
							<td></td>    
							<td></td>
							<td></td>
							<td align=right></td>
							<td></td>";
							if($lha)$stream.="<td align=right></td>    
							<td align=right></td>"; else $stream.="<td align=right colspan=2></td>";                
							if($lha)$stream.="<td align=right></td>    
							<td align=right></td>"; else $stream.="<td align=right colspan=2></td>";                
							$stream.="<td align=right></td>    
							<td align=right></td>"; 
							if($lha)$stream.="<td align=right></td>    
							<td align=right></td>"; else $stream.="<td align=right colspan=2></td>";                
							$stream.="<td>".$master['barangsbi'][$kunc][$dd]."</td>
							<td>".$master['satuanbarangsbi'][$kunc][$dd]."</td>"; 
							if($lha)$stream.="<td align=right>".@number_format($master['qtybi'][$kunc][$dd],2)."</td>
							<td align=right>".@number_format($master['qtysbi'][$kunc][$dd],2)."</td>"; else $stream.="<td align=right colspan=2>".@number_format($master['qtybi'][$kunc][$dd],2)."</td>"; 
							$stream.="<td align=right>".@number_format($master['hargabarangbi'][$kunc][$dd],0)."</td>
							<td align=right>".@number_format($master['bybarangbi'][$kunc][$dd],0)."</td>
							<td align=right></td>   
							<td align=right></td>                 
							<td align=right></td>                 
							</tr>";   
						}							
                  }
       // }
   }     
                   
       $stream.="
	<tr class=rowcontent>
	<td colspan=4>Total</td>
	<td align=right></td>
        <td></td>";    
	if($lha)$stream.="<td align=right>".@number_format($TOTAL['hkkhlbi'],2)."</td>
	<td align=right>".@number_format($TOTAL['hkkhlsbi'],2)."</td>"; else $stream.="<td align=right colspan=2>".@number_format($TOTAL['hkkhlbi'],2)."</td>";
	if($lha)$stream.="<td align=right>".@number_format($TOTAL['hkkblbi'],2)."</td>
	<td align=right>".@number_format($TOTAL['hkkblsbi'],2)."</td>"; else $stream.="<td align=right colspan=2>".@number_format($TOTAL['hkkblbi'],2)."</td>";
	$stream.="<td align=right></td>
	<td align=right>".@number_format($TOTAL['totalupah'])."</td>";
	$stream.="<td align=right></td>
	<td align=right>".@number_format($TOTAL['totalpremi'])."</td>";
	$stream.="<td align=right></td>
	<td align=right>".@number_format($TOTAL['totalsumupah'])."</td>";
 	if($lha)$stream.="<td align=right></td>
	<td align=right></td>"; else $stream.="<td align=right colspan=2></td>";          
        $stream.="<td></td> 
        <td></td>
        <td></td>  
        <td></td>  
        <td></td>
        <td align=right>".@number_format($TOTAL['totalbiayabarang'])."</td> 
        <td align=right>".@number_format($TOTAL['totalbiaya'])."</td>
        <td align=right></td>    
        <td align=right></td>    
	</tr>
        </tbody></table>";
$stream.="
<table>
<tr>
	<td>
	Warna <span style='color:orange;'>(orange)</span> Tidak Ada dalam RKH </td>
	</tr></table>";
//============================================================================== atas: lba perawatan        
//============================================================================== bawah: lba panen (dz apr 28, 2012)        
//        $stream.='<br>';
	if($proses=='excel')
                $stream.="<br><table border='1'>";
        else {
              $stream.="<br><table cellspacing='1' border='0' class='sortable' width=100%>";
            }
	$stream.="<thead>
	<tr class=rowheader>
        <td rowspan=2 align=center  >".$_SESSION['lang']['kode']."</td>
        <td rowspan=2 align=center>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>    
	<td rowspan=2 align=center>".$_SESSION['lang']['satuan']."</td>
	<td colspan=2 align=center>".$_SESSION['lang']['kodeblok']."</td>            
	<td rowspan=2 align=center>".$_SESSION['lang']['thntnm']."</td>
	<td colspan=2 align=center>HK</td>    
	<td colspan=4 align=center>".$_SESSION['lang']['biaya']."</td>
	<td colspan=2 align=center>".$_SESSION['lang']['hasilkerjajumlah']."</td>
        <td rowspan=2 align=center>Rp/Kg</td>    
        <td rowspan=2 align=center>Kg/HK</td>    
        </tr>        
 	<tr class=rowheader>
        <td align=center>".$_SESSION['lang']['blok']."</td>
        <td align=center>".$_SESSION['lang']['luas']."</td>";    
        if($lha)$stream.="<td align=center>".$_SESSION['lang']['hi']."</td>    
	<td align=center>".$_SESSION['lang']['sdhi']."</td>"; else $stream.="<td align=center colspan=2></td>";
        $stream.="<td align=center>".$_SESSION['lang']['upah']."</td>            
            <td align=center>Premi</td>            
            <td align=center>Penalty</td>            
	<td align=center>".$_SESSION['lang']['jumlah']."</td>";
	if($lha)$stream.="<td align=center>".$_SESSION['lang']['hi']."</td>
        <td align=center>".$_SESSION['lang']['sdhi']."</td>"; else $stream.="<td align=center colspan=2></td>";
	$stream.="</tr>       
        </thead>
	<tbody>";

// kegiatan panen
$str="SELECT kodekegiatan,namakegiatan FROM ".$dbname.".setup_kegiatan 
    where kelompok='PNN' order by kodekegiatan asc limit 1"; // buat jaga2 kalo ada PNN lebih dari 1, ambil yang paling atas aja (panen)
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())

{
    $kodepanen=$bar->kodekegiatan;
    $namapanen=$bar->namakegiatan;
} 

// kamus luas
$str="SELECT kodeorg, luasareaproduktif, tahuntanam FROM ".$dbname.".setup_blok 
    where kodeorg like '".$kdAfd."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())

{
    $area[$bar->kodeorg]=$bar->luasareaproduktif;
} 

if($lha)$str="SELECT count(*) as hk,kodeorg FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
else $str="SELECT count(*) as hk,kodeorg FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal between '".$tgl1_."' and '".$tgl2_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	setIt($hksd[$bar->kodeorg],0);
    $hksd[$bar->kodeorg]+=$bar->hk;
}

if($lha)$str="SELECT sum(hasilkerjakg)as hasil,kodeorg FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal between '".substr($tgl1_,0,6)."01' and '".$tgl1_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
else $str="SELECT sum(hasilkerjakg)as hasil,kodeorg FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal between '".$tgl1_."' and '".$tgl2_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	setIt($kgsd[$bar->kodeorg],0);
    $kgsd[$bar->kodeorg]+=$bar->hasil;
}

$areatotal=0;
$hktotal=0;
$hksdtotal=0;
$upahtotal=0;
$premitotal=0;
$penaltytotal=0;
$jumlahupahtotal=0;
$kgtotal=0;
$kgsdtotal=0;
/*
if($lha)$str="SELECT count(*) as hk,kodeorg,tahuntanam,sum(hasilkerjakg)as hasil,sum(upahkerja)as upah,sum(upahpremi)as premi,sum(rupiahpenalty)penalty FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal = '".$tgl1_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
else $str="SELECT count(*) as hk,kodeorg,tahuntanam,sum(hasilkerjakg)as hasil,sum(upahkerja)as upah,sum(upahpremi)as premi,sum(rupiahpenalty)penalty FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal between '".$tgl1_."' and '".$tgl2_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
*/


$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tglawal1=substr($tgl1,0,7).'-01';
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));
$whtgl='';
if($tgl2=='--'){
	$whtgl.=" '".$tglawal1."' and '".$tgl1."' ";
}else{
	$whtgl.=" '".$tgl1."' and '".$tgl2."' ";
}


if($lha)$str="SELECT sum(hkpanenperhari) as hk,kodeorg,tahuntanam,sum(hasilkerjakg)as hasil,sum(tupah)as upah,sum(tpremi )as premi,
	sum(rupiahpenalty)penalty FROM ".$dbname.".kebun_prestasi_vs_hk 
    where  tanggal between ".$whtgl." and kodeorg like '".$kdAfd."%' group by kodeorg";
else $str="SELECT sum(hkpanenperhari) as hk,kodeorg,tahuntanam,sum(hasilkerjakg)as hasil,sum(tupah)as upah,sum(tpremi)as premi,
	sum(rupiahpenalty)penalty FROM ".$dbname.".kebun_prestasi_vs_hk 
    where tanggal between ".$whtgl." and kodeorg like '".$kdAfd."%' group by kodeorg";
	//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $jumlahupah=$bar->upah+$bar->premi-$bar->penalty;
    @$rppersat=$jumlahupah/$bar->hasil;
    @$kgperhk=$bar->hasil/$bar->hk;
    $areatotal+=$area[$bar->kodeorg];
    $hktotal+=$bar->hk;
    $hksdtotal+=$hksd[$bar->kodeorg];
    $upahtotal+=$bar->upah;
    $premitotal+=$bar->premi;
    $penaltytotal+=$bar->penalty;
    $jumlahupahtotal+=$jumlahupah;
    $kgtotal+=$bar->hasil;
    //$kgsdtotal+=$kgsd[$bar->kodeorg];
	$stream.="<tr class=rowcontent>
            <td align=left>".$kodepanen."</td>
            <td align=left>".$kegiatanx[$kodepanen]."</td>
            <td align=left>KG</td>
            <td align=left>".$bar->kodeorg."</td>
            <td align=right>".$area[$bar->kodeorg]."</td>
            <td align=center>".$bar->tahuntanam."</td>";
            if($lha)$stream.="<td align=right>".@number_format($bar->hk,2)."</td>
            <td align=right>".$hksd[$bar->kodeorg]."</td>";
            else $stream.="<td align=right colspan=2>".@number_format($bar->hk,2)."</td>";
            $stream.="<td align=right>".@number_format($bar->upah,0)."</td>
            <td align=right>".@number_format($bar->premi,0)."</td>
            <td align=right>".@number_format($bar->penalty,0)."</td>
            <td align=right>".@number_format($jumlahupah,0)."</td>";
            if($lha)$stream.="<td align=right>".@number_format($bar->hasil,0)."</td>
            <td align=right>".@number_format($bar->hasil,0)."</td>";
            else $stream.="<td align=right colspan=2>".@number_format($bar->hasil,0)."</td>";
            $stream.="<td align=right>".@number_format($rppersat,0)."</td>
            <td align=right>".@number_format($kgperhk,2)."</td>
        </tr>    
        ";
}



	$stream.="<tr class=rowcontent>
            <td align=left colspan=4>Total</td>
            <td align=right>".$areatotal."</td>
            <td align=center></td>";
            if($lha)$stream.="<td align=right>".@number_format($hktotal,2)."</td>
            <td align=right>".@number_format($hksdtotal,2)."</td>";
            else $stream.="<td align=right colspan=2>".@number_format($hktotal,2)."</td>";
            $stream.="<td align=right>".@number_format($upahtotal,0)."</td>
            <td align=right>".@number_format($premitotal,0)."</td>";
            $stream.="<td align=right>".@number_format($penaltytotal,0)."</td>
            <td align=right>".@number_format($jumlahupahtotal,0)."</td>";
            if($lha)$stream.="<td align=right>".@number_format($kgtotal,0)."</td>
            <td align=right>".@number_format($kgtotal,0)."</td>";
            else $stream.="<td align=right colspan=2>".@number_format($kgtotal,0)."</td>";
            $stream.="<td align=right></td>
            <td align=right></td>
        </tr>    
        ";

$stream.="</tbody></table>";        
        
        
$stream.="<br>".  strtoupper('Supervisi');
$stream.="<table cellspacing='1' border='0' class='sortable'>";        
$stream.="<thead>
	<tr class=rowheader>
        <td align=center>".$_SESSION['lang']['nomor']."</td>    
        <td align=center>".$_SESSION['lang']['jenis']."</td>    
        <td align=center>".$_SESSION['lang']['jumlahhk']."</td>    
        <td align=center>".$_SESSION['lang']['upahkerja']."</td>	
		<td align=center>".$_SESSION['lang']['premi']."</td>
		<td align=center>".$_SESSION['lang']['lembur']."</td>	
		<td align=center>".$_SESSION['lang']['material']."</td>	
		<td align=center>".$_SESSION['lang']['total']."</td>			
        </tr></thead><tbody>";







#bentuk absen  mandor
$str="select a.nikmandor,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikmandor=b.karyawanid where 
		subbagian like '%".$kdAfd."%' and tanggal between ".$whtgl." and nikmandor!=''  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor']]=$bar['nikmandor'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['nikmandor']][$bar['tanggal']]=$bar['nikmandor'];
	$counttgl[$bar['nikmandor']][$bar['tanggal']]=1;
	
	$dtmandorm[$bar['nikmandor']]=$bar['nikmandor'];
	$dttglm[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkmm[$bar['nikmandor']][$bar['tanggal']]=$bar['nikmandor'];
	$counttglm[$bar['nikmandor']][$bar['tanggal']]=1;
	
}
#mandor 1
$str="select a.nikmandor1,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikmandor1=b.karyawanid where 
		subbagian like '%".$kdAfd."%' and tanggal between ".$whtgl." and nikmandor1!=''  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['nikmandor1']]=$bar['nikmandor1'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['nikmandor1']][$bar['tanggal']]=$bar['nikmandor1'];
	$counttgl[$bar['nikmandor1']][$bar['tanggal']]=1;
	
	$dtmandorm1[$bar['nikmandor1']]=$bar['nikmandor1'];
	$dttglm1[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkmm1[$bar['nikmandor1']][$bar['tanggal']]=$bar['nikmandor1'];
	$counttglm1[$bar['nikmandor1']][$bar['tanggal']]=1;
}

#krani
$str="select a.keranimuat,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.keranimuat=b.karyawanid where 
		subbagian like '%".$kdAfd."%' and tanggal between ".$whtgl." and keranimuat!=''  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	//bentuk tanggal dulu
	$dtmandor[$bar['keranimuat']]=$bar['keranimuat'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['keranimuat']][$bar['tanggal']]=$bar['keranimuat'];
	$counttgl[$bar['keranimuat']][$bar['tanggal']]=1;
	
	$dtmandorkr[$bar['keranimuat']]=$bar['keranimuat'];
	$dttglkr[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkmkr[$bar['keranimuat']][$bar['tanggal']]=$bar['keranimuat'];
	$counttglkr[$bar['keranimuat']][$bar['tanggal']]=1;
	
}

//krani panen
$str="select a.nikasisten,a.tanggal,a.kodeorg,b.subbagian from ".$dbname.".kebun_aktifitas a 
		left join ".$dbname.".datakaryawan b on a.nikasisten=b.karyawanid where 
		subbagian like '%".$kdAfd."%'  and tanggal between ".$whtgl." and nikasisten!=''  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtmandor[$bar['nikasisten']]=$bar['nikasisten'];
	$dttgl[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkm[$bar['nikasisten']][$bar['tanggal']]=$bar['nikasisten'];
	$counttgl[$bar['nikasisten']][$bar['tanggal']]=1;
	
	$dtmandorkrpnn[$bar['nikasisten']]=$bar['nikasisten'];
	$dttglkrpnn[$bar['tanggal']]=$bar['tanggal'];
	$dtpejabatbkmkrpnn[$bar['nikasisten']][$bar['tanggal']]=$bar['nikasisten'];
	$counttglkrpnn[$bar['nikasisten']][$bar['tanggal']]=1;
}





#umr pejabat kantor
$str="select a.*,b.subbagian,b.lokasitugas,b.tipekaryawan from ".$dbname.".sdm_5gajipokok a left join ".$dbname.".datakaryawan b
		on a.karyawanid=b.karyawanid where lokasitugas='".$kdOrg."' and tahun = '".substr($tgl1,0,4)."' and idkomponen=1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$umrhari[$bar['karyawanid']]=$bar['jumlah']/25;
}

if(!empty($dtmandor)){
	foreach ($dtmandor as $karid){
		foreach($dttgl as $tgl){
			$day = date('D', strtotime($tgl));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".substr($kdAfd,0,4)."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
			if($libur==false){		
				if(@$dtpejabatbkm[$karid][$tgl]!=''){
					@$rupiah[$karid][$tgl]=$counttgl[$karid][$tgl]*$umrhari[$karid];
					@$hk[$karid][$tgl]=$counttgl[$karid][$tgl];
				}
			}
		}
	}
}


#bentuk mandor
// echo"<pre>";
// print_r($hk);
// echo"</pre>";

/////////////////////////////////////////////////////////////////////////////////

#mandor
if(!empty($dtmandorm)){
	foreach ($dtmandorm as $karid){
		foreach($dttglm as $tgl){
			$day = date('D', strtotime($tgl));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".substr($kdAfd,0,4)."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
			if($libur==false){
				if(@$counttglm[$karid][$tgl]<=@$hk[$karid][$tgl]){
					@$rpmandor+=$counttglm[$karid][$tgl]*$umrhari[$karid];//upahnya
					@$hkmandor+=$counttglm[$karid][$tgl];  //hknya
					@$thk[$karid][$tgl]+=$counttglm[$karid][$tgl];
					
				}
			}
		}
	}
}

#mandor1
if(!empty($dtmandorm1)){
	foreach ($dtmandorm1 as $karid){
		foreach($dttglm1 as $tgl){
			$day = date('D', strtotime($tgl));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".substr($kdAfd,0,4)."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
			
			if($libur==false){
				@$hitungsisahk[$karid][$tgl]=@$counttglm1[$karid][$tgl]-@$thk[$karid][$tgl];
				if($hitungsisahk[$karid][$tgl]>0){
					@$rpmandor1+=$counttglm1[$karid][$tgl]*$umrhari[$karid];//upahnya
					@$hkmandor1+=$counttglm1[$karid][$tgl];  //hknya
					@$thk[$karid][$tgl]+=$counttglm1[$karid][$tgl];
				}
			}
		}
	}
}

#krani
if(!empty($dtmandorkr)){
	foreach ($dtmandorkr as $karid){
		foreach($dttglkr as $tgl){
			$day = date('D', strtotime($tgl));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".substr($kdAfd,0,4)."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
			
			if($libur==false){
				@$hitungsisahk[$karid][$tgl]=@$counttglkr[$karid][$tgl]-@$thk[$karid][$tgl];
				if($hitungsisahk[$karid][$tgl]>0){
					@$rpkr+=$counttglkr[$karid][$tgl]*$umrhari[$karid];//upahnya
					@$hkkr+=$counttglkr[$karid][$tgl];  //hknya
					@$thk[$karid][$tgl]+=$counttglkr[$karid][$tgl];
				}
			}
		}
	}
}


#krani pnn/krani buah
if(!empty($dtmandorkrpnn)){
	foreach ($dtmandorkrpnn as $karid){
		foreach($dttglkrpnn as $tgl){
			$day = date('D', strtotime($tgl));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."' and (kebun='GLOBAL' or kebun='".substr($kdAfd,0,4)."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
			
			if($libur==false){
				$hitungsisahk[$karid][$tgl]=@$counttglkrpnn[$karid][$tgl]-@$thk[$karid][$tgl];
				if($hitungsisahk[$karid][$tgl]>0){
					@$rpkrpnn+=$counttglkrpnn[$karid][$tgl]*$umrhari[$karid];//upahnya
					@$hkkrpnn+=$counttglkrpnn[$karid][$tgl];  //hknya
					@$thk[$karid][$tgl]+=$counttglkrpnn[$karid][$tgl];
				}
			}
		}
	}
}


#kranibuah



// echo"<pre>";
// print_r($thk);
// echo"</pre>";



#premi mandor
//kebun_premikemandoran
$str="select a.*,sum(a.premiinput) as premiinput2,b.subbagian from ".$dbname.".kebun_premikemandoran a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where subbagian like '%".$kdAfd."%' 
and periode between ".$whtgl." group by jabatan ";
		//exit("error :".$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$premispv[$bar['jabatan']]+=$bar['premiinput2'];
	
}



/**********************************************/
/******************** KANTOR ******************/
/**********************************************/

$str="select * from ".$dbname.".sdm_absensidt_vw where kodeorg like '%".$kdAfd."%' 
		and tanggal between ".$whtgl."  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$rupiahumum+=$bar['umr'];
	@$hkumum+=$bar['nilaihk'];
	@$premiumum+=$bar['premi']+@$bar['insentif'];
}


$str="select * from ".$dbname.".sdm_lemburdt where kodeorg like '%".$kdAfd."%' and tanggal between ".$whtgl."   ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$lemburumum+=$bar['uangkelebihanjam'];
}





#gt jabatan
@$tmandor=$rpmandor+$premispv['MANDORPANEN'];
$stream.="<tr class=rowcontent>
        <td align=center>1</td>    
        <td align=left>Mandor</td>    
        <td align=right>".@number_format($hkmandor,2)."</td>    
        <td align=right>".@number_format($rpmandor,2)."</td> 
		<td  align=right>".@number_format($premispv['MANDORPANEN'],2)."</td>	
		<td align=right>".@number_format(0,0)."</td>
				<td align=right>".@number_format(0)."</td> 
		<td align=right>".@number_format($tmandor,0)."</td>		
        </tr>";

@$tmandor1=@$rpmandor1+@$premispv['MANDOR1'];		
$stream.="<tr class=rowcontent>
        <td align=center>2</td>    
        <td align=left>Mandor I</td>    
        <td align=right>".@number_format($hkmandor1,2)."</td>    
        <td align=right>".@number_format($rpmandor1,2)."</td> 
		<td  align=right>".@number_format($premispv['MANDOR1'],2)."</td>	
		<td align=right>".@number_format(0,0)."</td>	
				<td align=right>".@number_format(0)."</td> 
		<td align=right>".@number_format($tmandor1,0)."</td>			
        </tr>";		
	
@$tkr=$rpkr;		
$stream.="<tr class=rowcontent>
        <td align=center>3</td>    
        <td align=left>Krani</td>    
        <td align=right>".@number_format($hkkr,2)."</td>    
		<td align=right>".@number_format($rpkr,2)."</td> 
        <td align=right>".@number_format(0,0)."</td>
		<td align=right>".@number_format(0,0)."</td>
				<td align=right>".@number_format(0)."</td> 
		<td align=right>".@number_format($tkr,0)."</td>
        </tr>";
		
@$tkrpnn=$rpkrpnn+$premispv['KERANI'];			
$stream.="<tr class=rowcontent>
        <td align=center>4</td>    
        <td align=left>Krani Buah</td>    
        <td align=right>".@number_format($hkkrpnn,2)."</td>    
		<td align=right>".@number_format($rpkrpnn,2)."</td> 
        <td align=right>".@number_format($premispv['KERANI'],2)."</td> 
		<td align=right>".@number_format(0,0)."</td>
				<td align=right>".@number_format(0)."</td> 
		<td align=right>".@number_format($tkrpnn,2)."</td>
        </tr>";		
	
@$tumum=$rupiahumum+$premiumum+$lemburumum;
$stream.="<tr class=rowcontent>
        <td align=center>5</td>    
        <td align=left>Umum (Kantor)</td>    
        <td align=right>".@number_format($hkumum,2)."</td>    
        <td align=right>".@number_format($rupiahumum,2)."</td>    
		<td align=right>".@number_format($premiumum,2)."</td>    
		<td align=right>".@number_format($lemburumum,2)."</td>   
				<td align=right>".@number_format(0)."</td> 	
		<td align=right>".@number_format($tumum,2)."</td>
        </tr>";

#total spv		
@$trp=@$rpmandor+@$rpmandor1+@$rpkr+@$rpkrpnn+@$rupiahumum;
@$tpremi=@$premispv['MANDORPANEN']+@$premispv['MANDOR1']+@$premispv['KERANI']+@$premiumum;
@$tlembur=$lemburumum;
@$gtspv=$trp+$tpremi+$tlembur;

#gt (spv+bkm+pnn)
# untuk premi panen, premipanen - penalty
@$premitotal=$premitotal-$penaltytotal;

@$gt=$trp+$TOTAL['totalupah']+$upahtotal;
@$gtpremi=$tpremi+$premitotal+$TOTAL['totalpremi'];
@$gtlembur=$tlembur;
@$gtall=$gt+$gtpremi+$gtlembur+$TOTAL['totalbiayabarang'];



@$costrp=@$gt/$luas;
@$costpremi=@$gtpremi/$luas;
@$costlembur=@$gtlembur/$luas;
@$costmaterial=@$TOTAL['totalbiayabarang']/$luas;
@$costall=@$gtall/$luas;
		
$stream.="<tr class=rowcontent>
        <td align=center colspan=3>Total</td>    
        <td align=right>".@number_format($trp,2)."</td>  
		<td align=right>".@number_format($tpremi,2)."</td> 
		<td align=right>".@number_format($tlembur,2)."</td> 
		<td align=right>".@number_format(0,2)."</td> 
		<td align=right>".@number_format($gtspv,2)."</td>  		
        </tr>";
$stream.="<tr class=rowcontent>
        <td align=center colspan=3>Grand Total Biaya (Rp.)</td>    
        <td align=right>".@number_format($gt,2)."</td>  
		<td align=right>".@number_format($gtpremi,2)."</td> 
		<td align=right>".@number_format($gtlembur,2)."</td> 
		<td align=right>".@number_format($TOTAL['totalbiayabarang'],2)."</td> 
		<td align=right>".@number_format($gtall,2)."</td>  	
        </tr>";
$stream.="<tr class=rowcontent>
        <td align=center colspan=3>Total Cost (Rp./Ha)</td>    
       <td align=right>".@number_format($costrp,2)."</td>  
		<td align=right>".@number_format($costpremi,2)."</td> 
		<td align=right>".@number_format($costlembur,2)."</td> 
		<td align=right>".@number_format($costmaterial,2)."</td> 
		<td align=right>".@number_format($costall,2)."</td>  
        </tr>";
$stream.="</tbody></table>";

       if($proses=='preview'){
            echo $stream;    
       }
        
       if($proses=='excel'){
            $stream.="</table><br>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
            $dte=date("YmdHms");
            $nop_="LHA_".$kdAfd."_".$tgl1_."_".$tgl2_."_".$dte;
             $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
             gzwrite($gztralala, $stream);
             gzclose($gztralala);
             echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls.gz';
                </script>";            
        }
       if($proses=='pdf')
       {
           //belum selesai
        exit('Sorry, length maximum exceed');   
            $lkojur=4;
            $lpeker=12;
            $llain=4;
           
            class PDF extends FPDF
            {
                function Header() {
                    global $kdAfd;
                    global $tgl1_,$tgl2_;
                    global $dbname;
                    global $lkojur, $lpeker, $llain,$lha,$luas;

                    $width = $this->w - $this->lMargin - $this->rMargin;
                    $height = 12;
                    $path='images/logo.jpg';
                    $this->Image($path,$this->lMargin,$this->tMargin,20);	
                    $this->SetFont('Arial','B',9);
                    $this->SetFillColor(255,255,255);	
                    $this->SetX(50);   
                    $this->Cell($width-50,$height,'OWL',0,1,'L');	 
                    $this->Line($this->lMargin+25,$this->tMargin+($height*1),
                        $this->lMargin+$width,$this->tMargin+($height*1));
                    $this->Ln();
                    $this->SetFont('Arial','U',10);
                    if($_SESSION['language']=='EN'){
                        $title='DAILY DIVISION REPORT';
                    }else{
                            $title='LAPORAN HARIAN DIVISI';
                    }
                    $this->Cell($width,$height,$title,0,1,'C');	
                    $this->Ln();	
                    $this->SetFont('Arial','',8);
                    $this->Cell((7/100*$width)-5,$height,$_SESSION['lang']['kebun'],'',0,'L');
                    $this->Cell(5,$height,':','',0,'L');
                    $this->Cell(43/100*$width,$height,substr($kdAfd,0,4),'',0,'L');		
                    $this->Cell(20/100*$width,$height,'','',0,'L');		
                    $this->Cell(15/100*$width,$height,$_SESSION['lang']['diperiksa'],'',0,'C');		
                    $this->Cell(15/100*$width,$height,$_SESSION['lang']['dibuat'],'',0,'C');		
                    $this->Ln();	
                    $this->Cell((7/100*$width)-5,$height,$_SESSION['lang']['afdeling'],'',0,'L');
                    $this->Cell(5,$height,':','',0,'L');
                    $this->Cell(43/100*$width,$height,$kdAfd.' ('.@number_format($luas,2).' Ha)','',0,'L');		
                    $this->Ln();
                    $this->Cell((7/100*$width)-5,$height,$_SESSION['lang']['tanggal'],'',0,'L');
                    $this->Cell(5,$height,':','',0,'L');
                    $this->Cell(43/100*$width,$height,tanggalnormal($tgl1_).' '.tanggalnormal($tgl2_),'',0,'L');		
                    $this->Ln();	
                    $this->Cell(70/100*$width,$height,'','',0,'L');		
                    $this->Cell(15/100*$width,$height,$_SESSION['lang']['askep'],'',0,'C');		
                    $this->Cell(15/100*$width,$height,$_SESSION['lang']['asisten'],'',0,'C');		
                    $this->Ln();	
                }

                function Footer()
                {
                    $this->SetY(-15);
                    $this->SetFont('Arial','I',8);
                    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C',1);
                    $this->SetX(-520);
                    $this->Cell(500,10,'Printed By'." : ".$_SESSION['empl']['name'].", ".date('d-m-Y H:i:s'),'',1,'R',1);		
                }
            }           
           
            $pdf=new PDF('L','pt','A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 12;
            $pdf->AddPage();
            
                    $pdf->SetFont('Arial','',7);
                    $pdf->SetFillColor(220,220,220);
                    $pdf->Cell($lkojur/100*$width,$height,$_SESSION['lang']['kode'],'TRL',0,'C',1);
                    $pdf->Cell($lpeker/100*$width,$height,$_SESSION['lang']['pekerjaan'],'TRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['satuan'],'TRL',0,'C',1);
                    $pdf->Cell(($lkojur+$llain)/100*$width,$height,$_SESSION['lang']['kodeblok'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['tahun'],'TRL',0,'C',1);
                    $pdf->Cell(2*$llain/100*$width,$height,'HK KHT/KHL/PHL',1,0,'C',1);
                    $pdf->Cell(2*$llain/100*$width,$height,'HK KBL',1,0,'C',1);
                    $pdf->Cell(2*$llain/100*$width,$height,$_SESSION['lang']['upah'].'(Rp.)',1,0,'C',1);
                    $pdf->Cell(2*$llain/100*$width,$height,$_SESSION['lang']['hasilkerjajumlah'],1,0,'C',1);
                    $pdf->Cell(((3*$llain)+$lkojur)/100*$width,$height,$_SESSION['lang']['pemakaianBarang'],1,0,'C',1);
                    $pdf->Cell(2*$llain/100*$width,$height,$_SESSION['lang']['material'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'T.'.$_SESSION['lang']['biaya'],'TRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'Rp./'.$_SESSION['lang']['satuan'],'TRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'HK/'.$_SESSION['lang']['satuan'],'TRL',0,'C',1);
                    $pdf->Ln();	
                    $pdf->Cell($lkojur/100*$width,$height,$_SESSION['lang']['jurnal'],'BRL',0,'C',1);
                    $pdf->Cell($lpeker/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Cell($lkojur/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['luas'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['hi'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['sdhi'],1,0,'C',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,'','RLB',0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['hi'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['sdhi'],1,0,'C',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,'','RLB',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'Rp./Unit',1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['hi'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['sdhi'],1,0,'C',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,'','RLB',0,'C',1);
                    $pdf->Cell($lkojur/100*$width,$height,$_SESSION['lang']['namabarang'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['satuan'],1,0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['hi'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['sdhi'],1,0,'C',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,'',1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'Rp./Unit',1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Ln();	
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',5);
            
                
 //PRINT OUT============================================
            if(!empty($master['blok']))foreach($master['blok'] as $kunc=>$va){
               $qwe=1;
                $pdf->Cell($lkojur/100*$width,$height,$master['kegiatan'][$kunc],1,0,'L',1);
                $pdf->Cell($lpeker/100*$width,$height,$kegiatanx[$master['kegiatan'][$kunc]],1,0,'L',1);
                $pdf->Cell($llain/100*$width,$height,$master['satuankegiatan'][$kunc],1,0,'L',1);
                $pdf->SetFont('Arial','',4);
                $pdf->Cell($lkojur/100*$width,$height,$master['blok'][$kunc],1,0,'L',1);
                $pdf->SetFont('Arial','',5);
                $pdf->Cell($llain/100*$width,$height,@number_format($master['luas'][$kunc],2),1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,$master['thntnm'][$kunc],1,0,'C',1);
                if($lha){
                $pdf->Cell($llain/100*$width,$height,@number_format($master['hkkhl'][$kunc],2),1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($master['hkkhlsbi'][$kunc],2),1,0,'R',1);
                }else $pdf->Cell($llain*2/100*$width,$height,@number_format($master['hkkhl'][$kunc],2),1,0,'R',1);
                if($lha){
                $pdf->Cell($llain/100*$width,$height,$master['hkkbl'][$kunc],1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,$master['hkkblsbi'][$kunc],1,0,'R',1);
                }else $pdf->Cell($llain*2/100*$width,$height,$master['hkkbl'][$kunc],1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($master['rpperhk'][$kunc],0),1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($master['totalupah'][$kunc],0),1,0,'R',1);
                if($lha){
                $pdf->Cell($llain/100*$width,$height,@number_format($master['hasilbi'][$kunc],2),1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($master['hasilsbi'][$kunc],2),1,0,'R',1);
                }else $pdf->Cell($llain*2/100*$width,$height,@number_format($master['hasilbi'][$kunc],2),1,0,'R',1);
                //                $pdf->Cell(((5*$llain)+$lkojur)/100*$width,$height,'',1,0,'C',1);
                if(!empty($master['barangsbi'][$kunc])){
                foreach($master['barangsbi'][$kunc] as $dd=>$ee)
                {
                    if($qwe==0){
                        $pdf->Cell((2*$lkojur+$lpeker+(11*$llain))/100*$width,$height,'',0,0,'L',0);
                    }
                    $pdf->Cell($lkojur/100*$width,$height,$master['barangsbi'][$kunc][$dd],1,0,'L',1);
                    $pdf->Cell($llain/100*$width,$height,$master['satuanbarangsbi'][$kunc][$dd],1,0,'L',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,@number_format($master['qtybi'][$kunc][$dd],2),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($master['qtysbi'][$kunc][$dd],2),1,0,'R',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,@number_format($master['qtybi'][$kunc][$dd],2),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($master['hargabarangbi'][$kunc][$dd],0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($master['bybarangbi'][$kunc][$dd],0),1,0,'R',1);         
                    if($qwe==1){
                        $qwe=0;
                        $pdf->Cell($llain/100*$width,$height,@number_format($master['totbiaya'][$kunc],0),1,0,'R',1);
                        $pdf->Cell($llain/100*$width,$height,@number_format($master['rppersatuan'][$kunc],0),1,0,'R',1);
                        $pdf->Cell($llain/100*$width,$height,@number_format($master['hkpersatuan'][$kunc],2),1,0,'R',1);
                    }
                    $pdf->Ln();	       
                }
           	 }
            }     
            $pdf->Cell(($lkojur+$lpeker+$llain)/100*$width,$height,'Total',1,0,'C',1);
            $pdf->Cell($lkojur/100*$width,$height,'',1,0,'L',1);
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['luas'],2),1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,'',1,0,'C',1);
            if($lha){
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['hkkhlbi'],2),1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['hkkhlsbi'],2),1,0,'R',1);
            }else $pdf->Cell($llain*2/100*$width,$height,@number_format($TOTAL['hkkhlbi'],2),1,0,'R',1);
            if($lha){
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['hkkblbi']),1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['hkkblsbi']),1,0,'R',1);
            }else $pdf->Cell($llain*2/100*$width,$height,@number_format($TOTAL['hkkblbi']),1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,'',1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['totalupah']),1,0,'R',1);
            if($lha){
            $pdf->Cell($llain/100*$width,$height,'',1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,'',1,0,'R',1);
            }else $pdf->Cell($llain*2/100*$width,$height,@number_format($TOTAL['hasilbi'],2),1,0,'R',1);
            $pdf->Cell((4*$llain+($lkojur))/100*$width,$height,'',1,0,'L',1);
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['totalbiayabarang']),1,0,'R',1);         
            $pdf->Cell($llain/100*$width,$height,@number_format($TOTAL['totalbiaya']),1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,'',1,0,'R',1);
            $pdf->Cell($llain/100*$width,$height,'',1,0,'R',1);
                    $pdf->Ln();	       
                    $pdf->Ln();	  

                    $ar=$pdf->GetY();

            if($ar>400){
                $pdf->AddPage();
            }else $pdf->Ln();	
            
                    $pdf->SetFont('Arial','',7);
                    $pdf->SetFillColor(220,220,220);
                    $pdf->Cell($lkojur/100*$width,$height,'Kode','TRL',0,'C',1);
                    $pdf->Cell($lpeker/100*$width,$height,$_SESSION['lang']['pekerjaan'],'TRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['satuan'],'TRL',0,'C',1);
                    $pdf->Cell(($lkojur+$llain)/100*$width,$height,$_SESSION['lang']['kodeblok'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['thntnm'],'TRL',0,'C',1);
                    $pdf->Cell(2*$llain/100*$width,$height,'HK',1,0,'C',1);
                    $pdf->Cell(4*$llain/100*$width,$height,$_SESSION['lang']['biaya'],1,0,'C',1);
                    $pdf->Cell(2*$llain/100*$width,$height,$_SESSION['lang']['hasilkerjajumlah'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'Rp./'.$_SESSION['lang']['satuan'],'TRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'HK/'.$_SESSION['lang']['satuan'],'TRL',0,'C',1);
                    $pdf->Ln();	
                    $pdf->Cell($lkojur/100*$width,$height,$_SESSION['lang']['jurnal'],'BRL',0,'C',1);
                    $pdf->Cell($lpeker/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Cell($lkojur/100*$width,$height,$_SESSION['lang']['blok'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['luas'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['hi'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['sdhi'],1,0,'C',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,'','RLB',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['upah'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'Premi',1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'Penalty',1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['jumlah'],1,0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['hi'],1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['sdhi'],1,0,'C',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,'','RLB',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'','BRL',0,'C',1);
                    $pdf->Ln();	
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',5);                    

// copy dari atasnya administrasi dan umum
            
$areatotal=0;
$hktotal=0;
$hksdtotal=0;
$upahtotal=0;
$premitotal=0;
$penaltytotal=0;
$jumlahupahtotal=0;
$kgtotal=0;
$kgsdtotal=0;

if($lha)$str="SELECT count(*) as hk,kodeorg,tahuntanam,sum(hasilkerjakg)as hasil,sum(upahkerja)as upah,sum(upahpremi)as premi,sum(rupiahpenalty)penalty FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal = '".$tgl1_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
else $str="SELECT count(*) as hk,kodeorg,tahuntanam,sum(hasilkerjakg)as hasil,sum(upahkerja)as upah,sum(upahpremi)as premi,sum(rupiahpenalty)penalty FROM ".$dbname.".kebun_prestasi_vw 
    where tanggal between '".$tgl1_."' and '".$tgl2_."' and kodeorg like '".$kdAfd."%' group by kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())

{
    $jumlahupah=$bar->upah+$bar->premi-$bar->penalty;
    @$rppersat=$jumlahupah/$bar->hasil;
    @$kgperhk=$bar->hasil/$bar->hk;
    $areatotal+=$area[$bar->kodeorg];
    $hktotal+=$bar->hk;
    $hksdtotal+=$hksd[$bar->kodeorg];
    $upahtotal+=$bar->upah;
    $premitotal+=$bar->premi;
    $penaltytotal+=$bar->penalty;
    $jumlahupahtotal+=$jumlahupah;
    $kgtotal+=$bar->hasil;
    $kgsdtotal+=$kgsd[$bar->kodeorg];

                    $pdf->Cell($lkojur/100*$width,$height,$kodepanen,1,0,'L',1);
                    $pdf->Cell($lpeker/100*$width,$height,$kegiatanx[$kodepanen],1,0,'L',1);
                    $pdf->Cell($llain/100*$width,$height,'KG',1,0,'L',1);
                $pdf->SetFont('Arial','',4);
                    $pdf->Cell($lkojur/100*$width,$height,$bar->kodeorg,1,0,'L',1);
                $pdf->SetFont('Arial','',5);
                    $pdf->Cell($llain/100*$width,$height,$area[$bar->kodeorg],1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,$bar->tahuntanam,1,0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$bar->hk,1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,$hksd[$bar->kodeorg],1,0,'R',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,$bar->hk,1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($bar->upah,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($bar->premi,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($bar->penalty,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($jumlahupah,0),1,0,'R',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,@number_format($bar->hasil,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($kgsd[$bar->kodeorg],0),1,0,'R',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,@number_format($bar->hasil,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($rppersat,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($kgperhk,2),1,0,'R',1);
                    $pdf->Ln();	    
    }        
                    $pdf->Cell(($lkojur+$lpeker+$llain)/100*$width,$height,'Total',1,0,'C',1);
                    $pdf->Cell($llain/100*$width,$height,'',1,0,'L',1);
                    $pdf->Cell($llain/100*$width,$height,$areatotal,1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,'',1,0,'C',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,$hktotal,1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,$hksdtotal,1,0,'R',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,$hktotal,1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($upahtotal,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($premitotal,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($penaltytotal,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($jumlahupahtotal,0),1,0,'R',1);
                    if($lha){
                    $pdf->Cell($llain/100*$width,$height,@number_format($kgtotal,0),1,0,'R',1);
                    $pdf->Cell($llain/100*$width,$height,@number_format($kgsdtotal,0),1,0,'R',1);
                    }else $pdf->Cell($llain*2/100*$width,$height,@number_format($kgtotal,0),1,0,'R',1);
                    $pdf->Cell(($llain+$llain)/100*$width,$height,'',1,0,'R',1);
                    $pdf->Ln();	    
            
                    $ar=$pdf->GetY();

            if($ar>400){
                $pdf->AddPage();
            }else $pdf->Ln();	
                    
                $pdf->Cell(($lkojur+$lpeker+$llain+$llain)/100*$width,$height,  strtoupper($_SESSION['lang']['biayaumum']),0,0,'L',1);
                    $pdf->SetFillColor(220,220,220);
                    $pdf->Ln();	       
                $pdf->Cell($lkojur/100*$width,$height,$_SESSION['lang']['nomor'],1,0,'L',1);
                $pdf->Cell($lpeker/100*$width,$height,$_SESSION['lang']['jenis'],1,0,'L',1);
                $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['jumlahhk'],1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,$_SESSION['lang']['upahkerja'],1,0,'R',1);
                    $pdf->Ln();	       
                    $pdf->SetFillColor(255,255,255);	
                $pdf->Cell($lkojur/100*$width,$height,'1',1,0,'R',1);
                $pdf->Cell($lpeker/100*$width,$height,'Pengawasan Mandor)',1,0,'L',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($awashk,2),1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($awasupah,0),1,0,'R',1);
                    $pdf->Ln();	       
                $pdf->Cell($lkojur/100*$width,$height,'2',1,0,'R',1);
                $pdf->Cell($lpeker/100*$width,$height,'Administrasi (Kerani)',1,0,'L',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($admhk,2),1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($admupah,0),1,0,'R',1);
                    $pdf->Ln();	       
                $pdf->Cell($lkojur/100*$width,$height,'3',1,0,'R',1);
                $pdf->Cell($lpeker/100*$width,$height,'Umum (Kantor)',1,0,'L',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($umumhk,2),1,0,'R',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($umumupah,0),1,0,'R',1);
                    $pdf->Ln();	       
                $pdf->Cell(($lkojur+$lpeker+$llain)/100*$width,$height,'Total',1,0,'C',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($total,0),1,0,'R',1);
                    $pdf->Ln();	       
                $pdf->Cell(($lkojur+$lpeker+$llain)/100*$width,$height,'Grand Total (Rp.)',1,0,'C',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($grandtotal,0),1,0,'R',1);
                    $pdf->Ln();	       
                    $pdf->SetFillColor(220,220,220);
                $pdf->Cell(($lkojur+$lpeker+$llain)/100*$width,$height,'Total Cost (Rp./Ha)',1,0,'C',1);
                $pdf->Cell($llain/100*$width,$height,@number_format($cost,2),1,0,'R',1);
                    $pdf->Ln();	            
                     $pdf->SetFillColor(255,255,255);            
                
            $pdf->Output();
           
       }
		}
?>