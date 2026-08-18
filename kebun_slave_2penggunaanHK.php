<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');
$kodeOrg = checkPostGet('kdUnit','');
$periode = checkPostGet('periode','');
$intiplasma=checkPostGet('intiplasma','');
$divisi=checkPostGet('divisi','');


$thnBudget='';
$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$where=" kodeunit='".$kodeOrg."' and tahunbudget='".$thnBudget."'";
$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
if($_SESSION['language']=='EN'){
    $zz="namakegiatan1";
}else{
    $zz="namakegiatan1";
}
$optNmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,'.$zz);
$optSatkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
if($kodeOrg==''||$periode=='')
{
    exit("Error: All field required");
}
$bln=explode("-",$periode);
$blnLalu=intval($bln[1]);
if($blnLalu==1)
{
    $blnLalu=12;
    $thnLalu=intval($bln[0])-1;
}
else
{
    $blnLalu=$blnLalu-1;
    if($blnLalu<10)
    {
        $blnLalu="0".$blnLalu;
    }
    $thnLalu=intval($bln[0]);
}


if($divisi!='' && $intiplasma!='')
{
	$whrip.=" and kodeorg in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$intiplasma."' and kodeorg like '".$divisi."%') ";
}

else if($intiplasma!='')
{
	$whrip.=" and kodeorg in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$intiplasma."' and kodeorg like '".$kodeOrg."%') ";
}
else if($divisi!='')
{
	$whrip.=" and kodeorg like '".$divisi."%' ";
} else{
	$whrip.=" and kodeorg like '".$kodeOrg."%' ";
}


 $arrDtBlnLalu=array();
//hasil kerja,perawatan....
$hslBlnIni="select kodeorg,kodekegiatan,namakegiatan,SUM(hasilkerja) as hasilkerja from ".$dbname.".kebun_perawatan_dan_spk_vw 
           where tanggal like '".$periode."%' ".$whrip." group by kodeorg,kodekegiatan
           order by kodekegiatan asc";
// echo $hslBlnIni;
$qhslBlnIni=$owlPDO->query($hslBlnIni) or die(print " Gagal: ".PDOException::getMessage());
$qhslBlnIni->setFetchMode(PDO::FETCH_ASSOC);
while($rhslBlnini=$qhslBlnIni->fetch())
{
    if($rhslBlnini['kodekegiatan']!='')
    {
        
        $cekKeg[$rhslBlnini['kodekegiatan']][$rhslBlnini['kodeorg']]=0;
        if($rhslBlnini['hasilkerja']!=0||$rhslBlnini['hasilkerja']!='')
        {
			setIt($jmlh[$rhslBlnini['kodekegiatan']],0);
            $jmlh[$rhslBlnini['kodekegiatan']]+=1;
            $cekKeg[$rhslBlnini['kodekegiatan']][$rhslBlnini['kodeorg']]=1;
            $arrBlok[$rhslBlnini['kodeorg']]=$rhslBlnini['kodeorg'];
            $arrKeg[$rhslBlnini['kodekegiatan']]=$rhslBlnini['kodekegiatan'];
            $arrDtBlnIni[$rhslBlnini['kodekegiatan']][$rhslBlnini['kodeorg']]=$rhslBlnini['hasilkerja'];
        }
    }
}

// hk bln ini
$shkBlnIni="select kodeorg, kodekegiatan,tipekaryawan,sum(jhk) as jhk from ".$dbname.".kebun_kehadiran_vw 
           where tanggal like '".$periode."%' ".$whrip." group by kodeorg,kodekegiatan,tipekaryawan
           order by kodekegiatan asc";
$qhkBlnIni=$owlPDO->query($shkBlnIni) or die(print " Gagal: ".PDOException::getMessage());
$qhkBlnIni->setFetchMode(PDO::FETCH_ASSOC);
while($rhkBlnini=$qhkBlnIni->fetch())
{
    $cekHk[$rhkBlnini['kodekegiatan']][$rhkBlnini['kodeorg']]=0;
    if($rhkBlnini['kodekegiatan']!='')
    {
		setIt($arrHk[$rhkBlnini['kodekegiatan'].$rhkBlnini['kodeorg']][$rhkBlnini['tipekaryawan']],0);
		$cekHk[$rhkBlnini['kodekegiatan']][$rhkBlnini['kodeorg']]=1;
		$arrBlok[$rhkBlnini['kodeorg']]=$rhkBlnini['kodeorg'];
		$arrKeg[$rhkBlnini['kodekegiatan']]=$rhkBlnini['kodekegiatan'];
		$arrHk[$rhkBlnini['kodekegiatan'].$rhkBlnini['kodeorg']][$rhkBlnini['tipekaryawan']]+=$rhkBlnini['jhk'];
		//$arrHk[$rhkBlnini['kodekegiatan'].$rhkBlnini['kodeorg']][$rhkBlnini['tipekaryawan']]+=$rhkBlnini['jhk'];    
    }
}

//hasil bulan lalu
$sHslBlnLalu="select kodeorg,kodekegiatan,namakegiatan,sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_perawatan_dan_spk_vw 
              where tanggal like '".$thnLalu."-".$blnLalu."%' ".$whrip." group by kodeorg,kodekegiatan
              order by kodekegiatan asc";
