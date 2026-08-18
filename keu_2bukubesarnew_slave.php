<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
$pt = checkPostGet('pt', '');
$gudang = checkPostGet('gudang', '');
$akundari = checkPostGet('akundari', '');
$akunsampai = checkPostGet('akunsampai', '');
$periode=checkPostGet('periode','');
$periode1=checkPostGet('periode1','');
$revisi=checkPostGet('revisi','');
$regional=checkPostGet('regional','');
$tampilanId=checkPostGet('tampilanId','');
$tipelaporan=checkPostGet('tipelaporan','');

$rekapdetail=checkPostGet('tampilkan','');

$stream="";
        
//cek periode dan periode1
if($periode1<$periode)
{  #ditukar
    $z=$periode;
    $periode=$periode1;
    $periode1=$z;
}
$where='';
if($akundari!='' and $akunsampai!=''){
	$where.=" and noakun between '".$akundari."' and  '".$akunsampai."'";
}	

$whereakun='';
if($akundari!='' and $akunsampai!=''){
	$whereakun.=" and noakun between '".$akundari."' and  '".$akunsampai."'";
}		
	

//ambil namapt
$str=$owlPDO->query("select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'");
$namapt='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}

//ambil namagudang
$str=$owlPDO->query("select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'");
$namagudang='';
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
    $namagudang=strtoupper($bar->namaorganisasi);
}

//ambil akun laba rugi tahun berjalan:
$CLM='';
$str=$owlPDO->query("select noakundebet from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='CLM'");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=  $str->fetch()){
    $CLM=$bar->noakundebet;
}

//ambil semua noakun dari bulan lalu dan bulan ini
$lmperiode=mktime(0,0,0,substr($periode,5,2)-1,4,substr($periode,0,4));
$lmperiode=date('Y-m',$lmperiode);
if($_SESSION['language']=='ID'){
$str="select distinct noakun,namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."'  ".$where." order by noakun";
}
else{
    $str="select distinct noakun,namaakun1 as namaakun from ".$dbname.".keu_5akun where  noakun!='".$CLM."' ".$where." order by noakun";
}
// echo $str;
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
$TAB=Array();

while($bar=$res->fetch())
{
    $TAB[$bar->noakun]['noakun']=$bar->noakun;
    $TAB[$bar->noakun]['namaakun']=$bar->namaakun;
    $TAB[$bar->noakun]['sawal']=0;
    $TAB[$bar->noakun]['salak']=0;
}

if($regional=='' && $gudang=='')
{
   $where =" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4)";
}
else if($regional!='' && $gudang=='')
{
    $where=" and kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) "; 
}
else
{
    $where =" and kodeorg ='".$gudang."'";
}




#disini tambahin kodeorg
$str="select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun,kodeorg from ".$dbname.".keu_saldobulanan 
      where periode ='".str_replace("-","",$periode)."'  and  noakun!='".$CLM."' ".$where."   group by noakun order by noakun";
// echo $str;
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $TAB[$bar->noakun]['sawal']=$bar->sawal;
    $TAB[$bar->noakun]['salak']=$bar->sawal;
}

//Ini tidak bisa karena dikunci menggunakan store procedure bawaan db. Gunakan script ini jika mau:
// CREATE USER 'root'@'%' IDENTIFIED BY 'password_database_anda';  -- Semoga membantu, created by hans
// GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
// FLUSH PRIVILEGES;

$whOld = " and periode>='".$periode."' and periode<='".$periode1."'";

$tanggal = $periode."-01";
$lastDay = cal_days_in_month(CAL_GREGORIAN,substr($periode1,5,2),substr($periode1,0,4));
$tanggalx = $periode1."-".$lastDay;
$whNew = " and tanggal>='".$tanggal."' and tanggal<='".$tanggalx."'";

$str="select sum(debet) as debet,sum(kredit) as kredit, noakun,kodeorg from ".$dbname.".keu_jurnaldt_vw
    where 5=5 {$whNew} ".$where." ".$whereakun." 
    and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun"; #tidak sama dengan laba/rugi berjalan
// echo $str;
$res=$owlPDO->query($str);

$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	@$TAB[$bar->noakun]['debet']+=$bar->debet;
	@$TAB[$bar->noakun]['kredit']+=$bar->kredit;
} 

// $str = "SELECT SUM(debet) AS debet, SUM(kredit) AS kredit, noakun, kodeorg 
//         FROM " . $dbname . ".keu_jurnaldt_vw
//         WHERE periode >= '" . $periode . "' 
//         AND periode <= '" . $periode1 . "' " . $where . " " . $whereakun . " 
//         AND noakun != '" . $CLM . "' 
//         AND revisi <= '" . $revisi . "' 
//         GROUP BY noakun"; 

// try {
//     $res = $owlPDO->query($str);
//     $res->setFetchMode(PDO::FETCH_OBJ);

