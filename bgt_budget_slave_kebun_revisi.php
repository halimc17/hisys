<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
$proses         =checkPostGet('proses','');
$param          =$_POST;
$arrBln         =array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sept","10"=>"Okt","11"=>"Nov","12"=>"Des");
$totRow         =checkPostGet('totRow','');
$keyId          =checkPostGet('keyId','');
$noakunCari     =checkPostGet('noakunCari','');
$nokegiatanCari =checkPostGet('nokegiatanCari','');
$excel          =checkPostGet('excel','');
$tt             =checkPostGet('tt','');
$update         =checkPostGet('update','');
$norma          =checkPostGet('norma','');
$jlhbaris       =checkPostGet('jlhbaris','');
$volpersen      =checkPostGet('volpersen','');
$hargakontrak   =checkPostGet('hargakontrak','');
//header
$page           =checkPostGet('page','');
$kdBlok         =checkPostGet('kdBlok','');
$kegId          =checkPostGet('kegId','');
$thnBudget      =checkPostGet('thnBudget','');
$noAkun         =checkPostGet('noAkun','');
$tpBudget       =checkPostGet('tpBudget','');
$rotThn         =checkPostGet('rotThn','');
$volKeg         =checkPostGet('volKeg','');
$satuan         =checkPostGet('satuan','');
$afd            =checkPostGet('afd','');
$where          =" tahunbudget='".$thnBudget."' and kodeorg like '".$kdBlok."%' and kegiatan='".$kegId."'  and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
$optNmBrg       =makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNm          =makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmAkun      =makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$optNmKeg       =makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$optKdBgt       =makeOption($dbname, 'bgt_kode', 'kodebudget,nama');
$where2         =" kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and tipebudget='ESTATE' and tahunbudget='".$thnBudget."'";
//sdm
$jmlhPerson     =checkPostGet('jmlhPerson','');
$kdGol          =checkPostGet('kdGol','');
$hkEfektif      =checkPostGet('hkEfektif','');
$tipeBudget     =checkPostGet('tipeBudget','');
$totBiaya       =checkPostGet('totBiaya','');
//material
$kdBudget       =checkPostGet('kdBudget','');
$kdBrg          =checkPostGet('kdBrg','');
$jmlhBrg        =checkPostGet('jmlhBrg','');
$satuanBrg      =checkPostGet('satuanBrg','');
$totHarga       =checkPostGet('totHarga','');
$nmBrg          =checkPostGet('nmBrg','');
$klmpkBrg       =checkPostGet('klmpkBrg','');
//tool
$nmBrgL         =checkPostGet('nmBrgL','');
$kdBrgL         =checkPostGet('kdBrgL','');
$jmlhBrgL       =checkPostGet('jmlhBrgL','');
$kdBudgetL      =checkPostGet('kdBudgetL','');
$totHargaL      =checkPostGet('totHargaL','');
$satuanBrgL     =checkPostGet('satuanBrgL','');
//kontrak
$kdBudgetK      =checkPostGet('kdBudgetK','');
$volKontrak     =checkPostGet('volKontrak','');
$satKontrak     =checkPostGet('satKontrak','');
$totBiayaK      =checkPostGet('totBiayaK','');
//kendaraan
$kdVhc          =checkPostGet('kdVhc','');
$jmlhJam        =checkPostGet('jmlhJam','');
$totBiayaKend   =checkPostGet('totBiayaKend','');
$kdBudgetV      =checkPostGet('kdBudgetV','');
$satVhc         =checkPostGet('satVhc','');
$thnbudgetHeader=checkPostGet('thnbudgetHeader','');
$tahunbudget    =checkPostGet('tahunbudget','');
$tahuntanam     =checkPostGet('tahuntanam','');
$kodeblok       =checkPostGet('kodeblok','');
$tipebudget     =checkPostGet('tipebudget','');
$noakun         =checkPostGet('noakun','');
$kegiatan       =checkPostGet('kegiatan','');
$volume         =checkPostGet('volume','');
$satuanvolume   =checkPostGet('satuanvolume','');
$rotasi         =checkPostGet('rotasi','');
$sebaran        =checkPostGet('sebaran','');
$divisi         =checkPostGet('divisi','');
if($proses==''){
    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';
    }else{
      $gen='genericGray.css';
    }
    $proses=$_GET['proses'];
    echo"<script language=javascript1.2 src=\"js/generic.js\"></script>
    <script language=javascript1.2 src=\"js/bgt_budget_kebun.js\"></script>
    <link rel=stylesheet type='text/css' href='style/".$gen."'>
    ";
    $tahunbudget=$_GET['tahunbudget'];
    $kodeblok=$_GET['kodeblok'];
    $tipebudget=$_GET['tipebudget'];
    $noakun=$_GET['noakun'];
    $kegiatan=$_GET['kegiatan'];
    $volume=$_GET['volume'];
    $satuanvolume=$_GET['satuanvolume'];
    $rotasi=$_GET['rotasi'];
}
switch($proses){
    case'copyblok':
        $bloktujuan=$_POST['bloktujuan'];
        $tahunbudget=$_POST['tahunbudget'];
        $bloksumber=$_POST['bloksumber'];
        $kegiatan=$_POST['kegiatan'];
        $tipebudget=$_POST['tipebudget'];
        // ambil luas blok sumber
        $str="select * from ".$dbname.".bgt_blok
            where tahunbudget = '".$tahunbudget."' and kodeblok = '".$bloksumber."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch())
        {
            $luassumber=$bar->hathnini;
        }
        // ambil luas blok tujuan
        $str="select * from ".$dbname.".bgt_blok
            where tahunbudget = '".$tahunbudget."' and kodeblok = '".$bloktujuan."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch())
        {
            $luastujuan=$bar->hathnini;
        }
        // cek apakah blok tujuan sudah ada data budget ESTATE
        $str="select * from ".$dbname.".bgt_budget
            where tahunbudget = '".$tahunbudget."' and kodeorg = '".$bloktujuan."' and kegiatan = '".$kegiatan."' and tipebudget = '".$tipebudget."'
            limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch())
        {
            echo "error: The same activity on this block already exist...";
            exit;
        }
        // list data budget sumber yang VHC
        $str="select * from ".$dbname.".bgt_budget
            where tahunbudget = '".$tahunbudget."' and kodeorg = '".$bloksumber."' and kegiatan = '".$kegiatan."' and tipebudget = '".$tipebudget."'
            and kodebudget = 'VHC'
            order by kodebudget";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch())
        {
            $dzArr[$bar->kunci]['kodevhc']=$bar->kodevhc;
            $dzArr[$bar->kunci]['jumlah']=$bar->jumlah;
        }
        if(!empty($dzArr))foreach($dzArr as $arey){
            $kodevhc=$arey['kodevhc'];
            @$jumlah=$luastujuan/$luassumber*$arey['jumlah'];
            // cek vhc
            if(!is_null($kodevhc)){
                $str="select * from ".$dbname.".bgt_biaya_jam_ken_vs_alokasi
                    where tahunbudget = '".$tahunbudget."' and kodevhc = '".$kodevhc."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                $no=0;
                while($bar= $res->fetch())
                {
                    $teralokasi=$bar->teralokasi;
                    $jamsetahun=$bar->jamsetahun;
                }
                $setelahditambah=$teralokasi+$jumlah;
                if($setelahditambah>$jamsetahun){
                    echo "error: Vehicle allocation ".$kodevhc." greater than capacity allowed\n
                        Allocated: ".number_format($teralokasi)." + ".number_format($jumlah)." = ".number_format($setelahditambah)."\n
                        Maximum: ".number_format($jamsetahun).".";
                    exit;
                }
            }
        }
        $dzArr=Array();
        // list data budget sumber
        $str="select * from ".$dbname.".bgt_budget
            where tahunbudget = '".$tahunbudget."' and kodeorg = '".$bloksumber."' and kegiatan = '".$kegiatan."' and tipebudget = '".$tipebudget."'
            and kodebudget != 'SUPERVISI'
            order by kodebudget";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch())
        {
            $dzArr[$bar->kunci]['tahunbudget']=$bar->tahunbudget;
            $dzArr[$bar->kunci]['kodeorg']=$bar->kodeorg;
            $dzArr[$bar->kunci]['tipebudget']=$bar->tipebudget;
            $dzArr[$bar->kunci]['kodebudget']=$bar->kodebudget;
            $dzArr[$bar->kunci]['kegiatan']=$bar->kegiatan;
            $dzArr[$bar->kunci]['noakun']=$bar->noakun;
            $dzArr[$bar->kunci]['volume']=$bar->volume;
            $dzArr[$bar->kunci]['satuanv']=$bar->satuanv;
            $dzArr[$bar->kunci]['rupiah']=$bar->rupiah;
            $dzArr[$bar->kunci]['kodevhc']=$bar->kodevhc;
            $dzArr[$bar->kunci]['kodebarang']=$bar->kodebarang;
            $dzArr[$bar->kunci]['rotasi']=$bar->rotasi;
            $dzArr[$bar->kunci]['regional']=$bar->regional;
            $dzArr[$bar->kunci]['jumlah']=$bar->jumlah;
            $dzArr[$bar->kunci]['satuanj']=$bar->satuanj;
            $dzArr[$bar->kunci]['keterangan']=$bar->keterangan;
            $dzArr[$bar->kunci]['tutup']=$bar->tutup;
        }
        if(!empty($dzArr))foreach($dzArr as $arey){
            $tahunbudget=$tahunbudget;
            $kodeorg=$bloktujuan;
            $tipebudget=$tipebudget;
            $kodebudget=$arey['kodebudget'];
            $kegiatan=$kegiatan;
            $noakun=$arey['noakun'];
            @$volume=$luastujuan/$luassumber*$arey['volume'];
            $satuanv=$arey['satuanv'];
            @$rupiah=$luastujuan/$luassumber*$arey['rupiah'];
            $kodevhc=$arey['kodevhc'];
            $kodebarang=$arey['kodebarang'];
            $rotasi=$arey['rotasi'];
            $regional=$arey['regional'];
            @$jumlah=$luastujuan/$luassumber*$arey['jumlah'];
            $satuanj=$arey['satuanj'];
            $keterangan=$arey['keterangan'];
            $tutup=$arey['tutup'];
            $sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,
                rupiah, kodevhc, kodebarang, rotasi, regional, updateby, jumlah, satuanj,
                keterangan, tutup) values
            ('".$tahunbudget."','".$kodeorg."','".$tipebudget."','".$kodebudget."','".$kegiatan."','".$noakun."','".$volume."','".$satuanv."',
                '".$rupiah."','".$kodevhc."','".$kodebarang."','".$rotasi."','".$regional."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satuanj."',
                '".$keterangan."','".$tutup."')";
            try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        } // end of foreach
    break;
	case'form_otherblok':
		$theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';
        }else{
          $gen='genericGray.css';
        }
        $stream="";
        $stream.="<fieldset>";
        $str="select * from ".$dbname.".bgt_blok where kodeblok = '".$kodeblok."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch()){
            $statusblok=$bar->statusblok;
            $tahuntanamx=$bar->thntnm;
            $luas=$bar->hathnini;
        }
       echo '
            <script language=JavaScript1.2 src=js/generic.js></script>
            <link rel=stylesheet type=text/css href=style/menu.css>
            <link rel=stylesheet type=text/css href=style/'.$gen.'>
            <link rel=stylesheet type=text/css href=style/calendarblue.css>
            <script language="javascript" src="js/zMaster.js"></script>
            <script type="text/javascript" src="js/bgt_budget_kebun.js"></script>
            <link rel=stylesheet type=text/css href="style/zTable.css">
                 ';
        $stream.= $_SESSION['lang']['budgetyear'].": ".$tahunbudget;
        $stream.= "<br>".$_SESSION['lang']['kodeblok'].": ".$kodeblok." ".$statusblok." ".$tahuntanamx." ".$luas." Ha";
        $stream.="<table class=sortable border=0 cellspacing=1 width=100%>";
        $stream.="<thead>
        <tr class=rowheader>
            <td align=center>".$_SESSION['lang']['kodebudget']."</td>
            <td align=center>".$_SESSION['lang']['rotasi']."</td>
            <td align=center>".$_SESSION['lang']['volume']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>
            <td align=center>".$_SESSION['lang']['kendaraan']."</td>
            <td align=center>".$_SESSION['lang']['material']."</td>
            <td align=center>".$_SESSION['lang']['jumlah']."</td>
            <td align=center>".$_SESSION['lang']['satuan']."</td>";
        $stream.="</tr>
        </thead>
        <tbody>";
        $str="select a.*, b.namabarang from ".$dbname.".bgt_budget a
            left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang
            where a.tahunbudget = '".$tahunbudget."' and a.kodeorg = '".$kodeblok."' and a.kegiatan = '".$kegiatan."' and a.tipebudget = 'ESTATE'
            and a.kodebudget != 'SUPERVISI'
            order by a.kodebudget";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch()){
          $stream.="<tr class=rowcontent><td align=center>".$bar->kodebudget."</td>
               <td align=center>".$bar->rotasi."</td>
               <td align=right>".$bar->volume."</td>
               <td align=left>".$bar->satuanv."</td>
               <td align=left>".$bar->kodevhc."</td>
               <td align=left>".$bar->namabarang."</td>
               <td align=right>".$bar->jumlah."</td>
               <td align=left>".$bar->satuanj."</td></tr>";
             }
        $stream.="</tbody></table>";
        $stream.="</br>";
		$sOrg2="select distinct thntnm from ".$dbname.".bgt_blok where tahunbudget='".$tahunbudget."' and kodeblok like '".substr($kodeblok,0,4)."%' order by thntnm asc";
		$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
		$opttt="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($rOrg2=$qOrg2->fetch()){
			$opttt.="<option value=".$rOrg2['thntnm'].">".$rOrg2['thntnm']."</option>";
		}
		$sOrg2="select distinct substr(kodeblok,1,6) as divisi from ".$dbname.".bgt_blok where tahunbudget='".$tahunbudget."' and kodeblok like '".substr($kodeblok,0,4)."%' order by divisi asc";
		$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
		$optdiv="<option value=''>".$_SESSION['lang']['all']."</option>";
		while($rOrg2=$qOrg2->fetch()){
			$optdiv.="<option value=".$rOrg2['divisi'].">".$rOrg2['divisi']."</option>";
		}
		$stream.="Tahun Tanam : <select style='width:155px;' id='ttcopyallblok' >".$opttt."</select>&nbsp;Divisi :&nbsp;<select style='width:155px;' id='divisicopyallblok' >".$optdiv."</select>
					<button class=mybutton onclick=\"isiviewOtherBlok('".$tahunbudget."','".$kodeblok."','".$tipebudget."','".$noakun."','".$kegiatan."','".$volume."','".$satuanvolume."','".$rotasi."','event','filter');\" >".$_SESSION['lang']['preview']."</button>
		";
		$stream.="<table class=sortable border=0 cellspacing=1 width=100%>";
        $stream.="<thead>
        <tr class=rowtitle>
            <td align=center>No</td>
            <td align=center>".$_SESSION['lang']['kodeblok']."</td>
            <td align=center>".$_SESSION['lang']['statusblok']."</td>
            <td align=center>".$_SESSION['lang']['thntanam']."</td>
            <td align=center>".$_SESSION['lang']['luasareal']."</td>
            <td align=center>Copy</td>";
        $stream.="</tr>
        </thead>
        <tbody id=containerdx>";
		echo $stream;
	break;
    case'otherblok':
		#$str="select kodeorg from ".$dbname.".bgt_budget where tahunbudget = '".$tahunbudget."' and kegiatan = '".$kegiatan."' and tipebudget = '".$tipebudget."'";
		$str="select * from ".$dbname.".bgt_blok where kodeblok = '".$kodeblok."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch()){
            $statusblok=$bar->statusblok;
            $tahuntanamx=$bar->thntnm;
            $luas=$bar->hathnini;
        }
        $wherestatus="";
        if($statusblok==('TB'||'TBM'||'TM'))$wherestatus="and statusblok in ('TB','TBM','TM')";
        if($statusblok==('BBT'))$wherestatus="and statusblok in ('BBT')";
		$whtt='';
		if($tahuntanam!=''){
			$whtt.=" and thntnm ='".$tahuntanam."' ";
		}
		if($divisi!=''){
			$whtt.=" and kodeblok like '%".$divisi."%' ";
		}
        $str="select * from ".$dbname.".bgt_blok
            where 1=1 ".$whtt." and closed='1' and kodeblok like '".substr($kodeblok,0,4)."%' ".$wherestatus." and kodeblok != '".$kodeblok."' and tahunbudget = '".$tahunbudget."' and kodeblok not in (select kodeorg from ".$dbname.".bgt_budget where tahunbudget = '".$tahunbudget."' and kegiatan = '".$kegiatan."' and tipebudget = '".$tipebudget."')
            order by kodeblok";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch()){
            $no+=1;
            $stream.="<tr class=rowcontent id=row".$no.">
               <td align=center>".$no."</td>
               <td align=center id=kdblok".$no.">".$bar->kodeblok."</td>
               <td align=center>".$bar->statusblok."</td>
               <td align=center>".$bar->thntnm."</td>
               <td align=right>".$bar->hathnini."</td>";
            $stream.="<td align=center><input type=\"checkbox\" name=\"copy\" value=\"copy\" onclick=\"copybudget('".$no."','0','".$tahunbudget."','".$kodeblok."','".$kegiatan."','ESTATE');\"></td>";
              $stream.="</tr>";
        }
		$stream.="<tr><td colspan=6 align=right>
						<button class='mybutton' onclick=\"copybudgetall('".$no."','".$tahunbudget."','".$kodeblok."','".$kegiatan."','ESTATE');\">Copy All</button>
		</td></tr>";
        $stream.="</tbody></table>";
		$stream.="</fieldset>";
        echo $stream;
		echo"<div id='progress' style='display:none;border:orange solid 1px;width:150px;position:fixed;right:20px;top:65px;color:#ff0000;font-family:Tahoma;font-size:13px;font-weight:bolder;text-align:center;background-color:#FFFFFF;z-index:10000;'>
		Please wait.....! <br>
		<img src='images/progress.gif'>
		</div>";
		echo"<div id='alternatelock' style='width:800px;height:600px;background-color:#999999;position:fixed;top:0px;left:0px;text-align:center;display:none;'>
		<img src='images/progress.gif'><br><b>P l e a s e &nbsp  w a i t . ...!</b>
		</div>";
    break;
	case'getBlok':
		$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sVhc="select distinct substr(kodeblok,1,6) as kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$thnBudget."' and kodeblok like '%".$_SESSION['empl']['lokasitugas']."%' and closed=1"; #exit("error".$sVhc);
		$qVhc=$owlPDO->query($sVhc) or die(print " Gagal: ".PDOException::getMessage());
		$qVhc->setFetchMode(PDO::FETCH_ASSOC);
		while($rVhc=$qVhc->fetch()){
			if($kdBlok!=''){
				$optBlok.="<option value='".$rVhc['kodeblok']."' ".($kdBlok==$rVhc['kodeblok']?'selected':'').">".$rVhc['kodeblok']."</option>";
			}else{
				$optBlok.="<option value='".$rVhc['kodeblok']."'>".$rVhc['kodeblok']."</option>";
			}
		}
		echo $optBlok;
	break;
	case'getKegiatan':
	if($kdBlok==''){
		exit("Error:Block code required");
	}
	$sStatus="select distinct statusblok from ".$dbname.".bgt_blok where kodeblok='".$kdBlok."'";
	$qStatus=$owlPDO->query($sStatus) or die(print " Gagal: ".PDOException::getMessage());
	$qStatus->setFetchMode(PDO::FETCH_ASSOC);
	$rStatus=$qStatus->fetch();
	$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sKeg="select distinct kodekegiatan,namakegiatan,kelompok from ".$dbname.".setup_kegiatan where  kelompok in ('PNN','TBM','TM','BBT','TB') and status='1'  order by kodekegiatan asc";
	$qKeg=$owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
	$qKeg->setFetchMode(PDO::FETCH_ASSOC);
	while($rKeg=$qKeg->fetch())
	{
		if($kegId!='')
		{
			$optKeg.="<option value=".$rKeg['kodekegiatan']." ".($rKeg['kodekegiatan']==$kegId?'selected':'').">".$rKeg['kodekegiatan']." - ".$rKeg['namakegiatan']." [".$rKeg['kelompok']."]</option>";
		}
		else
		{
			$optKeg.="<option value=".$rKeg['kodekegiatan'].">".$rKeg['kodekegiatan']." - ".$rKeg['namakegiatan']." [".$rKeg['kelompok']."]</option>";
		}
	}
	echo $optKeg;
	break;
	case'gantiKegiatan':
	if($kdBlok==''){
		exit("Error : Block code required");
	}
	$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	if($_SESSION['language']=='ID'){
		$dd='namakegiatan as namakegiatan';
	}else{
		$dd='namakegiatan1 as namakegiatan';
	}
	$sStatus="select * from ".$dbname.".bgt_blok where kodeblok like '".$kdBlok."%' and tahunbudget='".$thnBudget."' and thntnm='".$tt."'";
	$qStatus=$owlPDO->query($sStatus) or die(print " Gagal: ".PDOException::getMessage());
	$qStatus->setFetchMode(PDO::FETCH_ASSOC);
	while($rStatus=$qStatus->fetch()){
		@$luas+=$rStatus['hathnini'];
	}
	// $sStatus="select distinct statusblok from ".$dbname.".bgt_blok where kodeblok like '".$kdBlok."%' and tahunbudget='".$thnBudget."'";
	// $qStatus=$owlPDO->query($sStatus) or die(print " Gagal: ".PDOException::getMessage());
	// $qStatus->setFetchMode(PDO::FETCH_ASSOC);
	// while($rStatus=$qStatus->fetch()){
		// if($rStatus['statusblok']=='TM'){
			   // $sKeg="select distinct kodekegiatan,".$dd.",kelompok from ".$dbname.".setup_kegiatan where  kelompok in ('".$rStatus['statusblok']."','PNN') and status='1'  order by kodekegiatan asc";
		// }
		// // elseif($rStatus['statusblok']=='TBM'){
			   // // $sKeg="select distinct kodekegiatan,".$dd.",kelompok from ".$dbname.".setup_kegiatan where  kelompok='".$rStatus['statusblok']."' and status='1'  order by kodekegiatan asc";
		// //}
		// elseif($rStatus['statusblok']=='TB'){
			   // $sKeg="select distinct kodekegiatan,".$dd.",kelompok from ".$dbname.".setup_kegiatan where  kelompok in ('TB','TBM') and status='1'  order by kodekegiatan asc";
		// }
	// }
   $sKeg="select distinct kodekegiatan,".$dd.",kelompok from ".$dbname.".setup_kegiatan where  kelompok in ('TM','PNN','TB','TBM') and status='1'  order by kodekegiatan asc";
	//exit("error".$luas);
	$qKeg=$owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
	$qKeg->setFetchMode(PDO::FETCH_ASSOC);
	while($rKeg=$qKeg->fetch()){
		if(!empty($kegId)){
			$optKeg.="<option value=".$rKeg['kodekegiatan']." ".($rKeg['kodekegiatan']==$kegId?'selected':'').">".$rKeg['kodekegiatan']." - ".$rKeg['namakegiatan']." [".$rKeg['kelompok']."]</option>";
		}else{
			$optKeg.="<option value=".$rKeg['kodekegiatan'].">".$rKeg['kodekegiatan']." - ".$rKeg['namakegiatan']." [".$rKeg['kelompok']."]</option>";
		}
	}
	echo $optKeg."####".$luas;
	break;
	case'getSatuan':
           if($kegId=='')
           {
               exit("Error:Activity code required");
           }
            $sKegiata="select distinct satuan,noakun from ".$dbname.".setup_kegiatan where kodekegiatan='".$kegId."'";
            $qKegiatan=$owlPDO->query($sKegiata) or die(print " Gagal: ".PDOException::getMessage());
            $qKegiatan->setFetchMode(PDO::FETCH_ASSOC);
            $numrows=owlBaris($qKegiatan);
            $row=$numrows;
            if($row>0)
            {
                $rKegiatan=$qKegiatan->fetch();
                if($rKegiatan['satuan']=='NULL')
                {
                    $rKegiatan['satuan']='';
                }
                echo $rKegiatan['satuan']."###".$rKegiatan['noakun'];
            }
            else
            {
                exit("Error:Block status does not match");
            }
	break;
	case'cekSave':
		if($thnBudget==''||$kegId==''||$kdBlok==''||$tpBudget==''||$noAkun==''||$satuan==''||$rotThn==''){
			exit("Error:Field required");
		}
		if(strlen($thnBudget)<4){
			exit("Error:Budget year incorrect");
		}
		$sCek2="select distinct tutup from ".$dbname.".bgt_budget where ".$where2."";
		$qCek2=$owlPDO->query($sCek2) or die(print " Gagal: ".PDOException::getMessage());
		$qCek2->setFetchMode(PDO::FETCH_ASSOC);
		$rCek2=$qCek2->fetch();
		if($rCek2['tutup']>0){
		   exit("Error:  Budget ".$thnBudget." has been closed, could not modify");
		}
		if($hkEfektip==''){
			$sHk="select distinct * from ".$dbname.".bgt_hk where tahunbudget='".$thnBudget."' and unit = '".substr($_SESSION['empl']['lokasitugas'],0,4)."'";
			$qHk=$owlPDO->query($sHk) or die(print " Gagal: ".PDOException::getMessage());
			$qHk->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$qHk->fetch()){
				$thrlb=$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
				$thke=$bar['harisetahun']-$thrlb;
				$tsim=$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
				$tothke=$thke-($bar['jlhcuti']+$tsim);
				$hkEfektip=$tothke;
			}
			$optVhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$sVhc="select distinct kodetraksi ,kodevhc from ".$dbname.".bgt_biaya_jam_ken_vs_alokasi where tahunbudget='".$thnBudget."' order by kodevhc asc";
			$qVhc=$owlPDO->query($sVhc) or die(print " Gagal: ".PDOException::getMessage());
			$qVhc->setFetchMode(PDO::FETCH_ASSOC);
			while($rVhc=$qVhc->fetch()){
				$optnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$rVhc['kodevhc']."'");
				$nopol="";
				if($optnopol[$rVhc['kodevhc']]!=''){
					$nopol=" - ".$optnopol[$rVhc['kodevhc']];
				}
				$optVhc.="<option value='".$rVhc['kodevhc']."'>".$rVhc['kodevhc']."".$nopol." [".$rVhc['kodetraksi']."]</option>";
			}
			$optupah="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$supah="select distinct golongan from ".$dbname.".bgt_upah where
					kodeorg='".$_SESSION['empl']['lokasitugas']."'
					and tahunbudget='".$thnBudget."' and jumlah!=0";
			$qupah=$owlPDO->query($supah) or die(print " Gagal: ".PDOException::getMessage());
			$qupah->setFetchMode(PDO::FETCH_ASSOC);
			while($rupah=  $qupah->fetch()){
				$optupah.="<option value='".$rupah['golongan']."'>".$optKdBgt[$rupah['golongan']]."</option>";
			}
			$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$param['noAkun']."' order by a.noaruskas asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
			}
			echo $hkEfektip."###".$optVhc."###".$optupah."###".$optaruskas;
		}
	break;
        case'getUpah':
            if($kdGol=='')
            {
               exit("Error: Budget code required");
            }
          $sUpah="select jumlah from ".$dbname.".bgt_upah where tahunbudget='".$thnBudget."'
		  and kodeorg='".substr($kdBlok,0,4)."' and golongan='".$kdGol."' and closed=1";
          $qUpah=$owlPDO->query($sUpah) or die(print " Gagal: ".PDOException::getMessage());
          $qUpah->setFetchMode(PDO::FETCH_ASSOC);
          $numrows=owlBaris($qUpah);
          $row=$numrows;
          if($row!=0)
          {
              $rUpah=$qUpah->fetch();
              if($rUpah['jumlah']=='')
              {
                  exit("Error:Data upah belum ada, silahkan cek kembali");
              }
              else
              {
                  #$totalUpah=(floatval($rUpah['jumlah'])*floatval($jmlhPerson)*floatval($hkEfektif));
                  $totalUpah=(floatval($rUpah['jumlah'])*floatval($jmlhPerson));
                   echo $totalUpah;
              }
          }
          else
          {
              exit("Error: Data does not close yet, please verify");
          }
        break;
        case'saveSdm':
            if($kdGol==''||$totBiaya==0||$jmlhPerson==''){
                exit("Error : Fields are required");
            }
			$sUpah="select jumlah from ".$dbname.".bgt_upah where tahunbudget='".$thnBudget."' and kodeorg='".substr($kdBlok,0,4)."' and golongan='".$kdGol."' and closed=1";
			$qUpah=$owlPDO->query($sUpah) or die(print " Gagal: ".PDOException::getMessage());
			$qUpah->setFetchMode(PDO::FETCH_ASSOC);
			$rUpah=$qUpah->fetch();
			$rpperhk = $rUpah['jumlah'];
			$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdGol."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=  $res->fetch()){
				$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
			
			$luasttl=0;
			$str="select sum(hathnini) as luasttl from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdGol."' AND `kegiatan` = '".$kegId."') and `kodeblok` like '".$kdBlok."%'";
			$res = fetchdata($str)[0];
			$luasttl = $res['luasttl'];
			
			$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdGol."' AND `kegiatan` = '".$kegId."') and `kodeblok` like '".$kdBlok."%'";
			$res = fetchdata($str);
			$jlh = count($res);
			if($jlh>0){
				foreach($res as $bar){
					$luasblok = $bar['hathnini'];
					#$volume = $luasblok * $rotThn;
					$volume = $luasblok/$luasttl * $volKeg;
					#$jumlah = $rotThn * $luasblok * $norma;
					$jumlah = $luasblok/$luasttl * $param['jmlhPerson'];
					$totalrp = $jumlah * $rpperhk;
					$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,updateby,jumlah,satuanj,aruskas) values
					('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdGol."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','HK','".$param['aruskas']."')";
					try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
			}
			/*
			if($update=='1' or $update=='3'){
				$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdGol."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=  $res->fetch()){
					$luas=makeOption($dbname,'bgt_blok','kodeblok,hathnini',"kodeblok='".$bar['kodeorg']."' and tahunbudget='".$thnBudget."'");
					$luasblok = $luas[$bar['kodeorg']];
					$volume = $luasblok * $rotThn;
					$jumlah = $rotThn * $luasblok * $norma;
					$totalrp = $jumlah * $rpperhk;
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
					$str="update ".$dbname.".bgt_budget set jumlah='".$jumlah."', rupiah='".$totalrp."', volume='".$volume."', rotasi='".$rotThn."' and aruskas='".$param['aruskas']."' where kunci='".$bar['kunci']."'";
					try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
			}
			if($update=='2' or $update=='3'){
				$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdGol."' AND `kegiatan` = '".$kegId."') and `kodeblok` like '".$kdBlok."%'";
				$res = fetchdata($str);
				$jlh = count($res);
				if($jlh>0){
					foreach($res as $bar){
						$luasblok = $bar['hathnini'];
						$volume = $luasblok * $rotThn;
						$jumlah = $rotThn * $luasblok * $norma;
						$totalrp = $jumlah * $rpperhk;
						$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,updateby,jumlah,satuanj,aruskas) values
						('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdGol."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','HK','".$param['aruskas']."')";
						try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
					}
				}
			}
			 */
	break;
	case'saveMat':
		if($kdBudget==''||$totHarga==0||$jmlhBrg==''||$kdBrg==''){
			exit("Error : Fields are required");
		}
		$sRegion="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kdBlok,0,4)."' ";
		$qRegion=$owlPDO->query($sRegion) or die(print " Gagal: ".PDOException::getMessage());
		$qRegion->setFetchMode(PDO::FETCH_ASSOC);
		$rRegion=$qRegion->fetch();
		$sHrg="select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$rRegion['regional']."' and kodebarang='".$kdBrg."' and tahunbudget='".$thnBudget."' and closed=1";
		$qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
		$qHrg->setFetchMode(PDO::FETCH_ASSOC);
		$bHrg =  $qHrg->fetch();
		$hargabarang = $bHrg['hargasatuan'];
		$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrg."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=  $res->fetch()){
			$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
		
		
		$luasttl=0;
		$str="select sum(hathnini) as luasttl from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrg."' AND `kegiatan` = '".$kegId."') and kodeblok like '".$kdBlok."%'";
		$res = fetchdata($str)[0];
		$luasttl = $res['luasttl'];
			
			
		$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrg."' AND `kegiatan` = '".$kegId."') and kodeblok like '".$kdBlok."%'";
		$res = fetchdata($str);
		$jlh = count($res);
		if($jlh>0){
			foreach($res as $bar){
				$luasblok = $bar['hathnini'];
				$volume = $luasblok / $luasttl * $volKeg;
				#$jumlah = $rotThn * $luasblok * $norma;
				$jumlah = $luasblok/$luasttl * $param['jmlhBrg'];
				$totalrp = $jumlah * $hargabarang;
				$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,updateby,jumlah,satuanj,kodebarang,regional,aruskas) values
				('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudget."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satuanBrg."','".$kdBrg."','".$rRegion['regional']."','".$param['aruskas']."')";
				try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		/*
		if($update=='1' or $update=='3'){
			$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrg."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=  $res->fetch()){
				$luas=makeOption($dbname,'bgt_blok','kodeblok,hathnini',"kodeblok='".$bar['kodeorg']."' and tahunbudget='".$thnBudget."'");
				$luasblok = $luas[$bar['kodeorg']];
				$volume = $luasblok * $rotThn;
				$jumlah = $rotThn * $luasblok * $norma;
				$totalrp = $jumlah * $hargabarang;
				$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				$str="update ".$dbname.".bgt_budget set jumlah='".$jumlah."', rupiah='".$totalrp."', volume='".$volume."', rotasi='".$rotThn."' and aruskas='".$param['aruskas']."' where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		if($update=='2' or $update=='3'){
			$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrg."' AND `kegiatan` = '".$kegId."') and kodeblok like '".$kdBlok."%'";
			$res = fetchdata($str);
			$jlh = count($res);
			if($jlh>0){
				foreach($res as $bar){
					$luasblok = $bar['hathnini'];
					$volume = $luasblok * $rotThn;
					$jumlah = $rotThn * $luasblok * $norma;
					$totalrp = $jumlah * $hargabarang;
					$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,updateby,jumlah,satuanj,kodebarang,regional,aruskas) values
					('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudget."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satuanBrg."','".$kdBrg."','".$rRegion['regional']."','".$param['aruskas']."')";
					try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
			}
		}
		 */
	break;
	case'saveKontrak':
		if($kdBudgetK==''||$totBiayaK==0||$volKontrak==''){
			exit("Error: fields are required");
		}
		$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdBudgetK."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=  $res->fetch()){
			$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
		$luasttl=0;
		$str="select sum(hathnini) as luasttl from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdBudgetK."' AND `kegiatan` = '".$kegId."') and kodeblok like '".$kdBlok."%'";
		$res = fetchdata($str)[0];
		$luasttl = $res['luasttl'];
		
		
		$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdBudgetK."' AND `kegiatan` = '".$kegId."') and kodeblok like '".$kdBlok."%'";
		$res = fetchdata($str);
		$jlh = count($res);
		if($jlh>0){
			foreach($res as $bar){
				$luasblok = $bar['hathnini'];
				$volume = $luasblok / $luasttl * $volKeg;
				#$jumlah = $luasblok * $rotThn  * ($volpersen/100);
				$jumlah = $luasblok/$luasttl * $param['volKontrak'];
				$totalrp = $jumlah * $hargakontrak;
				$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,rupiah,rotasi,  updateby,jumlah,satuanj,aruskas) values
				('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudgetK."','".$kegId."','".$noAkun."','".$jumlah."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satKontrak."','".$param['aruskas']."')";
				try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		/*
		if($update=='1' or $update=='3'){
			$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdBudgetK."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=  $res->fetch()){
				$luas=makeOption($dbname,'bgt_blok','kodeblok,hathnini',"kodeblok='".$bar['kodeorg']."' and tahunbudget='".$thnBudget."'");
				$luasblok = $luas[$bar['kodeorg']];
				$volume = $luasblok * $rotThn;
				$jumlah = $luasblok * $rotThn  * ($volpersen/100);
				$totalrp = $jumlah * $hargakontrak;
				$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				$str="update ".$dbname.".bgt_budget set jumlah='".$jumlah."', rupiah='".$totalrp."', volume='".$jumlah."', rotasi='".$rotThn."' and aruskas='".$param['aruskas']."' where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		if($update=='2' or $update=='3'){
			$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdBudgetK."' AND `kegiatan` = '".$kegId."') and kodeblok like '".$kdBlok."%'";
			$res = fetchdata($str);
			$jlh = count($res);
			if($jlh>0){
				foreach($res as $bar){
					$luasblok = $bar['hathnini'];
					$volume = $luasblok * $rotThn;
					$jumlah = $luasblok * $rotThn  * ($volpersen/100);
					$totalrp = $jumlah * $hargakontrak;
					$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,rupiah,rotasi,  updateby,jumlah,satuanj,aruskas) values
					('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudgetK."','".$kegId."','".$noAkun."','".$jumlah."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satKontrak."','".$param['aruskas']."')";
					try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
			}
		} */
	break;
	case'saveTool':
		if($kdBudgetL==''||$totHargaL==0||$jmlhBrgL==''||$kdBrgL==''){
			exit("Error:fields are required");
		}
		$sRegion="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kdBlok,0,4)."' ";
		$qRegion=$owlPDO->query($sRegion) or die(print " Gagal: ".PDOException::getMessage());
		$qRegion->setFetchMode(PDO::FETCH_ASSOC);
		$rRegion=$qRegion->fetch();
		$sHrg="select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$rRegion['regional']."' and kodebarang='".$kdBrgL."' and tahunbudget='".$thnBudget."' and closed=1";
		$qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
		$qHrg->setFetchMode(PDO::FETCH_ASSOC);
		$bHrg =  $qHrg->fetch();
		$hargabarang = $bHrg['hargasatuan'];
		$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrgL."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=  $res->fetch()){
			$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
		
		$luasttl=0;
		$str="select sum(hathnini) as luasttl from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrgL."' AND `kegiatan` = '".$kegId."') and `kodeblok`  like '".$kdBlok."%'";
		$res = fetchdata($str)[0];
		$luasttl = $res['luasttl'];
		
		$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrgL."' AND `kegiatan` = '".$kegId."') and `kodeblok`  like '".$kdBlok."%'";
		$res = fetchdata($str);
		$jlh = count($res);
		if($jlh>0){
			foreach($res as $bar){
				$luasblok = $bar['hathnini'];
				$volume = $luasblok / $luasttl * $volKeg;
				#$jumlah = $rotThn * $luasblok * $norma;
				$jumlah = $luasblok/$luasttl * $param['jmlhBrgL'];
				$totalrp = $jumlah * $hargabarang;
				$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,updateby,jumlah,satuanj,kodebarang,regional,aruskas) values
				('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudgetL."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satuanBrgL."','".$kdBrgL."','".$rRegion['regional']."','".$param['aruskas']."')";
				try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		/*
		if($update=='1' or $update=='3'){
			$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrgL."' AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=  $res->fetch()){
				$luas=makeOption($dbname,'bgt_blok','kodeblok,hathnini',"kodeblok='".$bar['kodeorg']."' and tahunbudget='".$thnBudget."'");
				$luasblok = $luas[$bar['kodeorg']];
				$volume = $luasblok * $rotThn;
				$jumlah = $rotThn * $luasblok * $norma;
				$totalrp = $jumlah * $hargabarang;
				$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				$str="update ".$dbname.".bgt_budget set jumlah='".$jumlah."', rupiah='".$totalrp."', volume='".$volume."', rotasi='".$rotThn."' and aruskas='".$param['aruskas']."' where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		if($update=='2' or $update=='3'){
			$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebarang` = '".$kdBrgL."' AND `kegiatan` = '".$kegId."') and `kodeblok`  like '".$kdBlok."%'";
			$res = fetchdata($str);
			$jlh = count($res);
			if($jlh>0){
				foreach($res as $bar){
					$luasblok = $bar['hathnini'];
					$volume = $luasblok * $rotThn;
					$jumlah = $rotThn * $luasblok * $norma;
					$totalrp = $jumlah * $hargabarang;
					$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,updateby,jumlah,satuanj,kodebarang,regional,aruskas) values
					('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudgetL."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satuanBrgL."','".$kdBrgL."','".$rRegion['regional']."','".$param['aruskas']."')";
					try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
			}
		}
		 */
	break;
    case'saveKendaran':
		if($kdBudgetV==''||$totBiayaKend==0||$jmlhJam==''){
			exit("Error:Fields are required");
		}
		$sHrg="select distinct rpperjam from ".$dbname.".bgt_biaya_ken_per_jam where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."'";
		$qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
		$qHrg->setFetchMode(PDO::FETCH_ASSOC);
		$rHrg=$qHrg->fetch();
		$harga=$rHrg['rpperjam'];
		#
		$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdBudgetV."' AND kodevhc='".$kdVhc."'  AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
		$res = fetchdata($str);
		foreach($res as $bar){
			$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
		
		$luasttl=0;
		$str="select sum(hathnini) as luasttl from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND kodevhc='".$kdVhc."' AND `kodebudget` = '".$kdBudgetV."' AND `kegiatan` = '".$kegId."') and `kodeblok` like '".$kdBlok."%'";
		$res = fetchdata($str)[0];
		$luasttl = $res['luasttl'];
		
		
		$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND kodevhc='".$kdVhc."' AND `kodebudget` = '".$kdBudgetV."' AND `kegiatan` = '".$kegId."') and `kodeblok` like '".$kdBlok."%'";
		$res = fetchdata($str);
		$jlh = count($res);
		if($jlh>0){
			foreach($res as $bar){
				$kebun = substr($bar['kodeblok'],0,4);
				$sCekJam="select * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and unitalokasi='".$kebun."'";
				$rCekJam=fetchdata($sCekJam);
				$sAlokasi="select sum(jumlah) as jumlah from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and tipebudget<>'TRK' and left(kodeorg,4)='".$kebun."' group by left(kodeorg,4)";
				$rAlokasi=fetchdata($sCekJam);
				setIt($rAlokasi['jumlah'],0);
				$sisa=$rCekJam[0]['jumlahjam']-$rAlokasi[0]['jumlah'];
				if($jmlhJam>$sisa){
				  exit("Error : Vehicle ".$kdVhc." has been allocated : ".$rAlokasi[0]['jumlah']." from total hours :".$rCekJam[0]['jumlahjam']." can only allocate as remains : ".$sisa."");
				}
				$luasblok = $bar['hathnini'];
				#$volume = $luasblok * $rotThn;
				$volume = $luasblok / $luasttl * $volKeg;
				#$jumlah = $luasblok * $rotThn  * $norma;
				$jumlah = $luasblok / $luasttl  * $param['jmlhJam'];
				$totalrp = $jumlah * $harga;
				$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,rupiah,rotasi,kodevhc,  updateby,jumlah,satuanj,aruskas) values
				('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudgetV."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$kdVhc."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satVhc."','".$param['aruskas']."')";
				try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		/*
		if($update=='1' or $update=='3'){
			$str="SELECT * FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND `kodebudget` = '".$kdBudgetV."' AND kodevhc='".$kdVhc."'  AND `kegiatan` = '".$kegId."' and kodeorg in (select kodeblok from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` LIKE '".$kdBlok."%')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=  $res->fetch()){
				$kebun = substr($kdBlok,0,4);
				$sCekJam="select * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and unitalokasi='".$kebun."'";
				$rCekJam=fetchdata($sCekJam);
				$sAlokasi="select sum(jumlah) as jumlah from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and tipebudget<>'TRK' and left(kodeorg,4)='".$kebun."' group by left(kodeorg,4)";
				$rAlokasi=fetchdata($sCekJam);
				setIt($rAlokasi['jumlah'],0);
				@$sisa=$rCekJam[0]['jumlahjam']-$rAlokasi[0]['jumlah'];
				if($jmlhJam>$sisa){
				  exit("Error : Vehicle ".$kdVhc." has been allocated : ".$rAlokasi[0]['jumlah']." from total hours :".$rCekJam[0]['jumlahjam']." can only allocate as remains : ".$sisa."");
				}
				$luas=makeOption($dbname,'bgt_blok','kodeblok,hathnini',"kodeblok='".$bar['kodeorg']."' and tahunbudget='".$thnBudget."'");
				$luasblok = $luas[$bar['kodeorg']];
				$volume = $luasblok * $rotThn;
				$jumlah = $luasblok * $rotThn  * $norma;
				$totalrp = $jumlah * $harga;
				$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				$str="update ".$dbname.".bgt_budget set jumlah='".$jumlah."', rupiah='".$totalrp."', volume='".$volume."', rotasi='".$rotThn."' and aruskas='".$param['aruskas']."' where kunci='".$bar['kunci']."'";
				try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
			}
		}
		if($update=='2' or $update=='3'){
			$str="select * from ".$dbname.".bgt_blok where `tahunbudget` = '".$thnBudget."' and thntnm='".$tt."' and `kodeblok` not in (SELECT kodeorg FROM ".$dbname.".bgt_budget WHERE `tahunbudget` = '".$thnBudget."' AND `tipebudget` = '".$tpBudget."' AND `kodeorg` LIKE '".$kdBlok."%' AND kodevhc='".$kdVhc."' AND `kodebudget` = '".$kdBudgetV."' AND `kegiatan` = '".$kegId."') and `kodeblok` like '".$kdBlok."%'";
			$res = fetchdata($str);
			$jlh = count($res);
			if($jlh>0){
				foreach($res as $bar){
					$kebun = substr($bar['kodeblok'],0,4);
					$sCekJam="select * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and unitalokasi='".$kebun."'";
					$rCekJam=fetchdata($sCekJam);
					$sAlokasi="select sum(jumlah) as jumlah from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and tipebudget<>'TRK' and left(kodeorg,4)='".$kebun."' group by left(kodeorg,4)";
					$rAlokasi=fetchdata($sCekJam);
					setIt($rAlokasi['jumlah'],0);
					$sisa=$rCekJam[0]['jumlahjam']-$rAlokasi[0]['jumlah'];
					if($jmlhJam>$sisa){
					  exit("Error : Vehicle ".$kdVhc." has been allocated : ".$rAlokasi[0]['jumlah']." from total hours :".$rCekJam[0]['jumlahjam']." can only allocate as remains : ".$sisa."");
					}
					$luasblok = $bar['hathnini'];
					$volume = $luasblok * $rotThn;
					$jumlah = $luasblok * $rotThn  * $norma;
					$totalrp = $jumlah * $harga;
					$sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,rupiah,rotasi,kodevhc,  updateby,jumlah,satuanj,aruskas) values
					('".$thnBudget."','".$bar['kodeblok']."','".$tpBudget."','".$kdBudgetV."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totalrp."','".$rotThn."','".$kdVhc."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satVhc."','".$param['aruskas']."')";
					try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
			}
		}
		 */
		//exit("error");
		// $sCek="select * from ".$dbname.".bgt_budget where ".$where." and kodebudget like '%VHC%' and kodevhc='".$kdVhc."'";
		// $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		// $qCek->setFetchMode(PDO::FETCH_ASSOC);
		// $numrows=owlBaris($qCek);
		// $rCek=$numrows;
		// if($rCek<1){
		  // $volume=$volKeg*$rotThn;
		  // $sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,rupiah,rotasi, kodevhc, updateby,jumlah,satuanj) values
			  // ('".$thnBudget."','".$kdBlok."','".$tpBudget."','".$kdBudgetV."','".$kegId."','".$noAkun."','".$volume."','".$satuan."','".$totBiayaKend."','".$rotThn."','".$kdVhc."','".$_SESSION['standard']['userid']."','".$jmlhJam."','".$satVhc."')";
		  // try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		// }else{
		  // exit("Error:Data already exist");
		// }
	break;
    case'loadDataSdm':
		$sLoad="select tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,updateby,jumlah,satuanj,kunci,aruskas from ".$dbname.".bgt_budget where
		".$where." and kodebudget like '%SDM%'";
		$qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qLoad->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align='center'>".$res['kunci']."</td>";
			$tab.="<td align='center'>".$res['tahunbudget']."</td>";
			$tab.="<td align='center'>".$res['kodeorg']."</td>";
			$opttt=makeOption($dbname,'bgt_blok','kodeblok,thntnm',"kodeblok='".$res['kodeorg']."'");
			$tab.="<td align=center width=50px>".$opttt[$res['kodeorg']]."</td>";
			$tab.="<td align='center'>".$res['tipebudget']."</td>";
			$tab.="<td align='center'>".$res['kodebudget']."</td>";
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$res['kegiatan']."'");
			$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$res['noakun']."'");
			$tab.="<td align='center'>".$res['kegiatan']." - ".$nmkeg[$res['kegiatan']]."</td>";
			$tab.="<td align='center'>".$res['noakun']." - ".$nmakun[$res['noakun']]."</td>";
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$res['aruskas']."'");
			$tab.="<td align='center'>".$res['aruskas']." - ".$nmaruskas[$res['aruskas']]."</td>";
			$tab.="<td align='center'>".$res['rotasi']."</td>";
			$tab.="<td  align='right'>".$res['volume']."</td>";
			$tab.="<td  align='center'>".$res['satuanv']."</td>";
			$tab.="<td align='right'>".number_format($res['rupiah'],2)."</td>";
			$tab.="<td  align='right'>".number_format($res['jumlah'],2)."</td>";
			$tab.="<td  align='center'>".$res['satuanj']."</td>";
			$tab.="<td align=center style='cursor:pointer;'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$res['kunci'].",1)\" src='images/application/application_delete.png'/></td>";
			$tab.="</tr>";
			@$ttl+=$res['rupiah'];
			@$ttlhk+=$res['jumlah'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=13>TOTAL</td>";
		$tab.="<td align='right'>".number_format($ttl,2)."</td>";
		$tab.="<td align='right'>".number_format($ttlhk,2)."</td>";
		$tab.="<td align='right'></td>";
		$tab.="<td align='right'></td>";
		$tab.="</tr>";
		echo $tab;
	break;
        case'getBarang':
               $tab="<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>".$_SESSION['lang']['kodebarang']."</td>
                        <td>".$_SESSION['lang']['namabarang']."</td>
                        <td>".$_SESSION['lang']['satuan']."</td>
                        </tr><tbody>
                        ";
            if($nmBrg=='')
            {
                $nmBrg=$kdBarang;
            }
        $sLoad="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where  kelompokbarang='".substr($klmpkBrg,2,3)."' and (kodebarang like '%".$nmBrg."%'
            or namabarang like '%".$nmBrg."%')";
          $qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
          $qLoad->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$qLoad->fetch())
        {
            $no+=1;
            $tab.="<tr class=rowcontent onclick=\"setData('".$res['kodebarang']."','".$res['namabarang']."','".$res['satuan']."')\">";
            $tab.="<td>".$no."</td>";
            $tab.="<td>".$res['kodebarang']."</td>";
            $tab.="<td>".$res['namabarang']."</td>";
            $tab.="<td>".$res['satuan']."</td>";
            $tab.="</tr>";
        }
        echo $tab;
        break;
        case'getBarangL':
               $tab="<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:335px;width:480px;\">
                        <table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>
                        <thead>
                        <tr class=rowheader>
                        <td align=center>No.</td>
						<td align=center>".$_SESSION['lang']['kodebarang']."</td>
						<td align=center>".$_SESSION['lang']['namabarang']."</td>
						<td align=center>".$_SESSION['lang']['satuan']."</td>
						<td align=center>".$_SESSION['lang']['harga']."</td>
                        </tr><tbody>
                        ";
        $kodeOrg=$_SESSION['empl']['lokasitugas'];
		$sRegion="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kodeOrg,0,4)."' ";
		$qRegion=$owlPDO->query($sRegion) or die(print " Gagal: ".PDOException::getMessage());
		$qRegion->setFetchMode(PDO::FETCH_ASSOC);
		$rRegion=$qRegion->fetch();
		$region = $rRegion['regional'];
		$sLoad="select kodebarang,namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang like '%".$nmBrgL."%'
		or namabarang like '%".$nmBrgL."%'";
		$qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
        while($res=$qLoad->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent style=cursor:pointer onclick=\"setDataL('".$res['kodebarang']."','".$res['namabarang']."','".$res['satuan']."')\">";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$res['kodebarang']."</td>";
            $tab.="<td>".$res['namabarang']."</td>";
            $tab.="<td>".$res['satuan']."</td>";
			$sHrg="select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$region."' and kodebarang='".$res['kodebarang']."' and tahunbudget='".$thnBudget."' and closed=1";
            $qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
            $qHrg->setFetchMode(PDO::FETCH_ASSOC);
			$rHrg=$qHrg->fetch();
            $tab.="<td align=right>".@number_format($rHrg['hargasatuan'])."</td>";
            $tab.="</tr>";
        }
        echo $tab;
        break;
        case'getHarga':
            if(($jmlhBrg=='')||($jmlhBrg=='0')){
                exit("Material volume required");
            }
            $sRegion="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kdBlok,0,4)."' ";
            $qRegion=$owlPDO->query($sRegion) or die(print " Gagal: ".PDOException::getMessage());
            $qRegion->setFetchMode(PDO::FETCH_ASSOC);
            $rRegion=$qRegion->fetch();
            $sHrg="select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$rRegion['regional']."' and kodebarang='".$kdBrg."' and tahunbudget='".$thnBudget."' and closed=1";
            $qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
            $qHrg->setFetchMode(PDO::FETCH_ASSOC);
            $numrows=owlBaris($qHrg);
            $row=$numrows;
            if($row!=0)
            {
                $rHrg=$qHrg->fetch();
                if(($rHrg['hargasatuan']!='')||($rHrg['hargasatuan']!='0'))
                {
                    $hasil=floatval($rHrg['hargasatuan'])*floatval($jmlhBrg);
                    echo $hasil;
                }
                else
                {
                    exit("Error:Material price not found, please contact Purchase Dept.");
                }
            }
            else
            {
             exit("Error:Material price not found, please contact Purchase Dept.");
            }
        break;
         case'getHargaL':
            if(($jmlhBrgL=='')||($jmlhBrgL=='0'))
            {
                exit("Material volume required");
            }
            $sRegion="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kdBlok,0,4)."' ";
            $qRegion=$owlPDO->query($sRegion) or die(print " Gagal: ".PDOException::getMessage());
            $qRegion->setFetchMode(PDO::FETCH_ASSOC);
            $rRegion=$qRegion->fetch();
            $sHrg="select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$rRegion['regional']."' and kodebarang='".$kdBrgL."' and tahunbudget='".$thnBudget."' and closed=1";
            $qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
            $qHrg->setFetchMode(PDO::FETCH_ASSOC);
            $numrows=owlBaris($qHrg);
            $row=$numrows;
            if($row!=0)
            {
                $rHrg=$qHrg->fetch();
                if(($rHrg['hargasatuan']!='')||($rHrg['hargasatuan']!='0'))
                {
                    $hasil=floatval($rHrg['hargasatuan'])*floatval($jmlhBrgL);
                    echo $hasil;
                }
                else
                {
                    exit("Error:Material price not found, please contact Purchase Dept.");
                }
            }
            else
            {
                exit("Error:Material price not found, please contact Purchase Dept.");
            }
        break;
        case'getBiaya':
            $sHrg="select distinct rpperjam from ".$dbname.".bgt_biaya_ken_per_jam where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."'";
            $qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
            $qHrg->setFetchMode(PDO::FETCH_ASSOC);
            $rHrg=$qHrg->fetch();
            if(($rHrg['rpperjam']!='')||($rHrg['rpperjam']!='0'))
            {
                $hasil=$rHrg['rpperjam']*floatval($jmlhJam);
                echo $hasil;
            }
            else
            {
                exit("Error: Vehicle cost does not exist, please contact vehicle organizer");
            }
        break;
        case'delKodeblok':
            $sDel="delete from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg = '".$kdBlok."' and kegiatan = '".$kegId."' and tipebudget = 'ESTATE' and kodebudget != 'UMUM'";
            try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
        case'delData':
            $sDel="delete from ".$dbname.".bgt_budget where kunci='".$idData."'";
            try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
        case'loadDataMat':
			$sLoad="select kunci,tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,jumlah, satuanj,kodebarang,aruskas from ".$dbname.".bgt_budget where
			".$where." and substring(kodebudget,1,1)='M'";
			$qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
			$qLoad->setFetchMode(PDO::FETCH_ASSOC);
			while($res=$qLoad->fetch()){
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align='center'>".$no."</td>";
				$tab.="<td align='center'>".$res['kunci']."</td>";
				$tab.="<td align='center'>".$res['tahunbudget']."</td>";
				$tab.="<td align='center'>".$res['kodeorg']."</td>";
				$opttt=makeOption($dbname,'bgt_blok','kodeblok,thntnm',"kodeblok='".$res['kodeorg']."'");
				$tab.="<td align=center width=50px>".$opttt[$res['kodeorg']]."</td>";
				$tab.="<td align='center'>".$res['tipebudget']."</td>";
				$tab.="<td align='center'>".$res['kodebudget']."</td>";
				$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$res['kegiatan']."'");
				$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$res['noakun']."'");
				$tab.="<td align='center'>".$res['kegiatan']." - ".$nmkeg[$res['kegiatan']]."</td>";
				$tab.="<td align='center'>".$res['noakun']." - ".$nmakun[$res['noakun']]."</td>";
				$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$res['aruskas']."'");
				$tab.="<td align='center'>".$res['aruskas']." - ".$nmaruskas[$res['aruskas']]."</td>";
				$tab.="<td align='center'>".$res['rotasi']."</td>";
				$tab.="<td  align='right'>".$res['volume']."</td>";
				$tab.="<td  align='center'>".$res['satuanv']."</td>";
				$tab.="<td align='right'>".number_format($res['rupiah'],2)."</td>";
				$tab.="<td align='left'>".$res['kodebarang']." - ".$optNmBrg[$res['kodebarang']]."</td>";
				$tab.="<td  align='right'>".$res['jumlah']."</td>";
				$tab.="<td  align='center'>".$res['satuanj']."</td>";
				$tab.="<td align=center  style='cursor:pointer;'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$res['kunci'].",2)\" src='images/application/application_delete.png'/></td>";
				$tab.="</tr>";
				@$ttl+=$res['rupiah'];
				@$ttlhk+=$res['jumlah'];
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=13>TOTAL</td>";
			$tab.="<td align='right'>".number_format($ttl,2)."</td>";
			$tab.="<td align='right'></td>";
			$tab.="<td align='right'>".number_format($ttlhk,2)."</td>";
			$tab.="<td align='right'></td>";
			$tab.="<td align='right'></td>";
			$tab.="</tr>";
            echo $tab;
        break;
        case'loadDtLain':
		$sLoad="select kunci,tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,jumlah, satuanj from ".$dbname.".bgt_budget where
		".$where." and kodebudget like '%KONTRAK%'";
		$qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qLoad->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align='center'>".$no."</td>";
			$tab.="<td align='center'>".$res['kunci']."</td>";
			$tab.="<td align='center'>".$res['tahunbudget']."</td>";
			$tab.="<td align='center'>".$res['kodeorg']."</td>";
			$opttt=makeOption($dbname,'bgt_blok','kodeblok,thntnm',"kodeblok='".$res['kodeorg']."'");
			$tab.="<td align=center width=50px>".$opttt[$res['kodeorg']]."</td>";
			$tab.="<td align='center'>".$res['tipebudget']."</td>";
			$tab.="<td align='center'>".$res['kodebudget']."</td>";
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$res['kegiatan']."'");
			$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$res['noakun']."'");
			$tab.="<td align='center'>".$res['kegiatan']." - ".$nmkeg[$res['kegiatan']]."</td>";
			$tab.="<td align='center'>".$res['noakun']." - ".$nmakun[$res['noakun']]."</td>";
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$res['aruskas']."'");
			$tab.="<td align='center'>".$res['aruskas']." - ".$nmaruskas[$res['aruskas']]."</td>";
			$tab.="<td align='center'>".$res['rotasi']."</td>";
			$tab.="<td  align='right'>".$res['volume']."</td>";
			$tab.="<td  align='center'>".$res['satuanv']."</td>";
			$tab.="<td align='right'>".number_format($res['rupiah'],2)."</td>";
			$tab.="<td  align='right'>".number_format($res['jumlah'],2)."</td>";
			$tab.="<td  align='center'>".$res['satuanj']."</td>";
			$tab.="<td align=center  style='cursor:pointer;'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$res['kunci'].",4)\" src='images/application/application_delete.png'/></td>";
			$tab.="</tr>";
			@$ttl+=$res['rupiah'];
			@$ttlhk+=$res['jumlah'];
			@$ttlv+=$res['volume'];
		}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=13>TOTAL</td>";
			$tab.="<td align='right'>".number_format($ttl,2)."</td>";
			$tab.="<td align='right'></td>";
			$tab.="<td align='right'></td>";
			$tab.="<td align='right'></td>";
			$tab.="</tr>";
		echo $tab;
	break;
	case'loadDataTool':
		$sLoad="select aruskas,kunci,tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv, rupiah,rotasi,jumlah, satuanj,kodebarang from ".$dbname.".bgt_budget where
		".$where."  and kodebudget='TOOL'";
		$qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qLoad->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align='center'>".$no."</td>";
			$tab.="<td align='center'>".$res['kunci']."</td>";
			$tab.="<td align='center'>".$res['tahunbudget']."</td>";
			$tab.="<td align='center'>".$res['kodeorg']."</td>";
			$opttt=makeOption($dbname,'bgt_blok','kodeblok,thntnm',"kodeblok='".$res['kodeorg']."'");
			$tab.="<td align=center width=50px>".$opttt[$res['kodeorg']]."</td>";
			$tab.="<td align='center'>".$res['tipebudget']."</td>";
			$tab.="<td align='center'>".$res['kodebudget']."</td>";
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$res['kegiatan']."'");
			$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$res['noakun']."'");
			$tab.="<td align='left'>".$res['kegiatan']." - ".$nmkeg[$res['kegiatan']]."</td>";
			$tab.="<td align='left'>".$res['noakun']." - ".$nmakun[$res['noakun']]."</td>";
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$res['aruskas']."'");
			$tab.="<td align='center'>".$res['aruskas']." - ".$nmaruskas[$res['aruskas']]."</td>";
			$tab.="<td align='center'>".$res['rotasi']."</td>";
			$tab.="<td  align='right'>".$res['volume']."</td>";
			$tab.="<td  align='center'>".$res['satuanv']."</td>";
			$tab.="<td align='right'>".number_format($res['rupiah'],2)."</td>";
			$tab.="<td align='left'>".$res['kodebarang']." - ".$optNmBrg[$res['kodebarang']]."</td>";
			$tab.="<td  align='right'>".$res['jumlah']."</td>";
			$tab.="<td  align='center'>".$res['satuanj']."</td>";
			$tab.="<td align=center  style='cursor:pointer;'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$res['kunci'].",3)\" src='images/application/application_delete.png'/></td>";
			$tab.="</tr>";
			@$ttl+=$res['rupiah'];
			@$ttlhk+=$res['jumlah'];
		}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=13>TOTAL</td>";
			$tab.="<td align='right'>".number_format($ttl,2)."</td>";
			$tab.="<td align='right'></td>";
			$tab.="<td align='right'>".number_format($ttlhk,2)."</td>";
			$tab.="<td align='right'></td>";
			$tab.="<td align='right'></td>";
			$tab.="</tr>";
		echo $tab;
	break;
	case'loadDataKend':
		$sLoad="select aruskas,kunci,tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, kodevhc,  rupiah,rotasi,jumlah, satuanj from ".$dbname.".bgt_budget where
		".$where." and kodebudget like '%VHC%'";
		$qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qLoad->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align='center'>".$no."</td>";
			$tab.="<td align='center'>".$res['kunci']."</td>";
			$tab.="<td align='center'>".$res['tahunbudget']."</td>";
			$tab.="<td align='center'>".$res['kodeorg']."</td>";
			$opttt=makeOption($dbname,'bgt_blok','kodeblok,thntnm',"kodeblok='".$res['kodeorg']."'");
			$tab.="<td align=center width=50px>".$opttt[$res['kodeorg']]."</td>";
			$tab.="<td align='center'>".$res['tipebudget']."</td>";
			$tab.="<td align='center'>".$res['kodebudget']."</td>";
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$res['kegiatan']."'");
			$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$res['noakun']."'");
			$tab.="<td align='left'>".$res['kegiatan']." - ".$nmkeg[$res['kegiatan']]."</td>";
			$tab.="<td align='left'>".$res['noakun']." - ".$nmakun[$res['noakun']]."</td>";
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$res['aruskas']."'");
			$tab.="<td align='center'>".$res['aruskas']." - ".$nmaruskas[$res['aruskas']]."</td>";
			$tab.="<td align='center'>".$res['kodevhc']."</td>";
			$tab.="<td align='right'>".number_format($res['rupiah'],2)."</td>";
			$tab.="<td  align='right'>".$res['jumlah']."</td>";
			$tab.="<td  align='center'>".$res['satuanj']."</td>";
			$tab.="<td align=center  style='cursor:pointer;'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$res['kunci'].",5)\" src='images/application/application_delete.png'/></td>";
			$tab.="</tr>";
			@$ttl+=$res['rupiah'];
			@$ttlhk+=$res['jumlah'];
		}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=11>TOTAL</td>";
			$tab.="<td align='right'>".number_format($ttl,2)."</td>";
			$tab.="<td align='right'>".number_format($ttlhk,2)."</td>";
			$tab.="<td align='right'></td>";
			$tab.="<td align='right'></td>";
			$tab.="</tr>";
	echo $tab;
	break;
	case'setKdBrg':
	echo substr($klmpkBrg,2,3);
	break;
	case'loadDetailTotal':
            $optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
            $sBlok="select distinct kodeblok from ".$dbname.".bgt_blok where kodeblok like '".$_SESSION['empl']['lokasitugas']."%'order by kodeblok asc";
            $qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
            $qBlok->setFetchMode(PDO::FETCH_ASSOC);
            while($rBlok=$qBlok->fetch()){
                if($kdBlok!=''){
                    $optBlok.="<option value='".$rBlok['kodeblok']."' ".($rBlok['kodeblok']==$kdBlok?"selected":"").">".$rBlok['kodeblok']."</option>";
                }else{
                    $optBlok.="<option value='".$rBlok['kodeblok']."'>".$rBlok['kodeblok']."</option>";
                }
            }
            $whereCari="";
			if($kegiatan!=''){
                $whereCari.=" and (kegiatan like '%".$kegiatan."%' or kegiatan in (select kodekegiatan from ".$dbname.".setup_kegiatan where namakegiatan like '%".$kegiatan."%')) ";
            }
			if($sebaran=='1'){
                $whereCari.=" and kunci in (select kunci from ".$dbname.".bgt_distribusi) ";
            }elseif($sebaran=='2'){
				$whereCari.=" and kunci not in (select kunci from ".$dbname.".bgt_distribusi) ";
			}
            if($thnbudgetHeader!=''){
                $whereCari.=" and tahunbudget='".$thnbudgetHeader."'";
            }
            if($noakunCari!=''){
                $whereCari.=" and noakun='".$noakunCari."'";
            }
            if($afd!=''){
                $whereCari.=" and substr(kodeorg,1,6)='".$afd."'";
                // $optBlok="";
                // $optBlok="<option value=''>".$_SESSION['lang']['all']."</option>";
                // $sBlok="select distinct kodeblok from ".$dbname.".bgt_blok where kodeblok like '".$afd."%'order by kodeblok asc";
                // $qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
                // $qBlok->setFetchMode(PDO::FETCH_ASSOC);
                // while($rBlok=$qBlok->fetch()){
                    // $optBlok.="<option value='".$rBlok['kodeblok']."'>".$rBlok['kodeblok']."</option>";
                // }
            }
			if($kdBlok!=''){
                $whereCari.=" and kodeorg like '".$kdBlok."%'";
            }
	$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
		<tr class=rowheader>
		<th align=center>#</th>
		<th align=center>No</th>
		<th align=center>".$_SESSION['lang']['kodeblok']."</th>
		<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
		<th align=center>".$_SESSION['lang']['namakegiatan']."</th>
		<th align=center>".$_SESSION['lang']['aruskas']."</th>
		<th align=center>".$_SESSION['lang']['namabarang']."</th>
		<th align=center>".$_SESSION['lang']['kodevhc']."</th>
		<th align=center>".$_SESSION['lang']['total']."</th>";
		foreach($arrBln as $brsBulan =>$listBln){
			$tab.="<th align=center>".$listBln." (Rp)</th>";
		}
		$tab.="<th>Action</th>
			</tr>
			</thead>
		";
        $sLoad="select * from ".$dbname.".bgt_budget_detail where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and kodebudget!='UMUM' and tipebudget='ESTATE' ".$whereCari."";
        $qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
        $qLoad->setFetchMode(PDO::FETCH_ASSOC);
        $numrows=owlBaris($qLoad);
        $rAngka=$numrows;
		$jlhbrs=$rAngka;
        if($rAngka!=0){
			if($jlhbaris==0 or $jlhbaris==''){$jlhbaris=50;}
			$limit = $jlhbaris;
			$page = 0;
			$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
			if (isset($_POST['page'])) {
				$page = floatval($_POST['page']);
				if ($page < 0)
					$page = 0;
			}
			$offset = floatval($page) * $limit;
			$maxdisplay = (floatval($page) * $limit);
			$nox = 0;
			$nox = $maxdisplay;
			$sLoad="select * from ".$dbname.".bgt_budget_detail where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and kodebudget!='UMUM' and tipebudget='ESTATE' ".$whereCari." limit ".$offset.",".$limit."";
			$qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
			$qLoad->setFetchMode(PDO::FETCH_ASSOC);
			$numrowsx=owlBaris($qLoad);
			if($numrowsx>$limit){
				$ttlbrs=$limit;
			}else{
				$ttlbrs=$numrowsx;
			}
			$tab.="<tr class=rowcontent><td colspan=22><button class=mybutton onclick=sebarkanall(".$ttlbrs.") title=\"Sebarkan Seluruhnya\">Sebarkan Seluruhnya</button></td></tr>";
			$no = 0;
			while($res=$qLoad->fetch()){
				$no+=1;
				$nox+=1;
				$dtClik="onclick=\"getForm('Sebaran','<fieldset style=\'width:520px;height:400px;\'><legend>Sebaran Per Bulan</legend><div id=containerForm style=\'overflow:auto;height:450px;width:480px\'></div><input type=hidden id=keyId value=".$res['kunci']." /></fieldset>',".$res['rupiah'].",".$res['jumlah'].",'".$res['kodebudget']."',event);\"";
				$tab.="<tr class=rowcontent style='cursor:pointer;' id=baris".$no.">";
				$tab.="<td style=display:none>
						<input id='kunci".$no."' value=".$res['kunci']." />
						<input id='rupiah".$no."' value=".$res['rupiah']." />
						<input id='jlh".$no."' value=".$res['jumlah']." />
					</td>";
				$tab.="<td><input id=chkboxsebar".$no." type=checkbox onclick=sebarkanBoo(".$no."); title='Sebarkan sesuai proporsi diatas'></td>";
				$tab.="<td  align='center'>".$nox."</td>";
				$tab.="<td align='center' ".$dtClik.">".$res['kodeorg']."</td>";
				$tab.="<td align='center' ".$dtClik.">".$res['kodebudget']."</td>";
				$tab.="<td align='left' ".$dtClik.">".$optNmKeg[$res['kegiatan']]."</td>";
				$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$res['aruskas']."'");
				$tab.="<td align='center'>".$res['aruskas']." - ".$nmaruskas[$res['aruskas']]."</td>";
				$tab.="<td align='left' ".$dtClik.">".(isset($optNmBrg[$res['kodebarang']])? $optNmBrg[$res['kodebarang']]: '')."</td>";
				$tab.="<td align='left' ".$dtClik.">".$res['kodevhc']."</td>";
				$tab.="<td align='right' ".$dtClik.">".number_format($res['rupiah'],2)."</td>";
				foreach($arrBln as $brsBln =>$listData){
					if(strlen($brsBln)<2){
						$b="0".$brsBln;
					}else{
						$b=$brsBln;
					}
					$tab.="<td align='right'>".number_format($res['rp'.$b],2)."</td>";
				}
				$tab.="<td align=center  style='cursor:pointer;'><img src=\"images/zoom.png\" class=\"resicon\" title='sebarang_".$res['kunci']."' ".$dtClik." /></td>";
				$tab.="</tr>";
			}
		}else{
            $tab.="<tr class=rowcontent style='cursor:pointer;' id=baris".$no.">";
            $tab.="<td colspan=21>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }
		@$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = (floatval($page) == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $tab.="
                     <tr><td colspan=21 align=center>";
        if (floatval($page) == '0') {
            $tab.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $tab.="<button class=mybutton onclick=loadDetailTotal(" . (floatval($page) - 1) . ");>Prev</button>";
        }
        $tab.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $tab.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $tab.="<button class=mybutton onclick=loadDetailTotal(" . ($page + 1) . ");>Next</button>";
        }
        $tab.="</td>
            </tr>";
		echo $tab."<input type=hidden id=jmlhRow value=".$no." />"."###".@$optPage."###".@number_format($totalPage,0)."###".@$page."###".$optBlok;
	break;
	case'getForm':
            $rupiah=$_POST['rupiah'];
            $jumlah=$_POST['jumlah'];
            $kodebudget=$_POST['kodebudget'];
            $sCek="select distinct rp01, rp02, rp03, rp04, rp05, rp06, rp07, rp08, rp09, rp10, rp11, rp12,fis01, fis02, fis03, fis04, fis05, fis06, fis07, fis08, fis09, fis10, fis11, fis12
                from ".$dbname.".bgt_distribusi where kunci='".$keyId."'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $numrows=owlBaris($qCek);
            $rCek=$numrows;
            if($rCek<1){
                $sData="select rupiah,jumlah from ".$dbname.".bgt_budget_detail where kunci='".$keyId."'";
                $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                $qData->setFetchMode(PDO::FETCH_ASSOC);
                $rData=$qData->fetch();
                @$totRupiah=$rData['rupiah']/12;
                @$totFisik=$rData['jumlah']/12;
                    for($a=1;$a<=12;$a++){
                        $totRupiahArr[$a]=number_format($totRupiah,2,'.','');
                        $totFisikArr[$a]=number_format($totFisik,2,'.','');
                    }
            }else{
                $res=$qCek->fetch();
                 for($a=1;$a<=12;$a++){
					if(strlen($a)<2){
					 $b="0".$a;
					}else{
					 $b=$a;
					}
                    $totRupiahArr[$a]=$res['rp'.$b];
                    $totFisikArr[$a]=$res['fis'.$b];
                 }
                 for($c=1;$c<=12;$c++){
                    @$cekFisik+=$totFisikArr[$c];
                    @$cekRupiah+=$totRupiahArr[$c];
                 }
                if($cekFisik==0){
                    $sData="select rupiah,jumlah from ".$dbname.".bgt_budget_detail where kunci='".$keyId."'";
                    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                    $qData->setFetchMode(PDO::FETCH_ASSOC);
                    $rData=$qData->fetch();
                    @$totFisik=$rData['jumlah']/12;
                    for($a=1;$a<=12;$a++){
                       $totFisikArr[$a]=number_format($totFisik,2,'.','');
                    }
                }
                if($cekRupiah==0){
                    $sData="select rupiah,jumlah from ".$dbname.".bgt_budget_detail where kunci='".$keyId."'";
                    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                    $qData->setFetchMode(PDO::FETCH_ASSOC);
                    $rData=$qData->fetch();
                    @$totRupiah=$rData['rupiah']/12;
                    for($a=1;$a<=12;$a++){
                       $totRupiahArr[$a]=number_format($totRupiah,2,'.','');
                    }
                }
            }
            $tot=count($arrBln);
            if($tot==0){
                exit("Error : Data not found");
            }
            $tab.="<table width=100%><tr><td><fieldset><legend>Rupiah : ".$kodebudget."</legend>
                   <table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";
            $tab.="<tr class=rowheader><td>Rp/Thn</td></td><td>%</td>
                   <td>".number_format($rupiah,0)."</td></tr></thead><tbody>";
            foreach($arrBln as $rpBln =>$listRpBln){
                @$hasilBlnan=$totRupiahArr[$rpBln]/$rupiah;
                $tab.="<tr class=rowcontent><td>".$listRpBln."</td>
                     <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=rppersen".$rpBln." onblur=ubahNilai(this.value,'".$rupiah."','rupiah_','".$jumlah."') value=".number_format((($hasilBlnan)*100),2,'.','')."></td>";
                $tab.="<td><input type='text' id=rupiah_".$rpBln." class=\"myinputtextnumber\" style=\"width:75px;\" value=".$totRupiahArr[$rpBln]." /></td>
                      </tr>";
            }
            $tab.="<tr class=rowcontent><td  colspan=3 align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveRupiah(".$tot.")\" src='images/save.png'/>&nbsp;&nbsp;<img id='detail_add' title='Clear Form' class=zImgBtn  width='16' height='16'  onclick=\"clearRupiah()\" src='images/clear.png'/></td>";
            $tab.="</tr></tbody></table></fieldset></td><td>";
            $tab.="<fieldset><legend>Fisik : ".$kodebudget."</legend>
                <table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";
            $tab.="<tr class=rowheader><td>Fisik/Thn</td><td>%</td>
                  <td>".number_format($jumlah,0)."</td></tr></thead><tbody>";
            foreach($arrBln as $fisikBln =>$listFisikBln){
                @$hsilFIsik=$totFisikArr[$fisikBln]/$jumlah;
                $tab.="<tr class=rowcontent><td>".$listFisikBln."</td>
                       <td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=fispersen".$fisikBln." onblur=ubahNilai(this.value,'".$jumlah."','fisik_') value=".number_format(((@$hsilFIsik)*100),2,'.','')."></td>";
                $tab.="<td><input type='text' id=fisik_".$fisikBln." class=\"myinputtextnumber\" style=\"width:65px;\" value=".$totFisikArr[$fisikBln]." /></td>
                      </tr>";
            }
            $tab.="<tr class=rowcontent><td colspan=3 align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveFisik(".$tot.")\" src='images/save.png'/>&nbsp;&nbsp;<img id='detail_add' title='Clear Form' class=zImgBtn  width='16' height='16'  onclick=\"clearFisik()\" src='images/clear.png'/></td>";
            $tab.="</tr></tbody></table></fieldset></td></table></p>";
            $tab.="<p align=center><button class=mybutton id=btlTmbl2 name=btlTmbl2 onclick='closForm()'   >".$_SESSION['lang']['close']."</button>";
            echo $tab;
        break;
        case'saveRupiah':
            $sCek="select distinct rupiah,jumlah from ".$dbname.".bgt_budget_detail where kunci='".$keyId."'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=$qCek->fetch();
                    for($a=1;$a<=$totRow;$a++)
                    {
                        if($_POST['arrRup'][$a]=='')
                        {
                            $_POST['arrRup'][$a]=0;
                        }
                        $totalSumRup+=$_POST['arrRup'][$a];
                    }
                    if($totalSumRup>$rCek['rupiah'])
                    {
                        exit("Error:Total monthly greater than total a year");
                    }
                    if(($totalSumRup==0)||($totalSumRup==''))
                    {
                        exit("Error: Total  must greater than 0");
                    }
                    $sCek="select distinct * from ".$dbname.".bgt_distribusi where kunci='".$keyId."'";
                    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                    $qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $numrows=owlBaris($qCek);
                    $rCek=$numrows;
                    if($rCek<1)
                    {
                        $sInsert="insert into ".$dbname.".bgt_distribusi  (kunci, updateby, rp01, rp02, rp03, rp04, rp05, rp06, rp07, rp08, rp09, rp10, rp11, rp12)";
                        $sInsert.=" values ('".$keyId."','".$_SESSION['standard']['userid']."'";
                        for($a=1;$a<=$totRow;$a++)
                        {
                            $sInsert.=",'".str_replace(',','',$_POST['arrRup'][$a])."'";
                        }
                        $sInsert.=")";
                       try{$owlPDO->exec($sInsert); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                    }
                    else
                    {
                        $sUpdate="update ".$dbname.".bgt_distribusi set updateby='".$_SESSION['standard']['userid']."' ";
                            for($a=1;$a<=$totRow;$a++)
                            {
                                if(strlen($a)=='1')
                                {
                                    $c="0".$a;
                                }
                                else
                                {
                                    $c=$a;
                                }
                                $sUpdate.=" ,rp".$c."='".$_POST['arrRup'][$a]."'";
                            }
                         $sUpdate.=" where kunci='".$keyId."'";
                         try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                    }
            break;
            case'saveFisik':
            $sCek="select distinct rupiah,jumlah from ".$dbname.".bgt_budget_detail where kunci='".$keyId."'";
            $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            $rCek=$qCek->fetch();
                    for($a=1;$a<=$totRow;$a++)
                    {
                        if($_POST['arrFisik'][$a]=='')
                        {
                            $_POST['arrFisik'][$a]=0;
                        }
                        $totalSumFisik+=$_POST['arrFisik'][$a];
                    }
                    if(($totalSumFisik==0)||($totalSumFisik==''))
                    {
                        exit("Error: Total  must greater than 0");
                    }
                    $sCek="select distinct * from ".$dbname.".bgt_distribusi where kunci='".$keyId."'";
                    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                    $qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $numrows=owlBaris($qCek);
                    $rCek=$numrows;
                    if($rCek<1)
                    {
                        $sInsert="insert into ".$dbname.".bgt_distribusi  (kunci, updateby, fis01, fis02, fis03, fis04, fis05, fis06, fis07, fis08, fis09, fis10, fis11, fis12)";
                        $sInsert.=" values ('".$keyId."','".$_SESSION['standard']['userid']."'";
                        for($a=1;$a<=$totRow;$a++)
                        {
                            $sInsert.=",'".str_replace(',','',$_POST['arrFisik'][$a])."'";
                        }
                        $sInsert.=")";
                        try{$owlPDO->exec($sInsert);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                    }
                    else
                    {
                        $sUpdate="update ".$dbname.".bgt_distribusi set updateby='".$_SESSION['standard']['userid']."' ";
                            for($a=1;$a<=$totRow;$a++)
                            {
                                if(strlen($a)=='1')
                                {
                                    $c="0".$a;
                                }
                                else
                                {
                                    $c=$a;
                                }
                                $sUpdate.=" ,fis".$c."='".$_POST['arrFisik'][$a]."'";
                            }
                         $sUpdate.=" where kunci='".$keyId."'";
                         try{$owlPDO->exec($sUpdate);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                    }
            break;
            case'getDetailData':
                $tmbh='';
                if($thnbudgetHeader!=''){
                    $tmbh=" and tahunbudget='".$thnbudgetHeader."'";
                }
                if($noakunCari!=''){
                    $tmbh.=" and noakun ='".$noakunCari."'";
                }
				if($nokegiatanCari!=''){
                    $tmbh.=" and kegiatan ='".$nokegiatanCari."'";
                }
                if($kdBlok!=''){
                    $blok="kodeorg='".$kdBlok."'";
                }else{
                    $blok="kodeorg like '%".$_SESSION['empl']['lokasitugas']."%'";
                }
				if($excel=='excel'){
					$tab="<table cellspacing=1 cellpadding=1 class=sortable border=1><thead>";
				}else{
					$tab="<table cellspacing=1 cellpadding=1 class=sortable border=0><thead>";
				}
                $tab.="<tr class=rowheader>";
                $tab.="<td align=center>No.</td>";
                $tab.="<td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['tipe']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['divisi']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['kodeblok']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['noakun']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['kegiatan']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['volume']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['satuan']."</td>";
                $tab.="<td align=center width=50px>".$_SESSION['lang']['rotasi']." / ".$_SESSION['lang']['tahun']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['sdm']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['material']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['peralatan']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['kontrak']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['kndran']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['total']."</td>";
                $tab.="<td align=center>Rupiah / Sat</td>";
                $tab.="<td align=center colspan=3>Action</td>";
                $tab.="</tr></thead>";
                $tab.="<tbody>";
				$limit=30;
                $page=0;
                if(isset($_POST['page'])){
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;
                $sql2="select * from ".$dbname.".bgt_budget where ".$blok." and kodebudget!='UMUM' ".$tmbh."  group by tahunbudget,kodeorg,tipebudget, kegiatan, noakun order by tahunbudget desc , lastupdate desc";
                $numrows=count(fetchData($sql2));
				$jlhbrs=$numrows;
				$limitation =  " limit " . $offset . "," . $limit;
				if ($excel == 'excel') {
					$limitation =  "";
					if($jlhbrs > 1000){
						exit("WARNING: Data terlalu besar untuk di download.");
					}
				}
                if($jlhbrs!=0){
					if($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
						$whsdm=" and kodebudget like 'EXPL%'";
					}else{
						$whsdm=" and kodebudget like 'SDM%'";
					}
					$tlluas=0;
					$sData="select tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,rotasi,tutup,lastupdate from ".$dbname.".bgt_budget where ".$blok." and tipebudget='ESTATE'  and kodebudget!='UMUM' ".$tmbh."  group by tahunbudget,kodeorg,tipebudget, kegiatan, noakun order by tahunbudget desc, lastupdate desc ".$limitation."";
					$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
					$qData->setFetchMode(PDO::FETCH_ASSOC);
					while($rData=$qData->fetch()){
						$no+=1;
						$opttt=makeOption($dbname,'bgt_blok','kodeblok,thntnm',"kodeblok='".$rData['kodeorg']."'");
						$str="select sum(hathnini) as luas from ".$dbname.".bgt_blok where kodeblok like '".substr($rData['kodeorg'],0,6)."%' and tahunbudget='".$rData['tahunbudget']."' and thntnm='".$opttt[$rData['kodeorg']]."'";
						$bar = fetchData($str);
						$tlluas = $bar[0]['luas'];
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=center>".$rData['tahunbudget']."</td>";
						$tab.="<td align=center>".$rData['tipebudget']."</td>";
						$tab.="<td>".substr($rData['kodeorg'],0,6)."</td>";
						$tab.="<td>".$rData['kodeorg']."</td>";
						$tab.="<td align=center width=50px>".$opttt[$rData['kodeorg']]."</td>";
						$tab.="<td>".$rData['noakun']."</td>";
						$tab.="<td>".$optNmKeg[$rData['kegiatan']]."</td>";
						$tab.="<td align=right>".$rData['volume']."</td>";
						$tab.="<td>".$rData['satuanv']."</td>";
						$tab.="<td align=right>".$rData['rotasi']."</td>";
						#SDM
						$sdm = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$rData['tahunbudget']."' and kodeorg='".$rData['kodeorg']."' and tipebudget='".$rData['tipebudget']."' and  kegiatan='".$rData['kegiatan']."' and noakun='".$rData['noakun']."' ".$whsdm."";
						$ressdm = fetchData($sdm);
						$tab.="<td align=right>".@number_format($ressdm[0]['jumlah'])."</td>";
						#Material
						$mat = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$rData['tahunbudget']."' and kodeorg='".$rData['kodeorg']."' and tipebudget='".$rData['tipebudget']."' and  kegiatan='".$rData['kegiatan']."' and noakun='".$rData['noakun']."' and kodebudget like 'M-%'";
						$resmat = fetchData($mat);
						$tab.="<td align=right>".@number_format($resmat[0]['jumlah'])."</td>";
						#tool
						$tool = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$rData['tahunbudget']."' and kodeorg='".$rData['kodeorg']."' and tipebudget='".$rData['tipebudget']."' and  kegiatan='".$rData['kegiatan']."' and noakun='".$rData['noakun']."' and kodebudget like 'TOOL%'";
						$restool = fetchData($tool);
						$tab.="<td align=right>".@number_format($restool[0]['jumlah'])."</td>";
						#kont
						$kont = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$rData['tahunbudget']."' and kodeorg='".$rData['kodeorg']."' and tipebudget='".$rData['tipebudget']."' and  kegiatan='".$rData['kegiatan']."' and noakun='".$rData['noakun']."' and kodebudget like 'KONTRAK%'";
						$reskont = fetchData($kont);
						$tab.="<td align=right>".@number_format($reskont[0]['jumlah'])."</td>";
						#vhc
						$vhc = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$rData['tahunbudget']."' and kodeorg='".$rData['kodeorg']."' and tipebudget='".$rData['tipebudget']."' and  kegiatan='".$rData['kegiatan']."' and noakun='".$rData['noakun']."' and kodebudget like 'VHC%'";
						$resvhc = fetchData($vhc);
						$tab.="<td align=right>".@number_format($resvhc[0]['jumlah'])."</td>";
						$ttl=$resvhc[0]['jumlah']+$reskont[0]['jumlah']+$restool[0]['jumlah']+$resmat[0]['jumlah']+$ressdm[0]['jumlah'];
						$tab.="<td align=right>".@number_format($ttl)."</td>";
						$tab.="<td align=right>".@number_format($ttl/$rData['volume'])."</td>";
						if($rData['tutup']==0){
							$tab.="<td  align=center style='cursor:pointer;'>
								<img id='detail_edit' title='Edit' class=zImgBtn onclick=\"filFieldHead('".$rData['tahunbudget']."','".$rData['kodeorg']."','".$rData['tipebudget']."','".$rData['noakun']."','".$rData['kegiatan']."','".$rData['volume']."','".$rData['satuanv']."','".$rData['rotasi']."','".substr($rData['kodeorg'],0,6)."','".$opttt[$rData['kodeorg']]."','".$tlluas."')\" src='images/application/application_edit.png'/>
							</td>";
							$tab.="<td  align=center style='cursor:pointer;'>
								<img id='detail_copy' title='Copy' class=zImgBtn onclick=\"viewOtherBlok('".$rData['tahunbudget']."','".$rData['kodeorg']."','".$rData['tipebudget']."','".$rData['noakun']."','".$rData['kegiatan']."','".$rData['volume']."','".$rData['satuanv']."','".$rData['rotasi']."',event);\" src='images/application/application_cascade.png'/>
							</td>";
							if($rData['tutup']!=1)$tab.="<td  align=center style='cursor:pointer;'>
								<img id='detail_del' title='Delete' class=zImgBtn onclick=\"delFieldHead('".$rData['tahunbudget']."','".$rData['kodeorg']."','".$rData['kegiatan']."')\" src='images/application/application_delete.png'/>
							</td>";
							else $tab.="<td  align=center style='cursor:pointer;'>
								".$_SESSION['lang']['tutup']."
							</td>";
						}else{
							$tab.="<td colspan=3>".$_SESSION['lang']['tutup']."</td>";
						}
						$tab.="</tr>";
					}
					  $tab.="
					<tr class=rowheader><td colspan=21 align=center>
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
					<button class=mybutton onclick=cariTrans(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariTrans(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					</td>
					</tr>";
                }else{
                    $tab.="<tr class=rowcontent><td colspan=21>".$_SESSION['lang']['dataempty']."</td></tr>";
                }
                $tab.="</tbody></table>";
				if($excel!='excel'){
					echo $tab;
				}else{
					$stream = $tab;
					$nop_ = "budget_kebun_";
					if (strlen($stream) > 0) {
						if ($handle = opendir('tempExcel')) {
							while (false !== ($file = readdir($handle))) {
								if ($file != "." && $file != ".." && $file != "index.html") {
									@unlink('tempExcel/' . $file);
								}
							}
							closedir($handle);
						}
						$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
						if (!fwrite($handle, $stream)) {
							echo "<script language=javascript1.2>
										parent.window.alert('Cant convert to excel format');
										</script>";
							exit;
						} else {
							echo "<script language=javascript1.2>
										window.location='tempExcel/" . $nop_ . ".xls';
										</script>";
						}
						closedir($handle);
					}
				}
            break;
    case'closeBudget':
            if($thnBudget==''){
				exit("Error: Budget year required");
            }
            // List VHC
            $qVhc = "SELECT DISTINCT kodevhc FROM ".$dbname.".`bgt_biaya_jam_ken_vs_alokasi`";
            $resVhc = fetchData($qVhc);
            // Validasi Alokasi VHC
            $whereJam = "tahunbudget='".$thnBudget."' and unitalokasi='".$_SESSION['empl']['lokasitugas']."'";
             $optJam = makeOption($dbname,'bgt_vhc_jam','kodevhc,jumlahjam',$whereJam);
            $sAlokasi="select kodevhc,sum(jumlah) as jumlah from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget.
                    "' and tipebudget<>'TRK' and left(kodeorg,4)='".$_SESSION['empl']['lokasitugas']."' group by left(kodeorg,4),kodevhc";
            $resAlokasi = fetchData($sAlokasi);
            $optAlokasi = array();
            foreach($resAlokasi as $row) {
                    $optAlokasi[$row['kodevhc']] = $row['jumlah'];
            }
            $vhcPending = array();
            foreach($resVhc as $r) {
                    setIt($optAlokasi[$r['kodevhc']],0);
                    if($optJam[$r['kodevhc']] > $optAlokasi[$r['kodevhc']]) {
                            $vhcPending[$r['kodevhc']] = $optJam[$r['kodevhc']] - $optAlokasi[$r['kodevhc']];
                    }
            }
            if(!empty($vhcPending)) {
                    $str = "Warning: Ada Jam Kendaraan yang belum dialokasi\n";
                    foreach($vhcPending as $vhc=>$sisa) {
                            $str .= $vhc." = ".$sisa."\n";
                    }
                    exit($str);
            }
            //proteksi sebaran harus benar
            $strx="select noakun,kodeorg
                                    FROM bgt_budget_detail where tahunbudget=".$thnBudget." and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'
                                    and abs((rp01+rp02+rp03+rp04+rp05+rp06+rp07+rp08+rp09+rp10+rp11+rp12)-rupiah)>90";
            $res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            $numrows=owlBaris($res);
            if($numrows>0){
                    $cap="Sebaran masih salah untuk data dibawah:\n";
                    while($bar=$res->fetch()){
                            $cap+="Noakun:".$bar->noakun." | Kegiatan:".$bar->kodeorg."\n";
                    }
                    exit(" Error: ".$cap);
            }
            //======================
            $sQl="select distinct tutup from ".$dbname.".bgt_budget where ".$where2." and tutup=1";
            $qQl=$owlPDO->query($sQl) or die(print " Gagal: ".PDOException::getMessage());
            $qQl->setFetchMode(PDO::FETCH_OBJ);
            $numrows=owlBaris($qQl);
            $row=$numrows;
            if($row!=1)
            {
                    $sUpdate="update ".$dbname.".bgt_budget set tutup=1 where ".$where2."";
                    try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
            }
            else
            {
                    exit("Error: Data has been closed");
            }
            break;
    case'getThnBudget':
            $optThnTtp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $sThn="select distinct tahunbudget from ".$dbname.".bgt_budget where kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' and tipebudget='ESTATE' and tutup=0 order by tahunbudget desc";
            $qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
            $qThn->setFetchMode(PDO::FETCH_ASSOC);
            while($rThn=$qThn->fetch())
            {
             $optThnTtp.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
            }
            echo $optThnTtp;
            break;
case 'sebarDoong':
        $var1=$_POST['var1'];
        $var2=$_POST['var2'];
        $var3=$_POST['var3'];
        $var4=$_POST['var4'];
        $var5=$_POST['var5'];
        $var6=$_POST['var6'];
        $var7=$_POST['var7'];
        $var8=$_POST['var8'];
        $var9=$_POST['var9'];
        $var10=$_POST['var10'];
        $var11=$_POST['var11'];
        $var12=$_POST['var12'];
        $rupiah=$_POST['rupe'];
        $fis=$_POST['fis'];
        $kunci=$_POST['kunci'];
        $str="delete from ".$dbname.".bgt_distribusi where kunci=".$kunci;
        $owlPDO->exec($str);
        $str="insert into ".$dbname.".bgt_distribusi (kunci, rp01, fis01, rp02, fis02, rp03, fis03, rp04, fis04, rp05, fis05, rp06, fis06, rp07, fis07, rp08, fis08, rp09, fis09, rp10, fis10, rp11, fis11, rp12, fis12, updateby)
              values(".$kunci.",
                       ".$var1*$rupiah.",
                       ".$var1*$fis.",
                       ".$var2*$rupiah.",
                       ".$var2*$fis.",
                       ".$var3*$rupiah.",
                       ".$var3*$fis.",
                       ".$var4*$rupiah.",
                       ".$var4*$fis.",
                       ".$var5*$rupiah.",
                       ".$var5*$fis.",
                       ".$var6*$rupiah.",
                       ".$var6*$fis.",
                       ".$var7*$rupiah.",
                       ".$var7*$fis.",
                       ".$var8*$rupiah.",
                       ".$var8*$fis.",
                       ".$var9*$rupiah.",
                       ".$var9*$fis.",
                       ".$var10*$rupiah.",
                       ".$var10*$fis.",
                       ".$var11*$rupiah.",
                       ".$var11*$fis.",
                       ".$var12*$rupiah.",
                       ".$var12*$fis.",
                       ".$_SESSION['standard']['userid'].");";
        try{
            $owlPDO->exec($str);
            $arr = array(
                    'rp01' => $var1*$rupiah,
                    'rp02' => $var2*$rupiah,
                    'rp03' => $var3*$rupiah,
                    'rp04' => $var4*$rupiah,
                    'rp05' => $var5*$rupiah,
                    'rp06' => $var6*$rupiah,
                    'rp07' => $var7*$rupiah,
                    'rp08' => $var8*$rupiah,
                    'rp09' => $var9*$rupiah,
                    'rp10' => $var10*$rupiah,
                    'rp11' => $var11*$rupiah,
                    'rp12' => $var12*$rupiah,
            );
            echo json_encode($arr);
        }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
 case 'getLuas':
        $blok=$_POST['blok'];
        $str="select distinct thntnm from ".$dbname.".bgt_blok where kodeblok like '".$blok."%' and thntnm>'0' order by thntnm asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		$luas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        while($bar=$res->fetch()){
			$luas.="<option value='".$bar['thntnm']."'>".$bar['thntnm']."</option>";
        }
        echo $luas;
        break;
    default:
    break;
}
?>