//echo $sHslBlnLalu;
$qHslBlnLalu=$owlPDO->query($sHslBlnLalu) or die(print " Gagal: ".PDOException::getMessage());
$qHslBlnLalu->setFetchMode(PDO::FETCH_ASSOC);
while($rHslBlnLalu=$qHslBlnLalu->fetch())
{
    if($rHslBlnLalu['kodekegiatan']!='')
    {
        
        $cekKeg[$rHslBlnLalu['kodekegiatan']][$rHslBlnLalu['kodeorg']]=0;
        if($rHslBlnLalu['hasilkerja']!=0||$rHslBlnLalu['hasilkerja']!='')
        {
            $cekKeg[$rHslBlnLalu['kodekegiatan']][$rHslBlnLalu['kodeorg']]=1;
            //$jmlh[$rHslBlnLalu['kodekegiatan']]+=1;
            $arrBlok[$rHslBlnLalu['kodeorg']]=$rHslBlnLalu['kodeorg'];
            $arrKeg[$rHslBlnLalu['kodekegiatan']]=$rHslBlnLalu['kodekegiatan'];

            $arrHslKrjBlnLalu[$rHslBlnLalu['kodekegiatan']][$rHslBlnLalu['kodeorg']]=$rHslBlnLalu['hasilkerja'];
        }
    }
}

// hk bln lalu
$shkBlnLalu="select kodeorg, kodekegiatan,tipekaryawan,sum(jhk) as jhk from ".$dbname.".kebun_kehadiran_vw 
            where tanggal like '".$thnLalu."-".$blnLalu."%' ".$whrip." group by kodeorg,kodekegiatan,tipekaryawan
            order by kodekegiatan asc";
$qhkBlnLalu=$owlPDO->query($shkBlnLalu) or die(print " Gagal: ".PDOException::getMessage());
$qhkBlnLalu->setFetchMode(PDO::FETCH_ASSOC);
while($rhkBlnLalu=$qhkBlnLalu->fetch())
{
    $cekHk[$rhkBlnLalu['kodekegiatan']][$rhkBlnLalu['kodeorg']]=0;
    if($rhkBlnLalu['kodekegiatan']!='')
    {
    $cekHk[$rhkBlnLalu['kodekegiatan']][$rhkBlnLalu['kodeorg']]=1;
    $arrBlok[$rhkBlnLalu['kodeorg']]=$rhkBlnLalu['kodeorg'];
    $arrKeg[$rhkBlnLalu['kodekegiatan']]=$rhkBlnLalu['kodekegiatan'];
	setIt($arrHkLalu[$rhkBlnLalu['kodekegiatan'].$rhkBlnLalu['kodeorg']][$rhkBlnLalu['tipekaryawan']],0);
    $arrHkLalu[$rhkBlnLalu['kodekegiatan'].$rhkBlnLalu['kodeorg']][$rhkBlnLalu['tipekaryawan']]+=$rhkBlnLalu['jhk'];
    }
}

//panen
//hasil kerja panen
$sPanenHslKrj="select kodeorg,sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_prestasi_vw 
               where kodeorg like '%".$kodeOrg."%' ".$whrip." and tanggal like '%".$periode."%' group by kodeorg ";
// echo $sPanenHslKrj;
$qPanenHslKrj=$owlPDO->query($sPanenHslKrj) or die(print " Gagal: ".PDOException::getMessage());
$qPanenHslKrj->setFetchMode(PDO::FETCH_ASSOC);
while($rPanenHslKrj=$qPanenHslKrj->fetch())
{
    $panenHslBln[$rPanenHslKrj['kodeorg']]=$rPanenHslKrj['hasilkerja'];
    $lstKodeorg[$rPanenHslKrj['kodeorg']]=$rPanenHslKrj['kodeorg'];
}

//hk bulan ini
$sHkPanenBlnIni="select kodeorg,tipekaryawan, sum(hkpanenperhari) as hasilkerja from ".$dbname.".kebun_prestasi_vs_hk where 1=1 ".$whrip." and tanggal like '%".$periode."%' group by kodeorg,tipekaryawan";
//echo $sHkPanenBlnIni;
$qHkPanenBlnIni=$owlPDO->query($sHkPanenBlnIni) or die(print " Gagal: ".PDOException::getMessage());
$qHkPanenBlnIni->setFetchMode(PDO::FETCH_ASSOC);
while($rHkPanenBlnIni=$qHkPanenBlnIni->fetch())
{
    $lstKodeorg[$rHkPanenBlnIni['kodeorg']]=$rHkPanenBlnIni['kodeorg'];
	setIt($hkBln[$rHkPanenBlnIni['kodeorg']][$rHkPanenBlnIni['tipekaryawan']],0);
    $hkBln[$rHkPanenBlnIni['kodeorg']][$rHkPanenBlnIni['tipekaryawan']]+=$rHkPanenBlnIni['hasilkerja'];
}

//hasil panen bln lalu
$sPanenBlnLalu="select kodeorg,sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_prestasi_vw 
                where  1=1 ".$whrip." and tanggal like '".$thnLalu."-".$blnLalu."%' group by kodeorg";
$qPanenBlnLalu=$owlPDO->query($sPanenBlnLalu) or die(print " Gagal: ".PDOException::getMessage());
$qPanenBlnLalu->setFetchMode(PDO::FETCH_ASSOC);
while($rPanenBlnLalu=$qPanenBlnLalu->fetch())
{
    $lstKodeorg[$rPanenBlnLalu['kodeorg']]=$rPanenBlnLalu['kodeorg'];
    $panenHslBlnLalu[$rPanenBlnLalu['kodeorg']]=$rPanenBlnLalu['hasilkerja'];
}

//hk bulan lalu
$sHkBlnLalu="select kodeorg,tipekaryawan, sum(hkpanenperhari) as hasilkerja from ".$dbname.".kebun_prestasi_vs_hk 
             where  1=1 ".$whrip." and tanggal like '".$thnLalu."-".$blnLalu."%' group by kodeorg,tipekaryawan";
