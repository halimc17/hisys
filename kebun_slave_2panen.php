<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=checkPostGet('pt','');
$gudang=checkPostGet('gudang','');
$afdeling=checkPostGet('afdeling','');
if(isset($_GET['proses']))
{
    $proses=$_GET['proses'];
}
else
{   
    $proses=$_POST['proses'];
}

switch($proses)
{
    case'getKbn':
		$optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN'
        and kodeorganisasi IN (".getOrgDetail(2).")";
		$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while($rKbn=$qKbn->fetch())
		{
			$optKebun.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
		}
		
		$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($pt!='')
		{
			$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN') and tipe='AFDELING'
            and kodeorganisasi IN (".getOrgDetail(26).")";
			$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
			$qKbn->setFetchMode(PDO::FETCH_ASSOC);
			while($rKbn=$qKbn->fetch())
			{
				$optAfd.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
			}
		}
		
		echo $optKebun."##".$optAfd;
    break;
	
	case'getAfd':
		$optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($gudang!='')
		{
			$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$gudang."' and tipe='AFDELING' and kodeorganisasi IN (".getOrgDetail(26).")";
		}
		else
		{
			$sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN') and tipe='AFDELING'
            and kodeorganisasi IN (".getOrgDetail(26).")";
		}
        // echo $sKbn;
		$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while($rKbn=$qKbn->fetch())
		{
			$optKebun.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
		}
		echo $optKebun;
    break;

    case'getAfd_4':
		$optKebun="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($gudang!='')
		{
            $sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$gudang."' and tipe='AFDELING' and kodeorganisasi IN (".getOrgDetail(26).")";
		}
		else
		{
            $sKbn="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN') and tipe='AFDELING'
            and kodeorganisasi IN (".getOrgDetail(26).")";
		}
        // echo $sKbn;
		$qKbn=$owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
		$qKbn->setFetchMode(PDO::FETCH_ASSOC);
		while($rKbn=$qKbn->fetch())
		{
            $optKebun.="<option value=".$rKbn['kodeorganisasi'].">".$rKbn['kodeorganisasi']." - ".$rKbn['namaorganisasi']."</option>";
		}
        
        $optblok="<option value=''>".$_SESSION['lang']['all']."</option>";
        if ($gudang != "") {
            $whereb=" and induk like '".$gudang."%'";
        }
        $sblok="SELECT indukblok,namaindukblok FROM $dbname.organisasi WHERE 1=1 ".$whereb." and tipe='BLOK' group by indukblok";
        $rblok=fetchData($sblok);
        foreach ($rblok as $qblok) {
            $optblok.="<option value='".$qblok['indukblok']."'>".$qblok['namaindukblok']."</option>";
        }
		echo $optKebun."##".$optblok;
    break;

    case 'getBlok':
        $optblok="<option value=''>".$_SESSION['lang']['all']."</option>";
        $whereb="";
        // if ($pt != "") {
        //     $whereb=" and induk in (
        //                 select kodeorganisasi from organisasi where induk in (
        //                 select kodeorganisasi from organisasi where induk='".$pt."' 
        //                 and kodeorganisasi in(".getOrgDetail(24).")
        //                 )
        //             )";
        // }
        // if ($gudang != "") {
        //     $whereb=" and induk like '".$gudang."%'";
        // }
        if ($afdeling != "") {
            $whereb=" and induk='".$afdeling."'";
        }
        $sblok="SELECT indukblok,namaindukblok FROM $dbname.organisasi WHERE 1=1 ".$whereb." and tipe='BLOK' group by indukblok";
        $rblok=fetchData($sblok);
        foreach ($rblok as $qblok) {
            $optblok.="<option value='".$qblok['indukblok']."'>".$qblok['namaindukblok']."</option>";
        }
        echo $optblok;
    break;
	
    case'getDetail':
    $kodeorg=$_GET['kodeorg'];
    $tgl=$_GET['tanggal'];
    $sKary="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".substr($kodeorg,0,4)."'";
    $qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
	$qKary->setFetchMode(PDO::FETCH_ASSOC);
	while($rKary=$qKary->fetch())
    {
        $rArrKary[$rKary['karyawanid']]=$rKary['namakaryawan'];
    }
    
    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }   

    echo"<link rel=stylesheet type=text/css href=style/".$gen.">
        <script language=javascript1.2 src='js/generic.js'></script>
        <script language=javascript1.2 src='js/kebun_panen.js'></script>";
    echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
    echo $_SESSION['lang']['unit'].":".$kodeorg."<br />";
    echo $_SESSION['lang']['tanggal'].":".tanggalnormal($tgl)."<br />";
    
    echo"<br /><img onclick=fisikKeExcel2(event,'kebun_slave_2panen.php') src=images/excel.jpg class=resicon title='MS.Excel'> ";
    echo"<input type='hidden' id='tanggal' value='".$tgl."' /><input type='hidden' id='kdOrg' value='".$kodeorg."' />
        <table class=sortable cellspacing=1 border=0>
        <thead>
        <tr class=rowheader>
        <th>No.</th>
        <th>".$_SESSION['lang']['notransaksi']."</th>
        <th>".$_SESSION['lang']['blok']."</th>
        <th>".$_SESSION['lang']['nikmandor']."</th>
        <th>".$_SESSION['lang']['namakaryawan']."</th>
        <th>".$_SESSION['lang']['basisjjg']."</th>
        <th>".$_SESSION['lang']['luas']."</th>
        <th>".$_SESSION['lang']['hasilkerja']."</th>
        <th>".$_SESSION['lang']['hasilkerjakg']."</th>
        <th>".$_SESSION['lang']['upahkerja']."</th>
        <th>".$_SESSION['lang']['upahpenalty']."</th>
        <th>".$_SESSION['lang']['premibasis']."</th>
        <th>".$_SESSION['lang']['upahpremi']."</th>
        <th>".$_SESSION['lang']['rupiahpenalty']."</th>
        </tr></thead><tbody>
            ";
    
    $sPrestasi="select a.*,b.tanggal,b.nikmandor from ".$dbname.".kebun_prestasi a 
        left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
            where a.kodeorg='".$kodeorg."' and b.tanggal='".$tgl."' and b.tipetransaksi='PNN'";
	$qPrestasi=$owlPDO->query($sPrestasi) or die(print " Gagal: ".PDOException::getMessage());
	$qPrestasi->setFetchMode(PDO::FETCH_ASSOC);
    $no=0;
	$totKerja=$totKerjakg=$totUpahKerja=$totUpahpenalty=$totPenalty=$totPremi=$totPremibasis=0;
    while($rPrestasi=$qPrestasi->fetch())
    {
        $no+=1;
        
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$rPrestasi['notransaksi']."</td>
            <td>".$rPrestasi['kodeorg']."</td>";
            if(!isset($tempNik) or $tempNik!=$rPrestasi['nikmandor'])
            {
                $brs=1;
            }
            if($brs==1)
            {
                  $tempNik=$rPrestasi['nikmandor'];
                  echo"<td>".$rArrKary[$rPrestasi['nikmandor']]."</td>";
                  $brs=0;
            }
            else
            {
                  echo"<td>&nbsp;</td>";
            }
            echo"<td>".$rArrKary[$rPrestasi['nik']]."</td>
            <td align=right>".number_format($rPrestasi['norma'],2)."</td>
            <td align=right>".number_format($rPrestasi['luaspanen'],2)."</td>
            <td align=right>".number_format($rPrestasi['hasilkerja'],2)."</td>
            <td align=right>".number_format($rPrestasi['hasilkerjakg'],2)."</td>
            <td align=right>".number_format($rPrestasi['upahkerja'],2)."</td>
            <td align=right>".number_format($rPrestasi['upahpenalty'],2)."</td>
            <td align=right>".number_format($rPrestasi['premibasis'],2)."</td>
            <td align=right>".number_format($rPrestasi['upahpremi'],2)."</td>
            <td align=right>".number_format($rPrestasi['rupiahpenalty'],2)."</td>
            </tr>";
			$totKerja+=$rPrestasi['hasilkerja'];
            $totKerjakg+=$rPrestasi['hasilkerjakg'];
            $totUpahKerja+=$rPrestasi['upahkerja'];
            $totUpahpenalty+=$rPrestasi['upahpenalty'];
            $totPenalty+=$rPrestasi['rupiahpenalty'];
            $totPremi+=$rPrestasi['upahpremi'];
            $totPremibasis+=$rPrestasi['premibasis'];
    }
    echo"<tr class=rowcontent><td colspan=7>Total</td><td align=right>".number_format($totKerja,2)."</td>
        <td align=right>".number_format($totKerjakg,2)."</td><td align=right>".number_format($totUpahKerja,2)."</td>
        <td align=right>".number_format($totUpahpenalty,2)."</td><td align=right>".number_format($totPremibasis,2)."</td>
        <td align=right>".number_format($totPremi,2)."</td><td align=right>".number_format($totPenalty,2)."</td></tr>";
    echo"</tbody></table></fieldset>";
    break;
    
    case'getDetailDenda':
    $kodeorg=$_GET['kodeorg'];
    $tgl=$_GET['tanggal'];
    $sKary="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".substr($kodeorg,0,4)."'";
	$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
	$qKary->setFetchMode(PDO::FETCH_ASSOC);
    while($rKary=$qKary->fetch())
    {
        $rArrKary[$rKary['karyawanid']]=$rKary['namakaryawan'];
    }
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }   

    echo"<link rel=stylesheet type=text/css href=style/".$gen.">
        <script language=javascript1.2 src='js/generic.js'></script>
        <script language=javascript1.2 src='js/kebun_panen.js'></script>";
    echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
    echo $_SESSION['lang']['unit'].":".$kodeorg."<br />";
    echo $_SESSION['lang']['tanggal'].":".tanggalnormal($tgl)."<br />";
    
    echo"<br /><img onclick=fisikKeExcel2Denda(event,'kebun_slave_2panen.php') src=images/excel.jpg class=resicon title='MS.Excel'> ";
    echo"<input type='hidden' id='tanggal' value='".$tgl."' /><input type='hidden' id='kdOrg' value='".$kodeorg."' />
        <table class=sortable cellpadding=1 border=0>
        <thead>
        <tr class=rowheader>
        <th>No.</th>
        <th>".$_SESSION['lang']['notransaksi']."</th>
        <th>".$_SESSION['lang']['blok']."</th>
        <th>".$_SESSION['lang']['nikmandor']."</th>
        <th>".$_SESSION['lang']['namakaryawan']."</th>
        <th>".$_SESSION['lang']['rupiahpenalty']."</th>
        <th>".$_SESSION['lang']['penalti1']."</th>
        <th>".$_SESSION['lang']['penalti2']."</th>
        <th>".$_SESSION['lang']['penalti3']."</th>
        <th>".$_SESSION['lang']['penalti4']."</th>
        <th>".$_SESSION['lang']['penalti5']."</th>
        <th>".$_SESSION['lang']['penalti6']."</th>
        <th>".$_SESSION['lang']['penalti7']."</th>
        <th>".$_SESSION['lang']['penalti8']."</th>
        <th>".$_SESSION['lang']['penalti9']."</th>
        <th>".$_SESSION['lang']['penalti10']."</th>
        </tr></thead><tbody>
            ";
    
    $sPrestasi="select a.*,b.tanggal,b.nikmandor from ".$dbname.".kebun_prestasi a 
        left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
            where a.rupiahpenalty>0 and a.kodeorg='".$kodeorg."' and b.tanggal='".$tgl."' and b.tipetransaksi='PNN'";
	$qPrestasi=$owlPDO->query($sPrestasi) or die(print " Gagal: ".PDOException::getMessage());
	$qPrestasi->setFetchMode(PDO::FETCH_ASSOC);
    while($rPrestasi=$qPrestasi->fetch())
    {
        $no+=1;
        
        echo"<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$rPrestasi['notransaksi']."</td>
            <td>".$rPrestasi['kodeorg']."</td>";
            if($tempNik!=$rPrestasi['nikmandor'])
            {
                $brs=1;
            }
            if($brs==1)
            {
                  $tempNik=$rPrestasi['nikmandor'];
                  echo"<td>".$rArrKary[$rPrestasi['nikmandor']]."</td>";
                  $brs=0;
            }
            else
            {
                  echo"<td>&nbsp;</td>";
            }
            echo"<td>".$rArrKary[$rPrestasi['nik']]."</td>
            <td align=right>".number_format($rPrestasi['rupiahpenalty'])."</td>
            <td align=right>".number_format($rPrestasi['penalti1'])."</td>
            <td align=right>".number_format($rPrestasi['penalti2'])."</td>
            <td align=right>".number_format($rPrestasi['penalti3'])."</td>
            <td align=right>".number_format($rPrestasi['penalti4'])."</td>
            <td align=right>".number_format($rPrestasi['penalti5'])."</td>
            <td align=right>".number_format($rPrestasi['penalti6'])."</td>
            <td align=right>".number_format($rPrestasi['penalti7'])."</td>
            <td align=right>".number_format($rPrestasi['penalti8'])."</td>
            <td align=right>".number_format($rPrestasi['penalti9'])."</td>
            <td align=right>".number_format($rPrestasi['penalti10'])."</td>
            </tr>";
            $rupiahpenalty+=$rPrestasi['rupiahpenalty'];
            $penalti1+=$rPrestasi['penalti1'];
            $penalti2+=$rPrestasi['penalti2'];
            $penalti3+=$rPrestasi['penalti3'];
            $penalti4+=$rPrestasi['penalti4'];
            $penalti5+=$rPrestasi['penalti5'];
            $penalti6+=$rPrestasi['penalti6'];
            $penalti7+=$rPrestasi['penalti7'];
            $penalti8+=$rPrestasi['penalti8'];
            $penalti9+=$rPrestasi['penalti9'];
            $penalti10+=$rPrestasi['penalti10'];
    }
    echo"<tr class=rowcontent><td colspan=5>Total</td><td align=right>".number_format($rupiahpenalty)."</td>
        <td align=right>".number_format($penalti1)."</td>
        <td align=right>".number_format($penalti2)."</td>
        <td align=right>".number_format($penalti3)."</td>
        <td align=right>".number_format($penalti4)."</td>
        <td align=right>".number_format($penalti5)."</td>
        <td align=right>".number_format($penalti6)."</td>
        <td align=right>".number_format($penalti7)."</td>
        <td align=right>".number_format($penalti8)."</td>
        <td align=right>".number_format($penalti9)."</td>
        <td align=right>".number_format($penalti10)."</td>
        </tr>";
    echo"</tbody></table></fieldset>";
    break;
    
    case'getDetailTotal':
    $kodeorg=$_GET['kodeorg'];
    $gudang=$_GET['gudang'];
    $tgl1=$_GET['tgl1'];
    $tgl2=$_GET['tgl2'];
    
    $sKary="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".substr($gudang,0,4)."'";
	$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
	$qKary->setFetchMode(PDO::FETCH_ASSOC);
    while($rKary=$qKary->fetch())
    {
        $rArrKary[$rKary['karyawanid']]=$rKary['namakaryawan'];
    }
        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }       
    echo"<link rel=stylesheet type=text/css href=style/".$gen.">
        <script language=javascript1.2 src='js/generic.js'></script>
        <script language=javascript1.2 src='js/kebun_panen.js'></script>";
    echo"<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
    echo $_SESSION['lang']['unit'].":".$gudang."<br />";
    echo $_SESSION['lang']['tanggal'].":".$tgl1." s/d ".$tgl2."<br />";
    
    echo"<br /><img onclick=fisikKeExcel3(event,'kebun_slave_2panen.php') src=images/excel.jpg class=resicon title='MS.Excel'> ";
    echo"<input type='hidden' id='tgl1' value='".$tgl1."' /><input type='hidden' id='tgl2' value='".$tgl2."' />
         <input type='hidden' id='gudang' value='".$gudang."' />
        <table class=sortable cellpadding=1 border=0>
        <thead>
        <tr class=rowheader>
        <th>No.</th>
        <th>".$_SESSION['lang']['notransaksi']."</th>
        <th>".$_SESSION['lang']['blok']."</th>
        <th>".$_SESSION['lang']['nikmandor']."</th>
        <th>".$_SESSION['lang']['namakaryawan']."</th>
        <th>".$_SESSION['lang']['basisjjg']."</th>
        <th>".$_SESSION['lang']['luas']."</th>    
        <th>".$_SESSION['lang']['hasilkerja']."</th>
        <th>".$_SESSION['lang']['hasilkerjakg']."</th>
        <th>".$_SESSION['lang']['upahkerja']."</th>
        <th>".$_SESSION['lang']['upahpenalty']."</th>
        <th>".$_SESSION['lang']['premibasis']."</th>
        <th>".$_SESSION['lang']['upahpremi']."</th>
        <th>".$_SESSION['lang']['rupiahpenalty']."</th>
        </tr></thead><tbody>
            ";
    
        $where='';
        if($gudang != $_SESSION['empl']['lokasitugas']){                
            $where=" and b.jurnal=1";
        }
        
        $str="select a.norma as norma,a.luaspanen as luaspanen,a.notransaksi as notransaksi,a.kodeorg as kodeorg,
              b.nikmandor as nikmandor,
              a.hasilkerja as hasilkerja,hasilkerjakg as hasilkerjakg,a.karyawanid as nik,
              a.upahkerja as upahkerja,a.upahpenalty as upahpenalty, a.premibasis as premibasis,
              a.upahpremi as upahpremi,a.rupiahpenalty as rupiahpenalty
              from ".$dbname.".kebun_prestasi_vw a
              left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
              where a.unit = '".$gudang."'  and b.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." 
              ".$where."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrows=owlBaris($res);
    $no=0;
    if($numrows<1)
    {
            echo"<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    else
    {
        while($bar=$res->fetch())
        {
           $no+=1;
            echo"<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$bar['notransaksi']."</td>
                <td>".$bar['kodeorg']."</td>";
                if($tempNik!=$bar['nikmandor'])
                {
                    $brs=1;
                }
                if($brs==1)
                {
                      $tempNik=$bar['nikmandor'];
                      echo"<td>".$rArrKary[$bar['nikmandor']]."</td>";
                      $brs=0;
                }
                else
                {
                      echo"<td>&nbsp;</td>";
                }
                echo"<td>".$rArrKary[$bar['nik']]."</td>
                <td align=right>".number_format($bar['norma'],2)."</td>
                <td align=right>".number_format($bar['luaspanen'],2)."</td>
                <td align=right>".number_format($bar['hasilkerja'],2)."</td>
                <td align=right>".number_format($bar['hasilkerjakg'],2)."</td>
                <td align=right>".number_format($bar['upahkerja'],2)."</td>
                <td align=right>".number_format($bar['upahpenalty'],2)."</td>
                <td align=right>".number_format($bar['premibasis'],2)."</td>
                <td align=right>".number_format($bar['upahpremi'],2)."</td>
                <td align=right>".number_format($bar['rupiahpenalty'],2)."</td>
                </tr>";
                $totKerja+=$bar['hasilkerja'];
                $totKerjakg+=$bar['hasilkerjakg'];
                $totUpahKerja+=$bar['upahkerja'];
                $totUpahpenalty+=$bar['upahpenalty'];
                $totPenalty+=$bar['rupiahpenalty'];
                $totPremi+=$bar['upahpremi'];
                $totPremibasis+=$bar['premibasis'];
        }

        echo"<tr class=rowcontent><td colspan=7 align=center>Total</td><td align=right>".number_format($totKerja,2)."</td>
            <td align=right>".number_format($totKerjakg,2)."</td><td align=right>".number_format($totUpahKerja,2)."</td>
            <td align=right>".number_format($totUpahpenalty,2)."</td><td align=right>".number_format($totPremibasis,2)."</td>
            <td align=right>".number_format($totPremi,2)."</td><td align=right>".number_format($totPenalty,2)."</td></tr>";
        echo"</tbody></table></fieldset>";
    }
    
    break;
    
    case'excelDetailDenda':
        $kodeorg=$_GET['kdOrg'];
        $tgl=$_GET['tgl'];
        $sKary="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".substr($kodeorg,0,4)."'";
		$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
		$qKary->setFetchMode(PDO::FETCH_ASSOC);
        while($rKary=$qKary->fetch())
        {
            $rArrKary[$rKary['karyawanid']]=$rKary['namakaryawan'];
        }
        $tab.="
        <table class=sortable cellpadding=1 border=1>
        <thead>
        <tr class=rowheader>
        <td bgcolor=#DEDEDE align=center>No.</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['blok']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nikmandor']."</td>    
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['rupiahpenalty']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti1']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti2']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti3']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti4']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti5']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti6']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti7']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti8']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti9']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['penalti10']."</td>
        </tr></thead><tbody>
            ";
    
    $sPrestasi="select a.*,b.tanggal,b.nikmandor from ".$dbname.".kebun_prestasi a 
        left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
            where a.rupiahpenalty>0 and a.kodeorg='".$kodeorg."' and b.tanggal='".$tgl."' and b.tipetransaksi='PNN'";
	$qPrestasi=$owlPDO->query($sPrestasi) or die(print " Gagal: ".PDOException::getMessage());
	$qPrestasi->setFetchMode(PDO::FETCH_ASSOC);
    while($rPrestasi=$qPrestasi->fetch())
    {
        $no+=1;
        
        $tab.="<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$rPrestasi['notransaksi']."</td>
            <td>".$rPrestasi['kodeorg']."</td>";
            if($tempNik!=$rPrestasi['nikmandor'])
            {
                $brs=1;
            }
            if($brs==1)
            {
                  $tempNik=$rPrestasi['nikmandor'];
                  $tab.="<td>".$rArrKary[$rPrestasi['nikmandor']]."</td>";
                  $brs=0;
            }
            else
            {
                  $tab.="<td>&nbsp;</td>";
            }
            $tab.="
            <td>".$rArrKary[$rPrestasi['nik']]."</td>
            <td align=right>".number_format($rPrestasi['rupiahpenalty'])."</td>
            <td align=right>".number_format($rPrestasi['penalti1'])."</td>
            <td align=right>".number_format($rPrestasi['penalti2'])."</td>
            <td align=right>".number_format($rPrestasi['penalti3'])."</td>
            <td align=right>".number_format($rPrestasi['penalti4'])."</td>
            <td align=right>".number_format($rPrestasi['penalti5'])."</td>
            <td align=right>".number_format($rPrestasi['penalti6'])."</td>
            <td align=right>".number_format($rPrestasi['penalti7'])."</td>
            <td align=right>".number_format($rPrestasi['penalti8'])."</td>
            <td align=right>".number_format($rPrestasi['penalti9'])."</td>
            <td align=right>".number_format($rPrestasi['penalti10'])."</td>
            </tr>";
            $rupiahpenalty+=$rPrestasi['rupiahpenalty'];
            $penalti1+=$rPrestasi['penalti1'];
            $penalti2+=$rPrestasi['penalti2'];
            $penalti3+=$rPrestasi['penalti3'];
            $penalti4+=$rPrestasi['penalti4'];
            $penalti5+=$rPrestasi['penalti5'];
            $penalti6+=$rPrestasi['penalti6'];
            $penalti7+=$rPrestasi['penalti7'];
            $penalti8+=$rPrestasi['penalti8'];
            $penalti9+=$rPrestasi['penalti9'];
            $penalti10+=$rPrestasi['penalti10'];
    }
    $tab.="<tr class=rowcontent><td colspan=5>Total</td><td align=right>".number_format($rupiahpenalty)."</td>
        <td align=right>".number_format($penalti1)."</td>
        <td align=right>".number_format($penalti2)."</td>
        <td align=right>".number_format($penalti3)."</td>
        <td align=right>".number_format($penalti4)."</td>
        <td align=right>".number_format($penalti5)."</td>
        <td align=right>".number_format($penalti6)."</td>
        <td align=right>".number_format($penalti7)."</td>
        <td align=right>".number_format($penalti8)."</td>
        <td align=right>".number_format($penalti9)."</td>
        <td align=right>".number_format($penalti10)."</td>
        </tr>";
    $tab.="</tbody>";
			
			//echo "warning:".$strx;
			//=================================================

			
			$tab.="</table>Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
			
			$nop_="laporanPanenDetailDenda_".$kodeorg."_".$tgl;
			if(strlen($tab)>0)
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
			if(!fwrite($handle,$tab))
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
			//closedir($handle);
			}
    break;    
    
    case'excelDetail':
        $kodeorg=$_GET['kdOrg'];
        $tgl=$_GET['tgl'];
		$tempNik="";
        $sKary="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".substr($kodeorg,0,4)."'";
		$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
		$qKary->setFetchMode(PDO::FETCH_ASSOC);
        while($rKary=$qKary->fetch())
        {
            $rArrKary[$rKary['karyawanid']]=$rKary['namakaryawan'];
        }
        $tab.="
        <table class=sortable cellpadding=1 border=1>
        <thead>
        <tr class=rowheader>
        <td bgcolor=#DEDEDE align=center>No.</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['blok']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nikmandor']."</td>    
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['hasilkerja']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['hasilkerjakg']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahkerja']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahpenalty']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['premibasis']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahpremi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['rupiahpenalty']."</td>
        </tr></thead><tbody>
            ";
    
    $sPrestasi="select a.*,b.tanggal,b.nikmandor from ".$dbname.".kebun_prestasi a 
        left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
            where a.kodeorg='".$kodeorg."' and b.tanggal='".$tgl."' and b.tipetransaksi='PNN'";
    $qPrestasi=$owlPDO->query($sPrestasi) or die(print " Gagal: ".PDOException::getMessage());
	$qPrestasi->setFetchMode(PDO::FETCH_ASSOC);
    while($rPrestasi=$qPrestasi->fetch())
    {
        $no+=1;
        
        $tab.="<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$rPrestasi['notransaksi']."</td>
            <td>".$rPrestasi['kodeorg']."</td>";
            if($tempNik!=$rPrestasi['nikmandor'])
            {
                $brs=1;
            }
            if($brs==1)
            {
                  $tempNik=$rPrestasi['nikmandor'];
                  $tab.="<td>".$rArrKary[$rPrestasi['nikmandor']]."</td>";
                  $brs=0;
            }
            else
            {
                  $tab.="<td>&nbsp;</td>";
            }
            $tab.="
            <td>".$rArrKary[$rPrestasi['nik']]."</td>
            <td align=right>".number_format($rPrestasi['hasilkerja'],2)."</td>
            <td align=right>".number_format($rPrestasi['hasilkerjakg'],2)."</td>
            <td align=right>".number_format($rPrestasi['upahkerja'],2)."</td>
            <td align=right>".number_format($rPrestasi['upahpenalty'],2)."</td>
            <td align=right>".number_format($rPrestasi['premibasis'],2)."</td>
            <td align=right>".number_format($rPrestasi['upahpremi'],2)."</td>
            <td align=right>".number_format($rPrestasi['rupiahpenalty'],2)."</td>
            </tr>";
            @$totKerja+=$rPrestasi['hasilkerja'];
            @$totKerjakg+=$rPrestasi['hasilkerjakg'];
            @$totUpahKerja+=$rPrestasi['upahkerja'];
            @$totPenalty+=$rPrestasi['rupiahpenalty'];
            @$totPremi+=$rPrestasi['upahpremi'];
            @$totUpahpenalty+=$rPrestasi['upahpenalty'];
            @$totPremibasis+=$rPrestasi['premibasis'];
    }
    $tab.="<tr class=rowcontent><td colspan=5>Total</td><td align=right>".number_format($totKerja,2)."</td>
        <td align=right>".number_format($totKerjakg,2)."</td><td align=right>".number_format($totUpahKerja,2)."</td>
        <td align=right>".number_format($totUpahpenalty,2)."</td><td align=right>".number_format($totPremibasis,2)."</td>
        <td align=right>".number_format($totPremi,2)."</td><td align=right>".number_format($totPenalty,2)."</td></tr>";
    $tab.="</tbody>";
			
			//echo "warning:".$strx;
			//=================================================

			
			$tab.="</table>Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
			
			$nop_="laporanPanenDetail_".$kodeorg."_".$tgl;
			if(strlen($tab)>0)
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
			if(!fwrite($handle,$tab))
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
			//closedir($handle);
			}
    break;
    case'excelDetailTotal':
        $kodeorg=$_GET['kodeorg'];
    $gudang=$_GET['gudang'];
    $tgl1=$_GET['tgl1'];
    $tgl2=$_GET['tgl2'];
    
    $sKary="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".substr($gudang,0,4)."'";
	$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
	$qKary->setFetchMode(PDO::FETCH_ASSOC);
    while($rKary=$qKary->fetch())
    {
        $rArrKary[$rKary['karyawanid']]=$rKary['namakaryawan'];
    }
    
        $tab.="
        <table class=sortable cellpadding=1 border=1>
        <thead>
        <tr class=rowheader>
        <td bgcolor=#DEDEDE align=center>No.</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['blok']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nikmandor']."</td>    
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakaryawan']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['basisjjg']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['luas']."</td>            
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['hasilkerja']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['hasilkerjakg']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahkerja']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahpenalty']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['premibasis']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['upahpremi']."</td>
        <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['rupiahpenalty']."</td>
        </tr></thead><tbody>
            ";
    
        $where='';
        if($gudang != $_SESSION['empl']['lokasitugas']){                
            $where=" and b.jurnal=1";
        }
        
        $str="select a.norma as norma, a.luaspanen as luaspanen,a.notransaksi as notransaksi,a.kodeorg as kodeorg,
              b.nikmandor as nikmandor,
              a.hasilkerja as hasilkerja,hasilkerjakg as hasilkerjakg,a.karyawanid as nik,
              a.upahkerja as upahkerja,a.upahpenalty as upahpenalty, a.premibasis as premibasis,
              a.upahpremi as upahpremi,a.rupiahpenalty as rupiahpenalty
              from ".$dbname.".kebun_prestasi_vw a
              left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
              where a.unit = '".$gudang."'  and b.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." 
              ".$where."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrows=owlBaris($res);
    $no=0;
    if($numrows<1)
    {
		echo"<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }
    else
    {
        while($bar=$res->fetch())
        {
           $no+=1;
            $tab.="<tr class=rowcontent>
                <td>".$no."</td>
                <td>".$bar['notransaksi']."</td>
                <td>".$bar['kodeorg']."</td>";
                if($tempNik!=$bar['nikmandor'])
                {
                    $brs=1;
                }
                if($brs==1)
                {
                      $tempNik=$bar['nikmandor'];
                      $tab.="<td>".$rArrKary[$bar['nikmandor']]."</td>";
                      $brs=0;
                }
                else
                {
                      $tab.="<td>&nbsp;</td>";
                }
                $tab.="<td>".$rArrKary[$bar['nik']]."</td>
                <td align=right>".number_format($bar['norma'],2)."</td>
                <td align=right>".number_format($bar['luaspanen'],2)."</td>                    
                <td align=right>".number_format($bar['hasilkerja'],2)."</td>
                <td align=right>".number_format($bar['hasilkerjakg'],2)."</td>
                <td align=right>".number_format($bar['upahkerja'],2)."</td>
                <td align=right>".number_format($bar['upahpenalty'],2)."</td>
                <td align=right>".number_format($bar['premibasis'],2)."</td>
                <td align=right>".number_format($bar['upahpremi'],2)."</td>
                <td align=right>".number_format($bar['rupiahpenalty'],2)."</td>
                </tr>";
                $totKerja+=$bar['hasilkerja'];
                $totKerjakg+=$bar['hasilkerjakg'];
                $totUpahKerja+=$bar['upahkerja'];
                $totUpahpenalty+=$bar['upahpenalty'];
                $totPenalty+=$bar['rupiahpenalty'];
                $totPremi+=$bar['upahpremi'];
                $totPremibasis+=$bar['premibasis'];
        }

        $tab.="<tr class=rowcontent><td colspan=7 align=center>Total</td><td align=right>".number_format($totKerja,2)."</td>
            <td align=right>".number_format($totKerjakg,2)."</td><td align=right>".number_format($totUpahKerja,2)."</td>
            <td align=right>".number_format($totUpahpenalty,2)."</td><td align=right>".number_format($totPremibasis,2)."</td>
            <td align=right>".number_format($totPremi,2)."</td><td align=right>".number_format($totPenalty,2)."</td></tr>";
        $tab.="</tbody></table></fieldset>";
    }
    
    
			
			//echo "warning:".$strx;
			//=================================================

			
			$tab.="</table>Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
			
			$nop_="laporanPanenDetail_".$kodeorg."_".$tgl;
			if(strlen($tab)>0)
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
			if(!fwrite($handle,$tab))
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
			//closedir($handle);
			}
    break;
    default:
        break;
}
//if(isset($_POST['proses']))//=="getKbn")
//{
//    
//}
//if(isset($_POST['proses'])=="getDetail")
//{
//    echo"warning:masuk";
//}
?>
