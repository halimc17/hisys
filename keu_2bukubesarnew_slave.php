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
    $TAB[$bar->noakun]['unit']=array();
}

#daftar kodeorg diambil dulu (query kecil terpisah) lalu ditempel sebagai IN(literal) di query besar
#(keu_saldobulanan & keu_jurnaldt_vw) - jauh lebih cepat daripada IN(select ...) langsung di query besar itu,
#terbukti dari pengujian: query saldo awal 270ms->11ms, query jurnal 544ms->306ms untuk dataset yang sama
if($regional=='' && $gudang=='')
{
    $rUnit = fetchData("select kodeorganisasi from ".$dbname.".organisasi where induk='".addslashes($pt)."' and length(kodeorganisasi)=4");
}
else if($regional!='' && $gudang=='')
{
    $rUnit = fetchData("select kodeunit as kodeorganisasi from ".$dbname.".bgt_regional_assignment where regional='".addslashes($regional)."'"
            . " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".addslashes($pt)."')");
}
else
{
    $rUnit = array(array('kodeorganisasi'=>$gudang));
}
$listUnit = array();
foreach($rUnit as $r){ $listUnit[] = "'".addslashes($r['kodeorganisasi'])."'"; }
$where = (count($listUnit) > 0) ? " and kodeorg in (".implode(',', $listUnit).")" : " and 1=0";




#dipecah per kodeorg (bukan cuma per noakun) supaya mode Rincian beneran beda dari Rekap: kalau Unit
#filter-nya "Seluruhnya", tiap akun tampil satu baris per unit, bukan cuma satu baris gabungan semua unit
$str="select sum(awal".substr(str_replace("-","",$periode),4,2).") as sawal,noakun,kodeorg from ".$dbname.".keu_saldobulanan
      where periode ='".str_replace("-","",$periode)."'  and  noakun!='".$CLM."' ".$where."   group by noakun,kodeorg order by noakun";
// echo $str;
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    if (!isset($TAB[$bar->noakun])) { continue; }
    $kd = $bar->kodeorg;
    if (!isset($TAB[$bar->noakun]['unit'][$kd])) {
        $TAB[$bar->noakun]['unit'][$kd] = array('kodeorg'=>$kd,'sawal'=>0,'debet'=>0,'kredit'=>0,'salak'=>0);
    }
    $TAB[$bar->noakun]['unit'][$kd]['sawal'] += $bar->sawal;
    $TAB[$bar->noakun]['unit'][$kd]['salak'] += $bar->sawal;
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
    and noakun!='".$CLM."' and revisi <= '".$revisi."' group by noakun,kodeorg"; #tidak sama dengan laba/rugi berjalan
// echo $str;
$res=$owlPDO->query($str);

$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	if (!isset($TAB[$bar->noakun])) { continue; }
	$kd = $bar->kodeorg;
	if (!isset($TAB[$bar->noakun]['unit'][$kd])) {
		$TAB[$bar->noakun]['unit'][$kd] = array('kodeorg'=>$kd,'sawal'=>0,'debet'=>0,'kredit'=>0,'salak'=>0);
	}
	$TAB[$bar->noakun]['unit'][$kd]['debet'] += $bar->debet;
	$TAB[$bar->noakun]['unit'][$kd]['kredit'] += $bar->kredit;
	$TAB[$bar->noakun]['unit'][$kd]['salak'] = $TAB[$bar->noakun]['unit'][$kd]['sawal'] + $TAB[$bar->noakun]['unit'][$kd]['debet'] - $TAB[$bar->noakun]['unit'][$kd]['kredit'];
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
#nama semua unit di bawah PT ini (bukan cuma unit filter-nya) supaya breakdown per unit di mode Rincian
#bisa nampilin nama unit-nya, bukan cuma kode kodeorg mentah
$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"induk='".$pt."'");

if($gudang==''){
	$unit = 'Seluruh Unit';
	$infoUnit = $unit;
}else{
	$unit = $gudang;
	$infoUnit = $unit." - ".(isset($nmorg[$unit]) ? $nmorg[$unit] : '');
}
$infofilter = 'PT: '.$pt.' | Unit: '.$infoUnit.' | Periode: '.$periode.' s/d '.$periode1.' | Revisi: '.$revisi;