//echo $sHkBlnLalu;
$qHkBlnLalu=$owlPDO->query($sHkBlnLalu) or die(print " Gagal: ".PDOException::getMessage());
$qHkBlnLalu->setFetchMode(PDO::FETCH_ASSOC);
while($rHkBlnLalu=$qHkBlnLalu->fetch())
{
    $lstKodeorg[$rHkBlnLalu['kodeorg']]=$rHkBlnLalu['kodeorg'];
	setIt($hkBlnLalu[$rHkBlnLalu['kodeorg']][$rHkBlnLalu['tipekaryawan']],0);
    $hkBlnLalu[$rHkBlnLalu['kodeorg']][$rHkBlnLalu['tipekaryawan']]+=$rHkBlnLalu['hasilkerja'];
}

$brd=0;
$bg="";
if($proses=='excel')
{
    $brd=1;
    $bg="bgcolor=#DEDEDE";
}
$dtblokpertama=count($arrKeg);
$dtblokkedua=count($lstKodeorg);
 $tab=$_SESSION['lang']['jhk']." ".$_SESSION['lang']['pemeltanaman'];
                    $tab.="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable><thead>";
                    $tab.="<tr class=rowheader>";
                    $tab.="<td rowspan=3 align=center ".$bg.">".$_SESSION['lang']['kodekegiatan']."</td>";
                    $tab.="<td rowspan=3 align=center ".$bg.">".$_SESSION['lang']['kegiatan']."</td>";
                    $tab.="<td rowspan=3 align=center ".$bg.">".$_SESSION['lang']['blok']."</td>";
                    $tab.="<td colspan=9 align=center ".$bg.">".$_SESSION['lang']['blnini']."</td>";
                    $tab.="<td colspan=9 align=center ".$bg.">".$_SESSION['lang']['blnlalu']."</td>";
                    $tab.="</tr>";

                    $tab.="<tr class=rowheader>";
                    $tab.="<td colspan=2 align=center ".$bg.">".$_SESSION['lang']['hasilkerjajumlah']."</td>";
                    $tab.="<td colspan=6 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."</td>";
                    $tab.="<td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan']."</td>";
                    $tab.="<td colspan=2 align=center ".$bg.">".$_SESSION['lang']['hasilkerjajumlah']."</td>";
                    $tab.="<td colspan=6 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."</td>";
                    $tab.="<td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan']."</td>";
                    $tab.="</tr>";

                    $tab.="<tr class=rowheader>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['satuan']."</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['jumlah']."</td>";
                    $tab.="<td align=center ".$bg.">BNS</td>";
                    $tab.="<td align=center ".$bg.">KHT</td>";
                    $tab.="<td align=center ".$bg.">KHL</td>";
                    $tab.="<td align=center ".$bg.">PKWT</td>";
                    $tab.="<td align=center ".$bg.">KONTRAK</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['total']."</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['satuan']."</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['jumlah']."</td>";
                    $tab.="<td align=center ".$bg.">BNS</td>";
                    $tab.="<td align=center ".$bg.">KHT</td>";
                    $tab.="<td align=center ".$bg.">KHL</td>";
                    $tab.="<td align=center ".$bg.">PKWT</td>";
                    $tab.="<td align=center ".$bg.">KONTRAK</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['total']."</td>";
                    $tab.="</tr><thead><tbody>";
                    if($dtblokpertama!=0)
                    {
                        foreach($arrKeg as $dtKeg)
                        {
                                foreach($arrBlok as $dtBlok)
                                {
									setIt($arrDtBlnIni[$dtKeg][$dtBlok],0);
									setIt($arrHk[$dtKeg.$dtBlok]['BNS'],0);
									setIt($arrHk[$dtKeg.$dtBlok]['KHT'],0);
									setIt($arrHk[$dtKeg.$dtBlok]['KHL'],0);
									setIt($arrHk[$dtKeg.$dtBlok]['PKWT'],0);
									setIt($arrHk[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'],0);
									
									setIt($arrHslKrjBlnLalu[$dtKeg][$dtBlok],0);
									setIt($arrHkLalu[$dtKeg.$dtBlok]['BNS'],0);
									setIt($arrHkLalu[$dtKeg.$dtBlok]['KHT'],0);
									setIt($arrHkLalu[$dtKeg.$dtBlok]['KHL'],0);
									setIt($arrHkLalu[$dtKeg.$dtBlok]['PKWT'],0);
									setIt($arrHkLalu[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'],0);
									
                                    if(($arrDtBlnIni[$dtKeg][$dtBlok]!=0)
									   ||($arrHk[$dtKeg.$dtBlok]['BNS']!=0)
									   ||($arrHk[$dtKeg.$dtBlok]['KHT']!=0)
									   ||($arrHk[$dtKeg.$dtBlok]['KHL']!=0)
									   ||($arrHk[$dtKeg.$dtBlok]['PKWT']!=0)) {
                                        if(!isset($ardet) or $ardet!=$dtKeg)
                                        {
                                            $bars=0;
                                            $ardet=$dtKeg;
                                        }
                                        $bars+=1;
                                        $tab.="<tr class=rowcontent>";
                                        $tab.="<td>".$dtKeg."</td>";
                                        $tab.="<td>".$optNmkeg[$dtKeg]."</td>";
                                        $tab.="<td>".$dtBlok."</td>";
                                        $tab.="<td align=center>".$optSatkeg[$dtKeg]."</td>";
                                        $tab.="<td align=right>".number_format($arrDtBlnIni[$dtKeg][$dtBlok],2)."</td>";
                                        $tab.="<td align=right>".number_format($arrHk[$dtKeg.$dtBlok]['BNS'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHk[$dtKeg.$dtBlok]['KHT'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHk[$dtKeg.$dtBlok]['KHL'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHk[$dtKeg.$dtBlok]['PKWT'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHk[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'],0)."</td>";
                                        $totSub[$dtKeg][$dtBlok]=$arrHk[$dtKeg.$dtBlok]['BNS']+$arrHk[$dtKeg.$dtBlok]['KHT']+$arrHk[$dtKeg.$dtBlok]['KHL']+$arrHk[$dtKeg.$dtBlok]['PKWT']+$arrHk[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'];
                                        @$hkSatuan[$dtKeg][$dtBlok]=$totSub[$dtKeg][$dtBlok]/$arrDtBlnIni[$dtKeg][$dtBlok];
                                        $tab.="<td align=right>".number_format($totSub[$dtKeg][$dtBlok],0)."</td>";
                                        $tab.="<td align=right>".number_format($hkSatuan[$dtKeg][$dtBlok],2)."</td>";
                                        $tab.="<td align=center>".$optSatkeg[$dtKeg]."</td>";
                                        $tab.="<td align=right>".number_format($arrHslKrjBlnLalu[$dtKeg][$dtBlok],2)."</td>";
                                        $tab.="<td align=right>".number_format($arrHkLalu[$dtKeg.$dtBlok]['BNS'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHkLalu[$dtKeg.$dtBlok]['KHT'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHkLalu[$dtKeg.$dtBlok]['KHL'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHkLalu[$dtKeg.$dtBlok]['PKWT'],0)."</td>";
                                        $tab.="<td align=right>".number_format($arrHkLalu[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'],0)."</td>";
                                        $totSubLalu[$dtKeg][$dtBlok]=$arrHkLalu[$dtKeg.$dtBlok]['BNS']+$arrHkLalu[$dtKeg.$dtBlok]['KHT']+$arrHkLalu[$dtKeg.$dtBlok]['KHL']+$arrHkLalu[$dtKeg.$dtBlok]['PKWT']+$arrHkLalu[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'];
                                        @$hkSatuanLalu[$dtKeg][$dtBlok]=$totSubLalu[$dtKeg][$dtBlok]/$arrHslKrjBlnLalu[$dtKeg][$dtBlok];
                                        $tab.="<td align=right>".number_format($totSubLalu[$dtKeg][$dtBlok],0)."</td>";
                                        $tab.="<td align=right>".number_format($hkSatuanLalu[$dtKeg][$dtBlok],2)."</td>";
                                        $tab.="</tr>";
										
                                        //subtotalbln ini//
										setIt($sbHasil[$dtKeg],0);
										setIt($sbKbl[$dtKeg],0);
										setIt($sbKht[$dtKeg],0);
										setIt($sbKhl[$dtKeg],0);
										setIt($sbKontrak[$dtKeg],0);
										setIt($sbKkarya[$dtKeg],0);
										setIt($stotSub[$dtKeg],0);
										setIt($sHksat[$dtKeg],0);
                                        $sbHasil[$dtKeg]+=$arrDtBlnIni[$dtKeg][$dtBlok];
                                        $sbKbl[$dtKeg]+=$arrHk[$dtKeg.$dtBlok]['BNS'];
                                        $sbKht[$dtKeg]+=$arrHk[$dtKeg.$dtBlok]['KHT'];
                                        $sbKhl[$dtKeg]+=$arrHk[$dtKeg.$dtBlok]['KHL'];
                                        $sbKontrak[$dtKeg]+=$arrHk[$dtKeg.$dtBlok]['PKWT'];
                                        $sbKkarya[$dtKeg]+=$arrHk[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'];
                                        $stotSub[$dtKeg]+=$totSub[$dtKeg][$dtBlok];
                                        $sHksat[$dtKeg]+=$hkSatuan[$dtKeg][$dtBlok];
                                        //abis subtotalbln ini//
										
                                        //subtotalbln lalu//
										setIt($sbHasilL[$dtKeg],0);
										setIt($sbKblL[$dtKeg],0);
										setIt($sbKhtL[$dtKeg],0);
										setIt($sbKhlL[$dtKeg],0);
										setIt($sbKontrakL[$dtKeg],0);
										setIt($sbKkaryaL[$dtKeg],0);
										setIt($stotSubL[$dtKeg],0);
										setIt($sHksatL[$dtKeg],0);
                                        $sbHasilL[$dtKeg]+=$arrHslKrjBlnLalu[$dtKeg][$dtBlok];
                                        $sbKblL[$dtKeg]+=$arrHkLalu[$dtKeg.$dtBlok]['BNS'];
                                        $sbKhtL[$dtKeg]+=$arrHkLalu[$dtKeg.$dtBlok]['KHT'];
                                        $sbKhlL[$dtKeg]+=$arrHkLalu[$dtKeg.$dtBlok]['KHL'];
                                        $sbKontrakL[$dtKeg]+=$arrHkLalu[$dtKeg.$dtBlok]['PKWT'];
                                        $sbKkaryaL[$dtKeg]+=$arrHkLalu[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'];
                                        $stotSubL[$dtKeg]+=$totSubLalu[$dtKeg][$dtBlok];
                                        $sHksatL[$dtKeg]+=$hkSatuanLalu[$dtKeg][$dtBlok];
                                        //abis subtotalbln lalu//
                                        if($bars==$jmlh[$dtKeg])
                                        {

                                            $tab.="<tr class=rowcontent><td colspan=4>".$_SESSION['lang']['subtotal']."</td>";
                                            $tab.="<td align=right>".number_format($sbHasil[$dtKeg],2)."</td>";
                                            $tab.="<td align=right>".number_format($sbKbl[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKht[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKhl[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKontrak[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKkarya[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($stotSub[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sHksat[$dtKeg],2)."</td>";
                                            $tab.="<td>&nbsp;</td>";
                                            $tab.="<td align=right>".number_format($sbHasilL[$dtKeg],2)."</td>";
                                            $tab.="<td align=right>".number_format($sbKblL[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKhtL[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKhlL[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKontrakL[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sbKkaryaL[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($stotSubL[$dtKeg],0)."</td>";
                                            $tab.="<td align=right>".number_format($sHksatL[$dtKeg],2)."</td></tr>";
                                        }
                                    }

                                }
                                     
                            }
                        
                    }
                    else
                    {
                    $tab.="<tr class=rowcontent><td colspan=21>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }
                    $tab.="</tbody></table>";
                    $tab.=$_SESSION['lang']['jhk']." ".$_SESSION['lang']['panen'];
                    $tab.="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable><thead>";
                    $tab.="<tr class=rowheader>";
                    $tab.="<td rowspan=3 align=center ".$bg.">".$_SESSION['lang']['blok']."</td>";
                    $tab.="<td rowspan=3 align=center ".$bg.">".$_SESSION['lang']['kodekegiatan']."</td>";
                    $tab.="<td rowspan=3 align=center ".$bg.">".$_SESSION['lang']['kegiatan']."</td>";
                    $tab.="<td colspan=9 align=center ".$bg.">".$_SESSION['lang']['blnini']."</td>";
                    $tab.="<td colspan=9 align=center ".$bg.">".$_SESSION['lang']['blnlalu']."</td>";
                    $tab.="</tr>";

                    $tab.="<tr class=rowheader>";
                    $tab.="<td colspan=2 align=center ".$bg.">".$_SESSION['lang']['hasilkerjajumlah']."</td>";
                    $tab.="<td colspan=6 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."</td>";
                    $tab.="<td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan']."</td>";
                    $tab.="<td colspan=2 align=center ".$bg.">".$_SESSION['lang']['hasilkerjajumlah']."</td>";
                    $tab.="<td colspan=6 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."</td>";
                    $tab.="<td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan']."</td>";
                    $tab.="</tr>";

                    $tab.="<tr class=rowheader>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['satuan']."</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['jumlah']."</td>";
                    $tab.="<td align=center ".$bg.">BNS</td>";
                    $tab.="<td align=center ".$bg.">KHT</td>";
                    $tab.="<td align=center ".$bg.">KHL</td>";
                    $tab.="<td align=center ".$bg.">PKWT</td>";
                    $tab.="<td align=center ".$bg.">KONTRAK</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['total']."</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['satuan']."</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['jumlah']."</td>";
                    $tab.="<td align=center ".$bg.">BNS</td>";
                    $tab.="<td align=center ".$bg.">KHT</td>";
                    $tab.="<td align=center ".$bg.">KHL</td>";
                    $tab.="<td align=center ".$bg.">PKWT</td>";
                    $tab.="<td align=center ".$bg.">KONTRAK</td>";
                    $tab.="<td align=center ".$bg.">".$_SESSION['lang']['total']."</td>";
                    $tab.="</tr><thead><tbody>";
                    if($dtblokkedua!=0)
                    {
                        foreach($lstKodeorg as $dtBlok2)
                        {
							setIt($panenHslBln[$dtBlok2],0);
							setIt($hkBln[$dtBlok2]['BNS'],0);
							setIt($hkBln[$dtBlok2]['KHT'],0);
							setIt($hkBln[$dtBlok2]['KHL'],0);
							setIt($hkBln[$dtBlok2]['PKWT'],0);
							setIt($hkBln[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'],0);
							
							setIt($panenHslBlnLalu[$dtBlok2],0);
							setIt($hkBlnLalu[$dtBlok2]['BNS'],0);
							setIt($hkBlnLalu[$dtBlok2]['KHT'],0);
							setIt($hkBlnLalu[$dtBlok2]['KHL'],0);
							setIt($hkBlnLalu[$dtBlok2]['PKWT'],0);
							setIt($hkBlnLalu[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'],0);
							
							$tab.="<tr class=rowcontent>";
							$tab.="<td>".$dtBlok2."</td>";
							$tab.="<td>611010101</td>";
							$tab.="<td>".$optNmkeg[611010101]."</td>";
							$tab.="<td align=center>".$optSatkeg[611010101]."</td>";
							$tab.="<td align=right>".number_format($panenHslBln[$dtBlok2],2)."</td>";
							$tab.="<td align=right>".number_format($hkBln[$dtBlok2]['BNS'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBln[$dtBlok2]['KHT'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBln[$dtBlok2]['KHL'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBln[$dtBlok2]['PKWT'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBln[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'],0)."</td>";
							$totSubPanen[$dtBlok2]=$hkBln[$dtBlok2]['BNS']+$hkBln[$dtBlok2]['KHT']+$hkBln[$dtBlok2]['KHL']+$hkBln[$dtBlok2]['PKWT']+$hkBln[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'];
							@$hkSatuanPanen[$dtBlok2]=$totSub[$dtBlok2]/$panenHslBln[$dtBlok2];
							$tab.="<td align=right>".number_format($totSubPanen[$dtBlok2],0)."</td>";
							$tab.="<td align=right>".number_format($hkSatuanPanen[$dtBlok2],2)."</td>";
							$tab.="<td align=center>".$optSatkeg[611010101]."</td>";
							$tab.="<td align=right>".number_format($panenHslBlnLalu[$dtBlok2],2)."</td>";
							$tab.="<td align=right>".number_format($hkBlnLalu[$dtBlok2]['BNS'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBlnLalu[$dtBlok2]['KHT'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBlnLalu[$dtBlok2]['KHL'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBlnLalu[$dtBlok2]['PKWT'],0)."</td>";
							$tab.="<td align=right>".number_format($hkBlnLalu[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'],0)."</td>";
							$totSubPanenLalu[$dtBlok2]=$hkBlnLalu[$dtBlok2]['BNS']+$hkBlnLalu[$dtBlok2]['KHT']+$hkBlnLalu[$dtBlok2]['KHL']+$hkBlnLalu[$dtBlok2]['PKWT']+$hkBlnLalu[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'];
							@$hkSatuanPanenLalu[$dtBlok2]=$totSubLalu[$dtBlok2]/$panenHslBlnLalu[$dtBlok2];
							$tab.="<td align=right>".number_format($totSubPanenLalu[$dtBlok2],0)."</td>";
							$tab.="<td align=right>".number_format($hkSatuanPanenLalu[$dtBlok2],2)."</td>";
							$tab.="</tr>";
							
							
						
                        }

						// $tab.="<tr class=rowcontent>";
						// $tab.="<td colspan=5 align=center>TOTAL</td>";
						// $tab.="<td align=right>".number_format($ttlbnsbi[$dtBlok2])."</td>";
						// $tab.="<td align=right>".number_format($ttlkhtbi[$dtBlok2])."</td>";
						// $tab.="<td align=right>".number_format($ttlkhlbi[$dtBlok2])."</td>";
						// $tab.="<td align=right>".number_format($ttlpkwtbi[$dtBlok2])."</td>";
						// $tab.="</tr>";
					}
					
                    else
                    {
                    $tab.="<tr class=rowcontent><td colspan=21>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }
                    $tab.="</tbody></table>";

	switch($proses)
        {
            case'preview':
                   
            echo $tab;
            break;
            case'excel':
            $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
            $dte=date("His");
            $nop_="laporanPenggunaanHk_".$dte;
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
            fclose($handle);
            }
            break;
            case'pdf':
            if($kodeOrg==''||$periode=='')
            {
                exit("Error:All fields are reqired");
            }

           class PDF extends FPDF {
            function Header() {
            global $dtThnBudget;
            global $dtKdunit;
            global $dtJmlhKg;
            global $dtJjg;
            global $dtJmlhLuas;
            global $totKg;
            global $totJjg;
            global $totLuas;
            global $dbname;
            global $optNm;
            global $kodeOrg;
            global $totalUnit;
            global $modPil;
            global $spanLt;
            global $dtJmlhThnTnm;
            global $totaThntnm;
            global $arrBln;
            global $owlPDO;
            
			$arrHead = setheadreport(substr($kodeOrg,0,4));
			
			$width = $this->w - $this->lMargin - $this->rMargin;
			$height = 15;
			$path=$arrHead['logo'];
			$this->Image($path,$this->lMargin,($this->tMargin-12),0,55);
			$this->SetFont('Arial','B',9);
			$this->SetFillColor(255,255,255);	
			$this->SetX(110);   
			$this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
			$this->SetX(110); 		
			$this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
			$this->SetX(110); 			
			$this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
			$this->Line($this->lMargin,$this->tMargin+($height*4),
			$this->lMargin+$width,$this->tMargin+($height*4));
			$this->Ln();
  
			$this->Ln();
               
               

            }
            
            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
            }
            //================================

            $pdf=new PDF('L','pt','A4');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 15;
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell($width,$height,strtoupper($_SESSION['lang']['penggunaanhk']),0,1,'C');
            $pdf->Ln();	
             $pdf->SetFillColor(255,255,255);
            $pdf->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNm[$kodeOrg],0,1,'C');
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(650,$height,$_SESSION['lang']['tanggal'],0,0,'R');
            $pdf->Cell(10,$height,':','',0,0,'R');
            $pdf->Cell(70,$height,date('d-m-Y H:i'),0,1,'R');
//            $pdf->Cell(650,$height,$_SESSION['lang']['page'],0,0,'R');
//            $pdf->Cell(10,$height,':','',0,0,'R');
//            $pdf->Cell(70,$height,$pdf->PageNo(),0,1,'R');
            $pdf->Cell(650,$height,'User',0,0,'R');
            $pdf->Cell(10,$height,':','',0,0,'R');
            $pdf->Cell(70,$height,$_SESSION['standard']['username'],0,1,'R');
            $pdf->ln(18);
            $pdf->SetFont('Arial','B',6);
           
            $pdf->Cell(85, $height, $_SESSION['lang']['jhk']." ".$_SESSION['lang']['pemeltanaman'], 0, 1, 'L',1);
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(65, $height, $_SESSION['lang']['kodekegiatan'], 'TLR', 0, 'C',1);
            $pdf->Cell(165, $height, $_SESSION['lang']['kegiatan'], 'TLR', 0, 'C',1);
            $pdf->Cell(55, $height, $_SESSION['lang']['blok'], 'TLR', 0, 'C',1);
            $pdf->Cell(260, $height, $_SESSION['lang']['blnini'], 'TLR', 0, 'C',1);
            $pdf->Cell(260, $height, $_SESSION['lang']['blnlalu'], 'TLR', 1, 'C',1);
            
            $pdf->Cell(65, $height," ", 'LR', 0, 'C',1);
            $pdf->Cell(165, $height, " ", 'LR', 0, 'C',1);
            $pdf->Cell(55, $height, " ", 'LR', 0, 'C',1);
            $pdf->Cell(70, $height, $_SESSION['lang']['hasilkerjajumlah'], 'TLR', 0, 'C',1);
            $pdf->Cell(145, $height, $_SESSION['lang']['jumlahhk'], 'TLR', 0, 'C',1);
            $pdf->Cell(45, $height, $_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan'], 'TLR', 0, 'C',1);
            
            $pdf->Cell(70, $height, $_SESSION['lang']['hasilkerjajumlah'], 'TLR', 0, 'C',1);
            $pdf->Cell(145, $height, $_SESSION['lang']['jumlahhk'], 'TLR', 0, 'C',1);
            $pdf->Cell(45, $height, $_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan'], 'TLR', 1, 'C',1);
            
            $pdf->Cell(65, $height," ", 'BLR', 0, 'C',1);
            $pdf->Cell(165, $height, " ", 'BLR', 0, 'C',1);
            $pdf->Cell(55, $height, " ", 'BLR', 0, 'C',1);
            $pdf->Cell(30, $height, $_SESSION['lang']['satuan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(40, $height, $_SESSION['lang']['jumlah'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['bulanan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(23, $height, "KHT", 'TBLR', 0, 'C',1);
            $pdf->Cell(22, $height, "KHL", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['PKWT'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, "KKarya", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['total'], 'TBLR', 0, 'C',1);
            $pdf->Cell(45, $height, " ", 'BLR', 0, 'C',1);
            $pdf->Cell(30, $height, $_SESSION['lang']['satuan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(40, $height, $_SESSION['lang']['jumlah'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['bulanan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(23, $height, "KHT", 'TBLR', 0, 'C',1);
            $pdf->Cell(22, $height, "KHL", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['PKWT'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, "KKarya", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['total'], 'TBLR', 0, 'C',1);
            $pdf->Cell(45, $height, " ", 'BLR', 1, 'C',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',6);
            if($dtblokpertama!=0)
                    {
                        foreach($arrKeg as $dtKeg)
                        {
                                foreach($arrBlok as $dtBlok)
                                {
                                   if(($arrDtBlnIni[$dtKeg][$dtBlok]!=0)||($arrHk[$dtKeg.$dtBlok]['BNS']!=0)||($arrHk[$dtKeg.$dtBlok]['KHT']!=0)||($arrHk[$dtKeg.$dtBlok]['KHL']!=0)||($arrHk[$dtKeg.$dtBlok]['PKWT']!=0))
                                    {
                                        if($ardet!=$dtKeg)
                                        {
                                            $bars=0;
                                            $ardet=$dtKeg;
                                        }
                                        $pdf->Cell(65, $height,$dtKeg, 'TBLR', 0, 'C',1);
                                        $pdf->Cell(165, $height,$optNmkeg[$dtKeg], 'TBLR', 0, 'L',1);
                                        $pdf->Cell(55, $height, $dtBlok, 'TBLR', 0, 'L',1);
                                        $pdf->Cell(30, $height, $optSatkeg[$dtKeg], 'TBLR', 0, 'C',1);
                                        $pdf->Cell(40, $height, number_format($arrDtBlnIni[$dtKeg][$dtBlok],2), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($arrHk[$dtKeg.$dtBlok]['BNS'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(23, $height, number_format($arrHk[$dtKeg.$dtBlok]['KHT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(22, $height, number_format($arrHk[$dtKeg.$dtBlok]['KHL'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($arrHk[$dtKeg.$dtBlok]['PKWT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($arrHk[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($totSub[$dtKeg][$dtBlok],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(45, $height, number_format($hkSatuan[$dtKeg][$dtBlok],2), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(30, $height, $optSatkeg[$dtKeg], 'TBLR', 0, 'C',1);
                                        $pdf->Cell(40, $height, number_format($arrHslKrjBlnLalu[$dtKeg][$dtBlok],2), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($arrHkLalu[$dtKeg.$dtBlok]['BNS'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(23, $height, number_format($arrHkLalu[$dtKeg.$dtBlok]['KHT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(22, $height, number_format($arrHkLalu[$dtKeg.$dtBlok]['KHL'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($arrHkLalu[$dtKeg.$dtBlok]['PKWT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($arrHkLalu[$dtKeg.$dtBlok]['KONTRAK KARYAWAN [USIA LANJUT]'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($totSubLalu[$dtKeg][$dtBlok],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(45, $height, number_format($hkSatuanLalu[$dtKeg][$dtBlok],2), 'TBLR', 1, 'R',1);
                                        if($bars==$jmlh[$dtKeg])
                                        {
                                       
                                            $pdf->Cell(315, $height,$_SESSION['lang']['subtotal'], 'TBLR', 0, 'C',1);
                                            $pdf->Cell(40, $height, number_format($sbHasil[$dtKeg],2), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($sbKbl[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(23, $height, number_format($sbKht[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(22, $height, number_format($sbKhl[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($sbKontrak[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($sbKkarya[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($stotSub[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(45, $height, number_format($sHksat[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(30, $height, " ", 'TBLR', 0, 'C',1);
                                            $pdf->Cell(40, $height, number_format($sbHasilL[$dtKeg],2), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($sbKblL[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(23, $height, number_format($sbKhtL[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(22, $height, number_format($sbKhlL[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($sbKontrakL[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($sbKkaryaL[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(25, $height, number_format($stotSubL[$dtKeg],0), 'TBLR', 0, 'R',1);
                                            $pdf->Cell(45, $height, number_format($sHksatL[$dtKeg],0), 'TBLR', 1, 'R',1);
                                        }
                                    }
                                }
                            }
                        
                    }
                    else
                    {
                    $pdf->Cell(805, $height,$_SESSION['lang']['dataempty'], 'TBLR', 1, 'C',1);
                    }
                    
            $pdf->ln(18);
            $pdf->SetFont('Arial','B',6);
            $pdf->Cell(85, $height, $_SESSION['lang']['jhk']." ".$_SESSION['lang']['panen'], 0, 1, 'L',1);
            $pdf->SetFillColor(220,220,220);
            $pdf->Cell(65, $height, $_SESSION['lang']['kodekegiatan'], 'TLR', 0, 'C',1);
            $pdf->Cell(165, $height, $_SESSION['lang']['kegiatan'], 'TLR', 0, 'C',1);
            $pdf->Cell(55, $height, $_SESSION['lang']['blok'], 'TLR', 0, 'C',1);
            $pdf->Cell(260, $height, $_SESSION['lang']['blnini'], 'TLR', 0, 'C',1);
            $pdf->Cell(260, $height, $_SESSION['lang']['blnlalu'], 'TLR', 1, 'C',1);
            
            $pdf->Cell(65, $height," ", 'LR', 0, 'C',1);
            $pdf->Cell(165, $height, " ", 'LR', 0, 'C',1);
            $pdf->Cell(55, $height, " ", 'LR', 0, 'C',1);
            $pdf->Cell(70, $height, $_SESSION['lang']['hasilkerjajumlah'], 'TLR', 0, 'C',1);
            $pdf->Cell(145, $height, $_SESSION['lang']['jumlahhk'], 'TLR', 0, 'C',1);
            $pdf->Cell(45, $height, $_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan'], 'TLR', 0, 'C',1);
            
            $pdf->Cell(70, $height, $_SESSION['lang']['hasilkerjajumlah'], 'TLR', 0, 'C',1);
            $pdf->Cell(145, $height, $_SESSION['lang']['jumlahhk'], 'TLR', 0, 'C',1);
            $pdf->Cell(45, $height, $_SESSION['lang']['jumlahhk']."/".$_SESSION['lang']['satuan'], 'TLR', 1, 'C',1);
            
            $pdf->Cell(65, $height," ", 'BLR', 0, 'C',1);
            $pdf->Cell(165, $height, " ", 'BLR', 0, 'C',1);
            $pdf->Cell(55, $height, " ", 'BLR', 0, 'C',1);
            $pdf->Cell(30, $height, $_SESSION['lang']['satuan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(40, $height, $_SESSION['lang']['jumlah'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['bulanan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(23, $height, "KHT", 'TBLR', 0, 'C',1);
            $pdf->Cell(22, $height, "KHL", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['PKWT'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, "KKarya", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['total'], 'TBLR', 0, 'C',1);
            $pdf->Cell(45, $height, " ", 'BLR', 0, 'C',1);
            $pdf->Cell(30, $height, $_SESSION['lang']['satuan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(40, $height, $_SESSION['lang']['jumlah'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['bulanan'], 'TBLR', 0, 'C',1);
            $pdf->Cell(23, $height, "KHT", 'TBLR', 0, 'C',1);
            $pdf->Cell(22, $height, "KHL", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['PKWT'], 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, "KKarya", 'TBLR', 0, 'C',1);
            $pdf->Cell(25, $height, $_SESSION['lang']['total'], 'TBLR', 0, 'C',1);
            $pdf->Cell(45, $height, " ", 'BLR', 1, 'C',1);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','',6);
            
            if($dtblokkedua!=0)
                    {
                       foreach($lstKodeorg as $dtBlok2)
                       {
                                   
                                        $pdf->Cell(65, $height,"611010101", 'TBLR', 0, 'C',1);
                                        $pdf->Cell(165, $height,$optNmkeg[611010101], 'TBLR', 0, 'L',1);
                                        $pdf->Cell(55, $height, $dtBlok2, 'TBLR', 0, 'L',1);
                                        $pdf->Cell(30, $height, $optSatkeg[611010101], 'TBLR', 0, 'C',1);
                                        $pdf->Cell(40, $height, number_format($panenHslBln[$dtBlok2],2), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($hkBln[$dtBlok2]['BNS'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(23, $height, number_format($hkBln[$dtBlok2]['KHT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(22, $height, number_format($hkBln[$dtBlok2]['KHL'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($hkBln[$dtBlok2]['PKWT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($hkBln[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($totSubPanen[$dtBlok2],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(45, $height, number_format($hkSatuanPanen[$dtBlok2],2), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(30, $height, $optSatkeg[611010101], 'TBLR', 0, 'C',1);
                                        $pdf->Cell(40, $height, number_format($panenHslBlnLalu[$dtBlok2],2), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($hkBlnLalu[$dtBlok2]['BNS'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(23, $height, number_format($hkBlnLalu[$dtBlok2]['KHT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(22, $height, number_format($hkBlnLalu[$dtBlok2]['KHL'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($hkBlnLalu[$dtBlok2]['PKWT'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($hkBlnLalu[$dtBlok2]['KONTRAK KARYAWAN [USIA LANJUT]'],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(25, $height, number_format($totSubPanenLalu[$dtBlok2],0), 'TBLR', 0, 'R',1);
                                        $pdf->Cell(45, $height, number_format($hkSatuanPanenLalu[$dtBlok2],2), 'TBLR', 1, 'R',1);
                                
                            }
                        
                    }
                    else
                    {
                    $pdf->Cell(805, $height,$_SESSION['lang']['dataempty'], 'TBLR', 1, 'C',1);
                    }
            $pdf->Output();	
                
                
            break;
                
            default:
            break;
        }
	
?>
