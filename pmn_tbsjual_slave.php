<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

if(count($_POST)>0){	
	$param= $_POST;
}else{
	$param= $_GET;
}

$method = checkPostGet('method','');

$table='pmn_tbsjual';

$str = "select * from pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];
}

#= ambil daftar unit didalam pt bentukan array
$str = "select * from ".$dbname.".organisasi where (length(kodeorganisasi)=4 or length(kodeorganisasi)=3 or length(kodeorganisasi)=6) ";
// echo $str;exit();
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

switch($method){
	
	case'excel':
	
		$str = "select * from ".$dbname.".pmn_tbsjual  where notransaksi='".$param['notransaksi']."' ";
		$res=fetchdata($str);
			$tgl1excel1=tanggalnormal($res[0]['tanggaltbs1']);
			$tgl1excel2=tanggalnormal($res[0]['tanggaltbs2']);
		
			// exit("Error:".$disetujui.' '.$diperiksa.' '.$dibuat);
	
		$stream.="<table border=0>";
			$stream.="<tr>";
				$stream.="<td colspan=17 ><b>".$nmorg[$kodept[$res[0]['unit']]]."</td>";
			$stream.="</tr>";
			$stream.="<tr>";
				$stream.="<td colspan=17 ><b>Commercial Division</td>";
			$stream.="</tr>";
			$stream.="<tr>";
				$stream.="<td colspan=17 align=center><b>Rekapan Penjualan TBS Periode Tanggal ".tglnmbln($res[0]['tanggaltbs1'],'','')." s/d ".tglnmbln($res[0]['tanggaltbs2'],'','')."</td>";
			$stream.="</tr>";
			$stream.="<tr>";
				$stream.="<td colspan=17 align=center><b>".$namacustomer[$res[0]['kodecustomer']]."</td>";
			$stream.="</tr>";
		$stream.="</table>";
		$stream.="<br>";
		
	
		$stream.="<table border=1>";
		$stream.="
		<tr class=rowheader  bgcolor=#D3D3D3>
			<td  align=center>".$_SESSION['lang']['nourut']."</td>
			 <td  align=center>".$_SESSION['lang']['noTiket']."</td>
			<td  align=center>".$_SESSION['lang']['nospb']."</td>
			 <td  align=center>".$_SESSION['lang']['kodevhc']."</td>
			 <td  align=center>".$_SESSION['lang']['tanggal']."<br>PKS</td>
			 <td  align=center>".$_SESSION['lang']['tanggal']."<br>SPB</td>
			 <td  align=center>".$_SESSION['lang']['berat']." I</td>
			 <td  align=center>".$_SESSION['lang']['berat']." II</td>
			 <td  align=center>".$_SESSION['lang']['berat']." TBS</td>
			 <td  align=center>".$_SESSION['lang']['potongan']."</td>
			 <td  align=center>".$_SESSION['lang']['netto']."</td> 
			 <td  align=center>".$_SESSION['lang']['jjg']."</td> 
			 <td  align=center>".$_SESSION['lang']['bjr']."</td> 
			
			 <td  align=center>".$_SESSION['lang']['blok']."</td>   
			 <td  align=center>".$_SESSION['lang']['tahuntanam']."</td>   
			 <td  align=center>".$_SESSION['lang']['harga']."</td>   
			 <td  align=center>".$_SESSION['lang']['total']."</td>   
		</tr>";
		
		$no=0;
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$param['notransaksi']."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$stream.="<tr>";
				$stream.="<td>".$no."</td>";
				$stream.="<td>".$bar['notiket']."</td>";
				$stream.="<td>".$bar['nospb']."</td>";
				$stream.="<td>".$bar['nokendaraan']."</td>";
				$stream.="<td>".$bar['tanggalpks']."</td>";
				$stream.="<td>".$bar['tanggalspb']."</td>";
				$stream.="<td align=right>".number_format($bar['kgmasuk'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgkeluar'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgbruto'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgpotongan'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgnetto'])."</td>";
				$stream.="<td align=right>".number_format($bar['jjg'])."</td>";
				$stream.="<td align=right>".number_format($bar['bjr'],2)."</td>";
				$stream.="<td align=right>".$bar['blok']."</td>";
				$stream.="<td align=right>".$bar['tahuntanam']."</td>";
				$stream.="<td align=right>".number_format($bar['rpkg'],2)."</td>";
				$stream.="<td align=right>".number_format($bar['totalrp'],2)."</td>";
				
			$stream.="</tr>";
			
			@$ttotalrp+=$bar['totalrp'];
			@$tkgmasuk+=$bar['kgmasuk'];
			@$tkgkeluar+=$bar['kgkeluar'];
			@$tkgbruto+=$bar['kgbruto'];
			@$tkgpotongan+=$bar['kgpotongan'];
			@$tkgnetto+=$bar['kgnetto'];
			@$tjjg+=$bar['jjg'];
				
		}
		
			$stream.="<tr class=rowheader>";
				$stream.="<td align=center colspan=6>".$_SESSION['lang']['total']."</td>";
				$stream.="<td align=right>".@number_format($tkgmasuk)."</td>";
				$stream.="<td align=right>".@number_format($tkgkeluar)."</td>";
				$stream.="<td align=right>".@number_format($tkgbruto)."</td>";
				$stream.="<td align=right>".@number_format($tkgpotongan)."</td>";
				$stream.="<td align=right>".@number_format($tkgnetto)."</td>";
				$stream.="<td align=right>".@number_format($tjjg)."</td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right>".@number_format($ttotalrp,2)."</td>";
			$stream.="</tr>";
		
		
		$stream.="</table>";
		$stream.="<br>";
		
		
		$nop = "TBS_".$param['notransaksi']."_".$tgl1excel1."_s/d_".$tgl1excel2.".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("TBS", $stream);
		$xls->headers($nop);
		echo $xls->buildFile();

	break;

	case'posting':
		try {
			$owlPDO->beginTransaction();
			
			$str = "update ".$dbname.".".$table." set posting=1,postingby='".$_SESSION['standard']['userid']."',postingtime='".date('Y-m-d H:i:s')."' where notransaksi='".$param['notransaksi']."' ";
			// exit("Error:$str");
			$owlPDO->exec($str);
			
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
	
	case'deleteht':
		try {
			$owlPDO->beginTransaction();
			
			$str = "delete from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."' ";
			
			$owlPDO->exec($str);
			
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}
	break;
	
	case'geteditht':
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$notransaksi=$bar['notransaksi'];
			$unit=$bar['unit'];
			$kodecustomer=$bar['kodecustomer'];
			$tanggal=$bar['tanggal'];
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
			$persenppn=$bar['persenppn'];
			$persenpph=$bar['persenpph'];
			$keteranganht=$bar['keteranganht'];
			$nokontrak=$bar['nokontrak'];
		}
			
		
		echo $notransaksi."###".$unit."###".tanggalnormal($tanggal)."###".$kodecustomer."###".$nokontrak."###".tanggalnormal($tanggaltbs1)."###".tanggalnormal($tanggaltbs2)."###".$persenppn."###".$persenpph."###".$keteranganht;
		
		// exit("Error:a");
	break;
	

   case'loaddata':
	
		$where=" 1=1 ";
		if($param['tanggalselesaisch']!='' and $param['tanggalmulaisch']!=''){
			$where.=" and tanggal between '".$param['tanggalmulaisch']."' and '".$param['tanggalselesaisch']."'";
		}
		if($param['notransaksisch']!=''){
			$where.=" and notransaksi like '%".$param['notransaksisch']."%'";
		}
		
		if($param['nokontraksch']!=''){
			$where.=" and nokontrak like '%".$param['nokontraksch']."%'";
		}
		
		if($param['kodecustomersch']!=''){
			$where.=" and kodecustomer like '%".$param['kodecustomersch']."%'";
		}
		
		$limit = 15;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		
		$offset = $page * $limit;
		// $str = "select count(*) as jlhbrs from ".$dbname.".".$table." where ".$where." group by notransaksi  ";
		$str = "select count(distinct(notransaksi)) as jlhbrs from ".$dbname.".".$table." where ".$where."  ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jlhbrs = $bar['jlhbrs'];
		}
		$colspan=12;
		$no = 0;
		$no=$maxdisplay;
		$str = "select sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,tanggal,tanggaltbs1,tanggaltbs2,kodecustomer,nokontrak,unit,notransaksi,posting,createby,postingby,createtime,postingtime from ".$dbname.".".$table." where ".$where."  group by notransaksi order by tanggal desc,notransaksi desc  limit " . $offset . "," . $limit . " ";
			// echo $str;
			// exit();
		$res=fetchdata($str);
		foreach($res as $bar){
			
			#=datakaryawan
			$strdt="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid in ('".$bar['createby']."','".$bar['postingby']."') ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namakaryawan[$bardt['karyawanid']]=$bardt['namakaryawan'];
			}

			$no++;
			$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td>".$bar['notransaksi']."</td>";
				$stream.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$stream.="<td>".$bar['unit']."</td>";
				$stream.="<td>".@$namacustomer[$bar['kodecustomer']]."</td>";
				$stream.="<td>".$bar['nokontrak']."</td>";
				$stream.="<td>".tanggalnormal($bar['tanggaltbs1'])." s/d ".tanggalnormal($bar['tanggaltbs2'])."</td>";
				$stream.="<td align=right>".number_format($bar['kgnetto'],2)."</td>";
				$stream.="<td align=right>".number_format($bar['totalrp'],2)."</td>";
				$stream.="<td>".@$namakaryawan[$bar['createby']]."</td>";
				$stream.="<td>".@$namakaryawan[$bar['postingby']]."</td>";
				
				$stream.="<td align=center>";
				if($bar['posting']==0){
					$stream.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
						onclick=\"editht('".$bar['notransaksi']."');\">";
					$stream.="&nbsp;&nbsp;&nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"deleteht('".$bar['notransaksi']."');\">";		
					$stream.="&nbsp;&nbsp;&nbsp;<img src=images/skyblue/posting.png class=resicon  title='Posting Data' onclick=\"posting('".$bar['notransaksi']."');\" >";
				} else{
					$stream.="&nbsp;&nbsp;&nbsp;<img src=images/skyblue/posted.png class=resicon  title='Posted'>";   
				}
				$stream.="&nbsp;&nbsp;&nbsp;<img src=images/excel.jpg class=resicon  caption='Excel'  title='Excel  ".$bar['notransaksi']."' onclick=\"excel('".$bar['notransaksi']."');\">";	
				$stream.="</td>";
			$stream.="</tr>";
        }
		
		$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		// $tab.="</table>";
		
		echo $stream."####".$footd;
	break;
	
	case'saveht':
	
		#= validasi apakah ada nokontrak dan customer tersebut di pmn_kontrakjual
		
		if($param['notransaksi']==''){
			$str = "select count(*) as jumlah from ".$dbname.".pmn_kontrakjual  where  nokontrak='".$param['nokontrak']."' and koderekanan='".$param['kodecustomer']."'";	
			$res=fetchdata($str);
			foreach($res as $bar){
				$jumlah=$bar['jumlah'];
			}
		
			if($jumlah<1){
				exit("Warning:Tidak ada nomor kontrak ".$param['nokontrak']." dengan customer ".$namacustomer[$param['kodecustomer']]." ");
			}
		
			$unit=$param['unit'];
			$tanggal=tanggalsystemn($param['tanggal']);
			$notransaksi = generatenotransaksitbsbeli();
		}else{
			$notransaksi=$param['notransaksi'];
		}
		echo $notransaksi;
	break;
	
	
	case'loaddatadt':
	
	
	
		$str = "select * from ".$dbname.".pabrik_timbangan_vw  where kodeorg='".$param['unit']."' and tanggal between '".tanggalsystemn($param['tanggaltbs1'])."' and '".tanggalsystemn($param['tanggaltbs2'])."' and nokontrak='".$param['nokontrak']."' and kodecustomer='".$param['kodecustomer']."'";	
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnospbpks[$bar['nospb']]=$bar['nospb'];
			$arrnospblct[$bar['spblct']]=$bar['spblct'];
			// $arrnospb[$bar['nospb']]=$bar['nospb'];
			if($bar['spblct']!=''){
				$dttanggalpks[$bar['spblct']]=$bar['tanggal'];
			}else{
				$dttanggalpks[$bar['nospb']]=$bar['tanggal'];
			}
			
			$dtnotiket[$bar['nospb']]=$bar['notiket'];
		}
		
		// echo"<pre>";
		// print_r($dttanggalpks);
	
	
		$str = "select * from ".$dbname.".kebun_spb_vw  where  (nospb in ('".implode("','",$arrnospbpks)."')) or (spblct in ('".implode("','",$arrnospblct)."'))";	
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnospb[$bar['nospb']]=$bar['nospb'];
			$arrkodeblok[$bar['blok']]=$bar['blok'];
			$listkodeblok[$bar['nospb']][$bar['blok']]=$bar['blok'];
			$dtnokendaraan[$bar['nospb']][$bar['blok']]=$bar['nokendaraan'];
			// $dttanggalpks[$bar['nospb']][$bar['blok']]=$bar['tanggalpks'];
			$dttanggalspb[$bar['nospb']][$bar['blok']]=$bar['tanggal'];
			$dtkgmasuk[$bar['nospb']][$bar['blok']]=$bar['beratmasuk'];
			$dtkgkeluar[$bar['nospb']][$bar['blok']]=$bar['beratkeluar'];
			$dtkgbruto[$bar['nospb']][$bar['blok']]=$bar['kgwb'];
				$dtkgnetto[$bar['nospb']][$bar['blok']]=$bar['kgwb'];
				// $dtkgnetto[$bar['nospb']][$bar['blok']]=$bar['kgwbnetto'];
				// $dtkgpotongan[$bar['nospb']][$bar['blok']]=$bar['kgwb']-$bar['kgwbnetto'];
			$dtkgpotongan[$bar['nospb']][$bar['blok']]=0;
			$dtjjg[$bar['nospb']][$bar['blok']]=$bar['jjg'];
			$dtbjr[$bar['nospb']][$bar['blok']]=$bar['bjrwb'];
			// $dtnospb[$bar['nospb']][$bar['blok']]=$bar['nospb'];
			$dttahuntanam[$bar['nospb']][$bar['blok']]=$bar['tahuntanam'];
			$dtspblct[$bar['nospb']][$bar['blok']]=$bar['spblct'];
			// $dtnodo[$bar['nospb']][$bar['blok']]=$bar['nodo'];
			// $dttransportir[$bar['nospb']][$bar['blok']]=$bar['transportir'];
			@$nomax+=1;
        }
		
		
		$str = "select * from ".$dbname.".setup_blok  where  kodeorg in ('".implode("','",$arrkodeblok)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$dtintiplasma[$bar['kodeorg']]=$bar['intiplasma'];
			$dtstatusblok[$bar['kodeorg']]=$bar['statusblok'];
		}
		
		$stream.="<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
				 <th  align=center>".$_SESSION['lang']['nourut']."</th>
				 <th  align=center>".$_SESSION['lang']['noTiket']."</th>
				<th  align=center>".$_SESSION['lang']['nospb']."</th>
				<th  align=center>".$_SESSION['lang']['nospb']." LCT</th>
				 <th  align=center>".$_SESSION['lang']['kodevhc']."</th>
				 <th  align=center>".$_SESSION['lang']['tanggal']."<br>PKS</th>
				 <th  align=center>".$_SESSION['lang']['tanggal']."<br>SPB</th>
				 <th  align=center>".$_SESSION['lang']['berat']." I</th>
				 <th  align=center>".$_SESSION['lang']['berat']." II</th>
				 <th  align=center>".$_SESSION['lang']['berat']." TBS</th>
				 <th  align=center>".$_SESSION['lang']['potongan']."</th>
				 <th  align=center>".$_SESSION['lang']['netto']."</th> 
				 <th  align=center>".$_SESSION['lang']['jjg']."</th> 
				 <th  align=center>".$_SESSION['lang']['bjr']."</th> 
				
				 <th  align=center>".$_SESSION['lang']['blok']."</th>   
				 <th  align=center>".$_SESSION['lang']['tahuntanam']."</th>   
				 <th  align=center>".$_SESSION['lang']['intiplasma']."</th>   
				 <th  align=center>".$_SESSION['lang']['statusblok']."</th>   
				 <th  align=center>".$_SESSION['lang']['harga']."</th>   
				 <th  align=center>".$_SESSION['lang']['total']."</th>   
				 <th  align=center>*</th> 		
			</thead></tr>";
	
					// print_r($arrnotiket);
					
			foreach(@$arrnospb as $nospb){
				foreach($arrkodeblok as $kodeblok){
					if(@$listkodeblok[$nospb][$kodeblok]){
						@$no++;
						$stream.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
							$stream.="<td align=center>".$no."</td>";
							$stream.="<td id=notiket".$no.">".$dtnotiket[$nospb]."</td>";
							$stream.="<td id=nospb".$no.">".$nospb."</td>";
							$stream.="<td id=spblct".$no.">".$dtspblct[$nospb][$kodeblok]."</td>";
							$stream.="<td id=nokendaraan".$no.">".$dtnokendaraan[$nospb][$kodeblok]."</td>";
							if($dtspblct[$nospb][$kodeblok]!=''){
								$stream.="<td id=tanggalpks".$no.">".tanggalnormal($dttanggalpks[$dtspblct[$nospb][$kodeblok]])."</td>";
							}else{
								$stream.="<td id=tanggalpks".$no.">".tanggalnormal($dttanggalpks[$nospb])."</td>";
							}
							$stream.="<td id=tanggalspb".$no.">".tanggalnormal($dttanggalspb[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=kgmasuk".$no.">".number_format($dtkgmasuk[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=kgkeluar".$no.">".number_format($dtkgkeluar[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=kgbruto".$no.">".number_format($dtkgbruto[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=kgpotongan".$no.">".number_format($dtkgpotongan[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=kgnetto".$no.">".number_format($dtkgnetto[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=jjg".$no.">".number_format($dtjjg[$nospb][$kodeblok])."</td>";
							$stream.="<td align=right id=bjr".$no.">".number_format($dtbjr[$nospb][$kodeblok],2)."</td>";
							$stream.="<td id=blok".$no.">".$kodeblok."</td>";
							$stream.="<td align=right id=tahuntanam".$no.">".$dttahuntanam[$nospb][$kodeblok]."</td>";
							$stream.="<td id=intiplasma".$no.">".$dtintiplasma[$kodeblok]."</td>";
							$stream.="<td id=statusblok".$no.">".$dtstatusblok[$kodeblok]."</td>";
							if($dtspblct[$nospb][$kodeblok]!=''){
								$str = "select * from ".$dbname.".pmn_kontrakjualdt_harga  where nokontrak='".$param['nokontrak']."' and tanggalkirim2>='".$dttanggalpks[$dtspblct[$nospb][$kodeblok]]."' and tanggalkirim1<='".$dttanggalpks[$dtspblct[$nospb][$kodeblok]]."' and tahuntanam='".$dttahuntanam[$nospb][$kodeblok]."' order by tanggalkirim1 desc limit 1";
								$res=fetchdata($str);
								foreach($res as $bar){
									$datarpkg[$bar['tahuntanam']]=$bar['harga'];
								}	
							}else{
								$str = "select * from ".$dbname.".pmn_kontrakjualdt_harga  where nokontrak='".$param['nokontrak']."' and tanggalkirim2>='".$dttanggalpks[$nospb]."' and tanggalkirim1<='".$dttanggalpks[$nospb]."' and tahuntanam='".$dttahuntanam[$nospb][$kodeblok]."' order by tanggalkirim1 desc limit 1";
								$res=fetchdata($str);
								foreach($res as $bar){
									$datarpkg[$bar['tahuntanam']]=$bar['harga'];
								}	
							}
							
							
							$stream.="<td align=right id=rpkg".$no.">".number_format($datarpkg[$dttahuntanam[$nospb][$kodeblok]],2)."</td>";
								$dttotalrp[$nospb][$kodeblok]=$datarpkg[$dttahuntanam[$nospb][$kodeblok]]*$dtkgnetto[$nospb][$kodeblok];
							$stream.="<td align=right id=totalrp".$no.">".number_format($dttotalrp[$nospb][$kodeblok],2)."</td>";
							
							
							if($datarpkg[$dttahuntanam[$nospb][$kodeblok]]==0 || $datarpkg[$dttahuntanam[$nospb][$kodeblok]]==''){
								$stream.="<td><font color=red>Belum terdapat Harga untuk tahun tanam ".$dttahuntanam[$nospb][$kodeblok]."</font></td>";
								$errtampung=" Belum terdapat Harga untuk tahun tanam ".$dttahuntanam[$nospb][$kodeblok]."\n  ";
							} else{
								$stream.="<td></td>";		
							}		
							
							
						$stream.="</tr>";	
						
						$tdtkgmasuk+=$dtkgmasuk[$nospb][$kodeblok];
						$tdtkgkeluar+=$dtkgkeluar[$nospb][$kodeblok];
						$tdtkgbruto+=$dtkgbruto[$nospb][$kodeblok];
						$tdtkgpotongan+=$dtkgpotongan[$nospb][$kodeblok];
						$tdtkgnetto+=$dtkgnetto[$nospb][$kodeblok];
						$tdtjjg+=$dtjjg[$nospb][$kodeblok];
						$tdttotalrp+=$dttotalrp[$nospb][$kodeblok];
						
					}
				}
			}
	
			$stream.="<tr class=rowheader>";
				$stream.="<td align=center colspan=7>".$_SESSION['lang']['total']."</td>";
				$stream.="<td align=right>".@number_format($tdtkgmasuk)."</td>";
				$stream.="<td align=right>".@number_format($tdtkgkeluar)."</td>";
				$stream.="<td align=right>".@number_format($tdtkgbruto)."</td>";
				$stream.="<td align=right>".@number_format($tdtkgpotongan)."</td>";
				$stream.="<td align=right>".@number_format($tdtkgnetto)."</td>";
				$stream.="<td align=right>".@number_format($tdtjjg)."</td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right>".@number_format($tdttotalrp,2)."</td>";
				$stream.="<td align=right></td>";
			$stream.="</tr>";
			
			
			if($errtampung!=''){
				$stream.="<tr class=rowcontent>";
					$stream.="<td align=center colspan=20><font color=red><b>".$errtampung."</b></font><button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button><button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button></td>";
				$stream.="</tr>";	
			}else{
				$stream.="<tr class=rowcontent>";
					$stream.="<td align=center colspan=20><button  id=save class=mybutton onclick=savedt(".@$no.")>".$_SESSION['lang']['save']."</button>
					<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
					<button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button></td>";
				$stream.="</tr>";	
			// <button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button></td>";	
			}
			
			
			
		$stream.="</table>";
	
		
		echo $stream;
		
		
		// exit("Error");
		
		
	break;
	
	
	case'savedt':
	
		$param['rpkg']=str_replace(',', '',$param['rpkg']);
		$param['totalrp']=str_replace(',', '',$param['totalrp']);
		$param['kgmasuk']=str_replace(',', '',$param['kgmasuk']);
		$param['kgkeluar']=str_replace(',', '',$param['kgkeluar']);
		$param['kgbruto']=str_replace(',', '',$param['kgbruto']);
		$param['kgnetto']=str_replace(',', '',$param['kgnetto']);
		$param['kgpotongan']=str_replace(',', '',$param['kgpotongan']);
		$param['jjg']=str_replace(',', '',$param['jjg']);
		
		
		try {
			$owlPDO->beginTransaction();
			
			#= delete 1st
			if($param['currRow']=='1'){
				#= delete 1st
				$str = "delete from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."'";
				// exit("Error:$str");
				$owlPDO->exec($str);
			}
			
		
			$str = "insert into ".$dbname.".".$table." (notransaksi,tanggal,tanggaltbs1,tanggaltbs2,tanggalspb,tanggalpks,keteranganht,nospb,notiket,unit,blok,kgmasuk,kgkeluar,kgbruto,kgpotongan,kgnetto,jjg,bjr,tahuntanam,rpkg,totalrp,persenppn,persenpph,nokendaraan,nokontrak,kodecustomer,createby,createtime,updateby,updatetime,intiplasma,statusblok,spblct)
			VALUES ('".$param['notransaksi']."','".tanggalsystemn($param['tanggal'])."','".tanggalsystemn($param['tanggaltbs1'])."','".tanggalsystemn($param['tanggaltbs2'])."','".tanggalsystemn($param['tanggalspb'])."','".tanggalsystemn($param['tanggalpks'])."','".$param['keteranganht']."','".$param['nospb']."','".$param['notiket']."','".$param['unit']."','".$param['blok']."','".$param['kgmasuk']."','".$param['kgkeluar']."','".$param['kgbruto']."','".$param['kgpotongan']."','".$param['kgnetto']."','".$param['jjg']."','".$param['bjr']."','".$param['tahuntanam']."','".$param['rpkg']."','".$param['totalrp']."','".$param['persenppn']."','".$param['persenpph']."','".$param['nokendaraan']."','".$param['nokontrak']."','".$param['kodecustomer']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$param['intiplasma']."','".$param['statusblok']."','".$param['spblct']."')";
			
			$owlPDO->exec($str);
			
			$owlPDO->commit();			
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warning gagal eksekusi data \n" . addslashes($e->getMessage());
		}	
	break;
	
	
	default:
	break;
	
			
}

?>