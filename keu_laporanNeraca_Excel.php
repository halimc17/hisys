<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt=$_GET['pt'];
$unit=$_GET['gudang'];
$periode=$_GET['periode'];
$periode1=$_GET['periode1'];
$revisi=$_GET['revisi'];

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$bulan=$qwe[1];

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}
#++++++++++++++++++++++++++++++++++++++++++
$kodelaporan='BALANCE SHEET';

$periodesaldo=str_replace("-", "", $periode);

#lalu
if($periode1=='akhir')$periodPRF=substr($periodesaldo,0,4)."01"; else $periodPRF=$tahunlalu.$bulan;
if($periode1=='akhir')$periodPRF2=substr($periodesaldo,0,4)."-01"; else $periodPRF2=$tahunlalu."-".$bulan;
if($periode1=='akhir')$kolomPRF="awal01"; else $kolomPRF="awal".date('m',$t); #"awal".substr($periodesaldo,4,2);

#sekarang
$t=mktime(0,0,0,substr($periodesaldo,4,2)+1,15,substr($periodesaldo,0,4));
$periodCUR=date('Ym',$t);
$periodCUR2=substr($periodesaldo,0,4).'-'.substr($periodesaldo,4,2);
$kolomCUR="awal".date('m',$t);

#captionsekarang============================
$t=mktime(0,0,0,substr($periodesaldo,4,2),15,substr($periodesaldo,0,4));
$captionCUR=date('M-Y',$t);

#captionlalu
$t=mktime(0,0,0,12,15,substr($periodesaldo,0,4)-1);
$t1=mktime(0,0,0,$bulan,15,substr($periodesaldo,0,4)-1);
if($periode1=='akhir')$captionPRF=date('M-Y',$t); else $captionPRF=$captionPRF=date('M-Y',$t1);

//echo "--".$periodPRF."==".$kolomPRF.">>".$captionPRF;

#query+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if($unit=='')
    $where=" kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
else 
    $where=" kodeorg='".$unit."'";

$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
    $dzArr[$bar->nourut]['tampil']=$bar->variableoutput;    
    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
    if($_SESSION['language']=='ID'){
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;
    }
    else{
        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
    }
    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
}

$stream="<div style='position:fixed;'><table class=sortable border=0 cellspacing=1>
    <thead>
        <tr class=rowheader>
        <td width='395px;'></td>
        <td align=center width='200px;'>".$captionCUR."</td>
        <td align=center width='200px;'>".$captionPRF."</td>    
        </tr>
    </thead><tbody></tbody>
    </table>
    </div> 
    <table class=sortable border=0 cellspacing=1><thead><tr><td colspan=7 width='800px;'></td></tr></thead><tbody>";
$jlhkolom=7;
$addEx="";
if(!empty($dzArr))foreach($dzArr as $data){
    
    switch ($data['nourut']) {
        case '1253':
            #untuk biaya tbm,exclude biaya TM
            $addEx=" and noakun not in (".$data['noakundisplay'].")";
        break;
        case'1260':
            #untuk aktiva tetap
            $noDtakun=explode(",",$data['noakundisplay']);
            if(!empty($noDtakun)){
                $addEx=" and left(noakun,5) not between '".$noDtakun[0]."' and '".$noDtakun[1]."'";    
            }
        break;
        case'1270':
            #aktiva lainya
            $addEx=" and left(noakun,5) not in (".$data['noakundisplay'].")";
        break;
        case'1211':
            #piutang istimewa
            $addEx=" and left(noakun,3) not in (".$data['noakundisplay'].")";
        break;
        case'1060':
            #Jumlah Aktiva Lancar, (11302)
            $addEx=" and left(noakun,5) not in (".$data['noakundisplay'].")";
        break;
    }
    $st12="select sum(".$kolomPRF.") as kemarin
        from ".$dbname.".keu_saldobulanan where noakun between '".$data['noakundari']."' 
        and '".$data['noakunsampai']."' ".$addEx." and (periode='".$periodPRF."') and ".$where;
    $jlhlalu=0;
    $res1=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res1->fetch())
    {
        $jlhlalu=$ba12->kemarin;
    }     
    $dzArr[$data['nourut']]['jumlahlalu']=$jlhlalu;
    
    if($revisi==0){ // kalo revisi 0, ambil data dari saldo bulanan
        $st12="select sum(".$kolomCUR.") as sekarang
            from ".$dbname.".keu_saldobulanan where noakun between '".$data['noakundari']."' 
            and '".$data['noakunsampai']."'  ".$addEx." and (periode='".$periodCUR."') and ".$where;
        $jlhsekarang=0;
    $res1=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res1->fetch())
    {
            $jlhsekarang=$ba12->sekarang;
        }      
        $dzArr[$data['nourut']]['jumlahsekarang']=$jlhsekarang;      
    }
    if(!empty($data['noakundisplay'])){
        $dt=explode(",",$data['noakundisplay']);
        $temPdt=0;
        $temPdtLalu=0;
        switch ($data['nourut']) {
            case '1390':
                foreach($dt as $dtIsi){
                    $temPdt+=$dzArr[$dtIsi]['jumlahsekarang'];
                    $temPdtLalu+=$dzArr[$dtIsi]['jumlahlalu'];
                }

                $dzArr[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                $dzArr[$data['nourut']]['jumlahsekarang']=$temPdt;
            break;
        }

    }
    $addEx="";#mengosongkan additional exception, agar tidak terbawa ke nourut yg lain
}

