<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
	$pt=$_GET['pt'];
	$periode=$_GET['periode'];
	if($periode==''){ exit('Pilih periode terlebih dahulu'); }
#filter sama dengan list SPB
$txtSearch=isset($_GET['txtSearch']) ? $_GET['txtSearch'] : '';
$referensisearch=isset($_GET['referensisearch']) ? $_GET['referensisearch'] : '';
$txtDiv=isset($_GET['txtDiv']) ? $_GET['txtDiv'] : '';
$txtTglAsli=isset($_GET['txtTgl']) ? $_GET['txtTgl'] : '';
$txtTgl=($txtTglAsli!='') ? tanggalsystem($txtTglAsli) : '';
$status_spb=isset($_GET['status_spb']) ? $_GET['status_spb'] : '';
$postsch=isset($_GET['postsch']) ? $_GET['postsch'] : '';
$wherepilih=" a.kodeorg IN (".getOrgDetail(2).") ";
$infofilter='';
if($pt!=''){
	$wherepilih.=" and a.kodeorg='".$pt."' ";
}
if($txtSearch!=''){
	$wherepilih.=" and a.nospb like '%".$txtSearch."%' ";
	$infofilter.=" | No SPB: ".$txtSearch;
}
if($referensisearch!=''){
	$wherepilih.=" and a.noreferensi='".$referensisearch."' ";
	$infofilter.=" | No Referensi: ".$referensisearch;
}
if($txtDiv!=''){
	$wherepilih.=" and a.nospb in (select nospb from ".$dbname.".kebun_spbdt where blok like '%".$txtDiv."%') ";
	$infofilter.=" | Divisi: ".$txtDiv;
}
if($txtTgl!=''){
	$wherepilih.=" and a.tanggal='".$txtTgl."' ";
	$infofilter.=" | Tanggal: ".$txtTglAsli;
}
if($status_spb!=''){
	$wherepilih.=" and a.tujuan='".$status_spb."' ";
	$arrstatus=array('0'=>'Internal','1'=>'Alfiasi','3'=>'Eksternal','4'=>'TPH Besar');
	$infofilter.=" | Status: ".(isset($arrstatus[$status_spb]) ? $arrstatus[$status_spb] : $status_spb);
}
if($postsch=='1'){
	$wherepilih.=" and a.posting='1' ";
	$infofilter.=" | Status Posting: Posted";
}elseif($postsch=='0'){
	$wherepilih.=" and a.posting<>'1' ";
	$infofilter.=" | Status Posting: Belum Posting";
}

//ambil namapt
$query2 = selectQuery($dbname,'organisasi','namaorganisasi',
"kodeorganisasi='".$pt."'");
$orgData2 = fetchData($query2);	
			$strx="select a.tanggal,b.* from ".$dbname.".kebun_spbht a inner join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb 
		where a.tanggal like '".$periode."-%' and ".$wherepilih." order by a.tanggal asc "; 
//		echo"warning:".$strx;exit();
			#kop: logo PT, judul, informasi tarikan, dan waktu cetak
			$ptkode=getindukPT(($pt!='') ? substr($pt,0,4) : $_SESSION['empl']['lokasitugas']);
			$hd=setheadreport($ptkode,$ptkode);
			$logourl='';
			if(file_exists($hd['logo'])){
				$skema=(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']!='off') ? 'https' : 'http';
				$logourl=$skema."://".@$_SERVER['HTTP_HOST'].rtrim(dirname(@$_SERVER['SCRIPT_NAME']),'/')."/".$hd['logo'];
			}
			$stream="
			<table>
			<tr><td colspan=12 height='70' style='height:52pt'>".($logourl!='' ? "<img src='".$logourl."' height='60'>" : "")."</td></tr>
			<tr><td colspan=12><b>".$hd['nama']."</b></td></tr>
			<tr><td colspan=12><b>".strtoupper($_SESSION['lang']['listSpb'])."</b></td></tr>
			<tr><td colspan=12>".$_SESSION['lang']['unit'].": ".($pt!='' ? $pt." - ".$orgData2[0]['namaorganisasi'] : "Seluruhnya")." | ".$_SESSION['lang']['periode'].": ".$periode.$infofilter."</td></tr>
			<tr><td colspan=12>Dicetak: ".date('d-m-Y H:i:s')." oleh ".$_SESSION['empl']['name']."</td></tr>
			<tr><td colspan=12>&nbsp;</td></tr>
			</table>
			<table border=1>
						<tr>
							<td bgcolor=#DEDEDE align=center>No.</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nospb']."</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['blok']."</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['janjang']."</td>
                            <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kgwb']."</td>
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['bjr']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['brondolan']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['mentah']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['busuk']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['matang']."</td>	
							<td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['lewatmatang']."</td>	
						</tr>";
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
			$no=0;
			while($barx=$resx->fetch())
			{
				$no+=1;
						
				$stream.="	<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$barx['nospb']."</td>
					<td>".$barx['tanggal']."</td>
					<td>".$barx['blok']."</td>
					<td>".number_format($barx['jjg'],2)."</td>
                                        <td>".number_format($barx['kgwb'],2)."</td>    
					<td>".number_format($barx['bjr'],2)."</td>	
					<td>".number_format($barx['brondolan'],2)."</td>	
					<td>".number_format($barx['mentah'],2)."</td>	
					<td>".number_format($barx['busuk'],2)."</td>
					<td>".number_format($barx['matang'],2)."</td>	
					<td>".number_format($barx['lewatmatang'],2)."</td>	
					</tr>";
			}
	
	//echo "warning:".$strx;
//=================================================
		
	$stream.="</table>";	

$nop_="".$_SESSION['lang']['listSpb']."";
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
//closedir($handle);
}
?>