#kop/ditarik-oleh format standar (sama seperti laporan lain), hanya untuk export Excel, bukan preview html
if($tipelaporan=='excel'){
	$hdpt = setheadreport($pt, $pt);
	$kolom = 8;
	$stream .= "<table>
		<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
		<tr><td colspan=" . $kolom . "><b>NERACA SALDO</b></td></tr>
		<tr><td colspan=" . $kolom . ">" . htmlspecialchars($infofilter) . "</td></tr>
		<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
		<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
		</table>";
}
$stream.="
        <table class=sortable cellspacing=1 cellpadding=3 ".$border.">
            <colgroup><col style='width:50px'><col style='width:80px'><col style='width:400px'><col style='width:180px'><col style='width:130px'><col style='width:130px'><col style='width:130px'><col style='width:130px'></colgroup>
            <thead>
                <tr>
                    <th align=center>".$_SESSION['lang']['nomor']."</th>
                    <th align=center>".$_SESSION['lang']['noakun']."</th>
                    <th align=center>".$_SESSION['lang']['namaakun']."</th>
                    <th align=center>".$_SESSION['lang']['unit']."</th>
                    <th align=center>".$_SESSION['lang']['saldoawal']."</th>
                    <th align=center>".$_SESSION['lang']['debet']."</th>
                    <th align=center>".$_SESSION['lang']['kredit']."</th>
                    <th align=center>".$_SESSION['lang']['saldoakhir']."</th>
                </tr>
            </thead>
            <tbody>";



	
        foreach($TAB as $baris => $data){
            if($data['noakun']!=''){
                #satu baris per unit yang benar-benar ada datanya (kalau akun sama sekali tidak ada aktivitas
                #di unit manapun, tetap tampil satu baris kosong seperti sebelumnya)
                $units = $data['unit'];
                if(count($units)==0){
                    $units = array(''=>array('kodeorg'=>'','sawal'=>0,'debet'=>0,'kredit'=>0,'salak'=>0));
                }
                foreach($units as $u){
                if($tampilanId==1){
                    if(($u['sawal']==0)&&($u['debet']==0)&&($u['kredit']==0)){
                        continue;
                    }
                }
                $no+=1;

                if($tipelaporan=='excel'){
                    $qsawal=$u['sawal'];
                    $qdebet=$u['debet'];
                    $qkredit=$u['kredit'];
                    $qakhir=$u['salak'];
                }else{
                    $qsawal=number_format($u['sawal'],2);
                    $qdebet=number_format($u['debet'],2);
                    $qkredit=number_format($u['kredit'],2);
                    $qakhir=number_format($u['salak'],2);
                }

                #klik baris buka detail jurnal untuk unit baris itu sendiri (kodeorg), bukan filter Unit keseluruhan,
                #supaya kalau Unit="Seluruhnya" tetap buka rincian jurnal yang benar per unit
                $gudangDetail = ($u['kodeorg']!='') ? $u['kodeorg'] : $gudang;
                if($rekapdetail=='detail' OR $rekapdetail=='1'){

                $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatDetail('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$gudangDetail."','".$revisi."',event);\">";
                }else{
                $stream.="<tr class=rowcontent style='cursor:pointer;' title='Click untuk melihat detail' onclick=\"lihatRekap('".$data['noakun']."','".$periode."','".$periode1."','".$lmperiode."','".$pt."','".$regional."','".$gudangDetail."','".$revisi."',event);\">";

                }
                $namaUnitBaris = ($u['kodeorg']!='') ? $u['kodeorg'].' - '.(isset($nmorg[$u['kodeorg']]) ? $nmorg[$u['kodeorg']] : '') : '';
                $stream.="<td align=center>".$no."</td>
                    <td>".$data['noakun']."</td>
                    <td>".$data['namaakun']."</td>
                    <td>".$namaUnitBaris."</td>
                    <td align=right>".$qsawal."</td>
                    <td align=right>".$qdebet."</td>
                    <td align=right>".$qkredit."</td>
                    <td align=right>".$qakhir."</td>
                </tr>";

                $sal_awal+=$u['sawal'];
                $sal_debet+=$u['debet'];
                $sal_kredit+=$u['kredit'];
                $sal_salak+=$u['salak'];
                }
            }
        }

$stream.="<tr class=rowcontent>
            <td colspan=4 align=center><b>".$_SESSION['lang']['total']."</b></td>
            <td align=right><b>".number_format($sal_awal,2)."</b></td>
            <td align=right><b>".number_format($sal_debet,2)."</b></td>
            <td align=right><b>".number_format($sal_kredit,2)."</b></td>
            <td align=right><b>".number_format($sal_salak,2)."</b></td>
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