if($revisi>0){ // kalo revisi > 0, ambil data dari jurnal
    $st12="select noakun, sum(jumlah) as jumlah
        from ".$dbname.".keu_jurnaldt_vw where periode between '".$periodPRF2."' 
        and '".$periodCUR2."' and ".$where."  and revisi <= '".$revisi."' group by noakun";  
    $jlhsekarang=0;
    $res1=$owlPDO->query($st12) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($ba12=$res1->fetch())
    {
        if(!empty($dzArr))foreach($dzArr as $data){
            if(($ba12->noakun>=$data['noakundari'])&&($ba12->noakun<=$data['noakunsampai'])){
                if(!isset($dzArr[$data['nourut']]['jumlahtemp'])) $dzArr[$data['nourut']]['jumlahtemp']=0;
                $dzArr[$data['nourut']]['jumlahtemp']+=$ba12->jumlah; 
                $dzArr[$data['nourut']]['jumlahsekarang']=$dzArr[$data['nourut']]['jumlahlalu']+$dzArr[$data['nourut']]['jumlahtemp'];
            } else {
                $dzArr[$data['nourut']]['jumlahsekarang']=0;
            }
            if(!empty($data['noakundisplay'])){
            $dt=explode(",",$data['noakundisplay']);
            $temPdt=0;
            $temPdtLalu=0;
                switch ($data['nourut']) {
                    case '1390':
                        foreach($dt as $dtIsi){
                            $temPdt+=$dzArr[$dtIsi]['jumlahtemp'];
                            $temPdtLalu+=$dzArr[$dtIsi]['jumlahlalu'];
                        }
                        $dzArr[$data['nourut']]['jumlahlalu']=$temPdtLalu;
                        $dzArr[$data['nourut']]['jumlahsekarang']=$temPdt+$temPdtLalu;
                    break;
                }
            }
        }
    }                 
}

#ambil format mesinlaporan==========
if(!empty($dzArr))foreach($dzArr as $data){
    
    if($data['tipe']=='Header')
    {
        if($data['tampil']==0)
            $stream.="<tr class=rowcontent><td colspan=7><b>".$data['keterangan']."</b></td></tr>";  
        else{
            $stream.="<tr class=rowcontent>
                <td colspan=".$data['tampil']."></td>
                <td colspan=".($jlhkolom-$data['tampil'])."><b>".$data['keterangan']."</b></td>
            </tr>"; 
        }
    }
    else
    if($data['tipe']=='Total'){
        if($data['tampil']==0){
            $stream.="<tr class=rowcontent>
                <td colspan=5></td>
                <td colspan=2>--------------------------------------------------</td>
                </tr>
            <tr class=rowcontent>
                <td colspan=5><b>".$data['keterangan']."</b></td>
                <td align=right><b>".number_format($data['jumlahsekarang'],2)."</b></td>
                <td align=right><b>".number_format($data['jumlahlalu'],2)."</b></td>    
            </tr>
            <tr class=rowcontent>
                <td style='width:30px'></td>
                <td style='width:30px'></td>
                <td style='width:30px'></td>
                <td colspan=4></td>
            </tr>
            "; 
        }
        else
        {
            $stream.="<tr class=rowcontent>
                <td colspan=5></td>
                <td colspan=".($jlhkolom-5).">--------------------------------------------------</td>
            </tr>
            <tr class=rowcontent>
                <td colspan=".$data['tampil']."></td>
                <td colspan=".(5-$data['tampil'])."><b>".$data['keterangan']."</b></td>
                <td align=right width='200px;'><b>".number_format($data['jumlahsekarang'],2)."</b></td>
                <td align=right width='200px;'><b>".number_format($data['jumlahlalu'],2)."</b></td>    
            </tr>
            <tr class=rowcontent><td colspan=7>.</td></tr>
            ";                
        }   
    }
    else
	{
		$stream.="
		<tr class=rowcontent title='Click untuk melihat detail' onclick=\"lihatDetailNeraca('".$data['noakundari']."','".$data['noakunsampai']."','".$periode."','".$periode1."','".$pt."','".$unit."',event);\">
			<td colspan=".($data['tampil'])."></td>
			<td colspan=".(5-$data['tampil'])."><b>".$data['keterangan']."</b></td>
			<td align=right width='200px;'><b>".number_format($data['jumlahsekarang'],2)."</b></td>
			<td align=right width='200px;'><b>".number_format($data['jumlahlalu'],2)."</b></td>    
		</tr>";          
		
		$str="select sum(".$kolomCUR.") as ".$kolomCUR.",noakun from ".$dbname.".keu_saldobulanan where noakun between '".$data['noakundari']."' 
              and '".$data['noakunsampai']."'  ".$addEx." and (periode='".$periodCUR."') and ".$where." group by noakun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		while($bar=$res->fetch())
		{
			$vkolomCUR = $bar[$kolomCUR];
			if($bar['noakun']=='3110700'||$bar['noakun']=='3110800'){
				$vkolomCUR = $bar[$kolomCUR]*(-1);
			}
			$stream.="
				<tr class=rowcontent>
					<td align=center></td>
					<td colspan=2>".$bar['noakun']."</td>
					<td colspan=".(3-$data['tampil']).">".$nmakun[$bar['noakun']]."</td>
					<td align=right>".number_format($vkolomCUR,2)."</td>
				</tr>
			";
		}
		
	}
}

$stream.= "</tbody></tfoot></tfoot></table>";     
        
// echo $stream;
#===========================================================================

$nop_="Neraca-".$pt."-".$unit."-".$periodesaldo;
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