//     while ($bar = $res->fetch()) {
//         // Inisialisasi index jika belum ada agar tidak error
//         if (!isset($TAB[$bar->noakun]['debet'])) {
//             $TAB[$bar->noakun]['debet'] = 0;
//             $TAB[$bar->noakun]['kredit'] = 0;
//         }

//         $TAB[$bar->noakun]['debet'] += $bar->debet;
//         $TAB[$bar->noakun]['kredit'] += $bar->kredit;
//     }
// } catch (PDOException $e) {
//     // Jika masih error, pesan aslinya akan muncul di sini
//     echo "Gagal mengambil data: " . $e->getMessage();
// }


$no=0;

// echo "<pre>";
// print_r($kode);
// echo "</pre>";
if($tipelaporan=='excel'){
    $border = 'border=1';
}else{
    $border ='';
}
$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$gudang."'");
$nmorgPT	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");

if($tipelaporan!='html'){
	$stream.="Laporan Neraca<br>";
    // exit('error PT. " . $pt . " - " '.  $nmorgPT[$pt] .'"');
    $stream .= "PT. " . $pt . " - " . (isset($nmorgPT[$pt]) ? $nmorgPT[$pt] : "") . "<br>"; 
	if($gudang==''){
		$unit 	= 'Seluruh Unit';
		$stream.="".$unit."<br>";
	}else{
		$unit   = $gudang;
		$stream.="".$unit." - ".$nmorg[$unit]."<br>";
	}
	$stream.="Periode ".$periode." s/d ".$periode1."<br><br>";
}
$stream.="
        <table class=sortable cellspacing=1 ".$border.">
            <thead>
                <tr>
                    <th align=center style='width:50px;'>".$_SESSION['lang']['nomor']."</th>
                    <th align=center style='width:80px;'>".$_SESSION['lang']['noakun']."</th>
                    <th align=center style='width:450px;'>".$_SESSION['lang']['namaakun']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['saldoawal']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['debet']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['kredit']."</th>
                    <th align=center style='width:130px;'>".$_SESSION['lang']['saldoakhir']."</th>
                </tr> 
            </thead>
            <tbody>";



	
        foreach($TAB as $baris => $data){
            if($data['noakun']!=''){
                if($tampilanId==1){
                    if(($data['sawal']==0)&&($data['debet']==0)&&($data['kredit']==0)){
                        continue;
                    }
                }
                $no+=1;
				@$data['salak']=$data['sawal']+$data['debet']-$data['kredit'];

                if($tipelaporan=='excel'){
                    $qsawal=$data['sawal'];
                    $qdebet=isset($data['debet'])? $data['debet']: 0;
                    $qkredit=isset($data['kredit'])? $data['kredit']: 0;
                    $qakhir=$data['salak'];
                }else{
                    $qsawal=hidezerodecimal($data['sawal'],2);
                    $qdebet=hidezerodecimal(isset($data['debet'])? $data['debet']: 0,2);
                    $qkredit=hidezerodecimal(isset($data['kredit'])? $data['kredit']: 0,2);
                    $qakhir=hidezerodecimal($data['salak'],2);
                }    

                if($rekapdetail=='detail' OR $rekapdetail=='1'){

                $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$gudang."','".$revisi."',event);\">";
                }else{
                $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatRekap('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$gudang."','".$revisi."',event);\">";

                }
                $stream.="<td style='width:50px;' align=center>".$no."</td>
                    <td style='width:80px;'>".$data['noakun']."</td>    
                    <td style='width:450px;'>".$data['namaakun']."</td>
                    <td align=right style='width:130px;'>".$qsawal."</td>
                    <td align=right style='width:130px;'>".$qdebet."</td>
                    <td align=right style='width:130px;'>".$qkredit."</td>   
                    <td align=right style='width:130px;'>".$qakhir."</td>    
                </tr>";
            
                $sal_awal+=$data['sawal'];
                $sal_debet+=isset($data['debet'])? $data['debet']: 0;
                $sal_kredit+=isset($data['kredit'])? $data['kredit']: 0;
                $sal_salak+=$data['salak']; 
            }
        }
	
$stream.="<tr class=rowcontent>
            <td colspan=3 align=center><b>".$_SESSION['lang']['total']."</b></td>
            <td align=right><b>".hidezerodecimal($sal_awal,2)."</b></td>
            <td align=right><b>".hidezerodecimal($sal_debet,2)."</b></td>
            <td align=right><b>".hidezerodecimal($sal_kredit,2)."</b></td>   
            <td align=right><b>".hidezerodecimal($sal_salak,2)."</b></td> 
        </tr>"; 
$stream.="</tbody>
            <tfoot>
            </tfoot>		 
        </table>";

if($tipelaporan=='html'){
	echo $stream;
}else{
	
	$nop="NERACASALDO_".$gudang."_".$periode.".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("NERACASALDO", $stream);
	// $xls->addSheet("Report", $tab2);
	$xls->headers($nop);
	echo $xls->buildFile();
	
}	
       
?>